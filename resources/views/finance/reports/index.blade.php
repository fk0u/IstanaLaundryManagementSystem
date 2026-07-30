<x-app-layout>
    <div x-data="{ activeTab: 'trial_balance' }" class="flex flex-col gap-6">
        <x-page-header title="Laporan Keuangan & Akuntansi" :breadcrumbs="['Keuangan' => '#', 'Laporan' => '/finance/reports']" />

        <!-- Filter Form Card -->
        <x-card title="Filter Laporan">
            <form action="{{ route('finance.reports.index') }}" method="GET" class="grid grid-cols-4 gap-4 items-end">
                <div class="flex flex-col gap-1.5">
                    <label for="year" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tahun</label>
                    <select id="year" name="year" class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="month" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bulan</label>
                    <select id="month" name="month" class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                {{ Carbon\Carbon::create(null, $m, 1)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                @if(auth()->user()->hasRole(['Developer', 'Owner', 'Super Admin']))
                    <div class="flex flex-col gap-1.5">
                        <label for="branch_id" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Cabang (Consolidated)</label>
                        <select id="branch_id" name="branch_id" class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                            <option value="">-- Semua Cabang (Konsolidasi) --</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="branch_id" value="{{ $branchId }}">
                @endif

                <button type="submit" class="h-11 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs cursor-pointer transition-all active:scale-[0.98]">
                    Terapkan Filter
                </button>
            </form>

            <div class="flex flex-wrap items-center justify-between border-t border-slate-100 dark:border-slate-800 pt-3.5 mt-4 gap-2">
                <span class="text-xs font-bold text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-base text-primary">verified</span>
                    Format Ekspor Resmi Istana Laundry ERP:
                </span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('finance.reports.pdf', request()->all()) }}" target="_blank"
                       class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-2xs transition-all flex items-center gap-1 shadow-sm cursor-pointer">
                        <span class="material-symbols-outlined text-sm">picture_as_pdf</span> Unduh PDF
                    </a>
                    <a href="{{ route('finance.reports.excel', request()->all()) }}"
                       class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-2xs transition-all flex items-center gap-1 shadow-sm cursor-pointer">
                        <span class="material-symbols-outlined text-sm">table_chart</span> Ekspor Excel (.xlsx)
                    </a>
                    <a href="{{ route('finance.reports.export', array_merge(request()->all(), ['tab' => 'analytics'])) }}"
                       class="px-3.5 py-2 bg-slate-700 hover:bg-slate-800 text-white rounded-xl font-bold text-2xs transition-all flex items-center gap-1 shadow-sm cursor-pointer">
                        <span class="material-symbols-outlined text-sm">download</span> Ekspor CSV
                    </a>
                </div>
            </div>
        </x-card>

        <!-- Tab Controls -->
        <div class="flex gap-2 border-b border-slate-100 dark:border-slate-800 pb-2">
            <button @click="activeTab = 'trial_balance'"
                    :class="activeTab === 'trial_balance' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-400 hover:text-slate-650'"
                    class="py-2.5 px-4 border-b-2 text-xs transition-all cursor-pointer">
                Neraca Percobaan (Trial Balance)
            </button>
            <button @click="activeTab = 'income_statement'"
                    :class="activeTab === 'income_statement' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-400 hover:text-slate-650'"
                    class="py-2.5 px-4 border-b-2 text-xs transition-all cursor-pointer">
                Laporan Laba Rugi
            </button>
            <button @click="activeTab = 'balance_sheet'"
                    :class="activeTab === 'balance_sheet' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-400 hover:text-slate-650'"
                    class="py-2.5 px-4 border-b-2 text-xs transition-all cursor-pointer">
                Neraca Keuangan (Balance Sheet)
            </button>
        </div>

        <!-- Tab Panels -->
        <!-- 1. Trial Balance -->
        <div x-show="activeTab === 'trial_balance'">
            <x-card title="Neraca Percobaan">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3 px-4">Kode Akun</th>
                                <th class="py-3 px-4">Nama Akun</th>
                                <th class="py-3 px-4 text-right">Debit</th>
                                <th class="py-3 px-4 text-right">Kredit</th>
                                <th class="py-3 px-4 text-right">Saldo Akhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                            @forelse($trialBalance['lines'] as $line)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                    <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                        {{ $line['account']->code }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-700 dark:text-slate-300">
                                        {{ $line['account']->name }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-green-500 font-mono">
                                        {{ $line['debit'] > 0 ? 'Rp ' . number_format($line['debit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-slate-500 font-mono">
                                        {{ $line['credit'] > 0 ? 'Rp ' . number_format($line['credit'], 0, ',', '.') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-primary font-mono">
                                        Rp {{ number_format($line['balance'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400">Tidak ada data transaksi untuk filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-slate-200 dark:border-slate-800 font-bold bg-slate-50/50 dark:bg-slate-900/20">
                                <td colspan="2" class="py-4 px-4 text-slate-750 dark:text-slate-300 uppercase">Total Neraca Percobaan</td>
                                <td class="py-4 px-4 text-right text-green-500 font-mono">Rp {{ number_format($trialBalance['total_debit'], 0, ',', '.') }}</td>
                                <td class="py-4 px-4 text-right text-slate-500 font-mono">Rp {{ number_format($trialBalance['total_credit'], 0, ',', '.') }}</td>
                                <td class="py-4 px-4 text-right font-extrabold text-primary font-mono">
                                    Status: {{ $trialBalance['is_balanced'] ? 'SEIMBANG' : 'TIDAK SEIMBANG' }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- 2. Income Statement -->
        <div x-show="activeTab === 'income_statement'">
            <x-card title="Laporan Laba Rugi Periode">
                <div class="space-y-6">
                    <!-- Revenues -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pendapatan (Revenues)</h4>
                        <table class="w-full text-left text-xs border-collapse">
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                                @forelse($incomeStatement['revenues'] as $rev)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                        <td class="py-3 px-4 font-mono text-slate-500">{{ $rev['account']->code }}</td>
                                        <td class="py-3 px-4 text-slate-750 dark:text-slate-350">{{ $rev['account']->name }}</td>
                                        <td class="py-3 px-4 text-right font-mono">Rp {{ number_format($rev['balance'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-4 px-4 text-slate-400 text-center">Belum ada pendapatan terdata.</td>
                                    </tr>
                                @endforelse
                                <tr class="font-bold bg-slate-50/30 dark:bg-slate-900/10">
                                    <td colspan="2" class="py-3.5 px-4 text-slate-800 dark:text-slate-200">Total Pendapatan</td>
                                    <td class="py-3.5 px-4 text-right text-green-500 font-mono">Rp {{ number_format($incomeStatement['total_revenue'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Expenses -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Beban Operasional (Expenses)</h4>
                        <table class="w-full text-left text-xs border-collapse">
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                                @forelse($incomeStatement['expenses'] as $exp)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                        <td class="py-3 px-4 font-mono text-slate-500">{{ $exp['account']->code }}</td>
                                        <td class="py-3 px-4 text-slate-750 dark:text-slate-350">{{ $exp['account']->name }}</td>
                                        <td class="py-3 px-4 text-right font-mono">Rp {{ number_format($exp['balance'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-4 px-4 text-slate-400 text-center">Belum ada beban operasional terdata.</td>
                                    </tr>
                                @endforelse
                                <tr class="font-bold bg-slate-50/30 dark:bg-slate-900/10">
                                    <td colspan="2" class="py-3.5 px-4 text-slate-800 dark:text-slate-200">Total Beban Operasional</td>
                                    <td class="py-3.5 px-4 text-right text-red-500 font-mono">Rp {{ number_format($incomeStatement['total_expense'], 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Net Income -->
                    <div class="p-4 bg-primary/10 border border-primary/20 rounded-xl flex justify-between items-center font-bold text-sm">
                        <span class="text-slate-800 dark:text-slate-200">LABA/RUGI BERSIH (NET INCOME)</span>
                        <span class="text-primary font-mono text-base">Rp {{ number_format($incomeStatement['net_income'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- 3. Balance Sheet -->
        <div x-show="activeTab === 'balance_sheet'">
            <x-card title="Neraca Keuangan">
                <div class="grid grid-cols-2 gap-6">
                    <!-- Left Side: Assets -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100 dark:border-slate-800">Aset (Assets)</h4>
                        <table class="w-full text-xs text-left">
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                                @forelse($balanceSheet['assets'] as $asset)
                                    <tr>
                                        <td class="py-2.5 font-mono text-slate-500">{{ $asset['account']->code }}</td>
                                        <td class="py-2.5 text-slate-750 dark:text-slate-350">{{ $asset['account']->name }}</td>
                                        <td class="py-2.5 text-right font-mono">Rp {{ number_format($asset['balance'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-2.5 text-slate-400 text-center">Belum ada aset.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="font-bold border-t border-slate-200 dark:border-slate-800">
                                    <td colspan="2" class="py-4 text-slate-850 dark:text-slate-250 uppercase">Total Aset</td>
                                    <td class="py-4 text-right text-green-500 font-mono text-sm">Rp {{ number_format($balanceSheet['total_asset'], 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Right Side: Liabilities & Equity -->
                    <div class="space-y-6">
                        <!-- Liabilities -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100 dark:border-slate-800">Liabilitas (Liabilities)</h4>
                            <table class="w-full text-xs text-left">
                                <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                                    @forelse($balanceSheet['liabilities'] as $liability)
                                        <tr>
                                            <td class="py-2.5 font-mono text-slate-500">{{ $liability['account']->code }}</td>
                                            <td class="py-2.5 text-slate-750 dark:text-slate-350">{{ $liability['account']->name }}</td>
                                            <td class="py-2.5 text-right font-mono">Rp {{ number_format($liability['balance'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-2.5 text-slate-400 text-center">Belum ada liabilitas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="font-bold border-t border-slate-200 dark:border-slate-800">
                                        <td colspan="2" class="py-4 text-slate-850 dark:text-slate-250 uppercase">Total Liabilitas</td>
                                        <td class="py-4 text-right text-slate-550 font-mono text-sm">Rp {{ number_format($balanceSheet['total_liability'], 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Equity -->
                        <div class="space-y-4">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider pb-2 border-b border-slate-100 dark:border-slate-800">Ekuitas (Equity)</h4>
                            <table class="w-full text-xs text-left">
                                <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                                    @forelse($balanceSheet['equity'] as $eq)
                                        <tr>
                                            <td class="py-2.5 font-mono text-slate-500">{{ $eq['account']->code }}</td>
                                            <td class="py-2.5 text-slate-750 dark:text-slate-350">{{ $eq['account']->name }}</td>
                                            <td class="py-2.5 text-right font-mono">Rp {{ number_format($eq['balance'], 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-2.5 text-slate-400 text-center">Belum ada ekuitas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="font-bold border-t border-slate-200 dark:border-slate-800">
                                        <td colspan="2" class="py-4 text-slate-850 dark:text-slate-250 uppercase">Total Ekuitas</td>
                                        <td class="py-4 text-right text-slate-550 font-mono text-sm">Rp {{ number_format($balanceSheet['total_equity'], 0, ',', '.') }}</td>
                                    </tr>
                                    <tr class="font-bold border-t-2 border-slate-250 dark:border-slate-750 bg-slate-50/50 dark:bg-slate-900/10">
                                        <td colspan="2" class="py-4 text-slate-850 dark:text-slate-250 uppercase">Total Liabilitas & Ekuitas</td>
                                        <td class="py-4 text-right text-primary font-mono text-sm">Rp {{ number_format($balanceSheet['total_liability'] + $balanceSheet['total_equity'], 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>

    </div>
</x-app-layout>
