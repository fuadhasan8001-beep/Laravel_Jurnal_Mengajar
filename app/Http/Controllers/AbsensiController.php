<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Jurnal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AbsensiController extends Controller
{
    public function index(): View
    {
        $jurnals = Jurnal::with('absensis.siswa')
            ->orderByDesc('tanggal')
            ->get();

        return view('absensi.index', compact('jurnals'));
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
}
