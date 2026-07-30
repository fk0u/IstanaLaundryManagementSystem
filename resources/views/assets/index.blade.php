<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6" x-data="{ showAddAsset: false }">
        <x-page-header title="Aset Tetap & Depresiasi (Fixed Assets)" :breadcrumbs="['Aset Tetap' => '/assets']" />

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif

        <!-- Filter Bar & Action -->
        <div class="flex flex-wrap justify-between items-center gap-3">
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                <form method="GET" action="{{ route('assets.index') }}" class="flex items-center gap-3">
                    <label class="text-2xs font-bold text-slate-400 uppercase">Filter Cabang:</label>
                    <select name="branch_id" onchange="this.form.submit()" class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold px-2">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </form>
            @else
                <div></div>
            @endif
            <div class="flex items-center gap-2">
                <a href="{{ route('assets.export', ['branch_id' => request('branch_id')]) }}" 
                   class="btn-touch px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm cursor-pointer transition-colors">
                    <span class="material-symbols-outlined text-base">download</span> Ekspor CSV
                </a>
                <button type="button" @click="showAddAsset = true" class="btn-touch px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm cursor-pointer">
                    <span class="material-symbols-outlined text-base">add_home_work</span> Tambah Aset Tetap
                </button>
            </div>
        </div>

        <!-- Summary Stats -->
        @php
            $totalAsetNilai = $assets->sum('acquisition_cost');
            $totalNilaiBuku = $assets->sum('book_value');
            $totalDepresiasi = $assets->sum('accumulated_depreciation');
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-stat-card title="Total Aset" :value="$assets->total() . ' Unit'" icon="inventory" description="Terdaftar di sistem" />
            <x-stat-card title="Total Perolehan" :value="'Rp ' . number_format($totalAsetNilai, 0, ',', '.')" icon="account_balance" description="Harga beli semua aset" />
            <x-stat-card title="Total Depresiasi" :value="'Rp ' . number_format($totalDepresiasi, 0, ',', '.')" icon="trending_down" trendType="danger" description="Nilai akumulasi susut" />
            <x-stat-card title="Total Nilai Buku" :value="'Rp ' . number_format($totalNilaiBuku, 0, ',', '.')" icon="price_check" trendType="success" description="Nilai buku saat ini" />
        </div>

        <x-card title="Daftar Aset Tetap">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Kode / Nama Aset</th>
                            <th class="py-2.5 px-3">Kategori & Cabang</th>
                            <th class="py-2.5 px-3">Tgl Beli</th>
                            <th class="py-2.5 px-3 text-center">Kondisi</th>
                            <th class="py-2.5 px-3">Maintenance Terakhir</th>
                            <th class="py-2.5 px-3 text-right">Harga Beli</th>
                            <th class="py-2.5 px-3 text-right">Depresiasi</th>
                            <th class="py-2.5 px-3 text-right">Nilai Buku</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($assets as $ast)
                            @php
                                $conditionColor = match($ast->condition ?? 'good') {
                                    'good' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20',
                                    'fair' => 'text-amber-600 bg-amber-50 dark:bg-amber-950/20',
                                    'poor' => 'text-rose-600 bg-rose-50 dark:bg-rose-950/20',
                                    'scrapped' => 'text-slate-500 bg-slate-100 dark:bg-slate-800',
                                    default => 'text-emerald-600 bg-emerald-50',
                                };
                                $conditionLabel = match($ast->condition ?? 'good') {
                                    'good' => 'Baik',
                                    'fair' => 'Cukup',
                                    'poor' => 'Rusak',
                                    'scrapped' => 'Afkir',
                                    default => 'Baik',
                                };
                                $ageMonths = $ast->age_in_months;
                                $depProgress = min(100, $ast->depreciation_progress);
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $ast->name }}</span>
                                    <span class="text-2xs text-slate-400 font-mono">{{ $ast->asset_code }}</span>
                                    @if($ast->serial_number)
                                        <span class="text-2xs text-slate-400 block">SN: {{ $ast->serial_number }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <span class="block text-slate-600 dark:text-slate-400 font-medium">{{ $ast->category }}</span>
                                    <span class="text-2xs text-primary font-semibold flex items-center gap-0.5">
                                        <span class="material-symbols-outlined text-xs">store</span>
                                        {{ $ast->branch?->name ?? '—' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-500">
                                    <span class="block">{{ $ast->acquisition_date?->format('d M Y') }}</span>
                                    <span class="text-2xs text-slate-400">{{ $ageMonths }} bulan lalu</span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-2xs font-bold {{ $conditionColor }}">
                                        {{ $conditionLabel }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    @if($ast->last_maintenance_date)
                                        <span class="block text-slate-700 dark:text-slate-300 font-medium">
                                            {{ $ast->last_maintenance_date->format('d M Y') }}
                                        </span>
                                        <span class="text-2xs text-slate-400">
                                            {{ $ast->last_maintenance_date->diffForHumans() }}
                                        </span>
                                        @if($ast->next_maintenance_date)
                                            <span class="text-2xs {{ $ast->next_maintenance_date->isPast() ? 'text-rose-600 font-bold' : 'text-slate-400' }} block">
                                                Berikutnya: {{ $ast->next_maintenance_date->format('d M Y') }}
                                                @if($ast->next_maintenance_date->isPast())
                                                    ⚠️
                                                @endif
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-2xs text-slate-300 dark:text-slate-600 italic">Belum ada catatan</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($ast->acquisition_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <span class="block font-mono text-rose-600 font-bold">
                                        Rp {{ number_format($ast->accumulated_depreciation, 0, ',', '.') }}
                                    </span>
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1 mt-1">
                                        <div class="bg-rose-400 h-1 rounded-full transition-all" style="width: {{ $depProgress }}%"></div>
                                    </div>
                                    <span class="text-2xs text-slate-400">{{ $depProgress }}% tersusut</span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-black text-emerald-600">
                                    Rp {{ number_format($ast->book_value, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('assets.show', $ast->id) }}" class="btn-touch px-3 py-1.5 bg-orange-50 dark:bg-slate-800 text-primary text-2xs font-bold rounded-lg inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">calendar_month</span> Detail & Jadwal
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-slate-400">Belum ada aset tetap terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $assets->links() }}
            </div>
        </x-card>

        <!-- Add Asset Modal -->
        <div x-show="showAddAsset" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-5 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Tambah Aset Tetap Baru</h3>
                    <button type="button" @click="showAddAsset = false" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form action="{{ route('assets.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Kode Aset</label>
                            <input type="text" name="asset_code" required placeholder="AST-MC-001..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Nama Aset</label>
                            <input type="text" name="name" required placeholder="Mesin Cuci SpeedQueen 15kg..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Kategori</label>
                            <select name="category" required class="w-full h-9 px-2 rounded-xl border text-xs">
                                <option value="Mesin & Peralatan">Mesin & Peralatan</option>
                                <option value="Kendaraan">Kendaraan Operasional</option>
                                <option value="Bangunan & Renovasi">Bangunan & Renovasi</option>
                                <option value="Elektronik & IT">Elektronik & IT</option>
                                <option value="Perabotan & Inventaris">Perabotan & Inventaris</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Cabang</label>
                            <select name="branch_id" required class="w-full h-9 px-2 rounded-xl border text-xs">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">No. Seri / SN</label>
                            <input type="text" name="serial_number" placeholder="Opsional..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Supplier</label>
                            <input type="text" name="supplier" placeholder="Nama supplier/vendor..." class="w-full h-9 px-3 rounded-xl border text-xs">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Tgl Perolehan</label>
                            <input type="date" name="acquisition_date" required value="{{ date('Y-m-d') }}" class="w-full h-9 px-2 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Kondisi Awal</label>
                            <select name="condition" required class="w-full h-9 px-2 rounded-xl border text-xs">
                                <option value="good" selected>Baik</option>
                                <option value="fair">Cukup</option>
                                <option value="poor">Rusak</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Harga Beli (Rp)</label>
                            <input type="number" name="acquisition_cost" required placeholder="25000000..." class="w-full h-9 px-2 rounded-xl border text-xs font-bold">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Nilai Sisa (Salvage)</label>
                            <input type="number" name="salvage_value" required value="0" class="w-full h-9 px-2 rounded-xl border text-xs">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Masa Manfaat (Bulan)</label>
                            <input type="number" name="useful_life_months" required value="48" class="w-full h-9 px-2 rounded-xl border text-xs font-bold">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Metode Depresiasi</label>
                            <select name="depreciation_method" required class="w-full h-9 px-2 rounded-xl border text-xs">
                                <option value="straight_line">Garis Lurus (Straight Line)</option>
                                <option value="declining_balance">Saldo Menurun (Declining Balance)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Catatan</label>
                        <textarea name="notes" rows="2" placeholder="Catatan kondisi, spesifikasi, dll..." class="w-full px-3 py-2 rounded-xl border text-xs"></textarea>
                    </div>
                    <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5">Simpan Aset Tetap</button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
