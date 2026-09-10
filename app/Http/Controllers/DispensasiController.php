<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Dispensasi;
use App\Models\JamPelajaran;
use App\Models\Jurnal;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\DispensasiNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DispensasiController extends Controller
{
    public function index(): View
    {
        $query = Dispensasi::with(['siswa', 'jamMulai', 'jamSelesai'])
            ->latest();

        if (auth()->user()->role === 'siswa') {
            $query->where('siswa_id', $this->student()->id);
        }

        return view('dispensasi.index', [
            'dispensasis' => $query->get(),
        ]);
    }

    public function create(): View
    {
        return view('dispensasi.create', [
            'jamPelajarans' => JamPelajaran::query()
                ->where('is_active', true)
                ->orderBy('jam_ke')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'jam_mulai_id' => ['required', 'exists:jam_pelajarans,id'],
            'jam_selesai_id' => ['required', 'exists:jam_pelajarans,id'],
            'alasan' => ['required', 'string', 'max:5000'],
            'bukti' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $jamMulai = JamPelajaran::findOrFail($data['jam_mulai_id']);
        $jamSelesai = JamPelajaran::findOrFail($data['jam_selesai_id']);

        if ($jamMulai->jam_mulai >= $jamSelesai->jam_selesai) {
            return back()->withInput()->withErrors([
                'jam_selesai_id' => 'Jam selesai harus setelah jam mulai.',
            ]);
        }

        $dispensasi = new Dispensasi($data);
        $dispensasi->siswa_id = $this->student()->id;

        if ($request->hasFile('bukti')) {
            $dispensasi->bukti = $request->file('bukti')->store('dispensasi/bukti');
        }

        $dispensasi->save();

        User::whereIn('role', ['piket'])->where('is_active', true)->get()
            ->each->notify(new DispensasiNotification($dispensasi, 'submitted'));

        return redirect()->route('dispensasi.index')
            ->with('success', 'Pengajuan dispensasi berhasil dikirim.');
    }

    public function show(Dispensasi $dispensasi): View
    {
        $this->authorizeView($dispensasi);

        return view('dispensasi.show', [
            'dispensasi' => $dispensasi->load([
                'siswa',
                'jamMulai',
                'jamSelesai',
                'piket',
                'admin',
            ]),
        ]);
    }

    public function downloadEvidence(Dispensasi $dispensasi): BinaryFileResponse
    {
        $this->authorizeView($dispensasi);

        abort_unless($dispensasi->bukti && Storage::exists($dispensasi->bukti), 404);

        return response()->download(Storage::path($dispensasi->bukti));
    }

    public function verify(Request $request, Dispensasi $dispensasi): RedirectResponse
    {
        abort_if($dispensasi->status_akhir !== 'Menunggu', 422, 'Dispensasi sudah memiliki keputusan akhir.');

        $data = $request->validate([
            'status' => ['required', 'in:Disetujui,Ditolak'],
            'catatan_verifikasi' => ['nullable', 'string', 'max:5000'],
        ]);

        $user = auth()->user();
        $isPiket = $user->role === 'piket';

        if (! $isPiket && $dispensasi->status_piket !== 'Disetujui') {
            abort(422, 'Dispensasi harus disetujui piket terlebih dahulu.');
        }

        if ($isPiket) {
            $dispensasi->status_piket = $data['status'];
            $dispensasi->piket_id = $user->id;
            $dispensasi->verified_piket_at = Carbon::now();
        } else {
            $dispensasi->status_admin = $data['status'];
            $dispensasi->admin_id = $user->id;
            $dispensasi->verified_admin_at = Carbon::now();
        }

        $dispensasi->catatan_verifikasi = $data['catatan_verifikasi'] ?? null;
        $dispensasi->status_akhir = $this->finalStatus($dispensasi);
        $dispensasi->save();

        if ($isPiket) {
            $event = $data['status'] === 'Disetujui' ? 'piket_approved' : 'piket_rejected';
            User::where('role', 'admin')->where('is_active', true)->get()
                ->each->notify(new DispensasiNotification($dispensasi, $event));
        } else {
            $event = $data['status'] === 'Disetujui' ? 'admin_approved' : 'admin_rejected';
            $dispensasi->siswa->user?->notify(new DispensasiNotification($dispensasi, $event));
        }

        if ($dispensasi->status_akhir === 'Disetujui') {
            $this->markAttendanceAsDispensed($dispensasi);
        }

        return redirect()->route('dispensasi.show', $dispensasi)
            ->with('success', 'Verifikasi dispensasi berhasil disimpan.');
    }

    private function student(): Siswa
    {
        return Siswa::where('user_id', auth()->id())->firstOrFail();
    }

    private function authorizeView(Dispensasi $dispensasi): void
    {
        if (auth()->user()->role === 'siswa' && $dispensasi->siswa_id !== $this->student()->id) {
            abort(403);
        }
    }

    private function finalStatus(Dispensasi $dispensasi): string
    {
        if ($dispensasi->status_piket === 'Ditolak' || $dispensasi->status_admin === 'Ditolak') {
            return 'Ditolak';
        }

        if ($dispensasi->status_piket === 'Disetujui' && $dispensasi->status_admin === 'Disetujui') {
            return 'Disetujui';
        }

        return 'Menunggu';
    }

    private function markAttendanceAsDispensed(Dispensasi $dispensasi): void
    {
        $dispensasi->loadMissing(['siswa', 'jamMulai', 'jamSelesai']);

        $start = Carbon::parse($dispensasi->jamMulai->jam_mulai);
        $end = Carbon::parse($dispensasi->jamSelesai->jam_selesai);

        $jurnals = Jurnal::with(['jamMulai', 'jamSelesai'])
            ->where('tanggal', Carbon::parse($dispensasi->getRawOriginal('tanggal'))->toDateString())
            ->where('kelas_id', $dispensasi->siswa->kelas_id)
            ->get()
            ->filter(function (Jurnal $jurnal) use ($start, $end): bool {
                if (! $jurnal->jamMulai || ! $jurnal->jamSelesai) {
                    return false;
                }

                $jurnalStart = Carbon::parse($jurnal->jamMulai->jam_mulai);
                $jurnalEnd = Carbon::parse($jurnal->jamSelesai->jam_selesai);

                return $jurnalStart < $end && $jurnalEnd > $start;
            });

        foreach ($jurnals as $jurnal) {
            Absensi::updateOrCreate(
                ['jurnal_id' => $jurnal->id, 'siswa_id' => $dispensasi->siswa_id],
                [
                    'status' => 'D',
                    'catatan' => 'Dispensasi disetujui.',
                ]
            );
        }
    }
}
