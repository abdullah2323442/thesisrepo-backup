<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Teacher Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center space-x-4">
                        <img src="{{ $userType === 'model' ? $user->profile_image : 'https://ui-avatars.com/api/?name=' . urlencode(($userType === 'model' ? $user->name : ($user['Name'] ?? 'Teacher'))) . '&background=6366f1&color=ffffff' }}" 
                             alt="Profile" class="w-16 h-16 rounded-full">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">
                                Welcome, {{ $userType === 'model' ? $user->name : ($user['Name'] ?? 'Teacher') }}!
                            </h3>
                            <p class="text-gray-600">Last Login: {{ now()->setTimezone('Asia/Dhaka')->format('h:i A, l, F d, Y') }} ({{ now()->setTimezone('Asia/Dhaka')->format('T') }})</p>
                            @if($userType === 'model')
                                <p class="text-sm text-gray-500">Username: {{ $user->username ?? $user->name }}</p>
                                @if($user->designation)
                                    <p class="text-sm text-gray-500">Designation: {{ $user->designation }}</p>
                                @endif
                            @else
                                <p class="text-sm text-gray-500">Username: {{ $user['UserName'] ?? ($user['Name'] ?? 'N/A') }}</p>
                                @if(isset($user['designation']) && $user['designation'])
                                    <p class="text-sm text-gray-500">Designation: {{ $user['designation'] }}</p>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teacher Information Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <!-- Contact Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Contact Information</h4>
                        <div class="space-y-2">
                            <p class="text-sm">
                                <span class="font-medium">Phone:</span> 
                                {{ $userType === 'model' ? ($user->phone ?? 'N/A') : ($user['Phone'] ?? 'N/A') }}
                            </p>
                            <p class="text-sm">
                                <span class="font-medium">Email:</span> 
                                {{ $userType === 'model' ? ($user->email ?? 'N/A') : ($user['Email'] ?? 'N/A') }}
                            </p>
                            <p class="text-sm">
                                <span class="font-medium">Address:</span> 
                                {{ $userType === 'model' ? ($user->address ?? 'N/A') : ($user['Address'] ?? 'N/A') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Department Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Department Information</h4>
                        <div class="space-y-2">
                            <p class="text-sm">
                                <span class="font-medium">Department:</span> 
                                Computer Science & Engineering
                            </p>
                            <p class="text-sm">
                                <span class="font-medium">Department ID:</span> 
                                {{ $userType === 'model' ? ($user->department_id ?? 1) : ($user['DeptId'] ?? 1) }}
                            </p>
                            @php
                                // Get TypeIds based on user type
                                $typeIds = $userType === 'model' ? $user->typeIds : (is_array($user['TypeId']) ? $user['TypeId'] : ['2']);
                                // Generate descriptions for each type ID
                                $typeDescriptions = [];
                                foreach ($typeIds as $id) {
                                    $typeDescriptions[] = match (strval($id)) {
                                        '1' => 'Admin',
                                        '2' => 'Teacher',
                                        '3' => 'Advisor',
                                        default => 'Unknown',
                                    };
                                }
                            @endphp
                            <p class="text-sm">
                                <span class="font-medium">Type ID:</span> 
                                {{ implode(', ', $typeIds) }} ({{ implode(', ', $typeDescriptions) }})
                            </p>
                            <p class="text-sm">
                                <span class="font-medium">Status:</span> 
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    {{ $userType === 'model' ? ($user->status ?? 'Active') : ($user['Status'] ?? 'Active') }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Role-based Quick Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h4>
                        <div class="space-y-2">
                            @php
                                $hasAdminRole = in_array('1', $typeIds);
                                $hasTeacherRole = in_array('2', $typeIds);
                                $hasAdvisorRole = in_array('3', $typeIds);
                            @endphp
                            
                            @if($hasAdminRole)
                                <a href="{{ route('admin.dashboard') }}" class="block w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors text-center">
                                    Admin Panel
                                </a>
                            @endif
                            
                            @if($hasTeacherRole)
                                <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                    Manage Classes
                                </button>
                            @endif
                            
                            @if($hasTeacherRole)
                                <a href="{{ route('advisor.dashboard') }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors text-center">
                                    Advisor Panel
                                </a>
                            @endif
                            
                            @if($hasAdvisorRole)
                                <button class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm transition-colors">
                                    Student Advising
                                </button>
                            @endif
                            
                            @if(count($typeIds) === 1 && $typeIds[0] === '2')
                                <button class="w-full bg-gray-500 text-white font-bold py-2 px-4 rounded text-sm cursor-not-allowed" disabled>
                                    Basic Teacher Access Only
                                </button>
                            @endif
                        </div>
                        
                        <!-- Display current roles -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-600 mb-2">Current Roles:</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($typeDescriptions as $description)
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        {{ $description === 'Admin' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $description === 'Teacher' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $description === 'Advisor' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $description === 'Unknown' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ $description }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>