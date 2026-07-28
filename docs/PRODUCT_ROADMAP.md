# Product Roadmap — Istana Laundry (Samarinda)

**Tujuan produk:** sistem yang **layak dipakai harian** oleh kasir, workshop, admin cabang, finance, dan owner — bukan hanya demo modul.

**Versi roadmap:** 1.0 · **28 Juli 2026**

---

## 1. Visi

> Satu sistem untuk seluruh siklus laundry Istana: dari pelanggan datang di kasir, cucian bergerak di workshop, bahan masuk dari supplier, sampai buku besar dan gaji karyawan — **per cabang**, dengan kontrol akses yang benar dan data yang bisa dipercaya untuk keputusan bisnis.

---

## 2. Persona & job-to-be-done

| Persona | Job-to-be-done |
|---------|----------------|
| Kasir | Input order cepat, pelanggan ketemu/didaftar, bayar, struk |
| Operator workshop | Lihat antrean stasiun, majukan status tanpa error |
| Admin cabang | Stok & PR, pantau omset outlet, approve alur lokal |
| Finance | Jurnal bersih, laporan bulanan, tutup periode, aset |
| HR/Owner | Payroll adil (BPJS & komponen), kinerja staff |
| Owner | Bandingkan cabang, promo efektif, audit sengketa |

---

## 3. Maturity model

| Level | Arti | Target Istana |
|-------|------|---------------|
| L0 Demo | Modul terbuka, role longgar | Terlewati |
| **L1 Operasional** | Kasir+workshop jalan stabil | **Sekarang (V1)** |
| **L2 Terkontrol** | RBAC, audit, journal aman, isolasi cabang | **Gelombang A** |
| **L3 Bisnis mature** | Promo, payroll, aset, dashboard & kinerja dalam | **Gelombang B–C** |
| L4 Scale | API perangkat, notifikasi, multi-outlet optimasi | Setelah UAT L3 |

---

## 4. Gelombang rilis

### Gelombang A — “Boleh dipercaya” (Security + integrity)
**Outcome:** data keuangan & cabang tidak bocor/dobel; role sesuai kerja.

| Item | Issue | Value bisnis |
|------|-------|--------------|
| Role middleware | #15 | Kasir tidak bisa close buku / approve PO sembarangan |
| Auth register/API throttle | #16 | Akun tidak bisa digandakan sembarangan |
| Tenant isolation | #17 | Outlet A tidak lihat nota outlet B |
| Audit mutasi | #18 | Sengketa refund/gaji bisa dilacak |
| Journal lock/idempotent | #19 | Omset = jurnal, tanpa double count |
| Docker/Nginx harden | #20 | Production tidak rusak karena seed/header |
| Cache + queue | #21 | Dashboard & post-order tidak lemot |

**Branch:** `chore/security-and-caching`

---

### Gelombang B — “Kasir & supervisory enak dipakai”
**Outcome:** kecepatan outlet + keputusan owner.

| Item | Issue | Value bisnis |
|------|-------|--------------|
| POS cari/tambah customer | #23 | Antrian kasir lebih cepat |
| Promo/kupon engine | #22 | Kampanye terukur, tidak manual ad-hoc |
| Dashboard dinamis | #24 | Owner lihat hari ini tanpa spam refresh |
| Kinerja detail | #26 | Tahu siapa produktif & bottleneck stasiun |
| Role Finance UX | #25 | Finance kerja di modulnya sendiri |

---

### Gelombang C — “Back-office kelas UMKM formal”
**Outcome:** HR & aset siap audit internal.

| Item | Issue | Value bisnis |
|------|-------|--------------|
| Payroll BPJS Kes + TK + bonus | #27 | Slip sesuai praktik gaji riil |
| Aset + maintenance + depresiasi jelas | #28 | Nilai aset & biaya rawat terlacak per cabang |

---

### Gelombang D — Advanced (setelah L3 stabil)

| Tema | Contoh capability |
|------|-------------------|
| API & perangkat | Tablet kasir / scanner status lewat API orders & production |
| Notifikasi | WA official / antrian “Siap diambil” ke pelanggan |
| Pembayaran | Integrasi QRIS/payment gateway + rekonsiliasi otomatis |
| Inventori lanjutan | Pemakaian bahan per order (BOM ringan), low-stock alert aktif |
| Multi-outlet analytics | Perbandingan margin cabang, export berkala ke Excel terjadwal |
| Keamanan lanjut | 2FA owner/finance, enkripsi NIK |

---

## 5. Prinsip desain produk

1. **Cabang first** — setiap angka omset/stok/gaji bisa difilter outlet  
2. **Kasir under 1 minute** — jangan paksa kasir buka 5 menu untuk 1 nota  
3. **Keuangan immutable-ish** — koreksi lewat reverse/journal, bukan edit diam-diam  
4. **Role = job** — layar Finance ≠ layar Cashier  
5. **Auditability** — perubahan kritis selalu punya siapa/kapan/apa  
6. **Incremental** — jangan menahan kasir karena modul aset belum sempurna  

---

## 6. Metrik sukses (contoh KPI produk)

| KPI | Definisi kasar | Target awal |
|-----|----------------|-------------|
| Waktu buat order | Submit POS sampai dapat nomor nota | < 60s median |
| Akurasi jurnal | Order paid tanpa journal / double journal | 0 kejadian / bulan |
| Adoption workshop | % order yang statusnya di-update digital (bukan hanya kertas) | > 90% |
| Closing bulanan | Waktu finance tutup periode setelah akhir bulan | < 3 hari kerja |
| Tiket sengketa “data hilang” | Komplain internal data salah cabang | menurun tiap sprint |

---

## 7. Dependensi & risiko

| Risiko | Mitigasi |
|--------|----------|
| Role dikunci terlalu ketat sebelum mapping jelas | Matriks role disepakati Owner sebelum merge #15 |
| Cache menampilkan omset basi | TTL pendek + invalidasi on order paid |
| Scope feature creep | Gelombang A selesai dulu sebelum promo/payroll besar |
| Data seed di production | Guard entrypoint #20 |

---

## 8. Cara pakai dokumen ini

- **Owner / klien:** baca visi, gelombang B–C, KPI  
- **Tech lead:** Gelombang A + SRS §7–8  
- **Developer:** issue GitHub + `docs/AI_PROMPTS.md`  
- **QA:** kriteria penerimaan SRS §8 + test per issue  

Dokumen hidup — update saat issue epic ditutup atau prioritas bisnis berubah.
