<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function reviewedRegistrationRequests(): HasMany
    {
        return $this->hasMany(RegistrationRequest::class, 'reviewed_by');
    }

    public function kelasSekretaris(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'sekretaris_kelas');
    }

    public function kelasWali(): HasManyThrough
    {
        return $this->hasManyThrough(Kelas::class, Guru::class, 'user_id', 'wali_kelas_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isPiketHariIni(): bool
    {
        if ($this->isMaster() && session('master_bypass_enabled', false)) {
            return true;
        }

        if (! $this->is_active) {
            return false;
        }
        if ($this->role === 'piket') {
            return true;
        }

        if ($this->role !== 'guru') {
            return false;
        }

        $guruId = Guru::where('user_id', $this->id)->value('id');

        return $guruId !== null && JadwalPiket::where('guru_id', $guruId)->whereDate('tanggal', today())->exists();
    }

    public function isMaster(): bool
    {
        return is_file(config_path('master.php'))
            && $this->username === config('master.username')
            && $this->role === 'admin';
    }
}
