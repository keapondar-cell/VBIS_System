<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Item;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function receive(int $itemId, int $quantity, array $meta = []): InventoryTransaction
    {
        return $this->applyMovement($itemId, 'receive', $quantity, $meta);
    }

    public function issue(int $itemId, int $quantity, array $meta = []): InventoryTransaction
    {
        return $this->applyMovement($itemId, 'issue', -$quantity, $meta);
    }

    public function borrow(int $itemId, int $quantity, array $meta = []): InventoryTransaction
    {
        $userId = $meta['user_id'] ?? null;

        return DB::transaction(function () use ($itemId, $quantity, $meta, $userId) {
            $item = Item::lockForUpdate()->findOrFail($itemId);
            $existingPending = InventoryTransaction::query()
                ->where('item_id', $itemId)
                ->where('user_id', $userId)
                ->where('transaction_type', 'borrow')
                ->where('status', 'pending')
                ->exists();

            if ($existingPending && ($meta['status'] ?? 'pending') === 'pending') {
                throw ValidationException::withMessages([
                    'quantity' => 'A pending borrow request already exists for this item.',
                ]);
            }

            if ((int) $item->quantity < (int) $quantity && (($meta['skip_stock_check'] ?? false) !== true)) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock for this borrow request.',
                ]);
            }

            $transaction = InventoryTransaction::create([
                'item_id' => $itemId,
                'user_id' => $userId,
                'department_id' => $meta['department_id'] ?? null,
                'transaction_type' => 'borrow',
                'quantity' => $quantity,
                'related_transaction_id' => $meta['related_transaction_id'] ?? null,
                'expected_return_at' => $meta['expected_return_at'] ?? null,
                'status' => $meta['status'] ?? 'pending',
                'notes' => $meta['notes'] ?? null,
                'condition' => $meta['condition'] ?? null,
                'performed_by' => $meta['performed_by'] ?? $userId,
                'affected_user_id' => $meta['affected_user_id'] ?? $userId,
                'reason' => $meta['reason'] ?? null,
                'remarks' => $meta['remarks'] ?? null,
            ]);

            return $transaction;
        });
    }

    public function approveBorrow(InventoryTransaction $transaction): InventoryTransaction
    {
        return DB::transaction(function () use ($transaction) {
            $transaction->refresh();

            if ($transaction->transaction_type !== 'borrow') {
                throw ValidationException::withMessages([
                    'transaction' => 'Only borrow transactions may be approved.',
                ]);
            }

            if ($transaction->status !== 'pending') {
                throw ValidationException::withMessages([
                    'transaction' => 'Only pending borrow requests can be approved.',
                ]);
            }

            $item = Item::lockForUpdate()->findOrFail($transaction->item_id);
            $oldQuantity = (int) $item->quantity;
            $quantity = (int) $transaction->quantity;

            if ($oldQuantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock to approve this borrow request.',
                ]);
            }

            $newQuantity = $oldQuantity - $quantity;
            $item->quantity = $newQuantity;
            $item->save();

            $transaction->status = 'approved';
            $transaction->quantity_change = -$quantity;
            $transaction->old_quantity = $oldQuantity;
            $transaction->new_quantity = $newQuantity;
            $transaction->performed_by = $transaction->performed_by ?: $transaction->user_id;
            $transaction->affected_user_id = $transaction->affected_user_id ?: $transaction->user_id;
            $transaction->remarks = $transaction->remarks ?? 'Borrow request approved';
            $transaction->save();

            return $transaction->fresh();
        });
    }

    public function returnItem(int $itemId, int $quantity, array $meta = []): InventoryTransaction
    {
        return DB::transaction(function () use ($itemId, $quantity, $meta) {
            $item = Item::lockForUpdate()->findOrFail($itemId);
            $borrow = InventoryTransaction::query()
                ->where('item_id', $itemId)
                ->where('transaction_type', 'borrow')
                ->where('user_id', $meta['user_id'] ?? null)
                ->whereNotNull('status')
                ->where(function ($query) {
                    $query->whereNull('returned_at')->orWhere('returned_at', '>=', now()->subMinute());
                })
                ->orderByDesc('created_at')
                ->first();

            if ($borrow && $borrow->returned_at) {
                throw ValidationException::withMessages([
                    'quantity' => 'This borrow has already been returned.',
                ]);
            }

            if ($borrow && $quantity > (int) $borrow->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Return quantity cannot exceed the borrowed quantity.',
                ]);
            }

            if (! $borrow) {
                throw ValidationException::withMessages([
                    'item_id' => 'No active borrow record was found for this item.',
                ]);
            }

            $condition = strtoupper((string) ($meta['condition'] ?? 'Good'));
            $restoreStock = $condition === 'GOOD';
            $oldQuantity = (int) $item->quantity;
            $newQuantity = $restoreStock ? $oldQuantity + $quantity : $oldQuantity;

            if ($restoreStock) {
                $item->quantity = $newQuantity;
                $item->save();
            }

            $transaction = InventoryTransaction::create([
                'item_id' => $itemId,
                'user_id' => $meta['user_id'] ?? $borrow->user_id,
                'department_id' => $meta['department_id'] ?? $borrow->department_id,
                'transaction_type' => 'return',
                'quantity' => $quantity,
                'quantity_change' => $restoreStock ? $quantity : 0,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'related_transaction_id' => $borrow->id,
                'expected_return_at' => $borrow->expected_return_at,
                'returned_at' => now(),
                'status' => 'returned',
                'condition' => $meta['condition'] ?? 'Good',
                'performed_by' => $meta['performed_by'] ?? $borrow->user_id,
                'affected_user_id' => $meta['affected_user_id'] ?? $borrow->user_id,
                'reason' => $meta['reason'] ?? 'Return verification',
                'remarks' => $meta['remarks'] ?? null,
            ]);

            $borrow->status = 'returned';
            $borrow->returned_at = now();
            $borrow->returned_by = $meta['performed_by'] ?? $borrow->user_id;
            $borrow->condition = $meta['condition'] ?? 'Good';
            $borrow->remarks = $meta['remarks'] ?? $borrow->remarks;
            $borrow->save();

            return $transaction;
        });
    }

    public function adjust(int $itemId, int $newQuantity, array $meta = []): InventoryTransaction
    {
        return DB::transaction(function () use ($itemId, $newQuantity, $meta) {
            $item = Item::lockForUpdate()->findOrFail($itemId);
            $oldQuantity = (int) $item->quantity;

            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stock cannot be negative.',
                ]);
            }

            $item->quantity = $newQuantity;
            $item->save();

            $quantityChange = $newQuantity - $oldQuantity;

            return InventoryTransaction::create([
                'item_id' => $itemId,
                'user_id' => $meta['user_id'] ?? null,
                'department_id' => $meta['department_id'] ?? null,
                'transaction_type' => 'adjustment',
                'quantity' => abs($quantityChange),
                'quantity_change' => $quantityChange,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'status' => 'approved',
                'performed_by' => $meta['performed_by'] ?? null,
                'affected_user_id' => $meta['affected_user_id'] ?? null,
                'reason' => $meta['reason'] ?? 'Inventory adjustment',
                'remarks' => $meta['remarks'] ?? null,
                'notes' => $meta['notes'] ?? null,
            ]);
        });
    }

    protected function applyMovement(int $itemId, string $type, int $quantityDelta, array $meta = []): InventoryTransaction
    {
        return DB::transaction(function () use ($itemId, $type, $quantityDelta, $meta) {
            $item = Item::lockForUpdate()->findOrFail($itemId);
            $oldQuantity = (int) $item->quantity;
            $newQuantity = $oldQuantity + $quantityDelta;

            if ($quantityDelta < 0 && $newQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock for this transaction.',
                ]);
            }

            $item->quantity = $newQuantity;
            $item->save();

            return InventoryTransaction::create([
                'item_id' => $itemId,
                'user_id' => $meta['user_id'] ?? null,
                'department_id' => $meta['department_id'] ?? null,
                'transaction_type' => $type,
                'quantity' => abs($quantityDelta),
                'quantity_change' => $quantityDelta,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'related_transaction_id' => $meta['related_transaction_id'] ?? null,
                'expected_return_at' => $meta['expected_return_at'] ?? null,
                'returned_at' => $meta['returned_at'] ?? null,
                'status' => $meta['status'] ?? 'approved',
                'condition' => $meta['condition'] ?? null,
                'performed_by' => $meta['performed_by'] ?? null,
                'affected_user_id' => $meta['affected_user_id'] ?? null,
                'reason' => $meta['reason'] ?? null,
                'remarks' => $meta['remarks'] ?? null,
                'notes' => $meta['notes'] ?? null,
            ]);
        });
    }
}
