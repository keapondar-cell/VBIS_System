<x-app-layout>
    <div>
        <div>
            <div>

                <style>
                    .btn{display:inline-block;padding:8px 12px;border-radius:6px;background:#0b61d8;color:#fff;text-decoration:none}
                    .btn.secondary{background:#6b7280}
                    .dashboard-container{display:block}
                    .dashboard-content{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr);gap:20px}
                    .dashboard-content > .dashboard-metrics,
                    .dashboard-content > .dashboard-low-stock,
                    .dashboard-content > .dashboard-charts{grid-column:1/-1}
                    .dashboard-content > .dashboard-actions,
                    .dashboard-content > .dashboard-realtime{grid-column:1/-1}
                    .dashboard-metrics{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;align-items:stretch}
                    .dashboard-stat,
                    .dashboard-borrowed,
                    .dashboard-due-borrows,
                    .dashboard-pending-requests{min-width:0;grid-column:auto}
                    .dashboard-pending-requests{min-width:0}
                    .dashboard-pending-requests .low-table{font-size:12px}
                    .dashboard-pending-requests .low-table th,.dashboard-pending-requests .low-table td{padding:10px 8px}
                    .dashboard-due-borrows{min-width:0}
                    .dashboard-due-borrows .due-list{display:grid;gap:10px}
                    .dashboard-due-borrows .due-row{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:12px 14px;border:1px solid #d8e5e8;border-radius:10px;background:#f8fbfc}
                    .dashboard-due-borrows .due-date{font-size:12px;color:#64748b}
                    .dashboard-due-borrows .due-status{font-size:12px;font-weight:800;white-space:nowrap}
                    .dashboard-due-borrows .due-status.overdue{color:#dc2626}
                    .dashboard-due-borrows .due-status.today{color:#d97706}
                    .dashboard-due-borrows .due-status.upcoming{color:#15803d}
                    .card{background:#fff;border:1px solid #d8e5e8;padding:16px;border-radius:14px;box-shadow:0 10px 24px rgba(18,59,93,.05)}
                    .low-table{width:100%;border-collapse:collapse;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;background:#fff}
                    .low-table th, .low-table td{border:1px solid #e2e8f0;padding:14px 16px;vertical-align:middle}
                    .low-table thead th{background:#f8fafc;color:#1e293b;font-size:14px;font-weight:800;letter-spacing:0.04em;text-transform:uppercase}
                    .low-table tbody tr:hover{background:#f8fafc}
                    .low-table td{font-size:15px;color:#1e293b}
                    .low-stock-pagination{display:flex;justify-content:space-between;align-items:center;gap:12px;padding-top:12px;margin-top:12px;border-top:1px solid #e2e8f0}
                    .low-stock-pagination .page-meta{font-size:12px;color:#64748b;font-weight:600}
                    .low-stock-pagination .page-actions{display:flex;gap:8px}
                    .low-stock-page-btn{display:inline-flex;align-items:center;justify-content:center;min-width:88px;padding:8px 12px;border-radius:8px;border:1px solid #cbd5e1;background:#fff;color:#0f172a;font-size:13px;font-weight:700;text-decoration:none}
                    .low-stock-page-btn:hover{background:#f8fafc}
                    .low-stock-page-btn[disabled]{opacity:.45;pointer-events:none}
                    .realtime-shell{background:linear-gradient(135deg,#edf6f5,#f7fbfb);border:1px solid #d8e5e8;border-radius:18px;padding:18px}
                    .realtime-header{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px}
                    .live-dot{width:10px;height:10px;border-radius:999px;background:#22c55e;box-shadow:0 0 0 6px rgba(34,197,94,0.12);display:inline-block;animation:pulse 1.8s infinite}
                    @keyframes pulse{0%{transform:scale(0.95);opacity:0.8}70%{transform:scale(1.05);opacity:1}100%{transform:scale(0.95);opacity:0.8}}
                    .realtime-panel-grid{display:flex;gap:16px;align-items:stretch}
                    .realtime-panel-grid > .panel-box{flex:1 1 0;min-width:0}
                    .panel-box{background:#fff;border:1px solid #d8e5e8;border-radius:14px;padding:16px;box-shadow:0 10px 24px rgba(18,59,93,.05)}
                    .panel-box h3{margin:0 0 10px;font-size:14px;text-transform:uppercase;letter-spacing:0.06em;color:#475569}
                    .dashboard-charts{max-width:960px;margin-inline:auto;display:grid;grid-template-columns:1fr 1fr;gap:16px}
                    .dashboard-charts .panel-box{min-width:0}
                    .dashboard-charts canvas{display:block;width:100%!important;height:260px!important;max-height:260px}
                    .activity-list{display:grid;gap:10px;max-height:400px;overflow:auto}
                    .activity-card{background:linear-gradient(180deg,#ffffff,#f8fafc);border:1px solid #e2e8f0;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(15,23,42,0.04)}
                    .activity-top{display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:8px}
                    .activity-badge{display:inline-flex;align-items:center;justify-content:center;padding:4px 8px;border-radius:999px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em}
                    .activity-badge.in{background:#dcfce7;color:#166534}
                    .activity-badge.out{background:#fee2e2;color:#991b1b}
                    .activity-badge.adjust{background:#e0f2fe;color:#075985}
                    .activity-time{font-size:11px;color:#64748b}
                    .activity-body{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:6px}
                    .activity-body strong{font-size:14px;color:#0f172a}
                    .activity-body span{font-size:12px;color:#475569;font-weight:600}
                    .activity-meta{display:flex;justify-content:space-between;gap:12px;font-size:11px;color:#64748b;flex-wrap:wrap}
                    .empty-state{padding:18px;border:1px dashed #cbd5e1;border-radius:10px;background:#f8fafc;color:#64748b;text-align:center}
                    .mini-list{list-style:none;padding:0;margin:0;display:grid;gap:8px}
                    .mini-list li{padding:10px 12px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc}
                    .mini-list strong{display:block;margin-bottom:4px}
                    .muted{color:#64748b}
                    @media (max-width: 1024px){
                        .dashboard-content{grid-template-columns:1fr}
                        .dashboard-content > .dashboard-actions,
                        .dashboard-content > .dashboard-realtime{grid-column:1}
                    }
                    @media (max-width: 768px){
                        .realtime-panel-grid{flex-direction:column}
                        .dashboard-charts{grid-template-columns:1fr}
                        .activity-body,.activity-meta{flex-direction:column;align-items:flex-start}
                        .dashboard-charts canvas{height:220px!important;max-height:220px}
                    }
                    @media (min-width: 1180px){
                        .activity-list{max-height:260px}
                    }
                </style>

                <section class="dashboard-hero mb-5">
                    <p class="dashboard-kicker">Victor Bernal Provincial High School</p>
                    <h1>Inventory Overview</h1>
                    <p>Keep supplies visible, requests moving, and every transaction accounted for.</p>
                </section>

                <div class="dashboard-container">
                    <div class="dashboard-content">
                        <section class="dashboard-metrics grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="dashboard-stat dashboard-panel border-l-4 border-teal-500 p-4">
                                <p class="dashboard-panel-title">Total items</p>
                                <p class="mt-2 text-3xl font-extrabold text-slate-800">{{ number_format($summary['total_items'] ?? 0) }}</p>
                                <p class="mt-1 text-xs text-slate-500">Tracked inventory records</p>
                            </div>
                            <div class="dashboard-stat dashboard-panel border-l-4 border-amber-500 p-4">
                                <p class="dashboard-panel-title">Low stock</p>
                                <p class="mt-2 text-3xl font-extrabold text-amber-600">{{ number_format($summary['low_stock_count'] ?? 0) }}</p>
                                <p class="mt-1 text-xs text-slate-500">At or below threshold</p>
                            </div>
                            <div class="dashboard-stat dashboard-panel border-l-4 border-rose-500 p-4">
                                <p class="dashboard-panel-title">Issued this month</p>
                                <p class="mt-2 text-3xl font-extrabold text-rose-600">{{ number_format($summary['issued_this_month'] ?? 0) }}</p>
                                <p class="mt-1 text-xs text-slate-500">Units issued</p>
                            </div>
                            <div class="dashboard-borrowed dashboard-panel border-l-4 border-sky-500 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="dashboard-panel-title">Borrowed this month</p>
                                        <p class="mt-2 text-3xl font-extrabold text-sky-700">{{ number_format($summary['borrowed_this_month'] ?? 0) }}</p>
                                    </div>
                                    <a class="dashboard-action secondary" href="{{ route('inventory.reports.borrowing') }}">Open borrowing report</a>
                                </div>
                            </div>
                            @if(array_key_exists('teacher_due_borrows', $summary))
                                <section class="dashboard-due-borrows">
                                    <div class="dashboard-panel border-l-4 border-sky-500 p-4 h-full">
                                        <div class="flex items-center justify-between gap-3 mb-3">
                                            <div>
                                                <p class="dashboard-panel-title">Your borrowed items</p>
                                                <h2 class="mt-1 text-lg font-bold text-slate-800">Return due dates</h2>
                                            </div>
                                            <a href="{{ route('inventory.transactions.list') }}" class="text-xs font-bold text-teal-700 hover:text-teal-900">View transactions</a>
                                        </div>
                                        @if(!empty($summary['teacher_due_borrows']))
                                            <div class="due-list">
                                                @foreach($summary['teacher_due_borrows'] as $borrow)
                                                    @php
                                                        $daysRemaining = (int) ($borrow['days_remaining'] ?? 0);
                                                        $statusClass = $daysRemaining < 0 ? 'overdue' : ($daysRemaining === 0 ? 'today' : 'upcoming');
                                                        $statusText = $daysRemaining < 0 ? abs($daysRemaining).' '.(abs($daysRemaining) === 1 ? 'day' : 'days').' overdue' : ($daysRemaining === 0 ? 'Due today' : $daysRemaining.' '.($daysRemaining === 1 ? 'day' : 'days').' remaining');
                                                    @endphp
                                                    <div class="due-row">
                                                        <div class="min-w-0">
                                                            <div class="truncate font-bold text-slate-800">{{ $borrow['item_name'] ?? 'Unknown item' }}</div>
                                                            <div class="due-date">Quantity: {{ $borrow['quantity'] ?? 0 }} · Due {{ \Carbon\Carbon::parse($borrow['expected_return_at'])->format('M d, Y') }}</div>
                                                        </div>
                                                        <span class="due-status {{ $statusClass }}">{{ $statusText }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="muted">No active borrowed items with due dates.</div>
                                        @endif
                                    </div>
                                </section>
                            @endif
                            @if(array_key_exists('pending_teacher_requests', $summary))
                                <section class="dashboard-pending-requests">
                                    <div class="dashboard-panel border-l-4 border-slate-900 p-4 h-full">
                                        <div class="flex items-center justify-between gap-2 mb-3">
                                            <div>
                                                <p class="dashboard-panel-title">Approval queue</p>
                                                <h2 class="mt-1 text-lg font-bold text-slate-800">Pending requests</h2>
                                            </div>
                                            <a href="{{ route('inventory.issues.list') }}" class="text-xs font-bold text-teal-700 hover:text-teal-900">View all</a>
                                        </div>
                                        @if(!empty($summary['pending_teacher_requests']))
                                            <div class="grid gap-2">
                                            @foreach($summary['pending_teacher_requests'] as $request)
                                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-2">
                                                    <div class="truncate text-sm font-bold text-slate-800">{{ data_get($request, 'item.name', 'Unknown item') }}</div>
                                                    <div class="mt-1 flex items-center justify-between gap-2 text-xs text-slate-500">
                                                        <span class="truncate">{{ data_get($request, 'issued_to.name', 'Unknown teacher') }}</span>
                                                        <span>Qty {{ data_get($request, 'quantity', 0) }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                            </div>
                                        @else
                                            <div class="muted text-sm">No pending teacher requests.</div>
                                        @endif
                                    </div>
                                </section>
                            @endif
                        </section>
                        <section class="dashboard-low-stock">
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div>
                                    <p class="dashboard-panel-title">Attention needed</p>
                                    <h2 class="mt-1 text-xl font-bold text-slate-800">Low stock items</h2>
                                </div>
                                <a href="{{ route('inventory.low_stock') }}" class="text-sm font-bold text-teal-700 hover:text-teal-900">View all</a>
                            </div>
                            <div class="card">
                                @php
                                    $lowStockPage = max(1, (int) request()->query('page', 1));
                                    $lowStockItems = $summary['low_stock_items'] ?? [];
                                    $lowStockPagination = $summary['low_stock_pagination'] ?? null;
                                    $hasLowStock = !empty($lowStockItems);
                                @endphp
                                @if($hasLowStock)
                                    <table class="low-table">
                                        <thead>
                                            <tr>
                                                <th class="text-left">Item</th>
                                                <th class="text-right">Qty</th>
                                                <th class="text-right" style="width:180px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($lowStockItems as $it)
                                            <tr>
                                                <td>{{ $it['name'] ?? ('#'.$it['id']) }}</td>
                                                <td class="text-right">{{ $it['quantity'] ?? '0' }}</td>
                                                <td class="text-right"><a class="dashboard-action" href="{{ route('inventory.items.list') }}?low_stock=1#item-{{ $it['id'] }}">View item</a></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>

                                    @if($lowStockPagination)
                                        <div class="low-stock-pagination">
                                            <div class="page-meta">
                                                Showing {{ count($lowStockItems) }} of {{ (int) $lowStockPagination['total'] }} items
                                            </div>
                                            <div class="page-actions">
                                                @php
                                                    $prevPage = max(1, $lowStockPage - 1);
                                                    $nextPage = min((int) $lowStockPagination['last_page'], $lowStockPage + 1);
                                                @endphp
                                                <a class="low-stock-page-btn" href="{{ route('inventory.dashboard', ['page' => $prevPage]) }}" {{ $lowStockPage <= 1 ? 'disabled' : '' }}>Previous</a>
                                                <a class="low-stock-page-btn" href="{{ route('inventory.dashboard', ['page' => $nextPage]) }}" {{ !($lowStockPagination['has_more_pages'] ?? false) ? 'disabled' : '' }}>Next</a>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <div class="muted">No items currently below threshold.</div>
                                @endif
                            </div>
                        </section>

                        <section class="dashboard-actions">
                            <p class="dashboard-panel-title mb-2">Quick access</p>
                            <h2 class="mb-3 text-xl font-bold text-slate-800">Common actions</h2>
                            <div class="flex flex-wrap gap-2">
                                <a class="dashboard-action" href="{{ route('inventory.items.list') }}">View items</a>
                                <a class="dashboard-action" href="{{ route('inventory.transactions.list') }}">Transactions</a>
                                <a class="dashboard-action" href="{{ route('inventory.issues.list') }}">Issues</a>
                                <a class="dashboard-action secondary" href="/inventory/items/export">Export CSV</a>
                                <a class="dashboard-action secondary" href="/inventory/items/export?format=pdf">Export PDF</a>
                            </div>
                        </section>

                        <section class="dashboard-realtime">
                            <div class="realtime-shell">
                                <div class="realtime-header">
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <span class="live-dot"></span>
                                        <h2 style="margin:0">Realtime</h2>
                                    </div>
                                    <span id="lastUpdated" class="muted">Loading...</span>
                                </div>

                                <div class="realtime-panel-grid">
                                    <div class="panel-box">
                                        <h3>Latest Activity</h3>
                                        <div id="realtimeActivity" class="activity-list">
                                            <div class="empty-state">Loading recent activity...</div>
                                        </div>
                                    </div>
                                    <div class="panel-box">
                                        <h3>Low Stock</h3>
                                        <ul id="lowStockList" class="mini-list">
                                            <li class="muted">Loading...</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="dashboard-charts">
                            <div class="panel-box">
                                <h3>Inventory Status</h3>
                                <canvas id="inventoryStatusChart" height="180"></canvas>
                            </div>
                            <div class="panel-box">
                                <h3>Department Usage</h3>
                                <canvas id="departmentUsageChart" height="180"></canvas>
                                <p id="departmentUsageEmpty" class="muted" style="display:none">No department usage data yet.</p>
                            </div>
                        </section>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
    let inventoryStatusChart;
    let departmentUsageChart;

    function renderCharts(summary, departments){
        if (!window.Chart) return;
        const statusCounts = [
            Number(summary.total_items || 0) - Number(summary.low_stock_count || 0),
            Number(summary.low_stock_count || 0)
        ];
        inventoryStatusChart?.destroy();
        inventoryStatusChart = new Chart(document.getElementById('inventoryStatusChart'), {
            type: 'doughnut',
            data: { labels: ['Healthy stock', 'Low stock'], datasets: [{ data: statusCounts, backgroundColor: ['#2f78b7', '#d89b24'], borderWidth: 0 }] },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        const rows = departments || [];
        document.getElementById('departmentUsageEmpty').style.display = rows.length ? 'none' : 'block';
        departmentUsageChart?.destroy();
        departmentUsageChart = new Chart(document.getElementById('departmentUsageChart'), {
            type: 'bar',
            data: { labels: rows.map(row => row.label), datasets: [{ label: 'Units used', data: rows.map(row => row.total_quantity), backgroundColor: '#174a73' }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    }

    function formatUpdated(value){
        if(!value) return 'Loading...';
        const d = new Date(value);
        if(Number.isNaN(d.getTime())) return value;
        return d.toLocaleString();
    }

    function renderActivity(transactions){
        const container = document.getElementById('realtimeActivity');
        if(!Array.isArray(transactions) || transactions.length === 0){
            container.innerHTML = '<div class="empty-state">No recent activity yet.</div>';
            return;
        }

        container.innerHTML = transactions.map(tx => {
            const type = (tx.transaction_type || 'update').toLowerCase();
            const typeLabel = type === 'issue' ? 'Issued' : type === 'receive' ? 'Received' : type === 'adjustment' ? 'Adjusted' : 'Updated';
            const badgeClass = type === 'issue' ? 'out' : type === 'receive' ? 'in' : 'adjust';
            const itemName = tx.item && tx.item.name ? tx.item.name : (tx.item_id ? '#'+tx.item_id : 'Unknown item');
            const userName = tx.user && tx.user.name ? tx.user.name : 'System';
            const quantity = tx.quantity ?? 0;
            const notes = tx.notes ? tx.notes : 'No notes';

            return `
                <div class="activity-card">
                    <div class="activity-top">
                        <span class="activity-badge ${badgeClass}">${typeLabel}</span>
                        <span class="activity-time">${formatUpdated(tx.created_at)}</span>
                    </div>
                    <div class="activity-body">
                        <strong>${itemName}</strong>
                        <span>${quantity} units</span>
                    </div>
                    <div class="activity-meta">
                        <span>By ${userName}</span>
                        <span>${notes}</span>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderLowStock(items){
        const list = document.getElementById('lowStockList');
        if(!Array.isArray(items) || items.length === 0){
            list.innerHTML = '<li class="muted">No items currently below threshold.</li>';
            return;
        }

        list.innerHTML = items.map(item => `
            <li>
                <strong>${item.name || ('#' + item.id)}</strong>
                <span class="muted">Qty: ${item.quantity ?? 0}</span>
            </li>
        `).join('');
    }

    async function loadRealtime(){
        try {
            const res = await fetch('/inventory/dashboard/realtime');
            const data = await res.json();
            document.getElementById('lastUpdated').textContent = 'Updated: ' + formatUpdated(data.timestamp);
            renderActivity(data.recent_transactions || []);
            renderLowStock(data.low_stock_items || []);
            const summaryResponse = await fetch('/inventory/dashboard/summary');
            const summary = await summaryResponse.json();
            const departmentResponse = await fetch('/inventory/dashboard/department-summary');
            const departmentData = await departmentResponse.json();
            renderCharts(summary, departmentData.departments || []);
        } catch (error) {
            document.getElementById('realtimeActivity').innerHTML = '<div class="empty-state">Unable to load recent activity.</div>';
            document.getElementById('lastUpdated').textContent = 'Update failed';
        }
    }

    loadRealtime();
    setInterval(loadRealtime, 10000);
    </script>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:2000">
        <div style="background:#fff;border-radius:8px;max-width:400px;width:90%;padding:0;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.2)">
            <div style="padding:24px;border-bottom:1px solid #e5e7eb;text-align:center">
                <div style="width:60px;height:60px;background:linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#fff;font-size:24px">
                    👋
                </div>
                <h3 style="font-size:18px;font-weight:600;color:#1f2937;margin:0">Confirm Logout</h3>
            </div>
            
            <div style="padding:24px">
                <div style="background:#f3f4f6;border-radius:6px;padding:16px;margin-bottom:16px">
                    <div style="display:flex;align-items:center;gap:12px">
                        @if(Auth::user()->avatar)
                            <img src="{{ Auth::user()->avatar }}" alt="avatar" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid #e5e7eb">
                        @else
                            <div style="width:48px;height:48px;background:linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:18px">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <div style="font-weight:600;color:#1f2937">{{ Auth::user()->name }}</div>
                            <div style="font-size:12px;color:#6b7280">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                </div>
                
                <p style="color:#6b7280;font-size:14px;margin:0 0 20px 0">Are you sure you want to logout from your account?</p>
                
                <div style="display:flex;gap:8px">
                    <button type="button" onclick="closeLogoutModal()" style="flex:1;padding:10px;background:#e5e7eb;color:#1f2937;border:none;border-radius:6px;font-weight:500;cursor:pointer;transition:background 0.2s">
                        Cancel
                    </button>
                    <form id="logoutForm" method="POST" action="{{ route('logout') }}" style="flex:1">
                        @csrf
                        <button type="submit" style="width:100%;padding:10px;background:#dc2626;color:#fff;border:none;border-radius:6px;font-weight:500;cursor:pointer;transition:background 0.2s">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showLogoutModal() {
        document.getElementById('logoutModal').style.display = 'flex';
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    // Close modal when clicking outside
    document.getElementById('logoutModal').addEventListener('click', function(e) {
        if(e.target === this) {
            closeLogoutModal();
        }
    });
    </script>
</x-app-layout>
