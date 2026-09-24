<?php

namespace App\Notifications;

use App\Models\Dispensasi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DispensasiNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Dispensasi $dispensasi,
        public string $event,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Pembaruan dispensasi')
            ->line($this->message())
            ->action('Buka dispensasi', route('dispensasi.show', $this->dispensasi));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'message' => $this->message(),
            'url' => $this->event === 'teacher_approved' ? route('jurnal.index', ['tanggal_mulai' => $this->dispensasi->tanggal->toDateString(), 'tanggal_selesai' => $this->dispensasi->tanggal->toDateString(), 'kelas_id' => $this->dispensasi->siswa->kelas_id]) : route('dispensasi.show', $this->dispensasi),
            'dispensasi_id' => $this->dispensasi->id,
        ];
    }

    private function message(): string
    {
        $hari = $this->dispensasi->tanggal->locale('id')->translatedFormat('l');
        [$start] = $this->dispensasi->jamMulai->timesForDay($hari);
        [, $end] = $this->dispensasi->jamSelesai->timesForDay($hari);

        return match ($this->event) {
            'teacher_approved' => $this->dispensasi->siswa->nama_siswa.' mendapat dispensasi pada '.$this->dispensasi->tanggal->format('d-m-Y').' pukul '.$start.'–'.$end.'.',
            'submitted' => 'Pengajuan dispensasi baru menunggu pemeriksaan piket.',
            'piket_approved' => 'Pengajuan dispensasi telah disetujui piket dan menunggu admin.',
            'piket_rejected' => 'Pengajuan dispensasi ditolak oleh piket.',
            'admin_approved' => 'Pengajuan dispensasi telah disetujui admin.',
            'admin_rejected' => 'Pengajuan dispensasi ditolak oleh admin.',
            default => 'Ada pembaruan pada pengajuan dispensasi Anda.',
        };
    }
}
