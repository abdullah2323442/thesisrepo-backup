@extends('layouts.advisor')

@section('title', 'Admin Group Details - ' . $adminGroup->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-4">
                        <li>
                            <a href="{{ route('advisor.admin-groups.index') }}" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-layer-group mr-1"></i>
                                Admin Groups
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                                <span class="text-gray-500">Batch {{ $adminGroup->batch_number }}</span>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                                <span class="text-gray-900 font-medium">{{ $adminGroup->name }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-3xl font-bold text-gray-900 mt-2">{{ $adminGroup->name }}</h1>
                <p class="mt-2 text-gray-600">
                    Detailed view of admin-created group for Batch {{ $adminGroup->batch_number }}
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                    <i class="fas fa-user-shield mr-2"></i>
                    Admin Created
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($adminGroup->status_badge_color === 'green') bg-green-100 text-green-800
                    @elseif($adminGroup->status_badge_color === 'yellow') bg-yellow-100 text-yellow-800
                    @elseif($adminGroup->status_badge_color === 'blue') bg-blue-100 text-blue-800
                    @else bg-gray-100 text-gray-800
                    @endif">
                    {{ $adminGroup->status_text }}
                </span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Group Overview -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Group Overview</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Basic Information</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Group Name:</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $adminGroup->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Batch:</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $adminGroup->batch_number }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Capacity:</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $adminGroup->student_count }}/{{ $adminGroup->max_students }} students</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Available Slots:</dt>
                                <dd class="text-sm font-medium {{ $adminGroup->available_slots > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $adminGroup->available_slots }} remaining
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-2">Assignment Status</h3>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Area of Interest:</dt>
                                <dd class="text-sm font-medium {{ $adminGroup->area_of_interest_name ? 'text-gray-900' : 'text-gray-500' }}">
                                    {{ $adminGroup->area_of_interest_name ?? 'Not assigned' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Supervisor:</dt>
                                <dd class="text-sm font-medium {{ $adminGroup->supervisor_name ? 'text-gray-900' : 'text-gray-500' }}">
                                    {{ $adminGroup->supervisor_name ?? 'Not assigned' }}
                                </dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Manual Assignment:</dt>
                                <dd class="text-sm font-medium {{ $adminGroup->is_manual_assignment ? 'text-green-600' : 'text-gray-500' }}">
                                    {{ $adminGroup->is_manual_assignment ? 'Yes' : 'No' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Assigned Students</h2>
                    <span class="text-sm text-gray-500">{{ $actualGroup->students->count() }} students</span>
                </div>

                @if($actualGroup->students->count() > 0)
                    <div class="space-y-4">
                        @foreach($actualGroup->students as $student)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-4">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-900">{{ $student->student_name }}</h3>
                                        <p class="text-sm text-gray-600">ID: {{ $student->student_id }}</p>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">
                                    Added {{ $student->created_at->diffForHumans() }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-gray-400"></i>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 mb-2">No Students Assigned</h3>
                        <p class="text-sm text-gray-600">
                            Students will be assigned to this group by administrators.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Areas of Interest -->
            @if($actualGroup->areasOfInterest->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Areas of Interest</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($actualGroup->areasOfInterest as $area)
                            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-lightbulb text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-medium text-gray-900">{{ $area->name }}</h3>
                                    <p class="text-xs text-gray-600">{{ $area->description ?? 'No description' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Creation Details -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Creation Details</h3>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created By</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-user-shield text-purple-500"></i>
                                <span>{{ $adminGroup->created_by_admin_name ?? 'Unknown Admin' }}</span>
                            </div>
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created On</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            {{ $adminGroup->created_at->format('M j, Y \a\t g:i A') }}
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            {{ $adminGroup->updated_at->diffForHumans() }}
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Advisor Detection</dt>
                        <dd class="text-sm text-gray-900 mt-1">
                            @if($adminGroup->advisor_auto_detected)
                                <div class="flex items-center space-x-2 text-blue-600">
                                    <i class="fas fa-magic"></i>
                                    <span>Auto-detected</span>
                                </div>
                            @else
                                <div class="flex items-center space-x-2 text-gray-600">
                                    <i class="fas fa-hand-paper"></i>
                                    <span>Manually assigned</span>
                                </div>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Supervisor Details -->
            @if($actualGroup->supervisor)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Assigned Supervisor</h3>
                    
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-chalkboard-teacher text-green-600"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">{{ $actualGroup->supervisor->fullname }}</h4>
                            <p class="text-sm text-gray-600">{{ $actualGroup->supervisor->designation }}</p>
                        </div>
                    </div>
                    
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Available Slots:</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $actualGroup->supervisor->available_slots }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-600">Rank Priority:</dt>
                            <dd class="text-sm font-medium text-gray-900">{{ $actualGroup->supervisor->rank_priority }}</dd>
                        </div>
                        @if($actualGroup->assigned_at)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-600">Assigned:</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $actualGroup->assigned_at->diffForHumans() }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Information</h3>
                
                <div class="space-y-3">
                    <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-medium text-blue-900">Read-Only View</p>
                            <p class="text-sm text-blue-700">This group was created by an administrator. You can view details but cannot modify it.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3 p-3 bg-yellow-50 rounded-lg">
                        <i class="fas fa-users text-yellow-500 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-medium text-yellow-900">Enhanced Capacity</p>
                            <p class="text-sm text-yellow-700">Admin-created groups can hold up to 4 students (vs. 3 for advisor-created groups).</p>
                        </div>
                    </div>
                    
                    @if($adminGroup->advisor_auto_detected)
                        <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                            <i class="fas fa-magic text-green-500 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-medium text-green-900">Auto-Detection</p>
                                <p class="text-sm text-green-700">You were automatically assigned as advisor when the first student was added to this group.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection