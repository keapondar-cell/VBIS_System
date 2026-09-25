<style>
    .school-sidebar .school-sidebar-link,
    .school-sidebar .school-sidebar-link:hover,
    .school-sidebar .school-sidebar-link[aria-current="page"] {
        color: #fff !important;
    }

    .school-sidebar .school-notification-item {
        color: #fff !important;
    }

    .school-welcome { margin: .75rem .55rem 0; color: #dfeaf3; font-size: .68rem; line-height: 1.35; letter-spacing: .08em; text-transform: uppercase; }
    .school-welcome strong { display: block; margin-top: .15rem; color: #fff; font-size: .78rem; letter-spacing: .02em; text-transform: none; }

    .school-top-actions { position: absolute; top: 8px; right: 12px; z-index: 1100; display: flex; align-items: flex-start; gap: 6px; }
    .school-top-action { display: inline-flex; align-items: center; gap: 6px; min-height: 32px; padding: 4px 8px; border: 1px solid rgba(148,163,184,.35); border-radius: 999px; background: rgba(255,255,255,.96); color: #334155; text-decoration: none; box-shadow: 0 5px 14px rgba(15,23,42,.08); font-size: 12px; font-weight: 600; }
    .school-top-action:hover { color: #0f172a; }
    .school-notification-action { position: relative; width: 34px; justify-content: center; padding: 5px; }
    .school-notification-action .school-notification-badge { top: -5px; right: -5px; }
    .school-notification-icon { color: #2563eb; font-size: 16px; line-height: 1; }
    .school-top-action img, .school-top-action .school-user-initial { width: 24px; height: 24px; border-radius: 50%; object-fit: cover; }
    .school-profile-identity { display: grid; gap: 1px; line-height: 1.1; text-align: left; }
    .school-profile-role { color: #64748b; font-size: 10px; font-weight: 500; }
    .school-top-action .school-user-initial { display: inline-flex; align-items: center; justify-content: center; background: #dbeafe; color: #1d4ed8; font-size: 12px; }
    .school-top-profile-menu { position: absolute !important; top: 48px; right: 0; left: auto !important; bottom: auto !important; width: 140px; overflow: hidden; border: 1px solid #d8e5e8; border-radius: 8px; background: #fff; box-shadow: 0 8px 18px rgba(0,0,0,.14); }
    .school-top-profile-menu a, .school-top-profile-menu button { display: block; width: 100%; padding: 8px 12px; text-align: left; color: #17324d; background: #fff; border: 0; font-size: 12px; text-decoration: none; cursor: pointer; }
    .school-top-profile-menu a:hover, .school-top-profile-menu button:hover { background: #edf6f5; }
    .school-sidebar-bottom { display: none; }
    @media (max-width:1024px) { .school-top-actions { top: 8px; right: 10px; } .school-profile-chevron { display: none; } }
</style>

<nav x-data="{ open: false }" class="school-sidebar" aria-label="Main navigation">
    <div class="school-sidebar-brand">
        <a href="{{ route('inventory.dashboard') }}" class="flex items-center gap-3" aria-label="Inventory dashboard">
            <img src="/images/school-logo.jpg" alt="Victor Bernal Provincial High School" class="school-brand-mark" />
            <div class="leading-tight"><div class="text-lg font-extrabold text-white">VBIS</div><div class="text-[9px] font-semibold uppercase tracking-[.14em] text-slate-300">Inventory system</div></div>
        </a>
        <div class="school-welcome">Welcome back<strong>{{ Auth::user()->name }}</strong></div>
    </div>
    <div class="school-sidebar-section">Workspace</div>
    <div class="school-sidebar-links">
        <x-nav-link class="school-sidebar-link" :href="route('inventory.dashboard')" :active="request()->routeIs('inventory.dashboard')"><span class="school-sidebar-icon">⌂</span>{{ __('Dashboard') }}</x-nav-link>
        <x-nav-link class="school-sidebar-link" :href="route('inventory.items.list')" :active="request()->routeIs('inventory.items.*')"><span class="school-sidebar-icon">▦</span>{{ __('Inventory') }}</x-nav-link>
        <x-nav-link class="school-sidebar-link" :href="route('inventory.issues.list')" :active="request()->routeIs('inventory.issues.*')"><span class="school-sidebar-icon">▣</span>{{ __('Requests') }}</x-nav-link>
        <x-nav-link class="school-sidebar-link" :href="route('inventory.transactions.list')" :active="request()->routeIs('inventory.transactions.*')"><span class="school-sidebar-icon">↔</span>{{ __('Transactions') }}</x-nav-link>
        <x-nav-link class="school-sidebar-link" :href="route('inventory.qr.scan')" :active="request()->routeIs('inventory.qr.*')"><span class="school-sidebar-icon">▧</span>{{ __('QR Scanner') }}</x-nav-link>
        @if(auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian())
            <x-nav-link class="school-sidebar-link" :href="route('inventory.reports.menu')" :active="request()->routeIs('inventory.reports*')"><span class="school-sidebar-icon">▤</span>{{ __('Reports') }}</x-nav-link>
        @endif
        @if(auth()->user()->isAdmin())
            <x-nav-link class="school-sidebar-link" :href="route('admin.users')" :active="request()->routeIs('admin.*')"><span class="school-sidebar-icon">♙</span>{{ __('Users') }}</x-nav-link>
        @endif
        @if(auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian())
            <x-nav-link class="school-sidebar-link" :href="route('inventory.audit_trail')" :active="request()->routeIs('inventory.audit_trail')"><span class="school-sidebar-icon">◷</span>{{ __('Audit Trail') }}</x-nav-link>
        @endif
    </div>
    <div class="school-mobile-top lg:hidden">
        <button @click="open = !open" type="button" class="text-white text-xl" aria-label="Open menu">☰</button>
        <div x-show="open" x-cloak class="school-mobile-menu">
            <a href="{{ route('inventory.dashboard') }}">Dashboard</a>
            <a href="{{ route('inventory.items.list') }}">Inventory</a>
            <a href="{{ route('inventory.issues.list') }}">Requests</a>
            <a href="{{ route('inventory.transactions.list') }}">Transactions</a>
            <a href="{{ route('inventory.qr.scan') }}">QR Scanner</a>
            @if(auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian())<a href="{{ route('inventory.reports.menu') }}">Reports</a>@endif
            @if(auth()->user()->isAdmin())<a href="{{ route('admin.users') }}">Users</a>@endif
            @if(auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian())<a href="{{ route('inventory.audit_trail') }}">Audit Trail</a>@endif
            <a href="{{ route('profile.edit') }}">My Profile</a>
            <button type="button" onclick="showLogoutModal()">Log Out</button>
        </div>
    </div>
</nav>

<div x-data="{ open: false }" class="school-top-actions" aria-label="Account actions">
    <a href="{{ route('inventory.notifications.index') }}" class="school-top-action school-notification-action" aria-label="Notifications">
        <span class="school-notification-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" focusable="false"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9m-8.5 11a2.5 2.5 0 0 0 5 0z"/></svg></span>
        @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
        @if($unread > 0)<span class="school-notification-badge">{{ $unread }}</span>@endif
    </a>
    <button type="button" @click="open = !open" class="school-top-action">
        @if(Auth::user()->avatar)<img src="{{ Auth::user()->avatar }}" alt="avatar">@else<span class="school-user-initial">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>@endif
        <span class="school-profile-identity">
            <span>{{ Auth::user()->name }}</span>
            <small class="school-profile-role">{{ ucfirst(str_replace('_', ' ', Auth::user()->role ?? 'user')) }}</small>
        </span>
        <span class="school-profile-chevron" aria-hidden="true">⌄</span>
    </button>
    <div x-show="open" x-cloak class="school-top-profile-menu" style="z-index:1101;">
        <a href="{{ route('profile.edit') }}">My Profile</a>
        <button type="button" onclick="showLogoutModal()">Log Out</button>
    </div>
</div>

<div id="logoutModal" style="display:none;position:fixed;inset:0;background:rgba(7,25,44,.65);align-items:center;justify-content:center;z-index:2000"><div class="bg-white rounded-xl max-w-sm w-[90%] p-6 shadow-2xl"><h3 class="text-lg font-bold text-slate-800">Confirm Logout</h3><p class="mt-2 text-sm text-slate-500">Are you sure you want to logout from your account?</p><div class="mt-6 flex gap-2"><button type="button" onclick="closeLogoutModal()" class="flex-1 rounded-lg bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">Cancel</button><form method="POST" action="{{ route('logout') }}" class="flex-1">@csrf<button type="submit" class="w-full rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white">Logout</button></form></div></div></div>
<script>function showLogoutModal(){document.getElementById('logoutModal').style.display='flex'}function closeLogoutModal(){document.getElementById('logoutModal').style.display='none'}document.getElementById('logoutModal').addEventListener('click',function(e){if(e.target===this)closeLogoutModal()});</script>