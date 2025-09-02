@extends('layouts.supervisor')

@section('page-title', 'Edit Report')
@section('page-description', 'Edit report details')

@section('content')
<div class="container mx-auto max-w-4xl">
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Report</h2>

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('supervisor.reports.update', $report) }}" method="POST" x-data="{ reportType: '{{ old('type', $report->type) }}' }">
                @csrf
                @method('PUT')

                <!-- Group Selection -->
                <div class="mb-6">
                    <label for="group_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Select Group <span class="text-red-500">*</span>
                    </label>
                    <select name="group_id" id="group_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('group_id') border-red-500 @enderror">
                        <option value="">-- Select a Group --</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ (old('group_id', $report->group_id) == $group->id) ? 'selected' : '' }}>
                                {{ $group->name }} ({{ $group->students->count() }} students)
                            </option>
                        @endforeach
                    </select>
                    @error('group_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Report Type -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Report Type <span class="text-red-500">*</span>
                    </label>
                    <select name="type" id="type" required x-model="reportType"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('type') border-red-500 @enderror">
                        <option value="general" {{ old('type', $report->type) == 'general' ? 'selected' : '' }}>General Report</option>
                        <option value="final" {{ old('type', $report->type) == 'final' ? 'selected' : '' }}>Final Report</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-600">
                        <span x-show="reportType === 'general'">General reports are for regular progress updates and don't require project details.</span>
                        <span x-show="reportType === 'final'" x-cloak>Final reports require project title, abstract, and keywords.</span>
                    </p>
                </div>

                <!-- Final Report Info -->
                <div x-show="reportType === 'final'" x-cloak class="mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-blue-800 mb-1">Final Report Information</h4>
                                <p class="text-sm text-blue-700">
                                    For final reports, the project title, abstract, and keywords will be filled out during the approval process. 
                                    Students will submit their work first, then you can review and approve with the final project details.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supervisor Message for Students -->
                <div class="mb-6">
                    <label for="supervisor_message" class="block text-sm font-medium text-gray-700 mb-2">
                        Message to Students <span class="text-blue-600">(Will appear in notifications)</span>
                    </label>
                    <textarea name="supervisor_message" id="supervisor_message" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('supervisor_message') border-red-500 @enderror"
                              placeholder="Enter instructions or message for students about what they need to do for this report...">{{ old('supervisor_message', $report->supervisor_message) }}</textarea>
                    @error('supervisor_message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">This message will be included in the notification sent to students and will help them understand what is expected.</p>
                </div>

                <!-- Additional Notes (for both types) -->
                <div class="mb-6">
                    <label for="extra_input" class="block text-sm font-medium text-gray-700 mb-2">
                        Additional Notes (Optional)
                    </label>
                    <textarea name="extra_input" id="extra_input" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('extra_input') border-red-500 @enderror"
                              placeholder="Any additional notes or instructions for students">{{ old('extra_input', $report->extra_input) }}</textarea>
                    @error('extra_input')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('supervisor.reports.show', $report) }}" 
                       class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

@push('scripts')
<script src="//unpkg.com/alpinejs" defer></script>
@endpush