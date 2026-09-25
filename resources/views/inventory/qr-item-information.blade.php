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
                            <div><dt class="text-gray-500">Category</dt><dd>{{ $item->category ?? 'N/A' }}</dd></div>
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
            </div>
        </div>
    </div>
</x-app-layout>
