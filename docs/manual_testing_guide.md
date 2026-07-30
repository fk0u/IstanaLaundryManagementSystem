# Panduan Pengujian Manual & End-to-End UAT (Istana Laundry Management System)

> **Dokumen:** Comprehensive Manual UAT Testing Guide  
> **Versi:** 2.0 · **Dipublikasikan:** 30 Juli 2026  
> **Stack:** Laravel 13, Docker, PHP 8.4-FPM, MySQL 8, Sanctum, Spatie Permission  
> **Environment:** `http://localhost:8000`

---

## 📋 Daftar Isi
1. [Kredensial Akun Pengujian (Seeded Accounts)](#1-kredensial-akun-pengujian-seeded-accounts)
2. [Modul 1: Point of Sale (POS) & Billing](#modul-1-point-of-sale-pos--billing)
3. [Modul 2: Tracking Produksi Workshop & QR Code](#modul-2-tracking-produksi-workshop--qr-code)
4. [Modul 3: CRM & Loyalty Pelanggan](#modul-3-crm--loyalty-pelanggan)
5. [Modul 4: Laporan Keuangan & Ekspor Data](#modul-4-laporan-keuangan--ekspor-data)
6. [Modul 5: HR & Payroll Konsolidasi Global](#modul-5-hr--payroll-konsolidasi-global)
7. [Modul 6: Sinkronisasi Akun Staf & HR Karyawan](#modul-6-sinkronisasi-akun-staf--hr-karyawan)
8. [Modul 7: Manajemen Cabang & Scope Operasional](#modul-7-manajemen-cabang--scope-operasional)
9. [Modul 8: Manajemen Aset Tetap & Depresiasi](#modul-8-manajemen-aset-tetap--depresiasi)
10. [Modul 9: Inventori & Pengadaan (PR -> PO -> GRN)](#modul-9-inventori--pengadaan-pr---po---grn)
11. [Modul 10: Refund & Pembatalan Order 4-Tahap](#modul-10-refund--pembatalan-order-4-tahap)
12. [Modul 11: Pemantauan Kinerja & Analytics](#modul-11-pemantauan-kinerja--analytics)
13. [Modul 12: Pelacakan Nota Publik & Notifikasi WhatsApp](#modul-12-pelacakan-nota-publik--notifikasi-whatsapp)

---

## 1. Kredensial Akun Pengujian (Seeded Accounts)

Gunakan akun terdaftar berikut untuk menguji masing-masing peran (role) pada sistem:

| Peran (Role) | Email | Password | Hak Akses Utama |
|---|---|---|---|
| **Owner / Developer** | `owner@istanalaundry.com` | `password` | Akses penuh ke seluruh modul, switch cabang global, & finalisasi refund |
| **Finance** | `finance@istanalaundry.com` | `password` | Jurnal, COA, Laporan Keuangan, Payroll, Aset Tetap, & Approval Refund Stage 2 |
| **Branch Admin** | `admin.smd01@istanalaundry.com` | `password` | Manajemen cabang lokal, approval PR/PO, approval Refund Stage 1 |
| **Cashier** | `cashier.smd01@istanalaundry.com` | `password` | POS billing, pendaftaran pelanggan, cetak struk, pengajuan refund |
| **Workshop Admin / Staff** | `workshop.smd01@istanalaundry.com` | `password` | Antrean produksi 8-stasiun, update status laundry, scan QR |

---

## Modul 1: Point of Sale (POS) & Billing

### Skenario 1.1: Pembuatan Order & Input Kupon Promo Manual
- **Langkah-Langkah**:
  1. Login sebagai Kasir (`cashier.smd01@istanalaundry.com`).
  2. Akses menu **Point of Sale** (`/pos`).
  3. Pilih atau cari nama pelanggan (contoh: *Budi Santoso*).
  4. Tambahkan beberapa layanan cuci (misal: *Cuci Komplit Reguler* 5 Kg & *Dry Clean Jas* 2 Pcs).
  5. Pada bagian promo, ketik kode kupon manual: `PROMO50` atau `MERDEKA80` lalu tekan **Terapkan**.
  6. Masukkan nominal pembayaran tunai dan klik **Proses Bayar & Cetak Nota**.
- **Kriteria Keberhasilan**:
  - Kupon terverifikasi dan memotong subtotal sesuai rule promo.
  - Nota berhasil dibuat dengan nomor order unik (contoh: `ORD-SMD01-20260730-0001`).
  - Struk thermal 58mm/80mm atau A4 Invoice tampil siap dicetak.
  - Jurnal transaksi otomatis terposting ke Sistem Akuntansi tanpa error.

---

## Modul 2: Tracking Produksi Workshop & QR Code

### Skenario 2.1: Pencarian Order & Transisi Status Produksi Linear
- **Langkah-Langkah**:
  1. Login sebagai Staf Workshop (`workshop.smd01@istanalaundry.com`).
  2. Buka menu **Production** (`/production`).
  3. Gunakan bar pencarian **"Cari nomor order / pelanggan..."** untuk menemukan order specific.
  4. Gunakan toggle **"Sembunyikan Order Selesai (SIAP/DIAMBIL)"** untuk menyaring tampilan.
  5. Lakukan transisi status bertahap: `TERIMA` $\rightarrow$ `PILAH` $\rightarrow$ `CUCI` $\rightarrow$ `KERING` $\rightarrow$ `LIPAT` $\rightarrow$ `CEK` $\rightarrow$ `SIAP`.
- **Kriteria Keberhasilan**:
  - Pencarian memfilter daftar order secara instan.
  - Transisi status berjalan linear (tidak bisa lompat status tanpa otorisasi).
  - Setiap transisi terekam pada `production_status_logs` lengkap dengan nama updater dan timestamp WITA.

---

## Modul 3: CRM & Loyalty Pelanggan

### Skenario 3.1: Wawasan Pelanggan & Riwayat Transaksi
- **Langkah-Langkah**:
  1. Buka menu **CRM & Loyalty** (`/customers`).
  2. Perhatikan kartu statistik pelanggan: **Total Transaksi**, **Total Belanja**, dan **Transaksi Terakhir**.
  3. Klik tombol **Riwayat Nota** pada salah satu pelanggan.
  4. Klik ikon **WhatsApp** untuk mengirimkan pesan sapaan/follow-up langsung ke nomor WA pelanggan.
- **Kriteria Keberhasilan**:
  - Modal riwayat menampilkan 10 transaksi terakhir pelanggan secara detail.
  - Link WhatsApp mengarahkan ke `https://wa.me/62...` dengan teks salam terformat.
  - Poin loyalitas dan tier (*Bronze*, *Silver*, *Gold*, *Platinum*) terhitung otomatis.

---

## Modul 4: Laporan Keuangan & Ekspor Data

### Skenario 4.1: Grafik Visual & Ekspor CSV UTF-8 BOM
- **Langkah-Langkah**:
  1. Login sebagai Finance / Owner.
  2. Buka menu **Laporan Keuangan** (`/finance/reports`).
  3. Buka masing-masing tab: **Ringkasan Analytics**, **Laba Rugi**, **Neraca**, dan **Neraca Saldo (Trial Balance)**.
  4. Amati grafik visual Chart.js di setiap tab.
  5. Klik tombol **Ekspor CSV Laporan Keuangan**.
- **Kriteria Keberhasilan**:
  - Grafik komposisi & trend terisi data dinamis.
  - Berkas CSV terunduh dengan format UTF-8 BOM (karakter mata uang Rp & angka tidak berantakan saat dibuka di Microsoft Excel).

---

## Modul 5: HR & Payroll Konsolidasi Global

### Skenario 5.1: Generate Payroll Global & Penguncian Status FINAL
- **Langkah-Langkah**:
  1. Buka menu **HR & Payroll** (`/hr`).
  2. Klik **Generate Payroll Periode**.
  3. Pada pilihan cabang target, pilih `🌟 Konsolidasi Seluruh Cabang (Semua Karyawan)`.
  4. Setelah payroll terbuat, periksa daftar karyawan (gaji pokok, insentif workload, tunjangan transport, BPJS Kesehatan & Ketenagakerjaan).
  5. Klik tombol **Finalkan Payroll & Kunci** pada halaman detail payroll.
- **Kriteria Keberhasilan**:
  - Seluruh karyawan dari semua cabang terproses dalam 1 batch payroll global.
  - Nominal gaji bersifat non-zero mencerminkan gaji pokok & komponen.
  - Setelah difinalkan, status berubah menjadi `FINAL (DIKUNCI)` dan formulir edit komponen otomatis terkunci.

---

## Modul 6: Sinkronisasi Akun Staf & HR Karyawan

### Skenario 6.1: Otomatisasi Penautan User <-> Employee
- **Langkah-Langkah**:
  1. Buka menu **Manajemen Staf** (`/users`).
  2. Tambahkan akun pengguna staf baru (misal: Role *Cashier* di Cabang *Lambung Mangkurat*).
  3. Periksa tabel pengguna staf.
- **Kriteria Keberhasilan**:
  - Badge **Linked HR: NIK-STF-XXXX** warna biru tampil otomatis di samping nama user.
  - Record karyawan baru otomatis terbuat pada modul HR dengan jabatan *"Kasir Utama"* dan gaji pokok standar.

---

## Modul 7: Manajemen Cabang & Scope Operasional

### Skenario 7.1: Pengelolaan Cabang & Indikator Scope
- **Langkah-Langkah**:
  1. Buka menu **Manajemen Cabang** (`/branches`).
  2. Uji tombol **Tambah Cabang Baru** (masukkan Kode Cabang, Nama, Alamat, Telepon, Email).
  3. Uji tombol **Edit Cabang** dan tombol **Aktifkan / Nonaktifkan Cabang**.
- **Kriteria Keberhasilan**:
  - Cabang baru berhasil disimpan dan muncul pada dropdown switcher cabang di header atas.
  - Metrik statistik per cabang (Jumlah Staf, Jumlah Akun Login, Total Volume Nota) terhitung akurat.

---

## Modul 8: Manajemen Aset Tetap & Depresiasi

### Skenario 8.1: Registrasi Aset & Depresiasi Garis Lurus
- **Langkah-Langkah**:
  1. Buka menu **Aset Tetap** (`/assets`).
  2. Daftarkan aset baru (contoh: *Mesin Cuci Primus 15Kg*, Nilai Perolehan Rp 15.000.000, Umur Manfaat 48 bulan).
  3. Buka halaman detail aset tersebut (`/assets/{id}`).
- **Kriteria Keberhasilan**:
  - Tabel jadwal depresiasi bulanan terbuat dari bulan ke-1 hingga bulan ke-48.
  - Tanggal maintenance terakhir dan lokasi cabang penempatan tampil dengan jelas.

---

## Modul 9: Inventori & Pengadaan (PR -> PO -> GRN)

### Skenario 9.1: Siklus Pengadaan Bahan Kimia & Potongan Stok FIFO
- **Langkah-Langkah**:
  1. Buat **Purchase Request (PR)** untuk bahan kimia *Detergen Liquid*.
  2. Setujui PR menjadi **Purchase Order (PO)**.
  3. Konfirmasi **Goods Received Note (GRN)** saat barang fisik tiba.
- **Kriteria Keberhasilan**:
  - Stok inventori bertambah sesuai batch GRN.
  - Jurnal persediaan & hutang usaha otomatis terposting ke sistem akuntansi.
  - Pemakaian stok pada produksi menggunakan metode FIFO (First-In, First-Out).

---

## Modul 10: Refund & Pembatalan Order 4-Tahap

### Skenario 10.1: Alur Persetujuan Refund Berjenjang
- **Langkah-Langkah**:
  1. **Kasir**: Pengajuan refund pada order lunas via menu `/refunds`. (Status: `pending`).
  2. **Branch Admin**: Akses `/refunds` dan klik **Setujui**. (Status: `branch_approved`).
  3. **Finance**: Akses `/refunds` dan klik **Setujui**. (Status: `finance_approved`).
  4. **Owner**: Akses `/refunds` dan klik **Finalkan Refund**. (Status: `completed`).
- **Kriteria Keberhasilan**:
  - Status order berubah menjadi `refunded`.
  - Jurnal pembalik (*reversal journal*) otomatis tercatat di modul Akuntansi.
  - Poin loyalitas pelanggan terpotong secara proporsional.

---

## Modul 11: Pemantauan Kinerja & Analytics

### Skenario 11.1: Leaderboard Kasir & Produktivitas Workshop
- **Langkah-Langkah**:
  1. Akses menu **Kinerja** (`/performance`).
  2. Atur filter rentang tanggal (misal: 1 bulan terakhir).
- **Kriteria Keberhasilan**:
  - Tabel leaderboard kasir menampilkan urutan omset & jumlah nota yang diproses.
  - Tabel produktivitas workshop menampilkan total order diselesaikan per operator.

---

## Modul 12: Pelacakan Nota Publik & Notifikasi WhatsApp

### Skenario 12.1: Public Order Tracking & Pesan Siap Diambil
- **Langkah-Langkah**:
  1. Buka halaman pelacakan publik tanpa login: `http://localhost:8000/track?order_number=ORD-SMD01-20260730-0001`.
  2. Pada halaman detail order kasir, klik tombol **WhatsApp Siap Diambil**.
- **Kriteria Keberhasilan**:
  - Halaman pelacakan publik menampilkan status produksi real-time dengan PII pelanggan terenkripsi/termasking.
  - Tautan WhatsApp membuka aplikasi WA dengan pesan siap ambil dan hyperlink pelacakan terformat.

---

> **Kesimpulan**: Seluruh skenario pengujian di atas telah diverifikasi dan berjalan 100% aman pada lingkungan Docker Compose (`PHP 8.4-FPM` + `MySQL 8.0` + `Nginx`).
