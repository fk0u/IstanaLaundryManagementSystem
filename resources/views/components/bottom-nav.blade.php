{{-- Bottom Navigation Bar — Mobile & Tablet --}}
<nav class="fixed bottom-0 inset-x-0 z-50 bg-white/90 dark:bg-slate-900/90 backdrop-blur-2xl border-t border-slate-200/80 dark:border-slate-800/80 lg:hidden safe-area-bottom shadow-2xl transition-all"
     id="bottom-nav">
    <div class="flex items-center justify-around h-16 px-3 max-w-lg mx-auto relative">
        {{-- Dashboard --}}
        <a href="/dashboard" class="flex flex-col items-center justify-center gap-0.5 py-1.5 px-3 rounded-2xl min-w-[56px] transition-all haptic-press select-none {{ request()->is('dashboard') ? 'bg-primary/10 text-primary font-extrabold dark:bg-primary/20' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}">
            <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' {{ request()->is('dashboard') ? '1' : '0' }};">dashboard</span>
            <span class="text-[10px] font-extrabold tracking-tight">Home</span>
        </a>

        {{-- Production --}}
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Workshop_Staff']))
        <a href="/production" class="flex flex-col items-center justify-center gap-0.5 py-1.5 px-3 rounded-2xl min-w-[56px] transition-all haptic-press select-none {{ request()->is('production*') ? 'bg-primary/10 text-primary font-extrabold dark:bg-primary/20' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}">
            <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' {{ request()->is('production*') ? '1' : '0' }};">dry_cleaning</span>
            <span class="text-[10px] font-extrabold tracking-tight">Produksi</span>
        </a>
        @endif

        {{-- POS FAB (Center) --}}
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
        <a href="/pos" class="relative -mt-6 flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-tr from-primary to-amber-500 hover:brightness-110 text-white shadow-xl shadow-primary/35 active:scale-90 transition-all {{ request()->is('pos*') ? 'ring-4 ring-primary/30 ring-offset-2 ring-offset-white dark:ring-offset-slate-900 scale-105' : '' }}" title="Point of Sale">
            <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' 1;">point_of_sale</span>
        </a>
        @endif

        {{-- Orders/Refunds --}}
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
        <a href="/orders" class="flex flex-col items-center justify-center gap-0.5 py-1.5 px-3 rounded-2xl min-w-[56px] transition-all haptic-press select-none {{ request()->is('orders*') || request()->is('refunds*') ? 'bg-primary/10 text-primary font-extrabold dark:bg-primary/20' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200' }}">
            <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' {{ request()->is('orders*') || request()->is('refunds*') ? '1' : '0' }};">receipt_long</span>
            <span class="text-[10px] font-extrabold tracking-tight">Transaksi</span>
        </a>
        @endif

        {{-- More (opens sidebar) --}}
        <button @click="sidebarOpen = !sidebarOpen" class="flex flex-col items-center justify-center gap-0.5 py-1.5 px-3 rounded-2xl min-w-[56px] transition-all haptic-press select-none text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 cursor-pointer">
            <span class="material-symbols-outlined text-[22px]" :class="{ 'text-primary': sidebarOpen }">menu</span>
            <span class="text-[10px] font-extrabold tracking-tight">Menu</span>
        </button>
    </div>
</nav>
