@extends('layouts.admin')

@section('page-title', 'Edit Supervisor')
@section('page-description', 'Manage "' . $supervisor->fullname . '"')

@section('header-actions')
    <!-- Back Button -->
    <a href="{{ route('admin.supervisors.index') }}" 
       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        Back to List
    </a>
@endsection

@section('content')
    <!-- Error Messages -->
    @if($errors->any())
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Please fix the following errors:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <!-- Supervisor Info Card -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Supervisor Information</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supervisor->fullname }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supervisor->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Designation</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supervisor->designation }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Department</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supervisor->department }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gender</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $supervisor->gender }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Synced</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $supervisor->last_synced_at ? $supervisor->last_synced_at->format('M d, Y H:i') : 'Never' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Supervisor Settings</h3>
            </div>
            
            <form action="{{ route('admin.supervisors.update', $supervisor) }}" method="POST" class="px-6 py-4 space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Thesis Limit -->
                <div>
                    <label for="thesis_limit" class="block text-sm font-medium text-gray-700 mb-2">
                        Thesis Limit <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="thesis_limit" id="thesis_limit" 
                           value="{{ old('thesis_limit', $supervisor->thesis_limit) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('thesis_limit') border-red-500 @enderror"
                           min="0" max="20" required>
                    <p class="text-sm text-gray-500 mt-1">Maximum number of thesis projects this supervisor can handle</p>
                    @error('thesis_limit')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" 
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                           {{ old('is_active', $supervisor->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Active (available for thesis supervision)
                    </label>
                </div>

                <!-- Areas of Interest -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Areas of Interest
                    </label>
                    <p class="text-sm text-gray-500 mb-3">Select the research areas this supervisor can supervise</p>
                    
                    @if($areasOfInterest->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-60 overflow-y-auto border border-gray-300 rounded-md p-3">
                            @foreach($areasOfInterest as $area)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="areas_of_interest[]" 
                                           value="{{ $area->id }}"
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                           {{ in_array($area->id, old('areas_of_interest', $supervisor->areasOfInterest->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-900">{{ $area->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No areas of interest available. Please create some first.</p>
                    @endif
                    
                    @error('areas_of_interest')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Areas Display -->
                @if($supervisor->areasOfInterest->count() > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Currently Assigned Areas
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($supervisor->areasOfInterest as $area)
                                <span class="px-3 py-1 text-sm bg-indigo-100 text-indigo-800 rounded-full">
                                    {{ $area->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.supervisors.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        Cancel
                    </a>
                    
                    <!-- Refresh from API -->
                    <form action="{{ route('admin.supervisors.refresh', $supervisor) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                            Refresh from API
                        </button>
                    </form>
                    
                    <!-- Save Changes -->
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Additional Actions -->
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Additional Actions</h4>
            <div class="flex space-x-4">
                <!-- Toggle Status -->
                <form action="{{ route('admin.supervisors.toggle', $supervisor) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="bg-{{ $supervisor->is_active ? 'red' : 'green' }}-600 hover:bg-{{ $supervisor->is_active ? 'red' : 'green' }}-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors"
                            onclick="return confirm('Are you sure you want to {{ $supervisor->is_active ? 'deactivate' : 'activate' }} this supervisor?')">
                        {{ $supervisor->is_active ? 'Deactivate' : 'Activate' }} Supervisor
                    </button>
                </form>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                {{ $supervisor->is_active ? 'Deactivating will prevent students from selecting this supervisor for new thesis projects.' : 'Activating will make this supervisor available for new thesis projects.' }}
            </p>
        </div>
    </div>
@endsection
