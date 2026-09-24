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
use App\Notifications\DispensasiApprovalMail;
use App\Notifications\DispensasiNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

it('always dates a new dispensation today regardless of submitted date', function (mixed $submittedDate) {
    $data = dispensasiSetup();
    $this->travelTo(Carbon::parse('2026-09-15 00:05:00', 'Asia/Jakarta'));
    Notification::fake();

    $this->actingAs($data['piket'])->post(route('dispensasi.store'), [
        ...$data['payload'], 'tanggal' => $submittedDate,
        'siswa_ids' => $data['students']->pluck('id')->all(),
    ])->assertSessionHasNoErrors()->assertRedirect(route('dispensasi.index'));

    expect(Dispensasi::all()->map(fn ($item) => $item->tanggal->toDateString())->unique()->all())->toBe(['2026-09-15']);
    $this->assertDatabaseCount('dispensasis', 2);
})->with(['yesterday' => '2026-09-14', 'future' => '2026-10-01', 'invalid' => 'tanggal-bebas', 'empty' => null]);

it('shows todays date as readonly even after validation fails with old input', function () {
    $data = dispensasiSetup();
    $this->travelTo(Carbon::parse('2026-09-15 07:00:00', 'Asia/Jakarta'));

    $response = $this->actingAs($data['piket'])->withSession(['_old_input' => ['tanggal' => '2026-09-14']])
        ->get(route('dispensasi.create'));

    $response->assertOk();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $field = $document->getElementById('tanggal');
    expect($field->getAttribute('value'))->toBe('2026-09-15');
    expect($field->hasAttribute('readonly'))->toBeTrue();
});

function dispensasiSetup(): array
{
    test()->travelTo(Carbon::parse('2026-09-14 07:15:00', 'Asia/Jakarta'));
    $teacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $guru = Guru::create(['user_id' => $teacher->id, 'nip' => '12345', 'nama_guru' => 'Guru Uji', 'status_kepegawaian' => 'Honorer']);
    $piket = User::factory()->create(['role' => 'piket', 'is_active' => true]);
    $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    $kelas = Kelas::create(['nama_kelas' => 'X RPL', 'tingkat' => 'X']);
    $mapel = Mapel::create(['kode_mapel' => 'MAT', 'nama_mapel' => 'Matematika']);
    $start = JamPelajaran::create(['jam_ke' => 1, 'jam_mulai' => '07:00:00', 'jam_selesai' => '07:45:00', 'is_active' => true]);
    $end = JamPelajaran::create(['jam_ke' => 2, 'jam_mulai' => '07:45:00', 'jam_selesai' => '08:30:00', 'is_active' => true]);
    $students = collect([1, 2])->map(fn ($number) => Siswa::create([
        'user_id' => User::factory()->create(['role' => 'siswa', 'is_active' => true])->id,
        'kelas_id' => $kelas->id, 'nis' => '100'.$number, 'nama_siswa' => 'Siswa '.$number, 'jenis_kelamin' => 'L',
    ]));
    $schedule = Jadwal::create(['guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
        'jam_pelajaran_id' => $start->id, 'hari' => now()->locale('id')->translatedFormat('l'), 'is_active' => true]);
    $payload = ['tanggal' => today()->toDateString(), 'jam_mulai_id' => $start->id, 'jam_selesai_id' => $start->id, 'alasan' => 'Lomba sekolah'];
    $journal = ['guru_id' => $guru->id, 'kelas_id' => $kelas->id, 'mapel_id' => $mapel->id,
        'tanggal' => today()->toDateString(), 'jam_mulai_id' => $start->id, 'jam_selesai_id' => $start->id, 'status_guru' => 'Hadir', 'materi' => 'Aljabar'];

    return compact('teacher', 'guru', 'piket', 'admin', 'kelas', 'mapel', 'start', 'end', 'students', 'schedule', 'payload', 'journal');
}

