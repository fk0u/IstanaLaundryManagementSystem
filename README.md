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
| **Active phase** | **TEST 2** — UAT fixes [#29](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/29)–[#36](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/36) |

---

## Dokumentasi produk

| Dokumen | Untuk siapa | Isi |
|---------|-------------|-----|
| **[docs/SRS.md](docs/SRS.md)** | Stakeholder | Requirement AS-IS / TO-BE |
| **[docs/PRODUCT_ROADMAP.md](docs/PRODUCT_ROADMAP.md)** | Owner / PM | Visi, maturity, gelombang |
| **[docs/SYSTEM_OVERVIEW.md](docs/SYSTEM_OVERVIEW.md)** | Engineer | Arsitektur & peta modul |
| **[docs/PHASE_TEST2.md](docs/PHASE_TEST2.md)** | Engineer | **Fase aktif** UAT Notes #2 |
| [docs/PHASE_SECURITY_CACHE.md](docs/PHASE_SECURITY_CACHE.md) | Engineer | Arsip security + cache ✅ |
| [docs/AI_PROMPTS.md](docs/AI_PROMPTS.md) | AI-assisted dev | Prompt per issue |
| [tasks.md](tasks.md) | Dev harian | Backlog urutan kerja |

---

## Status pengembangan (30 Jul 2026)

| Area | Status |
|------|--------|
| Modul operasional + procurement + API foundation | ✅ |
| CI/CD (tests + GHCR) | ✅ |
| **Security hardening + Caching** (#14–#21) | ✅ Selesai |
| Product enhancements (#22–#28) | ✅ Closed di tracker |
| **TEST 2 UAT polish** (#29–#36) | 🔄 Aktif |

---

## Modul utama

1. Auth & RBAC · 2. Multi-cabang · 3. Services/COA/Supplier · 4. POS & Billing  
5. Production · 6. CRM & Loyalty · 7. Inventory & PR→PO→GRN · 8. Finance  
9. HR & Payroll · 10. Fixed assets · 11. Dashboard & Performance · 12. Public track · 13. REST API

---

## Quick start (Docker)

```bash
git clone https://github.com/fk0u/IstanaLaundryManagementSystem.git
cd IstanaLaundryManagementSystem
cp .env.example .env
docker compose up -d --build
# http://localhost:8000
docker compose exec app php artisan migrate --seed
# Queue (wajib untuk journal/GRN async):
docker compose exec app php artisan queue:work --tries=3
```

---

## Branch strategy

| Branch | Fungsi |
|--------|--------|
| `master` | Merge utama |
| `fix/*` | Bug TEST 2 (#29–#31) |
| `feat/*` | Improvement/export (#32–#36) |

---

## Keamanan

- Jangan commit `.env` · Production: `APP_DEBUG=false`
- RBAC, audit, journal idempotent, tenant isolation sudah di Gelombang A

---

## Lisensi

MIT — [LICENSE](LICENSE)

**Istana Laundry Samarinda** · **KOU / Alenkosa.id**
