<x-app-layout>
    <div class="space-y-6 md:space-y-8">
        <!-- 1. Header Overview Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <p class="text-[11px] font-extrabold text-primary uppercase tracking-[0.12em] mb-1">Executive Overview</p>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ !$branchId ? 'Executive Dashboard' : 'Cabang: ' . (\App\Models\Branch::find($branchId)?->name ?? 'Scoped') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mt-0.5">
                    {{ !$branchId ? 'Pantau performa konsolidasi seluruh cabang.' : 'Pantau kinerja harian cabang Anda.' }}
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <div class="flex items-center gap-2 bg-white dark:bg-slate-900 px-3.5 py-2 rounded-xl border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Sistem: Baik</span>
                </div>
                @if(!$branchId)
                    <div class="flex items-center gap-1.5 bg-orange-500/10 px-3.5 py-2 rounded-xl border border-primary/20">
                        <span class="material-symbols-outlined text-primary text-base">account_balance</span>
                        <span class="text-xs text-primary font-extrabold">Consolidated</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. Real Backend KPI Summary Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
            <!-- Card 1: Total Omset (Real DB sum) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 sm:p-5 rounded-2xl flex flex-col justify-between h-36 hover:shadow-md transition-all">
                <div>
                    <div class="flex items-center gap-1.5 mb-1 text-slate-500 dark:text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider">Total Omset</span>
                    </div>
                    <p class="text-base sm:text-lg lg:text-xl font-black font-mono text-slate-900 dark:text-slate-100 tracking-tight mt-1 truncate" title="Rp {{ number_format($totalRevenue, 0, ',', '.') }}">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </p>
                </div>
                <div class="mt-auto">
                    <svg class="w-full h-7 text-primary" fill="none" preserveAspectRatio="none" stroke="currentColor" stroke-width="2" viewBox="0 0 100 20">
                        <path d="M0 15 Q 10 5, 20 10 T 40 10 T 60 5 T 80 15 T 100 5"></path>
                    </svg>
                    <div class="flex items-center gap-1 text-[10px] text-slate-400 font-bold mt-1">
                        <span class="material-symbols-outlined text-[14px]">info</span>
                        <span>Akumulasi penjualan</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Kas Masuk Bulan Ini (Real DB sum) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 sm:p-5 rounded-2xl flex flex-col justify-between h-36 hover:shadow-md transition-all">
                <div>
                    <div class="flex items-center gap-1.5 mb-1 text-emerald-600">
                        <span class="material-symbols-outlined text-[18px]">payments</span>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kas Masuk (Bln Ini)</span>
                    </div>
                    <p class="text-base sm:text-lg lg:text-xl font-black font-mono text-emerald-600 dark:text-emerald-400 tracking-tight mt-1 truncate" title="Rp {{ number_format($monthCashFlow, 0, ',', '.') }}">
                        Rp {{ number_format($monthCashFlow, 0, ',', '.') }}
                    </p>
                </div>
                <div class="mt-auto">
                    <svg class="w-full h-7 text-emerald-500" fill="none" preserveAspectRatio="none" stroke="currentColor" stroke-width="2" viewBox="0 0 100 20">
                        <path d="M0 10 Q 15 15, 30 10 T 60 5 T 85 10 T 100 5"></path>
                    </svg>
                    <div class="flex items-center justify-between text-[10px] mt-1 font-bold">
                        <span class="text-slate-400">Status</span>
                        <span class="text-emerald-600">Terbayar (Paid)</span>
                    </div>
                </div>
            </div>

            <!-- Card 3: Total Piutang / Unpaid (Real DB query) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 sm:p-5 rounded-2xl flex flex-col justify-between h-36 hover:shadow-md transition-all">
                <div>
                    <div class="flex items-center gap-1.5 mb-1 text-rose-500">
                        <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Piutang</span>
                    </div>
                    <p class="text-base sm:text-lg lg:text-xl font-black font-mono text-rose-600 dark:text-rose-400 tracking-tight mt-1 truncate" title="Rp {{ number_format($totalPiutang, 0, ',', '.') }}">
                        Rp {{ number_format($totalPiutang, 0, ',', '.') }}
                    </p>
                </div>
                <a href="{{ route('orders.index') }}" class="flex items-center justify-between bg-rose-50 dark:bg-rose-950/40 px-2.5 py-1.5 rounded-xl mt-auto text-rose-600 dark:text-rose-400 hover:underline">
                    <span class="text-[10px] font-bold">Belum Lunas / Invoice</span>
                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </a>
            </div>

            <!-- Card 4: MoM Growth % (Real DB calculated) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm p-4 sm:p-5 rounded-2xl flex flex-col justify-between h-36 hover:shadow-md transition-all">
                <div>
                    <div class="flex items-center gap-1.5 mb-1 text-slate-500 dark:text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">monitoring</span>
                        <span class="text-[11px] font-extrabold uppercase tracking-wider">Pertumbuhan MoM</span>
                    </div>
                    <p class="text-base sm:text-lg lg:text-xl font-black font-mono mt-1 {{ $growthPercent >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        {{ $growthPercent >= 0 ? '+' : '' }}{{ number_format($growthPercent, 1, ',', '.') }}%
                    </p>
                </div>
                <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 px-2.5 py-1.5 rounded-xl mt-auto">
                    <div class="w-2 h-2 rounded-full {{ $growthPercent >= 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"></div>
                    <span class="text-[10px] text-slate-600 dark:text-slate-400 font-bold">vs bulan lalu</span>
                </div>
            </div>
        </div>

        <!-- 3. Signature Chart: Komparasi Pendapatan Cabang / Tren Mingguan -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6">
            <!-- Main Signature Chart Card -->
            <div class="lg:col-span-8 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h4 class="text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">bar_chart</span>
                            {{ $chartTitle }}
                        </h4>
                        <p class="text-xs text-slate-400 font-medium mt-0.5">{{ $chartSub }}</p>
                    </div>
                    @if(!$branchId)
                        <span class="px-3 py-1 rounded-full text-2xs font-extrabold bg-primary/10 text-primary border border-primary/20">
                            Cabang Terbaik: {{ $topBranchName }}
                        </span>
                    @endif
                </div>

                <div class="relative h-64 md:h-72 w-full">
                    <canvas id="signatureRevenueChart"></canvas>
                </div>
            </div>

            <!-- Side Metrics: Total Transactions & Branch Performance Breakdown (Real DB) -->
            <div class="lg:col-span-4 flex flex-col gap-4">
                <!-- Total Transaksi -->
                <div class="bg-slate-900 text-white rounded-3xl p-5 flex flex-col justify-between h-32 relative overflow-hidden shadow-md">
                    <div class="absolute right-0 bottom-0 opacity-10">
                        <span class="material-symbols-outlined text-[90px]">point_of_sale</span>
                    </div>
                    <div class="relative z-10">
                        <span class="text-2xs font-bold uppercase tracking-widest text-slate-400">Total Transaksi Konsolidasi</span>
                        <h5 class="text-2xl font-black mt-1 font-mono tracking-tight">{{ number_format($totalTransactions, 0, ',', '.') }} <span class="text-xs font-sans text-slate-400">Nota</span></h5>
                    </div>
                    <div class="flex items-center gap-1.5 text-emerald-400 relative z-10 text-xs font-bold mt-auto">
                        <span class="material-symbols-outlined text-sm">trending_up</span>
                        <span>Akumulasi Seluruh Cabang</span>
                    </div>
                </div>

                <!-- Branch Revenue & Share Breakdown (Real DB data) -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-5 shadow-sm flex flex-col justify-between flex-1">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kontribusi Pendapatan Cabang</h4>
                        <span class="text-2xs font-extrabold text-primary bg-primary/10 px-2.5 py-0.5 rounded-full border border-primary/20">{{ count($branchRankings) }} Cabang</span>
                    </div>
                    <div class="space-y-3.5 max-h-60 overflow-y-auto pr-1">
                        @forelse($branchRankings as $index => $rank)
                            @php
                                $rankNum = $index + 1;
                                $badgeColor = match($rankNum) {
                                    1 => 'bg-amber-500 text-white shadow-xs',
                                    2 => 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200',
                                    3 => 'bg-amber-700/20 text-amber-700 dark:text-amber-400',
                                    default => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                                };
                                $barGradient = match($rankNum) {
                                    1 => 'from-orange-500 to-amber-500',
                                    2 => 'from-blue-500 to-cyan-500',
                                    3 => 'from-emerald-500 to-teal-500',
                                    default => 'from-purple-500 to-indigo-500'
                                };
                            @endphp
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-xs font-bold">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="w-5 h-5 rounded-md flex items-center justify-center text-[10px] font-black shrink-0 {{ $badgeColor }}">
                                            #{{ $rankNum }}
                                        </span>
                                        <span class="text-slate-800 dark:text-slate-200 truncate" title="{{ $rank['name'] }}">{{ $rank['name'] }}</span>
                                        <span class="text-[10px] text-slate-400 font-semibold shrink-0">({{ $rank['orders_count'] }} Nota)</span>
                                    </div>
                                    <span class="font-mono text-slate-900 dark:text-slate-100 shrink-0">
                                        Rp {{ number_format($rank['revenue'], 0, ',', '.') }} 
                                        <span class="text-primary font-black text-[11px] ml-0.5">({{ $rank['share_percent'] }}%)</span>
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 h-2.5 rounded-full overflow-hidden flex p-0.5 shadow-inner">
                                    <div class="bg-gradient-to-r {{ $barGradient }} h-full rounded-full transition-all duration-700 shadow-sm" style="width: {{ max($rank['share_percent'], 3) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 font-medium text-center py-4">Belum ada data cabang.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Real Production Status Cards Section (100% Real DB Breakdown) -->
        @php
            $pb = $productionBreakdown ?? [
                'TERIMA' => 0, 'PILAH' => 0, 'CUCI' => 0, 'KERING' => 0,
                'SETRIKA' => 0, 'CEK' => 0, 'PACKING' => 0, 'SIAP' => 0
            ];
            $terimaPilah = $pb['TERIMA'] + $pb['PILAH'];
            $cuci = $pb['CUCI'];
            $kering = $pb['KERING'];
            $setrikaCek = $pb['SETRIKA'] + $pb['CEK'];
            $packing = $pb['PACKING'];
            $siap = $pb['SIAP'];
        @endphp
        <section class="bg-white dark:bg-slate-900 shadow-sm border border-slate-200/80 dark:border-slate-800 rounded-3xl p-5 sm:p-6 space-y-5">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-widest mb-0.5">Production Status</p>
                    <h3 class="text-lg sm:text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        Order Aktif Workshop
                        <span class="bg-primary/10 text-primary px-2.5 py-0.5 rounded-full text-xs font-black border border-primary/20">{{ $activeOrdersCount }} Nota</span>
                    </h3>
                </div>
                <a href="/production" class="text-primary font-bold text-xs hover:underline flex items-center gap-1">
                    Lihat Semua <span class="material-symbols-outlined text-sm">chevron_right</span>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- 1. Penerimaan & Pilah -->
                <div class="bg-sky-50/70 dark:bg-sky-950/30 border border-sky-100 dark:border-sky-900/40 p-3.5 rounded-2xl flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-sky-100 dark:bg-sky-900/50 text-sky-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-lg">inbox</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-extrabold text-sky-700 dark:text-sky-400 uppercase truncate">Terima & Pilah</p>
                        <p class="text-base font-black text-slate-900 dark:text-slate-100">{{ $terimaPilah }} <span class="text-[10px] font-normal text-slate-400">Nota</span></p>
                    </div>
                </div>

                <!-- 2. Pencucian -->
                <div class="bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40 p-3.5 rounded-2xl flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/50 text-blue-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-lg">water_drop</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-extrabold text-blue-700 dark:text-blue-400 uppercase truncate">Pencucian</p>
                        <p class="text-base font-black text-slate-900 dark:text-slate-100">{{ $cuci }} <span class="text-[10px] font-normal text-slate-400">Nota</span></p>
                    </div>
                </div>

                <!-- 3. Pengeringan -->
                <div class="bg-amber-50/70 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/40 p-3.5 rounded-2xl flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/50 text-amber-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-lg">air</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-extrabold text-amber-700 dark:text-amber-400 uppercase truncate">Pengeringan</p>
                        <p class="text-base font-black text-slate-900 dark:text-slate-100">{{ $kering }} <span class="text-[10px] font-normal text-slate-400">Nota</span></p>
                    </div>
                </div>

                <!-- 4. Penyetrikaan & QC -->
                <div class="bg-orange-50/70 dark:bg-orange-950/30 border border-orange-100 dark:border-orange-900/40 p-3.5 rounded-2xl flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/50 text-orange-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-lg">iron</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-extrabold text-orange-700 dark:text-orange-400 uppercase truncate">Setrika & QC</p>
                        <p class="text-base font-black text-slate-900 dark:text-slate-100">{{ $setrikaCek }} <span class="text-[10px] font-normal text-slate-400">Nota</span></p>
                    </div>
                </div>

                <!-- 5. Packing -->
                <div class="bg-purple-50/70 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/40 p-3.5 rounded-2xl flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/50 text-purple-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-lg">inventory_2</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-extrabold text-purple-700 dark:text-purple-400 uppercase truncate">Packing</p>
                        <p class="text-base font-black text-slate-900 dark:text-slate-100">{{ $packing }} <span class="text-[10px] font-normal text-slate-400">Nota</span></p>
                    </div>
                </div>

                <!-- 6. Siap Diambil -->
                <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 p-3.5 rounded-2xl flex items-center gap-3 shadow-2xs">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-extrabold text-emerald-800 dark:text-emerald-300 uppercase truncate">Siap Diambil</p>
                        <p class="text-base font-black text-emerald-900 dark:text-emerald-200">{{ $siap }} <span class="text-[10px] font-normal text-emerald-700 dark:text-emerald-400">Nota</span></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. Real Backend Recent Transactions Section -->
        <section class="space-y-3">
            <div class="flex justify-between items-center">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Recent Transactions</h3>
                <a href="{{ route('orders.index') }}" class="text-xs font-bold text-primary hover:underline">HISTORY</a>
            </div>
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200/80 dark:border-slate-800 overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($recentOrders as $trans)
                    @php
                        $nameParts = explode(' ', $trans->customer?->name ?? 'Pelanggan');
                        $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                    @endphp
                    <div class="p-4 flex items-center justify-between hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                        <div class="flex items-center gap-3.5 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-100 to-amber-100 text-primary font-black text-xs flex items-center justify-center shrink-0">
                                {{ $initials }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $trans->customer?->name ?? 'Pelanggan Walk-in' }}</p>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="w-2 h-2 rounded-full bg-primary"></span>
                                    <span class="text-xs text-slate-400 font-semibold">{{ $trans->production_status }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right font-mono">
                            <p class="text-sm font-black text-slate-900 dark:text-slate-100">Rp {{ number_format($trans->grand_total ?? $trans->total, 0, ',', '.') }}</p>
                            <p class="text-2xs text-slate-400 mt-0.5">{{ $trans->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-slate-400">Belum ada transaksi terbaru.</div>
                @endforelse
            </div>
        </section>
    </div>

    <!-- Floating Action Button (FAB) for Quick Order Creation -->
    <div class="fixed bottom-20 right-5 z-40 lg:hidden">
        <a href="/pos" class="bg-gradient-to-r from-primary to-orange-500 text-white w-14 h-14 rounded-2xl shadow-lg flex items-center justify-center active:scale-95 transition-transform hover:shadow-xl">
            <span class="material-symbols-outlined text-[28px] font-bold">add</span>
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('signatureRevenueChart');
            if (ctx) {
                const isGlobal = @js(!$branchId);
                const colors = ['#ff6600', '#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4', '#6366f1'];
                new Chart(ctx.getContext('2d'), {
                    type: isGlobal ? 'bar' : 'line',
                    data: {
                        labels: @js($chartLabels),
                        datasets: [{
                            label: 'Pendapatan (Rp)',
                            data: @js($chartValues),
                            backgroundColor: isGlobal ? colors : 'rgba(255, 102, 0, 0.15)',
                            borderColor: isGlobal ? colors : '#ff6600',
                            borderWidth: isGlobal ? 0 : 3,
                            borderRadius: isGlobal ? 10 : 0,
                            fill: !isGlobal,
                            tension: 0.35,
                            pointRadius: isGlobal ? 0 : 5,
                            pointHoverRadius: isGlobal ? 0 : 7,
                            pointBackgroundColor: '#ff6600'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { 
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                padding: 12,
                                titleFont: { weight: 'bold', size: 13 },
                                bodyFont: { size: 12 },
                                cornerRadius: 10,
                                callbacks: { 
                                    label: c => ' Pendapatan: Rp ' + Number(c.raw).toLocaleString('id-ID') 
                                } 
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(148, 163, 184, 0.1)' },
                                ticks: {
                                    font: { weight: 'bold', size: 11 },
                                    callback: function(v) {
                                        if (v >= 1000000000) return 'Rp ' + (v/1000000000).toFixed(1) + 'B';
                                        if (v >= 1000000) return 'Rp ' + (v/1000000).toFixed(1) + 'M';
                                        if (v >= 1000) return 'Rp ' + (v/1000).toFixed(0) + 'k';
                                        return 'Rp ' + v;
                                    }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { weight: 'bold', size: 11 } }
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
