<x-app-layout>
    <div x-data="{ showCreateModal: false }" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Manajemen Promosi & Kupon" :breadcrumbs="['Promosi' => '/promotions']" />
            <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                <span class="material-symbols-outlined">add</span>
                Buat Kupon Promo
            </button>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif

        <!-- Config Section: Pengaturan Poin Member -->
        <x-card title="Pengaturan Loyalty Poin Member">
            <form action="{{ route('promotions.settings.update') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Config 1: 1 Point = Rp X -->
                    <div class="bg-orange-50/60 dark:bg-slate-800/50 p-4 rounded-2xl border border-orange-100 dark:border-slate-700 flex flex-col gap-2">
                        <div class="flex items-center gap-2 text-primary font-bold text-xs">
                            <span class="material-symbols-outlined text-base">monetization_on</span>
                            <span>Nilai Tukar 1 Poin (Rp)</span>
                        </div>
                        <p class="text-2xs text-slate-500">Berapa rupiah diskon yang didapat pelanggan per 1 poin?</p>
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="point_exchange_rate" value="{{ old('point_exchange_rate', $pointExchangeRate ?? 1) }}" min="0.01" step="0.01" required
                                   class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-black text-primary focus:border-primary outline-none">
                        </div>
                        <span class="text-[10px] text-slate-400 font-semibold">Contoh: Rp 1 (1 Poin = Rp 1) atau Rp 10</span>
                    </div>

                    <!-- Config 2: Perolehan Poin (Belanja Rp Y = 1 Poin) -->
                    <div class="bg-orange-50/60 dark:bg-slate-800/50 p-4 rounded-2xl border border-orange-100 dark:border-slate-700 flex flex-col gap-2">
                        <div class="flex items-center gap-2 text-primary font-bold text-xs">
                            <span class="material-symbols-outlined text-base">stars</span>
                            <span>Syarat Belanja per 1 Poin (Rp)</span>
                        </div>
                        <p class="text-2xs text-slate-500">Nominal belanja minimum untuk mendapatkan 1 poin dasar.</p>
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="point_earn_spend_threshold" value="{{ old('point_earn_spend_threshold', $pointEarnSpendThreshold ?? 1000) }}" min="1" step="1" required
                                   class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-black text-primary focus:border-primary outline-none">
                        </div>
                        <span class="text-[10px] text-slate-400 font-semibold">Default: Rp 1.000 (Tiap Rp 1rb = 1 Pts)</span>
                    </div>

                    <!-- Config 3: Minimal Penukaran Poin -->
                    <div class="bg-orange-50/60 dark:bg-slate-800/50 p-4 rounded-2xl border border-orange-100 dark:border-slate-700 flex flex-col gap-2">
                        <div class="flex items-center gap-2 text-primary font-bold text-xs">
                            <span class="material-symbols-outlined text-base">lock_open</span>
                            <span>Minimal Poin Penukaran</span>
                        </div>
                        <p class="text-2xs text-slate-500">Batas poin terkumpul sebelum bisa ditukar di POS.</p>
                        <div class="relative mt-1">
                            <input type="number" name="point_min_redeem" value="{{ old('point_min_redeem', $pointMinRedeem ?? 0) }}" min="0" step="1" required
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-black text-primary focus:border-primary outline-none">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Poin</span>
                        </div>
                        <span class="text-[10px] text-slate-400 font-semibold">Default: 0 Poin (Bisa tukar berapa saja)</span>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="btn-touch h-10 px-5 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-md shadow-primary/20 transition-all cursor-pointer">
                        <span class="material-symbols-outlined text-base">save</span>
                        Simpan Pengaturan Loyalty Poin
                    </button>
                </div>
            </form>
        </x-card>

        <x-card title="Kupon Promo Aktif">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Promo</th>
                            <th class="py-3 px-4">Kode Kupon</th>
                            <th class="py-3 px-4">Tipe Diskon</th>
                            <th class="py-3 px-4">Nilai</th>
                            <th class="py-3 px-4">Min. Transaksi</th>
                            <th class="py-3 px-4">Masa Berlaku</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($promotions as $promo)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $promo->name }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 bg-orange-50 dark:bg-slate-800 text-primary dark:text-orange-400 rounded-lg font-mono font-bold text-xs">
                                        {{ $promo->code ?? 'AUTOMATIC' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 uppercase font-semibold text-slate-600 dark:text-slate-400">
                                    {{ $promo->type }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $promo->type === 'percent' ? $promo->value . '%' : 'Rp ' . number_format($promo->value, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400 font-semibold">
                                    Rp {{ number_format($promo->min_transaction, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ \Carbon\Carbon::parse($promo->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($promo->end_date)->format('d M Y') }}
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('promotions.destroy', $promo->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kupon promosi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-red-500 transition-colors cursor-pointer" title="Hapus">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada promosi yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $promotions->links() }}
            </div>
        </x-card>

        <!-- Custom Create Promo Modal -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Buat Kupon Promo Baru</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form action="{{ route('promotions.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="flex flex-col gap-1.5">
                        <label for="name" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Promosi</label>
                        <input type="text" id="name" name="name" required placeholder="Contoh: Diskon Kemerdekaan..."
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="code" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kode Kupon (Harus Unik)</label>
                        <input type="text" id="code" name="code" required placeholder="Contoh: MERDEKA80..."
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="type" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tipe Potongan</label>
                            <select id="type" name="type" required
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="percent">Persentase (%)</option>
                                <option value="nominal">Nominal Tunai (Rp)</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="value" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nilai Potongan</label>
                            <input type="number" id="value" name="value" required min="0" placeholder="Contoh: 10 atau 15000..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="min_transaction" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Minimal Transaksi (Rp)</label>
                        <input type="number" id="min_transaction" name="min_transaction" required min="0" placeholder="Contoh: 50000..."
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="start_date" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Mulai</label>
                            <input type="date" id="start_date" name="start_date" required
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="end_date" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Berakhir</label>
                            <input type="date" id="end_date" name="end_date" required
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl cursor-pointer transition-all">
                            Buat Kupon
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
