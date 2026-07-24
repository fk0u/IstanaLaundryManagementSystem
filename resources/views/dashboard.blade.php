<x-app-layout>
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-4">
        <div>
            <h3 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mb-2 tracking-tight">
                {{ !$branchId ? 'Ringkasan Super Admin' : 'Ringkasan Cabang' }}
            </h3>
            <p class="text-slate-500 dark:text-slate-400 font-semibold text-sm">
                {{ !$branchId ? 'Pantau performa seluruh cabang dalam satu ekosistem terpadu.' : 'Pantau performa cabang aktif dan antrean produksi.' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center gap-2 bg-white dark:bg-slate-900 px-4 py-2 rounded-lg border border-outline-variant dark:border-slate-800 shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-[#00C853] animate-pulse"></span>
                <span class="text-xs font-bold text-slate-700 dark:text-slate-350">System Health: Optimal</span>
            </div>
            <div class="flex items-center gap-2 bg-primary-container/10 dark:bg-orange-500/10 px-4 py-2 rounded-lg border border-primary/20 dark:border-orange-500/20">
                <span class="material-symbols-outlined text-primary text-[18px]">verified</span>
                <span class="text-xs text-primary font-bold">Premium Subscription Active</span>
            </div>
        </div>
    </div>

    <!-- Bento Grid Dashboard -->
    <div class="grid grid-cols-12 gap-6">
        <!-- Main Chart Card (8 cols) -->
        <div class="col-span-12 lg:col-span-8 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-8 premium-shadow transition-shadow">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h4 class="text-lg font-black text-slate-900 dark:text-white">{{ $chartTitle }}</h4>
                    <p class="text-xs text-slate-400 dark:text-slate-500 font-semibold mt-1">{{ $chartSub }}</p>
                </div>
                <select class="bg-slate-50 dark:bg-slate-950 border border-outline-variant dark:border-slate-850 rounded-lg text-xs font-bold px-4 py-2 outline-none focus:border-primary text-slate-650">
                    <option>Bulan Ini</option>
                    <option>Kuartal Terakhir</option>
                </select>
            </div>
            
            <div class="h-64 flex items-end justify-between gap-6 px-4 border-b border-outline-variant/30 dark:border-slate-800/50 pb-2">
                @foreach ($chartData as $data)
                    @php
                        $heightPercentage = $maxRevenue > 0 ? ($data['amount'] / $maxRevenue) * 100 : 0;
                        $heightPercentage = max($heightPercentage, 5); // min 5% height
                    @endphp
                    <div class="flex flex-col items-center flex-1 group">
                        <!-- Bar -->
                        <div style="height: {{ $heightPercentage }}%" 
                             class="w-full rounded-t-lg transition-all duration-500 relative cursor-pointer
                                    {{ $loop->last ? 'bg-primary shadow-[0_0_15px_rgba(255,102,0,0.2)]' : 'bg-primary-container/20 hover:bg-primary-container/40 dark:bg-slate-800 dark:hover:bg-slate-700' }}">
                            <!-- Tooltip -->
                            <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] font-extrabold px-2.5 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg z-10">
                                {{ $data['formatted'] }}
                            </div>
                        </div>
                        <span class="text-[10px] font-bold mt-4 text-slate-400 dark:text-slate-500 text-center truncate w-full">
                            {{ $data['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- KPI Side Column (4 cols) -->
        <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
            <div class="bg-slate-900 dark:bg-slate-950 text-white rounded-xl p-8 flex flex-col justify-between h-full relative overflow-hidden">
                <div class="absolute right-0 bottom-0 opacity-5">
                    <span class="material-symbols-outlined text-[150px]">point_of_sale</span>
                </div>
                <div class="relative z-10">
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Volume Transaksi</span>
                    <h5 class="text-4xl font-extrabold mt-3 tracking-tight">{{ number_format($totalTransactions, 0, ',', '.') }}</h5>
                </div>
                <div class="mt-8 flex items-center gap-2 text-[#00C853] relative z-10">
                    <span class="material-symbols-outlined text-base">trending_up</span>
                    <span class="text-xs font-bold">+12.4% vs Bulan Lalu</span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 flex items-center gap-6 premium-shadow transition-shadow">
                <div class="w-12 h-12 rounded-xl bg-primary-container/10 flex items-center justify-center text-primary dark:text-orange-400 shrink-0">
                    <span class="material-symbols-outlined text-[32px]">shield_with_heart</span>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Subscription Tier</p>
                    <h6 class="text-base font-extrabold text-slate-800 dark:text-slate-200">Enterprise Gold</h6>
                </div>
            </div>
        </div>

        <!-- System Health Bento Item (4 cols) -->
        <div class="col-span-12 md:col-span-6 lg:col-span-4 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-8 premium-shadow transition-shadow">
            <div class="flex justify-between items-start mb-6">
                <h4 class="text-base font-extrabold text-slate-800 dark:text-slate-200">Kesehatan Server</h4>
                <span class="material-symbols-outlined text-primary dark:text-orange-400">analytics</span>
            </div>
            <div class="space-y-6">
                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">
                        <span>API Response Time</span>
                        <span class="font-extrabold text-slate-900 dark:text-white">42ms</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-850 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-[#00C853] h-full w-[95%]"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">
                        <span>Server CPU Load</span>
                        <span class="font-extrabold text-slate-900 dark:text-white">24%</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-850 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-primary h-full w-[24%]"></div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">
                        <span>Penyimpanan Aset (S3)</span>
                        <span class="font-extrabold text-slate-900 dark:text-white">1.2TB / 5TB</span>
                    </div>
                    <div class="w-full bg-slate-100 dark:bg-slate-850 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-slate-800 dark:bg-slate-400 h-full w-[30%]"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Status Bento Item (4 cols) -->
        <div class="col-span-12 md:col-span-6 lg:col-span-4 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-8 premium-shadow transition-shadow flex flex-col justify-between">
            <h4 class="text-base font-extrabold text-slate-800 dark:text-slate-200 mb-6">Status Langganan</h4>
            <div class="p-6 bg-slate-50 dark:bg-slate-950 rounded-xl mb-4 border border-slate-100 dark:border-slate-850">
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400">Siklus Penagihan</span>
                    <span class="text-[10px] font-bold px-2 py-1 bg-primary text-white rounded">Auto-Renew</span>
                </div>
                <p class="text-xs font-semibold text-slate-400 mb-1">Pembayaran Berikutnya:</p>
                <p class="text-xl font-black text-slate-800 dark:text-white">12 Okt 2026</p>
            </div>
            <button class="w-full border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-350 py-3 rounded-xl font-bold text-xs hover:bg-slate-50 dark:hover:bg-slate-950 transition-colors">
                Kelola Billing
            </button>
        </div>

        <!-- Activity Log / Latest Transactions (4 cols) -->
        <div class="col-span-12 lg:col-span-4 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-8 premium-shadow transition-shadow">
            <h4 class="text-base font-extrabold text-slate-800 dark:text-slate-200 mb-6">Aktivitas Sistem</h4>
            <div class="space-y-6">
                <div class="flex gap-4 items-start">
                    <div class="w-2.5 h-2.5 rounded-full bg-primary mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Payout Berhasil</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Rp 12.500.000 ke Cabang Samarinda Central</p>
                        <span class="text-[9px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-widest mt-1.5 block">5 Menit Lalu</span>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <div class="w-2.5 h-2.5 rounded-full bg-[#00C853] mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Integrasi API Baru</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Metode Pembayaran QRIS OVO Terhubung</p>
                        <span class="text-[9px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-widest mt-1.5 block">2 Jam Lalu</span>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <div class="w-2.5 h-2.5 rounded-full bg-slate-600 mt-1.5 shrink-0"></div>
                    <div>
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Update Inventaris</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">Pasokan Deterjen Konsentrat Masuk Gudang</p>
                        <span class="text-[9px] text-slate-400 dark:text-slate-500 uppercase font-bold tracking-widest mt-1.5 block">4 Jam Lalu</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
