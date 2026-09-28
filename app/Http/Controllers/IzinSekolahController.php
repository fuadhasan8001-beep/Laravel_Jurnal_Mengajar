<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\IzinSekolah;
use App\Models\Jurnal;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class IzinSekolahController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizePiketAccess($request);

        return view('izin-sekolah.index', [
            'izinSekolahs' => IzinSekolah::with(['siswa.kelas', 'piket'])
                ->latest('tanggal')->latest('id')->paginate(20),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizePiketAccess($request);

        return view('izin-sekolah.create', [
            'siswas' => Siswa::with('kelas')->orderBy('nama_siswa')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePiketAccess($request);
        $data = $request->validate([
            'siswa_ids' => ['required', 'array', 'min:1', 'max:100'],
            'siswa_ids.*' => ['required', 'integer', 'distinct', 'exists:siswas,id'],
            'status' => ['nullable', 'in:I,S'],
            'alasan' => ['nullable', 'string', 'max:5000'],
            'surat_izin' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $data['tanggal'] = today()->toDateString();
        $data['status'] = $data['status'] ?? 'I';

        $alreadyRecorded = IzinSekolah::whereDate('tanggal', $data['tanggal'])
            ->whereIn('siswa_id', $data['siswa_ids'])->exists();
        if ($alreadyRecorded) {
            return back()->withInput()->withErrors([
                'siswa_ids' => 'Salah satu siswa sudah tercatat izin sekolah pada tanggal tersebut.',
            ]);
        }

        $students = Siswa::whereIn('id', $data['siswa_ids'])->get();
        $path = $request->file('surat_izin')->store('izin-sekolah/surat');

        try {
            DB::transaction(function () use ($data, $students, $path): void {
                foreach ($students as $student) {
                    IzinSekolah::create([
                        'siswa_id' => $student->id,
                        'tanggal' => $data['tanggal'],
                        'status' => $data['status'],
                        'alasan' => $data['alasan'] ?? null,
                        'surat_izin_path' => $path,
                        'piket_id' => auth()->id(),
                    ]);

                    $jurnals = Jurnal::whereDate('tanggal', $data['tanggal'])
                        ->where('kelas_id', $student->kelas_id)->get();
                    foreach ($jurnals as $jurnal) {
                        Absensi::updateOrCreate(
                            ['jurnal_id' => $jurnal->id, 'siswa_id' => $student->id],
                            [
                                'status' => $data['status'],
                                'catatan' => $data['status'] === 'S'
                                    ? 'Sakit seharian berdasarkan surat orang tua.'
                                    : 'Izin sekolah seharian berdasarkan surat orang tua.',
                                'surat_izin_path' => $path,
                            ]
                        );
                    }
                }
            });
        } catch (\Throwable $exception) {
            Storage::delete($path);
            throw $exception;
        }

        return redirect()->route('piket.izin-sekolah.index')
            ->with('success', ($data['status'] === 'S' ? 'Surat sakit seharian' : 'Surat izin seharian').' berhasil dicatat. Absensi jurnal hari tersebut sudah diperbarui.');
    }

    private function authorizePiketAccess(Request $request): void
    {
        if ($request->user()->role === 'guru') {
            abort_unless($request->user()->isPiketHariIni(), 403);
        }
    }
}