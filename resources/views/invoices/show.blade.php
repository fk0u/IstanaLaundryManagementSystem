<x-app-layout>
    <div class="max-w-3xl mx-auto">
        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
            <div>
                <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Invoice #{{ $order->order_number }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ $order->created_at->format('d F Y, H:i') }} WIB</p>
            </div>
            <div class="flex gap-2 flex-wrap no-print">
                <a href="{{ route('invoices.receipt', $order) }}" class="btn-touch px-4 py-2 bg-primary hover:bg-primary-hover text-white rounded-xl text-xs font-bold gap-1.5 transition-all active:scale-95 shadow-sm">
                    <span class="material-symbols-outlined text-base">receipt_long</span>
                    Cetak Struk
                </a>
                @if($order->customer?->phone)
                    <a href="{{ route('invoices.whatsapp', $order) }}" target="_blank" class="btn-touch px-4 py-2 bg-[#25D366] hover:bg-[#1DA851] text-white rounded-xl text-xs font-bold gap-1.5 transition-all active:scale-95 shadow-sm inline-flex items-center">
                        <span class="material-symbols-outlined text-base">chat</span>
                        WhatsApp
                    </a>
                @endif
                <button onclick="window.print()" class="btn-touch px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold gap-1.5 hover:bg-slate-50 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-base">print</span>
                    Print
                </button>
                <a href="{{ route('pos.index') }}" class="btn-touch px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold gap-1.5 hover:bg-slate-50 transition-all active:scale-95">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    POS
                </a>
            </div>
        </div>

        <!-- Invoice Card -->
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden print:shadow-none print:border-0">
            <!-- Invoice Header -->
            <div class="p-5 md:p-8 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                <div class="flex flex-col sm:flex-row justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-black text-primary tracking-tighter">ISTANA LAUNDRY</h2>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            {{ $order->branch?->name ?? 'Cabang Utama' }}<br>
                            {{ $order->branch?->address ?? '' }}<br>
                            {{ $order->branch?->phone ? 'Telp: ' . $order->branch->phone : '' }}
                        </p>
                    </div>
                    <div class="text-left sm:text-right">
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider
                            {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400' }}">
                            <span class="material-symbols-outlined text-sm">{{ $order->payment_status === 'paid' ? 'check_circle' : 'schedule' }}</span>
                            {{ $order->payment_status === 'paid' ? 'LUNAS' : strtoupper($order->payment_status) }}
                        </span>
                        <p class="text-xs text-slate-400 mt-2">
                            <strong>No. Invoice:</strong> 
                            <a href="{{ route('track', ['order_number' => $order->order_number]) }}" target="_blank" class="text-primary font-bold hover:underline font-mono">
                                {{ $order->order_number }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Customer & Order Info -->
            <div class="p-5 md:p-8 grid grid-cols-1 sm:grid-cols-2 gap-6 border-b border-slate-100 dark:border-slate-800">
                <div>
                    <h4 class="text-2xs font-bold text-slate-400 uppercase tracking-wider mb-2">Informasi Pelanggan</h4>
                    @if($order->customer)
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $order->customer->name }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $order->customer->phone }}</p>
                        @if($order->customer->email)
                            <p class="text-xs text-slate-500">{{ $order->customer->email }}</p>
                        @endif
                        @if($order->customer->address)
                            <p class="text-xs text-slate-500 mt-1">{{ $order->customer->address }}</p>
                        @endif
                    @else
                        <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Pelanggan Umum (Walk-In)</p>
                    @endif
                </div>
                <div>
                    <h4 class="text-2xs font-bold text-slate-400 uppercase tracking-wider mb-2">Detail Transaksi</h4>
                    <div class="space-y-1 text-xs">
                        <p><span class="text-slate-400">Tanggal:</span> <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $order->created_at->format('d M Y, H:i') }}</span></p>
                        <p><span class="text-slate-400">Kasir:</span> <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $order->cashier?->name ?? '-' }}</span></p>
                        <p><span class="text-slate-400">Metode:</span> <span class="font-semibold text-slate-700 dark:text-slate-300 uppercase">{{ $order->payment_method }}</span></p>
                        <p><span class="text-slate-400">Produksi:</span> <span class="font-bold text-primary">{{ $order->production_status }}</span></p>
                        @if($order->estimated_done_at)
                            <p><span class="text-slate-400">Est. Selesai:</span> <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $order->estimated_done_at->format('d M Y') }}</span></p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="p-5 md:p-8">
                <h4 class="text-2xs font-bold text-slate-400 uppercase tracking-wider mb-4">Detail Layanan</h4>
                
                {{-- Desktop table --}}
                <div class="hidden sm:block">
                    <table class="w-full text-xs border-collapse">
                        <thead>
                            <tr class="border-b-2 border-slate-200 dark:border-slate-700 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-2 text-left">Layanan</th>
                                <th class="py-2 text-center">Qty</th>
                                <th class="py-2 text-right">Harga Satuan</th>
                                <th class="py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="py-3 font-semibold text-slate-800 dark:text-slate-200">
                                        {{ $item->service?->name ?? 'Layanan' }}
                                        @if($item->notes)
                                            <span class="block text-2xs text-slate-400 mt-0.5">{{ $item->notes }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center text-slate-600 dark:text-slate-400">
                                        {{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }} {{ $item->unit ?? 'kg' }}
                                    </td>
                                    <td class="py-3 text-right text-slate-600 dark:text-slate-400 font-mono">
                                        Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 text-right font-bold text-slate-800 dark:text-slate-200 font-mono">
                                        Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile card list --}}
                <div class="sm:hidden space-y-3">
                    @foreach($order->items as $item)
                        <div class="bg-slate-50 dark:bg-slate-800/30 rounded-xl p-3">
                            <div class="flex justify-between items-start">
                                <div class="min-w-0 flex-1">
                                    <span class="font-bold text-xs text-slate-800 dark:text-slate-200">{{ $item->service?->name ?? 'Layanan' }}</span>
                                    <span class="block text-2xs text-slate-500 mt-0.5">
                                        {{ rtrim(rtrim(number_format($item->quantity, 2, ',', '.'), '0'), ',') }} {{ $item->unit ?? 'kg' }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                    </span>
                                </div>
                                <span class="font-bold text-xs text-slate-800 dark:text-slate-200 font-mono shrink-0 ml-3">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Totals Section -->
                <div class="mt-6 border-t-2 border-slate-200 dark:border-slate-700 pt-4 max-w-xs ml-auto">
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300 font-mono">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-red-600">
                                <span>Diskon{{ $order->promo ? ' (' . $order->promo->name . ')' : '' }}</span>
                                <span class="font-semibold font-mono">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($order->points_used > 0)
                            <div class="flex justify-between text-red-600">
                                <span>Poin Terpakai</span>
                                <span class="font-semibold font-mono">-Rp {{ number_format($order->points_used, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        @if($order->tax_amount > 0)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Pajak</span>
                                <span class="font-semibold text-slate-700 dark:text-slate-300 font-mono">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between mt-3 pt-3 border-t-2 border-slate-900 dark:border-slate-200">
                        <span class="text-base font-extrabold text-slate-900 dark:text-white">TOTAL</span>
                        <span class="text-base font-extrabold text-slate-900 dark:text-white font-mono">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>

                    <div class="mt-3 space-y-1.5 text-xs">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Dibayar</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300 font-mono">Rp {{ number_format($order->paid_amount, 0, ',', '.') }}</span>
                        </div>
                        @if($order->change_amount > 0)
                            <div class="flex justify-between text-emerald-600">
                                <span class="font-semibold">Kembalian</span>
                                <span class="font-bold font-mono">Rp {{ number_format($order->change_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoice Footer -->
            <div class="p-5 md:p-8 bg-slate-50/50 dark:bg-slate-800/20 border-t border-slate-100 dark:border-slate-800 text-center">
                <p class="text-xs font-bold text-slate-600 dark:text-slate-400">Terima kasih atas kepercayaan Anda!</p>
                <p class="text-2xs text-slate-400 mt-1">
                    Istana Premium Laundry Service — Samarinda<br>
                    Dokumen ini sah sebagai bukti transaksi.
                </p>
            </div>
        </div>

        @if($order->notes)
            <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/30 rounded-xl">
                <span class="text-2xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Catatan</span>
                <p class="text-xs text-amber-800 dark:text-amber-300 mt-1">{{ $order->notes }}</p>
            </div>
        @endif
    </div>
</x-app-layout>
