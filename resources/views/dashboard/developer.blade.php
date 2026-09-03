<x-app-layout>
    <div class="space-y-6 md:space-y-8" x-data="{
        activeTab: 'telemetry',
        tableSearch: '',
        tableCategory: 'ALL',
        dbPingLoading: false,
        dbPingResult: '{{ $telemetry['database']['ping_ms'] }} ms',
        cacheLoading: false,
        toastMessage: '',
        toastVisible: false,
        showToast(msg) {
            this.toastMessage = msg;
            this.toastVisible = true;
            setTimeout(() => this.toastVisible = false, 4000);
        },
        async runDbPing() {
            this.dbPingLoading = true;
            try {
                const res = await fetch('{{ route('dashboard.developer.db-ping') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                this.dbPingResult = data.latency_ms + ' ms';
                this.showToast('Database ping selesai: ' + data.latency_ms + ' ms (Status: ' + data.status + ')');
            } catch(e) {
                this.showToast('Gagal melakukan ping database: ' + e.message);
            } finally {
                this.dbPingLoading = false;
            }
        },
        async clearAppCache() {
            if (!confirm('Bersihkan seluruh application cache (optimize:clear & re-cache)?')) return;
            this.cacheLoading = true;
            try {
                const res = await fetch('{{ route('dashboard.developer.clear-cache') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                this.showToast(data.message || 'Cache aplikasi berhasil dibersihkan!');
            } catch(e) {
                this.showToast('Gagal membersihkan cache: ' + e.message);
            } finally {
                this.cacheLoading = false;
            }
        }
    }">

        <!-- Floating Action Toast -->
        <div x-show="toastVisible" 
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4"
             class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-slate-900/95 dark:bg-white/95 text-white dark:text-slate-900 px-5 py-3.5 rounded-2xl shadow-2xl backdrop-blur-md border border-slate-700 dark:border-slate-300"
             style="display: none;">
            <span class="material-symbols-outlined text-emerald-400 dark:text-emerald-600 text-xl">check_circle</span>
            <span class="text-xs font-bold" x-text="toastMessage"></span>
        </div>

        <!-- 1. Header & Quick Controls -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-6 rounded-3xl text-white shadow-xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-primary/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-orange-500/20 text-orange-400 border border-orange-500/30">
                        <span class="material-symbols-outlined text-[14px]">terminal</span>
                        Developer Console & Telemetry
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        Live Telemetry
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
                    System Control & Telemetry Center
                </h1>
                <p class="text-xs text-slate-300 font-medium mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <span>Host: <strong class="text-white font-mono">{{ $telemetry['host']['hostname'] }}</strong> ({{ $telemetry['host']['server_ip'] }})</span>
                    <span>&bull;</span>
                    <span>Uptime: <strong class="text-emerald-400 font-mono">{{ $telemetry['host']['uptime'] }}</strong></span>
                    <span>&bull;</span>
                    <span>Waktu Server: <strong class="text-slate-200 font-mono">{{ $telemetry['host']['server_time'] }}</strong></span>
                </p>
            </div>

            <div class="relative z-10 flex flex-wrap items-center gap-2.5 shrink-0">
                <!-- Tab View Switcher -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-primary text-white shadow-sm hover:brightness-110 transition-all">
                    <span class="material-symbols-outlined text-[16px]">speed</span>
                    <span>System Telemetry</span>
                </a>
                <a href="{{ route('dashboard', ['view' => 'business']) }}" 
                   class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-800/80 text-slate-300 hover:text-white hover:bg-slate-700/80 border border-slate-700 transition-all">
                    <span class="material-symbols-outlined text-[16px]">bar_chart</span>
                    <span>Business View</span>
                </a>

                <!-- Quick Actions -->
                <button type="button" 
                        @click="runDbPing()"
                        :disabled="dbPingLoading"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]" :class="{ 'animate-spin': dbPingLoading }">network_ping</span>
                    <span x-text="dbPingLoading ? 'Pinging...' : 'Ping DB (' + dbPingResult + ')'"></span>
                </button>

                <button type="button" 
                        @click="clearAppCache()"
                        :disabled="cacheLoading"
                        class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 border border-amber-500/40 transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]" :class="{ 'animate-spin': cacheLoading }">cached</span>
                    <span x-text="cacheLoading ? 'Cleaning...' : 'Clear Cache'"></span>
                </button>
            </div>
        </div>

        <!-- 2. Real-time Host Performance (4 Hero Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- CPU Load Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-xl bg-orange-500/10 text-primary">
                            <span class="material-symbols-outlined text-xl">memory</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">CPU Usage & Load</span>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white font-mono">{{ $telemetry['host']['cpu_usage_pct'] }}%</h3>
                        </div>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                        {{ $telemetry['host']['cpu_cores'] }} Cores
                    </span>
                </div>
                
                <!-- CPU Bar -->
                <div class="space-y-2">
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $telemetry['host']['cpu_usage_pct'] > 80 ? 'bg-rose-500' : ($telemetry['host']['cpu_usage_pct'] > 60 ? 'bg-amber-500' : 'bg-primary') }}"
                             style="width: {{ $telemetry['host']['cpu_usage_pct'] }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                        <span>Load: <strong>{{ $telemetry['host']['load_avg']['1m'] }}</strong> (1m)</span>
                        <span><strong>{{ $telemetry['host']['load_avg']['5m'] }}</strong> (5m)</span>
                        <span><strong>{{ $telemetry['host']['load_avg']['15m'] }}</strong> (15m)</span>
                    </div>
                </div>
            </div>

            <!-- RAM / Memory Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-xl bg-indigo-500/10 text-indigo-500">
                            <span class="material-symbols-outlined text-xl">developer_board</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">RAM / Memory</span>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white font-mono">{{ $telemetry['host']['memory_pct'] }}%</h3>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded-lg bg-indigo-500/10 text-indigo-500">
                        PHP: {{ $telemetry['host']['php_memory_mb'] }}M
                    </span>
                </div>

                <!-- Memory Bar -->
                <div class="space-y-2">
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $telemetry['host']['memory_pct'] > 85 ? 'bg-rose-500' : 'bg-indigo-500' }}"
                             style="width: {{ $telemetry['host']['memory_pct'] }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                        <span>Terpakai: <strong>{{ number_format($telemetry['host']['memory_used_mb'] / 1024, 2) }} GB</strong></span>
                        <span>Total: <strong>{{ number_format($telemetry['host']['memory_total_mb'] / 1024, 2) }} GB</strong></span>
                    </div>
                </div>
            </div>

            <!-- NVMe Disk Storage Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-500">
                            <span class="material-symbols-outlined text-xl">hard_drive</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Storage / Disk</span>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white font-mono">{{ $telemetry['host']['disk_pct'] }}%</h3>
                        </div>
                    </div>
                    <span class="text-[11px] font-mono font-bold px-2 py-0.5 rounded-lg {{ $telemetry['host']['disk_pct'] > 90 ? 'bg-rose-500/10 text-rose-500 font-black' : 'bg-emerald-500/10 text-emerald-500' }}">
                        {{ $telemetry['host']['disk_free_gb'] }} GB Free
                    </span>
                </div>

                <!-- Disk Bar -->
                <div class="space-y-2">
                    <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-500 {{ $telemetry['host']['disk_pct'] > 90 ? 'bg-rose-500' : 'bg-emerald-500' }}"
                             style="width: {{ $telemetry['host']['disk_pct'] }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                        <span>Used: <strong>{{ $telemetry['host']['disk_used_gb'] }} GB</strong></span>
                        <span>Total: <strong>{{ $telemetry['host']['disk_total_gb'] }} GB</strong></span>
                    </div>
                </div>
            </div>

            <!-- Host & OS Card -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 p-5 rounded-2xl shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2 rounded-xl bg-purple-500/10 text-purple-500">
                            <span class="material-symbols-outlined text-xl">dns</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Platform & OS</span>
                            <h3 class="text-xs font-black text-slate-900 dark:text-white truncate max-w-[140px]" title="{{ $telemetry['host']['os_name'] }}">
                                {{ $telemetry['host']['os_name'] }}
                            </h3>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-purple-500/10 text-purple-500">
                        {{ $telemetry['host']['architecture'] }}
                    </span>
                </div>

                <div class="pt-2 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                    <span>PHP Limit: <strong>{{ $telemetry['host']['php_memory_limit'] }}</strong></span>
                    <span>Peak: <strong>{{ $telemetry['host']['php_peak_memory_mb'] }} MB</strong></span>
                </div>
            </div>
        </div>

        <!-- 3. Database Engine & Telemetry Deep View -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-2xl">database</span>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white">Database Engine & Storage Metrics</h2>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Koneksi: <strong class="text-slate-800 dark:text-slate-200 font-mono">{{ $telemetry['database']['database'] }}</strong> on <strong class="font-mono">{{ $telemetry['database']['host'] }}</strong> ({{ $telemetry['database']['version'] }})
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-xl text-xs font-mono font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        Latency: {{ $telemetry['database']['ping_ms'] }} ms
                    </span>
                    <span class="px-3 py-1 rounded-xl text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                        Uptime: {{ $telemetry['database']['uptime_str'] }}
                    </span>
                </div>
            </div>

            <!-- Database Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Total DB Size</span>
                    <p class="text-xl font-black font-mono text-primary">{{ $telemetry['database']['total_size_mb'] }} <span class="text-xs">MB</span></p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Data Size</span>
                    <p class="text-xl font-black font-mono text-slate-800 dark:text-slate-200">{{ $telemetry['database']['data_size_mb'] }} <span class="text-xs">MB</span></p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Index Size</span>
                    <p class="text-xl font-black font-mono text-slate-800 dark:text-slate-200">{{ $telemetry['database']['index_size_mb'] }} <span class="text-xs">MB</span></p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Total Tables</span>
                    <p class="text-xl font-black font-mono text-indigo-500">{{ $telemetry['database']['table_count'] }} <span class="text-xs">Tabel</span></p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Active Threads</span>
                    <p class="text-xl font-black font-mono text-emerald-500">{{ $telemetry['database']['threads_connected'] }}</p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 block mb-1">Buffer Hit Rate</span>
                    <p class="text-xl font-black font-mono text-emerald-500">{{ $telemetry['database']['buffer_hit_rate'] }}%</p>
                </div>
            </div>

            <!-- Detailed Tables Inspector with Filter & Search -->
            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-base">table_chart</span>
                        Database Tables Inspector ({{ count($telemetry['database']['tables']) }} Tables)
                    </h3>
                    
                    <div class="flex items-center gap-2">
                        <input type="text" 
                               x-model="tableSearch" 
                               placeholder="Cari tabel..." 
                               class="text-xs px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-hidden focus:ring-2 focus:ring-primary/20 w-40 sm:w-56" />
                        
                        <select x-model="tableCategory" 
                                class="text-xs px-2.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:outline-hidden">
                            <option value="ALL">Semua Kategori</option>
                            <option value="Master Data">Master Data</option>
                            <option value="POS & Orders">POS & Orders</option>
                            <option value="Finance">Finance</option>
                            <option value="Procurement">Procurement</option>
                            <option value="System & Auth">System & Auth</option>
                            <option value="Queue & Cache">Queue & Cache</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200/80 dark:border-slate-800 max-h-96">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 font-bold border-b border-slate-200/80 dark:border-slate-800 sticky top-0 z-10 backdrop-blur-md">
                            <tr>
                                <th class="px-4 py-3">Nama Tabel</th>
                                <th class="px-4 py-3">Kategori</th>
                                <th class="px-4 py-3 text-right">Baris (Rows)</th>
                                <th class="px-4 py-3 text-right">Data Size</th>
                                <th class="px-4 py-3 text-right">Index Size</th>
                                <th class="px-4 py-3 text-right">Total Size</th>
                                <th class="px-4 py-3 text-center">Engine</th>
                                <th class="px-4 py-3 text-right">Update Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                            @foreach($telemetry['database']['tables'] as $tbl)
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
                                    x-show="('{{ $tbl['name'] }}'.toLowerCase().includes(tableSearch.toLowerCase()) || tableSearch === '') && (tableCategory === 'ALL' || tableCategory === '{{ $tbl['category'] }}')">
                                    <td class="px-4 py-2.5 font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1.5 font-sans">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $tbl['rows'] > 0 ? 'bg-primary' : 'bg-slate-300 dark:bg-slate-700' }}"></span>
                                        {{ $tbl['name'] }}
                                    </td>
                                    <td class="px-4 py-2.5 font-sans">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold 
                                            @if($tbl['category'] === 'Master Data') bg-blue-500/10 text-blue-500
                                            @elseif($tbl['category'] === 'POS & Orders') bg-orange-500/10 text-primary
                                            @elseif($tbl['category'] === 'Finance') bg-emerald-500/10 text-emerald-500
                                            @elseif($tbl['category'] === 'System & Auth') bg-purple-500/10 text-purple-500
                                            @else bg-slate-100 dark:bg-slate-800 text-slate-500 @endif">
                                            {{ $tbl['category'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-bold text-slate-800 dark:text-slate-200">
                                        {{ number_format($tbl['rows'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-slate-500 dark:text-slate-400">
                                        {{ number_format($tbl['data_kb'], 1) }} KB
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-slate-500 dark:text-slate-400">
                                        {{ number_format($tbl['index_kb'], 1) }} KB
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-bold text-slate-900 dark:text-slate-100">
                                        {{ number_format($tbl['total_kb'], 1) }} KB
                                    </td>
                                    <td class="px-4 py-2.5 text-center text-slate-400 text-[11px]">
                                        {{ $tbl['engine'] }}
                                    </td>
                                    <td class="px-4 py-2.5 text-right text-slate-400 text-[11px]">
                                        {{ $tbl['updated_at'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. Infrastructure, Git Versioning & Application Runtime -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Col 1: Git & Deployment Status -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="material-symbols-outlined text-rose-500 text-2xl">source</span>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Git & Deployment Engine</h3>
                        <p class="text-[11px] text-slate-400 font-medium">Status branch dan auto-sync production</p>
                    </div>
                </div>

                <div class="space-y-3 font-mono text-xs">
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-3.5 rounded-2xl border border-slate-200/60 dark:border-slate-800 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-sans font-bold uppercase text-slate-400">Active Branch</span>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-500 font-black">{{ $telemetry['git']['branch'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-sans font-bold uppercase text-slate-400">Commit Hash</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $telemetry['git']['commit_hash'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-sans font-bold uppercase text-slate-400">Commit Time</span>
                            <span class="text-slate-500">{{ $telemetry['git']['commit_date'] }}</span>
                        </div>
                        <div class="pt-1.5 border-t border-slate-200/60 dark:border-slate-700">
                            <span class="text-[10px] font-sans font-bold uppercase text-slate-400 block mb-0.5">Message</span>
                            <p class="text-[11px] font-sans text-slate-700 dark:text-slate-300 font-medium italic">"{{ $telemetry['git']['commit_message'] }}"</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-800">
                        <span class="font-sans text-slate-600 dark:text-slate-400 font-bold">Auto-Sync Service:</span>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 text-emerald-500 font-bold">{{ $telemetry['git']['auto_sync_status'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Col 2: Laravel Framework & Runtime Engine -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="material-symbols-outlined text-primary text-2xl">settings_applications</span>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Laravel & PHP Runtime</h3>
                        <p class="text-[11px] text-slate-400 font-medium">Framework configurations & cache engines</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                        <span class="text-[10px] font-sans font-bold uppercase text-slate-400 block">Framework</span>
                        <span class="font-bold text-slate-900 dark:text-slate-100">v{{ $telemetry['application']['laravel_version'] }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                        <span class="text-[10px] font-sans font-bold uppercase text-slate-400 block">PHP Engine</span>
                        <span class="font-bold text-slate-900 dark:text-slate-100">v{{ $telemetry['application']['php_version'] }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                        <span class="text-[10px] font-sans font-bold uppercase text-slate-400 block">Environment</span>
                        <span class="font-black text-emerald-500 uppercase">{{ $telemetry['application']['environment'] }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                        <span class="text-[10px] font-sans font-bold uppercase text-slate-400 block">Debug Mode</span>
                        <span class="font-bold {{ $telemetry['application']['debug_mode'] ? 'text-rose-500' : 'text-emerald-500' }}">
                            {{ $telemetry['application']['debug_mode'] ? 'TRUE (Warning)' : 'FALSE (Secure)' }}
                        </span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                        <span class="text-[10px] font-sans font-bold uppercase text-slate-400 block">Session Driver</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $telemetry['application']['session_driver'] }}</span>
                    </div>
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-800">
                        <span class="text-[10px] font-sans font-bold uppercase text-slate-400 block">Queue Driver</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ $telemetry['application']['queue_driver'] }}</span>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-xs font-mono">
                    <span class="font-sans text-slate-500">OPcache Engine:</span>
                    <span class="font-bold {{ $telemetry['application']['opcache_enabled'] ? 'text-emerald-500' : 'text-slate-400' }}">
                        {{ $telemetry['application']['opcache_enabled'] ? 'Enabled (' . $telemetry['application']['opcache_hit_rate'] . '% Hit)' : 'Disabled' }}
                    </span>
                </div>
            </div>

            <!-- Col 3: Storage Permissions & Queue Health -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <span class="material-symbols-outlined text-emerald-500 text-2xl">verified_user</span>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Security & Permissions</h3>
                        <p class="text-[11px] text-slate-400 font-medium">Directory health & account status</p>
                    </div>
                </div>

                <!-- Permissions List -->
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-600 dark:text-slate-400 font-bold">Directory `storage/`</span>
                        <span class="px-2 py-0.5 rounded-md font-mono font-bold {{ $telemetry['application']['storage_writable'] ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                            {{ $telemetry['application']['storage_writable'] ? 'Writable (777)' : 'Read Only' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-600 dark:text-slate-400 font-bold">Directory `bootstrap/cache/`</span>
                        <span class="px-2 py-0.5 rounded-md font-mono font-bold {{ $telemetry['application']['bootstrap_cache_writable'] ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                            {{ $telemetry['application']['bootstrap_cache_writable'] ? 'Writable (777)' : 'Read Only' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-600 dark:text-slate-400 font-bold">Total Accounts</span>
                        <span class="font-mono font-bold text-slate-800 dark:text-slate-200">{{ $telemetry['security']['total_users'] }} Users</span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-600 dark:text-slate-400 font-bold">Locked Accounts</span>
                        <span class="font-mono font-bold {{ $telemetry['security']['locked_users'] > 0 ? 'text-rose-500' : 'text-emerald-500' }}">
                            {{ $telemetry['security']['locked_users'] }} Locked
                        </span>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <span class="text-slate-600 dark:text-slate-400 font-bold">Failed Queue Jobs</span>
                        <span class="font-mono font-bold {{ $telemetry['application']['failed_jobs'] > 0 ? 'text-rose-500' : 'text-emerald-500' }}">
                            {{ $telemetry['application']['failed_jobs'] }} Failed
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Audit Trail & Live Application Log Feed -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Audit Trail -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">history</span>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Recent System Audit Trail</h3>
                    </div>
                    <span class="text-[11px] font-mono font-bold text-slate-400">10 Log Terbaru</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="text-slate-400 font-bold border-b border-slate-100 dark:border-slate-800">
                            <tr>
                                <th class="pb-2">Waktu</th>
                                <th class="pb-2">Pengguna</th>
                                <th class="pb-2">Aksi</th>
                                <th class="pb-2 text-right">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 font-mono">
                            @forelse($telemetry['security']['recent_audit_logs'] as $log)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="py-2.5 text-slate-500">{{ $log['created_at'] }}</td>
                                    <td class="py-2.5 font-sans font-bold text-slate-800 dark:text-slate-200">{{ $log['user'] }}</td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                            {{ $log['action'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-right text-slate-400">{{ $log['ip_address'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400 font-sans">
                                        Belum ada aktivitas audit log tercatat pasca sanitasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Application Logs Feed -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500 text-xl">bug_report</span>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Live Application Error / Event Log</h3>
                    </div>
                    <span class="text-[11px] font-mono font-bold text-slate-400">`storage/logs/laravel.log`</span>
                </div>

                <div class="bg-slate-950 text-slate-200 p-4 rounded-2xl font-mono text-[11px] h-64 overflow-y-auto space-y-1 border border-slate-800">
                    @forelse($telemetry['security']['recent_error_logs'] as $line)
                        <p class="truncate leading-relaxed hover:text-white transition-colors
                            @if(str_contains($line, '.ERROR:')) text-rose-400 font-bold
                            @elseif(str_contains($line, '.WARNING:')) text-amber-300
                            @elseif(str_contains($line, '.INFO:')) text-emerald-400
                            @else text-slate-400 @endif"
                           title="{{ $line }}">
                            {{ $line }}
                        </p>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-slate-500 gap-2">
                            <span class="material-symbols-outlined text-3xl text-emerald-500">task_alt</span>
                            <span>Tidak ada error log! Sistem berjalan mulus dan bersih.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
