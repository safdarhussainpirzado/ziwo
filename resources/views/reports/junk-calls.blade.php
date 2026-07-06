@extends('layouts.app')

@section('title', 'Junk Callers Summary - NHMP 130')

@section('page-title', 'Junk Callers Summary')

@section('content')
    <div x-data="reportFilters" class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Filters partial --}}
        @include('reports.partials.filters')

        <div id="report-content" class="space-y-6">
            <span id="report-has-data" data-value="{{ count($data) > 0 ? 'true' : 'false' }}" class="hidden"></span>
            {{-- Toolbar: Show Rows and Export Buttons --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4" x-show="!initialEmpty">
                {{-- Records Per Page selection --}}
                <div class="flex items-center gap-3 bg-white px-4 py-2.5 rounded-2xl border border-slate-100 shadow-sm w-fit">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Show</span>
                    <select name="per_page" form="filterForm" onchange="document.getElementById('filterForm').requestSubmit()" 
                            class="bg-slate-50 border-none rounded-xl px-3 py-1.5 text-xs font-black text-navy-900 focus:ring-2 focus:ring-emerald-500/20 transition-all cursor-pointer">
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 Rows</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Rows</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Rows</option>
                    </select>
                </div>

                {{-- Export Buttons --}}
                <div class="flex justify-end gap-3" x-show="Array.isArray(reportData) ? reportData.length > 0 : (reportData && reportData.data && reportData.data.length > 0)">
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
            </div>

            {{-- Report Table Panel --}}
            <div class="bg-white rounded-[2rem] shadow-xl border border-slate-100 overflow-hidden" x-show="!initialEmpty">
                {{-- Report Header matching call type summary design --}}
                <div class="text-center py-5 border-b border-slate-200 bg-slate-50/50 space-y-1">
                    <h3 class="text-base font-black text-navy-900 tracking-tight uppercase leading-tight">Junk Callers Summary</h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Operational Auditing Grid</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-100 border-b-2 border-slate-300">
                                <th class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 w-16">Sr. No</th>
                                @if(in_array('month', request('group_by', [])))
                                <th class="px-4 py-3 text-left font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Month</th>
                                @endif
                                @if(in_array('date', request('group_by', ['date'])))
                                <th class="px-4 py-3 text-left font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Date</th>
                                @endif
                                 @if(in_array('time', request('group_by', [])))
                                <th class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap w-24">Time</th>
                                @endif
                                <th x-show="visibleColumns.username" class="px-4 py-3 text-left font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Agent</th>
                                <th x-show="visibleColumns.mobile_number" class="px-4 py-3 text-left font-black text-navy-900 text-xs uppercase tracking-wide border-r border-slate-200 whitespace-nowrap">Mobile Numbers</th>
                                <th x-show="visibleColumns.total_calls" class="px-4 py-3 text-center font-black text-navy-900 text-xs uppercase tracking-wide whitespace-nowrap">Total Calls</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $i => $row)
                            <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50/60' }} hover:bg-rose-50/30 transition-colors border-b border-slate-100">
                                <td class="px-4 py-2.5 text-center border-r border-slate-200 text-slate-500 font-semibold">
                                    {{ $data->firstItem() ? ($data->firstItem() + $i) : ($i + 1) }}
                                </td>
                                @if(in_array('month', request('group_by', [])))
                                <td class="px-4 py-2.5 border-r border-slate-200 font-semibold text-slate-700 whitespace-nowrap">{{ $row['month'] ?? '' }}</td>
                                @endif
                                @if(in_array('date', request('group_by', ['date'])))
                                <td class="px-4 py-2.5 border-r border-slate-200 font-semibold text-slate-700 whitespace-nowrap">
                                    {{ !empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('d-M-Y') : '' }}
                                </td>
                                @endif
                                @if(in_array('time', request('group_by', [])))
                                <td class="px-4 py-2.5 border-r border-slate-200 font-semibold text-slate-700 text-center whitespace-nowrap">{{ $row['time'] ?? '' }}</td>
                                @endif
                                <td x-show="visibleColumns.username" class="px-4 py-2.5 border-r border-slate-200 font-semibold text-slate-700 whitespace-nowrap">
                                    {{ $row['full_name'] ?? ($row['username'] ?? '') }}
                                </td>
                                <td x-show="visibleColumns.mobile_number" class="px-4 py-2.5 border-r border-slate-200 font-mono font-bold text-emerald-700 tracking-wide">
                                    {{ $row['mobile_number'] }}
                                </td>
                                <td x-show="visibleColumns.total_calls" class="px-4 py-2.5 text-center font-black text-navy-900">
                                    {{ number_format($row['total_calls']) }}
                                </td>
                            </tr>
                            @empty
                            @php
                                $colsCount = 1;
                                $colsCount += count(request('group_by', ['date']));
                                if (isset($visibleColumns['username'])) $colsCount++;
                                if (isset($visibleColumns['mobile_number'])) $colsCount++;
                                if (isset($visibleColumns['total_calls'])) $colsCount++;
                            @endphp
                            <tr>
                                <td colspan="{{ $colsCount }}" class="px-4 py-16 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-300 flex items-center justify-center text-2xl mb-3">
                                            <i class="fa-solid fa-phone-slash"></i>
                                        </div>
                                        <p class="font-bold text-slate-500 text-sm">No junk call records found</p>
                                        <p class="text-xs text-slate-400 mt-1">Adjust the date range or filters</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($data) > 0)
                        <tfoot>
                            <tr class="bg-slate-100 border-t-2 border-slate-400">
                                <td class="px-4 py-3 font-black text-navy-900 text-center border-r border-slate-200 uppercase text-xs tracking-wide">-</td>
                                @if(in_array('month', request('group_by', [])))
                                <td class="px-4 py-3 border-r border-slate-200 font-black text-navy-900 uppercase text-xs tracking-wide">-</td>
                                @endif
                                @if(in_array('date', request('group_by', ['date'])))
                                <td class="px-4 py-3 font-black text-navy-900 border-r border-slate-200 uppercase text-xs tracking-wide">Total</td>
                                @endif
                                @if(in_array('time', request('group_by', [])))
                                <td class="px-4 py-3 border-r border-slate-200 font-black text-navy-900 uppercase text-xs tracking-wide">-</td>
                                @endif
                                <td x-show="visibleColumns.username" class="px-4 py-3 border-r border-slate-200 font-black text-navy-900 uppercase text-xs tracking-wide">-</td>
                                <td x-show="visibleColumns.mobile_number" class="px-4 py-3 font-black text-navy-900 border-r border-slate-200 uppercase text-xs tracking-wide">Summary</td>
                                <td x-show="visibleColumns.total_calls" class="px-4 py-3 text-center font-black text-navy-900">
                                    {{ number_format(array_sum(array_column($data->items(), 'total_calls'))) }}
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                {{-- Pagination Panel --}}
                {{-- Pagination --}}
                @if($data->hasPages())
                    <div class="p-8 border-t border-slate-100 bg-slate-50/30 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs font-bold text-slate-400">
                            Showing <span class="text-blue-900">{{ $data->firstItem() ?? 0 }}</span> to <span class="text-blue-900">{{ $data->lastItem() ?? 0 }}</span> of <span class="text-blue-900">{{ $data->total() }}</span> records
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- First/Prev -->
                            <a href="{{ $data->url(1) }}" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 transition-all font-black text-[10px] uppercase flex items-center justify-center {{ $data->onFirstPage() ? 'opacity-30 pointer-events-none' : '' }}">First</a>
                            <a href="{{ $data->previousPageUrl() ?? '#' }}" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-all flex items-center justify-center {{ $data->onFirstPage() ? 'opacity-30 pointer-events-none' : '' }}"><i class="fa-solid fa-chevron-left"></i></a>

                            <!-- Page Numbers -->
                            @php
                                $currentPage = $data->currentPage();
                                $lastPage = $data->lastPage();
                                $start = max(1, $currentPage - 2);
                                $end = min($lastPage, $currentPage + 2);
                                if ($end - $start < 4) {
                                    if ($start == 1) {
                                        $end = min($lastPage, $start + 4);
                                    } else {
                                        $start = max(1, $end - 4);
                                    }
                                }
                            @endphp

                            @if($start > 1)
                                <a href="{{ $data->url(1) }}" class="bg-white text-slate-600 border-slate-200 hover:bg-slate-50 w-10 h-10 rounded-xl border font-black text-xs transition-all flex items-center justify-center">1</a>
                                @if($start > 2)
                                    <span class="px-2 text-slate-300 font-bold">...</span>
                                @endif
                            @endif

                            @for($p = $start; $p <= $end; $p++)
                                <a href="{{ $data->url($p) }}" class="w-10 h-10 rounded-xl border font-black text-xs transition-all flex items-center justify-center {{ $currentPage === $p ? 'bg-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/30' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                                    {{ $p }}
                                </a>
                            @endfor

                            @if($end < $lastPage)
                                @if($end < $lastPage - 1)
                                    <span class="px-2 text-slate-300 font-bold">...</span>
                                @endif
                                <a href="{{ $data->url($lastPage) }}" class="bg-white text-slate-600 border-slate-200 hover:bg-slate-50 w-10 h-10 rounded-xl border font-black text-xs transition-all flex items-center justify-center">{{ $lastPage }}</a>
                            @endif

                            <!-- Next/Last -->
                            <a href="{{ $data->nextPageUrl() ?? '#' }}" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition-all flex items-center justify-center {{ !$data->hasMorePages() ? 'opacity-30 pointer-events-none' : '' }}"><i class="fa-solid fa-chevron-right"></i></a>
                            <a href="{{ $data->url($lastPage) }}" class="w-10 h-10 rounded-xl border border-slate-200 bg-white text-slate-400 hover:bg-slate-50 transition-all font-black text-[10px] uppercase flex items-center justify-center {{ !$data->hasMorePages() ? 'opacity-30 pointer-events-none' : '' }}">Last</a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Initial Empty State --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-12 text-center" x-show="initialEmpty">
                <div class="flex flex-col items-center justify-center space-y-4 max-w-md mx-auto">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl shadow-inner">
                        <i class="fa-solid fa-ban"></i>
                    </div>
                    <h3 class="text-lg font-black text-navy-900 uppercase tracking-tight">Junk Callers Summary</h3>
                    <p class="text-sm text-slate-500 font-semibold leading-relaxed">
                        Please select your date range and grouping options, then click <span class="text-blue-600 font-bold">"Generate Report"</span> to load the junk callers analysis data.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
