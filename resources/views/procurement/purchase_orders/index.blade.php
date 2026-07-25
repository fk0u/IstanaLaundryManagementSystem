<x-app-layout>
    <div x-data="{ showCreateModal: false, selectedPr: '', prs: @js($approvedPrs), items: [] }" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Purchase Orders (PO)" :breadcrumbs="['Pengadaan' => '#', 'PO' => '/procurement/purchase-orders']" />
            <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
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
                            <th class="py-3 px-4">Tanggal Order</th>
                            <th class="py-3 px-4">Tgl Estimasi Masuk</th>
                            <th class="py-3 px-4">Total Biaya (Inc PPN)</th>
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
                                                <button type="submit" class="px-2.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all">
                                                    Kirim
                                                </button>
                                            </form>
                                        @endif

                                        @if (in_array($po->status, ['draft', 'sent']))
                                            <form action="{{ route('procurement.purchase-orders.confirm', $po->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengonfirmasi penerimaan/pemrosesan Purchase Order ini?')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all">
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
                                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada PO terdaftar.</td>
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
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-3xl w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Buat Purchase Order (PO) Baru</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form action="{{ route('procurement.purchase-orders.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="supplier_id" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Supplier</label>
                            <select id="supplier_id" name="supplier_id" required
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="">-- Pilih Supplier --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                endforeach
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="expected_date" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Estimasi Masuk</label>
                            <input type="date" id="expected_date" name="expected_date" required min="{{ date('Y-m-d') }}"
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="pr_id" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Hubungkan dengan Purchase Request (Opsional)</label>
                        <select id="pr_id" name="pr_id" x-model="selectedPr"
                                @change="
                                    let pr = prs.find(p => p.id == selectedPr);
                                    if(pr) {
                                        items = pr.items.map(i => ({
                                            item_id: i.item_id,
                                            item_name: i.item.name,
                                            quantity: parseFloat(i.quantity),
                                            unit_cost: parseFloat(i.unit_cost_estimate)
                                        }));
                                    } else {
                                        items = [];
                                    }
                                "
                                class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                            <option value="">-- Tanpa Hubungan PR (Pencatatan Manual) --</option>
                            <template x-for="p in prs" :key="p.id">
                                <option :value="p.id" x-text="p.pr_number + ' - ' + p.requested_by.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Items Table -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Detail Items Purchase Order</span>
                            <button x-show="!selectedPr" type="button" @click="items.push({ item_id: '', item_name: '', quantity: 1, unit_cost: 0 })" 
                                    class="text-xs font-bold text-primary hover:text-orange-600 flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-sm">add_circle</span> Tambah Baris Manual
                            </button>
                        </div>
                        
                        <div class="max-h-60 overflow-y-auto space-y-2 pr-1">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="grid grid-cols-12 gap-3 p-3 bg-slate-50 dark:bg-slate-850/50 rounded-xl border border-slate-100 dark:border-slate-800/80 items-center">
                                    <div class="col-span-5 flex flex-col gap-1">
                                        <template x-if="selectedPr">
                                            <div class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-850 bg-slate-200 dark:bg-slate-800 text-xs flex items-center text-slate-600 dark:text-slate-450 font-bold" x-text="item.item_name"></div>
                                        </template>
                                        <template x-if="!selectedPr">
                                            <select :name="'items['+index+'][item_id]'" required x-model="item.item_id"
                                                    class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                                <option value="">-- Pilih Barang --</option>
                                                @foreach($inventoryItems as $inv)
                                                    <option value="{{ $inv->id }}">{{ $inv->name }}</option>
                                                @endforeach
                                            </select>
                                        </template>
                                        <input type="hidden" :name="'items['+index+'][item_id]'" x-model="item.item_id">
                                    </div>
                                    <div class="col-span-3 flex flex-col gap-1">
                                        <input type="number" :name="'items['+index+'][quantity]'" required x-model="item.quantity" min="0.01" step="any" placeholder="Qty"
                                               class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs text-center focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                    <div class="col-span-3 flex flex-col gap-1">
                                        <input type="number" :name="'items['+index+'][unit_cost]'" required x-model="item.unit_cost" min="0" placeholder="Harga Unit"
                                               class="w-full h-10 px-3 rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none">
                                    </div>
                                    <div class="col-span-1 flex justify-center">
                                        <button x-show="!selectedPr" type="button" @click="items.splice(index, 1)" class="text-slate-400 hover:text-red-500 cursor-pointer">
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
                            Buat PO
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
