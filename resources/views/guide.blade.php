<x-app-layout>
    <div class="flex flex-col gap-6 max-w-5xl mx-auto">
        <x-page-header title="Pusat Panduan & Training Staf (Interactive User Guide)" :breadcrumbs="['Bantuan' => '#', 'Panduan Training' => '/guide']" />

        <!-- Introduction Hero Card -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-orange-600 to-amber-500 p-6 md:p-8 text-white shadow-xl">
            <div class="relative z-10 space-y-3">
                <span class="px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-2xs font-extrabold uppercase tracking-widest text-white">Interactive Training Portal</span>
                <h1 class="font-black font-display text-2xl md:text-3xl text-white">Panduan Penggunaan Sistem ERP Istana Laundry</h1>
                <p class="text-xs md:text-sm text-orange-100 font-medium max-w-2xl">
                    Sistem didesain sangat intuitif (*friendly to use*). Pilih peranan (role) Anda di bawah untuk mempelajari alur kerja harian secara visual dan praktis.
                </p>
                <div class="pt-2 flex flex-wrap gap-3">
                    <button onclick="window.dispatchEvent(new CustomEvent('start-tour'))" class="px-5 py-2.5 bg-white text-orange-600 rounded-xl font-black text-xs hover:bg-orange-50 transition-all shadow-md cursor-pointer flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">play_circle</span>
                        Mulai Tour Pemandu Interaktif
                    </button>
                    <a href="https://wa.me/628115599199?text=Halo%20IT%20Support%2C%20saya%20butuh%20bantuan%20training" target="_blank" class="px-5 py-2.5 bg-black/20 hover:bg-black/30 backdrop-blur-md text-white rounded-xl font-bold text-xs transition-all flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">support_agent</span>
                        Hubungi Tim Helpdesk IT
                    </a>
                </div>
            </div>
            <span class="material-symbols-outlined absolute -right-6 -bottom-10 text-[180px] opacity-15 text-white pointer-events-none">school</span>
        </div>

        <!-- Role-Based Training Guide Accordion / Tabs -->
        <div x-data="{ activeTab: 'cashier' }" class="flex flex-col gap-6">
            
            <!-- Navigation Tabs -->
            <div class="flex overflow-x-auto gap-2 border-b border-slate-200 dark:border-slate-800 pb-2">
                <button @click="activeTab = 'cashier'" :class="activeTab === 'cashier' ? 'bg-primary text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                    <span class="material-symbols-outlined text-base">point_of_sale</span> 1. Kasir Outlet (POS)
                </button>
                <button @click="activeTab = 'workshop'" :class="activeTab === 'workshop' ? 'bg-primary text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                    <span class="material-symbols-outlined text-base">local_laundry_service</span> 2. Staf Workshop / Cuci
                </button>
                <button @click="activeTab = 'owner'" :class="activeTab === 'owner' ? 'bg-primary text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                    <span class="material-symbols-outlined text-base">monitoring</span> 3. Owner & Executive
                </button>
                <button @click="activeTab = 'finance'" :class="activeTab === 'finance' ? 'bg-primary text-white shadow-md' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'" class="px-4 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center gap-2 shrink-0 cursor-pointer">
                    <span class="material-symbols-outlined text-base">account_balance</span> 4. Tim Finance & HR
                </button>
            </div>

            <!-- Tab 1: Kasir POS -->
            <div x-show="activeTab === 'cashier'" class="space-y-4">
                <x-card title="Panduan Harian Kasir Outlet (POS)">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 py-2">
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-2">
                            <span class="w-8 h-8 rounded-xl bg-orange-100 dark:bg-orange-950/40 text-primary font-black text-xs flex items-center justify-center">01</span>
                            <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200">Cari / Daftarkan Pelanggan</h4>
                            <p class="text-2xs text-slate-500 leading-relaxed">
                                Masukkan nama atau nomor HP pelanggan di kolom pencarian. Jika pelanggan baru, klik tombol <strong class="text-primary">+ Person</strong> untuk mendaftarkan member global instan.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-2">
                            <span class="w-8 h-8 rounded-xl bg-orange-100 dark:bg-orange-950/40 text-primary font-black text-xs flex items-center justify-center">02</span>
                            <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200">Pilih Layanan & Promo</h4>
                            <p class="text-2xs text-slate-500 leading-relaxed">
                                Klik kartu layanan cuci (Kiloan/Satuan/Karpet). Ketikkan kode kupon promo atau pilih diskon aktif jika ada kampanye promo.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-2">
                            <span class="w-8 h-8 rounded-xl bg-orange-100 dark:bg-orange-950/40 text-primary font-black text-xs flex items-center justify-center">03</span>
                            <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200">Pembayaran & Struk Thermal</h4>
                            <p class="text-2xs text-slate-500 leading-relaxed">
                                Pilih metode pembayaran (Tunai/Transfer/Invoice). Tekan <strong class="text-primary">Proses Transaksi</strong> dan cetak struk nota 58mm/80mm atau kirim via WhatsApp.
                            </p>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Tab 2: Workshop & Produksi -->
            <div x-show="activeTab === 'workshop'" class="space-y-4" x-cloak>
                <x-card title="Panduan Operasional Workstation Workshop">
                    <div class="space-y-3 py-2">
                        <div class="flex items-start gap-4 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                            <span class="material-symbols-outlined text-amber-500 text-2xl shrink-0 mt-0.5">wash</span>
                            <div>
                                <h4 class="font-bold text-xs text-slate-800 dark:text-slate-200">Alur Status Produksi (Terima $\rightarrow$ Cuci $\rightarrow$ Kering $\rightarrow$ Setrika $\rightarrow$ Siap)</h4>
                                <p class="text-2xs text-slate-500 mt-1">Staf workshop memindahkan status cucian di menu <strong class="text-primary">Produksi (/production)</strong> saat setiap tahapan pengerjaan selesai.</p>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Tab 3: Owner & Analytics -->
            <div x-show="activeTab === 'owner'" class="space-y-4" x-cloak>
                <x-card title="Panduan Pemantauan Owner & Manajerial">
                    <div class="space-y-3 py-2">
                        <div class="flex items-start gap-4 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                            <span class="material-symbols-outlined text-primary text-2xl shrink-0 mt-0.5">analytics</span>
                            <div>
                                <h4 class="font-bold text-xs text-slate-800 dark:text-slate-200">Executive Dashboard & PowerBI Export</h4>
                                <p class="text-2xs text-slate-500 mt-1">Owner dapat beralih cabang via Scope Selector di topbar dan melihat komparasi omset, laba bersih, layanan laris/sepi, serta mengunduh laporan PDF PowerBI secara langsung.</p>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Tab 4: Tim Finance & HR -->
            <div x-show="activeTab === 'finance'" class="space-y-4" x-cloak>
                <x-card title="Panduan Pengelolaan Keuangan & Payroll">
                    <div class="space-y-3 py-2">
                        <div class="flex items-start gap-4 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                            <span class="material-symbols-outlined text-emerald-500 text-2xl shrink-0 mt-0.5">payments</span>
                            <div>
                                <h4 class="font-bold text-xs text-slate-800 dark:text-slate-200">Penggajian Karyawan & Slip Gaji Resmi</h4>
                                <p class="text-2xs text-slate-500 mt-1">Hitung gaji secara otomatis berdasarkan kehadiran & bonus kiloan. Cetak Slip Gaji resmi dengan format tabel ganda (Pendapatan vs Potongan) yang divalidasi digital.</p>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

        </div>
    </div>
</x-app-layout>
