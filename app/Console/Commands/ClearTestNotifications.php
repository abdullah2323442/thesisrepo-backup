<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ClearTestNotifications extends Command
{
    protected $signature = 'notifications:clear-test {user_id?}';
    protected $description = 'Clear test notifications from the database';

    public function handle()
    {
        $userId = $this->argument('user_id') ?? 3;
        
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return 1;
        }
        
        // Delete notifications with test data
        $deleted = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', 'App\Models\User')
            ->where('data', 'LIKE', '%Test Group%')
            ->delete();
            
        $this->info("Deleted {$deleted} test notifications for user: {$user->name}");
        
        // Show current notification count
        $currentCount = $user->notifications()->count();
        $unreadCount = $user->unreadNotifications()->count();
        
        $this->info("Current notifications: {$currentCount} total, {$unreadCount} unread");
        
        // Show latest notifications
        $latest = $user->notifications()->latest()->take(5)->get();
        if ($latest->isNotEmpty()) {
            $this->info("\nLatest notifications:");
            foreach ($latest as $notification) {
                $data = $notification->data;
                $this->line("  - {$data['title']} (Group: {$data['group_name']}) - " . ($notification->read_at ? 'Read' : 'Unread'));
            }
        }
        
        return 0;
    }
}