<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model
{
    protected $fillable = ['name', 'short_code', 'bps', 'similar_rank', 'type', 'sort_order', 'is_field', 'is_active'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
