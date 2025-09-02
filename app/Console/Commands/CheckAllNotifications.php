<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAllNotifications extends Command
{
    protected $signature = 'notifications:check-all';
    protected $description = 'Check all notifications in the database';

    public function handle()
    {
        $notifications = DB::table('notifications')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $this->info("Total notifications in database: " . $notifications->count());
        
        foreach ($notifications as $notification) {
            $data = json_decode($notification->data, true);
            $this->line("\nNotification ID: {$notification->id}");
            $this->line("  User ID: {$notification->notifiable_id}");
            $this->line("  Type: {$notification->type}");
            $this->line("  Title: " . ($data['title'] ?? 'N/A'));
            $this->line("  Group: " . ($data['group_name'] ?? 'N/A'));
            $this->line("  Created: {$notification->created_at}");
            $this->line("  Read: " . ($notification->read_at ? 'Yes' : 'No'));
        }
        
        return 0;
    }
}