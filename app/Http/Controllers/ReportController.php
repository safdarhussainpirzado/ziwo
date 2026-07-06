<?php

namespace App\Http\Controllers;

use App\Exports\GenericReportExport;
use App\Exports\CustomFormattedExport;
use App\Services\ReportService;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ReportController extends Controller
{
    use AuthorizesRequests;

    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }



    public function callTypeSummary(Request $request)
    {
        $request->merge($this->resolveFilters($request, 'call_type_summary'));
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $groupByRaw = $request->input('group_by', ['month']);
        if (is_string($groupByRaw) && str_contains($groupByRaw, ',')) {
            $groupBy = array_values(array_filter(explode(',', $groupByRaw)));
        } elseif (is_string($groupByRaw) && $groupByRaw !== '') {
            $groupBy = [$groupByRaw];
        } elseif (is_array($groupByRaw)) {
            $groupBy = array_values(array_filter($groupByRaw));
        } else {
            $groupBy = [];
        }
        if (empty($groupBy)) {
            $groupBy = ['month'];
        }
        $request->merge(['group_by' => $groupBy]);

        $availableColumns = [
            'month' => 'Month',
            'date' => 'Date',
            'time' => 'Time',
            'emergency' => 'Emergency',
            'information' => 'Information',
            'general_help' => 'General Help',
            'complaint' => 'Complaint',
            'junk' => 'Junk',
            'total_voice_calls' => 'Total Voice Calls',
            'ivr' => 'IVR',
            'total_calls_received' => 'Total Calls Received'
        ];

        if ($this->isRestrictedUser()) {
            unset($availableColumns['total_voice_calls']);
            unset($availableColumns['ivr']);
            unset($availableColumns['total_calls_received']);
        }

        $visibleColsInput = $request->input('cols');
        if ($visibleColsInput) {
            $visibleColumns = [];
            foreach ($visibleColsInput as $key => $val) {
                if ($val == '1' && isset($availableColumns[$key])) {
                    $visibleColumns[$key] = $availableColumns[$key];
                }
            }
        } else {
            $visibleColumns = $availableColumns;
            foreach (['month', 'date', 'time'] as $g) {
                if (!in_array($g, $groupBy)) {
                    unset($visibleColumns[$g]);
                }
            }
        }

        // Always align visible columns with the active group_by fields
        $orderedVisibleColumns = [];
        foreach (['month', 'date', 'time'] as $g) {
            if (in_array($g, $groupBy)) {
                $orderedVisibleColumns[$g] = $availableColumns[$g];
            }
        }
        foreach ($visibleColumns as $key => $label) {
            if (!in_array($key, ['month', 'date', 'time'])) {
                $orderedVisibleColumns[$key] = $label;
            }
        }
        $visibleColumns = $orderedVisibleColumns;

        $isInitial = !$request->has('group_by') && !$request->has('export');
        $data = $isInitial ? [] : $this->reportService->callTypeSummary($request->all());

        if ($request->has('export')) {
            $data = $this->reportService->callTypeSummary($request->all());
            $title = 'Calls Summary Report';
            $subtitle = $this->getSubtitle($request);
            
            $filteredData = [];
            foreach ($data as $row) {
                $filteredRow = [];
                foreach ($visibleColumns as $colKey => $colLabel) {
                    $filteredRow[] = is_array($row) ? ($row[$colKey] ?? '') : ($row->{$colKey} ?? '');
                }
                $filteredData[] = $filteredRow;
            }

            if ($request->export === 'excel') {
                return Excel::download(new CustomFormattedExport($title, $subtitle, array_values($visibleColumns), $filteredData), 'calls_summary_report.xlsx');
            }
            if ($request->export === 'pdf') {
                return Pdf::loadView('reports.pdf.generic', [
                    'data' => collect($data)->take(1000),
                    'visibleColumns' => $visibleColumns,
                    'title' => $title,
                    'subtitle' => $subtitle
                ])->download('calls_summary_report.pdf');
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data,
                'availableColumns' => $availableColumns,
                'visibleColumns' => $visibleColumns,
            ]);
        }

        return view('reports.call-type-summary', array_merge(
            compact('data', 'availableColumns', 'visibleColumns', 'isInitial'),
            $this->getFilterData()
        ));
    }

    public function beatWise(Request $request)
    {
        $request->merge($this->resolveFilters($request, 'beat_wise'));
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $data = $this->reportService->beatWiseReport($request->all());
        $availableColumns = ['name' => 'Beat Name', 'total' => 'Total Calls', 'zone' => 'Parent Zone', 'sector' => 'Parent Sector'];
        $visibleColumns = $this->resolveVisibleColumns($request, $availableColumns);

        if ($request->has('export')) {
            $title = 'Beat Wise Calls Summary Management Report';
            $subtitle = $this->getSubtitle($request);
            if ($request->export === 'excel') {
                return Excel::download(new CustomFormattedExport($title, $subtitle, array_values($visibleColumns), $data), 'beat_wise_report.xlsx');
            }
            if ($request->export === 'pdf') {
                return Pdf::loadView('reports.pdf.generic', compact('data', 'visibleColumns', 'title', 'subtitle'))->download('beat_wise_report.pdf');
            }
        }

        if ($request->ajax()) {
            return view('reports.beat-wise', array_merge(
                compact('data', 'availableColumns', 'visibleColumns'),
                $this->getFilterData()
            ));
        }

        return view('reports.beat-wise', array_merge(
            compact('data', 'availableColumns', 'visibleColumns'),
            $this->getFilterData()
        ));
    }

    public function agentWise(Request $request)
    {
        $request->merge($this->resolveFilters($request, 'agent_wise'));
        abort_if(!auth()->user()->hasPermission('reports.view'), 403, 'Unauthorized access to agent reports module');
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $groupByRaw = $request->input('group_by', []);
        if (is_string($groupByRaw) && str_contains($groupByRaw, ',')) {
            $groupBy = array_values(array_filter(explode(',', $groupByRaw)));
        } elseif (is_string($groupByRaw) && $groupByRaw !== '') {
            $groupBy = [$groupByRaw];
        } elseif (is_array($groupByRaw)) {
            $groupBy = array_values(array_filter($groupByRaw));
        } else {
            $groupBy = [];
        }
        $request->merge(['group_by' => $groupBy]);

        $isInitial = !$request->has('date_from') && !$request->has('export');
        $data = $isInitial ? [] : $this->reportService->agentPerformance($request->all());
        
        $availableColumns = [
            'month' => 'Month',
            'date' => 'Date',
            'time' => 'Time',
            'username' => 'Agent Name',
            'junk' => 'Junk',
            'info' => 'Info',
            'help' => 'Help',
            'complaint' => 'Complaint',
            'emergency' => 'Emergency',
            'total' => 'Total'
        ];
        $visibleColumns = $this->resolveVisibleColumns($request, $availableColumns);

        // Always align visible columns with the active group_by fields
        $orderedVisibleColumns = [];
        foreach (['month', 'date', 'time'] as $g) {
            if (in_array($g, $groupBy)) {
                $orderedVisibleColumns[$g] = $availableColumns[$g];
            }
        }
        foreach ($visibleColumns as $key => $label) {
            if (!in_array($key, ['month', 'date', 'time'])) {
                $orderedVisibleColumns[$key] = $label;
            }
        }
        $visibleColumns = $orderedVisibleColumns;

        $timeFrom = $request->input('time_from');
        $timeTo = $request->input('time_to');
        
        $shiftName = null;
        if ($timeFrom === '06:00' || $timeFrom === '06:00:00') {
            $shiftName = '1st Shift';
        } elseif ($timeFrom === '14:00' || $timeFrom === '14:00:00') {
            $shiftName = '2nd Shift';
        } elseif ($timeFrom === '22:00' || $timeFrom === '22:00:00') {
            $shiftName = '3rd Shift';
        }

        $incharge = $request->input('incharge');
        $inchargeShiftMapping = [
            'SI Aroosa Kainat' => '1st Shift',
            'SI Shabana Tabassum' => '2nd Shift',
            'SI Nida Naz' => '3rd Shift',
        ];

        $matchedShift = null;
        if ($incharge) {
            foreach ($inchargeShiftMapping as $name => $shift) {
                if (strcasecmp(trim($incharge), trim($name)) === 0) {
                    $matchedShift = $shift;
                    break;
                }
            }
            if ($matchedShift) {
                $shiftName = $matchedShift;
            }
        }
        
        $dateFormatted = \Carbon\Carbon::parse($request->input('date_from', now()->toDateString()))->format('d-m-Y');
        $title = 'Agent Wise Calls Progress';

        $subtitleSegments = ["Helpline 130"];
        
        if (auth()->check() && auth()->user()->role?->name === 'agent_supervisor') {
            $currentIncharge = $incharge ?: (auth()->user()->full_name ?: auth()->user()->username);
            if ($shiftName) {
                $subtitleSegments[] = "Incharge {$shiftName}: {$currentIncharge}";
            } else {
                $subtitleSegments[] = "Incharge: {$currentIncharge}";
            }
        } elseif ($shiftName) {
            $subtitleSegments[] = "Shift: {$shiftName}";
        }

        $timeSegment = "";
        if ($timeFrom && $timeTo) {
            $tF = str_replace(':', '', substr($timeFrom, 0, 5));
            $tT = str_replace(':', '', substr($timeTo, 0, 5));
            $timeSegment = " ({$tF}-{$tT} Hrs)";
        }
        $subtitleSegments[] = "Date: {$dateFormatted}{$timeSegment}";
        
        $subtitle = implode(' | ', $subtitleSegments);

        if ($request->has('export')) {
            // Prepend Sr. No. column for exports only
            $exportVisibleColumns = array_merge(['sr_no' => 'Sr. No.'], $visibleColumns);

            // Compute column totals
            $numericKeys = ['junk', 'info', 'help', 'complaint', 'emergency', 'total'];
            $colTotals = [];
            foreach ($numericKeys as $k) {
                $colTotals[$k] = array_sum(array_map(fn($r) => is_array($r) ? ($r[$k] ?? 0) : ($r->{$k} ?? 0), $data));
            }

            // Build flat rows for Excel with sequential Sr. No.
            $filteredData = [];
            $rowNum = 1;
            foreach ($data as $row) {
                $filteredRow = [];
                foreach ($exportVisibleColumns as $colKey => $colLabel) {
                    if ($colKey === 'sr_no') {
                        $filteredRow[] = $rowNum;
                    } else {
                        $val = is_array($row) ? ($row[$colKey] ?? '') : ($row->{$colKey} ?? '');
                        if ($colKey === 'username') {
                            $fullName = is_array($row) ? ($row['full_name'] ?? '') : ($row->full_name ?? '');
                            if ($fullName !== '') $val = $fullName;
                        }
                        $filteredRow[] = $val;
                    }
                }
                $filteredData[] = $filteredRow;
                $rowNum++;
            }

            if (!empty($data)) {
                $totalFlatRow = [];
                foreach ($exportVisibleColumns as $colKey => $colLabel) {
                    if ($colKey === 'sr_no') {
                        $totalFlatRow[] = '-';
                    } elseif (in_array($colKey, ['month', 'date', 'time'])) {
                        $totalFlatRow[] = '-';
                    } elseif ($colKey === 'username') {
                        $totalFlatRow[] = 'TOTAL';
                    } else {
                        $totalFlatRow[] = $colTotals[$colKey] ?? '';
                    }
                }
                $filteredData[] = $totalFlatRow;
            }

            // Build data + total row for PDF with sequential Sr. No.
            $pdfData = [];
            $rowNum = 1;
            foreach (array_values($data) as $row) {
                $arr = is_array($row) ? $row : (array) $row;
                $arr['sr_no'] = $rowNum++;
                $pdfData[] = $arr;
            }
            if (!empty($data)) {
                $pdfTotalRow = [
                    '_is_total' => true,
                    'sr_no'     => '-',
                    'month'     => in_array('month', $groupBy) ? '-' : '',
                    'date'      => in_array('date', $groupBy)  ? '-' : '',
                    'time'      => in_array('time', $groupBy)  ? '-' : '',
                    'username'  => 'TOTAL',
                    'full_name' => '',
                ];
                foreach ($numericKeys as $k) {
                    $pdfTotalRow[$k] = $colTotals[$k];
                }
                $pdfData[] = $pdfTotalRow;
            }

            if ($request->export === 'excel') {
                return Excel::download(new CustomFormattedExport($title, $subtitle, array_values($exportVisibleColumns), $filteredData, true), 'agent_performance.xlsx');
            }
            if ($request->export === 'pdf') {
                return Pdf::loadView('reports.pdf.generic', ['data' => collect($pdfData)->take(1001), 'visibleColumns' => $exportVisibleColumns, 'title' => $title, 'subtitle' => $subtitle])->download('agent_performance.pdf');
            }
        }

        if ($request->ajax()) {
            return view('reports.agent-wise', array_merge(
                compact('data', 'availableColumns', 'visibleColumns', 'title', 'subtitle', 'isInitial', 'groupBy'),
                $this->getFilterData()
            ));
        }

        return view('reports.agent-wise', array_merge(
            compact('data', 'availableColumns', 'visibleColumns', 'title', 'subtitle', 'isInitial', 'groupBy'),
            $this->getFilterData()
        ));
    }

    public function slaCompliance(Request $request)
    {
        $request->merge($this->resolveFilters($request, 'sla_compliance'));
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $data = $this->reportService->slaCompliance($request->all());
        $availableColumns = ['priority' => 'Priority', 'total' => 'Total Calls', 'within' => 'Within SLA', 'percentage' => 'Compliance %'];
        $visibleColumns = $this->resolveVisibleColumns($request, $availableColumns);

        if ($request->has('export')) {
            $title = 'SLA Compliance Monitoring Report';
            $subtitle = $this->getSubtitle($request);
            $flatData = array_values($data);
            if ($request->export === 'excel') {
                return Excel::download(new CustomFormattedExport($title, $subtitle, array_values($visibleColumns), $flatData), 'sla_compliance.xlsx');
            }
            if ($request->export === 'pdf') {
                return Pdf::loadView('reports.pdf.generic', ['data' => $flatData, 'visibleColumns' => $visibleColumns, 'title' => $title, 'subtitle' => $subtitle])->download('sla_compliance.pdf');
            }
        }

        if ($request->ajax()) {
            return view('reports.sla-compliance', array_merge(
                compact('data', 'availableColumns', 'visibleColumns'),
                $this->getFilterData()
            ));
        }

        return view('reports.sla-compliance', array_merge(
            compact('data', 'availableColumns', 'visibleColumns'),
            $this->getFilterData()
        ));
    }

    public function maxResponseTime(Request $request)
    {
        $request->merge($this->resolveFilters($request, 'max_response_time'));
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $data = $this->reportService->maxResponseTime($request->all());
        $availableColumns = ['beat' => 'Beat', 'max_response' => 'Max Response (sec)'];
        $visibleColumns = $this->resolveVisibleColumns($request, $availableColumns);

        if ($request->has('export')) {
            $title = 'Max Response Time Report';
            $subtitle = $this->getSubtitle($request);
            if ($request->export === 'excel') {
                return Excel::download(new CustomFormattedExport($title, $subtitle, array_values($visibleColumns), $data), 'max_response_time.xlsx');
            }
            if ($request->export === 'pdf') {
                return Pdf::loadView('reports.pdf.generic', compact('data', 'visibleColumns', 'title', 'subtitle'))->download('max_response_time.pdf');
            }
        }

        if ($request->ajax()) {
            return view('reports.max-response-time', array_merge(
                compact('data', 'availableColumns', 'visibleColumns'),
                $this->getFilterData()
            ));
        }

        return view('reports.max-response-time', array_merge(
            compact('data', 'availableColumns', 'visibleColumns'),
            $this->getFilterData()
        ));
    }

    public function predictiveAnalysis(Request $request)
    {
        $request->merge($this->resolveFilters($request, 'predictive_analysis'));
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $data = $this->reportService->predictiveAnalysis($request->all());
        $availableColumns = ['hour' => 'Hour', 'forecast' => 'Incident Forecast'];
        $visibleColumns = $this->resolveVisibleColumns($request, $availableColumns);

        if ($request->has('export')) {
            $title = 'Predictive Load Analysis Report';
            $subtitle = $this->getSubtitle($request);
            // Flatten temporal data for export
            $exportData = array_map(fn($h, $c) => ['hour' => str_pad($h, 2, '0', STR_PAD_LEFT).':00 hrs', 'forecast' => $c], array_keys($data['temporal']), $data['temporal']);
            if ($request->export === 'excel') {
                return Excel::download(new CustomFormattedExport($title, $subtitle, ['Hour', 'Incident Forecast'], $exportData), 'predictive_analysis.xlsx');
            }
            if ($request->export === 'pdf') {
                $visibleColumns = ['hour' => 'Hour', 'forecast' => 'Incident Forecast'];
                $data_pdf = $exportData;
                return Pdf::loadView('reports.pdf.generic', ['data' => $data_pdf, 'visibleColumns' => $visibleColumns, 'title' => $title, 'subtitle' => $subtitle])->download('predictive_analysis.pdf');
            }
        }

        if ($request->ajax()) {
            return view('reports.predictive-analysis', array_merge(
                compact('data', 'availableColumns', 'visibleColumns'),
                $this->getFilterData()
            ));
        }

        return view('reports.predictive-analysis', array_merge(
            compact('data', 'availableColumns', 'visibleColumns'),
            $this->getFilterData()
        ));
    }

    public function categoryAnalysis(Request $request)
    {
        $request->merge($this->resolveFilters($request, 'category_analysis'));
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $data = $this->reportService->categoryAnalysis($request->all());
        $availableColumns = ['category_label' => 'Major Category', 'total' => 'Total Volume', 'percentage' => 'Share %', 'avg_duration' => 'Avg Duration'];
        $visibleColumns = $this->resolveVisibleColumns($request, $availableColumns);

        if ($request->has('export')) {
            $title = 'Category Analysis Report';
            $subtitle = $this->getSubtitle($request);
            if ($request->export === 'excel') {
                return Excel::download(new CustomFormattedExport($title, $subtitle, array_values($visibleColumns), $data), 'category_analysis.xlsx');
            }
            if ($request->export === 'pdf') {
                return Pdf::loadView('reports.pdf.generic', compact('data', 'visibleColumns', 'title', 'subtitle'))->download('category_analysis.pdf');
            }
        }

        if ($request->ajax()) {
            return view('reports.category-analysis', array_merge(
                compact('data', 'availableColumns', 'visibleColumns'),
                $this->getFilterData()
            ));
        }

        return view('reports.category-analysis', array_merge(
            compact('data', 'availableColumns', 'visibleColumns'),
            $this->getFilterData()
        ));
    }

    public function junkCallsFrequency(Request $request)
    {
        $request->merge($this->resolveFilters($request, 'junk_calls_frequency'));
        set_time_limit(300);
        ini_set('memory_limit', '1024M');

        $groupByRaw = $request->input('group_by', ['date']);
        if (is_string($groupByRaw) && str_contains($groupByRaw, ',')) {
            $groupBy = array_values(array_filter(explode(',', $groupByRaw)));
        } elseif (is_string($groupByRaw) && $groupByRaw !== '') {
            $groupBy = [$groupByRaw];
        } elseif (is_array($groupByRaw)) {
            $groupBy = array_values(array_filter($groupByRaw));
        } else {
            $groupBy = [];
        }
        if (empty($groupBy)) {
            $groupBy = ['date'];
        }
        $request->merge(['group_by' => $groupBy]);

        $availableColumns = [
            'month' => 'Month',
            'date' => 'Date',
            'time' => 'Time',
            'username' => 'Agent',
            'mobile_number' => 'Mobile Numbers',
            'total_calls' => 'Total Calls'
        ];
        
        $visibleColumns = $this->resolveVisibleColumns($request, $availableColumns);

        // Always align visible columns with the active group_by fields
        $orderedVisibleColumns = [];
        foreach (['month', 'date', 'time'] as $g) {
            if (in_array($g, $groupBy)) {
                $orderedVisibleColumns[$g] = $availableColumns[$g];
            }
        }
        foreach ($visibleColumns as $key => $label) {
            if (!in_array($key, ['month', 'date', 'time'])) {
                $orderedVisibleColumns[$key] = $label;
            }
        }
        $visibleColumns = $orderedVisibleColumns;

        if ($request->has('export')) {
            $data = $this->reportService->junkCallsFrequency($request->all());
            $title = 'Junk Callers Summary Report';
            $subtitle = $this->getSubtitle($request);
            
            $filteredData = [];
            foreach ($data as $row) {
                $filteredRow = [];
                foreach ($visibleColumns as $colKey => $colLabel) {
                    $filteredRow[] = is_array($row) ? ($row[$colKey] ?? '') : ($row->{$colKey} ?? '');
                }
                $filteredData[] = $filteredRow;
            }

            if ($request->export === 'excel') {
                return Excel::download(new CustomFormattedExport($title, $subtitle, array_values($visibleColumns), $filteredData), 'junk_callers_summary.xlsx');
            }
            if ($request->export === 'pdf') {
                return Pdf::loadView('reports.pdf.generic', ['data' => collect($data)->take(1000), 'visibleColumns' => $visibleColumns, 'title' => $title, 'subtitle' => $subtitle])->download('junk_callers_summary.pdf');
            }
        }

        $isInitial = !$request->has('date_from') && !$request->has('export');
        $perPage = (int) $request->input('per_page', 10);
        $paginatedData = $isInitial ? new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, 1, ['path' => $request->url()]) : $this->reportService->junkCallsFrequency($request->all(), true, $perPage);

        if ($request->ajax()) {
            return view('reports.junk-calls', array_merge(
                ['data' => $paginatedData, 'availableColumns' => $availableColumns, 'visibleColumns' => $visibleColumns, 'isInitial' => $isInitial],
                $this->getFilterData()
            ));
        }

        return view('reports.junk-calls', array_merge(
            ['data' => $paginatedData, 'availableColumns' => $availableColumns, 'visibleColumns' => $visibleColumns, 'isInitial' => $isInitial],
            $this->getFilterData()
        ));
    }

    private function isRestrictedUser(): bool
    {
        $role = auth()->user()?->role?->name;
        return in_array($role, ['zone_admin', 'sector_admin', 'beat_operator']);
    }

    private function resolveFilters(Request $request, string $reportName)
    {
        $sessionKey = 'report_filters.' . $reportName;

        if ($request->has('date_from')) {
            $filters = $request->all();
            session()->put($sessionKey, $filters);
            return $filters;
        }

        if ($request->has('page')) {
            $sessionFilters = session()->get($sessionKey, []);
            return array_merge($sessionFilters, $request->all());
        }

        session()->forget($sessionKey);
        return $request->all();
    }

    private function getFilterData(): array
    {
        return [
            'offices' => [
                'zones' => \App\Models\Office::zones()->active()->get(['id', 'name']),
                'sectors' => \App\Models\Office::sectors()->active()->get(['id', 'name', 'parent_id']),
                'beats' => \App\Models\Office::beats()->active()->get(['id', 'name', 'parent_id']),
            ],
            'agents' => \App\Models\User::whereHas('role', function($q) {
                $q->where('name', 'agent');
            })
            ->where('full_name', '!=', 'System Administrator')
            ->where('username', '!=', '130_crm_admin')
            ->where('username', '!=', 'nhmp_admin')
            ->get(['id', 'username', 'full_name']),
            'callTypes' => \App\Models\CallType::active()->get(['id', 'name', 'category']),
        ];
    }

    private function resolveVisibleColumns(Request $request, array $availableColumns): array
    {
        $cols = $request->input('cols');
        if (is_array($cols)) {
            $visibleColumns = [];
            foreach ($cols as $key => $val) {
                if (isset($availableColumns[$key])) {
                    $visibleColumns[$key] = $availableColumns[$key];
                }
            }
            return $visibleColumns;
        }
        return $availableColumns;
    }

    private function getSubtitle(Request $request): string
    {
        $dateFrom = $request->input('date_from', now()->toDateString());
        $dateTo = $request->input('date_to', now()->toDateString());
        $timeFrom = $request->input('time_from', '00:00');
        $timeTo = $request->input('time_to', '23:59');

        $dF = \Carbon\Carbon::parse($dateFrom)->format('d-M-Y');
        $dT = \Carbon\Carbon::parse($dateTo)->format('d-M-Y');
        
        $tF = str_replace(':', '', $timeFrom) . ' hrs';
        $tT = str_replace(':', '', $timeTo) . ' hrs';

        return "{$dF} to {$dT} ({$tF} to {$tT})";
    }
}


