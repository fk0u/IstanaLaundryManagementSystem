<x-app-layout>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div>
            <h3 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mb-2 tracking-tight">
                {{ !$branchId ? 'Executive Dashboard (Global)' : 'Cabang: ' . (\App\Models\Branch::find($branchId)?->name ?? 'Scoped') }}
            </h3>
            <p class="text-slate-500 dark:text-slate-400 font-semibold text-sm">
                {{ !$branchId ? 'Pantau performa konsolidasi seluruh cabang Istana Laundry secara real-time.' : 'Pantau kinerja harian, stok, dan antrean cucian cabang Anda.' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center gap-2 bg-white dark:bg-slate-900 px-4 py-2 rounded-xl border border-outline-variant dark:border-slate-800 shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-350">Kesehatan Sistem: Baik</span>
            </div>
            @if(!$branchId)
                <div class="flex items-center gap-2 bg-orange-500/10 dark:bg-orange-500/20 px-4 py-2 rounded-xl border border-primary/20">
                    <span class="material-symbols-outlined text-primary text-[18px]">account_balance</span>
                    <span class="text-xs text-primary font-bold">Consolidated Views</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Executive Summary Cards -->
    <div class="grid grid-cols-4 gap-6 mb-8">
        <!-- Card 1: Total Revenue -->
        <div class="col-span-4 sm:col-span-2 lg:col-span-1 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pendapatan</span>
                    <h4 class="text-xl font-extrabold text-slate-800 dark:text-slate-100 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h4>
                </div>
                <span class="material-symbols-outlined text-primary bg-primary-container/10 p-2.5 rounded-lg">payments</span>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-slate-500 text-[10px] font-bold">
                <span class="material-symbols-outlined text-xs">info</span>
                <span>Akumulasi nilai transaksi lunas</span>
            </div>
        </div>

        <!-- Card 2: MoM Growth -->
        <div class="col-span-4 sm:col-span-2 lg:col-span-1 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pertumbuhan (MoM)</span>
                    <h4 class="text-xl font-extrabold mt-1 {{ $growthPercent >= 0 ? 'text-green-500' : 'text-red-500' }}">
                        {{ $growthPercent >= 0 ? '+' : '' }}{{ number_format($growthPercent, 1, ',', '.') }}%
                    </h4>
                </div>
                <span class="material-symbols-outlined p-2.5 rounded-lg {{ $growthPercent >= 0 ? 'text-green-500 bg-green-500/10' : 'text-red-500 bg-red-500/10' }}">
                    {{ $growthPercent >= 0 ? 'trending_up' : 'trending_down' }}
                </span>
            </div>
            <div class="mt-4 text-[10px] font-semibold text-slate-400">
                vs bulan lalu: <span class="font-bold text-slate-500">Rp {{ number_format($topBranchRevenue, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Card 3: Active Orders -->
        <div class="col-span-4 sm:col-span-2 lg:col-span-1 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Antrean Order Aktif</span>
                    <h4 class="text-xl font-extrabold text-slate-800 dark:text-slate-100 mt-1">{{ number_format($activeOrdersCount, 0, ',', '.') }} Nota</h4>
                </div>
                <span class="material-symbols-outlined text-blue-500 bg-blue-500/10 p-2.5 rounded-lg">local_laundry_service</span>
            </div>
            <div class="mt-4 flex items-center gap-1 text-[10px] text-slate-500 font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                <span>Sedang diproses di workshop</span>
            </div>
        </div>

        <!-- Card 4: Top Performing Branch -->
        <div class="col-span-4 sm:col-span-2 lg:col-span-1 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Cabang Terbaik</span>
                    <h4 class="text-sm font-extrabold text-slate-850 dark:text-slate-100 mt-1.5 truncate max-w-[150px]" title="{{ $topBranchName }}">
                        {{ $topBranchName }}
                    </h4>
                </div>
                <span class="material-symbols-outlined text-yellow-500 bg-yellow-500/10 p-2.5 rounded-lg">stars</span>
            </div>
            <div class="mt-4 text-[10px] text-slate-400 font-semibold truncate">
                Omset tertinggi: <span class="font-bold text-slate-500">Rp {{ number_format($topBranchRevenue, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Bento Grid Charts & Lists -->
    <div class="grid grid-cols-12 gap-6">
        <!-- Chart.js Card (8 cols) -->
        <div class="col-span-12 lg:col-span-8 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h4 class="text-base font-black text-slate-900 dark:text-white">{{ $chartTitle }}</h4>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $chartSub }}</p>
                </div>
            </div>
            
            <div class="relative h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Right Side: Quick Info & Metrics (4 cols) -->
        <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
            <!-- Total Transactions -->
            <div class="bg-slate-900 dark:bg-slate-950 text-white rounded-xl p-6 flex flex-col justify-between h-40 relative overflow-hidden">
                <div class="absolute right-0 bottom-0 opacity-5">
                    <span class="material-symbols-outlined text-[120px]">point_of_sale</span>
                </div>
                <div class="relative z-10">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Transaksi Nota</span>
                    <h5 class="text-3xl font-extrabold mt-2 tracking-tight">{{ number_format($totalTransactions, 0, ',', '.') }} Nota</h5>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-green-500 relative z-10 text-xs font-bold">
                    <span class="material-symbols-outlined text-sm">trending_up</span>
                    <span>Tingkat konversi transaksi sehat</span>
                </div>
            </div>

            <!-- Server Health Status -->
            <div class="bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow flex flex-col justify-between">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Metrik Server & Backup</h4>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-[10px] font-bold text-slate-600 dark:text-slate-450 mb-1.5">
                            <span>Waktu Respon Rata-rata</span>
                            <span>42ms</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-850 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-green-500 h-full w-[95%]"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-[10px] font-bold text-slate-600 dark:text-slate-450 mb-1.5">
                            <span>Spatie Auto-Backup</span>
                            <span class="text-green-500">Berhasil (Harian)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-slate-850 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-green-500 h-full w-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions Table (12 cols) -->
        <div class="col-span-12 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-base font-extrabold text-slate-800 dark:text-slate-200">Transaksi Terbaru Cabang</h4>
                <a href="/pos" class="text-xs font-bold text-primary hover:underline">Lihat POS & Order</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-4">Nomor Nota</th>
                            <th class="py-2.5 px-4">Pelanggan</th>
                            <th class="py-2.5 px-4">Cabang</th>
                            <th class="py-2.5 px-4">Metode Bayar</th>
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
                                    {{ $order->customer?->name ?? 'Walk-In Customer' }}
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
                                        <x-badge type="danger">Refunded</x-badge>
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
        </div>
    </div>

    <!-- Script Chart.js Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            
            // Premium gradients
            const gradientBg = ctx.createLinearGradient(0, 0, 0, 300);
            gradientBg.addColorStop(0, 'rgba(255, 102, 0, 0.45)');
            gradientBg.addColorStop(1, 'rgba(255, 102, 0, 0.02)');
            
            const isDark = document.documentElement.classList.contains('dark');
            const fontColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';

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
                        borderRadius: 8,
                        hoverBackgroundColor: '#FF6600',
                        barPercentage: 0.55
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
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
                                font: { family: 'Inter', weight: 'bold', size: 10 }
                            }
                        },
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: fontColor,
                                font: { family: 'Inter', size: 10 },
                                callback: function(value) {
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
