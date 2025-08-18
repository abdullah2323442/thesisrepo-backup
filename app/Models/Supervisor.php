<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supervisor extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_id',
        'fullname',
        'gender',
        'email',
        'designation',
        'department',
        'thesis_limit',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Relationship with areas of interest
     */
    public function areasOfInterest()
    {
        return $this->belongsToMany(AreaOfInterest::class, 'supervisor_area_of_interest');
    }



    /**
     * Check if supervisor is active and has available slots
     */
    public function canTakeThesis(): bool
    {
        return $this->is_active && $this->available_slots > 0;
    }

    /**
     * Get the supervisor's rank priority (lower number = higher priority)
     */
    public function getRankPriorityAttribute(): int
    {
        // Normalize designation: lowercase, strip punctuation and non-letters, collapse spaces
        $designation = mb_strtolower((string) $this->designation, 'UTF-8');
        $normalized = preg_replace('/[^a-z]+/u', ' ', $designation); // keep only letters as words
        $normalized = trim(preg_replace('/\s+/', ' ', $normalized));

        // Determine priority based on tokens present
        if (str_contains($normalized, 'associate') && str_contains($normalized, 'professor')) {
            return 2;
        }
        if (str_contains($normalized, 'assistant') && str_contains($normalized, 'professor')) {
            return 3;
        }
        if (str_contains($normalized, 'professor')) {
            return 1;
        }
        if (str_contains($normalized, 'lecturer')) {
            return 4;
        }

        return 5; // Default to lowest priority
    }

    /**
     * Get the supervisor's rank title
     */
    public function getRankTitleAttribute(): string
    {
        return $this->designation;
    }

    /**
     * Get groups assigned to this supervisor
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'supervisor_id');
    }

    /**
     * Get the number of assigned groups/theses
     */
    public function getAssignedThesesCountAttribute(): int
    {
        return $this->groups()->count();
    }

    /**
     * Get actual available slots (thesis_limit - assigned theses)
     */
    public function getAvailableSlotsAttribute(): int
    {
        return max(0, $this->thesis_limit - $this->assigned_theses_count);
    }

    /**
     * Scope to get supervisors by rank priority
     */
    public function scopeByRankPriority($query)
    {
        return $query->orderByRaw("
            CASE
                WHEN (LOWER(TRIM(designation)) LIKE '%associate%' AND LOWER(TRIM(designation)) LIKE '%professor%') THEN 2
                WHEN (LOWER(TRIM(designation)) LIKE '%assistant%' AND LOWER(TRIM(designation)) LIKE '%professor%') THEN 3
                WHEN LOWER(TRIM(designation)) LIKE '%professor%' THEN 1
                WHEN LOWER(TRIM(designation)) LIKE '%lecturer%' THEN 4
                ELSE 5
            END ASC, fullname ASC
        ");
    }

    /**
     * Scope to get supervisors with available slots
     */
    public function scopeWithAvailableSlots($query)
    {
        return $query->where('is_active', true)
                    ->whereRaw('thesis_limit > (SELECT COUNT(*) FROM groups WHERE supervisor_id = supervisors.id)');
    }

    /**
     * Check if supervisor has matching area of interest
     */
    public function hasAreaOfInterest(int $areaOfInterestId): bool
    {
        return $this->areasOfInterest()->where('area_of_interests.id', $areaOfInterestId)->exists();
    }
}
