<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\User;

class TestStudentLookup extends Command
{
    protected $signature = 'test:student-lookup {group_id?}';
    protected $description = 'Test student lookup for notifications';

    public function handle()
    {
        $groupId = $this->argument('group_id');
        
        if ($groupId) {
            $group = Group::find($groupId);
        } else {
            $group = Group::first();
        }
        
        if (!$group) {
            $this->error('No group found!');
            return 1;
        }
        
        $this->info("Testing student lookup for group: {$group->name} (ID: {$group->id})");
        
        // Get all students in the group
        $groupStudents = GroupStudent::where('group_id', $group->id)->get();
        
        $this->info("Found {$groupStudents->count()} students in group_students table:");
        
        foreach ($groupStudents as $groupStudent) {
            $this->line("  - Student ID: {$groupStudent->student_id}, Name: {$groupStudent->student_name}");
            
            // Try to find corresponding user
            $user = User::where('roll', $groupStudent->student_id)
                ->orWhere('student_id', $groupStudent->student_id)
                ->first();
                
            if ($user) {
                $this->info("    ✓ Found User: ID={$user->id}, Name={$user->name}, Roll={$user->roll}, Student_ID={$user->student_id}");
            } else {
                $this->error("    ✗ No User found with roll='{$groupStudent->student_id}' or student_id='{$groupStudent->student_id}'");
                
                // Let's check what users exist
                $this->line("    Checking for similar users:");
                $similarUsers = User::where('name', 'LIKE', '%' . explode(' ', $groupStudent->student_name)[0] . '%')
                    ->orWhere('roll', 'LIKE', '%' . $groupStudent->student_id . '%')
                    ->get();
                    
                foreach ($similarUsers as $similarUser) {
                    $this->line("      - User ID={$similarUser->id}, Name={$similarUser->name}, Roll={$similarUser->roll}, Student_ID={$similarUser->student_id}");
                }
            }
        }
        
        return 0;
    }
}