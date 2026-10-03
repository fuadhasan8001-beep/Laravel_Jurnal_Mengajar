<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JadwalPiket;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->wali = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $this->teacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $this->secretary = User::factory()->create(['role' => 'sekretaris', 'is_active' => true]);
    $wali = Guru::create(['user_id' => $this->wali->id, 'nip' => 'WALI-1', 'nama_guru' => 'Wali Kelas', 'status_kepegawaian' => 'Honorer']);
    $guru = Guru::create(['user_id' => $this->teacher->id, 'nip' => 'GURU-1', 'nama_guru' => 'Badrus Sulaiman', 'status_kepegawaian' => 'Honorer']);
    $this->kelas = Kelas::create(['nama_kelas' => 'XI RPL 2', 'tingkat' => 'XI', 'wali_kelas_id' => $wali->id]);
    $this->secretary->kelasSekretaris()->attach($this->kelas);
    $mapel = Mapel::create(['kode_mapel' => 'RPL', 'nama_mapel' => 'Pemrograman']);
    $jam = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '08:00:00', 'is_active' => true]);
    $this->journal = Jurnal::create([
        'guru_id' => $guru->id, 'kelas_id' => $this->kelas->id, 'mapel_id' => $mapel->id,
        'jam_mulai_id' => $jam->id, 'jam_selesai_id' => $jam->id, 'tanggal' => today(),
        'status_guru' => 'Hadir', 'materi' => 'Materi algoritma', 'tugas' => 'Latihan fungsi', 'status_verifikasi' => 'Disetujui',
    ]);
    $this->journal->verifikasiJurnals()->create(['verifikator_id' => $this->secretary->id, 'status' => 'Disetujui', 'verified_at' => now()]);
});

it('shows the homeroom recap only to homeroom teachers and keeps it protected', function () {
    $this->actingAs($this->wali)->get('/guru')->assertOk()->assertSee('Rekap jurnal kelas');
    $this->actingAs($this->teacher)->get('/guru')->assertOk()->assertDontSee('Rekap jurnal');
    $this->get(route('wali-kelas.jurnal.index'))->assertForbidden();
    $this->get(route('wali-kelas.jurnal.show', $this->journal))->assertForbidden();
    $this->actingAs($this->secretary)->get(route('wali-kelas.jurnal.index'))->assertForbidden();
});

it('keeps own journals separate from the homeroom class recap', function () {
    $this->actingAs($this->teacher)->get(route('jurnal.index'))
        ->assertOk()->assertSee('Materi algoritma');
    $this->actingAs($this->wali)->get(route('jurnal.index'))
        ->assertOk()->assertDontSee('Materi algoritma');
    $this->get(route('wali-kelas.jurnal.index'))->assertOk()->assertSee('Materi algoritma');
});

it('filters by date and teacher and shows complete readonly journal detail', function () {
    $this->actingAs($this->wali)->get(route('wali-kelas.jurnal.index', ['guru' => 'Badrus']))
        ->assertOk()->assertSee('Materi algoritma')->assertSee('Tampilkan detail');
    $this->get(route('wali-kelas.jurnal.index', ['guru' => 'Tidak cocok']))->assertOk()->assertDontSee('Materi algoritma');
    $this->get(route('wali-kelas.jurnal.show', $this->journal))->assertOk()->assertSee('Latihan fungsi')->assertDontSee('>Edit<', false);
    $this->get(route('jurnal.edit', $this->journal))->assertForbidden();
    $this->journal->update(['tanggal' => today()->subDay()]);
    $this->get(route('wali-kelas.jurnal.index'))->assertOk()->assertSee('Materi algoritma');
    $this->get(route('wali-kelas.jurnal.index', ['tanggal' => today()->toDateString()]))->assertOk()->assertDontSee('Materi algoritma');
    $this->get(route('wali-kelas.jurnal.index', ['tanggal' => today()->subDay()->toDateString()]))->assertOk()->assertSee('Materi algoritma');
});

