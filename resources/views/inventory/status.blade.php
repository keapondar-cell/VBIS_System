<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inventory Status</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Current Inventory Overview</h3>
                        <p class="text-sm text-gray-600">Monitor inventory status, stock levels, and locations in real time.</p>
                    </div>
                    <a href="{{ route('inventory.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Back to Dashboard</a>
                </div>

                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item or code" class="border rounded-md px-3 py-2">
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
                    <div class="md:col-span-4 flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md">Search</button>
                        <a href="{{ route('inventory.status') }}" class="px-4 py-2 border border-gray-300 rounded-md">Reset</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left">Item Code</th>
                                <th class="px-3 py-2 text-left">Name</th>
                                <th class="px-3 py-2 text-left">Category</th>
                                <th class="px-3 py-2 text-left">Quantity</th>
                                <th class="px-3 py-2 text-left">Location</th>
                                <th class="px-3 py-2 text-left">Status</th>
                                <th class="px-3 py-2 text-left">QR</th>
                                <th class="px-3 py-2 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr class="border-t border-gray-200">
                                    <td class="px-3 py-2">{{ $item->sku ?? 'ITM-'.$item->id }}</td>
                                    <td class="px-3 py-2">{{ $item->name }}</td>
                                    <td class="px-3 py-2">{{ $item->category ?? 'N/A' }}</td>
                                    <td class="px-3 py-2 font-semibold">{{ $item->quantity }}</td>
                                    <td class="px-3 py-2">{{ $item->location ?? 'N/A' }}</td>
                                    <td class="px-3 py-2">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            @if(strtolower($item->status ?? '') === 'out of stock') bg-red-100 text-red-700
                                            @elseif(strtolower($item->status ?? '') === 'low stock') bg-yellow-100 text-yellow-700
                                            @elseif(strtolower($item->status ?? '') === 'available') bg-green-100 text-green-700
                                            @else bg-blue-100 text-blue-700
                                            @endif">
                                            {{ ucfirst($item->status ?? 'available') }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('inventory.items.qr_image', $item->id) }}" target="_blank" class="text-blue-600">View QR</a>
                                    </td>
                                    <td class="px-3 py-2">
                                        <a href="{{ route('inventory.qr.item', $item->id) }}" class="text-blue-600">View</a>
                                    </td>
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
