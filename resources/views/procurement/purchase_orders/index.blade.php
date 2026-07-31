<x-app-layout>
    <div x-data="poApp(@js($approvedPrs))" class="flex flex-col gap-6">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <x-page-header title="Purchase Orders (PO)" :breadcrumbs="['Pengadaan' => '#', 'PO' => '/procurement/purchase-orders']" />
            <div class="flex items-center gap-2">
                <a href="{{ route('procurement.purchase-orders.export.pdf') }}" target="_blank"
                   class="h-11 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-base">picture_as_pdf</span> Unduh PDF
                </a>
                <a href="{{ route('procurement.purchase-orders.export.xlsx') }}"
                   class="h-11 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-base">table_chart</span> Ekspor XLSX
                </a>
                <button @click="openModal()" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                    <span class="material-symbols-outlined">description</span>
                    Buat Purchase Order
                </button>
            </div>
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
                                    <div class="flex justify-end items-center gap-1.5">
                                        <!-- Detail Modal Button -->
                                        <button type="button" @click="openPODetail({{ $po->id }})" class="btn-touch px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-2xs font-extrabold flex items-center gap-1 cursor-pointer transition-all haptic-press">
                                            <span class="material-symbols-outlined text-sm text-primary">visibility</span>
                                            Detail
                                        </button>

                                        <!-- WhatsApp Button -->
                                        @if ($po->supplier?->phone)
                                            <a href="{{ route('procurement.purchase-orders.whatsapp', $po->id) }}" target="_blank"
                                               class="btn-touch px-2.5 py-1.5 bg-[#25D366] hover:bg-emerald-600 text-white rounded-xl text-2xs font-extrabold flex items-center gap-1 cursor-pointer transition-all shadow-xs haptic-press"
                                               title="Kirim PO ke WhatsApp Supplier">
                                                <span class="material-symbols-outlined text-sm">chat</span>
                                                WA
                                            </a>
                                        @endif

                                        <!-- Print PO Button -->
                                        <a href="{{ route('procurement.purchase-orders.print', $po->id) }}" target="_blank"
                                           class="btn-touch px-2 py-1.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-xl text-2xs font-bold flex items-center gap-1 cursor-pointer transition-all"
                                           title="Cetak Surat PO Official">
                                            <span class="material-symbols-outlined text-sm">print</span>
                                        </a>

                                        @if ($po->status === 'draft')
                                            <form action="{{ route('procurement.purchase-orders.send', $po->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menandai Purchase Order ini sebagai terkirim ke supplier?')">
                                                @csrf
                                                <button type="submit" class="btn-touch px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-2xs font-extrabold cursor-pointer transition-all shadow-xs haptic-press">
                                                    Kirim
                                                </button>
                                            </form>
                                        @endif

                                        @if (in_array($po->status, ['draft', 'sent']))
                                            <form action="{{ route('procurement.purchase-orders.confirm', $po->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi penerimaan/pemrosesan Purchase Order ini?')">
                                                @csrf
                                                <button type="submit" class="btn-touch px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-2xs font-extrabold cursor-pointer transition-all shadow-xs haptic-press">
                                                    Konfirmasi
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if ($po->status === 'draft')
                                            <form action="{{ route('procurement.purchase-orders.destroy', $po->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus PO ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer" title="Hapus PO">
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
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->phone ?? 'No WA' }})</option>
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

        <!-- PO Detail Preview Modal -->
        <div x-show="showDetailModal"
             class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/70 backdrop-blur-md p-4 sm:p-6"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             x-cloak>
            
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl w-full max-w-4xl p-6 shadow-2xl flex flex-col max-h-[90vh] overflow-hidden"
                 @click.away="showDetailModal = false">
                
                <!-- Header -->
                <div class="flex justify-between items-start pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-orange-50 text-primary dark:bg-slate-800 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-2xl">description</span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-slate-900 dark:text-white" x-text="poDetail?.po?.po_number || 'Loading...'"></h3>
                                <template x-if="poDetail?.po">
                                    <span class="px-2.5 py-0.5 rounded-full text-2xs font-extrabold uppercase tracking-wider"
                                          :class="{
                                              'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 border border-emerald-200/50': poDetail.po.status === 'completed',
                                              'bg-blue-50 text-blue-600 dark:bg-blue-950/40 border border-blue-200/50': poDetail.po.status === 'confirmed',
                                              'bg-amber-50 text-amber-600 dark:bg-amber-950/40 border border-amber-200/50': poDetail.po.status === 'sent',
                                              'bg-slate-100 text-slate-600 dark:bg-slate-800 border border-slate-200/50': poDetail.po.status === 'draft'
                                          }"
                                          x-text="poDetail.po.status">
                                    </span>
                                </template>
                            </div>
                            <p class="text-2xs text-slate-400 font-semibold mt-0.5" x-text="'Supplier: ' + (poDetail?.po?.supplier_name || '-') + ' • Cabang Tujuan: ' + (poDetail?.po?.branch_name || '-')"></p>
                        </div>
                    </div>

                    <button type="button" @click="showDetailModal = false" class="btn-touch p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto py-5 space-y-6 pr-1">
                    <template x-if="detailLoading">
                        <div class="py-16 text-center flex flex-col items-center justify-center gap-3">
                            <span class="material-symbols-outlined text-primary text-4xl animate-spin">progress_activity</span>
                            <span class="text-xs font-bold text-slate-400">Memuat detail Purchase Order...</span>
                        </div>
                    </template>

                    <template x-if="!detailLoading && poDetail">
                        <div class="space-y-6">
                            <!-- Info Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
                                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Kontak Supplier</span>
                                    <div class="font-bold text-xs text-slate-800 dark:text-slate-200" x-text="poDetail.po.supplier_name"></div>
                                    <div class="text-2xs text-slate-500 mt-0.5" x-text="'📞 ' + poDetail.po.supplier_phone"></div>
                                    <div class="text-2xs text-slate-500" x-text="'✉️ ' + poDetail.po.supplier_email"></div>
                                </div>

                                <div class="bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800">
                                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Tanggal Pesanan & Estimasi</span>
                                    <div class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="'Order: ' + poDetail.po.order_date"></div>
                                    <div class="text-xs font-extrabold text-primary mt-0.5" x-text="'Estimasi Masuk: ' + poDetail.po.expected_date"></div>
                                    <template x-if="poDetail.po.pr_number">
                                        <div class="text-2xs text-slate-400 mt-1" x-text="'Ref PR: ' + poDetail.po.pr_number"></div>
                                    </template>
                                </div>

                                <div class="bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-2xl border border-slate-100 dark:border-slate-800 flex flex-col justify-between">
                                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Total Biaya Order</span>
                                    <div>
                                        <span class="text-lg font-black text-primary font-mono" x-text="'Rp ' + formatNumber(poDetail.po.total)"></span>
                                        <span class="block text-[10px] text-slate-400 font-semibold" x-text="'Termasuk PPN 11% (Rp ' + formatNumber(poDetail.po.tax_amount) + ')'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="space-y-3">
                                <h4 class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Daftar Barang yang Dipesan</h4>
                                <div class="border border-slate-200/80 dark:border-slate-800 rounded-2xl overflow-hidden shadow-xs">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-2xs font-extrabold text-slate-400 uppercase tracking-wider">
                                            <tr>
                                                <th class="py-2.5 px-3">Nama Barang</th>
                                                <th class="py-2.5 px-3 text-center">Qty Dipesan</th>
                                                <th class="py-2.5 px-3 text-center">Qty Diterima (GRN)</th>
                                                <th class="py-2.5 px-3 text-right">Harga Satuan</th>
                                                <th class="py-2.5 px-3 text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50 bg-white dark:bg-slate-900">
                                            <template x-for="item in poDetail.items" :key="item.id">
                                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/30 transition-colors">
                                                    <td class="py-2.5 px-3 font-bold text-slate-800 dark:text-slate-200" x-text="item.name"></td>
                                                    <td class="py-2.5 px-3 text-center font-bold text-slate-900 dark:text-slate-100 font-mono" x-text="item.quantity + ' ' + item.unit"></td>
                                                    <td class="py-2.5 px-3 text-center font-bold font-mono" :class="item.received_qty >= item.quantity ? 'text-emerald-600' : 'text-amber-600'" x-text="item.received_qty + ' ' + item.unit"></td>
                                                    <td class="py-2.5 px-3 text-right font-mono text-slate-600 dark:text-slate-400" x-text="'Rp ' + formatNumber(item.unit_cost)"></td>
                                                    <td class="py-2.5 px-3 text-right font-black font-mono text-slate-900 dark:text-slate-100" x-text="'Rp ' + formatNumber(item.subtotal)"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer Action Buttons -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <button type="button" @click="showDetailModal = false"
                            class="btn-touch px-4 h-10 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-350 font-extrabold text-xs rounded-xl transition-all cursor-pointer">
                        Tutup
                    </button>

                    <div class="flex items-center gap-2" x-show="poDetail">
                        <!-- Direct WhatsApp Button -->
                        <a :href="'/procurement/purchase-orders/' + poDetail?.po?.id + '/whatsapp'" target="_blank"
                           class="btn-touch px-4 h-10 bg-[#25D366] hover:bg-emerald-600 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-500/20 flex items-center gap-1.5 transition-all cursor-pointer haptic-press">
                            <span class="material-symbols-outlined text-base">chat</span>
                            Kirim via WhatsApp Supplier
                        </a>

                        <!-- Print Button -->
                        <a :href="'/procurement/purchase-orders/' + poDetail?.po?.id + '/print'" target="_blank"
                           class="btn-touch px-4 h-10 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl flex items-center gap-1.5 transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-base">print</span>
                            Cetak Document
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        function poApp(prsData) {
            return {
                showCreateModal: false,
                showDetailModal: false,
                detailLoading: false,
                poDetail: null,
                selectedPr: '',
                prs: prsData || [],
                items: [],

                openModal() {
                    this.showCreateModal = true;
                    if (this.items.length === 0 && !this.selectedPr) {
                        this.addItemRow();
                    }
                },

                openPODetail(poId) {
                    this.showDetailModal = true;
                    this.detailLoading = true;
                    this.poDetail = null;

                    fetch(`/procurement/purchase-orders/${poId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.poDetail = data;
                        this.detailLoading = false;
                    })
                    .catch(err => {
                        console.error(err);
                        this.detailLoading = false;
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal memuat detail PO.', type: 'error' } }));
                    });
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
