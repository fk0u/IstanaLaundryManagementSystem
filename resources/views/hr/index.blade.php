<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6" x-data="{
        showAddEmployee: false,
        showAddPayroll: false,
        activeEditEmployee: null,
        selectedPayrollBranch: 'all',
        scopedBranchId: @js(session('scoped_branch_id')),
        activeEditItem: @js(request('edit_item') ? (int) request('edit_item') : null),
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
                    <span class="material-symbols-outlined text-base">person_add</span> Tambah Karyawan Baru
                </button>
                <button type="button" @click="showAddPayroll = true" class="btn-touch px-4 py-2 bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-base">payments</span> Generate Payroll Periode
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Employees Table (7 cols) -->
            <div class="lg:col-span-7">
                <x-card title="Manajemen & Detail Staf Karyawan">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">NIK / Nama Staf</th>
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
                                            <button type="button" @click="activeEditEmployee = @js($emp)" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 hover:text-primary hover:bg-orange-50 transition-colors" title="Edit Staf">
                                                <span class="material-symbols-outlined text-base">edit</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-slate-400">Belum ada karyawan terdaftar.</td>
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
                    <div class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                        @forelse($payrolls as $pr)
                            <div class="p-3.5 border border-slate-100 dark:border-slate-800 rounded-2xl bg-slate-50/40 dark:bg-slate-900/40">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <span class="font-black text-xs text-slate-800 dark:text-slate-200 block">
                                            Periode {{ date('F', mktime(0, 0, 0, $pr->month, 10)) }} {{ $pr->year }}
                                        </span>
                                        <span class="text-2xs text-slate-400 block font-semibold mt-0.5">
                                            Cabang: {{ $pr->branch?->name ?? 'Seluruh Cabang (Konsolidasi Global)' }}
                                        </span>
                                        <div class="mt-1">
                                            @if($pr->status === 'final')
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-2xs font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">
                                                    <span class="material-symbols-outlined text-xs">lock</span> FINAL (DIKUNCI)
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-2xs font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/40 dark:text-amber-400">
                                                    <span class="material-symbols-outlined text-xs">edit_document</span> DRAFT
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1.5">
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('hr.payrolls.show', $pr->id) }}" class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:border-primary hover:text-primary transition-colors">
                                                <span class="material-symbols-outlined text-base">info</span> Detail
                                            </a>
                                            @if($pr->status === 'draft')
                                                <form action="{{ route('hr.payrolls.finalize', $pr->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MEMFINALKAN payroll ini? Setelah difinalkan, payroll akan DIKUNCI dan tidak dapat diubah.')">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors" title="Finalkan Payroll">
                                                        <span class="material-symbols-outlined text-base">check_circle</span> Finalkan
                                                    </button>
                                                </form>
                                            @endif
                                            <button type="button" @click="activeDeletePayroll = {{ $pr->id }}" class="inline-flex items-center gap-1 text-xs font-bold px-2 py-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition-colors">
                                                <span class="material-symbols-outlined text-base">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2 mt-3">
                                    @foreach($pr->items as $pItem)
                                        <div id="payroll-item-{{ $pItem->id }}" class="flex items-center justify-between bg-white dark:bg-slate-800 p-2.5 rounded-xl border border-slate-100 dark:border-slate-700/50 scroll-mt-24">
                                            <div class="flex-1 min-w-0 pr-2">
                                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">{{ $pItem->employee?->name }}</p>
                                                <p class="text-2xs text-slate-500 font-semibold">{{ $pItem->employee?->position }} • {{ $pItem->employee?->branch?->name }}</p>
                                                <p class="text-2xs font-mono font-bold text-emerald-600 mt-0.5">Gaji: Rp {{ number_format($pItem->net_salary, 0, ',', '.') }}</p>
                                            </div>
                                            <div class="flex flex-wrap justify-end gap-1">
                                                <a href="{{ route('hr.payslip', $pItem->id) }}" target="_blank" class="inline-flex items-center gap-1 text-2xs font-bold px-2 py-1 rounded-lg bg-orange-50 text-primary hover:bg-orange-100 transition-colors">
                                                    <span class="material-symbols-outlined text-sm">receipt_long</span> Slip
                                                </a>
                                                @if($pr->status !== 'final')
                                                    <button type="button" @click="activeEditItem = {{ $pItem->id }}" class="inline-flex items-center gap-1 text-2xs font-bold px-2 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 transition-colors">
                                                        <span class="material-symbols-outlined text-sm">edit</span> Edit
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-2xs mt-3">
                                    <div class="bg-white dark:bg-slate-800 p-2 rounded-lg text-center">
                                        <span class="text-slate-400 block">Total Karyawan</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $pr->items->count() }} Orang</span>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 p-2 rounded-lg text-center">
                                        <span class="text-slate-400 block">Total Nominal Gaji</span>
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
                        <label class="text-2xs font-bold text-slate-400 uppercase">Pilihan Cabang Target</label>
                        <select name="branch_id" x-model="selectedPayrollBranch" class="w-full h-9 px-2 rounded-xl border text-xs font-semibold">
                            <option value="all" selected>🌟 Konsolidasi Seluruh Cabang (Semua Karyawan)</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">Cabang {{ $b->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-2xs text-slate-400 mt-1">Pilih "Konsolidasi Seluruh Cabang" untuk generate gaji seluruh staf dari semua cabang sekaligus.</p>
                    </div>

                    <div x-show="scopedBranchId && selectedPayrollBranch != 'all' && selectedPayrollBranch != scopedBranchId" class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 rounded-xl text-amber-800 dark:text-amber-300 text-2xs space-y-1" x-cloak>
                        <div class="flex items-center gap-1.5 font-bold">
                            <span class="material-symbols-outlined text-sm text-amber-500">warning</span>
                            <span>Perhatian Scope Cabang</span>
                        </div>
                        <p>Cabang yang dipilih di form berbeda dengan <b>Scope Header</b> aktif. Payroll tetap diproses untuk cabang di form ini.</p>
                    </div>
                    <button type="submit" class="btn-touch w-full bg-slate-900 text-white font-bold text-xs rounded-xl py-2.5 shadow-md">Proses Payroll</button>
                </form>
            </div>
        </div>

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
                        <button type="button" @click="activeDeletePayroll = null" class="flex-1 py-2.5 px-4 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
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
