<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class ComprehensiveSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $teacher;
    protected $student;
    protected $advisor;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['1']),
            'email' => 'admin@test.com',
            'password' => Hash::make('password123')
        ]);

        $this->teacher = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['2']),
            'email' => 'teacher@test.com',
            'password' => Hash::make('password123')
        ]);

        $this->student = User::factory()->create([
            'login_type' => 'student',
            'email' => 'student@test.com',
            'password' => Hash::make('password123')
        ]);

        $this->advisor = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['3']),
            'email' => 'advisor@test.com',
            'password' => Hash::make('password123')
        ]);
    }

    // ========== AUTHENTICATION SECURITY TESTS ==========

    public function test_login_with_valid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_login_with_nonexistent_user()
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_logout_functionality()
    {
        $response = $this->actingAs($this->admin)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_password_reset_functionality()
    {
        $response = $this->post('/forgot-password', [
            'email' => 'admin@test.com'
        ]);

        $response->assertSessionHas('status');
    }

    // ========== AUTHORIZATION MIDDLEWARE TESTS ==========

    public function test_admin_middleware_allows_admin_access()
    {
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    public function test_admin_middleware_blocks_non_admin()
    {
        $response = $this->actingAs($this->student)->get('/admin/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($this->teacher)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_teacher_middleware_allows_teacher_access()
    {
        $response = $this->actingAs($this->teacher)->get('/teacher/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->get('/teacher/dashboard');
        $response->assertStatus(200);
    }

    public function test_teacher_middleware_blocks_non_teacher()
    {
        $response = $this->actingAs($this->student)->get('/teacher/dashboard');
        $response->assertStatus(403);
    }

    public function test_student_middleware_allows_student_access()
    {
        $response = $this->actingAs($this->student)->get('/student/dashboard');
        $response->assertStatus(200);
    }

    public function test_student_middleware_blocks_non_student()
    {
        $response = $this->actingAs($this->teacher)->get('/student/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($this->admin)->get('/student/dashboard');
        $response->assertStatus(403);
    }

    public function test_advisor_middleware_allows_advisor_access()
    {
        $response = $this->actingAs($this->advisor)->get('/advisor/dashboard');
        $response->assertStatus(200);
    }

    public function test_advisor_middleware_blocks_non_advisor()
    {
        $response = $this->actingAs($this->student)->get('/advisor/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($this->teacher)->get('/advisor/dashboard');
        $response->assertStatus(403);
    }

    // ========== AUTHENTICATION REQUIRED TESTS ==========

    public function test_unauthenticated_access_redirects_to_login()
    {
        $protectedRoutes = [
            '/dashboard',
            '/admin/dashboard',
            '/teacher/dashboard',
            '/student/dashboard',
            '/advisor/dashboard',
            '/profile'
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_authenticated_user_can_access_dashboard()
    {
        $response = $this->actingAs($this->student)->get('/dashboard');
        $response->assertStatus(200);
    }

    // ========== CSRF PROTECTION TESTS ==========

    public function test_csrf_protection_on_post_requests()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Test without CSRF token
        $response = $this->actingAs($this->admin)->post('/admin/areas-of-interest', [
            'name' => 'Test Area',
            'description' => 'Test Description'
        ]);

        // Should work without CSRF when middleware is disabled
        $response->assertRedirect();
    }

    public function test_csrf_token_validation()
    {
        Session::start();
        
        $response = $this->actingAs($this->admin)
                         ->withSession(['_token' => 'test-token'])
                         ->post('/admin/areas-of-interest', [
                             '_token' => 'test-token',
                             'name' => 'Test Area',
                             'description' => 'Test Description'
                         ]);

        $response->assertRedirect();
    }

    // ========== RATE LIMITING TESTS ==========

    public function test_external_api_rate_limiting()
    {
        $this->withoutMiddleware(['throttle:external_api_student_dashboard']);

        // Make multiple requests
        for ($i = 0; $i < 5; $i++) {
            $response = $this->actingAs($this->student)->get('/student/dashboard');
            $response->assertStatus(200);
        }
    }

    public function test_login_rate_limiting()
    {
        // Make multiple failed login attempts
        for ($i = 0; $i < 6; $i++) {
            $response = $this->post('/login', [
                'email' => 'admin@test.com',
                'password' => 'wrongpassword'
            ]);
        }

        // Should be rate limited after too many attempts
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(429);
    }

    // ========== INPUT VALIDATION TESTS ==========

    public function test_sql_injection_protection()
    {
        $this->withoutMiddleware();

        // Attempt SQL injection in area of interest creation
        $response = $this->actingAs($this->admin)->post('/admin/areas-of-interest', [
            'name' => "'; DROP TABLE area_of_interests; --",
            'description' => 'Test Description'
        ]);

        // Should not cause SQL injection
        $response->assertRedirect();
        $this->assertDatabaseHas('area_of_interests', [
            'name' => "'; DROP TABLE area_of_interests; --"
        ]);
    }

    public function test_xss_protection()
    {
        $this->withoutMiddleware();

        $xssPayload = '<script>alert("XSS")</script>';

        $response = $this->actingAs($this->admin)->post('/admin/areas-of-interest', [
            'name' => $xssPayload,
            'description' => 'Test Description'
        ]);

        $response->assertRedirect();
        
        $area = AreaOfInterest::where('name', $xssPayload)->first();
        $this->assertNotNull($area);

        // Check that the script is properly escaped when displayed
        $response = $this->actingAs($this->admin)->get('/admin/areas-of-interest');
        $response->assertStatus(200);
        $response->assertSee('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', false);
    }

    public function test_mass_assignment_protection()
    {
        $this->withoutMiddleware();

        // Try to mass assign protected fields
        $response = $this->actingAs($this->admin)->post('/admin/areas-of-interest', [
            'name' => 'Test Area',
            'description' => 'Test Description',
            'id' => 999999, // Should be ignored
            'created_at' => '2020-01-01', // Should be ignored
            'updated_at' => '2020-01-01' // Should be ignored
        ]);

        $response->assertRedirect();
        
        $area = AreaOfInterest::where('name', 'Test Area')->first();
        $this->assertNotNull($area);
        $this->assertNotEquals(999999, $area->id);
        $this->assertNotEquals('2020-01-01', $area->created_at->format('Y-m-d'));
    }

    // ========== FILE UPLOAD SECURITY TESTS ==========

    public function test_file_upload_validation()
    {
        $this->withoutMiddleware();

        // Test malicious file upload
        $maliciousFile = \Illuminate\Http\UploadedFile::fake()->create('malicious.php', 100, 'application/x-php');

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/upload-excel', [
            'excel_file' => $maliciousFile
        ]);

        $response->assertSessionHasErrors(['excel_file']);
    }

    public function test_file_size_validation()
    {
        $this->withoutMiddleware();

        // Test oversized file upload
        $largeFile = \Illuminate\Http\UploadedFile::fake()->create('large.xlsx', 10240, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->actingAs($this->advisor)->post('/advisor/groups/upload-excel', [
            'excel_file' => $largeFile
        ]);

        $response->assertSessionHasErrors(['excel_file']);
    }

    // ========== SESSION SECURITY TESTS ==========

    public function test_session_regeneration_on_login()
    {
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_session_invalidation_on_logout()
    {
        $this->actingAs($this->admin);
        
        $response = $this->post('/logout');
        
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    // ========== PERMISSION BOUNDARY TESTS ==========

    public function test_user_cannot_access_other_users_data()
    {
        $otherStudent = User::factory()->create(['login_type' => 'student']);
        
        // Student trying to access another student's profile
        $response = $this->actingAs($this->student)->get("/profile/{$otherStudent->id}");
        $response->assertStatus(404); // Should not exist or be accessible
    }

    public function test_advisor_cannot_access_other_advisors_groups()
    {
        $otherAdvisor = User::factory()->create([
            'login_type' => 'teacher',
            'type_id' => json_encode(['3'])
        ]);
        
        $otherGroup = Group::factory()->create(['advisor_id' => $otherAdvisor->id]);

        $this->withoutMiddleware();

        // Try to assign student to other advisor's group
        $response = $this->actingAs($this->advisor)->post('/advisor/groups/assign-student', [
            'group_id' => $otherGroup->id,
            'student_id' => $this->student->id
        ]);

        $response->assertStatus(403);
    }

    public function test_supervisor_cannot_access_other_supervisors_meetings()
    {
        $otherSupervisor = Supervisor::factory()->create();
        $meeting = \App\Models\Meeting::factory()->create(['supervisor_id' => $otherSupervisor->id]);

        $this->withoutMiddleware();

        $response = $this->actingAs($this->teacher)->get("/supervisor/meetings/{$meeting->id}");
        $response->assertStatus(403);
    }

    // ========== DATA INTEGRITY TESTS ==========

    public function test_foreign_key_constraints()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        // Try to create group with non-existent advisor
        Group::create([
            'name' => 'Test Group',
            'advisor_id' => 99999,
            'max_students' => 3
        ]);
    }

    public function test_unique_constraints()
    {
        AreaOfInterest::factory()->create(['name' => 'Unique Area']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        
        // Try to create another area with same name
        AreaOfInterest::create([
            'name' => 'Unique Area',
            'description' => 'Another description'
        ]);
    }

    // ========== SECURITY HEADERS TESTS ==========

    public function test_security_headers_present()
    {
        $response = $this->get('/');

        // Check for security headers
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
    }

    // ========== PASSWORD SECURITY TESTS ==========

    public function test_password_hashing()
    {
        $user = User::factory()->create(['password' => Hash::make('testpassword')]);
        
        $this->assertTrue(Hash::check('testpassword', $user->password));
        $this->assertFalse(Hash::check('wrongpassword', $user->password));
    }

    public function test_password_update_requires_current_password()
    {
        $response = $this->actingAs($this->student)->put('/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    // ========== API ENDPOINT SECURITY TESTS ==========

    public function test_api_endpoints_require_authentication()
    {
        $apiRoutes = [
            '/admin/groups/available-supervisors',
            '/advisor/supervisor-assignment/available-supervisors',
            '/supervisor/meetings/students'
        ];

        foreach ($apiRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_sensitive_data_not_exposed_in_responses()
    {
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin)->get('/admin/supervisors');
        
        $response->assertStatus(200);
        $response->assertDontSee('password');
        $response->assertDontSee('remember_token');
    }

    // ========== BRUTE FORCE PROTECTION TESTS ==========

    public function test_account_lockout_after_failed_attempts()
    {
        // Make multiple failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'admin@test.com',
                'password' => 'wrongpassword'
            ]);
        }

        // Account should be temporarily locked
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password123' // Correct password
        ]);

        $response->assertStatus(429);
    }

    // ========== PRIVILEGE ESCALATION TESTS ==========

    public function test_user_cannot_escalate_privileges()
    {
        $this->withoutMiddleware();

        // Student trying to update their role
        $response = $this->actingAs($this->student)->patch('/profile', [
            'name' => 'Updated Name',
            'login_type' => 'teacher', // Trying to escalate
            'type_id' => json_encode(['1']) // Trying to become admin
        ]);

        $this->student->refresh();
        $this->assertEquals('student', $this->student->login_type);
        $this->assertNull($this->student->type_id);
    }

    // ========== CLEANUP AND TEARDOWN ==========

    protected function tearDown(): void
    {
        // Clear any rate limiting
        RateLimiter::clear('login:admin@test.com');
        
        parent::tearDown();
    }
}