it('does not expose another class through filters detail or signature', function () {
    $other = Kelas::create(['nama_kelas' => 'XI RPL 1', 'tingkat' => 'XI']);
    $this->journal->update(['kelas_id' => $other->id]);
    $this->actingAs($this->wali)->get(route('wali-kelas.jurnal.index', ['kelas_id' => $other->id]))->assertOk()->assertDontSee('Materi algoritma');
    $this->get(route('wali-kelas.jurnal.show', $this->journal))->assertNotFound();
    $this->get(route('wali-kelas.jurnal.signature', $this->journal))->assertNotFound();
});

it('excludes journals without secretary approval', function (string $status, bool $secretaryApproval) {
    $this->journal->update(['status_verifikasi' => $status]);
    if (! $secretaryApproval) {
        $this->journal->verifikasiJurnals()->delete();
    }
    $this->actingAs($this->wali)->get(route('wali-kelas.jurnal.index'))->assertOk()->assertDontSee('Materi algoritma');
    $this->get(route('wali-kelas.jurnal.show', $this->journal))->assertNotFound();
})->with([['Menunggu', true], ['Ditolak', true], ['Disetujui', false]]);

it('validates date filters', function () {
    $this->actingAs($this->wali)->get(route('wali-kelas.jurnal.index', ['tanggal' => 'bukan-tanggal']))->assertSessionHasErrors('tanggal');
});

it('blocks the legacy journal report and export for teachers', function () {
    foreach ([$this->teacher, $this->wali] as $user) {
        $this->actingAs($user)->get(route('laporan.jurnal'))->assertForbidden();
        $this->get(route('laporan.jurnal.export'))->assertForbidden();
        $this->get('/guru')->assertOk()->assertDontSee('Rekap laporan');
        $this->get(route('jurnal.index'))->assertOk();
    }
});

it('keeps the duty report restricted to teachers assigned today', function () {
    $this->travelTo(today()->setTime(8, 0));
    $this->actingAs($this->teacher)->get(route('piket.rekap-jurnal'))->assertForbidden();
    $this->get(route('piket.rekap-jurnal.export'))->assertForbidden();
    JadwalPiket::create(['guru_id' => $this->journal->guru_id, 'tanggal' => today(), 'shift' => 'pagi']);
    $this->get(route('piket.rekap-jurnal'))->assertOk()
        ->assertSee('action="'.route('piket.rekap-jurnal').'"', false)
        ->assertDontSee('action="'.route('laporan.jurnal').'"', false);
    $this->get(route('piket.rekap-jurnal.export'))->assertDownload('rekap-jurnal.csv');
});

it('allows the dedicated duty role to view journal recaps but not attendance reports', function () {
    $piket = User::factory()->create(['role' => 'piket', 'is_active' => true]);

    $this->actingAs($piket)->get(route('piket.rekap-jurnal', ['kelas_id' => $this->journal->kelas_id, 'tanggal_mulai' => $this->journal->tanggal->toDateString()]))
        ->assertOk()->assertSee('Tampilkan detail')->assertSee('Materi algoritma');
    $this->get(route('laporan.absensi'))->assertForbidden();
    $this->get(route('laporan.absensi.export'))->assertForbidden();
});

it('shows only distinct teacher journal destinations', function () {
    $this->actingAs($this->wali)->get('/guru')
        ->assertOk()
        ->assertSee('Jurnal saya')
        ->assertSee('Rekap jurnal kelas')
        ->assertDontSee('>Rekap jurnal<', false)
        ->assertSee('href="'.route('jurnal.index').'"', false)
        ->assertSee('href="'.route('wali-kelas.jurnal.index').'"', false);
});

it('keeps attendance updates out of the teacher dashboard quick actions', function () {
    Jadwal::create([
        'guru_id' => $this->journal->guru_id,
        'kelas_id' => $this->journal->kelas_id,
        'mapel_id' => $this->journal->mapel_id,
        'jam_pelajaran_id' => $this->journal->jam_mulai_id,
        'hari' => today()->locale('id')->translatedFormat('l'),
        'is_active' => true,
    ]);

    $this->actingAs($this->teacher)->get('/guru')
        ->assertOk()
        ->assertSee('Isi jurnal')
        ->assertSee('Perbarui absensi')
        ->assertDontSee('Kelola absensi')
        ->assertDontSee('schedule-action-attendance')
        ->assertDontSee('class="quick-card" href="'.route('absensi.index').'"', false);
});
