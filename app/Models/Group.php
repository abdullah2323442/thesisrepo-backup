<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'name',
        'batch_number',
        'advisor_id',
        'max_students',
        'area_of_interest_id',
        'supervisor_id',
        'is_manual_assignment',
        'assignment_priority',
        'assigned_at'
    ];

    protected $casts = [
        'batch_number' => 'integer',
        'advisor_id' => 'integer',
        'max_students' => 'integer',
        'supervisor_id' => 'integer',
        'area_of_interest_id' => 'integer',
        'is_manual_assignment' => 'boolean',
        'assignment_priority' => 'integer',
        'assigned_at' => 'datetime'
    ];

    /**
     * Get the advisor that owns the group
     */
    public function advisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'advisor_id');
    }

    /**
     * Get the students in this group
     */
    public function students(): HasMany
    {
        return $this->hasMany(GroupStudent::class);
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
     * Get the count of students in this group
     */
    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
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
     * Extract group number from group name for sorting
     */
    public function getGroupNumberAttribute(): int
    {
        if (preg_match('/(\d+)/', $this->name, $matches)) {
            return (int) $matches[1];
        }
        return 0; // Default for groups without numbers
    }

    /**
     * Check if group has a supervisor assigned
     */
    public function hasSupervisor(): bool
    {
        return !is_null($this->supervisor_id);
    }

    /**
     * Check if group is eligible for supervisor assignment
     */
    public function isEligibleForAssignment(): bool
    {
        return !$this->hasSupervisor() && !is_null($this->area_of_interest_id);
    }

    /**
     * Check if this is a manual assignment (pre-assigned by advisor)
     */
    public function isManualAssignment(): bool
    {
        return $this->is_manual_assignment === true;
    }

    /**
     * Scope to get groups without supervisors
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('supervisor_id');
    }

    /**
     * Scope to get groups with supervisors
     */
    public function scopeAssigned($query)
    {
        return $query->whereNotNull('supervisor_id');
    }

    /**
     * Scope to get manually assigned groups
     */
    public function scopeManuallyAssigned($query)
    {
        return $query->where('is_manual_assignment', true);
    }

    /**
     * Scope to get lottery eligible groups (not manually assigned and has area of interest)
     */
    public function scopeLotteryEligible($query)
    {
        return $query->whereNull('supervisor_id')
                    ->where('is_manual_assignment', false)
                    ->whereNotNull('area_of_interest_id');
    }

    /**
     * Scope to order groups by their numeric value (SQLite compatible)
     */
    public function scopeOrderByGroupNumber($query)
    {
        // For SQLite compatibility, we'll do the sorting in PHP instead
        // This scope is kept for potential future use but sorting is handled in the controller
        return $query->orderBy('name');
    }
}
