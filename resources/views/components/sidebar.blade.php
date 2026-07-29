<aside class="h-screen flex flex-col fixed left-0 top-0 bg-white dark:bg-slate-900 border-r border-outline-variant dark:border-slate-800 z-50 transition-all duration-300 ease-in-out"
       :class="{ 
           'w-72': desktopSidebarOpen, 
           'w-20': !desktopSidebarOpen,
           'translate-x-0': sidebarOpen, 
           '-translate-x-full md:translate-x-0': !sidebarOpen 
       }"
       id="sidebar-nav">
    <div class="flex flex-col h-full py-5 overflow-hidden">
        <!-- Brand / Header -->
        <div class="px-4 mb-6 flex items-center justify-between min-h-[40px]">
            <div class="flex items-center gap-3 overflow-hidden" x-show="desktopSidebarOpen" x-transition.opacity>
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-primary to-amber-500 flex items-center justify-center text-white font-black text-lg shrink-0 shadow-md shadow-orange-500/20">
                    IL
                </div>
                <div class="truncate">
                    <h1 class="text-lg font-black text-slate-900 dark:text-white tracking-tight leading-none">Istana Laundry</h1>
                    <p class="text-[10px] font-bold text-primary uppercase tracking-wider mt-0.5">Enterprise Suite</p>
                </div>
            </div>

            <!-- Mini Logo when collapsed -->
            <div class="mx-auto" x-show="!desktopSidebarOpen" x-transition.opacity>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-primary to-amber-500 flex items-center justify-center text-white font-black text-xl shadow-md shadow-orange-500/20">
                    IL
                </div>
            </div>

            <!-- Close button for mobile -->
            <button @click="sidebarOpen = false" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 md:hidden cursor-pointer rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <!-- Desktop Toggle Button (Expand/Collapse Sidebar) -->
        <div class="px-3 mb-4 hidden md:block">
            <button @click="desktopSidebarOpen = !desktopSidebarOpen; localStorage.setItem('desktopSidebarOpen', desktopSidebarOpen)"
                    class="w-full flex items-center gap-3 px-3 py-2 text-slate-400 hover:text-primary dark:hover:text-orange-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 rounded-xl transition-all text-xs font-semibold group cursor-pointer"
                    :title="desktopSidebarOpen ? 'Ciutkan Sidebar' : 'Buka Sidebar'">
                <span class="material-symbols-outlined text-[20px] transition-transform duration-300 group-hover:scale-110"
                      :class="{ 'rotate-180': !desktopSidebarOpen }">
                    side_navigation
                </span>
                <span x-show="desktopSidebarOpen" class="truncate">Sembunyikan Sidebar</span>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 scrollbar-none">
            <!-- Dashboard -->
            <a href="/dashboard" @click="sidebarOpen = false" 
               class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('dashboard') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
               :title="!desktopSidebarOpen ? 'Dashboard' : ''">
                <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('dashboard') ? '1' : '0' }};">dashboard</span>
                <span x-show="desktopSidebarOpen" class="truncate">Dashboard</span>
            </a>

            <!-- POS (Cashier) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
                <a href="/pos" @click="sidebarOpen = false" 
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('pos*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Point of Sale' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('pos*') ? '1' : '0' }};">point_of_sale</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Point of Sale</span>
                </a>
            @endif

            <!-- Refunds -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
                <a href="/refunds" @click="sidebarOpen = false" 
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('refunds*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Refund & Pembatalan' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('refunds*') ? '1' : '0' }};">assignment_return</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Refund & Pembatalan</span>
                </a>
            @endif

            <!-- Orders (Semua Transaksi) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
                <a href="{{ route('orders.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('orders*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Semua Transaksi' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('orders*') ? '1' : '0' }};">receipt_long</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Semua Transaksi</span>
                </a>
            @endif

            <!-- Production Tracking -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Workshop_Staff']))
                <a href="/production" @click="sidebarOpen = false" 
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('production*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Production' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('production*') ? '1' : '0' }};">precision_manufacturing</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Production</span>
                </a>
            @endif

            <!-- Performance Monitoring -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Finance']))
                <a href="{{ route('performance.index') }}" @click="sidebarOpen = false" 
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('performance*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Memantau Kinerja' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('performance*') ? '1' : '0' }};">insights</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Memantau Kinerja</span>
                </a>
            @endif

            <!-- Section Divider -->
            <div class="pt-4 pb-1 px-3.5" x-show="desktopSidebarOpen">
                <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-600 uppercase tracking-widest">Management</span>
            </div>

            <!-- Customers (CRM) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'CS_Marketing']))
                <a href="/customers" @click="sidebarOpen = false" 
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('customers*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'CRM & Loyalty' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('customers*') ? '1' : '0' }};">groups</span>
                    <span x-show="desktopSidebarOpen" class="truncate">CRM & Loyalty</span>
                </a>
            @endif

            <!-- Promotions -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'CS_Marketing']))
                <a href="/promotions" @click="sidebarOpen = false" 
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('promotions*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Promosi / Kupon' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('promotions*') ? '1' : '0' }};">campaign</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Promosi / Kupon</span>
                </a>
            @endif

            <!-- Services (Master Jenis Layanan) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <a href="{{ route('services.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('services*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Jenis Layanan' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('services*') ? '1' : '0' }};">dry_cleaning</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Jenis Layanan</span>
                </a>
            @endif

            <!-- Users (Manajemen Staf & Hak Akses) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <a href="{{ route('users.index') }}" @click="sidebarOpen = false"
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('users*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Manajemen Staf' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('users*') ? '1' : '0' }};">manage_accounts</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Manajemen Staf</span>
                </a>
            @endif

            <!-- Inventory & Procurement -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin']))
                <a href="/inventory" @click="sidebarOpen = false" 
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('inventory*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Inventory' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('inventory*') ? '1' : '0' }};">inventory_2</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Inventory (Stok)</span>
                </a>
                
                <!-- Procurement Sub-menus (visible when expanded) -->
                <div x-show="desktopSidebarOpen" class="space-y-0.5 pl-4">
                    <a href="/procurement/suppliers" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-xl text-xs font-medium {{ request()->is('procurement/suppliers*') ? 'text-primary font-bold bg-orange-50/50' : 'text-slate-400 hover:text-slate-600' }}">
                        <span class="material-symbols-outlined text-[16px]">local_shipping</span>
                        <span class="truncate">Supplier</span>
                    </a>
                    <a href="/procurement/purchase-requests" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-xl text-xs font-medium {{ request()->is('procurement/purchase-requests*') ? 'text-primary font-bold bg-orange-50/50' : 'text-slate-400 hover:text-slate-600' }}">
                        <span class="material-symbols-outlined text-[16px]">shopping_basket</span>
                        <span class="truncate">Purchase Requests</span>
                    </a>
                    <a href="/procurement/purchase-orders" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-xl text-xs font-medium {{ request()->is('procurement/purchase-orders*') ? 'text-primary font-bold bg-orange-50/50' : 'text-slate-400 hover:text-slate-600' }}">
                        <span class="material-symbols-outlined text-[16px]">description</span>
                        <span class="truncate">Purchase Orders</span>
                    </a>
                    <a href="/procurement/grns" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-xl text-xs font-medium {{ request()->is('procurement/grns*') ? 'text-primary font-bold bg-orange-50/50' : 'text-slate-400 hover:text-slate-600' }}">
                        <span class="material-symbols-outlined text-[16px]">assignment_turned_in</span>
                        <span class="truncate">Goods Received</span>
                    </a>
                </div>
            @endif

            <!-- Section Divider -->
            <div class="pt-4 pb-1 px-3.5" x-show="desktopSidebarOpen">
                <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-600 uppercase tracking-widest">Finance & HR</span>
            </div>

            <!-- HR & Payroll -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/hr" @click="sidebarOpen = false" 
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('hr*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'HR & Payroll' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('hr*') ? '1' : '0' }};">badge</span>
                    <span x-show="desktopSidebarOpen" class="truncate">HR & Payroll</span>
                </a>
            @endif

            <!-- Fixed Assets -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/assets" @click="sidebarOpen = false" 
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('assets*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Aset Tetap' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('assets*') ? '1' : '0' }};">home_work</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Aset Tetap</span>
                </a>
            @endif

            <!-- Accounting -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/finance" @click="sidebarOpen = false" 
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('finance') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Keuangan & COA' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('finance') ? '1' : '0' }};">account_balance_wallet</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Keuangan & COA</span>
                </a>
                
                <!-- Finance Sub-menus -->
                <div x-show="desktopSidebarOpen" class="space-y-0.5 pl-4">
                    <a href="/finance/journals" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-xl text-xs font-medium {{ request()->is('finance/journals*') ? 'text-primary font-bold bg-orange-50/50' : 'text-slate-400 hover:text-slate-600' }}">
                        <span class="material-symbols-outlined text-[16px]">history_edu</span>
                        <span class="truncate">Jurnal Ledger</span>
                    </a>
                    <a href="/finance/periods" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-xl text-xs font-medium {{ request()->is('finance/periods*') ? 'text-primary font-bold bg-orange-50/50' : 'text-slate-400 hover:text-slate-600' }}">
                        <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                        <span class="truncate">Periode Akuntansi</span>
                    </a>
                    <a href="/finance/reports" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-xl text-xs font-medium {{ request()->is('finance/reports*') ? 'text-primary font-bold bg-orange-50/50' : 'text-slate-400 hover:text-slate-600' }}">
                        <span class="material-symbols-outlined text-[16px]">summarize</span>
                        <span class="truncate">Laporan Keuangan</span>
                    </a>
                </div>
            @endif

            <!-- Audit Logs -->
            @if(auth()->user()->hasRole('Developer'))
                <a href="/audit-logs" @click="sidebarOpen = false" 
                   class="flex items-center gap-3.5 px-3.5 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('audit-logs*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/30 font-bold border-r-4 border-primary' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60' }}"
                   :title="!desktopSidebarOpen ? 'Audit Logs' : ''">
                    <span class="material-symbols-outlined text-[22px] shrink-0" style="font-variation-settings: 'FILL' {{ request()->is('audit-logs*') ? '1' : '0' }};">history_toggle_off</span>
                    <span x-show="desktopSidebarOpen" class="truncate">Audit Logs</span>
                </a>
            @endif
        </nav>

        <!-- New Order Shortcut Button -->
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
            <div class="px-3 mt-auto hidden md:block">
                <a href="/pos" 
                   class="w-full bg-primary hover:bg-primary-hover text-white rounded-xl font-bold flex items-center justify-center transition-all active:scale-95 shadow-md shadow-orange-500/20"
                   :class="{ 'py-3.5 gap-2': desktopSidebarOpen, 'py-3 px-0': !desktopSidebarOpen }"
                   :title="!desktopSidebarOpen ? 'Order Baru' : ''">
                    <span class="material-symbols-outlined text-xl">add</span>
                    <span x-show="desktopSidebarOpen" class="truncate text-sm">Order Baru</span>
                </a>
            </div>
        @endif

        <!-- Footer / Profile & Logout -->
        <div class="mt-4 border-t border-outline-variant dark:border-slate-800 pt-3 px-3">
            <div class="flex items-center gap-3 mb-2" :class="{ 'justify-center': !desktopSidebarOpen }">
                <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-200 font-bold shrink-0 text-xs shadow-sm">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="overflow-hidden flex-1 min-w-0" x-show="desktopSidebarOpen">
                    <span class="block text-xs font-bold text-slate-800 dark:text-slate-200 truncate leading-tight">{{ auth()->user()->name }}</span>
                    <span class="block text-[10px] text-slate-400 truncate mt-0.5">{{ auth()->user()->getRoleNames()->first() }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center gap-3 py-2.5 transition-colors rounded-xl text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 cursor-pointer"
                        :class="{ 'px-3': desktopSidebarOpen, 'justify-center': !desktopSidebarOpen }"
                        :title="!desktopSidebarOpen ? 'Logout' : ''">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    <span x-show="desktopSidebarOpen" class="text-xs font-bold">Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
