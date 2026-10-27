<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_custodian_can_create_item()
    {
        $user = User::factory()->create(['role' => 'property_custodian']);
        $this->seed(\Database\Seeders\DepartmentSeeder::class);

        $payload = [
            'name' => 'Test Item',
            'quantity' => 5,
            'location' => 'Science Lab',
        ];

        $this->actingAs($user)
            ->post(route('inventory.items.store'), $payload)
            ->assertStatus(201);

        $this->assertDatabaseHas('items', [
            'name' => 'Test Item',
            'location' => 'Science Lab',
        ]);
    }

    public function test_item_creation_uses_the_selected_philippines_date_and_time(): void
    {
        $user = User::factory()->create(['role' => 'property_custodian']);

        $this->actingAs($user)
            ->postJson(route('inventory.items.store'), [
                'name' => 'Backdated Item',
                'quantity' => 5,
                'created_at' => '2026-10-25T09:15',
            ])
            ->assertCreated()
            ->assertJsonPath('created_at', '2026-10-25T01:15:00.000000Z');

        $this->assertDatabaseHas('items', [
            'name' => 'Backdated Item',
            'created_at' => '2026-10-25 01:15:00',
        ]);
    }

    public function test_inventory_items_form_shows_department_dropdown_options()
    {
        \App\Models\Department::query()->delete();
        $user = User::factory()->create(['role' => 'admin']);
        Item::factory()->create([
            'name' => 'Desk Lamp',
            'created_at' => Carbon::parse('2026-10-25 01:00:00', 'UTC'),
        ]);

        $this->actingAs($user)
            ->get(route('inventory.items.list'))
            ->assertOk()
            ->assertSee('<th>Name</th>', false)
            ->assertSee('<th>Date Added</th>', false)
            ->assertSee('type="datetime-local"', false)
            ->assertSee('Date and Time Added')
            ->assertSee('Oct 25, 2026 09:00 AM')
            ->assertDontSee('<th>ID</th>', false)
            ->assertDontSee('Item ID:')
            ->assertSee('Desk Lamp')
            ->assertSee('<select id="i_location"', false)
            ->assertSee('<option value="Library">Library</option>', false)
            ->assertSee('<option value="Computer Lab">Computer Lab</option>', false)
            ->assertSee('<option value="Science Lab">Science Lab</option>', false)
            ->assertSee('<option value="JHS Department">JHS Department</option>', false)
            ->assertSee('<option value="SHS Department">SHS Department</option>', false);
    }

    public function test_teacher_cannot_create_item()
    {
        $user = User::factory()->create(['role' => 'teacher']);

        $payload = [
            'name' => 'Test Item',
            'quantity' => 5,
        ];

        $this->actingAs($user)
            ->post(route('inventory.items.store'), $payload)
            ->assertStatus(403);
    }

    public function test_admin_can_create_material_request()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::factory()->create(['quantity' => 10]);

        $this->actingAs($admin)
            ->post(route('inventory.issues.store'), [
                'item_id' => $item->id,
                'quantity' => 1,
                'purpose' => 'For office use',
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('material_issues', [
            'item_id' => $item->id,
            'issued_to_user_id' => $admin->id,
            'quantity' => 1,
            'status' => 'pending',
            'notes' => 'For office use',
        ]);
    }

    public function test_admin_can_see_material_request_form()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('inventory.issues.list'))
            ->assertOk()
            ->assertSee('Request Material')
            ->assertSee('Borrow');
    }

    public function test_material_issues_page_shows_the_configured_departments_in_order()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->get(route('inventory.issues.list'))
            ->assertOk()
            ->assertSeeInOrder([
                'Library',
                'Computer Lab',
                'Science Lab',
                'JHS Department',
                'SHS Department',
            ]);
    }

    public function test_admin_can_borrow_item_with_purpose()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::factory()->create(['quantity' => 10]);
        $borrowedAt = '2026-10-18T09:30';
        $returnDate = now()->addDay()->toDateString();

        $this->actingAs($admin)
            ->postJson(route('inventory.transactions.store'), [
                'item_id' => $item->id,
                'transaction_type' => 'borrow',
                'quantity' => 1,
                'purpose' => 'For office use',
                'requested_for_at' => $borrowedAt,
                'expected_return_at' => $returnDate,
            ])
            ->assertStatus(201);

        $this->assertDatabaseHas('inventory_transactions', [
            'item_id' => $item->id,
            'user_id' => $admin->id,
            'transaction_type' => 'borrow',
            'quantity' => 1,
            'status' => 'pending',
            'notes' => 'For office use',
            'requested_for_at' => '2026-10-18 01:30:00',
            'expected_return_at' => $returnDate.' 00:00:00',
        ]);
    }

    public function test_teacher_request_requires_purpose()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 10]);

        $this->actingAs($teacher)
            ->from(route('inventory.issues.index'))
            ->post(route('inventory.issues.store'), [
                'item_id' => $item->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors(['purpose'])
            ->assertStatus(302);
    }

    public function test_property_custodian_cannot_borrow_item()
    {
        $user = User::factory()->create(['role' => 'property_custodian']);
        $item = Item::factory()->create(['quantity' => 10]);

        $this->actingAs($user)
            ->post(route('inventory.transactions.store'), [
                'item_id' => $item->id,
                'transaction_type' => 'borrow',
                'quantity' => 1,
                'purpose' => 'For classroom use',
                'expected_return_at' => now()->addDay()->toDateString(),
            ])
            ->assertStatus(403);
    }

    public function test_teacher_can_borrow_item_with_purpose()
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 10]);

        $this->actingAs($user)
            ->post(route('inventory.transactions.store'), [
                'item_id' => $item->id,
                'transaction_type' => 'borrow',
                'quantity' => 1,
                'purpose' => 'For classroom use',
                'expected_return_at' => now()->addDay()->toDateString(),
            ])
            ->assertStatus(201);
    }

    public function test_teacher_borrow_requires_purpose()
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 10]);

        $this->actingAs($user)
            ->from(route('inventory.transactions.list'))
            ->post(route('inventory.transactions.store'), [
                'item_id' => $item->id,
                'transaction_type' => 'borrow',
                'quantity' => 1,
                'expected_return_at' => now()->addDay()->toDateString(),
            ])
            ->assertSessionHasErrors(['purpose'])
            ->assertStatus(302);
    }

    public function test_property_custodian_cannot_create_material_request()
    {
        $user = User::factory()->create(['role' => 'property_custodian']);
        $item = Item::factory()->create(['quantity' => 10]);

        $this->actingAs($user)
            ->post(route('inventory.issues.store'), [
                'item_id' => $item->id,
                'quantity' => 1,
                'purpose' => 'For office use',
            ])
            ->assertStatus(403);
    }

    public function test_admin_can_delete_user()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $target->id))
            ->assertRedirect(route('admin.users'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_teacher_role_helper_matches_teacher_permissions()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->assertTrue($teacher->isTeacher());
        $this->assertFalse($teacher->isAdmin());
        $this->assertFalse($teacher->isPropertyCustodian());
    }
}
