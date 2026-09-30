<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Services\PiketJournalReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PiketJurnalController extends Controller
{
    public function index(Request $request, PiketJournalReport $report): View
    {
        $data = $this->reportData($request, $report);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $data['rows'] = new LengthAwarePaginator($data['rows']->forPage($page, 20)->values(), $data['rows']->count(), 20, $page, [
            'path' => $request->url(), 'query' => $request->query(),
        ]);

        return view('laporan.piket-jurnal', $data);
    }

    public function export(Request $request, PiketJournalReport $report): StreamedResponse
    {
        $rows = $this->reportData($request, $report)['rows'];

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kelas', 'Tanggal', 'Hari', 'Jam ke', 'Mulai', 'Selesai', 'Mapel', 'Guru', 'Ketersediaan', 'Verifikasi', 'Materi', 'Tugas']);
            foreach ($rows as $row) {
                $cells = [$row['kelas'], $row['tanggal']->toDateString(), $row['tanggal']->locale('id')->translatedFormat('l'),
                    $row['jam_ke'], $row['mulai'], $row['selesai'], $row['mapel'], $row['guru'], $row['status'],
                    $row['jurnal']?->status_verifikasi, $row['jurnal']?->materi, $row['jurnal']?->tugas];
                fputcsv($handle, array_map(fn ($value) => is_string($value) && preg_match('/^[\t\r\n ]*[=+\-@]/u', $value) === 1 ? "'".$value : $value, $cells));
            }
            fclose($handle);
        }, 'rekap-jurnal.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array{kelas: Collection, selectedClass: ?Kelas, start: Carbon, end: Carbon, rows: Collection} */
    private function reportData(Request $request, PiketJournalReport $report): array
    {
        abort_unless($request->user()->isPiketHariIni(), 403);
        $data = $request->validate([
            'kelas_id' => ['nullable', 'integer', 'exists:kelas,id'],
            'tanggal_mulai' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_selesai' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $start = Carbon::parse($data['tanggal_mulai'] ?? today()->toDateString())->startOfDay();
        $end = Carbon::parse($data['tanggal_selesai'] ?? $start->toDateString())->startOfDay();
        if ($end->lt($start) || $start->diffInDays($end) > 30) {
            throw ValidationException::withMessages(['tanggal_selesai' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai, maksimal 31 hari.']);
        }
        $kelas = Kelas::orderBy('nama_kelas')->get();
        $selectedClass = isset($data['kelas_id']) ? $kelas->firstWhere('id', (int) $data['kelas_id']) : null;

        return [
            'kelas' => $kelas, 'selectedClass' => $selectedClass, 'start' => $start, 'end' => $end,
            'rows' => $selectedClass ? $report->rows($selectedClass, $start, $end) : collect(),
        ];
    }
}
