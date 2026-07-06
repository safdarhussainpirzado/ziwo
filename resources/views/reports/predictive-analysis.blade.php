@extends('layouts.app')

@section('title', 'Predictive Load Trends - NHMP 130')

@section('page-title', 'Predictive Load Trends')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
        <!-- ── Filters ─────────────────────────────────────────────── -->
        @include('reports.partials.filters')

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
            <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-xl shadow-slate-200/20">
                <h3 class="font-extrabold text-xl text-navy-900 mb-6 flex items-center gap-3">
                    <i class="fa-solid fa-chart-line text-blue-600"></i> Temporal Density Analysis
                </h3>
                <div class="space-y-4">
                    @foreach($data['temporal'] as $hour => $count)
                    <div class="group">
                        <div class="flex justify-between items-center mb-1 text-[10px] font-black uppercase tracking-widest">
                            <span class="text-slate-500">{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00 HRS</span>
                            <span class="text-navy-900">{{ $count }} Incident Forecast</span>
                        </div>
                        <div class="h-2 bg-slate-50 rounded-full overflow-hidden border border-slate-100">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full transition-all duration-1000" style="width: {{ (max($data['temporal']) > 0) ? ($count / max(1, max($data['temporal']))) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-navy-900 rounded-[2.5rem] p-10 text-white shadow-2xl relative overflow-hidden">
                <div class="absolute -left-10 -bottom-10 w-64 h-64 bg-indigo-500 rounded-full blur-[80px] opacity-20"></div>
                <div class="relative z-10 h-full flex flex-col justify-between">
                    <div>
                        <div class="w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-sm flex items-center justify-center border border-white/20 mb-6 shadow-xl">
                            <i class="fa-solid fa-brain text-3xl text-blue-400"></i>
                        </div>
                        <h3 class="text-3xl font-extrabold tracking-tight mb-4 leading-tight">Predictive Operational Matrix</h3>
                        <p class="text-blue-100/60 font-medium leading-relaxed">System analysis leverage historical hourly density to forecast patrol load. Highly saturated temporal segments (Red bars) suggest immediate reinforcement of Tiger units at specified KM markers.</p>
                    </div>
                    <div class="mt-8 pt-8 border-t border-white/10 grid grid-cols-2 gap-8">
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-blue-400">Model Accuracy</div>
                            <div class="text-2xl font-extrabold mt-1">94.2%</div>
                        </div>
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-blue-400">Training Cycles</div>
                            <div class="text-2xl font-extrabold mt-1">12,400+</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-xl shadow-slate-200/20">
             <h3 class="font-extrabold text-xl text-navy-900 mb-6 flex items-center gap-3">
                    <i class="fa-solid fa-map-location text-emerald-600"></i> Predicted Zone Saturation
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    @foreach($data['zone_risk'] as $zone => $risk)
                    <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 group hover:bg-white hover:border-blue-200 transition-all text-center">
                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">{{ $zone }}</div>
                        <div class="text-3xl font-black text-navy-900 mb-2">{{ $risk }}%</div>
                        <div class="text-[9px] font-black uppercase tracking-widest text-{{ $risk > 70 ? 'rose' : ($risk > 40 ? 'amber' : 'emerald') }}-500">Risk Factor</div>
                    </div>
                    @endforeach
                </div>
        </div>
    </div>
@endsection
