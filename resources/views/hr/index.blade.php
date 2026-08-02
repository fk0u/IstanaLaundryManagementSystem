<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6" x-data="{
        activeTab: @js(request('tab', 'employees')),
        showAddEmployee: false,
        showAddPayroll: false,
        showAddAttendance: false,
        activeEditEmployee: null,
        activeCreateAccountEmp: null,
        activeLinkAccountEmp: null,
        activeResetPasswordEmp: null,
        selectedPayrollBranch: 'all',
        scopedBranchId: @js(session('scoped_branch_id')),
        activeEditItem: @js(request('edit_item') ? (int) request('edit_item') : null),
        activeDeletePayroll: null,
        createAccountChecked: false,
    }">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <x-page-header title="HR & Penggajian (Payroll)" :breadcrumbs="['HR & Payroll' => '/hr']" />
            <div class="flex items-center gap-2">
                <a href="{{ route('hr.export.pdf') }}" target="_blank"
                   class="h-10 px-3.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-base">picture_as_pdf</span> Unduh PDF
                </a>
                <a href="{{ route('hr.export.xlsx') }}"
                   class="h-10 px-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-base">table_chart</span> Ekspor XLSX
                </a>
            </div>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-2" />
        @endif

        <!-- Sleek Tabpane Navigation Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-1.5 overflow-x-auto min-w-max p-1 bg-slate-100 dark:bg-slate-900 rounded-2xl">
                <button type="button" @click="activeTab = 'employees'" :class="activeTab === 'employees' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">badge</span>
                    <span>Data Staf Karyawan</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="activeTab === 'employees' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300'">{{ $employees->total() }}</span>
                </button>

                <button type="button" @click="activeTab = 'payroll'" :class="activeTab === 'payroll' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">payments</span>
                    <span>Riwayat & Payroll Gaji</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="activeTab === 'payroll' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300'">{{ $payrolls->count() }}</span>
                </button>

                <button type="button" @click="activeTab = 'attendance'" :class="activeTab === 'attendance' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">how_to_reg</span>
                    <span>Sesi Kerja & Presensi</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="activeTab === 'attendance' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300'">{{ $attendances->total() }}</span>
                </button>

                <button type="button" @click="activeTab = 'analytics'" :class="activeTab === 'analytics' ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">analytics</span>
                    <span>Analytics & Insentif</span>
                </button>
            </div>

            <!-- Tab Context Action Buttons -->
            <div class="flex items-center gap-2">
                <div x-show="activeTab === 'employees'" x-cloak>
                    <button type="button" @click="showAddEmployee = true" class="btn-touch px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm">
                        <span class="material-symbols-outlined text-base">person_add</span> Tambah Karyawan Baru
                    </button>
                </div>
                <div x-show="activeTab === 'payroll'" x-cloak>
                    <button type="button" @click="showAddPayroll = true" class="btn-touch px-4 py-2 bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm">
                        <span class="material-symbols-outlined text-base">payments</span> Generate Payroll Periode
                    </button>
                </div>
                <div x-show="activeTab === 'attendance'" x-cloak>
                    <button type="button" @click="showAddAttendance = true" class="btn-touch px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm">
                        <span class="material-symbols-outlined text-base">how_to_reg</span> Catat Log Presensi
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 1: DATA KARYAWAN & STAF ==================== -->
        <div x-show="activeTab === 'employees'" class="space-y-4" x-cloak>
            <x-card title="Manajemen & Detail Staf Karyawan">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-2.5 px-3">NIK / Nama Staf</th>
                                <th class="py-2.5 px-3">Akun Login & Status</th>
                                <th class="py-2.5 px-3">Jabatan & Cabang</th>
                                <th class="py-2.5 px-3">Kontak & Usia</th>
                                <th class="py-2.5 px-3">Rekening Bank</th>
                                <th class="py-2.5 px-3 text-right">Gaji Pokok</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                            @forelse($employees as $emp)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                    <td class="py-3 px-3">
                                        <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $emp->name }}</span>
                                        <span class="text-2xs text-slate-400 font-mono">NIK: {{ $emp->nik }}</span>
                                    </td>
                                    <td class="py-3 px-3">
                                        @if($emp->user)
                                            <div class="space-y-1">
                                                <span class="font-bold text-2xs text-slate-700 dark:text-slate-200 block">{{ $emp->user->email }}</span>
                                                <div class="flex items-center gap-1 flex-wrap">
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400">
                                                        {{ $emp->user->roles->first()?->name ?? 'Staf' }}
                                                    </span>
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                        <span class="material-symbols-outlined text-[10px]">check_circle</span> Aktif
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-2xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400">
                                                <span class="material-symbols-outlined text-xs">no_accounts</span> Belum Ada Akun
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="font-bold text-xs text-primary block">{{ $emp->position }}</span>
                                        <span class="text-2xs text-slate-500">{{ $emp->branch?->name ?? 'Konsolidasi / Utama' }}</span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="font-medium text-slate-700 dark:text-slate-300 block">{{ $emp->phone ?? '-' }}</span>
                                        <span class="text-2xs text-slate-400">
                                            {{ $emp->birth_date ? $emp->birth_date->format('d/m/Y') : '' }} 
                                            ({{ $emp->age ? $emp->age.' th' : 'Usia -' }})
                                        </span>
                                    </td>
                                    <td class="py-3 px-3">
                                        @if($emp->bank_account_number)
                                            <span class="font-bold text-xs text-slate-800 dark:text-slate-200 block">{{ $emp->bank_name ?? 'Bank' }}: {{ $emp->bank_account_number }}</span>
                                            <span class="text-2xs text-slate-400">a.n {{ $emp->bank_account_holder ?? $emp->name }}</span>
                                        @else
                                            <span class="text-2xs text-slate-400 italic">Belum ada rekening</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                        Rp {{ number_format($emp->base_salary, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" @click="activeEditEmployee = @js($emp)" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 hover:text-primary hover:bg-orange-50 transition-colors" title="Edit Staf & Gaji">
                                                <span class="material-symbols-outlined text-base">edit</span>
                                            </button>
                                            @if($emp->user)
                                                <button type="button" @click="activeResetPasswordEmp = @js($emp)" class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/30 text-amber-700 hover:bg-amber-100 transition-colors" title="Reset Password Akun">
                                                    <span class="material-symbols-outlined text-base">key</span>
                                                </button>
                                            @else
                                                <button type="button" @click="activeCreateAccountEmp = @js($emp)" class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 hover:bg-emerald-100 transition-colors" title="Buat Akun Login Baru">
                                                    <span class="material-symbols-outlined text-base">person_add</span>
                                                </button>
                                                @if($unlinkedUsers->count() > 0)
                                                    <button type="button" @click="activeLinkAccountEmp = @js($emp)" class="p-1.5 rounded-lg bg-blue-50 dark:bg-blue-950/30 text-blue-700 hover:bg-blue-100 transition-colors" title="Tautkan ke Akun Pengguna Terdaftar">
                                                        <span class="material-symbols-outlined text-base">link</span>
                                                    </button>
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-400">Belum ada karyawan terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($employees->hasPages())
                    <div class="mt-3">
                        {{ $employees->links() }}
                    </div>
                @endif
            </x-card>
        </div>

        <!-- ==================== TAB 2: RIWAYAT & PAYROLL GAJI ==================== -->
        <div x-show="activeTab === 'payroll'" class="space-y-4" x-cloak>
            <x-card title="Riwayat & Dokumen Pemrosesan Payroll Gaji">
                <div class="space-y-3 max-h-[650px] overflow-y-auto pr-1">
                    @forelse($payrolls as $pr)
                        <div class="p-4 border border-slate-100 dark:border-slate-800 rounded-2xl bg-slate-50/40 dark:bg-slate-900/40 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/50 dark:border-slate-800 pb-2.5">
                                <div>
                                    <span class="font-black text-sm text-slate-800 dark:text-slate-200 block">
                                        Periode {{ date('F', mktime(0, 0, 0, $pr->month, 10)) }} {{ $pr->year }}
                                    </span>
                                    <span class="text-2xs text-slate-400 block font-semibold mt-0.5">
                                        Cabang: {{ $pr->branch?->name ?? 'Seluruh Cabang (Konsolidasi Global)' }} • Diproses Oleh: {{ $pr->createdByUser?->name ?? 'System' }}
                                    </span>
                                    <div class="mt-1 flex items-center gap-2 flex-wrap">
                                        @if($pr->status === 'final')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-2xs font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                <span class="material-symbols-outlined text-xs">lock</span> FINAL (DIKUNCI)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-2xs font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400">
                                                <span class="material-symbols-outlined text-xs">edit_document</span> DRAFT
                                            </span>
                                        @endif
                                        @if($pr->journals()->exists())
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-800">
                                                <span class="material-symbols-outlined text-[12px]">account_balance_wallet</span> Jurnal Terposting
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700">
                                                <span class="material-symbols-outlined text-[12px]">pending</span> Belum Terposting
                                            </span>
                                        @endif
                                        <span class="text-2xs font-mono font-bold text-slate-500">Total: {{ $pr->items->count() }} Staf</span>
                                        <span class="text-2xs font-mono font-bold text-emerald-600">Rp {{ number_format($pr->items->sum('net_salary'), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('hr.payrolls.show', $pr->id) }}" class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:border-primary hover:text-primary transition-colors shadow-sm">
                                        <span class="material-symbols-outlined text-base">info</span> Detail & Cetak
                                    </a>
                                    @if(!$pr->journals()->exists())
                                        <form action="{{ route('hr.payrolls.sync-journal', $pr->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow-sm" title="Sync ke Jurnal Keuangan">
                                                <span class="material-symbols-outlined text-base">sync</span> Sync Keuangan
                                            </button>
                                        </form>
                                    @endif
                                    @if($pr->status === 'draft')
                                        <form action="{{ route('hr.payrolls.finalize', $pr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MEMFINALKAN payroll ini? Setelah difinalkan, payroll akan DIKUNCI dan Jurnal Keuangan akan otomatis diposting.')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors shadow-sm" title="Finalkan Payroll">
                                                <span class="material-symbols-outlined text-base">check_circle</span> Finalkan
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" @click="activeDeletePayroll = {{ $pr->id }}" class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors" title="Hapus Riwayat Payroll">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 mt-2">
                                @foreach($pr->items as $pItem)
                                    <div id="payroll-item-{{ $pItem->id }}" class="flex items-center justify-between bg-white dark:bg-slate-800 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/50">
                                        <div class="flex-1 min-w-0 pr-2">
                                            <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $pItem->employee?->name }}</p>
                                            <p class="text-2xs text-slate-500 font-semibold truncate">{{ $pItem->employee?->position }} • {{ $pItem->employee?->branch?->name }}</p>
                                            <p class="text-2xs font-mono font-bold text-emerald-600 mt-0.5">Gaji: Rp {{ number_format($pItem->net_salary, 0, ',', '.') }}</p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('hr.payslip', $pItem->id) }}" target="_blank" class="inline-flex items-center gap-1 text-2xs font-bold px-2 py-1 rounded-lg bg-orange-50 text-primary hover:bg-orange-100 transition-colors">
                                                <span class="material-symbols-outlined text-sm">receipt_long</span> Slip
                                            </a>
                                            @if($pr->status !== 'final')
                                                <button type="button" @click="activeEditItem = {{ $pItem->id }}" class="inline-flex items-center gap-1 text-2xs font-bold px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 transition-colors">
                                                    <span class="material-symbols-outlined text-sm">edit</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-slate-400 text-xs">Belum ada data payroll diproses. Klik "Generate Payroll Periode" untuk memproses payroll baru.</div>
                    @endforelse
                </div>
            </x-card>
        </div>

        <!-- ==================== TAB 3: SESI KERJA & PRESENSI ==================== -->
        <div x-show="activeTab === 'attendance'" class="space-y-4" x-cloak>
            <x-card title="Sesi Kerja & Presensi Staf Karyawan">
                <div class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600 text-xl">how_to_reg</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Riwayat Presensi & Log Jam Kerja Bulan Ini</span>
                        </div>
                        <button type="button" @click="showAddAttendance = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors shadow-sm">
                            <span class="material-symbols-outlined text-base">add</span> Input Log Sesi Kerja
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Tanggal</th>
                                    <th class="py-2.5 px-3">Nama Karyawan</th>
                                    <th class="py-2.5 px-3">Cabang</th>
                                    <th class="py-2.5 px-3">Jam Masuk / Keluar</th>
                                    <th class="py-2.5 px-3">Status Presensi</th>
                                    <th class="py-2.5 px-3">Catatan / Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                                @forelse($attendances as $att)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                        <td class="py-2.5 px-3 font-bold text-slate-800 dark:text-slate-200">
                                            {{ $att->date ? $att->date->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $att->employee?->name ?? '-' }}</span>
                                            <span class="text-2xs text-slate-400 font-mono">NIK: {{ $att->employee?->nik ?? '-' }}</span>
                                        </td>
                                        <td class="py-2.5 px-3 font-medium text-slate-600 dark:text-slate-400">
                                            {{ $att->employee?->branch?->name ?? '-' }}
                                        </td>
                                        <td class="py-2.5 px-3 font-mono font-bold text-slate-700 dark:text-slate-300">
                                            {{ $att->check_in ? date('H:i', strtotime($att->check_in)) : '--:--' }} - {{ $att->check_out ? date('H:i', strtotime($att->check_out)) : '--:--' }}
                                        </td>
                                        <td class="py-2.5 px-3">
                                            @if(in_array($att->status, ['hadir', 'present']))
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-2xs font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                    <span class="material-symbols-outlined text-xs">check_circle</span> HADIR
                                                </span>
                                            @elseif(in_array($att->status, ['terlambat', 'late']))
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-2xs font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400">
                                                    <span class="material-symbols-outlined text-xs">schedule</span> TERLAMBAT
                                                </span>
                                            @elseif(in_array($att->status, ['izin']))
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-2xs font-extrabold bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-400">
                                                    <span class="material-symbols-outlined text-xs">info</span> IZIN
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-2xs font-extrabold bg-rose-100 text-rose-800 dark:bg-rose-950/40 dark:text-rose-400">
                                                    <span class="material-symbols-outlined text-xs">cancel</span> ALPA
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 text-slate-500 text-2xs italic">
                                            {{ $att->notes ?? 'Presensi Sesi Kerja Reguler' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-slate-400">Belum ada riwayat sesi kerja & presensi staf dicatat bulan ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($attendances->hasPages())
                        <div class="mt-2">
                            {{ $attendances->links() }}
                        </div>
                    @endif
                </div>
            </x-card>
        </div>

        <!-- ==================== TAB 4: ANALYTICS & INSENTIF ==================== -->
        <div x-show="activeTab === 'analytics'" class="space-y-4" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-2xs font-bold text-slate-400 uppercase">Total Staf Aktif</span>
                        <span class="p-2 rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                            <span class="material-symbols-outlined text-base">badge</span>
                        </span>
                    </div>
                    <p class="text-2xl font-black text-slate-800 dark:text-slate-100 font-mono">{{ $totalActiveEmployees }} Staf</p>
                    <p class="text-2xs text-slate-500 font-medium">Akun Terhubung: <strong>{{ $linkedAccountsCount }}/{{ $totalActiveEmployees }}</strong> ({{ $totalActiveEmployees > 0 ? round(($linkedAccountsCount/$totalActiveEmployees)*100) : 0 }}%)</p>
                </div>

                <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-2xs font-bold text-slate-400 uppercase">Beban Gaji Bulan Ini</span>
                        <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                            <span class="material-symbols-outlined text-base">payments</span>
                        </span>
                    </div>
                    <p class="text-2xl font-black text-emerald-600 font-mono">Rp {{ number_format($totalMonthSalaryExpense, 0, ',', '.') }}</p>
                    <p class="text-2xs text-slate-500 font-medium">Total penggajian bulan berjalan</p>
                </div>

                <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-2xs font-bold text-slate-400 uppercase">Insentif Workshop (Kg)</span>
                        <span class="p-2 rounded-xl bg-orange-50 text-primary dark:bg-orange-950/40">
                            <span class="material-symbols-outlined text-base">scale</span>
                        </span>
                    </div>
                    <p class="text-2xl font-black text-slate-800 dark:text-slate-100 font-mono">Rp {{ number_format($totalMonthBonusKg, 0, ',', '.') }}</p>
                    <p class="text-2xs text-slate-500 font-medium">Bonus performa laundry Kg</p>
                </div>

                <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-2xs font-bold text-slate-400 uppercase">Insentif Workshop (Pcs)</span>
                        <span class="p-2 rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/40 dark:text-purple-400">
                            <span class="material-symbols-outlined text-base">checkroom</span>
                        </span>
                    </div>
                    <p class="text-2xl font-black text-slate-800 dark:text-slate-100 font-mono">Rp {{ number_format($totalMonthBonusPcs, 0, ',', '.') }}</p>
                    <p class="text-2xs text-slate-500 font-medium">Bonus performa satuan Pcs</p>
                </div>
            </div>

            <x-card title="Ringkasan Distribusi Penggajian Staf Per Cabang">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-2.5 px-3">Cabang Penempatan</th>
                                <th class="py-2.5 px-3 text-center">Jumlah Karyawan</th>
                                <th class="py-2.5 px-3 text-right">Rata-rata Gaji Pokok</th>
                                <th class="py-2.5 px-3 text-right">Total Beban Gaji Pokok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                            @foreach($branches as $b)
                                @php
                                    $bEmployees = $employees->where('branch_id', $b->id);
                                    $bCount = $bEmployees->count();
                                    $bAvgSalary = $bCount > 0 ? $bEmployees->avg('base_salary') : 0;
                                    $bTotalSalary = $bEmployees->sum('base_salary');
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                    <td class="py-3 px-3 font-bold text-slate-800 dark:text-slate-200">
                                        Cabang {{ $b->name }}
                                    </td>
                                    <td class="py-3 px-3 text-center font-bold font-mono">
                                        {{ $bCount }} Staf
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono text-slate-700 dark:text-slate-300">
                                        Rp {{ number_format($bAvgSalary, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-bold text-emerald-600">
                                        Rp {{ number_format($bTotalSalary, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- Add Employee Modal -->
        <div x-show="showAddEmployee" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Tambah Karyawan Baru</h3>
                    <button type="button" @click="showAddEmployee = false" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form action="{{ route('hr.employees.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">NIK Staf</label>
                            <input type="text" name="nik" required placeholder="NIK Karyawan..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Nama Lengkap</label>
                            <input type="text" name="name" required placeholder="Nama Karyawan..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Jabatan / Sebagai Apa</label>
                            <input type="text" name="position" required placeholder="Kasir / Operator Workshop / Staf..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Cabang Penempatan</label>
                            <select name="branch_id" required class="w-full h-9 px-2 rounded-xl border text-xs">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" placeholder="08xxxxxxxxxx" class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Gaji Pokok (Rp)</label>
                            <input type="number" name="base_salary" required placeholder="3000000..." class="w-full h-9 px-3 rounded-xl border text-xs font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Tempat Lahir</label>
                            <input type="text" name="birth_place" placeholder="Samarinda..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Tanggal Lahir</label>
                            <input type="date" name="birth_date" class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Alamat Rumah</label>
                        <input type="text" name="address" placeholder="Alamat Karyawan..." class="w-full h-9 px-3 rounded-xl border text-xs">
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl space-y-2 border border-slate-100 dark:border-slate-700">
                        <p class="text-2xs font-bold text-slate-500 uppercase">Informasi Rekening Bank Pembayaran</p>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Bank</label>
                                <input type="text" name="bank_name" placeholder="BCA / Mandiri..." class="w-full h-9 px-2 rounded-xl border text-xs">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">No. Rekening</label>
                                <input type="text" name="bank_account_number" placeholder="1234567890..." class="w-full h-9 px-2 rounded-xl border text-xs font-mono">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Pemilik Rekening</label>
                                <input type="text" name="bank_account_holder" placeholder="Nama Pemilik..." class="w-full h-9 px-2 rounded-xl border text-xs">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5 shadow-md">Simpan Data Karyawan</button>
                </form>
            </div>
        </div>

        <!-- Edit Employee Modal -->
        <template x-if="activeEditEmployee">
            <div class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak @keydown.escape.window="activeEditEmployee = null">
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center pb-2 border-b">
                        <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Edit Data Staf Karyawan</h3>
                        <button type="button" @click="activeEditEmployee = null" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                    </div>
                    <form :action="'/hr/employees/' + activeEditEmployee.id" method="POST" class="space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">NIK Staf</label>
                                <input type="text" name="nik" x-model="activeEditEmployee.nik" required class="w-full h-9 px-3 rounded-xl border text-xs">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Nama Lengkap</label>
                                <input type="text" name="name" x-model="activeEditEmployee.name" required class="w-full h-9 px-3 rounded-xl border text-xs">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Jabatan / Sebagai Apa</label>
                                <input type="text" name="position" x-model="activeEditEmployee.position" required class="w-full h-9 px-3 rounded-xl border text-xs">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Cabang Penempatan</label>
                                <select name="branch_id" x-model="activeEditEmployee.branch_id" required class="w-full h-9 px-2 rounded-xl border text-xs">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Nomor Telepon / WhatsApp</label>
                                <input type="text" name="phone" x-model="activeEditEmployee.phone" class="w-full h-9 px-3 rounded-xl border text-xs">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Gaji Pokok (Rp)</label>
                                <input type="number" name="base_salary" x-model="activeEditEmployee.base_salary" required class="w-full h-9 px-3 rounded-xl border text-xs font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Tempat Lahir</label>
                                <input type="text" name="birth_place" x-model="activeEditEmployee.birth_place" class="w-full h-9 px-3 rounded-xl border text-xs">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Tanggal Lahir</label>
                                <input type="date" name="birth_date" x-model="activeEditEmployee.birth_date" class="w-full h-9 px-3 rounded-xl border text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Alamat Rumah</label>
                            <input type="text" name="address" x-model="activeEditEmployee.address" class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>

                        <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl space-y-2 border border-slate-100 dark:border-slate-700">
                            <p class="text-2xs font-bold text-slate-500 uppercase">Informasi Rekening Bank Pembayaran</p>
                            <div class="grid grid-cols-3 gap-2">
                                <div>
                                    <label class="text-2xs font-bold text-slate-400 uppercase">Bank</label>
                                    <input type="text" name="bank_name" x-model="activeEditEmployee.bank_name" class="w-full h-9 px-2 rounded-xl border text-xs">
                                </div>
                                <div>
                                    <label class="text-2xs font-bold text-slate-400 uppercase">No. Rekening</label>
                                    <input type="text" name="bank_account_number" x-model="activeEditEmployee.bank_account_number" class="w-full h-9 px-2 rounded-xl border text-xs font-mono">
                                </div>
                                <div>
                                    <label class="text-2xs font-bold text-slate-400 uppercase">Pemilik Rekening</label>
                                    <input type="text" name="bank_account_holder" x-model="activeEditEmployee.bank_account_holder" class="w-full h-9 px-2 rounded-xl border text-xs">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5 shadow-md">Simpan Perubahan Staf</button>
                    </form>
                </div>
            </div>
        </template>

        <!-- Add Payroll Modal -->
        <div x-show="showAddPayroll" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-2 border-b">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">payments</span>
                        <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Generate & Konfigurasi Komponen Payroll</h3>
                    </div>
                    <button type="button" @click="showAddPayroll = false" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form action="{{ route('hr.payrolls.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    {{-- Periode & Target Cabang --}}
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl space-y-3 border border-slate-100 dark:border-slate-800">
                        <p class="text-2xs font-bold text-slate-500 uppercase tracking-wider">1. Periode & Cabang Target</p>
                        <div class="grid grid-cols-2 gap-3">
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
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Pilihan Cabang Target</label>
                            <select name="branch_id" x-model="selectedPayrollBranch" class="w-full h-9 px-2 rounded-xl border text-xs font-semibold">
                                <option value="all" selected>🌟 Konsolidasi Seluruh Cabang (Semua Karyawan)</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">Cabang {{ $b->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-2xs text-slate-400 mt-1">Pilih "Konsolidasi Seluruh Cabang" untuk generate gaji seluruh staf dari semua cabang sekaligus.</p>
                        </div>
                    </div>

                    {{-- Komponen Tunjangan & Kehadiran --}}
                    <div class="p-3 bg-emerald-50/50 dark:bg-emerald-950/20 rounded-xl space-y-3 border border-emerald-100 dark:border-emerald-900/40">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base text-emerald-600">settings_suggest</span>
                            <p class="text-2xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">2. Komponen Tunjangan & Kehadiran (Earnings)</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-2xs font-bold text-slate-500 uppercase">Hari Kerja Standar / Bln</label>
                                <input type="number" name="work_days" value="26" min="1" required class="w-full h-9 px-3 rounded-xl border text-xs font-bold">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-500 uppercase">Tunj. Transport (Rp/Hadir)</label>
                                <input type="number" name="transport_rate" value="15000" min="0" required class="w-full h-9 px-3 rounded-xl border text-xs font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="text-2xs font-bold text-slate-500 uppercase">Bonus Hadir (%)</label>
                                <input type="number" name="attendance_bonus_pct" value="5" min="0" step="0.5" class="w-full h-9 px-2 rounded-xl border text-xs">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-500 uppercase">Insentif Rp/Kg</label>
                                <input type="number" name="bonus_kg_rate" value="200" min="0" class="w-full h-9 px-2 rounded-xl border text-xs font-mono">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-500 uppercase">Insentif Rp/Pcs</label>
                                <input type="number" name="bonus_pcs_rate" value="500" min="0" class="w-full h-9 px-2 rounded-xl border text-xs font-mono">
                            </div>
                        </div>
                    </div>

                    {{-- Komponen Potongan & BPJS --}}
                    <div class="p-3 bg-rose-50/50 dark:bg-rose-950/20 rounded-xl space-y-3 border border-rose-100 dark:border-rose-900/40">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base text-rose-600">remove_circle_outline</span>
                            <p class="text-2xs font-bold text-rose-700 dark:text-rose-400 uppercase tracking-wider">3. Komponen Potongan & BPJS (Deductions)</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-2xs font-bold text-slate-500 uppercase">Denda Terlambat (Rp/Hari)</label>
                                <input type="number" name="tardiness_rate" value="25000" min="0" required class="w-full h-9 px-3 rounded-xl border text-xs font-bold">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-500 uppercase">Bonus Tambahan (Rp)</label>
                                <input type="number" name="global_bonus" value="0" min="0" placeholder="0..." class="w-full h-9 px-3 rounded-xl border text-xs font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-2 text-2xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" name="include_bpjs_kesehatan" value="1" checked class="rounded text-primary focus:ring-primary">
                                    <span>BPJS Kesehatan (1%)</span>
                                </label>
                                <label class="flex items-center gap-2 text-2xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                                    <input type="checkbox" name="include_bpjs_ketenagakerjaan" value="1" checked class="rounded text-primary focus:ring-primary">
                                    <span>BPJS Ketenagakerjaan (2%)</span>
                                </label>
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-500 uppercase">Potongan Lainnya (Rp)</label>
                                <input type="number" name="global_deduction" value="0" min="0" placeholder="0..." class="w-full h-9 px-3 rounded-xl border text-xs font-bold">
                            </div>
                        </div>
                    </div>

                    <div x-show="scopedBranchId && selectedPayrollBranch != 'all' && selectedPayrollBranch != scopedBranchId" class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 rounded-xl text-amber-800 dark:text-amber-300 text-2xs space-y-1" x-cloak>
                        <div class="flex items-center gap-1.5 font-bold">
                            <span class="material-symbols-outlined text-sm text-amber-500">warning</span>
                            <span>Perhatian Scope Cabang</span>
                        </div>
                        <p>Cabang yang dipilih di form berbeda dengan <b>Scope Header</b> aktif. Payroll tetap diproses untuk cabang di form ini.</p>
                    </div>

                    <button type="submit" class="btn-touch w-full bg-slate-900 text-white font-bold text-xs rounded-xl py-3 shadow-md flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-base">save</span>
                        <span>Proses & Generate Payroll</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Edit Payroll Item Modal (Dynamic) -->
        @foreach($payrolls as $pr)
            @if($pr->status !== 'final')
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
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mt-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                                    <div class="text-center">
                                        <p class="text-2xs text-slate-400">Presensi</p>
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $pItem->attendance_days }}/{{ $pItem->work_days }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xs text-slate-400">Gaji Pokok</p>
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ number_format($pItem->base_salary, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xs text-slate-400">Tunjangan</p>
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ number_format($pItem->allowance, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-2xs text-slate-400">Potongan Dasar</p>
                                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ number_format($pItem->deduction, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                            <form action="{{ route('hr.payroll-item.update', $pItem->id) }}" method="POST" class="space-y-3">
                                @csrf
                                @method('PUT')
                                
                                {{-- ===== PENGHASILAN ===== --}}
                                <p class="text-2xs font-bold text-emerald-600 uppercase tracking-wider border-b border-emerald-100 dark:border-emerald-900/30 pb-1">
                                    <span class="material-symbols-outlined text-xs align-middle">add_circle</span> Komponen Penghasilan
                                </p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Tunjangan</label>
                                        <input type="number" name="allowance" value="{{ $pItem->allowance }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs">
                                    </div>
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Bonus Kiloan (Kg)</label>
                                        <input type="number" name="bonus_kg" value="{{ $pItem->bonus_kg }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs">
                                    </div>
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Bonus Pcs</label>
                                        <input type="number" name="bonus_pcs" value="{{ $pItem->bonus_pcs }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs">
                                    </div>
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Transport</label>
                                        <input type="number" name="transport_allowance" value="{{ $pItem->transport_allowance }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs">
                                    </div>
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Lembur</label>
                                        <input type="number" name="overtime_pay" value="{{ $pItem->overtime_pay }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs">
                                    </div>
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Bonus Presensi</label>
                                        <input type="number" name="attendance_bonus" value="{{ $pItem->attendance_bonus }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Bonus Khusus / Insentif</label>
                                        <input type="number" name="special_bonus" value="{{ $pItem->special_bonus ?? 0 }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs" placeholder="Bonus THR, insentif penjualan, dll...">
                                    </div>
                                </div>

                                {{-- ===== POTONGAN ===== --}}
                                <p class="text-2xs font-bold text-rose-600 uppercase tracking-wider border-b border-rose-100 dark:border-rose-900/30 pb-1 mt-2">
                                    <span class="material-symbols-outlined text-xs align-middle">remove_circle</span> Komponen Potongan
                                </p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Potongan Dasar</label>
                                        <input type="number" name="deduction" value="{{ $pItem->deduction }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs">
                                    </div>
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Denda Terlambat</label>
                                        <input type="number" name="tardiness_deduction" value="{{ $pItem->tardiness_deduction }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs">
                                    </div>
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Cicilan Kasbon</label>
                                        <input type="number" name="loan_deduction" value="{{ $pItem->loan_deduction }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs">
                                    </div>
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Ganti Rugi</label>
                                        <input type="number" name="damage_deduction" value="{{ $pItem->damage_deduction }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs">
                                    </div>
                                    {{-- BPJS Kesehatan --}}
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">BPJS Kesehatan (1%)</label>
                                        <input type="number" name="bpjs_kesehatan_deduction" value="{{ $pItem->bpjs_kesehatan_deduction ?? 0 }}" step="0.01" min="0" 
                                               class="w-full h-9 px-3 rounded-xl border text-xs" 
                                               placeholder="{{ number_format($pItem->base_salary * 0.01, 0) }}"
                                               title="Iuran BPJS Kesehatan karyawan: 1% dari gaji pokok">
                                        <span class="text-[9px] text-slate-400">Karyawan 1% dari gaji pokok</span>
                                    </div>
                                    {{-- BPJS Ketenagakerjaan --}}
                                    <div>
                                        <label class="text-2xs font-bold text-slate-400 uppercase">BPJS Ketenagakerjaan (2%)</label>
                                        <input type="number" name="bpjs_ketenagakerjaan_deduction" value="{{ $pItem->bpjs_ketenagakerjaan_deduction ?? 0 }}" step="0.01" min="0" 
                                               class="w-full h-9 px-3 rounded-xl border text-xs"
                                               placeholder="{{ number_format($pItem->base_salary * 0.02, 0) }}"
                                               title="Iuran JHT BPJS Ketenagakerjaan karyawan: 2% dari gaji pokok">
                                        <span class="text-[9px] text-slate-400">JHT karyawan 2% dari gaji pokok</span>
                                    </div>
                                    {{-- Legacy BPJS field (legacy support) --}}
                                    <div class="col-span-2">
                                        <label class="text-2xs font-bold text-slate-400 uppercase">Potongan BPJS Lainnya</label>
                                        <input type="number" name="bpjs_deduction" value="{{ $pItem->bpjs_deduction }}" step="0.01" min="0" class="w-full h-9 px-3 rounded-xl border text-xs">
                                    </div>
                                </div>

                                {{-- Summary Box --}}
                                <div class="bg-emerald-50 dark:bg-emerald-900/20 p-3 rounded-lg">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-bold text-slate-600 dark:text-slate-400">Estimasi Gaji Bersih:</span>
                                        <span class="font-bold text-emerald-600">Rp {{ number_format(
                                            $pItem->base_salary + $pItem->allowance + $pItem->bonus_kg + $pItem->bonus_pcs + $pItem->transport_allowance + $pItem->overtime_pay + $pItem->attendance_bonus + ($pItem->special_bonus ?? 0)
                                            - $pItem->deduction - $pItem->tardiness_deduction - $pItem->loan_deduction - $pItem->damage_deduction - $pItem->bpjs_deduction - ($pItem->bpjs_kesehatan_deduction ?? 0) - ($pItem->bpjs_ketenagakerjaan_deduction ?? 0),
                                            0, ',', '.') }}</span>
                                    </div>
                                    <p class="text-[9px] text-slate-400 mt-1">* Estimasi berdasarkan nilai saat ini, akan diupdate setelah disimpan.</p>
                                </div>
                                <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5">Simpan Perubahan</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @endif
        @endforeach

        <!-- Delete Payroll Confirmation Modal -->
        @foreach($payrolls as $pr)
            @if($pr->status !== 'final')
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
                            <button type="button" @click="activeDeletePayroll = null" class="flex-1 py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                                Batal
                            </button>
                            <button type="submit" class="flex-1 py-2.5 px-4 bg-red-500 text-white font-bold text-xs rounded-xl hover:bg-red-600 transition-colors">
                                Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach

        <!-- Add Attendance Log Modal -->
        <div x-show="showAddAttendance" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak @keydown.escape.window="showAddAttendance = false">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Catat Log Sesi Kerja & Presensi</h3>
                    <button type="button" @click="showAddAttendance = false" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form action="{{ route('hr.attendances.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Pilih Staf Karyawan</label>
                        <select name="employee_id" required class="w-full h-9 px-2 rounded-xl border text-xs">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->nik }}) - {{ $emp->position }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Tanggal Presensi</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full h-9 px-3 rounded-xl border text-xs font-bold">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Status Kehadiran</label>
                            <select name="status" required class="w-full h-9 px-2 rounded-xl border text-xs font-bold">
                                <option value="hadir" selected>HADIR (Tepat Waktu)</option>
                                <option value="terlambat">TERLAMBAT</option>
                                <option value="izin">IZIN / SAKIT</option>
                                <option value="alpa">ALPA (Tanpa Keterangan)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Jam Masuk (Check-In)</label>
                            <input type="time" name="check_in" value="08:00" class="w-full h-9 px-3 rounded-xl border text-xs font-mono">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Jam Keluar (Check-Out)</label>
                            <input type="time" name="check_out" value="17:00" class="w-full h-9 px-3 rounded-xl border text-xs font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Catatan / Keterangan Sesi</label>
                        <input type="text" name="notes" placeholder="Contoh: Terlambat 15 menit karena hujan..." class="w-full h-9 px-3 rounded-xl border text-xs">
                    </div>

                    <button type="submit" class="btn-touch w-full bg-emerald-600 text-white font-bold text-xs rounded-xl py-2.5 shadow-md">Simpan Log Presensi</button>
                </form>
            </div>
        </div>

        <!-- Create Account Modal (Dynamic) -->
        <template x-if="activeCreateAccountEmp">
            <div x-show="activeCreateAccountEmp" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak @keydown.escape.window="activeCreateAccountEmp = null">
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                    <div class="flex justify-between items-center pb-2 border-b">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600">person_add</span>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Buat Akun Login Akses Sistem</h3>
                        </div>
                        <button type="button" @click="activeCreateAccountEmp = null" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="activeCreateAccountEmp.name"></p>
                        <p class="text-2xs text-slate-500" x-text="'NIK: ' + activeCreateAccountEmp.nik + ' • Jabatan: ' + activeCreateAccountEmp.position"></p>
                    </div>

                    <form :action="'/hr/employees/' + activeCreateAccountEmp.id + '/create-account'" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Alamat Email Login</label>
                            <input type="email" name="email" required placeholder="email@istanalaundry.com" class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Password Akses</label>
                            <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Hak Akses Role Spatie</label>
                            <select name="role" required class="w-full h-9 px-2 rounded-xl border text-xs font-bold">
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5 shadow-md">Buat & Hubungkan Akun Login</button>
                    </form>
                </div>
            </div>
        </template>

        <!-- Link Existing User Account Modal (Dynamic) -->
        <template x-if="activeLinkAccountEmp">
            <div x-show="activeLinkAccountEmp" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak @keydown.escape.window="activeLinkAccountEmp = null">
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                    <div class="flex justify-between items-center pb-2 border-b">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-blue-600">link</span>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Tautkan ke Akun Terdaftar</h3>
                        </div>
                        <button type="button" @click="activeLinkAccountEmp = null" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="activeLinkAccountEmp.name"></p>
                        <p class="text-2xs text-slate-500" x-text="'NIK: ' + activeLinkAccountEmp.nik"></p>
                    </div>

                    <form :action="'/hr/employees/' + activeLinkAccountEmp.id + '/link-account'" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Pilih Akun Pengguna Terdaftar</label>
                            <select name="user_id" required class="w-full h-9 px-2 rounded-xl border text-xs font-bold">
                                @foreach($unlinkedUsers as $uUser)
                                    <option value="{{ $uUser->id }}">{{ $uUser->name }} ({{ $uUser->email }})</option>
                                @endforeach
                            </select>
                            <p class="text-2xs text-slate-400 mt-1">Hanya menampilkan akun pengguna yang belum terhubung ke profil karyawan mana pun.</p>
                        </div>

                        <button type="submit" class="btn-touch w-full bg-blue-600 text-white font-bold text-xs rounded-xl py-2.5 shadow-md">Tautkan Akun</button>
                    </form>
                </div>
            </div>
        </template>

        <!-- Reset Password Modal (Dynamic) -->
        <template x-if="activeResetPasswordEmp">
            <div x-show="activeResetPasswordEmp" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak @keydown.escape.window="activeResetPasswordEmp = null">
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                    <div class="flex justify-between items-center pb-2 border-b">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-600">key</span>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Reset Password Akun Staf</h3>
                        </div>
                        <button type="button" @click="activeResetPasswordEmp = null" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="activeResetPasswordEmp.name"></p>
                        <p class="text-2xs text-slate-500" x-text="'Email: ' + (activeResetPasswordEmp.user ? activeResetPasswordEmp.user.email : '-')"></p>
                    </div>

                    <form :action="'/hr/employees/' + activeResetPasswordEmp.id + '/reset-password'" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Password Baru</label>
                            <input type="password" name="password" required minlength="8" placeholder="Minimal 8 karakter..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Konfirmasi Password Baru</label>
                            <input type="password" name="password_confirmation" required minlength="8" placeholder="Ulangi password baru..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>

                        <button type="submit" class="btn-touch w-full bg-amber-600 text-white font-bold text-xs rounded-xl py-2.5 shadow-md">Reset Password Staf</button>
                    </form>
                </div>
            </div>
        </template>

    </div>
</x-app-layout>
