<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jurnal;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function index(): View
    {
        $query = Jurnal::with(['absensis.siswa', 'kelas.siswas'])
            ->orderByDesc('tanggal');

        if (auth()->user()->role === 'guru') {
            $query->where('guru_id', $this->currentGuru()->id);
        }

        if (auth()->user()->role === 'sekretaris') {
            $query->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id'));
        }

        return view('absensi.index', ['jurnals' => $query->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jurnal_id' => ['required', 'exists:jurnals,id'],
            'siswa_id' => ['required', 'exists:siswas,id'],
            'status' => ['required', 'in:H,S,I,A,D'],
            'catatan' => ['nullable', 'string'],
        ]);

        $jurnal = Jurnal::with(['jamMulai', 'jamSelesai'])->findOrFail($data['jurnal_id']);
        $this->authorizeJurnal($jurnal);

        abort_unless(
            Siswa::whereKey($data['siswa_id'])->where('kelas_id', $jurnal->kelas_id)->exists(),
            422,
            'Siswa tidak termasuk dalam kelas jurnal ini.'
        );

        $hasApprovedDispensasi = Dispensasi::approvedForJournal($jurnal)->contains('siswa_id', $data['siswa_id']);
        abort_if($data['status'] === 'D' && ! $hasApprovedDispensasi, 422, 'Status dispensasi memerlukan persetujuan admin.');
        if ($hasApprovedDispensasi) {
            $data['status'] = 'D';
            $data['catatan'] = 'Dispensasi disetujui.';
        }

        Absensi::updateOrCreate(
            [
                'jurnal_id' => $data['jurnal_id'],
                'siswa_id' => $data['siswa_id'],
            ],
            [
                'status' => $data['status'],
                'catatan' => $data['catatan'] ?? null,
            ]
        );

        return back()->with('success', 'Absensi berhasil disimpan.');
    }

    private function authorizeJurnal(Jurnal $jurnal): void
    {
        if (auth()->user()->role === 'guru') {
            abort_unless($jurnal->guru_id === $this->currentGuru()->id, 403);
        }

        if (auth()->user()->role === 'sekretaris') {
            abort_unless(auth()->user()->kelasSekretaris()->whereKey($jurnal->kelas_id)->exists(), 403);
        }
    }

    private function currentGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }
}
