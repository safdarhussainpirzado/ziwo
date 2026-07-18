// Thin fetch wrapper for /telephony/* + SDK event bridge.
// No state. The machine owns state; this module only sends commands
// and forwards events back as { type, ...payload }.

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
  return {
    // auth
    authenticate: (username, password) => post(baseUrl, '/authenticate', { username, password }),
    status:       () => getJson(baseUrl, '/status'),
    disconnect:   () => post(baseUrl, '/disconnect', {}),

    // single-call
    dial:    (number)   => post(baseUrl, '/dial', { number }),
    answer:  (callId)   => post(baseUrl, '/answer', { call_id: callId }),
    hangup:  (callId)   => post(baseUrl, '/hangup', { call_id: callId }),
    hold:    (callId)   => post(baseUrl, '/hold', { call_id: callId }),
    unhold:  (callId)   => post(baseUrl, '/resume', { call_id: callId }),
    mute:    (callId)   => post(baseUrl, '/mute', { call_id: callId }),
    unmute:  (callId)   => post(baseUrl, '/unmute', { call_id: callId }),
    dtmf:    (callId, digit) => post(baseUrl, '/dtmf', { call_id: callId, digit }),

    // transfer
    blindTransfer:    (callId, number) => post(baseUrl, '/transfer', { call_id: callId, number, type: 'blind' }),
    attendedStart:    (callId, number) => post(baseUrl, '/transfer', { call_id: callId, number, type: 'attended' }),
    attendedComplete: ()                => post(baseUrl, '/transfer', { type: 'proceed' }),
    attendedCancel:   ()                => post(baseUrl, '/transfer', { type: 'cancel' }),

    // conference — `roomId` is the original call id of the conference room;
    // reuse the same value on every subsequent addParticipant so the PBX keeps
    // merging new parties into the same room (true N-way).
    addParticipant:    (number, roomId) => post(baseUrl, '/conference', { number, room_id: roomId, call_id: roomId }),
    removeParticipant: (callId)         => post(baseUrl, '/disconnect', { call_id: callId }),
    leaveConference:   ()               => post(baseUrl, '/conference', { action: 'leave' }),

    // SDK event bridge — wire window ziwo-* events (the actual prefix the
    // ziwo-core-front SDK dispatches, see _jorel-dialog-state- + ziwo- in
    // ziwo-core-front.umd.js) into a single callback. The earlier 'ziwo-call-'
    // prefix was wrong and never matched anything.
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
        cb({
          type: 'SDK_EVENT',
          kind,
          call: call ? {
            id: call.callId || call.id,
            number: call.phoneNumber || call.callerNumber || d.number,
            name: call.callerIdName || call.displayName || d.name || '',
            direction: call.direction || d.direction,
          } : null,
          number: d.number, name: d.name,
          // pass through cause/sipCode for hangup events so the state
          // machine can log them
          cause: d.cause, sipCode: d.sipCode, sipReason: d.sipReason,
        });
      };
      kinds.forEach((k) => window.addEventListener('ziwo-' + k, handler(k)));
      return () => kinds.forEach((k) => window.removeEventListener('ziwo-' + k, handler(k)));
    },

    // Status poll: caller wires setInterval(() => adapter.status().then(cb), 4000)
    // and forwards to machine as { type: 'POLL_UPDATE', payload }
    onStatusPoll(cb, intervalMs = 4000) {
      let alive = true;
      const tick = async () => {
        if (!alive) return;
        const s = await getJson(baseUrl, '/status');
        if (s) cb(s);
      };
      const handle = setInterval(tick, intervalMs);
      tick();
      return () => { alive = false; clearInterval(handle); };
    },
  };
}
