<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ComprehensiveApiRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test student dashboard is rate limited
     */
    public function test_student_dashboard_is_rate_limited(): void
    {
        Http::fake();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $user = \App\Models\User::factory()->create();
        $this->be($user);

        $limit = (int) config('external_api.rate_limit.student_dashboard.max_attempts', 60);

        // Make requests up to the limit
        for ($i = 0; $i < $limit; $i++) {
            $response = $this->get('/student/dashboard');
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        // Next request should be throttled
        $blocked = $this->get('/student/dashboard');
        $blocked->assertStatus(429);
    }

    /**
     * Test advisor dashboard is rate limited
     */
    public function test_advisor_dashboard_is_rate_limited(): void
    {
        Http::fake();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $user = \App\Models\User::factory()->create();
        $this->be($user);

        // Create supervisor record
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

        $limit = (int) config('external_api.rate_limit.advisor_dashboard.max_attempts', 60);

        // Make requests up to the limit
        for ($i = 0; $i < $limit; $i++) {
            $response = $this->get('/advisor/dashboard');
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        // Next request should be throttled
        $blocked = $this->get('/advisor/dashboard');
        $blocked->assertStatus(429);
    }

    /**
     * Test advisor students show is rate limited
     */
    public function test_advisor_students_show_is_rate_limited(): void
    {
        Http::fake();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $user = \App\Models\User::factory()->create();
        $this->be($user);

        // Create supervisor record
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

        // Create active batch
        \App\Models\Batch::create([
            'batch_number' => 1,
            'program_id' => 1,
            'batch_name' => 'Batch 1',
            'is_active' => true,
        ]);

        $limit = (int) config('external_api.rate_limit.advisor_students_show.max_attempts', 120);

        // Make requests up to the limit
        for ($i = 0; $i < $limit; $i++) {
            $response = $this->get('/advisor/students/123');
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        // Next request should be throttled
        $blocked = $this->get('/advisor/students/123');
        $blocked->assertStatus(429);
    }

    /**
     * Test advisor students refresh is rate limited
     */
    public function test_advisor_students_refresh_is_rate_limited(): void
    {
        Http::fake();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $user = \App\Models\User::factory()->create();
        $this->be($user);

        // Create supervisor record
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

        $limit = (int) config('external_api.rate_limit.advisor_students_refresh.max_attempts', 30);

        // Make requests up to the limit
        for ($i = 0; $i < $limit; $i++) {
            $response = $this->post('/advisor/students/refresh');
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        // Next request should be throttled
        $blocked = $this->post('/advisor/students/refresh');
        $blocked->assertStatus(429);
    }

    /**
     * Test advisor groups is rate limited
     */
    public function test_advisor_groups_is_rate_limited(): void
    {
        Http::fake();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $user = \App\Models\User::factory()->create();
        $user->api_id = 123; // Set API ID for advisor
        $user->save();
        $this->be($user);

        // Create supervisor record
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

        $limit = (int) config('external_api.rate_limit.advisor_groups.max_attempts', 120);

        // Make requests up to the limit
        for ($i = 0; $i < $limit; $i++) {
            $response = $this->get('/advisor/groups');
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        // Next request should be throttled
        $blocked = $this->get('/advisor/groups');
        $blocked->assertStatus(429);
    }
}