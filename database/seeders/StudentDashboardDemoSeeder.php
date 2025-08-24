<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\AreaOfInterest;
use App\Models\Supervisor;
use App\Models\Batch;

class StudentDashboardDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a batch
        $batch = Batch::create([
            'batch_number' => 2020,
            'program_id' => 1,
            'batch_name' => 'Batch 2020',
            'is_active' => true,
        ]);

        // Create an advisor
        $advisor = User::create([
            'name' => 'Dr. John Advisor',
            'email' => 'advisor@example.com',
            'password' => bcrypt('password'),
            'login_type' => 'teacher',
            'api_id' => 100
        ]);

        // Create areas of interest
        $aiArea = AreaOfInterest::create([
            'name' => 'Artificial Intelligence',
            'description' => 'Study of intelligent agents and machine learning algorithms',
            'is_active' => true
        ]);

        $webArea = AreaOfInterest::create([
            'name' => 'Web Development',
            'description' => 'Development of web applications and services',
            'is_active' => true
        ]);

        // Create supervisors
        $supervisor1 = Supervisor::create([
            'api_id' => 201,
            'fullname' => 'Dr. Alice Smith',
            'gender' => 'Female',
            'email' => 'alice.smith@example.com',
            'designation' => 'Associate Professor',
            'department' => 'Computer Science & Engineering',
            'is_active' => true,
            'thesis_limit' => 5,
        ]);

        $supervisor2 = Supervisor::create([
            'api_id' => 202,
            'fullname' => 'Dr. Bob Johnson',
            'gender' => 'Male',
            'email' => 'bob.johnson@example.com',
            'designation' => 'Assistant Professor',
            'department' => 'Computer Science & Engineering',
            'is_active' => true,
            'thesis_limit' => 3,
        ]);

        // Create student users
        $student1 = User::create([
            'name' => 'John Student',
            'email' => 'john.student@example.com',
            'password' => bcrypt('password'),
            'login_type' => 'student',
            'roll' => '2020123001',
            'batch' => 2020,
            'department_name' => 'Computer Science & Engineering',
            'program_name' => 'Bachelor of Science in Computer Science'
        ]);

        $student2 = User::create([
            'name' => 'Jane Student',
            'email' => 'jane.student@example.com',
            'password' => bcrypt('password'),
            'login_type' => 'student',
            'roll' => '2020123002',
            'batch' => 2020,
            'department_name' => 'Computer Science & Engineering',
            'program_name' => 'Bachelor of Science in Computer Science'
        ]);

        $student3 = User::create([
            'name' => 'Mike Student',
            'email' => 'mike.student@example.com',
            'password' => bcrypt('password'),
            'login_type' => 'student',
            'roll' => '2020123003',
            'batch' => 2020,
            'department_name' => 'Computer Science & Engineering',
            'program_name' => 'Bachelor of Science in Computer Science'
        ]);

        $student4 = User::create([
            'name' => 'Sarah Student',
            'email' => 'sarah.student@example.com',
            'password' => bcrypt('password'),
            'login_type' => 'student',
            'roll' => '2020123004',
            'batch' => 2020,
            'department_name' => 'Computer Science & Engineering',
            'program_name' => 'Bachelor of Science in Computer Science'
        ]);

        // Create groups
        $group1 = Group::create([
            'name' => 'Group 1',
            'batch_number' => 2020,
            'advisor_id' => $advisor->id,
            'max_students' => 3,
            'area_of_interest_id' => $aiArea->id,
            'supervisor_id' => $supervisor1->id
        ]);

        $group2 = Group::create([
            'name' => 'Group 2',
            'batch_number' => 2020,
            'advisor_id' => $advisor->id,
            'max_students' => 3,
            'area_of_interest_id' => $webArea->id,
            'supervisor_id' => $supervisor2->id
        ]);

        // Assign students to groups
        // Group 1: John, Jane, Mike
        GroupStudent::create([
            'group_id' => $group1->id,
            'student_id' => $student1->roll,
            'student_name' => $student1->name,
            'student_email' => $student1->email
        ]);

        GroupStudent::create([
            'group_id' => $group1->id,
            'student_id' => $student2->roll,
            'student_name' => $student2->name,
            'student_email' => $student2->email
        ]);

        GroupStudent::create([
            'group_id' => $group1->id,
            'student_id' => $student3->roll,
            'student_name' => $student3->name,
            'student_email' => $student3->email
        ]);

        // Group 2: Sarah (only one member for now)
        GroupStudent::create([
            'group_id' => $group2->id,
            'student_id' => $student4->roll,
            'student_name' => $student4->name,
            'student_email' => $student4->email
        ]);

        $this->command->info('Student dashboard demo data created successfully!');
        $this->command->info('Demo student accounts:');
        $this->command->info('- john.student@example.com (password: password) - In Group 1 with AI area and Dr. Alice Smith');
        $this->command->info('- jane.student@example.com (password: password) - In Group 1 with AI area and Dr. Alice Smith');
        $this->command->info('- mike.student@example.com (password: password) - In Group 1 with AI area and Dr. Alice Smith');
        $this->command->info('- sarah.student@example.com (password: password) - In Group 2 with Web Dev area and Dr. Bob Johnson');
    }
}