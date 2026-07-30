<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="flex flex-col gap-4 md:gap-6">
        <x-page-header title="Laporan Keuangan" :breadcrumbs="['Keuangan' => '/finance', 'Laporan' => '/finance/reports']" />

        <!-- Filter Bar -->
        <x-card :compact="true">
            <form method="GET" action="{{ route('finance.reports.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="{{ $tab }}">
                
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                    <div class="flex flex-col gap-1 min-w-[150px]">
                        <label class="text-2xs font-bold text-slate-400 uppercase">Cabang</label>
                        <select name="branch_id" onchange="this.form.submit()" class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold">
                            <option value="">Semua Cabang (Konsolidasi)</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex flex-col gap-1 min-w-[100px]">
                    <label class="text-2xs font-bold text-slate-400 uppercase">Tahun</label>
                    <select name="year" onchange="this.form.submit()" class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold">
                        @foreach(range(date('Y'), 2024) as $y)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1 min-w-[120px]">
                    <label class="text-2xs font-bold text-slate-400 uppercase">Bulan</label>
                    <select name="month" onchange="this.form.submit()" class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold">
                        <option value="">Semua Bulan (Tahunan)</option>
                        @foreach([1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'] as $mNum => $mName)
                            <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto mt-2 sm:mt-auto">
                    <button type="submit" class="h-9 px-4 bg-primary text-white text-xs font-bold rounded-xl flex items-center gap-1 shadow-2xs cursor-pointer">
                        <span class="material-symbols-outlined text-base">filter_alt</span> Filter
                    </button>
                    <a href="{{ route('finance.reports.excel', request()->all()) }}" class="h-9 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl flex items-center gap-1 shadow-2xs">
                        <span class="material-symbols-outlined text-base">table_view</span> Ekspor Excel (.xlsx)
                    </a>
                    <a href="{{ route('finance.reports.pdf', request()->all()) }}" target="_blank" class="h-9 px-3 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl flex items-center gap-1 shadow-2xs">
                        <span class="material-symbols-outlined text-base">analytics</span> Cetak PowerBI PDF
                    </a>
                    <a href="{{ route('finance.closing-checklist') }}" class="h-9 px-3 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl flex items-center gap-1 shadow-2xs">
                        <span class="material-symbols-outlined text-base">checklist</span> Closing Checklist
                    </a>
                </div>
            </form>
        </x-card>

        <!-- Report Tabs -->
        <div class="flex border-b border-slate-200 dark:border-slate-800 gap-4 text-xs font-bold">
            <a href="{{ route('finance.reports.index', array_merge(request()->query(), ['tab' => 'analytics'])) }}" 
               class="pb-3 border-b-2 transition-all {{ $tab === 'analytics' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                Analytics KPI & Breakdown Cabang
            </a>
            <a href="{{ route('finance.reports.index', array_merge(request()->query(), ['tab' => 'income'])) }}" 
               class="pb-3 border-b-2 transition-all {{ $tab === 'income' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                Laba Rugi (Income Statement)
            </a>
            <a href="{{ route('finance.reports.index', array_merge(request()->query(), ['tab' => 'balance'])) }}" 
               class="pb-3 border-b-2 transition-all {{ $tab === 'balance' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                Neraca (Balance Sheet)
            </a>
            <a href="{{ route('finance.reports.index', array_merge(request()->query(), ['tab' => 'trial'])) }}" 
               class="pb-3 border-b-2 transition-all {{ $tab === 'trial' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                Neraca Saldo (Trial Balance)
            </a>
        </div>

        <!-- Tab 0: Analytics & KPI Breakdown -->
        @if($tab === 'analytics' || empty($tab))
            <div class="flex flex-col gap-6">
                <!-- Executive KPI Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-card :compact="true">
                        <div class="flex flex-col gap-1">
                            <span class="text-2xs font-extrabold uppercase text-slate-400">Total Omset Kotor</span>
                            <span class="text-lg font-black text-slate-800 dark:text-slate-100 font-mono">Rp {{ number_format($kpiAnalytics['total_gross_revenue'], 0, ',', '.') }}</span>
                            <span class="text-2xs text-slate-400 font-semibold">{{ $kpiAnalytics['total_orders_count'] }} Transaksi Terproses</span>
                        </div>
                    </x-card>

                    <x-card :compact="true">
                        <div class="flex flex-col gap-1">
                            <span class="text-2xs font-extrabold uppercase text-emerald-600">Omset Terbayar (Paid)</span>
                            <span class="text-lg font-black text-emerald-600 font-mono">Rp {{ number_format($kpiAnalytics['total_paid_revenue'], 0, ',', '.') }}</span>
                            <span class="text-2xs text-emerald-500 font-semibold">Kas & Transfer Masuk</span>
                        </div>
                    </x-card>

                    <x-card :compact="true">
                        <div class="flex flex-col gap-1">
                            <span class="text-2xs font-extrabold uppercase text-amber-600">Piutang / Unpaid</span>
                            <span class="text-lg font-black text-amber-600 font-mono">Rp {{ number_format($kpiAnalytics['total_outstanding_piutang'], 0, ',', '.') }}</span>
                            <span class="text-2xs text-amber-500 font-semibold">Belum Pelunasan</span>
                        </div>
                    </x-card>

                    <x-card :compact="true">
                        <div class="flex flex-col gap-1">
                            <span class="text-2xs font-extrabold uppercase text-primary">Rata-rata Nota (Basket Size)</span>
                            <span class="text-lg font-black text-primary font-mono">Rp {{ number_format($kpiAnalytics['average_basket_size'], 0, ',', '.') }}</span>
                            <span class="text-2xs text-slate-400 font-semibold">Per Nota Transaksi</span>
                        </div>
                    </x-card>
                </div>

                <!-- Visual Chart Card for Analytics -->
                <x-card title="Grafik Perbandingan Omset & Terbayar Per Cabang">
                    <div class="relative h-64 md:h-72">
                        <canvas id="analyticsBranchChart"></canvas>
                    </div>
                </x-card>

                <!-- Deep Branch Performance Ranking Table -->
                <x-card title="Transparansi Performa Per Cabang">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4 text-left">Cabang</th>
                                    <th class="py-3 px-4 text-right">Total Transaksi</th>
                                    <th class="py-3 px-4 text-right">Total Omset</th>
                                    <th class="py-3 px-4 text-right">Terbayar (Paid)</th>
                                    <th class="py-3 px-4 text-right">Piutang (Unpaid)</th>
                                    <th class="py-3 px-4 text-right">Rata-rata / Order</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850 font-mono">
                                @forelse($kpiAnalytics['branch_breakdown'] as $br)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                        <td class="py-3.5 px-4 font-sans font-bold text-slate-800 dark:text-slate-200">
                                            {{ $br['name'] }}
                                            <span class="text-2xs font-mono text-slate-400 block font-normal">{{ $br['code'] }}</span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right text-slate-700 dark:text-slate-300 font-sans font-semibold">
                                            {{ number_format($br['total_orders']) }} Nota
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-extrabold text-slate-900 dark:text-slate-100">
                                            Rp {{ number_format($br['total_revenue'], 0, ',', '.') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right text-emerald-600 font-bold">
                                            Rp {{ number_format($br['paid_revenue'], 0, ',', '.') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right text-amber-600 font-bold">
                                            Rp {{ number_format($br['unpaid_revenue'], 0, ',', '.') }}
                                        </td>
                                        <td class="py-3.5 px-4 text-right text-slate-600 dark:text-slate-400 font-sans">
                                            Rp {{ number_format($br['avg_order_value'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400 font-sans">Belum ada data transaksi cabang.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>

                <!-- Service Popularity & Payment Methods Breakdown -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Top 5 Services (Paling Laris) -->
                    <div class="lg:col-span-6">
                        <x-card title="Layanan Paling Laris (Top Popular)">
                            <div class="space-y-3">
                                @forelse($kpiAnalytics['top_services'] as $index => $srv)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30">
                                        <div class="flex items-center gap-3">
                                            <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white font-extrabold text-xs flex items-center justify-center">#{{ $index + 1 }}</span>
                                            <div>
                                                <span class="font-bold text-xs text-slate-800 dark:text-slate-200 block">{{ $srv->name }}</span>
                                                <span class="text-2xs text-slate-400 uppercase font-semibold">{{ $srv->type }} • {{ $srv->unit }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right font-mono">
                                            <span class="font-black text-xs text-emerald-600 block">{{ number_format($srv->total_qty) }} {{ $srv->unit }}</span>
                                            <span class="text-2xs text-slate-400">Rp {{ number_format($srv->total_revenue, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-slate-400 text-xs">Belum ada data penjualan layanan.</div>
                                @endforelse
                            </div>
                        </x-card>
                    </div>

                    <!-- Bottom 5 Services (Paling Sepi) -->
                    <div class="lg:col-span-6">
                        <x-card title="Layanan Paling Sepi Ditempah (Low Demand)">
                            <div class="space-y-3">
                                @forelse($kpiAnalytics['least_services'] as $index => $srv)
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800">
                                        <div class="flex items-center gap-3">
                                            <span class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-extrabold text-xs flex items-center justify-center">#{{ $index + 1 }}</span>
                                            <div>
                                                <span class="font-bold text-xs text-slate-800 dark:text-slate-200 block">{{ $srv->name }}</span>
                                                <span class="text-2xs text-slate-400 uppercase font-semibold">{{ $srv->type }} • {{ $srv->unit }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right font-mono">
                                            <span class="font-bold text-xs text-slate-600 dark:text-slate-400 block">{{ number_format($srv->total_qty) }} {{ $srv->unit }}</span>
                                            <span class="text-2xs text-slate-400">Rp {{ number_format($srv->total_revenue, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="py-6 text-center text-slate-400 text-xs">Belum ada data penjualan layanan.</div>
                                @endforelse
                            </div>
                        </x-card>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', () => {
                        const ctxBranch = document.getElementById('analyticsBranchChart');
                        if (ctxBranch) {
                            const branchData = @js($kpiAnalytics['branch_breakdown']);
                            const labels = branchData.map(b => b.name);
                            const totalRev = branchData.map(b => b.total_revenue);
                            const paidRev = branchData.map(b => b.paid_revenue);
                            const unpaidRev = branchData.map(b => b.unpaid_revenue);

                            new Chart(ctxBranch.getContext('2d'), {
                                type: 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [
                                        { label: 'Total Omset (Rp)', data: totalRev, backgroundColor: 'rgba(255, 102, 0, 0.75)', borderRadius: 6 },
                                        { label: 'Terbayar / Paid (Rp)', data: paidRev, backgroundColor: 'rgba(16, 185, 129, 0.75)', borderRadius: 6 },
                                        { label: 'Piutang / Unpaid (Rp)', data: unpaidRev, backgroundColor: 'rgba(245, 158, 11, 0.75)', borderRadius: 6 }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: { tooltip: { callbacks: { label: c => ' ' + c.dataset.label + ': Rp ' + Number(c.raw).toLocaleString('id-ID') } } }
                                }
                            });
                        }
                    });
                </script>

            </div>
        @endif

        <!-- Tab 1: Income Statement (Laba Rugi) -->
        @if($tab === 'income')
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Visual Historical Line Chart Card for Income -->
                <div class="lg:col-span-12">
                    <x-card title="Tren Historikal Laba Rugi Bulanan (Tahun {{ $year }})">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-2xs text-slate-400 font-medium">Perbandingan pendapatan, beban operasional, dan laba bersih per bulan</p>
                            <div class="flex items-center gap-3 text-2xs font-bold">
                                <span class="flex items-center gap-1 text-emerald-600"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Pendapatan</span>
                                <span class="flex items-center gap-1 text-rose-600"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Beban</span>
                                <span class="flex items-center gap-1 text-sky-600"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> Laba Bersih</span>
                            </div>
                        </div>
                        <div class="relative h-64 md:h-80">
                            <canvas id="historicalIncomeChart"></canvas>
                        </div>
                    </x-card>
                </div>

                <!-- Table View for Income -->
                <div class="lg:col-span-12">
                    <x-card title="Laporan Laba Rugi Rinci (Periode: {{ $month ? date('F Y', mktime(0,0,0,$month,10,$year)) : 'Tahun '.$year }})">
                        <div class="space-y-6 py-2">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">trending_up</span>
                                    Pendapatan Operasional (Revenue)
                                </h4>
                                <div class="space-y-2 text-xs">
                                    @forelse($incomeStatement['revenues'] as $rev)
                                        <div class="flex justify-between border-b border-slate-50 dark:border-slate-800/50 pb-1">
                                            <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ $rev['code'] }} - {{ $rev['name'] }}</span>
                                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($rev['amount'], 0, ',', '.') }}</span>
                                        </div>
                                    @empty
                                        <div class="text-slate-400 text-2xs py-2">Belum ada pendapatan terposting.</div>
                                    @endforelse
                                </div>
                                <div class="flex justify-between text-sm font-bold pt-2 mt-2 border-t-2 border-slate-200 dark:border-slate-700">
                                    <span>TOTAL PENDAPATAN</span>
                                    <span class="text-emerald-600 font-mono font-black">Rp {{ number_format($incomeStatement['total_revenue'], 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-rose-600 mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-sm">payments</span>
                                    Beban-Beban Operasional (Expenses)
                                </h4>
                                <div class="space-y-2 text-xs">
                                    @forelse($incomeStatement['expenses'] as $exp)
                                        <div class="flex justify-between border-b border-slate-50 dark:border-slate-800/50 pb-1">
                                            <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ $exp['code'] }} - {{ $exp['name'] }}</span>
                                            <span class="font-mono font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($exp['amount'], 0, ',', '.') }}</span>
                                        </div>
                                    @empty
                                        <div class="text-slate-400 text-2xs py-2">Belum ada beban terposting.</div>
                                    @endforelse
                                </div>
                                <div class="flex justify-between text-sm font-bold pt-2 mt-2 border-t-2 border-slate-200 dark:border-slate-700">
                                    <span>TOTAL BEBAN OPERASIONAL</span>
                                    <span class="text-rose-600 font-mono font-black">Rp {{ number_format($incomeStatement['total_expense'], 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl border {{ $incomeStatement['net_income'] >= 0 ? 'bg-emerald-50/70 border-emerald-200 text-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-300' : 'bg-rose-50/70 border-rose-200 text-rose-900 dark:bg-rose-950/30 dark:text-rose-300' }} flex justify-between items-center shadow-2xs">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-xl">{{ $incomeStatement['net_income'] >= 0 ? 'account_balance_wallet' : 'warning' }}</span>
                                    <span class="font-black text-sm uppercase tracking-tight">Laba (Rugi) Bersih Periode Ini</span>
                                </div>
                                <span class="font-mono text-xl font-black">Rp {{ number_format($incomeStatement['net_income'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const ctxHist = document.getElementById('historicalIncomeChart');
                    if (ctxHist) {
                        const trendData = @js($historicalIncomeTrend);
                        new Chart(ctxHist.getContext('2d'), {
                            type: 'line',
                            data: {
                                labels: trendData.labels,
                                datasets: [
                                    {
                                        label: 'Pendapatan (Revenue)',
                                        data: trendData.revenues,
                                        borderColor: '#10b981',
                                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                                        borderWidth: 3,
                                        fill: true,
                                        tension: 0.35,
                                        pointRadius: 4,
                                        pointHoverRadius: 6
                                    },
                                    {
                                        label: 'Beban (Expenses)',
                                        data: trendData.expenses,
                                        borderColor: '#ef4444',
                                        backgroundColor: 'rgba(239, 68, 68, 0.08)',
                                        borderWidth: 3,
                                        fill: true,
                                        tension: 0.35,
                                        pointRadius: 4,
                                        pointHoverRadius: 6
                                    },
                                    {
                                        label: 'Laba Bersih (Net Income)',
                                        data: trendData.net_incomes,
                                        borderColor: '#0284c7',
                                        backgroundColor: 'transparent',
                                        borderWidth: 3,
                                        borderDash: [5, 5],
                                        fill: false,
                                        tension: 0.35,
                                        pointRadius: 4,
                                        pointHoverRadius: 6
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: c => ' ' + c.dataset.label + ': Rp ' + Number(c.raw).toLocaleString('id-ID')
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        ticks: {
                                            callback: function(v) {
                                                if (v >= 1000000) return 'Rp ' + (v/1000000).toFixed(1) + 'M';
                                                if (v >= 1000) return 'Rp ' + (v/1000).toFixed(0) + 'k';
                                                return 'Rp ' + v;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            </script>
        @endif

        <!-- Tab 2: Balance Sheet (Neraca) -->
        @if($tab === 'balance')
            <div class="space-y-6" x-data="{ activeBalanceChart: 'pie' }">
                <!-- Dual-Option Chart Card for Balance Sheet -->
                <x-card>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-100">Visualisasi Analisis Neraca</h4>
                            <p class="text-2xs text-slate-400">Pilih opsi tampilan grafik di sebelah kanan untuk melihat komposisi aset atau perbandingan neraca.</p>
                        </div>
                        <!-- Chart Switcher Buttons -->
                        <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800 rounded-xl gap-1 shrink-0">
                            <button @click="activeBalanceChart = 'pie'" 
                                    :class="{ 'bg-white dark:bg-slate-900 text-primary shadow-2xs': activeBalanceChart === 'pie', 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300': activeBalanceChart !== 'pie' }"
                                    class="px-3 py-1.5 rounded-lg text-2xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">pie_chart</span>
                                <span>Pie Chart (Aset)</span>
                            </button>
                            <button @click="activeBalanceChart = 'bar'" 
                                    :class="{ 'bg-white dark:bg-slate-900 text-primary shadow-2xs': activeBalanceChart === 'bar', 'text-slate-500 hover:text-slate-700 dark:hover:text-slate-300': activeBalanceChart !== 'bar' }"
                                    class="px-3 py-1.5 rounded-lg text-2xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">bar_chart</span>
                                <span >Bar Chart (Aktiva vs Pasiva)</span>
                            </button>
                        </div>
                    </div>

                    <div class="relative h-64 md:h-80 pt-4">
                        <!-- Option A: Pie / Doughnut Chart -->
                        <div x-show="activeBalanceChart === 'pie'" class="h-full w-full">
                            <canvas id="balanceAssetPieChart"></canvas>
                        </div>

                        <!-- Option B: Bar Chart -->
                        <div x-show="activeBalanceChart === 'bar'" class="h-full w-full" x-cloak>
                            <canvas id="balanceComparisonBarChart"></canvas>
                        </div>
                    </div>
                </x-card>

                <x-card title="Laporan Neraca Keuangan">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2">
                        <!-- Aktiva (Assets) -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-sky-600 pb-2 border-b border-sky-200 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">account_balance</span>
                                AKTIVA (ASSETS)
                            </h4>
                            <div class="space-y-2 text-xs">
                                @foreach($balanceSheet['assets'] as $ast)
                                    <div class="flex justify-between border-b border-slate-50 dark:border-slate-800/50 pb-1">
                                        <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ $ast['code'] }} - {{ $ast['name'] }}</span>
                                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($ast['amount'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-between text-sm font-black pt-3 border-t-2 border-slate-900 dark:border-slate-100">
                                <span>TOTAL AKTIVA</span>
                                <span class="text-sky-600 font-mono font-black">Rp {{ number_format($balanceSheet['total_assets'], 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Pasiva (Liabilities & Equities) -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-600 pb-2 border-b border-amber-200 flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm">real_estate_agent</span>
                                PASIVA (KEWAJIBAN & MODAL)
                            </h4>
                            <div class="space-y-2 text-xs">
                                <span class="block text-2xs font-bold text-slate-400 uppercase">Kewajiban (Liabilities)</span>
                                @foreach($balanceSheet['liabilities'] as $liab)
                                    <div class="flex justify-between border-b border-slate-50 dark:border-slate-800/50 pb-1">
                                        <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ $liab['code'] }} - {{ $liab['name'] }}</span>
                                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($liab['amount'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach

                                <span class="block text-2xs font-bold text-slate-400 uppercase pt-2">Ekuitas (Equities)</span>
                                @foreach($balanceSheet['equities'] as $eq)
                                    <div class="flex justify-between border-b border-slate-50 dark:border-slate-800/50 pb-1">
                                        <span class="text-slate-600 dark:text-slate-300 font-semibold">{{ $eq['code'] }} - {{ $eq['name'] }}</span>
                                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($eq['amount'], 0, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="flex justify-between text-sm font-black pt-3 border-t-2 border-slate-900 dark:border-slate-100">
                                <span>TOTAL PASIVA</span>
                                <span class="text-amber-600 font-mono font-black">Rp {{ number_format($balanceSheet['total_liabilities_equity'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const chartData = @js($balanceSheetChartData);
                    
                    // 1. Pie / Doughnut Chart
                    const ctxPie = document.getElementById('balanceAssetPieChart');
                    if (ctxPie) {
                        new Chart(ctxPie.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: chartData.pie_asset_labels,
                                datasets: [{
                                    data: chartData.pie_asset_values,
                                    backgroundColor: ['#0284c7', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#64748b'],
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { position: 'right', labels: { font: { family: 'Inter', size: 11, weight: 'bold' } } },
                                    tooltip: { callbacks: { label: c => ' ' + c.label + ': Rp ' + Number(c.raw).toLocaleString('id-ID') } }
                                }
                            }
                        });
                    }

                    // 2. Bar Chart Comparison
                    const ctxBar = document.getElementById('balanceComparisonBarChart');
                    if (ctxBar) {
                        new Chart(ctxBar.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: chartData.bar_comparison_labels,
                                datasets: [{
                                    label: 'Nilai (Rp)',
                                    data: chartData.bar_comparison_values,
                                    backgroundColor: ['#0284c7', '#d97706'],
                                    borderRadius: 8
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: { callbacks: { label: c => ' Rp ' + Number(c.raw).toLocaleString('id-ID') } }
                                },
                                scales: {
                                    y: {
                                        ticks: {
                                            callback: function(v) {
                                                if (v >= 1000000) return 'Rp ' + (v/1000000).toFixed(1) + 'M';
                                                if (v >= 1000) return 'Rp ' + (v/1000).toFixed(0) + 'k';
                                                return 'Rp ' + v;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
            </script>
        @endif

        <!-- Tab 3: Trial Balance -->
        @if($tab === 'trial')
            <div class="space-y-6">
                <!-- Visual Chart Card for Trial Balance -->
                <x-card title="Grafik Perbandingan Total Debit vs Kredit (Trial Balance)">
                    <div class="relative h-64 md:h-72">
                        <canvas id="trialChart"></canvas>
                    </div>
                </x-card>

                <x-card title="Laporan Neraca Saldo (Trial Balance)">
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse">
                            <thead>
                                <tr class="border-b-2 border-slate-200 dark:border-slate-700 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3 text-left">Kode Akun</th>
                                    <th class="py-2.5 px-3 text-left">Nama Akun</th>
                                    <th class="py-2.5 px-3 text-left">Kategori</th>
                                    <th class="py-2.5 px-3 text-right">Debit</th>
                                    <th class="py-2.5 px-3 text-right">Kredit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-mono">
                                @forelse($trialBalance['rows'] as $row)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                        <td class="py-2 px-3 font-bold text-slate-800 dark:text-slate-200">{{ $row['code'] }}</td>
                                        <td class="py-2 px-3 text-slate-700 dark:text-slate-350 font-sans">{{ $row['name'] }}</td>
                                        <td class="py-2 px-3 text-2xs uppercase text-slate-400 font-sans">{{ $row['type'] }}</td>
                                        <td class="py-2 px-3 text-right text-slate-700 dark:text-slate-300">
                                            {{ $row['debit'] > 0 ? 'Rp ' . number_format($row['debit'], 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="py-2 px-3 text-right text-slate-700 dark:text-slate-350">
                                            {{ $row['credit'] > 0 ? 'Rp ' . number_format($row['credit'], 0, ',', '.') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400 font-sans">Belum ada data transaksi jurnal terposting.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-slate-900 dark:border-slate-100 font-bold font-mono text-sm">
                                    <td colspan="3" class="py-3 px-3 font-sans font-black">TOTAL</td>
                                    <td class="py-3 px-3 text-right text-primary">Rp {{ number_format($trialBalance['total_debit'], 0, ',', '.') }}</td>
                                    <td class="py-3 px-3 text-right text-primary">Rp {{ number_format($trialBalance['total_credit'], 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </x-card>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const ctxTrial = document.getElementById('trialChart');
                    if (ctxTrial) {
                        new Chart(ctxTrial.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: ['Total Debit', 'Total Kredit'],
                                datasets: [{
                                    label: 'Saldo (Rp)',
                                    data: [
                                        @js($trialBalance['total_debit']),
                                        @js($trialBalance['total_credit'])
                                    ],
                                    backgroundColor: ['#FF6600', '#10b981'],
                                    borderRadius: 8
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' Rp ' + Number(c.raw).toLocaleString('id-ID') } } }
                            }
                        });
                    }
                });
            </script>
        @endif

    </div>
</x-app-layout>
