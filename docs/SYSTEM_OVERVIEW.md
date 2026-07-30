# System Overview — Istana Laundry Management System

**Versi:** 1.1 · **30 Juli 2026**  
**Audience:** developer, tech lead, stakeholder teknis

---

## 1. Ringkasan

Semi-ERP laundry multi-cabang berbasis **Laravel 13**, UI **Blade + Alpine.js + Tailwind CSS v4**, database **MySQL 8**, runtime **Docker Compose**, API **Laravel Sanctum**, otorisasi **Spatie Permission**.

Klien: **Istana Laundry Samarinda** · Dev: **KOU / Alenkosa.id**

Fase aktif: **TEST 2** ([PHASE_TEST2.md](PHASE_TEST2.md) · issues #29–#36).

---

## 2. Arsitektur logis

```text
┌─────────────────────────────────────────────────────────┐
│  Presentation: Blade views, Alpine, Chart.js, Vite     │
├─────────────────────────────────────────────────────────┤
│  HTTP: routes/web.php · routes/api.php · middleware     │
│        auth, verified?, branch.scope, role: (modul)     │
├─────────────────────────────────────────────────────────┤
│  Application: Controllers → Services → Observers → Jobs │
│    POS, Production, Finance/Journal, Loyalty, Audit     │
├─────────────────────────────────────────────────────────┤
│  Domain models + BranchScoped + Auditable traits        │
├─────────────────────────────────────────────────────────┤
│  MySQL │ Cache (database) │ Queue jobs (database)       │
└─────────────────────────────────────────────────────────┘
```

### 2.1 Multi-cabang
- Session key: `scoped_branch_id`
- Middleware: `BranchScopeMiddleware` (tolak user `is_active=false`)
- Trait: `BranchScoped` (fail-safe branch user jika session kosong)
- Super-role: `POST /switch-branch`

### 2.2 Alur order utama

```text
POS store → Order (TERIMA, payment_*) → OrderItems
                │
                ├─ payment paid → OrderObserver → PostOrderJournalJob (queue)
                │                      → loyalty + cache bust
                └─ Production updateStatus → ProductionStatusLog → … → DIAMBIL
```

### 2.3 Alur pengadaan

```text
Supplier → PR → PO → GRN confirm → PostGrnJournalJob (stock + journal)
```

---

## 3. Peta modul ↔ route utama

| Modul | Route | Notes |
|-------|-------|-------|
| Dashboard | `/dashboard` | Owner chart scope-sensitive (#30 open) |
| POS | `/pos` | + `pos.customers.store` |
| Production | `/production` | paginate 15; search UX #32 |
| Performance | `/performance` | export #36 |
| Customers | `/customers` | CRM stats #33 |
| Finance reports | `/finance/reports` | charts #35 |
| HR | `/hr` | payroll calc #31 |
| Assets | `/assets` | export #36 |
| Public track | `/track` | rate-limit + masked PII |
| API | `/api/*` | Sanctum + login throttle |

---

## 4. Stack & runtime

Laravel 13 · PHP 8.3+ · Sanctum · Spatie Permission v8 · Blade/Alpine/Tailwind · Chart.js · MySQL 8 · dompdf · maatwebsite/excel · Docker · GHCR CI/CD

**Queue:** `php artisan queue:work --tries=3` wajib di lingkungan yang memproses order/GRN.

---

## 5. Keamanan (post #14–#20)

| Area | Kondisi |
|------|---------|
| Role middleware modul sensitif | ✅ |
| Public register | ✅ Off |
| API / password throttle | ✅ |
| Tenant + track PII | ✅ |
| Audit mutasi bisnis | ✅ Auditable trait |
| Journal race + idempotency | ✅ |
| Docker seed guard + nginx headers | ✅ |
| Timezone WITA | 🔓 #29 |

---

## 6. Performa (post #21)

| Area | Kondisi |
|------|---------|
| Dashboard N+1 branch/hari | ✅ Aggregated queries |
| `branches:list` cache | ✅ TTL + invalidate on Branch save |
| FinancialReportService batch balances | ✅ |
| Journal/GRN via queue jobs | ✅ |
| Redis as primary cache | Opsional backlog |

---

## 7. Lingkungan

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan queue:work --tries=3
docker compose exec app php artisan test
```

---

## 8. Dokumentasi terkait

| Dokumen | Isi |
|---------|-----|
| [SRS.md](SRS.md) | Requirement |
| [PRODUCT_ROADMAP.md](PRODUCT_ROADMAP.md) | Gelombang rilis |
| [PHASE_TEST2.md](PHASE_TEST2.md) | Fase aktif |
| [PHASE_SECURITY_CACHE.md](PHASE_SECURITY_CACHE.md) | Arsip security/cache |
| [AI_PROMPTS.md](AI_PROMPTS.md) | Prompt AI |
| [tasks.md](../tasks.md) | Backlog harian |
