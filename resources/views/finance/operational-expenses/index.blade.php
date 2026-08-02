<x-app-layout>
    <div x-data="{ showCreateModal: false }" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Beban Operasional (Kas Kecil)" :breadcrumbs="['Keuangan' => '#', 'Beban Operasional' => '/finance/operational-expenses']" />
            <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                <span class="material-symbols-outlined">add_card</span>
                Catat Pengeluaran
            </button>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-4" />
        @endif

        {{-- Filter & Analytics Row --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Total Pengeluaran Card --}}
            <x-card class="!p-4 col-span-1">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-500 text-2xl">trending_down</span>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Pengeluaran</p>
                        <p class="text-lg font-black text-slate-800 dark:text-slate-200">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
                    </div>
                </div>
            </x-card>

            {{-- Date Filter --}}
            <x-card class="!p-4 col-span-3">
                <form method="GET" action="{{ route('finance.operational-expenses.index') }}" class="flex flex-wrap items-end gap-4">
                    @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Cabang</label>
                            <select name="branch_id" class="h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none min-w-[160px]">
                                <option value="">Semua Cabang</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                    </div>
                    <button type="submit" class="h-10 px-5 bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-600 text-white font-bold rounded-lg text-xs flex items-center gap-2 transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-sm">filter_alt</span> Filter
                    </button>
                </form>
            </x-card>
        </div>

        {{-- Breakdown per Kategori --}}
        @if($expensesByCategory->isNotEmpty())
            <x-card title="Breakdown Pengeluaran per Kategori Akun">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($expensesByCategory as $cat)
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-850/50 border border-slate-100 dark:border-slate-800">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">{{ $cat['account']?->code ?? '-' }}</p>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300 truncate" title="{{ $cat['account']?->name }}">{{ $cat['account']?->name ?? 'N/A' }}</p>
                            <p class="text-sm font-black text-red-500 mt-1">Rp {{ number_format($cat['total'], 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
            </x-card>
        @endif

        {{-- Table --}}
        <x-card title="Riwayat Pencatatan Beban Operasional">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Deskripsi</th>
                            <th class="py-3 px-4">Akun Beban</th>
                            <th class="py-3 px-4">Metode</th>
                            <th class="py-3 px-4 text-right">Jumlah (Rp)</th>
                            <th class="py-3 px-4">No. Bukti</th>
                            <th class="py-3 px-4">Cabang</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 text-slate-500 whitespace-nowrap">
                                    {{ $expense->expense_date?->format('d M Y') }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700 dark:text-slate-350 max-w-xs truncate" title="{{ $expense->description }}">
                                    {{ $expense->description }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="font-mono text-[10px] text-slate-500">[{{ $expense->account?->code }}]</span>
                                    <span class="text-slate-600 dark:text-slate-400">{{ $expense->account?->name }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    @if($expense->payment_method === 'cash')
                                        <x-badge type="success">Tunai</x-badge>
                                    @else
                                        <x-badge type="info">Transfer</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right font-mono font-bold text-red-500">
                                    {{ number_format($expense->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-slate-400 font-mono text-[10px]">
                                    {{ $expense->receipt_number ?? '-' }}
                                </td>
                                <td class="py-4 px-4 text-slate-500 text-[10px] font-bold">
                                    {{ $expense->branch?->name ?? '-' }}
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <form action="{{ route('finance.operational-expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Hapus pengeluaran ini dan reverse jurnal keuangan terkait?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all border border-red-500/20">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl mb-2 block opacity-30">receipt_long</span>
                                    Belum ada catatan beban operasional untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $expenses->appends(request()->query())->links() }}
            </div>
        </x-card>

        {{-- Create Modal --}}
        <div x-show="showCreateModal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">add_card</span>
                        Catat Pengeluaran Operasional
                    </h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('finance.operational-expenses.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="expense_date" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Pengeluaran</label>
                            <input type="date" id="expense_date" name="expense_date" required max="{{ date('Y-m-d') }}" value="{{ old('expense_date', date('Y-m-d')) }}"
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="payment_method" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Metode Bayar</label>
                            <select id="payment_method" name="payment_method" required
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="cash" selected>Tunai (Kas Kecil)</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="account_id" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kategori Beban (Akun COA)</label>
                        <select id="account_id" name="account_id" required
                                class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                            <option value="">-- Pilih Akun Beban --</option>
                            @foreach($expenseAccounts as $acc)
                                <option value="{{ $acc->id }}">[{{ $acc->code }}] {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="amount" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jumlah (Rp)</label>
                        <input type="number" id="amount" name="amount" required min="1" placeholder="Contoh: 500000"
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="description" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Deskripsi / Keterangan</label>
                        <input type="text" id="description" name="description" required maxlength="255" placeholder="Contoh: Pembayaran tagihan listrik bulan Juli 2026"
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="receipt_number" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">No. Bukti / Kwitansi (Opsional)</label>
                        <input type="text" id="receipt_number" name="receipt_number" maxlength="100" placeholder="Contoh: KWT-2026-0102"
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    {{-- Sync Info Banner --}}
                    <div class="p-3 rounded-xl bg-blue-500/10 border border-blue-500/20 text-blue-600 dark:text-blue-400 text-[10px] font-bold flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">sync</span>
                        Jurnal keuangan (Dr. Beban Operasional | Cr. Kas/Bank) akan otomatis terposting ke Buku Besar.
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl cursor-pointer transition-all">
                            Simpan & Posting Jurnal
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
