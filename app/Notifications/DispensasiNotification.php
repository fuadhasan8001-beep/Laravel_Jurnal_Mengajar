<?php

namespace App\Notifications;

use App\Models\Dispensasi;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DispensasiNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Dispensasi $dispensasi,
        public string $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'message' => $this->message(),
            'url' => route('dispensasi.show', $this->dispensasi),
            'dispensasi_id' => $this->dispensasi->id,
        ];
    }

    private function message(): string
    {
        return match ($this->event) {
            'submitted' => 'Pengajuan dispensasi baru menunggu pemeriksaan piket.',
            'piket_approved' => 'Pengajuan dispensasi telah disetujui piket dan menunggu admin.',
            'piket_rejected' => 'Pengajuan dispensasi ditolak oleh piket.',
            'admin_approved' => 'Pengajuan dispensasi telah disetujui admin.',
            'admin_rejected' => 'Pengajuan dispensasi ditolak oleh admin.',
            default => 'Ada pembaruan pada pengajuan dispensasi Anda.',
        };
    }
}
