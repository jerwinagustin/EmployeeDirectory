<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('employees.index'))->assertRedirect(route('login'));
    }

    public function test_employees_page_is_displayed(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs(User::factory()->create());

        $this->get(route('employees.index'))
            ->assertOk()
            ->assertSee($employee->name)
            ->assertSee($employee->job_title);
    }

    public function test_employee_details_page_is_displayed(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs(User::factory()->create());

        $this->get(route('employees.show', $employee))
            ->assertOk()
            ->assertSee($employee->name)
            ->assertSee($employee->email)
            ->assertSee($employee->job_title);
    }

    public function test_create_employee_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('employees.create'))->assertOk();
    }

    public function test_edit_employee_page_is_displayed(): void
    {
        $employee = Employee::factory()->create();

        $this->actingAs(User::factory()->create());

        $this->get(route('employees.edit', $employee))->assertOk();
    }

    public function test_employees_can_be_searched(): void
    {
        $this->actingAs(User::factory()->create());

        Employee::factory()->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
        Employee::factory()->create(['first_name' => 'Grace', 'last_name' => 'Hopper']);

        Livewire::test('pages::employees.index')
            ->set('search', 'Lovelace')
            ->assertSee('Ada Lovelace')
            ->assertDontSee('Grace Hopper');
    }

    public function test_employees_can_be_filtered_by_department(): void
    {
        $this->actingAs(User::factory()->create());

        $engineering = Department::factory()->create(['name' => 'Engineering']);
        $design = Department::factory()->create(['name' => 'Design']);

        Employee::factory()->for($engineering)->create(['first_name' => 'Ada', 'last_name' => 'Lovelace']);
        Employee::factory()->for($design)->create(['first_name' => 'Grace', 'last_name' => 'Hopper']);

        Livewire::test('pages::employees.index')
            ->set('departmentId', $engineering->id)
            ->assertSee('Ada Lovelace')
            ->assertDontSee('Grace Hopper');
    }

    public function test_employee_can_be_created(): void
    {
        $this->actingAs(User::factory()->create());

        $department = Department::factory()->create();

        Livewire::test('pages::employees.form')
            ->set('first_name', 'Ada')
            ->set('last_name', 'Lovelace')
            ->set('email', 'ada@example.com')
            ->set('phone', '555-1234')
            ->set('job_title', 'Software Engineer')
            ->set('department_id', $department->id)
            ->set('hire_date', '2024-01-15')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('employees.show', Employee::where('email', 'ada@example.com')->firstOrFail()));

        $this->assertDatabaseHas('employees', [
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'email' => 'ada@example.com',
            'job_title' => 'Software Engineer',
            'department_id' => $department->id,
        ]);
    }

    public function test_employee_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create());

        $employee = Employee::factory()->create();

        Livewire::test('pages::employees.form', ['employee' => $employee])
            ->set('first_name', 'Ada')
            ->set('last_name', 'Lovelace')
            ->set('email', 'ada@example.com')
            ->set('job_title', 'Mathematician')
            ->set('hire_date', '2024-01-15')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('employees.show', $employee));

        $employee->refresh();

        $this->assertSame('Ada', $employee->first_name);
        $this->assertSame('Lovelace', $employee->last_name);
        $this->assertSame('ada@example.com', $employee->email);
        $this->assertSame('Mathematician', $employee->job_title);
    }

    public function test_employee_information_requires_valid_data(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test('pages::employees.form')
            ->set('first_name', '')
            ->set('email', 'not-an-email')
            ->set('job_title', '')
            ->set('hire_date', '')
            ->call('save')
            ->assertHasErrors(['first_name', 'email', 'job_title', 'hire_date']);
    }

    public function test_employee_email_must_be_unique(): void
    {
        $this->actingAs(User::factory()->create());

        Employee::factory()->create(['email' => 'ada@example.com']);

        Livewire::test('pages::employees.form')
            ->set('first_name', 'Ada')
            ->set('last_name', 'Lovelace')
            ->set('email', 'ada@example.com')
            ->set('job_title', 'Software Engineer')
            ->set('hire_date', '2024-01-15')
            ->call('save')
            ->assertHasErrors(['email']);

        $this->assertDatabaseCount('employees', 1);
    }

    public function test_employee_can_be_deleted(): void
    {
        $this->actingAs(User::factory()->create());

        $employee = Employee::factory()->create();

        Livewire::test('pages::employees.index')
            ->call('confirmDeletion', $employee->id)
            ->assertSet('deletingEmployeeId', $employee->id)
            ->assertSet('showDeleteModal', true)
            ->call('deleteEmployee')
            ->assertHasNoErrors();

        $this->assertNull($employee->fresh());
    }
}
