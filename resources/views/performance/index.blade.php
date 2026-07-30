<x-app-layout>
    <div class="flex flex-col gap-5 md:gap-7 pb-8">
        
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm">
            <div>
                <div class="flex items-center gap-2 text-2xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                    <span>/</span>
                    <span class="text-primary font-black">Laporan Kinerja</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black font-display text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-primary text-2xl sm:text-3xl">insights</span>
                    Pemantauan Kinerja & Produktivitas
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Analisis performa transaksi kasir, kepatuhan SLA order, dan produktivitas staf workshop.</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-300 text-2xs font-bold shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sistem Real-Time
                </span>
            </div>
        </div>

        <!-- Filter & Action Toolbar -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm">
            <form method="GET" action="{{ route('performance.index') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                
                <!-- Left: Filter Inputs -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 flex-1">
                    @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                        <div class="flex flex-col gap-1.5">
                            <label class="text-2xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">storefront</span> Cabang
                            </label>
                            <select name="branch_id" onchange="this.form.submit()" class="h-10 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850 font-bold text-slate-800 dark:text-slate-200 px-3 focus:ring-2 focus:ring-primary/20 transition-all cursor-pointer">
                                <option value="">Semua Cabang (Konsolidasi)</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="branch_id" value="{{ $branchId }}">
                    @endif

                    <div class="flex flex-col gap-1.5">
                        <label class="text-2xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">calendar_today</span> Dari Tanggal
                        </label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="h-10 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850 px-3 font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-2xs font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">event</span> Sampai Tanggal
                        </label>
                        <input type="date" name="date_to" value="{{ $dateTo }}" class="h-10 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850 px-3 font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>

                    <div class="flex items-end">
                        <button type="submit" class="w-full h-10 px-4 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center justify-center gap-2 transition-all shadow-sm active:scale-95 cursor-pointer">
                            <span class="material-symbols-outlined text-base">filter_alt</span> Terapkan Filter
                        </button>
                    </div>
                </div>

                <!-- Right: Export Utilities -->
                <div class="flex items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-slate-800 justify-end">
                    <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider hidden xl:inline-block mr-1">Ekspor:</span>
                    
                    <a href="{{ route('performance.export.pdf', ['branch_id' => $branchId, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" target="_blank"
                       class="h-10 px-3.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 transition-all shadow-sm hover:shadow active:scale-95 cursor-pointer"
                       title="Unduh Laporan PDF Resmi dengan Kop Istana Laundry">
                        <span class="material-symbols-outlined text-base">picture_as_pdf</span> PDF
                    </a>
                    
                    <a href="{{ route('performance.export.xlsx', ['branch_id' => $branchId, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                       class="h-10 px-3.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 transition-all shadow-sm hover:shadow active:scale-95 cursor-pointer"
                       title="Ekspor Data ke Spreadsheet Excel (.xlsx)">
                        <span class="material-symbols-outlined text-base">table_chart</span> Excel (.xlsx)
                    </a>
                    
                    <a href="{{ route('performance.export', ['branch_id' => $branchId, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" 
                       class="h-10 px-3.5 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 transition-all shadow-sm hover:shadow active:scale-95 cursor-pointer"
                       title="Ekspor Mentah Format CSV">
                        <span class="material-symbols-outlined text-base">download</span> CSV
                    </a>
                    
                    <a href="{{ route('performance.index') }}" 
                       class="h-10 w-10 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl flex items-center justify-center transition-colors shadow-2xs"
                       title="Reset Filter & Refresh Data">
                        <span class="material-symbols-outlined text-base">refresh</span>
                    </a>
                </div>
            </form>
        </div>

        <!-- Period Summary Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            
            <!-- Order Aktif -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <span class="text-2xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Order Aktif</span>
                    <h3 class="text-xl sm:text-2xl font-black font-mono text-slate-900 dark:text-slate-100 leading-none mb-1.5 whitespace-nowrap">
                        {{ number_format($totalActiveOrders) }} <span class="text-xs font-bold text-slate-400 font-sans">Nota</span>
                    </h3>
                    <div class="flex items-center gap-1.5 text-2xs font-semibold text-orange-600 dark:text-orange-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-ping"></span>
                        <span class="truncate">Antrean Workshop</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-orange-500/10 text-orange-600 dark:bg-orange-950/40 dark:text-orange-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">local_laundry_service</span>
                </div>
            </div>

            <!-- Terlambat (Overdue) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <span class="text-2xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Terlambat (Overdue)</span>
                    <h3 class="text-xl sm:text-2xl font-black font-mono text-slate-900 dark:text-slate-100 leading-none mb-1.5 whitespace-nowrap">
                        {{ number_format($overdueOrders) }} <span class="text-xs font-bold text-slate-400 font-sans">Nota</span>
                    </h3>
                    <div class="flex items-center gap-1 text-2xs font-bold {{ $overdueOrders > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        <span class="px-1.5 py-0.5 rounded-full {{ $overdueOrders > 0 ? 'bg-rose-100 dark:bg-rose-950/60' : 'bg-emerald-100 dark:bg-emerald-950/60' }}">
                            {{ $overdueRate }}%
                        </span>
                        <span class="text-slate-400 font-normal truncate">Melewati SLA</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl {{ $overdueOrders > 0 ? 'bg-rose-500/10 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }} flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">warning</span>
                </div>
            </div>

            <!-- Kepatuhan SLA -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <span class="text-2xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Kepatuhan SLA</span>
                    <h3 class="text-xl sm:text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400 leading-none mb-1.5 whitespace-nowrap">
                        {{ number_format(100 - $overdueRate, 1) }}%
                    </h3>
                    <div class="flex items-center gap-1 text-2xs font-bold text-emerald-600 dark:text-emerald-400">
                        <span class="material-symbols-outlined text-xs">verified</span>
                        <span class="truncate">Tepat Waktu</span>
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">published_with_changes</span>
                </div>
            </div>

            <!-- Omset Periode -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <span class="text-2xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Omset Periode</span>
                    <h3 class="text-base sm:text-lg lg:text-xl font-black font-mono text-slate-900 dark:text-slate-100 leading-tight mb-1.5 whitespace-nowrap overflow-hidden text-ellipsis" title="Rp {{ number_format($periodRevenue, 0, ',', '.') }}">
                        Rp {{ number_format($periodRevenue, 0, ',', '.') }}
                    </h3>
                    <div class="text-2xs font-medium text-slate-400 dark:text-slate-500 truncate">
                        {{ \Carbon\Carbon::parse($dateFrom)->format('d/m') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-primary/10 text-primary dark:bg-slate-800 dark:text-orange-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">payments</span>
                </div>
            </div>

            <!-- Rata-rata Nota -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <span class="text-2xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Rata-rata Nota</span>
                    <h3 class="text-base sm:text-lg lg:text-xl font-black font-mono text-slate-900 dark:text-slate-100 leading-tight mb-1.5 whitespace-nowrap overflow-hidden text-ellipsis" title="Rp {{ number_format($periodAvgOrder, 0, ',', '.') }}">
                        Rp {{ number_format($periodAvgOrder, 0, ',', '.') }}
                    </h3>
                    <div class="text-2xs font-medium text-slate-400 dark:text-slate-500 truncate">
                        {{ number_format($periodOrders) }} transaksi total
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-teal-500/10 text-teal-600 dark:bg-teal-950/40 dark:text-teal-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-2xl">receipt_long</span>
                </div>
            </div>

        </div>

        <!-- Leaderboards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Cashiers Leaderboard (6 cols) -->
            <div class="lg:col-span-6 flex flex-col">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex-1 flex flex-col">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <span class="material-symbols-outlined text-amber-500">point_of_sale</span>
                                Kinerja Kasir
                            </h3>
                            <p class="text-2xs text-slate-400 dark:text-slate-500 mt-0.5">Periode {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-2xs font-extrabold">
                            {{ count($cashiers) }} Kasir Aktif
                        </span>
                    </div>

                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800 text-slate-400 font-extrabold uppercase tracking-wider text-2xs">
                                    <th class="py-3 px-4">Kasir</th>
                                    <th class="py-3 px-3 text-center">Total Nota</th>
                                    <th class="py-3 px-4 text-right">Omset Lunas</th>
                                    <th class="py-3 px-4 text-right">Pending</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @php
                                    $maxRevenue = $cashiers->max('total_revenue') ?: 1;
                                @endphp
                                @forelse($cashiers as $index => $cashier)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-850/50 transition-colors group">
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-3">
                                                <!-- Rank Badge -->
                                                @if($loop->iteration == 1)
                                                    <span class="w-7 h-7 rounded-full bg-amber-400 text-amber-950 font-black text-xs flex items-center justify-center shadow-xs ring-2 ring-amber-200 shrink-0" title="Juara 1">🥇</span>
                                                @elseif($loop->iteration == 2)
                                                    <span class="w-7 h-7 rounded-full bg-slate-300 text-slate-800 font-black text-xs flex items-center justify-center shadow-xs ring-2 ring-slate-200 shrink-0" title="Juara 2">🥈</span>
                                                @elseif($loop->iteration == 3)
                                                    <span class="w-7 h-7 rounded-full bg-amber-700 text-amber-50 font-black text-xs flex items-center justify-center shadow-xs ring-2 ring-amber-600/30 shrink-0" title="Juara 3">🥉</span>
                                                @else
                                                    <span class="w-7 h-7 rounded-full bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 font-extrabold text-2xs flex items-center justify-center shrink-0">
                                                        {{ $loop->iteration }}
                                                    </span>
                                                @endif

                                                <div class="min-w-0">
                                                    <div class="font-extrabold text-slate-900 dark:text-slate-100 group-hover:text-primary transition-colors truncate">
                                                        {{ $cashier->name }}
                                                    </div>
                                                    <!-- Progress mini bar -->
                                                    <div class="w-24 bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mt-1">
                                                        <div class="bg-primary h-full rounded-full" style="width: {{ min(100, round(($cashier->total_revenue / $maxRevenue) * 100)) }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 text-center">
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-300 font-mono text-2xs">
                                                {{ number_format($cashier->total_orders) }} nota
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-black font-mono text-emerald-600 dark:text-emerald-400 text-xs">
                                            Rp {{ number_format($cashier->total_revenue ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-extrabold font-mono text-amber-500 dark:text-amber-400 text-xs">
                                            Rp {{ number_format($cashier->total_pending_revenue ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-slate-400">
                                                <span class="material-symbols-outlined text-4xl mb-2">person_search</span>
                                                <p class="text-xs font-semibold">Belum ada data transaksi kasir di periode ini.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Workshop Staff Productivity (6 cols) -->
            <div class="lg:col-span-6 flex flex-col">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex-1 flex flex-col">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <span class="material-symbols-outlined text-blue-500">engineering</span>
                                Produktivitas Staf Workshop
                            </h3>
                            <p class="text-2xs text-slate-400 dark:text-slate-500 mt-0.5">Aktivitas pengerjaan & transisi status produksi</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-2xs font-extrabold">
                            {{ count($staffProductivity) }} Staf Aktif
                        </span>
                    </div>

                    <div class="overflow-x-auto flex-1">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800 text-slate-400 font-extrabold uppercase tracking-wider text-2xs">
                                    <th class="py-3 px-4">Nama Staf</th>
                                    <th class="py-3 px-3 text-center">Aksi Transisi</th>
                                    <th class="py-3 px-3 text-center">Order Unik</th>
                                    <th class="py-3 px-4 text-right">Selesai (SIAP)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @forelse($staffProductivity as $staff)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-850/50 transition-colors group">
                                        <td class="py-3.5 px-4 font-extrabold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 transition-colors">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 font-black text-2xs flex items-center justify-center border border-blue-200/60 dark:border-blue-800/60">
                                                    {{ strtoupper(substr($staff->staff_name, 0, 1)) }}
                                                </div>
                                                <span>{{ $staff->staff_name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 text-center">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 font-extrabold text-2xs border border-blue-100 dark:border-blue-900/40">
                                                <span class="material-symbols-outlined text-xs">touch_app</span>
                                                {{ number_format($staff->total_actions) }}x
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-3 text-center font-bold text-slate-700 dark:text-slate-300 font-mono text-2xs">
                                            {{ $staff->unique_orders }} nota
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <span class="inline-flex items-center gap-1 font-black font-mono text-emerald-600 dark:text-emerald-400 text-xs">
                                                <span class="material-symbols-outlined text-xs">check_circle</span>
                                                {{ number_format($staff->completed_orders) }} Nota
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-slate-400">
                                                <span class="material-symbols-outlined text-4xl mb-2">work_history</span>
                                                <p class="text-xs font-semibold">Belum ada riwayat aktivitas staf workshop di periode ini.</p>
                                                <p class="text-2xs text-slate-400 mt-1">Aktivitas diisi saat staf mengubah status order di menu Produksi.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- Daily Cashier Breakdown -->
        @if($cashierDailyBreakdown->isNotEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-500">calendar_view_day</span>
                            Rincian Harian Transaksi per Kasir
                        </h3>
                        <p class="text-2xs text-slate-400 dark:text-slate-500 mt-0.5">Rekapitulasi pendapatan dan diskon harian</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-2xs font-extrabold">
                        {{ $cashierDailyBreakdown->count() }} Baris Rekap
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800 text-slate-400 font-extrabold uppercase tracking-wider text-2xs">
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Kasir</th>
                                <th class="py-3 px-3 text-center">Nota</th>
                                <th class="py-3 px-4 text-right">Omset Lunas</th>
                                <th class="py-3 px-4 text-right">Pending</th>
                                <th class="py-3 px-4 text-right">Total Diskon</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @foreach($cashierDailyBreakdown as $row)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-850/50 transition-colors">
                                    <td class="py-3 px-4 font-bold text-slate-800 dark:text-slate-200">
                                        <div class="flex items-center gap-2">
                                            <span>{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</span>
                                            @if(\Carbon\Carbon::parse($row->date)->isToday())
                                                <span class="px-2 py-0.5 bg-primary/10 text-primary text-[9px] font-black rounded-full uppercase tracking-wider">Hari Ini</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-extrabold text-slate-700 dark:text-slate-300">
                                        {{ $row->cashier?->name ?? 'N/A' }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-300 font-mono text-2xs">
                                            {{ $row->total_orders }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-black font-mono text-emerald-600 dark:text-emerald-400">
                                        Rp {{ number_format($row->paid_revenue, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono {{ $row->pending_revenue > 0 ? 'text-amber-500 dark:text-amber-400 font-extrabold' : 'text-slate-400' }}">
                                        Rp {{ number_format($row->pending_revenue, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-rose-600 dark:text-rose-400 font-bold">
                                        Rp {{ number_format($row->total_discount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
