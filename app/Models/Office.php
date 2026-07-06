<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Office extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'parent_id',
        'operational_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Office::class, 'parent_id');
    }

    /**
     * Get all descendant IDs of this office, including itself if $includeSelf is true.
     */
    public function getDescendantIds(bool $includeSelf = true): array
    {
        $ids = $includeSelf ? [$this->id] : [];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getDescendantIds(true));
        }
        return $ids;
    }

    /**
     * Get the root office (Region).
     */
    public function region()
    {
        if ($this->type === 'region') {
            return $this;
        }
        return $this->parent ? $this->parent->region() : null;
    }

    /**
     * Get the Zone.
     */
    public function zone()
    {
        if ($this->type === 'zone') {
            return $this;
        }
        return $this->parent ? $this->parent->zone() : null;
    }

    /**
     * Get the Sector.
     */
    public function sector()
    {
        if ($this->type === 'sector') {
            return $this;
        }
        return $this->parent ? $this->parent->sector() : null;
    }

    // Scopes for specific types
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRegions($query)
    {
        return $query->where('type', 'region');
    }

    public function scopeZones($query)
    {
        return $query->where('type', 'zone');
    }

    public function scopeSectors($query)
    {
        return $query->where('type', 'sector');
    }

    public function scopeBeats($query)
    {
        return $query->where('type', 'beat');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
