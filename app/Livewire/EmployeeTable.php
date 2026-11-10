<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Employee;

class EmployeeTable extends Component
{
    // Search & Filter properties
    public string $search = '';
    public string $departmentFilter = '';

    public ?int $selectedEmployeeId = null;

    // Modal states
    public bool $showModal = false;
    public bool $showSaveConfirmModal = false;
    public bool $showDeleteConfirmModal = false;
    public bool $isEditing = false;

    // Feedback message
    public string $statusMessage = '';

    // Form fields
    public string $name = '';
    public string $email = '';
    public string $position = '';
    public string $department = '';
    public string $date_hired = '';

    // Clear search and department filter
    public function clearFilters(): void
    {
        $this->reset(['search', 'departmentFilter']);
    }

    public function selectEmployee(int $id): void
    {
        $this->selectedEmployeeId = ($this->selectedEmployeeId === $id) ? null : $id;
        $this->statusMessage = '';
    }

    public function openAddModal(): void
    {
        $this->reset(['name', 'email', 'position', 'department', 'date_hired', 'isEditing', 'showSaveConfirmModal', 'statusMessage']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(?int $id = null): void
    {
        if ($id) {
            $this->selectedEmployeeId = $id;
        }

        if (!$this->selectedEmployeeId) {
            $this->statusMessage = '⚠️ Please click on an employee row in the table first to edit.';
            return;
        }

        $employee = Employee::find($this->selectedEmployeeId);
        if (!$employee) {
            $this->statusMessage = '⚠️ Selected employee no longer exists.';
            return;
        }

        $this->name = $employee->name;
        $this->email = $employee->email;
        $this->position = $employee->position;
        $this->department = $employee->department;
        $this->date_hired = \Carbon\Carbon::parse($employee->date_hired)->format('Y-m-d');

        $this->resetValidation();
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function openDeleteModal(?int $id = null): void
    {
        if ($id) {
            $this->selectedEmployeeId = $id;
        }

        if (!$this->selectedEmployeeId) {
            $this->statusMessage = '⚠️ Please click on an employee row in the table first to delete.';
            return;
        }

        $this->showDeleteConfirmModal = true;
    }

    public function deleteConfirmed(): void
    {
        if ($this->selectedEmployeeId) {
            Employee::whereKey($this->selectedEmployeeId)->delete();
            $this->statusMessage = '✅ Employee deleted successfully.';
            $this->selectedEmployeeId = null;
        }

        $this->showDeleteConfirmModal = false;
    }

    public function confirmSave(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . ($this->isEditing ? $this->selectedEmployeeId : 'NULL'),
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'date_hired' => 'required|date',
        ]);

        $this->showSaveConfirmModal = true;
    }

    public function save(): void
    {
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'position' => $this->position,
            'department' => $this->department,
            'date_hired' => $this->date_hired,
        ];

        if ($this->isEditing && $this->selectedEmployeeId) {
            Employee::findOrFail($this->selectedEmployeeId)->update($data);
            $this->statusMessage = '✅ Employee updated successfully.';
        } else {
            Employee::create($data);
            $this->statusMessage = '✅ New employee added successfully.';
        }

        $this->showSaveConfirmModal = false;
        $this->showModal = false;
        $this->reset(['name', 'email', 'position', 'department', 'date_hired', 'isEditing']);
    }

    public function render()
    {
        $viewPath = view()->exists('livewire.employee-table')
            ? 'livewire.employee-table'
            : 'components.employee-table';

        $employees = Employee::query()
            ->when(trim($this->search) !== '', function ($query) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                      ->orWhere('email', 'like', $term)
                      ->orWhere('position', 'like', $term)
                      ->orWhere('department', 'like', $term);
                });
            })
            ->when($this->departmentFilter !== '', function ($query) {
                $query->where('department', $this->departmentFilter);
            })
            ->latest('id')
            ->get();

        // Get unique departments for the dropdown filter
        $departments = Employee::select('department')
            ->distinct()
            ->whereNotNull('department')
            ->pluck('department');

        return view($viewPath, [
            'employees' => $employees,
            'departments' => $departments,
        ]);
    }
}