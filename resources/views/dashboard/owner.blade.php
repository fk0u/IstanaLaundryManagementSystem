<x-app-layout>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-6 md:mb-8 gap-3 md:gap-4">
        <div>
            <h3 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-slate-900 dark:text-white mb-1 md:mb-2 tracking-tight">
                {{ !$branchId ? 'Executive Dashboard' : 'Cabang: ' . (\App\Models\Branch::find($branchId)?->name ?? 'Scoped') }}
            </h3>
            <p class="text-slate-500 dark:text-slate-400 font-semibold text-xs md:text-sm">
                {{ !$branchId ? 'Pantau performa konsolidasi seluruh cabang.' : 'Pantau kinerja harian cabang Anda.' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2 md:gap-4">
            <div class="flex items-center gap-2 bg-white dark:bg-slate-900 px-3 py-1.5 md:px-4 md:py-2 rounded-xl border border-outline-variant dark:border-slate-800 shadow-sm">
                <span class="w-2 h-2 md:w-2.5 md:h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-2xs md:text-xs font-bold text-slate-700 dark:text-slate-350">Sistem: Baik</span>
            </div>
            @if(!$branchId)
                <div class="flex items-center gap-2 bg-orange-500/10 dark:bg-orange-500/20 px-3 py-1.5 md:px-4 md:py-2 rounded-xl border border-primary/20">
                    <span class="material-symbols-outlined text-primary text-[16px] md:text-[18px]">account_balance</span>
                    <span class="text-2xs md:text-xs text-primary font-bold">Consolidated</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Executive Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4 mb-6 md:mb-8">
        <!-- Card 1: Total Revenue -->
        <div class="bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-4 md:p-5 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0">
                    <span class="text-2xs md:text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Omset</span>
                    <h4 class="text-base md:text-lg font-extrabold text-slate-800 dark:text-slate-100 mt-1 truncate">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                </div>
                <span class="material-symbols-outlined text-primary bg-primary-container/10 p-2 rounded-lg text-lg md:text-xl shrink-0">payments</span>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-slate-500 text-2xs md:text-[10px] font-bold">
                <span class="material-symbols-outlined text-xs">info</span>
                <span class="truncate">Akumulasi penjualan</span>
            </div>
        </div>

        <!-- Card 2: Cash Flow Month -->
        <div class="bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-4 md:p-5 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0">
                    <span class="text-2xs md:text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kas Masuk (Bln Ini)</span>
                    <h4 class="text-base md:text-lg font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 truncate">Rp {{ number_format($monthCashFlow, 0, ',', '.') }}</h4>
                </div>
                <span class="material-symbols-outlined text-emerald-500 bg-emerald-500/10 p-2 rounded-lg text-lg md:text-xl shrink-0">account_balance_wallet</span>
            </div>
            <div class="mt-3 text-2xs md:text-[10px] font-semibold text-slate-400 truncate">
                Pembayaran diterima
            </div>
        </div>

        <!-- Card 3: Piutang (Unpaid) -->
        <div class="bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-4 md:p-5 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0">
                    <span class="text-2xs md:text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Piutang</span>
                    <h4 class="text-base md:text-lg font-extrabold text-rose-500 mt-1 truncate">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h4>
                </div>
                <span class="material-symbols-outlined text-rose-500 bg-rose-500/10 p-2 rounded-lg text-lg md:text-xl shrink-0">pending_actions</span>
            </div>
            <div class="mt-3 text-2xs md:text-[10px] font-semibold text-rose-400 truncate">
                Belum lunas / invoice
            </div>
        </div>

        <!-- Card 4: MoM Growth -->
        <div class="bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-4 md:p-5 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0">
                    <span class="text-2xs md:text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pertumbuhan</span>
                    <h4 class="text-base md:text-lg font-extrabold mt-1 {{ $growthPercent >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $growthPercent >= 0 ? '+' : '' }}{{ number_format($growthPercent, 1, ',', '.') }}%
                    </h4>
                </div>
                <span class="material-symbols-outlined p-2 rounded-lg text-lg md:text-xl shrink-0 {{ $growthPercent >= 0 ? 'text-green-500 bg-green-500/10' : 'text-red-500 bg-red-500/10' }}">
                    {{ $growthPercent >= 0 ? 'trending_up' : 'trending_down' }}
                </span>
            </div>
            <div class="mt-3 text-2xs md:text-[10px] font-semibold text-slate-400 truncate">
                vs bulan lalu
            </div>
        </div>

        <!-- Card 5: Active Orders -->
        <div class="bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-4 md:p-5 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0">
                    <span class="text-2xs md:text-[10px] font-bold text-slate-400 uppercase tracking-wider">Order Aktif</span>
                    <h4 class="text-base md:text-lg font-extrabold text-slate-800 dark:text-slate-100 mt-1">{{ number_format($activeOrdersCount, 0, ',', '.') }}</h4>
                </div>
                <span class="material-symbols-outlined text-orange-500 bg-orange-500/10 p-2 rounded-lg text-lg md:text-xl shrink-0">local_laundry_service</span>
            </div>
            <div class="mt-3 flex items-center gap-1 text-2xs md:text-[10px] text-slate-500 font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse shrink-0"></span>
                <span class="truncate">Di workshop</span>
            </div>
        </div>
    </div>

    <!-- Bento Grid Charts & Lists -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">
        <!-- Chart.js Card -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-4 md:p-6 premium-shadow">
            <div class="flex justify-between items-center mb-4 md:mb-6">
                <div>
                    <h4 class="text-sm md:text-base font-black text-slate-900 dark:text-white">{{ $chartTitle }}</h4>
                    <p class="text-2xs md:text-xs text-slate-400 mt-0.5">{{ $chartSub }}</p>
                </div>
            </div>
            
            <div class="relative h-48 md:h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Right Side: Quick Info & Metrics -->
        <div class="lg:col-span-4 flex flex-col gap-4 md:gap-6">
            <!-- Total Transactions -->
            <div class="bg-slate-900 dark:bg-slate-950 text-white rounded-xl p-4 md:p-6 flex flex-col justify-between h-32 md:h-40 relative overflow-hidden">
                <div class="absolute right-0 bottom-0 opacity-5">
                    <span class="material-symbols-outlined text-[80px] md:text-[120px]">point_of_sale</span>
                </div>
                <div class="relative z-10">
                    <span class="text-2xs md:text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Transaksi</span>
                    <h5 class="text-2xl md:text-3xl font-extrabold mt-1 md:mt-2 tracking-tight">{{ number_format($totalTransactions, 0, ',', '.') }} <span class="text-base md:text-lg font-bold text-slate-400">Nota</span></h5>
                </div>
                <div class="mt-2 md:mt-4 flex items-center gap-1.5 text-green-500 relative z-10 text-2xs md:text-xs font-bold">
                    <span class="material-symbols-outlined text-sm">trending_up</span>
                    <span>Konversi transaksi sehat</span>
                </div>
            </div>

            <!-- Server Health Status -->
            <div class="bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-4 md:p-6 premium-shadow flex flex-col justify-between">
                <h4 class="text-2xs md:text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 md:mb-4">Metrik Server</h4>
                <div class="space-y-3 md:space-y-4">
                    <div>
                        <div class="flex justify-between text-2xs md:text-[10px] font-bold text-slate-600 dark:text-slate-450 mb-1.5">
                            <span>Respon Rata-rata</span>
                            <span>42ms</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-850 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-green-500 h-full w-[95%]"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-2xs md:text-[10px] font-bold text-slate-600 dark:text-slate-450 mb-1.5">
                            <span>Auto-Backup</span>
                            <span class="text-green-500">OK (Harian)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-850 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-green-500 h-full w-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="lg:col-span-12 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-4 md:p-6 premium-shadow">
            <div class="flex justify-between items-center mb-4 md:mb-6">
                <h4 class="text-sm md:text-base font-extrabold text-slate-800 dark:text-slate-200">Transaksi Terbaru</h4>
                <a href="{{ route('orders.index') }}" class="text-2xs md:text-xs font-bold text-primary hover:underline inline-flex items-center gap-1">
                    Lihat Semua
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>

            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-4">Nomor Nota</th>
                            <th class="py-2.5 px-4">Pelanggan</th>
                            <th class="py-2.5 px-4">Cabang</th>
                            <th class="py-2.5 px-4">Metode</th>
                            <th class="py-2.5 px-4">Bayar</th>
                            <th class="py-2.5 px-4">Produksi</th>
                            <th class="py-2.5 px-4 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $order->order_number }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-700 dark:text-slate-350">
                                    {{ $order->customer?->name ?? 'Walk-In' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $order->branch?->name }}
                                </td>
                                <td class="py-3.5 px-4 uppercase text-[10px] font-bold text-slate-500">
                                    {{ $order->payment_method }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($order->payment_status === 'paid')
                                        <x-badge type="success">Lunas</x-badge>
                                    @elseif ($order->payment_status === 'refunded')
                                        <x-badge type="danger">Refund</x-badge>
                                    @else
                                        <x-badge type="warning">Pending</x-badge>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-bold text-[10px]">
                                    <span class="px-2 py-0.5 rounded-full bg-primary-container/10 text-primary border border-primary/20">
                                        {{ $order->production_status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-extrabold text-slate-800 dark:text-slate-100 font-mono">
                                    Rp {{ number_format($order->total, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">Belum ada transaksi terekam.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card List --}}
            <div class="md:hidden space-y-3">
                @forelse($recentOrders as $order)
                    <div class="border border-slate-100 dark:border-slate-800 rounded-xl p-3.5 bg-slate-50/30 dark:bg-slate-800/20">
                        <div class="flex justify-between items-start mb-2">
                            <div class="min-w-0">
                                <span class="font-mono font-bold text-xs text-slate-800 dark:text-slate-200 block">{{ $order->order_number }}</span>
                                <span class="text-2xs text-slate-500">{{ $order->customer?->name ?? 'Walk-In' }}</span>
                            </div>
                            <span class="font-extrabold text-sm text-slate-800 dark:text-slate-100 font-mono shrink-0">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            @if ($order->payment_status === 'paid')
                                <x-badge type="success">Lunas</x-badge>
                            @elseif ($order->payment_status === 'refunded')
                                <x-badge type="danger">Refund</x-badge>
                            @else
                                <x-badge type="warning">Pending</x-badge>
                            @endif
                            <span class="px-2 py-0.5 rounded-full bg-primary-container/10 text-primary border border-primary/20 text-2xs font-bold">
                                {{ $order->production_status }}
                            </span>
                            <span class="text-2xs text-slate-400 uppercase font-bold">{{ $order->payment_method }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-sm">Belum ada transaksi.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Script Chart.js Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            const gradientBg = ctx.createLinearGradient(0, 0, 0, 300);
            gradientBg.addColorStop(0, 'rgba(255, 102, 0, 0.45)');
            gradientBg.addColorStop(1, 'rgba(255, 102, 0, 0.02)');
            
            const isDark = document.documentElement.classList.contains('dark');
            const fontColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
            const isMobile = window.innerWidth < 768;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @js($chartLabels),
                    datasets: [{
                        label: 'Pendapatan (Rp)',
                        data: @js($chartValues),
                        backgroundColor: gradientBg,
                        borderColor: '#FF6600',
                        borderWidth: 2,
                        borderRadius: isMobile ? 4 : 8,
                        hoverBackgroundColor: '#FF6600',
                        barPercentage: isMobile ? 0.7 : 0.55
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { size: 11, weight: 'bold' },
                            bodyFont: { size: 12, weight: 'extrabold' },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' Rp ' + context.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: fontColor,
                                font: { family: 'Inter', weight: 'bold', size: isMobile ? 8 : 10 },
                                maxRotation: isMobile ? 45 : 0,
                            }
                        },
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: fontColor,
                                font: { family: 'Inter', size: isMobile ? 8 : 10 },
                                callback: function(value) {
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                    return 'Rp ' + (value / 1000).toLocaleString('id-ID') + 'k';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
