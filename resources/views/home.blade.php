<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>University Thesis Repository System</title>
    <meta name="description"
        content="Explore our comprehensive digital repository of academic theses and research projects from leading scholars and researchers.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-inter antialiased bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 min-h-screen relative overflow-x-hidden">
    <!-- Glassmorphism Background Elements -->
    <div class="fixed inset-0 -z-10">
        <div class="absolute top-0 left-0 w-96 h-96 bg-gradient-to-br from-blue-400/30 to-purple-400/30 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute top-1/3 right-0 w-80 h-80 bg-gradient-to-br from-pink-400/30 to-orange-400/30 rounded-full blur-3xl animate-pulse delay-1000"></div>
        <div class="absolute bottom-0 left-1/3 w-72 h-72 bg-gradient-to-br from-green-400/30 to-blue-400/30 rounded-full blur-3xl animate-pulse delay-2000"></div>
    </div>
    <!-- Navigation -->
    <nav class="bg-white/20 backdrop-blur-md border-b border-white/30 sticky top-0 z-50 shadow-lg shadow-black/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <img src="{{ asset('Picture1.png') }}" alt="Picture1" class="w-16 h-auto object-contain">
                        <div class="ml-3">
                            <h1 class="text-xl font-bold text-slate-900">University Repository System</h1>
                            <p class="text-xs text-slate-500 -mt-0.5">Academic Research Archive</p>
                        </div>
                    </div>
                </div>

                @auth
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-slate-600">{{ auth()->user()->name }}</span>
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition-colors">
                        Dashboard
                    </a>
                </div>
                @else
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}"
                        class="text-slate-600 hover:text-slate-900 text-sm font-medium transition-colors">
                        Sign In
                    </a> 
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative py-16 lg:py-24">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 to-indigo-600/5"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                <h1 class="text-4xl lg:text-6xl font-bold text-slate-900 mb-6">
                    Discover Academic
                    <span
                        class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Excellence</span>
                </h1>
                <p class="text-xl text-slate-600 mb-8 leading-relaxed">
                    Explore our comprehensive digital repository featuring cutting-edge research, innovative theses,
                    and scholarly works from distinguished academics and emerging researchers.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-12">
                    <div class="flex items-center space-x-2 text-slate-600">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-semibold">{{ $reports->total() }}</span>
                        <span>Published Theses</span>
                    </div>
                    <div class="flex items-center space-x-2 text-slate-600">
                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z">
                            </path>
                        </svg>
                        <span class="font-semibold">{{ $supervisors->count() }}</span>
                        <span>Research Supervisors</span>
                    </div>
                    <div class="flex items-center space-x-2 text-slate-600">
                        <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1h-6a1 1 0 01-1-1V8z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-semibold">{{ $areasOfInterest->count() }}</span>
                        <span>Research Areas</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Advanced Search Section -->
    <section class="relative bg-gradient-to-br from-white/40 via-white/30 to-white/20 backdrop-blur-md border-b border-white/20 overflow-hidden">
        <!-- Decorative Elements -->
        <div class="absolute top-0 left-0 w-32 h-32 bg-gradient-to-br from-blue-400/20 to-purple-400/20 rounded-full blur-2xl"></div>
        <div class="absolute bottom-0 right-0 w-40 h-40 bg-gradient-to-br from-indigo-400/20 to-pink-400/20 rounded-full blur-2xl"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl mb-6 shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl lg:text-4xl font-bold text-slate-900 mb-4">
                    Advanced Research <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-indigo-600">Discovery</span>
                </h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    Explore our comprehensive digital library with powerful search and filtering capabilities
                </p>
            </div>

            <form method="GET" action="{{ route('home') }}" id="filterForm" class="space-y-8">
                <!-- Primary Search Card -->
                <div class="group relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 rounded-2xl blur opacity-25 group-hover:opacity-40 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative bg-white/60 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/40 p-8 hover:shadow-3xl transition-all duration-300">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <div class="flex-1">
                                <label class="flex items-center text-sm font-bold text-slate-800 mb-3">
                                    <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                    Search Query
                                </label>
                                <div class="relative group">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-indigo-500/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative flex items-center">
                                        <svg class="absolute left-4 w-5 h-5 text-slate-500 group-hover:text-blue-600 transition-colors duration-200"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <input type="text" name="search" value="{{ request('search') }}"
                                            placeholder="Search by title, abstract, keywords, or author..."
                                            class="w-full pl-12 pr-4 py-4 bg-white/80 backdrop-blur-sm border border-slate-200/60 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-400 text-slate-900 placeholder-slate-500 shadow-inner hover:bg-white/90 transition-all duration-200">
                                    </div>
                                </div>
                            </div>
                            <div class="lg:w-auto flex items-end">
                                <button type="submit"
                                    class="group relative w-full lg:w-auto px-8 py-4 bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-white font-bold rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200">
                                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500 via-purple-600 to-indigo-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="relative flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                        <span>Search</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Filters Card -->
                <div class="group relative">
                    <div class="absolute -inset-1 bg-gradient-to-r from-purple-600 via-pink-600 to-indigo-600 rounded-2xl blur opacity-20 group-hover:opacity-30 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative bg-white/50 backdrop-blur-xl rounded-2xl shadow-xl border border-white/30 p-8 hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-center mb-6">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl mr-4 shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Advanced Filters</h3>
                                <p class="text-sm text-slate-600">Refine your search with specific criteria</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Research Area Filter -->
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-semibold text-slate-700">
                                    <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                    Research Area
                                </label>
                                <select name="area_of_interest"
                                    class="w-full px-4 py-3 bg-white/80 backdrop-blur-sm border border-slate-200/60 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:border-purple-400 text-slate-900 shadow-sm hover:bg-white/90 transition-all duration-200">
                                    <option value="">All Research Areas</option>
                                    @foreach($areasOfInterest as $area)
                                    <option value="{{ $area->id }}"
                                        {{ request('area_of_interest') == $area->id ? 'selected' : '' }}>
                                        {{ $area->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Publication Year -->
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-semibold text-slate-700">
                                    <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1h3z"></path>
                                    </svg>
                                    Publication Year
                                </label>
                                <select name="year_from"
                                    class="w-full px-4 py-3 bg-white/80 backdrop-blur-sm border border-slate-200/60 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-400 text-slate-900 shadow-sm hover:bg-white/90 transition-all duration-200">
                                    <option value="">Any Year</option>
                                    @foreach($availableYears as $year)
                                    <option value="{{ $year }}" {{ request('year_from') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Supervisor -->
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-semibold text-slate-700">
                                    <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Research Supervisor
                                </label>
                                <select name="supervisor"
                                    class="w-full px-4 py-3 bg-white/80 backdrop-blur-sm border border-slate-200/60 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500/50 focus:border-green-400 text-slate-900 shadow-sm hover:bg-white/90 transition-all duration-200">
                                    <option value="">All Supervisors</option>
                                    @foreach($supervisors as $supervisor)
                                    <option value="{{ $supervisor->id }}"
                                        {{ request('supervisor') == $supervisor->id ? 'selected' : '' }}>
                                        {{ $supervisor->fullname }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Sort Options -->
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-semibold text-slate-700">
                                    <svg class="w-4 h-4 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h9m5-4v12m0 0l-4-4m4 4l4-4"></path>
                                    </svg>
                                    Sort Results
                                </label>
                                <select name="sort"
                                    class="w-full px-4 py-3 bg-white/80 backdrop-blur-sm border border-slate-200/60 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-400 text-slate-900 shadow-sm hover:bg-white/90 transition-all duration-200">
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Most Recent</option>
                                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                                    <option value="title" {{ request('sort') == 'title' ? 'selected' : '' }}>Alphabetical</option>
                                </select>
                            </div>
                        </div>

                        <!-- Keywords Section -->
                        <div class="mt-8 p-6 bg-gradient-to-r from-slate-50/80 to-blue-50/80 backdrop-blur-sm rounded-xl border border-white/40">
                            <label class="flex items-center text-sm font-semibold text-slate-700 mb-3">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Keywords & Tags
                            </label>
                            <input type="text" name="keywords" value="{{ request('keywords') }}"
                                placeholder="e.g., artificial intelligence, machine learning, data science, neural networks"
                                class="w-full px-4 py-3 bg-white/90 backdrop-blur-sm border border-slate-200/60 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-400 text-slate-900 placeholder-slate-500 shadow-sm hover:bg-white transition-all duration-200">
                            <p class="text-xs text-slate-600 mt-2 flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Separate multiple keywords with commas for better results
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-between mt-8 pt-6 border-t border-white/30">
                            @if(request()->hasAny(['search', 'keywords', 'area_of_interest', 'year_from', 'year_to', 'supervisor', 'sort']))
                            <a href="{{ route('home') }}"
                                class="group inline-flex items-center px-6 py-3 bg-white/60 backdrop-blur-sm text-slate-700 font-semibold rounded-xl border border-slate-200/60 hover:bg-white/80 hover:border-slate-300/60 transition-all duration-200 shadow-sm hover:shadow-md mb-4 sm:mb-0">
                                <svg class="w-4 h-4 mr-2 group-hover:rotate-180 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Clear All Filters
                            </a>
                            @endif
                            
                            <div class="flex items-center text-sm text-slate-600">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-medium">{{ $reports->total() }}</span>
                                <span class="ml-1">research papers available</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Trending Research Topics -->
    @if(!empty($popularKeywords) && !request()->hasAny(['search', 'keywords', 'area_of_interest', 'year_from',
    'year_to', 'supervisor']))
    <section class="bg-white/20 backdrop-blur-md border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Trending Research Topics</h2>
                <p class="text-slate-600">Explore the most popular areas of academic research</p>
            </div>
            <div class="flex flex-wrap justify-center gap-3">
                @foreach(array_slice($popularKeywords, 0, 12, true) as $keyword => $count)
                <a href="{{ route('home', ['keywords' => $keyword]) }}"
                    class="group inline-flex items-center px-6 py-3 bg-white border border-slate-200 rounded-full text-sm font-medium text-slate-700 hover:bg-blue-50 hover:border-blue-200 hover:text-blue-700 transition-all duration-200 shadow-sm hover:shadow-md">
                    <span class="capitalize">{{ $keyword }}</span>
                    <span
                        class="ml-2 px-2 py-0.5 bg-slate-100 group-hover:bg-blue-100 text-xs text-slate-500 group-hover:text-blue-600 rounded-full transition-colors">{{ $count }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Research Collection -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($reports->count() > 0)
            <!-- Results Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">
                        @if(request()->hasAny(['search', 'keywords', 'area_of_interest', 'year_from', 'year_to',
                        'supervisor']))
                        Search Results
                        @else
                        Featured Research
                        @endif
                    </h2>
                    <p class="text-slate-600">
                        @if(request()->hasAny(['search', 'keywords', 'area_of_interest', 'year_from', 'year_to',
                        'supervisor']))
                        {{ $reports->total() }} {{ Str::plural('thesis', $reports->total()) }} found matching your
                        criteria
                        @else
                        Discover {{ $reports->total() }} published {{ Str::plural('thesis', $reports->total()) }} from
                        our academic community
                        @endif
                    </p>
                </div>
            </div>

            <!-- Research Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                @foreach($reports as $report)
                <article
                    class="group bg-white/40 backdrop-blur-md rounded-2xl shadow-lg border border-white/30 hover:shadow-xl hover:border-white/40 transition-all duration-300">
                    <div class="p-8">
                        <!-- Research Area Badge -->
                        @if($report->areaOfInterest)
                        <div class="mb-4">
                            <span
                                class="inline-flex items-center px-3 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-full">
                                {{ $report->areaOfInterest->name }}
                            </span>
                        </div>
                        @endif

                        <!-- Title -->
                        <h3
                            class="text-xl font-bold text-slate-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors">
                            <a href="{{ route('reports.show', $report) }}" class="block">
                                {{ $report->project_title }}
                            </a>
                        </h3>

                        <!-- Abstract Preview -->
                        <p class="text-slate-600 text-sm mb-6 line-clamp-3 leading-relaxed">
                            {{ Str::limit(strip_tags($report->abstract_md), 180) }}
                        </p>

                        <!-- Keywords -->
                        @if($report->keywords)
                        <div class="flex flex-wrap gap-2 mb-6">
                            @foreach(array_slice(json_decode($report->keywords, true) ?? [], 0, 3) as $keyword)
                            <span class="px-2 py-1 bg-slate-100 text-slate-600 text-xs font-medium rounded-md">
                                {{ $keyword }}
                            </span>
                            @endforeach
                            @if(count(json_decode($report->keywords, true) ?? []) > 3)
                            <span class="px-2 py-1 bg-slate-100 text-slate-500 text-xs rounded-md">
                                +{{ count(json_decode($report->keywords, true) ?? []) - 3 }} more
                            </span>
                            @endif
                        </div>
                        @endif

                        <!-- Metadata -->
                        <div class="border-t border-slate-200 pt-6">
                            <div class="flex items-center justify-between text-sm text-slate-500 mb-3">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                        <span class="font-medium">{{ $report->group->students->count() }}</span>
                                        <span
                                            class="ml-1">{{ Str::plural('Author', $report->group->students->count()) }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V8a1 1 0 011-1h3z">
                                            </path>
                                        </svg>
                                        <span class="font-medium">{{ $report->approved_at->format('Y') }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($report->group->supervisor)
                            <div class="text-xs text-slate-500">
                                <span class="font-medium">Supervised by:</span>
                                {{ $report->group->supervisor->fullname }}
                            </div>
                            @endif
                        </div>

                        <!-- Read More Link -->
                        <div class="mt-6">
                            <a href="{{ route('reports.show', $report) }}"
                                class="inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                                Read Full Thesis
                                <svg class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="flex justify-center">
                {{ $reports->links() }}
            </div>
            @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-24 h-24 mx-auto mb-6 bg-slate-100 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mb-3">No Research Found</h3>
                @if(request()->hasAny(['search', 'keywords', 'area_of_interest', 'year_from', 'year_to', 'supervisor']))
                <p class="text-slate-600 mb-8 max-w-md mx-auto">We couldn't find any theses matching your search
                    criteria. Try adjusting your filters or search terms.</p>
                <a href="{{ route('home') }}"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                        </path>
                    </svg>
                    Browse All Research
                </a>
                @else
                <p class="text-slate-600 max-w-md mx-auto">Our repository is growing! Check back soon for new academic
                    contributions and research publications.</p>
                @endif
            </div>
            @endif
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-slate-900/80 backdrop-blur-md text-white border-t border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <!-- Brand -->
                <div class="mb-8">
                    <div class="flex items-center justify-center mb-4">
                        <img src="{{ asset('Picture1.png') }}" alt="Picture1" class="w-12 h-auto object-contain">
                        <div class="ml-3">
                            <h3 class="text-xl font-bold">University Thesis Repository System</h3>
                            <p class="text-sm text-slate-400">Academic Research Archive</p>
                        </div>
                    </div>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-2xl mx-auto">
                        Advancing knowledge through open access to academic research. Discover, explore, and contribute
                        to the global scholarly community.
                    </p>
                </div>

                <div class="border-t border-slate-800 pt-8">
                    <p class="text-sm text-slate-400">
                        &copy; {{ date('Y') }} University Thesis Repository System. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');

        // Get all filter elements by their specific names
        const searchInput = document.querySelector('input[name="search"]');
        const keywordsInput = document.querySelector('input[name="keywords"]');
        const areaSelect = document.querySelector('select[name="area_of_interest"]');
        const yearFromSelect = document.querySelector('select[name="year_from"]');
        const supervisorSelect = document.querySelector('select[name="supervisor"]');
        const sortSelect = document.querySelector('select[name="sort"]');

        // Function to submit form
        function submitForm() {
            form.submit();
        }

        // Add event listeners to all select dropdowns for immediate submission
        if (areaSelect) {
            areaSelect.addEventListener('change', submitForm);
        }
        if (yearFromSelect) {
            yearFromSelect.addEventListener('change', submitForm);
        }
        if (supervisorSelect) {
            supervisorSelect.addEventListener('change', submitForm);
        }
        if (sortSelect) {
            sortSelect.addEventListener('change', submitForm);
        }

        // Add debounced event listeners to text inputs
        let debounceTimer;

        function handleTextInput() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(submitForm, 500);
        }

        if (searchInput) {
            searchInput.addEventListener('input', handleTextInput);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitForm();
                }
            });
        }

        if (keywordsInput) {
            keywordsInput.addEventListener('input', handleTextInput);
            keywordsInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    submitForm();
                }
            });
        }
    });
    </script>
</body>

</html>