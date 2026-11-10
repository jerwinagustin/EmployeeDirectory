<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Engineering',
            'Product',
            'Design',
            'Marketing',
            'Sales',
            'Customer Support',
            'Finance',
            'Human Resources',
            'Legal',
            'Operations',
            'IT',
            'Security',
            'Data',
            'Research',
            'Quality Assurance',
            'Facilities',
        ]);

        return [
            'name' => $name,
            'description' => 'The '.strtolower($name).' department.',
        ];
    }
}
