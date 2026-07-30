<x-app-layout>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div>
            <h3 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mb-2 tracking-tight">
                Dashboard Cabang: {{ $branch->name }}
            </h3>
            <p class="text-slate-500 dark:text-slate-400 font-semibold text-sm">
                Pantau kinerja harian, stok bahan habis pakai, dan antrean cucian cabang Anda.
            </p>
        </div>
        <div class="flex items-center gap-2 bg-white dark:bg-slate-900 px-4 py-2 rounded-xl border border-outline-variant dark:border-slate-800 shadow-sm">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-355">Cabang Aktif</span>
        </div>
    </div>

    <!-- Scoped Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 md:gap-4.5 mb-6 md:mb-8">
        <!-- Revenue -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0 flex-1">
                    <span class="text-2xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Total Omset Cabang</span>
                    <h4 class="text-base sm:text-lg lg:text-xl font-black font-mono text-slate-900 dark:text-slate-100 mt-1 whitespace-nowrap overflow-hidden text-ellipsis" title="Rp {{ number_format($totalRevenue, 0, ',', '.') }}">
                        Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                    </h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary dark:bg-slate-800 dark:text-orange-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-xl">payments</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-slate-400 dark:text-slate-500 text-2xs font-semibold">
                <span class="material-symbols-outlined text-xs">info</span>
                <span class="truncate">Akumulasi transaksi cabang</span>
            </div>
        </div>

        <!-- Active Orders -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0 flex-1">
                    <span class="text-2xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Antrean Cucian Aktif</span>
                    <h4 class="text-base sm:text-lg lg:text-xl font-black font-mono text-slate-900 dark:text-slate-100 mt-1 whitespace-nowrap">
                        {{ number_format($activeOrdersCount, 0, ',', '.') }} <span class="text-xs font-bold text-slate-400 font-sans">Nota</span>
                    </h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-600 dark:bg-orange-950/40 dark:text-orange-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-xl">local_laundry_service</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1 text-2xs text-orange-600 dark:text-orange-400 font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 animate-ping shrink-0"></span>
                <span class="truncate">Belum berstatus DIAMBIL</span>
            </div>
        </div>

        <!-- Critical Stock -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0 flex-1">
                    <span class="text-2xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Bahan Stok Kritis</span>
                    <h4 class="text-base sm:text-lg lg:text-xl font-black font-mono mt-1 {{ $lowStockCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ number_format($lowStockCount, 0, ',', '.') }} <span class="text-xs font-bold text-slate-400 font-sans">Item</span>
                    </h4>
                </div>
                <div class="w-10 h-10 rounded-xl {{ $lowStockCount > 0 ? 'text-rose-600 bg-rose-500/10' : 'text-emerald-600 bg-emerald-500/10' }} flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-xl">warning</span>
                </div>
            </div>
            <div class="mt-3 text-2xs font-semibold text-slate-400 dark:text-slate-500 truncate">
                Stok &le; batas minimum
            </div>
        </div>

        <!-- Today Transactions -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0 flex-1">
                    <span class="text-2xs font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Transaksi Hari Ini</span>
                    <h4 class="text-base sm:text-lg lg:text-xl font-black font-mono text-slate-900 dark:text-slate-100 mt-1 whitespace-nowrap">
                        {{ number_format($todayOrdersCount ?? 0, 0, ',', '.') }} <span class="text-xs font-bold text-slate-400 font-sans">Nota</span>
                    </h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400 flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-xl">today</span>
                </div>
            </div>
            <div class="mt-3 text-2xs font-semibold text-slate-400 dark:text-slate-500 truncate">
                Dibuat {{ now()->format('d M Y') }}
            </div>
        </div>
    </div>

    <!-- Quick Navigation & Chart -->
    <div class="grid grid-cols-12 gap-6">
        <!-- Sales Chart Card (8 cols) -->
        <div class="col-span-12 lg:col-span-8 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow">
            <h4 class="text-base font-black text-slate-900 dark:text-white mb-2">Tren Omset Mingguan Cabang</h4>
            <p class="text-xs text-slate-400 mb-6">Total transaksi cabang 7 hari terakhir (Rupiah)</p>
            
            <div class="relative h-64">
                <canvas id="branchRevenueChart"></canvas>
            </div>
        </div>

        <!-- Quick Links Panel (4 cols) -->
        <div class="col-span-12 lg:col-span-4 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow flex flex-col justify-between">
            <div>
                <h4 class="text-base font-black text-slate-900 dark:text-white mb-4">Pintasan Cepat Operasional</h4>
                <div class="grid grid-cols-1 gap-3">
                    <a href="/pos" class="flex items-center gap-3 p-3 bg-primary/5 hover:bg-primary/10 border border-primary/10 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-primary">point_of_sale</span>
                        <div class="text-left">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Point of Sale (POS)</h5>
                            <p class="text-[10px] text-slate-400">Buat nota cucian baru & kasir</p>
                        </div>
                    </a>
                    
                    <a href="/production" class="flex items-center gap-3 p-3 bg-blue-500/5 hover:bg-blue-500/10 border border-blue-550/10 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-blue-500">precision_manufacturing</span>
                        <div class="text-left">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Produksi & Workshop</h5>
                            <p class="text-[10px] text-slate-400">Pantau proses pencucian & setrika</p>
                        </div>
                    </a>

                    <a href="/inventory" class="flex items-center gap-3 p-3 bg-green-500/5 hover:bg-green-500/10 border border-green-550/10 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-green-500">inventory_2</span>
                        <div class="text-left">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Manajemen Stok (BHP)</h5>
                            <p class="text-[10px] text-slate-400">Kelola stok detergen, softener, dll.</p>
                        </div>
                    </a>

                    <a href="/procurement/purchase-requests" class="flex items-center gap-3 p-3 bg-yellow-500/5 hover:bg-yellow-500/10 border border-yellow-550/10 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-yellow-500">shopping_basket</span>
                        <div class="text-left">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Pengadaan (Purchase Request)</h5>
                            <p class="text-[10px] text-slate-400">Ajukan permintaan pembelian barang</p>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="pt-4 border-t border-slate-100 dark:border-slate-850 flex items-center gap-2 text-slate-400 text-[10px]">
                <span class="material-symbols-outlined text-sm">security</span>
                <span>Hak Akses: Branch Administrator</span>
            </div>
        </div>

        <!-- Recent Branch Orders (12 cols) -->
        <div class="col-span-12 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow">
            <div class="flex justify-between items-center mb-6">
                <h4 class="text-base font-extrabold text-slate-800 dark:text-slate-200">Nota Transaksi Terbaru Cabang</h4>
                <a href="{{ route('orders.index') }}" class="text-2xs md:text-xs font-bold text-primary hover:underline inline-flex items-center gap-1">
                    Lihat Semua
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-4">Nomor Nota</th>
                            <th class="py-2.5 px-4">Pelanggan</th>
                            <th class="py-2.5 px-4">Metode Bayar</th>
                            <th class="py-2.5 px-4">Bayar</th>
                            <th class="py-2.5 px-4">Produksi</th>
                            <th class="py-2.5 px-4 text-right">Total</th>
                            <th class="py-2.5 px-4 text-right">Aksi</th>
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
                                <td class="py-3.5 px-4 uppercase text-[10px] font-bold text-slate-505">
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
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('invoices.show', $order) }}"
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-orange-50 dark:bg-slate-800 text-primary text-2xs font-bold rounded-lg hover:bg-orange-100 dark:hover:bg-slate-700 transition-colors">
                                        <span class="material-symbols-outlined text-sm">description</span>
                                        Invoice
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">Belum ada transaksi di cabang ini.</td>
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
            const ctx = document.getElementById('branchRevenueChart').getContext('2d');
            
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
