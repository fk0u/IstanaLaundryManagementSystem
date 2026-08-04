@props(['title', 'value', 'icon', 'description' => null, 'trend' => null, 'trendType' => 'success', 'truncateValue' => false])

<div {{ $attributes->merge(['class' => 'group relative overflow-hidden p-4 sm:p-5 flex items-center justify-between gap-3 select-none nm-card hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.99]']) }}>
    <div class="flex-1 min-w-0 z-10">
        <span class="text-[10px] font-extrabold font-sans uppercase tracking-widest block mb-1 truncate" style="color: var(--text-tertiary);">
            {{ $title }}
        </span>
        <h3 class="text-xl sm:text-2xl lg:text-3xl font-black font-display leading-tight mb-1.5 tracking-tight {{ $truncateValue ? 'truncate' : 'whitespace-nowrap overflow-hidden text-ellipsis' }}" style="color: var(--text-primary);">
            {{ $value }}
        </h3>
        
        @if ($trend || $description)
            <div class="flex items-center gap-1.5 text-2xs sm:text-xs">
                @if ($trend)
                    <span class="nm-badge-inset inline-flex items-center gap-0.5 font-extrabold px-2 py-0.5 shrink-0 {{ $trendType === 'success' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        <span class="material-symbols-outlined text-xs">{{ $trendType === 'success' ? 'trending_up' : 'trending_down' }}</span>
                        {{ $trend }}
                    </span>
                @endif
                @if ($description)
                    <span class="font-semibold truncate" style="color: var(--text-tertiary);">
                        {{ $description }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    {{-- Icon Container: Convex embossed circle --}}
    <div class="w-11 h-11 sm:w-13 sm:h-13 flex items-center justify-center shrink-0 z-10 group-hover:scale-105 transition-transform duration-300" style="border-radius: var(--radius-md); background: var(--nm-surface-high); box-shadow: var(--nm-convex);">
        @if($icon === 'local_laundry_service')
            <img alt="Istana Laundry Logo" class="w-6 h-6 sm:w-7 sm:h-7 object-contain" src="{{ asset('images/logo.webp') }}"/>
        @else
            <span class="material-symbols-outlined text-2xl sm:text-3xl text-primary">
                {{ $icon }}
            </span>
        @endif
    </div>
</div>
