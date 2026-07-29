<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6">
        <x-page-header title="Pemantauan Kinerja & Produktivitas" :breadcrumbs="['Kinerja' => route('performance.index')]" />

        <!-- Filter Bar -->
        <x-card :compact="true">
            <form method="GET" action="{{ route('performance.index') }}" class="flex flex-wrap items-end gap-3">
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                    <div class="flex flex-col gap-1">
                        <label class="text-2xs font-bold text-slate-400 uppercase">Cabang</label>
                        <select name="branch_id" onchange="this.form.submit()" class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold px-2">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="flex flex-col gap-1">
                    <label class="text-2xs font-bold text-slate-400 uppercase">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-2 font-semibold">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-2xs font-bold text-slate-400 uppercase">Sampai Tanggal</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-2 font-semibold">
                </div>
                <input type="hidden" name="branch_id" value="{{ $branchId }}">
                <button type="submit" class="h-9 px-4 bg-primary text-white text-xs font-bold rounded-xl flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base">filter_alt</span> Terapkan Filter
                </button>
                <a href="{{ route('performance.index') }}" class="h-9 px-3 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">refresh</span>
                </a>
            </form>
        </x-card>

        <!-- Period Summary Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <x-stat-card title="Order Aktif" :value="$totalActiveOrders . ' Nota'" icon="local_laundry_service" description="Antrean workshop" />
            <x-stat-card title="Terlambat (Overdue)" :value="$overdueOrders . ' Nota'" icon="warning" trend="{{ $overdueRate . '%' }}" trendType="{{ $overdueOrders > 0 ? 'danger' : 'success' }}" description="Melewati estimasi" />
            <x-stat-card title="Kepatuhan SLA" :value="(100 - $overdueRate) . '%'" icon="verified" trendType="success" description="Selesai tepat waktu" />
            <x-stat-card title="Omset Periode" :value="'Rp ' . number_format($periodRevenue, 0, ',', '.')" icon="payments" description="{{ $dateFrom }} — {{ $dateTo }}" />
            <x-stat-card title="Rata-rata Nota" :value="'Rp ' . number_format($periodAvgOrder, 0, ',', '.')" icon="receipt" :description="$periodOrders . ' transaksi di periode ini'" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Cashiers Leaderboard (6 cols) -->
            <div class="lg:col-span-6">
                <x-card title="Kinerja Kasir — Periode {{ $dateFrom }} s/d {{ $dateTo }}">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Kasir</th>
                                    <th class="py-2.5 px-3 text-center">Total Nota</th>
                                    <th class="py-2.5 px-3 text-right">Omset Lunas</th>
                                    <th class="py-2.5 px-3 text-right">Pending</th>
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
                                            {{ number_format($cashier->total_orders) }} nota
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold font-mono text-emerald-600">
                                            Rp {{ number_format($cashier->total_revenue ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold font-mono text-amber-500">
                                            Rp {{ number_format($cashier->total_pending_revenue ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-400">Belum ada data transaksi kasir di periode ini.</td>
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
                                    <th class="py-2.5 px-3 text-center">Order Unik</th>
                                    <th class="py-2.5 px-3 text-right">Selesai (SIAP)</th>
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
                                        <td class="py-3 px-3 text-center text-slate-600">
                                            {{ $staff->unique_orders }} nota
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold font-mono text-emerald-600">
                                            {{ number_format($staff->completed_orders) }} Nota
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-400">Belum ada riwayat aktivitas staf workshop di periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Daily Cashier Breakdown -->
        @if($cashierDailyBreakdown->isNotEmpty())
            <x-card title="Rincian Harian Transaksi per Kasir">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-2.5 px-3">Tanggal</th>
                                <th class="py-2.5 px-3">Kasir</th>
                                <th class="py-2.5 px-3 text-center">Nota</th>
                                <th class="py-2.5 px-3 text-right">Omset Lunas</th>
                                <th class="py-2.5 px-3 text-right">Pending</th>
                                <th class="py-2.5 px-3 text-right">Total Diskon</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                            @foreach($cashierDailyBreakdown as $row)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                    <td class="py-2.5 px-3 font-bold text-slate-700 dark:text-slate-300">
                                        {{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}
                                        @if(\Carbon\Carbon::parse($row->date)->isToday())
                                            <span class="ml-1 px-1.5 py-0.5 bg-primary/10 text-primary text-[9px] font-extrabold rounded-full">Hari Ini</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 font-semibold text-slate-700 dark:text-slate-300">
                                        {{ $row->cashier?->name ?? 'N/A' }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-700">{{ $row->total_orders }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono font-bold text-emerald-600">
                                        Rp {{ number_format($row->paid_revenue, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono {{ $row->pending_revenue > 0 ? 'text-amber-500 font-bold' : 'text-slate-400' }}">
                                        Rp {{ number_format($row->pending_revenue, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-right font-mono text-rose-600">
                                        Rp {{ number_format($row->total_discount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif

    </div>
</x-app-layout>
