# To-Do List

Aplikasi buat nyatet tugas harian, simpel aja. Tambah - liat - centang - edit - hapus. Tanpa database, data kesimpen di browser (localStorage).

## Cara pakai

1. Taruh folder di `htdocs`, nyalain Apache, buka `http://localhost/junior-web/`.
2. Gak perlu MySQL / import SQL, gak perlu setting config.
3. Pas pertama dibuka langsung ada 3 tugas contoh (dummy).

## Fitur

- Tambah tugas, centang selesai/belum via checkbox
- Edit inline (klik Edit, langsung ketik di kartunya, Enter simpan / Esc batal)
- Hapus pakai konfirmasi
- Filter Semua / Belum / Selesai + pencarian realtime
- Tiap tugas nampilin id, judul, status

## Struktur

```
junior-web/
  index.php      <- satu file: tampilan (PHP + HTML + Tailwind) + logika (JS inline)
  README.md
```

Styling pakai Tailwind via CDN, jadi butuh koneksi internet pas dibuka.
