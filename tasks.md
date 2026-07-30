# Istana Laundry — Tasks

> Updated: **2026-07-30**  
> Repo: https://github.com/fk0u/IstanaLaundryManagementSystem  
> Active phase: **TEST 2 / UAT fixes** (`feat/*` atau `fix/*` dari `master`)  
> Linear: project *Istana Laundry Management System*

---

## Stack singkat

- Laravel 13 / PHP 8.3+ (Docker: PHP 8.4-FPM)
- Blade + Alpine.js + Tailwind v4 + Chart.js
- MySQL 8 · Redis (compose) · Sanctum · Spatie Permission
- Local: `docker compose up -d --build` → http://localhost:8000

```bash
git fetch origin && git checkout master && git pull origin master
# kerjakan di branch khusus, contoh:
git checkout -b fix/payroll-zero-calc   # Refs #31
```

**Queue worker (wajib setelah #21):** `php artisan queue:work --tries=3`

---

## Phase selesai

### Tech Lead TEST 1 + operasional (#1–#13)
Bugs/improvements/features awal + PR/PO/GRN + Supplier — **closed**.

### Security + Caching (#14–#21) ✅
| # | Task | Status |
|---|------|--------|
| #15 | Role middleware modul sensitif | ✅ |
| #16 | Auth harden (no public register, throttle) | ✅ |
| #17 | Tenant isolation + track PII | ✅ |
| #18 | Audit log mutasi bisnis | ✅ |
| #19 | Journal lock + idempotency | ✅ |
| #20 | Docker/Nginx prod hygiene | ✅ |
| #21 | Cache dashboard/reports + queue observers | ✅ |

Detail: [docs/PHASE_SECURITY_CACHE.md](docs/PHASE_SECURITY_CACHE.md)

### Product enhancements gelombang B–C (#22–#28) ✅ closed
Promo, POS customer, dashboard dinamis, Finance UX, kinerja detail, payroll BPJS, aset/maintenance — **closed di tracker** (verifikasi UAT jika masih ada residual di lapangan).

---

## Phase aktif — TEST 2 (UAT 30 Jul 2026)

**Sumber:** Notes Laundry System #2 · UAT ~01:52 WITA, Kamis 30 Jul 2026  
**Prompts:** [docs/AI_PROMPTS.md](docs/AI_PROMPTS.md)  
**Guide:** [docs/PHASE_TEST2.md](docs/PHASE_TEST2.md)

### 🟢 Bugs (kerjakan dulu)

| Urutan | GH | Linear | Task | Status |
|--------|-----|--------|------|--------|
| 1 | [#31](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/31) | [KIL-39](https://linear.app/kiloux/issue/KIL-39) | Payroll generate → nominal semua **0** | ✅ Closed |
| 2 | [#30](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/30) | [KIL-38](https://linear.app/kiloux/issue/KIL-38) | Chart Komparasi Cabang kosong setelah switch scope global | ✅ Closed |
| 3 | [#29](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/29) | [KIL-37](https://linear.app/kiloux/issue/KIL-37) | Timestamp sistem → **GMT+8 / WITA** | ✅ Closed |

### 🟡 Improvements

| Urutan | GH | Linear | Task | Status |
|--------|-----|--------|------|--------|
| 4 | [#32](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/32) | [KIL-40](https://linear.app/kiloux/issue/KIL-40) | Production: search no. order + UI hide list untuk Staff | ✅ Closed |
| 5 | [#33](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/33) | [KIL-41](https://linear.app/kiloux/issue/KIL-41) | CRM: total/last transaksi, riwayat, WhatsApp | ✅ Closed |
| 6 | [#34](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/34) | [KIL-42](https://linear.app/kiloux/issue/KIL-42) | Receipt/WA: nomor order = hyperlink `/track` | ✅ Closed |
| 7 | [#35](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/35) | [KIL-43](https://linear.app/kiloux/issue/KIL-43) | Laporan Keuangan: grafik per tab | ✅ Closed |

### 2. Feature & Enterprise Enhancements

| Urutan | GH | Linear | Task | Status |
|--------|-----|--------|------|--------|
| 8 | [#36](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/36) | [KIL-44](https://linear.app/kiloux/issue/KIL-44) | Export CRM / Performance / Aset (PDF·Excel·CSV) | ✅ Closed |
| 9 | - | - | Payroll Global (Konsolidasi Seluruh Cabang), Locking Status `FINAL`, Biodata & Rekening Bank Staf | ✅ Closed |
| 10 | - | - | Auto-Sync Akun Staf `User` $\leftrightarrow$ `Employee` HR & Modul Manajemen Cabang (`/branches`) | ✅ Closed |

### Definition of done (TEST 2)

- [x] #29–#36 closed di GitHub + Linear Done
- [x] Smoke UAT ulang item A–H Notes #2
- [x] `APP_TIMEZONE=Asia/Makassar` (atau setara) + tampilan UI konsisten
- [x] Payroll generate menampilkan nominal non-zero yang masuk akal
- [x] Switch cabang → global: chart komparasi terisi tanpa hard refresh

### Aturan commit

```text
fix(hr): payroll zero calculation Refs #31
fix(dashboard): chart after global scope switch Refs #30
fix(tz): app timezone Asia/Makassar Refs #29
feat(production): order search and staff list toggle Refs #32
feat(crm): customer stats and whatsapp Refs #33
feat(export): crm performance assets Refs #36
```

Satu issue ≈ satu branch fokus. Jangan campur bug payroll dengan export aset di PR yang sama.

---

## Backlog lanjutan (setelah TEST 2)

| Item | Catatan |
|------|---------|
| KPI Dashboard Keuangan (omset **paid**, AR, selisih POS vs journal) | Spec sudah dibahas chat; belum issue formal |
| 2FA Owner/Finance | Post-hardening |
| Enkripsi PII selektif (NIK) | Migration + access pattern |
| Redis `CACHE_STORE` + `QUEUE_CONNECTION` prod | Opsional setelah #21 database queue stabil |
| `chore/pint-cleanup` | Style only, PR terpisah |
| Expand REST API (orders, customers, POS) | `feat/api-*` |
| WA gateway production-grade / QRIS | Gelombang D roadmap |

---

## Mapping file kritis (TEST 2)

| Topic | Path |
|-------|------|
| Timezone | `config/app.php`, `.env` `APP_TIMEZONE` |
| Dashboard chart | `DashboardController`, `resources/views/dashboard/owner.blade.php` |
| Payroll calc | `HR/HRController`, `Models/PayrollItem`, views `hr/*` |
| Production search | `ProductionController`, `production/index.blade.php` |
| CRM cards | `customers/*` routes/views, `Customer` ↔ `orders` |
| Receipt/WA link | `InvoiceController`, invoice views |
| Finance charts | `Finance/FinancialReportController`, `finance/reports*` |
| Export | controllers + `maatwebsite/excel` / dompdf |
