<!DOCTYPE html>
<html>
<head>
    <title>Call Type Summary Report</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #0f172a; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #0f172a; padding-bottom: 10px; }
        .header h1 { margin: 0; text-transform: uppercase; font-size: 18px; }
        .meta { margin-bottom: 20px; font-size: 10px; color: #64748b; }
        table { w-full; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #0f172a; color: white; text-align: left; padding: 10px; text-transform: uppercase; font-size: 10px; }
        td { border-bottom: 1px solid #e2e8f0; padding: 10px; }
        .total-row { font-weight: bold; background-color: #f8fafc; }
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; }
        .badge-blue { background-color: #eff6ff; color: #1d4ed8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>NHMP 130 Helpline - Call Type Summary</h1>
        <div class="meta">Target Synchronization: {{ now()->format('Y-m-d H:i:s') }} | Operational Node: HQ Mainframe</div>
    </div>

    <table width="100%">
        <thead>
            <tr>
                <th>Type</th>
                <th>Sub-Type</th>
                <th>Total Volume</th>
                <th>% Dist</th>
                <th>Emergency (P1)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td style="font-weight: bold; text-transform: uppercase;">{{ $row['type'] }}</td>
                <td>{{ $row['sub_type'] }}</td>
                <td><span class="badge badge-blue">{{ $row['total'] }}</span></td>
                <td>{{ $row['percentage'] }}%</td>
                <td style="color: {{ $row['emergency'] > 0 ? '#e11d48' : '#64748b' }}">{{ $row['emergency'] }} cases</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 10px; text-align: center; font-size: 9px; color: #94a3b8;">
        This is an automatically generated operational audit report. Confidential - Restricted Access.
    </div>
</body>
</html>
