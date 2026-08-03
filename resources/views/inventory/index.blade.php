<x-app-layout>
    <div x-data="{ 
        showCreateModal: false, 
        showAdjustModal: false, 
        adjustId: null, 
        adjustName: '', 
        adjustCurrent: 0, 
        adjustUnit: '',
        openAdjust(item) {
            this.adjustId = item.id;
            this.adjustName = item.name;
            this.adjustCurrent = item.current_stock;
            this.adjustUnit = item.unit;
            this.showAdjustModal = true;
        }
    }" class="flex flex-col gap-6">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <x-page-header title="Manajemen Inventori & Stok Bahan" :breadcrumbs="['Inventori' => '/inventory']" />
            <div class="flex items-center gap-2">
                <a href="{{ route('inventory.export.pdf') }}" target="_blank"
                   class="h-11 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-base">picture_as_pdf</span> Unduh PDF
                </a>
                <a href="{{ route('inventory.export.xlsx') }}"
                   class="h-11 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-base">table_chart</span> Ekspor XLSX
                </a>
                <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                    <span class="material-symbols-outlined">add</span>
                    Tambah Item Inventori
                </button>
            </div>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif

        <div class="relative max-w-md">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">search</span>
            <input type="text" name="search" value="{{ request('search') }}"
                   data-realtime-search="inventory-table-container"
                   data-search-url="{{ route('inventory.index') }}"
                   placeholder="Cari SKU, nama barang, atau kategori (Real-time)..."
                   class="w-full pl-10 pr-8 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all">
        </div>

        <div id="inventory-table-container">
            <x-card title="Stok Bahan Kimia & Sabun Cabang">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Item</th>
                            <th class="py-3 px-4">SKU / Kode</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Stok Saat Ini</th>
                            <th class="py-3 px-4">Stok Minimum</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($inventoryItems as $item)
                            @php
                                $isLowStock = $item->current_stock <= $item->min_stock;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200 text-sm">
                                    {{ $item->name }}
                                </td>
                                <td class="py-4 px-4 font-mono font-semibold text-slate-500">
                                    {{ $item->sku }}
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400 capitalize">
                                    {{ $item->category }}
                                </td>
                                <td class="py-4 px-4 font-bold text-sm {{ $isLowStock ? 'text-red-500' : 'text-slate-800 dark:text-slate-200' }}">
                                    {{ round($item->current_stock, 2) }} {{ $item->unit }}
                                </td>
                                <td class="py-4 px-4 text-slate-500 font-semibold">
                                    {{ round($item->min_stock, 2) }} {{ $item->unit }}
                                </td>
                                <td class="py-4 px-4">
                                    @if ($isLowStock)
                                        <x-badge type="danger">Stok Menipis</x-badge>
                                    @else
                                        <x-badge type="success">Stok Aman</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <button @click="openAdjust({{ $item->toJson() }})" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-350 font-bold rounded-lg transition-colors cursor-pointer text-[10px]">
                                        Koreksi Stok
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada item inventori terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $inventoryItems->links() }}
            </div>
        </x-card>
        </div>

        <!-- Custom Create Item Modal -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Tambah Item Inventori</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form action="{{ route('inventory.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="flex flex-col gap-1.5">
                        <label for="name" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Item Bahan</label>
                        <input type="text" id="name" name="name" required placeholder="Contoh: Deterjen Liquid Scented..."
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="sku" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">SKU / Kode Barang (Harus Unik)</label>
                        <input type="text" id="sku" name="sku" required placeholder="Contoh: DET-LIQ-01..."
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="category" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kategori</label>
                            <select id="category" name="category" required
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="deterjen">Deterjen & Sabun</option>
                                <option value="pewangi">Pewangi & Softener</option>
                                <option value="pemutih">Pemutih & Chemical</option>
                                <option value="kemasan">Kemasan & Plastik</option>
                                <option value="lainnya">Lain-lain</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="unit" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Satuan Ukur</label>
                            <input type="text" id="unit" name="unit" required placeholder="Contoh: Liter, Kg, Pcs..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="min_stock" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Batas Stok Minimum</label>
                            <input type="number" id="min_stock" name="min_stock" required min="0" step="0.01" placeholder="Contoh: 10..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="current_stock" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Stok Awal Saat Ini</label>
                            <input type="number" id="current_stock" name="current_stock" required min="0" step="0.01" placeholder="Contoh: 100..."
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
                            Simpan Item
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Custom Adjust Stock Modal -->
        <div x-show="showAdjustModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showAdjustModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Koreksi Stok Item</h3>
                    <button @click="showAdjustModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form :action="'/inventory/' + adjustId + '/adjust'" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-4 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-100 dark:border-slate-850">
                        <span class="block text-xs font-bold text-slate-400 uppercase tracking-wide">Nama Item</span>
                        <span class="block font-bold text-slate-800 dark:text-slate-200 text-sm mt-1" x-text="adjustName"></span>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="adjust_current" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jumlah Stok Baru</label>
                        <div class="relative">
                            <input type="number" id="adjust_current" name="current_stock" x-model="adjustCurrent" required min="0" step="0.01"
                                   class="w-full h-11 pl-3 pr-16 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-xs text-slate-400" x-text="adjustUnit"></span>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showAdjustModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl cursor-pointer transition-all">
                            Simpan Koreksi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
