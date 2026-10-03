<?php

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function classReportFixture(): array
{
    $classes = collect(['XI RPL 2', 'XI RPL 3'])->map(fn ($name) => Kelas::create(['nama_kelas' => $name, 'tingkat' => 'XI']));
    $teachers = collect(['Guru Pertama', 'Guru Kedua'])->map(fn ($name, $index) => Guru::create([
        'user_id' => User::factory()->create(['role' => 'guru'])->id, 'nip' => 'REPORT-'.$index, 'nama_guru' => $name, 'status_kepegawaian' => 'Honorer',
    ]));
    $mapel = Mapel::create(['kode_mapel' => 'RPL', 'nama_mapel' => 'Konsentrasi RPL']);
    $periods = collect([
        ['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '08:00:00'],
        ['jam_ke' => 2, 'jam_mulai' => '08:00:00', 'jam_selesai' => '09:00:00'],
        ['jam_ke' => 3, 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:00:00'],
    ])->map(fn ($data) => JamPelajaran::create([...$data, 'is_active' => true]));
    foreach ($classes as $kelas) {
        foreach ($periods as $index => $period) {
            Jadwal::create(['guru_id' => $teachers[$index === 0 ? 0 : 1]->id, 'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id, 'jam_pelajaran_id' => $period->id, 'hari' => 'Rabu', 'is_active' => true]);
        }
    }

    return [$classes, $teachers, $mapel, $periods];
}

it('shows all teachers for one class in lesson order and distinguishes missing and upcoming journals', function () {
    $this->travelTo(Carbon::parse('2026-09-30 08:30:00'));
    [$classes, $teachers, $mapel, $periods] = classReportFixture();
    $piket = User::factory()->create(['role' => 'piket', 'is_active' => true]);
    $journal = Jurnal::create(['guru_id' => $teachers[0]->id, 'kelas_id' => $classes[0]->id,
        'mapel_id' => $mapel->id, 'jam_mulai_id' => $periods[0]->id, 'jam_selesai_id' => $periods[0]->id,
        'tanggal' => today(), 'status_guru' => 'Hadir', 'status_verifikasi' => 'Disetujui', 'materi' => 'Materi kelas dua']);
    Jurnal::create(['guru_id' => $teachers[0]->id, 'kelas_id' => $classes[1]->id,
        'mapel_id' => $mapel->id, 'jam_mulai_id' => $periods[0]->id, 'jam_selesai_id' => $periods[0]->id,
        'tanggal' => today(), 'status_guru' => 'Hadir', 'materi' => 'RAHASIA KELAS TIGA']);

    $url = route('piket.rekap-jurnal', ['kelas_id' => $classes[0]->id, 'guru_id' => $teachers[0]->id]);
    $this->actingAs($piket)->get($url)->assertOk()->assertSee('Materi kelas dua')->assertDontSee('RAHASIA KELAS TIGA')
        ->assertSee('Tampilkan detail')->assertDontSee('name="guru_id"', false)
        ->assertViewHas('rows', fn ($rows) => collect($rows->items())->pluck('status')->all() === ['Sudah Diisi', 'Belum Diisi', 'Belum waktunya'])
        ->assertViewHas('rows', fn ($rows) => collect($rows->items())->pluck('guru')->all() === ['Guru Pertama', 'Guru Kedua', 'Guru Kedua']);
    $csv = $this->get(route('piket.rekap-jurnal.export', ['kelas_id' => $classes[0]->id]))->assertOk()->streamedContent();
    expect($csv)->toContain('Materi kelas dua', 'Belum Diisi', 'Belum waktunya')->not->toContain('RAHASIA KELAS TIGA');
    $this->get(route('piket.rekap-jurnal', ['kelas_id' => $classes[1]->id]))->assertOk()
        ->assertSee('RAHASIA KELAS TIGA')->assertDontSee('Materi kelas dua');
    $journal->update(['materi' => '<script>alert(1)</script>']);
    $this->get($url)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
});

it('marks a fully covered multi-period class as filled and keeps history after schedule changes', function () {
    $this->travelTo(Carbon::parse('2026-09-30 10:30:00'));
    [$classes, $teachers, $mapel, $periods] = classReportFixture();
    foreach ([[0, 0, 0], [1, 1, 2]] as [$teacher, $first, $last]) {
        Jurnal::create(['guru_id' => $teachers[$teacher]->id, 'kelas_id' => $classes[0]->id,
            'mapel_id' => $mapel->id, 'jam_mulai_id' => $periods[$first]->id, 'jam_selesai_id' => $periods[$last]->id,
            'tanggal' => today(), 'status_guru' => 'Hadir', 'materi' => 'Jurnal lengkap']);
    }
    $this->actingAs(User::factory()->create(['role' => 'piket', 'is_active' => true]));
    $url = route('piket.rekap-jurnal', ['kelas_id' => $classes[0]->id]);
    $this->get($url)->assertOk()->assertViewHas('rows', fn ($rows) => collect($rows->items())->pluck('status')->all() === ['Sudah Diisi', 'Sudah Diisi', 'Sudah Diisi']);
    Jadwal::where('kelas_id', $classes[0]->id)->update(['is_active' => false]);
    $this->get($url)->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 2)
        ->assertSee('Jurnal lengkap')->assertDontSee('Belum Diisi');
});

it('does not infer missing journals on unscheduled dates or claim historical noncompliance', function () {
    $this->travelTo(Carbon::parse('2026-09-30 10:30:00'));
    [$classes] = classReportFixture();
    $this->actingAs(User::factory()->create(['role' => 'piket', 'is_active' => true]));
    $this->get(route('piket.rekap-jurnal', ['kelas_id' => $classes[0]->id, 'tanggal_mulai' => '2026-09-29']))
        ->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 0)->assertDontSee('Belum Diisi');
    $this->get(route('piket.rekap-jurnal', ['kelas_id' => $classes[0]->id, 'tanggal_mulai' => '2026-09-23']))
        ->assertOk()->assertSee('Tidak ada jurnal tercatat')->assertDontSee('Belum Diisi');
});

it('validates date ranges and classes on both report and export', function (array $filters, string $field) {
    $this->travelTo(Carbon::parse('2026-09-30 08:00:00'));
    $this->actingAs(User::factory()->create(['role' => 'piket', 'is_active' => true]));
    foreach (['piket.rekap-jurnal', 'piket.rekap-jurnal.export'] as $route) {
        $this->from(route('piket.rekap-jurnal'))->get(route($route, $filters))->assertRedirect()->assertSessionHasErrors($field);
    }
})->with([
    'invalid class' => [['kelas_id' => 9999], 'kelas_id'],
    'invalid date' => [['tanggal_mulai' => 'bad'], 'tanggal_mulai'],
    'backward dates' => [['tanggal_mulai' => '2026-09-30', 'tanggal_selesai' => '2026-09-01'], 'tanggal_selesai'],
    'unbounded period' => [['tanggal_mulai' => '2026-01-01', 'tanggal_selesai' => '2026-09-30'], 'tanggal_selesai'],
]);

it('denies journal recap to guests and teachers without a duty assignment', function () {
    $this->get(route('piket.rekap-jurnal'))->assertRedirect('/login');
    $this->actingAs(User::factory()->create(['role' => 'guru', 'is_active' => true]));
    $this->get(route('piket.rekap-jurnal'))->assertForbidden();
    $this->get(route('piket.rekap-jurnal.export'))->assertForbidden();
});

it('orders the selected period chronologically and exports the same class without spreadsheet formulas', function () {
    $this->travelTo(Carbon::parse('2026-09-30 10:30:00'));
    [$classes, $teachers, $mapel, $periods] = classReportFixture();
    foreach (['2026-09-30', '2026-09-23'] as $date) {
        Jurnal::create(['guru_id' => $teachers[0]->id, 'kelas_id' => $classes[0]->id,
            'mapel_id' => $mapel->id, 'jam_mulai_id' => $periods[0]->id, 'jam_selesai_id' => $periods[0]->id,
            'tanggal' => $date, 'status_guru' => 'Hadir', 'materi' => '=1+1']);
    }
    $filters = ['kelas_id' => $classes[0]->id, 'tanggal_mulai' => '2026-09-23', 'tanggal_selesai' => '2026-09-30'];
    $this->actingAs(User::factory()->create(['role' => 'piket', 'is_active' => true]))
        ->get(route('piket.rekap-jurnal', $filters))->assertOk()
        ->assertViewHas('rows', fn ($rows) => collect($rows->items())->map(fn ($row) => $row['tanggal']->toDateString().' '.$row['jam_ke'])->all() === [
            '2026-09-23 1', '2026-09-23 2', '2026-09-23 3', '2026-09-30 1', '2026-09-30 2', '2026-09-30 3',
        ]);
    expect($this->get(route('piket.rekap-jurnal.export', $filters))->streamedContent())->toContain("'=1+1");
});
