<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Users</title>
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
    <h1>User Directory</h1>
    <p>Victor Bernal Provincial High School | Generated {{ now()->format('M d, Y h:i A') }}</p>
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr></thead>
        <tbody>
            @foreach($users as $user)
                <tr><td>{{ $user->id }}</td><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->role }}</td><td>{{ $user->is_active ? 'active' : 'inactive' }}</td><td>{{ $user->created_at }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
