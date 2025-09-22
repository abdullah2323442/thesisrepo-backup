@extends('layouts.co-supervisor')

@section('page-title', 'My Groups')
@section('page-description', 'View and manage your co-supervised groups')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Co-Supervised Groups</h3>
    </div>

    <div class="p-6">
        @if($groups->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Groups Assigned</h3>
                <p class="text-gray-600">You haven't been assigned as co-supervisor to any groups yet.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($groups as $group)
                    <div class="border rounded-lg">
                        <div class="p-4 bg-indigo-50 flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $group->name }}</h4>
                                <p class="text-sm text-gray-600">Batch {{ $group->batch_number }} • Max Students: {{ $group->max_students }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs px-2 py-1 rounded bg-indigo-100 text-indigo-800">Students: {{ $group->students->count() }}</span>
                                <span class="text-xs px-2 py-1 rounded bg-purple-100 text-purple-800 ml-2">Co-Supervisor</span>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        <span class="font-medium">Main Supervisor:</span> 
                                        {{ $group->supervisor ? $group->supervisor->fullname : 'Not assigned' }}
                                    </p>
                                    <p class="text-sm text-gray-700">
                                        <span class="font-medium">Area of Interest:</span> 
                                        {{ optional($group->matchedAreaOfInterest)->name ?? 'Not set' }}
                                    </p>
                                </div>
                                
                                <div class="flex items-center justify-end">
                                    @if($group->co_supervisor_can_manage_meetings)
                                        <div class="flex items-center gap-2 text-green-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            <span class="text-sm font-medium">Meeting Management Enabled</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-2 text-amber-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                            <span class="text-sm font-medium">Meeting Permission Required</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($group->students->isEmpty())
                                <p class="text-gray-500">No students in this group.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="text-left text-gray-600">
                                                <th class="py-2 pr-4">#</th>
                                                <th class="py-2 pr-4">Student Name</th>
                                                <th class="py-2 pr-4">Student ID</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-800">
                                            @foreach($group->students as $index => $student)
                                                <tr class="border-t">
                                                    <td class="py-2 pr-4">{{ $index + 1 }}</td>
                                                    <td class="py-2 pr-4">{{ $student->student_name }}</td>
                                                    <td class="py-2 pr-4">{{ $student->student_id }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <!-- Quick Actions -->
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm text-gray-600">
                                        <span class="font-medium">Role:</span> Co-Supervisor (Support role - cannot approve final projects)
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('co-supervisor.reports.index') }}?group={{ $group->id }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Reports
                                        </a>
                                        @if($group->co_supervisor_can_manage_meetings)
                                            <a href="{{ route('co-supervisor.meetings.index') }}?group={{ $group->id }}" 
                                               class="text-green-600 hover:text-green-800 text-sm font-medium ml-4">
                                                Manage Meetings
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $groups->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Role Information -->
<div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-6">
    <div class="flex items-start gap-4">
        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-amber-800 mb-2">Co-Supervisor Responsibilities</h3>
            <ul class="text-amber-700 space-y-1 text-sm">
                <li>• Support the main supervisor in guiding students</li>
                <li>• Review and provide feedback on student reports</li>
                <li>• Participate in meetings (when permission is granted)</li>
                <li>• Help evaluate student progress and performance</li>
                <li>• <strong>Note:</strong> Only the main supervisor can approve final projects</li>
            </ul>
        </div>
    </div>
</div>
@endsection