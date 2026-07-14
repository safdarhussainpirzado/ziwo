<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $tab ?? 'Report' }} - ZIWO Export</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9pt; color: #1e293b; padding: 20px; }
        h1 { font-size: 14pt; color: #4f46e5; margin-bottom: 4px; }
        .meta { font-size: 8pt; color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #4f46e5; color: white; padding: 6px 8px; text-align: left; font-size: 8pt; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 8pt; }
        tr:nth-child(even) td { background: #f8fafc; }
        .footer { margin-top: 20px; font-size: 7pt; color: #94a3b8; text-align: right; }
    </style>
</head>
<body>
    <h1>ZIWO Report — {{ ucfirst($tab ?? 'General') }}</h1>
    <div class="meta">
        Period: {{ $filters['from'] ?? '-' }} to {{ $filters['to'] ?? '-' }} &nbsp;|&nbsp;
        Generated: {{ now()->format('Y-m-d H:i:s') }} &nbsp;|&nbsp;
        NHMP 130 CRM — ZIWO Admin
    </div>

    <table>
        <thead>
            <tr>
                @foreach($headers as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
            <tr>
                @foreach($row as $cell)
                    <td>{{ $cell }}</td>
                @endforeach
            </tr>
            @empty
            <tr><td colspan="{{ count($headers) }}" style="text-align:center;color:#94a3b8;">No data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Total rows: {{ count($rows) }} &nbsp;|&nbsp; NHMP 130 — ZIWO Admin Reports</div>
</body>
</html>
