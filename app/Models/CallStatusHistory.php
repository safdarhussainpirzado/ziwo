<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallStatusHistory extends Model
{

    protected $table = 'call_status_history';
    public $timestamps = true;
    const UPDATED_AT = null;
    protected $fillable = ['call_id', 'changed_by', 'old_status', 'new_status', 'remarks', 'officer_id'];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function call() { return $this->belongsTo(Call::class); }
    public function changedBy() { return $this->belongsTo(User::class, 'changed_by'); }
}
