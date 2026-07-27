<x-app-layout>
    <div x-data="{ 
        showEditModal: false, 
        editId: null, 
        editName: '', 
        editPhone: '', 
        editEmail: '', 
        editAddress: '', 
        editTier: 'Bronze', 
        editPoints: 0,
        openEdit(customer) {
            this.editId = customer.id;
            this.editName = customer.name;
            this.editPhone = customer.phone;
            this.editEmail = customer.email || '';
            this.editAddress = customer.address || '';
            this.editTier = customer.loyalty_tier;
            this.editPoints = customer.loyalty_points;
            this.showEditModal = true;
        }
    }" class="flex flex-col gap-4 md:gap-6">
        <x-page-header title="Customer Relationship Management (CRM)" :breadcrumbs="['CRM' => '/customers']" />

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif

        <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col sm:flex-row gap-2">
            <div class="flex-1 relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Cari nama, no HP, atau kode member..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-primary text-white rounded-xl font-semibold text-sm hover:bg-primary-hover transition-all active:scale-95 cursor-pointer">
                Cari
            </button>
            @if(request('q'))
                <a href="{{ route('customers.index') }}" class="px-5 py-2.5 text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 rounded-xl border border-slate-200 dark:border-slate-700 font-semibold text-sm transition-colors text-center">
                    Reset
                </a>
            @endif
        </form>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-6">
            <!-- Customer List Table / Mobile Cards (8 cols) -->
            <div class="lg:col-span-8">
                <x-card title="Daftar Pelanggan">
                    {{-- Desktop Table View --}}
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-3 px-4">Nama / Kode</th>
                                    <th class="py-3 px-4">Kontak</th>
                                    <th class="py-3 px-4">Loyalty Tier</th>
                                    <th class="py-3 px-4">Poin</th>
                                    <th class="py-3 px-4">Cabang</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                                @forelse($customers as $customer)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                        <td class="py-4 px-4">
                                            <span class="font-bold text-slate-800 dark:text-slate-200 block text-sm">{{ $customer->name }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono block mt-0.5">{{ $customer->member_code }}</span>
                                        </td>
                                        <td class="py-4 px-4 text-slate-600 dark:text-slate-400">
                                            <span class="block font-semibold">{{ $customer->phone }}</span>
                                            <span class="block text-[10px]">{{ $customer->email ?? '-' }}</span>
                                        </td>
                                        <td class="py-4 px-4">
                                            @php
                                                $tierColors = [
                                                    'Bronze' => 'gray',
                                                    'Silver' => 'info',
                                                    'Gold' => 'primary',
                                                    'Platinum' => 'success'
                                                ];
                                                $badgeType = $tierColors[$customer->loyalty_tier] ?? 'gray';
                                            @endphp
                                            <x-badge :type="$badgeType">{{ $customer->loyalty_tier }}</x-badge>
                                        </td>
                                        <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200">
                                            {{ number_format($customer->loyalty_points) }} pts
                                        </td>
                                        <td class="py-4 px-4 text-slate-500">
                                            {{ $customer->branch?->name ?? 'Global' }}
                                        </td>
                                        <td class="py-4 px-4">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="openEdit({{ $customer->toJson() }})" class="p-1.5 text-slate-500 hover:text-primary transition-colors cursor-pointer" title="Edit">
                                                    <span class="material-symbols-outlined text-base">edit</span>
                                                </button>
                                                <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-slate-500 hover:text-red-500 transition-colors cursor-pointer" title="Hapus">
                                                        <span class="material-symbols-outlined text-base">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 text-center text-slate-400">Belum ada pelanggan terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Card List View --}}
                    <div class="sm:hidden space-y-3">
                        @forelse($customers as $customer)
                            <div class="p-3.5 border border-slate-100 dark:border-slate-800 rounded-xl bg-slate-50/30 dark:bg-slate-800/30 flex flex-col gap-2">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span class="font-bold text-sm text-slate-800 dark:text-slate-200 block">{{ $customer->name }}</span>
                                        <span class="text-2xs text-slate-400 font-mono">{{ $customer->member_code }}</span>
                                    </div>
                                    @php
                                        $badgeType = ['Bronze' => 'gray', 'Silver' => 'info', 'Gold' => 'primary', 'Platinum' => 'success'][$customer->loyalty_tier] ?? 'gray';
                                    @endphp
                                    <x-badge :type="$badgeType">{{ $customer->loyalty_tier }}</x-badge>
                                </div>

                                <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-100 dark:border-slate-800">
                                    <span class="font-semibold text-slate-600 dark:text-slate-400 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm text-slate-400">call</span>
                                        {{ $customer->phone }}
                                    </span>
                                    <span class="font-bold text-primary">{{ number_format($customer->loyalty_points) }} pts</span>
                                </div>

                                <div class="flex justify-end gap-2 pt-1">
                                    <button @click="openEdit({{ $customer->toJson() }})" class="btn-touch px-3 py-1.5 bg-orange-50 dark:bg-slate-800 text-primary text-2xs font-bold rounded-lg flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">edit</span> Edit
                                    </button>
                                    <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Hapus pelanggan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-touch px-3 py-1.5 bg-rose-50 dark:bg-rose-950/20 text-rose-500 text-2xs font-bold rounded-lg flex items-center gap-1">
                                            <span class="material-symbols-outlined text-sm">delete</span> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-slate-400 text-xs">Belum ada pelanggan terdaftar.</div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $customers->links() }}
                    </div>
                </x-card>
            </div>

            <!-- Register New Customer Form (4 cols) -->
            <div class="lg:col-span-4">
                <x-card title="Daftar Pelanggan Baru">
                    <form action="{{ route('customers.store') }}" method="POST" class="space-y-3.5">
                        @csrf
                        <div class="flex flex-col gap-1">
                            <label for="name" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
                            <input type="text" id="name" name="name" required placeholder="Masukkan nama..."
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="phone" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Nomor HP (WhatsApp)</label>
                            <input type="text" id="phone" name="phone" required placeholder="08123456789..."
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="email" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Email (Opsional)</label>
                            <input type="email" id="email" name="email" placeholder="pelanggan@gmail.com..."
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="address" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Alamat</label>
                            <textarea id="address" name="address" placeholder="Tulis alamat rumah..." rows="2"
                                      class="w-full p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
                        </div>

                        <button type="submit" class="btn-touch w-full bg-primary hover:bg-primary-hover text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-primary/20">
                            <span class="material-symbols-outlined text-base">person_add</span>
                            Daftarkan Pelanggan
                        </button>
                    </form>
                </x-card>
            </div>
        </div>

        <!-- Custom Edit Customer Modal -->
        <div x-show="showEditModal" 
             class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showEditModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-t-2xl sm:rounded-2xl max-w-lg w-full p-5 shadow-2xl transition-all duration-300 max-h-[90vh] overflow-y-auto">
                
                <div class="sheet-handle sm:hidden"></div>
                <div class="flex justify-between items-center mb-4 pb-2 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Edit Data Pelanggan</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form :action="'/customers/' + editId" method="POST" class="space-y-3.5">
                    @csrf
                    @method('PUT')
                    
                    <div class="flex flex-col gap-1">
                        <label for="edit_name" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
                        <input type="text" id="edit_name" name="name" x-model="editName" required
                               class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary outline-none">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="edit_phone" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Nomor HP</label>
                        <input type="text" id="edit_phone" name="phone" x-model="editPhone" required
                               class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary outline-none">
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="edit_email" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Email</label>
                        <input type="email" id="edit_email" name="email" x-model="editEmail"
                               class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label for="edit_tier" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Tier</label>
                            <select id="edit_tier" name="loyalty_tier" x-model="editTier" required
                                    class="w-full h-10 px-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary outline-none">
                                <option value="Bronze">Bronze</option>
                                <option value="Silver">Silver</option>
                                <option value="Gold">Gold</option>
                                <option value="Platinum">Platinum</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="edit_points" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Poin</label>
                            <input type="number" id="edit_points" name="loyalty_points" x-model="editPoints" required min="0"
                                   class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-primary focus:border-primary outline-none">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="edit_address" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Alamat</label>
                        <textarea id="edit_address" name="address" x-model="editAddress" rows="2"
                                  class="w-full p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs focus:border-primary outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showEditModal = false"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl shadow-md shadow-primary/20">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
