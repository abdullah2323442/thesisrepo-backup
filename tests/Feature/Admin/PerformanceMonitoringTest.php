<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Services\PerformanceMonitoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['1']), // Admin
        ]);
    }

    public function test_performance_dashboard_displays_correctly()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/performance');

        $response->assertStatus(200);
        $response->assertSee('Performance Monitoring');
        $response->assertSee('System Health');
        $response->assertSee('Key Metrics Overview');
        $response->assertViewHas(['metrics', 'health']);
    }

    public function test_performance_dashboard_requires_admin_access()
    {
        $regularUser = User::factory()->create([
            'login_type' => 'student',
        ]);

        $response = $this->actingAs($regularUser)->get('/admin/performance');

        $response->assertStatus(403);
    }

    public function test_performance_metrics_api_returns_json()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/performance/metrics');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function test_system_health_api_returns_status()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/performance/health');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('overall_status', $data['data']);
        $this->assertArrayHasKey('checks', $data['data']);
    }

    public function test_database_metrics_api_returns_database_info()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/performance/database');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
    }

    public function test_api_metrics_returns_api_status()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/performance/api');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_security_metrics_returns_security_info()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/performance/security');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_clear_cache_functionality()
    {
        // Set some cache data
        Cache::put('api_metrics_' . now()->format('Y-m-d-H'), 'test_data', 3600);
        
        $response = $this->actingAs($this->adminUser)->post('/admin/performance/clear-cache');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Performance cache cleared successfully',
        ]);
    }

    public function test_export_report_functionality()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/performance/export');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('filename', $data);
        $this->assertStringContains('performance_report_', $data['filename']);
    }

    public function test_system_component_tests()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/performance/test');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        
        $data = $response->json();
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('component_tested', $data);
        $this->assertEquals('all', $data['component_tested']);
    }

    public function test_specific_component_test()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/performance/test?component=database');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'component_tested' => 'database',
        ]);
        
        $data = $response->json();
        $this->assertArrayHasKey('database', $data['data']);
    }

    public function test_performance_monitoring_service_system_metrics()
    {
        $service = new PerformanceMonitoringService();
        $metrics = $service->getSystemMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('system', $metrics);
        $this->assertArrayHasKey('database', $metrics);
        $this->assertArrayHasKey('users', $metrics);
        $this->assertArrayHasKey('meta', $metrics);
        
        // Check system info
        $this->assertArrayHasKey('php_version', $metrics['system']);
        $this->assertArrayHasKey('laravel_version', $metrics['system']);
        $this->assertArrayHasKey('environment', $metrics['system']);
        
        // Check meta info
        $this->assertArrayHasKey('generated_at', $metrics['meta']);
        $this->assertArrayHasKey('execution_time_ms', $metrics['meta']);
        $this->assertArrayHasKey('memory_usage_mb', $metrics['meta']);
    }

    public function test_performance_monitoring_service_system_health()
    {
        $service = new PerformanceMonitoringService();
        $health = $service->getSystemHealth();

        $this->assertIsArray($health);
        $this->assertArrayHasKey('overall_status', $health);
        $this->assertArrayHasKey('checks', $health);
        
        // Check that database health is included
        $this->assertArrayHasKey('database', $health['checks']);
        $this->assertArrayHasKey('status', $health['checks']['database']);
        
        // Check that cache health is included
        $this->assertArrayHasKey('cache', $health['checks']);
        $this->assertArrayHasKey('status', $health['checks']['cache']);
        
        // Check that storage health is included
        $this->assertArrayHasKey('storage', $health['checks']);
        $this->assertArrayHasKey('status', $health['checks']['storage']);
    }

    public function test_performance_event_recording()
    {
        $service = new PerformanceMonitoringService();
        
        // This should not throw an exception
        $service->recordPerformanceEvent('test_event', [
            'test_data' => 'test_value',
            'user_id' => $this->adminUser->id,
        ]);
        
        $this->assertTrue(true); // If we get here, the method worked
    }

    public function test_performance_dashboard_handles_errors_gracefully()
    {
        // Mock a database error by using an invalid connection
        config(['database.connections.testing.database' => '/invalid/path']);
        
        $response = $this->actingAs($this->adminUser)->get('/admin/performance');

        // Should still return 200 but with error data
        $response->assertStatus(200);
        $response->assertSee('Performance Monitoring');
    }

    public function test_performance_metrics_caching()
    {
        $service = new PerformanceMonitoringService();
        
        // First call should generate metrics
        $metrics1 = $service->getSystemMetrics();
        
        // Check that some data is cached
        $cacheKey = 'api_metrics_' . now()->format('Y-m-d-H');
        $this->assertTrue(Cache::has($cacheKey));
        
        // Second call should use cached data for some metrics
        $metrics2 = $service->getSystemMetrics();
        
        $this->assertIsArray($metrics1);
        $this->assertIsArray($metrics2);
    }

    public function test_admin_navigation_includes_performance_link()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Performance Monitoring');
        $response->assertSee(route('admin.performance.index'));
    }

    public function test_performance_dashboard_shows_real_data()
    {
        // Create some test data
        User::factory()->count(5)->create(['login_type' => 'student']);
        User::factory()->count(3)->create(['login_type' => 'teacher']);
        
        $response = $this->actingAs($this->adminUser)->get('/admin/performance');

        $response->assertStatus(200);
        
        // Check that the view has metrics data
        $metrics = $response->viewData('metrics');
        $this->assertIsArray($metrics);
        
        if (isset($metrics['users']['total_users'])) {
            $this->assertGreaterThan(0, $metrics['users']['total_users']);
        }
    }

    public function test_performance_monitoring_requires_authentication()
    {
        $response = $this->get('/admin/performance');
        $response->assertRedirect('/login');
        
        $response = $this->get('/admin/performance/metrics');
        $response->assertRedirect('/login');
        
        $response = $this->get('/admin/performance/health');
        $response->assertRedirect('/login');
    }

    public function test_performance_monitoring_logs_access()
    {
        $response = $this->actingAs($this->adminUser)->get('/admin/performance/metrics');
        
        $response->assertStatus(200);
        
        // Check that the response includes timestamp
        $data = $response->json();
        $this->assertArrayHasKey('timestamp', $data);
    }
}