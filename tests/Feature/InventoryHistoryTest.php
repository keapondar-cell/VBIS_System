<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\User;
use Carbon\CarbonImmutable;

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

    public function test_transaction_form_includes_expected_return_for_material_issues(): void
    {
        Item::factory()->create(['category' => 'Consumable']);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/inventory/transactions/list')
            ->assertOk()
            ->assertSee('Expected Return Date')
            ->assertSee('data-category="Consumable"', false)
            ->assertSee('expectedReturnDate')
            ->assertSee("payload.expected_return_at = expectedReturn.value || undefined;", false);
    }

    public function test_issue_transaction_can_be_downloaded_as_an_editable_memorandum_receipt(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create([
            'name' => 'Classroom Microscope',
            'sku' => 'MICRO-001',
            'category' => 'Science Equipment',
        ]);
        $transaction = InventoryTransaction::factory()->create([
            'item_id' => $item->id,
            'user_id' => $teacher->id,
            'transaction_type' => 'issue',
            'quantity' => 2,
            'expected_return_at' => '2026-11-05 00:00:00',
        ]);

        $response = $this->actingAs($teacher)
            ->get('/inventory/transactions/'.$transaction->id.'/memorandum-receipt')
            ->assertOk()
            ->assertDownload('memorandum_receipt_MR-'.now()->format('Y').'-'.$transaction->id.'.docx')
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $docx = $response->streamedContent();
        $this->assertStringStartsWith('PK', $docx);

        $path = tempnam(sys_get_temp_dir(), 'vbis-receipt-test-');
        $this->assertNotFalse($path);
        try {
            file_put_contents($path, $docx);
            $archive = new \ZipArchive();
            $this->assertSame(true, $archive->open($path));
            $documentXml = $archive->getFromName('word/document.xml');
            $archive->close();

            $this->assertIsString($documentXml);
            $this->assertStringContainsString('MEMORANDUM RECEIPT', $documentXml);
            $this->assertStringContainsString('Classroom Microscope', $documentXml);
            $this->assertStringContainsString('MICRO-001', $documentXml);
            $this->assertStringContainsString('Science Equipment', $documentXml);
            $this->assertStringContainsString('Expected return:', $documentXml);
        } finally {
            unlink($path);
        }
    }

    public function test_teacher_cannot_download_another_users_memorandum_receipt(): void
    {
        $owner = User::factory()->create(['role' => 'teacher']);
        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        $transaction = InventoryTransaction::factory()->create([
            'user_id' => $owner->id,
            'transaction_type' => 'issue',
        ]);

        $this->actingAs($otherTeacher)
            ->get('/inventory/transactions/'.$transaction->id.'/memorandum-receipt')
            ->assertForbidden();
    }

    public function test_issue_transaction_records_the_selected_issue_date(): void
    {
        $custodian = User::factory()->create(['role' => 'property_custodian']);
        $item = Item::factory()->create(['quantity' => 5]);

        $response = $this->actingAs($custodian)->postJson('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'issue',
            'quantity' => 1,
            'requested_for_at' => '2026-10-24',
            'expected_return_at' => now('Asia/Manila')->addDays(3)->toDateString(),
        ]);

        $response->assertCreated();
        $this->assertSame('2026-10-24', \Carbon\Carbon::parse($response->json('requested_for_at'))->format('Y-m-d'));
        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $response->json('id'),
            'requested_for_at' => '2026-10-24 00:00:00',
            'expected_return_at' => now('Asia/Manila')->addDays(3)->toDateString().' 00:00:00',
        ]);
    }

    public function test_consumable_issue_transaction_ignores_expected_return_date(): void
    {
        $custodian = User::factory()->create(['role' => 'property_custodian']);
        $item = Item::factory()->create([
            'quantity' => 5,
            'category' => 'Consumable',
        ]);

        $response = $this->actingAs($custodian)->postJson('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'issue',
            'quantity' => 1,
            'expected_return_at' => 'not-a-valid-date',
        ])->assertCreated();

        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $response->json('id'),
            'expected_return_at' => null,
        ]);
    }

    public function test_borrow_datetime_inputs_are_saved_as_utc_from_philippine_local_time(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 5]);
        $requestedFor = now('Asia/Manila')->addDays(2)->setTime(14, 30)->format('Y-m-d\TH:i');
        $expectedReturn = now('Asia/Manila')->addDays(3)->setTime(9, 0)->format('Y-m-d\TH:i');

        $response = $this->actingAs($teacher)->postJson('/inventory/transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'borrow',
            'quantity' => 1,
            'purpose' => 'For classroom use',
            'requested_for_at' => $requestedFor,
            'expected_return_at' => $expectedReturn,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('inventory_transactions', [
            'id' => $response->json('id'),
            'requested_for_at' => CarbonImmutable::parse($requestedFor, 'Asia/Manila')->utc()->format('Y-m-d H:i:s'),
            'expected_return_at' => CarbonImmutable::parse($expectedReturn, 'Asia/Manila')->utc()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_transaction_date_filters_use_philippine_calendar_days(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::factory()->create();
        $transaction = InventoryTransaction::factory()->create([
            'item_id' => $item->id,
            'created_at' => '2026-10-26 16:30:00',
        ]);

        $this->actingAs($admin)
            ->getJson('/inventory/transactions?date_from=2026-10-27&date_to=2026-10-27')
            ->assertOk()
            ->assertJsonPath('data.0.id', $transaction->id);
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

        $approver = User::factory()->create(['role' => 'admin']);
        $this->actingAs($approver)
            ->post('/inventory/transactions/'.$borrowId.'/approve')
            ->assertOk();

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

    public function test_borrow_request_requires_a_future_return_date(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 5]);

        $this->actingAs($teacher)
            ->postJson('/inventory/transactions', [
                'item_id' => $item->id,
                'transaction_type' => 'borrow',
                'quantity' => 1,
                'purpose' => 'For classroom activity',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('expected_return_at');

        $this->assertSame(5, $item->fresh()->quantity);
        $this->assertDatabaseMissing('inventory_transactions', [
            'item_id' => $item->id,
            'transaction_type' => 'borrow',
        ]);
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

    public function test_teacher_cannot_update_condition_to_an_unlisted_value(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $transaction = InventoryTransaction::factory()->create([
            'user_id' => $teacher->id,
            'transaction_type' => 'borrow',
        ]);

        $this->actingAs($teacher)
            ->patchJson('/inventory/transactions/'.$transaction->id.'/condition', [
                'condition' => 'In good condition',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('condition');
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
