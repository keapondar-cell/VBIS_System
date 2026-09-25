<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transaction History</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Inventory Movement Log</h3>
                        <p class="text-sm text-gray-600">Recent stock changes and user actions recorded in the system.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <a href="{{ route('inventory.reports.export', ['type' => 'transactions', 'format' => 'csv']) }}" style="display:inline-flex; align-items:center; justify-content:center; width:130px; height:42px; background:#0d6c3f; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); line-height:1; border:0;">Export CSV</a>
                        <a href="{{ route('inventory.reports.export', ['type' => 'transactions', 'format' => 'pdf']) }}" style="display:inline-flex; align-items:center; justify-content:center; width:130px; height:42px; background:#d63c31; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); line-height:1; border:0;">Export PDF</a>
                    </div>
                </div>

                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item or type" class="border rounded-md px-3 py-2">
                    <select name="transaction_type" class="border rounded-md px-3 py-2">
                        <option value="">All Types</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" @selected(request('transaction_type') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="status" value="{{ request('status') }}" placeholder="Status keyword" class="border rounded-md px-3 py-2">
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md">Search</button>
                        <a href="{{ route('inventory.transactions.history') }}" class="px-4 py-2 border border-gray-300 rounded-md">Reset</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">Date/Time</th>
                                <th class="px-3 py-2 text-left">User</th>
                                <th class="px-3 py-2 text-left">Item</th>
                                <th class="px-3 py-2 text-left">Type</th>
                                <th class="px-3 py-2 text-left">Qty</th>
                                <th class="px-3 py-2 text-left">Department</th>
                                <th class="px-3 py-2 text-left">Expected Return</th>
                                <th class="px-3 py-2 text-left">Returned</th>
                                <th class="px-3 py-2 text-left">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr class="border-t border-gray-200">
                                    <td class="px-3 py-2">{{ $transaction->created_at ? $transaction->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $transaction->user->name ?? 'System' }}</td>
                                    <td class="px-3 py-2">{{ $transaction->item->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ ucfirst($transaction->transaction_type ?? 'update') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->quantity }}</td>
                                    <td class="px-3 py-2">{{ $transaction->department->name ?? ($transaction->department_id ? 'Department '.$transaction->department_id : 'N/A') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->expected_return_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $transaction->returned_at?->format('M d, Y h:i A') ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $transaction->notes ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-3 py-6 text-center text-gray-500">No transaction history found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
