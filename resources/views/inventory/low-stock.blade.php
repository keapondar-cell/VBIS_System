<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Low Stock Monitoring</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Critical Stock Items</h3>
                        <p class="text-sm text-gray-600">Threshold: {{ $threshold }} units or lower</p>
                    </div>
                    <a href="{{ route('inventory.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Back to Dashboard</a>
                </div>

                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search item" class="border rounded-md px-3 py-2">
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
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-md">Search</button>
                        <a href="{{ route('inventory.low_stock') }}" class="px-4 py-2 border border-gray-300 rounded-md">Reset</a>
                    </div>
                </form>

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
