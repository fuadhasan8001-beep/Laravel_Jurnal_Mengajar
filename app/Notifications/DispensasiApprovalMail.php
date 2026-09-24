<?php

namespace App\Notifications;

use App\Models\Dispensasi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DispensasiApprovalMail extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Dispensasi $dispensasi) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->dispensasi->loadMissing(['siswa.kelas', 'groupStudents.siswa.kelas', 'jamMulai', 'jamSelesai', 'piket']);
        $students = $this->dispensasi->groupStudents->isNotEmpty()
            ? $this->dispensasi->groupStudents->pluck('siswa')->map(fn ($student): string => $student->nama_siswa.' — '.$student->kelas->nama_kelas)->implode('; ')
            : $this->dispensasi->siswa->nama_siswa.' — '.$this->dispensasi->siswa->kelas->nama_kelas;
        $hari = $this->dispensasi->tanggal->locale('id')->translatedFormat('l');
        [$start] = $this->dispensasi->jamMulai->timesForDay($hari);
        [, $end] = $this->dispensasi->jamSelesai->timesForDay($hari);

        return (new MailMessage)
            ->subject('Verifikasi pernyataan dispensasi siswa')
            ->greeting('Yth. '.$notifiable->name)
            ->line('Piket '.$this->dispensasi->piket?->name.' mengajukan pernyataan dispensasi untuk:')
            ->line($students)
            ->line('Tanggal: '.$this->dispensasi->tanggal->format('d-m-Y'))
            ->line('Waktu: '.$start.' – '.$end)
            ->line('Alasan: '.$this->dispensasi->alasan)
            ->action('Periksa dan verifikasi dispensasi', route('dispensasi.show', $this->dispensasi))
            ->line('Masuk menggunakan akun admin untuk menyetujui atau menolak. Status siswa berubah menjadi dispen setelah disetujui.');
    }
}
