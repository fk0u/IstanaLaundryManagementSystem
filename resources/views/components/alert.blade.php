@props(['type' => 'info', 'message' => null])

@php
    $styles = match($type) {
        'success' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-950/10',
            'border' => 'border-emerald-100 dark:border-emerald-900/30',
            'text' => 'text-emerald-800 dark:text-emerald-400',
            'icon' => 'check_circle',
            'iconColor' => 'text-emerald-500'
        ],
        'danger' => [
            'bg' => 'bg-rose-50 dark:bg-rose-950/10',
            'border' => 'border-rose-100 dark:border-rose-900/30',
            'text' => 'text-rose-800 dark:text-rose-400',
            'icon' => 'error',
            'iconColor' => 'text-rose-500'
        ],
        'warning' => [
            'bg' => 'bg-amber-50 dark:bg-amber-950/10',
            'border' => 'border-amber-100 dark:border-amber-900/30',
            'text' => 'text-amber-800 dark:text-amber-400',
            'icon' => 'warning',
            'iconColor' => 'text-amber-500'
        ],
        default => [
            'bg' => 'bg-sky-50 dark:bg-sky-950/10',
            'border' => 'border-sky-100 dark:border-sky-900/30',
            'text' => 'text-sky-800 dark:text-sky-400',
            'icon' => 'info',
            'iconColor' => 'text-sky-500'
        ]
    };
@endphp

<div {{ $attributes->merge(['class' => 'border rounded-xl p-4 flex gap-3 ' . $styles['bg'] . ' ' . $styles['border']]) }}>
    <span class="material-symbols-outlined shrink-0 {{ $styles['iconColor'] }}">
        {{ $styles['icon'] }}
    </span>
    <div class="text-sm font-medium {{ $styles['text'] }}">
        {{ $message ?? $slot }}
    </div>
</div>
