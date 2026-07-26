# AI Prompts — Istana Laundry (context-free)

Pakai prompt di bawah ini di AI **baru** yang tidak punya riwayat chat.  
Selalu mulai session dengan **Prompt Bootstrap**, lalu 1 task prompt per sesi.

Repo: `https://github.com/fk0u/IstanaLaundryManagementSystem`  
Branch kerja: `master` (atau buat branch `fix/...` / `feat/...`)

---

## Prompt Bootstrap (tempel sekali di awal chat)

```
Kamu adalah senior Laravel developer.

## Project
- Nama: Istana Laundry Management System (semi-ERP laundry multi-cabang)
- Repo lokal / clone: IstanaLaundryManagementSystem
- Stack: Laravel 13, PHP 8.3+, Blade, Alpine.js, Tailwind, MySQL, Spatie Permission
- Multi-branch: trait BranchScoped + middleware branch.scope + session scoped_branch_id
- Production status linear: TERIMA → PILAH → CUCI → KERING → LIPAT → CEK → SIAP → DIAMBIL
- Local run: docker compose up -d --build → http://localhost:8000

## Aturan kerja
1. Baca file terkait dulu sebelum edit. Jangan mengarang API/route yang tidak ada.
2. Minimal diff — jangan refactor besar di luar scope task.
3. Ikuti pola UI existing (x-card, x-badge, x-page-header, Alpine modal).
4. Setelah edit: jelaskan file yang diubah + cara test manual.
5. Jangan commit secrets. Jangan hapus fitur yang tidak terkait.

## Referensi task
Baca `tasks.md` di root repo untuk daftar issue terbuka dan prioritas.
Graphify/codebase index boleh dipakai jika ada, tapi verifikasi ke source file nyata.

Konfirmasi kamu siap, lalu tunggu task prompt berikutnya.
```

---

## T1 — Bug Production status (#3 + #4) — KERJAKAN DULU

```
Task T1 | GitHub #3 + #4 | Linear KIL-12 + KIL-13

## Bug
Update status Production gagal untuk transisi ke KERING dan DIAMBIL dengan error:
SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column 'action'

## Root cause (sudah dianalisis)
Di app/Http/Controllers/ProductionController.php method updateStatus:
$this->auditLogService->log("update_production_status_{$newStatus}", $order, ...)

Migration audit_logs: $table->string('action', 30);
Panjang string:
- update_production_status_KERING = 31 > 30
- update_production_status_DIAMBIL = 32 > 30
(CUCI masih muat ≈29, jadi kadang status pendek lolos)

## Fix yang diminta
1. Perpendek nilai action yang di-log (contoh: "prod_status_KERING" atau "prod:{STATUS}") agar selalu ≤30, ATAU lebih baik:
2. Tambah migration baru: alter audit_logs.action menjadi string(64) atau string(100).
3. Ideal: lakukan keduanya (action pendek + kolom lebih longgar) biar aman ke depan.
4. Jangan ubah logic transisi status linear / role check kecuali perlu.
5. Pastikan update CUCI→KERING dan SIAP→DIAMBIL sukses, audit log terisi.

## Files
- app/Http/Controllers/ProductionController.php
- app/Services/AuditLogService.php (opsional: Str::limit safeguard)
- database/migrations/*_create_audit_logs_table.php JANGAN edit migration lama yang sudah deploy — buat migration baru alter column

## Acceptance
- Tidak ada SQLSTATE 22001 saat update status ke KERING / DIAMBIL
- Redirect success, production_status di orders berubah, production_status_logs & audit_logs terisi

Kerjakan minimal diff. Jelaskan perubahan + cara test.
```

---

## T2 — Bug Laporan Keuangan = 0 (#5)

```
Task T2 | GitHub #5 | Linear KIL-14

## Bug
Menu Laporan Keuangan selalu menampilkan nominal 0 padahal sudah ada transaksi.

## Investigasi wajib (baca dulu)
1. app/Http/Controllers/Finance/FinancialReportController.php
2. app/Services/FinancialReportService.php DAN app/Services/Finance/FinancialReportService.php — cek mana yang di-inject container
3. app/Services/Finance/JournalService.php + app/Observers/OrderObserver.php — apakah order paid membuat journal?
4. resources/views/finance/reports*.blade.php — apakah baca key array yang salah?
5. Sample data: orders payment_status=paid, journals + journal_lines

## Yang harus dicapai
- Laporan (income statement / trial balance / balance sheet sesuai UI) menampilkan angka dari journal yang valid
- Jika root cause-nya order tidak pernah post journal: perbaiki observer/service agar POS paid membuat journal seimbang
- Jika root cause filter branch/year/month: perbaiki query
- Jangan hardcode angka dummy

## Acceptance
Dengan minimal 1 order paid + journal entries, laporan ≠ 0 untuk periode yang sesuai.

Minimal diff. Jelaskan root cause aktual yang kamu temukan.
```

