<x-app-layout>
    <div x-data="{ 
        showCreateModal: false, 
        lines: [{ account_id: '', debit: 0, credit: 0, description: '' }, { account_id: '', debit: 0, credit: 0, description: '' }],
        get totalDebit() {
            return this.lines.reduce((sum, line) => sum + (parseFloat(line.debit) || 0), 0);
        },
        get totalCredit() {
            return this.lines.reduce((sum, line) => sum + (parseFloat(line.credit) || 0), 0);
        },
        get hasInvalidLine() {
            return this.lines.some(line => (parseFloat(line.debit) || 0) > 0 && (parseFloat(line.credit) || 0) > 0);
        },
        get isBalanced() {
            return !this.hasInvalidLine && Math.abs(this.totalDebit - this.totalCredit) < 0.01 && this.totalDebit > 0;
        }
    }" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Jurnal Transaksi (Ledger)" :breadcrumbs="['Keuangan' => '#', 'Jurnal' => '/finance/journals']" />
            <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                <span class="material-symbols-outlined">edit_note</span>
                Buat Jurnal Manual
            </button>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-4" />
        @endif

        <x-card title="Buku Jurnal Umum (Double-Entry Ledger)">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Referensi Jurnal</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Deskripsi</th>
                            <th class="py-3 px-4">Detail Postings (Debit / Kredit)</th>
                            <th class="py-3 px-4">Jenis</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($journals as $journal)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $journal->reference }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ $journal->date?->format('d M Y') }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700 dark:text-slate-350 max-w-xs truncate" title="{{ $journal->description }}">
                                    {{ $journal->description }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="space-y-1.5 font-mono text-[10px]">
                                        @foreach($journal->journalLines as $line)
                                            <div class="flex justify-between gap-4 border-b border-slate-50 dark:border-slate-850 pb-1">
                                                <span class="text-slate-600 dark:text-slate-400">
                                                    [{{ $line->account?->code }}] {{ $line->account?->name }}
                                                </span>
                                                <span class="font-bold">
                                                    @if ($line->debit > 0)
                                                        <span class="text-green-500">Dr Rp {{ number_format($line->debit, 0, ',', '.') }}</span>
                                                    @else
                                                        <span class="text-slate-500">Cr Rp {{ number_format($line->credit, 0, ',', '.') }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-4 px-4 uppercase font-bold text-[10px] text-slate-500">
                                    {{ $journal->type }}
                                </td>
                                <td class="py-4 px-4">
                                    @if ($journal->status === 'posted')
                                        <x-badge type="success">Posted</x-badge>
                                    @elseif ($journal->status === 'reversed')
                                        <x-badge type="gray">Reversed</x-badge>
                                    @else
                                        <x-badge type="warning">Draft</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    @if ($journal->status === 'posted')
                                        <form action="{{ route('finance.journals.reverse', $journal->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/me-reverse jurnal ini?')">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all border border-red-500/20">
                                                Reverse
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-700 material-symbols-outlined text-base cursor-not-allowed">lock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada entri jurnal dibukukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $journals->links() }}
            </div>
        </x-card>

        <!-- Custom Create Manual Journal Modal -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-3xl w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Buat Entri Jurnal Manual</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form action="{{ route('finance.journals.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-1 flex flex-col gap-1.5">
                            <label for="date" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Jurnal</label>
                            <input type="date" id="date" name="date" required max="{{ date('Y-m-d') }}"
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="col-span-2 flex flex-col gap-1.5">
                            <label for="description" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Deskripsi Transaksi / Memo</label>
                            <input type="text" id="description" name="description" required placeholder="Contoh: Koreksi kas kecil cabang untuk beban pemeliharaan..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <!-- Journal Lines -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Postings (Min. 2 Baris)</span>
                            <button type="button" @click="lines.push({ account_id: '', debit: 0, credit: 0, description: '' })" 
                                    class="text-xs font-bold text-primary hover:text-orange-600 flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">add_circle</span> Tambah Baris Posting
                            </button>
                        </div>
                        
                        <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                            <template x-for="(line, index) in lines" :key="index">
                                <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 dark:bg-slate-850/50 rounded-xl border border-slate-100 dark:border-slate-800/80 items-center">
                                    <div class="col-span-4 flex flex-col gap-1">
                                        <select :name="'lines['+index+'][account_id]'" required x-model="line.account_id"
                                                class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                            <option value="">-- Pilih Akun --</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}">[{{ $acc->code }}] {{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-3 flex flex-col gap-1">
                                        <input type="number" :name="'lines['+index+'][debit]'" required x-model.number="line.debit" min="0" placeholder="Debit (Rp)"
                                               class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                    <div class="col-span-3 flex flex-col gap-1">
                                        <input type="number" :name="'lines['+index+'][credit]'" required x-model.number="line.credit" min="0" placeholder="Kredit (Rp)"
                                               class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                    <div class="col-span-1 flex justify-center">
                                        <button type="button" @click="if(lines.length > 2) lines.splice(index, 1)" class="text-slate-400 hover:text-red-500 cursor-pointer">
                                            <span class="material-symbols-outlined text-lg">remove_circle_outline</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Balance Verification Banner -->
                    <div class="p-4 rounded-xl flex justify-between items-center text-xs font-bold border transition-colors duration-300"
                         :class="isBalanced ? 'bg-green-500/10 border-green-500/20 text-green-500' : 'bg-red-500/10 border-red-500/20 text-red-500'">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined" x-text="isBalanced ? 'check_circle' : 'error'"></span>
                            <span x-text="isBalanced ? 'Jurnal Seimbang (Ready to Post)' : 'Jurnal Belum Seimbang (Selisih Harus 0)'"></span>
                        </div>
                        <div class="font-mono">
                            Total Debit: Rp <span x-text="totalDebit.toLocaleString('id-ID')"></span> | Kredit: Rp <span x-text="totalCredit.toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit" :disabled="!isBalanced"
                                class="px-5 py-3 bg-primary hover:bg-primary-hover disabled:bg-slate-300 dark:disabled:bg-slate-800 disabled:text-slate-400 dark:disabled:text-slate-650 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl cursor-pointer transition-all">
                            Posting Jurnal
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
