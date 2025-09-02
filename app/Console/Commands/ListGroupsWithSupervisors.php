<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Group;

class ListGroupsWithSupervisors extends Command
{
    protected $signature = 'list:groups-with-supervisors';
    protected $description = 'List all groups with their supervisors';

    public function handle()
    {
        $groups = Group::with('supervisor')->get();
        
        $this->info("Groups and their supervisors:");
        $this->info("================================");
        
        foreach ($groups as $group) {
            if ($group->supervisor) {
                $this->info("Group: {$group->name} (ID: {$group->id})");
                $this->line("  Supervisor: {$group->supervisor->fullname} (Email: {$group->supervisor->email})");
                $studentCount = $group->students()->count();
                $this->line("  Students: {$studentCount}");
            } else {
                $this->warn("Group: {$group->name} (ID: {$group->id}) - NO SUPERVISOR");
            }
        }
        
        return 0;
    }
}