---

## T3 — Production filter DIAMBIL (#6)

```
Task T3 | GitHub #6 | Linear KIL-15

## Kebutuhan
Di Production, status DIAMBIL tidak punya list/filter. Saat ini index mengecualikan DIAMBIL:
Order::...->where('production_status', '!=', 'DIAMBIL')

## Lakukan
1. Tambah filter/tab status termasuk DIAMBIL (dan status lain yang sudah ada)
2. Jika filter=DIAMBIL (atau "all"), tampilkan order berstatus DIAMBIL
3. Default view boleh tetap hide DIAMBIL agar board operasional tidak penuh — tapi user harus bisa pilih filter Diambil
4. Update resources/views/production/index.blade.php agar UI filter jelas

## Acceptance
User bisa melihat daftar order DIAMBIL lewat filter/tab di halaman Production.
```

---

## T4 — POS Customer (#7)

```
Task T4 | GitHub #7 | Linear KIL-16

## Kebutuhan di POS (resources/views/pos/index.blade.php + POSController)
1. Modal/form tambah pelanggan baru dari POS (boleh POST ke route customers.store yang sudah ada, atau endpoint JSON kecil)
2. Hapus opsi "Walk-In" / "Pelanggan Umum (Walk-In)" dari dropdown customer
3. Dropdown customer searchable (Select2 via CDN, atau Choices.js, atau Alpine.js filter — pilih yang paling ringan dan konsisten)

## Constraint
- Jangan rusak alur store order POS
- Customer baru harus dapat branch_id yang benar (session scoped_branch_id / user branch)
- Setelah tambah customer, dropdown ter-update (reload atau append option)

## Acceptance
Kasir bisa cari customer, tambah customer baru tanpa buka CRM, tidak ada Walk-In di list.
```

---

## T5 — CRM Search (#8)

```
Task T5 | GitHub #8 | Linear KIL-17

## Kebutuhan
Halaman CRM customers (routes/web.php closure customers.index + resources/views/customers/index.blade.php):
- Tambah search bar (nama / phone / member_code)
- Query ?q= dengan pagination tetap jalan
- UX: searchable, debounced optional (form GET sudah cukup)

Minimal diff. Jangan rewrite seluruh CRM.
```

---

## T6 — CRUD Service / jenis layanan (#9)

```
Task T6 | GitHub #9 | Linear KIL-18

## Kebutuhan
CRUD Master Data jenis layanan cuci. Model Service + ServiceBranchPrice + ServicePriceHistory sudah ada.

## Lakukan
1. Halaman index list services
2. Form create/edit (nama, kategori, satuan, harga default, aktif/nonaktif)
3. Opsional: harga per cabang (ServiceBranchPrice)
4. Routes + controller tipis (jangan taruh semua logic di closure web.php jika sudah terlalu penuh — boleh ServiceController)
5. Akses: role Owner / Super_Admin / Developer (sesuaikan sidebar permission)
6. Link menu di sidebar jika belum ada

Ikuti UI existing. Seed data jangan dihapus.
```

---

## T7 — Menu Memantau Kinerja (#10)

```
Task T7 | GitHub #10 | Linear KIL-19

## Kebutuhan
Menu "Memantau Kinerja" reported not accessible / broken link.

## Fakta kode
- Route sudah ada: Route::get('/performance', [PerformanceController::class, 'index'])->name('performance.index');
- Controller + view performance/index.blade.php sudah ada

## Lakukan
1. Cek resources/views/components/sidebar.blade.php (dan topbar/bottom-nav) — perbaiki href/route label "Memantau Kinerja" agar ke route('performance.index')
2. Jika 403: sesuaikan middleware/role agar role yang berhak (Owner, dll) bisa akses
3. Jika view error: perbaiki error runtime di PerformanceController (mis. relation orders di User)
4. Jangan rebuild fitur dari nol kecuali memang kosong total

## Acceptance
Klik menu Memantau Kinerja → halaman performance tampil tanpa 404/500.
```

---

## Setelah tiap task

Minta AI:
```
Setelah selesai:
1. List file yang diubah
2. Cara test manual (step by step)
3. Saran commit message 1 baris
```

Lalu di GitHub close issue terkait + di Linear set status Done.
