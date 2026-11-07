<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Low Stock Report</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @include('reports.school-header')
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Critical Inventory Items</h3>
                        <p class="text-sm text-gray-600">Items below or equal to the configured threshold.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <x-export-link href="{{ route('inventory.reports.export', ['type' => 'low_stock', 'format' => 'csv']) }}" type="csv" label="Export low stock report as CSV" />
                        <x-export-link href="{{ route('inventory.reports.export', ['type' => 'low_stock', 'format' => 'pdf']) }}" type="pdf" label="Export low stock report as PDF" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-red-50">
                            <tr>
                                <th class="px-3 py-2 text-left">Item Code</th>
                                <th class="px-3 py-2 text-left">Item Name</th>
                                <th class="px-3 py-2 text-left">Classification</th>
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
                                    <td class="px-3 py-2">{{ $item->classification ?? $item->category ?? 'N/A' }}</td>
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
