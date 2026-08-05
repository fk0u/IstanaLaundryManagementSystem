# Istana Laundry Management System — Complete RESTful API Documentation

> **Base URL (Production):** `https://istanasystem.alk-tech.my.id/api`  
> **Base URL (Local):** `http://localhost:8000/api`  
> **Interactive Swagger UI:** `https://istanasystem.alk-tech.my.id/api/documentation`  
> **Official Admin / Customer Support:** +62 811-5599-199  

---

## 🔑 Autentikasi API & Keamanan

Seluruh API privat membutuhkan autentikasi **Laravel Sanctum Bearer Token**.

### 1. Login & Dapatkan Token
**Endpoint:** `POST /api/login`  
**Headers:** `Content-Type: application/json`, `Accept: application/json`

#### Request Body (Standard Login):
```json
{
  "email": "admin@istanalaundry.com",
  "password": "password",
  "device_name": "Postman-Client"
}
```

#### Response (Jika 2FA Aktif):
```json
{
  "status": "2fa_required",
  "message": "Autentikasi 2FA diperlukan. Kirimkan two_factor_code atau recovery_code."
}
```

#### Request Body (Dengan Kode 2FA & Trust Device 30 Hari):
```json
{
  "email": "admin@istanalaundry.com",
  "password": "password",
  "two_factor_code": "123456",
  "trust_device": true
}
```

#### Response (Berhasil):
```json
{
  "token": "1|abcdef1234567890...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "admin@istanalaundry.com",
    "branch_id": 1,
    "roles": ["Super_Admin"]
  },
  "device_trust_token": "xyz987654321..."
}
```

> **Perangkat Terpercaya (API):** Untuk login berikutnya tanpa menginput `two_factor_code`, sertakan header `X-Device-Trust-Token: {device_trust_token}` pada request login.

---

## 📋 Ringkasan Endpoints API (13 Modul Utama)

### 0. User Profile & 2FA Security (`/api/v1/profile`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/v1/profile` | Tampilkan profil user yang sedang login |
| `PUT` | `/v1/profile` | Update nama, email, dan nomor HP |
| `POST` | `/v1/profile/avatar` | Upload foto profil (WebP ≤ 200KB auto-compression) |
| `POST` | `/v1/profile/2fa/enable` | Inisialisasi setup 2FA (QR Code & Secret) |
| `POST` | `/v1/profile/2fa/confirm` | Konfirmasi & aktifkan 2FA dengan kode OTP |
| `POST` | `/v1/profile/2fa/disable` | Nonaktifkan 2FA dengan verifikasi password |

### 1. Public Endpoints (`/api/v1`)
| Method | Endpoint | Deskripsi | Rate Limit |
|--------|----------|-----------|------------|
| `GET` | `/v1/branches` | List semua cabang resmi Istana Laundry | 30 req/min |
| `GET` | `/v1/services` | Catalog harga & layanan cucian | 30 req/min |
| `GET/POST` | `/v1/track` | Lacak status laundry berdasarkan nomor order | 30 req/min |
| `POST` | `/v1/orders/online` | Kirim pemesanan laundry online dengan koordinat GPS | 10 req/min |

### 2. POS Tablet & Kasir (`/api/pos`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/pos/services` | Catalog layanan untuk aplikasi Kasir Tablet |
| `GET` | `/pos/customers` | Pencarian cepat data pelanggan |
| `POST` | `/pos/orders` | Buat transaksi baru dari POS Tablet |

### 3. Production & Workshop (`/api/production`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/production` | Daftar antrean cucian di workshop produksi |
| `GET` | `/production/{order}` | Detail item & status produksi order |
| `PATCH` | `/production/{order}/status` | Update status tahap produksi (CUCI, KERING, SETRIKA, PACKING, SIAP) |

### 4. Dashboard & Analytics (`/api/v1/dashboard`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/v1/dashboard/stats` | Summary KPI (omset hari ini, order aktif, pelanggan baru) |
| `GET` | `/v1/dashboard/charts` | Data grafik tren pendapatan & performa cabang |

### 5. Orders & Transactions (`/api/v1/orders`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/v1/orders` | List transaksi laundry (support search & status filter) |
| `GET` | `/v1/orders/{id}` | Detail transaksi lengkap beserta item & jurnal |
| `POST` | `/v1/orders/{id}/payments` | Input pelunasan/pembayaran pesanan |
| `POST` | `/v1/orders/{id}/refund` | Pembatalan & refund transaksi |
| `GET` | `/v1/orders/{id}/receipt-data` | Formatting data struk belanja untuk printer thermal |

