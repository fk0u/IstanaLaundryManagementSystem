<p align="center">
  <img src="public/images/logo.webp" alt="Istana Laundry Logo" width="80" />
</p>

<h1 align="center">Istana Laundry Management System</h1>

<p align="center">
  <strong>Enterprise-Grade Multi-Branch Laundry ERP — Samarinda, Kalimantan Timur</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=flat-square&logo=laravel" alt="Laravel" />
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php" alt="PHP" />
  <img src="https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql" alt="MySQL" />
  <img src="https://img.shields.io/badge/Docker-Compose-2496ED?style=flat-square&logo=docker" alt="Docker" />
  <img src="https://img.shields.io/badge/Sanctum-API-38BDF8?style=flat-square&logo=laravel" alt="Sanctum" />
  <img src="https://img.shields.io/badge/TOTP%202FA-RFC6238-00C853?style=flat-square" alt="2FA" />
</p>

---

## 📋 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Fitur Utama](#-fitur-utama)
- [Arsitektur & Teknologi](#-arsitektur--teknologi)
- [Instalasi & Setup](#-instalasi--setup)
- [Konfigurasi Environment](#-konfigurasi-environment)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Sistem Keamanan](#-sistem-keamanan)
- [RESTful API](#-restful-api)
- [Testing](#-testing)
- [Deployment VPS](#-deployment-vps)
- [Dokumentasi Lengkap](#-dokumentasi-lengkap)
- [Struktur Proyek](#-struktur-proyek)
- [Lisensi](#-lisensi)

---

## 🏢 Tentang Proyek

**Istana Laundry Management System** adalah sistem ERP (*Enterprise Resource Planning*) berbasis web yang dibangun khusus untuk **Istana Premium Laundry Service** di Samarinda, Kalimantan Timur. Sistem ini mengelola **5 outlet cabang resmi** secara terpusat dengan fitur multi-branch, role-based access control (RBAC), dan keamanan tingkat enterprise.

### Cabang Resmi
| # | Cabang | Alamat |
|---|--------|--------|
| 1 | **Samarinda Utama** | Jl. KH. Wahid Hasyim 2 No.57 |
| 2 | **Samarinda Seberang** | Jl. Pelita |
| 3 | **Palaran** | Jl. Palaran |
| 4 | **Sungai Kunjang** | Jl. Kadrie Oening |
| 5 | **Loa Janan** | Jl. Cipto Mangunkusumo |

---

## ✨ Fitur Utama

### 🏪 Operasional Laundry
- **Point of Sale (POS)** — Kasir terintegrasi untuk transaksi cuci kiloan, satuan, express, dan dry clean
- **Production Tracking** — Pelacakan status produksi real-time (ANTRI → CUCI → SETRIKA → PACKING → SIAP DIAMBIL → DIAMBIL)
- **Shift Management** — Buka/tutup shift kasir dengan audit setoran kas
- **Order Online** — Order pickup via landing page publik dengan koordinat GPS

### 👥 CRM & Pelanggan
- **Customer Database** — Manajemen data pelanggan dengan riwayat transaksi
- **Loyalty Points** — Sistem poin loyalitas (1 poin per Rp10.000) dengan penukaran otomatis
- **WhatsApp Notifications** — Notifikasi status cucian via WhatsApp API

### 📊 Keuangan & Akuntansi
- **Chart of Accounts (COA)** — Bagan akun akuntansi standar Indonesia
- **Double-Entry Journal** — Pencatatan jurnal otomatis (akrual) untuk setiap transaksi
- **Laporan Keuangan** — Neraca, Laba/Rugi, Trial Balance
- **Beban Operasional** — Pencatatan kas kecil (petty cash) harian
- **Periode Akuntansi** — Manajemen buka/tutup periode bulanan

### 🏭 Supply Chain & Inventory
- **Inventory FIFO** — Manajemen stok bahan cuci (deterjen, pewangi, plastik) dengan metode FIFO
- **Low Stock Alert** — Notifikasi otomatis saat stok di bawah minimum
- **Procurement** — Purchase Request → Approval → Purchase Order → Goods Received Note (GRN)
- **Supplier Management** — Database supplier dengan pelacakan hutang dan pembayaran

### 👨‍💼 HR & Payroll
- **Employee Database** — Data karyawan dengan posisi, gaji pokok, dan status aktif
- **Payroll** — Penggajian bulanan dengan komponen gaji pokok, tunjangan, lembur, potongan, dan BPJS
- **Payroll Finalization** — Penguncian payroll setelah disetujui (tidak bisa diedit)

### 🏗️ Aset Tetap
- **Fixed Asset Registry** — Pencatatan aset tetap (mesin cuci, setrika uap, kendaraan operasional)
- **Depreciation** — Perhitungan penyusutan otomatis (Straight-Line Method)
- **Depreciation Schedule** — Jadwal penyusutan bulanan terintegrasi jurnal akuntansi

### 🛡️ Keamanan Enterprise
- **Two-Factor Authentication (2FA TOTP)** — Google Authenticator / Authy
- **Trust Device (30 Hari)** — Opsi percayai perangkat untuk melewati 2FA
- **Security Headers** — HSTS, CSP, X-Frame-Options, X-Content-Type-Options
- **Rate Limiting** — Anti-DDoS (30 req/min public, 120 req/min authenticated)
- **Role-Based Access Control (RBAC)** — 7 role: Developer, Owner, Super_Admin, Branch_Admin, Finance, Cashier, Workshop
- **Brute-Force Protection** — Lockout akun setelah 5 percobaan gagal (15 menit)
- **Audit Logging** — Riwayat lengkap setiap aktivitas login, logout, dan perubahan data

### 📸 Profil & Avatar
- **Upload Foto Profil** — Auto-convert ke WebP & kompresi dinamis ≤ 200KB
- **Avatar di Topbar & Sidebar** — Tampilan foto profil di seluruh antarmuka dashboard

---

## 🏗️ Arsitektur & Teknologi

| Komponen | Teknologi |
|----------|-----------|
| **Backend** | Laravel 13.x (PHP 8.4) |
| **Database** | MySQL 8.x |
| **Frontend** | Blade + Tailwind CSS 4 + Alpine.js |
| **API Auth** | Laravel Sanctum (Bearer Token) |
| **2FA** | Custom TOTP RFC 6238 (HMAC-SHA1) |
| **Containerization** | Docker Compose |
| **Web Server (Prod)** | Nginx + PHP-FPM |
| **Caching** | File-based (Laravel Cache) |
| **Image Processing** | PHP GD (WebP/JPEG fallback) |
| **API Docs** | Swagger UI (OpenAPI 3.0) |

---

## ⚡ Instalasi & Setup

### Prasyarat
- PHP 8.4+ dengan ekstensi: `gd`, `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.x (atau Docker)
- Docker & Docker Compose (opsional)

### 1. Clone Repository
```bash
git clone https://github.com/fk0u/IstanaLaundryManagementSystem.git
cd IstanaLaundryManagementSystem
```

### 2. Install Dependencies
```bash
composer install
npm install && npm run build
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Migration & Seeder
```bash
php artisan migrate --seed
php artisan storage:link
```

### 5. Jalankan (Development)
```bash
php artisan serve --port=8000
```

---

## 🐳 Menjalankan dengan Docker

```bash
# Build & start containers
docker compose up -d --build

# Run migrations inside container
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

Akses: **http://localhost:8000**

---

## 🔐 Sistem Keamanan

### Security Headers (Setiap Response)
| Header | Value |
|--------|-------|
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains; preload` |
| `X-Frame-Options` | `SAMEORIGIN` |
| `X-Content-Type-Options` | `nosniff` |
| `X-XSS-Protection` | `1; mode=block` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | `camera=(), microphone=(), geolocation=(self)` |

### Two-Factor Authentication (2FA TOTP)
1. Buka **Pengaturan Profil** → klik **"Setup 2FA Google Authenticator"**
2. Scan QR Code menggunakan Google Authenticator / Authy
3. Masukkan 6 digit OTP → Terima **8 Recovery Codes** darurat
4. Setiap login berikutnya wajib memasukkan kode 2FA
5. Centang **"Percayai perangkat ini selama 30 hari"** untuk bypass 2FA di browser ini

### Rate Limiting
| Scope | Limit |
|-------|-------|
| Public API | 30 requests / menit / IP |
| Authenticated API | 120 requests / menit / Token |
| 2FA Challenge | 6 percobaan / menit |
| Login | 10 percobaan / menit / IP |

---

## 🔌 RESTful API

Seluruh backend aplikasi diakses melalui RESTful API v1 yang lengkap.

**Base URL:** `https://istanasystem.alk-tech.my.id/api` atau `http://localhost:8000/api`

### Autentikasi
```bash
# Login & dapatkan Bearer Token
curl -X POST /api/login \
  -d '{"email":"admin@istanalaundry.com", "password":"password"}'

# Gunakan token di setiap request
curl -H "Authorization: Bearer {token}" /api/v1/dashboard/stats
```

### Endpoint Tersedia (13 Modul)
| # | Modul | Endpoints |
|---|-------|-----------|
| 0 | Profile & 2FA | `GET/PUT /v1/profile`, `POST /v1/profile/avatar`, `POST /v1/profile/2fa/*` |
| 1 | Dashboard | `GET /v1/dashboard/stats`, `GET /v1/dashboard/charts` |
| 2 | Orders | `GET /v1/orders`, `GET /v1/orders/{id}`, `POST /v1/orders/{id}/payments`, `POST /v1/orders/{id}/refund` |
| 3 | CRM Customers | `GET/POST /v1/customers`, `PUT/DELETE /v1/customers/{id}`, `POST /v1/customers/{id}/adjust-points` |
| 4 | Inventory | `GET/POST /v1/inventory`, `PUT/DELETE /v1/inventory/{id}`, `PUT /v1/inventory/{id}/adjust` |
| 5 | HR & Payroll | `GET/POST /v1/hr/employees`, `GET/POST /v1/hr/payrolls` |
| 6 | Fixed Assets | `GET/POST /v1/assets`, `PUT /v1/assets/{id}`, `POST /v1/assets/depreciate` |
| 7 | Finance & COA | `GET/POST /v1/finance/coa`, `GET/POST /v1/finance/journals`, `GET/POST /v1/finance/expenses` |
| 8 | Procurement | `GET/POST /v1/procurement/suppliers`, `GET/POST /v1/procurement/purchase-requests`, `PUT approve` |
| 9 | Shifts | `GET /v1/shifts`, `POST /v1/shifts/open`, `POST /v1/shifts/close` |
| 10 | Master Data | `CRUD /v1/master/services`, `CRUD /v1/master/branches` |
| 11 | Users & RBAC | `GET/POST /v1/users`, `GET /v1/roles` |
| 12 | Performance | `GET /v1/performance/cashiers`, `GET /v1/performance/branches`, `GET /v1/audit-logs` |

📖 **Swagger UI:** [/api/documentation](https://istanasystem.alk-tech.my.id/api/documentation)

---

## 🧪 Testing

```bash
# Jalankan semua test
php artisan test

# Test spesifik
php artisan test --filter=TwoFactorAuthenticationTest
php artisan test --filter=SecurityAndProfileTest
php artisan test --filter=FullRestApiTest
php artisan test --filter=BackOfficeTest
```

### Test Coverage
| Test Suite | Tests | Assertions | Status |
|------------|-------|------------|--------|
| `TwoFactorAuthenticationTest` | 7 | 47 | ✅ PASS |
| `SecurityAndProfileTest` | 3 | 26 | ✅ PASS |
| `FullRestApiTest` | 13+ | 100+ | ✅ PASS |
| `BackOfficeTest` | 10+ | 80+ | ✅ PASS |
| `POSAndProductionTest` | 5+ | 40+ | ✅ PASS |

---

## 🚀 Deployment VPS

Aplikasi telah di-deploy ke VPS Production:

| Item | Detail |
|------|--------|
| **URL** | https://istanasystem.alk-tech.my.id |
| **Web Server** | Nginx + PHP-FPM 8.4 |
| **Database** | MySQL 8.x |
| **SSL** | Let's Encrypt (auto-renew) |
| **Auto-Deploy** | Git post-commit hook (auto pull) |
| **Timezone** | Asia/Singapore (WITA UTC+8) |

---

## 📚 Dokumentasi Lengkap

| Dokumen | Path |
|---------|------|
| API Reference | [`docs/api/API_DOCUMENTATION.md`](docs/api/API_DOCUMENTATION.md) |
| Swagger UI | [`/api/documentation`](https://istanasystem.alk-tech.my.id/api/documentation) |
| Postman Collection | [`docs/api/IstanaLaundry_Postman_Collection.json`](docs/api/IstanaLaundry_Postman_Collection.json) |
| Bruno Collection | [`docs/api/bruno/`](docs/api/bruno/) |
| System Overview | [`docs/SYSTEM_OVERVIEW.md`](docs/SYSTEM_OVERVIEW.md) |
| Architecture & Security | [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) |
| Database Schema | [`docs/DATABASE_SCHEMA.md`](docs/DATABASE_SCHEMA.md) |
| Product Roadmap | [`docs/PRODUCT_ROADMAP.md`](docs/PRODUCT_ROADMAP.md) |
| SRS | [`docs/SRS.md`](docs/SRS.md) |

---

## 📁 Struktur Proyek

```
IstanaLaundryManagementSystem/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/              # 16 RESTful API Controllers
│   │   ├── Auth/             # Login, 2FA Challenge, Password
│   │   ├── Finance/          # COA, Journal, Reports
│   │   ├── HR/               # Employee, Payroll
│   │   ├── Procurement/      # PR, PO, GRN
│   │   └── ...               # Dashboard, POS, Production, etc.
│   ├── Models/               # 25+ Eloquent Models
│   ├── Services/             # Business Logic Layer
│   │   ├── CRM/              # LoyaltyService
│   │   ├── Finance/          # JournalService (Double-Entry)
│   │   └── Security/         # TwoFactorService (TOTP RFC 6238)
│   └── Http/Middleware/      # Security, Performance, Gzip, Branch Scope
├── database/
│   ├── migrations/           # 30+ migration files
│   └── seeders/              # ERPDataSeeder (5000+ demo records)
├── resources/views/          # 80+ Blade templates
├── routes/
│   ├── web.php               # Web routes (50+ routes)
│   ├── api.php               # API routes (80+ endpoints)
│   └── auth.php              # Auth routes (login, 2FA, password)
├── tests/Feature/            # Automated test suites
├── docs/                     # Documentation
└── docker-compose.yml        # Docker configuration
```

---

## 📄 Lisensi

Hak Cipta © 2026 **Istana Premium Laundry Service** — Samarinda, Kalimantan Timur.

Sistem ini dikembangkan secara eksklusif untuk penggunaan internal Istana Laundry. Seluruh hak dilindungi undang-undang.

---

<p align="center">
  <strong>Dikembangkan dengan ❤️ untuk Istana Laundry Samarinda</strong>
</p>
