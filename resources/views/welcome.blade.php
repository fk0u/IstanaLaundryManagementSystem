<!DOCTYPE html>
<html class="scroll-smooth" lang="id" x-data="{ darkMode: false }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Istana Laundry Samarinda | Royal Care for Your Finest Garments</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />
    
    <!-- Leaflet.js Interactive Maps CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

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
            font-feature-settings: 'liga' 1;
        }

        /* Glassmorphism & Motion Keyframes */
        .glass-panel {
            background: rgba(15, 23, 42, 0.80);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .glow-orange {
            box-shadow: 0 0 40px -5px rgba(255, 102, 0, 0.4);
        }

        /* Custom Pulse Marker Animation */
        @keyframes pulse-orange {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 102, 0, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 15px rgba(255, 102, 0, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(255, 102, 0, 0); }
        }
        .animate-pulse-orange {
            animation: pulse-orange 2s infinite;
        }

        /* Leaflet Dark Custom Popup Styling */
        .leaflet-popup-content-wrapper {
            background: #0f172a !important;
            color: #ffffff !important;
            border-radius: 1rem !important;
            padding: 4px !important;
            border: 1px solid rgba(255, 102, 0, 0.4) !important;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5) !important;
        }
        .leaflet-popup-tip {
            background: #0f172a !important;
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased selection:bg-[#FF6600] selection:text-white transition-colors duration-300 overflow-x-hidden"
      x-data="{
          orderModalOpen: false,
          modalStep: 1,
          selectedBranch: 'WJK',
          serviceType: 'kiloan',
          qty: 5,
          speed: 'regular',
          addonParfum: true,
          addonSoftener: false,
          addonUV: false,
          addonGarmentBag: false,
          customerName: '',
          customerPhone: '',
          customerAddress: '',
          customerNotes: '',
          deliveryMethod: 'pickup',
          
          branches: {
              'WJK': { name: 'Wijaya Kusuma (Pusat)', phone: '628115550001', address: 'Jl. Wijaya Kusuma Blok V-C Gg. Rina, Air Hitam', lat: -0.48696232, lng: 117.12927615, maps: 'https://maps.app.goo.gl/vGYX4GPX8qbyVC5t6' },
              'SUT': { name: 'Dr Sutomo', phone: '628115550002', address: 'Jl. Dr. Sutomo, Sidodadi, Kec. Samarinda Ulu', lat: -0.47985591, lng: 117.14684332, maps: 'https://maps.app.goo.gl/hfExyB2DF99JDhYr8' },
              'HID': { name: 'Pangeran Hidayatullah', phone: '628115550003', address: 'Jl. Pangeran Hidayatullah, Karang Mumus', lat: -0.50231714, lng: 117.15582759, maps: 'https://maps.app.goo.gl/La8rGoQ6kxgtHrnEA' },
              'LMG': { name: 'Lambung Mangkurat', phone: '628115550004', address: 'Jl. Lambung Mangkurat, Sungai Pinang Dalam', lat: -0.48598488, lng: 117.16400973, maps: 'https://maps.app.goo.gl/eAduH777U6U3mqj7A' },
              'GTS': { name: 'Grand Taman Sari', phone: '628115550005', address: 'Perumahan Grand Taman Sari, Harapan Baru', lat: -0.56096529, lng: 117.11677339, maps: 'https://maps.app.goo.gl/zYeoDMZBqKdB1CUR8' }
          },
          
          prices: {
              'kiloan': 10000,
              'dry_clean': 35000,
              'shoes_bag': 45000,
              'bed_cover': 30000,
              'curtain': 15000
          },

          get calculatedTotal() {
              let basePrice = (this.prices[this.serviceType] || 10000) * this.qty;
              if (this.speed === 'express') basePrice *= 1.25;
              if (this.speed === 'flash') basePrice *= 1.50;
              
              if (this.addonParfum) basePrice += (2000 * (this.serviceType === 'kiloan' ? this.qty : 1));
              if (this.addonSoftener) basePrice += (2000 * (this.serviceType === 'kiloan' ? this.qty : 1));
              if (this.addonUV) basePrice += 5000;
              if (this.addonGarmentBag) basePrice += (5000 * (this.serviceType === 'kiloan' ? 1 : this.qty));
              
              return Math.round(basePrice);
          },

          get speedHours() {
              if (this.speed === 'express') return 24;
              if (this.speed === 'flash') return 6;
              return 48;
          },

          openOrderModal(branchCode = 'WJK') {
              this.selectedBranch = branchCode;
              this.modalStep = 1;
              this.orderModalOpen = true;
          },

          sendWhatsAppOrder() {
              let b = this.branches[this.selectedBranch] || this.branches['WJK'];
              let text = '*FORM PEMESANAN ONLINE ISTANA LAUNDRY*' + '\n';
              text += '=================================' + '\n';
              text += '📍 *Cabang Target:* ' + b.name + '\n';
              text += '👤 *Nama Pemesan:* ' + (this.customerName || '-') + '\n';
              text += '📞 *No. WhatsApp:* ' + (this.customerPhone || '-') + '\n';
              text += '🚚 *Metode:* ' + (this.deliveryMethod === 'pickup' ? 'Antar-Jemput Kurir Ke Rumah' : 'Antar Mandiri ke Outlet') + '\n';
              if (this.deliveryMethod === 'pickup') {
                  text += '🏠 *Alamat Penjemputan:* ' + (this.customerAddress || '-') + '\n';
              }
              text += '---------------------------------' + '\n';
              text += '👔 *Layanan:* ' + this.serviceType.toUpperCase() + ' (' + this.qty + ' ' + (this.serviceType === 'kiloan' ? 'Kg' : 'Pcs/Pasang') + ')\n';
              text += '⚡ *Kecepatan:* ' + this.speed.toUpperCase() + ' (' + this.speedHours + ' Jam)\n';
              text += '💰 *Estimasi Total:* Rp ' + this.calculatedTotal.toLocaleString('id-ID') + '\n';
              if (this.customerNotes) {
                  text += '📝 *Catatan Pakaian:* ' + this.customerNotes + '\n';
              }
              text += '=================================' + '\n';
              text += 'Mohon segera diproses dan dijadwalkan kurir penjemputan. Terima kasih!';

              let waUrl = 'https://wa.me/' + b.phone + '?text=' + encodeURIComponent(text);
              window.open(waUrl, '_blank');
              this.orderModalOpen = false;
          }
      }">

    <!-- Top Navigation Bar -->
    <header class="w-full sticky top-0 bg-white/90 dark:bg-slate-900/90 backdrop-blur-2xl border-b border-slate-200/80 dark:border-slate-800/80 z-40 transition-colors">
        <nav class="flex justify-between items-center px-4 sm:px-8 lg:px-12 py-3.5 max-w-7xl mx-auto">
            <!-- Brand Logo -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <img src="{{ asset('images/logo.webp') }}" alt="Istana Laundry" class="h-9 w-auto object-contain transition-transform group-hover:scale-105">
                <div class="flex flex-col">
                    <span class="font-black font-display text-xl text-slate-900 dark:text-white tracking-tight leading-none">ISTANA LAUNDRY</span>
                    <span class="text-[10px] font-bold tracking-widest uppercase text-[#FF6600] mt-0.5">Enterprise Garment Care</span>
                </div>
            </a>
            
            <!-- Center Navigation Links -->
            <div class="hidden lg:flex items-center space-x-8">
                <a class="font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hover:text-[#FF6600] transition-colors" href="#tracking">Lacak Nota</a>
                <a class="font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hover:text-[#FF6600] transition-colors" href="#calculator">Kalkulator Tarif</a>
                <a class="font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hover:text-[#FF6600] transition-colors" href="#outlets">Peta 5 Outlet</a>
                <a class="font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hover:text-[#FF6600] transition-colors" href="#membership">Royal Member</a>
                <a class="font-bold text-xs uppercase tracking-wider text-slate-600 dark:text-slate-300 hover:text-[#FF6600] transition-colors" href="#services">Layanan</a>
            </div>
            
            <!-- Right Actions (Theme Toggle, Modal Trigger, Staff Login) -->
            <div class="flex items-center space-x-3">
                <button @click="darkMode = !darkMode" 
                        type="button" 
                        class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition-colors"
                        title="Toggle Light/Dark Theme">
                    <span x-show="!darkMode" class="material-symbols-outlined text-xl">dark_mode</span>
                    <span x-show="darkMode" class="material-symbols-outlined text-xl text-amber-400">light_mode</span>
                </button>

                <!-- Order Online Button Trigger -->
                <button @click="openOrderModal()" 
                        type="button" 
                        class="px-4 py-2 bg-gradient-to-r from-[#FF6600] to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-orange-500/25 flex items-center gap-1.5 transition-all active:scale-95 glow-orange">
                    <span class="material-symbols-outlined text-sm">shopping_bag</span>
                    <span>Pesan Online</span>
                </button>

                @auth
                    <a href="{{ url('/dashboard') }}" class="px-4 py-2 bg-slate-900 dark:bg-slate-800 text-white font-extrabold text-xs rounded-xl flex items-center gap-1.5 transition-all">
                        <span>ERP</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:flex px-3.5 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 font-extrabold text-xs rounded-xl items-center gap-1 transition-all border border-slate-200 dark:border-slate-700">
                        <span class="material-symbols-outlined text-sm text-[#FF6600]">lock</span>
                        <span>Staf</span>
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        <!-- Hero Section with Animated Dynamic Canvas & Motion Glow -->
        <section class="relative min-h-[840px] flex items-center overflow-hidden bg-slate-950 text-white py-16 sm:py-24">
            <!-- Animated Background Image & Radiant Canvas Overlay -->
            <div class="absolute inset-0 z-0">
                <div class="absolute inset-0 bg-gradient-to-b from-slate-950/80 via-slate-950/90 to-slate-950 z-10"></div>
                <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-orange-500/30 via-slate-950/60 to-transparent z-10"></div>
                
                <!-- Floating Ambient Glow Orbs -->
                <div class="absolute top-1/4 left-10 w-96 h-96 bg-[#FF6600]/15 rounded-full blur-[120px] animate-pulse"></div>
                <div class="absolute bottom-10 right-10 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-[140px] animate-pulse" style="animation-duration: 6s;"></div>

                <div class="w-full h-full bg-cover bg-center opacity-30 mix-blend-luminosity scale-105 transition-transform duration-1000" 
                     style="background-image: url('https://images.unsplash.com/photo-1545173168-9f1947eebb7f?auto=format&fit=crop&w=1600&q=80')">
                </div>
            </div>
            
            <div class="relative z-20 px-4 sm:px-8 lg:px-12 max-w-7xl mx-auto w-full">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                    
                    <!-- Left Hero Text & Dynamic Actions -->
                    <div class="lg:col-span-7">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-orange-500/10 border border-orange-500/30 text-[#FF6600] text-xs font-black uppercase tracking-wider mb-6 backdrop-blur-md">
                            <span class="material-symbols-outlined text-sm animate-spin" style="animation-duration: 8s;">verified</span>
                            <span>#1 Enterprise Garment &amp; Dry Care Samarinda</span>
                        </div>

                        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black font-display mb-6 leading-[1.08] tracking-tight">
                            Royal Care for Your <br/>
                            <span class="bg-gradient-to-r from-[#FF6600] via-amber-400 to-orange-500 bg-clip-text text-transparent">Finest Garments</span>
                        </h1>

                        <p class="text-base sm:text-lg text-slate-300 mb-8 leading-relaxed max-w-xl font-medium">
                            Layanan cuci kiloan premium, dry cleaning jas &amp; gaun, serta treatment sepatu branded di 5 cabang resmi Samarinda. Dilengkapi kurir antar-jemput express ke rumah Anda.
                        </p>

                        <!-- Primary Dynamic CTA Buttons -->
                        <div class="flex flex-wrap items-center gap-4 mb-10">
                            <button @click="openOrderModal()" 
                                    class="px-7 py-4 bg-gradient-to-r from-[#FF6600] to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-black text-sm rounded-2xl shadow-xl shadow-orange-500/30 flex items-center gap-2.5 transition-all hover:scale-105 active:scale-95 glow-orange">
                                <span class="material-symbols-outlined text-xl">shopping_cart_checkout</span>
                                <span>Pesan Online Sekarang</span>
                            </button>

                            <a href="#calculator" 
                               class="px-6 py-4 bg-slate-900/90 hover:bg-slate-800 text-slate-200 font-extrabold text-sm rounded-2xl border border-slate-700/80 flex items-center gap-2 transition-all hover:scale-105 active:scale-95">
                                <span class="material-symbols-outlined text-xl text-[#FF6600]">calculate</span>
                                <span>Kalkulator Tarif</span>
                            </a>
                        </div>

                        <!-- Real-Time Order Tracking Widget Box -->
                        <div id="tracking" class="glass-panel p-6 sm:p-7 rounded-3xl max-w-xl shadow-2xl relative overflow-hidden border border-white/15">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-xs font-black uppercase tracking-wider text-slate-200 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[#FF6600] text-lg">barcode_scanner</span>
                                    <span>Lacak Status Cucian Real-Time</span>
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 font-extrabold text-[10px] uppercase">Live Sync</span>
                            </div>

                            <form action="{{ route('track') }}" method="GET" class="flex flex-col sm:flex-row gap-2.5">
                                <div class="flex-1 relative">
                                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                                    <input type="text" name="order_number" required placeholder="Masukkan Nomor Nota (contoh: ORD-WJK-...)" 
                                           class="w-full pl-10 pr-4 h-11 bg-slate-900/90 text-white rounded-xl text-xs font-semibold focus:ring-2 focus:ring-[#FF6600] focus:outline-none border border-slate-700/80 placeholder:text-slate-500" />
                                </div>
                                <button type="submit" class="h-11 px-6 bg-[#FF6600] hover:bg-orange-600 text-white font-black text-xs rounded-xl flex items-center justify-center gap-1.5 transition-colors shrink-0 shadow-md shadow-orange-500/20">
                                    <span>Lacak</span>
                                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                </button>
                            </form>

                            @if(session('error'))
                                <p class="text-rose-400 text-xs font-bold mt-2.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm">warning</span>
                                    {{ session('error') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Right Hero Interactive Card Showcase -->
                    <div class="lg:col-span-5 relative hidden lg:block">
                        <div class="relative w-full aspect-[4/5] rounded-3xl overflow-hidden shadow-2xl border border-white/10 group">
                            <img src="https://images.unsplash.com/photo-1517677208171-0bc6725a3e60?auto=format&fit=crop&w=800&q=80" 
                                 alt="Istana Laundry Samarinda" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" />
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent"></div>

                            <!-- Animated Floating Badge 1 -->
                            <div class="absolute top-6 right-6 glass-panel p-4 rounded-2xl flex items-center gap-3 animate-bounce shadow-xl" style="animation-duration: 4s;">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                                    <span class="material-symbols-outlined">verified_user</span>
                                </div>
                                <div>
                                    <span class="block text-2xs font-extrabold uppercase text-slate-400">Garansi Kepuasan</span>
                                    <span class="block text-xs font-black text-white">Cuci Ulang 100% Gratis</span>
                                </div>
                            </div>

                            <!-- Animated Floating Badge 2 -->
                            <div class="absolute bottom-6 left-6 right-6 glass-panel p-5 rounded-2xl border border-white/15">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-2xs font-black uppercase text-[#FF6600] tracking-wider">Layanan Antar-Jemput</span>
                                    <span class="text-xs font-bold text-slate-300">5 Cabang Samarinda</span>
                                </div>
                                <h4 class="text-sm font-black text-white">Kurir Siap Jemput Pakaian Anda Ke Rumah</h4>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Executive Proof Statistics Strip -->
        <section class="bg-slate-900 border-y border-slate-800 text-white py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-12">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
                    <div class="p-4 border-r border-slate-800 last:border-r-0">
                        <span class="block text-2xl sm:text-4xl font-black font-display text-[#FF6600]">50.000+</span>
                        <span class="block text-2xs sm:text-xs font-extrabold uppercase tracking-wider text-slate-400 mt-1">Kg Cucian Terproses</span>
                    </div>
                    <div class="p-4 border-r border-slate-800 last:border-r-0">
                        <span class="block text-2xl sm:text-4xl font-black font-display text-white">3.500+</span>
                        <span class="block text-2xs sm:text-xs font-extrabold uppercase tracking-wider text-slate-400 mt-1">Member Setia</span>
                    </div>
                    <div class="p-4 border-r border-slate-800 last:border-r-0">
                        <span class="block text-2xl sm:text-4xl font-black font-display text-[#FF6600]">99.8%</span>
                        <span class="block text-2xs sm:text-xs font-extrabold uppercase tracking-wider text-slate-400 mt-1">Kepuasan Pelanggan</span>
                    </div>
                    <div class="p-4">
                        <span class="block text-2xl sm:text-4xl font-black font-display text-white">5 Outlet</span>
                        <span class="block text-2xs sm:text-xs font-extrabold uppercase tracking-wider text-slate-400 mt-1">Cabang Resmi Samarinda</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Real-Case Interactive Tarif Calculator Section -->
        <section id="calculator" class="py-20 bg-white dark:bg-slate-900 transition-colors">
            <div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-12">
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <span class="text-xs font-black uppercase tracking-widest text-[#FF6600] block mb-2">Simulasi Biaya Real-Case</span>
                    <h2 class="text-3xl sm:text-4xl font-black font-display text-slate-900 dark:text-white tracking-tight">Kalkulator Tarif &amp; Add-On Layanan</h2>
                    <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-2">Hitung perkiraan biaya pakaian Anda dan langsung lanjutkan pemesanan online ke cabang pilihan.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                    <!-- Calculator Options (7 Cols) -->
                    <div class="lg:col-span-7 bg-slate-50 dark:bg-slate-950 p-6 sm:p-8 rounded-3xl border border-slate-200 dark:border-slate-800 flex flex-col justify-between">
                        <div class="space-y-6">
                            <!-- 1. Select Service -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3">1. Pilih Jenis Layanan</label>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                    <button type="button" @click="serviceType = 'kiloan'; if(qty<1) qty=5;"
                                            :class="serviceType === 'kiloan' ? 'bg-[#FF6600] text-white border-[#FF6600]' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-800 hover:border-slate-400'"
                                            class="p-3.5 rounded-2xl border text-center transition-all">
                                        <span class="material-symbols-outlined text-2xl block mb-1">local_laundry_service</span>
                                        <span class="text-xs font-black block">Cuci Kiloan</span>
                                        <span class="text-[10px] opacity-80">Rp 10.000 /kg</span>
                                    </button>

                                    <button type="button" @click="serviceType = 'dry_clean'; if(qty>10) qty=1;"
                                            :class="serviceType === 'dry_clean' ? 'bg-[#FF6600] text-white border-[#FF6600]' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-800 hover:border-slate-400'"
                                            class="p-3.5 rounded-2xl border text-center transition-all">
                                        <span class="material-symbols-outlined text-2xl block mb-1">dry_cleaning</span>
                                        <span class="text-xs font-black block">Dry Clean Jas/Kebaya</span>
                                        <span class="text-[10px] opacity-80">Rp 35.000 /pcs</span>
                                    </button>

                                    <button type="button" @click="serviceType = 'shoes_bag'; if(qty>10) qty=1;"
                                            :class="serviceType === 'shoes_bag' ? 'bg-[#FF6600] text-white border-[#FF6600]' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-800 hover:border-slate-400'"
                                            class="p-3.5 rounded-2xl border text-center transition-all">
                                        <span class="material-symbols-outlined text-2xl block mb-1">roller_skating</span>
                                        <span class="text-xs font-black block">Sepatu / Tas Spa</span>
                                        <span class="text-[10px] opacity-80">Rp 45.000 /pasang</span>
                                    </button>

                                    <button type="button" @click="serviceType = 'bed_cover'; if(qty>10) qty=1;"
                                            :class="serviceType === 'bed_cover' ? 'bg-[#FF6600] text-white border-[#FF6600]' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-800 hover:border-slate-400'"
                                            class="p-3.5 rounded-2xl border text-center transition-all">
                                        <span class="material-symbols-outlined text-2xl block mb-1">bed</span>
                                        <span class="text-xs font-black block">Bed Cover / Selimut</span>
                                        <span class="text-[10px] opacity-80">Rp 30.000 /pcs</span>
                                    </button>

                                    <button type="button" @click="serviceType = 'curtain'; if(qty>20) qty=5;"
                                            :class="serviceType === 'curtain' ? 'bg-[#FF6600] text-white border-[#FF6600]' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-800 hover:border-slate-400'"
                                            class="p-3.5 rounded-2xl border text-center transition-all">
                                        <span class="material-symbols-outlined text-2xl block mb-1">curtains</span>
                                        <span class="text-xs font-black block">Gorden / Hordeng</span>
                                        <span class="text-[10px] opacity-80">Rp 15.000 /m²</span>
                                    </button>
                                </div>
                            </div>

                            <!-- 2. Quantity Slider -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300">2. Kuantitas / Berat</label>
                                    <span class="text-xs font-black text-[#FF6600]" x-text="qty + ' ' + (serviceType === 'kiloan' ? 'Kg' : (serviceType === 'curtain' ? 'm²' : 'Pcs'))"></span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <input type="range" min="1" max="30" x-model.number="qty" class="w-full accent-[#FF6600]" />
                                    <input type="number" min="1" max="100" x-model.number="qty" class="w-20 h-10 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-center text-xs font-bold" />
                                </div>
                            </div>

                            <!-- 3. Turnaround Speed -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">3. Kecepatan Pengerjaan</label>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <button type="button" @click="speed = 'regular'"
                                            :class="speed === 'regular' ? 'border-[#FF6600] bg-orange-500/10 text-[#FF6600]' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900'"
                                            class="p-3 rounded-2xl border text-left transition-all">
                                        <span class="block text-xs font-bold">Reguler (48 Jam)</span>
                                        <span class="text-[10px] text-slate-400">Harga Standar</span>
                                    </button>

                                    <button type="button" @click="speed = 'express'"
                                            :class="speed === 'express' ? 'border-[#FF6600] bg-orange-500/10 text-[#FF6600]' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900'"
                                            class="p-3 rounded-2xl border text-left transition-all">
                                        <span class="block text-xs font-bold">Express (24 Jam)</span>
                                        <span class="text-[10px] text-slate-400">+25% Biaya</span>
                                    </button>

                                    <button type="button" @click="speed = 'flash'"
                                            :class="speed === 'flash' ? 'border-[#FF6600] bg-orange-500/10 text-[#FF6600]' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900'"
                                            class="p-3 rounded-2xl border text-left transition-all">
                                        <span class="block text-xs font-bold">Flash (6 Jam)</span>
                                        <span class="text-[10px] text-slate-400">+50% Biaya</span>
                                    </button>
                                </div>
                            </div>

                            <!-- 4. Add-on Options -->
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">4. Add-On Perawatan Ekstra</label>
                                <div class="grid grid-cols-2 gap-2 text-xs font-semibold">
                                    <label class="flex items-center gap-2 p-2.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 cursor-pointer">
                                        <input type="checkbox" x-model="addonParfum" class="accent-[#FF6600] rounded" />
                                        <span>Parfum Premium (+Rp 2k)</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 cursor-pointer">
                                        <input type="checkbox" x-model="addonSoftener" class="accent-[#FF6600] rounded" />
                                        <span>Extra Softener (+Rp 2k)</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 cursor-pointer">
                                        <input type="checkbox" x-model="addonUV" class="accent-[#FF6600] rounded" />
                                        <span>Sterilisasi UV (+Rp 5k)</span>
                                    </label>
                                    <label class="flex items-center gap-2 p-2.5 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 cursor-pointer">
                                        <input type="checkbox" x-model="addonGarmentBag" class="accent-[#FF6600] rounded" />
                                        <span>Garment Cover (+Rp 5k)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calculator Live Output & Direct Order CTA (5 Cols) -->
                    <div class="lg:col-span-5 bg-slate-900 text-white p-6 sm:p-8 rounded-3xl border border-slate-800 flex flex-col justify-between shadow-2xl relative overflow-hidden">
                        <div class="absolute -top-24 -right-24 w-48 h-48 bg-[#FF6600]/20 rounded-full blur-3xl"></div>
                        
                        <div>
                            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800">
                                <span class="text-xs font-black uppercase tracking-wider text-slate-400">Ringkasan Kalkulasi</span>
                                <span class="px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-400 font-extrabold text-[10px]" x-text="'Selesai dalam ' + speedHours + ' Jam'"></span>
                            </div>

                            <div class="space-y-3 mb-8 text-xs font-medium text-slate-300">
                                <div class="flex justify-between">
                                    <span>Jenis Layanan</span>
                                    <span class="font-bold text-white uppercase" x-text="serviceType"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Kuantitas</span>
                                    <span class="font-bold text-white" x-text="qty + ' ' + (serviceType === 'kiloan' ? 'Kg' : (serviceType === 'curtain' ? 'm²' : 'Pcs'))"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Kecepatan</span>
                                    <span class="font-bold text-white uppercase" x-text="speed"></span>
                                </div>
                                <div class="flex justify-between pt-2 border-t border-slate-800 text-[11px] text-slate-400">
                                    <span>Add-On Terpilih</span>
                                    <span class="font-bold text-slate-200" x-text="(addonParfum?'Parfum ':'') + (addonSoftener?'Softener ':'') + (addonUV?'UV ':'') + (addonGarmentBag?'Cover':'')"></span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-800">
                            <span class="block text-2xs font-black uppercase tracking-widest text-slate-400 mb-1">Estimasi Total Biaya</span>
                            <div class="text-3xl sm:text-4xl font-black font-display text-[#FF6600] mb-6" x-text="'Rp ' + calculatedTotal.toLocaleString('id-ID')"></div>

                            <button @click="openOrderModal('WJK')" 
                                    class="w-full py-4 bg-gradient-to-r from-[#FF6600] to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-black text-xs rounded-2xl flex items-center justify-center gap-2 transition-all hover:scale-105 active:scale-95 shadow-xl shadow-orange-500/25">
                                <span class="material-symbols-outlined text-lg">shopping_cart_checkout</span>
                                <span>Lanjut Pesan Online di Cabang Target</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Single Integrated Interactive Leaflet Canvas Map Section -->
        <section id="outlets" class="py-20 bg-slate-950 text-white transition-colors relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-12">
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-xs font-black text-[#FF6600] uppercase tracking-widest block mb-2">Peta Lokasi Terintegrasi</span>
                    <h2 class="text-3xl sm:text-4xl font-black font-display tracking-tight">Peta Pinpoint 5 Outlet Resmi Samarinda</h2>
                    <p class="text-xs sm:text-sm font-medium text-slate-400 mt-2">Klik pinpoint lokasi untuk melihat informasi cabang atau langsung pesan online antar-jemput.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <!-- Interactive Leaflet Canvas (8 Cols) -->
                    <div class="lg:col-span-8 rounded-3xl overflow-hidden border border-slate-800 shadow-2xl bg-slate-900 h-[480px] relative z-10" id="leafletMapContainer" x-data="{ isSatellite: false }">
                        <div class="absolute top-4 right-4 z-20 flex items-center gap-1.5 bg-slate-900/90 backdrop-blur-md p-1.5 rounded-2xl border border-slate-700 shadow-lg">
                            <button type="button" 
                                    @click="isSatellite = false; toggleMapSatellite(false);" 
                                    :class="!isSatellite ? 'bg-[#FF6600] text-white' : 'text-slate-400 hover:text-white bg-slate-800/80'"
                                    class="px-3 py-1.5 rounded-xl text-2xs font-extrabold flex items-center gap-1 transition-all">
                                <span class="material-symbols-outlined text-xs">map</span>
                                <span>Peta Google</span>
                            </button>
                            <button type="button" 
                                    @click="isSatellite = true; toggleMapSatellite(true);" 
                                    :class="isSatellite ? 'bg-[#FF6600] text-white' : 'text-slate-400 hover:text-white bg-slate-800/80'"
                                    class="px-3 py-1.5 rounded-xl text-2xs font-extrabold flex items-center gap-1 transition-all">
                                <span class="material-symbols-outlined text-xs">satellite_alt</span>
                                <span>Satelit Google</span>
                            </button>
                        </div>
                        <div id="samarindaMap" class="w-full h-full"></div>
                    </div>

                    <!-- Branch Selector List & Instant Order Action (4 Cols) -->
                    <div class="lg:col-span-4 space-y-3 max-h-[480px] overflow-y-auto pr-1">
                        <template x-for="(b, code) in branches" :key="code">
                            <div @click="selectedBranch = code; flyToBranch(code);" 
                                 :class="selectedBranch === code ? 'border-[#FF6600] bg-orange-500/10' : 'border-slate-800 bg-slate-900/80 hover:border-slate-700'"
                                 class="p-4 rounded-2xl border cursor-pointer transition-all flex flex-col justify-between gap-3">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs font-black text-white" x-text="b.name"></span>
                                        <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold uppercase">Buka</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 leading-relaxed flex items-start gap-1">
                                        <span class="material-symbols-outlined text-xs text-[#FF6600] shrink-0 mt-0.5">location_on</span>
                                        <span x-text="b.address"></span>
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 pt-2 border-t border-slate-800/80">
                                    <button type="button" 
                                            @click.stop="openOrderModal(code)"
                                            class="flex-1 py-1.5 bg-[#FF6600] hover:bg-orange-600 text-white text-[11px] font-extrabold rounded-xl flex items-center justify-center gap-1 transition-colors">
                                        <span class="material-symbols-outlined text-xs">shopping_bag</span>
                                        <span>Pesan di Cabang Ini</span>
                                    </button>
                                    <a :href="b.maps" target="_blank" @click.stop=""
                                       class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-bold rounded-xl flex items-center justify-center">
                                        <span class="material-symbols-outlined text-xs text-rose-400">map</span>
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Services Grid Section -->
        <section class="py-24 bg-slate-50 dark:bg-slate-950 transition-colors" id="services">
            <div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-12">
                <div class="text-center max-w-xl mx-auto mb-16">
                    <span class="text-xs font-black text-[#FF6600] uppercase tracking-widest block mb-2">Layanan Spesialis</span>
                    <h2 class="text-3xl sm:text-4xl font-black font-display text-slate-900 dark:text-white tracking-tight">Perawatan Profesional Wardrobe Anda</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Service 1 -->
                    <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:border-[#FF6600] transition-all hover:-translate-y-1 group">
                        <div>
                            <div class="w-14 h-14 bg-orange-500/10 rounded-2xl flex items-center justify-center mb-6 text-[#FF6600] group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-3xl">local_laundry_service</span>
                            </div>
                            <h3 class="text-xl font-black font-display text-slate-900 dark:text-white mb-3">Wet Wash &amp; Fresh Fold</h3>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">Pencucian higienis harian menggunakan air demineralisasi, detergen ramah kulit, dan pelembut premium disesuaikan dengan jenis kain.</p>
                            <ul class="space-y-2.5 mb-6 text-xs font-bold text-slate-600 dark:text-slate-300">
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> Pemisahan Warna &amp; Jenis Bahan</li>
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> Pengeringan Suhu Terkontrol</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Service 2 -->
                    <div class="bg-slate-900 dark:bg-slate-900 text-white p-8 rounded-3xl border border-slate-800 flex flex-col justify-between shadow-2xl relative overflow-hidden group hover:-translate-y-1 transition-all">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-[#FF6600]/20 rounded-full blur-2xl"></div>
                        <div>
                            <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center mb-6 text-white group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-3xl">dry_cleaning</span>
                            </div>
                            <h3 class="text-xl font-black font-display mb-3">Professional Dry Cleaning</h3>
                            <p class="text-xs font-medium text-slate-300 mb-6 leading-relaxed">Menjaga integritas kain sensitif seperti wol, sutra, jas, dan kebaya menggunakan pelarut hidrokarbon yang lembut bagi serat pakaian Anda.</p>
                            <ul class="space-y-2.5 mb-6 text-xs font-bold text-slate-200">
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> Ahli Penghilang Noda Membandel</li>
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> Hanger &amp; Cover Pelindung Debu</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Service 3 -->
                    <div class="bg-white dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:border-[#FF6600] transition-all hover:-translate-y-1 group">
                        <div>
                            <div class="w-14 h-14 bg-orange-500/10 rounded-2xl flex items-center justify-center mb-6 text-[#FF6600] group-hover:scale-110 transition-transform">
                                <span class="material-symbols-outlined text-3xl">roller_skating</span>
                            </div>
                            <h3 class="text-xl font-black font-display text-slate-900 dark:text-white mb-3">Footwear &amp; Bag Spa</h3>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">Perawatan khusus sepatu kets, kulit, sneakers branded, gaun pengantin, serta tas branded dengan teknik cuci tangan manual &amp; sterilisasi UV.</p>
                            <ul class="space-y-2.5 mb-6 text-xs font-bold text-slate-600 dark:text-slate-300">
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> Deep Cleaning Material Leather &amp; Suede</li>
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> Anti-Bacterial &amp; Deodorizing UV</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Royal Membership Tier Showcase Section -->
        <section class="py-24 bg-white dark:bg-slate-950 transition-colors" id="membership">
            <div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-12">
                <div class="text-center mb-16 max-w-2xl mx-auto">
                    <span class="text-xs font-black text-[#FF6600] uppercase tracking-widest block mb-2">Loyalty Rewards</span>
                    <h2 class="text-3xl sm:text-4xl font-black font-display text-slate-900 dark:text-white tracking-tight">Royal Loyalty Program</h2>
                    <p class="text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400 mt-2">Kumpulkan poin di setiap transaksi dan nikmati keistimewaan diskon otomatis, prioritas pengerjaan, dan antar-jemput gratis.</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Silver Tier -->
                    <div class="bg-slate-50 dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-2xs font-black text-slate-400 uppercase tracking-widest">Tier Perak</span>
                                <span class="material-symbols-outlined text-slate-400 text-3xl">military_tech</span>
                            </div>
                            <h3 class="text-2xl font-black font-display text-slate-900 dark:text-white mb-2">Silver Member</h3>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-6">Membership otomatis untuk seluruh pelanggan baru. Dapatkan poin di setiap transaksi.</p>
                            <ul class="space-y-3 mb-8 text-xs font-bold text-slate-600 dark:text-slate-300">
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> 1 Poin per kelipatan Rp 10.000</li>
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> Potongan Langsung via Poin</li>
                            </ul>
                        </div>
                        <div class="text-2xs font-extrabold text-slate-400 uppercase tracking-widest pt-4 border-t border-slate-200 dark:border-slate-800">Aktif Sejak Transaksi Pertama</div>
                    </div>
                    
                    <!-- Gold Tier -->
                    <div class="bg-slate-900 text-white p-8 rounded-3xl border border-slate-800 flex flex-col justify-between relative overflow-hidden shadow-2xl">
                        <div class="relative z-10">
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-2xs font-black text-[#FF6600] uppercase tracking-widest">Tier Emas</span>
                                <span class="material-symbols-outlined text-[#FF6600] text-3xl">military_tech</span>
                            </div>
                            <h3 class="text-2xl font-black font-display mb-2">Gold Member Privilege</h3>
                            <p class="text-xs font-medium text-slate-300 mb-6">Terbuka otomatis setelah akumulasi 1.000 poin. Keistimewaan harga &amp; kecepatan.</p>
                            <ul class="space-y-3 mb-8 text-xs font-bold text-slate-200">
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> Diskon Langsung 5% Semua Layanan</li>
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> Prioritas Pengerjaan Express</li>
                            </ul>
                        </div>
                        <div class="text-2xs font-extrabold text-[#FF6600] uppercase tracking-widest pt-4 border-t border-slate-800">Syarat: Poin &gt;= 1.000</div>
                    </div>
                    
                    <!-- Platinum Tier -->
                    <div class="bg-slate-50 dark:bg-slate-900 p-8 rounded-3xl border border-slate-200 dark:border-slate-800 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-6">
                                <span class="text-2xs font-black text-slate-400 uppercase tracking-widest">Tier Platinum</span>
                                <span class="material-symbols-outlined text-slate-400 text-3xl">military_tech</span>
                            </div>
                            <h3 class="text-2xl font-black font-display text-slate-900 dark:text-white mb-2">Platinum Luxury</h3>
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-6">Level teratas loyalty bagi pelanggan premium dengan perlakuan VIP tanpa batas.</p>
                            <ul class="space-y-3 mb-8 text-xs font-bold text-slate-600 dark:text-slate-300">
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> Diskon Langsung 10% Semua Layanan</li>
                                <li class="flex items-center gap-2"><span class="material-symbols-outlined text-[#FF6600] text-base">check_circle</span> Gratis Antar Jemput Unlimited</li>
                            </ul>
                        </div>
                        <div class="text-2xs font-extrabold text-slate-400 uppercase tracking-widest pt-4 border-t border-slate-200 dark:border-slate-800">Syarat: Poin &gt;= 5.000</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ Accordion Section -->
        <section class="py-20 bg-slate-50 dark:bg-slate-950 transition-colors" x-data="{ openFaq: null }">
            <div class="max-w-4xl mx-auto px-4 sm:px-8">
                <div class="text-center mb-12">
                    <span class="text-xs font-black text-[#FF6600] uppercase tracking-widest block mb-2">Pertanyaan Umum</span>
                    <h2 class="text-3xl sm:text-4xl font-black font-display text-slate-900 dark:text-white tracking-tight">Sering Ditanyakan (FAQ)</h2>
                </div>

                <div class="space-y-3">
                    <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 1 ? null : 1" class="w-full p-5 text-left font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex justify-between items-center bg-white dark:bg-slate-900">
                            <span>Berapa lama proses pengerjaan cucian di Istana Laundry?</span>
                            <span class="material-symbols-outlined text-[#FF6600]" x-text="openFaq === 1 ? 'remove' : 'add'"></span>
                        </button>
                        <div x-show="openFaq === 1" class="p-5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 leading-relaxed">
                            Layanan reguler kami membutuhkan waktu 48 jam. Kami juga menyediakan layanan Express (24 jam) dan Flash (6 jam) bagi kebutuhan darurat Anda.
                        </div>
                    </div>

                    <div class="border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
                        <button type="button" @click="openFaq = openFaq === 2 ? null : 2" class="w-full p-5 text-left font-bold text-xs sm:text-sm text-slate-900 dark:text-white flex justify-between items-center bg-white dark:bg-slate-900">
                            <span>Apakah ada garansi jika pakaian belum bersih atau kurang wangi?</span>
                            <span class="material-symbols-outlined text-[#FF6600]" x-text="openFaq === 2 ? 'remove' : 'add'"></span>
                        </button>
                        <div x-show="openFaq === 2" class="p-5 text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 leading-relaxed">
                            Ya! Kami memiliki Garansi Kepuasan 100%. Jika hasil cucian dirasa kurang bersih atau harum, kami akan mencuci ulang pakaian Anda secara gratis.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Multi-Step Interactive Online Order Modal Dialog Pop-Up -->
    <div x-show="orderModalOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4 sm:p-6"
         style="display: none;">
        
        <div class="bg-white dark:bg-slate-900 text-slate-900 dark:text-white rounded-3xl max-w-xl w-full border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden relative"
             @click.away="orderModalOpen = false">
            
            <!-- Modal Header -->
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/80 dark:bg-slate-950/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-[#FF6600] flex items-center justify-center text-white font-black text-sm shadow-md shadow-orange-500/30">
                        <span x-text="modalStep"></span>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Form Pemesanan Online</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400" x-text="modalStep === 1 ? 'Langkah 1: Pilih Cabang Target' : (modalStep === 2 ? 'Langkah 2: Konfigurasi Pakaian' : 'Langkah 3: Data Pemesan & Alamat')"></p>
                    </div>
                </div>

                <button @click="orderModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-lg">close</span>
                </button>
            </div>

            <!-- Modal Body (Multi-Step Form) -->
            <div class="p-6 space-y-6">
                <!-- STEP 1: SELECT BRANCH -->
                <div x-show="modalStep === 1" class="space-y-4">
                    <label class="block text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300">Pilih Outlet Cabang Terdekat</label>
                    <div class="space-y-2.5 max-h-[300px] overflow-y-auto pr-1">
                        <template x-for="(b, code) in branches" :key="code">
                            <div @click="selectedBranch = code" 
                                 :class="selectedBranch === code ? 'border-[#FF6600] bg-orange-50/80 dark:bg-orange-500/10' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950/60 hover:border-slate-300 dark:hover:border-slate-700'"
                                 class="p-3.5 rounded-2xl border cursor-pointer transition-all flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-orange-500/15 text-[#FF6600] flex items-center justify-center font-bold text-xs">
                                        <span class="material-symbols-outlined text-base">storefront</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-extrabold text-slate-900 dark:text-white" x-text="b.name"></span>
                                        <span class="block text-[10px] text-slate-500 dark:text-slate-400" x-text="b.address"></span>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined text-base" :class="selectedBranch === code ? 'text-[#FF6600]' : 'text-slate-400 dark:text-slate-600'">
                                    <span x-text="selectedBranch === code ? 'radio_button_checked' : 'radio_button_unchecked'"></span>
                                </span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- STEP 2: SERVICE CONFIGURATION -->
                <div x-show="modalStep === 2" class="space-y-4">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Layanan &amp; Jumlah Pakaian</label>
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <select x-model="serviceType" class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-[#FF6600]">
                                <option value="kiloan">Cuci Kiloan (Rp 10k/kg)</option>
                                <option value="dry_clean">Dry Clean Jas/Kebaya (Rp 35k/pcs)</option>
                                <option value="shoes_bag">Sepatu / Tas Spa (Rp 45k/pair)</option>
                                <option value="bed_cover">Bed Cover (Rp 30k/pcs)</option>
                                <option value="curtain">Gorden (Rp 15k/m²)</option>
                            </select>
                            <input type="number" min="1" max="100" x-model.number="qty" placeholder="Jumlah" class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 text-xs font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-[#FF6600]" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-2">Kecepatan Pengerjaan</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="speed = 'regular'" :class="speed === 'regular' ? 'bg-[#FF6600] text-white shadow-md shadow-orange-500/20' : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900'" class="p-2 rounded-xl text-2xs font-bold transition-all">Reguler (48h)</button>
                            <button type="button" @click="speed = 'express'" :class="speed === 'express' ? 'bg-[#FF6600] text-white shadow-md shadow-orange-500/20' : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900'" class="p-2 rounded-xl text-2xs font-bold transition-all">Express (24h)</button>
                            <button type="button" @click="speed = 'flash'" :class="speed === 'flash' ? 'bg-[#FF6600] text-white shadow-md shadow-orange-500/20' : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900'" class="p-2 rounded-xl text-2xs font-bold transition-all">Flash (6h)</button>
                        </div>
                    </div>

                    <div class="p-4 bg-orange-50/60 dark:bg-slate-950 rounded-2xl border border-orange-100 dark:border-slate-800 flex justify-between items-center">
                        <span class="text-xs text-slate-600 dark:text-slate-400 font-bold">Estimasi Total Biaya</span>
                        <span class="text-lg font-black text-[#FF6600]" x-text="'Rp ' + calculatedTotal.toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- STEP 3: CUSTOMER DETAILS & PICKUP ADDRESS -->
                <div x-show="modalStep === 3" class="space-y-4">
                    <div class="grid grid-cols-2 gap-2 mb-3">
                        <button type="button" @click="deliveryMethod = 'pickup'" :class="deliveryMethod === 'pickup' ? 'bg-[#FF6600] text-white shadow-md shadow-orange-500/20' : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900'" class="p-2.5 rounded-xl text-xs font-bold transition-all">Antar-Jemput Kurir</button>
                        <button type="button" @click="deliveryMethod = 'dropoff'" :class="deliveryMethod === 'dropoff' ? 'bg-[#FF6600] text-white shadow-md shadow-orange-500/20' : 'bg-slate-50 dark:bg-slate-950 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-900'" class="p-2.5 rounded-xl text-xs font-bold transition-all">Antar Sendiri ke Outlet</button>
                    </div>

                    <div>
                        <label class="block text-2xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nama Lengkap</label>
                        <input type="text" x-model="customerName" placeholder="Masukkan Nama Lengkap Anda..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-[#FF6600]" />
                    </div>

                    <div>
                        <label class="block text-2xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Nomor WhatsApp Active</label>
                        <input type="text" x-model="customerPhone" placeholder="Contoh: 081234567890..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-[#FF6600]" />
                    </div>

                    <div x-show="deliveryMethod === 'pickup'">
                        <label class="block text-2xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Alamat Penjemputan di Samarinda</label>
                        <textarea x-model="customerAddress" rows="2" placeholder="Jl. Pelita No. 12, RT 05, Samarinda..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-[#FF6600]"></textarea>
                    </div>

                    <div>
                        <label class="block text-2xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300 mb-1">Catatan Pakaian (Opsional)</label>
                        <input type="text" x-model="customerNotes" placeholder="Contoh: Jangan disetrika panas jas sutra..." class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-2.5 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-[#FF6600]" />
                    </div>
                </div>
            </div>

            <!-- Modal Footer Controls -->
            <div class="p-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/60 flex items-center justify-between">
                <button type="button" 
                        x-show="modalStep > 1" 
                        @click="modalStep--" 
                        class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-xl flex items-center gap-1 transition-colors">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Kembali</span>
                </button>
                
                <div class="ml-auto">
                    <button type="button" 
                            x-show="modalStep < 3" 
                            @click="modalStep++" 
                            class="px-5 py-2.5 bg-[#FF6600] hover:bg-orange-600 text-white font-black text-xs rounded-xl flex items-center gap-1.5 transition-colors shadow-md shadow-orange-500/20">
                        <span>Lanjut</span>
                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                    </button>

                    <button type="button" 
                            x-show="modalStep === 3" 
                            @click="sendWhatsAppOrder()" 
                            class="px-6 py-2.5 bg-[#25D366] hover:bg-[#1DA851] text-white font-black text-xs rounded-xl flex items-center gap-2 transition-colors shadow-lg shadow-emerald-600/20">
                        <span class="material-symbols-outlined text-base">chat</span>
                        <span>Kirim Pesanan via WhatsApp</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating WhatsApp Concierge & Order Action -->
    <div class="fixed bottom-6 right-6 z-40 flex flex-col gap-3">
        <button @click="openOrderModal()" 
                class="w-14 h-14 bg-[#FF6600] hover:bg-orange-600 text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-transform active:scale-95 glow-orange"
                title="Pesan Online Fast Order">
            <span class="material-symbols-outlined text-3xl">shopping_cart_checkout</span>
        </button>

        <a href="https://wa.me/628115550001?text=Halo%20Istana%20Laundry%2C%20saya%20butuh%20bantuan%20layanan" 
           target="_blank"
           class="w-14 h-14 bg-[#25D366] hover:bg-[#1DA851] text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-110 transition-transform active:scale-95"
           title="Hubungi Customer Care WhatsApp">
            <span class="material-symbols-outlined text-3xl">chat</span>
        </a>
    </div>

    <!-- Executive Footer -->
    <footer class="bg-slate-950 text-white border-t border-slate-900 py-16 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-8 lg:px-12 flex flex-col items-center justify-center space-y-8">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.webp') }}" alt="Istana Laundry" class="h-8 w-auto object-contain">
                <span class="font-black font-display text-xl tracking-tight">ISTANA LAUNDRY</span>
            </div>
            
            <div class="flex flex-wrap justify-center gap-6 text-xs font-extrabold uppercase tracking-wider text-slate-400">
                <a class="hover:text-[#FF6600] transition-colors" href="#tracking">Lacak Nota</a>
                <a class="hover:text-[#FF6600] transition-colors" href="#calculator">Kalkulator Tarif</a>
                <a class="hover:text-[#FF6600] transition-colors" href="#outlets">Peta 5 Outlet</a>
                <a class="hover:text-[#FF6600] transition-colors" href="#membership">Membership</a>
                <a class="hover:text-[#FF6600] transition-colors" href="{{ route('login') }}">Portal Staf</a>
            </div>
            
            <p class="text-xs text-slate-500 font-medium text-center leading-relaxed max-w-md">
                © {{ date('Y') }} Istana Premium Laundry Samarinda. Hak Cipta Dilindungi Undang-Undang. <br/>
                Standardizing Garment &amp; Textile Care Excellence in East Kalimantan.
            </p>
        </div>
    </footer>

    <!-- Leaflet.js Interactive Map with Google Maps Tiles Initialization Script -->
    <script>
        let map;
        let markers = {};
        let markersGroup;
        let googleRoadmapLayer;
        let googleSatelliteLayer;

        document.addEventListener('DOMContentLoaded', function() {
            // Google Maps Tile Layers
            googleRoadmapLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: '&copy; Google Maps | Istana Laundry Samarinda'
            });

            googleSatelliteLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                attribution: '&copy; Google Maps Satellite | Istana Laundry Samarinda'
            });

            // Initialize Leaflet Map with Google Maps Roadmap Layer
            map = L.map('samarindaMap', {
                layers: [googleRoadmapLayer]
            });

            // Custom High-Visibility SVG Pinpoint Marker
            const createCustomPin = (label) => {
                return L.divIcon({
                    className: 'custom-div-icon',
                    html: `
                        <div style="position:relative; width:34px; height:42px; display:flex; flex-direction:column; align-items:center;">
                            <div style="background:#FF6600; width:34px; height:34px; border-radius:50% 50% 50% 0; transform:rotate(-45deg); border:3px solid #ffffff; box-shadow:0 6px 15px rgba(0,0,0,0.5); display:flex; items-center; justify-content:center;" class="animate-pulse-orange">
                                <span style="transform:rotate(45deg); color:#ffffff; font-weight:900; font-size:12px; font-family:sans-serif;">${label}</span>
                            </div>
                        </div>
                    `,
                    iconSize: [34, 42],
                    iconAnchor: [17, 42],
                    popupAnchor: [0, -42]
                });
            };

            const branchLocations = [
                { code: 'WJK', label: '1', name: 'Pusat Wijaya Kusuma', lat: -0.48696231847856347, lng: 117.12927615036908, address: 'Jl. Wijaya Kusuma Blok V-C Gg. Rina, Air Hitam' },
                { code: 'SUT', label: '2', name: 'Cabang Dr. Sutomo', lat: -0.4798559089094088, lng: 117.1468433221731, address: 'Jl. Dr. Sutomo, Sidodadi' },
                { code: 'HID', label: '3', name: 'Cabang Pangeran Hidayatullah', lat: -0.5023171396817897, lng: 117.15582759477942, address: 'Jl. Pangeran Hidayatullah, Karang Mumus' },
                { code: 'LMG', label: '4', name: 'Cabang Lambung Mangkurat', lat: -0.4859848769525549, lng: 117.16400973393, address: 'Jl. Lambung Mangkurat, Sungai Pinang Dalam' },
                { code: 'GTS', label: '5', name: 'Cabang Grand Taman Sari', lat: -0.5609652945077885, lng: 117.11677338777984, address: 'Perumahan Grand Taman Sari, Harapan Baru' }
            ];

            const markerList = [];

            branchLocations.forEach(b => {
                let m = L.marker([b.lat, b.lng], { icon: createCustomPin(b.label) });
                let popupContent = `
                    <div style="font-family:'Plus Jakarta Sans',sans-serif; padding:8px; min-width:220px;">
                        <span style="color:#FF6600; font-size:10px; font-weight:800; text-transform:uppercase; display:block; margin-bottom:2px;">OFFICIAL GOOGLE MAPS PINPOINT</span>
                        <strong style="color:#ffffff; font-size:13px; display:block; margin-bottom:4px;">${b.name}</strong>
                        <p style="color:#94a3b8; font-size:11px; margin-bottom:10px; line-height:1.3;">${b.address}</p>
                        <button onclick="document.body._x_dataStack[0].openOrderModal('${b.code}')" 
                                style="width:100%; background:#FF6600; color:#fff; border:none; padding:8px; border-radius:10px; font-size:11px; font-weight:800; cursor:pointer; box-shadow:0 4px 12px rgba(255,102,0,0.4);">
                            Pesan di Cabang Ini
                        </button>
                    </div>
                `;
                m.bindPopup(popupContent);
                markers[b.code] = { marker: m, lat: b.lat, lng: b.lng };
                markerList.push(m);
            });

            // Feature Group for Auto Fit Bounds
            markersGroup = L.featureGroup(markerList).addTo(map);
            map.fitBounds(markersGroup.getBounds().pad(0.12));
        });

        function flyToBranch(code) {
            if (markers[code]) {
                map.flyTo([markers[code].lat, markers[code].lng], 17, { duration: 1.5 });
                setTimeout(() => {
                    markers[code].marker.openPopup();
                }, 1000);
            }
        }

        function toggleMapSatellite(isSatellite) {
            if (isSatellite) {
                map.removeLayer(googleRoadmapLayer);
                map.addLayer(googleSatelliteLayer);
            } else {
                map.removeLayer(googleSatelliteLayer);
                map.addLayer(googleRoadmapLayer);
            }
        }
    </script>
</body>
</html>
