<aside class="w-72 h-screen flex flex-col fixed left-0 top-0 bg-white dark:bg-slate-900 border-r border-outline-variant dark:border-slate-800 z-50 transition-transform -translate-x-full md:translate-x-0"
       :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
       id="sidebar-nav">
    <div class="flex flex-col h-full py-6">
        <!-- Brand / Header -->
        <div class="px-6 mb-10">
            <h1 class="text-2xl font-black text-primary dark:text-orange-400 tracking-tighter">Istana Laundry</h1>
            <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Enterprise Suite</p>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 space-y-1 overflow-y-auto px-2">
            <!-- Dashboard -->
            <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 transition-all rounded-lg text-sm font-semibold {{ request()->is('dashboard') ? 'text-primary dark:text-orange-400 bg-primary-container/10 dark:bg-primary-container/20 border-r-4 border-primary font-bold' : 'text-on-surface-variant hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>

            <!-- POS (Cashier) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
                <a href="/pos" class="flex items-center gap-3 px-4 py-3 transition-all rounded-lg text-sm font-semibold {{ request()->is('pos*') ? 'text-primary dark:text-orange-400 bg-primary-container/10 dark:bg-primary-container/20 border-r-4 border-primary font-bold' : 'text-on-surface-variant hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined">point_of_sale</span>
                    <span>Point of Sale</span>
                </a>
            @endif

            <!-- Production Tracking -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Workshop_Staff']))
                <a href="/production" class="flex items-center gap-3 px-4 py-3 transition-all rounded-lg text-sm font-semibold {{ request()->is('production*') ? 'text-primary dark:text-orange-400 bg-primary-container/10 dark:bg-primary-container/20 border-r-4 border-primary font-bold' : 'text-on-surface-variant hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined">precision_manufacturing</span>
                    <span>Production</span>
                </a>
            @endif

            <!-- Customers (CRM) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'CS_Marketing']))
                <a href="/customers" class="flex items-center gap-3 px-4 py-3 transition-all rounded-lg text-sm font-semibold {{ request()->is('customers*') ? 'text-primary dark:text-orange-400 bg-primary-container/10 dark:bg-primary-container/20 border-r-4 border-primary font-bold' : 'text-on-surface-variant hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined">groups</span>
                    <span>CRM &amp; Loyalty</span>
                </a>
            @endif

            <!-- Promotions -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'CS_Marketing']))
                <a href="/promotions" class="flex items-center gap-3 px-4 py-3 transition-all rounded-lg text-sm font-semibold {{ request()->is('promotions*') ? 'text-primary dark:text-orange-400 bg-primary-container/10 dark:bg-primary-container/20 border-r-4 border-primary font-bold' : 'text-on-surface-variant hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined">campaign</span>
                    <span>Promosi / Kupon</span>
                </a>
            @endif

            <!-- Inventory -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin']))
                <a href="/inventory" class="flex items-center gap-3 px-4 py-3 transition-all rounded-lg text-sm font-semibold {{ request()->is('inventory*') ? 'text-primary dark:text-orange-400 bg-primary-container/10 dark:bg-primary-container/20 border-r-4 border-primary font-bold' : 'text-on-surface-variant hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined">inventory_2</span>
                    <span>Inventory (Stok)</span>
                </a>
                <!-- Procurement Sub-menus -->
                <a href="/procurement/purchase-requests" class="flex items-center gap-3 pl-10 pr-4 py-2 transition-all rounded-lg text-xs {{ request()->is('procurement/purchase-requests*') ? 'text-primary dark:text-orange-400 font-bold' : 'text-slate-400 hover:text-slate-650 hover:bg-slate-50 dark:hover:bg-slate-850' }}">
                    <span class="material-symbols-outlined text-sm">shopping_basket</span>
                    <span>Purchase Requests</span>
                </a>
                <a href="/procurement/purchase-orders" class="flex items-center gap-3 pl-10 pr-4 py-2 transition-all rounded-lg text-xs {{ request()->is('procurement/purchase-orders*') ? 'text-primary dark:text-orange-400 font-bold' : 'text-slate-400 hover:text-slate-650 hover:bg-slate-50 dark:hover:bg-slate-850' }}">
                    <span class="material-symbols-outlined text-sm">description</span>
                    <span>Purchase Orders</span>
                </a>
                <a href="/procurement/grns" class="flex items-center gap-3 pl-10 pr-4 py-2 transition-all rounded-lg text-xs {{ request()->is('procurement/grns*') ? 'text-primary dark:text-orange-400 font-bold' : 'text-slate-400 hover:text-slate-650 hover:bg-slate-50 dark:hover:bg-slate-850' }}">
                    <span class="material-symbols-outlined text-sm">assignment_turned_in</span>
                    <span>Goods Received Notes</span>
                </a>
            @endif

            <!-- HR & Payroll -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/hr" class="flex items-center gap-3 px-4 py-3 transition-all rounded-lg text-sm font-semibold {{ request()->is('hr*') ? 'text-primary dark:text-orange-400 bg-primary-container/10 dark:bg-primary-container/20 border-r-4 border-primary font-bold' : 'text-on-surface-variant hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined">badge</span>
                    <span>HR &amp; Payroll</span>
                </a>
            @endif

            <!-- Fixed Assets -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/assets" class="flex items-center gap-3 px-4 py-3 transition-all rounded-lg text-sm font-semibold {{ request()->is('assets*') ? 'text-primary dark:text-orange-400 bg-primary-container/10 dark:bg-primary-container/20 border-r-4 border-primary font-bold' : 'text-on-surface-variant hover:bg-slate-100 dark:hover:bg-slate-850' }}">
                    <span class="material-symbols-outlined">home_work</span>
                    <span>Aset Tetap</span>
                </a>
            @endif

            <!-- Accounting -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/finance" class="flex items-center gap-3 px-4 py-3 transition-all rounded-lg text-sm font-semibold {{ request()->is('finance') ? 'text-primary dark:text-orange-400 bg-primary-container/10 dark:bg-primary-container/20 border-r-4 border-primary font-bold' : 'text-on-surface-variant hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined">account_balance_wallet</span>
                    <span>Keuangan &amp; COA</span>
                </a>
                <!-- Finance Sub-menus -->
                <a href="/finance/journals" class="flex items-center gap-3 pl-10 pr-4 py-2 transition-all rounded-lg text-xs {{ request()->is('finance/journals*') ? 'text-primary dark:text-orange-400 font-bold' : 'text-slate-400 hover:text-slate-650 hover:bg-slate-50 dark:hover:bg-slate-850' }}">
                    <span class="material-symbols-outlined text-sm">history_edu</span>
                    <span>Jurnal Ledger</span>
                </a>
                <a href="/finance/periods" class="flex items-center gap-3 pl-10 pr-4 py-2 transition-all rounded-lg text-xs {{ request()->is('finance/periods*') ? 'text-primary dark:text-orange-400 font-bold' : 'text-slate-400 hover:text-slate-650 hover:bg-slate-50 dark:hover:bg-slate-850' }}">
                    <span class="material-symbols-outlined text-sm">calendar_month</span>
                    <span>Periode Akuntansi</span>
                </a>
                <a href="/finance/reports" class="flex items-center gap-3 pl-10 pr-4 py-2 transition-all rounded-lg text-xs {{ request()->is('finance/reports*') ? 'text-primary dark:text-orange-400 font-bold' : 'text-slate-400 hover:text-slate-650 hover:bg-slate-50 dark:hover:bg-slate-850' }}">
                    <span class="material-symbols-outlined text-sm">summarize</span>
                    <span>Laporan Keuangan</span>
                </a>
            @endif

            <!-- Audit Logs -->
            @if(auth()->user()->hasRole('Developer'))
                <a href="/audit-logs" class="flex items-center gap-3 px-4 py-3 transition-all rounded-lg text-sm font-semibold {{ request()->is('audit-logs*') ? 'text-primary dark:text-orange-400 bg-primary-container/10 dark:bg-primary-container/20 border-r-4 border-primary font-bold' : 'text-on-surface-variant hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined">history_toggle_off</span>
                    <span>Audit Logs</span>
                </a>
            @endif
        </nav>

        <!-- New Order Shortcut Button -->
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
            <div class="px-4 mt-auto">
                <a href="/pos" class="w-full bg-primary hover:bg-orange-600 text-white py-4 rounded-lg font-bold flex items-center justify-center gap-2 transition-all active:scale-95 shadow-md shadow-orange-500/10">
                    <span class="material-symbols-outlined">add</span>
                    New Order
                </a>
            </div>
        @endif

        <!-- Footer / Switch Account & Logout -->
        <div class="mt-6 border-t border-outline-variant dark:border-slate-800 pt-4">
            <div class="px-4 py-2 flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-200 font-bold shrink-0">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="overflow-hidden">
                    <span class="block text-xs font-bold text-slate-700 dark:text-slate-200 truncate">{{ auth()->user()->name }}</span>
                    <span class="block text-[10px] text-slate-400 truncate">{{ auth()->user()->getRoleNames()->first() }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-red-500 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined">logout</span>
                    <span class="text-sm font-semibold">Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
