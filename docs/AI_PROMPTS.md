# AI Prompts — Istana Laundry (Security + Caching phase)

> Updated: 2026-07-28  
> Branch kerja: **`chore/security-and-caching`**  
> Epic: GitHub #14

Pakai di AI **baru** (Cursor / Claude / dll). Satu sesi = **satu issue**.  
Baca file nyata di repo sebelum edit. Minimal diff. Jangan refactor di luar scope.

---

## 0) Bootstrap (tempel sekali di awal chat)

```
Kamu senior Laravel engineer.

## Project
- Istana Laundry Management System — semi-ERP laundry multi-cabang
- Branch: chore/security-and-caching (JANGAN kerja di master kecuali diminta)
- Stack: Laravel 13, PHP 8.3+, Blade, Alpine, Tailwind, MySQL, Spatie Permission, Sanctum, Docker
- Multi-branch: trait BranchScoped + middleware branch.scope + session scoped_branch_id
- Production status linear: TERIMA → PILAH → CUCI → KERING → LIPAT → CEK → SIAP → DIAMBIL
- Docs: tasks.md, docs/PHASE_SECURITY_CACHE.md

## Aturan
1. Baca file terkait dulu. Jangan mengarang route/API.
2. Minimal diff — tidak ada feature bisnis baru di phase ini.
3. Hormati nama role Spatie yang ada di seeder (Super_Admin, Branch_Admin, dll).
4. Jangan commit .env / secrets.
5. Setelah selesai: list file diubah + cara test + saran commit message dengan Refs #N.

Konfirmasi siap, tunggu prompt issue berikutnya.
```

---

## #15 — Role & permission middleware (P0)

```
Task GH #15 | Linear KIL-23 | Branch chore/security-and-caching

## Goal
Semua modul sensitif wajib role/permission, bukan hanya auth + branch.scope.

## Problem
Finance, Procurement (approve/send/confirm), HR/Payroll, POS, Refund, Inventory, dll.
hanya middleware auth — Cashier secara teknis bisa hit endpoint kritis.

## Do
1. Baca routes/web.php — kelompokkan route per modul.
2. Tambah middleware role: (atau permission:) sesuai SRS:
   - Finance / journals / periods / reports → Finance, Owner, Super_Admin, Developer
   - Procurement approve/send/confirm → Branch_Admin+, Owner, …
   - HR payroll → role HR/Owner/…
   - POS tetap Cashier+ yang relevan
3. Samakan pola dengan services.* yang sudah role:Developer|Owner|Super_Admin.
4. Jangan putus akses Owner/Developer.
5. Test mental: role Cashier tidak bisa close period / approve PO / store payroll.

## Out of scope
UI redesign, cache, 2FA.

Jelaskan mapping role per grup route.
```

---

## #16 — Auth hardening (P0)

```
Task GH #16 | Linear KIL-24

## Goal
1. Nonaktifkan atau batasi self-registration publik (prefer admin-only create user).
2. Throttle POST /api/login + cek is_active / locked_until seperti login web.
3. Throttle eksplisit password.email / password.store di routes/auth.php.
4. Evaluasi MustVerifyEmail — minimal dokumentasikan jika belum diaktifkan penuh.

## Files
routes/auth.php, routes/api.php, Api\AuthController, RegisteredUserController,
AuthenticatedSessionController, LoginRequest

## Acceptance
Guest tidak mass-register ke dashboard operasional; API login ter-throttle.
```

---

## #17 — Tenant isolation (P0)

```
Task GH #17 | Linear KIL-25

## Goal
Cegah data leak lintas cabang.

## Focus
1. Public /track dan GET /api/track/{orderNumber} — batasi PII yang diekspos;
   rate-limit; pertimbangkan validasi format nota; jangan andalkan session kosong = no scope.
2. RefundController index — pastikan query order/refund ter-filter branch.
3. BranchScopeMiddleware: set scope konsisten; tolak user is_active=false.
4. Route sensitif wajib lewat branch.scope kecuali memang global super-user pattern.

Baca: BranchScopeMiddleware, BranchScoped trait, track routes, RefundController.
```

---

## #18 — Audit log bisnis (P0)

```
Task GH #18 | Linear KIL-26

## Goal
Mutasi model kritis tercatat di audit_logs (bukan hanya login/logout).

## Approach (pilih minimal invasif)
- Observer generik / trait Auditable pada: Order, Journal, Payroll, Refund,
  Supplier, PurchaseOrder, GoodsReceivedNote, Customer, Service (sesuaikan prioritas)
- Atau panggil AuditLogService di service layer write paths

## Rules
- action string pendek atau pastikan kolom action cukup (64+)
- Jangan log password/token
- old_values/new_values JSON untuk field penting saja

Acceptance: ubah status produksi / buat supplier / post journal → ada baris audit.
```

---

## #19 — Journal race-safe (P1)

```
Task GH #19 | Linear KIL-27

## Goal
JournalService anti race + anti double-post.

## Do
1. lockForUpdate (atau counter table) saat generate sequence reference
2. Idempotency: jika journal untuk source_type+source_id sudah ada → skip/return existing
3. Unique constraint reference per branch bila feasible
4. Observer: error journal tidak boleh silent tanpa jejak (log + optional rethrow/flag)

Files: app/Services/Finance/JournalService.php, OrderObserver, GRNObserver

Acceptance: concurrent paid orders tidak collision; retry tidak dobel journal.
```

---

## #20 — Docker / Nginx hygiene (P1)

```
Task GH #20 | Linear KIL-28

## Do
1. docker/entrypoint.sh — JANGAN db:seed jika APP_ENV=production (atau SEED_ON_BOOT=false)
2. docker/nginx/default.conf — headers: X-Frame-Options, X-Content-Type-Options,
   Referrer-Policy, baseline CSP, (HSTS hanya jika HTTPS terminator jelas)
3. docker-compose.prod.yml — hindari :latest mengambang bila memungkinkan; dokumentasikan pin
4. Pastikan .gitignore memuat .env

Jangan ubah dev DX secara merusak (local tetap boleh seed).
```

---

## #21 — Caching + queue (Perf)

```
Task GH #21 | Linear KIL-29

## Goal
1. Cache::remember untuk Branch list & agregat dashboard (TTL pendek + invalidasi wajar)
2. Hilangkan N+1 DashboardController (loop sum per branch/hari → aggregated query)
3. Kurangi query FinancialReportService per COA (batch/sum grouping)
4. OrderObserver / GRNObserver kerja berat → Job ShouldQueue (QUEUE_CONNECTION=database ok)
5. Dokumentasikan config:cache + route:cache di CD/deploy

## Constraints
- Invalidasi cache saat order paid / journal post / branch berubah
- Jangan cache data lintas-tenant tanpa key branchId
- Redis opsional; file/database cache acceptable dulu

Acceptance: dashboard owner tidak 1 query per cabang; observer tidak block request lama.
```

---

## Setelah tiap issue

```
1. File yang diubah
2. Cara test manual (role + skenario)
3. Commit message 1 baris + Refs #N
4. Ingatkan close GH issue + Linear Done setelah merge
```
