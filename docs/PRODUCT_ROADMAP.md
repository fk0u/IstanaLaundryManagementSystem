# Product Roadmap — Istana Laundry (Samarinda)

**Tujuan produk:** sistem yang **layak dipakai harian** oleh kasir, workshop, admin cabang, finance, dan owner.

**Versi roadmap:** 1.1 · **30 Juli 2026**

---

## 1. Visi

> Satu sistem untuk seluruh siklus laundry Istana: dari pelanggan di kasir, cucian di workshop, bahan dari supplier, sampai buku besar dan gaji — **per cabang**, dengan kontrol akses benar dan data yang bisa dipercaya.

---

## 2. Persona & job-to-be-done

| Persona | Job-to-be-done |
|---------|----------------|
| Kasir | Order cepat, pelanggan ketemu/didaftar, bayar, struk |
| Operator workshop | Antrean stasiun, update status, cari nota |
| Admin cabang | Stok & PR, omset outlet |
| Finance | Jurnal, laporan, tutup periode, aset |
| HR/Owner | Payroll (BPJS & komponen), kinerja |
| Owner | Bandingkan cabang, promo, audit |

---

## 3. Maturity model

| Level | Arti | Status Istana |
|-------|------|---------------|
| L0 Demo | Modul terbuka, role longgar | Terlewati |
| L1 Operasional | Kasir+workshop stabil | ✅ |
| **L2 Terkontrol** | RBAC, audit, journal aman, isolasi, cache/queue | ✅ **Gelombang A selesai** (#14–#21) |
| **L3 Bisnis mature** | Promo, payroll, aset, dashboard/kinerja + **UAT polish** | 🔄 **TEST 2** (#29–#36) + residual #22–#28 verifikasi |
| L4 Scale | API perangkat, WA/QRIS, multi-outlet analytics | Belum |

---

## 4. Gelombang rilis

### Gelombang A — “Boleh dipercaya” ✅ DONE
|#15–#21| Role, auth, tenant, audit, journal, docker, cache/queue |

### Gelombang B–C — fitur bisnis (#22–#28)
Ditutup di tracker 29 Jul 2026. **Verifikasi UAT** jika masih ada residual di lapangan (POS customer, promo engine, dll.).

### Gelombang B2 — TEST 2 polish (aktif)
**Outcome:** bug UAT 30 Jul hilang; CRM/Production/Export lebih siap outlet.

| Item | Issue |
|------|-------|
| Payroll nominal 0 | #31 |
| Chart scope global | #30 |
| Timezone WITA | #29 |
| Production search + staff UI | #32 |
| CRM stats / WA / riwayat | #33 |
| Receipt track link | #34 |
| Finance report charts | #35 |
| Export CRM / Performance / Aset | #36 |

### Gelombang D — Advanced
API orders/customers · WA production · QRIS · BOM stok · 2FA · KPI keuangan dashboard formal · Redis prod

---

## 5. Prinsip desain

1. Cabang first  
2. Kasir under 1 minute  
3. Keuangan immutable-ish (reverse, bukan edit diam-diam)  
4. Role = job  
5. Auditability  
6. Incremental  

---

## 6. KPI produk (contoh)

| KPI | Target awal |
|-----|-------------|
| Waktu buat order | < 60s median |
| Order paid tanpa journal / double | 0 / bulan |
| Update status digital | > 90% order |
| Closing bulanan | < 3 hari kerja |

---

## 7. Risiko

| Risiko | Mitigasi |
|--------|----------|
| Queue worker tidak jalan | Supervisor + docs deploy #21 |
| Timezone salah di server | APP_TIMEZONE + #29 |
| Feature creep saat bug P0 | Kerjakan #31/#30/#29 dulu |

---

## 8. Dokumen terkait

- [tasks.md](../tasks.md) — backlog harian  
- [PHASE_TEST2.md](PHASE_TEST2.md) — fase aktif  
- [PHASE_SECURITY_CACHE.md](PHASE_SECURITY_CACHE.md) — arsip Gelombang A  
- [SRS.md](SRS.md) · [SYSTEM_OVERVIEW.md](SYSTEM_OVERVIEW.md) · [AI_PROMPTS.md](AI_PROMPTS.md)  
