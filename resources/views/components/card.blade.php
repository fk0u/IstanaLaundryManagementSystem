@props(['title' => null, 'footer' => null, 'compact' => false, 'variant' => 'elevated'])

@php
    $cardClasses = match($variant) {
        'filled' => 'nm-card-flat',
        'outlined' => 'nm-card-flat',
        'inset' => 'nm-card-inset',
        default => 'nm-card',
    };
@endphp

<div {{ $attributes->merge(['class' => $cardClasses . ' overflow-hidden']) }}>
    @if ($title || isset($header))
        <div class="px-5 md:px-7 py-4 flex items-center justify-between" style="border-bottom: 1px solid rgba(0,0,0,0.04);">
            @if (isset($header))
                {{ $header }}
            @else
                <h3 class="text-base md:text-lg font-black font-display tracking-tight" style="color: var(--text-primary);">
                    {{ $title }}
                </h3>
            @endif
        </div>
    @endif

    <div class="{{ $compact ? 'p-4 md:p-5' : 'p-5 md:p-7' }}">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="px-5 md:px-7 py-4" style="border-top: 1px solid rgba(0,0,0,0.04);">
            {{ $footer }}
        </div>
    @endif
</div>
