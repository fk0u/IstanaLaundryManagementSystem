# Istana Laundry Management System

[![Laravel Version](https://img.shields.io/badge/laravel-v13.x-red.svg)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/php-v8.4+-blue.svg)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/tailwindcss-v4.0-38bdf8.svg)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**Istana Laundry Management System** adalah platform Semi-ERP (Enterprise Resource Planning) berbasis web yang dirancang khusus untuk mengelola operasional bisnis laundry multi-cabang secara terintegrasi. Sistem ini menyediakan visibilitas penuh bagi Owner, pengelolaan terdesentralisasi bagi cabang, pelacakan proses pengerjaan cucian (production tracking) secara real-time, serta pencatatan keuangan berpasangan otomatis (double-entry bookkeeping).

---

## 🚀 Fitur Utama & Modul (14 Modul)

1. **Authentication & RBAC**: Autentikasi aman berbasis peran menggunakan Laravel Breeze dan Spatie Permission v8. Mendukung 8 peran: *Developer, Owner, Super Admin, Branch Admin, Workshop Admin, Cashier, Workshop Staff, CS/Marketing,* dan *Finance*.
2. **Workshop & Branch Management**: Manajemen multi-cabang terpusat dengan isolasi data otomatis berbasis cabang pengguna yang sedang masuk (`Branch_Scope`).
3. **Master Data**: Pengelolaan daftar layanan (satuan/kiloan/kategori), harga dinamis per cabang, riwayat perubahan harga, dan struktur Chart of Accounts (COA) dasar.
4. **POS & Billing**: Point of Sale kasir berkinerja tinggi, pencarian pelanggan cepat, perhitungan promo otomatis, auto-journal jurnal umum, cetak struk thermal, serta alur persetujuan refund 4 tahap.
5. **Production Tracking**: Pelacakan siklus hidup pengerjaan cucian (8 tahapan tetap: *TERIMA → PILAH → CUCI → KERING → LIPAT → CEK → SIAP → DIAMBIL*) menggunakan kode QR dan fitur scan status massal.
6. **CRM & Loyalty**: Program loyalitas pelanggan dengan akumulasi poin otomatis dan 4 tingkatan keanggotaan (*Bronze, Silver, Gold, Platinum*).
7. **Promotions Engine**: Pembuatan promosi yang fleksibel (diskon persen/nominal, beli X gratis Y, promo khusus tier) dengan pembatasan kuota dan pendeteksi overlap.
8. **Inventory & Procurement**: Manajemen stok inventori cabang dengan metode pengeluaran barang **FIFO** (First In First Out) serta alur pengadaan barang (PR → PO → GRN).
9. **Finance & Accounting**: Pencatatan keuangan otomatis menggunakan pembukuan double-entry (Debit/Kredit seimbang), penutupan periode akuntansi bulanan, dan buku besar.
10. **HR Management**: Pengelolaan data karyawan, absensi harian, riwayat kenaikan gaji, dan perhitungan payroll bulanan otomatis terintegrasi.
11. **Fixed Asset**: Pencatatan aset tetap (misal: mesin cuci, pengering) beserta jadwal penyusutan periodik otomatis.
12. **Dashboard & Analytics**: Halaman visualisasi data yang menampilkan metrik pendapatan, produktivitas karyawan, performa cabang, dan analisis persediaan.
13. **Reporting & Export**: Ekspor data laporan operasional dan keuangan ke format Excel atau PDF terformat.
14. **Public Tracking**: Halaman pelacakan status cucian mandiri untuk pelanggan tanpa perlu login menggunakan nomor order atau QR Code.

---

## 🛠️ Tech Stack

Sistem ini dirancang menggunakan arsitektur **3-Layer Architecture** (Presentation, Business Logic, dan Data Layer) yang kokoh dan mudah dipelihara.

- **Backend Framework**: Laravel 13 (PHP 8.4+)
- **Frontend Interactivity**: Alpine.js 3
- **Styling / UI**: Tailwind CSS v4.0 (diintegrasikan melalui `@tailwindcss/vite`)
- **Database (Dev/Local)**: SQLite (file database lokal)
- **Database (Prod)**: MySQL 8+
- **Security & Authorization**: Spatie Laravel Permission v8 & Laravel Breeze
- **E-reporting & Assets**: Barryvdh Laravel DomPDF, Maatwebsite Laravel Excel, Simple QR Code, Spatie Laravel Backup
- **Charts / Visuals**: Chart.js v4

---

## ⚙️ Persyaratan Sistem

Pastikan perangkat Anda memenuhi spesifikasi berikut:
- PHP >= 8.4 (ekstensi: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD, ZIP)
- Composer >= 2.9
- Node.js >= 24 & NPM >= 11
- SQLite 3 (untuk pengembangan lokal) atau MySQL 8 (untuk produksi)

---

## 📥 Langkah Instalasi (Pengembangan Lokal)

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek di komputer lokal Anda:

1. **Clone Repositori**:
   ```bash
   git clone <URL_REPOSITORI_ANDA>
   cd IstanaLaundryManagementSystem
   ```

2. **Instal Dependensi Backend (Composer)**:
   ```bash
   composer install
   ```

3. **Instal Dependensi Frontend (NPM)**:
   ```bash
   npm install
   ```

4. **Konfigurasi Environment**:
   Salin berkas konfigurasi lingkungan default:
   ```bash
   cp .env.example .env
   ```
   Secara default, konfigurasi `.env` telah diset menggunakan driver **SQLite** untuk pengembangan lokal. Pastikan file database SQLite kosong telah dibuat:
   ```bash
   # Di Windows (PowerShell)
   New-Item -Path database\database.sqlite -ItemType File -Force
   
   # Di Linux / macOS
   touch database/database.sqlite
   ```

5. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

6. **Jalankan Migrasi Database & Seeder**:
   ```bash
   php artisan migrate --seed
   ```

7. **Kompilasi Aset Frontend**:
   ```bash
   npm run build
   ```

8. **Jalankan Server Pengembangan**:
   Jalankan server Laravel dan bundler Vite secara bersamaan:
   ```bash
   npm run dev
   ```
   Aplikasi default dapat diakses melalui browser pada alamat [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

## 📂 Struktur Arsitektur & Folder

Proyek ini menerapkan struktur standar Laravel dengan perluasan layer untuk logika bisnis:
- `/app/Repositories` - Layer abstraksi database (Repository Pattern) untuk query database.
- `/app/Services` - Layer logika bisnis terpusat (Service Pattern) untuk menjaga controller tetap tipis (*thin controllers*).
- `/app/Observers` - Observer event-driven untuk efek samping transaksi (misal: auto-journal, penambahan poin loyalitas).
- `/app/Models` - Model Eloquent dan relasi antar entitas.
- `/resources/views` - Halaman tampilan UI menggunakan Blade & Alpine.js.

---

## 📝 Lisensi

Proyek ini dilisensikan di bawah lisensi MIT. Lihat file [LICENSE](LICENSE) untuk informasi lebih lanjut.
