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
- [x] Setelah audit lanjutan: seluruh suite 171 tes / 606 assertion lulus; Pint, lint PHP (97 file), `optimize:clear`, dan `git diff --check` lulus.

## Masih menunggu akses atau keputusan di luar repo

- [ ] **Constraint unik jurnal:** audit duplikasi pada database staging/production untuk `guru_id`, `kelas_id`, `mapel_id`, `jam_mulai_id`, dan `tanggal`; setelah data dibersihkan/terkonfirmasi, tambahkan migrasi unik. Database lokal tidak diperiksa agar data di dalamnya tidak tersentuh.
- [ ] **Uji visual browser:** verifikasi halaman dashboard, jadwal, absensi, form, dropdown, modal, header/notifikasi pada lebar 360, 390, 768, 1366, dan 1920 px. CSS memiliki breakpoint responsif dan tabel memiliki pembungkus overflow, tetapi runtime ini tidak menyediakan browser automation/browser terpasang untuk melihat hasil aktual.
- [ ] **Production/staging:** verifikasi `.env` deployment (`APP_ENV=production`, `APP_DEBUG=false`, secret aman), cache konfigurasi, serta halaman 403/404/419/422/500 pada server. Repo tidak memiliki konfigurasi deployment atau akses server; `.env` lokal tetap tidak diubah dan tidak terlacak Git.
- [ ] **Cakupan activity log:** saat ini log mencakup perubahan data guru; pastikan event bisnis lain yang wajib dicatat (misalnya jurnal, dispensasi, dan login/logout), aturan retensi, serta kebutuhan audit sebelum memperluas cakupannya.
- [ ] **Sinkronisasi database:** `db-sync.sh` tidak ditemukan di repo; perintah `help`, `push`, dan `pull` belum dapat diuji.

## Audit lanjutan: 26 temuan

- [x] **#1 ALREADY FIXED:** route, controller, form, dan test konsisten mendukung siswa mengajukan dispensasi sendiri; piket dapat mengajukan untuk beberapa siswa.
- [x] **#2 ALREADY FIXED:** NIS memiliki unique constraint, validasi pembuatan siswa, dan login berbasis NIS diuji.
- [x] **#3 FIXED:** dashboard mengambil jadwal aktif dari `Jadwal::sessionsForGuru()` yang memakai `timesForDay()`, termasuk Jumat.
- [x] **#4 ALREADY FIXED:** validasi urutan periode jurnal sudah menggunakan tanggal/hari jurnal dan waktu Jumat.
- [x] **#5 FIXED:** master jam mendukung input, edit, display, validasi urutan dan overlap waktu Jumat opsional.
- [x] **#6 FIXED:** edit jadwal tidak menggeser jam global; perubahan waktu dilakukan melalui master jam.
- [x] **#7 FIXED BY DESIGN:** satu jurnal dibuat untuk sesi jadwal aktif (termasuk beberapa jam berurutan); status Izin/Sakit menerima data sesi yang sama dan materi boleh kosong.
- [x] **#8 FIXED:** guru dibatasi pada jurnalnya, sekretaris pada kelas penugasan; piket tidak mendapat akses tulis/rekap absensi umum.
- [x] **#9 FIXED SAFELY:** tidak ada kode aplikasi yang memakai `alokasi_jam_pelajarans`; migration baru menghapus tabel hanya bila kosong dan sengaja gagal tanpa menghapus data bila tabel masih berisi baris. Kedua kondisi diuji. SQL dump mengikuti skema setelah migration.
- [x] **#10 ALREADY FIXED:** urutan waktu divalidasi memakai waktu aktual hari terkait; tes mencakup sesi satu jam, multi-jam, konflik, dan Jumat.
- [x] **#11 ALREADY FIXED IN ACTIVE FLOW:** form/request hanya menerima Hadir, Izin, Sakit. Nilai lama Dinas/Tanpa Keterangan dipertahankan dalam migration historis untuk kompatibilitas data.
- [x] **#12 FIXED:** notifikasi guru dibatasi overlap jadwal/jurnal dan pesan menampilkan waktu lokal Jumat bila berlaku.
- [x] **#13 FIXED:** perubahan status, absensi, dan notifikasi database berjalan dalam transaksi; notifikasi antrean dikirim setelah commit.
- [x] **#14 FIXED:** file bukti dihapus bila transaksi penyimpanan gagal; tes memverifikasi cleanup.
- [x] **#15 FIXED:** SQL dump kini memuat signature, witness absensi, activity log, dan alokasi jam; dump berhasil dimuat pada SQLite in-memory.
- [x] **#16 FIXED:** halaman pemulihan akun menyebut bantuan admin dan menjelaskan bahwa reset tidak otomatis.
- [x] **#17 ALREADY FIXED:** password seeder berasal dari konfigurasi lingkungan; password contoh hanya dipakai test.
- [x] **#18 ALREADY FIXED:** daftar dispensasi menggunakan pagination.
- [x] **#19 ALREADY FIXED:** rekap absensi menggunakan pagination dan eager loading.
- [x] **#20 FIXED:** dashboard lima role dipindahkan dari closure route ke `DashboardController`.
- [x] **#21 FIXED:** perhitungan jadwal aktif dashboard menggunakan sumber sesi jadwal yang sama dengan flow jurnal.
- [x] **#22 ALREADY FIXED:** jumlah siswa terpantau menghitung kelas unik dari seluruh jadwal aktif guru; regression test menegaskan cakupan ini.
- [x] **#23 ALREADY FIXED BY REQUIREMENT:** tanggal pengajuan dipatok ke hari ini karena flow dispensasi mencatat kejadian hari berjalan; regression test memastikan tanggal kiriman diabaikan.
- [x] **#24 VERIFIED:** lint PHP dijalankan pada file aplikasi, route, migration, dan test.
- [x] **#25 ALREADY FIXED:** unique index `jurnal_id + siswa_id` dipertahankan dan diuji langsung.
- [x] **#26 ALREADY FIXED:** assignment sekretaris diperiksa saat melihat, memverifikasi jurnal, dan mengubah absensi; regression test akses kelas lain tersedia.
