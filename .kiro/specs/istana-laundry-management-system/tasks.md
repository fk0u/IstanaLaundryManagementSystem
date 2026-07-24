# Implementation Plan: Istana Laundry Management System

## Overview

Rencana implementasi ini mencakup seluruh sistem Semi-ERP laundry multi-cabang menggunakan Laravel 13 + PHP 8.5+, Blade + Alpine.js 3, Tailwind CSS v4, MySQL/SQLite, Spatie Permission v8, dan berbagai paket pendukung. Implementasi dibagi dalam 8 sprint (21 minggu) mengikuti Development Roadmap di SRS, dengan total 14 property-based tests yang menjamin kebenaran logika bisnis kritis.

Setiap task dirancang selesai dalam 1–3 hari. Tasks bertanda `*` adalah opsional (test) dan tidak wajib diimplementasikan oleh coding agent kecuali diminta secara eksplisit.

---

## Tasks


## Sprint 1–2: Foundation (5 Minggu)

---

- [ ] 1. Project Setup & Configuration
  - [ ] 1.1 Inisialisasi proyek Laravel 13 dengan PHP 8.5+
    - Jalankan `composer create-project laravel/laravel istana-laundry "^13.0" --prefer-dist`
    - Konfigurasi `.env` untuk dua environment: SQLite (dev) dan MySQL 8 (production)
    - Set `APP_NAME=Istana Laundry`, `APP_TIMEZONE=Asia/Jakarta`, `APP_LOCALE=id`
    - Aktifkan `APP_DEBUG=false` di production template `.env.example`
    - _Requirements: 15.1, 15.5_

  - [ ] 1.2 Instalasi dan konfigurasi semua package dependencies
    - Install via Composer: `spatie/laravel-permission:^8.0`, `maatwebsite/excel:^3.1`, `barryvdh/laravel-dompdf:^3.0`, `simplesoftwareio/simple-qrcode:^4.2`, `spatie/laravel-backup:^9.0`
    - Install via npm: `alpinejs@^3`, `@tailwindcss/vite@^4`, `chart.js@^4`
    - Publish config files untuk semua package: `vendor:publish --tag=...`
    - Konfigurasi `config/permission.php`: set `teams = false`, cache duration 24h
    - _Requirements: 15.1, 15.9_

  - [ ] 1.3 Setup Tailwind CSS v4 dengan @tailwindcss/vite dan konfigurasi brand
    - Konfigurasi `vite.config.js` dengan plugin `@tailwindcss/vite`
    - Buat file `resources/css/app.css` dengan import Tailwind dan CSS custom properties
    - Definisikan custom color: `--color-primary: #FF6600` (oranye brand)
    - Konfigurasi dark mode dengan class strategy: `darkMode: 'class'`
    - Setup font: Inter atau Poppins via Google Fonts CDN
    - _Requirements: 15.2, 15.3_

  - [ ] 1.4 Instalasi dan konfigurasi Laravel Breeze
    - Jalankan `php artisan breeze:install blade`
    - Hapus scaffolding default Breeze yang tidak diperlukan (dashboard placeholder)
    - Sesuaikan views login/register dengan branding Istana Laundry (#FF6600)
    - Tambahkan kolom `branch_id`, `is_active`, `last_login_at`, `login_attempts`, `locked_until` ke tabel users migration
    - _Requirements: 1.9, 15.3_

  - [ ] 1.5 Konfigurasi filesystem, cache, queue, dan session
    - Set queue driver ke `database` di `.env` (production: `redis` opsional)
    - Set session driver ke `database` dengan lifetime 120 menit (default) atau 43200 menit (remember me)
    - Set cache driver ke `file` (production: `redis` opsional)
    - Konfigurasi `config/filesystems.php` untuk penyimpanan lokal dan QR code
    - Konfigurasi Spatie Backup: daily schedule, keep 7 backups, backup ke `storage/app/backups`
    - _Requirements: 15.7_


- [ ] 2. Database Migrations — Semua 40+ Tabel
  - [ ] 2.1 Migrasi tabel foundation: branches, workshops, users (modifikasi)
    - Buat migration `create_branches_table`: kolom sesuai schema (code, name, address, phone, email, lat, lng, is_active, soft deletes), tambahkan index pada `code` dan `is_active`
    - Buat migration `create_workshops_table`: FK ke branches, soft deletes
    - Modifikasi migration users (dari Breeze): tambah kolom `branch_id` (FK nullable), `is_active`, `last_login_at`, `login_attempts`, `locked_until`
    - _Requirements: 1.1, 2.1, 2.8_

  - [ ] 2.2 Migrasi tabel master data: services, service_branch_prices, service_price_histories, suppliers
    - Buat `create_services_table`: kolom type ENUM(`kilogram`,`satuan`,`kategori`), unit, base_price, est_duration_hours
    - Buat `create_service_branch_prices_table`: FK ke services dan branches, price, is_active; unique constraint (service_id, branch_id)
    - Buat `create_service_price_histories_table`: old_price, new_price, changed_by, changed_at
    - Buat `create_suppliers_table`: name, phone, email, address, npwp, is_active
    - _Requirements: 3.1, 3.2, 3.7_

  - [ ] 2.3 Migrasi tabel customers dan loyalty
    - Buat `create_customers_table`: branch_id (FK), name, phone (UNIQUE), email, address, member_code (UNIQUE), loyalty_tier ENUM, loyalty_points, total_spent, transaction_count, last_transaction_at; index pada phone, member_code, branch_id
    - Buat `create_loyalty_point_logs_table`: customer_id, order_id (nullable), points (signed INT), type ENUM(`earn`,`redeem`,`expire`,`adjust`), balance_after, description, expired_at
    - _Requirements: 6.1, 6.2, 6.4_

  - [ ] 2.4 Migrasi tabel promotions
    - Buat `create_promotions_table`: branch_id (nullable = all branches), name, code (UNIQUE nullable), type ENUM(`percent`,`nominal`,`buy_x_get_y`,`loyalty_tier`), value, min_transaction, service_id (nullable), applicable_tier (nullable), usage_limit (nullable), usage_count, per_customer_limit (nullable), start_date, end_date, is_active
    - _Requirements: 7.1, 7.6_

  - [ ] 2.5 Migrasi tabel orders dan order_items
    - Buat `create_orders_table`: order_number (UNIQUE), branch_id, workshop_id (nullable), customer_id (nullable), cashier_id, promo_id (nullable), production_status ENUM(8 status), payment_method ENUM, payment_status ENUM, subtotal, discount_amount, points_used, tax_amount, total, paid_amount, change_amount, notes, qr_code_path, estimated_done_at, paid_at, soft deletes; semua index yang ditentukan di schema
    - Buat `create_order_items_table`: order_id, service_id, quantity DECIMAL, unit, unit_price (snapshot), discount, subtotal, notes
    - Buat `create_order_sequence_counters_table`: branch_id, year_month CHAR(6), last_sequence; unique (branch_id, year_month)
    - _Requirements: 4.1, 4.4, 4.6_

  - [ ] 2.6 Migrasi tabel production dan refunds
    - Buat `create_production_status_logs_table`: order_id, status ENUM(8 status), updated_by, notes, created_at (timestamp only, no updated_at)
    - Buat `create_refunds_table`: order_id, branch_id, requested_by, amount, reason, status ENUM(6 status), 4 approval timestamps, processed_at
    - _Requirements: 5.1, 5.6, 4.11_

  - [ ] 2.7 Migrasi tabel chart_of_accounts, journals, journal_lines, accounting_periods
    - Buat `create_chart_of_accounts_table`: parent_id (self-referencing FK nullable), code (UNIQUE), name, type ENUM(5), normal_balance ENUM, level TINYINT, is_active, is_system
    - Buat `create_accounting_periods_table`: branch_id, month, year, status ENUM, closed_at, closed_by; unique (branch_id, month, year)
    - Buat `create_journals_table`: branch_id, accounting_period_id (FK), reference, source_type, source_id (polymorphic), type ENUM, description, date, status ENUM, reversed_by (self-referencing nullable), created_by, posted_at; composite index (branch_id, date) dan (source_type, source_id)
    - Buat `create_journal_lines_table`: journal_id, account_id, debit DECIMAL, credit DECIMAL, description; tambahkan DB check constraint `(debit = 0 OR credit = 0)`
    - _Requirements: 9.1, 9.2, 9.3, 9.11_

  - [ ] 2.8 Migrasi tabel inventory: inventory_items, inventory_batches
    - Buat `create_inventory_items_table`: branch_id, name, sku (UNIQUE), category, unit, min_stock DECIMAL, current_stock DECIMAL
    - Buat `create_inventory_batches_table`: item_id, grn_id, batch_number, quantity, remaining_qty, unit_cost, received_date; composite index (item_id, received_date) untuk mendukung FIFO ordering
    - _Requirements: 8.1, 8.6, 8.7_

  - [ ] 2.9 Migrasi tabel procurement: purchase_requests, purchase_request_items, purchase_orders, purchase_order_items, goods_received_notes, grn_items
    - Buat `create_purchase_requests_table`: branch_id, pr_number (UNIQUE), requested_by, approved_by (nullable), status ENUM(4), request_date, notes
    - Buat `create_purchase_request_items_table`: pr_id, item_id, quantity, unit_cost_estimate (nullable), notes
    - Buat `create_purchase_orders_table`: pr_id (nullable), branch_id, po_number (UNIQUE), supplier_id, status ENUM(6), subtotal, tax_amount, total, order_date, expected_date
    - Buat `create_purchase_order_items_table`: po_id, item_id, quantity, unit_cost, subtotal, received_qty (default 0)
    - Buat `create_goods_received_notes_table`: po_id, grn_number (UNIQUE), received_by, status ENUM, received_date, notes
    - Buat `create_grn_items_table`: grn_id, item_id, po_item_id, quantity, unit_cost
    - _Requirements: 8.3, 8.4, 8.5, 8.6_

  - [ ] 2.10 Migrasi tabel HR: employees, salary_histories, attendances, payrolls, payroll_items
    - Buat `create_employees_table`: branch_id, user_id (nullable FK), nik (UNIQUE), name, position, base_salary, is_active, joined_at
    - Buat `create_salary_histories_table`: employee_id, old_salary, new_salary, effective_date, notes, changed_by
    - Buat `create_attendances_table`: employee_id, date, status ENUM(5), check_in, check_out, notes; unique constraint (employee_id, date)
    - Buat `create_payrolls_table`: branch_id, month, year, status ENUM(3), processed_at, created_by; unique (branch_id, month, year)
    - Buat `create_payroll_items_table`: payroll_id, employee_id, base_salary, allowance, deduction, attendance_days, work_days, net_salary
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

  - [ ] 2.11 Migrasi tabel fixed assets: fixed_assets, depreciation_schedules
    - Buat `create_fixed_assets_table`: branch_id, account_id (FK COA), asset_code (UNIQUE), name, category, acquisition_date, acquisition_cost, salvage_value, useful_life_months SMALLINT, depreciation_method ENUM, accumulated_depreciation, book_value, is_active, disposal_date (nullable), disposal_value (nullable)
    - Buat `create_depreciation_schedules_table`: asset_id, period_date, depreciation_amount, accumulated, book_value, is_posted, journal_id (nullable); unique (asset_id, period_date)
    - _Requirements: 11.1, 11.2_

  - [ ] 2.12 Migrasi tabel audit_logs dan sistem
    - Buat `create_audit_logs_table`: user_id (nullable), action, model_type, model_id, old_values JSON, new_values JSON, ip_address, user_agent; indexes pada user_id, (model_type, model_id), created_at
    - Pastikan semua FK constraints terdefinisi dengan benar dan semua indexes ada
    - Jalankan `php artisan migrate --seed` dan verifikasi semua tabel terbuat tanpa error
    - _Requirements: 15.8_


- [ ] 3. Eloquent Models, Relationships, dan Traits
  - [ ] 3.1 Buat model Branch dengan relationships dan trait BranchScoped (untuk super-level users)
    - Model `Branch` dengan fillable fields sesuai schema
    - Relationships: `hasMany` workshops, users, customers, orders, etc.
    - Soft deletes trait
    - Accessor untuk formatted address, latitude-longitude
    - Scope `active()` untuk branch aktif
    - _Requirements: 2.1, 2.8_

  - [ ] 3.2 Buat model Workshop dengan relationships
    - Model `Workshop` dengan fillable fields
    - `belongsTo` Branch
    - Scope `active()`
    - _Requirements: 2.3, 2.9_

  - [ ] 3.3 Modifikasi model User dengan relationships dan traits untuk RBAC
    - Tambahkan trait `HasRoles` dari Spatie Permission
    - `belongsTo` Branch (nullable)
    - Relationships: `hasMany` orders (as cashier), audit logs, etc.
    - Accessor `is_locked` untuk cek apakah locked_until > now()
    - Accessor `can_login` untuk cek is_active dan is_locked
    - _Requirements: 1.1, 1.3, 1.11_

  - [ ] 3.4 Buat trait BranchScoped untuk auto-filtering berdasarkan branch_id
    - File: `app/Models/Traits/BranchScoped.php`
    - Tambahkan global scope yang otomatis filter query dengan `where('branch_id', auth()->user()->branch_id)` jika user branch-level role
    - Cek role user: jika `Developer`, `Owner`, atau `Super_Admin` → skip scope
    - Gunakan trait ini di semua model yang perlu branch isolation (Customer, Order, InventoryItem, etc.)
    - _Requirements: 2.6, 2.7, 15.10_

  - [ ] 3.5 Buat model Customer dengan relationships dan trait BranchScoped
    - Fillable fields sesuai schema
    - Trait `BranchScoped`
    - `belongsTo` Branch
    - `hasMany` orders, loyalty_point_logs
    - Accessor untuk formatted phone, tier badge
    - Scope `tier($tier)`, `activeMembers()`
    - _Requirements: 6.1, 6.2_

  - [ ] 3.6 Buat model Service, ServiceBranchPrice, ServicePriceHistory
    - Model `Service` dengan relationships: `hasMany` serviceBranchPrices, `hasMany` priceHistories
    - Model `ServiceBranchPrice` dengan `belongsTo` service dan branch
    - Model `ServicePriceHistory` dengan `belongsTo` service dan branch
    - Scope `active()` di Service
    - _Requirements: 3.1, 3.2_

  - [ ] 3.7 Buat model Order, OrderItem, OrderSequenceCounter dengan trait BranchScoped
    - Model `Order` dengan trait `BranchScoped`, soft deletes
    - Relationships: `belongsTo` branch, workshop, customer, cashier (User), promo; `hasMany` orderItems, productionStatusLogs; `morphMany` journals
    - Casts: production_status ENUM, payment_method ENUM, payment_status ENUM
    - Accessor untuk formatted order_number, status badge, payment badge
    - Scope `byStatus($status)`, `paid()`, `pending()`
    - Model `OrderItem` dengan `belongsTo` order dan service
    - Model `OrderSequenceCounter` untuk generate order_number sequence
    - _Requirements: 4.1, 4.2, 4.6_

  - [ ] 3.8 Buat model ProductionStatusLog dan Refund dengan trait BranchScoped
    - Model `ProductionStatusLog` dengan `belongsTo` order dan updatedBy (User); timestamp `created_at` only
    - Model `Refund` dengan `belongsTo` order, branch, requestedBy (User); trait `BranchScoped`
    - Casts: status ENUM di Refund
    - _Requirements: 5.1, 5.6, 4.11_

  - [ ] 3.9 Buat model LoyaltyPointLog dan Promotion dengan trait BranchScoped
    - Model `LoyaltyPointLog` dengan `belongsTo` customer dan order (nullable)
    - Casts: type ENUM, expired_at timestamp
    - Model `Promotion` dengan trait `BranchScoped` (nullable branch_id = all branches)
    - Relationships: `belongsTo` branch (nullable), service (nullable)
    - Scope `active()`, `forTier($tier)`
    - _Requirements: 6.4, 6.6, 7.1_

  - [ ] 3.10 Buat model ChartOfAccount, Journal, JournalLine, AccountingPeriod
    - Model `ChartOfAccount` dengan self-referencing `parent_id`: `belongsTo` parent (self), `hasMany` children (self), `hasMany` journalLines
    - Casts: type ENUM, normal_balance ENUM
    - Scope `active()`, `byType($type)`, `systemAccounts()`
    - Model `AccountingPeriod` dengan trait `BranchScoped`, `belongsTo` branch, closedBy (User)
    - Casts: status ENUM
    - Scope `open()`, `closed()`
    - Model `Journal` dengan trait `BranchScoped`, `belongsTo` branch, accountingPeriod, createdBy (User), reversedBy (self-referencing nullable); `hasMany` journalLines; morphTo source (Order, GRN, Payroll, etc.)
    - Casts: type ENUM, status ENUM, date
    - Model `JournalLine` dengan `belongsTo` journal dan account
    - _Requirements: 9.1, 9.2, 9.3, 9.11_

  - [ ] 3.11 Buat model InventoryItem, InventoryBatch, Supplier dengan trait BranchScoped
    - Model `InventoryItem` dengan trait `BranchScoped`, `belongsTo` branch, `hasMany` batches
    - Model `InventoryBatch` dengan `belongsTo` item dan grn; scope `fifoOrder()` → `orderBy('received_date', 'asc')`
    - Model `Supplier` tanpa branch scope (global supplier pool)
    - _Requirements: 8.1, 8.6_

  - [ ] 3.12 Buat model PurchaseRequest, PurchaseRequestItem, PurchaseOrder, PurchaseOrderItem, GoodsReceivedNote, GRNItem dengan trait BranchScoped
    - Model `PurchaseRequest` dengan trait `BranchScoped`, `belongsTo` branch, requestedBy, approvedBy; `hasMany` items; `hasOne` purchaseOrder
    - Model `PurchaseRequestItem` dengan `belongsTo` pr dan inventoryItem
    - Model `PurchaseOrder` dengan trait `BranchScoped`, `belongsTo` pr, branch, supplier; `hasMany` items, `hasMany` grns
    - Model `PurchaseOrderItem` dengan `belongsTo` po dan inventoryItem
    - Model `GoodsReceivedNote` dengan `belongsTo` po, receivedBy (User); `hasMany` grnItems; `morphMany` journals
    - Model `GRNItem` dengan `belongsTo` grn, inventoryItem, poItem
    - _Requirements: 8.3, 8.4, 8.5, 8.6_

  - [ ] 3.13 Buat model Employee, SalaryHistory, Attendance, Payroll, PayrollItem dengan trait BranchScoped
    - Model `Employee` dengan trait `BranchScoped`, `belongsTo` branch dan user (nullable); `hasMany` salaryHistories, attendances, payrollItems
    - Model `SalaryHistory` dengan `belongsTo` employee dan changedBy (User)
    - Model `Attendance` dengan `belongsTo` employee; unique constraint validation via model observer
    - Model `Payroll` dengan trait `BranchScoped`, `belongsTo` branch, createdBy; `hasMany` payrollItems; `morphMany` journals
    - Model `PayrollItem` dengan `belongsTo` payroll dan employee
    - _Requirements: 10.1, 10.2, 10.3_

  - [ ] 3.14 Buat model FixedAsset, DepreciationSchedule dengan trait BranchScoped
    - Model `FixedAsset` dengan trait `BranchScoped`, `belongsTo` branch dan account (COA); `hasMany` depreciationSchedules
    - Casts: depreciation_method ENUM, acquisition_date, disposal_date
    - Model `DepreciationSchedule` dengan `belongsTo` asset dan journal (nullable)
    - Scope `unposted()`, `forPeriod($year, $month)`
    - _Requirements: 11.1, 11.2_

  - [ ] 3.15 Buat model AuditLog (tanpa trait BranchScoped)
    - Model `AuditLog` dengan `belongsTo` user (nullable)
    - Casts: old_values JSON, new_values JSON
    - Tidak ada BranchScoped trait karena audit bersifat global untuk compliance
    - _Requirements: 15.8_


- [ ] 4. Authentication & RBAC (Laravel Breeze + Spatie Permission)
  - [ ] 4.1 Buat LoginController dengan account lockout dan rate limiter
    - Override default Breeze `LoginRequest` atau buat `AuthenticatedSessionController` custom
    - Implementasi cek `locked_until`: jika `locked_until > now()` → return error tanpa cek password
    - Setelah 5 kali gagal: set `locked_until = now()->addMinutes(15)`, log ke `audit_logs`
    - Setelah login berhasil: reset `login_attempts = 0`, update `last_login_at`, log ke `audit_logs`
    - Implementasi rate limiter: `RateLimiter::tooManyAttempts('login:' . request()->ip(), 10)` → HTTP 429
    - _Requirements: 1.2, 1.3, 1.5, 1.8_

  - [ ] 4.2 Implementasi redirect berdasarkan role setelah login
    - Buat `app/Http/Middleware/RedirectBasedOnRole.php`
    - Switch case berdasarkan primary role: Owner → `/dashboard/owner`, Branch_Admin → `/dashboard/branch`, Cashier → `/pos`, Workshop_Staff/Workshop_Admin → `/production`, Finance → `/dashboard/finance`, CS_Marketing → `/customers`, default → `/dashboard`
    - _Requirements: 1.4_

  - [ ] 4.3 Seeder untuk roles, permissions, dan default users
    - Buat `RolesAndPermissionsSeeder`: seed semua permissions sesuai daftar di design.md (orders.view, orders.create, production.update, customers.view, journals.create, dst.)
    - Seed 8 roles: Developer, Owner, Super_Admin, Branch_Admin, Workshop_Admin, Cashier, Workshop_Staff, CS_Marketing, Finance
    - Assign permissions ke setiap role sesuai tabel RBAC di design.md
    - Seed 1 default Developer user: `developer@istanalaundry.com` / `password`
    - Seed demo users untuk setiap role
    - _Requirements: 1.1, 1.9_

  - [ ] 4.4 Implementasi BranchScopeMiddleware
    - Buat `app/Http/Middleware/BranchScopeMiddleware.php`
    - Cek apakah user memiliki branch-level role (bukan Developer/Owner/Super_Admin)
    - Jika ya: simpan `branch_id` user ke `app()->instance('branch_scope', $user->branch_id)` atau session
    - Trait `BranchScoped` di model akan membaca nilai ini untuk apply global scope
    - Register middleware di `bootstrap/app.php` atau `Kernel.php` pada web middleware group
    - _Requirements: 2.6, 2.7, 15.10_

  - [ ] 4.5 Implementasi fitur Remember Me dan password reset
    - Konfigurasi `remember_token` dengan cookie lifetime 30 hari di `config/auth.php`
    - Implementasi password reset flow (sudah ada di Breeze, sesuaikan styling)
    - Validasi password strength: minimum 8 karakter, mengandung huruf dan angka (regex di FormRequest)
    - _Requirements: 1.7, 1.12_

  - [ ] 4.6 Buat AuditLogObserver dan AuditLogService
    - Buat `app/Services/AuditLogService.php`: method `log($action, $model = null, $oldValues = null, $newValues = null)` yang menyimpan ke `audit_logs`
    - Tangkap: user_id, action, model_type, model_id, old_values, new_values, ip_address, user_agent
    - Register service di AppServiceProvider
    - _Requirements: 1.8, 15.8_

  - [ ]* 4.7 Tulis property-based test untuk Property 1 (Account Lockout)
    - **Property 1: Account Lockout After Consecutive Failures**
    - Dengan PHPUnit data providers atau loop: untuk setiap sequence tepat 5 gagal → login ke-6 harus ditolak tanpa cek credentials (locked_until di-set, error dikembalikan)
    - Uji juga: setelah 4 kali gagal, login ke-5 masih cek credentials; setelah lock berakhir, login normal bisa kembali
    - **Validates: Requirements 1.3**

  - [ ]* 4.8 Tulis feature test untuk authentication flow
    - Test: login success → redirect ke dashboard yang tepat per role
    - Test: login failed 5x → akun terkunci, error message tampil
    - Test: rate limiter 10x dari IP yang sama → HTTP 429
    - Test: remember me = cookie 30 hari; tanpa remember me = session standard
    - Test: logout → sesi dihapus, redirect ke login
    - _Requirements: 1.2, 1.3, 1.4, 1.5, 1.6, 1.7_

- [ ] 5. Checkpoint — Jalankan semua tests dan validasi setup
  - Pastikan semua migrations berjalan tanpa error (`php artisan migrate:fresh --seed`)
  - Pastikan login/logout flow berfungsi dengan correct role-based redirect
  - Pastikan BranchScopeMiddleware aktif dan filtering data berdasarkan branch_id user
  - Pastikan semua property tests dan feature tests pass
  - _Ensure all tests pass, ask the user if questions arise._


- [ ] 6. Branch & Workshop Management
  - [ ] 6.1 Buat BranchController, BranchRepository, BranchService
    - `BranchRepository`: interface + implementation dengan method `all()`, `findById()`, `create()`, `update()`, `deactivate()`
    - `BranchService`: `createBranch(array $data)` — generate unique branch code, validate, save; `deactivateBranch($id)` — cek tidak ada active orders dulu
    - `BranchController`: resource controller (index, create, store, show, edit, update, destroy); gunakan `BranchRequest` untuk validasi
    - _Requirements: 2.1, 2.2, 2.4, 2.5_

  - [ ] 6.2 Buat WorkshopController, form requests, dan CRUD views
    - WorkshopController resource, `WorkshopRequest` validasi (name required, branch_id required, FK valid)
    - `BranchService::associateWorkshop($branchId, $workshopData)`
    - View: `resources/views/workshops/index.blade.php`, `create.blade.php`, `edit.blade.php`
    - _Requirements: 2.3, 2.9_

  - [ ] 6.3 Buat Blade views untuk Branch Management (index, create, edit, show)
    - `branches/index.blade.php`: table dengan kolom code, name, phone, is_active, action buttons; pagination
    - `branches/create.blade.php` & `edit.blade.php`: form dengan validasi Alpine.js real-time
    - `branches/show.blade.php`: detail cabang + daftar workshops + statistik ringkas
    - Implementasi toggle aktif/nonaktif tanpa hapus data historis
    - _Requirements: 2.4, 2.5_

  - [ ] 6.4 Buat Seeder untuk Branch dan Workshop data awal
    - Seed minimal 2 cabang contoh: `JKT01 - Jakarta Pusat`, `JKT02 - Jakarta Selatan`
    - Seed 1–2 workshop per cabang
    - _Requirements: 2.10_

  - [ ]* 6.5 Tulis property-based test untuk Property 2 (Branch Scope Query Isolation)
    - **Property 2: Branch Scope Query Isolation**
    - Buat multiple branches dan data per branch; untuk setiap model dengan BranchScoped dan setiap branch-level user role, pastikan query hanya mengembalikan data branch_id yang sesuai
    - Uji semua model: Customer, Order, InventoryItem, Journal, Employee, FixedAsset, dll.
    - **Validates: Requirements 2.6, 2.7, 15.10**


- [ ] 7. Master Data Management (Services, COA, Suppliers)
  - [ ] 7.1 Buat ServiceController, ServiceRepository, ServiceService, dan form requests
    - `ServiceRepository`: interface + implementation
    - `ServiceService`: `createService()`, `updateService()` — saat update harga: simpan ke `service_price_histories`, update `service_branch_prices` jika ada override per cabang
    - `ServiceController`: resource controller, gunakan `ServiceRequest` untuk validasi (nama required, type valid, base_price > 0, est_duration_hours > 0)
    - _Requirements: 3.1, 3.2, 3.8_

  - [ ] 7.2 Buat Blade views untuk Services CRUD
    - `services/index.blade.php`: table dengan filter by type, status; badge untuk jenis layanan (kg/pcs/set); toggle aktif/nonaktif
    - `services/create.blade.php` & `edit.blade.php`: dynamic form — saat type berubah, placeholder unit berubah via Alpine.js
    - `services/show.blade.php`: detail layanan + riwayat harga per cabang
    - `services/prices.blade.php`: manage override harga per cabang (service_branch_prices)
    - _Requirements: 3.1, 3.7_

  - [ ] 7.3 Buat ChartOfAccountController, COARepository, dan Blade views
    - `COARepository`: `getHierarchy()` untuk tree structure, `getByType($type)`, `findByCode($code)`
    - `ChartOfAccountController`: resource controller; prevent delete jika sudah ada journal_lines
    - `ChartOfAccountRequest`: validate code unique, type valid, parent_id FK valid, level konsisten dengan parent
    - Views: `coa/index.blade.php` — tampilkan sebagai tree accordion (parent-child), search by code/name; `create/edit.blade.php` — select parent dari dropdown
    - _Requirements: 3.3, 3.4, 3.5_

  - [ ] 7.4 Buat SupplierController dan Blade views
    - Resource controller untuk CRUD supplier
    - `SupplierRequest` validasi (nama required, NPWP format optional)
    - `suppliers/index.blade.php`: table dengan search
    - `suppliers/create/edit.blade.php`: form standard
    - _Requirements: 8.3_

  - [ ] 7.5 Buat DatabaseSeeder dengan COA, Services, dan Reference Data
    - Buat `ChartOfAccountSeeder`: seed minimal 50 akun standar bisnis laundry (aset kas, piutang, inventori, peralatan; liabilitas hutang; ekuitas modal; pendapatan jasa; beban gaji, listrik, bahan, depresiasi, dll.)
    - Buat `ServiceSeeder`: seed 10+ layanan dasar (cuci kiloan, cuci satuan set, express, dry cleaning, dll.)
    - Buat `SupplierSeeder`: seed 3–5 supplier contoh
    - _Requirements: 3.6_

  - [ ]* 7.6 Tulis unit tests untuk master data validation
    - Test: kode COA unik (duplicate ditolak), akun dengan journal tidak bisa dihapus
    - Test: price history tersimpan saat harga service diupdate
    - Test: branch price override menggunakan harga override, bukan base_price
    - _Requirements: 3.2, 3.4, 3.5_


- [ ] 8. UI Foundation (Blade Layouts, Components, Dark Mode)
  - [ ] 8.1 Buat Blade layout utama dengan sidebar navigasi
    - Buat `resources/views/layouts/app.blade.php`: main authenticated layout dengan sidebar, topbar, content area
    - Buat `resources/views/layouts/guest.blade.php`: layout untuk halaman auth (login, register)
    - Buat `resources/views/layouts/pos.blade.php`: layout khusus POS tanpa sidebar standar
    - Buat `resources/views/layouts/print.blade.php`: layout minimal untuk cetak struk/invoice
    - _Requirements: 12.7, 15.4_

  - [ ] 8.2 Buat sidebar navigasi dengan role-based menu visibility
    - File: `resources/views/components/sidebar.blade.php`
    - Gunakan `@can()` dan `@role()` directives dari Spatie untuk show/hide menu items
    - Menu items: Dashboard, POS, Orders, Production, Customers, Promotions, Inventory, Finance, HR, Assets, Reports, Settings
    - Active state detection berdasarkan current route
    - Mobile responsive: sidebar collapse on small screens, toggle via Alpine.js
    - _Requirements: 12.1_

  - [ ] 8.3 Buat Blade components dasar (card, badge, table, alert, modal)
    - `resources/views/components/card.blade.php`: card wrapper dengan optional header/footer
    - `resources/views/components/badge.blade.php`: badge dengan color variants (success, warning, danger, info, gray)
    - `resources/views/components/stat-card.blade.php`: card statistik dengan icon, nilai, trend
    - `resources/views/components/data-table.blade.php`: wrapper table dengan styling
    - `resources/views/components/modal.blade.php`: Alpine.js driven modal dialog
    - `resources/views/components/alert.blade.php`: flash messages (success, error, warning, info)
    - `resources/views/components/page-header.blade.php`: page title + breadcrumbs + action buttons
    - _Requirements: 15.4_

  - [ ] 8.4 Implementasi dark mode dengan Alpine.js dan localStorage
    - Buat `resources/js/darkmode.js`: toggle class `dark` pada `<html>`, simpan preferensi ke `localStorage`
    - Tambahkan dark mode toggle button di topbar menggunakan Alpine.js `x-data`
    - Pastikan semua Tailwind dark variants (`dark:bg-gray-900`, `dark:text-white`, dll.) terdefinisi
    - Simpan preferensi dark mode per-user ke database saat user toggle (update `users.preferences` JSON column atau session)
    - _Requirements: 15.2, 12.6_

  - [ ] 8.5 Buat topbar dengan notifikasi, user menu, dan branch indicator
    - File: `resources/views/components/topbar.blade.php`
    - Tampilkan nama cabang aktif user (jika branch-level role)
    - Dropdown user menu: profil, ganti password, logout
    - Notifikasi bell icon dengan Alpine.js dropdown (low stock alerts, status ready notifications)
    - _Requirements: 12.1_

  - [ ] 8.6 Buat halaman-halaman auth yang di-restyle sesuai branding
    - `auth/login.blade.php`: logo Istana Laundry, warna #FF6600, form email + password + remember me
    - `auth/register.blade.php`: form registrasi dasar
    - `auth/forgot-password.blade.php` dan `auth/reset-password.blade.php`: styled sesuai branding
    - Pastikan fully responsive (375px → 1920px)
    - _Requirements: 15.3, 15.4_


---

## Sprint 3–4: Core Operations (5 Minggu)

---

- [ ] 9. POS & Billing System
  - [ ] 9.1 Buat OrderService dengan logic pembuatan order dan generate order number
    - Buat `app/Services/Order/OrderService.php`
    - Method `generateOrderNumber($branchCode, $branchId)`: gunakan DB transaction + `lockForUpdate()` pada `order_sequence_counters` untuk menghindari race condition; format `KODE-YYYYMM-XXXX`
    - Method `createOrder(array $data, User $cashier)`: validasi items, hitung total, apply promo, create order dan order_items dalam DB transaction
    - Method `processPayment(Order $order, array $paymentData)`: update payment_status, set paid_at, hitung kembalian; fire event `OrderPaid`
    - _Requirements: 4.1, 4.4, 4.6, 4.7_

  - [ ] 9.2 Buat PromotionService untuk evaluasi dan penerapan promo
    - Buat `app/Services/Promotion/PromotionService.php`
    - Method `evaluatePromos(Order $draft, Customer $customer = null)`: query semua promo aktif yang valid untuk order ini (date range, min_transaction, tier, service); kembalikan promo terbaik (highest discount)
    - Method `applyPromo(Order $draft, Promotion $promo)`: hitung `discount_amount` sesuai tipe (percent, nominal, buy_x_get_y, loyalty_tier)
    - Method `validatePromoQuota(Promotion $promo, Customer $customer = null)`: cek `usage_count < usage_limit` dan per-customer limit
    - _Requirements: 7.3, 7.4, 7.6, 7.7_

  - [ ] 9.3 Buat POSController untuk alur transaksi real-time
    - `POSController::index()`: tampilkan halaman POS dengan daftar services aktif (harga per cabang)
    - `POSController::calculate()` (AJAX): terima items + customer_id + promo_code, return total/diskon/poin/kembalian
    - `POSController::saveDraft()` (AJAX): simpan draft order ke session/DB, return draft_id
    - `POSController::checkout()`: validasi `CheckoutRequest`, panggil `OrderService::createOrder()`, return order ID
    - Implementasi draft auto-save setiap 30 detik menggunakan Alpine.js `setInterval` + `fetch()`
    - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.14, 4.15_

  - [ ] 9.4 Buat Form Request untuk POS: CheckoutRequest, CustomerSearchRequest
    - `CheckoutRequest`: validate items (array required, setiap item harus ada service_id valid dan quantity > 0), payment_method in [`cash`,`transfer`,`invoice`], paid_amount >= total jika cash
    - `CustomerSearchRequest`: validate query (min 3 chars)
    - _Requirements: 4.2, 4.6, 15.11_

  - [ ] 9.5 Buat Blade view halaman POS yang interaktif dengan Alpine.js
    - `pos/index.blade.php`: split layout — kiri: daftar layanan (click to add), kanan: keranjang order
    - Customer search bar (Alpine.js debounced search `x-on:input.debounce.300ms`)
    - Cart item list dengan qty input, unit price, subtotal per baris
    - Total section: subtotal, diskon, poin, pajak, total, nominal bayar (input), kembalian (auto-calc)
    - Modal: registrasi customer baru langsung dari POS
    - Modal: promo code input manual
    - Submit button → konfirmasi → checkout
    - Real-time calculation semuanya menggunakan Alpine.js `x-data` dan computed values
    - _Requirements: 4.2, 4.3, 4.4, 4.5, 4.7, 4.13_

  - [ ] 9.6 Buat OrderObserver untuk auto-journal dan auto-loyalty-points
    - Buat `app/Observers/OrderObserver.php`
    - Event `updated` — jika `payment_status` berubah ke `paid`:
      1. Panggil `JournalService::postOrderJournal($order)` → Dr: Kas/AR; Cr: Pendapatan
      2. Panggil `LoyaltyService::awardPoints($order)` → floor(total/1000) poin
      3. Update `customer.total_spent`, `customer.transaction_count`, `customer.last_transaction_at`
    - Register observer di `AppServiceProvider::boot()`
    - _Requirements: 4.9, 4.10_

  - [ ] 9.7 Buat QR Code generation untuk setiap order
    - Buat `app/Services/QRCodeService.php`
    - Method `generateOrderQR(Order $order)`: buat QR code berisi URL publik tracking order, simpan ke `storage/app/public/qrcodes/{order_number}.svg`
    - Simpan path ke `orders.qr_code_path`
    - Buat symbolic link `php artisan storage:link` di setup script
    - _Requirements: 5.3_

  - [ ] 9.8 Buat receipt dan invoice PDF menggunakan DomPDF
    - Buat `app/Services/PrintService.php`
    - Method `generateThermalReceipt(Order $order)`: render view `prints/receipt.blade.php` sebagai HTML, stream ke browser atau return PDF response
    - Method `generateInvoicePDF(Order $order)`: render view `prints/invoice.blade.php` sebagai PDF
    - `prints/receipt.blade.php`: layout thermal 80mm, order number, QR code, item list, total, kembalian, footer cabang
    - `prints/invoice.blade.php`: format A4, kop surat cabang, logo, nomor faktur terstruktur, detail order, tanda tangan
    - _Requirements: 4.8, 13.7_

  - [ ] 9.9 Buat OrderController untuk manajemen orders dan alur refund
    - `OrderController::index()`: list orders dengan filter (status, tanggal, customer); pagination
    - `OrderController::show()`: detail order + production history + payment info
    - `OrderController::printReceipt()` / `printInvoice()`: stream PDF
    - `OrderController::requestRefund()`: create `Refund` record dengan status `pending`
    - `OrderController::approveRefund()`: advance status sesuai role user (Branch_Admin → `branch_approved`, Finance → `finance_approved`, Owner → `owner_approved`); setelah `owner_approved`: set `completed`, reverse journal, kurangi loyalty points
    - _Requirements: 4.8, 4.11_

  - [ ] 9.10 Buat Blade views untuk manajemen orders
    - `orders/index.blade.php`: table dengan filter sidebar, badge status produksi dan pembayaran
    - `orders/show.blade.php`: detail lengkap order, progress bar status produksi, timeline produksi, tombol print
    - `orders/refund.blade.php`: form request refund dengan reason, amount; tampilkan status approval saat ini
    - _Requirements: 4.8, 4.11_

  - [ ]* 9.11 Tulis property-based test untuk Property 3 (Order Number Format)
    - **Property 3: Order Number Format Invariant**
    - Untuk berbagai kombinasi branch code (3–10 karakter) dan timing (berbagai bulan/tahun), verifikasi bahwa setiap order number cocok dengan regex `^[A-Z0-9]{3,10}-\d{6}-\d{4}$`
    - Verifikasi auto-increment per branch per bulan (sequence reset tiap bulan baru)
    - **Validates: Requirements 4.1**

  - [ ]* 9.12 Tulis property-based test untuk Property 4 (Order Total Calculation)
    - **Property 4: Order Total Calculation Correctness**
    - Untuk kombinasi random items (1–50 items), harga, quantity, dan diskon, verifikasi: `total = SUM(qty × price - item_discount) - discount_amount - points_used + tax_amount`
    - Pastikan tidak ada floating point error (gunakan integer/Decimal arithmetic)
    - **Validates: Requirements 4.4**

  - [ ]* 9.13 Tulis property-based test untuk Property 5 (Cash Change)
    - **Property 5: Cash Change Calculation**
    - Untuk setiap order cash dengan `paid_amount >= total`, verifikasi `change_amount = paid_amount - total` dan `change_amount >= 0`
    - Uji dengan nilai batas: paid_amount = total (kembalian 0), paid_amount = total + 0.01, paid_amount >> total
    - **Validates: Requirements 4.7**

  - [ ]* 9.14 Tulis feature tests untuk POS flow
    - Test: cashier buat order → order_number terbuat → QR code ter-generate → journal ter-posting → poin ter-award
    - Test: promo aktif → auto-apply diskon
    - Test: customer tidak ditemukan → bisa walk-in atau daftar baru
    - Test: draft auto-save dan load kembali
    - _Requirements: 4.1, 4.5, 4.8, 4.9, 4.10, 4.15_

- [ ] 10. Production Tracking + QR Code
  - [ ] 10.1 Buat ProductionService dengan forward-only status transition logic
    - Buat `app/Services/Production/ProductionService.php`
    - Definisikan const `STATUS_SEQUENCE = ['TERIMA','PILAH','CUCI','KERING','LIPAT','CEK','SIAP','DIAMBIL']`
    - Method `updateStatus(Order $order, string $newStatus, User $user, string $notes = null)`:
      1. Cek `indexOf($newStatus) === indexOf($currentStatus) + 1`; jika tidak → throw `InvalidStatusTransitionException`
      2. Cek jika status saat ini `DIAMBIL` → throw exception
      3. Update `orders.production_status`
      4. Insert ke `production_status_logs`
      5. Jika `newStatus === 'SIAP'` → fire event `OrderReady`
    - Method `bulkUpdateStatus(array $orderIds, string $newStatus, User $user)`: loop per order, skip yang tidak valid, return summary
    - _Requirements: 5.1, 5.2, 5.5, 5.6, 5.9_

  - [ ] 10.2 Buat ProductionController, form requests, dan QR scan handler
    - `ProductionController::index()`: list semua order aktif dengan filter status; default tampilkan semua selain DIAMBIL
    - `ProductionController::kanban()`: tampilkan board kanban 8 kolom (setiap kolom = 1 status)
    - `ProductionController::updateStatus($orderId)`: proses update via form POST; pakai `UpdateStatusRequest`
    - `ProductionController::bulkUpdate()`: proses bulk update via form POST; pakai `BulkUpdateRequest`
    - `ProductionController::scanQR($qrCode)`: extract order ID dari QR code path/token, return info order
    - `ProductionController::processScan($qrCode)`: advance status order ke next step
    - _Requirements: 5.4, 5.8, 5.9_

  - [ ] 10.3 Buat Blade views untuk production tracking
    - `production/index.blade.php`: list view dengan filter status badges; setiap row tampilkan order_number, customer, services, status badge, estimasi selesai, tombol update
    - `production/kanban.blade.php`: board kanban responsive dengan Alpine.js; setiap kolom menampilkan kartu order; kartu bisa di-klik untuk update status
    - `production/scan.blade.php`: halaman scan QR via kamera browser (menggunakan `getUserMedia()` API atau input file); tampilkan info order dan tombol advance status
    - Bulk select checkbox dengan Alpine.js untuk bulk update
    - _Requirements: 5.4, 5.7, 5.8, 5.9, 5.10_

  - [ ] 10.4 Buat estimasi waktu selesai dan overdue indicator
    - Saat order dibuat: hitung `estimated_done_at = created_at + MAX(service.est_duration_hours)` dari semua items
    - Di views production: tampilkan badge "TERLAMBAT" merah jika `now() > estimated_done_at && status != DIAMBIL`
    - _Requirements: 5.10_

  - [ ]* 10.5 Tulis property-based test untuk Property 8 (Forward-Only Status Transition)
    - **Property 8: Forward-Only Production Status Transition**
    - Untuk setiap status saat ini di index i, generate semua kemungkinan target status; verifikasi hanya index i+1 yang diterima, semua lainnya throw exception
    - Test khusus: DIAMBIL adalah status terminal (tidak ada transisi yang valid dari sini)
    - **Validates: Requirements 5.2, 5.5**

  - [ ]* 10.6 Tulis feature tests untuk production flow
    - Test: QR scan → tampil info order → advance status
    - Test: bulk update multiple orders
    - Test: status SIAP → event OrderReady terfired
    - Test: DIAMBIL → tidak bisa diubah lagi
    - _Requirements: 5.2, 5.4, 5.5, 5.7, 5.9_


- [ ] 11. CRM & Loyalty Program
  - [ ] 11.1 Buat LoyaltyService untuk manajemen poin dan tier
    - Buat `app/Services/CRM/LoyaltyService.php`
    - Method `awardPoints(Order $order)`: calculate `floor($order->total / 1000)`; create `LoyaltyPointLog` tipe `earn`; update `customer.loyalty_points`; panggil `checkTierUpgrade()`
    - Method `redeemPoints(Customer $customer, int $points)`: validasi `customer.loyalty_points >= $points`; create log tipe `redeem`; update `customer.loyalty_points`; panggil `checkTierUpgrade()`
    - Method `checkTierUpgrade(Customer $customer)`: hitung tier berdasarkan `loyalty_points` (Bronze<1000, Silver 1000–4999, Gold 5000–9999, Platinum 10000+); update `customer.loyalty_tier` jika berubah
    - Method `expirePoints()`: command scheduled yang menjalankan query `LoyaltyPointLog::where('expired_at', '<', now())`, tandai expired, kurangi saldo customer
    - _Requirements: 6.3, 6.4, 6.5, 6.6_

  - [ ] 11.2 Buat CustomerController, form requests, dan segmentation logic
    - `CustomerController` resource controller: index, create, store, show, edit, update, destroy
    - `CustomerController::search(SearchRequest $request)`: query customer by name, phone, member_code; return JSON untuk POS autocomplete
    - `CustomerController::orders($id)`: tampilkan riwayat order customer
    - `CustomerController::loyalty($id)`: tampilkan saldo poin, log poin, tier, benefit, riwayat tier
    - `CustomerController::adjustPoints($id)`: form untuk manual adjust poin (admin only); create log tipe `adjust`
    - `CustomerController::segment()`: filter customers by tier, frekuensi, nilai transaksi, last transaction date; return paginated list
    - _Requirements: 6.1, 6.7, 6.8, 6.9_

  - [ ] 11.3 Buat Blade views untuk CRM & Loyalty
    - `customers/index.blade.php`: table dengan search, filter by tier; badge tier (Bronze/Silver/Gold/Platinum) dengan warna berbeda; action edit/view
    - `customers/create.blade.php` & `edit.blade.php`: form data customer; auto-generate `member_code` saat create
    - `customers/show.blade.php`: profile customer + statistik (total_spent, transaction_count, current tier) + recent orders + loyalty points balance + chart transaksi
    - `customers/loyalty.blade.php`: tab dengan riwayat poin (earn/redeem/expire/adjust), timeline tier change
    - `customers/segment.blade.php`: filter UI untuk segmentasi, export list ke Excel
    - _Requirements: 6.8, 6.9_

  - [ ] 11.4 Buat CustomerObserver untuk auto-generate member_code dan tracking changes
    - Event `creating`: generate `member_code` unik dengan format `CUST-YYYYMMDD-XXXX`
    - Event `updating`: jika `loyalty_points` berubah, cek tier dan update jika perlu via `LoyaltyService::checkTierUpgrade()`
    - _Requirements: 6.1, 6.3_

  - [ ] 11.5 Buat command untuk expiry poin otomatis
    - `app/Console/Commands/ExpireLoyaltyPoints.php`
    - Query `LoyaltyPointLog::where('type', 'earn')->where('expired_at', '<', now())->where('expired', false)`
    - Untuk setiap log: kurangi `customer.loyalty_points`, create log tipe `expire`, update `expired = true`
    - Cek tier downgrade via `checkTierUpgrade()`
    - Schedule di `app/Console/Kernel.php`: `daily()`
    - _Requirements: 6.6, 6.7_

  - [ ]* 11.6 Tulis property-based test untuk Property 6 (Loyalty Points Earn)
    - **Property 6: Loyalty Points Earn Calculation**
    - Untuk order dengan berbagai nilai total, verifikasi poin yang di-award = `floor(total / 1000)`
    - Verifikasi `balance_after` di log = `balance_before + points`
    - Verifikasi customer tidak walk-in (customer_id not null)
    - **Validates: Requirements 4.9, 6.4**

  - [ ]* 11.7 Tulis property-based test untuk Property 9 (Loyalty Tier Consistency)
    - **Property 9: Loyalty Tier Consistency with Points**
    - Untuk customer dengan berbagai skenario perubahan poin (earn, redeem, expire, adjust), verifikasi tier selalu konsisten dengan threshold:
      - loyalty_points < 1000 → Bronze
      - 1000 <= loyalty_points < 5000 → Silver
      - 5000 <= loyalty_points < 10000 → Gold
      - loyalty_points >= 10000 → Platinum
    - Uji boundary cases: 999, 1000, 1001; 4999, 5000, 5001; 9999, 10000, 10001
    - **Validates: Requirements 6.2, 6.3**

  - [ ]* 11.8 Tulis feature tests untuk CRM flow
    - Test: customer baru → member_code ter-generate
    - Test: order paid → poin bertambah otomatis
    - Test: poin >= threshold tier → tier naik otomatis
    - Test: redeem poin → poin berkurang, tier turun jika di bawah threshold
    - Test: expire poin → poin berkurang, tier turun
    - _Requirements: 6.1, 6.3, 6.4, 6.5, 6.6, 6.7_

- [ ] 12. Promotions Engine
  - [ ] 12.1 Buat PromotionController dan form requests
    - Resource controller untuk CRUD promotions
    - `PromotionRequest`: validate name, type, value (sesuai tipe), date range (end_date >= start_date), usage limits
    - `PromotionController::activate()` / `deactivate()`: toggle `is_active`
    - `PromotionController::report()`: tampilkan efektivitas promo (jumlah penggunaan, total diskon, revenue impact)
    - _Requirements: 7.1, 7.2, 7.8_

  - [ ] 12.2 Buat Blade views untuk Promotions Management
    - `promotions/index.blade.php`: table dengan badge status (active/expired/inactive), usage count, remaining quota
    - `promotions/create.blade.php` & `edit.blade.php`: form dengan conditional fields based on type (Alpine.js); date range picker; service selector; tier selector
    - `promotions/show.blade.php`: detail promo + chart usage over time
    - `promotions/report.blade.php`: laporan efektivitas per promo
    - _Requirements: 7.1, 7.8_

  - [ ] 12.3 Buat scheduled command untuk auto-deactivate expired promotions
    - Command: `app/Console/Commands/DeactivateExpiredPromotions.php`
    - Query `Promotion::where('end_date', '<', today())->where('is_active', true)->update(['is_active' => false])`
    - Schedule daily di Kernel
    - _Requirements: 7.5_

  - [ ]* 12.4 Tulis feature tests untuk promotions flow
    - Test: promo aktif dengan min_transaction valid → diskon applied
    - Test: promo usage_limit habis → promo tidak diterapkan
    - Test: promo expired → auto nonaktif (cek via command)
    - Test: promo tier-specific → hanya customer dengan tier match yang dapat
    - _Requirements: 7.2, 7.3, 7.4, 7.5, 7.6_

- [ ] 13. Checkpoint — Core Operations Complete
  - Pastikan POS flow berjalan end-to-end: create order → apply promo → checkout → QR code ter-generate → receipt print → journal posted → poin awarded
  - Pastikan production tracking forward-only berjalan: scan QR → advance status → status SIAP → notifikasi
  - Pastikan loyalty flow berjalan: poin earn → tier upgrade → poin redeem → tier downgrade
  - Pastikan promo engine berjalan: evaluasi promo → apply → usage count increment → quota check
  - Jalankan semua property tests dan feature tests, pastikan pass
  - _Ensure all tests pass, ask the user if questions arise._


---

## Sprint 5–6: Back Office (5 Minggu)

---

- [ ] 14. Inventory & Procurement
  - [ ] 14.1 Buat FIFOService untuk logika FIFO stock deduction
    - Buat `app/Services/Inventory/FIFOService.php`
    - Method `deduct(int $itemId, float $quantity)`: query `InventoryBatch::where('item_id', $itemId)->where('remaining_qty', '>', 0)->orderBy('received_date', 'asc')->get()`; loop dari batch tertua, kurangi `remaining_qty` sampai qty terpenuhi; return array `['batches_used' => [...], 'total_cogs' => sum(batch_qty × batch_unit_cost)]`
    - Throw `InsufficientStockException` jika total remaining < quantity
    - _Requirements: 8.1, 8.7_

  - [ ] 14.2 Buat InventoryService untuk stock management
    - Method `updateStock(int $itemId, float $delta, string $source, int $sourceId)`: update `inventory_items.current_stock += delta`; jika delta negatif, panggil `FIFOService::deduct()`; log ke inventory movement log (opsional table)
    - Method `checkLowStock(int $branchId)`: query items dengan `current_stock < min_stock` di branch; return list; fire event `LowStockAlert`
    - _Requirements: 8.2, 8.10_

  - [ ] 14.3 Buat InventoryItemController dan Blade views
    - Resource controller untuk CRUD inventory items
    - `InventoryItemController::index()`: list dengan badge low stock (merah jika < min_stock); filter by branch; search by name/SKU
    - `InventoryItemController::movement($id)`: log pergerakan stok (in/out) per item
    - `InventoryItemController::batches($id)`: tampilkan daftar batch aktif dengan FIFO order
    - Views: `inventory/index.blade.php`, `create/edit.blade.php`, `movement.blade.php`, `batches.blade.php`
    - _Requirements: 8.1, 8.2, 8.10_

  - [ ] 14.4 Buat PurchaseRequestController dan alur approval
    - `PurchaseRequestController::index()`: list semua PR dengan filter status
    - `PurchaseRequestController::create()`: form pilih items, input qty, reason
    - `PurchaseRequestController::store()`: create PR dengan status `draft` atau langsung `pending`
    - `PurchaseRequestController::approve($id)`: set `approved_by`, status → `approved`; generate PO otomatis atau manual
    - Views: `procurement/purchase_requests/index.blade.php`, `create/edit.blade.php`, `show.blade.php`
    - _Requirements: 8.3, 8.4_

  - [ ] 14.5 Buat PurchaseOrderController dan form
    - `PurchaseOrderController::index()`: list PO dengan filter status
    - `PurchaseOrderController::create()`: pilih PR (optional), pilih supplier, input items + qty + unit_cost; hitung subtotal, pajak, total
    - `PurchaseOrderController::store()`: create PO dengan status `draft`
    - `PurchaseOrderController::send($id)`: status → `sent`, print PO PDF
    - `PurchaseOrderController::confirm($id)`: status → `confirmed` (dari supplier konfirmasi)
    - Print PO PDF: view `prints/purchase_order.blade.php`
    - _Requirements: 8.3, 8.4, 8.5_

  - [ ] 14.6 Buat GoodsReceivedNoteController dan GRNObserver
    - `GRNController::create($poId)`: buat GRN dari PO, pre-fill items
    - `GRNController::store()`: simpan GRN dengan status `draft`; jika semua items dari PO sudah received sepenuhnya → PO status `completed`; jika partial → PO status `partial`
    - `GRNController::confirm($id)`: status → `confirmed`; fire event `GRNConfirmed`
    - `GRNObserver` event `updated` — jika status → `confirmed`:
      1. Untuk setiap `grn_item`: create `InventoryBatch` baru dengan `received_date = grn.received_date`, update `inventory_items.current_stock += grn_item.quantity`
      2. Panggil `JournalService::postGRNJournal($grn)` → Dr: Inventori, Cr: Hutang Usaha
      3. Update `po_items.received_qty`
    - _Requirements: 8.6, 8.9_

  - [ ] 14.7 Buat Blade views untuk GRN
    - `procurement/grn/index.blade.php`: list GRN dengan badge status
    - `procurement/grn/create.blade.php`: form dari PO, edit qty received
    - `procurement/grn/show.blade.php`: detail GRN + print button
    - Print GRN PDF: `prints/grn.blade.php`
    - _Requirements: 8.5, 8.6_

  - [ ] 14.8 Buat command untuk low stock alert scheduling
    - Command: `app/Console/Commands/CheckLowStock.php`
    - Panggil `InventoryService::checkLowStock()` per branch; kirim notifikasi ke Branch_Admin (email atau in-app notification)
    - Schedule daily di Kernel
    - _Requirements: 8.2_

  - [ ]* 14.9 Tulis property-based test untuk Property 10 (FIFO Stock Deduction Order)
    - **Property 10: FIFO Stock Deduction Order**
    - Buat item dengan 2+ batches berbeda received_date; deduct quantity; verifikasi batch tertua terkonsumsi penuh sebelum batch lebih baru
    - Test edge cases: deduct quantity > batch1 tapi < batch1+batch2; deduct quantity >> total stock (throw exception)
    - **Validates: Requirements 8.1**

  - [ ]* 14.10 Tulis property-based test untuk Property 11 (GRN Stock Update)
    - **Property 11: GRN Stock Update Round-Trip**
    - Buat GRN dengan N items; confirm GRN; verifikasi `inventory_items.current_stock` untuk setiap item bertambah tepat sebesar `SUM(grn_item.quantity)` untuk item itu
    - **Validates: Requirements 8.6**

  - [ ]* 14.11 Tulis feature tests untuk procurement flow
    - Test: PR → approval → PO → GRN → stock update + journal posting
    - Test: low stock alert triggered saat current_stock < min_stock
    - Test: FIFO deduction dari batch tertua
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.6, 8.9_


- [ ] 15. Finance & Accounting (Double-Entry)
  - [ ] 15.1 Buat JournalService untuk auto-posting dan manual journal
    - Method `postOrderJournal(Order $order)`: buat journal dengan source Order; journal_lines:
      - Dr: 1-1100 Kas (jika cash) atau 1-1200 Piutang (jika transfer/invoice), amount = `order.paid_amount`
      - Cr: 4-1100 Pendapatan Jasa Laundry, amount = `order.total`
      - Jika ada diskon: Dr: 5-9900 Beban Diskon, amount = `order.discount_amount`
    - Method `postGRNJournal(GRN $grn)`: buat journal dengan source GRN; journal_lines:
      - Dr: 1-1400 Inventori, amount = `SUM(grn_item.quantity × grn_item.unit_cost)`
      - Cr: 2-1100 Hutang Usaha, amount = sama
    - Method `postPayrollJournal(Payroll $payroll)`: buat journal dengan source Payroll; journal_lines:
      - Dr: 5-1100 Beban Gaji, amount = `SUM(payroll_items.net_salary)`
      - Cr: 1-1100 Kas, amount = sama (asumsi gaji dibayar cash)
    - Method `postDepreciationJournal(DepreciationSchedule $schedule)`: buat journal; journal_lines:
      - Dr: 5-2100 Beban Depresiasi, amount = `schedule.depreciation_amount`
      - Cr: 1-1510 Akumulasi Depresiasi, amount = sama
    - Method `createManualJournal(array $data, User $user)`: validasi balance (total debit = total credit); create journal dengan status `draft`; allow posting
    - Method `postJournal(Journal $journal)`: set status → `posted`, set `posted_at`; lock accounting period jika sudah closed
    - Method `reverseJournal(Journal $journal, User $user)`: buat journal baru dengan journal_lines terbalik (debit → credit, credit → debit), set `reversed_by` di journal asli
    - _Requirements: 4.10, 8.9, 9.1, 9.2, 9.5, 9.6, 11.2_

  - [ ] 15.2 Buat JournalController dan form untuk manual journal
    - `JournalController::index()`: list journals dengan filter (type, status, date range, branch)
    - `JournalController::create()`: form manual journal — select accounts, input debit/credit per line; real-time balance check via Alpine.js (total debit vs credit)
    - `JournalController::store()`: validasi balance via `ManualJournalRequest`; simpan sebagai draft
    - `JournalController::post($id)`: panggil `JournalService::postJournal()`
    - `JournalController::reverse($id)`: panggil `JournalService::reverseJournal()`
    - _Requirements: 9.5, 9.6, 9.7, 9.11_

  - [ ] 15.3 Buat AccountingPeriodController dan period closing logic
    - `AccountingPeriodController::index()`: list semua periods per branch dengan badge status
    - `AccountingPeriodController::close($id)`: validasi semua journals di periode sudah posted; hitung trial balance; generate laporan keuangan; set status → `closed`, `closed_at`, `closed_by`; prevent future journal posting di periode tersebut
    - `AccountingPeriodController::report($id)`: generate Balance Sheet, Income Statement, Cash Flow untuk periode itu
    - _Requirements: 9.3, 9.8_

  - [ ] 15.4 Buat Blade views untuk Finance & Accounting
    - `finance/journals/index.blade.php`: table dengan filter, search by reference, badge status; tombol post/reverse
    - `finance/journals/create.blade.php`: form manual journal dengan dynamic line items (Alpine.js `x-for` untuk add/remove rows), real-time total debit vs credit
    - `finance/journals/show.blade.php`: detail journal + lines + audit trail
    - `finance/periods/index.blade.php`: list accounting periods; tombol close period (dengan konfirmasi warning)
    - `finance/periods/report.blade.php`: tampilkan Balance Sheet, Income Statement, Cash Flow dalam tabs
    - _Requirements: 9.3, 9.5, 9.8, 9.11_

  - [ ] 15.5 Buat FinancialReportService untuk generate laporan keuangan
    - Method `getBalanceSheet(int $branchId, int $year, int $month)`: query COA by type (asset, liability, equity); aggregate journal_lines balances; return structured array
    - Method `getIncomeStatement(int $branchId, int $year, int $month)`: query COA revenue & expense; aggregate balances; hitung net income
    - Method `getCashFlowStatement(int $branchId, int $year, int $month)`: classify journals by activity (operating, investing, financing); aggregate cash in/out
    - Method `getTrialBalance(int $branchId, int $year, int $month)`: query semua accounts dengan balance; verifikasi total debit = total credit
    - Method `getConsolidatedReport(string $reportType, int $year, int $month)`: aggregate per branch untuk Owner view
    - _Requirements: 9.8, 9.9_

  - [ ] 15.6 Implementasi tax calculation (PP23 & PPN) placeholder
    - Buat config `config/tax.php`: tarif PP23 (2%), PPN (11%)
    - Method di `TaxService::calculateTax(Order $order)`: hitung pajak berdasarkan total; simpan ke `order.tax_amount`
    - Method di `TaxService::generateTaxReport(int $branchId, int $month, int $year)`: aggregate orders dengan pajak; return summary
    - _Requirements: 9.4, 9.10_

  - [ ]* 15.7 Tulis property-based test untuk Property 7 (Auto Journal Double-Entry Balance)
    - **Property 7: Auto Journal Double-Entry Balance**
    - Untuk berbagai skenario transaksi (order payment, GRN confirmation, payroll processing, depreciation posting), verifikasi bahwa `SUM(journal_lines.debit) === SUM(journal_lines.credit)` untuk setiap journal yang dihasilkan
    - Test dengan berbagai kombinasi nominal: kecil, besar, nilai random
    - **Validates: Requirements 4.10, 9.1, 9.6**

  - [ ]* 15.8 Tulis property-based test untuk Property 12 (Closed Accounting Period Immutability)
    - **Property 12: Closed Accounting Period Immutability**
    - Tutup accounting period; attempt create journal atau update journal existing dengan date di periode itu → harus ditolak (throw `AccountingPeriodClosedException`)
    - Verifikasi untuk semua jenis journal: auto, manual, adjustment, reversal
    - **Validates: Requirements 9.3**

  - [ ]* 15.9 Tulis feature tests untuk finance flow
    - Test: order payment → auto journal posted → debit kas = kredit pendapatan
    - Test: GRN confirmed → auto journal posted → debit inventori = kredit hutang
    - Test: manual journal balance check (tidak seimbang → ditolak)
    - Test: accounting period closed → journal baru di periode itu ditolak
    - Test: laporan keuangan generated → balance sheet, income statement, cash flow
    - _Requirements: 9.1, 9.3, 9.5, 9.6, 9.8_

- [ ] 16. Auto Journal Observers Refinement
  - [ ] 16.1 Register semua observers di AppServiceProvider
    - Register: `OrderObserver`, `GRNObserver`, `PayrollObserver` (nanti di HR), `DepreciationScheduleObserver` (nanti di Asset)
    - Pastikan semua auto-posting hanya terjadi sekali per event (gunakan flag atau cek existing journal)
    - _Requirements: 4.10, 8.9, 9.1_

  - [ ] 16.2 Buat AuditLogObserver untuk semua model kritis
    - Tambahkan observer ke: Order, Journal, Customer, InventoryItem, Employee, FixedAsset
    - Event `created`, `updated`, `deleted`: log ke `audit_logs` dengan old/new values
    - _Requirements: 15.8_

- [ ] 17. Checkpoint — Back Office Complete
  - Pastikan inventory flow berjalan: PR → PO → GRN → stock update (FIFO) → journal posted
  - Pastikan finance flow berjalan: auto journal posting untuk Order, GRN, Payroll, Depreciation
  - Pastikan accounting period closing berfungsi dengan immutability enforcement
  - Pastikan laporan keuangan ter-generate dengan benar: Balance Sheet, Income Statement, Cash Flow
  - Jalankan semua property tests dan feature tests untuk inventory dan finance
  - _Ensure all tests pass, ask the user if questions arise._


---

## Sprint 7: HR & Analytics (4 Minggu)

---

- [ ] 18. HR Management
  - [ ] 18.1 Buat EmployeeController dan form requests
    - Resource controller untuk CRUD employees
    - `EmployeeRequest`: validasi NIK unique, position required, base_salary > 0
    - `EmployeeController::index()`: list employees dengan filter by branch, position, status
    - `EmployeeController::show($id)`: profile + riwayat gaji + riwayat absensi
    - `EmployeeController::updateSalary($id)`: form update base_salary; simpan ke `salary_histories`
    - _Requirements: 10.1, 10.7_

  - [ ] 18.2 Buat AttendanceController untuk pencatatan absensi
    - `AttendanceController::index()`: calendar view atau table per bulan dengan badge status
    - `AttendanceController::create()`: form pilih employee, date, status, check_in/out times
    - `AttendanceController::store()`: simpan attendance; validasi unique (employee_id, date)
    - `AttendanceController::bulk()`: form bulk input absensi untuk satu hari, semua employees di branch
    - _Requirements: 10.2_

  - [ ] 18.3 Buat PayrollController dan PayrollService
    - `PayrollService::processPayroll(int $branchId, int $month, int $year)`: query employees + attendances bulan itu; hitung work_days, attendance_days, net_salary = base_salary × (attendance_days / work_days) + allowance - deduction; create payroll + payroll_items; fire event `PayrollProcessed`
    - `PayrollObserver` event `updated` — jika status → `paid`: panggil `JournalService::postPayrollJournal($payroll)`
    - `PayrollController::index()`: list payrolls per branch-month-year
    - `PayrollController::create()`: pilih branch, month, year; preview payroll items; submit → process
    - `PayrollController::show($id)`: detail payroll + items; tombol print slip gaji
    - `PayrollController::pay($id)`: set status → `paid`, trigger auto journal
    - _Requirements: 10.3, 10.4, 10.5_

  - [ ] 18.4 Buat Blade views untuk HR
    - `hr/employees/index.blade.php`: table dengan search, filter
    - `hr/employees/create.blade.php`, `edit.blade.php`, `show.blade.php`
    - `hr/attendances/index.blade.php`: calendar atau list view per bulan
    - `hr/attendances/bulk.blade.php`: table checklist semua employees dengan dropdown status per row
    - `hr/payrolls/index.blade.php`: list per periode
    - `hr/payrolls/show.blade.php`: detail + print slip
    - Print slip gaji PDF: `prints/payslip.blade.php`
    - _Requirements: 10.2, 10.4, 10.5_

  - [ ]* 18.5 Tulis feature tests untuk HR flow
    - Test: employee created → nik unique, salary history recorded
    - Test: attendance recorded → unique per (employee, date)
    - Test: payroll processed → payroll items created, net_salary calculated
    - Test: payroll paid → journal posted (debit beban gaji, kredit kas)
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

- [ ] 19. Fixed Asset Management
  - [ ] 19.1 Buat FixedAssetController dan form requests
    - Resource controller untuk CRUD fixed assets
    - `FixedAssetRequest`: validasi asset_code unique, acquisition_date <= today, useful_life_months > 0, salvage_value >= 0
    - `FixedAssetController::index()`: list assets dengan filter by category, branch, status
    - `FixedAssetController::show($id)`: detail asset + depreciation schedule table
    - `FixedAssetController::dispose($id)`: form disposal (disposal_date, disposal_value); hitung gain/loss; create journal disposal
    - _Requirements: 11.1, 11.7_

  - [ ] 19.2 Buat DepreciationService untuk perhitungan depresiasi
    - Method `calculateStraightLine(FixedAsset $asset, Date $periodDate)`: `(acquisition_cost - salvage_value) / useful_life_months`
    - Method `calculateDoubleDeclining(FixedAsset $asset, Date $periodDate)`: `book_value × (2 / useful_life_months)`; stop jika book_value <= salvage_value
    - Method `generateSchedule(FixedAsset $asset)`: loop dari acquisition_date hingga acquisition_date + useful_life_months; create `DepreciationSchedule` per bulan dengan calculated amount, accumulated, book_value
    - _Requirements: 11.2, 11.4_

  - [ ] 19.3 Buat command untuk proses depresiasi bulanan
    - Command: `app/Console/Commands/ProcessDepreciation.php`
    - Query semua `FixedAsset::where('is_active', true)` per branch; untuk setiap asset: query `DepreciationSchedule::where('period_date', first_day_of_current_month)->where('is_posted', false)`; panggil `JournalService::postDepreciationJournal($schedule)`; set `schedule.is_posted = true`, `schedule.journal_id = $journal->id`
    - Schedule di Kernel: `monthly()->onFirstOfMonth()`
    - _Requirements: 11.3_

  - [ ] 19.4 Buat MaintenanceScheduleController (optional feature)
    - Table `maintenance_schedules`: asset_id, scheduled_date, description, status (pending/done), completed_at
    - Controller CRUD untuk schedule maintenance
    - Command untuk reminder 7 hari sebelum scheduled_date
    - _Requirements: 11.5, 11.6_

  - [ ] 19.5 Buat Blade views untuk Fixed Assets
    - `assets/index.blade.php`: table dengan filter by category, branch; badge status aktif/disposed
    - `assets/create.blade.php`, `edit.blade.php`: form dengan dynamic calculation preview (estimated monthly depreciation via Alpine.js)
    - `assets/show.blade.php`: detail asset + depreciation schedule table + tombol generate schedule
    - `assets/maintenance.blade.php`: list maintenance schedules dengan badge overdue
    - _Requirements: 11.1, 11.4, 11.5_

  - [ ]* 19.6 Tulis property-based test untuk Property 13 (Depreciation Calculation Accuracy)
    - **Property 13: Depreciation Calculation Accuracy**
    - Straight line: untuk berbagai kombinasi acquisition_cost, salvage_value, useful_life_months, verifikasi monthly depreciation = `(cost - salvage) / life` dan final book_value = salvage_value
    - Double declining: verifikasi monthly depreciation = `book_value × (2 / life)` dan book_value tidak pernah < salvage_value
    - **Validates: Requirements 11.2**

  - [ ]* 19.7 Tulis feature tests untuk asset management flow
    - Test: asset created → depreciation schedule generated
    - Test: monthly command → depreciation journals posted, is_posted flag set
    - Test: disposal → gain/loss journal created
    - Test: maintenance reminder triggered 7 hari sebelum scheduled_date
    - _Requirements: 11.1, 11.2, 11.3, 11.5, 11.6, 11.7_


- [ ] 20. Role-based Dashboards
  - [ ] 20.1 Buat DashboardController dengan logic per role
    - `DashboardController::index()`: switch berdasarkan primary role user, redirect atau render view sesuai role
    - Method `ownerDashboard()`: aggregate data semua branch (total revenue, jumlah order, top branch, trend chart)
    - Method `branchAdminDashboard()`: data cabang sendiri (revenue hari ini, order aktif, stok kritis, produksi summary)
    - Method `cashierDashboard()`: quick link ke POS, order count hari ini, revenue hari ini
    - Method `workshopAdminDashboard()`: production queue per status, overdue orders, estimasi workload
    - Method `financeDashboard()`: pending journals, closed periods, receivables/payables summary
    - Method `csMarketingDashboard()`: customer stats, new members this month, orders ready for pickup notification
    - _Requirements: 12.1, 12.2, 12.3_

  - [ ] 20.2 Buat Blade views untuk setiap dashboard role
    - `dashboard/owner.blade.php`: executive summary dengan Chart.js (revenue trend line, branch comparison pie, service distribution bar)
    - `dashboard/branch_admin.blade.php`: stat cards (revenue, orders, low stock, production status), quick links
    - `dashboard/cashier.blade.php`: simple stat cards, link to POS, recent orders
    - `dashboard/workshop_admin.blade.php`: kanban mini overview, overdue alerts
    - `dashboard/finance.blade.php`: financial summary, pending approvals, period status
    - `dashboard/cs_marketing.blade.php`: CRM stats, ready orders list
    - _Requirements: 12.1, 12.2, 12.3_

  - [ ] 20.3 Implementasi Chart.js untuk dashboard charts
    - Buat `resources/js/charts.js`: wrapper Alpine.js component untuk Chart.js
    - Revenue trend line chart (weekly/monthly)
    - Branch comparison pie chart
    - Service distribution bar chart
    - Customer tier distribution doughnut chart
    - Fetch data dari API endpoints (e.g. `/dashboard/stats?type=revenue&period=month`)
    - _Requirements: 12.2, 12.5_

  - [ ] 20.4 Buat API endpoints untuk dashboard stats (JSON)
    - `DashboardController::stats(Request $request)`: return JSON berdasarkan query params (type, period, branch_id)
    - Type: revenue, orders, customers, production, inventory, finance
    - Period: today, week, month, year
    - Implementasi caching (Redis atau file) dengan TTL 5 menit untuk performa
    - _Requirements: 12.4_

  - [ ] 20.5 Implementasi auto-refresh dashboard via Alpine.js polling
    - Gunakan Alpine.js `x-init` dengan `setInterval(() => fetchStats(), 300000)` — refresh setiap 5 menit
    - Tampilkan loading indicator saat fetch
    - _Requirements: 12.4_

  - [ ]* 20.6 Tulis feature tests untuk dashboard data
    - Test: owner dashboard menampilkan data konsolidasi semua branch
    - Test: branch_admin dashboard hanya menampilkan data branch sendiri
    - Test: stats API return data yang tepat per role dan branch scope
    - _Requirements: 12.1, 12.2, 12.3_

- [ ] 21. Checkpoint — HR & Analytics Complete
  - Pastikan HR flow berjalan: employee CRUD → attendance recording → payroll processing → journal posting → slip gaji print
  - Pastikan asset flow berjalan: asset created → schedule generated → monthly depreciation → journal posted
  - Pastikan dashboard menampilkan data yang tepat per role dengan chart yang interaktif
  - Jalankan semua property tests dan feature tests
  - _Ensure all tests pass, ask the user if questions arise._


---

## Sprint 8: Reporting & Polish (4 Minggu)

---

- [ ] 22. Reporting & Export (Excel + PDF)
  - [ ] 22.1 Buat ReportController dengan semua jenis laporan
    - `ReportController::index()`: halaman index laporan dengan links ke setiap report type
    - `ReportController::sales(Request $request)`: laporan penjualan harian/bulanan; filter by branch, date range, service; tampilkan table + summary + chart
    - `ReportController::production(Request $request)`: laporan produksi (jumlah order per status, rata-rata completion time, overdue count)
    - `ReportController::finance(Request $request)`: laporan keuangan (Balance Sheet, Income Statement, Cash Flow, Trial Balance)
    - `ReportController::inventory(Request $request)`: laporan stok (current stock, movement, low stock items, valuation)
    - `ReportController::customers(Request $request)`: laporan CRM (customer growth, tier distribution, loyalty points summary, segmentation)
    - `ReportController::payroll(Request $request)`: laporan penggajian per periode
    - _Requirements: 13.2, 13.3_

  - [ ] 22.2 Implementasi ekspor ke Excel menggunakan Laravel Excel
    - Buat `app/Exports/SalesReportExport.php`: implement `FromCollection`, `WithHeadings`, `WithMapping`
    - Buat exports untuk: `ProductionReportExport`, `FinanceReportExport`, `InventoryReportExport`, `CustomerReportExport`, `PayrollReportExport`
    - Method di controller: `ReportController::exportSales()` → return Excel::download(new SalesReportExport($data), 'sales.xlsx')
    - _Requirements: 13.1, 13.2_

  - [ ] 22.3 Implementasi ekspor ke PDF menggunakan DomPDF
    - Method di controller: `ReportController::exportSalesPDF()` → return PDF::loadView('reports/sales_pdf', $data)->download('sales.pdf')
    - Buat views PDF: `reports/sales_pdf.blade.php`, `reports/finance_pdf.blade.php`, dll.
    - Style PDF sesuai branding: logo cabang, kop surat, format tabel rapi
    - _Requirements: 13.1, 13.7_

  - [ ] 22.4 Implementasi asinkron export untuk laporan besar
    - Buat Job `app/Jobs/ExportLargeReportJob.php`: terima export class, user email; jalankan export, simpan ke storage, kirim notifikasi dengan download link
    - Dispatch job jika row count > 10.000
    - Tampilkan message: "Laporan sedang diproses, Anda akan menerima notifikasi setelah selesai."
    - _Requirements: 13.5_

  - [ ] 22.5 Buat Blade views untuk reporting interface
    - `reports/index.blade.php`: card links ke setiap report type dengan icon
    - `reports/sales.blade.php`: filter form (branch, date range, service); tampilkan table hasil; tombol Export Excel / Export PDF
    - `reports/production.blade.php`, `reports/finance.blade.php`, `reports/inventory.blade.php`, `reports/customers.blade.php`, `reports/payroll.blade.php`: similar structure
    - Implementasi filter sticky (parameter tersimpan di query string)
    - _Requirements: 13.2, 13.6_

  - [ ] 22.6 Implementasi filter dan branch scope untuk laporan
    - Semua report harus respect branch scope: jika user branch-level role → filter by user.branch_id otomatis
    - Owner dan Finance → dropdown pilih branch atau "Semua Cabang" (consolidated)
    - _Requirements: 13.3, 13.4_

  - [ ]* 22.7 Tulis feature tests untuk reporting & export
    - Test: generate sales report → data sesuai filter
    - Test: export Excel → file downloaded dengan data benar
    - Test: export PDF → file generated dengan format tepat
    - Test: branch scope applied → branch_admin hanya melihat data cabangnya
    - Test: owner → bisa generate consolidated report
    - Test: large report → dispatched as job, notification sent
    - _Requirements: 13.1, 13.2, 13.3, 13.4, 13.5_

- [ ] 23. Public Order Tracking Page
  - [ ] 23.1 Buat PublicTrackingController (tanpa auth)
    - `PublicTrackingController::index()`: form input order_number
    - `PublicTrackingController::track(Request $request)`: query order by order_number; validasi order exists; return view dengan order details + production timeline
    - Implementasi rate limiter: `RateLimiter::for('public-tracking', fn() => Limit::perMinute(30))`
    - _Requirements: 14.1, 14.2, 14.5_

  - [ ] 23.2 Buat Blade view untuk public tracking page
    - `public/track.blade.php`: halaman standalone (tanpa sidebar), form input order_number dengan desain clean
    - `public/track_result.blade.php`: tampilkan order_number, nama layanan, estimasi selesai, branch address, timeline production status (visual progress bar dari TERIMA → DIAMBIL dengan status saat ini highlighted)
    - Jangan tampilkan info sensitif (harga, customer name lengkap, data finansial)
    - _Requirements: 14.1, 14.2, 14.4, 14.6_

  - [ ] 23.3 Buat route public tracking tanpa middleware auth
    - Route::get('/track', [PublicTrackingController::class, 'index'])->name('public.track')
    - Route::post('/track', [PublicTrackingController::class, 'track'])->name('public.track.submit')->middleware('throttle:public-tracking')
    - _Requirements: 14.1, 14.5_

  - [ ]* 23.4 Tulis property-based test untuk Property 14 (Public Tracking Timeline)
    - **Property 14: Public Tracking Shows Correct Status Timeline**
    - Untuk order dengan production_status di posisi S dalam sequence, verifikasi halaman tracking menampilkan status TERIMA..S sebagai "completed", S sebagai "current", dan status setelah S sebagai "pending"
    - Test untuk setiap kemungkinan status saat ini (TERIMA, PILAH, ..., DIAMBIL)
    - **Validates: Requirements 14.4**

  - [ ]* 23.5 Tulis feature tests untuk public tracking
    - Test: order_number valid → tampilkan timeline benar
    - Test: order_number invalid → error message muncul
    - Test: rate limiter → 30 request/menit dari IP yang sama → HTTP 429
    - Test: tidak ada data sensitif ditampilkan
    - _Requirements: 14.1, 14.2, 14.3, 14.5, 14.6_


- [ ] 24. Testing & QA Finalization
  - [ ] 24.1 Buat Database Factories untuk semua models
    - Buat factories: `BranchFactory`, `WorkshopFactory`, `UserFactory` (extend default), `CustomerFactory`, `ServiceFactory`, `OrderFactory`, `OrderItemFactory`, `ProductionStatusLogFactory`
    - Buat factories: `ChartOfAccountFactory`, `JournalFactory`, `JournalLineFactory`, `InventoryItemFactory`, `InventoryBatchFactory`, `EmployeeFactory`, `AttendanceFactory`, `PayrollFactory`
    - Buat factories: `FixedAssetFactory`, `DepreciationScheduleFactory`, `PromotionFactory`, `SupplierFactory`, `PurchaseRequestFactory`, `PurchaseOrderFactory`, `GRNFactory`
    - Pastikan factories mengisi data yang realistis menggunakan Faker
    - _Requirements: 15.1_

  - [ ] 24.2 Finalisasi semua feature tests yang belum ditulis
    - Lengkapi feature tests untuk semua major flows yang belum ter-cover
    - Test user management (create, edit, assign role, deactivate)
    - Test audit logging (setiap action kritis tercatat)
    - Test dark mode preference tersimpan per user
    - Test responsive design check (visual regression optional)
    - _Requirements: 1.8, 15.2, 15.8_

  - [ ] 24.3 Buat TestCase base class dan helper methods
    - Buat `tests/TestCase.php` dengan helper: `loginAs($role)`, `actingAsBranch($role, $branch)`, `createBranchWithData($branchCode)`
    - Helper `assertJournalBalanced(Journal $journal)`: assert SUM(debit) === SUM(credit)
    - Helper `assertBranchScoped(Model $model, Branch $branch)`: assert collection hanya berisi records dari branch tersebut
    - _Requirements: 15.9_

  - [ ]* 24.4 Tulis integration tests untuk full system flow
    - Test end-to-end: Customer walk-in → POS order → checkout → QR generated → production update TERIMA→SIAP→DIAMBIL → journal posted → poin earned → tier upgraded
    - Test finance integration: Order paid → journal posted → accounting period closed → balance sheet generated
    - Test inventory integration: low stock → PR → PO → GRN → stock updated → FIFO batch used
    - _Requirements: seluruh functional requirements_

- [ ] 25. Deployment Preparation
  - [ ] 25.1 Konfigurasi environment production
    - Buat file `.env.production.example` dengan semua variabel yang diperlukan: DB_CONNECTION=mysql, QUEUE_CONNECTION=redis (opsional), MAIL_*, APP_URL, dll.
    - Konfigurasi `config/trustedproxies.php` untuk HTTPS reverse proxy (jika ada)
    - Pastikan `APP_DEBUG=false` di production
    - Konfigurasi `config/logging.php`: log ke file dengan rotation daily, level error untuk production
    - _Requirements: 15.5, 15.7_

  - [ ] 25.2 Buat artisan commands untuk initial setup
    - Command: `app/Console/Commands/InstallSystem.php` — jalankan migrations, seeders wajib (COA, roles, permissions, default users, branch dummy, services), create storage symlink
    - Command: `app/Console/Commands/SetupDemo.php` — data demo lengkap (branches, customers, orders, dll.) untuk testing UAT
    - _Requirements: 2.10, 3.6_

  - [ ] 25.3 Konfigurasi Spatie Laravel Backup
    - Konfigurasi `config/backup.php`: source = database + all storage; destination = local storage
    - Schedule di Kernel: `->daily()->at('02:00')` untuk backup; `->monthly()` untuk cleanup (keep 30 backups)
    - Test backup command: `php artisan backup:run`
    - _Requirements: 15.7_

  - [ ] 25.4 Konfigurasi queue workers dan scheduled tasks
    - Pastikan semua commands registered di `app/Console/Kernel.php`:
      - `DeactivateExpiredPromotions` → daily
      - `ExpireLoyaltyPoints` → daily
      - `CheckLowStock` → daily
      - `ProcessDepreciation` → monthly first of month
      - `backup:run` → daily
    - Buat `supervisor.conf` template untuk queue worker process
    - _Requirements: 15.7_

  - [ ] 25.5 Optimasi performance dan final polish
    - Jalankan `php artisan optimize` (cache config, routes, views)
    - Audit N+1 queries menggunakan `DB::listen()` atau Laravel Debugbar di development
    - Tambahkan eager loading yang diperlukan di semua controllers yang list collections
    - Pastikan semua indexes database sudah ada (verifikasi via `php artisan db:show` atau query EXPLAIN)
    - Jalankan `npm run build` untuk production assets (minified CSS + JS)
    - _Requirements: 15.1_

  - [ ] 25.6 Security checklist
    - Verifikasi semua routes yang butuh auth sudah menggunakan middleware `auth`
    - Verifikasi semua routes yang butuh permission sudah menggunakan `can:` middleware atau `authorize()` di controller
    - Verifikasi tidak ada mass assignment vulnerability (semua `$fillable` atau `$guarded` terdefinisi)
    - Verifikasi CSRF protection aktif di semua form
    - Verifikasi input validation ada di semua endpoint yang menerima data
    - Verifikasi rate limiting aktif di endpoint sensitif (login, public tracking)
    - _Requirements: 15.5, 15.6, 15.11_

- [ ] 26. Final Checkpoint — Sistem Siap Produksi
  - Jalankan `php artisan migrate:fresh --seed` di environment staging dengan data lengkap
  - Jalankan full test suite: `php artisan test` → semua tests hijau
  - Verifikasi semua 14 property-based tests pass
  - Lakukan manual smoke test pada critical paths: login, POS order creation, production update, finance reporting, public tracking
  - Verifikasi backup berjalan: `php artisan backup:run --only-db`
  - Verifikasi semua scheduled commands terdaftar: `php artisan schedule:list`
  - _Ensure all tests pass, ask the user if questions arise._


---

## Notes

- Tasks bertanda `*` adalah **opsional** (test tasks) dan tidak diimplementasikan secara default oleh coding agent kecuali diminta eksplisit. Mereka direkomendasikan untuk menjaga kualitas dan memverifikasi kebenaran sistem.
- Setiap task mereferensikan requirements spesifik dari `requirements.md` menggunakan format `Requirements: X.Y`
- Property-based tests mereferensikan properties dari `design.md` menggunakan format **Property N: [Title]** dan **Validates: Requirements X.Y**
- Checkpoints di setiap akhir sprint memastikan validasi inkremental dan early error detection
- Urutan tasks memperhatikan dependencies: Foundation → Core Operations → Back Office → Analytics → Reporting & Deployment
- Semua code harus menggunakan **PHP 8.5+** features (readonly properties, intersection types, fibers jika relevan), **Laravel 13** conventions, dan **Tailwind CSS v4** class syntax
- Semua pesan error dan validasi dalam **Bahasa Indonesia**
- Gunakan **Repository Pattern** + **Service Layer Pattern** untuk semua modul sesuai `requirements.md 15.9`
- **Dark mode** menggunakan Tailwind `dark:` variants dengan class strategy, diaktifkan via Alpine.js toggle
- **Primary color** `#FF6600` digunakan sebagai warna brand di seluruh antarmuka

### Ringkasan 14 Property-Based Tests

| Property | Test Task | Validates |
|----------|-----------|-----------|
| Property 1: Account Lockout | 4.7 | Req 1.3 |
| Property 2: Branch Scope Query Isolation | 6.5 | Req 2.6, 2.7, 15.10 |
| Property 3: Order Number Format Invariant | 9.11 | Req 4.1 |
| Property 4: Order Total Calculation Correctness | 9.12 | Req 4.4 |
| Property 5: Cash Change Calculation | 9.13 | Req 4.7 |
| Property 6: Loyalty Points Earn Calculation | 11.6 | Req 4.9, 6.4 |
| Property 7: Auto Journal Double-Entry Balance | 15.7 | Req 4.10, 9.1, 9.6 |
| Property 8: Forward-Only Production Status | 10.5 | Req 5.2, 5.5 |
| Property 9: Loyalty Tier Consistency | 11.7 | Req 6.2, 6.3 |
| Property 10: FIFO Stock Deduction Order | 14.9 | Req 8.1 |
| Property 11: GRN Stock Update Round-Trip | 14.10 | Req 8.6 |
| Property 12: Closed Period Immutability | 15.8 | Req 9.3 |
| Property 13: Depreciation Calculation Accuracy | 19.6 | Req 11.2 |
| Property 14: Public Tracking Timeline | 23.4 | Req 14.4 |

### Ringkasan Sprint Roadmap

| Sprint | Tasks | Durasi | Focus |
|--------|-------|--------|-------|
| Sprint 1–2 | 1–8 | 5 minggu | Foundation, Migrations, Models, Auth, RBAC, Master Data, UI |
| Sprint 3–4 | 9–13 | 5 minggu | POS, Production Tracking, CRM, Loyalty, Promotions |
| Sprint 5–6 | 14–17 | 5 minggu | Inventory, Procurement, Finance, Double-Entry, Auto Journals |
| Sprint 7 | 18–21 | 4 minggu | HR, Fixed Assets, Dashboards & Analytics |
| Sprint 8 | 22–26 | 4 minggu | Reporting, Export, Public Tracking, QA, Deployment |
