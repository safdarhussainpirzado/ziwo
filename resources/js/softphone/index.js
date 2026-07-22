// Registers the softphone Alpine component + Alpine.store('softphone').
// The fixed panel uses <div x-data="softphone"> and the intakeComponent
// reads/writes Alpine.store('softphone') for cross-component state sharing.
//
// State flow:
//   machine (state-machine.js) ──► Alpine.store('softphone') ──► intakeComponent reads
//   intakeComponent actions ──► Alpine.store('softphone').send() ──► machine ──► adapter

import Alpine from 'alpinejs';
import { createMachine, EVENTS } from './state-machine.js';
import { createAdapter } from './ziwo-adapter.js';

// ─── Shared store (readable from any Alpine component incl. intakeComponent) ──
// Initialized here; updated by machine.subscribe() below.
Alpine.store('softphone', {
  state:    'idle',
  context:  {},
  // Convenience booleans for partials
  authenticated: false,
  status:   'offline',     // 'offline'|'online'|'ringing_inbound'|'ringing_outbound'|'active'|'held'
  currentCall: {
    id: null, caller_number: '', caller_name: '',
    is_held: false, is_muted: false, recording_paused: false,
    duration: 0, direction: null,
  },
  // Expose send so intakeComponent can drive the machine
  send: null,
});

