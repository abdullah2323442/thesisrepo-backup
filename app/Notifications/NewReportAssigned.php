<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReportAssigned extends Notification
{
    use Queueable;

    protected $report;

    /**
     * Create a new notification instance.
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
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
        $reportType = ucfirst($this->report->type);
        $groupName = $this->report->group->name;
        
        // Build the main message
        $mainMessage = "A new {$reportType} Report has been assigned to your group ({$groupName})";
        
        // Add supervisor message if provided
        if ($this->report->supervisor_message) {
            $mainMessage .= "\n\nSupervisor Instructions: " . $this->report->supervisor_message;
        }
        
        return [
            'type' => 'report_assigned',
            'report_id' => $this->report->id,
            'report_type' => $this->report->type,
            'group_id' => $this->report->group_id,
            'group_name' => $groupName,
            'title' => "{$reportType} Report Assigned",
            'message' => $mainMessage,
            'supervisor_message' => $this->report->supervisor_message,
            'project_title' => $this->report->project_title,
            'created_by' => $this->report->creator->name,
            'created_at' => $this->report->created_at->toISOString(),
        ];
    }
}