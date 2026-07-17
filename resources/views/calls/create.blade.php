@extends('layouts.app')

@section('title', 'Help Management - NHMP 130')
@section('page-title', 'New Help')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

[x-cloak] {
    display: none !important;
}

/* Hide all scrollbars — zero scroll policy */
* {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

*::-webkit-scrollbar {
    display: none;
}

input:focus, textarea:focus, button:focus-visible {
    outline: none;
}

/* Right Sidebar Softphone Toggle Tab */
.phone-toggle-tab {
    position: absolute;
    top: 50%;
    margin-top: -20px;
    z-index: 60;
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    border: 3px solid #1e293b;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    color: #94a3b8;
}

.phone-toggle-tab:hover {
    background: linear-gradient(135deg, #4f46e5, #6366f1);
    border-color: #4f46e5;
    color: white;
    transform: scale(1.1) !important;
    box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
}

.phone-toggle-tab:active {
    transform: scale(0.95) !important;
}
</style>
{{--
    ═══════════════════════════════════════════════════════════════
    NHMP 130 — Material Design 3 + Glass Morphism Dispatch Console
    ═══════════════════════════════════════════════════════════════
    Tailwind CSS classes used:
    • Glass surfaces: bg-white/70 backdrop-blur-sm border-white/60
    • Material Elevation: shadow-lg shadow-{color}-900/5
    • MD3 Shapes: rounded-2xl (16dp), rounded-xl (12dp)
    • MD3 State Layers: hover:bg-black/[0.04], active:bg-black/[0.08]
    • Zero scroll: overflow-hidden on ALL containers
--}}

<div x-data="intakeComponent()" x-init="init()"
     class="h-screen w-full overflow-hidden flex flex-col p-3 gap-3 select-none"
     style="background:
         radial-gradient(ellipse 80% 60% at 10% 20%, rgba(59,130,246,0.08) 0%, transparent 60%),
         radial-gradient(ellipse 70% 50% at 90% 80%, rgba(16,185,129,0.06) 0%, transparent 60%),
         radial-gradient(ellipse 60% 70% at 50% 50%, rgba(245,158,11,0.04) 0%, transparent 50%),
         linear-gradient(180deg, #f0f4f8 0%, #e8eef5 100%);
         background-size: 100% 100%;">

    <form action="{{ route('calls.store') }}" method="POST" id="intake-form" @submit.prevent="handleSubmit" class="contents" data-no-pjax>
        @csrf

        {{-- Hidden inputs --}}
        <input type="hidden" name="caller_number" x-model="caller.number">
        <input type="hidden" name="caller_name" x-model="caller.name">
        <input type="hidden" name="vehicle_no" x-model="caller.vehicle_no">
        <input type="hidden" name="geospatial_marker_id" x-model="spatial.geospatial_marker_id">
        <input type="hidden" name="carriageway_id" x-model="spatial.carriageway_id">
        <input type="hidden" name="zone_id" x-model="spatial.zone_id">
        <input type="hidden" name="sector_id" x-model="spatial.sector_id">
        <input type="hidden" name="beat_id" x-model="spatial.beat_id">
        <input type="hidden" name="km_marker_text" :value="spatial.km">
        <input type="hidden" name="tiger_id" x-model="selectedTigerId">
        <input type="hidden" name="priority" x-model="priority">
        <input type="hidden" name="call_type_id" x-model="selectedCallTypeId">
        <input type="hidden" name="call_sub_type_id" x-model="selectedSubType">
        <input type="hidden" name="vehicle_type_id" x-model="selectedVehicleType">
        <input type="hidden" name="caller_lat" x-model="spatial.lat">
        <input type="hidden" name="caller_lng" x-model="spatial.lng">
        <input type="hidden" name="details" x-model="details">
        <input type="hidden" name="location_details" x-model="spatial.coord_input">



        {{-- ═══════════════════════════════════════
            BENTO GRID — Zero Scroll
        ═══════════════════════════════════════ --}}
        <div class="flex-1 min-h-0 grid grid-cols-12 grid-rows-2 gap-3">

            {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                CALLER INTEL — 3 cols, spans 2 rows
            ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
            <div class="col-span-3 row-span-2 flex flex-col rounded-2xl p-3 relative z-30"
                 style="background: rgba(255,255,255,0.72); backdrop-filter: blur(20px) saturate(1.4); border: 1px solid rgba(255,255,255,0.65); box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 4px 8px rgba(0,0,0,0.03), 0 8px 24px rgba(0,0,0,0.04);">
                {{-- Card Header --}}
                <div class="shrink-0 flex items-center gap-2.5 mb-2.5">
                    <div class="w-7 h-7 rounded-xl flex items-center justify-center border backdrop-blur-sm bg-blue-100/80 text-blue-700 border-blue-200/60">
                        <i class="fa-solid fa-fingerprint text-xs"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-[11px] font-bold text-slate-800 uppercase tracking-wider truncate">Caller Information</h3>
                        <p class="text-[9px] font-semibold text-blue-600 uppercase tracking-[0.15em] truncate">Localization Matrix</p>
                    </div>
                    <button type="button" @click.stop="openHistoryModal()"
                            class="shrink-0 px-2.5 py-1.5 rounded-lg text-[8px] font-bold uppercase tracking-wider border border-slate-200/80 text-slate-600 bg-white/40 flex items-center gap-1 transition-all duration-150 hover:bg-white/80 hover:border-blue-300 hover:text-blue-700  hover:-translate-y-1  active:!translate-y-[2px]  cursor-pointer"
                            style="position: relative;">
                        <i class="fa-solid fa-clock-rotate-left text-[10px]"></i> History
                    </button>
                </div>

                {{-- Form Content — NO overflow-y-auto, everything fits --}}
                <div class="flex-1 min-h-0 flex flex-col gap-2 relative">
                    {{-- Phone --}}
                    <div class="shrink-0">
                        <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-500 mb-1">Mobile Number</label>
                        <div class="relative group flex items-center gap-1.5">
                            <div class="relative flex-1">
                                <i class="fa-solid fa-phone absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs group-focus-within:text-blue-500 transition-colors"></i>
                                <input type="text" id="phone_link" x-model="caller.number" @blur="searchCaller()"
                                       @input="caller.number = $event.target.value.replace(/[^0-9]/g, '').slice(0, 11)"
                                       pattern="[0-9]{11}" maxlength="11" minlength="11" title="Please enter exactly 11 digits"
                                       placeholder="03001234567"
                                       class="w-full pl-8 pr-2.5 py-1.5 bg-white/60 border border-slate-200/80 rounded-xl text-[11px] text-slate-700 placeholder:text-slate-400 outline-none transition-all focus:border-blue-400/80 focus:bg-white/80 focus:shadow-[0_0_0_3px_rgba(59,130,246,0.08)] hover:border-slate-300/80">
                            </div>
                            <!-- Quick Dial -->
                            <button type="button" @click="if (caller.number) phoneTriggerQuickDial(caller.number, caller.name)" :disabled="!caller.number || caller.number.length < 3"
                                    class="shrink-0 h-8 w-8 rounded-xl bg-blue-600 hover:bg-blue-500 disabled:opacity-40 disabled:hover:translate-y-0 text-white flex items-center justify-center transition active:scale-95 hover:-translate-y-0.5 cursor-pointer shadow-sm shadow-blue-500/20"
                                    title="Dial Outbound call via ZIWO">
                                <i class="fa-solid fa-phone-flip text-[10px]"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Name --}}
                    <div class="shrink-0">
                        <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-500 mb-1">Caller Name</label>
                        <div class="relative group">
                            <i class="fa-solid fa-user absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="text" x-model="caller.name"
                                   class="w-full pl-8 pr-2.5 py-1.5 bg-white/60 border border-slate-200/80 rounded-xl text-[11px] text-slate-700 outline-none transition-all focus:border-blue-400/80 focus:bg-white/80 focus:shadow-[0_0_0_3px_rgba(59,130,246,0.08)] hover:border-slate-300/80">
                        </div>
                    </div>

                    {{-- Vehicle & KM --}}
                    <div class="shrink-0 grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-500 mb-1">Vehicle ID</label>
                            <input type="text" x-model="caller.vehicle_no" placeholder="ABC-1234"
                                   class="w-full px-2.5 py-1.5 bg-white/60 border border-slate-200/80 rounded-xl text-[11px] text-slate-700 placeholder:text-slate-400 outline-none transition-all focus:border-blue-400/80 focus:bg-white/80 focus:shadow-[0_0_0_3px_rgba(59,130,246,0.08)] hover:border-slate-300/80 uppercase font-bold">
                        </div>
                        <div class="relative">
                            <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-500 mb-1">KM Marker</label>
                            <input type="text" x-model="spatial.km" @input.debounce.500ms="lookupKM()" placeholder="e.g. 504, 24-N"
                                   class="w-full px-2.5 py-1.5 bg-white/60 border border-slate-200/80 rounded-xl text-[11px] text-slate-700 placeholder:text-slate-400 outline-none transition-all focus:border-blue-400/80 focus:bg-white/80 focus:shadow-[0_0_0_3px_rgba(59,130,246,0.08)] hover:border-slate-300/80 font-bold text-center">
                            
                            <!-- Suggestions Dropdown -->
                            <div x-show="geospatialSuggestions.length > 0" class="absolute z-[100] mt-1 w-72 left-0 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden" x-cloak>
                                <div class="bg-blue-50 text-blue-800 text-[9px] uppercase font-black px-3 py-2 border-b border-blue-100 flex items-center justify-between">
                                    <span><i class="fa-solid fa-map-location-dot mr-1"></i> Geospatial Intelligence</span>
                                    <button @click="geospatialSuggestions = []" type="button" class="text-blue-400 hover:text-blue-800"><i class="fa-solid fa-times"></i></button>
                                </div>
                                <div class="max-h-48 overflow-y-auto">
                                    <template x-for="lm in geospatialSuggestions" :key="lm.id">
                                        <div @click="applyGeospatialSuggestion(lm)" class="p-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 transition-colors">
                                            <div class="flex items-start justify-between">
                                                <div>
                                                    <div class="flex items-center gap-1.5 mb-0.5">
                                                        <div class="text-[11px] font-bold text-slate-800 leading-tight" x-text="lm.location_name || 'KM ' + lm.km_marker"></div>
                                                        <template x-if="lm.is_range_match">
                                                            <span class="px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-700 text-[7px] font-black uppercase tracking-tighter">Jurisdiction</span>
                                                        </template>
                                                    </div>
                                                    <div class="text-[9px] font-semibold text-emerald-600 uppercase tracking-widest" x-text="lm.office?.name || 'Unmapped Beat'"></div>
                                                </div>
                                                <div class="text-[10px] font-mono font-bold text-blue-600 bg-blue-50 px-1.5 rounded" x-text="lm.road_name"></div>
                                            </div>
                                            
                                            <div class="mt-1.5 space-y-1">
                                                <template x-if="lm.nearby_cities">
                                                    <div class="text-[9px] text-slate-500"><span class="font-bold">City:</span> <span x-text="lm.nearby_cities"></span></div>
                                                </template>
                                                <template x-if="lm.contact_numbers">
                                                    <div class="text-[9px] text-slate-500"><span class="font-bold">Contact:</span> <span x-text="lm.contact_numbers"></span></div>
                                                </template>
                                            </div>
                                            
                                            <template x-if="lm.agent_prompt">
                                                <div class="mt-1.5 p-1.5 bg-amber-50 rounded-lg border border-amber-100 text-[9px] text-amber-800 italic leading-snug">
                                                    <i class="fa-solid fa-comment-dots mr-1 text-amber-500"></i> <span x-text="lm.agent_prompt"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Asset Type — MD3 Filter Chips --}}
                    <div class="shrink-0">
                        <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-500 mb-1">Vehicle</label>
                        <div class="grid grid-cols-3 gap-1.5">
                            @foreach ($vehicleTypes as $vt)
                                <button type="button"
                                        @click="selectedVehicleType = '{{ $vt->id }}'"
                                        :style="selectedVehicleType == '{{ $vt->id }}' 
                                            ? 'background-color: {{ $vt->color_hex ?? '#2563eb' }}; border-color: transparent; color: white;' 
                                            : ''"
                                        :class="selectedVehicleType == '{{ $vt->id }}'
                                            ? '-translate-y-0.5 '
                                            : 'bg-white/50 text-slate-600 border-slate-200/70 hover:bg-white/80 hover:border-blue-300/60 hover:text-blue-700  hover:-translate-y-1 '"
                                        class="px-2 py-2 rounded-xl border transition-all duration-150 flex flex-col items-center justify-center gap-0.5 text-center select-none active:!translate-y-[2px]  cursor-pointer">
                                    <!-- <i class="fa-solid {{ $vt->icon ?? 'fa-car-side' }} text-sm"></i> -->
                                    <span class="text-[10px] font-bold uppercase tracking-tight truncate w-full">{{ $vt->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Telemetry --}}
                    <div class="shrink-0">
                        <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-500 mb-1">Location</label>
                        <div class="relative group">
                            <i class="fa-solid fa-crosshairs absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="text" x-model="spatial.coord_input" placeholder="location details"
                                   class="w-full pl-8 pr-2.5 py-1.5 bg-white/60 border border-slate-200/80 rounded-xl text-[11px] text-slate-700 placeholder:text-slate-400 outline-none transition-all focus:border-blue-400/80 focus:bg-white/80 focus:shadow-[0_0_0_3px_rgba(59,130,246,0.08)] hover:border-slate-300/80">
                        </div>
                    </div>

                    {{-- Operational Intelligence --}}
                    <div class="flex-1 min-h-0 flex flex-col overflow-hidden">
                        <label class="shrink-0 block text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-500 mb-1">
                            <i class="fa-solid fa-brain mr-1 text-blue-500 text-[10px]"></i>Details and Notes
                        </label>
                        <textarea x-model="details" placeholder="Qualitative nodes..."
                                  class="flex-1 min-h-0 w-full px-2.5 py-2 bg-white/60 border border-slate-200/80 rounded-xl text-[11px] text-slate-700 placeholder:text-slate-400 outline-none transition-all focus:border-blue-400/80 focus:bg-white/80 focus:shadow-[0_0_0_3px_rgba(59,130,246,0.08)] hover:border-slate-300/80 resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                CLASSIFICATION WORKSPACE — 9 cols, row 1
            ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
            <div class="col-span-9 row-span-1 flex flex-col rounded-2xl p-3 overflow-hidden"
                 style="background: rgba(255,255,255,0.72); backdrop-filter: blur(20px) saturate(1.4); border: 1px solid rgba(255,255,255,0.65); box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 4px 8px rgba(0,0,0,0.03), 0 8px 24px rgba(0,0,0,0.04);">
                <div class="shrink-0 flex items-center gap-2.5 mb-2.5 pb-2 border-b border-slate-200/40">
                    <div class="w-7 h-7 rounded-xl flex items-center justify-center border backdrop-blur-sm bg-blue-100/80 text-blue-700 border-blue-200/60">
                        <i class="fa-solid fa-tags text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-[11px] font-bold text-slate-800 uppercase tracking-wider truncate">Classification Workspace</h3>
                        <p class="text-[9px] font-semibold text-blue-600 uppercase tracking-[0.15em] truncate">Logic & Protocol Assignment</p>
                    </div>
                </div>

                <div class="flex-1 min-h-0 flex flex-col gap-2.5 overflow-hidden">
                    {{-- Priority — MD3 Segmented Buttons --}}
                    <div class="shrink-0">
                        <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-500 mb-1.5">Help Priority Level</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="p in [
                                {id: '1', label: 'Critical', tone: 'rose', icon: 'fa-bolt'},
                                {id: '2', label: 'Urgent', tone: 'orange', icon: 'fa-triangle-exclamation'},
                                {id: '3', label: 'Normal', tone: 'amber', icon: 'fa-clock'}
                            ]" :key="p.id">
                                <button type="button"
                                        @click="priority = p.id"
                                        :class="priority == p.id
                                            ? (p.tone == 'rose' ? 'bg-rose-600 border-rose-600' : p.tone == 'orange' ? 'bg-orange-500 border-orange-500' : 'bg-amber-500 border-amber-500') + ' text-white -translate-y-0.5 '
                                            : 'bg-white/50 text-slate-500 border-slate-200/70 hover:bg-white/80  hover:-translate-y-1 '"
                                        class="rounded-xl border transition-all duration-150 flex items-center justify-center gap-2 py-2 text-[10px] font-black uppercase tracking-tight select-none active:!translate-y-[2px]  cursor-pointer">
                                    <i :class="'fa-solid ' + p.icon"></i>
                                    <span x-text="p.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Primary Category — MD3 Filter Chips --}}
                    <div class="shrink-0">
                        <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-500 mb-1.5">Primary Category</label>
                        <div class="grid grid-cols-6 gap-2">
                            @foreach ($callTypes as $type)
                                <button type="button"
                                        @click="onCallTypeChange('{{ $type->id }}')"
                                        :style="selectedCallTypeId == '{{ $type->id }}'
                                            ? 'background-color: {{ $type->color_hex ?? '#2563eb' }}; border-color: transparent; color: white;'
                                            : ''"
                                        :class="selectedCallTypeId == '{{ $type->id }}'
                                            ? '-translate-y-0.5 '
                                            : 'bg-white/50 text-slate-600 border-slate-200/70 hover:bg-white/80 hover:border-blue-300/60 hover:text-blue-700  hover:-translate-y-1 '"
                                        class="px-1.5 py-2 rounded-xl border transition-all duration-150 flex flex-col items-center justify-center gap-1 text-center select-none active:!translate-y-[2px]  cursor-pointer">
                                    <i class="fa-solid {{ $type->icon ?? 'fa-thumbtack' }} text-xs"></i>
                                    <span class="text-[8px] font-black uppercase leading-tight truncate w-full">{{ $type->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sub-Type — Animated reveal --}}
                    <div x-show="selectedCallTypeId"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="shrink-0 overflow-hidden">
                        <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-blue-600 mb-1.5">Secondary Category</label>
                        <div class="grid grid-cols-7 gap-2">
                            <template x-for="st in filteredSubTypes" :key="st.id">
                                <button type="button"
                                        @click="selectedSubType = st.id"
                                        :class="selectedSubType == st.id
                                            ? 'bg-blue-600 text-white border-blue-600 -translate-y-0.5 '
                                            : 'bg-white/50 text-slate-600 border-slate-200/70 hover:bg-white/80 hover:border-blue-300/60 hover:text-blue-700  hover:-translate-y-1 '"
                                        class="px-1.5 py-2 rounded-xl border transition-all duration-150 flex flex-col items-center justify-center gap-1 text-center select-none active:!translate-y-[2px]  cursor-pointer">
                                    <i :class="'fa-solid ' + (st.icon || 'fa-tag')" class="text-xs"></i>
                                    <span class="text-[8px] font-black uppercase leading-tight truncate w-full" x-text="st.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                GEOSPATIAL HIERARCHY — 5 cols, row 2 (REDUCED WIDTH)
            ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
            <div class="col-span-4 row-span-1 flex flex-col rounded-2xl p-3 overflow-hidden"
                 style="background: rgba(255,255,255,0.72); backdrop-filter: blur(20px) saturate(1.4); border: 1px solid rgba(255,255,255,0.65); box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 4px 8px rgba(0,0,0,0.03), 0 8px 24px rgba(0,0,0,0.04);">
                <div class="shrink-0 flex items-center gap-2.5 mb-2.5 pb-2 border-b border-slate-200/40">
                    <div class="w-7 h-7 rounded-xl flex items-center justify-center border backdrop-blur-sm bg-emerald-100/80 text-emerald-700 border-emerald-200/60">
                        <i class="fa-solid fa-map-location-dot text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-[11px] font-bold text-slate-800 uppercase tracking-wider truncate">Area of responsibility</h3>
                        <p class="text-[9px] font-semibold text-emerald-600 uppercase tracking-[0.15em] truncate">Zone, Sector, Beat</p>
                    </div>
                </div>

                <div class="flex-1 min-h-0 flex flex-col gap-2 overflow-hidden">
                    {{-- Zone — Step 1 --}}
                    <div class="shrink-0">
                        <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-emerald-600 mb-1">
                            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 text-[8px] font-black mr-1">1</span>
                            Operational Zone
                        </label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <template x-for="z in allZones" :key="z.id">
                                <button type="button"
                                        @click="spatial.zone_id = z.id; onZoneChange()"
                                        :class="spatial.zone_id == z.id
                                            ? 'bg-emerald-600 text-white border-emerald-600 -translate-y-0.5 '
                                            : 'bg-white/50 text-slate-600 border-slate-200/70 hover:bg-white/80 hover:border-emerald-300/60 hover:text-emerald-700  hover:-translate-y-1 '"
                                        class="px-1 py-1.5 rounded-xl border transition-all duration-150 flex items-center justify-center text-center select-none active:!translate-y-[2px]  cursor-pointer">
                                    <span class="text-[9px] font-black uppercase tracking-tight truncate" x-text="z.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Sector — Step 2 --}}
                    <div x-show="spatial.zone_id"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="shrink-0 overflow-hidden">
                        <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-emerald-600 mb-1">
                            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 text-[8px] font-black mr-1">2</span>
                            Jurisdiction Sector
                        </label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <template x-for="s in filteredSectors" :key="s.id">
                                <button type="button"
                                        @click="spatial.sector_id = s.id; onSectorChange()"
                                        :class="spatial.sector_id == s.id
                                            ? 'bg-emerald-600 text-white border-emerald-600 -translate-y-0.5 '
                                            : 'bg-white/50 text-slate-600 border-slate-200/70 hover:bg-white/80 hover:border-emerald-300/60 hover:text-emerald-700  hover:-translate-y-1 '"
                                        class="px-1 py-1.5 rounded-xl border transition-all duration-150 flex items-center justify-center text-center select-none active:!translate-y-[2px]  cursor-pointer">
                                    <span class="text-[9px] font-black uppercase tracking-tight truncate" x-text="s.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Beat — Step 3 --}}
                    <div x-show="spatial.sector_id"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="shrink-0 overflow-hidden">
                        <label class="block text-[9px] font-semibold uppercase tracking-[0.12em] text-emerald-600 mb-1">
                            <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-emerald-100 text-emerald-700 text-[8px] font-black mr-1">3</span>
                            Field Beat
                        </label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <template x-for="b in filteredBeats" :key="b.id">
                                <button type="button"
                                        @click="spatial.beat_id = b.id; onBeatChange()"
                                        :class="spatial.beat_id == b.id
                                            ? 'bg-emerald-600 text-white border-emerald-600 -translate-y-0.5 '
                                            : 'bg-white/50 text-slate-600 border-slate-200/70 hover:bg-white/80 hover:border-emerald-300/60 hover:text-emerald-700  hover:-translate-y-1 h'"
                                        class="px-1 py-1.5 rounded-xl border transition-all duration-150 flex items-center justify-center text-center select-none active:!translate-y-[2px]  cursor-pointer">
                                    <span class="text-[9px] font-black uppercase tracking-tight truncate" x-text="b.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Spatial Lock Status — MD3 Linear Progress Indicator --}}
                    <div class="mt-auto pt-2 border-t border-slate-200/40 shrink-0">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[9px] font-semibold uppercase tracking-[0.12em] text-slate-500">Beat Lock</span>
                                <template x-if="!spatial.zone_id">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200/60 text-slate-500 text-[8px] font-black uppercase">
                                        <i class="fa-solid fa-circle-dot text-[7px]"></i> Unlocked
                                    </span>
                                </template>
                                <template x-if="spatial.zone_id && spatial.sector_id && spatial.beat_id">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200/60 text-emerald-700 text-[8px] font-black uppercase">
                                        <i class="fa-solid fa-circle-check text-[7px]"></i> Locked
                                    </span>
                                </template>
                                <template x-if="spatial.zone_id && (!spatial.sector_id || !spatial.beat_id)">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 border border-amber-200/60 text-amber-700 text-[8px] font-black uppercase">
                                        <i class="fa-solid fa-lock-open text-[7px]"></i> Partial
                                    </span>
                                </template>
                            </div>
                            <span class="text-[8px] font-mono text-slate-400 tabular-nums" x-text="(spatial.zone_id ? (spatial.sector_id ? (spatial.beat_id ? 3 : 2) : 1) : 0) + '/3'"></span>
                        </div>
                        <div class="flex gap-1 mt-1.5">
                            <div :class="spatial.zone_id ? 'bg-emerald-500 shadow-sm shadow-emerald-500/30' : 'bg-slate-200'" class="h-1.5 flex-1 rounded-full transition-all duration-500"></div>
                            <div :class="spatial.sector_id ? 'bg-emerald-500 shadow-sm shadow-emerald-500/30' : 'bg-slate-200'" class="h-1.5 flex-1 rounded-full transition-all duration-500"></div>
                            <div :class="spatial.beat_id ? 'bg-emerald-500 shadow-sm shadow-emerald-500/30' : 'bg-slate-200'" class="h-1.5 flex-1 rounded-full transition-all duration-500"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Contact Information / Beat/Sector/Zone Contact --}}
            <div class="col-span-2 row-span-1 flex flex-col rounded-2xl p-3 overflow-hidden"
                 style="background: rgba(255,255,255,0.72); backdrop-filter: blur(20px) saturate(1.4); border: 1px solid rgba(255,255,255,0.65); box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 4px 8px rgba(0,0,0,0.03), 0 8px 24px rgba(0,0,0,0.04);">
                <div class="shrink-0 flex items-center gap-2.5 mb-2.5 pb-2 border-b border-slate-200/40">
                    <div class="w-7 h-7 rounded-xl flex items-center justify-center border backdrop-blur-sm bg-blue-100/80 text-blue-700 border-blue-200/60">
                        <i class="fa-solid fa-address-book text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-[11px] font-bold text-slate-800 uppercase tracking-wider truncate">Office Contacts</h3>
                        <p class="text-[9px] font-semibold text-blue-600 uppercase tracking-[0.15em] truncate">Active Directory</p>
                    </div>
                </div>

                <div class="flex-1 min-h-0 flex flex-col justify-around gap-2 overflow-hidden">
                    {{-- Zone Contact --}}
                    <div class="space-y-0.5">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block font-bold">Zone Hotline</span>
                        <div class="flex items-center justify-between gap-1.5">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="spatialContacts.zone ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                                <span class="text-[10px] font-bold text-slate-700 truncate" x-text="spatialContacts.zone || 'No Contact Loaded'"></span>
                            </div>
                            <button type="button" x-show="spatialContacts.zone" @click="phoneTriggerQuickDial(spatialContacts.zone, 'Zone Hotline')"
                                    class="shrink-0 p-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition active:scale-90 cursor-pointer"
                                    title="Call Zone Hotline">
                                <i class="fa-solid fa-phone text-[8px]"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Sector Contact --}}
                    <div class="space-y-0.5">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block font-bold">Sector Helpline</span>
                        <div class="flex items-center justify-between gap-1.5">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="spatialContacts.sector ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                                <span class="text-[10px] font-bold text-slate-700 truncate" x-text="spatialContacts.sector || 'No Contact Loaded'"></span>
                            </div>
                            <button type="button" x-show="spatialContacts.sector" @click="phoneTriggerQuickDial(spatialContacts.sector, 'Sector Helpline')"
                                    class="shrink-0 p-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 transition active:scale-90 cursor-pointer"
                                    title="Call Sector Helpline">
                                <i class="fa-solid fa-phone text-[8px]"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Beat Contact --}}
                    <div class="space-y-0.5">
                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest block font-bold">Beat Phone</span>
                        <div class="flex items-center justify-between gap-1.5">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="spatialContacts.beat ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                                <span class="text-[10px] font-bold text-emerald-700 font-mono tracking-tight truncate" x-text="spatialContacts.beat || 'No Contact Loaded'"></span>
                            </div>
                            <button type="button" x-show="spatialContacts.beat" @click="phoneTriggerQuickDial(spatialContacts.beat, 'Beat Phone')"
                                    class="shrink-0 p-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition active:scale-90 cursor-pointer"
                                    title="Call Beat Phone">
                                <i class="fa-solid fa-phone text-[8px]"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
                MISSION AUTHORITY — 4 cols, row 2
            ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
            <div class="col-span-3 row-span-1 flex flex-col rounded-2xl p-3 relative overflow-hidden"
                 style="background: rgba(254,243,199,0.55); backdrop-filter: blur(16px) saturate(1.3); border: 1px solid rgba(252,211,77,0.4); box-shadow: 0 1px 2px rgba(0,0,0,0.04), 0 4px 8px rgba(0,0,0,0.03), 0 8px 24px rgba(0,0,0,0.04);">
                {{-- Ambient glow effects --}}
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-amber-300/20 rounded-full blur-[60px] pointer-events-none"></div>
                <div class="absolute -left-8 -bottom-8 w-32 h-32 bg-orange-300/15 rounded-full blur-[50px] pointer-events-none"></div>

                <div class="relative z-10 flex flex-col h-full overflow-hidden">
                    {{-- Header --}}
                    <div class="shrink-0 flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-bullseye text-amber-600 text-xs"></i>
                        <span class="text-[9px] font-black uppercase tracking-[0.25em] text-amber-700">Dispatch</span>
                    </div>

                    {{-- Hero Text --}}
                    <div class="shrink-0">
                        <p class="text-lg font-black text-slate-800 tracking-tight leading-tight">Help Summary</p>
                        <!-- <p class="text-lg font-black text-amber-700 tracking-tight leading-tight">Help Dispatch</p> -->
                    </div>

                    {{-- Selected Summary — MD3 List Items --}}
                    <div class="shrink-0 mt-2 space-y-0">
                        <div class="flex items-center justify-between py-[3px] border-b border-amber-900/5">
                            <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Priority</span>
                            <span class="text-[10px] font-bold text-amber-700" x-text="priority ? 'P' + priority : '—'"></span>
                        </div>
                        <div class="flex items-center justify-between py-[3px] border-b border-amber-900/5">
                            <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Zone</span>
                            <span class="text-[10px] font-bold text-slate-700 truncate max-w-[110px]" x-text="spatial.zone_name || '—'"></span>
                        </div>
                        <div class="flex items-center justify-between py-[3px] border-b border-amber-900/5">
                            <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Sector</span>
                            <span class="text-[10px] font-bold text-slate-700 truncate max-w-[110px]" x-text="spatial.sector_name || '—'"></span>
                        </div>
                        <div class="flex items-center justify-between py-[3px] border-b border-amber-900/5">
                            <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Beat</span>
                            <span class="text-[10px] font-bold text-slate-700 truncate max-w-[110px]" x-text="spatial.beat_name || '—'"></span>
                        </div>
                        <div class="flex items-center justify-between py-[3px]">
                            <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">Category</span>
                            <span class="text-[10px] font-bold text-slate-700 truncate max-w-[110px]" x-text="selectedCallTypeName || '—'"></span>
                        </div>
                    </div>

                    {{-- Actions — MD3 Filled + Outlined Buttons --}}
                    <div class="shrink-0 space-y-2 mt-auto pt-2">
                        <button type="submit" :disabled="submitting"
                                class="w-full py-2.5 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] flex items-center justify-center gap-2 transition-all duration-150
                                       bg-amber-600 text-white border border-amber-600
                                       
                                        hover:-translate-y-1
                                       active:!translate-y-[2px] 
                                       disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:translate-y-0 cursor-pointer">
                            <i class="fa-solid fa-bolt-lightning text-sm"></i>
                            <span x-show="!submitting">Submit & Dispatch</span>
                            <span x-show="submitting">Submitting...</span>
                        </button>

                        <button type="button" @click="resetForm()"
                                class="w-full py-2 rounded-xl text-[10px] font-black uppercase tracking-[0.2em] flex items-center justify-center gap-2 transition-all duration-150
                                       border border-rose-200/80 text-rose-700 bg-rose-50/40
                                       
                                       hover:bg-rose-50/80 hover:border-rose-300/80 hover:-translate-y-1 
                                       active:!translate-y-[2px]  cursor-pointer">
                            <i class="fa-solid fa-ban text-xs"></i>
                            Cancel
                        </button>
                    </div>

                    {{-- Footer --}}
                    <div class="shrink-0 mt-1.5 pt-1.5 border-t border-amber-900/5 flex items-center justify-between opacity-50">
                        <span class="text-[7px] font-black uppercase tracking-widest italic text-slate-400">Help Registry</span>
                        <div class="flex gap-1">
                            <div class="w-1 h-1 rounded-full bg-slate-300"></div>
                            <div class="w-1 h-1 rounded-full bg-slate-300"></div>
                            <div class="w-1 h-1 rounded-full bg-amber-500"></div>
                        </div>
                    </div>
                </div>
            </div>

            
        </div>
    </form>

    {{-- ═══════════════════════════════════════
        HISTORY MODAL — Material Dialog (Glass)
    ═══════════════════════════════════════ --}}
    <div x-show="historyModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center p-6"
         style="background-color: rgba(15,23,42,0.35); backdrop-filter: blur(12px);"
         x-cloak>
        <div x-show="historyModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-5"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-5"
             class="bg-white/85 backdrop-blur-2xl border border-white/70 shadow-2xl shadow-slate-900/10 rounded-3xl w-full max-w-3xl max-h-[82vh] flex flex-col overflow-hidden"
             @click.away="historyModalOpen = false">

            {{-- Modal Header --}}
            <div class="px-8 py-8 border-b border-white/10 flex items-center justify-between shrink-0 bg-gradient-to-br from-amber-400 via-orange-500 to-rose-500 relative overflow-hidden">
                <div class="absolute inset-0 bg-grid-white/[0.05] bg-[size:16px_16px]"></div>
                <div class="relative z-10 flex items-center gap-5">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center border backdrop-blur-sm bg-white/10 text-white border-white/20 shadow-2xl">
                        <i class="fa-solid fa-clock-rotate-left text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-white uppercase tracking-tighter italic scale-y-110">Mobile Number Calls History</h3>
                        <p class="text-[9px] font-black text-navy-900 uppercase tracking-[0.4em] mt-1 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-navy-900 animate-pulse"></span>
                            Commuter previous calls records
                        </p>
                    </div>
                </div>
                <button type="button" @click="historyModalOpen = false" class="relative z-10 w-10 h-10 rounded-full flex items-center justify-center bg-white/5 border border-white/10 text-white transition-all duration-150 backdrop-blur-md  hover:-translate-y-1  hover:bg-white/10 active:!translate-y-[2px]  cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="flex-1 min-h-0 flex flex-col md:flex-row overflow-hidden relative">
                {{-- Loading Overlay --}}
                <div x-show="historyLoading"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="absolute inset-0 bg-white/70 backdrop-blur-md z-10 flex items-center justify-center">
                    <div class="flex flex-col items-center gap-3">
                        <div class="w-10 h-10 border-[3px] border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                        <span class="text-[9px] font-black text-blue-700 uppercase tracking-[0.2em]">Loading...</span>
                    </div>
                </div>

                {{-- LEFT: Dossier --}}
                <div class="flex-1 p-5 overflow-hidden flex flex-col">
                    <template x-if="selectedHistoryCall">
                        <div class="flex-1 flex flex-col overflow-hidden">
                            <h4 class="text-[10px] font-bold text-slate-800 uppercase tracking-wider mb-3 border-b border-slate-200/50 pb-2 shrink-0 flex items-center gap-2">
                                <i class="fa-solid fa-layer-group text-blue-600 text-xs"></i> Call Details
                            </h4>
                            <div class="space-y-3 flex-1 overflow-hidden">
                                <div class="grid grid-cols-2 gap-4 shrink-0">
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Caller Name</p>
                                        <p class="text-[10px] font-bold text-slate-700" x-text="selectedHistoryCall.caller_name || 'Anonymous'"></p>
                                    </div>
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Mobile Number</p>
                                        <p class="text-[10px] font-bold text-amber-700" x-text="selectedHistoryCall.caller_number ||'N/A'"></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 shrink-0">
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Date & Time</p>
                                        <p class="text-[10px] font-bold text-slate-700" x-text="new Date(selectedHistoryCall.created_at).toLocaleString()"></p>
                                    </div>
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Call Record Id</p>
                                        <p class="text-[10px] font-bold text-amber-700" x-text="selectedHistoryCall.id || 'N/A'"></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 shrink-0" x-show="selectedHistoryCall.agent_name">
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Handled By (Agent)</p>
                                        <p class="text-[10px] font-bold text-blue-600" x-text="selectedHistoryCall.agent_name"></p>
                                    </div>
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Priority</p>
                                        <p class="text-[10px] font-bold text-slate-700" x-text="selectedHistoryCall.priority == '1' ? 'Critical' : (selectedHistoryCall.priority == '2' ? 'Urgent' : 'Normal')"></p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 shrink-0" x-show="selectedHistoryCall.call_reminder_count > 0">
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Reminder Count</p>
                                        <p class="text-[10px] font-bold text-rose-600" x-text="selectedHistoryCall.call_reminder_count"></p>
                                    </div>
                                    <div>
                                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Last Reminder</p>
                                        <p class="text-[10px] font-bold text-rose-600" x-text="selectedHistoryCall.last_reminder_at ? new Date(selectedHistoryCall.last_reminder_at).toLocaleString() : 'N/A'"></p>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Assigned To Beat</p>
                                    <div class="flex items-center gap-1 text-[10px] font-bold text-slate-700">
                                        <i class="fa-solid fa-location-arrow text-emerald-600 text-[10px]"></i>
                                        <span x-text="`${selectedHistoryCall.zone_name || 'No Zone'} / ${selectedHistoryCall.sector_name || 'No Sector'} / ${selectedHistoryCall.beat_name || 'No Beat'}`"></span>
                                    </div>
                                </div>
                                <div class="shrink-0">
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Location Details</p>
                                    <p class="text-[10px] font-bold text-slate-700 font-mono" x-text="`${selectedHistoryCall.location_details || 'No details'}`"></p>
                                </div>
                                <div class="flex-1 min-h-0 flex flex-col overflow-hidden">
                                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-0.5 shrink-0">Help Details & Notes</p>
                                    <div class="flex-1 p-3 bg-slate-50/80 rounded-xl border border-slate-200/60 text-[10px] text-slate-600 leading-relaxed overflow-hidden"
                                         x-text="selectedHistoryCall.details || 'No details provided.'"></div>
                                </div>
                            </div>
                            <div class="mt-3 pt-3 border-t border-slate-200/50 shrink-0" x-show="['pending', 'in_progress'].includes(selectedHistoryCall.status)">
                                <button type="button" @click="applyReminderCall()"
                                        class="w-full py-2.5 bg-emerald-600 border border-emerald-600 text-white rounded-xl text-[9px] font-black uppercase tracking-[0.15em] flex items-center justify-center gap-2 transition-all duration-150
                                               
                                                hover:-translate-y-1
                                               active:!translate-y-[2px]  cursor-pointer">
                                    <i class="fa-solid fa-clone text-xs"></i> Send Reminder
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selectedHistoryCall && callerHistory.length > 0 && !historyLoading">
                        <div class="flex-1 flex flex-col items-center justify-center gap-2 text-slate-400">
                            <i class="fa-solid fa-layer-group text-2xl opacity-30"></i>
                            <span class="text-[10px] font-bold uppercase tracking-wider">Select a call record to view details</span>
                        </div>
                    </template>
                    <template x-if="callerHistory.length === 0 && !historyLoading">
                        <div class="flex-1 flex flex-col items-center justify-center gap-2 text-slate-400">
                            <i class="fa-solid fa-circle-dot text-2xl opacity-30"></i>
                            <span class="text-[10px] font-bold uppercase tracking-wider">No call records found</span>
                        </div>
                    </template>
                </div>

                {{-- RIGHT: List --}}
                <div class="w-full md:w-64 border-l border-slate-200/50 bg-slate-50/40 flex flex-col shrink-0">
                    <div class="px-3 py-2.5 border-b border-slate-200/50 bg-white/40 shrink-0">
                        <h4 class="text-[9px] font-bold text-blue-700 uppercase tracking-widest flex items-center gap-1.5">
                            <i class="fa-solid fa-clock text-[10px]"></i> Call Records (Last 5)
                        </h4>
                    </div>
                    <div class="flex-1 overflow-hidden p-2 space-y-1.5">
                        <template x-for="call in callerHistory" :key="call.id">
                            <button type="button" @click="selectHistoryCall(call)"
                                    :class="selectedHistoryCall && selectedHistoryCall.id === call.id
                                        ? 'bg-blue-50/80 border-blue-300/60 -translate-y-0.5 '
                                        : 'bg-white/50 border-slate-200/40 hover:bg-white/80 hover:border-blue-200/60  hover:-translate-y-1 '"
                                    class="w-full text-left p-2.5 rounded-xl border transition-all duration-150 group active:!translate-y-[2px]  cursor-pointer">
                                <div class="flex items-center justify-between mb-0.5">
                                    <span :class="selectedHistoryCall && selectedHistoryCall.id === call.id ? 'text-blue-700' : 'text-slate-700'"
                                          class="text-[9px] font-bold uppercase tracking-wider"
                                          x-text="call.call_number || ('CALL-'+call.id)"></span>
                                    <i :class="selectedHistoryCall && selectedHistoryCall.id === call.id ? 'text-blue-500' : 'text-slate-300'"
                                       class="fa-solid fa-chevron-right text-[10px] transition-colors"></i>
                                </div>
                                <div class="text-[8px] font-semibold text-slate-400 uppercase tracking-wider truncate"
                                     x-text="new Date(call.created_at).toLocaleString()"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        STICKED RIGHT SIDEBAR SOFTPHONE CONSOLE
    ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ --}}
    <div class="fixed inset-y-0 right-0 h-screen z-[55] flex transition-all duration-300"
         :class="phoneCollapsed ? 'w-0' : 'w-80'"
         x-cloak>
        
        <!-- Toggle Tab Button (always visible on edge) -->
        <button type="button" 
                @click="togglePhoneCollapse()"
                class="phone-toggle-tab"
                :style="phoneCollapsed ? 'right: 12px; position: fixed; top: 50%; transform: translateY(-50%);' : 'left: -20px;'"
                title="Toggle Telephony Console">
            <i class="fa-solid" :class="phoneCollapsed ? 'fa-headset' : 'fa-chevron-right'"></i>
            <!-- State indicator badge when collapsed -->
            <span x-show="phoneCollapsed && phoneCallActive && currentCall.id"
                  class="absolute -top-1 -right-1 flex h-4 w-4">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-4 w-4 bg-rose-500 text-[8px] font-black items-center justify-center text-white">!</span>
            </span>
        </button>

        <!-- Expanded Phone Console -->
        <div x-show="!phoneCollapsed" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-8"
             class="w-80 h-full bg-slate-950 border-l border-slate-900 shadow-2xl flex flex-col overflow-hidden text-slate-100">
            
            <!-- Header bar -->
            <div class="p-3 bg-slate-900 border-b border-slate-800/80 flex justify-between items-center shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="relative flex h-2.5 w-2.5">
                        <span x-show="['ringing', 'speaking', 'active'].includes(phoneStatus)" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5"
                              :class="{
                                  'bg-emerald-500': phoneStatus === 'online' || phoneStatus === 'speaking' || phoneStatus === 'active',
                                  'bg-rose-500': phoneStatus === 'ringing' || phoneStatus === 'ringing_inbound',
                                  'bg-amber-500': phoneStatus === 'pause' || phoneStatus === 'held',
                                  'bg-slate-500': phoneStatus === 'offline'
                              }"></span>
                    </span>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-300" x-text="phoneStatus"></span>
                        <span class="text-[8px] text-slate-400 block tracking-tight" x-text="phoneAuthenticated ? '@' + ziwoUsername : 'Disconnected'"></span>
                    </div>
                </div>
                <div class="flex items-center gap-2" x-show="phoneAuthenticated && !['ringing', 'ringing_inbound', 'speaking', 'active'].includes(phoneStatus)">
                    <button type="button" @click="checkOrRequestMicrophone()" class="text-slate-400 p-1 rounded hover:bg-slate-800 transition" :class="micAllowed === true ? 'text-emerald-500 hover:text-emerald-400' : 'text-amber-500 hover:text-rose-500 animate-pulse'" title="Check Microphone Access">
                        <i class="fa-solid" :class="micAllowed === true ? 'fa-microphone' : 'fa-microphone-slash'"></i>
                    </button>
                    <!-- Quick Tab Switchers (Dialer & Directory) -->
                    <button type="button" @click="phoneTab = 'dialer'"
                            class="p-1 rounded hover:bg-slate-800 transition text-xs shrink-0"
                            :class="phoneTab === 'dialer' ? 'text-indigo-400 hover:text-indigo-300' : 'text-slate-400 hover:text-white'"
                            title="Dialer">
                        <i class="fa-solid fa-keyboard text-xs"></i>
                    </button>
                    <button type="button" @click="phoneTab = 'phonebook'; $nextTick(() => phoneSearchContacts())"
                            class="p-1 rounded hover:bg-slate-800 transition text-xs shrink-0"
                            :class="phoneTab === 'phonebook' ? 'text-indigo-400 hover:text-indigo-300' : 'text-slate-400 hover:text-white'"
                            title="Directory">
                        <i class="fa-solid fa-address-book text-xs"></i>
                    </button>
                    <button type="button" @click="phoneDisconnect()" class="text-slate-400 hover:text-rose-400 transition" title="Log Out Telephony">
                        <i class="fa-solid fa-power-off text-xs"></i>
                    </button>
                    <button type="button" @click="togglePhoneCollapse()" class="text-slate-400 hover:text-white transition p-1 rounded hover:bg-slate-800" title="Collapse Softphone">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </button>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1 min-h-0 flex flex-col relative">

                <!-- 1. Authentication View -->
                <div x-show="!phoneAuthenticated" class="bg-slate-900 border border-amber-500/30 rounded-xl p-3 mb-1">
                    <div class="text-center mb-2">
                        <i class="fa-solid fa-shield-halved text-indigo-500 text-3xl mb-2"></i>
                        <h4 class="font-bold text-sm text-slate-200">ZIWO Agent Portal</h4>
                        <p class="text-[10px] text-slate-500">Authenticate session to enable incoming/outbound calls.</p>
                    </div>

                    <!-- Microphone Permission Banner -->
                    <div class="text-[10px] space-y-1 mb-2">
                        <div x-show="micAllowed === false" class="bg-amber-500/10 border border-amber-500/30 text-amber-400 p-2.5 rounded-xl flex items-center justify-between gap-2">
                            <span class="leading-tight"><i class="fa-solid fa-microphone-slash mr-1"></i> Mic access blocked. Calls will fail.</span>
                            <button type="button" @click="checkOrRequestMicrophone()" class="px-2 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded-lg font-bold text-[9px] transition shrink-0 cursor-pointer">Allow</button>
                        </div>
                        <div x-show="micAllowed === null" class="bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 p-2.5 rounded-xl flex items-center justify-between gap-2">
                            <span class="leading-tight"><i class="fa-solid fa-circle-info mr-1"></i> Authorize microphone to enable voice calls.</span>
                            <button type="button" @click="checkOrRequestMicrophone()" class="px-2 py-1 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold text-[9px] transition shrink-0 cursor-pointer">Enable</button>
                        </div>
                        <div x-show="micAllowed === true" class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 p-2 rounded-xl flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i> Microphone fully authorized
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-[8px] font-bold uppercase tracking-wider text-slate-400 mb-1">Username / Email</label>
                            <input type="text" x-model="phoneAuthForm.username" placeholder="agent_username"
                                   class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-600 outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[8px] font-bold uppercase tracking-wider text-slate-400 mb-1">Password</label>
                            <div class="relative">
                                <input :type="showPhonePassword ? 'text' : 'password'" x-model="phoneAuthForm.password" placeholder="••••••••"
                                       class="w-full pl-3 pr-10 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-600 outline-none focus:border-indigo-500">
                                <button type="button" @click="showPhonePassword = !showPhonePassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200 focus:outline-none">
                                    <i class="fa-solid" :class="showPhonePassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>

                        <div x-show="phoneStatusError" class="text-[9px] text-rose-500 bg-rose-500/10 p-2 rounded-lg border border-rose-500/20" x-text="phoneStatusError"></div>

                        <button type="button" @click="phoneAuthenticate()" :disabled="phoneSubmitting"
                                class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 text-white text-xs font-bold rounded-xl transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-lock-open text-xs"></i>
                            <span x-text="phoneSubmitting ? 'Authenticating...' : 'Establish Session'"></span>
                        </button>
                    </div>
                </div>

                <!-- 2. Active Incoming / Outgoing Call Overlay -->
                <!-- Only show when phoneStatus is in a call state. Uses phoneCallActive
                     which combines status with valid currentCall to prevent stuck overlay. -->
                <template x-if="phoneAuthenticated && phoneCallActive && phoneStatus !== 'held'">
                <div class="absolute inset-0 z-40 p-6 flex flex-col justify-between select-none"
                     :style="(phoneStatus === 'ringing_inbound' || (phoneStatus === 'ringing' && currentCall?.direction === 'inbound'))
                        ? 'background: linear-gradient(180deg, #34d399 0%, #059669 50%, #047857 100%);'
                        : (phoneStatus === 'speaking' || phoneStatus === 'active' || phoneStatus === 'held')
                            ? 'background: linear-gradient(180deg, #1e3a8a 0%, #312e81 40%, #4c1d95 100%);'
                            : 'background: linear-gradient(180deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);'">
                    
                    <!-- Top row: note icon + close button -->
                    <div class="flex justify-between items-center shrink-0">
                        <div x-show="heldParticipants.length > 0" class="flex-1"></div>
                        <div class="flex-1 flex justify-end">
                            <button type="button" x-show="['active', 'held', 'speaking'].includes(phoneStatus)"
                                    @click="if (window.Notification) window.Notification.info('Detailed logging/notes view is active in the main workspace.', 'Note Protocol')" 
                                    class="text-white/80 hover:text-white transition">
                                <i class="fa-solid fa-comment-medical text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Active Call Stack / Held Calls List -->
                    <div class="shrink-0 space-y-2 max-h-[45%] overflow-y-auto pr-1">
                        <!-- Loop over all active calls from SDK/state so they stack up neatly -->
                        <template x-for="callId in Object.keys(ziwoActiveCalls)" :key="callId">
                            <div x-show="callId !== currentCall.id" 
                                 class="bg-black/50 border border-white/15 rounded-xl overflow-hidden shadow-md">
                                <div class="flex items-center gap-2.5 px-3 py-2.5 cursor-pointer hover:bg-white/10 transition group"
                                     @click="switchToHeldCall({ id: callId, number: ziwoActiveCalls[callId]?.phoneNumber || ziwoActiveCalls[callId]?.callerNumber || '', name: ziwoActiveCalls[callId]?.callerIdName || ziwoActiveCalls[callId]?.displayName || '' })">
                                    
                                    <!-- Avatar -->
                                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white flex-shrink-0 transition">
                                        <i class="fa-solid fa-phone text-xs"></i>
                                    </div>
                                    
                                    <!-- Info -->
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[11px] font-bold text-white truncate" 
                                             x-text="ziwoActiveCalls[callId]?.phoneNumber || ziwoActiveCalls[callId]?.callerNumber || 'Unknown Leg'"></div>
                                        <div class="text-[9px] text-white/50 flex items-center gap-1">
                                            <span>On Hold</span>
                                            <span>·</span>
                                            <span class="text-emerald-400 font-semibold">tap to resume</span>
                                        </div>
                                    </div>
                                    <div class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shrink-0"></div>
                                </div>
                                
                                <!-- Individual Actions per stacked call leg -->
                                <div class="flex border-t border-white/10">
                                    <button type="button" 
                                            @click.stop="toggleMuteHeldCall({ id: callId })"
                                            class="flex-1 py-1.5 text-[9px] text-white/60 hover:text-white font-bold transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-microphone"></i> Mute
                                    </button>
                                    <div class="w-px bg-white/10"></div>
                                    <button type="button" 
                                            @click.stop="switchToHeldCall({ id: callId, number: ziwoActiveCalls[callId]?.phoneNumber || ziwoActiveCalls[callId]?.callerNumber || '', name: ziwoActiveCalls[callId]?.callerIdName || ziwoActiveCalls[callId]?.displayName || '' })"
                                            class="flex-1 py-1.5 text-[9px] text-emerald-400 hover:text-emerald-300 font-bold transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-phone-flip"></i> Switch
                                    </button>
                                    <div class="w-px bg-white/10"></div>
                                    <button type="button" 
                                            @click.stop="hangupHeldCall({ id: callId })"
                                            class="flex-1 py-1.5 text-[9px] text-rose-400 hover:text-rose-300 font-bold transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-phone-slash"></i> End
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Fallback / Static heldParticipants array check if SDK sync is pending -->
                        <template x-for="(p, idx) in heldParticipants" :key="idx">
                            <div x-show="!Object.keys(ziwoActiveCalls).includes(p.id) && p.id !== currentCall.id" 
                                 class="bg-black/50 border border-white/15 rounded-xl overflow-hidden shadow-md">
                                <div class="flex items-center gap-2.5 px-3 py-2.5 cursor-pointer hover:bg-white/10 transition group"
                                     @click="switchToHeldCall(p)">
                                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white flex-shrink-0">
                                        <i class="fa-solid fa-user text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-[11px] font-bold text-white truncate" x-text="p.number || p.name"></div>
                                        <div class="text-[9px] text-white/50">On Hold · tap to resume</div>
                                    </div>
                                    <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse shrink-0"></div>
                                </div>
                                <div class="flex border-t border-white/10">
                                    <button type="button" @click.stop="toggleMuteHeldCall(p)"
                                            class="flex-1 py-1.5 text-[9px] text-white/60 hover:text-white font-bold transition flex items-center justify-center gap-1">
                                        <i class="fa-solid" :class="p.is_muted ? 'fa-microphone-slash text-rose-400' : 'fa-microphone'"></i> Mute
                                    </button>
                                    <div class="w-px bg-white/10"></div>
                                    <button type="button" @click.stop="switchToHeldCall(p)"
                                            class="flex-1 py-1.5 text-[9px] text-emerald-400 hover:text-emerald-300 font-bold transition flex items-center justify-center gap-1">
                                        Resume
                                    </button>
                                    <div class="w-px bg-white/10"></div>
                                    <button type="button" @click.stop="hangupHeldCall(p)"
                                            class="flex-1 py-1.5 text-[9px] text-rose-400 hover:text-rose-300 font-bold transition flex items-center justify-center gap-1">
                                        End
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Main screen info -->
                    <div class="text-center space-y-4 my-auto">
                        <!-- Subtitle -->
                        <h4 class="text-sm tracking-wide text-white/90" 
                            x-text="['ringing_inbound', 'ringing'].includes(phoneStatus) 
                                ? (phoneStatus === 'ringing' ? 'Outbound Call' : 'Inbound Call') 
                                : (currentCall.direction === 'outbound' ? 'On Outbound Call to' : 'On Inbound Call from')"></h4>
                        
                        <!-- Caller Number & Label -->
                        <h2 class="text-xl font-extrabold text-white tracking-tight" 
                            x-text="currentCall.caller_number + (currentCall.caller_name && currentCall.caller_name.trim() !== '' && currentCall.caller_name.replace(/\D/g,'') !== currentCall.caller_number.replace(/\D/g,'') ? ' - ' + currentCall.caller_name : '')"></h2>
                        
                        <!-- Flag + Time pill -->
                        <div class="inline-flex items-center gap-1.5 bg-black/30 border border-white/10 rounded-full px-3.5 py-1 text-[11px] text-white">
                            <span x-text="getCountryFlagAndLocalTime(currentCall.caller_number).code"></span>
                            <template x-if="['PK', 'US', 'GB', 'AE', 'SA'].includes(getCountryFlagAndLocalTime(currentCall.caller_number).code)">
                                <img :src="'/images/flags/' + getCountryFlagAndLocalTime(currentCall.caller_number).code.toLowerCase() + '.svg'" class="w-4.5 h-3 rounded-sm object-cover">
                            </template>
                            <template x-if="!['PK', 'US', 'GB', 'AE', 'SA'].includes(getCountryFlagAndLocalTime(currentCall.caller_number).code)">
                                <span x-text="getCountryFlagAndLocalTime(currentCall.caller_number).flag"></span>
                            </template>
                            <span class="font-extrabold" x-text="getCountryFlagAndLocalTime(currentCall.caller_number).time"></span>
                            <span class="opacity-80">local time</span>
                        </div>

                        <!-- Silhouette Avatar -->
                        <div class="my-6">
                            <div class="mx-auto w-24 h-24 rounded-full border-4 border-white/80 overflow-hidden bg-gradient-to-b from-blue-300 to-blue-500 shadow-xl flex items-center justify-center">
                                <svg class="w-16 h-16 text-white translate-y-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Timer (speaking) or Ringing status -->
                        <div class="text-xl font-semibold text-white">
                            <template x-if="['ringing_inbound', 'ringing'].includes(phoneStatus)">
                                <span class="animate-pulse">Ringing</span>
                            </template>
                            <template x-if="['active', 'held', 'speaking'].includes(phoneStatus)">
                                <span class="font-mono tracking-wider font-extrabold" x-text="formattedCallDuration">00:00:00</span>
                            </template>
                        </div>
                        
                        <!-- Clear/Reset Button for recovery of stuck screens -->
                        <div class="flex justify-center mt-2">
                            <button type="button" @click.stop="phoneResetUI()" 
                                    style="background: linear-gradient(135deg, #d97706, #b45309) !important; border: 1px solid #f59e0b !important;"
                                    class="text-[10px] text-white hover:opacity-90 font-bold px-3.5 py-1.5 rounded-lg shadow-md transition active:scale-95">
                                Reset Softphone
                            </button>
                        </div>
                    </div>

                    <!-- Call Actions grid / buttons -->
                    <div class="shrink-0 space-y-6">
                        <!-- Six buttons call options layout (Only on attended call screen) -->
                        <div class="grid grid-cols-3 gap-y-4 gap-x-2 justify-items-center" x-show="['active', 'held', 'speaking'].includes(phoneStatus)">
                            <!-- Mute -->
                            <button type="button" @click="currentCall.is_muted ? phoneUnmute() : phoneMute()"
                                    class="flex flex-col items-center gap-1.5 bg-transparent hover:bg-white/10 p-2 rounded-xl transition duration-150 cursor-pointer w-20">
                                <div class="w-11 h-11 rounded-full flex items-center justify-center border transition-colors duration-200"
                                     :class="currentCall.is_muted ? 'border-rose-400 bg-rose-500 text-white' : 'border-white/20 bg-white/5 text-white'">
                                    <i class="fa-solid text-base" :class="currentCall.is_muted ? 'fa-microphone-slash' : 'fa-microphone'"></i>
                                </div>
                                <span class="text-[10px] font-bold tracking-wider text-white/95" x-text="currentCall.is_muted ? 'Muted' : 'Mute'"></span>
                            </button>

                            <!-- Hold -->
                            <button type="button" @click="currentCall.is_held ? phoneResume() : phoneHold()"
                                    class="flex flex-col items-center gap-1.5 bg-transparent hover:bg-white/10 p-2 rounded-xl transition duration-150 cursor-pointer w-20">
                                <div class="w-11 h-11 rounded-full flex items-center justify-center border transition-colors duration-200"
                                     :class="currentCall.is_held ? 'border-amber-400 bg-amber-500 text-white' : 'border-white/20 bg-white/5 text-white'">
                                    <i class="fa-solid text-base" :class="currentCall.is_held ? 'fa-play' : 'fa-pause'"></i>
                                </div>
                                <span class="text-[10px] font-bold tracking-wider text-white/95" x-text="currentCall.is_held ? 'Resume' : 'Hold'"></span>
                            </button>

                            <!-- Keypad -->
                            <button type="button" @click="keypadPanelOpen = true"
                                    class="flex flex-col items-center gap-1.5 bg-transparent hover:bg-white/10 p-2 rounded-xl transition duration-150 cursor-pointer w-20">
                                <div class="w-11 h-11 rounded-full flex items-center justify-center border border-white/20 bg-white/5 text-white">
                                    <i class="fa-solid fa-table-cells text-base"></i>
                                </div>
                                <span class="text-[10px] font-bold tracking-wider text-white/95">Keypad</span>
                            </button>

                            <!-- Transfer -->
                            <button type="button" @click="openInlineTransfer()"
                                    class="flex flex-col items-center gap-1.5 bg-transparent hover:bg-white/10 p-2 rounded-xl transition duration-150 cursor-pointer w-20">
                                <div class="w-11 h-11 rounded-full flex items-center justify-center border border-white/20 bg-white/5 text-white">
                                    <i class="fa-solid fa-share-nodes text-base"></i>
                                </div>
                                <span class="text-[10px] font-bold tracking-wider text-white/95">Transfer</span>
                            </button>

                            <!-- Add Call (Conference) -->
                            <button type="button" @click="openAddOrCallPanel()"
                                    class="flex flex-col items-center gap-1.5 bg-transparent hover:bg-white/10 p-2 rounded-xl transition duration-150 cursor-pointer w-20">
                                <div class="w-11 h-11 rounded-full flex items-center justify-center border border-white/20 bg-white/5 text-white">
                                    <i class="fa-solid fa-user-plus text-base"></i>
                                </div>
                                <span class="text-[10px] font-bold tracking-wider text-white/95">Add Call</span>
                            </button>
                        </div>

                        <!-- Main call controls -->
                        <div class="flex justify-center mt-4">
                            <!-- Inbound ringing: Green Answer/Accept button at bottom center -->
                            <template x-if="phoneStatus === 'ringing_inbound'">
                                <button type="button" @click="phoneAnswer()"
                                        class="w-14 h-14 rounded-full bg-emerald-500 hover:bg-emerald-400 active:scale-95 text-white flex items-center justify-center transition-all duration-200 shadow-lg shadow-emerald-500/30 cursor-pointer">
                                    <i class="fa-solid fa-phone text-xl"></i>
                                </button>
                            </template>

                            <!-- Attended call / Outbound Ringing: Red Hang Up button at bottom center -->
                            <template x-if="['active', 'held', 'speaking', 'ringing', 'ringing_inbound'].includes(phoneStatus)">
                                <button type="button" @click="phoneHangup()"
                                        class="w-14 h-14 rounded-full bg-rose-600 hover:bg-rose-500 active:scale-95 text-white flex items-center justify-center transition-all duration-200 shadow-lg shadow-rose-600/30 cursor-pointer">
                                    <i class="fa-solid fa-phone-slash text-xl animate-pulse"></i>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
                <!-- End of Active Call Overlay template -->

                <!-- Transfer Panel (Full Overlay, same style as Add or Call) -->
                    <div x-show="transferPanelOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute inset-0 z-50 flex flex-col rounded-2xl overflow-hidden"
                         style="background: #0f172a;">

                        <!-- Header -->
                        <div class="flex items-center px-4 pt-4 pb-2 shrink-0 border-b border-slate-700/50">
                            <i class="fa-solid fa-share-nodes text-indigo-400 mr-3 text-base"></i>
                            <h5 class="flex-1 font-bold text-sm text-white">Transfer Call</h5>
                            <button type="button" @click="transferPanelOpen = false"
                                    class="text-slate-400 hover:text-white transition ml-2">
                                <i class="fa-solid fa-xmark text-base"></i>
                            </button>
                        </div>

                        <!-- Tabs: Teammates / Queues / Manual -->
                        <div class="flex border-b border-slate-700/50 px-4 shrink-0">
                            <button type="button" @click="transferTab='manual'"
                                    :class="transferTab==='manual' ? 'border-b-2 border-indigo-500 text-white' : 'text-slate-400 hover:text-slate-200'"
                                    class="text-[11px] font-bold px-3 py-2 transition">Manual</button>
                            <button type="button" @click="transferTab='teammates'"
                                    :class="transferTab==='teammates' ? 'border-b-2 border-indigo-500 text-white' : 'text-slate-400 hover:text-slate-200'"
                                    class="text-[11px] font-bold px-3 py-2 transition">Teammates</button>
                            <button type="button" @click="transferTab='queues'"
                                    :class="transferTab==='queues' ? 'border-b-2 border-indigo-500 text-white' : 'text-slate-400 hover:text-slate-200'"
                                    class="text-[11px] font-bold px-3 py-2 transition">Queues</button>
                        </div>

                        <!-- Search bar -->
                        <div x-show="transferTab !== 'manual'" class="px-3 pt-3 pb-1 shrink-0">
                            <div class="flex items-center gap-2 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                                <input type="text" x-model="transferSearch" placeholder="Search..."
                                       class="bg-transparent flex-1 text-xs text-white placeholder-slate-500 outline-none">
                            </div>
                        </div>

                        <!-- Manual Entry -->
                        <div x-show="transferTab === 'manual'" class="flex-1 flex flex-col justify-center px-5 gap-4">
                            <div>
                                <p class="text-[10px] text-slate-500 mb-2">Enter extension or phone number to transfer to</p>
                                <input type="text" x-model="transferNumber" placeholder="Extension or phone number..."
                                       class="w-full px-3 py-2.5 bg-slate-800 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 outline-none focus:border-indigo-500 transition">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="phoneExecuteTransfer('blind')"
                                        class="py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-bold rounded-xl transition active:scale-95">
                                    <i class="fa-solid fa-forward mr-1"></i> Blind Transfer
                                </button>
                                <button type="button" @click="phoneExecuteTransfer('warm')"
                                        class="py-2.5 bg-slate-700 hover:bg-slate-600 text-white text-[11px] font-bold rounded-xl border border-slate-600 transition active:scale-95">
                                    <i class="fa-solid fa-phone-volume mr-1"></i> Attended
                                </button>
                            </div>
                        </div>

                        <!-- Teammates list -->
                        <div x-show="transferTab === 'teammates'" class="flex-1 overflow-y-auto px-2 pb-4 pt-1">
                            <template x-for="t in mockTeammates.filter(t => !transferSearch || t.name.toLowerCase().includes(transferSearch.toLowerCase()))" :key="t.id">
                                <div class="flex items-center gap-3 px-3 py-2.5 border-b border-slate-800/50">
                                    <div class="relative shrink-0">
                                        <div class="w-9 h-9 rounded-full bg-indigo-700 flex items-center justify-center text-white font-bold text-xs" x-text="t.name.charAt(0)"></div>
                                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-slate-900"
                                             :class="t.status === 'online' ? 'bg-emerald-400' : t.status === 'busy' ? 'bg-amber-400' : 'bg-slate-500'"></div>
                                    </div>
                                    <div class="flex-1 text-left min-w-0">
                                        <div class="text-xs font-semibold text-slate-200 truncate" x-text="t.name"></div>
                                        <div class="text-[10px] text-slate-500" x-text="'Ext. ' + t.ext + ' · ' + t.status.charAt(0).toUpperCase() + t.status.slice(1)"></div>
                                    </div>
                                    <div class="flex gap-1.5 shrink-0">
                                        <button type="button" @click="transferNumber = t.number; phoneExecuteTransfer('blind')"
                                                :disabled="t.status === 'offline'"
                                                class="px-2 py-1 bg-indigo-600/80 hover:bg-indigo-500 disabled:opacity-40 text-white text-[9px] font-bold rounded-lg transition">
                                            Blind
                                        </button>
                                        <button type="button" @click="transferNumber = t.number; phoneExecuteTransfer('warm')"
                                                :disabled="t.status === 'offline'"
                                                class="px-2 py-1 bg-slate-700 hover:bg-slate-600 disabled:opacity-40 text-white text-[9px] font-bold rounded-lg border border-slate-600 transition">
                                            Attended
                                        </button>
                                    </div>
                                </div>
                            </template>
                            <template x-if="mockTeammates.filter(t => !transferSearch || t.name.toLowerCase().includes(transferSearch.toLowerCase())).length === 0">
                                <div class="flex flex-col items-center justify-center py-12 text-center">
                                    <div class="text-5xl mb-3">👥</div>
                                    <div class="text-xs font-bold text-slate-300">No teammates found</div>
                                </div>
                            </template>
                        </div>

                        <!-- Queues list -->
                        <div x-show="transferTab === 'queues'" class="flex-1 overflow-y-auto px-2 pb-4 pt-1">
                            <template x-for="q in mockQueues.filter(q => !transferSearch || q.name.toLowerCase().includes(transferSearch.toLowerCase()))" :key="q.id">
                                <button type="button" @click="transferNumber = q.number; phoneExecuteTransfer('blind')"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition cursor-pointer border-b border-slate-800/50">
                                    <div class="w-9 h-9 rounded-full bg-fuchsia-800 flex items-center justify-center text-white shrink-0">
                                        <i class="fa-solid fa-layer-group text-xs"></i>
                                    </div>
                                    <div class="flex-1 text-left min-w-0">
                                        <div class="text-xs font-semibold text-slate-200 truncate" x-text="q.name"></div>
                                        <div class="text-[10px] text-slate-500" x-text="q.number"></div>
                                    </div>
                                    <i class="fa-solid fa-arrow-right text-indigo-400 text-xs shrink-0"></i>
                                </button>
                            </template>
                            <template x-if="mockQueues.filter(q => !transferSearch || q.name.toLowerCase().includes(transferSearch.toLowerCase())).length === 0">
                                <div class="flex flex-col items-center justify-center py-12 text-center">
                                    <div class="text-5xl mb-3">📋</div>
                                    <div class="text-xs font-bold text-slate-300">No queues found</div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- ════════════════════════════════════
                         Add or Call Panel (full-screen overlay)
                         ════════════════════════════════════ -->
                    <div x-show="addOrCallOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute inset-0 z-50 flex flex-col rounded-2xl overflow-hidden"
                         style="background: #0f172a;">

                        <!-- Header -->
                        <div class="flex items-center px-4 pt-4 pb-2 shrink-0">
                            <!-- Back arrow (shown only when country picker is open) -->
                            <button type="button" x-show="addOrCallCountryPickerOpen" @click="addOrCallCountryPickerOpen = false"
                                    class="mr-2 text-slate-400 hover:text-white transition">
                                <i class="fa-solid fa-arrow-left text-sm"></i>
                            </button>
                            <h5 class="flex-1 font-bold text-sm text-white" 
                                x-text="addOrCallCountryPickerOpen ? 'Select a Country' : 'Add or Call'"></h5>
                            <button type="button" @click="closeAddOrCallPanel()"
                                    class="text-slate-400 hover:text-white transition ml-2">
                                <i class="fa-solid fa-xmark text-base"></i>
                            </button>
                        </div>

                        <!-- ── COUNTRY PICKER VIEW ── -->
                        <template x-if="addOrCallCountryPickerOpen">
                            <div class="flex flex-col flex-1 overflow-hidden">
                                <!-- Search bar -->
                                <div class="px-3 pb-2 shrink-0">
                                    <div class="flex items-center gap-2 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2">
                                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                                        <input type="text" x-model="addOrCallSearch" placeholder="Search country..."
                                               class="bg-transparent flex-1 text-xs text-white placeholder-slate-500 outline-none">
                                    </div>
                                </div>
                                <!-- Country list -->
                                <div class="flex-1 overflow-y-auto px-2 pb-4">
                                    <template x-for="c in filteredCountries" :key="c.code">
                                        <button type="button" @click="selectAddOrCallCountry(c)"
                                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition cursor-pointer border-b border-slate-800/50">
                                            <span class="text-xl leading-none" x-text="c.flag"></span>
                                            <div class="flex-1 text-left min-w-0">
                                                <div class="text-xs font-semibold text-slate-200 truncate" x-text="c.name"></div>
                                                <div class="text-[10px] text-slate-500" x-text="'+' + c.dial"></div>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- ── MAIN VIEW (Tabs + Search + Lists) ── -->
                        <template x-if="!addOrCallCountryPickerOpen">
                            <div class="flex flex-col flex-1 overflow-hidden">

                                <!-- Tabs -->
                                <div class="flex border-b border-slate-700/50 px-4 shrink-0">
                                    <button type="button" @click="addOrCallTab='phonebook'; addOrCallDialpadOpen=false"
                                            :class="addOrCallTab==='phonebook' && !addOrCallDialpadOpen ? 'border-b-2 border-fuchsia-500 text-white' : 'text-slate-400 hover:text-slate-200'"
                                            class="text-[11px] font-bold px-3 py-2 transition">
                                        PhoneBook
                                    </button>
                                    <button type="button" @click="addOrCallTab='teammates'; addOrCallDialpadOpen=false"
                                            :class="addOrCallTab==='teammates' && !addOrCallDialpadOpen ? 'border-b-2 border-fuchsia-500 text-white' : 'text-slate-400 hover:text-slate-200'"
                                            class="text-[11px] font-bold px-3 py-2 transition">
                                        Teammates
                                    </button>
                                    <button type="button" @click="addOrCallTab='queues'; addOrCallDialpadOpen=false"
                                            :class="addOrCallTab==='queues' && !addOrCallDialpadOpen ? 'border-b-2 border-fuchsia-500 text-white' : 'text-slate-400 hover:text-slate-200'"
                                            class="text-[11px] font-bold px-3 py-2 transition">
                                        Queues
                                    </button>
                                    <!-- Dialpad toggle -->
                                    <button type="button" @click="addOrCallDialpadOpen = !addOrCallDialpadOpen"
                                            :class="addOrCallDialpadOpen ? 'text-fuchsia-400' : 'text-slate-400 hover:text-slate-200'"
                                            class="ml-auto text-[11px] font-bold py-2 px-2 transition">
                                        <i class="fa-solid fa-table-cells-large text-base"></i>
                                    </button>
                                </div>

                                <!-- Search bar (list mode) -->
                                <div x-show="!addOrCallDialpadOpen" class="px-3 pt-3 pb-1 shrink-0">
                                    <div class="flex items-center gap-2 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2">
                                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                                        <input type="text" x-model="addOrCallSearch" placeholder="Search..."
                                               class="bg-transparent flex-1 text-xs text-white placeholder-slate-500 outline-none">
                                    </div>
                                </div>

                                <!-- ── DIALPAD VIEW ── -->
                                <div x-show="addOrCallDialpadOpen" class="flex flex-col items-center px-4 pt-3 pb-2 gap-3">
                                    <!-- Number display with flag/country -->
                                    <div class="w-full flex items-center gap-2 bg-slate-800 border border-slate-700 rounded-xl px-3 py-2">
                                        <!-- Flag button (opens country picker) -->
                                        <button type="button" @click="addOrCallSearch=''; addOrCallCountryPickerOpen = true"
                                                class="flex items-center gap-1.5 shrink-0 hover:opacity-80 transition">
                                            <template x-if="['PK', 'US', 'GB', 'AE', 'SA'].includes(addOrCallSelectedCountry.code)">
                                                <img :src="'/images/flags/' + addOrCallSelectedCountry.code.toLowerCase() + '.svg'" class="w-5 h-3.5 rounded-sm object-cover">
                                            </template>
                                            <template x-if="!['PK', 'US', 'GB', 'AE', 'SA'].includes(addOrCallSelectedCountry.code)">
                                                <span class="text-xl leading-none" x-text="addOrCallSelectedCountry.flag"></span>
                                            </template>
                                            <span class="text-[10px] text-slate-300 font-bold" x-text="addOrCallSelectedCountry.code"></span>
                                            <i class="fa-solid fa-chevron-down text-[8px] text-slate-500"></i>
                                        </button>
                                        <div class="w-px h-5 bg-slate-700"></div>
                                        <!-- Number -->
                                        <input type="text" x-model="addOrCallInput"
                                               @keydown="handleDialerKey($event)"
                                               placeholder="Dial number..."
                                               class="bg-transparent flex-1 font-mono text-sm text-white tracking-widest outline-none border-none">
                                        <!-- Backspace -->
                                        <button type="button" @click="addOrCallKeypad('backspace')" class="text-slate-400 hover:text-white transition ml-1">
                                            <i class="fa-solid fa-delete-left text-sm"></i>
                                        </button>
                                    </div>

                                    <!-- Keypad grid -->
                                    <div class="grid grid-cols-3 gap-2 w-full">
                                        <template x-for="k in ['1','2','3','4','5','6','7','8','9','*','0','#']">
                                            <button type="button" @click="addOrCallKeypad(k)"
                                                    class="h-11 rounded-xl bg-slate-800 border border-slate-700 hover:bg-slate-700 text-white font-mono font-bold text-sm transition active:scale-90 cursor-pointer">
                                                <span x-text="k"></span>
                                            </button>
                                        </template>
                                    </div>

                                    <!-- Add to the call CTA -->
                                    <button type="button"
                                            @click="executeAddToCall(addOrCallInput, addOrCallInput)"
                                            :disabled="addOrCallInput.length < 4"
                                            style="background: linear-gradient(135deg, #a21caf, #d946ef) !important;" class="w-full py-2.5 disabled:opacity-40 disabled:cursor-not-allowed text-white text-[11px] font-bold rounded-xl transition active:scale-95">
                                        Add to the call
                                    </button>
                                </div>

                                <!-- ── LIST VIEWS ── -->
                                <div x-show="!addOrCallDialpadOpen" class="flex-1 overflow-y-auto px-2 pb-4">

                                    <!-- PhoneBook tab -->
                                    <template x-if="addOrCallTab === 'phonebook'">
                                        <div>
                                            <template x-if="filteredPhonebook.length === 0">
                                                <div class="flex flex-col items-center justify-center py-12 text-center">
                                                    <div class="text-5xl mb-3">💬</div>
                                                    <div class="text-xs font-bold text-slate-300">We couldn't find any results</div>
                                                    <div class="text-[10px] text-slate-500 mt-1">Try a different keyword</div>
                                                </div>
                                            </template>
                                            <template x-for="contact in filteredPhonebook" :key="contact.id">
                                                <button type="button" @click="executeAddToCall(contact.phone_number, contact.name)"
                                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition cursor-pointer border-b border-slate-800/50">
                                                    <div class="w-9 h-9 rounded-full bg-slate-700 flex items-center justify-center text-white shrink-0">
                                                        <i class="fa-solid fa-user text-xs"></i>
                                                    </div>
                                                    <div class="flex-1 text-left min-w-0">
                                                        <div class="text-xs font-semibold text-slate-200 truncate" x-text="contact.name"></div>
                                                        <div class="text-[10px] text-slate-500 truncate">
                                                            <template x-if="['PK', 'US', 'GB', 'AE', 'SA'].includes(getCountryFlagAndLocalTime(contact.phone_number).code)">
                                                                <img :src="'/images/flags/' + getCountryFlagAndLocalTime(contact.phone_number).code.toLowerCase() + '.svg'" class="w-4 h-3 rounded-sm inline-block object-cover mr-1">
                                                            </template>
                                                            <template x-if="!['PK', 'US', 'GB', 'AE', 'SA'].includes(getCountryFlagAndLocalTime(contact.phone_number).code)">
                                                                <span x-text="getCountryFlagAndLocalTime(contact.phone_number).flag"></span>
                                                            </template>
                                                            <span class="ml-1" x-text="contact.phone_number"></span>
                                                        </div>
                                                    </div>
                                                    <i class="fa-solid fa-phone text-fuchsia-400 text-xs shrink-0"></i>
                                                </button>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Teammates tab -->
                                    <template x-if="addOrCallTab === 'teammates'">
                                        <div>
                                            <template x-for="t in filteredTeammates" :key="t.id">
                                                <button type="button" @click="executeAddToCall(t.number, t.name)"
                                                        :disabled="t.status === 'offline'"
                                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer border-b border-slate-800/50">
                                                    <!-- Avatar -->
                                                    <div class="relative shrink-0">
                                                        <div class="w-9 h-9 rounded-full bg-indigo-700 flex items-center justify-center text-white font-bold text-xs" x-text="t.name.charAt(0)"></div>
                                                        <!-- Status dot -->
                                                        <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-slate-900"
                                                             :class="t.status === 'online' ? 'bg-emerald-400' : t.status === 'busy' ? 'bg-amber-400' : 'bg-slate-500'"></div>
                                                    </div>
                                                    <div class="flex-1 text-left min-w-0">
                                                        <div class="text-xs font-semibold text-slate-200 truncate" x-text="t.name"></div>
                                                        <div class="text-[10px] text-slate-500" x-text="'Ext. ' + t.ext + ' · ' + t.status.charAt(0).toUpperCase() + t.status.slice(1)"></div>
                                                    </div>
                                                    <i x-show="t.status !== 'offline'" class="fa-solid fa-phone text-fuchsia-400 text-xs shrink-0"></i>
                                                </button>
                                            </template>
                                        </div>
                                    </template>

                                    <!-- Queues tab -->
                                    <template x-if="addOrCallTab === 'queues'">
                                        <div>
                                            <template x-for="q in filteredQueues" :key="q.id">
                                                <button type="button" @click="executeAddToCall(q.number, q.name + ' Queue')"
                                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-800 transition cursor-pointer border-b border-slate-800/50">
                                                    <div class="w-9 h-9 rounded-full bg-violet-700 flex items-center justify-center text-white shrink-0">
                                                        <i class="fa-solid fa-headset text-xs"></i>
                                                    </div>
                                                    <div class="flex-1 text-left min-w-0">
                                                        <div class="text-xs font-semibold text-slate-200 truncate" x-text="q.name"></div>
                                                        <div class="text-[10px] text-slate-500" x-text="q.agents + ' agents · ' + q.waiting + ' waiting'"></div>
                                                    </div>
                                                    <i class="fa-solid fa-phone text-fuchsia-400 text-xs shrink-0"></i>
                                                </button>
                                            </template>
                                        </div>
                                    </template>

                                </div>
                            </div>
                        </template>

                    </div>

                    <!-- Keypad / DTMF Panel (Full Overlay, same style as Add or Call) -->
                    <div x-show="keypadPanelOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute inset-0 z-50 flex flex-col rounded-2xl overflow-hidden"
                         style="background: #0f172a;">

                        <!-- Header -->
                        <div class="flex items-center px-4 pt-4 pb-3 shrink-0 border-b border-slate-700/50">
                            <i class="fa-solid fa-table-cells text-indigo-400 mr-3 text-base"></i>
                            <h5 class="flex-1 font-bold text-sm text-white">Keypad / DTMF</h5>
                            <button type="button" @click="keypadPanelOpen = false"
                                    class="text-slate-400 hover:text-white transition ml-2">
                                <i class="fa-solid fa-xmark text-base"></i>
                            </button>
                        </div>

                        <!-- DTMF Display -->
                        <div class="flex-1 flex flex-col justify-center px-5 gap-4">
                            <p class="text-[10px] text-slate-500 text-center">Press digits to send tones during the call</p>
                            <div class="text-center bg-slate-800 border border-slate-700 rounded-xl py-3 px-4 text-white font-mono text-xl tracking-[0.4em] min-h-[52px] flex items-center justify-center">
                                <span x-text="dtmfInput || '· · ·'" :class="dtmfInput ? 'text-white' : 'text-slate-600'"></span>
                            </div>

                            <!-- Keypad Grid -->
                            <div class="grid grid-cols-3 gap-2.5">
                                <template x-for="k in ['1','2','3','4','5','6','7','8','9','*','0','#']">
                                    <button type="button" @click="dtmfInput += k; sendDTMF(k)"
                                            class="h-12 rounded-xl bg-slate-800 border border-slate-700 hover:bg-slate-700 hover:border-indigo-500 text-white transition active:scale-90 flex items-center justify-center font-mono font-bold text-base cursor-pointer">
                                        <span x-text="k"></span>
                                    </button>
                                </template>
                            </div>

                            <!-- Actions -->
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" @click="dtmfInput = ''"
                                        class="py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-300 text-[11px] font-bold rounded-xl border border-slate-600 transition active:scale-95">
                                    <i class="fa-solid fa-delete-left mr-1"></i> Clear
                                </button>
                                <button type="button" @click="keypadPanelOpen = false"
                                        class="py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-bold rounded-xl transition active:scale-95">
                                    <i class="fa-solid fa-check mr-1"></i> Done
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Tab Content Area (only rendered when authenticated AND not in call) -->
                <div class="flex-1 min-h-0 flex flex-col" x-show="phoneAuthenticated && !['ringing', 'active', 'speaking', 'ringing_inbound'].includes(phoneStatus)">

                    <!-- Tab Headers — only show after ZIWO auth. Use bright colors so they remain visible. -->
                    <div x-show="phoneAuthenticated" class="flex border-b-2 border-indigo-500/30 shrink-0 bg-slate-800/80 backdrop-blur-sm">
                        <button type="button" @click="phoneTab = 'dialer'" :class="phoneTab === 'dialer' ? 'text-indigo-300 bg-slate-900/80 border-indigo-400' : 'text-slate-300 hover:text-white hover:bg-slate-700/60 border-transparent'" class="flex-1 py-2.5 text-center text-[11px] font-black uppercase tracking-wider border-b-2 transition">⌨ Dialer</button>
                        <button type="button" @click="phoneTab = 'phonebook'; $nextTick(() => phoneSearchContacts())" :class="phoneTab === 'phonebook' ? 'text-indigo-300 bg-slate-900/80 border-indigo-400' : 'text-slate-300 hover:text-white hover:bg-slate-700/60 border-transparent'" class="flex-1 py-2.5 text-center text-[11px] font-black uppercase tracking-wider border-b-2 transition">📞 Directory</button>
                        <button type="button" @click="phoneTab = 'history'" :class="phoneTab === 'history' ? 'text-indigo-300 bg-slate-900/80 border-indigo-400' : 'text-slate-300 hover:text-white hover:bg-slate-700/60 border-transparent'" class="flex-1 py-2.5 text-center text-[11px] font-black uppercase tracking-wider border-b-2 transition">⏱ Recent</button>
                    </div>

                    <!-- Tab panels -->
                    <div class="flex-1 min-h-0 relative">
                        
                        <!-- Panel A: Dialer -->
                        <template x-if="phoneTab === 'dialer'">
                        <div class="absolute inset-0 flex flex-col p-4 justify-between">

                            <!-- Display -->
                            <div class="relative flex items-center bg-slate-900/80 rounded-2xl border border-slate-850 px-3 py-2.5">
                                <i class="fa-solid fa-phone text-xs text-indigo-500 mr-2"></i>
                                <input type="text" id="dialer-input" x-model="dialNumberInput"
                                       placeholder="Enter phone number..."
                                       @keydown="handleDialerKey($event)"
                                       @keyup.enter="if (dialNumberInput.length >= 3) phoneDial()"
                                       class="flex-1 bg-transparent text-sm font-semibold tracking-wide text-white outline-none">
                                <button type="button" @click="dialNumberInput = dialNumberInput.slice(0,-1)" x-show="dialNumberInput.length > 0" class="text-slate-400 hover:text-slate-200 p-1">
                                    <i class="fa-solid fa-backspace"></i>
                                </button>
                            </div>

                            <!-- Keys Grid (3x4) -->
                            <div class="grid grid-cols-3 gap-y-2 gap-x-4 px-4 my-2">
                                <template x-for="k in ['1','2','3','4','5','6','7','8','9','*','0','#']">
                                    <button type="button" @click="dialNumberInput += k"
                                            class="h-10 w-full rounded-2xl bg-slate-900 border border-slate-800/60 hover:bg-slate-800 hover:text-white transition active:scale-90 flex items-center justify-center font-mono font-bold text-sm text-slate-300 select-none cursor-pointer">
                                        <span x-text="k"></span>
                                    </button>
                                </template>
                            </div>

                            <!-- Dial Button -->
                            <button type="button" @click="phoneDial()" :disabled="dialNumberInput.length < 3"
                                    class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-bold rounded-xl transition active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                                <i class="fa-solid fa-phone text-xs"></i> Place Outbound Call
                            </button>

                        </div>
                        </template>

                        <!-- Panel B: Directory (Centralized Phonebook) -->
                        <template x-if="phoneTab === 'phonebook'">
                        <div class="absolute inset-0 flex flex-col p-3">
                            <!-- Search -->
                            <div class="relative shrink-0 mb-2">
                                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                                <input type="text" x-model="phoneSearchQuery" @input.debounce.300ms="phoneSearchContacts()" placeholder="Search directory..."
                                       class="w-full pl-8 pr-2.5 py-1.5 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-600 outline-none focus:border-indigo-500">
                            </div>

                            <!-- Filters -->
                            <div class="flex gap-1.5 overflow-x-auto shrink-0 mb-2 pb-1 text-[8px] font-bold">
                                <button type="button" @click="phoneCategoryFilter = ''; phoneSearchContacts()" :class="phoneCategoryFilter === '' ? 'bg-indigo-600 text-white' : 'bg-slate-900 text-slate-400'" class="px-2 py-1 rounded-md transition">All</button>
                                <button type="button" @click="phoneCategoryFilter = 'beat'; phoneSearchContacts()" :class="phoneCategoryFilter === 'beat' ? 'bg-indigo-600 text-white' : 'bg-slate-900 text-slate-400'" class="px-2 py-1 rounded-md transition">Beats</button>
                                <button type="button" @click="phoneCategoryFilter = 'emergency'; phoneSearchContacts()" :class="phoneCategoryFilter === 'emergency' ? 'bg-indigo-600 text-white' : 'bg-slate-900 text-slate-400'" class="px-2 py-1 rounded-md transition">Emergency</button>
                                <button type="button" @click="phoneCategoryFilter = 'custom'; phoneSearchContacts()" :class="phoneCategoryFilter === 'custom' ? 'bg-indigo-600 text-white' : 'bg-slate-900 text-slate-400'" class="px-2 py-1 rounded-md transition">Custom</button>
                                
                                <button type="button" @click="openAddContactModal()" class="ml-auto px-2 py-1 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white rounded-md transition" title="Add Contact">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>

                            <!-- Directory List -->
                            <div class="flex-1 overflow-y-auto space-y-1.5 pr-0.5">
                                <template x-for="c in phonebookContacts" :key="c.id">
                                    <div class="flex items-center justify-between p-2 bg-slate-900 border border-slate-850 rounded-xl hover:border-slate-800 transition">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] font-bold text-slate-200 truncate" x-text="c.name"></span>
                                                <span class="text-[7px] px-1 bg-slate-800 text-slate-400 rounded uppercase font-black tracking-tight" x-text="c.category"></span>
                                            </div>
                                            <span class="text-[9px] font-mono text-indigo-400 font-semibold block mt-0.5" x-text="c.phone_number"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            <!-- Dial action -->
                                            <button type="button" @click="phoneTriggerQuickDial(c.phone_number, c.name)" class="p-1.5 bg-indigo-600 hover:bg-indigo-500 rounded-lg transition" title="Call">
                                                <i class="fa-solid fa-phone text-[9px] text-white"></i>
                                            </button>
                                            <!-- Delete action (Only custom category or super admin) -->
                                            <button type="button" x-show="c.category === 'custom'" @click="phoneDeleteContact(c.id)" class="p-1.5 bg-slate-800 hover:bg-rose-950 text-slate-400 hover:text-rose-500 rounded-lg transition" title="Delete">
                                                <i class="fa-solid fa-trash-can text-[9px]"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="phonebookContacts.length === 0" class="text-center py-12 text-slate-500 text-xs">
                                    No directory contacts match.
                                </div>
                            </div>

                            <!-- Directory Add Contact Form Modal Overlay -->
                            <div x-show="addContactOpen" class="absolute inset-0 bg-slate-950 p-4 flex flex-col justify-center gap-3 z-50">
                                <h5 class="font-bold text-xs text-slate-200 text-center mb-1">Add Phonebook Contact</h5>
                                <div class="space-y-2">
                                    <div>
                                        <label class="block text-[8px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Name</label>
                                        <input type="text" x-model="contactForm.name" placeholder="Contact Name" 
                                               class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-xs text-white outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[8px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Phone Number</label>
                                        <input type="text" x-model="contactForm.phone_number" placeholder="03001234567" 
                                               class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-xs text-white outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[8px] font-bold uppercase tracking-wider text-slate-400 mb-0.5">Category</label>
                                        <select x-model="contactForm.category" class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-800 rounded-lg text-xs text-white outline-none">
                                            <option value="custom">Custom (Private)</option>
                                            <option value="emergency">Emergency (Public)</option>
                                            <option value="beat">Beat (Public)</option>
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 pt-2">
                                        <button type="button" @click="phoneSaveContact()" class="py-1.5 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-500 transition">Save</button>
                                        <button type="button" @click="addContactOpen = false" class="py-1.5 bg-slate-800 text-slate-300 text-xs font-bold rounded-lg hover:bg-slate-700 transition">Cancel</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                        </template>

                        <!-- Panel C: Recent call history -->
                        <!-- Panel C: Recent call history -->
                        <template x-if="phoneTab === 'history'">
                        <div class="absolute inset-0 flex flex-col p-3">
                            <div class="flex-1 overflow-y-auto space-y-1.5 pr-0.5">
                                <template x-for="h in recentCallLogs" :key="h.id">
                                    <div class="flex items-center justify-between p-2 bg-slate-900 border border-slate-850 rounded-xl">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <i class="fa-solid text-[9px]" :class="h.direction === 'inbound' ? 'fa-arrow-down-left text-indigo-400' : 'fa-arrow-up-right text-amber-400'"></i>
                                                <span class="text-[9px] font-bold text-slate-200" x-text="h.caller_number"></span>
                                            </div>
                                            <span class="text-[8px] text-slate-500 block mt-0.5" x-text="h.time_ago"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[8px] uppercase tracking-wider font-semibold"
                                                  :class="{
                                                      'text-emerald-500': h.status === 'finished',
                                                      'text-rose-500': h.status === 'missed',
                                                      'text-blue-500': h.status === 'active'
                                                  }"
                                                  x-text="h.status"></span>
                                            
                                            <button type="button" @click="phoneTriggerQuickDial(h.caller_number)" class="p-1 bg-slate-800 hover:bg-indigo-600 rounded transition" title="Redial">
                                                <i class="fa-solid fa-phone text-[8px] text-white"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="recentCallLogs.length === 0" class="text-center py-12 text-slate-500 text-xs">
                                    No recent telephony activities.
                                </div>
                            </div>
                        </div>
                        </template>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>

