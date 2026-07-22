// Smoke + live-flow tests for the softphone modules.
// Run: node --test resources/js/softphone/load.test.js
//
// Tests do not need Alpine or a browser — they import the modules and exercise
// the state machine + fetch adapter with mocked globalThis.fetch. Each
// fetch-mock test saves and restores the original fetch in a try/finally.
import { test } from 'node:test';
import assert from 'node:assert/strict';

// Stub a minimal `document` for the adapter's CSRF lookup. The adapter reads
// `document.querySelector('meta[name="csrf-token"]')`; in node that throws.
// The tests don't care about the CSRF value — they just need the code path
// not to throw before fetch is called.
if (typeof globalThis.document === 'undefined') {
  globalThis.document = { querySelector: () => null };
}

// ── Module exports ─────────────────────────────────────────────

test('state-machine exports createMachine, STATES, EVENTS', async () => {
  const m = await import('./state-machine.js');
  assert.equal(typeof m.createMachine, 'function');
  assert.ok(m.STATES);
  assert.ok(m.EVENTS);
  assert.equal(m.STATES.IDLE, 'idle');
  assert.equal(m.EVENTS.DIAL, 'DIAL');
});

test('ziwo-adapter exports createAdapter and is fully wired', async () => {
  const a = await import('./ziwo-adapter.js');
  assert.equal(typeof a.createAdapter, 'function');
  const adapter = a.createAdapter({ baseUrl: '/telephony' });
  for (const fn of [
    'dial', 'answer', 'hangup', 'hold', 'unhold', 'mute', 'unmute', 'dtmf',
    'blindTransfer', 'attendedStart', 'attendedComplete', 'attendedCancel',
    'addParticipant', 'removeParticipant', 'leaveConference',
    'onSdkEvent', 'onStatusPoll',
    'authenticate', 'status', 'disconnect',
  ]) {
    assert.equal(typeof adapter[fn], 'function', `adapter.${fn} missing`);
  }
});

// ── State machine happy path ───────────────────────────────────

const stubAdapter = () => ({
  dial: () => {}, answer: () => {}, hangup: () => {},
  hold: () => {}, unhold: () => {}, mute: () => {}, unmute: () => {},
  attendedStart: () => {}, attendedComplete: () => {}, attendedCancel: () => {},
  addParticipant: () => {}, removeParticipant: () => {}, leaveConference: () => {},
});

test('state machine: idle → auth → dial → answer → inCall.active', async () => {
  const { createMachine, STATES, EVENTS } = await import('./state-machine.js');
  const m = createMachine();
  m.attachAdapter(stubAdapter());
  assert.equal(m.state, STATES.IDLE);
  m.send({ type: EVENTS.AUTH_OK });
  assert.equal(m.state, STATES.READY);
  m.send({ type: EVENTS.DIAL, number: '+923001234567' });
  assert.equal(m.state, STATES.DIALING);
  m.send({ type: EVENTS.REMOTE_ANSWERED });
  assert.equal(m.state, STATES.IN_CALL_ACTIVE);
});

test('state machine: hold toggles between active and held', async () => {
  const { createMachine, STATES, EVENTS } = await import('./state-machine.js');
  const m = createMachine();
  m.attachAdapter(stubAdapter());
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.DIAL, number: '+923001234567' });
  m.send({ type: EVENTS.REMOTE_ANSWERED });
  m.send({ type: EVENTS.HOLD });
  assert.equal(m.state, STATES.IN_CALL_HELD);
  m.send({ type: EVENTS.UNHOLD });
  assert.equal(m.state, STATES.IN_CALL_ACTIVE);
});

test('state machine: incoming ring transitions ready → incoming → inCall on answer', async () => {
  const { createMachine, STATES, EVENTS } = await import('./state-machine.js');
  const m = createMachine();
  m.attachAdapter(stubAdapter());
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.INCOMING, number: '03001234567' });
  assert.equal(m.state, STATES.INCOMING);
  m.send({ type: EVENTS.ANSWER });
  assert.equal(m.state, STATES.IN_CALL_ACTIVE);
});

test('state machine: ATTENDED transfer round-trip', async () => {
  const { createMachine, STATES, EVENTS } = await import('./state-machine.js');
  const m = createMachine();
  m.attachAdapter(stubAdapter());
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.DIAL, number: '+923001234567' });
  m.send({ type: EVENTS.REMOTE_ANSWERED });
  m.send({ type: EVENTS.START_TRANSFER, number: '+923009876543' });
  assert.equal(m.state, STATES.TRANSFER_CONSULTING);
  m.send({ type: EVENTS.CONSULT_ANSWERED });
  assert.equal(m.state, STATES.TRANSFER_CONSULT_ACTIVE);
  m.send({ type: EVENTS.COMPLETE_TRANSFER });
  // After complete transfer the original call ends; state should settle
  assert.ok([STATES.READY, STATES.IDLE, STATES.IN_CALL_ACTIVE].includes(m.state));
});

test('state machine: conference addParticipant keeps roomId stable for N-way', async () => {
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
  m.send({ type: EVENTS.ADD_PARTICIPANT, number: '+923009999992' });
  await new Promise((r) => queueMicrotask(() => queueMicrotask(r)));
  assert.equal(calls.length, 2);
  // Same room id on every add so the PBX keeps merging into the same room.
  assert.equal(calls[0].roomId, m.context.conferenceRoomId);
  assert.equal(calls[1].roomId, m.context.conferenceRoomId);
  assert.equal(calls[0].roomId, calls[1].roomId);
});

