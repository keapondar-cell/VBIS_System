<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Item Information</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">QR Inventory Record</h3>
                        <p class="text-sm text-gray-600">Digital item information linked to the physical inventory.</p>
                    </div>
                    <a href="{{ route('inventory.qr.scan') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Scan Again</a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold mb-4">Item Details</h4>
                        <dl class="space-y-2 text-sm">
                            <div><dt class="text-gray-500">Item Code</dt><dd class="font-medium">{{ $item->sku ?? 'ITM-'.$item->id }}</dd></div>
                            <div><dt class="text-gray-500">Item Name</dt><dd class="font-medium">{{ $item->name }}</dd></div>
                            <div><dt class="text-gray-500">Classification</dt><dd>{{ $item->classification ?? $item->category ?? 'N/A' }}</dd></div>
                            <div><dt class="text-gray-500">Description</dt><dd>{{ $item->description ?? 'N/A' }}</dd></div>
                            <div><dt class="text-gray-500">Current Quantity</dt><dd>{{ $item->quantity }}</dd></div>
                            <div><dt class="text-gray-500">Location</dt><dd>{{ $item->location ?? 'N/A' }}</dd></div>
                            <div><dt class="text-gray-500">Status</dt><dd>{{ ucfirst($item->status ?? 'available') }}</dd></div>
                            <div><dt class="text-gray-500">Last Updated</dt><dd>{{ $item->updated_at ? $item->updated_at->format('F d, Y h:i A') : 'N/A' }}</dd></div>
                        </dl>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4 flex flex-col items-center justify-center">
                        <h4 class="font-semibold mb-3">QR Identifier</h4>
                        <img src="{{ route('inventory.items.qr_image', $item->id) }}" alt="QR code" class="w-44 h-44 border rounded-lg bg-white p-3">
                        <div class="mt-3 text-xs text-gray-500">QR record: {{ $item->qr_code ?? 'ITEM-'.$item->id }}</div>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold mb-4">Recent History</h4>
                        @if($item->transactions->isNotEmpty())
                            <div class="space-y-2 text-xs">
                                @foreach($item->transactions as $tx)
                                    <div class="border-b pb-2">
                                        <div class="font-medium text-gray-700">{{ ucfirst($tx->transaction_type ?? 'update') }}</div>
                                        <div>{{ $tx->quantity }} units</div>
                                        <div class="text-gray-500">{{ $tx->created_at ? $tx->created_at->format('M d, Y h:i A') : 'N/A' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">No transaction history found.</p>
                        @endif
                    </div>
                </div>

                @if($action === 'borrow' && $canBorrow)
                    <section class="mt-6 border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold mb-2">Borrow {{ $item->name }}</h4>
                        @if($item->quantity > 0)
                            <p class="mb-3 text-sm text-gray-600">Submit a borrow request for approval. Available quantity: {{ $item->quantity }}.</p>
                            <form id="qrBorrowForm" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <label class="text-sm">Quantity
                                    <input name="quantity" type="number" min="1" max="{{ $item->quantity }}" value="1" required class="mt-1 w-full border rounded-md px-3 py-2">
                                </label>
                                <label class="text-sm">Return date
                                    <input name="expected_return_at" type="date" min="{{ now()->addDay()->toDateString() }}" required class="mt-1 w-full border rounded-md px-3 py-2">
                                </label>
                                <label class="text-sm md:col-span-2">Purpose
                                    <input name="purpose" type="text" minlength="5" maxlength="500" required class="mt-1 w-full border rounded-md px-3 py-2" placeholder="Why do you need to borrow this item?">
                                </label>
                                <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Submit Borrow Request</button></div>
                                <p id="qrBorrowMessage" class="md:col-span-2 text-sm" role="status"></p>
                            </form>
                        @else
                            <p class="text-sm text-red-700">This item is out of stock and cannot be borrowed.</p>
                        @endif
                    </section>
                @elseif($action === 'return')
                    <section class="mt-6 border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold mb-2">Return {{ $item->name }}</h4>
                        @if($activeBorrows->isNotEmpty())
                            <p class="mb-3 text-sm text-gray-600">Select the active borrow record to return. Items in good condition are restored to stock.</p>
                            <form id="qrReturnForm" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @if($canManageReturns)
                                    <label class="text-sm">Borrower
                                        <select id="qrReturnUser" class="mt-1 w-full border rounded-md px-3 py-2">
                                            @foreach($activeBorrows as $borrow)
                                                <option value="{{ $borrow->user_id }}" data-quantity="{{ $borrow->quantity }}">{{ $borrow->user?->name ?? 'User '.$borrow->user_id }} — {{ $borrow->quantity }} unit(s)</option>
                                            @endforeach
                                        </select>
                                    </label>
                                @else
                                    <input type="hidden" id="qrReturnUser" value="{{ auth()->id() }}">
                                    <p class="text-sm">Your active borrow: {{ $activeBorrows->first()->quantity }} unit(s)</p>
                                @endif
                                <label class="text-sm">Condition
                                    <select name="condition" required class="mt-1 w-full border rounded-md px-3 py-2">
                                        <option value="Good">Good</option>
                                        <option value="Damaged">Damaged</option>
                                        <option value="Lost">Lost</option>
                                        <option value="For Repair">For Repair</option>
                                    </select>
                                </label>
                                <div class="md:col-span-2"><button type="submit" class="px-4 py-2 bg-green-700 text-white rounded-md">Confirm Return</button></div>
                                <p id="qrReturnMessage" class="md:col-span-2 text-sm" role="status"></p>
                            </form>
                        @else
                            <p class="text-sm text-gray-600">No approved active borrow record was found for this item.</p>
                        @endif
                    </section>
                @elseif($canBorrow || $activeBorrows->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-3">
                        @if($canBorrow)
                            <a href="{{ route('inventory.qr.scan', ['action' => 'borrow']) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md">Scan QR to Borrow</a>
                        @endif
                        <a href="{{ route('inventory.qr.scan', ['action' => 'return']) }}" class="inline-flex items-center px-4 py-2 bg-green-700 text-white rounded-md">Scan QR to Return</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const itemId = @json($item->id);

        async function submitQrTransaction(form, transactionType, messageElement, extraFields) {
            const formData = new FormData(form);
            const payload = Object.fromEntries(formData.entries());
            Object.assign(payload, extraFields);
            payload.item_id = itemId;
            payload.transaction_type = transactionType;

            const response = await fetch('/inventory/transactions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(payload),
            });
            const result = await response.json();

            if (!response.ok) {
                messageElement.textContent = Object.values(result.errors || {}).flat().join(' ') || result.error || result.message || 'Unable to submit this request.';
                messageElement.className = 'md:col-span-2 text-sm text-red-700';
                return;
            }

            messageElement.textContent = transactionType === 'borrow'
                ? 'Borrow request submitted and awaiting approval.'
                : 'Return recorded successfully.';
            messageElement.className = 'md:col-span-2 text-sm text-green-700';
            form.querySelector('button[type="submit"]').disabled = true;
        }

        document.getElementById('qrBorrowForm')?.addEventListener('submit', async event => {
            event.preventDefault();
            await submitQrTransaction(event.currentTarget, 'borrow', document.getElementById('qrBorrowMessage'), {});
        });

        document.getElementById('qrReturnForm')?.addEventListener('submit', async event => {
            event.preventDefault();
            const selectedBorrower = document.getElementById('qrReturnUser');
            const option = selectedBorrower.selectedOptions?.[0];
            const userId = selectedBorrower.value;
            const quantity = option?.dataset.quantity || @json($activeBorrows->first()?->quantity);
            await submitQrTransaction(event.currentTarget, 'return', document.getElementById('qrReturnMessage'), {
                user_id: userId,
                quantity: Number(quantity),
                notes: 'Return submitted from item QR scan',
            });
        });
    </script>
</x-app-layout>
