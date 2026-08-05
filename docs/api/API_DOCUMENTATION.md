# Istana Laundry Management System — 100% Feature-Complete RESTful API v1 Reference

Dokumentasi lengkap RESTful API v1 untuk **Istana Laundry Management System Samarinda**. API ini melingkupi 100% seluruh modul backend ERP (Public, Auth, Dashboard, Orders & Refunds, Thermal Receipts, CRM Customers, Inventory, HR & Payroll, Fixed Assets & Depreciation, Akuntansi/Finance, Operational Expenses, Supplier Debt Payments, Procurement & Approvals, Cashier Shifts, Master Data Services/Branches, User & RBAC Management, serta Performance Metrics & Audit Trail).

---

## 📌 Base URL & Headers

* **Production:** `https://istanasystem.alk-tech.my.id/api`
* **Local:** `http://localhost:8000/api`
* **Timezone:** **WITA (UTC+8 / Asia/Singapore)**
* **Headers:**
  ```http
  Accept: application/json
  Content-Type: application/json
  Authorization: Bearer {sanctum_token}
  ```

---

## 🚀 Ringkasan Seluruh Endpoint API v1

### 1. Public API v1 (Company Profile & Online Order)
* `GET /v1/branches`: List cabang aktif & alamat lokasi.
* `GET /v1/services`: List tarif & jenis layanan laundry.
* `GET /v1/track/{orderNumber}`: Lacak status nota & timeline pengerjaan WITA.
* `POST /v1/orders/online`: Form order online dengan koordinat presisi Latitude & Longitude GPS.

### 2. Staff Authentication
* `POST /login`: Staff Login (Dapatkan Token Bearer Sanctum).
* `GET /me`: Detail profil staf login.
* `POST /logout`: Revoke token Sanctum.

### 3. Dashboard & Executive Analytics
* `GET /v1/dashboard/stats`: KPI penjualan, omzet hari ini, order aktif, dan siap ambil.
* `GET /v1/dashboard/charts`: Grafik omzet bulanan real-time.

### 4. Orders, Payments, Refunds, & Struk Thermal
* `GET /v1/orders`: List transaksi order (filter status, payment, tanggal, cabang).
* `GET /v1/orders/{id}`: Detail transaksi order lengkap (items, pembayaran, log status).
* `POST /v1/orders/{id}/payments`: Pelunasan / bayar cicilan transaksi.
* `POST /v1/orders/{id}/refund`: Permohonan refund / pembatalan order.
* `GET /v1/orders/{id}/receipt-data`: Data terstruktur JSON untuk printer thermal Bluetooth (ESC/POS).

### 5. CRM Customers & Loyalty Member
* `GET /v1/customers`: List & pencarian pelanggan/member.
* `POST /v1/customers`: Registrasi member baru.
* `GET /v1/customers/{id}`: Detail member & histori loyalty poin.
* `PUT /v1/customers/{id}`: Update profil member.
* `DELETE /v1/customers/{id}`: Hapus data member.
* `POST /v1/customers/{id}/adjust-points`: Koreksi poin loyalitas.

### 6. Inventory & Stock Management
* `GET /v1/inventory`: List stok bahan & filter *low stock warning*.
* `POST /v1/inventory`: Tambah item stok baru.
* `GET /v1/inventory/{id}`: Detail item stok.
* `PUT /v1/inventory/{id}`: Update info item stok.
* `PUT /v1/inventory/{id}/adjust`: Penyesuaian stok opname manual.
* `DELETE /v1/inventory/{id}`: Hapus item stok.

### 7. HR, Employees, & Payroll
* `GET /v1/hr/employees`: List karyawan aktif.
* `POST /v1/hr/employees`: Tambah karyawan baru.
* `PUT /v1/hr/employees/{id}`: Edit data karyawan.
* `GET /v1/hr/payrolls`: Histori laporan penggajian.
* `POST /v1/hr/payrolls`: Generate slip gaji bulanan.

