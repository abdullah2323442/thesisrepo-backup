@extends('layouts.supervisor')

@section('page-title', 'Meeting Details')
@section('page-description', 'View meeting information and attendance')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Meeting Details</h3>
            <div class="flex space-x-2">
                <a href="{{ route('supervisor.meetings.edit', $meeting) }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                    Edit Meeting
                </a>
                <a href="{{ route('supervisor.meetings.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                    Back to Meetings
                </a>
            </div>
        </div>

        <div class="p-6">
            <!-- Meeting Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Meeting Information</h4>
                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                        <div>
                            <span class="text-sm font-medium text-gray-600">Date:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $meeting->meeting_date->format('F j, Y') }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Group:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $meeting->group->name }} (Batch {{ $meeting->group->batch_number }})</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Total Students:</span>
                            <span class="text-sm text-gray-900 ml-2">{{ $meeting->total_count }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Present:</span>
                            <span class="text-sm text-green-600 ml-2 font-medium">{{ $meeting->present_count }}</span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Absent:</span>
                            <span class="text-sm text-red-600 ml-2 font-medium">{{ $meeting->total_count - $meeting->present_count }}</span>
                        </div>
                    </div>
                </div>

                <!-- Attendance Summary -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Attendance Summary</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="space-y-2">
                            @foreach($meeting->attendances as $attendance)
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $attendance->groupStudent->student_name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $attendance->groupStudent->student_id }}</div>
                                    </div>
                                    <div class="flex items-center">
                                        @if($attendance->present)
                                            <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm text-green-600 ml-2 font-medium">Present</span>
                                        @else
                                            <div class="w-6 h-6 rounded-full bg-red-500 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm text-red-600 ml-2 font-medium">Absent</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Meeting Content -->
            <div class="space-y-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Discussed Topics</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        @if($meeting->discussed_topics)
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $meeting->discussed_topics }}</p>
                        @else
                            <p class="text-sm text-gray-500 italic">No topics recorded</p>
                        @endif
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Outcomes of Discussion</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        @if($meeting->outcomes)
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $meeting->outcomes }}</p>
                        @else
                            <p class="text-sm text-gray-500 italic">No outcomes recorded</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection