<?php

namespace App\Console\Commands;

use App\Models\Guru;
use App\Models\JadwalPiket;
use App\Models\User;
use App\Support\SeptemberPiketRoster;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:import-september-piket-roster {--apply : Synchronize September 2026 after validating all names}')]
#[Description('Validate or import the dated September 2026 PDF duty roster')]
class ImportSeptemberPiketRoster extends Command
{
    public function handle(): int
    {
        $gurus = Guru::with('user')->get()->groupBy(fn (Guru $guru): string => SeptemberPiketRoster::nameKey($guru->nama_guru));
        $wakas = User::where('role', 'waka')->get()->groupBy(fn (User $user): string => SeptemberPiketRoster::nameKey($user->name));
        $rows = [];
        $mapping = [];
        $errors = [];

        foreach (SeptemberPiketRoster::groups() as $group) {
            foreach (['pagi', 'siang', 'waka'] as $shift) {
                $names = $shift === 'waka' ? [$group['waka']] : $group[$shift];
                foreach ($names as $position => $name) {
                    $key = SeptemberPiketRoster::nameKey(SeptemberPiketRoster::canonicalName($name));
                    $matches = ($shift === 'waka' ? $wakas : $gurus)->get($key, collect());
                    if ($matches->count() !== 1) {
                        $errors[$name] = "$name: {$matches->count()} kecocokan; impor dibatalkan.";

                        continue;
                    }
                    $person = $matches->first();
                    $account = $shift === 'waka' ? $person : $person->user;
                    if (! $account?->is_active || $account->role !== ($shift === 'waka' ? 'waka' : 'guru')) {
                        $errors[$name] = "$name: akun aktif dengan role yang sesuai tidak ditemukan.";

                        continue;
                    }
                    $mapping[$shift === 'waka' ? 'waka:'.$name : $name] = [$name, $person->nama_guru ?? $person->name, $person->id];
                    foreach ($group['dates'] as $day) {
                        $rows[] = [
                            'guru_id' => $shift === 'waka' ? null : $person->id,
                            'user_id' => $shift === 'waka' ? $person->id : null,
                            'tanggal' => sprintf('2026-09-%02d', $day),
                            'shift' => $shift,
                            'is_koordinator' => $shift !== 'waka' && $position === 3,
                        ];
                    }
                }
            }
        }

        $this->table(['Nama PDF', 'Nama database', 'ID'], array_values($mapping));
        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->info(count($mapping).' nama cocok; 22 tanggal, 176 petugas KBM (44 koordinator), 22 Piket Waka.');
        if (! $this->option('apply')) {
            $this->info('Validasi saja, tidak ada data diubah. Gunakan --apply setelah backup untuk menyinkronkan September.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($rows): void {
            $keepIds = [];
            foreach ($rows as $row) {
                $assignment = JadwalPiket::where('guru_id', $row['guru_id'])->where('user_id', $row['user_id'])
                    ->whereDate('tanggal', $row['tanggal'])->where('shift', $row['shift'])->first() ?? new JadwalPiket;
                $assignment->fill($row)->save();
                $keepIds[] = $assignment->id;
            }

            // Replace only obsolete September assignments, including the old all-morning import.
            JadwalPiket::whereDate('tanggal', '>=', '2026-09-01')->whereDate('tanggal', '<=', '2026-09-30')
                ->whereNotIn('id', $keepIds)->delete();
        });
        $this->info('198 penugasan September tersinkron. Jadwal bulan lain dan data jurnal tidak diubah.');

        return self::SUCCESS;
    }
}
