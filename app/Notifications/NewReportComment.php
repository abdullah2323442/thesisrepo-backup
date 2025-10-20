<?php

namespace App\Notifications;

use App\Models\ReportComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReportComment extends Notification
{
    use Queueable;

    protected $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReportComment $comment)
    {
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $report = $this->comment->report;
        $reportType = ucfirst($report->type);
        $groupName = $report->group->name;
        
        // Determine the commenter's role
        $role = $this->comment->getCommenterRole();
        $roleLabel = match($role) {
            'main_supervisor' => 'supervisor',
            'co_supervisor' => 'co-supervisor',
            'panel_member' => 'panel member',
            default => 'supervisor',
        };
        
        return [
            'type' => 'report_comment',
            'report_id' => $report->id,
            'comment_id' => $this->comment->id,
            'report_type' => $report->type,
            'group_id' => $report->group_id,
            'group_name' => $groupName,
            'title' => "New Comment on {$reportType} Report",
            'message' => "Your {$roleLabel} commented on your {$reportType} Report",
            'comment_preview' => \Str::limit($this->comment->body, 100),
            'teacher_name' => $this->comment->teacher->name,
            'supervisor_name' => $this->comment->teacher->name, // For notification bell compatibility
            'teacher_role' => $roleLabel,
            'created_at' => $this->comment->created_at->toISOString(),
        ];
    }
}