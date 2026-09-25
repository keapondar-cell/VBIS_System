<style>
    .audit-page { background: radial-gradient(circle at 100% 0, rgba(47,154,145,.12), transparent 28rem), linear-gradient(135deg, #f4f8f8 0%, #e8f1f2 100%); }
    .audit-page-title { position: relative; display: inline-block; color: #123b5d !important; }
    .audit-page-title::after { content: ""; display: block; width: 3.5rem; height: 4px; margin-top: .45rem; border-radius: 4px; background: #d99b2b; }
    .audit-shell { border: 1px solid #d8e5e8; border-top: 4px solid #2f9a91; box-shadow: 0 16px 40px rgba(18,59,93,.08); }
    .audit-heading { color: #123b5d !important; }
    .audit-description { color: #536879 !important; }
    .audit-search input { border-color: #b9d0d5; }
    .audit-search input:focus { border-color: #287f92; box-shadow: 0 0 0 3px rgba(47,154,145,.16); outline: none; }
    .audit-table { border-color: #d8e5e8 !important; }
    .audit-table thead { background: #dcefed !important; color: #123b5d; }
    .audit-table tbody tr { background: rgba(255,255,255,.82); transition: background .2s; }
    .audit-table tbody tr:hover { background: #fff; }
    .audit-reset { color: #123b5d; border-color: #b9d0d5; background: #fff; }
    @media (max-width: 700px) { .audit-page { padding: 1rem 0; } .audit-shell { padding: 1rem !important; } .audit-search > div { flex-wrap: wrap; } .audit-search input { width: 100%; } }
</style>

<x-app-layout>
    <x-slot name="header">
        <h2 class="audit-page-title font-semibold text-xl text-gray-800 leading-tight">Audit Trail</h2>
    </x-slot>

    <div class="audit-page py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="audit-shell bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="audit-heading text-lg font-bold text-gray-900">System Activity</h3>
                        <p class="audit-description text-sm text-gray-600">Authorized user actions and inventory-related system events.</p>
                    </div>
                </div>

                <form method="GET" class="audit-search mb-6">
                    <div class="flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search activity, item, or notes" class="border rounded-md px-3 py-2 w-full md:w-96">
                        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md">Search</button>
                        <a href="{{ route('inventory.audit_trail') }}" class="audit-reset px-4 py-2 border border-gray-300 rounded-md">Reset</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="audit-table min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">Date/Time</th>
                                <th class="px-3 py-2 text-left">User</th>
                                <th class="px-3 py-2 text-left">Action</th>
                                <th class="px-3 py-2 text-left">Item</th>
                                <th class="px-3 py-2 text-left">Quantity</th>
                                <th class="px-3 py-2 text-left">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($events as $event)
                                <tr class="border-t border-gray-200">
                                    <td class="px-3 py-2">{{ $event->created_at ? $event->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $event->user->name ?? 'System' }}</td>
                                    <td class="px-3 py-2 capitalize">{{ $event->transaction_type ?? 'System action' }}</td>
                                    <td class="px-3 py-2">{{ $event->item->name ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $event->quantity }}</td>
                                    <td class="px-3 py-2">{{ $event->notes ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-6 text-center text-gray-500">No audit events found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $events->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
