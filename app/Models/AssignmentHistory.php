<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentHistory extends Model
{
    protected $table = 'assignment_history';
    
    protected $fillable = [
        'group_id',
        'supervisor_id',
        'area_of_interest_id',
        'assignment_method',
        'assignment_round',
        'assigned_at',
        'unassigned_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
        'assignment_round' => 'integer'
    ];

    /**
     * Get the group for this history record
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the supervisor for this history record
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class);
    }

    /**
     * Get the area of interest for this history record
     */
    public function areaOfInterest(): BelongsTo
    {
        return $this->belongsTo(AreaOfInterest::class);
    }

    /**
     * Check if a supervisor was recently assigned to a group
     */
    public static function wasRecentlyAssigned(int $groupId, int $supervisorId, int $hoursAgo = 24): bool
    {
        return self::where('group_id', $groupId)
            ->where('supervisor_id', $supervisorId)
            ->where('assigned_at', '>=', now()->subHours($hoursAgo))
            ->exists();
    }

    /**
     * Get assignment count for a group-supervisor pair
     */
    public static function getAssignmentCount(int $groupId, int $supervisorId): int
    {
        return self::where('group_id', $groupId)
            ->where('supervisor_id', $supervisorId)
            ->count();
    }

    /**
     * Get supervisors previously assigned to a group
     */
    public static function getPreviousSupervisors(int $groupId, int $limit = 10): array
    {
        return self::where('group_id', $groupId)
            ->orderBy('assigned_at', 'desc')
            ->limit($limit)
            ->pluck('supervisor_id')
            ->unique()
            ->toArray();
    }

    /**
     * Record a new assignment
     */
    public static function recordAssignment(int $groupId, int $supervisorId, ?int $areaId = null, string $method = 'lottery'): void
    {
        // Get the round number (how many times this pairing has occurred)
        $round = self::getAssignmentCount($groupId, $supervisorId) + 1;
        
        self::create([
            'group_id' => $groupId,
            'supervisor_id' => $supervisorId,
            'area_of_interest_id' => $areaId,
            'assignment_method' => $method,
            'assignment_round' => $round,
            'assigned_at' => now()
        ]);
    }

    /**
     * Mark an assignment as unassigned
     */
    public static function markUnassigned(int $groupId, int $supervisorId): void
    {
        self::where('group_id', $groupId)
            ->where('supervisor_id', $supervisorId)
            ->whereNull('unassigned_at')
            ->update(['unassigned_at' => now()]);
    }
}