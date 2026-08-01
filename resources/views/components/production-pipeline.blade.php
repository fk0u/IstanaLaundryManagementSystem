@props([
    'breakdown' => [],
    'activeCount' => null,
])

@php
    $pb = array_merge([
        'TERIMA' => 0, 'PILAH' => 0, 'CUCI' => 0,
        'KERING' => 0, 'LIPAT' => 0, 'CEK' => 0, 'SIAP' => 0
    ], $breakdown);

    $totalInWorkshop = $pb['TERIMA'] + $pb['PILAH'] + $pb['CUCI'] + $pb['KERING'] + $pb['LIPAT'] + $pb['CEK'];
    $totalSiap = $pb['SIAP'];

    $stages = [
        [
            'code' => 'TERIMA',
            'label' => 'Terima Cucian',
            'short' => 'Terima',
            'icon' => 'inbox',
            'count' => $pb['TERIMA'],
            'activeBg' => 'from-sky-500 to-sky-600 text-white shadow-md shadow-sky-500/20 border-sky-600',
            'badgeBg' => 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/20',
            'accent' => 'bg-sky-500',
        ],
        [
            'code' => 'PILAH',
            'label' => 'Pilah & Sortir',
            'short' => 'Pilah',
            'icon' => 'inventory',
            'count' => $pb['PILAH'],
            'activeBg' => 'from-indigo-500 to-indigo-600 text-white shadow-md shadow-indigo-500/20 border-indigo-600',
            'badgeBg' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
            'accent' => 'bg-indigo-500',
        ],
        [
            'code' => 'CUCI',
            'label' => 'Pencucian Mesin',
            'short' => 'Cuci',
            'icon' => 'water_drop',
            'count' => $pb['CUCI'],
            'activeBg' => 'from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/20 border-blue-700',
            'badgeBg' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
            'accent' => 'bg-blue-600',
        ],
        [
            'code' => 'KERING',
            'label' => 'Pengeringan',
            'short' => 'Kering',
            'icon' => 'air',
            'count' => $pb['KERING'],
            'activeBg' => 'from-amber-500 to-amber-600 text-white shadow-md shadow-amber-500/20 border-amber-600',
            'badgeBg' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
            'accent' => 'bg-amber-500',
        ],
        [
            'code' => 'LIPAT',
            'label' => 'Setrika & Lipat',
            'short' => 'Lipat & Setrika',
            'icon' => 'iron',
            'count' => $pb['LIPAT'],
            'activeBg' => 'from-orange-500 to-orange-600 text-white shadow-md shadow-orange-500/20 border-orange-600',
            'badgeBg' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
            'accent' => 'bg-orange-500',
        ],
        [
            'code' => 'CEK',
            'label' => 'Quality Control',
            'short' => 'QC / Cek',
            'icon' => 'fact_check',
            'count' => $pb['CEK'],
            'activeBg' => 'from-purple-600 to-purple-700 text-white shadow-md shadow-purple-500/20 border-purple-700',
            'badgeBg' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
            'accent' => 'bg-purple-600',
        ],
        [
            'code' => 'SIAP',
            'label' => 'Siap Diambil',
            'short' => 'Siap Ambil',
            'icon' => 'task_alt',
            'count' => $pb['SIAP'],
            'activeBg' => 'from-emerald-600 to-emerald-700 text-white shadow-md shadow-emerald-500/20 border-emerald-700',
            'badgeBg' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
            'accent' => 'bg-emerald-600',
        ],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-4 sm:p-5 shadow-xs space-y-4 relative overflow-hidden']) }}>
    
    <!-- Top Header Ribbon -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <!-- Native Icon Container with Pulsing Beacon -->
            <div class="w-9 h-9 rounded-xl bg-orange-500/10 dark:bg-slate-800 text-primary flex items-center justify-center shrink-0 border border-orange-500/20 relative shadow-2xs">
                <span class="material-symbols-outlined text-xl">precision_manufacturing</span>
                @if ($totalInWorkshop > 0)
                    <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-primary animate-ping"></span>
                    <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-primary"></span>
                @endif
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="text-sm font-extrabold text-slate-900 dark:text-white truncate">Status Produksi Workshop</h4>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-primary/10 text-primary border border-primary/20">
                        {{ $totalInWorkshop }} Dalam Antrean
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        {{ $totalSiap }} Siap Ambil
                    </span>
                </div>
            </div>
        </div>

        <a href="{{ route('production.index') }}" 
           class="btn-touch shrink-0 px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-primary hover:text-white text-slate-700 dark:text-slate-200 text-2xs font-extrabold flex items-center gap-1 transition-all border border-slate-200/60 dark:border-slate-700 shadow-2xs group">
            <span>Buka Kanban</span>
            <span class="material-symbols-outlined text-sm group-hover:translate-x-0.5 transition-transform">chevron_right</span>
        </a>
    </div>

    <!-- ================================================================= -->
    <!-- MOBILE & TABLET DEDICATED IMMERSIVE UI (< lg screens)             -->
    <!-- ================================================================= -->
    <div class="block lg:hidden">
        <!-- Touch-Optimized Horizontal Stage Card Carousel -->
        <div class="flex items-stretch gap-2.5 overflow-x-auto py-1 px-0.5 no-scrollbar scroll-smooth snap-x snap-mandatory touch-pan-x">
            @foreach ($stages as $index => $stage)
                <a href="{{ route('production.index', ['status' => $stage['code']]) }}"
                   title="Filter Status: {{ $stage['label'] }}"
                   class="btn-touch shrink-0 snap-start w-36 p-3 rounded-2xl border flex flex-col justify-between gap-2.5 transition-all duration-150 active:scale-95 touch-manipulation select-none cursor-pointer relative overflow-hidden
                          {{ $stage['count'] > 0 ? 'bg-gradient-to-br ' . $stage['activeBg'] : 'bg-slate-50/90 dark:bg-slate-800/60 border-slate-200/70 dark:border-slate-800 text-slate-600 dark:text-slate-300' }}">
                    
                    <!-- Card Top: Stage Index & Count Pill -->
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[10px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-md
                                     {{ $stage['count'] > 0 ? 'bg-white/20 text-white' : 'bg-slate-200/60 dark:bg-slate-700 text-slate-500 dark:text-slate-400' }}">
                            0{{ $index + 1 }}
                        </span>

                        <span class="material-symbols-outlined text-base {{ $stage['count'] > 0 ? 'text-white' : 'text-slate-400' }}">
                            {{ $stage['icon'] }}
                        </span>
                    </div>

                    <!-- Card Body: Label & Count Number -->
                    <div class="mt-1">
                        <span class="block text-2xl font-black {{ $stage['count'] > 0 ? 'text-white' : 'text-slate-900 dark:text-slate-100' }}">
                            {{ $stage['count'] }} <span class="text-[10px] font-semibold {{ $stage['count'] > 0 ? 'text-white/80' : 'text-slate-400' }}">Nota</span>
                        </span>
                        <p class="text-xs font-bold truncate mt-0.5 {{ $stage['count'] > 0 ? 'text-white' : 'text-slate-800 dark:text-slate-200' }}">
                            {{ $stage['short'] }}
                        </p>
                    </div>

                    <!-- Card Bottom Accent Progress Line -->
                    <div class="w-full h-1 rounded-full overflow-hidden {{ $stage['count'] > 0 ? 'bg-white/30' : 'bg-slate-200 dark:bg-slate-700' }}">
                        <div class="h-full {{ $stage['count'] > 0 ? 'bg-white w-full' : 'w-0' }}"></div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- DESKTOP INTEGRATED PIPELINE RIBBON (>= lg screens)               -->
    <!-- ================================================================= -->
    <div class="hidden lg:block">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar scroll-smooth">
            @foreach ($stages as $index => $stage)
                <a href="{{ route('production.index', ['status' => $stage['code']]) }}"
                   title="Filter Status: {{ $stage['label'] }}"
                   class="btn-touch shrink-0 inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border text-xs font-bold transition-all duration-200 cursor-pointer group
                          {{ $stage['count'] > 0 ? 'bg-gradient-to-r ' . $stage['activeBg'] : 'bg-slate-50 dark:bg-slate-800/60 border-slate-200/70 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:border-primary/50' }}">
                    
                    <!-- Stage Dot Indicator -->
                    <span class="w-2 h-2 rounded-full {{ $stage['count'] > 0 ? 'bg-white animate-pulse' : 'bg-slate-300 dark:bg-slate-600' }}"></span>

                    <!-- Icon & Label -->
                    <span class="material-symbols-outlined text-base {{ $stage['count'] > 0 ? 'text-white' : 'text-slate-400 group-hover:text-primary' }}">{{ $stage['icon'] }}</span>
                    <span class="truncate font-extrabold">{{ $stage['short'] }}</span>

                    <!-- Count Badge -->
                    <span class="ml-0.5 px-2 py-0.5 rounded-md text-[10px] font-black 
                                 {{ $stage['count'] > 0 ? 'bg-white/20 text-white' : 'bg-slate-200/80 dark:bg-slate-700 text-slate-700 dark:text-slate-200' }}">
                        {{ $stage['count'] }}
                    </span>
                </a>

                @if (!$loop->last)
                    <span class="material-symbols-outlined text-slate-300 dark:text-slate-700 text-sm shrink-0 pointer-events-none">chevron_right</span>
                @endif
            @endforeach
        </div>
    </div>

</div>
