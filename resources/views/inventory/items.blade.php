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
        .legacy-module-page td:last-child button.delete-item-btn { background:#dc2626 !important; }
        .legacy-module-page td:last-child button.delete-item-btn:hover { background:#b91c1c !important; }
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
            @if(auth()->user() && (auth()->user()->isTeacher() || auth()->user()->isAdmin()))
                <a href="{{ route('inventory.qr.scan', ['action' => 'borrow']) }}" class="qr-btn" style="display:inline-block;margin:0 0 1rem 8px;text-decoration:none">Scan QR to Borrow</a>
                <a href="{{ route('inventory.qr.scan', ['action' => 'return']) }}" class="qr-btn" style="display:inline-block;margin:0 0 1rem 8px;text-decoration:none">Scan QR to Return</a>
            @elseif(auth()->user() && auth()->user()->isPropertyCustodian())
                <a href="{{ route('inventory.qr.scan', ['action' => 'return']) }}" class="qr-btn" style="display:inline-block;margin:0 0 1rem 8px;text-decoration:none">Scan QR to Return</a>
            @endif
            <form method="GET" action="{{ route('inventory.items.list') }}" class="items-search">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by item name, SKU, classification, or department" aria-label="Search inventory items">
                <button type="submit"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>Search</button>
                @if(request('search'))<a href="{{ route('inventory.items.list') }}">Clear</a>@endif
            </form>
            <table>
                <thead>
                    <tr><th>Name</th><th>Classification</th><th>Qty</th><th>Department</th><th>Status</th><th>Date Added</th><th>QR</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->classification ?? $item->category ?? 'N/A' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->location }}</td>
                        @php
                            $itemStatus = strtolower((string) ($item->status ?? 'available'));
                            $statusClass = $item->quantity <= 0 || in_array($itemStatus, ['out_of_stock', 'out of stock'], true) ? 'background:#fee2e2;color:#991b1b' : ($item->quantity <= 5 || $itemStatus === 'low stock' ? 'background:#fef3c7;color:#92400e' : 'background:#dcfce7;color:#166534');
                        @endphp
                        <td><span style="{{ $statusClass }};padding:3px 8px;border-radius:999px;font-size:12px;font-weight:700">{{ $item->quantity <= 0 ? 'Out of stock' : ($item->quantity <= 5 ? 'Low stock' : 'Available') }}</span></td>
                        <td>{{ $item->created_at?->timezone('Asia/Manila')->format('M d, Y h:i A') ?? 'N/A' }}</td>
                        <td><button class="qr-btn" onclick="generateQR({{ $item->id }}, '{{ addslashes($item->name) }}')">Generate QR</button></td>
                        @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian()))
                        <td>
                            <button onclick="editItem({{ $item->id }}, '{{ addslashes($item->name) }}', {{ (int)$item->quantity }}, '{{ addslashes($item->location) }}')">Edit</button>
                            <button class="delete-item-btn" onclick="deleteItem({{ $item->id }})">Delete</button>
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
        <div class="qr-modal-content" style="background:#fff;padding:20px;border-radius:8px;text-align:center;max-width:760px;max-height:90vh;overflow-y:auto">
            <h3>Item QR Codes</h3>
            <div class="qr-options" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin:15px 0"></div>
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
                    <div><label style="display:block;margin-bottom:4px"><strong>Classification</strong> <input id="i_classification" list="classificationOptions" placeholder="Consumable, Glassware, etc." style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px"></label><datalist id="classificationOptions"><option value="Consumable"><option value="Glassware"><option value="Chemical"><option value="Equipment"><option value="Office Supply"></datalist></div>
                    <div><label style="display:block;margin-bottom:4px"><strong>Quantity</strong> <input id="i_quantity" type="number" min="0" value="0" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px"></label></div>
                    <div id="itemCreatedAtField"><label style="display:block;margin-bottom:4px"><strong>Date and Time Added</strong> <input id="i_created_at" type="datetime-local" value="{{ now('Asia/Manila')->format('Y-m-d\TH:i') }}" required style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px"></label></div>
                    <div><label style="display:block;margin-bottom:4px"><strong>Department</strong> <select id="i_location" style="width:100%;padding:6px;border:1px solid #ddd;border-radius:4px"><option value="">Select department</option><option value="Library">Library</option><option value="Computer Lab">Computer Lab</option><option value="Science Lab">Science Lab</option><option value="JHS Department">JHS Department</option><option value="SHS Department">SHS Department</option></select></label></div>
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

    function editItem(id,name,qty,loc){
        document.getElementById('itemModal').style.display='flex';
        document.getElementById('itemModalTitle').textContent='Edit Item';
        document.getElementById('item_id_field').value = id;
        document.getElementById('itemCreatedAtField').style.display='none';
        document.getElementById('i_created_at').disabled=true;
        document.getElementById('i_name').value = name;
        document.getElementById('i_quantity').value = qty;
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
        const qrTypes = [
            {label: 'Item Info', action: null},
            {label: 'Scan to Borrow', action: 'borrow'},
            {label: 'Scan to Return', action: 'return'}
        ];
        options.replaceChildren();

        qrTypes.forEach(({label, action}) => {
            const card = document.createElement('a');
            const imageUrl = '/inventory/items/'+id+'/qr-image'+(action ? '?action='+encodeURIComponent(action) : '');
            card.href = imageUrl;
            card.target = '_blank';
            card.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:8px;padding:12px;border:1px solid #d8e5e8;border-radius:8px;color:#123b5d;text-decoration:none';

            const image = document.createElement('img');
            image.src = imageUrl;
            image.alt = label+' QR code for '+itemName;
            image.style.cssText = 'width:180px;max-width:100%;height:auto';

            const title = document.createElement('strong');
            title.textContent = label;
            card.append(image, title);
            options.appendChild(card);
        });

        try {
            const imageUrls = qrTypes.map(({action}) => '/inventory/items/'+id+'/qr-image'+(action ? '?action='+encodeURIComponent(action) : ''));
            const responses = await Promise.all(imageUrls.map(url => fetch(url, {headers: {'Accept': 'image/svg+xml'}})));
            if (responses.some(response => !response.ok)) throw new Error('QR request failed');

            qrModal.querySelector('h3').textContent = 'QR Codes for '+itemName;
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
