<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $fillable = [
        'group_id',
        'meeting_date',
        'discussed_topics',
        'outcomes',
    ];

    protected $casts = [
        'meeting_date' => 'date',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(MeetingAttendance::class);
    }

    public function getPresentCountAttribute(): int
    {
        return $this->attendances()->where('present', true)->count();
    }

    public function getTotalCountAttribute(): int
    {
        return $this->attendances()->count();
    }
}
