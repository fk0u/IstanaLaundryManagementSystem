<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6">
        <x-page-header title="Detail Aset & Jadwal Depresiasi" :breadcrumbs="['Aset Tetap' => route('assets.index'), $asset->asset_code => '#']" />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Asset Overview (4 cols) -->
            <div class="lg:col-span-4 flex flex-col gap-4">
                <x-card title="Informasi Aset">
                    <div class="space-y-3 text-xs">
                        <div class="flex items-start gap-3">
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-primary text-2xl">home_work</span>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $asset->name }}</h3>
                                <span class="text-2xs text-slate-400 font-mono">{{ $asset->asset_code }}</span>
                                @if($asset->serial_number)
                                    <span class="block text-2xs text-slate-400">SN: {{ $asset->serial_number }}</span>
                                @endif
                            </div>
                        </div>

                        @php
                            $conditionColor = match($asset->condition ?? 'good') {
                                'good' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 border-emerald-100',
                                'fair' => 'text-amber-600 bg-amber-50 dark:bg-amber-950/20 border-amber-100',
                                'poor' => 'text-rose-600 bg-rose-50 dark:bg-rose-950/20 border-rose-100',
                                'scrapped' => 'text-slate-500 bg-slate-100 dark:bg-slate-800 border-slate-200',
                                default => 'text-emerald-600 bg-emerald-50',
                            };
                            $conditionLabel = match($asset->condition ?? 'good') {
                                'good' => 'Kondisi Baik',
                                'fair' => 'Kondisi Cukup',
                                'poor' => 'Perlu Perbaikan',
                                'scrapped' => 'Sudah Diafkir',
                                default => 'Kondisi Baik',
                            };
                        @endphp

                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-2xs font-bold border {{ $conditionColor }}">
                                {{ $conditionLabel }}
                            </span>
                            @if(!$asset->is_active)
                                <span class="px-2.5 py-1 rounded-full text-2xs font-bold text-slate-500 bg-slate-100 border border-slate-200">
                                    Tidak Aktif
                                </span>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-1.5">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Kategori:</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $asset->category }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Cabang:</span>
                                <span class="font-bold text-primary">{{ $asset->branch?->name ?? '—' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Dibeli:</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $asset->acquisition_date?->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Usia Aset:</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $asset->age_in_months }} bulan</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Umur Manfaat:</span>
                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $asset->useful_life_months }} Bulan</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Metode:</span>
                                <span class="font-bold text-primary uppercase text-2xs">{{ str_replace('_', ' ', $asset->depreciation_method) }}</span>
                            </div>
                            @if($asset->supplier)
                                <div class="flex justify-between">
                                    <span class="text-slate-400">Supplier:</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $asset->supplier }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-1.5 font-mono">
                            <div class="flex justify-between">
                                <span class="text-slate-400 font-sans">Harga Perolehan:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-rose-600">
                                <span class="font-sans">Nilai Sisa (Salvage):</span>
                                <span class="font-bold">Rp {{ number_format($asset->salvage_value, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-rose-500">
                                <span class="font-sans">Akumulasi Depresiasi:</span>
                                <span class="font-bold">Rp {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-emerald-600 font-bold text-sm">
                                <span class="font-sans">Nilai Buku Sekarang:</span>
                                <span>Rp {{ number_format($asset->book_value, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Depreciation Progress Bar -->
                        @php $depProgress = min(100, $asset->depreciation_progress); @endphp
                        <div class="pt-2 border-t border-slate-100 dark:border-slate-800">
                            <div class="flex justify-between text-2xs mb-1">
                                <span class="text-slate-400 font-bold">Progress Depresiasi</span>
                                <span class="font-bold text-rose-600">{{ $depProgress }}%</span>
                            </div>
                            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2">
                                <div class="bg-gradient-to-r from-emerald-400 to-rose-500 h-2 rounded-full transition-all" style="width: {{ $depProgress }}%"></div>
                            </div>
                            <div class="flex justify-between text-2xs mt-1">
                                <span class="text-emerald-600">0%</span>
                                <span class="text-rose-600">Habis di bulan ke-{{ $asset->useful_life_months }}</span>
                            </div>
                        </div>
                    </div>
                </x-card>

                <!-- Maintenance Info Card -->
                <x-card title="Riwayat Maintenance">
                    <div class="space-y-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-500 text-xl">build</span>
                            <div>
                                <span class="block text-2xs text-slate-400 font-bold uppercase">Maintenance Terakhir</span>
                                @if($asset->last_maintenance_date)
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $asset->last_maintenance_date->format('d M Y') }}</span>
                                    <span class="text-2xs text-slate-400 block">({{ $asset->last_maintenance_date->diffForHumans() }})</span>
                                @else
                                    <span class="text-slate-400 italic">Belum ada catatan maintenance</span>
                                @endif
                            </div>
                        </div>
                        @if($asset->next_maintenance_date)
                            <div class="flex items-center gap-2 {{ $asset->next_maintenance_date->isPast() ? 'bg-rose-50 dark:bg-rose-950/20 border border-rose-100 rounded-xl p-2' : '' }}">
                                <span class="material-symbols-outlined {{ $asset->next_maintenance_date->isPast() ? 'text-rose-500' : 'text-slate-400' }} text-xl">event</span>
                                <div>
                                    <span class="block text-2xs text-slate-400 font-bold uppercase">Maintenance Berikutnya</span>
                                    <span class="font-bold {{ $asset->next_maintenance_date->isPast() ? 'text-rose-600' : 'text-slate-800 dark:text-slate-200' }}">
                                        {{ $asset->next_maintenance_date->format('d M Y') }}
                                        @if($asset->next_maintenance_date->isPast())
                                            ⚠️ Sudah Lewat!
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif
                        @if($asset->maintenance_notes)
                            <div class="bg-amber-50 dark:bg-amber-950/10 border border-amber-100 dark:border-amber-900/20 rounded-xl p-2.5">
                                <span class="text-2xs font-bold text-amber-700 dark:text-amber-400 block mb-1">Catatan Maintenance</span>
                                <p class="text-2xs text-slate-600 dark:text-slate-400">{{ $asset->maintenance_notes }}</p>
                            </div>
                        @endif
                        @if($asset->notes)
                            <div class="bg-slate-50 dark:bg-slate-800/40 rounded-xl p-2.5">
                                <span class="text-2xs font-bold text-slate-500 block mb-1">Catatan Umum</span>
                                <p class="text-2xs text-slate-600 dark:text-slate-400">{{ $asset->notes }}</p>
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>

            <!-- Depreciation Schedule Table (8 cols) -->
            <div class="lg:col-span-8">
                <x-card title="Tabel Jadwal Depresiasi Bulanan">
                    @php
                        $today = now()->startOfMonth();
                    @endphp
                    <div class="overflow-x-auto max-h-[600px]">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead class="sticky top-0 bg-slate-50 dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
                                <tr class="text-slate-400 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">#</th>
                                    <th class="py-2.5 px-3">Periode</th>
                                    <th class="py-2.5 px-3 text-right">Depresiasi Bulanan</th>
                                    <th class="py-2.5 px-3 text-right">Akumulasi</th>
                                    <th class="py-2.5 px-3 text-right">Nilai Buku</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 dark:divide-slate-850 font-mono">
                                @foreach($asset->depreciationSchedules as $idx => $sch)
                                    @php
                                        $periodDate = \Carbon\Carbon::parse($sch->period_date);
                                        $isCurrentMonth = $periodDate->isSameMonth($today);
                                        $isPast = $periodDate->isPast() || $periodDate->isSameMonth($today);
                                    @endphp
                                    <tr class="{{ $isCurrentMonth ? 'bg-orange-50/50 dark:bg-orange-950/10' : 'hover:bg-slate-50/50 dark:hover:bg-slate-900/30' }}">
                                        <td class="py-2.5 px-3 text-slate-400 font-sans">{{ $idx + 1 }}</td>
                                        <td class="py-2.5 px-3 font-sans font-bold {{ $isCurrentMonth ? 'text-primary' : 'text-slate-700 dark:text-slate-300' }}">
                                            {{ $periodDate->translatedFormat('F Y') }}
                                            @if($isCurrentMonth)
                                                <span class="ml-1 px-1.5 py-0.5 bg-primary/10 text-primary text-[9px] font-extrabold rounded-full">SEKARANG</span>
                                            @endif
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
                                            @elseif($isPast)
                                                <x-badge type="warning">Belum Posted</x-badge>
                                            @else
                                                <x-badge type="gray">Scheduled</x-badge>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between text-xs text-slate-500">
                        <span>Total periode: {{ $asset->depreciationSchedules->count() }} bulan</span>
                        <span>Nilai akhir buku: Rp {{ number_format($asset->salvage_value, 0, ',', '.') }}</span>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