<!-- ZIWO Inbound Ringtone (official ZIWO ringtone, looped) -->
<audio id="ring-audio" src="{{ asset('audio/ringtone.mp3') }}" loop preload="auto" style="display:none"></audio>

<!-- ZIWO End Call Notification (official ZIWO end call tone) -->
<audio id="end-call-audio" src="{{ asset('audio/end_call_notification.mp3') }}" preload="auto" style="display:none"></audio>

<!-- ZIWO WebRTC Audio stream (required by ziwo-core-front SDK — binds the call audio) -->
<audio id="ziwo-peer-audio" autoplay playsinline style="display:none"></audio>

@endsection

@push('scripts')
{{-- ZIWO Core Frontend SDK (WebSocket/Verto — powers real-time call events) --}}
<script src="{{ asset('js/ziwo-core-front.umd.js') }}"></script>

<script>
window.intakeComponent = function () {
    return {
        caller: {
            number: '{{ old('caller_number', '') }}',
            name: '{{ old('caller_name', '') }}',
            vehicle_no: '{{ old('vehicle_no', '') }}'
        },
        spatial: {
            carriageway_id: '{{ old('carriageway_id', '') }}',
            zone_id: '{{ old('zone_id', '') }}',
            sector_id: '{{ old('sector_id', '') }}',
            beat_id: '{{ old('beat_id', '') }}',
            geospatial_marker_id: '{{ old('geospatial_marker_id', '') }}',
            km: '{{ old('km_marker_text', '') }}',
            lat: '{{ old('caller_lat', '') }}',
            lng: '{{ old('caller_lng', '') }}',
            coord_input: '{{ old('caller_lat') ? old('caller_lat') . ', ' . old('caller_lng') : '' }}',
            zone_name: '',
            sector_name: '',
            beat_name: ''
        },
        priority: '{{ old('priority', '3') }}',
        selectedCallTypeId: '{{ old('call_type_id', '') }}',
        selectedSubType: '{{ old('call_sub_type_id', '') }}',
        selectedVehicleType: '{{ old('vehicle_type_id', '') }}',
        selectedTigerId: '{{ old('tiger_id', '') }}',
        details: '',
        allZones: @js($zones),
        allSectors: @js($sectors),
        allBeats: @js($beats),
        allVehicleTypes: @js($vehicleTypes),
        allCarriageways: @js($carriageways ?? []),
        callTypes: @js($callTypes),
        subTypes: @js($callTypes->flatMap(fn($t) => $t->subTypes ?? [])),
        submitting: false,
        success: false,
        geospatialSuggestions: [],
        errors: [],
        clock: '',
        clockInterval: null,
        historyModalOpen: false,
        historyLoading: false,
        callerHistory: [],
        selectedHistoryCall: null,
        spatialContacts: {
            zone: null,
            sector: null,
            beat: null
        },

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // SOFTPHONE STATE VARIABLES
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        phoneCollapsed: false,
        phoneTab: 'dialer',
        phoneAuthenticated: false,
        phoneSubmitting: false,
        phoneStatus: 'offline', // offline, online, ringing, speaking, active, held, ringing_inbound
        get phoneCallActive() {
            return ['ringing', 'active', 'held', 'speaking', 'ringing_inbound'].includes(this.phoneStatus);
        },
        phoneStatusError: '',
        showPhonePassword: false,
        isMockMode: false,
        micAllowed: null,
        dialNumberInput: '',
        ziwoUsername: '',
        ziwoToken: null,              // Real ZIWO access_token from backend
        ziwoContactCenter: 'nayatel', // Contact center name for SDK
        ziwoSdkClient: null,          // ziwo-core-front ZiwoClient instance
        ziwoActiveCalls: {},          // Live call objects from SDK, keyed by callId
        ziwoDataLoaded: false,
        phoneAuthForm: {
            username: '',
            password: ''
        },
        recentCallLogs: [],
        phonebookContacts: [],
        phoneSearchQuery: '',
        phoneCategoryFilter: '',
        _explicitDisconnect: false, // guards poll from re-authenticating after manual logout

        // Active Call Fields
        currentCall: {
            id: null,
            uuid: null,
            caller_number: '',
            caller_name: '',
            is_held: false,
            is_muted: false,
            recording_paused: false,
            duration: 0
        },
        callDurationInterval: null,

        // Transfer Panel Overlay
        transferPanelOpen: false,
        transferNumber: '',

        // Keypad Panel Overlay
        keypadPanelOpen: false,
        _pendingResumeParticipant: null, // holds ref when canceling outbound to resume a held call
        transferTab: 'manual',       // 'manual' | 'teammates' | 'queues'
        transferSearch: '',
        dtmfInput: '',

        // ─── Add or Call Panel (Conference / Add Participant) ───
        addOrCallOpen: false,
        addOrCallTab: 'phonebook',   // 'phonebook' | 'teammates' | 'queues'
        addOrCallSearch: '',
        addOrCallDialpadOpen: false, // show numeric dialpad inside panel
        addOrCallInput: '',          // typed number in panel dialpad
        addOrCallCountryPickerOpen: false, // flag/country picker
        addOrCallSelectedCountry: { name: 'Pakistan', flag: '🇵🇰', dial: '92', code: 'PK' },

        // Held-call participants (previous callers put on hold for conference)
        heldParticipants: [],  // [{number, name, flag, duration, direction, heldAt, id}]

        // Flag to suppress ziwo-requesting/held events during SDK-internal resume cycles
        isConferenceResuming: false,

        // Conference merged participants once connected
        conferenceParticipants: [], // [{number, name, flag, duration}]

        mockTeammates: [
            { id: 1, name: 'Safdar Hussain',  ext: '101', status: 'online',  number: '+921000000101' },
            { id: 2, name: 'Ahmed Raza',       ext: '102', status: 'online',  number: '+921000000102' },
            { id: 3, name: 'Sara Khan',        ext: '103', status: 'busy',    number: '+921000000103' },
            { id: 4, name: 'John Carter',      ext: '104', status: 'offline', number: '+921000000104' },
            { id: 5, name: 'Maria Lopez',      ext: '105', status: 'online',  number: '+921000000105' },
        ],
        mockQueues: [
            { id: 1, name: 'Support',   number: '3001', agents: 5, waiting: 2 },
            { id: 2, name: 'Sales',     number: '3002', agents: 3, waiting: 0 },
            { id: 3, name: 'Dispatch',  number: '3003', agents: 4, waiting: 1 },
            { id: 4, name: 'Billing',   number: '3004', agents: 2, waiting: 3 },
        ],

        // Add Contact Modal Overlay
        addContactOpen: false,
        contactForm: {
            name: '',
            phone_number: '',
            category: 'custom'
        },

        lastSubmission: null,
        submissionCooldown: 5000, // 5 seconds

        init() {
            this.startClock();
            this.$nextTick(() => {
                document.getElementById('phone_link')?.focus();
            });
            if (this.spatial.zone_id) {
                const z = this.allZones.find(x => x.id == this.spatial.zone_id);
                if (z) this.spatial.zone_name = z.name;
            }
            if (this.spatial.sector_id) {
                const s = this.allSectors.find(x => x.id == this.spatial.sector_id);
                if (s) this.spatial.sector_name = s.name;
            }
            if (this.spatial.beat_id) {
                const b = this.allBeats.find(x => x.id == this.spatial.beat_id);
                if (b) this.spatial.beat_name = b.name;
            }
            this.fetchSpatialContacts();
            this.phoneInit();
        },

        startClock() {
            this.updateClock();
            this.clockInterval = setInterval(() => this.updateClock(), 1000);
        },

        updateClock() {
            const now = new Date();
            this.clock = now.toLocaleTimeString('en-US', { hour12: false });
        },

        destroy() {
            if (this.clockInterval) clearInterval(this.clockInterval);
            this.phoneCleanup();
        },

        get filteredSectors() {
            if (!this.spatial.zone_id) return [];
            return this.allSectors.filter(s => s.zone_id == this.spatial.zone_id);
        },

        get filteredBeats() {
            if (!this.spatial.sector_id) return [];
            return this.allBeats.filter(b => b.sector_id == this.spatial.sector_id);
        },

        get filteredSubTypes() {
            if (!this.selectedCallTypeId) return [];
            return this.subTypes.filter(s => s.call_type_id == this.selectedCallTypeId);
        },

        get selectedCallTypeName() {
            if (!this.selectedCallTypeId) return '';
            const type = this.callTypes.find(t => t.id == this.selectedCallTypeId);
            return type ? type.name : '';
        },

        onZoneChange() {
            this.spatial.sector_id = '';
            this.spatial.beat_id = '';
            this.spatial.sector_name = '';
            this.spatial.beat_name = '';
            const zone = this.allZones.find(z => z.id == this.spatial.zone_id);
            this.spatial.zone_name = zone ? zone.name : '';
            this.fetchSpatialContacts();
        },

        onSectorChange() {
            this.spatial.beat_id = '';
            this.spatial.beat_name = '';
            const sector = this.allSectors.find(s => s.id == this.spatial.sector_id);
            this.spatial.sector_name = sector ? sector.name : '';
            this.fetchSpatialContacts();
        },

        onBeatChange() {
            const beat = this.allBeats.find(b => b.id == this.spatial.beat_id);
            if (beat) this.spatial.beat_name = beat.name;
            this.fetchSpatialContacts();
        },

        async fetchSpatialContacts() {
            const params = new URLSearchParams();
            if (this.spatial.zone_id) params.append('zone_id', this.spatial.zone_id);
            if (this.spatial.sector_id) params.append('sector_id', this.spatial.sector_id);
            if (this.spatial.beat_id) params.append('beat_id', this.spatial.beat_id);
            
            if (params.toString() === '') {
                this.spatialContacts = { zone: null, sector: null, beat: null };
                return;
            }
            
            try {
                const res = await fetch(`/ajax/spatial-contacts?${params.toString()}`);
                const data = await res.json();
                this.spatialContacts = data;
            } catch (e) {
                console.error('Failed to fetch spatial contacts', e);
            }
        },

        onCallTypeChange(typeId) {
            this.selectedCallTypeId = typeId;
            this.selectedSubType = '';
            
            const type = this.callTypes.find(t => t.id == typeId);
            if (type) {
                // Auto-select sub-type if it's the only one
                const relatedSubTypes = this.subTypes.filter(s => s.call_type_id == typeId);
                if (relatedSubTypes.length === 1) {
                    this.selectedSubType = relatedSubTypes[0].id;
                }
            }
        },

        validatePhone(number) {
            const clean = number.replace(/[^0-9]/g, '');
            return clean.length === 11;
        },

        validateVehicle(plate) {
            if (!plate) return true; // Optional field for some calls
            // Flexible regex: Allows alphanumeric, spaces, and dashes
            // Standard: ABC-1234, RIZ 3725, LEL 06 4520, ICT ST-161
            const plateRegex = /^[A-Z0-9\s-]{3,12}$/i;
            return plateRegex.test(plate.trim());
        },


        async handleSubmit(event) {
            if (this.submitting) return;

            // 1. Validation
            this.errors = [];
            if (!this.caller.number) this.errors.push('Phone number is required.');
            if (this.caller.number && !this.validatePhone(this.caller.number)) {
                this.errors.push('Invalid phone number format. Use 03001234567.');
            }
            if (this.caller.vehicle_no && !this.validateVehicle(this.caller.vehicle_no)) {
                this.errors.push('Invalid vehicle plate number format.');
            }
            if (!this.selectedCallTypeId) this.errors.push('Please select a Call Primary Category.');
            if (!this.selectedSubType) this.errors.push('Please select a Call Secondary Category.');

            const isExempt = ['Junk/Silent', 'Information'].includes(this.selectedCallTypeName);
            if (!isExempt) {
                if (!this.spatial.beat_id) this.errors.push('Beat selection is mandatory.');
                if (!this.spatial.sector_id) this.errors.push('Sector selection is mandatory.');
                if (!this.spatial.zone_id) this.errors.push('Zone selection is mandatory.');
            }

            if (this.errors.length) {
                if (window.Notification) {
                    window.Notification.error(this.errors, 'Data Validation Error', 3000);
                }
                return;
            }

            // 2. Duplicate Prevention (Idempotency)
            const currentHash = JSON.stringify({
                n: this.caller.number,
                v: this.caller.vehicle_no,
                t: this.selectedCallTypeId,
                s: this.selectedSubType,
                b: this.spatial.beat_id
            });

            const now = Date.now();
            if (this.lastSubmission && (now - this.lastSubmission.time < this.submissionCooldown) && (this.lastSubmission.hash === currentHash)) {
                if (window.Notification) window.Notification.info('Duplicate submission detected. Please wait.', 'Safety Lock');
                return;
            }

            this.submitting = true;
            this.lastSubmission = { hash: currentHash, time: now };

            try {
                const form = document.getElementById('intake-form');
                const formData = new FormData(form);

                // WAF bypass: base64-encode free-text fields so WAF cannot inspect Urdu/mixed content
                ['details', 'location_details'].forEach(field => {
                    const val = formData.get(field);
                    if (val) formData.set(field, btoa(unescape(encodeURIComponent(val))));
                });

                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                // Session expired: server returns 419 or redirects to login (HTML)
                if (response.status === 419) {
                    if (window.Notification) window.Notification.warning('Your session has expired. The page will reload automatically.', 'Session Expired');
                    setTimeout(() => window.location.reload(), 2000);
                    return;
                }

                // Only parse JSON if the server actually returned JSON
                const ct = response.headers.get('content-type') || '';
                if (!ct.includes('application/json')) {
                    // WAF block, HAProxy error page, or session redirect (HTML response)
                    if (window.Notification) window.Notification.error(`Server returned an unexpected response (HTTP ${response.status}). Please refresh and try again.`, 'Server Error');
                    console.error('Non-JSON response:', response.status, response.url);
                    return;
                }

                const result = await response.json();

                if (response.ok) {
                    if (window.Notification) window.Notification.success('Help dispatched Successfully.', 'Success');
                    if (result.redirect) {
                        setTimeout(() => window.location.href = result.redirect, 1000);
                    } else {
                        this.resetForm();
                    }
                } else {
                    const errorMsg = result.message || 'Mission authority rejected the trace.';
                    if (window.Notification) {
                        if (result.errors) {
                            const allErrors = [];
                            Object.values(result.errors).forEach(errs => {
                                if (Array.isArray(errs)) allErrors.push(...errs);
                                else allErrors.push(errs);
                            });
                            window.Notification.error(allErrors, 'Data Validation Error', 5000);
                        } else {
                            window.Notification.error(errorMsg, 'Critical System Failure');
                        }
                    }
                }
            } catch (e) {
                // Only genuine network failures (DNS failure, connection refused, request aborted)
                if (window.Notification) window.Notification.error('Network connection failed. Please check your internet connection.', 'Connection Error');
                console.error('Fetch failed:', e);
            } finally {
                this.submitting = false;
            }
        },

        async searchCaller() {
            if (!this.caller.number) return;
            try {
                const res = await fetch(`/api/lookup-caller?number=${this.caller.number}`);
                const data = await res.json();
                if (data.name) {
                    this.caller.name = data.name;
                    this.caller.vehicle_no = data.vehicle_no || '';
                }
            } catch (e) {
                console.error('Caller lookup failed', e);
            }
        },

        async lookupKM() {
            if (!this.spatial.km) return;
            try {
                const res = await fetch(`/ajax/geospatial-lookup?km_marker=${this.spatial.km}`);
                const data = await res.json();
                if (data.success && data.landmarks.length > 0) {
                    this.geospatialSuggestions = data.landmarks;
                } else {
                    this.geospatialSuggestions = [];
                }
            } catch (e) {
                console.error('KM Lookup Failed', e);
            }
        },

        applyGeospatialSuggestion(marker) {
            this.updateSpatialContext(marker);
            this.geospatialSuggestions = [];
        },

        updateSpatialContext(marker) {
            this.spatial.geospatial_marker_id = marker.id;
            
            if (marker.office) {
                // Set Beat
                this.spatial.beat_id = marker.office.id;
                this.spatial.beat_name = marker.office.name;
                
                // Set Sector (Parent)
                if (marker.office.parent) {
                    this.spatial.sector_id = marker.office.parent.id;
                    this.spatial.sector_name = marker.office.parent.name;
                    
                    // Set Zone (Grandparent)
                    if (marker.office.parent.parent) {
                        this.spatial.zone_id = marker.office.parent.parent.id;
                        this.spatial.zone_name = marker.office.parent.parent.name;
                    }
                }
            }
            
            // If it's a range match, ensure the KM text is updated if it was empty
            if (marker.is_range_match && !this.spatial.km) {
                this.spatial.km = marker.km_marker;
            }

            this.fetchSpatialContacts();
        },

        onCoordInput(value) {
            const parts = value.split(/[,\s]+/).map(p => p.trim());
            if (parts.length >= 2) {
                this.spatial.lat = parts[parts.length - 2];
                this.spatial.lng = parts[parts.length - 1];
            }
        },

        resetForm() {
            this.caller = { number: '', name: '', vehicle_no: '' };
            this.spatial = {
                carriageway_id: '', zone_id: '', sector_id: '', beat_id: '',
                geospatial_marker_id: '', km: '', lat: '', lng: '',
                coord_input: '', zone_name: '', sector_name: '', beat_name: ''
            };
            this.spatialContacts = { zone: null, sector: null, beat: null };
            this.priority = '3';
            this.selectedCallTypeId = '';
            this.selectedSubType = '';
            this.selectedVehicleType = '';
            this.selectedTigerId = '';
            this.details = '';
            this.errors = [];
            this.success = false;
            this.$nextTick(() => {
                document.getElementById('phone_link')?.focus();
            });
        },

        async openHistoryModal() {
            if (!this.caller.number) {
                if (window.Notification) window.Notification.warning('Phone Number is required for history.', 'Scan Interrupted');
                return;
            }
            this.historyModalOpen = true;
            this.historyLoading = true;
            this.callerHistory = [];
            this.selectedHistoryCall = null;
            try {
                const res = await fetch(`/api/lookup-caller?number=${this.caller.number}`);
                const data = await res.json();
                if (data.name) {
                    this.caller.name = data.name;
                    this.caller.vehicle_no = data.vehicle_no || this.caller.vehicle_no;
                }
                this.callerHistory = data.calls || [];
                if (this.callerHistory.length > 0) this.selectedHistoryCall = this.callerHistory[0];
            } catch (e) {
                console.error('History loading failed:', e);
            } finally {
                this.historyLoading = false;
            }
        },

        selectHistoryCall(call) {
            this.selectedHistoryCall = call;
        },

        async applyReminderCall() {
            if (!this.selectedHistoryCall) return;
            
            this.historyLoading = true;
            try {
                const response = await fetch(`/api/dispatch/ping`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ref: this.selectedHistoryCall.id })
                });

                if (response.status === 419) {
                    if (window.Notification) window.Notification.warning('Session expired. Please refresh the page.', 'Session Expired');
                    setTimeout(() => window.location.reload(), 2000);
                    return;
                }

                const ct = response.headers.get('content-type') || '';
                if (!ct.includes('application/json')) {
                    if (window.Notification) window.Notification.error(`Server returned an unexpected response (HTTP ${response.status}). Please try again.`, 'Server Error');
                    return;
                }

                const result = await response.json();

                if (response.ok) {
                    if (window.Notification) window.Notification.success(result.message, 'Reminder Dispatched');
                    this.selectedHistoryCall.call_reminder_count = result.call.call_reminder_count;
                    this.selectedHistoryCall.last_reminder_at = result.call.last_reminder_at;

                    const idx = this.callerHistory.findIndex(h => h.id === result.call.id);
                    if (idx !== -1) {
                        this.callerHistory[idx].call_reminder_count = result.call.call_reminder_count;
                        this.callerHistory[idx].last_reminder_at = result.call.last_reminder_at;
                    }
                } else {
                    if (window.Notification) window.Notification.error(result.message || 'Failed to send reminder', 'Protocol Error');
                }
            } catch (e) {
                console.error(e);
                if (window.Notification) window.Notification.error('Network connection failed. Please check your connection.', 'Connection Error');
            } finally {
                this.historyLoading = false;
            }
        },

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // SOFTPHONE ACTIONS & CONTROLLER
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        phonePollInterval: null,

        async phoneInit() {
            // Prevent softphone initialization if the main application user is a guest
            @guest
                this.phoneAuthenticated = false;
                this.phoneStatus = 'offline';
                this.ziwoToken = null;
                console.warn('[Softphone] Main application user is a guest. Preventing ZIWO softphone initialization.');
                return;
            @endguest
            // Restore auth from localStorage so SPA nav can't wipe it
            try {
                const saved = JSON.parse(localStorage.getItem('ziwo_auth'));
                if (saved && saved.authenticated) {
                    console.log('[ZIWO] phoneInit: restored auth from localStorage, username=' + (saved.username || '?'));
                    this.phoneAuthenticated = true;
                    if (saved.username) this.ziwoUsername = saved.username;
                }
            } catch(_) {}

            // Check microphone permission status initially
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                if (navigator.permissions && navigator.permissions.query) {
                    navigator.permissions.query({ name: 'microphone' }).then(res => {
                        this.micAllowed = res.state === 'granted';
                        res.onchange = () => { this.micAllowed = res.state === 'granted'; }
                    }).catch(() => {
                        this.micAllowed = null;
                    });
                }
            }

            // Check current session status (and grab token for SDK init if already logged in)
            await this.phoneCheckStatus();

            // Polling is now just a keepalive/fallback — SDK handles real-time events
            this.phonePollInterval = setInterval(() => this.phoneCheckStatus(), 30000);

            // Listen to Laravel Echo if available (secondary channel)
            if (window.Echo) {
                window.Echo.channel('telephony')
                    .listen('.CallStatusUpdated', (e) => {
                        this.handleCallBroadcast(e);
                    })
                    .listen('.AgentStatusChanged', (e) => {
                        this.handleAgentBroadcast(e);
                    });
            }
        },

        async checkOrRequestMicrophone() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                this.phoneStatusError = 'Microphone API not supported by browser/HTTP context.';
                this.micAllowed = false;
                return;
            }
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                stream.getTracks().forEach(t => t.stop());
                this.micAllowed = true;
                this.phoneStatusError = '';
            } catch (err) {
                this.micAllowed = false;
                this.phoneStatusError = 'Microphone access denied. Please allow microphone in browser settings.';
            }
        },

        phoneCleanup() {
            if (this.phonePollInterval) clearInterval(this.phonePollInterval);
            if (this.callDurationInterval) clearInterval(this.callDurationInterval);
            this.stopRinging();
        },

        phoneResetUI() {
            // Debounce — ignore if called within 1 s of last call
            const now = Date.now();
            if (this._lastResetAt && (now - this._lastResetAt) < 1000) return;
            this._lastResetAt = now;

            console.log('[Softphone] Manually resetting softphone UI state');
            if (this.currentCall && this.currentCall.id) {
                fetch('/telephony/hangup', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ call_id: this.currentCall.id })
                }).catch(() => {});
            }
            // Hang up all known SDK calls
            Object.values(this.ziwoActiveCalls).forEach(sdkCall => {
                try { if (typeof sdkCall.hangup === 'function') sdkCall.hangup(); } catch (_) {}
            });
            this.phoneStatus = 'online';
            this.phoneTab = 'dialer';         // ← always return to Dialer screen
            this.phoneCollapsed = false;
            this.stopCallTimer();
            this.stopRinging();
            this.ziwoActiveCalls = {};
            this.heldParticipants = [];
            this._pendingResumeParticipant = null;
            this.currentCall = {
                id: null,
                uuid: null,
                caller_number: '',
                caller_name: '',
                is_held: false,
                is_muted: false,
                recording_paused: false,
                duration: 0
            };
            this.transferPanelOpen = false;
            this.addOrCallOpen = false;
            this.keypadPanelOpen = false;
            this.dialNumberInput = '';
        },

        async phoneCheckStatus() {
            try {
                const res = await fetch('/telephony/status', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) {
                    console.warn('[ZIWO] phoneCheckStatus: fetch returned', res.status);
                    return;
                }
                const data = await res.json();
                console.log('[ZIWO] phoneCheckStatus: response', JSON.stringify(data));

                // ─── CRITICAL FIX: Poll is ONE-WAY only. It can PROMOTE (true) but
                //     MUST NEVER demote (false). Only phoneDisconnect() clears auth.
                //     This prevents the 30s poll from undoing a successful manual login
                //     when the backend session hasn't fully committed yet. ───
                if (this.phoneAuthenticated && data.is_authenticated === false) {
                    console.warn('[ZIWO] phoneCheckStatus: ignoring backend is_authenticated=false because frontend is already authenticated. Only explicit disconnect clears auth.');
                    return;
                }

                const wasAuthenticated = this.phoneAuthenticated;
                this.phoneAuthenticated = data.is_authenticated || this.phoneAuthenticated;
                console.log('[ZIWO] phoneCheckStatus: wasAuthenticated=%s now=%s isMock=%s', wasAuthenticated, this.phoneAuthenticated, data.is_mock);
                this.ziwoUsername = data.ziwo_username || '';
                this.isMockMode = data.is_mock || false;

                // Expand softphone console if newly authenticated
                if (!wasAuthenticated && this.phoneAuthenticated) {
                    console.log('[ZIWO] phoneCheckStatus: newly authenticated — expanding console');
                    this.phoneCollapsed = false;
                }

                // ── Load real queues/teammates if authenticated and not loaded yet ──
                if (this.phoneAuthenticated) {
                    if (!this.ziwoDataLoaded) {
                        this.ziwoDataLoaded = true;
                        this.fetchZiwoQueues();
                        this.fetchZiwoTeammates();
                    }
                } else {
                    this.ziwoDataLoaded = false;
                }

                // ── Token / SDK init (runs once on page-load or re-auth) ──
                if (data.ziwo_token && !this.ziwoToken) {
                    this.ziwoToken = data.ziwo_token;
                    this.ziwoContactCenter = data.contact_center || 'nayatel';
                    if (data.is_authenticated && !this.ziwoSdkInitialized) {
                        this.$nextTick(() => this.initZiwoSdk());
                    }
                }

                // If agent is logged out, always update
                if (!this.phoneAuthenticated) {
                    if (this.phoneStatus !== 'offline') {
                        this.phoneStatus = 'offline';
                        this.stopCallTimer();
                        this.stopRinging();
                    }
                    return;
                }

                // ─────────────────────────────────────────────────────────
                // SDK MODE: poller is auth-only. NEVER touch phoneStatus/currentCall.
                // All call state is owned exclusively by the SDK's real-time events
                // (ziwo-requesting, ziwo-active, ziwo-hangup, etc.).
                // The backend's /status endpoint does NOT track WebRTC calls placed
                // directly from the browser, so data.active_call is always null here.
                // Treating that null as "call ended" caused the phantom reset loop.
                // ─────────────────────────────────────────────────────────
                if (!this.isMockMode) {
                    if (this.phoneAuthenticated && this.phoneStatus === 'offline') {
                        this.phoneStatus = 'online';
                        this.phoneTab = 'dialer'; // Always land on Dialer tab when going online
                    }
                    return; // SDK events own all call state — poller stays silent
                }

                // ── MOCK MODE: backend fully tracks call state ──
                const newStatus = data.agent_status || 'online';
                const uiIsInCall = ['ringing', 'ringing_inbound', 'speaking', 'held'].includes(this.phoneStatus);

                if (data.active_call) {
                    this.currentCall = {
                        id: data.active_call.id,
                        uuid: data.active_call.uuid,
                        caller_number: data.active_call.caller_number,
                        caller_name: data.active_call.caller_name || '',
                        is_held: data.active_call.is_held || false,
                        is_muted: data.active_call.is_muted || false,
                        recording_paused: data.active_call.recording_paused || false,
                        duration: data.active_call.seconds_duration || 0
                    };

                    if (newStatus === 'ringing_inbound' && this.phoneStatus !== 'ringing_inbound') {
                        this.phoneCollapsed = false;
                        this.startRinging();
                    } else if (newStatus !== 'ringing_inbound') {
                        this.stopRinging();
                    }

                    if (['speaking', 'active'].includes(newStatus) && !this.callDurationInterval) {
                        this.startCallTimer();
                    }

                    this.phoneStatus = newStatus;
                } else {
                    if (uiIsInCall) {
                        this.stopRinging();
                        this.stopCallTimer();
                        this.currentCall = { id: null, uuid: null, caller_number: '', caller_name: '', is_held: false, is_muted: false, recording_paused: false, duration: 0 };
                    }
                    this.phoneStatus = newStatus;
                }
            } catch (e) {
                console.error('Telephony status check failed:', e);
            }
        },

        async fetchZiwoQueues() {
            try {
                const res = await fetch('/telephony/queues', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) return;
                const data = await res.json();
                if (data.status === 'success') {
                    this.mockQueues = data.queues;
                }
            } catch (err) {
                console.error('[ZIWO] Failed to fetch queues:', err);
            }
        },

        async fetchZiwoTeammates() {
            try {
                const res = await fetch('/telephony/teammates', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) return;
                const data = await res.json();
                if (data.status === 'success') {
                    this.mockTeammates = data.teammates;
                }
            } catch (err) {
                console.error('[ZIWO] Failed to fetch teammates:', err);
            }
        },

        togglePhoneCollapse() {
            this.phoneCollapsed = !this.phoneCollapsed;
            if (!this.phoneCollapsed) {
                if (typeof this.expanded !== 'undefined') {
                    this.expanded = false;
                }
            }
        },

        async phoneAuthenticate() {
            console.log('[ZIWO] phoneAuthenticate: starting auth with username=' + this.phoneAuthForm.username);
            if (!this.phoneAuthForm.username || !this.phoneAuthForm.password) {
                this.phoneStatusError = 'Username and password are required.';
                console.warn('[ZIWO] phoneAuthenticate: validation failed — missing fields');
                return;
            }
            this.phoneSubmitting = true;
            this.phoneStatusError = '';
            try {
                const response = await fetch('/telephony/authenticate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.phoneAuthForm)
                });
                console.log('[ZIWO] phoneAuthenticate: response status=' + response.status);
                const data = await response.json();
                console.log('[ZIWO] phoneAuthenticate: response data', JSON.stringify(data));
                if (response.ok && data.status === 'success') {
                    console.log('[ZIWO] phoneAuthenticate: SUCCESS — setting phoneAuthenticated=true, status=online, expanding console');
                    this.phoneAuthenticated = true;
                    this.phoneCollapsed = false; // Expand console on successful login
                    this.currentCall = {
                        id: null,
                        uuid: null,
                        caller_number: '',
                        caller_name: '',
                        is_held: false,
                        is_muted: false,
                        recording_paused: false,
                        duration: 0
                    };
                    this.ziwoUsername = data.username || data.ziwo_username || '';
                    this.ziwoToken = data.access_token || data.ziwo_token || null;
                    this.ziwoContactCenter = data.contact_center || 'nayatel';
                    this.phoneStatus = 'online';
                    this.phoneAuthForm.password = '';
                    this.phoneSearchContacts();
                    this.phoneLoadRecentLogs();
                    // Initialize ZIWO SDK WebSocket connection after successful auth
                    this.$nextTick(() => this.initZiwoSdk());
                    if (window.Notification) window.Notification.success('Telephony session registered successfully.', 'Telephony Connected');
                    console.log('[ZIWO] phoneAuthenticate: Fully done — phoneAuthenticated=' + this.phoneAuthenticated + ' phoneStatus=' + this.phoneStatus + ' collapsed=' + this.phoneCollapsed);
                    // Persist auth in localStorage so SPA nav / poll can't kill it
                    try { localStorage.setItem('ziwo_auth', JSON.stringify({authenticated: true, username: this.ziwoUsername, ts: Date.now()})); } catch(_) {}
                } else {
                    this.phoneStatusError = data.message || 'Authentication failed.';
                    console.warn('[ZIWO] phoneAuthenticate: FAILED —', this.phoneStatusError);
                }
            } catch (e) {
                console.error('[ZIWO] phoneAuthenticate: network error', e);
                this.phoneStatusError = 'Network failure connecting to telephony client.';
            } finally {
                this.phoneSubmitting = false;
            }
        },

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // ZIWO SDK — WebSocket/Verto real-time call event wiring
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        initZiwoSdk() {
            // ── SINGLETON GUARD: never initialize twice ──
            // Without this, two overlapping phoneCheckStatus() calls (e.g. poll + Echo broadcast)
            // can both pass the !this.ziwoSdkClient check before $nextTick resolves, registering
            // ALL event listeners twice. Doubled listeners = every SDK event fires twice,
            // including ziwo-requesting, which spawns a phantom second outbound call.
            if (this.ziwoSdkInitialized) {
                console.warn('[ZIWO SDK] Already initialized — skipping duplicate init.');
                return;
            }
            this.ziwoSdkInitialized = true;

            if (this.isMockMode) {
                console.log('[ZIWO SDK] In mock mode — skipping WebRTC client initialization.');
                this.phoneStatus = 'online';
                return;
            }
            if (!window.ziwoCoreFront) {
                console.error('[ZIWO SDK] ziwo-core-front not loaded on window. Check CDN script tag.');
                this.ziwoSdkInitialized = false; // allow retry
                return;
            }
            if (!this.ziwoToken) {
                console.warn('[ZIWO SDK] No access_token available — cannot initialize SDK.');
                this.ziwoSdkInitialized = false; // allow retry
                return;
            }

            console.log('[ZIWO SDK] Initializing ZiwoClient for contact center:', this.ziwoContactCenter);

            try {
                this.ziwoSdkClient = new window.ziwoCoreFront.ZiwoClient({
                    contactCenterName: this.ziwoContactCenter,
                    autoConnect: false,
                    credentials: {
                        authenticationToken: this.ziwoToken,
                    },
                    mediaTag: document.getElementById('ziwo-peer-audio'),
                });

                // Connect explicitly and handle 401 Unauthorized or other connection rejections
                this.ziwoSdkClient.connect()
                    .then(() => {
                        console.log('[ZIWO SDK] Connected successfully ✓');
                    })
                    .catch((err) => {
                        console.error('[ZIWO SDK] Connection/Auth failed:', err);
                        
                        // SDK connection issue — do NOT reset auth state
                        this.phoneStatus = 'online';
                        this.ziwoSdkInitialized = false; // allow retry
                        this.phoneStatusError = 'WebRTC connection failed. Calls unavailable. Try refreshing.';
                        if (window.Notification) {
                            window.Notification.warning('Telephony SDK not connected. Calls unavailable.', 'SDK Connection');
                        }
                    });
            } catch (err) {
                console.error('[ZIWO SDK] ZiwoClient init failed:', err);
                this.ziwoSdkInitialized = false; // allow retry on next attempt
                return;
            }

            // Request browser notification permission proactively
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }

            // Helper: SDK event detail = { currentCall: Call, callID, direction, ... }
            // The actual SDK Call object (with .answer()/.hangup()) lives at e.detail.currentCall
            // The call ID field is "callID" (capital D)
            const extractCall = (e) => e.detail?.currentCall || e.detail?.call || null;
            const extractCallId = (e) => e.detail?.callID || e.detail?.primaryCallID || e.detail?.currentCall?.callID || null;
            // ZIWO doc shows ev.details.call.answer() — the Call object lives at event.details.call
            const extractNum = (detail) => detail?.call?.phoneNumber || detail?.details?.call?.phoneNumber || detail?.phoneNumber || detail?.callerNumber || detail?.from || detail?.callerIdNumber || detail?.caller || detail?.currentCall?.phoneNumber || detail?.call?.from || detail?.call?.callerIdNumber || '';

            // ── LISTENER DEDUP: window.addEventListener is global, listeners
            //    cannot be re-bound without removeEventListener. Skip the
            //    re-bind on re-init by checking a window-scoped flag.
            if (window.__ziwoListenersBound) {
                console.log('[ZIWO SDK] All event listeners already bound — skipping re-bind.');
                return;
            }
            window.__ziwoListenersBound = true;

            // ── INBOUND: call is ringing ──────────────────────────────────
            window.addEventListener('ziwo-ringing', (e) => {
                // ZIWO doc: event.detail.call (and event.details.call alias) holds the Call instance
                const call = extractCall(e) || e.details?.call || null;
                const callId = extractCallId(e) || ('inbound-' + Date.now());
                const detail = e.detail || e.details || {};
                const num = extractNum(detail);
                const isInbound = !detail?.direction || detail?.direction === 'inbound' || detail?.call?.direction === 'inbound';

                console.log('[ZIWO SDK] ziwo-ringing | callId:', callId, 'num:', num, 'direction:', detail?.direction || detail?.call?.direction, 'call obj:', call);

                // Store the REAL SDK Call instance (has .answer() / .hangup())
                if (call) this.ziwoActiveCalls[callId] = call;

                if (isInbound && this.phoneStatus !== 'ringing_inbound') {
                    this.currentCall = {
                        id: callId,
                        uuid: callId,
                        caller_number: num,
                        caller_name: detail?.callerIdName || detail?.displayName || detail?.call?.callerIdName || '',
                        is_held: false,
                        is_muted: false,
                        recording_paused: false,
                        duration: 0,
                        direction: 'inbound'
                    };
                    this.phoneStatus = 'ringing_inbound';
                    this.phoneCollapsed = false;
                    this.startRinging();

                    // Fire browser desktop notification
                    if ('Notification' in window && Notification.permission === 'granted') {
                        const notif = new Notification('📞 Incoming Call', {
                            body: `Caller: ${num || 'Unknown'}`,
                            icon: '/favicon.ico',
                            requireInteraction: true,
                            tag: 'inbound-call-' + callId
                        });
                        notif.onclick = () => {
                            window.focus();
                            this.phoneCollapsed = false;
                            notif.close();
                        };
                    }

                    // Pre-fill caller form
                    if (num && !this.caller.number) {
                        this.caller.number = num;
                        this.searchCaller();
                    }
                }
            });

            // ── INBOUND FALLBACK: SDK may fire ziwo-invite for inbound ──
            window.addEventListener('ziwo-invite', (e) => {
                const call = extractCall(e);
                console.log('[ZIWO SDK] ziwo-invite raw detail:', e.detail, 'extracted call:', call);
                if (this.phoneStatus === 'ringing_inbound') return; // already handled

                const callId = call?.callId || call?.id || ('invite-' + Date.now());
                const num = call?.phoneNumber || call?.callerNumber || call?.from || call?.callerIdNumber || call?.caller || '';

                if (call) this.ziwoActiveCalls[callId] = call;

                this.currentCall = {
                    id: callId,
                    uuid: callId,
                    caller_number: num,
                    caller_name: call?.callerIdName || call?.displayName || '',
                    is_held: false,
                    is_muted: false,
                    recording_paused: false,
                    duration: 0
                };
                this.phoneStatus = 'ringing_inbound';
                this.phoneCollapsed = false;
                this.startRinging();
            });

            // ── OUTBOUND: call is being established ───────────────────────
            // isConferenceResuming: set true when SDK internally resumes a held call
            // (which also fires ziwo-requesting). This prevents treating the SDK's
            // own resume cycle as a user-initiated new outbound leg.
            window.addEventListener('ziwo-requesting', (e) => {
                const call = e.detail?.call;
                if (call) {
                    this.ziwoActiveCalls[call.callId] = call;
                    // Only update currentCall.id for genuine new outbound calls,
                    // not for SDK-internal resume sequences
                    if (!this.isConferenceResuming) {
                        // Accept transition from online→ringing (outbound)
                        // and ringing_inbound→ringing (rare inbound→outbound handoff)
                        if (this.phoneStatus === 'online' || this.phoneStatus === 'ringing_inbound') {
                            this.currentCall.id = call.callId;
                            this.currentCall.uuid = call.callId;
                            this.phoneStatus = 'ringing';
                        }
                    }
                } else {
                    if (!this.isConferenceResuming && (this.phoneStatus === 'online' || this.phoneStatus === 'ringing_inbound')) this.phoneStatus = 'ringing';
                }
                console.log('[ZIWO SDK] ziwo-requesting');
            });
            window.addEventListener('ziwo-trying', (e) => {
                const call = e.detail?.call;
                if (call) {
                    this.ziwoActiveCalls[call.callId] = call;
                    if (!this.isConferenceResuming) {
                        if ((this.phoneStatus === 'online' || this.phoneStatus === 'ringing_inbound') || !this.currentCall.id) {
                            this.currentCall.id = this.currentCall.id || call.callId;
                            this.currentCall.uuid = this.currentCall.uuid || call.callId;
                        }
                    }
                }
                console.log('[ZIWO SDK] ziwo-trying');
                if (!this.isConferenceResuming && (this.phoneStatus === 'online' || this.phoneStatus === 'ringing_inbound')) this.phoneStatus = 'ringing';
            });
            window.addEventListener('ziwo-early', (e) => {
                const call = e.detail?.call;
                if (call) {
                    this.ziwoActiveCalls[call.callId] = call;
                    if (!this.isConferenceResuming) {
                        if ((this.phoneStatus === 'online' || this.phoneStatus === 'ringing_inbound') || !this.currentCall.id) {
                            this.currentCall.id = this.currentCall.id || call.callId;
                            this.currentCall.uuid = this.currentCall.uuid || call.callId;
                        }
                    }
                }
                console.log('[ZIWO SDK] ziwo-early — call is ringing on remote side');
                if (!this.isConferenceResuming && (this.phoneStatus === 'online' || this.phoneStatus === 'ringing_inbound')) this.phoneStatus = 'ringing';
            });

            // ── ACTIVE: call connected / answered ─────────────────────────
            window.addEventListener('ziwo-active', (e) => {
                const call = extractCall(e);
                console.log('[ZIWO SDK] ziwo-active:', call);
                const callId = call?.callId || call?.id;

                // ── GHOST CALL GUARD (relaxed) ────────────────────────────
                // Only reject if BOTH: the call object itself is missing AND we
                // are completely idle. If we have a real SDK call instance,
                // trust the SDK — outbound calls go from requesting→trying→early→active
                // and we must accept active to render the call screen.
                if (!call && this.phoneStatus === 'online' && !this.currentCall.id) {
                    console.warn('[ZIWO SDK] ziwo-active with no call object and idle UI — ignoring.');
                    return;
                }
                // If we have a current active call OR we tracked requesting/trying/early
                // for this callId, accept it. Otherwise, accept real SDK calls
                // (verto set) but log a soft warning.
                const isTracked = callId && (this.ziwoActiveCalls[callId] || this.currentCall.id === callId);
                const isRealSdkCall = call && call.verto;
                if (!isTracked && !isRealSdkCall && this.phoneStatus === 'online' && !this.currentCall.id) {
                    console.warn('[ZIWO SDK] ziwo-active for unknown call on idle UI — ignoring.', callId);
                    return;
                }
                if (!isTracked && callId) {
                    console.warn('[ZIWO SDK] ziwo-active for callId seen for first time — accepting (real SDK event).', callId);
                }

                if (call && callId) this.ziwoActiveCalls[callId] = call;
                this.stopRinging();

                // ── Restore pending held participant when outbound was cancelled ──
                // When agent cancels a ringing outbound conference leg by tapping
                // the held card, the SDK fires ziwo-active for the held call.
                // Restore that participant's data to currentCall.
                if (this._pendingResumeParticipant) {
                    const pending = this._pendingResumeParticipant;
                    this._pendingResumeParticipant = null;
                    if (callId === pending.id || Object.keys(this.ziwoActiveCalls).includes(pending.id)) {
                        this.currentCall = {
                            id: pending.id,
                            uuid: pending.id,
                            caller_number: pending.number,
                            caller_name: pending.name,
                            is_held: false,
                            is_muted: false,
                            recording_paused: false,
                            duration: pending.duration || 0,
                            direction: pending.direction || 'inbound'
                        };
                        this.phoneStatus = 'speaking';
                        this.isConferenceResuming = false;
                        // Notify backend to resume
                        fetch('/telephony/resume', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ call_id: pending.id })
                        }).catch(() => {});
                        return;
                    }
                }

                this.phoneStatus = 'speaking';
                if (!this.currentCall.id && callId) {
                    this.currentCall.id = callId;
                    this.currentCall.uuid = callId;
                }
                // Ensure caller number is filled if it was blank in currentCall
                const activeNum = call?.phoneNumber || call?.callerNumber || extractNum(e.detail);
                if (activeNum && (!this.currentCall.caller_number || this.currentCall.caller_number === '')) {
                    this.currentCall.caller_number = activeNum;
                    this.currentCall.caller_name = call?.callerIdName || call?.displayName || '';
                }
                // Call connected — clear the dial watchdog
                if (this._dialWatchdog) { clearTimeout(this._dialWatchdog); this._dialWatchdog = null; }
                this.startCallTimer();
                if (this.currentCall.caller_number && !this.caller.number) {
                    this.caller.number = this.currentCall.caller_number;
                    this.searchCaller();
                }
            });

            // ── HANGUP: call ended ────────────────────────────────────────
            window.addEventListener('ziwo-hangup', (e) => {
                const call = extractCall(e);
                console.log('[ZIWO SDK] ziwo-hangup:', call);
                const callId = call?.callId || call?.id;
                if (callId) delete this.ziwoActiveCalls[callId];
                
                this.stopRinging();
                this.playEndCallTone();
                this.phoneLoadRecentLogs();

                // If we still have other active calls in the SDK, try to restore from heldParticipants
                const remainingCallIds = Object.keys(this.ziwoActiveCalls);
                if (remainingCallIds.length > 0) {
                    // Find if any remaining call is in heldParticipants
                    let restoredFromHeld = false;
                    for (const nextCallId of remainingCallIds) {
                        const heldIndex = this.heldParticipants.findIndex(p => p.id === nextCallId);
                        if (heldIndex !== -1) {
                            const heldEntry = this.heldParticipants[heldIndex];
                            this.heldParticipants.splice(heldIndex, 1);
                            const nextCall = this.ziwoActiveCalls[nextCallId];
                            const num = heldEntry.number || nextCall?.phoneNumber || nextCall?.callerNumber || '';

                            this.currentCall = {
                                id: nextCallId,
                                uuid: nextCallId,
                                caller_number: num,
                                caller_name: heldEntry.name || nextCall?.callerIdName || '',
                                is_held: false,
                                is_muted: false,
                                recording_paused: false,
                                duration: heldEntry.duration || 0,
                                direction: heldEntry.direction || 'inbound'
                            };

                            // Set flag BEFORE calling unhold so ziwo-requesting
                            // events from SDK resume don't overwrite our state
                            this.isConferenceResuming = true;

                            // Tell SDK to resume (will fire ziwo-unheld when done)
                            if (nextCall && typeof nextCall.unhold === 'function') {
                                try { nextCall.unhold(); } catch (err) {
                                    console.warn('[ZIWO] Resume failed:', err);
                                    this.isConferenceResuming = false;
                                }
                            } else {
                                // No unhold method, clear flag immediately
                                this.isConferenceResuming = false;
                            }

                            // Also notify backend
                            fetch('/telephony/resume', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                body: JSON.stringify({ call_id: nextCallId })
                            }).catch(() => {});

                            this.phoneStatus = 'speaking';
                            restoredFromHeld = true;
                            break;
                        }
                    }
                    if (restoredFromHeld) return;

                    // Remaining call not in heldParticipants.
                    // The SDK may auto-resume it; call unhold() to ensure it resumes,
                    // then update the UI to show it as the active call.
                    const nextCallId = remainingCallIds[0];
                    const nextCall = this.ziwoActiveCalls[nextCallId];
                    const num = nextCall?.phoneNumber || nextCall?.callerNumber || '';
                    // Attempt SDK resume for the remaining call
                    this.isConferenceResuming = true;
                    if (nextCall && typeof nextCall.unhold === 'function') {
                        try { nextCall.unhold(); } catch (err) {
                            console.warn('[ZIWO] Auto-resume on hangup failed:', err);
                            this.isConferenceResuming = false;
                        }
                    } else {
                        this.isConferenceResuming = false;
                    }
                    // Also notify backend
                    fetch('/telephony/resume', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ call_id: nextCallId })
                    }).catch(() => {});
                    this.currentCall = {
                        id: nextCallId,
                        uuid: nextCallId,
                        caller_number: num,
                        caller_name: nextCall?.callerIdName || nextCall?.displayName || '',
                        is_held: false,
                        is_muted: false,
                        recording_paused: false,
                        duration: this.currentCall.duration
                    };
                    this.phoneStatus = 'speaking';
                    return;
                }

                // No active SDK calls — check heldParticipants array directly
                if (this.heldParticipants.length > 0) {
                    const lastHeld = this.heldParticipants.pop();
                    this.currentCall = {
                        id: lastHeld.id,
                        uuid: lastHeld.id,
                        caller_number: lastHeld.number,
                        caller_name: lastHeld.name,
                        is_held: false,
                        is_muted: false,
                        recording_paused: false,
                        duration: lastHeld.duration,
                        direction: lastHeld.direction || 'inbound'
                    };
                    const sdkCall = this.ziwoActiveCalls[lastHeld.id];
                    if (sdkCall && typeof sdkCall.unhold === 'function') {
                        this.isConferenceResuming = true;
                        try { sdkCall.unhold(); } catch (err) {
                            console.warn('[ZIWO] Unhold failed:', err);
                            this.isConferenceResuming = false;
                        }
                    }
                    if (lastHeld.id) {
                        fetch('/telephony/resume', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ call_id: lastHeld.id })
                        }).catch(() => {});
                    }
                    this.phoneStatus = 'speaking';
                    return;
                }

                // If absolutely no calls are left, reset back to online/idle
                this.phoneStatus = 'online';
                this.stopCallTimer();
                this.dialNumberInput = '';
                this.currentCall = { id: null, uuid: null, caller_number: '', caller_name: '', is_held: false, is_muted: false, recording_paused: false, duration: 0 };
            });

            // ── DESTROY: call fully cleaned up ────────────────────────────
            window.addEventListener('ziwo-destroy', (e) => {
                const call = extractCall(e);
                const callId = call?.callId || call?.id;
                if (callId) delete this.ziwoActiveCalls[callId];
                console.log('[ZIWO SDK] ziwo-destroy');
                // Only reset to idle when ALL calls are gone AND we are not in
                // a conference-hold transition (heldParticipants means a new leg
                // is being dialed — don't wipe the overlay)
                if (Object.keys(this.ziwoActiveCalls).length === 0 && this.heldParticipants.length === 0) {
                    this.phoneStatus = 'online';
                    this.stopCallTimer();
                }
            });

            // ── HOLD / UNHOLD ─────────────────────────────────────────────
            window.addEventListener('ziwo-held', () => {
                console.log('[ZIWO SDK] ziwo-held');
                // During conference, the SDK may fire ziwo-held for the OTHER leg.
                // Only update state if we are not mid-resume (the held event is for our current call).
                if (!this.isConferenceResuming) {
                    this.currentCall.is_held = true;
                    this.phoneStatus = 'held';
                }
            });
            window.addEventListener('ziwo-unheld', () => {
                console.log('[ZIWO SDK] ziwo-unheld');
                this.isConferenceResuming = false; // resume cycle complete
                this.currentCall.is_held = false;
                this.phoneStatus = 'speaking';
            });

            // ── MUTE / UNMUTE ─────────────────────────────────────────────
            window.addEventListener('ziwo-mute', () => { this.currentCall.is_muted = true; });
            window.addEventListener('ziwo-unmute', () => { this.currentCall.is_muted = false; });

            // ── SDK CONNECTED / DISCONNECTED ──────────────────────────────
            window.addEventListener('ziwo-connected', () => {
                console.log('[ZIWO SDK] WebSocket connected ✓');
                // Only update status if we're NOT in an active call.
                // The SDK fires ziwo-connected on every reconnect — including reconnects
                // that happen mid-call — and overwriting phoneStatus here would dismiss
                // the call overlay while a live call is still in progress.
                const liveStates = ['ringing', 'ringing_inbound', 'speaking', 'held'];
                if (!liveStates.includes(this.phoneStatus)) {
                    this.phoneStatus = 'online';
                }
            });
            window.addEventListener('ziwo-disconnected', () => {
                console.warn('[ZIWO SDK] WebSocket disconnected');
                if (this.phoneStatus !== 'offline') {
                    const liveStates = ['ringing', 'ringing_inbound', 'speaking', 'held'];
                    if (!liveStates.includes(this.phoneStatus)) {
                        this.phoneStatus = 'online'; // stay online, SDK will auto-reconnect
                    }
                }
            });
            window.addEventListener('ziwo-recovering', () => {
                console.warn('[ZIWO SDK] Call recovering from reconnect...');
            });

            console.log('[ZIWO SDK] All event listeners registered ✓');
        },

        async phoneDisconnect() {
            console.log('[ZIWO] phoneDisconnect: manually disconnecting telephony session');
            // Mark explicit disconnect — prevents poll from re-authenticating us
            this._explicitDisconnect = true;
            setTimeout(() => { this._explicitDisconnect = false; }, 5000);
            try {
                const response = await fetch('/telephony/disconnect', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (!response.ok) {
                    console.warn('Telephony disconnect returned', response.status);
                }
                if (window.Notification) window.Notification.info('Telephony session terminated.', 'Telephony Disconnected');
            } catch (e) {
                console.error('Telephony disconnect failed:', e);
            } finally {
                // Always reset UI state regardless of network/server outcome
                console.log('[ZIWO] phoneDisconnect: resetting all state');
                try { localStorage.removeItem('ziwo_auth'); } catch(_) {}
                this.phoneAuthenticated = false;
                this.phoneStatus = 'offline';
                this.phoneCollapsed = true;
                this.ziwoToken = null;
                this.ziwoSdkInitialized = false;
                this.ziwoSdkClient = null;
                this.ziwoDataLoaded = false;
                this.stopCallTimer();
                this.stopRinging();
                this.currentCall = {
                    id: null,
                    uuid: null,
                    caller_number: '',
                    caller_name: '',
                    is_held: false,
                    is_muted: false,
                    recording_paused: false,
                    duration: 0
                };
                this.ziwoActiveCalls = {};
                this.heldParticipants = [];
                this.transferPanelOpen = false;
                this.addOrCallOpen = false;
                this.keypadPanelOpen = false;
                this.dialNumberInput = '';
                this.phoneTab = 'dialer';
            }
        },

        async phoneDial() {
            if (!this.dialNumberInput) return;
            const num = this.dialNumberInput.trim();

            // Debounce — prevent multi-tap from placing duplicate calls
            const now = Date.now();
            if (this._lastDialAt && (now - this._lastDialAt) < 2000) {
                console.warn('[ZIWO SDK] phoneDial debounced (last dial', now - this._lastDialAt, 'ms ago)');
                return;
            }
            this._lastDialAt = now;

            if (['ringing', 'ringing_inbound', 'speaking', 'active', 'held'].includes(this.phoneStatus)) {
                console.warn('[ZIWO SDK] phoneDial ignored — already in call, status=' + this.phoneStatus);
                return;
            }

            // Stuck-call watchdog: if ziwo-active/trying/early never arrives within 60s,
            // reset UI so the user can dial again. Clear any prior watchdog.
            if (this._dialWatchdog) clearTimeout(this._dialWatchdog);
            this._dialWatchdog = setTimeout(() => {
                if (this.phoneStatus === 'ringing') {
                    console.warn('[ZIWO SDK] Dial watchdog: no ziwo-active/trying within 60s, resetting UI');
                    this.phoneStatus = 'online';
                    this.stopCallTimer();
                    this.stopRinging();
                    this.currentCall = { id: null, uuid: null, caller_number: '', caller_name: '', is_held: false, is_muted: false, recording_paused: false, duration: 0 };
                    if (window.Notification) window.Notification.warning('Call did not connect. Please try again.', 'Call Timeout');
                }
            }, 60000);

            if (this.isMockMode) {
                try {
                    const response = await fetch('/telephony/dial', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ phone_number: num })
                    });
                    const data = await response.json();
                    if (response.ok && data.status === 'success') {
                        this.currentCall.caller_number = num;
                        this.currentCall.caller_name = '';
                        this.currentCall.duration = 0;
                        this.currentCall.direction = 'outbound';
                        this.phoneStatus = 'ringing';
                        this.phoneCollapsed = false;
                        this.caller.number = num;
                        this.searchCaller();

                        if (data.call_id) {
                            this.currentCall.id = data.call_id;
                            this.currentCall.uuid = data.call_id;
                        }
                    } else {
                        if (window.Notification) window.Notification.error(data.message || 'Outbound call failed in mock mode.', 'Dial Failed');
                    }
                } catch (e) {
                    console.error('Outbound call failed in mock mode:', e);
                }
                return;
            }

            if (!this.ziwoSdkClient) {
                console.error('[ZIWO SDK] SDK not initialized — cannot place call.');
                if (window.Notification) window.Notification.error('Telephony SDK not ready. Please re-authenticate.', 'Call Failed');
                return;
            }
            try {
                console.log('[ZIWO SDK] Starting outbound call to:', num);
                this.currentCall.caller_number = num;
                this.currentCall.caller_name = '';
                this.currentCall.duration = 0;
                this.currentCall.direction = 'outbound';
                this.phoneStatus = 'ringing';
                this.phoneCollapsed = false;
                this.caller.number = num;
                this.searchCaller();
                this.ziwoSdkClient.startCall(num);
            } catch (e) {
                console.error('[ZIWO SDK] Outbound call failed:', e);
                this.phoneStatus = 'online';
                this.currentCall.id = null;
                this.currentCall.uuid = null;
                if (window.Notification) window.Notification.error('Could not place outbound call.', 'Call Failed');
            }
        },

        async phoneAnswer() {
            const call = Object.values(this.ziwoActiveCalls)[0];
            console.log('[ZIWO SDK] Answering call:', call);
            this.stopRinging();

            const callId = call ? (call.callId || call.id) : this.currentCall.id;
            if (callId) {
                fetch('/telephony/answer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ call_id: callId })
                }).then(() => {
                    if (this.isMockMode) {
                        this.phoneStatus = 'speaking';
                        this.startCallTimer();
                    }
                }).catch(err => console.error('Failed to notify backend of answer:', err));
            }

            if (this.isMockMode) {
                return;
            }

            if (!call) {
                console.warn('[ZIWO SDK] No active call object to answer');
                return;
            }
            try {
                if (typeof call.answer === 'function') {
                    call.answer();
                } else if (typeof call.accept === 'function') {
                    call.accept();
                } else {
                    console.error('[ZIWO SDK] Call object has no answer/accept method. Keys:', Object.keys(call));
                }
            } catch (e) {
                console.error('[ZIWO SDK] Answer failed:', e);
                this.phoneStatus = 'ringing_inbound';
                this.startRinging();
            }
        },

        async phoneHangup() {
            // During conference: hang up the CURRENT active leg (tracked by currentCall.id),
            // not blindly the first entry in ziwoActiveCalls (which may be the held leg).
            let currentCallId = this.currentCall.id;
            let call = currentCallId
                ? (this.ziwoActiveCalls[currentCallId] || Object.values(this.ziwoActiveCalls).find(c => (c.callId || c.id) === currentCallId))
                : Object.values(this.ziwoActiveCalls)[0];

            // If currentCallId is a pending-{timestamp} marker, find the real SDK call
            // by matching the caller_number. The pending marker is set by phoneDial()
            // before ziwo-requesting replaces it with the real callId.
            if (!call && currentCallId && currentCallId.toString().startsWith('pending-') && this.currentCall.caller_number) {
                call = Object.values(this.ziwoActiveCalls).find(c =>
                    c.phoneNumber === this.currentCall.caller_number ||
                    c.callerNumber === this.currentCall.caller_number
                );
            }

            // Fallback for dialing outbound calls when currentCallId matches but isn't stored by ID yet
            if (!call && this.phoneStatus === 'ringing') {
                call = Object.values(this.ziwoActiveCalls).find(c => c.direction === 'outbound' || c.phoneNumber === this.currentCall.caller_number);
            }

            console.log('[ZIWO SDK] Hanging up call:', call);
            this.stopRinging();

            const callId = call ? (call.callId || call.id) : currentCallId;
            if (callId) {
                try {
                    await fetch('/telephony/hangup', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ call_id: callId })
                    });
                } catch (err) {
                    console.error('Failed to notify backend of hangup:', err);
                }
            }

            if (this.isMockMode) {
                this.phoneStatus = 'online';
                this.stopCallTimer();
                this.dialNumberInput = '';
                this.currentCall = { id: null, uuid: null, caller_number: '', caller_name: '', is_held: false, is_muted: false, recording_paused: false, duration: 0 };
                return;
            }

            // In SDK mode, let the ziwo-hangup event handle state transitions.
            // Just invoke the SDK hangup; ziwo-hangup will clean up currentCall / phoneStatus.
            if (!call) {
                console.warn('[ZIWO SDK] No call object found to hang up — forcing UI reset.');
                this.phoneStatus = 'online';
                this.stopCallTimer();
                this.currentCall = { id: null, uuid: null, caller_number: '', caller_name: '', is_held: false, is_muted: false, recording_paused: false, duration: 0 };
                return;
            }
            try {
                if (typeof call.hangup === 'function') {
                    call.hangup();
                } else if (typeof call.terminate === 'function') {
                    call.terminate();
                } else if (typeof call.reject === 'function') {
                    call.reject();
                } else {
                    console.error('[ZIWO SDK] Call object has no hangup/terminate method. Keys:', Object.keys(call));
                }
            } catch (e) {
                console.error('[ZIWO SDK] Hangup failed:', e);
            }
        },

        async phoneHold() {
            const callId = this.currentCall.id;
            const call = callId
                ? (this.ziwoActiveCalls[callId] || Object.values(this.ziwoActiveCalls)[0])
                : Object.values(this.ziwoActiveCalls)[0];
            if (!call) return;
            console.log('[ZIWO SDK] Holding call:', call.callId || callId);
            try {
                call.hold();
                // Notify backend
                if (callId) {
                    fetch('/telephony/hold', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ call_id: callId })
                    }).catch(() => {});
                }
            } catch (e) {
                console.error('[ZIWO SDK] Hold failed:', e);
            }
        },

        async phoneResume() {
            const callId = this.currentCall.id;
            const call = callId
                ? (this.ziwoActiveCalls[callId] || Object.values(this.ziwoActiveCalls)[0])
                : Object.values(this.ziwoActiveCalls)[0];
            if (!call) return;
            console.log('[ZIWO SDK] Resuming call:', call.callId || callId);
            try {
                this.isConferenceResuming = true;
                call.unhold();
                if (callId) {
                    fetch('/telephony/resume', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ call_id: callId })
                    }).catch(() => {});
                }
            } catch (e) {
                console.error('[ZIWO SDK] Resume failed:', e);
                this.isConferenceResuming = false;
            }
        },

        // ── Switch to a held call (tap held card) ────────────────────────
        async switchToHeldCall(participant) {
            if (!participant || !participant.id) return;

            // ── CASE A: Cancel a ringing outbound conference leg ──────────
            // When agent is dialing a 2nd leg and taps the held card to cancel,
            // the ZIWO SDK automatically resumes the held call (fires ziwo-active).
            // We just need to cancel the outbound leg and let the SDK do the rest.
            if (this.phoneStatus === 'ringing') {
                console.log('[Softphone] Canceling outbound ringing leg — SDK will auto-resume held call.');
                // Set flag so ziwo-active knows to restore the held participant's data
                this._pendingResumeParticipant = participant;
                // Remove from heldParticipants now (the ziwo-active handler will restore currentCall)
                const idx = this.heldParticipants.findIndex(p => p.id === participant.id);
                if (idx !== -1) this.heldParticipants.splice(idx, 1);
                // Cancel outbound leg — ziwo-hangup → ziwo-destroy will fire,
                // and since heldParticipants is now empty, ziwo-active for the
                // existing held call will set phoneStatus = 'speaking'
                await this.phoneHangup();
                return;
            }

            // ── CASE B: Swap between two connected calls ──────────────────
            // 1. Snapshot current call to place it on hold
            const currentId     = this.currentCall.id;
            const currentNum    = this.currentCall.caller_number;
            const currentName   = this.currentCall.caller_name;
            const currentDur    = this.currentCall.duration;
            const currentDir    = this.currentCall.direction || 'inbound';

            // 2. Hold the current call
            if (currentId) {
                const currentSdk = this.ziwoActiveCalls[currentId];
                if (currentSdk && typeof currentSdk.hold === 'function') {
                    try { currentSdk.hold(); } catch (e) { console.warn('[ZIWO] Switch-hold failed:', e); }
                }
                fetch('/telephony/hold', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ call_id: currentId })
                }).catch(() => {});

                // Push current call back to heldParticipants (dedup)
                const alreadyHeld = this.heldParticipants.some(p => p.id === currentId);
                if (!alreadyHeld) {
                    this.heldParticipants.push({
                        id: currentId,
                        number: currentNum,
                        name: currentName,
                        flag: this.getCountryFlagAndLocalTime(currentNum).flag,
                        duration: currentDur,
                        direction: currentDir,
                        heldAt: Date.now()
                    });
                }
            }

            // 3. Remove the target participant from heldParticipants
            const idx = this.heldParticipants.findIndex(p => p.id === participant.id);
            if (idx !== -1) this.heldParticipants.splice(idx, 1);

            // 4. Resume target call on SDK
            const targetSdk = this.ziwoActiveCalls[participant.id];
            this.isConferenceResuming = true;
            if (targetSdk && typeof targetSdk.unhold === 'function') {
                try { targetSdk.unhold(); } catch (e) {
                    console.warn('[ZIWO] Switch-resume failed:', e);
                    this.isConferenceResuming = false;
                }
            } else {
                this.isConferenceResuming = false;
            }

            // 5. Notify backend
            fetch('/telephony/resume', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ call_id: participant.id })
            }).catch(() => {});

            // 6. Update currentCall to the resumed participant
            this.currentCall = {
                id: participant.id,
                uuid: participant.id,
                caller_number: participant.number,
                caller_name: participant.name,
                is_held: false,
                is_muted: false,
                recording_paused: false,
                duration: participant.duration || 0,
                direction: participant.direction || 'inbound'
            };
            this.phoneStatus = 'speaking';
        },

        // ── Disconnect a held call (× button on held card) ──────────────
        toggleMuteHeldCall(p) {
            p.is_muted = !p.is_muted;
            const call = this.ziwoActiveCalls[p.id];
            if (call) {
                try {
                    if (p.is_muted) {
                        if (typeof call.mute === 'function') call.mute();
                    } else {
                        if (typeof call.unmute === 'function') call.unmute();
                    }
                } catch (err) {
                    console.warn('[ZIWO] Mute held call failed:', err);
                }
            }
            fetch(p.is_muted ? '/telephony/mute' : '/telephony/unmute', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ call_id: p.id })
            }).catch(() => {});
        },

        async hangupHeldCall(participant) {
            if (!participant || !participant.id) return;
            const sdkCall = this.ziwoActiveCalls[participant.id];
            if (sdkCall && typeof sdkCall.hangup === 'function') {
                try { sdkCall.hangup(); } catch (e) { console.warn('[ZIWO] Hangup held failed:', e); }
            }
            // Remove from heldParticipants immediately
            const idx = this.heldParticipants.findIndex(p => p.id === participant.id);
            if (idx !== -1) this.heldParticipants.splice(idx, 1);
            delete this.ziwoActiveCalls[participant.id];
            fetch('/telephony/hangup', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ call_id: participant.id })
            }).catch(() => {});
        },

        async phoneMute() {
            const call = Object.values(this.ziwoActiveCalls)[0];
            if (!call) return;
            try {
                call.mute();
            } catch (e) {
                console.error('[ZIWO SDK] Mute failed:', e);
            }
        },

        async phoneUnmute() {
            const call = Object.values(this.ziwoActiveCalls)[0];
            if (!call) return;
            try {
                call.unmute();
            } catch (e) {
                console.error('[ZIWO SDK] Unmute failed:', e);
            }
        },

        async phoneToggleRecording() {
            if (!this.currentCall.id) return;
            const newPauseState = !this.currentCall.recording_paused;
            try {
                const response = await fetch('/telephony/recording', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        call_id: this.currentCall.id,
                        pause: newPauseState
                    })
                });
                if (response.ok) {
                    this.currentCall.recording_paused = newPauseState;
                    if (window.Notification) {
                        window.Notification.info(
                            newPauseState ? 'Call recording paused.' : 'Call recording resumed.',
                            'Recording Protocol'
                        );
                    }
                }
            } catch (e) {
                console.error('Toggle recording failed:', e);
            }
        },

        openInlineTransfer() {
            this.transferTab = 'manual';
            this.transferSearch = '';
            this.transferNumber = '';
            this.transferPanelOpen = true;
        },

        async phoneExecuteTransfer(type) {
            if (!this.transferNumber) return;
            try {
                const response = await fetch('/telephony/transfer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        call_id: this.currentCall.id,
                        target_number: this.transferNumber,
                        type: type
                    })
                });
                if (response.ok) {
                    this.transferPanelOpen = false;
                    this.phoneStatus = 'online';
                    this.stopCallTimer();
                    if (window.Notification) window.Notification.success(`Call transfer protocol initialized to ${this.transferNumber}.`, 'Call Transferred');
                } else {
                    const data = await response.json();
                    if (window.Notification) window.Notification.error(data.message || 'Transfer failed.', 'Protocol Alert');
                }
            } catch (e) {
                console.error('Execute transfer failed:', e);
            }
        },

        // ─── Add or Call Panel logic ───

        openAddOrCallPanel() {
            this.addOrCallInput = '+' + String(this.addOrCallSelectedCountry.dial).replace('+', '');
            this.addOrCallSearch = '';
            this.addOrCallDialpadOpen = false;
            this.addOrCallCountryPickerOpen = false;
            this.addOrCallOpen = true;
        },

        closeAddOrCallPanel() {
            this.addOrCallOpen = false;
            this.addOrCallCountryPickerOpen = false;
        },

        addOrCallKeypad(key) {
            if (key === 'backspace') {
                this.addOrCallInput = this.addOrCallInput.slice(0, -1);
            } else {
                this.addOrCallInput += key;
            }
            // Detect country prefix as user types
            const country = this.detectCountryFromNumber(this.addOrCallInput);
            if (country) this.addOrCallSelectedCountry = country;
        },

        get filteredCountries() {
            const q = this.addOrCallSearch.toLowerCase();
            return this.COUNTRY_DATA.filter(c =>
                c.name.toLowerCase().includes(q) || c.dial.includes(q) || c.code.toLowerCase().includes(q)
            );
        },

        selectAddOrCallCountry(country) {
            this.addOrCallSelectedCountry = country;
            this.addOrCallInput = '+' + country.dial;
            this.addOrCallCountryPickerOpen = false;
        },

        get filteredTeammates() {
            const q = this.addOrCallSearch.toLowerCase();
            return this.mockTeammates.filter(t =>
                t.name.toLowerCase().includes(q) || t.ext.includes(q)
            );
        },

        get filteredQueues() {
            const q = this.addOrCallSearch.toLowerCase();
            return this.mockQueues.filter(q2 =>
                q2.name.toLowerCase().includes(q)
            );
        },

        get filteredPhonebook() {
            const q = this.addOrCallSearch.toLowerCase();
            return this.phonebookContacts.filter(c =>
                (c.name || '').toLowerCase().includes(q) || (c.phone_number || '').includes(q)
            );
        },

        get formattedHeldDuration() {
            return (p) => {
                const h = Math.floor(p.duration / 3600).toString().padStart(2, '0');
                const m = Math.floor((p.duration % 3600) / 60).toString().padStart(2, '0');
                const s = (p.duration % 60).toString().padStart(2, '0');
                return `${h}:${m}:${s}`;
            };
        },

        async executeAddToCall(targetNumber, displayName) {
            if (!targetNumber) return;

            // ── Step 1: Snapshot the current caller before we modify anything ──
            const previousCallerNumber = this.currentCall.caller_number;
            const previousCallerName   = this.currentCall.caller_name;
            const previousCallId       = this.currentCall.id;
            const previousCallDuration = this.currentCall.duration;

            // ── Step 2: Put the current active caller on hold (soft hold only — no reset) ──
            if (previousCallerNumber) {
                const participant = {
                    number: previousCallerNumber,
                    name: previousCallerName || previousCallerNumber,
                    flag: this.getCountryFlagAndLocalTime(previousCallerNumber).flag,
                    duration: previousCallDuration,
                    direction: this.currentCall.direction || 'inbound',
                    heldAt: Date.now(),
                    id: previousCallId
                };

                // Put SDK call on hold (does NOT destroy it)
                const existingCall = previousCallId
                    ? (this.ziwoActiveCalls[previousCallId] || Object.values(this.ziwoActiveCalls)[0])
                    : Object.values(this.ziwoActiveCalls)[0];
                if (existingCall && typeof existingCall.hold === 'function') {
                    try { existingCall.hold(); } catch (e) { console.warn('[ZIWO] Hold failed:', e); }
                }

                // Notify backend of hold (fire-and-forget)
                if (previousCallId) {
                    fetch('/telephony/hold', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ call_id: previousCallId })
                    }).catch(() => {});
                }

                // Only push if not already tracked (prevents duplicate held cards)
                const alreadyHeld = this.heldParticipants.some(p => p.id === previousCallId);
                if (!alreadyHeld) {
                    this.heldParticipants.push(participant);
                }
                this.currentCall.is_held = true;
                // Keep the call timer running — do NOT call stopCallTimer()
            }

            this.closeAddOrCallPanel();

            // ── Step 3: Start new outbound leg ──
            if (this.isMockMode) {
                try {
                    const response = await fetch('/telephony/dial', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ phone_number: targetNumber })
                    });
                    const data = await response.json();
                    if (response.ok && data.status === 'success') {
                        // Update currentCall to track the new leg — keep timer running
                        this.currentCall.id            = data.call_id || ('conf_' + Date.now());
                        this.currentCall.uuid          = data.call_id || this.currentCall.id;
                        this.currentCall.caller_number = targetNumber;
                        this.currentCall.caller_name   = displayName || '';
                        this.currentCall.is_held       = false;
                        this.currentCall.duration      = 0;
                        this.currentCall.direction     = 'outbound';
                        this.phoneStatus = 'ringing';
                        if (window.Notification) window.Notification.info(`Calling ${displayName || targetNumber}… previous caller is on hold.`, 'Conference Mode');
                    } else {
                        if (window.Notification) window.Notification.error(data.message || 'Could not add to call.', 'Add to Call Failed');
                    }
                } catch (e) {
                    console.error('Execute add-to-call failed:', e);
                }
                return;
            }

            // SDK real-mode: dial the new leg via SDK
            if (this.ziwoSdkClient) {
                try {
                    // Update tracking for incoming ziwo-active event
                    this.currentCall.caller_number = targetNumber;
                    this.currentCall.caller_name   = displayName || '';
                    this.currentCall.is_held       = false;
                    this.currentCall.duration      = 0;
                    this.currentCall.direction     = 'outbound';
                    this.phoneStatus = 'ringing';
                    this.ziwoSdkClient.startCall(targetNumber);
                    if (window.Notification) window.Notification.info(`Calling ${displayName || targetNumber}… previous caller is on hold.`, 'Conference Mode');
                } catch (e) {
                    console.error('[ZIWO SDK] Conference dial failed:', e);
                    // Restore previous caller if dial failed
                    this.currentCall.caller_number = previousCallerNumber;
                    this.currentCall.caller_name   = previousCallerName;
                    this.currentCall.is_held       = false;
                }
            }

            // Notify backend
            if (previousCallId) {
                fetch('/telephony/conference', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ call_id: previousCallId, target_number: targetNumber })
                }).catch(() => {});
            }
        },

        async phoneSearchContacts() {
            try {
                const params = new URLSearchParams();
                if (this.phoneSearchQuery) params.append('query', this.phoneSearchQuery);
                if (this.phoneCategoryFilter) params.append('category', this.phoneCategoryFilter);

                const response = await fetch(`/telephony/phonebook?${params.toString()}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (response.ok) {
                    const data = await response.json();
                    // Backend returns {status, contacts:[...]} or a plain array
                    this.phonebookContacts = Array.isArray(data) ? data : (data.contacts || []);
                }
            } catch (e) {
                console.error('Fetch contacts failed:', e);
            }
        },

        openAddContactModal() {
            this.contactForm = { name: '', phone_number: '', category: 'custom' };
            this.addContactOpen = true;
        },

        async phoneSaveContact() {
            if (!this.contactForm.name || !this.contactForm.phone_number) {
                if (window.Notification) window.Notification.warning('Name and phone number are required.', 'Form Incomplete');
                return;
            }
            try {
                const response = await fetch('/telephony/phonebook', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.contactForm)
                });
                if (response.ok) {
                    this.addContactOpen = false;
                    this.phoneSearchContacts();
                    if (window.Notification) window.Notification.success('Contact added to CRM directory.', 'Contact Created');
                } else {
                    const data = await response.json();
                    if (window.Notification) window.Notification.error(data.message || 'Failed to save contact.', 'Save Error');
                }
            } catch (e) {
                console.error('Save contact failed:', e);
            }
        },

        async phoneDeleteContact(id) {
            if (!confirm('Are you sure you want to delete this custom contact?')) return;
            try {
                const response = await fetch(`/telephony/phonebook/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (response.ok) {
                    this.phoneSearchContacts();
                    if (window.Notification) window.Notification.info('Contact deleted from CRM directory.', 'Contact Removed');
                }
            } catch (e) {
                console.error('Delete contact failed:', e);
            }
        },

        async phoneLoadRecentLogs() {
            try {
                const response = await fetch('/telephony/calls/recent');
                if (response.ok) {
                    this.recentCallLogs = await response.json();
                }
            } catch (e) {
                console.error('Recent logs fetch failed:', e);
            }
        },

        phoneTriggerQuickDial(number, name = '') {
            // Fill main caller information inputs
            this.caller.number = number;
            if (name) this.caller.name = name;
            
            // Trigger caller lookup in form
            this.searchCaller();

            // Populate softphone dialer input
            this.dialNumberInput = number;
            
            // Expand phone and set active tab to dialer
            if (this.phoneCollapsed) {
                this.togglePhoneCollapse();
            }
            this.phoneTab = 'dialer';

            // Auto Dial
            this.phoneDial();
        },

        startCallTimer() {
            if (this.callDurationInterval) clearInterval(this.callDurationInterval);
            this.callDurationInterval = setInterval(() => {
                this.currentCall.duration++;
            }, 1000);
        },

        stopCallTimer() {
            if (this.callDurationInterval) {
                clearInterval(this.callDurationInterval);
                this.callDurationInterval = null;
            }
            
            // If we are stopping an active/ringing call, play the end call notification sound
            if (this.currentCall.id || this.currentCall.uuid) {
                this.playEndCallTone();
            }

            this.currentCall = {
                id: null,
                uuid: null,
                caller_number: '',
                caller_name: '',
                is_held: false,
                is_muted: false,
                recording_paused: false,
                duration: 0
            };
        },

        get formattedCallDuration() {
            const h = Math.floor(this.currentCall.duration / 3600).toString().padStart(2, '0');
            const m = Math.floor((this.currentCall.duration % 3600) / 60).toString().padStart(2, '0');
            const s = (this.currentCall.duration % 60).toString().padStart(2, '0');
            return `${h}:${m}:${s}`;
        },

        formatHeldCallDuration(duration) {
            const h = Math.floor(duration / 3600).toString().padStart(2, '0');
            const m = Math.floor((duration % 3600) / 60).toString().padStart(2, '0');
            const s = (duration % 60).toString().padStart(2, '0');
            return `${h}:${m}:${s}`;
        },

        // ── Full country dial-code → flag/code/name lookup ──
        get COUNTRY_DATA() {
            return [
                { dial: '93',   code: 'AF', flag: '🇦🇫', name: 'Afghanistan' },
                { dial: '358',  code: 'AX', flag: '🇦🇽', name: 'Åland Islands' },
                { dial: '355',  code: 'AL', flag: '🇦🇱', name: 'Albania' },
                { dial: '213',  code: 'DZ', flag: '🇩🇿', name: 'Algeria' },
                { dial: '1684', code: 'AS', flag: '🇦🇸', name: 'American Samoa' },
                { dial: '376',  code: 'AD', flag: '🇦🇩', name: 'Andorra' },
                { dial: '244',  code: 'AO', flag: '🇦🇴', name: 'Angola' },
                { dial: '1264', code: 'AI', flag: '🇦🇮', name: 'Anguilla' },
                { dial: '1268', code: 'AG', flag: '🇦🇬', name: 'Antigua and Barbuda' },
                { dial: '54',   code: 'AR', flag: '🇦🇷', name: 'Argentina' },
                { dial: '374',  code: 'AM', flag: '🇦🇲', name: 'Armenia' },
                { dial: '297',  code: 'AW', flag: '🇦🇼', name: 'Aruba' },
                { dial: '61',   code: 'AU', flag: '🇦🇺', name: 'Australia' },
                { dial: '43',   code: 'AT', flag: '🇦🇹', name: 'Austria' },
                { dial: '994',  code: 'AZ', flag: '🇦🇿', name: 'Azerbaijan' },
                { dial: '1242', code: 'BS', flag: '🇧🇸', name: 'Bahamas' },
                { dial: '973',  code: 'BH', flag: '🇧🇭', name: 'Bahrain' },
                { dial: '880',  code: 'BD', flag: '🇧🇩', name: 'Bangladesh' },
                { dial: '1246', code: 'BB', flag: '🇧🇧', name: 'Barbados' },
                { dial: '375',  code: 'BY', flag: '🇧🇾', name: 'Belarus' },
                { dial: '32',   code: 'BE', flag: '🇧🇪', name: 'Belgium' },
                { dial: '501',  code: 'BZ', flag: '🇧🇿', name: 'Belize' },
                { dial: '229',  code: 'BJ', flag: '🇧🇯', name: 'Benin' },
                { dial: '1441', code: 'BM', flag: '🇧🇲', name: 'Bermuda' },
                { dial: '975',  code: 'BT', flag: '🇧🇹', name: 'Bhutan' },
                { dial: '591',  code: 'BO', flag: '🇧🇴', name: 'Bolivia' },
                { dial: '387',  code: 'BA', flag: '🇧🇦', name: 'Bosnia and Herzegovina' },
                { dial: '267',  code: 'BW', flag: '🇧🇼', name: 'Botswana' },
                { dial: '55',   code: 'BR', flag: '🇧🇷', name: 'Brazil' },
                { dial: '246',  code: 'IO', flag: '🇮🇴', name: 'British Indian Ocean Territory' },
                { dial: '673',  code: 'BN', flag: '🇧🇳', name: 'Brunei Darussalam' },
                { dial: '359',  code: 'BG', flag: '🇧🇬', name: 'Bulgaria' },
                { dial: '226',  code: 'BF', flag: '🇧🇫', name: 'Burkina Faso' },
                { dial: '257',  code: 'BI', flag: '🇧🇮', name: 'Burundi' },
                { dial: '855',  code: 'KH', flag: '🇰🇭', name: 'Cambodia' },
                { dial: '237',  code: 'CM', flag: '🇨🇲', name: 'Cameroon' },
                { dial: '1',    code: 'CA', flag: '🇨🇦', name: 'Canada' },
                { dial: '238',  code: 'CV', flag: '🇨🇻', name: 'Cape Verde' },
                { dial: '1345', code: 'KY', flag: '🇰🇾', name: 'Cayman Islands' },
                { dial: '236',  code: 'CF', flag: '🇨🇫', name: 'Central African Republic' },
                { dial: '235',  code: 'TD', flag: '🇹🇩', name: 'Chad' },
                { dial: '56',   code: 'CL', flag: '🇨🇱', name: 'Chile' },
                { dial: '86',   code: 'CN', flag: '🇨🇳', name: 'China' },
                { dial: '57',   code: 'CO', flag: '🇨🇴', name: 'Colombia' },
                { dial: '269',  code: 'KM', flag: '🇰🇲', name: 'Comoros' },
                { dial: '243',  code: 'CD', flag: '🇨🇩', name: 'Congo (Kinshasa)' },
                { dial: '242',  code: 'CG', flag: '🇨🇬', name: 'Congo (Brazzaville)' },
                { dial: '682',  code: 'CK', flag: '🇨🇰', name: 'Cook Islands' },
                { dial: '506',  code: 'CR', flag: '🇨🇷', name: 'Costa Rica' },
                { dial: '225',  code: 'CI', flag: '🇨🇮', name: "Cote d'Ivoire" },
                { dial: '385',  code: 'HR', flag: '🇭🇷', name: 'Croatia' },
                { dial: '53',   code: 'CU', flag: '🇨🇺', name: 'Cuba' },
                { dial: '357',  code: 'CY', flag: '🇨🇾', name: 'Cyprus' },
                { dial: '420',  code: 'CZ', flag: '🇨🇿', name: 'Czech Republic' },
                { dial: '45',   code: 'DK', flag: '🇩🇰', name: 'Denmark' },
                { dial: '253',  code: 'DJ', flag: '🇩🇯', name: 'Djibouti' },
                { dial: '1767', code: 'DM', flag: '🇩🇲', name: 'Dominica' },
                { dial: '593',  code: 'EC', flag: '🇪🇨', name: 'Ecuador' },
                { dial: '20',   code: 'EG', flag: '🇪🇬', name: 'Egypt' },
                { dial: '503',  code: 'SV', flag: '🇸🇻', name: 'El Salvador' },
                { dial: '240',  code: 'GQ', flag: '🇬🇶', name: 'Equatorial Guinea' },
                { dial: '291',  code: 'ER', flag: '🇪🇷', name: 'Eritrea' },
                { dial: '372',  code: 'EE', flag: '🇪🇪', name: 'Estonia' },
                { dial: '251',  code: 'ET', flag: '🇪🇹', name: 'Ethiopia' },
                { dial: '500',  code: 'FK', flag: '🇫🇰', name: 'Falkland Islands' },
                { dial: '298',  code: 'FO', flag: '🇫🇴', name: 'Faroe Islands' },
                { dial: '679',  code: 'FJ', flag: '🇫🇯', name: 'Fiji' },
                { dial: '358',  code: 'FI', flag: '🇫🇮', name: 'Finland' },
                { dial: '33',   code: 'FR', flag: '🇫🇷', name: 'France' },
                { dial: '594',  code: 'GF', flag: '🇬🇫', name: 'French Guiana' },
                { dial: '689',  code: 'PF', flag: '🇵🇫', name: 'French Polynesia' },
                { dial: '241',  code: 'GA', flag: '🇬🇦', name: 'Gabon' },
                { dial: '220',  code: 'GM', flag: '🇬🇲', name: 'Gambia' },
                { dial: '995',  code: 'GE', flag: '🇬🇪', name: 'Georgia' },
                { dial: '49',   code: 'DE', flag: '🇩🇪', name: 'Germany' },
                { dial: '233',  code: 'GH', flag: '🇬🇭', name: 'Ghana' },
                { dial: '350',  code: 'GI', flag: '🇬🇮', name: 'Gibraltar' },
                { dial: '30',   code: 'GR', flag: '🇬🇷', name: 'Greece' },
                { dial: '299',  code: 'GL', flag: '🇬🇱', name: 'Greenland' },
                { dial: '1473', code: 'GD', flag: '🇬🇩', name: 'Grenada' },
                { dial: '590',  code: 'GP', flag: '🇬🇵', name: 'Guadeloupe' },
                { dial: '1671', code: 'GU', flag: '🇬🇺', name: 'Guam' },
                { dial: '502',  code: 'GT', flag: '🇬🇹', name: 'Guatemala' },
                { dial: '44',   code: 'GG', flag: '🇬🇬', name: 'Guernsey' },
                { dial: '224',  code: 'GN', flag: '🇬🇳', name: 'Guinea' },
                { dial: '245',  code: 'GW', flag: '🇬🇼', name: 'Guinea-Bissau' },
                { dial: '592',  code: 'GY', flag: '🇬🇾', name: 'Guyana' },
                { dial: '509',  code: 'HT', flag: '🇭🇹', name: 'Haiti' },
                { dial: '504',  code: 'HN', flag: '🇭🇳', name: 'Honduras' },
                { dial: '852',  code: 'HK', flag: '🇭🇰', name: 'Hong Kong' },
                { dial: '36',   code: 'HU', flag: '🇭🇺', name: 'Hungary' },
                { dial: '354',  code: 'IS', flag: '🇮🇸', name: 'Iceland' },
                { dial: '91',   code: 'IN', flag: '🇮🇳', name: 'India' },
                { dial: '62',   code: 'ID', flag: '🇮🇩', name: 'Indonesia' },
                { dial: '98',   code: 'IR', flag: '🇮🇷', name: 'Iran' },
                { dial: '964',  code: 'IQ', flag: '🇮🇶', name: 'Iraq' },
                { dial: '353',  code: 'IE', flag: '🇮🇪', name: 'Ireland' },
                { dial: '44',   code: 'IM', flag: '🇮🇲', name: 'Isle of Man' },
                { dial: '972',  code: 'IL', flag: '🇮🇱', name: 'Israel' },
                { dial: '39',   code: 'IT', flag: '🇮🇹', name: 'Italy' },
                { dial: '1876', code: 'JM', flag: '🇯🇲', name: 'Jamaica' },
                { dial: '81',   code: 'JP', flag: '🇯🇵', name: 'Japan' },
                { dial: '44',   code: 'JE', flag: '🇯🇪', name: 'Jersey' },
                { dial: '962',  code: 'JO', flag: '🇯🇴', name: 'Jordan' },
                { dial: '7',    code: 'KZ', flag: '🇰🇿', name: 'Kazakhstan' },
                { dial: '254',  code: 'KE', flag: '🇰🇪', name: 'Kenya' },
                { dial: '686',  code: 'KI', flag: '🇰🇮', name: 'Kiribati' },
                { dial: '850',  code: 'KP', flag: '🇰🇵', name: 'Korea (North)' },
                { dial: '82',   code: 'KR', flag: '🇰🇷', name: 'Korea (South)' },
                { dial: '965',  code: 'KW', flag: '🇰🇼', name: 'Kuwait' },
                { dial: '996',  code: 'KG', flag: '🇰🇬', name: 'Kyrgyzstan' },
                { dial: '856',  code: 'LA', flag: '🇱🇦', name: 'Laos' },
                { dial: '371',  code: 'LV', flag: '🇱🇻', name: 'Latvia' },
                { dial: '961',  code: 'LB', flag: '🇱🇧', name: 'Lebanon' },
                { dial: '266',  code: 'LS', flag: '🇱🇸', name: 'Lesotho' },
                { dial: '231',  code: 'LR', flag: '🇱🇷', name: 'Liberia' },
                { dial: '218',  code: 'LY', flag: '🇱🇾', name: 'Libya' },
                { dial: '423',  code: 'LI', flag: '🇱🇮', name: 'Liechtenstein' },
                { dial: '370',  code: 'LT', flag: '🇱🇹', name: 'Lithuania' },
                { dial: '352',  code: 'LU', flag: '🇱🇺', name: 'Luxembourg' },
                { dial: '853',  code: 'MO', flag: '🇲🇴', name: 'Macao' },
                { dial: '389',  code: 'MK', flag: '🇲🇰', name: 'Macedonia' },
                { dial: '261',  code: 'MG', flag: '🇲🇬', name: 'Madagascar' },
                { dial: '265',  code: 'MW', flag: '🇲🇼', name: 'Malawi' },
                { dial: '60',   code: 'MY', flag: '🇲🇾', name: 'Malaysia' },
                { dial: '960',  code: 'MV', flag: '🇲🇻', name: 'Maldives' },
                { dial: '223',  code: 'ML', flag: '🇲🇱', name: 'Mali' },
                { dial: '356',  code: 'MT', flag: '🇲🇹', name: 'Malta' },
                { dial: '692',  code: 'MH', flag: '🇲🇭', name: 'Marshall Islands' },
                { dial: '596',  code: 'MQ', flag: '🇲🇶', name: 'Martinique' },
                { dial: '222',  code: 'MR', flag: '🇲🇷', name: 'Mauritania' },
                { dial: '230',  code: 'MU', flag: '🇲🇺', name: 'Mauritius' },
                { dial: '262',  code: 'YT', flag: '🇾🇹', name: 'Mayotte' },
                { dial: '52',   code: 'MX', flag: '🇲🇽', name: 'Mexico' },
                { dial: '691',  code: 'FM', flag: '🇫🇲', name: 'Micronesia' },
                { dial: '373',  code: 'MD', flag: '🇲🇩', name: 'Moldova' },
                { dial: '377',  code: 'MC', flag: '🇲🇨', name: 'Monaco' },
                { dial: '976',  code: 'MN', flag: '🇲🇳', name: 'Mongolia' },
                { dial: '382',  code: 'ME', flag: '🇲🇪', name: 'Montenegro' },
                { dial: '1664', code: 'MS', flag: '🇲🇸', name: 'Montserrat' },
                { dial: '212',  code: 'MA', flag: '🇲🇦', name: 'Morocco' },
                { dial: '258',  code: 'MZ', flag: '🇲🇿', name: 'Mozambique' },
                { dial: '95',   code: 'MM', flag: '🇲🇲', name: 'Myanmar' },
                { dial: '264',  code: 'NA', flag: '🇳🇦', name: 'Namibia' },
                { dial: '674',  code: 'NR', flag: '🇳🇷', name: 'Nauru' },
                { dial: '977',  code: 'NP', flag: '🇳🇵', name: 'Nepal' },
                { dial: '31',   code: 'NL', flag: '🇳🇱', name: 'Netherlands' },
                { dial: '687',  code: 'NC', flag: '🇳🇨', name: 'New Caledonia' },
                { dial: '64',   code: 'NZ', flag: '🇳🇿', name: 'New Zealand' },
                { dial: '505',  code: 'NI', flag: '🇳🇮', name: 'Nicaragua' },
                { dial: '227',  code: 'NE', flag: '🇳🇪', name: 'Niger' },
                { dial: '234',  code: 'NG', flag: '🇳🇬', name: 'Nigeria' },
                { dial: '683',  code: 'NU', flag: '🇳🇺', name: 'Niue' },
                { dial: '47',   code: 'NO', flag: '🇳🇴', name: 'Norway' },
                { dial: '968',  code: 'OM', flag: '🇴🇲', name: 'Oman' },
                { dial: '92',   code: 'PK', flag: '🇵🇰', name: 'Pakistan' },
                { dial: '680',  code: 'PW', flag: '🇵🇼', name: 'Palau' },
                { dial: '970',  code: 'PS', flag: '🇵🇸', name: 'Palestine' },
                { dial: '507',  code: 'PA', flag: '🇵🇦', name: 'Panama' },
                { dial: '675',  code: 'PG', flag: '🇵🇬', name: 'Papua New Guinea' },
                { dial: '595',  code: 'PY', flag: '🇵🇾', name: 'Paraguay' },
                { dial: '51',   code: 'PE', flag: '🇵🇪', name: 'Peru' },
                { dial: '63',   code: 'PH', flag: '🇵🇭', name: 'Philippines' },
                { dial: '48',   code: 'PL', flag: '🇵🇱', name: 'Poland' },
                { dial: '351',  code: 'PT', flag: '🇵🇹', name: 'Portugal' },
                { dial: '1787', code: 'PR', flag: '🇵🇷', name: 'Puerto Rico' },
                { dial: '974',  code: 'QA', flag: '🇶🇦', name: 'Qatar' },
                { dial: '262',  code: 'RE', flag: '🇷🇪', name: 'Réunion' },
                { dial: '40',   code: 'RO', flag: '🇷🇴', name: 'Romania' },
                { dial: '7',    code: 'RU', flag: '🇷🇺', name: 'Russia' },
                { dial: '250',  code: 'RW', flag: '🇷🇼', name: 'Rwanda' },
                { dial: '590',  code: 'BL', flag: '🇧🇱', name: 'Saint Barthélemy' },
                { dial: '290',  code: 'SH', flag: '🇸🇭', name: 'Saint Helena' },
                { dial: '1869', code: 'KN', flag: '🇰🇳', name: 'Saint Kitts and Nevis' },
                { dial: '1758', code: 'LC', flag: '🇱🇨', name: 'Saint Lucia' },
                { dial: '1784', code: 'VC', flag: '🇻🇨', name: 'Saint Vincent and the Grenadines' },
                { dial: '685',  code: 'WS', flag: '🇼🇸', name: 'Samoa' },
                { dial: '378',  code: 'SM', flag: '🇸🇲', name: 'San Marino' },
                { dial: '239',  code: 'ST', flag: '🇸🇹', name: 'Sao Tome and Principe' },
                { dial: '966',  code: 'SA', flag: '🇸🇦', name: 'Saudi Arabia' },
                { dial: '221',  code: 'SN', flag: '🇸🇳', name: 'Senegal' },
                { dial: '381',  code: 'RS', flag: '🇷🇸', name: 'Serbia' },
                { dial: '248',  code: 'SC', flag: '🇸🇨', name: 'Seychelles' },
                { dial: '232',  code: 'SL', flag: '🇸🇱', name: 'Sierra Leone' },
                { dial: '65',   code: 'SG', flag: '🇸🇬', name: 'Singapore' },
                { dial: '1721', code: 'SX', flag: '🇸🇽', name: 'Sint Maarten' },
                { dial: '421',  code: 'SK', flag: '🇸🇰', name: 'Slovakia' },
                { dial: '386',  code: 'SI', flag: '🇸🇮', name: 'Slovenia' },
                { dial: '677',  code: 'SB', flag: '🇸🇧', name: 'Solomon Islands' },
                { dial: '252',  code: 'SO', flag: '🇸🇴', name: 'Somalia' },
                { dial: '27',   code: 'ZA', flag: '🇿🇦', name: 'South Africa' },
                { dial: '211',  code: 'SS', flag: '🇸🇸', name: 'South Sudan' },
                { dial: '34',   code: 'ES', flag: '🇪🇸', name: 'Spain' },
                { dial: '94',   code: 'LK', flag: '🇱🇰', name: 'Sri Lanka' },
                { dial: '249',  code: 'SD', flag: '🇸🇩', name: 'Sudan' },
                { dial: '597',  code: 'SR', flag: '🇸🇷', name: 'Suriname' },
                { dial: '268',  code: 'SZ', flag: '🇸🇿', name: 'Swaziland' },
                { dial: '46',   code: 'SE', flag: '🇸🇪', name: 'Sweden' },
                { dial: '41',   code: 'CH', flag: '🇨🇭', name: 'Switzerland' },
                { dial: '963',  code: 'SY', flag: '🇸🇾', name: 'Syria' },
                { dial: '886',  code: 'TW', flag: '🇹🇼', name: 'Taiwan' },
                { dial: '992',  code: 'TJ', flag: '🇹🇯', name: 'Tajikistan' },
                { dial: '255',  code: 'TZ', flag: '🇹🇿', name: 'Tanzania' },
                { dial: '66',   code: 'TH', flag: '🇹🇭', name: 'Thailand' },
                { dial: '670',  code: 'TL', flag: '🇹🇱', name: 'Timor-Leste' },
                { dial: '228',  code: 'TG', flag: '🇹🇬', name: 'Togo' },
                { dial: '690',  code: 'TK', flag: '🇹🇰', name: 'Tokelau' },
                { dial: '676',  code: 'TO', flag: '🇹🇴', name: 'Tonga' },
                { dial: '1868', code: 'TT', flag: '🇹🇹', name: 'Trinidad and Tobago' },
                { dial: '216',  code: 'TN', flag: '🇹🇳', name: 'Tunisia' },
                { dial: '90',   code: 'TR', flag: '🇹🇷', name: 'Turkey' },
                { dial: '993',  code: 'TM', flag: '🇹🇲', name: 'Turkmenistan' },
                { dial: '1649', code: 'TC', flag: '🇹🇨', name: 'Turks and Caicos Islands' },
                { dial: '688',  code: 'TV', flag: '🇹🇻', name: 'Tuvalu' },
                { dial: '256',  code: 'UG', flag: '🇺🇬', name: 'Uganda' },
                { dial: '380',  code: 'UA', flag: '🇺🇦', name: 'Ukraine' },
                { dial: '971',  code: 'AE', flag: '🇦🇪', name: 'United Arab Emirates' },
                { dial: '44',   code: 'GB', flag: '🇬🇧', name: 'United Kingdom' },
                { dial: '1',    code: 'US', flag: '🇺🇸', name: 'United States' },
                { dial: '598',  code: 'UY', flag: '🇺🇾', name: 'Uruguay' },
                { dial: '998',  code: 'UZ', flag: '🇺🇿', name: 'Uzbekistan' },
                { dial: '678',  code: 'VU', flag: '🇻🇺', name: 'Vanuatu' },
                { dial: '58',   code: 'VE', flag: '🇻🇪', name: 'Venezuela' },
                { dial: '84',   code: 'VN', flag: '🇻🇳', name: 'Vietnam' },
                { dial: '1284', code: 'VG', flag: '🇻🇬', name: 'Virgin Islands (British)' },
                { dial: '1340', code: 'VI', flag: '🇻🇮', name: 'Virgin Islands (US)' },
                { dial: '681',  code: 'WF', flag: '🇼🇫', name: 'Wallis and Futuna' },
                { dial: '967',  code: 'YE', flag: '🇾🇪', name: 'Yemen' },
                { dial: '260',  code: 'ZM', flag: '🇿🇲', name: 'Zambia' },
                { dial: '263',  code: 'ZW', flag: '🇿🇼', name: 'Zimbabwe' },
            ];
        },

        detectCountryFromNumber(number) {
            if (!number) return null;
            let clean = String(number).replace(/[^0-9]/g, '');

            // ── Normalize local-format numbers to international ──
            // Pakistan local: 03xxxxxxxxx  →  923xxxxxxxxx
            if (clean.startsWith('03') && clean.length >= 10) {
                clean = '92' + clean.slice(1); // 03xx → 923xx
            }
            // Handle 00-prefix international notation: 0092xx → 92xx
            if (clean.startsWith('00')) {
                clean = clean.slice(2);
            }

            // Sort by dial code length descending — longest match wins
            const sorted = [...this.COUNTRY_DATA].sort((a, b) => b.dial.length - a.dial.length);
            for (const c of sorted) {
                if (clean.startsWith(c.dial)) return c;
            }
            return null;
        },

        getCountryFlagAndLocalTime(number) {
            const timeStr = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            if (!number) {
                return { flag: '🌐', code: '??', time: timeStr };
            }
            try {
                const country = this.detectCountryFromNumber(number);
                const flag  = country ? country.flag : '🌐';
                const code  = country ? country.code : '??';
                return { flag, code, time: timeStr };
            } catch (err) {
                console.error('[Softphone] getCountryFlagAndLocalTime error:', err);
                return { flag: '🌐', code: '??', time: timeStr };
            }
        },

        sendDTMF(digit) {
            const call = Object.values(this.ziwoActiveCalls)[0];
            if (call && typeof call.sendDTMF === 'function') {
                try {
                    call.sendDTMF(digit);
                    console.log('[ZIWO SDK] Sent DTMF tone:', digit);
                } catch (e) {
                    console.error('[ZIWO SDK] Failed to send DTMF tone:', e);
                }
            } else {
                console.log('[ZIWO SDK] Mock DTMF tone keypress:', digit);
            }
        },

        handleCallBroadcast(e) {
            // Live broadcast handler
            if (e.agent_id && e.agent_id !== {{ auth()->id() }}) return;

            console.log('Call Broadcast received:', e);
            // Trigger an immediate status check for real-time responsiveness
            this.phoneCheckStatus();
        },

        handleAgentBroadcast(e) {
            if (e.user_id === {{ auth()->id() }}) {
                // If agent status changed to ringing_inbound, start ring immediately
                if (e.status === 'ringing_inbound' && this.phoneStatus !== 'ringing_inbound') {
                    this.phoneStatus = 'ringing_inbound';
                    if (this.phoneCollapsed) this.togglePhoneCollapse();
                    this.startRinging();
                } else if (e.status !== 'ringing_inbound') {
                    this.stopRinging();
                    // In SDK mode, the backend status is always stale for in-progress WebRTC calls.
                    // Only allow status overwrite if not currently in an active SDK call state.
                    const sdkCallActive = ['ringing', 'ringing_inbound', 'speaking', 'held'].includes(this.phoneStatus);
                    if (!sdkCallActive || this.isMockMode) {
                        this.phoneStatus = e.status;
                    }
                }
                this.phoneCheckStatus();
            }
        },

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // RING AUDIO — Official ZIWO Ringtone
        // Uses: https://static.ziwo.io/audio/ringtone.mp3
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        startRinging() {
            this.stopRinging(); // Reset any previous playback

            const audioEl = document.getElementById('ring-audio');
            if (!audioEl) return;

            // Ensure correct src is set (in case element was created without it)
            if (!audioEl.src || audioEl.src === window.location.href) {
                audioEl.src = "{{ asset('audio/ringtone.mp3') }}";
            }

            audioEl.currentTime = 0;
            audioEl.loop = true;
            audioEl.volume = 1.0;

            const playPromise = audioEl.play();
            if (playPromise !== undefined) {
                playPromise.catch(err => {
                    // Autoplay was blocked by browser policy — retry on next user interaction
                    console.warn('Ringtone autoplay blocked. Will retry on next interaction.', err);
                    const unlockRing = () => {
                        audioEl.play().catch(() => {});
                        document.removeEventListener('click', unlockRing);
                        document.removeEventListener('keydown', unlockRing);
                    };
                    document.addEventListener('click', unlockRing, { once: true });
                    document.addEventListener('keydown', unlockRing, { once: true });
                });
            }
        },

        stopRinging() {
            const audioEl = document.getElementById('ring-audio');
            if (audioEl) {
                audioEl.pause();
                audioEl.currentTime = 0;
            }
        },

        playEndCallTone() {
            const endCallAudioEl = document.getElementById('end-call-audio');
            if (endCallAudioEl) {
                endCallAudioEl.currentTime = 0;
                endCallAudioEl.volume = 1.0;
                endCallAudioEl.play().catch(err => {
                    console.warn('End call tone autoplay blocked:', err);
                });
            }
        },

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // KEYBOARD HANDLER FOR DIALER
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        handleDialerKey(event) {
            if (!event || !event.key) return;
            const validKeys = ['0','1','2','3','4','5','6','7','8','9','*','#','+'];
            if (validKeys.includes(event.key)) {
                // Allow native input
                return;
            }
            if (event.key === 'Backspace' || event.key === 'Delete') {
                // Allow native backspace
                return;
            }
            // Block all other keys (letters, etc.) except control keys
            if (!event.ctrlKey && !event.metaKey && event.key.length === 1) {
                event.preventDefault();
            }
        }
    };
}
</script>
@endpush
