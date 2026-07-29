<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6">
        <x-page-header title="Semua Transaksi" :breadcrumbs="['Transaksi' => route('orders.index')]" />

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('orders.index') }}"
              class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-3 md:p-4 flex flex-wrap items-end gap-3">

            {{-- Search --}}
            <div class="flex-1 min-w-[160px]">
                <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Cari Nota / Pelanggan</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-base pointer-events-none">search</span>
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Cari nomor nota, nama..."
                           class="w-full pl-8 pr-3 h-9 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 font-medium focus:outline-none focus:border-primary">
                </div>
            </div>

            {{-- Branch Filter (global users only) --}}
            @if($isGlobalUser)
                <div class="min-w-[140px]">
                    <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Cabang</label>
                    <select name="branch_id" class="h-9 w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Status Produksi Filter --}}
            <div class="min-w-[130px]">
                <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Status Produksi</label>
                <select name="status" class="h-9 w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold">
                    <option value="">Semua Status</option>
                    @foreach($productionStatuses as $ps)
                        <option value="{{ $ps }}" {{ $status === $ps ? 'selected' : '' }}>{{ $ps }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Status Bayar Filter --}}
            <div class="min-w-[120px]">
                <label class="text-2xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Status Bayar</label>
                <select name="pay_status" class="h-9 w-full text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 font-semibold">
                    <option value="">Semua</option>
                    <option value="paid"     {{ $payStatus === 'paid'     ? 'selected' : '' }}>Lunas</option>
                    <option value="pending"  {{ $payStatus === 'pending'  ? 'selected' : '' }}>Pending</option>
                    <option value="refunded" {{ $payStatus === 'refunded' ? 'selected' : '' }}>Refund</option>
                </select>
            </div>

            <button type="submit" class="h-9 px-4 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl shrink-0">
                <span class="material-symbols-outlined text-base align-middle">filter_alt</span>
                Filter
            </button>
            @if($search || $status || $payStatus || $branchId)
                <a href="{{ route('orders.index') }}" class="h-9 px-4 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 text-xs font-bold rounded-xl flex items-center gap-1 hover:bg-slate-50 dark:hover:bg-slate-800">
                    <span class="material-symbols-outlined text-base">close</span>Reset
                </a>
            @endif
        </form>

        {{-- Orders Table --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider bg-slate-50/50 dark:bg-slate-800/30">
                            <th class="py-3 px-4">Nomor Nota</th>
                            <th class="py-3 px-4">Pelanggan</th>
                            @if($isGlobalUser)
                                <th class="py-3 px-4">Cabang</th>
                            @endif
                            <th class="py-3 px-4">Kasir</th>
                            <th class="py-3 px-4">Metode Bayar</th>
                            <th class="py-3 px-4">Status Bayar</th>
                            <th class="py-3 px-4">Status Produksi</th>
                            <th class="py-3 px-4 text-right">Total</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
                        @forelse($orders as $order)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800 dark:text-slate-200 text-[11px]">
                                    {{ $order->order_number }}
                                    <div class="text-2xs text-slate-400 font-sans font-normal">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">
                                    {{ $order->customer?->name ?? 'Walk-In' }}
                                    @if($order->customer?->member_code)
                                        <div class="text-2xs text-slate-400 font-mono">{{ $order->customer->member_code }}</div>
                                    @endif
                                </td>
                                @if($isGlobalUser)
                                    <td class="py-3 px-4 text-slate-500 text-[11px]">{{ $order->branch?->name }}</td>
                                @endif
                                <td class="py-3 px-4 text-slate-500 text-[11px]">{{ $order->cashier?->name ?? '-' }}</td>
                                <td class="py-3 px-4 uppercase text-[10px] font-bold text-slate-500">{{ $order->payment_method }}</td>
                                <td class="py-3 px-4">
                                    @if($order->payment_status === 'paid')
                                        <x-badge type="success">Lunas</x-badge>
                                    @elseif($order->payment_status === 'refunded')
                                        <x-badge type="danger">Refund</x-badge>
                                    @else
                                        <x-badge type="warning">Pending</x-badge>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @php
                                        $statusColors = [
                                            'TERIMA'  => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400',
                                            'PILAH'   => 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400',
                                            'CUCI'    => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/20 dark:text-cyan-400',
                                            'KERING'  => 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400',
                                            'LIPAT'   => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400',
                                            'CEK'     => 'bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-400',
                                            'SIAP'    => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
                                            'DIAMBIL' => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
                                        ];
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-extrabold {{ $statusColors[$order->production_status] ?? 'bg-slate-100 text-slate-500' }}">
                                        {{ $order->production_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($order->total, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('track', ['order_number' => $order->order_number]) }}" target="_blank"
                                           class="btn-touch inline-flex items-center gap-1 px-2 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-2xs font-bold rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors" title="Lacak Live Tracking">
                                            <span class="material-symbols-outlined text-sm">my_location</span>
                                            Lacak
                                        </a>
                                        <a href="{{ route('invoices.show', $order) }}" target="_blank"
                                           class="btn-touch inline-flex items-center gap-1 px-2 py-1 bg-orange-50 dark:bg-slate-800 text-primary text-2xs font-bold rounded-lg hover:bg-orange-100 dark:hover:bg-slate-700 transition-colors" title="Cetak Invoice Billing A4">
                                            <span class="material-symbols-outlined text-sm">description</span>
                                            Invoice
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isGlobalUser ? 9 : 8 }}" class="py-12 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">receipt_long</span>
                                    Belum ada transaksi ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card View --}}
            <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($orders as $order)
                    <div class="p-4 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-mono font-black text-sm text-slate-800 dark:text-slate-200">{{ $order->order_number }}</p>
                                <p class="text-2xs text-slate-400">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="font-mono font-black text-sm text-primary">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <span class="material-symbols-outlined text-sm text-slate-400">person</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $order->customer?->name ?? 'Walk-In' }}</span>
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            @if($order->payment_status === 'paid')
                                <x-badge type="success">Lunas</x-badge>
                            @elseif($order->payment_status === 'refunded')
                                <x-badge type="danger">Refund</x-badge>
                            @else
                                <x-badge type="warning">Pending</x-badge>
                            @endif
                            <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-extrabold {{ $statusColors[$order->production_status] ?? 'bg-slate-100 text-slate-500' }}">
                                {{ $order->production_status }}
                            </span>
                            <a href="{{ route('invoices.show', $order) }}" class="ml-auto btn-touch inline-flex items-center gap-1 px-2.5 py-1.5 bg-orange-50 dark:bg-slate-800 text-primary text-2xs font-bold rounded-lg">
                                <span class="material-symbols-outlined text-sm">description</span>Invoice
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400">
                        <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">receipt_long</span>
                        Belum ada transaksi ditemukan.
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if($orders->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-800">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
