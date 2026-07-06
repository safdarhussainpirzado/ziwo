<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeospatialMarker extends Model
{
    protected $fillable = [
        'office_id',
        'km',
        'lat',
        'lng',
        'side',
    ];

    protected $casts = [
        'km' => 'decimal:2',
        'lat' => 'double',
        'lng' => 'double',
    ];

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
