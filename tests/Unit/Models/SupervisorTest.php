<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Supervisor;
use App\Models\AreaOfInterest;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupervisorTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisor_can_be_created()
    {
        $supervisor = Supervisor::create([
            'api_id' => 123,
            'fullname' => 'Dr. John Smith',
            'gender' => 'Male',
            'email' => 'john.smith@example.com',
            'designation' => 'Associate Professor',
            'department' => 'Computer Science & Engineering',
            'thesis_limit' => 5,
            'is_active' => true
        ]);

        $this->assertDatabaseHas('supervisors', [
            'api_id' => 123,
            'fullname' => 'Dr. John Smith',
            'email' => 'john.smith@example.com'
        ]);
    }

    public function test_supervisor_has_fillable_attributes()
    {
        $supervisor = new Supervisor();
        $fillable = $supervisor->getFillable();

        $expectedFillable = [
            'api_id', 'fullname', 'gender', 'email', 'designation',
            'department', 'thesis_limit', 'is_active', 'last_synced_at'
        ];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_supervisor_belongs_to_many_areas_of_interest()
    {
        $supervisor = Supervisor::factory()->create();
        $area1 = AreaOfInterest::factory()->create();
        $area2 = AreaOfInterest::factory()->create();

        $supervisor->areasOfInterest()->attach([$area1->id, $area2->id]);

        $this->assertCount(2, $supervisor->areasOfInterest);
        $this->assertTrue($supervisor->areasOfInterest->contains($area1));
        $this->assertTrue($supervisor->areasOfInterest->contains($area2));
    }

    public function test_supervisor_can_take_thesis_method()
    {
        $activeSupervisor = Supervisor::factory()->create([
            'is_active' => true,
            'thesis_limit' => 3
        ]);
        
        $inactiveSupervisor = Supervisor::factory()->create([
            'is_active' => false,
            'thesis_limit' => 3
        ]);

        // Create groups to fill up the active supervisor's slots
        Group::factory()->count(2)->create(['supervisor_id' => $activeSupervisor->id]);

        $this->assertTrue($activeSupervisor->canTakeThesis());
        $this->assertFalse($inactiveSupervisor->canTakeThesis());
    }

    public function test_supervisor_rank_priority_attribute()
    {
        $professor = Supervisor::factory()->create(['designation' => 'Professor']);
        $associateProfessor = Supervisor::factory()->create(['designation' => 'Associate Professor']);
        $assistantProfessor = Supervisor::factory()->create(['designation' => 'Assistant Professor']);
        $lecturer = Supervisor::factory()->create(['designation' => 'Lecturer']);
        $other = Supervisor::factory()->create(['designation' => 'Research Assistant']);

        $this->assertEquals(1, $professor->rank_priority);
        $this->assertEquals(2, $associateProfessor->rank_priority);
        $this->assertEquals(3, $assistantProfessor->rank_priority);
        $this->assertEquals(4, $lecturer->rank_priority);
        $this->assertEquals(5, $other->rank_priority);
    }

    public function test_supervisor_rank_title_attribute()
    {
        $supervisor = Supervisor::factory()->create(['designation' => 'Associate Professor']);
        
        $this->assertEquals('Associate Professor', $supervisor->rank_title);
    }

    public function test_supervisor_has_many_groups()
    {
        $supervisor = Supervisor::factory()->create();
        Group::factory()->count(3)->create(['supervisor_id' => $supervisor->id]);

        $this->assertCount(3, $supervisor->groups);
        $this->assertInstanceOf(Group::class, $supervisor->groups->first());
    }

    public function test_supervisor_assigned_theses_count_attribute()
    {
        $supervisor = Supervisor::factory()->create();
        Group::factory()->count(2)->create(['supervisor_id' => $supervisor->id]);

        $this->assertEquals(2, $supervisor->assigned_theses_count);
    }

    public function test_supervisor_available_slots_attribute()
    {
        $supervisor = Supervisor::factory()->create(['thesis_limit' => 5]);
        Group::factory()->count(2)->create(['supervisor_id' => $supervisor->id]);

        $this->assertEquals(3, $supervisor->available_slots);
    }

    public function test_supervisor_with_available_slots_scope()
    {
        $supervisor1 = Supervisor::factory()->create([
            'is_active' => true,
            'thesis_limit' => 3
        ]);
        
        $supervisor2 = Supervisor::factory()->create([
            'is_active' => true,
            'thesis_limit' => 2
        ]);

        // Fill up supervisor2's slots
        Group::factory()->count(2)->create(['supervisor_id' => $supervisor2->id]);

        $availableSupervisors = Supervisor::withAvailableSlots()->get();

        $this->assertCount(1, $availableSupervisors);
        $this->assertEquals($supervisor1->id, $availableSupervisors->first()->id);
    }

    public function test_supervisor_has_area_of_interest_method()
    {
        $supervisor = Supervisor::factory()->create();
        $area = AreaOfInterest::factory()->create();
        
        $supervisor->areasOfInterest()->attach($area->id);

        $this->assertTrue($supervisor->hasAreaOfInterest($area->id));
        $this->assertFalse($supervisor->hasAreaOfInterest(999));
    }

    public function test_supervisor_by_rank_priority_scope()
    {
        $lecturer = Supervisor::factory()->create(['designation' => 'Lecturer', 'fullname' => 'A Lecturer']);
        $professor = Supervisor::factory()->create(['designation' => 'Professor', 'fullname' => 'B Professor']);
        $assistant = Supervisor::factory()->create(['designation' => 'Assistant Professor', 'fullname' => 'C Assistant']);

        $supervisors = Supervisor::byRankPriority()->get();

        $this->assertEquals($professor->id, $supervisors[0]->id);
        $this->assertEquals($assistant->id, $supervisors[1]->id);
        $this->assertEquals($lecturer->id, $supervisors[2]->id);
    }
}