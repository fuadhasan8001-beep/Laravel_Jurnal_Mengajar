<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolEvent extends Model
{
    protected $fillable = ['event_date', 'title', 'description', 'event_type', 'participant_scope', 'participant_ids', 'activity_start', 'activity_end', 'attendance_enabled', 'attendance_mode', 'once_start', 'once_deadline', 'morning_start', 'morning_deadline', 'evening_start', 'evening_deadline', 'location_mode', 'location_latitude', 'location_longitude', 'location_radius_meters'];

    protected function casts(): array
    {
        return ['event_date' => 'date', 'attendance_enabled' => 'boolean', 'participant_ids' => 'array'];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(SchoolEventAttendanceRecord::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SchoolEventAudit::class);
    }

    public function targetUsers(): Builder
    {
        return match ($this->participant_scope) {
            'guru_tertentu' => User::query()->where('role', 'guru')->where('is_active', true)
                ->whereHas('guru', fn (Builder $query) => $query->whereIn('id', $this->participant_ids ?? [])),
            'semua_siswa' => User::query()->where('role', 'siswa')->where('is_active', true)->whereHas('siswa'),
            'kelas_tertentu' => User::query()->where('role', 'siswa')->where('is_active', true)
                ->whereHas('siswa', fn (Builder $query) => $query->whereIn('kelas_id', $this->participant_ids ?? [])),
            'semua_guru', 'all_guru' => User::query()->where('role', 'guru')->where('is_active', true)->whereHas('guru'),
            default => User::query()->whereRaw('1 = 0'),
        };
    }

    public function isParticipant(User $user): bool
    {
        return $this->targetUsers()->whereKey($user->id)->exists();
    }

    public function overridesScheduledAttendance(): bool
    {
        return in_array($this->attendance_mode, ['morning_evening', 'once', 'none'], true);
    }
}
