# Phase: Security Hardening + Caching

> Started: 2026-07-28  
> Branch: **`chore/security-and-caching`**  
> Epic: [#14](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/14) · Linear [KIL-22](https://linear.app/kiloux/issue/KIL-22)

## Workflow

```bash
git fetch origin
git checkout chore/security-and-caching
git pull origin chore/security-and-caching
```

Scope = **non-functional only** (security + cache/queue). No large business features.

## Issue map

| Priority | GH | Linear | Title | Status |
|----------|-----|--------|--------|--------|
| Epic | [#14](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/14) | KIL-22 | Parent tracking | Open |
| P0 | [#15](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/15) | KIL-23 | Role middleware | ✅ Done |
| P0 | [#16](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/16) | KIL-24 | Auth register / API throttle | ✅ Done |
| P0 | [#17](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/17) | KIL-25 | Tenant /track + BranchScoped | ✅ Done |
| P0 | [#18](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/18) | KIL-26 | Audit log mutations | ✅ Done |
| P1 | [#19](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/19) | KIL-27 | Journal lock + idempotency | ✅ Done |
| P1 | [#20](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/20) | KIL-28 | Docker/Nginx hygiene | ✅ Done |
| Perf | [#21](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/21) | KIL-29 | Caching + queue | Open |

Order: **#15 → #16 → #17 → #18 → #19 → #20 → #21**

Prompts: [AI_PROMPTS.md](AI_PROMPTS.md)

---

## Work Log

### #15 — Role & Permission Middleware (P0) ✅

**Status:** Completed  
**Date:** 2026-07-28

**Changes:**
- `routes/web.php` — Added role middleware to 8 sensitive route groups:
  - Finance (COA, journals, periods, reports): `Finance|Owner|Super_Admin|Developer`
  - Procurement (suppliers, PR, PO, GRN): `Branch_Admin|Owner|Super_Admin|Developer`
  - HR & Payroll: `Finance|Owner|Super_Admin|Developer`
  - Refund: `Branch_Admin|Owner|Super_Admin|Developer`
  - Inventory (write operations): `Branch_Admin|Owner|Super_Admin|Developer`
  - Fixed Assets: `Finance|Owner|Super_Admin|Developer`
  - Audit Logs: `Super_Admin|Developer`
  - Promotions: `Branch_Admin|Owner|Super_Admin|Developer`
- Inventory view (`GET /inventory`) kept without role restriction for Cashier access

**Testing:**
- Cashier cannot access: `/finance/journals`, `/procurement/purchase-orders`, `/hr/payrolls`, `/refunds`, `/finance/periods/{id}/close`
- Cashier can access: `/inventory` (view only), `/pos`, `/production`
- Finance role can access: Finance routes, HR/Payroll, Fixed Assets
- Branch_Admin can access: Procurement, Refund, Inventory, Promotions
- Owner/Super_Admin/Developer can access: All routes

---

### #16 — Auth Hardening (P0) ✅

**Status:** Completed  
**Date:** 2026-07-28

**Changes:**
- `routes/auth.php` — Disabled public self-registration (commented out register routes)
- `routes/auth.php` — Added throttle to password reset: `throttle:3,1` for email, `throttle:5,1` for store
- `routes/api.php` — Added throttle to API login: `throttle:10,1`
- `app/Models/User.php` — Documented MustVerifyEmail status (currently disabled with TODO)

**Testing:**
- `/register` returns 404
- 11x failed API login → 429 Too Many Requests
- 4x password reset email → 429
- Web login still has existing throttle (IP 10/min, email 5 attempts, lockout 15 min)

---

### #17 — Tenant Isolation (P0) ✅

**Status:** Completed  
**Date:** 2026-07-28

**Changes:**
- `app/Http/Middleware/BranchScopeMiddleware.php` — Reject `is_active=false` users (logout + redirect)
- `app/Models/Traits/BranchScoped.php` — Fail-safe: fallback to user's branch_id when session empty
- `routes/web.php` — Added throttle `throttle:30,1` and format validation to `/track`
- `routes/api.php` — Added throttle `throttle:30,1` to `/api/track`
- `app/Http/Controllers/Api/OrderTrackingController.php` — Added order number format validation
- `app/Http/Resources/OrderTrackingResource.php` — **NEW** — Limited PII resource (phone masked)
- `app/Http/Controllers/Api/OrderTrackingController.php` — Use OrderTrackingResource instead of OrderResource

**Testing:**
- Inactive user automatically logged out on any branch.scope request
- BranchScoped trait prevents cross-branch data leaks when session empty
- Track endpoints rate-limited (30/min per IP)
- Order number format validated (alphanumeric + dash only)
- Public tracking returns masked phone numbers (last 4 digits only)
- RefundController already filters by branch_id (verified)

---

### #18 — Audit Log Bisnis (P0) ✅

**Status:** Completed  
**Date:** 2026-07-28

**Changes:**
- `app/Models/Traits/Auditable.php` — **NEW** — Trait with model event hooks (created, updated, deleted)
  - Auto-logs model mutations to audit_logs table
  - Excludes sensitive fields (password, token, secret, etc.)
  - Skips logging when no authenticated user (seeder/console)
  - Prevents infinite loop by skipping AuditLog model itself
- Applied Auditable trait to:
  - `app/Models/Order.php`
  - `app/Models/Journal.php`
  - `app/Models/Payroll.php`
  - `app/Models/Refund.php`
  - `app/Models/Supplier.php`
  - `app/Models/PurchaseOrder.php`
  - `app/Models/GoodsReceivedNote.php`

**Testing:**
- Create/update/delete Order → audit log entry created
- Post/reverse Journal → audit log entry created
- Create/update Payroll → audit log entry created
- Create/approve/reject Refund → audit log entry created
- Create/update Supplier → audit log entry created
- Create/update PurchaseOrder → audit log entry created
- Create/update GoodsReceivedNote → audit log entry created
- Password fields excluded from audit logs
- Seeder operations do not create audit logs (no auth user)

---

### #19 — Journal Race-Safe (P1) ✅

**Status:** Completed  
**Date:** 2026-07-28

**Changes:**
- `app/Services/Finance/JournalService.php` — Added `lockForUpdate()` for reference generation to prevent race conditions
- `app/Services/Finance/JournalService.php` — Added idempotency check before creating journal (check if journal already exists for source)
- `database/migrations/2026_07_28_000001_add_unique_reference_to_journals.php` — **NEW** — Add unique constraint on `(branch_id, reference)` to prevent duplicate references
- `app/Services/Finance/JournalService.php` — Added Log facade for error logging (not silent)
- `app/Services/Finance/JournalService.php` — Log errors for: unbalanced journals, closed period attempts, idempotency violations

**Testing:**
- Simultaneous journal creation → no duplicate references due to lockForUpdate
- Attempt to create duplicate journal for same source → throws exception with existing reference
- Unique constraint prevents duplicate references at DB level
- Check Laravel logs for journal errors (balance, period, idempotency)

---

### #20 — Docker/Nginx Hygiene (P1) ✅

**Status:** Completed  
**Date:** 2026-07-28

**Changes:**
- `docker/entrypoint.sh` — Guard `db:seed` with `APP_ENV != production` check to prevent data overwrites in production
- `docker/nginx/default.conf` — Added security headers:
  - `X-Frame-Options: SAMEORIGIN`
  - `X-Content-Type-Options: nosniff`
  - `X-XSS-Protection: 1; mode=block`
  - `Referrer-Policy: strict-origin-when-cross-origin`
- `docker-compose.prod.yml` — Pinned nginx image version: `nginx:1.27-alpine`
- `.gitignore` — Verified `.env` is already in gitignore (line 3)

**Testing:**
- Set `APP_ENV=production` → entrypoint skips db:seed
- Set `APP_ENV=local` → entrypoint runs db:seed
- Check nginx response headers → security headers present
- Verify docker-compose.prod.yml uses pinned image versions

---

## Remaining Issues

### #21 — Caching + Queue (Perf)
- Cache branch list and dashboard aggregates with TTL
- Fix N+1 in DashboardController
- Optimize FinancialReportService queries
- Queue heavy observers (OrderObserver, GRNObserver)
- Document config:cache + route:cache for deployment
