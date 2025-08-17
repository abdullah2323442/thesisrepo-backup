@extends('layouts.advisor')

@section('page-title', 'Student Details')
@section('page-description')
{{ $student['name'] }} ({{ $student['roll'] }})
@endsection

@section('header-actions')
    <a href="{{ route('advisor.students.index') }}" 
       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        Back to Students
    </a>
@endsection

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Student Information Card -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Student Information</h3>
            </div>
            <div class="px-6 py-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Student ID</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $student['id'] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Roll Number</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $student['roll'] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $student['name'] }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gender</label>
                        <p class="mt-1">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $student['gender'] === 'Male' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                                {{ $student['gender'] }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Batch</label>
                        <p class="mt-1">
                            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">
                                Batch {{ $student['batch_name'] }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Advisor</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $student['advisor'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bio Information -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Bio Information</h3>
            </div>
            <div class="px-6 py-6">
                @if($student['bio_info'] && $student['bio_info'] !== 'No Bio')
                    <p class="text-sm text-gray-900">{{ $student['bio_info'] }}</p>
                @else
                    <p class="text-sm text-gray-500 italic">No bio information provided by the student.</p>
                @endif
            </div>
        </div>

        <!-- Academic Progress -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Academic Progress</h3>
            </div>
            <div class="px-6 py-6">
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h4 class="mt-2 text-sm font-medium text-gray-900">Academic Progress Tracking</h4>
                    <p class="mt-1 text-sm text-gray-500">This feature will be available soon.</p>
                </div>
            </div>
        </div>

        <!-- Thesis Information -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Thesis Information</h3>
            </div>
            <div class="px-6 py-6">
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C20.168 18.477 18.582 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <h4 class="mt-2 text-sm font-medium text-gray-900">Thesis Management</h4>
                    <p class="mt-1 text-sm text-gray-500">Thesis information and management tools will be available soon.</p>
                </div>
            </div>
        </div>

        <!-- Meeting History -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Meeting History</h3>
                    <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors" disabled>
                        Schedule Meeting
                    </button>
                </div>
            </div>
            <div class="px-6 py-6">
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h4 class="mt-2 text-sm font-medium text-gray-900">No Meetings Yet</h4>
                    <p class="mt-1 text-sm text-gray-500">Meeting scheduling and history will be available soon.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
