// node --test happy-path for the softphone state machine.
// Run: node --test resources/js/softphone/state-machine.test.js
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { createMachine, STATES, EVENTS } from './state-machine.js';

const noopAdapter = () => ({
  dial: () => {}, answer: () => {}, hangup: () => {},
  hold: () => {}, unhold: () => {}, mute: () => {}, unmute: () => {},
  attendedStart: () => {}, attendedComplete: () => {}, attendedCancel: () => {},
  addParticipant: () => {}, removeParticipant: () => {}, leaveConference: () => {},
});

const newMachine = () => {
  const m = createMachine();
  m.attachAdapter(noopAdapter());
  return m;
};

test('AUTH_OK moves idle to ready', () => {
  const m = newMachine();
  m.send({ type: EVENTS.AUTH_OK });
  assert.equal(m.state, STATES.READY);
});

test('DIAL then REMOTE_ANSWERED transitions ready → dialing → inCall.active', () => {
  const m = newMachine();
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.DIAL, number: '+923001234567' });
  assert.equal(m.state, STATES.DIALING);
  assert.ok(m.context.activeCallId);
  m.send({ type: EVENTS.REMOTE_ANSWERED });
  assert.equal(m.state, STATES.IN_CALL_ACTIVE);
  assert.equal(m.matches('inCall'), true);
});

test('HOLD then UNHOLD toggles inCall.held', () => {
  const m = newMachine();
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.DIAL, number: '+923001234567' });
  m.send({ type: EVENTS.REMOTE_ANSWERED });
  m.send({ type: EVENTS.HOLD });
  assert.equal(m.state, STATES.IN_CALL_HELD);
  assert.equal(m.matches('inCall.held'), true);
  m.send({ type: EVENTS.UNHOLD });
  assert.equal(m.state, STATES.IN_CALL_ACTIVE);
});

test('ADD_PARTICIPANT moves inCall.active → conference.active and grows participants', () => {
  const m = newMachine();
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.DIAL, number: '+923001234567' });
  m.send({ type: EVENTS.REMOTE_ANSWERED });
  m.send({ type: EVENTS.ADD_PARTICIPANT, number: '+923009999999' });
  assert.equal(m.state, STATES.CONFERENCE_ACTIVE);
  assert.equal(m.context.participants.length, 1); // existing caller promoted
  m.send({
    type: EVENTS.PARTICIPANT_JOINED,
    participant: { id: 'p2', number: '+923009999999', name: 'B', isHeld: false, isMuted: false, joinedAt: Date.now() },
  });
  assert.equal(m.context.participants.length, 2);
});

test('HOLD_PARTICIPANT on conference with no other held → activeWithHeld', () => {
  const m = newMachine();
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.DIAL, number: '+923001234567' });
  m.send({ type: EVENTS.REMOTE_ANSWERED });
  m.send({ type: EVENTS.ADD_PARTICIPANT, number: '+923009999999' });
  m.send({
    type: EVENTS.PARTICIPANT_JOINED,
    participant: { id: 'p2', number: '+923009999999', name: 'B', isHeld: false, isMuted: false, joinedAt: Date.now() },
  });
  m.send({ type: EVENTS.HOLD_PARTICIPANT, participantId: 'p2' });
  assert.equal(m.state, STATES.CONFERENCE_HELD);
  assert.equal(m.context.participants.find((p) => p.id === 'p2').isHeld, true);
  m.send({ type: EVENTS.RESUME_PARTICIPANT, participantId: 'p2' });
  assert.equal(m.state, STATES.CONFERENCE_ACTIVE);
  assert.equal(m.context.participants.find((p) => p.id === 'p2').isHeld, false);
});

test('LEAVE_CONFERENCE returns to ready and clears participants', () => {
  const m = newMachine();
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.DIAL, number: '+923001234567' });
  m.send({ type: EVENTS.REMOTE_ANSWERED });
  m.send({ type: EVENTS.ADD_PARTICIPANT, number: '+923009999999' });
  m.send({ type: EVENTS.LEAVE_CONFERENCE });
  assert.equal(m.state, STATES.READY);
  assert.equal(m.context.participants.length, 0);
});

test('HANGUP_ALL from inCall.active returns to ready', () => {
  const m = newMachine();
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.DIAL, number: '+923001234567' });
  m.send({ type: EVENTS.REMOTE_ANSWERED });
  m.send({ type: EVENTS.HANGUP_ALL });
  assert.equal(m.state, STATES.READY);
  assert.equal(m.context.activeCallId, null);
});

test('INCOMING → ANSWER', () => {
  const m = newMachine();
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.INCOMING, callId: 'inb1', number: '+923001111111' });
  assert.equal(m.state, STATES.INCOMING);
  m.send({ type: EVENTS.ANSWER });
  assert.equal(m.state, STATES.IN_CALL_ACTIVE);
});

test('conference room id is locked on first ADD_PARTICIPANT and reused for N-way', async () => {
  // Bug that motivated the backend extension: every ADD_PARTICIPANT must
  // pass the SAME room id, otherwise the PBX creates a fresh 2-way each time.
  const calls = [];
  const capturingAdapter = () => ({
    dial: () => {}, answer: () => {}, hangup: () => {},
    hold: () => {}, unhold: () => {}, mute: () => {}, unmute: () => {},
    attendedStart: () => {}, attendedComplete: () => {}, attendedCancel: () => {},
    addParticipant: (number, roomId) => calls.push({ number, roomId }),
    removeParticipant: () => {}, leaveConference: () => {},
  });
  const m = createMachine();
  m.attachAdapter(capturingAdapter());

  const flush = () => new Promise((r) => queueMicrotask(() => queueMicrotask(r)));

  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.DIAL, number: '+923001234567' });
  m.send({ type: EVENTS.REMOTE_ANSWERED });

  // first add
  m.send({ type: EVENTS.ADD_PARTICIPANT, number: '+923009999991' });
  await flush();
  const firstRoom = m.context.conferenceRoomId;
  assert.ok(firstRoom, 'conferenceRoomId must be set on first add');
  assert.equal(calls[0].roomId, firstRoom);

  // second add: room id MUST be the same
  m.send({
    type: EVENTS.PARTICIPANT_JOINED,
    participant: { id: 'p2', number: '+923009999991', isHeld: false, isMuted: false, joinedAt: Date.now() },
  });
  m.send({ type: EVENTS.ADD_PARTICIPANT, number: '+923009999992' });
  await flush();
  assert.equal(calls[1].roomId, firstRoom, 'second add must reuse the same room id');

  // LEAVE_CONFERENCE clears the room id
  m.send({ type: EVENTS.LEAVE_CONFERENCE });
  assert.equal(m.context.conferenceRoomId, null);
});
