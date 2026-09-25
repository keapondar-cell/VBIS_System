<?php

namespace Tests\Feature;

use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryStockIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_adjustment_does_not_change_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::factory()->create(['quantity' => 100]);

        $this->actingAs($admin)
            ->putJson('/inventory/items/'.$item->id, [
                'name' => 'Updated item',
                'quantity' => 50,
            ])
            ->assertStatus(202);

        $this->assertSame(100, $item->fresh()->quantity);
    }

    public function test_approved_borrow_deducts_stock_once_and_return_restores_it(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $custodian = User::factory()->create(['role' => 'property_custodian']);
        $item = Item::factory()->create(['quantity' => 10]);

        $borrow = $this->actingAs($teacher)->postJson('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'borrow',
            'quantity' => 3,
            'purpose' => 'For classroom demonstration',
            'expected_return_at' => now()->addDay()->format('Y-m-d'),
        ]);

        $borrow->assertStatus(201);
        $this->assertSame('pending', $borrow->json('status'));
        $this->assertSame(10, $item->fresh()->quantity);

        $this->actingAs($custodian)
            ->post('/inventory/transactions/'.$borrow->json('id').'/approve')
            ->assertStatus(200);

        $this->assertSame(7, $item->fresh()->quantity);

        $return = $this->actingAs($teacher)->postJson('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'return',
            'quantity' => 3,
            'condition' => 'Good',
        ]);

        $return->assertStatus(201);
        $this->assertSame(10, $item->fresh()->quantity);
        $this->assertSame(1, InventoryTransaction::where('item_id', $item->id)->where('transaction_type', 'return')->count());
    }

    public function test_duplicate_return_is_prevented_for_same_borrow(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $custodian = User::factory()->create(['role' => 'property_custodian']);
        $item = Item::factory()->create(['quantity' => 10]);

        $borrow = $this->actingAs($teacher)->postJson('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'borrow',
            'quantity' => 2,
            'purpose' => 'For classroom activity',
            'expected_return_at' => now()->addDay()->format('Y-m-d'),
        ]);

        $this->actingAs($custodian)->post('/inventory/transactions/'.$borrow->json('id').'/approve');

        $first = $this->actingAs($teacher)->postJson('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'return',
            'quantity' => 2,
            'condition' => 'Good',
        ]);
        $first->assertStatus(201);

        $second = $this->actingAs($teacher)->postJson('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'return',
            'quantity' => 2,
            'condition' => 'Good',
        ]);

        $second->assertStatus(422);
    }
}
