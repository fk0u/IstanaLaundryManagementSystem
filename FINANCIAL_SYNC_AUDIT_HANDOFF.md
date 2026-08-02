# 📊 Document Synchronisasi & Audit Keuangan Real-Time (ERP Financial Handoff)

> **Catatan Penting untuk Agent / Developer Selanjutnya**: Document ini dibuat untuk memastikan konteks arsitektur keuangan, alur transaksi *double-entry*, sinkronisasi *General Ledger (Buku Besar)*, dan perintah CLI sinkronisasi tersimpan secara lengkap tanpa kehilangan konteks.

---

## 🏛️ 1. Arsitektur & Prinsip Dasar Sistem Keuangan

Aplikasi **Istana Laundry Management System** menerapkan sistem akuntansi *Double-Entry (Jurnal Berimbang)* yang terintegrasi secara otomatis dengan modul operasional bisnis.

### 🔑 Aturan Utama Keuangan:
1. **Idempotensi & Anti-Duplikasi**: Setiap transaksi operasional (`source_type` & `source_id`) hanya boleh menerbitkan 1 record `Journal` resmi.
2. **Keseimbangan Jurnal (Balanced Entries)**: Setiap panggilan `autoPostJournal()` memvalidasi bahwa `Total Debit == Total Kredit`.
3. **Multi-Branch & Periode Akuntansi**: Setiap jurnal terikat pada `branch_id` dan `accounting_period_id` (bulan/tahun) yang berstatus `open`. Jika belum ada, sistem akan membuat periode akuntansi terbuka secara otomatis untuk cabang terkait.
4. **Bypass Scope untuk Backfill**: Command backfill menggunakan `withoutBranchScope()` atau `withoutGlobalScopes()` agar dapat menyinkronkan data lintas 4 cabang tanpa terhalang middleware session.

---

## 🔄 2. Peta Sinkronisasi Real-Time Modul Keuangan (Current State)

| Modul Transaksi | Trigger Event | Service Method | Debit Account | Credit Account | Status Sync |
| :--- | :--- | :--- | :--- | :--- | :---: |
| **POS Laundry Order** | Checkout Order / Pembayaran | `JournalService::postOrderJournal($order)` | `1-1101` Kas Kecil / `1-1201` Piutang Usaha & `5-4105` Diskon | `4-1001` Pendapatan Laundry & `2-2101` Hutang PPN | ✅ Active & Tested |
| **Payroll HR** | Finalisasi Payroll (`finalizePayroll`) | `JournalService::postPayrollJournal($payroll)` | `5-3101` Beban Gaji & Upah (Gross Earnings) | `1-1101` Kas Kecil (THP) & `2-1201` Hutang Gaji/BPJS (Deductions) | ✅ Active & Tested |
| **Pembelian Bahan (GRN)** | Konfirmasi Penerimaan Barang | `JournalService::postGRNJournal($grn)` | `1-1301` Persediaan Bahan Habis Pakai | `2-1101` Hutang Usaha | ✅ Active & Tested |
| **Penyusutan Aset Tetap** | Schedule Eksekusi Depresiasi | `JournalService::postDepreciationJournal($schedule)` | `5-4101` Beban Penyusutan Aset | `1-290X` Akumulasi Penyusutan Aset | ✅ Active & Tested |
| **Refund / Pembatalan** | Approval Final Stage Refund | `JournalService::reverseJournal($journal)` | Reversal Debit/Credit Jurnal Asli | Reversal Debit/Credit Jurnal Asli | ✅ Active & Tested |
| **Beban Operasional (OpEx)** | Input Pengeluaran Harian | `JournalService::postOperationalExpenseJournal($expense)` | `5-2XXX` Beban Operasional (Listrik/Air/Gas/Sewa) | `1-1101` Kas Kecil / `1-1102` Bank | ✅ Active |
| **Pelunasan Hutang Supplier (AP)** | Input Pembayaran Invoice | `JournalService::postSupplierPaymentJournal($payment)` | `2-1101` Hutang Usaha | `1-1101` Kas Kecil / `1-1102` Bank | ✅ Active |

---

## 💻 3. Command CLI & Backfill Tooling

Untuk menyinkronkan data historis atau data dummy seeder yang belum memiliki jurnal keuangan, tersedia command CLI berikut:

### A. Backfill Jurnal Order POS
```bash
php artisan finance:backfill-order-journals
# Opsi dry-run untuk simulasi:
php artisan finance:backfill-order-journals --dry-run
```

