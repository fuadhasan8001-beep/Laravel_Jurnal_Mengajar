<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminDataController;
use App\Http\Controllers\AdminReferenceController;
use App\Http\Controllers\AdminRegistrationController;
use App\Http\Controllers\DispensasiController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JurnalController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', [RegistrationController::class, 'create'])->name('register');
Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
Route::get('/register/success', [RegistrationController::class, 'success'])->name('register.success');

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

Route::middleware('role:admin')->prefix('admin/data')->group(function () {
    Route::get('/guru', [AdminDataController::class, 'gurus'])->name('admin.gurus.index');
    Route::get('/guru/create', [AdminDataController::class, 'createGuru'])->name('admin.gurus.create');
    Route::post('/guru', [AdminDataController::class, 'storeGuru'])->name('admin.gurus.store');
    Route::get('/guru/{guru}/edit', [AdminDataController::class, 'editGuru'])->name('admin.gurus.edit');
    Route::put('/guru/{guru}', [AdminDataController::class, 'updateGuru'])->name('admin.gurus.update');
    Route::delete('/guru/{guru}', [AdminDataController::class, 'destroyGuru'])->name('admin.gurus.destroy');
    Route::get('/siswa', [AdminDataController::class, 'siswas'])->name('admin.siswas.index');
    Route::get('/siswa/create', [AdminDataController::class, 'createSiswa'])->name('admin.siswas.create');
    Route::post('/siswa', [AdminDataController::class, 'storeSiswa'])->name('admin.siswas.store');
    Route::post('/siswa/import', [AdminDataController::class, 'importSiswa'])->name('admin.siswas.import');
    Route::get('/siswa/{siswa}/edit', [AdminDataController::class, 'editSiswa'])->name('admin.siswas.edit');
    Route::put('/siswa/{siswa}', [AdminDataController::class, 'updateSiswa'])->name('admin.siswas.update');
    Route::delete('/siswa/{siswa}', [AdminDataController::class, 'destroySiswa'])->name('admin.siswas.destroy');
    Route::get('/kelas', [AdminReferenceController::class, 'kelas'])->name('admin.kelas.index');
    Route::post('/kelas', [AdminReferenceController::class, 'storeKelas'])->name('admin.kelas.store');
    Route::get('/kelas/create', fn () => view('admin.reference.form', ['type' => 'kelas', 'title' => 'Tambah Kelas']))->name('admin.kelas.create');
    Route::get('/kelas/{kelas}/edit', [AdminReferenceController::class, 'editKelas'])->name('admin.kelas.edit');
    Route::put('/kelas/{kelas}', [AdminReferenceController::class, 'updateKelas'])->name('admin.kelas.update');
    Route::delete('/kelas/{kelas}', [AdminReferenceController::class, 'destroyKelas'])->name('admin.kelas.destroy');
    Route::get('/mapel', [AdminReferenceController::class, 'mapel'])->name('admin.mapel.index');
    Route::post('/mapel', [AdminReferenceController::class, 'storeMapel'])->name('admin.mapel.store');
    Route::get('/mapel/create', fn () => view('admin.reference.form', ['type' => 'mapel', 'title' => 'Tambah Mata Pelajaran']))->name('admin.mapel.create');
    Route::get('/mapel/{mapel}/edit', [AdminReferenceController::class, 'editMapel'])->name('admin.mapel.edit');
    Route::put('/mapel/{mapel}', [AdminReferenceController::class, 'updateMapel'])->name('admin.mapel.update');
    Route::delete('/mapel/{mapel}', [AdminReferenceController::class, 'destroyMapel'])->name('admin.mapel.destroy');
    Route::get('/jam', [AdminReferenceController::class, 'jam'])->name('admin.jam.index');
    Route::post('/jam', [AdminReferenceController::class, 'storeJam'])->name('admin.jam.store');
    Route::get('/jam/create', fn () => view('admin.reference.form', ['type' => 'jam', 'title' => 'Tambah Jam Pelajaran']))->name('admin.jam.create');
    Route::get('/jam/{jam}/edit', [AdminReferenceController::class, 'editJam'])->name('admin.jam.edit');
    Route::put('/jam/{jam}', [AdminReferenceController::class, 'updateJam'])->name('admin.jam.update');
    Route::delete('/jam/{jam}', [AdminReferenceController::class, 'destroyJam'])->name('admin.jam.destroy');
});

