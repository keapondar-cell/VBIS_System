<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit User</title>
    <style>
        body{font-family:Arial;margin:0;padding:20px;background:#f5f5f5}
        .container{max-width:600px;margin:0 auto}
        .card{background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);padding:24px;margin-bottom:16px}
        .card h1{margin:0 0 24px 0;font-size:24px;color:#333}
        .form-group{margin-bottom:16px}
        .form-group label{display:block;margin-bottom:6px;font-weight:600;color:#333}
        .form-group input, .form-group select, .form-group textarea{width:100%;padding:8px 12px;border:1px solid #ddd;border-radius:4px;font-size:14px;box-sizing:border-box}
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus{outline:none;border-color:#007bff;box-shadow:0 0 0 3px rgba(0,123,255,0.25)}
        .form-actions{display:flex;gap:8px;margin-top:24px}
        .btn{padding:8px 16px;border:none;border-radius:4px;cursor:pointer;font-size:14px;font-weight:600;text-decoration:none;display:inline-block;transition:all 0.2s}
        .btn-primary{background:#007bff;color:#fff}
        .btn-primary:hover{background:#0056b3}
        .btn-secondary{background:#6c757d;color:#fff}
        .btn-secondary:hover{background:#5a6268}
        .alert{padding:12px 16px;border-radius:4px;margin-bottom:16px}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
        .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
        .info-box{background:#e7f3ff;border:1px solid #b3d9ff;border-radius:4px;padding:12px;margin-bottom:16px;color:#004085;font-size:14px}
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Edit User</h1>

            @if($errors->any())
                <div class="alert alert-error">
                    <strong>Error:</strong> Please fix the following issues:
                    <ul style="margin:8px 0 0 20px;padding:0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="info-box">
                <strong>User ID:</strong> {{ $user->id }}<br>
                <strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $user->role)) }}<br>
                <strong>Status:</strong> {{ $user->is_active ? '✓ Active' : '✗ Inactive' }}<br>
                <strong>Member Since:</strong> {{ $user->created_at->format('M d, Y') }}
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                @csrf
                @method('PATCH')

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('admin.users') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
