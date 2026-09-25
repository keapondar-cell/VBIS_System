<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_custodian_can_create_item()
    {
        $user = User::factory()->create(['role' => 'property_custodian']);

        $payload = [
            'name' => 'Test Item',
            'quantity' => 5,
        ];

        $this->actingAs($user)
            ->post(route('inventory.items.store'), $payload)
            ->assertStatus(201);
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

    public function test_admin_cannot_create_material_request()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::factory()->create(['quantity' => 10]);

        $this->actingAs($admin)
            ->post(route('inventory.issues.store'), [
                'item_id' => $item->id,
                'quantity' => 1,
                'purpose' => 'For office use',
            ])
            ->assertStatus(403);
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
