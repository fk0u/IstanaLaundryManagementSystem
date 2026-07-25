<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6" x-data="{ showAddEmployee: false, showAddPayroll: false }">
        <x-page-header title="HR & Penggajian (Payroll)" :breadcrumbs="['HR & Payroll' => '/hr']" />

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-2" />
        @endif

        <!-- Action Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex gap-2">
                <button type="button" @click="showAddEmployee = true" class="btn-touch px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-base">person_add</span> Tambah Karyawan
                </button>
                <button type="button" @click="showAddPayroll = true" class="btn-touch px-4 py-2 bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-base">payments</span> Generate Payroll
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Employees Table (7 cols) -->
            <div class="lg:col-span-7">
                <x-card title="Daftar Karyawan">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">NIK / Nama</th>
                                    <th class="py-2.5 px-3">Jabatan</th>
                                    <th class="py-2.5 px-3">Cabang</th>
                                    <th class="py-2.5 px-3 text-right">Gaji Pokok</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                                @forelse($employees as $emp)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                        <td class="py-3 px-3">
                                            <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $emp->name }}</span>
                                            <span class="text-2xs text-slate-400 font-mono">{{ $emp->nik }}</span>
                                        </td>
                                        <td class="py-3 px-3 text-slate-600 dark:text-slate-400 font-medium">
                                            {{ $emp->position }}
                                        </td>
                                        <td class="py-3 px-3 text-slate-500">
                                            {{ $emp->branch?->name ?? 'Utama' }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                            Rp {{ number_format($emp->base_salary, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-400">Belum ada karyawan terdaftar.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            <!-- Payroll History & Slip Gaji (5 cols) -->
            <div class="lg:col-span-5">
                <x-card title="Riwayat Payroll Diproses">
                    <div class="space-y-3 max-h-[400px] overflow-y-auto pr-1">
                        @forelse($payrolls as $pr)
                            <div class="p-3 border border-slate-100 dark:border-slate-800 rounded-xl bg-slate-50/40 dark:bg-slate-900/40 flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200 block">
                                        Periode {{ date('F', mktime(0, 0, 0, $pr->month, 10)) }} {{ $pr->year }}
                                    </span>
                                    <span class="text-2xs text-slate-400">
                                        Cabang: {{ $pr->branch?->name }} • Status: <span class="uppercase font-bold text-emerald-600">{{ $pr->status }}</span>
                                    </span>
                                </div>
                                <div class="flex gap-1">
                                    @foreach($pr->payrollItems as $pItem)
                                        <a href="{{ route('hr.payslip', $pItem->id) }}" target="_blank" class="p-1.5 text-xs text-primary hover:bg-orange-50 rounded-lg" title="Slip Gaji {{ $pItem->employee?->name }}">
                                            <span class="material-symbols-outlined text-base">badge</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-slate-400 text-xs">Belum ada data payroll diproses.</div>
                        @endforelse
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Add Employee Modal -->
        <div x-show="showAddEmployee" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Tambah Karyawan Baru</h3>
                    <button type="button" @click="showAddEmployee = false" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form action="{{ route('hr.employees.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">NIK</label>
                        <input type="text" name="nik" required placeholder="NIK Karyawan..." class="w-full h-9 px-3 rounded-xl border text-xs">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Nama Lengkap</label>
                        <input type="text" name="name" required placeholder="Nama Karyawan..." class="w-full h-9 px-3 rounded-xl border text-xs">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Jabatan</label>
                        <input type="text" name="position" required placeholder="Kasir / Operator / Admin..." class="w-full h-9 px-3 rounded-xl border text-xs">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Gaji Pokok (Rp)</label>
                        <input type="number" name="base_salary" required placeholder="3000000..." class="w-full h-9 px-3 rounded-xl border text-xs font-bold">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Cabang</label>
                        <select name="branch_id" required class="w-full h-9 px-2 rounded-xl border text-xs">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5">Simpan Karyawan</button>
                </form>
            </div>
        </div>

        <!-- Add Payroll Modal -->
        <div x-show="showAddPayroll" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Generate Payroll Periode</h3>
                    <button type="button" @click="showAddPayroll = false" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form action="{{ route('hr.payrolls.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Bulan</label>
                        <select name="month" required class="w-full h-9 px-2 rounded-xl border text-xs">
                            @foreach([1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'] as $mNum => $mName)
                                <option value="{{ $mNum }}" {{ date('n') == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Tahun</label>
                        <input type="number" name="year" value="{{ date('Y') }}" required class="w-full h-9 px-3 rounded-xl border text-xs font-bold">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Cabang</label>
                        <select name="branch_id" required class="w-full h-9 px-2 rounded-xl border text-xs">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-touch w-full bg-slate-900 text-white font-bold text-xs rounded-xl py-2.5">Proses Payroll</button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
