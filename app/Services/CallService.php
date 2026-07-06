<?php

namespace App\Services;

use App\Events\CallLogged; 
use App\Models\Call;
use App\Models\CallStatusHistory;
use App\Models\User;
use App\Models\CallType;
use App\Models\CallSubType;
use Illuminate\Support\Facades\DB;

class CallService
{
    /**
     * Create a new call with advanced priority resolution.
     */
    public function createCall(array $data, User $agent): Call
    {
        return DB::transaction(function () use ($data, $agent) {
            $data['agent_id'] = $agent->id;
            $data['call_start_time'] = now();

            // Determine if this is a Junk/Silent or Information call for automated completion
            $type = null;
            if (isset($data['call_type_id'])) {
                $type = CallType::find($data['call_type_id']);
            }
            $isAutomated = $type && in_array($type->category, ['junk_silent', 'information']);

            $initialStatus = $isAutomated ? 'completed' : 'pending';
            $data['status'] = $initialStatus;

            if ($isAutomated) {
                $data['completed_at'] = now();
                $data['completed_status_by'] = $agent->id;
                $data['completed_remarks'] = 'Automated completion for ' . $type->category . ' call';
                $data['resolution_time_sec'] = 0;
            }

            // Resolve priority from call type/sub type
            if (!isset($data['priority'])) {
                $data['priority'] = $this->resolvePriority($data);
            }

            // Atomic sequential call number — prevents race-condition duplicates
            // lockForUpdate holds a row lock within this transaction so concurrent
            // agents always get unique sequential numbers.
            if (!isset($data['call_number'])) {
                $year = (int) date('Y');
                // Use MAX(id) or a more robust count that doesn't rely on valid created_at
                $lastCall = DB::table('calls')
                    ->where('call_number', 'like', "CRM-{$year}-%")
                    ->lockForUpdate()
                    ->orderBy('id', 'desc')
                    ->first();

                $nextSeq = 1;
                if ($lastCall) {
                    $parts = explode('-', $lastCall->call_number);
                    $nextSeq = (int) end($parts) + 1;
                }

                $data['call_number'] = sprintf('CRM-%d-%05d', $year, $nextSeq);
            }

            $call = Call::create($data);

            // Record initial status
            CallStatusHistory::create([
                'call_id' => $call->id,
                'old_status' => null,
                'new_status' => $initialStatus,
                'changed_by' => $agent->id,
                'remarks' => $isAutomated ? 'Automated completion for ' . $type->category . ' call' : 'Help inserted',
            ]);

            // Fire events
            event(new CallLogged($call->call_number, $call->caller_number, $call->call_type_id, $call->priority));

            return $call;
        });
    }

    /**
     * Update call status with metrics calculation.
     */
    public function updateStatus(Call $call, string $newStatus, User $changedBy, array $data = []): Call
    {
        $validTransitions = [
            'pending' => ['in_progress', 'cancelled', 'junk', 'forwarded'],
            'in_progress' => ['completed', 'pending', 'forwarded'],
            'forwarded' => ['in_progress', 'completed'],
        ];

        $allowed = $validTransitions[$call->status] ?? [];

        if (!in_array($newStatus, $allowed)) {
            throw new \InvalidArgumentException("Invalid state transition: {$call->status} -> {$newStatus}");
        }

        return DB::transaction(function () use ($call, $newStatus, $changedBy, $data) {
            $oldStatus = $call->status;
            $updateData = ['status' => $newStatus];

            if ($newStatus === 'in_progress') {
                $updateData['inprogress_at'] = now();
                $updateData['inprogress_status_by'] = $changedBy->id;
                $updateData['inprogress_remarks'] = $data['remarks'] ?? null;

                if ($call->call_start_time) {
                    $updateData['response_time_sec'] = (int) $call->call_start_time->diffInSeconds(now());
                }

                if (isset($data['tiger_id'])) {
                    $assetCode = $data['tiger_id'];
                    $updateData['inprogress_remarks'] = ($updateData['inprogress_remarks'] ? $updateData['inprogress_remarks'] . " " : "") . "[Allocated Asset: {$assetCode}]";
                    $data['remarks'] = ($data['remarks'] ?? '') . " [Asset: {$assetCode}]";
                }
                if (isset($data['patrol_officer_id'])) {
                    $officerInfo = $data['patrol_officer_id'];
                    $updateData['inprogress_remarks'] = ($updateData['inprogress_remarks'] ? $updateData['inprogress_remarks'] . " " : "") . "[Patrol Officer: {$officerInfo}]";
                    $data['remarks'] = ($data['remarks'] ?? '') . " [Officer: {$officerInfo}]";
                }
            }

            if ($newStatus === 'completed') {
                $updateData['completed_at'] = now();
                $updateData['completed_status_by'] = $changedBy->id;
                $updateData['completed_remarks'] = $data['remarks'] ?? null;
                $updateData['followup_needed'] = $data['followup_needed'] ?? false;

                if ($call->call_start_time) {
                    $updateData['resolution_time_sec'] = (int) $call->call_start_time->diffInSeconds(now());
                }
            }

            $call->update($updateData);

            CallStatusHistory::create([
                'call_id' => $call->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $changedBy->id,
                'remarks' => $data['remarks'] ?? null,
            ]);

            return $call->fresh();
        });
    }

    public function sendReminder(Call $call, User $user): Call
    {
        return DB::transaction(function () use ($call, $user) {
            $call->increment('call_reminder_count');
            $call->update([
                'last_reminder_at' => now(),
                'is_reminder_call' => true,
            ]);

            // Record this trigger in status history for audit
            CallStatusHistory::create([
                'call_id'    => $call->id,
                'old_status' => $call->status,
                'new_status' => $call->status, // Status remains the same
                'changed_by' => $user->id,
                'remarks'    => "Call reminder #{$call->call_reminder_count} sent to field units.",
            ]);

            // Re-fire event to trigger notifications via polling/broadcast
            event(new CallLogged($call->call_number, $call->caller_number, $call->call_type_id, $call->priority));

            return $call->fresh();
        });
    }

    private function resolvePriority(array $data): int
    {
        if (isset($data['call_sub_type_id'])) {
            $subType = CallSubType::find($data['call_sub_type_id']);
            if ($subType) return $subType->priority;
        }

        if (isset($data['call_type_id'])) {
            $type = CallType::find($data['call_type_id']);
            if ($type) return $type->priority;
        }

        return 3;
    }
}
