@props(['title', 'value', 'icon', 'description' => null, 'trend' => null, 'trendType' => 'success', 'truncateValue' => false])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 md:p-5 rounded-expressive shadow-md3-1 hover:shadow-md3-2 transition-all duration-300 flex items-center justify-between gap-3']) }}>
    <div class="flex-1 min-w-0">
        <span class="text-2xs font-bold font-sans text-slate-400 dark:text-slate-500 uppercase tracking-widest block mb-1 truncate">
            {{ $title }}
        </span>
        <h3 class="text-lg md:text-xl lg:text-2xl font-black font-display text-slate-900 dark:text-slate-100 leading-tight mb-1 tracking-tight {{ $truncateValue ? 'truncate' : 'whitespace-nowrap overflow-hidden text-ellipsis' }}">
            {{ $value }}
        </h3>
        
        @if ($trend || $description)
            <div class="flex items-center gap-1.5 text-2xs md:text-xs">
                @if ($trend)
                    <span class="font-bold px-2 py-0.5 rounded-full shrink-0 {{ $trendType === 'success' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400' }}">
                        {{ $trend }}
                    </span>
                @endif
                @if ($description)
                    <span class="text-slate-400 dark:text-slate-500 font-medium truncate">
                        {{ $description }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <div class="w-10 h-10 md:w-12 md:h-12 rounded-2xl bg-primary/10 text-primary dark:bg-slate-800 dark:text-orange-400 flex items-center justify-center shrink-0 shadow-sm">
        @if($icon === 'local_laundry_service')
            <img alt="Istana Laundry Logo" class="w-6 h-6 md:w-8 md:h-8 object-contain" src="{{ asset('images/logo.webp') }}"/>
        @else
            <span class="material-symbols-outlined text-xl md:text-2xl">
                {{ $icon }}
            </span>
        @endif
    </div>
</div>
