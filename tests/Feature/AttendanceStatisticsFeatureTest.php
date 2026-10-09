<?php

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('drills recorded attendance from school to program and class for one shared period', function () {
    $this->travelTo('2026-10-15 12:00:00');
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $firstTeacherUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $secondTeacherUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $teacherWithoutJournalsUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $firstTeacher = Guru::create(['user_id' => $firstTeacherUser->id, 'nip' => 'STATS-1', 'nama_guru' => 'Guru Satu', 'status_kepegawaian' => 'Honorer']);
    $secondTeacher = Guru::create(['user_id' => $secondTeacherUser->id, 'nip' => 'STATS-2', 'nama_guru' => 'Guru Dua', 'status_kepegawaian' => 'Honorer']);
    Guru::create(['user_id' => $teacherWithoutJournalsUser->id, 'nip' => 'STATS-3', 'nama_guru' => 'Guru Tanpa Jurnal', 'status_kepegawaian' => 'Honorer']);
    $secretary = User::factory()->create(['role' => 'sekretaris', 'is_active' => true]);
    $rplClass = Kelas::create(['nama_kelas' => 'X RPL 1', 'tingkat' => 'X']);
    $secondRplClass = Kelas::create(['nama_kelas' => 'X RPL 2', 'tingkat' => 'X']);
    $tkjClass = Kelas::create(['nama_kelas' => 'X TKJ 1', 'tingkat' => 'X']);
    $subject = Mapel::create(['kode_mapel' => 'STAT', 'nama_mapel' => 'Statistik']);
    $period = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:40:00', 'is_active' => true]);

    $createStudent = function (string $name, string $nis, Kelas $class): Siswa {
        $user = User::factory()->create(['role' => 'siswa', 'is_active' => true]);

        return Siswa::create([
            'user_id' => $user->id,
            'kelas_id' => $class->id,
            'nis' => $nis,
            'nama_siswa' => $name,
            'jenis_kelamin' => 'L',
        ]);
    };
    $createJournal = function (Guru $teacher, Kelas $class, string $date, string $teacherStatus, string $verificationStatus = 'Menunggu') use ($subject, $period): Jurnal {
        return Jurnal::create([
            'guru_id' => $teacher->id,
            'kelas_id' => $class->id,
            'mapel_id' => $subject->id,
            'jam_mulai_id' => $period->id,
            'jam_selesai_id' => $period->id,
            'tanggal' => $date,
            'status_guru' => $teacherStatus,
            'materi' => 'Materi tercatat',
            'status_verifikasi' => $verificationStatus,
        ]);
    };
    $record = fn (Jurnal $journal, Siswa $student, string $status) => Absensi::create([
        'jurnal_id' => $journal->id,
        'siswa_id' => $student->id,
        'status' => $status,
    ]);

    $firstStudent = $createStudent('Siswa Alfa', 'STAT-1', $rplClass);
    $secondStudent = $createStudent('Siswa Beta', 'STAT-2', $rplClass);
    $dispensedStudent = $createStudent('Siswa Dispensasi', 'STAT-3', $rplClass);
    $secondClassStudent = $createStudent('Siswa RPL Dua', 'STAT-4', $secondRplClass);
    $tkjStudent = $createStudent('Siswa TKJ', 'STAT-5', $tkjClass);

    $pendingJournal = $createJournal($firstTeacher, $rplClass, '2026-10-05', 'Hadir');
    $record($pendingJournal, $firstStudent, 'H');
    $record($pendingJournal, $secondStudent, 'S');
    $record($pendingJournal, $dispensedStudent, 'D');

    $approvedJournal = $createJournal($firstTeacher, $rplClass, '2026-10-06', 'Izin', 'Disetujui');
    $approvedJournal->verifikasiJurnals()->create([
        'verifikator_id' => $secretary->id,
        'status' => 'Disetujui',
        'verified_at' => now(),
    ]);
    $record($approvedJournal, $firstStudent, 'A');
    $record($approvedJournal, $secondStudent, 'I');

    $secondRplJournal = $createJournal($secondTeacher, $secondRplClass, '2026-10-07', 'Hadir');
    $record($secondRplJournal, $secondClassStudent, 'H');
    $tkjJournal = $createJournal($secondTeacher, $tkjClass, '2026-10-08', 'Sakit');
    $record($tkjJournal, $tkjStudent, 'H');
    $outsidePeriodJournal = $createJournal($firstTeacher, $rplClass, '2026-09-30', 'Sakit');
    $record($outsidePeriodJournal, $firstStudent, 'S');

    $response = $this->actingAs($admin)->get(route('laporan.absensi', [
        'tanggal_mulai' => '2026-10-01',
        'tanggal_selesai' => '2026-10-31',
    ]));

    $response->assertOk()
        ->assertSee('Siswa Alfa')
        ->assertSee('Guru Satu')
        ->assertSee('Verifikasi jurnal')
        ->assertSee('Menunggu')
        ->assertSee('Disetujui')
        ->assertSee('tanggal_mulai=2026-10-01', false)
        ->assertSee('tanggal_selesai=2026-10-31', false)
        ->assertSee('kelas_id='.$rplClass->id, false);
    expect($response->viewData('studentSummary')['Hadir'])->toBe(3)
        ->and($response->viewData('studentSummary')['Sakit'])->toBe(1)
        ->and($response->viewData('studentSummary')['Izin'])->toBe(1)
        ->and($response->viewData('studentSummary')['Alpa'])->toBe(1)
        ->and($response->viewData('studentSummary')['Dispensasi'])->toBe(1)
        ->and($response->viewData('studentAttendanceRate'))->toBe(50.0)
        ->and($response->viewData('teacherSummary')['Hadir'])->toBe(2)
        ->and($response->viewData('teacherSummary')['Izin'])->toBe(1)
        ->and($response->viewData('teacherSummary')['Sakit'])->toBe(1)
        ->and($response->viewData('absensis')->total())->toBe(7)
        ->and((int) $response->viewData('studentAbsenceLeaders')->first()->absence_count)->toBe(2)
        ->and($response->viewData('studentAbsenceLeaders')->first()->nama_siswa)->toBe('Siswa Beta')
        ->and($response->viewData('teacherAbsenceLeaders')->count())->toBe(2)
        ->and($response->viewData('teacherAbsenceLeaders')->pluck('nama_guru'))->not->toContain('Guru Tanpa Jurnal');

    $groups = $response->viewData('attendanceGroups');
    $rplClassStats = $groups->get('RPL')['classes']->first(fn (array $classStat): bool => $classStat['kelas']->is($rplClass));
    expect($groups->keys()->all())->toBe(['RPL', 'TKJ'])
        ->and($groups->get('RPL')['siswa']['Hadir'])->toBe(2)
        ->and($groups->get('RPL')['siswa_hadir_persen'])->toBe(40.0)
        ->and($rplClassStats['siswa_hadir_persen'])->toBe(25.0);

    $programResponse = $this->get(route('laporan.absensi', [
        'tanggal_mulai' => '2026-10-01',
        'tanggal_selesai' => '2026-10-31',
        'program' => 'RPL',
    ]));
    expect($programResponse->viewData('absensis')->total())->toBe(6)
        ->and($programResponse->viewData('studentSummary')['Hadir'])->toBe(2)
        ->and($programResponse->viewData('studentSummary')['Dispensasi'])->toBe(1);

    $classResponse = $this->get(route('laporan.absensi', [
        'tanggal_mulai' => '2026-10-01',
        'tanggal_selesai' => '2026-10-31',
        'program' => 'RPL',
        'kelas_id' => $rplClass->id,
    ]));
    expect($classResponse->viewData('absensis')->total())->toBe(5)
        ->and($classResponse->viewData('studentSummary')['Hadir'])->toBe(1)
        ->and($classResponse->viewData('selectedClass')->is($rplClass))->toBeTrue();

    $emptyResponse = $this->get(route('laporan.absensi', [
        'tanggal_mulai' => '2026-11-01',
        'tanggal_selesai' => '2026-11-30',
    ]));
    $emptyResponse->assertOk()->assertSee('Belum ada catatan kehadiran pada periode ini.');
    expect($emptyResponse->viewData('hasAttendanceData'))->toBeFalse()
        ->and($emptyResponse->viewData('studentSummary'))->toBe([])
        ->and($emptyResponse->viewData('teacherAbsenceLeaders'))->toBeEmpty();
});

