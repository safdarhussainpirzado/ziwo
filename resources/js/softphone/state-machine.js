// Hand-rolled softphone state machine. ~80 lines, no deps.
// Single source of truth for call/connection state. UI subscribes via Alpine.
//
// States (dot-paths):
//   idle, ready, incoming, dialing, connecting,
//   inCall.active, inCall.held,
//   transfer.consulting, transfer.consultActive,
//   conference.active, conference.activeWithHeld
//
// Events are plain { type, ...payload } objects. send() applies the matching
// transition; if no transition matches, the event is dropped (and warned).

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
  PARTICIPANT_JOINED: 'PARTIPANT_JOINED',
  PARTICIPANT_LEFT: 'PARTICIPANT_LEFT',
  HOLD_PARTICIPANT: 'HOLD_PARTICIPANT',
  RESUME_PARTICIPANT: 'RESUME_PARTICIPANT',
  REMOVE_PARTICIPANT: 'REMOVE_PARTICIPANT',
  LEAVE_CONFERENCE: 'LEAVE_CONFERENCE',
  HANGUP_ALL: 'HANGUP_ALL',
  SDK_EVENT: 'SDK_EVENT',
  ERROR: 'ERROR',
};

const initialContext = () => ({
  auth: null,
  activeCallId: null,
  conferenceRoomId: null,          // first call id of the conference; reused for every ADD_PARTICIPANT
  calls: {},                       // callId -> { id, number, name, direction, isHeld, isMuted, startedAt }
  participants: [],                // conference only: [{ id, number, name, isHeld, isMuted, joinedAt }]
  transfer: { consultCallId: null, originalCallId: null },
  error: null,
});

