# Phase: TEST 2 — UAT Fixes & Improvements

> Started: **2026-07-30**  
> Source: *Notes Laundry System #2* (Tech Lead UAT ~01:52 WITA)  
> Issues: GitHub **#29–#36** · Linear **KIL-37 – KIL-44**  
> Base branch: **`master`**

---

## Konteks

Setelah Gelombang A (security + cache #14–#21) dan penutupan #22–#28 di tracker, UAT lapangan menemukan sisa bug + gap UX yang harus ditutup sebelum rilis “enak dipakai harian”.

---

## Issue map

| Priority | GH | Linear | Title |
|----------|-----|--------|--------|
| P0 Bug | [#31](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/31) | KIL-39 | Payroll generate nominal 0 |
| P0 Bug | [#30](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/30) | KIL-38 | Chart komparasi kosong setelah switch global |
| P0 Bug | [#29](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/29) | KIL-37 | Timezone GMT+8 (WITA) |
| P1 UX | [#32](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/32) | KIL-40 | Production search + role list UI |
| P1 UX | [#33](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/33) | KIL-41 | CRM stats / riwayat / WA |
| P1 UX | [#34](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/34) | KIL-42 | Receipt hyperlink track |
| P2 | [#35](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/35) | KIL-43 | Laporan Keuangan charts |
| P2 | [#36](https://github.com/fk0u/IstanaLaundryManagementSystem/issues/36) | KIL-44 | Export CRM / Performance / Aset |

**Urutan kerja disarankan:** #31 → #30 → #29 → #32 → #33 → #34 → #35 → #36

Prompts: [AI_PROMPTS.md](AI_PROMPTS.md)

---

## Mapping mentah Notes #2 → Issue

| Notes item | Issue |
|------------|-------|
| A Chart scope global | #30 |
| B CRM fitur | #33 |
| C Receipt link | #34 |
| D Timestamp GMT+8 | #29 |
| E Export multi-modul | #36 |
| F Payroll 0 | #31 |
| G Laporan grafik | #35 |
| H Production search + role UI | #32 |

---

## Workflow

```bash
git checkout master && git pull
git checkout -b fix/payroll-zero   # contoh untuk #31
# ... implement, test ...
# PR → master
# Close GH issue + Linear Done
```

---

## Acceptance fase

- [ ] Semua #29–#36 closed
- [ ] UAT ulang A–H tanpa regresi security (#15–#20)
- [ ] Queue worker tetap jalan untuk journal/GRN jobs (#21)
