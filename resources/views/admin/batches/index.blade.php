@extends('layouts.admin')

@section('page-title', 'Batch Management')
@section('page-description', 'Manage student batches and their availability for system operations')

@section('header-actions')
    <form action="{{ route('admin.batches.sync') }}" method="POST" class="inline">
        @csrf
        <button type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Sync from API
        </button>
    </form>
    <button onclick="toggleBulkActions()" 
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-md">
        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
        </svg>
        Bulk Actions
    </button>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Batches -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Batches</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <!-- Active Batches -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Batches</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>

        <!-- Inactive Batches -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Inactive Batches</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['inactive'] }}</p>
                </div>
            </div>
        </div>

        <!-- Last Sync -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Last Sync</p>
                    <p class="text-sm font-bold text-gray-900">
                        {{ $stats['last_sync'] ? \Carbon\Carbon::parse($stats['last_sync'])->diffForHumans() : 'Never' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Instructions for Bulk Actions -->
    <div id="bulkInstructions" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-blue-800">
                <strong>Tip:</strong> Select batches using checkboxes below, then click "Bulk Actions" button above to activate/deactivate multiple batches at once.
            </p>
        </div>
    </div>

    <!-- Bulk Actions Form (Hidden by default) -->
    <div id="bulkActionsForm" class="hidden bg-white shadow rounded-lg p-6 mb-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Bulk Actions</h3>
            <span class="text-sm text-gray-500">Perform actions on selected batches</span>
        </div>
        <form action="{{ route('admin.batches.bulk-action') }}" method="POST" id="bulkForm">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="action" class="block text-sm font-medium text-gray-700 mb-2">
                        Action
                    </label>
                    <select name="action" id="action" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            required>
                        <option value="">Select Action</option>
                        <option value="activate">Activate Selected</option>
                        <option value="deactivate">Deactivate Selected</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        Apply to Selected
                    </button>
                </div>
                <div class="flex items-end space-x-2">
                    <form action="{{ route('admin.batches.activate-all') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm font-medium transition-colors"
                                onclick="return confirm('Activate all batches?')">
                            Activate All
                        </button>
                    </form>
                    <form action="{{ route('admin.batches.deactivate-all') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm font-medium transition-colors"
                                onclick="return confirm('Deactivate all batches?')">
                            Deactivate All
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="mt-4 flex space-x-2">
                <button type="button" onclick="toggleBulkActions()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded text-sm font-medium transition-colors">
                    Cancel
                </button>
                <button type="button" onclick="selectAllBatches()" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
                    Select All
                </button>
                <button type="button" onclick="clearSelection()" 
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
                    Clear Selection
                </button>
            </div>
        </form>
    </div>

    <!-- Batches Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">All Batches</h3>
                
                <!-- Bulk Actions Buttons (Visible when batches are selected) -->
                <div id="bulkActionButtons" class="hidden flex items-center space-x-2">
                    <span id="selectedCount" class="text-sm text-gray-600"></span>
                    <form action="{{ route('admin.batches.bulk-action') }}" method="POST" class="inline flex items-center space-x-2">
                        @csrf
                        <input type="hidden" name="action" value="activate">
                        <button type="submit" 
                                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors"
                                onclick="return submitBulkAction(this, 'activate')">
                            Activate Selected
                        </button>
                    </form>
                    <form action="{{ route('admin.batches.bulk-action') }}" method="POST" class="inline flex items-center space-x-2">
                        @csrf
                        <input type="hidden" name="action" value="deactivate">
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors"
                                onclick="return submitBulkAction(this, 'deactivate')">
                            Deactivate Selected
                        </button>
                    </form>
                    <button onclick="clearSelection()" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                        Clear
                    </button>
                </div>
            </div>
        </div>

        @if($batches->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="selectAll" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Synced</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($batches as $batch)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" name="batch_ids[]" value="{{ $batch->id }}" 
                                           class="batch-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $batch->batch_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $batch->display_name }}</div>
                                    @if($batch->description)
                                        <div class="text-xs text-gray-500">{{ Str::limit($batch->description, 50) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $batch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $batch->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $batch->last_synced_at ? $batch->last_synced_at->format('M d, Y H:i') : 'Never' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <form action="{{ route('admin.batches.toggle', $batch) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="text-{{ $batch->is_active ? 'red' : 'green' }}-600 hover:text-{{ $batch->is_active ? 'red' : 'green' }}-900">
                                                {{ $batch->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                        
                                        <a href="{{ route('admin.batches.edit', $batch) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        
                                        <form action="{{ route('admin.batches.destroy', $batch) }}" 
                                              method="POST" class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this batch?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $batches->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No batches found</h3>
                <p class="mt-1 text-sm text-gray-500">Sync from API to get batch data.</p>
                <div class="mt-6">
                    <form action="{{ route('admin.batches.sync') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Sync from API
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    function toggleBulkActions() {
        const form = document.getElementById('bulkActionsForm');
        const instructions = document.getElementById('bulkInstructions');
        
        form.classList.toggle('hidden');
        
        // Hide instructions when bulk form is shown
        if (!form.classList.contains('hidden')) {
            instructions.classList.add('hidden');
        } else {
            instructions.classList.remove('hidden');
        }
    }

    function selectAllBatches() {
        const checkboxes = document.querySelectorAll('.batch-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = true);
    }

    function clearSelection() {
        const checkboxes = document.querySelectorAll('.batch-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = false);
        document.getElementById('selectAll').checked = false;
        updateBulkActionButtons();
    }

    // Handle bulk action submission
    function submitBulkAction(button, action) {
        const checkedBoxes = document.querySelectorAll('.batch-checkbox:checked');
        
        if (checkedBoxes.length === 0) {
            alert('Please select at least one batch.');
            return false;
        }
        
        const form = button.closest('form');
        
        // Remove any existing hidden inputs
        const existingInputs = form.querySelectorAll('input[name="batch_ids[]"]');
        existingInputs.forEach(input => input.remove());
        
        // Add selected batch IDs as hidden inputs to the form
        checkedBoxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'batch_ids[]';
            hiddenInput.value = checkbox.value;
            form.appendChild(hiddenInput);
        });
        
        const actionText = action === 'activate' ? 'activate' : 'deactivate';
        return confirm(`Are you sure you want to ${actionText} ${checkedBoxes.length} selected batch(es)?`);
    }



    // Handle individual checkboxes
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('batch-checkbox')) {
            const allCheckboxes = document.querySelectorAll('.batch-checkbox');
            const checkedCheckboxes = document.querySelectorAll('.batch-checkbox:checked');
            const selectAllCheckbox = document.getElementById('selectAll');
            
            selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
            
            // Show/hide bulk action buttons
            updateBulkActionButtons();
        }
    });

    // Handle select all checkbox change
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.batch-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        updateBulkActionButtons();
    });

    // Update bulk action buttons visibility and count
    function updateBulkActionButtons() {
        const checkedCheckboxes = document.querySelectorAll('.batch-checkbox:checked');
        const bulkActionButtons = document.getElementById('bulkActionButtons');
        const selectedCount = document.getElementById('selectedCount');
        
        console.log('updateBulkActionButtons called:', {
            checkedCount: checkedCheckboxes.length,
            bulkActionButtons: bulkActionButtons,
            selectedCount: selectedCount
        });
        
        if (checkedCheckboxes.length > 0) {
            bulkActionButtons.classList.remove('hidden');
            selectedCount.textContent = `${checkedCheckboxes.length} selected`;
        } else {
            bulkActionButtons.classList.add('hidden');
        }
    }

    // Handle form submission
    document.getElementById('bulkForm').addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.batch-checkbox:checked');
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Please select at least one batch.');
            return false;
        }
        
        const action = document.getElementById('action').value;
        if (!action) {
            e.preventDefault();
            alert('Please select an action.');
            return false;
        }
        
        // Remove any existing hidden inputs
        const existingInputs = this.querySelectorAll('input[name="batch_ids[]"]');
        existingInputs.forEach(input => input.remove());
        
        // Add selected batch IDs as hidden inputs to the form
        checkedBoxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'batch_ids[]';
            hiddenInput.value = checkbox.value;
            this.appendChild(hiddenInput);
        });
        
        const actionText = action === 'activate' ? 'activate' : 'deactivate';
        return confirm(`Are you sure you want to ${actionText} ${checkedBoxes.length} selected batch(es)?`);
    });
</script>
@endpush
