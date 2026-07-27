<x-app-layout>
    @php
        $typeLabels = [
            'kilogram' => 'Per Kg',
            'satuan'   => 'Per Pcs',
            'kategori' => 'Per Kategori',
        ];
        $typeBadge = [
            'kilogram' => 'info',
            'satuan'   => 'primary',
            'kategori' => 'success',
        ];
    @endphp
    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        showCreateBranchPrices: false,
        showEditBranchPrices: false,
        edit: {
            id: null, name: '', type: 'kilogram', unit: 'kg',
            base_price: 0, est_duration_hours: 24, description: '', is_active: true,
            branch_prices: {}
        },
        openEdit(service) {
            this.edit.id = service.id;
            this.edit.name = service.name;
            this.edit.type = service.type;
            this.edit.unit = service.unit;
            this.edit.base_price = Number(service.base_price);
            this.edit.est_duration_hours = Number(service.est_duration_hours);
            this.edit.description = service.description || '';
            this.edit.is_active = !!service.is_active;
            this.edit.branch_prices = {};
            if (Array.isArray(service.branch_prices)) {
                service.branch_prices.forEach(bp => {
                    this.edit.branch_prices[String(bp.branch_id)] = Number(bp.price);
                });
            }
            this.showEditBranchPrices = false;
            this.showEditModal = true;
        },
        resetCreateForm() {
            this.showCreateBranchPrices = false;
        }
    }" class="flex flex-col gap-4 md:gap-6">
        <x-page-header title="Jenis Layanan Laundry" :breadcrumbs="['Jenis Layanan' => '/services']">
            <x-slot name="actions">
                <button @click="showCreateModal = true; resetCreateForm();"
                        class="bg-primary hover:bg-primary-hover text-white rounded-xl font-bold flex items-center justify-center gap-1.5 px-4 py-2.5 text-sm transition-all active:scale-95 shadow-md shadow-primary/20 cursor-pointer">
                    <span class="material-symbols-outlined text-base">add</span>
                    Tambah Layanan
                </button>
            </x-slot>
        </x-page-header>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif
        @if (session('error'))
            <x-alert type="error" :message="session('error')" class="mb-2" />
        @endif

        <x-card>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Layanan</th>
                            <th class="py-3 px-4">Tipe</th>
                            <th class="py-3 px-4">Satuan</th>
                            <th class="py-3 px-4 text-right">Harga Default</th>
                            <th class="py-3 px-4 text-center">Est. Selesai</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($services as $service)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4">
                                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200 block">{{ $service->name }}</span>
                                    @if($service->description)
                                        <span class="text-[10px] text-slate-400 block mt-0.5 max-w-xs truncate">{{ $service->description }}</span>
                                    @endif
                                    @if($service->branchPrices && $service->branchPrices->count() > 0)
                                        <span class="inline-block mt-1 text-[9px] font-bold text-primary bg-orange-50 dark:bg-orange-950/30 rounded-md px-2 py-0.5">
                                            Override {{ $service->branchPrices->count() }} cabang
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <x-badge :type="$typeBadge[$service->type] ?? 'gray'">{{ $typeLabels[$service->type] ?? $service->type }}</x-badge>
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400 font-semibold">
                                    / {{ $service->unit }}
                                </td>
                                <td class="py-4 px-4 text-right font-bold text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($service->base_price, 0, ',', '.') }}
                                    @if($service->branchPrices && $service->branchPrices->count() > 0)
                                        <div class="text-[9px] text-slate-400 mt-0.5 space-y-0.5 text-right">
                                            @foreach($service->branchPrices as $bp)
                                                <div>[{{ $bp->branch->code ?? '?' }}] Rp {{ number_format($bp->price, 0, ',', '.') }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-center text-slate-600 dark:text-slate-400 font-semibold">
                                    {{ $service->est_duration_hours }} jam
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($service->is_active)
                                        <x-badge type="success">Aktif</x-badge>
                                    @else
                                        <x-badge type="gray">Nonaktif</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('services.toggle-active', $service->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="p-1.5 rounded-lg transition-colors cursor-pointer {{ $service->is_active ? 'text-slate-400 hover:text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/20' : 'text-emerald-500 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' }}"
                                                    title="{{ $service->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <span class="material-symbols-outlined text-base">power_settings_new</span>
                                            </button>
                                        </form>
                                        <button type="button"
                                                @click='openEdit(@json($service->append(["branch_prices" => $service->branchPrices->makeHidden(["created_at","updated_at","service_id","is_active"]) ])))'
                                                class="p-1.5 text-slate-500 hover:text-primary hover:bg-orange-50 dark:hover:bg-orange-950/20 transition-colors rounded-lg cursor-pointer"
                                                title="Edit">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada jenis layanan terdaftar. Klik tombol "Tambah Layanan" untuk membuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $services->links() }}
            </div>
        </x-card>

        <!-- ========== CREATE MODAL ========== -->
        <div x-show="showCreateModal"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-t-2xl sm:rounded-2xl max-w-2xl w-full p-5 shadow-2xl transition-all duration-300 max-h-[90vh] overflow-y-auto">
                <div class="sheet-handle sm:hidden"></div>
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Tambah Jenis Layanan</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">Master data layanan (global untuk semua cabang).</p>
                    </div>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('services.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Nama Layanan</label>
                            <input type="text" name="name" required maxlength="255" placeholder="Contoh: Cuci Kiloan Reguler"
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Kategori Tipe</label>
                            <select name="type" required
                                    class="w-full h-10 px-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="kilogram">Per Kilogram (kg)</option>
                                <option value="satuan">Per Satuan / Pcs</option>
                                <option value="kategori">Per Kategori Item</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Satuan</label>
                            <input type="text" name="unit" required maxlength="10" placeholder="kg / pcs / set"
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Harga Default (Rp)</label>
                            <input type="number" name="base_price" required min="0" step="100" placeholder="7000"
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-primary focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Est. Durasi (jam)</label>
                            <input type="number" name="est_duration_hours" required min="1" value="24"
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Deskripsi (Opsional)</label>
                            <textarea name="description" rows="2" placeholder="Penjelasan singkat layanan..."
                                      class="w-full p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
                        </div>

                        <div class="sm:col-span-2 flex items-center gap-2 pt-1">
                            <input type="checkbox" id="cs_active" name="is_active" value="1" checked
                                   class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/30">
                            <label for="cs_active" class="text-xs font-semibold text-slate-600 dark:text-slate-300">Layanan aktif (bisa dipilih di POS)</label>
                        </div>
                    </div>

                    <!-- Branch Price Override (Collapsible) -->
                    <div class="mt-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateBranchPrices = !showCreateBranchPrices"
                                class="flex items-center justify-between w-full text-left">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-base text-primary">tune</span>
                                Override Harga Per Cabang (Opsional)
                            </span>
                            <span class="material-symbols-outlined text-sm text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': showCreateBranchPrices }">expand_more</span>
                        </button>
                        <div x-show="showCreateBranchPrices" x-collapse class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                            @foreach($branches as $b)
                                <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-2.5 bg-slate-50/50 dark:bg-slate-800/30">
                                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">
                                        [{{ $b->code }}] {{ $b->name }}
                                    </label>
                                    <input type="number" name="branch_prices[{{ $b->id }}]" min="0" step="100" placeholder="Kosong = pakai default"
                                           class="w-full h-9 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800 mt-4">
                        <button type="button" @click="showCreateModal = false"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl shadow-md shadow-primary/20 cursor-pointer transition-all active:scale-[0.98]">
                            Simpan Layanan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========== EDIT MODAL ========== -->
        <div x-show="showEditModal"
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showEditModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-t-2xl sm:rounded-2xl max-w-2xl w-full p-5 shadow-2xl transition-all duration-300 max-h-[90vh] overflow-y-auto">
                <div class="sheet-handle sm:hidden"></div>
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Edit Layanan</h3>
                        <p class="text-[10px] text-slate-400 mt-0.5">Perubahan harga otomatis dicatat di riwayat harga.</p>
                    </div>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form :action="`/services/${edit.id}`" method="POST" class="space-y-3.5">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Nama Layanan</label>
                            <input type="text" name="name" required maxlength="255" x-model="edit.name"
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Kategori Tipe</label>
                            <select name="type" required x-model="edit.type"
                                    class="w-full h-10 px-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="kilogram">Per Kilogram (kg)</option>
                                <option value="satuan">Per Satuan / Pcs</option>
                                <option value="kategori">Per Kategori Item</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Satuan</label>
                            <input type="text" name="unit" required maxlength="10" x-model="edit.unit"
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Harga Default (Rp)</label>
                            <input type="number" name="base_price" required min="0" step="100" x-model.number="edit.base_price"
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-primary focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Est. Durasi (jam)</label>
                            <input type="number" name="est_duration_hours" required min="1" x-model.number="edit.est_duration_hours"
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Deskripsi (Opsional)</label>
                            <textarea name="description" rows="2" x-model="edit.description"
                                      class="w-full p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
                        </div>

                        <div class="sm:col-span-2 flex items-center gap-2 pt-1">
                            <input type="checkbox" id="es_active" name="is_active" value="1" x-model.boolean="edit.is_active"
                                   class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/30">
                            <label for="es_active" class="text-xs font-semibold text-slate-600 dark:text-slate-300">Layanan aktif (bisa dipilih di POS)</label>
                        </div>
                    </div>

                    <!-- Branch Price Override (Collapsible) -->
                    <div class="mt-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showEditBranchPrices = !showEditBranchPrices"
                                class="flex items-center justify-between w-full text-left">
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-base text-primary">tune</span>
                                Override Harga Per Cabang (Opsional)
                            </span>
                            <span class="material-symbols-outlined text-sm text-slate-400 transition-transform duration-200" :class="{ 'rotate-180': showEditBranchPrices }">expand_more</span>
                        </button>
                        <div x-show="showEditBranchPrices" x-collapse class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                            @foreach($branches as $b)
                                <div class="rounded-xl border border-slate-100 dark:border-slate-800 p-2.5 bg-slate-50/50 dark:bg-slate-800/30">
                                    <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">
                                        [{{ $b->code }}] {{ $b->name }}
                                    </label>
                                    <input type="number" :name="`branch_prices[{{ $b->id }}]`" min="0" step="100"
                                           placeholder="Kosong = hapus override / pakai default"
                                           :value="edit.branch_prices && edit.branch_prices['{{ $b->id }}'] != null ? edit.branch_prices['{{ $b->id }}'] : ''"
                                           class="w-full h-9 px-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800 mt-4">
                        <button type="button" @click="showEditModal = false"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl shadow-md shadow-primary/20 cursor-pointer transition-all active:scale-[0.98]">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
