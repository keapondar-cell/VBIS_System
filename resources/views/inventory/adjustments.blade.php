<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Adjustment Requests</title>
    <style>body{font-family:Arial,sans-serif;margin:20px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px}.btn{padding:6px 8px;border-radius:6px;background:#0b61d8;color:#fff;text-decoration:none}.btn.secondary{background:#6b7280}</style>
</head>
<body>
    <h1>Pending Adjustment Requests</h1>
    <a href="{{ route('inventory.dashboard') }}">Back to Dashboard</a>
    <div id="content">Loading...</div>

    <script>
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    async function load(){
        const res = await fetch('/inventory/adjustments/index');
        const data = await res.json();
        const list = data.data || data;
        if(!list || list.length===0){ document.getElementById('content').textContent = 'No requests.'; return; }
        let html = '<table><thead><tr><th>ID</th><th>Item</th><th>Old</th><th>New</th><th>Delta</th><th>Requester</th><th>Notes</th><th>Created</th><th>Actions</th></tr></thead><tbody>';
        list.forEach(i=>{
            html += `<tr><td>${i.id}</td><td>${i.item?i.item.name:i.item_id}</td><td>${i.old_quantity}</td><td>${i.new_quantity}</td><td>${i.delta}</td><td>${i.requester?i.requester.name:i.requested_by}</td><td>${i.notes||''}</td><td>${i.created_at}</td><td><button onclick="approve(${i.id})" class=\"btn\">Approve</button> <button onclick="reject(${i.id})" class=\"btn secondary\">Reject</button></td></tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('content').innerHTML = html;
    }

    async function approve(id){ if(!confirm('Approve adjustment #'+id+'?')) return; const res = await fetch('/inventory/adjustments/'+id+'/approve',{method:'POST',headers:{'X-CSRF-TOKEN':csrf}}); const j = await res.json(); if(res.ok) load(); else alert(j.error||JSON.stringify(j)); }
    async function reject(id){ if(!confirm('Reject adjustment #'+id+'?')) return; const res = await fetch('/inventory/adjustments/'+id+'/reject',{method:'POST',headers:{'X-CSRF-TOKEN':csrf}}); const j = await res.json(); if(res.ok) load(); else alert(j.error||JSON.stringify(j)); }

    load();
    </script>
</body>
</html>
