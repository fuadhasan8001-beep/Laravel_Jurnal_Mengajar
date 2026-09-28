<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jurnal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WaliKelasJurnalController extends Controller
{
    private function verifiedJournals(Request $request): Builder
    {
        abort_unless($request->user()->role === 'guru' && $request->user()->kelasWali()->exists(), 403);

        $guruId = Guru::where('user_id', $request->user()->id)->value('id');
        abort_if($guruId === null, 403);

        return Jurnal::query()
            ->where(function (Builder $query) use ($guruId, $request): void {
                $query->where('guru_id', $guruId)
                    ->orWhereIn('kelas_id', $request->user()->kelasWali()->select('kelas.id'));
            })
            ->where(function (Builder $query) use ($guruId): void {
                $query->where('guru_id', $guruId)
                    ->orWhere(function (Builder $verified): void {
                        $verified->where('status_verifikasi', 'Disetujui')
                            ->whereHas('verifikasiJurnals', fn (Builder $verification) => $verification
                                ->where('status', 'Disetujui')
                                ->whereHas('verifikator', fn (Builder $user) => $user->where('role', 'sekretaris')));
                    });
            });
    }

    public function index(Request $request): View
    {
        $query = $this->verifiedJournals($request);
        $filters = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'guru' => ['nullable', 'string', 'max:100'],
        ]);
        $tanggal = $filters['tanggal'] ?? null;
        $guru = trim($filters['guru'] ?? '');

        return view('wali-kelas.jurnal', [
            'tanggal' => $tanggal,
            'guru' => $guru,
            'kelasWali' => $request->user()->kelasWali()->orderBy('nama_kelas')->get(),
            'jurnals' => $query->with(['guru', 'kelas', 'mapel', 'jamMulai', 'jamSelesai'])
                ->when($tanggal, fn (Builder $query) => $query->whereDate('tanggal', $tanggal))
                ->when($guru !== '', fn (Builder $query) => $query->whereHas('guru', fn (Builder $teacher) => $teacher->where('nama_guru', 'like', '%'.$guru.'%')))
                ->orderBy('jam_mulai_id')->orderBy('id')->paginate(15)->withQueryString(),
        ]);
    }

    public function show(Request $request, int $jurnal): View
    {
        return view('jurnal.show', [
            'jurnal' => $this->verifiedJournals($request)
                ->with(['guru', 'kelas', 'mapel', 'jamMulai', 'jamSelesai', 'absensis.siswa'])
                ->findOrFail($jurnal),
            'readOnlyWali' => true,
        ]);
    }

    public function signature(Request $request, int $jurnal): StreamedResponse
    {
        $journal = $this->verifiedJournals($request)->findOrFail($jurnal);
        abort_unless($journal->tanda_tangan && Storage::exists($journal->tanda_tangan), 404);

        return Storage::response($journal->tanda_tangan, 'tanda-tangan-jurnal.png', [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="tanda-tangan-jurnal.png"',
        ]);
    }
}
