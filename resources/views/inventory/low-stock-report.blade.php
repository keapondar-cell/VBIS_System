<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Low Stock Report</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Critical Inventory Items</h3>
                        <p class="text-sm text-gray-600">Items below or equal to the configured threshold.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <a href="{{ route('inventory.reports.export', ['type' => 'low_stock', 'format' => 'csv']) }}" style="display:inline-flex; align-items:center; justify-content:center; width:130px; height:42px; background:#0d6c3f; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); line-height:1; border:0;">Export CSV</a>
                        <a href="{{ route('inventory.reports.export', ['type' => 'low_stock', 'format' => 'pdf']) }}" style="display:inline-flex; align-items:center; justify-content:center; width:130px; height:42px; background:#d63c31; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); line-height:1; border:0;">Export PDF</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-red-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Item Code</th>
                                <th class="px-3 py-2 text-left">Item Name</th>
                                <th class="px-3 py-2 text-left">Category</th>
                                <th class="px-3 py-2 text-left">Current Quantity</th>
                                <th class="px-3 py-2 text-left">Threshold</th>
                                <th class="px-3 py-2 text-left">Location</th>
                                <th class="px-3 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr class="border-t border-gray-200">
                                    <td class="px-3 py-2">{{ $item->sku ?? 'ITM-'.$item->id }}</td>
                                    <td class="px-3 py-2">{{ $item->name }}</td>
                                    <td class="px-3 py-2">{{ $item->category ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 font-semibold text-red-700">{{ $item->quantity }}</td>
                                    <td class="px-3 py-2">{{ $threshold }}</td>
                                    <td class="px-3 py-2">{{ $item->location ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-700">Low Stock</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-gray-500">No low-stock items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
