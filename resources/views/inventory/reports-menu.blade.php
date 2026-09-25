<style>
    .reports-page { background: radial-gradient(circle at 100% 0, rgba(47,154,145,.12), transparent 28rem), linear-gradient(135deg, #f4f8f8 0%, #e8f1f2 100%); }
    .reports-page-title { position: relative; display: inline-block; color: #123b5d !important; }
    .reports-page-title::after { content: ""; display: block; width: 3.5rem; height: 4px; margin-top: .45rem; border-radius: 4px; background: #d99b2b; }
    .reports-shell { border: 1px solid #d8e5e8; border-top: 4px solid #2f9a91; box-shadow: 0 16px 40px rgba(18,59,93,.08); }
    .reports-heading { color: #123b5d !important; }
    .reports-description { color: #536879 !important; }
    .report-export-pdf { background: #d32f2f !important; }
    .report-export-pdf:hover { background: #b71c1c !important; }
    .report-export-excel { background: #217346 !important; }
    .report-export-excel:hover { background: #185c37 !important; }
</style>

<x-app-layout>
    <x-slot name="header">
        <h2 class="reports-page-title font-semibold text-xl text-gray-800 leading-tight">Reports</h2>
    </x-slot>

    <div class="reports-page py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="reports-shell bg-white shadow sm:rounded-lg p-6">
                <div class="mb-8">
                    <h3 class="reports-heading text-lg font-bold text-gray-900 mb-2">Available Reports</h3>
                    <p class="reports-description text-sm text-gray-600">Select a report to view detailed information about your inventory.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="md:col-span-2 lg:col-span-3 flex flex-wrap gap-2">
                        <a href="{{ route('inventory.reports.export', ['type' => 'departments', 'format' => 'pdf']) }}" class="report-export-pdf px-4 py-2 bg-blue-700 text-white rounded-md">Department PDF</a>
                        <a href="{{ route('inventory.reports.export', ['type' => 'departments', 'format' => 'excel']) }}" class="report-export-excel px-4 py-2 bg-green-700 text-white rounded-md">Department Excel</a>
                        <a href="{{ route('inventory.reports.export', ['type' => 'transactions', 'format' => 'pdf']) }}" class="report-export-pdf px-4 py-2 bg-blue-700 text-white rounded-md">Transactions PDF</a>
                        <a href="{{ route('inventory.reports.export', ['type' => 'transactions', 'format' => 'excel']) }}" class="report-export-excel px-4 py-2 bg-green-700 text-white rounded-md">Transactions Excel</a>
                    </div>
                    <!-- Inventory Report -->
                    <a href="{{ route('inventory.reports') }}" class="block p-6 bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg hover:shadow-lg hover:border-blue-400 transition-all">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center text-white text-xl">📦</div>
                            <h4 class="ml-3 font-semibold text-gray-900">Inventory Status</h4>
                        </div>
                        <p class="text-sm text-gray-600">View all items in your inventory with their current quantities, locations, and status.</p>
                    </a>

                    <!-- Issuance Report -->
                    <a href="{{ route('inventory.reports.issuance') }}" class="block p-6 bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg hover:shadow-lg hover:border-green-400 transition-all">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center text-white text-xl">📤</div>
                            <h4 class="ml-3 font-semibold text-gray-900">Issuance Report</h4>
                        </div>
                        <p class="text-sm text-gray-600">Track all material issuances including approved, pending, and rejected requests.</p>
                    </a>

                    <!-- Borrowing Report -->
                    <a href="{{ route('inventory.reports.borrowing') }}" class="block p-6 bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg hover:shadow-lg hover:border-purple-400 transition-all">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center text-white text-xl">🔄</div>
                            <h4 class="ml-3 font-semibold text-gray-900">Borrowing Report</h4>
                        </div>
                        <p class="text-sm text-gray-600">View borrowing and return transactions with borrower and item details.</p>
                    </a>

                    <!-- Return Report -->
                    <a href="{{ route('inventory.reports.returns') }}" class="block p-6 bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg hover:shadow-lg hover:border-orange-400 transition-all">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center text-white text-xl">📥</div>
                            <h4 class="ml-3 font-semibold text-gray-900">Return Report</h4>
                        </div>
                        <p class="text-sm text-gray-600">View all returned items with return dates and related information.</p>
                    </a>

                    <!-- Low Stock Report -->
                    <a href="{{ route('inventory.reports.low_stock') }}" class="block p-6 bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-lg hover:shadow-lg hover:border-red-400 transition-all">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center text-white text-xl">⚠️</div>
                            <h4 class="ml-3 font-semibold text-gray-900">Low Stock Report</h4>
                        </div>
                        <p class="text-sm text-gray-600">Identify items below threshold to plan for replenishment.</p>
                    </a>

                    <!-- Transaction History -->
                    <a href="{{ route('inventory.transactions.history') }}" class="block p-6 bg-gradient-to-br from-indigo-50 to-indigo-100 border border-indigo-200 rounded-lg hover:shadow-lg hover:border-indigo-400 transition-all">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center text-white text-xl">📋</div>
                            <h4 class="ml-3 font-semibold text-gray-900">Transaction History</h4>
                        </div>
                        <p class="text-sm text-gray-600">Complete audit trail of all inventory transactions and movements.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
