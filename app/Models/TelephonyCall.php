<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelephonyCall extends Model
{
    use HasFactory;

    protected $table = 'telephony_call_logs';

    protected $fillable = [
        'call_id',
        'call_uuid',
        'agent_id',
        'caller_number',
        'direction',
        'status',
        'recording_url',
        'start_time',
        'end_time',
        'duration_seconds',
        'metadata',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_seconds' => 'integer',
        'metadata' => 'array',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
