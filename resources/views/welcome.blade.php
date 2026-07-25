<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Istana Laundry Samarinda | Royal Care for Your Finest Garments</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <!-- Vite CSS/JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .service-card:hover .service-icon { color: #ff6600; transform: scale(1.1); transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased overflow-x-hidden">

    <!-- TopNavBar -->
    <header class="w-full sticky top-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800 z-50 transition-colors">
        <nav class="flex justify-between items-center px-6 md:px-16 py-4 max-w-7xl mx-auto">
            <div class="flex items-center gap-2">
                <img alt="Istana Laundry Logo" class="h-10 w-auto object-contain" src="{{ asset('images/logo.webp') }}"/>
                <span class="font-black text-2xl text-slate-900 dark:text-white tracking-tighter">ISTANA LAUNDRY</span>
            </div>
            
            <div class="hidden md:flex items-center space-x-8">
                <a class="font-semibold text-sm text-primary border-b-2 border-primary pb-1 transition-all" href="#services">Layanan</a>
                <a class="font-semibold text-sm text-slate-500 dark:text-slate-400 hover:text-primary transition-colors" href="#pricing">Tarif</a>
                <a class="font-semibold text-sm text-slate-500 dark:text-slate-400 hover:text-primary transition-colors" href="#membership">Membership</a>
                <a class="font-semibold text-sm text-slate-500 dark:text-slate-400 hover:text-primary transition-colors" href="#tracking">Lacak Nota</a>
            </div>
            
            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="bg-primary hover:bg-orange-600 text-white font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-xl transition-all shadow-md shadow-orange-500/10 active:scale-95">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hover:text-primary transition-colors">
                        Masuk Staff
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="relative min-h-[680px] flex items-center overflow-hidden bg-slate-900 text-white">
            <div class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-900/60 to-transparent z-10"></div>
                <div class="w-full h-full bg-cover bg-center opacity-40 mix-blend-luminosity" 
                     style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAknJiJUNzHo2h5ysFw5MH3lHz2fmvbuzV_zHlSw6W-lUHMU8t-dNkaCyKDYX5HUph0jhNEcJEencgv5b5C0Ect9Ev3ONVUv9kk4PeEmS1CtrgrtedPu9FrMNmk_3F8CDo0O5z6bA-wVhxeRX0muFLBItvPHetUzL0nd3MwpMiQWrtl9np_yxAzgHmB0CMpnsOrAaei-z1Db7AZFJMGGTG0Efsw1QpIPEbKSoscMp6sK4VhksXYEhGjTh7K9a9YsWvkWpJ1Dtf-IWM')">
                </div>
            </div>
            
            <div class="relative z-20 px-6 md:px-16 max-w-7xl mx-auto w-full py-16">
                <div class="max-w-2xl">
                    <span class="text-xs font-bold text-primary tracking-widest uppercase mb-4 block">Premium Garment Care Samarinda</span>
                    <h1 class="text-4xl md:text-5xl font-black mb-6 leading-none tracking-tight">
                        Royal Care for Your<br/><span class="text-primary">Finest Garments</span>
                    </h1>
                    <p class="text-base md:text-lg text-slate-300 mb-8 leading-relaxed max-w-lg">
                        Rasakan perawatan cucian berkualitas tinggi oleh para ahli garment. Menggunakan formula ramah lingkungan dan teknologi modern untuk menjaga kelembutan setiap serat pakaian Anda.
                    </p>
                    
                    <!-- Public Tracking Form -->
                    <div id="tracking" class="bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/10 max-w-lg mb-8 shadow-2xl">
                        <h3 class="text-sm font-bold mb-3 flex items-center gap-2 text-white">
                            <span class="material-symbols-outlined text-primary text-base">search</span>
                            Lacak Pengerjaan Laundry Anda
                        </h3>
                        <form action="{{ route('track') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1 relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">barcode_scanner</span>
                                <input type="text" name="order_number" required placeholder="Masukkan nomor nota (cth: SMD01-YYYYMM-XXXX)..." 
                                       class="w-full pl-10 pr-4 py-3 bg-white text-slate-900 rounded-xl text-xs focus:ring-2 focus:ring-primary focus:outline-none placeholder:text-slate-400 font-semibold border-none" />
                            </div>
                            <button type="submit" class="bg-primary hover:bg-orange-600 text-white font-bold text-xs uppercase tracking-wider py-3 px-6 rounded-xl transition-all shadow-md shadow-orange-500/20 whitespace-nowrap active:scale-[0.98]">
                                Lacak Sekarang
                            </button>
                        </form>
                        @if(session('error'))
                            <p class="text-red-400 text-xs font-bold mt-2.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">error</span>
                                {{ session('error') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Services Section -->
        <section class="py-24 bg-white dark:bg-slate-900 transition-colors" id="services">
            <div class="max-w-7xl mx-auto px-6 md:px-16">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-4">
                    <div class="max-w-xl">
                        <span class="text-xs font-bold text-primary uppercase tracking-wider block mb-1">Layanan Spesialis</span>
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Perawatan Terbaik Wardrobe Anda</h2>
                    </div>
                    <a class="font-bold text-xs text-primary uppercase tracking-wider flex items-center gap-2 hover:gap-3 transition-all" href="#pricing">
                        Lihat Daftar Harga <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Service 1 -->
                    <div class="service-card group border border-slate-100 dark:border-slate-800 p-8 rounded-2xl hover:shadow-xl dark:hover:shadow-orange-500/5 transition-all duration-300 flex flex-col bg-slate-50 dark:bg-slate-900/50">
                        <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-6 service-icon transition-transform">
                            <span class="material-symbols-outlined text-primary text-3xl">local_laundry_service</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-3">Wet Wash &amp; Fold</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 flex-grow leading-relaxed">Pencucian higienis harian menggunakan air demineralisasi, detergen ramah kulit, dan pelembut premium disesuaikan dengan jenis kain.</p>
                        <ul class="space-y-2 mb-6">
                            <li class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Pemisahan Warna Ketat</li>
                            <li class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Opsi Kilat 24 Jam</li>
                        </ul>
                    </div>
                    
                    <!-- Service 2 -->
                    <div class="service-card group border border-slate-900 bg-slate-950 dark:border-slate-800 p-8 rounded-2xl hover:shadow-xl transition-all duration-300 flex flex-col text-white">
                        <div class="w-14 h-14 bg-white/10 rounded-xl flex items-center justify-center mb-6 service-icon transition-transform">
                            <span class="material-symbols-outlined text-white text-3xl">dry_cleaning</span>
                        </div>
                        <h3 class="text-lg font-bold mb-3">Professional Dry Cleaning</h3>
                        <p class="text-xs text-slate-300 mb-6 flex-grow leading-relaxed">Menjaga integritas kain sensitif seperti wol, sutra, jas, dan kebaya menggunakan pelarut hidrokarbon yang lembut bagi serat pakaian Anda.</p>
                        <ul class="space-y-2 mb-6">
                            <li class="flex items-center gap-2 text-xs font-semibold text-slate-300"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Ahli Penghilang Noda</li>
                            <li class="flex items-center gap-2 text-xs font-semibold text-slate-300"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Hanger &amp; Cover Pelindung</li>
                        </ul>
                    </div>
                    
                    <!-- Service 3 -->
                    <div class="service-card group border border-slate-100 dark:border-slate-800 p-8 rounded-2xl hover:shadow-xl dark:hover:shadow-orange-500/5 transition-all duration-300 flex flex-col bg-slate-50 dark:bg-slate-900/50">
                        <div class="w-14 h-14 bg-primary/10 rounded-xl flex items-center justify-center mb-6 service-icon transition-transform">
                            <span class="material-symbols-outlined text-primary text-3xl">auto_awesome</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-3">Premium Care Treatment</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 flex-grow leading-relaxed">Perawatan khusus gaun malam, gaun pengantin, tas branded, hingga sepatu kulit dengan teknik cuci tangan manual (handwash) & sterilisasi UV.</p>
                        <ul class="space-y-2 mb-6">
                            <li class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Detailing Manual Kebaya & Payet</li>
                            <li class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Kemasan Box Eksklusif</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Elevated Membership -->
        <section class="py-24 bg-slate-50 dark:bg-slate-950 transition-colors" id="membership">
            <div class="max-w-7xl mx-auto px-6 md:px-16">
                <div class="text-center mb-16">
                    <span class="text-xs font-bold text-primary uppercase tracking-wider block mb-1">Loyalty Rewards</span>
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">Royal Loyalty Program</h2>
                    <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 max-w-xl mx-auto mt-2">Dapatkan poin di setiap pesanan dan tingkatkan status tier Anda untuk mendapatkan keistimewaan harga, prioritas pengerjaan, dan gratis antar-jemput.</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Silver Tier -->
                    <div class="bg-white dark:bg-slate-900 p-8 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Tier Perak</span>
                                <span class="material-symbols-outlined text-slate-400 text-3xl">military_tech</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Silver Member</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Default membership untuk seluruh pelanggan baru. Kumpulkan poin untuk naik level.</p>
                            <ul class="space-y-3 mb-8">
                                <li class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400"><span class="material-symbols-outlined text-primary text-base">check_circle</span> 1 Poin per Kelipatan Rp 10.000</li>
                                <li class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Redeem Poin Menjadi Potongan Nota</li>
                            </ul>
                        </div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-6">Aktif Sejak Transaksi Pertama</div>
                    </div>
                    
                    <!-- Gold Tier -->
                    <div class="bg-slate-900 text-white p-8 rounded-2xl border border-slate-800 shadow-xl flex flex-col justify-between relative overflow-hidden group">
                        <div class="absolute -right-12 -bottom-12 w-48 h-48 opacity-5 group-hover:opacity-10 transition-opacity">
                            <span class="material-symbols-outlined text-[192px] text-primary">military_tech</span>
                        </div>
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-xs font-bold text-primary uppercase tracking-widest">Tier Emas</span>
                                <span class="material-symbols-outlined text-primary text-3xl">military_tech</span>
                            </div>
                            <h3 class="text-xl font-bold mb-2">Gold Member Privilege</h3>
                            <p class="text-xs text-slate-300 mb-6">Terbuka otomatis setelah mencapai akumulasi 1.000 Poin. Keistimewaan pelayanan eksklusif.</p>
                            <ul class="space-y-3 mb-8">
                                <li class="flex items-center gap-2 text-xs font-semibold text-slate-300"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Diskon Langsung 5% Semua Layanan</li>
                                <li class="flex items-center gap-2 text-xs font-semibold text-slate-300"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Prioritas Pengerjaan Express</li>
                                <li class="flex items-center gap-2 text-xs font-semibold text-slate-300"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Diskon Promo Spesial Hari Ulang Tahun</li>
                            </ul>
                        </div>
                        <div class="text-xs font-bold text-primary uppercase tracking-widest mt-6 relative z-10">Syarat: Akumulasi Poin &gt;= 1.000</div>
                    </div>
                    
                    <!-- Platinum Tier -->
                    <div class="bg-white dark:bg-slate-900 p-8 rounded-2xl border border-slate-100 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Tier Platinum</span>
                                <span class="material-symbols-outlined text-slate-600 dark:text-slate-300 text-3xl">military_tech</span>
                            </div>
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Platinum Luxury Care</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">Level teratas loyalty dengan kemudahan pelayanan total. Dikhususkan bagi garment eksklusif.</p>
                            <ul class="space-y-3 mb-8">
                                <li class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Diskon Langsung 10% Semua Layanan</li>
                                <li class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Gratis Antar Jemput Tanpa Batas Jarak</li>
                                <li class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400"><span class="material-symbols-outlined text-primary text-base">check_circle</span> Saluran Support Dedicated Concierge</li>
                            </ul>
                        </div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-6">Syarat: Akumulasi Poin &gt;= 5.000</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white border-t border-slate-800 py-12 transition-colors">
        <div class="max-w-7xl mx-auto px-6 md:px-16 flex flex-col items-center justify-center space-y-6">
            <div class="flex items-center gap-2">
                <img alt="Istana Laundry Logo" class="h-8 w-auto object-contain" src="{{ asset('images/logo.webp') }}"/>
                <span class="font-black text-2xl tracking-tighter">ISTANA LAUNDRY</span>
            </div>
            
            <div class="flex flex-wrap justify-center gap-8 text-xs font-bold text-slate-400 uppercase">
                <a class="hover:text-primary transition-colors" href="#services">Layanan</a>
                <a class="hover:text-primary transition-colors" href="#pricing">Tarif</a>
                <a class="hover:text-primary transition-colors" href="#membership">Membership</a>
                <a class="hover:text-primary transition-colors" href="{{ route('login') }}">Area Staff</a>
            </div>
            
            <p class="text-xs text-slate-500 text-center leading-relaxed">
                © {{ date('Y') }} Istana Premium Laundry Samarinda. Hak Cipta Dilindungi Undang-Undang. <br/>
                Memberikan kenyamanan perawatan pakaian terbaik untuk Samarinda.
            </p>
        </div>
    </footer>
</body>
</html>
