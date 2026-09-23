<?php

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('refuses duplicate NIS while keeping both accounts intact', function () {
    $kelas = \App\Models\Kelas::create(['nama_kelas' => 'X AUDIT', 'tingkat' => 'X']);
    $first = \App\Models\User::factory()->create(['role' => 'siswa']);
    $second = \App\Models\User::factory()->create(['role' => 'siswa']);
    $data = ['kelas_id' => $kelas->id, 'nis' => 'AUDIT-1', 'nama_siswa' => 'Siswa', 'jenis_kelamin' => 'L'];
    \App\Models\Siswa::create([...$data, 'user_id' => $first->id]);
    expect(fn () => \App\Models\Siswa::create([...$data, 'user_id' => $second->id]))
        ->toThrow(\Illuminate\Database\QueryException::class);
    $this->assertDatabaseCount('siswas', 1);
    $this->assertDatabaseCount('users', 2);
});
