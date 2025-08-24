@extends('layouts.admin')

@section('title', 'Performance Monitoring')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Performance Monitoring</h1>
            <p class="text-gray-600">Monitor system performance, health, and metrics</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="refreshMetrics()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
            <button onclick="exportReport()" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Report
            </button>
        </div>
    </div>

    <!-- System Health Status -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">System Health</h2>
            <div class="flex items-center space-x-2">
                <div class="w-3 h-3 rounded-full {{ $health['overall_status'] === 'healthy' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                <span class="text-sm font-medium {{ $health['overall_status'] === 'healthy' ? 'text-green-700' : 'text-red-700' }}">
                    {{ ucfirst($health['overall_status']) }}
                </span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($health['checks'] ?? [] as $component => $check)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                    <div class="w-2 h-2 rounded-full {{ $check['status'] === 'healthy' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                    <span class="text-sm font-medium text-gray-700">{{ ucfirst($component) }}</span>
                </div>
                <span class="text-xs text-gray-500">{{ $check['status'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Key Metrics Overview -->
    @if(isset($metrics['system']))
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- System Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">PHP Version</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $metrics['system']['php_version'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Memory Usage -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Memory Usage</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $metrics['meta']['memory_usage_mb'] ?? 'N/A' }}MB</p>
                </div>
            </div>
        </div>

        <!-- Database Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Database</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $metrics['database']['connection_status'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $metrics['users']['total_users'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Metrics Tabs -->
    <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('database')" class="tab-button active border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Database
                </button>
                <button onclick="showTab('api')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    API Performance
                </button>
                <button onclick="showTab('security')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Security
                </button>
                <button onclick="showTab('system')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    System
                </button>
                <button onclick="showTab('errors')" class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Errors
                </button>
            </nav>
        </div>

        <!-- Database Tab -->
        <div id="database-tab" class="tab-content p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Database Performance</h3>
            @if(isset($metrics['database']))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Connection Status</h4>
                    <p class="text-lg font-semibold text-gray-900">{{ $metrics['database']['connection_status'] }}</p>
                    <p class="text-xs text-gray-500">{{ $metrics['database']['connection_time_ms'] ?? 'N/A' }}ms response time</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Total Records</h4>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($metrics['database']['total_records'] ?? 0) }}</p>
                    <p class="text-xs text-gray-500">Across all tables</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Database Size</h4>
                    <p class="text-lg font-semibold text-gray-900">{{ $metrics['database']['database_size']['size_mb'] ?? 'N/A' }}MB</p>
                    <p class="text-xs text-gray-500">{{ $metrics['database']['database_size']['table_count'] ?? 'N/A' }} tables</p>
                </div>
            </div>

            @if(isset($metrics['database']['table_counts']))
            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Table Record Counts</h4>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    @foreach($metrics['database']['table_counts'] as $table => $count)
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($count) }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $table)) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($metrics['database']['query_performance']))
            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Query Performance</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($metrics['database']['query_performance'] as $query => $time)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $query)) }}</h5>
                        <p class="text-lg font-semibold text-gray-900">{{ $time }}{{ is_numeric($time) ? 'ms' : '' }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif
        </div>

        <!-- API Tab -->
        <div id="api-tab" class="tab-content p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">API Performance</h3>
            @if(isset($metrics['api']))
            
            @if(isset($metrics['api']['external_api_status']))
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">External API Status</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($metrics['api']['external_api_status'] as $api => $status)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <h5 class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $api)) }}</h5>
                            <span class="px-2 py-1 text-xs rounded-full {{ $status['status'] === 'Available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $status['status'] }}
                            </span>
                        </div>
                        @if(isset($status['response_time_ms']))
                        <p class="text-sm text-gray-600 mt-1">Response: {{ $status['response_time_ms'] }}ms</p>
                        @endif
                        @if(isset($status['error']))
                        <p class="text-sm text-red-600 mt-1">{{ $status['error'] }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($metrics['api']['api_response_times']))
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Response Time Statistics</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($metrics['api']['api_response_times'] as $metric => $value)
                    @if($metric !== 'note')
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <p class="text-lg font-semibold text-gray-900">{{ $value }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', $metric)) }}</p>
                    </div>
                    @endif
                    @endforeach
                </div>
                @if(isset($metrics['api']['api_response_times']['note']))
                <p class="text-xs text-gray-500 mt-2">{{ $metrics['api']['api_response_times']['note'] }}</p>
                @endif
            </div>
            @endif

            @if(isset($metrics['api']['rate_limiting_status']))
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Rate Limiting Status</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($metrics['api']['rate_limiting_status'] as $limiter => $status)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $limiter)) }}</h5>
                        <div class="flex items-center mt-1">
                            <div class="w-2 h-2 rounded-full {{ $status['active'] ? 'bg-green-500' : 'bg-red-500' }} mr-2"></div>
                            <span class="text-sm text-gray-600">{{ $status['active'] ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif
        </div>

        <!-- Security Tab -->
        <div id="security-tab" class="tab-content p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Security Metrics</h3>
            @if(isset($metrics['security']))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Failed Logins (24h)</h4>
                    <p class="text-lg font-semibold text-gray-900">{{ $metrics['security']['failed_logins_24h'] ?? 0 }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Rate Limit Violations</h4>
                    <p class="text-lg font-semibold text-gray-900">{{ $metrics['security']['rate_limit_violations'] ?? 0 }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">CSRF Failures</h4>
                    <p class="text-lg font-semibold text-gray-900">{{ $metrics['security']['csrf_failures'] ?? 0 }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Blocked IPs</h4>
                    <p class="text-lg font-semibold text-gray-900">{{ $metrics['security']['suspicious_activity']['blocked_ips'] ?? 0 }}</p>
                </div>
            </div>

            @if(isset($metrics['security']['security_headers']))
            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Security Headers</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($metrics['security']['security_headers'] as $header => $status)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $header)) }}</span>
                        <span class="px-2 py-1 text-xs rounded-full {{ $status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $status ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif
        </div>

        <!-- System Tab -->
        <div id="system-tab" class="tab-content p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">System Information</h3>
            @if(isset($metrics['system']))
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Environment</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">PHP Version:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $metrics['system']['php_version'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Laravel Version:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $metrics['system']['laravel_version'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Environment:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $metrics['system']['environment'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Debug Mode:</span>
                            <span class="text-sm font-medium {{ $metrics['system']['debug_mode'] ? 'text-red-600' : 'text-green-600' }}">
                                {{ $metrics['system']['debug_mode'] ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">PHP Configuration</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Memory Limit:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $metrics['system']['memory_limit'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Max Execution Time:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $metrics['system']['max_execution_time'] }}s</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Upload Max Size:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $metrics['system']['upload_max_filesize'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Post Max Size:</span>
                            <span class="text-sm font-medium text-gray-900">{{ $metrics['system']['post_max_size'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($metrics['performance']))
            <div class="mt-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Performance Metrics</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="text-sm font-medium text-gray-700">Memory Usage</h5>
                        <p class="text-lg font-semibold text-gray-900">{{ $metrics['performance']['memory_usage']['current_mb'] }}MB</p>
                        <p class="text-xs text-gray-500">Peak: {{ $metrics['performance']['memory_usage']['peak_mb'] }}MB</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="text-sm font-medium text-gray-700">Response Time</h5>
                        <p class="text-lg font-semibold text-gray-900">{{ $metrics['performance']['average_response_time'] }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h5 class="text-sm font-medium text-gray-700">Slow Queries</h5>
                        <p class="text-lg font-semibold text-gray-900">{{ $metrics['performance']['slow_queries'] }}</p>
                    </div>
                </div>
            </div>
            @endif
            @endif
        </div>

        <!-- Errors Tab -->
        <div id="errors-tab" class="tab-content p-6 hidden">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Error Monitoring</h3>
            @if(isset($metrics['errors']))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Errors (24h)</h4>
                    <p class="text-lg font-semibold text-red-600">{{ $metrics['errors']['errors_24h'] ?? 0 }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Warnings (24h)</h4>
                    <p class="text-lg font-semibold text-yellow-600">{{ $metrics['errors']['warnings_24h'] ?? 0 }}</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Critical Errors</h4>
                    <p class="text-lg font-semibold text-red-800">{{ $metrics['errors']['critical_errors'] ?? 0 }}</p>
                </div>
            </div>

            @if(isset($metrics['errors']['most_common_errors']))
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-3">Most Common Errors</h4>
                <div class="space-y-3">
                    @foreach($metrics['errors']['most_common_errors'] as $error => $count)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">{{ $error }}</span>
                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">{{ $count }} occurrences</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            @endif
        </div>
    </div>

    <!-- System Tests -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">System Tests</h2>
            <button onclick="runSystemTests()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Run Tests
            </button>
        </div>
        
        <div id="test-results" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700">Database</h4>
                <p class="text-sm text-gray-500">Click "Run Tests" to check</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700">Cache</h4>
                <p class="text-sm text-gray-500">Click "Run Tests" to check</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700">Storage</h4>
                <p class="text-sm text-gray-500">Click "Run Tests" to check</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700">External API</h4>
                <p class="text-sm text-gray-500">Click "Run Tests" to check</p>
            </div>
        </div>
    </div>

    <!-- Last Updated -->
    @if(isset($metrics['meta']))
    <div class="text-center text-sm text-gray-500">
        Last updated: {{ \Carbon\Carbon::parse($metrics['meta']['generated_at'])->format('M j, Y g:i A') }}
        (Generated in {{ $metrics['meta']['execution_time_ms'] }}ms)
    </div>
    @endif
</div>

<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-indigo-500', 'text-indigo-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Add active class to selected tab button
    event.target.classList.add('active', 'border-indigo-500', 'text-indigo-600');
    event.target.classList.remove('border-transparent', 'text-gray-500');
}

// Refresh metrics
function refreshMetrics() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Refreshing...';
    button.disabled = true;
    
    fetch('{{ route("admin.performance.metrics") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Simple refresh for now
            } else {
                alert('Failed to refresh metrics: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to refresh metrics');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

// Export report
function exportReport() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Exporting...';
    button.disabled = true;
    
    fetch('{{ route("admin.performance.export") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Create and download file
                const blob = new Blob([JSON.stringify(data.data, null, 2)], { type: 'application/json' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = data.filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            } else {
                alert('Failed to export report: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to export report');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

// Run system tests
function runSystemTests() {
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Running Tests...';
    button.disabled = true;
    
    fetch('{{ route("admin.performance.test") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateTestResults(data.data);
            } else {
                alert('Failed to run tests: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to run tests');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

// Update test results
function updateTestResults(results) {
    const container = document.getElementById('test-results');
    container.innerHTML = '';
    
    Object.entries(results).forEach(([component, result]) => {
        const div = document.createElement('div');
        div.className = 'p-4 bg-gray-50 rounded-lg';
        
        const statusColor = result.status === 'healthy' ? 'text-green-600' : 'text-red-600';
        const statusIcon = result.status === 'healthy' ? '✓' : '✗';
        
        let additionalInfo = '';
        
        // Handle different component types
        if (component === 'database') {
            additionalInfo = result.connection_time_ms ? `<p class="text-xs text-gray-500 mt-1">Connection: ${result.connection_time_ms}ms</p>` : '';
            if (result.query_time_ms) {
                additionalInfo += `<p class="text-xs text-gray-500">Query: ${result.query_time_ms}ms</p>`;
            }
        } else if (component === 'cache') {
            additionalInfo = result.driver ? `<p class="text-xs text-gray-500 mt-1">Driver: ${result.driver}</p>` : '';
        } else if (component === 'storage') {
            additionalInfo = result.storage_path ? `<p class="text-xs text-gray-500 mt-1">Path: ${result.storage_path}</p>` : '';
        } else if (component === 'api') {
            if (result.average_response_time_ms) {
                additionalInfo = `<p class="text-xs text-gray-500 mt-1">Avg Response: ${result.average_response_time_ms}ms</p>`;
            }
            if (result.apis_tested !== undefined) {
                additionalInfo += `<p class="text-xs text-gray-500">APIs Tested: ${result.apis_tested}/${result.apis_configured}</p>`;
            }
        }
        
        div.innerHTML = `
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-gray-700">${component.charAt(0).toUpperCase() + component.slice(1)}</h4>
                <span class="${statusColor} font-bold">${statusIcon}</span>
            </div>
            <p class="text-sm ${statusColor} mt-1">${result.status}</p>
            ${result.error ? `<p class="text-xs text-red-500 mt-1">${result.error}</p>` : ''}
            ${additionalInfo}
        `;
        
        container.appendChild(div);
    });
}

// Auto-refresh every 5 minutes
setInterval(() => {
    fetch('{{ route("admin.performance.health") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update health status indicator
                console.log('Health check:', data.data.overall_status);
            }
        })
        .catch(error => console.error('Health check failed:', error));
}, 300000); // 5 minutes
</script>
@endsection