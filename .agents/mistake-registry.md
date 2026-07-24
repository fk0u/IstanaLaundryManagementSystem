# Mistake Registry - Istana Laundry Management System

Pendaftaran kesalahan yang pernah terjadi untuk menghindari kesalahan berulang di masa mendatang.

| ID | Tanggal | Deskripsi Kesalahan | Solusi / Tindakan Pencegahan |
|---|---|---|---|
| M1 | 2026-07-24 | Menulis file workspace menggunakan ArtifactMetadata di `write_to_file`. | Parameter `ArtifactMetadata` hanya digunakan untuk file artefak di direktori brain (`<appDataDir>/brain/`). Untuk file workspace biasa, hilangkan parameter ini. |
