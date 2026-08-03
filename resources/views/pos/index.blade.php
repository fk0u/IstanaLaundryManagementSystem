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
                
                <!-- Customer & Promo Selectors -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Customer Selection -->
                    <x-card title="Pilih Pelanggan" :compact="true" class="!overflow-visible z-20">
                        <div class="flex flex-col gap-3">
                            
                            <!-- State 1: When Customer Selected -->
                            <div x-show="customerId" x-cloak class="relative bg-gradient-to-r from-orange-500/10 via-amber-500/5 to-transparent dark:from-slate-800 dark:to-slate-800/50 p-3.5 rounded-2xl border border-orange-200/80 dark:border-slate-700/80 flex items-center gap-3.5 shadow-xs">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary to-orange-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-primary/20 shrink-0">
                                    <span x-text="selectedCustomer ? selectedCustomer.name.substring(0, 2).toUpperCase() : 'CU'"></span>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white truncate" x-text="selectedCustomer ? selectedCustomer.name : ''"></h4>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20" x-text="customerTier">Bronze</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-2xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
                                        <span class="flex items-center gap-1" x-show="selectedCustomer && selectedCustomer.phone"><span class="material-symbols-outlined text-xs">call</span> <span x-text="selectedCustomer ? selectedCustomer.phone : ''"></span></span>
                                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">stars</span> <span class="font-bold text-primary" x-text="customerPoints"></span> Poin</span>
                                    </div>
                                </div>

                                <button type="button" @click="clearCustomer()" title="Ganti Pelanggan"
                                        class="btn-touch shrink-0 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-rose-500 text-2xs font-bold flex items-center gap-1 transition-all cursor-pointer shadow-xs">
                                    <span class="material-symbols-outlined text-sm">swap_horiz</span>
                                    Ganti
                                </button>
                            </div>

                            <!-- State 2: Customer Combobox Search -->
                            <div x-show="!customerId" class="flex flex-col gap-2">
                                <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Cari Nama / No. HP Pelanggan</label>
                                <div class="flex gap-2">
                                    <div class="relative flex-1" @click.outside="customerOpen = false">
                                        <div class="relative flex items-center">
                                            <span class="material-symbols-outlined absolute left-3 text-slate-400 text-lg pointer-events-none">search</span>
                                            <input type="text" x-model="customerSearch" autocomplete="off"
                                                   @focus="customerOpen = true" @click="customerOpen = true" @input="customerOpen = true"
                                                   placeholder="Ketik nama atau no. HP..."
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
                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Pelanggan Terdaftar</span>
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
                                                            <h5 class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-primary transition-colors truncate" x-text="c.name"></h5>
                                                            <p class="text-2xs text-slate-400 font-mono" x-text="c.phone"></p>
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
                        <input type="hidden" name="draft_id" x-model="currentDraftId">

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

                            <!-- Submit Action Button -->
                            <button type="submit" :disabled="!activeShift"
                                    class="w-full h-12 mt-3 rounded-2xl bg-gradient-to-r from-primary to-orange-600 hover:from-primary-hover hover:to-orange-700 text-white font-black text-sm shadow-lg shadow-primary/25 flex items-center justify-center gap-2 cursor-pointer transition-all active:scale-98 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span class="material-symbols-outlined text-lg">check_circle</span>
                                Process Order & Print Nota
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

                showOpenShiftModal: false,
                showCloseShiftModal: false,
                showPettyCashModal: false,
                showDraftModal: false,
                showAddCustomerModal: false,

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
                    const q = this.customerSearch.trim().toLowerCase();
                    if (!q) return this.customers;
                    return this.customers.filter(c => c.name.toLowerCase().includes(q) || (c.phone || '').includes(q));
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
                            alert("Order berhasil di-hold!");
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
                },

                deleteDraft(id) {
                    if (!confirm("Hapus draft transaksi ini?")) return;
                    fetch(`/pos/drafts/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        }
                    }).then(r => r.json()).then(data => {
                        if (data.success) {
                            this.draftOrders = this.draftOrders.filter(d => d.id !== id);
                        }
                    });
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
                        if (this.paidAmount < this.total) {
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

                confirmCheckout() {
                    if (!this.activeShift) {
                        alert("Buka shift kasir terlebih dahulu!");
                        this.showOpenShiftModal = true;
                        return;
                    }
                    if (!this.customerId) {
                        alert("Pilih pelanggan terlebih dahulu!");
                        return;
                    }
                    if (this.cart.length === 0) {
                        alert("Keranjang belanja masih kosong!");
                        return;
                    }
                    document.getElementById('pos-form').submit();
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('id-ID').format(num || 0);
                }
            };
        }
    </script>
</x-app-layout>
