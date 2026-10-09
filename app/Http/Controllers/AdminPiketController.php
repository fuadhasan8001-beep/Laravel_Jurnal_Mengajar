<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPiketController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $filters = $request->validate(['bulan' => ['nullable', 'date_format:Y-m']]);
        $bulan = $filters['bulan'] ?? today()->format('Y-m');
        $monthStart = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth()->toDateString();
        $monthEnd = Carbon::createFromFormat('Y-m', $bulan)->endOfMonth()->toDateString();

        return view('admin.piket.index', [
            'bulan' => $bulan,
            'allGurus' => Guru::with('user')->orderBy('nama_guru')->get(),
            'allWakas' => User::where('role', 'waka')->where('is_active', true)->orderBy('name')->get(),
            'gurus' => Guru::with(['user', 'jadwalPikets' => fn ($query) => $query
                ->whereDate('tanggal', '>=', today())
                ->orderBy('tanggal')])
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('nama_guru', 'like', '%'.$search.'%')
                        ->orWhere('nip', 'like', '%'.$search.'%');
                }))
                ->orderBy('nama_guru')
                ->paginate(20)
                ->withQueryString(),
            'jadwals' => JadwalPiket::with(['guru', 'user'])
                ->whereDate('tanggal', '>=', $monthStart)->whereDate('tanggal', '<=', $monthEnd)
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->whereHas('guru', fn ($query) => $query->where(function ($query) use ($search): void {
                        $query->where('nama_guru', 'like', '%'.$search.'%')->orWhere('nip', 'like', '%'.$search.'%');
                    }))->orWhereHas('user', fn ($query) => $query->where('name', 'like', '%'.$search.'%'));
                }))
                ->orderBy('tanggal')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jenis_tugas' => ['required', 'in:kbm,waka'],
            'guru_id' => ['nullable', 'required_if:jenis_tugas,kbm', 'exists:gurus,id'],
            'user_id' => ['nullable', 'required_if:jenis_tugas,waka', Rule::exists('users', 'id')->where('role', 'waka')->where('is_active', true)],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'shift' => ['nullable', 'required_if:jenis_tugas,kbm', 'in:pagi,siang'],
            'is_koordinator' => ['sometimes', 'boolean'],
        ]);

        $assignee = $data['jenis_tugas'] === 'kbm'
            ? ['guru_id' => $data['guru_id'], 'user_id' => null, 'tanggal' => $data['tanggal'], 'shift' => $data['shift']]
            : ['guru_id' => null, 'user_id' => $data['user_id'], 'tanggal' => $data['tanggal'], 'shift' => JadwalPiket::SHIFT_WAKA];
        $assignment = JadwalPiket::query()
            ->where($data['jenis_tugas'] === 'kbm' ? 'guru_id' : 'user_id', $data['jenis_tugas'] === 'kbm' ? $data['guru_id'] : $data['user_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->where('shift', $data['jenis_tugas'] === 'kbm' ? $data['shift'] : JadwalPiket::SHIFT_WAKA)
            ->first() ?? new JadwalPiket;
        $assignment->fill([...$assignee, 'dibuat_oleh' => $request->user()->id,
            'is_koordinator' => $data['jenis_tugas'] === 'kbm' && $request->boolean('is_koordinator')])->save();

        return back()->with('success', 'Jadwal piket guru berhasil disimpan.');
    }

    public function repeatWakaRoster(Request $request): RedirectResponse
    {
        $data = $request->validate(['bulan' => ['required', 'date_format:Y-m']]);
        $targetMonth = Carbon::createFromFormat('!Y-m', $data['bulan']);
        $sourceMonth = $targetMonth->copy()->subMonthNoOverflow();
        $sourceSchedules = JadwalPiket::where('shift', JadwalPiket::SHIFT_WAKA)
            ->whereBetween('tanggal', [$sourceMonth->copy()->startOfMonth()->toDateString(), $sourceMonth->copy()->endOfMonth()->toDateString()])
            ->orderBy('tanggal')
            ->get();

        if ($sourceSchedules->isEmpty()) {
            return back()->withErrors(['bulan' => 'Tidak ada jadwal Piket Waka pada bulan sebelumnya untuk disalin.']);
        }

        $copied = 0;
        $skipped = 0;
        $targetDates = [];

        DB::transaction(function () use ($request, $sourceSchedules, $targetMonth, &$copied, &$skipped, &$targetDates): void {
            JadwalPiket::where('shift', JadwalPiket::SHIFT_WAKA)
                ->whereBetween('tanggal', [$targetMonth->copy()->startOfMonth()->toDateString(), $targetMonth->copy()->endOfMonth()->toDateString()])
                ->delete();

            foreach ($sourceSchedules as $sourceSchedule) {
                $day = min($sourceSchedule->tanggal->day, $targetMonth->daysInMonth);
                $targetDate = $targetMonth->copy()->day($day)->toDateString();

                if (isset($targetDates[$targetDate])) {
                    $skipped++;

                    continue;
                }

                $targetDates[$targetDate] = true;
                JadwalPiket::create([
                    'guru_id' => null,
                    'user_id' => $sourceSchedule->user_id,
                    'tanggal' => $targetDate,
                    'shift' => JadwalPiket::SHIFT_WAKA,
                    'dibuat_oleh' => $request->user()->id,
                    'is_koordinator' => false,
                ])->save();
                $copied++;
            }
        });

        $message = "$copied jadwal Piket Waka berhasil disalin dari bulan sebelumnya.";
        if ($skipped > 0) {
            $message .= " $skipped jadwal dilewati karena tanggal bertumpuk setelah penyesuaian akhir bulan.";
        }

        return back()->with('success', $message);
    }

    public function update(Request $request, JadwalPiket $jadwalPiket): RedirectResponse
    {
        $fixedDate = $jadwalPiket->tanggal->toDateString();

        $data = $request->validate([
            'jenis_tugas' => ['required', 'in:kbm,waka'],
            'guru_id' => ['nullable', 'required_if:jenis_tugas,kbm', 'exists:gurus,id'],
            'user_id' => ['nullable', 'required_if:jenis_tugas,waka', Rule::exists('users', 'id')->where('role', 'waka')->where('is_active', true)],
            'tanggal' => ['sometimes', 'nullable', 'date', 'after_or_equal:today'],
            'shift' => ['nullable', 'required_if:jenis_tugas,kbm', 'in:pagi,siang'],
            'is_koordinator' => ['sometimes', 'boolean'],
        ]);

        $data['tanggal'] = $fixedDate;

        if ($data['jenis_tugas'] === 'kbm') {
            $duplicate = JadwalPiket::where('guru_id', $data['guru_id'])
                ->whereDate('tanggal', $fixedDate)
                ->where('shift', $data['shift'])
                ->where('id', '!=', $jadwalPiket->id)
                ->exists();
            abort_if($duplicate, 422, 'Guru tersebut sudah dijadwalkan pada shift ini.');

            $jadwalPiket->update([
                'guru_id' => $data['guru_id'], 'user_id' => null,
                'tanggal' => $fixedDate, 'shift' => $data['shift'],
                'is_koordinator' => $request->has('is_koordinator') ? $request->boolean('is_koordinator') : $jadwalPiket->is_koordinator,
            ]);
        } else {
            $duplicate = JadwalPiket::where('user_id', $data['user_id'])
                ->whereDate('tanggal', $fixedDate)
                ->where('shift', JadwalPiket::SHIFT_WAKA)
                ->where('id', '!=', $jadwalPiket->id)
                ->exists();
            abort_if($duplicate, 422, 'Petugas Waka tersebut sudah dijadwalkan pada tanggal ini.');

            $jadwalPiket->update([
                'guru_id' => null, 'user_id' => $data['user_id'],
                'tanggal' => $fixedDate, 'shift' => JadwalPiket::SHIFT_WAKA,
                'is_koordinator' => false,
            ]);
        }

        return back()->with('success', 'Jadwal piket guru berhasil diperbarui.');
    }

    public function destroy(JadwalPiket $jadwalPiket): RedirectResponse
    {
        $jadwalPiket->delete();

        return back()->with('success', 'Jadwal piket berhasil dihapus.');
    }
}
