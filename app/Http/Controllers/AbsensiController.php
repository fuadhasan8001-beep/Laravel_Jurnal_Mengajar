<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\Guru;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Siswa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AbsensiController extends Controller
{
    public function index(Request $request): View
    {
        $query = Jurnal::with(['absensis.siswa', 'kelas.siswas'])
            ->orderByDesc('tanggal');

        if (auth()->user()->role === 'guru') {
            $query->where('guru_id', $this->currentGuru()->id);
        }

        if (auth()->user()->role === 'sekretaris') {
            $query->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id'));
        }

        $query
            ->when($request->filled('tanggal_mulai'), fn ($builder) => $builder->whereDate('tanggal', '>=', $request->date('tanggal_mulai')))
            ->when($request->filled('tanggal_selesai'), fn ($builder) => $builder->whereDate('tanggal', '<=', $request->date('tanggal_selesai')))
            ->when($request->filled('kelas_id'), fn ($builder) => $builder->where('kelas_id', $request->integer('kelas_id')))
            ->when($request->filled('guru_id'), fn ($builder) => $builder->where('guru_id', $request->integer('guru_id')))
            ->when($request->filled('mapel_id'), fn ($builder) => $builder->where('mapel_id', $request->integer('mapel_id')))
            ->when($request->filled('siswa_id'), fn ($builder) => $builder->whereHas('absensis', fn ($attendance) => $attendance->where('siswa_id', $request->integer('siswa_id'))));

        return view('absensi.index', [
            'jurnals' => $query->paginate(15)->withQueryString(),
            'gurus' => Guru::orderBy('nama_guru')->get(),
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
            'mapels' => Mapel::orderBy('nama_mapel')->get(),
            'siswas' => Siswa::orderBy('nama_siswa')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->has('absensis')) {
            return $this->storeMultiple($request);
        }

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

    private function storeMultiple(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jurnal_id' => ['required', 'exists:jurnals,id'],
            'absensis' => ['required', 'array', 'min:1'],
            'absensis.*.siswa_id' => ['required', 'integer', 'distinct', 'exists:siswas,id'],
            'absensis.*.status' => ['required', 'in:H,S,I,A,D'],
            'absensis.*.catatan' => ['nullable', 'string'],
        ]);

        $jurnal = Jurnal::with(['jamMulai', 'jamSelesai'])->findOrFail($data['jurnal_id']);
        $this->authorizeJurnal($jurnal);

        $records = collect($data['absensis']);
        $studentIds = $records->pluck('siswa_id');
        $studentsInClass = Siswa::where('kelas_id', $jurnal->kelas_id)
            ->whereIn('id', $studentIds)
            ->pluck('id');
        abort_unless($studentIds->diff($studentsInClass)->isEmpty(), 422, 'Siswa tidak termasuk dalam kelas jurnal ini.');

        $approvedDispensations = Dispensasi::approvedForJournal($jurnal)->pluck('siswa_id');
        DB::transaction(function () use ($records, $jurnal, $approvedDispensations): void {
            foreach ($records as $record) {
                $studentId = (int) $record['siswa_id'];
                $hasApprovedDispensation = $approvedDispensations->contains($studentId);
                abort_if($record['status'] === 'D' && ! $hasApprovedDispensation, 422, 'Status dispensasi memerlukan persetujuan admin.');

                Absensi::updateOrCreate(
                    ['jurnal_id' => $jurnal->id, 'siswa_id' => $studentId],
                    [
                        'status' => $hasApprovedDispensation ? 'D' : $record['status'],
                        'catatan' => $hasApprovedDispensation ? 'Dispensasi disetujui.' : ($record['catatan'] ?? null),
                    ]
                );
            }
        });

        return back()->with('success', 'Absensi berhasil disimpan.');
    }

    public function downloadParentLetter(Absensi $absensi): BinaryFileResponse
    {
        $absensi->load('jurnal');
        $this->authorizeJurnal($absensi->jurnal);

        abort_unless($absensi->surat_izin_path && Storage::exists($absensi->surat_izin_path), 404);

        return response()->download(Storage::path($absensi->surat_izin_path));
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
