<aside class="w-72 h-screen flex flex-col fixed left-0 top-0 bg-white dark:bg-slate-900 border-r border-outline-variant dark:border-slate-800 z-50 transition-transform duration-300 ease-out -translate-x-full md:translate-x-0"
       :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
       id="sidebar-nav">
    <div class="flex flex-col h-full py-6">
        <!-- Brand / Header -->
        <div class="px-6 mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-primary dark:text-orange-400 tracking-tighter">Istana Laundry</h1>
                <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Enterprise Suite</p>
            </div>
            <!-- Close button for mobile -->
            <button @click="sidebarOpen = false" class="p-2 -mr-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 md:hidden cursor-pointer rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 scrollbar-none">
            <!-- Dashboard -->
            <a href="/dashboard" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('dashboard') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/20 font-bold' : 'text-on-surface-variant hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->is('dashboard') ? '1' : '0' }};">dashboard</span>
                <span>Dashboard</span>
            </a>

            <!-- POS (Cashier) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
                <a href="/pos" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('pos*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/20 font-bold' : 'text-on-surface-variant hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->is('pos*') ? '1' : '0' }};">point_of_sale</span>
                    <span>Point of Sale</span>
                </a>
            @endif

            <!-- Refunds -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
                <a href="/refunds" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('refunds*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/20 font-bold' : 'text-on-surface-variant hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->is('refunds*') ? '1' : '0' }};">assignment_return</span>
                    <span>Refund & Pembatalan</span>
                </a>
            @endif

            <!-- Production Tracking -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Workshop_Staff']))
                <a href="/production" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('production*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/20 font-bold' : 'text-on-surface-variant hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->is('production*') ? '1' : '0' }};">precision_manufacturing</span>
                    <span>Production</span>
                </a>
            @endif

            <!-- Section Divider -->
            <div class="pt-4 pb-2 px-4">
                <span class="text-2xs font-bold text-slate-300 dark:text-slate-700 uppercase tracking-widest">Management</span>
            </div>

            <!-- Customers (CRM) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'CS_Marketing']))
                <a href="/customers" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('customers*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/20 font-bold' : 'text-on-surface-variant hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->is('customers*') ? '1' : '0' }};">groups</span>
                    <span>CRM & Loyalty</span>
                </a>
            @endif

            <!-- Promotions -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'CS_Marketing']))
                <a href="/promotions" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('promotions*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/20 font-bold' : 'text-on-surface-variant hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->is('promotions*') ? '1' : '0' }};">campaign</span>
                    <span>Promosi / Kupon</span>
                </a>
            @endif

            <!-- Inventory & Procurement -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin']))
                <a href="/inventory" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('inventory*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/20 font-bold' : 'text-on-surface-variant hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->is('inventory*') ? '1' : '0' }};">inventory_2</span>
                    <span>Inventory (Stok)</span>
                </a>
                <!-- Procurement Sub-menus -->
                <a href="/procurement/purchase-requests" @click="sidebarOpen = false" class="flex items-center gap-3 pl-11 pr-4 py-2.5 transition-all rounded-xl text-xs font-medium {{ request()->is('procurement/purchase-requests*') ? 'text-primary dark:text-orange-400 font-bold bg-orange-50/50 dark:bg-orange-950/10' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[16px]">shopping_basket</span>
                    <span>Purchase Requests</span>
                </a>
                <a href="/procurement/purchase-orders" @click="sidebarOpen = false" class="flex items-center gap-3 pl-11 pr-4 py-2.5 transition-all rounded-xl text-xs font-medium {{ request()->is('procurement/purchase-orders*') ? 'text-primary dark:text-orange-400 font-bold bg-orange-50/50 dark:bg-orange-950/10' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[16px]">description</span>
                    <span>Purchase Orders</span>
                </a>
                <a href="/procurement/grns" @click="sidebarOpen = false" class="flex items-center gap-3 pl-11 pr-4 py-2.5 transition-all rounded-xl text-xs font-medium {{ request()->is('procurement/grns*') ? 'text-primary dark:text-orange-400 font-bold bg-orange-50/50 dark:bg-orange-950/10' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[16px]">assignment_turned_in</span>
                    <span>Goods Received Notes</span>
                </a>
            @endif

            <!-- Section Divider -->
            <div class="pt-4 pb-2 px-4">
                <span class="text-2xs font-bold text-slate-300 dark:text-slate-700 uppercase tracking-widest">Finance & HR</span>
            </div>

            <!-- HR & Payroll -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/hr" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('hr*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/20 font-bold' : 'text-on-surface-variant hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->is('hr*') ? '1' : '0' }};">badge</span>
                    <span>HR & Payroll</span>
                </a>
            @endif

            <!-- Fixed Assets -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/assets" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('assets*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/20 font-bold' : 'text-on-surface-variant hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->is('assets*') ? '1' : '0' }};">home_work</span>
                    <span>Aset Tetap</span>
                </a>
            @endif

            <!-- Accounting -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/finance" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('finance') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/20 font-bold' : 'text-on-surface-variant hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->is('finance') ? '1' : '0' }};">account_balance_wallet</span>
                    <span>Keuangan & COA</span>
                </a>
                <!-- Finance Sub-menus -->
                <a href="/finance/journals" @click="sidebarOpen = false" class="flex items-center gap-3 pl-11 pr-4 py-2.5 transition-all rounded-xl text-xs font-medium {{ request()->is('finance/journals*') ? 'text-primary dark:text-orange-400 font-bold bg-orange-50/50 dark:bg-orange-950/10' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[16px]">history_edu</span>
                    <span>Jurnal Ledger</span>
                </a>
                <a href="/finance/periods" @click="sidebarOpen = false" class="flex items-center gap-3 pl-11 pr-4 py-2.5 transition-all rounded-xl text-xs font-medium {{ request()->is('finance/periods*') ? 'text-primary dark:text-orange-400 font-bold bg-orange-50/50 dark:bg-orange-950/10' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                    <span>Periode Akuntansi</span>
                </a>
                <a href="/finance/reports" @click="sidebarOpen = false" class="flex items-center gap-3 pl-11 pr-4 py-2.5 transition-all rounded-xl text-xs font-medium {{ request()->is('finance/reports*') ? 'text-primary dark:text-orange-400 font-bold bg-orange-50/50 dark:bg-orange-950/10' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[16px]">summarize</span>
                    <span>Laporan Keuangan</span>
                </a>
            @endif

            <!-- Audit Logs -->
            @if(auth()->user()->hasRole('Developer'))
                <a href="/audit-logs" @click="sidebarOpen = false" class="flex items-center gap-3 px-4 py-3 transition-all rounded-xl text-sm font-semibold {{ request()->is('audit-logs*') ? 'text-primary dark:text-orange-400 bg-orange-50 dark:bg-orange-950/20 font-bold' : 'text-on-surface-variant hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' {{ request()->is('audit-logs*') ? '1' : '0' }};">history_toggle_off</span>
                    <span>Audit Logs</span>
                </a>
            @endif
        </nav>

        <!-- New Order Shortcut Button -->
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
            <div class="px-4 mt-auto hidden md:block">
                <a href="/pos" class="w-full bg-primary hover:bg-primary-hover text-white py-4 rounded-xl font-bold flex items-center justify-center gap-2 transition-all active:scale-95 shadow-md shadow-orange-500/10">
                    <span class="material-symbols-outlined">add</span>
                    New Order
                </a>
            </div>
        @endif

        <!-- Footer / Switch Account & Logout -->
        <div class="mt-4 border-t border-outline-variant dark:border-slate-800 pt-4">
            <div class="px-4 py-2 flex items-center gap-3 mb-2">
                <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-200 font-bold shrink-0 text-xs">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="overflow-hidden flex-1 min-w-0">
                    <span class="block text-xs font-bold text-slate-700 dark:text-slate-200 truncate">{{ auth()->user()->name }}</span>
                    <span class="block text-2xs text-slate-400 truncate">{{ auth()->user()->getRoleNames()->first() }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-slate-500 hover:text-red-500 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    <span class="text-sm font-semibold">Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
