@extends('layouts.admin')

@section('page-title', 'Group Management')
@section('page-description', 'Manage student groups and assignments across all batches')

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
        
        <form method="GET" action="{{ route('admin.groups.index') }}" class="flex items-end space-x-4">
            <div class="flex-1">
                <label for="batch" class="block text-sm font-medium text-gray-700 mb-2">Batch (Only batches with existing groups)</label>
                @if(count($availableBatches) > 0)
                    <select name="batch" id="batch" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                        <option value="">Select a batch</option>
                        @foreach($availableBatches as $batch)
                            <option value="{{ $batch }}" {{ $selectedBatch == $batch ? 'selected' : '' }}>
                                Batch {{ $batch }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <div class="w-full border-gray-300 rounded-md shadow-sm bg-gray-100 px-3 py-2 text-gray-500">
                        No batches with groups found
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
                    <p class="text-sm text-gray-600">
                        Total Groups: {{ count($groups) }} | 
                        Eligible Students: {{ count($eligibleStudents) }} (from batches < {{ $selectedBatch }})
                    </p>
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="toggleAddGroup()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                        Add Group
                    </button>
                </div>
            </div>

            <!-- Add Group Form (Hidden by default) -->
            <div id="add-group-form" class="hidden bg-gray-50 p-4 rounded-lg mb-4">
                <h3 class="text-md font-medium text-gray-900 mb-3">Add New Group</h3>
                <p class="text-sm text-gray-600 mb-3">
                    Create a new group for this batch. You can assign students and supervisors after creation.
                </p>
                
                <form method="POST" action="{{ route('admin.groups.create') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="batch" value="{{ $selectedBatch }}">
                    
                    <div>
                        <label for="group_name" class="block text-sm font-medium text-gray-700 mb-2">Group Name</label>
                        <input type="text" name="group_name" id="group_name" required 
                               placeholder="e.g., Group {{ count($groups) + 1 }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="text-xs text-gray-500 mt-1">Suggested: Group {{ count($groups) + 1 }}</p>
                    </div>
                    
                    <div class="bg-blue-50 p-3 rounded-md">
                        <p class="text-sm text-blue-800">
                            <strong>Note:</strong> The advisor will be automatically set when you assign the first student to this group, based on the student's advisor from the API.
                        </p>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                            Create Group
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Advisor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area of Interest</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Co-Supervisor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Panel Members</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($groups as $group)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">{{ $group->name }}</div>
                                            @if($group->created_by_type === 'admin')
                                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    <i class="fas fa-user-shield mr-1"></i>
                                                    Admin Created
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $group->advisor ? $group->advisor->name : 'Not assigned' }}
                                            @if($group->advisor_auto_detected)
                                                <span class="ml-1 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Auto-detected
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            @forelse($group->students as $student)
                                                <div class="flex items-center justify-between bg-gray-50 px-3 py-2 rounded">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $student->student_name }}</div>
                                                        <div class="text-xs text-gray-500">Roll: {{ $student->student_id }}</div>
                                                    </div>
                                                    <form method="POST" action="{{ route('admin.groups.remove-student') }}" class="inline">
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
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            @if($group->areasOfInterest->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($group->areasOfInterest as $area)
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                            {{ $area->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @elseif($group->areaOfInterest)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $group->areaOfInterest->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 italic">Not assigned</span>
                                            @endif
                                        </div>
                                        <button onclick="showAreaOfInterestModal({{ $group->id }}, '{{ $group->name }}', {{ json_encode($group->getAreaOfInterestIds()) }})" 
                                                class="text-blue-600 hover:text-blue-800 text-xs mt-1">
                                            {{ ($group->areasOfInterest->count() > 0 || $group->areaOfInterest) ? 'Change' : 'Assign' }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            @if($group->supervisor)
                                                <div>
                                                    <div class="font-medium">{{ $group->supervisor->fullname }}</div>
                                                    <div class="text-xs text-gray-500">{{ $group->supervisor->designation }}</div>
                                                </div>
                                                <form method="POST" action="{{ route('admin.groups.unassign-supervisor') }}" class="inline mt-1">
                                                    @csrf
                                                    <input type="hidden" name="group_id" value="{{ $group->id }}">
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs"
                                                            onclick="return confirm('Remove supervisor assignment?')">
                                                        Remove
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 italic">Not assigned</span>
                                                <button onclick="showSupervisorModal({{ $group->id }}, '{{ $group->name }}', {{ json_encode($group->getAreaOfInterestIds()) }})" 
                                                        class="text-green-600 hover:text-green-800 text-xs block mt-1">
                                                    Assign
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            @if($group->coSupervisor)
                                                <div>
                                                    <div class="font-medium">{{ $group->coSupervisor->fullname }}</div>
                                                    <div class="text-xs text-gray-500">{{ $group->coSupervisor->designation }}</div>
                                                </div>
                                                <form method="POST" action="{{ route('admin.groups.unassign-co-supervisor') }}" class="inline mt-1">
                                                    @csrf
                                                    <input type="hidden" name="group_id" value="{{ $group->id }}">
                                                    <button type="submit" class="text-red-600 hover:text-red-800 text-xs"
                                                            onclick="return confirm('Remove co-supervisor assignment?')">
                                                        Remove
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 italic">Not assigned</span>
                                                <button onclick="showCoSupervisorModal({{ $group->id }}, '{{ $group->name }}', {{ $group->supervisor_id ?: 'null' }})" 
                                                        class="text-purple-600 hover:text-purple-800 text-xs block mt-1">
                                                    Assign
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            @if($group->panelMembers->count() > 0)
                                                <div class="space-y-1">
                                                    @foreach($group->panelMembers as $panelMember)
                                                        <div class="flex items-center justify-between bg-orange-50 px-2 py-1 rounded">
                                                            <div>
                                                                <div class="text-xs font-medium text-gray-900">{{ $panelMember->supervisor->fullname }}</div>
                                                                <div class="text-xs text-gray-500">{{ $panelMember->supervisor->designation }}</div>
                                                            </div>
                                                            <form method="POST" action="{{ route('admin.groups.unassign-panel-member') }}" class="inline">
                                                                @csrf
                                                                <input type="hidden" name="group_id" value="{{ $group->id }}">
                                                                <input type="hidden" name="supervisor_id" value="{{ $panelMember->supervisor_id }}">
                                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs"
                                                                        onclick="return confirm('Remove this panel member?')">
                                                                    Remove
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic">No panel members</span>
                                            @endif
                                            <button onclick="showPanelMemberModal({{ $group->id }}, '{{ $group->name }}')" 
                                                    class="text-orange-600 hover:text-orange-800 text-xs block mt-1">
                                                {{ $group->panelMembers->count() > 0 ? 'Add More' : 'Assign' }}
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $group->students->count() }}/{{ $group->max_students }}
                                            @if($group->students->count() >= $group->max_students)
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Full
                                                </span>
                                            @else
                                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $group->max_students - $group->students->count() }} slots
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            @if($group->students->count() < $group->max_students && count($eligibleStudents) > 0)
                                                <button onclick="showAssignModal({{ $group->id }}, '{{ $group->name }}')" 
                                                        class="text-green-600 hover:text-green-800 text-sm font-medium">
                                                    <i class="fas fa-user-plus mr-1"></i>
                                                    Assign
                                                </button>
                                            @endif
                                            
                                            <!-- Delete Group Button -->
                                            <button onclick="showDeleteModal({{ $group->id }}, '{{ $group->name }}', {{ $group->students->count() }})" 
                                                    class="text-red-600 hover:text-red-800 text-sm font-medium">
                                                <i class="fas fa-trash-alt mr-1"></i>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Eligible Students -->
            @if(count($eligibleStudents) > 0)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Eligible Students ({{ count($eligibleStudents) }})</h3>
                    <p class="text-sm text-gray-600 mb-4">Students from batches < {{ $selectedBatch }} who are not assigned to any group</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($eligibleStudents as $student)
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="text-sm font-medium text-gray-900">{{ $student['name'] }}</div>
                                <div class="text-xs text-gray-500">Roll: {{ $student['roll'] }}</div>
                                <div class="text-xs text-gray-500">Batch: {{ $student['batch'] }}</div>
                                <div class="text-xs text-gray-500">Advisor: {{ $student['advisor'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    @endif
</div>

<!-- Delete Group Modal -->
<div id="delete-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-lg font-medium text-gray-900">Delete Group</h3>
            </div>
        </div>
        
        <div class="mb-4">
            <p class="text-sm text-gray-600">
                Are you sure you want to delete <strong id="delete-group-name"></strong>?
            </p>
            <div id="delete-warning" class="mt-2 p-3 bg-red-50 rounded-md hidden">
                <p class="text-sm text-red-800">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    This group has <span id="delete-student-count"></span> student(s) assigned. Please remove all students before deleting the group.
                </p>
            </div>
            <div id="delete-info" class="mt-2 p-3 bg-yellow-50 rounded-md">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-1"></i>
                    After deletion, group numbers will be automatically rearranged to maintain sequential order.
                </p>
            </div>
        </div>
        
        <form id="delete-form" method="POST" action="">
            @csrf
            @method('DELETE')
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideDeleteModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button type="submit" id="delete-confirm-btn" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
                    <i class="fas fa-trash-alt mr-1"></i>
                    Delete Group
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Student Modal -->
<div id="assign-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Student to <span id="modal-group-name"></span></h3>
        
        <form method="POST" action="{{ route('admin.groups.assign-student') }}">
            @csrf
            <input type="hidden" name="group_id" id="modal-group-id">
            
            <div class="mb-4">
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-2">Select Student</label>
                <select name="student_id" id="student_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Choose a student</option>
                    @foreach($eligibleStudents->groupBy('batch') as $batch => $students)
                        <optgroup label="Batch {{ $batch }}">
                            @foreach($students as $student)
                                <option value="{{ $student['roll'] }}">
                                    {{ $student['name'] }} ({{ $student['roll'] }}) - {{ $student['advisor'] }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Admin can assign up to 4 students per group
                </p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideAssignModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                    Assign
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Area of Interest Modal -->
<div id="area-of-interest-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg p-6 w-full max-w-md my-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Areas of Interest to <span id="aoi-modal-group-name"></span></h3>
        
        <form method="POST" action="{{ route('admin.groups.assign-area-of-interest') }}">
            @csrf
            <input type="hidden" name="group_id" id="aoi-modal-group-id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Areas of Interest (Multiple Selection Allowed)</label>
                <div class="border border-gray-300 rounded-md p-3 max-h-60 overflow-y-auto">
                    @foreach($areasOfInterest as $area)
                        <div class="flex items-center mb-2">
                            <input type="checkbox" 
                                   name="area_of_interest_ids[]" 
                                   value="{{ $area->id }}" 
                                   id="area_{{ $area->id }}"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="area_{{ $area->id }}" class="ml-2 block text-sm text-gray-900">
                                {{ $area->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-2">
                    Select one or more areas of interest for this group's thesis project. Leave all unchecked to remove all areas.
                </p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideAreaOfInterestModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                    Save Areas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Supervisor Modal -->
<div id="supervisor-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Supervisor to <span id="supervisor-modal-group-name"></span></h3>
        
        <form method="POST" action="{{ route('admin.groups.assign-supervisor') }}">
            @csrf
            <input type="hidden" name="group_id" id="supervisor-modal-group-id">
            
            <div class="mb-4">
                <label for="supervisor_id" class="block text-sm font-medium text-gray-700 mb-2">Select Supervisor</label>
                <select name="supervisor_id" id="supervisor_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Choose a supervisor</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}">
                            {{ $supervisor->fullname }} ({{ $supervisor->designation }}) - {{ $supervisor->available_slots }} slots
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Only supervisors with available thesis slots are shown
                </p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideSupervisorModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                    Assign
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Co-Supervisor Modal -->
<div id="co-supervisor-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Co-Supervisor to <span id="co-supervisor-modal-group-name"></span></h3>
        
        <form method="POST" action="{{ route('admin.groups.assign-co-supervisor') }}">
            @csrf
            <input type="hidden" name="group_id" id="co-supervisor-modal-group-id">
            <input type="hidden" id="co-supervisor-modal-main-supervisor-id">
            
            <div class="mb-4">
                <label for="co_supervisor_id" class="block text-sm font-medium text-gray-700 mb-2">Select Co-Supervisor</label>
                <select name="co_supervisor_id" id="co_supervisor_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Choose a co-supervisor</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}" data-supervisor-id="{{ $supervisor->id }}">
                            {{ $supervisor->fullname }} ({{ $supervisor->designation }}) - {{ $supervisor->available_slots }} slots
                        </option>
                    @endforeach
                </select>
                <div id="co-supervisor-warning" class="hidden mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-xs text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <span id="co-supervisor-warning-text"></span>
                    </p>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Only supervisors with available thesis slots are shown. Cannot assign the same person as main supervisor.
                </p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hideCoSupervisorModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition-colors">
                    Assign Co-Supervisor
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Panel Member Modal -->
<div id="panel-member-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Panel Member to <span id="panel-member-modal-group-name"></span></h3>
        
        <form method="POST" action="{{ route('admin.groups.assign-panel-member') }}">
            @csrf
            <input type="hidden" name="group_id" id="panel-member-modal-group-id">
            <input type="hidden" id="panel-member-modal-main-supervisor-id">
            <input type="hidden" id="panel-member-modal-co-supervisor-id">
            <input type="hidden" id="panel-member-modal-existing-members">
            
            <div class="mb-4">
                <label for="panel_member_id" class="block text-sm font-medium text-gray-700 mb-2">Select Panel Member</label>
                <select name="supervisor_id" id="panel_member_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Choose a panel member</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}" data-supervisor-id="{{ $supervisor->id }}">
                            {{ $supervisor->fullname }} ({{ $supervisor->designation }})
                        </option>
                    @endforeach
                </select>
                <div class="mt-2 p-2 bg-orange-50 border border-orange-200 rounded">
                    <p class="text-xs text-orange-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Note:</strong> Panel members are reviewers/evaluators only. They don't supervise the thesis directly, so thesis limits don't apply.
                    </p>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    Cannot assign the same person as main supervisor, co-supervisor, or existing panel member.
                </p>
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="hidePanelMemberModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 transition-colors">
                    Assign Panel Member
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function toggleAddGroup() {
    const form = document.getElementById('add-group-form');
    form.classList.toggle('hidden');
}

function showDeleteModal(groupId, groupName, studentCount) {
    document.getElementById('delete-group-name').textContent = groupName;
    document.getElementById('delete-student-count').textContent = studentCount;
    document.getElementById('delete-form').action = `/admin/groups/${groupId}`;
    
    const warning = document.getElementById('delete-warning');
    const confirmBtn = document.getElementById('delete-confirm-btn');
    
    if (studentCount > 0) {
        warning.classList.remove('hidden');
        confirmBtn.disabled = true;
        confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
        confirmBtn.innerHTML = '<i class="fas fa-ban mr-1"></i>Cannot Delete';
    } else {
        warning.classList.add('hidden');
        confirmBtn.disabled = false;
        confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        confirmBtn.innerHTML = '<i class="fas fa-trash-alt mr-1"></i>Delete Group';
    }
    
    document.getElementById('delete-modal').classList.remove('hidden');
    document.getElementById('delete-modal').classList.add('flex');
}

function hideDeleteModal() {
    document.getElementById('delete-modal').classList.add('hidden');
    document.getElementById('delete-modal').classList.remove('flex');
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

function showAreaOfInterestModal(groupId, groupName, currentAreaIds) {
    document.getElementById('aoi-modal-group-id').value = groupId;
    document.getElementById('aoi-modal-group-name').textContent = groupName;
    
    // Clear all checkboxes first
    const checkboxes = document.querySelectorAll('input[name="area_of_interest_ids[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Check the boxes for currently assigned areas
    if (currentAreaIds && Array.isArray(currentAreaIds)) {
        currentAreaIds.forEach(areaId => {
            const checkbox = document.getElementById('area_' + areaId);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
    
    document.getElementById('area-of-interest-modal').classList.remove('hidden');
    document.getElementById('area-of-interest-modal').classList.add('flex');
}

function hideAreaOfInterestModal() {
    document.getElementById('area-of-interest-modal').classList.add('hidden');
    document.getElementById('area-of-interest-modal').classList.remove('flex');
}

function showSupervisorModal(groupId, groupName, areaIds) {
    document.getElementById('supervisor-modal-group-id').value = groupId;
    document.getElementById('supervisor-modal-group-name').textContent = groupName;
    document.getElementById('supervisor-modal').classList.remove('hidden');
    document.getElementById('supervisor-modal').classList.add('flex');
}

function hideSupervisorModal() {
    document.getElementById('supervisor-modal').classList.add('hidden');
    document.getElementById('supervisor-modal').classList.remove('flex');
}

function showCoSupervisorModal(groupId, groupName, mainSupervisorId) {
    document.getElementById('co-supervisor-modal-group-id').value = groupId;
    document.getElementById('co-supervisor-modal-group-name').textContent = groupName;
    document.getElementById('co-supervisor-modal-main-supervisor-id').value = mainSupervisorId || '';
    
    // Get the select element
    const selectElement = document.getElementById('co_supervisor_id');
    const warningDiv = document.getElementById('co-supervisor-warning');
    const warningText = document.getElementById('co-supervisor-warning-text');
    
    // Reset all options to be visible
    const options = selectElement.querySelectorAll('option');
    options.forEach(option => {
        if (option.value) {
            option.style.display = 'block';
            option.disabled = false;
            
            // Check if this option is the main supervisor
            if (mainSupervisorId && option.value == mainSupervisorId) {
                option.style.display = 'none';
                option.disabled = true;
            }
        }
    });
    
    // Show warning if no main supervisor is assigned yet
    if (!mainSupervisorId) {
        warningDiv.classList.remove('hidden');
        warningText.textContent = 'No main supervisor assigned yet. It\'s recommended to assign a main supervisor first.';
    } else {
        warningDiv.classList.add('hidden');
    }
    
    // Reset selection
    selectElement.value = '';
    
    document.getElementById('co-supervisor-modal').classList.remove('hidden');
    document.getElementById('co-supervisor-modal').classList.add('flex');
}

function hideCoSupervisorModal() {
    document.getElementById('co-supervisor-modal').classList.add('hidden');
    document.getElementById('co-supervisor-modal').classList.remove('flex');
}

function showPanelMemberModal(groupId, groupName) {
    document.getElementById('panel-member-modal-group-id').value = groupId;
    document.getElementById('panel-member-modal-group-name').textContent = groupName;
    document.getElementById('panel-member-modal').classList.remove('hidden');
    document.getElementById('panel-member-modal').classList.add('flex');
}

function hidePanelMemberModal() {
    document.getElementById('panel-member-modal').classList.add('hidden');
    document.getElementById('panel-member-modal').classList.remove('flex');
}

// Close modals when clicking outside
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteModal();
    }
});

document.getElementById('assign-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAssignModal();
    }
});

document.getElementById('area-of-interest-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAreaOfInterestModal();
    }
});

document.getElementById('supervisor-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideSupervisorModal();
    }
});

document.getElementById('co-supervisor-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideCoSupervisorModal();
    }
});

document.getElementById('panel-member-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hidePanelMemberModal();
    }
});
</script>
@endpush
@endsection