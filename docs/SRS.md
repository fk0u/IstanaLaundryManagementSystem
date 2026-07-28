# Software Requirements Specification (SRS)

**Produk:** Istana Laundry Management System (Semi-ERP)  
**Klien:** Istana Laundry Samarinda  
**Pengembang:** KOU / Alenkosa.id  
**Versi dokumen:** 2.0  
**Tanggal:** 28 Juli 2026  
**Repo:** https://github.com/fk0u/IstanaLaundryManagementSystem  
**Status implementasi:** V1 operasional (MVP+); V2 = hardening + fitur bisnis lanjutan

---

## 1. Pendahuluan

### 1.1 Tujuan dokumen
Dokumen ini menetapkan **kebutuhan fungsional dan non-fungsional** sistem manajemen laundry multi-cabang untuk penggunaan **real business** di Istana Laundry Samarinda: kasir harian, workshop, inventory/pengadaan, keuangan, HR, dan supervisory (owner).

SRS ini membedakan:
- **AS-IS** — yang sudah ada di aplikasi (V1)
- **TO-BE** — yang diharapkan pada pengembangan lanjutan (V2+)

### 1.2 Ruang lingkup produk
Sistem web (responsive) + fondasi REST API untuk:
- Penerimaan order & pembayaran (POS)
- Pelacakan produksi cucian per stasiun
- CRM / loyalty / promo
- Stok bahan & pengadaan (PR → PO → GRN)
- Akuntansi double-entry otomatis + laporan
- HR/payroll & aset tetap
- Isolasi data per cabang dengan kontrol role

**Di luar ruang lingkup V1 (boleh masuk V2+):** aplikasi mobile native kasir, integrasi pembayaran QRIS gateway resmi, e-Faktur DJP otomatis, WMS gudang terpisah, BI tool eksternal.

### 1.3 Definisi & singkatan

| Istilah | Arti |
|---------|------|
| Cabang / Branch | Unit operasional fisik (kode, nama, alamat) |
| Nota / Order | Transaksi layanan laundry pelanggan |
| POS | Point of Sale — layar kasir |
| PR / PO / GRN | Purchase Request / Purchase Order / Goods Received Note |
| COA | Chart of Accounts |
| RBAC | Role-Based Access Control |
| SLA | Target waktu selesai order (estimasi) |

### 1.4 Referensi
- Kode sumber Laravel 13 (branch `master`)
- Issue GitHub #14–#21 (security & cache), #22–#28 (fitur bisnis lanjutan)
- `tasks.md`, `docs/PHASE_SECURITY_CACHE.md`

---

## 2. Deskripsi umum sistem

### 2.1 Perspektif produk
Aplikasi **multi-tenant ringan berbasis cabang** (bukan SaaS multi-company penuh). Satu database; isolasi data operasional lewat `branch_id` + middleware `branch.scope` + trait `BranchScoped`.

```text
[Pelanggan] --track nota--> (Public Web)
[Kasir] ----POS/Order----> [App Laravel] <---> MySQL
[Workshop] --status------>      |
[Admin/Finance/HR] --------------+---- Journal, Payroll, Assets
[Owner] ----dashboard/switch cabang----+
[Mobile/Integrasi] --Sanctum API-------+
```

### 2.2 Karakteristik pengguna

| Peran (target) | Kebutuhan utama |
|----------------|-----------------|
| **Cashier** | POS cepat, cari/tambah pelanggan, bayar, cetak/WA struk |
| **Workshop Staff / Admin** | Antrean produksi, update status, filter stasiun |
| **Branch Admin** | Operasional cabang, approve PR, pantau omset cabang |
| **Finance** | Jurnal, periode, laporan, COA, aset — tanpa campur kasir |
| **Owner / Super Admin** | Multi-cabang, kinerja, kebijakan harga/promo, audit |
| **Developer** | Full access teknis |
| **Pelanggan (publik)** | Lacak status nota |

### 2.3 Asumsi & dependensi
- Browser modern (Chrome/Edge/Safari); mobile browser untuk kasir/workshop
- Server/Docker: PHP 8.3+, MySQL 8, Nginx, Redis (disarankan V2)
- Koneksi internet stabil di outlet; offline-first **bukan** requirement V1
- Role Spatie sudah di-seed; user dibuat admin (registrasi publik harus dikunci di V2)

