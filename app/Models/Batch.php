<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_number',
        'program_id',
        'batch_name',
        'is_active',
        'description',
        'last_synced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Get the display name for the batch
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->batch_name ?: "Batch {$this->batch_number}";
    }

    /**
     * Scope to get only active batches
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only inactive batches
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get batch statistics
     */
    public static function getStats(): array
    {
        return [
            'total' => self::count(),
            'active' => self::where('is_active', true)->count(),
            'inactive' => self::where('is_active', false)->count(),
            'last_sync' => self::max('last_synced_at'),
        ];
    }
}
