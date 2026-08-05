# QA Automation & AI Automated Testing Guide
# Istana Laundry Management System

> **Official Support:** +62 811-5599-199  
> **Testing Framework:** PHPUnit / Laravel Feature & Unit Tests  

---

## 🛠️ Ringkasan Automated Test Suite

Aplikasi dilengkapi dengan rangkaian automated test suite yang mencakup seluruh lapisan keamanan, autentikasi 2FA, RESTful API, dan alur operasional bisnis.

```bash
# Jalankan seluruh test suite
php artisan test
```

### Test Suite Execution Matrix

| Test Suite File | Modul Pengujian | Jumlah Test | Assertions | Status |
|-----------------|-----------------|-------------|------------|--------|
| `TwoFactorAuthenticationTest.php` | TOTP 2FA, Login Challenge, Trust Device 30 Hari, Recovery Code, API 2FA | 7 | 47 | ✅ PASS |
| `SecurityAndProfileTest.php` | Security Headers, WebP Avatar Compression ≤200KB, Password Update | 3 | 26 | ✅ PASS |
| `FullRestApiTest.php` | RESTful API Engine (12 Modul, Sanctum Auth, Branch Scope) | 13 | 100+ | ✅ PASS |
| `BackOfficeTest.php` | COA, Journal, Reports, Payroll, Depreciation | 10 | 80+ | ✅ PASS |
| `POSAndProductionTest.php` | POS Order, Production Status Tracking, Stock FIFO | 5 | 40+ | ✅ PASS |
| `WhatsAppServiceTest.php` | WhatsApp URL Generator & Message Formatting | 8 | 41 | ✅ PASS |

---

## 🏃 Commands Pengujian Spesifik

```bash
# Test 2FA & Perangkat Terpercaya
php artisan test --filter=TwoFactorAuthenticationTest

# Test Keamanan & Kompresi Foto Profil
php artisan test --filter=SecurityAndProfileTest

# Test Seluruh RESTful API
php artisan test --filter=FullRestApiTest

# Test Operasional Back-Office Keuangan
php artisan test --filter=BackOfficeTest

# Test Service WhatsApp
php artisan test --filter=WhatsAppServiceTest
```
