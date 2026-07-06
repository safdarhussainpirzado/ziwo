<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelephonyAgentConfig extends Model
{
    use HasFactory;

    protected $table = 'telephony_agent_configs';

    protected $fillable = [
        'user_id',
        'ziwo_username',
        'ziwo_token',
        'expires_at',
        'agent_status',
        'last_status_change_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_status_change_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
