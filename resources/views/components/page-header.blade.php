@props(['title', 'breadcrumbs' => []])

<div {{ $attributes->merge(['class' => 'flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8']) }}>
    <div>
        @if (!empty($breadcrumbs))
            <nav class="flex gap-2 items-center text-xs font-semibold text-slate-400 dark:text-slate-500 mb-2">
                <a href="/dashboard" class="hover:text-primary dark:hover:text-orange-400 transition-colors">Home</a>
                @foreach ($breadcrumbs as $label => $link)
                    <span class="material-symbols-outlined text-xs leading-none">chevron_right</span>
                    @if ($loop->last)
                        <span class="text-slate-600 dark:text-slate-400">{{ $label }}</span>
                    @else
                        <a href="{{ $link }}" class="hover:text-primary dark:hover:text-orange-400 transition-colors">{{ $label }}</a>
                    @endif
                @endforeach
            </nav>
        @endif
        
        <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 dark:text-slate-200 tracking-tight">
            {{ $title }}
        </h1>
    </div>

    @if (isset($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>
