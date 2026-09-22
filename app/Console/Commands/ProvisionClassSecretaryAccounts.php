<?php

namespace App\Console\Commands;

use App\Models\Kelas;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Signature('app:provision-class-secretary-accounts {--reset-passwords : Generate a new password for every account} {--output= : Write credentials to a private local CSV file}')]
#[Description('Create one secretary account for every class with a schedule')]
class ProvisionClassSecretaryAccounts extends Command
{
    public function handle(): int
    {
        $classes = Kelas::whereHas('jadwals')->orderBy('nama_kelas')->get();
        $resetPasswords = (bool) $this->option('reset-passwords');
        $credentialRows = [['Kelas', 'Username', 'Password']];

        if ($classes->isEmpty()) {
            $this->warn('Tidak ada kelas yang memiliki jadwal.');

            return self::SUCCESS;
        }

        $this->info('Kredensial akun pengurus kelas (simpan sekarang, password tidak ditampilkan lagi):');

        foreach ($classes as $kelas) {
            $slug = Str::slug($kelas->nama_kelas);
            $email = "pengurus.{$slug}.{$kelas->id}@sekolah.local";
            $username = "pengurus.{$slug}.{$kelas->id}";
            $password = $this->classPassword($kelas->nama_kelas);
            $created = false;

            DB::transaction(function () use ($kelas, $email, $username, $password, $resetPasswords, &$created): void {
                $user = User::where('email', $email)->first();

                if (! $user) {
                    $user = User::create([
                        'name' => 'Pengurus '.$kelas->nama_kelas,
                        'username' => $username,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'role' => 'sekretaris',
                        'is_active' => true,
                    ]);
                    $created = true;
                } elseif ($resetPasswords) {
                    $user->update(['password' => Hash::make($password)]);
                }

                abort_unless($user->role === 'sekretaris', 422, "Email {$email} sudah dipakai role lain.");
                $user->kelasSekretaris()->syncWithoutDetaching([$kelas->id]);
            });

            $this->line($created
                ? "{$kelas->nama_kelas}: {$username} / {$password}"
                : ($resetPasswords
                    ? "{$kelas->nama_kelas}: {$username} / {$password}"
                    : "{$kelas->nama_kelas}: {$username} (sudah ada, password tidak diubah)"));

            if ($created || $resetPasswords) {
                $credentialRows[] = [$kelas->nama_kelas, $username, $password];
            }
        }

        if ($output = $this->option('output')) {
            $handle = fopen('php://temp', 'r+');
            foreach ($credentialRows as $row) {
                fputcsv($handle, $row);
            }
            rewind($handle);
            Storage::disk('local')->put($output, stream_get_contents($handle));
            fclose($handle);
            $this->info('File kredensial privat: '.Storage::disk('local')->path($output));
        }

        $this->info("{$classes->count()} kelas diproses.");

        return self::SUCCESS;
    }

    private function classPassword(string $className): string
    {
        return 'Jurnal-'.Str::upper(Str::slug($className, '')).'-'.now()->year;
    }
}
