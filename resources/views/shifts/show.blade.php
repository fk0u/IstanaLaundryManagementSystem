<x-app-layout>
    <div class="flex flex-col gap-5 md:gap-6 pb-10">

        {{-- ── HEADER ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm">
            <div>
                <div class="flex items-center gap-2 text-2xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('shifts.index') }}" class="hover:text-primary transition-colors">Rekapitulasi Shift</a>
                    <span>/</span>
                    <span class="text-primary font-black">Detail Audit #{{ $shift->id }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black font-display text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-primary text-2xl sm:text-3xl">receipt_long</span>
                    Audit Rekapitulasi Shift #{{ $shift->id }}
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Cabang: <span class="font-bold text-slate-700 dark:text-slate-200">{{ $shift->branch?->name ?? 'Pusat' }}</span> • 
                    Kasir: <span class="font-bold text-slate-700 dark:text-slate-200">{{ $shift->cashier?->name }}</span> • 
                    Status: <span class="font-black text-emerald-600 uppercase">{{ $shift->status }}</span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pos.shift.summary-pdf', $shift->id) }}" target="_blank" class="px-4 py-2.5 bg-rose-600 text-white font-extrabold text-xs rounded-xl shadow-md shadow-rose-500/20 flex items-center gap-1.5 transition-all hover:bg-rose-700">
                    <span class="material-symbols-outlined text-sm">print</span>
                    Cetak Struk Shift PDF
                </a>
                <a href="{{ route('shifts.index') }}" class="px-3.5 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-extrabold text-xs rounded-xl hover:bg-slate-200">
                    Kembali
                </a>
            </div>
        </div>

        {{-- ── FINANCIAL AUDIT SUMMARY ── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- 1. Rekonsiliasi Kas --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm space-y-4">
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="material-symbols-outlined text-primary text-lg">account_balance_wallet</span>
                    Rekonsiliasi Kas Laci (System vs Actual)
                </h3>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400 font-bold">Modal Awal Shift</span>
                        <span class="font-black text-slate-800 dark:text-slate-200">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400 font-bold">+ Total Pembayaran Tunai (Cash Sales)</span>
                        <span class="font-black text-emerald-600">+ Rp {{ number_format($cashSales, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-slate-400 font-bold">- Total Kas Kecil Keluar (Petty Cash)</span>
                        <span class="font-black text-rose-500">- Rp {{ number_format($shift->petty_cash_total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 bg-orange-500/10 p-3 rounded-xl border border-orange-200 dark:border-orange-800/60 font-black text-sm">
                        <span class="text-orange-700 dark:text-orange-300">Ekspektasi Kas Sistem</span>
                        <span class="text-primary">Rp {{ number_format($shift->closing_cash_system ?? ($shift->opening_cash + $cashSales - $shift->petty_cash_total), 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl font-black text-sm">
                        <span class="text-slate-600 dark:text-slate-300">Total Uang Fisik Kasir (Actual)</span>
                        <span class="text-slate-900 dark:text-white">Rp {{ number_format($shift->closing_cash_actual ?? 0, 0, ',', '.') }}</span>
                    </div>

                    @php $diff = (float) $shift->cash_difference; @endphp
                    <div class="p-3 rounded-xl border flex items-center justify-between mt-2 {{ $diff == 0 ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($diff > 0 ? 'bg-blue-50 border-blue-200 text-blue-800' : 'bg-amber-50 border-amber-300 text-amber-900') }}">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-lg">{{ $diff == 0 ? 'check_circle' : ($diff > 0 ? 'trending_up' : 'warning') }}</span>
                            <div>
                                <div class="font-extrabold text-xs">{{ $diff == 0 ? 'Kas Fisik Sesuai 100%' : ($diff > 0 ? 'Surplus / Uang Lebih' : 'Defisit / Uang Kurang') }}</div>
                                <div class="text-2xs opacity-80">{{ $diff == 0 ? 'Uang laci cocok dengan data sistem.' : ($diff > 0 ? 'Fisik laci melebihi sistem.' : 'Fisik laci kurang dari sistem.') }}</div>
                            </div>
                        </div>
                        <div class="font-black text-sm">{{ ($diff > 0 ? '+ Rp ' : 'Rp ') . number_format($diff, 0, ',', '.') }}</div>
                    </div>

                    @if($shift->notes)
                        <div class="mt-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 text-2xs">
                            <span class="text-slate-400 uppercase font-black block mb-0.5">Catatan Kasir / Serah Terima:</span>
                            <p class="text-slate-700 dark:text-slate-300 italic font-medium">"{{ $shift->notes }}"</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. Omset & Digital Payment Breakdown --}}
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm space-y-4">
                <h3 class="text-sm font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="material-symbols-outlined text-primary text-lg">pie_chart</span>
                    Rincian Omset Shift per Metode Pembayaran
                </h3>

                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between items-center py-2 px-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl">
                        <span class="font-bold text-slate-600 dark:text-slate-300 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-emerald-600">payments</span> Pembayaran Tunai (Cash)
                        </span>
                        <span class="font-black text-slate-900 dark:text-white">Rp {{ number_format($cashSales, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 px-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl">
                        <span class="font-bold text-slate-600 dark:text-slate-300 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-blue-600">qr_code_scanner</span> QRIS / E-Wallet
                        </span>
                        <span class="font-black text-slate-900 dark:text-white">Rp {{ number_format($qrisSales, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 px-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl">
                        <span class="font-bold text-slate-600 dark:text-slate-300 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-purple-600">account_balance</span> Transfer Bank
                        </span>
                        <span class="font-black text-slate-900 dark:text-white">Rp {{ number_format($transferSales, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center py-2 px-3 bg-slate-50 dark:bg-slate-800/40 rounded-xl">
                        <span class="font-bold text-slate-600 dark:text-slate-300 flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-amber-600">credit_card</span> EDC / Debit Card
                        </span>
                        <span class="font-black text-slate-900 dark:text-white">Rp {{ number_format($debitSales, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center py-3 px-4 bg-primary/10 rounded-2xl border border-primary/20 mt-3 font-black text-sm">
                        <span class="text-primary uppercase tracking-wider">TOTAL OMSET SHIFT</span>
                        <span class="text-primary text-base">Rp {{ number_format($cashSales + $qrisSales + $transferSales + $debitSales, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Status Sinkronisasi Jurnal Finance --}}
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                    <h4 class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider mb-2">Sinkronisasi Jurnal Keuangan (Finance)</h4>
                    @if($journal)
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 rounded-xl flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-600">verified</span>
                                <div>
                                    <div class="font-bold text-emerald-800 dark:text-emerald-300">Jurnal #{{ $journal->journal_number ?? $journal->id }} Posted</div>
                                    <div class="text-2xs text-slate-500">Tersinkronisasi otomatis ke Modul Akuntansi</div>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 bg-emerald-600 text-white rounded-lg text-2xs font-extrabold">Post Done</span>
                        </div>
                    @else
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700 rounded-xl text-2xs text-slate-500 flex items-center justify-between">
                            <span>Jurnal penyesuaian kas seimbang (tidak ada selisih/kas keluar).</span>
                            <span class="font-bold text-emerald-600">Clean</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- ── PETTY CASH EXPENSES TABLE ── --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="material-symbols-outlined text-rose-500">payments</span>
                    Pengeluaran Kas Kecil (Petty Cash) — Total: Rp {{ number_format($shift->petty_cash_total, 0, ',', '.') }}
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Keperluan / Deskripsi</th>
                            <th class="py-3 px-4">Dicatat Oleh</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                        @forelse($shift->pettyCashRecords as $p)
                            <tr>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 bg-rose-50 dark:bg-rose-950/40 text-rose-600 rounded text-2xs font-extrabold">
                                        {{ $p->category }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-900 dark:text-white">{{ $p->description }}</td>
                                <td class="py-3 px-4 text-2xs text-slate-500">{{ $p->user?->name ?? 'Kasir' }}</td>
                                <td class="py-3 px-4 text-right text-rose-600 font-black">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-center text-slate-400 text-2xs font-medium">Tidak ada pengeluaran kas kecil pada shift ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── ORDERS IN THIS SHIFT TABLE ── --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 dark:border-slate-800">
                <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">local_laundry_service</span>
                    Daftar Order Transaksi Shift Ini (Total {{ $shift->orders->count() }} Nota)
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-400 uppercase text-[10px] font-extrabold border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="py-3 px-4">No. Order</th>
                            <th class="py-3 px-4">Pelanggan</th>
                            <th class="py-3 px-4">Metode Bayar</th>
                            <th class="py-3 px-4">Status Bayar</th>
                            <th class="py-3 px-4 text-right">Total Order</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                        @forelse($shift->orders as $o)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-black text-primary">{{ $o->order_number }}</td>
                                <td class="py-3 px-4">{{ $o->customer?->name ?? $o->customer_name_walkin ?? 'Pelanggan Walk-In' }}</td>
                                <td class="py-3 px-4 uppercase font-extrabold text-2xs">{{ $o->payment_method }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-2xs font-extrabold {{ $o->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ strtoupper($o->payment_status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-black text-slate-900 dark:text-white">Rp {{ number_format($o->total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-400 text-2xs font-medium">Belum ada transaksi order yang tercatat pada shift ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
