{{--
  Softphone partial. State-driven via x-data="softphone".
  Replaces the inline softphone markup in calls/create.blade.php.
  Include with: @include('partials.softphone')

  Uses state.matches('inCall.active') etc. (NOT phoneStatus === '...').
  Sends events via dial(), hold(), addParticipant(), etc. (NOT direct fetch).
--}}
<div x-data="softphone" x-init="init()" @beforeunload.window="destroy()"
     class="w-80 h-full bg-slate-950 border-l border-slate-900 shadow-2xl flex flex-col overflow-hidden text-slate-100">

  {{-- ───────── Header ───────── --}}
  <div class="p-3 bg-slate-900 border-b border-slate-800/80 flex justify-between items-center shrink-0">
    <div class="flex items-center gap-2.5">
      <span class="relative flex h-2.5 w-2.5">
        <span x-show="inCallActive() || inConferenceActive()" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2.5 w-2.5"
              :class="{
                'bg-emerald-500': inReady() || inCallActive() || inConferenceActive(),
                'bg-rose-500':   inIncoming() || inDialing(),
                'bg-amber-500':  inCallHeld() || inConferenceHeld(),
                'bg-slate-500':  inIdle()
              }"></span>
      </span>
      <div>
        <span class="text-[10px] font-black uppercase tracking-wider text-slate-300" x-text="state"></span>
      </div>
    </div>
  </div>

  {{-- ───────── Idle / Login ───────── --}}
  <div x-show="inIdle()" class="p-4">
    <p class="text-xs text-slate-400">Not authenticated.</p>
  </div>

  {{-- ───────── Ready / Dialer ───────── --}}
  <div x-show="inReady()" class="p-4 space-y-3">
    <input type="text" x-model="dialNumber" placeholder="Enter number to dial..."
           class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-600 outline-none focus:border-indigo-500">
    <button type="button" @click="dial(dialNumber)" :disabled="!dialNumber"
            class="w-full py-2 rounded-xl bg-emerald-500 text-white text-xs font-bold disabled:opacity-50">
      Call
    </button>
  </div>

  {{-- ───────── Incoming ───────── --}}
  <div x-show="inIncoming()" class="p-4 space-y-2">
    <p class="text-xs text-slate-300">Incoming call</p>
    <div class="flex gap-2">
      <button type="button" @click="answer()" class="flex-1 py-2 rounded-xl bg-emerald-500 text-white text-xs font-bold">Answer</button>
      <button type="button" @click="reject()" class="flex-1 py-2 rounded-xl bg-rose-500 text-white text-xs font-bold">Reject</button>
    </div>
  </div>

  {{-- ───────── Dialing ───────── --}}
  <div x-show="inDialing()" class="p-4 space-y-2">
    <p class="text-xs text-slate-300">Dialing…</p>
    <button type="button" @click="hangupAll()" class="w-full py-2 rounded-xl bg-rose-500 text-white text-xs font-bold">Cancel</button>
  </div>

  {{-- ───────── In Call (1:1) ───────── --}}
  <div x-show="inCallActive() || inCallHeld()" class="p-4 space-y-3">
    <p class="text-[10px] uppercase tracking-wider text-slate-400" x-text="state"></p>
    <div class="flex gap-2">
      <button x-show="inCallActive()"  @click="hold()"  class="flex-1 py-2 rounded-xl bg-amber-500 text-white text-xs font-bold">Hold</button>
      <button x-show="inCallHeld()"    @click="unhold()" class="flex-1 py-2 rounded-xl bg-emerald-500 text-white text-xs font-bold">Resume</button>
      <button @click="addParticipant(dialNumber, 'new')" class="flex-1 py-2 rounded-xl bg-indigo-500 text-white text-xs font-bold">Add</button>
      <button @click="hangupAll()" class="flex-1 py-2 rounded-xl bg-rose-500 text-white text-xs font-bold">Hangup</button>
    </div>
  </div>

  {{-- ───────── Conference (N-way) ───────── --}}
  <div x-show="inConferenceActive() || inConferenceHeld()" class="p-4 space-y-3">
    <p class="text-[10px] uppercase tracking-wider text-emerald-300">
      Conference · <span x-text="context.participants.length"></span> participants
    </p>
    <ul class="space-y-2">
      <template x-for="p in context.participants" :key="p.id">
        <li class="flex items-center gap-2 px-2 py-1.5 rounded-lg bg-slate-900/60 border border-slate-800">
          <span class="flex-1 text-xs text-white" x-text="p.name || p.number"></span>
          <span x-show="p.isHeld" class="text-[9px] text-amber-400 font-bold uppercase">Held</span>
          <button @click="p.isHeld ? resumeParticipant(p.id) : holdParticipant(p.id)"
                  class="px-2 py-0.5 rounded text-[10px] font-bold"
                  :class="p.isHeld ? 'bg-emerald-500' : 'bg-amber-500'" x-text="p.isHeld ? 'Resume' : 'Hold'"></button>
          <button @click="removeParticipant(p.id)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500">Kick</button>
        </li>
      </template>
    </ul>
    <div class="flex gap-2 pt-2 border-t border-slate-800">
      <input type="text" x-model="dialNumber" placeholder="Add number…"
             class="flex-1 px-2 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-xs text-white placeholder-slate-600 outline-none focus:border-indigo-500">
      <button @click="addParticipant(dialNumber, 'new')" :disabled="!dialNumber"
              class="px-3 py-1.5 rounded-lg bg-indigo-500 text-white text-xs font-bold disabled:opacity-50">Add</button>
    </div>
    <div class="flex gap-2">
      <button @click="leaveConference()" class="flex-1 py-2 rounded-xl bg-amber-500 text-white text-xs font-bold">Leave conference</button>
      <button @click="hangupAll()" class="flex-1 py-2 rounded-xl bg-rose-500 text-white text-xs font-bold">End all</button>
    </div>
  </div>
</div>
