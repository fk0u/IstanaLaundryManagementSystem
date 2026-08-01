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

---

## 💻 3. Command CLI & Backfill Tooling

Untuk menyinkronkan data historis atau data dummy seeder yang belum memiliki jurnal keuangan, tersedia 2 command CLI:

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

---

## 🚀 4. Area Audit & Pengembangan Lanjutan untuk Agent Selanjutnya

Jika pengguna meminta pengembangan modul keuangan lebih mendalam, berikut adalah daftar rekomendasi *roadmap* teknis yang dapat dilanjutkan:

1. **Pencatatan Beban Operasional Langsung (Direct Operational Expenses)**:
   - Modul pencatatan pengeluaran Kas Kecil harian (Beban Listrik `5-2101`, Beban Air `5-2102`, Beban Gas `5-2103`, Beban Sewa `5-2104`).
   - Menerbitkan jurnal: `Dr. Beban Operasional` | `Cr. Kas Kecil`.
2. **Pelunasan Hutang Usaha Supplier (Accounts Payable Settlement)**:
   - Modul pencatatan pembayaran invoice GRN ke supplier.
   - Menerbitkan jurnal: `Dr. Hutang Usaha (2-1101)` | `Cr. Bank / Kas (1-1102)`.
3. **Konsolidasi Laporan Laba Rugi (Profit & Loss) Multi-Cabang**:
   - Menampilkan perbandingan performa finansial antar cabang (Wijaya Kusuma, Dr. Sutomo, Pangeran Hidayatullah, Lambung Mangkurat) secara konsolidasi maupun per cabang.

---

## 📂 5. Peta Berkas Kunci (Key Files Map)

- **[JournalService.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Services/Finance/JournalService.php)**: Central engine pembuatan dan validasi jurnal akuntansi.
- **[HRController.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Http/Controllers/HR/HRController.php)**: Controller HR & Payroll (termasuk finalisasi & manual sync).
- **[JournalController.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Http/Controllers/Finance/JournalController.php)**: Controller manajemen Jurnal Umum Keuangan.
- **[BackfillPayrollJournals.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/app/Console/Commands/BackfillPayrollJournals.php)**: CLI command untuk backfill jurnal payroll.
- **[PayrollFinancialSyncTest.php](file:///c:/laragon/www/IstanaLaundryManagementSystem/tests/Feature/PayrollFinancialSyncTest.php)**: Automated test suite sinkronisasi payroll ke buku besar.
