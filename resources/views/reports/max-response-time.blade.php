@extends('layouts.app')

@section('title', 'Max Response Analysis - NHMP 130')

@section('page-title', 'Max Response Time Analysis')

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

            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden p-8">
                <div class="mb-6 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-500 text-xl shrink-0">
                        <i class="fa-solid fa-gauge-high"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-extrabold text-navy-900 tracking-tight">Response Benchmarks by Beat</h3>
                        <p class="text-xs font-medium text-slate-400 mt-0.5">Maximum recorded resolution time per beat — flag any beat exceeding the 1800s threshold</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] bg-slate-50/50">
                                <th class="p-5">#</th>
                                <th class="p-5">Beat / Location</th>
                                <th class="p-5">Max Response (sec)</th>
                                <th class="p-5">Max Response (min)</th>
                                <th class="p-5">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($data as $i => $row)
                            <tr class="hover:bg-rose-50/30 transition-all">
                                <td class="p-5 text-slate-400 font-black text-xs">{{ $i + 1 }}</td>
                                <td class="p-5 font-extrabold text-navy-900 uppercase tracking-tight">{{ $row['beat'] ?? '—' }}</td>
                                <td class="p-5">
                                    <span class="px-3 py-1 rounded-lg text-xs font-black {{ ($row['max_response'] ?? 0) > 1800 ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-slate-50 text-slate-600 border border-slate-100' }}">
                                        {{ number_format($row['max_response'] ?? 0) }}s
                                    </span>
                                </td>
                                <td class="p-5 font-bold text-slate-600 text-sm">
                                    {{ round(($row['max_response'] ?? 0) / 60, 1) }} <span class="text-[10px] text-slate-400 uppercase tracking-widest">min</span>
                                </td>
                                <td class="p-5">
                                    @if(($row['max_response'] ?? 0) > 1800)
                                        <span class="flex items-center gap-1.5 text-rose-600 font-black text-[10px] uppercase tracking-widest">
                                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span> Critical
                                        </span>
                                    @elseif(($row['max_response'] ?? 0) > 600)
                                        <span class="flex items-center gap-1.5 text-amber-600 font-black text-[10px] uppercase tracking-widest">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Warning
                                        </span>
                                    @else
                                        <span class="flex items-center gap-1.5 text-emerald-600 font-black text-[10px] uppercase tracking-widest">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Optimal
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="p-16 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <div class="w-16 h-16 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center text-2xl mb-4">
                                            <i class="fa-solid fa-gauge"></i>
                                        </div>
                                        <p class="font-bold text-slate-500">No response data for this period</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
