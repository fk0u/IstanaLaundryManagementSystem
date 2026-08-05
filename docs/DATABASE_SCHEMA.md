# Database Schema & Entity Specifications
# Istana Laundry Management System

> **Database Engine:** MySQL 8.0  
> **Total Tables:** 32 Tables  
> **Official Support:** +62 811-5599-199  

---

## 1. Core Tables Summary

| Tabel | Deskripsi | Key Foreign Keys |
|-------|-----------|------------------|
| `users` | Pengguna sistem & kredensial auth | `branch_id` |
| `user_trusted_devices` | Perangkat 2FA terpercaya (30 hari) | `user_id` |
| `branches` | Data outlet cabang Istana Laundry | - |
| `customers` | Database pelanggan & poin loyalitas | `branch_id` |
| `services` | Katalog harga & jenis layanan cucian | - |
| `orders` | Transaksi laundry utama | `branch_id`, `customer_id`, `user_id` |
| `order_items` | Rincian item pakaian per order | `order_id`, `service_id` |
| `order_payments` | Riwayat pembayaran & pelunasan | `order_id`, `user_id` |
| `production_status_logs` | Log pelacakan status produksi | `order_id`, `user_id` |
| `cashier_shifts` | Shift kasir & audit kas awal/akhir | `branch_id`, `user_id` |
| `inventory_items` | Stok bahan cuci (BHP) | `branch_id` |
| `inventory_stock_logs` | Riwayat mutasi stok FIFO | `inventory_item_id`, `user_id` |
| `employees` | Master data karyawan & staf | `branch_id` |
| `payrolls` | Rekapitulasi penggajian bulanan | `branch_id`, `employee_id` |
| `payroll_items` | Rincian komponen gaji & potongan | `payroll_id` |
| `fixed_assets` | Registri aset tetap | `branch_id` |
| `depreciation_schedules` | Jadwal penyusutan aset bulanan | `fixed_asset_id` |
| `chart_of_accounts` | Bagan akun akuntansi (COA) | - |
| `journals` | Header transaksi jurnal akuntansi | `branch_id` |
| `journal_lines` | Rincian debit & kredit jurnal | `journal_id`, `chart_of_account_id` |
| `suppliers` | Database vendor & supplier | - |
| `purchase_requests` | Pengajuan Pembelian (PR) | `branch_id`, `user_id` |
| `purchase_orders` | Pesanan Pembelian (PO) | `purchase_request_id`, `supplier_id` |
| `goods_received_notes` | Penerimaan barang (GRN) | `purchase_order_id`, `user_id` |
| `operational_expenses` | Beban operasional kas kecil | `branch_id`, `user_id` |
| `supplier_payments` | Pelunasan tagihan supplier | `supplier_id`, `user_id` |
| `accounting_periods` | Status periode akuntansi bulanan | - |
| `audit_logs` | Audit trail perubahan sistem | `user_id` |
| `system_settings` | Konfigurasi sistem global | - |

---

## 2. Dynamic Security Fields (`users` & `user_trusted_devices`)

```sql
-- Security columns added to users table
ALTER TABLE users 
  ADD COLUMN two_factor_secret TEXT NULL AFTER remember_token,
  ADD COLUMN two_factor_recovery_codes TEXT NULL AFTER two_factor_secret,
  ADD COLUMN two_factor_confirmed_at TIMESTAMP NULL AFTER two_factor_recovery_codes,
  ADD COLUMN avatar_path VARCHAR(255) NULL AFTER two_factor_confirmed_at,
  ADD COLUMN login_attempts INT DEFAULT 0 AFTER avatar_path,
  ADD COLUMN locked_until TIMESTAMP NULL AFTER login_attempts,
  ADD COLUMN last_login_at TIMESTAMP NULL AFTER locked_until;

-- Trusted devices table schema
CREATE TABLE user_trusted_devices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  device_token VARCHAR(64) NOT NULL,
  device_name VARCHAR(255) NULL,
  ip_address VARCHAR(45) NULL,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (device_token)
);
```