it('lets piket submit multiple students while leaving attendance pending admin approval', function () {
    $data = dispensasiSetup();
    $this->travelTo(Carbon::parse('2026-09-14 06:00:00', 'Asia/Jakarta'));
    Notification::fake();

    $this->actingAs($data['piket'])->post(route('dispensasi.store'), [...$data['payload'], 'siswa_ids' => $data['students']->pluck('id')->all()])->assertRedirect(route('dispensasi.index'));

    $this->assertDatabaseCount('dispensasis', 2);
    $this->assertDatabaseCount('absensis', 0);
    foreach ($data['students'] as $student) {
        $this->assertDatabaseHas('dispensasis', ['siswa_id' => $student->id, 'piket_id' => $data['piket']->id,
            'status_piket' => 'Disetujui', 'status_admin' => 'Menunggu', 'status_akhir' => 'Menunggu']);
    }
    Notification::assertSentToTimes($data['admin'], DispensasiApprovalMail::class, 1);
    Notification::assertNotSentTo($data['teacher'], DispensasiNotification::class);
});

it('rejects missing duplicate and nonexistent students without partial requests', function (array $ids) {
    $data = dispensasiSetup();
    Notification::fake();
    $ids = array_map(fn ($id) => $id === 'first' ? $data['students']->first()->id : $id, $ids);

    $this->actingAs($data['piket'])->post(route('dispensasi.store'), [...$data['payload'], 'siswa_ids' => $ids])->assertSessionHasErrors();

    $this->assertDatabaseCount('dispensasis', 0);
    Notification::assertNothingSent();
})->with(['empty' => [[]], 'duplicates' => [['first', 'first']], 'unknown student' => [['first', 999999]]]);

it('lets students create only their own online dispensation', function () {
    $data = dispensasiSetup();
    $this->travelTo(Carbon::parse('2026-09-14 06:00:00', 'Asia/Jakarta'));
    Notification::fake();
    $student = $data['students']->first();

    $this->actingAs($student->user)->get(route('dispensasi.create'))->assertOk();
    $this->actingAs($student->user)->post(route('dispensasi.store'), [...$data['payload'], 'siswa_ids' => [$data['students']->last()->id]])->assertForbidden();
    $this->actingAs($student->user)->post(route('dispensasi.store'), $data['payload'])
        ->assertRedirect(route('dispensasi.index'));
    $this->assertDatabaseHas('dispensasis', ['siswa_id' => $student->id, 'piket_id' => null]);
    $this->assertDatabaseMissing('dispensasis', ['siswa_id' => $data['students']->last()->id]);
    Notification::assertSentTo($data['piket'], DispensasiNotification::class, fn ($notification) => $notification->event === 'submitted');
});

it('sends the admin an email when piket approves a student request', function () {
    $data = dispensasiSetup();
    Notification::fake();
    $dispensasi = Dispensasi::create([...$data['payload'], 'siswa_id' => $data['students']->first()->id]);

    $this->actingAs($data['piket'])->post(route('dispensasi.verify', $dispensasi), ['status' => 'Disetujui'])->assertRedirect();

    $this->assertDatabaseHas('dispensasis', ['id' => $dispensasi->id, 'status_piket' => 'Disetujui', 'status_akhir' => 'Menunggu']);
    Notification::assertSentTo($data['admin'], DispensasiApprovalMail::class);
});

it('marks only overlapping journals and notifies the teacher once after admin approval', function () {
    $this->freezeTime();
    $data = dispensasiSetup();
    Notification::fake();
    $student = $data['students']->first();
    $journal = Jurnal::create($data['journal']);
    $later = Jurnal::create([...$data['journal'], 'jam_mulai_id' => $data['end']->id, 'jam_selesai_id' => $data['end']->id]);
    $dispensasi = Dispensasi::create([...$data['payload'], 'siswa_id' => $student->id, 'status_piket' => 'Disetujui']);

    $this->actingAs($data['admin'])->post(route('dispensasi.verify', $dispensasi), ['status' => 'Disetujui'])->assertRedirect();

    $this->assertDatabaseHas('dispensasis', ['id' => $dispensasi->id, 'status_akhir' => 'Disetujui', 'admin_id' => $data['admin']->id]);
    $this->assertDatabaseHas('absensis', ['jurnal_id' => $journal->id, 'siswa_id' => $student->id, 'status' => 'D']);
    $this->assertDatabaseMissing('absensis', ['jurnal_id' => $later->id, 'status' => 'D']);
    Notification::assertSentToTimes($data['teacher'], DispensasiNotification::class, 1);
    Notification::assertSentTo($data['teacher'], DispensasiNotification::class, fn ($notification) => $notification->event === 'teacher_approved');
});

