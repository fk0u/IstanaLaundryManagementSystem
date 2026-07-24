<x-app-layout>
    <div class="flex flex-col gap-6">
        <x-page-header title="Manajemen Promosi & Kupon" :breadcrumbs="['Promosi' => '/promotions']" />

        <x-card title="Kupon Promo Aktif">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Promo</th>
                            <th class="py-3 px-4">Kode Kupon</th>
                            <th class="py-3 px-4">Tipe Diskon</th>
                            <th class="py-3 px-4">Nilai</th>
                            <th class="py-3 px-4">Min. Transaksi</th>
                            <th class="py-3 px-4">Masa Berlaku</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($promotions as $promo)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $promo->name }}
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 bg-orange-50 dark:bg-slate-800 text-primary dark:text-orange-400 rounded-lg font-mono font-bold text-xs">
                                        {{ $promo->code ?? 'AUTOMATIC' }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 uppercase font-semibold text-slate-600 dark:text-slate-400">
                                    {{ $promo->type }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $promo->type === 'percent' ? $promo->value . '%' : 'Rp ' . number_format($promo->value, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400 font-semibold">
                                    Rp {{ number_format($promo->min_transaction, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ \Carbon\Carbon::parse($promo->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($promo->end_date)->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">Belum ada promosi yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $promotions->links() }}
            </div>
        </x-card>
    </div>
</x-app-layout>
