<?php

namespace App\Http\Controllers;

use App\Telephony\Contracts\TelephonyServiceInterface;
use App\Telephony\Contracts\TelephonyRepositoryInterface;
use App\Telephony\DTOs\ContactDTO;
use App\Telephony\DTOs\CallLogDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class TelephonyController extends Controller
{
    public function __construct(
        protected TelephonyServiceInterface $service,
        protected TelephonyRepositoryInterface $repository
    ) {}

    /**
     * Authenticate the active agent with ZIWO APIs.
     */
    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $result = $this->service->authenticateAgent(
            auth()->id(),
            $request->input('username'),
            $request->input('password')
        );

        return response()->json($result);
    }

    /**
     * Get active agent configuration and status.
     */
    public function getStatus()
    {
        $config = $this->repository->getAgentConfig(auth()->id());
        if (!$config) {
            return response()->json([
                'status' => 'offline',
                'ziwo_username' => null,
                'is_authenticated' => false
            ]);
        }

        return response()->json([
            'status' => $config->agent_status,
            'ziwo_username' => $config->ziwo_username,
            'is_authenticated' => !empty($config->ziwo_token),
            'expires_at' => $config->expires_at?->toIso8601String()
        ]);
    }

    /**
     * Get recent calls for authenticated agent.
     */
    public function recentCalls()
    {
        $calls = $this->repository->getRecentCalls(auth()->id());
        return response()->json($calls);
    }

    /**
     * Answer incoming call.
     */
    public function answer(Request $request)
    {
        $request->validate(['call_id' => 'required|string']);
        
        $call = $this->repository->getCallByZiwoId($request->input('call_id'));
        if ($call) {
            $dto = new CallLogDTO(
                callId: $call->call_id,
                callUuid: $call->call_uuid,
                agentId: auth()->id(),
                callerNumber: $call->caller_number,
                direction: $call->direction,
                status: 'active',
                startTime: $call->start_time ?? now(),
                metadata: $call->metadata
            );
            $this->repository->logCall($dto);
        }
        
        return response()->json(['status' => 'success', 'message' => 'Call marked as answered']);
    }

    /**
     * Disconnect the agent from ZIWO telephony.
     */
    public function disconnect()
    {
        $result = $this->service->disconnectAgent(auth()->id());
        return response()->json(['status' => $result ? 'success' : 'error']);
    }

    /**
     * Dial an outbound phone number.
     */
    public function dial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $result = $this->service->dialOutbound(auth()->id(), $request->input('phone_number'));
        return response()->json($result);
    }

    /**
     * Hold active call.
     */
    public function hold(Request $request)
    {
        $request->validate(['call_id' => 'required|string']);
        $result = $this->service->hold(auth()->id(), $request->input('call_id'));
        return response()->json($result);
    }

    /**
     * Resume held call.
     */
    public function resume(Request $request)
    {
        $request->validate(['call_id' => 'required|string']);
        $result = $this->service->resume(auth()->id(), $request->input('call_id'));
        return response()->json($result);
    }

    /**
     * Mute microphone.
     */
    public function mute(Request $request)
    {
        $request->validate(['call_id' => 'required|string']);
        $result = $this->service->mute(auth()->id(), $request->input('call_id'));
        return response()->json($result);
    }

    /**
     * Unmute microphone.
     */
    public function unmute(Request $request)
    {
        $request->validate(['call_id' => 'required|string']);
        $result = $this->service->unmute(auth()->id(), $request->input('call_id'));
        return response()->json($result);
    }

    /**
     * Hang up active call.
     */
    public function hangup(Request $request)
    {
        $request->validate(['call_id' => 'required|string']);
        $result = $this->service->hangup(auth()->id(), $request->input('call_id'));
        return response()->json($result);
    }

    /**
     * Transfer call.
     */
    public function transfer(Request $request)
    {
        $request->validate([
            'call_id' => 'required|string',
            'target_number' => 'required|string',
            'type' => 'nullable|string|in:blind,warm'
        ]);

        $result = $this->service->transfer(
            auth()->id(),
            $request->input('call_id'),
            $request->input('target_number'),
            $request->input('type', 'blind')
        );
        return response()->json($result);
    }

    /**
     * Toggle active recording.
     */
    public function toggleRecording(Request $request)
    {
        $request->validate([
            'call_id' => 'required|string',
            'pause' => 'required|boolean'
        ]);

        $result = $this->service->toggleCallRecording(
            auth()->id(),
            $request->input('call_id'),
            $request->input('pause')
        );
        return response()->json($result);
    }

    /**
     * Search and retrieve phonebook contacts.
     */
    public function searchPhonebook(Request $request)
    {
        $query = $request->input('query', '');
        $category = $request->input('category');

        $contacts = $this->repository->searchContacts($query, $category, auth()->id());
        return response()->json(['status' => 'success', 'contacts' => $contacts]);
    }

    /**
     * Save a phonebook contact.
     */
    public function storePhonebook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'phone_number' => 'required|string|max:50',
            'category' => 'required|string|in:beat,sector,zone,emergency,custom',
            'is_favorite' => 'nullable|boolean',
            'metadata' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $dto = new ContactDTO(
            name: $request->input('name'),
            phoneNumber: $request->input('phone_number'),
            category: $request->input('category'),
            createdBy: auth()->id(),
            isFavorite: (bool)$request->input('is_favorite', false),
            metadata: $request->input('metadata', [])
        );

        $contact = $this->repository->saveContact($dto, $request->input('id'));
        return response()->json(['status' => 'success', 'contact' => $contact]);
    }

    /**
     * Delete a contact.
     */
    public function destroyPhonebook($id)
    {
        $contact = \App\Models\PhonebookContact::findOrFail($id);

        // Check ownership if category is custom
        if ($contact->category === 'custom' && $contact->created_by !== auth()->id() && !auth()->user()->hasPermission('super_admin')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized deletion request'], 403);
        }

        $result = $this->repository->deleteContact($id);
        return response()->json(['status' => $result ? 'success' : 'error']);
    }
}
