<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJadwalRequest;
use App\Http\Requests\UpdateJadwalRequest;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Jadwal::with(['guru', 'kelas', 'mapel', 'jamPelajaran'])->latest();

        if (auth()->user()->role === 'sekretaris') {
            $query->whereIn('kelas_id', auth()->user()->kelasSekretaris()->select('kelas.id'));
        }

        if (auth()->user()->role === 'guru') {
            $query->where('guru_id', Guru::where('user_id', auth()->id())->value('id'));
        }

        $query
            ->when($request->filled('hari'), fn ($builder) => $builder->where('hari', $request->string('hari')))
            ->when($request->filled('guru_id'), fn ($builder) => $builder->where('guru_id', $request->integer('guru_id')))
            ->when($request->filled('kelas_id'), fn ($builder) => $builder->where('kelas_id', $request->integer('kelas_id')));

        return view('jadwal.index', [
            'jadwals' => $query->paginate(15)->withQueryString(),
            ...$this->formData(),
        ]);
    }

    public function create(): View
    {
        return view('jadwal.create', $this->formData());
    }

    public function store(StoreJadwalRequest $request): RedirectResponse
    {
        $data = [...$request->validated(), 'is_active' => $request->boolean('is_active')];
        $this->ensureNoConflict($data);
        Jadwal::create($data);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil dibuat.');
    }

    public function show(Jadwal $jadwal): View
    {
        if (auth()->user()->role === 'sekretaris') {
            abort_unless(auth()->user()->kelasSekretaris()->whereKey($jadwal->kelas_id)->exists(), 403);
        }
        if (auth()->user()->role === 'guru') {
            abort_unless($jadwal->guru_id === Guru::where('user_id', auth()->id())->value('id'), 403);
        }

        return view('jadwal.show', ['jadwal' => $jadwal->load(['guru', 'kelas', 'mapel', 'jamPelajaran'])]);
    }

    public function edit(Jadwal $jadwal): View
    {
        return view('jadwal.edit', [
            ...$this->formData(),
            'jadwal' => $jadwal,
        ]);
    }

    public function update(UpdateJadwalRequest $request, Jadwal $jadwal): RedirectResponse
    {
        $data = [...$request->validated(), 'is_active' => $request->boolean('is_active')];
        $this->ensureNoConflict($data, $jadwal);
        $jadwal->update($data);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal): RedirectResponse
    {
        $jadwal->delete();

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function ensureNoConflict(array $data, ?Jadwal $jadwal = null): void
    {
        if (! $data['is_active']) {
            return;
        }

        $period = JamPelajaran::findOrFail($data['jam_pelajaran_id']);
        [$start] = $period->timesForDay($data['hari']);
        [, $end] = $period->timesForDay($data['hari']);

        $conflictExists = Jadwal::with('jamPelajaran')
            ->where('hari', $data['hari'])
            ->where('is_active', true)
            ->when($jadwal, fn ($builder) => $builder->where('id', '!=', $jadwal->id))
            ->where(function ($builder) use ($data): void {
                $builder->where('guru_id', $data['guru_id'])
                    ->orWhere('kelas_id', $data['kelas_id']);
            })
            ->get()
            ->contains(function (Jadwal $existing) use ($data, $start, $end): bool {
                [$existingStart, $existingEnd] = $existing->jamPelajaran->timesForDay($data['hari']);

                return $start < $existingEnd && $end > $existingStart;
            });

        if ($conflictExists) {
            throw ValidationException::withMessages([
                'jam_pelajaran_id' => 'Guru atau kelas sudah memiliki jadwal yang waktunya bertabrakan.',
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        $guru = auth()->user()->role === 'guru'
            ? Guru::where('user_id', auth()->id())->first()
            : null;
        $guruSchedule = Jadwal::query()->where('guru_id', $guru?->id);

        return [
            'gurus' => $guru ? collect([$guru]) : Guru::orderBy('nama_guru')->get(),
            'kelas' => $guru
                ? Kelas::whereIn('id', (clone $guruSchedule)->select('kelas_id')->distinct())->orderBy('nama_kelas')->get()
                : Kelas::orderBy('nama_kelas')->get(),
            'mapels' => $guru
                ? Mapel::whereIn('id', (clone $guruSchedule)->select('mapel_id')->distinct())->orderBy('nama_mapel')->get()
                : Mapel::orderBy('nama_mapel')->get(),
            'jamPelajarans' => JamPelajaran::where('is_active', true)->orderBy('jam_ke')->get(),
        ];
    }
}
