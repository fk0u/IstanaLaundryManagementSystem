<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6">
        <x-page-header title="Detail Aset & Jadwal Depresiasi" :breadcrumbs="['Aset Tetap' => '/assets', $asset->asset_code => '/assets/' . $asset->id]" />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Asset Overview (4 cols) -->
            <div class="lg:col-span-4">
                <x-card title="Informasi Aset">
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-2xs font-bold text-slate-400 uppercase">Nama Aset</span>
                            <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">{{ $asset->name }}</h3>
                            <span class="text-2xs text-slate-400 font-mono">{{ $asset->asset_code }}</span>
                        </div>
                        <div class="pt-2 border-t space-y-1.5">
                            <div class="flex justify-between"><span class="text-slate-400">Kategori:</span><span class="font-bold text-slate-700 dark:text-slate-300">{{ $asset->category }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Cabang:</span><span class="font-bold text-slate-700 dark:text-slate-300">{{ $asset->branch?->name }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Tgl Perolehan:</span><span class="font-bold text-slate-700 dark:text-slate-300">{{ $asset->acquisition_date?->format('d/m/Y') }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Umur Manfaat:</span><span class="font-bold text-slate-700 dark:text-slate-300">{{ $asset->useful_life_months }} Bulan</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Metode:</span><span class="font-bold text-primary uppercase">{{ str_replace('_', ' ', $asset->depreciation_method) }}</span></div>
                        </div>
                        <div class="pt-3 border-t space-y-1.5 font-mono">
                            <div class="flex justify-between"><span class="text-slate-400 font-sans">Harga Perolehan:</span><span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between text-rose-600"><span class="font-sans">Nilai Sisa:</span><span class="font-bold">Rp {{ number_format($asset->salvage_value, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between text-emerald-600 font-bold text-sm"><span class="font-sans">Nilai Buku:</span><span>Rp {{ number_format($asset->book_value, 0, ',', '.') }}</span></div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Depreciation Schedule Table (8 cols) -->
            <div class="lg:col-span-8">
                <x-card title="Tabel Jadwal Depresiasi Bulanan">
                    <div class="overflow-x-auto max-h-[500px]">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="sticky top-0 bg-slate-50 dark:bg-slate-900 border-b border-slate-200">
                                <tr class="text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Periode</th>
                                    <th class="py-2.5 px-3 text-right">Depresiasi Bulanan</th>
                                    <th class="py-2.5 px-3 text-right">Akumulasi Depresiasi</th>
                                    <th class="py-2.5 px-3 text-right">Nilai Buku</th>
                                    <th class="py-2.5 px-3 text-center">Status Jurnal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850 font-mono">
                                @foreach($asset->depreciationSchedules as $sch)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                        <td class="py-2.5 px-3 font-sans font-bold text-slate-700 dark:text-slate-300">
                                            {{ \Carbon\Carbon::parse($sch->period_date)->format('M Y') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-right text-slate-800 dark:text-slate-200">
                                            Rp {{ number_format($sch->depreciation_amount, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-right text-rose-600">
                                            Rp {{ number_format($sch->accumulated, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-right text-emerald-600 font-bold">
                                            Rp {{ number_format($sch->book_value, 0, ',', '.') }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-sans">
                                            @if($sch->is_posted)
                                                <x-badge type="success">Posted</x-badge>
                                            @else
                                                <x-badge type="gray">Scheduled</x-badge>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
