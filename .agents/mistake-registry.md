# Mistake Registry - Istana Laundry Management System

Pendaftaran kesalahan yang pernah terjadi untuk menghindari kesalahan berulang di masa mendatang.

| ID | Tanggal | Deskripsi Kesalahan | Solusi / Tindakan Pencegahan |
|---|---|---|---|
| M1 | 2026-07-24 | Menulis file workspace menggunakan ArtifactMetadata di `write_to_file`. | Parameter `ArtifactMetadata` hanya digunakan untuk file artefak di direktori brain (`<appDataDir>/brain/`). Untuk file workspace biasa, hilangkan parameter ini. |
| M2 | 2026-07-24 | Menggunakan `$table->check()` secara langsung dalam migrasi database. | Fungsi `check` tidak selalu tersedia secara bawaan di semua driver database Laravel. Gunakan raw SQL DB statement secara kondisional (`if (config('database.default') === 'mysql')`) agar tetap kompatibel di lingkungan SQLite (dev) dan MySQL (prod). |
