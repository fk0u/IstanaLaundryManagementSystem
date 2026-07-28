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

## Status pengembangan (28 Jul 2026)

| Area | Status |
|------|--------|
| Modul operasional (POS, Production, CRM, Services, Performance) | ✅ Stabil (issue Tech Lead #1–#11 closed) |
| Pengadaan PR → PO → GRN + **Supplier** | ✅ Diperbaiki end-to-end |
| REST API (Sanctum: auth, track, production) | ✅ Foundation merged |
| CI/CD (tests + GHCR image + optional SSH deploy) | ✅ |
| **Security hardening + Caching** | 🔄 In progress — epic [#14](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/14) |

Detail task: [`tasks.md`](tasks.md) · Panduan fase: [`docs/PHASE_SECURITY_CACHE.md`](docs/PHASE_SECURITY_CACHE.md) · Prompt AI: [`docs/AI_PROMPTS.md`](docs/AI_PROMPTS.md)

---

## Modul utama

1. **Auth & RBAC** — Breeze + Spatie Permission (Developer, Owner, Super_Admin, Branch_Admin, Workshop, Cashier, Finance, CS, …)
2. **Multi-cabang** — `BranchScoped` + middleware `branch.scope` + switch cabang untuk super-role
3. **Master data** — Services (CRUD + harga cabang), COA, Supplier
4. **POS & Billing** — Kasir, promo, invoice/struk, refund flow
5. **Production** — Alur status linear: `TERIMA → PILAH → CUCI → KERING → LIPAT → CEK → SIAP → DIAMBIL` (+ filter Diambil)
6. **CRM & Loyalty** — Customer, tier, poin
7. **Inventory & Procurement** — FIFO stock, **PR → PO → GRN**, master **Supplier**
8. **Finance** — Auto-journal double-entry, periode, laporan
9. **HR & Payroll** — Karyawan, generate payroll, slip
10. **Fixed assets** — Aset + depresiasi
11. **Dashboard & Performance** — Metrik cabang / konsolidasi / Memantau Kinerja
12. **Public track** — Lacak nota tanpa login
13. **REST API** — `POST /api/login`, `GET /api/me`, `GET /api/track/{orderNumber}`, production endpoints (Sanctum)

---

## Tech stack

| Layer | Pilihan |
|-------|---------|
| Backend | Laravel 13, PHP 8.3+ (Docker image 8.4-FPM) |
| Auth API | Laravel Sanctum |
| RBAC | spatie/laravel-permission v8 |
| UI | Blade, Alpine.js, Tailwind CSS v4, Chart.js |
| DB | MySQL 8 (Docker / prod) · SQLite opsional lokal non-Docker |
| PDF / Excel / QR | dompdf, maatwebsite/excel, simple-qrcode |
| Backup | spatie/laravel-backup |
| Runtime | Docker Compose (`app`, `nginx`, `mysql`, `redis`, `node`) |
| CI/CD | GitHub Actions → test matrix + push `ghcr.io/fk0u/istanalaundrymanagementsystem` |

---

## Quick start (Docker — direkomendasikan)

```bash
git clone https://github.com/fk0u/IstanaLaundryManagementSystem.git
cd IstanaLaundryManagementSystem
cp .env.example .env          # pastikan DB_HOST=db untuk compose

docker compose up -d --build
```

Buka **http://localhost:8000**

Perintah berguna:

```bash
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan test
docker compose logs -f app
docker compose down
```

### Tanpa Docker

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
# set DB di .env (SQLite atau MySQL)
php artisan migrate --seed
npm run build
composer run dev            # serve + queue + vite + pail
```

---

## Branch strategy

| Branch | Fungsi |
|--------|--------|
| `master` | Stabil / production-ready merge |
| `chore/security-and-caching` | **Phase aktif:** security + cache/queue |
| `feat/*` | Fitur baru |
| `fix/*` | Bugfix terisolasi |

```bash
# mulai kerja fase security/cache
git fetch origin
git checkout chore/security-and-caching
git pull origin chore/security-and-caching
```

PR: issue branch → `chore/security-and-caching` → `master`.

---

## CI/CD

- **CI** (`.github/workflows/ci.yml`): PHP 8.3/8.4, MySQL service, Pint (non-blocking), `php artisan test`, smoke Docker build on PR
- **CD** (`.github/workflows/deploy.yml`): build & push GHCR; optional SSH deploy jika secret `DEPLOY_HOST` diisi
- **Prod compose**: `docker-compose.prod.yml` (image GHCR, bukan bind-mount source)

Image: `ghcr.io/fk0u/istanalaundrymanagementsystem:latest`

---

## API (ringkas)

| Method | Path | Auth |
|--------|------|------|
| POST | `/api/login` | Public |
| POST | `/api/logout` | Sanctum |
| GET | `/api/me` | Sanctum |
| GET | `/api/track/{orderNumber}` | Public |
| GET | `/api/production` | Sanctum |
| PATCH | `/api/production/{order}/status` | Sanctum |

---

## Dokumentasi internal

| File | Isi |
|------|-----|
| [`tasks.md`](tasks.md) | Backlog fase aktif + urutan kerja |
| [`docs/PHASE_SECURITY_CACHE.md`](docs/PHASE_SECURITY_CACHE.md) | Epic security/cache, issue map |
| [`docs/AI_PROMPTS.md`](docs/AI_PROMPTS.md) | Prompt bootstrap + per-issue untuk AI coding |

Issue tracker: [GitHub Issues](https://github.com/fk0u/IstanaLaundryManagementSystem/issues) · Linear project *Istana Laundry Management System*

---

## Keamanan (catatan operasional)

- Jangan commit `.env` / `APP_KEY` production
- Production: `APP_DEBUG=false`, `APP_ENV=production`
- Fase berjalan: role middleware ketat, tenant isolation, audit log bisnis, journal race-safe, cache/queue — lihat epic #14

---

## Lisensi

MIT — lihat [LICENSE](LICENSE).

---

**Istana Laundry Samarinda** · dibangun oleh **KOU / Alenkosa.id**
