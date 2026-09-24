<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\JadwalSemesterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not duplicate a saved lesson or overwrite its attendance on resubmission', function (string $verification) {
    $data = activeJournalSchedule();
    $this->travelTo(Carbon::parse('2026-09-14 11:20:00', 'Asia/Jakarta'));
    $student = Siswa::create(['user_id' => User::factory()->create(['role' => 'siswa'])->id,
        'kelas_id' => $data['kelas']->id, 'nis' => 'DUPLICATE-1', 'nama_siswa' => 'Siswa Uji', 'jenis_kelamin' => 'L']);
    $this->actingAs($data['user'])->post(route('jurnal.store'), [
        'status_guru' => 'Hadir', 'materi' => 'Materi pertama',
        'absensi' => [['siswa_id' => $student->id, 'status' => 'S', 'catatan' => 'Demam']],
    ])->assertSessionHasNoErrors();
    $journal = Jurnal::firstOrFail();
    $journal->update(['status_verifikasi' => $verification]);

    $this->post(route('jurnal.store'), ['status_guru' => 'Izin', 'materi' => 'Materi kedua',
        'absensi' => [['siswa_id' => $student->id, 'status' => 'H']],
    ])->assertRedirect(route('jurnal.show', $journal))
        ->assertSessionHas('success', 'Jurnal untuk sesi ini sudah diisi. Data sebelumnya tetap tersimpan.');

    $this->assertDatabaseCount('jurnals', 1);
    $this->assertDatabaseHas('jurnals', ['id' => $journal->id, 'materi' => 'Materi pertama', 'status_guru' => 'Hadir', 'status_verifikasi' => $verification]);
    $this->assertDatabaseHas('absensis', ['jurnal_id' => $journal->id, 'siswa_id' => $student->id, 'status' => 'S', 'catatan' => 'Demam']);
})->with(['Menunggu', 'Disetujui', 'Ditolak']);

it('replaces the creation form with links to the saved lesson', function (string $verification) {
    $data = activeJournalSchedule();
    $this->travelTo(Carbon::parse('2026-09-14 11:20:00', 'Asia/Jakarta'));
    $this->actingAs($data['user'])->post(route('jurnal.store'), ['status_guru' => 'Hadir'])->assertSessionHasNoErrors();
    $journal = Jurnal::firstOrFail();
    $journal->update(['status_verifikasi' => $verification]);

    $response = $this->get(route('jurnal.create'));

    $response->assertSee('Jurnal untuk sesi ini sudah diisi.')->assertSee(route('jurnal.show', $journal))
        ->assertDontSee('id="journal-form"', false);
    if ($verification === 'Menunggu') {
        $response->assertSee(route('jurnal.edit', $journal));
    } else {
        $response->assertDontSee(route('jurnal.edit', $journal));
    }
})->with(['Menunggu', 'Disetujui', 'Ditolak']);

it('allows the same lesson on another date', function () {
    $data = activeJournalSchedule();
    $this->travelTo(Carbon::parse('2026-09-14 11:20:00', 'Asia/Jakarta'));
    $this->actingAs($data['user'])->post(route('jurnal.store'), ['status_guru' => 'Hadir'])->assertSessionHasNoErrors();
    $this->travelTo(Carbon::parse('2026-09-21 11:20:00', 'Asia/Jakarta'));

    $this->post(route('jurnal.store'), ['status_guru' => 'Hadir'])->assertSessionHasNoErrors();

    $this->assertDatabaseCount('jurnals', 2);
});

it('allows another session in the same class on the same date', function () {
    $data = activeJournalSchedule();
    $this->travelTo(Carbon::parse('2026-09-14 11:20:00', 'Asia/Jakarta'));
    $this->actingAs($data['user'])->post(route('jurnal.store'), ['status_guru' => 'Hadir'])->assertSessionHasNoErrors();
    $period = JamPelajaran::create(['jam_ke' => 10, 'jam_mulai' => '14:25:00', 'jam_selesai' => '15:00:00', 'is_active' => true]);
    Jadwal::create(['guru_id' => $data['guru']->id, 'kelas_id' => $data['kelas']->id, 'mapel_id' => $data['mapel']->id,
        'jam_pelajaran_id' => $period->id, 'hari' => 'Senin', 'is_active' => true]);
    $this->travelTo(Carbon::parse('2026-09-14 14:30:00', 'Asia/Jakarta'));

    $this->post(route('jurnal.store'), ['status_guru' => 'Hadir'])->assertSessionHasNoErrors();

    $this->assertDatabaseCount('jurnals', 2);
    $this->assertDatabaseHas('jurnals', ['jam_mulai_id' => $period->id, 'jam_selesai_id' => $period->id]);
});