Route::middleware('role:admin')->prefix('admin/registrations')->name('admin.registrations.')->group(function () {
    Route::get('/', [AdminRegistrationController::class, 'index'])->name('index');
    Route::get('/{registration}', [AdminRegistrationController::class, 'show'])->name('show');
    Route::post('/{registration}/approve', [AdminRegistrationController::class, 'approve'])->name('approve');
    Route::post('/{registration}/reject', [AdminRegistrationController::class, 'reject'])->name('reject');
});

Route::middleware('role:admin,guru,sekretaris,piket')->prefix('rekap')->group(function () {
    Route::get('/jurnal', [LaporanController::class, 'jurnal'])->name('laporan.jurnal');
    Route::get('/jurnal/export', [LaporanController::class, 'jurnalExport'])->name('laporan.jurnal.export');
    Route::get('/absensi', [LaporanController::class, 'absensi'])->name('laporan.absensi');
    Route::get('/absensi/export', [LaporanController::class, 'absensiExport'])->name('laporan.absensi.export');
    Route::get('/dispensasi', [LaporanController::class, 'dispensasi'])->name('laporan.dispensasi');
    Route::get('/dispensasi/export', [LaporanController::class, 'dispensasiExport'])->name('laporan.dispensasi.export');
});

Route::middleware('role:guru,admin,sekretaris')->group(function () {
    Route::get('/jurnal', [JurnalController::class, 'index'])->name('jurnal.index');
    Route::get('/jurnal/{jurnal}', [JurnalController::class, 'show'])
        ->whereNumber('jurnal')
        ->name('jurnal.show');
});

Route::post('/jurnal/{jurnal}/verify', [JurnalController::class, 'verify'])
    ->middleware('role:sekretaris')
    ->whereNumber('jurnal')
    ->name('jurnal.verify');

Route::middleware('role:guru')->group(function () {
    Route::get('/jurnal/create', [JurnalController::class, 'create'])->name('jurnal.create');
    Route::post('/jurnal', [JurnalController::class, 'store'])->name('jurnal.store');
    Route::get('/jurnal/{jurnal}/edit', [JurnalController::class, 'edit'])
        ->whereNumber('jurnal')
        ->name('jurnal.edit');
    Route::put('/jurnal/{jurnal}', [JurnalController::class, 'update'])
        ->whereNumber('jurnal')
        ->name('jurnal.update');
    Route::delete('/jurnal/{jurnal}', [JurnalController::class, 'destroy'])
        ->whereNumber('jurnal')
        ->name('jurnal.destroy');
});

Route::middleware('role:admin,guru,sekretaris')->group(function () {
    Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
    Route::get('/jadwal/{jadwal}', [JadwalController::class, 'show'])
        ->whereNumber('jadwal')
        ->name('jadwal.show');
});

Route::middleware('role:admin')->group(function () {
    Route::get('/jadwal/create', [JadwalController::class, 'create'])->name('jadwal.create');
    Route::post('/jadwal', [JadwalController::class, 'store'])->name('jadwal.store');
    Route::get('/jadwal/{jadwal}/edit', [JadwalController::class, 'edit'])
        ->whereNumber('jadwal')
        ->name('jadwal.edit');
    Route::put('/jadwal/{jadwal}', [JadwalController::class, 'update'])
        ->whereNumber('jadwal')
        ->name('jadwal.update');
    Route::delete('/jadwal/{jadwal}', [JadwalController::class, 'destroy'])
        ->whereNumber('jadwal')
        ->name('jadwal.destroy');
});

Route::get('/admin', function () {
    return view('dashboard.admin', [
        'totalGuru' => Guru::count(),
        'totalSiswa' => Siswa::count(),
        'totalKelas' => Kelas::count(),
        'totalMapel' => Mapel::count(),
        'totalJurnal' => Jurnal::count(),
        'jurnalHariIni' => Jurnal::whereDate('tanggal', today())->count(),
        'jurnalBulanIni' => Jurnal::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count(),
        'jurnalBelumLengkap' => Jurnal::where(function ($query): void {
            $query->whereNull('tujuan_pembelajaran')->orWhere('tujuan_pembelajaran', '');
        })->count(),
        'menungguVerifikasi' => Dispensasi::where('status_akhir', 'Menunggu')->count(),
        'disetujuiBulanIni' => Dispensasi::where('status_akhir', 'Disetujui')->whereMonth('updated_at', now()->month)->count(),
        'perluPerhatian' => Dispensasi::where('status_akhir', 'Ditolak')->whereMonth('updated_at', now()->month)->count(),
    ]);
})->middleware('role:admin');

