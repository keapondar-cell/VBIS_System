<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Department::DEFAULT_NAMES as $name) {
            Department::updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }

        Department::whereNotIn('name', Department::DEFAULT_NAMES)->update(['is_active' => false]);
    }
}
