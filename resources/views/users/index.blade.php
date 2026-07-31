<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6" x-data="{ showAddUser: false, editUser: null, resetUser: null }">
        <x-page-header title="Manajemen Pengguna (Staf & Hak Akses)" :breadcrumbs="['Pengguna' => route('users.index')]" />

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-2" />
        @endif

        <!-- Filter Bar & Action -->
        <div class="flex flex-wrap justify-between items-center gap-3">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama / email..." class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 font-semibold">
                
                <select name="branch_id" onchange="this.form.submit()" class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold px-2">
                    <option value="">Semua Cabang</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchFilter == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>

                <select name="role" onchange="this.form.submit()" class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold px-2">
                    <option value="">Semua Role</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ $roleFilter == $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="h-9 px-3 bg-slate-800 text-white text-xs font-bold rounded-xl flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">search</span> Cari
                </button>
            </form>

            <button type="button" @click="showAddUser = true" class="btn-touch px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-base">person_add</span> Tambah Pengguna Staf
            </button>
        </div>

        <x-card title="Daftar Pengguna Sistem">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Nama & Email</th>
                            <th class="py-2.5 px-3">Role / Hak Akses</th>
                            <th class="py-2.5 px-3">Cabang Operasional</th>
                            <th class="py-2.5 px-3 text-center">Status</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($users as $u)
                            @php
                                $roleName = $u->roles->first()?->name ?? 'Belum Ada Role';
                                $roleBadge = match($roleName) {
                                    'Owner', 'Developer' => 'primary',
                                    'Super_Admin', 'Branch_Admin' => 'success',
                                    'Finance' => 'warning',
                                    'Cashier' => 'info',
                                    default => 'gray',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $u->name }}</span>
                                    <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                        <span class="text-2xs text-slate-400 font-mono">{{ $u->email }}</span>
                                        @if($u->employee)
                                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/40 dark:text-sky-400" title="Tersinkronisasi ke HR Employees">
                                                <span class="material-symbols-outlined text-[12px]">badge</span> {{ $u->employee->nik }} ({{ $u->employee->position }})
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-3">
                                    <x-badge :type="$roleBadge">{{ $roleName }}</x-badge>
                                    @if($u->employee)
                                        <span class="block text-2xs font-mono font-bold text-emerald-600 mt-1">
                                            Gaji: Rp {{ number_format($u->employee->base_salary, 0, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-xs text-primary">store</span>
                                        {{ $u->branch?->name ?? 'Semua Cabang' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    @if($u->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-2xs font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-2xs font-bold text-rose-600 bg-rose-50 dark:bg-rose-950/20">Non-Aktif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right flex items-center justify-end gap-1.5">
                                    <a href="/hr" class="px-2 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-2xs font-bold flex items-center gap-0.5" title="Lihat Profil & Payroll HR">
                                        <span class="material-symbols-outlined text-xs">payments</span> HR
                                    </a>
                                    <button type="button" @click="editUser = { id: {{ $u->id }}, name: '{{ addslashes($u->name) }}', email: '{{ addslashes($u->email) }}', branch_id: '{{ $u->branch_id }}', role: '{{ $roleName }}', is_active: {{ $u->is_active ? 1 : 0 }} }" 
                                            class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-2xs font-bold flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-xs">edit</span> Edit
                                    </button>
                                    <button type="button" @click="resetUser = { id: {{ $u->id }}, name: '{{ addslashes($u->name) }}' }" 
                                            class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-2xs font-bold flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-xs">lock_reset</span> Reset
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">Pengguna tidak ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </x-card>

        <!-- Add User Modal -->
        <div x-show="showAddUser" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Tambah Pengguna Staf Baru</h3>
                    <button type="button" @click="showAddUser = false" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form action="{{ route('users.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Nama Lengkap</label>
                        <input type="text" name="name" required placeholder="Nama staf..." class="w-full h-9 px-3 rounded-xl border text-xs">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Email Login</label>
                        <input type="email" name="email" required placeholder="kasir.smd01@istanalaundry.com..." class="w-full h-9 px-3 rounded-xl border text-xs">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Password</label>
                        <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter..." class="w-full h-9 px-3 rounded-xl border text-xs">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Cabang</label>
                            <select name="branch_id" required class="w-full h-9 px-2 rounded-xl border text-xs">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Role / Akses</label>
                            <select name="role" required class="w-full h-9 px-2 rounded-xl border text-xs">
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5">Simpan Pengguna</button>
                </form>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div x-show="editUser" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Edit Data Pengguna</h3>
                    <button type="button" @click="editUser = null" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                </div>
                <template x-if="editUser">
                    <form :action="'/users/' + editUser.id" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Nama Lengkap</label>
                            <input type="text" name="name" x-model="editUser.name" required class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Email Login</label>
                            <input type="email" name="email" x-model="editUser.email" required class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Cabang</label>
                                <select name="branch_id" x-model="editUser.branch_id" required class="w-full h-9 px-2 rounded-xl border text-xs">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Role / Akses</label>
                                <select name="role" x-model="editUser.role" required class="w-full h-9 px-2 rounded-xl border text-xs">
                                    @foreach($roles as $r)
                                        <option value="{{ $r->name }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Status Akun</label>
                            <select name="is_active" x-model="editUser.is_active" required class="w-full h-9 px-2 rounded-xl border text-xs">
                                <option value="1">Aktif</option>
                                <option value="0">Non-Aktif (Blokir)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5">Simpan Perubahan</button>
                    </form>
                </template>
            </div>
        </div>

        <!-- Reset Password Modal -->
        <div x-show="resetUser" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Reset Password Staf</h3>
                    <button type="button" @click="resetUser = null" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                </div>
                <template x-if="resetUser">
                    <form :action="'/users/' + resetUser.id + '/reset-password'" method="POST" class="space-y-3">
                        @csrf
                        <p class="text-xs text-slate-500">Reset password baru untuk <strong class="text-slate-800 dark:text-slate-200" x-text="resetUser.name"></strong>:</p>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Password Baru</label>
                            <input type="password" name="password" required minlength="8" placeholder="Password baru..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi password baru..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <button type="submit" class="btn-touch w-full bg-amber-600 text-white font-bold text-xs rounded-xl py-2.5">Update Password</button>
                    </form>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>
