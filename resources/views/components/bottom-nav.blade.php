{{-- Bottom Navigation Bar — Mobile & Tablet · Neumorphism --}}
<nav class="fixed bottom-0 inset-x-0 z-50 lg:hidden safe-area-bottom transition-all bg-white dark:bg-slate-900 border-t border-slate-200/80 dark:border-slate-800"
     id="bottom-nav"
     style="background: var(--nm-surface); box-shadow: 0 -4px 20px rgba(0,0,0,0.12), -6px -6px 14px var(--nm-shadow-light);">
    <div class="flex items-center justify-around h-16 px-3 max-w-lg mx-auto relative">
        {{-- Dashboard --}}
        <a href="/dashboard" class="flex flex-col items-center justify-center gap-0.5 py-1.5 px-3 min-w-[56px] transition-all haptic-press select-none {{ request()->is('dashboard') ? 'text-primary font-extrabold' : '' }}" style="{{ request()->is('dashboard') ? 'box-shadow: var(--nm-inset-sm); border-radius: var(--radius-md); background: var(--nm-bg);' : 'color: var(--text-tertiary); border-radius: var(--radius-md);' }}">
            <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' {{ request()->is('dashboard') ? '1' : '0' }};">dashboard</span>
            <span class="text-[10px] font-extrabold tracking-tight">Home</span>
        </a>

        {{-- Production --}}
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Workshop_Admin', 'Workshop_Staff']))
        <a href="/production" class="flex flex-col items-center justify-center gap-0.5 py-1.5 px-3 min-w-[56px] transition-all haptic-press select-none {{ request()->is('production*') ? 'text-primary font-extrabold' : '' }}" style="{{ request()->is('production*') ? 'box-shadow: var(--nm-inset-sm); border-radius: var(--radius-md); background: var(--nm-bg);' : 'color: var(--text-tertiary); border-radius: var(--radius-md);' }}">
            <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' {{ request()->is('production*') ? '1' : '0' }};">dry_cleaning</span>
            <span class="text-[10px] font-extrabold tracking-tight">Produksi</span>
        </a>
        @endif

        {{-- POS FAB (Center): Convex neumorphism embossed button --}}
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
        <a href="/pos"
           class="relative -mt-6 flex items-center justify-center w-14 h-14 text-white active:scale-90 transition-all {{ request()->is('pos*') ? 'scale-105' : '' }}"
           style="border-radius: var(--radius-lg); background: linear-gradient(135deg, #FF6600, #FF8533); box-shadow: 6px 6px 14px rgba(174,78,0,0.40), -4px -4px 10px rgba(255,170,80,0.25), 0 4px 12px rgba(255,102,0,0.35); {{ request()->is('pos*') ? 'box-shadow: inset 3px 3px 6px rgba(174,78,0,0.40), inset -3px -3px 6px rgba(255,170,80,0.20), 0 0 20px rgba(255,102,0,0.30);' : '' }}"
           title="Point of Sale">
            <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' 1;">point_of_sale</span>
        </a>
        @endif

        {{-- Orders/Refunds --}}
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier', 'Finance']))
        <a href="/orders" class="flex flex-col items-center justify-center gap-0.5 py-1.5 px-3 min-w-[56px] transition-all haptic-press select-none {{ request()->is('orders*') || request()->is('refunds*') ? 'text-primary font-extrabold' : '' }}" style="{{ request()->is('orders*') || request()->is('refunds*') ? 'box-shadow: var(--nm-inset-sm); border-radius: var(--radius-md); background: var(--nm-bg);' : 'color: var(--text-tertiary); border-radius: var(--radius-md);' }}">
            <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' {{ request()->is('orders*') || request()->is('refunds*') ? '1' : '0' }};">receipt_long</span>
            <span class="text-[10px] font-extrabold tracking-tight">Transaksi</span>
        </a>
        @endif

        {{-- Halaman Menu (Opens Mobile Menu Sheet) --}}
        <button @click="mobileMenuOpen = true" class="flex flex-col items-center justify-center gap-0.5 py-1.5 px-3 min-w-[56px] transition-all haptic-press select-none cursor-pointer" style="color: var(--text-tertiary); border-radius: var(--radius-md);">
            <span class="material-symbols-outlined text-[22px]" :class="{ 'text-primary': mobileMenuOpen }">widgets</span>
            <span class="text-[10px] font-extrabold tracking-tight">Menu</span>
        </button>
    </div>
</nav>