it('limits teacher and homeroom statistics to permitted recorded journals', function () {
    $this->travelTo('2026-10-15 12:00:00');
    $homeroomUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $recordingTeacherUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $otherTeacherUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $secretary = User::factory()->create(['role' => 'sekretaris', 'is_active' => true]);
    $homeroomGuru = Guru::create(['user_id' => $homeroomUser->id, 'nip' => 'WALI-STATS', 'nama_guru' => 'Wali Statistik', 'status_kepegawaian' => 'Honorer']);
    $recordingTeacher = Guru::create(['user_id' => $recordingTeacherUser->id, 'nip' => 'GURU-STATS', 'nama_guru' => 'Guru Pemilik', 'status_kepegawaian' => 'Honorer']);
    $otherTeacher = Guru::create(['user_id' => $otherTeacherUser->id, 'nip' => 'GURU-OTHER', 'nama_guru' => 'Guru Lain', 'status_kepegawaian' => 'Honorer']);
    $homeroomClass = Kelas::create(['nama_kelas' => 'XI RPL 1', 'tingkat' => 'XI', 'wali_kelas_id' => $homeroomGuru->id]);
    $otherClass = Kelas::create(['nama_kelas' => 'XI TKJ 1', 'tingkat' => 'XI']);
    $subject = Mapel::create(['kode_mapel' => 'WST', 'nama_mapel' => 'Wali Statistik']);
    $period = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:40:00', 'is_active' => true]);
    $homeroomStudentUser = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
    $homeroomStudent = Siswa::create(['user_id' => $homeroomStudentUser->id, 'kelas_id' => $homeroomClass->id, 'nis' => 'WST-1', 'nama_siswa' => 'Siswa Wali', 'jenis_kelamin' => 'P']);
    $pendingStudentUser = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
    $pendingStudent = Siswa::create(['user_id' => $pendingStudentUser->id, 'kelas_id' => $homeroomClass->id, 'nis' => 'WST-2', 'nama_siswa' => 'Siswa Belum Disetujui', 'jenis_kelamin' => 'L']);
    $otherStudentUser = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
    $otherStudent = Siswa::create(['user_id' => $otherStudentUser->id, 'kelas_id' => $otherClass->id, 'nis' => 'WST-3', 'nama_siswa' => 'Siswa Kelas Lain', 'jenis_kelamin' => 'L']);

    $approvedJournal = Jurnal::create([
        'guru_id' => $recordingTeacher->id, 'kelas_id' => $homeroomClass->id, 'mapel_id' => $subject->id,
        'jam_mulai_id' => $period->id, 'jam_selesai_id' => $period->id, 'tanggal' => '2026-10-10',
        'status_guru' => 'Hadir', 'materi' => 'Disetujui', 'status_verifikasi' => 'Disetujui',
    ]);
    $approvedJournal->verifikasiJurnals()->create(['verifikator_id' => $secretary->id, 'status' => 'Disetujui', 'verified_at' => now()]);
    Absensi::create(['jurnal_id' => $approvedJournal->id, 'siswa_id' => $homeroomStudent->id, 'status' => 'S']);
    $pendingJournal = Jurnal::create([
        'guru_id' => $recordingTeacher->id, 'kelas_id' => $homeroomClass->id, 'mapel_id' => $subject->id,
        'jam_mulai_id' => $period->id, 'jam_selesai_id' => $period->id, 'tanggal' => '2026-10-11',
        'status_guru' => 'Sakit', 'materi' => 'Menunggu', 'status_verifikasi' => 'Menunggu',
    ]);
    Absensi::create(['jurnal_id' => $pendingJournal->id, 'siswa_id' => $pendingStudent->id, 'status' => 'A']);
    $otherJournal = Jurnal::create([
        'guru_id' => $otherTeacher->id, 'kelas_id' => $otherClass->id, 'mapel_id' => $subject->id,
        'jam_mulai_id' => $period->id, 'jam_selesai_id' => $period->id, 'tanggal' => '2026-10-12',
        'status_guru' => 'Sakit', 'materi' => 'Kelas lain', 'status_verifikasi' => 'Disetujui',
    ]);
    $otherJournal->verifikasiJurnals()->create(['verifikator_id' => $secretary->id, 'status' => 'Disetujui', 'verified_at' => now()]);
    Absensi::create(['jurnal_id' => $otherJournal->id, 'siswa_id' => $otherStudent->id, 'status' => 'S']);

    $response = $this->actingAs($homeroomUser)->get(route('laporan.absensi', [
        'tanggal_mulai' => '2026-10-01',
        'tanggal_selesai' => '2026-10-31',
    ]));
    $response->assertOk()->assertSee('Siswa Wali')->assertDontSee('Siswa Belum Disetujui')->assertDontSee('Siswa Kelas Lain');
    expect($response->viewData('absensis')->total())->toBe(1)
        ->and($response->viewData('studentSummary')['Sakit'])->toBe(1)
        ->and($response->viewData('teacherSummary')['Hadir'])->toBe(1)
        ->and($response->viewData('teacherSummary'))->not->toHaveKey('Sakit')
        ->and($response->viewData('statisticsScopeLabel'))->toBe('Statistik kelas yang diampu dan kelas wali');

    $otherClassResponse = $this->get(route('laporan.absensi', ['kelas_id' => $otherClass->id]));
    expect($otherClassResponse->viewData('absensis')->total())->toBe(0);

    $teacherResponse = $this->actingAs($recordingTeacherUser)->get(route('laporan.absensi', [
        'tanggal_mulai' => '2026-10-01',
        'tanggal_selesai' => '2026-10-31',
    ]));
    expect($teacherResponse->viewData('absensis')->total())->toBe(2)
        ->and($teacherResponse->viewData('studentSummary')['Sakit'])->toBe(1)
        ->and($teacherResponse->viewData('studentSummary')['Alpa'])->toBe(1);
});

