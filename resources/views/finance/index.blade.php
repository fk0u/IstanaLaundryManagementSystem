<x-app-layout>
    <div x-data="{ showCreateModal: false }" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Bagan Akun Keuangan (Chart of Accounts)" :breadcrumbs="['Keuangan' => '/finance']" />
            <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                <span class="material-symbols-outlined">add_card</span>
                Tambah Akun COA
            </button>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif

        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-4" />
        @endif

        <x-card title="Bagan Akun Akuntansi (COA)">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nomor Akun (Code)</th>
                            <th class="py-3 px-4">Nama Akun</th>
                            <th class="py-3 px-4">Tipe Akun</th>
                            <th class="py-3 px-4">Saldo Normal</th>
                            <th class="py-3 px-4">Sistem</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($coas as $coa)
                            @php
                                $padding = ($coa->level - 1) * 16;
                                $canDelete = !$coa->is_system && $coa->children()->count() == 0;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $coa->code }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-800 dark:text-slate-200 text-sm" style="padding-left: {{ 16 + $padding }}px">
                                    @if ($coa->level > 1)
                                        <span class="text-slate-400 font-normal mr-1">└─</span>
                                    @endif
                                    <span class="{{ $coa->level === 1 ? 'font-extrabold text-primary' : 'font-semibold' }}">
                                        {{ $coa->name }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-650 dark:text-slate-400 uppercase font-bold text-[10px]">
                                    {{ $coa->type }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 font-bold uppercase text-[10px]">
                                    {{ $coa->normal_balance }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($coa->is_system)
                                        <x-badge type="info">System</x-badge>
                                    @else
                                        <x-badge type="gray">User</x-badge>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($coa->is_active)
                                        <x-badge type="success">Aktif</x-badge>
                                    @else
                                        <x-badge type="gray">Nonaktif</x-badge>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if ($canDelete)
                                        <form action="{{ route('finance.destroy', $coa->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun COA ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 text-slate-500 hover:text-red-500 transition-colors cursor-pointer" title="Hapus Akun">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-700 material-symbols-outlined text-base cursor-not-allowed" title="Akun sistem/memiliki sub-akun tidak dapat dihapus">lock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada bagan akun yang di-seed.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $coas->links() }}
            </div>
        </x-card>

        <!-- Custom Create COA Modal -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Tambah Akun Akuntansi (COA) Baru</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form action="{{ route('finance.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="flex flex-col gap-1.5">
                        <label for="code" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor / Kode Akun (Harus Unik)</label>
                        <input type="text" id="code" name="code" required placeholder="Contoh: 11130..."
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="name" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Akun Keuangan</label>
                        <input type="text" id="name" name="name" required placeholder="Contoh: Kas Kecil Cabang..."
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="type" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tipe Klasifikasi Akun</label>
                            <select id="type" name="type" required
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="asset">Aset (Asset)</option>
                                <option value="liability">Liabilitas (Liability)</option>
                                <option value="equity">Ekuitas (Equity)</option>
                                <option value="revenue">Pendapatan (Revenue)</option>
                                <option value="expense">Beban (Expense)</option>
                            </select>
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="normal_balance" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Saldo Normal</label>
                            <select id="normal_balance" name="normal_balance" required
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <option value="debit">Debit</option>
                                <option value="credit">Kredit</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="parent_id" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Akun Induk (Parent Account - Opsional)</label>
                        <select id="parent_id" name="parent_id"
                                class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                            <option value="">-- Tanpa Induk (Level 1) --</option>
                            @foreach ($allCoas as $parentCoa)
                                <option value="{{ $parentCoa->id }}">[{{ $parentCoa->code }}] {{ $parentCoa->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl cursor-pointer transition-all">
                            Buat Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
