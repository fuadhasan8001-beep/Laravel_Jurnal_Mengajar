<?php

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function navigationLesson(): array
{
    $teacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $teacher->id, 'nip' => 'NAV-1', 'nama_guru' => 'Guru Uji', 'status_kepegawaian' => 'Honorer']);
    $kelas = Kelas::create(['nama_kelas' => 'X UJI', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'NAV', 'nama_mapel' => 'Mapel Uji']);
    $period = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:40', 'is_active' => true]);
    $journal = Jurnal::create(['guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
        'jam_mulai_id' => $period->id, 'jam_selesai_id' => $period->id, 'tanggal' => '2026-09-15', 'materi' => 'Materi guru', 'status_guru' => 'Hadir']);

    return compact('teacher', 'guru', 'kelas', 'mapel', 'period', 'journal');
}

it('opens the dashboard belonging to the logged in role', function (string $role) {
    $user = User::factory()->create(['role' => $role, 'is_active' => true]);
    $this->actingAs($user)->get('/')->assertRedirect('/'.$role);
})->with(['admin', 'guru', 'piket', 'siswa', 'sekretaris']);

it('does not redirect an inactive account into a dashboard', function () {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => false]);
    $this->actingAs($user)->get('/')->assertForbidden();
});

it('opens a notification target without approving or removing its pending request', function () {
    $data = navigationLesson();
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $student = Siswa::create(['user_id' => User::factory()->create(['role' => 'siswa'])->id, 'kelas_id' => $data['kelas']->id,
        'nis' => 'NAV-STUDENT', 'nama_siswa' => 'Siswa Uji', 'jenis_kelamin' => 'L']);
    $request = Dispensasi::create(['siswa_id' => $student->id, 'tanggal' => '2026-09-15', 'jam_mulai_id' => $data['period']->id,
        'jam_selesai_id' => $data['period']->id, 'alasan' => 'Lomba', 'status_piket' => 'Disetujui']);
    $notification = $admin->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test',
        'data' => ['message' => 'Pengajuan uji menunggu admin', 'url' => route('dispensasi.show', $request)]]);

    $this->actingAs($admin)->post(route('notifications.read', $notification->id))->assertRedirect('/dispensasi/'.$request->id);

    expect($notification->fresh()->read_at)->not->toBeNull();
    $this->assertDatabaseHas('dispensasis', ['id' => $request->id, 'status_akhir' => 'Menunggu']);
    $this->get('/admin')->assertSee('Pengajuan uji menunggu admin')->assertSee('Sudah dibaca')->assertSee('Notifikasi (0)');
});

it('cannot read another users notification', function () {
    $owner = User::factory()->create(['role' => 'admin']);
    $other = User::factory()->create(['role' => 'admin']);
    $notification = $owner->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test', 'data' => ['url' => '/admin']]);

    $this->actingAs($other)->post(route('notifications.read', $notification->id))->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();
});

it('falls back to home for missing or external notification targets', function (?string $url) {
    $user = User::factory()->create(['role' => 'admin']);
    $notification = $user->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test', 'data' => ['url' => $url]]);

    $this->actingAs($user)->post(route('notifications.read', $notification->id))->assertRedirect('/');
})->with([null, 'https://example.org/steal', '//example.org/steal', 'javascript:alert(1)']);

it('marks all notifications read while keeping them in the menu', function () {
    $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $user->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test', 'data' => ['message' => 'Riwayat tetap ada', 'url' => '/admin']]);

    $this->actingAs($user)->from('/admin')->post(route('notifications.read-all'))->assertRedirect('/admin');

    $this->get('/admin')->assertSee('Riwayat tetap ada')->assertSee('Sudah dibaca')->assertSee('Notifikasi (0)');
});

it('lets admin view but not create edit update or delete a teachers journal', function () {
    $data = navigationLesson();
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)->get(route('jurnal.show', $data['journal']))->assertOk();
    $this->get(route('jurnal.create'))->assertForbidden();
    $this->get(route('jurnal.edit', $data['journal']))->assertForbidden();
    $this->post(route('jurnal.store'), ['materi' => 'Perubahan admin'])->assertForbidden();
    $this->put(route('jurnal.update', $data['journal']), ['materi' => 'Perubahan admin'])->assertForbidden();
    $this->delete(route('jurnal.destroy', $data['journal']))->assertForbidden();

    $this->assertDatabaseHas('jurnals', ['id' => $data['journal']->id, 'materi' => 'Materi guru']);
});

it('counts only students in the teachers active scheduled classes without duplicates', function () {
    $data = navigationLesson();
    $otherClass = Kelas::create(['nama_kelas' => 'X LAIN', 'tingkat' => 'X']);
    foreach ([$data['kelas'], $otherClass] as $index => $kelas) {
        Siswa::create(['user_id' => User::factory()->create(['role' => 'siswa'])->id, 'kelas_id' => $kelas->id,
            'nis' => 'NAV-'.$index, 'nama_siswa' => 'Siswa '.$index, 'jenis_kelamin' => 'P']);
    }
    foreach (['Senin', 'Selasa'] as $hari) {
        Jadwal::create(['guru_id' => $data['guru']->id, 'kelas_id' => $data['kelas']->id, 'mapel_id' => $data['mapel']->id,
            'jam_pelajaran_id' => $data['period']->id, 'hari' => $hari, 'is_active' => true]);
    }
    Jadwal::create(['guru_id' => $data['guru']->id, 'kelas_id' => $otherClass->id, 'mapel_id' => $data['mapel']->id,
        'jam_pelajaran_id' => $data['period']->id, 'hari' => 'Rabu', 'is_active' => false]);

    $this->actingAs($data['teacher'])->get('/guru')->assertViewHas('siswaTerpantau', 1);
});

it('shows zero monitored students when a teacher has no profile', function () {
    $user = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $this->actingAs($user)->get('/guru')->assertViewHas('siswaTerpantau', 0);
});
