<?php

namespace App\Notifications;

use App\Models\RegistrationRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationNotification extends Notification
{
    use Queueable;

    public function __construct(public RegistrationRequest $registrationRequest, public string $event) {}

    public function via(object $notifiable): array
    {
        return $notifiable instanceof RegistrationRequest ? ['mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->event === 'approved' ? 'Pendaftaran disetujui' : 'Pendaftaran ditolak')
            ->greeting('Halo '.$this->registrationRequest->name.',')
            ->line($this->event === 'approved'
                ? 'Pendaftaran Anda telah disetujui. Silakan login menggunakan email yang didaftarkan.'
                : 'Pendaftaran Anda ditolak oleh admin.'.($this->registrationRequest->rejection_reason ? ' Alasan: '.$this->registrationRequest->rejection_reason : ''))
            ->action('Buka halaman login', route('login'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $isAdmin = $notifiable instanceof User && $notifiable->role === 'admin';

        return [
            'event' => $this->event,
            'message' => match ($this->event) {
                'submitted' => 'Pendaftaran baru menunggu persetujuan admin.',
                'approved' => 'Pendaftaran Anda telah disetujui. Silakan login.',
                'rejected' => 'Pendaftaran Anda ditolak oleh admin.'.($this->registrationRequest->rejection_reason ? ' Alasan: '.$this->registrationRequest->rejection_reason : ''),
                default => 'Ada pembaruan pada pendaftaran Anda.',
            },
            'url' => $isAdmin
                ? route('admin.registrations.show', $this->registrationRequest)
                : route('register.success'),
        ];
    }
}
