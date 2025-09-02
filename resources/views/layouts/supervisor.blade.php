<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'Laravel') }} - Supervisor Panel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg">
            <div class="flex items-center justify-center h-16 px-4 bg-blue-600">
                <h1 class="text-xl font-bold text-white">Supervisor Panel</h1>
            </div>

            <nav class="mt-8">
                <div class="px-4 space-y-2">
                    <!-- Dashboard -->
                    <a href="{{ route('supervisor.dashboard') }}"
                        class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors {{ request()->routeIs('supervisor.dashboard') ? 'bg-blue-100 text-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                        </svg>
                        Dashboard
                    </a>

                    <!-- My Groups -->
                    <a href="{{ route('supervisor.groups.index') }}"
                        class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors {{ request()->routeIs('supervisor.groups.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a4 4 0 00-5-4m-6 6H2v-2a4 4 0 014-4m6-4a4 4 0 110-8 4 4 0 010 8z"></path>
                        </svg>
                        My Groups
                    </a>

                    <!-- Meeting Management -->
                    <a href="{{ route('supervisor.meetings.index') }}"
                        class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors {{ request()->routeIs('supervisor.meetings.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2v-7H3v7a2 2 0 002 2z" />
                        </svg>
                        Meeting Management
                    </a>

                    <!-- Report Management -->
                    <a href="{{ route('supervisor.reports.index') }}"
                        class="flex items-center px-4 py-2 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors {{ request()->routeIs('supervisor.reports.*') ? 'bg-blue-100 text-blue-700' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 6h8m-8 4h8m-8 4h8M6 6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2H6z" />
                        </svg>
                        Report Management
                    </a>
                </div>
            </nav>

            <!-- Back to Teacher Dashboard -->
            <div class="absolute bottom-16 w-full px-4">
                <a href="{{ route('teacher.dashboard') }}"
                    class="flex items-center px-4 py-2 text-gray-500 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Teacher Dashboard
                </a>
            </div>

            <!-- User Info -->
            <div class="absolute bottom-0 w-full p-4 border-t border-gray-200">
                <div class="flex items-center">
                    <img src="{{ auth()->user()->profile_image }}" alt="Profile" class="w-10 h-10 rounded-full">
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">Supervisor</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="ml-64">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="flex items-center justify-between px-6 py-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Supervisor Dashboard')</h1>
                        <p class="text-gray-600">@yield('page-description', 'Welcome to the supervisor panel')</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        @isset($headerActions)
                        {{ $headerActions }}
                        @endisset

                        <!-- Profile -->
                        <div class="relative">
                            <button class="flex items-center text-gray-600 hover:text-gray-900">
                                <img src="{{ auth()->user()->profile_image }}" alt="Profile"
                                    class="w-8 h-8 rounded-full mr-2">
                                <span class="text-sm">{{ auth()->user()->name }}</span>
                            </button>
                        </div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit"
                                class="flex items-center px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>