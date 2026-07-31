<!DOCTYPE html>
<html class="scroll-smooth" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Istana Laundry Samarinda | Royal Care for Your Finest Garments</title>
    
    <!-- Fonts & Icons (Material Design 3 Expressive typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <!-- Vite CSS/JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined' !important;
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            font-feature-settings: 'liga' 1;
        }
    </style>
</head>
<body class="bg-surface-bright dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased overflow-x-hidden">

    <!-- TopNavBar -->
    <header class="w-full sticky top-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-surface-outline/30 dark:border-slate-800 z-50 transition-colors">
        <nav class="flex justify-between items-center px-6 md:px-16 py-4 max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <img alt="Istana Laundry Logo" class="h-10 w-auto object-contain" src="{{ asset('images/logo.webp') }}"/>
                <span class="font-black font-display text-2xl text-slate-900 dark:text-white tracking-tight">ISTANA LAUNDRY</span>
            </div>
            
            <div class="hidden md:flex items-center space-x-8">
                <a class="font-bold text-xs uppercase tracking-widest text-primary border-b-2 border-primary pb-1 transition-all" href="#services">Layanan</a>
                <a class="font-bold text-xs uppercase tracking-widest text-slate-500 dark:text-slate-400 hover:text-primary transition-colors" href="#pricing">Tarif</a>
                <a class="font-bold text-xs uppercase tracking-widest text-slate-500 dark:text-slate-400 hover:text-primary transition-colors" href="#membership">Membership</a>
                <a class="font-bold text-xs uppercase tracking-widest text-slate-500 dark:text-slate-400 hover:text-primary transition-colors" href="#tracking">Lacak Nota</a>
            </div>
            
            <div class="flex items-center space-x-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="md3-btn-primary shadow-md3-2">
                        <span>Dashboard ERP</span>
                        <span class="material-symbols-outlined text-base">arrow_forward</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="md3-btn-tonal">
                        <span class="material-symbols-outlined text-base">lock</span>
                        <span>Masuk Staff</span>
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        <!-- Hero Section -->
        <section class="relative min-h-[720px] flex items-center overflow-hidden bg-slate-950 text-white">
            <div class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/70 to-transparent z-10"></div>
                <div class="w-full h-full bg-cover bg-center opacity-40 mix-blend-luminosity scale-105 transition-transform duration-1000" 
                     style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAknJiJUNzHo2h5ysFw5MH3lHz2fmvbuzV_zHlSw6W-lUHMU8t-dNkaCyKDYX5HUph0jhNEcJEencgv5b5C0Ect9Ev3ONVUv9kk4PeEmS1CtrgrtedPu9FrMNmk_3F8CDo0O5z6bA-wVhxeRX0muFLBItvPHetUzL0nd3MwpMiQWrtl9np_yxAzgHmB0CMpnsOrAaei-z1Db7AZFJMGGTG0Efsw1QpIPEbKSoscMp6sK4VhksXYEhGjTh7K9a9YsWvkWpJ1Dtf-IWM')">
                </div>
            </div>
            
            <div class="relative z-20 px-6 md:px-16 max-w-7xl mx-auto w-full py-20">
                <div class="max-w-2xl">
                    <span class="inline-block px-4 py-1.5 mb-6 rounded-full bg-primary-container text-primary-on-container text-2xs font-black uppercase tracking-widest border border-primary/30 shadow-sm">
                        Premium Garment Care Samarinda
                    </span>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black font-display mb-6 leading-tight tracking-tight">
                        Royal Care for Your<br/><span class="text-primary">Finest Garments</span>
                    </h1>
                    <p class="text-base md:text-lg text-slate-300 mb-10 leading-relaxed max-w-lg font-medium">
                        Rasakan perawatan cucian berkualitas tinggi oleh para ahli garment. Menggunakan formula ramah lingkungan dan teknologi modern untuk menjaga kelembutan setiap serat pakaian Anda.
                    </p>
                    
                    <!-- Public Tracking Form -->
                    <div id="tracking" class="bg-white/10 backdrop-blur-2xl p-6 md:p-8 rounded-expressive border border-white/20 max-w-xl mb-8 shadow-md3-4">
                        <h3 class="text-sm font-extrabold font-display mb-4 flex items-center gap-2 text-white">
                            <span class="material-symbols-outlined text-primary text-xl">search</span>
                            Lacak Status Pengerjaan Laundry Anda
                        </h3>
                        <form action="{{ route('track') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1 relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xl">barcode_scanner</span>
                                <input type="text" name="order_number" required placeholder="Nomor nota (cth: SMD01-202607-0001)..." 
                                       class="w-full pl-12 pr-4 h-[52px] bg-white text-slate-900 rounded-2xl text-xs font-semibold focus:ring-4 focus:ring-primary/20 focus:outline-none placeholder:text-slate-400 border-none shadow-sm" />
                            </div>
                            <button type="submit" class="md3-btn-primary shadow-md3-2 shrink-0">
                                <span>Lacak Sekarang</span>
                                <span class="material-symbols-outlined text-base">arrow_forward</span>
                            </button>
                        </form>
                        @if(session('error'))
                            <p class="text-red-400 text-xs font-bold mt-3 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-base">error</span>
                                {{ session('error') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Services Section -->
        <section class="py-24 bg-surface-bright dark:bg-slate-900 transition-colors" id="services">
            <div class="max-w-7xl mx-auto px-6 md:px-16">
                <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-4">
                    <div class="max-w-xl">
                        <span class="text-xs font-black font-sans text-primary uppercase tracking-widest block mb-2">Layanan Spesialis</span>
                        <h2 class="text-3xl md:text-4xl font-black font-display text-slate-900 dark:text-white tracking-tight">Perawatan Terbaik Wardrobe Anda</h2>
                    </div>
                    <a class="font-bold text-xs text-primary uppercase tracking-widest flex items-center gap-2 hover:gap-3 transition-all" href="#pricing">
                        Lihat Daftar Harga <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Service 1 -->
                    <div class="md3-card p-8 flex flex-col justify-between">
                        <div>
                            <div class="w-16 h-16 bg-primary-container rounded-expressive flex items-center justify-center mb-6 text-primary shadow-sm">
                                <span class="material-symbols-outlined text-4xl">local_laundry_service</span>
                            </div>
                            <h3 class="text-xl font-black font-display text-slate-900 dark:text-white mb-3">Wet Wash &amp; Fold</h3>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">Pencucian higienis harian menggunakan air demineralisasi, detergen ramah kulit, dan pelembut premium disesuaikan dengan jenis kain.</p>
                            <ul class="space-y-3 mb-6">
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Pemisahan Warna Ketat</li>
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Opsi Kilat 24 Jam</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Service 2 -->
                    <div class="md3-card p-8 bg-slate-950 dark:bg-slate-950 text-white border-slate-800 flex flex-col justify-between shadow-md3-3">
                        <div>
                            <div class="w-16 h-16 bg-white/10 rounded-expressive flex items-center justify-center mb-6 text-white shadow-sm">
                                <span class="material-symbols-outlined text-4xl">dry_cleaning</span>
                            </div>
                            <h3 class="text-xl font-black font-display mb-3">Professional Dry Cleaning</h3>
                            <p class="text-xs font-medium text-slate-300 mb-6 leading-relaxed">Menjaga integritas kain sensitif seperti wol, sutra, jas, dan kebaya menggunakan pelarut hidrokarbon yang lembut bagi serat pakaian Anda.</p>
                            <ul class="space-y-3 mb-6">
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-200"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Ahli Penghilang Noda</li>
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-200"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Hanger &amp; Cover Pelindung</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Service 3 -->
                    <div class="md3-card p-8 flex flex-col justify-between">
                        <div>
                            <div class="w-16 h-16 bg-primary-container rounded-expressive flex items-center justify-center mb-6 text-primary shadow-sm">
                                <span class="material-symbols-outlined text-4xl">auto_awesome</span>
                            </div>
                            <h3 class="text-xl font-black font-display text-slate-900 dark:text-white mb-3">Premium Care Treatment</h3>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">Perawatan khusus gaun malam, gaun pengantin, tas branded, hingga sepatu kulit dengan teknik cuci tangan manual (handwash) & sterilisasi UV.</p>
                            <ul class="space-y-3 mb-6">
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Detailing Manual Kebaya & Payet</li>
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Kemasan Box Eksklusif</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Elevated Membership -->
        <section class="py-24 bg-surface-container-low dark:bg-slate-950 transition-colors" id="membership">
            <div class="max-w-7xl mx-auto px-6 md:px-16">
                <div class="text-center mb-16">
                    <span class="text-xs font-black font-sans text-primary uppercase tracking-widest block mb-2">Loyalty Rewards</span>
                    <h2 class="text-3xl md:text-4xl font-black font-display text-slate-900 dark:text-white tracking-tight">Royal Loyalty Program</h2>
                    <p class="text-xs md:text-sm font-medium text-slate-500 dark:text-slate-400 max-w-xl mx-auto mt-3">Dapatkan poin di setiap pesanan dan tingkatkan status tier Anda untuk mendapatkan keistimewaan harga, prioritas pengerjaan, dan gratis antar-jemput.</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Silver Tier -->
                    <div class="md3-card p-8 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-widest">Tier Perak</span>
                                <span class="material-symbols-outlined text-slate-400 text-3xl">military_tech</span>
                            </div>
                            <h3 class="text-2xl font-black font-display text-slate-900 dark:text-white mb-2">Silver Member</h3>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-6">Default membership untuk seluruh pelanggan baru. Kumpulkan poin untuk naik level.</p>
                            <ul class="space-y-3 mb-8">
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> 1 Poin per Kelipatan Rp 10.000</li>
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Redeem Poin Menjadi Potongan Nota</li>
                            </ul>
                        </div>
                        <div class="text-2xs font-extrabold text-slate-400 uppercase tracking-widest pt-4 border-t border-slate-100 dark:border-slate-800">Aktif Sejak Transaksi Pertama</div>
                    </div>
                    
                    <!-- Gold Tier -->
                    <div class="md3-card p-8 bg-slate-950 dark:bg-slate-950 text-white border-slate-800 flex flex-col justify-between relative overflow-hidden shadow-md3-3">
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-2xs font-extrabold text-primary uppercase tracking-widest">Tier Emas</span>
                                <span class="material-symbols-outlined text-primary text-3xl">military_tech</span>
                            </div>
                            <h3 class="text-2xl font-black font-display mb-2">Gold Member Privilege</h3>
                            <p class="text-xs font-medium text-slate-300 mb-6">Terbuka otomatis setelah mencapai akumulasi 1.000 Poin. Keistimewaan pelayanan eksklusif.</p>
                            <ul class="space-y-3 mb-8">
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-200"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Diskon Langsung 5% Semua Layanan</li>
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-200"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Prioritas Pengerjaan Express</li>
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-200"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Diskon Promo Spesial Ulang Tahun</li>
                            </ul>
                        </div>
                        <div class="text-2xs font-extrabold text-primary uppercase tracking-widest pt-4 border-t border-slate-800 relative z-10">Syarat: Akumulasi Poin &gt;= 1.000</div>
                    </div>
                    
                    <!-- Platinum Tier -->
                    <div class="md3-card p-8 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-2xs font-extrabold text-slate-600 dark:text-slate-300 uppercase tracking-widest">Tier Platinum</span>
                                <span class="material-symbols-outlined text-slate-600 dark:text-slate-300 text-3xl">military_tech</span>
                            </div>
                            <h3 class="text-2xl font-black font-display text-slate-900 dark:text-white mb-2">Platinum Luxury Care</h3>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-6">Level teratas loyalty dengan kemudahan pelayanan total. Dikhususkan bagi garment eksklusif.</p>
                            <ul class="space-y-3 mb-8">
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Diskon Langsung 10% Semua Layanan</li>
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Gratis Antar Jemput Tanpa Batas Jarak</li>
                                <li class="flex items-center gap-2 text-xs font-bold text-slate-600 dark:text-slate-300"><span class="material-symbols-outlined text-primary text-lg">check_circle</span> Dedicated Concierge Support</li>
                            </ul>
                        </div>
                        <div class="text-2xs font-extrabold text-slate-400 uppercase tracking-widest pt-4 border-t border-slate-100 dark:border-slate-800">Syarat: Akumulasi Poin &gt;= 5.000</div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 text-white border-t border-slate-900 py-16 transition-colors">
        <div class="max-w-7xl mx-auto px-6 md:px-16 flex flex-col items-center justify-center space-y-8">
            <div class="flex items-center gap-3">
                <img alt="Istana Laundry Logo" class="h-9 w-auto object-contain" src="{{ asset('images/logo.webp') }}"/>
                <span class="font-black font-display text-2xl tracking-tight">ISTANA LAUNDRY</span>
            </div>
            
            <div class="flex flex-wrap justify-center gap-8 text-xs font-extrabold font-sans text-slate-400 uppercase tracking-widest">
                <a class="hover:text-primary transition-colors" href="#services">Layanan</a>
                <a class="hover:text-primary transition-colors" href="#pricing">Tarif</a>
                <a class="hover:text-primary transition-colors" href="#membership">Membership</a>
                <a class="hover:text-primary transition-colors" href="{{ route('login') }}">Area Staff</a>
            </div>
            
            <p class="text-xs text-slate-500 font-medium text-center leading-relaxed">
                © {{ date('Y') }} Istana Premium Laundry Samarinda. Hak Cipta Dilindungi Undang-Undang. <br/>
                Memberikan kenyamanan perawatan pakaian terbaik untuk Samarinda.
            </p>
        </div>
    </footer>
</body>
</html>
