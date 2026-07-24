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
    }" class="flex flex-col gap-6">
        <x-page-header title="Customer Relationship Management (CRM)" :breadcrumbs="['CRM' => '/customers']" />

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Customer List Table (8 cols) -->
            <div class="lg:col-span-8">
                <x-card title="Daftar Pelanggan">
                    <div class="overflow-x-auto">
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

                    <div class="mt-4">
                        {{ $customers->links() }}
                    </div>
                </x-card>
            </div>

            <!-- Register New Customer Form (4 cols) -->
            <div class="lg:col-span-4">
                <x-card title="Daftar Pelanggan Baru">
                    <form action="{{ route('customers.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="flex flex-col gap-1.5">
                            <label for="name" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
                            <input type="text" id="name" name="name" required placeholder="Masukkan nama pelanggan..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="phone" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor Handphone</label>
                            <input type="text" id="phone" name="phone" required placeholder="Contoh: 08123456789..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="email" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email (Opsional)</label>
                            <input type="email" id="email" name="email" placeholder="Contoh: pelanggan@gmail.com..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="address" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Tinggal</label>
                            <textarea id="address" name="address" placeholder="Tulis alamat rumah..." rows="3"
                                      class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
                        </div>

                        <button type="submit" class="w-full h-12 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-sm">
                            <span class="material-symbols-outlined">person_add</span>
                            Daftarkan Pelanggan
                        </button>
                    </form>
                </x-card>
            </div>
        </div>

        <!-- Custom Edit Customer Modal -->
        <div x-show="showEditModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showEditModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Edit Data Pelanggan</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form :action="'/customers/' + editId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="flex flex-col gap-1.5">
                        <label for="edit_name" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
                        <input type="text" id="edit_name" name="name" x-model="editName" required
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="edit_phone" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor Handphone</label>
                        <input type="text" id="edit_phone" name="phone" x-model="editPhone" required
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="edit_email" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email</label>
                        <input type="email" id="edit_email" name="email" x-model="editEmail"
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="edit_tier" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Loyalty Tier</label>
                            <select id="edit_tier" name="loyalty_tier" x-model="editTier" required
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="Bronze">Bronze</option>
                                <option value="Silver">Silver</option>
                                <option value="Gold">Gold</option>
                                <option value="Platinum">Platinum</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="edit_points" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Loyalty Points</label>
                            <input type="number" id="edit_points" name="loyalty_points" x-model="editPoints" required min="0"
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="edit_address" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat</label>
                        <textarea id="edit_address" name="address" x-model="editAddress" rows="3"
                                  class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
                    </div>

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
