<x-app-layout>
    <div class="flex flex-col gap-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
            <div>
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest block mb-1">Overview</span>
                <h2 class="text-2xl md:text-3xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight">Dashboard Ringkasan</h2>
            </div>
            <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 font-semibold text-sm bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 px-4 py-2.5 rounded-xl shadow-sm">
                <span class="material-symbols-outlined text-primary text-base">calendar_today</span>
                <span>{{ now()->translatedFormat('d F Y') }}</span>
            </div>
        </div>

        <!-- Bento Grid Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Revenue -->
            <x-stat-card 
                title="Total Pendapatan" 
                value="Rp {{ number_format($totalRevenue, 0, ',', '.') }}" 
                icon="account_balance_wallet"
                description="Akumulasi pendapatan cabang"
                trend="+8.2%"
                trendType="success"
            />

            <!-- Active Orders -->
            <x-stat-card 
                title="Antrean Cucian" 
                value="{{ $activeOrdersCount }} Pesanan" 
                icon="local_laundry_service"
                description="Pesanan aktif dalam produksi"
            />

            <!-- New Customers -->
            <x-stat-card 
                title="Pelanggan Baru" 
                value="{{ $newCustomersCount }}" 
                icon="person_add"
                description="Terdaftar bulan ini"
                trend="+12%"
                trendType="success"
            />

            <!-- Active Workshops -->
            <x-stat-card 
                title="Stasiun Aktif" 
                value="{{ $activeWorkshops }}" 
                icon="precision_manufacturing"
                description="Workshop beroperasi"
            />
        </div>

        <!-- Charts & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Revenue Chart (2 cols) -->
            <div class="lg:col-span-2">
                <x-card title="Grafik Pendapatan Mingguan">
                    <!-- Dynamic CSS Bar Chart -->
                    <div class="flex flex-col justify-between h-72 pt-4 relative">
                        <div class="flex-1 flex items-end justify-between gap-4 h-56 border-b border-slate-100 dark:border-slate-800 pb-2">
                            @foreach ($weeklyRevenue as $data)
                                @php
                                    $heightPercentage = $maxRevenue > 0 ? ($data['amount'] / $maxRevenue) * 100 : 0;
                                    // Minimum 5% height so empty bars still show slightly
                                    $heightPercentage = max($heightPercentage, 5);
                                @endphp
                                <div class="flex-1 flex flex-col items-center h-full justify-end group relative">
                                    <!-- Tooltip -->
                                    <div class="absolute -top-8 bg-slate-900 text-white text-[10px] font-bold py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity z-10 shadow-lg pointer-events-none whitespace-nowrap">
                                        Rp {{ number_format($data['amount'], 0, ',', '.') }}
                                    </div>
                                    <!-- Bar -->
                                    <div style="height: {{ $heightPercentage }}%" 
                                         class="w-full max-w-[40px] rounded-t-lg transition-all duration-300 {{ $data['amount'] > 0 ? 'bg-primary dark:bg-orange-500 hover:bg-orange-600 dark:hover:bg-orange-400 shadow-[0_0_15px_rgba(255,102,0,0.15)]' : 'bg-slate-100 dark:bg-slate-800 hover:bg-slate-200' }}">
                                    </div>
                                    <!-- Date label on hover -->
                                    <span class="text-[9px] text-slate-400 dark:text-slate-500 mt-2 font-bold">{{ $data['date'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- X-Axis Labels -->
                        <div class="flex justify-between text-xs font-bold text-slate-500 dark:text-slate-400 pt-3">
                            @foreach ($weeklyRevenue as $data)
                                <span class="flex-1 text-center {{ $loop->last ? 'text-primary font-extrabold' : '' }}">{{ $data['day'] }}</span>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Recent Activity List (1 col) -->
            <div>
                <x-card title="Aktivitas Terkini">
                    <div class="flex flex-col gap-4 max-h-[320px] overflow-y-auto pr-1">
                        @if ($recentOrders->isEmpty())
                            <div class="py-12 text-center">
                                <span class="material-symbols-outlined text-slate-300 dark:text-slate-700 text-4xl mb-2">history</span>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Belum ada aktivitas pesanan baru.</p>
                            </div>
                        @else
                            @foreach ($recentOrders as $order)
                                <div class="flex justify-between items-center p-3 rounded-xl border border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-900/50 transition-all">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="w-10 h-10 rounded-xl bg-orange-50 dark:bg-slate-800 text-primary dark:text-orange-400 flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ substr($order->customer?->name ?? 'U', 0, 2) }}
                                        </div>
                                        <div class="overflow-hidden">
                                            <span class="font-bold text-xs text-slate-800 dark:text-slate-200 block truncate">
                                                #{{ $order->order_number }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 block truncate">
                                                {{ $order->customer?->name ?? 'Pelanggan Umum' }} • Rp {{ number_format($order->total, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                    <x-badge type="primary" class="text-[10px]">{{ $order->production_status }}</x-badge>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    
                    <x-slot name="footer">
                        <a href="/production" class="text-xs font-bold text-primary hover:text-primary-hover flex items-center gap-1 justify-center transition-colors">
                            Lihat Semua Antrean
                            <span class="material-symbols-outlined text-xs">arrow_forward</span>
                        </a>
                    </x-slot>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
