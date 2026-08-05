<aside class="h-screen flex flex-col fixed left-0 top-0 bg-white dark:bg-slate-900 border-r border-surface-outline/30 dark:border-slate-800 z-50 transition-all duration-300 ease-in-out"
       :class="{ 
           'w-72': desktopSidebarOpen, 
           'w-20': !desktopSidebarOpen,
           'translate-x-0': sidebarOpen, 
           '-translate-x-full md:translate-x-0': !sidebarOpen 
       }"
       id="sidebar-nav">
    <div class="flex flex-col h-full py-4 overflow-hidden">
        <!-- Brand / Header -->
        <div class="px-3 mb-3">
            <div class="flex items-center justify-between min-h-[44px]">
                <div class="flex items-center gap-3 overflow-hidden" x-show="desktopSidebarOpen" x-transition.opacity>
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-primary via-orange-500 to-amber-500 flex items-center justify-center text-white font-black font-display text-xl shrink-0 shadow-md3-2 hover:scale-105 transition-transform">
                        <img alt="Istana Laundry Logo" class="w-6 h-6 object-contain drop-shadow-xs" src="{{ asset('images/logo.webp') }}"/>
                    </div>
                    <div class="truncate">
                        <h1 class="text-base font-black font-display text-slate-900 dark:text-white tracking-tight leading-none">Istana Laundry</h1>
                        <p class="text-[9px] font-extrabold font-sans text-primary uppercase tracking-widest mt-1">Enterprise Suite</p>
                    </div>
                </div>

                <!-- Mini Logo when collapsed -->
                <div class="mx-auto group relative" x-show="!desktopSidebarOpen" x-transition.opacity>
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-primary via-orange-500 to-amber-500 flex items-center justify-center text-white font-black font-display text-xl shadow-md3-2 hover:scale-110 transition-transform cursor-pointer">
                        <img alt="Istana Laundry Logo" class="w-6 h-6 object-contain drop-shadow-xs" src="{{ asset('images/logo.webp') }}"/>
                    </div>
                    <!-- Tooltip for mini logo -->
                    <div class="absolute left-full ml-3.5 top-1/2 -translate-y-1/2 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-primary shrink-0 animate-pulse"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Istana Laundry ERP</p>
                            <p class="text-[9px] font-bold text-slate-400">Enterprise Multi-Branch Suite</p>
                        </div>
                    </div>
                </div>

                <!-- Close button for mobile -->
                <button @click="sidebarOpen = false" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 md:hidden cursor-pointer rounded-2xl hover:bg-surface-container dark:hover:bg-slate-800 transition-colors">
                    <span class="material-symbols-outlined text-2xl">close</span>
                </button>
            </div>

            <!-- Branch Scope Switcher (Expanded Mode) -->
            <div class="mt-3" x-show="desktopSidebarOpen || sidebarOpen" x-transition.opacity>
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" type="button"
                                class="w-full bg-slate-100 dark:bg-slate-800/90 hover:bg-slate-200/70 dark:hover:bg-slate-800 border border-slate-200/90 dark:border-slate-700/80 rounded-xl text-xs font-extrabold px-3 py-2.5 text-slate-800 dark:text-slate-200 flex items-center justify-between shadow-2xs transition-all cursor-pointer">
                            <div class="flex items-center gap-2 min-w-0 flex-1">
                                <span class="material-symbols-outlined text-primary text-base shrink-0">storefront</span>
                                <span class="truncate text-left">
                                    @if(session('scoped_branch_id'))
                                        {{ \App\Models\Branch::find(session('scoped_branch_id'))?->name ?? 'Global (Semua Cabang)' }}
                                    @else
                                        Global (Semua Cabang)
                                    @endif
                                </span>
                            </div>
                            <span class="material-symbols-outlined text-slate-400 text-base shrink-0 transition-transform duration-200 ml-1" :class="{ 'rotate-180': open }">expand_more</span>
                        </button>

                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute left-0 right-0 mt-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-lg z-50 p-1 space-y-0.5 max-h-56 overflow-y-auto"
                             x-cloak>
                            <form action="{{ route('switch-branch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="branch_id" value="">
                                <button type="submit" class="w-full text-left px-3 py-2 text-xs font-bold rounded-lg flex items-center justify-between transition-colors {{ !session('scoped_branch_id') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <span>Global (Semua Cabang)</span>
                                    @if(!session('scoped_branch_id'))
                                        <span class="material-symbols-outlined text-sm">check</span>
                                    @endif
                                </button>
                            </form>

                            @foreach(\App\Models\Branch::orderBy('name')->get() as $br)
                                <form action="{{ route('switch-branch') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="branch_id" value="{{ $br->id }}">
                                    <button type="submit" class="w-full text-left px-3 py-2 text-xs font-bold rounded-lg flex items-center justify-between transition-colors {{ session('scoped_branch_id') == $br->id ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                        <span class="truncate">{{ $br->name }}</span>
                                        @if(session('scoped_branch_id') == $br->id)
                                            <span class="material-symbols-outlined text-sm">check</span>
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="w-full bg-primary/10 border border-primary/20 rounded-xl text-xs font-bold px-3 py-2 text-primary flex items-center gap-2 truncate">
                        <span class="material-symbols-outlined text-sm">storefront</span>
                        <span class="truncate">{{ auth()->user()->branch?->name ?? 'Cabang Utama' }}</span>
                    </div>
                @endif
            </div>

            <!-- Mini Branch Selector (Minimized Mode) -->
            <div class="mt-3 flex justify-center" x-show="!desktopSidebarOpen && !sidebarOpen">
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                    <div class="relative group" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" type="button"
                                class="w-11 h-11 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-primary/15 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center text-primary shadow-2xs hover:scale-110 transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-xl">storefront</span>
                        </button>

                        <div x-show="!open" class="absolute left-full ml-3.5 top-1/2 -translate-y-1/2 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-primary shrink-0"></div>
                            <div>
                                <p class="text-xs font-black text-white leading-tight">Scope Cabang</p>
                                <p class="text-[9px] font-bold text-slate-400">Aktif: {{ session('scoped_branch_id') ? (\App\Models\Branch::find(session('scoped_branch_id'))?->name ?? 'Global') : 'Global (Semua)' }}</p>
                            </div>
                        </div>

                        <div x-show="open" 
                             x-transition
                             class="absolute left-full ml-3 top-0 w-60 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl z-[999] p-2 space-y-1"
                             x-cloak>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider px-2 py-1">Pilih Cabang (Scope)</p>
                            <form action="{{ route('switch-branch') }}" method="POST">
                                @csrf
                                <input type="hidden" name="branch_id" value="">
                                <button type="submit" class="w-full text-left px-3 py-2 text-xs font-bold rounded-xl flex items-center justify-between transition-colors {{ !session('scoped_branch_id') ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <span>Global (Semua Cabang)</span>
                                    @if(!session('scoped_branch_id'))
                                        <span class="material-symbols-outlined text-sm">check</span>
                                    @endif
                                </button>
                            </form>

                            @foreach(\App\Models\Branch::orderBy('name')->get() as $br)
                                <form action="{{ route('switch-branch') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="branch_id" value="{{ $br->id }}">
                                    <button type="submit" class="w-full text-left px-3 py-2 text-xs font-bold rounded-xl flex items-center justify-between transition-colors {{ session('scoped_branch_id') == $br->id ? 'bg-primary/10 text-primary' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                        <span class="truncate">{{ $br->name }}</span>
                                        @if(session('scoped_branch_id') == $br->id)
                                            <span class="material-symbols-outlined text-sm">check</span>
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Desktop/Tablet Toggle Button (Expand/Collapse Sidebar) -->
        <div class="px-3 mb-2 hidden md:block">
            <button @click="desktopSidebarOpen = !desktopSidebarOpen; localStorage.setItem('desktopSidebarOpen', desktopSidebarOpen)"
                    class="w-full flex items-center gap-3 py-2 text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-orange-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 rounded-2xl transition-all text-xs font-bold group cursor-pointer relative"
                    :class="{ 'px-3.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto': !desktopSidebarOpen }"
                    :title="desktopSidebarOpen ? 'Ciutkan Sidebar' : 'Buka Sidebar'">
                <span class="material-symbols-outlined text-xl transition-transform duration-300 group-hover:scale-110"
                      :class="{ 'rotate-180': !desktopSidebarOpen }">
                    menu_open
                </span>
                <span x-show="desktopSidebarOpen" class="truncate">Sembunyikan Sidebar</span>

                <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-slate-400 shrink-0"></div>
                    <div>
                        <p class="text-xs font-black text-white leading-tight">Perluas Sidebar</p>
                        <p class="text-[9px] font-bold text-slate-400">Tampilkan Menu Lengkap</p>
                    </div>
                </div>
            </button>
        </div>

        <!-- Navigation Links with ICONIC Badges & Floating Hover Tooltip Cards -->
        <nav class="flex-1 space-y-1.5 overflow-y-auto px-3 scrollbar-none">
            <!-- Dashboard -->
            <a href="/dashboard" @click="sidebarOpen = false" 
               class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('dashboard') ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-amber-500/10 hover:text-amber-500' }}"
               :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('dashboard') ? 'bg-amber-500 text-white shadow-sm' : 'bg-amber-500/10 text-amber-500 dark:bg-amber-500/20' }}">
                    <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('dashboard') ? '1' : '0' }};">dashboard</span>
                </div>
                <span x-show="desktopSidebarOpen" class="truncate">Dashboard</span>

                @if(request()->is('dashboard'))
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-amber-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                @endif

                <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></div>
                    <div>
                        <p class="text-xs font-black text-white leading-tight">Executive Dashboard</p>
                        <p class="text-[9px] font-bold text-slate-400">Ringkasan Operasional & Omzet</p>
                    </div>
                </div>
            </a>

            <!-- POS (Cashier) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
                <a href="/pos" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('pos*') ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('pos*') ? 'bg-emerald-500 text-white shadow-sm' : 'bg-emerald-500/10 text-emerald-500 dark:bg-emerald-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('pos*') ? '1' : '0' }};">point_of_sale</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Point of Sale</span>

                    @if(request()->is('pos*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-emerald-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Point of Sale (POS)</p>
                            <p class="text-[9px] font-bold text-slate-400">Kasir Transaksi & Nota</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Rekap Shift (Closing & Settlement Audit) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
                <a href="/shifts" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('shifts*') ? 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-indigo-500/10 hover:text-indigo-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('shifts*') ? 'bg-indigo-500 text-white shadow-sm' : 'bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('shifts*') ? '1' : '0' }};">receipt_long</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Rekap Shift (Closing)</span>

                    @if(request()->is('shifts*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-indigo-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-indigo-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Rekapitulasi Shift</p>
                            <p class="text-[9px] font-bold text-slate-400">Audit Cashier Closing & Settlement</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Refunds -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
                <a href="/refunds" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('refunds*') ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-rose-500/10 hover:text-rose-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('refunds*') ? 'bg-rose-500 text-white shadow-sm' : 'bg-rose-500/10 text-rose-500 dark:bg-rose-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('refunds*') ? '1' : '0' }};">assignment_return</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Refund & Pembatalan</span>

                    @if(request()->is('refunds*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-rose-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Refund & Pembatalan</p>
                            <p class="text-[9px] font-bold text-slate-400">Persetujuan & Audit Refund</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Orders (Semua Transaksi) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
                <a href="{{ route('orders.index') }}" @click="sidebarOpen = false"
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('orders*') ? 'bg-blue-500/15 text-blue-600 dark:text-blue-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-blue-500/10 hover:text-blue-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('orders*') ? 'bg-blue-500 text-white shadow-sm' : 'bg-blue-500/10 text-blue-500 dark:bg-blue-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('orders*') ? '1' : '0' }};">receipt_long</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Semua Transaksi</span>

                    @if(request()->is('orders*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-blue-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Semua Transaksi</p>
                            <p class="text-[9px] font-bold text-slate-400">Riwayat & Status Pelunasan</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Production Tracking -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Workshop_Staff']))
                <a href="/production" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('production*') ? 'bg-purple-500/15 text-purple-600 dark:text-purple-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-purple-500/10 hover:text-purple-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('production*') ? 'bg-purple-500 text-white shadow-sm' : 'bg-purple-500/10 text-purple-500 dark:bg-purple-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('production*') ? '1' : '0' }};">precision_manufacturing</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Production</span>

                    @if(request()->is('production*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-purple-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-purple-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Production Tracking</p>
                            <p class="text-[9px] font-bold text-slate-400">Monitoring Antrean Workshop</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Performance Monitoring -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Finance']))
                <a href="{{ route('performance.index') }}" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('performance*') ? 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-indigo-500/10 hover:text-indigo-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('performance*') ? 'bg-indigo-500 text-white shadow-sm' : 'bg-indigo-500/10 text-indigo-500 dark:bg-indigo-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('performance*') ? '1' : '0' }};">insights</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Memantau Kinerja</span>

                    @if(request()->is('performance*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-indigo-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-indigo-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Memantau Kinerja</p>
                            <p class="text-[9px] font-bold text-slate-400">Analitik Produktivitas Staf</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Section Divider -->
            <div class="pt-3 pb-1 px-4" x-show="desktopSidebarOpen">
                <span class="text-[10px] font-black font-sans text-slate-400 dark:text-slate-600 uppercase tracking-widest">Management</span>
            </div>
            <div class="my-2 border-t border-slate-100 dark:border-slate-800/60 w-8 mx-auto" x-show="!desktopSidebarOpen"></div>

            <!-- Customers (CRM) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'CS_Marketing']))
                <a href="/customers" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('customers*') ? 'bg-sky-500/15 text-sky-600 dark:text-sky-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-sky-500/10 hover:text-sky-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('customers*') ? 'bg-sky-500 text-white shadow-sm' : 'bg-sky-500/10 text-sky-500 dark:bg-sky-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('customers*') ? '1' : '0' }};">groups</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">CRM & Loyalty</span>

                    @if(request()->is('customers*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-sky-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-sky-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">CRM & Loyalty</p>
                            <p class="text-[9px] font-bold text-slate-400">Database & Poin Pelanggan</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Promotions -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'CS_Marketing']))
                <a href="/promotions" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('promotions*') ? 'bg-pink-500/15 text-pink-600 dark:text-pink-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-pink-500/10 hover:text-pink-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('promotions*') ? 'bg-pink-500 text-white shadow-sm' : 'bg-pink-500/10 text-pink-500 dark:bg-pink-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('promotions*') ? '1' : '0' }};">campaign</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Promosi / Kupon</span>

                    @if(request()->is('promotions*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-pink-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-pink-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Manajemen Promosi</p>
                            <p class="text-[9px] font-bold text-slate-400">Voucher Diskon & Campaign</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Services (Master Jenis Layanan) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <a href="{{ route('services.index') }}" @click="sidebarOpen = false"
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('services*') ? 'bg-teal-500/15 text-teal-600 dark:text-teal-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-teal-500/10 hover:text-teal-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('services*') ? 'bg-teal-500 text-white shadow-sm' : 'bg-teal-500/10 text-teal-500 dark:bg-teal-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('services*') ? '1' : '0' }};">dry_cleaning</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Jenis Layanan</span>

                    @if(request()->is('services*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-teal-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-teal-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Master Jenis Layanan</p>
                            <p class="text-[9px] font-bold text-slate-400">Kategori & Tarif Cucian</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Users (Manajemen Staf & Hak Akses) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <a href="{{ route('users.index') }}" @click="sidebarOpen = false"
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('users*') ? 'bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-cyan-500/10 hover:text-cyan-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('users*') ? 'bg-cyan-500 text-white shadow-sm' : 'bg-cyan-500/10 text-cyan-500 dark:bg-cyan-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('users*') ? '1' : '0' }};">manage_accounts</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Manajemen Staf</span>

                    @if(request()->is('users*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-cyan-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-cyan-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Manajemen Staf & Akses</p>
                            <p class="text-[9px] font-bold text-slate-400">Akun Pengguna & Otoritas</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Branches (Manajemen Cabang & Scope) -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                <a href="{{ route('branches.index') }}" @click="sidebarOpen = false"
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('branches*') ? 'bg-amber-600/15 text-amber-700 dark:text-amber-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-amber-600/10 hover:text-amber-600' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('branches*') ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-600/10 text-amber-600 dark:bg-amber-600/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('branches*') ? '1' : '0' }};">store</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Manajemen Cabang</span>

                    @if(request()->is('branches*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-amber-600 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-amber-600 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Manajemen Cabang</p>
                            <p class="text-[9px] font-bold text-slate-400">Master Data Outlets</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Inventory & Procurement -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin']))
                <a href="/inventory" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('inventory*') ? 'bg-orange-500/15 text-orange-600 dark:text-orange-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-orange-500/10 hover:text-orange-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('inventory*') ? 'bg-orange-500 text-white shadow-sm' : 'bg-orange-500/10 text-orange-500 dark:bg-orange-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('inventory*') ? '1' : '0' }};">inventory_2</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Inventory (Stok)</span>

                    @if(request()->is('inventory*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-orange-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-orange-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Inventory & Stok</p>
                            <p class="text-[9px] font-bold text-slate-400">Pengadaan Bahan & Gudang</p>
                        </div>
                    </div>
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
            <div class="pt-3 pb-1 px-4" x-show="desktopSidebarOpen">
                <span class="text-[10px] font-black font-sans text-slate-400 dark:text-slate-600 uppercase tracking-widest">Finance & HR</span>
            </div>
            <div class="my-2 border-t border-slate-100 dark:border-slate-800/60 w-8 mx-auto" x-show="!desktopSidebarOpen"></div>

            <!-- HR & Payroll -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/hr" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('hr*') ? 'bg-violet-500/15 text-violet-600 dark:text-violet-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-violet-500/10 hover:text-violet-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('hr*') ? 'bg-violet-500 text-white shadow-sm' : 'bg-violet-500/10 text-violet-500 dark:bg-violet-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('hr*') ? '1' : '0' }};">badge</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">HR & Payroll</span>

                    @if(request()->is('hr*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-violet-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-violet-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">HR & Payroll</p>
                            <p class="text-[9px] font-bold text-slate-400">Absensi & Gaji Karyawan</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Fixed Assets -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/assets" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('assets*') ? 'bg-fuchsia-500/15 text-fuchsia-600 dark:text-fuchsia-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-fuchsia-500/10 hover:text-fuchsia-500' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('assets*') ? 'bg-fuchsia-500 text-white shadow-sm' : 'bg-fuchsia-500/10 text-fuchsia-500 dark:bg-fuchsia-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('assets*') ? '1' : '0' }};">home_work</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Aset Tetap</span>

                    @if(request()->is('assets*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-fuchsia-500 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-fuchsia-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Manajemen Aset Tetap</p>
                            <p class="text-[9px] font-bold text-slate-400">Penyusutan Mesin & Inventaris</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Accounting -->
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <a href="/finance" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('finance') ? 'bg-emerald-600/15 text-emerald-700 dark:text-emerald-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-emerald-600/10 hover:text-emerald-600' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('finance') ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-600/10 text-emerald-600 dark:bg-emerald-600/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('finance') ? '1' : '0' }};">account_balance_wallet</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Keuangan & COA</span>

                    @if(request()->is('finance'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-emerald-600 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-600 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Keuangan & COA</p>
                            <p class="text-[9px] font-bold text-slate-400">Jurnal Ledger & Laporan R/L</p>
                        </div>
                    </div>
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
                    <a href="/finance/operational-expenses" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-full text-xs font-bold {{ request()->is('finance/operational-expenses*') ? 'text-primary font-black bg-orange-50' : 'text-slate-400 hover:text-slate-700' }}">
                        <span class="material-symbols-outlined text-lg">receipt_long</span>
                        <span class="truncate">Beban Operasional</span>
                    </a>
                    <a href="/finance/supplier-payments" @click="sidebarOpen = false" class="flex items-center gap-3 px-3 py-2 transition-all rounded-full text-xs font-bold {{ request()->is('finance/supplier-payments*') ? 'text-primary font-black bg-orange-50' : 'text-slate-400 hover:text-slate-700' }}">
                        <span class="material-symbols-outlined text-lg">payments</span>
                        <span class="truncate">Pelunasan Supplier</span>
                    </a>
                </div>
            @endif

            <!-- Audit Logs -->
            @if(auth()->user()->hasRole('Developer'))
                <a href="/audit-logs" @click="sidebarOpen = false" 
                   class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('audit-logs*') ? 'bg-slate-500/15 text-slate-700 dark:text-slate-300 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-500/10 hover:text-slate-600' }}"
                   :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('audit-logs*') ? 'bg-slate-600 text-white shadow-sm' : 'bg-slate-500/10 text-slate-500 dark:bg-slate-500/20' }}">
                        <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('audit-logs*') ? '1' : '0' }};">history_toggle_off</span>
                    </div>
                    <span x-show="desktopSidebarOpen" class="truncate">Audit Logs</span>

                    @if(request()->is('audit-logs*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-slate-600 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                    @endif

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-slate-400 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Audit Logs System</p>
                            <p class="text-[9px] font-bold text-slate-400">Jejak Aktivitas & Otorisasi</p>
                        </div>
                    </div>
                </a>
            @endif

            <!-- Panduan & Training Staf -->
            <a href="{{ route('guide') }}" @click="sidebarOpen = false" 
               class="group relative flex items-center transition-all duration-200 rounded-2xl text-xs md:text-sm font-bold {{ request()->is('guide*') ? 'bg-lime-600/15 text-lime-700 dark:text-lime-400 shadow-2xs font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:bg-lime-600/10 hover:text-lime-600' }}"
               :class="{ 'gap-3.5 px-3.5 py-2.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto my-1': !desktopSidebarOpen }">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->is('guide*') ? 'bg-lime-600 text-white shadow-sm' : 'bg-lime-600/10 text-lime-600 dark:bg-lime-600/20' }}">
                    <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' {{ request()->is('guide*') ? '1' : '0' }};">school</span>
                </div>
                <span x-show="desktopSidebarOpen" class="truncate">Panduan Training Staf</span>

                @if(request()->is('guide*'))
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-6 bg-lime-600 rounded-r-full" x-show="!desktopSidebarOpen"></span>
                @endif

                <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-lime-500 shrink-0"></div>
                    <div>
                        <p class="text-xs font-black text-white leading-tight">Panduan Training Staf</p>
                        <p class="text-[9px] font-bold text-slate-400">Modul Operasional & FAQ</p>
                    </div>
                </div>
            </a>
        </nav>

        <!-- New Order Shortcut Button (MD3 FAB Style) -->
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
            <div class="px-3 mt-auto hidden md:block">
                <a href="/pos" 
                   class="group relative w-full md3-btn-primary shadow-md3-2 hover:scale-[1.03] transition-transform flex items-center"
                   :class="{ '!py-3.5 gap-2 justify-center': desktopSidebarOpen, '!py-0 !px-0 !w-11 !h-11 mx-auto justify-center rounded-2xl': !desktopSidebarOpen }">
                    <span class="material-symbols-outlined text-xl">add</span>
                    <span x-show="desktopSidebarOpen" class="truncate font-bold">Order Baru</span>

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-primary shrink-0 animate-pulse"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Buat Order Baru</p>
                            <p class="text-[9px] font-bold text-slate-400">Buka Kasir Point of Sale</p>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        <!-- Footer / Profile & Logout -->
        <div class="mt-3 border-t border-surface-outline/30 dark:border-slate-800 pt-3 px-3">
            <div class="flex items-center gap-3 mb-2 group relative" :class="{ 'justify-center': !desktopSidebarOpen }">
                @if(auth()->user()->avatar_path)
                    <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-2xl object-cover shrink-0 shadow-md hover:scale-105 transition-transform cursor-pointer">
                @else
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-primary to-amber-500 text-white flex items-center justify-center font-black font-display shrink-0 text-sm shadow-md hover:scale-105 transition-transform cursor-pointer">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                @endif
                <div class="overflow-hidden flex-1 min-w-0" x-show="desktopSidebarOpen">
                    <span class="block text-xs font-black font-display text-slate-800 dark:text-slate-200 truncate leading-tight">{{ auth()->user()->name }}</span>
                    <span class="block text-[10px] font-bold text-slate-400 truncate mt-0.5">{{ auth()->user()->getRoleNames()->first() }}</span>
                </div>

                <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 top-1/2 -translate-y-1/2 px-3.5 py-2 bg-slate-950/95 dark:bg-slate-800/95 backdrop-blur-md text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-slate-800 flex items-center gap-2.5">
                    <div class="w-2.5 h-2.5 rounded-full bg-primary shrink-0"></div>
                    <div>
                        <p class="text-xs font-black text-white leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-[9px] font-bold text-amber-400">{{ auth()->user()->getRoleNames()->first() }}</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="group relative w-full flex items-center gap-3 py-2.5 transition-all rounded-2xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 cursor-pointer"
                        :class="{ 'px-3.5': desktopSidebarOpen, 'w-11 h-11 justify-center mx-auto': !desktopSidebarOpen }"
                        :title="!desktopSidebarOpen ? 'Logout' : ''">
                    <span class="material-symbols-outlined text-xl">logout</span>
                    <span x-show="desktopSidebarOpen" class="text-xs font-bold">Logout</span>

                    <div x-show="!desktopSidebarOpen" class="absolute left-full ml-3.5 px-3.5 py-2 bg-rose-950/95 text-white rounded-2xl shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-200 z-[999] whitespace-nowrap border border-rose-800 flex items-center gap-2.5">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500 shrink-0"></div>
                        <div>
                            <p class="text-xs font-black text-white leading-tight">Keluar (Logout)</p>
                            <p class="text-[9px] font-bold text-rose-300">Akhiri Sesi Pengguna</p>
                        </div>
                    </div>
                </button>
            </form>
        </div>
    </div>
</aside>
