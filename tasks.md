# Istana Laundry — Tasks

> Updated: **2026-07-28**  
> Repo: https://github.com/fk0u/IstanaLaundryManagementSystem  
> Active branch: **`perf/caching-and-queue`** (based on `master`)  
> Linear: project *Istana Laundry Management System*

---

## Stack singkat

- Laravel 13 / PHP 8.3+ (Docker: PHP 8.4-FPM)
- Blade + Alpine.js + Tailwind v4 + Chart.js
- MySQL 8 · Redis (compose) · Sanctum · Spatie Permission
- Local: `docker compose up -d --build` → http://localhost:8000

```bash
git fetch origin
git checkout chore/security-and-caching
git pull origin chore/security-and-caching
```

---

## Phase done — operasional & Tech Lead TEST 1

Semua closed di GitHub + Linear:

| Cluster | Issues |
|---------|--------|
| Bugs | #1 HR · #2 Dashboard · #3–#4 Production status · #5 Laporan Keuangan |
| Improvements | #6 Diambil filter · #7 POS customer · #8 CRM search |
| Features | #9 Services CRUD · #10 Memantau Kinerja · #11 CI/CD |
| Procurement | #13 PR/PO/GRN + **Supplier** sub-page |

---

## Phase aktif — Security + Caching

**Epic:** [#14](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/14) · Linear [KIL-22](https://linear.app/kiloux/issue/KIL-22)  
**Guide:** [docs/PHASE_SECURITY_CACHE.md](docs/PHASE_SECURITY_CACHE.md)  
**Prompts:** [docs/AI_PROMPTS.md](docs/AI_PROMPTS.md)

### Urutan wajib

| # | GH | Linear | Priority | Task | Status |
|---|-----|--------|----------|------|--------|
| 1 | [#15](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/15) | KIL-23 | P0 | Role/permission middleware modul sensitif | ✅ Done |
| 2 | [#16](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/16) | KIL-24 | P0 | Auth: register, API login throttle, password reset | ✅ Done |
| 3 | [#17](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/17) | KIL-25 | P0 | Tenant isolation `/track` + BranchScoped | ✅ Done |
| 4 | [#18](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/18) | KIL-26 | P0 | Audit log mutasi bisnis | ✅ Done |
| 5 | [#19](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/19) | KIL-27 | P1 | Journal `lockForUpdate` + idempotency | ✅ Done |
| 6 | [#20](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/20) | KIL-28 | P1 | Docker/Nginx prod hygiene (seed guard, headers) | ✅ Done |
| 7 | [#21](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/21) | KIL-29 | Perf | Caching layer + queue observers | ✅ Done |

### Definition of done (fase)

- [x] #15–#21 closed + Linear Done
- [ ] PR `perf/caching-and-queue` → `master`
- [x] Smoke: role Cashier tidak akses approve PO/close period; journal tidak double-post; dashboard tidak N+1 parah; prod entrypoint tidak seed

### Aturan commit

```text
fix(sec): …    Refs #15
feat(audit): …  Refs #18
perf(cache): …  Refs #21
```

Satu issue ≈ satu fokus diff. Jangan campur fitur bisnis (POS UI baru, dll.) di branch ini.

---

## Backlog (setelah fase ini)

| Item | Catatan |
|------|---------|
| 2FA (evaluasi Fortify/custom) | Post-hardening |
| Enkripsi PII selektif (NIK, phone) | Butuh migration + akses pattern |
| `chore/pint-cleanup` | Style only, PR terpisah |
| Expand REST API (orders, customers, POS) | Branch `feat/api-*` |
| Redis sebagai `CACHE_STORE` + `QUEUE_CONNECTION` prod | Setelah #21 |

---

## Mapping cepat file kritis

| Topic | Path |
|-------|------|
| Routes web/API | `routes/web.php`, `routes/api.php`, `routes/auth.php` |
| Branch scope | `app/Http/Middleware/BranchScopeMiddleware.php`, `app/Models/Traits/BranchScoped.php` |
| Audit | `app/Services/AuditLogService.php` |
| Journal | `app/Services/Finance/JournalService.php`, `app/Observers/*` |
| Dashboard heavy | `app/Http/Controllers/DashboardController.php` |
| Reports heavy | `app/Services/Finance/FinancialReportService.php` |
| Docker/Nginx | `docker/entrypoint.sh`, `docker/nginx/default.conf`, `docker-compose.prod.yml` |
