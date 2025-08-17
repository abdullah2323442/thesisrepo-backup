<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupStudent extends Model
{
    protected $fillable = [
        'group_id',
        'student_id',
        'student_name',
        'student_email'
    ];

    protected $casts = [
        'group_id' => 'integer'
    ];

    /**
     * Get the group that owns the student
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
