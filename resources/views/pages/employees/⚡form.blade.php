<?php

use App\Models\Department;
use App\Models\Employee;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Employee')] class extends Component {
    #[Locked]
    public ?Employee $employee = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $phone = '';

    public string $job_title = '';

    public ?int $department_id = null;

    public string $hire_date = '';

    /**
     * Mount the component.
     */
    public function mount(?Employee $employee = null): void
    {
        $this->employee = $employee;

        $this->first_name = $employee?->first_name ?? '';
        $this->last_name = $employee?->last_name ?? '';
        $this->email = $employee?->email ?? '';
        $this->phone = $employee?->phone ?? '';
        $this->job_title = $employee?->job_title ?? '';
        $this->department_id = $employee?->department_id;
        $this->hire_date = $employee?->hire_date?->format('Y-m-d') ?? '';
    }

    /**
     * Save the employee.
     */
    public function save(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($this->employee?->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'job_title' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'hire_date' => ['required', 'date'],
        ]);

        $employee = Employee::updateOrCreate(
            ['id' => $this->employee?->id],
            [...$validated, 'phone' => $validated['phone'] ?: null],
        );

        Flux::toast(variant: 'success', text: $this->employee ? __('Employee updated.') : __('Employee created.'));

        $this->redirect(route('employees.show', $employee), navigate: true);
    }

    #[Computed]
    public function departments()
    {
        return Department::orderBy('name')->get();
    }
}; ?>

<section class="w-full">
    <flux:heading level="2" class="sr-only">
        {{ $employee ? __('Edit employee') : __('Add employee') }}
    </flux:heading>

    <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('employees.index')" wire:navigate class="-ms-2">
        {{ __('Back to employees') }}
    </flux:button>

    <div class="mt-4 mb-6">
        <flux:heading size="xl" level="1">
            {{ $employee ? __('Edit employee') : __('Add employee') }}
        </flux:heading>
        <flux:subheading size="lg">
            {{ $employee ? __('Update the details for :name.', ['name' => $employee->name]) : __('Add a new person to the organization.') }}
        </flux:subheading>
    </div>

    <flux:card class="max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <flux:input wire:model="first_name" :label="__('First name')" type="text" required autofocus autocomplete="given-name" data-test="first-name-input" />

                <flux:input wire:model="last_name" :label="__('Last name')" type="text" required autocomplete="family-name" data-test="last-name-input" />

                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" placeholder="email@example.com" data-test="email-input" />

                <flux:input wire:model="phone" :label="__('Phone')" type="tel" autocomplete="tel" data-test="phone-input" />

                <flux:input wire:model="job_title" :label="__('Job title')" type="text" required autocomplete="organization-title" placeholder="{{ __('e.g. Software Engineer') }}" data-test="job-title-input" />

                <flux:select wire:model="department_id" :label="__('Department')" data-test="department-select">
                    <flux:select.option value="">{{ __('No department') }}</flux:select.option>

                    @foreach ($this->departments as $department)
                        <flux:select.option :value="$department->id">{{ $department->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="hire_date" :label="__('Hire date')" type="date" required data-test="hire-date-input" />
            </div>

            <div class="flex gap-3">
                <flux:button variant="primary" type="submit" data-test="save-employee-button">
                    {{ $employee ? __('Save changes') : __('Create employee') }}
                </flux:button>

                <flux:button variant="ghost" :href="route('employees.index')" wire:navigate>
                    {{ __('Cancel') }}
                </flux:button>
            </div>
        </form>
    </flux:card>
</section>
