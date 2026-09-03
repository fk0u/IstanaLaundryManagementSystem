# Spesifikasi Lengkap Fitur, Matriks RBAC & Kredensial Akun Sistem
# Istana Laundry Management System (Enterprise Semi-ERP)

> **Versi Dokumen:** 3.3 (Full Enterprise Credentials Edition)  
> **Tanggal Rilis:** 27 Agustus 2026  
> **Klien / Bisnis:** Istana Premium Laundry Service (Samarinda, Kalimantan Timur)  
> **Default Password:** `password` (Default untuk seluruh akun awal)  
> **Official Hotline / WhatsApp:** [+62 811-5599-199](https://wa.me/628115599199)  
> **Live Production URL:** [https://istanasystem.alk-tech.my.id](https://istanasystem.alk-tech.my.id)  
> **Dokumentasi API Swagger:** [https://istanasystem.alk-tech.my.id/api/documentation](https://istanasystem.alk-tech.my.id/api/documentation)  
> **Repository Kode:** [https://github.com/fk0u/IstanaLaundryManagementSystem](https://github.com/fk0u/IstanaLaundryManagementSystem)  
> **Berkas PDF Resmi:** [ISTANA_LAUNDRY_FULL_RBAC_ACCOUNTS_AND_FEATURES.pdf](file:///d:/Project/IstanaLaundryManagementSystem/docs/ISTANA_LAUNDRY_FULL_RBAC_ACCOUNTS_AND_FEATURES.pdf)

---

## 1. Ringkasan Eksekutif & Arsitektur Sistem

### 1.1 Gambaran Umum Produk
**Istana Laundry Management System** adalah platform Enterprise Resource Planning (Semi-ERP) berbasis web multi-cabang terpadu yang dirancang khusus untuk operasional komersial laundry modern kelas premium di Kota Samarinda, Kalimantan Timur.

Sistem ini mengintegrasikan seluruh rantai proses bisnis secara *real-time*:
1. **Front-Office (POS Kasir)**: Transaksi harian, pembayaran multi-metode, cetak struk thermal/A4, manajemen shift kasir, kas kecil, dan integrasi WhatsApp.
2. **Workshop Produksi**: Pelacakan cucian 8-stasiun linear (`TERIMA` s/d `DIAMBIL`), penegakan aturan pengerjaan, dan log audit operator.
3. **CRM & Loyalitas Pelanggan**: 4-Tier keanggotaan (Bronze, Silver, Gold, Platinum), engine poin loyalitas otomatis, dan voucher promo diskon.
4. **Inventory & Pengadaan (Procurement)**: Master Bahan Habis Pakai (BHP), peringatan stok menipis, alur 3-tahap PR → PO → GRN, dan pemotongan stok metode FIFO.
5. **Finance & Double-Entry Accounting**: Bagan akun standar (COA Level 1–4), penjurnalan otomatis berbasis event bisnis, penutupan periode buku, dan laporan keuangan komprehensif.
6. **HR & Penggajian Terkonsolidasi (Payroll)**: Biodata karyawan, integrasi user login, pencatatan presensi & lembur, draf gaji batch bulanan, dan slip gaji resmi.
7. **Fixed Assets & Depresiasi**: Inventarisasi aset tetap, kalkulasi depresiasi garis lurus & saldo menurun ganda, serta log perawatan mesin.
8. **Security & Profiling**: Two-Factor Authentication (TOTP RFC 6238), Trust Device 30 hari, proteksi HTTP headers, dan kompresi WebP avatar ≤ 200KB.
9. **Full RESTful API Suite**: 16 API Controller, 80+ endpoints terproteksi Sanctum Bearer Token, dan dokumentasi interaktif Swagger UI di `/api/documentation`.

---

### 1.2 Arsitektur Multi-Cabang & 5 Outlet Fisik Resmi Samarinda
Sistem menerapkan isolasi data multi-cabang yang ketat melalui:
- **Global Scope Eloquent (`BranchScoped` Trait)**: Kueri database otomatis difilter berdasarkan `branch_id` pengguna yang login.
- **`BranchScopeMiddleware`**: Memvalidasi setiap request HTTP web dan API v1 agar tidak terjadi kebocoran data antar outlet.
- **Dynamic Branch Switcher**: Pengguna eksekutif (`Developer`, `Owner`, `Super_Admin`, `Finance`) dapat beralih konteks cabang aktif kapan saja melalui modal selector topbar/sidebar.

#### Daftar 5 Outlet Fisik Samarinda:
| Kode | Nama Outlet | Alamat Lengkap | Telepon | Email Cabang |
|:---:|---|---|:---:|---|
| **WJK** | Istana Laundry - Wijaya Kusuma (Pusat) | Jl. Wijaya Kusuma Blok V-C Gg. Rina, Samarinda Ulu | 08115550001 | `wjk@istanalaundry.com` |
| **SUT** | Istana Laundry - Dr. Sutomo | Jl. Dr. Sutomo, Sidodadi, Samarinda Ulu | 08115550002 | `sutomo@istanalaundry.com` |
| **HID** | Istana Laundry - P. Hidayatullah | Jl. Pangeran Hidayatullah, Karang Mumus, Samarinda Kota | 08115550003 | `hidayatullah@istanalaundry.com` |
| **LMG** | Istana Laundry - Lambung Mangkurat | Jl. Lambung Mangkurat, Sungai Pinang Dalam, Sungai Pinang | 08115550004 | `lambung@istanalaundry.com` |
| **GTS** | Istana Laundry - Grand Taman Sari | Kawasan Grand Taman Sari, Harapan Baru, Loa Janan Ilir | 08115550005 | `gts@istanalaundry.com` |

---

## 2. Daftar Lengkap 18 Akun Pengguna Resmi & Kredensial Login

> 🔑 **Default Password Semua Akun:** `password`

### 2.1 Akun Tingkat Eksekutif & Global (Super Level Users)
| Role | Nama Lengkap | NIK | Email Login | Password | Jabatan Resmi | Scope Cabang |
|---|---|:---:|---|:---:|---|:---:|
| **Developer** | Rian Ardiansyah | NIK-DEV-0001 | `developer@istanalaundry.com` | `password` | Developer Utama | Global (Semua) |
| **Owner** | H. Bambang Setiawan, S.E. | NIK-OWN-0001 | `owner@istanalaundry.com` | `password` | Pemilik Utama / Direksi | Global (Switchable) |
| **Super_Admin** | Siti Nurhaliza, M.M. | NIK-ADM-0001 | `superadmin@istanalaundry.com` | `password` | Super Administrator Pusat | Global (Switchable) |

---

### 2.2 Akun Cabang Wijaya Kusuma (Pusat - WJK)
| Role | Nama Lengkap | NIK | Email Login | Password | Jabatan Resmi | Info Rekening |
|---|---|:---:|---|:---:|---|:---:|
| **Branch_Admin** | Rahmat Hidayat | NIK-WJK-0001 | `admin.wjk@istanalaundry.com` | `password` | Manager Cabang WJK | BCA 8830-xxxxxx |
| **Cashier** | Dewi Anggraini | NIK-WJK-0002 | `cashier.wjk@istanalaundry.com` | `password` | Kasir Senior WJK | BCA 8830-xxxxxx |
| **Workshop_Admin** | Agus Prasetyo | NIK-WJK-0003 | `workshop.admin.wjk@istanalaundry.com` | `password` | Supervisor Workshop WJK | BCA 8830-xxxxxx |
| **Workshop_Staff** | Budi Santoso | NIK-WJK-0004 | `staff.wjk@istanalaundry.com` | `password` | Operator Cuci & Setrika | BCA 8830-xxxxxx |
| **CS_Marketing** | Indah Permatasari | NIK-WJK-0005 | `marketing.wjk@istanalaundry.com` | `password` | Staf CS & Marketing | BCA 8830-xxxxxx |
| **Finance** | Sri Wahyuni, A.Md. | NIK-WJK-0006 | `finance.wjk@istanalaundry.com` | `password` | Kepala Akuntan & Finance | Global (Switchable) |

---

### 2.3 Akun Cabang Dr. Sutomo (SUT)
| Role | Nama Lengkap | NIK | Email Login | Password | Jabatan Resmi | Info Rekening |
|---|---|:---:|---|:---:|---|:---:|
| **Branch_Admin** | Eko Kurniawan | NIK-SUT-0001 | `admin.sut@istanalaundry.com` | `password` | Manager Cabang Sutomo | BCA 8830-xxxxxx |
| **Cashier** | Nia Ramadhani | NIK-SUT-0002 | `cashier.sut@istanalaundry.com` | `password` | Kasir Utama Sutomo | BCA 8830-xxxxxx |
| **Workshop_Staff** | Dedi Kurnia | NIK-SUT-0003 | `staff.sut@istanalaundry.com` | `password` | Operator Workshop Sutomo | BCA 8830-xxxxxx |

---

### 2.4 Akun Cabang Pangeran Hidayatullah (HID)
| Role | Nama Lengkap | NIK | Email Login | Password | Jabatan Resmi | Info Rekening |
|---|---|:---:|---|:---:|---|:---:|
| **Branch_Admin** | Fajar Nugraha | NIK-HID-0001 | `admin.hid@istanalaundry.com` | `password` | Manager Cabang Hidayatullah | BCA 8830-xxxxxx |
| **Cashier** | Rina Astuti | NIK-HID-0002 | `cashier.hid@istanalaundry.com` | `password` | Kasir Utama Hidayatullah | BCA 8830-xxxxxx |
| **Workshop_Staff** | Ahmad Fauzi | NIK-HID-0003 | `staff.hid@istanalaundry.com` | `password` | Operator Workshop Hidayatullah | BCA 8830-xxxxxx |

---

### 2.5 Akun Cabang Lambung Mangkurat (LMG)
| Role | Nama Lengkap | NIK | Email Login | Password | Jabatan Resmi | Info Rekening |
|---|---|:---:|---|:---:|---|:---:|
| **Branch_Admin** | Hendra Kusuma | NIK-LMG-0001 | `admin.lmg@istanalaundry.com` | `password` | Manager Cabang Lambung | BCA 8830-xxxxxx |
| **Cashier** | Maya Safitri | NIK-LMG-0002 | `cashier.lmg@istanalaundry.com` | `password` | Kasir Utama Lambung | BCA 8830-xxxxxx |
| **Workshop_Staff** | Rizky Febrian | NIK-LMG-0003 | `staff.lmg@istanalaundry.com` | `password` | Operator Workshop Lambung | BCA 8830-xxxxxx |

---

## 3. Matriks Hak Akses Granular (RBAC Matrix)

Pemetaan antara **32 Granular Permissions** dengan **9 System Roles**:

| Kategori & Permission | DEV | OWN | SUP | ADM | W-ADM | CSH | W-STF | CSM | FIN |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **A. POS & ORDER MANAGEMENT** | | | | | | | | | |
| `orders.view` (Lihat Order) | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | ✅ |
| `orders.create` (Input Order POS) | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `orders.update` (Update Bayar Order) | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| `orders.delete` (Hapus Order) | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `orders.refund` (Approval Refund) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **B. WORKSHOP & PRODUCTION** | | | | | | | | | |
| `production.view` (Lihat Antrean Produksi) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| `production.update` (Update Status Stasiun) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| `production.bulk_update` (Bulk Update Status) | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| **C. CRM, CUSTOMER & LOYALTY** | | | | | | | | | |
| `customers.view` (Lihat Data Pelanggan) | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ |
| `customers.create` (Tambah Pelanggan Baru) | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ |
| `customers.update` (Edit Data Pelanggan) | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ |
| `customers.delete` (Hapus Data Pelanggan) | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `loyalty.manage` (Kelola Poin & Promo) | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ |
| **D. INVENTORY & PROCUREMENT** | | | | | | | | | |
| `inventory.view` (Lihat Stok BHP) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| `inventory.create` (Tambah Item BHP) | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `inventory.update` (Koreksi / Adjust Stok) | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `purchase_requests.approve` (Approve PR) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **E. FINANCE & ACCOUNTING** | | | | | | | | | |
| `journals.view` (Lihat Jurnal & Buku Besar) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `journals.create` (Input Jurnal Manual) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `journals.post` (Posting Jurnal) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `journals.reverse` (Reversal Jurnal) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `accounting_periods.close` (Tutup Buku) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **F. HR & PAYROLL MANAGEMENT** | | | | | | | | | |
| `employees.manage` (Kelola Karyawan) | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `attendances.manage` (Kelola Presensi) | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `payroll.manage` (Generate & Slip Gaji) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **G. FIXED ASSETS & DEPRECIATION** | | | | | | | | | |
| `assets.manage` (Master Aset & Servis) | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `depreciation.process` (Proses Penyusutan) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **H. REPORTS & ANALYTICS** | | | | | | | | | |
| `reports.sales` (Laporan Omset & Penjualan) | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ | ✅ |
| `reports.production` (Laporan Produksi) | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `reports.finance` (Laba Rugi, Neraca) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| `reports.export` (Ekspor PDF, Excel, CSV) | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| **I. MASTER DATA & GOVERNANCE** | | | | | | | | | |
| `services.manage` (Master Layanan & Harga) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `branches.manage` (Master Cabang) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `users.manage` (Manajemen Akun Staf) | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| `roles.manage` (Konfigurasi RBAC) | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 4. Rincian Lengkap Fitur per Modul

### Modul 1: Autentikasi, Keamanan & Profiling
- **Two-Factor Authentication (2FA TOTP)**: Sesuai standar RFC 6238, QR code setup, 8 kode darurat.
- **Trust Device 30 Hari**: Cookie `2fa_device_trust` & token SHA-256 pada `user_trusted_devices`.
- **Kompresi Avatar WebP**: Otomatis dikompresi ≤ 200KB via `ImageCompressionService`.
- **Security Headers & Anti-Brute Force**: HSTS, CSP, X-Frame-Options, rate limiting login.
- **Audit Logging**: Jejak riwayat login, logout, create, update, delete, dan mutasi finansial.

### Modul 2: Point of Sale (POS) & Shift Kasir
- **Layar POS Cepat**: Mendukung desktop dan tablet; katalog Kiloan, Satuan, Karpet/Gorden, Express.
- **Pendaftaran Member Instan**: Pencarian & modal tambah pelanggan langsung dari layar POS.
- **Multi-Metode Bayar**: Tunai, Transfer Bank, QRIS, Pembayaran Sebagian/Piutang.
- **Nomor Nota Unik Berurutan**: `ORD-[CABANG]-[YYYYMM]-[NOMOR]`.
- **Manajemen Shift Kasir**: Buka/Tutup shift, rekonsiliasi kas, kas kecil (petty cash), ekspor summary PDF.
- **Cetak Struk & WhatsApp**: Struk thermal 58mm/80mm, invoice A4, kirim nota via WA resmi (+62 811-5599-199).

### Modul 3: Workshop Production & Order Tracking
- **Alur Linear 8-Stasiun**: `TERIMA → PILAH → CUCI → KERING → LIPAT → CEK → SIAP → DIAMBIL`.
- **Enforced Step Progression**: Mencegah peloncatan stasiun cucian tanpa pengerjaan bertahap.
- **Log Stasiun & Operator**: Merekam waktu pengerjaan dan staf penanggung jawab (`ProductionStatusLog`).
- **Portal Tracking Publik**: Cek progress cucian via `/track?order_number=...` atau REST API.
- **Notifikasi WA Cucian Siap**: Tombol pemicu notifikasi saat cucian berstatus `SIAP`.

### Modul 4: CRM, Membership & Loyalty Point
- **4-Tier Membership**: Bronze, Silver, Gold, Platinum berdasarkan total belanja.
- **Engine Poin Loyalitas**: Auto-earn poin, penukaran diskon langsung di kasir, koreksi poin admin.
- **Engine Promo Kupon**: Diskon %, potongan nominal, min transaksi, kuota promo, segmentasi member.
- **Ekspor Data CRM**: Format CSV, PDF, dan Excel.

### Modul 5: Inventory, FIFO Stock & Pengadaan (Procurement)
- **Master BHP & Stok Kritis**: Stok deterjen, softener, parfum, plastik packing per cabang.
- **Siklus Pengadaan 3-Tahap**:
  1. *Purchase Request (PR)*: Pengajuan kebutuhan barang & approval bertingkat.
  2. *Purchase Order (PO)*: Penerbitan order resmi ke supplier, cetak PDF, kirim via WhatsApp.
  3. *Goods Received Note (GRN)*: Penerimaan barang, penambahan stok batch FIFO, auto-post jurnal utang usaha/persediaan.
- **Master Supplier**: Database kontak, termin pembayaran, riwayat pesanan.

### Modul 6: Finance & Automated Double-Entry Accounting
- **Chart of Accounts (COA)**: Struktur hierarki Level 1–4 untuk 5 kelompok akun standar.
- **Jurnal Otomatis Berbasis Event**:
  - Order Paid → Kas/Bank (D) vs Pendapatan Laundry (K).
  - GRN Confirm → Persediaan (D) vs Utang Usaha (K).
  - Payroll Final → Beban Gaji (D) vs Utang Gaji/Kas (K).
  - Depresiasi Aset → Beban Penyusutan (D) vs Akumulasi Penyusutan (K).
- **Jurnal Umum Manual & Reversal**: Input multi-baris seimbang dan pembatalan jurnal.
- **Penutupan Periode Akuntansi**: Closing checklist dan penguncian transaksi historis.
- **Laporan Keuangan**: Laba Rugi, Neraca, Neraca Saldo, Buku Besar (CSV, Excel, PDF Standar, PowerBI PDF).

### Modul 7: HR Management & Consolidated Payroll
- **Master Karyawan & Akun User**: NIK unik, integrasi akun sistem, reset password.
- **Presensi & Lembur**: Pencatatan kehadiran dan upah lembur staf.
- **Payroll Bulanan Batch**: Gaji pokok, tunjangan, lembur, bonus, potongan absensi & BPJS.
- **Siklus Status Gaji**: `DRAFT` → `FINAL` (Terkunci) → `PAID`.
- **Cetak Slip Gaji & Auto Sync Jurnal**: Slip gaji resmi dan posting beban gaji ke buku besar.

### Modul 8: Fixed Assets & Jadwal Depresiasi
- **Inventarisasi Aset Tetap**: Mesin cuci, dryer, boiler, kendaraan operasional, peralatan toko.
- **Kalkulasi Depresiasi Otomatis**: Metode Garis Lurus & Saldo Menurun Ganda (`DepreciationSchedule`).
- **Log Maintenance Mesin**: Catatan servis berkala, biaya suku cadang, vendor teknisi.
- **Portfolio Analytics**: Grafik nilai buku vs akumulasi penyusutan per cabang.

### Modul 9: Executive Dashboard & Performance KPI
- **5 Tampilan Dashboard Khusus**: Owner, Branch Admin, Workshop Supervisor, Kasir, Finance.
- **Leaderboard Performa Staf**: Rangking omset kasir dan throughput operator workshop.
- **Ekspor Laporan Kinerja**: Unduh rekap performa dalam format PDF dan Excel.

### Modul 10: Customer Portal & Interactive Landing Page
- **Landing Page Interaktif**: Tema Material Design 3 Expressive dengan kalkulator estimasi tarif.
- **Peta Interaktif 5 Outlet**: Leaflet map canvas dengan pinpoint 5 cabang Samarinda.
- **Order Online GPS**: Modal pemesanan pickup & delivery dengan titik koordinat GPS.
- **Portal Training Staf (`/guide`)**: Panduan operasional interaktif untuk staf baru.

### Modul 11: RESTful API Engine (v1)
- **16 API Controllers, 80+ Endpoints**: Terproteksi Bearer Token Sanctum & rate limit 120 req/menit.
- **Dokumentasi Interaktif Swagger UI**: Tersedia langsung di `/api/documentation`.
- **Public API v1**: List cabang, layanan, tracking order, dan pemesanan online.
- **POS Tablet API**: Integrasi khusus aplikasi kasir berbasis tablet.

---

## 5. Informasi Kontak & Dukungan Resmi

- **Perusahaan:** Istana Premium Laundry Service Samarinda
- **Alamat Kantor Pusat:** Jl. KH. Wahid Hasyim 2 No.57, Samarinda, Kalimantan Timur 75119
- **Official Customer Care WhatsApp:** **+62 811-5599-199**
- **Live Production URL:** [https://istanasystem.alk-tech.my.id](https://istanasystem.alk-tech.my.id)
- **API Swagger Documentation:** [https://istanasystem.alk-tech.my.id/api/documentation](https://istanasystem.alk-tech.my.id/api/documentation)
- **Repository GitHub:** [https://github.com/fk0u/IstanaLaundryManagementSystem](https://github.com/fk0u/IstanaLaundryManagementSystem)

---
*© 2026 Istana Laundry Management System. Hak Cipta Dilindungi Undang-Undang.*
