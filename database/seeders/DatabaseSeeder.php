<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $departments = Department::factory()
            ->count(6)
            ->sequence(
                ['name' => 'Engineering', 'description' => 'Builds and maintains the product.'],
                ['name' => 'Product', 'description' => 'Defines the roadmap and product strategy.'],
                ['name' => 'Design', 'description' => 'Crafts the product experience and brand.'],
                ['name' => 'Marketing', 'description' => 'Tells the product story and grows the audience.'],
                ['name' => 'Sales', 'description' => 'Helps customers find the right solution.'],
                ['name' => 'Customer Support', 'description' => 'Keeps customers happy and successful.'],
            )
            ->create();

        Employee::factory()
            ->recycle($departments)
            ->count(40)
            ->create();
    }
}
