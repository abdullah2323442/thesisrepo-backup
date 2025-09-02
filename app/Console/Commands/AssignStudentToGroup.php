<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\User;

class AssignStudentToGroup extends Command
{
    protected $signature = 'assign:student-to-group {user_id?} {group_id?}';
    protected $description = 'Assign a student user to a group for testing notifications';

    public function handle()
    {
        $userId = $this->argument('user_id') ?? 3; // Default to MD. ABDULLAH JOBAYER
        $groupId = $this->argument('group_id') ?? 1; // Default to first group
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return 1;
        }
        
        $group = Group::find($groupId);
        if (!$group) {
            $this->error("Group with ID {$groupId} not found!");
            return 1;
        }
        
        // Check if student is already in the group
        $existing = GroupStudent::where('group_id', $group->id)
            ->where('student_id', $user->roll)
            ->first();
            
        if ($existing) {
            $this->info("Student is already in the group!");
            $this->info("Student ID in group_students: {$existing->student_id}");
            $this->info("User roll: {$user->roll}");
            return 0;
        }
        
        // Add student to group
        GroupStudent::create([
            'group_id' => $group->id,
            'student_id' => $user->roll, // Use the user's roll number
            'student_name' => $user->name,
            'student_email' => $user->email,
        ]);
        
        $this->info("Successfully assigned student to group!");
        $this->info("User: {$user->name} (Roll: {$user->roll})");
        $this->info("Group: {$group->name} (ID: {$group->id})");
        $this->info("Now when a report is created for this group, the student will receive notifications.");
        
        return 0;
    }
}