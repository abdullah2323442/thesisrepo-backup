<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(); // Skip middleware for testing
    }

    public function test_admin_dashboard_displays_correctly()
    {
        $admin = User::factory()->create([
            'login_type' => 'teacher',
            'typeIds' => ['1'] // Admin type
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Total Users');
        $response->assertSee('Students');
        $response->assertSee('Teachers');
        $response->assertSee('Admins');
    }

    public function test_admin_dashboard_shows_statistics()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        // Create test data
        User::factory()->count(5)->create(['login_type' => 'student']);
        User::factory()->count(3)->create(['login_type' => 'teacher']);
        User::factory()->count(2)->create(['login_type' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        $this->assertEquals(11, $stats['total_users']); // 5 + 3 + 2 + 1 (admin)
        $this->assertEquals(5, $stats['total_students']);
        $this->assertEquals(4, $stats['total_teachers']); // 3 + 1 (admin)
        $this->assertEquals(2, $stats['total_admins']);
    }

    public function test_admin_dashboard_requires_authentication()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_admin_dashboard_layout_includes_navigation()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Admin Panel');
        $response->assertSee('Area of Interest');
        $response->assertSee('Supervisor Management');
        $response->assertSee('Batch Management');
    }

    public function test_admin_dashboard_shows_system_status()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('System Status');
        $response->assertSee('Database');
        $response->assertSee('API Services');
        $response->assertSee('Storage');
    }

    public function test_admin_dashboard_has_logout_functionality()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Logout');
        $response->assertSee('route(\'logout\')');
    }
}