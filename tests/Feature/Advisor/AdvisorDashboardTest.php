<?php

namespace Tests\Feature\Advisor;

use Tests\TestCase;
use App\Models\User;
use App\Models\Supervisor;
use App\Models\Batch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class AdvisorDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(); // Skip middleware for testing
    }

    public function test_advisor_dashboard_displays_correctly()
    {
        $advisor = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 123
        ]);

        // Create supervisor record
        Supervisor::factory()->create([
            'api_id' => 123,
            'email' => $advisor->email
        ]);

        // Create active batch
        Batch::factory()->create(['is_active' => true]);

        Http::fake();

        $response = $this->actingAs($advisor)->get('/advisor/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Advisor Dashboard');
        $response->assertSee('Welcome');
    }

    public function test_advisor_dashboard_shows_statistics()
    {
        $advisor = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 123
        ]);

        Supervisor::factory()->create([
            'api_id' => 123,
            'email' => $advisor->email
        ]);

        Batch::factory()->create(['is_active' => true]);

        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'roll' => '2020123001',
                        'advisor_id' => 123
                    ],
                    [
                        'id' => 2,
                        'name' => 'Jane Smith',
                        'roll' => '2020123002',
                        'advisor_id' => 123
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    public function test_advisor_dashboard_requires_supervisor_record()
    {
        $advisor = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 123
        ]);

        // No supervisor record created

        $response = $this->actingAs($advisor)->get('/advisor/dashboard');

        $response->assertRedirect('/teacher/dashboard');
        $response->assertSessionHas('error');
    }

    public function test_advisor_dashboard_handles_api_failure()
    {
        $advisor = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 123
        ]);

        Supervisor::factory()->create([
            'api_id' => 123,
            'email' => $advisor->email
        ]);

        Batch::factory()->create(['is_active' => true]);

        Http::fake([
            '*' => Http::response([], 500) // API failure
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        $this->assertFalse($stats['success']);
    }

    public function test_advisor_dashboard_shows_no_active_batches_message()
    {
        $advisor = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 123
        ]);

        Supervisor::factory()->create([
            'api_id' => 123,
            'email' => $advisor->email
        ]);

        // No active batches

        $response = $this->actingAs($advisor)->get('/advisor/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        $this->assertFalse($stats['success']);
        $this->assertStringContains('No active batches', $stats['error']);
    }

    public function test_advisor_dashboard_layout_includes_navigation()
    {
        $advisor = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 123
        ]);

        Supervisor::factory()->create([
            'api_id' => 123,
            'email' => $advisor->email
        ]);

        Batch::factory()->create(['is_active' => true]);
        Http::fake();

        $response = $this->actingAs($advisor)->get('/advisor/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Advisor Panel');
        $response->assertSee('My Students');
        $response->assertSee('Group Management');
        $response->assertSee('Supervisor Assignment');
    }

    public function test_advisor_dashboard_has_logout_functionality()
    {
        $advisor = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 123
        ]);

        Supervisor::factory()->create([
            'api_id' => 123,
            'email' => $advisor->email
        ]);

        Batch::factory()->create(['is_active' => true]);
        Http::fake();

        $response = $this->actingAs($advisor)->get('/advisor/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Logout');
    }

    public function test_advisor_dashboard_shows_student_statistics()
    {
        $advisor = User::factory()->create([
            'login_type' => 'teacher',
            'api_id' => 123
        ]);

        Supervisor::factory()->create([
            'api_id' => 123,
            'email' => $advisor->email
        ]);

        Batch::factory()->create(['is_active' => true]);

        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'id' => 1,
                        'name' => 'John Doe',
                        'roll' => '2020123001',
                        'advisor_id' => 123,
                        'gender' => 'Male'
                    ],
                    [
                        'id' => 2,
                        'name' => 'Jane Smith',
                        'roll' => '2020123002',
                        'advisor_id' => 123,
                        'gender' => 'Female'
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($advisor)->get('/advisor/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        $this->assertTrue($stats['success']);
        $this->assertEquals(2, $stats['total_students']);
        $this->assertEquals(1, $stats['male_students']);
        $this->assertEquals(1, $stats['female_students']);
    }
}