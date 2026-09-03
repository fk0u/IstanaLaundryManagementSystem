# Mistake Registry - Istana Laundry Management System

Pendaftaran kesalahan yang pernah terjadi untuk menghindari kesalahan berulang di masa mendatang.

| ID | Tanggal | Deskripsi Kesalahan | Solusi / Tindakan Pencegahan |
|---|---|---|---|
| M1 | 2026-07-24 | Menulis file workspace menggunakan ArtifactMetadata di `write_to_file`. | Parameter `ArtifactMetadata` hanya digunakan untuk file artefak di direktori brain (`<appDataDir>/brain/`). Untuk file workspace biasa, hilangkan parameter ini. |
| M2 | 2026-07-24 | Menggunakan `$table->check()` secara langsung dalam migrasi database. | Fungsi `check` tidak selalu tersedia secara bawaan di semua driver database Laravel. Gunakan raw SQL DB statement secara kondisional (`if (config('database.default') === 'mysql')`) agar tetap kompatibel di lingkungan SQLite (dev) dan MySQL (prod). |
| M3 | 2026-07-24 | Nilai kolom ENUM tidak cocok pada test fixture. | Pastikan nilai data mock/test fixture mematuhi batasan constraint ENUM yang tepat sesuai migrasi (seperti `'kilogram'` bukan `'Kiloan'`). |
| M4 | 2026-07-24 | Nama kolom foreign key tidak sesuai skema database. | Selalu periksa schema berkas migrasi sebelum melakukan query insert/update (seperti `updated_by` pada tabel `production_status_logs` bukan `changed_by`). |
| M5 | 2026-09-03 | Menuliskan IP host VPS pada dokumentasi session briefing yang terlacak git. | Jangan pernah menyertakan IP server, password VPS, kredensial SSH, atau password database ke dalam git source code/dokumentasi. Selalu gunakan placeholder lingkungan dan untrack file briefing jika diperlukan. |

