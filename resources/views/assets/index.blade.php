<x-app-layout>
    <div class="flex flex-col gap-6">
        <x-page-header title="Manajemen Aset Tetap & Depresiasi" :breadcrumbs="['Aset' => '/assets']" />

        <x-card title="Daftar Mesin & Peralatan Cabang">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Alat</th>
                            <th class="py-3 px-4">Kode Aset</th>
                            <th class="py-3 px-4">Kategori</th>
                            <th class="py-3 px-4">Biaya Perolehan</th>
                            <th class="py-3 px-4">Nilai Buku</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($assets as $asset)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200 text-sm">
                                    {{ $asset->name }}
                                </td>
                                <td class="py-4 px-4 font-mono text-slate-500">
                                    {{ $asset->asset_code }}
                                </td>
                                <td class="py-4 px-4 text-slate-600 dark:text-slate-400 capitalize">
                                    {{ $asset->category }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($asset->acquisition_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 font-bold text-primary">
                                    Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4">
                                    @if ($asset->is_active)
                                        <x-badge type="success">Operasional</x-badge>
                                    @else
                                        <x-badge type="danger">Disposal / Rusak</x-badge>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">Belum ada data aset tetap terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $assets->links() }}
            </div>
        </x-card>
    </div>
</x-app-layout>
