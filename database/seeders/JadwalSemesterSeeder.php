<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JadwalSemesterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = json_decode(file_get_contents(__DIR__.'/jadwal_ganjil_2026.json'), true, 512, JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($data): void {
            $regular = [['07:00', '07:40'], ['07:40', '08:20'], ['08:20', '09:00'], ['09:00', '09:40'],
                ['10:00', '10:40'], ['10:40', '11:20'], ['11:20', '12:00'], ['13:00', '13:40'],
                ['13:40', '14:20'], ['14:20', '15:00'], ['15:00', '15:30'], ['15:30', '16:00'],
                ['16:00', '16:30']];
            $friday = [['07:00', '07:30'], ['07:30', '08:00'], ['08:00', '08:30'], ['08:30', '09:00'],
                ['09:00', '09:30'], ['09:50', '10:20'], ['10:20', '10:50'], ['10:50', '11:20'],
                ['13:00', '13:30'], ['13:30', '14:00'], ['14:00', '14:30'], ['14:30', '15:00'], ['15:00', '15:30']];
            foreach ($friday as $index => [$start, $end]) {
                JamPelajaran::updateOrCreate(['jam_ke' => $index + 1], [
                    'jam_mulai' => ($regular[$index][0] ?? $start).':00',
                    'jam_selesai' => ($regular[$index][1] ?? $end).':00',
                    'jam_mulai_jumat' => $start.':00', 'jam_selesai_jumat' => $end.':00', 'is_active' => true,
                ]);
            }
            $periods = JamPelajaran::all()->keyBy('jam_ke');
            $teachers = Guru::all()->keyBy(fn (Guru $guru): string => $this->identity($guru->nama_guru));
            $subjects = Mapel::all()->keyBy(fn (Mapel $mapel): string => $this->identity($mapel->nama_mapel));
            foreach ($data['schedules'] as $row) {
                $teacherKey = $this->identity($row['guru']);
                $guru = $teachers->get($teacherKey);
                if (! $guru) {
                    $username = Str::slug($row['guru'], '.');
                    $user = User::firstOrCreate(['username' => $username], [
                        'name' => $row['guru'], 'email' => $username.'@guru.smkn1boyolangu.sch.id',
                        'password' => Str::random(40), 'role' => 'guru', 'is_active' => true,
                    ]);
                    $guru = Guru::firstOrCreate(['user_id' => $user->id], [
                        'nama_guru' => $row['guru'], 'nip' => 'JADWAL-'.$user->id,
                        'status_kepegawaian' => 'Honorer',
                    ]);
                    $teachers->put($teacherKey, $guru);
                }
                $subjectKey = $this->identity($row['mapel']);
                $mapel = $subjects->get($subjectKey);
                if (! $mapel) {
                    $mapel = Mapel::create(['nama_mapel' => $row['mapel'], 'kode_mapel' => 'PDF-'.strtoupper(substr(sha1($subjectKey), 0, 12))]);
                    $subjects->put($subjectKey, $mapel);
                }
                $kelas = Kelas::firstOrCreate(['nama_kelas' => $row['kelas']], ['tingkat' => explode(' ', $row['kelas'])[0]]);
                $guru->mapels()->syncWithoutDetaching([$mapel->id]);
                for ($period = $row['mulai']; $period <= $row['selesai']; $period++) {
                    $jadwal = Jadwal::updateOrCreate([
                        'kelas_id' => $kelas->id, 'hari' => $row['hari'], 'jam_pelajaran_id' => $periods[$period]->id,
                    ], ['guru_id' => $guru->id, 'mapel_id' => $mapel->id, 'is_active' => true]);
                    Jadwal::where('kelas_id', $kelas->id)->where('hari', $row['hari'])
                        ->where('jam_pelajaran_id', $periods[$period]->id)->where('id', '!=', $jadwal->id)
                        ->update(['is_active' => false]);
                }
            }
        });
    }

    private function identity(string $name): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($name)));
    }
}
