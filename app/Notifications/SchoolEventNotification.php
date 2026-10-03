<?php

namespace App\Notifications;

use App\Models\SchoolEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SchoolEventNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly SchoolEvent $event, public readonly string $message, public readonly ?string $url = null) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return ['event' => 'school_event', 'message' => $this->message, 'url' => $this->url ?? route('calendar.show', $this->event)];
    }
}
