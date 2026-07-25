<header class="flex justify-between items-center h-14 md:h-16 px-4 md:px-6 lg:px-8 w-full bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-b border-outline-variant dark:border-slate-800 z-40 sticky top-0 transition-colors duration-200"
        x-data="{ 
            showNotifications: false, 
            showQuickAction: false, 
            showProfileMenu: false,
            showSupportMenu: false 
        }">
    <div class="flex items-center gap-2 md:gap-4 min-w-0">
        <!-- Hamburger Menu for Mobile -->
        <button @click="sidebarOpen = !sidebarOpen" class="p-1.5 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 md:hidden cursor-pointer rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0">
            <span class="material-symbols-outlined text-[22px]">menu</span>
        </button>

        <!-- Sidebar Toggle Icon for Desktop/Tablet -->
        <button @click="desktopSidebarOpen = !desktopSidebarOpen; localStorage.setItem('desktopSidebarOpen', desktopSidebarOpen)" 
                class="hidden md:flex p-2 text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-orange-400 cursor-pointer rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors shrink-0 items-center justify-center"
                :title="desktopSidebarOpen ? 'Ciutkan Sidebar' : 'Perluas Sidebar'">
            <span class="material-symbols-outlined text-[22px] transition-transform duration-300"
                  :class="{ 'rotate-180': !desktopSidebarOpen }">
                menu_open
            </span>
        </button>

        <!-- Brand Logo & Name - Desktop -->
        <div class="hidden md:flex items-center gap-3">
            <img alt="Istana Laundry Logo" class="h-8 w-auto object-contain" src="{{ asset('images/logo.webp') }}"/>
            <h2 class="text-base md:text-lg font-black text-primary dark:text-orange-400 tracking-tight whitespace-nowrap">Istana Laundry Samarinda</h2>
        </div>
        
        <!-- Mobile Brand -->
        <div class="flex md:hidden items-center gap-2">
            <img alt="Istana Laundry Logo" class="h-6 w-auto object-contain" src="{{ asset('images/logo.webp') }}"/>
            <h2 class="text-sm font-extrabold text-primary dark:text-orange-400 tracking-tight truncate">Istana Laundry</h2>
        </div>

        <!-- Branch Scope Switcher -->
        <div class="flex gap-2 md:gap-4 items-center shrink-0">
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <form action="{{ route('switch-branch') }}" method="POST" class="inline-flex items-center">
                    @csrf
                    <label class="text-2xs font-bold text-slate-400 dark:text-slate-500 mr-1.5 uppercase tracking-wider hidden lg:inline">Scope:</label>
                    <select name="branch_id" onchange="this.form.submit()"
                            class="bg-slate-50 dark:bg-slate-950 border border-outline-variant dark:border-slate-800 rounded-lg text-2xs md:text-[10px] font-bold px-2 py-1.5 md:py-1 outline-none focus:border-primary text-slate-700 dark:text-slate-300 cursor-pointer max-w-[120px] md:max-w-none">
                        <option value="">Global</option>
                        @foreach(\App\Models\Branch::orderBy('name')->get() as $br)
                            <option value="{{ $br->id }}" {{ session('scoped_branch_id') == $br->id ? 'selected' : '' }}>
                                {{ $br->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @else
                <span class="text-2xs md:text-[10px] font-bold text-primary bg-primary-container/10 px-2 py-1 rounded-lg border border-primary/20 whitespace-nowrap">
                    {{ auth()->user()->branch?->name ?? 'N/A' }}
                </span>
            @endif
        </div>
    </div>

    <!-- Header Right Controls -->
    <div class="flex items-center gap-1.5 md:gap-2.5 shrink-0">

        <!-- 1. Dark Mode Toggle -->
        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" 
                class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors" 
                title="Toggle Dark Mode">
            <span class="material-symbols-outlined text-xl" x-show="!darkMode">dark_mode</span>
            <span class="material-symbols-outlined text-xl" x-show="darkMode" x-cloak>light_mode</span>
        </button>

        <!-- 2. Support & Help Center Button -->
        <div class="relative" @click.away="showSupportMenu = false">
            <button @click="showSupportMenu = !showSupportMenu; showNotifications = false; showQuickAction = false; showProfileMenu = false"
                    class="p-2 text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-orange-400 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                    title="Pusat Bantuan & Support">
                <span class="material-symbols-outlined text-xl">help_outline</span>
            </button>

            <!-- Support Menu Dropdown -->
            <div x-show="showSupportMenu" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-72 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-50 p-3 space-y-2"
                 x-cloak>
                <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-slate-800">
                    <span class="material-symbols-outlined text-primary">support_agent</span>
                    <div>
                        <h4 class="font-bold text-xs text-slate-800 dark:text-slate-200">Pusat Bantuan ERP</h4>
                        <p class="text-2xs text-slate-400">Dukungan teknis & Operasional</p>
                    </div>
                </div>
                <div class="space-y-1 text-xs">
                    <a href="https://wa.me/6281234567890?text=Halo%20IT%20Support%20Istana%20Laundry%2C%20saya%20butuh%20bantuan" target="_blank" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold">
                        <span class="material-symbols-outlined text-emerald-500 text-base">chat</span>
                        WhatsApp IT Helpdesk
                    </a>
                    <a href="/docs/user-guide" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold">
                        <span class="material-symbols-outlined text-sky-500 text-base">auto_stories</span>
                        Panduan Penggunaan 8 Roles
                    </a>
                    <a href="/production" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold">
                        <span class="material-symbols-outlined text-amber-500 text-base">checklist</span>
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
                    class="p-2 text-slate-500 dark:text-slate-400 hover:text-primary rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition-all cursor-pointer relative"
                    title="Notifikasi">
                <span class="material-symbols-outlined text-xl">notifications</span>
                @if($activeCount > 0)
                    <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-primary rounded-full ring-2 ring-white dark:ring-slate-900 animate-pulse"></span>
                @endif
            </button>

            <!-- Notifications Dropdown -->
            <div x-show="showNotifications" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-50 p-4 space-y-3"
                 x-cloak>
                <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-xs text-slate-800 dark:text-slate-200">Notifikasi</span>
                        <span class="px-2 py-0.5 text-2xs font-extrabold bg-orange-100 text-primary rounded-full">{{ $activeCount }} Order Aktif</span>
                    </div>
                    <a href="/production" class="text-2xs font-bold text-primary hover:underline">Lihat Semua</a>
                </div>

                <div class="space-y-2.5 max-h-64 overflow-y-auto">
                    @forelse($recentOrders as $notifOrder)
                        <a href="/production" class="flex items-start gap-2.5 p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                            <span class="w-8 h-8 rounded-full bg-orange-50 text-primary flex items-center justify-center shrink-0">
                                <img alt="Istana Laundry Logo" class="w-6 h-6 object-contain" src="{{ asset('images/logo.webp') }}"/>
                            </span>
                            <div class="min-w-0 flex-1 text-xs">
                                <p class="font-bold text-slate-800 dark:text-slate-200 truncate">#{{ $notifOrder->order_number }} - {{ $notifOrder->customer?->name }}</p>
                                <p class="text-2xs text-slate-400">Status: <span class="font-bold text-primary">{{ $notifOrder->production_status }}</span> • {{ $notifOrder->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Tidak ada notifikasi baru.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 4. Quick Action Button Dropdown -->
        <div class="relative" @click.away="showQuickAction = false">
            <button @click="showQuickAction = !showQuickAction; showNotifications = false; showSupportMenu = false; showProfileMenu = false" 
                    class="px-3.5 py-1.5 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs hover:bg-slate-50 dark:hover:bg-slate-800 transition-all active:scale-[0.98] hidden sm:flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-base text-primary">add_circle</span>
                <span>Quick Action</span>
                <span class="material-symbols-outlined text-sm transition-transform" :class="{ 'rotate-180': showQuickAction }">expand_more</span>
            </button>

            <!-- Quick Action Dropdown Menu -->
            <div x-show="showQuickAction" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-50 p-2 space-y-1"
                 x-cloak>
                
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
                    <a href="/pos" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-orange-50 dark:hover:bg-orange-950/30 text-slate-700 dark:text-slate-300 font-bold text-xs">
                        <span class="material-symbols-outlined text-primary text-base">point_of_sale</span>
                        Buat Transaksi Kasir (POS)
                    </a>
                @endif

                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin']))
                    <a href="/production" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs">
                        <span class="material-symbols-outlined text-sky-500 text-base">precision_manufacturing</span>
                        Kelola Antrean Produksi
                    </a>
                @endif

                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Finance']))
                    <a href="/hr" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs">
                        <span class="material-symbols-outlined text-emerald-500 text-base">person_add</span>
                        Tambah Data Karyawan
                    </a>
                    <a href="/finance/reports" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-xs">
                        <span class="material-symbols-outlined text-purple-500 text-base">analytics</span>
                        Lihat Laporan Keuangan
                    </a>
                @endif
            </div>
        </div>

        <!-- 5. User Profile Avatar & Dropdown Menu -->
        <div class="relative" @click.away="showProfileMenu = false">
            <button @click="showProfileMenu = !showProfileMenu; showNotifications = false; showQuickAction = false; showSupportMenu = false"
                    class="flex items-center gap-2 cursor-pointer p-0.5 rounded-full hover:ring-2 hover:ring-primary/40 transition-all">
                <div class="w-8 h-8 md:w-9 md:h-9 rounded-full overflow-hidden border border-outline-variant dark:border-slate-800 shrink-0">
                    <img class="w-full h-full object-cover" 
                         alt="Profile Picture" 
                         src="https://lh3.googleusercontent.com/aida-public/AB6AXuD9Yo8eZudS1XZIORWPZfki2FC4WzJDnIC-GyJ7h-hOvAeMwQCevFfCprrGYz9o_P748aVm3W0-hXbeTH4YCNNYsTQyIRs2lc-jvfmR6kACE9WFglgCX7-7qnDMnMIYcHGJuIi6JavXqVc4Oj1Sg8xDgHMWNeN7bSooQeYSdLCtTRGE7mXPtWfBk2CX1YpEwiwIMDRNKfRr017XvUUSWyrPN1NXN0_2mxQMmjQ__RgIgccXO3YoXp83qNrf1ZO2sebtlUu8NXWylI4"/>
                </div>
            </button>

            <!-- User Profile Dropdown -->
            <div x-show="showProfileMenu" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-64 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-50 p-3 space-y-2"
                 x-cloak>
                <div class="pb-2 border-b border-slate-100 dark:border-slate-800">
                    <p class="font-black text-sm text-slate-800 dark:text-slate-200 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-2xs text-slate-400 truncate mb-1.5">{{ auth()->user()->email }}</p>
                    <span class="inline-block px-2 py-0.5 text-2xs font-extrabold bg-orange-100 text-primary rounded-md uppercase">
                        {{ auth()->user()->roles->first()?->name ?? 'User' }}
                    </span>
                </div>

                <div class="space-y-1 text-xs">
                    <a href="/profile" class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold">
                        <span class="material-symbols-outlined text-base text-slate-400">person</span>
                        Pengaturan Profil
                    </a>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 p-2 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 font-bold text-xs cursor-pointer">
                            <span class="material-symbols-outlined text-base">logout</span>
                            Keluar (Logout)
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</header>
