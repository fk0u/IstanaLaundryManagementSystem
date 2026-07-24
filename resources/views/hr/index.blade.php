<x-app-layout>
    <div x-data="{ 
        showCreateModal: false, 
        showEditModal: false, 
        editId: null, 
        editName: '', 
        editPosition: '', 
        editSalary: 0, 
        editActive: 1,
        openEdit(employee) {
            this.editId = employee.id;
            this.editName = employee.name;
            this.editPosition = employee.position;
            this.editSalary = employee.base_salary;
            this.editActive = employee.is_active;
            this.showEditModal = true;
        }
    }" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Manajemen Karyawan & SDM" :breadcrumbs="['Karyawan' => '/hr']" />
            <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                <span class="material-symbols-outlined">person_add</span>
                Tambah Karyawan
            </button>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif

        <x-card title="Daftar Staf Cabang & Penggajian">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">NIK</th>
                            <th class="py-3 px-4">Jabatan</th>
                            <th class="py-3 px-4">Gaji Pokok</th>
                            <th class="py-3 px-4">Tanggal Masuk</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200 text-sm">
                                    {{ $employee->name }}
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-500">
                                    {{ $employee->nik }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700 dark:text-slate-350">
                                    {{ $employee->position }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-850 dark:text-slate-150">
                                    Rp {{ number_format($employee->base_salary, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ \Carbon\Carbon::parse($employee->joined_at)->format('d M Y') }}
                                </td>
                                <td class="py-4 px-4">
                                    @if ($employee->is_active)
                                        <x-badge type="success">Aktif</x-badge>
                                    @else
                                        <x-badge type="gray">Nonaktif</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <button @click="openEdit({{ $employee->toJson() }})" class="p-1.5 text-slate-500 hover:text-primary transition-colors cursor-pointer" title="Edit">
                                        <span class="material-symbols-outlined text-base">edit</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">Belum ada data karyawan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $employees->links() }}
            </div>
        </x-card>

        <!-- Custom Create Employee Modal -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Tambah Karyawan Baru</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form action="{{ route('hr.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="flex flex-col gap-1.5">
                        <label for="name" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required placeholder="Contoh: Andi Wijaya..."
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="nik" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor Induk Karyawan (NIK)</label>
                        <input type="text" id="nik" name="nik" required placeholder="Contoh: NIK-SMD-001..."
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="position" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jabatan / Posisi</label>
                            <input type="text" id="position" name="position" required placeholder="Contoh: Kasir, Operator..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="base_salary" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gaji Pokok (Rp)</label>
                            <input type="number" id="base_salary" name="base_salary" required min="0" placeholder="Contoh: 3500000..."
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="joined_at" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal Masuk Kerja</label>
                        <input type="date" id="joined_at" name="joined_at" required
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl cursor-pointer transition-all">
                            Daftarkan Karyawan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Custom Edit Employee Modal -->
        <div x-show="showEditModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showEditModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Edit Data Karyawan</h3>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form :action="'/hr/' + editId" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="flex flex-col gap-1.5">
                        <label for="edit_name" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</label>
                        <input type="text" id="edit_name" name="name" x-model="editName" required
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label for="edit_position" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jabatan / Posisi</label>
                            <input type="text" id="edit_position" name="position" x-model="editPosition" required
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label for="edit_salary" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Gaji Pokok (Rp)</label>
                            <input type="number" id="edit_salary" name="base_salary" x-model="editSalary" required min="0"
                                   class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="edit_active" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Keaktifan</label>
                        <select id="edit_active" name="is_active" x-model="editActive" required
                                class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showEditModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
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
