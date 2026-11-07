<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\Department;
use App\Models\MaterialIssue;
use App\Models\InventoryTransaction;
use App\Models\User;
use App\Notifications\IssueApprovedNotification;
use Carbon\CarbonImmutable;

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
                'expected_return_at' => now('Asia/Manila')->addDays(3)->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('quantity', 3);

        $this->assertSame('Updated classroom purpose', $issue->fresh()->notes);
        $this->assertNotNull($issue->fresh()->expected_return_at);
    }

    public function test_teacher_cannot_edit_request_to_an_out_of_stock_item(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create();
        $outOfStockItem = Item::factory()->create(['quantity' => 0]);
        $issue = MaterialIssue::factory()->create([
            'item_id' => $item->id,
            'issued_to_user_id' => $teacher->id,
            'status' => 'pending',
        ]);

        $this->actingAs($teacher)
            ->patchJson('/inventory/issues/'.$issue->id, [
                'item_id' => $outOfStockItem->id,
                'quantity' => 1,
                'purpose' => 'Updated classroom purpose',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('error', 'This item is out of stock and cannot be requested.');

        $this->assertSame($item->id, $issue->fresh()->item_id);
    }

    public function test_material_issue_page_is_separate_from_borrowing(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        Item::factory()->create(['name' => 'Consumable Materials', 'category' => 'Consumable']);

        $this->actingAs($teacher)
            ->get('/inventory/issues/list')
            ->assertOk()
            ->assertSee('Material Issue')
            ->assertDontSee('option value="borrow"', false)
            ->assertSee('Computer Lab')
            ->assertSee('Science Lab')
            ->assertSee('Library')
            ->assertSee('JHS Department')
            ->assertSee('SHS Department')
            ->assertSee('Requested date & time')
            ->assertSee('Expected Return Date')
            ->assertSee('Out of stock')
            ->assertSee("selectedItemClassification.trim().toLowerCase() === 'consumable'", false)
            ->assertSee('type="datetime-local"', false);

        $this->getJson('/inventory/items/search?q=Consumable')
            ->assertOk()
            ->assertJsonPath('0.category', 'Consumable');
    }

    public function test_property_custodian_can_select_a_user_for_a_material_issue_request(): void
    {
        $custodian = User::factory()->create(['role' => 'property_custodian', 'name' => 'Custodian']);
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Selected Teacher']);
        $inactiveUser = User::factory()->create([
            'role' => 'teacher',
            'name' => 'Inactive Teacher Account',
            'is_active' => false,
        ]);

        $this->actingAs($custodian)
            ->get(route('inventory.issues.list'))
            ->assertOk()
            ->assertSee('Issue to user')
            ->assertSee('value="'.$teacher->id.'"', false)
            ->assertSee($teacher->name)
            ->assertDontSee($inactiveUser->name);

        $item = Item::factory()->create();
        $this->postJson(route('inventory.issues.store'), [
            'item_id' => $item->id,
            'quantity' => 1,
            'purpose' => 'Requesting on behalf of teacher',
            'issued_to_user_id' => $teacher->id,
        ])
            ->assertCreated()
            ->assertJsonPath('issued_to_user_id', $teacher->id);
    }

    public function test_admin_and_teacher_material_issue_forms_default_to_only_their_own_account(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Signed In Teacher']);
        $otherUser = User::factory()->create(['role' => 'teacher', 'name' => 'Other Teacher']);

        $response = $this->actingAs($teacher)
            ->get(route('inventory.issues.list'));
        $response
            ->assertOk()
            ->assertSee('value="'.$teacher->id.'" selected', false)
            ->assertSee($teacher->name);

        preg_match('/<select id="ci_user"[^>]*>(.*?)<\/select>/s', $response->getContent(), $userSelect);
        $this->assertNotEmpty($userSelect);
        $this->assertStringContainsString($teacher->name, $userSelect[1]);
        $this->assertStringNotContainsString($otherUser->name, $userSelect[1]);

        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Signed In Admin']);
        $adminResponse = $this->actingAs($admin)->get(route('inventory.issues.list'));
        $adminResponse->assertOk()
            ->assertSee('value="'.$admin->id.'" selected', false);
        preg_match('/<select id="ci_user"[^>]*>(.*?)<\/select>/s', $adminResponse->getContent(), $adminSelect);
        $this->assertNotEmpty($adminSelect);
        $this->assertStringContainsString($admin->name, $adminSelect[1]);
        $this->assertStringNotContainsString($otherUser->name, $adminSelect[1]);

        $item = Item::factory()->create();
        $this->actingAs($teacher);
        $this->postJson(route('inventory.issues.store'), [
            'item_id' => $item->id,
            'quantity' => 1,
            'purpose' => 'For classroom supplies',
            'issued_to_user_id' => $otherUser->id,
        ])
            ->assertCreated()
            ->assertJsonPath('issued_to_user_id', $teacher->id);
    }

    public function test_teacher_cannot_request_material_when_item_is_out_of_stock(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 0]);

        $this->actingAs($teacher)
            ->postJson('/inventory/issues', [
                'item_id' => $item->id,
                'quantity' => 1,
                'purpose' => 'For classroom activity',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('error', 'This item is out of stock and cannot be requested.');

        $this->assertDatabaseMissing('material_issues', [
            'item_id' => $item->id,
            'issued_to_user_id' => $teacher->id,
        ]);
    }

    public function test_admin_can_transfer_an_approved_material_issue_to_another_user_without_changing_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $previousUser = User::factory()->create(['role' => 'teacher']);
        $newUser = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 6]);
        $issue = MaterialIssue::factory()->create([
            'item_id' => $item->id,
            'issued_to_user_id' => $previousUser->id,
            'quantity' => 2,
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->postJson(route('inventory.issues.transfer', $issue), [
                'new_user_id' => $newUser->id,
                'notes' => 'Original assignee is retiring',
            ])
            ->assertOk()
            ->assertJsonPath('issued_to_user_id', $newUser->id);

        $this->assertSame(6, $item->fresh()->quantity);
        $this->assertSame($newUser->id, $issue->fresh()->issued_to_user_id);
        $this->assertDatabaseHas('inventory_transactions', [
            'item_id' => $item->id,
            'user_id' => $previousUser->id,
            'affected_user_id' => $newUser->id,
            'performed_by' => $admin->id,
            'transaction_type' => 'transfer',
            'quantity' => 2,
            'quantity_change' => 0,
            'old_quantity' => 6,
            'new_quantity' => 6,
            'reason' => 'Material issuance transfer',
            'remarks' => 'Original assignee is retiring',
        ]);
    }

    public function test_material_issuance_actions_offer_transfer_for_approved_items(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $holder = User::factory()->create(['role' => 'teacher']);
        $recipient = User::factory()->create(['role' => 'teacher', 'name' => 'New Assignee']);
        MaterialIssue::factory()->create([
            'item_id' => Item::factory(),
            'issued_to_user_id' => $holder->id,
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get('/inventory/issues/list')
            ->assertOk()
            ->assertSee('Transfer Issued Item')
            ->assertSee('Transfer to:')
            ->assertSee($recipient->name)
            ->assertSee('/inventory/issues/${issueId}/transfer', false);
    }

    public function test_only_admins_and_property_custodians_can_transfer_material_issues(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $oldUser = User::factory()->create(['role' => 'teacher']);
        $newUser = User::factory()->create(['role' => 'teacher']);
        $issue = MaterialIssue::factory()->create([
            'issued_to_user_id' => $oldUser->id,
            'status' => 'approved',
        ]);

        $this->actingAs($teacher)
            ->postJson(route('inventory.issues.transfer', $issue), ['new_user_id' => $newUser->id])
            ->assertForbidden();

        $this->assertSame($oldUser->id, $issue->fresh()->issued_to_user_id);
        $this->assertDatabaseMissing('inventory_transactions', [
            'transaction_type' => 'transfer',
            'item_id' => $issue->item_id,
        ]);
    }

    public function test_pending_material_issue_cannot_be_transferred(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $oldUser = User::factory()->create(['role' => 'teacher']);
        $newUser = User::factory()->create(['role' => 'teacher']);
        $issue = MaterialIssue::factory()->create(['issued_to_user_id' => $oldUser->id]);

        $this->actingAs($admin)
            ->postJson(route('inventory.issues.transfer', $issue), ['new_user_id' => $newUser->id])
            ->assertUnprocessable()
            ->assertJsonPath('error', 'Only approved material issuances can be transferred.');

        $this->assertSame($oldUser->id, $issue->fresh()->issued_to_user_id);
    }

    public function test_consumable_material_issue_does_not_save_or_copy_an_expected_return_date(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::factory()->create(['category' => ' consumable ']);

        $response = $this->actingAs($teacher)->postJson('/inventory/issues', [
            'item_id' => $item->id,
            'quantity' => 1,
            'purpose' => 'Consumable classroom supplies',
            'expected_return_at' => 'not-a-valid-date',
        ])->assertCreated();

        $issueId = $response->json('id');
        $this->assertDatabaseHas('material_issues', [
            'id' => $issueId,
            'expected_return_at' => null,
        ]);

        $this->actingAs($admin)
            ->postJson('/inventory/issues/'.$issueId.'/approve')
            ->assertOk();

        $this->assertDatabaseHas('inventory_transactions', [
            'transaction_type' => 'issue',
            'expected_return_at' => null,
        ]);
    }

    public function test_editing_consumable_material_issue_clears_expected_return_date(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['category' => 'Consumable']);
        $issue = MaterialIssue::factory()->create([
            'item_id' => $item->id,
            'issued_to_user_id' => $teacher->id,
            'expected_return_at' => now()->addDays(2),
        ]);

        $this->actingAs($teacher)
            ->patchJson('/inventory/issues/'.$issue->id, [
                'item_id' => $item->id,
                'quantity' => $issue->quantity,
                'purpose' => 'Update consumable supply request',
                'expected_return_at' => now()->addDays(3)->toDateString(),
            ])
            ->assertOk()
            ->assertJsonPath('expected_return_at', null);

        $this->assertNull($issue->fresh()->expected_return_at);
    }

    public function test_teacher_can_request_item_for_department_and_requested_time(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create();
        $department = Department::where('name', 'Computer Lab')->firstOrFail();
        $requestedFor = now()->addDays(2)->setTime(14, 30)->format('Y-m-d\TH:i');
        $expectedReturn = now('Asia/Manila')->addDays(4)->toDateString();

        $response = $this->actingAs($teacher)->postJson('/inventory/issues', [
            'item_id' => $item->id,
            'quantity' => 1,
            'purpose' => 'For scheduled class use',
            'department_id' => $department->id,
            'requested_for_at' => $requestedFor,
            'expected_return_at' => $expectedReturn,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('material_issues', [
            'id' => $response->json('id'),
            'department_id' => $department->id,
            'requested_for_at' => CarbonImmutable::parse($requestedFor, 'Asia/Manila')->utc()->format('Y-m-d H:i:s'),
            'expected_return_at' => $expectedReturn.' 00:00:00',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->postJson('/inventory/issues/'.$response->json('id').'/approve')
            ->assertOk();

        $this->assertDatabaseHas('inventory_transactions', [
            'transaction_type' => 'issue',
            'expected_return_at' => $expectedReturn.' 00:00:00',
        ]);
    }

    public function test_teacher_can_set_department_and_requested_time_for_borrow(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create();
        $department = Department::where('name', 'Library')->firstOrFail();
        $requestedFor = now()->addDays(2)->setTime(9, 15)->format('Y-m-d\TH:i');

        $response = $this->actingAs($teacher)->postJson('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'borrow',
            'quantity' => 1,
            'purpose' => 'For scheduled reading activity',
            'department_id' => $department->id,
            'requested_for_at' => $requestedFor,
            'expected_return_at' => now()->addDays(3)->toDateString(),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $response->json('id'),
            'department_id' => $department->id,
            'requested_for_at' => CarbonImmutable::parse($requestedFor, 'Asia/Manila')->utc()->format('Y-m-d H:i:s'),
        ]);
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
