<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountApprovalNotification extends Notification
{
    use Queueable;

    public function __construct(public User $user, public string $status = 'pending')
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        if ($this->status === 'pending') {
            return (new MailMessage)
                ->subject('New user account pending approval')
                ->line("A new user account for {$this->user->name} ({$this->user->email}) is awaiting approval.")
                ->action('Review Account', url('/admin/users'))
                ->line('Please review the request and approve or reject it.');
        }

        if ($this->status === 'approved') {
            return (new MailMessage)
                ->subject('Your account has been approved')
                ->line('Your account has been approved and you can now log in to the system.')
                ->action('Login', url('/login'))
                ->line('Thank you for using the inventory system.');
        }

        return (new MailMessage)
            ->subject('Your account status update')
            ->line('Your account has been rejected or is currently inactive. Please contact the administrator for more information.')
            ->action('Login', url('/login'))
            ->line('Thank you for using the inventory system.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'account_approval',
            'user_id' => $this->user->id,
            'status' => $this->status,
            'message' => match ($this->status) {
                'approved' => 'Your account has been approved. You can now log in.',
                'rejected' => 'Your account was not approved. Please contact the administrator.',
                default => "New account needs approval: {$this->user->name} ({$this->user->email})",
            },
        ];
    }
}
