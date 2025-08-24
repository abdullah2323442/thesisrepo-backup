<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExternalApiRateLimitingTest extends TestCase
{
    use RefreshDatabase;

    
    /**
     * Ensure the login endpoint is rate limited and prevents brute force to external API.
     */
    public function test_login_endpoint_is_rate_limited_and_blocks_bruteforce_calls_to_external_api(): void
    {
        // Fake all outgoing HTTP calls to external APIs
        Http::fake();

        // Disable CSRF middleware for this test only
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Prepare payload that would trigger external API call in controller
        $payload = [
            'user' => 'teacher1',
            'pass' => 'wrong-password',
        ];

        // Perform 5 attempts (limit is set via config('external_api.rate_limit.login'))
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', $payload);
            // First 5 attempts should not be throttled (likely 302 redirect back with errors or 422)
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        // Ensure only 5 external calls were attempted so far
        Http::assertSentCount(5);

        // 6th attempt should be throttled by middleware -> 429 Too Many Requests
        $blocked = $this->post('/login', $payload);
        $blocked->assertStatus(429);

        // Ensure no additional external calls happen after throttling (still 5 total)
        Http::assertSentCount(5);
    }

    /**
     * Ensure advisor students listing route is limited based on config.
     */
    public function test_advisor_students_listing_is_rate_limited(): void
    {
        // Fake all outgoing HTTP calls
        Http::fake();

        // Disable CSRF
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Create a user with advisor role and log them in
        $user = \App\Models\User::factory()->create();
        // Simulate they have advisor access via a simple gate (or you can adjust as per your auth logic)
        // Here we assume middleware(['auth','advisor']) passes for this test context.
        $this->be($user);

        // Create a supervisor record for the user so they can access advisor routes
        $supervisor = \App\Models\Supervisor::create([
            'api_id' => 123,
            'fullname' => $user->name,
            'gender' => 'Male',
            'email' => $user->email,
            'department' => 'Computer Science & Engineering',
            'designation' => 'Assistant Professor',
            'is_active' => true,
            'thesis_limit' => 5,
        ]);

        // Create some active batches so that HTTP calls will be made
        \App\Models\Batch::create([
            'batch_number' => 1,
            'program_id' => 1,
            'batch_name' => 'Batch 1',
            'is_active' => true,
        ]);

        $limit = (int) config('external_api.rate_limit.advisor_students.max_attempts', 60);
        $attempts = max(1, $limit);

        for ($i = 0; $i < $attempts; $i++) {
            $res = $this->get('/advisor/students');
            $this->assertNotEquals(429, $res->getStatusCode());
        }

        Http::assertSentCount($attempts);

        // One more attempt should be throttled
        $blocked = $this->get('/advisor/students');
        $blocked->assertStatus(429);

        // Ensure count did not increase
        Http::assertSentCount($attempts);
    }
}
