<aside class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full md:translate-x-0 bg-slate-900 border-r border-slate-800 text-slate-400 flex flex-col justify-between"
       :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen }"
       id="sidebar-nav">
    <div>
        <!-- Brand/Logo Area -->
        <div class="h-16 flex items-center px-6 border-b border-slate-800 bg-slate-950/20">
            <span class="text-orange-500 font-black text-xl tracking-wider uppercase flex items-center gap-2">
                <span class="material-symbols-outlined text-orange-500 text-2xl font-bold">local_laundry_service</span>
                Istana Laundry
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="p-4 space-y-1.5 overflow-y-auto flex-1 h-[calc(100vh-120px)]">
            <!-- Dashboard -->
            <a href="/dashboard" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white {{ request()->is('dashboard') ? 'bg-primary text-white shadow-sm' : '' }}">
                <span class="material-symbols-outlined">dashboard</span>
                Dashboard
            </a>

            <!-- POS (Cashier) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
                <a href="/pos" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white {{ request()->is('pos*') ? 'bg-primary text-white shadow-sm' : '' }}">
                    <span class="material-symbols-outlined">point_of_sale</span>
                    POS / Kasir
                </a>
            @endif

            <!-- Production Tracking -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Workshop_Staff']))
                <a href="/production" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white {{ request()->is('production*') ? 'bg-primary text-white shadow-sm' : '' }}">
                    <span class="material-symbols-outlined">precision_manufacturing</span>
                    Produksi
                </a>
            @endif

            <!-- Customers (CRM) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'CS_Marketing']))
                <a href="/customers" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white {{ request()->is('customers*') ? 'bg-slate-800 text-white' : '' }}">
                    <span class="material-symbols-outlined">groups</span>
                    CRM & Pelanggan
                </a>
            @endif

            <!-- Promotions -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'CS_Marketing']))
                <a href="/promotions" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white {{ request()->is('promotions*') ? 'bg-slate-800 text-white' : '' }}">
                    <span class="material-symbols-outlined">campaign</span>
                    Promosi / Kupon
                </a>
            @endif

            <!-- Inventory -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin']))
                <a href="/inventory" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white {{ request()->is('inventory*') ? 'bg-slate-800 text-white' : '' }}">
                    <span class="material-symbols-outlined">inventory_2</span>
                    Inventori & Stok
                </a>
            @endif

            <!-- HR & Payroll -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/hr" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white {{ request()->is('hr*') ? 'bg-slate-800 text-white' : '' }}">
                    <span class="material-symbols-outlined">badge</span>
                    Karyawan & Payroll
                </a>
            @endif

            <!-- Fixed Assets -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/assets" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white {{ request()->is('assets*') ? 'bg-slate-800 text-white' : '' }}">
                    <span class="material-symbols-outlined">home_work</span>
                    Aset Tetap
                </a>
            @endif

            <!-- Accounting -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/finance" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white {{ request()->is('finance*') ? 'bg-slate-800 text-white' : '' }}">
                    <span class="material-symbols-outlined">account_balance_wallet</span>
                    Keuangan & COA
                </a>
            @endif

            <!-- Audit Logs -->
            @if(auth()->user()->hasRole('Developer'))
                <a href="/audit-logs" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-semibold transition-all hover:bg-slate-800 hover:text-white {{ request()->is('audit-logs*') ? 'bg-slate-800 text-white' : '' }}">
                    <span class="material-symbols-outlined">history_toggle_off</span>
                    Audit Logs
                </a>
            @endif
        </nav>
    </div>

    <!-- User Profile Footer Section -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/20">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-200 font-bold shrink-0">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="overflow-hidden">
                    <span class="block text-xs font-bold text-slate-200 truncate">{{ auth()->user()->name }}</span>
                    <span class="block text-[10px] text-slate-500 truncate">{{ auth()->user()->getRoleNames()->first() }}</span>
                </div>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-slate-500 hover:text-rose-500 transition-colors cursor-pointer" title="Sign Out">
                    <span class="material-symbols-outlined text-xl">logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
