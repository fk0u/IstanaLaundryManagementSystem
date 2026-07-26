# Istana Laundry — Tasks (Remaining)

> Generated: 2026-07-26  
> Repo: https://github.com/fk0u/IstanaLaundryManagementSystem  
> Linear project: [Istana Laundry Management System](https://linear.app/kiloux/project/istana-laundry-management-system-35a0125eaea3)

## Stack singkat
- Laravel 13 / PHP 8.3+ (Dockerfile: 8.4)
- Blade + Alpine.js + Tailwind
- MySQL 8 (prod) / SQLite (local optional)
- Spatie Permission, multi-branch (`BranchScoped`, middleware `branch.scope`)
- Local: `docker compose up -d --build` → http://localhost:8000

## Status

### Done
| GH | Linear | Judul |
|----|--------|-------|
| #1 | KIL-10 | [Bug] HR & Payroll crash setelah generate payroll |
| #2 | KIL-11 | [Bug] "Lihat Semua" dashboard salah ke POS |
| #11 | KIL-20 | [Feature] CI/CD (sudah di-merge) |

### Open — kerjakan berurutan

---

### P0 — Bug Fixes (wajib dulu)

#### T1 · GH #3 + #4 · Linear KIL-12 + KIL-13
**[Bug] Production status update gagal (Cuci→Kering, Siap→Diambil)**

- **Error:** `SQLSTATE[22001]: Data too long for column 'action' at row 1` pada `audit_logs`
- **Root cause:** `ProductionController::updateStatus` memanggil:
  ```php
  $this->auditLogService->log("update_production_status_{$newStatus}", ...)
  ```
  Kolom `action` di migration = `string(30)`.  
  Contoh: `update_production_status_KERING` = **31** char, `..._DIAMBIL` = **32** char → truncate error.
- **Files:**
  - `app/Http/Controllers/ProductionController.php`
  - `app/Services/AuditLogService.php` (opsional harden)
  - `database/migrations/2026_07_24_090030_create_audit_logs_table.php` ATAU migration baru alter column
- **Fix yang disarankan:**
  1. Shorten action string (mis. `prod_status:{STATUS}` atau `update_prod_status`) **dan/atau**
  2. Perlebar kolom `action` ke `string(64)` / `string(100)` via migration baru
  3. Pastikan transisi CUCI→KERING dan SIAP→DIAMBIL sukses end-to-end
- **Test:** Login workshop/owner → Production → update status maju 1 langkah untuk KERING & DIAMBIL tanpa 500.

#### T2 · GH #5 · Linear KIL-14
**[Bug] Laporan Keuangan nominal selalu 0**

- **Files:**
  - `app/Http/Controllers/Finance/FinancialReportController.php`
  - `app/Services/FinancialReportService.php` dan/atau `app/Services/Finance/FinancialReportService.php` (ada 2 path — cek mana yang di-bind)
  - `resources/views/finance/reports.blade.php` / `finance/reports/index.blade.php`
  - Pastikan POS/order create menulis journal (lihat `OrderObserver`, `JournalService`)
- **Investigasi:**
  1. Apakah transaksi POS membuat journal entries?
  2. Apakah filter `branch_id` / `year` / `month` terlalu ketat?
  3. Apakah service membaca tabel salah / status journal tidak `posted`?
- **Acceptance:** Setelah ada order paid + journal, laporan income/trial balance/balance sheet ≠ 0.

---

### P1 — Improvements

#### T3 · GH #6 · Linear KIL-15
**[Improvement] Production: list + filter status DIAMBIL**

- **Sekarang:** `ProductionController::index` exclude `DIAMBIL`:
  ```php
  ->where('production_status', '!=', 'DIAMBIL');
  ```
- **Perlu:** Tab/filter termasuk **Diambil**; tampilkan list order berstatus DIAMBIL; sorting opsional (tanggal, nota).
- **Files:** `ProductionController.php`, `resources/views/production/index.blade.php`

#### T4 · GH #7 · Linear KIL-16
**[Improvement] POS Customer UX**

1. Modal/form **tambah customer baru** dari POS (tanpa ke CRM)
2. **Hapus** opsi Walk-In dari dropdown
3. Dropdown customer **searchable** (Select2 / Choices.js / Alpine filter)
- **Files:** `resources/views/pos/index.blade.php`, `POSController.php`, route `customers.store` sudah ada — bisa reuse

#### T5 · GH #8 · Linear KIL-17
**[Improvement] CRM search bar / Select2**

- Search dinamis di `resources/views/customers/index.blade.php`
- Query param `?q=` di route customers index (saat ini closure di `routes/web.php`)

---

### P2 — Features

#### T6 · GH #9 · Linear KIL-18
**[Feature] CRUD jenis layanan cuci (Master Data)**

- Model `Service` sudah ada + seeder
- Perlu UI CRUD: list, create, edit, soft-deactivate
- Harga per cabang: `ServiceBranchPrice`
- Route + policy/role: Owner / Super_Admin / Branch_Admin sesuai SRS

#### T7 · GH #10 · Linear KIL-19
**[Feature] Menu Memantau Kinerja accessible**

- Route sudah ada: `performance.index` → `PerformanceController` + view `performance/index.blade.php`
- Kemungkinan **broken link di sidebar** (label/path salah)
- **Files:** `resources/views/components/sidebar.blade.php`, cek `route('performance.index')`
- Pastikan role yang berhak bisa akses; perbaiki link/menu label "Memantau Kinerja"

---

### Opsional / backlog

#### T8 · GH #11 (boleh close)
CI/CD sudah ada. Opsional follow-up:
- Nonaktifkan `db:seed` di `docker/entrypoint.sh` untuk production
- GitHub Environments staging/prod

---

## Urutan kerja yang disarankan

```
T1 (#3+#4 audit_logs action)  →  T2 (#5 laporan keuangan)
  →  T3 (#6 DIAMBIL filter)  →  T4 (#7 POS customer)  →  T5 (#8 CRM search)
  →  T6 (#9 service CRUD)  →  T7 (#10 performance menu)
```

## Prompt AI
Lihat file **`docs/AI_PROMPTS.md`** — copy-paste per task ke AI baru (Cursor/Claude/dll).
