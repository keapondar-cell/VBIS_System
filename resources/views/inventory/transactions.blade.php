<x-app-layout>
    <style>
        body{font-family:Arial,sans-serif;margin:0;color:#17324d;background:radial-gradient(circle at 100% 0,rgba(47,154,145,.12),transparent 28rem),linear-gradient(135deg,#f4f8f8 0%,#e8f1f2 100%)}
        .legacy-module-page>h1{position:relative;display:inline-block;margin-bottom:1.25rem;color:#123b5d;letter-spacing:.01em}
        .legacy-module-page>h1::after{content:"";display:block;width:3.5rem;height:4px;margin-top:.45rem;border-radius:4px;background:#d99b2b}
        .toolbar{display:flex;gap:12px;align-items:center;margin-bottom:16px;flex-wrap:wrap}
        .card{background:rgba(255,255,255,.9);padding:20px;border:1px solid #d8e5e8;border-radius:12px;box-shadow:0 16px 40px rgba(18,59,93,.08);overflow:hidden}
        #results{width:100%;overflow-x:auto;padding-bottom:4px}
        .transaction-results table{width:100%;min-width:1380px;margin-top:8px;border:0;border-collapse:collapse;border-spacing:0;border-radius:0;table-layout:auto}
        .transaction-results th{padding:13px 14px;border:0;border-bottom:1px solid #cfe2e3;background:#e8f4f3;color:#526b7a;font-size:11px;font-weight:700;letter-spacing:.07em;text-align:left;text-transform:uppercase;white-space:nowrap}
        .transaction-results td{padding:14px;border:0;border-bottom:1px solid #edf1f3;color:#334b5d;font-size:13px;line-height:1.45;vertical-align:middle}
        .transaction-results tbody tr:nth-child(even){background:#f8fbfb}
        .transaction-results tbody tr:hover{background:#eff8f7}
        .transaction-results .transaction-id{width:64px;min-width:64px;text-align:center;white-space:nowrap;font-variant-numeric:tabular-nums}
        .transaction-results .transaction-item{width:220px;min-width:220px;color:#123b5d;font-weight:700;white-space:nowrap}
        .transaction-results .transaction-user{width:170px;min-width:170px;white-space:nowrap}
        .transaction-results .transaction-notes{width:260px;min-width:260px;max-width:320px;white-space:normal;overflow-wrap:anywhere;line-height:1.5}
        .transaction-results .transaction-quantity{text-align:center;font-weight:700;font-variant-numeric:tabular-nums}
        .transaction-results .transaction-type-cell{min-width:110px;white-space:nowrap}
        .transaction-results .transaction-type-badge{display:inline-flex;align-items:center;justify-content:center;max-width:100%;padding:5px 9px;border-radius:999px;background:#e2e8f0;color:#334155;font-size:11px;font-weight:700;line-height:1.2;text-transform:capitalize;white-space:nowrap}
        .transaction-results .transaction-type-badge.type-issue{background:#dbeafe;color:#1d4ed8}
        .transaction-results .transaction-type-badge.type-borrow{background:#ede9fe;color:#6d28d9}
        .transaction-results .transaction-type-badge.type-return{background:#dcfce7;color:#15803d}
        .transaction-results .transaction-type-badge.type-receive{background:#ccfbf1;color:#0f766e}
        .transaction-results .transaction-type-badge.type-adjustment{background:#fef3c7;color:#92400e}
        .transaction-results .transaction-date{white-space:nowrap}
        .transaction-results .transaction-status{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;background:#edf2f7;color:#475569;font-size:11px;font-weight:700;text-transform:capitalize;white-space:nowrap}
        .transaction-results .transaction-status.status-approved,.transaction-results .transaction-status.status-completed,.transaction-results .transaction-status.status-returned{background:#dcfce7;color:#166534}
        .transaction-results .transaction-status.status-pending{background:#fef3c7;color:#92400e}
        .transaction-results .transaction-status.status-rejected{background:#fee2e2;color:#991b1b}
        .transaction-results .transaction-actions{min-width:150px}
        .transaction-results .action-buttons{flex-wrap:nowrap;gap:6px}
        .transaction-results .action-buttons button{padding:7px 10px;white-space:nowrap;font-size:11px}
        .transaction-results .action-buttons .return-borrow{background:#16794b}
        .transaction-results .action-buttons .return-borrow:hover{background:#11633d}
        table{width:100%;min-width:940px;border-collapse:separate;border-spacing:0;margin-top:8px;border:1px solid #d8e5e8;border-radius:12px;overflow:hidden;table-layout:fixed}
        th,td{border:1px solid #d8e5e8;padding:8px 10px;text-align:left;vertical-align:middle;word-break:break-word;overflow-wrap:anywhere;white-space:normal;line-height:1.3}
        th{background:#dcefed;color:#123b5d;font-size:12px;letter-spacing:.02em}
        td{font-size:13px}
        tbody tr{background:rgba(255,255,255,.82);transition:background .2s}
        tbody tr:hover{background:#fff}
        .borrowing-results table{min-width:1080px;border:0;border-collapse:collapse;border-radius:0;table-layout:auto}
        .borrowing-results th{padding:13px 14px;border:0;border-bottom:1px solid #cfe2e3;background:#e8f4f3;color:#526b7a;font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;white-space:nowrap}
        .borrowing-results td{padding:14px;border:0;border-bottom:1px solid #edf1f3;color:#334b5d;font-size:13px;line-height:1.45}
        .borrowing-results .borrow-id{width:64px;min-width:64px;text-align:center;white-space:nowrap;font-variant-numeric:tabular-nums}
        .borrowing-results tbody tr:nth-child(even){background:#f8fbfb}
        .borrowing-results tbody tr:hover{background:#eff8f7}
        .borrowing-results .borrow-item{width:220px;min-width:220px;color:#123b5d;font-weight:700;white-space:nowrap}
        .borrowing-results .borrow-user{width:170px;min-width:170px;white-space:nowrap}
        .borrowing-results .borrow-purpose{width:260px;min-width:260px;max-width:320px;color:#536879;white-space:normal;overflow-wrap:anywhere;line-height:1.5}
        .borrowing-results .borrow-quantity{text-align:center;font-weight:700;font-variant-numeric:tabular-nums}
        .borrowing-results .borrow-date{white-space:nowrap}
        .borrowing-results .borrow-status{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;background:#edf2f7;color:#475569;font-size:11px;font-weight:700;text-transform:capitalize;white-space:nowrap}
        .borrowing-results .borrow-status.status-approved{background:#dcfce7;color:#166534}
        .borrowing-results .borrow-status.status-pending{background:#fef3c7;color:#92400e}
        .borrowing-results .borrow-status.status-rejected{background:#fee2e2;color:#991b1b}
        .borrowing-results .action-buttons{flex-wrap:nowrap;gap:6px}
        .borrowing-results .action-buttons button{padding:7px 10px;white-space:nowrap;font-size:11px}
        .borrowing-results .action-buttons .return-borrow{background:#16794b}
        .borrowing-results .action-buttons .return-borrow:hover{background:#11633d}
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
        .modal{position:fixed;inset:0;z-index:1000;box-sizing:border-box;width:100vw;height:100vh;padding:20px;background:rgba(15,23,42,.18);backdrop-filter:blur(2px);display:none;align-items:center;justify-content:center;overflow-y:auto}
        .modal .panel{box-sizing:border-box;background:#f2f5f7;padding:24px 28px 20px;max-width:980px;width:100%;max-height:calc(100vh - 32px);overflow-y:auto;border:1px solid #dfe6eb;border-radius:18px;box-shadow:0 20px 50px rgba(18,59,93,.18)}
        .create-form-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:12px}
        .create-form-head h3{margin:0;color:#1d2a36;font-size:2.1rem;line-height:1.2;font-weight:700}
        .create-form{display:flex;flex-direction:column;gap:12px}
        .create-field{display:grid;grid-template-columns:minmax(150px,220px) minmax(0,1fr);align-items:center;gap:18px 20px;min-height:58px}
        .create-field > span{font-size:1.05rem;font-weight:700;color:#1f2d3d}
        .create-field > select,.create-field > input{width:100%;height:54px;padding:0 16px;border:1px solid #c8d1db;border-radius:12px;background:#f8fafc;color:#1f2d3d;font-size:1.05rem;box-sizing:border-box;transition:border-color .15s ease, box-shadow .15s ease, background .15s ease}
        .create-field > select:focus,.create-field > input:focus{outline:none;border-color:#2b86c5;background:#fff;box-shadow:0 0 0 4px rgba(43,134,197,.12)}
        .create-field.full-width{grid-template-columns:1fr}
        .create-field.full-width > span{display:block;margin-bottom:8px}
        .create-field.full-width > input,
        .create-field.full-width > select{width:100%}
        .create-field .date-input{padding-right:44px;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%235f6f7a' stroke-width='1.7'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M8 2v4M16 2v4M3 9h18M5 5h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 14px center;background-size:20px}
        .create-actions{display:flex;justify-content:flex-end;gap:14px;margin-top:8px;padding-top:8px}
        .close-ghost{background:#8a939d;border-color:#8a939d;padding:12px 24px;font-weight:700;border-radius:12px}
        .close-ghost:hover{background:#6f7b88;border-color:#6f7b88}
        .btn-create{background:linear-gradient(180deg,#2f9cf8 0%,#1d7ed6 100%);border-color:#1d7ed6;padding:14px 28px;border-radius:12px;font-weight:700;box-shadow:0 10px 20px rgba(29,126,214,.2)}
        .btn-create:hover{background:linear-gradient(180deg,#268ce5 0%,#196fba 100%);border-color:#196fba}
        .btn-cancel{background:#a7b0ba;border-color:#a7b0ba;padding:14px 28px;border-radius:12px;font-weight:700}
        .btn-cancel:hover{background:#8f98a3;border-color:#8f98a3}
        @media (max-width: 720px){
            .modal .panel{padding:20px 18px}
            .create-form-head{align-items:flex-start}
            .create-form-head h3{font-size:1.8rem}
            .create-field{grid-template-columns:1fr;gap:8px}
            .create-actions{flex-direction:column}
            .create-actions .btn{width:100%}
        }
        @media (max-width:700px){.legacy-module-page{padding:1rem .75rem}.toolbar{align-items:flex-start}.card{padding:14px}.filters{align-items:stretch!important}.filters label{width:100%}.filters input,.filters select{width:100%;box-sizing:border-box}}
    </style>

    <div class="legacy-module-page">
        <h1>{{ !empty($borrowingOnly) ? 'Borrowing Transactions' : 'Inventory Transaction History' }}</h1>
        <div class="toolbar">
            @if(!empty($borrowingOnly) && auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isTeacher()))
                <a id="newTx" class="btn">Request to Borrow</a>
            @elseif(empty($borrowingOnly) && auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isPropertyCustodian()))
                <a id="newTx" class="btn">New Transaction</a>
            @endif
            <div style="flex:1"></div>
            @if(auth()->user() && (auth()->user()->isApprover() || auth()->user()->role==='admin'))
                <x-export-link id="exportCsv" href="#" type="csv" label="Export transactions as CSV" />
                <x-export-link id="exportPdf" href="#" type="pdf" label="Export transactions as PDF" />
            @endif
        </div>

        <div class="card">
            <div class="filters" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                <label class="muted">Item Name
                    <select id="f_item">
                        <option value="">Select item</option>
                        @foreach($items ?? [] as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="muted">User
                    <select id="f_user">
                        <option value="">Select user</option>
                        @foreach($users ?? [] as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="muted">Department
                    <select id="f_dept">
                        <option value="">Select department</option>
                        @foreach($departments ?? [] as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </label>
                @if(!empty($borrowingOnly))
                    <input id="f_type" type="hidden" value="borrow">
                @else
                    <label class="muted">Type
                        <select id="f_type">
                            <option value="">Any</option>
                            <option value="issue">issue</option>
                            <option value="receive">receive</option>
                            <option value="borrow">borrow</option>
                            <option value="return">return</option>
                        </select>
                    </label>
                @endif
                <label class="muted">From <input id="f_from" type="date"></label>
                <label class="muted">To <input id="f_to" type="date"></label>
                <button id="applyFilters" class="btn">Apply</button>
                <button id="clearFilters" class="btn secondary">Clear</button>
            </div>

            <div id="results" class="transaction-results {{ !empty($borrowingOnly) ? 'borrowing-results' : '' }}">Loading...</div>
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
                    <label>Condition
                        <select id="e_condition" name="condition" required>
                            <option value="">Select condition</option>
                            <option value="Good">Good</option>
                            <option value="Damaged">Damaged</option>
                            <option value="Lost">Lost</option>
                            <option value="For Repair">For Repair</option>
                        </select>
                    </label>
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
                <div class="create-form-head">
                    <h3>{{ !empty($borrowingOnly) ? 'Request to Borrow' : 'New Transaction' }}</h3>
                    <button id="closeCreate" class="btn secondary close-ghost">Close</button>
                </div>
                <form id="createForm" class="create-form">
                    <label class="create-field">
                        <span>Item name</span>
                        <select id="c_item_id" name="item_id" required>
                            <option value="">Select item</option>
                            @foreach($items ?? [] as $item)
                                <option value="{{ $item->id }}" data-category="{{ $item->category }}" data-quantity="{{ $item->quantity }}" @if(!empty($borrowingOnly) && $item->quantity <= 0) disabled @endif>{{ $item->name }}@if(!empty($borrowingOnly)) — {{ $item->quantity > 0 ? $item->quantity.' available' : 'Out of stock' }}@endif</option>
                            @endforeach
                        </select>
                    </label>
                    @if(!empty($borrowingOnly))
                        <input id="c_type" name="transaction_type" type="hidden" value="borrow">
                    @else
                        <label class="create-field">
                            <span>Type</span>
                            <select id="c_type" name="transaction_type">
                                <option value="issue">issue</option>
                                <option value="receive">receive</option>
                                <option value="return">return</option>
                            </select>
                        </label>
                    @endif
                    <label class="create-field">
                        <span>Quantity</span>
                        <input id="c_quantity" name="quantity" type="number" min="1" value="1" required>
                    </label>
                    <label id="createPurposeField" class="create-field">
                        <span>Purpose</span>
                        <input id="c_purpose" name="purpose" placeholder="Specify the reason for borrowing">
                    </label>
                    <label id="createRequestedAtField" class="create-field">
                        <span id="createRequestedAtLabel">Date borrowed</span>
                        <input id="c_requested_for_at" class="date-input" name="requested_for_at" type="datetime-local">
                    </label>
                    <label id="createExpectedReturnField" class="create-field">
                        <span id="createExpectedReturnLabel">Expected Return</span>
                        <input id="c_expected_return_at" class="date-input" name="expected_return_at" type="date">
                    </label>
                    @if(empty($borrowingOnly))
                    <label class="create-field">
                        <span>User</span>
                        <select id="c_user_id" name="user_id">
                            <option value="">Select user</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}{{ $user->email ? ' — '.$user->email : '' }}</option>
                            @endforeach
                        </select>
                    </label>
                    @else
                        <input id="c_user_id" type="hidden" value="{{ auth()->id() }}">
                    @endif
                    <label class="create-field">
                        <span>Department</span>
                        <select id="c_department_id" name="department_id">
                            <option value="">Select department</option>
                            @foreach($departments ?? [] as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="create-field full-width">
                        <span>Notes</span>
                        <input id="c_notes" name="notes">
                    </label>
                    <div class="create-actions">
                        <button type="submit" class="btn btn-create">Create</button>
                        <button type="button" id="cancelCreate" class="btn btn-cancel secondary">Cancel</button>
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
        const createType = document.getElementById('c_type');
        const borrowingOnly = @json(!empty($borrowingOnly));
        const createPurpose = document.getElementById('c_purpose');
        const requestedAt = document.getElementById('c_requested_for_at');
        const expectedReturn = document.getElementById('c_expected_return_at');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function formatManilaDateTime(value, includeTime = true){
            if(!value) return 'N/A';
            const date = new Date(value);
            if(Number.isNaN(date.getTime())) return 'N/A';
            const options = includeTime
                ? { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Manila' }
                : { dateStyle: 'medium', timeZone: 'Asia/Manila' };
            return new Intl.DateTimeFormat('en-PH', options).format(date);
        }

        function updateBorrowFields(){
            const isBorrow = createType.value === 'borrow';
            const isIssue = createType.value === 'issue';
            const selectedItem = document.getElementById('c_item_id').selectedOptions[0];
            const isConsumableIssue = isIssue
                && (selectedItem?.dataset.category || '').trim().toLowerCase() === 'consumable';
            document.getElementById('createPurposeField').hidden = !isBorrow;
            document.getElementById('createRequestedAtField').hidden = !isBorrow && !isIssue;
            document.getElementById('createExpectedReturnField').hidden = (!isBorrow && !isIssue) || isConsumableIssue;
            document.getElementById('createExpectedReturnLabel').textContent = isIssue ? 'Expected Return Date' : 'Expected Return';
            const expectedReturnType = isBorrow ? 'datetime-local' : 'date';
            if (expectedReturn.type !== expectedReturnType) {
                expectedReturn.value = '';
                expectedReturn.type = expectedReturnType;
            }
            expectedReturn.required = isBorrow;
            document.getElementById('createRequestedAtLabel').textContent = isIssue ? 'Issue date' : 'Date borrowed';
            const requestedAtType = isIssue ? 'date' : 'datetime-local';
            if (requestedAt.type !== requestedAtType) {
                requestedAt.value = '';
                requestedAt.type = requestedAtType;
            }
            requestedAt.required = isIssue;
            createPurpose.required = isBorrow;
            if ((!isBorrow && !isIssue) || isConsumableIssue) {
                expectedReturn.value = '';
            }
            if (!isBorrow && !isIssue) {
                requestedAt.value = '';
            }
        }

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
                transaction_type: borrowingOnly ? 'borrow' : document.getElementById('f_type').value,
                date_from: document.getElementById('f_from').value,
                date_to: document.getElementById('f_to').value,
            };

            const q = buildQuery(params);
            const url = pageUrl + (q ? ('?'+q) : '');
            const data = await fetchPage(url);

            const list = data.data || data;
            if(!list || list.length===0){ resultsEl.innerHTML = '<div class="muted">No transactions found.</div>'; return; }

            let html = borrowingOnly
                ? '<table class="borrowing-table" aria-label="Borrowing transactions"><thead><tr><th class="borrow-id">ID</th><th>Item</th><th>Borrower</th><th>Qty</th><th class="borrow-purpose">Purpose</th><th>Department</th><th>Borrow date</th><th>Expected return</th><th>Status</th>'
                : '<table><thead><tr><th>ID</th><th>Item</th><th>User</th><th>Type</th><th>Qty</th><th>Department</th><th>Issue / borrow date</th><th>Expected return</th><th>Condition</th><th>Status</th><th class="transaction-notes">Notes</th><th>Date</th>';
            const canEdit = @json(auth()->user()?->isTeacher() ?? false);
            const canManage = @json(auth()->user()?->isAdmin() || auth()->user()?->isPropertyCustodian());
            if(canEdit || canManage) html += '<th>Actions</th>';
            html += '</tr></thead><tbody>';
            list.forEach(t=>{
                const type = (t.transaction_type||'').toLowerCase();
                const typeClass = type.replace(/[^a-z0-9_-]/g, '');
                const typeLabel = `<span class="transaction-type-badge type-${typeClass}">${escapeHtml(t.transaction_type || 'N/A')}</span>`;
                const department = t.department && t.department.name ? t.department.name : (t.department_id ? 'Department '+t.department_id : '');
                const requestedFor = t.requested_for_at
                    ? formatManilaDateTime(t.requested_for_at, type !== 'issue')
                    : 'N/A';
                const expectedReturnDate = formatManilaDateTime(t.expected_return_at, false);
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
                if(borrowingOnly){
                    const statusClass = `status-${String(t.status || '').toLowerCase().replace(/[^a-z0-9_-]/g, '')}`;
                    html += `<tr class="link-row" data-id="${t.id}"><td class="borrow-id">${t.id}</td><td class="borrow-item">${t.item?escapeHtml(t.item.name):t.item_id}</td><td class="borrow-user">${t.user?escapeHtml(t.user.name):t.user_id}</td><td class="borrow-quantity">${t.quantity}</td><td class="borrow-purpose">${escapeHtml(notes || '—')}</td><td>${escapeHtml(department || '—')}</td><td class="borrow-date">${escapeHtml(requestedFor)}</td><td class="borrow-date">${escapeHtml(expectedReturnDate)}</td><td><span class="borrow-status ${statusClass}">${statusCell}</span></td>`;
                } else {
                    const statusClass = `status-${String(t.status || '').toLowerCase().replace(/[^a-z0-9_-]/g, '')}`;
                    html += `<tr class="link-row" data-id="${t.id}"><td class="transaction-id">${t.id}</td><td class="transaction-item">${t.item?escapeHtml(t.item.name):t.item_id}</td><td class="transaction-user">${t.user?escapeHtml(t.user.name):t.user_id}</td><td class="transaction-type-cell">${typeLabel}</td><td class="transaction-quantity">${t.quantity}</td><td>${escapeHtml(department)}</td><td class="transaction-date">${escapeHtml(requestedFor)}</td><td class="transaction-date">${escapeHtml(expectedReturnDate)}</td><td>${escapeHtml(t.condition||'Not recorded')}</td><td><span class="transaction-status ${statusClass}">${statusCell}</span></td><td class="transaction-notes">${escapeHtml(notes || '—')}</td><td class="transaction-date">${escapeHtml(formatManilaDateTime(t.created_at))}`;
                }
                if(canEdit || canManage){ html += `<td class="${borrowingOnly ? '' : 'transaction-actions'}"><div class="action-buttons">${actions}</div></td>`; }
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
            const requestedDateLabel = t.transaction_type === 'issue' ? 'Issue date' : (t.transaction_type === 'borrow' ? 'Date borrowed' : 'Requested date &amp; time');
            const requestedDate = t.requested_for_at
                ? formatManilaDateTime(t.requested_for_at, t.transaction_type !== 'issue')
                : 'N/A';
            html += `<div><strong>${requestedDateLabel}:</strong> ${escapeHtml(requestedDate)}</div>`;
            html += `<div><strong>Expected return:</strong> ${escapeHtml(formatManilaDateTime(t.expected_return_at, false))}</div>`;
            html += `<div><strong>Returned:</strong> ${escapeHtml(formatManilaDateTime(t.returned_at))}</div>`;
            html += `<div><strong>Condition:</strong> ${escapeHtml(t.condition||'Not recorded')}</div>`;
            html += `<div><strong>Notes:</strong> ${escapeHtml(t.notes||'')}</div>`;
            if (t.rejection_reason) html += `<div><strong>Rejection reason:</strong> ${escapeHtml(t.rejection_reason)}</div>`;
            html += `<div><strong>Date:</strong> ${escapeHtml(formatManilaDateTime(t.created_at))}</div>`;
            if ((t.transaction_type || '').toLowerCase() === 'issue') {
                const receiptUrl = @json(route('inventory.transactions.receipt', ['id' => 'TRANSACTION_ID']));
                html += `<div style="margin-top:16px"><a class="btn" href="${receiptUrl.replace('TRANSACTION_ID', encodeURIComponent(t.id))}">Download Memorandum Receipt (.docx)</a></div>`;
            }
            if(item){ html += `<div style="margin-top:8px"><strong>Current Item Qty:</strong> ${item.quantity||'N/A'}</div>`; }
            detailContent.innerHTML = html;
        }

        document.addEventListener('click', async (e)=>{
            if(e.target.matches('.return-borrow')){
                e.stopPropagation();
                const button = e.target;
                const condition = prompt('Returned condition: Good, Damaged, Lost, or For Repair', 'Good');
                if(condition === null) return;
                const allowedConditions = ['Good', 'Damaged', 'Lost', 'For Repair'];
                if(!allowedConditions.includes(condition.trim())){
                    alert('Choose Good, Damaged, Lost, or For Repair.');
                    return;
                }
                showConfirm(`Mark this borrowed item as returned in ${condition.trim()} condition? Inventory stock is restored only for items in Good condition.`, async ()=>{
                    const response = await fetch('/inventory/transactions', {
                        method:'POST',
                        headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},
                        body:JSON.stringify({
                            item_id:button.dataset.itemId,
                            user_id:button.dataset.userId,
                            transaction_type:'return',
                            quantity:button.dataset.quantity,
                            condition:condition.trim(),
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
        createType.addEventListener('change', updateBorrowFields);
        document.getElementById('c_item_id').addEventListener('change', updateBorrowFields);
        updateBorrowFields();

        createForm.addEventListener('submit', async (e)=>{
            e.preventDefault();
            const isBorrow = createType.value === 'borrow';
            const purpose = createPurpose.value.trim();
            if (isBorrow && !purpose) {
                document.getElementById('createMessage').textContent = 'Please specify the purpose for this borrowing request.';
                return;
            }

            const payload = {
                item_id: document.getElementById('c_item_id').value,
                transaction_type: createType.value,
                quantity: document.getElementById('c_quantity').value,
                user_id: document.getElementById('c_user_id').value || undefined,
                department_id: document.getElementById('c_department_id').value || undefined,
                notes: document.getElementById('c_notes').value || undefined,
            };
            if (isBorrow || createType.value === 'issue') {
                payload.expected_return_at = expectedReturn.value || undefined;
            }
            if (isBorrow) {
                payload.purpose = purpose;
            }
            if (isBorrow || createType.value === 'issue') {
                payload.requested_for_at = requestedAt.value || undefined;
            }

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
                document.getElementById('createMessage').textContent = borrowingOnly ? 'Borrow request submitted for approval.' : 'Transaction created.';
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