// ─── Alpine component (the fixed softphone panel) ──────────────────────────
Alpine.data('softphone', () => {
  const machine = createMachine();
  const adapter = createAdapter({ baseUrl: '/telephony' });
  machine.attachAdapter(adapter);

  // Map machine state → legacy phoneStatus strings so partials work unchanged
  function machineStateToStatus(state, ctx) {
    if (state === 'idle')                     return 'offline';
    if (state === 'ready')                    return 'online';
    if (state === 'incoming')                 return 'ringing_inbound';
    if (state === 'dialing')                  return 'ringing_outbound';
    if (state === 'connecting')               return 'ringing_outbound';
    if (state === 'inCall.active')            return 'active';
    if (state === 'inCall.held')              return 'held';
    if (state === 'transferConsulting')       return 'transfer_consulting';
    if (state === 'conferenceActive')         return 'conference';
    if (state === 'conferenceHeld')           return 'conference';
    if (state.startsWith('transfer'))         return 'active';
    if (state.startsWith('conference'))       return 'conference';
    return 'online';
  }

  // Map machine context → legacy currentCall shape
  function ctxToCurrentCall(ctx) {
    const id = ctx.activeCallId;
    const call = id && ctx.calls ? ctx.calls[id] : null;
    let name = call?.name || '';

    if (call?.number && (!name || name === call.number)) {
      try {
        const num = call.number.replace(/\D/g, '');
        const el = document.querySelector('[x-data]');
        if (el && window.Alpine) {
          const data = window.Alpine.store('softphone') ? el.__x?.$data : null;
          const contacts = data?.phonebookContacts || [];
          const contact = contacts.find(c => {
            const cn = (c.phone_number || c.phone || '').replace(/\D/g, '');
            return cn && (cn.endsWith(num) || num.endsWith(cn));
          });
          if (contact) name = contact.name;
        }
      } catch(_) {}
    }

    return {
      id:               call?.id    || null,
      caller_number:    call?.number || '',
      caller_name:      name || 'Unknown',
      is_held:          call?.isHeld  || false,
      is_muted:         call?.isMuted || false,
      recording_paused: false,
      duration:         0,
      direction:        call?.direction || null,
    };
  }

  // Sync machine state into Alpine.store so intakeComponent can read it
  function syncStore(state, ctx) {
    const store = Alpine.store('softphone');
    store.state       = state;
    store.context     = ctx;
    store.status      = machineStateToStatus(state, ctx);
    store.authenticated = state !== 'idle';
    store.currentCall = ctxToCurrentCall(ctx);
  }

  // Forward SDK window events into the machine
  const offSdk = adapter.onSdkEvent((evt) => machine.send(evt));

  // Poll the status endpoint; translate into machine events
  const offPoll = adapter.onStatusPoll((s) => {
    if (!s) return;
    if (s.is_authenticated && machine.matches('idle')) {
      machine.send({ type: EVENTS.AUTH_OK, auth: { username: s.ziwo_username } });
    }
    if (!s.is_authenticated && !machine.matches('idle')) {
      machine.send({ type: EVENTS.AUTH_FAIL });
    }
    if (s.active_call) {
      const ac = s.active_call;
      if (ac.status === 'ringing_inbound' && !machine.matches('incoming')) {
        machine.send({ type: EVENTS.INCOMING, callId: ac.id, number: ac.caller_number, name: ac.caller_name });
      } else if (ac.status === 'active' && machine.matches('dialing')) {
        machine.send({ type: EVENTS.REMOTE_ANSWERED });
      } else if (ac.status === 'held' && machine.matches('inCall.active')) {
        machine.send({ type: EVENTS.HOLD });
      }
    }
  });

  // Expose send globally on the store so intakeComponent proxies work
  Alpine.store('softphone').send = (event) => machine.send(event);

  return {
    // Reactive state (re-set by subscribe)
    state:   machine.state,
    context: machine.context,

    // Expose EVENTS constant for inline markup
    EVENTS,

    matches:       machine.matches,
    send:          (ev) => machine.send(ev),

    init() {
      machine.subscribe((state, ctx) => {
        this.state   = state;
        this.context = ctx;
        syncStore(state, ctx);
        // Notify intakeComponent immediately so it doesn't need to wait for the poll
        window.dispatchEvent(new CustomEvent('softphone:statechange', { detail: { state, status: Alpine.store('softphone').status } }));
      });
      // Initial sync
      syncStore(machine.state, machine.context);
      Alpine.store('softphone').send = (ev) => machine.send(ev);

      // Listen for the cross-component quick-dial event
      window.addEventListener('softphone:dial', (e) => {
        const { number } = e.detail || {};
        if (number) machine.send({ type: EVENTS.DIAL, number });
      });
    },
    destroy() { offSdk?.(); offPoll?.(); },

    // ── Convenience action shortcuts for markup ──────────────────────
    dial:            (n)   => machine.send({ type: EVENTS.DIAL,           number: n }),
    answer:          ()    => machine.send({ type: EVENTS.ANSWER }),
    reject:          ()    => machine.send({ type: EVENTS.REJECT }),
    hold:            ()    => machine.send({ type: EVENTS.HOLD }),
    unhold:          ()    => machine.send({ type: EVENTS.UNHOLD }),
    hangupAll:       ()    => machine.send({ type: EVENTS.HANGUP_ALL }),
    addParticipant:  (n)   => machine.send({ type: EVENTS.ADD_PARTICIPANT, number: n }),
    leaveConference: ()    => machine.send({ type: EVENTS.LEAVE_CONFERENCE }),
    holdParticipant:   (id) => machine.send({ type: EVENTS.HOLD_PARTICIPANT,   participantId: id }),
    resumeParticipant: (id) => machine.send({ type: EVENTS.RESUME_PARTICIPANT, participantId: id }),
    removeParticipant: (id) => machine.send({ type: EVENTS.REMOVE_PARTICIPANT, participantId: id }),
    startTransfer:   (n)   => machine.send({ type: EVENTS.START_TRANSFER,  number: n }),
    completeTransfer:()    => machine.send({ type: EVENTS.COMPLETE_TRANSFER }),
    cancelTransfer:  ()    => machine.send({ type: EVENTS.CANCEL_TRANSFER }),

    // State guards for markup
    isInCall:           () => machine.isInCall(),
    isConference:       () => machine.isConference(),
    inReady:            () => machine.matches('ready'),
    inIdle:             () => machine.matches('idle'),
    inIncoming:         () => machine.matches('incoming'),
    inDialing:          () => machine.matches('dialing'),
    inConnecting:       () => machine.matches('connecting'),
    inCallActive:       () => machine.matches('inCall.active'),
    inCallHeld:         () => machine.matches('inCall.held'),
    inTransferring:     () => machine.matches('transfer.consulting') || machine.matches('transfer.consultActive'),
    inConferenceActive: () => machine.matches('conference.active'),
    inConferenceHeld:   () => machine.matches('conference.activeWithHeld'),
    inAnyCall:          () => machine.isInCall() || machine.isConference()
                            || machine.matches('incoming') || machine.matches('dialing'),
  };
});
