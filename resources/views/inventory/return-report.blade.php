<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Return Report</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @include('reports.school-header')
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Item Returns Summary</h3>
                        <p class="text-sm text-gray-600">All returned items and their details retrieved from inventory movement records.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <x-export-link href="{{ route('inventory.reports.export', ['type' => 'transactions', 'format' => 'csv']) }}" type="csv" label="Export return report as CSV" />
                        <x-export-link href="{{ route('inventory.reports.export', ['type' => 'transactions', 'format' => 'pdf']) }}" type="pdf" label="Export return report as PDF" />
                    </div>
                </div>

                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search returner or item" class="border rounded-md px-3 py-2">
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md">Search</button>
                        <a href="{{ route('inventory.reports.returns') }}" class="px-4 py-2 border border-gray-300 rounded-md">Reset</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">Returner</th>
                                <th class="px-3 py-2 text-left">Item</th>
                                <th class="px-3 py-2 text-left">Item Code</th>
                                <th class="px-3 py-2 text-left">Classification</th>
                                <th class="px-3 py-2 text-left">Quantity</th>
                                <th class="px-3 py-2 text-left">Return Date</th>
                                <th class="px-3 py-2 text-left">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr class="border-t border-gray-200">
                                    <td class="px-3 py-2">{{ $report->user->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $report->item->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $report->item->sku ?? 'ITM-'.$report->item_id }}</td>
                                    <td class="px-3 py-2">{{ $report->item->classification ?? $report->item->category ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $report->quantity }}</td>
                                    <td class="px-3 py-2">{{ \App\Support\SchoolTime::format($report->created_at, 'M d, Y h:i A') }}</td>
                                    <td class="px-3 py-2 text-xs">{{ $report->notes ?? 'No notes' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-6 text-center text-gray-500">No return records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($reports->hasPages())
                    <div class="mt-4">
                        {{ $reports->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
