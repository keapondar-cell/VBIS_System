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

    public function test_inventory_items_can_be_filtered_by_search_classification_location_and_stock_level(): void
    {
        Item::factory()->create([
            'name' => 'Filtered Gauze',
            'sku' => 'FILTERED-GAUZE',
            'category' => 'Consumable',
            'location' => 'Science Lab',
            'quantity' => 4,
        ]);
        Item::factory()->create([
            'name' => 'Other Classification',
            'category' => 'Glassware',
            'location' => 'Science Lab',
            'quantity' => 4,
        ]);
        Item::factory()->create([
            'name' => 'Other Department',
            'category' => 'Consumable',
            'location' => 'Library',
            'quantity' => 4,
        ]);
        Item::factory()->create([
            'name' => 'Out of Stock Gauze',
            'category' => 'Consumable',
            'location' => 'Science Lab',
            'quantity' => 0,
        ]);

        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user)
            ->get('/inventory/items/list?'.http_build_query([
                'search' => 'Filtered',
                'classification' => 'Consumable',
                'location' => 'Science Lab',
                'stock_status' => 'low_stock',
            ]))
            ->assertOk()
            ->assertSee('Filtered Gauze')
            ->assertDontSee('Other Classification')
            ->assertDontSee('Other Department')
            ->assertDontSee('Out of Stock Gauze')
            ->assertSee('name="stock_status"', false)
            ->assertSee('value="low_stock" selected', false);
    }

    public function test_inventory_items_low_stock_link_filters_items_and_keeps_anchor_targets(): void
    {
        $lowStock = Item::factory()->create(['name' => 'Low Stock Supply', 'quantity' => 5]);
        Item::factory()->create(['name' => 'Healthy Supply', 'quantity' => 6]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/inventory/items/list?low_stock=1#item-'.$lowStock->id)
            ->assertOk()
            ->assertSee('Low Stock Supply')
            ->assertDontSee('Healthy Supply')
            ->assertSee('id="item-'.$lowStock->id.'"', false);
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

    public function test_item_creation_date_is_used_for_its_realtime_activity_date(): void
    {
        $user = User::factory()->create(['role' => 'property_custodian']);

        $this->actingAs($user)
            ->postJson('/inventory/items', [
                'name' => 'Backdated Item',
                'quantity' => 3,
                'created_at' => '2026-10-25T09:15',
            ])
            ->assertCreated();

        $this->get('/inventory/dashboard/realtime')
            ->assertOk()
            ->assertJsonPath('recent_transactions.0.transaction_type', 'receive')
            ->assertJsonPath('recent_transactions.0.item.name', 'Backdated Item')
            ->assertJsonPath('recent_transactions.0.created_at', '2026-10-25T01:15:00.000000Z');
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
            ->get('/inventory/reports')
            ->assertOk()
            ->assertSee('Victor Bernal Provincial High School')
            ->assertSee('data:image/jpeg;base64,', false);

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

    public function test_inventory_report_exports_apply_selected_filters(): void
    {
        Item::factory()->create([
            'name' => 'Filtered Consumable',
            'sku' => 'FILTERED-1',
            'category' => 'Consumable',
            'location' => 'Science Lab',
            'status' => 'available',
        ]);
        Item::factory()->create([
            'name' => 'Other Classification',
            'sku' => 'OTHER-1',
            'category' => 'Glassware',
            'location' => 'Science Lab',
            'status' => 'available',
        ]);
        Item::factory()->create([
            'name' => 'Other Location',
            'sku' => 'OTHER-2',
            'category' => 'Consumable',
            'location' => 'Library',
            'status' => 'available',
        ]);
        Item::factory()->create([
            'name' => 'Other Status',
            'sku' => 'OTHER-3',
            'category' => 'Consumable',
            'location' => 'Science Lab',
            'status' => 'out_of_stock',
        ]);

        $user = User::factory()->create(['role' => 'admin']);
        $filters = [
            'classification' => 'Consumable',
            'status' => 'available',
            'location' => 'Science Lab',
        ];

        $this->actingAs($user)
            ->get('/inventory/reports?'.http_build_query($filters))
            ->assertOk()
            ->assertSee('classification=Consumable')
            ->assertSee('location=Science%20Lab');

        $csv = $this->actingAs($user)
            ->get('/inventory/reports/export?'.http_build_query($filters + ['type' => 'inventory', 'format' => 'csv']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('FILTERED-1', $csv);
        $this->assertStringNotContainsString('OTHER-1', $csv);
        $this->assertStringNotContainsString('OTHER-2', $csv);
        $this->assertStringNotContainsString('OTHER-3', $csv);

        $this->actingAs($user)
            ->get('/inventory/reports/export?'.http_build_query($filters + ['type' => 'inventory', 'format' => 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
