# System Overview & Technical Architecture Blueprint
# Istana Laundry Management System (Enterprise Semi-ERP)

> **Versi:** 2.5 · **Dipublikasikan:** 30 Juli 2026  
> **Audience:** Developer, System Architect, Technical Lead, Stakeholder  
> **Repository:** https://github.com/fk0u/IstanaLaundryManagementSystem  
> **Status Production:** 100% Fully Operational & Enterprise Ready

---

## 1. Ringkasan Eksekutif

**Istana Laundry Management System** adalah aplikasi Semi-ERP multi-cabang terintegrasi yang dirancang khusus untuk operasional laundry komersial skala menengah hingga besar (Istana Laundry Samarinda). 

Sistem ini mencakup seluruh rantai operasional bisnis:
- **Front-Office**: Point of Sale (POS) kasir harian, cetak struk thermal/A4, & notifikasi WhatsApp otomatis.
- **Workshop**: Pelacakan alur produksi 8-stasiun (TERIMA $\rightarrow$ PILAH $\rightarrow$ CUCI $\rightarrow$ KERING $\rightarrow$ LIPAT $\rightarrow$ CEK $\rightarrow$ SIAP $\rightarrow$ DIAMBIL) & QR Code tracking.
- **CRM & Loyalty**: Manajemen keanggotaan, 4-tier loyalty points (Bronze, Silver, Gold, Platinum), & promo kupon diskon manual.
- **Inventory & Procurement**: Manajemen stok Bahan Habis Pakai (BHP), siklus pengadaan (PR $\rightarrow$ PO $\rightarrow$ GRN), & pemotongan stok beraturan FIFO.
- **Finance & Accounting**: Akuntansi double-entry otomatis, Chart of Accounts (COA), Jurnal Umum, Penutupan Periode Akuntansi, Laporan Keuangan (Laba Rugi, Neraca, Neraca Saldo, Analytics), & ekspor CSV UTF-8 BOM.
- **HR & Payroll**: Manajemen biodata & rekening bank staf karyawan, payroll konsolidasi seluruh cabang, penguncian status `FINAL`, insentif workload workshop, & cetak slip gaji.
- **Fixed Assets**: Manajemen aset tetap, depresiasi garis lurus & saldo menurun, serta riwayat maintenance.
- **Scope & Multi-Branch**: Multi-tenant data isolation per cabang dengan `BranchScoped` trait, middleware `branch.scope`, & penyesuaian scope global.

---

## 2. Arsitektur Logis & Stack Teknologi

```text
┌────────────────────────────────────────────────────────────────────────┐
│  Presentation Layer: Blade Views, Alpine.js, Tailwind CSS v4, Chart.js  │
├────────────────────────────────────────────────────────────────────────┤
│  HTTP / API Layer: routes/web.php · routes/api.php (Sanctum Tokens)    │
│  Middleware: auth, verified, branch.scope, role: (Spatie Permission)   │
├────────────────────────────────────────────────────────────────────────┤
│  Application Layer: Controllers → Services → Observers → Queue Jobs    │
│  Services: JournalService, LoyaltyService, FinancialReportService,     │
│            AuditLogService, WhatsAppService                             │
├────────────────────────────────────────────────────────────────────────┤
│  Domain Layer: Eloquent Models + BranchScoped + Auditable Traits       │
├────────────────────────────────────────────────────────────────────────┤
│  Infrastructure Layer: Docker Compose (Nginx, PHP 8.4-FPM, MySQL 8.0)  │
└────────────────────────────────────────────────────────────────────────┘
```

### 2.1 Stack Teknologi Utama
- **Framework Core**: Laravel 13.x (PHP 8.4-FPM di container Docker)
- **Frontend / UI**: Laravel Blade Templates, Alpine.js v3, Tailwind CSS v4, Google Material Symbols Outlined, Chart.js
- **Database & Persistence**: MySQL 8.0 (47 Migrations)
- **Keamanan & Otorisasi**: Spatie Laravel-Permission v8, Laravel Sanctum, Custom Audit Log Observer
- **Ekspor & Cetak**: Streaming CSV UTF-8 BOM, Dompdf, Thermal Receipt Builder 58mm/80mm
- **Containerization**: Docker Compose (`app`, `db`, `nginx`)

---

## 3. Peta Modul, Controllers & Middleware Matrix

