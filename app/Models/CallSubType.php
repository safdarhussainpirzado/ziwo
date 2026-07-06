<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallSubType extends Model
{
    public $timestamps = false;

    protected $fillable = ['call_type_id', 'name', 'priority', 'is_active', 'sort_order'];

    public function callType()
    {
        return $this->belongsTo(CallType::class);
    }
}
