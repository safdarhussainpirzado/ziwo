// Quick smoke test that the softphone state machine + adapter + Alpine
// component register without throwing. Run from any environment with
// `node --test resources/js/softphone/load.test.js`. Does NOT require Alpine
// in a browser; just imports the modules and asserts they exist & export.
import { test } from 'node:test';
import assert from 'node:assert/strict';

test('state-machine exports createMachine, STATES, EVENTS', async () => {
  const m = await import('./state-machine.js');
  assert.equal(typeof m.createMachine, 'function');
  assert.ok(m.STATES);
  assert.ok(m.EVENTS);
  assert.equal(m.STATES.IDLE, 'idle');
  assert.equal(m.EVENTS.DIAL, 'DIAL');
});

test('ziwo-adapter exports createAdapter', async () => {
  const a = await import('./ziwo-adapter.js');
  assert.equal(typeof a.createAdapter, 'function');
  const adapter = a.createAdapter({ baseUrl: '/telephony' });
  // every command the markup needs must exist
  for (const fn of ['dial', 'answer', 'hangup', 'hold', 'unhold', 'mute', 'unmute',
                    'blindTransfer', 'attendedStart', 'attendedComplete', 'attendedCancel',
                    'addParticipant', 'removeParticipant', 'leaveConference',
                    'onSdkEvent', 'onStatusPoll', 'authenticate', 'status', 'disconnect']) {
    assert.equal(typeof adapter[fn], 'function', `adapter.${fn} missing`);
  }
});

test('machine + adapter integration: ADD_PARTICIPANT sends through adapter with room id', async () => {
  const { createMachine, EVENTS } = await import('./state-machine.js');
  const calls = [];
  const fakeAdapter = {
    dial: () => {}, answer: () => {}, hangup: () => {},
    hold: () => {}, unhold: () => {}, mute: () => {}, unmute: () => {},
    attendedStart: () => {}, attendedComplete: () => {}, attendedCancel: () => {},
    addParticipant: (number, roomId) => calls.push({ number, roomId }),
    removeParticipant: () => {}, leaveConference: () => {},
  };
  const m = createMachine();
  m.attachAdapter(fakeAdapter);
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.DIAL, number: '+923001234567' });
  m.send({ type: EVENTS.REMOTE_ANSWERED });
  m.send({ type: EVENTS.ADD_PARTICIPANT, number: '+923009999991' });
  await new Promise((r) => queueMicrotask(() => queueMicrotask(r)));
  assert.equal(calls.length, 1);
  assert.equal(calls[0].roomId, m.context.conferenceRoomId);
});
