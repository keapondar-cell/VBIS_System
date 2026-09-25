<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\MaterialIssue;

class IssueApprovedNotification extends Notification
{
    use Queueable;

    protected $issue;

    public function __construct(MaterialIssue $issue)
    {
        $this->issue = $issue;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $itemName = $this->issue->item?->name ?? 'Item';
        return (new MailMessage)
                    ->subject('Material Issue Approved')
                    ->line("Your request for {$itemName} (x{$this->issue->quantity}) has been approved.")
                    ->action('View Issue', url('/inventory/issues'))
                    ->line('Thank you for using the inventory system.');
    }

    public function toDatabase($notifiable): array
    {
        $itemName = $this->issue->item?->name ?? 'Item';

        return [
            'type' => 'issue_approved',
            'issue_id' => $this->issue->id,
            'message' => "Your request for {$itemName} (x{$this->issue->quantity}) has been approved.",
        ];
    }
}
