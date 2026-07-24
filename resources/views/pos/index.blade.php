<x-app-layout>
    <div x-data="posApp()" class="grid grid-cols-1 lg:grid-cols-12 gap-8 h-[calc(100vh-120px)] overflow-hidden">
        
        <!-- Left Side: Services & Customer Selection (8 cols) -->
        <div class="lg:col-span-8 flex flex-col h-full overflow-y-auto pr-2">
            
            <x-page-header title="Point of Sale (POS)" :breadcrumbs="['POS' => '/pos']" class="mb-6" />

            <!-- Alert Flash Messages -->
            @if (session('success'))
                <x-alert type="success" :message="session('success')" class="mb-6" />
            @endif
            @if ($errors->any())
                <x-alert type="danger" message="Terjadi kesalahan validasi. Periksa inputan Anda." class="mb-6" />
            @endif

            <!-- Customer & Promo Selectors -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Customer Selection -->
                <x-card title="Pilih Pelanggan">
                    <div class="flex flex-col gap-4">
                        <label for="customer_id" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Nama Pelanggan</label>
                        <select id="customer_id" x-model="customerId" @change="updateCustomerData"
                                class="w-full h-11 px-3 rounded border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                            <option value="">-- Pelanggan Umum --</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}" 
                                        data-points="{{ $customer->loyalty_points }}"
                                        data-tier="{{ $customer->loyalty_tier }}">
                                    {{ $customer->name }} (Poin: {{ $customer->loyalty_points }} • {{ $customer->loyalty_tier }})
                                </option>
                            @endforeach
                        </select>
                        <div x-show="customerId" class="flex gap-4 items-center bg-orange-50/50 dark:bg-slate-800/30 p-3 rounded-lg border border-orange-100/50 dark:border-slate-800" x-cloak>
                            <span class="material-symbols-outlined text-primary text-2xl">workspace_premium</span>
                            <div>
                                <span class="block text-xs text-slate-400 font-bold uppercase tracking-wider">Tier Loyalitas</span>
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-200" x-text="customerTier">Bronze</span>
                            </div>
                            <div class="ml-auto text-right">
                                <span class="block text-xs text-slate-400 font-bold uppercase tracking-wider">Saldo Poin</span>
                                <span class="text-sm font-bold text-primary" x-text="customerPoints">0</span>
                            </div>
                        </div>
                    </div>
                </x-card>

                <!-- Promotion Selection -->
                <x-card title="Gunakan Promo">
                    <div class="flex flex-col gap-4">
                        <label for="promo_id" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kupon / Promo Aktif</label>
                        <select id="promo_id" x-model="promoId" @change="updatePromoData"
                                class="w-full h-11 px-3 rounded border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                            <option value="">-- Tanpa Promo --</option>
                            @foreach ($promotions as $promo)
                                <option value="{{ $promo->id }}" 
                                        data-type="{{ $promo->type }}" 
                                        data-value="{{ $promo->value }}"
                                        data-min="{{ $promo->min_transaction }}">
                                    {{ $promo->name }} (Min: Rp {{ number_format($promo->min_transaction, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                        <div x-show="promoId" class="flex gap-4 items-center bg-emerald-50/50 dark:bg-emerald-950/10 p-3 rounded-lg border border-emerald-100/50 dark:border-emerald-900/20" x-cloak>
                            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-2xl">local_activity</span>
                            <div>
                                <span class="block text-xs text-slate-400 font-bold uppercase tracking-wider">Nilai Promo</span>
                                <span class="text-sm font-bold text-slate-700 dark:text-slate-200" x-text="promoDescription">Bronze</span>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- Services Grid -->
            <x-card title="Layanan Cuci">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($services as $service)
                        <div class="border border-slate-100 dark:border-slate-800 rounded-xl p-5 hover:border-primary/50 dark:hover:border-orange-500/50 transition-all flex flex-col justify-between bg-white dark:bg-slate-900 shadow-sm relative group">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <h4 class="font-bold text-slate-800 dark:text-slate-200 group-hover:text-primary dark:group-hover:text-orange-400 transition-colors">
                                        {{ $service->name }}
                                    </h4>
                                    <x-badge type="primary">{{ $service->type }}</x-badge>
                                </div>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mb-4 line-clamp-2">
                                    {{ $service->description ?? 'Tidak ada deskripsi layanan.' }}
                                </p>
                            </div>
                            <div class="flex items-center justify-between mt-2 pt-3 border-t border-slate-50 dark:border-slate-800/30">
                                <div>
                                    <span class="block text-[10px] text-slate-400 font-bold uppercase tracking-wider">Harga per {{ $service->unit }}</span>
                                    <span class="text-base font-bold text-primary">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                </div>
                                <button type="button" @click="addToCart({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->price }}, '{{ $service->unit }}')"
                                        class="w-10 h-10 rounded-lg bg-orange-50 hover:bg-primary hover:text-white dark:bg-slate-800 dark:text-orange-400 dark:hover:bg-orange-500/20 text-primary flex items-center justify-center cursor-pointer transition-all active:scale-95 shadow-sm">
                                    <span class="material-symbols-outlined text-xl">add_shopping_cart</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-card>

        </div>

        <!-- Right Side: Cart Panel (4 cols) -->
        <div class="lg:col-span-4 h-full flex flex-col">
            <x-card title="Keranjang Belanja" class="flex-1 flex flex-col h-full justify-between">
                
                <form action="{{ route('pos.store') }}" method="POST" class="flex flex-col h-full justify-between">
                    @csrf
                    
                    <input type="hidden" name="customer_id" x-model="customerId">
                    <input type="hidden" name="promo_id" x-model="promoId">

                    <!-- Cart Item List -->
                    <div class="flex-1 overflow-y-auto pr-2 divide-y divide-slate-100 dark:divide-slate-800 max-h-[300px]">
                        <template x-if="cart.length === 0">
                            <div class="py-12 text-center">
                                <span class="material-symbols-outlined text-slate-300 dark:text-slate-700 text-5xl mb-3">shopping_basket</span>
                                <p class="text-sm text-slate-400 dark:text-slate-500">Keranjang kosong. Pilih layanan di sebelah kiri.</p>
                            </div>
                        </template>

                        <template x-for="(item, index) in cart" :key="index">
                            <div class="py-4 flex items-center justify-between gap-4">
                                <div class="flex-1">
                                    <span class="font-bold text-sm text-slate-800 dark:text-slate-200 block" x-text="item.name"></span>
                                    <span class="text-xs text-slate-400" x-text="'Rp ' + formatNumber(item.price) + ' / ' + item.unit"></span>
                                    <input type="hidden" :name="'items[' + index + '][service_id]'" :value="item.service_id">
                                    
                                    <!-- Item Notes -->
                                    <input type="text" :name="'items[' + index + '][notes]'" placeholder="Catatan item..."
                                           class="mt-1 w-full h-8 px-2 rounded border border-slate-150 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900 text-xs text-slate-600 dark:text-slate-400 focus:border-primary outline-none transition-all placeholder:text-slate-300">
                                </div>
                                <div class="flex items-center gap-2">
                                    <!-- Qty input -->
                                    <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="0.01" step="0.01" @input="calculateTotals()"
                                           class="w-16 h-8 text-center rounded border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-950 text-xs text-slate-800 dark:text-slate-200 font-bold focus:border-primary outline-none">
                                    <button type="button" @click="removeFromCart(index)" class="p-1 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded cursor-pointer">
                                        <span class="material-symbols-outlined text-base">delete</span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Summary & Calculation Area -->
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-4 mt-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Subtotal</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="'Rp ' + formatNumber(subtotal)">Rp 0</span>
                        </div>

                        <!-- Promo Discount -->
                        <div class="flex justify-between text-sm text-emerald-600" x-show="discount > 0" x-cloak>
                            <span>Potongan Promo</span>
                            <span class="font-bold" x-text="'- Rp ' + formatNumber(discount)">- Rp 0</span>
                        </div>

                        <!-- Loyalty Points Redemption -->
                        <div class="flex flex-col gap-2 pt-2 border-t border-slate-50 dark:border-slate-800/30" x-show="customerId && customerPoints > 0" x-cloak>
                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-400 font-semibold uppercase tracking-wider">Tukarkan Poin</span>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="points_used" x-model.number="pointsUsed" min="0" :max="customerPoints" @input="calculateTotals()"
                                           class="w-20 h-7 text-center rounded border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-xs font-bold text-primary focus:border-primary outline-none">
                                    <span class="text-slate-400 font-bold">Poin</span>
                                </div>
                            </div>
                            <div class="flex justify-between text-sm text-orange-600" x-show="pointsUsed > 0">
                                <span class="text-xs">Potongan Poin (1 Poin = Rp 1)</span>
                                <span class="font-bold" x-text="'- Rp ' + formatNumber(pointsUsed)">- Rp 0</span>
                            </div>
                        </div>

                        <div class="flex justify-between text-lg font-black pt-3 border-t border-slate-100 dark:border-slate-850">
                            <span class="text-slate-800 dark:text-slate-200">Total Tagihan</span>
                            <span class="text-primary" x-text="'Rp ' + formatNumber(total)">Rp 0</span>
                        </div>

                        <!-- Payment Fields -->
                        <div class="grid grid-cols-2 gap-4 pt-3">
                            <div class="flex flex-col gap-1.5">
                                <label for="payment_method" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Metode Bayar</label>
                                <select id="payment_method" name="payment_method" x-model="paymentMethod"
                                        class="w-full h-10 px-2 rounded border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-slate-200 outline-none focus:border-primary">
                                    <option value="cash">Cash / Tunai</option>
                                    <option value="transfer">Transfer Bank</option>
                                    <option value="invoice">Invoice / Piutang</option>
                                </select>
                            </div>
                            <div class="flex flex-col gap-1.5">
                                <label for="paid_amount" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jumlah Bayar</label>
                                <input type="number" id="paid_amount" name="paid_amount" x-model.number="paidAmount" @input="calculateTotals()"
                                       class="w-full h-10 px-3 rounded border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 outline-none focus:border-primary">
                            </div>
                        </div>

                        <!-- Change Amount -->
                        <div class="flex justify-between text-sm pt-2" x-show="paymentMethod !== 'invoice'">
                            <span class="text-slate-400">Kembalian</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200" x-text="'Rp ' + formatNumber(changeAmount)">Rp 0</span>
                        </div>

                        <!-- Notes -->
                        <div class="flex flex-col gap-1.5 pt-2">
                            <label for="notes" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Catatan Nota (Opsional)</label>
                            <textarea id="notes" name="notes" placeholder="Tulis catatan transaksi..." rows="2"
                                      class="w-full p-3 rounded border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs text-slate-800 dark:text-slate-200 focus:border-primary outline-none transition-all"></textarea>
                        </div>

                        <!-- Submit Checkout -->
                        <button type="submit" :disabled="cart.length === 0"
                                class="w-full h-12 mt-4 bg-primary hover:bg-primary-hover disabled:bg-slate-200 dark:disabled:bg-slate-850 disabled:text-slate-400 dark:disabled:text-slate-600 disabled:cursor-not-allowed text-white font-bold rounded-xl text-sm flex items-center justify-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-sm">
                            <span class="material-symbols-outlined">payments</span>
                            Simpan Transaksi / Cetak Nota
                        </button>
                    </div>

                </form>

            </x-card>
        </div>

    </div>

    <!-- Alpine.js POS Logic -->
    <script>
        function posApp() {
            return {
                cart: [],
                customerId: '',
                customerPoints: 0,
                customerTier: '',
                promoId: '',
                promoType: '',
                promoValue: 0,
                promoMin: 0,
                promoDescription: '',
                pointsUsed: 0,
                paymentMethod: 'cash',
                paidAmount: 0,
                subtotal: 0,
                discount: 0,
                total: 0,
                changeAmount: 0,

                addToCart(serviceId, name, price, unit) {
                    let exists = this.cart.find(item => item.service_id === serviceId);
                    if (exists) {
                        exists.quantity += 1;
                    } else {
                        this.cart.push({
                            service_id: serviceId,
                            name: name,
                            price: price,
                            unit: unit,
                            quantity: 1,
                            notes: ''
                        });
                    }
                    this.calculateTotals();
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                    this.calculateTotals();
                },

                updateCustomerData(e) {
                    let option = e.target.options[e.target.selectedIndex];
                    if (option && option.value !== "") {
                        this.customerPoints = parseInt(option.getAttribute('data-points')) || 0;
                        this.customerTier = option.getAttribute('data-tier') || 'Bronze';
                    } else {
                        this.customerId = '';
                        this.customerPoints = 0;
                        this.customerTier = '';
                        this.pointsUsed = 0;
                    }
                    this.calculateTotals();
                },

                updatePromoData(e) {
                    let option = e.target.options[e.target.selectedIndex];
                    if (option && option.value !== "") {
                        this.promoType = option.getAttribute('data-type') || '';
                        this.promoValue = parseFloat(option.getAttribute('data-value')) || 0;
                        this.promoMin = parseFloat(option.getAttribute('data-min')) || 0;
                        
                        if (this.promoType === 'percent') {
                            this.promoDescription = 'Diskon ' + this.promoValue + '%';
                        } else {
                            this.promoDescription = 'Potongan Rp ' + this.formatNumber(this.promoValue);
                        }
                    } else {
                        this.promoId = '';
                        this.promoType = '';
                        this.promoValue = 0;
                        this.promoMin = 0;
                        this.promoDescription = '';
                    }
                    this.calculateTotals();
                },

                calculateTotals() {
                    // 1. Calculate subtotal
                    this.subtotal = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

                    // 2. Promo discount
                    this.discount = 0;
                    if (this.promoId && this.subtotal >= this.promoMin) {
                        if (this.promoType === 'percent') {
                            this.discount = this.subtotal * (this.promoValue / 100);
                        } else if (this.promoType === 'nominal') {
                            this.discount = this.promoValue;
                        }
                        this.discount = Math.min(this.discount, this.subtotal);
                    }

                    // 3. Loyalty Points discount
                    if (!this.customerId) {
                        this.pointsUsed = 0;
                    } else {
                        this.pointsUsed = Math.min(this.pointsUsed, this.customerPoints);
                        let remainingAfterPromo = this.subtotal - this.discount;
                        this.pointsUsed = Math.min(this.pointsUsed, remainingAfterPromo);
                    }

                    // 4. Net Total
                    this.total = Math.max(0, this.subtotal - this.discount - this.pointsUsed);

                    // 5. Change amount
                    if (this.paymentMethod === 'invoice') {
                        this.changeAmount = 0;
                    } else {
                        this.changeAmount = Math.max(0, this.paidAmount - this.total);
                    }
                },

                formatNumber(val) {
                    return new Intl.NumberFormat('id-ID').format(Math.round(val));
                }
            }
        }
    </script>
</x-app-layout>
