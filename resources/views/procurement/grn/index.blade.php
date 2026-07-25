<x-app-layout>
    <div x-data="{ showCreateModal: false, selectedPo: '', pos: @js($activePos), items: [] }" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Goods Received Notes (GRN)" :breadcrumbs="['Pengadaan' => '#', 'GRN' => '/procurement/grns']" />
            <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
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
                            <th class="py-3 px-4">Keterangan</th>
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
                                <td class="py-4 px-4 font-mono text-slate-650 dark:text-slate-400">
                                    {{ $grn->purchaseOrder?->po_number }}
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
                                    <div class="flex justify-end gap-2">
                                        @if ($grn->status === 'draft')
                                            <form action="{{ route('procurement.grns.confirm', $grn->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi penerimaan barang ini? Tindakan ini akan menambah stok dan memposting jurnal persediaan secara otomatis.')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all">
                                                    Konfirmasi Penerimaan
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('procurement.grns.destroy', $grn->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus GRN ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-500 hover:text-red-500 transition-colors cursor-pointer" title="Hapus GRN">
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
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-3xl w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Buat Goods Received Note Baru</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form action="{{ route('procurement.grns.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="po_id" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Purchase Order Terkait</label>
                            <select id="po_id" name="po_id" x-model="selectedPo" required
                                    @change="
                                        let po = pos.find(p => p.id == selectedPo);
                                        if(po) {
                                            items = po.items.map(i => ({
                                                po_item_id: i.id,
                                                item_id: i.item_id,
                                                item_name: i.item.name,
                                                po_qty: parseFloat(i.quantity),
                                                received_qty: parseFloat(i.received_qty),
                                                quantity: parseFloat(i.quantity) - parseFloat(i.received_qty),
                                                unit_cost: parseFloat(i.unit_cost)
                                            }));
                                        } else {
                                            items = [];
                                        }
                                    "
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="">-- Pilih Purchase Order --</option>
                                <template x-for="p in pos" :key="p.id">
                                    <option :value="p.id" x-text="p.po_number + ' - ' + p.supplier.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="received_date" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Penerimaan</label>
                            <input type="date" id="received_date" name="received_date" required max="{{ date('Y-m-d') }}"
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="notes" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Catatan Tambahan</label>
                        <textarea id="notes" name="notes" placeholder="Contoh: Kondisi barang baik, kemasan aman..." rows="2"
                                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
                    </div>

                    <!-- Items Detail Table -->
                    <div class="space-y-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Verifikasi Kuantitas Barang Masuk</span>
                        
                        <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 dark:bg-slate-850/50 rounded-xl border border-slate-100 dark:border-slate-800/80 items-center">
                                    <div class="col-span-5">
                                        <div class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="item.item_name"></div>
                                        <div class="text-[10px] text-slate-400">Dipesan: <span x-text="item.po_qty"></span> | Diterima: <span x-text="item.received_qty"></span></div>
                                    </div>
                                    <div class="col-span-4 flex flex-col gap-1">
                                        <label class="text-[9px] text-slate-400">Masuk Sekarang</label>
                                        <input type="number" :name="'items['+index+'][quantity]'" required x-model="item.quantity" min="0" :max="item.po_qty - item.received_qty" step="any"
                                               class="w-full h-9 px-3 rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs text-center focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                    <div class="col-span-3 flex flex-col gap-1">
                                        <label class="text-[9px] text-slate-400">Harga Beli PO</label>
                                        <div class="w-full h-9 px-3 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs flex items-center text-slate-600 dark:text-slate-400 font-mono" x-text="'Rp ' + parseFloat(item.unit_cost).toLocaleString('id-ID')"></div>
                                    </div>
                                    
                                    <input type="hidden" :name="'items['+index+'][item_id]'" x-model="item.item_id">
                                    <input type="hidden" :name="'items['+index+'][po_item_id]'" x-model="item.po_item_id">
                                    <input type="hidden" :name="'items['+index+'][unit_cost]'" x-model="item.unit_cost">
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl cursor-pointer transition-all">
                            Buat GRN
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
