<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <x-page-header title="Detail Payroll" :breadcrumbs="['HR & Payroll' => route('hr.index'), 'Detail Payroll' => '#']" />
                <p class="mt-1 text-xs text-slate-500">
                    Periode {{ date('F Y', mktime(0, 0, 0, $payroll->month, 10)) }} • Cabang {{ $payroll->branch?->name ?? 'Utama' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('hr.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <span class="material-symbols-outlined text-base">arrow_back</span> Kembali
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-2xs uppercase text-slate-400 font-bold">Status</p>
                <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-200 uppercase">{{ $payroll->status }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-2xs uppercase text-slate-400 font-bold">Karyawan</p>
                <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-200">{{ $payroll->items->count() }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-2xs uppercase text-slate-400 font-bold">Total Gaji</p>
                <p class="mt-1 text-sm font-bold text-emerald-600">Rp {{ number_format($payroll->items->sum('net_salary'), 0, ',', '.') }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-2xs uppercase text-slate-400 font-bold">Diproses</p>
                <p class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-200">{{ optional($payroll->processed_at)->format('d/m/Y H:i') ?? '-' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8">
                <x-card title="Daftar Komponen Payroll">
                    <div class="space-y-3">
                        @foreach($payroll->items as $item)
                            <div class="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/40 p-4">
                                <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                                    <div>
                                        <p class="font-bold text-sm text-slate-800 dark:text-slate-200">{{ $item->employee?->name }}</p>
                                        <p class="text-2xs text-slate-500">{{ $item->employee?->nik }} • {{ $item->employee?->position }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xs uppercase text-slate-400">Gaji Bersih</p>
                                        <p class="font-bold text-emerald-600">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-2xs">
                                    <div class="bg-white dark:bg-slate-800 rounded-lg p-3">
                                        <span class="text-slate-400 block">Presensi</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ $item->attendance_days }}/{{ $item->work_days }}</span>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 rounded-lg p-3">
                                        <span class="text-slate-400 block">Tunjangan</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($item->allowance, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 rounded-lg p-3">
                                        <span class="text-slate-400 block">Bonus</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($item->bonus_kg + $item->bonus_pcs + $item->transport_allowance + $item->overtime_pay + $item->attendance_bonus, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="bg-white dark:bg-slate-800 rounded-lg p-3">
                                        <span class="text-slate-400 block">Potongan</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($item->deduction + $item->tardiness_deduction + $item->loan_deduction + $item->damage_deduction + $item->bpjs_deduction, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <a href="{{ route('hr.payslip', $item->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-orange-50 text-primary text-xs font-bold hover:bg-orange-100 transition-colors">
                                        <span class="material-symbols-outlined text-base">receipt_long</span> Slip Gaji
                                    </a>
                                    <a href="{{ route('hr.index', ['edit_item' => $item->id]) }}#payroll-item-{{ $item->id }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                                        <span class="material-symbols-outlined text-base">edit</span> Edit Komponen
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <x-card title="Ringkasan Payroll">
                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between gap-3 border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                            <span class="text-slate-500">Dibuat Oleh</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-right">{{ $payroll->createdByUser?->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                            <span class="text-slate-500">Cabang</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-right">{{ $payroll->branch?->name ?? 'Utama' }}</span>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-dashed border-slate-200 dark:border-slate-700 pb-2">
                            <span class="text-slate-500">Periode</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-right">{{ date('F Y', mktime(0, 0, 0, $payroll->month, 10)) }}</span>
                        </div>
                        <div class="flex justify-between gap-3">
                            <span class="text-slate-500">Jumlah Item</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-right">{{ $payroll->items->count() }}</span>
                        </div>
                    </div>
                </x-card>

                <x-card title="Tindakan Cepat">
                    <div class="space-y-2">
                        <a href="{{ route('hr.index') }}" class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-slate-900 dark:bg-slate-800 text-white text-xs font-bold hover:opacity-90 transition-opacity">
                            <span class="material-symbols-outlined text-base">dashboard</span> Kembali ke Riwayat
                        </a>
                        @if($payroll->items->first())
                            <a href="{{ route('hr.index', ['edit_item' => $payroll->items->first()->id]) }}#payroll-item-{{ $payroll->items->first()->id }}" class="inline-flex w-full items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-hover transition-colors">
                                <span class="material-symbols-outlined text-base">edit</span> Edit Item Pertama
                            </a>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
