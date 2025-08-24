<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Batch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_batch_can_be_created()
    {
        $batch = Batch::create([
            'batch_number' => 2020,
            'program_id' => 1,
            'batch_name' => 'Batch 2020',
            'is_active' => true,
            'description' => 'Computer Science Batch 2020'
        ]);

        $this->assertDatabaseHas('batches', [
            'batch_number' => 2020,
            'program_id' => 1,
            'batch_name' => 'Batch 2020',
            'is_active' => true
        ]);
    }

    public function test_batch_has_fillable_attributes()
    {
        $batch = new Batch();
        $fillable = $batch->getFillable();

        $expectedFillable = [
            'batch_number', 'program_id', 'batch_name', 
            'is_active', 'description', 'last_synced_at'
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_batch_has_casts()
    {
        $batch = new Batch();
        $casts = $batch->getCasts();

        $this->assertEquals('boolean', $casts['is_active']);
        $this->assertEquals('datetime', $casts['last_synced_at']);
    }

    public function test_batch_display_name_attribute()
    {
        $batchWithName = Batch::factory()->create([
            'batch_number' => 2020,
            'batch_name' => 'CS Batch 2020'
        ]);

        $batchWithoutName = Batch::factory()->create([
            'batch_number' => 2021,
            'batch_name' => null
        ]);

        $this->assertEquals('CS Batch 2020', $batchWithName->display_name);
        $this->assertEquals('Batch 2021', $batchWithoutName->display_name);
    }

    public function test_batch_active_scope()
    {
        Batch::factory()->create(['is_active' => true]);
        Batch::factory()->create(['is_active' => true]);
        Batch::factory()->create(['is_active' => false]);

        $activeBatches = Batch::active()->get();

        $this->assertCount(2, $activeBatches);
        $this->assertTrue($activeBatches->every(fn($batch) => $batch->is_active));
    }

    public function test_batch_inactive_scope()
    {
        Batch::factory()->create(['is_active' => true]);
        Batch::factory()->create(['is_active' => false]);
        Batch::factory()->create(['is_active' => false]);

        $inactiveBatches = Batch::inactive()->get();

        $this->assertCount(2, $inactiveBatches);
        $this->assertTrue($inactiveBatches->every(fn($batch) => !$batch->is_active));
    }

    public function test_batch_get_stats_method()
    {
        Batch::factory()->count(3)->create(['is_active' => true]);
        Batch::factory()->count(2)->create(['is_active' => false]);

        $stats = Batch::getStats();

        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(3, $stats['active']);
        $this->assertEquals(2, $stats['inactive']);
        $this->assertArrayHasKey('last_sync', $stats);
    }

    public function test_batch_can_be_activated()
    {
        $batch = Batch::factory()->create(['is_active' => false]);
        
        $batch->update(['is_active' => true]);
        
        $this->assertTrue($batch->fresh()->is_active);
    }

    public function test_batch_can_be_deactivated()
    {
        $batch = Batch::factory()->create(['is_active' => true]);
        
        $batch->update(['is_active' => false]);
        
        $this->assertFalse($batch->fresh()->is_active);
    }

    public function test_batch_last_synced_at_can_be_updated()
    {
        $batch = Batch::factory()->create(['last_synced_at' => null]);
        
        $now = now();
        $batch->update(['last_synced_at' => $now]);
        
        $this->assertEquals($now->format('Y-m-d H:i:s'), $batch->fresh()->last_synced_at->format('Y-m-d H:i:s'));
    }

    public function test_batch_program_id_is_stored()
    {
        $batch = Batch::factory()->create(['program_id' => 5]);
        
        $this->assertEquals(5, $batch->program_id);
    }

    public function test_batch_description_is_optional()
    {
        $batchWithDescription = Batch::factory()->create(['description' => 'Test description']);
        $batchWithoutDescription = Batch::factory()->create(['description' => null]);
        
        $this->assertEquals('Test description', $batchWithDescription->description);
        $this->assertNull($batchWithoutDescription->description);
    }
}