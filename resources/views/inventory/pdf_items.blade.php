<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Items</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #17324d; font-size: 10px; }
        h1 { color: #174a73; margin: 0 0 4px; }
        p { color: #5d7487; margin: 0 0 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #174a73; color: #fff; padding: 7px 5px; text-align: left; }
        td { border-bottom: 1px solid #d5e4ed; padding: 6px 5px; }
        tr:nth-child(even) td { background: #eef7fb; }
    </style>
</head>
<body>
    <h1>Inventory Items</h1>
    <p>Victor Bernal Provincial High School | Generated {{ now()->format('M d, Y h:i A') }}</p>
    <table>
        <thead><tr><th>ID</th><th>SKU</th><th>Name</th><th>Category</th><th>Location</th><th>Qty</th><th>Status</th></tr></thead>
        <tbody>
            @foreach($items as $item)
                <tr><td>{{ $item->id }}</td><td>{{ $item->sku }}</td><td>{{ $item->name }}</td><td>{{ $item->category }}</td><td>{{ $item->location }}</td><td>{{ $item->quantity }}</td><td>{{ $item->status }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
