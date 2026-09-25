<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryHistoryController;
use App\Http\Controllers\MaterialIssuanceController;
use App\Http\Controllers\InventoryMonitoringController;
use App\Http\Controllers\InventoryDocsController;
use App\Http\Controllers\RoleManagementController;

use Illuminate\Http\Request;

Route::get('/', function (Request $request) {
    // Force logout and invalidate session so the login page is always shown first
    if (auth()->check()) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('inventory.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin routes
Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('users', [RoleManagementController::class, 'index'])->name('users');
    Route::get('users/{id}/edit', [RoleManagementController::class, 'editView'])->name('users.edit');
    Route::patch('users/{id}', [RoleManagementController::class, 'update'])->name('users.update');
    Route::delete('users/{id}', [RoleManagementController::class, 'destroy'])->name('users.destroy');
    Route::post('users/{id}/role', [RoleManagementController::class, 'updateRole'])->name('users.role');
    Route::post('users/{id}/toggle-status', [RoleManagementController::class, 'toggleStatus'])->name('users.toggle_status');
    Route::post('users/{id}/approve', [RoleManagementController::class, 'approveUser'])->name('users.approve');
    Route::post('users/{id}/reject', [RoleManagementController::class, 'rejectUser'])->name('users.reject');
    Route::get('users/export', [RoleManagementController::class, 'export'])->name('users.export');
});

Route::prefix('inventory')->middleware('auth')->name('inventory.')->group(function () {
    // Inventory history / transactions
    Route::get('transactions', [InventoryHistoryController::class, 'index'])->name('transactions.index');
    Route::get('transactions/list', [InventoryHistoryController::class, 'transactionsView'])->name('transactions.list');
    Route::get('transactions/{id}', [InventoryHistoryController::class, 'show'])->whereNumber('id')->name('transactions.show');
    Route::post('transactions', [InventoryHistoryController::class, 'store'])->name('transactions.store');
    Route::post('transactions/{id}/approve', [InventoryHistoryController::class, 'approve'])->name('transactions.approve');
    Route::post('transactions/{id}/reject', [InventoryHistoryController::class, 'reject'])->name('transactions.reject');
    Route::put('transactions/{id}', [InventoryHistoryController::class, 'update'])->name('transactions.update');
    Route::patch('transactions/{id}/condition', [InventoryHistoryController::class, 'updateCondition'])->name('transactions.condition');
    Route::delete('transactions/{id}', [InventoryHistoryController::class, 'destroy'])->name('transactions.destroy');

    // Material issues (requests / approvals)
    Route::get('issues', [MaterialIssuanceController::class, 'index'])->name('issues.index');
    Route::get('issues/list', [MaterialIssuanceController::class, 'issuesView'])->name('issues.list');
    Route::get('issues/{id}', [MaterialIssuanceController::class, 'show'])->name('issues.show');
    Route::post('issues', [MaterialIssuanceController::class, 'store'])->name('issues.store');
    Route::post('issues/{id}/approve', [MaterialIssuanceController::class, 'approve'])->name('issues.approve');
    Route::post('issues/{id}/reject', [MaterialIssuanceController::class, 'reject'])->name('issues.reject');
    Route::patch('issues/{id}', [MaterialIssuanceController::class, 'update'])->name('issues.update');
    Route::put('issues/{id}', [MaterialIssuanceController::class, 'update'])->name('issues.update');
    Route::delete('issues/{id}', [MaterialIssuanceController::class, 'destroy'])->name('issues.destroy');

    // Items and monitoring
    Route::get('items', [InventoryMonitoringController::class, 'index'])->name('items.index');
    Route::post('items', [InventoryMonitoringController::class, 'store'])->name('items.store');
    Route::put('items/{id}', [InventoryMonitoringController::class, 'update'])->name('items.update');
    Route::delete('items/{id}', [InventoryMonitoringController::class, 'destroy'])->name('items.destroy');
    Route::get('items/list', [InventoryMonitoringController::class, 'itemsView'])->name('items.list');
    Route::get('items/export', [InventoryMonitoringController::class, 'exportItems'])->name('items.export');
    Route::get('items/search', [InventoryMonitoringController::class, 'search'])->name('items.search');
    Route::get('items/{id}', [InventoryMonitoringController::class, 'show'])->name('items.show');
    Route::get('items/{id}/qr-image', [InventoryMonitoringController::class, 'generateQrImage'])->name('items.qr_image');
    Route::get('items/{id}/qr-inline', [InventoryMonitoringController::class, 'generateQrInline'])->name('items.qr_inline');

    // Module 3 monitoring, QR, reports, history, and audit trail
    Route::get('status', [InventoryMonitoringController::class, 'statusView'])->name('status');
    Route::get('low-stock', [InventoryMonitoringController::class, 'lowStockView'])->name('low_stock');
    Route::get('qr/generate', [InventoryMonitoringController::class, 'qrGenerateView'])->name('qr.generate');
    Route::get('qr/scan', [InventoryMonitoringController::class, 'qrScanView'])->name('qr.scan');
    Route::get('qr/item/{id}', [InventoryMonitoringController::class, 'qrItemInformation'])->name('qr.item');
    Route::get('qr/borrow/{id}', [InventoryMonitoringController::class, 'borrowQrInformation'])->name('qr.borrow');
    Route::get('qr/borrow/{id}/image', [InventoryMonitoringController::class, 'generateBorrowQrImage'])->name('qr.borrow_image');
    Route::get('reports-menu', [InventoryMonitoringController::class, 'reportsMenuView'])->name('reports.menu');
    Route::get('reports', [InventoryMonitoringController::class, 'inventoryReportView'])->name('reports');
    Route::get('reports/borrowing', [InventoryMonitoringController::class, 'borrowingReportView'])->name('reports.borrowing');
    Route::get('reports/returns', [InventoryMonitoringController::class, 'returnReportView'])->name('reports.returns');
    Route::get('reports/issuance', [InventoryMonitoringController::class, 'issuanceReportView'])->name('reports.issuance');
    Route::get('reports/low-stock', [InventoryMonitoringController::class, 'lowStockReportView'])->name('reports.low_stock');
    Route::get('reports/export', [InventoryMonitoringController::class, 'exportReport'])->name('reports.export');
    Route::get('transactions/history', [InventoryMonitoringController::class, 'transactionHistoryView'])->name('transactions.history');
    Route::get('audit-trail', [InventoryMonitoringController::class, 'auditTrailView'])->name('audit_trail');

    // Dashboard & docs
    Route::get('dashboard', [InventoryMonitoringController::class, 'dashboardView'])->name('dashboard');
    Route::get('dashboard/summary', [InventoryMonitoringController::class, 'dashboardSummary'])->name('dashboard.summary');
    Route::get('dashboard/realtime', [InventoryMonitoringController::class, 'realtimeData'])->name('dashboard.realtime');
    Route::get('dashboard/department-summary', [InventoryMonitoringController::class, 'departmentSummary'])->name('dashboard.department_summary');
    Route::get('notifications/unread-count', [InventoryMonitoringController::class, 'unreadNotificationCount'])->name('notifications.unread_count');
    Route::get('notifications', [InventoryMonitoringController::class, 'notifications'])->name('notifications.index');
    Route::post('notifications/{id}/read', [InventoryMonitoringController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('notifications/read-all', [InventoryMonitoringController::class, 'markAllNotificationsRead'])->name('notifications.read_all');
    // Adjustment requests
    Route::get('adjustments/index', [InventoryMonitoringController::class, 'adjustmentsIndex'])->name('adjustments.index');
    Route::get('adjustments/list', [InventoryMonitoringController::class, 'adjustmentsView'])->name('adjustments.list');
    Route::post('adjustments/{id}/approve', [InventoryMonitoringController::class, 'approveAdjustment'])->name('adjustments.approve');
    Route::post('adjustments/{id}/reject', [InventoryMonitoringController::class, 'rejectAdjustment'])->name('adjustments.reject');
    Route::get('openapi.json', [InventoryDocsController::class, 'openapi'])->name('openapi.json');
    Route::get('docs', [InventoryDocsController::class, 'docsView'])->name('docs');
});

require __DIR__.'/auth.php';
