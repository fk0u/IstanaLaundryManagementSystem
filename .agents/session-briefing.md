# Session Briefing — Istana Laundry ERP Complete & Enhanced

## System Status
- **Docker Stack**: Running (Nginx, PHP 8.4-FPM, MySQL 8, Vite)
- **Primary Color**: `#FF6600` (Istana Laundry Orange)
- **Role Count**: 8 User Roles (Owner, Super Admin, Branch Admin, Workshop Admin, Cashier, Workshop Staff, CS/Marketing, Finance)

## Completed Core Modules & Enhancements (100% Complete)
1. **POS & Billing**: Cashier interface, input manual kode kupon promo + validasi kupon, discount promo, loyalty points, auto-calc, print receipt 58/80mm, WhatsApp blast.
2. **Production & QR Tracking**: 8-stage laundry flow (TERIMA -> PILAH -> CUCI -> KERING -> LIPAT -> CEK -> SIAP -> DIAMBIL), QR code tracking, pagination per 10 items.
3. **CRM & Loyalty**: Member code generation, tier progression (Bronze, Silver, Gold, Platinum), loyalty points logs & redemption.
4. **Inventory & Procurement**: Purchase Requests (PR), Purchase Orders (PO), Goods Received Notes (GRN), FIFO batch stock deduction.
5. **Finance & Accounting**: Chart of Accounts (COA), Journal Ledger, Auto-Journaling (Order, GRN, Payroll, Depreciation), Accounting Period Closing, Financial Reports, Finance-specific Dashboard metrics (Arus Kas, Piutang Unpaid, Omset).
6. **HR & Payroll**: Employee management dengan biodata lengkap (NIK, Tempat/Tgl Lahir, Usia otomatis, No. HP, Alamat, Nama Bank, No. Rekening, Pemilik Rekening), Modal Edit Staf, Pilihan Generate Payroll **Konsolidasi Seluruh Cabang (Semua Karyawan sekaligus)**, Status Payroll **DRAFT / FINAL (DIKUNCI)** dengan tombol kunci "Finalkan Payroll", pemisahan BPJS Kesehatan & BPJS Ketenagakerjaan, Tunjangan Transportasi otomatis, Insentif Workload Workshop, Printable Payslip.
7. **Fixed Assets & Depreciation**: Asset registration, Straight-Line & Declining Balance Depreciation Schedule, Penandaan Cabang, Acquisition Date dengan label jelas, Catatan & Tanggal Maintenance Terakhir, Detail View Interaktif.
8. **Performance Monitoring & Analytics**: Filter tanggal/periode, Cashier sales leaderboard & rincian harian per kasir, Workshop staff productivity metrics & order status breakdown.
10. **Admin-Only User Management**: Manajemen pengguna internal di `/users` untuk pendaftaran akun staf (Kasir, Workshop, Finance, Admin Cabang), pengubahan role Spatie & penautan cabang, reset password staf, dan penonaktifan akun.
11. **Production-Grade WhatsApp Notification**: Pesan otomatis "Siap Diambil" (`/invoices/{order}/ready-whatsapp`) untuk order di status `SIAP`/`DIAMBIL` dengan rincian nota dan tautan pelacakan publik (`/track?order_number=...`).
12. **POS Tablet REST API Expansion**: Endpoint REST API berotentikasi Sanctum (`/api/pos/services`, `/api/pos/customers`, `/api/pos/orders`) untuk aplikasi POS Tablet/Mobile.
13. **Workload-Linked Payroll HR**: Penghitungan otomatis insentif produktivitas Kg & Pcs workshop pada slip gaji.
14. **Closing Checklist & CSV Financial Reports Export**: Interface penutupan buku bulanan (`/finance/closing-checklist`) dan tombol ekspor CSV laporan keuangan.

## Last Session (30 Jul 2026 — TEST 2 Phase)
- **Completed**: Issues #31, #30, #29 (P0 bugs)
  - #31: Payroll zero fix — Employee query bypasses BranchScoped global scope + float cast safety
  - #30: Dashboard chart scope — branchId fallback logic corrected for global view
  - #29: Timezone WITA — config/app.php reads APP_TIMEZONE env, default Asia/Makassar
- **Branches ready for PR**: `fix/payroll-zero-calc`, `fix/dashboard-chart-scope`, `fix/timezone-wita`
- **Remaining TEST 2 items**: #32 (Production search), #33 (CRM stats), #34 (Receipt link), #35 (Finance charts), #36 (Exports)
- **User note**: `.env` lokal perlu manual update `APP_TIMEZONE=Asia/Makassar`
