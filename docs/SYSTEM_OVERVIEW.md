# System Overview — Istana Laundry Management System

**Versi:** 1.0 · **28 Juli 2026**  
**Audience:** developer, tech lead, stakeholder teknis

---

## 1. Ringkasan

Semi-ERP laundry multi-cabang berbasis **Laravel 13**, UI **Blade + Alpine.js + Tailwind CSS v4**, database **MySQL 8**, runtime **Docker Compose**, API **Laravel Sanctum**, otorisasi **Spatie Permission**.

Klien: **Istana Laundry Samarinda** · Dev: **KOU / Alenkosa.id**

---

## 2. Arsitektur logis

```text
┌─────────────────────────────────────────────────────────┐
│  Presentation: Blade views, Alpine, Chart.js, Vite     │
├─────────────────────────────────────────────────────────┤
│  HTTP: routes/web.php · routes/api.php · middleware     │
│        auth, branch.scope, role (parsial)               │
├─────────────────────────────────────────────────────────┤
│  Application: Controllers → Services → Observers        │
│    POS, Production, Finance/Journal, Loyalty, Audit     │
├─────────────────────────────────────────────────────────┤
│  Domain models + BranchScoped global scope              │
├─────────────────────────────────────────────────────────┤
│  MySQL │ Redis (tersedia, cache app belum optimal)      │
└─────────────────────────────────────────────────────────┘
```

### 2.1 Multi-cabang
- Session key: `scoped_branch_id`
- Middleware: `App\Http\Middleware\BranchScopeMiddleware`
- Trait model: `App\Models\Traits\BranchScoped`
- Super-role dapat `POST /switch-branch`

### 2.2 Alur order utama

```text
POS store → Order (TERIMA, payment_*) → OrderItems
                │
                ├─ payment paid → OrderObserver → Journal + Loyalty
                │
                └─ Production updateStatus (linear) → ProductionStatusLog
                         → … → SIAP → DIAMBIL
```

### 2.3 Alur pengadaan

```text
Supplier → PR (pending→approved) → PO (draft→sent→confirmed)
    → GRN (draft→confirmed) → stock + journal (observer)
```

---

## 3. Peta modul ↔ route utama

| Modul | Route prefix / name | Controller / notes |
|-------|---------------------|--------------------|
| Dashboard | `/dashboard` | `DashboardController` |
| POS | `/pos` | `POSController` + `pos.customers.store` |
| Orders | `/orders` | `OrderController` |
| Invoice | `/invoices/{order}` | `InvoiceController` |
| Production | `/production` | `ProductionController` · paginate 15 |
| Performance | `/performance` | `PerformanceController` |
| Customers | `/customers` | Closure + search `?q=` |
| Promotions | `/promotions` | Closure CRUD dasar |
| Services | `/services` | `ServiceController` + `role:` |
| Inventory | `/inventory` | Closure |
| Suppliers | `/procurement/suppliers` | `SupplierController` |
| PR/PO/GRN | `/procurement/*` | Procurement controllers |
| Finance COA | `/finance` | Closure |
| Journals | `/finance/journals` | `JournalController` |
| Periods | `/finance/periods` | `AccountingPeriodController` |
| Reports | `/finance/reports` | `FinancialReportController` |
| HR | `/hr` | `HRController` |
| Assets | `/assets` | `AssetController` |
| Refunds | `/refunds` | `RefundController` |
| Audit | `/audit-logs` | Closure |
| Public track | `/track` | Closure (no auth) |
| API | `/api/*` | Sanctum |

---

## 4. Stack & runtime

| Komponen | Pilihan |
|----------|---------|
| PHP | 8.3+ (image 8.4-FPM) |
| Framework | Laravel 13 |
| Auth web | Breeze session |
| Auth API | Sanctum |
| Permission | spatie/laravel-permission v8 |
| PDF/Excel/QR | dompdf, maatwebsite/excel, simple-qrcode |
| Backup | spatie/laravel-backup |
| CI | `.github/workflows/ci.yml` |
| CD | `deploy.yml` → GHCR |

Docker services (dev): `app`, `nginx`, `mysql`, `redis`, `node` (lihat `docker-compose.yml`).

---

## 5. Keamanan — posisi saat ini

| Area | Kondisi |
|------|---------|
| CSRF web | Default Laravel ON |
| Mass assignment | `#[Fillable]` models |
| Login web throttle/lockout | Ada |
| Role middleware | Hampir hanya `services.*` |
| Audit bisnis | Minim |
| Public track | Perlu pengerasan isolasi/PII |

Detail temuan & rencana: issue **#14–#21**, dokumen `PHASE_SECURITY_CACHE.md`.

---

## 6. Performa — posisi saat ini

- `Cache::` hampir tidak dipakai di app code
- Queue connection terkonfigurasi; job domain minim
- N+1 risk: dashboard owner loop, financial report per COA

Rencana: issue **#21**, **#24**.

---

## 7. Lingkungan & perintah

```bash
# Dev
docker compose up -d --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan test

# Branch fase non-functional
git checkout chore/security-and-caching
```

Prod: `docker-compose.prod.yml` + image GHCR; secrets Actions untuk deploy SSH opsional.

---

## 8. Dokumentasi terkait

| Dokumen | Isi |
|---------|-----|
| [SRS.md](SRS.md) | Requirement AS-IS / TO-BE |
| [PRODUCT_ROADMAP.md](PRODUCT_ROADMAP.md) | Visi produk & gelombang rilis |
| [PHASE_SECURITY_CACHE.md](PHASE_SECURITY_CACHE.md) | Epic security/cache |
| [AI_PROMPTS.md](AI_PROMPTS.md) | Prompt implementasi |
| [/tasks.md](../tasks.md) | Backlog ringkas |
| [/README.md](../README.md) | Quick start |
