# T4 — POS Customer (#7) Implementation Plan

## Repo Research Conclusion

### Current State (Sudah Ada)
1. **Modal tambah pelanggan dari POS** — ✅ SUDAH TERIMPLEMENTASI
   - Modal UI: [pos/index.blade.php#L312-L359](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/pos/index.blade.php#L312-L359)
   - Frontend JS: `openAddCustomerModal()` dan `saveNewCustomer()`: [pos/index.blade.php#L519-L558](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/pos/index.blade.php#L519-L558)
   - Route `pos.customers.store`: [web.php#L65](file:///d:/Project/IstanaLaundryManagementSystem/routes/web.php#L65)
   - Controller `storeCustomer()` dengan branch_id dari session: [POSController.php#L75-L110](file:///d:/Project/IstanaLaundryManagementSystem/app/Http/Controllers/POSController.php#L75-L110)
   - Setelah save: customer langsung di-push ke array dan auto-selected via `selectCustomer()`

2. **Searchable customer dropdown** — ✅ SUDAH TERIMPLEMENTASI
   - Menggunakan **Alpine.js filter pattern** (ringan, tidak perlu library eksternal)
   - UI Search + dropdown hasil: [pos/index.blade.php#L26-L50](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/pos/index.blade.php#L26-L50)
   - Fungsi `filteredCustomers()`: [pos/index.blade.php#L496-L500](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/pos/index.blade.php#L496-L500)
   - Tidak perlu Select2 / Choices.js — sudah native & konsisten dengan stack Alpine+Tailwind yang dipakai project

3. **Walk-In capability (yang harus dihapus)** — ⚠️ MASIH ADA
   - `customer_id` masih **nullable** di validasi `POSController::store()`: [POSController.php#L124](file:///d:/Project/IstanaLaundryManagementSystem/app/Http/Controllers/POSController.php#L124)
   - `confirmCheckout()` tidak mengecek apakah customer dipilih: [pos/index.blade.php#L603-L615](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/pos/index.blade.php#L603-L615)
   - Order bisa dibuat tanpa customer (NULL customer_id = walk-in implisit)
   - Tidak ada customer record bernama "Walk-In" di seeder, NULL = walk-in

## Target Acceptance
Kasir bisa **cari customer** (✅ done), **tambah customer baru tanpa buka CRM** (✅ done), **tidak bisa walk-in / order tanpa customer** (❌ belum).

---

## Files & Modules to Edit

| # | File | Change | Scope |
|---|------|--------|-------|
| 1 | `app/Http/Controllers/POSController.php` | `store()`: Ubah `customer_id` dari `nullable` → `required` | Backend validation |
| 2 | `resources/views/pos/index.blade.php` | `confirmCheckout()`: Tambah cek `customerId` harus ada sebelum lanjut | Frontend validation |
| 3 | `resources/views/pos/index.blade.php` | Ubah teks empty result "Pelanggan tidak ditemukan." → tambah hint untuk tambah pelanggan | UX |
| 4 | `app/Http/Controllers/POSController.php` | `index()`: Tambahkan defensive filter agar customer dengan nama mengandung "Walk-In" / "Umum" tidak masuk list (jika ada data lama) | Defensive |

---

## Step-by-Step Modifications

### Step 1: Backend — Ubah customer_id menjadi REQUIRED di POS store
**File:** [POSController.php](file:///d:/Project/IstanaLaundryManagementSystem/app/Http/Controllers/POSController.php)

**Lokasi:** `store()` method, validator rules (baris ~124)
```
SEBELUM: 'customer_id' => 'nullable|exists:customers,id',
SETELAH: 'customer_id' => 'required|exists:customers,id',
```

**Reason:** Mencegah order dibuat tanpa customer terdaftar. Walk-in dihapus dengan memaksa setiap order punya `customer_id` yang valid.

**Risk Mitigation:** Cek test suite — test yang ada `POSAndProductionTest::test_pos_can_create_order_successfully` sudah menggunakan `customer_id` → tidak akan break.

---

### Step 2: Frontend — Blokir checkout jika customer belum dipilih
**File:** [pos/index.blade.php](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/pos/index.blade.php)

**Lokasi:** Fungsi `confirmCheckout()` (baris ~603-615)
- Sebelum pengecekan cart / paid_amount, tambahkan:
  - Jika `!this.customerId` → tampilkan toast error "Pilih pelanggan terlebih dahulu atau daftar pelanggan baru." dan return.

**Reason:** Frontend guard agar cashier tidak sempat buka modal konfirmasi sebelum pilih customer. Backend (Step 1) sebagai last line of defense.

---

### Step 3: UX — Perbaiki empty-state message customer search
**File:** [pos/index.blade.php](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/pos/index.blade.php)

**Lokasi:** Template x-if empty result di dropdown (baris ~46-48)
```
SEBELUM: "Pelanggan tidak ditemukan."
SETELAH: "Pelanggan tidak ditemukan. Klik tombol + untuk mendaftarkan pelanggan baru."
```

**Reason:** Mengarahkan perilaku kasir — karena walk-in tidak lagi diperbolehkan, maka jalan satu-satunya adalah tambah customer baru.

---

### Step 4 (Defensive): Filter customer list di controller dari entry "Walk-In" potensial
**File:** [POSController.php](file:///d:/Project/IstanaLaundryManagementSystem/app/Http/Controllers/POSController.php)

**Lokasi:** `index()` method, query `$customers` (baris ~57)
```php
// Tambahkan ->whereNotIn filter:
$customers = Customer::where('branch_id', $branchId)
    ->where(function($q) {
        $q->where('name', 'NOT LIKE', '%Walk-In%')
          ->where('name', 'NOT LIKE', '%Pelanggan Umum%');
    })
    ->get();
```

**Reason:** Jika ada data customer lama (migrasi / seeder sebelumnya) yang memang nama-nya "Walk-In", di POS list akan disembunyikan. CRM masih bisa lihat. Ini safety-net.

---

## Potential Dependencies / Considerations
- **Branch ID**: `storeCustomer()` sudah mengambil dari `session('scoped_branch_id') ?? Auth::user()->branch_id` — konsisten dengan ambil branch_id di `index()` dan `store()`. Tidak ada issue. ✅
- **Loyalty Points**: Karena `customer_id` sekarang required, code path `if ($customer)` di store() akan selalu terpenuhi. `points_used` validation tetap aman karena sudah ada `if ($customer && !empty($data['points_used']))`.
- **Invoice / Dashboard views fallback "Walk-In"** (ditemukan di grep): view lain seperti `invoices/show.blade.php`, `production/index.blade.php`, `orders/index.blade.php` masih punya `?? 'Walk-In'`. **Out of scope T4** — T4 hanya spesifik di POS customer dropdown. Namun perlu dicatat bahwa order historical yang `customer_id = NULL` akan tetap tampil "Walk-In" di halaman lain (tidak merusak).

## Risk Handling
| Risiko | Mitigasi |
|--------|----------|
| Order lama dengan customer_id NULL tidak bisa direplikasi test-nya | Tugas ini memang menghapus walk-in, jadi perilaku baru adalah tidak bisa. Test yang mengasumsikan nullable perlu diupdate terpisah jika ada. |
| Validasi required customer_id menyalahi Design Doc (yang menyatakan NULLABLE = walk-in) | Task #7 secara eksplisit menghapus walk-in. Ini adalah perubahan requirement yang disengaja. |
| CSRF token tidak terbaca di saveNewCustomer() | Layout `app.blade.php` baris 13 sudah punya `<meta name="csrf-token">`, dan saveNewCustomer membacanya dari `meta[name="csrf-token"]` → aman ✅ |

## Acceptance Test (Manual)
1. Buka `/pos` sebagai kasir
2. Coba langsung checkout tanpa pilih customer → harus muncul toast error "Pilih pelanggan terlebih dahulu..."
3. Cari customer yang tidak ada → muncul pesan "Klik tombol + untuk mendaftarkan..."
4. Klik tombol + person_add → modal terbuka
5. Isi nama + no hp, submit → customer tersimpan, dropdown otomatis reload & customer terpilih
6. Tambah service → checkout → order berhasil tersimpan dengan `customer_id` benar (tidak NULL)
7. Customer baru memiliki `branch_id` yang sesuai dengan scoped_branch session

**Run test command (setelah perubahan):**
```
vendor\bin\phpunit --filter POSAndProductionTest
```
