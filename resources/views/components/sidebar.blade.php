<aside class="h-screen flex flex-col fixed left-0 top-0 bg-white dark:bg-slate-900 border-r border-surface-outline/30 dark:border-slate-800 z-50 transition-all duration-300 ease-in-out"
       :class="{ 
           'w-72': desktopSidebarOpen, 
           'w-20': !desktopSidebarOpen,
           'translate-x-0': sidebarOpen, 
           '-translate-x-full lg:translate-x-0': !sidebarOpen 
       }"
       id="sidebar-nav">
    <div class="flex flex-col h-full py-6 overflow-hidden">
        <!-- Brand / Header -->
        <div class="px-4 mb-4">
            <div class="flex items-center justify-between min-h-[44px]">
                <div class="flex items-center gap-3 overflow-hidden" x-show="desktopSidebarOpen" x-transition.opacity>
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-primary to-amber-500 flex items-center justify-center text-white font-black font-display text-xl shrink-0 shadow-md3-2">
                        <img alt="Istana Laundry Logo" class="w-6 h-6 object-contain" src="{{ asset('images/logo.webp') }}"/>
                    </div>
                    <div class="truncate">
                        <h1 class="text-base font-black font-display text-slate-900 dark:text-white tracking-tight leading-none">Istana Laundry</h1>
                        <p class="text-[9px] font-extrabold font-sans text-primary uppercase tracking-widest mt-1">Enterprise Suite</p>
                    </div>
                </div>

                <!-- Mini Logo when collapsed -->
                <div class="mx-auto" x-show="!desktopSidebarOpen" x-transition.opacity>
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-primary to-amber-500 flex items-center justify-center text-white font-black font-display text-xl shadow-md3-2" title="Istana Laundry Enterprise">
                        <img alt="Istana Laundry Logo" class="w-6 h-6 object-contain" src="{{ asset('images/logo.webp') }}"/>
                    </div>
                </div>

                <!-- Close button for mobile -->
                <button @click="sidebarOpen = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 md:hidden cursor-pointer rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 transition-colors">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>

            <!-- Branch Scope Switcher inside Sidebar Header -->
            <div class="mt-3.5" x-show="desktopSidebarOpen" x-transition.opacity>
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                    <form action="{{ route('switch-branch') }}" method="POST">
                        @csrf
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined text-primary text-sm absolute left-3 pointer-events-none">storefront</span>
                            <select name="branch_id" onchange="this.form.submit()"
                                    class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/80 rounded-xl text-xs font-bold pl-8 pr-7 py-2 text-slate-800 dark:text-slate-200 outline-none focus:ring-2 focus:ring-primary/20 cursor-pointer shadow-2xs transition-all appearance-none truncate">
                                <option value="">Global (Semua Cabang)</option>
                                @foreach(\App\Models\Branch::orderBy('name')->get() as $br)
                                    <option value="{{ $br->id }}" {{ session('scoped_branch_id') == $br->id ? 'selected' : '' }}>
                                        {{ $br->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined text-slate-400 text-sm absolute right-2.5 pointer-events-none">expand_more</span>
                        </div>
                    </form>
                @else
                    <div class="w-full bg-primary/10 border border-primary/20 rounded-xl text-xs font-bold px-3 py-2 text-primary flex items-center gap-2 truncate">
                        <span class="material-symbols-outlined text-sm">storefront</span>
                        <span class="truncate">{{ auth()->user()->branch?->name ?? 'Cabang Utama' }}</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Desktop Toggle Button (Expand/Collapse Sidebar) -->
        <div class="px-4 mb-3 hidden md:block">
            <button @click="desktopSidebarOpen = !desktopSidebarOpen; localStorage.setItem('desktopSidebarOpen', desktopSidebarOpen)"
                    class="w-full flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-primary dark:hover:text-orange-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 rounded-xl transition-all text-xs font-bold group cursor-pointer"
                    :title="desktopSidebarOpen ? 'Ciutkan Sidebar' : 'Buka Sidebar'">
                <span class="material-symbols-outlined text-[20px] transition-transform duration-300 group-hover:scale-110"
                      :class="{ 'rotate-180': !desktopSidebarOpen }">
                    side_navigation
                </span>
                <span x-show="desktopSidebarOpen" class="truncate">Sembunyikan Sidebar</span>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 space-y-1.5 overflow-y-auto px-4 scrollbar-none">
            <!-- Dashboard -->
            <a href="/dashboard" @click="sidebarOpen = false" 
               class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('dashboard') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
               :title="!desktopSidebarOpen ? 'Dashboard' : ''">
                <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('dashboard') ? '1' : '0' }};">dashboard</span>
                <span x-show="desktopSidebarOpen" class="truncate">Dashboard</span>
            </a>

            <!-- POS (Cashier) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
                <a href="/pos" @click="sidebarOpen = false" 
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('pos*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Point of Sale' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('pos*') ? '1' : '0' }};">point_of_sale</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Point of Sale</span>
                </a>
            @endif

            <!-- Refunds -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
                <a href="/refunds" @click="sidebarOpen = false" 
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('refunds*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Refund & Pembatalan' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('refunds*') ? '1' : '0' }};">assignment_return</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Refund & Pembatalan</span>
                </a>
            @endif

            <!-- Orders (Semua Transaksi) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
                <a href="{{ route('orders.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('orders*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Semua Transaksi' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('orders*') ? '1' : '0' }};">receipt_long</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Semua Transaksi</span>
                </a>
            @endif

            <!-- Production Tracking -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Workshop_Staff']))
                <a href="/production" @click="sidebarOpen = false" 
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('production*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Production' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('production*') ? '1' : '0' }};">precision_manufacturing</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Production</span>
                </a>
            @endif

            <!-- Performance Monitoring -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Finance']))
                <a href="{{ route('performance.index') }}" @click="sidebarOpen = false" 
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('performance*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Memantau Kinerja' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('performance*') ? '1' : '0' }};">insights</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Memantau Kinerja</span>
                </a>
            @endif

            <!-- Section Divider -->
            <div class="pt-5 pb-1.5 px-4" x-show="desktopSidebarOpen">
                <span class="text-[10px] font-black font-sans text-slate-400 dark:text-slate-600 uppercase tracking-widest">Management</span>
            </div>

            <!-- Customers (CRM) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'CS_Marketing']))
                <a href="/customers" @click="sidebarOpen = false" 
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('customers*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'CRM & Loyalty' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('customers*') ? '1' : '0' }};">groups</span>
                    <span x-show="desktopSidebarOpen" class="truncate">CRM & Loyalty</span>
                </a>
            @endif

            <!-- Promotions -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'CS_Marketing']))
                <a href="/promotions" @click="sidebarOpen = false" 
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('promotions*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Promosi / Kupon' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('promotions*') ? '1' : '0' }};">campaign</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Promosi / Kupon</span>
                </a>
            @endif

            <!-- Services (Master Jenis Layanan) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <a href="{{ route('services.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('services*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Jenis Layanan' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('services*') ? '1' : '0' }};">dry_cleaning</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Jenis Layanan</span>
                </a>
            @endif

            <!-- Users (Manajemen Staf & Hak Akses) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <a href="{{ route('users.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('users*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Manajemen Staf' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('users*') ? '1' : '0' }};">manage_accounts</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Manajemen Staf</span>
                </a>
            @endif

            <!-- Branches (Manajemen Cabang & Scope) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <a href="{{ route('branches.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('branches*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Manajemen Cabang' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('branches*') ? '1' : '0' }};">store</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Manajemen Cabang</span>
                </a>
            @endif

            <!-- Inventory & Procurement -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin']))
                <a href="/inventory" @click="sidebarOpen = false" 
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('inventory*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Inventory' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('inventory*') ? '1' : '0' }};">inventory_2</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Inventory (Stok)</span>
                </a>
                
                <!-- Procurement Sub-menus (visible when expanded) -->
                <div x-show="desktopSidebarOpen" class="space-y-1 pl-6">
                    <a href="/procurement/suppliers" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-full text-xs font-bold {{ request()->is('procurement/suppliers*') ? 'text-primary font-black bg-orange-50' : 'text-slate-400 hover:text-slate-700' }}">
                        <span class="material-symbols-outlined text-lg">local_shipping</span>
                        <span class="truncate">Supplier</span>
                    </a>
                    <a href="/procurement/purchase-requests" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-full text-xs font-bold {{ request()->is('procurement/purchase-requests*') ? 'text-primary font-black bg-orange-50' : 'text-slate-400 hover:text-slate-700' }}">
                        <span class="material-symbols-outlined text-lg">shopping_basket</span>
                        <span class="truncate">Purchase Requests</span>
                    </a>
                    <a href="/procurement/purchase-orders" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-full text-xs font-bold {{ request()->is('procurement/purchase-orders*') ? 'text-primary font-black bg-orange-50' : 'text-slate-400 hover:text-slate-700' }}">
                        <span class="material-symbols-outlined text-lg">description</span>
                        <span class="truncate">Purchase Orders</span>
                    </a>
                    <a href="/procurement/grns" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-full text-xs font-bold {{ request()->is('procurement/grns*') ? 'text-primary font-black bg-orange-50' : 'text-slate-400 hover:text-slate-700' }}">
                        <span class="material-symbols-outlined text-lg">assignment_turned_in</span>
                        <span class="truncate">Goods Received</span>
                    </a>
                </div>
            @endif

            <!-- Section Divider -->
            <div class="pt-5 pb-1.5 px-4" x-show="desktopSidebarOpen">
                <span class="text-[10px] font-black font-sans text-slate-400 dark:text-slate-600 uppercase tracking-widest">Finance & HR</span>
            </div>

            <!-- HR & Payroll -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/hr" @click="sidebarOpen = false" 
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('hr*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'HR & Payroll' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('hr*') ? '1' : '0' }};">badge</span>
                    <span x-show="desktopSidebarOpen" class="truncate">HR & Payroll</span>
                </a>
            @endif

            <!-- Fixed Assets -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/assets" @click="sidebarOpen = false" 
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('assets*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Aset Tetap' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('assets*') ? '1' : '0' }};">home_work</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Aset Tetap</span>
                </a>
            @endif

            <!-- Accounting -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/finance" @click="sidebarOpen = false" 
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('finance') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Keuangan & COA' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('finance') ? '1' : '0' }};">account_balance_wallet</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Keuangan & COA</span>
                </a>
                
                <!-- Finance Sub-menus -->
                <div x-show="desktopSidebarOpen" class="space-y-1 pl-6">
                    <a href="/finance/journals" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-full text-xs font-bold {{ request()->is('finance/journals*') ? 'text-primary font-black bg-orange-50' : 'text-slate-400 hover:text-slate-700' }}">
                        <span class="material-symbols-outlined text-lg">history_edu</span>
                        <span class="truncate">Jurnal Ledger</span>
                    </a>
                    <a href="/finance/periods" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-full text-xs font-bold {{ request()->is('finance/periods*') ? 'text-primary font-black bg-orange-50' : 'text-slate-400 hover:text-slate-700' }}">
                        <span class="material-symbols-outlined text-lg">calendar_month</span>
                        <span class="truncate">Periode Akuntansi</span>
                    </a>
                    <a href="/finance/reports" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-full text-xs font-bold {{ request()->is('finance/reports*') ? 'text-primary font-black bg-orange-50' : 'text-slate-400 hover:text-slate-700' }}">
                        <span class="material-symbols-outlined text-lg">summarize</span>
                        <span class="truncate">Laporan Keuangan</span>
                    </a>
                </div>
            @endif

            <!-- Audit Logs -->
            @if(auth()->user()->hasRole('Developer'))
                <a href="/audit-logs" @click="sidebarOpen = false" 
                   class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('audit-logs*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Audit Logs' : ''">
                    <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('audit-logs*') ? '1' : '0' }};">history_toggle_off</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Audit Logs</span>
            @endif

            <!-- Panduan & Training Staf -->
            <a href="{{ route('guide') }}" @click="sidebarOpen = false" 
               class="flex items-center gap-4 px-4 py-3 transition-all rounded-full text-xs md:text-sm font-bold {{ request()->is('guide*') ? 'bg-primary-container text-primary-on-container shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-surface-container dark:hover:bg-slate-800/60' }}"
               :title="!desktopSidebarOpen ? 'Panduan Training' : ''">
                <span class="material-symbols-outlined text-2xl shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('guide*') ? '1' : '0' }};">school</span>
                <span x-show="desktopSidebarOpen" class="truncate">Panduan Training Staf</span>
            </a>
        </nav>

        <!-- New Order Shortcut Button (MD3 FAB Style) -->
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
            <div class="px-4 mt-auto hidden md:block">
                <a href="/pos" 
                   class="w-full md3-btn-primary shadow-md3-2"
                   :class="{ '!py-3.5 gap-2': desktopSidebarOpen, '!py-3 !px-0 !w-12 !h-12 mx-auto': !desktopSidebarOpen }"
                   :title="!desktopSidebarOpen ? 'Order Baru' : ''">
                    <span class="material-symbols-outlined text-xl">add</span>
                    <span x-show="desktopSidebarOpen" class="truncate font-bold">Order Baru</span>
                </a>
            </div>
        @endif

        <!-- Footer / Profile & Logout -->
        <div class="mt-4 border-t border-surface-outline/30 dark:border-slate-800 pt-4 px-4">
            <div class="flex items-center gap-3 mb-3" :class="{ 'justify-center': !desktopSidebarOpen }">
                <div class="w-10 h-10 rounded-full bg-primary-container text-primary-on-container border border-primary/20 flex items-center justify-center font-black font-display shrink-0 text-sm shadow-sm">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="overflow-hidden flex-1 min-w-0" x-show="desktopSidebarOpen">
                    <span class="block text-xs font-black font-display text-slate-800 dark:text-slate-200 truncate leading-tight">{{ auth()->user()->name }}</span>
                    <span class="block text-[10px] font-bold text-slate-400 truncate mt-0.5">{{ auth()->user()->getRoleNames()->first() }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center gap-3 py-2.5 transition-colors rounded-full text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 cursor-pointer"
                        :class="{ 'px-4': desktopSidebarOpen, 'justify-center': !desktopSidebarOpen }"
                        :title="!desktopSidebarOpen ? 'Logout' : ''">
                    <span class="material-symbols-outlined text-xl">logout</span>
                    <span x-show="desktopSidebarOpen" class="text-xs font-bold">Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
