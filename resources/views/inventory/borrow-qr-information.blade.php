<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Borrowed Item Record</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Borrowed Item Information</h3>
                        <p class="text-sm text-gray-600">This QR record includes the item details and the borrower information.</p>
                    </div>
                    <a href="{{ route('inventory.qr.scan') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Scan Again</a>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold mb-4">Item Details</h4>
                        <dl class="space-y-2 text-sm">
                            <div><dt class="text-gray-500">Item Code</dt><dd class="font-medium">{{ $transaction->item->sku ?? 'ITM-'.$transaction->item_id }}</dd></div>
                            <div><dt class="text-gray-500">Item Name</dt><dd class="font-medium">{{ $transaction->item->name }}</dd></div>
                            <div><dt class="text-gray-500">Category</dt><dd>{{ $transaction->item->category ?? 'N/A' }}</dd></div>
                            <div><dt class="text-gray-500">Current Quantity</dt><dd>{{ $transaction->item->quantity }}</dd></div>
                            <div><dt class="text-gray-500">Location</dt><dd>{{ $transaction->item->location ?? 'N/A' }}</dd></div>
                            <div><dt class="text-gray-500">Borrow Quantity</dt><dd>{{ $transaction->quantity }}</dd></div>
                        </dl>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4 flex flex-col items-center justify-center">
                        <h4 class="font-semibold mb-3">Borrow QR</h4>
                        <img src="{{ route('inventory.qr.borrow_image', $transaction->id) }}" alt="Borrow QR code" class="w-44 h-44 border rounded-lg bg-white p-3">
                        <div class="mt-3 text-xs text-gray-500">Transaction ID: {{ $transaction->id }}</div>
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-semibold mb-4">Borrower Details</h4>
                        <dl class="space-y-2 text-sm">
                            <div><dt class="text-gray-500">Borrower Name</dt><dd class="font-medium">{{ $transaction->user->name ?? 'Unknown user' }}</dd></div>
                            <div><dt class="text-gray-500">Email</dt><dd>{{ $transaction->user->email ?? 'N/A' }}</dd></div>
                            <div><dt class="text-gray-500">Role</dt><dd>{{ ucfirst(str_replace('_', ' ', $transaction->user->role ?? 'user')) }}</dd></div>
                            <div><dt class="text-gray-500">Borrow Date</dt><dd>{{ $transaction->created_at ? $transaction->created_at->format('F d, Y h:i A') : 'N/A' }}</dd></div>
                            <div><dt class="text-gray-500">Expected Return</dt><dd>{{ $transaction->expected_return_at ? $transaction->expected_return_at->format('F d, Y h:i A') : 'N/A' }}</dd></div>
                            <div><dt class="text-gray-500">Notes</dt><dd>{{ $transaction->notes ?? 'N/A' }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