### B. Backfill Jurnal Payroll HR
```bash
php artisan finance:backfill-payroll-journals
# Opsi dry-run untuk simulasi:
php artisan finance:backfill-payroll-journals --dry-run
```

### C. Backfill Jurnal Penyusutan Aset
```bash
php artisan finance:backfill-depreciation-journals
# Opsi dry-run untuk simulasi:
php artisan finance:backfill-depreciation-journals --dry-run
# Filter per periode bulan:
php artisan finance:backfill-depreciation-journals --month=2026-08
```

### D. Master Sync-All (Menjalankan Semua Backfill Sekaligus)
```bash
php artisan finance:sync-all
# Opsi dry-run untuk simulasi semua:
php artisan finance:sync-all --dry-run
```

---

## 🚀 4. Area Audit & Pengembangan Lanjutan untuk Agent Selanjutnya

Jika pengguna meminta pengembangan modul keuangan lebih mendalam, berikut adalah daftar rekomendasi *roadmap* teknis yang dapat dilanjutkan:

1. **Konsolidasi Laporan Laba Rugi (Profit & Loss) Multi-Cabang**:
   - Menampilkan perbandingan performa finansial antar cabang (Wijaya Kusuma, Dr. Sutomo, Pangeran Hidayatullah, Lambung Mangkurat) secara konsolidasi maupun per cabang.
2. **Laporan Arus Kas (Cash Flow Statement)**:
   - Menyusun laporan arus kas operasional, investasi, dan pendanaan berdasarkan jurnal yang sudah tercatat.
3. **Integrasi Notifikasi Keuangan**:
   - Push notification atau WhatsApp alert saat ada transaksi besar, periode hampir ditutup, atau anomali keuangan terdeteksi.

---

## 📂 5. Peta Berkas Kunci (Key Files Map)

### Core Services
- **[JournalService.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Services/Finance/JournalService.php)**: Central engine pembuatan dan validasi jurnal akuntansi (Order, Payroll, GRN, Depreciation, OpEx, AP Settlement).
- **[FinancialReportService.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Services/FinancialReportService.php)**: Service untuk laporan neraca, laba rugi, trial balance, dan KPI analytics.

### Controllers
- **[HRController.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Http/Controllers/HR/HRController.php)**: Controller HR & Payroll (termasuk finalisasi & manual sync jurnal).
- **[JournalController.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Http/Controllers/Finance/JournalController.php)**: Controller manajemen Jurnal Umum Keuangan.
- **[OperationalExpenseController.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Http/Controllers/Finance/OperationalExpenseController.php)**: Controller pencatatan beban operasional harian (Kas Kecil).
- **[SupplierPaymentController.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Http/Controllers/Finance/SupplierPaymentController.php)**: Controller pelunasan hutang usaha ke supplier.
- **[AssetController.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Http/Controllers/AssetController.php)**: Controller aset tetap dan jadwal depresiasi.

### Queued Jobs (Auto-Sync Async)
- **[PostOrderJournalJob.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Jobs/PostOrderJournalJob.php)**: Job async posting jurnal order POS.
- **[PostGrnJournalJob.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Jobs/PostGrnJournalJob.php)**: Job async posting jurnal GRN & update stok.
- **[PostDepreciationJournalJob.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Jobs/PostDepreciationJournalJob.php)**: Job async posting jurnal penyusutan aset.

### Model Observers (Real-Time Trigger)
- **[OrderObserver.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Observers/OrderObserver.php)**: Trigger jurnal saat order dibuat/dibayar.
- **[GRNObserver.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Observers/GRNObserver.php)**: Trigger jurnal saat GRN dikonfirmasi.

### CLI Commands
- **[BackfillOrderJournals.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Console/Commands/BackfillOrderJournals.php)**: Backfill jurnal order historis.
- **[BackfillPayrollJournals.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Console/Commands/BackfillPayrollJournals.php)**: Backfill jurnal payroll historis.
- **[BackfillDepreciationJournals.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Console/Commands/BackfillDepreciationJournals.php)**: Backfill jurnal depresiasi historis.
- **[SyncAllJournals.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Console/Commands/SyncAllJournals.php)**: Master command sync semua modul sekaligus.

### Tests
- **[PayrollFinancialSyncTest.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/tests/Feature/PayrollFinancialSyncTest.php)**: Automated test suite sinkronisasi payroll ke buku besar.

### Models
- **[OperationalExpense.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Models/OperationalExpense.php)**: Model pencatatan beban operasional harian.
- **[SupplierPayment.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Models/SupplierPayment.php)**: Model pelunasan hutang usaha supplier.
