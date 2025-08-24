<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AreaOfInterestTest extends TestCase
{
    use RefreshDatabase;

    public function test_area_of_interest_can_be_created()
    {
        $area = AreaOfInterest::create([
            'name' => 'Machine Learning',
            'description' => 'Study of algorithms that improve automatically through experience',
            'is_active' => true
        ]);

        $this->assertDatabaseHas('area_of_interests', [
            'name' => 'Machine Learning',
            'description' => 'Study of algorithms that improve automatically through experience',
            'is_active' => true
        ]);
    }

    public function test_area_of_interest_has_fillable_attributes()
    {
        $area = new AreaOfInterest();
        $fillable = $area->getFillable();

        $expectedFillable = ['name', 'description', 'is_active'];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_area_of_interest_has_casts()
    {
        $area = new AreaOfInterest();
        $casts = $area->getCasts();

        $this->assertEquals('boolean', $casts['is_active']);
    }

    public function test_area_of_interest_belongs_to_many_supervisors()
    {
        $area = AreaOfInterest::factory()->create();
        $supervisor1 = Supervisor::factory()->create();
        $supervisor2 = Supervisor::factory()->create();

        $area->supervisors()->attach([$supervisor1->id, $supervisor2->id]);

        $this->assertCount(2, $area->supervisors);
        $this->assertTrue($area->supervisors->contains($supervisor1));
        $this->assertTrue($area->supervisors->contains($supervisor2));
    }

    public function test_area_of_interest_has_many_groups()
    {
        $area = AreaOfInterest::factory()->create();
        Group::factory()->count(3)->create(['area_of_interest_id' => $area->id]);

        $this->assertCount(3, $area->groups);
        $this->assertInstanceOf(Group::class, $area->groups->first());
    }

    public function test_area_of_interest_active_scope()
    {
        AreaOfInterest::factory()->create(['is_active' => true]);
        AreaOfInterest::factory()->create(['is_active' => true]);
        AreaOfInterest::factory()->create(['is_active' => false]);

        $activeAreas = AreaOfInterest::active()->get();

        $this->assertCount(2, $activeAreas);
        $this->assertTrue($activeAreas->every(fn($area) => $area->is_active));
    }

    public function test_area_of_interest_inactive_scope()
    {
        AreaOfInterest::factory()->create(['is_active' => true]);
        AreaOfInterest::factory()->create(['is_active' => false]);
        AreaOfInterest::factory()->create(['is_active' => false]);

        $inactiveAreas = AreaOfInterest::inactive()->get();

        $this->assertCount(2, $inactiveAreas);
        $this->assertTrue($inactiveAreas->every(fn($area) => !$area->is_active));
    }

    public function test_area_of_interest_get_stats_method()
    {
        AreaOfInterest::factory()->count(3)->create(['is_active' => true]);
        AreaOfInterest::factory()->count(2)->create(['is_active' => false]);

        $stats = AreaOfInterest::getStats();

        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(3, $stats['active']);
        $this->assertEquals(2, $stats['inactive']);
    }

    public function test_area_of_interest_supervisor_count_attribute()
    {
        $area = AreaOfInterest::factory()->create();
        $supervisor1 = Supervisor::factory()->create();
        $supervisor2 = Supervisor::factory()->create();

        $area->supervisors()->attach([$supervisor1->id, $supervisor2->id]);

        $this->assertEquals(2, $area->supervisor_count);
    }

    public function test_area_of_interest_group_count_attribute()
    {
        $area = AreaOfInterest::factory()->create();
        Group::factory()->count(4)->create(['area_of_interest_id' => $area->id]);

        $this->assertEquals(4, $area->group_count);
    }

    public function test_area_of_interest_can_be_assigned_method()
    {
        $activeArea = AreaOfInterest::factory()->create(['is_active' => true]);
        $inactiveArea = AreaOfInterest::factory()->create(['is_active' => false]);

        $this->assertTrue($activeArea->canBeAssigned());
        $this->assertFalse($inactiveArea->canBeAssigned());
    }

    public function test_area_of_interest_has_supervisors_method()
    {
        $areaWithSupervisors = AreaOfInterest::factory()->create();
        $areaWithoutSupervisors = AreaOfInterest::factory()->create();
        
        $supervisor = Supervisor::factory()->create();
        $areaWithSupervisors->supervisors()->attach($supervisor->id);

        $this->assertTrue($areaWithSupervisors->hasSupervisors());
        $this->assertFalse($areaWithoutSupervisors->hasSupervisors());
    }

    public function test_area_of_interest_available_supervisors_method()
    {
        $area = AreaOfInterest::factory()->create();
        
        $activeSupervisor = Supervisor::factory()->create([
            'is_active' => true,
            'thesis_limit' => 3
        ]);
        
        $inactiveSupervisor = Supervisor::factory()->create([
            'is_active' => false,
            'thesis_limit' => 3
        ]);

        $area->supervisors()->attach([$activeSupervisor->id, $inactiveSupervisor->id]);

        $availableSupervisors = $area->availableSupervisors();

        $this->assertCount(1, $availableSupervisors);
        $this->assertEquals($activeSupervisor->id, $availableSupervisors->first()->id);
    }
}