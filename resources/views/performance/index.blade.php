<x-app-layout>
    {{-- =============================================
         PERFORMANCE MONITORING — ADVANCED ANALYTICS
         ============================================= --}}
    <div class="flex flex-col gap-5 md:gap-6 pb-10" x-data="performanceDash()">

        {{-- ── HEADER ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm">
            <div>
                <div class="flex items-center gap-2 text-2xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                    <span>/</span>
                    <span class="text-primary font-black">Pemantauan Kinerja</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black font-display text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-primary text-2xl sm:text-3xl">monitoring</span>
                    Pemantauan Kinerja &amp; Produktivitas
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Analytics mendalam: performa kasir, pipeline workshop, retensi pelanggan, dan distribusi channel order.</p>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/60 text-emerald-700 dark:text-emerald-300 text-2xs font-bold shadow-2xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Real Data Live
                </span>
            </div>
        </div>

        {{-- ── FILTER TOOLBAR ── --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 sm:p-5 rounded-2xl shadow-sm">
            <form method="GET" action="{{ route('performance.index') }}" class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 flex-1">
                    @if($isGlobalUser)
                        <div class="flex flex-col gap-1.5">
                            <label class="text-2xs font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1">
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
                        <label class="text-2xs font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                            <span class="material-symbols-outlined text-xs">calendar_today</span> Dari Tanggal
                        </label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}" class="h-10 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-850 px-3 font-bold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-2xs font-extrabold text-slate-500 uppercase tracking-wider flex items-center gap-1">
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

                <div class="flex items-center gap-2 pt-2 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-slate-800 justify-end">
                    <span class="text-2xs font-bold text-slate-400 uppercase tracking-wider hidden xl:inline-block mr-1">Ekspor:</span>
                    <a href="{{ route('performance.export.pdf', ['branch_id' => $branchId, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}" target="_blank"
                       class="h-10 px-3.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 transition-all shadow-sm active:scale-95 cursor-pointer">
                        <span class="material-symbols-outlined text-base">picture_as_pdf</span> PDF
                    </a>
                    <a href="{{ route('performance.export.xlsx', ['branch_id' => $branchId, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                       class="h-10 px-3.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 transition-all shadow-sm active:scale-95 cursor-pointer">
                        <span class="material-symbols-outlined text-base">table_chart</span> Excel
                    </a>
                    <a href="{{ route('performance.export', ['branch_id' => $branchId, 'date_from' => $dateFrom, 'date_to' => $dateTo]) }}"
                       class="h-10 px-3.5 bg-slate-700 hover:bg-slate-800 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 transition-all shadow-sm active:scale-95 cursor-pointer">
                        <span class="material-symbols-outlined text-base">download</span> CSV
                    </a>
                    <a href="{{ route('performance.index') }}" class="h-10 w-10 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl flex items-center justify-center transition-colors shadow-2xs" title="Reset Filter">
                        <span class="material-symbols-outlined text-base">refresh</span>
                    </a>
                </div>
            </form>
        </div>

        {{-- ── 6 KPI HERO CARDS ── --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 md:gap-4">
            {{-- Omset Lunas --}}
            <div class="col-span-2 xl:col-span-2 bg-gradient-to-br from-orange-500 to-orange-600 text-white p-5 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-2xs font-extrabold uppercase tracking-wider text-orange-100">Omset Lunas</span>
                    <span class="material-symbols-outlined text-2xl text-orange-200">payments</span>
                </div>
                <p class="text-xl sm:text-2xl font-black font-mono leading-none truncate" title="Rp {{ number_format($periodRevenue, 0, ',', '.') }}">
                    Rp {{ number_format($periodRevenue, 0, ',', '.') }}
                </p>
                <p class="text-2xs text-orange-100 mt-1.5">{{ \Carbon\Carbon::parse($dateFrom)->format('d/m') }} — {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</p>
            </div>

            {{-- Total Nota --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider">Total Nota</span>
                    <span class="material-symbols-outlined text-xl text-blue-500">receipt_long</span>
                </div>
                <p class="text-xl sm:text-2xl font-black font-mono text-slate-900 dark:text-slate-100">{{ number_format($periodOrders) }}</p>
                <p class="text-2xs text-slate-400 mt-1">Avg Rp {{ number_format($periodAvgOrder, 0, ',', '.') }}</p>
            </div>

            {{-- Total Diskon --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider">Total Diskon</span>
                    <span class="material-symbols-outlined text-xl text-rose-500">local_offer</span>
                </div>
                <p class="text-xl sm:text-2xl font-black font-mono text-rose-600 dark:text-rose-400">Rp {{ number_format($periodTotalDiscount, 0, ',', '.') }}</p>
                <p class="text-2xs text-slate-400 mt-1">Voucher &amp; Promo</p>
            </div>

            {{-- Order Aktif --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider">Order Aktif</span>
                    <span class="material-symbols-outlined text-xl text-orange-500">local_laundry_service</span>
                </div>
                <p class="text-xl sm:text-2xl font-black font-mono text-slate-900 dark:text-slate-100">{{ number_format($totalActiveOrders) }}</p>
                <p class="text-2xs {{ $overdueOrders > 0 ? 'text-rose-500' : 'text-emerald-500' }} mt-1 font-semibold">
                    {{ $overdueOrders }} terlambat ({{ $overdueRate }}%)
                </p>
            </div>

            {{-- Retensi Pelanggan --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm hover:shadow-md transition-all duration-300">
                <div class="flex items-start justify-between mb-2">
                    <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider">Retensi</span>
                    <span class="material-symbols-outlined text-xl text-violet-500">people</span>
                </div>
                <p class="text-xl sm:text-2xl font-black font-mono text-violet-600 dark:text-violet-400">{{ $customerRetention['retention_rate'] }}%</p>
                <p class="text-2xs text-slate-400 mt-1">{{ $customerRetention['returning_customers'] }} kembali, {{ $customerRetention['new_customers'] }} baru</p>
            </div>
        </div>

        {{-- ── TAB NAVIGATION ── --}}
        <div class="flex gap-1 bg-slate-100/80 dark:bg-slate-800/50 p-1 rounded-2xl overflow-x-auto no-scrollbar">
            @php
                $tabs = [
                    ['id' => 'overview',   'icon' => 'dashboard',        'label' => 'Overview'],
                    ['id' => 'cashier',    'icon' => 'point_of_sale',     'label' => 'Kasir'],
                    ['id' => 'workshop',   'icon' => 'engineering',       'label' => 'Workshop'],
                    ['id' => 'analytics',  'icon' => 'bar_chart_4_bars',  'label' => 'Analytics'],
                ];
            @endphp
            @foreach($tabs as $tab)
                <button @click="activeTab = '{{ $tab['id'] }}'"
                    :class="activeTab === '{{ $tab['id'] }}' ? 'bg-white dark:bg-slate-900 text-primary shadow-sm font-extrabold' : 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 font-semibold'"
                    class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs transition-all duration-200 whitespace-nowrap flex-shrink-0 cursor-pointer">
                    <span class="material-symbols-outlined text-sm">{{ $tab['icon'] }}</span>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>

        {{-- ======================================================
             TAB 1: OVERVIEW
             ====================================================== --}}
        <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="grid grid-cols-1 xl:grid-cols-12 gap-5">

            {{-- Revenue Trend Line Chart --}}
            <div class="xl:col-span-8 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-primary">trending_up</span>
                            Tren Omset Harian
                        </h3>
                        <p class="text-2xs text-slate-400 mt-0.5">Pendapatan lunas &amp; total transaksi per hari</p>
                    </div>
                </div>
                <div class="p-5" style="height:280px">
                    <canvas id="revenueLineChart"></canvas>
                </div>
            </div>

            {{-- Channel Breakdown --}}
            <div class="xl:col-span-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base text-blue-500">route</span>
                        Channel Order
                    </h3>
                    <p class="text-2xs text-slate-400 mt-0.5">Distribusi outlet vs pickup &amp; delivery</p>
                </div>
                <div class="p-5 flex flex-col gap-4">
                    <div style="height:180px; position:relative">
                        <canvas id="channelDonutChart"></canvas>
                    </div>
                    @php $totalChannel = $channelBreakdown['outlet']['count'] + $channelBreakdown['pickup_delivery']['count']; @endphp
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-xl bg-orange-50 dark:bg-orange-950/20 border border-orange-100 dark:border-orange-900/30">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="material-symbols-outlined text-xs text-orange-500">storefront</span>
                                <span class="text-2xs font-bold text-orange-700 dark:text-orange-400">Langsung Outlet</span>
                            </div>
                            <p class="text-base font-black font-mono text-orange-700 dark:text-orange-300">{{ $channelBreakdown['outlet']['count'] }}</p>
                            <p class="text-2xs text-orange-400">nota</p>
                        </div>
                        <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30">
                            <div class="flex items-center gap-1.5 mb-1">
                                <span class="material-symbols-outlined text-xs text-blue-500">local_shipping</span>
                                <span class="text-2xs font-bold text-blue-700 dark:text-blue-400">Pickup &amp; Delivery</span>
                            </div>
                            <p class="text-base font-black font-mono text-blue-700 dark:text-blue-300">{{ $channelBreakdown['pickup_delivery']['count'] }}</p>
                            <p class="text-2xs text-blue-400">nota</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hourly Heatmap --}}
            <div class="xl:col-span-12 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                    <span class="material-symbols-outlined text-base text-amber-500">schedule</span>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100">Jam Tersibuk</h3>
                        <p class="text-2xs text-slate-400">Distribusi transaksi per jam (00:00 — 23:00)</p>
                    </div>
                </div>
                <div class="p-5" style="height:200px">
                    <canvas id="hourlyBarChart"></canvas>
                </div>
            </div>
        </div>

        {{-- ======================================================
             TAB 2: KASIR
             ====================================================== --}}
        <div x-show="activeTab === 'cashier'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col gap-5">

            {{-- Cashier Leaderboard + Bar Chart --}}
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">

                {{-- Leaderboard Table --}}
                <div class="xl:col-span-7 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <span class="material-symbols-outlined text-base text-amber-500">leaderboard</span>
                                Leaderboard Omset Kasir
                            </h3>
                            <p class="text-2xs text-slate-400 mt-0.5">Periode {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-2xs font-extrabold">
                            {{ count($cashiers) }} Kasir Aktif
                        </span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50/70 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800 text-slate-400 font-extrabold uppercase tracking-wider text-2xs">
                                    <th class="py-3 px-4">Kasir</th>
                                    <th class="py-3 px-3 text-center">Nota</th>
                                    <th class="py-3 px-4 text-right">Omset Lunas</th>
                                    <th class="py-3 px-4 text-right">Pending</th>
                                    <th class="py-3 px-3 text-center">Diskon</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                                @php $maxRevenue = $cashiers->max('total_revenue') ?: 1; @endphp
                                @forelse($cashiers as $index => $cashier)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-850/50 transition-colors group">
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-3">
                                                {{-- Rank Medal (Material Icons, no emoji) --}}
                                                @if($loop->iteration == 1)
                                                    <div class="w-7 h-7 rounded-full bg-amber-400 text-amber-900 flex items-center justify-center shrink-0 ring-2 ring-amber-200 shadow-sm" title="Juara 1">
                                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1">military_tech</span>
                                                    </div>
                                                @elseif($loop->iteration == 2)
                                                    <div class="w-7 h-7 rounded-full bg-slate-300 text-slate-700 flex items-center justify-center shrink-0 ring-2 ring-slate-200 shadow-sm" title="Juara 2">
                                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1">military_tech</span>
                                                    </div>
                                                @elseif($loop->iteration == 3)
                                                    <div class="w-7 h-7 rounded-full bg-amber-700 text-amber-100 flex items-center justify-center shrink-0 ring-2 ring-amber-600/40 shadow-sm" title="Juara 3">
                                                        <span class="material-symbols-outlined text-sm" style="font-variation-settings: 'FILL' 1">military_tech</span>
                                                    </div>
                                                @else
                                                    <div class="w-7 h-7 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center shrink-0 text-2xs font-extrabold">
                                                        {{ $loop->iteration }}
                                                    </div>
                                                @endif

                                                <div class="min-w-0">
                                                    <div class="font-extrabold text-slate-900 dark:text-slate-100 group-hover:text-primary transition-colors truncate">{{ $cashier->name }}</div>
                                                    <div class="w-28 bg-slate-100 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden mt-1">
                                                        <div class="bg-primary h-full rounded-full transition-all duration-700" style="width: {{ min(100, round(($cashier->total_revenue / $maxRevenue) * 100)) }}%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 text-center">
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-300 font-mono text-2xs">{{ number_format($cashier->total_orders) }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-black font-mono text-emerald-600 dark:text-emerald-400 text-xs">
                                            Rp {{ number_format($cashier->total_revenue ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-mono {{ ($cashier->total_pending_revenue ?? 0) > 0 ? 'text-amber-500 font-extrabold' : 'text-slate-300' }} text-xs">
                                            Rp {{ number_format($cashier->total_pending_revenue ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3.5 px-3 text-center">
                                            <span class="px-2 py-0.5 rounded-md {{ ($cashier->total_discount_orders ?? 0) > 0 ? 'bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400' : 'bg-slate-50 dark:bg-slate-800 text-slate-400' }} font-mono font-bold text-2xs">
                                                {{ $cashier->total_discount_orders ?? 0 }}x
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="py-12 text-center">
                                        <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">person_search</span>
                                        <p class="text-xs text-slate-400">Belum ada data kasir di periode ini.</p>
                                    </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Cashier Revenue Bar Chart --}}
                <div class="xl:col-span-5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-primary">bar_chart</span>
                            Perbandingan Omset
                        </h3>
                        <p class="text-2xs text-slate-400 mt-0.5">Omset lunas vs pending per kasir</p>
                    </div>
                    <div class="p-5" style="height:300px">
                        <canvas id="cashierBarChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Daily Breakdown Table --}}
            @if($cashierDailyBreakdown->isNotEmpty())
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <span class="material-symbols-outlined text-base text-emerald-500">calendar_view_day</span>
                                Rincian Harian per Kasir
                            </h3>
                            <p class="text-2xs text-slate-400 mt-0.5">Rekapitulasi pendapatan dan diskon harian</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-2xs font-extrabold">{{ $cashierDailyBreakdown->count() }} Baris</span>
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
                                                {{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}
                                                @if(\Carbon\Carbon::parse($row->date)->isToday())
                                                    <span class="px-1.5 py-0.5 bg-primary/10 text-primary text-[9px] font-black rounded-full uppercase tracking-wider">Hari Ini</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 font-extrabold text-slate-700 dark:text-slate-300">{{ $row->cashier?->name ?? 'N/A' }}</td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="px-2.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-300 font-mono text-2xs">{{ $row->total_orders }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-right font-black font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($row->paid_revenue, 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-right font-mono {{ $row->pending_revenue > 0 ? 'text-amber-500 font-extrabold' : 'text-slate-300' }}">Rp {{ number_format($row->pending_revenue, 0, ',', '.') }}</td>
                                        <td class="py-3 px-4 text-right font-mono text-rose-600 dark:text-rose-400 font-bold">Rp {{ number_format($row->total_discount, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- ======================================================
             TAB 3: WORKSHOP
             ====================================================== --}}
        <div x-show="activeTab === 'workshop'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col gap-5">

            {{-- Pipeline Funnel + Stage Distribution --}}
            <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">

                {{-- Pipeline Funnel (Visual Progress Bars) --}}
                <div class="xl:col-span-5 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-indigo-500">waterfall_chart</span>
                            Pipeline Produksi
                        </h3>
                        <p class="text-2xs text-slate-400 mt-0.5">Distribusi order per tahap saat ini</p>
                    </div>
                    <div class="p-5 space-y-3">
                        @php
                            $stageColors = [
                                'TERIMA' => ['bg' => 'bg-sky-500',    'light' => 'bg-sky-100 dark:bg-sky-950/40',    'text' => 'text-sky-700 dark:text-sky-300'],
                                'PILAH'  => ['bg' => 'bg-violet-500', 'light' => 'bg-violet-100 dark:bg-violet-950/40', 'text' => 'text-violet-700 dark:text-violet-300'],
                                'CUCI'   => ['bg' => 'bg-blue-500',   'light' => 'bg-blue-100 dark:bg-blue-950/40',   'text' => 'text-blue-700 dark:text-blue-300'],
                                'KERING' => ['bg' => 'bg-amber-500',  'light' => 'bg-amber-100 dark:bg-amber-950/40', 'text' => 'text-amber-700 dark:text-amber-300'],
                                'LIPAT'  => ['bg' => 'bg-orange-500', 'light' => 'bg-orange-100 dark:bg-orange-950/40','text' => 'text-orange-700 dark:text-orange-300'],
                                'CEK'    => ['bg' => 'bg-teal-500',   'light' => 'bg-teal-100 dark:bg-teal-950/40',   'text' => 'text-teal-700 dark:text-teal-300'],
                                'SIAP'   => ['bg' => 'bg-emerald-500','light' => 'bg-emerald-100 dark:bg-emerald-950/40','text' => 'text-emerald-700 dark:text-emerald-300'],
                                'DIAMBIL'=> ['bg' => 'bg-slate-500',  'light' => 'bg-slate-100 dark:bg-slate-800',    'text' => 'text-slate-600 dark:text-slate-400'],
                            ];
                            $maxPipeline = $productionPipeline->max('count') ?: 1;
                        @endphp
                        @foreach($productionPipeline as $stage)
                            @php $clr = $stageColors[$stage['stage']] ?? ['bg' => 'bg-slate-400', 'light' => 'bg-slate-100', 'text' => 'text-slate-600']; @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-2xs font-black w-14 shrink-0 {{ $clr['text'] }} uppercase tracking-wider">{{ $stage['stage'] }}</span>
                                <div class="flex-1 {{ $clr['light'] }} rounded-full h-5 overflow-hidden">
                                    <div class="{{ $clr['bg'] }} h-full rounded-full transition-all duration-700 flex items-center justify-end pr-2"
                                         style="width: {{ max(8, round(($stage['count'] / $maxPipeline) * 100)) }}%; min-width: {{ $stage['count'] > 0 ? '2rem' : '0' }}">
                                        @if($stage['count'] > 0)
                                            <span class="text-white text-[9px] font-black">{{ $stage['count'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-xs font-black font-mono text-slate-700 dark:text-slate-300 w-6 text-right shrink-0">{{ $stage['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Stage Donut Chart --}}
                <div class="xl:col-span-4 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-violet-500">donut_large</span>
                            Komposisi Stage
                        </h3>
                        <p class="text-2xs text-slate-400 mt-0.5">Proporsi order tiap tahap produksi</p>
                    </div>
                    <div class="p-5 flex items-center justify-center" style="height:270px">
                        <canvas id="pipelineDonutChart"></canvas>
                    </div>
                </div>

                {{-- Active vs Overdue Alert --}}
                <div class="xl:col-span-3 flex flex-col gap-4">
                    <div class="flex-1 p-5 rounded-2xl border {{ $overdueOrders > 0 ? 'bg-rose-50 dark:bg-rose-950/20 border-rose-200 dark:border-rose-900/40' : 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-900/40' }}">
                        <span class="material-symbols-outlined text-3xl {{ $overdueOrders > 0 ? 'text-rose-500' : 'text-emerald-500' }} mb-2 block" style="font-variation-settings: 'FILL' 1">
                            {{ $overdueOrders > 0 ? 'warning' : 'check_circle' }}
                        </span>
                        <p class="text-2xs font-extrabold {{ $overdueOrders > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }} uppercase tracking-wider">SLA Melewati Deadline</p>
                        <p class="text-3xl font-black font-mono {{ $overdueOrders > 0 ? 'text-rose-700 dark:text-rose-300' : 'text-emerald-700 dark:text-emerald-300' }} mt-1">{{ $overdueOrders }}</p>
                        <p class="text-2xs text-slate-500 mt-1">dari {{ $totalActiveOrders }} order aktif ({{ $overdueRate }}%)</p>
                    </div>
                    <div class="flex-1 p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800">
                        <span class="material-symbols-outlined text-3xl text-primary mb-2 block" style="font-variation-settings: 'FILL' 1">done_all</span>
                        <p class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider">Kepatuhan SLA</p>
                        <p class="text-3xl font-black font-mono text-primary mt-1">{{ number_format(100 - $overdueRate, 1) }}%</p>
                        <p class="text-2xs text-slate-500 mt-1">Tepat Waktu</p>
                    </div>
                </div>
            </div>

            {{-- Workshop Staff Productivity --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-blue-500">engineering</span>
                            Produktivitas Staf Workshop
                        </h3>
                        <p class="text-2xs text-slate-400 mt-0.5">Aktivitas transisi status produksi per staf</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-2xs font-extrabold">{{ count($staffProductivity) }} Staf Aktif</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800 text-slate-400 font-extrabold uppercase tracking-wider text-2xs">
                                <th class="py-3 px-4">Staf</th>
                                <th class="py-3 px-3 text-center">Total Aksi</th>
                                <th class="py-3 px-3 text-center">Order Unik</th>
                                <th class="py-3 px-3 text-center">Cuci</th>
                                <th class="py-3 px-3 text-center">Kering</th>
                                <th class="py-3 px-3 text-center">Lipat</th>
                                <th class="py-3 px-4 text-right">Selesai (SIAP)</th>
                                <th class="py-3 px-4 text-right">Diambil</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($staffProductivity as $staff)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-850/50 transition-colors group">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 font-black text-2xs flex items-center justify-center border border-blue-200/60 dark:border-blue-800/60">
                                                {{ strtoupper(substr($staff->staff_name, 0, 1)) }}
                                            </div>
                                            <span class="font-extrabold text-slate-900 dark:text-slate-100 group-hover:text-blue-600 transition-colors">{{ $staff->staff_name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 font-extrabold text-2xs border border-blue-100 dark:border-blue-900/40">
                                            <span class="material-symbols-outlined text-xs">touch_app</span>
                                            {{ number_format($staff->total_actions) }}x
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-bold text-slate-700 dark:text-slate-300 font-mono text-2xs">{{ $staff->unique_orders }}</td>
                                    <td class="py-3.5 px-3 text-center font-mono text-2xs text-blue-600 dark:text-blue-400 font-bold">{{ $staff->cuci_count }}</td>
                                    <td class="py-3.5 px-3 text-center font-mono text-2xs text-amber-600 dark:text-amber-400 font-bold">{{ $staff->kering_count }}</td>
                                    <td class="py-3.5 px-3 text-center font-mono text-2xs text-orange-600 dark:text-orange-400 font-bold">{{ $staff->lipat_count }}</td>
                                    <td class="py-3.5 px-4 text-right">
                                        <span class="inline-flex items-center gap-1 font-black font-mono text-emerald-600 dark:text-emerald-400 text-xs">
                                            <span class="material-symbols-outlined text-xs">check_circle</span>
                                            {{ number_format($staff->completed_orders) }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-mono text-slate-600 dark:text-slate-400 font-bold text-2xs">{{ $staff->picked_up_orders }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="py-12 text-center">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">work_history</span>
                                    <p class="text-xs text-slate-400">Belum ada riwayat staf workshop di periode ini.</p>
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ======================================================
             TAB 4: ANALYTICS
             ====================================================== --}}
        <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="flex flex-col gap-5">

            {{-- Row 1: Payment Method + Customer Retention + Refund --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

                {{-- Payment Method Donut --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-teal-500">wallet</span>
                            Metode Pembayaran
                        </h3>
                        <p class="text-2xs text-slate-400 mt-0.5">Distribusi metode bayar pelanggan</p>
                    </div>
                    <div class="p-5" style="height:220px; position:relative">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>
                    <div class="px-5 pb-4 space-y-1.5">
                        @foreach($paymentBreakdown as $pm)
                            <div class="flex items-center justify-between text-2xs">
                                <span class="font-bold text-slate-700 dark:text-slate-300 uppercase">{{ $pm->payment_method }}</span>
                                <span class="font-black font-mono text-slate-900 dark:text-slate-100">{{ $pm->total_orders }} nota</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Customer Retention --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-violet-500">loyalty</span>
                            Retensi Pelanggan
                        </h3>
                        <p class="text-2xs text-slate-400 mt-0.5">Pelanggan baru vs kembali berlangganan</p>
                    </div>
                    <div class="p-5 flex flex-col gap-4">
                        <div style="height:160px; position:relative">
                            <canvas id="retentionDonutChart"></canvas>
                        </div>
                        <div class="grid grid-cols-2 gap-3 text-center">
                            <div class="p-3 rounded-xl bg-violet-50 dark:bg-violet-950/20 border border-violet-100 dark:border-violet-900/30">
                                <p class="text-lg font-black font-mono text-violet-700 dark:text-violet-300">{{ $customerRetention['returning_customers'] }}</p>
                                <p class="text-2xs text-violet-500 font-bold">Kembali Lagi</p>
                                <p class="text-[9px] text-slate-400">{{ $customerRetention['retention_rate'] }}% dari total</p>
                            </div>
                            <div class="p-3 rounded-xl bg-sky-50 dark:bg-sky-950/20 border border-sky-100 dark:border-sky-900/30">
                                <p class="text-lg font-black font-mono text-sky-700 dark:text-sky-300">{{ $customerRetention['new_customers'] }}</p>
                                <p class="text-2xs text-sky-500 font-bold">Pelanggan Baru</p>
                                <p class="text-[9px] text-slate-400">{{ $customerRetention['total_customers'] > 0 ? round((1 - $customerRetention['retention_rate']/100) * 100, 1) : 0 }}% dari total</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Refund Stats --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-rose-500">assignment_return</span>
                            Statistik Refund
                        </h3>
                        <p class="text-2xs text-slate-400 mt-0.5">Permintaan &amp; nilai refund periode ini</p>
                    </div>
                    <div class="p-5 flex flex-col gap-4">
                        <div class="p-4 rounded-2xl {{ $refundStats['refund_rate'] > 5 ? 'bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40' : 'bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700' }}">
                            <p class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider">Refund Rate</p>
                            <p class="text-4xl font-black font-mono {{ $refundStats['refund_rate'] > 5 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300' }} mt-1">{{ $refundStats['refund_rate'] }}%</p>
                            <p class="text-2xs text-slate-400 mt-1">{{ $refundStats['refund_rate'] > 5 ? 'Di atas threshold 5% — perlu investigasi' : 'Dalam batas normal' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <p class="text-2xs text-slate-400 font-bold">Total Request</p>
                                <p class="text-xl font-black font-mono text-slate-900 dark:text-slate-100">{{ $refundStats['total_requests'] }}</p>
                            </div>
                            <div>
                                <p class="text-2xs text-slate-400 font-bold">Nilai Disetujui</p>
                                <p class="text-base font-black font-mono text-rose-600 dark:text-rose-400 leading-tight">Rp {{ number_format($refundStats['approved_amount'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top Services Table --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-slate-100 flex items-center gap-2">
                            <span class="material-symbols-outlined text-base text-primary">workspace_premium</span>
                            Top 10 Layanan Terlaris
                        </h3>
                        <p class="text-2xs text-slate-400 mt-0.5">Berdasarkan total pendapatan periode ini</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-2xs font-extrabold">{{ count($topServices) }} Layanan</span>
                </div>
                @php $maxSvcRev = $topServices->max('total_revenue') ?: 1; @endphp
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50/70 dark:bg-slate-850 border-b border-slate-100 dark:border-slate-800 text-slate-400 font-extrabold uppercase tracking-wider text-2xs">
                                <th class="py-3 px-4">Layanan</th>
                                <th class="py-3 px-3 text-center">Jml Transaksi</th>
                                <th class="py-3 px-4 text-center">Total Qty</th>
                                <th class="py-3 px-4 text-right">Pendapatan</th>
                                <th class="py-3 px-4">Kontribusi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            @forelse($topServices as $i => $svc)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-850/50 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-6 h-6 rounded-lg {{ $i < 3 ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }} flex items-center justify-center text-2xs font-extrabold shrink-0">{{ $i + 1 }}</div>
                                            <div>
                                                <span class="font-extrabold text-slate-900 dark:text-slate-100">{{ $svc->service?->name ?? 'N/A' }}</span>
                                                @if($svc->service?->type)
                                                    <span class="ml-1.5 px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-500 text-[9px] font-bold rounded uppercase">{{ $svc->service->type }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-3 text-center font-mono font-bold text-slate-700 dark:text-slate-300">{{ $svc->total_orders }}</td>
                                    <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700 dark:text-slate-300">{{ number_format($svc->total_quantity, 1) }}</td>
                                    <td class="py-3.5 px-4 text-right font-black font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($svc->total_revenue, 0, ',', '.') }}</td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden max-w-[100px]">
                                                <div class="bg-primary h-full rounded-full" style="width: {{ round(($svc->total_revenue / $maxSvcRev) * 100) }}%"></div>
                                            </div>
                                            <span class="text-2xs font-bold text-slate-500">{{ round(($svc->total_revenue / $maxSvcRev) * 100) }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-12 text-center">
                                    <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">inventory_2</span>
                                    <p class="text-xs text-slate-400">Belum ada data layanan di periode ini.</p>
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- ── CHART.JS DATA & SCRIPTS ── --}}
    @push('scripts')
    <script>
    // ── Raw Data from PHP/Blade ──────────────────────────────────────
    const perfData = {
        revenueByDay: @json($revenueByDay->values()),
        hourlyData:   @json(array_values($hourlyData)),
        cashiers:     @json($cashiers->values()),
        pipeline:     @json($productionPipeline),
        paymentBreak: @json($paymentBreakdown),
        retention: {
            returning: {{ $customerRetention['returning_customers'] }},
            new:       {{ $customerRetention['new_customers'] }},
        },
        channel: {
            outlet:   {{ $channelBreakdown['outlet']['count'] }},
            delivery: {{ $channelBreakdown['pickup_delivery']['count'] }},
        },
    };

    // ── Shared Chart Defaults ────────────────────────────────────────
    function isDark() { return document.documentElement.classList.contains('dark'); }
    const gridColor  = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const textColor  = () => isDark() ? '#94a3b8' : '#94a3b8';

    // ── Alpine Component ─────────────────────────────────────────────
    function performanceDash() {
        return {
            activeTab: 'overview',
            charts: {},

            init() {
                this.$watch('activeTab', (tab) => {
                    this.$nextTick(() => {
                        if (tab === 'overview' && !this.charts.revenue) this.initOverviewCharts();
                        if (tab === 'cashier'  && !this.charts.cashierBar) this.initCashierCharts();
                        if (tab === 'workshop' && !this.charts.pipeline) this.initWorkshopCharts();
                        if (tab === 'analytics'&& !this.charts.payment) this.initAnalyticsCharts();
                    });
                });
                // Init overview immediately on first load
                this.$nextTick(() => { this.initOverviewCharts(); });
            },

            // ── Overview Charts ──────────────────────────────────────
            initOverviewCharts() {
                // Revenue Line Chart
                const dates   = perfData.revenueByDay.map(d => d.date);
                const revenue = perfData.revenueByDay.map(d => d.paid_revenue);
                const orders  = perfData.revenueByDay.map(d => d.total_orders);

                this.charts.revenue = new Chart(document.getElementById('revenueLineChart'), {
                    type: 'line',
                    data: {
                        labels: dates,
                        datasets: [
                            {
                                label: 'Omset Lunas (Rp)',
                                data: revenue,
                                borderColor: '#FF6600',
                                backgroundColor: 'rgba(255,102,0,0.08)',
                                borderWidth: 2.5,
                                pointRadius: 3,
                                pointBackgroundColor: '#FF6600',
                                fill: true,
                                tension: 0.35,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Jumlah Nota',
                                data: orders,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.07)',
                                borderWidth: 2,
                                pointRadius: 2,
                                pointBackgroundColor: '#3b82f6',
                                fill: false,
                                tension: 0.35,
                                yAxisID: 'y1',
                            }
                        ]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: { display: true, labels: { color: textColor(), font: { size: 10, weight: 'bold' }, boxWidth: 12 } },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ctx.datasetIndex === 0
                                        ? ' Rp ' + Number(ctx.raw).toLocaleString('id-ID')
                                        : ' ' + ctx.raw + ' nota'
                                }
                            }
                        },
                        scales: {
                            x: { grid: { color: gridColor() }, ticks: { color: textColor(), maxRotation: 45, font: { size: 9 } } },
                            y: {
                                position: 'left',
                                grid: { color: gridColor() },
                                ticks: { color: textColor(), font: { size: 9 }, callback: v => 'Rp ' + (v >= 1000000 ? (v/1000000).toFixed(1)+'jt' : (v/1000).toFixed(0)+'rb') }
                            },
                            y1: {
                                position: 'right',
                                grid: { drawOnChartArea: false },
                                ticks: { color: '#3b82f6', font: { size: 9 }, callback: v => v + ' nota' }
                            }
                        }
                    }
                });

                // Channel Donut
                this.charts.channel = new Chart(document.getElementById('channelDonutChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Langsung Outlet', 'Pickup & Delivery'],
                        datasets: [{ data: [perfData.channel.outlet, perfData.channel.delivery], backgroundColor: ['#FF6600','#3b82f6'], borderWidth: 0 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '65%',
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ctx.label + ': ' + ctx.raw + ' nota' } } }
                    }
                });

                // Hourly Bar Chart
                const hours  = perfData.hourlyData.map(h => h.hour + ':00');
                const hOrd   = perfData.hourlyData.map(h => h.total_orders);
                this.charts.hourly = new Chart(document.getElementById('hourlyBarChart'), {
                    type: 'bar',
                    data: {
                        labels: hours,
                        datasets: [{
                            label: 'Jumlah Nota',
                            data: hOrd,
                            backgroundColor: hOrd.map(v => v === Math.max(...hOrd) ? '#FF6600' : 'rgba(255,102,0,0.3)'),
                            borderRadius: 4, borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.raw + ' nota' } } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: textColor(), font: { size: 8 } } },
                            y: { grid: { color: gridColor() }, ticks: { color: textColor(), font: { size: 9 }, stepSize: 1 } }
                        }
                    }
                });
            },

            // ── Cashier Charts ───────────────────────────────────────
            initCashierCharts() {
                const names    = perfData.cashiers.map(c => c.name.split(' ')[0]);
                const revenues = perfData.cashiers.map(c => c.total_revenue ?? 0);
                const pending  = perfData.cashiers.map(c => c.total_pending_revenue ?? 0);

                this.charts.cashierBar = new Chart(document.getElementById('cashierBarChart'), {
                    type: 'bar',
                    data: {
                        labels: names,
                        datasets: [
                            { label: 'Omset Lunas', data: revenues, backgroundColor: '#FF6600', borderRadius: 6, borderSkipped: false },
                            { label: 'Pending',     data: pending,  backgroundColor: '#f59e0b', borderRadius: 6, borderSkipped: false }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true, maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, labels: { color: textColor(), font: { size: 10, weight: 'bold' }, boxWidth: 12 } },
                            tooltip: { callbacks: { label: ctx => ' Rp ' + Number(ctx.raw).toLocaleString('id-ID') } }
                        },
                        scales: {
                            x: { stacked: false, grid: { color: gridColor() }, ticks: { color: textColor(), font: { size: 9 }, callback: v => 'Rp' + (v >= 1000000 ? (v/1000000).toFixed(0)+'jt' : (v/1000).toFixed(0)+'rb') } },
                            y: { grid: { display: false }, ticks: { color: textColor(), font: { size: 10, weight: 'bold' } } }
                        }
                    }
                });
            },

            // ── Workshop Charts ──────────────────────────────────────
            initWorkshopCharts() {
                const stages = perfData.pipeline.map(p => p.stage);
                const counts = perfData.pipeline.map(p => p.count);
                const stageColors = ['#0ea5e9','#8b5cf6','#3b82f6','#f59e0b','#f97316','#14b8a6','#10b981','#64748b'];

                this.charts.pipeline = new Chart(document.getElementById('pipelineDonutChart'), {
                    type: 'doughnut',
                    data: {
                        labels: stages,
                        datasets: [{ data: counts, backgroundColor: stageColors, borderWidth: 0 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '55%',
                        plugins: {
                            legend: { display: true, position: 'right', labels: { color: textColor(), font: { size: 9, weight: 'bold' }, boxWidth: 10, padding: 8 } },
                            tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.raw + ' order' } }
                        }
                    }
                });
            },

            // ── Analytics Charts ─────────────────────────────────────
            initAnalyticsCharts() {
                // Payment Method Donut
                const pmLabels = perfData.paymentBreak.map(p => p.payment_method.toUpperCase());
                const pmData   = perfData.paymentBreak.map(p => p.total_orders);
                const pmColors = ['#FF6600','#3b82f6','#10b981','#8b5cf6','#f59e0b','#ef4444','#0ea5e9','#14b8a6'];

                this.charts.payment = new Chart(document.getElementById('paymentMethodChart'), {
                    type: 'doughnut',
                    data: {
                        labels: pmLabels,
                        datasets: [{ data: pmData, backgroundColor: pmColors.slice(0, pmLabels.length), borderWidth: 0 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '60%',
                        plugins: {
                            legend: { display: true, position: 'bottom', labels: { color: textColor(), font: { size: 9, weight: 'bold' }, boxWidth: 10, padding: 6 } },
                            tooltip: { callbacks: { label: ctx => ' ' + ctx.raw + ' nota' } }
                        }
                    }
                });

                // Retention Donut
                this.charts.retention = new Chart(document.getElementById('retentionDonutChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Pelanggan Kembali', 'Pelanggan Baru'],
                        datasets: [{ data: [perfData.retention.returning, perfData.retention.new], backgroundColor: ['#8b5cf6','#0ea5e9'], borderWidth: 0 }]
                    },
                    options: {
                        responsive: true, maintainAspectRatio: false, cutout: '62%',
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ' ' + ctx.raw + ' pelanggan' } }
                        }
                    }
                });
            },
        }
    }
    </script>
    @endpush
</x-app-layout>
