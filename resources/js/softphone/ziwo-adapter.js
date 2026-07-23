// Thin fetch wrapper for /telephony/* + SDK event bridge.
// Integrates window.ziwoCoreFront.ZiwoClient to handle real WebRTC signaling,
// connection establishment, and media rendering.

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

const jsonHeaders = () => ({
  'Content-Type': 'application/json',
  'X-CSRF-TOKEN': csrf(),
  'Accept': 'application/json',
});

async function post(baseUrl, path, body) {
  try {
    const res = await fetch(baseUrl + path, {
      method: 'POST',
      headers: jsonHeaders(),
      body: JSON.stringify(body || {}),
    });
    return await res.json();
  } catch (e) {
    console.warn('[softphone-adapter] post', path, 'failed', e);
    return { status: 'error', message: e?.message || 'network error' };
  }
}

async function getJson(baseUrl, path) {
  try {
    const res = await fetch(baseUrl + path, { headers: { Accept: 'application/json' } });
    return await res.json();
  } catch (e) {
    return null;
  }
}

export function createAdapter({ baseUrl = '/telephony' } = {}) {
  let sdkClient = null;
  const activeCalls = {};
  let currentUsername = null;

  // Watch for SDK events to keep the activeCalls map populated
  const bindCallTracker = () => {
    const kinds = ['ringing', 'requesting', 'trying', 'early', 'active', 'held', 'unheld', 'mute', 'unmute'];
    kinds.forEach(k => {
      window.addEventListener('ziwo-' + k, (e) => {
        const d = e?.detail || {};
        const call = d.call || d.currentCall || null;
        const callId = d.callID || d.primaryCallID || call?.callId || call?.id || null;
        if (call && callId) {
          activeCalls[callId] = call;
        }
      });
    });

    const destroyKinds = ['hangup', 'destroy'];
    destroyKinds.forEach(k => {
      window.addEventListener('ziwo-' + k, (e) => {
        const d = e?.detail || {};
        const call = d.call || d.currentCall || null;
        const callId = d.callID || d.primaryCallID || call?.callId || call?.id || null;
        if (callId) {
          delete activeCalls[callId];
        }
      });
    });
  };

  bindCallTracker();

  const getCallInstance = (callId) => {
    if (callId && activeCalls[callId]) return activeCalls[callId];
    // Fallback: get first active call
    const calls = Object.values(activeCalls);
    if (calls.length > 0) return calls[0];
    // Fallback: check sdkClient active properties
    if (sdkClient) {
      return sdkClient.currentCall || sdkClient.call || sdkClient.activeCall || null;
    }
    return null;
  };

  return {
    // auth
    authenticate: (username, password) => post(baseUrl, '/authenticate', { username, password }),
    status:       () => getJson(baseUrl, '/status'),
    
    disconnect:   async () => {
      // Clean up SDK client
      if (sdkClient) {
        try {
          Object.values(activeCalls).forEach(c => {
            try { c.hangup(); } catch(_) {}
          });
          sdkClient.disconnect();
        } catch(_) {}
        sdkClient = null;
      }
      currentUsername = null;
      return post(baseUrl, '/disconnect', {});
    },

    // single-call: WebRTC via SDK (primary), backend for call logging/routing only
    dial: async (number) => {
      console.log('[softphone-adapter] Dialing:', number);
      // WebRTC call via SDK - this is the primary path
      if (sdkClient) {
        try {
          console.log('[softphone-adapter] Placing WebRTC dial via Ziwo Client...');
          sdkClient.startCall(number);
          // Don't also POST to backend /dial — the SDK handles the actual call
          // Backend is only needed for SIP-only fallback or call logging
          return { status: 'success', via: 'webrtc' };
        } catch (err) {
          console.error('[softphone-adapter] WebRTC startCall failed, falling back to API:', err);
        }
      }
      // Fallback: SIP-only backend dial
      return post(baseUrl, '/dial', { phone_number: number });
    },

    answer: async (callId) => {
      console.log('[softphone-adapter] Answering:', callId);
      const call = getCallInstance(callId);
      if (call && typeof call.answer === 'function') {
        try { call.answer(); } catch(e) { console.error('[softphone-adapter] answer WebRTC call failed:', e); }
      }
      return post(baseUrl, '/answer', { call_id: callId });
    },

    hangup: async (callId) => {
      console.log('[softphone-adapter] Hanging up:', callId);
      const call = getCallInstance(callId);
      if (call) {
        try {
          if (typeof call.hangup === 'function') call.hangup();
          else if (typeof call.reject === 'function') call.reject();
        } catch(e) {
          console.error('[softphone-adapter] WebRTC hangup failed:', e);
        }
      }
      return post(baseUrl, '/hangup', { call_id: callId });
    },

    reject: async (callId) => {
      console.log('[softphone-adapter] Rejecting (inbound decline):', callId);
      const call = getCallInstance(callId);
      if (call) {
        try {
          if (typeof call.reject === 'function') call.reject();
          else if (typeof call.hangup === 'function') call.hangup();
        } catch(e) {
          console.error('[softphone-adapter] WebRTC reject failed:', e);
        }
      }
      // Also tell backend
      return post(baseUrl, '/hangup', { call_id: callId });
    },

    hold: async (callId) => {
      console.log('[softphone-adapter] Holding:', callId);
      if (sdkClient && typeof sdkClient.holdActiveCall === 'function') {
        try { sdkClient.holdActiveCall(); } catch(_) {}
      } else {
        const call = getCallInstance(callId);
        if (call && typeof call.hold === 'function') {
          try { call.hold(); } catch(_) {}
        }
      }
      return post(baseUrl, '/hold', { call_id: callId });
    },

    unhold: async (callId) => {
      console.log('[softphone-adapter] Resuming:', callId);
      if (sdkClient && typeof sdkClient.unholdActiveCall === 'function') {
        try { sdkClient.unholdActiveCall(); } catch(_) {}
      } else {
        const call = getCallInstance(callId);
        if (call && typeof call.unhold === 'function') {
          try { call.unhold(); } catch(_) {}
        }
      }
      return post(baseUrl, '/resume', { call_id: callId });
    },

    mute: async (callId) => {
      console.log('[softphone-adapter] Muting:', callId);
      // Try SDK-level mute first (most reliable)
      if (sdkClient) {
        try {
          if (typeof sdkClient.muteActiveCall === 'function') { sdkClient.muteActiveCall(); }
          else if (typeof sdkClient.mute === 'function') { sdkClient.mute(); }
        } catch(_) {}
      }
      // Also try call-level mute
      const call = getCallInstance(callId);
      if (call && typeof call.mute === 'function') {
        try { call.mute(); } catch(_) {}
      }
      // Final fallback: mute the microphone track directly
      try {
        const streams = Object.values(activeCalls).map(c => c.stream || c.localStream).filter(Boolean);
        streams.forEach(stream => stream.getAudioTracks().forEach(t => { t.enabled = false; }));
      } catch(_) {}
      return post(baseUrl, '/mute', { call_id: callId });
    },

    unmute: async (callId) => {
      console.log('[softphone-adapter] Unmuting:', callId);
      if (sdkClient) {
        try {
          if (typeof sdkClient.unmuteActiveCall === 'function') { sdkClient.unmuteActiveCall(); }
          else if (typeof sdkClient.unmute === 'function') { sdkClient.unmute(); }
        } catch(_) {}
      }
      const call = getCallInstance(callId);
      if (call && typeof call.unmute === 'function') {
        try { call.unmute(); } catch(_) {}
      }
      // Re-enable microphone track
      try {
        const streams = Object.values(activeCalls).map(c => c.stream || c.localStream).filter(Boolean);
        streams.forEach(stream => stream.getAudioTracks().forEach(t => { t.enabled = true; }));
      } catch(_) {}
      return post(baseUrl, '/unmute', { call_id: callId });
    },

    dtmf: (callId, digit) => {
      const call = getCallInstance(callId);
      if (call && typeof call.sendDtmf === 'function') {
        try { call.sendDtmf(digit); } catch(_) {}
      }
      return post(baseUrl, '/dtmf', { call_id: callId, digit });
    },

    // ── Transfer ──────────────────────────────────────────────────────────
    // ZIWO SDK: call.transfer(number) sends SIP REFER to PBX for blind transfer.
    // After transfer, PBX handles the redirect — agent leg disconnects.
    blindTransfer: async (callId, number) => {
      console.log('[softphone-adapter] Blind transfer:', callId, '->', number);
      const call = getCallInstance(callId);
      if (call) {
        try {
          // Try call-level transfer (SIP REFER) — correct WebRTC path
          if (typeof call.transfer === 'function') {
            call.transfer(number);
            console.log('[softphone-adapter] Call.transfer() sent via WebRTC SDK');
            // Also notify backend for logging
            post(baseUrl, '/transfer', { call_id: callId, target_number: number, type: 'blind' }).catch(() => {});
            return { status: 'success' };
          }
          // Fallback: sdkClient-level transfer
          if (typeof sdkClient?.transfer === 'function') {
            sdkClient.transfer(number);
            post(baseUrl, '/transfer', { call_id: callId, target_number: number, type: 'blind' }).catch(() => {});
            return { status: 'success' };
          }
        } catch(e) {
          console.warn('[softphone-adapter] WebRTC transfer failed, using backend:', e);
        }
      }
      // Final fallback: backend-only transfer (SIP proxy)
      return post(baseUrl, '/transfer', { call_id: callId, target_number: number, type: 'blind' });
    },

    attendedStart: async (callId, number) => {
      console.log('[softphone-adapter] Attended transfer start:', callId, '->', number);
      if (sdkClient) {
        try {
          if (typeof sdkClient.attendedTransfer === 'function') sdkClient.attendedTransfer(number);
          else if (typeof sdkClient.startCall === 'function') sdkClient.startCall(number); // consult leg
        } catch(e) { console.warn('[softphone-adapter] SDK attended transfer start failed:', e); }
      }
      return post(baseUrl, '/transfer', { call_id: callId, target_number: number, type: 'warm' });
    },
    attendedComplete: (callId) => {
      if (sdkClient && typeof sdkClient.completeTransfer === 'function') {
        try { sdkClient.completeTransfer(); } catch(_) {}
      }
      return post(baseUrl, '/transfer', { call_id: callId, type: 'proceed' });
    },
    attendedCancel: (callId) => {
      if (sdkClient && typeof sdkClient.cancelTransfer === 'function') {
        try { sdkClient.cancelTransfer(); } catch(_) {}
      }
      return post(baseUrl, '/transfer', { call_id: callId, type: 'cancel' });
    },

    // ── Conference ────────────────────────────────────────────────────────
    // ZIWO SDK conference: use call.conference(number) or sdkClient.conference(number).
    // The SDK initiates a 3-way bridge on the PBX — the active call is held
    // and the new participant is called. When they answer, all 3 are bridged.
    addParticipant: async (number, roomCallId) => {
      console.log('[softphone-adapter] Conference add participant:', number, 'room:', roomCallId);
      const call = getCallInstance(roomCallId);
      if (call) {
        try {
          // Preferred: call-level conference (hold current + dial new + bridge)
          if (typeof call.conference === 'function') {
            call.conference(number);
            console.log('[softphone-adapter] call.conference() sent');
            post(baseUrl, '/conference', { call_id: roomCallId, target_number: number }).catch(() => {});
            return { status: 'success' };
          }
        } catch(e) {
          console.warn('[softphone-adapter] call.conference() failed:', e);
        }
      }
      // Fallback: sdkClient-level
      if (sdkClient) {
        try {
          if (typeof sdkClient.conference === 'function') {
            sdkClient.conference(number);
            post(baseUrl, '/conference', { call_id: roomCallId, target_number: number }).catch(() => {});
            return { status: 'success' };
          }
          if (typeof sdkClient.addParticipant === 'function') {
            sdkClient.addParticipant(number);
            post(baseUrl, '/conference', { call_id: roomCallId, target_number: number }).catch(() => {});
            return { status: 'success' };
          }
        } catch(e) {
          console.warn('[softphone-adapter] sdkClient conference failed:', e);
        }
      }
      // Backend-only fallback
      return post(baseUrl, '/conference', { call_id: roomCallId, target_number: number });
    },
    removeParticipant: (callId) => post(baseUrl, '/disconnect', { call_id: callId }),
    leaveConference: (callId, roomId) => post(baseUrl, '/conference', { call_id: callId || roomId, target_number: '', action: 'leave', room_id: roomId }),

    // SDK event bridge
    onSdkEvent(cb) {
      const kinds = [
        'ready', 'ringing', 'invite', 'requesting', 'trying', 'early',
        'attach', 'answering', 'active', 'recovering',
        'hangup', 'destroy', 'held', 'unheld', 'mute', 'unmute',
        'connected', 'disconnected',
      ];
      const handler = (kind) => (e) => {
        const d = e?.detail || {};
        const call = d.call || d.currentCall || null;
        // Normalize direction: ZIWO SDK can use 'inbound'/'outbound', 'incoming', boolean `incoming`, etc.
        const rawDirection = call?.direction || d.direction;
        const direction = rawDirection
          || (call?.incoming === true || d.incoming === true ? 'inbound' : null)
          || (call?.isIncoming === true ? 'inbound' : null);

        cb({
          type: 'SDK_EVENT',
          kind,
          call: call ? {
            id:        call.callId || call.id,
            number:    call.phoneNumber || call.callerNumber || call.callerIdNumber || call.number || d.number || '',
            name:      call.callerIdName || call.displayName || call.name || d.name || '',
            direction,
            incoming:  call?.incoming === true || d.incoming === true,
          } : null,
          number:    d.number,
          name:      d.name,
          direction: d.direction,
          cause:     d.cause,
          sipCode:   d.sipCode,
          sipReason: d.sipReason,
        });
      };
      kinds.forEach((k) => window.addEventListener('ziwo-' + k, handler(k)));
      return () => kinds.forEach((k) => window.removeEventListener('ziwo-' + k, handler(k)));
    },

    // Status poll: intercepts poll status updates to bootstrap/ensure the WebRTC SDK Client is alive and logged in
    onStatusPoll(cb, intervalMs = 4000) {
      let alive = true;
      const tick = async () => {
        if (!alive) return;
        const s = await getJson(baseUrl, '/status');
        if (!s) return;

        cb(s);

        // Bootstrap/Connect the SDK Client when authenticated
        if (s.is_authenticated && s.ziwo_token && s.contact_center) {
          if (!sdkClient || currentUsername !== s.ziwo_username) {
            console.log('[softphone-adapter] Initializing Ziwo WebRTC SDK Client for username:', s.ziwo_username);
            currentUsername = s.ziwo_username;
            try {
              if (sdkClient) {
                try { sdkClient.disconnect(); } catch(_) {}
              }

              if (window.ziwoCoreFront) {
                sdkClient = new window.ziwoCoreFront.ZiwoClient({
                  contactCenterName: s.contact_center,
                  autoConnect: false,
                  credentials: {
                    authenticationToken: s.ziwo_token,
                  },
                  mediaTag: document.getElementById('ziwo-peer-audio'),
                });

                sdkClient.connect()
                  .then(() => console.log('[softphone-adapter] Ziwo SDK WebRTC client connected successfully ✓'))
                  .catch((err) => console.error('[softphone-adapter] Ziwo SDK WebRTC connection failed:', err));

                window.ziwoSdkClient = sdkClient;
              } else {
                console.error('[softphone-adapter] window.ziwoCoreFront not loaded.');
              }
            } catch (err) {
              console.error('[softphone-adapter] ZiwoClient instantiation error:', err);
            }
          }
        } else {
          // If we lose authentication, tear down the WebRTC client
          if (sdkClient) {
            console.log('[softphone-adapter] Telephony session expired. Tearing down SDK Client.');
            try { sdkClient.disconnect(); } catch(_) {}
            sdkClient = null;
            currentUsername = null;
          }
        }
      };
      const handle = setInterval(tick, intervalMs);
      tick();
      return () => { alive = false; clearInterval(handle); };
    },
  };
}
