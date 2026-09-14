<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\JadwalSemesterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function activeJournalSchedule(): array
{
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => 'SCHEDULE-1', 'nama_guru' => 'Yani, S.Pd.', 'status_kepegawaian' => 'Honorer']);
    $kelas = Kelas::create(['nama_kelas' => 'X TKI 1', 'tingkat' => 'X']);
    $mapel = Mapel::create(['nama_mapel' => 'Bahasa Indonesia', 'kode_mapel' => 'BIND']);
    $periods = collect([
        [7, '11:10:00', '11:45:00', '10:20:00', '10:50:00'],
        [8, '13:15:00', '13:50:00', '10:50:00', '11:20:00'],
        [9, '13:50:00', '14:25:00', '13:00:00', '13:30:00'],
    ])->map(fn ($item) => JamPelajaran::create([
        'jam_ke' => $item[0], 'jam_mulai' => $item[1], 'jam_selesai' => $item[2],
        'jam_mulai_jumat' => $item[3], 'jam_selesai_jumat' => $item[4], 'is_active' => true,
    ]));
    $schedules = $periods->take(2)->map(fn ($period) => Jadwal::create([
        'guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
        'jam_pelajaran_id' => $period->id, 'hari' => 'Senin', 'is_active' => true,
    ]));

    return compact('user', 'guru', 'kelas', 'mapel', 'periods', 'schedules');
}

it('fills the entire consecutive lesson block and ignores manual selection', function () {
    $data = activeJournalSchedule();
    $this->travelTo(Carbon::parse('2026-09-14 13:20:00', 'Asia/Jakarta'));

    $this->actingAs($data['user'])->get(route('jurnal.create', ['kelas_id' => 999, 'jadwal_id' => 999]))
        ->assertSee('X TKI 1')->assertSee('Bahasa Indonesia')->assertSee('11:10 (jam ke-7)')->assertSee('13:50 (jam ke-8)')
        ->assertDontSee('Cari kelas')->assertDontSee('name="kelas_id"', false);
});

it('saves status only with server derived dates and lesson boundaries', function () {
    $data = activeJournalSchedule();
    $this->travelTo(Carbon::parse('2026-09-14 11:20:00', 'Asia/Jakarta'));

    $this->actingAs($data['user'])->post(route('jurnal.store'), [
        'jadwal_id' => $data['schedules']->first()->id, 'status_guru' => 'Hadir',
        'tanggal' => '2000-01-01', 'guru_id' => 999, 'kelas_id' => 999, 'mapel_id' => 999,
        'jam_mulai_id' => 999, 'jam_selesai_id' => 999,
    ])->assertSessionHasNoErrors()->assertRedirect();

    $this->assertDatabaseHas('jurnals', [
        'guru_id' => $data['guru']->id, 'kelas_id' => $data['kelas']->id, 'mapel_id' => $data['mapel']->id,
        'jam_mulai_id' => $data['periods'][0]->id, 'jam_selesai_id' => $data['periods'][1]->id,
        'status_guru' => 'Hadir', 'materi' => '',
    ]);
    expect(Jurnal::firstOrFail()->tanggal->toDateString())->toBe('2026-09-14');
});

it('does not permit journals before during breaks or after teaching', function (string $time) {
    $data = activeJournalSchedule();
    $this->travelTo(Carbon::parse($time, 'Asia/Jakarta'));

    $this->actingAs($data['user'])->get(route('jurnal.create'))->assertSee('Tidak ada jadwal mengajar yang sedang berlangsung.')->assertDontSee('id="journal-form"', false);
    $this->post(route('jurnal.store'), ['jadwal_id' => $data['schedules']->first()->id, 'status_guru' => 'Hadir'])
        ->assertSessionHasErrors('jadwal_id');

    $this->assertDatabaseCount('jurnals', 0);
})->with(['before' => '2026-09-14 11:09:59', 'break starts' => '2026-09-14 11:45:00', 'lunch' => '2026-09-14 12:30:00', 'end' => '2026-09-14 13:50:00', 'other day' => '2026-09-15 11:20:00']);

it('uses the Friday timetable instead of weekday times', function () {
    $data = activeJournalSchedule();
    Jadwal::query()->update(['hari' => 'Jumat']);
    $this->travelTo(Carbon::parse('2026-09-18 10:30:00', 'Asia/Jakarta'));

    $this->actingAs($data['user'])->get(route('jurnal.create'))
        ->assertSee('10:20 (jam ke-7)')->assertSee('11:20 (jam ke-8)');
});

