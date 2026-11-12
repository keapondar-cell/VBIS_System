<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ !empty($consumablesOnly) ? 'Consumables Inventory' : 'Inventory Items' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; font-family: Arial, sans-serif; }
        .legacy-module-page { background: radial-gradient(circle at 100% 0, rgba(47,154,145,.12), transparent 28rem), linear-gradient(135deg, #f4f8f8 0%, #e8f1f2 100%); }
        .inventory-toolbar { margin-bottom:18px; padding:22px; border:1px solid #dce7eb; border-radius:16px; background:rgba(255,255,255,.92); box-shadow:0 10px 28px rgba(18,59,93,.06); }
        .inventory-page-heading { display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .inventory-page-heading h1 { margin:0; color:#123b5d; font-size:1.65rem; line-height:1.2; font-weight:750; letter-spacing:-.02em; }
        .inventory-page-description { margin:6px 0 0; color:#64748b; font-size:.9rem; }
        .legacy-module-page > a { display: inline-block; margin-bottom: 1rem; padding: .45rem .75rem; border-radius: 7px; color: #287f92; background: rgba(255,255,255,.7); text-decoration: none; transition: background .2s, color .2s; }
        .legacy-module-page > a:hover { color: #123b5d; background: #fff; }
        .items-table-wrap { overflow-x:auto; border:1px solid #d8e5e8; border-radius:14px; background:rgba(255,255,255,.9); box-shadow:0 16px 40px rgba(18,59,93,.08); }
        table.items-table { width:100%; min-width:1050px; border:0; border-collapse:collapse; border-spacing:0; }
        .items-table th { padding:13px 14px; border:0; border-bottom:1px solid #cfe2e3; background:#e8f4f3; color:#526b7a; font-size:11px; font-weight:700; letter-spacing:.07em; text-align:left; text-transform:uppercase; white-space:nowrap; }
        .items-table td { padding:14px; border:0; border-bottom:1px solid #edf1f3; color:#334b5d; font-size:13px; line-height:1.45; vertical-align:middle; }
        .items-table tbody tr { background:rgba(255,255,255,.82); transition:background .2s; }
        .items-table tbody tr:nth-child(even) { background:#f8fbfb; }
        .items-table tbody tr:hover { background:#eff8f7; }
        .items-table tbody tr:last-child td { border-bottom:0; }
        .items-table td:first-child { color:#123b5d; font-weight:700; }
        .items-table td:nth-child(3) { text-align:center; font-variant-numeric:tabular-nums; }
        .items-table td:nth-child(7),.items-table td:nth-child(8) { white-space:nowrap; }
        .item-actions { display:flex; align-items:center; gap:6px; }
        .item-action-btn { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; padding:0; border:0; border-radius:50%; color:#fff; cursor:pointer; box-shadow:0 3px 8px rgba(18,59,93,.16); transition:transform .15s,background .15s; }
        .item-action-btn:hover { transform:translateY(-1px); }
        .item-action-btn:focus-visible { outline:3px solid #93c5fd; outline-offset:3px; }
        .item-action-btn svg { width:18px; height:18px; fill:none; stroke:currentColor; stroke-width:2; stroke-linecap:round; stroke-linejoin:round; }
        .qr-btn { width:72px; border-radius:12px; background:#2563eb; }
        .qr-btn:hover { background:#1d4ed8; }
        .edit-item-btn { background:#2563eb !important; }
        .edit-item-btn:hover { background:#1d4ed8 !important; }
        .delete-item-btn { background:#dc2626 !important; }
        .delete-item-btn:hover { background:#b91c1c !important; }
        .legacy-module-page>button,.legacy-module-page td:last-child button { background:#0d67d8 !important; color:#fff !important; font-size:.82rem; line-height:1.2; white-space:nowrap; padding:8px 10px; }
        .legacy-module-page>button:hover,.legacy-module-page td:last-child button:hover { background:#0958bb !important; }
        .legacy-module-page td button.qr-btn { background:#2563eb !important; color:#fff !important; }
        .legacy-module-page td button.qr-btn:hover { background:#1d4ed8 !important; }
        .legacy-module-page td button.item-action-btn.qr-btn { width:72px; height:36px; padding:0; border-radius:12px; }
        .legacy-module-page td:last-child button.item-action-btn { width:36px; height:36px; padding:0; border-radius:50%; }
        .legacy-module-page td:last-child button.edit-item-btn { background:#2563eb !important; }
        .legacy-module-page td:last-child button.edit-item-btn:hover { background:#1d4ed8 !important; }
        .legacy-module-page td:last-child button.delete-item-btn { background:#dc2626 !important; }
        .legacy-module-page td:last-child button.delete-item-btn:hover { background:#b91c1c !important; }
        .legacy-module-page td:last-child { white-space: nowrap; }
        .inventory-add-button { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:42px; margin:0 !important; padding:9px 16px !important; border:0; border-radius:9px; color:#fff; background:#0d67d8; font-size:.9rem; font-weight:700; white-space:nowrap; box-shadow:0 4px 10px rgba(13,103,216,.16); }
        .inventory-add-button:hover { background:#0958bb !important; }
        .inventory-tabs { display:flex; gap:5px; width:max-content; max-width:100%; margin-top:20px; padding:4px; overflow-x:auto; border:1px solid #e2e8f0; border-radius:11px; background:#f3f7f8; }
        .inventory-tab { display:inline-flex; align-items:center; justify-content:center; min-height:36px; padding:8px 13px; border-radius:8px; color:#526b7a; font-size:13px; font-weight:650; text-decoration:none; white-space:nowrap; }
        .inventory-tab:hover { color:#123b5d; background:#e8f4f3; }
        .inventory-tab[aria-current="page"] { color:#123b5d; background:#fff; box-shadow:0 1px 3px rgba(15,23,42,.12); }
        .usage-identifier-note { margin:14px 0 0; padding:10px 12px; border-left:3px solid #287f92; border-radius:6px; background:#f0f8f7; color:#526b7a; font-size:.85rem; }
        .items-search-panel { width:100%; min-width:0; box-sizing:border-box; margin-bottom:18px; padding:16px; border:1px solid #dce7eb; border-radius:14px; background:rgba(255,255,255,.9); }
        .items-search { display:grid; width:100%; min-width:0; box-sizing:border-box; grid-template-columns:minmax(0,2fr) repeat(4,minmax(0,1fr)) auto; gap:10px; align-items:center; margin:0; }
        .items-search input, .items-search select { width:100%; min-width:0; max-width:100%; min-height:42px; box-sizing:border-box; padding:9px 11px; border:1px solid #cbd9df; border-radius:8px; background:#fff; color:#263b4d; font-size:.88rem; }
        .items-search input::placeholder { color:#82909d; }
        .items-search input:focus, .items-search select:focus { outline:none; border-color:#287f92; box-shadow:0 0 0 3px rgba(47,154,145,.14); }
        .items-search > span { display:inline-flex; align-items:center; min-height:40px; padding:0 11px; border-radius:8px; background:#ecfdf5; color:#047857; font-size:.82rem; font-weight:650; white-space:nowrap; }
        .items-search-actions { display:flex; min-width:0; align-items:center; gap:8px; }
        .items-search button, .items-search a { display:inline-flex; align-items:center; justify-content:center; gap:6px; min-height:42px; padding:9px 13px; border:0; border-radius:8px; color:#fff; background:#0d67d8; font-size:.88rem; font-weight:650; text-decoration:none; cursor:pointer; white-space:nowrap; }
        .items-search button:hover { background:#0958bb; }
        .items-search a { border:1px solid #cbd9df; color:#526b7a; background:#fff; }
        .items-search a:hover { background:#f3f7f8; }
        .pagination { display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-top:12px; }
        .pagination a, .pagination span { display:inline-flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 10px; border-radius:6px; border:1px solid #d8e5e8; background:#fff; color:#123b5d; text-decoration:none; font-size:12px; }
        .pagination a:hover { background:#edf6f5; }
        .pagination .active { background:#123b5d; border-color:#123b5d; color:#fff; }
        .pagination .disabled { opacity:.45; pointer-events:none; }
        #qrModal { display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000; }
        .qr-modal-content { background:#fff; padding:20px; border-radius:8px; text-align:center; max-width:400px; }
        .qr-modal-content img { max-width:300px; height:auto; margin:15px 0; }
        .qr-modal-close { background:#6c757d; color:#fff; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; margin-top:15px; }
        .item-modal-backdrop {
            display:none;
            position:fixed;
            inset:0;
            background:rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(2px);
            align-items:center;
            justify-content:center;
            z-index:999;
            padding:24px;
        }
        .item-modal-panel {
            width:min(100%, 920px);
            background:#f3f5f7;
            border:1px solid #dfe6eb;
            border-radius:18px;
            box-shadow:0 20px 40px rgba(15, 23, 42, 0.12);
            padding:24px 28px 20px;
            max-height:90vh;
            overflow-y:auto;
        }
        .item-modal-title {
            margin:0 0 18px;
            font-size:2.2rem;
            line-height:1.1;
            font-weight:700;
            color:#1d2a36;
        }
        .item-form {
            display:flex;
            flex-direction:column;
            gap:18px;
        }
        .item-form-grid {
            display:grid;
            grid-template-columns:repeat(2, minmax(0, 1fr));
            gap:18px 20px;
        }
        .item-form-field {
            display:flex;
            flex-direction:column;
            gap:8px;
        }
        .item-form-field label {
            display:flex;
            flex-direction:column;
            gap:8px;
            font-size:1.05rem;
            font-weight:700;
            color:#1f2d3d;
        }
        .item-form-field input,
        .item-form-field select,
        .item-form-field textarea {
            width:100%;
            border:1px solid #c9d3dd;
            border-radius:12px;
            background:#f8fafc;
            color:#1f2d3d;
            padding:14px 16px;
            font-size:1.05rem;
            box-sizing:border-box;
            transition:border-color .15s ease, box-shadow .15s ease, background .15s ease;
        }
        .item-form-field input::placeholder,
        .item-form-field textarea::placeholder {
            color:#64748b;
        }
        .item-form-field input:focus,
        .item-form-field select:focus,
        .item-form-field textarea:focus {
            outline:none;
            border-color:#2b86c5;
            background:#fff;
            box-shadow:0 0 0 4px rgba(43, 134, 197, 0.12);
        }
        .item-form-field textarea {
            min-height:120px;
            resize:vertical;
        }
        .item-status-field { grid-column:1 / -1; }
        .item-form-actions {
            display:flex;
            gap:14px;
            padding-top:8px;
        }
        .item-primary-btn,
        .item-secondary-btn {
            border:none;
            border-radius:12px;
            padding:18px 30px;
            font-size:1.1rem;
            font-weight:700;
            cursor:pointer;
            transition:transform .15s ease, box-shadow .15s ease, opacity .15s ease;
        }
        .item-primary-btn {
            background:linear-gradient(180deg, #2f9cf8 0%, #1d7ed6 100%);
            color:#fff;
            box-shadow:0 10px 20px rgba(29,126,214,.2);
        }
        .item-secondary-btn {
            background:#a9b2bd;
            color:#1f2d3d;
        }
        .item-primary-btn:hover,
        .item-secondary-btn:hover {
            transform:translateY(-1px);
        }
        #itemMessage {
            min-height:1.2rem;
            font-size:.95rem;
            font-weight:600;
            color:#b45309;
        }
        @media (max-width: 1100px) {
            .items-search { grid-template-columns:repeat(3,minmax(0,1fr)); }
            .items-search input, .items-search > span, .items-search-actions { grid-column:1 / -1; }
        }
        @media (max-width: 640px) {
            .legacy-module-page { padding: 1rem .75rem; }
            .inventory-toolbar { padding:16px 14px; }
            .inventory-page-heading { align-items:center; }
            .inventory-page-heading h1 { font-size:1.35rem; }
            .inventory-page-description { font-size:.82rem; }
            .inventory-add-button { min-height:40px; padding:8px 11px !important; font-size:.82rem; }
            .inventory-tabs { width:100%; }
            .inventory-tab { flex:1; padding:8px 9px; font-size:.77rem; }
            .items-search-panel { padding:12px; }
            .items-search { grid-template-columns:minmax(0,1fr); gap:9px; }
            .items-search input, .items-search select, .items-search > span, .items-search-actions { grid-column:1; width:100%; }
            .items-search-actions > * { flex:1; }
            .items-table { min-width:1050px; }
            .item-form-grid { grid-template-columns:1fr; }
            .item-modal-panel { padding:20px 18px; }
            .item-modal-title { font-size:1.8rem; }
            .item-form-actions { flex-direction:column; }
            .item-primary-btn,
            .item-secondary-btn { width:100%; }
        }
        @media (max-width: 380px) {
            .inventory-page-heading { align-items:stretch; flex-direction:column; }
            .inventory-add-button { width:100%; }
        }
    </style>
</head>
<body class="school-page min-h-screen lg:flex">
    @include('layouts.navigation')

    <main class="school-content flex-1 min-w-0">
        <div class="legacy-module-page">
            <section class="inventory-toolbar" aria-label="Inventory navigation">
                <div class="inventory-page-heading">
                    <div>
                        <h1>{{ !empty($usageIdentifiersOnly) ? 'Item Usage Identifiers' : (!empty($consumablesOnly) ? 'Consumables Inventory' : 'Inventory Items') }}</h1>
                        <p class="inventory-page-description">{{ !empty($usageIdentifiersOnly) ? 'Review how each item is allowed to be used.' : (!empty($consumablesOnly) ? 'Track consumable supplies and their stock levels.' : 'Manage items, stock levels, and usage classifications.') }}</p>
                    </div>
                    @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian()))
                        <button id="newItem" class="inventory-add-button"><span aria-hidden="true">＋</span> Add New Item</button>
                    @endif
                </div>
                <nav class="inventory-tabs" aria-label="Inventory sections">
                    <a class="inventory-tab" href="{{ route('inventory.items.list') }}" @if(empty($consumablesOnly) && empty($usageIdentifiersOnly)) aria-current="page" @endif>Inventory Items</a>
                    <a class="inventory-tab" href="{{ route('inventory.consumables.list') }}" @if(!empty($consumablesOnly)) aria-current="page" @endif>Consumables</a>
                    <a class="inventory-tab" href="{{ route('inventory.item_usage.list') }}" @if(!empty($usageIdentifiersOnly)) aria-current="page" @endif>Usage Identifier</a>
                </nav>
                @if(!empty($usageIdentifiersOnly))
                    <p class="usage-identifier-note">This identifier describes how an item may be used. It does not create or record an issuance or borrowing transaction.</p>
                @endif
            </section>
            <section class="items-search-panel" aria-label="Search and filter inventory">
            <form method="GET" action="{{ !empty($usageIdentifiersOnly) ? route('inventory.item_usage.list') : (!empty($consumablesOnly) ? route('inventory.consumables.list') : route('inventory.items.list')) }}" class="items-search">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by item, SKU, or classification" aria-label="Search inventory items">
                @if(!empty($consumablesOnly))
                    <span class="px-3 py-2 rounded-md bg-emerald-50 text-emerald-800">Classification: Consumable</span>
                @else
                    <select name="classification" aria-label="Filter by classification">
                        <option value="">All Classifications</option>
                        @foreach($classifications as $classification)
                            <option value="{{ $classification }}" @selected(request('classification') === $classification)>{{ $classification }}</option>
                        @endforeach
                    </select>
                @endif
                @if(!empty($usageIdentifiersOnly))
                    <select name="usage_type" aria-label="Filter by item usage">
                        <option value="">All Usage Types</option>
                        <option value="issuance_only" @selected(request('usage_type') === 'issuance_only')>Material issuance only</option>
                        <option value="borrowing_only" @selected(request('usage_type') === 'borrowing_only')>Borrowing only</option>
                        <option value="both" @selected(request('usage_type') === 'both')>Material issuance and borrowing</option>
                    </select>
                @endif
                <select name="location" aria-label="Filter by department">
                    <option value="">All Departments</option>
                    @foreach($locations as $location)
                        <option value="{{ $location }}" @selected(request('location') === $location)>{{ $location }}</option>
                    @endforeach
                </select>
                <select name="stock_status" aria-label="Filter by stock status">
                    <option value="">All Stock Levels</option>
                    <option value="available" @selected(request('stock_status') === 'available')>Available</option>
                    <option value="low_stock" @selected(request('stock_status') === 'low_stock')>Low stock</option>
                    <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>Out of stock</option>
                </select>
                <div class="items-search-actions">
                    <button type="submit"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>Search</button>
                    @if(request()->hasAny(['search', 'classification', 'usage_type', 'location', 'stock_status', 'low_stock']))<a href="{{ !empty($usageIdentifiersOnly) ? route('inventory.item_usage.list') : (!empty($consumablesOnly) ? route('inventory.consumables.list') : route('inventory.items.list')) }}">Clear</a>@endif
                </div>
            </form>
            </section>
            <div class="items-table-wrap">
            <table class="items-table" aria-label="Inventory items">
                <thead>
                    <tr><th>Name</th><th>Classification</th><th>Allowed Use</th><th>Qty</th><th>Department</th><th>Status</th><th>Date Added</th><th>QR</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr id="item-{{ $item->id }}">
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->classification ?? $item->category ?? 'N/A' }}</td>
                        <td>
                            @switch($item->usage_type)
                                @case('issuance_only') Material issuance only @break
                                @case('borrowing_only') Borrowing only @break
                                @default Material issuance and borrowing
                            @endswitch
                        </td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->location }}</td>
                        @php
                            $itemStatus = strtolower((string) ($item->status ?? 'available'));
                            $statusClass = $item->quantity <= 0 || in_array($itemStatus, ['out_of_stock', 'out of stock'], true) ? 'background:#fee2e2;color:#991b1b' : ($item->quantity <= 5 || $itemStatus === 'low stock' ? 'background:#fef3c7;color:#92400e' : 'background:#dcfce7;color:#166534');
                        @endphp
                        <td><span style="{{ $statusClass }};padding:3px 8px;border-radius:999px;font-size:12px;font-weight:700">{{ $item->quantity <= 0 ? 'Out of stock' : ($item->quantity <= 5 ? 'Low stock' : 'Available') }}</span></td>
                        <td>{{ \App\Support\SchoolTime::format($item->created_at, 'M d, Y h:i A') }}</td>
                        <td><button type="button" class="item-action-btn qr-btn" aria-label="Generate QR codes for {{ $item->name }}" title="Generate QR codes" onclick="generateQR({{ $item->id }}, '{{ addslashes($item->name) }}')"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h3v3h-3zM19 14v2M14 19h2M19 19h2v2"/></svg></button></td>
                        @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian()))
                        <td>
                            <div class="item-actions">
                                <button type="button" class="item-action-btn edit-item-btn" aria-label="Edit {{ $item->name }}" title="Edit item" onclick="editItem({{ $item->id }}, '{{ addslashes($item->name) }}', {{ (int)$item->quantity }}, '{{ addslashes($item->location) }}', @js($item->usage_type))"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg></button>
                                <button type="button" class="item-action-btn delete-item-btn" aria-label="Delete {{ $item->name }}" title="Delete item" onclick="deleteItem({{ $item->id }})"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6"/></svg></button>
                            </div>
                        </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>

            @if($items->total() > 0)
                <div class="pagination">
                    @if($items->onFirstPage())
                        <span class="disabled">Previous</span>
                    @else
                        <a href="{{ $items->previousPageUrl() }}">Previous</a>
                    @endif

                    <span class="active">Page {{ $items->currentPage() }} of {{ $items->lastPage() }}</span>

                    @if($items->hasMorePages())
                        <a href="{{ $items->nextPageUrl() }}">Next</a>
                    @else
                        <span class="disabled">Next</span>
                    @endif
                </div>
            @endif

    <div id="qrModal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:1000">
        <div class="qr-modal-content" style="background:#fff;padding:20px;border-radius:8px;text-align:center;max-width:760px;max-height:90vh;overflow-y:auto">
            <h3>Item QR Code</h3>
            <div class="qr-options" style="display:flex;justify-content:center;margin:15px 0"></div>
            <button class="qr-modal-close" style="background:#6c757d;color:#fff;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;margin-top:15px" onclick="document.getElementById('qrModal').style.display='none'">Close</button>
        </div>
    </div>

    <div id="itemModal" class="item-modal-backdrop">
        <div class="item-modal-panel">
            <h3 id="itemModalTitle" class="item-modal-title">New Item</h3>
            <form id="itemForm" class="item-form">
                <input type="hidden" id="item_id_field">
                <div class="item-form-grid">
                    <div class="item-form-field"><label><span>Name</span><input id="i_name" required></label></div>
                    <div class="item-form-field"><label><span>SKU</span><input id="i_sku"></label></div>
                    <div class="item-form-field"><label><span>Classification</span><input id="i_classification" list="classificationOptions" placeholder="Consumable, Glassware, etc."></label><datalist id="classificationOptions"><option value="Consumable"><option value="Glassware"><option value="Chemical"><option value="Equipment"><option value="Office Supply"></datalist></div>
                    <div class="item-form-field"><label><span>Allowed Use</span><select id="i_usage_type" required><option value="issuance_only">Material issuance only (no return expected)</option><option value="borrowing_only">Borrowing only (return required)</option><option value="both">Material issuance and borrowing</option></select></label></div>
                    <div class="item-form-field"><label><span>Quantity</span><input id="i_quantity" type="number" min="0" value="0"></label></div>
                    <div id="itemCreatedAtField" class="item-form-field"><label><span>Date and Time Added</span><input id="i_created_at" type="datetime-local" value="{{ now(\App\Support\SchoolTime::TIMEZONE)->format('Y-m-d\TH:i') }}" required></label></div>
                    <div class="item-form-field"><label><span>Department</span><select id="i_location"><option value="">Select department</option><option value="Library">Library</option><option value="Computer Lab">Computer Lab</option><option value="Science Lab">Science Lab</option><option value="JHS Department">JHS Department</option><option value="SHS Department">SHS Department</option></select></label></div>
                    <div class="item-form-field item-status-field"><label><span>Status</span><select id="i_status"><option value="available">Available</option><option value="out_of_stock">Out of Stock</option><option value="maintenance">Maintenance</option></select></label></div>
                </div>
                <div class="item-form-field"><label><span>Description</span><textarea id="i_description"></textarea></label></div>
                <div class="item-form-actions">
                    <button type="submit" class="item-primary-btn">Save</button>
                    <button type="button" id="cancelItem" class="item-secondary-btn">Cancel</button>
                </div>
                <div id="itemMessage"></div>
            </form>
        </div>
    </div>

    <script>
    const csrf = document.querySelector('meta[name="csrf-token"]')?document.querySelector('meta[name="csrf-token"]').getAttribute('content'):null;
    const standardDepartments = ['Library','Computer Lab','Science Lab','JHS Department','SHS Department'];

    function resetDepartmentOptions(){
        const departmentSelect = document.getElementById('i_location');
        departmentSelect.replaceChildren(new Option('Select department', ''));
        standardDepartments.forEach(name=>departmentSelect.add(new Option(name, name)));
        departmentSelect.value = '';
    }

    const newItemButton = document.getElementById('newItem');
    if (newItemButton) {
        newItemButton.addEventListener('click', ()=>{
            document.getElementById('itemModal').style.display='flex';
            document.getElementById('itemModalTitle').textContent='New Item';
            document.getElementById('item_id_field').value='';
            document.getElementById('itemCreatedAtField').style.display='';
            document.getElementById('i_created_at').disabled=false;
            ['i_name','i_sku','i_classification','i_quantity','i_description','i_status'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
            document.getElementById('i_usage_type').value='both';
            document.getElementById('itemMessage').textContent='';
            resetDepartmentOptions();
            document.getElementById('i_quantity').value='0';
            document.getElementById('i_status').value='available';
        });
    }
    document.getElementById('cancelItem').addEventListener('click', ()=>{ document.getElementById('itemModal').style.display='none'; });

    document.getElementById('itemForm').addEventListener('submit', async (e)=>{
        e.preventDefault();
        const id = document.getElementById('item_id_field').value;
        const classification = document.getElementById('i_classification').value || null;
        const payload = { 
            name: document.getElementById('i_name').value, 
            sku: document.getElementById('i_sku').value || null,
            category: classification,
            classification: classification,
            usage_type: document.getElementById('i_usage_type').value,
            quantity: Number(document.getElementById('i_quantity').value||0), 
            location: document.getElementById('i_location').value,
            description: document.getElementById('i_description').value || null,
            status: document.getElementById('i_status').value || 'available'
        };
        if(!id){
            payload.created_at = document.getElementById('i_created_at').value;
        }
        const method = id? 'PUT' : 'POST';
        const url = id? ('/inventory/items/'+id) : '/inventory/items';
        const res = await fetch(url, { method, headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body: JSON.stringify(payload) });
        const result = await res.json().catch(()=>({}));
        const message = document.getElementById('itemMessage');
        message.replaceChildren();

        if(res.status === 202){
            message.append(document.createTextNode('Quantity change submitted for approval. The current stock will remain unchanged until it is approved. '));
            const adjustmentsLink = document.createElement('a');
            adjustmentsLink.href = '/inventory/adjustments/list';
            adjustmentsLink.textContent = 'Review adjustment requests';
            message.append(adjustmentsLink);
        } else if(res.ok){
            location.reload();
        } else {
            const validationErrors = Object.values(result.errors || {}).flat().join(' ');
            message.textContent = result.error || validationErrors || result.message || 'Unable to save item.';
        }
    });

    document.getElementById('i_classification').addEventListener('change', event=>{
        const usageType = document.getElementById('i_usage_type');
        if (event.target.value.trim().toLowerCase() === 'consumable') {
            usageType.value = 'issuance_only';
        } else if (usageType.value === 'issuance_only') {
            usageType.value = 'both';
        }
    });

    function editItem(id,name,qty,loc,usageType){
        document.getElementById('itemModal').style.display='flex';
        document.getElementById('itemModalTitle').textContent='Edit Item';
        document.getElementById('item_id_field').value = id;
        document.getElementById('itemCreatedAtField').style.display='none';
        document.getElementById('i_created_at').disabled=true;
        document.getElementById('i_name').value = name;
        document.getElementById('i_quantity').value = qty;
        document.getElementById('i_usage_type').value = usageType || 'both';
        resetDepartmentOptions();
        const departmentSelect = document.getElementById('i_location');
        if (loc && !standardDepartments.includes(loc)) {
            const legacyDepartment = new Option(loc, loc);
            legacyDepartment.dataset.legacyDepartment = 'true';
            departmentSelect.add(legacyDepartment);
        }
        departmentSelect.value = loc || '';
        // Load full item data
        fetch('/inventory/items/'+id, {headers:{'Accept':'application/json'}})
            .then(r=>r.json())
            .then(item=>{
                document.getElementById('i_sku').value = item.sku || '';
                document.getElementById('i_classification').value = item.classification || item.category || '';
                document.getElementById('i_description').value = item.description || '';
                document.getElementById('i_status').value = item.status || 'available';
                document.getElementById('i_usage_type').value = item.usage_type || 'both';
            });
    }

    async function deleteItem(id){
        if(!confirm('Delete this item?')) return;
        const res = await fetch('/inventory/items/'+id, { method: 'DELETE', headers: {'X-CSRF-TOKEN': csrf} });
        if(res.ok){ location.reload(); } else { alert('Delete failed'); }
    }

    async function generateQR(id, itemName) {
        const qrModal = document.getElementById('qrModal');
        const options = qrModal.querySelector('.qr-options');
        const label = 'Scan for Item Info, Borrow or Return';
        const imageUrl = '/inventory/items/'+id+'/qr-image';
        options.replaceChildren();

        const card = document.createElement('a');
        card.href = imageUrl;
        card.target = '_blank';
        card.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:8px;padding:12px;border:1px solid #d8e5e8;border-radius:8px;color:#123b5d;text-decoration:none';

        const image = document.createElement('img');
        image.src = imageUrl;
        image.alt = label+' QR code for '+itemName;
        image.style.cssText = 'width:220px;max-width:100%;height:auto';

        const title = document.createElement('strong');
        title.textContent = label;
        card.append(image, title);
        options.appendChild(card);

        try {
            const response = await fetch(imageUrl, {headers: {'Accept': 'image/svg+xml'}});
            if (!response.ok) throw new Error('QR request failed');

            qrModal.querySelector('h3').textContent = 'QR Code for '+itemName;
            qrModal.style.display = 'flex';
        } catch (error) {
            options.replaceChildren();
            alert('Unable to generate QR code. Please try again.');
        }
    }
    </script>
        </div>
    </main>
</body>
</html>
