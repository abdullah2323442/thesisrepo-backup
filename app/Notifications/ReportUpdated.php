<?php

namespace App\Notifications;

use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportUpdated extends Notification
{
    use Queueable;

    protected $report;
    protected $changes;

    /**
     * Create a new notification instance.
     */
    public function __construct(Report $report, array $changes = [])
    {
        $this->report = $report;
        $this->changes = $changes;
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
        $mainMessage = "Your {$reportType} Report has been updated by your supervisor";
        
        // Add details about what changed
        $changeDetails = [];
        if (isset($this->changes['type'])) {
            $changeDetails[] = "Report type changed to " . ucfirst($this->changes['type']);
        }
        if (isset($this->changes['supervisor_message'])) {
            $changeDetails[] = "Supervisor message updated";
        }
        if (isset($this->changes['extra_input'])) {
            $changeDetails[] = "Additional requirements updated";
        }
        
        if (!empty($changeDetails)) {
            $mainMessage .= "\n\nChanges made: " . implode(', ', $changeDetails);
        }
        
        // Add supervisor message if provided
        if ($this->report->supervisor_message) {
            $mainMessage .= "\n\nCurrent Instructions: " . $this->report->supervisor_message;
        }
        
        return [
            'type' => 'report_updated',
            'report_id' => $this->report->id,
            'report_type' => $this->report->type,
            'group_id' => $this->report->group_id,
            'group_name' => $groupName,
            'title' => "{$reportType} Report Updated",
            'message' => $mainMessage,
            'supervisor_message' => $this->report->supervisor_message,
            'project_title' => $this->report->project_title,
            'updated_by' => $this->report->creator->name,
            'changes' => $this->changes,
            'updated_at' => $this->report->updated_at->toISOString(),
        ];
    }
}