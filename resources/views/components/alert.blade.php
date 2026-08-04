@props(['type' => 'info', 'message' => null])

@php
    $styles = match($type) {
        'success' => ['color' => 'text-emerald-700 dark:text-emerald-400', 'icon' => 'check_circle', 'iconColor' => 'text-emerald-500', 'glow' => 'rgba(34,197,94,0.08)'],
        'danger' => ['color' => 'text-rose-700 dark:text-rose-400', 'icon' => 'error', 'iconColor' => 'text-rose-500', 'glow' => 'rgba(239,68,68,0.08)'],
        'warning' => ['color' => 'text-amber-700 dark:text-amber-400', 'icon' => 'warning', 'iconColor' => 'text-amber-500', 'glow' => 'rgba(245,158,11,0.08)'],
        default => ['color' => 'text-sky-700 dark:text-sky-400', 'icon' => 'info', 'iconColor' => 'text-sky-500', 'glow' => 'rgba(59,130,246,0.08)'],
    };
@endphp

<div {{ $attributes->merge(['class' => 'nm-card-sm p-4 flex gap-3']) }} style="background: {{ $styles['glow'] }};">
    <span class="material-symbols-outlined shrink-0 {{ $styles['iconColor'] }}">
        {{ $styles['icon'] }}
    </span>
    <div class="text-sm font-medium {{ $styles['color'] }}">
        {{ $message ?? $slot }}
    </div>
</div>
