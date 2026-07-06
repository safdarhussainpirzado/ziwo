<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Call extends Model
{
    use HasFactory;

    protected $fillable = [
        'call_number',
        'agent_id',
        'caller_number',
        'caller_name',
        'is_reminder_call',
        'parent_call_id',
        'call_type_id',
        'call_sub_type_id',
        'details',
        'language_id',
        'is_phone_activity',
        'vehicle_type_id',
        'vehicle_no',
        'carriageway_id',
        'km_marker_text',
        'office_id',
        'caller_lat',
        'caller_lng',
        'status',
        'priority',
        'forwarded_to_level',
        'forwarded_to_user_id',
        'rating',
        'call_start_time',
        'call_pickup_time',
        'call_end_time',
        'wait_time_seconds',
        'agent_call_duration',
        'response_time_sec',
        'resolution_time_sec',
        'pending_remarks',
        'pending_status_by',
        'inprogress_remarks',
        'inprogress_status_by',
        'inprogress_at',
        'completed_remarks',
        'completed_status_by',
        'completed_at',
        'cancelled_remarks',
        'followup_needed',
        'call_reminder_count',
        'last_reminder_at',
        'location_details',
    ];

    protected $casts = [
        'is_reminder_call' => 'boolean',
        'is_phone_activity' => 'boolean',
        'followup_needed' => 'boolean',
        'call_start_time' => 'datetime',
        'call_pickup_time' => 'datetime',
        'call_end_time' => 'datetime',
        'inprogress_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_reminder_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function callType(): BelongsTo
    {
        return $this->belongsTo(CallType::class);
    }

    public function callSubType(): BelongsTo
    {
        return $this->belongsTo(CallSubType::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function carriageway(): BelongsTo
    {
        return $this->belongsTo(Carriageway::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }


    public function statusHistory(): HasMany
    {
        return $this->hasMany(CallStatusHistory::class);
    }

    public function outgoingCalls(): HasMany
    {
        return $this->hasMany(OutgoingCall::class);
    }
}
