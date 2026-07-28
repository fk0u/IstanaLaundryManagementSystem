# Istana Laundry — Tasks

> Updated: 2026-07-28  
> Repo: https://github.com/fk0u/IstanaLaundryManagementSystem  
> Linear: Istana Laundry Management System

## Stack
Laravel 13 · PHP 8.3+ · Docker · Sanctum · Spatie Permission · multi-branch

Local: `docker compose up -d --build` → http://localhost:8000

---

## Phase complete (Tech Lead TEST 1 + ops)

| GH | Status |
|----|--------|
| #1–#11, #13 | Closed |

Includes: HR, Dashboard, Production status, Laporan Keuangan, POS/CRM UX, Services CRUD, Performance menu, CI/CD, Procurement PR/PO/GRN + **Supplier** sub-page.

---

## Current phase — Security + Caching

**Branch:** `chore/security-and-caching`  
**Epic:** [#14](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/14)  
**Guide:** [docs/PHASE_SECURITY_CACHE.md](docs/PHASE_SECURITY_CACHE.md)

| Order | GH | Task |
|-------|-----|------|
| 1 | [#15](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/15) | Role/permission middleware modul sensitif |
| 2 | [#16](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/16) | Auth: register, API throttle, password reset |
| 3 | [#17](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/17) | Tenant isolation /track + BranchScoped |
| 4 | [#18](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/18) | Audit log mutasi bisnis |
| 5 | [#19](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/19) | Journal lockForUpdate + idempotency |
| 6 | [#20](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/20) | Docker/Nginx prod hygiene |
| 7 | [#21](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/21) | Caching layer + queue observers |

```bash
git fetch origin
git checkout chore/security-and-caching
```

Kerjakan **satu issue per PR** ke branch phase (atau commit atomic dengan `Refs #15`).

---

## Backlog (nanti)

- 2FA evaluation
- PII field encryption (NIK/phone)
- Pint style cleanup (`chore/pint-cleanup`)
- Expand REST API modules
