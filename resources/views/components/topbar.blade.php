<header class="flex flex-col w-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-xl border-b border-slate-200/80 dark:border-slate-800/80 z-40 sticky top-0 transition-colors duration-200 shadow-2xs"
        x-data="{ 
            showNotifications: false, 
            showQuickAction: false, 
            showProfileMenu: false,
            showSupportMenu: false 
        }">
    <!-- Tier 1: Main App Bar Row -->
    <div class="flex items-center justify-between min-h-[58px] md:min-h-[64px] px-3 sm:px-6 w-full gap-2">
        <div class="flex items-center gap-2.5 min-w-0">
            <!-- Hamburger Menu for Mobile / Tablet -->
            <button @click="sidebarOpen = !sidebarOpen" class="p-2 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200 lg:hidden cursor-pointer rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all shrink-0">
                <span class="material-symbols-outlined text-[24px]">menu</span>
            </button>

            <!-- Sidebar Toggle Icon for Desktop -->
            <button @click="desktopSidebarOpen = !desktopSidebarOpen; localStorage.setItem('desktopSidebarOpen', desktopSidebarOpen)" 
                    class="hidden lg:flex p-2 text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-orange-400 cursor-pointer rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-all shrink-0 items-center justify-center"
                    :title="desktopSidebarOpen ? 'Ciutkan Sidebar' : 'Perluas Sidebar'">
                <span class="material-symbols-outlined text-[24px] transition-transform duration-300"
                      :class="{ 'rotate-180': !desktopSidebarOpen }">
                    menu_open
                </span>
            </button>

            <!-- App Logo & Brand Header -->
            <div class="flex items-center gap-2 shrink-0">
                <img alt="Istana Laundry Logo" class="h-8 w-auto object-contain" src="{{ asset('images/logo.webp') }}"/>
                <div class="hidden md:flex flex-col">
                    <h2 class="text-sm sm:text-base font-black font-display text-primary dark:text-orange-400 tracking-tight leading-none whitespace-nowrap">Istana Laundry</h2>
                    <span class="text-[9px] font-extrabold text-slate-400 uppercase tracking-widest leading-none mt-0.5">Enterprise Suite</span>
                </div>
            </div>
        </div>

    <!-- Header Right Controls -->
    <div class="flex items-center gap-2 md:gap-3 shrink-0">

        <!-- 1. Dark Mode Toggle -->
        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                class="p-2.5 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200 rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 cursor-pointer transition-all" 
                title="Toggle Dark Mode">
            <span class="material-symbols-outlined text-2xl" x-show="!darkMode">dark_mode</span>
            <span class="material-symbols-outlined text-2xl" x-show="darkMode" x-cloak>light_mode</span>
        </button>

        <!-- 2. Support & Help Center Button -->
        <div class="relative" @click.away="showSupportMenu = false">
            <button @click="showSupportMenu = !showSupportMenu; showNotifications = false; showQuickAction = false; showProfileMenu = false"
                    class="p-2.5 text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-orange-400 rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 transition-all cursor-pointer"
                    title="Pusat Bantuan & Support">
                <span class="material-symbols-outlined text-2xl">help_outline</span>
            </button>

            <!-- Support Menu Dropdown -->
            <div x-show="showSupportMenu" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-3 w-80 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-expressive shadow-md3-3 z-50 p-4 space-y-3"
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
                        Panduan Training & SOP Staf (Friendly Guide)
                    </a>
                    <a href="/production" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                        <span class="material-symbols-outlined text-amber-500 text-lg">checklist</span>
                        SOP Alur Produksi Cucian
                    </a>
                </div>
            </div>
        </div>

        <!-- 3. Notifications Bell Dropdown -->
        @php
            $activeCount = \App\Models\Order::whereNotIn('production_status', ['DIAMBIL'])->count();
            $recentOrders = \App\Models\Order::latest()->take(4)->get();
        @endphp
        <div class="relative" @click.away="showNotifications = false">
            <button @click="showNotifications = !showNotifications; showQuickAction = false; showSupportMenu = false; showProfileMenu = false" 
                    class="p-2.5 text-slate-500 dark:text-slate-400 hover:text-primary rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 transition-all cursor-pointer relative"
                    title="Notifikasi">
                <span class="material-symbols-outlined text-2xl">notifications</span>
                @if($activeCount > 0)
                    <span class="absolute top-2 right-2 w-3 h-3 bg-primary rounded-full ring-2 ring-white dark:ring-slate-900 animate-pulse"></span>
                @endif
            </button>

            <!-- Notifications Dropdown -->
            <div x-show="showNotifications" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-3 w-88 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-expressive shadow-md3-3 z-50 p-4 space-y-3"
                 x-cloak>
                <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="font-black font-display text-sm text-slate-800 dark:text-slate-100">Notifikasi System</span>
                        <span class="px-2.5 py-0.5 text-2xs font-extrabold bg-primary-container text-primary-on-container rounded-full">{{ $activeCount }} Order Aktif</span>
                    </div>
                    <a href="/production" class="text-2xs font-bold text-primary hover:underline">Lihat Semua</a>
                </div>

                <div class="space-y-2 max-h-72 overflow-y-auto">
                    @forelse($recentOrders as $notifOrder)
                        <a href="/production" class="flex items-start gap-3 p-2.5 rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800/60 transition-colors">
                            <span class="w-9 h-9 rounded-full bg-primary-container text-primary flex items-center justify-center shrink-0">
                                <img alt="Istana Laundry Logo" class="w-6 h-6 object-contain" src="{{ asset('images/logo.webp') }}"/>
                            </span>
                            <div class="min-w-0 flex-1 text-xs">
                                <p class="font-extrabold text-slate-800 dark:text-slate-200 truncate">#{{ $notifOrder->order_number }} - {{ $notifOrder->customer?->name }}</p>
                                <p class="text-2xs font-medium text-slate-400 mt-0.5">Status: <span class="font-bold text-primary">{{ $notifOrder->production_status }}</span> • {{ $notifOrder->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400 font-medium text-center py-4">Tidak ada notifikasi baru.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 4. Quick Action Button Dropdown -->
        <div class="relative" @click.away="showQuickAction = false">
            <button @click="showQuickAction = !showQuickAction; showNotifications = false; showSupportMenu = false; showProfileMenu = false" 
                    class="md3-btn-tonal !min-h-[42px] !px-4 !rounded-full text-xs font-bold hidden sm:flex items-center gap-2 cursor-pointer shadow-sm">
                <span class="material-symbols-outlined text-lg text-primary">add_circle</span>
                <span>Quick Action</span>
                <span class="material-symbols-outlined text-base transition-transform" :class="{ 'rotate-180': showQuickAction }">expand_more</span>
            </button>

            <!-- Quick Action Dropdown Menu -->
            <div x-show="showQuickAction" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-3 w-64 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-expressive shadow-md3-3 z-50 p-2 space-y-1"
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

        <!-- 5. User Profile Avatar & Dropdown Menu -->
        <div class="relative" @click.away="showProfileMenu = false">
            <button @click="showProfileMenu = !showProfileMenu; showNotifications = false; showQuickAction = false; showSupportMenu = false"
                    class="flex items-center gap-2 cursor-pointer p-1 rounded-full hover:ring-4 hover:ring-primary/20 transition-all">
                <div class="w-9 h-9 md:w-10 md:h-10 rounded-full overflow-hidden border-2 border-primary/30 shrink-0 shadow-sm">
                    <img class="w-full h-full object-cover" 
                         alt="Profile Picture" 
                         src="https://lh3.googleusercontent.com/aida-public/AB6AXuD9Yo8eZudS1XZIORWPZfki2FC4WzJDnIC-GyJ7h-hOvAeMwQCevFfCprrGYz9o_P748aVm3W0-hXbeTH4YCNNYsTQyIRs2lc-jvfmR6kACE9WFglgCX7-7qnDMnMIYcHGJuIi6JavXqVc4Oj1Sg8xDgHMWNeN7bSooQeYSdLCtTRGE7mXPtWfBk2CX1YpEwiwIMDRNKfRr017XvUUSWyrPN1NXN0_2mxQMmjQ__RgIgccXO3YoXp83qNrf1ZO2sebtlUu8NXWylI4"/>
                </div>
            </button>

            <!-- User Profile Dropdown -->
            <div x-show="showProfileMenu" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-3 w-72 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-expressive shadow-md3-3 z-50 p-4 space-y-3"
                 x-cloak>
                <div class="pb-3 border-b border-slate-100 dark:border-slate-800">
                    <p class="font-black font-display text-base text-slate-800 dark:text-slate-100 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-2xs font-semibold text-slate-400 truncate mb-2">{{ auth()->user()->email }}</p>
                    <span class="inline-block px-3 py-1 text-2xs font-extrabold bg-primary-container text-primary-on-container rounded-full uppercase tracking-wider">
                        {{ auth()->user()->roles->first()?->name ?? 'User' }}
                    </span>
                </div>

                <div class="space-y-1 text-xs">
                    <a href="/profile" class="flex items-center gap-3 p-2.5 rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                        <span class="material-symbols-outlined text-lg text-slate-400">person</span>
                        Pengaturan Profil Staf
                    </a>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 p-2.5 rounded-2xl hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 font-bold text-xs cursor-pointer">
                            <span class="material-symbols-outlined text-lg">logout</span>
                            Keluar (Logout)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tier 2: Secondary Flutter Floating Toolbar (Zero Overlap Guarantee) -->
    <div class="px-3 sm:px-6 py-1.5 bg-slate-50/80 dark:bg-slate-950/60 border-t border-slate-200/50 dark:border-slate-800/50 flex items-center justify-between gap-2 overflow-x-auto scrollbar-none">
        <div class="flex items-center gap-2 shrink-0">
            <!-- Branch Scope Switcher Pill -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <form action="{{ route('switch-branch') }}" method="POST" class="inline-flex items-center">
                    @csrf
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined text-slate-400 text-xs absolute left-2.5 pointer-events-none">storefront</span>
                        <select name="branch_id" onchange="this.form.submit()"
                                class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-2xs md:text-xs font-bold pl-7 pr-7 py-1 outline-none focus:ring-2 focus:ring-primary/20 text-slate-700 dark:text-slate-300 cursor-pointer shadow-2xs transition-all">
                            <option value="">Global (Semua Cabang)</option>
                            @foreach(\App\Models\Branch::orderBy('name')->get() as $br)
                                <option value="{{ $br->id }}" {{ session('scoped_branch_id') == $br->id ? 'selected' : '' }}>
                                    {{ $br->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            @else
                <span class="text-2xs font-bold text-primary bg-primary/10 text-primary px-3 py-1 rounded-full border border-primary/20 whitespace-nowrap shadow-2xs flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-xs">storefront</span>
                    {{ auth()->user()->branch?->name ?? 'N/A' }}
                </span>
            @endif

            <!-- Active Status Badge -->
            <div class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-2xs font-bold border border-emerald-500/20">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>System Online</span>
            </div>
        </div>

        <!-- Quick Access Shortcuts (Mobile/Tablet) -->
        <div class="flex items-center gap-1.5 shrink-0">
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
                <a href="/pos" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all text-2xs font-bold shadow-2xs">
                    <span class="material-symbols-outlined text-xs">point_of_sale</span>
                    <span>POS Kasir</span>
                </a>
            @endif
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Workshop_Staff']))
                <a href="/production" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-sky-500/10 text-sky-600 dark:text-sky-400 hover:bg-sky-500 hover:text-white transition-all text-2xs font-bold shadow-2xs">
                    <span class="material-symbols-outlined text-xs">precision_manufacturing</span>
                    <span>Produksi</span>
                </a>
            @endif
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/finance/reports" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500 hover:text-white transition-all text-2xs font-bold shadow-2xs">
                    <span class="material-symbols-outlined text-xs">analytics</span>
                    <span>Laporan</span>
                </a>
            @endif
        </div>
    </div>
</header>
