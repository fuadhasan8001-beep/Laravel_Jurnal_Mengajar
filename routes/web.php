<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AbsensiController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/admin', function () {
    return view('dashboard.admin');
})->middleware('role:admin');

Route::get('/guru', function () {
    return view('dashboard.guru');
})->middleware('role:guru');

Route::get('/siswa', function () {
    return view('dashboard.siswa');
})->middleware('role:siswa');

Route::get('/sekretaris', function () {
    return view('dashboard.sekretaris');
})->middleware('role:sekretaris');

Route::get('/piket', function () {
    return view('dashboard.piket');
})->middleware('role:piket');

Route::get('/absensi', [AbsensiController::class, 'index'])
    ->middleware('role:admin,guru,piket')
    ->name('absensi.index');

Route::post('/absensi', [AbsensiController::class, 'store'])
    ->middleware('role:admin,guru,piket')
    ->name('absensi.store');