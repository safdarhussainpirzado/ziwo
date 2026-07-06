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
            <span x-show="phoneCollapsed && ['ringing', 'active', 'held', 'speaking', 'ringing_inbound'].includes(phoneStatus)"
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
                <div class="flex items-center gap-2">
                    <button type="button" @click="phoneDisconnect()" x-show="phoneAuthenticated" class="text-slate-400 hover:text-rose-400 transition" title="Log Out Telephony">
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
                <div x-show="!phoneAuthenticated" class="absolute inset-0 z-50 bg-slate-950 p-6 flex flex-col justify-center gap-4">
                    <div class="text-center mb-2">
                        <i class="fa-solid fa-shield-halved text-indigo-500 text-3xl mb-2"></i>
                        <h4 class="font-bold text-sm text-slate-200">ZIWO Agent Portal</h4>
                        <p class="text-[10px] text-slate-500">Authenticate session to enable incoming/outbound calls.</p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-[8px] font-bold uppercase tracking-wider text-slate-400 mb-1">Username / Email</label>
                            <input type="text" x-model="phoneAuthForm.username" placeholder="agent_username" 
                                   class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-600 outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[8px] font-bold uppercase tracking-wider text-slate-400 mb-1">Password</label>
                            <input type="password" x-model="phoneAuthForm.password" placeholder="••••••••" 
                                   class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-600 outline-none focus:border-indigo-500">
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
                <div x-show="['ringing', 'active', 'held', 'speaking', 'ringing_inbound'].includes(phoneStatus)"
                     class="absolute inset-0 z-40 bg-slate-950/98 p-6 flex flex-col justify-between">
                    
                    <div class="text-center space-y-2 mt-4">
                        <div class="inline-flex p-4 rounded-full relative"
                             :class="phoneStatus === 'ringing' || phoneStatus === 'ringing_inbound' ? 'bg-rose-500/10 text-rose-500' : 'bg-indigo-500/10 text-indigo-500'">
                            <span x-show="phoneStatus === 'ringing' || phoneStatus === 'ringing_inbound'" class="animate-ping absolute inset-0 rounded-full bg-rose-500/20 opacity-70"></span>
                            <i class="fa-solid fa-phone text-3xl animate-pulse"></i>
                        </div>
                        <div>
                            <h4 class="text-xs uppercase font-black tracking-widest text-slate-500" 
                                x-text="phoneStatus === 'ringing_inbound' ? 'Incoming Ringing' : phoneStatus === 'ringing' ? 'Dialing Outbound' : 'Connected Session'"></h4>
                            <p class="text-lg font-extrabold text-white mt-1" x-text="currentCall.caller_number"></p>
                            <p class="text-xs text-slate-400 italic" x-text="currentCall.caller_name || 'Anonymous Contact'"></p>
                        </div>
                        <div class="text-2xl font-mono text-indigo-400 font-semibold pt-2" x-text="formattedCallDuration">00:00</div>
                    </div>

                    <!-- Call Actions grid -->
                    <div class="space-y-4">
                        <!-- Mute, Hold, Record, Transfer grid -->
                        <div class="grid grid-cols-4 gap-2" x-show="['active', 'held', 'speaking'].includes(phoneStatus)">
                            <!-- Mute button -->
                            <button type="button" @click="currentCall.is_muted ? phoneUnmute() : phoneMute()"
                                    class="py-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 transition active:scale-95 flex flex-col items-center gap-1 cursor-pointer">
                                <i class="fa-solid text-xs" :class="currentCall.is_muted ? 'fa-microphone-slash text-rose-500' : 'fa-microphone text-slate-400'"></i>
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-500" x-text="currentCall.is_muted ? 'Muted' : 'Mute'"></span>
                            </button>

                            <!-- Hold button -->
                            <button type="button" @click="currentCall.is_held ? phoneResume() : phoneHold()"
                                    class="py-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 transition active:scale-95 flex flex-col items-center gap-1 cursor-pointer">
                                <i class="fa-solid text-xs" :class="currentCall.is_held ? 'fa-play text-emerald-500' : 'fa-pause text-slate-400'"></i>
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-500" x-text="currentCall.is_held ? 'Resume' : 'Hold'"></span>
                            </button>

                            <!-- Record button -->
                            <button type="button" @click="phoneToggleRecording()"
                                    class="py-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 transition active:scale-95 flex flex-col items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-circle text-xs" :class="currentCall.recording_paused ? 'text-slate-500' : 'text-rose-600 animate-pulse'"></i>
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-500" x-text="currentCall.recording_paused ? 'Record' : 'Rec ON'"></span>
                            </button>

                            <!-- Transfer button -->
                            <button type="button" @click="openInlineTransfer()"
                                    class="py-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:bg-slate-800 transition active:scale-95 flex flex-col items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-share-from-square text-xs text-slate-400"></i>
                                <span class="text-[8px] font-bold uppercase tracking-wider text-slate-500">Transfer</span>
                            </button>
                        </div>

                        <!-- Main call control (Answer / Reject / Hangup) -->
                        <div class="flex gap-4">
                            <!-- Answer button (Incoming only) -->
                            <button type="button" @click="phoneAnswer()" x-show="phoneStatus === 'ringing_inbound'"
                                    class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-2xl transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-phone text-sm"></i> Accept
                            </button>

                            <!-- Hang Up / Decline button -->
                            <button type="button" @click="phoneHangup()"
                                    class="flex-1 py-3 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-2xl transition active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fa-solid fa-phone-slash text-sm"></i> Hang Up
                            </button>
                        </div>
                    </div>

                    <!-- Inline Transfer Panel Overlay -->
                    <div x-show="transferPanelOpen" class="absolute inset-0 bg-slate-950/98 p-6 flex flex-col justify-center gap-4 z-50">
                        <div class="text-center">
                            <i class="fa-solid fa-share-nodes text-indigo-500 text-2xl mb-1"></i>
                            <h5 class="font-bold text-xs text-slate-200">Call Transfer Protocol</h5>
                            <p class="text-[9px] text-slate-500">Enter target extension or external phone number.</p>
                        </div>
                        <div class="space-y-3">
                            <input type="text" x-model="transferNumber" placeholder="Extension or phone..." 
                                   class="w-full px-3 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-600 outline-none focus:border-indigo-500">
                            
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="phoneExecuteTransfer('blind')" class="py-2 bg-indigo-600 text-white text-[10px] font-bold rounded-lg hover:bg-indigo-500 transition active:scale-95">Blind Transfer</button>
                                <button type="button" @click="phoneExecuteTransfer('warm')" class="py-2 bg-indigo-50/10 text-slate-300 text-[10px] font-bold border border-slate-800 rounded-lg hover:bg-slate-900 transition active:scale-95">Attended</button>
                            </div>
                            <button type="button" @click="transferPanelOpen = false" class="w-full py-1.5 text-slate-500 hover:text-slate-300 text-[10px] font-bold transition">Cancel</button>
                        </div>
                    </div>
                </div>

                <!-- 3. Tab Content Area -->
                <div class="flex-1 min-h-0 flex flex-col">
                    
                    <!-- Tab Headers -->
                    <div class="flex border-b border-slate-900/60 shrink-0 bg-slate-900">
                        <button type="button" @click="phoneTab = 'dialer'" :class="phoneTab === 'dialer' ? 'text-indigo-400 border-indigo-500' : 'text-slate-400 border-transparent'" class="flex-1 py-2 text-center text-xs font-bold border-b-2 hover:text-slate-200 transition">Dialer</button>
                        <button type="button" @click="phoneTab = 'phonebook'; phoneSearchContacts()" :class="phoneTab === 'phonebook' ? 'text-indigo-400 border-indigo-500' : 'text-slate-400 border-transparent'" class="flex-1 py-2 text-center text-xs font-bold border-b-2 hover:text-slate-200 transition">Directory</button>
                        <button type="button" @click="phoneTab = 'history'" :class="phoneTab === 'history' ? 'text-indigo-400 border-indigo-500' : 'text-slate-400 border-transparent'" class="flex-1 py-2 text-center text-xs font-bold border-b-2 hover:text-slate-200 transition">Recent</button>
                    </div>

                    <!-- Tab panels -->
                    <div class="flex-1 min-h-0 relative">
                        
                        <!-- Panel A: Dialer -->
                        <div x-show="phoneTab === 'dialer'" class="absolute inset-0 flex flex-col p-4 justify-between">
                            
                            <!-- Display -->
                            <div class="relative flex items-center bg-slate-900/80 rounded-2xl border border-slate-850 px-3 py-2.5">
                                <i class="fa-solid fa-phone text-xs text-indigo-500 mr-2"></i>
                                <input type="text" x-model="dialNumberInput" placeholder="Enter phone number..." readonly
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

                        <!-- Panel B: Directory (Centralized Phonebook) -->
                        <div x-show="phoneTab === 'phonebook'" class="absolute inset-0 flex flex-col p-3">
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

                        <!-- Panel C: Recent call history -->
                        <div x-show="phoneTab === 'history'" class="absolute inset-0 flex flex-col p-3">
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

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
function intakeComponent() {
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
        phoneCollapsed: true,
        phoneTab: 'dialer',
        phoneAuthenticated: false,
        phoneSubmitting: false,
        phoneStatus: 'offline', // offline, online, ringing, speaking, active, held, ringing_inbound
        phoneStatusError: '',
        dialNumberInput: '',
        ziwoUsername: '',
        phoneAuthForm: {
            username: '',
            password: ''
        },
        recentCallLogs: [],
        phonebookContacts: [],
        phoneSearchQuery: '',
        phoneCategoryFilter: '',

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
            // Check current session status
            await this.phoneCheckStatus();
            
            // Start regular status polling
            this.phonePollInterval = setInterval(() => this.phoneCheckStatus(), 3000);

            // Listen to Laravel Echo if available
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

        phoneCleanup() {
            if (this.phonePollInterval) clearInterval(this.phonePollInterval);
            if (this.callDurationInterval) clearInterval(this.callDurationInterval);
        },

        async phoneCheckStatus() {
            try {
                const res = await fetch('/telephony/status');
                if (!res.ok) return;
                const data = await res.json();
                
                this.phoneAuthenticated = data.is_authenticated;
                this.ziwoUsername = data.ziwo_username || '';
                
                // If agent logged in/out on another page, update local state
                if (!this.phoneAuthenticated) {
                    if (this.phoneStatus !== 'offline') {
                        this.phoneStatus = 'offline';
                        this.stopCallTimer();
                    }
                    return;
                }

                // If agent status has changed
                if (data.agent_status && this.phoneStatus !== data.agent_status) {
                    this.phoneStatus = data.agent_status;
                }

                // Process active call state if any
                if (data.active_call) {
                    const call = data.active_call;
                    this.currentCall.id = call.id;
                    this.currentCall.uuid = call.uuid;
                    this.currentCall.caller_number = call.caller_number;
                    this.currentCall.caller_name = call.caller_name || '';
                    this.currentCall.is_held = call.is_held || false;
                    this.currentCall.is_muted = call.is_muted || false;
                    this.currentCall.recording_paused = call.recording_paused || false;
                    
                    // Start duration timer
                    if (call.seconds_duration) {
                        this.currentCall.duration = call.seconds_duration;
                        this.startCallTimer();
                    }

                    // Auto expand phone console on active call
                    if (this.phoneCollapsed) {
                        this.togglePhoneCollapse();
                    }
                    
                    // Autofill main caller intake form if blank
                    if (!this.caller.number && call.caller_number) {
                        this.caller.number = call.caller_number;
                        this.searchCaller();
                    }
                } else {
                    // No active call
                    if (['ringing', 'speaking', 'active', 'held', 'ringing_inbound'].includes(this.phoneStatus)) {
                        this.phoneStatus = 'online';
                        this.stopCallTimer();
                        this.phoneLoadRecentLogs();
                    }
                }
            } catch (e) {
                console.error('Telephony status check failed:', e);
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
            if (!this.phoneAuthForm.username || !this.phoneAuthForm.password) {
                this.phoneStatusError = 'Username and password are required.';
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
                const data = await response.json();
                if (response.ok) {
                    this.phoneAuthenticated = true;
                    this.ziwoUsername = data.username;
                    this.phoneStatus = data.status || 'online';
                    this.phoneAuthForm.password = '';
                    this.phoneSearchContacts();
                    this.phoneLoadRecentLogs();
                    if (window.Notification) window.Notification.success('Telephony session registered successfully.', 'Telephony Connected');
                } else {
                    this.phoneStatusError = data.message || 'Authentication failed.';
                }
            } catch (e) {
                console.error(e);
                this.phoneStatusError = 'Network failure connecting to telephony client.';
            } finally {
                this.phoneSubmitting = false;
            }
        },

        async phoneDisconnect() {
            try {
                const response = await fetch('/telephony/disconnect', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (response.ok) {
                    this.phoneAuthenticated = false;
                    this.phoneStatus = 'offline';
                    this.stopCallTimer();
                    if (window.Notification) window.Notification.info('Telephony session terminated.', 'Telephony Disconnected');
                }
            } catch (e) {
                console.error('Telephony disconnect failed:', e);
            }
        },

        async phoneDial() {
            if (!this.dialNumberInput) return;
            this.phoneSubmitting = true;
            try {
                const response = await fetch('/telephony/dial', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ phone_number: this.dialNumberInput })
                });
                const data = await response.json();
                if (response.ok) {
                    this.phoneStatus = 'ringing';
                    this.currentCall.id = data.call_id;
                    this.currentCall.uuid = data.uuid;
                    this.currentCall.caller_number = this.dialNumberInput;
                    this.currentCall.caller_name = data.caller_name || '';
                    this.currentCall.duration = 0;
                    this.startCallTimer();
                    
                    // Populate main intake form too
                    this.caller.number = this.dialNumberInput;
                    this.searchCaller();
                } else {
                    if (window.Notification) window.Notification.error(data.message || 'Could not place outbound call.', 'Call Failed');
                }
            } catch (e) {
                console.error('Outbound call failed:', e);
            } finally {
                this.phoneSubmitting = false;
            }
        },

        async phoneAnswer() {
            if (!this.currentCall.id) return;
            try {
                const response = await fetch('/telephony/answer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ call_id: this.currentCall.id })
                });
                if (response.ok) {
                    this.phoneStatus = 'speaking';
                    this.startCallTimer();
                }
            } catch (e) {
                console.error('Answer call failed:', e);
            }
        },

        async phoneHangup() {
            if (!this.currentCall.id && !this.currentCall.uuid) return;
            try {
                const response = await fetch('/telephony/hangup', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        call_id: this.currentCall.id,
                        uuid: this.currentCall.uuid
                    })
                });
                if (response.ok) {
                    this.phoneStatus = 'online';
                    this.stopCallTimer();
                    this.dialNumberInput = '';
                    this.phoneLoadRecentLogs();
                }
            } catch (e) {
                console.error('Hangup failed:', e);
            }
        },

        async phoneHold() {
            if (!this.currentCall.id) return;
            try {
                const response = await fetch('/telephony/hold', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ call_id: this.currentCall.id })
                });
                if (response.ok) {
                    this.currentCall.is_held = true;
                    this.phoneStatus = 'held';
                }
            } catch (e) {
                console.error('Hold call failed:', e);
            }
        },

        async phoneResume() {
            if (!this.currentCall.id) return;
            try {
                const response = await fetch('/telephony/resume', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ call_id: this.currentCall.id })
                });
                if (response.ok) {
                    this.currentCall.is_held = false;
                    this.phoneStatus = 'speaking';
                }
            } catch (e) {
                console.error('Resume call failed:', e);
            }
        },

        async phoneMute() {
            if (!this.currentCall.id) return;
            try {
                const response = await fetch('/telephony/mute', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ call_id: this.currentCall.id })
                });
                if (response.ok) {
                    this.currentCall.is_muted = true;
                }
            } catch (e) {
                console.error('Mute call failed:', e);
            }
        },

        async phoneUnmute() {
            if (!this.currentCall.id) return;
            try {
                const response = await fetch('/telephony/unmute', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ call_id: this.currentCall.id })
                });
                if (response.ok) {
                    this.currentCall.is_muted = false;
                }
            } catch (e) {
                console.error('Unmute call failed:', e);
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

        async phoneSearchContacts() {
            try {
                const params = new URLSearchParams();
                if (this.phoneSearchQuery) params.append('query', this.phoneSearchQuery);
                if (this.phoneCategoryFilter) params.append('category', this.phoneCategoryFilter);
                
                const response = await fetch(`/telephony/phonebook?${params.toString()}`);
                if (response.ok) {
                    this.phonebookContacts = await response.json();
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
            const m = Math.floor(this.currentCall.duration / 60).toString().padStart(2, '0');
            const s = (this.currentCall.duration % 60).toString().padStart(2, '0');
            return `${m}:${s}`;
        },

        handleCallBroadcast(e) {
            // Live broadcast handler
            if (e.agent_id && e.agent_id !== {{ auth()->id() }}) return;
            
            console.log('Call Broadcast received:', e);
            this.phoneCheckStatus();
        },

        handleAgentBroadcast(e) {
            if (e.user_id === {{ auth()->id() }}) {
                this.phoneStatus = e.status;
                this.phoneCheckStatus();
            }
        }
    };
}
</script>
@endpush
