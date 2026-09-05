# To-Do List

Aplikasi buat nyatet tugas harian, simpel aja. Full PHP + MySQL, tanpa JavaScript. Tambah - liat - centang - edit - hapus.

## Cara pakai

1. Nyalain MySQL di XAMPP.
2. Import `database.sql` (via phpMyAdmin atau `mysql -u root < database.sql`) — bikin database `junior_web`, tabel `todos`, + 2 data contoh.
3. Taruh folder di `htdocs`, nyalain Apache, buka `http://localhost/junior-web/`.
4. Setting koneksi ada di `config/db.php` ($DB_HOST, $DB_NAME, $DB_USER, $DB_PASS).

## Fitur

- Tambah tugas, centang selesai/belum via tombol status
- Edit + hapus pakai konfirmasi (render server-side)
- Filter Semua / Belum / Selesai + pencarian (dikerjain di SQL)
- Tiap tugas nampilin id, judul, status

## Struktur

```
junior-web/
  index.php       <- tampilan 
  config/db.php   <- koneksi PDO ke MySQL
  database.sql    <- skema + data awal
  README.md
```

Styling pakai Tailwind via CDN, jadi butuh koneksi internet pas dibuka.
