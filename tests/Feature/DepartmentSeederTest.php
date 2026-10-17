<?php

namespace Tests\Feature;

use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_configured_departments_are_active(): void
    {
        $this->seed(\Database\Seeders\DepartmentSeeder::class);

        $this->assertEqualsCanonicalizing(
            ['Library', 'Computer Lab', 'Science Lab', 'JHS Department', 'SHS Department'],
            Department::where('is_active', true)->pluck('name')->all()
        );

        $this->assertDatabaseHas('departments', [
            'name' => 'Teachers',
            'is_active' => 0,
        ]);
    }
}
