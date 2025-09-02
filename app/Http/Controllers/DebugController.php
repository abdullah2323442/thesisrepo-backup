<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DebugController extends Controller
{
    public function checkNotifications()
    {
        // Get the first student user
        $user = User::where('login_type', 'student')->first();
        
        if (!$user) {
            return response()->json(['error' => 'No student user found']);
        }
        
        // Get all notifications
        $allNotifications = $user->notifications()->get();
        $unreadNotifications = $user->unreadNotifications()->get();
        
        // Check the notifications table directly
        $directQuery = \DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', 'App\Models\User')
            ->get();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'type' => $user->login_type
            ],
            'notifications_count' => $allNotifications->count(),
            'unread_count' => $unreadNotifications->count(),
            'all_notifications' => $allNotifications,
            'unread_notifications' => $unreadNotifications,
            'direct_query_count' => $directQuery->count(),
            'direct_query' => $directQuery
        ]);
    }
}