<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateDirectNotification extends Command
{
    protected $signature = 'notification:create {user_id?}';
    protected $description = 'Create a notification directly in the database';

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
        
        // Create notification directly
        $notificationId = Str::uuid()->toString();
        
        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\Notifications\NewReportAssigned',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'type' => 'report_assigned',
                'report_id' => 1,
                'report_type' => 'general',
                'group_id' => 1,
                'group_name' => 'Test Group',
                'title' => 'General Report Assigned',
                'message' => 'A new General Report has been assigned to your group (Test Group)',
                'project_title' => null,
                'created_by' => 'Test Supervisor',
                'created_at' => now()->toISOString(),
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->info("Notification created directly for user: {$user->name} (ID: {$user->id})");
        
        // Check count
        $count = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', 'App\Models\User')
            ->whereNull('read_at')
            ->count();
            
        $this->info("Current unread notifications count: {$count}");
        
        return 0;
    }
}