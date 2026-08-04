@props(['title' => null, 'footer' => null, 'compact' => false, 'variant' => 'elevated'])

@php
    $cardClasses = match($variant) {
        'filled' => 'bg-surface-container dark:bg-slate-900/90 border border-transparent rounded-expressive shadow-none',
        'outlined' => 'bg-white dark:bg-slate-900 border border-surface-outline/50 dark:border-slate-800 rounded-expressive shadow-none',
        default => 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-expressive shadow-md3-1 hover:shadow-md3-2 transition-all duration-300',
    };
@endphp

<div {{ $attributes->merge(['class' => $cardClasses . ' overflow-hidden']) }}>
    @if ($title || isset($header))
        <div class="px-5 md:px-7 py-4 border-b border-slate-100 dark:border-slate-800/80 flex items-center justify-between bg-surface-container-low/60 dark:bg-slate-800/30">
            @if (isset($header))
                {{ $header }}
            @else
                <h3 class="text-base md:text-lg font-black font-display text-slate-800 dark:text-slate-100 tracking-tight">
                    {{ $title }}
                </h3>
            @endif
        </div>
    @endif

    <div class="{{ $compact ? 'p-4 md:p-5' : 'p-5 md:p-7' }}">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="px-5 md:px-7 py-4 border-t border-slate-100 dark:border-slate-800/80 bg-surface-container-low/40 dark:bg-slate-800/20">
            {{ $footer }}
        </div>
    @endif
</div>
