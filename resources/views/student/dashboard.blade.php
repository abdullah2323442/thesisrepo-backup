<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Profile Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center space-x-6">
                        <!-- Profile Image -->
                        <div class="flex-shrink-0">
                            @php
                                $profileImage = '';
                                $userName = '';
                                
                                if ($userType === 'model') {
                                    $profileImage = $user->profile_image ?? $user->profile_image_url ?? '';
                                    $userName = $user->name ?? 'User';
                                } else {
                                    $profileImage = $user['Url'] ?? '';
                                    $userName = $user['Name'] ?? 'User';
                                }
                                
                                if (empty($profileImage)) {
                                    $profileImage = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=6366f1&color=ffffff';
                                }
                            @endphp
                            
                            <img class="h-24 w-24 rounded-full object-cover border-4 border-gray-200" 
                                 src="{{ $profileImage }}" 
                                 alt="Profile Image">
                        </div>
                        
                        <!-- Basic Info -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900">
                                {{ $userType === 'model' ? ($user->name ?? 'N/A') : ($user['Name'] ?? 'N/A') }}
                            </h3>
                            <p class="text-lg text-gray-600">
                                Roll: {{ $userType === 'model' ? ($user->roll ?? 'N/A') : ($user['Roll'] ?? 'N/A') }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $userType === 'model' ? ($user->login_type ?? 'Student') : ($user['LoginType'] ?? 'Student') }} • 
                                Batch {{ $userType === 'model' ? ($user->batch ?? 'N/A') : ($user['Batch'] ?? 'N/A') }}
                            </p>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="text-center">
                            <div class="bg-blue-100 text-blue-800 text-lg font-bold py-3 px-6 rounded-lg">
                                @php
                                    $status = $userType === 'model' ? ($user->status ?? 'Active') : ($user['Status'] ?? 'Active');
                                @endphp
                                {{ $status }}
                            </div>
                            <p class="text-sm text-gray-600 mt-1">Status</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Academic Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Academic Information</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Department:</span>
                                <span class="font-medium">
                                    {{ $userType === 'model' ? ($user->department_name ?? 'N/A') : ($user['DepartmentName'] ?? 'N/A') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Program:</span>
                                <span class="font-medium">
                                    {{ $userType === 'model' ? ($user->program_name ?? 'N/A') : ($user['ProgramName'] ?? 'N/A') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Batch:</span>
                                <span class="font-medium">
                                    {{ $userType === 'model' ? ($user->batch ?? 'N/A') : ($user['Batch'] ?? 'N/A') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Advisor:</span>
                                <span class="font-medium">
                                    {{ $userType === 'model' ? ($user->advisor ?? 'N/A') : ($user['Advisor'] ?? 'N/A') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="font-medium">
                                    {{ $userType === 'model' ? ($user->email ?? 'N/A') : ($user['Email'] ?? 'N/A') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Phone:</span>
                                <span class="font-medium">
                                    {{ $userType === 'model' ? ($user->phone ?? 'N/A') : ($user['Phone'] ?? 'N/A') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium">
                                    @php
                                        $status = $userType === 'model' ? ($user->status ?? 'Active') : ($user['Status'] ?? 'Active');
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $status }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Address Information -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Address Information</h4>
                    <div class="text-gray-700">
                        <p>{{ $userType === 'model' ? ($user->address ?? 'No address provided') : ($user['Address'] ?? 'No address provided') }}</p>
                    </div>
                </div>
            </div>
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-6 rounded-lg">
                    <div class="text-2xl font-bold">
                        {{ $userType === 'model' ? ($user->batch ?? 'N/A') : ($user['Batch'] ?? 'N/A') }}
                    </div>
                    <div class="text-blue-100">Batch</div>
                </div>
                
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-lg">
                    <div class="text-2xl font-bold">
                        {{ $userType === 'model' ? ($user->department_name ?? 'CSE') : ($user['DepartmentName'] ?? 'CSE') }}
                    </div>
                    <div class="text-green-100">Department</div>
                </div>
                
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-lg">
                    <div class="text-2xl font-bold">
                        @php
                            $status = $userType === 'model' ? ($user->status ?? 'Active') : ($user['Status'] ?? 'Active');
                        @endphp
                        {{ $status }}
                    </div>
                    <div class="text-purple-100">Status</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

            