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
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin
        $userAdmin = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Testing',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Akun Guru
        $userGuru = User::updateOrCreate(
            ['email' => 'guru@test.com'],
            [
                'name' => 'Guru Testing',
                'username' => 'guru.testing',
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

        $mapel = Mapel::updateOrCreate(
            ['kode_mapel' => 'RPL'],
            [
                'nama_mapel' => 'Rekayasa Perangkat Lunak',
            ]
        );

        $guru->mapels()->syncWithoutDetaching([$mapel->id]);

        $mapelJadwal = [
            'BHS-IND' => 'Bahasa Indonesia',
            'PJOK' => 'PJOK',
            'BHS-ING' => 'Bahasa Inggris',
            'BHS-JAW' => 'Bahasa Jawa',
            'SENI' => 'Seni Budaya',
            'IPAS' => 'IPAS',
            'PAI' => 'Pendidikan Agama Islam dan Budi Pekerti',
            'PANCASILA' => 'Pendidikan Pancasila',
            'SEJARAH' => 'Sejarah',
            'BK' => 'Bimbingan Konseling',
            'INFORMATIKA' => 'Informatika',
            'MAT' => 'Matematika',
            'BHS-JEP' => 'Bahasa Jepang',
            'KODING-AI' => 'Koding dan Kecerdasan Artifisial',
            'DASAR-TKI' => 'Dasar TKI',
            'DASAR-PPLG' => 'Dasar PPLG',
            'DASAR-TJKT' => 'Dasar TJKT',
            'DASAR-PM' => 'Dasar PM',
            'DASAR-MPLB' => 'Dasar MPLB',
            'DASAR-ULP' => 'Dasar ULP',
            'DASAR-DKV' => 'Dasar DKV',
            'DASAR-BP' => 'Dasar BP',
            'DASAR-AN' => 'Dasar AN',
            'KONS-TKI' => 'Konsentrasi TKI',
            'KONS-RPL' => 'Konsentrasi RPL',
            'KONS-TKJ' => 'Konsentrasi TKJ',
            'KONS-BD' => 'Konsentrasi BD',
            'KONS-MP' => 'Konsentrasi MP',
            'KONS-AK' => 'Konsentrasi AK',
            'KONS-ULW' => 'Konsentrasi ULW',
            'KONS-DKV' => 'Konsentrasi DKV',
            'KONS-PSPT' => 'Konsentrasi PSPT',
            'KONS-AN' => 'Konsentrasi AN',
            'KIK' => 'Kreativitas, Inovasi, dan Kewirausahaan',
            'PIL-TKI' => 'Mapel Pilihan TKI',
            'PIL-RPL' => 'Mapel Pilihan RPL',
            'PIL-TKJ' => 'Mapel Pilihan TKJ',
            'PIL-BD' => 'Mapel Pilihan BD',
            'PIL-MP' => 'Mapel Pilihan MP',
            'PIL-AK' => 'Mapel Pilihan AK',
            'PIL-DKV' => 'Mapel Pilihan DKV',
            'PIL-PSPT' => 'Mapel Pilihan PSPT',
            'PIL-AN' => 'Mapel Pilihan AN',
        ];

        foreach ($mapelJadwal as $kode => $nama) {
            Mapel::updateOrCreate(
                ['kode_mapel' => $kode],
                ['nama_mapel' => $nama]
            );
        }

        $guruJadwal = [
            'Yani, S.Pd.',
            'Ilham Sungeidi, S.Pd',
            "Muto'atul Khosi'ah, S.Pd",
            'Yustin Febrini, S.Pd',
            'Anang Prasetyo, S.Pd',
            'Khuriyatul Kamila, S.Si',
            "Rifkotin Na'imah, S.Pd",
            'Muashofah, M.Pd',
            'Endang Ary Handayani, S.T., M.Pd',
            'Sri Kusumastuti, S.Pd',
            'Lutfia Marsalina, S.Pd.I, M.Pd.',
            'Arvia Rienetasary, S.Pd',
            'Wiwik Yuniarsih, S.Pd',
            'Fajar Luthfianto, S.Pd',
            'Yuni Jiastuti, S.Pd',
            'Fitria Renytasari, S.Pd',
            'Mufatiroh, S.Ag',
            'Indriati, S.Pd',
            'Ista Nofasari, S.Pd',
            'Zainul Arifin, S.Pd',
            'Widodo, S.Pd',
            "Elysa Yuli Nur'aini, S.Si",
            'Badrus Sulaiman, S.Pd.',
            'Kurnila Putri Islamawati, S.Pd',
            'Ruly Dwi Setyaningrum, S.Kom',
            'Abdul Rohman, S.Pd',
            'Umi Kulsum, S.Pd',
            'Fitri Amaliyah, S.Pd',
            'Fajar Wahyu Pratiwi, S.S',
            'Dwi Rini Manfaati, S.Pd',
            'Agus Muharyanto, M.Pd',
            'Dwi Kuswanto, S.Pd',
            'Listyana Hartati, S.Kom., M.Pd',
            'Siswanti Purwaningsih, S.T., M.Pd',
            'Nishfu Laili, S.Pd',
            'Sri Rahayu, S.Pd',
            'Basuki Sarjono, S.Pd',
            'Muhammad Fajar Assidiqi, S.Pd',
            'Eko Saputro, S.Pd',
            'Erna Qoriah, S.E.',
            'Rulik Indrawati, S.Pd',
            'Pipit Ambarwati, S.Pd',
            'Baskoro, S.Si',
            'Dian Mawarti, S.Pd',
            'Laili Ermawati, S.Pd',
            'Agus Fahruddy, S.Pd., M.Pd',
            'Sinta Lestari, S.Pd.I',
            'Ayu Puspitorini, ST',
            'Diana Hartanti, S.T., M.Pd',
            'Dra. Hanik Pangestuti',
            'Niken Hari Pratiwi, S.Psi., M.Pd',
            'Nur Eko Wahyuningsih, S.Pd',
            'Tuhu Eries Kudori, S.Sn',
            'Rika Okta Maulida, S.Ds.',
            'Mega Mahardika, S.Pd',
            'Dhuana Putri Puspitasary, S.Pd',
            'Andika Christian Sasmita, S.ST',
            'Endik Kuswantoro, S.Kom., M.T',
            'Erwan Septiyono, S.Pd',
            'Ninik Sriwidayati, S.Pd., M.Pd',
            'Titin Sukmasari, S.Pd., M.Pd',
            'Setiyo Winarko, S.Pd',
            'Agustina Mardika Rini, S.Pd., M.Pd',
            'Dyah Esti Rahayu, S.Pd',
            'Indayah, S.Pd., M.Pd',
            'Astra Bella Flamboyan, S.Psi',
            'Sulistyowati, SS',
            'Martiin, S.Pd',
            'Tutut Sriatin, S.Pd',
            'Peni Wulandari, S.Pd',
            'Titik Samsistini, S.Pd',
            'Sunarti, S.Pd',
            'Veronica Damay Rulitasari, S.Pd',
            "Mas'an Widodo, S.Pd., M.T",
            'Istiana Suhartati, S.T',
            'Joko Priyanto, S.Kom',
            'Benny Mamora, S.Kom',
            'Siti Umiharsih, S.Pd',
            "Sa'ad Wazis Hiedayat, S.Pd",
            'Nurul Azizah, S.Pd',
            'Agung Yulianto, S.Pd',
            'Niken Dewi Hastika, S.Pd',
            'Risqi Nur Imama, S.Tr.Par',
            'Nur Nastutisari, S.ST.Par.',
            'Endang Safitri, S.Pd',
            'Erna Rinawati, S.Pd',
            'Siti Khoiriyah, S.Pd',
            'Arif Setyobudi, S.Pd',
            'Andri Retno Yuli Astuti, S.Pd',
            'Fitria Diah Ayu Hartati, S.Pd',
            'Dra. Anik Indriani',
            'Ary Sunaryo, ST., M.Pd',
            'Siti Munawaroh, S.Kom., M.Pd',
            'Retno Widyastuti, S.Pd., M.Pd',
            'Ratih Dian Irawati, SE',
            'Anisa Kusumawati, S.Pd',
            'Dwi Nova Setyandari, S.Pd',
            'Khoyrotun Hisani, S.Sn',
            'Siti Maisaroh, S.Pd',
            'Winarsih, S.Pd., M.Pd',
        ];

        foreach ($guruJadwal as $namaGuru) {
            $username = Str::of($namaGuru)
                ->lower()
                ->ascii()
                ->replaceMatches('/[^a-z0-9]+/', '.')
                ->trim('.')
                ->toString();
            $email = $username.'@guru.smkn1boyolangu.sch.id';
            $userJadwal = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $namaGuru,
                    'username' => $username,
                    'password' => Hash::make('password123'),
                    'role' => 'guru',
                    'is_active' => true,
                ]
            );

            $guruJadwalRecord = Guru::firstOrNew(['user_id' => $userJadwal->id]);
            $guruJadwalRecord->fill([
                'nama_guru' => $namaGuru,
                'status_kepegawaian' => 'Honorer',
            ]);

            if (! $guruJadwalRecord->exists) {
                $guruJadwalRecord->nip = '1990'.str_pad((string) (Guru::max('id') + 1), 11, '0', STR_PAD_LEFT);
            }

            $guruJadwalRecord->save();
        }

        foreach ([
            [1, '07:00', '07:40'],
            [2, '07:40', '08:20'],
            [3, '08:20', '09:00'],
            [4, '09:00', '09:40'],
            [5, '10:00', '10:40'],
            [6, '10:40', '11:20'],
            [7, '11:20', '12:00'],
            [8, '13:00', '13:40'],
            [9, '13:40', '14:20'],
            [10, '14:20', '15:00'],
            [11, '14:00', '14:30'],
            [12, '14:30', '15:00'],
            [13, '15:00', '15:30'],
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

        // Akun Piket
        $userPiket = User::updateOrCreate(
            ['email' => 'piket@test.com'],
            [
                'name' => 'Piket Testing',
                'username' => 'piket',
                'password' => Hash::make('password123'),
                'role' => 'piket',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'sekretaris@test.com'],
            [
                'name' => 'Sekretaris Testing',
                'username' => 'sekretaris',
                'password' => Hash::make('password123'),
                'role' => 'sekretaris',
                'is_active' => true,
            ]
        );

        // Akun Siswa
        $userSiswa = User::updateOrCreate(
            ['email' => 'siswa@test.com'],
            [
                'name' => 'Siswa Testing',
                'username' => 'siswa.testing',
                'password' => Hash::make('password123'),
                'role' => 'siswa',
                'is_active' => true,
            ]
        );

        // Data Kelas
        $kelas = Kelas::updateOrCreate(
            ['nama_kelas' => 'XI RPL 2'],
            [
                'tingkat' => 'XI',
            ]
        );

        // Data Siswa
        $siswa = Siswa::updateOrCreate(
            ['user_id' => $userSiswa->id],
            [
                'kelas_id' => $kelas->id,
                'nis' => '123456',
                'nama_siswa' => 'Siswa Testing',
                'jenis_kelamin' => 'L',
            ]
        );

        $userSiswaDua = User::updateOrCreate(
            ['email' => 'siswa2@test.com'],
            [
                'name' => 'Siswa Dua Testing',
                'username' => 'siswa.dua.testing',
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

        $jamPertama = JamPelajaran::where('jam_ke', 1)->firstOrFail();
        $jamKedua = JamPelajaran::where('jam_ke', 2)->firstOrFail();
        $tanggalDemo = now()->toDateString();
        $hariDemo = now()->locale('id')->translatedFormat('l');

        $jadwal = Jadwal::updateOrCreate(
            [
                'guru_id' => $guru->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'jam_pelajaran_id' => $jamPertama->id,
                'hari' => $hariDemo,
            ],
            ['is_active' => true]
        );

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

        Absensi::updateOrCreate(
            ['jurnal_id' => $jurnal->id, 'siswa_id' => $siswa->id],
            ['status' => 'H', 'catatan' => null]
        );
        Absensi::updateOrCreate(
            ['jurnal_id' => $jurnal->id, 'siswa_id' => $siswaDua->id],
            ['status' => 'H', 'catatan' => null]
        );

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
