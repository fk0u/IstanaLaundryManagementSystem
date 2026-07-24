@props(['title' => null, 'footer' => null])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden']) }}>
    @if ($title || isset($header))
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/30">
            @if (isset($header))
                {{ $header }}
            @else
                <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">
                    {{ $title }}
                </h3>
            @endif
        </div>
    @endif

    <div class="p-6">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/10">
            {{ $footer }}
        </div>
    @endif
</div>
