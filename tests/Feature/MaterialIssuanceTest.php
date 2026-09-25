<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\MaterialIssue;
use App\Models\User;
use App\Notifications\IssueApprovedNotification;

class MaterialIssuanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_only_sees_own_material_requests(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create();

        MaterialIssue::factory()->create(['item_id' => $item->id, 'issued_to_user_id' => $teacher->id]);
        MaterialIssue::factory()->create(['item_id' => $item->id, 'issued_to_user_id' => $otherTeacher->id]);

        $response = $this->actingAs($teacher)->getJson('/inventory/issues');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($teacher->id, $response->json('data.0.issued_to_user_id'));
    }

    public function test_teacher_can_edit_own_pending_material_request(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create();
        $newItem = Item::factory()->create();
        $issue = MaterialIssue::factory()->create([
            'item_id' => $item->id,
            'issued_to_user_id' => $teacher->id,
            'quantity' => 1,
            'status' => 'pending',
            'notes' => 'Old purpose',
        ]);

        $this->actingAs($teacher)
            ->patchJson('/inventory/issues/'.$issue->id, [
                'item_id' => $newItem->id,
                'quantity' => 3,
                'purpose' => 'Updated classroom purpose',
            ])
            ->assertOk()
            ->assertJsonPath('quantity', 3);

        $this->assertSame('Updated classroom purpose', $issue->fresh()->notes);
    }

    public function test_material_issue_page_exposes_borrow_option_for_teachers(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->get('/inventory/issues/list')
            ->assertOk()
            ->assertSee('Borrow')
            ->assertSee('Material Issue');
    }

    public function test_create_pending_issue_and_approve()
    {
        $item = Item::factory()->create(['quantity' => 10]);

        $this->actingAs(User::factory()->create());

        $res = $this->post('/inventory/issues', [
            'item_id' => $item->id,
            'quantity' => 3,
            'purpose' => 'For classroom use',
        ]);

        $res->assertStatus(201);

        $issueId = $res->json('id');

        $approver = User::factory()->create(['role' => 'admin']);
        $this->actingAs($approver);

        $approve = $this->post('/inventory/issues/'.$issueId.'/approve');
        $approve->assertStatus(200);

        $item->refresh();
        $this->assertEquals(7, $item->quantity);
    }

    public function test_reject_issue()
    {
        $item = Item::factory()->create(['quantity' => 5]);

        $this->actingAs(User::factory()->create());

        $res = $this->post('/inventory/issues', ['item_id' => $item->id, 'quantity' => 2, 'purpose' => 'For classroom use']);
        $issueId = $res->json('id');
        $approver = User::factory()->create(['role' => 'admin']);
        $this->actingAs($approver);

        $rej = $this->post('/inventory/issues/'.$issueId.'/reject', ['notes' => 'Not allowed']);
        $rej->assertStatus(200);

        $issue = MaterialIssue::find($issueId);
        $this->assertEquals('rejected', $issue->status);
    }

    public function test_property_custodian_can_view_and_reject_pending_issue_but_cannot_edit_or_delete_it()
    {
        $item = Item::factory()->create(['quantity' => 10]);
        $requester = User::factory()->create();
        $this->actingAs($requester);

        $issue = MaterialIssue::create([
            'item_id' => $item->id,
            'issued_to_user_id' => $requester->id,
            'department_id' => null,
            'quantity' => 2,
            'status' => 'pending',
        ]);

        $custodian = User::factory()->create(['role' => 'property_custodian']);
        $this->actingAs($custodian);

        $show = $this->get('/inventory/issues/'.$issue->id);
        $show->assertStatus(200);

        $edit = $this->put('/inventory/issues/'.$issue->id, [
            'quantity' => 5,
            'issued_to_user_id' => $requester->id,
        ]);
        $edit->assertStatus(405);
        $this->assertEquals(2, $issue->fresh()->quantity);

        $delete = $this->delete('/inventory/issues/'.$issue->id);
        $delete->assertStatus(405);
        $this->assertDatabaseHas('material_issues', ['id' => $issue->id]);

        $otherIssue = MaterialIssue::create([
            'item_id' => $item->id,
            'issued_to_user_id' => $requester->id,
            'department_id' => null,
            'quantity' => 1,
            'status' => 'pending',
        ]);

        $reject = $this->post('/inventory/issues/'.$otherIssue->id.'/reject', ['notes' => 'Not approved']);
        $reject->assertStatus(200);
        $this->assertEquals('rejected', $otherIssue->fresh()->status);
    }

    public function test_material_issue_request_creates_notification_for_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 10]);

        $this->actingAs($teacher)
            ->post('/inventory/issues', [
                'item_id' => $item->id,
                'quantity' => 2,
                'purpose' => 'For classroom use',
            ])
            ->assertStatus(201);

        $this->assertSame(1, $admin->fresh()->unreadNotifications()->count());
        $this->assertStringContainsString('requested', strtolower($admin->fresh()->unreadNotifications()->first()->data['message']));
    }

    public function test_approval_creates_database_notification_for_requester(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::factory()->create(['quantity' => 10]);

        $this->actingAs($teacher)
            ->post('/inventory/issues', [
                'item_id' => $item->id,
                'quantity' => 2,
                'purpose' => 'For classroom use',
            ]);

        $this->actingAs($admin)
            ->post('/inventory/issues/'.MaterialIssue::latest()->first()->id.'/approve')
            ->assertStatus(200);

        $this->assertSame(1, $teacher->fresh()->unreadNotifications()->count());
        $this->assertStringContainsString('approved', strtolower($teacher->fresh()->unreadNotifications()->first()->data['message']));
    }

    public function test_user_can_view_notification_history_and_mark_notifications_read(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create();
        $issue = MaterialIssue::factory()->create([
            'item_id' => $item->id,
            'issued_to_user_id' => $teacher->id,
        ]);
        $teacher->notify(new IssueApprovedNotification($issue));

        $this->actingAs($teacher)
            ->getJson('/inventory/notifications')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.read', false);

        $notificationId = $teacher->fresh()->notifications()->first()->id;
        $this->actingAs($teacher)
            ->postJson('/inventory/notifications/'.$notificationId.'/read')
            ->assertOk();

        $this->assertSame(0, $teacher->fresh()->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_read(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create();
        $issue = MaterialIssue::factory()->create([
            'item_id' => $item->id,
            'issued_to_user_id' => $teacher->id,
        ]);
        $teacher->notify(new IssueApprovedNotification($issue));
        $teacher->notify(new IssueApprovedNotification($issue));

        $this->actingAs($teacher)
            ->postJson('/inventory/notifications/read-all')
            ->assertOk();

        $this->assertSame(0, $teacher->fresh()->unreadNotifications()->count());
    }
}
