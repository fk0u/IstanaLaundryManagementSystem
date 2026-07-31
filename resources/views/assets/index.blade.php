<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6" x-data="{
        activeTab: 'catalog',
        showAddAsset: false,
        activeMaintenanceAsset: null,
        switchTab(tab) {
            this.activeTab = tab;
            this.$nextTick(() => {
                if (tab === 'depreciation' || tab === 'analytics') {
                    if (window.renderAssetCharts) {
                        window.renderAssetCharts();
                    }
                }
            });
        }
    }">
        <x-page-header title="Aset Tetap & Depresiasi (Fixed Assets)" :breadcrumbs="['Aset Tetap' => '/assets']" />

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif

        <!-- Executive Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <x-stat-card title="Total Unit Aset" :value="$assets->total() . ' Unit'" icon="inventory" description="Terdaftar di sistem" />
            <x-stat-card title="Total Perolehan (Capex)" :value="'Rp ' . number_format($totalCost, 0, ',', '.')" icon="account_balance" description="Harga beli semua aset" />
            <x-stat-card title="Akumulasi Depresiasi" :value="'Rp ' . number_format($totalDepreciation, 0, ',', '.')" icon="trending_down" trendType="danger" description="Nilai penyusutan terpakai" />
            <x-stat-card title="Total Nilai Buku" :value="'Rp ' . number_format($totalBookValue, 0, ',', '.')" icon="price_check" trendType="success" description="Nilai ekuitas aset saat ini" />
        </div>

        <!-- Sleek Tabpane Navigation Header -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
            <div class="flex items-center gap-1.5 overflow-x-auto min-w-max p-1 bg-slate-100 dark:bg-slate-900 rounded-2xl">
                <button type="button" @click="switchTab('catalog')" :class="activeTab === 'catalog' ? 'bg-primary text-white shadow-md shadow-primary/20 scale-[1.02]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">inventory_2</span>
                    <span>Katalog & Daftar Aset</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black" :class="activeTab === 'catalog' ? 'bg-white/20 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300'">{{ $assets->total() }}</span>
                </button>

                <button type="button" @click="switchTab('depreciation')" :class="activeTab === 'depreciation' ? 'bg-primary text-white shadow-md shadow-primary/20 scale-[1.02]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">show_chart</span>
                    <span>Grafik Depresiasi</span>
                </button>

                <button type="button" @click="switchTab('maintenance')" :class="activeTab === 'maintenance' ? 'bg-primary text-white shadow-md shadow-primary/20 scale-[1.02]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">build</span>
                    <span>Maintenance & Servis</span>
                    @if($urgentMaintenanceAssets->count() > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white animate-pulse">{{ $urgentMaintenanceAssets->count() }} Urgent</span>
                    @endif
                </button>

                <button type="button" @click="switchTab('analytics')" :class="activeTab === 'analytics' ? 'bg-primary text-white shadow-md shadow-primary/20 scale-[1.02]' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="px-4 py-2 rounded-xl font-bold text-xs flex items-center gap-2 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-base">pie_chart</span>
                    <span>Portfolio & Analytics</span>
                </button>
            </div>

            <!-- Tab Context Action Buttons -->
            <div class="flex items-center gap-2">
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance']))
                    <form method="GET" action="{{ route('assets.index') }}" class="flex items-center gap-2">
                        <select name="branch_id" onchange="this.form.submit()" class="h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold px-2">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>Cabang {{ $b->name }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif

                <div class="flex items-center gap-1">
                    <a href="{{ route('assets.export.pdf', ['branch_id' => request('branch_id')]) }}" target="_blank"
                       class="btn-touch px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl flex items-center gap-1 shadow-sm cursor-pointer transition-colors"
                       title="Unduh PDF Resmi">
                        <span class="material-symbols-outlined text-sm">picture_as_pdf</span> PDF
                    </a>
                    <a href="{{ route('assets.export.xlsx', ['branch_id' => request('branch_id')]) }}" 
                       class="btn-touch px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl flex items-center gap-1 shadow-sm cursor-pointer transition-colors"
                       title="Ekspor Excel Spreadsheet">
                        <span class="material-symbols-outlined text-sm">table_chart</span> Excel
                    </a>
                    <button type="button" @click="showAddAsset = true" class="btn-touch px-3.5 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center gap-1 shadow-sm cursor-pointer ml-1">
                        <span class="material-symbols-outlined text-sm">add_home_work</span> Tambah Aset
                    </button>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 1: KATALOG & DAFTAR ASET TETAP ==================== -->
        <div x-show="activeTab === 'catalog'" class="space-y-4" x-cloak>
            <x-card title="Katalog Lengkap Aset Tetap">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-2.5 px-3">Kode / Nama Aset</th>
                                <th class="py-2.5 px-3">Kategori & Cabang</th>
                                <th class="py-2.5 px-3">Tgl Beli & Usia</th>
                                <th class="py-2.5 px-3 text-center">Kondisi Fisik</th>
                                <th class="py-2.5 px-3 text-right">Harga Beli</th>
                                <th class="py-2.5 px-3 text-right">Penyusutan</th>
                                <th class="py-2.5 px-3 text-right">Nilai Buku</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                            @forelse($assets as $ast)
                                @php
                                    $conditionColor = match($ast->condition ?? 'good') {
                                        'good' => 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/20 border-emerald-100',
                                        'fair' => 'text-amber-600 bg-amber-50 dark:bg-amber-950/20 border-amber-100',
                                        'poor' => 'text-rose-600 bg-rose-50 dark:bg-rose-950/20 border-rose-100',
                                        'scrapped' => 'text-slate-500 bg-slate-100 dark:bg-slate-800 border-slate-200',
                                        default => 'text-emerald-600 bg-emerald-50',
                                    };
                                    $conditionLabel = match($ast->condition ?? 'good') {
                                        'good' => 'Baik',
                                        'fair' => 'Cukup',
                                        'poor' => 'Perlu Perbaikan',
                                        'scrapped' => 'Afkir',
                                        default => 'Baik',
                                    };
                                    $ageMonths = $ast->age_in_months;
                                    $depProgress = min(100, $ast->depreciation_progress);
                                @endphp
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                    <td class="py-3 px-3">
                                        <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $ast->name }}</span>
                                        <span class="text-2xs text-slate-400 font-mono">Kode: {{ $ast->asset_code }}</span>
                                        @if($ast->serial_number)
                                            <span class="text-2xs text-slate-400 block font-mono">SN: {{ $ast->serial_number }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="block text-slate-700 dark:text-slate-300 font-bold text-2xs">{{ $ast->category }}</span>
                                        <span class="text-2xs text-primary font-semibold flex items-center gap-0.5 mt-0.5">
                                            <span class="material-symbols-outlined text-xs">store</span>
                                            Cabang {{ $ast->branch?->name ?? 'Konsolidasi' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-500">
                                        <span class="block font-bold text-slate-700 dark:text-slate-300">{{ $ast->acquisition_date?->format('d/m/Y') }}</span>
                                        <span class="text-2xs text-slate-400">{{ $ageMonths }} bulan ({{ $ast->useful_life_months }} bln max)</span>
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-2xs font-extrabold border {{ $conditionColor }}">
                                            {{ $conditionLabel }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                        Rp {{ number_format($ast->acquisition_cost, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <span class="block font-mono text-rose-600 font-bold">
                                            Rp {{ number_format($ast->accumulated_depreciation, 0, ',', '.') }}
                                        </span>
                                        <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-1.5 mt-1 overflow-hidden">
                                            <div class="bg-gradient-to-r from-emerald-400 via-amber-400 to-rose-500 h-1.5 rounded-full transition-all" style="width: {{ $depProgress }}%"></div>
                                        </div>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $depProgress }}% tersusut</span>
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-black text-emerald-600">
                                        Rp {{ number_format($ast->book_value, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('assets.show', $ast->id) }}" class="p-1.5 rounded-lg bg-orange-50 text-primary dark:bg-slate-800 hover:bg-orange-100 transition-colors" title="Jadwal Depresiasi & Detail">
                                                <span class="material-symbols-outlined text-base">calendar_month</span>
                                            </a>
                                            <button type="button" @click="activeMaintenanceAsset = @js($ast)" class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950/30 text-amber-600 hover:bg-amber-100 transition-colors" title="Update Log Maintenance & Kondisi">
                                                <span class="material-symbols-outlined text-base">build</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400">Belum ada aset tetap terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($assets->hasPages())
                    <div class="mt-3">
                        {{ $assets->links() }}
                    </div>
                @endif
            </x-card>
        </div>

        <!-- ==================== TAB 2: GRAFIK & SIMULASI DEPRESIASI ==================== -->
        <div x-show="activeTab === 'depreciation'" class="space-y-4" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Bar Chart: Capex vs Book Value per Top Asset (7 cols) -->
                <div class="lg:col-span-7">
                    <x-card title="Visualisasi Nilai Perolehan vs Nilai Buku Aset Utama">
                        <div class="h-72 relative">
                            <canvas id="assetDepreciationChart"></canvas>
                        </div>
                    </x-card>
                </div>

                <!-- Line Chart: Monthly Depreciation Forecast (5 cols) -->
                <div class="lg:col-span-5">
                    <x-card title="Proyeksi Beban Penyusutan Bulanan (Tahun {{ date('Y') }})">
                        <div class="h-72 relative">
                            <canvas id="assetMonthlyDepreciationChart"></canvas>
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- Proyeksi Rincian Beban Depresiasi Bulanan Table -->
            <x-card title="Tabel Proyeksi Beban Penyusutan Aset 12 Bulan ({{ date('Y') }})">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-2.5 px-3">Bulan</th>
                                <th class="py-2.5 px-3 text-right">Proyeksi Beban Depresiasi (Rp)</th>
                                <th class="py-2.5 px-3 text-center">Status Periode</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-850 font-mono">
                            @foreach($monthlyDepreciationForecast as $idx => $mDep)
                                <tr class="{{ ($idx + 1) == date('n') ? 'bg-orange-50/60 dark:bg-orange-950/20 font-bold' : 'hover:bg-slate-50/50 dark:hover:bg-slate-900/30' }}">
                                    <td class="py-2.5 px-3 font-sans font-bold text-slate-800 dark:text-slate-200">
                                        Bulan {{ $mDep['month_name'] }} {{ date('Y') }}
                                        @if(($idx + 1) == date('n'))
                                            <span class="ml-1.5 px-2 py-0.5 bg-primary text-white text-[10px] font-black rounded-full">Bulan Ini</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-right text-rose-600 font-bold">
                                        Rp {{ number_format($mDep['amount'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 px-3 text-center font-sans">
                                        @if(($idx + 1) < date('n'))
                                            <span class="px-2 py-0.5 rounded-full text-2xs font-extrabold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">Terlewati</span>
                                        @elseif(($idx + 1) == date('n'))
                                            <span class="px-2 py-0.5 rounded-full text-2xs font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-400">Aktif Diproses</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-2xs font-extrabold bg-blue-50 text-blue-700 dark:bg-blue-950/30 dark:text-blue-400">Terjadwal</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- ==================== TAB 3: MAINTENANCE & SERVIS ==================== -->
        <div x-show="activeTab === 'maintenance'" class="space-y-4" x-cloak>
            <!-- Maintenance Metrics Bar -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-3.5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm space-y-1">
                    <span class="text-2xs font-bold text-slate-400 uppercase">Jatuh Tempo Urgent</span>
                    <p class="text-2xl font-black text-rose-600 font-mono">{{ $urgentMaintenanceAssets->count() }} Aset</p>
                    <p class="text-2xs text-slate-500">Perlu perbaikan / lewat tanggal</p>
                </div>
                <div class="p-3.5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm space-y-1">
                    <span class="text-2xs font-bold text-slate-400 uppercase">Servis 30 Hari Kedepan</span>
                    <p class="text-2xl font-black text-amber-600 font-mono">{{ $maintenanceUpcoming30Days->count() }} Aset</p>
                    <p class="text-2xs text-slate-500">Jadwal pemeliharaan rutin</p>
                </div>
                <div class="p-3.5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm space-y-1">
                    <span class="text-2xs font-bold text-slate-400 uppercase">Kondisi Baik</span>
                    <p class="text-2xl font-black text-emerald-600 font-mono">{{ $conditionCounts['good'] }} Unit</p>
                    <p class="text-2xs text-slate-500">Siap operasional 100%</p>
                </div>
                <div class="p-3.5 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm space-y-1">
                    <span class="text-2xs font-bold text-slate-400 uppercase">Sudah Afkir</span>
                    <p class="text-2xl font-black text-slate-500 font-mono">{{ $conditionCounts['scrapped'] }} Unit</p>
                    <p class="text-2xs text-slate-500">Tidak berfungsi</p>
                </div>
            </div>

            @if($urgentMaintenanceAssets->count() > 0)
                <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 rounded-2xl space-y-3">
                    <div class="flex items-center gap-2 text-rose-700 dark:text-rose-400 font-extrabold text-xs uppercase tracking-wider">
                        <span class="material-symbols-outlined text-lg animate-bounce">warning</span>
                        <span>Peringatan Maintenance Urgent & Servis Jatuh Tempo ({{ $urgentMaintenanceAssets->count() }} Aset)</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($urgentMaintenanceAssets as $uAst)
                            <div class="bg-white dark:bg-slate-900 p-3 rounded-xl border border-rose-100 dark:border-rose-900/40 flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200 block">{{ $uAst->name }}</span>
                                    <span class="text-2xs text-slate-400 font-mono">{{ $uAst->asset_code }} • Cabang {{ $uAst->branch?->name }}</span>
                                    <div class="mt-1 flex items-center gap-1.5 text-2xs font-bold text-rose-600">
                                        <span class="material-symbols-outlined text-xs">event</span>
                                        <span>Berikutnya: {{ $uAst->next_maintenance_date ? $uAst->next_maintenance_date->format('d/m/Y') : 'Jatuh Tempo!' }}</span>
                                    </div>
                                </div>
                                <button type="button" @click="activeMaintenanceAsset = @js($uAst)" class="px-2.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-2xs font-bold shadow-sm cursor-pointer">
                                    Servis
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <x-card title="Jadwal & Catatan Pemeliharaan Aset (Maintenance Log)">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-2.5 px-3">Aset</th>
                                <th class="py-2.5 px-3">Cabang</th>
                                <th class="py-2.5 px-3">Kondisi Fisik</th>
                                <th class="py-2.5 px-3">Maintenance Terakhir</th>
                                <th class="py-2.5 px-3">Maintenance Berikutnya</th>
                                <th class="py-2.5 px-3">Catatan Servis</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                            @forelse($assets as $ast)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                    <td class="py-3 px-3">
                                        <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $ast->name }}</span>
                                        <span class="text-2xs text-slate-400 font-mono">{{ $ast->asset_code }}</span>
                                    </td>
                                    <td class="py-3 px-3 font-semibold text-slate-600 dark:text-slate-400">
                                        {{ $ast->branch?->name }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded-full text-2xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            {{ strtoupper($ast->condition ?? 'GOOD') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-700 dark:text-slate-300">
                                        {{ $ast->last_maintenance_date ? $ast->last_maintenance_date->format('d M Y') : 'Belum Ada' }}
                                    </td>
                                    <td class="py-3 px-3">
                                        @if($ast->next_maintenance_date)
                                            <span class="font-bold {{ $ast->next_maintenance_date->isPast() ? 'text-rose-600 font-extrabold' : 'text-slate-700 dark:text-slate-300' }}">
                                                {{ $ast->next_maintenance_date->format('d M Y') }}
                                            </span>
                                        @else
                                            <span class="text-2xs text-slate-400 italic">Belum dijadwalkan</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 text-2xs text-slate-500 max-w-xs truncate">
                                        {{ $ast->maintenance_notes ?? '-' }}
                                    </td>
                                    <td class="py-3 px-3 text-center">
                                        <button type="button" @click="activeMaintenanceAsset = @js($ast)" class="px-3 py-1.5 bg-amber-50 dark:bg-amber-950/30 text-amber-700 text-2xs font-bold rounded-lg hover:bg-amber-100 transition-colors cursor-pointer">
                                            Update Servis
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-slate-400">Belum ada aset terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <!-- ==================== TAB 4: PORTFOLIO & ANALYTICS ==================== -->
        <div x-show="activeTab === 'analytics'" class="space-y-4" x-cloak>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Doughnut Chart: Category Capex Distribution (5 cols) -->
                <div class="lg:col-span-5">
                    <x-card title="Komposisi Investasi Capex Per Kategori">
                        <div class="h-64 relative flex items-center justify-center">
                            <canvas id="assetCategoryChart"></canvas>
                        </div>
                    </x-card>
                </div>

                <!-- Doughnut Chart: Condition Distribution (7 cols) -->
                <div class="lg:col-span-7">
                    <x-card title="Ringkasan Kesehatan & Kondisi Fisik Portofolio Aset">
                        <div class="h-64 relative flex items-center justify-center">
                            <canvas id="assetConditionChart"></canvas>
                        </div>
                    </x-card>
                </div>
            </div>

            <!-- Category Summary Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($categoriesSummary as $cat)
                    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-xs text-slate-800 dark:text-slate-200">{{ $cat['name'] }}</span>
                            <span class="px-2 py-0.5 rounded-full text-2xs font-bold bg-primary/10 text-primary">{{ $cat['count'] }} Aset</span>
                        </div>
                        <div class="space-y-1 font-mono text-2xs">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Harga Perolehan:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">Rp {{ number_format($cat['total_cost'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Nilai Buku Saat Ini:</span>
                                <span class="font-bold text-emerald-600">Rp {{ number_format($cat['total_book_value'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Update Maintenance Modal (Dynamic) -->
        <template x-if="activeMaintenanceAsset">
            <div x-show="activeMaintenanceAsset" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak @keydown.escape.window="activeMaintenanceAsset = null">
                <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4">
                    <div class="flex justify-between items-center pb-2 border-b">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-amber-600">build</span>
                            <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Log Maintenance & Servis Aset</h3>
                        </div>
                        <button type="button" @click="activeMaintenanceAsset = null" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl">
                        <p class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="activeMaintenanceAsset.name"></p>
                        <p class="text-2xs text-slate-500" x-text="'Kode: ' + activeMaintenanceAsset.asset_code"></p>
                    </div>

                    <form :action="'/assets/' + activeMaintenanceAsset.id + '/maintenance'" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Tgl Servis Terakhir</label>
                                <input type="date" name="last_maintenance_date" :value="activeMaintenanceAsset.last_maintenance_date ? activeMaintenanceAsset.last_maintenance_date.split('T')[0] : '{{ date('Y-m-d') }}'" required class="w-full h-9 px-3 rounded-xl border text-xs font-bold">
                            </div>
                            <div>
                                <label class="text-2xs font-bold text-slate-400 uppercase">Tgl Servis Berikutnya</label>
                                <input type="date" name="next_maintenance_date" :value="activeMaintenanceAsset.next_maintenance_date ? activeMaintenanceAsset.next_maintenance_date.split('T')[0] : ''" class="w-full h-9 px-3 rounded-xl border text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Kondisi Fisik Aset</label>
                            <select name="condition" x-model="activeMaintenanceAsset.condition" required class="w-full h-9 px-2 rounded-xl border text-xs font-bold">
                                <option value="good">Baik (Siap Operasional)</option>
                                <option value="fair">Cukup (Perlu Pemantauan)</option>
                                <option value="poor">Rusak (Perlu Servis / Perbaikan)</option>
                                <option value="scrapped">Afkir (Tidak Berfungsi)</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Catatan Maintenance / Servis</label>
                            <textarea name="maintenance_notes" rows="3" x-model="activeMaintenanceAsset.maintenance_notes" placeholder="Catatan pergantian sparepart, teknisi, biaya servis..." class="w-full px-3 py-2 rounded-xl border text-xs"></textarea>
                        </div>

                        <button type="submit" class="btn-touch w-full bg-amber-600 text-white font-bold text-xs rounded-xl py-2.5 shadow-md">Simpan Log Maintenance</button>
                    </form>
                </div>
            </div>
        </template>

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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        window.assetChartInstances = {};

        window.renderAssetCharts = function() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#94a3b8' : '#64748b';
            const gridColor = isDark ? '#1e293b' : '#f1f5f9';

            @php
                $topAssets = $assets->take(7);
                $assetNames = $topAssets->pluck('name');
                $assetCosts = $topAssets->pluck('acquisition_cost');
                $assetBookValues = $topAssets->pluck('book_value');

                $forecastMonths = array_column($monthlyDepreciationForecast, 'month_name');
                $forecastAmounts = array_column($monthlyDepreciationForecast, 'amount');

                $catNames = array_keys($categoriesSummary->toArray());
                $catCosts = array_column($categoriesSummary->toArray(), 'total_cost');
            @endphp

            // 1. Bar Chart: Top Assets Valuation (Depreciation Tab)
            const ctxDep = document.getElementById('assetDepreciationChart');
            if (ctxDep) {
                if (window.assetChartInstances.dep) {
                    window.assetChartInstances.dep.destroy();
                }
                window.assetChartInstances.dep = new Chart(ctxDep, {
                    type: 'bar',
                    data: {
                        labels: @js($assetNames),
                        datasets: [
                            {
                                label: 'Harga Perolehan (Capex)',
                                data: @js($assetCosts),
                                backgroundColor: '#FF6600',
                                borderRadius: 6,
                            },
                            {
                                label: 'Nilai Buku Sekarang',
                                data: @js($assetBookValues),
                                backgroundColor: '#10b981',
                                borderRadius: 6,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { color: textColor, font: { family: 'Plus Jakarta Sans', weight: 'bold' } } }
                        },
                        scales: {
                            x: { ticks: { color: textColor }, grid: { display: false } },
                            y: { ticks: { color: textColor }, grid: { color: gridColor } }
                        }
                    }
                });
            }

            // 2. Line Chart: 12-Month Depreciation Forecast (Depreciation Tab)
            const ctxMonthly = document.getElementById('assetMonthlyDepreciationChart');
            if (ctxMonthly) {
                if (window.assetChartInstances.monthly) {
                    window.assetChartInstances.monthly.destroy();
                }
                window.assetChartInstances.monthly = new Chart(ctxMonthly, {
                    type: 'line',
                    data: {
                        labels: @js($forecastMonths),
                        datasets: [{
                            label: 'Proyeksi Beban Depresiasi (Rp)',
                            data: @js($forecastAmounts),
                            borderColor: '#f43f5e',
                            backgroundColor: 'rgba(244, 63, 94, 0.1)',
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#f43f5e',
                            pointRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { labels: { color: textColor, font: { weight: 'bold' } } }
                        },
                        scales: {
                            x: { ticks: { color: textColor }, grid: { display: false } },
                            y: { ticks: { color: textColor }, grid: { color: gridColor } }
                        }
                    }
                });
            }

            // 3. Doughnut Chart: Asset Category Capex Distribution (Analytics Tab)
            const ctxCat = document.getElementById('assetCategoryChart');
            if (ctxCat) {
                if (window.assetChartInstances.cat) {
                    window.assetChartInstances.cat.destroy();
                }
                window.assetChartInstances.cat = new Chart(ctxCat, {
                    type: 'doughnut',
                    data: {
                        labels: @js($catNames),
                        datasets: [{
                            data: @js($catCosts),
                            backgroundColor: ['#FF6600', '#10b981', '#3b82f6', '#8b5cf6', '#64748b'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: textColor, font: { weight: 'bold' } } }
                        },
                        cutout: '65%'
                    }
                });
            }

            // 4. Doughnut Chart: Condition Distribution (Analytics Tab)
            const ctxCond = document.getElementById('assetConditionChart');
            if (ctxCond) {
                if (window.assetChartInstances.cond) {
                    window.assetChartInstances.cond.destroy();
                }
                window.assetChartInstances.cond = new Chart(ctxCond, {
                    type: 'doughnut',
                    data: {
                        labels: ['Baik', 'Cukup', 'Rusak', 'Afkir'],
                        datasets: [{
                            data: [@js($conditionCounts['good']), @js($conditionCounts['fair']), @js($conditionCounts['poor']), @js($conditionCounts['scrapped'])],
                            backgroundColor: ['#10b981', '#f59e0b', '#f43f5e', '#64748b'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: textColor, font: { weight: 'bold' } } }
                        },
                        cutout: '65%'
                    }
                });
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(window.renderAssetCharts, 200);
        });
    </script>
    @endpush
</x-app-layout>
