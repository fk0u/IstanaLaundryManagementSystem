<x-app-layout>
    <div x-data="posApp()" class="min-h-[calc(100vh-100px)] flex flex-col gap-6">
        
        <x-page-header title="Point of Sale (POS)" :breadcrumbs="['POS' => '/pos']" />

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
                    <!-- Customer Selection -->
                    <x-card title="Pilih Pelanggan" :compact="true">
                        <div class="flex flex-col gap-3">
                            <label for="customer_id" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Nama Pelanggan</label>
                            <select id="customer_id" x-model="customerId" @change="updateCustomerData"
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all cursor-pointer">
                                <option value="">-- Pelanggan Umum (Walk-In) --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            data-points="{{ $customer->loyalty_points }}"
                                            data-tier="{{ $customer->loyalty_tier }}">
                                        {{ $customer->name }} (Poin: {{ $customer->loyalty_points }} • {{ $customer->loyalty_tier }})
                                    </option>
                                @endforeach
                            </select>
                            <div x-show="customerId" class="flex gap-3 items-center bg-orange-50/60 dark:bg-slate-800/40 p-2.5 rounded-xl border border-orange-100 dark:border-slate-800" x-cloak>
                                <span class="material-symbols-outlined text-primary text-xl">workspace_premium</span>
                                <div>
                                    <span class="block text-2xs text-slate-400 font-bold uppercase tracking-wider">Tier</span>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200" x-text="customerTier">Bronze</span>
                                </div>
                                <div class="ml-auto text-right">
                                    <span class="block text-2xs text-slate-400 font-bold uppercase tracking-wider">Poin</span>
                                    <span class="text-xs font-bold text-primary" x-text="customerPoints">0</span>
                                </div>
                            </div>
                        </div>
                    </x-card>

                    <!-- Promotion Selection -->
                    <x-card title="Gunakan Promo" :compact="true">
                        <div class="flex flex-col gap-3">
                            <label for="promo_id" class="text-2xs font-bold text-slate-400 uppercase tracking-wider">Kupon / Promo Aktif</label>
                            <select id="promo_id" x-model="promoId" @change="updatePromoData"
                                    class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all cursor-pointer">
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
                            <div x-show="promoId" class="flex gap-3 items-center bg-emerald-50/60 dark:bg-emerald-950/20 p-2.5 rounded-xl border border-emerald-100 dark:border-emerald-900/30" x-cloak>
                                <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-xl">local_activity</span>
                                <div>
                                    <span class="block text-2xs text-slate-400 font-bold uppercase tracking-wider">Nilai Promo</span>
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-200" x-text="promoDescription">Promo</span>
                                </div>
                            </div>
                        </div>
                    </x-card>
                </div>

                <!-- Services Grid -->
                <x-card title="Layanan Cuci">
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach ($services as $service)
                            <div class="border border-slate-100 dark:border-slate-800 rounded-xl p-4 hover:border-primary/50 dark:hover:border-orange-500/50 transition-all flex flex-col justify-between bg-white dark:bg-slate-900 shadow-sm relative group">
                                <div>
                                    <div class="flex justify-between items-start mb-2 gap-2">
                                        <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200 group-hover:text-primary dark:group-hover:text-orange-400 transition-colors">
                                            {{ $service->name }}
                                        </h4>
                                        <x-badge type="primary">{{ $service->type }}</x-badge>
                                    </div>
                                    <p class="text-2xs text-slate-400 dark:text-slate-500 mb-3 line-clamp-2">
                                        {{ $service->description ?? 'Layanan laundry profesional.' }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between mt-2 pt-2.5 border-t border-slate-50 dark:border-slate-800/30">
                                    <div>
                                        <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider">Per {{ $service->unit }}</span>
                                        <span class="text-sm font-extrabold text-primary">Rp {{ number_format($service->price, 0, ',', '.') }}</span>
                                    </div>
                                    <button type="button" @click="addToCart({{ $service->id }}, '{{ addslashes($service->name) }}', {{ $service->price }}, '{{ $service->unit }}')"
                                            class="w-9 h-9 rounded-xl bg-orange-50 hover:bg-primary hover:text-white dark:bg-slate-800 dark:text-orange-400 dark:hover:bg-orange-500/30 text-primary flex items-center justify-center cursor-pointer transition-all active:scale-95 shadow-sm">
                                        <span class="material-symbols-outlined text-lg">add_shopping_cart</span>
                                    </button>
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

                            <div class="flex flex-col gap-1.5 pt-2 border-t border-slate-50 dark:border-slate-800/30" x-show="customerId && customerPoints > 0" x-cloak>
                                <div class="flex justify-between items-center text-xs">
                                    <span class="text-slate-400 font-semibold">Tukarkan Poin</span>
                                    <div class="flex items-center gap-1">
                                        <input type="number" name="points_used" x-model.number="pointsUsed" min="0" :max="customerPoints" @input="calculateTotals()"
                                               class="w-16 h-6 text-center rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 text-2xs font-bold text-primary focus:border-primary outline-none">
                                        <span class="text-2xs text-slate-400 font-bold">Poin</span>
                                    </div>
                                </div>
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
                <form action="{{ route('pos.store') }}" method="POST" @submit.prevent="confirmCheckout(); mobileCartOpen = false;">
                    @csrf
                    <input type="hidden" name="customer_id" x-model="customerId">
                    <input type="hidden" name="promo_id" x-model="promoId">

                    <div class="space-y-3 max-h-[250px] overflow-y-auto pr-1">
                        <template x-if="cart.length === 0">
                            <p class="text-center py-6 text-xs text-slate-400">Keranjang masih kosong.</p>
                        </template>
                        <template x-for="(item, index) in cart" :key="index">
                            <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                                <div class="flex-1 min-w-0">
                                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200 block truncate" x-text="item.name"></span>
                                    <span class="text-2xs text-slate-400" x-text="'Rp ' + formatNumber(item.price)"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <input type="number" x-model.number="item.quantity" min="0.01" step="0.01" @input="calculateTotals()" class="w-14 h-7 text-center rounded-lg border text-xs font-bold">
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

        <!-- Checkout Confirmation Modal -->
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
                            <span class="text-base">📱</span>
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
        function posApp() {
            return {
                cart: [],
                customerId: '',
                customerPoints: 0,
                customerTier: 'Bronze',
                promoId: '',
                promoType: '',
                promoValue: 0,
                promoMin: 0,
                promoDescription: '',
                pointsUsed: 0,
                subtotal: 0,
                discount: 0,
                total: 0,
                paymentMethod: 'cash',
                paidAmount: 0,
                changeAmount: 0,
                showConfirmModal: false,
                mobileCartOpen: false,

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

                updateCustomerData(e) {
                    let select = e.target;
                    let selected = select.options[select.selectedIndex];
                    if (this.customerId) {
                        this.customerPoints = parseInt(selected.getAttribute('data-points')) || 0;
                        this.customerTier = selected.getAttribute('data-tier') || 'Bronze';
                    } else {
                        this.customerPoints = 0;
                        this.customerTier = 'Bronze';
                        this.pointsUsed = 0;
                    }
                    this.calculateTotals();
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
                    let maxPointsDisc = this.subtotal - this.discount;
                    if (this.pointsUsed > maxPointsDisc) this.pointsUsed = Math.max(0, maxPointsDisc);

                    this.total = Math.max(0, this.subtotal - this.discount - this.pointsUsed);

                    if (this.paymentMethod === 'cash' || this.paymentMethod === 'transfer') {
                        this.changeAmount = Math.max(0, this.paidAmount - this.total);
                    } else {
                        this.changeAmount = 0;
                    }
                },

                confirmCheckout() {
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
                    document.getElementById('pos-form').submit();
                },

                formatNumber(num) {
                    return new Intl.NumberFormat('id-ID').format(num || 0);
                }
            };
        }
    </script>
</x-app-layout>
