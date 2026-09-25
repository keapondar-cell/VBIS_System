<?php

namespace Tests\Feature;

use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Module3AccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_module_3_views(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $this->get('/inventory/status')->assertOk();
        $this->get('/inventory/low-stock')->assertOk();
        $this->get('/inventory/reports')->assertOk();
        $this->get('/inventory/audit-trail')->assertOk();
    }

    public function test_admin_users_page_has_working_create_user_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSee(route('register'));
    }

    public function test_property_custodian_can_access_monitoring_and_qr_views(): void
    {
        $user = User::factory()->create(['role' => 'property_custodian']);
        $this->actingAs($user);

        $this->get('/inventory/status')->assertOk();
        $this->get('/inventory/qr/generate')->assertOk();
        $this->get('/inventory/qr/scan')->assertOk();
        $this->get('/inventory/transactions/history')->assertOk();
    }

    public function test_teacher_has_restricted_access_to_monitoring_and_audit_views(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $this->actingAs($user);

        $this->get('/inventory/status')->assertOk();
        $this->get('/inventory/low-stock')->assertOk();
        $this->get('/inventory/audit-trail')->assertForbidden();
        $this->get('/inventory/reports')->assertForbidden();
    }

    public function test_low_stock_page_shows_actual_inventory_values(): void
    {
        Item::factory()->create(['name' => 'Bond Paper', 'category' => 'Office Supplies', 'quantity' => 3, 'location' => 'Supply Room', 'status' => 'low stock']);
        Item::factory()->create(['name' => 'Marker', 'category' => 'Office Supplies', 'quantity' => 12, 'location' => 'Cabinet 1', 'status' => 'available']);

        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $response = $this->get('/inventory/low-stock');
        $response->assertOk();
        $response->assertSee('Bond Paper');
        $response->assertSee('3');
    }

    public function test_borrowing_report_uses_inventory_transaction_records(): void
    {
        $user = User::factory()->create(['name' => 'Teacher Borrower', 'role' => 'teacher']);
        $item = Item::factory()->create(['name' => 'Laptop Charger', 'sku' => 'ITM-1001', 'quantity' => 8]);

        InventoryTransaction::factory()->create([
            'item_id' => $item->id,
            'user_id' => $user->id,
            'transaction_type' => 'borrow',
            'quantity' => 2,
            'notes' => 'Borrowed for classroom use',
        ]);

        InventoryTransaction::factory()->create([
            'item_id' => $item->id,
            'user_id' => $user->id,
            'transaction_type' => 'return',
            'quantity' => 2,
            'notes' => 'Returned after use',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/inventory/reports/borrowing');
        $response->assertOk();
        $response->assertSee('Teacher Borrower');
        $response->assertSee('Laptop Charger');
    }
}
