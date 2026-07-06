<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeospatialLandmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_id',
        'road_name',
        'bound_direction',
        'km_marker',
        'km_numeric',
        'zone_name',
        'sector_name',
        'beat_name',
        'location_name',
        'nearby_cities',
        'fuel_station',
        'agent_prompt',
        'contact_numbers',
    ];

    /**
     * Get the beat (office) associated with this landmark.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }

    public function getZoneAttribute()
    {
        return $this->office?->zone();
    }

    public function getSectorAttribute()
    {
        return $this->office?->sector();
    }

    public function getStatusAttribute(): string
    {
        return str_starts_with($this->agent_prompt ?? '', '[INACTIVE]') ? 'inactive' : 'active';
    }
}
