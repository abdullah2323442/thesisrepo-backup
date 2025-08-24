<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Batch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class BatchControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(); // Skip middleware for testing
    }

    public function test_index_displays_batches()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        Batch::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/batches');

        $response->assertStatus(200);
        $response->assertSee('Batch Management');
        $response->assertViewHas('batches');
        $this->assertCount(3, $response->viewData('batches'));
    }

    public function test_index_shows_batch_statistics()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        Batch::factory()->count(4)->create(['is_active' => true]);
        Batch::factory()->count(2)->create(['is_active' => false]);

        $response = $this->actingAs($admin)->get('/admin/batches');

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        
        $stats = $response->viewData('stats');
        $this->assertEquals(6, $stats['total']);
        $this->assertEquals(4, $stats['active']);
        $this->assertEquals(2, $stats['inactive']);
    }

    public function test_sync_from_api_creates_batches()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'batch_name' => 'Batch 2020',
                        'batch' => 2020,
                        'programID' => 1
                    ],
                    [
                        'batch_name' => 'Batch 2021',
                        'batch' => 2021,
                        'programID' => 1
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($admin)->post('/admin/batches/sync');

        $response->assertRedirect('/admin/batches');
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('batches', ['batch_number' => 2020]);
        $this->assertDatabaseHas('batches', ['batch_number' => 2021]);
    }

    public function test_toggle_status_changes_batch_status()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $batch = Batch::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post("/admin/batches/{$batch->id}/toggle");

        $response->assertRedirect('/admin/batches');
        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'is_active' => false
        ]);
    }

    public function test_bulk_action_activates_multiple_batches()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $batch1 = Batch::factory()->create(['is_active' => false]);
        $batch2 = Batch::factory()->create(['is_active' => false]);

        $data = [
            'action' => 'activate',
            'batch_ids' => [$batch1->id, $batch2->id]
        ];

        $response = $this->actingAs($admin)->post('/admin/batches/bulk-action', $data);

        $response->assertRedirect('/admin/batches');
        $this->assertDatabaseHas('batches', ['id' => $batch1->id, 'is_active' => true]);
        $this->assertDatabaseHas('batches', ['id' => $batch2->id, 'is_active' => true]);
    }

    public function test_bulk_action_deactivates_multiple_batches()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $batch1 = Batch::factory()->create(['is_active' => true]);
        $batch2 = Batch::factory()->create(['is_active' => true]);

        $data = [
            'action' => 'deactivate',
            'batch_ids' => [$batch1->id, $batch2->id]
        ];

        $response = $this->actingAs($admin)->post('/admin/batches/bulk-action', $data);

        $response->assertRedirect('/admin/batches');
        $this->assertDatabaseHas('batches', ['id' => $batch1->id, 'is_active' => false]);
        $this->assertDatabaseHas('batches', ['id' => $batch2->id, 'is_active' => false]);
    }

    public function test_edit_displays_batch_form()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $batch = Batch::factory()->create([
            'batch_name' => 'CS Batch 2020',
            'description' => 'Computer Science Batch'
        ]);

        $response = $this->actingAs($admin)->get("/admin/batches/{$batch->id}/edit");

        $response->assertStatus(200);
        $response->assertSee('Edit Batch');
        $response->assertSee('CS Batch 2020');
        $response->assertSee('Computer Science Batch');
    }

    public function test_update_modifies_batch()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $batch = Batch::factory()->create([
            'batch_name' => 'Old Name',
            'description' => 'Old Description'
        ]);

        $data = [
            'batch_name' => 'New Name',
            'description' => 'New Description',
            'is_active' => false
        ];

        $response = $this->actingAs($admin)->put("/admin/batches/{$batch->id}", $data);

        $response->assertRedirect('/admin/batches');
        $this->assertDatabaseHas('batches', [
            'id' => $batch->id,
            'batch_name' => 'New Name',
            'description' => 'New Description',
            'is_active' => false
        ]);
    }

    public function test_destroy_deletes_batch()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $batch = Batch::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/batches/{$batch->id}");

        $response->assertRedirect('/admin/batches');
        $this->assertDatabaseMissing('batches', ['id' => $batch->id]);
    }

    public function test_compare_with_api_shows_differences()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        // Create local batch
        Batch::factory()->create(['batch_number' => 2020, 'batch_name' => 'Local Batch 2020']);

        Http::fake([
            '*' => Http::response([
                'Data' => [
                    [
                        'batch_name' => 'API Batch 2020',
                        'batch' => 2020,
                        'programID' => 1
                    ],
                    [
                        'batch_name' => 'API Batch 2021',
                        'batch' => 2021,
                        'programID' => 1
                    ]
                ]
            ])
        ]);

        $response = $this->actingAs($admin)->get('/admin/batches/compare');

        $response->assertStatus(200);
        $response->assertSee('Batch Comparison');
        $response->assertViewHas('comparison');
    }

    public function test_activate_all_activates_all_batches()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        Batch::factory()->count(3)->create(['is_active' => false]);

        $response = $this->actingAs($admin)->post('/admin/batches/activate-all');

        $response->assertRedirect('/admin/batches');
        $this->assertEquals(3, Batch::where('is_active', true)->count());
    }

    public function test_deactivate_all_deactivates_all_batches()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        Batch::factory()->count(3)->create(['is_active' => true]);

        $response = $this->actingAs($admin)->post('/admin/batches/deactivate-all');

        $response->assertRedirect('/admin/batches');
        $this->assertEquals(3, Batch::where('is_active', false)->count());
    }

    public function test_index_filters_by_status()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        Batch::factory()->create(['is_active' => true, 'batch_name' => 'Active Batch']);
        Batch::factory()->create(['is_active' => false, 'batch_name' => 'Inactive Batch']);

        $response = $this->actingAs($admin)->get('/admin/batches?status=active');

        $response->assertStatus(200);
        $response->assertSee('Active Batch');
        $response->assertDontSee('Inactive Batch');
    }

    public function test_sync_handles_api_failure()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        
        Http::fake([
            '*' => Http::response([], 500) // Simulate API failure
        ]);

        $response = $this->actingAs($admin)->post('/admin/batches/sync');

        $response->assertRedirect('/admin/batches');
        $response->assertSessionHas('error');
    }

    public function test_bulk_action_validates_required_fields()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);

        $response = $this->actingAs($admin)->post('/admin/batches/bulk-action', []);

        $response->assertSessionHasErrors(['action', 'batch_ids']);
    }

    public function test_update_validates_required_fields()
    {
        $admin = User::factory()->create(['login_type' => 'teacher']);
        $batch = Batch::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/batches/{$batch->id}", []);

        $response->assertSessionHasErrors(['batch_name']);
    }
}