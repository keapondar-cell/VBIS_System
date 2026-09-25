<?php

namespace App\Notifications;

use App\Models\InventoryTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InventoryTransactionNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected InventoryTransaction $transaction,
        protected string $event,
        protected ?string $reason = null,
    ) {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $itemName = $this->transaction->item?->name ?? 'Item';
        $quantity = $this->transaction->quantity;
        $message = match ($this->event) {
            'borrow_requested' => "A borrow request was submitted for {$itemName} (x{$quantity}).",
            'borrow_approved' => "Your borrow request for {$itemName} (x{$quantity}) was approved.",
            'borrow_rejected' => "Your borrow request for {$itemName} (x{$quantity}) was rejected.",
            'returned' => "{$itemName} (x{$quantity}) was returned.",
            default => "Inventory transaction update for {$itemName} (x{$quantity}).",
        };

        return [
            'type' => $this->event,
            'transaction_id' => $this->transaction->id,
            'message' => $this->reason ? $message.' Reason: '.$this->reason : $message,
        ];
    }
}
