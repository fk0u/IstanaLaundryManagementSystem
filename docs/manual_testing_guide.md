# Panduan Pengujian Manual & Dokumentasi Fitur (8 Area Sistem Istana Laundry ERP)

## Status Environment & Docker Container
Sistem berjalan di container Docker dengan status **Healthy & Active**:
- **Nginx Web Server**: `http://localhost:8000`
- **Database MySQL 8.0**: Port `3306` (Database: `istana_laundry`)
- **PHP 8.4-FPM**: Container `app` aktif

---

## Modul & Prosedur Pengujian Manual

### 1. POS (Point of Sale) — Input Manual Kode Kupon
- **Tujuan**: Memastikan kasir dapat memilih promo aktif dari dropdown ATAU mengetik kode kupon secara manual.
- **Prosedur Pengujian**:
  1. Login sebagai Kasir (`cashier@istanalaundry.com`) / Admin (`owner@istanalaundry.com`).
  2. Buka menu **POS** (`/pos`).
  3. Pilih Pelanggan (misal *Budi Santoso*) dan tambahkan beberapa layanan cuci hingga subtotal melebihi Rp 50.000.
  4. Ketik kode kupon pada kotak **"Masukkan Kode Kupon..."** (contoh: `PROMO50` atau `DISKON10`) lalu tekan **Enter** atau tombol **Terapkan**.
  5. **Hasil yang Diharapkan**:
     - Jika kode valid & memenuhi minimum transaksi, pesan hijau `"Kupon X berhasil diterapkan!"` muncul dan diskon langsung terpotong.
     - Jika subtotal kurang dari minimum transaksi atau kode salah, pesan error merah akan muncul memberitahukan syarat minimum transaksi.
     - Tombol silang `(X)` dapat diklik untuk menghapus promo.

---

### 2. Dashboard — Metrik Keuangan Khusus (Executive Finance)
- **Tujuan**: Memastikan Owner, Super Admin, dan Finance dapat memantau indikator keuangan utama secara langsung.
- **Prosedur Pengujian**:
  1. Login sebagai Owner (`owner@istanalaundry.com`) atau Finance (`finance@istanalaundry.com`).
  2. Buka **Executive Dashboard** (`/dashboard`).
  3. Perhatikan 5 Card Summary di bagian atas.
  4. **Hasil yang Diharapkan**:
     - **Total Omset**: Menampilkan total akumulasi nilai transaksi.
     - **Kas Masuk (Bln Ini)**: Menampilkan total nilai pembayaran yang lunas pada bulan berjalan (`paid_amount`).
     - **Total Piutang**: Menampilkan total nilai tagihan invoice/pembayaran yang belum lunas (`pending` & `partial`).
     - **Pertumbuhan**: Persentase perbandingan pendapatan dibanding bulan sebelumnya (MoM).
     - **Order Aktif**: Jumlah nota yang masih diproses di workshop.

---

### 3. Pemantauan Kinerja & Analytics (Performance Monitoring)
- **Tujuan**: Memverifikasi filter periode tanggal serta detail breakdown harian transaksi kasir & produktivitas staf workshop.
- **Prosedur Pengujian**:
  1. Buka menu **Kinerja** (`/performance`).
  2. Gunakan **Filter Bar** (Cabang, Dari Tanggal, Sampai Tanggal) lalu klik **Terapkan Filter**.
  3. Gulir ke bagian **"Rincian Harian Transaksi per Kasir"**.
  4. **Hasil yang Diharapkan**:
     - Sistem menampilkan tabel per tanggal dengan rincian: Tanggal, Nama Kasir, Jumlah Nota, Omset Lunas, Pending, dan Total Diskon.
     - Tabel **Produktivitas Staf Workshop** menampilkan total aksi transisi status, order unik, dan jumlah order diselesaikan (`SIAP`).

---

### 4. HR & Payroll Enhancement
- **Tujuan**: Memverifikasi pemisahan BPJS Kesehatan & BPJS Ketenagakerjaan, tunjangan transport otomatis, dan bonus umum.
- **Prosedur Pengujian**:
  1. Buka menu **HR & Payroll** (`/hr`).
  2. Buat penggajian baru untuk bulan berjalan via tombol **Generate Payroll**.
  3. Klik ikon **Detail / Edit Item** pada salah satu karyawan.
  4. **Hasil yang Diharapkan**:
     - Inputan terpisah untuk **BPJS Kesehatan (1%)** dan **BPJS Ketenagakerjaan (2%)**.
     - Tunjangan Transport terisi otomatis berdasarkan jumlah kehadiran (`Hari Hadir x Rp 15.000`).
     - Terdapat field **Bonus Umum / Spesial** (`special_bonus`).
  5. Klik **Cetak Slip Gaji**. Pada lembar slip gaji, rincian BPJS Kesehatan dan BPJS Ketenagakerjaan ditampilkan terpisah secara rapi di bagian Potongan.

---

### 5. Fixed Assets Enhancement (Aset Tetap)
- **Tujuan**: Memastikan kolom Cabang, Tanggal Pembelian, dan Tanggal/Catatan Maintenance Terakhir tampil dengan jelas.
- **Prosedur Pengujian**:
  1. Buka menu **Aset Tetap** (`/assets`).
  2. **Hasil yang Diharapkan di Halaman Index**:
     - Kolom **Kategori & Cabang**: Menampilkan nama cabang lokasi aset berada.
     - Kolom **Tgl Beli**: Menampilkan tanggal perolehan (`acquisition_date`) beserta informasi usia aset (misal: "3 bulan lalu").
     - Kolom **Maintenance Terakhir**: Menampilkan tanggal maintenance terakhir dan jadwal maintenance berikutnya (dengan indikator ⚠️ jika sudah lewatin tanggal).
  3. Klik tombol **Detail & Jadwal** pada salah satu aset (`/assets/{id}`).
  4. **Hasil yang Diharapkan di Halaman Detail**:
     - Card **Riwayat Maintenance** menampilkan rincian tanggal & catatan maintenance terakhir.
     - Tabel **Jadwal Depresiasi** menampilkan status posting depresiasi bulanan dari bulan ke-1 hingga akhir umur manfaat.

---

### 6. Verification Role & Permissions (Finance Role Audit)
- **Tujuan**: Memastikan pengguna dengan role **Finance** dapat mengakses seluruh fitur keuangan tanpa kendala 403 Forbidden.
- **Prosedur Pengujian**:
  1. Login dengan akun Finance (`finance@istanalaundry.com`).
  2. Buka rute berikut satu per satu:
     - `/finance` (Chart of Accounts / COA)
     - `/finance/journals` (Jurnal Umum & Buku Besar)
     - `/finance/periods` (Penutupan Periode Akuntansi)
     - `/finance/reports` (Laporan Keuangan: Neraca, Laba Rugi, Trial Balance)
     - `/hr` (Penggajian & Slip Gaji)
     - `/assets` (Manajemen Aset Tetap & Depresiasi)
  3. **Hasil yang Diharapkan**: Seluruh halaman dapat diakses 100% tanpa ada pesan error `403 | Unauthorized`.

---

## Panduan Akun Pengujian (Seeded Users)

| Role | Email | Password |
|---|---|---|
| **Owner / Dev** | `owner@istanalaundry.com` | `password` |
| **Finance** | `finance@istanalaundry.com` | `password` |
| **Branch Admin** | `admin.smd01@istanalaundry.com` | `password` |
| **Cashier** | `cashier.smd01@istanalaundry.com` | `password` |
| **Workshop Admin** | `workshop.smd01@istanalaundry.com` | `password` |
