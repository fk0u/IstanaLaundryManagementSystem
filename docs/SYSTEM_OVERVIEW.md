# System Overview & Technical Architecture Blueprint
# Istana Laundry Management System (Enterprise Semi-ERP)

> **Versi:** 3.0 · **Dipublikasikan:** 5 Agustus 2026  
> **Audience:** Developer, System Architect, Technical Lead, Stakeholder  
> **Official Admin / Customer Care:** +62 811-5599-199  
> **Repository:** https://github.com/fk0u/IstanaLaundryManagementSystem  
> **Live Production:** https://istanasystem.alk-tech.my.id  
> **API Documentation:** https://istanasystem.alk-tech.my.id/api/documentation  

---

## 1. Ringkasan Eksekutif

**Istana Laundry Management System** adalah aplikasi ERP berbasis web multi-cabang terintegrasi yang dirancang khusus untuk operasional laundry komersial kelas premium (**Istana Premium Laundry Service**, Samarinda, Kalimantan Timur).

Sistem ini mengintegrasikan seluruh rantai bisnis secara real-time:
- **Front-Office (POS)**: Point of Sale kasir harian, cetak struk thermal/A4, manajemen shift, & integrasi WhatsApp notification (+62 811-5599-199).
- **Workshop Production**: Pelacakan alur produksi 8-stasiun (TERIMA → PILAH → CUCI → KERING → LIPAT → CEK → SIAP → DIAMBIL) & QR Code tracking.
- **CRM & Loyalty**: Manajemen keanggotaan, 4-tier loyalty points (Bronze, Silver, Gold, Platinum), & promo kupon diskon.
- **Inventory & FIFO Stock**: Stok Bahan Habis Pakai (BHP), siklus pengadaan (PR → PO → GRN), & pemotongan stok otomatis metode FIFO.
- **Finance & Accounting**: Akuntansi double-entry otomatis, Chart of Accounts (COA), Jurnal Umum, Penutupan Periode Akuntansi, & Laporan Keuangan (Laba Rugi, Neraca, Neraca Saldo).
- **HR & Payroll**: Manajemen biodata staf, penggajian bulanan konsolidasi, penguncian status `FINAL`, & cetak slip gaji.
- **Fixed Assets & Depreciation**: Pencatatan aset tetap, perhitungan depresiasi garis lurus, & jadwal penyusutan bulanan.
- **Enterprise Security**: TOTP 2FA (RFC 6238), Trust Device 30 Hari, Security Headers (HSTS, CSP), Rate Limiting, Audit Logging, & WebP Avatar <200KB.
- **Full RESTful API Engine**: 16 API Controller, 80+ endpoint terproteksi Sanctum Token & Swagger UI interaktif di `/api/documentation`.

---

## 2. Arsitektur Logis & Stack Teknologi

```text
┌────────────────────────────────────────────────────────────────────────┐
│  Presentation Layer: Blade Views, Alpine.js, Tailwind CSS v4, Chart.js  │
├────────────────────────────────────────────────────────────────────────┤
│  API Engine / Documentation: Swagger UI (/api/documentation), Sanctum  │
│  HTTP Layer: routes/web.php · routes/api.php · routes/auth.php         │
│  Middleware: auth, verified, branch.scope, SecurityHeadersMiddleware   │
├────────────────────────────────────────────────────────────────────────┤
│  Application Layer: Controllers → Services → Observers → Queue Jobs    │
│  Services: JournalService, LoyaltyService, FinancialReportService,     │
│            AuditLogService, WhatsAppService, TwoFactorService,        │
│            ImageCompressionService                                     │
├────────────────────────────────────────────────────────────────────────┤
│  Domain Layer: Eloquent Models + BranchScoped + Auditable Traits       │
├────────────────────────────────────────────────────────────────────────┤
│  Infrastructure Layer: Docker Compose (Nginx, PHP 8.4-FPM, MySQL 8.0)  │
└────────────────────────────────────────────────────────────────────────┘
```

### 2.1 Stack Teknologi Utama
- **Core Framework**: Laravel 13.x (PHP 8.4-FPM di container Docker)
- **Database**: MySQL 8.0 (30+ Migration, 5000+ Demo Seed Records)
- **Frontend**: Blade Templates, Alpine.js v3, Tailwind CSS v4, Material Symbols Outlined, Chart.js
- **API Engine**: Laravel Sanctum (Bearer Token) + Swagger UI (L5-Swagger / OpenAPI 3.0)
- **Security & Auth**: Custom TOTP 2FA (RFC 6238), Spatie Laravel-Permission v8, Security Headers, Brute-Force Lockout
- **Image Processing**: PHP GD / WebP (Kompresi Avatar Dinamis ≤ 200KB)
- **Production Server**: Nginx + PHP 8.4-FPM di VPS (Ubuntu 24.04 LTS, SSL Let's Encrypt)

---

## 3. Fitur Utama & Modul ERP

### 3.1 Profil & Keamanan Pengguna
- **Setup 2FA Google Authenticator**: Scan QR code, simpan 8 kode pemulihan darurat.
- **2FA Login Challenge**: Verifikasi OTP saat login untuk akun yang mengaktifkan 2FA.
- **Trust Device 30 Hari**: Opsi simpan cookie terenkripsi `2fa_device_trust` untuk bypass 2FA di browser terpercaya selama 30 hari.
- **Foto Profil Optimized WebP**: Automatic resize & convert ke `.webp` dengan ukuran maksimal 200KB. Tampil di topbar dan sidebar.

### 3.2 Full RESTful API Suite
- **Public API**: Tracking orderpublik (`/api/v1/track`), daftar cabang & layanan (`/api/v1/branches`, `/api/v1/services`), order online via GPS.
- **Authenticated RESTful API**: 12 Modul utama yang mencakup seluruh fungsionalitas sistem (Dashboard, Orders, Customers, Inventory, HR, Assets, Finance, Procurement, Shifts, Master, Users, Performance/Logs).
- **Swagger Documentation**: Dokumentasi interaktif di URL `/api/documentation`.

### 3.3 Multi-Branch & Data Isolation
- Isolasi data otomatis per cabang menggunakan Trait `BranchScoped` dan Middleware `branch.scope`.
- Administrator dapat berpindah scope cabang via selector di topbar/sidebar.

---

## 4. Kontak Resmi & Support

- **Official Admin / Customer Care WhatsApp**: +62 811-5599-199
- **Alamat Kantor Pusat**: Jl. KH. Wahid Hasyim 2 No.57, Samarinda, Kalimantan Timur
- **Dokumentasi API Live**: https://istanasystem.alk-tech.my.id/api/documentation
