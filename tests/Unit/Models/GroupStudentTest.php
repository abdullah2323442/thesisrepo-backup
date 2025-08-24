<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\GroupStudent;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupStudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_student_can_be_created()
    {
        $group = Group::factory()->create();
        
        $groupStudent = GroupStudent::create([
            'group_id' => $group->id,
            'student_id' => '2020123456',
            'student_name' => 'John Doe',
            'student_email' => 'john.doe@example.com'
        ]);

        $this->assertDatabaseHas('group_students', [
            'group_id' => $group->id,
            'student_id' => '2020123456',
            'student_name' => 'John Doe',
            'student_email' => 'john.doe@example.com'
        ]);
    }

    public function test_group_student_has_fillable_attributes()
    {
        $groupStudent = new GroupStudent();
        $fillable = $groupStudent->getFillable();

        $expectedFillable = ['group_id', 'student_id', 'student_name', 'student_email'];

        foreach ($expectedFillable as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_group_student_has_casts()
    {
        $groupStudent = new GroupStudent();
        $casts = $groupStudent->getCasts();

        $this->assertEquals('integer', $casts['group_id']);
    }

    public function test_group_student_belongs_to_group()
    {
        $group = Group::factory()->create();
        $groupStudent = GroupStudent::factory()->create(['group_id' => $group->id]);

        $this->assertInstanceOf(Group::class, $groupStudent->group);
        $this->assertEquals($group->id, $groupStudent->group->id);
    }

    public function test_group_student_can_have_null_email()
    {
        $group = Group::factory()->create();
        
        $groupStudent = GroupStudent::create([
            'group_id' => $group->id,
            'student_id' => '2020123456',
            'student_name' => 'John Doe',
            'student_email' => null
        ]);

        $this->assertNull($groupStudent->student_email);
        $this->assertDatabaseHas('group_students', [
            'student_id' => '2020123456',
            'student_email' => null
        ]);
    }

    public function test_group_student_student_id_is_string()
    {
        $group = Group::factory()->create();
        
        $groupStudent = GroupStudent::create([
            'group_id' => $group->id,
            'student_id' => '2020123456',
            'student_name' => 'John Doe'
        ]);

        $this->assertIsString($groupStudent->student_id);
        $this->assertEquals('2020123456', $groupStudent->student_id);
    }

    public function test_group_student_can_be_deleted()
    {
        $groupStudent = GroupStudent::factory()->create();
        $id = $groupStudent->id;

        $groupStudent->delete();

        $this->assertDatabaseMissing('group_students', ['id' => $id]);
    }

    public function test_group_student_group_relationship_works()
    {
        $group = Group::factory()->create(['name' => 'Test Group']);
        $groupStudent = GroupStudent::factory()->create(['group_id' => $group->id]);

        $this->assertEquals('Test Group', $groupStudent->group->name);
    }

    public function test_multiple_students_can_belong_to_same_group()
    {
        $group = Group::factory()->create();
        
        $student1 = GroupStudent::factory()->create([
            'group_id' => $group->id,
            'student_id' => '2020123001'
        ]);
        
        $student2 = GroupStudent::factory()->create([
            'group_id' => $group->id,
            'student_id' => '2020123002'
        ]);

        $this->assertEquals($group->id, $student1->group_id);
        $this->assertEquals($group->id, $student2->group_id);
        $this->assertCount(2, $group->students);
    }

    public function test_group_student_required_fields()
    {
        $group = Group::factory()->create();
        
        // Test that group_id is required
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        GroupStudent::create([
            'student_id' => '2020123456',
            'student_name' => 'John Doe'
        ]);
    }
}