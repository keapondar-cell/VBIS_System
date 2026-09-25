<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InventoryReportGeneratedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $reportPath)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Daily inventory report generated')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('The daily inventory monitoring report has been generated.')
            ->line('The PDF is attached to this message.')
            ->attach(storage_path('app/private/'.$this->reportPath), [
                'as' => 'inventory-report.pdf',
                'mime' => 'application/pdf',
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'inventory_report_generated',
            'path' => $this->reportPath,
            'message' => 'The daily inventory monitoring report is ready.',
        ];
    }
}
