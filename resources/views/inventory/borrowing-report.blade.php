<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Borrowing Report</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Borrowing Summary</h3>
                        <p class="text-sm text-gray-600">Borrowing and return transactions retrieved from current inventory movement records.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <a href="{{ route('inventory.reports.export', ['type' => 'transactions', 'format' => 'csv']) }}" style="display:inline-flex; align-items:center; justify-content:center; width:130px; height:42px; background:#0d6c3f; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); line-height:1; border:0;">Export CSV</a>
                        <a href="{{ route('inventory.reports.export', ['type' => 'transactions', 'format' => 'pdf']) }}" style="display:inline-flex; align-items:center; justify-content:center; width:130px; height:42px; background:#d63c31; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); line-height:1; border:0;">Export PDF</a>
                    </div>
                </div>

                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search borrower or item" class="border rounded-md px-3 py-2">
                    <select name="status" class="border rounded-md px-3 py-2">
                        <option value="">All Status</option>
                        <option value="borrowed" @selected(request('status') === 'borrowed')>Borrowed</option>
                        <option value="returned" @selected(request('status') === 'returned')>Returned</option>
                    </select>
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md">Search</button>
                        <a href="{{ route('inventory.reports.borrowing') }}" class="px-4 py-2 border border-gray-300 rounded-md">Reset</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">Borrower</th>
                                <th class="px-3 py-2 text-left">Item</th>
                                <th class="px-3 py-2 text-left">Item Code</th>
                                <th class="px-3 py-2 text-left">Department</th>
                                <th class="px-3 py-2 text-left">Quantity</th>
                                <th class="px-3 py-2 text-left">Borrow Date</th>
                                <th class="px-3 py-2 text-left">Expected Return Date</th>
                                <th class="px-3 py-2 text-left">Actual Return Date</th>
                                <th class="px-3 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                @php
                                    $status = strtolower($report->transaction_type ?? 'borrow');
                                    $statusLabel = $status === 'return' ? 'Returned' : 'Borrowed';
                                    $borrowDate = $report->transaction_type === 'borrow' ? ($report->created_at ? $report->created_at->format('M d, Y h:i A') : 'N/A') : 'N/A';
                                    $actualReturnDate = $report->transaction_type === 'return' ? ($report->created_at ? $report->created_at->format('M d, Y h:i A') : 'N/A') : 'N/A';
                                @endphp
                                <tr class="border-t border-gray-200">
                                    <td class="px-3 py-2">{{ $report->user->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $report->item->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $report->item->sku ?? 'ITM-'.$report->item_id }}</td>
                                    <td class="px-3 py-2">{{ $report->department->name ?? ($report->department_id ? 'Department '.$report->department_id : 'N/A') }}</td>
                                    <td class="px-3 py-2">{{ $report->quantity }}</td>
                                    <td class="px-3 py-2">{{ $borrowDate }}</td>
                                    <td class="px-3 py-2">{{ $report->expected_return_at ? $report->expected_return_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $actualReturnDate }}</td>
                                    <td class="px-3 py-2"><span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusLabel === 'Returned' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">{{ $statusLabel }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-3 py-6 text-center text-gray-500">No borrowing records found.</td>
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