Route::get('/guru', function () {
    $hariIni = now()->locale('id')->translatedFormat('l');
    $guru = Guru::where('user_id', auth()->id())->first();

    return view('dashboard.guru', [
        'jurnalMingguIni' => Jurnal::when($guru, fn ($query) => $query->where('guru_id', $guru->id))
            ->whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])
            ->count(),
        'kelasAktif' => Jurnal::when($guru, fn ($query) => $query->where('guru_id', $guru->id))
            ->distinct('kelas_id')
            ->count('kelas_id'),
        'siswaTerpantau' => Siswa::count(),
        'jadwalHariIni' => Jadwal::with(['kelas', 'mapel', 'jamPelajaran'])
            ->when($guru, fn ($query) => $query->where('guru_id', $guru->id))
            ->where('hari', $hariIni)
            ->where('is_active', true)
            ->get(),
        'jurnalHariIni' => Jurnal::when($guru, fn ($query) => $query->where('guru_id', $guru->id))
            ->whereDate('tanggal', today())
            ->get(),
    ]);
})->middleware('role:guru');

Route::get('/siswa', function () {
    $siswa = Siswa::where('user_id', auth()->id())->first();
    $dispensasi = $siswa ? Dispensasi::where('siswa_id', $siswa->id) : Dispensasi::whereRaw('1 = 0');

    return view('dashboard.siswa', [
        'menunggu' => (clone $dispensasi)->where('status_akhir', 'Menunggu')->count(),
        'disetujui' => (clone $dispensasi)->where('status_akhir', 'Disetujui')->count(),
        'ditolak' => (clone $dispensasi)->where('status_akhir', 'Ditolak')->count(),
        'totalPengajuan' => $dispensasi->count(),
    ]);
})->middleware('role:siswa');

Route::get('/sekretaris', function () {
    return view('dashboard.sekretaris', [
        'jurnalTercatat' => Jurnal::count(),
        'jurnalLengkap' => Jurnal::whereNotNull('materi')->where('materi', '!=', '')->count(),
        'jurnalMenunggu' => Jurnal::where('status_verifikasi', 'Menunggu')->count(),
        'kelasAktif' => Jurnal::distinct('kelas_id')->count('kelas_id'),
        'jurnalPerluVerifikasi' => Jurnal::with(['guru', 'kelas', 'mapel'])
            ->where('status_verifikasi', 'Menunggu')
            ->latest('tanggal')
            ->limit(5)
            ->get(),
    ]);
})->middleware('role:sekretaris');

Route::get('/piket', function () {
    return view('dashboard.piket', [
        'antrianBaru' => Dispensasi::where('status_akhir', 'Menunggu')->count(),
        'diverifikasiHariIni' => Dispensasi::whereDate('verified_piket_at', today())->count(),
        'totalBulanIni' => Dispensasi::whereMonth('created_at', now()->month)->count(),
        'perluPerhatian' => Dispensasi::where('status_piket', 'Ditolak')->whereMonth('updated_at', now()->month)->count(),
        'pengajuanMenunggu' => Dispensasi::with(['siswa', 'jamMulai', 'jamSelesai'])
            ->where('status_akhir', 'Menunggu')
            ->latest()
            ->limit(5)
            ->get(),
    ]);
})->middleware('role:piket');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
});

Route::get('/absensi', [AbsensiController::class, 'index'])
    ->middleware('role:admin,guru,piket')
    ->name('absensi.index');

Route::post('/absensi', [AbsensiController::class, 'store'])
    ->middleware('role:admin,guru,piket')
    ->name('absensi.store');

Route::get('/dispensasi/create', [DispensasiController::class, 'create'])
    ->middleware('role:siswa')
    ->name('dispensasi.create');

Route::middleware('role:siswa,piket,admin')->group(function () {
    Route::get('/dispensasi', [DispensasiController::class, 'index'])->name('dispensasi.index');
    Route::get('/dispensasi/{dispensasi}/bukti', [DispensasiController::class, 'downloadEvidence'])
        ->name('dispensasi.evidence');
    Route::get('/dispensasi/{dispensasi}', [DispensasiController::class, 'show'])->name('dispensasi.show');
});

Route::middleware('role:siswa')->group(function () {
    Route::post('/dispensasi', [DispensasiController::class, 'store'])->name('dispensasi.store');
});

Route::post('/dispensasi/{dispensasi}/verify', [DispensasiController::class, 'verify'])
    ->middleware('role:piket,admin')
    ->name('dispensasi.verify');
