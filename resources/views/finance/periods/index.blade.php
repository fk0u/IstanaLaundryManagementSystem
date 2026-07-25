<x-app-layout>
    <div class="flex flex-col gap-6">
        <x-page-header title="Periode Akuntansi (Accounting Periods)" :breadcrumbs="['Keuangan' => '#', 'Periode' => '/finance/periods']" />

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-4" />
        @endif

        <x-card title="Daftar Periode Pembukuan Bulanan Cabang">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Tahun / Bulan</th>
                            <th class="py-3 px-4">Cabang</th>
                            <th class="py-3 px-4">Tanggal Penutupan</th>
                            <th class="py-3 px-4">Ditutup Oleh</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($periods as $period)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $period->year }} / {{ str_pad($period->month, 2, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700 dark:text-slate-350">
                                    {{ $period->branch?->name }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ $period->closed_at ? $period->closed_at->format('d M Y H:i') : '-' }}
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400">
                                    {{ $period->closedBy?->name ?? '-' }}
                                </td>
                                <td class="py-4 px-4">
                                    @if ($period->status === 'closed')
                                        <x-badge type="danger">Closed (Terkunci)</x-badge>
                                    @else
                                        <x-badge type="success">Open (Aktif)</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    @if ($period->status === 'open')
                                        <form action="{{ route('finance.periods.close', $period->id) }}" method="POST" onsubmit="return confirm('PERINGATAN: Menutup periode akuntansi akan mengunci semua transaksi dan mencegah posting jurnal baru pada bulan ini. Apakah Anda yakin?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-750 text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all">
                                                Tutup Periode
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-700 material-symbols-outlined text-base cursor-not-allowed">lock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">Belum ada periode akuntansi terbuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $periods->links() }}
            </div>
        </x-card>
    </div>
</x-app-layout>
