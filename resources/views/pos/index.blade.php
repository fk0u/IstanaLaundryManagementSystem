<x-app-layout>
    {{-- Pass customer data via a script tag to avoid HTML attribute parsing issues
         when customer names contain special characters (quotes, newlines, etc.).
         Using x-data="posApp()" (no args) lets Alpine init the component cleanly. --}}
    <script>
        window.__posCustomers = @json($customers);
    </script>
    <div x-data="posApp()" class="min-h-[calc(100vh-100px)] flex flex-col gap-6">
        
        <x-page-header title="Point of Sale (POS)" :breadcrumbs="['POS' => '/pos']">
            <x-slot:actions>
                @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
                    <div class="flex items-center gap-2 bg-orange-50 dark:bg-slate-800 border border-orange-200 dark:border-slate-700 px-3 py-1.5 rounded-xl">
                        <span class="material-symbols-outlined text-primary text-base">storefront</span>
                        <div class="text-xs">
                            <span class="text-slate-400 block text-[10px] font-bold uppercase leading-none">Cabang Aktif</span>
                            <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ $branch?->name ?? 'Semua Cabang' }}</span>
                        </div>
                        <button type="button" @click="showBranchScopeModal = true" class="ml-2 btn-touch text-2xs font-bold text-primary hover:underline cursor-pointer">
                            Ganti Cabang
                        </button>
                    </div>
                @else
                    <div class="flex items-center gap-2 bg-slate-100 dark:bg-slate-800 px-3 py-1.5 rounded-xl">
                        <span class="material-symbols-outlined text-primary text-base">storefront</span>
                        <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $branch?->name ?? 'Cabang Utama' }}</span>
                    </div>
                @endif
            </x-slot:actions>
        </x-page-header>

        <!-- Alert Flash Messages -->
        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif
        @if ($errors->any())
            <x-alert type="danger" message="Terjadi kesalahan validasi. Periksa inputan Anda." class="mb-2" />
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 flex-1 items-start">
            
            <!-- Left Side: Customer, Promo & Services (8 cols) -->
            <div class="lg:col-span-8 flex flex-col gap-6">
                
                <!-- Customer & Promo Selectors -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Customer Selection (searchable combobox + quick add) -->
                    <x-card title="Pilih Pelanggan" :compact="true" class="!overflow-visible z-20">
                        <div class="flex flex-col gap-3">
                            
                            <!-- State 1: When a Customer IS Selected -->
                            <div x-show="customerId" x-cloak class="relative bg-gradient-to-r from-orange-500/10 via-amber-500/5 to-transparent dark:from-slate-800 dark:to-slate-800/50 p-3.5 rounded-2xl border border-orange-200/80 dark:border-slate-700/80 flex items-center gap-3.5 shadow-xs">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary to-orange-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-primary/20 shrink-0">
                                    <span x-text="selectedCustomer ? selectedCustomer.name.substring(0, 2).toUpperCase() : (customerSearch ? customerSearch.substring(0, 2).toUpperCase() : 'CU')"></span>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white truncate" x-text="selectedCustomer ? selectedCustomer.name : customerSearch"></h4>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20" x-text="customerTier">Bronze</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-2xs text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
                                        <span class="flex items-center gap-1" x-show="selectedCustomer && selectedCustomer.phone"><span class="material-symbols-outlined text-xs">call</span> <span x-text="selectedCustomer ? selectedCustomer.phone : ''"></span></span>
                                        <span class="flex items-center gap-1"><span class="material-symbols-outlined text-xs">stars</span> <span class="font-bold text-primary" x-text="customerPoints"></span> Poin</span>
                                    </div>
                                </div>

                                <button type="button" @click="clearCustomer()" title="Ganti Pelanggan"
                                        class="btn-touch shrink-0 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:text-rose-500 hover:border-rose-300 text-2xs font-bold flex items-center gap-1 transition-all cursor-pointer shadow-xs">
                                    <span class="material-symbols-outlined text-sm">swap_horiz</span>
                                    Ganti
                                </button>
                            </div>

                            <!-- State 2: When Searching / Selecting Customer -->
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

                                        <!-- Dropdown Float Menu -->
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

                                            <template x-if="filteredCustomers().length === 0">
                                                <div class="px-4 py-6 text-center flex flex-col items-center gap-2">
                                                    <span class="material-symbols-outlined text-3xl text-slate-300 dark:text-slate-600">person_search</span>
                                                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">Pelanggan tidak ditemukan</p>
                                                    <button type="button" @click="openAddCustomerModal()" class="mt-1 btn-touch px-3 py-1.5 bg-primary/10 hover:bg-primary/20 text-primary rounded-xl text-2xs font-bold flex items-center gap-1 transition-all">
                                                        <span class="material-symbols-outlined text-sm">person_add</span>
                                                        Daftarkan Pelanggan Baru
                                                    </button>
                                                </div>
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
                            
                            <!-- Dropdown Select Promo -->
                            <select id="promo_id" x-model="promoId" @change="updatePromoData"
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all cursor-pointer">
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

                            <!-- Input Manual Kode Kupon -->
                            <div class="flex gap-2">
                                <input type="text" x-model="manualCouponCode" @keydown.enter.prevent="applyManualCoupon()"
                                       placeholder="Masukkan Kode Kupon..."
                                       class="w-full h-10 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs font-semibold uppercase focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                                <button type="button" @click="applyManualCoupon()"
                                        class="btn-touch shrink-0 px-4 h-10 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-bold transition-all cursor-pointer">
                                    Terapkan
                                </button>
                            </div>

                            <!-- Alert status kupon -->
                            <div x-show="couponMessage" x-cloak class="text-2xs font-semibold px-1" :class="couponError ? 'text-rose-500' : 'text-emerald-600 dark:text-emerald-400'" x-text="couponMessage"></div>

                            <div x-show="promoId" class="flex gap-3 items-center bg-emerald-50/60 dark:bg-emerald-950/20 p-2.5 rounded-xl border border-emerald-100 dark:border-emerald-900/30" x-cloak>
                                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-xl">local_activity</span>
                                <div class="flex-1">
                                    <span class="block text-2xs text-slate-400 font-bold uppercase tracking-wider">Diskon Promo</span>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200" x-text="promoDescription">Promo</span>
                                </div>
                                <button type="button" @click="clearPromo()" class="text-slate-400 hover:text-rose-500 p-1">
                                    <span class="material-symbols-outlined text-base">close</span>
                                </button>
                            </div>
                        </div>
                    </x-card>
                </div>

                <!-- Services Grid & Filters -->
                <x-card title="Pilih Layanan Cuci">
                    <!-- Category Scroll Pills & Search Bar -->
                    <div class="flex flex-col sm:flex-row gap-3 mb-4 items-stretch sm:items-center justify-between">
                        <!-- Scrollable Category Pills -->
                        <div class="scroll-pills flex-1 max-w-full">
                            <button type="button" @click="activeCategory = 'all'"
                                    :class="activeCategory === 'all' ? 'bg-primary text-white font-extrabold shadow-md shadow-primary/25 scale-[1.02]' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700'"
                                    class="pill-item">
                                <span class="material-symbols-outlined text-base">apps</span>
                                Semua
                            </button>
                            @php
                                $types = $services->pluck('type')->unique()->values();
                            @endphp
                            @foreach($types as $type)
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

                        <!-- Live Service Search -->
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
                                        <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-100 group-hover:text-primary dark:group-hover:text-orange-400 transition-colors">
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
                                            <button type="button" @click="decrementServiceQuantity({{ $service->id }})" class="w-8 h-8 rounded-lg bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 font-extrabold text-sm shadow-xs hover:bg-rose-50 hover:text-rose-600 transition-all flex items-center justify-center cursor-pointer active:scale-90">
                                                -
                                            </button>
                                            <span class="w-7 text-center text-xs font-black text-primary" x-text="getItemQuantity({{ $service->id }})"></span>
                                            <button type="button" @click="addToCart({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->price }}, '{{ $service->unit }}')" class="w-8 h-8 rounded-lg bg-primary text-white font-extrabold text-sm shadow-xs hover:bg-primary-hover transition-all flex items-center justify-center cursor-pointer active:scale-90">
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

            <!-- Right Side: Cart Panel (4 cols - Desktop/Tablet) -->
            <div class="lg:col-span-4 hidden lg:block sticky top-20">
                <x-card title="Keranjang Belanja">
                    
                    <form id="pos-form" action="{{ route('pos.store') }}" method="POST" class="flex flex-col gap-4" @submit.prevent="confirmCheckout()">
                        @csrf
                        
                        <input type="hidden" name="customer_id" x-model="customerId">
                        <input type="hidden" name="promo_id" x-model="promoId">

                        <!-- Cart Item List -->
                        <div class="overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 max-h-[320px] pr-1">
                            <template x-if="cart.length === 0">
                                <div class="py-12 text-center">
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
                                        
                                        <input type="text" :name="'items[' + index + '][notes]'" placeholder="Catatan..."
                                               class="mt-1 w-full h-7 px-2 rounded-lg border border-slate-150 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900 text-2xs text-slate-600 dark:text-slate-400 focus:border-primary outline-none">
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="0.01" step="0.01" @input="calculateTotals()"
                                               class="w-14 h-7 text-center rounded-lg border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-950 text-xs text-slate-800 dark:text-slate-200 font-bold focus:border-primary outline-none">
                                        <button type="button" @click="removeFromCart(index)" class="p-1 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg cursor-pointer">
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

                            <div class="flex justify-between text-xs text-orange-600 dark:text-orange-400 font-bold" x-show="pointsDiscount > 0" x-cloak>
                                <span>Diskon Poin (<span x-text="pointsUsed"></span> Pts)</span>
                                <span x-text="'- Rp ' + formatNumber(pointsDiscount)">- Rp 0</span>
                            </div>

                            <div class="flex flex-col gap-2 pt-2 border-t border-slate-100 dark:border-slate-800" x-show="customerId && customerPoints > 0" x-cloak>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-400 font-bold uppercase tracking-wider text-[10px]">Tukarkan Poin Member</span>
                                    <span class="text-2xs font-extrabold text-primary" x-text="'Saldo: ' + customerPoints + ' Poin'"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="points_used" x-model.number="pointsUsed" min="0" :max="customerPoints" @input="calculateTotals()"
                                           class="w-full h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-extrabold text-primary focus:border-primary outline-none">
                                    
                                    <button type="button" @click="maxRedeemPoints()" class="btn-touch shrink-0 px-2.5 h-9 bg-primary/10 hover:bg-primary/20 text-primary rounded-xl text-2xs font-extrabold transition-all cursor-pointer">
                                        Maksimal
                                    </button>
                                </div>
                                <div class="flex gap-1.5 flex-wrap">
                                    <button type="button" @click="quickRedeemPoints(50)" x-show="customerPoints >= 50" class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-2xs font-bold text-slate-600 dark:text-slate-300 hover:bg-primary hover:text-white transition-all">50 Pts</button>
                                    <button type="button" @click="quickRedeemPoints(100)" x-show="customerPoints >= 100" class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-2xs font-bold text-slate-600 dark:text-slate-300 hover:bg-primary hover:text-white transition-all">100 Pts</button>
                                    <button type="button" @click="quickRedeemPoints(500)" x-show="customerPoints >= 500" class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-2xs font-bold text-slate-600 dark:text-slate-300 hover:bg-primary hover:text-white transition-all">500 Pts</button>
                                    <button type="button" @click="pointsUsed = 0; calculateTotals()" x-show="pointsUsed > 0" class="px-2 py-0.5 rounded-md bg-rose-50 dark:bg-rose-950/30 text-rose-500 text-2xs font-bold hover:bg-rose-100 transition-all">Reset</button>
                                </div>
                            </div>

                            <!-- Bonus Estimated Earned Points Badge -->
                            <div x-show="customerId && total > 0" class="flex justify-between items-center p-2.5 rounded-xl border text-2xs font-semibold"
                                 :class="pointsUsed > 0 ? 'bg-slate-100/80 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 text-slate-500' : 'bg-orange-50/70 dark:bg-slate-800/40 border-orange-100 dark:border-slate-800/60 text-primary'" x-cloak>
                                <div class="flex items-center gap-1.5 font-bold" :class="pointsUsed > 0 ? 'text-slate-500' : 'text-primary'">
                                    <span class="material-symbols-outlined text-sm">stars</span>
                                    <span>Estimasi Poin Transaksi</span>
                                </div>
                                <span class="font-extrabold" :class="pointsUsed > 0 ? 'text-slate-400 font-semibold' : 'text-primary'" x-text="pointsUsed > 0 ? '0 Poin (Mekanisme Redeem)' : '+' + estimatedPointsEarned() + ' Poin (' + getTierMultiplier() + 'x)'"></span>
                            </div>

                            <div class="flex justify-between text-base font-black pt-2 border-t border-slate-100 dark:border-slate-800">
                                <span class="text-slate-800 dark:text-slate-200">Total</span>
                                <span class="text-primary" x-text="'Rp ' + formatNumber(total)">Rp 0</span>
                            </div>

                            <!-- Payment Fields -->
                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <div class="flex flex-col gap-1">
                                    <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Metode Bayar</label>
                                    <select name="payment_method" x-model="paymentMethod"
                                            class="w-full h-9 px-2 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-slate-200 outline-none focus:border-primary cursor-pointer">
                                        <option value="cash">Cash / Tunai</option>
                                        <option value="transfer">Transfer Bank</option>
                                        <option value="invoice">Invoice / Piutang</option>
                                    </select>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Bayar</label>
                                    <input type="number" name="paid_amount" x-model.number="paidAmount" @input="calculateTotals()"
                                           class="w-full h-9 px-2.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 outline-none focus:border-primary">
                                </div>
                            </div>

                            <!-- Quick Cash Buttons -->
                            <div class="flex flex-wrap gap-1.5 pt-1" x-show="paymentMethod === 'cash'" x-cloak>
                                <button type="button" @click="setQuickPaid('exact')" class="px-2.5 py-1 rounded-lg bg-orange-50 dark:bg-slate-800 text-primary dark:text-orange-400 border border-orange-200/60 dark:border-slate-700 text-2xs font-extrabold hover:bg-primary hover:text-white transition-all cursor-pointer haptic-press">
                                    Uang Pas
                                </button>
                                <button type="button" @click="setQuickPaid(50000)" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-2xs font-bold hover:bg-primary hover:text-white transition-all cursor-pointer haptic-press">
                                    50rb
                                </button>
                                <button type="button" @click="setQuickPaid(100000)" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-2xs font-bold hover:bg-primary hover:text-white transition-all cursor-pointer haptic-press">
                                    100rb
                                </button>
                                <button type="button" @click="setQuickPaid(200000)" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-2xs font-bold hover:bg-primary hover:text-white transition-all cursor-pointer haptic-press">
                                    200rb
                                </button>
                            </div>

                            <div class="flex justify-between text-xs pt-1" x-show="paymentMethod !== 'invoice'">
                                <span class="text-slate-400">Kembalian</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200" x-text="'Rp ' + formatNumber(changeAmount)">Rp 0</span>
                            </div>

                            <!-- Submit Checkout -->
                            <button type="submit" :disabled="cart.length === 0"
                                    class="w-full h-11 mt-3 bg-primary hover:bg-primary-hover disabled:bg-slate-200 dark:disabled:bg-slate-850 disabled:text-slate-400 dark:disabled:text-slate-600 disabled:cursor-not-allowed text-white font-bold rounded-xl text-xs flex items-center justify-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/20">
                                <span class="material-symbols-outlined text-base">payments</span>
                                Simpan & Cetak Nota
                            </button>
                        </div>

                    </form>
                </x-card>
            </div>

        </div>

        <!-- Floating Cart Bar for Mobile/Tablet (< lg screens) -->
        <div class="lg:hidden fixed bottom-16 inset-x-0 z-40 p-3 bg-white/90 dark:bg-slate-900/90 backdrop-blur-lg border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shadow-lg">
            <div class="flex items-center gap-3">
                <div class="relative">
                    <span class="material-symbols-outlined text-primary text-2xl">shopping_basket</span>
                    <span x-show="cart.length > 0" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center" x-text="cart.length"></span>
                </div>
                <div>
                    <span class="block text-2xs text-slate-400 font-bold uppercase">Total Tagihan</span>
                    <span class="text-sm font-black text-primary" x-text="'Rp ' + formatNumber(total)">Rp 0</span>
                </div>
            </div>
            <button type="button" @click="mobileCartOpen = true" class="btn-touch px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl shadow-md shadow-primary/20 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-base">shopping_cart</span>
                Lihat Keranjang (<span x-text="cart.length">0</span>)
            </button>
        </div>

        <!-- Mobile Slide-Up Cart Sheet -->
        <div x-show="mobileCartOpen" class="fixed inset-0 z-[9999] lg:hidden" x-cloak>
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" @click="mobileCartOpen = false"></div>
            <div class="fixed inset-x-0 bottom-0 max-h-[90vh] bg-white dark:bg-slate-900 rounded-t-3xl p-5 pb-24 overflow-y-auto flex flex-col gap-4 shadow-2xl">
                <div class="sheet-handle"></div>
                <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-black text-base text-slate-900 dark:text-white">Keranjang Belanja</h3>
                    <button type="button" @click="mobileCartOpen = false" class="p-1 text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Mobile Cart Form -->
                <form id="pos-form-mobile" action="{{ route('pos.store') }}" method="POST" @submit.prevent="confirmCheckout(); mobileCartOpen = false;">
                    @csrf
                    <input type="hidden" name="customer_id" x-model="customerId">
                    <input type="hidden" name="promo_id" x-model="promoId">
                    <input type="hidden" name="points_used" x-model="pointsUsed">

                    <div class="space-y-3 max-h-[250px] overflow-y-auto pr-1">
                        <template x-if="cart.length === 0">
                            <p class="text-center py-6 text-xs text-slate-400">Keranjang masih kosong.</p>
                        </template>
                        <template x-for="(item, index) in cart" :key="index">
                            <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                                <div class="flex-1 min-w-0">
                                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200 block truncate" x-text="item.name"></span>
                                    <span class="text-2xs text-slate-400" x-text="'Rp ' + formatNumber(item.price) + ' / ' + item.unit"></span>
                                    <input type="hidden" :name="'items[' + index + '][service_id]'" :value="item.service_id">
                                    <input type="hidden" :name="'items[' + index + '][notes]'" :value="item.notes || ''">
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="0.01" step="0.01" @input="calculateTotals()" class="w-14 h-7 text-center rounded-lg border text-xs font-bold">
                                    <button type="button" @click="removeFromCart(index)" class="text-rose-500"><span class="material-symbols-outlined text-base">delete</span></button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800 pt-3 mt-3 space-y-2">
                        <div class="flex justify-between text-sm font-black">
                            <span>Total</span>
                            <span class="text-primary" x-text="'Rp ' + formatNumber(total)"></span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <select name="payment_method" x-model="paymentMethod" class="h-10 text-xs rounded-xl border px-2">
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="invoice">Invoice</option>
                            </select>
                            <input type="number" name="paid_amount" x-model.number="paidAmount" @input="calculateTotals()" class="h-10 text-xs font-bold rounded-xl border px-3" placeholder="Bayar">
                        </div>
                        <button type="submit" :disabled="cart.length === 0" class="w-full h-12 bg-primary text-white font-bold rounded-xl text-xs mt-2 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">payments</span>
                            Proses Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add New Customer Modal -->
        <div x-show="showAddCustomerModal"
             class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
             x-transition x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl flex flex-col gap-4"
                 @click.away="showAddCustomerModal = false">

                <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100">Tambah Pelanggan Baru</h3>
                    <button type="button" @click="showAddCustomerModal = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div x-show="newCustomerError" x-cloak class="text-2xs font-semibold text-rose-600 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 rounded-xl px-3 py-2" x-text="newCustomerError"></div>

                <div class="flex flex-col gap-3">
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Nama *</label>
                        <input type="text" x-model="newCustomer.name" placeholder="Nama pelanggan"
                               class="w-full h-11 mt-1 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider">No. HP *</label>
                        <input type="text" x-model="newCustomer.phone" placeholder="08xxxxxxxxxx"
                               class="w-full h-11 mt-1 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Email</label>
                        <input type="email" x-model="newCustomer.email" placeholder="Opsional"
                               class="w-full h-11 mt-1 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold outline-none focus:border-primary">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Alamat</label>
                        <input type="text" x-model="newCustomer.address" placeholder="Opsional"
                               class="w-full h-11 mt-1 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold outline-none focus:border-primary">
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showAddCustomerModal = false" class="flex-1 h-11 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300">Batal</button>
                    <button type="button" @click="saveNewCustomer()" :disabled="savingCustomer"
                            class="flex-1 h-11 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-md shadow-primary/20 disabled:opacity-60">
                        <span x-text="savingCustomer ? 'Menyimpan...' : 'Simpan Pelanggan'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Branch Scope Selection Modal for Owner/SuperAdmin -->
        @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']))
            <div x-show="showBranchScopeModal" 
                 class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/70 backdrop-blur-md p-4"
                 x-transition x-cloak>
                <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-3xl w-full max-w-md p-6 shadow-2xl flex flex-col gap-5 text-left">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-12 h-12 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-2xl">storefront</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Pilih Cabang Transaksi</h3>
                            <p class="text-2xs text-slate-400 font-semibold">Tentukan lokasi outlet cabang sebelum memulai POS kasir.</p>
                        </div>
                    </div>

                    <form action="{{ route('switch-branch') }}" method="POST" class="flex flex-col gap-4">
                        @csrf
                        <div class="flex flex-col gap-1.5">
                            <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Lokasi Outlet / Cabang</label>
                            <select name="branch_id" class="w-full h-12 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-xs font-bold text-slate-800 dark:text-slate-200 outline-none focus:border-primary cursor-pointer">
                                @foreach($branches as $br)
                                    <option value="{{ $br->id }}" {{ session('scoped_branch_id') == $br->id ? 'selected' : '' }}>
                                        {{ $br->name }} — {{ $br->address ?? 'Samarinda' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="button" @click="showBranchScopeModal = false" class="flex-1 h-11 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 cursor-pointer">Lanjutkan</button>
                            <button type="submit" class="flex-1 h-11 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-md shadow-primary/20 cursor-pointer">
                                <span class="material-symbols-outlined text-base">check</span>
                                Terapkan Scope
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
        <div x-show="showConfirmModal" 
             class="fixed inset-0 z-[9999] flex items-[end] sm:items-center justify-center bg-slate-900/60 backdrop-blur-sm p-0 sm:p-4 pb-20 sm:pb-0"
             x-transition x-cloak>
            
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-t-3xl sm:rounded-2xl w-full max-w-lg p-5 sm:p-6 shadow-2xl flex flex-col gap-4"
                 @click.away="showConfirmModal = false">
                 
                 <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
                     <h3 class="text-base font-black text-slate-800 dark:text-slate-100">Konfirmasi Pembayaran</h3>
                     <button @click="showConfirmModal = false" class="text-slate-400 hover:text-slate-600">
                         <span class="material-symbols-outlined">close</span>
                     </button>
                 </div>

                 <div class="space-y-3 max-h-[220px] overflow-y-auto pr-1">
                     <template x-for="(item, index) in cart" :key="index">
                         <div class="flex justify-between text-xs py-1 border-b border-slate-50 dark:border-slate-800/30">
                             <div>
                                 <span class="font-bold text-slate-700 dark:text-slate-300" x-text="item.name"></span>
                                 <span class="text-slate-400 block text-2xs" x-text="item.quantity + ' ' + item.unit + ' × Rp ' + formatNumber(item.price)"></span>
                             </div>
                             <span class="font-mono font-bold text-slate-800 dark:text-slate-200" x-text="'Rp ' + formatNumber(item.price * item.quantity)"></span>
                         </div>
                     </template>
                 </div>

                 <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl space-y-1.5 text-xs">
                     <div class="flex justify-between font-extrabold text-sm">
                         <span>TOTAL TAGIHAN</span>
                         <span class="text-primary" x-text="'Rp ' + formatNumber(total)"></span>
                     </div>
                     <div class="flex justify-between text-slate-600 dark:text-slate-400">
                         <span>Dibayar (<span x-text="paymentMethod.toUpperCase()"></span>)</span>
                         <span class="font-bold" x-text="'Rp ' + formatNumber(paidAmount)"></span>
                     </div>
                     <div class="flex justify-between text-emerald-600 font-bold" x-show="paymentMethod !== 'invoice'">
                         <span>Kembalian</span>
                         <span x-text="'Rp ' + formatNumber(changeAmount)"></span>
                     </div>
                 </div>

                 <div class="flex gap-3 pt-2">
                     <button type="button" @click="showConfirmModal = false" class="flex-1 h-11 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300">Batal</button>
                     <button type="button" @click="submitOrder()" class="flex-1 h-11 bg-primary hover:bg-primary-hover text-white font-bold rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-md shadow-primary/20">
                         <span class="material-symbols-outlined text-base">check_circle</span>
                         Ya, Bayar Sekarang
                     </button>
                 </div>
            </div>
        </div>

        <!-- Post-Checkout Receipt & WhatsApp Modal (Show automatically after order created) -->
        @if(session('last_order_id'))
            <div x-data="{ openReceiptModal: true }" x-show="openReceiptModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition x-cloak>
                <div class="bg-white dark:bg-slate-900 rounded-2xl w-full max-w-md p-6 shadow-2xl flex flex-col gap-4 text-center">
                    <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-3xl">task_alt</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Transaksi Berhasil!</h3>
                        <p class="text-xs text-slate-500 mt-1">Nota #{{ session('last_order_number') }} telah disimpan.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-2.5 pt-2">
                        <a href="{{ route('invoices.receipt', session('last_order_id')) }}" target="_blank" class="btn-touch w-full bg-primary text-white rounded-xl font-bold text-xs flex items-center justify-center gap-2 py-3 shadow-md shadow-primary/20">
                            <span class="material-symbols-outlined text-lg">print</span>
                            Cetak Struk Thermal (58/80mm)
                        </a>

                        <a href="{{ route('invoices.whatsapp', session('last_order_id')) }}" target="_blank" class="btn-touch w-full bg-[#25D366] text-white rounded-xl font-bold text-xs flex items-center justify-center gap-2 py-3 shadow-md shadow-emerald-500/20">
                            <span class="material-symbols-outlined text-lg">chat</span>
                            Kirim Struk via WhatsApp
                        </a>

                        <a href="{{ route('invoices.show', session('last_order_id')) }}" target="_blank" class="btn-touch w-full border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs flex items-center justify-center gap-2 py-2.5">
                            <span class="material-symbols-outlined text-lg">description</span>
                            Lihat Invoice A4
                        </a>
                    </div>

                    <button type="button" @click="openReceiptModal = false" class="text-xs font-bold text-slate-400 hover:text-slate-600 mt-2">
                        Tutup & Buat Order Baru
                    </button>
                </div>
            </div>
        @endif

    </div>

    <script>
        function posApp(initialCustomers) {
            return {
                showBranchScopeModal: {{ auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin']) && !session()->has('scoped_branch_id') ? 'true' : 'false' }},
                cart: [],
                customers: initialCustomers || window.__posCustomers || [],
                selectedCustomer: null,
                customerId: '',
                customerSearch: '',
                customerOpen: false,
                customerPoints: 0,
                customerTier: 'Bronze',
                newCustomer: { name: '', phone: '', email: '', address: '' },
                newCustomerError: '',
                savingCustomer: false,
                showAddCustomerModal: false,
                promoId: '',
                promoType: '',
                promoValue: 0,
                promoMin: 0,
                promoDescription: '',
                manualCouponCode: '',
                couponMessage: '',
                couponError: false,
                pointsUsed: 0,
                pointsDiscount: 0,
                pointExchangeRate: {{ $pointExchangeRate ?? 1 }},
                pointEarnSpendThreshold: {{ $pointEarnSpendThreshold ?? 1000 }},
                pointMinRedeem: {{ $pointMinRedeem ?? 0 }},
                subtotal: 0,
                discount: 0,
                total: 0,
                paymentMethod: 'cash',
                paidAmount: 0,
                changeAmount: 0,
                showConfirmModal: false,
                mobileCartOpen: false,
                activeCategory: 'all',
                serviceSearch: '',

                getItemQuantity(serviceId) {
                    let item = this.cart.find(i => i.service_id === serviceId);
                    return item ? item.quantity : 0;
                },

                decrementServiceQuantity(serviceId) {
                    let index = this.cart.findIndex(i => i.service_id === serviceId);
                    if (index !== -1) {
                        if (this.cart[index].quantity > 1) {
                            this.cart[index].quantity -= 1;
                        } else {
                            this.cart.splice(index, 1);
                        }
                        this.calculateTotals();
                    }
                },

                matchesServiceSearch(name, type) {
                    let q = (this.serviceSearch || '').trim().toLowerCase();
                    if (!q) return true;
                    return name.toLowerCase().includes(q) || type.toLowerCase().includes(q);
                },

                setQuickPaid(val) {
                    if (val === 'exact') {
                        this.paidAmount = this.total;
                    } else {
                        this.paidAmount = Number(val);
                    }
                    this.calculateTotals();
                },

                applyManualCoupon() {
                    let code = (this.manualCouponCode || '').trim().toUpperCase();
                    if (!code) {
                        this.couponMessage = 'Masukkan kode kupon terlebih dahulu.';
                        this.couponError = true;
                        return;
                    }
                    let select = document.getElementById('promo_id');
                    let matchedOpt = Array.from(select.options).find(opt => (opt.getAttribute('data-code') || '').toUpperCase() === code);
                    
                    if (!matchedOpt) {
                        this.couponMessage = `Kode kupon "${code}" tidak ditemukan atau tidak aktif.`;
                        this.couponError = true;
                        return;
                    }

                    let minTx = parseFloat(matchedOpt.getAttribute('data-min')) || 0;
                    if (this.subtotal < minTx) {
                        this.couponMessage = `Kupon "${code}" membutuhkan minimal transaksi Rp ${this.formatNumber(minTx)}. Subtotal saat ini: Rp ${this.formatNumber(this.subtotal)}.`;
                        this.couponError = true;
                        return;
                    }

                    this.promoId = matchedOpt.value;
                    select.value = matchedOpt.value;
                    this.promoType = matchedOpt.getAttribute('data-type');
                    this.promoValue = parseFloat(matchedOpt.getAttribute('data-value')) || 0;
                    this.promoMin = minTx;
                    this.promoDescription = this.promoType === 'percent' ? `${this.promoValue}% (${code})` : `Rp ${this.formatNumber(this.promoValue)} (${code})`;
                    this.couponMessage = `Kupon "${code}" berhasil diterapkan!`;
                    this.couponError = false;
                    this.manualCouponCode = '';
                    this.calculateTotals();
                },

                clearPromo() {
                    this.promoId = '';
                    let select = document.getElementById('promo_id');
                    if (select) select.value = '';
                    this.promoType = '';
                    this.promoValue = 0;
                    this.promoMin = 0;
                    this.promoDescription = '';
                    this.manualCouponCode = '';
                    this.couponMessage = '';
                    this.couponError = false;
                    this.calculateTotals();
                },

                addToCart(id, name, price, unit) {
                    let existing = this.cart.find(item => item.service_id === id);
                    if (existing) {
                        existing.quantity += 1;
                    } else {
                        this.cart.push({ service_id: id, name: name, price: price, unit: unit, quantity: 1, notes: '' });
                    }
                    this.calculateTotals();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: `"${name}" ditambahkan ke keranjang`, type: 'success' } }));
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

                openAddCustomerModal() {
                    this.newCustomer = { name: '', phone: '', email: '', address: '' };
                    this.newCustomerError = '';
                    this.customerOpen = false;
                    this.showAddCustomerModal = true;
                },

                async saveNewCustomer() {
                    if (!this.newCustomer.name || !this.newCustomer.phone) {
                        this.newCustomerError = 'Nama dan No. HP wajib diisi.';
                        return;
                    }
                    this.savingCustomer = true;
                    this.newCustomerError = '';
                    try {
                        const response = await fetch('{{ route("pos.customers.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(this.newCustomer),
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                            this.newCustomerError = firstError || data.message || 'Gagal menyimpan pelanggan.';
                            return;
                        }
                        this.customers.push(data.customer);
                        this.selectCustomer(data.customer);
                        this.showAddCustomerModal = false;
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Pelanggan "' + data.customer.name + '" berhasil ditambahkan', type: 'success' } }));
                    } catch (e) {
                        this.newCustomerError = 'Terjadi kesalahan jaringan.';
                    } finally {
                        this.savingCustomer = false;
                    }
                },

                updatePromoData(e) {
                    let select = e.target;
                    let selected = select.options[select.selectedIndex];
                    if (this.promoId) {
                        this.promoType = selected.getAttribute('data-type');
                        this.promoValue = parseFloat(selected.getAttribute('data-value')) || 0;
                        this.promoMin = parseFloat(selected.getAttribute('data-min')) || 0;
                        this.promoDescription = this.promoType === 'percent' ? `${this.promoValue}%` : `Rp ${this.formatNumber(this.promoValue)}`;
                    } else {
                        this.promoType = '';
                        this.promoValue = 0;
                        this.promoMin = 0;
                        this.promoDescription = '';
                    }
                    this.calculateTotals();
                },

                getTierMultiplier() {
                    const tier = (this.customerTier || 'Bronze').toUpperCase();
                    if (tier === 'PLATINUM') return 2.0;
                    if (tier === 'GOLD') return 1.5;
                    if (tier === 'SILVER') return 1.25;
                    return 1.0;
                },

                estimatedPointsEarned() {
                    if (!this.total || this.total <= 0) return 0;
                    if (this.pointsUsed > 0) return 0; // Transaksi penukaran poin tidak mendapatkan poin baru
                    const threshold = this.pointEarnSpendThreshold > 0 ? this.pointEarnSpendThreshold : 1000;
                    const basePoints = Math.floor(this.total / threshold);
                    return Math.floor(basePoints * this.getTierMultiplier());
                },

                quickRedeemPoints(pts) {
                    let available = Math.min(pts, this.customerPoints);
                    if (this.pointMinRedeem > 0 && available < this.pointMinRedeem) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: `Minimal penukaran poin adalah ${this.pointMinRedeem} poin.`, type: 'warning' } }));
                        return;
                    }
                    let remainingBill = Math.max(0, this.subtotal - this.discount);
                    let maxPointsAllowed = this.pointExchangeRate > 0 ? Math.floor(remainingBill / this.pointExchangeRate) : remainingBill;
                    this.pointsUsed = Math.min(available, maxPointsAllowed);
                    this.calculateTotals();
                },

                maxRedeemPoints() {
                    if (this.pointMinRedeem > 0 && this.customerPoints < this.pointMinRedeem) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: `Minimal penukaran poin adalah ${this.pointMinRedeem} poin. Saldo pelanggan saat ini: ${this.customerPoints} poin.`, type: 'warning' } }));
                        return;
                    }
                    let remainingBill = Math.max(0, this.subtotal - this.discount);
                    let maxPointsAllowed = this.pointExchangeRate > 0 ? Math.floor(remainingBill / this.pointExchangeRate) : remainingBill;
                    this.pointsUsed = Math.min(this.customerPoints, maxPointsAllowed);
                    this.calculateTotals();
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

                    if (this.pointsUsed > this.customerPoints) this.pointsUsed = this.customerPoints;
                    
                    let calcDiscount = this.pointsUsed * (this.pointExchangeRate > 0 ? this.pointExchangeRate : 1);
                    let maxPointsDisc = Math.max(0, this.subtotal - this.discount);
                    if (calcDiscount > maxPointsDisc) {
                        calcDiscount = maxPointsDisc;
                    }
                    this.pointsDiscount = calcDiscount;

                    this.total = Math.max(0, this.subtotal - this.discount - this.pointsDiscount);

                    if (this.paymentMethod === 'cash' || this.paymentMethod === 'transfer') {
                        this.changeAmount = Math.max(0, this.paidAmount - this.total);
                    } else {
                        this.changeAmount = 0;
                    }
                },

                confirmCheckout() {
                    if (!this.customerId) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Pilih pelanggan terlebih dahulu atau daftar pelanggan baru.', type: 'warning' } }));
                        return;
                    }
                    if (this.cart.length === 0) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Keranjang belanja masih kosong!', type: 'warning' } }));
                        return;
                    }

                    if ((this.paymentMethod === 'cash' || this.paymentMethod === 'transfer') && this.paidAmount < this.total) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: `Jumlah bayar kurang dari total tagihan (Rp ${this.formatNumber(this.total)})`, type: 'error' } }));
                        return;
                    }

                    this.showConfirmModal = true;
                },

                submitOrder() {
                    this.showConfirmModal = false;
                    const desktopForm = document.getElementById('pos-form');
                    if (desktopForm && desktopForm.offsetParent !== null) {
                        desktopForm.submit();
                    } else {
                        const mobileForm = document.getElementById('pos-form-mobile');
                        if (mobileForm) {
                            mobileForm.submit();
                        } else if (desktopForm) {
                            desktopForm.submit();
                        }
                    }
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('id-ID').format(num || 0);
                }
            };
        }
    </script>
</x-app-layout>
