<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6" x-data="{ showAddAsset: false }">
        <x-page-header title="Aset Tetap & Depresiasi (Fixed Assets)" :breadcrumbs="['Aset Tetap' => '/assets']" />

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif

        <div class="flex justify-between items-center">
            <button type="button" @click="showAddAsset = true" class="btn-touch px-4 py-2 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-base">add_home_work</span> Tambah Aset Tetap
            </button>
        </div>

        <x-card title="Daftar Aset Tetap">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-2.5 px-3">Kode / Nama Aset</th>
                            <th class="py-2.5 px-3">Kategori</th>
                            <th class="py-2.5 px-3">Perolehan</th>
                            <th class="py-2.5 px-3 text-right">Harga Beli</th>
                            <th class="py-2.5 px-3 text-right">Akumulasi Depresiasi</th>
                            <th class="py-2.5 px-3 text-right">Nilai Buku</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($assets as $ast)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30">
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-800 dark:text-slate-200 block">{{ $ast->name }}</span>
                                    <span class="text-2xs text-slate-400 font-mono">{{ $ast->asset_code }}</span>
                                </td>
                                <td class="py-3 px-3 text-slate-600 dark:text-slate-400">
                                    {{ $ast->category }}
                                </td>
                                <td class="py-3 px-3 text-slate-500">
                                    {{ $ast->acquisition_date?->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-bold text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($ast->acquisition_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-rose-600">
                                    Rp {{ number_format($ast->accumulated_depreciation, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right font-mono font-black text-emerald-600">
                                    Rp {{ number_format($ast->book_value, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <a href="{{ route('assets.show', $ast->id) }}" class="btn-touch px-3 py-1.5 bg-orange-50 dark:bg-slate-800 text-primary text-2xs font-bold rounded-lg inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">calendar_month</span> Jadwal Depresiasi
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">Belum ada aset tetap terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $assets->links() }}
            </div>
        </x-card>

        <!-- Add Asset Modal -->
        <div x-show="showAddAsset" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-5 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-2 border-b">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-200">Tambah Aset Tetap Baru</h3>
                    <button type="button" @click="showAddAsset = false" class="text-slate-400"><span class="material-symbols-outlined">close</span></button>
                </div>
                <form action="{{ route('assets.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Kode Aset</label>
                        <input type="text" name="asset_code" required placeholder="AST-MC-001..." class="w-full h-9 px-3 rounded-xl border text-xs">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Nama Aset</label>
                        <input type="text" name="name" required placeholder="Mesin Cuci SpeedQueen 15kg..." class="w-full h-9 px-3 rounded-xl border text-xs">
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Kategori</label>
                        <select name="category" required class="w-full h-9 px-2 rounded-xl border text-xs">
                            <option value="Mesin & Peralatan">Mesin & Peralatan</option>
                            <option value="Kendaraan">Kendaraan Operasional</option>
                            <option value="Bangunan & Renovasi">Bangunan & Renovasi</option>
                            <option value="Elektronik & IT">Elektronik & IT</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Tgl Perolehan</label>
                            <input type="date" name="acquisition_date" required value="{{ date('Y-m-d') }}" class="w-full h-9 px-2 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Harga Beli (Rp)</label>
                            <input type="number" name="acquisition_cost" required placeholder="25000000..." class="w-full h-9 px-2 rounded-xl border text-xs font-bold">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Nilai Sisa (Salvage)</label>
                            <input type="number" name="salvage_value" required value="0" class="w-full h-9 px-2 rounded-xl border text-xs">
                        </div>
                        <div>
                            <label class="text-2xs font-bold text-slate-400 uppercase">Manfaat (Bulan)</label>
                            <input type="number" name="useful_life_months" required value="48" class="w-full h-9 px-2 rounded-xl border text-xs font-bold">
                        </div>
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Metode Depresiasi</label>
                        <select name="depreciation_method" required class="w-full h-9 px-2 rounded-xl border text-xs">
                            <option value="straight_line">Garis Lurus (Straight Line)</option>
                            <option value="declining_balance">Saldo Menurun (Declining Balance)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-2xs font-bold text-slate-400 uppercase">Cabang</label>
                        <select name="branch_id" required class="w-full h-9 px-2 rounded-xl border text-xs">
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn-touch w-full bg-primary text-white font-bold text-xs rounded-xl py-2.5">Simpan Aset Tetap</button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
