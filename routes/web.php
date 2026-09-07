<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DispensasiController;
use App\Http\Controllers\LoginController;
use App\Models\Dispensasi;
use App\Models\Jurnal;
use App\Models\Siswa;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/admin', function () {
    return view('dashboard.admin', [
        'totalJurnal' => Jurnal::count(),
        'menungguVerifikasi' => Dispensasi::where('status_akhir', 'Menunggu')->count(),
        'disetujuiBulanIni' => Dispensasi::where('status_akhir', 'Disetujui')->whereMonth('updated_at', now()->month)->count(),
        'perluPerhatian' => Dispensasi::where('status_akhir', 'Ditolak')->whereMonth('updated_at', now()->month)->count(),
    ]);
})->middleware('role:admin');

Route::get('/guru', function () {
    return view('dashboard.guru', [
        'jurnalMingguIni' => Jurnal::whereBetween('tanggal', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        'kelasAktif' => Jurnal::distinct('kelas_id')->count('kelas_id'),
        'siswaTerpantau' => Siswa::count(),
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
    ]);
})->middleware('role:sekretaris');

Route::get('/piket', function () {
    return view('dashboard.piket', [
        'antrianBaru' => Dispensasi::where('status_akhir', 'Menunggu')->count(),
        'diverifikasiHariIni' => Dispensasi::whereDate('verified_piket_at', today())->count(),
        'totalBulanIni' => Dispensasi::whereMonth('created_at', now()->month)->count(),
        'perluPerhatian' => Dispensasi::where('status_piket', 'Ditolak')->whereMonth('updated_at', now()->month)->count(),
    ]);
})->middleware('role:piket');

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
