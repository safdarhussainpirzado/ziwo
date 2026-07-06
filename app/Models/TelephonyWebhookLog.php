<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelephonyWebhookLog extends Model
{
    use HasFactory;

    protected $table = 'telephony_webhook_logs';

    protected $fillable = [
        'event_type',
        'payload',
        'processed',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed' => 'boolean',
    ];
}
