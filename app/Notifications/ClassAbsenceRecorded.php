<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ClassAbsenceRecorded extends Notification
{
    public function __construct(
        public readonly string $message,
        public readonly string $url,
    ) {}

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
        return ['event' => 'class_absence_recorded', 'message' => $this->message, 'url' => $this->url];
    }
}
