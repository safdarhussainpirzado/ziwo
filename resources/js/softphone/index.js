// Registers the softphone Alpine component. The partial Blade
// uses <div x-data="softphone"> and gets { state, context, matches, send, ...adapter }.
import Alpine from 'alpinejs';
import { createMachine, EVENTS } from './state-machine.js';
import { createAdapter } from './ziwo-adapter.js';

Alpine.data('softphone', () => {
  const machine = createMachine();
  const adapter = createAdapter({ baseUrl: '/telephony' });
  machine.attachAdapter(adapter);

  // forward SDK window events into the machine
  const offSdk = adapter.onSdkEvent((evt) => machine.send(evt));
  // poll the status endpoint; translate into machine events
  const offPoll = adapter.onStatusPoll((s) => {
    if (!s) return;
    if (s.active_call) {
      const ac = s.active_call;
      if (ac.status === 'ringing_inbound') machine.send({ type: EVENTS.INCOMING, callId: ac.id, number: ac.caller_number, name: ac.caller_name });
      else if (ac.status === 'active') machine.send({ type: EVENTS.REMOTE_ANSWERED });
      else if (ac.status === 'held') machine.send({ type: EVENTS.HOLD });
    } else if (machine.isInCall() || machine.isConference()) {
      machine.send({ type: EVENTS.REMOTE_HANGUP });
    }
  });

  return {
    state: machine.state,
    context: machine.context,
    matches: machine.matches,
    send: machine.send,
    init() {
      // re-render `state` getter on every machine change
      machine.subscribe(() => { this.state = machine.state; this.context = machine.context; });
    },
    destroy() { offSdk?.(); offPoll?.(); },

    // thin pass-throughs so the markup can call dial(), hangup(), etc. directly
    dial:           (n)     => machine.send({ type: EVENTS.DIAL, number: n }),
    answer:         ()      => machine.send({ type: EVENTS.ANSWER }),
    reject:         ()      => machine.send({ type: EVENTS.REJECT }),
    hold:           ()      => machine.send({ type: EVENTS.HOLD }),
    unhold:         ()      => machine.send({ type: EVENTS.UNHOLD }),
    hangupAll:      ()      => machine.send({ type: EVENTS.HANGUP_ALL }),
    addParticipant: (n, name) => machine.send({ type: EVENTS.ADD_PARTICIPANT, number: n, name }),
    leaveConference:()      => machine.send({ type: EVENTS.LEAVE_CONFERENCE }),
    holdParticipant:(id)    => machine.send({ type: EVENTS.HOLD_PARTICIPANT, participantId: id }),
    resumeParticipant:(id)  => machine.send({ type: EVENTS.RESUME_PARTICIPANT, participantId: id }),
    removeParticipant:(id)  => machine.send({ type: EVENTS.REMOVE_PARTICIPANT, participantId: id }),

    // exposed for markup convenience
    isInCall:      () => machine.isInCall(),
    isConference:  () => machine.isConference(),
    inReady:       () => machine.matches('ready'),
    inIdle:        () => machine.matches('idle'),
    inIncoming:    () => machine.matches('incoming'),
    inDialing:     () => machine.matches('dialing'),
    inConnecting:  () => machine.matches('connecting'),
    inCallActive:  () => machine.matches('inCall.active'),
    inCallHeld:    () => machine.matches('inCall.held'),
    inTransferring:() => machine.matches('transfer.consulting') || machine.matches('transfer.consultActive'),
    inConferenceActive: () => machine.matches('conference.active'),
    inConferenceHeld:   () => machine.matches('conference.activeWithHeld'),
    inAnyCall:     () => machine.isInCall() || machine.isConference() || machine.matches('incoming') || machine.matches('dialing'),
  };
});
