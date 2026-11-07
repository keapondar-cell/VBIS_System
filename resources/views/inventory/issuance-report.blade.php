<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Issuance Report</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @include('reports.school-header')
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Issued Items Summary</h3>
                        <p class="text-sm text-gray-600">This report retrieves issuance details from current inventory requests and approvals.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <x-export-link href="{{ route('inventory.reports.export', ['type' => 'issuance', 'format' => 'csv']) }}" type="csv" label="Export issuance report as CSV" />
                        <x-export-link href="{{ route('inventory.reports.export', ['type' => 'issuance', 'format' => 'pdf']) }}" type="pdf" label="Export issuance report as PDF" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">Item Code</th>
                                <th class="px-3 py-2 text-left">Item Name</th>
                                <th class="px-3 py-2 text-left">Quantity Issued</th>
                                <th class="px-3 py-2 text-left">Recipient</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-left">Date/Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr class="border-t border-gray-200">
                                    <td class="px-3 py-2">{{ $report->item->sku ?? 'ITM-'.$report->item_id }}</td>
                                    <td class="px-3 py-2">{{ $report->item->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $report->quantity }}</td>
                                    <td class="px-3 py-2">{{ $report->issuedTo->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 capitalize">{{ $report->status }}</td>
                                    <td class="px-3 py-2">{{ \App\Support\SchoolTime::format($report->created_at, 'M d, Y h:i A') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-gray-500">No issuance records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
