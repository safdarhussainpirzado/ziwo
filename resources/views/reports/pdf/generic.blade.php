<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 0;
            font-size: 8.5px;
            line-height: 1.4;
        }
        .header {
            margin-bottom: 15px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin: 0;
        }
        .header-table td {
            border: none;
            padding: 0;
        }
        .logo-title h1 {
            margin: 0;
            text-transform: uppercase;
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
            color: #0f172a;
        }
        .logo-title p {
            margin: 2px 0 0 0;
            font-size: 9px;
            color: #64748b;
            font-weight: bold;
        }
        .meta-info {
            text-align: right;
            font-size: 8.5px;
            color: #475569;
        }
        .meta-info span {
            font-weight: bold;
            color: #0f172a;
        }
        
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: auto;
        }
        table.report-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
            padding: 7px 5px;
            border: 1px solid #475569;
        }
        table.report-table td {
            padding: 6px 5px;
            border: 1px solid #cbd5e1;
            color: #334155;
            vertical-align: middle;
        }
        /* Zebra Striping */
        table.report-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        /* Total Row Styling */
        table.report-table tr.total-row {
            background-color: #e2e8f0 !important;
        }
        table.report-table tr.total-row td {
            color: #0f172a;
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 25px;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    @php
        $alignLeft = ['month', 'date', 'time', 'name', 'username', 'zone', 'sector', 'beat', 'mobile_number', 'mobile', 'phone'];
    @endphp

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-title">
                    <h1>{{ $title }}</h1>
                    <p>{{ $subtitle }}</p>
                </td>
                <td class="meta-info">
                    <span>{{ now()->timezone('Asia/Karachi')->format('M d, Y h:i A') }}</span><br>
                    <span>NHMP 130 Command Center</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                @foreach($visibleColumns as $key => $label)
                <th style="text-align: {{ in_array($key, $alignLeft) ? 'left' : 'center' }};">{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                @php
                    $isTotal = false;
                    if (is_array($row)) {
                        if (($row['month'] ?? '') === 'Total' || ($row['name'] ?? '') === 'Total' || !empty($row['_is_total'])) {
                            $isTotal = true;
                        }
                    } else {
                        if (($row->month ?? '') === 'Total' || ($row->name ?? '') === 'Total' || !empty($row->_is_total)) {
                            $isTotal = true;
                        }
                    }
                @endphp
                <tr class="{{ $isTotal ? 'total-row' : '' }}">
                    @foreach($visibleColumns as $key => $label)
                        @php
                            $val = is_array($row) ? ($row[$key] ?? '') : ($row->$key ?? '');
                            if ($key === 'username') {
                                $fullName = is_array($row) ? ($row['full_name'] ?? '') : ($row->full_name ?? '');
                                if ($fullName !== '') {
                                    $val = $fullName;
                                }
                            }
                            $isNumeric = !in_array($key, $alignLeft) && is_numeric($val) && $val !== '';
                            $formattedVal = $isNumeric ? number_format($val) : $val;
                        @endphp
                        <td style="text-align: {{ in_array($key, $alignLeft) ? 'left' : 'center' }};">
                            {{ $formattedVal }}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Confidential - National Highways & Motorway Police © {{ date('Y') }} - System Audited Document
    </div>
</body>
</html>
