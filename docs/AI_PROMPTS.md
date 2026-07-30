# AI Prompts — Istana Laundry

> Updated: **2026-07-30**  
> Phase aktif: **TEST 2** (GH #29–#36)  
> Phase selesai: Security+Cache #14–#21 (lihat arsip prompt di bawah / git history)

Satu sesi AI = **satu issue**. Baca file nyata di repo. Minimal diff.

---

## 0) Bootstrap (tempel sekali di awal chat)

```
Kamu senior Laravel engineer.

## Project
- Istana Laundry Management System — semi-ERP laundry multi-cabang (Samarinda)
- Branch kerja: dari master, buat branch fix/* atau feat/* per issue
- Stack: Laravel 13, PHP 8.3+, Blade, Alpine, Tailwind, Chart.js, MySQL, Spatie Permission, Sanctum, Docker
- Multi-branch: BranchScoped + middleware branch.scope + session scoped_branch_id
- Production status: TERIMA → PILAH → CUCI → KERING → LIPAT → CEK → SIAP → DIAMBIL
- Security phase #15–#21 SUDAH SELESAI (RBAC, audit, journal idempotent, cache, queue jobs)
- Docs: tasks.md, docs/PHASE_TEST2.md, docs/SRS.md

## Aturan
1. Baca file terkait dulu. Jangan mengarang route/API.
2. Minimal diff — jangan refactor di luar scope issue.
3. Hormati role Spatie yang ada (Cashier, Workshop_Staff, Finance, Owner, …).
4. Jangan commit .env / secrets.
5. Setelah selesai: list file diubah + cara test + commit message dengan Refs #N.

Konfirmasi siap, tunggu prompt issue berikutnya.
```

---

## #31 — Payroll nominal 0 (P0)

```
Task GH #31 | Linear KIL-39 | Branch fix/payroll-zero-calc

## Goal
Generate payroll tidak boleh menghasilkan semua nominal 0.

## Investigate
1. HRController::storePayroll — copy base_salary employee ke PayrollItem?
2. PayrollItem::saveCalculations / calculateNetSalary — pro-rata absensi membuat net 0?
3. View payroll detail / payslip — field yang ditampilkan salah?
4. Data seed: employees punya base_salary > 0?

## Do
- Perbaiki kalkulasi agar net_salary & komponen mencerminkan base_salary (+ absensi/bonus/potongan yang ada)
- Jangan scope-creep ke #27 BPJS split kecuali diperlukan untuk non-zero baseline

## Acceptance
Generate payroll untuk cabang dengan karyawan aktif → minimal base_salary / net tampil non-zero yang masuk akal.
```

---

## #30 — Dashboard chart kosong setelah switch global (P0)

```
Task GH #30 | Linear KIL-38 | Branch fix/dashboard-chart-scope

## Goal
Owner: buka global → chart Komparasi Pendapatan Cabang terisi.
Switch ke cabang → chart trend OK.
Kembali ke global → chart komparasi TERISI LAGI tanpa hard refresh.

## Investigate
- DashboardController::ownerDashboard saat branchId null vs set
- Chart.js init di owner.blade.php (destroy/recreate, empty labels)
- Route switch-branch + full page reload vs partial

## Do
Pastikan chartLabels/chartValues terisi saat scope global; re-init chart setelah navigasi bila perlu.

## Acceptance
Tidak perlu Ctrl+F5 untuk melihat data komparasi setelah kembali ke global.
```

---

## #29 — Timezone WITA / GMT+8 (P0)

```
Task GH #29 | Linear KIL-37 | Branch fix/timezone-wita

## Goal
APP_TIMEZONE = Asia/Makassar (WITA, GMT+8).
Timestamp di UI, audit, payroll period, order times konsisten zona operasional Samarinda.

## Do
1. config/app.php + .env.example dokumentasikan Asia/Makassar
2. Cek display Carbon di Blade (hindari asumsi UTC mentah di UI)
3. Jangan ubah historical data mass-update kecuali diperlukan

## Acceptance
now() dan created_at yang di-format di UI sesuai WITA.
```

---

## #32 — Production search + role UI

```
Task GH #32 | Linear KIL-40 | Branch feat/production-order-search

## Goal
1. Search bar nomor order = kontrol utama di /production
2. Workshop_Staff / Workshop_Admin: default HIDE list; toggle untuk tampilkan
3. Owner/Admin/role lain: list tampil default

## Files
ProductionController, resources/views/production/index.blade.php

## Acceptance
Cari nota → order ketemu; staff landing page fokus search.
```

---

## #33 — CRM card stats + riwayat + WA

```
Task GH #33 | Linear KIL-41 | Branch feat/crm-customer-insights

## Goal
Pada card/list customer:
- Total transaksi (count orders)
- Last transaction (tanggal / no nota terakhir)
- Tombol buka riwayat transaksi customer
- Tombol WhatsApp (wa.me/<phone>) follow-up

Hormati branch scope. Phone format untuk wa.me (62…).
```

---

## #34 — Receipt hyperlink track

```
Task GH #34 | Linear KIL-42 | Branch feat/receipt-track-link

## Goal
Template WA/receipt: nomor order menjadi URL klik ke
url('/track') . '?order_number=' . $order->order_number
(atau route track yang ada).

Files: InvoiceController, view receipt/whatsapp message builder.
```

---

## #35 — Laporan Keuangan charts

```
Task GH #35 | Linear KIL-43 | Branch feat/finance-report-charts

## Goal
Tiap tab laporan (income / balance / trial balance) punya chart visual penyerta (Chart.js sudah di stack).
Jangan rusak angka tabel yang ada.
```

---

## #36 — Export CRM / Performance / Aset

```
Task GH #36 | Linear KIL-44 | Branch feat/export-modules

## Goal
Tombol export Excel dan/atau CSV (PDF opsional) di:
- Customers (CRM)
- Performance
- Assets

Reuse maatwebsite/excel + pola export yang sudah ada di project jika ada.
Hormati filter branch & role.
```

---

## Setelah tiap issue

```
1. File yang diubah
2. Cara test manual (role + skenario Notes #2)
3. Commit 1 baris + Refs #N
4. Close GH + Linear Done setelah merge ke master
```

---

## Arsip — Security phase prompts (#15–#21)

Phase selesai. Detail implementasi: `docs/PHASE_SECURITY_CACHE.md`.  
Prompt historis tersedia di git history commit docs sebelum 2026-07-30.
