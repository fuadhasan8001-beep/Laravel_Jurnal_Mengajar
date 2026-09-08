```php
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
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==============================
        // AKUN ADMIN
        // ==============================
        $userAdmin = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Testing',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // ==============================
        // AKUN GURU
        // ==============================
        $userGuru = User::updateOrCreate(
            ['email' => 'guru@test.com'],
            [
                'name' => 'Guru Testing',
                'password' => Hash::make('password123'),
                'role' => 'guru',
                'is_active' => true,
            ]
        );

        $guru = Guru::updateOrCreate(
            ['user_id' => $userGuru->id],
            [
                'nip' => '19800101202601001',
                'nama_guru' => 'Guru Testing',
                'status_kepegawaian' => 'Honorer',
            ]
        );

        // ==============================
        // MATA PELAJARAN
        // ==============================
        $mapel = Mapel::updateOrCreate(
            ['kode_mapel' => 'RPL'],
            [
                'nama_mapel' => 'Rekayasa Perangkat Lunak',
            ]
        );

        $guru->mapels()->syncWithoutDetaching([$mapel->id]);

        // ==============================
        // JAM PELAJARAN
        // ==============================
        foreach ([
            [1, '07:00', '07:45'],
            [2, '07:45', '08:30'],
            [3, '08:30', '09:15'],
            [4, '09:30', '10:15'],
            [5, '10:15', '11:00'],
        ] as [$jamKe, $jamMulai, $jamSelesai]) {
            JamPelajaran::updateOrCreate(
                ['jam_ke' => $jamKe],
                [
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'is_active' => true,
                ]
            );
        }

        // ==============================
        // AKUN PIKET
        // ==============================
        $userPiket = User::updateOrCreate(
            ['email' => 'piket@test.com'],
            [
                'name' => 'Piket Testing',
                'password' => Hash::make('password123'),
                'role' => 'piket',
                'is_active' => true,
            ]
        );

        // ==============================
        // AKUN SEKRETARIS
        // ==============================
        User::updateOrCreate(
            ['email' => 'sekretaris@test.com'],
            [
                'name' => 'Sekretaris Testing',
                'password' => Hash::make('password123'),
                'role' => 'sekretaris',
                'is_active' => true,
            ]
        );

        // ==============================
        // AKUN SISWA
        // ==============================
        $userSiswa = User::updateOrCreate(
            ['email' => 'siswa@test.com'],
            [
                'name' => 'Siswa Testing',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'is_active' => true,
            ]
        );

        // ==============================
        // DATA KELAS
        // ==============================
        $kelas = Kelas::updateOrCreate(
            ['nama_kelas' => 'XI RPL 2'],
            [
                'tingkat' => 'XI',
            ]
        );

        // ==============================
        // SISWA 1
        // ==============================
        $siswa = Siswa::updateOrCreate(
            ['user_id' => $userSiswa->id],
            [
                'kelas_id' => $kelas->id,
                'nis' => '123456',
                'nama_siswa' => 'Siswa Testing',
                'jenis_kelamin' => 'L',
            ]
        );

        // ==============================
        // SISWA 2
        // ==============================
        $userSiswaDua = User::updateOrCreate(
            ['email' => 'siswa2@test.com'],
            [
                'name' => 'Siswa Dua Testing',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'is_active' => true,
            ]
        );

        $siswaDua = Siswa::updateOrCreate(
            ['user_id' => $userSiswaDua->id],
            [
                'kelas_id' => $kelas->id,
                'nis' => '123457',
                'nama_siswa' => 'Siswa Dua Testing',
                'jenis_kelamin' => 'P',
            ]
        );

        // ==============================
        // JAM DEMO
        // ==============================
        $jamPertama = JamPelajaran::where('jam_ke', 1)->firstOrFail();
        $jamKedua = JamPelajaran::where('jam_ke', 2)->firstOrFail();

        $tanggalDemo = now()->toDateString();
        $hariDemo = now()->locale('id')->translatedFormat('l');

        // ==============================
        // JADWAL DEMO
        // ==============================
        Jadwal::updateOrCreate(
            [
                'guru_id' => $guru->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'jam_pelajaran_id' => $jamPertama->id,
                'hari' => $hariDemo,
            ],
            [
                'is_active' => true,
            ]
        );

        // ==============================
        // JURNAL DEMO
        // ==============================
        $demoJurnals = Jurnal::where('guru_id', $guru->id)
            ->whereDate('tanggal', $tanggalDemo)
            ->where('materi', 'Pengenalan jurnal mengajar')
            ->orderBy('id')
            ->get();

        $jurnal = $demoJurnals->first();

        $demoJurnals->skip(1)->each->delete();

        if (! $jurnal) {
            $jurnal = new Jurnal;
        }

        $jurnal->fill([
            'guru_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'jam_mulai_id' => $jamPertama->id,
            'jam_selesai_id' => $jamKedua->id,
            'tanggal' => $tanggalDemo,
            'status_guru' => 'Hadir',
            'materi' => 'Pengenalan jurnal mengajar',
            'tujuan_pembelajaran' => 'Siswa memahami alur pembelajaran.',
            'kegiatan' => 'Diskusi dan latihan singkat.',
            'status_verifikasi' => 'Menunggu',
        ]);

        $jurnal->save();

        // ==============================
        // ABSENSI DEMO
        // ==============================
        Absensi::updateOrCreate(
            [
                'jurnal_id' => $jurnal->id,
                'siswa_id' => $siswa->id,
            ],
            [
                'status' => 'H',
                'catatan' => null,
            ]
        );

        Absensi::updateOrCreate(
            [
                'jurnal_id' => $jurnal->id,
                'siswa_id' => $siswaDua->id,
            ],
            [
                'status' => 'H',
                'catatan' => null,
            ]
        );

        // ==============================
        // DISPENSASI DEMO
        // ==============================
        $demoDispensasis = Dispensasi::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', $tanggalDemo)
            ->where('alasan', 'Kegiatan sekolah.')
            ->orderBy('id')
            ->get();

        $dispensasi = $demoDispensasis->first();

        $demoDispensasis->skip(1)->each->delete();

        if (! $dispensasi) {
            $dispensasi = new Dispensasi;
        }

        $dispensasi->fill([
            'siswa_id' => $siswa->id,
            'tanggal' => $tanggalDemo,
            'jam_mulai_id' => $jamPertama->id,
            'jam_selesai_id' => $jamKedua->id,
            'alasan' => 'Kegiatan sekolah.',
            'status_piket' => 'Disetujui',
            'piket_id' => $userPiket->id,
            'verified_piket_at' => now(),
            'status_admin' => 'Disetujui',
            'admin_id' => $userAdmin->id,
            'verified_admin_at' => now(),
            'status_akhir' => 'Disetujui',
            'catatan_verifikasi' => 'Data demo.',
        ]);

        $dispensasi->save();
    }
}
```
