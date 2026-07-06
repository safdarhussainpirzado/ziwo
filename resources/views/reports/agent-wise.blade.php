@extends('layouts.app')

@section('title', 'Agent Performance - NHMP 130')

@section('page-title', 'Agent Performance')

@section('content')
    <div x-data="reportFilters" class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- ── Filters ─────────────────────────────────────────────── -->
        @include('reports.partials.filters')

        <!-- ── Report Table ────────────────────────────────────────── -->
        <div id="report-content" class="space-y-6">
            <span id="report-has-data" data-value="{{ count($data) > 0 ? 'true' : 'false' }}" class="hidden"></span>
            <div class="flex justify-end gap-3" x-show="!initialEmpty && reportData.length > 0">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" id="export-excel" data-no-pjax
                   @click="exporting = 'excel'; setTimeout(() => exporting = null, 3000)"
                   :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': exporting }"
                   class="h-11 px-5 rounded-2xl bg-gradient-to-b from-emerald-500 to-emerald-600 border-b-4 border-emerald-800 text-white flex items-center justify-center font-black text-xs uppercase tracking-widest hover:brightness-110 hover:border-b-[3px] hover:translate-y-[1px] active:border-b-0 active:translate-y-1 transition-all shadow-lg shadow-emerald-500/30 gap-2">
                    <i class="fa-solid text-sm" :class="exporting === 'excel' ? 'fa-spinner fa-spin' : 'fa-file-excel'"></i> <span x-text="exporting === 'excel' ? 'Wait...' : 'Excel'"></span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" id="export-pdf" data-no-pjax
                   @click="exporting = 'pdf'; setTimeout(() => exporting = null, 8000)"
                   :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': exporting }"
                   class="h-11 px-5 rounded-2xl bg-gradient-to-b from-rose-500 to-rose-600 border-b-4 border-rose-800 text-white flex items-center justify-center font-black text-xs uppercase tracking-widest hover:brightness-110 hover:border-b-[3px] hover:translate-y-[1px] active:border-b-0 active:translate-y-1 transition-all shadow-lg shadow-rose-500/30 gap-2">
                    <i class="fa-solid text-sm" :class="exporting === 'pdf' ? 'fa-spinner fa-spin' : 'fa-file-pdf'"></i> <span x-text="exporting === 'pdf' ? 'Wait...' : 'PDF'"></span>
                </a>
            </div>
            
            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden" x-show="!initialEmpty">
                <!-- Report Header Panel matching user's requested layout -->
                <div class="text-center py-5 border-b border-slate-200 bg-slate-50/50 space-y-1">
                    <h3 class="text-base font-black text-navy-900 tracking-tight uppercase leading-tight">{{ $title }}</h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $subtitle }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-100 border-b-2 border-slate-300">
                                <th class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 w-16 whitespace-nowrap">Sr. No</th>
                                @if(in_array('month', $groupBy))
                                <th class="px-4 py-3 text-left font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Month</th>
                                @endif
                                @if(in_array('date', $groupBy))
                                <th class="px-4 py-3 text-left font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Date</th>
                                @endif
                                @if(in_array('time', $groupBy))
                                <th class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap w-24">Time</th>
                                @endif
                                <th x-show="visibleColumns.username" class="px-4 py-3 text-left font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Agent Name</th>
                                <th x-show="visibleColumns.junk" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Junk</th>
                                <th x-show="visibleColumns.info" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Info</th>
                                <th x-show="visibleColumns.help" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Help</th>
                                <th x-show="visibleColumns.complaint" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Complaint</th>
                                <th x-show="visibleColumns.emergency" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Emergency</th>
                                <th x-show="visibleColumns.total" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide whitespace-nowrap">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalJunk = 0;
                                $totalInfo = 0;
                                $totalHelp = 0;
                                $totalComplaint = 0;
                                $totalEmergency = 0;
                                $totalGrand = 0;
                            @endphp

                            @forelse($data as $index => $row)
                                @php
                                    $junk = $row['junk'] ?? 0;
                                    $info = $row['info'] ?? 0;
                                    $help = $row['help'] ?? 0;
                                    $complaint = $row['complaint'] ?? 0;
                                    $emergency = $row['emergency'] ?? 0;
                                    $total = $row['total'] ?? 0;

                                    $totalJunk += $junk;
                                    $totalInfo += $info;
                                    $totalHelp += $help;
                                    $totalComplaint += $complaint;
                                    $totalEmergency += $emergency;
                                    $totalGrand += $total;
                                @endphp
                                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-slate-50/60' }} hover:bg-blue-50/40 transition-colors border-b border-slate-100">
                                    <td class="px-4 py-2.5 text-slate-500 font-semibold text-center border-r border-slate-200">{{ $index + 1 }}</td>
                                    @if(in_array('month', $groupBy))
                                    <td class="px-4 py-2.5 border-r border-slate-200 font-semibold text-slate-700 whitespace-nowrap">{{ $row['month'] ?? '' }}</td>
                                    @endif
                                    @if(in_array('date', $groupBy))
                                    <td class="px-4 py-2.5 border-r border-slate-200 font-semibold text-slate-700 whitespace-nowrap">{{ $row['date'] ?? '' }}</td>
                                    @endif
                                    @if(in_array('time', $groupBy))
                                    <td class="px-4 py-2.5 border-r border-slate-200 font-semibold text-slate-700 text-center whitespace-nowrap">{{ $row['time'] ?? '' }}</td>
                                    @endif
                                    <td x-show="visibleColumns.username" class="px-4 py-2.5 font-bold text-slate-800 uppercase tracking-tight border-r border-slate-200">{{ $row['full_name'] ?: $row['username'] }}</td>
                                    <td x-show="visibleColumns.junk" class="px-4 py-2.5 text-center border-r border-slate-200 {{ $junk > 0 ? 'text-slate-600 font-bold' : 'text-slate-400' }}">{{ number_format($junk) }}</td>
                                    <td x-show="visibleColumns.info" class="px-4 py-2.5 text-center border-r border-slate-200 {{ $info > 0 ? 'text-blue-600 font-bold' : 'text-slate-400' }}">{{ number_format($info) }}</td>
                                    <td x-show="visibleColumns.help" class="px-4 py-2.5 text-center border-r border-slate-200 {{ $help > 0 ? 'text-emerald-600 font-bold' : 'text-slate-400' }}">{{ number_format($help) }}</td>
                                    <td x-show="visibleColumns.complaint" class="px-4 py-2.5 text-center border-r border-slate-200 {{ $complaint > 0 ? 'text-amber-600 font-bold' : 'text-slate-400' }}">{{ number_format($complaint) }}</td>
                                    <td x-show="visibleColumns.emergency" class="px-4 py-2.5 text-center border-r border-slate-200 {{ $emergency > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">{{ number_format($emergency) }}</td>
                                    <td x-show="visibleColumns.total" class="px-4 py-2.5 text-center font-black text-navy-900">{{ number_format($total) }}</td>
                                </tr>
                            @empty
                                @php
                                    $colsCount = 8 + count($groupBy);
                                @endphp
                                <tr>
                                    <td colspan="{{ $colsCount }}" class="px-4 py-16 text-center">
                                        <div class="inline-flex flex-col items-center">
                                            <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center text-2xl mb-3">
                                                <i class="fa-solid fa-chart-simple"></i>
                                            </div>
                                            <p class="font-bold text-slate-500 text-sm">No Performance logs found for selected period</p>
                                            <p class="text-xs text-slate-400 mt-1">Adjust the dates or shift selections</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse

                            {{-- Summary Footer Row --}}
                            @if(count($data) > 0)
                                <tr class="bg-slate-100 border-t-2 border-slate-400 font-black text-navy-900">
                                    <td class="px-4 py-3 text-center border-r border-slate-200">-</td>
                                    @if(in_array('month', $groupBy))
                                    <td class="px-4 py-3 border-r border-slate-200 font-black text-navy-900 uppercase text-xs tracking-wide">-</td>
                                    @endif
                                    @if(in_array('date', $groupBy))
                                    <td class="px-4 py-3 border-r border-slate-200 font-black text-navy-900 uppercase text-xs tracking-wide">-</td>
                                    @endif
                                    @if(in_array('time', $groupBy))
                                    <td class="px-4 py-3 border-r border-slate-200 font-black text-navy-900 uppercase text-xs tracking-wide">-</td>
                                    @endif
                                    <td x-show="visibleColumns.username" class="px-4 py-3 font-black uppercase tracking-tight border-r border-slate-200">Total</td>
                                    <td x-show="visibleColumns.junk" class="px-4 py-3 text-center border-r border-slate-200 font-black text-slate-800">{{ number_format($totalJunk) }}</td>
                                    <td x-show="visibleColumns.info" class="px-4 py-3 text-center border-r border-slate-200 font-black text-blue-700">{{ number_format($totalInfo) }}</td>
                                    <td x-show="visibleColumns.help" class="px-4 py-3 text-center border-r border-slate-200 font-black text-emerald-700">{{ number_format($totalHelp) }}</td>
                                    <td x-show="visibleColumns.complaint" class="px-4 py-3 text-center border-r border-slate-200 font-black text-amber-700">{{ number_format($totalComplaint) }}</td>
                                    <td x-show="visibleColumns.emergency" class="px-4 py-3 text-center border-r border-slate-200 font-black text-rose-700">{{ number_format($totalEmergency) }}</td>
                                    <td x-show="visibleColumns.total" class="px-4 py-3 text-center font-black text-navy-900">{{ number_format($totalGrand) }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Initial Empty State --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-12 text-center" x-show="initialEmpty">
                <div class="flex flex-col items-center justify-center space-y-4 max-w-md mx-auto">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl shadow-inner">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <h3 class="text-lg font-black text-navy-900 uppercase tracking-tight">Agent Performance</h3>
                    <p class="text-sm text-slate-500 font-semibold leading-relaxed">
                        Please choose one or more agents and configure your filters above, then click <span class="text-blue-600 font-bold">"Generate Report"</span> to analyze personnel calling progress.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
