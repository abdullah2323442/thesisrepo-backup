<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListStudentUsers extends Command
{
    protected $signature = 'list:student-users';
    protected $description = 'List all student users in the system';

    public function handle()
    {
        $students = User::where('login_type', 'student')->get();
        
        $this->info("Found {$students->count()} student users:");
        
        foreach ($students as $student) {
            $this->line("ID: {$student->id}, Name: {$student->name}, Roll: {$student->roll}, Student_ID: {$student->student_id}, Email: {$student->email}");
        }
        
        if ($students->isEmpty()) {
            $this->warn("No student users found in the system!");
            $this->info("Students need to login first to create their user accounts.");
        }
        
        return 0;
    }
}