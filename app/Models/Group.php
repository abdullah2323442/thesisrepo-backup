<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'batch_number',
        'advisor_id',
        'max_students',
        'area_of_interest_id',
        'matched_area_of_interest_id',
        'supervisor_id',
        'is_manual_assignment',
        'assignment_priority',
        'assigned_at',
        'created_by_type',
        'created_by_admin_id',
        'advisor_auto_detected'
    ];

    protected $casts = [
        'batch_number' => 'integer',
        'advisor_id' => 'integer',
        'max_students' => 'integer',
        'supervisor_id' => 'integer',
        'area_of_interest_id' => 'integer',
        'is_manual_assignment' => 'boolean',
        'assignment_priority' => 'integer',
        'assigned_at' => 'datetime',
        'created_by_admin_id' => 'integer',
        'advisor_auto_detected' => 'boolean'
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
     * Get meetings for this group
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    /**
     * Get the area of interest for this group (legacy - single area)
     * @deprecated Use areasOfInterest() for multiple areas support
     */
    public function areaOfInterest(): BelongsTo
    {
        return $this->belongsTo(AreaOfInterest::class, 'area_of_interest_id');
    }

    /**
     * Get the areas of interest for this group (many-to-many relationship)
     */
    public function areasOfInterest(): BelongsToMany
    {
        return $this->belongsToMany(AreaOfInterest::class, 'group_area_of_interest')
                    ->withTimestamps();
    }

    /**
     * Get the supervisor assigned to this group
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class, 'supervisor_id');
    }

    /**
     * Get the matched/finalized area of interest (the one that led to supervisor assignment)
     */
    public function matchedAreaOfInterest(): BelongsTo
    {
        return $this->belongsTo(AreaOfInterest::class, 'matched_area_of_interest_id');
    }

    /**
     * Get the admin who created this group (if created by admin)
     */
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
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
        // Check if group has at least one area of interest (either legacy or new)
        $hasAreas = $this->areasOfInterest()->exists() || !is_null($this->area_of_interest_id);
        return !$this->hasSupervisor() && $hasAreas;
    }

    /**
     * Check if group has a specific area of interest
     */
    public function hasAreaOfInterest(int $areaId): bool
    {
        return $this->areasOfInterest()->where('area_of_interests.id', $areaId)->exists();
    }

    /**
     * Get all area of interest IDs for this group
     */
    public function getAreaOfInterestIds(): array
    {
        $ids = $this->areasOfInterest()->pluck('area_of_interests.id')->toArray();
        
        // Include legacy single area if exists and not already in array
        if ($this->area_of_interest_id && !in_array($this->area_of_interest_id, $ids)) {
            $ids[] = $this->area_of_interest_id;
        }
        
        return $ids;
    }

    /**
     * Sync areas of interest for this group
     */
    public function syncAreasOfInterest(array $areaIds): void
    {
        $this->areasOfInterest()->sync($areaIds);
        // Always clear legacy single area to avoid stale display
        $this->area_of_interest_id = null;
        // Also clear matched AOI so it will be recomputed on next assignment
        $this->matched_area_of_interest_id = null;
        $this->save();
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

    /**
     * Scope to get groups created by admin
     */
    public function scopeCreatedByAdmin($query)
    {
        return $query->where('created_by_type', 'admin');
    }

    /**
     * Scope to get groups created by advisor
     */
    public function scopeCreatedByAdvisor($query)
    {
        return $query->where('created_by_type', 'advisor');
    }

    /**
     * Check if group was created by admin
     */
    public function isCreatedByAdmin(): bool
    {
        return $this->created_by_type === 'admin';
    }

    /**
     * Check if advisor was auto-detected from student API
     */
    public function isAdvisorAutoDetected(): bool
    {
        return $this->advisor_auto_detected === true;
    }

    /**
     * Get the creation source text
     */
    public function getCreationSourceAttribute(): string
    {
        if ($this->created_by_type === 'admin') {
            $adminName = $this->createdByAdmin ? $this->createdByAdmin->name : 'Unknown Admin';
            return "Created by Admin: {$adminName}";
        }
        return 'Created by Advisor';
    }

    /**
     * Get advisor assignment status text
     */
    public function getAdvisorStatusAttribute(): string
    {
        if (!$this->advisor_id) {
            return 'No advisor assigned';
        }
        
        if ($this->advisor_auto_detected) {
            return 'Advisor auto-detected from student';
        }
        
        return 'Advisor manually assigned';
    }
}
