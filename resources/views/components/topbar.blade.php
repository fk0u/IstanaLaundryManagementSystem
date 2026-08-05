<header class="flex items-center justify-between min-h-[60px] md:min-h-[64px] px-3 sm:px-6 w-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800/80 z-40 sticky top-0 transition-all duration-300 shadow-2xs"
        x-data="{ 
            showNotifications: false, 
            showQuickAction: false, 
            showProfileMenu: false,
            showSupportMenu: false,
            showBranchDropdown: false
        }">
    @php
        $pageTitle = match(true) {
            request()->is('dashboard') => 'Executive Dashboard',
            request()->is('pos*') => 'Point of Sale (Kasir)',
            request()->is('refunds*') => 'Refund & Pembatalan',
            request()->is('orders*') => 'Semua Transaksi',
            request()->is('production*') => 'Production Tracking',
            request()->is('performance*') => 'Pemantauan Kinerja',
            request()->is('customers*') => 'CRM & Loyalty Pelanggan',
            request()->is('promotions*') => 'Manajemen Promosi',
            request()->is('services*') => 'Master Layanan Cucian',
            request()->is('users*') => 'Manajemen Staf',
            request()->is('branches*') => 'Manajemen Cabang',
            request()->is('inventory*') => 'Manajemen Inventory',
            request()->is('procurement*') => 'Procurement & Supplier',
            request()->is('hr*') => 'HR & Payroll Karyawan',
            request()->is('assets*') => 'Aset Tetap (Fixed Assets)',
            request()->is('finance/journals*') => 'Jurnal Ledger',
            request()->is('finance/periods*') => 'Periode Akuntansi',
            request()->is('finance/reports*') => 'Laporan Keuangan',
            request()->is('finance/operational-expenses*') => 'Beban Operasional (Kas Kecil)',
            request()->is('finance/supplier-payments*') => 'Pelunasan Hutang Supplier',
            request()->is('finance*') => 'Keuangan & COA',
            request()->is('audit-logs*') => 'Audit Logs',
            request()->is('guide*') => 'Panduan Training Staf',
            default => 'Istana Laundry ERP'
        };

        $currentBranchId = session('scoped_branch_id');
        $currentBranchName = $currentBranchId ? (\App\Models\Branch::find($currentBranchId)?->name ?? 'Cabang') : 'Global';
    @endphp

    <!-- Mobile & Small Tablet TopAppBar View (md:hidden) -->
    <div class="flex items-center justify-between w-full md:hidden">
        <!-- Logo & Brand Header -->
        <div class="flex items-center gap-2">
            <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200 cursor-pointer rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all shrink-0">
                <span class="material-symbols-outlined text-[24px]">menu</span>
            </button>
            <div class="w-8 h-8 rounded-xl bg-orange-500/10 dark:bg-orange-500/20 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-primary text-xl">local_laundry_service</span>
            </div>
            <h1 class="text-base font-bold text-slate-900 dark:text-white tracking-tight truncate">
                Istana Laundry
            </h1>
        </div>

        <!-- Mobile Controls: Dark Mode Toggle & Notifications -->
        <div class="flex items-center gap-1.5">
            <!-- Dark Mode Toggle Button (Replaces Mobile Branch Dropdown) -->
            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                    class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-all flex items-center justify-center" 
                    title="Toggle Dark Mode">
                <span class="material-symbols-outlined text-xl text-amber-500" x-show="!darkMode">dark_mode</span>
                <span class="material-symbols-outlined text-xl text-amber-400" x-show="darkMode" x-cloak>light_mode</span>
            </button>

            <!-- Notifications Button -->
            <button @click="sidebarOpen = true" class="relative w-9 h-9 flex items-center justify-center rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                <span class="material-symbols-outlined text-slate-600 dark:text-slate-300 text-xl">notifications</span>
                <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-white dark:border-slate-900"></span>
            </button>
        </div>
    </div>

    <!-- Tablet & Desktop Topbar View (hidden md:flex) -->
    <div class="hidden md:flex items-center justify-between w-full">
        <!-- Left: Page Title & Sidebar Toggle -->
        <div class="flex items-center gap-3 min-w-0 flex-1">
            <button @click="desktopSidebarOpen = !desktopSidebarOpen; localStorage.setItem('desktopSidebarOpen', desktopSidebarOpen)" 
                    class="p-2 text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-orange-400 cursor-pointer rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all shrink-0 items-center justify-center"
                    :title="desktopSidebarOpen ? 'Ciutkan Sidebar' : 'Perluas Sidebar'">
                <span class="material-symbols-outlined text-[24px] transition-transform duration-300"
                      :class="{ 'rotate-180': !desktopSidebarOpen }">
                    menu_open
                </span>
            </button>

            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-black font-display text-slate-900 dark:text-white tracking-tight truncate">
                    {{ $pageTitle }}
                </h1>
            </div>
        </div>

        <!-- Right Controls: Quick Action, Icons, Profile -->
        <div class="flex items-center gap-2 shrink-0">
            <!-- Quick Action Dropdown -->
            <div class="relative" @click.away="showQuickAction = false">
                <button @click="showQuickAction = !showQuickAction; showNotifications = false; showSupportMenu = false; showProfileMenu = false" 
                        class="md3-btn-tonal !min-h-[38px] !px-3.5 !rounded-full text-xs font-bold flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <span class="material-symbols-outlined text-base text-primary">add_circle</span>
                    <span>Quick Action</span>
                    <span class="material-symbols-outlined text-sm transition-transform" :class="{ 'rotate-180': showQuickAction }">expand_more</span>
                </button>

                <div x-show="showQuickAction" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2.5 w-64 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-expressive shadow-md3-3 z-50 p-2 space-y-1"
                     x-cloak>
                    
                    @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
                        <a href="/pos" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-orange-50 dark:hover:bg-orange-950/30 text-slate-800 dark:text-slate-200 font-bold text-xs">
                            <span class="material-symbols-outlined text-primary text-lg">point_of_sale</span>
                            Buat Transaksi Kasir (POS)
                        </a>
                    @endif

                    @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin']))
                        <a href="/production" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs">
                            <span class="material-symbols-outlined text-sky-500 text-lg">precision_manufacturing</span>
                            Kelola Antrean Produksi
                        </a>
                    @endif

                    @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Finance']))
                        <a href="/hr" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs">
                            <span class="material-symbols-outlined text-emerald-500 text-lg">person_add</span>
                            Tambah Data Karyawan
                        </a>
                        <a href="/finance/reports" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs">
                            <span class="material-symbols-outlined text-purple-500 text-lg">analytics</span>
                            Lihat Laporan Keuangan
                        </a>
                    @endif
                </div>
            </div>

            <!-- Dark Mode Toggle -->
            <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                    class="p-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition-all" 
                    title="Toggle Dark Mode">
                <span class="material-symbols-outlined text-xl" x-show="!darkMode">dark_mode</span>
                <span class="material-symbols-outlined text-xl" x-show="darkMode" x-cloak>light_mode</span>
            </button>

            <!-- Support Button -->
            <div class="relative" @click.away="showSupportMenu = false">
                <button @click="showSupportMenu = !showSupportMenu; showNotifications = false; showQuickAction = false; showProfileMenu = false"
                        class="p-2 text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-orange-400 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
                        title="Pusat Bantuan & Support">
                    <span class="material-symbols-outlined text-xl">help_outline</span>
                </button>

                <div x-show="showSupportMenu" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2.5 w-80 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-expressive shadow-md3-3 z-50 p-4 space-y-3"
                     x-cloak>
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <span class="material-symbols-outlined text-primary text-2xl">support_agent</span>
                        <div>
                            <h4 class="font-extrabold font-display text-sm text-slate-800 dark:text-slate-100">Pusat Bantuan ERP</h4>
                            <p class="text-2xs font-semibold text-slate-400">Dukungan Teknis & Operasional</p>
                        </div>
                    </div>
                    <div class="space-y-1 text-xs">
                        <a href="https://wa.me/6281234567890?text=Halo%20IT%20Support%20Istana%20Laundry%2C%20saya%20butuh%20bantuan" target="_blank" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                            <span class="material-symbols-outlined text-emerald-500 text-lg">chat</span>
                            WhatsApp IT Helpdesk
                        </a>
                        <a href="{{ route('guide') }}" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                            <span class="material-symbols-outlined text-sky-500 text-lg">auto_stories</span>
                            Panduan Training Staf
                        </a>
                    </div>
                </div>
            </div>

            <!-- Notifications Bell -->
            @php
                $activeCount = \App\Models\Order::whereNotIn('production_status', ['DIAMBIL'])->count();
                $recentOrders = \App\Models\Order::latest()->take(4)->get();
            @endphp
            <div class="relative" @click.away="showNotifications = false">
                <button @click="showNotifications = !showNotifications; showQuickAction = false; showSupportMenu = false; showProfileMenu = false" 
                        class="p-2 text-slate-500 dark:text-slate-400 hover:text-primary rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer relative"
                        title="Notifikasi">
                    <span class="material-symbols-outlined text-xl">notifications</span>
                    @if($activeCount > 0)
                        <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-primary rounded-full ring-2 ring-white dark:ring-slate-900 animate-pulse"></span>
                    @endif
                </button>

                <div x-show="showNotifications" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2.5 w-88 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-expressive shadow-md3-3 z-50 p-4 space-y-3"
                     x-cloak>
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="font-black font-display text-sm text-slate-800 dark:text-slate-100">Notifikasi System</span>
                            <span class="px-2 py-0.5 text-2xs font-extrabold bg-primary-container text-primary-on-container rounded-full">{{ $activeCount }} Active</span>
                        </div>
                        <a href="/production" class="text-2xs font-bold text-primary hover:underline">Lihat Semua</a>
                    </div>

                    <div class="space-y-2 max-h-72 overflow-y-auto">
                        @forelse($recentOrders as $notifOrder)
                            <a href="/production" class="flex items-start gap-3 p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                                <span class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-base">local_laundry_service</span>
                                </span>
                                <div class="min-w-0 flex-1 text-xs">
                                    <p class="font-extrabold text-slate-800 dark:text-slate-200 truncate">#{{ $notifOrder->order_number }} - {{ $notifOrder->customer?->name }}</p>
                                    <p class="text-2xs font-medium text-slate-400 mt-0.5">Status: <span class="font-bold text-primary">{{ $notifOrder->production_status }}</span></p>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-slate-400 font-medium text-center py-4">Tidak ada notifikasi baru.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Profile Avatar & Menu -->
            <div class="relative" @click.away="showProfileMenu = false">
                <button @click="showProfileMenu = !showProfileMenu; showNotifications = false; showQuickAction = false; showSupportMenu = false"
                        class="flex items-center gap-2 cursor-pointer p-1 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
                    @if(auth()->user()->avatar_path)
                        <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}" class="w-8.5 h-8.5 md:w-9 md:h-9 rounded-2xl object-cover shrink-0 shadow-xs border border-orange-300/40 dark:border-slate-700">
                    @else
                        <div class="w-8.5 h-8.5 md:w-9 md:h-9 rounded-2xl bg-gradient-to-tr from-primary to-amber-500 text-white flex items-center justify-center font-black font-display text-xs md:text-sm shrink-0 shadow-xs border border-orange-300/40 dark:border-slate-700">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="hidden xl:block text-left pr-1">
                        <span class="block text-xs font-black font-display text-slate-800 dark:text-slate-200 leading-none truncate max-w-[130px]">{{ auth()->user()->name }}</span>
                        <span class="block text-[9px] font-bold text-slate-400 mt-0.5 leading-none truncate">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</span>
                    </div>
                    <span class="material-symbols-outlined text-xs text-slate-400 hidden xl:block">expand_more</span>
                </button>

                <div x-show="showProfileMenu" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2.5 w-68 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-expressive shadow-md3-3 z-50 p-4 space-y-3"
                     x-cloak>
                    <div class="pb-3 border-b border-slate-100 dark:border-slate-800 flex items-center gap-3">
                        @if(auth()->user()->avatar_path)
                            <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-2xl object-cover shrink-0 shadow-md">
                        @else
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-primary to-amber-500 text-white flex items-center justify-center font-black font-display shrink-0 text-sm shadow-md">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-black font-display text-sm text-slate-800 dark:text-slate-100 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-2xs font-semibold text-slate-400 truncate mb-1">{{ auth()->user()->email }}</p>
                            <span class="inline-block px-2.5 py-0.5 text-[9px] font-black bg-primary/15 text-primary rounded-full uppercase tracking-wider">
                                {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-1 text-xs">
                        <a href="/profile" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold transition-colors">
                            <span class="material-symbols-outlined text-base text-primary">person</span>
                            Pengaturan Profil Staf
                        </a>
                    </div>

                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-3 p-2.5 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 font-bold text-xs cursor-pointer transition-colors">
                                <span class="material-symbols-outlined text-base">logout</span>
                                Keluar (Logout)
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
