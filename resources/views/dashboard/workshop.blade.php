<x-app-layout>
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
        <div>
            <h3 class="text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white mb-2 tracking-tight">
                Dashboard Produksi Workshop: {{ $branch->name }}
            </h3>
            <p class="text-slate-500 dark:text-slate-400 font-semibold text-sm">
                Pantau antrean pencucian, penjemuran, setrika, packing, dan pembaruan status pengerjaan laundry.
            </p>
        </div>
        <div class="flex items-center gap-2 bg-white dark:bg-slate-900 px-4 py-2 rounded-xl border border-outline-variant dark:border-slate-800 shadow-sm">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-350">Staf Workshop: {{ auth()->user()->name }}</span>
        </div>
    </div>

    <!-- Active Queue Status Tracker Grid -->
    <div class="bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow mb-8">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-6">Status Alur Antrean Cucian</h4>
        
        <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
            <!-- 1. TERIMA -->
            <div class="bg-slate-50 dark:bg-slate-850 p-4 rounded-xl border border-slate-100 dark:border-slate-800 text-center relative overflow-hidden">
                <span class="text-[9px] font-bold text-slate-400 uppercase">1. TERIMA</span>
                <h5 class="text-2xl font-black text-slate-700 dark:text-slate-200 mt-2">{{ $stats['TERIMA'] }}</h5>
                <div class="absolute bottom-0 inset-x-0 h-1 bg-slate-350"></div>
            </div>

            <!-- 2. CUCI -->
            <div class="bg-slate-50 dark:bg-slate-850 p-4 rounded-xl border border-slate-100 dark:border-slate-800 text-center relative overflow-hidden">
                <span class="text-[9px] font-bold text-slate-400 uppercase">2. CUCI</span>
                <h5 class="text-2xl font-black text-primary mt-2">{{ $stats['CUCI'] }}</h5>
                <div class="absolute bottom-0 inset-x-0 h-1 bg-primary"></div>
            </div>

            <!-- 3. KERING -->
            <div class="bg-slate-50 dark:bg-slate-850 p-4 rounded-xl border border-slate-100 dark:border-slate-800 text-center relative overflow-hidden">
                <span class="text-[9px] font-bold text-slate-400 uppercase">3. JEMUR / KERING</span>
                <h5 class="text-2xl font-black text-amber-500 mt-2">{{ $stats['KERING'] }}</h5>
                <div class="absolute bottom-0 inset-x-0 h-1 bg-amber-550"></div>
            </div>

            <!-- 4. SETRIKA -->
            <div class="bg-slate-50 dark:bg-slate-850 p-4 rounded-xl border border-slate-100 dark:border-slate-800 text-center relative overflow-hidden">
                <span class="text-[9px] font-bold text-slate-400 uppercase">4. SETRIKA</span>
                <h5 class="text-2xl font-black text-blue-500 mt-2">{{ $stats['SETRIKA'] }}</h5>
                <div class="absolute bottom-0 inset-x-0 h-1 bg-blue-500"></div>
            </div>

            <!-- 5. PACKING -->
            <div class="bg-slate-50 dark:bg-slate-850 p-4 rounded-xl border border-slate-100 dark:border-slate-800 text-center relative overflow-hidden">
                <span class="text-[9px] font-bold text-slate-400 uppercase">5. PACKING</span>
                <h5 class="text-2xl font-black text-indigo-500 mt-2">{{ $stats['PACKING'] }}</h5>
                <div class="absolute bottom-0 inset-x-0 h-1 bg-indigo-550"></div>
            </div>

            <!-- 6. SIAP -->
            <div class="bg-slate-50 dark:bg-slate-850 p-4 rounded-xl border border-slate-100 dark:border-slate-800 text-center relative overflow-hidden">
                <span class="text-[9px] font-bold text-slate-400 uppercase">6. SIAP AMBIL</span>
                <h5 class="text-2xl font-black text-green-500 mt-2">{{ $stats['SIAP'] }}</h5>
                <div class="absolute bottom-0 inset-x-0 h-1 bg-green-500"></div>
            </div>
        </div>
    </div>

    <!-- Active Production Queue List -->
    <div class="grid grid-cols-12 gap-6">
        <!-- Queue Table (8 cols) -->
        <div class="col-span-12 lg:col-span-8 bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow">
            <h4 class="text-base font-extrabold text-slate-800 dark:text-slate-200 mb-6">Antrean Kerja Produksi Aktif</h4>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-4">Nomor Nota</th>
                            <th class="py-2.5 px-4">Pelanggan</th>
                            <th class="py-2.5 px-4">Item Detail</th>
                            <th class="py-2.5 px-4">Status Produksi</th>
                            <th class="py-2.5 px-4">Estimasi Selesai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($activeProductionOrders as $order)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $order->order_number }}
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-700 dark:text-slate-350">
                                    {{ $order->customer?->name ?? 'Walk-In Customer' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $order->orderItems->map(fn($it) => $it->quantity . ' ' . $it->service?->name)->join(', ') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-primary-container/10 text-primary border border-primary/20 animate-pulse">
                                        {{ $order->production_status }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-550 font-bold">
                                    {{ $order->estimated_done_at ? \Carbon\Carbon::parse($order->estimated_done_at)->format('d M - H:i') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">Tidak ada antrean cucian aktif di workshop saat ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Summary & Action Card (4 cols) -->
        <div class="col-span-12 lg:col-span-4 flex flex-col gap-6">
            <!-- stats summary -->
            <div class="bg-slate-900 dark:bg-slate-950 text-white rounded-xl p-6 flex flex-col justify-between h-40 relative overflow-hidden">
                <div class="absolute right-0 bottom-0 opacity-5">
                    <span class="material-symbols-outlined text-[120px]">precision_manufacturing</span>
                </div>
                <div class="relative z-10">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Total Cucian Dalam Proses</span>
                    <h5 class="text-3xl font-extrabold mt-2 tracking-tight">{{ number_format($totalActive, 0, ',', '.') }} Nota</h5>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-orange-500 relative z-10 text-xs font-bold">
                    <span class="material-symbols-outlined text-sm font-bold animate-spin">refresh</span>
                    <span>Proses pencucian aktif sedang berjalan</span>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="bg-white dark:bg-slate-900 border border-outline-variant dark:border-slate-800 rounded-xl p-6 premium-shadow flex flex-col justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Aksi Cepat Workshop</h4>
                    <div class="space-y-3">
                        <a href="/production" class="flex items-center gap-3 p-4 bg-primary hover:bg-orange-655 text-white rounded-xl transition-all shadow-md shadow-orange-500/10">
                            <span class="material-symbols-outlined text-[24px]">assignment_turned_in</span>
                            <div class="text-left">
                                <h5 class="text-xs font-black">Lembar Kerja Produksi</h5>
                                <p class="text-[10px] text-white/85">Proses antrean & ubah status cucian</p>
                            </div>
                        </a>
                    </div>
                </div>
                
                <div class="pt-4 border-t border-slate-100 dark:border-slate-850 flex items-center gap-2 text-slate-405 text-[10px] mt-6">
                    <span class="material-symbols-outlined text-sm">security</span>
                    <span>Hak Akses: Staf / Admin Workshop</span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
