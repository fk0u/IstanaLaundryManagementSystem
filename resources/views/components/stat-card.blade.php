@props(['title', 'value', 'icon', 'description' => null, 'trend' => null, 'trendType' => 'success'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 md:p-6 rounded-expressive shadow-md3-1 hover:shadow-md3-2 transition-all duration-300 flex items-center justify-between gap-4']) }}>
    <div class="flex-1 min-w-0">
        <span class="text-2xs md:text-xs font-bold font-sans text-slate-400 dark:text-slate-500 uppercase tracking-widest block mb-1">
            {{ $title }}
        </span>
        <h3 class="text-xl md:text-2xl lg:text-3xl font-black font-display text-slate-800 dark:text-slate-100 leading-tight mb-1 truncate">
            {{ $value }}
        </h3>
        
        @if ($trend || $description)
            <div class="flex items-center gap-1.5 text-2xs md:text-xs">
                @if ($trend)
                    <span class="font-bold px-2 py-0.5 rounded-full {{ $trendType === 'success' ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400' }}">
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

    <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl bg-primary-container text-primary-on-container dark:bg-slate-800 dark:text-orange-400 flex items-center justify-center shrink-0 shadow-sm">
        @if($icon === 'local_laundry_service')
            <img alt="Istana Laundry Logo" class="w-8 h-8 md:w-10 md:h-10 object-contain" src="{{ asset('images/logo.webp') }}"/>
        @else
            <span class="material-symbols-outlined text-2xl md:text-3xl">
                {{ $icon }}
            </span>
        @endif
    </div>
</div>
