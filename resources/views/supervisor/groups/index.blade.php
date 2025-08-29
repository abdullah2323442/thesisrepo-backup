@extends('layouts.supervisor')

@section('page-title', 'My Groups')
@section('page-description', 'View and manage your assigned groups and students')

@section('content')
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Assigned Groups</h3>
    </div>

    <div class="p-6">
        @if($groups->isEmpty())
            <p class="text-gray-500">No groups assigned.</p>
        @else
            <div class="space-y-6">
                @foreach($groups as $group)
                    <div class="border rounded-lg">
                        <div class="p-4 bg-gray-50 flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $group->name }}</h4>
                                <p class="text-sm text-gray-600">Batch {{ $group->batch_number }} • Max Students: {{ $group->max_students }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs px-2 py-1 rounded bg-blue-100 text-blue-800">Students: {{ $group->students->count() }}</span>
                            </div>
                        </div>

                        <div class="p-4">
                            <p class="text-sm text-gray-700 mb-2">Area of Interest: <span class="font-medium">{{ optional($group->matchedAreaOfInterest)->name ?? 'Not set' }}</span></p>
                            @if($group->students->isEmpty())
                                <p class="text-gray-500">No students in this group.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-sm">
                                        <thead>
                                            <tr class="text-left text-gray-600">
                                                <th class="py-2 pr-4">#</th>
                                                <th class="py-2 pr-4">Student Name</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-800">
                                            @foreach($group->students as $index => $student)
                                                <tr class="border-t">
                                                    <td class="py-2 pr-4">{{ $index + 1 }}</td>
                                                    <td class="py-2 pr-4">{{ $student->student_name }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
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
@endsection
