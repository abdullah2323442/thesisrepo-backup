<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportAnnotationSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'submission_id',
        'supervisor_id',
        'version',
        'message',
        'annotations_json',
        'annotated_file_path',
        'is_sent',
        'sent_at',
        'created_by_type',
    ];

    protected $casts = [
        'annotations_json' => 'array',
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the report that owns this annotation session
     */
    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the submission that this annotation session belongs to
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(StudentReportSubmission::class, 'submission_id');
    }

    /**
     * Get the supervisor who created this annotation session
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get the URL for the annotated PDF file
     */
    public function getAnnotatedFileUrlAttribute(): ?string
    {
        if (!$this->annotated_file_path) {
            return null;
        }
        
        return \Illuminate\Support\Facades\Storage::url($this->annotated_file_path);
    }

    /**
     * Get formatted date for display
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    /**
     * Get the next version number for a submission
     */
    public static function getNextVersionForSubmission(int $submissionId): int
    {
        $lastVersion = static::where('submission_id', $submissionId)
            ->max('version');
            
        return ($lastVersion ?? 0) + 1;
    }

    /**
     * Delete the associated annotated file when the model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($session) {
            if ($session->annotated_file_path && \Illuminate\Support\Facades\Storage::exists($session->annotated_file_path)) {
                \Illuminate\Support\Facades\Storage::delete($session->annotated_file_path);
            }
        });
    }
}