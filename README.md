# Istana Laundry Management System

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-Compose-2496ED?logo=docker&logoColor=white)](https://docs.docker.com/compose/)
[![Sanctum](https://img.shields.io/badge/API-Sanctum-black)](https://laravel.com/docs/sanctum)
[![CI](https://img.shields.io/badge/CI%2FCD-GitHub%20Actions-2088FF?logo=github-actions&logoColor=white)](https://github.com/fk0u/IstanaLaundryManagementSystem/actions)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

**Semi-ERP multi-cabang** untuk operasional laundry: POS, production tracking, procurement, inventory, finance (double-entry), HR/payroll, CRM/loyalty, dan REST API.

| | |
|---|---|
| **Client** | Istana Laundry Samarinda |
| **Developer** | KOU / Alenkosa.id |
| **Repo** | https://github.com/fk0u/IstanaLaundryManagementSystem |
| **Default branch** | `master` |
| **Active phase branch** | [`chore/security-and-caching`](https://github.com/fk0u/IstanaLaundryManagementSystem/tree/chore/security-and-caching) |

---

## Dokumentasi produk (baca dulu)

| Dokumen | Untuk siapa | Isi |
|---------|-------------|-----|
| **[docs/SRS.md](docs/SRS.md)** | Semua stakeholder | **SRS v2** — requirement AS-IS vs TO-BE, NFR, kriteria rilis bisnis |
| **[docs/PRODUCT_ROADMAP.md](docs/PRODUCT_ROADMAP.md)** | Owner / PM | Visi, maturity L1–L4, gelombang A–D, KPI |
| **[docs/SYSTEM_OVERVIEW.md](docs/SYSTEM_OVERVIEW.md)** | Engineer | Arsitektur, peta modul↔route, runtime |
| [docs/PHASE_SECURITY_CACHE.md](docs/PHASE_SECURITY_CACHE.md) | Engineer | Epic security + cache |
| [docs/AI_PROMPTS.md](docs/AI_PROMPTS.md) | AI-assisted dev | Prompt per issue |
| [tasks.md](tasks.md) | Dev harian | Backlog urutan kerja |

---

## Status pengembangan (28 Jul 2026)

| Area | Status |
|------|--------|
| Modul operasional (POS, Production, CRM, Services, Performance) | ✅ Stabil (issue Tech Lead #1–#11 closed) |
| Pengadaan PR → PO → GRN + **Supplier** | ✅ Diperbaiki end-to-end |
| REST API (Sanctum: auth, track, production) | ✅ Foundation merged |
| CI/CD (tests + GHCR image + optional SSH deploy) | ✅ |
| **Security hardening + Caching** | 🔄 Epic [#14](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/14) |
| **Fitur bisnis lanjutan** (promo, POS UX, payroll BPJS, aset, dashboard, kinerja) | 📋 [#22](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/22)–[#28](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/28) |

---

## Modul utama

1. **Auth & RBAC** — Breeze + Spatie Permission
2. **Multi-cabang** — `BranchScoped` + `branch.scope` + switch cabang
3. **Master data** — Services, COA, Supplier
4. **POS & Billing** — Kasir, promo, invoice/struk, refund
5. **Production** — Status linear + filter + pagination 15/page
6. **CRM & Loyalty** — Customer, tier, poin
7. **Inventory & Procurement** — PR → PO → GRN + Supplier
8. **Finance** — Double-entry, periode, laporan
9. **HR & Payroll** — Karyawan, payroll, slip
10. **Fixed assets** — Aset + depresiasi
11. **Dashboard & Performance** — Metrik & Memantau Kinerja
12. **Public track** — Lacak nota
13. **REST API** — Sanctum auth, track, production

---

## Tech stack

Laravel 13 · PHP 8.3+ · Sanctum · Spatie Permission · Blade/Alpine/Tailwind · MySQL 8 · Docker · GitHub Actions → GHCR

---

## Quick start (Docker)

```bash
git clone https://github.com/fk0u/IstanaLaundryManagementSystem.git
cd IstanaLaundryManagementSystem
cp .env.example .env
docker compose up -d --build
# http://localhost:8000
docker compose exec app php artisan migrate --seed
```

---

## Branch strategy

| Branch | Fungsi |
|--------|--------|
| `master` | Merge utama |
| `chore/security-and-caching` | Security + cache |
| `feat/*` | Fitur bisnis |
| `fix/*` | Bugfix |

---

## Keamanan

Jangan commit `.env`. Production: `APP_DEBUG=false`. Rencana: epic #14.

---

## Lisensi

MIT — [LICENSE](LICENSE)

**Istana Laundry Samarinda** · **KOU / Alenkosa.id**
