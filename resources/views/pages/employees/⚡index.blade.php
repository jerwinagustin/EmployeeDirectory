<?php

use App\Models\Department;
use App\Models\Employee;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Employees')] class extends Component {
    use WithPagination;

    /**
     * The columns employees may be sorted by.
     */
    protected const SORTABLE_COLUMNS = ['name', 'job_title', 'department', 'hire_date'];

    #[Url]
    public string $search = '';

    #[Url]
    public ?int $departmentId = null;

    #[Url]
    public string $sortCol = 'name';

    #[Url]
    public string $sortDir = 'asc';

    public bool $showDeleteModal = false;

    #[Locked]
    public ?int $deletingEmployeeId = null;

    #[Locked]
    public string $deletingEmployeeName = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        if (! in_array($this->sortCol, self::SORTABLE_COLUMNS)) {
            $this->reset('sortCol', 'sortDir');
        }
    }

    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->with('department')
            ->when($this->search !== '', function ($query) {
                $search = '%'.str($this->search)->trim()->toString().'%';

                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('job_title', 'like', $search);
                });
            })
            ->when($this->departmentId, fn ($query) => $query->where('department_id', $this->departmentId))
            ->when($this->sortCol === 'name', fn ($query) => $query->orderBy('last_name', $this->sortDir)->orderBy('first_name', $this->sortDir))
            ->when($this->sortCol === 'job_title', fn ($query) => $query->orderBy('job_title', $this->sortDir))
            ->when($this->sortCol === 'department', fn ($query) => $query->orderBy(
                Department::select('name')->whereColumn('departments.id', 'employees.department_id'),
                $this->sortDir,
            )->orderBy('last_name'))
            ->when($this->sortCol === 'hire_date', fn ($query) => $query->orderBy('hire_date', $this->sortDir))
            ->paginate(10);
    }

    #[Computed]
    public function departments()
    {
        return Department::orderBy('name')->get();
    }

    /**
     * Sort employees by the given column.
     */
    public function sortBy(string $column): void
    {
        if (! in_array($column, self::SORTABLE_COLUMNS)) {
            return;
        }

        if ($this->sortCol === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortCol = $column;
            $this->sortDir = 'asc';
        }
    }

    /**
     * Show the delete confirmation modal for the given employee.
     */
    public function confirmDeletion(int $employeeId): void
    {
        $employee = Employee::findOrFail($employeeId);

        $this->deletingEmployeeId = $employee->id;
        $this->deletingEmployeeName = $employee->name;
        $this->showDeleteModal = true;
    }

    /**
     * Delete the selected employee.
     */
    public function deleteEmployee(): void
    {
        if (! $this->deletingEmployeeId) {
            return;
        }

        Employee::findOrFail($this->deletingEmployeeId)->delete();

        $this->closeDeleteModal();

        Flux::toast(variant: 'success', text: __('Employee deleted.'));
    }

    /**
     * Close the delete confirmation modal.
     */
    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingEmployeeId = null;
        $this->deletingEmployeeName = '';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedDepartmentId(): void
    {
        $this->resetPage();
    }
}; ?>

