<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\MaterialIssue;

class IssueRejectedNotification extends Notification
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
                    ->subject('Material Issue Rejected')
                    ->line("Your request for {$itemName} (x{$this->issue->quantity}) was rejected.")
                    ->line('Reason: '.($this->issue->rejection_reason ?? 'No reason provided.'))
                    ->action('View Issue', url('/inventory/issues'));
    }

    public function toDatabase($notifiable): array
    {
        $itemName = $this->issue->item?->name ?? 'Item';

        return [
            'type' => 'issue_rejected',
            'issue_id' => $this->issue->id,
            'message' => "Your request for {$itemName} (x{$this->issue->quantity}) was rejected. Reason: ".($this->issue->rejection_reason ?? 'No reason provided.'),
        ];
    }
}
