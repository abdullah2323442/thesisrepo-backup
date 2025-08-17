@extends('layouts.advisor')

@section('page-title', 'Supervisor Assignment')
@section('page-description', 'Manage supervisor assignments for thesis groups')

@section('header-actions')
    <div class="flex items-center space-x-4">
        <!-- Batch Filter -->
        <div class="flex items-center space-x-2">
            <label for="batch-filter" class="text-sm font-medium text-gray-700">Batch:</label>
            <select id="batch-filter" onchange="filterByBatch()" 
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="">All Batches</option>
                @if(isset($availableBatches) && $availableBatches->count() > 0)
                    @foreach($availableBatches as $batch)
                        <option value="{{ $batch }}" {{ request('batch') == $batch ? 'selected' : '' }}>
                            Batch {{ $batch }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>
        
        <!-- Lottery Actions -->
        <div class="flex space-x-2">
            <button onclick="previewLottery()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Preview Lottery
            </button>
            <form id="lottery-form" action="{{ route('advisor.supervisor-assignment.run-lottery') }}" method="POST" class="inline" 
                  onsubmit="return confirm('Are you sure you want to run the lottery assignment? This will assign supervisors to all eligible groups.')">
                @csrf
                <input type="hidden" name="batch" id="lottery-batch" value="{{ request('batch') }}">
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Run Lottery Assignment
                </button>
            </form>
        </div>
    </div>
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

    <!-- Assignment Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <!-- Total Groups -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Groups</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_groups'] }}</p>
                </div>
            </div>
        </div>

        <!-- Assigned Groups -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Assigned</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['assigned_groups'] }}</p>
                </div>
            </div>
        </div>

        <!-- Unassigned Groups -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Unassigned</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['unassigned_groups'] }}</p>
                </div>
            </div>
        </div>

        <!-- Manual Assignments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Manual</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['manual_assignments'] }}</p>
                </div>
            </div>
        </div>

        <!-- Lottery Eligible -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Lottery Eligible</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['lottery_eligible'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Rules Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-blue-800 mb-1">Assignment Rules</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• <strong>Interest Matching:</strong> Supervisors are only assigned to groups with matching areas of interest</li>
                    <li>• <strong>Rank Priority:</strong> Professor > Associate Professor > Assistant Professor > Lecturer</li>
                    <li>• <strong>Manual Override:</strong> Manually assigned groups are excluded from lottery</li>
                    <li>• <strong>Random Selection:</strong> When multiple supervisors of same rank are available, one is selected randomly</li>
                    <li>• <strong>Capacity Limits:</strong> Supervisors cannot exceed their thesis limits</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Groups Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Group Assignments</h3>
        </div>

        @if($groups->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area of Interest</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignment Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($groups as $group)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $group->name }}</div>
                                    <div class="text-xs text-gray-500">Batch {{ $group->batch_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $group->student_count }}/{{ $group->max_students }}</div>
                                    @if($group->students->count() > 0)
                                        <div class="text-xs text-gray-500">
                                            {{ $group->students->pluck('student_name')->take(2)->join(', ') }}
                                            @if($group->students->count() > 2)
                                                + {{ $group->students->count() - 2 }} more
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($group->areaOfInterest)
                                        <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                            {{ $group->areaOfInterest->name }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                            Not Set
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($group->supervisor)
                                        <div class="text-sm font-medium text-gray-900">{{ $group->supervisor->fullname }}</div>
                                        <div class="text-xs text-gray-500">{{ $group->supervisor->designation }}</div>
                                    @else
                                        <span class="text-sm text-gray-500">Unassigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($group->supervisor)
                                        @if($group->is_manual_assignment)
                                            <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                                Manual
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                Lottery
                                            </span>
                                        @endif
                                    @else
                                        @if($group->areaOfInterest)
                                            <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                Eligible
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                Ineligible
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if($group->supervisor)
                                            <!-- Unassign Button -->
                                            <form action="{{ route('advisor.supervisor-assignment.unassign') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="group_id" value="{{ $group->id }}">
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900"
                                                        onclick="return confirm('Are you sure you want to remove the supervisor assignment for {{ $group->name }}?')">
                                                    Unassign
                                                </button>
                                            </form>
                                        @else
                                            <!-- Manual Assign Button -->
                                            @if($group->areaOfInterest)
                                                <button onclick="showManualAssignModal({{ $group->id }}, '{{ $group->name }}', {{ $group->area_of_interest_id }})" 
                                                        class="text-green-600 hover:text-green-900">
                                                    Assign Manually
                                                </button>
                                            @else
                                                <span class="text-gray-400">Set Area First</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No groups found</h3>
                <p class="mt-1 text-sm text-gray-500">Create groups first to manage supervisor assignments.</p>
            </div>
        @endif
    </div>

    <!-- Supervisor Overview -->
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Supervisor Overview</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Designation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thesis Limit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Areas of Interest</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($supervisors as $supervisor)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $supervisor->fullname }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $supervisor->designation === 'Professor' ? 'bg-purple-100 text-purple-800' : 
                                       ($supervisor->designation === 'Associate Professor' ? 'bg-blue-100 text-blue-800' :
                                       ($supervisor->designation === 'Assistant Professor' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ $supervisor->designation }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $supervisor->thesis_limit }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $supervisor->assigned_theses_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm {{ $supervisor->available_slots > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $supervisor->available_slots }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($supervisor->areasOfInterest as $area)
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">
                                            {{ $area->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Manual Assignment Modal -->
    <div id="manualAssignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Manual Supervisor Assignment</h3>
                <form id="manualAssignForm" action="{{ route('advisor.supervisor-assignment.assign-manual') }}" method="POST">
                    @csrf
                    <input type="hidden" id="assign_group_id" name="group_id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Group</label>
                        <p id="assign_group_name" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="assign_supervisor_id" class="block text-sm font-medium text-gray-700 mb-2">Supervisor</label>
                        <select name="supervisor_id" id="assign_supervisor_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            <option value="">Select Supervisor</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="hideManualAssignModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Assign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lottery Preview Modal -->
    <div id="lotteryPreviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Lottery Assignment Preview</h3>
                <div id="lotteryPreviewContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="hideLotteryPreviewModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function filterByBatch() {
        const batchSelect = document.getElementById('batch-filter');
        const selectedBatch = batchSelect.value;
        
        // Update the lottery form hidden input
        document.getElementById('lottery-batch').value = selectedBatch;
        
        // Redirect with batch parameter
        const url = new URL(window.location);
        if (selectedBatch) {
            url.searchParams.set('batch', selectedBatch);
        } else {
            url.searchParams.delete('batch');
        }
        window.location.href = url.toString();
    }

    function showManualAssignModal(groupId, groupName, areaOfInterestId) {
        document.getElementById('assign_group_id').value = groupId;
        document.getElementById('assign_group_name').textContent = groupName;
        
        // Load available supervisors for this area of interest
        console.log('Loading supervisors for area of interest:', areaOfInterestId);
        fetch(`{{ route('advisor.supervisor-assignment.available-supervisors') }}?area_of_interest_id=${areaOfInterestId}`, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                const select = document.getElementById('assign_supervisor_id');
                select.innerHTML = '<option value="">Select Supervisor</option>';
                
                if (data.error) {
                    console.error('Server error:', data);
                    select.innerHTML = '<option value="">Error loading supervisors</option>';
                    alert('Failed to load supervisors: ' + data.message + (data.details ? '\nDetails: ' + JSON.stringify(data.details) : ''));
                    return;
                }
                
                if (data.length === 0) {
                    select.innerHTML = '<option value="">No available supervisors for this area</option>';
                    console.warn('No supervisors found for area of interest:', areaOfInterestId);
                    return;
                }
                
                console.log('Loading', data.length, 'supervisors');
                data.forEach(supervisor => {
                    const option = document.createElement('option');
                    option.value = supervisor.id;
                    option.textContent = `${supervisor.fullname} (${supervisor.designation}) - ${supervisor.available_slots} slots available`;
                    select.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Network or parsing error:', error);
                const select = document.getElementById('assign_supervisor_id');
                select.innerHTML = '<option value="">Failed to load supervisors</option>';
                alert('Failed to load available supervisors. Network error: ' + error.message);
            });
        
        document.getElementById('manualAssignModal').classList.remove('hidden');
    }
    
    function hideManualAssignModal() {
        document.getElementById('manualAssignModal').classList.add('hidden');
    }
    
    function previewLottery() {
        const modal = document.getElementById('lotteryPreviewModal');
        const content = document.getElementById('lotteryPreviewContent');
        
        content.innerHTML = '<div class="text-center py-4">Loading preview...</div>';
        modal.classList.remove('hidden');
        
        // Include batch parameter if selected
        const batchSelect = document.getElementById('batch-filter');
        const selectedBatch = batchSelect.value;
        const url = '{{ route('advisor.supervisor-assignment.preview-lottery') }}' + 
                   (selectedBatch ? '?batch=' + encodeURIComponent(selectedBatch) : '');
        
        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    let html = '<div class="space-y-4">';
                    
                    // Statistics
                    html += '<div class="bg-gray-50 p-4 rounded-lg">';
                    html += '<h4 class="font-medium mb-2">Preview Statistics</h4>';
                    html += `<div class="grid grid-cols-4 gap-4 text-sm">`;
                    html += `<div>Total: ${data.preview.stats.total_groups}</div>`;
                    html += `<div class="text-green-600">Will Assign: ${data.preview.stats.will_be_assigned}</div>`;
                    html += `<div class="text-red-600">No Supervisors: ${data.preview.stats.no_matching_supervisors}</div>`;
                    html += `<div class="text-yellow-600">Will Remain: ${data.preview.stats.will_remain_unassigned}</div>`;
                    html += '</div></div>';
                    
                    // Assignments
                    if (data.preview.assignments.length > 0) {
                        html += '<div><h4 class="font-medium mb-2">Planned Assignments</h4>';
                        html += '<div class="space-y-2">';
                        data.preview.assignments.forEach(assignment => {
                            html += `<div class="flex justify-between items-center p-2 bg-green-50 rounded">`;
                            html += `<span><strong>${assignment.group_name}</strong> (${assignment.area_of_interest})</span>`;
                            html += `<span class="text-sm">${assignment.supervisor_name} (${assignment.designation})</span>`;
                            html += `</div>`;
                        });
                        html += '</div></div>';
                    }
                    
                    // Unassigned
                    if (data.preview.unassigned.length > 0) {
                        html += '<div><h4 class="font-medium mb-2">Will Remain Unassigned</h4>';
                        html += '<div class="space-y-2">';
                        data.preview.unassigned.forEach(group => {
                            html += `<div class="flex justify-between items-center p-2 bg-red-50 rounded">`;
                            html += `<span><strong>${group.group_name}</strong> (${group.area_of_interest})</span>`;
                            html += `<span class="text-sm text-red-600">${group.reason}</span>`;
                            html += `</div>`;
                        });
                        html += '</div></div>';
                    }
                    
                    html += '</div>';
                    content.innerHTML = html;
                } else {
                    content.innerHTML = `<div class="text-red-600 text-center py-4">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading preview:', error);
                content.innerHTML = '<div class="text-red-600 text-center py-4">Failed to load preview</div>';
            });
    }
    
    function hideLotteryPreviewModal() {
        document.getElementById('lotteryPreviewModal').classList.add('hidden');
    }
</script>
@endpush
