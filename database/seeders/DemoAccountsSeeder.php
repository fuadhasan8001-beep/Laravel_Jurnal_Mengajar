<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\NationalHoliday;
use App\Models\SchoolEvent;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoAccountsSeeder extends Seeder
{
    private const PASSWORD = 'password123';

    private const JOURNAL_MATERIAL = 'Demo: jurnal untuk diverifikasi';

    private const DISPENSATION_REASON = 'Demo: pengajuan dispensasi siswa';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo accounts may only be seeded in local or testing environments.');
        }

        $admin = $this->demoUser('admin', 'Admin Demo', 'admin');
        $waka = $this->demoUser('waka', 'Waka Demo', 'waka');
        $teacherUser = $this->demoUser('guru', 'Guru Demo', 'guru');
        $studentUser = $this->demoUser('siswa', 'Siswa Demo', 'siswa');
        $secondStudentUser = $this->demoUser('siswa2', 'Siswa Demo Dua', 'siswa');
        $secretary = $this->demoUser('sekretaris', 'Sekretaris Demo', 'sekretaris');
        $piket = $this->demoUser('piket', 'Piket Demo', 'piket');

        $teacher = Guru::updateOrCreate(
            ['user_id' => $teacherUser->id],
            ['nip' => 'DEMO-GURU-01', 'nama_guru' => 'Guru Demo', 'status_kepegawaian' => 'Honorer'],
        );
        $class = Kelas::updateOrCreate(
            ['nama_kelas' => 'X RPL 99'],
            ['tingkat' => 'X', 'wali_kelas_id' => $teacher->id],
        );
        $students = collect([
            Siswa::updateOrCreate(
                ['user_id' => $studentUser->id],
                ['kelas_id' => $class->id, 'nis' => 'DEMO-SISWA-01', 'nama_siswa' => 'Siswa Demo', 'jenis_kelamin' => 'L'],
            ),
            Siswa::updateOrCreate(
                ['user_id' => $secondStudentUser->id],
                ['kelas_id' => $class->id, 'nis' => 'DEMO-SISWA-02', 'nama_siswa' => 'Siswa Demo Dua', 'jenis_kelamin' => 'P'],
            ),
        ]);
        $secretary->kelasSekretaris()->syncWithoutDetaching([$class->id]);

        $subject = Mapel::updateOrCreate(
            ['kode_mapel' => 'DEMO-RPL'],
            ['nama_mapel' => 'Pemrograman Web Demo'],
        );
        $teacher->mapels()->syncWithoutDetaching([$subject->id]);
        $periods = collect([
            JamPelajaran::firstOrCreate(
                ['jam_ke' => 1],
                ['jam_mulai' => '07:00:00', 'jam_selesai' => '07:40:00', 'is_active' => true],
            ),
            JamPelajaran::firstOrCreate(
                ['jam_ke' => 2],
                ['jam_mulai' => '07:40:00', 'jam_selesai' => '08:20:00', 'is_active' => true],
            ),
        ]);

        foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $weekday) {
            foreach ($periods as $period) {
                Jadwal::updateOrCreate(
                    [
                        'guru_id' => $teacher->id,
                        'kelas_id' => $class->id,
                        'mapel_id' => $subject->id,
                        'jam_pelajaran_id' => $period->id,
                        'hari' => $weekday,
                    ],
                    ['is_active' => true],
                );
            }
        }

        $demoDate = $this->demoDate();
        $classEvent = $this->demoEvent(
            'Demo: kegiatan kelas RPL',
            $demoDate,
            'kelas_tertentu',
            [$class->id],
            earlyDismissalAt: '10:00',
        );
        $teacherEvent = $this->demoEvent(
            'Demo: lokakarya guru',
            $demoDate,
            'guru_tertentu',
            [$teacher->id],
        );

        foreach ($classEvent->targetUsers()->get() as $participant) {
            $classEvent->attendances()->firstOrCreate(
                ['user_id' => $participant->id],
                ['participant_type' => 'siswa'],
            );
        }
        foreach ($teacherEvent->targetUsers()->get() as $participant) {
            $teacherEvent->attendances()->firstOrCreate(
                ['user_id' => $participant->id],
                ['participant_type' => 'guru'],
            );
        }

        $journal = Jurnal::firstOrNew([
            'guru_id' => $teacher->id,
            'materi' => self::JOURNAL_MATERIAL,
        ]);
        $journal->fill([
            'kelas_id' => $class->id,
            'mapel_id' => $subject->id,
            'jam_mulai_id' => $periods->first()->id,
            'jam_selesai_id' => $periods->last()->id,
            'tanggal' => $demoDate->toDateString(),
            'status_guru' => 'Hadir',
            'learning_mode' => 'tatap_muka',
            'school_event_id' => $teacherEvent->id,
            'tujuan_pembelajaran' => 'Siswa memahami dasar halaman web dan elemen HTML.',
            'kegiatan' => 'Guru mendemonstrasikan halaman sederhana; siswa mencoba secara berpasangan.',
            'tugas' => 'Buat satu halaman profil sederhana.',
            'status_verifikasi' => 'Menunggu',
        ])->save();

        foreach ($students as $student) {
            Absensi::updateOrCreate(
                ['jurnal_id' => $journal->id, 'siswa_id' => $student->id],
                ['status' => 'H', 'catatan' => null],
            );
        }

        $dispensation = Dispensasi::firstOrNew([
            'siswa_id' => $students->first()->id,
            'alasan' => self::DISPENSATION_REASON,
        ]);
        $dispensation->fill([
            'tanggal' => $demoDate->toDateString(),
            'jam_mulai_id' => $periods->first()->id,
            'jam_selesai_id' => $periods->last()->id,
            'status_piket' => 'Menunggu',
            'piket_id' => null,
            'verified_piket_at' => null,
            'status_admin' => 'Menunggu',
            'admin_id' => null,
            'verified_admin_at' => null,
            'waka_id' => null,
            'verified_waka_at' => null,
            'status_akhir' => 'Menunggu',
            'catatan_verifikasi' => null,
        ])->save();

        $this->command?->info('Demo accounts seeded. Shared password: '.self::PASSWORD);
        $this->command?->table(['Role', 'Username', 'Login tambahan'], [
            ['Admin', 'demo.admin', ''],
            ['Waka', 'demo.waka', ''],
            ['Guru / Wali Kelas', 'demo.guru', ''],
            ['Siswa', 'demo.siswa', 'NIS DEMO-SISWA-01'],
            ['Sekretaris', 'demo.sekretaris', ''],
            ['Piket', 'demo.piket', ''],
        ]);
        $this->command?->line('Demo class: '.$class->nama_kelas.' · '.$demoDate->format('d-m-Y'));
    }

    private function demoUser(string $suffix, string $name, string $role): User
    {
        return User::updateOrCreate(
            ['username' => 'demo.'.$suffix],
            [
                'name' => $name,
                'email' => 'demo.'.$suffix.'@example.test',
                'password' => Hash::make(self::PASSWORD),
                'role' => $role,
                'is_active' => true,
            ],
        );
    }

    /** @param array<int, int> $participantIds */
    private function demoEvent(
        string $title,
        Carbon $eventDate,
        string $participantScope,
        array $participantIds,
        ?string $earlyDismissalAt = null,
    ): SchoolEvent {
        return SchoolEvent::updateOrCreate(
            ['title' => $title],
            [
                'event_date' => $eventDate->toDateString(),
                'event_type' => 'Kegiatan demo',
                'description' => 'Kegiatan contoh untuk demonstrasi akun demo.',
                'participant_scope' => $participantScope,
                'participant_ids' => $participantIds,
                'activity_start' => '08:00',
                'activity_end' => '12:00',
                'early_dismissal_at' => $earlyDismissalAt,
                'attendance_enabled' => false,
                'attendance_mode' => 'normal',
                'location_mode' => 'school',
            ],
        );
    }

    private function demoDate(): Carbon
    {
        $existingEvent = SchoolEvent::whereIn('title', ['Demo: kegiatan kelas RPL', 'Demo: lokakarya guru'])
            ->orderBy('event_date')
            ->first();
        if ($existingEvent) {
            return $existingEvent->event_date->copy();
        }

        for ($offset = 0; $offset <= 60; $offset++) {
            $date = today()->addDays($offset);
            $weekday = $date->locale('id')->translatedFormat('l');
            if ($weekday === 'Minggu'
                || NationalHoliday::whereDate('holiday_date', $date)->exists()
                || SchoolEvent::whereDate('event_date', $date)->exists()) {
                continue;
            }

            return $date;
        }

        throw new RuntimeException('Could not find an available school date for the demo records.');
    }
}
