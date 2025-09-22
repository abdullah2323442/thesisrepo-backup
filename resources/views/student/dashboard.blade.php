@extends('layouts.student')

@section('page-title', 'Student Dashboard')
@section('page-description')
Welcome back, {{ $userType === 'model' ? ($user->name ?? 'Student') : ($user['Name'] ?? 'Student') }}
@endsection

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

    <!-- Student Profile Overview -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <div class="flex items-center space-x-6">
                <!-- Profile Image -->
                <div class="flex-shrink-0">
                    @php
                        $profileImage = '';
                        $userName = '';
                        
                        if ($userType === 'model') {
                            $profileImage = $user->profile_image ?? $user->profile_image_url ?? '';
                            $userName = $user->name ?? 'User';
                        } else {
                            $profileImage = $user['Url'] ?? '';
                            $userName = $user['Name'] ?? 'User';
                        }
                        
                        if (empty($profileImage)) {
                            $profileImage = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=3b82f6&color=ffffff';
                        }
                    @endphp
                    
                    <img class="h-24 w-24 rounded-full object-cover border-4 border-blue-200" 
                         src="{{ $profileImage }}" 
                         alt="Profile Image">
                </div>
                
                <!-- Basic Info -->
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900">
                        {{ $userType === 'model' ? ($user->name ?? 'N/A') : ($user['Name'] ?? 'N/A') }}
                    </h3>
                    <p class="text-lg text-gray-600">
                        Roll: {{ $userType === 'model' ? ($user->roll ?? 'N/A') : ($user['Roll'] ?? 'N/A') }}
                    </p>
                    <p class="text-sm text-gray-500">
                        {{ $userType === 'model' ? ($user->login_type ?? 'Student') : ($user['LoginType'] ?? 'Student') }} • 
                        Batch {{ $userType === 'model' ? ($user->batch ?? 'N/A') : ($user['Batch'] ?? 'N/A') }}
                    </p>
                </div>
                
                <!-- Status Badge -->
                <div class="text-center">
                    <div class="bg-blue-100 text-blue-800 text-lg font-bold py-3 px-6 rounded-lg">
                        @php
                            $status = $userType === 'model' ? ($user->status ?? 'Active') : ($user['Status'] ?? 'Active');
                        @endphp
                        {{ $status }}
                    </div>
                    <p class="text-sm text-gray-600 mt-1">Status</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Batch -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Batch</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $userType === 'model' ? ($user->batch ?? 'N/A') : ($user['Batch'] ?? 'N/A') }}</p>
                </div>
            </div>
        </div>

        <!-- Department -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Department</p>
                    <p class="text-xl font-bold text-gray-900">{{ $userType === 'model' ? ($user->department_name ?? 'CSE') : ($user['DepartmentName'] ?? 'CSE') }}</p>
                </div>
            </div>
        </div>

        <!-- Group Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Group Status</p>
                    <p class="text-xl font-bold text-gray-900">
                        @if($groupInfo && $groupInfo['hasGroup'])
                            {{ $groupInfo['group']['name'] }}
                        @else
                            Not Assigned
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Supervisor Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Supervisor</p>
                    <p class="text-xl font-bold text-gray-900">
                        @if($groupInfo && $groupInfo['hasGroup'] && $groupInfo['supervisor'])
                            Assigned
                        @else
                            Not Assigned
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- My Group Section -->
        <div id="group-section" class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    My Group
                </h3>
            </div>
            <div class="p-6">
                @if($groupInfo && $groupInfo['hasGroup'])
                    <div class="mb-4">
                        <h4 class="text-lg font-semibold text-gray-900">{{ $groupInfo['group']['name'] }}</h4>
                        <p class="text-sm text-gray-600">Batch {{ $groupInfo['group']['batch_number'] }} • {{ $groupInfo['group']['student_count'] }}/{{ $groupInfo['group']['max_students'] }} members</p>
                    </div>
                    
                    <div class="space-y-3">
                        <h5 class="font-medium text-gray-900">Group Members:</h5>
                        @foreach($groupInfo['members'] as $member)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                        {{ substr($member['name'], 0, 1) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $member['name'] }}
                                            @if($member['is_current_user'])
                                                <span class="text-blue-600">(You)</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500">Roll: {{ $member['student_id'] }}</p>
                                    </div>
                                </div>
                                @if($member['is_current_user'])
                                    <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">Me</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-gray-500">{{ $groupInfo['message'] ?? 'No group information available.' }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Area of Interest Section -->
        <div id="area-section" class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    Area of Interest
                </h3>
            </div>
            <div class="p-6">
                @if($groupInfo && $groupInfo['hasGroup'] && $groupInfo['areaOfInterest'])
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <h4 class="text-lg font-semibold text-green-800">{{ $groupInfo['areaOfInterest']['name'] }}</h4>
                        </div>
                        @if($groupInfo['areaOfInterest']['description'])
                            <p class="text-sm text-green-700">{{ $groupInfo['areaOfInterest']['description'] }}</p>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <p class="text-gray-500">No area of interest assigned yet.</p>
                        <p class="text-xs text-gray-400 mt-1">Your advisor will assign an area of interest to your group.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Supervisor Section -->
    <div id="supervisor-section" class="bg-white rounded-lg shadow mt-6">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Supervision Team
            </h3>
        </div>
        <div class="p-6">
            @if($groupInfo && $groupInfo['hasGroup'] && $groupInfo['supervisor'])
                <div class="space-y-4">
                    <!-- Main Supervisor -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-lg font-semibold text-blue-900">Main Supervisor</h4>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Primary</span>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                {{ substr($groupInfo['supervisor']['name'], 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <h5 class="text-xl font-semibold text-blue-900">{{ $groupInfo['supervisor']['name'] }}</h5>
                                <p class="text-blue-700">{{ $groupInfo['supervisor']['designation'] }}</p>
                                <p class="text-sm text-blue-600">{{ $groupInfo['supervisor']['department'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-blue-600">Contact</p>
                                <p class="text-sm font-medium text-blue-800">{{ $groupInfo['supervisor']['email'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Co-Supervisor -->
                    @if($groupInfo['coSupervisor'])
                        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-lg font-semibold text-purple-900">Co-Supervisor</h4>
                                <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Secondary</span>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                    {{ substr($groupInfo['coSupervisor']['name'], 0, 1) }}
                                </div>
                                <div class="flex-1">
                                    <h5 class="text-xl font-semibold text-purple-900">{{ $groupInfo['coSupervisor']['name'] }}</h5>
                                    <p class="text-purple-700">{{ $groupInfo['coSupervisor']['designation'] }}</p>
                                    <p class="text-sm text-purple-600">{{ $groupInfo['coSupervisor']['department'] }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-purple-600">Contact</p>
                                    <p class="text-sm font-medium text-purple-800">{{ $groupInfo['coSupervisor']['email'] }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Panel Members -->
                        @if(count($groupInfo['panelMembers']) > 0)
                            @foreach($groupInfo['panelMembers'] as $panelMember)
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-lg font-semibold text-orange-900">Panel Member</h4>
                                        <span class="bg-orange-100 text-orange-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Advisory</span>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                            {{ substr($panelMember['name'], 0, 1) }}
                                        </div>
                                        <div class="flex-1">
                                            <h5 class="text-xl font-semibold text-orange-900">{{ $panelMember['name'] }}</h5>
                                            <p class="text-orange-700">{{ $panelMember['designation'] }}</p>
                                            <p class="text-sm text-orange-600">{{ $panelMember['department'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-orange-600">Contact</p>
                                            <p class="text-sm font-medium text-orange-800">{{ $panelMember['email'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif

                        <!-- Important Note -->
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-amber-800">Supervision Team</p>
                                    <p class="text-sm text-amber-700 mt-1">
                                        @if(count($groupInfo['panelMembers']) > 0)
                                            Your supervision team includes main supervisor, co-supervisor (if assigned), and panel members. 
                                            All can guide you and provide feedback, but <strong>only your main supervisor can approve final projects</strong>.
                                        @else
                                            Both supervisors can guide you, review your work, and provide feedback. However, 
                                            <strong>only your main supervisor can approve final projects</strong>.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <p class="text-gray-500">No supervisor assigned yet.</p>
                    <p class="text-xs text-gray-400 mt-1">Your supervisor will be assigned after the lottery process.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Academic Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <!-- Academic Details -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Academic Information</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Department:</span>
                        <span class="font-medium">
                            {{ $userType === 'model' ? ($user->department_name ?? 'N/A') : ($user['DepartmentName'] ?? 'N/A') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Program:</span>
                        <span class="font-medium">
                            {{ $userType === 'model' ? ($user->program_name ?? 'N/A') : ($user['ProgramName'] ?? 'N/A') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Batch:</span>
                        <span class="font-medium">
                            {{ $userType === 'model' ? ($user->batch ?? 'N/A') : ($user['Batch'] ?? 'N/A') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Advisor:</span>
                        <span class="font-medium">
                            @if($groupInfo && $groupInfo['hasGroup'] && $groupInfo['advisor'])
                                {{ $groupInfo['advisor']['name'] }}
                            @else
                                {{ $userType === 'model' ? ($user->advisor ?? 'N/A') : ($user['Advisor'] ?? 'N/A') }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Contact Information</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Email:</span>
                        <span class="font-medium">
                            {{ $userType === 'model' ? ($user->email ?? 'N/A') : ($user['Email'] ?? 'N/A') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Phone:</span>
                        <span class="font-medium">
                            {{ $userType === 'model' ? ($user->phone ?? 'N/A') : ($user['Phone'] ?? 'N/A') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Status:</span>
                        <span class="font-medium">
                            @php
                                $status = $userType === 'model' ? ($user->status ?? 'Active') : ($user['Status'] ?? 'Active');
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $status }}
                            </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

            