export function createMachine() {
  let state = STATES.IDLE;
  let ctx = initialContext();
  const listeners = new Set();
  const effects = []; // queued to fire after state mutation

  const matches = (id) =>
    state === id || state.startsWith(id + '.');

  const isInCall = () => matches('inCall');
  const isConference = () => matches('conference');
  const hasHeldParticipant = () => ctx.participants.some((p) => p.isHeld);

  const onConferenceEnter = () => {
    // promote the current call to the participants list if not already there
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

  const transition = (event) => {
    const t = event.type;
    const resetCallRefs = () => {
      ctx.activeCallId = null;
      ctx.transfer = { consultCallId: null, originalCallId: null };
    };

    switch (state) {
      case STATES.IDLE:
        if (t === EVENTS.AUTH_OK) return set(STATES.READY, () => { ctx.auth = event.auth || true; });
        if (t === EVENTS.ERROR)  return set(STATES.IDLE, () => { ctx.error = event.msg; });
        break;

      case STATES.READY:
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
        if (t === EVENTS.SDK_EVENT) {
          // SDK may deliver an inbound when the page is in ready
          if (event.payload?.kind === 'inbound') {
            return transition({ type: EVENTS.INCOMING, ...event.payload });
          }
        }
        break;

      case STATES.INCOMING:
        if (t === EVENTS.ANSWER) {
          return set(STATES.IN_CALL_ACTIVE, () => {
            effects.push(() => ctx._adapter?.answer?.(ctx.activeCallId));
          });
        }
        if (t === EVENTS.REJECT || t === EVENTS.REMOTE_HANGUP) {
          return set(STATES.READY, () => {
            const id = ctx.activeCallId;
            delete ctx.calls[id];
            resetCallRefs();
          });
        }
        break;

      case STATES.DIALING:
        if (t === EVENTS.REMOTE_ANSWERED) {
          return set(STATES.IN_CALL_ACTIVE);
        }
        if (t === EVENTS.REMOTE_HANGUP) {
          return set(STATES.READY, () => {
            const id = ctx.activeCallId;
            delete ctx.calls[id];
            resetCallRefs();
          });
        }
        break;

      case STATES.IN_CALL_ACTIVE:
        if (t === EVENTS.HOLD) {
          return set(STATES.IN_CALL_HELD, () => {
            if (ctx.activeCallId) ctx.calls[ctx.activeCallId].isHeld = true;
            effects.push(() => ctx._adapter?.hold?.(ctx.activeCallId));
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
            // original caller goes on hold
            if (ctx.calls[originalCallId]) ctx.calls[originalCallId].isHeld = true;
            effects.push(() => ctx._adapter?.attendedStart?.(originalCallId, event.number));
          });
        }
        if (t === EVENTS.ADD_PARTICIPANT) {
          return set(STATES.CONFERENCE_ACTIVE, () => {
            onConferenceEnter();
            // lock the room id the first time we enter conference; every subsequent
            // ADD_PARTICIPANT reuses it so the PBX keeps the same room alive.
            if (!ctx.conferenceRoomId) ctx.conferenceRoomId = ctx.activeCallId;
            const roomId = ctx.conferenceRoomId;
            effects.push(() => ctx._adapter?.addParticipant?.(event.number, roomId));
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

      case STATES.IN_CALL_HELD:
        if (t === EVENTS.UNHOLD) {
          return set(STATES.IN_CALL_ACTIVE, () => {
            if (ctx.activeCallId) ctx.calls[ctx.activeCallId].isHeld = false;
            effects.push(() => ctx._adapter?.unhold?.(ctx.activeCallId));
          });
        }
        if (t === EVENTS.ADD_PARTICIPANT) {
          return set(STATES.CONFERENCE_ACTIVE, () => {
            onConferenceEnter();
            effects.push(() => ctx._adapter?.addParticipant?.(event.number));
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

      case STATES.TRANSFER_CONSULTING:
        if (t === EVENTS.CONSULT_ANSWERED) return set(STATES.TRANSFER_CONSULT_ACTIVE);
        if (t === EVENTS.CONSULT_FAILED || t === EVENTS.CANCEL_TRANSFER) {
          return set(STATES.IN_CALL_ACTIVE, () => {
            const { originalCallId, consultCallId } = ctx.transfer;
            delete ctx.calls[consultCallId];
            if (ctx.calls[originalCallId]) ctx.calls[originalCallId].isHeld = false;
            ctx.activeCallId = originalCallId;
            ctx.transfer = { consultCallId: null, originalCallId: null };
            effects.push(() => ctx._adapter?.unhold?.(originalCallId));
          });
        }
        break;

      case STATES.TRANSFER_CONSULT_ACTIVE:
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

      case STATES.CONFERENCE_ACTIVE:
      case STATES.CONFERENCE_HELD:
        if (t === EVENTS.PARTICIPANT_JOINED) {
          return set(hasHeldParticipant() ? STATES.CONFERENCE_HELD : STATES.CONFERENCE_ACTIVE, () => {
            if (!ctx.participants.find((p) => p.id === event.participant.id)) {
              ctx.participants.push(event.participant);
            }
          });
        }
        if (t === EVENTS.PARTICIPANT_LEFT || t === EVENTS.REMOVE_PARTICIPANT) {
          return set(hasHeldParticipant() ? STATES.CONFERENCE_HELD : STATES.CONFERENCE_ACTIVE, () => {
            const id = event.participantId || event.participant?.id;
            ctx.participants = ctx.participants.filter((p) => p.id !== id);
            effects.push(() => ctx._adapter?.removeParticipant?.(id));
            if (ctx.participants.length === 0) {
              // last participant gone → back to ready
              state = STATES.READY;
              ctx.calls = {};
              resetCallRefs();
            }
          });
        }
        if (t === EVENTS.HOLD_PARTICIPANT) {
          return set(STATES.CONFERENCE_HELD, () => {
            const p = ctx.participants.find((x) => x.id === event.participantId);
            if (p) p.isHeld = true;
            effects.push(() => ctx._adapter?.hold?.(event.participantId));
          });
        }
        if (t === EVENTS.RESUME_PARTICIPANT) {
          return set(STATES.CONFERENCE_ACTIVE, () => {
            const p = ctx.participants.find((x) => x.id === event.participantId);
            if (p) p.isHeld = false;
            effects.push(() => ctx._adapter?.unhold?.(event.participantId));
          });
        }
        if (t === EVENTS.ADD_PARTICIPANT) {
          return set(hasHeldParticipant() ? STATES.CONFERENCE_HELD : STATES.CONFERENCE_ACTIVE, () => {
            if (!ctx.conferenceRoomId) ctx.conferenceRoomId = ctx.activeCallId;
            const roomId = ctx.conferenceRoomId;
            effects.push(() => ctx._adapter?.addParticipant?.(event.number, roomId));
          });
        }
        if (t === EVENTS.LEAVE_CONFERENCE) {
          return set(STATES.READY, () => {
            effects.push(() => ctx._adapter?.leaveConference?.());
            ctx.participants = [];
            ctx.calls = {};
            ctx.conferenceRoomId = null;
            resetCallRefs();
          });
        }
        if (t === EVENTS.HANGUP_ALL) {
          return set(STATES.READY, () => {
            ctx.participants.forEach((p) => effects.push(() => ctx._adapter?.hangup?.(p.id)));
            ctx.participants = [];
            ctx.calls = {};
            ctx.conferenceRoomId = null;
            resetCallRefs();
          });
        }
        break;
    }

    // unmapped: warn once per type per state to surface wiring mistakes
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
