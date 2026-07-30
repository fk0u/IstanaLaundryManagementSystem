# Panduan Pengujian Manual & End-to-End UAT (Istana Laundry Management System)

> **Dokumen:** Comprehensive Manual UAT Testing Guide with Screenshots  
> **Versi:** 2.5 · **Dipublikasikan:** 30 Juli 2026  
> **Stack:** Laravel 13, Docker, PHP 8.4-FPM, MySQL 8, Sanctum, Spatie Permission  
> **Environment:** `http://localhost:8000`

---

## 📋 Daftar Isi
1. [Kredensial Akun Pengujian (Seeded Accounts)](#1-kredensial-akun-pengujian-seeded-accounts)
2. [Modul 1: Executive Dashboard & Metrik Keuangan](#modul-1-executive-dashboard--metrik-keuangan)
3. [Modul 2: Point of Sale (POS) & Billing](#modul-2-point-of-sale-pos--billing)
4. [Modul 3: Tracking Produksi Workshop & QR Code](#modul-3-tracking-produksi-workshop--qr-code)
5. [Modul 4: HR & Payroll Konsolidasi Global](#modul-4-hr--payroll-konsolidasi-global)
6. [Modul 5: Manajemen Cabang & Scope Operasional](#modul-5-manajemen-cabang--scope-operasional)
7. [Modul 6: CRM & Loyalty Pelanggan](#modul-6-crm--loyalty-pelanggan)
8. [Modul 7: Laporan Keuangan & Ekspor Data](#modul-7-laporan-keuangan--ekspor-data)
9. [Modul 8: Fixed Assets & Depresiasi](#modul-8-fixed-assets--depresiasi)
10. [Modul 9: Inventori & Pengadaan (PR -> PO -> GRN)](#modul-9-inventori--pengadaan-pr---po---grn)
11. [Modul 10: Refund & Pembatalan Order 4-Tahap](#modul-10-refund--pembatalan-order-4-tahap)

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

## Modul 1: Executive Dashboard & Metrik Keuangan

![Executive Dashboard](file:///d:/Project/IstanaLaundryManagementSystem/docs/images/executive_dashboard_1785408508196.png)

### Skenario 1.1: Pemantauan Omset, Kas, & Order Aktif Multi-Cabang
- **Tujuan**: Memastikan 5 Kartu Ringkasan (Total Omset, Kas Masuk, Total Piutang, Pertumbuhan MoM, Order Aktif) serta Grafik Analytics menampilkan akumulasi data sesuai scope cabang aktif.
- **Prosedur Pengujian**:
  1. Login sebagai Owner (`owner@istanalaundry.com`).
  2. Buka halaman **Dashboard** (`/dashboard`).
  3. Gunakan Switcher Cabang pada header atas untuk memilih `Semua Cabang` atau cabang spesifik (*Lambung Mangkurat* / *Dr. Sutomo*).
- **Hasil yang Diharapkan**:
  - Angka pada 5 kartu summary diperbarui secara dinamis tanpa error.
  - Grafik tren omset harian dan persentase pertumbuhan bulanan terisi dengan akurat.

---

## Modul 2: Point of Sale (POS) & Billing

![Point of Sale](file:///d:/Project/IstanaLaundryManagementSystem/docs/images/point_of_sale_1785408515705.png)

### Skenario 2.1: Pembuatan Order & Input Kupon Promo Manual
- **Tujuan**: Memastikan kasir dapat memilih pelanggan, menambahkan layanan cuci, menerapkan kode kupon manual, dan memproses pembayaran.
- **Prosedur Pengujian**:
  1. Login sebagai Kasir (`cashier.smd01@istanalaundry.com`).
  2. Akses menu **POS** (`/pos`).
  3. Cari & pilih nama pelanggan (*Budi Santoso*).
  4. Tambahkan layanan *Cuci Komplit Reguler* (5 Kg) dan *Dry Clean Jas* (2 Pcs).
  5. Masukkan kode kupon manual `PROMO50` pada kotak input lalu tekan **Terapkan**.
  6. Masukkan nominal pembayaran tunai dan klik **Proses Bayar & Cetak Nota**.
- **Hasil yang Diharapkan**:
  - Kupon terverifikasi dan diskon terpotong otomatis.
  - Nota terbuat dengan nomor order unik (contoh: `ORD-SMD01-20260730-0001`).
  - Struk thermal / A4 invoice tampil siap dicetak.

---

## Modul 3: Tracking Produksi Workshop & QR Code

![Production Tracking](file:///d:/Project/IstanaLaundryManagementSystem/docs/images/production_tracking_1785408523512.png)

### Skenario 3.1: Pencarian Order & Transisi Status Produksi Linear
- **Tujuan**: Memastikan staf workshop dapat mencari order dan mengubah status produksi secara bertahap.
- **Prosedur Pengujian**:
  1. Login sebagai Staf Workshop (`workshop.smd01@istanalaundry.com`).
  2. Buka menu **Production** (`/production`).
  3. Gunakan kotak pencarian untuk mengetik nomor nota atau nama pelanggan.
  4. Lakukan transisi status linear: `TERIMA` $\rightarrow$ `PILAH` $\rightarrow$ `CUCI` $\rightarrow$ `KERING` $\rightarrow$ `LIPAT` $\rightarrow$ `CEK` $\rightarrow$ `SIAP`.
- **Hasil yang Diharapkan**:
  - Pencarian memfilter daftar antrean produksi secara instan.
  - Setiap transisi status tercatat pada log audit dengan timestamp waktu Samarinda (WITA).

---

## Modul 4: HR & Payroll Konsolidasi Global

![HR & Payroll](file:///d:/Project/IstanaLaundryManagementSystem/docs/images/hr_payroll_1785408533859.png)

### Skenario 4.1: Penggajian Global & Penguncian Status FINAL
- **Tujuan**: Memastikan penggajian dapat dibuat untuk seluruh cabang sekaligus dan statusnya dapat dikunci (`FINAL`).
- **Prosedur Pengujian**:
  1. Buka menu **HR & Payroll** (`/hr`).
  2. Klik **Generate Payroll Periode** dan pilih scope `🌟 Konsolidasi Seluruh Cabang`.
  3. Periksa rincian komponen: Gaji Pokok, Insentif Workload, BPJS Kesehatan, BPJS Ketenagakerjaan.
  4. Klik tombol **Finalkan Payroll & Kunci**.
- **Hasil yang Diharapkan**:
  - Payroll mencakup seluruh karyawan dari semua cabang.
  - Nominal gaji terhitung akurat dan status berubah menjadi `FINAL (DIKUNCI)`.

---

## Modul 5: Manajemen Cabang & Scope Operasional

![Branch Management](file:///d:/Project/IstanaLaundryManagementSystem/docs/images/branch_management_1785408542826.png)

### Skenario 5.1: Pengelolaan Cabang & Otomatisasi Scope
- **Tujuan**: Menguji fitur CRUD cabang dan penautan data operasional.
- **Prosedur Pengujian**:
  1. Buka menu **Manajemen Cabang** (`/branches`).
  2. Tambahkan cabang baru (Kode, Nama, Alamat, Telepon).
  3. Uji tombol Edit & Nonaktifkan Cabang.
- **Hasil yang Diharapkan**:
  - Cabang baru langsung tersedia pada dropdown header switcher.
  - Metrik statistik per cabang (Jumlah Staf, User Login, Volume Nota) terhitung akurat.

---

## Modul 6: CRM & Loyalty Pelanggan

![CRM Customers](file:///d:/Project/IstanaLaundryManagementSystem/docs/images/crm_customers_1785408551007.png)

### Skenario 6.1: Wawasan Pelanggan & WhatsApp Direct Link
- **Tujuan**: Memastikan informasi riwayat belanja, poin loyalitas, dan link WhatsApp pelanggan berfungsi dengan baik.
- **Prosedur Pengujian**:
  1. Buka menu **CRM & Loyalty** (`/customers`).
  2. Perhatikan kartu statistik: Total Transaksi, Total Belanja, Transaksi Terakhir.
  3. Klik tombol **Riwayat Nota** dan ikon **WhatsApp**.
- **Hasil yang Diharapkan**:
  - Modal riwayat menampilkan 10 nota terakhir.
  - Tautan WhatsApp mengarahkan ke nomor WA pelanggan dengan pesan sapaan terformat.

---

## Modul 7: Laporan Keuangan & Ekspor Data

![Finance Reports](file:///d:/Project/IstanaLaundryManagementSystem/docs/images/finance_reports_1785408562738.png)

### Skenario 7.1: Grafik Visual & Ekspor CSV UTF-8 BOM
- **Tujuan**: Memverifikasi 4 tab laporan keuangan (Laba Rugi, Neraca, Neraca Saldo, Analytics) dan fitur ekspor CSV.
- **Prosedur Pengujian**:
  1. Buka menu **Laporan Keuangan** (`/finance/reports`).
  2. Amati grafik visual Chart.js di setiap tab.
  3. Klik **Ekspor CSV Laporan Keuangan**.
- **Hasil yang Diharapkan**:
  - Grafik visual terisi data keuangan yang relevan.
  - Berkas CSV terunduh dalam format UTF-8 BOM tanpa karakter acak saat dibuka di Excel.

---

> **Dokumentasi Terkait AI Testing:**
> Lihat [QA_AUTOMATION_AI_TESTING_GUIDE.md](QA_AUTOMATION_AI_TESTING_GUIDE.md) untuk petunjuk otomatisasi pengujian berbasis AI menggunakan Playwright, ZeroStep AI, dan Applitools.
