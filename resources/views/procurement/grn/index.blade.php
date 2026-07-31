<x-app-layout>
    <div x-data="grnApp(@js($activePos))" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Goods Received Notes (GRN)" :breadcrumbs="['Pengadaan' => '#', 'GRN' => '/procurement/grns']" />
            <button @click="openModal()" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                <span class="material-symbols-outlined">receipt_long</span>
                Buat GRN Baru
            </button>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-4" />
        @endif

        <x-card title="Daftar Penerimaan Barang Gudang">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nomor GRN</th>
                            <th class="py-3 px-4">Nomor PO</th>
                            <th class="py-3 px-4">Tanggal Terima</th>
                            <th class="py-3 px-4">Diterima Oleh</th>
                            <th class="py-3 px-4">Catatan / Keterangan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($grns as $grn)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $grn->grn_number }}
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-650 dark:text-slate-400 font-semibold">
                                    <span class="px-2 py-0.5 rounded bg-orange-50 dark:bg-slate-800 text-primary dark:text-orange-400 font-bold border border-orange-200/50 dark:border-slate-700">
                                        {{ $grn->purchaseOrder?->po_number ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ $grn->received_date?->format('d M Y') }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $grn->receivedBy?->name }}
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $grn->notes ?? '-' }}
                                </td>
                                <td class="py-4 px-4">
                                    @if ($grn->status === 'confirmed')
                                        <x-badge type="success">Terkonfirmasi (Stok & Jurnal OK)</x-badge>
                                    @else
                                        <x-badge type="gray">Draft</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        @if ($grn->status === 'draft')
                                            <form action="{{ route('procurement.grns.confirm', $grn->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi penerimaan barang ini? Tindakan ini akan menambah stok dan memposting jurnal persediaan secara otomatis.')">
                                                @csrf
                                                <button type="submit" class="btn-touch px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-2xs font-extrabold cursor-pointer transition-all shadow-xs haptic-press">
                                                    Konfirmasi Penerimaan
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('procurement.grns.destroy', $grn->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus GRN ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer" title="Hapus GRN">
                                                    <span class="material-symbols-outlined text-base">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada penerimaan barang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $grns->links() }}
            </div>
        </x-card>

        <!-- Custom Create GRN Modal -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-4xl w-full p-6 shadow-2xl transition-all duration-300 max-h-[90vh] flex flex-col overflow-hidden">
                
                <div class="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-black text-slate-800 dark:text-slate-200">Buat Goods Received Note (GRN) Baru</h3>
                        <p class="text-2xs text-slate-400 mt-0.5">Catat barang masuk gudang berdasarkan Purchase Order terdaftar.</p>
                    </div>
                    <button @click="showCreateModal = false" class="btn-touch text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer p-1 rounded-xl">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                
                <form action="{{ route('procurement.grns.store') }}" method="POST" class="flex-1 flex flex-col overflow-hidden space-y-4 pt-4">
                    @csrf
                    
                    <div class="overflow-y-auto space-y-4 pr-1 flex-1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- PO Selector -->
                            <div class="flex flex-col gap-1.5">
                                <label for="po_id" class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                                    <span>Purchase Order Terkait <span class="text-rose-500">*</span></span>
                                    <span class="text-primary font-bold">{{ count($activePos) }} PO Tersedia</span>
                                </label>
                                <select id="po_id" name="po_id" x-model="selectedPo" required @change="onPoChange()"
                                        class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 focus:border-primary outline-none transition-all">
                                    <option value="">-- Pilih Purchase Order --</option>
                                    @foreach($activePos as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->po_number }} - {{ $p->supplier?->name ?? 'Supplier' }} ({{ $p->items->count() }} jenis barang)
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="received_date" class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Tanggal Penerimaan <span class="text-rose-500">*</span></label>
                                <input type="date" id="received_date" name="received_date" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                                       class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 focus:border-primary outline-none transition-all">
                            </div>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="notes" class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Catatan Penerimaan / Fisik Barang</label>
                            <textarea id="notes" name="notes" placeholder="Contoh: Barang diterima dalam kondisi baik & segel aman..." rows="2"
                                      class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary outline-none transition-all"></textarea>
                        </div>

                        <!-- Items Detail Table -->
                        <div class="space-y-3">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Verifikasi Kuantitas Barang Masuk Gudang</span>
                            
                            <!-- Table Column Headers -->
                            <div class="grid grid-cols-12 gap-3 px-3 py-2 bg-slate-100 dark:bg-slate-800/80 rounded-xl text-[10px] font-extrabold text-slate-600 dark:text-slate-300 uppercase tracking-wider items-center border border-slate-200/60 dark:border-slate-700/50">
                                <div class="col-span-5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs text-primary">inventory_2</span>
                                    <span>Daftar Produk / Barang</span>
                                </div>
                                <div class="col-span-4 text-center flex items-center justify-center gap-1">
                                    <span class="material-symbols-outlined text-xs text-primary">input</span>
                                    <span>Kuantitas Masuk</span>
                                </div>
                                <div class="col-span-3 flex items-center justify-start gap-1">
                                    <span class="material-symbols-outlined text-xs text-primary">payments</span>
                                    <span>Harga Satuan (PO)</span>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <template x-if="items.length === 0">
                                    <div class="py-8 text-center text-slate-400 text-xs border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                                        Pilih Purchase Order di atas untuk memuat daftar barang pesanan.
                                    </div>
                                </template>

                                <template x-for="(item, index) in items" :key="index">
                                    <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 dark:bg-slate-850/50 rounded-xl border border-slate-100 dark:border-slate-800/80 items-center">
                                        <div class="col-span-5">
                                            <div class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="item.item_name"></div>
                                            <div class="text-[10px] text-slate-400 mt-0.5">
                                                Dipesan: <span class="font-bold text-slate-700 dark:text-slate-300" x-text="item.po_qty"></span> | 
                                                Diterima Lalu: <span class="font-bold text-emerald-600" x-text="item.received_qty"></span>
                                            </div>
                                        </div>
                                        <div class="col-span-4 flex flex-col gap-1">
                                            <label class="text-[9px] font-bold text-slate-400">Masuk Sekarang</label>
                                            <input type="number" :name="'items['+index+'][quantity]'" required x-model.number="item.quantity" min="0" :max="item.po_qty - item.received_qty" step="any"
                                                   class="w-full h-9 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 text-center focus:border-primary outline-none">
                                        </div>
                                        <div class="col-span-3 flex flex-col gap-1">
                                            <label class="text-[9px] font-bold text-slate-400">Harga Beli PO</label>
                                            <div class="w-full h-9 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs flex items-center text-slate-700 dark:text-slate-300 font-mono font-bold" x-text="'Rp ' + formatNumber(item.unit_cost)"></div>
                                        </div>
                                        
                                        <input type="hidden" :name="'items['+index+'][item_id]'" :value="item.item_id">
                                        <input type="hidden" :name="'items['+index+'][po_item_id]'" :value="item.po_item_id">
                                        <input type="hidden" :name="'items['+index+'][unit_cost]'" :value="item.unit_cost">
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false"
                                class="btn-touch px-5 h-11 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit" :disabled="items.length === 0"
                                class="btn-touch px-6 h-11 bg-primary hover:bg-primary-hover disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl cursor-pointer transition-all shadow-md shadow-primary/20">
                            Buat GRN Penerimaan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function grnApp(posData) {
            return {
                showCreateModal: false,
                selectedPo: '',
                pos: posData || [],
                items: [],

                openModal() {
                    this.showCreateModal = true;
                },

                onPoChange() {
                    if (!this.selectedPo) {
                        this.items = [];
                        return;
                    }

                    let po = this.pos.find(p => p.id == this.selectedPo);
                    if (po && po.items && po.items.length > 0) {
                        this.items = po.items.map(i => {
                            let poQty = parseFloat(i.quantity) || 0;
                            let recQty = parseFloat(i.received_qty) || 0;
                            let remQty = Math.max(0, poQty - recQty);
                            return {
                                po_item_id: i.id,
                                item_id: i.item_id,
                                item_name: i.item ? i.item.name : 'Barang #' + i.item_id,
                                po_qty: poQty,
                                received_qty: recQty,
                                quantity: remQty,
                                unit_cost: parseFloat(i.unit_cost) || 0
                            };
                        });
                    } else {
                        this.items = [];
                    }
                },

                formatNumber(val) {
                    let num = Math.round(parseFloat(val) || 0);
                    return num.toLocaleString('id-ID');
                }
            };
        }
    </script>
</x-app-layout>
