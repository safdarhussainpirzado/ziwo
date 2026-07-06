<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserScope extends Model
{
    protected $fillable = [
        'user_id',
        'office_id',
        'access_level',
        'label',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'office_id' => 'integer',
    ];

    /**
     * The access levels available per scope.
     */
    const ACCESS_LEVELS = [
        'read_only'  => 'Read Only',
        'read_write' => 'Read & Write',
        'full'       => 'Full Access',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * Returns human-readable display name for the scope.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->label) {
            return $this->label;
        }
        
        $office = $this->office;
        $typeName = $office ? ucfirst($office->type) : 'National';
        $unitName = $office ? $office->name : 'All Units';
        $level    = self::ACCESS_LEVELS[$this->access_level] ?? $this->access_level;

        return "{$typeName}: {$unitName} [{$level}]";
    }

    /**
     * Whether this scope allows write operations (store/update).
     */
    public function canWrite(): bool
    {
        return in_array($this->access_level, ['read_write', 'full']);
    }

    /**
     * Whether this scope allows delete/export operations.
     */
    public function canDelete(): bool
    {
        return $this->access_level === 'full';
    }
}
