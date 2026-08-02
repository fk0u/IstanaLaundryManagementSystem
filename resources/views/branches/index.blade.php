<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6" x-data="{
        showAddBranch: false,
        activeEditBranch: null,
        activeDetailBranch: null
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
                <button type="button" @click="showAddBranch = true" class="btn-touch px-4 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-md shadow-primary/20 cursor-pointer">
                    <span class="material-symbols-outlined text-lg">add_location_alt</span> Tambah Cabang Baru
                </button>
            </div>
            
            <form method="GET" action="{{ route('branches.index') }}" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama / kode / alamat..." class="h-10 px-3.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs w-48 md:w-64 focus:border-primary outline-none">
                <button type="submit" class="h-10 px-4 bg-slate-900 dark:bg-slate-800 text-white rounded-xl text-xs font-bold flex items-center gap-1 cursor-pointer">
                    <span class="material-symbols-outlined text-base">search</span> Cari
                </button>
            </form>
        </div>

        <!-- Executive KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-950/30 text-purple-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">receipt_long</span>
                </div>
                <div>
                    <p class="text-2xs font-extrabold uppercase text-slate-400">Total Nota Transaksi</p>
                    <p class="text-xl font-black text-purple-600">{{ number_format($branches->sum('orders_count')) }} Nota</p>
                </div>
            </div>
        </div>

        <!-- Branch List Table Card -->
        <x-card title="Daftar Cabang Laundry & Scope Operasional">
            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider text-left">
                            <th class="py-3.5 px-4">Kode & Nama Cabang</th>
                            <th class="py-3.5 px-4">Alamat & GPS Location</th>
                            <th class="py-3.5 px-4 text-center">Staf HR</th>
                            <th class="py-3.5 px-4 text-center">Akun Login</th>
                            <th class="py-3.5 px-4 text-center">Total Transaksi</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi & Scope</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($branches as $branch)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-primary font-black text-xs flex items-center justify-center font-mono border border-orange-500/20">
                                            {{ $branch->code }}
                                        </div>
                                        <div>
                                            <span class="font-black text-sm text-slate-900 dark:text-slate-100 block">{{ $branch->name }}</span>
                                            <span class="text-[10px] text-slate-400 font-semibold">{{ $branch->email ?? 'No Email' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-300">
                                    <p class="font-medium truncate max-w-xs" title="{{ $branch->address }}">{{ $branch->address ?? '-' }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] font-bold text-slate-400">Telp: {{ $branch->phone ?? '-' }}</span>
                                        @if($branch->lat && $branch->lng)
                                            <a href="https://www.google.com/maps?q={{ $branch->lat }},{{ $branch->lng }}" target="_blank" class="inline-flex items-center gap-0.5 text-[10px] font-bold text-blue-500 hover:underline">
                                                <span class="material-symbols-outlined text-xs">location_on</span> Maps
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800">
                                        {{ $branch->employees_count }} Staf
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center font-bold text-slate-800 dark:text-slate-200">
                                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800">
                                        {{ $branch->users_count }} Akun
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                    {{ number_format($branch->orders_count) }} Nota
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($branch->is_active)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-2xs font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">
                                            ● Operasional Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-2xs font-extrabold bg-slate-100 text-slate-500">
                                            Non-Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        {{-- 1-Click Scope Switch Button for Super Users --}}
                                        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                                            <form action="{{ route('switch-branch') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                                                <button type="submit" class="px-2.5 py-1.5 bg-primary/10 hover:bg-primary text-primary hover:text-white rounded-xl text-2xs font-extrabold transition-all cursor-pointer border border-primary/20" title="Beralih scope ke cabang ini">
                                                    Scope
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" @click="activeEditBranch = @js($branch)" class="p-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 hover:text-primary hover:bg-orange-50 transition-colors cursor-pointer" title="Edit Cabang">
                                            <span class="material-symbols-outlined text-base">edit</span>
                                        </button>
                                        <form action="{{ route('branches.toggle-active', $branch->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-xl {{ $branch->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100' }} transition-colors cursor-pointer" title="{{ $branch->is_active ? 'Nonaktifkan Cabang' : 'Aktifkan Cabang' }}">
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
        <div x-show="showAddBranch" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200 dark:border-slate-800">
                <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-base text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">add_location_alt</span>
                        Tambah Cabang Operasional Baru
                    </h3>
                    <button type="button" @click="showAddBranch = false" class="text-slate-400 hover:text-slate-600 cursor-pointer"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form action="{{ route('branches.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kode Cabang</label>
                            <input type="text" name="code" required placeholder="LMG / SUT..." class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-mono font-bold uppercase focus:border-primary outline-none">
                        </div>
                        <div class="col-span-2">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Cabang</label>
                            <input type="text" name="name" required placeholder="Cabang Lambung Mangkurat..." class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</label>
                        <input type="text" name="address" placeholder="Jl. Lambung Mangkurat No. 45, Samarinda..." class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor Telepon</label>
                            <input type="text" name="phone" placeholder="0811xxxxxxxx" class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary outline-none">
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email Cabang</label>
                            <input type="email" name="email" placeholder="lambung@istanalaundry.com" class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary outline-none">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showAddBranch = false" class="px-5 py-3 rounded-xl border text-xs font-bold">Batal</button>
                        <button type="submit" class="px-6 py-3 bg-primary text-white font-bold text-xs rounded-xl shadow-md cursor-pointer">Simpan Cabang Baru</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Branch Modal -->
        <template x-if="activeEditBranch">
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak @keydown.escape.window="activeEditBranch = null">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="font-bold text-base text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary">edit_location</span>
                            Edit Informasi Cabang
                        </h3>
                        <button type="button" @click="activeEditBranch = null" class="text-slate-400 hover:text-slate-600 cursor-pointer"><span class="material-symbols-outlined">close</span></button>
                    </div>
                    <form :action="'/branches/' + activeEditBranch.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kode Cabang</label>
                                <input type="text" name="code" x-model="activeEditBranch.code" required class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-mono font-bold uppercase focus:border-primary outline-none">
                            </div>
                            <div class="col-span-2">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Cabang</label>
                                <input type="text" name="name" x-model="activeEditBranch.name" required class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</label>
                            <input type="text" name="address" x-model="activeEditBranch.address" class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor Telepon</label>
                                <input type="text" name="phone" x-model="activeEditBranch.phone" class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary outline-none">
                            </div>
                            <div>
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Email Cabang</label>
                                <input type="email" name="email" x-model="activeEditBranch.email" class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Operasional</label>
                            <select name="is_active" x-model="activeEditBranch.is_active" class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold focus:border-primary outline-none">
                                <option :value="1">● Operasional Aktif</option>
                                <option :value="0">Non-Aktif</option>
                            </select>
                        </div>
                        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="activeEditBranch = null" class="px-5 py-3 rounded-xl border text-xs font-bold">Batal</button>
                            <button type="submit" class="px-6 py-3 bg-primary text-white font-bold text-xs rounded-xl shadow-md cursor-pointer">Simpan Perubahan Cabang</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-app-layout>
