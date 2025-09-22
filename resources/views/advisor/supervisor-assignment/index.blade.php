@extends('layouts.advisor')

@section('page-title', 'Supervisor Assignment')
@section('page-description', 'Manage supervisor assignments for thesis groups')

@section('header-actions')
    <div class="flex items-center space-x-4">
        <!-- Batch Filter -->
        <div class="flex items-center space-x-2">
            <label for="batch-filter" class="text-sm font-medium text-gray-700">Batch:</label>
            <select id="batch-filter" onchange="filterByBatch()" 
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                <option value="">All Batches</option>
                @if(isset($availableBatches) && $availableBatches->count() > 0)
                    @foreach($availableBatches as $batch)
                        <option value="{{ $batch }}" {{ request('batch') == $batch ? 'selected' : '' }}>
                            Batch {{ $batch }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        <!-- Lottery Mode Checkboxes -->
        <div class="flex items-center space-x-4">
            <span class="text-sm font-medium text-gray-700">Assignment Criteria:</span>
            <label class="flex items-center space-x-2">
                <input type="checkbox" id="use-aoi" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                <span class="text-sm text-gray-700">Area of Interest</span>
            </label>
            <label class="flex items-center space-x-2">
                <input type="checkbox" id="use-ranking" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <span class="text-sm text-gray-700">Ranking Priority</span>
            </label>
        </div>
        
        <!-- Lottery Actions -->
        <div class="flex space-x-2">
            <button onclick="previewLottery()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Preview Lottery
            </button>
            <form id="lottery-form" action="{{ route('advisor.supervisor-assignment.run-lottery') }}" method="POST" class="inline" 
                  onsubmit="return false;">
                @csrf
                <input type="hidden" name="batch" id="lottery-batch" value="{{ request('batch') }}">
                <input type="hidden" name="use_aoi" id="lottery-use-aoi" value="1">
                <input type="hidden" name="use_ranking" id="lottery-use-ranking" value="0">
                <button type="button" onclick="showLotteryConfirmModal()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Run Lottery Assignment
                </button>
            </form>
            <form id="unassign-all-form" action="{{ route('advisor.supervisor-assignment.unassign-all') }}" method="POST" class="inline"
                  onsubmit="return confirm('This will remove supervisor assignments from all matching groups. Continue?')">
                @csrf
                <input type="hidden" name="batch" id="unassign-all-batch" value="{{ request('batch') }}">
                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Unassign All
                </button>
            </form>
        </div>
    </div>
@endsection

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm" role="alert">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        @if(str_contains(session('error'), 'co-supervisor'))
            <div class="mb-6 bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg shadow-sm" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-amber-800">Co-Supervisor Conflict</h3>
                        <div class="mt-2 text-sm text-amber-700">
                            <p>{{ session('error') }}</p>
                            <div class="mt-2 p-2 bg-amber-100 rounded">
                                <p class="text-xs">
                                    <strong>Note:</strong> Co-supervisors are assigned exclusively by administrators. 
                                    A person cannot serve as both main supervisor and co-supervisor for the same group.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-sm" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Assignment Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <!-- Total Groups -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Groups</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_groups'] }}</p>
                </div>
            </div>
        </div>

        <!-- Assigned Groups -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Assigned</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['assigned_groups'] }}</p>
                </div>
            </div>
        </div>

        <!-- Unassigned Groups -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Unassigned</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['unassigned_groups'] }}</p>
                </div>
            </div>
        </div>

        <!-- Manual Assignments -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Manual</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['manual_assignments'] }}</p>
                </div>
            </div>
        </div>

        <!-- Lottery Eligible -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Lottery Eligible</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['lottery_eligible'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Rules Info -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-blue-800 mb-2">Intelligent Assignment System</h3>
                
                <div class="space-y-3">
                    <!-- Area of Interest Only Mode -->
                    <div class="bg-white bg-opacity-60 rounded p-2">
                        <h4 class="text-xs font-semibold text-blue-800 mb-1">📍 Area of Interest Only (1 checkbox)</h4>
                        <ul class="text-xs text-blue-700 space-y-0.5 ml-3">
                            <li>• <strong>Pure Randomization:</strong> Completely random selection from matching supervisors</li>
                            <li>• <strong>Smart Rotation:</strong> Never assigns same supervisor twice in a row to same group</li>
                            <li>• <strong>Different Every Time:</strong> Each lottery run produces different results</li>
                        </ul>
                    </div>
                    
                    <!-- Ranking Priority Only Mode -->
                    <div class="bg-white bg-opacity-60 rounded p-2">
                        <h4 class="text-xs font-semibold text-blue-800 mb-1">🎯 Ranking Priority Only (1 checkbox)</h4>
                        <ul class="text-xs text-blue-700 space-y-0.5 ml-3">
                            <li>• <strong>Perfect Round-Robin:</strong> No supervisor gets 2 groups before everyone gets 1</li>
                            <li>• <strong>Rank Order:</strong> Professor → Associate → Assistant → Lecturer</li>
                            <li>• <strong>Fair Distribution:</strong> Equal load across all supervisors</li>
                        </ul>
                    </div>
                    
                    <!-- Combined Mode -->
                    <div class="bg-white bg-opacity-60 rounded p-2">
                        <h4 class="text-xs font-semibold text-blue-800 mb-1">⚡ Both Selected (2 checkboxes)</h4>
                        <ul class="text-xs text-blue-700 space-y-0.5 ml-3">
                            <li>• <strong>Area-Specific Fairness:</strong> Within each area, no one gets 2 before everyone gets 1</li>
                            <li>• <strong>Intelligent Load Balancing:</strong> Prevents senior professors from getting all groups</li>
                            <li>• <strong>Rank as Tiebreaker:</strong> Seniority only matters when assignment counts are equal</li>
                            <li>• <strong>Example:</strong> If 3 ML experts exist, all get 1 group before anyone gets 2nd</li>
                        </ul>
                    </div>
                    
                    <!-- General Rules -->
                    <div class="border-t border-blue-200 pt-2">
                        <h4 class="text-xs font-semibold text-blue-800 mb-1">📋 General Rules</h4>
                        <ul class="text-xs text-blue-700 space-y-0.5 ml-3">
                            <li>• <strong>Capacity Limits:</strong> Supervisors cannot exceed their thesis limits</li>
                            <li>• <strong>Manual Override:</strong> Manually assigned groups are excluded from lottery</li>
                            <li>• <strong>Multiple Areas:</strong> Groups can have primary and fallback areas of interest</li>
                            <li>• <strong>Matched Area Tracking:</strong> System shows which area led to supervisor match</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Groups Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Group Assignments</h3>
        </div>

        @if($groups->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Group</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area of Interest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Main Supervisor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Co-Supervisor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignment</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($groups as $group)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $group->name }}</div>
                                    <div class="text-xs text-gray-500">Batch {{ $group->batch_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $group->student_count }}/{{ $group->max_students }}</div>
                                    @if($group->students->count() > 0)
                                        <div class="text-xs text-gray-500">
                                            {{ $group->students->pluck('student_name')->take(2)->join(', ') }}
                                            @if($group->students->count() > 2)
                                                + {{ $group->students->count() - 2 }} more
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <!-- Show all assigned areas -->
                                        <div>
                                            @if($group->areasOfInterest->count() > 0)
                                                @foreach($group->areasOfInterest as $area)
                                                    <span class="inline-block px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full mr-1 mb-1">
                                                        {{ $area->name }}
                                                    </span>
                                                @endforeach
                                            @elseif($group->areaOfInterest)
                                                <span class="inline-block px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full">
                                                    {{ $group->areaOfInterest->name }}
                                                </span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                    Not Set
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Show matched/finalized area if supervisor is assigned -->
                                        @if($group->supervisor && $group->matchedAreaOfInterest)
                                            <div class="text-xs">
                                                <span class="text-green-600 font-medium">✓ Matched:</span>
                                                <span class="inline-block px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                    {{ $group->matchedAreaOfInterest->name }}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($group->supervisor)
                                        <div class="text-sm font-medium text-gray-900">{{ $group->supervisor->fullname }}</div>
                                        <div class="text-xs text-gray-500">{{ $group->supervisor->designation }}</div>
                                    @else
                                        <span class="text-sm text-gray-500 italic">Not assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($group->coSupervisor)
                                        <div class="flex flex-col">
                                            <div class="text-sm font-medium text-blue-700">{{ $group->coSupervisor->fullname }}</div>
                                            <div class="text-xs text-blue-600">{{ $group->coSupervisor->designation }}</div>
                                            <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/>
                                                </svg>
                                                Admin Only
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400 italic">Not assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($group->supervisor)
                                        @if($group->is_manual_assignment)
                                            <span class="px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                                                Manual
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                                Lottery
                                            </span>
                                        @endif
                                    @else
                                        @php $hasAnyAoi = ($group->areasOfInterest->count() > 0) || $group->areaOfInterest; @endphp
                                        @if($hasAnyAoi)
                                            <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                Eligible
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">
                                                Ineligible
                                            </span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if($group->supervisor)
                                            <!-- Unassign Button -->
                                            <form action="{{ route('advisor.supervisor-assignment.unassign') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="group_id" value="{{ $group->id }}">
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900"
                                                        onclick="return confirm('Are you sure you want to remove the supervisor assignment for {{ $group->name }}?')">
                                                    Unassign
                                                </button>
                                            </form>
                                        @else
                                            <!-- Manual Assign Button -->
                                            @php 
                                                $hasAnyAoi = ($group->areasOfInterest->count() > 0) || $group->areaOfInterest;
                                                $manualAreaId = $group->area_of_interest_id ?: optional($group->areasOfInterest->first())->id;
                                            @endphp
                                            @if($hasAnyAoi)
                                                <button onclick='showManualAssignModal({{ $group->id }}, "{{ $group->name }}", {!! json_encode($group->getAreaOfInterestIds()) !!})'
                                                        class="text-green-600 hover:text-green-900">
                                                    Assign Manually
                                                </button>
                                            @else
                                                <span class="text-gray-400">Set Area First</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 515.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No groups found</h3>
                <p class="mt-1 text-sm text-gray-500">Create groups first to manage supervisor assignments.</p>
            </div>
        @endif
    </div>

    <!-- Supervisor Overview -->
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Supervisor Overview</h3>
            <p class="text-sm text-gray-600 mt-1">Sorted by ranking: Professor → Associate Professor → Assistant Professor → Lecturer</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Designation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thesis Limit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Areas of Interest</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($supervisors as $supervisor)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $supervisor->fullname }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $d = mb_strtolower(trim(preg_replace('/\s+/', ' ', $supervisor->designation ?? '')));
                                    if (str_contains($d, 'associate') && str_contains($d, 'professor')) {
                                        $badgeClasses = 'bg-red-100 text-red-800';
                                    } elseif (str_contains($d, 'assistant') && str_contains($d, 'professor')) {
                                        $badgeClasses = 'bg-green-100 text-green-800';
                                    } elseif (str_contains($d, 'professor')) {
                                        $badgeClasses = 'bg-purple-100 text-purple-800';
                                    } else {
                                        $badgeClasses = 'bg-gray-100 text-gray-800';
                                    }
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $badgeClasses }}">
                                    {{ $supervisor->designation }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $supervisor->thesis_limit }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $supervisor->assigned_theses_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm {{ $supervisor->available_slots > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $supervisor->available_slots }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($supervisor->areasOfInterest as $area)
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded">
                                            {{ $area->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Manual Assignment Modal -->
    <div id="manualAssignModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Manual Supervisor Assignment</h3>
                <form id="manualAssignForm" action="{{ route('advisor.supervisor-assignment.assign-manual') }}" method="POST">
                    @csrf
                    <input type="hidden" id="assign_group_id" name="group_id">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Group</label>
                        <p id="assign_group_name" class="text-sm text-gray-900 bg-gray-50 p-2 rounded"></p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="assign_supervisor_id" class="block text-sm font-medium text-gray-700 mb-2">Supervisor</label>
                        <select name="supervisor_id" id="assign_supervisor_id" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            <option value="">Select Supervisor</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="hideManualAssignModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Assign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Lottery Preview Modal -->
    <div id="lotteryPreviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Lottery Assignment Preview</h3>
                <div id="lotteryPreviewContent">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="flex justify-end space-x-2 mt-4">
                    <button type="button" onclick="hideLotteryPreviewModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Beautiful Lottery Confirmation Modal -->
    <div id="lotteryConfirmModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50 transition-opacity duration-300">
        <div class="relative top-20 mx-auto p-5 w-full max-w-md transform transition-all duration-300 scale-95 opacity-0" id="lotteryConfirmContent">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Header with gradient background -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-2 bg-white bg-opacity-20 rounded-full">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                        </div>
                        <h3 class="ml-3 text-xl font-bold text-white">Confirm Lottery Assignment</h3>
                    </div>
                </div>
                
                <!-- Body -->
                <div class="px-6 py-5">
                    <!-- Warning Icon and Message -->
                    <div class="flex items-start mb-4">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-amber-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Are you sure you want to run the lottery assignment?</p>
                            <p class="mt-2 text-sm text-gray-600">This action will automatically assign supervisors to all eligible groups based on your selected criteria.</p>
                        </div>
                    </div>
                    
                    <!-- Selected Criteria Display -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Selected Criteria</h4>
                        <div id="selectedCriteriaDisplay" class="space-y-2">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>
                    
                    <!-- Impact Summary -->
                    <div class="border-l-4 border-blue-400 bg-blue-50 p-4 rounded">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-blue-900">What will happen:</h4>
                                <ul class="mt-2 text-xs text-blue-700 space-y-1">
                                    <li class="flex items-start">
                                        <span class="text-blue-400 mr-1">•</span>
                                        <span>Eligible groups will be automatically assigned supervisors</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="text-blue-400 mr-1">•</span>
                                        <span>Assignments will follow the selected criteria and rules</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="text-blue-400 mr-1">•</span>
                                        <span>Manual assignments will not be affected</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="text-blue-400 mr-1">•</span>
                                        <span>This action cannot be automatically undone</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Footer with Actions -->
                <div class="bg-gray-50 px-6 py-4">
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="hideLotteryConfirmModal()" 
                                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors duration-200">
                            <span class="flex items-center">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cancel
                            </span>
                        </button>
                        <button type="button" onclick="confirmLotteryAssignment()" 
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                            <span class="flex items-center">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Confirm & Run Lottery
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function filterByBatch() {
        const batchSelect = document.getElementById('batch-filter');
        const selectedBatch = batchSelect.value;
        
        // Update the lottery form hidden input
        document.getElementById('lottery-batch').value = selectedBatch;
        
        // Redirect with batch parameter
        const url = new URL(window.location);
        if (selectedBatch) {
            url.searchParams.set('batch', selectedBatch);
        } else {
            url.searchParams.delete('batch');
        }
        window.location.href = url.toString();
    }

    function showManualAssignModal(groupId, groupName, areaIds) {
        document.getElementById('assign_group_id').value = groupId;
        document.getElementById('assign_group_name').textContent = groupName;

        const ids = Array.isArray(areaIds) ? areaIds : (areaIds ? [areaIds] : []);
        const select = document.getElementById('assign_supervisor_id');
        select.innerHTML = '<option value="">Loading...</option>';

        if (ids.length === 0) {
            select.innerHTML = '<option value="">No areas set for this group</option>';
            document.getElementById('manualAssignModal').classList.remove('hidden');
            return;
        }

        const q = ids.map(id => `area_of_interest_ids[]=${encodeURIComponent(id)}`).join('&');
        const url = `{{ route('advisor.supervisor-assignment.available-supervisors') }}?${q}&group_id=${encodeURIComponent(groupId)}`;
        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                select.innerHTML = '<option value="">Select Supervisor</option>';
                if (data.error) {
                    select.innerHTML = '<option value="">Error loading supervisors</option>';
                    alert('Failed to load supervisors: ' + data.message + (data.details ? '\nDetails: ' + JSON.stringify(data.details) : ''));
                    return;
                }
                if (!Array.isArray(data) || data.length === 0) {
                    select.innerHTML = '<option value="">No available supervisors for selected areas</option>';
                    return;
                }
                const seen = new Set();
                data.forEach(supervisor => {
                    if (seen.has(supervisor.id)) return;
                    seen.add(supervisor.id);
                    const option = document.createElement('option');
                    option.value = supervisor.id;
                    option.textContent = `${supervisor.fullname} (${supervisor.designation}) - ${supervisor.available_slots} slots available`;
                    select.appendChild(option);
                });
            })
            .catch(error => {
                const select = document.getElementById('assign_supervisor_id');
                select.innerHTML = '<option value="">Failed to load supervisors</option>';
                alert('Failed to load available supervisors. Network error: ' + error.message);
            });

        document.getElementById('manualAssignModal').classList.remove('hidden');
    }
    
    function hideManualAssignModal() {
        document.getElementById('manualAssignModal').classList.add('hidden');
    }
    
    function previewLottery() {
        const modal = document.getElementById('lotteryPreviewModal');
        const content = document.getElementById('lotteryPreviewContent');
        
        content.innerHTML = '<div class="text-center py-4">Loading preview...</div>';
        modal.classList.remove('hidden');
        
        // Include batch parameter if selected
        const batchSelect = document.getElementById('batch-filter');
        const selectedBatch = batchSelect.value;
        const base = '{{ route('advisor.supervisor-assignment.preview-lottery') }}';
        const modeSelect = document.getElementById('lottery-mode');
        const selectedMode = modeSelect ? modeSelect.value : 'aoi';
        let url = base + (selectedBatch ? '?batch=' + encodeURIComponent(selectedBatch) : '');
        url += (url.includes('?') ? '&' : '?') + 'mode=' + encodeURIComponent(selectedMode);
        
        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    let html = '<div class="space-y-4">';
                    
                    // Statistics
                    html += '<div class="bg-gray-50 p-4 rounded-lg">';
                    html += '<h4 class="font-medium mb-2">Preview Statistics</h4>';
                    html += `<div class="grid grid-cols-4 gap-4 text-sm">`;
                    html += `<div>Total: ${data.preview.stats.total_groups}</div>`;
                    html += `<div class="text-green-600">Will Assign: ${data.preview.stats.will_be_assigned}</div>`;
                    html += `<div class="text-red-600">No Supervisors: ${data.preview.stats.no_matching_supervisors}</div>`;
                    html += `<div class="text-yellow-600">Will Remain: ${data.preview.stats.will_remain_unassigned}</div>`;
                    html += '</div></div>';
                    
                    // Assignments
                    if (data.preview.assignments.length > 0) {
                        html += '<div><h4 class="font-medium mb-2">Planned Assignments</h4>';
                        html += '<div class="space-y-2">';
                        data.preview.assignments.forEach(assignment => {
                            html += `<div class="flex justify-between items-center p-2 bg-green-50 rounded">`;
                            html += `<span><strong>${assignment.group_name}</strong> (${assignment.area_of_interest})</span>`;
                            html += `<span class="text-sm">${assignment.supervisor_name} (${assignment.designation})</span>`;
                            html += `</div>`;
                        });
                        html += '</div></div>';
                    }
                    
                    // Unassigned
                    if (data.preview.unassigned.length > 0) {
                        html += '<div><h4 class="font-medium mb-2">Will Remain Unassigned</h4>';
                        html += '<div class="space-y-2">';
                        data.preview.unassigned.forEach(group => {
                            html += `<div class="flex justify-between items-center p-2 bg-red-50 rounded">`;
                            html += `<span><strong>${group.group_name}</strong> (${group.area_of_interest})</span>`;
                            html += `<span class="text-sm text-red-600">${group.reason}</span>`;
                            html += `</div>`;
                        });
                        html += '</div></div>';
                    }
                    
                    html += '</div>';
                    content.innerHTML = html;
                } else {
                    content.innerHTML = `<div class="text-red-600 text-center py-4">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error loading preview:', error);
                content.innerHTML = '<div class="text-red-600 text-center py-4">Failed to load preview</div>';
            });
    }
    
    function hideLotteryPreviewModal() {
        document.getElementById('lotteryPreviewModal').classList.add('hidden');
    }
function updateLotteryParams() {
        const useAoi = document.getElementById('use-aoi').checked;
        const useRanking = document.getElementById('use-ranking').checked;
        
        document.getElementById('lottery-use-aoi').value = useAoi ? '1' : '0';
        document.getElementById('lottery-use-ranking').value = useRanking ? '1' : '0';
        
        // Validate at least one option is selected
        if (!useAoi && !useRanking) {
            alert('Please select at least one assignment criteria (Area of Interest or Ranking Priority)');
            return false;
        }
        
        return true;
    }

    // Update preview function to use checkboxes
    function getLotteryMode() {
        const useAoi = document.getElementById('use-aoi').checked;
        const useRanking = document.getElementById('use-ranking').checked;
        
        if (useAoi && useRanking) {
            return 'both';
        } else if (useAoi) {
            return 'aoi';
        } else if (useRanking) {
            return 'ranking';
        } else {
            return 'none';
        }
    }

    // Show beautiful lottery confirmation modal
    function showLotteryConfirmModal() {
        // First validate parameters
        if (!updateLotteryParams()) {
            return;
        }
        
        // Update criteria display
        const criteriaDisplay = document.getElementById('selectedCriteriaDisplay');
        const useAoi = document.getElementById('use-aoi').checked;
        const useRanking = document.getElementById('use-ranking').checked;
        const selectedBatch = document.getElementById('batch-filter').value;
        
        let criteriaHtml = '';
        
        if (useAoi && useRanking) {
            criteriaHtml = `
                <div class="flex items-center text-sm">
                    <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">Combined Mode:</span>
                    <span class="ml-1 text-gray-600">Area of Interest + Ranking Priority</span>
                </div>
                <div class="ml-6 text-xs text-gray-500 mt-1">
                    Fair distribution within each area, with ranking as tiebreaker
                </div>`;
        } else if (useAoi) {
            criteriaHtml = `
                <div class="flex items-center text-sm">
                    <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">Area of Interest Only</span>
                    <span class="ml-1 text-gray-600">- Random selection from matching supervisors</span>
                </div>`;
        } else if (useRanking) {
            criteriaHtml = `
                <div class="flex items-center text-sm">
                    <svg class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">Ranking Priority Only</span>
                    <span class="ml-1 text-gray-600">- Round-robin by designation</span>
                </div>`;
        }
        
        if (selectedBatch) {
            criteriaHtml += `
                <div class="flex items-center text-sm mt-2">
                    <svg class="h-4 w-4 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">Batch Filter:</span>
                    <span class="ml-1 text-gray-600">Batch ${selectedBatch}</span>
                </div>`;
        } else {
            criteriaHtml += `
                <div class="flex items-center text-sm mt-2">
                    <svg class="h-4 w-4 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="font-medium">Batch Filter:</span>
                    <span class="ml-1 text-gray-600">All Batches</span>
                </div>`;
        }
        
        criteriaDisplay.innerHTML = criteriaHtml;
        
        // Show modal with animation
        const modal = document.getElementById('lotteryConfirmModal');
        const content = document.getElementById('lotteryConfirmContent');
        
        modal.classList.remove('hidden');
        
        // Trigger animation
        setTimeout(() => {
            modal.classList.add('opacity-100');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }
    
    // Hide lottery confirmation modal
    function hideLotteryConfirmModal() {
        const modal = document.getElementById('lotteryConfirmModal');
        const content = document.getElementById('lotteryConfirmContent');
        
        // Reverse animation
        modal.classList.remove('opacity-100');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        // Hide after animation
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
    
    // Confirm and submit lottery assignment
    function confirmLotteryAssignment() {
        // Hide modal
        hideLotteryConfirmModal();
        
        // Submit the form
        document.getElementById('lottery-form').submit();
    }
</script>
@endpush
