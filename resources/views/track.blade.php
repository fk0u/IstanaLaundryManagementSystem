<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Status Pesanan #{{ $orderNumber }} | Istana Laundry Samarinda</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Vite CSS/JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .status-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: .7; }
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased overflow-x-hidden min-h-screen flex flex-col justify-between">

    <!-- TopNavBar -->
    <header class="w-full bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800 z-50 sticky top-0 transition-colors">
        <nav class="flex justify-between items-center px-6 md:px-16 py-4 max-w-7xl mx-auto">
            <a href="/" class="flex items-center gap-2">
                <img alt="Istana Laundry Logo" class="h-10 w-auto object-contain" src="{{ asset('images/logo.webp') }}"/>
                <span class="font-black text-2xl text-slate-900 dark:text-white tracking-tighter">ISTANA LAUNDRY</span>
            </a>
            
            <a href="/" class="font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hover:text-primary transition-colors flex items-center gap-1.5">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Kembali ke Beranda
            </a>
        </nav>
    </header>

    <main class="flex-grow py-12 px-6 md:px-16 max-w-4xl mx-auto w-full">
        @if (!$order)
            <!-- Not Found State -->
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-12 text-center shadow-xl max-w-lg mx-auto">
                <span class="material-symbols-outlined text-red-500 text-6xl mb-4">search_off</span>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white mb-2">Nota Tidak Ditemukan</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-8 leading-relaxed">
                    Nomor nota <span class="font-bold text-slate-800 dark:text-slate-200">"{{ $orderNumber }}"</span> tidak terdaftar di sistem kami. Pastikan nomor nota yang Anda masukkan sudah benar.
                </p>
                <a href="/#tracking" class="bg-primary hover:bg-orange-600 text-white font-bold text-xs uppercase tracking-wider py-4 px-8 rounded-xl transition-all shadow-md shadow-orange-500/10">
                    Cari Ulang
                </a>
            </div>
        @else
            <!-- Found State -->
            <div class="space-y-8">
                <!-- Status Hero -->
                <div class="bg-slate-900 text-white p-8 rounded-3xl relative overflow-hidden shadow-2xl">
                    <div class="absolute -right-12 -bottom-12 w-48 h-48 opacity-10">
                        <img alt="Istana Laundry Logo" class="w-full h-full object-contain" src="{{ asset('images/logo.webp') }}"/>
                    </div>
                    <div class="relative z-10 space-y-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary/20 text-primary-fixed rounded-full">
                            <span class="w-2 h-2 bg-primary rounded-full status-pulse"></span>
                            <span class="text-[10px] font-bold uppercase tracking-widest">Lacak Langsung</span>
                        </div>
                        
                        @php
                            $statusLabels = [
                                'TERIMA' => 'Cucian Diterima',
                                'PILAH' => 'Pakaian Dipilah',
                                'CUCI' => 'Sedang Dicuci',
                                'KERING' => 'Proses Pengeringan',
                                'LIPAT' => 'Sedang Dilipat',
                                'CEK' => 'Pengecekan Kualitas',
                                'SIAP' => 'Siap Diambil',
                                'DIAMBIL' => 'Selesai & Diambil'
                            ];
                            $statusDescs = [
                                'TERIMA' => 'Cucian Anda telah masuk antrean dan siap untuk diproses.',
                                'PILAH' => 'Kami sedang memilah pakaian Anda berdasarkan warna dan jenis serat kain.',
                                'CUCI' => 'Pakaian Anda sedang dicuci dengan teknologi air demineralisasi terbaik kami.',
                                'KERING' => 'Proses pengeringan aman untuk menjaga serat pakaian tetap lembut.',
                                'LIPAT' => 'Staf kami sedang menyetrika dan melipat cucian Anda dengan rapi sempurna.',
                                'CEK' => 'Kami sedang memeriksa kelengkapan dan memastikan tidak ada noda tersisa.',
                                'SIAP' => 'Cucian Anda telah selesai! Silakan datang ke cabang kami untuk mengambilnya.',
                                'DIAMBIL' => 'Pesanan ini telah selesai diambil. Terima kasih atas kepercayaan Anda.'
                            ];
                        @endphp
                        
                        <h1 class="text-3xl font-black">
                            Status: <span class="text-primary">{{ $statusLabels[$order->production_status] ?? $order->production_status }}</span>
                        </h1>
                        <p class="text-xs md:text-sm text-slate-300 max-w-xl leading-relaxed">
                            {{ $statusDescs[$order->production_status] ?? 'Sedang diproses oleh tim profesional kami.' }}
                        </p>

                        <!-- Live Financial Payment Status Synchronization Bar -->
                        <div class="pt-4 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                @if($order->payment_status === 'paid')
                                    <span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-full text-xs font-black uppercase flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">verified</span> Pembayaran Lunas (Paid)
                                    </span>
                                @elseif($order->payment_status === 'partial')
                                    <span class="px-3 py-1 bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-full text-xs font-black uppercase flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">pending</span> DP / Sebagian (Piutang: Rp {{ number_format($order->total - $order->paid_amount, 0, ',', '.') }})
                                    </span>
                                @else
                                    <span class="px-3 py-1 bg-rose-500/20 text-rose-400 border border-rose-500/30 rounded-full text-xs font-black uppercase flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">error</span> Belum Lunas (Unpaid: Rp {{ number_format($order->total - $order->paid_amount, 0, ',', '.') }})
                                    </span>
                                @endif
                                <span class="text-xs text-slate-400 font-semibold uppercase">Metode: {{ strtoupper($order->payment_method) }}</span>
                            </div>

                            <a href="{{ route('invoices.show', $order->id) }}" target="_blank" class="px-4 py-2 bg-white/10 hover:bg-white/20 backdrop-blur-md text-white rounded-xl font-bold text-xs flex items-center gap-1.5 transition-all">
                                <span class="material-symbols-outlined text-sm">description</span> Unduh Invoice / Billing A4
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Timeline Progress -->
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-8 shadow-sm">
                    <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-8">Progress Pengerjaan</h2>
                    
                    @php
                        $statusKeys = ['TERIMA', 'CUCI', 'KERING', 'LIPAT', 'SIAP', 'DIAMBIL'];
                        $simpleLabels = [
                            'TERIMA' => 'Diterima',
                            'CUCI' => 'Dicuci',
                            'KERING' => 'Dikeringkan',
                            'LIPAT' => 'Disetrika',
                            'SIAP' => 'Siap Diambil',
                            'DIAMBIL' => 'Diambil'
                        ];
                        $icons = [
                            'TERIMA' => 'check_circle',
                            'CUCI' => 'local_laundry_service',
                            'KERING' => 'dry',
                            'LIPAT' => 'iron',
                            'SIAP' => 'shopping_bag',
                            'DIAMBIL' => 'done_all'
                        ];
                        
                        // Map 8 database status to 6 simple timeline nodes
                        $dbStatus = $order->production_status;
                        $nodeIndexMap = [
                            'TERIMA' => 0,
                            'PILAH' => 0,
                            'CUCI' => 1,
                            'KERING' => 2,
                            'LIPAT' => 3,
                            'CEK' => 3,
                            'SIAP' => 4,
                            'DIAMBIL' => 5
                        ];
                        $activeIndex = $nodeIndexMap[$dbStatus] ?? 0;
                    @endphp

                    <!-- Desktop Horizontal Timeline -->
                    <div class="relative mb-6 hidden md:block">
                        <!-- Progress Line Base -->
                        <div class="absolute top-5 left-0 w-full h-1 bg-slate-100 dark:bg-slate-800 rounded-full"></div>
                        <!-- Completed Line -->
                        <div class="absolute top-5 left-0 h-1 bg-primary rounded-full transition-all duration-500" 
                             style="width: {{ ($activeIndex / 5) * 100 }}%"></div>
                        
                        <div class="relative flex justify-between">
                            @foreach ($statusKeys as $index => $key)
                                <div class="flex flex-col items-center text-center w-24">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center z-10 mb-3 border-4 border-white dark:border-slate-900 transition-all duration-300 
                                                {{ $index <= $activeIndex 
                                                   ? 'bg-primary text-white ring-4 ring-orange-500/10' 
                                                   : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600' }}">
                                        <span class="material-symbols-outlined text-[18px]">
                                            {{ $icons[$key] }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-bold {{ $index <= $activeIndex ? 'text-slate-800 dark:text-white' : 'text-slate-400' }}">
                                        {{ $simpleLabels[$key] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Mobile Vertical Timeline -->
                    <div class="md:hidden space-y-6 relative ml-4">
                        <div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-slate-100 dark:bg-slate-800"></div>
                        <div class="absolute left-[11px] top-2 w-0.5 bg-primary transition-all duration-500" 
                             style="height: {{ $activeIndex > 0 ? (($activeIndex / 5) * 100) - 5 : 0 }}%"></div>
                        
                        @foreach ($statusKeys as $index => $key)
                            <div class="relative flex gap-4">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center z-10 text-white transition-all duration-300
                                            {{ $index <= $activeIndex ? 'bg-primary ring-4 ring-orange-500/10' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-600' }}">
                                    <span class="material-symbols-outlined text-[12px] font-bold">
                                        {{ $index <= $activeIndex ? 'check' : 'circle' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs font-bold {{ $index <= $activeIndex ? 'text-slate-800 dark:text-white' : 'text-slate-400' }}">
                                        {{ $simpleLabels[$key] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Order Info & Items -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <!-- Left: Items list (8 cols) -->
                    <div class="md:col-span-8 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-8 shadow-sm">
                        <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-6">Rincian Cucian</h2>
                        
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($order->items as $item)
                                <div class="py-4 flex justify-between items-center first:pt-0 last:pb-0">
                                    <div>
                                        <h3 class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                            {{ $item->service?->name ?? 'Layanan Laundry' }}
                                        </h3>
                                        <p class="text-[10px] text-slate-400 mt-0.5">
                                            {{ $item->qty }} {{ $item->service?->unit === 'kg' ? 'Kg' : 'Pcs' }} x Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </p>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border-t border-slate-100 dark:border-slate-800 mt-6 pt-6 space-y-3">
                            <div class="flex justify-between text-xs text-slate-500">
                                <span>Subtotal</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-200">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if ($order->discount_promo > 0)
                                <div class="flex justify-between text-xs text-red-500">
                                    <span>Potongan Promo</span>
                                    <span class="font-semibold">- Rp {{ number_format($order->discount_promo, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if ($order->discount_points > 0)
                                <div class="flex justify-between text-xs text-red-500">
                                    <span>Redeem Poin loyalitas</span>
                                    <span class="font-semibold">- Rp {{ number_format($order->discount_points, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-sm font-extrabold text-slate-800 dark:text-white pt-3 border-t border-dashed border-slate-100 dark:border-slate-800">
                                <span>Total Bayar</span>
                                <span class="text-primary">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Customer/Cabang Info (4 cols) -->
                    <div class="md:col-span-4 space-y-6">
                        <!-- Customer Info Card -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
                            <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4">Informasi Nota</h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Nomor Nota</span>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $order->order_number }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Pelanggan</span>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $order->customer?->name ?? 'Pelanggan Umum' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Cabang</span>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $order->branch?->name ?? 'Samarinda Central' }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-slate-400 block">Tanggal Masuk</span>
                                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $order->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status Log History -->
                        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl p-6 shadow-sm">
                            <h2 class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-4">Riwayat Pengerjaan</h2>
                            
                            <div class="space-y-4 max-h-[220px] overflow-y-auto pr-1">
                                @if ($order->productionStatusLogs->isEmpty())
                                    <p class="text-[10px] text-slate-400">Tidak ada riwayat terekam.</p>
                                @else
                                    @foreach ($order->productionStatusLogs->sortByDesc('created_at') as $log)
                                        <div class="flex gap-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary mt-1 shrink-0"></span>
                                            <div>
                                                <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300 block">
                                                    {{ $statusLabels[$log->status] ?? $log->status }}
                                                </span>
                                                <span class="text-[9px] text-slate-400 block">
                                                    {{ $log->created_at->format('d M Y, H:i') }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white border-t border-slate-800 py-8 transition-colors">
        <div class="max-w-7xl mx-auto px-6 md:px-16 flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
            <div class="flex items-center gap-2">
                <img alt="Istana Laundry Logo" class="h-6 w-auto object-contain" src="{{ asset('images/logo.webp') }}"/>
                <span class="font-black text-lg tracking-tighter text-white">ISTANA LAUNDRY</span>
            </div>
            <p class="text-[10px] text-slate-500">
                © {{ date('Y') }} Istana Premium Laundry Samarinda. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html>
