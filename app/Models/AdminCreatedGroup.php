<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for the admin_created_groups_view
 * This provides a read-only view of groups created by admin for advisors to see
 */
class AdminCreatedGroup extends Model
{
    protected $table = 'admin_created_groups_view';
    
    // This is a view, so no timestamps or fillable fields
    public $timestamps = false;
    protected $fillable = [];

    protected $casts = [
        'batch_number' => 'integer',
        'advisor_id' => 'integer',
        'created_by_admin_id' => 'integer',
        'advisor_auto_detected' => 'boolean',
        'area_of_interest_id' => 'integer',
        'supervisor_id' => 'integer',
        'max_students' => 'integer',
        'is_manual_assignment' => 'boolean',
        'student_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the advisor that owns the group
     */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    /**
     * Get the admin who created the group
     */
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    /**
     * Get the area of interest for this group
     */
    public function areaOfInterest(): BelongsTo
    {
        return $this->belongsTo(AreaOfInterest::class, 'area_of_interest_id');
    }

    /**
     * Get the supervisor assigned to this group
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'supervisor_id');
    }

    /**
     * Get the actual group model for full functionality
     */
    public function actualGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'id');
    }

    /**
     * Scope to get groups for a specific advisor
     */
    public function scopeForAdvisor($query, int $advisorId)
    {
        return $query->where('advisor_id', $advisorId);
    }

    /**
     * Scope to get groups for a specific batch
     */
    public function scopeForBatch($query, int $batchNumber)
    {
        return $query->where('batch_number', $batchNumber);
    }

    /**
     * Check if group is full
     */
    public function isFull(): bool
    {
        return $this->student_count >= $this->max_students;
    }

    /**
     * Get available slots in the group
     */
    public function getAvailableSlotsAttribute(): int
    {
        return $this->max_students - $this->student_count;
    }

    /**
     * Get formatted student names
     */
    public function getFormattedStudentNamesAttribute(): string
    {
        return $this->student_names ?? 'No students assigned';
    }

    /**
     * Check if advisor was auto-detected
     */
    public function isAdvisorAutoDetected(): bool
    {
        return $this->advisor_auto_detected === true;
    }

    /**
     * Get status badge color based on group state
     */
    public function getStatusBadgeColorAttribute(): string
    {
        if ($this->supervisor_id) {
            return 'green'; // Has supervisor
        } elseif ($this->area_of_interest_id) {
            return 'yellow'; // Has area of interest but no supervisor
        } elseif ($this->student_count > 0) {
            return 'blue'; // Has students but no area of interest
        } else {
            return 'gray'; // Empty group
        }
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute(): string
    {
        if ($this->supervisor_id) {
            return 'Complete';
        } elseif ($this->area_of_interest_id) {
            return 'Pending Supervisor';
        } elseif ($this->student_count > 0) {
            return 'Pending Area of Interest';
        } else {
            return 'Empty';
        }
    }
}