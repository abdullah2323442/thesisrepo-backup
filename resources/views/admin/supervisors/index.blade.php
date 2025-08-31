@extends('layouts.admin')

@section('page-title', 'Supervisor Management')
@section('page-description', 'Manage thesis supervisors and their settings')

@section('header-actions')
    <!-- Sync Button -->
    <form action="{{ route('admin.supervisors.sync') }}" method="POST" class="inline">
        @csrf
        <button type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Sync from API
        </button>
    </form>
    <!-- Bulk Actions Button -->
    <button onclick="toggleBulkForm()" 
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
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
        <!-- Total Supervisors -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Supervisors</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_supervisors'] }}</p>
                </div>
            </div>
        </div>

        <!-- Active Supervisors -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Supervisors</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active_supervisors'] }}</p>
                </div>
            </div>
        </div>

        <!-- Total Thesis Slots -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Thesis Slots</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_slots'] }}</p>
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
                        {{ $stats['last_sync'] ? $stats['last_sync']->diffForHumans() : 'Never' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Form (Hidden by default) -->
    <div id="bulkForm" class="hidden bg-white shadow rounded-lg p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Update Thesis Limits</h3>
        <form action="{{ route('admin.supervisors.bulk-limits') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="bulk_thesis_limit" class="block text-sm font-medium text-gray-700 mb-2">
                        New Thesis Limit
                    </label>
                    <input type="number" name="bulk_thesis_limit" id="bulk_thesis_limit" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           min="0" max="20" value="3" required>
                </div>
                <div>
                    <label for="apply_to" class="block text-sm font-medium text-gray-700 mb-2">
                        Apply To
                    </label>
                    <select name="apply_to" id="apply_to" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            onchange="toggleSupervisorSelection()" required>
                        <option value="all">All Supervisors</option>
                        <option value="active">Active Supervisors Only</option>
                        <option value="selected">Selected Supervisors</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        Update Limits
                    </button>
                </div>
            </div>
            
            <!-- Supervisor Selection (Hidden by default) -->
            <div id="supervisorSelection" class="hidden mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Select Supervisors
                </label>
                <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-md p-2">
                    @foreach($supervisors as $supervisor)
                        <label class="flex items-center space-x-2 py-1">
                            <input type="checkbox" name="supervisor_ids[]" value="{{ $supervisor->id }}" 
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <span class="text-sm">{{ $supervisor->fullname }} ({{ $supervisor->designation }})</span>
                        </label>
                    @endforeach
                </div>
            </div>
            
            <div class="mt-4 flex space-x-2">
                <button type="button" onclick="toggleBulkForm()" 
                        class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded text-sm font-medium transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Supervisors Table -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900">Supervisors ({{ $supervisors->total() }})</h3>
        </div>

        @if($supervisors->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Limit</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Areas</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assign Area</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($supervisors as $supervisor)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span class="text-xs font-medium text-indigo-800">
                                                    {{ substr($supervisor->fullname, 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $supervisor->fullname }}</div>
                                            <div class="text-xs text-gray-500">{{ $supervisor->designation }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900">{{ $supervisor->email }}</div>
                                    <div class="text-xs text-gray-500">{{ $supervisor->gender }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $supervisor->thesis_limit }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($supervisor->areasOfInterest as $area)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $area->name }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">None</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="relative">
                                        <select onchange="toggleArea({{ $supervisor->id }}, this.value)" 
                                                class="block w-full text-xs border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Select area...</option>
                                            @foreach($areasOfInterest as $area)
                                                @php
                                                    $isAssigned = $supervisor->areasOfInterest->contains('id', $area->id);
                                                @endphp
                                                <option value="{{ $area->id }}" {{ $isAssigned ? 'selected' : '' }}>
                                                    {{ $isAssigned ? '✓ ' : '+ ' }}{{ $area->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('admin.supervisors.toggle', $supervisor) }}" 
                                          method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="page" value="{{ request()->get('page', 1) }}">
                                        <button type="submit" 
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-colors {{ $supervisor->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}"
                                                title="Click to {{ $supervisor->is_active ? 'deactivate' : 'activate' }}">
                                            <span class="w-2 h-2 rounded-full mr-1 {{ $supervisor->is_active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                            {{ $supervisor->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.supervisors.edit', $supervisor) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 text-xs font-medium">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.supervisors.refresh', $supervisor) }}" 
                                              method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="page" value="{{ request()->get('page', 1) }}">
                                            <button type="submit" 
                                                    class="text-blue-600 hover:text-blue-900 text-xs font-medium">
                                                Refresh
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                {{ $supervisors->links() }}
            </div>
        @else
            <div class="px-4 py-8 text-center">
                <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">No supervisors found</h3>
                <p class="text-xs text-gray-500 mb-4">Sync from API to get supervisor data.</p>
                <form action="{{ route('admin.supervisors.sync') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-3 py-2 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Sync from API
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Hidden form for area assignment -->
    <form id="areaToggleForm" action="" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="area_id" id="areaToggleAreaId">
        <input type="hidden" name="page" value="{{ request()->get('page', 1) }}">
    </form>
@endsection

@push('scripts')
    <script>
        function toggleBulkForm() {
            const form = document.getElementById('bulkForm');
            form.classList.toggle('hidden');
        }

        function toggleSupervisorSelection() {
            const applyTo = document.getElementById('apply_to').value;
            const selection = document.getElementById('supervisorSelection');
            
            if (applyTo === 'selected') {
                selection.classList.remove('hidden');
            } else {
                selection.classList.add('hidden');
            }
        }

        function toggleArea(supervisorId, areaId) {
            if (areaId === '') {
                return; // Do nothing if no area selected
            }
            
            const form = document.getElementById('areaToggleForm');
            const areaInput = document.getElementById('areaToggleAreaId');
            
            // Set the form action and area ID
            form.action = `/admin/supervisors/${supervisorId}/toggle-area`;
            areaInput.value = areaId;
            
            // Submit the form
            form.submit();
        }
    </script>
@endpush
