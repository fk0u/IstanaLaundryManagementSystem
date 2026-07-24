<x-app-layout>
    <div x-data="{ showCreateModal: false }" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Manajemen Aset Tetap & Depresiasi" :breadcrumbs="['Aset' => '/assets']" />
            <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                <span class="material-symbols-outlined">add_business</span>
                Daftarkan Aset
            </button>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif

        <x-card title="Daftar Mesin & Peralatan Cabang">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Alat</th>
                            <th class="py-3 px-4">Kode Aset</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Biaya Perolehan</th>
                            <th class="py-3 px-4">Nilai Buku</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200 text-sm">
                                    {{ $asset->name }}
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-500">
                                    {{ $asset->asset_code }}
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400 capitalize">
                                    {{ $asset->category }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 font-bold text-primary">
                                    Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4">
                                    @if ($asset->is_active)
                                        <x-badge type="success">Operasional</x-badge>
                                    @else
                                        <x-badge type="danger">Disposal / Rusak</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin melakukan disposal/hapus aset tetap ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-slate-500 hover:text-red-500 transition-colors cursor-pointer" title="Hapus Aset">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada data aset tetap terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $assets->links() }}
            </div>
        </x-card>

        <!-- Custom Create Asset Modal -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Daftarkan Aset Tetap Baru</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form action="{{ route('assets.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="flex flex-col gap-1.5">
                        <label for="name" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Aset / Alat</label>
                        <input type="text" id="name" name="name" required placeholder="Contoh: Mesin Cuci LG 15kg..."
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="asset_code" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kode Aset (Harus Unik)</label>
                        <input type="text" id="asset_code" name="asset_code" required placeholder="Contoh: AST-MC-001..."
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="category" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kategori Aset</label>
                            <select id="category" name="category" required
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="mesin">Mesin Cuci & Kering</option>
                                <option value="peralatan">Peralatan Workshop</option>
                                <option value="kendaraan">Kendaraan Operasional</option>
                                <option value="furniture">Furniture & Mebel</option>
                                <option value="komputer">Komputer & IT</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="acquisition_date" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Perolehan</label>
                            <input type="date" id="acquisition_date" name="acquisition_date" required
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="acquisition_cost" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Biaya Perolehan awal (Rp)</label>
                            <input type="number" id="acquisition_cost" name="acquisition_cost" required min="0" placeholder="Contoh: 8500000..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="salvage_value" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nilai Residu/Sisa (Rp)</label>
                            <input type="number" id="salvage_value" name="salvage_value" required min="0" placeholder="Contoh: 500000..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="useful_life_months" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Umur Ekonomis (Bulan)</label>
                            <input type="number" id="useful_life_months" name="useful_life_months" required min="1" placeholder="Contoh: 60..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="depreciation_method" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Metode Penyusutan</label>
                            <select id="depreciation_method" name="depreciation_method" required
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="straight_line">Garis Lurus (Straight Line)</option>
                                <option value="double_declining">Saldo Menurun (Double Declining)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl cursor-pointer transition-all">
                            Daftarkan Aset
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