it('notifies a scheduled teacher even before their journal exists', function () {
    $this->freezeTime();
    $data = dispensasiSetup();
    Notification::fake();
    $dispensasi = Dispensasi::create([...$data['payload'], 'siswa_id' => $data['students']->first()->id, 'status_piket' => 'Disetujui']);

    $this->actingAs($data['admin'])->post(route('dispensasi.verify', $dispensasi), ['status' => 'Disetujui'])->assertRedirect();

    Notification::assertSentTo($data['teacher'], DispensasiNotification::class, fn ($notification) => $notification->event === 'teacher_approved');
    $this->assertDatabaseCount('jurnals', 0);
});

it('notifies only teachers whose Friday schedule overlaps the dispensation and reports Friday times', function () {
    $data = dispensasiSetup();
    $data['start']->update(['jam_mulai_jumat' => '10:00', 'jam_selesai_jumat' => '10:30']);
    $data['end']->update(['jam_mulai_jumat' => '10:30', 'jam_selesai_jumat' => '11:00']);
    $data['schedule']->update(['hari' => 'Jumat']);
    $laterTeacher = User::factory()->create(['role' => 'guru', 'is_active' => true]);
    $laterGuru = Guru::create(['user_id' => $laterTeacher->id, 'nip' => 'FRIDAY-LATER', 'nama_guru' => 'Guru Sore', 'status_kepegawaian' => 'Honorer']);
    $laterPeriod = JamPelajaran::create([
        'jam_ke' => 3, 'jam_mulai' => '12:00', 'jam_selesai' => '12:30',
        'jam_mulai_jumat' => '11:30', 'jam_selesai_jumat' => '12:00', 'is_active' => true,
    ]);
    Jadwal::create(['guru_id' => $laterGuru->id, 'kelas_id' => $data['kelas']->id, 'mapel_id' => $data['mapel']->id,
        'jam_pelajaran_id' => $laterPeriod->id, 'hari' => 'Jumat', 'is_active' => true]);
    $this->travelTo(Carbon::parse('2026-09-18 10:20:00', 'Asia/Jakarta'));
    Notification::fake();
    $dispensasi = Dispensasi::create([
        ...$data['payload'], 'tanggal' => '2026-09-18', 'jam_mulai_id' => $data['start']->id,
        'jam_selesai_id' => $data['end']->id, 'siswa_id' => $data['students']->first()->id, 'status_piket' => 'Disetujui',
    ]);

    $this->actingAs($data['admin'])->post(route('dispensasi.verify', $dispensasi), ['status' => 'Disetujui'])->assertRedirect();

    Notification::assertSentTo($data['teacher'], DispensasiNotification::class, function (DispensasiNotification $notification) use ($data): bool {
        return $notification->event === 'teacher_approved'
            && str_contains($notification->toArray($data['teacher'])['message'], '10:00–11:00');
    });
    Notification::assertNotSentTo($laterTeacher, DispensasiNotification::class);
});

it('does not mark attendance or notify teachers when admin rejects', function () {
    $data = dispensasiSetup();
    Notification::fake();
    $journal = Jurnal::create($data['journal']);
    $dispensasi = Dispensasi::create([...$data['payload'], 'siswa_id' => $data['students']->first()->id, 'status_piket' => 'Disetujui']);

    $this->actingAs($data['admin'])->post(route('dispensasi.verify', $dispensasi), ['status' => 'Ditolak'])->assertRedirect();

    $this->assertDatabaseHas('dispensasis', ['id' => $dispensasi->id, 'status_akhir' => 'Ditolak']);
    $this->assertDatabaseMissing('absensis', ['jurnal_id' => $journal->id, 'status' => 'D']);
    Notification::assertNotSentTo($data['teacher'], DispensasiNotification::class);
});

it('refuses admin approval before piket and refuses a repeated decision', function (string $piketStatus, string $finalStatus) {
    $data = dispensasiSetup();
    Notification::fake();
    $dispensasi = Dispensasi::create([...$data['payload'], 'siswa_id' => $data['students']->first()->id, 'status_piket' => $piketStatus, 'status_akhir' => $finalStatus]);

    $this->actingAs($data['admin'])->post(route('dispensasi.verify', $dispensasi), ['status' => 'Disetujui'])->assertUnprocessable();

    $this->assertDatabaseHas('dispensasis', ['id' => $dispensasi->id, 'status_akhir' => $finalStatus]);
    Notification::assertNothingSent();
})->with([['Menunggu', 'Menunggu'], ['Disetujui', 'Disetujui'], ['Disetujui', 'Ditolak']]);

