{{-- Mobile & Tablet Dedicated Menu Sheet (Halaman Menu Mobile) --}}
<div x-show="mobileMenuOpen"
     x-transition:enter="transition ease-out duration-300 transform"
     x-transition:enter-start="opacity-0 translate-y-full"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200 transform"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-full"
     @keydown.escape.window="mobileMenuOpen = false"
     class="fixed inset-0 z-[9999] lg:hidden flex flex-col overflow-hidden"
     style="background: var(--nm-bg);"
     x-cloak>

    {{-- Top Header --}}
    <div class="px-5 py-4 flex items-center justify-between shrink-0" style="background: var(--nm-surface); border-bottom: 1px solid rgba(0,0,0,0.05);">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 flex items-center justify-center text-white font-black text-xl shrink-0" style="border-radius: var(--radius-md); background: linear-gradient(135deg, #FF6600, #FF8533); box-shadow: var(--nm-convex);">
                <img alt="Istana Laundry Logo" class="w-6 h-6 object-contain" src="{{ asset('images/logo.webp') }}"/>
            </div>
            <div>
                <h2 class="text-base font-black font-display tracking-tight" style="color: var(--text-primary);">Istana Laundry</h2>
                <p class="text-[9px] font-extrabold uppercase tracking-widest text-primary">Enterprise Menu</p>
            </div>
        </div>

        <button @click="mobileMenuOpen = false" class="w-10 h-10 flex items-center justify-center cursor-pointer rounded-2xl active:scale-95 transition-all" style="background: var(--nm-surface-high); box-shadow: var(--nm-convex);">
            <span class="material-symbols-outlined text-xl" style="color: var(--text-primary);">close</span>
        </button>
    </div>

    {{-- Menu Scrollable Body --}}
    <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6 pb-28">
        
        {{-- User Profile Card --}}
        <div class="nm-card p-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-11 h-11 rounded-2xl text-white font-black font-display text-sm flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #FF6600, #FF8533); box-shadow: var(--nm-convex);">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <h3 class="text-sm font-extrabold truncate" style="color: var(--text-primary);">{{ auth()->user()->name }}</h3>
                    <p class="text-2xs font-semibold truncate" style="color: var(--text-tertiary);">{{ auth()->user()->roles->first()?->name ?? 'User' }}</p>
                </div>
            </div>
            <span class="nm-badge text-primary shrink-0">
                {{ auth()->user()->branch?->name ?? 'Global' }}
            </span>
        </div>

        {{-- Branch Scope Switcher (For Multi-Branch Roles) --}}
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
            <div class="nm-card-sm p-3">
                <span class="text-[10px] font-extrabold uppercase tracking-wider block mb-1.5" style="color: var(--text-tertiary);">Pilih Cabang (Scope Active)</span>
                <form action="{{ route('switch-branch') }}" method="POST">
                    @csrf
                    <select name="branch_id" onchange="this.form.submit()" class="nm-input nm-select text-xs font-bold">
                        <option value="" {{ !session('scoped_branch_id') ? 'selected' : '' }}>Global (Semua Cabang)</option>
                        @foreach(\App\Models\Branch::orderBy('name')->get() as $br)
                            <option value="{{ $br->id }}" {{ session('scoped_branch_id') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        @endif

        {{-- Modules Grid Section --}}
        <div>
            <h4 class="text-2xs font-extrabold uppercase tracking-widest mb-3 px-1" style="color: var(--text-tertiary);">Modul Aplikasi</h4>
            <div class="grid grid-cols-2 gap-3">
                
                {{-- Dashboard --}}
                <a href="/dashboard" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">dashboard</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Dashboard</span>
                </a>

                {{-- POS Kasir --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
                <a href="/pos" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all" style="background: rgba(255,102,0,0.06);">
                    <div class="w-10 h-10 rounded-2xl bg-primary text-white flex items-center justify-center shadow-md">
                        <span class="material-symbols-outlined text-2xl">point_of_sale</span>
                    </div>
                    <span class="text-xs font-black text-primary">Kasir (POS)</span>
                </a>
                @endif

                {{-- Semua Transaksi --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
                <a href="/orders" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-sky-500/10 text-sky-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">receipt_long</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Transaksi</span>
                </a>
                @endif

                {{-- Produksi --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Workshop_Staff']))
                <a href="/production" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">dry_cleaning</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Produksi & QR</span>
                </a>
                @endif

                {{-- CRM & Pelanggan --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'CS_Marketing']))
                <a href="/customers" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-pink-500/10 text-pink-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">groups</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Pelanggan & CRM</span>
                </a>
                @endif

                {{-- HR & Payroll --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/hr" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-purple-500/10 text-purple-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">badge</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">HR & Payroll</span>
                </a>
                @endif

                {{-- Inventory --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin']))
                <a href="/inventory" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">inventory_2</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Inventory Stok</span>
                </a>
                @endif

                {{-- Procurement --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance', 'Branch_Admin']))
                <a href="/procurement" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-teal-500/10 text-teal-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">shopping_cart</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Procurement PO</span>
                </a>
                @endif

                {{-- Keuangan & Ledger --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/finance/journals" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Jurnal Keuangan</span>
                </a>

                <a href="/finance/reports" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">analytics</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Laporan Keuangan</span>
                </a>
                @endif

                {{-- Fixed Assets --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance', 'Branch_Admin']))
                <a href="/assets" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-slate-500/10 text-slate-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">precision_manufacturing</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Fixed Assets</span>
                </a>
                @endif

                {{-- Operational Expenses --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance', 'Branch_Admin']))
                <a href="/finance/operational-expenses" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500/10 text-rose-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Beban Operasional</span>
                </a>
                @endif

                {{-- Layanan & Promosi --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'CS_Marketing']))
                <a href="/services" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">styler</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Layanan Cucian</span>
                </a>

                <a href="/promotions" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">campaign</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Manajemen Promo</span>
                </a>
                @endif

                {{-- Rekap Shift Closing --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
                <a href="/shifts" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">history</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Rekap Shift</span>
                </a>
                @endif

                {{-- Manajemen Cabang & Staf --}}
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <a href="/branches" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-sky-500/10 text-sky-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">storefront</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Cabang ERP</span>
                </a>

                <a href="/users" @click="mobileMenuOpen = false" class="nm-card p-3.5 flex flex-col items-center justify-center text-center gap-2 hover:scale-[1.02] active:scale-95 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-teal-500/10 text-teal-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-2xl">manage_accounts</span>
                    </div>
                    <span class="text-xs font-extrabold" style="color: var(--text-primary);">Manajemen Staf</span>
                </a>
                @endif

            </div>
        </div>
    </div>

    {{-- Bottom Action Controls Bar --}}
    <div class="px-5 py-4 flex items-center justify-between gap-3 shrink-0" style="background: var(--nm-surface); border-top: 1px solid rgba(0,0,0,0.05);">
        {{-- Dark Mode Toggle --}}
        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="nm-btn nm-btn-sm flex-1">
            <span class="material-symbols-outlined text-lg text-amber-500" x-show="!darkMode">dark_mode</span>
            <span class="material-symbols-outlined text-lg text-amber-400" x-show="darkMode" x-cloak>light_mode</span>
            <span x-text="darkMode ? 'Mode Terang' : 'Mode Gelap'"></span>
        </button>

        {{-- Logout Button --}}
        <form method="POST" action="{{ route('logout') }}" class="flex-1">
            @csrf
            <button type="submit" class="nm-btn nm-btn-danger nm-btn-sm w-full">
                <span class="material-symbols-outlined text-lg">logout</span>
                Keluar
            </button>
        </form>
    </div>
</div>
