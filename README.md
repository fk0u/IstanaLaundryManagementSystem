# Istana Laundry Management System (Enterprise Semi-ERP)

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4--FPM-777BB4?logo=php&logoColor=white)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://docs.docker.com/compose/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com)
[![Sanctum](https://img.shields.io/badge/API-Sanctum-black)](https://laravel.com/docs/sanctum)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**Istana Laundry Management System** adalah platform Semi-ERP multi-cabang terintegrasi untuk operasional bisnis laundry komersial: Point of Sale (POS), pelacakan produksi 8-stasiun, pengadaan (PR $\rightarrow$ PO $\rightarrow$ GRN), persediaan FIFO, akuntansi double-entry otomatis, HR & payroll konsolidasi global, CRM & loyalty points, aset tetap & depresiasi, serta REST API Sanctum.

| Meta Data | Keterangan |
|---|---|
| **Client** | Istana Laundry Samarinda |
| **Developer** | KOU / Alenkosa.id |
| **Repository** | https://github.com/fk0u/IstanaLaundryManagementSystem |
| **Main Branch** | `master` |
| **Status Production** | **100% Production Ready & Tested** |

---

## 📚 Dokumentasi Utama & Panduan Pengujian

| Dokumen | Target Pembaca | Deskripsi & Isi |
|---|---|---|
| 🧪 **[MANUAL_TESTING_GUIDE.md](docs/MANUAL_TESTING_GUIDE.md)** | **QA / Tester / Admin** | **Panduan Pengujian Manual UAT Step-by-Step 12 Modul** |
| 📐 **[SYSTEM_OVERVIEW.md](docs/SYSTEM_OVERVIEW.md)** | **Architect / Lead** | **Cakupan Arsitektur, DB Schema, & Matrix Modul** |
| 📋 **[SRS.md](docs/SRS.md)** | **Stakeholder / PM** | **Software Requirements Specification (V1 & V2)** |
| 🗺️ **[PRODUCT_ROADMAP.md](docs/PRODUCT_ROADMAP.md)** | **Product Owner** | **Roadmap Pengembangan Jangka Panjang** |
| 📝 **[tasks.md](tasks.md)** | **Developer** | **Backlog & Issue Tracker (#29–#36 Closed)** |

---

## 🚀 Quick Start (Docker Environment)

Jalankan seluruh stack aplikasi menggunakan Docker Compose:

```bash
# 1. Clone repository
git clone https://github.com/fk0u/IstanaLaundryManagementSystem.git
cd IstanaLaundryManagementSystem

# 2. Salin environment configuration
cp .env.example .env

# 3. Build & jalankan container Docker
docker compose up -d --build

# 4. Jalankan migrasi database & seeder
docker exec -i istanalaundrymanagementsystem-app-1 php artisan migrate --seed

# 5. Jalankan queue worker (untuk jurnal otomatis & GRN)
docker exec -i istanalaundrymanagementsystem-app-1 php artisan queue:work --tries=3

# 6. Jalankan automated test suite
docker exec -i istanalaundrymanagementsystem-app-1 php artisan test
```

Aplikasi dapat diakses via browser pada: **`http://localhost:8000`**

---

## 🔑 Kredensial Pengguna Pengujian (Seeded Accounts)

| Peran (Role) | Email | Password | Akses Modul |
|---|---|---|---|
| **Owner / Developer** | `owner@istanalaundry.com` | `password` | Full Control, Switch Cabang Global, Final Approval |
| **Finance** | `finance@istanalaundry.com` | `password` | Jurnal, COA, Laporan Keuangan, Payroll, Aset, Refund |
| **Branch Admin** | `admin.smd01@istanalaundry.com` | `password` | Manajemen Cabang Lokal, Approval PR/PO, Refund |
| **Cashier** | `cashier.smd01@istanalaundry.com` | `password` | POS Billing, Pelanggan, Cetak Struk, WhatsApp |
| **Workshop Admin / Staff** | `workshop.smd01@istanalaundry.com` | `password` | Antrean Produksi 8-Stasiun, Scan QR |

---

## 🌟 Ringkasan Modul Utama

1. **POS & Billing**: Kasir cepat, input kupon promo manual (`PROMO50`), diskon poin, nota thermal 58mm/80mm & A4.
2. **Production Tracking**: 8 stasiun (`TERIMA` $\rightarrow$ `PILAH` $\rightarrow$ `CUCI` $\rightarrow$ `KERING` $\rightarrow$ `LIPAT` $\rightarrow$ `CEK` $\rightarrow$ `SIAP` $\rightarrow$ `DIAMBIL`), QR code tracking, pencarian no. order, & filter role workshop.
3. **CRM & Loyalty**: Keanggotaan pelanggan, 4 tier (Bronze, Silver, Gold, Platinum), modal riwayat transaksi, & link WhatsApp.
4. **Inventory & Procurement**: Stok BHP, PR $\rightarrow$ PO $\rightarrow$ GRN, & pemotongan stok beraturan FIFO.
5. **Finance & Accounting**: Akuntansi double-entry otomatis, COA, Jurnal Umum, Laporan Keuangan (Laba Rugi, Neraca, Neraca Saldo, Analytics), grafik visual Chart.js, & ekspor CSV UTF-8 BOM.
6. **HR & Payroll**: Biodata & rekening bank staf, payroll konsolidasi seluruh cabang, status `FINAL (DIKUNCI)`, insentif workload, & slip gaji.
7. **Manajemen Cabang & User Sync**: Modul `/branches` dengan metrik scope & sinkronisasi otomatis `User` $\leftrightarrow$ `Employee`.
8. **Fixed Assets**: Pendaftaran aset tetap, depresiasi otomatis (garis lurus & saldo menurun), & jadwal maintenance.
9. **Refund & Pembatalan**: Alur persetujuan 4-tahap (Kasir $\rightarrow$ Branch Admin $\rightarrow$ Finance $\rightarrow$ Owner) dengan reversal journal otomatis.
10. **Public Track & WhatsApp**: Pelacakan publik `/track?order_number=...` dengan masking PII & notifikasi WA otomatis.

---

## 🛡️ Lisensi

Sistem ini dirilis di bawah lisensi [MIT License](LICENSE).  
**Istana Laundry Samarinda** · **KOU / Alenkosa.id**
