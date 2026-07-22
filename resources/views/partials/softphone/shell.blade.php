{{--
    Premium Softphone shell.
    Renders the fixed right-side panel. The Alpine state (`phoneStatus`,
    `phoneTab`, `currentCall`, etc.) lives on the parent `intakeComponent`
    so data flows across screens.

    Token palette (Aircall / Linear inspired):
      bg-panel      #0B1220   (panel base)
      bg-surface    #0F172A   (raised card)
      bg-elevated   #1E293B   (top-bar / inputs)
      text-1        #F1F5F9   (primary)
      text-2        #94A3B8   (secondary)
      text-3        #64748B   (muted)
      accent        #6366F1   (indigo-500)
      available     #10B981   (emerald)
      break         #F59E0B   (amber)
      meeting       #3B82F6   (blue)
      outgoing      #A78BFA   (violet)
      hangup        #EF4444   (rose)
--}}
<aside
    class="fixed inset-y-0 right-0 w-80 z-[55] bg-[#0B1220] text-slate-100 flex flex-col shadow-[-12px_0_40px_-12px_rgba(0,0,0,0.6)] font-sans"
    x-show="!phoneCollapsed"
    x-transition:enter="transition ease-out duration-200 transform"
    x-transition:enter-start="opacity-0 translate-x-6"
    x-transition:enter-end="opacity-100 translate-x-0"
    x-transition:leave="transition ease-in duration-150 transform"
    x-transition:leave-start="opacity-100 translate-x-0"
    x-transition:leave-end="opacity-0 translate-x-6"
    x-cloak
>
    {{-- 1. HEADER ──────────────────────────────────────────────────────── --}}
    @include('partials.softphone.header')

    {{-- 2. SCREEN ROUTER ─────────────────────────────────────────────────
         Single source of truth: the `get screen()` getter on intakeComponent
         returns one of: login | idle | ringing | outgoing | active | held | transfer
         (defined in the parent data block). Each template below is mutually
         exclusive and absolute-positioned to fill the remaining space. --}}
    <main class="flex-1 min-h-0 relative overflow-hidden">

        {{-- Authenticate (covers both unauthenticated and mic-denied cases) --}}
        <template x-if="!phoneAuthenticated">
            <section class="absolute inset-0 overflow-y-auto"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                @include('partials.softphone.screen-login')
            </section>
        </template>

        {{-- Ringing (inbound only) --}}
        <template x-if="phoneAuthenticated && ['ringing_inbound', 'ringing'].includes(phoneStatus) && currentCall.direction === 'inbound'">
            <section class="absolute inset-0"
                     x-transition:enter="transition ease-out duration-250"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                @include('partials.softphone.screen-ringing')
            </section>
        </template>

        {{-- Outgoing ringing (violet screen) --}}
        <template x-if="phoneAuthenticated && ['ringing_outbound', 'ringing'].includes(phoneStatus) && currentCall.direction === 'outbound'">
            <section class="absolute inset-0"
                     x-transition:enter="transition ease-out duration-250"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                @include('partials.softphone.screen-outgoing')
            </section>
        </template>

        {{-- Transfer overlay --}}
        <template x-if="phoneAuthenticated && transferPanelOpen">
            <section class="absolute inset-0 z-30"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                @include('partials.softphone.screen-transfer')
            </section>
        </template>

        {{-- Add participant overlay --}}
        <template x-if="phoneAuthenticated && addOrCallOpen">
            <section class="absolute inset-0 z-30"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                @include('partials.softphone.screen-add')
            </section>
        </template>

        {{-- Active call --}}
        <template x-if="phoneAuthenticated && ['active','speaking'].includes(phoneStatus) && !transferPanelOpen && !addOrCallOpen">
            <section class="absolute inset-0"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0">
                @include('partials.softphone.screen-active')
            </section>
        </template>

        {{-- Held --}}
        <template x-if="phoneAuthenticated && phoneStatus === 'held' && !transferPanelOpen && !addOrCallOpen">
            <section class="absolute inset-0"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100">
                @include('partials.softphone.screen-held')
            </section>
        </template>

        {{-- Idle (default authenticated view: tabs + selected panel) --}}
        <template x-if="phoneAuthenticated
                          && !['ringing','ringing_inbound','ringing_outbound','active','speaking','held'].includes(phoneStatus)
                          && !transferPanelOpen && !addOrCallOpen">
            <section class="absolute inset-0 flex flex-col"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100">
                @include('partials.softphone.tabs')
                <div class="flex-1 min-h-0 relative">
                    @include('partials.softphone.tab-dialer')
                    @include('partials.softphone.tab-directory')
                    @include('partials.softphone.tab-recent')
                </div>
            </section>
        </template>
    </main>

    {{-- 3. FOOTER (only when authenticated) ───────────────────────────── --}}
    @include('partials.softphone.footer')
</aside>

{{-- Toggle tab: visible only when collapsed, on the right edge ─────────── --}}
<button
    type="button"
    @click="togglePhoneCollapse()"
    class="fixed right-0 top-1/2 -translate-y-1/2 z-[56] w-7 h-16 bg-[#1E293B] hover:bg-[#334155] border border-slate-800 border-r-0 rounded-l-xl flex items-center justify-center text-slate-300 hover:text-white transition-all duration-200 shadow-lg"
    title="Toggle Softphone"
>
    <i class="fa-solid text-xs" :class="phoneCollapsed ? 'fa-headset' : 'fa-chevron-right'"></i>

    {{-- Pulse indicator if there's a missed call in recent --}}
    <span
        x-show="phoneCollapsed && recentCallLogs.some(h => h.status === 'missed' || h.status === 'no-answer' || h.status === 'cancel')"
        class="absolute -top-1 -right-1 flex h-3 w-3"
    >
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500"></span>
    </span>
</button>
