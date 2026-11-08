<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryItemsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_configured_department_gets_seeded_inventory_items_idempotently(): void
    {
        $this->seed([
            \Database\Seeders\DepartmentSeeder::class,
            \Database\Seeders\InventoryItemsSeeder::class,
        ]);

        foreach (Department::DEFAULT_NAMES as $department) {
            $this->assertGreaterThanOrEqual(
                5,
                Item::where('location', $department)->count(),
                "Expected at least five seeded items for {$department}."
            );
        }

        $itemCount = Item::count();

        $this->seed(\Database\Seeders\InventoryItemsSeeder::class);

        $this->assertSame($itemCount, Item::count());
    }
}
