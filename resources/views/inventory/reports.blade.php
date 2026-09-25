<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inventory Report</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Inventory Status Report</h3>
                        <p class="text-sm text-gray-600">Current inventory data retrieved from the database.</p>
                    </div>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <a href="{{ route('inventory.reports.export', ['type' => 'inventory', 'format' => 'csv']) }}" style="display:inline-flex; align-items:center; justify-content:center; width:130px; height:42px; background:#0d6c3f; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); line-height:1; border:0;">Export CSV</a>
                        <a href="{{ route('inventory.reports.export', ['type' => 'inventory', 'format' => 'pdf']) }}" style="display:inline-flex; align-items:center; justify-content:center; width:130px; height:42px; background:#d63c31; color:#ffffff; font-size:16px; font-weight:700; text-decoration:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); line-height:1; border:0;">Export PDF</a>
                    </div>
                </div>

                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-3 mb-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item" class="border rounded-md px-3 py-2 md:col-span-2">
                    <select name="category" class="border rounded-md px-3 py-2">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                        @endforeach
                    </select>
                    <select name="location" class="border rounded-md px-3 py-2">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location }}" @selected(request('location') === $location)>{{ $location }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="border rounded-md px-3 py-2">
                        <option value="">All Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <div class="md:col-span-5 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md">Search</button>
                        <a href="{{ route('inventory.reports') }}" class="px-4 py-2 border border-gray-300 rounded-md">Reset</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">Item Code</th>
                                <th class="px-3 py-2 text-left">Item Name</th>
                                <th class="px-3 py-2 text-left">Category</th>
                                <th class="px-3 py-2 text-left">Description</th>
                                <th class="px-3 py-2 text-left">Quantity</th>
                                <th class="px-3 py-2 text-left">Location</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-left">Last Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr class="border-t border-gray-200">
                                    <td class="px-3 py-2">{{ $item->sku ?? 'ITM-'.$item->id }}</td>
                                    <td class="px-3 py-2">{{ $item->name }}</td>
                                    <td class="px-3 py-2">{{ $item->category ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ $item->description ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 font-semibold">{{ $item->quantity }}</td>
                                    <td class="px-3 py-2">{{ $item->location ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">{{ ucfirst($item->status ?? 'available') }}</td>
                                    <td class="px-3 py-2">{{ $item->updated_at ? $item->updated_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-6 text-center text-gray-500">No inventory records found for the selected filters.</td>
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