| Modul Utama | Rute Utama | Controller | Middleware Otorisasi |
|---|---|---|---|
| **Executive Dashboard** | `/dashboard` | `DashboardController` | `auth`, `branch.scope` |
| **Point of Sale (POS)** | `/pos` | `POSController` | `role:Developer|Owner|Super_Admin|Branch_Admin|Cashier` |
| **Tracking Produksi** | `/production` | `ProductionController` | `role:Developer|Owner|Super_Admin|Branch_Admin|Workshop_Admin|Workshop_Staff` |
| **CRM & Loyalty** | `/customers` | `CustomerController` | `role:Developer|Owner|Super_Admin|Branch_Admin|CS_Marketing` |
| **Pengadaan (Procurement)** | `/procurement/*` | `ProcurementController` | `role:Developer|Owner|Super_Admin|Branch_Admin|Workshop_Admin` |
| **Akuntansi & Jurnal** | `/finance/*` | `JournalController`, `FinancialReportController` | `role:Developer|Owner|Super_Admin|Finance` |
| **HR & Payroll** | `/hr/*` | `HRController` | `role:Developer|Owner|Super_Admin|Finance` |
| **Manajemen Staf (Users)** | `/users` | `UserController` | `role:Developer|Owner|Super_Admin` |
| **Manajemen Cabang** | `/branches` | `BranchController` | `role:Developer|Owner|Super_Admin` |
| **Aset Tetap & Depresiasi** | `/assets/*` | `AssetController` | `role:Developer|Owner|Super_Admin|Finance` |
| **Refund & Pembatalan** | `/refunds` | `RefundController` | `role:Developer|Owner|Super_Admin|Branch_Admin|Finance|Cashier` |
| **Pelacakan Publik** | `/track` | Anonymous Controller | `throttle:30,1` (Public) |

---

## 4. Alur Integrasi Otomatis (Event-Driven Pipeline)

### 4.1 Order Placement & Auto-Journal Pipeline
1. Kasir memproses transaksi di `/pos`.
2. Model `Order` terbuat dengan status pembayaran `paid` atau `pending`.
3. Model Event `OrderObserver` menangkap pembuatan order lunas $\rightarrow$ Menjadwalkan `PostOrderJournalJob` ke Queue.
4. `JournalService` memposting jurnal otomatis (Debit: Kas/Piutang & Beban Diskon, Kredit: Pendapatan & Hutang PPN).
5. `LoyaltyService` menambahkan poin loyalitas ke akun pelanggan.

### 4.2 Alur Refund 4-Tahap & Reversal Jurnal
1. **Stage 1 (Pengajuan)**: Kasir/Admin membuat pengajuan refund di `/refunds` (Status `pending`).
2. **Stage 2 (Branch Approval)**: Branch Admin menyetujui (Status `branch_approved`).
3. **Stage 3 (Finance Approval)**: Finance menyetujui (Status `finance_approved`).
4. **Stage 4 (Final Owner Approval)**: Owner menyetujui $\rightarrow$ System otomatis:
   - Mengubah status pembayaran order menjadi `refunded`.
   - Memunculkan jurnal pembalik (*reversal journal*) via `JournalService::reverseJournal()`.
   - Memotong poin loyalitas yang pernah diberikan secara proporsional.

---

## 5. Ringkasan Skema Database (47 Migrations)

Tabel utama dalam database MySQL `istana_laundry`:
- **Core Systems**: `users`, `roles`, `permissions`, `branches`, `workshops`, `audit_logs`, `jobs`.
- **POS & Production**: `orders`, `order_items`, `production_status_logs`, `order_sequence_counters`.
- **CRM & Marketing**: `customers`, `loyalty_point_logs`, `promotions`.
- **Procurement & Inventory**: `suppliers`, `inventory_items`, `inventory_batches`, `purchase_requests`, `purchase_request_items`, `purchase_orders`, `purchase_order_items`, `goods_received_notes`, `grn_items`.
- **Finance & Accounting**: `chart_of_accounts`, `accounting_periods`, `journals`, `journal_lines`, `refunds`.
- **HR & Assets**: `employees`, `salary_histories`, `attendances`, `payrolls`, `payroll_items`, `fixed_assets`, `depreciation_schedules`.

---

## 6. Prosedur Deployment & Perintah Perawatan

### Launching Docker Environment
```bash
# 1. Build & Jalankan Container Docker
docker compose up -d --build

# 2. Jalankan Migrasi Database & Seeder
docker exec -i istanalaundrymanagementsystem-app-1 php artisan migrate --seed

# 3. Jalankan Queue Worker (untuk Jurnal Otomatis & GRN)
docker exec -i istanalaundrymanagementsystem-app-1 php artisan queue:work --tries=3

# 4. Jalankan Automated Test Suite
docker exec -i istanalaundrymanagementsystem-app-1 php artisan test
```

---

> **Dokumentasi Terkait:**
> - [MANUAL_TESTING_GUIDE.md](MANUAL_TESTING_GUIDE.md) — Panduan pengujian manual UAT 12 modul.
> - [SRS.md](SRS.md) — Software Requirements Specification.
> - [PRODUCT_ROADMAP.md](PRODUCT_ROADMAP.md) — Roadmap pengembangan jangka panjang.
