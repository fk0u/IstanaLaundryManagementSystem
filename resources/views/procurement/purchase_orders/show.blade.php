<x-app-layout>
    <div class="flex flex-col gap-6 max-w-5xl mx-auto">
        <div class="flex flex-wrap justify-between items-center gap-3">
            <x-page-header :title="'Detail Purchase Order: ' . $po->po_number" :breadcrumbs="['Pengadaan' => '#', 'PO' => '/procurement/purchase-orders', $po->po_number => '#']" />
            <div class="flex items-center gap-2">
                <a href="{{ route('procurement.purchase-orders.index') }}"
                   class="h-10 px-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center gap-1.5 transition-all">
                    <span class="material-symbols-outlined text-sm">arrow_back</span> Kembali
                </a>
                <a href="{{ route('procurement.purchase-orders.print', $po->id) }}" target="_blank"
                   class="h-10 px-4 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center gap-1.5 transition-all">
                    <span class="material-symbols-outlined text-sm">print</span> Cetak
                </a>
                @if ($waUrl)
                    <a href="{{ route('procurement.purchase-orders.whatsapp', $po->id) }}" target="_blank"
                       class="h-10 px-4 bg-[#25D366] hover:bg-emerald-600 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">chat</span> Kirim WA
                    </a>
                @endif
            </div>
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-2" />
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-2xl shadow-xs">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Informasi Supplier</span>
                <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-200">{{ $po->supplier?->name }}</h4>
                <p class="text-xs text-slate-500 mt-1">📞 {{ $po->supplier?->phone ?? '-' }}</p>
                <p class="text-xs text-slate-500">✉️ {{ $po->supplier?->email ?? '-' }}</p>
                <p class="text-xs text-slate-400 mt-2">{{ $po->supplier?->address ?? '-' }}</p>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-2xl shadow-xs">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">Status & Jadwal</span>
                <div class="mb-2">
                    @if ($po->status === 'completed')
                        <x-badge type="success">Selesai (Received)</x-badge>
                    @elseif ($po->status === 'partial')
                        <x-badge type="info">Diterima Sebagian</x-badge>
                    @elseif ($po->status === 'confirmed')
                        <x-badge type="primary">Terkonfirmasi</x-badge>
                    @elseif ($po->status === 'sent')
                        <x-badge type="warning">Dikirim</x-badge>
                    @else
                        <x-badge type="gray">Draft</x-badge>
                    @endif
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-300"><strong>Tgl Order:</strong> {{ $po->order_date?->format('d/m/Y') ?? '-' }}</p>
                <p class="text-xs text-slate-600 dark:text-slate-300"><strong>Estimasi:</strong> {{ $po->expected_date?->format('d/m/Y') ?? '-' }}</p>
                <p class="text-xs text-slate-400 mt-1"><strong>Cabang:</strong> {{ $po->branch?->name ?? '-' }}</p>
            </div>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-2xl shadow-xs flex flex-col justify-between">
                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Ringkasan Biaya</span>
                <div class="space-y-1">
                    <div class="flex justify-between text-xs text-slate-500">
                        <span>Subtotal:</span>
                        <span class="font-mono font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-slate-500">
                        <span>PPN (11%):</span>
                        <span class="font-mono font-bold text-slate-700 dark:text-slate-300">Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-black text-primary pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span>Total PO:</span>
                        <span class="font-mono text-base">Rp {{ number_format($po->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <x-card title="Daftar Barang yang Dipesan">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Barang</th>
                            <th class="py-3 px-4 text-center">Qty Dipesan</th>
                            <th class="py-3 px-4 text-center">Qty Diterima</th>
                            <th class="py-3 px-4 text-right">Harga Satuan</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @foreach($po->items as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $item->item?->name ?? ('Item #' . $item->item_id) }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-700 dark:text-slate-300">
                                    {{ number_format($item->quantity, 2) }} {{ $item->item?->unit ?? 'unit' }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-mono font-bold {{ $item->received_qty >= $item->quantity ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ number_format($item->received_qty, 2) }} {{ $item->item?->unit ?? 'unit' }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                    Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-black text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</x-app-layout>
