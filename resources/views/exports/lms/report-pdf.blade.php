<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; line-height: 1.4; color: #333; }
        .header { padding: 20px; border-bottom: 2px solid #4472C4; margin-bottom: 20px; }
        .header-content { display: table; width: 100%; }
        .title-section { display: table-cell; vertical-align: middle; }
        .school-name { font-size: 18px; font-weight: bold; color: #4472C4; margin-bottom: 5px; }
        .report-title { font-size: 14px; color: #666; }
        .meta-section { display: table-cell; text-align: right; vertical-align: middle; font-size: 9px; color: #888; }
        .content { padding: 0 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #4472C4; color: white; padding: 8px 6px; text-align: left; font-weight: bold; font-size: 9px; }
        td { padding: 6px; border-bottom: 1px solid #e0e0e0; font-size: 9px; }
        tr:nth-child(even) { background-color: #f8f9fa; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 10px 20px; border-top: 1px solid #ddd; font-size: 8px; color: #888; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        .badge-danger { background-color: #f8d7da; color: #721c24; }
        .badge-info { background-color: #d1ecf1; color: #0c5460; }
        .badge-secondary { background-color: #e2e3e5; color: #383d41; }
        .text-center { text-align: center; }
        .no-data { text-align: center; padding: 40px; color: #888; font-style: italic; }
        .summary-box { background-color: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 4px; padding: 15px; margin-bottom: 20px; }
        .summary-title { font-size: 11px; font-weight: bold; color: #4472C4; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="title-section">
                <div class="school-name">{{ $school }}</div>
                <div class="report-title">{{ $title }}</div>
            </div>
            <div class="meta-section">
                <div>Generated: {{ $generatedAt }}</div>
                <div>Total Records: {{ count($data) }}</div>
            </div>
        </div>
    </div>

    <div class="content">
        @if(count($data) > 0)
            @include("exports.lms.partials.{$reportType}", ['data' => $data])
        @else
            <div class="no-data">No data available for this report.</div>
        @endif
    </div>

    <div class="footer">
        {{ $school }} - {{ $title }} | Generated on {{ $generatedAt }}
    </div>
</body>
</html>
