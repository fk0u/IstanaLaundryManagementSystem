# Phase: Security Hardening + Caching — **COMPLETED**

> Started: 2026-07-28  
> Completed: 2026-07-29  
> Branches: `chore/security-and-caching`, `perf/caching-and-queue` → merge `master`  
> Epic: [#14](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/14) · Linear KIL-22 — **Closed**

**Next phase:** [PHASE_TEST2.md](PHASE_TEST2.md) (UAT Notes #2 · issues #29–#36)

---

## Issue map (final)

| Priority | GH | Title | Status |
|----------|-----|--------|--------|
| Epic | #14 | Parent tracking | ✅ Closed |
| P0 | #15 | Role middleware | ✅ |
| P0 | #16 | Auth register / API throttle | ✅ |
| P0 | #17 | Tenant /track + BranchScoped | ✅ |
| P0 | #18 | Audit log mutations | ✅ |
| P1 | #19 | Journal lock + idempotency | ✅ |
| P1 | #20 | Docker/Nginx hygiene | ✅ |
| Perf | #21 | Caching + queue observers | ✅ |

---

## Work log ringkas

### #15 Role middleware
Route groups Finance, Procurement, HR, Refund, Inventory write, Assets, Audit Logs, Promotions dilindungi `role:`.

### #16 Auth hardening
Public register off; throttle API login + password reset; MustVerifyEmail documented.

### #17 Tenant isolation
Inactive user rejected; BranchScoped fail-safe; track rate-limit + order format + masked phone resource.

### #18 Audit
Trait `Auditable` on Order, Journal, Payroll, Refund, Supplier, PO, GRN.

### #19 Journal
`lockForUpdate`, idempotency by source, unique `(branch_id, reference)`, error logging.

### #20 Docker/Nginx
No seed on production; security headers; nginx image pin; `.env` gitignored.

### #21 Cache + queue
Dashboard aggregated queries + `branches:list` cache; FinancialReportService batched balances; `PostOrderJournalJob` / `PostGrnJournalJob`; jobs tables; queue worker required in prod.

---

## Deployment reminders (masih berlaku)

```bash
php artisan queue:work --tries=3 --backoff=10
php artisan config:cache && php artisan route:cache
```

Tanpa worker: journal/GRN side-effects menumpuk di tabel `jobs`.
