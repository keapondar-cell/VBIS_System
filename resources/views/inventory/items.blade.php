<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Inventory Items</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; font-family: Arial, sans-serif; }
        .legacy-module-page { background: radial-gradient(circle at 100% 0, rgba(47,154,145,.12), transparent 28rem), linear-gradient(135deg, #f4f8f8 0%, #e8f1f2 100%); }
        .legacy-module-page > h1 { position: relative; display: inline-block; margin-bottom: 1.25rem; letter-spacing: .01em; }
        .legacy-module-page > h1::after { content: ""; display: block; width: 3.5rem; height: 4px; margin-top: .45rem; border-radius: 4px; background: #d99b2b; }
        .legacy-module-page > a { display: inline-block; margin-bottom: 1rem; padding: .45rem .75rem; border-radius: 7px; color: #287f92; background: rgba(255,255,255,.7); text-decoration: none; transition: background .2s, color .2s; }
        .legacy-module-page > a:hover { color: #123b5d; background: #fff; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th, td { border: 1px solid #d8e5e8; padding: 10px 12px; }
        th { background: #dcefed; }
        tbody tr { background: rgba(255,255,255,.82); transition: background .2s; }
        tbody tr:hover { background: #fff; }
        .qr-btn { background: #0d67d8; color: #fff; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; }
        .qr-btn:hover { background: #0958bb; }
        .legacy-module-page>button,.legacy-module-page td:last-child button { background:#0d67d8 !important; color:#fff !important; font-size:.82rem; line-height:1.2; white-space:nowrap; padding:8px 10px; }
        .legacy-module-page>button:hover,.legacy-module-page td:last-child button:hover { background:#0958bb !important; }
        .legacy-module-page td button.qr-btn { background:#0d67d8 !important; color:#fff !important; }
        .legacy-module-page td button.qr-btn:hover { background:#0958bb !important; }
        .legacy-module-page td:last-child { white-space: nowrap; }
        .legacy-module-page td:last-child button+button { margin-left: 4px; }
        .items-search { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin:0 0 16px; }
        .items-search input { min-width:280px; flex:1; max-width:560px; padding:9px 12px; border:1px solid #b9d0d5; border-radius:7px; background:#fff; }
        .items-search input:focus { outline:none; border-color:#287f92; box-shadow:0 0 0 3px rgba(47,154,145,.16); }
        .items-search button, .items-search a { display:inline-flex; align-items:center; gap:6px; padding:9px 13px; border:0; border-radius:7px; color:#fff; background:#0d67d8; text-decoration:none; cursor:pointer; }
        .items-search a { background:#64748b; }
        .pagination { display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-top:12px; }
        .pagination a, .pagination span { display:inline-flex; align-items:center; justify-content:center; min-width:32px; height:32px; padding:0 10px; border-radius:6px; border:1px solid #d8e5e8; background:#fff; color:#123b5d; text-decoration:none; font-size:12px; }
        .pagination a:hover { background:#edf6f5; }
        .pagination .active { background:#123b5d; border-color:#123b5d; color:#fff; }
        .pagination .disabled { opacity:.45; pointer-events:none; }
        #qrModal { display:none; position:fixed; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; z-index:1000; }
        .qr-modal-content { background:#fff; padding:20px; border-radius:8px; text-align:center; max-width:400px; }
        .qr-modal-content img { max-width:300px; height:auto; margin:15px 0; }
        .qr-modal-close { background:#6c757d; color:#fff; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; margin-top:15px; }
        @media (max-width: 700px) { .legacy-module-page { padding: 1rem; overflow-x: auto; } table { min-width: 760px; } }
    </style>
</head>
<body class="school-page min-h-screen lg:flex">
    @include('layouts.navigation')

    <main class="school-content flex-1 min-w-0">
        <div class="legacy-module-page">
            <h1>Inventory Items</h1>
            @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian()))
                <button id="newItem" style="margin-left:12px"><span aria-hidden="true">＋</span> Add New Item</button>
            @endif
            <form method="GET" action="{{ route('inventory.items.list') }}" class="items-search">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by item name, SKU, category, or location" aria-label="Search inventory items">
                <button type="submit"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>Search</button>
                @if(request('search'))<a href="{{ route('inventory.items.list') }}">Clear</a>@endif
            </form>
            <table>
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Category</th><th>Qty</th><th>Location</th><th>Status</th><th>QR</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->category ?? 'N/A' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->location }}</td>
                        @php
                            $itemStatus = strtolower((string) ($item->status ?? 'available'));
                            $statusClass = $item->quantity <= 0 || in_array($itemStatus, ['out_of_stock', 'out of stock'], true) ? 'background:#fee2e2;color:#991b1b' : ($item->quantity <= 5 || $itemStatus === 'low stock' ? 'background:#fef3c7;color:#92400e' : 'background:#dcfce7;color:#166534');
                        @endphp
                        <td><span style="{{ $statusClass }};padding:3px 8px;border-radius:999px;font-size:12px;font-weight:700">{{ $item->quantity <= 0 ? 'Out of stock' : ($item->quantity <= 5 ? 'Low stock' : 'Available') }}</span></td>
                        <td><button class="qr-btn" onclick="generateQR({{ $item->id }}, '{{ addslashes($item->name) }}')">Generate QR</button></td>
                        @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian()))
                        <td>
                            <button onclick="editItem({{ $item->id }}, '{{ addslashes($item->name) }}', {{ (int)$item->quantity }}, '{{ addslashes($item->location) }}')">Edit</button>
                            <button onclick="deleteItem({{ $item->id }})">Delete</button>
                        </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>

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
        <div class="qr-modal-content" style="background:#fff;padding:20px;border-radius:8px;text-align:center;max-width:400px">
            <h3>QR Code</h3>
            <img src="" alt="QR" style="max-width:300px;height:auto;margin:15px 0">
            <p style="font-size:12px;color:#666">Item ID: </p>
            <button class="qr-modal-close" style="background:#6c757d;color:#fff;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;margin-top:15px" onclick="document.getElementById('qrModal').style.display='none'">Close</button>
        </div>
    </div>

    <div id="itemModal" style="display:none;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.4);align-items:center;justify-content:center;z-index:999">
        <div style="background:#fff;padding:20px;max-width:600px;margin:40px auto;border-radius:6px;max-height:90vh;overflow-y:auto">
            <h3 id="itemModalTitle">New Item</h3>
            <form id="itemForm">
                <input type="hidden" id="item_id_field">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div><label style="display:block;margin-bottom:4px"><strong>Name</strong> <input id="i_name" required style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px"></label></div>
                    <div><label style="display:block;margin-bottom:4px"><strong>SKU</strong> <input id="i_sku" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px"></label></div>
                    <div><label style="display:block;margin-bottom:4px"><strong>Category</strong> <input id="i_category" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px"></label></div>
                    <div><label style="display:block;margin-bottom:4px"><strong>Quantity</strong> <input id="i_quantity" type="number" min="0" value="0" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px"></label></div>
                    <div><label style="display:block;margin-bottom:4px"><strong>Location</strong> <input id="i_location" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px"></label></div>
                    <div><label style="display:block;margin-bottom:4px"><strong>Status</strong> <select id="i_status" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px"><option value="available">Available</option><option value="out_of_stock">Out of Stock</option><option value="maintenance">Maintenance</option></select></label></div>
                </div>
                <div><label style="display:block;margin-bottom:4px;margin-top:12px"><strong>Description</strong> <textarea id="i_description" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px;min-height:80px"></textarea></label></div>
                <div style="margin-top:12px;display:flex;gap:8px"><button type="submit" style="padding:8px 16px;background:#007bff;color:#fff;border:none;border-radius:4px;cursor:pointer">Save</button> <button type="button" id="cancelItem" style="padding:8px 16px;background:#6c757d;color:#fff;border:none;border-radius:4px;cursor:pointer">Cancel</button></div>
                <div id="itemMessage" style="color:#b45309;margin-top:8px"></div>
            </form>
        </div>
    </div>

    <script>
    const csrf = document.querySelector('meta[name="csrf-token"]')?document.querySelector('meta[name="csrf-token"]').getAttribute('content'):null;
    const newItemButton = document.getElementById('newItem');
    if (newItemButton) {
        newItemButton.addEventListener('click', ()=>{
            document.getElementById('itemModal').style.display='flex';
            document.getElementById('itemModalTitle').textContent='New Item';
            document.getElementById('item_id_field').value='';
            ['i_name','i_sku','i_category','i_quantity','i_location','i_description','i_status','itemMessage'].forEach(id=>{ const el=document.getElementById(id); if(el) {el.value=''; el.textContent='';} });
            document.getElementById('i_quantity').value='0';
            document.getElementById('i_status').value='available';
        });
    }
    document.getElementById('cancelItem').addEventListener('click', ()=>{ document.getElementById('itemModal').style.display='none'; });

    document.getElementById('itemForm').addEventListener('submit', async (e)=>{
        e.preventDefault();
        const id = document.getElementById('item_id_field').value;
        const payload = { 
            name: document.getElementById('i_name').value, 
            sku: document.getElementById('i_sku').value || null,
            category: document.getElementById('i_category').value || null,
            quantity: Number(document.getElementById('i_quantity').value||0), 
            location: document.getElementById('i_location').value,
            description: document.getElementById('i_description').value || null,
            status: document.getElementById('i_status').value || 'available'
        };
        const method = id? 'PUT' : 'POST';
        const url = id? ('/inventory/items/'+id) : '/inventory/items';
        const res = await fetch(url, { method, headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}, body: JSON.stringify(payload) });
        if(res.ok){ document.getElementById('itemMessage').textContent='Saved.'; location.reload(); } else { const j = await res.json().catch(()=>({error:'error'})); document.getElementById('itemMessage').textContent = j.error||JSON.stringify(j); }
    });

    function editItem(id,name,qty,loc){
        document.getElementById('itemModal').style.display='flex';
        document.getElementById('itemModalTitle').textContent='Edit Item '+id;
        document.getElementById('item_id_field').value = id;
        document.getElementById('i_name').value = name;
        document.getElementById('i_quantity').value = qty;
        document.getElementById('i_location').value = loc;
        // Load full item data
        fetch('/inventory/items/'+id, {headers:{'Accept':'application/json'}})
            .then(r=>r.json())
            .then(item=>{
                document.getElementById('i_sku').value = item.sku || '';
                document.getElementById('i_category').value = item.category || '';
                document.getElementById('i_description').value = item.description || '';
                document.getElementById('i_status').value = item.status || 'available';
            });
    }

    async function deleteItem(id){
        if(!confirm('Delete item #'+id+'?')) return;
        const res = await fetch('/inventory/items/'+id, { method: 'DELETE', headers: {'X-CSRF-TOKEN': csrf} });
        if(res.ok){ location.reload(); } else { alert('Delete failed'); }
    }

    async function generateQR(id, itemName) {
        const imageUrl = '/inventory/items/'+id+'/qr-image';
        try {
            const res = await fetch(imageUrl, {headers: {'Accept': 'image/svg+xml'}});
            if (!res.ok) throw new Error('QR request failed');

            const modal = document.getElementById('qrModal');
            const modalContent = document.querySelector('.qr-modal-content');
            if(!modal) {
                const newModal = document.createElement('div');
                newModal.id = 'qrModal';
                newModal.style.cssText = 'display:flex;position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;z-index:1000';
                newModal.innerHTML = '<div class="qr-modal-content" style="background:#fff;padding:20px;border-radius:8px;text-align:center;max-width:400px"><h3>QR Code for ' + itemName + '</h3><img src="' + imageUrl + '" alt="QR" style="max-width:300px;height:auto;margin:15px 0"><p style="font-size:12px;color:#666">Item ID: ' + id + '</p><button class="qr-modal-close" style="background:#6c757d;color:#fff;border:none;padding:8px 16px;border-radius:4px;cursor:pointer;margin-top:15px" onclick="document.getElementById(\'qrModal\').style.display=\'none\'">Close</button></div>';
                document.body.appendChild(newModal);
            } else {
                const img = modal.querySelector('img');
                const heading = modal.querySelector('h3');
                const idText = modal.querySelector('p');
                if(img) img.src = imageUrl;
                if(heading) heading.textContent = 'QR Code for ' + itemName;
                if(idText) idText.textContent = 'Item ID: ' + id;
                modal.style.display = 'flex';
            }
            document.getElementById('qrModal').style.display = 'flex';
        } catch (error) {
            alert('Unable to generate QR code. Please try again.');
        }
    }
    </script>
        </div>
    </main>
</body>
</html>
