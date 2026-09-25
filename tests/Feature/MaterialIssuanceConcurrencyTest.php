<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\MaterialIssue;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\IssueApprovedNotification;

class MaterialIssuanceConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_pending_issues_second_fails_when_stock_insufficient()
    {
        Notification::fake();

        $item = Item::factory()->create(['quantity' => 5]);

        // create two pending requests totaling more than stock
        $this->actingAs(User::factory()->create());
        $to1 = User::factory()->create();
        $to2 = User::factory()->create();
        $r1 = $this->post('/inventory/issues', ['item_id' => $item->id, 'quantity' => 3, 'issued_to_user_id' => $to1->id, 'purpose' => 'For classroom use']);
        $r1->assertStatus(201);
        $id1 = $r1->json('id');

        $r2 = $this->post('/inventory/issues', ['item_id' => $item->id, 'quantity' => 3, 'issued_to_user_id' => $to2->id, 'purpose' => 'For classroom use']);
        $r2->assertStatus(201);
        $id2 = $r2->json('id');

        // approve first
        $approver = User::factory()->create(['role' => 'admin']);
        $this->actingAs($approver);
        $a1 = $this->post('/inventory/issues/'.$id1.'/approve');
        $a1->assertStatus(200);

        // approving second should fail due to insufficient stock
        $a2 = $this->post('/inventory/issues/'.$id2.'/approve');
        $a2->assertStatus(422);

        // notification sent for first approval
        Notification::assertSentTo([$to1], IssueApprovedNotification::class);
    }
}
