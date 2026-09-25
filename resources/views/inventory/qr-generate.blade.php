<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Generate QR Code</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Inventory QR Labels</h3>
                        <p class="text-sm text-gray-600">Each item uses a unique QR identifier linked to its database record.</p>
                    </div>
                    <a href="{{ route('inventory.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Back to Dashboard</a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @forelse($items as $item)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <div class="font-semibold">{{ $item->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->sku ?? 'ITM-'.$item->id }}</div>
                                </div>
                                <span class="inline-flex px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">{{ $item->quantity }} qty</span>
                            </div>

                            <div class="flex justify-center mb-3">
                                <img src="{{ route('inventory.items.qr_image', $item->id) }}" alt="QR code for {{ $item->name }}" class="w-32 h-32 border rounded-md bg-white p-2">
                            </div>

                            <div class="text-xs text-gray-600 mb-3">
                                <div><strong>Location:</strong> {{ $item->location ?? 'N/A' }}</div>
                                <div><strong>Status:</strong> {{ ucfirst($item->status ?? 'available') }}</div>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('inventory.items.qr_image', $item->id) }}" target="_blank" class="inline-flex flex-1 justify-center px-3 py-2 bg-blue-600 text-white rounded-md text-xs">Preview QR</a>
                                <a href="{{ route('inventory.qr.item', $item->id) }}" class="inline-flex flex-1 justify-center px-3 py-2 border border-gray-300 rounded-md text-xs">Item Info</a>
                            </div>
                        </div>
                    @empty
                        <div class="md:col-span-2 xl:col-span-3 rounded border border-dashed border-gray-300 p-6 text-center text-gray-500">No inventory items available to generate QR codes.</div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
