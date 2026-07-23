// Hand-rolled softphone state machine. Single source of truth for call state.
// UI subscribes via Alpine. No deps.
//
// States (dot-paths):
//   idle, ready, incoming, dialing, connecting,
//   inCall.active, inCall.held,
//   transfer.consulting, transfer.consultActive,
//   conference.active, conference.activeWithHeld
//
// INCOMING calls MUST use adapter.reject() not adapter.hangup() — ZIWO SDK
// hangup() on a still-ringing call is a no-op and the call keeps ringing.

export const STATES = {
  IDLE: 'idle',
  READY: 'ready',
  INCOMING: 'incoming',
  DIALING: 'dialing',
  CONNECTING: 'connecting',
  IN_CALL_ACTIVE: 'inCall.active',
  IN_CALL_HELD: 'inCall.held',
  TRANSFER_CONSULTING: 'transfer.consulting',
  TRANSFER_CONSULT_ACTIVE: 'transfer.consultActive',
  CONFERENCE_ACTIVE: 'conference.active',
  CONFERENCE_HELD: 'conference.activeWithHeld',
};

export const EVENTS = {
  AUTH_OK: 'AUTH_OK',
  AUTH_FAIL: 'AUTH_FAIL',
  INCOMING: 'INCOMING',
  ANSWER: 'ANSWER',
  REJECT: 'REJECT',
  DIAL: 'DIAL',
  REMOTE_ANSWERED: 'REMOTE_ANSWERED',
  REMOTE_HANGUP: 'REMOTE_HANGUP',
  HOLD: 'HOLD',
  UNHOLD: 'UNHOLD',
  MUTE: 'MUTE',
  UNMUTE: 'UNMUTE',
  START_TRANSFER: 'START_TRANSFER',
  CONSULT_ANSWERED: 'CONSULT_ANSWERED',
  CONSULT_FAILED: 'CONSULT_FAILED',
  COMPLETE_TRANSFER: 'COMPLETE_TRANSFER',
  CANCEL_TRANSFER: 'CANCEL_TRANSFER',
  ADD_PARTICIPANT: 'ADD_PARTICIPANT',
  BLIND_TRANSFER: 'BLIND_TRANSFER',
  PARTICIPANT_JOINED: 'PARTICIPANT_JOINED',
  PARTICIPANT_LEFT: 'PARTICIPANT_LEFT',
  HOLD_PARTICIPANT: 'HOLD_PARTICIPANT',
  RESUME_PARTICIPANT: 'RESUME_PARTICIPANT',
  REMOVE_PARTICIPANT: 'REMOVE_PARTICIPANT',
  MUTE_PARTICIPANT: 'MUTE_PARTICIPANT',
  UNMUTE_PARTICIPANT: 'UNMUTE_PARTICIPANT',
  LEAVE_CONFERENCE: 'LEAVE_CONFERENCE',
  HANGUP_ALL: 'HANGUP_ALL',
  SDK_EVENT: 'SDK_EVENT',
  ERROR: 'ERROR',
};

const initialContext = () => ({
  auth: null,
  activeCallId: null,
  conferenceRoomId: null,
  calls: {},
  participants: [],
  transfer: { consultCallId: null, originalCallId: null },
  error: null,
});

