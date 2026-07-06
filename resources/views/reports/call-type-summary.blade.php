@extends('layouts.app')

@section('title', 'Calls Summary Report - NHMP 130')

@section('page-title', 'Calls Summary Report')

@section('content')
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="reportFilters">
        @include('reports.partials.filters')

        <div id="report-content">
            {{-- Export Buttons --}}
            <div class="flex justify-end gap-3 mb-5" x-show="!initialEmpty && reportData.length > 0">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" id="export-excel" data-no-pjax
                   @click="exporting = 'excel'; setTimeout(() => exporting = null, 3000)"
                   :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': exporting }"
                   class="h-12 px-6 rounded-2xl bg-gradient-to-b from-emerald-500 to-emerald-600 border-b-4 border-emerald-800 text-white flex items-center justify-center font-black text-xs uppercase tracking-widest hover:brightness-110 hover:border-b-[3px] hover:translate-y-[1px] active:border-b-0 active:translate-y-1 transition-all shadow-lg shadow-emerald-500/30 gap-2">
                    <i class="fa-solid text-sm" :class="exporting === 'excel' ? 'fa-spinner fa-spin' : 'fa-file-excel'"></i> <span x-text="exporting === 'excel' ? 'WAIT...' : 'EXCEL'"></span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" id="export-pdf" data-no-pjax
                   @click="exporting = 'pdf'; setTimeout(() => exporting = null, 8000)"
                   :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': exporting }"
                   class="h-12 px-6 rounded-2xl bg-gradient-to-b from-rose-500 to-rose-600 border-b-4 border-rose-800 text-white flex items-center justify-center font-black text-xs uppercase tracking-widest hover:brightness-110 hover:border-b-[3px] hover:translate-y-[1px] active:border-b-0 active:translate-y-1 transition-all shadow-lg shadow-rose-500/30 gap-2">
                    <i class="fa-solid text-sm" :class="exporting === 'pdf' ? 'fa-spinner fa-spin' : 'fa-file-pdf'"></i> <span x-text="exporting === 'pdf' ? 'WAIT...' : 'PDF'"></span>
                </a>
            </div>

            {{-- Report Table --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden" x-show="!initialEmpty">
                {{-- Report Header --}}
                <div class="text-center py-4 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-base font-black text-navy-900 tracking-tight uppercase" x-text="'Calls Summary Report (' + (Array.isArray(groupBy) ? groupBy.map(g => g.charAt(0).toUpperCase() + g.slice(1) + ' Wise').join(' & ') : (groupBy === 'time' ? 'Time Wise' : (groupBy === 'date' ? 'Date Wise' : 'Month Wise'))) + ')'">Calls Summary Report</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-100 border-b-2 border-slate-300">
                                <th x-show="groupBy.includes('month')" class="px-4 py-3 text-left font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Month</th>
                                <th x-show="groupBy.includes('date')" class="px-4 py-3 text-left font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Date</th>
                                <th x-show="groupBy.includes('time')" class="px-4 py-3 text-left font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Time</th>
                                <th x-show="visibleColumns.emergency" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Emergency</th>
                                <th x-show="visibleColumns.information" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Information</th>
                                <th x-show="visibleColumns.general_help" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">General Help</th>
                                <th x-show="visibleColumns.complaint" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Complaint</th>
                                <th x-show="visibleColumns.junk" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Junk</th>
                                <th x-show="visibleColumns.total_voice_calls" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Total Voice Calls</th>
                                <th x-show="visibleColumns.ivr" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">IVR</th>
                                <th x-show="visibleColumns.total_calls_received" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide whitespace-nowrap">Total Calls Received</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, i) in reportData" :key="i">
                                <tr :class="row.month === 'Total' ? 'bg-slate-100 border-t-2 border-slate-400 font-black' : (i % 2 === 0 ? 'bg-white' : 'bg-slate-50/60') + ' hover:bg-blue-50/40 transition-colors border-b border-slate-100'">
                                    <td x-show="groupBy.includes('month')" class="px-4 py-2.5 border-r border-slate-200 whitespace-nowrap" :class="row.month === 'Total' ? 'font-black text-navy-900' : 'font-semibold text-slate-700'">
                                        <span x-text="row.month === 'Total' ? (groupBy.includes('month') ? 'Total' : '') : row.month"></span>
                                    </td>
                                    <td x-show="groupBy.includes('date')" class="px-4 py-2.5 border-r border-slate-200 whitespace-nowrap" :class="row.month === 'Total' ? 'font-black text-navy-900' : 'font-semibold text-slate-700'">
                                        <span x-text="row.month === 'Total' ? (!groupBy.includes('month') && groupBy.includes('date') ? 'Total' : '') : row.date"></span>
                                    </td>
                                    <td x-show="groupBy.includes('time')" class="px-4 py-2.5 border-r border-slate-200 whitespace-nowrap" :class="row.month === 'Total' ? 'font-black text-navy-900' : 'font-semibold text-slate-700'">
                                        <span x-text="row.month === 'Total' ? (!groupBy.includes('month') && !groupBy.includes('date') && groupBy.includes('time') ? 'Total' : '') : row.time"></span>
                                    </td>
                                    <td x-show="visibleColumns.emergency" class="px-4 py-2.5 text-center border-r border-slate-200" :class="row.emergency > 0 ? 'text-rose-600 font-bold' : 'text-slate-500'">
                                        <span x-text="Number(row.emergency).toLocaleString()"></span>
                                    </td>
                                    <td x-show="visibleColumns.information" class="px-4 py-2.5 text-center border-r border-slate-200" :class="row.information > 0 ? 'text-blue-600 font-bold' : 'text-slate-500'">
                                        <span x-text="Number(row.information).toLocaleString()"></span>
                                    </td>
                                    <td x-show="visibleColumns.general_help" class="px-4 py-2.5 text-center border-r border-slate-200" :class="row.general_help > 0 ? 'text-emerald-600 font-bold' : 'text-slate-500'">
                                        <span x-text="Number(row.general_help).toLocaleString()"></span>
                                    </td>
                                    <td x-show="visibleColumns.complaint" class="px-4 py-2.5 text-center border-r border-slate-200" :class="row.complaint > 0 ? 'text-amber-600 font-bold' : 'text-slate-500'">
                                        <span x-text="Number(row.complaint).toLocaleString()"></span>
                                    </td>
                                    <td x-show="visibleColumns.junk" class="px-4 py-2.5 text-center border-r border-slate-200" :class="row.junk > 0 ? 'text-slate-600 font-bold' : 'text-slate-400'">
                                        <span x-text="Number(row.junk).toLocaleString()"></span>
                                    </td>
                                    <td x-show="visibleColumns.total_voice_calls" class="px-4 py-2.5 text-center border-r border-slate-200 font-semibold text-navy-900" :class="row.month === 'Total' ? 'font-black' : ''">
                                        <span x-text="Number(row.total_voice_calls).toLocaleString()"></span>
                                    </td>
                                    <td x-show="visibleColumns.ivr" class="px-4 py-2.5 text-center border-r border-slate-200 text-slate-500" :class="row.month === 'Total' ? 'font-black text-navy-900' : ''">
                                        <span x-text="Number(row.ivr).toLocaleString()"></span>
                                    </td>
                                    <td x-show="visibleColumns.total_calls_received" class="px-4 py-2.5 text-center font-bold text-navy-900" :class="row.month === 'Total' ? 'font-black' : ''">
                                        <span x-text="Number(row.total_calls_received).toLocaleString()"></span>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="reportData.length === 0">
                                <td colspan="9" class="text-center py-12 text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <i class="fa-solid fa-triangle-exclamation text-4xl text-amber-300"></i>
                                        <p class="font-semibold text-slate-500">No data found</p>
                                        <p class="text-xs text-slate-400">Try adjusting your filters or date range.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Initial Empty State --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-12 text-center" x-show="initialEmpty">
                <div class="flex flex-col items-center justify-center space-y-4 max-w-md mx-auto">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl shadow-inner">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3 class="text-lg font-black text-navy-900 uppercase tracking-tight">Calls Summary Report</h3>
                    <p class="text-sm text-slate-500 font-semibold leading-relaxed">
                        Please choose your grouping (Month Wise, Date Wise, or Time Wise) and configure filters above, then click <span class="text-blue-600 font-bold">"Generate Report"</span> to analyze the dynamic calls dataset.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
