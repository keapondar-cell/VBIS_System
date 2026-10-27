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

    public function test_teacher_can_scan_an_item_qr_to_submit_a_borrow_request(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 5]);

        $this->actingAs($teacher)
            ->get('/inventory/items/list')
            ->assertOk()
            ->assertSee('Scan QR to Borrow')
            ->assertSee('action=borrow')
            ->assertSee('Scan QR to Return')
            ->assertSee('action=return');

        $this->get('/inventory/qr/scan?action=borrow')
            ->assertOk()
            ->assertSee('scanAction = "borrow"', false);

        $this->get('/inventory/qr/item/'.$item->id.'?action=borrow')
            ->assertOk()
            ->assertSee('Borrow '.$item->name)
            ->assertSee('name="expected_return_at"', false)
            ->assertSee('required', false)
            ->assertSee('name="quantity"', false)
            ->assertSee('Borrow request submitted from item QR code')
            ->assertSee('The item is borrowed once an administrator or property custodian approves it.')
            ->assertSee('Submit Borrow Request');

        $returnDate = now()->addDays(2)->toDateString();
        $this->postJson('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'borrow',
            'quantity' => 2,
            'purpose' => 'Borrow request submitted from item QR code',
            'expected_return_at' => $returnDate,
        ])->assertCreated()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('expected_return_at', $returnDate.'T00:00:00.000000Z');

        $this->assertSame(5, $item->fresh()->quantity);
        $this->assertDatabaseHas('inventory_transactions', [
            'item_id' => $item->id,
            'user_id' => $teacher->id,
            'transaction_type' => 'borrow',
            'expected_return_at' => $returnDate.' 00:00:00',
            'status' => 'pending',
        ]);
    }

    public function test_teacher_can_scan_an_item_qr_to_return_an_active_borrow(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 7]);
        InventoryTransaction::factory()->create([
            'item_id' => $item->id,
            'user_id' => $teacher->id,
            'transaction_type' => 'borrow',
            'quantity' => 3,
            'status' => 'approved',
            'returned_at' => null,
        ]);

        $this->actingAs($teacher)
            ->get('/inventory/qr/item/'.$item->id.'?action=return')
            ->assertOk()
            ->assertSee('Return '.$item->name)
            ->assertSee('Confirm Return');

        $this->postJson('/inventory/transactions', [
            'item_id' => $item->id,
            'user_id' => $teacher->id,
            'transaction_type' => 'return',
            'quantity' => 3,
            'condition' => 'Good',
        ])->assertCreated();

        $this->assertSame(10, $item->fresh()->quantity);
        $this->assertDatabaseHas('inventory_transactions', [
            'item_id' => $item->id,
            'user_id' => $teacher->id,
            'transaction_type' => 'return',
            'status' => 'returned',
        ]);
    }

    public function test_item_qr_codes_include_item_info_borrow_and_return_options(): void
    {
        $custodian = User::factory()->create(['role' => 'property_custodian']);
        $item = Item::factory()->create(['name' => 'Lab Beaker']);

        $this->actingAs($custodian)
            ->get('/inventory/qr/generate')
            ->assertOk()
            ->assertSee('Item Info')
            ->assertSee('Scan to Borrow')
            ->assertSee('Scan to Return')
            ->assertSee('action=borrow')
            ->assertSee('action=return');

        $itemInfoQr = $this->get('/inventory/items/'.$item->id.'/qr-image');
        $borrowQr = $this->get('/inventory/items/'.$item->id.'/qr-image?action=borrow');
        $returnQr = $this->get('/inventory/items/'.$item->id.'/qr-image?action=return');

        $itemInfoQr->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $borrowQr->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $returnQr->assertOk()->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertNotSame($itemInfoQr->getContent(), $borrowQr->getContent());
        $this->assertNotSame($borrowQr->getContent(), $returnQr->getContent());
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
