<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carriageway extends Model
{
    protected $fillable = [
        'type',
        'road',
        'road_short',
        'road_name',
        'road_from',
        'road_to',
        'total_km',
        'status',
    ];

}
