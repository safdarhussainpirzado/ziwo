<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallType extends Model
{
    public $timestamps = false;

    protected $fillable = ['name', 'category', 'priority', 'color_hex', 'sort_order', 'is_active'];

    public function subTypes()
    {
        return $this->hasMany(CallSubType::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
