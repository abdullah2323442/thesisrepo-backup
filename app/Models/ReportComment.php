<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'teacher_id',
        'body',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the report that owns the comment
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the teacher who made the comment
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the user who made the comment (alias for teacher)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get formatted date for display
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    /**
     * Get the role of the commenter for this report's group
     * Returns: 'main_supervisor', 'co_supervisor', 'panel_member', or null
     */
    public function getCommenterRole(): ?string
    {
        $group = $this->report->group;
        $teacherId = $this->teacher_id;

        // Find the supervisor record for this teacher
        $supervisor = \App\Models\Supervisor::where('email', $this->teacher->email)->first();
        
        if (!$supervisor) {
            return null;
        }

        // Check if main supervisor
        if ($group->supervisor_id === $supervisor->id) {
            return 'main_supervisor';
        }

        // Check if co-supervisor
        if ($group->co_supervisor_id === $supervisor->id) {
            return 'co_supervisor';
        }

        // Check if panel member
        if ($group->panelMembers()->where('supervisor_id', $supervisor->id)->exists()) {
            return 'panel_member';
        }

        return null;
    }

    /**
     * Get the role label for display
     */
    public function getRoleLabelAttribute(): string
    {
        $role = $this->getCommenterRole();
        
        return match($role) {
            'main_supervisor' => 'Main Supervisor',
            'co_supervisor' => 'Co-Supervisor',
            'panel_member' => 'Panel Member',
            default => 'Teacher',
        };
    }

    /**
     * Get the role badge color classes
     */
    public function getRoleBadgeColorAttribute(): string
    {
        $role = $this->getCommenterRole();
        
        return match($role) {
            'main_supervisor' => 'bg-blue-100 text-blue-800',
            'co_supervisor' => 'bg-purple-100 text-purple-800',
            'panel_member' => 'bg-orange-100 text-orange-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}