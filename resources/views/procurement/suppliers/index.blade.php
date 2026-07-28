<x-app-layout>
    <div x-data="{
            showCreateModal: false,
            showEditModal: false,
            editData: { id: '', name: '', phone: '', email: '', address: '', npwp: '', is_active: true },
            openEdit(supplier) {
                this.editData = {
                    id: supplier.id,
                    name: supplier.name,
                    phone: supplier.phone || '',
                    email: supplier.email || '',
                    address: supplier.address || '',
                    npwp: supplier.npwp || '',
                    is_active: supplier.is_active == true || supplier.is_active == 1
                };
                this.showEditModal = true;
            }
        }"
         class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Supplier" :breadcrumbs="['Pengadaan' => '#', 'Supplier' => '/procurement/suppliers']" />
            <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                <span class="material-symbols-outlined">add</span>
                Tambah Supplier
            </button>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-4" />
        @endif

        {{-- Search --}}
        <form method="GET" action="{{ route('procurement.suppliers.index') }}" class="flex gap-2">
            <div class="relative flex-1 max-w-md">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, telepon, atau email supplier..."
                       class="w-full h-11 pl-10 pr-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
            </div>
            <button type="submit" class="h-11 px-4 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold cursor-pointer transition-all">
                Cari
            </button>
            @if (request('search'))
                <a href="{{ route('procurement.suppliers.index') }}" class="h-11 px-4 border border-slate-200 dark:border-slate-800 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 flex items-center cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-850">
                    Reset
                </a>
            @endif
        </form>

        <x-card title="Daftar Supplier">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Supplier</th>
                            <th class="py-3 px-4">Kontak</th>
                            <th class="py-3 px-4">NPWP</th>
                            <th class="py-3 px-4 text-center">Jumlah PO</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($suppliers as $supplier)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">{{ $supplier->name }}</div>
                                    @if ($supplier->address)
                                        <div class="text-slate-400 text-[11px] mt-0.5 max-w-xs truncate">{{ $supplier->address }}</div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400">
                                    @if ($supplier->phone)
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[14px] text-slate-400">call</span>
                                            {{ $supplier->phone }}
                                        </div>
                                    @endif
                                    @if ($supplier->email)
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="material-symbols-outlined text-[14px] text-slate-400">mail</span>
                                            <span class="truncate max-w-[180px]">{{ $supplier->email }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-slate-500 font-mono">
                                    {{ $supplier->npwp ?: '-' }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span class="px-2 py-1 bg-slate-100 dark:bg-slate-800 rounded-md font-bold text-slate-600 dark:text-slate-300">
                                        {{ $supplier->purchase_orders_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if ($supplier->is_active)
                                        <x-badge type="success">Aktif</x-badge>
                                    @else
                                        <x-badge type="gray">Nonaktif</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="openEdit(@js($supplier))"
                                                class="px-2.5 py-1.5 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all">
                                            Edit
                                        </button>
                                        <form action="{{ route('procurement.suppliers.destroy', $supplier->id) }}" method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus supplier {{ $supplier->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-red-500 transition-colors cursor-pointer" title="Hapus Supplier">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    Belum ada supplier terdaftar. Klik <strong class="text-primary">"Tambah Supplier"</strong> untuk membuat baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $suppliers->links() }}
            </div>
        </x-card>

        {{-- ============================= MODAL CREATE ============================= --}}
        <div x-show="showCreateModal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Tambah Supplier Baru</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form action="{{ route('procurement.suppliers.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Supplier <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required placeholder="PT/CV/UD Nama Supplier"
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Telepon</label>
                            <input type="text" name="phone" placeholder="08xx / 021xxx"
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NPWP</label>
                            <input type="text" name="npwp" placeholder="00.000.000.0-000.000"
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email</label>
                        <input type="email" name="email" placeholder="supplier@email.com"
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat</label>
                        <textarea name="address" rows="3" placeholder="Alamat lengkap supplier"
                                  class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all resize-none"></textarea>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-primary focus:ring-primary">
                        <span class="text-xs text-slate-600 dark:text-slate-400">Supplier aktif (langsung bisa dipakai di Purchase Order)</span>
                    </label>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl cursor-pointer transition-all">
                            Simpan Supplier
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================= MODAL EDIT ============================= --}}
        <div x-show="showEditModal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showEditModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Edit Supplier</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form :action="'{{ route('procurement.suppliers.update', ':ID') }}'.replace(':ID', editData.id)" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Supplier <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required x-model="editData.name"
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Telepon</label>
                            <input type="text" name="phone" x-model="editData.phone"
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">NPWP</label>
                            <input type="text" name="npwp" x-model="editData.npwp"
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email</label>
                        <input type="email" name="email" x-model="editData.email"
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat</label>
                        <textarea name="address" rows="3" x-model="editData.address"
                                  class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all resize-none"></textarea>
                    </div>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" x-model="editData.is_active" class="rounded border-slate-300 text-primary focus:ring-primary">
                        <span class="text-xs text-slate-600 dark:text-slate-400">Supplier aktif</span>
                    </label>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showEditModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl cursor-pointer transition-all">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
