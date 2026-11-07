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
            ->assertSee(route('register'))
            ->assertSee('aria-label="Export users as CSV"', false)
            ->assertSee('aria-label="Export users as PDF"', false)
            ->assertSee('viewBox="0 0 48 48"', false)
            ->assertDontSee('>Export CSV</a>', false)
            ->assertDontSee('>Export PDF</a>', false);
    }

    public function test_reports_menu_exports_use_csv_excel_and_pdf_icons(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('inventory.reports.menu'))
            ->assertOk()
            ->assertSee('aria-label="Export departments as PDF"', false)
            ->assertSee('aria-label="Export departments as Excel"', false)
            ->assertSee('aria-label="Export transactions as PDF"', false)
            ->assertSee('aria-label="Export transactions as Excel"', false)
            ->assertSee('format=excel')
            ->assertSee('format=pdf');
    }

    public function test_sidebar_separates_borrowing_material_issuance_and_transactions(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->get(route('inventory.issues.list'))
            ->assertOk()
            ->assertSee('Borrowing')
            ->assertSee('Material Issuance')
            ->assertSee('Transactions')
            ->assertSee('Consumables')
            ->assertDontSee('>Requests</a>', false)
            ->assertSee(route('inventory.borrowing.list'), false)
            ->assertSee(route('inventory.consumables.list'), false)
            ->assertSee(route('inventory.issues.list'), false)
            ->assertSee(route('inventory.transactions.list'), false)
            ->assertDontSee(route('inventory.transactions.history'), false);

        $this->get(route('inventory.borrowing.list'))
            ->assertOk()
            ->assertSee('Borrowing Transactions')
            ->assertSee('Request to Borrow')
            ->assertSee('name="transaction_type" type="hidden" value="borrow"', false)
            ->assertSee('transaction_type: borrowingOnly ? \'borrow\'', false)
            ->assertSee('class="transaction-results borrowing-results"', false)
            ->assertSee('Borrower</th><th>Qty</th><th>Purpose</th>', false)
            ->assertSee('class="borrow-status', false);
    }

    public function test_material_issuance_page_does_not_offer_borrowing_as_a_request_type(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->get(route('inventory.issues.list'))
            ->assertOk()
            ->assertSee('Submit and track material issuance requests')
            ->assertDontSee('ci_request_type')
            ->assertDontSee('option value="borrow"', false);
    }

    public function test_admin_can_filter_users_by_search_role_and_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matchingUser = User::factory()->create([
            'name' => 'Matching Teacher',
            'email' => 'matching@example.com',
            'role' => 'teacher',
            'is_active' => true,
        ]);
        User::factory()->create([
            'name' => 'Matching Custodian',
            'email' => 'custodian@example.com',
            'role' => 'property_custodian',
            'is_active' => true,
        ]);
        User::factory()->create([
            'name' => 'Pending Teacher',
            'email' => 'pending@example.com',
            'role' => 'teacher',
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users', [
                'search' => 'matching',
                'role' => 'teacher',
                'status' => 'active',
            ]))
            ->assertOk()
            ->assertSeeText($matchingUser->name)
            ->assertDontSeeText('Matching Custodian')
            ->assertDontSeeText('Pending Teacher')
            ->assertSee('value="teacher" selected', false)
            ->assertSee('value="active" selected', false);
    }

    public function test_property_custodian_can_access_monitoring_and_generate_qr_views(): void
    {
        $user = User::factory()->create(['role' => 'property_custodian']);
        $this->actingAs($user);

        $this->get('/inventory/status')->assertOk();
        $this->get('/inventory/qr/generate')
            ->assertOk()
            ->assertSee('Generate QR')
            ->assertSee('Print QR Codes')
            ->assertSee('window.print()', false)
            ->assertSee('@media print', false);
        $this->get('/inventory/qr/scan')->assertNotFound();
        $this->get('/inventory/transactions/history')->assertOk();
    }

    public function test_teacher_can_scan_an_item_qr_to_submit_a_borrow_request(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 5]);

        $this->actingAs($teacher)
            ->get('/inventory/items/list')
            ->assertOk()
            ->assertDontSee('Scan QR to Borrow')
            ->assertDontSee('Scan QR to Return');

        $this->get('/inventory/qr/item/'.$item->id.'?action=borrow')
            ->assertOk()
            ->assertSee('Borrow '.$item->name)
            ->assertDontSee('Item Details')
            ->assertDontSee('QR Identifier')
            ->assertDontSee('Recent History')
            ->assertDontSee('Scan QR to Borrow')
            ->assertDontSee('Scan QR to Return')
            ->assertDontSee('Scan Again')
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
            ->assertDontSee('Item Details')
            ->assertDontSee('Recent History')
            ->assertDontSee('Scan Again')
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
