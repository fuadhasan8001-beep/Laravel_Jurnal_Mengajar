<?php

namespace App\Http\Requests;

use App\Models\Guru;
use App\Models\Jadwal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJurnalRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $guru = Guru::where('user_id', $this->user()?->id)->first();
        $active = $guru ? Jadwal::sessionsForGuru($guru, now())->where('active', true) : collect();
        $submittedSchedule = $this->input('jadwal_id');
        $session = $submittedSchedule
            ? $active->firstWhere('id', (int) $submittedSchedule)
            : ($active->count() === 1 ? $active->first() : null);
        $this->merge([
            'tanggal' => today()->toDateString(), 'materi' => $this->input('materi') ?? '',
            'kelas_id' => null, 'mapel_id' => null, 'jam_mulai_id' => null, 'jam_selesai_id' => null,
            'jadwal_id' => null,
        ]);
        if ($session && ($submittedSchedule === null || (string) $submittedSchedule === (string) $session['id'])) {
            $this->merge(collect($session)->only(['kelas_id', 'mapel_id', 'jam_mulai_id', 'jam_selesai_id'])->all());
            $this->merge(['jadwal_id' => $session['id']]);
        }
    }

    public function messages(): array
    {
        return ['jadwal_id.required' => 'Jadwal mengajar sudah berubah atau tidak aktif. Buka kembali halaman tambah jurnal.'];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_active && auth()->user()->role === 'guru';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->journalRules();
    }

    protected function journalRules(): array
    {
        return [
            'jadwal_id' => ['required', 'integer'],
            'tanggal' => ['required', 'date'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mapels,id'],
            'jam_mulai_id' => ['required', 'exists:jam_pelajarans,id'],
            'jam_selesai_id' => ['required', 'exists:jam_pelajarans,id'],
            'status_guru' => ['required', 'in:Hadir,Izin,Sakit,Dinas,Tanpa Keterangan'],
            'materi' => ['nullable', 'string', 'max:200'],
            'tujuan_pembelajaran' => ['nullable', 'string', 'max:5000'],
            'kegiatan' => ['nullable', 'string', 'max:5000'],
            'tugas' => ['nullable', 'string', 'max:5000'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'tanda_tangan' => ['nullable', 'string', 'max:1048576'],
            'absensi' => ['nullable', 'array'],
            'absensi.*.siswa_id' => ['required', 'integer', 'distinct', 'exists:siswas,id'],
            'absensi.*.status' => ['required', 'in:H,S,I,A,D'],
            'absensi.*.catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
