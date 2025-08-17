@extends('layouts.admin')

@section('page-title', 'Areas of Interest')
@section('page-description', 'Manage research areas and topics')

@section('header-actions')
    <a href="{{ route('admin.areas-of-interest.create') }}" 
       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
        Add New Area
    </a>
@endsection

@section('content')
    <!-- Success Message -->
    @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Stats Card -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center">
            <div class="p-2 bg-indigo-100 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Areas of Interest</p>
                <p class="text-2xl font-bold text-gray-900">{{ $areasOfInterest->total() }}</p>
            </div>
        </div>
    </div>

    <!-- Areas of Interest Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">All Areas of Interest</h3>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.areas-of-interest.create') }}" 
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                        Single Add
                    </a>
                    <button onclick="toggleBulkForm()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                        Bulk Add
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Add Form (Hidden by default) -->
        <div id="bulkForm" class="hidden px-6 py-4 bg-gray-50 border-b border-gray-200">
            <form action="{{ route('admin.areas-of-interest.store-bulk') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="bulk_areas" class="block text-sm font-medium text-gray-700 mb-2">
                        Bulk Add Areas (one per line)
                    </label>
                    <textarea name="bulk_areas" id="bulk_areas" rows="6" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                              placeholder="Machine Learning&#10;Artificial Intelligence&#10;Data Science&#10;Web Development&#10;Mobile App Development"></textarea>
                    @error('bulk_areas')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex space-x-2">
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors">
                        Create All
                    </button>
                    <button type="button" onclick="toggleBulkForm()" 
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded text-sm font-medium transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>

        @if($areasOfInterest->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($areasOfInterest as $area)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $area->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ $area->description ? Str::limit($area->description, 50) : 'No description' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $area->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $area->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $area->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('admin.areas-of-interest.edit', $area) }}" 
                                           class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                        <form action="{{ route('admin.areas-of-interest.destroy', $area) }}" 
                                              method="POST" class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this area of interest?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $areasOfInterest->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No areas of interest</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new area of interest.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.areas-of-interest.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Add Area of Interest
                    </a>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function toggleBulkForm() {
            const form = document.getElementById('bulkForm');
            form.classList.toggle('hidden');
        }
    </script>
    @endpush
@endsection
