# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Security

- **#15** — Add role middleware to sensitive route groups
- **#16** — Harden authentication mechanisms
- **#17** — Strengthen tenant isolation
- **#18** — Implement audit logging for business mutations
- **#19** — Add journal race-safety and idempotency
  - Add `lockForUpdate()` for reference generation to prevent race conditions
  - Add idempotency check before creating journal (check if journal already exists for source)
  - Add unique constraint on `(branch_id, reference)` to prevent duplicate references
  - Add error logging for journal operations (balance, period, idempotency violations)
- **#20** — Docker/Nginx production hygiene
  - Guard `db:seed` in entrypoint.sh for production environments
  - Add security headers to nginx config (X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Referrer-Policy)
  - Pin nginx image version in docker-compose.prod.yml (nginx:1.27-alpine)
  - Verify .env is in .gitignore

- **#16** — Harden authentication mechanisms
  - Disable public self-registration (admin-only user creation)
  - Add throttle to API login endpoint (10 requests/minute)
  - Add throttle to password reset email (3 requests/minute)
  - Add throttle to password reset store (5 requests/minute)
  - Document MustVerifyEmail status for future enablement

- **#17** — Strengthen tenant isolation
  - Reject inactive users (`is_active=false`) in BranchScopeMiddleware
  - Add fail-safe to BranchScoped trait (fallback to user's branch_id when session empty)
  - Add rate limiting to public track endpoints (30 requests/minute)
  - Add order number format validation to track routes (alphanumeric + dash only)
  - Create OrderTrackingResource with limited PII exposure (phone masked)
  - Prevent cross-branch data leaks when session is empty

- **#18** — Implement audit logging for business mutations
  - Create Auditable trait with automatic model event hooks (created, updated, deleted)
  - Apply Auditable trait to critical models: Order, Journal, Payroll, Refund, Supplier, PurchaseOrder, GoodsReceivedNote
  - Exclude sensitive fields from audit logs (password, token, secret, etc.)
  - Skip audit logging during seeder/console operations (no authenticated user)
  - Prevent infinite loops by excluding AuditLog model from self-auditing

### Changed

- `routes/web.php` — Added role middleware groups for Finance, Procurement, HR, Refund, Inventory, Assets, Audit Logs, Promotions
- `routes/auth.php` — Disabled registration routes, added throttle to password reset
- `routes/api.php` — Added throttle to login and track endpoints
- `app/Http/Middleware/BranchScopeMiddleware.php` — Reject inactive users
- `app/Models/Traits/BranchScoped.php` — Add fail-safe for empty session
- `app/Models/User.php` — Document MustVerifyEmail status
- `app/Http/Controllers/Api/OrderTrackingController.php` — Add validation, use OrderTrackingResource
- `app/Http/Resources/OrderTrackingResource.php` — New resource with PII limiting
- `app/Models/Traits/Auditable.php` — New trait for audit logging
- `app/Models/Order.php` — Add Auditable trait
- `app/Models/Journal.php` — Add Auditable trait
- `app/Models/Payroll.php` — Add Auditable trait
- `app/Models/Refund.php` — Add Auditable trait
- `app/Models/Supplier.php` — Add Auditable trait
- `app/Models/PurchaseOrder.php` — Add Auditable trait
- `app/Models/GoodsReceivedNote.php` — Add Auditable trait

### Fixed

- Cashier role can no longer access critical endpoints (Finance, Procurement, HR, Refund, etc.)
- Inactive users are automatically logged out when accessing protected routes
- BranchScoped trait now prevents cross-branch data leaks when session is empty
- Public tracking endpoints now have rate limiting and input validation
- Sensitive fields (password, tokens) are excluded from audit logs

### Security

- All sensitive routes now require appropriate role-based access control
- API endpoints are rate-limited to prevent abuse
- Public tracking endpoints limit PII exposure
- Audit trail now captures all critical business mutations
- Tenant isolation strengthened with multiple fail-safes

---

## [1.0.0] - 2026-07-XX

### Added
- Initial release of Istana Laundry Management System
- Multi-branch laundry management with POS, Production, Finance, HR, Procurement modules
- Role-based access control with Spatie Permission
- Branch scoping with session-based tenant isolation
- Order tracking and status management
- Financial journaling and reporting
- Payroll management
- Supplier and procurement workflow (PR/PO/GRN)
- Customer loyalty program
- Inventory management
- Fixed assets tracking
