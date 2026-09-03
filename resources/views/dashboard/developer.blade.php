<x-app-layout>
    <div class="space-y-6" x-data="{
        activeTab: 'server',
        tableSearch: '',
        tableCategory: 'ALL',
        userSearch: '',
        logSearch: '',
        dbPingLoading: false,
        dbPingResult: '{{ $telemetry['database']['ping_ms'] }} ms',
        actionLoading: false,
        toastMessage: '',
        toastType: 'success',
        toastVisible: false,
        passwordModalOpen: false,
        selectedUserId: null,
        selectedUserEmail: '',
        newPasswordInput: 'Istana@2026!',

        showToast(msg, type = 'success') {
            this.toastMessage = msg;
            this.toastType = type;
            this.toastVisible = true;
            setTimeout(() => this.toastVisible = false, 4000);
        },

        async runAction(action, payload = {}) {
            this.actionLoading = true;
            try {
                const res = await fetch('{{ route('dashboard.developer.action') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ action: action, ...payload })
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message || 'Aksi berhasil dieksekusi.');
                    if (data.latency_ms !== undefined) {
                        this.dbPingResult = data.latency_ms + ' ms';
                    }
                } else {
                    this.showToast(data.message || 'Gagal mengeksekusi aksi.', 'error');
                }
            } catch(e) {
                this.showToast('Kesalahan jaringan: ' + e.message, 'error');
            } finally {
                this.actionLoading = false;
            }
        },

        openPasswordModal(userId, email) {
            this.selectedUserId = userId;
            this.selectedUserEmail = email;
            this.newPasswordInput = 'Istana@2026!';
            this.passwordModalOpen = true;
        },

        async submitResetPassword() {
            if (!this.newPasswordInput) {
                alert('Password tidak boleh kosong!');
                return;
            }
            await this.runAction('reset_user_password', {
                user_id: this.selectedUserId,
                new_password: this.newPasswordInput
            });
            this.passwordModalOpen = false;
        }
    }">

        <!-- Notification Bar (Utilitarian Toast) -->
        <div x-show="toastVisible" 
             x-transition
             class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-lg shadow-2xl font-mono text-xs border"
             :class="toastType === 'error' ? 'bg-red-950 text-red-200 border-red-800' : 'bg-slate-900 text-emerald-300 border-emerald-800'"
             style="display: none;">
            <span class="font-bold uppercase tracking-wider" x-text="toastType === 'error' ? '[ERROR]' : '[OK]'"></span>
            <span x-text="toastMessage"></span>
        </div>

        <!-- Password Reset Modal -->
        <div x-show="passwordModalOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-xs p-4"
             style="display: none;">
            <div class="bg-slate-900 border border-slate-700 text-slate-100 rounded-xl max-w-md w-full p-5 space-y-4 font-mono">
                <div class="border-b border-slate-800 pb-2">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Reset Password Pengguna</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Target Akun: <span class="text-amber-400 font-bold" x-text="selectedUserEmail"></span></p>
                </div>

                <div class="space-y-1.5 text-xs">
                    <label class="text-slate-400">Password Baru:</label>
                    <input type="text" 
                           x-model="newPasswordInput" 
                           class="w-full bg-slate-950 border border-slate-700 rounded px-3 py-2 text-white font-mono text-xs focus:outline-hidden focus:border-primary">
                    <p class="text-[10px] text-slate-500">Default yang disarankan: Istana@2026!</p>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" 
                            @click="passwordModalOpen = false"
                            class="px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-xs text-slate-300">
                        Batal
                    </button>
                    <button type="button" 
                            @click="submitResetPassword()"
                            :disabled="actionLoading"
                            class="px-3 py-1.5 rounded bg-primary hover:bg-orange-600 text-xs text-white font-bold">
                        Simpan Password
                    </button>
                </div>
            </div>
        </div>

        <!-- 1. System Console Header -->
        <div class="bg-slate-950 border border-slate-800 rounded-xl p-5 text-slate-200 font-mono">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
                        <span class="text-orange-500 font-bold">[ROOT / DEVELOPER WORKSTATION]</span>
                        <span>::</span>
                        <span class="text-slate-300">SYSTEM ARCHITECTURE TELEMETRY</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                        Developer System Control Console
                    </h1>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400 mt-2">
                        <span>HOST: <strong class="text-white">{{ $telemetry['host']['hostname'] }}</strong></span>
                        <span>IP: <strong class="text-white">{{ $telemetry['host']['server_ip'] }}</strong></span>
                        <span>OS: <strong class="text-white">{{ $telemetry['host']['os_name'] }}</strong></span>
                        <span>UPTIME: <strong class="text-emerald-400">{{ $telemetry['host']['uptime'] }}</strong></span>
                        <span>TIME: <strong class="text-slate-300">{{ $telemetry['host']['server_time'] }}</strong></span>
                    </div>
                </div>

                <!-- Fast Command Buttons -->
                <div class="flex flex-wrap items-center gap-2 shrink-0 text-xs">
                    <button type="button" 
                            @click="runAction('db_ping')" 
                            :disabled="actionLoading"
                            class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-emerald-400 border border-slate-700 rounded font-bold cursor-pointer">
                        PING DB: <span x-text="dbPingResult"></span>
                    </button>

                    <button type="button" 
                            @click="runAction('clear_cache')" 
                            :disabled="actionLoading"
                            class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-amber-400 border border-slate-700 rounded font-bold cursor-pointer">
                        OPTIMIZE:CLEAR
                    </button>

                    <button type="button" 
                            @click="runAction('optimize_all')" 
                            :disabled="actionLoading"
                            class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-cyan-400 border border-slate-700 rounded font-bold cursor-pointer">
                        CACHE ALL
                    </button>

                    <button type="button" 
                            @click="runAction('clear_logs')" 
                            :disabled="actionLoading"
                            class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-rose-400 border border-slate-700 rounded font-bold cursor-pointer">
                        TRUNCATE LOGS
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. System Navigation Tabs (Sharp & Utilitarian) -->
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-800 pb-2 font-mono text-xs">
            <button type="button" 
                    @click="activeTab = 'server'"
                    :class="activeTab === 'server' ? 'bg-primary text-white font-bold' : 'bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800'"
                    class="px-4 py-2 rounded cursor-pointer transition-colors">
                [ 1. KINERJA SERVER ]
            </button>

            <button type="button" 
                    @click="activeTab = 'database'"
                    :class="activeTab === 'database' ? 'bg-primary text-white font-bold' : 'bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800'"
                    class="px-4 py-2 rounded cursor-pointer transition-colors">
                [ 2. DATABASE ENGINE ({{ count($telemetry['database']['tables']) }} TABEL) ]
            </button>

            <button type="button" 
                    @click="activeTab = 'accounts'"
                    :class="activeTab === 'accounts' ? 'bg-primary text-white font-bold' : 'bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800'"
                    class="px-4 py-2 rounded cursor-pointer transition-colors">
                [ 3. MANAJEMEN AKUN ({{ count($telemetry['users']) }} USERS) ]
            </button>

            <button type="button" 
                    @click="activeTab = 'management'"
                    :class="activeTab === 'management' ? 'bg-primary text-white font-bold' : 'bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800'"
                    class="px-4 py-2 rounded cursor-pointer transition-colors">
                [ 4. MANAJEMEN SISTEM & FRAMEWORK ]
            </button>

            <button type="button" 
                    @click="activeTab = 'logs'"
                    :class="activeTab === 'logs' ? 'bg-primary text-white font-bold' : 'bg-slate-900 hover:bg-slate-800 text-slate-400 border border-slate-800'"
                    class="px-4 py-2 rounded cursor-pointer transition-colors">
                [ 5. LOG SISTEM & AUDIT ]
            </button>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 1: KINERJA SERVER (HOST, CPU, MEMORY, STORAGE, TOP PROCESSES, SERVICES) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'server'" class="space-y-6 font-mono text-xs">
            <!-- Hardware Summary Cards (Dense Grid) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- CPU -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-400 font-bold uppercase">CPU UTILIZATION</span>
                        <span class="text-xs font-bold {{ $telemetry['host']['cpu_usage_pct'] > 80 ? 'text-red-500' : 'text-emerald-400' }}">
                            {{ $telemetry['host']['cpu_usage_pct'] }}%
                        </span>
                    </div>
                    <div class="space-y-1 text-slate-300">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Cores:</span>
                            <span class="text-white">{{ $telemetry['host']['cpu_cores'] }} ({{ $telemetry['host']['architecture'] }})</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Load 1 min:</span>
                            <span class="text-white">{{ $telemetry['host']['load_avg']['1m'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Load 5 min:</span>
                            <span class="text-white">{{ $telemetry['host']['load_avg']['5m'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Load 15 min:</span>
                            <span class="text-white">{{ $telemetry['host']['load_avg']['15m'] }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-900 h-1.5 rounded overflow-hidden">
                        <div class="h-full {{ $telemetry['host']['cpu_usage_pct'] > 80 ? 'bg-red-500' : 'bg-primary' }}" 
                             style="width: {{ $telemetry['host']['cpu_usage_pct'] }}%"></div>
                    </div>
                </div>

                <!-- RAM -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-400 font-bold uppercase">RAM / MEMORY</span>
                        <span class="text-xs font-bold {{ $telemetry['host']['memory_pct'] > 85 ? 'text-red-500' : 'text-cyan-400' }}">
                            {{ $telemetry['host']['memory_pct'] }}%
                        </span>
                    </div>
                    <div class="space-y-1 text-slate-300">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total RAM:</span>
                            <span class="text-white">{{ number_format($telemetry['host']['memory_total_mb'], 0) }} MB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Used RAM:</span>
                            <span class="text-white">{{ number_format($telemetry['host']['memory_used_mb'], 0) }} MB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Free / Cache:</span>
                            <span class="text-white">{{ number_format($telemetry['host']['memory_available_mb'], 0) }} MB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">PHP Memory Limit:</span>
                            <span class="text-white">{{ $telemetry['host']['php_memory_limit'] }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-900 h-1.5 rounded overflow-hidden">
                        <div class="h-full bg-cyan-400" style="width: {{ $telemetry['host']['memory_pct'] }}%"></div>
                    </div>
                </div>

                <!-- Storage -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-400 font-bold uppercase">DISK STORAGE</span>
                        <span class="text-xs font-bold {{ $telemetry['host']['disk_pct'] > 90 ? 'text-red-500' : 'text-emerald-400' }}">
                            {{ $telemetry['host']['disk_pct'] }}%
                        </span>
                    </div>
                    <div class="space-y-1 text-slate-300">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Total Space:</span>
                            <span class="text-white">{{ $telemetry['host']['disk_total_gb'] }} GB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Used Space:</span>
                            <span class="text-white">{{ $telemetry['host']['disk_used_gb'] }} GB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Available:</span>
                            <span class="text-white">{{ $telemetry['host']['disk_free_gb'] }} GB</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Mount:</span>
                            <span class="text-white">/ (ext4)</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-900 h-1.5 rounded overflow-hidden">
                        <div class="h-full {{ $telemetry['host']['disk_pct'] > 90 ? 'bg-red-500' : 'bg-emerald-500' }}" 
                             style="width: {{ $telemetry['host']['disk_pct'] }}%"></div>
                    </div>
                </div>

                <!-- Services Daemon -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <span class="text-slate-400 font-bold uppercase">DAEMON SERVICES</span>
                        <span class="text-xs font-bold text-slate-400">SYSTEMD</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($telemetry['services'] as $svcKey => $svc)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-300">{{ $svc['name'] }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $svc['status'] === 'RUNNING' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-red-950 text-red-300 border border-red-800' }}">
                                    [{{ $svc['status'] }}]
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Top Running Linux Host Processes (Raw Table) -->
            <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Top 10 Active Host Processes (ps aux)</h3>
                    <span class="text-slate-500 text-[11px]">SORTED BY: %CPU DESCENDING</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="text-slate-500 border-b border-slate-800">
                            <tr>
                                <th class="py-2">USER</th>
                                <th class="py-2">PID</th>
                                <th class="py-2 text-right">%CPU</th>
                                <th class="py-2 text-right">%MEM</th>
                                <th class="py-2 text-right">TIME</th>
                                <th class="py-2 pl-4">COMMAND</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-900">
                            @forelse($telemetry['processes'] as $proc)
                                <tr class="hover:bg-slate-900/50">
                                    <td class="py-1.5 text-slate-400">{{ $proc['user'] }}</td>
                                    <td class="py-1.5 text-amber-400">{{ $proc['pid'] }}</td>
                                    <td class="py-1.5 text-right {{ (float)$proc['cpu'] > 20 ? 'text-red-400 font-bold' : 'text-slate-200' }}">{{ $proc['cpu'] }}%</td>
                                    <td class="py-1.5 text-right text-slate-200">{{ $proc['mem'] }}%</td>
                                    <td class="py-1.5 text-right text-slate-400">{{ $proc['time'] }}</td>
                                    <td class="py-1.5 pl-4 text-slate-300 truncate max-w-lg" title="{{ $proc['command'] }}">{{ $proc['command'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-4 text-center text-slate-500">Tidak ada data proses host tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 2: DATABASE ENGINE (STORAGE, STATUS VARIABLES, 57 TABLES INSPECTOR) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'database'" class="space-y-6 font-mono text-xs">
            <!-- Database Engine Telemetry Bar -->
            <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">MySQL Engine Telemetry</h3>
                        <p class="text-slate-400 mt-0.5">Database: <strong class="text-white">{{ $telemetry['database']['database'] }}</strong> (Engine: InnoDB | Server: {{ $telemetry['database']['version'] }})</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded bg-slate-900 text-emerald-400 border border-slate-700 font-bold">
                            LATENCY: {{ $telemetry['database']['ping_ms'] }} ms
                        </span>
                        <span class="px-2.5 py-1 rounded bg-slate-900 text-slate-300 border border-slate-700">
                            UPTIME: {{ $telemetry['database']['uptime_str'] }}
                        </span>
                    </div>
                </div>

                <!-- Database Metrics Row -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div class="bg-slate-900/60 p-3 rounded border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase">TOTAL DB SIZE</span>
                        <span class="text-base font-bold text-primary">{{ $telemetry['database']['total_size_mb'] }} MB</span>
                    </div>
                    <div class="bg-slate-900/60 p-3 rounded border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase">DATA SIZE</span>
                        <span class="text-base font-bold text-white">{{ $telemetry['database']['data_size_mb'] }} MB</span>
                    </div>
                    <div class="bg-slate-900/60 p-3 rounded border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase">INDEX SIZE</span>
                        <span class="text-base font-bold text-white">{{ $telemetry['database']['index_size_mb'] }} MB</span>
                    </div>
                    <div class="bg-slate-900/60 p-3 rounded border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase">TOTAL TABLES</span>
                        <span class="text-base font-bold text-cyan-400">{{ $telemetry['database']['table_count'] }}</span>
                    </div>
                    <div class="bg-slate-900/60 p-3 rounded border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase">ACTIVE THREADS</span>
                        <span class="text-base font-bold text-emerald-400">{{ $telemetry['database']['threads_connected'] }}</span>
                    </div>
                    <div class="bg-slate-900/60 p-3 rounded border border-slate-800">
                        <span class="text-slate-500 block text-[10px] uppercase">BUFFER HIT RATE</span>
                        <span class="text-base font-bold text-emerald-400">{{ $telemetry['database']['buffer_hit_rate'] }}%</span>
                    </div>
                </div>
            </div>

            <!-- Database Tables Inspector Table -->
            <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">
                        Table Schema & Row Counter ({{ count($telemetry['database']['tables']) }} Tables)
                    </h3>

                    <div class="flex items-center gap-2">
                        <input type="text" 
                               x-model="tableSearch" 
                               placeholder="Filter tabel..." 
                               class="bg-slate-900 border border-slate-700 text-slate-200 text-xs px-3 py-1.5 rounded focus:outline-hidden focus:border-primary w-44">
                        
                        <select x-model="tableCategory" 
                                class="bg-slate-900 border border-slate-700 text-slate-200 text-xs px-2.5 py-1.5 rounded focus:outline-hidden">
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

                <div class="overflow-x-auto max-h-[550px]">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="text-slate-500 border-b border-slate-800 sticky top-0 bg-slate-950 z-10">
                            <tr>
                                <th class="py-2.5 px-3">TABLE NAME</th>
                                <th class="py-2.5 px-3">CATEGORY</th>
                                <th class="py-2.5 px-3 text-right">ROWS</th>
                                <th class="py-2.5 px-3 text-right">DATA SIZE</th>
                                <th class="py-2.5 px-3 text-right">INDEX SIZE</th>
                                <th class="py-2.5 px-3 text-right">TOTAL SIZE</th>
                                <th class="py-2.5 px-3 text-center">ENGINE</th>
                                <th class="py-2.5 px-3 text-right">LAST UPDATE</th>
                                <th class="py-2.5 px-3 text-center">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-900">
                            @foreach($telemetry['database']['tables'] as $tbl)
                                <tr class="hover:bg-slate-900/60"
                                    x-show="('{{ $tbl['name'] }}'.toLowerCase().includes(tableSearch.toLowerCase()) || tableSearch === '') && (tableCategory === 'ALL' || tableCategory === '{{ $tbl['category'] }}')">
                                    <td class="py-2 px-3 font-bold text-white">{{ $tbl['name'] }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] border border-slate-700 bg-slate-900 text-slate-400">
                                            {{ $tbl['category'] }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-right font-bold {{ $tbl['rows'] > 0 ? 'text-amber-400' : 'text-slate-500' }}">
                                        {{ number_format($tbl['rows'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-2 px-3 text-right text-slate-400">{{ number_format($tbl['data_kb'], 1) }} KB</td>
                                    <td class="py-2 px-3 text-right text-slate-400">{{ number_format($tbl['index_kb'], 1) }} KB</td>
                                    <td class="py-2 px-3 text-right font-bold text-slate-200">{{ number_format($tbl['total_kb'], 1) }} KB</td>
                                    <td class="py-2 px-3 text-center text-slate-400">{{ $tbl['engine'] }}</td>
                                    <td class="py-2 px-3 text-right text-slate-500 text-[11px]">{{ $tbl['updated_at'] }}</td>
                                    <td class="py-2 px-3 text-center">
                                        <button type="button" 
                                                @click="runAction('optimize_table', { table: '{{ $tbl['name'] }}' })"
                                                class="px-2 py-0.5 rounded bg-slate-900 hover:bg-slate-800 text-[10px] text-cyan-400 border border-slate-700 cursor-pointer">
                                            OPTIMIZE
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 3: MANAJEMEN AKUN & PENGGUNA (ALL 18 USERS, RESET PASSWORD, UNLOCK) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'accounts'" class="space-y-6 font-mono text-xs">
            <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">User Accounts & Access Control ({{ count($telemetry['users']) }} Akun)</h3>
                        <p class="text-slate-400 mt-0.5">Kontrol status login, pembukaan kunci lockout, dan reset password langsung</p>
                    </div>

                    <input type="text" 
                           x-model="userSearch" 
                           placeholder="Filter nama / email / role..." 
                           class="bg-slate-900 border border-slate-700 text-slate-200 text-xs px-3 py-1.5 rounded focus:outline-hidden focus:border-primary w-64">
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="text-slate-500 border-b border-slate-800">
                            <tr>
                                <th class="py-2.5 px-3">ID</th>
                                <th class="py-2.5 px-3">NAMA</th>
                                <th class="py-2.5 px-3">EMAIL</th>
                                <th class="py-2.5 px-3">ROLE</th>
                                <th class="py-2.5 px-3">CABANG</th>
                                <th class="py-2.5 px-3 text-center">STATUS</th>
                                <th class="py-2.5 px-3 text-center">LOCKOUT</th>
                                <th class="py-2.5 px-3 text-center">2FA</th>
                                <th class="py-2.5 px-3 text-center">AKSI MANAJEMEN</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-900">
                            @foreach($telemetry['users'] as $u)
                                <tr class="hover:bg-slate-900/60"
                                    x-show="userSearch === '' || '{{ $u['name'] }} {{ $u['email'] }} {{ $u['role'] }}'.toLowerCase().includes(userSearch.toLowerCase())">
                                    <td class="py-2 px-3 text-slate-500">{{ $u['id'] }}</td>
                                    <td class="py-2 px-3 font-bold text-white">{{ $u['name'] }}</td>
                                    <td class="py-2 px-3 text-slate-300">{{ $u['email'] }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-900 border border-slate-700 text-cyan-400">
                                            {{ $u['role'] }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-slate-400 truncate max-w-[160px]" title="{{ $u['branch'] }}">{{ $u['branch'] }}</td>
                                    <td class="py-2 px-3 text-center">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $u['is_active'] ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-red-950 text-red-300 border border-red-800' }}">
                                            {{ $u['is_active'] ? 'ACTIVE' : 'INACTIVE' }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        @if($u['is_locked'])
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-950 text-red-300 border border-red-800">
                                                LOCKED ({{ $u['locked_until'] }})
                                            </span>
                                        @elseif($u['login_attempts'] > 0)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-950 text-amber-300 border border-amber-800">
                                                {{ $u['login_attempts'] }} FAIL
                                            </span>
                                        @else
                                            <span class="text-slate-500 text-[10px]">OK</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <span class="text-[10px] {{ $u['has_2fa'] ? 'text-emerald-400 font-bold' : 'text-slate-600' }}">
                                            {{ $u['has_2fa'] ? 'ENABLED' : 'OFF' }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" 
                                                    @click="openPasswordModal({{ $u['id'] }}, '{{ $u['email'] }}')"
                                                    class="px-2 py-1 bg-slate-900 hover:bg-slate-800 text-amber-400 border border-slate-700 rounded text-[10px] font-bold cursor-pointer">
                                                SET PASSWORD
                                            </button>

                                            @if($u['is_locked'] || $u['login_attempts'] > 0)
                                                <button type="button" 
                                                        @click="runAction('unlock_user', { user_id: {{ $u['id'] }} })"
                                                        class="px-2 py-1 bg-emerald-950 hover:bg-emerald-900 text-emerald-300 border border-emerald-800 rounded text-[10px] font-bold cursor-pointer">
                                                    UNLOCK
                                                </button>
                                            @endif

                                            <button type="button" 
                                                    @click="runAction('toggle_user_active', { user_id: {{ $u['id'] }} })"
                                                    class="px-2 py-1 bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-700 rounded text-[10px] font-bold cursor-pointer">
                                                {{ $u['is_active'] ? 'NONAKTIFKAN' : 'AKTIFKAN' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 4: MANAJEMEN SISTEM & FRAMEWORK (CACHE, ENV, OPCACHE, QUEUE, CRON)   -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'management'" class="space-y-6 font-mono text-xs">
            <!-- Framework Actions Toolbar -->
            <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-4">
                <div class="border-b border-slate-800 pb-2">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">Operasi Cache & Optimasi Framework</h3>
                    <p class="text-slate-400 mt-0.5">Eksekusi perintah internal artisan secara aman</p>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <button type="button" 
                            @click="runAction('clear_cache')"
                            :disabled="actionLoading"
                            class="p-3 rounded bg-slate-900 hover:bg-slate-800 border border-slate-700 text-left cursor-pointer">
                        <span class="text-slate-400 text-[10px] block uppercase">ARTISAN</span>
                        <span class="text-white font-bold block text-sm">optimize:clear</span>
                        <span class="text-slate-500 text-[10px]">Hapus semua cache bootstrap</span>
                    </button>

                    <button type="button" 
                            @click="runAction('cache_config')"
                            :disabled="actionLoading"
                            class="p-3 rounded bg-slate-900 hover:bg-slate-800 border border-slate-700 text-left cursor-pointer">
                        <span class="text-slate-400 text-[10px] block uppercase">ARTISAN</span>
                        <span class="text-white font-bold block text-sm">config:cache</span>
                        <span class="text-slate-500 text-[10px]">Gabungkan file konfigurasi</span>
                    </button>

                    <button type="button" 
                            @click="runAction('cache_routes')"
                            :disabled="actionLoading"
                            class="p-3 rounded bg-slate-900 hover:bg-slate-800 border border-slate-700 text-left cursor-pointer">
                        <span class="text-slate-400 text-[10px] block uppercase">ARTISAN</span>
                        <span class="text-white font-bold block text-sm">route:cache</span>
                        <span class="text-slate-500 text-[10px]">Pre-compile route registrasi</span>
                    </button>

                    <button type="button" 
                            @click="runAction('cache_views')"
                            :disabled="actionLoading"
                            class="p-3 rounded bg-slate-900 hover:bg-slate-800 border border-slate-700 text-left cursor-pointer">
                        <span class="text-slate-400 text-[10px] block uppercase">ARTISAN</span>
                        <span class="text-white font-bold block text-sm">view:cache</span>
                        <span class="text-slate-500 text-[10px]">Pre-compile template Blade</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Environment Config Inspector -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Environment Configuration (.env)</h3>
                        <span class="text-slate-500 text-[10px]">WHITELISTED NON-SECRET</span>
                    </div>

                    <div class="space-y-1.5 divide-y divide-slate-900">
                        @foreach($telemetry['environment_config'] as $key => $val)
                            <div class="flex items-center justify-between pt-1.5">
                                <span class="text-slate-400">{{ $key }}:</span>
                                <span class="text-white font-bold">{{ $val }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Queue, Scheduler & Storage Health -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-4">
                    <div class="border-b border-slate-800 pb-2">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">Queue Worker, Scheduler & Permissions</h3>
                        <p class="text-slate-400 mt-0.5">Integritas background job dan crontab host</p>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between items-center p-2.5 rounded bg-slate-900/60 border border-slate-800">
                            <span class="text-slate-400">Queue Pending Jobs:</span>
                            <span class="text-white font-bold">{{ $telemetry['queue_scheduler']['pending_jobs'] }} Jobs</span>
                        </div>

                        <div class="flex justify-between items-center p-2.5 rounded bg-slate-900/60 border border-slate-800">
                            <span class="text-slate-400">Queue Failed Jobs:</span>
                            <span class="font-bold {{ $telemetry['queue_scheduler']['failed_jobs'] > 0 ? 'text-red-400' : 'text-emerald-400' }}">
                                {{ $telemetry['queue_scheduler']['failed_jobs'] }} Failed
                            </span>
                        </div>

                        <div class="flex justify-between items-center p-2.5 rounded bg-slate-900/60 border border-slate-800">
                            <span class="text-slate-400">Direktori storage/:</span>
                            <span class="font-bold {{ $telemetry['application']['storage_writable'] ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $telemetry['application']['storage_writable'] ? 'WRITABLE (777)' : 'READ ONLY' }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center p-2.5 rounded bg-slate-900/60 border border-slate-800">
                            <span class="text-slate-400">Direktori bootstrap/cache/:</span>
                            <span class="font-bold {{ $telemetry['application']['bootstrap_cache_writable'] ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $telemetry['application']['bootstrap_cache_writable'] ? 'WRITABLE (777)' : 'READ ONLY' }}
                            </span>
                        </div>

                        <div class="p-3 rounded bg-slate-900/60 border border-slate-800 space-y-1">
                            <span class="text-slate-500 text-[10px] uppercase block">HOST CRONTAB (SCHEDULER):</span>
                            <pre class="text-slate-300 text-[11px] overflow-x-auto whitespace-pre-wrap">{{ $telemetry['queue_scheduler']['crontab'] }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 5: LOG SISTEM & AUDIT TRAIL (LARAVEL.LOG VIEWER & AUDIT LOGS)        -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'logs'" class="space-y-6 font-mono text-xs">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Laravel.log Live Browser -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Live Log: storage/logs/laravel.log</h3>
                            <p class="text-slate-500 text-[10px]">Ukuran File: {{ $telemetry['application']['logs_size_mb'] }} MB</p>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <input type="text" 
                                   x-model="logSearch" 
                                   placeholder="Filter log..." 
                                   class="bg-slate-900 border border-slate-700 text-slate-200 text-xs px-2.5 py-1 rounded focus:outline-hidden w-36">
                            
                            <button type="button" 
                                    @click="runAction('clear_logs')"
                                    class="px-2 py-1 bg-red-950 hover:bg-red-900 text-red-300 border border-red-800 rounded text-[10px] font-bold cursor-pointer">
                                EMPTY
                            </button>
                        </div>
                    </div>

                    <div class="bg-black text-slate-300 p-3 rounded h-96 overflow-y-auto font-mono text-[11px] space-y-1 border border-slate-900">
                        @forelse($telemetry['security']['recent_error_logs'] as $line)
                            <p x-show="logSearch === '' || '{{ addslashes($line) }}'.toLowerCase().includes(logSearch.toLowerCase())"
                               class="truncate leading-relaxed hover:text-white
                                @if(str_contains($line, '.ERROR:')) text-red-400 font-bold
                                @elseif(str_contains($line, '.WARNING:')) text-amber-300
                                @elseif(str_contains($line, '.INFO:')) text-emerald-400
                                @else text-slate-500 @endif"
                               title="{{ $line }}">
                                {{ $line }}
                            </p>
                        @empty
                            <div class="h-full flex items-center justify-center text-slate-600">
                                [ LOG FILE CLEAN // ZERO SYSTEM ERRORS ]
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Audit Trail Table -->
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                        <h3 class="text-sm font-bold text-white uppercase tracking-wider">System Audit Trail (15 Log Terakhir)</h3>
                        <span class="text-slate-500 text-[10px]">TABEL: audit_logs</span>
                    </div>

                    <div class="overflow-x-auto h-96">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="text-slate-500 border-b border-slate-800 sticky top-0 bg-slate-950">
                                <tr>
                                    <th class="py-2 px-2">TIMESTAMP</th>
                                    <th class="py-2 px-2">USER</th>
                                    <th class="py-2 px-2">ACTION</th>
                                    <th class="py-2 px-2 text-right">IP ADDRESS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900">
                                @forelse($telemetry['security']['recent_audit_logs'] as $log)
                                    <tr class="hover:bg-slate-900/60">
                                        <td class="py-2 px-2 text-slate-500">{{ $log['created_at'] }}</td>
                                        <td class="py-2 px-2 font-bold text-white">{{ $log['user'] }}</td>
                                        <td class="py-2 px-2">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-900 border border-slate-700 text-slate-300">
                                                {{ $log['action'] }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-2 text-right text-slate-400">{{ $log['ip_address'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-slate-600">
                                            [ BELUM ADA AUDIT LOG TERCATAT ]
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
