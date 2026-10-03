<?php

namespace App\Http\Controllers;

use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\NationalHoliday;
use App\Models\SchoolEvent;
use App\Models\Siswa;
use App\Services\SchoolEventScheduleConflictDetector;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboard.admin', [
            'totalGuru' => Guru::count(),
            'totalSiswa' => Siswa::count(),
            'totalKelas' => Kelas::count(),
            'totalMapel' => Mapel::count(),
            'totalJurnal' => Jurnal::count(),
            'totalJadwal' => Jadwal::where('is_active', true)->count(),
            'jurnalHariIni' => Jurnal::whereDate('tanggal', today())->count(),
            'jurnalBulanIni' => Jurnal::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count(),
            'jurnalTanpaTujuan' => Jurnal::where(fn ($query) => $query->whereNull('tujuan_pembelajaran')->orWhere('tujuan_pembelajaran', ''))->count(),
            'jurnalMenunggu' => Jurnal::where('status_verifikasi', 'Menunggu')->count(),
            'menungguVerifikasi' => Dispensasi::where('status_akhir', 'Menunggu')->count(),
            'disetujuiBulanIni' => Dispensasi::where('status_akhir', 'Disetujui')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count(),
            'perluPerhatian' => Dispensasi::where('status_akhir', 'Ditolak')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count(),
            'masterGurus' => auth()->user()->isMaster() ? Guru::with('user')->orderBy('nama_guru')->get() : collect(),
            'schoolEventsToday' => SchoolEvent::with('attendances')->whereDate('event_date', today())->orderBy('activity_start')->get(),
            'nationalHolidayToday' => NationalHoliday::whereDate('holiday_date', today())->value('name'),
        ]);
    }

    public function guru(SchoolEventScheduleConflictDetector $conflictDetector): View
    {
        $now = now();
        $hariIni = $now->copy()->locale('id')->translatedFormat('l');
        $guru = Guru::where('user_id', auth()->id())->first();
        $nationalHolidayName = NationalHoliday::whereDate('holiday_date', $now->toDateString())->value('name');
        $schoolEvents = SchoolEvent::with('attendances')->whereBetween('event_date', [$now->toDateString(), $now->copy()->addDays(30)->toDateString()])
            ->orderBy('event_date')->orderBy('activity_start')->get()
            ->filter(fn (SchoolEvent $event): bool => $event->isParticipant(auth()->user()))
            ->values();
        $holidayNames = NationalHoliday::whereBetween('holiday_date', [$now->toDateString(), $now->copy()->addDays(30)->toDateString()])->pluck('name', 'holiday_date');
        $schoolEvents->each(function (SchoolEvent $event) use ($guru, $conflictDetector, $holidayNames): void {
            $event->setAttribute('attendance_record', $event->attendances->firstWhere('user_id', auth()->id()));
            $event->setAttribute('schedule_conflicts', $guru ? $conflictDetector->conflicts($event)->where('teacher_id', $guru->id)->values() : collect());
            $event->setAttribute('holiday_name', $holidayNames[$event->event_date->toDateString()] ?? null);
        });
        $todayEvents = $schoolEvents->filter(fn (SchoolEvent $event): bool => $event->event_date->isToday())->values();
        $specialDay = (bool) $nationalHolidayName || $todayEvents->contains(fn (SchoolEvent $event): bool => $event->overridesScheduledAttendance());
        $jadwalHariIni = Jadwal::with(['kelas', 'mapel', 'jamPelajaran'])
            ->where('guru_id', $guru?->id)
            ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
            ->where('hari', $hariIni)
            ->where('is_active', true)
            ->get()
            ->sortBy('jamPelajaran.jam_ke')
            ->values();
        $sessions = $guru && ! $specialDay ? Jadwal::sessionsForGuru($guru, $now) : collect();
        $activeJadwalIds = $sessions->where('active', true)
            ->flatMap(fn (array $session): array => $session['jadwal_ids'])
            ->unique()
            ->values();

        return view('dashboard.guru', [
            'jurnalMingguIni' => Jurnal::where('guru_id', $guru?->id)->whereBetween('tanggal', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])->count(),
            'kelasAktif' => Jurnal::where('guru_id', $guru?->id)->distinct('kelas_id')->count('kelas_id'),
            'siswaTerpantau' => $guru
                ? Siswa::whereIn('kelas_id', Jadwal::query()
                    ->where('guru_id', $guru->id)
                    ->where('is_active', true)
                    ->whereHas('jamPelajaran', fn ($query) => $query->where('is_active', true))
                    ->select('kelas_id')
                    ->distinct())
                    ->count()
                : 0,
            'jadwalHariIni' => $jadwalHariIni,
            'jurnalHariIni' => Jurnal::with(['jamMulai', 'jamSelesai'])->where('guru_id', $guru?->id)->whereDate('tanggal', $now->toDateString())->get(),
            'jurnalTerbaru' => Jurnal::with(['guru', 'kelas', 'mapel', 'jamMulai', 'jamSelesai', 'absensis.siswa'])
                ->where('guru_id', $guru?->id)->latest('tanggal')->first(),
            'activeJadwalIds' => $activeJadwalIds,
            'schoolEvents' => $schoolEvents,
            'todayEvents' => $todayEvents,
            'nationalHolidayName' => $nationalHolidayName,
            'specialDay' => $specialDay,
        ]);
    }

    public function siswa(): View
    {
        $siswa = Siswa::where('user_id', auth()->id())->first();
        $dispensasi = $siswa ? Dispensasi::where('siswa_id', $siswa->id) : Dispensasi::whereRaw('1 = 0');
        $schoolEvents = SchoolEvent::with('attendances')->whereBetween('event_date', [today()->toDateString(), today()->addDays(30)->toDateString()])
            ->orderBy('event_date')->get()->filter(fn (SchoolEvent $event): bool => $event->isParticipant(auth()->user()))->values();
        $holidayNames = NationalHoliday::whereBetween('holiday_date', [today()->toDateString(), today()->addDays(30)->toDateString()])->pluck('name', 'holiday_date');
        $schoolEvents->each(function (SchoolEvent $event) use ($holidayNames): void {
            $event->setAttribute('attendance_record', $event->attendances->firstWhere('user_id', auth()->id()));
            $event->setAttribute('holiday_name', $holidayNames[$event->event_date->toDateString()] ?? null);
            $event->setAttribute('schedule_conflicts', collect());
        });

        return view('dashboard.siswa', [
            'menunggu' => (clone $dispensasi)->where('status_akhir', 'Menunggu')->count(),
            'disetujui' => (clone $dispensasi)->where('status_akhir', 'Disetujui')->count(),
            'ditolak' => (clone $dispensasi)->where('status_akhir', 'Ditolak')->count(),
            'totalPengajuan' => $dispensasi->count(),
            'schoolEvents' => $schoolEvents,
            'nationalHolidayName' => NationalHoliday::whereDate('holiday_date', today())->value('name'),
        ]);
    }

    public function sekretaris(): View
    {
        $kelasSekretaris = auth()->user()->kelasSekretaris()->orderBy('nama_kelas')->get();
        $kelasIds = $kelasSekretaris->modelKeys();

        return view('dashboard.sekretaris', [
            'kelasSekretaris' => $kelasSekretaris,
            'jurnalTercatat' => Jurnal::whereIn('kelas_id', $kelasIds)->count(),
            'jurnalLengkap' => Jurnal::whereIn('kelas_id', $kelasIds)->whereNotNull('materi')->where('materi', '!=', '')->count(),
            'jurnalMenunggu' => Jurnal::whereIn('kelas_id', $kelasIds)->where('status_verifikasi', 'Menunggu')->count(),
            'kelasAktif' => Jurnal::whereIn('kelas_id', $kelasIds)->distinct('kelas_id')->count('kelas_id'),
            'jurnalPerluVerifikasi' => Jurnal::with(['guru', 'kelas', 'mapel'])->whereIn('kelas_id', $kelasIds)
                ->where('status_verifikasi', 'Menunggu')->latest('tanggal')->limit(5)->get(),
        ]);
    }

    public function piket(): View
    {
        abort_unless(auth()->user()->isPiketHariIni(), 403);

        return view('dashboard.piket', [
            'antrianBaru' => Dispensasi::where('status_akhir', 'Menunggu')->count(),
            'diverifikasiHariIni' => Dispensasi::whereDate('verified_piket_at', today())->count(),
            'totalBulanIni' => Dispensasi::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'perluPerhatian' => Dispensasi::where('status_akhir', 'Ditolak')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count(),
            'pengajuanMenunggu' => Dispensasi::with(['siswa', 'jamMulai', 'jamSelesai'])->where('status_akhir', 'Menunggu')->latest()->limit(5)->get(),
        ]);
    }
}
