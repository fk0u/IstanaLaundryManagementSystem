@props(['messages'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-xs font-semibold text-rose-600 space-y-1 mt-1.5']) }}>
        @foreach ((array) $messages as $message)
            <li class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm shrink-0">error</span>
                <span>{{ $message }}</span>
            </li>
        @endforeach
    </ul>
@endif
