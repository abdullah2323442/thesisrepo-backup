@extends('layouts.advisor')

@section('page-title', 'My Students')
@section('page-description', 'Manage and view your advised students')

@section('header-actions')
    <form action="{{ route('advisor.students.refresh') }}" method="POST" class="inline">
        @csrf
        <button type="submit" 
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            Refresh Data
        </button>
    </form>
@endsection

@section('content')
    <!-- Error Message -->
    @if(isset($error))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ $error }}</span>
        </div>
    @endif

    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Filters -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Filter Students</h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('advisor.students.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Batch Filter -->
                <div>
                    <label for="batch" class="block text-sm font-medium text-gray-700 mb-2">Batch</label>
                    <select name="batch" id="batch" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            {{ (isset($no_active_batches) && $no_active_batches) ? 'disabled' : '' }}>
                        <option value="">{{ (isset($no_active_batches) && $no_active_batches) ? 'No Active Batches' : 'All Batches' }}</option>
                        @if(!isset($no_active_batches) || !$no_active_batches)
                            @foreach($batches as $batch)
                                <option value="{{ $batch }}" {{ $selectedBatch == $batch ? 'selected' : '' }}>
                                    Batch {{ $batch }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" id="search" 
                           value="{{ $search }}"
                           placeholder="Search by name or roll number..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Filter Button -->
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Summary -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">
                        @if($selectedBatch)
                            Students from Batch {{ $selectedBatch }}
                        @else
                            All My Students
                        @endif
                    </h3>
                    <p class="text-sm text-gray-600">
                        Showing {{ $students->count() }} of {{ $totalStudents ?? $students->count() }} students
                        @if($search)
                            matching "{{ $search }}"
                        @endif
                    </p>
                </div>
                @if($selectedBatch || $search)
                    <a href="{{ route('advisor.students.index') }}" 
                       class="text-green-600 hover:text-green-900 text-sm font-medium">
                        Clear Filters
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Students Table -->
    <div class="bg-white shadow rounded-lg">
        @if($students->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roll Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gender</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bio Info</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($students as $student)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $student['roll'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $student['name'] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $student['gender'] === 'Male' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                        {{ $student['gender'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                        Batch {{ $student['batch_name'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ $student['bio_info'] === 'No Bio' ? 'Not provided' : $student['bio_info'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('advisor.students.show', $student['id']) }}" 
                                       class="text-green-600 hover:text-green-900">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Batch Summary -->
            @if($students->count() > 0)
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <div>
                            <strong>Summary:</strong>
                            {{ $students->where('gender', 'Male')->count() }} Male, 
                            {{ $students->where('gender', 'Female')->count() }} Female students
                        </div>
                        <div>
                            Total: {{ $students->count() }} students
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                @if(isset($no_active_batches) && $no_active_batches)
                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-red-900">No Active Batches</h3>
                    <p class="mt-1 text-sm text-red-600">
                        There are currently no active batches in the system. Contact your administrator to activate batches.
                    </p>
                @else
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No students found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        @if($search || $selectedBatch)
                            Try adjusting your filters or refresh the data.
                        @else
                            No students are currently assigned to you as an advisor.
                        @endif
                    </p>
                    @if($search || $selectedBatch)
                        <div class="mt-6">
                            <a href="{{ route('advisor.students.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                Clear Filters
                            </a>
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>
@endsection
