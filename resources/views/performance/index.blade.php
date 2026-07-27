<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6">
        <x-page-header title="Pemantauan Kinerja & Produktivitas" :breadcrumbs="['Kinerja' => route('performance.index')]" />

        <!-- Filter Bar -->
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
            <x-card :compact="true">
                <form method="GET" action="{{ route('performance.index') }}" class="flex items-center gap-3">
                    <label class="text-2xs font-bold text-slate-400 uppercase">Filter Cabang:</label>
                    <select name="branch_id" onchange="this.form.submit()" class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold">
                        <option value="">Semua Cabang (Global)</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </form>
            </x-card>
        @endif

        <!-- Executive Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-stat-card title="Order Aktif Diproses" :value="$totalActiveOrders . ' Nota'" icon="local_laundry_service" description="Antrean workshop" />
            <x-stat-card title="Terlambat (Overdue)" :value="$overdueOrders . ' Nota'" icon="warning" trend="{{ $overdueRate . '%' }}" trendType="{{ $overdueOrders > 0 ? 'danger' : 'success' }}" description="Melewati estimasi" />
            <x-stat-card title="Tingkat Kepatuhan SLA" :value="(100 - $overdueRate) . '%'" icon="verified" trendType="success" description="Selesai tepat waktu" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Cashiers Leaderboard (6 cols) -->
            <div class="lg:col-span-6">
                <x-card title="Kinerja Kasir (Penjualan)">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Kasir</th>
                                    <th class="py-2.5 px-3 text-center">Total Order</th>
                                    <th class="py-2.5 px-3 text-right">Total Omset</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                                @forelse($cashiers as $index => $cashier)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                        <td class="py-3 px-3 flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full font-bold text-2xs flex items-center justify-center {{ $loop->first ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400' }}">
                                                {{ $loop->iteration }}
                                            </span>
                                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $cashier->name }}</span>
                                        </td>
                                        <td class="py-3 px-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                            {{ number_format($cashier->total_orders) }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold font-mono text-primary">
                                            Rp {{ number_format($cashier->total_revenue ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-slate-400">Belum ada data transaksi kasir.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            <!-- Workshop Staff Productivity (6 cols) -->
            <div class="lg:col-span-6">
                <x-card title="Produktivitas Staf Workshop">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Nama Staf</th>
                                    <th class="py-2.5 px-3 text-center">Aksi Transisi</th>
                                    <th class="py-2.5 px-3 text-right">Order Selesai (SIAP)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                                @forelse($staffProductivity as $staff)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                        <td class="py-3 px-3 font-bold text-slate-800 dark:text-slate-200">
                                            {{ $staff->staff_name }}
                                        </td>
                                        <td class="py-3 px-3 text-center font-bold text-slate-700 dark:text-slate-300">
                                            {{ number_format($staff->total_actions) }}x
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold font-mono text-emerald-600">
                                            {{ number_format($staff->completed_orders) }} Nota
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-8 text-center text-slate-400">Belum ada riwayat aktivitas staf workshop.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>
        </div>

    </div>
</x-app-layout>
