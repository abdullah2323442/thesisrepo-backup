<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupPanelMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'supervisor_id',
        'assigned_at',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    /**
     * Get the group that this panel member belongs to
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the supervisor who is the panel member
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class);
    }

    /**
     * Get the admin who assigned this panel member
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}