it('shows statistics in admin and homeroom navigation but denies unrelated roles', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $waka = User::factory()->create(['role' => 'waka', 'is_active' => true]);
    $student = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
    $piket = User::factory()->create(['role' => 'piket', 'is_active' => true]);
    $homeroomUser = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $homeroomGuru = Guru::create(['user_id' => $homeroomUser->id, 'nip' => 'WALI-NAV', 'nama_guru' => 'Wali Menu', 'status_kepegawaian' => 'Honorer']);
    Kelas::create(['nama_kelas' => 'X RPL 1', 'tingkat' => 'X', 'wali_kelas_id' => $homeroomGuru->id]);

    $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Statistik kehadiran')->assertSee(route('laporan.absensi'), false);
    $this->actingAs($waka)->get(route('laporan.absensi'))->assertOk()->assertViewHas('statisticsScopeLabel', 'Statistik sekolah');
    $this->actingAs($homeroomUser)->get('/guru')->assertOk()->assertSee('Statistik kehadiran kelas');
    $this->actingAs($student)->get(route('laporan.absensi'))->assertForbidden();
    $this->actingAs($piket)->get(route('laporan.absensi'))->assertForbidden();
});

it('validates period dates before applying a partial-month default', function () {
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

    $this->actingAs($admin)->get(route('laporan.absensi', ['tanggal_selesai' => 'bukan-tanggal']))
        ->assertSessionHasErrors('tanggal_selesai');
});
