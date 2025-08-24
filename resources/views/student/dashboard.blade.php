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
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                            </svg>
                            Academic Information
                        </h4>
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
                        <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Contact Information
                        </h4>
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
                    <h4 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12l4.243-4.243m-11.314 0L10.586 12l-4.243 4.243M21 12H3"></path>
                        </svg>
                        Address Information
                    </h4>
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

            