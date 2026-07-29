<x-app-layout>
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

                <div class="flex items-center gap-2 mt-auto pb-0.5">
                    <button type="submit" class="h-9 px-4 bg-primary text-white text-xs font-bold rounded-xl flex items-center gap-1 shadow-sm">
                        <span class="material-symbols-outlined text-base">filter_alt</span> Filter
                    </button>
                    <a href="{{ route('finance.reports.excel', request()->all()) }}" class="h-9 px-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl flex items-center gap-1 shadow-sm">
                        <span class="material-symbols-outlined text-base">table_view</span> Ekspor Excel (.CSV)
                    </a>
                    <a href="{{ route('finance.reports.pdf', request()->all()) }}" target="_blank" class="h-9 px-3 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl flex items-center gap-1 shadow-sm">
                        <span class="material-symbols-outlined text-base">analytics</span> Cetak PowerBI PDF
                    </a>
                    <a href="{{ route('finance.closing-checklist') }}" class="h-9 px-3 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl flex items-center gap-1 shadow-sm">
                        <span class="material-symbols-outlined text-base">checklist</span> Closing Checklist
                    </a>
                </div>
            </form>
        </x-card>

        <!-- Report Tabs -->
        <div class="flex border-b border-slate-200 dark:border-slate-800 gap-4 text-xs font-bold">
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

        <!-- Tab 1: Income Statement -->
        @if($tab === 'income')
            <x-card title="Laporan Laba Rugi">
                <div class="space-y-6 max-w-2xl mx-auto py-2">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-600 mb-3">Pendapatan (Revenue)</h4>
                        <div class="space-y-2 text-xs">
                            @forelse($incomeStatement['revenues'] as $rev)
                                <div class="flex justify-between border-b border-slate-50 dark:border-slate-800/50 pb-1">
                                    <span class="text-slate-600 dark:text-slate-300">{{ $rev['code'] }} - {{ $rev['name'] }}</span>
                                    <span class="font-mono font-bold">Rp {{ number_format($rev['amount'], 0, ',', '.') }}</span>
                                </div>
                            @empty
                                <div class="text-slate-400 text-2xs py-2">Belum ada pendapatan terposting.</div>
                            @endforelse
                        </div>
                        <div class="flex justify-between text-sm font-bold pt-2 mt-2 border-t-2 border-slate-200 dark:border-slate-700">
                            <span>TOTAL PENDAPATAN</span>
                            <span class="text-emerald-600 font-mono">Rp {{ number_format($incomeStatement['total_revenue'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-rose-600 mb-3">Beban-Beban (Expenses)</h4>
                        <div class="space-y-2 text-xs">
                            @forelse($incomeStatement['expenses'] as $exp)
                                <div class="flex justify-between border-b border-slate-50 dark:border-slate-800/50 pb-1">
                                    <span class="text-slate-600 dark:text-slate-300">{{ $exp['code'] }} - {{ $exp['name'] }}</span>
                                    <span class="font-mono font-bold">Rp {{ number_format($exp['amount'], 0, ',', '.') }}</span>
                                </div>
                            @empty
                                <div class="text-slate-400 text-2xs py-2">Belum ada beban terposting.</div>
                            @endforelse
                        </div>
                        <div class="flex justify-between text-sm font-bold pt-2 mt-2 border-t-2 border-slate-200 dark:border-slate-700">
                            <span>TOTAL BEBAN</span>
                            <span class="text-rose-600 font-mono">Rp {{ number_format($incomeStatement['total_expense'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border {{ $incomeStatement['net_income'] >= 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-900 dark:bg-emerald-950/20 dark:text-emerald-400' : 'bg-rose-50 border-rose-200 text-rose-900 dark:bg-rose-950/20 dark:text-rose-400' }} flex justify-between items-center">
                        <span class="font-black text-sm">LABA (RUGI) BERSIH</span>
                        <span class="font-mono text-lg font-black">Rp {{ number_format($incomeStatement['net_income'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </x-card>
        @endif

        <!-- Tab 2: Balance Sheet -->
        @if($tab === 'balance')
            <x-card title="Laporan Neraca Keuangan">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-2">
                    <!-- Aktiva (Assets) -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-sky-600 pb-2 border-b border-sky-200">AKTIVA (ASSETS)</h4>
                        <div class="space-y-2 text-xs">
                            @foreach($balanceSheet['assets'] as $ast)
                                <div class="flex justify-between border-b border-slate-50 dark:border-slate-800/50 pb-1">
                                    <span class="text-slate-600 dark:text-slate-300">{{ $ast['code'] }} - {{ $ast['name'] }}</span>
                                    <span class="font-mono font-bold">Rp {{ number_format($ast['amount'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-between text-sm font-black pt-3 border-t-2 border-slate-900 dark:border-slate-100">
                            <span>TOTAL AKTIVA</span>
                            <span class="text-sky-600 font-mono">Rp {{ number_format($balanceSheet['total_assets'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Pasiva (Liabilities & Equities) -->
                    <div class="space-y-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-amber-600 pb-2 border-b border-amber-200">PASIVA (KEWAJIBAN & MODAL)</h4>
                        <div class="space-y-2 text-xs">
                            <span class="block text-2xs font-bold text-slate-400 uppercase">Kewajiban (Liabilities)</span>
                            @foreach($balanceSheet['liabilities'] as $liab)
                                <div class="flex justify-between border-b border-slate-50 dark:border-slate-800/50 pb-1">
                                    <span class="text-slate-600 dark:text-slate-300">{{ $liab['code'] }} - {{ $liab['name'] }}</span>
                                    <span class="font-mono font-bold">Rp {{ number_format($liab['amount'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach

                            <span class="block text-2xs font-bold text-slate-400 uppercase pt-2">Ekuitas (Equities)</span>
                            @foreach($balanceSheet['equities'] as $eq)
                                <div class="flex justify-between border-b border-slate-50 dark:border-slate-800/50 pb-1">
                                    <span class="text-slate-600 dark:text-slate-300">{{ $eq['code'] }} - {{ $eq['name'] }}</span>
                                    <span class="font-mono font-bold">Rp {{ number_format($eq['amount'], 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-between text-sm font-black pt-3 border-t-2 border-slate-900 dark:border-slate-100">
                            <span>TOTAL PASIVA</span>
                            <span class="text-amber-600 font-mono">Rp {{ number_format($balanceSheet['total_liabilities_equity'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </x-card>
        @endif

        <!-- Tab 3: Trial Balance -->
        @if($tab === 'trial')
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
                                    <td class="py-2 px-3 text-right text-slate-700 dark:text-slate-300">
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
        @endif

    </div>
</x-app-layout>
