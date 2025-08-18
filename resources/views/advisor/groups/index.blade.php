@extends('layouts.advisor')

@section('page-title', 'Group Management')
@section('page-description', 'Manage student groups for thesis projects')

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Batch Selection -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Select Batch</h2>
        
        <form method="GET" action="{{ route('advisor.groups.index') }}" class="flex items-end space-x-4">
            <div class="flex-1">
                <label for="batch" class="block text-sm font-medium text-gray-700 mb-2">Batch</label>
                @if(count($batches) > 0)
                    <select name="batch" id="batch" class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500" onchange="this.form.submit()">
                        <option value="">Select a batch</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch }}" {{ $selectedBatch == $batch ? 'selected' : '' }}>
                                Batch {{ $batch }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <div class="w-full border-gray-300 rounded-md shadow-sm bg-gray-100 px-3 py-2 text-gray-500">
                        No active batches found where you are assigned as advisor
                    </div>
                @endif
            </div>
        </form>
    </div>

    @if($selectedBatch)
        <!-- Batch Info and Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Batch {{ $selectedBatch }} Groups</h2>
                    <p class="text-sm text-gray-600">Total Students: {{ count($students) }} | Groups: {{ count($groups) }}</p>
                </div>
                
                <div class="flex space-x-3">
                    @if(count($groups) == 0)
                        <form method="POST" action="{{ route('advisor.groups.create') }}" class="inline">
                            @csrf
                            <input type="hidden" name="batch" value="{{ $selectedBatch }}">
                            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                                Create Groups
                            </button>
                        </form>
                    @else
                        <button onclick="toggleAddGroup()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                            Add Group
                        </button>
                        
                        @php
                            $groupsWithAOI = $groups->filter(function($group) {
                                return $group->area_of_interest_id !== null;
                            });
                        @endphp
                        
                        @if($groupsWithAOI->count() > 0)
                            <form method="POST" action="{{ route('advisor.groups.unassign-all-areas-of-interest') }}" class="inline">
                                @csrf
                                <input type="hidden" name="batch" value="{{ $selectedBatch }}">
                                <button type="submit" 
                                        class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors"
                                        onclick="return confirm('This will remove area of interest assignments from all groups in this batch. Are you sure?')">
                                    Unassign All Areas of Interest
                                </button>
                            </form>
                        @endif
                        
                        <form method="POST" action="{{ route('advisor.groups.remove-all-groups') }}" class="inline">
                            @csrf
                            <input type="hidden" name="batch" value="{{ $selectedBatch }}">
                            <button type="submit" 
                                    class="bg-red-800 text-white px-4 py-2 rounded-md hover:bg-red-900 transition-colors"
                                    onclick="return confirm('This will permanently delete ALL groups and student assignments in this batch. This action cannot be undone. Are you sure?')">
                                Remove All Groups
                            </button>
                        </form>
                    @endif
                    
                    <button onclick="toggleExcelUpload()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                        Upload Excel
                    </button>
                </div>
            </div>

            <!-- Excel Upload Form (Hidden by default) -->
            <div id="excel-upload-form" class="hidden bg-gray-50 p-4 rounded-lg mb-4">
                <h3 class="text-md font-medium text-gray-900 mb-3">Upload Excel File</h3>
                <p class="text-sm text-gray-600 mb-3">
                    Excel format: Any column with Student ID (Roll) and Group Name/Number. Groups will be created automatically if they don't exist.
                    @if(count($groups) > 0)
                        <a href="{{ route('advisor.groups.download-template', ['batch' => $selectedBatch]) }}" 
                           class="ml-2 text-blue-600 hover:text-blue-800 underline">
                            Download Template
                        </a>
                    @endif
                </p>
                
                <form method="POST" action="{{ route('advisor.groups.upload-excel') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="hidden" name="batch" value="{{ $selectedBatch }}">
                    
                    <div>
                        <input type="file" name="excel_file" accept=".xlsx,.xls" required 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                            Upload & Assign
                        </button>
                        <button type="button" onclick="toggleExcelUpload()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            <!-- Add Group Form (Hidden by default) -->
            <div id="add-group-form" class="hidden bg-gray-50 p-4 rounded-lg mb-4">
                <h3 class="text-md font-medium text-gray-900 mb-3">Add New Group</h3>
                <p class="text-sm text-gray-600 mb-3">
                    Add an additional group to this batch. The system will suggest the next available group number.
                </p>
                
                <form method="POST" action="{{ route('advisor.groups.add') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="batch" value="{{ $selectedBatch }}">
                    
                    <div>
                        <label for="group_name" class="block text-sm font-medium text-gray-700 mb-2">Group Name</label>
                        <input type="text" name="group_name" id="group_name" required 
                               placeholder="e.g., Group {{ count($groups) + 1 }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500">
                        <p class="text-xs text-gray-500 mt-1">Suggested: Group {{ count($groups) + 1 }}</p>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                            Add Group
                        </button>
                        <button type="button" onclick="toggleAddGroup()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(count($groups) > 0)
            <!-- Groups Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Groups Overview</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area of Interest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($groups as $group)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $group->name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            @forelse($group->students as $student)
                                                <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $student->student_name }}</div>
                                                        <div class="text-xs text-gray-500">Roll: {{ $student->student_id }}</div>
                                                    </div>
                                                    <form method="POST" action="{{ route('advisor.groups.remove-student') }}" class="inline">
                                                        @csrf
                                                        <input type="hidden" name="group_student_id" value="{{ $student->id }}">
                                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs" 
                                                                onclick="return confirm('Remove this student from the group?')">
                                                            Remove
                                                        </button>
                                                    </form>
                                                </div>
                                            @empty
                                                <div class="text-sm text-gray-500 italic">No students assigned</div>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            @if($group->areaOfInterest)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $group->areaOfInterest->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 italic">Not assigned</span>
                                            @endif
                                        </div>
                                        <button onclick="showAreaOfInterestModal({{ $group->id }}, '{{ $group->name }}', {{ $group->area_of_interest_id ?? 'null' }})" 
                                                class="text-blue-600 hover:text-blue-800 text-xs mt-1">
                                            {{ $group->areaOfInterest ? 'Change' : 'Assign' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $group->students->count() }}/{{ $group->max_students }}
                                            @if($group->isFull())
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Full
                                                </span>
                                            @else
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $group->available_slots }} slots
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(!$group->isFull() && ((isset($allAvailableStudents) && count($allAvailableStudents) > 0) || count($unassignedStudents) > 0))
                                            <button onclick="showAssignModal({{ $group->id }}, '{{ $group->name }}')" 
                                                    class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                Assign Student
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Unassigned Students -->
            @if(count($unassignedStudents) > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Unassigned Students ({{ count($unassignedStudents) }})</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($unassignedStudents as $student)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-900">{{ $student['name'] }}</div>
                                <div class="text-xs text-gray-500">Roll: {{ $student['roll'] }}</div>
                                <div class="text-xs text-gray-500">Advisor: {{ $student['advisor'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    @endif
</div>

<!-- Assign Student Modal -->
<div id="assign-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Student to <span id="modal-group-name"></span></h3>
        
        <form method="POST" action="{{ route('advisor.groups.assign-student') }}">
            @csrf
            <input type="hidden" name="group_id" id="modal-group-id">
            
            <div class="mb-4">
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Select Student</label>
                <select name="student_id" id="student_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-green-500 focus:ring-green-500">
                    <option value="">Choose a student</option>
                    
                    @if(isset($allAvailableStudents) && count($allAvailableStudents) > 0)
                        @php
                            $currentBatchStudents = collect($allAvailableStudents)->where('is_current_batch', true);
                            $otherBatchStudents = collect($allAvailableStudents)->where('is_current_batch', false)->groupBy('batch');
                        @endphp
                        
                        @if($currentBatchStudents->count() > 0)
                            <optgroup label="Current Batch ({{ $selectedBatch }})">
                                @foreach($currentBatchStudents as $student)
                                    <option value="{{ $student['roll'] }}">
                                        {{ $student['name'] }} ({{ $student['roll'] }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        
                        @foreach($otherBatchStudents as $batch => $students)
                            <optgroup label="Batch {{ $batch }}">
                                @foreach($students as $student)
                                    <option value="{{ $student['roll'] }}">
                                        {{ $student['name'] }} ({{ $student['roll'] }}) - Batch {{ $student['batch'] }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    @else
                        @foreach($unassignedStudents as $student)
                            <option value="{{ $student['roll'] }}">
                                {{ $student['name'] }} ({{ $student['roll'] }})
                            </option>
                        @endforeach
                    @endif
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    You can assign students from any batch where you are the advisor
                </p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideAssignModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
                    Assign
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Area of Interest Modal -->
<div id="area-of-interest-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Area of Interest to <span id="aoi-modal-group-name"></span></h3>
        
        <form method="POST" action="{{ route('advisor.groups.assign-area-of-interest') }}">
            @csrf
            <input type="hidden" name="group_id" id="aoi-modal-group-id">
            
            <div class="mb-4">
                <label for="area_of_interest_id" class="block text-sm font-medium text-gray-700 mb-2">Select Area of Interest</label>
                <select name="area_of_interest_id" id="area_of_interest_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">No area of interest</option>
                    @foreach($areasOfInterest as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Select an area of interest for this group's thesis project
                </p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideAreaOfInterestModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Assign
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleExcelUpload() {
    const form = document.getElementById('excel-upload-form');
    form.classList.toggle('hidden');
    // Hide add group form if it's open
    const addGroupForm = document.getElementById('add-group-form');
    if (!addGroupForm.classList.contains('hidden')) {
        addGroupForm.classList.add('hidden');
    }
}

function toggleAddGroup() {
    const form = document.getElementById('add-group-form');
    form.classList.toggle('hidden');
    // Hide excel upload form if it's open
    const excelForm = document.getElementById('excel-upload-form');
    if (!excelForm.classList.contains('hidden')) {
        excelForm.classList.add('hidden');
    }
}

function showAssignModal(groupId, groupName) {
    document.getElementById('modal-group-id').value = groupId;
    document.getElementById('modal-group-name').textContent = groupName;
    document.getElementById('assign-modal').classList.remove('hidden');
    document.getElementById('assign-modal').classList.add('flex');
}

function hideAssignModal() {
    document.getElementById('assign-modal').classList.add('hidden');
    document.getElementById('assign-modal').classList.remove('flex');
}

function showAreaOfInterestModal(groupId, groupName, currentAreaOfInterestId) {
    document.getElementById('aoi-modal-group-id').value = groupId;
    document.getElementById('aoi-modal-group-name').textContent = groupName;
    
    // Set the current area of interest if one is assigned
    const select = document.getElementById('area_of_interest_id');
    select.value = currentAreaOfInterestId || '';
    
    document.getElementById('area-of-interest-modal').classList.remove('hidden');
    document.getElementById('area-of-interest-modal').classList.add('flex');
}

function hideAreaOfInterestModal() {
    document.getElementById('area-of-interest-modal').classList.add('hidden');
    document.getElementById('area-of-interest-modal').classList.remove('flex');
}

// Close modal when clicking outside
document.getElementById('assign-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAssignModal();
    }
});

// Close area of interest modal when clicking outside
document.getElementById('area-of-interest-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAreaOfInterestModal();
    }
});
</script>
@endpush
@endsection