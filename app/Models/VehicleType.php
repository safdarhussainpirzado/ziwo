<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    public $timestamps = false;
    protected $fillable = ['name', 'is_active', 'sort_order'];
}
