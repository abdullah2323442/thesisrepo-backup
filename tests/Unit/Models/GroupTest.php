<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\User;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_can_be_created()
    {
        $advisor = User::factory()->create();
        
        $group = Group::create([
            'name' => 'Group 1',
            'batch_number' => 2020,
            'advisor_id' => $advisor->id,
            'max_students' => 3
        ]);

        $this->assertDatabaseHas('groups', [
            'name' => 'Group 1',
            'batch_number' => 2020,
            'advisor_id' => $advisor->id,
            'max_students' => 3
        ]);
    }

    public function test_group_belongs_to_advisor()
    {
        $advisor = User::factory()->create();
        $group = Group::factory()->create(['advisor_id' => $advisor->id]);

        $this->assertInstanceOf(User::class, $group->advisor);
        $this->assertEquals($advisor->id, $group->advisor->id);
    }

    public function test_group_has_many_students()
    {
        $group = Group::factory()->create();
        
        GroupStudent::factory()->count(2)->create(['group_id' => $group->id]);

        $this->assertCount(2, $group->students);
        $this->assertInstanceOf(GroupStudent::class, $group->students->first());
    }

    public function test_group_belongs_to_area_of_interest()
    {
        $area = AreaOfInterest::factory()->create();
        $group = Group::factory()->create(['area_of_interest_id' => $area->id]);

        $this->assertInstanceOf(AreaOfInterest::class, $group->areaOfInterest);
        $this->assertEquals($area->id, $group->areaOfInterest->id);
    }

    public function test_group_belongs_to_supervisor()
    {
        $supervisor = Supervisor::factory()->create();
        $group = Group::factory()->create(['supervisor_id' => $supervisor->id]);

        $this->assertInstanceOf(Supervisor::class, $group->supervisor);
        $this->assertEquals($supervisor->id, $group->supervisor->id);
    }

    public function test_group_student_count_attribute()
    {
        $group = Group::factory()->create();
        GroupStudent::factory()->count(2)->create(['group_id' => $group->id]);

        $this->assertEquals(2, $group->student_count);
    }

    public function test_group_is_full_method()
    {
        $group = Group::factory()->create(['max_students' => 2]);
        
        $this->assertFalse($group->isFull());
        
        GroupStudent::factory()->count(2)->create(['group_id' => $group->id]);
        $group->refresh();
        
        $this->assertTrue($group->isFull());
    }

    public function test_group_available_slots_attribute()
    {
        $group = Group::factory()->create(['max_students' => 3]);
        GroupStudent::factory()->count(1)->create(['group_id' => $group->id]);

        $this->assertEquals(2, $group->available_slots);
    }

    public function test_group_number_attribute()
    {
        $group1 = Group::factory()->create(['name' => 'Group 1']);
        $group2 = Group::factory()->create(['name' => 'Group 10']);
        $group3 = Group::factory()->create(['name' => 'Team Alpha']);

        $this->assertEquals(1, $group1->group_number);
        $this->assertEquals(10, $group2->group_number);
        $this->assertEquals(0, $group3->group_number);
    }

    public function test_group_has_supervisor_method()
    {
        $groupWithSupervisor = Group::factory()->create(['supervisor_id' => 1]);
        $groupWithoutSupervisor = Group::factory()->create(['supervisor_id' => null]);

        $this->assertTrue($groupWithSupervisor->hasSupervisor());
        $this->assertFalse($groupWithoutSupervisor->hasSupervisor());
    }

    public function test_group_is_eligible_for_assignment_method()
    {
        $eligibleGroup = Group::factory()->create([
            'supervisor_id' => null,
            'area_of_interest_id' => 1
        ]);
        
        $ineligibleGroup1 = Group::factory()->create([
            'supervisor_id' => 1,
            'area_of_interest_id' => 1
        ]);
        
        $ineligibleGroup2 = Group::factory()->create([
            'supervisor_id' => null,
            'area_of_interest_id' => null
        ]);

        $this->assertTrue($eligibleGroup->isEligibleForAssignment());
        $this->assertFalse($ineligibleGroup1->isEligibleForAssignment());
        $this->assertFalse($ineligibleGroup2->isEligibleForAssignment());
    }

    public function test_group_is_manual_assignment_method()
    {
        $manualGroup = Group::factory()->create(['is_manual_assignment' => true]);
        $autoGroup = Group::factory()->create(['is_manual_assignment' => false]);

        $this->assertTrue($manualGroup->isManualAssignment());
        $this->assertFalse($autoGroup->isManualAssignment());
    }

    public function test_group_scopes()
    {
        Group::factory()->create(['supervisor_id' => null]);
        Group::factory()->create(['supervisor_id' => 1]);
        Group::factory()->create(['is_manual_assignment' => true]);
        Group::factory()->create([
            'supervisor_id' => null,
            'is_manual_assignment' => false,
            'area_of_interest_id' => 1
        ]);

        $this->assertEquals(1, Group::unassigned()->count());
        $this->assertEquals(1, Group::assigned()->count());
        $this->assertEquals(1, Group::manuallyAssigned()->count());
        $this->assertEquals(1, Group::lotteryEligible()->count());
    }
}