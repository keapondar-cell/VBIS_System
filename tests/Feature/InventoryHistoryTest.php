<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\User;

class InventoryHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_transactions_and_export()
    {
        InventoryTransaction::factory()->count(3)->create();

        $this->actingAs(User::factory()->create());

        $res = $this->get('/inventory/transactions');
        $res->assertStatus(200);

        $csv = $this->get('/inventory/transactions?export=csv');
        $csv->assertStatus(200);
        $csv->assertHeader('Content-Type');

        $pdf = $this->get('/inventory/transactions?export=pdf');
        $pdf->assertOk();
        $pdf->assertHeader('Content-Type', 'application/pdf');
        $pdf->assertHeader('Content-Disposition');
    }

    public function test_return_transaction_links_to_open_borrow_and_closes_it(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 5]);
        $expectedReturn = now()->addDay()->format('Y-m-d H:i:s');

        $borrow = $this->actingAs($teacher)->post('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'borrow',
            'quantity' => 2,
            'purpose' => 'For classroom use',
            'expected_return_at' => $expectedReturn,
        ]);

        $borrow->assertCreated();
        $borrowId = $borrow->json('id');

        $return = $this->actingAs($teacher)->post('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'return',
            'quantity' => 2,
        ]);

        $return->assertCreated();
        $this->assertSame($borrowId, $return->json('related_transaction_id'));
        $this->assertNotNull(InventoryTransaction::find($borrowId)->returned_at);
    }

    public function test_teacher_borrow_requires_admin_approval_before_inventory_changes(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::factory()->create(['quantity' => 5]);

        $response = $this->actingAs($teacher)->post('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'borrow',
            'quantity' => 2,
            'purpose' => 'For classroom demo',
            'expected_return_at' => now()->addDay()->format('Y-m-d'),
        ]);

        $response->assertCreated();
        $response->assertJsonPath('status', 'pending');
        $item->refresh();
        $this->assertSame(5, $item->quantity);

        $this->actingAs($admin)
            ->post('/inventory/transactions/'.$response->json('id').'/approve')
            ->assertOk();

        $item->refresh();
        $this->assertSame(3, $item->quantity);
    }

    public function test_inventory_history_records_cannot_be_changed_or_deleted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $transaction = InventoryTransaction::factory()->create();

        $this->actingAs($admin)->put('/inventory/transactions/'.$transaction->id, [
            'item_id' => $transaction->item_id,
            'transaction_type' => $transaction->transaction_type,
            'quantity' => 1,
        ])->assertStatus(405);

        $this->actingAs($admin)->delete('/inventory/transactions/'.$transaction->id)
            ->assertStatus(405);
    }

    public function test_teacher_can_update_condition_on_owned_issue_borrow_or_return_transaction(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $transaction = InventoryTransaction::factory()->create([
            'user_id' => $teacher->id,
            'transaction_type' => 'borrow',
            'condition' => 'In good condition',
        ]);

        $this->actingAs($teacher)
            ->patchJson('/inventory/transactions/'.$transaction->id.'/condition', [
                'condition' => 'Damaged',
            ])
            ->assertOk()
            ->assertJsonPath('condition', 'Damaged');

        $this->assertSame('Damaged', $transaction->fresh()->condition);
    }

    public function test_only_the_owning_teacher_can_update_transaction_condition(): void
    {
        $owner = User::factory()->create(['role' => 'teacher']);
        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->create(['role' => 'admin']);
        $transaction = InventoryTransaction::factory()->create([
            'user_id' => $owner->id,
            'transaction_type' => 'issue',
        ]);

        $this->actingAs($otherTeacher)
            ->patchJson('/inventory/transactions/'.$transaction->id.'/condition', ['condition' => 'Damaged'])
            ->assertForbidden();

        $this->actingAs($admin)
            ->patchJson('/inventory/transactions/'.$transaction->id.'/condition', ['condition' => 'Damaged'])
            ->assertForbidden();

        $this->assertNull($transaction->fresh()->condition);
    }
}
