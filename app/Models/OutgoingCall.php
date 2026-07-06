<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutgoingCall extends Model
{
    protected $fillable = ['call_id', 'made_by', 'called_no', 'reason', 'duration_sec', 'outcome', 'called_at'];
    protected $casts = ['called_at' => 'datetime'];

    public function call() { return $this->belongsTo(Call::class); }
    public function madeBy() { return $this->belongsTo(User::class, 'made_by'); }
}
