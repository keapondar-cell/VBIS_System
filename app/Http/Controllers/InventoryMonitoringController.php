<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\MaterialIssue;
use Illuminate\Http\Request;
use App\Models\InventoryTransaction;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class InventoryMonitoringController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 5;
    private const LOW_STOCK_LIMIT = 10;
    private const RECENT_TRANSACTIONS_LIMIT = 10;

    public function index()
    {
        $items = Item::paginate(25);
        return response()->json($items);
    }

    public function unreadNotificationCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function notifications(Request $request)
    {
        $allNotifications = $request->user()->notifications()->latest()->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'message' => $notification->data['message'] ?? 'You have a new notification.',
                'type' => $notification->data['type'] ?? 'notification',
                'issue_id' => $notification->data['issue_id'] ?? null,
                'transaction_id' => $notification->data['transaction_id'] ?? null,
                'user_id' => $notification->data['user_id'] ?? null,
                'status' => $notification->data['status'] ?? null,
                'read' => ! is_null($notification->read_at),
                'created_at' => $notification->created_at?->toIso8601String(),
            ]);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'notifications' => $allNotifications->take(40),
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        $filter = strtolower((string) $request->query('filter', 'all'));
        $notifications = match ($filter) {
            'unread' => $allNotifications->filter(fn ($notification) => ! $notification['read'])->values(),
            'read' => $allNotifications->filter(fn ($notification) => $notification['read'])->values(),
            default => $allNotifications,
        };

        return view('inventory.notifications-page', [
            'notifications' => $notifications,
            'filter' => in_array($filter, ['all', 'unread', 'read'], true) ? $filter : 'all',
            'unread_count' => $allNotifications->where('read', false)->count(),
            'read_count' => $allNotifications->where('read', true)->count(),
            'total_count' => $allNotifications->count(),
        ]);
    }

    public function markNotificationRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllNotificationsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    public function show($id)
    {
        $item = Item::with(['transactions', 'issues'])->findOrFail($id);
        return response()->json($item);
    }

    public function qr($id)
    {
        $item = Item::findOrFail($id);
        return response()->json(['qr_code' => $item->qr_code, 'item' => $item]);
    }

    public function exportItems(Request $request)
    {
        $items = Item::all();

        $format = $request->input('format', 'csv');

        if ($format === 'excel') {
            return $this->downloadSpreadsheetXlsx(
                'Inventory Items',
                ['id', 'sku', 'name', 'category', 'location', 'quantity', 'status', 'qr_code', 'created_at'],
                $items->map(fn ($item) => [$item->id, $item->sku, $item->name, $item->category, $item->location, $item->quantity, $item->status, $item->qr_code, $item->created_at])->all(),
                'inventory_items_'.now()->format('Ymd_His').'.xlsx'
            );
        }

        if ($format === 'pdf' && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('inventory.pdf_items', ['items' => $items]);
            return $pdf->download('inventory_items_'.now()->format('Ymd_His').'.pdf');
        }

        // Fallback to CSV
        $response = new StreamedResponse(function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'sku', 'name', 'category', 'location', 'quantity', 'status', 'qr_code', 'created_at']);

            foreach ($items as $i) {
                fputcsv($handle, [
                    $i->id,
                    $i->sku,
                    $i->name,
                    $i->category,
                    $i->location,
                    $i->quantity,
                    $i->status,
                    $i->qr_code,
                    $i->created_at,
                ]);
            }

            fclose($handle);
        });

        $filename = 'inventory_items_'.now()->format('Ymd_His').'.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }

    public function dashboardSummary(?Request $request = null)
    {
        $request ??= request();

        $totalItems = Item::count();
        $totalQuantity = Item::sum('quantity');
        $lowStockThreshold = self::LOW_STOCK_THRESHOLD;
        $lowStockCount = Item::where('quantity', '<=', $lowStockThreshold)->count();

        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 10));
        $perPage = min($perPage, 25);

        $lowStockPaginator = Item::where('quantity', '<=', $lowStockThreshold)
            ->orderBy('quantity', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);

        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $issuedThisMonth = InventoryTransaction::where('transaction_type', 'issue')
            ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('quantity');
        $borrowedThisMonth = InventoryTransaction::where('transaction_type', 'borrow')
            ->whereBetween('created_at', [$monthStart, $monthEnd])->sum('quantity');

        $topUsed = InventoryTransaction::select('item_id', DB::raw('SUM(quantity) as total'))
            ->groupBy('item_id')
            ->orderByDesc('total')
            ->with('item')
            ->limit(10)
            ->get();

        $pendingTeacherRequests = collect();
        if ($this->isMonitoringAdminOrCustodian()) {
            $pendingTeacherRequests = MaterialIssue::with(['item', 'issuedTo', 'department'])
                ->where('status', 'pending')
                ->whereHas('issuedTo', fn ($query) => $query->where('role', 'teacher'))
                ->latest()
                ->limit(8)
                ->get();
        }

        $teacherDueBorrows = null;
        if ($request->user()?->isTeacher()) {
            $teacherDueBorrows = InventoryTransaction::with('item')
                ->where('transaction_type', 'borrow')
                ->where('user_id', $request->user()->id)
                ->where('status', 'approved')
                ->whereNull('returned_at')
                ->whereNotNull('expected_return_at')
                ->orderBy('expected_return_at')
                ->limit(8)
                ->get()
                ->map(function ($borrow) {
                    $dueDate = $borrow->expected_return_at->copy()->startOfDay();
                    $today = now()->startOfDay();
                    $daysRemaining = $today->diffInDays($dueDate, false);

                    return [
                        'id' => $borrow->id,
                        'item_name' => $borrow->item?->name ?? 'Unknown item',
                        'quantity' => (int) $borrow->quantity,
                        'expected_return_at' => $borrow->expected_return_at->toIso8601String(),
                        'days_remaining' => $daysRemaining,
                    ];
                });
        }

        return response()->json([
            'total_items' => $totalItems,
            'total_quantity' => $totalQuantity,
            'low_stock_count' => $lowStockCount,
            'low_stock_threshold' => $lowStockThreshold,
            'low_stock_items' => $lowStockPaginator->items(),
            'low_stock_pagination' => [
                'current_page' => $lowStockPaginator->currentPage(),
                'last_page' => $lowStockPaginator->lastPage(),
                'total' => $lowStockPaginator->total(),
                'per_page' => $lowStockPaginator->perPage(),
                'has_more_pages' => $lowStockPaginator->hasMorePages(),
            ],
            'issued_this_month' => (int) $issuedThisMonth,
            'borrowed_this_month' => (int) $borrowedThisMonth,
            'top_used_items' => $topUsed,
            'pending_teacher_requests' => $pendingTeacherRequests,
            'teacher_due_borrows' => $teacherDueBorrows,
        ]);
    }

    public function realtimeData()
    {
        $recentTransactions = InventoryTransaction::with(['item', 'user'])
            ->orderByDesc('created_at')
            ->limit(self::RECENT_TRANSACTIONS_LIMIT)
            ->get();

        $lowStockItems = Item::where('quantity', '<=', self::LOW_STOCK_THRESHOLD)
            ->orderBy('quantity', 'asc')
            ->orderBy('id', 'asc')
            ->limit(self::LOW_STOCK_LIMIT)
            ->get();

        return response()->json([
            'timestamp' => Carbon::now()->toIso8601String(),
            'recent_transactions' => $recentTransactions,
            'low_stock_items' => $lowStockItems,
        ]);
    }

    /**
     * Search items by name or sku for autocomplete.
     */
    public function search(Request $request)
    {
        $q = $request->input('q', '');
        $q = trim($q);
        if ($q === '') {
            return response()->json([]);
        }

        $items = Item::where('name', 'like', "%{$q}%")
            ->orWhere('sku', 'like', "%{$q}%")
            ->limit(50)
            ->get(['id','name','sku','quantity']);

        return response()->json($items);
    }

    public function generateQrImage($id)
    {
        $item = Item::findOrFail($id);
        $item->qr_code ??= 'VBIS-'.\Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(16));
        $item->save();
        $text = url('/inventory/qr/item/'.$id.'?code='.urlencode($item->qr_code));
        $qrCode = new \Endroid\QrCode\QrCode(
            data: $text,
            encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Medium,
            size: 300,
            margin: 10,
            roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin,
            foregroundColor: new \Endroid\QrCode\Color\Color(23, 74, 115),
            backgroundColor: new \Endroid\QrCode\Color\Color(255, 255, 255),
        );
        $result = (new \Endroid\QrCode\Writer\SvgWriter())->write($qrCode);

        return response($result->getString(), 200)->header('Content-Type', 'image/svg+xml');
    }

    public function generateQrInline($id)
    {
        $item = Item::findOrFail($id);
            $item->qr_code ??= 'VBIS-'.\Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(16));
            $item->save();
            $text = url('/inventory/qr/item/'.$id.'?code='.urlencode($item->qr_code));
        $img = '<img src="'.route('inventory.items.qr_image', $item->id).'" alt="QR for item '.$item->id.'">';
        return response($img, 200)->header('Content-Type', 'text/html');
    }

    // UI views
    private function isMonitoringAdminOrCustodian(): bool
    {
        $user = auth()->user();
        return $user && ($user->isAdmin() || $user->isPropertyCustodian());
    }

    private function canAccessReports(): bool
    {
        return $this->isMonitoringAdminOrCustodian();
    }

    private function canAccessAuditTrail(): bool
    {
        return $this->isMonitoringAdminOrCustodian();
    }

    public function statusView(Request $request)
    {
        $query = Item::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('location')) {
            $query->where('location', $request->input('location'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $items = $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString();
        $categories = Item::select('category')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        $locations = Item::select('location')->whereNotNull('location')->distinct()->orderBy('location')->pluck('location');
        $statuses = ['available', 'low stock', 'out of stock', 'borrowed', 'issued', 'returned'];

        return view('inventory.status', compact('items', 'categories', 'locations', 'statuses'));
    }

    public function lowStockView(Request $request)
    {
        $threshold = config('inventory.low_stock_threshold', 10);
        $query = Item::where('quantity', '<=', $threshold)->orderBy('quantity', 'asc')->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('location')) {
            $query->where('location', $request->input('location'));
        }

        $items = $query->paginate(20)->withQueryString();
        $categories = Item::select('category')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        $locations = Item::select('location')->whereNotNull('location')->distinct()->orderBy('location')->pluck('location');

        return view('inventory.low-stock', compact('items', 'categories', 'locations', 'threshold'));
    }

    public function qrGenerateView()
    {
        if (!$this->isMonitoringAdminOrCustodian()) {
            abort(403, 'You are not authorized to generate QR codes.');
        }

        $items = Item::orderBy('updated_at', 'desc')->paginate(20);
        return view('inventory.qr-generate', compact('items'));
    }

    public function qrScanView()
    {
        $user = auth()->user();
        if (!$user || !($user->isAdmin() || $user->isPropertyCustodian() || $user->isTeacher())) {
            abort(403, 'You are not authorized to access QR scanning.');
        }

        return view('inventory.qr-scan');
    }

    public function qrItemInformation($id)
    {
        $item = Item::with(['transactions' => function ($query) {
            $query->orderByDesc('created_at')->limit(10);
        }, 'transactions.user'])->findOrFail($id);

        $user = auth()->user();
        if ($user && $user->isTeacher()) {
            $item->transactions = $item->transactions->filter(function ($tx) use ($user) {
                return $tx->user_id === $user->id || $tx->transaction_type !== 'admin';
            });
        }

        return view('inventory.qr-item-information', compact('item'));
    }

    public function borrowQrInformation($id)
    {
        $transaction = InventoryTransaction::with(['item', 'user'])->findOrFail($id);

        $user = auth()->user();
        if ($user && !($user->isAdmin() || $user->isPropertyCustodian() || $transaction->user_id === $user->id)) {
            abort(403, 'You are not authorized to view this borrowed item record.');
        }

        return view('inventory.borrow-qr-information', compact('transaction'));
    }

    public function generateBorrowQrImage($id)
    {
        $transaction = InventoryTransaction::with(['item', 'user'])->findOrFail($id);
        $text = url('/inventory/qr/borrow/'.$transaction->id).'?item='.urlencode($transaction->item?->name ?? '').'&borrower='.urlencode($transaction->user?->name ?? '');

        $qrCode = new \Endroid\QrCode\QrCode(
            data: $text,
            encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Medium,
            size: 300,
            margin: 10,
            roundBlockSizeMode: \Endroid\QrCode\RoundBlockSizeMode::Margin,
            foregroundColor: new \Endroid\QrCode\Color\Color(23, 74, 115),
            backgroundColor: new \Endroid\QrCode\Color\Color(255, 255, 255),
        );
        $result = (new \Endroid\QrCode\Writer\SvgWriter())->write($qrCode);

        return response($result->getString(), 200)->header('Content-Type', 'image/svg+xml');
    }

        public function departmentSummary()
        {
            $summary = InventoryTransaction::query()
                ->with('department')
                ->select('department_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('COUNT(*) as transaction_count'))
                ->whereNotNull('department_id')
                ->groupBy('department_id')
                ->orderByDesc('total_quantity')
                ->limit(20)
                ->get()
                ->map(fn ($row) => [
                    'department_id' => $row->department_id,
                    'label' => $row->department?->name ?? 'Department '.$row->department_id,
                    'total_quantity' => (int) $row->total_quantity,
                    'transaction_count' => (int) $row->transaction_count,
                ]);

            return response()->json(['departments' => $summary]);
        }

        public function exportReport(Request $request)
        {
            if (!$this->canAccessReports()) {
                abort(403, 'You are not authorized to export reports.');
            }

            $type = $request->input('type', 'inventory');
            $format = $request->input('format', 'pdf');

            if ($type === 'transactions') {
                $rows = InventoryTransaction::with(['item', 'user', 'department'])->latest()->get()->map(fn ($row) => [
                    $row->id, optional($row->item)->name, optional($row->user)->name,
                    $row->transaction_type, $row->quantity, optional($row->department)->name ?? 'Department '.$row->department_id,
                    $row->notes, $row->expected_return_at, $row->returned_at, $row->created_at,
                ])->all();
                $headings = ['id', 'item', 'user', 'transaction_type', 'quantity', 'department', 'notes', 'expected_return_at', 'returned_at', 'created_at'];
                $title = 'Inventory Transactions Report';
            } elseif ($type === 'departments') {
                $rows = InventoryTransaction::with('department')->select('department_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('COUNT(*) as transaction_count'))
                    ->whereNotNull('department_id')->groupBy('department_id')->orderByDesc('total_quantity')->get()
                    ->map(fn ($row) => [$row->department?->name ?? 'Department '.$row->department_id, $row->department_id, $row->total_quantity, $row->transaction_count])->all();
                $headings = ['department', 'department_id', 'total_quantity', 'transaction_count'];
                $title = 'Department Usage Summary';
            } elseif ($type === 'issuance') {
                $rows = MaterialIssue::with(['item', 'issuedTo'])->latest()->get()->map(fn ($row) => [
                    $row->id,
                    optional($row->item)->sku ?? 'ITM-'.$row->item_id,
                    optional($row->item)->name ?? 'N/A',
                    $row->quantity,
                    optional($row->issuedTo)->name ?? 'N/A',
                    $row->status,
                    $row->created_at,
                ])->all();
                $headings = ['id', 'item_code', 'item_name', 'quantity_issued', 'recipient', 'status', 'created_at'];
                $title = 'Issuance Report';
            } else {
                $rows = Item::orderBy('name')->get()->map(fn ($row) => [
                    $row->id, $row->sku, $row->name, $row->category, $row->description,
                    $row->quantity, $row->location, $row->status, $row->qr_code,
                ])->all();
                $headings = ['id', 'sku', 'name', 'category', 'description', 'quantity', 'location', 'status', 'qr_code'];
                $title = 'Inventory Status Report';
            }

            $filename = str($type)->replace('_', '-')->append('-report-'.now()->format('Ymd_His'));

            if ($format === 'csv') {
                return $this->downloadCsv($headings, $rows, $filename.'.csv');
            }

            if ($format === 'excel') {
                return $this->downloadSpreadsheetXlsx($title, $headings, $rows, $filename.'.xlsx');
            }

            if ($format === 'pdf' && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return \Barryvdh\DomPDF\Facade\Pdf::loadView('inventory.pdf_report', compact('title', 'headings', 'rows'))
                    ->download($filename.'.pdf');
            }

            abort(503, 'The requested export format is unavailable.');
        }

        private function downloadCsv(array $headings, array $rows, string $filename)
        {
            $handle = fopen('php://temp', 'r+');
            fputcsv($handle, $headings);

            foreach ($rows as $row) {
                $normalizedRow = array_map(function ($value) {
                    if ($value === null) {
                        return '';
                    }

                    return is_scalar($value) ? (string) $value : json_encode($value);
                }, $row);

                fputcsv($handle, $normalizedRow);
            }

            rewind($handle);
            $content = stream_get_contents($handle);
            fclose($handle);

            return response($content, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        private function downloadSpreadsheetXlsx(string $title, array $headings, array $rows, string $filename)
        {
            $worksheetRows = '';
            foreach ([$headings, ...$rows] as $row) {
                $worksheetRows .= '<row>'.collect($row)->map(fn ($value) => '<c t="inlineStr"><is><t>'.htmlspecialchars((string) $value, ENT_XML1).'</t></is></c>')->implode('').'</row>';
            }
            $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>'.$worksheetRows.'</sheetData></worksheet>';
            $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.htmlspecialchars(substr($title, 0, 31), ENT_XML1).'" sheetId="1" r:id="rId1"/></sheets></workbook>';
            $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>';
            $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
            $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>';
            $temporaryFile = tempnam(sys_get_temp_dir(), 'vbis-xlsx-');
            $archive = new \ZipArchive();
            $archive->open($temporaryFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $archive->addFromString('[Content_Types].xml', $contentTypes);
            $archive->addFromString('_rels/.rels', $rootRels);
            $archive->addFromString('xl/workbook.xml', $workbook);
            $archive->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
            $archive->addFromString('xl/worksheets/sheet1.xml', $worksheet);
            $archive->close();
            $content = file_get_contents($temporaryFile);
            unlink($temporaryFile);

            return response($content, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

    public function reportsMenuView()
    {
        if (!$this->canAccessReports()) {
            abort(403, 'You are not authorized to access reports.');
        }
        return view('inventory.reports-menu');
    }

    public function inventoryReportView(Request $request)
    {
        if (!$this->canAccessReports()) {
            abort(403, 'You are not authorized to access inventory reports.');
        }

        $query = Item::query();
        if ($request->filled('category')) { $query->where('category', $request->input('category')); }
        if ($request->filled('location')) { $query->where('location', $request->input('location')); }
        if ($request->filled('status')) { $query->where('status', $request->input('status')); }
        if ($request->filled('search')) { $query->where(function ($q) use ($request) { $q->where('name', 'like', '%'.$request->input('search').'%')->orWhere('sku', 'like', '%'.$request->input('search').'%'); }); }

        $items = $query->orderBy('updated_at', 'desc')->paginate(20)->withQueryString();
        $categories = Item::select('category')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        $locations = Item::select('location')->whereNotNull('location')->distinct()->orderBy('location')->pluck('location');
        $statuses = ['available', 'low stock', 'out of stock', 'borrowed', 'issued', 'returned'];

        return view('inventory.reports', compact('items', 'categories', 'locations', 'statuses'));
    }

    public function borrowingReportView(Request $request)
    {
        if (!$this->canAccessReports()) {
            abort(403, 'You are not authorized to access borrowing reports.');
        }

        $query = InventoryTransaction::with(['item', 'user', 'department', 'relatedTransaction'])
            ->whereIn('transaction_type', ['borrow', 'return'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $status = strtolower($request->input('status'));
            $query->where('transaction_type', $status === 'returned' ? 'return' : 'borrow');
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('item', function ($itemQuery) use ($search) {
                    $itemQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                })->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $reports = $query->paginate(20)->withQueryString();
        return view('inventory.borrowing-report', compact('reports'));
    }

    public function returnReportView(Request $request)
    {
        if (!$this->canAccessReports()) {
            abort(403, 'You are not authorized to access return reports.');
        }

        $query = InventoryTransaction::with(['item', 'user'])
            ->where('transaction_type', 'return')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('item', function ($itemQuery) use ($search) {
                    $itemQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                })->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        $reports = $query->paginate(20)->withQueryString();
        return view('inventory.return-report', compact('reports'));
    }

    public function issuanceReportView(Request $request)
    {
        if (!$this->canAccessReports()) {
            abort(403, 'You are not authorized to access issuance reports.');
        }

        $query = MaterialIssue::with(['item', 'issuedTo'])->whereIn('status', ['approved', 'pending', 'rejected'])->orderBy('created_at', 'desc');
        if ($request->filled('search')) { $query->whereHas('item', function ($q) use ($request) { $q->where('name', 'like', '%'.$request->input('search').'%')->orWhere('sku', 'like', '%'.$request->input('search').'%'); }); }
        if ($request->filled('status')) { $query->where('status', $request->input('status')); }

        $reports = $query->paginate(20)->withQueryString();
        return view('inventory.issuance-report', compact('reports'));
    }

    public function lowStockReportView(Request $request)
    {
        if (!$this->canAccessReports()) {
            abort(403, 'You are not authorized to access low stock reports.');
        }

        $threshold = config('inventory.low_stock_threshold', 10);
        $query = Item::where('quantity', '<=', $threshold)->orderBy('quantity', 'asc');
        if ($request->filled('category')) { $query->where('category', $request->input('category')); }
        if ($request->filled('location')) { $query->where('location', $request->input('location')); }
        if ($request->filled('search')) { $query->where(function ($q) use ($request) { $q->where('name', 'like', '%'.$request->input('search').'%')->orWhere('sku', 'like', '%'.$request->input('search').'%'); }); }

        $items = $query->paginate(20)->withQueryString();
        return view('inventory.low-stock-report', compact('items', 'threshold'));
    }

    public function transactionHistoryView(Request $request)
    {
        $query = InventoryTransaction::with(['item', 'user', 'department'])->orderByDesc('created_at');

        $user = auth()->user();
        if ($user && $user->isTeacher()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('item', function ($itemQuery) use ($search) {
                    $itemQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                })->orWhere('transaction_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('transaction_type')) { $query->where('transaction_type', $request->input('transaction_type')); }
        if ($request->filled('status')) { $query->where('notes', 'like', '%'.$request->input('status').'%'); }

        $transactions = $query->paginate(20)->withQueryString();
        $types = ['borrow', 'return', 'issue', 'receive', 'adjustment'];

        return view('inventory.transaction-history', compact('transactions', 'types'));
    }

    public function auditTrailView(Request $request)
    {
        if (!$this->canAccessAuditTrail()) {
            abort(403, 'You are not authorized to view the audit trail.');
        }

        $query = InventoryTransaction::with(['item', 'user'])->orderByDesc('created_at');

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('item', function ($itemQuery) use ($search) { $itemQuery->where('name', 'like', "%{$search}%"); })
                  ->orWhere('transaction_type', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $events = $query->paginate(20)->withQueryString();
        return view('inventory.audit-trail', compact('events'));
    }

    public function dashboardView(Request $request)
    {
        $data = $this->dashboardSummary($request)->getData();
        // ensure arrays (convert nested stdClass to arrays) so views can use array syntax
        $summary = json_decode(json_encode($data), true);
        return view('inventory.dashboard', ['summary' => $summary]);
    }

    public function itemsView(Request $request)
    {
        $query = Item::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($itemQuery) use ($search) {
                $itemQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')->paginate(25)->withQueryString();
        return view('inventory.items', ['items' => $items]);
    }

    // API: create item
    public function store(Request $request)
    {
        // Authorization: only admin or property custodian can create items
        if (!($request->user() && ($request->user()->isAdmin() || $request->user()->isPropertyCustodian()))) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        $data = $request->validate([
            'sku' => 'nullable|string|max:64',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:128',
            'quantity' => 'required|integer|min:0',
            'location' => 'nullable|string|max:128',
            'status' => 'nullable|string|max:32',
        ]);

        $item = Item::create($data);

        // record initial stock as a receive transaction for audit trail
        try {
            \App\Models\InventoryTransaction::create([
                'item_id' => $item->id,
                'user_id' => $request->user() ? $request->user()->id : null,
                'department_id' => null,
                'transaction_type' => 'receive',
                'quantity' => $item->quantity ?? 0,
                'notes' => 'Initial stock on item creation',
            ]);
        } catch (\Exception $e) {
            // non-fatal: continue even if audit write fails
        }
        return response()->json($item, 201);
    }

    public function update(Request $request, $id)
    {
        // Authorization: only admin or property custodian can update items
        if (!($request->user() && ($request->user()->isAdmin() || $request->user()->isPropertyCustodian()))) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $item = Item::findOrFail($id);
        $data = $request->validate([
            'sku' => 'nullable|string|max:64',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:128',
            'quantity' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:128',
            'status' => 'nullable|string|max:32',
        ]);

        $oldQty = (int) $item->quantity;
        $newQty = array_key_exists('quantity', $data) ? (int) $data['quantity'] : $oldQty;

        if (array_key_exists('quantity', $data) && $newQty !== $oldQty) {
            $delta = $newQty - $oldQty;
            $req = \App\Models\AdjustmentRequest::create([
                'item_id' => $item->id,
                'requested_by' => $request->user() ? $request->user()->id : null,
                'old_quantity' => $oldQty,
                'new_quantity' => $newQty,
                'quantity_change' => $delta,
                'delta' => $delta,
                'reason' => 'Inventory adjustment request',
                'notes' => 'Requested quantity change from '.$oldQty.' to '.$newQty,
                'status' => 'pending',
                'performed_by' => $request->user()?->id,
            ]);

            unset($data['quantity']);
            $item->fill($data);
            $item->save();

            return response()->json(['message' => 'Adjustment request created', 'request_id' => $req->id], 202);
        }

        $item->fill($data);
        $item->save();

        return response()->json($item);
    }

    public function destroy(Request $request, $id)
    {
        // Authorization: only admin or property custodian can delete items
        if (!($request->user() && ($request->user()->isAdmin() || $request->user()->isPropertyCustodian()))) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $item = Item::findOrFail($id);
        $item->delete();
        return response()->json(['deleted' => true]);
    }

    // Adjustment requests APIs + UI
    public function adjustmentsIndex()
    {
        $list = \App\Models\AdjustmentRequest::with('item','requester')->orderBy('created_at','desc')->paginate(25);
        return response()->json($list);
    }

    public function adjustmentsView()
    {
        return view('inventory.adjustments');
    }

    public function approveAdjustment(Request $request, $id)
    {
        $req = \App\Models\AdjustmentRequest::findOrFail($id);
        if ($req->status !== 'pending') {
            return response()->json(['error' => 'Not pending'], 422);
        }

        // only admin or property custodian may alter item quantities via adjustments
        if (!($request->user() && ($request->user()->isAdmin() || $request->user()->isPropertyCustodian()))) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return \DB::transaction(function () use ($req, $request) {
            $item = Item::lockForUpdate()->findOrFail($req->item_id);
            $finalQty = $req->new_quantity;
            if ($finalQty < 0) {
                return response()->json(['error' => 'Resulting quantity would be negative'], 422);
            }

            $item->quantity = $finalQty;
            $item->save();

            // create adjustment transaction
            InventoryTransaction::create([
                'item_id' => $item->id,
                'user_id' => $req->requested_by,
                'department_id' => null,
                'transaction_type' => 'adjustment',
                'quantity' => abs($req->delta),
                'notes' => 'Approved adjustment request #'.$req->id,
            ]);

            $req->status = 'approved';
            $req->approved_by = $request->user() ? $request->user()->id : null;
            $req->approved_at = now();
            $req->save();

            return response()->json(['approved' => true, 'request' => $req]);
        });
    }

    public function rejectAdjustment(Request $request, $id)
    {
        $req = \App\Models\AdjustmentRequest::findOrFail($id);
        if ($req->status !== 'pending') {
            return response()->json(['error' => 'Not pending'], 422);
        }

        if (!($request->user() && ($request->user()->isApprover() || $request->user()->role === 'admin'))) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $req->status = 'rejected';
        $req->approved_by = $request->user() ? $request->user()->id : null;
        $req->approved_at = now();
        $req->save();

        return response()->json(['rejected' => true, 'request' => $req]);
    }
}