it('uses approved dispensations on later journal creation and preserves them on edit', function () {
    $this->freezeTime();
    $data = dispensasiSetup();
    $student = $data['students']->first();
    Dispensasi::create([...$data['payload'], 'siswa_id' => $student->id, 'status_piket' => 'Disetujui', 'status_admin' => 'Disetujui', 'status_akhir' => 'Disetujui']);

    $this->actingAs($data['teacher'])->post(route('jurnal.store'), ['jadwal_id' => $data['schedule']->id, 'status_guru' => 'Hadir', 'materi' => 'Aljabar'])->assertRedirect();
    $journal = Jurnal::firstOrFail();
    $this->assertDatabaseHas('absensis', ['jurnal_id' => $journal->id, 'siswa_id' => $student->id, 'status' => 'D']);

    $this->actingAs($data['teacher'])->put(route('jurnal.update', $journal), [...$data['journal'], 'absensi' => [['siswa_id' => $student->id, 'status' => 'H']]])->assertRedirect();
    $this->assertDatabaseHas('absensis', ['jurnal_id' => $journal->id, 'siswa_id' => $student->id, 'status' => 'D']);
});

it('rejects manually forged dispensation attendance', function () {
    $data = dispensasiSetup();
    $student = $data['students']->first();

    $this->actingAs($data['teacher'])->post(route('jurnal.store'), [...$data['journal'], 'absensi' => [['siswa_id' => $student->id, 'status' => 'D']]])->assertUnprocessable();

    $this->assertDatabaseCount('jurnals', 0);
});

it('derives journal fields from the authenticated teachers schedule', function () {
    $this->freezeTime();
    $data = dispensasiSetup();

    $this->actingAs($data['teacher'])->post(route('jurnal.store'), ['jadwal_id' => $data['schedule']->id,
        'tanggal' => '2000-01-01', 'kelas_id' => 99999, 'jam_mulai_id' => $data['end']->id, 'status_guru' => 'Hadir', 'materi' => 'Aljabar'])->assertRedirect();

    $this->assertDatabaseHas('jurnals', ['guru_id' => $data['guru']->id, 'kelas_id' => $data['kelas']->id,
        'mapel_id' => $data['mapel']->id, 'jam_mulai_id' => $data['start']->id, 'jam_selesai_id' => $data['start']->id,
        'status_guru' => 'Hadir']);
    expect(Jurnal::firstOrFail()->tanggal->toDateString())->toBe(today()->toDateString());
});

it('rejects a schedule belonging to another teacher or an inactive schedule', function (string $case) {
    $data = dispensasiSetup();
    if ($case === 'other teacher') {
        $data['schedule']->update(['guru_id' => Guru::create(['user_id' => User::factory()->create(['role' => 'guru'])->id, 'nip' => '98765', 'nama_guru' => 'Guru lain', 'status_kepegawaian' => 'Honorer'])->id]);
    } else {
        $data['schedule']->update(['is_active' => false]);
    }

    $this->actingAs($data['teacher'])->post(route('jurnal.store'), [...$data['journal'], 'jadwal_id' => $data['schedule']->id])->assertSessionHasErrors('jadwal_id');

    $this->assertDatabaseCount('jurnals', 0);
})->with(['other teacher', 'inactive']);

it('renders the journal and piket forms with automatic data and student selection', function () {
    $data = dispensasiSetup();
    Jurnal::create([...$data['journal'], 'tanggal' => today()->subDay()->toDateString(), 'materi' => 'Materi sebelumnya']);

    $this->actingAs($data['teacher'])->get(route('jurnal.create'))->assertOk()->assertSee('Kehadiran guru')->assertDontSee('Cari kelas')->assertViewHas('activeSession', fn ($session) => $session['id'] === $data['schedule']->id);
    $this->actingAs($data['piket'])->get(route('dispensasi.create'))->assertOk()->assertSee('Tambahkan siswa');
});

