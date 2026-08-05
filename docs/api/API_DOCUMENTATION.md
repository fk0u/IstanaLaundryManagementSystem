# Istana Laundry Management System — Complete RESTful API v1 Reference

Dokumentasi lengkap RESTful API v1 untuk **Istana Laundry Management System Samarinda**. API ini melingkupi 10 domain utama ERP backend (Dashboard, Orders, CRM Customers, Inventory, HR & Payroll, Fixed Assets, Akuntansi/Finance, Procurement, Cashier Shifts, dan Master Data).

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

## 🚀 Ringkasan Modul API

| Modul | Method & Endpoint | Keterangan |
| :--- | :--- | :--- |
| **Public API** | `GET /v1/branches` | List cabang aktif & alamat |
| | `GET /v1/services` | List tarif & jenis cuci |
| | `GET /v1/track/{orderNumber}` | Lacak status nota & timeline |
| | `POST /v1/orders/online` | Order online dengan koordinat GPS |
| **Auth** | `POST /login` | Staff Login (Mendapatkan Bearer Token) |
| | `GET /me` | Detail profil staf login |
| | `POST /logout` | Revoke token Sanctum |
| **Dashboard** | `GET /v1/dashboard/stats` | KPI penjualan, omzet & order aktif |
| | `GET /v1/dashboard/charts` | Grafik omzet bulanan |
| **Orders** | `GET /v1/orders` | List transaksi order (filter status/cabang) |
| | `GET /v1/orders/{id}` | Detail transaksi order |
| | `POST /v1/orders/{id}/payments` | Pelunasan / bayar cicilan order |
| **Customers** | `GET /v1/customers` | List & cari member pelanggan |
| | `POST /v1/customers` | Daftarkan member baru |
| | `GET /v1/customers/{id}` | Detail member & histori poin |
| | `PUT /v1/customers/{id}` | Update profil member |
| | `DELETE /v1/customers/{id}` | Hapus member |
| | `POST /v1/customers/{id}/adjust-points` | Koreksi poin loyalitas |
| **Inventory** | `GET /v1/inventory` | Stok bahan & peringatan low stock |
| | `POST /v1/inventory` | Tambah item stok baru |
| | `PUT /v1/inventory/{id}/adjust` | Penyesuaian stok opname |
| **HR & Payroll** | `GET /v1/hr/employees` | List karyawan aktif |
| | `POST /v1/hr/employees` | Tambah karyawan baru |
| | `PUT /v1/hr/employees/{id}` | Update data karyawan |
| | `GET /v1/hr/payrolls` | Histori laporan slip gaji |
| | `POST /v1/hr/payrolls` | Generate gaji bulanan |
| **Fixed Assets** | `GET /v1/assets` | List aset tetap & nilai buku |
| | `POST /v1/assets` | Pendaftaran aset baru |
| **Finance & COA**| `GET /v1/finance/coa` | Chart of Accounts |
| | `POST /v1/finance/coa` | Tambah akun perkiraan COA |
| | `GET /v1/finance/journals` | Jurnal umum & mutasi |
| | `POST /v1/finance/journals` | Entri jurnal manual (Debit/Kredit) |
| | `GET /v1/finance/reports/income-statement` | Laporan Laba Rugi real-time |
| **Procurement** | `GET /v1/procurement/suppliers` | List supplier pemasok |
| | `POST /v1/procurement/suppliers` | Tambah supplier baru |
| | `GET /v1/procurement/purchase-requests` | List Purchase Request (PR) |
| | `POST /v1/procurement/purchase-requests` | Pengajuan PR baru |
| | `GET /v1/procurement/purchase-orders` | List Purchase Order (PO) |
| | `POST /v1/procurement/grns` | Penerimaan Barang (GRN) |
| **Shifts** | `GET /v1/shifts` | Histori shift & audit selisih kas |
| | `POST /v1/shifts/open` | Buka shift kasir baru |
| | `POST /v1/shifts/close` | Tutup shift & setoran kasir |
| **Master Data** | `POST /v1/master/services` | Tambah jenis layanan laundry |
| | `PUT /v1/master/services/{id}` | Update harga layanan per cabang |
| | `POST /v1/master/branches` | Registrasi cabang baru |

---

*Hak Cipta © 2026 Istana Laundry Management System — Technical API Reference.*
