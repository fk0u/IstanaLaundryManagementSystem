# Phase: Security Hardening + Caching

> Started: 2026-07-28  
> Branch: **`chore/security-and-caching`** (base: `master` @ `93a1fe5`)  
> Epic: [#14](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/14)

## Workflow

```bash
git fetch origin
git checkout chore/security-and-caching
git pull origin chore/security-and-caching
# ... work one issue at a time ...
git checkout -b fix/sec-role-middleware   # optional per-issue branch
# PR → chore/security-and-caching → lalu PR ke master
```

Jangan merge fitur bisnis besar ke branch ini. Scope = non-functional only.

## Issue map

| Priority | GH | Linear (approx) | Title |
|----------|----|-----------------|--------|
| Epic | [#14](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/14) | Epic Security+Cache | Parent tracking |
| P0 | [#15](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/15) | Role middleware | AuthZ routes |
| P0 | [#16](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/16) | Auth harden | Register / API throttle |
| P0 | [#17](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/17) | Tenant | /track + BranchScoped |
| P0 | [#18](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/18) | Audit log | Business mutations |
| P1 | [#19](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/19) | Journal lock | Race + idempotency |
| P1 | [#20](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/20) | Docker/Nginx | Prod hygiene |
| Perf | [#21](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/21) | Caching | Cache + queue |

## Recommended order

```
#15 Role middleware
  → #16 Auth registration/throttle
  → #17 Tenant isolation
  → #18 Audit log mutations
  → #19 Journal lockForUpdate
  → #20 Docker/Nginx entrypoint
  → #21 Caching + queue observers
```

## Key files (from audit)

### Security
- `routes/web.php`, `routes/api.php`, `routes/auth.php`
- `app/Http/Middleware/BranchScopeMiddleware.php`
- `app/Models/Traits/BranchScoped.php`
- `app/Services/AuditLogService.php`
- `app/Services/Finance/JournalService.php`
- `app/Http/Controllers/Auth/*`
- `docker/entrypoint.sh`, `docker/nginx/default.conf`, `docker-compose.prod.yml`

### Caching
- `config/cache.php`, `config/queue.php`, `.env` (`CACHE_STORE`, `QUEUE_CONNECTION`)
- `app/Http/Controllers/DashboardController.php`
- `app/Services/Finance/FinancialReportService.php`
- `app/Observers/OrderObserver.php`, `GRNObserver.php`

## Definition of done (phase)

- [ ] All #15–#21 closed
- [ ] PR `chore/security-and-caching` → `master` reviewed
- [ ] Staging smoke: login roles, POS paid → journal once, dashboard load, no seed on prod entrypoint