it('renders an admin email containing the review link and student details', function () {
    $data = dispensasiSetup();
    $dispensasi = Dispensasi::create([...$data['payload'], 'siswa_id' => $data['students']->first()->id, 'piket_id' => $data['piket']->id, 'status_piket' => 'Disetujui']);
    $mail = (new DispensasiApprovalMail($dispensasi))->toMail($data['admin']);

    expect($mail->actionUrl)->toBe(route('dispensasi.show', $dispensasi));
    expect((string) $mail->render())->toContain('Siswa 1', 'Lomba sekolah', 'Periksa dan verifikasi dispensasi');
});

it('returns the admin to email review after login without approving on GET', function () {
    $data = dispensasiSetup();
    $dispensasi = Dispensasi::create([...$data['payload'], 'siswa_id' => $data['students']->first()->id, 'status_piket' => 'Disetujui']);

    $this->get(route('dispensasi.show', $dispensasi))->assertRedirect(route('login'));
    $this->post('/login', ['email' => $data['admin']->email, 'password' => 'password'])->assertRedirect(route('dispensasi.show', $dispensasi));
    $this->get(route('dispensasi.show', $dispensasi))->assertOk()->assertSee('Ambil keputusan');
    $this->assertDatabaseHas('dispensasis', ['id' => $dispensasi->id, 'status_akhir' => 'Menunggu']);
});

it('forbids teachers from issuing dispensations and students from verifying', function () {
    $data = dispensasiSetup();
    $dispensasi = Dispensasi::create([...$data['payload'], 'siswa_id' => $data['students']->first()->id]);

    $this->actingAs($data['teacher'])->post(route('dispensasi.store'), [...$data['payload'], 'siswa_ids' => $data['students']->pluck('id')->all()])->assertForbidden();
    $this->actingAs($data['students']->first()->user)->post(route('dispensasi.verify', $dispensasi), ['status' => 'Disetujui'])->assertForbidden();
    $this->assertDatabaseCount('dispensasis', 1);
    $this->assertDatabaseHas('dispensasis', ['id' => $dispensasi->id, 'status_akhir' => 'Menunggu']);
});

it('shows approved students as dispen on the journal detail page', function () {
    $data = dispensasiSetup();
    $journal = Jurnal::create($data['journal']);
    $journal->absensis()->create(['siswa_id' => $data['students']->first()->id, 'status' => 'D']);

    $this->actingAs($data['teacher'])->get(route('jurnal.show', $journal))->assertOk()->assertSee('Dispen')->assertSee('Siswa 1');
});

it('protects automatic dispensation status through the separate attendance endpoint', function (bool $approved) {
    $data = dispensasiSetup();
    $student = $data['students']->first();
    $journal = Jurnal::create($data['journal']);
    if ($approved) {
        Dispensasi::create([...$data['payload'], 'siswa_id' => $student->id, 'status_akhir' => 'Disetujui']);
    }

    $response = $this->actingAs($data['teacher'])->post(route('absensi.store'), [
        'jurnal_id' => $journal->id, 'siswa_id' => $student->id, 'status' => $approved ? 'H' : 'D',
    ]);

    if ($approved) {
        $response->assertRedirect();
        $this->assertDatabaseHas('absensis', ['jurnal_id' => $journal->id, 'siswa_id' => $student->id, 'status' => 'D']);
    } else {
        $response->assertUnprocessable();
        $this->assertDatabaseCount('absensis', 0);
    }
})->with([true, false]);

it('does not apply pending rejected or nonoverlapping dispensations to new journals', function (string $status, bool $overlaps) {
    $data = dispensasiSetup();
    $student = $data['students']->first();
    Dispensasi::create([...$data['payload'], 'siswa_id' => $student->id, 'status_akhir' => $status,
        'jam_mulai_id' => $overlaps ? $data['start']->id : $data['end']->id,
        'jam_selesai_id' => $overlaps ? $data['start']->id : $data['end']->id]);

    $this->actingAs($data['teacher'])->post(route('jurnal.store'), $data['journal'])->assertRedirect();

    $this->assertDatabaseHas('absensis', ['siswa_id' => $student->id, 'status' => 'H']);
})->with([['Menunggu', true], ['Ditolak', true], ['Disetujui', false]]);
