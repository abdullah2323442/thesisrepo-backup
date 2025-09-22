@extends('layouts.panel-member')

@section('page-title', 'My Groups')
@section('page-description', 'View groups where you serve as panel member')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Panel Member Groups</h3>
    </div>

    <div class="p-6">
        @if($groups->isEmpty())
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Groups Assigned</h3>
                <p class="text-gray-600">You haven't been assigned as panel member to any groups yet.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($groups as $group)
                    <div class="border rounded-lg">
                        <div class="p-4 bg-orange-50 flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $group->name }}</h4>
                                <p class="text-sm text-gray-600">Batch {{ $group->batch_number }} • Max Students: {{ $group->max_students }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs px-2 py-1 rounded bg-orange-100 text-orange-800">Students: {{ $group->students->count() }}</span>
                                <span class="text-xs px-2 py-1 rounded bg-green-100 text-green-800 ml-2">Panel Member</span>
                            </div>
                        </div>

                        <div class="p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        <span class="font-medium">Main Supervisor:</span> 
                                        {{ $group->supervisor ? $group->supervisor->fullname : 'Not assigned' }}
                                    </p>
                                    @if($group->coSupervisor)
                                        <p class="text-sm text-gray-700">
                                            <span class="font-medium">Co-Supervisor:</span> 
                                            {{ $group->coSupervisor->fullname }}
                                        </p>
                                    @endif
                                    <p class="text-sm text-gray-700">
                                        <span class="font-medium">Area of Interest:</span> 
                                        {{ optional($group->matchedAreaOfInterest)->name ?? 'Not set' }}
                                    </p>
                                </div>
                                
                                <div class="flex items-center justify-end">
                                    <div class="text-right">
                                        <div class="text-sm text-gray-600">
                                            <span class="font-medium">Panel Members:</span>
                                        </div>
                                        <div class="flex flex-wrap gap-1 mt-1">
                                            @foreach($group->panelMembers as $panelMember)
                                                <span class="text-xs px-2 py-1 rounded bg-orange-100 text-orange-800">
                                                    {{ $panelMember->supervisor->fullname }}
                                                    @if($panelMember->supervisor_id === $supervisor->id)
                                                        <span class="font-bold">(You)</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
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
                                        <span class="font-medium">Role:</span> Panel Member (Evaluation and feedback role)
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('panel-member.reports.index') }}?group={{ $group->id }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Reports
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- Role Information -->
<div class="mt-6 bg-orange-50 border border-orange-200 rounded-lg p-6">
    <div class="flex items-start gap-4">
        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-orange-800 mb-2">Panel Member Responsibilities</h3>
            <ul class="text-orange-700 space-y-1 text-sm">
                <li>• Evaluate student projects and provide expert feedback</li>
                <li>• Review and assess student reports from your area of expertise</li>
                <li>• Participate in defense sessions and project evaluations</li>
                <li>• Provide additional perspectives to support student learning</li>
                <li>• <strong>Note:</strong> Panel members focus on evaluation and feedback, not project management</li>
            </ul>
        </div>
    </div>
</div>
@endsection