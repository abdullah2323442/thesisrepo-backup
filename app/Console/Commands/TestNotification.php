<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Report;
use App\Models\Group;
use App\Notifications\NewReportAssigned;
use Illuminate\Support\Facades\Notification;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:notification {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test notification to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $user = User::find($userId);
        } else {
            // Get first student user
            $user = User::where('login_type', 'student')->first();
        }
        
        if (!$user) {
            $this->error('No user found!');
            return 1;
        }
        
        // Create a dummy report for testing
        $group = Group::first();
        if (!$group) {
            $this->error('No group found! Please create a group first.');
            return 1;
        }
        
        // Create a test report
        $report = new Report();
        $report->id = 999999; // Dummy ID
        $report->group_id = $group->id;
        $report->type = 'general';
        $report->project_title = 'Test Report';
        $report->created_by = $user->id;
        
        // Manually set the group relationship
        $report->setRelation('group', $group);
        $report->setRelation('creator', $user);
        
        // Send notification using the Notification facade
        try {
            Notification::send($user, new NewReportAssigned($report));
            
            // Check if notification was created
            $count = $user->unreadNotifications()->count();
            
            $this->info("Test notification sent to user: {$user->name} (ID: {$user->id})");
            $this->info("Current unread notifications count: {$count}");
            $this->info("Check the student dashboard for the notification bell with red count.");
            
            // Show the last notification
            $lastNotification = $user->notifications()->latest()->first();
            if ($lastNotification) {
                $this->info("Last notification data: " . json_encode($lastNotification->data));
            }
        } catch (\Exception $e) {
            $this->error("Failed to send notification: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}