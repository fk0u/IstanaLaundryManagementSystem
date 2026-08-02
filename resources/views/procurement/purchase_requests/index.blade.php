<x-app-layout>
    <div x-data="{ showCreateModal: false, activeDetailPr: null, items: [{ item_id: '', quantity: 1, unit_cost_estimate: 0, notes: '' }] }" class="flex flex-col gap-6">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <x-page-header title="Purchase Requests (PR)" :breadcrumbs="['Pengadaan' => '#', 'PR' => '/procurement/purchase-requests']" />
            <div class="flex items-center gap-2">
                <a href="{{ route('procurement.purchase-requests.export.pdf') }}" target="_blank"
                   class="h-11 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-base">picture_as_pdf</span> Unduh PDF
                </a>
                <a href="{{ route('procurement.purchase-requests.export.xlsx') }}"
                   class="h-11 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-base">table_chart</span> Ekspor XLSX
                </a>
                <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                    <span class="material-symbols-outlined">add_shopping_cart</span>
                    Buat Purchase Request
                </button>
            </div>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-4" />
        @endif

        <x-card title="Daftar Permintaan Pembelian Bahan Habis Pakai (PR)">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nomor PR</th>
                            <th class="py-3 px-4">Tanggal Pengajuan</th>
                            <th class="py-3 px-4">Diajukan Oleh</th>
                            <th class="py-3 px-4">Rincian Barang</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($purchaseRequests as $pr)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $pr->pr_number }}
                                </td>
                                <td class="py-4 px-4 text-slate-500 whitespace-nowrap">
                                    {{ $pr->request_date?->format('d M Y') }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $pr->requestedBy?->name ?? '-' }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="space-y-1">
                                        @foreach($pr->items->take(2) as $item)
                                            <div class="text-slate-700 dark:text-slate-300 font-medium">
                                                • {{ $item->item?->name }} (<span class="font-bold text-primary">{{ number_format($item->quantity, 0) }} {{ $item->item?->unit }}</span>)
                                            </div>
                                        @endforeach
                                        @if($pr->items->count() > 2)
                                            <span class="text-[10px] text-slate-400 font-bold">+{{ $pr->items->count() - 2 }} barang lainnya</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @if ($pr->status === 'approved')
                                        <x-badge type="success">● Disetujui</x-badge>
                                    @elseif ($pr->status === 'ordered')
                                        <x-badge type="info">● Sudah Di-PO</x-badge>
                                    @elseif ($pr->status === 'rejected')
                                        <x-badge type="danger">Ditolak</x-badge>
                                    @else
                                        <x-badge type="warning">● Pending Approval</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex justify-end gap-2 items-center">
                                        <button type="button" @click="activeDetailPr = @js($pr)"
                                                class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-2xs font-bold transition-all flex items-center gap-1 cursor-pointer">
                                            <span class="material-symbols-outlined text-sm">visibility</span> Detail
                                        </button>

                                        @if ($pr->status === 'pending_approval' && auth()->user()->hasRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin']))
                                            <form action="{{ route('procurement.purchase-requests.approve', $pr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui Purchase Request ini?')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-2xs font-extrabold cursor-pointer transition-all shadow-xs">
                                                    Setujui
                                                </button>
                                            </form>
                                            <form action="{{ route('procurement.purchase-requests.reject', $pr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak Purchase Request ini?')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-2xs font-extrabold cursor-pointer transition-all">
                                                    Tolak
                                                </button>
                                            </form>
                                        @endif

                                        @if ($pr->status === 'approved')
                                            <a href="{{ route('procurement.purchase-orders.index') }}" class="px-2.5 py-1.5 bg-primary hover:bg-orange-600 text-white rounded-xl text-2xs font-extrabold transition-all flex items-center gap-1">
                                                <span class="material-symbols-outlined text-sm">shopping_bag</span> Buat PO
                                            </a>
                                        @endif

                                        @if (in_array($pr->status, ['pending_approval', 'rejected']))
                                            <form action="{{ route('procurement.purchase-requests.destroy', $pr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus PR ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 transition-colors cursor-pointer" title="Hapus PR">
                                                    <span class="material-symbols-outlined text-base">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">Belum ada permintaan pembelian (PR) diajukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $purchaseRequests->links() }}
            </div>
        </x-card>

        <!-- Detail PR Modal -->
        <template x-if="activeDetailPr">
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak @keydown.escape.window="activeDetailPr = null">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-3xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col overflow-hidden">
                    <div class="flex justify-between items-center pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-base font-black text-slate-800 dark:text-slate-200" x-text="'Detail PR ' + activeDetailPr.pr_number"></h3>
                                <template x-if="activeDetailPr.status === 'approved'">
                                    <span class="px-2.5 py-0.5 rounded-full text-2xs font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">● Disetujui</span>
                                </template>
                                <template x-if="activeDetailPr.status === 'ordered'">
                                    <span class="px-2.5 py-0.5 rounded-full text-2xs font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400">● Sudah Di-PO</span>
                                </template>
                                <template x-if="activeDetailPr.status === 'pending_approval'">
                                    <span class="px-2.5 py-0.5 rounded-full text-2xs font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400">● Pending</span>
                                </template>
                                <template x-if="activeDetailPr.status === 'rejected'">
                                    <span class="px-2.5 py-0.5 rounded-full text-2xs font-extrabold bg-rose-100 text-rose-800">Ditolak</span>
                                </template>
                            </div>
                            <p class="text-2xs text-slate-400 mt-0.5" x-text="'Diajukan oleh: ' + (activeDetailPr.requested_by ? activeDetailPr.requested_by.name : '-') + (activeDetailPr.approved_by ? ' | Disetujui: ' + activeDetailPr.approved_by.name : '')"></p>
                        </div>
                        <button type="button" @click="activeDetailPr = null" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                            <span class="material-symbols-outlined text-xl">close</span>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto space-y-4 pr-1">
                        <template x-if="activeDetailPr.notes">
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-100 dark:border-slate-800 text-xs text-slate-700 dark:text-slate-300">
                                <span class="font-bold text-[10px] uppercase text-slate-400 block mb-0.5">Catatan Pengajuan:</span>
                                <span x-text="activeDetailPr.notes"></span>
                            </div>
                        </template>

                        <!-- Items Table -->
                        <div class="space-y-2">
                            <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Rincian Barang Diajukan</span>
                            <div class="border border-slate-100 dark:border-slate-800 rounded-2xl overflow-hidden">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-100 dark:bg-slate-800/80 text-slate-500 font-bold uppercase text-[10px]">
                                        <tr>
                                            <th class="py-2.5 px-3">Nama Barang</th>
                                            <th class="py-2.5 px-3 text-center">Qty Diajukan</th>
                                            <th class="py-2.5 px-3 text-right">Est. Harga Satuan (Rp)</th>
                                            <th class="py-2.5 px-3 text-right">Est. Total Subtotal (Rp)</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                        <template x-for="item in activeDetailPr.items" :key="item.id">
                                            <tr>
                                                <td class="py-2.5 px-3 font-semibold text-slate-800 dark:text-slate-200" x-text="item.item ? item.item.name : 'Item #' + item.item_id"></td>
                                                <td class="py-2.5 px-3 text-center font-bold text-slate-700 dark:text-slate-300" x-text="item.quantity + ' ' + (item.item ? item.item.unit : '')"></td>
                                                <td class="py-2.5 px-3 text-right font-mono" x-text="Math.round(parseFloat(item.unit_cost_estimate) || 0).toLocaleString('id-ID')"></td>
                                                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-900 dark:text-slate-100" x-text="Math.round((parseFloat(item.quantity) || 0) * (parseFloat(item.unit_cost_estimate) || 0)).toLocaleString('id-ID')"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="activeDetailPr = null" class="px-5 py-2 bg-slate-800 text-white text-xs font-bold rounded-xl cursor-pointer">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- Custom Create PR Modal -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Buat Purchase Request Baru</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form action="{{ route('procurement.purchase-requests.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="flex flex-col gap-1.5">
                        <label for="notes" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Catatan Keperluan / Alasan</label>
                        <textarea id="notes" name="notes" placeholder="Contoh: Pembelian detergen stok bulanan workshop utama..." rows="2"
                                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
                    </div>

                    <!-- Items Dynamic Input -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Barang & Kuantitas</span>
                            <button type="button" @click="items.push({ item_id: '', quantity: 1, unit_cost_estimate: 0, notes: '' })" 
                                    class="text-xs font-bold text-primary hover:text-orange-600 flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">add_circle</span> Tambah Baris
                            </button>
                        </div>

                        <div class="grid grid-cols-12 gap-3 px-3 py-2 bg-slate-100 dark:bg-slate-800/80 rounded-xl text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider items-center border border-slate-200/60 dark:border-slate-700/50">
                            <div class="col-span-5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs text-primary">inventory_2</span>
                                <span>Daftar Produk / Barang</span>
                            </div>
                            <div class="col-span-2 text-center flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-xs text-primary">numbers</span>
                                <span>Kuantitas</span>
                            </div>
                            <div class="col-span-4 flex items-center justify-start gap-1">
                                <span class="material-symbols-outlined text-xs text-primary">payments</span>
                                <span>Estimasi Harga Satuan (Rp)</span>
                            </div>
                            <div class="col-span-1 text-center">
                                <span>Hapus</span>
                            </div>
                        </div>
                        
                        <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 dark:bg-slate-850/50 rounded-xl border border-slate-100 dark:border-slate-800/80 items-center">
                                    <div class="col-span-5 flex flex-col gap-1">
                                        <select :name="'items['+index+'][item_id]'" required x-model="item.item_id"
                                                class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                            <option value="">-- Pilih Barang --</option>
                                            @foreach($inventoryItems as $inv)
                                                <option value="{{ $inv->id }}">{{ $inv->name }} (Min: {{ number_format($inv->min_stock, 0) }} / Sisa: {{ number_format($inv->current_stock, 0) }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-2 flex flex-col gap-1">
                                        <input type="number" :name="'items['+index+'][quantity]'" required x-model="item.quantity" min="0.01" step="any" placeholder="Qty"
                                               class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs text-center focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                    <div class="col-span-4 flex flex-col gap-1">
                                        <input type="number" :name="'items['+index+'][unit_cost_estimate]'" required x-model="item.unit_cost_estimate" min="0" placeholder="Estimasi Harga"
                                               class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                    <div class="col-span-1 flex justify-center">
                                        <button type="button" @click="if(items.length > 1) items.splice(index, 1)" class="text-slate-400 hover:text-red-500 cursor-pointer">
                                            <span class="material-symbols-outlined text-lg">remove_circle_outline</span>
                                        </button>
                                    </div>
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
                            Ajukan PR
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
