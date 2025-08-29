@extends('layouts.supervisor')

@section('page-title', 'Edit Meeting')
@section('page-description', 'Update meeting information and attendance')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Edit Meeting</h3>
            <a href="{{ route('supervisor.meetings.show', $meeting) }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                Cancel
            </a>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('supervisor.meetings.update', $meeting) }}">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Meeting Details -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4">Meeting Details</h4>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Group</label>
                                <input type="text" value="{{ $meeting->group->name }} (Batch {{ $meeting->group->batch_number }})" 
                                       class="mt-1 w-full border rounded p-2 bg-gray-100" readonly>
                                <p class="text-xs text-gray-500 mt-1">Group cannot be changed</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="meeting_date" 
                                       value="{{ $meeting->meeting_date->format('Y-m-d') }}" 
                                       class="mt-1 w-full border rounded p-2" required>
                                @error('meeting_date')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Discussed Topics</label>
                                <textarea name="discussed_topics" class="mt-1 w-full border rounded p-2" rows="4">{{ old('discussed_topics', $meeting->discussed_topics) }}</textarea>
                                @error('discussed_topics')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Outcomes</label>
                                <textarea name="outcomes" class="mt-1 w-full border rounded p-2" rows="4">{{ old('outcomes', $meeting->outcomes) }}</textarea>
                                @error('outcomes')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <!-- Attendance -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-700 mb-4">Attendance</h4>
                        <div class="space-y-3">
                            @foreach($meeting->group->students as $index => $student)
                                @php
                                    $attendance = $meeting->attendances->where('group_student_id', $student->id)->first();
                                    $isPresent = $attendance ? $attendance->present : false;
                                @endphp
                                <div class="flex items-center justify-between border rounded p-3">
                                    <div>
                                        <div class="font-medium">{{ $student->student_name }}</div>
                                        <div class="text-sm text-gray-500">ID: {{ $student->student_id }}</div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <input type="hidden" name="attendance[{{ $index }}][group_student_id]" value="{{ $student->id }}">
                                        <input type="hidden" name="attendance[{{ $index }}][present]" value="{{ $isPresent ? '1' : '0' }}" id="present_{{ $index }}">
                                        
                                        <button type="button" onclick="toggleAttendance({{ $index }}, true)" 
                                                class="attendance-btn present-btn w-8 h-8 rounded-full border-2 border-green-500 flex items-center justify-center hover:bg-green-50 transition-colors {{ $isPresent ? 'bg-green-500' : '' }}" 
                                                id="present_btn_{{ $index }}">
                                            <svg class="w-5 h-5 {{ $isPresent ? 'text-white' : 'text-green-500 hidden' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                        
                                        <button type="button" onclick="toggleAttendance({{ $index }}, false)" 
                                                class="attendance-btn absent-btn w-8 h-8 rounded-full border-2 border-red-500 flex items-center justify-center hover:bg-red-50 transition-colors {{ !$isPresent ? 'bg-red-500' : '' }}" 
                                                id="absent_btn_{{ $index }}">
                                            <svg class="w-4 h-4 {{ !$isPresent ? 'text-white' : 'text-red-500 hidden' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('attendance')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200 flex justify-end space-x-3">
                    <a href="{{ route('supervisor.meetings.show', $meeting) }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                        Update Meeting
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAttendance(index, isPresent) {
  const hiddenInput = document.getElementById(`present_${index}`);
  const presentBtn = document.getElementById(`present_btn_${index}`);
  const absentBtn = document.getElementById(`absent_btn_${index}`);
  const presentIcon = presentBtn.querySelector('svg');
  const absentIcon = absentBtn.querySelector('svg');
  
  if (isPresent) {
    // Mark as present
    hiddenInput.value = '1';
    presentBtn.classList.add('bg-green-500');
    presentBtn.classList.remove('hover:bg-green-50');
    presentIcon.classList.remove('hidden');
    presentIcon.classList.add('text-white');
    presentIcon.classList.remove('text-green-500');
    
    absentBtn.classList.remove('bg-red-500');
    absentBtn.classList.add('hover:bg-red-50');
    absentIcon.classList.add('hidden');
    absentIcon.classList.add('text-red-500');
    absentIcon.classList.remove('text-white');
  } else {
    // Mark as absent
    hiddenInput.value = '0';
    absentBtn.classList.add('bg-red-500');
    absentBtn.classList.remove('hover:bg-red-50');
    absentIcon.classList.remove('hidden');
    absentIcon.classList.add('text-white');
    absentIcon.classList.remove('text-red-500');
    
    presentBtn.classList.remove('bg-green-500');
    presentBtn.classList.add('hover:bg-green-50');
    presentIcon.classList.add('hidden');
    presentIcon.classList.add('text-green-500');
    presentIcon.classList.remove('text-white');
  }
}
</script>
@endpush