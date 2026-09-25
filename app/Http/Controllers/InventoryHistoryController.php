<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransaction;
use App\Models\User;
use App\Notifications\InventoryTransactionNotification;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Carbon\Carbon;

use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (! $user || ! ($user->isAdmin() || $user->isPropertyCustodian() || $user->isTeacher())) {
            abort(403);
        }

        $query = InventoryTransaction::with(['item', 'user', 'department', 'relatedTransaction']);

        if ($user->isTeacher()) {
            $query->where('user_id', $user->id);
        }

        $query->when($request->filled('item_id'), function ($q) use ($request) {
            $q->where('item_id', $request->input('item_id'));
        });

        $query->when($request->filled('user_id'), function ($q) use ($request) {
            $q->where('user_id', $request->input('user_id'));
        });

        $query->when($request->filled('department_id'), function ($q) use ($request) {
            $q->where('department_id', $request->input('department_id'));
        });

        $query->when($request->filled('transaction_type'), function ($q) use ($request) {
            $q->where('transaction_type', $request->input('transaction_type'));
        });

        if ($request->filled('date_from')) {
            $from = Carbon::parse($request->input('date_from'))->startOfDay();
            $query->where('created_at', '>=', $from);
        }

        if ($request->filled('date_to')) {
            $to = Carbon::parse($request->input('date_to'))->endOfDay();
            $query->where('created_at', '<=', $to);
        }

        $query->orderBy('created_at', 'desc');

        if ($request->input('export') === 'pdf') {
            $transactions = $query->get();

            return \Barryvdh\DomPDF\Facade\Pdf::loadView('inventory.pdf_transactions', compact('transactions'))
                ->download('inventory_transactions_'.now()->format('Ymd_His').'.pdf');
        }

        if ($request->input('export') === 'csv') {
            $items = $query->get();

            $response = new StreamedResponse(function () use ($items) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['id', 'item_id', 'item_name', 'user_id', 'user_name', 'department_id', 'department', 'transaction_type', 'quantity', 'notes', 'expected_return_at', 'returned_at', 'created_at']);

                foreach ($items as $t) {
                    fputcsv($handle, [
                        $t->id,
                        $t->item_id,
                        optional($t->item)->name,
                        $t->user_id,
                        optional($t->user)->name,
                        $t->department_id,
                        optional($t->department)->name,
                        $t->transaction_type,
                        $t->quantity,
                        $t->notes,
                        $t->expected_return_at,
                        $t->returned_at,
                        $t->created_at,
                    ]);
                }

                fclose($handle);
            });

            $filename = 'inventory_transactions_'.now()->format('Ymd_His').'.csv';
            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

            return $response;
        }

        $transactions = $query->paginate(25);
        return response()->json($transactions);
    }

    public function show($id)
    {
        $transaction = InventoryTransaction::with(['item', 'user', 'department', 'relatedTransaction', 'returnTransaction'])->findOrFail($id);
        $user = request()->user();
        if (! $user || (! $user->isAdmin() && ! $user->isPropertyCustodian() && $transaction->user_id !== $user->id)) {
            abort(403);
        }
        return response()->json($transaction);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $transactionType = strtolower((string) $request->input('transaction_type'));

        // Only teachers may borrow items; admin/property custodian can manage other inventory transactions.
        if ($transactionType === 'borrow' && (! $user || ! $user->isTeacher())) {
            return response()->json(['error' => 'Only teachers can borrow items.'], 403);
        }

        if (! in_array($transactionType, ['borrow', 'return'], true) && (! $user || ! ($user->isAdmin() || $user->isPropertyCustodian()))) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'user_id' => 'nullable|integer|exists:users,id',
            'department_id' => 'nullable|integer',
            'transaction_type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'purpose' => 'nullable|string|min:5|max:500',
            'notes' => 'nullable|string',
            'condition' => 'nullable|string|in:Good,Damaged,Lost,For Repair',
            'expected_return_at' => 'nullable|date|after:now',
            'related_transaction_id' => 'nullable|integer|exists:inventory_transactions,id',
        ]);

        // Default the actor before resolving return linkage so teachers can only close their own borrow.
        if (empty($data['user_id']) && $request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        if ($transactionType === 'borrow') {
            $data['purpose'] = $data['purpose'] ?? $request->input('notes');
            $request->validate(['purpose' => 'required|string|min:5|max:500']);
            $data['notes'] = $data['purpose'];
            unset($data['purpose']);
            $data['expected_return_at'] = $data['expected_return_at'] ?? null;
            $data['status'] = 'pending';
        } elseif ($transactionType === 'return') {
            $openBorrow = InventoryTransaction::where('item_id', $data['item_id'])
                ->where('transaction_type', 'borrow')
                ->where('user_id', $data['user_id'])
                ->where('status', '!=', 'returned')
                ->where(function ($query) {
                    $query->whereNull('returned_at')->orWhere('returned_at', '<', now());
                })
                ->latest()
                ->first();

            if (! $openBorrow) {
                return response()->json(['error' => 'No active borrow record exists for this item.'], 422);
            }

            $alreadyReturned = InventoryTransaction::where('related_transaction_id', $openBorrow->id)
                ->where('transaction_type', 'return')
                ->exists();

            if ($alreadyReturned) {
                return response()->json(['error' => 'This borrow has already been returned.'], 422);
            }

            $data['related_transaction_id'] = $openBorrow->id;
            $data['status'] = 'return_requested';
            $data['condition'] = $data['condition'] ?? 'Good';
        } else {
            $data['status'] = 'approved';
        }

        return \DB::transaction(function () use ($data, $transactionType, $request) {
            $type = strtolower($data['transaction_type']);

            if ($type === 'borrow') {
                $transaction = InventoryTransaction::create(array_merge($data, [
                    'performed_by' => $request->user()?->id,
                    'affected_user_id' => $data['user_id'],
                    'reason' => 'Borrow request',
                ]));
                $transaction->load('item');
                User::whereIn('role', ['admin', 'property_custodian'])->get()
                    ->each(fn ($manager) => $manager->notify(new InventoryTransactionNotification($transaction, 'borrow_requested')));
            } elseif ($type === 'return') {
                $transaction = app(InventoryService::class)->returnItem(
                    (int) $data['item_id'],
                    (int) $data['quantity'],
                    [
                        'user_id' => $data['user_id'],
                        'department_id' => $data['department_id'] ?? null,
                        'performed_by' => $request->user()?->id,
                        'affected_user_id' => $data['user_id'],
                        'condition' => $data['condition'] ?? 'Good',
                        'reason' => 'Return verification',
                        'remarks' => $data['notes'] ?? null,
                    ]
                );
                $transaction->load('item');
                $transaction->user?->notify(new InventoryTransactionNotification($transaction, 'returned'));
                User::whereIn('role', ['admin', 'property_custodian'])->get()
                    ->each(fn ($manager) => $manager->notify(new InventoryTransactionNotification($transaction, 'returned')));
            } else {
                $transaction = InventoryTransaction::create($data);
            }

            $payload = $transaction->toArray();
            if ($type === 'borrow') {
                $payload['borrow_qr_url'] = route('inventory.qr.borrow', $transaction->id);
            }

            return response()->json($payload, 201);
        });
    }

    public function approve(Request $request, $id)
    {
        $transaction = InventoryTransaction::findOrFail($id);

        if ($transaction->transaction_type !== 'borrow') {
            return response()->json(['error' => 'Only borrow transactions need approval.'], 422);
        }

        if ($transaction->status !== 'pending') {
            return response()->json(['error' => 'Only pending borrow requests can be approved.'], 422);
        }

        if (! $request->user() || ! ($request->user()->isAdmin() || $request->user()->isPropertyCustodian())) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return \DB::transaction(function () use ($transaction, $request) {
            $approved = app(InventoryService::class)->approveBorrow($transaction);
            $approved->status = 'approved';
            $approved->notes = $approved->notes ?: 'Approved by '.($request->user()?->name ?? 'admin');
            $approved->performed_by = $request->user()?->id ?? $approved->performed_by;
            $approved->affected_user_id = $approved->affected_user_id ?: $approved->user_id;
            $approved->save();

            $approved->load('item');
            $approved->user?->notify(new InventoryTransactionNotification($approved, 'borrow_approved'));

            return response()->json($approved->fresh());
        });
    }

    public function reject(Request $request, $id)
    {
        $transaction = InventoryTransaction::findOrFail($id);

        if ($transaction->transaction_type !== 'borrow' || $transaction->status !== 'pending') {
            return response()->json(['error' => 'Only pending borrow requests can be rejected.'], 422);
        }

        if (! $request->user() || ! ($request->user()->isAdmin() || $request->user()->isPropertyCustodian())) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $request->merge(['reason' => $request->input('reason', $request->input('notes'))]);
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $transaction->status = 'rejected';
        $transaction->rejection_reason = $data['reason'];
        $transaction->performed_by = $request->user()->id;
        $transaction->remarks = 'Borrow request rejected';
        $transaction->save();
        $transaction->load('item');
        $transaction->user?->notify(new InventoryTransactionNotification($transaction, 'borrow_rejected', $data['reason']));

        return response()->json($transaction->fresh());
    }

    // UI view for transactions list
    public function transactionsView()
    {
        return view('inventory.transactions');
    }

    public function update(Request $request, $id)
    {
        abort(405, 'Inventory history records are immutable. Create a correcting transaction instead.');

        $tx = InventoryTransaction::findOrFail($id);

        // Authorization: only admin or property custodian may edit inventory transactions
        $user = $request->user();
        if (!($user && ($user->isAdmin() || $user->isPropertyCustodian()))) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'item_id' => 'required|integer|exists:items,id',
            'transaction_type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($data['item_id'] != $tx->item_id) {
            return response()->json(['error' => 'Changing item_id is not supported'], 422);
        }

        return \DB::transaction(function () use ($tx, $data) {
            $item = \App\Models\Item::lockForUpdate()->findOrFail($tx->item_id);

            $oldType = strtolower($tx->transaction_type);
            $oldQty = (int) $tx->quantity;
            $oldEffect = in_array($oldType, ['receive','return']) ? $oldQty : -$oldQty;

            $newType = strtolower($data['transaction_type']);
            $newQty = (int) $data['quantity'];
            $newEffect = in_array($newType, ['receive','return']) ? $newQty : -$newQty;

            $finalQty = $item->quantity - $oldEffect + $newEffect;
            if ($finalQty < 0) {
                return response()->json(['error' => 'Insufficient stock for update'], 422);
            }

            $item->quantity = $finalQty;
            $item->save();

            $tx->transaction_type = $data['transaction_type'];
            $tx->quantity = $data['quantity'];
            $tx->notes = $data['notes'] ?? $tx->notes;
            $tx->save();

            return response()->json($tx);
        });
    }

    public function updateCondition(Request $request, $id)
    {
        $transaction = InventoryTransaction::findOrFail($id);
        $user = $request->user();

        if (! $user || ! $user->isTeacher() || $transaction->user_id !== $user->id) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        if (! in_array(strtolower($transaction->transaction_type), ['issue', 'borrow', 'return'], true)) {
            return response()->json(['error' => 'Condition can only be updated for issued, borrowed, or returned items.'], 422);
        }

        $data = $request->validate([
            'condition' => 'required|string|min:2|max:100',
        ]);

        $transaction->update(['condition' => trim($data['condition'])]);

        return response()->json($transaction->fresh(['item', 'user', 'department', 'relatedTransaction', 'returnTransaction']));
    }

    public function destroy($id)
    {
        abort(405, 'Inventory history records are immutable. Create a correcting transaction instead.');

        $tx = InventoryTransaction::findOrFail($id);

        // Authorization: only admin or property custodian may delete transactions
        $user = auth()->user();
        if (!($user && ($user->isAdmin() || $user->isPropertyCustodian()))) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return \DB::transaction(function () use ($tx) {
            $item = \App\Models\Item::lockForUpdate()->findOrFail($tx->item_id);
            $type = strtolower($tx->transaction_type);
            $qty = (int) $tx->quantity;
            $effect = in_array($type, ['receive','return']) ? $qty : -$qty;

            $finalQty = $item->quantity - $effect;
            if ($finalQty < 0) {
                return response()->json(['error' => 'Cannot delete transaction — insufficient stock rollback'], 422);
            }

            $item->quantity = $finalQty;
            $item->save();

            $tx->delete();
            return response()->json(['deleted' => true]);
        });
    }
}