### 6. CRM Customers & Loyalty (`/api/v1/customers`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/v1/customers` | List pelanggan & saldo poin loyalitas |
| `POST` | `/v1/customers` | Tambah pelanggan baru |
| `GET/PUT/DELETE` | `/v1/customers/{id}` | Detail, update, & hapus pelanggan |
| `POST` | `/v1/customers/{id}/adjust-points` | Penyesuaian poin loyalitas manual (Admin) |

### 7. Inventory & Stock FIFO (`/api/v1/inventory`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET/POST` | `/v1/inventory` | List & tambah item stok bahan cuci |
| `GET/PUT/DELETE` | `/v1/inventory/{id}` | Detail, update, & hapus item inventory |
| `PUT` | `/v1/inventory/{id}/adjust` | Stock opname & penyesuaian jumlah fisik |

### 8. HR & Payroll (`/api/v1/hr`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET/POST` | `/v1/hr/employees` | Master data karyawan |
| `PUT` | `/v1/hr/employees/{id}` | Update data karyawan & rekening bank |
| `GET/POST` | `/v1/hr/payrolls` | Rekapitulasi & pembuatan payroll bulanan |

### 9. Fixed Assets & Depreciation (`/api/v1/assets`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET/POST` | `/v1/assets` | Data aset tetap & tanggal perolehan |
| `GET/PUT` | `/v1/assets/{id}` | Detail & update spesifikasi aset |
| `POST` | `/v1/assets/depreciate` | Jalankan kalkulasi depresiasi bulanan otomatis |

### 10. Finance, COA, & Accounting (`/api/v1/finance`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET/POST` | `/v1/finance/coa` | Chart of Accounts (Akun Akuntansi) |
| `GET/POST` | `/v1/finance/journals` | Pencatatan jurnal manual & otomatis |
| `GET/POST` | `/v1/finance/expenses` | Catat beban kas kecil (petty cash) |
| `GET/POST` | `/v1/finance/supplier-payments` | Pelunasan tagihan hutang supplier |
| `GET` | `/v1/finance/reports/income-statement` | Laporan Laba Rugi |
| `GET` | `/v1/finance/reports/balance-sheet` | Laporan Neraca Keuangan |
| `GET` | `/v1/finance/reports/trial-balance` | Laporan Neraca Saldo |
| `POST` | `/v1/finance/accounting-periods/{id}/close` | Tutup periode akuntansi bulanan |

### 11. Procurement & Suppliers (`/api/v1/procurement`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET/POST` | `/v1/procurement/suppliers` | Master data supplier |
| `GET/POST` | `/v1/procurement/purchase-requests` | Pengajuan Purchase Request (PR) |
| `PUT` | `/v1/procurement/purchase-requests/{id}/approve` | Persetujuan/Approval PR |
| `GET/POST` | `/v1/procurement/purchase-orders` | Penerbitan Purchase Order (PO) |
| `GET/POST` | `/v1/procurement/grns` | Penerimaan barang (Goods Received Note - GRN) |

### 12. Cashier Shifts (`/api/v1/shifts`)
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET` | `/v1/shifts` | Riwayat shift kasir |
| `POST` | `/v1/shifts/open` | Buka shift kasir baru dengan kas awal |
| `POST` | `/v1/shifts/close` | Tutup shift kasir & audit selisih kas |

### 13. System Users, Performance, & Audit Logs
| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| `GET/POST` | `/v1/users` | Kelola akun user & hak akses role |
| `GET` | `/v1/roles` | List 7 role sistem (Spatie RBAC) |
| `GET` | `/v1/performance/cashiers` | Metrik kinerja kasir |
| `GET` | `/v1/performance/branches` | Metrik perbandingan antar cabang |
| `GET` | `/v1/audit-logs` | Audit trail lengkap seluruh aktivitas sistem |

---

## 📄 Postman & Swagger Collections

- **Swagger UI (Live Interactive):** [`https://istanasystem.alk-tech.my.id/api/documentation`](https://istanasystem.alk-tech.my.id/api/documentation)
- **Postman Collection File:** [`docs/api/IstanaLaundry_Postman_Collection.json`](IstanaLaundry_Postman_Collection.json)
- **Bruno Collection Directory:** [`docs/api/bruno/`](bruno/)
