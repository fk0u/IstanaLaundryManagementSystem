<x-app-layout>
    <div class="flex flex-col gap-6">
        <x-page-header title="Manajemen Inventori & Stok Bahan" :breadcrumbs="['Inventori' => '/inventory']" />

        <x-card title="Stok Bahan Kimia & Sabun Cabang">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Item</th>
                            <th class="py-3 px-4">SKU / Kode</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Stok Saat Ini</th>
                            <th class="py-3 px-4">Stok Minimum</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($inventoryItems as $item)
                            @php
                                $isLowStock = $item->current_stock <= $item->min_stock;
                            @endphp
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200 text-sm">
                                    {{ $item->name }}
                                </td>
                                <td class="py-4 px-4 font-mono font-semibold text-slate-500">
                                    {{ $item->sku }}
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400 capitalize">
                                    {{ $item->category }}
                                </td>
                                <td class="py-4 px-4 font-bold text-sm {{ $isLowStock ? 'text-red-500' : 'text-slate-800 dark:text-slate-200' }}">
                                    {{ round($item->current_stock, 2) }} {{ $item->unit }}
                                </td>
                                <td class="py-4 px-4 text-slate-500 font-semibold">
                                    {{ round($item->min_stock, 2) }} {{ $item->unit }}
                                </td>
                                <td class="py-4 px-4">
                                    @if ($isLowStock)
                                        <x-badge type="danger">Stok Menipis</x-badge>
                                    @else
                                        <x-badge type="success">Stok Aman</x-badge>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">Belum ada item inventori terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $inventoryItems->links() }}
            </div>
        </x-card>
    </div>
</x-app-layout>