it('keeps another active class accessible after the first class has been filled', function () {
    $data = activeJournalSchedule();
    $this->travelTo(Carbon::parse('2026-09-14 11:20:00', 'Asia/Jakarta'));
    $this->actingAs($data['user'])->post(route('jurnal.store'), ['status_guru' => 'Hadir'])->assertSessionHasNoErrors();
    $kelas = Kelas::create(['nama_kelas' => 'X TKI 2', 'tingkat' => 'X']);
    $schedule = Jadwal::create(['guru_id' => $data['guru']->id, 'kelas_id' => $kelas->id, 'mapel_id' => $data['mapel']->id,
        'jam_pelajaran_id' => $data['periods'][0]->id, 'hari' => 'Senin', 'is_active' => true]);

    $this->get(route('jurnal.create'))->assertSee(route('jurnal.create', ['jadwal_id' => $schedule->id]));
    $this->get(route('jurnal.create', ['jadwal_id' => $schedule->id]))->assertSee('id="journal-form"', false);
    $this->post(route('jurnal.store'), ['jadwal_id' => $schedule->id, 'status_guru' => 'Hadir'])->assertSessionHasNoErrors();

    $this->assertDatabaseCount('jurnals', 2);
    $this->assertDatabaseHas('jurnals', ['kelas_id' => $kelas->id]);
});

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

it('only exposes and accepts schedules belonging to the logged in teacher', function () {
    $data = activeJournalSchedule();
    $otherUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $otherGuru = Guru::create(['user_id' => $otherUser->id, 'nip' => 'SCHEDULE-2', 'nama_guru' => 'Guru Lain', 'status_kepegawaian' => 'Honorer']);
    $otherClass = Kelas::create(['nama_kelas' => 'X LAIN', 'tingkat' => 'X']);
    $otherSchedule = Jadwal::create([
        'guru_id' => $otherGuru->id,
        'kelas_id' => $otherClass->id,
        'mapel_id' => $data['mapel']->id,
        'jam_pelajaran_id' => $data['periods'][0]->id,
        'hari' => 'Senin',
        'is_active' => true,
    ]);
    $this->travelTo(Carbon::parse('2026-09-14 11:20:00', 'Asia/Jakarta'));

    $this->actingAs($data['user'])
        ->get(route('jurnal.create'))
        ->assertSee('X TKI 1')
        ->assertDontSee('X LAIN');

    $this->post(route('jurnal.store'), ['jadwal_id' => $otherSchedule->id, 'status_guru' => 'Hadir'])
        ->assertSessionHasErrors('jadwal_id');

    $this->assertDatabaseCount('jurnals', 0);
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

it('prevents a teacher from creating a duplicate journal for the same lesson and date', function () {
    $data = activeJournalSchedule();
    $this->travelTo(Carbon::parse('2026-09-14 13:20:00', 'Asia/Jakarta'));
    $payload = ['jadwal_id' => $data['schedules']->first()->id, 'status_guru' => 'Hadir'];

    $this->actingAs($data['user'])->post(route('jurnal.store'), $payload)->assertRedirect();
    $journal = Jurnal::firstOrFail();
    $this->post(route('jurnal.store'), $payload)
        ->assertRedirect(route('jurnal.show', $journal))
        ->assertSessionHas('success', 'Jurnal untuk sesi ini sudah diisi. Data sebelumnya tetap tersimpan.');

    $this->assertDatabaseCount('jurnals', 1);
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

it('validates journal boundaries using Friday times', function () {
    $data = activeJournalSchedule();
    $start = JamPelajaran::create([
        'jam_ke' => 10,
        'jam_mulai' => '14:20:00',
        'jam_selesai' => '15:00:00',
        'jam_mulai_jumat' => '10:20:00',
        'jam_selesai_jumat' => '10:50:00',
        'is_active' => true,
    ]);
    $end = JamPelajaran::create([
        'jam_ke' => 11,
        'jam_mulai' => '13:00:00',
        'jam_selesai' => '14:00:00',
        'jam_mulai_jumat' => '10:50:00',
        'jam_selesai_jumat' => '11:20:00',
        'is_active' => true,
    ]);
    $startSchedule = Jadwal::create([
        'guru_id' => $data['guru']->id,
        'kelas_id' => $data['kelas']->id,
        'mapel_id' => $data['mapel']->id,
        'jam_pelajaran_id' => $start->id,
        'hari' => 'Jumat',
        'is_active' => true,
    ]);
    Jadwal::create([
        'guru_id' => $data['guru']->id,
        'kelas_id' => $data['kelas']->id,
        'mapel_id' => $data['mapel']->id,
        'jam_pelajaran_id' => $end->id,
        'hari' => 'Jumat',
        'is_active' => true,
    ]);
    $this->travelTo(Carbon::parse('2026-09-18 10:30:00', 'Asia/Jakarta'));

    $this->actingAs($data['user'])->post(route('jurnal.store'), [
        'jadwal_id' => $startSchedule->id,
        'status_guru' => 'Hadir',
    ])->assertRedirect();

    $this->assertDatabaseHas('jurnals', [
        'guru_id' => $data['guru']->id,
        'jam_mulai_id' => $start->id,
        'jam_selesai_id' => $end->id,
    ]);
    expect(Jurnal::firstOrFail()->tanggal->toDateString())->toBe('2026-09-18');
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