### 2.4 Batasan V1 yang diketahui (harus ditutup bertahap)
- Banyak route hanya `auth` (belum RBAC ketat) → issue #15, #25
- Audit log bisnis belum menyeluruh → #18
- Promo & payroll komponen masih sederhana → #22, #27
- Dashboard belum realtime → #24
- Cache/queue hampir belum dipakai → #21

---

## 3. Kebutuhan fungsional

Legenda status: **✅ V1 ada** · **🔶 V1 parsial** · **⬜ V2 direncanakan**

### 3.1 Autentikasi & otorisasi

| ID | Requirement | Status |
|----|-------------|--------|
| FR-AUTH-01 | Login session web dengan rate limit & lockout | ✅ |
| FR-AUTH-02 | Logout & manajemen profil | ✅ |
| FR-AUTH-03 | RBAC Spatie (role per user) | 🔶 role ada; middleware belum merata |
| FR-AUTH-04 | API token Sanctum (login/logout/me) | ✅ |
| FR-AUTH-05 | Nonaktifkan self-registration publik / invite-only | ⬜ #16 |
| FR-AUTH-06 | Throttle API login & password reset | ⬜ #16 |
| FR-AUTH-07 | 2FA untuk Owner/Finance (evaluasi) | ⬜ backlog |

### 3.2 Multi-cabang

| ID | Requirement | Status |
|----|-------------|--------|
| FR-BR-01 | Setiap transaksi operasional terikat `branch_id` | ✅ |
| FR-BR-02 | User non-super terkunci ke cabangnya | 🔶 |
| FR-BR-03 | Owner/Super dapat switch cabang aktif | ✅ |
| FR-BR-04 | Tidak ada leak data lintas cabang di endpoint publik | ⬜ #17 |

### 3.3 POS & order

| ID | Requirement | Status |
|----|-------------|--------|
| FR-POS-01 | Pilih layanan + qty, hitung subtotal harga cabang | ✅ |
| FR-POS-02 | Metode bayar cash/transfer/invoice; status paid/partial/pending | ✅ |
| FR-POS-03 | Nomor nota unik per cabang+periode (sequence lock) | ✅ |
| FR-POS-04 | Cari pelanggan (nama/phone) searchable di POS | 🔶 backend ada; UX #23 |
| FR-POS-05 | Tambah pelanggan dari POS tanpa ke CRM | 🔶 #23 |
| FR-POS-06 | Terapkan promo/kupon valid | 🔶 percent/nominal dasar #22 |
| FR-POS-07 | Redeem loyalty points | ✅ |
| FR-POS-08 | Cetak struk / kirim WA | ✅ route invoice |
| FR-POS-09 | List seluruh order + filter | ✅ |

**Kriteria penerimaan bisnis (POS):** kasir menyelesaikan 1 order berbayar < 60 detik pada data master yang sudah ada; pelanggan baru bisa didaftarkan tanpa meninggalkan layar POS.

### 3.4 Produksi

| ID | Requirement | Status |
|----|-------------|--------|
| FR-PRD-01 | Status linear: TERIMA→…→DIAMBIL | ✅ |
| FR-PRD-02 | Update hanya ke status berikutnya (enforced service) | ✅ |
| FR-PRD-03 | Filter per status termasuk DIAMBIL | ✅ |
| FR-PRD-04 | Pagination antrean | ✅ `paginate(15)` — UI tombol muncul jika total > 15 |
| FR-PRD-05 | Log riwayat status + petugas | ✅ (ProductionStatusLog) |
| FR-PRD-06 | Scan QR massal / stasiun kerja dedicated | ⬜ V2 |

### 3.5 CRM, loyalty, promo

| ID | Requirement | Status |
|----|-------------|--------|
| FR-CRM-01 | CRUD pelanggan + search | ✅ |
| FR-CRM-02 | Tier Bronze–Platinum + poin | ✅ |
| FR-CRM-03 | Engine promo: kode, periode, kuota, cabang, tier, service | 🔶 #22 |
| FR-CRM-04 | Laporan pemakaian promo | ⬜ #22 |

