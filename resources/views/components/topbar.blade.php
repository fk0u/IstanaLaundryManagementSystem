<header class="h-16 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 sticky top-0 z-30 px-6 flex items-center justify-between transition-colors duration-200">
    <div class="flex items-center gap-4">
        <!-- Hamburger Menu for Mobile -->
        <button @click="sidebarOpen = !sidebarOpen" class="p-1 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 md:hidden cursor-pointer">
            <span class="material-symbols-outlined text-2xl">menu</span>
        </button>

        <!-- Branch Indicator -->
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-slate-400 dark:text-slate-500">store</span>
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider leading-none mb-0.5">Cabang Aktif</span>
                <span class="text-sm font-bold text-slate-800 dark:text-slate-200">
                    @if(session('scoped_branch_id'))
                        {{ \App\Models\Branch::find(session('scoped_branch_id'))?->name ?? 'Unknown Branch' }}
                    @elseif(auth()->user()->branch_id)
                        {{ auth()->user()->branch?->name ?? 'Unknown Branch' }}
                    @else
                        Semua Cabang (Global)
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Right Side Actions -->
    <div class="flex items-center gap-4">
        <!-- Dark Mode Toggle -->
        <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors" title="Toggle Dark Mode">
            <span class="material-symbols-outlined text-xl" x-show="!darkMode">dark_mode</span>
            <span class="material-symbols-outlined text-xl" x-show="darkMode" x-cloak>light_mode</span>
        </button>

        <!-- Notification Center -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors relative">
                <span class="material-symbols-outlined text-xl">notifications</span>
                <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-rose-500 border-2 border-white dark:border-slate-900 rounded-full"></span>
            </button>
            
            <div x-show="open" @click.away="open = false" x-cloak
                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl shadow-xl z-50 py-2 overflow-hidden">
                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200">Notifikasi</span>
                    <span class="text-xs text-primary font-semibold">Tandai dibaca</span>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <a href="#" class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 flex gap-3 border-b border-slate-50 dark:border-slate-800/30">
                        <span class="w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-950/20 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-sm">inventory_2</span>
                        </span>
                        <div>
                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Stok Deterjen Menipis</span>
                            <span class="block text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Cabang Samarinda Kota • 2 jam yang lalu</span>
                        </div>
                    </a>
                    <a href="#" class="px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 flex gap-3">
                        <span class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-950/20 text-emerald-600 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-sm">local_shipping</span>
                        </span>
                        <div>
                            <span class="block text-xs font-bold text-slate-800 dark:text-slate-200">Order #SMD-0001 Siap Diambil</span>
                            <span class="block text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">Pelanggan: Budi Santoso • 4 jam yang lalu</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- User Dropdown (Breeze Profile + Logout) -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-2 cursor-pointer focus:outline-none">
                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-white font-bold text-sm">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-350 hidden sm:inline-block">
                    {{ auth()->user()->name }}
                </span>
                <span class="material-symbols-outlined text-slate-400 dark:text-slate-500 text-sm hidden sm:inline-block">
                    keyboard_arrow_down
                </span>
            </button>

            <div x-show="open" @click.away="open = false" x-cloak
                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl shadow-xl z-50 py-1 overflow-hidden">
                <a href="/profile" class="px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/50 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-400 text-base">person</span>
                    Profil Saya
                </a>
                <div class="border-t border-slate-100 dark:border-slate-800 my-1"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/10 flex items-center gap-2 cursor-pointer">
                        <span class="material-symbols-outlined text-rose-500 text-base">logout</span>
                        Keluar Sistem
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
