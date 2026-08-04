<x-app-layout>
    <div x-data="periodModalApp()" class="flex flex-col gap-6">
        <x-page-header title="Periode Akuntansi (Accounting Periods)" :breadcrumbs="['Keuangan' => '#', 'Periode' => '/finance/periods']" />

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-4" />
        @endif

        <x-card title="Daftar Periode Pembukuan Bulanan Cabang">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Tahun / Bulan</th>
                            <th class="py-3 px-4">Cabang</th>
                            <th class="py-3 px-4">Tanggal Penutupan</th>
                            <th class="py-3 px-4">Ditutup Oleh</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($periods as $period)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $period->year }} / {{ str_pad($period->month, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700 dark:text-slate-350">
                                    {{ $period->branch?->name ?? 'Global (Semua Cabang)' }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ $period->closed_at ? $period->closed_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $period->closedBy?->name ?? '-' }}
                                </td>
                                <td class="py-4 px-4">
                                    @if ($period->status === 'closed')
                                        <x-badge type="danger">Closed (Terkunci)</x-badge>
                                    @else
                                        <x-badge type="success">Open (Aktif)</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Detail Preview Button -->
                                        <button type="button" @click="openPreview({{ $period->id }})"
                                                class="btn-touch px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-2xs font-extrabold flex items-center gap-1.5 cursor-pointer transition-all haptic-press">
                                            <span class="material-symbols-outlined text-sm text-primary">visibility</span>
                                            Lihat Detail
                                        </button>

                                        @if ($period->status === 'open')
                                            <form action="{{ route('finance.periods.close', $period->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Menutup periode akuntansi akan mengunci semua transaksi dan mencegah posting jurnal baru pada bulan ini. Apakah Anda yakin?')">
                                                @csrf
                                                <button type="submit" class="btn-touch px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-2xs font-extrabold cursor-pointer transition-all flex items-center gap-1 shadow-xs haptic-press">
                                                    <span class="material-symbols-outlined text-sm">lock</span>
                                                    Tutup Periode
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('finance.periods.reopen', $period->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuka kembali (re-open) periode akuntansi ini? Transaksi dan posting jurnal akan dapat dilakukan kembali.')">
                                                @csrf
                                                <button type="submit" class="btn-touch px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-2xs font-extrabold cursor-pointer transition-all flex items-center gap-1 shadow-xs haptic-press">
                                                    <span class="material-symbols-outlined text-sm">lock_open</span>
                                                    Buka Periode
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">Belum ada periode akuntansi terbuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $periods->links() }}
            </div>
        </x-card>

        <!-- Preview Modal -->
        <div x-show="showModal"
             class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/70 backdrop-blur-md p-4 sm:p-6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-cloak>
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl w-full max-w-4xl p-6 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden"
                 @click.away="showModal = false">
                
                <!-- Modal Header -->
                <div class="flex justify-between items-start pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary dark:bg-primary/20 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-2xl">calendar_month</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-slate-900 dark:text-white" x-text="periodData?.period?.month_name || 'Loading...'"></h3>
                                <template x-if="periodData?.period">
                                    <span class="px-2.5 py-0.5 rounded-full text-2xs font-extrabold uppercase tracking-wider"
                                          :class="periodData.period.status === 'closed' ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/50' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/50'"
                                          x-text="periodData.period.status === 'closed' ? 'Closed (Terkunci)' : 'Open (Aktif)'">
                                    </span>
                                </template>
                            </div>
                            <p class="text-2xs text-slate-400 font-semibold mt-0.5" x-text="'Cabang: ' + (periodData?.period?.branch_name || '-') + (periodData?.period?.closed_at ? ' • Ditutup pada ' + periodData.period.closed_at + ' oleh ' + periodData.period.closed_by_name : '')"></p>
                        </div>
                    </div>

                    <button type="button" @click="showModal = false" class="btn-touch p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <!-- Modal Body with Scrollable Area -->
                <div class="flex-1 overflow-y-auto py-5 space-y-6 pr-1">
                    
                    <!-- Loading Indicator -->
                    <template x-if="loading">
                        <div class="py-16 text-center flex flex-col items-center justify-center gap-3">
                            <span class="material-symbols-outlined text-primary text-4xl animate-spin">progress_activity</span>
                            <span class="text-xs font-bold text-slate-400">Memuat detail periode...</span>
                        </div>
                    </template>

                    <template x-if="!loading && periodData">
                        <div class="space-y-6">
                            
                            <!-- 4 Summary Metrics -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
                                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Total Jurnal</span>
                                    <span class="text-lg font-black text-slate-800 dark:text-slate-100" x-text="periodData.summary.total_journals">0</span>
                                    <div class="text-[10px] text-slate-400 font-medium mt-0.5">
                                        <span class="text-emerald-600 font-bold" x-text="periodData.summary.posted_journals + ' Posted'"></span> • 
                                        <span class="text-amber-600 font-bold" x-text="periodData.summary.draft_journals + ' Draft'"></span>
                                    </div>
                                </div>

                                <div class="bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
                                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Total Debit</span>
                                    <span class="text-sm sm:text-base font-black text-primary" x-text="'Rp ' + formatNumber(periodData.summary.total_debit)">Rp 0</span>
                                </div>

                                <div class="bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
                                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Total Kredit</span>
                                    <span class="text-sm sm:text-base font-black text-primary" x-text="'Rp ' + formatNumber(periodData.summary.total_credit)">Rp 0</span>
                                </div>

                                <div class="p-3.5 rounded-2xl border"
                                     :class="periodData.summary.is_balanced ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200/60 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50/60 dark:bg-rose-950/20 border-rose-200/60 dark:border-rose-900/40 text-rose-700 dark:text-rose-400'">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider block mb-1">Status Keseimbangan</span>
                                    <div class="flex items-center gap-1 font-black text-xs sm:text-sm">
                                        <span class="material-symbols-outlined text-base" x-text="periodData.summary.is_balanced ? 'check_circle' : 'warning'"></span>
                                        <span x-text="periodData.summary.is_balanced ? 'Balanced (Seimbang)' : 'Tidak Seimbang'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Draft Warning Alert if Drafts exist and status is Open -->
                            <template x-if="periodData.period.status === 'open' && periodData.summary.draft_journals > 0">
                                <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 flex items-center gap-3 text-xs text-amber-800 dark:text-amber-300">
                                    <span class="material-symbols-outlined text-amber-500 text-xl shrink-0">warning</span>
                                    <div>
                                        <span class="font-extrabold">Terdapat <span x-text="periodData.summary.draft_journals"></span> jurnal berstatus DRAFT.</span>
                                        <span class="block text-2xs text-amber-700 dark:text-amber-400 mt-0.5">Semua jurnal draft harus diposting atau dihapus terlebih dahulu sebelum periode ini dapat ditutup.</span>
                                    </div>
                                </div>
                            </template>

                            <!-- Journals Table List -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Jurnal Transaksi Terbaru dalam Periode Ini</h4>
                                    <span class="text-2xs text-slate-400 font-semibold" x-text="'Menampilkan ' + periodData.journals.length + ' dari ' + periodData.summary.total_journals + ' jurnal'"></span>
                                </div>

                                <div class="border border-slate-200/80 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
                                    <div class="max-h-72 overflow-y-auto">
                                        <table class="w-full text-left text-xs">
                                            <thead class="bg-slate-50 dark:bg-slate-800/60 sticky top-0 z-10 border-b border-slate-100 dark:border-slate-800 text-2xs font-extrabold text-slate-400 uppercase tracking-wider">
                                                <tr>
                                                    <th class="py-2.5 px-3">No. Referensi</th>
                                                    <th class="py-2.5 px-3">Tanggal</th>
                                                    <th class="py-2.5 px-3">Keterangan</th>
                                                    <th class="py-2.5 px-3 text-right">Nominal</th>
                                                    <th class="py-2.5 px-3 text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 bg-white dark:bg-slate-900">
                                                <template x-if="periodData.journals.length === 0">
                                                    <tr>
                                                        <td colspan="5" class="py-8 text-center text-slate-400 text-xs">Belum ada jurnal transaksi dalam periode ini.</td>
                                                    </tr>
                                                </template>
                                                <template x-for="j in periodData.journals" :key="j.id">
                                                    <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                                                        <td class="py-2.5 px-3 font-bold text-slate-800 dark:text-slate-200 font-mono text-2xs" x-text="j.reference"></td>
                                                        <td class="py-2.5 px-3 text-slate-500 text-2xs" x-text="j.date"></td>
                                                        <td class="py-2.5 px-3 text-slate-700 dark:text-slate-300 max-w-xs truncate" x-text="j.description"></td>
                                                        <td class="py-2.5 px-3 text-right font-bold text-slate-900 dark:text-slate-100 font-mono text-2xs" x-text="'Rp ' + formatNumber(j.debit)"></td>
                                                        <td class="py-2.5 px-3 text-center">
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase"
                                                                  :class="{
                                                                      'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400': j.status === 'posted',
                                                                      'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400': j.status === 'draft',
                                                                      'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400': j.status === 'reversed'
                                                                  }"
                                                                  x-text="j.status">
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </template>

                </div>

                <!-- Modal Footer Actions -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                    <button type="button" @click="showModal = false"
                            class="btn-touch px-4 h-10 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl transition-all cursor-pointer">
                        Tutup
                    </button>

                    <template x-if="periodData && periodData.period.status === 'open'">
                        <form :action="'/finance/periods/' + periodData.period.id + '/close'" method="POST" onsubmit="return confirm('PERINGATAN: Menutup periode akuntansi akan mengunci semua transaksi dan mencegah posting jurnal baru pada bulan ini. Apakah Anda yakin?')">
                            @csrf
                            <button type="submit" :disabled="periodData.summary.draft_journals > 0"
                                    class="btn-touch px-5 h-10 bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-xs rounded-xl shadow-md shadow-rose-500/20 flex items-center gap-1.5 transition-all cursor-pointer">
                                <span class="material-symbols-outlined text-base">lock</span>
                                Tutup Periode Akuntansi Ini
                            </button>
                        </form>
                    </template>
                    <template x-if="periodData && periodData.period.status === 'closed'">
                        <form :action="'/finance/periods/' + periodData.period.id + '/reopen'" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membuka kembali (re-open) periode akuntansi ini?')">
                            @csrf
                            <button type="submit"
                                    class="btn-touch px-5 h-10 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-500/20 flex items-center gap-1.5 transition-all cursor-pointer">
                                <span class="material-symbols-outlined text-base">lock_open</span>
                                Buka Kembali Periode (Re-Open)
                            </button>
                        </form>
                    </template>
                </div>

            </div>
        </div>

    </div>

    <script>
        function periodModalApp() {
            return {
                showModal: false,
                loading: false,
                periodData: null,

                openPreview(periodId) {
                    this.showModal = true;
                    this.loading = true;
                    this.periodData = null;

                    fetch(`/finance/periods/${periodId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.periodData = data;
                        this.loading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.loading = false;
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal memuat detail periode.', type: 'error' } }));
                    });
                },

                formatNumber(val) {
                    let num = parseFloat(val) || 0;
                    return num.toLocaleString('id-ID');
                }
            };
        }
    </script>
</x-app-layout>
