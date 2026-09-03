# Session Briefing — Istana Laundry Management System

**Last Updated:** 2026-09-03 23:15 WITA

## Current Status
- **Sanitasi Database VPS Production (db_istanasystem) — LIVE & CLEAN**:
  - Database production di VPS (`157.10.161.42`) telah dibersihkan untuk persiapan operasional riil (*production go-live*).
  - **Data Mandatory Dipertahankan**: 18 Akun Pengguna (`users`), 18 Karyawan (`employees`), 5 Cabang (`branches`), 5 Unit Workshop (`workshops`), 9 Roles & 35 Permissions (Spatie), 59 Bagan Akun (`chart_of_accounts`), 10 Katalog Layanan (`services`), 5 Vendor (`suppliers`), 10 Pengaturan Sistem (`system_settings`), 40 Katalog Inventaris (`inventory_items`, stok di-reset ke 0), dan 5 Periode Akuntansi Aktif (`accounting_periods`, September 2026).
  - **Data Transaksi Dikosongkan (Truncated)**: Seluruh transaksi pesanan (`orders`, `order_items`), riwayat pelacakan workshop (`production_status_logs`), pelanggan demo (`customers`), voucher jurnal akuntansi (`journals`, `journal_lines`), pengadaan (`purchase_orders`, `grn_items`), shift kasir, absensi, dan payroll demo telah dikosongkan.
  - Snapshot clean database tersimpan di `/home/istanadev/backup_clean_production_20260903_151304.sql`.
  - Cache Laravel telah di-clear dan di-cache ulang (`optimize:clear`, `config:cache`, `route:cache`, `view:cache`).
  - Web health check terverifikasi HTTP 200 OK di `https://istanasystem.alk-tech.my.id`.
- **Dokumen Spesifikasi Lengkap Fitur & Matriks RBAC — LIVE**:
  - Dihasilkan dokumen PDF resmi: [`docs/ISTANA_LAUNDRY_FEATURE_AND_RBAC_SPECIFICATION.pdf`](file:///d:/Project/IstanaLaundryManagementSystem/docs/ISTANA_LAUNDRY_FEATURE_AND_RBAC_SPECIFICATION.pdf) dan versi Markdown: [`docs/ISTANA_LAUNDRY_FEATURE_AND_RBAC_SPECIFICATION.md`](file:///d:/Project/IstanaLaundryManagementSystem/docs/ISTANA_LAUNDRY_FEATURE_AND_RBAC_SPECIFICATION.md).
  - Merinci 11 Modul Utama ERP (Auth & Security, POS Kasir, Workshop 8-Stasiun, CRM & Loyalty, Inventory & Procurement PR-PO-GRN, Finance Double-Entry, HR Payroll, Fixed Assets, Dashboard Eksekutif, Portal Landing Page, dan RESTful API Engine 80+ endpoints).
  - Memetakan Matriks RBAC lengkap antara 9 System Roles (`Developer`, `Owner`, `Super_Admin`, `Branch_Admin`, `Workshop_Admin`, `Cashier`, `Workshop_Staff`, `CS_Marketing`, `Finance`) terhadap 32 granular permissions.
- **2FA Login Challenge Flow (Web + API) — LIVE**:
  - User yang mengaktifkan 2FA akan diminta kode TOTP saat login melalui halaman `/two-factor-challenge`.
  - Opsi **"Trust This Device"** — perangkat dipercaya selama 30 hari via cookie `2fa_device_trust`.
  - Tabel `user_trusted_devices` menyimpan token hash SHA-256 dengan expiry 30 hari.
  - API Login mendukung 2FA melalui field `two_factor_code` / `recovery_code` dan header `X-Device-Trust-Token`.
- **Purchase Order (PO) Error 500 Fix**:
  - Memperbaiki kolom relasi di `PurchaseOrderController@index` (`whereColumn('purchase_orders.pr_id', 'purchase_requests.id')` sebelumnya `purchase_request_id` yang menyebabkan SQL column not found).
  - Menghapus klausa `where('is_active', true)` pada model `InventoryItem` di controller index karena tabel `inventory_items` tidak memiliki kolom `is_active`.
  - Memperbaiki pemetaan kolom input pada `PurchaseOrderController@store` (`pr_id`, default `order_date`, dan `po_id` untuk `PurchaseOrderItem`).
  - Menambahkan template `resources/views/procurement/purchase_orders/show.blade.php` untuk direct web view.
- **Tampilan Toolbar PoS Header Inline (Segaris)**:
  - Tombol-tombol aksi atas pada halaman PoS (`/pos`): status shift aktif, Kas Kecil, Hold Order, Tutup/Buka Shift, dan Cabang diratakan dalam satu baris (single line, `flex-nowrap`, `overflow-x-auto`, unified `h-9` buttons) sehingga tidak pecah baris di berbagai resolusi layar.
- **VPS Deployment & Auto-Sync**:
  - Repo GitHub `master` & `main` sudah sinkron dan dipush ke `https://github.com/fk0u/IstanaLaundryManagementSystem.git`.
  - VPS (`157.10.161.42`) telah diupdate ke commit terbaru (`main`), dependency dikunci ke platform PHP 8.3.6, asset frontend di-compile dengan Vite (`npm run build`), migration & cache optimization dijalankan (HTTP 200 OK di `https://istanasystem.alk-tech.my.id`).
  - Auto-sync cron job (`*/5 * * * * deploy.sh`) aktif di VPS untuk mendeteksi commit baru di `origin/main` setiap 5 menit.
