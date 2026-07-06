@extends('layouts.app')

@section('title', 'Help Management - NHMP 130')

@section('page-title', 'Update Help')

@section('content')


    <div class="py-6 px-4 sm:px-6 lg:px-8" x-data="intakeComponent" x-init="init()">
        <!-- Multi-Column Bento Layout -->
        <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-3 px-3 items-stretch">
            
            <!-- ROW 1: SPATIAL (4) + INTEL (8) -->
            <!-- COLUMN 1: NODE REGISTRY (col-span-12 lg:col-span-4) -->
            <div class="col-span-12 lg:col-span-4 flex flex-col gap-3 min-w-0">
                <!-- User/Node Metadata Tile -->
                <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 flex-1 flex flex-col relative overflow-hidden group">
                    <div class="absolute -right-10 -bottom-10 w-32 h-32 bg-blue-500/5 rounded-full blur-2xl group-hover:scale-150 transition-transform"></div>
                    <div class="flex items-center gap-4 mb-8">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 border border-blue-100 shadow-sm">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-navy-900 uppercase">Node Registry</h4>
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic">Identity Persistent</p>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="group">
                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2">Response Link (Phone)</label>
                            <div class="relative">
                                <input type="text" name="caller_number" x-model="caller.number" required
                                    class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-400 outline-none font-bold text-navy-900 shadow-sm">
                                <i class="fa-solid fa-phone-flip absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 text-xs text-blue-400/60"></i>
                            </div>
                        </div>
                        <div class="group">
                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2">Node Principal (Name)</label>
                            <input type="text" name="caller_name" x-model="caller.name"
                                class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-400 outline-none font-bold text-navy-900 shadow-sm">
                        </div>
                         <div class="group">
                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2">Vehicle Reg (Plate)</label>
                            <input type="text" name="vehicle_no" value="{{ $call->vehicle_no }}" placeholder="ABC-123"
                                class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-400 outline-none font-bold text-navy-900 shadow-sm uppercase placeholder:text-slate-300">
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMN 2: OPERATIONAL INTEL (col-span-12 lg:col-span-8) -->
            <div class="col-span-12 lg:col-span-8 flex flex-col min-w-0">
                <form action="{{ route('calls.update', $call) }}" method="POST" id="intake-form" class="flex-1 flex flex-col">
                    @csrf
                    @method('PUT')
                    <!-- Hidden Spatial IDs -->
                    <input type="hidden" name="geospatial_marker_id" x-model="spatial.geospatial_marker_id">
                    <input type="hidden" name="carriageway_id" x-model="spatial.carriageway_id">
                    <input type="hidden" name="zone_id" x-model="spatial.zone_id">
                    <input type="hidden" name="sector_id" x-model="spatial.sector_id">
                    <input type="hidden" name="beat_id" x-model="spatial.beat_id">
                    <input type="hidden" name="km_marker_text" x-model="spatial.km_marker_text">
                    <input type="hidden" name="tiger_id" x-model="selectedTigerId">

                    <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-6 flex-1 flex flex-col relative overflow-hidden group">
                        <!-- Luxury Decor -->
                        <div class="absolute -right-20 -top-20 w-64 h-64 bg-amber-500/5 rounded-full blur-3xl"></div>
                        
                        <div class="flex items-center justify-between mb-8 shrink-0">
                            <div>
                                <h3 class="text-xl font-black text-navy-900 tracking-tight mb-1">Operational Intelligence</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Recalibrate the assistance request profile</p>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 border border-amber-200 shadow-sm">
                                <i class="fa-solid fa-code-merge"></i>
                            </div>
                        </div>

                        <div class="flex-1 space-y-8">
                            <!-- Taxonomy & Priority Row -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="group">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 group-focus-within:text-amber-500 transition-colors">Classification</label>
                                    <select name="call_type_id" required @change="onCallTypeChange($event.target.value)" 
                                        class="w-full px-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-amber-400 focus:bg-white outline-none font-bold text-navy-900 transition-all shadow-sm appearance-none">
                                        <option value="">Select Category...</option>
                                        @foreach($callTypes as $type)
                                            <option value="{{ $type->id }}" @selected($call->call_type_id == $type->id)>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="group">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 group-focus-within:text-amber-500 transition-colors">Profile</label>
                                    <select name="call_sub_type_id" x-model="selectedSubType"
                                        class="w-full px-4 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-amber-400 focus:bg-white outline-none font-bold text-navy-900 transition-all shadow-sm appearance-none">
                                        <option value="">Select Profile...</option>
                                        <template x-for="sub in filteredSubTypes" :key="sub.id">
                                            <option :value="sub.id" x-text="sub.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="group">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Priority</label>
                                    <div class="grid grid-cols-3 gap-2">
                                        <template x-for="p in ['low', 'medium', 'high']" :key="p">
                                            <button type="button" @click="priority = p"
                                                :class="priority === p ? 'bg-navy-900 text-white border-navy-900 shadow-lg' : 'bg-slate-50 text-slate-400 border-slate-100 hover:bg-slate-100'"
                                                class="py-4 rounded-xl border-2 font-black text-[9px] uppercase tracking-widest transition-all">
                                                <input type="radio" name="priority" :value="p" x-model="priority" class="hidden">
                                                <span x-text="p"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            <!-- Asset Type Interactive Bento Buttons -->
                            <div class="group">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-2">Asset Type (Involved)</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                                    <input type="hidden" name="vehicle_type_id" x-model="selectedVehicleType">
                                    <button type="button" @click="selectedVehicleType = ''"
                                        :class="selectedVehicleType == '' ? 'border-amber-400 bg-amber-50 text-navy-900 shadow-lg' : 'border-slate-100 bg-slate-50 text-slate-400 hover:bg-slate-100'"
                                        class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all group/btn">
                                        <i class="fa-solid fa-ban text-lg mb-2 opacity-50"></i>
                                        <span class="text-[8px] font-black uppercase tracking-widest">None</span>
                                    </button>
                                    @foreach($vehicleTypes as $vType)
                                        @php
                                            $icon = 'fa-car-side';
                                            if (stripos($vType->name, 'car') !== false) $icon = 'fa-car';
                                            elseif (stripos($vType->name, 'bike') !== false || stripos($vType->name, 'motor') !== false) $icon = 'fa-motorcycle';
                                            elseif (stripos($vType->name, 'truck') !== false) $icon = 'fa-truck';
                                            elseif (stripos($vType->name, 'bus') !== false) $icon = 'fa-bus';
                                            elseif (stripos($vType->name, 'ambulance') !== false) $icon = 'fa-truck-medical';
                                            elseif (stripos($vType->name, 'jeep') !== false) $icon = 'fa-suv';
                                            elseif (stripos($vType->name, 'trailer') !== false) $icon = 'fa-trailer';
                                        @endphp
                                        <button type="button" @click="selectedVehicleType = '{{ $vType->id }}'"
                                            :class="selectedVehicleType == '{{ $vType->id }}' ? 'border-amber-400 bg-amber-50 text-navy-900 shadow-lg' : 'border-slate-100 bg-slate-50 text-slate-400 hover:bg-slate-100'"
                                            class="flex flex-col items-center justify-center p-4 rounded-2xl border-2 transition-all group/btn">
                                            <i class="fa-solid {{ $icon }} text-lg mb-2 transition-transform group-hover/btn:scale-110"></i>
                                            <span class="text-[8px] font-black uppercase tracking-widest text-center leading-tight">{{ $vType->name }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Intel Details -->
                            <div class="group">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Intelligence Summary (Details)</label>
                                <textarea name="details" rows="3" placeholder="Enter actionable intelligence for dispatch units..."
                                    class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-amber-400 focus:bg-white outline-none font-bold text-navy-900 transition-all shadow-sm placeholder:text-slate-300 resize-none leading-relaxed">{{ $call->details }}</textarea>
                            </div>
                        </div>

                        <!-- Update Protocol -->
                        <div class="mt-8 shrink-0">
                            <button type="submit" :disabled="submitting" 
                                class="w-full py-5 bg-gradient-to-r from-navy-900 to-slate-800 text-white rounded-2xl font-black text-xs uppercase tracking-[0.3em] shadow-xl shadow-navy-900/40 hover:-translate-y-1 hover:shadow-amber-500/20 active:scale-95 transition-all flex items-center justify-center gap-4 group">
                                <span x-show="!submitting" class="flex items-center gap-4">
                                    <i class="fa-solid fa-cloud-arrow-up text-lg text-amber-400 group-hover:scale-125 transition-transform"></i>
                                    Commit Modifications
                                </span>
                                <span x-show="submitting" class="flex items-center gap-4">
                                    <i class="fa-solid fa-spinner animate-spin"></i>
                                    Synchronizing...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- ROW 2: NODE (4) + DISPATCH (8) -->
            <!-- COLUMN 1: NODE REGISTRY (col-span-12 lg:col-span-4) -->
            <div class="col-span-12 lg:col-span-4 flex flex-col gap-3 min-w-0">

                <!-- Geospatial Origin Tile -->
                <div class="bg-navy-900 rounded-2xl shadow-xl p-6 text-white relative overflow-hidden border border-white/5 flex-1 flex flex-col justify-center min-h-[400px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/5 to-transparent pointer-none"></div>
                    <div class="flex items-center gap-4 mb-8 relative z-10">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500 border border-amber-500/20 shadow-lg">
                            <i class="fa-solid fa-map-location-dot animate-bounce"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-white uppercase tracking-tight">Spatial Context</h4>
                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest italic">AUDIT LOCK ACTIVE</p>
                        </div>
                    </div>

                    <div class="space-y-6 relative z-10 flex-1">
                        <!-- Searchable Carriageway Utility -->
                        <div class="group" x-data="searchableSelect({ 
                            items: carriageways,
                            placeholder: 'Search Highway Network...',
                            initialSelectedId: {{ $call->carriageway_id ?? 'null' }},
                            onSelect: (item) => { spatial.carriageway_id = item.id; lookupKM(); }
                        })">
                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2">Highway Network</label>
                            <div class="relative">
                                <div @click="open = !open" 
                                    class="w-full px-6 py-4 bg-white/5 border-2 border-white/10 rounded-2xl outline-none font-bold text-white shadow-sm flex items-center justify-between cursor-pointer hover:border-amber-500/50 transition-all">
                                    <span x-text="selectedItem ? (selectedItem.code && selectedItem.code !== 'null' ? selectedItem.code : 'M-?') + ' — ' + selectedItem.name : 'Select Network...'" class="truncate mr-4"></span>
                                    <i class="fa-solid fa-chevron-down text-[10px] text-amber-500/60" :class="open ? 'rotate-180' : ''"></i>
                                </div>
                                <div x-show="open" @click.away="open = false" x-transition 
                                    class="absolute left-0 right-0 mt-3 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-[100] max-h-64 flex flex-col">
                                    <div class="p-4 border-b border-slate-50 bg-slate-50/50 sticky top-0 text-navy-900 overflow-hidden">
                                        <input type="text" x-model="search" placeholder="Type highway or code..." 
                                            class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl outline-none font-bold text-navy-900 text-xs focus:border-amber-400 transition-all">
                                    </div>
                                    <div class="overflow-y-auto no-scrollbar flex-1">
                                        <template x-for="item in filteredItems" :key="item.id">
                                            <div @click="select(item)" 
                                                class="px-6 py-4 hover:bg-amber-50 cursor-pointer flex flex-col group border-b border-slate-50 last:border-0 transition-colors">
                                                <span class="text-navy-900 font-extrabold text-xs group-hover:text-amber-600 transition-colors" x-text="item.code && item.code !== 'null' ? item.code : 'M-?'"></span>
                                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight" x-text="item.name"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="group">
                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-2">KM Marker (Intel Origin)</label>
                            <div class="relative">
                                <input type="number" step="1" x-model="spatial.km" @input.debounce.500ms="lookupKM()"
                                    class="w-full pl-12 pr-6 py-4 bg-white/5 border-2 border-white/10 rounded-2xl outline-none font-black text-2xl text-amber-500 shadow-sm focus:border-amber-500/50 transition-all italic scale-y-110">
                                <i class="fa-solid fa-diamond absolute left-5 top-1/2 -translate-y-1/2 text-white/20 text-xs"></i>
                            </div>
                        </div>

                        <!-- Resolved Hierarchy Display -->
                        <div class="mt-4 space-y-3 pt-4 border-t border-white/10">
                            <div class="flex justify-between items-center group">
                                <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest group-hover:text-amber-500 transition-colors">Zone</span>
                                <span class="text-[10px] font-black text-white uppercase" x-text="spatial.zone_name || '—'"></span>
                            </div>
                            <div class="flex justify-between items-center group">
                                <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest group-hover:text-amber-500 transition-colors">Sector</span>
                                <span class="text-[10px] font-black text-white uppercase" x-text="spatial.sector_name || '—'"></span>
                            </div>
                            <div class="flex justify-between items-center group">
                                <span class="text-[8px] font-black text-slate-500 uppercase tracking-widest group-hover:text-amber-500 transition-colors">Beat</span>
                                <span class="text-[10px] font-black text-amber-500 uppercase italic" x-text="spatial.beat_name || '—'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COLUMN 2: DISPATCH COMMAND (col-span-12 lg:col-span-8) -->
            <div class="col-span-12 lg:col-span-8 flex flex-col min-w-0">
                <div class="bg-white rounded-2xl shadow-xl border border-slate-100 flex-1 flex flex-col relative overflow-hidden">
                    <div class="bg-slate-50/70 border-b border-slate-100 p-6 shrink-0 relative overflow-hidden group">
                        <div class="absolute -left-10 -top-10 w-24 h-24 bg-amber-500/10 rounded-full blur-xl group-hover:scale-150 transition-transform pointer-events-none"></div>
                        
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse shadow-sm shadow-amber-500/50"></span>
                                    Asset Ops
                                </div>
                                <h4 class="text-2xl font-black text-navy-900 tracking-tighter">Dispatch</h4>
                            </div>
                            <div class="w-10 h-10 rounded-xl bg-navy-900/5 flex items-center justify-center text-navy-900/40">
                                <i class="fa-solid fa-truck-fast text-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 space-y-3 no-scrollbar" style="max-height: 400px;">
                        <template x-if="tigers.length === 0">
                            <div class="flex flex-col items-center justify-center py-20 opacity-30 select-none">
                                <i class="fa-solid fa-truck-monster text-5xl mb-4 hover:rotate-12 transition-transform"></i>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-center">Awaiting Spatial Sync</p>
                            </div>
                        </template>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <template x-for="tiger in tigers" :key="tiger.id">
                                <div @click="assignTiger(tiger.id)" 
                                    class="p-5 rounded-2xl border-2 transition-all cursor-pointer group hover:-translate-y-1 hover:shadow-xl"
                                    :class="selectedTigerId == tiger.id ? 'bg-navy-900 border-navy-900 text-white shadow-xl shadow-navy-900/30' : 'bg-slate-50 border-slate-100 hover:border-amber-300 hover:bg-white'">
                                    
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center border transition-all"
                                            :class="selectedTigerId == tiger.id ? 'bg-white/10 border-white/20 text-amber-400' : 'bg-white border-slate-200 text-navy-900'">
                                            <i class="fa-solid fa-truck-fast text-sm"></i>
                                        </div>
                                        <span class="text-[8px] font-black uppercase tracking-widest" :class="selectedTigerId == tiger.id ? 'text-amber-400' : 'text-slate-400'" x-text="tiger.status"></span>
                                    </div>

                                    <div class="text-lg font-black tracking-tight mb-1" x-text="tiger.tiger_code"></div>
                                    <div class="text-[10px] font-bold opacity-60 uppercase mb-4" x-text="tiger.vehicle_reg_no"></div>

                                    <div class="space-y-2 pt-4 border-t" :class="selectedTigerId == tiger.id ? 'border-white/10' : 'border-slate-200'">
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-user-tie text-[10px]" :class="selectedTigerId == tiger.id ? 'text-amber-400' : 'text-blue-400'"></i>
                                            <span class="text-[10px] font-black uppercase" x-text="tiger.officer_name"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <i class="fa-solid fa-phone text-[10px]" :class="selectedTigerId == tiger.id ? 'text-amber-400' : 'text-blue-400'"></i>
                                            <span class="text-[10px] font-black" x-text="tiger.officer_phone"></span>
                                        </div>
                                    </div>
                                    
                                    <template x-if="selectedTigerId == tiger.id">
                                        <div class="mt-4 flex justify-center">
                                            <div class="px-3 py-1 rounded-full bg-white/10 text-[8px] font-black uppercase tracking-widest border border-white/20 animate-pulse text-amber-400">
                                                ✦ Asset Linked
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="p-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
                         <div class="p-3 rounded-xl bg-white border-2 border-slate-100 shadow-sm flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">Update Protocol</div>
                                <div class="text-[10px] font-black text-navy-900 uppercase italic">LAST SYNC: {{ $call->updated_at->format('H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bento Alerts Layer -->
    <div id="bento-notifications" class="fixed bottom-10 left-10 z-[2000] flex flex-col gap-4 max-w-sm"></div>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
             // Global Searchable Select Component
             Alpine.data('searchableSelect', (config) => ({
                open: false,
                search: '',
                items: config.items || [],
                selectedItem: null,
                initialSelectedId: config.initialSelectedId,
                placeholder: config.placeholder || 'Search...',
                onSelect: config.onSelect || (() => {}),

                init() {
                    if (this.initialSelectedId) {
                        this.selectedItem = this.items.find(i => i.id == this.initialSelectedId);
                    }
                },

                get filteredItems() {
                    const s = this.search.toLowerCase();
                    return this.items.filter(i => 
                        (i.code || '').toLowerCase().includes(s) || 
                        (i.name || '').toLowerCase().includes(s)
                    );
                },

                select(item) {
                    this.selectedItem = item;
                    this.search = '';
                    this.open = false;
                    this.onSelect(item);
                }
            }));

            Alpine.data('intakeComponent', () => ({
                priority: @json($call->priority),
                selectedSubType: @json($call->call_sub_type_id),
                selectedVehicleType: @json($call->vehicle_type_id),
                allSubTypes: @json($callTypes->flatMap->subTypes),
                filteredSubTypes: [],
                submitting: false,
                caller: { number: @json($call->caller_number), name: @json($call->caller_name) },
                spatial: {
                    carriageway_id: @json($call->carriageway_id),
                    km: @json($call->km_marker_text),
                    km_marker_text: @json($call->km_marker_text),
                    geospatial_marker_id: @json($call->geospatial_marker_id),
                    zone_id: @json($call->zone_id),
                    zone_name: @json($call->zone->name ?? ""),
                    sector_id: @json($call->sector_id),
                    sector_name: @json($call->sector->name ?? ""),
                    beat_id: @json($call->beat_id),
                    beat_name: @json($call->beat->name ?? "")
                },
                carriageways: @json($carriageways->map(fn($c) => ['id' => $c->id, 'code' => $c->road, 'name' => $c->road_name])),
                tigers: [],
                selectedTigerId: @json($call->tiger_id),

                init() {
                    console.log('Edit Command Operational.');
                    this.onCallTypeChange({{ $call->call_type_id }}, true);
                    if (this.spatial.beat_id) {
                        this.fetchResources();
                    }
                },

                onCallTypeChange(typeId, initial = false) {
                    this.filteredSubTypes = this.allSubTypes.filter(s => s.call_type_id == typeId);
                    if (!initial) this.selectedSubType = '';
                },

                async lookupKM() {
                    if (!this.spatial.carriageway_id || !this.spatial.km) return;
                    
                    try {
                        const url = `/api/km-lookup?carriageway_id=${this.spatial.carriageway_id}&km=${this.spatial.km}`;
                        const res = await fetch(url);
                        const data = await res.json();
                        
                        if (data.found || data.message) {
                            this.spatial.geospatial_marker_id = data.geospatial_marker_id;
                            this.spatial.km_marker_text = this.spatial.km;
                            this.spatial.zone_id = data.zone_id;
                            this.spatial.zone_name = data.zone_name;
                            this.spatial.sector_id = data.sector_id;
                            this.spatial.sector_name = data.sector_name;
                            this.spatial.beat_id = data.beat_id;
                            this.spatial.beat_name = data.beat_name;

                            if (this.spatial.beat_id) {
                                this.fetchResources();
                            }

                            sendBentoNotification({
                                title: data.exact ? 'Spatial Sync Exact' : 'Spatial Fallback Active',
                                message: `Coordinated to ${data.beat_name} | ${data.sector_name}`,
                                type: data.exact ? 'success' : 'warning'
                            });
                        }
                    } catch (e) { console.error('Spatial sync failed.'); }
                },

                async fetchResources() {
                    try {
                        const res = await fetch(`/api/beat-resources?beat_id=${this.spatial.beat_id}`);
                        const data = await res.json();
                        this.tigers = data.tigers || [];
                    } catch (e) { console.error('Resource fetch failed.'); }
                },

                assignTiger(id) {
                    this.selectedTigerId = id;
                    sendBentoNotification({
                        title: 'Asset Allocation Updated',
                        message: 'Tiger unit successfully linked to acquisition record.',
                        type: 'success'
                    });
                }
            }));
        });

        // Global Bento Notification System
        function sendBentoNotification({ title, message, type = 'info' }) {
            const container = document.getElementById('bento-notifications');
            if(!container) return;

            const id = Date.now();
            const colors = {
                success: 'border-emerald-500 bg-emerald-50 text-emerald-900',
                error: 'border-rose-500 bg-rose-50 text-rose-900',
                warning: 'border-amber-500 bg-amber-50 text-amber-900',
                info: 'border-blue-500 bg-blue-50 text-blue-900'
            };

            const icons = {
                success: 'fa-circle-check',
                error: 'fa-circle-xmark',
                warning: 'fa-triangle-exclamation',
                info: 'fa-circle-info'
            };

            const el = document.createElement('div');
            el.id = `ntf-${id}`;
            el.className = `p-6 rounded-[2rem] border-2 shadow-2xl transition-all duration-500 transform translate-x-[-100%] opacity-0 flex items-start gap-4 ${colors[type]}`;
            el.innerHTML = `
                <div class="w-10 h-10 rounded-xl bg-white/50 flex items-center justify-center shrink-0">
                    <i class="fa-solid ${icons[type]} text-lg"></i>
                </div>
                <div class="flex-1">
                    <h5 class="text-[10px] font-black uppercase tracking-widest mb-1 italic">${title}</h5>
                    <p class="text-xs font-bold leading-relaxed">${message}</p>
                </div>
            `;

            container.appendChild(el);
            setTimeout(() => {
                el.classList.remove('translate-x-[-100%]', 'opacity-0');
            }, 100);

            setTimeout(() => {
                el.classList.add('translate-x-[-100%]', 'opacity-0');
                setTimeout(() => el.remove(), 500);
            }, 5000);
        }
    </script>
    @endpush
@endsection
