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
}