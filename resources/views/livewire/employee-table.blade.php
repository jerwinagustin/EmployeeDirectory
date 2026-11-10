<div 
    class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full"
    x-on:open-add-modal.window="$wire.openAddModal()"
    x-on:open-edit-modal.window="$wire.openEditModal()"
    x-on:open-delete-modal.window="$wire.openDeleteModal()"
>
  

    <!-- Status / Alert Banner -->
   
<div 
    class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full"
    x-on:open-add-modal.window="$wire.openAddModal()"
    x-on:open-edit-modal.window="$wire.openEditModal()"
    x-on:open-delete-modal.window="$wire.openDeleteModal()"
>
    <!-- Header & Action Bar -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Employees</h2>
            <p class="text-sm text-gray-500 mt-1">
                Click any row to select an employee, or use the search bar below to filter records.
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($selectedEmployeeId)
                <span class="px-3 py-1.5 text-xs font-semibold bg-indigo-100 text-indigo-700 rounded-lg">
                    Selected ID: #{{ $selectedEmployeeId }}
                </span>
            @endif

            <button 
                type="button"
                wire:click="openAddModal"
                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition cursor-pointer"
            >
                + Add Employee
            </button>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="mb-4 bg-white p-4 border border-gray-200 rounded-xl shadow-sm flex flex-col sm:flex-row items-center gap-3">
        <!-- Live Search Input -->
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input 
                type="text" 
                wire:model.live.debounce.250ms="search" 
                placeholder="Search by name, email, position, or department..." 
                class="w-full pl-10 pr-9 py-2 border border-gray-300 rounded-lg text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            >
            @if($search !== '')
                <button 
                    type="button" 
                    wire:click="$set('search', '')" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer"
                    title="Clear search"
                >
                    ✕
                </button>
            @endif
        </div>

        <!-- Department Dropdown Filter -->
        <div class="w-full sm:w-56">
            <select 
                wire:model.live="departmentFilter" 
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"
            >
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>
        </div>

        <!-- Reset Button (Shows only when a filter is active) -->
        @if($search !== '' || $departmentFilter !== '')
            <button 
                type="button" 
                wire:click="clearFilters" 
                class="w-full sm:w-auto px-3 py-2 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition cursor-pointer whitespace-nowrap"
            >
                Reset Filters
            </button>
        @endif
    </div>

    <!-- Status / Alert Banner -->
    @if($statusMessage)
        <div class="mb-4 px-4 py-3 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-800 text-sm flex items-center justify-between">
            <span>{{ $statusMessage }}</span>
            <button type="button" wire:click="$set('statusMessage', '')" class="text-indigo-500 hover:text-indigo-800 font-bold">✕</button>
        </div>
    @endif
    <!-- Table Card Container -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left text-sm">
                <thead class="bg-gray-50 text-gray-600 font-medium">
                    <tr>
                        <th scope="col" class="px-6 py-3.5">Name & Email</th>
                        <th scope="col" class="px-6 py-3.5">Position</th>
                        <th scope="col" class="px-6 py-3.5">Department</th>
                        <th scope="col" class="px-6 py-3.5">Date Hired</th>
                        <th scope="col" class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($employees as $employee)
                        <tr 
                            wire:key="emp-{{ $employee->id }}"
                            wire:click="selectEmployee({{ $employee->id }})"
                            class="cursor-pointer transition-colors {{ $selectedEmployeeId === $employee->id ? 'bg-indigo-50 ring-2 ring-inset ring-indigo-500' : 'hover:bg-gray-50' }}"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $employee->name }}</div>
                                <div class="text-gray-500 text-xs mt-0.5">{{ $employee->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">
                                {{ $employee->position }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $employee->department }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                {{ $employee->date_hired ? \Carbon\Carbon::parse($employee->date_hired)->format('M d, Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-2">
                                <button 
                                    type="button"
                                    wire:click.stop="openEditModal({{ $employee->id }})"
                                    class="px-2.5 py-1 text-xs font-medium text-amber-800 bg-amber-100 hover:bg-amber-200 rounded-md transition cursor-pointer"
                                >
                                    Edit
                                </button>
                                <button 
                                    type="button"
                                    wire:click.stop="openDeleteModal({{ $employee->id }})"
                                    class="px-2.5 py-1 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-md transition cursor-pointer"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <p class="text-base font-medium">No employees found</p>
                                <p class="text-sm text-gray-400 mt-1">Click "+ Add Employee" in the sidebar or top-right to add your first record.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 1. ADD / EDIT FORM MODAL -->
    @if($showModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-xl shadow-lg max-w-md w-full p-6 space-y-4">
                <h3 class="text-lg font-bold text-gray-900">
                    {{ $isEditing ? 'Edit Employee' : 'Add New Employee' }}
                </h3>

                <form wire:submit.prevent="confirmSave" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" wire:model="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900" required>
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" wire:model="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900" required>
                        @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Position</label>
                        <input type="text" wire:model="position" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900" required>
                        @error('position') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Department</label>
                        <input type="text" wire:model="department" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900" required>
                        @error('department') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Date Hired</label>
                        <input type="date" wire:model="date_hired" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-900" required>
                        @error('date_hired') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg cursor-pointer">
                            {{ $isEditing ? 'Update Employee' : 'Save Employee' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- 2. PRE-SAVE CONFIRMATION MODAL -->
    @if($showSaveConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 text-center space-y-4">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 text-indigo-600 font-bold text-lg">
                    ?
                </div>
                <h4 class="text-base font-bold text-gray-900">
                    {{ $isEditing ? 'Confirm Update' : 'Confirm Save' }}
                </h4>
                <p class="text-sm text-gray-500">
                    Are you sure you want to {{ $isEditing ? 'save changes to' : 'add' }} <span class="font-semibold text-gray-800">{{ $name }}</span>?
                </p>
                <div class="flex justify-center gap-3 pt-2">
                    <button 
                        type="button" 
                        wire:click="$set('showSaveConfirmModal', false)" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg cursor-pointer"
                    >
                        No, Go Back
                    </button>
                    <button 
                        type="button" 
                        wire:click="save" 
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg cursor-pointer"
                    >
                        Yes, {{ $isEditing ? 'Update' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- 3. DELETE CONFIRMATION MODAL -->
    @if($showDeleteConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-sm w-full p-6 text-center space-y-4">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600 font-bold text-lg">
                    !
                </div>
                <h4 class="text-base font-bold text-gray-900">Confirm Delete</h4>
                <p class="text-sm text-gray-500">
                    Are you sure you want to permanently delete Employee #{{ $selectedEmployeeId }}?
                </p>
                <div class="flex justify-center gap-3 pt-2">
                    <button 
                        type="button" 
                        wire:click="$set('showDeleteConfirmModal', false)" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        wire:click="deleteConfirmed" 
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg cursor-pointer"
                    >
                        Yes, Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>