<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JurnalReminderNotification extends Notification
{
    public function __construct(private readonly int $lessonCount) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pengingat isi jurnal')
            ->line($this->message())
            ->action('Isi jurnal', route('jurnal.create'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'event' => 'missing_journal',
            'message' => $this->message(),
            'url' => route('jurnal.create'),
        ];
    }

    private function message(): string
    {
        return 'Ada '.$this->lessonCount.' jadwal mengajar hari ini yang belum memiliki jurnal.';
    }
}
