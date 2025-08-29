<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingAttendance extends Model
{
    protected $fillable = [
        'meeting_id',
        'group_student_id',
        'present',
    ];

    protected $casts = [
        'present' => 'boolean',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function groupStudent(): BelongsTo
    {
        return $this->belongsTo(GroupStudent::class);
    }
}
