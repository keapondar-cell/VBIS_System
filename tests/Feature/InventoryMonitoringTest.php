<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Item;
use App\Models\InventoryTransaction;
use App\Models\User;

class InventoryMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_export_and_qr()
    {
        Item::factory()->count(5)->create();

        $this->actingAs(User::factory()->create());

        $res = $this->get('/inventory/items/export');
        $res->assertStatus(200);
        $res->assertHeader('Content-Type');

        $item = Item::first();
        $qr = $this->get('/inventory/items/'.$item->id.'/qr-image');
        $qr->assertStatus(200)->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $qr->getContent());
    }

    public function test_realtime_data_is_limited_and_prioritizes_the_lowest_stock_items()
    {
        $this->actingAs(User::factory()->create());

        $itemOrder = [3, 0, 5, 1, 4, 2, 6, 0, 1, 2, 5, 0];
        foreach ($itemOrder as $index => $quantity) {
            Item::factory()->create([
                'name' => 'Item '.$index,
                'quantity' => $quantity,
            ]);
        }

        foreach (range(1, 12) as $index) {
            InventoryTransaction::factory()->create([
                'item_id' => $index,
                'user_id' => 1,
                'quantity' => $index,
                'created_at' => now()->subMinutes(12 - $index),
            ]);
        }

        $response = $this->get('/inventory/dashboard/realtime');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'recent_transactions')
            ->assertJsonCount(10, 'low_stock_items');

        $latest = $response->json('low_stock_items');
        $quantities = array_map(fn ($item) => (int) $item['quantity'], $latest);
        $expected = $quantities;
        sort($expected);

        $this->assertSame(0, $latest[0]['quantity']);
        $this->assertSame($expected, $quantities);
    }

    public function test_items_receive_unique_qr_codes_and_department_summary_is_available(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $item = Item::factory()->create(['qr_code' => null]);
        $item->refresh();

        $this->assertNotEmpty($item->qr_code);

        InventoryTransaction::factory()->create([
            'item_id' => $item->id,
            'department_id' => 12,
            'quantity' => 4,
        ]);

        $response = $this->actingAs($user)->get('/inventory/dashboard/department-summary');

        $response->assertOk()->assertJsonPath('departments.0.department_id', 12);
    }

    public function test_dashboard_summary_supports_paginated_low_stock_items(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (range(1, 12) as $index) {
            Item::factory()->create([
                'name' => 'Low stock item '.$index,
                'quantity' => min($index, 5),
            ]);
        }

        $response = $this->get('/inventory/dashboard/summary?page=2&per_page=10');

        $response->assertOk()
            ->assertJsonPath('low_stock_pagination.current_page', 2)
            ->assertJsonPath('low_stock_pagination.last_page', 2)
            ->assertJsonCount(2, 'low_stock_items');
    }

    public function test_borrow_qr_record_displays_item_and_borrower_details(): void
    {
        $user = User::factory()->create(['role' => 'teacher', 'name' => 'Borrower Name']);
        $item = Item::factory()->create(['name' => 'Laptop', 'quantity' => 10]);
        $transaction = InventoryTransaction::factory()->create([
            'item_id' => $item->id,
            'user_id' => $user->id,
            'transaction_type' => 'borrow',
            'quantity' => 1,
            'notes' => 'Borrow for class demo',
        ]);

        $this->actingAs($user)
            ->get('/inventory/qr/borrow/'.$transaction->id)
            ->assertOk()
            ->assertSee('Laptop')
            ->assertSee('Borrower Name');
    }

    public function test_inventory_reports_can_be_exported_as_pdf_and_excel_compatible_files(): void
    {
        Item::factory()->create();
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get('/inventory/reports/export?type=inventory&format=pdf')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($user)
            ->get('/inventory/reports/export?type=inventory&format=excel')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_inventory_reports_can_be_exported_as_csv(): void
    {
        Item::factory()->create();
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get('/inventory/reports/export?type=inventory&format=csv')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
