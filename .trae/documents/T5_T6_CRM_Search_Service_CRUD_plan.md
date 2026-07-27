# T5 — CRM Search (#8) + T6 — CRUD Service / Jenis Layanan (#9) Implementation Plan

## Repo Research Conclusion

### T5 — CRM Search
- **Customers index route**: masih closure di [routes/web.php](file:///d:/Project/IstanaLaundryManagementSystem/routes/web.php) (baris ~89-99) dengan `Customer::query()->orderBy('created_at','desc')->simplePaginate(20)` → **belum ada search query**.
- **View [customers/index.blade.php](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/customers/index.blade.php)**: Menggunakan Alpine.js + Blade, component `x-page-header` + `x-card`, table dengan filter kategori (semua/points>0/member aktif) + tab loyalty point. **Belum ada input search bar**.
- **Kolom searchable di Design Doc**: Customer punya `name`, `phone`, `member_code` (unique) — tiga target pencarian utama.
- **Pagination**: `simplePaginate(20)` — search perlu preserve query param via `appends('q')`.
- **Pattern existing reference**: [orders/index.blade.php](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/orders/index.blade.php) sudah punya filter bar pattern (GET form di atas table).

### T6 — CRUD Service
- **Model [Service.php](file:///d:/Project/IstanaLaundryManagementSystem/app/Models/Service.php)**: Fillable = `name, type, unit, base_price, est_duration_hours, description, is_active` (tambah `is_active` belum ada di #[Fillable] tapi ada di casts). **TIDAK pakai BranchScoped** — Service = data global shared antar cabang (sesuai Design Doc §4.1.1: Service Master Data global, override harga per cabang via ServiceBranchPrice).
- **Model [ServiceBranchPrice.php](file:///d:/Project/IstanaLaundryManagementSystem/app/Models/ServiceBranchPrice.php)**: Fillable = `service_id, branch_id, price, is_active`. PAKAI `BranchScoped`.
- **Model [ServicePriceHistory.php](file:///d:/Project/IstanaLaundryManagementSystem/app/Models/ServicePriceHistory.php)**: Fillable = `service_id, branch_id, old_price, new_price, changed_by, changed_at`. PAKAI `BranchScoped`.
- **Seeder [ServiceSeeder.php](file:///d:/Project/IstanaLaundryManagementSystem/database/seeders/ServiceSeeder.php)**: 10 pre-defined services (Cuci Kiloan Reguler, Kilat, Express, etc.) + 2 branch price override untuk Cabang Dr. Sutomo. Seed data tidak boleh dihapus.
- **Role + Permission**: Permission `services.manage` SUDAH ADA di [RolePermissionSeeder.php](file:///d:/Project/IstanaLaundryManagementSystem/database/seeders/RolePermissionSeeder.php#L35) diberikan ke: **Developer, Owner, Super_Admin** (sesuai SRS). Branch_Admin TIDAK punya services.manage (hanya manage cabang sendiri tapi tidak define master layanan).
- **Sidebar**: BELUM ADA menu "Jenis Layanan" / "Service" di [sidebar.blade.php](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/components/sidebar.blade.php). Terletak di section "Management", setelah Promotions / sebelum Inventory adalah lokasi ideal.
- **UI Patterns existing (untuk reuse)**:
  - Header & card: `x-page-header` + `x-card` component
  - Table: `<thead class="table-head">` pattern
  - CRUD Modal: Alpine `x-data` dengan `showCreateModal` / `showEditModal` boolean, wire-up dengan `<x-modal>` component (lihat pattern POS add customer atau assets).
  - Buttons: `x-primary-button` (submit), `x-secondary-button` (cancel/back)
  - Forms: `x-input-label` + `x-text-input` + `x-input-error` bundle
  - Active toggle: checkbox / switch `is_active`
- **Akses route policy**: Karena Service global scope, role access guard di route group middleware `role:Developer|Owner|Super_Admin` adalah yang paling simple & aman.

---

## Files & Modules to Edit

### 🔴 T5 — CRM Search (Minimal diff)
| # | File | Change | Scope |
|---|------|--------|-------|
| T5.1 | `routes/web.php` | Customers closure index: tambah `when($q, search)` pada nama/phone/member_code + `appends('q')` di pagination link | Route/Query |
| T5.2 | `resources/views/customers/index.blade.php` | Tambah search bar `<form method="GET">` di atas tabs/table, input name="q", value="{{ request('q') }}", submit button + reset link | UX |

### 🟠 T6 — CRUD Service (Feature baru, minimal 1 controller)
| # | File | Change | Scope |
|---|------|--------|-------|
| T6.1 | `app/Http/Controllers/ServiceController.php` | **NEW** — `index()`, `store()`, `update(Service $service)`, `toggleActive(Service $service)`; include: validation, upsert ServiceBranchPrice (if branch override provided), tulis ServicePriceHistory **on base_price / branch price update** | Backend CRUD |
| T6.2 | `routes/web.php` | **Tambah route group** (prefix `/services`, middleware `role:Developer|Owner|Super_Admin`, name `services.*`) — index GET, store POST, update PATCH/PUT, toggle-active PATCH. **JANGAN taruh logic di closure** | Route |
| T6.3 | `resources/views/services/index.blade.php` | **NEW** — Pakai `x-page-header` + "Tambah Layanan" button. Table: ID, Nama, Tipe, Satuan, Harga Default, Est Durasi, Aktif, Actions (Edit/On-Off). Alpine modal for Create & Edit, inside modal ada field base_price + optional "Harga Per Cabang" collapsible (list branch + input harga). | View |
| T6.4 | `resources/views/components/sidebar.blade.php` | Tambah menu **"Jenis Layanan"** di section Management (antara Promotions dan Inventory), guard `hasAnyRole(['Developer', 'Owner', 'Super_Admin'])`, icon `miscellaneous_services` (material-symbols-outlined) | Navigation |
| T6.5 | `app/Models/Service.php` | Tambahkan `is_active` ke dalam `#[Fillable]` — ada di casts tapi belum di Fillable attribute list → fix supaya Mass Assignment jalan | Model |

---

## Step-by-Step Modifications

### PART A — T5 CRM Search (Done dulu, lebih cepat)

#### Step T5.1: Route closure + Search query
**File:** [web.php](file:///d:/Project/IstanaLaundryManagementSystem/routes/web.php) ~ baris 89-99 customers.index

Lokasi sebelum `return view('customers.index')`:
```php
$q = $request->query('q', '');
$filterCategory = $request->query('category', 'all');

$customersQuery = Customer::with('branch')
    ->when($q !== '', function ($query) use ($q) {
        $like = "%{$q}%";
        $query->where(function ($sub) use ($like, $q) {
            $sub->where('name', 'LIKE', $like)
                ->orWhere('phone', 'LIKE', $like)
                ->orWhere('member_code', 'LIKE', $like);
        });
    })
    // — keep existing loyalty/member filters below as-is, just rename var to $customersQuery
    ->when($filterCategory === 'has_points', fn($q) => $q->where('loyalty_points', '>', 0))
    ->when($filterCategory === 'active_members', fn($q) => $q->whereNotNull('member_code')->where('is_member_active', true))
    ->orderBy('created_at', 'desc');

$customers = $customersQuery->simplePaginate(20)->withQueryString(); // preserve q + category via withQueryString()
```

#### Step T5.2: Customer view — Add search bar
**File:** [customers/index.blade.php](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/customers/index.blade.php)

**Insert location**: Setelah `x-page-header` slot `description`, SEBELUM `<x-card>` pertama (tabs loyalty). Bentuk:
```html
<form method="GET" action="{{ route('customers.index') }}" class="mb-4 flex flex-col sm:flex-row gap-2">
    <div class="flex-1 relative">
        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
        <input type="text" name="q" value="{{ request('q') }}" 
               placeholder="Cari nama, no HP, atau kode member..." 
               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
    </div>
    <button type="submit" class="px-5 py-2.5 bg-primary text-white rounded-xl font-semibold text-sm hover:bg-primary-hover transition-all active:scale-95">
        Cari
    </button>
    @if(request('q') || request('category'))
        <a href="{{ route('customers.index') }}" class="px-5 py-2.5 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-sm transition-colors text-center">
            Reset
        </a>
    @endif
</form>
```

---

### PART B — T6 CRUD Service (Lebih banyak file)

#### Step T6.1: Fix Fillable Service model
**File:** [Service.php](file:///d:/Project/IstanaLaundryManagementSystem/app/Models/Service.php#L10)

```
SEBELUM: #[Fillable(['name', 'type', 'unit', 'base_price', 'est_duration_hours', 'description'])]
SETELAH: #[Fillable(['name', 'type', 'unit', 'base_price', 'est_duration_hours', 'description', 'is_active'])]
```

#### Step T6.2: Buat ServiceController
**File BARU:** `app/Http/Controllers/ServiceController.php`

**4 Methods:**

1. **`index(Request $request)`**
   - `$services = Service::with('branchPrices.branch')->orderBy('name')->paginate(15);`
   - `$branches = Branch::orderBy('name')->get();` (untuk harga per cabang di modal)
   - `return view('services.index', compact('services', 'branches'));`

2. **`store(Request $request)`** → Validasi + create + branch prices + history
   ```php
   $valid = $request->validate([
       'name' => 'required|string|max:255|unique:services,name',
       'type' => 'required|in:kilogram,satuan,kategori',
       'unit' => 'required|string|max:10',
       'base_price' => 'required|numeric|min:0',
       'est_duration_hours' => 'required|integer|min:1',
       'description' => 'nullable|string',
       'is_active' => 'boolean',
       'branch_prices' => 'nullable|array', // key = branch_id, value = price numeric
   ]);
   ```
   - DB::transaction create Service
   - Jika ada `branch_prices[]` → loop `ServiceBranchPrice::updateOrCreate(['service_id','branch_id'],['price','is_active'=>true])` → untuk tiap entry tulis `ServicePriceHistory` (old=0, new=price, changed_by=Auth::id())
   - Redirect back with flash `success`.

3. **`update(Request $request, Service $service)`**
   - Unique name rule di-ignore untuk id sendiri: `'name' => 'required|string|max:255|unique:services,name,'.$service->id`
   - Jika `$request->base_price != $service->base_price` → tulis history (old_price = lama, new=baru, branch_id NULL untuk global price change, changed_by + changed_at)
   - Branch prices upsert (jika ada) → bandingkan old vs new, tulis history jika beda.
   - Redirect with flash.

4. **`toggleActive(Request $request, Service $service)`**
   - Flip `$service->is_active = !$service->is_active;`
   - Save, redirect back.

#### Step T6.3: Routes services.* di web.php
**File:** [web.php](file:///d:/Project/IstanaLaundryManagementSystem/routes/web.php) — Tambahkan SETELAH route group promotions atau sebelum inventory, di grup auth+sandardize middleware.

```php
// ===== MASTER DATA: SERVICES =====
Route::middleware('role:Developer|Owner|Super_Admin')->prefix('services')->name('services.')->group(function () use ($router) {
    Route::get('/', [ServiceController::class, 'index'])->name('index');
    Route::post('/', [ServiceController::class, 'store'])->name('store');
    Route::match(['put', 'patch'], '/{service}', [ServiceController::class, 'update'])->name('update');
    Route::patch('/{service}/toggle-active', [ServiceController::class, 'toggleActive'])->name('toggle-active');
});
```

**Jangan lupa use import di atas web.php**: `use App\Http\Controllers\ServiceController;`

#### Step T6.4: View Services Index (NEW FILE)
**File BARU:** `resources/views/services/index.blade.php`

**Layout structure (follow existing convention — mirror customers/orders):**
1. Extend `layouts.app`
2. Section `content`:
   - `<x-page-header title="Jenis Layanan Laundry" description="Kelola master data jenis layanan, harga default, dan override harga per cabang.">` with `@slot('actions')` → button "Tambah Layanan" (trigger modal create).
   - `<x-card>` → table dengan kolom: Nama, Tipe, Satuan, Harga Default (Rp formatted), Est Durasi (jam), Aktif (x-badge success/slate), Actions row:
     - Toggle aktif (mini-form PATCH `services.toggle-active`)
     - Edit icon (buka modal edit Alpine)
   - Di BAWAH card: pagination links `$services->links()`
3. **Create modal** (id createServiceModal) — dibuka dari x-page-header actions.
   - Inside `x-modal` component:
     - Input: name, type (dropdown kilogram/satuan/kategori), unit (text pendek), base_price, est_duration_hours, description textarea, is_active checkbox.
     - Optional collapse "Override Harga Per Cabang" → loop branches, input number dengan name `branch_prices[{{$branch->id}}]` label "[{{code}}] {{name}}".
4. **Edit modal** (id editServiceModal) — same fields, terisi data dari `$service` yang diklik. Use `@method('PATCH')` + `action="{{ route('services.update', '__ID__') }}"` (diisi dynamic via Alpine x-bind atau generate inline di row button).
   - Strategi Alpine paling minimal: set attributes via `@click="editService = {{ $service->toJson() }}"` lalu bind value dengan Alpine `x-model`.

#### Step T6.5: Sidebar — Tambah menu "Jenis Layanan"
**File:** [sidebar.blade.php](file:///d:/Project/IstanaLaundryManagementSystem/resources/views/components/sidebar.blade.php)

**Lokasi insert:** Setelah menu "Promosi / Kupon" (baris ~121 @endif), SEBELUM comment `<!-- Inventory & Procurement -->` (baris ~133).

```blade
<!-- Services (Master Jenis Layanan) -->
@if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
    <a href="{{ route('services.index') }}" @click="sidebarOpen = false"
       class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('services*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
       :title="!desktopSidebarOpen ? 'Jenis Layanan' : ''">
        <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('services*') ? '1' : '0' }};">miscellaneous_services</span>
        <span x-show="desktopSidebarOpen" class="truncate">Jenis Layanan</span>
    </a>
@endif
```

---

## Potential Dependencies / Considerations

### T5 considerations
- **`LIKE` search MySQL/MariaDB** — default case-insensitive untuk collation umum (`utf8mb4_unicode_ci`), jadi tidak perlu `LOWER()` wrapping. SQLite local test juga support LIKE wildcard.
- **XSS protection**: `request('q')` selalu di-escape di `{{ }}` Blade → aman.
- **Pagination links**: `simplePaginate(20)->withQueryString()` adalah cara paling clean Laravel untuk preserve `?q=&category=` — otomatis include semua query params. Tidak perlu `appends()` manual.

### T6 considerations
- **Service TIDAK pakai BranchScoped** — penting! Service adalah master data GLOBAL. Hanya ServiceBranchPrice yang branch-scoped. Jangan salah tambahkan trait.
- **Service uniqueness**: Nama service UNIQUE (tidak boleh ada 2 service dengan nama sama, karena shared global) → validasi `unique:services,name` benar.
- **Price history selalu dicatat**: setiap update base_price (global) atau branch_price (per cabang) harus masuk ke `ServicePriceHistory` dengan `changed_by` dan `changed_at` now.
- **Seed ServiceSeeder 10 data aman**: Create dengan `firstOrCreate` — tidak akan overwrite, ServiceController baru juga tidak menghapus data.
- **Role check**: Permission `services.manage` ada tapi yang paling sederhana untuk route group adalah `role:Developer|Owner|Super_Admin` middleware (karena Spatie Permission route middleware sudah di-bind — lihat existing route pattern HR/Finance).
- **Fallback untuk service tidak aktif**: Di POS nanti ke depannya bisa filter `is_active = true`, tapi **TIDAK di-scope T6 ini** — T6 hanya menyediakan UI aktif/nonaktif toggle.

## Risk Handling

| Risiko | Mitigasi |
|--------|----------|
| T5: Query LIKE tanpa index bisa lambat untuk >10rb customer | Customer table punya pencarian kata-kata (full name), namun karena minimal spec ≤ 5k record (laundry skala UMKM) + `simplePaginate` → risiko performance aman. Di task production upgrade nanti bisa tambah index `name`, `phone`, `member_code`. |
| T6: ServicePriceHistory `branch_id` nullable vs. ServiceBranchPrice history | Untuk base_price (global) → history branch_id = NULL dengan comment/konvensi "harga global". Di UI history display (bukan scope T6) nanti bisa dibedakan. Kolom `branch_id` di ServicePriceHistory TIDAK nullable di migration. **Solusi**: T6 insert history branch_price override per branch saja; base_price global perubahan tidak tulis ServicePriceHistory? TIDAK BOLEH — Design Doc §4.3.4 semua price changes harus dicatat. **Keputusan**: Di schema history, pakai branch_id = session scoped_branch_id (cabang user yang sedang login saat mengubah global price) sebagai fallback, atau pilih branch 1 jika tidak ada. Buka file migration dulu untuk memastikan — jika memang foreign key NOT NULL, maka gunakan scoped branch. |
| ServiceSeeder branch price untuk branch SUT (Dr. Sutomo) dan branch default (SMP, dll.) jika tidak ada override | POS harus fallback ke base_price jika branch active price tidak ada. Logika ini TIDAK di T6 (berada di POS Controller). Tapi kita TIDAK sentuh POS di T6, aman. |
| Mass Assignment `is_active` tidak bisa diisi | Step T6.1 menambahkan `is_active` ke Fillable — mitigated. |
| Form edit price string vs decimal formatting | Di view, input `type="number" step="100"` (minimal 100 rupiah) — format integer sederhana, hindari float formatting locale issue. `(int)` cast di controller sebelum save — atau biarkan Laravel handle numeric validation. |

---

## Acceptance Test Manual

### 🧪 T5 — CRM Search
Buka `/customers` sebagai Owner/CS_Marketing:
1. Input search bar di atas table: ketik sebagian nama customer (mis. "Budi") → submit → tampil customers yg nama mengandung Budi.
2. Ketik no HP (mis. "0812") → hasil mengandung nomor itu.
3. Ketik kode member (mis. "IL2026" prefix) → member dengan kode itu muncul.
4. Link pagination halaman 2 klik → URL tetap ada `?q=xxx&category=yyy`.
5. Teks search + filter category dikombinasikan (tab loyalty) → kedua param tetap ter-apply.
6. Klik "Reset" → kembali ke `/customers` tanpa query, menampilkan semua.

### 🧪 T6 — CRUD Service
Sebelum mulai: jalankan `php artisan db:seed --class=ServiceSeeder` jika belum ada data.
1. Login sebagai **Developer** → di sidebar Management section muncul menu **"Jenis Layanan"**, icon `miscellaneous_services`.
2. Klik menu → table list 10 service dari seeder muncul.
3. Klik **"Tambah Layanan"**:
   - Isi: nama = "Cuci Helm Premium", type = kategori, unit = pcs, harga = 35000, durasi = 72, deskripsi = "Cuci helm premium deep clean" → submit.
   - Row baru muncul di table, is_active=Aktif.
4. Di row tersebut, klik tombol Edit (pencil icon):
   - Edit nama jadi "Cuci Helm Premium Plus", base_price jadi 40000, unfold Harga Per Cabang, isi Dr. Sutomo (SUT branch) dengan harga 42000 → Save.
   - Reload table → harga default 40000. Check DB: ServiceBranchPrice ada entry 42000 utk service_id itu, ServicePriceHistory mencatat old 0 / new 42000 / branch_id SUT / changed_by.
5. Toggle aktif (switch icon power): service jadi non-aktif, badge berubah dari success → slate.
6. Login sebagai **Branch_Admin**: menu "Jenis Layanan" TIDAK muncul di sidebar (access denied jika ketik URL `/services` langsung).
7. Login sebagai **Cashier**: sama — tidak bisa akses.

---

## Verification Commands (Setelah implementasi)
```powershell
# Syntax check PHP files (new + edited)
php -l app/Http/Controllers/ServiceController.php
php -l app/Models/Service.php
php -l routes/web.php

# Lint blade via get diagnostics after edits
```
