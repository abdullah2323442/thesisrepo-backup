<?php

namespace App\Notifications;

use App\Models\ReportAnnotationSession;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewReportAnnotation extends Notification
{
    use Queueable;

    protected $annotationSession;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReportAnnotationSession $annotationSession)
    {
        $this->annotationSession = $annotationSession;
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
        $report = $this->annotationSession->report;
        $submission = $this->annotationSession->submission;
        $reportType = ucfirst($report->type);
        $groupName = $report->group->name;
        
        // Determine the role of the person who created the annotation
        $createdByType = $this->annotationSession->created_by_type ?? 'supervisor';
        $roleTitle = match($createdByType) {
            'co_supervisor' => 'Co-Supervisor',
            'panel_member' => 'Panel Member',
            default => 'Supervisor'
        };
        
        return [
            'type' => 'report_annotation',
            'report_id' => $report->id,
            'submission_id' => $submission->id,
            'annotation_session_id' => $this->annotationSession->id,
            'report_type' => $report->type,
            'group_id' => $report->group_id,
            'group_name' => $groupName,
            'title' => "New {$roleTitle} Feedback on {$reportType} Report",
            'message' => "Your {$roleTitle} has reviewed and annotated your {$reportType} Report submission",
            'feedback_preview' => $this->annotationSession->message ? \Str::limit($this->annotationSession->message, 100) : 'Annotations added',
            'supervisor_name' => $this->annotationSession->supervisor->name,
            'supervisor_role' => $roleTitle,
            'created_by_type' => $createdByType,
            'version' => $this->annotationSession->version,
            'created_at' => $this->annotationSession->created_at->toISOString(),
        ];
    }
}