<?php

use App\Models\Employee;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Employee details')] class extends Component {
    public Employee $employee;

    /**
     * Mount the component.
     */
    public function mount(Employee $employee): void
    {
        $this->employee = $employee->load('department');
    }
}; ?>

<section class="w-full">
    <flux:heading level="2" class="sr-only">{{ __('Employee details') }}</flux:heading>

    <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('employees.index')" wire:navigate class="-ms-2">
        {{ __('Back to employees') }}
    </flux:button>

    <flux:card class="mt-4">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <flux:avatar :name="$employee->name" size="xl" />

                <div>
                    <flux:heading size="lg" level="1">{{ $employee->name }}</flux:heading>
                    <flux:subheading>{{ $employee->job_title }}</flux:subheading>

                    @if ($employee->department)
                        <flux:badge size="sm" class="mt-2">{{ $employee->department->name }}</flux:badge>
                    @endif
                </div>
            </div>

            <flux:button variant="primary" :href="route('employees.edit', $employee)" wire:navigate icon="pencil-square" data-test="edit-employee-button">
                {{ __('Edit employee') }}
            </flux:button>
        </div>

        <flux:separator class="my-6" />

        <dl class="grid gap-6 sm:grid-cols-2">
            <div class="flex items-center gap-3">
                <flux:icon.envelope class="size-5 text-zinc-400 dark:text-zinc-500" variant="outline" />
                <div class="grid">
                    <flux:text size="sm" variant="subtle">{{ __('Email') }}</flux:text>
                    <flux:link :href="'mailto:'.$employee->email">{{ $employee->email }}</flux:link>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <flux:icon.phone class="size-5 text-zinc-400 dark:text-zinc-500" variant="outline" />
                <div class="grid">
                    <flux:text size="sm" variant="subtle">{{ __('Phone') }}</flux:text>
                    @if ($employee->phone)
                        <flux:link :href="'tel:'.$employee->phone">{{ $employee->phone }}</flux:link>
                    @else
                        <flux:text>{{ __('—') }}</flux:text>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                <flux:icon.building-office-2 class="size-5 text-zinc-400 dark:text-zinc-500" variant="outline" />
                <div class="grid">
                    <flux:text size="sm" variant="subtle">{{ __('Department') }}</flux:text>
                    <flux:text>{{ $employee->department?->name ?? __('—') }}</flux:text>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <flux:icon.calendar class="size-5 text-zinc-400 dark:text-zinc-500" variant="outline" />
                <div class="grid">
                    <flux:text size="sm" variant="subtle">{{ __('Hire date') }}</flux:text>
                    <flux:text>{{ $employee->hire_date->format('M j, Y') }}</flux:text>
                </div>
            </div>
        </dl>
    </flux:card>
</section>
