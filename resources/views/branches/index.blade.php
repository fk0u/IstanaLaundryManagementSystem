<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6" x-data="{
        showAddBranch: false,
        activeEditBranch: null
    }">
        <x-page-header title="Manajemen Cabang & Scope Operasional" :breadcrumbs="['Sistem' => '#', 'Manajemen Cabang' => route('branches.index')]" />

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-2" />
        @endif

        <!-- Action & Search Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex gap-2">
                <button type="button" @click="showAddBranch = true" class="btn-touch px-4 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-md">
                    <span class="material-symbols-outlined text-lg">add_location_alt</span> Tambah Cabang Baru
                </button>
            </div>
            
            <form method="GET" action="{{ route('branches.index') }}" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama / kode cabang..." class="h-9 px-3 rounded-xl border border-slate-200 text-xs w-48 md:w-64">
                <button type="submit" class="h-9 px-3 bg-slate-900 text-white rounded-xl text-xs font-bold flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">search</span> Cari
                </button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-orange-50 dark:bg-orange-950/30 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">store</span>
                </div>
                <div>
                    <p class="text-2xs font-extrabold uppercase text-slate-400">Total Cabang</p>
                    <p class="text-xl font-black text-slate-900 dark:text-slate-100">{{ $branches->count() }} Cabang</p>
                </div>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">check_circle</span>
                </div>
                <div>
                    <p class="text-2xs font-extrabold uppercase text-slate-400">Cabang Aktif</p>
                    <p class="text-xl font-black text-emerald-600">{{ $branches->where('is_active', true)->count() }} Cabang</p>
                </div>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-sky-50 dark:bg-sky-950/30 text-sky-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">badge</span>
                </div>
                <div>
                    <p class="text-2xs font-extrabold uppercase text-slate-400">Total Staf Terikat</p>
                    <p class="text-xl font-black text-sky-600">{{ $branches->sum('employees_count') }} Staf</p>
                </div>
            </div>
        </div>

        <!-- Branch List Table Card -->
        <x-card title="Daftar Cabang Laundry Operasional">
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider text-left">
                            <th class="py-3 px-4">Kode / Nama Cabang</th>
                            <th class="py-3 px-4">Alamat & Kontak</th>
                            <th class="py-3 px-4 text-center">Jumlah Staf (HR)</th>
                            <th class="py-3 px-4 text-center">Akun Login</th>
                            <th class="py-3 px-4 text-center">Total Nota</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($branches as $branch)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    <span class="font-black text-sm text-slate-900 dark:text-slate-100 block">{{ $branch->name }}</span>
                                    <span class="text-2xs font-mono font-bold text-primary">Kode: {{ $branch->code }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">
                                    <p class="font-medium truncate max-w-xs">{{ $branch->address ?? '-' }}</p>
                                    <p class="text-2xs text-slate-400 mt-0.5">Telp: {{ $branch->phone ?? '-' }} • Email: {{ $branch->email ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                                    {{ $branch->employees_count }} Staf
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                                    {{ $branch->users_count }} Akun
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-emerald-600">
                                    {{ number_format($branch->orders_count) }} Nota
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($branch->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-2xs font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">
                                            ● Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-2xs font-extrabold bg-slate-100 text-slate-500">
                                            Non-Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button" @click="activeEditBranch = @js($branch)" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 hover:text-primary hover:bg-orange-50 transition-colors" title="Edit Cabang">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                        <form action="{{ route('branches.toggle-active', $branch->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg {{ $branch->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} transition-colors" title="{{ $branch->is_active ? 'Nonaktifkan Cabang' : 'Aktifkan Cabang' }}">
                                                <span class="material-symbols-outlined text-base">{{ $branch->is_active ? 'power_settings_new' : 'check_circle' }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 font-sans">Belum ada data cabang terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <!-- Add Branch Modal -->
        <div x-show="showAddBranch" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Tambah Cabang Operasional Baru</h3>
                    <button type="button" @click="showAddBranch = false" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form action="{{ route('branches.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Kode Cabang</label>
                            <input type="text" name="code" required placeholder="LMB / SUT..." class="w-full h-9 px-3 rounded-xl border text-xs font-mono font-bold uppercase">
                        </div>
                        <div class="col-span-2">
                            <label class="text-2xs font-bold text-slate-400 uppercase">Nama Cabang</label>
                            <input type="text" name="name" required placeholder="Cabang Lambung Mangkurat..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Alamat Lengkap</label>
                        <input type="text" name="address" placeholder="Jl. Lambung Mangkurat No. 45..." class="w-full h-9 px-3 rounded-xl border text-xs">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Nomor Telepon</label>
                            <input type="text" name="phone" placeholder="0541-xxxxxxxx" class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Email Cabang</label>
                            <input type="email" name="email" placeholder="lambung@istanalaundry.com" class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                    </div>
                    <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5 shadow-md">Simpan Cabang Baru</button>
                </form>
            </div>
        </div>

        <!-- Edit Branch Modal -->
        <template x-if="activeEditBranch">
            <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak @keydown.escape.window="activeEditBranch = null">
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                    <div class="flex justify-between items-center pb-2 border-b">
                        <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Edit Informasi Cabang</h3>
                        <button type="button" @click="activeEditBranch = null" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                    </div>
                    <form :action="'/branches/' + activeEditBranch.id" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Kode Cabang</label>
                                <input type="text" name="code" x-model="activeEditBranch.code" required class="w-full h-9 px-3 rounded-xl border text-xs font-mono font-bold uppercase">
                            </div>
                            <div class="col-span-2">
                                <label class="text-2xs font-bold text-slate-400 uppercase">Nama Cabang</label>
                                <input type="text" name="name" x-model="activeEditBranch.name" required class="w-full h-9 px-3 rounded-xl border text-xs">
                            </div>
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Alamat Lengkap</label>
                            <input type="text" name="address" x-model="activeEditBranch.address" class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Nomor Telepon</label>
                                <input type="text" name="phone" x-model="activeEditBranch.phone" class="w-full h-9 px-3 rounded-xl border text-xs">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Email Cabang</label>
                                <input type="email" name="email" x-model="activeEditBranch.email" class="w-full h-9 px-3 rounded-xl border text-xs">
                            </div>
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Status Operasional</label>
                            <select name="is_active" x-model="activeEditBranch.is_active" class="w-full h-9 px-2 rounded-xl border text-xs font-semibold">
                                <option :value="1">● Aktif Operasional</option>
                                <option :value="0">Non-Aktif</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5 shadow-md">Simpan Perubahan Cabang</button>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
