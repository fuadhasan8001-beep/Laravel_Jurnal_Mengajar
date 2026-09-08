<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJurnalRequest;
use App\Http\Requests\UpdateJurnalRequest;
use App\Models\Guru;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JurnalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Jurnal::with(['guru', 'kelas', 'mapel', 'jamMulai', 'jamSelesai'])
            ->latest('tanggal');

        if (auth()->user()->role === 'guru') {
            $query->where('guru_id', $this->currentGuru()->id);
        }

        $query
            ->when($request->filled('tanggal_mulai'), fn ($builder) => $builder->whereDate('tanggal', '>=', $request->date('tanggal_mulai')))
            ->when($request->filled('tanggal_selesai'), fn ($builder) => $builder->whereDate('tanggal', '<=', $request->date('tanggal_selesai')))
            ->when($request->filled('kelas_id'), fn ($builder) => $builder->where('kelas_id', $request->integer('kelas_id')))
            ->when($request->filled('mapel_id'), fn ($builder) => $builder->where('mapel_id', $request->integer('mapel_id')))
            ->when($request->filled('status_verifikasi'), fn ($builder) => $builder->where('status_verifikasi', $request->string('status_verifikasi')));

        return view('jurnal.index', [
            'jurnals' => $query->paginate(15)->withQueryString(),
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
            'mapels' => Mapel::orderBy('nama_mapel')->get(),
        ]);
    }

    public function create(): View
    {
        return view('jurnal.create', $this->formData());
    }

    public function store(StoreJurnalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->validatePeriodOrder($data);

        $jurnal = Jurnal::create([
            ...$data,
            'guru_id' => $this->currentGuru()->id,
        ]);

        return redirect()->route('jurnal.show', $jurnal)
            ->with('success', 'Jurnal berhasil disimpan.');
    }

    public function show(Jurnal $jurnal): View
    {
        $this->authorizeJournal($jurnal);

        return view('jurnal.show', [
            'jurnal' => $jurnal->load([
                'guru',
                'kelas',
                'mapel',
                'jamMulai',
                'jamSelesai',
                'absensis.siswa',
            ]),
        ]);
    }

    public function edit(Jurnal $jurnal): View
    {
        $this->authorizeJournal($jurnal, true);

        return view('jurnal.edit', [
            ...$this->formData(),
            'jurnal' => $jurnal,
        ]);
    }

    public function update(UpdateJurnalRequest $request, Jurnal $jurnal): RedirectResponse
    {
        $this->authorizeJournal($jurnal, true);
        abort_if($jurnal->status_verifikasi !== 'Menunggu', 422, 'Jurnal yang sudah diverifikasi tidak dapat diubah.');

        $data = $request->validated();
        $this->validatePeriodOrder($data);
        $jurnal->update($data);

        return redirect()->route('jurnal.show', $jurnal)
            ->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(Jurnal $jurnal): RedirectResponse
    {
        $this->authorizeJournal($jurnal, true);
        abort_if($jurnal->status_verifikasi !== 'Menunggu', 422, 'Jurnal yang sudah diverifikasi tidak dapat dihapus.');

        $jurnal->delete();

        return redirect()->route('jurnal.index')
            ->with('success', 'Jurnal berhasil dihapus.');
    }

    public function verify(Request $request, Jurnal $jurnal): RedirectResponse
    {
        abort_if($jurnal->status_verifikasi !== 'Menunggu', 422, 'Jurnal sudah diverifikasi.');

        $data = $request->validate([
            'status' => ['required', 'in:Disetujui,Ditolak'],
            'catatan' => ['nullable', 'string', 'max:5000'],
        ]);

        $jurnal->update(['status_verifikasi' => $data['status']]);
        $jurnal->verifikasiJurnals()->create([
            'verifikator_id' => auth()->id(),
            'status' => $data['status'],
            'catatan' => $data['catatan'] ?? null,
            'verified_at' => now(),
        ]);

        return redirect()->route('jurnal.show', $jurnal)->with('success', 'Verifikasi jurnal berhasil disimpan.');
    }

    private function currentGuru(): Guru
    {
        return Guru::where('user_id', auth()->id())->firstOrFail();
    }

    private function authorizeJournal(Jurnal $jurnal, bool $mustOwn = false): void
    {
        abort_unless(in_array(auth()->user()->role, ['guru', 'admin', 'sekretaris'], true), 403);

        if ($mustOwn || auth()->user()->role === 'guru') {
            abort_unless($jurnal->guru_id === $this->currentGuru()->id, 403);
        }
    }

    private function validatePeriodOrder(array $data): void
    {
        $start = JamPelajaran::findOrFail($data['jam_mulai_id']);
        $end = JamPelajaran::findOrFail($data['jam_selesai_id']);

        abort_if($start->jam_mulai >= $end->jam_selesai, 422, 'Jam selesai harus setelah jam mulai.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
            'mapels' => Mapel::orderBy('nama_mapel')->get(),
            'jamPelajarans' => JamPelajaran::where('is_active', true)->orderBy('jam_ke')->get(),
        ];
    }
}
