<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PerformanceMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class PerformanceController extends Controller
{
    private PerformanceMonitoringService $performanceService;

    public function __construct(PerformanceMonitoringService $performanceService)
    {
        $this->performanceService = $performanceService;
    }

    /**
     * Display the performance monitoring dashboard
     */
    public function index(): View
    {
        try {
            $metrics = $this->performanceService->getSystemMetrics();
            $health = $this->performanceService->getSystemHealth();

            return view('admin.performance.index', compact('metrics', 'health'));

        } catch (\Exception $e) {
            Log::error('Failed to load performance dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('admin.performance.index', [
                'metrics' => ['error' => 'Failed to load metrics'],
                'health' => ['overall_status' => 'error', 'checks' => []],
            ]);
        }
    }

    /**
     * Get real-time metrics via AJAX
     */
    public function metrics(): JsonResponse
    {
        try {
            $metrics = $this->performanceService->getSystemMetrics();
            
            return response()->json([
                'success' => true,
                'data' => $metrics,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch performance metrics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch metrics',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * Get system health status
     */
    public function health(): JsonResponse
    {
        try {
            $health = $this->performanceService->getSystemHealth();
            
            return response()->json([
                'success' => true,
                'data' => $health,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch system health', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch health status',
                'message' => $e->getMessage(),
                'timestamp' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * Get database metrics
     */
    public function database(): JsonResponse
    {
        try {
            $metrics = $this->performanceService->getSystemMetrics();
            
            return response()->json([
                'success' => true,
                'data' => $metrics['database'] ?? [],
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch database metrics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get API performance metrics
     */
    public function api(): JsonResponse
    {
        try {
            $metrics = $this->performanceService->getSystemMetrics();
            
            return response()->json([
                'success' => true,
                'data' => $metrics['api'] ?? [],
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch API metrics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get security metrics
     */
    public function security(): JsonResponse
    {
        try {
            $metrics = $this->performanceService->getSystemMetrics();
            
            return response()->json([
                'success' => true,
                'data' => $metrics['security'] ?? [],
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch security metrics',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear performance cache
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            // Clear performance-related cache keys
            $cacheKeys = [
                'api_metrics_' . now()->format('Y-m-d-H'),
                'user_metrics_' . now()->format('Y-m-d-H'),
            ];

            foreach ($cacheKeys as $key) {
                \Illuminate\Support\Facades\Cache::forget($key);
            }

            Log::info('Performance cache cleared', [
                'admin_user' => auth()->id(),
                'keys_cleared' => count($cacheKeys),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Performance cache cleared successfully',
                'keys_cleared' => count($cacheKeys),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to clear performance cache', [
                'error' => $e->getMessage(),
                'admin_user' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to clear cache',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export performance report
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $metrics = $this->performanceService->getSystemMetrics();
            $health = $this->performanceService->getSystemHealth();

            $report = [
                'report_generated_at' => now()->toISOString(),
                'system_health' => $health,
                'performance_metrics' => $metrics,
                'summary' => [
                    'overall_status' => $health['overall_status'],
                    'total_users' => $metrics['users']['total_users'] ?? 0,
                    'database_status' => $metrics['database']['connection_status'] ?? 'Unknown',
                    'memory_usage_mb' => $metrics['meta']['memory_usage_mb'] ?? 0,
                ],
            ];

            Log::info('Performance report exported', [
                'admin_user' => auth()->id(),
                'report_size' => strlen(json_encode($report)),
            ]);

            return response()->json([
                'success' => true,
                'data' => $report,
                'filename' => 'performance_report_' . now()->format('Y-m-d_H-i-s') . '.json',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to export performance report', [
                'error' => $e->getMessage(),
                'admin_user' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to export report',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test system components
     */
    public function test(Request $request): JsonResponse
    {
        $component = $request->get('component', 'all');
        
        try {
            $results = [];

            if ($component === 'all' || $component === 'database') {
                $results['database'] = $this->testDatabase();
            }

            if ($component === 'all' || $component === 'cache') {
                $results['cache'] = $this->testCache();
            }

            if ($component === 'all' || $component === 'storage') {
                $results['storage'] = $this->testStorage();
            }

            if ($component === 'all' || $component === 'api') {
                $results['api'] = $this->testExternalApi();
            }

            Log::info('System component test completed', [
                'component' => $component,
                'admin_user' => auth()->id(),
                'results' => array_keys($results),
            ]);

            return response()->json([
                'success' => true,
                'data' => $results,
                'component_tested' => $component,
                'timestamp' => now()->toISOString(),
            ]);

        } catch (\Exception $e) {
            Log::error('System component test failed', [
                'component' => $component,
                'error' => $e->getMessage(),
                'admin_user' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Component test failed',
                'message' => $e->getMessage(),
                'component' => $component,
            ], 500);
        }
    }

    /**
     * Test database connectivity and performance
     */
    private function testDatabase(): array
    {
        $start = microtime(true);
        
        try {
            // Test connection
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
            $connectionTime = round((microtime(true) - $start) * 1000, 2);

            // Test simple query
            $queryStart = microtime(true);
            $userCount = \App\Models\User::count();
            $queryTime = round((microtime(true) - $queryStart) * 1000, 2);

            return [
                'status' => 'healthy',
                'connection_time_ms' => $connectionTime,
                'query_time_ms' => $queryTime,
                'test_query_result' => $userCount,
                'driver' => config('database.default'),
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'connection_time_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }
    }

    /**
     * Test cache functionality
     */
    private function testCache(): array
    {
        try {
            $testKey = 'performance_test_' . time();
            $testValue = 'test_value_' . rand(1000, 9999);

            // Test write
            \Illuminate\Support\Facades\Cache::put($testKey, $testValue, 60);

            // Test read
            $retrievedValue = \Illuminate\Support\Facades\Cache::get($testKey);

            // Test delete
            \Illuminate\Support\Facades\Cache::forget($testKey);

            $success = $retrievedValue === $testValue;

            return [
                'status' => $success ? 'healthy' : 'unhealthy',
                'driver' => config('cache.default'),
                'write_success' => true,
                'read_success' => $retrievedValue === $testValue,
                'delete_success' => true,
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'driver' => config('cache.default'),
            ];
        }
    }

    /**
     * Test storage functionality
     */
    private function testStorage(): array
    {
        try {
            $testFile = storage_path('app/performance_test_' . time() . '.txt');
            $testContent = 'Performance test content: ' . now()->toISOString();

            // Test write
            file_put_contents($testFile, $testContent);

            // Test read
            $readContent = file_get_contents($testFile);

            // Test delete
            unlink($testFile);

            $success = $readContent === $testContent;

            return [
                'status' => $success ? 'healthy' : 'unhealthy',
                'write_success' => true,
                'read_success' => $readContent === $testContent,
                'delete_success' => !file_exists($testFile),
                'storage_path' => storage_path('app'),
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Test external API connectivity
     */
    private function testExternalApi(): array
    {
        $apis = [
            'student_api' => config('app.external_api_login_url'),
            'teacher_api' => config('app.external_api_teacher_login_url'),
        ];

        $results = [];
        $overallStatus = 'healthy';
        $totalResponseTime = 0;
        $testedApis = 0;
        $errors = [];

        foreach ($apis as $name => $url) {
            if (!$url) {
                $results[$name] = ['status' => 'not_configured'];
                continue;
            }

            try {
                $start = microtime(true);
                $response = \Illuminate\Support\Facades\Http::timeout(5)->get($url);
                $responseTime = round((microtime(true) - $start) * 1000, 2);
                $totalResponseTime += $responseTime;
                $testedApis++;

                $apiStatus = $response->successful() ? 'healthy' : 'unhealthy';
                if ($apiStatus === 'unhealthy') {
                    $overallStatus = 'unhealthy';
                }

                $results[$name] = [
                    'status' => $apiStatus,
                    'response_time_ms' => $responseTime,
                    'status_code' => $response->status(),
                ];

            } catch (\Exception $e) {
                $overallStatus = 'unhealthy';
                $errors[] = $e->getMessage();
                $results[$name] = [
                    'status' => 'unreachable',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Return overall API status with details
        return [
            'status' => $overallStatus,
            'average_response_time_ms' => $testedApis > 0 ? round($totalResponseTime / $testedApis, 2) : null,
            'apis_tested' => $testedApis,
            'apis_configured' => count($apis),
            'details' => $results,
            'errors' => $errors,
        ];
    }
}