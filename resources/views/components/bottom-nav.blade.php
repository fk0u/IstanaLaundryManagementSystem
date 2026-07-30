{{-- Bottom Navigation Bar — Mobile & Tablet --}}
<nav class="fixed bottom-0 inset-x-0 z-50 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-t border-slate-200/80 dark:border-slate-800/80 lg:hidden safe-area-bottom shadow-md"
     id="bottom-nav">
    <div class="flex items-center justify-around h-16 px-2 max-w-lg mx-auto relative">
        {{-- Dashboard --}}
        <a href="/dashboard" class="flex flex-col items-center justify-center gap-0.5 py-1 px-3 rounded-xl transition-all {{ request()->is('dashboard') ? 'text-primary' : 'text-slate-400 active:text-slate-600 dark:text-slate-500' }}">
            <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' {{ request()->is('dashboard') ? '1' : '0' }};">dashboard</span>
            <span class="text-2xs font-bold tracking-tight">Home</span>
        </a>

        {{-- Production --}}
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Workshop_Staff']))
        <a href="/production" class="flex flex-col items-center justify-center gap-0.5 py-1 px-3 rounded-xl transition-all {{ request()->is('production*') ? 'text-primary' : 'text-slate-400 active:text-slate-600 dark:text-slate-500' }}">
            <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' {{ request()->is('production*') ? '1' : '0' }};">dry_cleaning</span>
            <span class="text-2xs font-bold tracking-tight">Produksi</span>
        </a>
        @endif

        {{-- POS FAB (Center) --}}
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
        <a href="/pos" class="relative -mt-5 flex items-center justify-center w-14 h-14 rounded-2xl bg-primary hover:bg-primary-hover text-white shadow-lg shadow-primary/30 active:scale-95 transition-all {{ request()->is('pos*') ? 'ring-2 ring-primary/30 ring-offset-2 ring-offset-white dark:ring-offset-slate-900' : '' }}">
            <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' 1;">point_of_sale</span>
        </a>
        @endif

        {{-- Orders/Refunds --}}
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
        <a href="/refunds" class="flex flex-col items-center justify-center gap-0.5 py-1 px-3 rounded-xl transition-all {{ request()->is('refunds*') ? 'text-primary' : 'text-slate-400 active:text-slate-600 dark:text-slate-500' }}">
            <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' {{ request()->is('refunds*') ? '1' : '0' }};">receipt_long</span>
            <span class="text-2xs font-bold tracking-tight">Transaksi</span>
        </a>
        @endif

        {{-- More (opens sidebar) --}}
        <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col items-center justify-center gap-0.5 py-1 px-3 rounded-xl transition-all text-slate-400 active:text-slate-600 dark:text-slate-500 cursor-pointer">
            <span class="material-symbols-outlined text-[22px]">menu</span>
            <span class="text-2xs font-bold tracking-tight">Menu</span>
        </button>
    </div>
</nav>