export function createMachine() {
  let state = STATES.IDLE;
  let ctx = initialContext();
  const listeners = new Set();
  const effects = [];

  const matches = (id) => state === id || state.startsWith(id + '.');
  const isInCall = () => matches('inCall');
  const isConference = () => matches('conference');
  const hasHeldParticipant = () => ctx.participants.some((p) => p.isHeld);

  const onConferenceEnter = () => {
    const id = ctx.activeCallId;
    if (!id) return;
    const call = ctx.calls[id];
    if (!call) return;
    if (!ctx.participants.find((p) => p.id === id)) {
      ctx.participants.push({
        id,
        number: call.number,
        name: call.name,
        isHeld: !!call.isHeld,
        isMuted: !!call.isMuted,
        joinedAt: Date.now(),
        isAgent: true,
      });
    }
  };

  const set = (next, mutator) => {
    if (mutator) mutator();
    state = next;
    for (const l of listeners) l(state, ctx);
    const queued = effects.splice(0);
    queueMicrotask(() => queued.forEach((fn) => fn()));
  };

  const resetCallRefs = () => {
    ctx.activeCallId = null;
    ctx.transfer = { consultCallId: null, originalCallId: null };
  };

  const transition = (event) => {
    let t = event.type;

    if (t === EVENTS.SDK_EVENT) {
      const kind = event.kind;
      const call = event.call;
      const callId = call?.id;

      if (kind === 'requesting' || kind === 'trying' || kind === 'early' || kind === 'active') {
        if (callId && ctx.activeCallId && ctx.activeCallId !== callId) {
          // Relink temporary call record to real SDK Call ID
          const oldId = ctx.activeCallId;
          if (ctx.calls[oldId]) {
            ctx.calls[callId] = { ...ctx.calls[oldId], id: callId };
            delete ctx.calls[oldId];
          } else {
            ctx.calls[callId] = {
              id: callId, number: call.number || '', name: call.name || '',
              direction: call.direction || 'outbound', isHeld: false, isMuted: false, startedAt: Date.now()
            };
          }
          ctx.activeCallId = callId;
        }
      }

      // Rewrite SDK event kind to standard machine event
      if (kind === 'active' || kind === 'answering') {
        t = EVENTS.REMOTE_ANSWERED;
      } else if (kind === 'ringing' || kind === 'invite' || kind === 'attach') {
        // Detect inbound vs outbound:
        // ZIWO SDK may not always include a `direction` field.
        // If we're in READY state (not dialing), treat any ringing as inbound.
        // If we're in DIALING state, it's the remote party ringing (outbound confirmation).
        const isExplicitlyInbound = call?.direction === 'inbound'
          || event.direction === 'inbound'
          || call?.incoming === true;
        const isExplicitlyOutbound = call?.direction === 'outbound'
          || state === STATES.DIALING || state === STATES.CONNECTING;

        if (isExplicitlyInbound || (!isExplicitlyOutbound && state === STATES.READY)) {
          t = EVENTS.INCOMING;
          event.callId = callId;
          // Pull caller number from any possible ZIWO SDK field
          event.number = call?.number
            || call?.callerNumber
            || call?.callerIdNumber
            || call?.phoneNumber
            || event.number || '';
          event.name = call?.name
            || call?.callerIdName
            || call?.displayName
            || event.name || '';
        }
        // If it's outbound ringing we just drop it (DIALING state already shows the screen)
      } else if (kind === 'hangup' || kind === 'destroy') {
        t = EVENTS.REMOTE_HANGUP;
      } else if (kind === 'held') {
        t = EVENTS.HOLD;
        event._fromSdk = true; // SDK confirms hold — guard against feedback loops
      } else if (kind === 'unheld') {
        t = EVENTS.UNHOLD;
        event._fromSdk = true; // SDK confirms unhold — guard against feedback loops
      } else if (kind === 'mute') {
        // SDK confirms mute — just update state flag, do NOT re-trigger adapter.mute()
        t = EVENTS.MUTE;
        event._fromSdk = true;
      } else if (kind === 'unmute') {
        // SDK confirms unmute — just update state flag, do NOT re-trigger adapter.unmute()
        t = EVENTS.UNMUTE;
        event._fromSdk = true;
      } else if (kind === 'connected') {
        // SDK connected = WebRTC session established; if in dialing, treat as answered
        if (state === STATES.DIALING || state === STATES.CONNECTING) {
          t = EVENTS.REMOTE_ANSWERED;
        }
      }
    }

    // Global: AUTH_FAIL from any state resets to idle
    if (t === EVENTS.AUTH_FAIL && state !== STATES.IDLE) {
      return set(STATES.IDLE, () => {
        ctx = initialContext();
      });
    }

    switch (state) {
      case STATES.IDLE: {
        if (t === EVENTS.AUTH_OK) return set(STATES.READY, () => { ctx.auth = event.auth || true; });
        if (t === EVENTS.AUTH_FAIL) return; // already idle
        if (t === EVENTS.ERROR) return set(STATES.IDLE, () => { ctx.error = event.msg; });
        break;
      }

      case STATES.READY: {
        if (t === EVENTS.INCOMING) {
          return set(STATES.INCOMING, () => {
            const callId = event.callId || ('in_' + Date.now());
            ctx.calls[callId] = {
              id: callId, number: event.number || '', name: event.name || '',
              direction: 'inbound', isHeld: false, isMuted: false, startedAt: Date.now(),
            };
            ctx.activeCallId = callId;
          });
        }
        if (t === EVENTS.DIAL) {
          return set(STATES.DIALING, () => {
            const callId = 'out_' + Date.now();
            ctx.calls[callId] = {
              id: callId, number: event.number, name: event.number,
              direction: 'outbound', isHeld: false, isMuted: false, startedAt: Date.now(),
            };
            ctx.activeCallId = callId;
            effects.push(() => ctx._adapter?.dial?.(event.number));
          });
        }
        break;
      }

      case STATES.INCOMING: {
        if (t === EVENTS.ANSWER) {
          return set(STATES.IN_CALL_ACTIVE, () => {
            effects.push(() => ctx._adapter?.answer?.(ctx.activeCallId));
          });
        }
        if (t === EVENTS.REJECT || t === EVENTS.REMOTE_HANGUP || t === EVENTS.HANGUP_ALL) {
          return set(STATES.READY, () => {
            const id = ctx.activeCallId;
            // reject() for a still-ringing call, hangup() for answered/ambiguous
            effects.push(() => {
              try { ctx._adapter?.reject?.(id); } catch(_) {}
              try { ctx._adapter?.hangup?.(id); } catch(_) {}
            });
            delete ctx.calls[id];
            resetCallRefs();
          });
        }
        break;
      }

      case STATES.DIALING: {
        if (t === EVENTS.REMOTE_ANSWERED) return set(STATES.IN_CALL_ACTIVE);
        if (t === EVENTS.REMOTE_HANGUP || t === EVENTS.REJECT || t === EVENTS.CANCEL_TRANSFER || t === EVENTS.HANGUP_ALL) {
          return set(STATES.READY, () => {
            const id = ctx.activeCallId;
            effects.push(() => ctx._adapter?.hangup?.(id));
            delete ctx.calls[id];
            resetCallRefs();
          });
        }
        break;
      }

      case STATES.IN_CALL_ACTIVE: {
        if (t === EVENTS.HOLD) {
          return set(STATES.IN_CALL_HELD, () => {
            if (ctx.activeCallId) ctx.calls[ctx.activeCallId].isHeld = true;
            if (!event._fromSdk) effects.push(() => ctx._adapter?.hold?.(ctx.activeCallId));
          });
        }
        if (t === EVENTS.REMOTE_HANGUP) {
          return set(STATES.READY, () => {
            const id = ctx.activeCallId;
            delete ctx.calls[id];
            resetCallRefs();
          });
        }
        if (t === EVENTS.START_TRANSFER) {
          return set(STATES.TRANSFER_CONSULTING, () => {
            const originalCallId = ctx.activeCallId;
            const consultCallId = 'tf_' + Date.now();
            ctx.calls[consultCallId] = {
              id: consultCallId, number: event.number, name: event.number,
              direction: 'outbound', isHeld: false, isMuted: false, startedAt: Date.now(),
            };
            ctx.transfer = { consultCallId, originalCallId };
            if (ctx.calls[originalCallId]) ctx.calls[originalCallId].isHeld = true;
            effects.push(() => ctx._adapter?.attendedStart?.(originalCallId, event.number));
          });
        }
        if (t === EVENTS.BLIND_TRANSFER) {
          return set(STATES.READY, () => {
            const id = ctx.activeCallId;
            const targetNumber = event.number; // transfer target number (NOT event.callId)
            effects.push(() => ctx._adapter?.blindTransfer?.(id, targetNumber));
            delete ctx.calls[id];
            resetCallRefs();
          });
        }
        if (t === EVENTS.ADD_PARTICIPANT) {
          return set(STATES.CONFERENCE_ACTIVE, () => {
            onConferenceEnter();
            if (!ctx.conferenceRoomId) ctx.conferenceRoomId = ctx.activeCallId;
            ctx.conferenceDialingNumber = event.number || null;
            effects.push(() => ctx._adapter?.addParticipant?.(event.number, ctx.conferenceRoomId));
          });
        }
        if (t === EVENTS.MUTE) {
          if (ctx.activeCallId) ctx.calls[ctx.activeCallId].isMuted = true;
          // Only call adapter.mute() for user-initiated events, not SDK confirmations
          if (!event._fromSdk) effects.push(() => ctx._adapter?.mute?.(ctx.activeCallId));
          return set(STATES.IN_CALL_ACTIVE);
        }
        if (t === EVENTS.UNMUTE) {
          if (ctx.activeCallId) ctx.calls[ctx.activeCallId].isMuted = false;
          if (!event._fromSdk) effects.push(() => ctx._adapter?.unmute?.(ctx.activeCallId));
          return set(STATES.IN_CALL_ACTIVE);
        }
        if (t === EVENTS.HANGUP_ALL) {
          return set(STATES.READY, () => {
            const id = ctx.activeCallId;
            effects.push(() => ctx._adapter?.hangup?.(id));
            delete ctx.calls[id];
            resetCallRefs();
          });
        }
        break;
      }

      case STATES.IN_CALL_HELD: {
        if (t === EVENTS.UNHOLD) {
          return set(STATES.IN_CALL_ACTIVE, () => {
            if (ctx.activeCallId) ctx.calls[ctx.activeCallId].isHeld = false;
            if (!event._fromSdk) effects.push(() => ctx._adapter?.unhold?.(ctx.activeCallId));
          });
        }
        if (t === EVENTS.MUTE) {
          if (ctx.activeCallId) ctx.calls[ctx.activeCallId].isMuted = true;
          if (!event._fromSdk) effects.push(() => ctx._adapter?.mute?.(ctx.activeCallId));
          return set(STATES.IN_CALL_HELD);
        }
        if (t === EVENTS.UNMUTE) {
          if (ctx.activeCallId) ctx.calls[ctx.activeCallId].isMuted = false;
          if (!event._fromSdk) effects.push(() => ctx._adapter?.unmute?.(ctx.activeCallId));
          return set(STATES.IN_CALL_HELD);
        }
        if (t === EVENTS.ADD_PARTICIPANT) {
          return set(STATES.CONFERENCE_ACTIVE, () => {
            onConferenceEnter();
            if (!ctx.conferenceRoomId) ctx.conferenceRoomId = ctx.activeCallId;
            ctx.conferenceDialingNumber = event.number || null;
            effects.push(() => ctx._adapter?.addParticipant?.(event.number, ctx.conferenceRoomId));
          });
        }
        if (t === EVENTS.HANGUP_ALL) {
          return set(STATES.READY, () => {
            const id = ctx.activeCallId;
            effects.push(() => ctx._adapter?.hangup?.(id));
            delete ctx.calls[id];
            resetCallRefs();
          });
        }
        break;
      }

      case STATES.TRANSFER_CONSULTING: {
        if (t === EVENTS.CONSULT_ANSWERED) return set(STATES.TRANSFER_CONSULT_ACTIVE);
        if (t === EVENTS.CONSULT_FAILED || t === EVENTS.CANCEL_TRANSFER) {
          return set(STATES.IN_CALL_ACTIVE, () => {
            const { originalCallId, consultCallId } = ctx.transfer;
            effects.push(() => ctx._adapter?.attendedCancel?.());
            delete ctx.calls[consultCallId];
            if (ctx.calls[originalCallId]) ctx.calls[originalCallId].isHeld = false;
            ctx.activeCallId = originalCallId;
            ctx.transfer = { consultCallId: null, originalCallId: null };
          });
        }
        break;
      }

      case STATES.TRANSFER_CONSULT_ACTIVE: {
        if (t === EVENTS.COMPLETE_TRANSFER) {
          return set(STATES.READY, () => {
            effects.push(() => ctx._adapter?.attendedComplete?.());
            ctx.calls = {};
            resetCallRefs();
          });
        }
        if (t === EVENTS.CANCEL_TRANSFER) {
          return set(STATES.IN_CALL_ACTIVE, () => {
            const { originalCallId, consultCallId } = ctx.transfer;
            effects.push(() => ctx._adapter?.attendedCancel?.());
            delete ctx.calls[consultCallId];
            if (ctx.calls[originalCallId]) ctx.calls[originalCallId].isHeld = false;
            ctx.activeCallId = originalCallId;
            ctx.transfer = { consultCallId: null, originalCallId: null };
          });
        }
        break;
      }

      case STATES.CONFERENCE_ACTIVE:
      case STATES.CONFERENCE_HELD: {
        if (t === EVENTS.PARTICIPANT_JOINED) {
          return set(hasHeldParticipant() ? STATES.CONFERENCE_HELD : STATES.CONFERENCE_ACTIVE, () => {
            if (!ctx.participants.find((p) => p.id === event.participant.id)) {
              ctx.participants.push(event.participant);
            }
            ctx.conferenceDialingNumber = null; // clear dialing indicator
          });
        }
        if (t === EVENTS.PARTICIPANT_LEFT || t === EVENTS.REMOVE_PARTICIPANT) {
          const id = event.participantId || event.participant?.id;
          ctx.participants = ctx.participants.filter((p) => p.id !== id);
          effects.push(() => ctx._adapter?.removeParticipant?.(id));
          if (ctx.participants.length === 0) {
            return set(STATES.READY, () => {
              ctx.calls = {};
              ctx.conferenceRoomId = null;
              resetCallRefs();
            });
          }
          return set(hasHeldParticipant() ? STATES.CONFERENCE_HELD : STATES.CONFERENCE_ACTIVE);
        }
        if (t === EVENTS.HOLD_PARTICIPANT) {
          const p = ctx.participants.find((x) => x.id === event.participantId);
          if (p) p.isHeld = true;
          effects.push(() => ctx._adapter?.hold?.(event.participantId));
          return set(STATES.CONFERENCE_HELD);
        }
        if (t === EVENTS.RESUME_PARTICIPANT) {
          const p = ctx.participants.find((x) => x.id === event.participantId);
          if (p) p.isHeld = false;
          effects.push(() => ctx._adapter?.unhold?.(event.participantId));
          return set(hasHeldParticipant() ? STATES.CONFERENCE_HELD : STATES.CONFERENCE_ACTIVE);
        }
        if (t === EVENTS.MUTE_PARTICIPANT) {
          const p = ctx.participants.find((x) => x.id === event.participantId);
          if (p) p.isMuted = true;
          effects.push(() => ctx._adapter?.mute?.(event.participantId));
          return set(state);
        }
        if (t === EVENTS.UNMUTE_PARTICIPANT) {
          const p = ctx.participants.find((x) => x.id === event.participantId);
          if (p) p.isMuted = false;
          effects.push(() => ctx._adapter?.unmute?.(event.participantId));
          return set(state);
        }
        if (t === EVENTS.ADD_PARTICIPANT) {
          if (!ctx.conferenceRoomId) ctx.conferenceRoomId = ctx.activeCallId;
          ctx.conferenceDialingNumber = event.number || null;
          effects.push(() => ctx._adapter?.addParticipant?.(event.number, ctx.conferenceRoomId));
          return set(state);
        }
        if (t === EVENTS.LEAVE_CONFERENCE) {
          effects.push(() => ctx._adapter?.leaveConference?.());
          ctx.participants = [];
          ctx.calls = {};
          ctx.conferenceRoomId = null;
          return set(STATES.READY, () => resetCallRefs());
        }
        if (t === EVENTS.HANGUP_ALL) {
          ctx.participants.forEach((p) => effects.push(() => ctx._adapter?.hangup?.(p.id)));
          ctx.participants = [];
          ctx.calls = {};
          ctx.conferenceRoomId = null;
          return set(STATES.READY, () => resetCallRefs());
        }
        break;
      }
    }

    if (typeof console !== 'undefined' && console.debug) {
      console.debug('[softphone] dropped event', t, 'in state', state);
    }
  };

  return {
    get state() { return state; },
    get context() { return ctx; },
    matches,
    isInCall,
    isConference,
    attachAdapter: (adapter) => { ctx._adapter = adapter; },
    send: (event) => transition(event),
    subscribe: (l) => { listeners.add(l); return () => listeners.delete(l); },
  };
}
