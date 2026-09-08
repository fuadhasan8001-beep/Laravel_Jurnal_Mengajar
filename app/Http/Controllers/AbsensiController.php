<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jurnal;
use App\Models\Siswa;
use Carbon\Carbon;
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

        $hasApprovedDispensasi = Dispensasi::with(['jamMulai', 'jamSelesai'])
            ->where('siswa_id', $data['siswa_id'])
            ->where('status_akhir', 'Disetujui')
            ->whereDate('tanggal', $jurnal->tanggal)
            ->get()
            ->contains(function (Dispensasi $dispensasi) use ($jurnal): bool {
                if (! $jurnal->jamMulai || ! $jurnal->jamSelesai) {
                    return false;
                }

                $dispensasiStart = Carbon::parse($dispensasi->jamMulai->jam_mulai);
                $dispensasiEnd = Carbon::parse($dispensasi->jamSelesai->jam_selesai);
                $jurnalStart = Carbon::parse($jurnal->jamMulai->jam_mulai);
                $jurnalEnd = Carbon::parse($jurnal->jamSelesai->jam_selesai);

                return $jurnalStart < $dispensasiEnd && $jurnalEnd > $dispensasiStart;
            });

        $existingAbsensi = Absensi::where('jurnal_id', $data['jurnal_id'])
            ->where('siswa_id', $data['siswa_id'])
            ->first();

        if ($hasApprovedDispensasi || $existingAbsensi?->status === 'D') {
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
    }

    private function currentGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }
}
