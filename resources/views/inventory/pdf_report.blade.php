<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #17324d; font-size: 9px; }
        h1 { color: #174a73; margin: 0 0 4px; }
        p { color: #5d7487; margin: 0 0 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #174a73; color: #fff; padding: 6px 4px; text-align: left; }
        td { border-bottom: 1px solid #d5e4ed; padding: 5px 4px; }
        tr:nth-child(even) td { background: #eef7fb; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Victor Bernal Provincial High School | Generated {{ now()->format('M d, Y h:i A') }}</p>
    <table>
        <thead><tr>@foreach($headings as $heading)<th>{{ ucfirst(str_replace('_', ' ', $heading)) }}</th>@endforeach</tr></thead>
        <tbody>@foreach($rows as $row)<tr>@foreach($row as $value)<td>{{ $value }}</td>@endforeach</tr>@endforeach</tbody>
    </table>
</body>
</html>
