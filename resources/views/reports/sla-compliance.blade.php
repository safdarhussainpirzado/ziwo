@extends('layouts.app')

@section('title', 'SLA Compliance - NHMP 130')

@section('page-title', 'SLA Compliance Monitoring')

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
        <!-- Filters -->
        @include('reports.partials.filters')

        <div id="report-content">
            <div class="flex justify-end gap-3 mb-6">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" id="export-excel"
                   class="h-12 px-6 rounded-xl bg-gradient-to-b from-emerald-400 to-emerald-500 border-b-4 border-emerald-700 text-white flex items-center justify-center font-black text-xs uppercase tracking-widest hover:brightness-110 hover:border-b-[3px] hover:translate-y-[1px] active:border-b-0 active:translate-y-1 transition-all shadow-lg shadow-emerald-500/30">
                    <i class="fa-solid fa-file-excel mr-2"></i> Excel
                </a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" id="export-pdf"
                   class="h-12 px-6 rounded-xl bg-gradient-to-b from-rose-400 to-rose-500 border-b-4 border-rose-700 text-white flex items-center justify-center font-black text-xs uppercase tracking-widest hover:brightness-110 hover:border-b-[3px] hover:translate-y-[1px] active:border-b-0 active:translate-y-1 transition-all shadow-lg shadow-rose-500/30">
                    <i class="fa-solid fa-file-pdf mr-2"></i> PDF
                </a>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @foreach($data as $priority => $row)
                @php
                    $color = $row['percentage'] >= 90 ? 'emerald' : ($row['percentage'] >= 70 ? 'amber' : 'rose');
                @endphp
                <div class="bg-white rounded-[2rem] border border-slate-100 p-8 shadow-xl relative overflow-hidden group hover:border-{{ $color }}-200 transition-all">
                    <div class="absolute -right-4 -bottom-4 opacity-5 text-8xl">
                        <i class="fa-solid fa-stopwatch"></i>
                    </div>
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <span class="px-3 py-1 bg-slate-900 text-white rounded-lg text-[10px] font-black tracking-widest uppercase">{{ $row['priority'] }}</span>
                            <div class="text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-widest">SLA Window</div>
                        </div>
                        <div class="text-4xl font-extrabold text-{{ $color }}-600">{{ $row['percentage'] }}%</div>
                    </div>
                    <div class="h-3 bg-slate-100 rounded-full overflow-hidden mb-4 shadow-inner">
                        <div class="h-full rounded-full bg-{{ $color }}-500 transition-all duration-1000" style="width: {{ $row['percentage'] }}%"></div>
                    </div>
                    <div class="flex justify-between text-[10px] font-black uppercase tracking-widest text-slate-400">
                        <span>{{ $row['within'] }} Within SLA</span>
                        <span>{{ $row['total'] }} Total</span>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Detail Table -->
            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden p-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-slate-50/50">
                                <th class="p-5">Priority</th>
                                <th class="p-5">Total Calls</th>
                                <th class="p-5">Within SLA</th>
                                <th class="p-5">Breached</th>
                                <th class="p-5">Compliance %</th>
                                <th class="p-5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($data as $priority => $row)
                            @php $color = $row['percentage'] >= 90 ? 'emerald' : ($row['percentage'] >= 70 ? 'amber' : 'rose'); @endphp
                            <tr class="hover:bg-slate-50/50 transition-all">
                                <td class="p-5">
                                    <span class="px-3 py-1 bg-slate-900 text-white rounded-lg text-[10px] font-black tracking-widest uppercase">{{ $row['priority'] }}</span>
                                </td>
                                <td class="p-5 font-bold text-slate-700">{{ number_format($row['total']) }}</td>
                                <td class="p-5">
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-black border border-emerald-100">{{ number_format($row['within']) }}</span>
                                </td>
                                <td class="p-5">
                                    <span class="px-3 py-1 bg-rose-50 text-rose-600 rounded-lg text-xs font-black border border-rose-100">{{ number_format($row['total'] - $row['within']) }}</span>
                                </td>
                                <td class="p-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-{{ $color }}-500 rounded-full" style="width: {{ $row['percentage'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-black text-{{ $color }}-600">{{ $row['percentage'] }}%</span>
                                    </div>
                                </td>
                                <td class="p-5">
                                    <span class="flex items-center gap-1.5 text-{{ $color }}-600 font-black text-[10px] uppercase tracking-widest">
                                        <span class="w-2 h-2 rounded-full bg-{{ $color }}-500 {{ $color === 'emerald' ? '' : 'animate-pulse' }}"></span>
                                        {{ $row['percentage'] >= 90 ? 'Compliant' : ($row['percentage'] >= 70 ? 'At Risk' : 'Breached') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
