<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Transaction History</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                @include('reports.school-header')
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Inventory Movement Log</h3>
                        <p class="text-sm text-gray-600">Recent stock changes and user actions recorded in the system.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <x-export-link href="{{ route('inventory.reports.export', ['type' => 'transactions', 'format' => 'csv']) }}" type="csv" label="Export transaction history as CSV" />
                        <x-export-link href="{{ route('inventory.reports.export', ['type' => 'transactions', 'format' => 'pdf']) }}" type="pdf" label="Export transaction history as PDF" />
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
                                <th class="px-3 py-2 text-left">Issue / Borrow Date</th>
                                <th class="px-3 py-2 text-left">Expected Return</th>
                                <th class="px-3 py-2 text-left">Returned</th>
                                <th class="px-3 py-2 text-left">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr class="border-t border-gray-200">
                                    <td class="px-3 py-2">{{ \App\Support\SchoolTime::format($transaction->created_at, 'M d, Y h:i A') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->user->name ?? 'System' }}</td>
                                    <td class="px-3 py-2">{{ $transaction->item->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ ucfirst($transaction->transaction_type ?? 'update') }}</td>
                                    <td class="px-3 py-2">{{ $transaction->quantity }}</td>
                                    <td class="px-3 py-2">{{ $transaction->department->name ?? ($transaction->department_id ? 'Department '.$transaction->department_id : 'N/A') }}</td>
                                    <td class="px-3 py-2">{{ \App\Support\SchoolTime::format($transaction->requested_for_at, $transaction->transaction_type === 'issue' ? 'M d, Y' : 'M d, Y h:i A') }}</td>
                                    <td class="px-3 py-2">{{ \App\Support\SchoolTime::format($transaction->expected_return_at, 'M d, Y') }}</td>
                                    <td class="px-3 py-2">{{ \App\Support\SchoolTime::format($transaction->returned_at, 'M d, Y h:i A') }}</td>
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
