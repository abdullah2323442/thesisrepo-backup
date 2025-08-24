<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LogoutFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_layout_has_logout_button()
    {
        $user = User::factory()->create([
            'login_type' => 'teacher'
        ]);

        // Skip middleware for this test to focus on layout
        $this->withoutMiddleware();

        $response = $this->actingAs($user)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Logout');
    }

    public function test_advisor_layout_has_logout_button()
    {
        $user = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 123
        ]);

        // Create supervisor record for advisor access
        \App\Models\Supervisor::create([
            'api_id' => 123,
            'fullname' => $user->name,
            'gender' => 'Male',
            'email' => $user->email,
            'department' => 'Computer Science & Engineering',
            'designation' => 'Assistant Professor',
            'is_active' => true,
            'thesis_limit' => 5,
        ]);

        $response = $this->actingAs($user)->get('/advisor/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Logout');
    }

    public function test_supervisor_layout_has_logout_button()
    {
        $user = User::factory()->create([
            'login_type' => 'teacher'
        ]);

        $response = $this->actingAs($user)->get('/supervisor/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Logout');
    }

    public function test_student_layout_has_logout_button()
    {
        $user = User::factory()->create([
            'login_type' => 'student',
            'roll' => '2020123456'
        ]);

        $response = $this->actingAs($user)->get('/student/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Logout');
    }

    public function test_teacher_dashboard_has_logout_functionality()
    {
        $user = User::factory()->create([
            'login_type' => 'teacher'
        ]);

        $response = $this->actingAs($user)->get('/teacher/dashboard');
        
        $response->assertStatus(200);
        // Teacher dashboard uses app layout which includes navigation with logout
        $response->assertSee('Log Out');
    }

    public function test_logout_actually_works()
    {
        $user = User::factory()->create();

        // Login user
        $this->actingAs($user);
        $this->assertAuthenticated();

        // Logout
        $response = $this->post('/logout');
        
        // Should redirect and user should be logged out
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}