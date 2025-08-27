@extends('layouts.advisor')

@section('title', 'Additional Groups Added by Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Additional Groups Added by Admin</h1>
                <p class="mt-2 text-gray-600">
                    View groups that have been created by administrators and assigned to you
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Read-only view
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Filter -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('advisor.admin-groups.index') }}" class="flex items-end space-x-4">
            <div class="flex-1">
                <label for="batch" class="block text-sm font-medium text-gray-700 mb-2">Select Batch</label>
                <select name="batch" id="batch" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="this.form.submit()">
                    <option value="">Choose a batch to view admin-created groups</option>
                    @foreach($availableBatches as $batch)
                        <option value="{{ $batch }}" {{ $selectedBatch == $batch ? 'selected' : '' }}>
                            Batch {{ $batch }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($selectedBatch)
        <!-- Summary Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-layer-group text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Groups</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $adminGroups->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-users text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Students</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $adminGroups->sum('student_count') }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-lightbulb text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">With Areas</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $adminGroups->whereNotNull('area_of_interest_name')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-chalkboard-teacher text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">With Supervisors</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $adminGroups->whereNotNull('supervisor_name')->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if($adminGroups->count() > 0)
            <!-- Groups List -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Admin-Created Groups for Batch {{ $selectedBatch }}
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        These groups were created by administrators and assigned to you
                    </p>
                </div>

                <div class="divide-y divide-gray-200">
                    @foreach($adminGroups as $group)
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <!-- Group Header -->
                                    <div class="flex items-center space-x-3 mb-3">
                                        <h4 class="text-lg font-semibold text-gray-900">{{ $group->name }}</h4>
                                        
                                        <!-- Status Badge -->
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($group->status_badge_color === 'green') bg-green-100 text-green-800
                                            @elseif($group->status_badge_color === 'yellow') bg-yellow-100 text-yellow-800
                                            @elseif($group->status_badge_color === 'blue') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $group->status_text }}
                                        </span>

                                        <!-- Admin Created Badge -->
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-user-shield mr-1"></i>
                                            Admin Created
                                        </span>
                                    </div>

                                    <!-- Group Details Grid -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                        <!-- Students -->
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-users text-gray-400"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Students</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $group->student_count }}/{{ $group->max_students }}
                                                    @if($group->student_count > 0)
                                                        <span class="text-green-600">({{ $group->available_slots }} slots left)</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Area of Interest -->
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-lightbulb text-gray-400"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Area of Interest</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $group->area_of_interest_name ?? 'Not assigned' }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Supervisor -->
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-chalkboard-teacher text-gray-400"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Supervisor</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $group->supervisor_name ?? 'Not assigned' }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Created By -->
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-user-shield text-gray-400"></i>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">Created By</p>
                                                <p class="text-sm text-gray-600">
                                                    {{ $group->created_by_admin_name ?? 'Unknown Admin' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Student Names -->
                                    @if($group->student_names)
                                        <div class="mb-3">
                                            <p class="text-sm font-medium text-gray-900 mb-1">Assigned Students:</p>
                                            <p class="text-sm text-gray-600">{{ $group->formatted_student_names }}</p>
                                        </div>
                                    @endif

                                    <!-- Advisor Detection Status -->
                                    @if($group->advisor_auto_detected)
                                        <div class="flex items-center space-x-2 text-sm text-blue-600">
                                            <i class="fas fa-magic"></i>
                                            <span>Advisor was auto-detected from student assignment</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Button -->
                                <div class="ml-4">
                                    <a href="{{ route('advisor.admin-groups.show', $group->id) }}" 
                                       class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="fas fa-eye mr-2"></i>
                                        View Details
                                    </a>
                                </div>
                            </div>

                            <!-- Creation Timestamp -->
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-xs text-gray-500">
                                    Created on {{ $group->created_at->format('M j, Y \a\t g:i A') }}
                                    @if($group->updated_at->ne($group->created_at))
                                        • Last updated {{ $group->updated_at->diffForHumans() }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- No Groups Message -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-layer-group text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Admin-Created Groups</h3>
                <p class="text-gray-600 mb-4">
                    No groups have been created by administrators for batch {{ $selectedBatch }} yet.
                </p>
                <div class="text-sm text-gray-500">
                    <p>Admin-created groups will appear here when:</p>
                    <ul class="mt-2 space-y-1">
                        <li>• An administrator creates a group and assigns it to you</li>
                        <li>• Students are added to the group (which auto-detects you as the advisor)</li>
                        <li>• The group is configured with areas of interest and supervisors</li>
                    </ul>
                </div>
            </div>
        @endif
    @else
        <!-- No Batch Selected -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-filter text-indigo-600 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Select a Batch</h3>
            <p class="text-gray-600">
                Choose a batch from the dropdown above to view admin-created groups assigned to you.
            </p>
        </div>
    @endif
</div>

<script>
// Auto-refresh summary statistics
document.addEventListener('DOMContentLoaded', function() {
    if ({{ $selectedBatch ? 'true' : 'false' }}) {
        // Optional: Add real-time updates or additional interactivity
        console.log('Admin groups view loaded for batch {{ $selectedBatch }}');
    }
});
</script>
@endsection