it('rejects a form from an earlier lesson when another lesson has begun', function () {
    $data = activeJournalSchedule();
    $otherClass = Kelas::create(['nama_kelas' => 'X TKI 2', 'tingkat' => 'X']);
    Jadwal::create(['guru_id' => $data['guru']->id, 'kelas_id' => $otherClass->id, 'mapel_id' => $data['mapel']->id,
        'jam_pelajaran_id' => $data['periods'][2]->id, 'hari' => 'Senin', 'is_active' => true]);
    $this->travelTo(Carbon::parse('2026-09-14 14:00:00', 'Asia/Jakarta'));

    $this->actingAs($data['user'])->post(route('jurnal.store'), ['jadwal_id' => $data['schedules']->first()->id, 'status_guru' => 'Hadir'])
        ->assertSessionHasErrors('jadwal_id');

    $this->assertDatabaseCount('jurnals', 0);
});

it('blocks ambiguous overlapping schedules', function () {
    $data = activeJournalSchedule();
    $otherClass = Kelas::create(['nama_kelas' => 'X TKI 2', 'tingkat' => 'X']);
    Jadwal::create(['guru_id' => $data['guru']->id, 'kelas_id' => $otherClass->id, 'mapel_id' => $data['mapel']->id,
        'jam_pelajaran_id' => $data['periods'][0]->id, 'hari' => 'Senin', 'is_active' => true]);
    $this->travelTo(Carbon::parse('2026-09-14 11:20:00', 'Asia/Jakarta'));

    $this->actingAs($data['user'])->get(route('jurnal.create'))->assertSee('Ada jadwal mengajar yang bertabrakan.');
    $this->post(route('jurnal.store'), ['status_guru' => 'Hadir'])->assertSessionHasErrors('jadwal_id');

    $this->assertDatabaseCount('jurnals', 0);
});

it('keeps lesson identity fixed when editing a saved journal outside teaching hours', function () {
    $data = activeJournalSchedule();
    $journal = Jurnal::create(['guru_id' => $data['guru']->id, 'kelas_id' => $data['kelas']->id, 'mapel_id' => $data['mapel']->id,
        'jam_mulai_id' => $data['periods'][0]->id, 'jam_selesai_id' => $data['periods'][1]->id, 'tanggal' => '2026-09-14', 'status_guru' => 'Hadir', 'materi' => '']);
    $this->travelTo(Carbon::parse('2026-09-15 20:00:00', 'Asia/Jakarta'));

    $this->actingAs($data['user'])->put(route('jurnal.update', $journal), [
        'status_guru' => 'Izin', 'kelas_id' => 999, 'tanggal' => '2000-01-01', 'jam_mulai_id' => 999,
    ])->assertSessionHasNoErrors();

    expect($journal->fresh()->tanggal->toDateString())->toBe('2026-09-14');
    $this->assertDatabaseHas('jurnals', ['id' => $journal->id, 'kelas_id' => $data['kelas']->id,
        'jam_mulai_id' => $data['periods'][0]->id, 'status_guru' => 'Izin']);
});

it('imports the official schedule without resetting existing teacher credentials and can run twice', function () {
    $user = User::factory()->create(['name' => 'Yani, S.Pd.', 'role' => 'guru']);
    $guru = Guru::create(['user_id' => $user->id, 'nip' => 'REAL-NIP', 'nama_guru' => 'Yani, S.Pd.', 'status_kepegawaian' => 'PNS']);
    $password = $user->password;
    $this->travelTo(Carbon::parse('2026-09-15 08:30:00', 'Asia/Jakarta'));

    $this->seed(JadwalSemesterSeeder::class);
    $count = Jadwal::count();
    $this->seed(JadwalSemesterSeeder::class);

    expect(Jadwal::count())->toBe($count);
    expect($user->fresh()->password)->toBe($password);
    expect(Kelas::count())->toBe(48);
    $session = Jadwal::sessionsForGuru($guru, now())->firstWhere('active', true);
    expect($session)->toMatchArray(['kelas' => 'X TKI 1', 'mapel' => 'Bahasa Indonesia', 'jam_mulai' => '08:20:00', 'jam_selesai' => '09:40:00']);
});
