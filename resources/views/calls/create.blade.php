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

    {{-- Premium softphone (split into partials/softphone/*.blade.php) --}}
    @include('partials.softphone.shell')

    {{--
        Hidden machine runner: mounts Alpine.data('softphone') so the state machine,
        ziwo-adapter, SDK event bridge and status poll all start up.
        No visible UI — all reactive state flows back via Alpine.store('softphone').
    --}}
    @auth
    <div id="softphone-machine" x-data="softphone" style="display:none" aria-hidden="true"></div>
    @endauth

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
        phoneAgentStatus: 'offline', // agent presence/availability (Ziwo PBX): available|meeting|break|outgoing|offline
        phoneUpdatingStatus: false,
        get phoneCallActive() {
            return ['ringing', 'active', 'held', 'speaking', 'ringing_inbound'].includes(this.phoneStatus);
        },

        // Premium softphone helpers (used by partials/softphone/*)
        /**
         * Map ZIWO's live presence string to our 4-state UI vocabulary.
         * ZIWO returns strings like "Available" / "On Break" / "Meeting" / "Outgoing".
         */
        mapZiwoAgentStatus(z) {
            const s = (z || '').toString().trim().toLowerCase();
            if (s === 'available' || s === 'online' || s === 'active' || s === 'ready') return 'available';
            if (s === 'on break' || s === 'break' || s === 'away') return 'break';
            if (s === 'meeting') return 'meeting';
            if (s === 'outgoing' || s === 'busy') return 'outgoing';
            return 'available';
        },
        get filteredPhonebook() {
            const q = (this.phoneSearchQuery || '').toLowerCase();
            // Normalize: API returns { contacts: [...] } - ensure we always have an array
            const list = Array.isArray(this.phonebookContacts)
                ? this.phonebookContacts
                : (Array.isArray(this.phonebookContacts?.contacts) ? this.phonebookContacts.contacts : []);
            return list.filter(c =>
                !q || (c.name || '').toLowerCase().includes(q) || (c.phone_number || c.phone || '').includes(q)
            );
        },
        get filteredTeammates() {
            const q = (this.transferSearch || '').toLowerCase();
            return (this.ziwoTeammates || []).filter(t =>
                !q || (t.name || '').toLowerCase().includes(q) || (t.ext || t.number || '').includes(q)
            );
        },
        get filteredQueues() {
            const q = (this.transferSearch || '').toLowerCase();
            return (this.ziwoQueues || []).filter(qq =>
                !q || (qq.name || '').toLowerCase().includes(q) || (qq.number || '').includes(q)
            );
        },
        formatSecondsToShort(total) {
            const s = parseInt(total, 10) || 0;
            const h = Math.floor(s / 3600);
            const m = Math.floor((s % 3600) / 60);
            if (h > 0) return h + 'h' + m + 'm';
            if (m > 0) return m + 'm';
            return s + 's';
        },
        get formattedCallDuration() {
            const total = this.currentCall?.duration || 0;
            const s = parseInt(total, 10) || 0;
            const m = Math.floor(s / 60);
            const sec = s % 60;
            return [
                m.toString().padStart(2, '0'),
                sec.toString().padStart(2, '0')
            ].join(':');
        },

        formattedHeldDuration(participant) {
            if (!participant?.heldAt) return '0:00';
            const secs = Math.floor((Date.now() - participant.heldAt) / 1000);
            const m = Math.floor(secs / 60);
            const s = secs % 60;
            return m + ':' + s.toString().padStart(2, '0');
        },

        // ── STATE MACHINE (focused refactor) ──────────────────────────
        // Single source of truth for status. All transitions funneled through `send()`.
        get phoneState() {
            const s = this.phoneStatus;
            return {
                current: s,
                inCall:       ['ringing_outbound', 'ringing', 'active', 'held', 'speaking', 'ringing_inbound'].includes(s),
                ringing:     ['ringing_outbound', 'ringing', 'ringing_inbound'].includes(s),
                ringingOut:   s === 'ringing_outbound',
                ringingIn:    s === 'ringing_inbound',
                active:       s === 'active' || s === 'speaking',
                speaking:    s === 'active' || s === 'speaking',
                held:         s === 'held',
                online:       s === 'online',
                offline:      s === 'offline',
                // Action permissions (use instead of scattered phoneStatus checks):
                canDial:      s === 'online',
                canAnswer:    s === 'ringing_inbound',
                canHangup:    ['ringing', 'ringing_outbound', 'ringing_inbound', 'active', 'speaking', 'held'].includes(s),
                canHold:      (s === 'active' || s === 'speaking') && this.currentCall?.id && !this.currentCall.is_held,
                canUnhold:    s === 'held',
                canTransfer:  ['active', 'speaking', 'held'].includes(s),
                canAddCall:   ['active', 'speaking', 'held'].includes(s),
            };
        },
        sendPhoneEvent(event, payload = {}) {
            // Centralized event router — single place to update derived fields.
            switch (event) {
                case 'AUTH_SUCCESS':
                    this.phoneStatus = 'online';
                    this.phoneAuthenticated = true;
                    this.phoneCollapsed = false;
                    // Default new auth sessions to "available" so PBX routes calls.
                    this.phoneAgentStatus = 'available';
                    this.phoneUpdateAgentStatus('available').catch(() => {});
                    break;
                case 'LOGOUT':
                    this.phoneStatus = 'offline';
                    this.phoneAuthenticated = false;
                    this.phoneAgentStatus = 'offline';
                    this.currentCall = { id: null, uuid: null, caller_number: '', caller_name: '', is_held: false, is_muted: false, recording_paused: false, duration: 0 };
                    this.stopCallTimer();
                    this.stopRinging();
                    break;
                case 'DIAL_INITIATED':
                    if (this.phoneState.canDial) this.phoneStatus = 'ringing_outbound';
                    break;
                case 'INCOMING_RINGING':
                    if (!this.phoneAuthenticated || this._explicitDisconnect) {
                        return;
                    }
                    // ZIWO can route an inbound call to an agent that is currently
                    // set to Available/Meeting/Break. Honor the ring regardless of
                    // presence state — PBX already decided to deliver the call.
                    if (this.phoneStatus !== 'ringing_inbound') {
                        this.phoneStatus = 'ringing_inbound';
                        if (payload.call) {
                            this.currentCall = {
                                id: payload.call.callId || payload.call.id || ('inbound-' + Date.now()),
                                uuid: payload.call.callId || payload.call.id,
                                caller_number: payload.num || '',
                                caller_name: payload.name || '',
                                is_held: false,
                                is_muted: false,
                                recording_paused: false,
                                duration: 0,
                                direction: 'inbound'
                            };
                        }
                    }
                    break;
                case 'CALL_CONNECTED':
                    if (payload.call?.id) {
                        this.currentCall = {
                            id: payload.call.callId || payload.call.id,
                            uuid: payload.call.callId || payload.call.id,
                            caller_number: payload.call.phoneNumber || this.currentCall.caller_number,
                            caller_name: payload.call.callerIdName || payload.call.displayName || this.currentCall.caller_name,
                            is_held: false,
                            is_muted: false,
                            recording_paused: false,
                            duration: 0,
                            direction: this.currentCall.direction || (payload.outbound ? 'outbound' : 'inbound')
                        };
                    }
                    this.phoneStatus = 'active';
                    this.startCallTimer();
                    this.stopRinging();
                    break;
                case 'CALL_HELD':
                    this.phoneStatus = 'held';
                    this.currentCall.is_held = true;
                    break;
                case 'CALL_UNHELD':
                    this.phoneStatus = 'active';
                    this.currentCall.is_held = false;
                    break;
                case 'CALL_HANGUP':
                    this.phoneStatus = 'online';
                    this.stopCallTimer();
                    this.stopRinging();
                    this.currentCall = { id: null, uuid: null, caller_number: '', caller_name: '', is_held: false, is_muted: false, recording_paused: false, duration: 0 };
                    break;
                case 'CALL_DESTROYED':
                    this.stopCallTimer();
                    this.stopRinging();
                    break;
            }
        },
        phoneStatusError: '',
        showPhonePassword: false,
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

        // ─── Inline Transfer Panel ───────────────────────────────────────
        inlineTransferOpen: false,
        transferType: 'blind',   // 'blind' | 'attended'
        transferTarget: '',      // number/extension to transfer to

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

        // Attended-transfer state (warm transfer / proceed flow)
        attendedTransferPending: false,          // true while consulting the transfer destination
        attendedTransferPendingCall: null,        // SDK Call object for the consult leg
        attendedTransferOriginalCall: null,       // SDK Call object for the original held leg

        // Flag to suppress ziwo-requesting/held events during SDK-internal resume cycles
        isConferenceResuming: false,

        // Conference merged participants once connected
        conferenceParticipants: [], // [{number, name, flag, duration}]

        ziwoTeammates: [],
        ziwoQueues: [],

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
            if (this._storeSyncInterval) clearInterval(this._storeSyncInterval);
            if (this.callDurationInterval) clearInterval(this.callDurationInterval);
            this.stopRinging();
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

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // SOFTPHONE ↔ INTAKE BRIDGE
        // ─────────────────────────────────────────────────────────────────────
        // All telephony logic lives in resources/js/softphone/:
        //   • state-machine.js  — pure FSM, no side effects
        //   • ziwo-adapter.js   — thin fetch wrapper for /telephony/*
        //   • index.js          — Alpine component + Alpine.store('softphone')
        //
        // intakeComponent ONLY:
        //   1. Reads reactive state from Alpine.store('softphone') via _syncFromStore()
        //   2. Dispatches action events to the store's send() proxy
        //   3. Exposes proxy methods so existing partials/softphone/* work unchanged
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

        // ── Bootstrap ──────────────────────────────────────────────────────
        phoneInit() {
            @guest
                this.phoneAuthenticated = false;
                this.phoneStatus = 'offline';
                return;
            @endguest

            // Always validate with the server on page load — do NOT blindly trust
            // localStorage (session may have expired server-side).
            fetch('/telephony/status', { headers: { Accept: 'application/json' } })
                .then(r => r.ok ? r.json() : null)
                .then(data => {
                    if (data?.is_authenticated) {
                        // Server confirms session is valid
                        this.phoneAuthenticated = true;
                        this.phoneStatus = 'online';
                        // Set agent presence — backend returns e.g. 'online', map to our 4-state
                        const rawStatus = data.agent_status || data.presence || 'online';
                        this.phoneAgentStatus = this.mapZiwoAgentStatus(rawStatus);
                        this.ziwoUsername = data.ziwo_username || this.ziwoUsername;
                        try { localStorage.setItem('ziwo_auth', JSON.stringify({ authenticated: true, username: this.ziwoUsername })); } catch(_) {}
                        // Also signal the state machine
                        Alpine.store('softphone')?.send?.({ type: 'AUTH_OK', auth: data });
                        this.$nextTick(() => {
                            this.phoneLoadRecentLogs();
                            this.phoneSearchContacts();
                            this.phoneLoadTeammates();
                            this.phoneLoadQueues();
                        });
                    } else {
                        // Session expired or never created — clear localStorage, show login
                        try { localStorage.removeItem('ziwo_auth'); } catch(_) {}
                        this.phoneAuthenticated = false;
                        this.phoneStatus = 'offline';
                        this.phoneAgentStatus = 'offline';
                        this.phoneCollapsed = false; // make sure panel is open to show login
                    }
                })
                .catch(() => {
                    // Network error — fall back to localStorage so offline/flaky connections
                    // still show the dialer (the agent was previously authenticated).
                    try {
                        const saved = JSON.parse(localStorage.getItem('ziwo_auth') || 'null');
                        if (saved?.authenticated) {
                            this.phoneAuthenticated = true;
                            this.phoneStatus = 'online';
                            if (saved.username) this.ziwoUsername = saved.username;
                        }
                    } catch(_) {}
                });

            // Start store sync after a short delay so the hidden x-data="softphone" div
            // has time to mount and set Alpine.store('softphone').send.
            setTimeout(() => {
                const sync = () => this._syncFromStore();
                sync();
                this._storeSyncInterval = setInterval(sync, 500);

                // Also react immediately to every state-machine transition
                window.addEventListener('softphone:statechange', () => sync());
            }, 600);
        },

        // Sync Alpine.store('softphone') → intakeComponent properties.
        _syncFromStore() {
            const s = window.Alpine?.store('softphone');
            if (!s) return;

            const prevStatus = this.phoneStatus;

            // Sync phoneStatus (accept offline as well)
            this.phoneStatus = s.status || 'offline';

            // Sync auth status using localStorage presence as guard to prevent initial status poll flash
            const hasAuth = !!localStorage.getItem('ziwo_auth');
            this.phoneAuthenticated = !!(s.authenticated && hasAuth);

            // Sync active call data
            const sc = s.currentCall;
            if (sc && sc.id) {
                this.currentCall = {
                    id:               sc.id,
                    uuid:             sc.id,
                    caller_number:    sc.caller_number || '',
                    caller_name:      sc.caller_name   || '',
                    is_held:          !!sc.is_held,
                    is_muted:         !!sc.is_muted,
                    recording_paused: !!sc.recording_paused,
                    duration:         sc.duration || this.currentCall?.duration || 0,
                    direction:        sc.direction || null,
                };
            } else if (this.phoneStatus === 'online' || this.phoneStatus === 'offline') {
                this.currentCall = { id: null, uuid: null, caller_number: '', caller_name: '', is_held: false, is_muted: false, recording_paused: false, duration: 0 };
            }

            // Manage call timer transitions
            const IN_CALL_STATUSES = ['active', 'speaking', 'held', 'conference', 'transfer_consulting'];
            const nowActive = IN_CALL_STATUSES.includes(this.phoneStatus);
            const wasActive = IN_CALL_STATUSES.includes(prevStatus);
            if (nowActive && !wasActive) {
                // Just went active — start timer, stop ringing
                this.startCallTimer?.();
                this.stopRinging?.();
            } else if (!nowActive && wasActive) {
                // Just left active — stop timer
                this.stopCallTimer?.();
            }

            // Manage ringing audio
            const nowRinging = this.phoneStatus === 'ringing_inbound';
            const wasRinging = prevStatus === 'ringing_inbound';
            if (nowRinging && !wasRinging) {
                this.startRinging?.();
            } else if (!nowRinging && wasRinging) {
                this.stopRinging?.();
            }
        },

        // ── Auth actions ────────────────────────────────────────────────────
        async phoneAuthenticate() {
            if (!this.phoneAuthForm.username || !this.phoneAuthForm.password) {
                this.phoneStatusError = 'Username and password are required.';
                return;
            }
            this.phoneStatusError = '';
            this.phoneSubmitting = true;
            try {
                const res = await fetch('/telephony/authenticate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        username: this.phoneAuthForm.username,
                        password: this.phoneAuthForm.password,
                    }),
                });
                const data = await res.json();
                console.log('[ZIWO] phoneAuthenticate response:', data.status, 'is_authenticated:', data.is_authenticated);

                if (data.status === 'success' || data.is_authenticated) {
                    // ── Step 1: persist to localStorage ─────────────────────
                    try { localStorage.setItem('ziwo_auth', JSON.stringify({ authenticated: true, username: this.phoneAuthForm.username })); } catch(_) {}

                    // ── Step 2: flip state NOW (must happen before anything else) ──
                    this.ziwoUsername = this.phoneAuthForm.username;
                    this.phoneAuthenticated = true;  // hides login screen
                    this.phoneStatus = 'online';
                    this.phoneCollapsed = false;
                    this.phoneAgentStatus = 'available'; // default presence to available
                    console.log('[ZIWO] phoneAuthenticated set to', this.phoneAuthenticated);

                    // ── Step 3: nudge state machine (best-effort, isolated) ──
                    try { window.Alpine?.store('softphone')?.send?.({ type: 'AUTH_OK', auth: { username: this.phoneAuthForm.username } }); } catch(_) {}

                    // ── Step 4: set presence on ZIWO server ──────────────────
                    try { this.phoneUpdateAgentStatus('available'); } catch(_) {}

                    // ── Step 5: load data (best-effort, background) ──────────
                    try { this.phoneLoadRecentLogs(); } catch(_) {}
                    try { this.phoneSearchContacts(); } catch(_) {}
                    try { this.phoneLoadTeammates(); } catch(_) {}
                    try { this.phoneLoadQueues(); } catch(_) {}
                } else {
                    this.phoneStatusError = data.message || 'Authentication failed. Check credentials.';
                    console.warn('[ZIWO] phoneAuthenticate failed:', data);
                }
            } catch(e) {
                console.error('[ZIWO] phoneAuthenticate error:', e);
                this.phoneStatusError = 'Connection error. Please try again.';
            } finally {
                this.phoneSubmitting = false;
            }
        },

        async phoneDisconnect() {
            this._explicitDisconnect = true;
            // 1) tear down active calls via machine
            Alpine.store('softphone').send?.({ type: 'HANGUP_ALL' });
            // 2) give hangup a brief moment then force logout
            await new Promise(r => setTimeout(r, 300));
            Alpine.store('softphone').send?.({ type: 'AUTH_FAIL' });
            // 3) call backend to invalidate server session
            try {
                await fetch('/telephony/disconnect', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({}),
                });
            } catch(_) {}
            // 4) wipe local state
            try { localStorage.removeItem('ziwo_auth'); } catch(_) {}
            this.phoneAuthenticated = false;
            this.phoneStatus = 'offline';
            this.currentCall = { id: null, uuid: null, caller_number: '', caller_name: '', is_held: false, is_muted: false, recording_paused: false, duration: 0 };
            this.stopCallTimer?.();
            this.stopRinging?.();
            // 5) allow incoming calls again after re-login
            this._explicitDisconnect = false;
        },

        // ── Call actions (proxy to state machine) ───────────────────────────
        phoneDial() {
            const n = this.dialNumberInput?.trim();
            if (!n) return;
            Alpine.store('softphone').send?.({ type: 'DIAL', number: n });
        },

        phoneAnswer() {
            Alpine.store('softphone').send?.({ type: 'ANSWER' });
        },

        phoneHangup() {
            Alpine.store('softphone').send?.({ type: 'HANGUP_ALL' });
        },

        phoneHold() {
            Alpine.store('softphone').send?.({ type: 'HOLD' });
        },

        phoneResume() {
            Alpine.store('softphone').send?.({ type: 'UNHOLD' });
        },

        phoneMute() {
            Alpine.store('softphone').send?.({ type: 'MUTE' });
        },

        phoneUnmute() {
            Alpine.store('softphone').send?.({ type: 'UNMUTE' });
        },

        phoneSendDtmf(digit) {
            if (!digit) return;
            this.dtmfInput = (this.dtmfInput || '') + digit;
            // Send to adapter directly (doesn't need to go through state machine)
            const callId = this.currentCall?.id;
            try {
                window.Alpine?.store('softphone');
                // Access adapter via SDK
                if (window.ziwoSdkClient) {
                    const call = window.ziwoSdkClient.currentCall || window.ziwoSdkClient.call || window.ziwoSdkClient.activeCall;
                    if (call && typeof call.sendDtmf === 'function') call.sendDtmf(digit);
                }
            } catch(_) {}
            // Also send via backend
            fetch('/telephony/dtmf', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ call_id: callId, digit }),
            }).catch(() => {});
        },

        phoneMergeCalls() {
            // Merge = add held participants into a conference
            if (this.heldParticipants.length > 0) {
                const p = this.heldParticipants[0];
                Alpine.store('softphone').send?.({ type: 'ADD_PARTICIPANT', number: p.number, name: p.name });
            }
        },

        phoneAddConferenceParticipant(number) {
            if (!number) return;
            this.addOrCallOpen = false;
            this.addOrCallInput = '';
            Alpine.store('softphone').send?.({ type: 'ADD_PARTICIPANT', number });
        },

        // ── Transfer actions ─────────────────────────────────────────────────
        phoneExecuteTransfer() {
            const n = (this.transferTarget || '').trim();
            if (!n) return;
            const callId = this.currentCall.id;
            const type = this.transferType || 'blind';

            if (type === 'blind') {
                // Blind transfer: send via state machine which calls adapter.blindTransfer()
                Alpine.store('softphone').send?.({ type: 'BLIND_TRANSFER', number: n, callId });
            } else {
                // Attended (warm) transfer: start consult call
                Alpine.store('softphone').send?.({ type: 'START_TRANSFER', number: n });
                this.attendedTransferPending = true;
            }

            this.inlineTransferOpen = false;
            this.transferTarget = '';
        },

        phoneCancelAttendedTransfer() {
            Alpine.store('softphone').send?.({ type: 'CANCEL_TRANSFER', callId: this.currentCall.id });
            this.attendedTransferPending = false;
            this.inlineTransferOpen = false;
        },

        phoneResumeHeldParticipant(participant) {
            const id = participant?.id || participant;
            Alpine.store('softphone').send?.({ type: 'RESUME_PARTICIPANT', participantId: id });
        },

        // ── UI helpers ───────────────────────────────────────────────────────
        togglePhoneCollapse() {
            this.phoneCollapsed = !this.phoneCollapsed;
        },

        openInlineTransfer() {
            this.transferType = 'blind';
            this.transferTarget = '';
            this.inlineTransferOpen = true;
            // Load teammates for quick-select
            if (!this.teammates || this.teammates.length === 0) this.phoneLoadTeammates();
            if (!this.queues || this.queues.length === 0) this.phoneLoadQueues();
        },

        openAddOrCallPanel() {
            this.addOrCallTab = 'phonebook';
            this.addOrCallSearch = '';
            this.addOrCallOpen = true;
        },

        closeAddOrCallPanel() {
            this.addOrCallOpen = false;
        },

        openAddContactModal() {
            this.contactForm = { name: '', phone_number: '', category: 'custom' };
            this.addContactOpen = true;
        },

        checkOrRequestMicrophone() {
            if (!navigator.mediaDevices?.getUserMedia) {
                this.phoneStatusError = 'Microphone API not available in this browser.';
                return;
            }
            navigator.mediaDevices.getUserMedia({ audio: true })
                .then(stream => {
                    stream.getTracks().forEach(t => t.stop());
                    this.micAllowed = true;
                    this.phoneStatusError = '';
                })
                .catch(() => {
                    this.micAllowed = false;
                    this.phoneStatusError = 'Microphone access denied. Allow microphone in browser settings.';
                });
        },

        async phoneToggleRecording() {
            const callId = this.currentCall?.id;
            if (!callId) return;
            try {
                await fetch('/telephony/recording', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ call_id: callId, action: this.currentCall.recording_paused ? 'resume' : 'pause' }),
                });
                this.currentCall.recording_paused = !this.currentCall.recording_paused;
            } catch(_) {}
        },

        async phoneUpdateAgentStatus(status) {
            if (this.phoneUpdatingStatus) return;
            if (status === 'offline') {
                // 'offline' is not supported by /telephony/status/set validation
                this.phoneAgentStatus = 'offline';
                return;
            }
            this.phoneUpdatingStatus = true;
            try {
                await fetch('/telephony/status/set', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify({ status }),
                });
                this.phoneAgentStatus = this.mapZiwoAgentStatus(status);
            } catch(_) {} finally {
                this.phoneUpdatingStatus = false;
            }
        },

        // ── Data loaders ─────────────────────────────────────────────────────
        async phoneLoadRecentLogs() {
            try {
                const r = await fetch('/telephony/calls/live', { headers: { Accept: 'application/json' } });
                if (r.ok) {
                    const data = await r.json();
                    this.recentCallLogs = Array.isArray(data) ? data : (Array.isArray(data?.calls) ? data.calls : []);
                }
            } catch(_) {}
        },

        async phoneSearchContacts() {
            try {
                const params = new URLSearchParams();
                if (this.phoneSearchQuery)    params.set('query',    this.phoneSearchQuery);
                if (this.phoneCategoryFilter) params.set('category', this.phoneCategoryFilter);
                
                // 1. Fetch local phonebook
                const r1 = await fetch('/telephony/phonebook?' + params, { headers: { Accept: 'application/json' } });
                const d1 = await r1.json();
                const localList = Array.isArray(d1) ? d1 : (Array.isArray(d1?.contacts) ? d1.contacts : []);

                // 2. Fetch ZIWO CRM contacts if there is a query
                let crmList = [];
                if (this.phoneSearchQuery) {
                    const r2 = await fetch('/telephony/crm/search?' + params, { headers: { Accept: 'application/json' } });
                    if (r2.ok) {
                        const d2 = await r2.json();
                        crmList = Array.isArray(d2) ? d2 : (Array.isArray(d2?.contacts) ? d2.contacts : []);
                    }
                }

                // 3. Merge them together
                this.phonebookContacts = [...localList, ...crmList];
            } catch(_) {}
        },

        async phoneLoadTeammates() {
            try {
                const r = await fetch('/telephony/teammates', { headers: { Accept: 'application/json' } });
                if (r.ok) {
                    const data = await r.json();
                    // API returns { status, teammates: [...] }
                    this.ziwoTeammates = Array.isArray(data) ? data : (Array.isArray(data?.teammates) ? data.teammates : []);
                }
            } catch(_) {}
        },

        async phoneLoadQueues() {
            try {
                const r = await fetch('/telephony/queues', { headers: { Accept: 'application/json' } });
                if (r.ok) {
                    const data = await r.json();
                    // API returns { status, queues: [...] }
                    this.ziwoQueues = Array.isArray(data) ? data : (Array.isArray(data?.queues) ? data.queues : []);
                }
            } catch(_) {}
        },

        async phoneSaveContact() {
            if (!this.contactForm.name || !this.contactForm.phone_number) return;
            try {
                const r = await fetch('/telephony/phonebook', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: JSON.stringify(this.contactForm),
                });
                if (r.ok) {
                    this.addContactOpen = false;
                    this.contactForm = { name: '', phone_number: '', category: 'custom' };
                    this.phoneSearchContacts();
                }
            } catch(_) {}
        },

        async phoneDeleteContact(id) {
            if (!confirm('Delete this contact?')) return;
            try {
                await fetch('/telephony/phonebook/' + id, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                });
                this.phoneSearchContacts();
            } catch(_) {}
        },

        // ── Quick-dial bridge (form → softphone panel) ───────────────────────
        phoneTriggerQuickDial(number, name = '') {
            this.caller.number = number;
            if (name) this.caller.name = name;
            // Dispatch native event — softphone panel listens in its init()
            window.dispatchEvent(new CustomEvent('softphone:dial', { detail: { number, name } }));
            this.phoneCollapsed = false;
        },

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // AUDIO & TIMER UTILITIES
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        startRinging() {
            const audio = document.getElementById('ring-audio');
            if (audio) { audio.currentTime = 0; audio.play().catch(() => {}); }
        },

        stopRinging() {
            const audio = document.getElementById('ring-audio');
            if (audio) { audio.pause(); audio.currentTime = 0; }
        },

        startCallTimer() {
            this.stopCallTimer();
            this.currentCall.duration = 0;
            this.callDurationInterval = setInterval(() => {
                this.currentCall.duration++;
            }, 1000);
        },

        stopCallTimer() {
            if (this.callDurationInterval) {
                clearInterval(this.callDurationInterval);
                this.callDurationInterval = null;
            }
        },

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // GEO / FLAG UTILITY (used by screen-outgoing, screen-ringing)
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        getCountryFlagAndLocalTime(number) {
            const n = (number || '').replace(/\s+/g, '');
            const map = [
                { prefix: '+92',  code: 'PK', flag: '🇵🇰', tz: 'Asia/Karachi' },
                { prefix: '0092', code: 'PK', flag: '🇵🇰', tz: 'Asia/Karachi' },
                { prefix: '03',   code: 'PK', flag: '🇵🇰', tz: 'Asia/Karachi' },
                { prefix: '3',    code: 'PK', flag: '🇵🇰', tz: 'Asia/Karachi' },
                { prefix: '+971', code: 'AE', flag: '🇦🇪', tz: 'Asia/Dubai' },
                { prefix: '+966', code: 'SA', flag: '🇸🇦', tz: 'Asia/Riyadh' },
                { prefix: '+44',  code: 'GB', flag: '🇬🇧', tz: 'Europe/London' },
                { prefix: '+1',   code: 'US', flag: '🇺🇸', tz: 'America/New_York' },
            ];
            const match = map.find(m => n.startsWith(m.prefix)) || { code: '??', flag: '🌐', tz: null };
            let time = '';
            try {
                if (match.tz) {
                    time = new Intl.DateTimeFormat('en', {
                        timeZone: match.tz, hour: '2-digit', minute: '2-digit', hour12: true
                    }).format(new Date());
                }
            } catch(_) {}
            return { code: match.code, flag: match.flag, time };
        },

        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        // KEYBOARD HANDLER FOR DIALER
        // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
        handleDialerKey(event) {
            if (!event || !event.key) return;
            const validKeys = ['0','1','2','3','4','5','6','7','8','9','*','#','+'];
            if (validKeys.includes(event.key)) return;
            if (event.key === 'Backspace' || event.key === 'Delete') return;
            if (!event.ctrlKey && !event.metaKey && event.key.length === 1) {
                event.preventDefault();
            }
        }
    };
}
</script>
@endpush
