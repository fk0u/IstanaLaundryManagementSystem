<x-app-layout>
    <div x-data="{ showCreateModal: false, items: [{ item_id: '', quantity: 1, unit_cost_estimate: 0, notes: '' }] }" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Purchase Requests (PR)" :breadcrumbs="['Pengadaan' => '#', 'PR' => '/procurement/purchase-requests']" />
            <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                <span class="material-symbols-outlined">add_shopping_cart</span>
                Buat Purchase Request
            </button>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-4" />
        @endif

        <x-card title="Daftar Permintaan Pembelian Bahan Habis Pakai">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nomor PR</th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Diajukan Oleh</th>
                            <th class="py-3 px-4">Detail Items</th>
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
                                <td class="py-4 px-4 text-slate-500">
                                    {{ $pr->request_date?->format('d M Y') }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $pr->requestedBy?->name }}
                                </td>
                                <td class="py-4 px-4">
                                    <ul class="list-disc list-inside space-y-1 text-slate-600 dark:text-slate-400">
                                        @foreach($pr->items as $item)
                                            <li>{{ $item->item?->name }} ({{ number_format($item->quantity, 0) }} {{ $item->item?->unit }})</li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="py-4 px-4">
                                    @if ($pr->status === 'approved')
                                        <x-badge type="success">Disetujui</x-badge>
                                    @elseif ($pr->status === 'rejected')
                                        <x-badge type="danger">Ditolak</x-badge>
                                    @else
                                        <x-badge type="warning">Pending</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if ($pr->status === 'pending_approval' && auth()->user()->hasRole(['Developer', 'Owner', 'Super Admin', 'Branch Admin']))
                                            <form action="{{ route('procurement.purchase-requests.approve', $pr->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all">
                                                    Setujui
                                                </button>
                                            </form>
                                            <form action="{{ route('procurement.purchase-requests.reject', $pr->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-red-550 hover:bg-red-650 text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all">
                                                    Tolak
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if (in_array($pr->status, ['pending_approval', 'rejected']))
                                            <form action="{{ route('procurement.purchase-requests.destroy', $pr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus PR ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-500 hover:text-red-500 transition-colors cursor-pointer" title="Hapus PR">
                                                    <span class="material-symbols-outlined text-base">delete</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">Belum ada permintaan pembelian diajukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $purchaseRequests->links() }}
            </div>
        </x-card>

        <!-- Custom Create PR Modal -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl transition-all duration-300">
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
