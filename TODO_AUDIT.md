# TODO Audit Jurnal Mengajar

## Dituntaskan secara lokal

- [x] Audit role dashboard dan data view; tes rendering kelima dashboard dan akses guest/role.
- [x] Batasi akses guru pada jadwal dan jurnal miliknya; cegah akses silang pada jurnal, dispensasi/bukti, notifikasi, profil, dan route role.
- [x] Selaraskan alur dispensasi dengan UI dan controller: siswa mengajukan untuk dirinya sendiri, piket memeriksa, admin menetapkan keputusan akhir. ID siswa kiriman tidak dapat mengganti pemilik pengajuan.
- [x] Kirim notifikasi pengajuan ke piket aktif; terapkan status dispensasi yang disetujui ke absensi yang waktunya bertumpang tindih, termasuk Jumat.
- [x] Cegah konflik jadwal berdasarkan interval waktu, validasi urutan jam, batasi pembuatan jurnal pada jadwal yang sedang berlangsung, dan cegah jurnal ganda pada layer aplikasi.
- [x] Verifikasi integritas skema dari migration: NIS unik; pasangan `jurnal_id + siswa_id` unik; foreign key dan aturan hapus tersedia. Belum membuat constraint unik jurnal karena perlu pemeriksaan data deployment terlebih dahulu.
- [x] Terapkan pagination daftar absensi dan dispensasi; eager-load relasi yang dipakai; ekspor CSV memakai pemrosesan bertahap dan tetap melindungi formula spreadsheet.
- [x] Lengkapi filter status absensi pada tampilan, query, ringkasan, dan CSV; uji filter tabel/export.
- [x] Tambahkan pembatasan login, validasi upload dispensasi berbasis MIME dan ukuran, perlindungan registrasi role admin, dan pengamanan kredensial seeder.
- [x] Jalankan feature test alur jurnal → verifikasi sekretaris → pengajuan siswa → verifikasi piket/admin → status absensi → notifikasi → export.
- [x] Signature jurnal disimpan privat, hanya guru pemilik yang dapat mengubahnya, dan diuji bersama alur edit.
- [x] Activity log tersedia untuk perubahan data guru; log hanya dapat dilihat admin dan memiliki pagination.
- [x] Build aset Vite berhasil; Composer audit bersih; npm audit melaporkan 0 kerentanan (pemeriksaan sebelum rebase).
- [x] Setelah integrasi dengan `origin/DEV`: seluruh suite 158 tes / 558 assertion lulus, Pint lulus, dan `git diff --check` lulus.

## Masih menunggu akses atau keputusan di luar repo

- [ ] **Constraint unik jurnal:** audit duplikasi pada database staging/production untuk `guru_id`, `kelas_id`, `mapel_id`, `jam_mulai_id`, dan `tanggal`; setelah data dibersihkan/terkonfirmasi, tambahkan migrasi unik. Database lokal tidak diperiksa agar data di dalamnya tidak tersentuh.
- [ ] **Uji visual browser:** verifikasi halaman dashboard, jadwal, absensi, form, dropdown, modal, header/notifikasi pada lebar 360, 390, 768, 1366, dan 1920 px. CSS memiliki breakpoint responsif dan tabel memiliki pembungkus overflow, tetapi runtime ini tidak menyediakan browser automation/browser terpasang untuk melihat hasil aktual.
- [ ] **Production/staging:** verifikasi `.env` deployment (`APP_ENV=production`, `APP_DEBUG=false`, secret aman), cache konfigurasi, serta halaman 403/404/419/422/500 pada server. Repo tidak memiliki konfigurasi deployment atau akses server; `.env` lokal tetap tidak diubah dan tidak terlacak Git.
- [ ] **Cakupan activity log:** saat ini log mencakup perubahan data guru; pastikan event bisnis lain yang wajib dicatat (misalnya jurnal, dispensasi, dan login/logout), aturan retensi, serta kebutuhan audit sebelum memperluas cakupannya.
- [ ] **Sinkronisasi database:** `db-sync.sh` tidak ditemukan di repo; perintah `help`, `push`, dan `pull` belum dapat diuji.
