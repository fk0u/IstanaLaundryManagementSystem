@props(['title', 'breadcrumbs' => []])

<div {{ $attributes->merge(['class' => 'flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6']) }}>
    <div class="min-w-0">
        @if (!empty($breadcrumbs))
            <nav class="flex gap-2 items-center text-xs font-semibold mb-2 flex-wrap" style="color: var(--text-tertiary);">
                <a href="/dashboard" class="hover:text-primary transition-colors">Home</a>
                @foreach ($breadcrumbs as $label => $link)
                    <span class="material-symbols-outlined text-xs leading-none">chevron_right</span>
                    @if ($loop->last)
                        <span style="color: var(--text-secondary);">{{ $label }}</span>
                    @else
                        <a href="{{ $link }}" class="hover:text-primary transition-colors">{{ $label }}</a>
                    @endif
                @endforeach
            </nav>
        @endif
        
        <h1 class="text-2xl md:text-3xl font-extrabold font-display tracking-tight truncate" style="color: var(--text-primary);">
            {{ $title }}
        </h1>
    </div>

    @if (isset($actions))
        <div class="flex items-center gap-3 flex-wrap shrink-0 max-w-full">
            {{ $actions }}
        </div>
    @endif
</div>
