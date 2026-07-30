# Panduan Otomatisasi Pengujian & Rekomendasi Tools AI QA (Istana Laundry ERP)

> **Dokumen:** AI-Assisted QA Automation & Testing Strategy  
> **Versi:** 1.0 · **Dipublikasikan:** 30 Juli 2026  
> **Target System:** Istana Laundry Management System (Laravel 13, Docker, Sanctum)  
> **Penulis:** Quality Assurance & AI Architecture Team

---

## 📋 Daftar Isi
1. [Visi & Strategi QA Berbasis AI](#1-visi--strategi-qa-berbasis-ai)
2. [Rekomendasi Ecosystem Tools AI QA Testing](#2-rekomendasi-ecosystem-tools-ai-qa-testing)
   - [2.1 AI E2E Testing & Natural Language Automation (ZeroStep + Playwright)](#21-ai-e2e-testing--natural-language-automation-zerostep--playwright)
   - [2.2 AI Visual Regression Testing (Applitools Eyes / VisualAI)](#22-ai-visual-regression-testing-applitools-eyes--visualai)
   - [2.3 Autonomous Agent Exploratory Testing (Octomind AI)](#23-autonomous-agent-exploratory-testing-octomind-ai)
   - [2.4 Self-Healing Test Locators (Healenium / Playwright AI)](#24-self-healing-test-locators-healenium--playwright-ai)
   - [2.5 AI REST API Contract & Fuzzing (Postman Postbot & Schemathesis)](#25-ai-rest-api-contract--fuzzing-postman-postbot--schemathesis)
3. [Arsitektur Implementasi Script AI QA](#3-arsitektur-implementasi-script-ai-qa)
4. [Contoh Playwright + AI Test Script (POS & Billing Flow)](#4-contoh-playwright--ai-test-script-pos--billing-flow)
5. [Contoh Visual AI Test Script (Dashboard & Reports)](#5-contoh-visual-ai-test-script-dashboard--reports)
6. [Integrasi CI/CD Pipeline (GitHub Actions)](#6-integrasi-cicd-pipeline-github-actions)

---

## 1. Visi & Strategi QA Berbasis AI

Seiring dengan pesatnya perkembangan fitur Istana Laundry ERP (POS, Produksi 8-stasiun, Pengadaan, Akuntansi, Payroll, dan Aset), pengujian manual berulang (*regression testing*) berisiko memakan waktu dan melelahkan.

Strategi **AI-Powered QA Testing** bertujuan untuk:
- **Zero-Maintenance Locators**: Menghindari kegagalan test akibat perubahan selector CSS/ID di Blade templates.
- **Natural Language Test Cases**: Menulis pengujian dalam bahasa instruksi manusia (contoh: *"Ketik promo PROMO50 dan pastikan diskon terpotong"*).
- **Autonomous Bug Discovery**: AI agent yang secara mandiri menjelajahi alur bisnis, menemukan *edge cases*, dan melaporkan kecacatan UI.
- **Visual AI Inspection**: Memastikan grafik Chart.js, layout Tailwind, dan komponen Blade tampil presisi di layar desktop maupun seluler.

---

## 2. Rekomendasi Ecosystem Tools AI QA Testing

### 2.1 AI E2E Testing & Natural Language Automation (ZeroStep + Playwright)
- **Teknologi**: **ZeroStep AI** (`@zerostep/playwright`) + **Playwright Test**.
- **Keunggulan**: Memungkinkan penulisan pengujian Playwright menggunakan fungsi `ai()`. AI membaca DOM secara dinamis dan mengeksekusi instruksi tanpa memerlukan hardcoded CSS selector (`#btn-submit`).
- **Penggunaan di Istana Laundry**: Pengujian checkout POS, pengajuan refund 4-tahap, dan penginputan payroll karyawan.

### 2.2 AI Visual Regression Testing (Applitools Eyes / VisualAI)
- **Teknologi**: **Applitools Eyes VisualAI** / **Percy by BrowserStack**.
- **Keunggulan**: Menggunakan Computer Vision berbasis AI untuk membandingkan screenshot UI sebelum dan sesudah deployment. Berbeda dari pixel-matching tradisional, VisualAI mengabaikan perbedaan piksel rendering minor dan hanya mendeteksi perubahan visual yang berdampak pada pengguna.
- **Penggunaan di Istana Laundry**: Memeriksa grafik Chart.js pada Executive Dashboard & Laporan Keuangan, serta cetak slip gaji & struk thermal.

### 2.3 Autonomous Agent Exploratory Testing (Octomind AI)
- **Teknologi**: **Octomind.ai** / **Bugbug.io AI**.
- **Keunggulan**: AI Bot yang melakukan crawling otomatis pada aplikasi web, memetakan seluruh rute (`/pos`, `/production`, `/finance`, `/hr`), dan menghasilkan script Playwright secara otomatis jika menemukan error 404/500 atau form crash.
- **Penggunaan di Istana Laundry**: Pengujian stres pada alur multi-cabang dan permission role.

### 2.4 Self-Healing Test Locators (Healenium / Playwright AI)
- **Teknologi**: **Healenium** (Plugin Selenium/Playwright) atau **Relicx AI**.
- **Keunggulan**: Apabila struktur HTML Blade berubah (misal class Tailwind berganti), Healenium menganalisis tree DOM dengan algoritma ML dan memperbaiki selector yang patah secara real-time tanpa menggagalkan build CI/CD.

### 2.5 AI REST API Contract & Fuzzing (Postman Postbot & Schemathesis)
- **Teknologi**: **Schemathesis AI** + **Postman Postbot**.
- **Keunggulan**: Membaca spesifikasi OpenAPI/Sanctum REST API (`/api/production`, `/api/track`) dan secara otomatis menghasilkan ratusan skenario request acak (fuzzy testing) untuk menguji ketahanan server terhadap SQL Injection, Null Pointer, dan Unauthorized Bypass.

---

## 3. Arsitektur Implementasi Script AI QA

```text
┌─────────────────────────────────────────────────────────────────────────┐
│                      GitHub Actions CI/CD Pipeline                      │
├─────────────────────────────────────────────────────────────────────────┤
│ 1. Docker Compose Up → PHP 8.4-FPM + Nginx + MySQL 8 + App               │
├─────────────────────────────────────────────────────────────────────────┤
│ 2. Playwright + ZeroStep AI ─── (Natural Language Prompts) ──────────┐  │
│ 3. Applitools VisualAI ─────── (UI Visual Comparison) ───────────────┼──┤
│ 4. Schemathesis API Fuzzer ─── (Sanctum API Endpoint Validation) ────┘  │
├─────────────────────────────────────────────────────────────────────────┤
│                      Hasil Testing & Laporan AI QA                      │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 4. Contoh Playwright + AI Test Script (POS & Billing Flow)

Berkas: `tests/e2e/pos-billing.spec.ts`

```typescript
import { test, expect } from '@playwright/test';
import { ai } from '@zerostep/playwright';

test.describe('AI Automated POS Checkout & Promo Test', () => {
  test('Cashier can apply manual promo code and process payment', async ({ page }) => {
    // 1. Navigasi ke Halaman Login
    await page.goto('http://localhost:8000/login');
    
    // 2. Gunakan AI Prompt untuk Login
    await ai('Type "cashier.smd01@istanalaundry.com" into the email field', { page });
    await ai('Type "password" into the password field', { page });
    await ai('Click the login button', { page });

    // 3. Buka Halaman POS
    await page.goto('http://localhost:8000/pos');

    // 4. Instruksi AI Natural Language
    await ai('Select customer "Budi Santoso" from the dropdown or search list', { page });
    await ai('Add item "Cuci Komplit Reguler" with quantity 5 to cart', { page });
    await ai('Enter promo code "PROMO50" in the coupon code box and click Apply', { page });

    // 5. Verifikasi Diskon Terpotong
    const isPromoApplied = await ai('Confirm green success alert "Kupon PROMO50 berhasil diterapkan" is visible', { page });
    expect(isPromoApplied).toBeTruthy();

    // 6. Selesaikan Pembayaran
    await ai('Enter 50000 in the cash payment input', { page });
    await ai('Click the "Proses Bayar & Cetak Nota" button', { page });

    // 7. Verifikasi Struk Muncul
    await ai('Confirm invoice receipt modal or window is displayed', { page });
  });
});
```

---

## 5. Contoh Visual AI Test Script (Dashboard & Reports)

Berkas: `tests/visual/dashboard-visual.spec.ts`

```typescript
import { test } from '@playwright/test';
import { Eyes, Target } from '@applitools/eyes-playwright';

test.describe('Visual AI Layout & Chart Verification', () => {
  let eyes: Eyes;

  test.beforeAll(() => {
    eyes = new Eyes();
    eyes.setApiKey(process.env.APPLITOOLS_API_KEY || 'YOUR_API_KEY');
  });

  test('Executive Dashboard Charts Visual Check', async ({ page }) => {
    await page.goto('http://localhost:8000/login');
    await page.fill('#email', 'owner@istanalaundry.com');
    await page.fill('#password', 'password');
    await page.click('button[type="submit"]');

    await page.goto('http://localhost:8000/dashboard');

    // Inisialisasi Applitools Eyes Visual Check
    await eyes.open(page, 'Istana Laundry ERP', 'Executive Dashboard Layout');
    
    // Periksa visual seluruh halaman beserta Chart.js
    await eyes.check('Dashboard Full View', Target.window().fully());
    
    await eyes.close();
  });
});
```

---

## 6. Integrasi CI/CD Pipeline (GitHub Actions)

Tambahkan langkah AI QA Testing pada `.github/workflows/ci.yml`:

```yaml
name: Istana Laundry AI QA Pipeline

on:
  push:
    branches: [ master ]
  pull_request:
    branches: [ master ]

jobs:
  ai-qa-testing:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v2

      - name: Start Environment in Docker
        run: |
          cp .env.example .env
          docker compose up -d --build
          docker compose exec -T app php artisan migrate --seed

      - name: Install Node & Playwright
        run: |
          npm install -g @playwright/test @zerostep/playwright
          npx playwright install --with-deps

      - name: Run AI Natural Language E2E Tests
        env:
          ZEROSTEP_TOKEN: ${{ secrets.ZEROSTEP_TOKEN }}
        run: npx playwright test tests/e2e/

      - name: Run Schemathesis API Fuzzing
        run: |
          pip install schemathesis
          schemathesis run http://localhost:8000/api/documentation
```

---

> **Kesimpulan**: Dengan mengadopsi kombinasi **ZeroStep AI**, **Applitools VisualAI**, dan **Schemathesis**, pengujian Istana Laundry ERP menjadi jauh lebih cepat, resilien terhadap perubahan UI Blade, dan menjamin kualitas enterprise secara otomatis di CI/CD.
