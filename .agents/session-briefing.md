# Session Briefing — Istana Laundry Management System

**Last Updated:** 2026-08-05 23:35 WITA

## Current Status
- **2FA Login Challenge Flow (Web + API) — LIVE**:
  - User yang mengaktifkan 2FA akan diminta kode TOTP saat login melalui halaman `/two-factor-challenge`.
  - Opsi **"Trust This Device"** — perangkat dipercaya selama 30 hari via cookie `2fa_device_trust`.
  - Tabel `user_trusted_devices` menyimpan token hash SHA-256 dengan expiry 30 hari.
  - API Login mendukung 2FA melalui field `two_factor_code` / `recovery_code` dan header `X-Device-Trust-Token`.
- **Purchase Order (PO) Error 500 Fix**:
  - Memperbaiki kolom relasi di `PurchaseOrderController@index` (`whereColumn('purchase_orders.pr_id', 'purchase_requests.id')` sebelumnya `purchase_request_id` yang menyebabkan SQL column not found).
  - Memperbaiki pemetaan kolom input pada `PurchaseOrderController@store` (`pr_id`, default `order_date`, dan `po_id` untuk `PurchaseOrderItem`).
  - Menambahkan template `resources/views/procurement/purchase_orders/show.blade.php` untuk direct web view.
- **Tampilan Toolbar PoS Header Inline (Segaris)**:
  - Tombol-tombol aksi atas pada halaman PoS (`/pos`): status shift aktif, Kas Kecil, Hold Order, Tutup/Buka Shift, dan Cabang diratakan dalam satu baris (single line, `flex-nowrap`, `overflow-x-auto`, unified `h-9` buttons) sehingga tidak pecah baris di berbagai resolusi layar.
