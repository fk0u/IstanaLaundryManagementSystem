@props(['title', 'value', 'icon', 'description' => null, 'trend' => null, 'trendType' => 'success', 'truncateValue' => false])

<div {{ $attributes->merge(['class' => 'group relative overflow-hidden bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 rounded-expressive shadow-sm hover:shadow-md3-2 hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99] transition-all duration-200 flex items-center justify-between gap-3 select-none']) }}>
    <!-- Background Glow Accent -->
    <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-primary/5 dark:bg-primary/10 blur-xl pointer-events-none group-hover:scale-150 transition-transform duration-500"></div>

    <div class="flex-1 min-w-0 z-10">
        <span class="text-[10px] font-extrabold font-sans text-slate-400 dark:text-slate-500 uppercase tracking-widest block mb-1 truncate">
            {{ $title }}
        </span>
        <h3 class="text-xl sm:text-2xl lg:text-3xl font-black font-display text-slate-900 dark:text-slate-100 leading-tight mb-1.5 tracking-tight {{ $truncateValue ? 'truncate' : 'whitespace-nowrap overflow-hidden text-ellipsis' }}">
            {{ $value }}
        </h3>
        
        @if ($trend || $description)
            <div class="flex items-center gap-1.5 text-2xs sm:text-xs">
                @if ($trend)
                    <span class="inline-flex items-center gap-0.5 font-extrabold px-2 py-0.5 rounded-full shrink-0 {{ $trendType === 'success' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-800/50' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/50 dark:border-rose-800/50' }}">
                        <span class="material-symbols-outlined text-xs">{{ $trendType === 'success' ? 'trending_up' : 'trending_down' }}</span>
                        {{ $trend }}
                    </span>
                @endif
                @if ($description)
                    <span class="text-slate-400 dark:text-slate-500 font-semibold truncate">
                        {{ $description }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <div class="w-11 h-11 sm:w-13 sm:h-13 rounded-2xl bg-gradient-to-br from-primary/10 to-amber-500/10 dark:from-primary/20 dark:to-amber-500/20 text-primary dark:text-orange-400 flex items-center justify-center shrink-0 shadow-xs border border-primary/10 z-10 group-hover:scale-105 transition-transform duration-300">
        @if($icon === 'local_laundry_service')
            <img alt="Istana Laundry Logo" class="w-6 h-6 sm:w-7 sm:h-7 object-contain" src="{{ asset('images/logo.webp') }}"/>
        @else
            <span class="material-symbols-outlined text-2xl sm:text-3xl">
                {{ $icon }}
            </span>
        @endif
    </div>
</div>
