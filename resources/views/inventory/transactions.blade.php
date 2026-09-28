<x-app-layout>
    <style>
        body{font-family:Arial,sans-serif;margin:0;color:#17324d;background:radial-gradient(circle at 100% 0,rgba(47,154,145,.12),transparent 28rem),linear-gradient(135deg,#f4f8f8 0%,#e8f1f2 100%)}
        .legacy-module-page>h1{position:relative;display:inline-block;margin-bottom:1.25rem;color:#123b5d;letter-spacing:.01em}
        .legacy-module-page>h1::after{content:"";display:block;width:3.5rem;height:4px;margin-top:.45rem;border-radius:4px;background:#d99b2b}
        .toolbar{display:flex;gap:12px;align-items:center;margin-bottom:16px;flex-wrap:wrap}
        .card{background:rgba(255,255,255,.9);padding:20px;border:1px solid #d8e5e8;border-radius:12px;box-shadow:0 16px 40px rgba(18,59,93,.08);overflow:hidden}
        #results{width:100%;overflow-x:auto;padding-bottom:4px}
        table{width:100%;min-width:940px;border-collapse:separate;border-spacing:0;margin-top:8px;border:1px solid #d8e5e8;border-radius:12px;overflow:hidden;table-layout:fixed}
        th,td{border:1px solid #d8e5e8;padding:8px 10px;text-align:left;vertical-align:middle;word-break:break-word;overflow-wrap:anywhere;white-space:normal;line-height:1.3}
        th{background:#dcefed;color:#123b5d;font-size:12px;letter-spacing:.02em}
        td{font-size:13px}
        tbody tr{background:rgba(255,255,255,.82);transition:background .2s}
        tbody tr:hover{background:#fff}
        .muted{color:#536879;font-size:13px}
        .btn{display:inline-block;padding:8px 12px;border-radius:8px;text-decoration:none;color:#fff;background:#123b5d;border:1px solid #123b5d;box-shadow:0 6px 14px rgba(18,59,93,.12);transition:background .2s ease, border-color .2s ease, transform .15s ease}
        .btn:hover{background:#287f92;border-color:#287f92;transform:translateY(-1px)}
        .btn.secondary{background:#7a8794;border-color:#7a8794}
        .btn.secondary:hover{background:#657789;border-color:#657789}
        .action-buttons{display:flex;flex-wrap:wrap;align-items:center;gap:4px}
        .action-buttons button{display:inline-flex;align-items:center;justify-content:center;padding:4px 6px;border:1px solid #123b5d;border-radius:6px;background:#123b5d;color:#fff;font-size:10px;line-height:1.2;cursor:pointer;white-space:normal}
        .action-buttons button:hover{background:#287f92;border-color:#287f92}
        .action-buttons .return-borrow{border-color:#16794b;background:#16794b}
        .action-buttons .return-borrow:hover{border-color:#11633d;background:#11633d}
        .filters input, .filters select{padding:8px;border:1px solid #cbdde1;border-radius:7px;background:#fff;color:#17324d}
            #exportCsv{background:#217346;color:#fff}
            #exportCsv:hover{background:#185c37}
            #exportPdf{background:#d32f2f;color:#fff}
            #exportPdf:hover{background:#b71c1c}
        .pager{margin-top:10px}
        .link{color:#0b61d8;cursor:pointer}
        .modal{position:fixed;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:none;align-items:center;justify-content:center}
        .modal .panel{background:#fff;padding:20px;max-width:800px;width:90%;border-radius:12px;box-shadow:0 20px 50px rgba(18,59,93,.2)}
        @media (max-width:700px){.legacy-module-page{padding:1rem .75rem}.toolbar{align-items:flex-start}.card{padding:14px}.filters{align-items:stretch!important}.filters label{width:100%}.filters input,.filters select{width:100%;box-sizing:border-box}}
    </style>

    <div class="legacy-module-page">
        <h1>Inventory History & Borrowing Audit</h1>
        <div class="toolbar">
            @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian()))
                <a id="newTx" class="btn">New Transaction</a>
            @endif
            <div style="flex:1"></div>
            @if(auth()->user() && (auth()->user()->isApprover() || auth()->user()->role==='admin'))
                <a id="exportCsv" class="btn" href="#">Export CSV</a>
                <a id="exportPdf" class="btn secondary" href="#">Export PDF</a>
            @endif
        </div>

        <div class="card">
            <div class="filters" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                <label class="muted">Item ID <input id="f_item" type="text" placeholder="item id"></label>
                <label class="muted">User ID <input id="f_user" type="text" placeholder="user id"></label>
                <label class="muted">Department <input id="f_dept" type="text" placeholder="department"></label>
                <label class="muted">Type
                    <select id="f_type">
                        <option value="">Any</option>
                        <option value="issue">issue</option>
                        <option value="receive">receive</option>
                        <option value="borrow">borrow</option>
                        <option value="return">return</option>
                    </select>
                </label>
                <label class="muted">From <input id="f_from" type="date"></label>
                <label class="muted">To <input id="f_to" type="date"></label>
                <button id="applyFilters" class="btn">Apply</button>
                <button id="clearFilters" class="btn secondary">Clear</button>
            </div>

            <div id="results">Loading...</div>
        </div>

        <div id="detailModal" class="modal" role="dialog" aria-hidden="true">
            <div class="panel">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <h3>Transaction Detail</h3>
                    <button id="closeModal" class="btn secondary">Close</button>
                </div>
                <div id="detailContent" style="margin-top:12px"></div>
            </div>
        </div>

        <div id="editModal" class="modal" aria-hidden="true">
            <div class="panel">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <h3>Update Item Condition</h3>
                    <button id="closeEdit" class="btn secondary">Close</button>
                </div>
                <form id="editForm" style="margin-top:12px;display:grid;gap:8px">
                    <input type="hidden" id="e_id">
                    <div><strong>Item:</strong> <span id="e_item_name"></span></div>
                    <label>Condition <input id="e_condition" name="condition" maxlength="100" placeholder="e.g. In good condition" required></label>
                    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
                        <button type="submit" class="btn">Save</button>
                        <button type="button" id="cancelEdit" class="btn secondary">Cancel</button>
                    </div>
                    <div id="editMessage" class="muted" style="margin-top:6px"></div>
                </form>
            </div>
        </div>

        <div id="confirmModal" class="modal" aria-hidden="true">
            <div class="panel">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <h3 id="confirmTitle">Confirm Action</h3>
                    <button id="closeConfirm" class="btn secondary">Close</button>
                </div>
                <div id="confirmBody" style="margin-top:12px"></div>
                <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:12px">
                    <button id="confirmOk" class="btn">Confirm</button>
                    <button id="confirmCancel" class="btn secondary">Cancel</button>
                </div>
            </div>
        </div>

        <div id="createModal" class="modal" aria-hidden="true">
            <div class="panel">
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <h3>New Transaction</h3>
                    <button id="closeCreate" class="btn secondary">Close</button>
                </div>
                <form id="createForm" style="margin-top:12px;display:grid;gap:8px">
                    <label>Item ID <input id="c_item_id" name="item_id" required></label>
                    <label>Type
                        <select id="c_type" name="transaction_type">
                            <option value="issue">issue</option>
                            <option value="receive">receive</option>
                            <option value="borrow">borrow</option>
                            <option value="return">return</option>
                        </select>
                    </label>
                    <label>Quantity <input id="c_quantity" name="quantity" type="number" min="1" value="1" required></label>
                    <label>Purpose <input id="c_purpose" name="purpose" placeholder="Specify the reason for borrowing" required></label>
                    <label>Expected Return <input id="c_expected_return_at" name="expected_return_at" type="datetime-local"></label>
                    <label>User ID <input id="c_user_id" name="user_id"></label>
                    <label>Department ID <input id="c_department_id" name="department_id"></label>
                    <label>Notes <input id="c_notes" name="notes"></label>
                    <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:6px">
                        <button type="submit" class="btn">Create</button>
                        <button type="button" id="cancelCreate" class="btn secondary">Cancel</button>
                    </div>
                    <div id="createMessage" class="muted" style="margin-top:6px"></div>
                </form>
            </div>
        </div>

        <script>
        const resultsEl = document.getElementById('results');
        const exportBtn = document.getElementById('exportCsv');
        const exportPdfBtn = document.getElementById('exportPdf');
        const modal = document.getElementById('detailModal');
        const detailContent = document.getElementById('detailContent');
        const createModal = document.getElementById('createModal');
        const createForm = document.getElementById('createForm');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function buildQuery(params){
            return Object.keys(params).filter(k=>params[k]!=='' && params[k]!=null).map(k=>encodeURIComponent(k)+'='+encodeURIComponent(params[k])).join('&');
        }

        async function fetchPage(url){
            const res = await fetch(url);
            return res.json();
        }

        async function load(pageUrl = '/inventory/transactions'){
            resultsEl.textContent = 'Loading...';
            const params = {
                item_id: document.getElementById('f_item').value,
                user_id: document.getElementById('f_user').value,
                department_id: document.getElementById('f_dept').value,
                transaction_type: document.getElementById('f_type').value,
                date_from: document.getElementById('f_from').value,
                date_to: document.getElementById('f_to').value,
            };

            const q = buildQuery(params);
            const url = pageUrl + (q ? ('?'+q) : '');
            const data = await fetchPage(url);

            const list = data.data || data;
            if(!list || list.length===0){ resultsEl.innerHTML = '<div class="muted">No transactions found.</div>'; return; }

            let html = '<table><thead><tr><th>ID</th><th>Item</th><th>User</th><th>Type</th><th>Qty</th><th>Department</th><th>Condition</th><th>Status</th><th>Notes</th><th>Date</th>';
            const canEdit = @json(auth()->user()?->isTeacher() ?? false);
            const canManage = @json(auth()->user()?->isAdmin() || auth()->user()?->isPropertyCustodian());
            if(canEdit || canManage) html += '<th>Actions</th>';
            html += '</tr></thead><tbody>';
            list.forEach(t=>{
                const type = (t.transaction_type||'').toLowerCase();
                const typeLabel = type === 'adjustment' ? `<span style="background:#f59e0b;color:#fff;padding:4px 6px;border-radius:4px">Adjustment</span>` : `<span style="background:#0b61d8;color:#fff;padding:4px 6px;border-radius:4px">${escapeHtml(t.transaction_type)}</span>`;
                const department = t.department && t.department.name ? t.department.name : (t.department_id ? 'Department '+t.department_id : '');
                let statusCell = escapeHtml(t.status || 'N/A');
                let actions = '';
                if (canManage && type === 'borrow' && t.status === 'pending') {
                    actions += `<button class="approve-borrow" data-id="${t.id}">Approve</button> <button class="reject-borrow" data-id="${t.id}">Reject</button>`;
                }
                if(canEdit){ actions += `<button class="edit-tx" data-id="${t.id}">Edit condition</button>`; }
                if((canEdit || canManage) && type === 'borrow' && t.status === 'approved' && !t.returned_at){
                    actions += `<button class="return-borrow" data-id="${t.id}" data-item-id="${t.item_id}" data-user-id="${t.user_id}" data-quantity="${t.quantity}">Mark as returned</button>`;
                }
                const notes = t.status === 'rejected' && t.rejection_reason ? `${t.notes || ''} | Rejection: ${t.rejection_reason}` : (t.notes || '');
                html += `<tr class="link-row" data-id="${t.id}"><td>${t.id}</td><td>${t.item?escapeHtml(t.item.name):t.item_id}</td><td>${t.user?escapeHtml(t.user.name):t.user_id}</td><td>${typeLabel}</td><td>${t.quantity}</td><td>${escapeHtml(department)}</td><td>${escapeHtml(t.condition||'Not recorded')}</td><td>${statusCell}</td><td>${escapeHtml(notes)}</td><td>${t.created_at}`;
                if(canEdit || canManage){ html += `<td><div class="action-buttons">${actions}</div></td>`; }
                html += `</tr>`;
            });
            html += '</tbody></table>';

            if(data.links){
                html += '<div class="pager">'+renderPagination(data)+'</div>';
            }

            resultsEl.innerHTML = html;

            document.querySelectorAll('.link-row').forEach(r=>{
                r.addEventListener('click', ()=> showDetail(r.dataset.id));
            });

            if(exportBtn){
                const exportUrl = '/inventory/transactions?' + buildQuery(Object.assign(params,{export:'csv'}));
                exportBtn.setAttribute('href', exportUrl);
            }
            if(exportPdfBtn){
                const exportUrl = '/inventory/transactions?' + buildQuery(Object.assign(params,{export:'pdf'}));
                exportPdfBtn.setAttribute('href', exportUrl);
            }
        }

        function escapeHtml(str){ return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

        function renderPagination(data){
            let html = '';
            if(data.prev_page_url){ html += `<a class="link" href="#" data-url="${data.prev_page_url}">« Prev</a> `; }
            html += `<span class="muted"> Page ${data.current_page || 1} of ${data.last_page || 1}</span> `;
            if(data.next_page_url){ html += ` <a class="link" href="#" data-url="${data.next_page_url}">Next »</a>`; }
            return html;
        }

        async function showDetail(id){
            detailContent.textContent = '';
            modal.style.display = 'flex';
            const res = await fetch('/inventory/transactions/'+id);
            const json = await res.json();
            const t = json;
            const item = t.item || {};
            const user = t.user || {};
            let html = `<div><strong>ID:</strong> ${t.id}</div>`;
            html += `<div><strong>Item:</strong> ${escapeHtml(item.name || t.item_id)} (SKU: ${escapeHtml(item.sku||'')})</div>`;
            html += `<div><strong>User:</strong> ${escapeHtml(user.name||t.user_id)}</div>`;
            html += `<div><strong>Type:</strong> ${escapeHtml(t.transaction_type)} ${t.transaction_type === 'adjustment' ? '<em style="color:#b45309;margin-left:8px">(manual adjustment)</em>' : ''}</div>`;
            html += `<div><strong>Quantity:</strong> ${t.quantity}</div>`;
            html += `<div><strong>Department:</strong> ${escapeHtml(t.department && t.department.name ? t.department.name : (t.department_id || 'N/A'))}</div>`;
            html += `<div><strong>Expected return:</strong> ${escapeHtml(t.expected_return_at||'N/A')}</div>`;
            html += `<div><strong>Returned:</strong> ${escapeHtml(t.returned_at||'N/A')}</div>`;
            html += `<div><strong>Condition:</strong> ${escapeHtml(t.condition||'Not recorded')}</div>`;
            html += `<div><strong>Notes:</strong> ${escapeHtml(t.notes||'')}</div>`;
            if (t.rejection_reason) html += `<div><strong>Rejection reason:</strong> ${escapeHtml(t.rejection_reason)}</div>`;
            html += `<div><strong>Date:</strong> ${t.created_at}</div>`;
            if(item){ html += `<div style="margin-top:8px"><strong>Current Item Qty:</strong> ${item.quantity||'N/A'}</div>`; }
            detailContent.innerHTML = html;
        }

        document.addEventListener('click', async (e)=>{
            if(e.target.matches('.return-borrow')){
                e.stopPropagation();
                const button = e.target;
                showConfirm('Mark this borrowed item as returned? The returned quantity will be added back to inventory.', async ()=>{
                    const response = await fetch('/inventory/transactions', {
                        method:'POST',
                        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},
                        body:JSON.stringify({
                            item_id:button.dataset.itemId,
                            user_id:button.dataset.userId,
                            transaction_type:'return',
                            quantity:button.dataset.quantity,
                            condition:'Good',
                            notes:'Marked as returned',
                        }),
                    });
                    if(response.ok){ load(); }
                    else { const result = await response.json().catch(()=>({error:'Return failed'})); alert(result.error || 'Return failed'); }
                });
            }

            if(e.target.matches('.edit-tx')){
                e.stopPropagation();
                const id = e.target.getAttribute('data-id');
                openEdit(id);
            }

            if(e.target.matches('.approve-borrow')){
                e.stopPropagation();
                const id = e.target.getAttribute('data-id');
                const response = await fetch('/inventory/transactions/'+id+'/approve', { method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken} });
                if(response.ok) load(); else alert((await response.json().catch(()=>({error:'Approval failed'}))).error || 'Approval failed');
            }

            if(e.target.matches('.reject-borrow')){
                e.stopPropagation();
                const id = e.target.getAttribute('data-id');
                const reason = prompt('Why are you rejecting borrow request #'+id+'?');
                if(reason === null || !reason.trim()) return;
                const response = await fetch('/inventory/transactions/'+id+'/reject', { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken}, body:JSON.stringify({reason:reason.trim()}) });
                if(response.ok) load(); else alert((await response.json().catch(()=>({error:'Rejection failed'}))).error || 'Rejection failed');
            }

            if(e.target.matches('.del-tx')){
                e.stopPropagation();
                const id = e.target.getAttribute('data-id');
                const r = await fetch('/inventory/transactions/'+id);
                const tx = await r.json().catch(()=>null);
                if(!tx){ alert('Failed to load transaction'); return; }
                const itemQty = Number(tx.item && tx.item.quantity ? tx.item.quantity : 0);
                const type = (tx.transaction_type||'').toLowerCase();
                const qty = Number(tx.quantity||0);
                const effect = (['receive','return'].includes(type)? qty : -qty);
                const afterDelete = itemQty - effect;

                showConfirm(`Deleting will change item stock from ${itemQty} to ${afterDelete}. Proceed?`, async ()=>{
                    const res = await fetch('/inventory/transactions/'+id, { method: 'DELETE', headers: {'X-CSRF-TOKEN': csrfToken} });
                    if(res.ok){ load(); } else { const j = await res.json().catch(()=>({error:'Delete failed'})); alert(j.error||'Delete failed'); }
                });
            }
        });

        async function openEdit(id){
            document.getElementById('editMessage').textContent = '';
            document.getElementById('editModal').style.display = 'flex';
            document.getElementById('e_id').value = id;
            const res = await fetch('/inventory/transactions/'+id);
            if(!res.ok){ document.getElementById('editMessage').textContent = 'Failed to load'; return; }
            const j = await res.json();
            const itemNameEl = document.getElementById('e_item_name');
            itemNameEl.textContent = (j.item && j.item.name) ? j.item.name : j.item_id;
            document.getElementById('e_condition').value = j.condition || '';
        }

        document.getElementById('closeEdit').addEventListener('click', ()=>{ document.getElementById('editModal').style.display='none'; });
        document.getElementById('cancelEdit').addEventListener('click', ()=>{ document.getElementById('editModal').style.display='none'; });

        function showConfirm(message, onOk){
            document.getElementById('confirmBody').textContent = message;
            const modal = document.getElementById('confirmModal');
            modal.style.display = 'flex';
            const ok = document.getElementById('confirmOk');
            const cancel = document.getElementById('confirmCancel');
            function cleanup(){ ok.removeEventListener('click', okHandler); cancel.removeEventListener('click', cancelHandler); modal.style.display='none'; }
            function okHandler(){ cleanup(); onOk(); }
            function cancelHandler(){ cleanup(); }
            ok.addEventListener('click', okHandler);
            cancel.addEventListener('click', cancelHandler);
        }

        document.getElementById('closeConfirm').addEventListener('click', ()=>{ document.getElementById('confirmModal').style.display='none'; });

        document.getElementById('editForm').addEventListener('submit', async (e)=>{
            e.preventDefault();
            const id = document.getElementById('e_id').value;
            const payload = { condition: document.getElementById('e_condition').value.trim() };
            const res = await fetch('/inventory/transactions/'+id+'/condition', { method: 'PATCH', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrfToken,'Accept':'application/json'}, body: JSON.stringify(payload) });
            if(res.ok){ document.getElementById('editMessage').textContent='Saved.'; document.getElementById('editModal').style.display='none'; load(); }
            else { const j = await res.json().catch(()=>({error:'error'})); document.getElementById('editMessage').textContent = j.error||JSON.stringify(j); }
        });

        document.getElementById('applyFilters').addEventListener('click', ()=> load());
        document.getElementById('clearFilters').addEventListener('click', ()=>{
            ['f_item','f_user','f_dept','f_type','f_from','f_to'].forEach(id=>document.getElementById(id).value='');
            load();
        });

        document.addEventListener('click', (e)=>{
            if(e.target.matches('.pager .link')){
                e.preventDefault();
                const url = e.target.getAttribute('data-url');
                load(url);
            }
        });

        document.getElementById('closeModal').addEventListener('click', ()=>{ modal.style.display='none'; });

        if(document.getElementById('newTx')){
            const newTransactionButton = document.getElementById('newTx');
            if (newTransactionButton) {
                newTransactionButton.addEventListener('click', ()=>{ createModal.style.display='flex'; document.getElementById('createMessage').textContent=''; });
            }
        }
        document.getElementById('closeCreate').addEventListener('click', ()=>{ createModal.style.display='none'; });
        document.getElementById('cancelCreate').addEventListener('click', ()=>{ createModal.style.display='none'; });

        createForm.addEventListener('submit', async (e)=>{
            e.preventDefault();
            const purpose = (document.getElementById('c_purpose').value || '').trim();
            if (!purpose) {
                document.getElementById('createMessage').textContent = 'Please specify the purpose for this borrowing request.';
                return;
            }

            const payload = {
                item_id: document.getElementById('c_item_id').value,
                transaction_type: document.getElementById('c_type').value,
                quantity: document.getElementById('c_quantity').value,
                purpose,
                expected_return_at: document.getElementById('c_expected_return_at').value || undefined,
                user_id: document.getElementById('c_user_id').value || undefined,
                department_id: document.getElementById('c_department_id').value || undefined,
                notes: document.getElementById('c_notes').value || undefined,
            };

            const res = await fetch('/inventory/transactions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if(res.status === 201){
                const json = await res.json();
                document.getElementById('createMessage').textContent = 'Transaction created.';
                createModal.style.display = 'none';
                load();
                showDetail(json.id);
            } else {
                const err = await res.json().catch(()=>({error:'Unknown error'}));
                document.getElementById('createMessage').textContent = err.error || JSON.stringify(err);
            }
        });

        load();
        </script>
    </div>
</x-app-layout>
