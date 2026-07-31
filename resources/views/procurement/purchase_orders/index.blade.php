<x-app-layout>
    <div x-data="poCreateApp(@js($approvedPrs))" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Purchase Orders (PO)" :breadcrumbs="['Pengadaan' => '#', 'PO' => '/procurement/purchase-orders']" />
            <button @click="openModal()" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                <span class="material-symbols-outlined">description</span>
                Buat Purchase Order
            </button>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-4" />
        @endif

        <x-card title="Daftar Purchase Order Pengadaan">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nomor PO</th>
                            <th class="py-3 px-4">Supplier</th>
                            <th class="py-3 px-4">Link PR</th>
                            <th class="py-3 px-4">Tanggal Order</th>
                            <th class="py-3 px-4">Tgl Estimasi Masuk</th>
                            <th class="py-3 px-4">Total Biaya (Inc PPN 11%)</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($purchaseOrders as $po)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $po->po_number }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $po->supplier?->name }}
                                </td>
                                <td class="py-4 px-4 font-mono text-2xs text-slate-500">
                                    @if ($po->pr)
                                        <span class="px-2 py-0.5 rounded bg-orange-50 dark:bg-slate-800 text-primary dark:text-orange-400 font-bold border border-orange-200/50 dark:border-slate-700">
                                            {{ $po->pr->pr_number }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 font-normal">Manual (Tanpa PR)</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ $po->order_date?->format('d M Y') }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ $po->expected_date?->format('d M Y') }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($po->total, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4">
                                    @if ($po->status === 'completed')
                                        <x-badge type="success">Selesai (Received)</x-badge>
                                    @elseif ($po->status === 'partial')
                                        <x-badge type="info">Diterima Sebagian</x-badge>
                                    @elseif ($po->status === 'confirmed')
                                        <x-badge type="primary">Terkonfirmasi</x-badge>
                                    @elseif ($po->status === 'sent')
                                        <x-badge type="warning">Dikirim</x-badge>
                                    @else
                                        <x-badge type="gray">Draft</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if ($po->status === 'draft')
                                            <form action="{{ route('procurement.purchase-orders.send', $po->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengirim Purchase Order ini ke supplier?')">
                                                @csrf
                                                <button type="submit" class="btn-touch px-2.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all">
                                                    Kirim
                                                </button>
                                            </form>
                                        @endif

                                        @if (in_array($po->status, ['draft', 'sent']))
                                            <form action="{{ route('procurement.purchase-orders.confirm', $po->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi penerimaan/pemrosesan Purchase Order ini?')">
                                                @csrf
                                                <button type="submit" class="btn-touch px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all">
                                                    Konfirmasi
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if ($po->status === 'draft')
                                            <form action="{{ route('procurement.purchase-orders.destroy', $po->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus PO ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-500 hover:text-red-500 transition-colors cursor-pointer" title="Hapus PO">
                                                    <span class="material-symbols-outlined text-base">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">Belum ada PO terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $purchaseOrders->links() }}
            </div>
        </x-card>

        <!-- Custom Create PO Modal -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-4xl w-full p-6 shadow-2xl transition-all duration-300 max-h-[90vh] flex flex-col overflow-hidden">
                
                <div class="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-black text-slate-800 dark:text-slate-200">Buat Purchase Order (PO) Baru</h3>
                        <p class="text-2xs text-slate-400 mt-0.5">Buat pesanan ke supplier baik secara manual atau hubungkan dari Purchase Request yang disetujui.</p>
                    </div>
                    <button @click="showCreateModal = false" class="btn-touch text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer p-1 rounded-xl">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
                
                <form action="{{ route('procurement.purchase-orders.store') }}" method="POST" class="flex-1 flex flex-col overflow-hidden space-y-4 pt-4">
                    @csrf
                    
                    <div class="overflow-y-auto space-y-4 pr-1 flex-1">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1.5">
                                <label for="supplier_id" class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Supplier <span class="text-rose-500">*</span></label>
                                <select id="supplier_id" name="supplier_id" required
                                        class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 focus:border-primary outline-none transition-all">
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <label for="expected_date" class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Tanggal Estimasi Masuk <span class="text-rose-500">*</span></label>
                                <input type="date" id="expected_date" name="expected_date" required min="{{ date('Y-m-d') }}"
                                       class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 focus:border-primary outline-none transition-all">
                            </div>
                        </div>

                        <!-- PR Selector -->
                        <div class="flex flex-col gap-1.5 bg-slate-50 dark:bg-slate-800/40 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
                            <label for="pr_id" class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider flex items-center justify-between">
                                <span>Hubungkan dengan Purchase Request (Disetujui)</span>
                                <span class="text-primary font-bold">{{ count($approvedPrs) }} PR Tersedia</span>
                            </label>
                            <select id="pr_id" name="pr_id" x-model="selectedPr" @change="onPrChange()"
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 focus:border-primary outline-none transition-all">
                                <option value="">-- Tanpa Hubungan PR (Pencatatan Manual) --</option>
                                @foreach($approvedPrs as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->pr_number }} - Ditulis oleh {{ $p->requestedBy?->name ?? 'Staf' }} ({{ $p->items->count() }} jenis barang)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Items Table -->
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Detail Barang / Material PO</span>
                                <button x-show="!selectedPr" type="button" @click="addItemRow()" 
                                        class="text-xs font-bold text-primary hover:text-orange-600 flex items-center gap-1 cursor-pointer">
                                    <span class="material-symbols-outlined text-sm">add_circle</span> Tambah Baris Barang
                                </button>
                            </div>

                            <!-- Table Column Headers -->
                            <div class="grid grid-cols-12 gap-3 px-3 py-2 bg-slate-100 dark:bg-slate-800/80 rounded-xl text-[10px] font-extrabold text-slate-600 dark:text-slate-300 uppercase tracking-wider items-center border border-slate-200/60 dark:border-slate-700/50">
                                <div class="col-span-5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs text-primary">inventory_2</span>
                                    <span>Nama Barang</span>
                                </div>
                                <div class="col-span-2 text-center">
                                    <span>Qty</span>
                                </div>
                                <div class="col-span-3">
                                    <span>Harga Satuan (Rp)</span>
                                </div>
                                <div class="col-span-2 text-right">
                                    <span>Subtotal</span>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <template x-if="items.length === 0">
                                    <div class="py-8 text-center text-slate-400 text-xs border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                                        Belum ada barang dipilih. Silakan hubungkan dari Purchase Request atau klik "Tambah Baris Barang".
                                    </div>
                                </template>

                                <template x-for="(item, index) in items" :key="index">
                                    <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 dark:bg-slate-850/50 rounded-xl border border-slate-100 dark:border-slate-800/80 items-center">
                                        <div class="col-span-5 flex flex-col gap-1">
                                            <!-- If linked to PR show read-only item name -->
                                            <template x-if="selectedPr">
                                                <div class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-200/60 dark:bg-slate-800 text-xs flex items-center text-slate-700 dark:text-slate-300 font-bold truncate" x-text="item.item_name"></div>
                                            </template>

                                            <!-- If manual PO show item select -->
                                            <template x-if="!selectedPr">
                                                <select :name="'items['+index+'][item_id]'" required x-model="item.item_id"
                                                        class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 focus:border-primary outline-none">
                                                    <option value="">-- Pilih Barang --</option>
                                                    @foreach($inventoryItems as $inv)
                                                        <option value="{{ $inv->id }}">{{ $inv->name }}</option>
                                                    @endforeach
                                                </select>
                                            </template>

                                            <input type="hidden" :name="'items['+index+'][item_id]'" :value="item.item_id">
                                        </div>

                                        <div class="col-span-2 flex flex-col gap-1">
                                            <input type="number" :name="'items['+index+'][quantity]'" required x-model.number="item.quantity" min="0.01" step="any" placeholder="Qty"
                                                   class="w-full h-10 px-2 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 text-center focus:border-primary outline-none">
                                        </div>

                                        <div class="col-span-3 flex flex-col gap-1">
                                            <input type="number" :name="'items['+index+'][unit_cost]'" required x-model.number="item.unit_cost" min="0" placeholder="Harga Unit"
                                                   class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 focus:border-primary outline-none">
                                        </div>

                                        <div class="col-span-2 flex items-center justify-between gap-1">
                                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 font-mono" x-text="'Rp ' + formatNumber((item.quantity || 0) * (item.unit_cost || 0))"></span>
                                            
                                            <button x-show="!selectedPr" type="button" @click="items.splice(index, 1)" class="text-slate-400 hover:text-red-500 cursor-pointer p-1">
                                                <span class="material-symbols-outlined text-base">remove_circle_outline</span>
                                            </button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Grand Total Calculation Summary -->
                        <div class="p-4 rounded-2xl bg-orange-50/50 dark:bg-slate-800/60 border border-orange-100 dark:border-slate-700 flex flex-col gap-1 items-end text-xs">
                            <div class="flex justify-between w-full max-w-xs text-slate-500">
                                <span>Subtotal Barang:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 font-mono" x-text="'Rp ' + formatNumber(calcSubtotal())"></span>
                            </div>
                            <div class="flex justify-between w-full max-w-xs text-slate-500">
                                <span>PPN Standard (11%):</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 font-mono" x-text="'Rp ' + formatNumber(calcSubtotal() * 0.11)"></span>
                            </div>
                            <div class="flex justify-between w-full max-w-xs text-sm font-black text-primary pt-1.5 border-t border-orange-200/60 dark:border-slate-700">
                                <span>Total PO:</span>
                                <span class="font-mono text-base" x-text="'Rp ' + formatNumber(calcSubtotal() * 1.11)"></span>
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
                            Buat Purchase Order
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function poCreateApp(prsData) {
            return {
                showCreateModal: false,
                selectedPr: '',
                prs: prsData || [],
                items: [],

                openModal() {
                    this.showCreateModal = true;
                    if (this.items.length === 0 && !this.selectedPr) {
                        this.addItemRow();
                    }
                },

                addItemRow() {
                    this.items.push({
                        item_id: '',
                        item_name: '',
                        quantity: 1,
                        unit_cost: 0
                    });
                },

                onPrChange() {
                    if (!this.selectedPr) {
                        this.items = [];
                        this.addItemRow();
                        return;
                    }

                    let pr = this.prs.find(p => p.id == this.selectedPr);
                    if (pr && pr.items && pr.items.length > 0) {
                        this.items = pr.items.map(i => ({
                            item_id: i.item_id,
                            item_name: i.item ? i.item.name : 'Barang #' + i.item_id,
                            quantity: parseFloat(i.quantity) || 1,
                            unit_cost: parseFloat(i.unit_cost_estimate) || 0
                        }));
                    } else {
                        this.items = [];
                        this.addItemRow();
                    }
                },

                calcSubtotal() {
                    return this.items.reduce((sum, item) => {
                        let q = parseFloat(item.quantity) || 0;
                        let c = parseFloat(item.unit_cost) || 0;
                        return sum + (q * c);
                    }, 0);
                },

                formatNumber(val) {
                    let num = Math.round(parseFloat(val) || 0);
                    return num.toLocaleString('id-ID');
                }
            };
        }
    </script>
</x-app-layout>
