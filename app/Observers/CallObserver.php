<?php

namespace App\Observers;

use App\Models\Call;
use App\Models\CallStatusHistory;
use Illuminate\Support\Facades\Auth;

class CallObserver
{
    public function updated(Call $call): void
    {
        // History logging is handled centrally via CallService@updateStatus. 
        // This observer previously duplicated those log entries.
    }

    private function getRemarksForStatus(Call $call): ?string
    {
        return match ($call->status) {
            'pending' => $call->pending_remarks,
            'in_progress' => $call->inprogress_remarks,
            'completed' => $call->completed_remarks,
            'cancelled' => $call->cancelled_remarks,
            default => null,
        };
    }
}
