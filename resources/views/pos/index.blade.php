<x-app-layout>
    {{-- Pass customer data, active shift, & draft orders via a script tag --}}
    <script>
        window.__posCustomers = @json($customers);
        window.__posActiveShift = @json($activeShift);
        window.__posDraftOrders = @json($draftOrders);
    </script>
    <div x-data="posApp()" class="min-h-[calc(100vh-100px)] flex flex-col gap-6">
        
        <x-page-header title="Point of Sale (POS)" :breadcrumbs="['POS' => '/pos']">
            <x-slot:actions>
                <div class="flex items-center gap-3 flex-wrap">
                    <!-- Shift Status & Quick Action Ribbon -->
                    @if($activeShift)
                        <div class="flex items-center gap-2 bg-emerald-50 dark:bg-slate-800/80 border border-emerald-200 dark:border-slate-700 px-3.5 py-1.5 rounded-xl shadow-xs">
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                            </span>
                            <div class="text-xs">
                                <span class="text-slate-400 block text-[9px] font-extrabold uppercase leading-none">Shift Active #{{ $activeShift->id }}</span>
                                <span class="font-extrabold text-slate-800 dark:text-slate-100">Modal: Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <button type="button" @click="showPettyCashModal = true" class="btn-touch px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:text-primary text-2xs font-extrabold flex items-center gap-1.5 transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-sm text-rose-500">payments</span>
                            Kas Kecil
                        </button>

                        <button type="button" @click="showDraftModal = true" class="btn-touch px-3 py-1.5 rounded-xl bg-orange-50 dark:bg-slate-800 border border-orange-200 dark:border-slate-700 text-primary dark:text-orange-400 text-2xs font-extrabold flex items-center gap-1.5 transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-sm">pending_actions</span>
                            Hold Order
                            <span class="px-1.5 py-0.2 rounded-full bg-primary text-white text-[10px]" x-text="draftOrders.length">0</span>
                        </button>

                        <button type="button" @click="showCloseShiftModal = true" class="btn-touch px-3 py-1.5 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-rose-600 dark:text-rose-400 text-2xs font-extrabold flex items-center gap-1.5 transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-sm">lock_clock</span>
                            Tutup Shift
                        </button>
                    @else
                        <button type="button" @click="showOpenShiftModal = true" class="btn-touch px-4 py-2 rounded-xl bg-gradient-to-r from-primary to-orange-600 text-white font-extrabold text-xs flex items-center gap-1.5 shadow-md shadow-primary/20 transition-all cursor-pointer">
                            <span class="material-symbols-outlined text-base">key</span>
                            Buka Shift Kasir Sekarang
                        </button>
                    @endif

                    @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                        <div class="flex items-center gap-2 bg-orange-50 dark:bg-slate-800 border border-orange-200 dark:border-slate-700 px-3 py-1.5 rounded-xl">
                            <span class="material-symbols-outlined text-primary text-base">storefront</span>
                            <div class="text-xs">
                                <span class="text-slate-400 block text-[10px] font-bold uppercase leading-none">Cabang Aktif</span>
                                <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ $branch?->name ?? 'Semua Cabang' }}</span>
                            </div>
                            <button type="button" @click="showBranchScopeModal = true" class="ml-2 btn-touch text-2xs font-bold text-primary hover:underline cursor-pointer">
                                Ganti
                            </button>
                        </div>
                    @endif
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- Alert Flash Messages -->
        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-2" />
        @endif
        @if ($errors->any())
            <x-alert type="danger" message="Terjadi kesalahan validasi. Periksa inputan Anda." class="mb-2" />
        @endif

        <!-- Banner Prompt Buka Shift Kasir -->
        @if(!$activeShift)
            <div class="bg-gradient-to-r from-amber-500/15 via-orange-500/10 to-transparent border border-amber-200 dark:border-amber-900/40 p-4 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold shrink-0">
                        <span class="material-symbols-outlined text-xl">lock</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Shift Kasir Belum Aktif</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Harap buka shift dan masukkan Modal Kas Awal untuk mulai melakukan transaksi POS.</p>
                    </div>
                </div>
                <button type="button" @click="showOpenShiftModal = true" class="btn-touch px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl font-extrabold text-xs shrink-0 cursor-pointer shadow-md shadow-primary/20">
                    Buka Shift Kasir
                </button>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-1 items-start">
            
            <!-- Left Side: Customer, Promo, Presets & Services (8 cols) -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                
                {{-- ===== ORDER CHANNEL / JENIS ORDER CARD ===== --}}
                <div class="rounded-2xl border-2 transition-all duration-200"
                     :class="orderType === 'pickup_delivery'
                         ? 'border-blue-400/60 dark:border-blue-600/50 bg-gradient-to-r from-blue-50/80 via-indigo-50/40 to-transparent dark:from-blue-950/30 dark:via-indigo-950/20'
                         : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900/60'">
                    <div class="p-4 flex flex-col gap-4">
                        {{-- Header --}}
                        <div class="flex items-center justify-between gap-4 flex-wrap">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold shrink-0 transition-colors"
                                     :class="orderType === 'pickup_delivery' ? 'bg-blue-500 text-white' : 'bg-primary text-white'">
                                    <span class="material-symbols-outlined text-xl"
                                          x-text="orderType === 'pickup_delivery' ? 'local_shipping' : 'storefront'"></span>
                                </div>
                                <div>
                                    <span class="block text-2xs font-black text-slate-400 uppercase tracking-wider">Jenis / Channel Order</span>
                                    <span class="block text-sm font-extrabold text-slate-900 dark:text-white"
                                          x-text="orderType === 'pickup_delivery' ? 'Online — Pickup & Delivery' : 'Langsung ke Outlet / Gerai'"></span>
                                </div>
                            </div>

                            {{-- Toggle Buttons --}}
                            <div class="flex bg-slate-100 dark:bg-slate-800 p-1 rounded-xl gap-1 shrink-0">
                                <button type="button" @click="orderType = 'outlet'"
                                        :class="orderType === 'outlet'
                                            ? 'bg-white dark:bg-slate-900 text-primary font-extrabold shadow-sm'
                                            : 'text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700'"
                                        class="px-3 py-1.5 rounded-lg text-2xs flex items-center gap-1.5 transition-all cursor-pointer">
                                    <span class="material-symbols-outlined text-sm">storefront</span>
                                    Langsung Outlet
                                </button>
                                <button type="button" @click="orderType = 'pickup_delivery'"
                                        :class="orderType === 'pickup_delivery'
                                            ? 'bg-blue-500 text-white font-extrabold shadow-sm'
                                            : 'text-slate-500 dark:text-slate-400 font-medium hover:text-slate-700'"
                                        class="px-3 py-1.5 rounded-lg text-2xs flex items-center gap-1.5 transition-all cursor-pointer">
                                    <span class="material-symbols-outlined text-sm">local_shipping</span>
                                    Pickup & Delivery
                                </button>
                            </div>
                        </div>

                        {{-- Walk-In Name (always shown for walk-in customers) --}}
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider mb-1 block">
                                Nama Pelanggan Walk-In (opsional, jika tidak pilih member)
                            </label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">person</span>
                                <input type="text" x-model="walkInName" placeholder="Cth: Budi Santoso (isi jika tanpa akun member)..."
                                       class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:border-primary outline-none transition-all">
                            </div>
                        </div>

                        {{-- Pickup & Delivery Fields (conditionally shown) --}}
                        <div x-show="orderType === 'pickup_delivery'" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-blue-200/60 dark:border-blue-800/40">
                            {{-- Delivery Address --}}
                            <div class="sm:col-span-2">
                                <label class="text-2xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-1 block flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">location_on</span>
                                    Alamat Penjemputan / Pengantaran *
                                </label>
                                <textarea x-model="deliveryAddress" rows="2"
                                          placeholder="Masukkan alamat lengkap untuk penjemputan atau pengantaran laundry..."
                                          class="w-full px-3 py-2.5 rounded-xl border border-blue-200 dark:border-blue-800/60 bg-blue-50/50 dark:bg-blue-950/20 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:border-blue-400 outline-none transition-all resize-none"></textarea>
                            </div>
                            {{-- Delivery Phone --}}
                            <div>
                                <label class="text-2xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-1 block flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">phone</span>
                                    No. HP Koordinasi *
                                </label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-blue-400 text-base pointer-events-none">call</span>
                                    <input type="tel" x-model="deliveryPhone"
                                           placeholder="08xxxxxxxxxx"
                                           class="w-full h-10 pl-9 pr-3 rounded-xl border border-blue-200 dark:border-blue-800/60 bg-blue-50/50 dark:bg-blue-950/20 text-slate-800 dark:text-slate-200 text-xs font-bold focus:border-blue-400 outline-none transition-all">
                                </div>
                            </div>
                            {{-- Pickup Schedule --}}
                            <div>
                                <label class="text-2xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-1 block flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">schedule</span>
                                    Jadwal Penjemputan
                                </label>
                                <input type="datetime-local" x-model="pickupScheduledAt"
                                       class="w-full h-10 px-3 rounded-xl border border-blue-200 dark:border-blue-800/60 bg-blue-50/50 dark:bg-blue-950/20 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:border-blue-400 outline-none transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer & Promo Selectors -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Customer Selection -->
                    <x-card title="Pilih Pelanggan" :compact="true" class="!overflow-visible z-20">
                        <div class="flex flex-col gap-3">
                            <!-- State 1: Customer Selected Card & Loyalty Points Redeemer -->
                            <div x-show="customerId" class="flex flex-col gap-2.5" x-cloak>
                                <div class="flex items-center justify-between gap-3 p-3.5 rounded-2xl bg-gradient-to-r from-orange-50 to-amber-50 dark:from-slate-800/80 dark:to-slate-850 border border-orange-200/80 dark:border-slate-700">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-orange-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-md shadow-primary/20">
                                        <span x-text="selectedCustomer ? selectedCustomer.name.substring(0, 2).toUpperCase() : 'CU'"></span>
                                    </div>
                                    
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h4 class="text-sm font-extrabold text-slate-900 dark:text-white truncate" x-text="selectedCustomer ? selectedCustomer.name : ''"></h4>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-orange-500 text-white shadow-xs" x-text="customerTier">Bronze</span>
                                        </div>
                                        <div class="flex items-center gap-3 text-2xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
                                            <span class="flex items-center gap-1" x-show="selectedCustomer && selectedCustomer.phone"><span class="material-symbols-outlined text-xs">call</span> <span x-text="selectedCustomer ? selectedCustomer.phone : ''"></span></span>
                                            <span class="flex items-center gap-1 text-orange-600 dark:text-orange-400 font-extrabold"><span class="material-symbols-outlined text-xs">stars</span> <span x-text="customerPoints"></span> Poin Loyalty</span>
                                        </div>
                                    </div>

                                    <button type="button" @click="clearCustomer()" title="Ganti Pelanggan"
                                            class="btn-touch shrink-0 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-rose-500 text-2xs font-bold flex items-center gap-1 transition-all cursor-pointer shadow-xs">
                                        <span class="material-symbols-outlined text-sm">swap_horiz</span>
                                        Ganti
                                    </button>
                                </div>

                                <!-- Loyalty Points Redemption Action Box -->
                                <div class="p-3 rounded-2xl bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-transparent border border-amber-200/80 dark:border-slate-800 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-amber-500 text-white flex items-center justify-center font-bold shrink-0">
                                            <span class="material-symbols-outlined text-base">redeem</span>
                                        </div>
                                        <div>
                                            <span class="block text-2xs font-black text-slate-900 dark:text-white">Tukarkan Poin Loyalty</span>
                                            <span class="block text-[10px] text-slate-500 dark:text-slate-400" x-text="'1 Poin = Rp ' + formatNumber(pointExchangeRate) + ' Diskon'"></span>
                                        </div>
                                    </div>

                                    <button type="button" @click="toggleRedeemPoints()"
                                            :class="pointsUsed > 0 ? 'bg-emerald-600 text-white' : 'bg-amber-500 hover:bg-amber-600 text-white'"
                                            class="px-3 py-1.5 rounded-xl font-extrabold text-2xs shadow-xs flex items-center gap-1 transition-all cursor-pointer">
                                        <span class="material-symbols-outlined text-xs" x-text="pointsUsed > 0 ? 'check_circle' : 'stars'"></span>
                                        <span x-text="pointsUsed > 0 ? 'Poin Terpasang (' + pointsUsed + ' Pts)' : 'Gunakan Poin'"></span>
                                    </button>
                                </div>
                            </div>

                            <!-- State 2: Customer Combobox Search (Phone-First Priority) -->
                            <div x-show="!customerId" class="flex flex-col gap-2">
                                <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Cari No. HP Member (Utama) / Nama</label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1" @click.outside="customerOpen = false">
                                        <div class="relative flex items-center">
                                            <span class="material-symbols-outlined absolute left-3 text-slate-400 text-lg pointer-events-none">phone_search</span>
                                            <input type="text" x-model="customerSearch" autocomplete="off"
                                                   @focus="customerOpen = true" @click="customerOpen = true" @input="customerOpen = true"
                                                   placeholder="Ketik No. HP (Utama) atau nama member..."
                                                   class="w-full h-11 pl-9 pr-8 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all shadow-xs">

                                            <button type="button" x-show="customerSearch" x-cloak @click="customerSearch = ''; customerOpen = true"
                                                    class="absolute right-2.5 text-slate-400 hover:text-slate-600 cursor-pointer">
                                                <span class="material-symbols-outlined text-base">close</span>
                                            </button>
                                        </div>

                                        <!-- Dropdown List -->
                                        <div x-show="customerOpen" x-transition.opacity.duration.150ms x-cloak
                                             class="absolute z-50 mt-2 w-full max-h-64 overflow-y-auto bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl divide-y divide-slate-100 dark:divide-slate-800/60">
                                            
                                            <div class="px-3.5 py-2 bg-slate-50/80 dark:bg-slate-800/50 flex items-center justify-between sticky top-0 backdrop-blur-sm z-10">
                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Pelanggan Terdaftar (No. HP Utamakan)</span>
                                                <span class="text-[10px] font-bold text-primary" x-text="filteredCustomers().length + ' Pelanggan'"></span>
                                            </div>

                                            <template x-for="c in filteredCustomers()" :key="c.id">
                                                <button type="button" @click="selectCustomer(c); customerOpen = false"
                                                        class="w-full text-left px-3.5 py-2.5 hover:bg-primary/5 dark:hover:bg-slate-800/80 flex items-center justify-between gap-3 group transition-colors cursor-pointer">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-slate-800 text-primary flex items-center justify-center font-bold text-xs shrink-0 group-hover:bg-primary group-hover:text-white transition-colors">
                                                            <span x-text="c.name.substring(0, 2).toUpperCase()"></span>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <div class="flex items-center gap-1">
                                                                <span class="material-symbols-outlined text-[13px] text-orange-500 font-bold">call</span>
                                                                <span class="text-xs font-black font-mono text-orange-600 dark:text-orange-400 group-hover:text-primary transition-colors" x-text="c.phone || 'Tanpa No. HP'"></span>
                                                            </div>
                                                            <h5 class="text-2xs font-bold text-slate-700 dark:text-slate-200 truncate mt-0.5" x-text="c.name"></h5>
                                                        </div>
                                                    </div>
                                                    <div class="text-right shrink-0">
                                                        <span class="inline-block text-[10px] font-extrabold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300" x-text="c.loyalty_tier || 'Bronze'"></span>
                                                        <span class="block text-[10px] font-bold text-primary mt-0.5" x-text="c.loyalty_points + ' Poin'"></span>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <button type="button" @click="openAddCustomerModal()" title="Tambah Pelanggan Baru"
                                            class="btn-touch shrink-0 w-11 h-11 rounded-xl bg-primary/10 hover:bg-primary/20 text-primary flex items-center justify-center cursor-pointer transition-all shadow-xs">
                                        <span class="material-symbols-outlined text-xl">person_add</span>
                                    </button>
                                </div>
                            </div>

                        </div>
                    </x-card>

                    <!-- Promotion Selection -->
                    <x-card title="Gunakan Promo / Kupon" :compact="true">
                        <div class="flex flex-col gap-3">
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Kupon / Promo Aktif</label>
                            
                            <select id="promo_id" x-model="promoId" @change="updatePromoData"
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:border-primary outline-none transition-all cursor-pointer">
                                <option value="">-- Pilih Promo Terdaftar --</option>
                                @foreach ($promotions as $promo)
                                    <option value="{{ $promo->id }}" 
                                            data-code="{{ $promo->code }}"
                                            data-type="{{ $promo->type }}" 
                                            data-value="{{ $promo->value }}"
                                            data-min="{{ $promo->min_transaction }}">
                                        {{ $promo->name }} ({{ $promo->code }}) - Min: Rp {{ number_format($promo->min_transaction, 0, ',', '.') }}
                                    </option>
                                @endforeach
                            </select>

                            <div class="flex gap-2">
                                <input type="text" x-model="manualCouponCode" @keydown.enter.prevent="applyManualCoupon()"
                                       placeholder="Masukkan Kode Kupon..."
                                       class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs font-semibold uppercase focus:border-primary outline-none transition-all">
                                <button type="button" @click="applyManualCoupon()"
                                        class="btn-touch shrink-0 px-4 h-10 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-bold transition-all cursor-pointer">
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </x-card>
                </div>

                <!-- Quick-Tap Layanan Favorit (Presets) -->
                @if($popularServices->count() > 0)
                    <div class="bg-gradient-to-r from-orange-500/10 via-amber-500/5 to-transparent p-4 rounded-2xl border border-orange-200/70 dark:border-slate-800 flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-2xs font-extrabold text-primary uppercase tracking-wider flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">bolt</span>
                                Quick-Tap Layanan Favorit
                            </span>
                            <span class="text-[10px] text-slate-400 font-bold">1-Click Tambah Keranjang</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2.5">
                            @foreach($popularServices as $pop)
                                <button type="button" @click="addToCart({{ $pop->id }}, '{{ addslashes($pop->name) }}', {{ $pop->price }}, '{{ $pop->unit }}')"
                                        class="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-primary text-left transition-all hover:scale-[1.02] shadow-2xs group cursor-pointer">
                                    <span class="block text-xs font-extrabold text-slate-800 dark:text-slate-100 group-hover:text-primary truncate">{{ $pop->name }}</span>
                                    <span class="block text-[10px] font-bold text-primary mt-0.5">Rp {{ number_format($pop->price, 0, ',', '.') }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Services Grid & Filters -->
                <x-card title="Pilih Layanan Cuci">
                    <!-- Category Scroll Pills & Search Bar -->
                    <div class="flex flex-col sm:flex-row gap-3 mb-4 items-stretch sm:items-center justify-between">
                        <div class="scroll-pills flex-1 max-w-full">
                            <button type="button" @click="activeCategory = 'all'"
                                    :class="activeCategory === 'all' ? 'bg-primary text-white font-extrabold shadow-md shadow-primary/25 scale-[1.02]' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="pill-item">
                                <span class="material-symbols-outlined text-base">apps</span>
                                Semua
                            </button>
                            @foreach($categories as $type)
                                <button type="button" @click="activeCategory = '{{ $type }}'"
                                        :class="activeCategory === '{{ $type }}' ? 'bg-primary text-white font-extrabold shadow-md shadow-primary/25 scale-[1.02]' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                        class="pill-item">
                                    <span class="material-symbols-outlined text-base">
                                        {{ strtolower($type) === 'kiloan' ? 'weight' : (strtolower($type) === 'satuan' ? 'dry_cleaning' : (strtolower($type) === 'express' ? 'bolt' : 'local_laundry_service')) }}
                                    </span>
                                    {{ ucfirst($type) }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Live Search -->
                        <div class="relative w-full sm:w-60 shrink-0">
                            <span class="material-symbols-outlined text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-base">search</span>
                            <input type="text" x-model="serviceSearch" placeholder="Cari layanan..."
                                   class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:border-primary outline-none transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach ($services as $service)
                            <div x-show="(activeCategory === 'all' || activeCategory === '{{ $service->type }}') && matchesServiceSearch('{{ addslashes($service->name) }}', '{{ addslashes($service->type) }}')"
                                 class="border border-slate-200/80 dark:border-slate-800/80 rounded-2xl p-4 hover:border-primary/60 dark:hover:border-orange-500/60 transition-all flex flex-col justify-between bg-white dark:bg-slate-900 shadow-xs hover:shadow-md relative group select-none">
                                <div>
                                    <div class="flex justify-between items-start mb-2 gap-2">
                                        <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-100 group-hover:text-primary transition-colors">
                                            {{ $service->name }}
                                        </h4>
                                        <x-badge type="primary">{{ $service->type }}</x-badge>
                                    </div>
                                    <p class="text-2xs text-slate-400 dark:text-slate-500 mb-3 line-clamp-2">
                                        {{ $service->description ?? 'Layanan laundry profesional higienis.' }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between mt-2 pt-2.5 border-t border-slate-100 dark:border-slate-800/60">
                                    <div>
                                        <span class="block text-[9px] text-slate-400 font-extrabold uppercase tracking-wider">Per {{ $service->unit }}</span>
                                        <span class="text-sm font-black text-primary">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                    </div>

                                    <template x-if="getItemQuantity({{ $service->id }}) > 0">
                                        <div class="flex items-center gap-1 bg-orange-50 dark:bg-slate-800/80 p-1 rounded-xl border border-orange-200/60 dark:border-slate-700">
                                            <button type="button" @click="decrementServiceQuantity({{ $service->id }})" class="w-8 h-8 rounded-lg bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 font-extrabold text-sm shadow-xs flex items-center justify-center cursor-pointer active:scale-90">
                                                -
                                            </button>
                                            <span class="w-7 text-center text-xs font-black text-primary" x-text="getItemQuantity({{ $service->id }})"></span>
                                            <button type="button" @click="addToCart({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->price }}, '{{ $service->unit }}')" class="w-8 h-8 rounded-lg bg-primary text-white font-extrabold text-sm shadow-xs flex items-center justify-center cursor-pointer active:scale-90">
                                                +
                                            </button>
                                        </div>
                                    </template>

                                    <template x-if="getItemQuantity({{ $service->id }}) === 0">
                                        <button type="button" @click="addToCart({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->price }}, '{{ $service->unit }}')"
                                                class="btn-touch h-9 px-3 rounded-xl bg-orange-50 hover:bg-primary hover:text-white dark:bg-slate-800 dark:text-orange-400 dark:hover:bg-primary text-primary font-extrabold text-xs flex items-center justify-center gap-1.5 cursor-pointer transition-all active:scale-95 shadow-xs">
                                            <span class="material-symbols-outlined text-base">add_shopping_cart</span>
                                            Tambah
                                        </button>
                                    </template>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>

            </div>

            <!-- Right Side: Cart Panel (4 cols) -->
            <div class="lg:col-span-4 block sticky top-20">
                <x-card title="Keranjang Belanja">
                    
                    <form id="pos-form" action="{{ route('pos.store') }}" method="POST" class="flex flex-col gap-4" @submit.prevent="confirmCheckout()">
                        @csrf
                        
                        <input type="hidden" name="customer_id" x-model="customerId">
                        <input type="hidden" name="promo_id" x-model="promoId">
                        <input type="hidden" name="points_used" x-model="pointsUsed">
                        <input type="hidden" name="discount_amount" x-model="discount">
                        <input type="hidden" name="draft_id" x-model="currentDraftId">
                        {{-- Order Channel Hidden Inputs --}}
                        <input type="hidden" name="order_type" x-model="orderType">
                        <input type="hidden" name="customer_name_walkin" x-model="walkInName">
                        <input type="hidden" name="delivery_address" x-model="deliveryAddress">
                        <input type="hidden" name="delivery_phone" x-model="deliveryPhone">
                        <input type="hidden" name="pickup_scheduled_at" x-model="pickupScheduledAt">

                        <!-- Hold Order Quick Action Button inside Cart -->
                        <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-2xs font-extrabold text-slate-400 uppercase tracking-wider">Items (<span x-text="cart.length"></span>)</span>
                            <button type="button" x-show="cart.length > 0" @click="openHoldOrderModal()" class="text-2xs font-bold text-primary hover:underline flex items-center gap-1 cursor-pointer" x-cloak>
                                <span class="material-symbols-outlined text-sm">bookmark_add</span>
                                Simpan Pending (Hold)
                            </button>
                        </div>

                        <!-- Cart Item List -->
                        <div class="overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 max-h-[280px] pr-1">
                            <template x-if="cart.length === 0">
                                <div class="py-10 text-center">
                                    <span class="material-symbols-outlined text-slate-300 dark:text-slate-700 text-4xl mb-2">shopping_basket</span>
                                    <p class="text-xs text-slate-400 dark:text-slate-500">Keranjang kosong. Klik layanan untuk menambahkan.</p>
                                </div>
                            </template>

                            <template x-for="(item, index) in cart" :key="index">
                                <div class="py-3 flex items-start justify-between gap-3">
                                    <div class="flex-1 min-w-0">
                                        <span class="font-bold text-xs text-slate-800 dark:text-slate-200 block truncate" x-text="item.name"></span>
                                        <span class="text-2xs text-slate-400" x-text="'Rp ' + formatNumber(item.price) + ' / ' + item.unit"></span>
                                        <input type="hidden" :name="'items[' + index + '][service_id]'" :value="item.service_id">
                                        
                                        <input type="text" :name="'items[' + index + '][notes]'" x-model="item.notes" placeholder="Catatan..."
                                               class="mt-1 w-full h-7 px-2 rounded-lg border border-slate-150 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900 text-2xs text-slate-600 dark:text-slate-400 focus:border-primary outline-none">
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="0.01" step="0.01" @input="calculateTotals()"
                                               class="w-14 h-7 text-center rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-950 text-xs text-slate-800 dark:text-slate-200 font-bold focus:border-primary outline-none">
                                        <button type="button" @click="removeFromCart(index)" class="p-1 text-rose-500 hover:bg-rose-50 rounded-lg cursor-pointer">
                                            <span class="material-symbols-outlined text-base">delete</span>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Summary & Calculation -->
                        <div class="border-t border-slate-100 dark:border-slate-800 pt-3 space-y-2">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400">Subtotal</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200" x-text="'Rp ' + formatNumber(subtotal)">Rp 0</span>
                            </div>

                            <div class="flex justify-between text-xs text-emerald-600" x-show="discount > 0" x-cloak>
                                <span>Promo</span>
                                <span class="font-bold" x-text="'- Rp ' + formatNumber(discount)">- Rp 0</span>
                            </div>

                            <div class="flex justify-between text-xs text-orange-600 font-bold" x-show="pointsDiscount > 0" x-cloak>
                                <span>Diskon Poin (<span x-text="pointsUsed"></span> Pts)</span>
                                <span x-text="'- Rp ' + formatNumber(pointsDiscount)">- Rp 0</span>
                            </div>

                            <!-- Total -->
                            <div class="flex justify-between text-base font-black pt-2 border-t border-slate-100 dark:border-slate-800">
                                <span class="text-slate-800 dark:text-slate-200">Total Tagihan</span>
                                <span class="text-primary" x-text="'Rp ' + formatNumber(total)">Rp 0</span>
                            </div>

                            <!-- Multi-Payment Mode Selectors -->
                            <div class="flex flex-col gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                                <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Tipe & Metode Pembayaran</label>
                                
                                <div class="grid grid-cols-3 gap-1.5 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                                    <button type="button" @click="paymentType = 'full'; paymentMethod = 'cash'; calculateTotals()"
                                            :class="paymentType === 'full' ? 'bg-white dark:bg-slate-900 text-primary font-extrabold shadow-xs' : 'text-slate-600 dark:text-slate-400 font-medium'"
                                            class="py-1.5 text-2xs rounded-lg transition-all">Lunas</button>
                                    <button type="button" @click="paymentType = 'dp'; paymentMethod = 'dp'; calculateTotals()"
                                            :class="paymentType === 'dp' ? 'bg-white dark:bg-slate-900 text-primary font-extrabold shadow-xs' : 'text-slate-600 dark:text-slate-400 font-medium'"
                                            class="py-1.5 text-2xs rounded-lg transition-all">DP (Uang Muka)</button>
                                    <button type="button" @click="paymentType = 'split'; paymentMethod = 'split'; calculateTotals()"
                                            :class="paymentType === 'split' ? 'bg-white dark:bg-slate-900 text-primary font-extrabold shadow-xs' : 'text-slate-600 dark:text-slate-400 font-medium'"
                                            class="py-1.5 text-2xs rounded-lg transition-all">Split Pay</button>
                                </div>

                                <!-- Full Payment Method Picker -->
                                <template x-if="paymentType === 'full'">
                                    <div class="grid grid-cols-2 gap-2 mt-1">
                                        <select name="payment_method" x-model="paymentMethod" @change="calculateTotals()"
                                                class="w-full h-9 px-2 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-slate-200 outline-none focus:border-primary">
                                            <option value="cash">Tunai (Cash)</option>
                                            <option value="qris">QRIS / E-Wallet</option>
                                            <option value="transfer">Transfer Bank</option>
                                            <option value="debit">Debit / EDC</option>
                                            <option value="invoice">Invoice (Piutang)</option>
                                        </select>
                                        <input type="number" name="paid_amount" x-model.number="paidAmount" @input="calculateTotals()" placeholder="Nominal Bayar"
                                               class="w-full h-9 px-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 outline-none focus:border-primary">
                                    </div>
                                </template>

                                <!-- DP (Down Payment) Fields -->
                                <template x-if="paymentType === 'dp'">
                                    <div class="flex flex-col gap-2 mt-1 bg-orange-50/50 dark:bg-slate-800/40 p-2.5 rounded-xl border border-orange-100 dark:border-slate-700">
                                        <div class="flex justify-between text-2xs font-extrabold text-primary">
                                            <span>Nominal Uang Muka (DP)</span>
                                            <span x-text="'Sisa Tagihan: Rp ' + formatNumber(Math.max(0, total - paidAmount))"></span>
                                        </div>
                                        <input type="number" name="paid_amount" x-model.number="paidAmount" @input="calculateTotals()" placeholder="Masukkan Nominal DP..."
                                               class="w-full h-9 px-2.5 rounded-lg border border-orange-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-xs font-bold text-primary outline-none">
                                        <input type="hidden" name="payment_method" value="dp">
                                    </div>
                                </template>

                                <!-- Split Payment Form -->
                                <template x-if="paymentType === 'split'">
                                    <div class="flex flex-col gap-2 mt-1 bg-slate-50 dark:bg-slate-800/40 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700">
                                        <input type="hidden" name="payment_method" value="split">
                                        <div class="flex justify-between text-2xs font-extrabold text-slate-700 dark:text-slate-200">
                                            <span>Rincian Split Payment</span>
                                            <span x-text="'Total Split: Rp ' + formatNumber(getSplitTotal())"></span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <select x-model="split1.method" class="h-8 px-2 text-2xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                                <option value="cash">Tunai</option>
                                                <option value="qris">QRIS</option>
                                                <option value="transfer">Transfer</option>
                                                <option value="debit">Debit</option>
                                            </select>
                                            <input type="number" x-model.number="split1.amount" @input="calculateTotals()" placeholder="Nominal 1" class="h-8 px-2 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                            <input type="hidden" name="split_payments[0][method]" :value="split1.method">
                                            <input type="hidden" name="split_payments[0][amount]" :value="split1.amount">
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <select x-model="split2.method" class="h-8 px-2 text-2xs rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                                <option value="qris">QRIS</option>
                                                <option value="transfer">Transfer</option>
                                                <option value="cash">Tunai</option>
                                                <option value="debit">Debit</option>
                                            </select>
                                            <input type="number" x-model.number="split2.amount" @input="calculateTotals()" placeholder="Nominal 2" class="h-8 px-2 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                                            <input type="hidden" name="split_payments[1][method]" :value="split2.method">
                                            <input type="hidden" name="split_payments[1][amount]" :value="split2.amount">
                                        </div>
                                    </div>
                                </template>

                                <!-- Change / Kembalian Calculation -->
                                <div class="flex justify-between items-center text-xs font-bold text-emerald-600 dark:text-emerald-400 pt-1" x-show="changeAmount > 0" x-cloak>
                                    <span>Kembalian</span>
                                    <span class="text-sm font-black" x-text="'Rp ' + formatNumber(changeAmount)">Rp 0</span>
                                </div>
                            </div>

                            <!-- Real-time Live Validation Status Card -->
                            <div class="mt-3 p-3 rounded-2xl border transition-all text-xs font-bold"
                                 :class="getValidationState().isValid 
                                    ? 'bg-emerald-50/60 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200' 
                                    : 'bg-rose-50/60 dark:bg-rose-950/30 border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200'">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-2xs font-black uppercase tracking-wider flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm" x-text="getValidationState().isValid ? 'verified' : 'fact_check'"></span>
                                        Validasi Realtime
                                    </span>
                                    <span class="text-2xs font-extrabold px-2 py-0.5 rounded-full"
                                          :class="getValidationState().isValid ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white'"
                                          x-text="getValidationState().isValid ? 'SIAP TRANSAKSI' : 'BELUM LENGKAP'">
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-1.5 text-2xs">
                                    <div class="flex items-center gap-1.5" :class="getValidationState().shiftValid ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                        <span class="material-symbols-outlined text-xs" x-text="getValidationState().shiftValid ? 'check_circle' : 'cancel'"></span>
                                        <span>Shift: <strong x-text="getValidationState().shiftValid ? 'Aktif' : 'Tutup'"></strong></span>
                                    </div>
                                    <div class="flex items-center gap-1.5" :class="getValidationState().customerValid ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                        <span class="material-symbols-outlined text-xs" x-text="getValidationState().customerValid ? 'check_circle' : 'cancel'"></span>
                                        <span>Pelanggan: <strong x-text="getValidationState().customerValid ? 'OK' : 'Kosong'"></strong></span>
                                    </div>
                                    <div class="flex items-center gap-1.5" :class="getValidationState().cartValid ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                        <span class="material-symbols-outlined text-xs" x-text="getValidationState().cartValid ? 'check_circle' : 'cancel'"></span>
                                        <span>Items: <strong x-text="cart.length + ' Item'"></strong></span>
                                    </div>
                                    <div class="flex items-center gap-1.5" :class="getValidationState().paymentValid ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                        <span class="material-symbols-outlined text-xs" x-text="getValidationState().paymentValid ? 'check_circle' : 'cancel'"></span>
                                        <span>Bayar: <strong x-text="getValidationState().paymentValid ? 'Valid' : 'Kurang'"></strong></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Dynamic Real-time Submit Action Button -->
                            <button type="submit"
                                    :disabled="!getValidationState().isValid"
                                    :class="getValidationState().isValid 
                                        ? 'bg-gradient-to-r from-primary to-orange-600 hover:from-primary-hover hover:to-orange-700 text-white shadow-lg shadow-primary/25 active:scale-98 cursor-pointer' 
                                        : 'bg-slate-200 dark:bg-slate-800 text-slate-400 dark:text-slate-600 cursor-not-allowed opacity-75'"
                                    class="w-full h-12 mt-2 rounded-2xl font-black text-xs md:text-sm flex items-center justify-center gap-2 transition-all">
                                <span class="material-symbols-outlined text-lg" x-text="getValidationState().isValid ? 'point_of_sale' : 'block'"></span>
                                <span x-text="getValidationState().buttonLabel"></span>
                            </button>
                        </div>
                    </form>
                </x-card>
            </div>
        </div>

        <!-- Modal Buka Shift Kasir -->
        <div x-show="showOpenShiftModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-primary text-white flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-xl">key</span>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Buka Shift Kasir</h3>
                        <p class="text-xs text-slate-500">Masukkan Modal Kas Awal (Opening Cash) di laci kasir.</p>
                    </div>
                </div>

                <form action="{{ route('pos.shift.open') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-2xs font-extrabold text-slate-400 uppercase mb-1">Modal Kas Awal (Rp)</label>
                        <input type="number" name="opening_cash" required min="0" step="1000" placeholder="Misal: 200000"
                               class="w-full h-11 px-3.5 rounded-xl border border-slate-200 dark:border-slate-800 text-sm font-extrabold text-primary outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-2xs font-extrabold text-slate-400 uppercase mb-1">Catatan Shift (Opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Catatan kondisi awal kasir..." class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs outline-none focus:border-primary"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showOpenShiftModal = false" class="px-4 py-2 text-xs font-bold text-slate-500">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-primary text-white font-extrabold text-xs rounded-xl shadow-md shadow-primary/20">Mulai Shift</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Tutup Shift Kasir -->
        @if($activeShift)
            <div x-show="showCloseShiftModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-cloak>
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-rose-500 text-white flex items-center justify-center font-bold">
                                <span class="material-symbols-outlined text-xl">lock_clock</span>
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">Rekapitulasi Closing Shift #{{ $activeShift->id }}</h3>
                                <p class="text-xs text-slate-500">Buka: {{ $activeShift->opened_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <button type="button" @click="showCloseShiftModal = false" class="text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <form action="{{ route('pos.shift.close') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-2 gap-3 bg-slate-50 dark:bg-slate-800/50 p-3 rounded-2xl text-xs">
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Modal Awal</span>
                                <span class="font-black text-slate-800 dark:text-slate-200">Rp {{ number_format($activeShift->opening_cash, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase font-bold">Petty Cash Out</span>
                                <span class="font-black text-rose-500">Rp {{ number_format($activeShift->petty_cash_total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-2xs font-extrabold text-slate-400 uppercase mb-1">Hitung Uang Kas Fisik di Laci (Actual Cash)</label>
                            <input type="number" name="closing_cash_actual" required min="0" placeholder="Masukkan total uang tunai di laci..."
                                   class="w-full h-11 px-3.5 rounded-xl border border-slate-200 dark:border-slate-800 text-sm font-black text-slate-900 dark:text-white outline-none focus:border-primary">
                        </div>

                        <div>
                            <label class="block text-2xs font-extrabold text-slate-400 uppercase mb-1">Catatan Closing</label>
                            <textarea name="notes" rows="2" placeholder="Catatan selisih kas / serah terima..." class="w-full p-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs outline-none focus:border-primary"></textarea>
                        </div>

                        <div class="flex justify-between items-center pt-2">
                            <a href="{{ route('pos.shift.summary-pdf', $activeShift->id) }}" target="_blank" class="px-3 py-2 bg-slate-100 text-slate-700 font-extrabold text-2xs rounded-xl flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">print</span>
                                Preview Struk Shift
                            </a>
                            <div class="flex gap-2">
                                <button type="button" @click="showCloseShiftModal = false" class="px-4 py-2 text-xs font-bold text-slate-500">Batal</button>
                                <button type="submit" class="px-5 py-2.5 bg-rose-600 text-white font-extrabold text-xs rounded-xl shadow-md shadow-rose-500/20">Proses Closing Shift</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Modal Petty Cash -->
        <div x-show="showPettyCashModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-2xl bg-rose-500 text-white flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-xl">payments</span>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Pengeluaran Kas Kecil (Petty Cash)</h3>
                        <p class="text-xs text-slate-500">Catat pengeluaran operasional instan kasir.</p>
                    </div>
                </div>

                <form action="{{ route('pos.petty-cash.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-2xs font-extrabold text-slate-400 uppercase mb-1">Kategori Pengeluaran</label>
                        <select name="category" class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold outline-none">
                            <option value="Operasional">Operasional Harian</option>
                            <option value="Perlengkapan">Perlengkapan & Plastik</option>
                            <option value="Konsumsi">Konsumsi Staf</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-2xs font-extrabold text-slate-400 uppercase mb-1">Nominal (Rp)</label>
                        <input type="number" name="amount" required min="1" placeholder="Nominal..." class="w-full h-10 px-3.5 rounded-xl border border-slate-200 text-sm font-black text-rose-600 outline-none">
                    </div>
                    <div>
                        <label class="block text-2xs font-extrabold text-slate-400 uppercase mb-1">Keterangan / Keperluan</label>
                        <input type="text" name="description" required placeholder="Beli plastik 5kg..." class="w-full h-10 px-3.5 rounded-xl border border-slate-200 text-xs outline-none">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showPettyCashModal = false" class="px-4 py-2 text-xs font-bold text-slate-500">Batal</button>
                        <button type="submit" class="px-5 py-2.5 bg-rose-600 text-white font-extrabold text-xs rounded-xl shadow-md">Simpan Pengeluaran</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Hold Order (Draft Cart) List & Save -->
        <div x-show="showDraftModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200 dark:border-slate-800">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-orange-500 text-white flex items-center justify-center font-bold">
                            <span class="material-symbols-outlined text-xl">pending_actions</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Hold Order (Pending Cart)</h3>
                            <p class="text-xs text-slate-500">Daftar transaksi yang disimpan sementara.</p>
                        </div>
                    </div>
                    <button type="button" @click="showDraftModal = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-80 overflow-y-auto mb-4">
                    <template x-if="draftOrders.length === 0">
                        <div class="py-8 text-center text-xs text-slate-400">Tidak ada draft transaksi disimpan.</div>
                    </template>
                    <template x-for="d in draftOrders" :key="d.id">
                        <div class="py-3 flex items-center justify-between gap-3">
                            <div>
                                <h5 class="text-xs font-black text-slate-800 dark:text-slate-100" x-text="d.draft_name"></h5>
                                <p class="text-2xs text-slate-400" x-text="(d.customer ? d.customer.name : 'Pelanggan Walk-In') + ' • Rp ' + formatNumber(d.total_amount)"></p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="loadDraft(d)" class="px-3 py-1.5 bg-primary/10 text-primary font-extrabold text-2xs rounded-xl">Load</button>
                                <button type="button" @click="deleteDraft(d.id)" class="px-2 py-1.5 text-rose-500 hover:bg-rose-50 rounded-xl text-2xs">Hapus</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine App Controller -->
    <script>
        function posApp() {
            return {
                customers: window.__posCustomers || [],
                activeShift: window.__posActiveShift || null,
                draftOrders: window.__posDraftOrders || [],
                activeCategory: 'all',
                serviceSearch: '',
                customerSearch: '',
                customerOpen: false,
                selectedCustomer: null,
                customerId: '',
                customerPoints: 0,
                customerTier: 'Bronze',
                promoId: '',
                promoType: '',
                promoValue: 0,
                promoMin: 0,
                promoDescription: '',
                manualCouponCode: '',
                couponMessage: '',
                couponError: false,
                cart: [],
                subtotal: 0,
                discount: 0,
                pointsUsed: 0,
                pointsDiscount: 0,
                total: 0,
                paymentType: 'full',
                paymentMethod: 'cash',
                paidAmount: 0,
                changeAmount: 0,
                split1: { method: 'cash', amount: 0 },
                split2: { method: 'qris', amount: 0 },
                currentDraftId: null,

                // Order Channel / Jenis Order
                orderType: 'outlet',
                walkInName: '',
                deliveryAddress: '',
                deliveryPhone: '',
                pickupScheduledAt: '',

                showOpenShiftModal: false,
                showCloseShiftModal: false,
                showPettyCashModal: false,
                showDraftModal: false,
                showAddCustomerModal: false,
                showBranchScopeModal: false,

                pointExchangeRate: {{ $pointExchangeRate }},
                pointEarnSpendThreshold: {{ $pointEarnSpendThreshold }},
                pointMinRedeem: {{ $pointMinRedeem }},

                init() {
                    this.calculateTotals();
                },

                matchesServiceSearch(name, type) {
                    const q = this.serviceSearch.trim().toLowerCase();
                    if (!q) return true;
                    return name.toLowerCase().includes(q) || type.toLowerCase().includes(q);
                },

                getItemQuantity(id) {
                    const found = this.cart.find(i => i.service_id === id);
                    return found ? found.quantity : 0;
                },

                addToCart(id, name, price, unit) {
                    let existing = this.cart.find(item => item.service_id === id);
                    if (existing) {
                        existing.quantity += 1;
                    } else {
                        this.cart.push({ service_id: id, name: name, price: price, unit: unit, quantity: 1, notes: '' });
                    }
                    this.calculateTotals();
                },

                decrementServiceQuantity(id) {
                    let idx = this.cart.findIndex(i => i.service_id === id);
                    if (idx !== -1) {
                        if (this.cart[idx].quantity > 1) {
                            this.cart[idx].quantity -= 1;
                        } else {
                            this.cart.splice(idx, 1);
                        }
                        this.calculateTotals();
                    }
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                    this.calculateTotals();
                },

                filteredCustomers() {
                    if (!this.customerSearch) return this.customers;
                    const q = this.customerSearch.trim();
                    const phoneQ = q.replace(/[^0-9]/g, '');

                    if (window.FuzzyEngine) {
                        return [...this.customers].filter(c => {
                            const cPhone = (c.phone || '').replace(/[^0-9]/g, '');
                            const phoneMatch = phoneQ ? cPhone.includes(phoneQ) : false;
                            const fuzzyName = window.FuzzyEngine.match(q, c.name, 0.45);
                            const fuzzyPhone = window.FuzzyEngine.match(q, c.phone || '', 0.45);
                            return phoneMatch || fuzzyName || fuzzyPhone;
                        }).sort((a, b) => {
                            const scoreA = Math.max(
                                window.FuzzyEngine.score(q, a.name),
                                window.FuzzyEngine.score(q, a.phone || '')
                            );
                            const scoreB = Math.max(
                                window.FuzzyEngine.score(q, b.name),
                                window.FuzzyEngine.score(q, b.phone || '')
                            );
                            return scoreB - scoreA;
                        });
                    }

                    return [...this.customers].filter(c => {
                        const cPhone = (c.phone || '').replace(/[^0-9]/g, '');
                        const phoneMatch = phoneQ ? cPhone.includes(phoneQ) : false;
                        const nameMatch = c.name.toLowerCase().includes(q.toLowerCase());
                        const rawPhoneMatch = (c.phone || '').toLowerCase().includes(q.toLowerCase());
                        return phoneMatch || nameMatch || rawPhoneMatch;
                    }).sort((a, b) => {
                        return a.name.localeCompare(b.name);
                    });
                },

                selectCustomer(c) {
                    this.selectedCustomer = c;
                    this.customerId = String(c.id);
                    this.customerSearch = c.name;
                    this.customerPoints = c.loyalty_points || 0;
                    this.customerTier = c.loyalty_tier || 'Bronze';
                    this.calculateTotals();
                },

                clearCustomer() {
                    this.selectedCustomer = null;
                    this.customerId = '';
                    this.customerSearch = '';
                    this.customerPoints = 0;
                    this.customerTier = 'Bronze';
                    this.pointsUsed = 0;
                    this.calculateTotals();
                },

                openHoldOrderModal() {
                    let name = prompt("Masukkan nama/label untuk hold order ini:", this.selectedCustomer ? this.selectedCustomer.name : "Hold Order");
                    if (!name) return;

                    fetch('{{ route("pos.drafts.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            draft_name: name,
                            customer_id: this.customerId || null,
                            cart_data: this.cart,
                            total_amount: this.total,
                        })
                    }).then(r => r.json()).then(data => {
                        if (data.success) {
                            this.draftOrders.unshift(data.draft);
                            this.cart = [];
                            this.calculateTotals();
                            Toast.success("Order berhasil di-hold!");
                        }
                    });
                },

                loadDraft(draft) {
                    this.cart = draft.cart_data || [];
                    this.currentDraftId = draft.id;
                    if (draft.customer) {
                        this.selectCustomer(draft.customer);
                    }
                    this.showDraftModal = false;
                    this.calculateTotals();
                    Toast.info(`Draft "${draft.draft_name}" dimuat ke keranjang.`);
                },

                deleteDraft(id) {
                    AppDialog.danger("Hapus Draft", "Apakah Anda yakin ingin menghapus draft transaksi ini?").then(confirmed => {
                        if (!confirmed) return;
                        fetch(`/pos/drafts/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            }
                        }).then(r => r.json()).then(data => {
                            if (data.success) {
                                this.draftOrders = this.draftOrders.filter(d => d.id !== id);
                                Toast.success("Draft transaksi berhasil dihapus.");
                            }
                        });
                    });
                },

                updatePromoData() {
                    const el = document.getElementById('promo_id');
                    if (!el || !el.value) {
                        this.promoId = '';
                        this.promoType = '';
                        this.promoValue = 0;
                        this.promoMin = 0;
                        this.calculateTotals();
                        return;
                    }

                    const opt = el.options[el.selectedIndex];
                    this.promoId = el.value;
                    this.promoType = opt.dataset.type || '';
                    this.promoValue = parseFloat(opt.dataset.value) || 0;
                    this.promoMin = parseFloat(opt.dataset.min) || 0;

                    if (this.subtotal < this.promoMin) {
                        Toast.warning(`Minimal transaksi promo ini adalah Rp ${this.formatNumber(this.promoMin)}.`);
                    } else {
                        Toast.success(`Promo "${opt.text.split('(')[0].trim()}" berhasil diterapkan!`);
                    }

                    this.calculateTotals();
                },

                applyManualCoupon() {
                    const code = (this.manualCouponCode || '').trim().toUpperCase();
                    if (!code) {
                        Toast.warning("Masukkan kode kupon terlebih dahulu!");
                        return;
                    }

                    const el = document.getElementById('promo_id');
                    if (!el) return;

                    let foundIndex = -1;
                    for (let i = 0; i < el.options.length; i++) {
                        const optCode = (el.options[i].dataset.code || '').toUpperCase();
                        if (optCode === code) {
                            foundIndex = i;
                            break;
                        }
                    }

                    if (foundIndex !== -1) {
                        el.selectedIndex = foundIndex;
                        this.updatePromoData();
                        this.manualCouponCode = '';
                    } else {
                        Toast.error(`Kode kupon "${code}" tidak ditemukan atau sudah tidak aktif.`);
                    }
                },

                toggleRedeemPoints() {
                    if (!this.selectedCustomer) {
                        Toast.warning("Pilih pelanggan terlebih dahulu untuk menukarkan poin!");
                        return;
                    }

                    if (this.pointsUsed > 0) {
                        this.pointsUsed = 0;
                        this.pointsDiscount = 0;
                        Toast.info("Penggunaan poin dibatalkan.");
                    } else {
                        if (this.customerPoints < (this.pointMinRedeem || 0)) {
                            Toast.warning(`Minimal poin untuk penukaran adalah ${this.pointMinRedeem || 0} poin. Poin Anda: ${this.customerPoints}`);
                            return;
                        }

                        const rate = this.pointExchangeRate > 0 ? this.pointExchangeRate : 1;
                        const maxPointsPossible = Math.min(this.customerPoints, Math.floor((this.subtotal - this.discount) / rate));
                        if (maxPointsPossible <= 0) {
                            Toast.warning("Subtotal transaksi terlalu kecil untuk penukaran poin.");
                            return;
                        }

                        this.pointsUsed = maxPointsPossible;
                        this.pointsDiscount = this.pointsUsed * rate;
                        Toast.success(`Berhasil menukarkan ${this.pointsUsed} Poin (Diskon Rp ${this.formatNumber(this.pointsDiscount)})`);
                    }

                    this.calculateTotals();
                },

                getSplitTotal() {
                    return (parseFloat(this.split1.amount) || 0) + (parseFloat(this.split2.amount) || 0);
                },

                calculateTotals() {
                    this.subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                    
                    this.discount = 0;
                    if (this.promoId && this.subtotal >= this.promoMin) {
                        if (this.promoType === 'percent') {
                            this.discount = this.subtotal * (this.promoValue / 100);
                        } else if (this.promoType === 'nominal') {
                            this.discount = this.promoValue;
                        }
                        if (this.discount > this.subtotal) this.discount = this.subtotal;
                    }

                    this.total = Math.max(0, this.subtotal - this.discount - this.pointsDiscount);

                    if (this.paymentType === 'full' && this.paymentMethod === 'cash') {
                        if (this.paidAmount === null || this.paidAmount === undefined || this.paidAmount === '') {
                            this.paidAmount = this.total;
                        }
                        this.changeAmount = Math.max(0, this.paidAmount - this.total);
                    } else if (this.paymentType === 'split') {
                        this.paidAmount = this.getSplitTotal();
                        this.changeAmount = 0;
                    } else {
                        this.changeAmount = 0;
                    }
                },

                getValidationState() {
                    const shiftValid = Boolean(this.activeShift);
                    const customerValid = Boolean(this.customerId);
                    const cartValid = this.cart.length > 0;
                    
                    let paymentValid = false;
                    let paymentMessage = '';

                    if (this.paymentType === 'full') {
                        if (this.paymentMethod === 'cash') {
                            const deficit = this.total - (this.paidAmount || 0);
                            if (deficit > 0) {
                                paymentValid = false;
                                paymentMessage = `Uang tunai kurang Rp ${this.formatNumber(deficit)}`;
                            } else {
                                paymentValid = true;
                            }
                        } else {
                            paymentValid = true;
                        }
                    } else if (this.paymentType === 'dp') {
                        const dpVal = parseFloat(this.paidAmount) || 0;
                        if (dpVal <= 0) {
                            paymentValid = false;
                            paymentMessage = 'Nominal DP harus lebih dari Rp 0';
                        } else if (dpVal > this.total) {
                            paymentValid = false;
                            paymentMessage = `DP melebihi total (Rp ${this.formatNumber(this.total)})`;
                        } else {
                            paymentValid = true;
                        }
                    } else if (this.paymentType === 'split') {
                        const splitTotal = this.getSplitTotal();
                        const diff = Math.abs(this.total - splitTotal);
                        if (diff > 0.99) {
                            paymentValid = false;
                            paymentMessage = `Split Pay (${this.formatNumber(splitTotal)}) ${splitTotal < this.total ? 'kurang' : 'lebih'} Rp ${this.formatNumber(diff)}`;
                        } else {
                            paymentValid = true;
                        }
                    }

                    const isValid = shiftValid && customerValid && cartValid && paymentValid;

                    let buttonLabel = 'Proses Order & Cetak Nota';
                    if (!shiftValid) buttonLabel = 'Buka Shift Kasir Terlebih Dahulu';
                    else if (!customerValid) buttonLabel = 'Silakan Pilih Data Pelanggan';
                    else if (!cartValid) buttonLabel = 'Keranjang Belanja Masih Kosong';
                    else if (!paymentValid) buttonLabel = paymentMessage;
                    else buttonLabel = `Proses Order & Cetak Nota (Rp ${this.formatNumber(this.total)})`;

                    return {
                        shiftValid,
                        customerValid,
                        cartValid,
                        paymentValid,
                        paymentMessage,
                        isValid,
                        buttonLabel
                    };
                },

                confirmCheckout() {
                    const val = this.getValidationState();
                    if (!val.isValid) {
                        AppDialog.alert("Validasi Gagal", val.buttonLabel, { type: 'warning' });
                        return;
                    }

                    const customerName = this.selectedCustomer ? this.selectedCustomer.name : 'Pelanggan Walk-In';
                    const customerPhone = this.selectedCustomer ? (this.selectedCustomer.phone || '-') : '-';

                    let itemsRows = this.cart.map(item => `
                        <tr>
                            <td style="font-weight: 700;">${item.name}</td>
                            <td class="text-right" style="font-weight: 700;">${item.quantity} ${item.unit}</td>
                            <td class="text-right">Rp ${this.formatNumber(item.price)}</td>
                            <td class="text-right font-bold">Rp ${this.formatNumber(item.price * item.quantity)}</td>
                        </tr>
                    `).join('');

                    let paymentMethodLabel = (this.paymentMethod || 'CASH').toUpperCase();
                    if (this.paymentType === 'dp') paymentMethodLabel = 'DP (Uang Muka)';
                    if (this.paymentType === 'split') paymentMethodLabel = 'Split Payment';

                    const tableHtml = `
                        <div style="margin-bottom: 8px; font-weight: 800; font-size: 11px; text-transform: uppercase; color: #ff6600; display: flex; align-items: center; gap: 4px;">
                            <svg style="width: 15px; height: 15px; fill: currentColor; flex-shrink: 0;" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            <span>PELANGGAN: ${customerName} (${customerPhone})</span>
                        </div>
                        <table class="dialog-table">
                            <thead>
                                <tr>
                                    <th>Layanan</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Harga</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsRows}
                            </tbody>
                        </table>
                        <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #cbd5e1; font-size: 11.5px; display: flex; flex-direction: column; gap: 4px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span>Subtotal:</span>
                                <span style="font-weight: 700;">Rp ${this.formatNumber(this.subtotal)}</span>
                            </div>
                            ${this.discount > 0 ? `
                                <div style="display: flex; justify-content: space-between; color: #059669;">
                                    <span>Diskon Promo:</span>
                                    <span style="font-weight: 700;">- Rp ${this.formatNumber(this.discount)}</span>
                                </div>
                            ` : ''}
                            <div style="display: flex; justify-content: space-between; font-weight: 900; font-size: 13px; color: #ff6600; padding-top: 4px; border-top: 1px dashed #cbd5e1;">
                                <span>TOTAL TAGIHAN:</span>
                                <span>Rp ${this.formatNumber(this.total)}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; opacity: 0.8; margin-top: 2px;">
                                <span>Metode Bayar: <strong>${paymentMethodLabel}</strong></span>
                                <span>Bayar: <strong>Rp ${this.formatNumber(this.paidAmount)}</strong></span>
                            </div>
                            ${this.changeAmount > 0 ? `
                                <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 11.5px; color: #059669;">
                                    <span>Kembalian:</span>
                                    <span>Rp ${this.formatNumber(this.changeAmount)}</span>
                                </div>
                            ` : ''}
                        </div>
                    `;

                    AppDialog.confirm(
                        "Konfirmasi Pembayaran POS",
                        `Periksa rincian item order & total tagihan di bawah ini sebelum mencetak nota:`,
                        { 
                            type: 'info', 
                            confirmText: 'Ya, Proses Transaksi',
                            detailsHtml: tableHtml
                        }
                    ).then(confirmed => {
                        if (confirmed) {
                            document.getElementById('pos-form').submit();
                        }
                    });
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('id-ID').format(num || 0);
                }
            };
        }
    </script>
</x-app-layout>
