<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\MaterialIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryApprovalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_custodian_can_approve_issue()
    {
        $pc = User::factory()->create(['role' => 'property_custodian']);
        $requester = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 10]);

        $issue = MaterialIssue::create([
            'item_id' => $item->id,
            'issued_to_user_id' => $requester->id,
            'department_id' => null,
            'quantity' => 2,
            'status' => 'pending',
        ]);

        $this->actingAs($pc)
            ->post(route('inventory.issues.approve', ['id' => $issue->id]))
            ->assertStatus(200);
    }

    public function test_teacher_cannot_approve_issue()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $requester = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 10]);

        $issue = MaterialIssue::create([
            'item_id' => $item->id,
            'issued_to_user_id' => $requester->id,
            'department_id' => null,
            'quantity' => 2,
            'status' => 'pending',
        ]);

        $this->actingAs($teacher)
            ->post(route('inventory.issues.approve', ['id' => $issue->id]))
            ->assertStatus(403);
    }

    public function test_property_custodian_can_create_transaction()
    {
        $pc = User::factory()->create(['role' => 'property_custodian']);
        $item = Item::factory()->create(['quantity' => 10]);

        $payload = [
            'item_id' => $item->id,
            'transaction_type' => 'issue',
            'quantity' => 1,
        ];

        $this->actingAs($pc)
            ->post(route('inventory.transactions.store'), $payload)
            ->assertStatus(201);
    }

    public function test_teacher_cannot_create_transaction()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        $item = Item::factory()->create(['quantity' => 10]);

        $payload = [
            'item_id' => $item->id,
            'transaction_type' => 'issue',
            'quantity' => 1,
        ];

        $this->actingAs($teacher)
            ->post(route('inventory.transactions.store'), $payload)
            ->assertStatus(403);
    }
}
