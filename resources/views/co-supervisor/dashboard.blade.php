@extends('layouts.co-supervisor')

@section('page-title', 'Co-Supervisor Dashboard')
@section('page-description', 'Welcome to your co-supervisor panel')

@section('content')
<div class="space-y-6">
    <!-- Welcome Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Welcome back, {{ $supervisor->fullname }}</h2>
                <p class="text-gray-600">{{ $supervisor->designation }}</p>
            </div>
            <div class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm font-medium">
                Co-Supervisor
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Groups -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Co-Supervised Groups</p>
                        <p class="text-3xl font-bold text-indigo-600">{{ $totalGroups }}</p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Students -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Students</p>
                        <p class="text-3xl font-bold text-green-600">{{ $totalStudents }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending Reports -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pending Reports</p>
                        <p class="text-3xl font-bold text-orange-600">{{ $pendingReports->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notice -->
        <div class="bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-200 rounded-xl p-6 mb-8">
            <div class="flex items-start gap-4">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-amber-800 mb-2">Co-Supervisor Role Information</h3>
                    <p class="text-amber-700 leading-relaxed">
                        As a co-supervisor, you have the same access and responsibilities as the main supervisor, including reviewing reports, 
                        providing feedback, conducting meetings, and guiding students. However, <strong>only the main supervisor can approve final projects</strong>. 
                        You can access all the same tools and features to support your assigned groups.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Assigned Groups -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-semibold text-gray-900">Co-Supervised Groups</h2>
                        <span class="bg-indigo-100 text-indigo-800 text-sm font-medium px-3 py-1 rounded-full">
                            {{ $totalGroups }} Groups
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    @if($groups->count() > 0)
                        <div class="space-y-4">
                            @foreach($groups as $group)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 mb-1">{{ $group->name }}</h3>
                                            <div class="text-sm text-gray-600 space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    <span>Main Supervisor: {{ $group->supervisor ? $group->supervisor->fullname : 'Not assigned' }}</span>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                                    </svg>
                                                    <span>{{ $group->students->count() }} Students</span>
                                                </div>
                                                @if($group->students->count() > 0)
                                                    <div class="mt-2">
                                                        <div class="text-xs text-gray-500 mb-1">Students:</div>
                                                        <div class="flex flex-wrap gap-1">
                                                            @foreach($group->students as $student)
                                                                <span class="bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                                                                    {{ $student->student_name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 ml-4">
                                            <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2 py-1 rounded">
                                                Batch {{ $group->batch_number }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Groups Assigned</h3>
                            <p class="text-gray-600">You haven't been assigned as co-supervisor to any groups yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="space-y-6">
                <!-- Recent Reports -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-xl font-semibold text-gray-900">Recent Reports</h2>
                    </div>
                    <div class="p-6">
                        @if($recentReports->count() > 0)
                            <div class="space-y-3">
                                @foreach($recentReports as $report)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">{{ $report->project_title ?: 'Report' }}</h4>
                                            <p class="text-sm text-gray-600">{{ $report->group->name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if($report->status === 'approved') bg-green-100 text-green-800
                                                @elseif($report->status === 'under_review') bg-yellow-100 text-yellow-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                                            </span>
                                            <p class="text-xs text-gray-500 mt-1">{{ $report->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-600 text-sm">No recent reports</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Upcoming Meetings -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-xl font-semibold text-gray-900">Upcoming Meetings</h2>
                    </div>
                    <div class="p-6">
                        @if($upcomingMeetings->count() > 0)
                            <div class="space-y-3">
                                @foreach($upcomingMeetings as $meeting)
                                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">{{ $meeting->title }}</h4>
                                            <p class="text-sm text-gray-600">{{ $meeting->group->name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-medium text-blue-600">
                                                {{ $meeting->meeting_date->format('M d, Y') }}
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $meeting->meeting_date->format('l') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-gray-600 text-sm">No upcoming meetings</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white rounded-xl shadow-lg border border-gray-100 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('co-supervisor.reports.index') }}" class="flex items-center gap-3 p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">View Reports</h3>
                        <p class="text-sm text-gray-600">Review student reports</p>
                    </div>
                </a>

                <a href="{{ route('co-supervisor.groups.index') }}" class="flex items-center gap-3 p-4 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-900">Manage Groups</h3>
                        <p class="text-sm text-gray-600">View assigned groups</p>
                    </div>
                </a>

                @php
                    $canManageMeetings = $groups->contains(function($group) use ($supervisor) {
                        return $group->canSupervisorManageMeetings($supervisor->id);
                    });
                @endphp
                
                @if($canManageMeetings)
                    <a href="{{ route('supervisor.meetings.index') }}" class="flex items-center gap-3 p-4 bg-purple-50 hover:bg-purple-100 rounded-lg transition-colors">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-900">Manage Meetings</h3>
                            <p class="text-sm text-gray-600">Schedule and manage meetings</p>
                        </div>
                    </a>
                @else
                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg opacity-60">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-medium text-gray-500">Meeting Management</h3>
                            <p class="text-sm text-gray-400">Permission required from main supervisor</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection