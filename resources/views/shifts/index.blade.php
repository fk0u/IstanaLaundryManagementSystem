<x-app-layout>
    <div class="flex flex-col gap-5 md:gap-6 pb-10">

        {{-- ── HEADER ── --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm">
            <div>
                <div class="flex items-center gap-2 text-2xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('pos.index') }}" class="hover:text-primary transition-colors">Point of Sale</a>
                    <span>/</span>
                    <span class="text-primary font-black">Rekapitulasi Shift</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black font-display text-slate-900 dark:text-slate-100 tracking-tight flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-primary text-2xl sm:text-3xl">point_of_sale</span>
                    Rekapitulasi &amp; Audit Shift Kasir
                </h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Riwayat pertanggungjawaban kas, selisih kas fisik vs sistem, serta sinkronisasi jurnal otomatis ke Keuangan.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pos.index') }}" class="px-4 py-2.5 bg-primary text-white font-extrabold text-xs rounded-xl shadow-md shadow-primary/20 flex items-center gap-1.5 transition-all hover:bg-primary/90">
                    <span class="material-symbols-outlined text-sm">point_of_sale</span>
                    Buka POS / Shift Kasir
                </a>
            </div>
        </div>

        {{-- ── SUMMARY KPI METRICS ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-2xl">lock_clock</span>
                </div>
                <div>
                    <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider">Total Shift Ditutup</span>
                    <div class="text-xl font-black text-slate-900 dark:text-slate-100 mt-0.5">{{ number_format($totalClosedShifts, 0, ',', '.') }} Shift</div>
                    <span class="text-2xs text-slate-500">Telah direkapitulasi</span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-2xl">payments</span>
                </div>
                <div>
                    <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider">Total Petty Cash (Kas Keluar)</span>
                    <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-0.5">Rp {{ number_format($totalPettyCashSpent, 0, ',', '.') }}</div>
                    <span class="text-2xs text-slate-500">Operasional instan kasir</span>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl {{ $totalCashDifference >= 0 ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600' }} flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-2xl">{{ $totalCashDifference >= 0 ? 'balance' : 'warning' }}</span>
                </div>
                <div>
                    <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider">Net Selisih Kas Fisik</span>
                    <div class="text-xl font-black {{ $totalCashDifference >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }} mt-0.5">
                        {{ $totalCashDifference >= 0 ? '+' : '' }}Rp {{ number_format($totalCashDifference, 0, ',', '.') }}
                    </div>
                    <span class="text-2xs text-slate-500">{{ $totalCashDifference >= 0 ? 'Surplus akumulasi' : 'Defisit akumulasi' }}</span>
                </div>
            </div>
        </div>

        {{-- ── FILTER TOOLBAR ── --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-4 rounded-2xl shadow-sm">
            <form action="{{ route('shifts.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                @if(auth()->user()->isOwner() || auth()->user()->isSuperAdmin())
                    <div>
                        <label class="block text-2xs font-extrabold text-slate-400 uppercase mb-1">Cabang / Outlet</label>
                        <select name="branch_id" class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold bg-slate-50 dark:bg-slate-800 outline-none">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="block text-2xs font-extrabold text-slate-400 uppercase mb-1">Kasir</label>
                    <select name="cashier_id" class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold bg-slate-50 dark:bg-slate-800 outline-none">
                        <option value="">Semua Kasir</option>
                        @foreach($cashiers as $c)
                            <option value="{{ $c->id }}" {{ request('cashier_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-2xs font-extrabold text-slate-400 uppercase mb-1">Status Shift</label>
                    <select name="status" class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold bg-slate-50 dark:bg-slate-800 outline-none">
                        <option value="">Semua Status</option>
                        <option value="OPEN" {{ request('status') == 'OPEN' ? 'selected' : '' }}>AKTIF (OPEN)</option>
                        <option value="CLOSED" {{ request('status') == 'CLOSED' ? 'selected' : '' }}>SELESAI (CLOSED)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-2xs font-extrabold text-slate-400 uppercase mb-1">Dari Tanggal</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold bg-slate-50 dark:bg-slate-800 outline-none">
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="h-10 px-4 bg-primary text-white font-extrabold text-xs rounded-xl flex items-center justify-center gap-1 w-full">
                        <span class="material-symbols-outlined text-sm">filter_alt</span> Filter
                    </button>
                    <a href="{{ route('shifts.index') }}" class="h-10 px-3 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-extrabold text-xs rounded-xl flex items-center justify-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- ── DATA TABLE ── --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-400 dark:text-slate-500 uppercase text-[10px] font-extrabold border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="py-3.5 px-4">Shift &amp; Cabang</th>
                            <th class="py-3.5 px-4">Kasir</th>
                            <th class="py-3.5 px-4">Waktu Buka / Tutup</th>
                            <th class="py-3.5 px-4">Modal Awal</th>
                            <th class="py-3.5 px-4">Ekspektasi Sistem</th>
                            <th class="py-3.5 px-4">Uang Fisik (Kasir)</th>
                            <th class="py-3.5 px-4 text-center">Selisih Kas</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                        @forelse($shifts as $s)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-black text-slate-900 dark:text-white">Shift #{{ $s->id }}</div>
                                    <div class="text-2xs text-slate-400 font-bold">{{ $s->branch?->name ?? 'Pusat' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-primary/10 text-primary flex items-center justify-center font-black text-2xs uppercase">
                                            {{ substr($s->cashier?->name ?? 'K', 0, 1) }}
                                        </div>
                                        <span>{{ $s->cashier?->name ?? 'Kasir' }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-xs">{{ $s->opened_at->format('d/m/Y H:i') }}</div>
                                    <div class="text-2xs text-slate-400 font-normal">
                                        {{ $s->closed_at ? $s->closed_at->format('d/m/Y H:i') : 'Masih Buka' }}
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    Rp {{ number_format($s->opening_cash, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="text-slate-900 dark:text-white font-black">
                                        Rp {{ number_format($s->closing_cash_system ?? 0, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($s->status === 'CLOSED')
                                        <span class="text-slate-900 dark:text-white font-black">
                                            Rp {{ number_format($s->closing_cash_actual ?? 0, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic text-2xs">- Belum Closing -</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($s->status === 'CLOSED')
                                        @php $diff = (float) $s->cash_difference; @endphp
                                        @if($diff == 0)
                                            <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 rounded-lg text-2xs font-extrabold">
                                                Match (Rp 0)
                                            </span>
                                        @elseif($diff > 0)
                                            <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-200 rounded-lg text-2xs font-extrabold">
                                                +Rp {{ number_format($diff, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 rounded-lg text-2xs font-extrabold">
                                                -Rp {{ number_format(abs($diff), 0, ',', '.') }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 text-2xs">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($s->status === 'OPEN')
                                        <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full text-2xs font-extrabold animate-pulse">
                                            ● AKTIF
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-full text-2xs font-extrabold">
                                            CLOSED
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('shifts.show', $s->id) }}" class="p-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-xl transition-all" title="Detail Rekapitulasi Audit">
                                            <span class="material-symbols-outlined text-sm">visibility</span>
                                        </a>
                                        <a href="{{ route('pos.shift.summary-pdf', $s->id) }}" target="_blank" class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-xl transition-all" title="Cetak Struk Shift PDF">
                                            <span class="material-symbols-outlined text-sm">print</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-3xl mb-1 text-slate-300">point_of_sale</span>
                                    <p class="text-xs font-bold">Belum ada data rekapitulasi shift kasir.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($shifts->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $shifts->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