### 3.6 Inventory & pengadaan

| ID | Requirement | Status |
|----|-------------|--------|
| FR-INV-01 | Item stok per cabang, min stock, adjust | ✅ |
| FR-INV-02 | Master supplier | ✅ |
| FR-INV-03 | Alur PR → approve → PO → send/confirm → GRN → stok + jurnal | 🔶 diperbaiki; perlu uji UAT ketat |
| FR-INV-04 | FIFO batch pada pengeluaran produksi | 🔶 model/support ada; integrasi penuh V2 |
| FR-INV-05 | Alert low stock (event sudah ada, belum aktif penuh) | ⬜ |

### 3.7 Keuangan

| ID | Requirement | Status |
|----|-------------|--------|
| FR-FIN-01 | COA hierarchical | ✅ |
| FR-FIN-02 | Auto-journal order paid / GRN / payroll (observer) | 🔶 |
| FR-FIN-03 | Jurnal manual + reverse | ✅ |
| FR-FIN-04 | Tutup periode akuntansi | ✅ |
| FR-FIN-05 | Laporan: trial balance, income, balance sheet | 🔶 |
| FR-FIN-06 | Role Finance: menu & hak akses sesuai fungsi | ⬜ #25 + #15 |
| FR-FIN-07 | Journal race-safe + idempotent | ⬜ #19 |

### 3.8 HR & payroll

| ID | Requirement | Status |
|----|-------------|--------|
| FR-HR-01 | Data karyawan per cabang | ✅ |
| FR-HR-02 | Generate payroll periode | ✅ |
| FR-HR-03 | Komponen: gaji pokok, tunjangan, lembur, bonus, potongan | 🔶 parsial |
| FR-HR-04 | BPJS Kesehatan & BPJS TK terpisah + rate policy | ⬜ #27 |
| FR-HR-05 | Slip gaji cetak/PDF | 🔶 view payslip |
| FR-HR-06 | Absensi terintegrasi penuh ke payroll | 🔶 |

### 3.9 Aset tetap

| ID | Requirement | Status |
|----|-------------|--------|
| FR-AST-01 | Registrasi aset + cabang + biaya perolehan | ✅ |
| FR-AST-02 | Jadwal depresiasi (straight line / declining) | ✅ |
| FR-AST-03 | Post depresiasi ke jurnal + penanda last/next | ⬜ #28 |
| FR-AST-04 | Log maintenance (tanggal, biaya, vendor, notes) | ⬜ #28 |

### 3.10 Dashboard, kinerja, audit

| ID | Requirement | Status |
|----|-------------|--------|
| FR-DSH-01 | Dashboard role-aware (owner vs branch) | ✅ |
| FR-DSH-02 | Metrik realtime / auto-refresh | ⬜ #24 |
| FR-PRF-01 | Leaderboard kasir & workshop | ✅ |
| FR-PRF-02 | Detail drill-down transaksi & lead time | ⬜ #26 |
| FR-AUD-01 | Audit login/logout | ✅ |
| FR-AUD-02 | Audit mutasi bisnis kritis | ⬜ #18 |

### 3.11 API & integrasi

| ID | Requirement | Status |
|----|-------------|--------|
| FR-API-01 | Auth + track + production API | ✅ foundation |
| FR-API-02 | Orders/customers/POS API untuk perangkat kasir | ⬜ |
| FR-API-03 | Webhook / notifikasi WA gateway production-grade | ⬜ |

---

## 4. Kebutuhan non-fungsional

### 4.1 Keamanan (NFR-SEC)

| ID | Requirement | Target |
|----|-------------|--------|
| NFR-SEC-01 | Semua endpoint sensitif dilindungi role/permission | V2 P0 #15 |
| NFR-SEC-02 | Session secure di HTTPS; APP_DEBUG=false production | V2 |
| NFR-SEC-03 | Security headers Nginx | #20 |
| NFR-SEC-04 | Tidak seed DB otomatis di production | #20 |
| NFR-SEC-05 | Rate limit auth API | #16 |
| NFR-SEC-06 | PII (NIK, phone) dilindungi (akses + opsi encrypt) | backlog |

### 4.2 Performa (NFR-PERF)

