<x-app-layout>
    <style>
        body{font-family:Figtree,Arial,sans-serif;margin:0;background:#f4f8f8;color:#17324d}.page{max-width:1400px;margin:0 auto;padding:32px 24px}.page-head{display:flex;justify-content:space-between;align-items:end;margin-bottom:24px}.eyebrow{color:#d99b2b;font-size:12px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.page h1{margin:4px 0;font-size:30px}.page-subtitle{color:#657789;margin:0}.surface{background:#fff;border:1px solid #d8e5e8;border-radius:16px;box-shadow:0 12px 30px rgba(18,59,93,.06);padding:20px;margin-bottom:20px}.surface h3{margin:0 0 14px}.form-grid{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.form-grid input,.form-grid select,.edit-form input,.edit-form select{padding:10px;border:1px solid #cbdde1;border-radius:8px;background:#fff;color:#17324d}.form-grid input[type=text]{min-width:220px}.btn{border:0;border-radius:8px;padding:10px 14px;background:#123b5d;color:#fff;cursor:pointer;font-weight:700}.btn:hover{background:#1d687d}.btn.secondary{background:#edf6f5;color:#123b5d}.btn.warning{background:#fef3c7;color:#92400e}.btn.danger{background:#fee2e2;color:#991b1b}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;min-width:780px}th,td{padding:12px;text-align:left;border-bottom:1px solid #e5eef0}th{font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#657789;background:#f7fbfb}tr:hover{background:#fbfefe}.status{display:inline-block;padding:4px 9px;border-radius:999px;font-size:12px;font-weight:800;text-transform:capitalize}.status.pending{background:#fef3c7;color:#92400e}.status.approved{background:#dcfce7;color:#166534}.status.rejected{background:#fee2e2;color:#991b1b}.actions{display:flex;gap:6px;flex-wrap:wrap}.modal{display:none;position:fixed;inset:0;z-index:100;background:rgba(7,25,44,.65);align-items:center;justify-content:center}.modal-box{background:#fff;border-radius:14px;padding:22px;width:min(460px,90%);box-shadow:0 20px 50px rgba(0,0,0,.2)}.edit-form{display:grid;gap:12px}
        body { background: radial-gradient(circle at 100% 0, rgba(47,154,145,.12), transparent 28rem), linear-gradient(135deg, #f4f8f8 0%, #e8f1f2 100%); }
        .page { max-width: 1400px; }
        .page h1 { color: #123b5d; letter-spacing: .01em; }
        .page h1::after { content: ""; display: block; width: 3.5rem; height: 4px; margin-top: .45rem; border-radius: 4px; background: #d99b2b; }
        .page-subtitle { color: #536879; }
        .surface { border-color: #d8e5e8; box-shadow: 0 16px 40px rgba(18,59,93,.08); }
        .surface.table-wrap { padding: 22px; }
        table { border: 1px solid #d8e5e8; border-radius: 12px; overflow: hidden; }
        th { color: #123b5d; background: #dcefed; }
        tbody tr { background: rgba(255,255,255,.82); transition: background .2s; }
        tbody tr:hover { background: #fff; }
        .page-head > .btn.secondary { text-decoration: none; transition: background .2s, color .2s; }
        .page-head > .btn.secondary:hover { background: #dcefed; color: #123b5d; }
        @media (max-width: 700px) { .page { padding: 1rem; } .page-head { align-items: flex-start; gap: 1rem; flex-direction: column; } }
    </style>

    <main class="page">
        <header class="page-head">
            <div>
                <div class="eyebrow">VBIS Requests</div>
                <h1>Material Issues</h1>
                <p class="page-subtitle">Track requests, approvals, and issued materials.</p>
            </div>
        </header>

        @if(auth()->check() && auth()->user()->isTeacher())
            <div class="surface">
                <h3>Request Material</h3>
                <form id="createIssueForm" class="form-grid" style="position:relative">
                    <input id="ci_item_search" type="text" placeholder="Search item by name or SKU" autocomplete="off" style="min-width:220px;padding:6px">
                    <input type="hidden" id="ci_item" name="item_id">
                    <div id="ci_suggestions" style="position:absolute;left:8px;top:62px;background:#fff;border:1px solid #ddd;max-height:220px;overflow:auto;display:none;z-index:60;width:360px;border-radius:4px"></div>
                    <input id="ci_qty" type="number" min="1" value="1" style="width:80px;padding:6px">
                    <select id="ci_request_type" style="width:150px;padding:6px">
                        <option value="issue">Material Issue</option>
                        <option value="borrow">Borrow</option>
                    </select>
                    <input id="ci_purpose" type="text" placeholder="Purpose / reason for request" required style="min-width:260px;padding:6px">
                    <div id="ci_borrowed_date_group" style="display:none;">
                        <label for="ci_borrowed_date" style="display:block;font-size:12px;font-weight:700;color:#17324d;margin-bottom:4px;">Date Borrowed</label>
                        <input id="ci_borrowed_date" type="date" style="width:170px;padding:6px" aria-label="Date borrowed">
                    </div>
                    <div id="ci_return_date_group" style="display:none;">
                        <label for="ci_return_date" style="display:block;font-size:12px;font-weight:700;color:#17324d;margin-bottom:4px;">Return Date</label>
                        <input id="ci_return_date" type="date" style="width:170px;padding:6px" aria-label="Return date">
                    </div>
                    <input id="ci_user" type="text" placeholder="User ID or name (optional)" style="width:160px;padding:6px">
                    <select id="ci_dept" style="width:180px;padding:6px">
                        <option value="">No department</option>
                        @foreach($departments ?? [] as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn">Request</button>
                </form>
                <div id="createMsg" class="page-subtitle" style="margin-top:10px"></div>
            </div>
        @else
            <div class="surface">
                <p class="page-subtitle" style="margin:0;">Only teachers can request or borrow items.</p>
            </div>
        @endif

        <div class="surface table-wrap">
            <div id="content">Loading...</div>
        </div>
    </main>

    <div id="editIssueModal" class="modal">
        <div class="modal-box">
            <h3>Edit Pending Request</h3>
            <form id="editIssueForm" class="edit-form">
                <input type="hidden" id="edit_issue_id">
                <label>Item<input id="edit_item_id" type="number" min="1" required></label>
                <label>Quantity<input id="edit_quantity" type="number" min="1" required></label>
                <label>Purpose<input id="edit_purpose" type="text" minlength="5" required></label>
                <label>Department<select id="edit_department"><option value="">No department</option>@foreach($departments ?? [] as $department)<option value="{{ $department->id }}">{{ $department->name }}</option>@endforeach</select></label>
                <div class="actions">
                    <button type="submit" class="btn">Save changes</button>
                    <button type="button" class="btn secondary" onclick="closeEditIssue()">Cancel</button>
                </div>
                <div id="editIssueMsg" class="page-subtitle"></div>
            </form>
        </div>
    </div>

    <script>
        const canApprove = @json(auth()->user() ? (auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian()) : false);
        const currentUserId = @json(auth()->check() ? auth()->id() : null);
        const csrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : null;

        function escapeHtml(str){ return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

        async function load(){
            const res = await fetch('/inventory/issues');
            const data = await res.json();
            const list = data.data || data;
            if(!list || list.length===0){ document.getElementById('content').textContent = 'No issues.'; return; }
            let html = '<table><thead><tr><th>ID</th><th>Item</th><th>Qty</th><th>Purpose</th><th>Requested By</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody>';
            list.forEach(i=>{
                const rel = i.issuedTo || i.issued_to;
                const issuer = (rel && rel.name) ? rel.name : (i.issued_to_user_id || '');
                const avatar = (rel && rel.avatar) ? rel.avatar : '';
                const purpose = i.status === 'rejected' && i.rejection_reason ? `${i.notes || 'No purpose specified'} | Rejection: ${i.rejection_reason}` : (i.notes || 'No purpose specified');
                const itemName = i.item ? escapeHtml(i.item.name) : i.item_id;
                html += `<tr><td>${i.id}</td><td>${itemName}</td><td>${i.quantity}</td><td>${escapeHtml(purpose)}</td><td>${avatar ? `<img src="${avatar}" alt="avatar" style="width:28px;height:28px;border-radius:50%;object-fit:cover;margin-right:8px;vertical-align:middle">` : ''}${escapeHtml(issuer)}</td><td><span class="status ${i.status}">${escapeHtml(i.status)}</span></td><td>${i.created_at}</td><td class="actions">`;
                if(i.status === 'pending' && currentUserId === i.issued_to_user_id && !canApprove){ html += `<button class="btn warning" onclick="openEditIssue(${i.id},${i.item_id},${i.quantity},'${escapeHtml(purpose).replace(/'/g, "\\'")}',${i.department_id || 'null'})">Edit</button>`; }
                if(i.status==='pending'){
                    if(canApprove){ html += ` <button class="btn" onclick="approveIssue(${i.id})">Approve</button> <button class="btn danger" onclick="rejectIssue(${i.id})">Reject</button>`; }
                }
                html += `</td></tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('content').innerHTML = html;

            const notificationIssueId = new URLSearchParams(window.location.search).get('notification_issue');
            if (notificationIssueId) {
                const issueRow = Array.from(document.querySelectorAll('#content tbody tr')).find(row => row.firstElementChild?.textContent.trim() === notificationIssueId);
                if (issueRow) {
                    issueRow.style.background = '#edf6f5';
                    issueRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }
        load();
    </script>

    <script>
        function debounce(fn, wait){ let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args), wait); }; }

        async function searchItems(q){
            if(!q || q.trim().length<1) return [];
            const res = await fetch('/inventory/items/search?q='+encodeURIComponent(q));
            if(!res.ok) return [];
            return await res.json();
        }

        const sugBox = document.getElementById('ci_suggestions');
        const searchInput = document.getElementById('ci_item_search');
        const requestTypeSelect = document.getElementById('ci_request_type');
        const borrowedDateGroup = document.getElementById('ci_borrowed_date_group');
        const returnDateGroup = document.getElementById('ci_return_date_group');
        const borrowedDateInput = document.getElementById('ci_borrowed_date');
        const returnDateInput = document.getElementById('ci_return_date');
        const createIssueForm = document.getElementById('createIssueForm');

        if (searchInput && sugBox) {
            async function renderSuggestions(q){
                const items = await searchItems(q);
                if(!items || items.length===0){ sugBox.style.display='none'; sugBox.innerHTML=''; return; }
                sugBox.innerHTML = items.map(it=>`<div data-id="${it.id}" style="padding:8px;border-bottom:1px solid #f0f0f0;cursor:pointer">${it.name} <span style="color:#666;font-size:12px">(${it.sku||''})</span> <span style="float:right;color:#0b61d8">${it.quantity}</span></div>`).join('');
                sugBox.style.display='block';
                Array.from(sugBox.children).forEach(ch=>{ ch.addEventListener('click', ()=>{ document.getElementById('ci_item').value = ch.getAttribute('data-id'); searchInput.value = ch.textContent.trim(); sugBox.style.display='none'; }); });
            }

            searchInput.addEventListener('input', debounce((e)=>{ const v=e.target.value; document.getElementById('ci_item').value=''; renderSuggestions(v); }, 250));
            document.addEventListener('click', (ev)=>{ if(!ev.target.closest('#ci_suggestions') && ev.target !== searchInput) sugBox.style.display='none'; });
        }

        if (requestTypeSelect && borrowedDateGroup && returnDateGroup && borrowedDateInput && returnDateInput) {
            function toggleBorrowFields() {
                const isBorrow = (requestTypeSelect.value || 'issue') === 'borrow';
                borrowedDateGroup.style.display = isBorrow ? 'block' : 'none';
                returnDateGroup.style.display = isBorrow ? 'block' : 'none';
                if (!isBorrow) {
                    borrowedDateInput.value = '';
                    returnDateInput.value = '';
                }
            }

            requestTypeSelect.addEventListener('change', toggleBorrowFields);
            toggleBorrowFields();
        }

        if (createIssueForm) {
            createIssueForm.addEventListener('submit', async (e)=>{
                e.preventDefault();
                const deptVal = document.getElementById('ci_dept').value || '';
                const deptId = deptVal === '' ? null : (isNaN(Number(deptVal)) ? null : Number(deptVal));
                const purpose = (document.getElementById('ci_purpose').value || '').trim();
                const requestType = document.getElementById('ci_request_type').value || 'issue';
                const itemId = Number(document.getElementById('ci_item').value || 0);
                const borrowedDate = document.getElementById('ci_borrowed_date').value || null;
                const returnDate = document.getElementById('ci_return_date').value || null;

                if(!itemId){ document.getElementById('createMsg').textContent='Please select an item from suggestions.'; return; }
                if(!purpose){ document.getElementById('createMsg').textContent='Please specify the purpose for this request.'; return; }
                if(requestType === 'borrow' && !borrowedDate){ document.getElementById('createMsg').textContent='Please choose the date borrowed.'; return; }
                if(requestType === 'borrow' && !returnDate){ document.getElementById('createMsg').textContent='Please choose the date to be returned.'; return; }

                const payload = {
                    item_id: itemId,
                    quantity: Number(document.getElementById('ci_qty').value || 1),
                    purpose,
                    department_id: deptId,
                    issued_to_user_id: document.getElementById('ci_user').value || null,
                };

                let endpoint = '/inventory/issues';
                let method = 'POST';
                let successText = 'Request created.';

                if (requestType === 'borrow') {
                    endpoint = '/inventory/transactions';
                    method = 'POST';
                    Object.assign(payload, {
                        transaction_type: 'borrow',
                        user_id: currentUserId,
                        notes: purpose,
                        expected_return_at: returnDate,
                    });
                    delete payload.issued_to_user_id;
                    successText = 'Borrow recorded successfully.';
                }

                const res = await fetch(endpoint, {
                    method,
                    headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': csrf},
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    const j = await res.json().catch(()=>null);
                    document.getElementById('createMsg').textContent = requestType === 'borrow' ? successText : (j && j.over_requested ? 'Requested, but exceeds available stock.' : successText);
                    document.getElementById('createIssueForm').reset();
                    if (typeof toggleBorrowFields === 'function') {
                        toggleBorrowFields();
                    }
                    load();
                } else {
                    let text = '';
                    try { text = await res.text(); } catch (e) { text = 'Unable to read response'; }
                    document.getElementById('createMsg').textContent = `Create failed (${res.status}): ${text}`;
                }
            });
        }

        async function approveIssue(id){ if(!confirm('Approve issue #'+id+'?')) return; const res = await fetch('/inventory/issues/'+id+'/approve', { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf} }); const j = await res.json().catch(()=>({error:'error'})); if(res.ok) load(); else alert(j.error||JSON.stringify(j)); }
        async function rejectIssue(id){ const reason = prompt('Why are you rejecting issue #'+id+'?'); if(reason === null || !reason.trim()) return; const res = await fetch('/inventory/issues/'+id+'/reject', { method:'POST', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf}, body:JSON.stringify({reason:reason.trim()}) }); const j = await res.json().catch(()=>({error:'error'})); if(res.ok) load(); else alert(j.error||JSON.stringify(j)); }

        function openEditIssue(id, itemId, quantity, purpose, departmentId){ document.getElementById('edit_issue_id').value=id; document.getElementById('edit_item_id').value=itemId; document.getElementById('edit_quantity').value=quantity; document.getElementById('edit_purpose').value=purpose; document.getElementById('edit_department').value=departmentId || ''; document.getElementById('editIssueModal').style.display='flex'; }
        function closeEditIssue(){ document.getElementById('editIssueModal').style.display='none'; document.getElementById('editIssueMsg').textContent=''; }
        document.getElementById('editIssueForm').addEventListener('submit', async (event)=>{ event.preventDefault(); const id=document.getElementById('edit_issue_id').value; const payload={item_id:Number(document.getElementById('edit_item_id').value),quantity:Number(document.getElementById('edit_quantity').value),purpose:document.getElementById('edit_purpose').value,department_id:document.getElementById('edit_department').value || null}; const res=await fetch('/inventory/issues/'+id,{method:'PATCH',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)}); if(res.ok){closeEditIssue();load();}else{document.getElementById('editIssueMsg').textContent='Unable to update this request.';} });
    </script>
</x-app-layout>
