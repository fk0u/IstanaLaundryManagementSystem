@props(['title', 'value', 'icon', 'description' => null, 'trend' => null, 'trendType' => 'success'])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-6 rounded-xl shadow-sm flex items-center justify-between']) }}>
    <div class="flex-1">
        <span class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">
            {{ $title }}
        </span>
        <h3 class="text-2xl font-bold text-slate-800 dark:text-slate-200 leading-none mb-2">
            {{ $value }}
        </h3>
        
        @if ($trend || $description)
            <div class="flex items-center gap-1.5 text-xs">
                @if ($trend)
                    <span class="font-semibold {{ $trendType === 'success' ? 'text-emerald-600 dark:text-emerald-500' : 'text-rose-600 dark:text-rose-500' }}">
                        {{ $trend }}
                    </span>
                @endif
                @if ($description)
                    <span class="text-slate-400 dark:text-slate-500">
                        {{ $description }}
                    </span>
                @endif
            </div>
        @endif
    </div>

    <div class="w-12 h-12 rounded-lg bg-orange-50 dark:bg-slate-800 text-primary dark:text-orange-400 flex items-center justify-center">
        <span class="material-symbols-outlined text-2xl">
            {{ $icon }}
        </span>
    </div>
</div>
