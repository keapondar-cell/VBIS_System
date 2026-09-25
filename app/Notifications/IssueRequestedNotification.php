<?php

namespace App\Notifications;

use App\Models\MaterialIssue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IssueRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(protected MaterialIssue $issue)
    {
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $itemName = $this->issue->item?->name ?? 'Item';
        $requesterName = $this->issue->issuedTo?->name ?? 'a teacher';

        return [
            'type' => 'issue_requested',
            'issue_id' => $this->issue->id,
            'message' => "A new material request has been requested for {$itemName} (x{$this->issue->quantity}) by {$requesterName}.",
        ];
    }
}
