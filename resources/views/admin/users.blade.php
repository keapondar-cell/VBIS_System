<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>User Management</title>
    <style>
        body{font-family:Arial;margin:0;padding:20px;background:radial-gradient(circle at 100% 0,rgba(47,154,145,.12),transparent 28rem),linear-gradient(135deg,#f4f8f8 0%,#e8f1f2 100%);color:#17324d}
        .container{max-width:1200px;margin:0 auto}
        .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .header h1{position:relative;display:inline-block;margin:0;color:#123b5d;letter-spacing:.01em}
        .header h1:after{content:"";display:block;width:3.5rem;height:4px;margin-top:.45rem;border-radius:4px;background:#d99b2b}
        .btn{display:inline-block;padding:8px 16px;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer;text-decoration:none;font-size:14px}
        .btn:hover{background:#0056b3}
        .btn.secondary{background:#6c757d}
        .btn.secondary:hover{background:#5a6268}
        .btn.danger{background:#dc3545}
        .btn.danger:hover{background:#c82333}
        .btn.success{background:#28a745}
        .btn.success:hover{background:#218838}
        .search-form{margin-bottom:20px;display:flex;gap:8px;padding:12px;background:rgba(255,255,255,.9);border:1px solid #d8e5e8;border-radius:12px;box-shadow:0 10px 24px rgba(18,59,93,.06)}
        .search-form input{flex:1;padding:8px 12px;border:1px solid #cbdde1;border-radius:7px}
        .table-wrapper{background:rgba(255,255,255,.9);border:1px solid #d8e5e8;border-radius:12px;box-shadow:0 16px 40px rgba(18,59,93,.08);overflow:hidden}
        table{width:100%;border-collapse:collapse}
        th{background:#dcefed;padding:12px;text-align:left;border-bottom:2px solid #c4dfe0;font-weight:600;color:#123b5d}
        td{padding:12px;border-bottom:1px solid #d8e5e8}
        tr:hover{background:#fbfefe}
        .status-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}
        .status-active{background:#d4edda;color:#155724}
        .status-inactive{background:#f8d7da;color:#721c24}
        .role-badge{display:inline-block;padding:4px 12px;border-radius:4px;font-size:12px;font-weight:600;background:#e7f3ff;color:#004085}
        .role-admin{background:#f8d7da;color:#721c24}
        .role-property_custodian{background:#d1ecf1;color:#0c5460}
        .role-teacher{background:#fff3cd;color:#856404}
        .user-cell{display:flex;align-items:center;gap:10px}
        .user-avatar{width:34px;height:34px;border-radius:50%;object-fit:cover;border:1px solid #dfe3e8;background:#eef2f7}
        .actions{display:flex;gap:6px;flex-wrap:wrap}
        .actions form{display:inline}
        .actions button{padding:6px 12px;font-size:12px}
        .pagination{display:flex;gap:4px;margin-top:16px;justify-content:center}
        .pagination a, .pagination span{padding:8px 12px;border:1px solid #b9d0d5;border-radius:4px;color:#287f92;text-decoration:none;font-size:14px}
        .pagination span{background:#f8f9fa;color:#666}
        .pagination a:hover{background:#f8f9fa}
        .alert{padding:12px 16px;border-radius:4px;margin-bottom:16px}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>User Management</h1>
            <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end">
                <a href="{{ route('inventory.dashboard') }}" class="btn secondary">Back to Dashboard</a>
                <a href="{{ route('register') }}" class="btn">Create New User</a>
                <a href="{{ route('admin.users.export') }}" class="btn secondary">Export CSV</a>
                <a href="{{ route('admin.users.export', ['format' => 'pdf']) }}" class="btn secondary">Export PDF</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="search-form">
            <form method="GET" action="{{ route('admin.users') }}" style="display:flex;gap:8px;width:100%">
                <input type="text" name="search" placeholder="Search by name or email..." value="{{ $search ?? '' }}" style="flex:1">
                <button type="submit" class="btn">Search</button>
                <a href="{{ route('admin.users') }}" class="btn secondary">Reset</a>
            </form>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="user-cell">
                                    @if($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }} avatar" class="user-avatar">
                                    @else
                                        <div class="user-avatar" style="display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:600;color:#495057;">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                    @endif
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="role-badge role-{{ $user->role }}">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                            </td>
                            <td>
                                <span class="status-badge {{ $user->is_active ? 'status-active' : 'status-inactive' }}">
                                    {{ $user->is_active ? 'Active' : 'Pending approval' }}
                                </span>
                            </td>
                            <td>{{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</td>
                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn secondary">Edit</a>

                                    <form method="POST" action="{{ route('admin.users.role', $user->id) }}" style="display:inline">
                                        @csrf
                                        <select name="role" onchange="this.form.submit()" style="padding:6px;border:1px solid #ddd;border-radius:4px;font-size:12px">
                                            <option value="">Change Role...</option>
                                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator</option>
                                            <option value="property_custodian" {{ $user->role === 'property_custodian' ? 'selected' : '' }}>Property Custodian</option>
                                            <option value="teacher" {{ $user->role === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                        </select>
                                    </form>

                                    @if($user->is_active)
                                        <form method="POST" action="{{ route('admin.users.toggle_status', $user->id) }}" style="display:inline" onsubmit="return confirm('Deactivate this user?')">
                                            @csrf
                                            <button type="submit" class="btn danger">Deactivate</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.approve', $user->id) }}" style="display:inline" onsubmit="return confirm('Approve this user account?')">
                                            @csrf
                                            <button type="submit" class="btn success">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.reject', $user->id) }}" style="display:inline" onsubmit="return confirm('Reject this user account?')">
                                            @csrf
                                            <button type="submit" class="btn danger">Reject</button>
                                        </form>
                                    @endif

                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}" style="display:inline" onsubmit="return confirm('Delete this user permanently?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn danger">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;color:#666">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $users->links() }}
        </div>
    </div>
</body>
</html>
