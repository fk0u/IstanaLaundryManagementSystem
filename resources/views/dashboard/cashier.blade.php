<x-app-layout>
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div>
            <h3 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mb-2 tracking-tight">
                Dashboard Kasir: {{ $branch->name }}
            </h3>
            <p class="text-slate-500 dark:text-slate-400 font-semibold text-sm">
                Kelola transaksi laundry, input cucian baru, dan pantau histori nota kasir Anda.
            </p>
        </div>
        <div class="flex items-center gap-2 bg-white dark:bg-slate-900 px-4 py-2 rounded-xl border border-outline-variant dark:border-slate-800 shadow-sm">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-350">Kasir Bertugas: {{ auth()->user()->name }}</span>
        </div>
    </div>

    <!-- Cashier Summary Cards -->
    <div class="grid grid-cols-3 gap-6 mb-8">
        <!-- Today Transactions Count -->
        <div class="col-span-3 md:col-span-1 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Transaksi Anda Hari Ini</span>
                    <h4 class="text-xl font-extrabold text-slate-800 dark:text-slate-100 mt-1">{{ number_format($todayTransactionsCount, 0, ',', '.') }} Nota</h4>
                </div>
                <span class="material-symbols-outlined text-primary bg-primary-container/10 p-2.5 rounded-lg">receipt_long</span>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-slate-500 text-[10px] font-bold">
                <span class="material-symbols-outlined text-xs">info</span>
                <span>Jumlah nota yang Anda buat hari ini</span>
            </div>
        </div>

        <!-- Today Revenue -->
        <div class="col-span-3 md:col-span-1 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Omset Anda Hari Ini</span>
                    <h4 class="text-xl font-extrabold text-slate-800 dark:text-slate-100 mt-1">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</h4>
                </div>
                <span class="material-symbols-outlined text-green-500 bg-green-500/10 p-2.5 rounded-lg">monetization_on</span>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-slate-550 text-[10px] font-bold">
                <span class="material-symbols-outlined text-xs">payments</span>
                <span>Akumulasi nominal transaksi Anda</span>
            </div>
        </div>

        <!-- Total Customer Count -->
        <div class="col-span-3 md:col-span-1 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow flex flex-col justify-between">
            <div class="flex justify-between items-start">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pelanggan Cabang</span>
                    <h4 class="text-xl font-extrabold text-slate-800 dark:text-slate-100 mt-1">{{ number_format($customerCount, 0, ',', '.') }} Orang</h4>
                </div>
                <span class="material-symbols-outlined text-blue-500 bg-blue-500/10 p-2.5 rounded-lg">groups</span>
            </div>
            <div class="mt-4 text-[10px] font-semibold text-slate-400">
                Terdaftar di database <span class="font-bold text-slate-500">Cabang {{ $branch->name }}</span>
            </div>
        </div>
    </div>

    <!-- Production Pipeline Widget -->
    <x-production-pipeline :breakdown="$productionBreakdown" class="mb-6" />

    <!-- Quick Actions Panel & Recent Transactions -->
    <div class="grid grid-cols-12 gap-6">
        <!-- Quick Actions (4 cols) -->
        <div class="col-span-12 lg:col-span-4 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow flex flex-col justify-between">
            <div>
                <h4 class="text-base font-black text-slate-900 dark:text-white mb-4">Aksi Cepat Kasir</h4>
                <div class="grid grid-cols-1 gap-3">
                    <a href="/pos" class="flex items-center gap-3 p-4 bg-primary hover:bg-orange-655 text-white rounded-xl transition-all shadow-md shadow-orange-500/10">
                        <span class="material-symbols-outlined text-[24px]">point_of_sale</span>
                        <div class="text-left">
                            <h5 class="text-xs font-black">Buka Layar POS</h5>
                            <p class="text-[10px] text-white/80">Input order & cetak nota baru</p>
                        </div>
                    </a>
                    
                    <a href="/customers" class="flex items-center gap-3 p-3 bg-blue-500/5 hover:bg-blue-500/10 border border-blue-550/10 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-blue-550">person_add</span>
                        <div class="text-left">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Registrasi Pelanggan</h5>
                            <p class="text-[10px] text-slate-400">Tambah member & cek poin loyalitas</p>
                        </div>
                    </a>

                    <a href="/refunds" class="flex items-center gap-3 p-3 bg-red-500/5 hover:bg-red-500/10 border border-red-550/10 rounded-xl transition-all">
                        <span class="material-symbols-outlined text-red-500">assignment_return</span>
                        <div class="text-left">
                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-200">Ajukan Refund</h5>
                            <p class="text-[10px] text-slate-400">Pembatalan nota & retur dana</p>
                        </div>
                    </a>
                </div>
            </div>
            
            <div class="pt-4 border-t border-slate-100 dark:border-slate-850 flex items-center gap-2 text-slate-400 text-[10px]">
                <span class="material-symbols-outlined text-sm">security</span>
                <span>Hak Akses: Kasir POS</span>
            </div>
        </div>

        <!-- Recent Cashier Transactions (8 cols) -->
        <div class="col-span-12 lg:col-span-8 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow">
            <h4 class="text-base font-extrabold text-slate-800 dark:text-slate-200 mb-6">Nota Transaksi Terakhir yang Anda Input</h4>
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
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Anda belum membuat transaksi hari ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