// ── Adapter contract ──────────────────────────────────────────

test('adapter.addParticipant posts to /conference with room_id and call_id', async () => {
  const { createAdapter } = await import('./ziwo-adapter.js');
  const captured = [];
  const real = globalThis.fetch;
  globalThis.fetch = async (url, opts) => {
    captured.push({ url, opts });
    return { json: async () => ({ status: 'success', call_id: 'conf_1' }) };
  };
  try {
    const adapter = createAdapter({ baseUrl: '/telephony' });
    const res = await adapter.addParticipant('+923001234567', 'room-abc');
    assert.equal(captured.length, 1);
    assert.equal(captured[0].url, '/telephony/conference');
    const body = JSON.parse(captured[0].opts.body);
    assert.equal(body.number, '+923001234567');
    assert.equal(body.room_id, 'room-abc');
    assert.equal(body.call_id, 'room-abc');
    assert.equal(res.status, 'success');
  } finally {
    globalThis.fetch = real;
  }
});

test('adapter.dtmf sends digit in payload', async () => {
  const { createAdapter } = await import('./ziwo-adapter.js');
  const captured = [];
  const real = globalThis.fetch;
  globalThis.fetch = async (url, opts) => {
    captured.push({ url, body: JSON.parse(opts.body) });
    return { json: async () => ({ status: 'success' }) };
  };
  try {
    const adapter = createAdapter({ baseUrl: '/telephony' });
    await adapter.dtmf('call_42', '5');
    assert.equal(captured[0].url, '/telephony/dtmf');
    assert.equal(captured[0].body.call_id, 'call_42');
    assert.equal(captured[0].body.digit, '5');
  } finally {
    globalThis.fetch = real;
  }
});

test('adapter.authenticate/status/disconnect hit correct endpoints', async () => {
  const { createAdapter } = await import('./ziwo-adapter.js');
  const captured = [];
  const real = globalThis.fetch;
  globalThis.fetch = async (url, opts) => {
    captured.push({ url, method: opts?.method || 'GET', body: opts?.body ? JSON.parse(opts.body) : null });
    return { json: async () => ({ status: 'success', access_token: 'tok_x' }) };
  };
  try {
    const adapter = createAdapter({ baseUrl: '/telephony' });
    const auth = await adapter.authenticate('user@example.com', 'pw');
    assert.equal(auth.status, 'success');
    assert.equal(captured[0].url, '/telephony/authenticate');
    assert.equal(captured[0].method, 'POST');
    assert.equal(captured[0].body.username, 'user@example.com');

    const s = await adapter.status();
    assert.equal(s.status, 'success');
    assert.equal(captured[1].url, '/telephony/status');

    const d = await adapter.disconnect();
    assert.equal(d.status, 'success');
    assert.equal(captured[2].url, '/telephony/disconnect');
  } finally {
    globalThis.fetch = real;
  }
});

test('adapter.transfer variants hit /transfer with correct type', async () => {
  const { createAdapter } = await import('./ziwo-adapter.js');
  const captured = [];
  const real = globalThis.fetch;
  globalThis.fetch = async (url, opts) => {
    captured.push({ url, body: JSON.parse(opts.body) });
    return { json: async () => ({ status: 'success' }) };
  };
  try {
    const adapter = createAdapter({ baseUrl: '/telephony' });
    await adapter.blindTransfer('c1', '+923001');
    await adapter.attendedStart('c1', '+923002');
    await adapter.attendedComplete();
    await adapter.attendedCancel();
    assert.equal(captured[0].body.type, 'blind');
    assert.equal(captured[1].body.type, 'attended');
    assert.equal(captured[2].body.type, 'proceed');
    assert.equal(captured[3].body.type, 'cancel');
  } finally {
    globalThis.fetch = real;
  }
});

test('adapter.fetch failure is reported as error object, never throws', async () => {
  const { createAdapter } = await import('./ziwo-adapter.js');
  const real = globalThis.fetch;
  globalThis.fetch = async () => { throw new Error('network unreachable'); };
  try {
    const adapter = createAdapter({ baseUrl: '/telephony' });
    const r = await adapter.dial('+923001234567');
    assert.equal(r.status, 'error');
    // Adapter swallows the original error message and uses 'network error' as fallback
    assert.ok(r.message === 'network error' || r.message.includes('network unreachable'));
  } finally {
    globalThis.fetch = real;
  }
});

// ── Live-flow simulation: full inbound answer cycle ───────────

test('live flow simulation: incoming → answer → hold → unhold → remote hangup', async () => {
  const { createMachine, STATES, EVENTS } = await import('./state-machine.js');
  const m = createMachine();
  m.attachAdapter(stubAdapter());
  m.send({ type: EVENTS.AUTH_OK });
  m.send({ type: EVENTS.INCOMING, number: '03001234567' });
  m.send({ type: EVENTS.ANSWER });
  m.send({ type: EVENTS.HOLD });
  m.send({ type: EVENTS.UNHOLD });
  m.send({ type: EVENTS.REMOTE_HANGUP });
  // After remote hangup from inCall, state should return to READY (not IDLE — we still have auth)
  assert.equal(m.state, STATES.READY);
});