| ID | Requirement | Target |
|----|-------------|--------|
| NFR-PERF-01 | Dashboard owner tanpa N+1 per cabang | #21 |
| NFR-PERF-02 | Cache list cabang & agregat hot path | #21 |
| NFR-PERF-03 | Observer berat (journal, GRN) via queue | #21 |
| NFR-PERF-04 | POS submit < 2s p95 pada hardware outlet tipikal | V2 measure |

### 4.3 Ketersediaan & operasional
- Deployment Docker Compose prod + CI/CD GitHub Actions → GHCR
- Backup terjadwal (spatie/laravel-backup)
- Log aplikasi + failed jobs terpantau

### 4.4 Usability
- UI Bahasa Indonesia
- Mobile-friendly untuk kasir & workshop
- Konfirmasi aksi destruktif; pesan error jelas

### 4.5 Kepatuhan bisnis lokal
- Dukungan komponen gaji sesuai praktik UMKM (BPJS)
- Jejak audit untuk sengketa order/refund/payroll
- Isolasi cabang untuk akuntabilitas outlet

---

## 5. Model data (ringkas)

Entitas inti: `User`, `Branch`, `Customer`, `Service`, `ServiceBranchPrice`, `Order`, `OrderItem`, `Promotion`, `ProductionStatusLog`, `InventoryItem`, `Supplier`, `PurchaseRequest`, `PurchaseOrder`, `GoodsReceivedNote`, `ChartOfAccount`, `Journal`, `JournalLine`, `AccountingPeriod`, `Employee`, `Attendance`, `Payroll`, `PayrollItem`, `FixedAsset`, `DepreciationSchedule`, `Refund`, `AuditLog`.

Aturan bisnis kunci:
- Order selalu punya `production_status` awal `TERIMA`
- Journal post saat order `payment_status = paid` (observer)
- GRN confirm mengupdate stok + journal
- Payroll item menghitung net dari earnings − deductions

---

## 6. Antarmuka eksternal

| Interface | Keterangan |
|-----------|------------|
| Web UI | Blade + Alpine + Tailwind |
| REST API | `/api/*` Sanctum |
| Public track | `/track?order_number=` |
| WhatsApp | Deep link / helper invoice (bukan official Business API penuh di V1) |
| GHCR | Image deploy |

---

## 7. Prioritas pengembangan (disepakati)

### Gelombang A — Fondasi aman (wajib sebelum scale user)
1. RBAC middleware (#15) + role Finance (#25)  
2. Auth hardening (#16) + tenant isolation (#17)  
3. Audit bisnis (#18) + journal idempotent (#19)  
4. Docker/Nginx hygiene (#20) + caching/queue (#21)  

### Gelombang B — Operasional outlet
5. POS customer UX (#23)  
6. Promo engine (#22)  
7. Dashboard dinamis (#24)  
8. Kinerja detail (#26)  

### Gelombang C — Back-office mature
9. Payroll BPJS lengkap (#27)  
10. Aset + maintenance (#28)  
11. API perluasan & notifikasi  

---

## 8. Kriteria penerimaan rilis “layak bisnis real”

Sistem dianggap **siap dipakai rutin Istana Laundry Samarinda** jika:

1. Kasir & workshop role terpisah; tidak bisa saling akses modul kritis tanpa izin  
2. Order paid selalu punya jejak journal tunggal (tidak dobel)  
3. Laporan keuangan mencerminkan transaksi paid cabang yang benar  
4. PR→PO→GRN diuji end-to-end di 1 cabang nyata  
5. Payroll slip memuat komponen yang disepakati HR (minimal BPJS + bonus/potongan)  
6. Owner dapat melihat kinerja per cabang dengan filter waktu  
7. Production & staging: `APP_DEBUG=false`, tanpa auto-seed, backup berjalan  
8. UAT signed-off oleh perwakilan outlet + finance  

---

## 9. Riwayat revisi

| Versi | Tanggal | Catatan |
|-------|---------|---------|
| 1.0 | 2025–2026 early | Spesifikasi awal modul 14 |
| **2.0** | **28 Jul 2026** | Sinkron AS-IS kode, issue tracker, roadmap V2 advanced untuk user bisnis nyata |
