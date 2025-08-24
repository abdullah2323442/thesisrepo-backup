@extends('layouts.supervisor')

@section('page-title', 'Supervisor Dashboard')
@section('page-description')
Welcome back{{ $supervisor ? ', ' . $supervisor->fullname : '' }}
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Assigned Groups</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['assigned_groups'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-5-4m-6 6H2v-2a4 4 0 014-4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Students</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_students'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">My Assigned Groups</h3>
            <a href="{{ route('supervisor.groups.index') }}" class="text-sm text-blue-600 hover:underline">View all</a>
        </div>
        <div class="p-6">
            @if($groups->isEmpty())
                <p class="text-gray-500">No groups have been assigned to you yet.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($groups as $group)
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold">{{ $group->name }}</h4>
                                <span class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700">Batch {{ $group->batch_number }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">Students: {{ $group->students->count() }}</p>
                            <ul class="text-sm text-gray-700 list-disc pl-4">
                                @foreach($group->students->take(4) as $student)
                                    <li>{{ $student->student_name }}</li>
                                @endforeach
                                @if($group->students->count() > 4)
                                    <li class="text-gray-500">+{{ $group->students->count() - 4 }} more</li>
                                @endif
                            </ul>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
