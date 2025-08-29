@extends('layouts.student')

@section('page-title', 'My Meetings')
@section('page-description', 'View and download your group meeting records')

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    @if(!$groupInfo || !$groupInfo['hasGroup'])
        <!-- No Group Assigned -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-8 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Group Assigned</h3>
                <p class="text-gray-600">You are not assigned to any group yet. Please contact your advisor.</p>
            </div>
        </div>
    @else
        <!-- Group Information Header -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $groupInfo['group']['name'] }}</h2>
                        <p class="text-gray-600">Batch {{ $groupInfo['group']['batch_number'] }} • {{ $groupInfo['group']['student_count'] }} members</p>
                        
                        @if($groupInfo['supervisor'])
                            <div class="mt-2">
                                <span class="text-sm text-gray-500">Supervisor: </span>
                                <span class="text-sm font-medium text-gray-900">{{ $groupInfo['supervisor']['name'] }}</span>
                            </div>
                        @endif
                        
                        @if($groupInfo['areaOfInterest'])
                            <div class="mt-1">
                                <span class="text-sm text-gray-500">Area of Interest: </span>
                                <span class="text-sm font-medium text-gray-900">{{ $groupInfo['areaOfInterest']['name'] }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Download PDF Button -->
                    <div>
                        <a href="{{ route('student.meetings.pdf') }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download PDF Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Group Members -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Group Members</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($groupInfo['members'] as $member)
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium">
                                {{ substr($member['name'], 0, 1) }}
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $member['name'] }}
                                    @if($member['is_current_user'])
                                        <span class="text-blue-600">(You)</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">{{ $member['student_id'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Meetings List -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Meeting Records</h3>
                    <span class="text-sm text-gray-500">{{ $meetings->count() }} meeting(s) conducted</span>
                </div>
            </div>
            
            @if($meetings->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($meetings as $meeting)
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <div class="flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V6a2 2 0 012-2h4a2 2 0 012 2v1m-6 0h8m-8 0H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-2"></path>
                                            </svg>
                                            {{ $meeting->meeting_date->format('F d, Y') }}
                                        </div>
                                        <div class="ml-4 flex items-center text-sm text-gray-500">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                            {{ $meeting->present_count }}/{{ $meeting->total_count }} present
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">Discussed Topics:</h4>
                                        <p class="text-sm text-gray-700">{{ $meeting->discussed_topics ?? 'No topics recorded' }}</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <h4 class="text-sm font-medium text-gray-900 mb-1">Outcomes:</h4>
                                        <p class="text-sm text-gray-700">{{ $meeting->outcomes ?? 'No outcomes recorded' }}</p>
                                    </div>
                                </div>
                                
                                <!-- Attendance Status -->
                                <div class="ml-6">
                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Attendance:</h4>
                                    <div class="space-y-1">
                                        @php
                                            $attendanceMap = [];
                                            foreach($meeting->attendances as $attendance) {
                                                $attendanceMap[$attendance->groupStudent->student_id] = $attendance->present;
                                            }
                                        @endphp
                                        
                                        @foreach($groupInfo['members'] as $member)
                                            <div class="flex items-center text-sm">
                                                @if(isset($attendanceMap[$member['student_id']]))
                                                    @if($attendanceMap[$member['student_id']])
                                                        <span class="w-4 h-4 text-green-600 mr-2">✓</span>
                                                        <span class="text-gray-700">{{ $member['name'] }}</span>
                                                    @else
                                                        <span class="w-4 h-4 text-red-600 mr-2">✗</span>
                                                        <span class="text-gray-700">{{ $member['name'] }}</span>
                                                    @endif
                                                @else
                                                    <span class="w-4 h-4 text-gray-400 mr-2">-</span>
                                                    <span class="text-gray-500">{{ $member['name'] }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- No Meetings -->
                <div class="p-8 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V6a2 2 0 012-2h4a2 2 0 012 2v1m-6 0h8m-8 0H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-2"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No Meetings Yet</h3>
                    <p class="text-gray-600 mb-4">No meetings have been conducted for your group yet.</p>
                    <p class="text-sm text-gray-500">Your supervisor will schedule and record meetings as they occur.</p>
                </div>
            @endif
        </div>
    @endif
@endsection