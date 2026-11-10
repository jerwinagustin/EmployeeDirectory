<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        [$firstName, $lastName] = [fake()->firstName(), fake()->lastName()];

        return [
            'department_id' => Department::factory(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'job_title' => fake()->randomElement([
                'Software Engineer',
                'Senior Software Engineer',
                'Product Manager',
                'Product Designer',
                'Account Executive',
                'Support Specialist',
                'Data Analyst',
                'Team Lead',
            ]),
            'hire_date' => Carbon::today()->subDays(fake()->numberBetween(0, 365 * 5)),
        ];
    }

    /**
     * Indicate that the employee does not belong to a department.
     */
    public function withoutDepartment(): static
    {
        return $this->state(fn (array $attributes) => [
            'department_id' => null,
        ]);
    }
}
