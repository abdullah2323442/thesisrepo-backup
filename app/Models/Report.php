<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group_id',
        'type',
        'project_title',
        'abstract_md',
        'extra_input',
        'supervisor_message',
        'keywords',
        'created_by',
    ];

    protected $casts = [
        'keywords' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the group that owns the report
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the user who created the report
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the comments for the report
     */
    public function comments(): HasMany
    {
        return $this->hasMany(ReportComment::class);
    }

    /**
     * Get the student submissions for the report
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(StudentReportSubmission::class);
    }

    /**
     * Check if report is of type 'final'
     */
    public function isFinal(): bool
    {
        return $this->type === 'final';
    }

    /**
     * Check if report is of type 'general'
     */
    public function isGeneral(): bool
    {
        return $this->type === 'general';
    }

    /**
     * Get formatted keywords as string
     */
    public function getKeywordsStringAttribute(): string
    {
        if (is_array($this->keywords)) {
            return implode(', ', $this->keywords);
        }
        return $this->keywords ?? '';
    }

    /**
     * Set keywords from string
     */
    public function setKeywordsFromString(string $keywords): void
    {
        $this->keywords = array_map('trim', explode(',', $keywords));
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by group
     */
    public function scopeForGroup($query, int $groupId)
    {
        return $query->where('group_id', $groupId);
    }
}