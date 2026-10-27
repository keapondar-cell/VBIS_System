<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Transactions</title>
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
    @include('reports.school-header')
    <h1>Inventory Transactions</h1>
    <p>Generated {{ \App\Support\SchoolTime::format(now(), 'M d, Y h:i A') }}</p>
    <table>
        <thead><tr><th>ID</th><th>Item</th><th>User</th><th>Type</th><th>Qty</th><th>Department</th><th>Due</th><th>Returned</th><th>Notes</th><th>Date</th></tr></thead>
        <tbody>
            @foreach($transactions as $transaction)
                <tr><td>{{ $transaction->id }}</td><td>{{ optional($transaction->item)->name }}</td><td>{{ optional($transaction->user)->name }}</td><td>{{ $transaction->transaction_type }}</td><td>{{ $transaction->quantity }}</td><td>{{ optional($transaction->department)->name ?? 'Department '.$transaction->department_id }}</td><td>{{ \App\Support\SchoolTime::format($transaction->expected_return_at, 'M d, Y') }}</td><td>{{ \App\Support\SchoolTime::format($transaction->returned_at, 'M d, Y h:i A') }}</td><td>{{ $transaction->notes }}</td><td>{{ \App\Support\SchoolTime::format($transaction->created_at, 'M d, Y h:i A') }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