### 8. Fixed Assets & Monthly Depreciation
* `GET /v1/assets`: List aset tetap & nilai buku.
* `POST /v1/assets`: Pendaftaran aset baru.
* `GET /v1/assets/{id}`: Detail aset tetap.
* `PUT /v1/assets/{id}`: Edit info aset.
* `POST /v1/assets/depreciate`: Eksekusi jurnal depresiasi garis lurus bulanan.

### 9. Finance, COA, Expenses, & Accounting Reports
* `GET /v1/finance/coa`: Chart of Accounts.
* `POST /v1/finance/coa`: Tambah akun perkiraan baru.
* `GET /v1/finance/journals`: Jurnal Umum & entri mutasi.
* `POST /v1/finance/journals`: Buat entri jurnal manual berimbang (Debit/Kredit).
* `GET /v1/finance/expenses`: List pengeluaran operasional (listrik, air, dll).
* `POST /v1/finance/expenses`: Pencatatan beban operasional baru.
* `GET /v1/finance/supplier-payments`: List pembayaran utang supplier.
* `POST /v1/finance/supplier-payments`: Pelunasan pembayaran supplier.
* `GET /v1/finance/reports/income-statement`: Laporan Laba Rugi real-time.
* `GET /v1/finance/reports/balance-sheet`: Laporan Neraca Keuangan.
* `GET /v1/finance/reports/trial-balance`: Neraca Saldo (Trial Balance).
* `GET /v1/finance/accounting-periods`: Daftar periode akuntansi bulanan.
* `POST /v1/finance/accounting-periods/{id}/close`: Proses Tutup Buku bulanan.

### 10. Procurement & Approval Workflow
* `GET /v1/procurement/suppliers`: List supplier pemasok.
* `POST /v1/procurement/suppliers`: Tambah supplier baru.
* `GET /v1/procurement/purchase-requests`: List Purchase Request (PR).
* `GET /v1/procurement/purchase-requests/{id}`: Detail PR.
* `POST /v1/procurement/purchase-requests`: Pengajuan PR baru.
* `PUT /v1/procurement/purchase-requests/{id}/approve`: Persetujuan / Penolakan PR.
* `GET /v1/procurement/purchase-orders`: List Purchase Order (PO).
* `GET /v1/procurement/purchase-orders/{id}`: Detail PO.
* `POST /v1/procurement/purchase-orders`: Buat PO baru dari PR.
* `GET /v1/procurement/grns`: List Goods Received Notes.
* `POST /v1/procurement/grns`: Penerimaan barang & update stok.

### 11. Cashier Shifts & Settlement
* `GET /v1/shifts`: Histori shift & audit selisih kas (*variance*).
* `POST /v1/shifts/open`: Buka shift kasir baru.
* `POST /v1/shifts/close`: Tutup shift & laporan setoran kasir.

### 12. Master Data Services & Branches
* `GET /v1/master/services/{id}`: Detail layanan & harga per cabang.
* `POST /v1/master/services`: Tambah jenis layanan baru.
* `PUT /v1/master/services/{id}`: Update layanan & tarif cabang.
* `DELETE /v1/master/services/{id}`: Hapus layanan.
* `GET /v1/master/branches/{id}`: Detail cabang.
* `POST /v1/master/branches`: Registrasi cabang baru.
* `PUT /v1/master/branches/{id}`: Edit data cabang.
* `DELETE /v1/master/branches/{id}`: Hapus cabang.

### 13. User Management & Spatie RBAC
* `GET /v1/users`: List akun staf user & cabang.
* `POST /v1/users`: Buat akun staf baru.
* `GET /v1/users/{id}`: Detail user & role.
* `PUT /v1/users/{id}`: Update akun & role user.
* `GET /v1/roles`: List Spatie Permission roles & permissions.

### 14. Performance Metrics & Audit Trail
* `GET /v1/performance/cashiers`: Statistik performa kasir & rata-rata nilai order.
* `GET /v1/performance/branches`: Perbandingan omzet antar cabang.
* `GET /v1/audit-logs`: Audit trail aktivitas sistem.

---

*Hak Cipta © 2026 Istana Laundry Management System — Technical RESTful API Engine.*
