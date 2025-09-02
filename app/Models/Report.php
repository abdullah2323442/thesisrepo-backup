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
        'area_of_interest_id',
        'type',
        'project_title',
        'abstract_md',
        'extra_input',
        'supervisor_message',
        'keywords',
        'created_by',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_UNDER_REVIEW = 'under_review';
    const STATUS_APPROVED = 'approved';

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
     * Get the user who approved the report
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the area of interest for the report
     */
    public function areaOfInterest(): BelongsTo
    {
        return $this->belongsTo(AreaOfInterest::class, 'area_of_interest_id');
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

    /**
     * Status check methods
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isUnderReview(): bool
    {
        return $this->status === self::STATUS_UNDER_REVIEW;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if report can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->isFinal() && 
               $this->submissions()->exists() && 
               !$this->isApproved();
    }

    /**
     * Check if report has student submissions
     */
    public function hasSubmissions(): bool
    {
        return $this->submissions()->exists();
    }

    /**
     * Scope to filter approved reports
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to filter final reports
     */
    public function scopeFinal($query)
    {
        return $query->where('type', 'final');
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'bg-gray-100 text-gray-800',
            self::STATUS_SUBMITTED => 'bg-blue-100 text-blue-800',
            self::STATUS_UNDER_REVIEW => 'bg-yellow-100 text-yellow-800',
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get formatted status text
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_APPROVED => 'Approved',
            default => 'Unknown',
        };
    }
}