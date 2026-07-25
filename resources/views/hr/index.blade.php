<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6" x-data="{
        showAddEmployee: false,
        showAddPayroll: false,
        activeDetailPayroll: null,
        activeEditItem: null,
        activeDeletePayroll: null,
    }">
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
                            <div class="p-3 border border-slate-100 dark:border-slate-800 rounded-xl bg-slate-50/40 dark:bg-slate-900/40">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <span class="font-bold text-xs text-slate-800 dark:text-slate-200 block">
                                            Periode {{ date('F', mktime(0, 0, 0, $pr->month, 10)) }} {{ $pr->year }}
                                        </span>
                                        <span class="text-2xs text-slate-400">
                                            Cabang: {{ $pr->branch?->name }} • Status: <span class="uppercase font-bold text-emerald-600">{{ $pr->status }}</span>
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button type="button" @click="activeDetailPayroll = {{ $pr->id }}" class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:border-primary hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-base">info</span> Detail
                                        </button>
                                        <button type="button" @click="activeDeletePayroll = {{ $pr->id }}" class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                                            <span class="material-symbols-outlined text-base">delete</span> Hapus Riwayat
                                        </button>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    @foreach($pr->items as $pItem)
                                        <div class="flex items-center justify-between bg-white dark:bg-slate-800 p-2 rounded-lg">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $pItem->employee?->name }}</p>
                                                <p class="text-2xs text-slate-500">Gaji: Rp {{ number_format($pItem->net_salary, 0, ',', '.') }}</p>
                                            </div>
                                            <div class="flex flex-wrap justify-end gap-1.5">
                                                <a href="{{ route('hr.payslip', $pItem->id) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1.5 rounded-lg bg-orange-50 text-primary hover:bg-orange-100 transition-colors">
                                                    <span class="material-symbols-outlined text-base">receipt_long</span> Slip
                                                </a>
                                                <button type="button" @click="activeEditItem = {{ $pItem->id }}" class="inline-flex items-center gap-1.5 text-xs font-bold px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                                    <span class="material-symbols-outlined text-base">edit</span> Edit Komponen
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-2xs mt-2">
                                    <div class="bg-white dark:bg-slate-800 p-2 rounded-lg">
                                        <span class="text-slate-400 block">Total Karyawan</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $pr->items->count() }}</span>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 p-2 rounded-lg">
                                        <span class="text-slate-400 block">Total Gaji</span>
                                        <span class="font-bold text-emerald-600">Rp {{ number_format($pr->items->sum('net_salary'), 0, ',', '.') }}</span>
                                    </div>
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

        <!-- Detail Payroll Modal -->
        @foreach($payrolls as $pr)
            <div x-show="activeDetailPayroll === {{ $pr->id }}" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak @keydown.escape.window="activeDetailPayroll = null">
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-4xl w-full p-5 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center pb-2 border-b">
                        <div>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Detail Payroll</h3>
                            <p class="text-2xs text-slate-500">Periode {{ date('F Y', mktime(0, 0, 0, $pr->month, 10)) }} • {{ $pr->branch?->name ?? 'Utama' }}</p>
                        </div>
                        <button type="button" @click="activeDetailPayroll = null" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div class="bg-slate-50 dark:bg-slate-800 p-3 rounded-xl">
                            <span class="text-slate-400 block">Status</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 uppercase">{{ $pr->status }}</span>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800 p-3 rounded-xl">
                            <span class="text-slate-400 block">Karyawan</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $pr->items->count() }}</span>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800 p-3 rounded-xl">
                            <span class="text-slate-400 block">Total Gaji</span>
                            <span class="font-bold text-emerald-600">Rp {{ number_format($pr->items->sum('net_salary'), 0, ',', '.') }}</span>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800 p-3 rounded-xl">
                            <span class="text-slate-400 block">Diproses</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ optional($pr->processed_at)->format('d/m/Y H:i') ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @foreach($pr->items as $pItem)
                            <div class="border border-slate-100 dark:border-slate-800 rounded-xl p-4 bg-slate-50/40 dark:bg-slate-900/40">
                                <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                                    <div>
                                        <p class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ $pItem->employee?->name }}</p>
                                        <p class="text-2xs text-slate-500">{{ $pItem->employee?->nik }} • {{ $pItem->employee?->position }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xs text-slate-400 uppercase">Gaji Bersih</p>
                                        <p class="font-bold text-emerald-600">Rp {{ number_format($pItem->net_salary, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-2xs">
                                    <div class="bg-white dark:bg-slate-800 rounded-lg p-2">
                                        <span class="text-slate-400 block">Kehadiran</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ $pItem->attendance_days }}/{{ $pItem->work_days }}</span>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 rounded-lg p-2">
                                        <span class="text-slate-400 block">Bonus</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($pItem->bonus_kg + $pItem->bonus_pcs + $pItem->transport_allowance + $pItem->overtime_pay + $pItem->attendance_bonus, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 rounded-lg p-2">
                                        <span class="text-slate-400 block">Potongan</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($pItem->deduction + $pItem->tardiness_deduction + $pItem->loan_deduction + $pItem->damage_deduction + $pItem->bpjs_deduction, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 rounded-lg p-2 flex items-center justify-between gap-2">
                                        <a href="{{ route('hr.payslip', $pItem->id) }}" target="_blank" class="inline-flex items-center gap-1.5 font-bold text-primary hover:underline">
                                            <span class="material-symbols-outlined text-base">receipt_long</span> Slip
                                        </a>
                                        <button type="button" @click="activeEditItem = {{ $pItem->id }}; activeDetailPayroll = null" class="inline-flex items-center gap-1.5 font-bold text-slate-600 dark:text-slate-300 hover:text-primary transition-colors">
                                            <span class="material-symbols-outlined text-base">edit</span> Edit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-end pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="activeDetailPayroll = null" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-bold text-xs hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Edit Payroll Item Modal (Dynamic) -->
        @foreach($payrolls as $pr)
            @foreach($pr->items as $pItem)
                <div x-show="activeEditItem === {{ $pItem->id }}" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak @keydown.escape.window="activeEditItem = null">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center pb-2 border-b">
                            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Edit Komponen Payroll</h3>
                            <button type="button" @click="activeEditItem = null" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800 p-3 rounded-lg mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-primary">person</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $pItem->employee?->name }}</p>
                                    <p class="text-2xs text-slate-500">{{ $pItem->employee?->position }} • {{ $pr->branch?->name }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-2 mt-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                                <div class="text-center">
                                    <p class="text-2xs text-slate-400">Presensi</p>
                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $pItem->attendance_days }}/{{ $pItem->work_days }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xs text-slate-400">Gaji Pokok</p>
                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ number_format($pItem->base_salary, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-2xs text-slate-400">Periode</p>
                                    <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ date('M y', mktime(0, 0, 0, $pr->month, 10)) }}</p>
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('hr.payroll-item.update', $pItem->id) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="text-2xs font-bold text-slate-400 uppercase">Bonus Kiloan</label>
                                    <input type="number" name="bonus_kg" value="{{ $pItem->bonus_kg }}" step="0.01" class="w-full h-9 px-3 rounded-xl border text-xs">
                                </div>
                                <div>
                                    <label class="text-2xs font-bold text-slate-400 uppercase">Bonus Pcs</label>
                                    <input type="number" name="bonus_pcs" value="{{ $pItem->bonus_pcs }}" step="0.01" class="w-full h-9 px-3 rounded-xl border text-xs">
                                </div>
                                <div>
                                    <label class="text-2xs font-bold text-slate-400 uppercase">Transport</label>
                                    <input type="number" name="transport_allowance" value="{{ $pItem->transport_allowance }}" step="0.01" class="w-full h-9 px-3 rounded-xl border text-xs">
                                </div>
                                <div>
                                    <label class="text-2xs font-bold text-slate-400 uppercase">Lembur</label>
                                    <input type="number" name="overtime_pay" value="{{ $pItem->overtime_pay }}" step="0.01" class="w-full h-9 px-3 rounded-xl border text-xs">
                                </div>
                                <div>
                                    <label class="text-2xs font-bold text-slate-400 uppercase">Bonus Presensi</label>
                                    <input type="number" name="attendance_bonus" value="{{ $pItem->attendance_bonus }}" step="0.01" class="w-full h-9 px-3 rounded-xl border text-xs">
                                </div>
                                <div>
                                    <label class="text-2xs font-bold text-slate-400 uppercase">Denda Terlambat</label>
                                    <input type="number" name="tardiness_deduction" value="{{ $pItem->tardiness_deduction }}" step="0.01" class="w-full h-9 px-3 rounded-xl border text-xs">
                                </div>
                                <div>
                                    <label class="text-2xs font-bold text-slate-400 uppercase">Cicilan Kasbon</label>
                                    <input type="number" name="loan_deduction" value="{{ $pItem->loan_deduction }}" step="0.01" class="w-full h-9 px-3 rounded-xl border text-xs">
                                </div>
                                <div>
                                    <label class="text-2xs font-bold text-slate-400 uppercase">Ganti Rugi</label>
                                    <input type="number" name="damage_deduction" value="{{ $pItem->damage_deduction }}" step="0.01" class="w-full h-9 px-3 rounded-xl border text-xs">
                                </div>
                                <div class="col-span-2">
                                    <label class="text-2xs font-bold text-slate-400 uppercase">Potongan BPJS</label>
                                    <input type="number" name="bpjs_deduction" value="{{ $pItem->bpjs_deduction }}" step="0.01" class="w-full h-9 px-3 rounded-xl border text-xs">
                                </div>
                            </div>
                            <div class="bg-emerald-50 dark:bg-emerald-900/20 p-3 rounded-lg">
                                <div class="flex justify-between text-xs">
                                    <span class="font-bold text-slate-600 dark:text-slate-400">Estimasi Gaji Bersih:</span>
                                    <span class="font-bold text-emerald-600">Rp {{ number_format($pItem->base_salary + $pItem->allowance + $pItem->bonus_kg + $pItem->bonus_pcs + $pItem->transport_allowance + $pItem->overtime_pay + $pItem->attendance_bonus - $pItem->deduction - $pItem->tardiness_deduction - $pItem->loan_deduction - $pItem->damage_deduction - $pItem->bpjs_deduction, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endforeach

        <!-- Delete Payroll Confirmation Modal -->
        @foreach($payrolls as $pr)
            <div x-show="activeDeletePayroll === {{ $pr->id }}" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak @keydown.escape.window="activeDeletePayroll = null">
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                    <div class="flex items-center gap-3 pb-2 border-b">
                        <span class="material-symbols-outlined text-red-500 text-2xl">warning</span>
                        <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Hapus Riwayat Payroll</h3>
                        <button type="button" @click="activeDeletePayroll = null" class="ml-auto text-slate-400"><span class="material-symbols-outlined">close</span></button>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
                        <p class="text-xs text-slate-700 dark:text-slate-300">
                            Apakah Anda yakin ingin menghapus riwayat payroll untuk periode <strong>{{ date('F Y', mktime(0, 0, 0, $pr->month, 10)) }}</strong>?
                        </p>
                        <p class="text-2xs text-slate-500 mt-2">
                            Cabang: {{ $pr->branch?->name }} • {{ $pr->items->count() }} karyawan • Total: Rp {{ number_format($pr->items->sum('net_salary'), 0, ',', '.') }}
                        </p>
                    </div>
                    <p class="text-xs text-slate-500">
                        Tindakan ini akan menghapus semua data gaji karyawan untuk periode ini dan tidak dapat dibatalkan.
                    </p>
                    <form action="{{ route('hr.payroll.destroy', $pr->id) }}" method="POST" class="flex gap-2">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="showDeletePayroll{{ $pr->id }} = false" class="flex-1 py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-red-500 text-white font-bold text-xs rounded-xl hover:bg-red-600 transition-colors">
                            Ya, Hapus
                        </button>
                    </form>
                </div>
            </div>
        @endforeach

    </div>
</x-app-layout>
