<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJurnalRequest extends FormRequest
{
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
        return [
            'tanggal' => ['required', 'date'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'mapel_id' => ['required', 'exists:mapels,id'],
            'jam_mulai_id' => ['required', 'exists:jam_pelajarans,id'],
            'jam_selesai_id' => ['required', 'exists:jam_pelajarans,id'],
            'status_guru' => ['required', 'in:Hadir,Izin,Sakit,Dinas,Tanpa Keterangan'],
            'materi' => ['required', 'string', 'max:200'],
            'tujuan_pembelajaran' => ['nullable', 'string', 'max:5000'],
            'kegiatan' => ['nullable', 'string', 'max:5000'],
            'tugas' => ['nullable', 'string', 'max:5000', 'required_unless:status_guru,Hadir'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'absensi' => ['nullable', 'array'],
            'absensi.*.siswa_id' => ['required', 'integer', 'distinct', 'exists:siswas,id'],
            'absensi.*.status' => ['required', 'in:H,S,I,A,D'],
            'absensi.*.catatan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
