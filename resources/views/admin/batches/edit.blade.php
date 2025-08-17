@extends('layouts.admin')

@section('page-title', 'Edit Batch')
@section('page-description')
Update "{{ $batch->display_name }}"
@endsection

@section('header-actions')
    <a href="{{ route('admin.batches.index') }}" 
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

    <div class="max-w-2xl mx-auto">
        <!-- Batch Information Card -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Batch Information</h3>
            </div>
            <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Batch Number</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $batch->batch_number }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Program ID</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $batch->program_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Status</label>
                        <p class="mt-1">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $batch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $batch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Synced</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $batch->last_synced_at ? $batch->last_synced_at->format('M d, Y H:i') : 'Never' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Edit Batch Details</h3>
            </div>
            
            <form action="{{ route('admin.batches.update', $batch) }}" method="POST" class="px-6 py-4 space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Batch Name -->
                <div>
                    <label for="batch_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Batch Name
                    </label>
                    <input type="text" name="batch_name" id="batch_name" 
                           value="{{ old('batch_name', $batch->batch_name) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('batch_name') border-red-500 @enderror"
                           placeholder="e.g., Batch 41, Spring 2023">
                    <p class="text-sm text-gray-500 mt-1">Display name for this batch</p>
                    @error('batch_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="4" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror"
                              placeholder="Optional description for this batch...">{{ old('description', $batch->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" 
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                           {{ old('is_active', $batch->is_active) ? 'checked' : '' }}>
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Active (available for system operations like group assignments)
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.batches.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                        Update Batch
                    </button>
                </div>
            </form>
        </div>

        <!-- Additional Actions -->
        <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Additional Actions</h4>
            <div class="flex space-x-4">
                <!-- Toggle Status -->
                <form action="{{ route('admin.batches.toggle', $batch) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="bg-{{ $batch->is_active ? 'red' : 'green' }}-600 hover:bg-{{ $batch->is_active ? 'red' : 'green' }}-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors"
                            onclick="return confirm('Are you sure you want to {{ $batch->is_active ? 'deactivate' : 'activate' }} this batch?')">
                        {{ $batch->is_active ? 'Deactivate' : 'Activate' }} Batch
                    </button>
                </form>

                <!-- Delete Batch -->
                <form action="{{ route('admin.batches.destroy', $batch) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors"
                            onclick="return confirm('Are you sure you want to delete this batch? This action cannot be undone.')">
                        Delete Batch
                    </button>
                </form>
            </div>
            <p class="text-sm text-gray-500 mt-2">
                {{ $batch->is_active ? 'Deactivating will prevent this batch from being used in new operations.' : 'Activating will make this batch available for system operations.' }}
                Deleting will permanently remove this batch from the system.
            </p>
        </div>
    </div>
@endsection