<section class="w-full">
    <flux:heading level="2" class="sr-only">{{ __('Employees') }}</flux:heading>

    <div class="flex items-start justify-between gap-4 max-md:flex-col">
        <div>
            <flux:heading size="xl" level="1">{{ __('Employees') }}</flux:heading>
            <flux:subheading size="lg">{{ __('Browse and manage everyone in the organization.') }}</flux:subheading>
        </div>

        <flux:button variant="primary" :href="route('employees.create')" wire:navigate icon="plus" data-test="add-employee-button">
            {{ __('Add employee') }}
        </flux:button>
    </div>

    <flux:separator variant="subtle" class="my-6" />

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <flux:input
            wire:model.live.debounce.300ms="search"
            :placeholder="__('Search employees...')"
            icon="magnifying-glass"
            clearable
            class="md:w-80"
            data-test="employee-search"
        />

        <flux:select wire:model.live="departmentId" class="md:w-64" data-test="department-filter">
            <flux:select.option value="">{{ __('All departments') }}</flux:select.option>

            @foreach ($this->departments as $department)
                <flux:select.option :value="$department->id">{{ $department->name }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    @if ($this->employees->count())
        <flux:table :paginate="$this->employees" class="mt-6">
            <flux:table.columns>
                <flux:table.column
                    sortable
                    :sorted="$sortCol === 'name'"
                    :direction="$sortDir"
                    wire:click="sortBy('name')"
                >
                    {{ __('Name') }}
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortCol === 'job_title'"
                    :direction="$sortDir"
                    wire:click="sortBy('job_title')"
                >
                    {{ __('Job title') }}
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortCol === 'department'"
                    :direction="$sortDir"
                    wire:click="sortBy('department')"
                >
                    {{ __('Department') }}
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortCol === 'hire_date'"
                    :direction="$sortDir"
                    wire:click="sortBy('hire_date')"
                >
                    {{ __('Hire date') }}
                </flux:table.column>

                <flux:table.column align="end">
                    <span class="sr-only">{{ __('Actions') }}</span>
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->employees as $employee)
                    <flux:table.row :key="$employee->id">
                        <flux:table.cell variant="strong" class="py-4">
                            <div class="flex items-center gap-3">
                                <flux:avatar :name="$employee->name" size="xs" />

                                <div class="grid leading-tight">
                                    <flux:link :href="route('employees.show', $employee)" wire:navigate>
                                        {{ $employee->name }}
                                    </flux:link>

                                    <flux:text size="sm" class="mt-0.5">{{ $employee->email }}</flux:text>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>{{ $employee->job_title }}</flux:table.cell>

                        <flux:table.cell class="py-0">
                            @if ($employee->department)
                                <flux:badge size="sm">{{ $employee->department->name }}</flux:badge>
                            @else
                                <flux:text variant="subtle">{{ __('—') }}</flux:text>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>{{ $employee->hire_date->format('M j, Y') }}</flux:table.cell>

                        <flux:table.cell align="end" class="py-0">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" aria-label="{{ __('Employee actions') }}" />

                                <flux:menu>
                                    <flux:menu.item :href="route('employees.show', $employee)" icon="eye" wire:navigate>
                                        {{ __('View') }}
                                    </flux:menu.item>

                                    <flux:menu.item :href="route('employees.edit', $employee)" icon="pencil-square" wire:navigate>
                                        {{ __('Edit') }}
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:menu.item variant="danger" icon="trash" wire:click="confirmDeletion({{ $employee->id }})" data-test="delete-employee">
                                        {{ __('Delete') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @else
        <div class="mt-6 rounded-xl border border-zinc-200 p-8 text-center dark:border-zinc-700">
            <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800">
                <flux:icon.user-group class="size-7 text-zinc-400 dark:text-zinc-500" variant="outline" />
            </div>

            <p class="font-medium">{{ __('No employees found') }}</p>

            <flux:text class="mt-1">
                {{ __('Try adjusting your search or filters, or add a new employee.') }}
            </flux:text>

            <flux:button variant="primary" :href="route('employees.create')" wire:navigate icon="plus" class="mt-6">
                {{ __('Add employee') }}
            </flux:button>
        </div>
    @endif

    <flux:modal
        name="delete-employee-modal"
        class="max-w-md md:min-w-md"
        @close="closeDeleteModal"
        wire:model="showDeleteModal"
    >
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Delete employee') }}</flux:heading>
                <flux:text>
                    {{ __('Are you sure you want to delete ":name"? This action cannot be undone.', ['name' => $deletingEmployeeName]) }}
                </flux:text>
            </div>

            <div class="flex gap-3 justify-end">
                <flux:button variant="outline" wire:click="closeDeleteModal">
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button variant="danger" wire:click="deleteEmployee" data-test="confirm-delete-employee">
                    {{ __('Delete employee') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
