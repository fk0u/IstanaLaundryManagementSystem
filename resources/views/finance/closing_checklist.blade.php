<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6">
        <x-page-header title="Closing Checklist Periode Akuntansi" :breadcrumbs="['Keuangan' => '/finance', 'Closing Checklist' => route('finance.closing-checklist')]" />

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-stat-card title="Periode Berjalan" :value="$currentMonth" icon="calendar_month" description="Bulan pembukuan aktif" />
            <x-stat-card title="Piutang Unpaid" :value="$unpaidOrdersCount . ' Order'" icon="pending_actions" trendType="warning" description="'Rp ' . number_format($unpaidOrdersAmount, 0, ',', '.')" />
            <x-stat-card title="Status Periode" :value="$openPeriods->count() . ' Periode Terbuka'" icon="lock_open" trendType="info" description="Siap ditutup di akhir bulan" />
        </div>

        <x-card title="Daftar Periksa Penutupan Buku Bulanan (Closing Checklist)">
            <div class="space-y-4">
                <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700 flex items-start gap-3">
                    <span class="material-symbols-outlined text-emerald-600 text-2xl">check_circle</span>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200">1. Rekonsiliasi Kas POS & Bank</h4>
                        <p class="text-xs text-slate-500">Pastikan selisih kas fisik di kasir sesuai dengan total transaksi paid tunai & transfer pada jurnal ledger.</p>
                    </div>
                    <a href="/finance/journals" class="btn-touch px-3 py-1.5 bg-white border text-xs font-bold rounded-lg">Cek Jurnal</a>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700 flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-500 text-2xl">warning</span>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200">2. Evaluasi Piutang Pelanggan (Accounts Receivable)</h4>
                        <p class="text-xs text-slate-500">Terdapat {{ $unpaidOrdersCount }} nota cucian dengan total piutang <strong>Rp {{ number_format($unpaidOrdersAmount, 0, ',', '.') }}</strong> yang belum dilunasi.</p>
                    </div>
                    <a href="/orders?payment_status=pending" class="btn-touch px-3 py-1.5 bg-white border text-xs font-bold rounded-lg">Tagih Nota</a>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700 flex items-start gap-3">
                    <span class="material-symbols-outlined text-emerald-600 text-2xl">check_circle</span>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200">3. Depresiasi Aset Tetap</h4>
                        <p class="text-xs text-slate-500">Nilai akumulasi susut mesin dan peralatan cabang telah dihitung otomatis oleh sistem.</p>
                    </div>
                    <a href="/assets" class="btn-touch px-3 py-1.5 bg-white border text-xs font-bold rounded-lg">Lihat Aset</a>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-700 flex items-start gap-3">
                    <span class="material-symbols-outlined text-primary text-2xl">lock</span>
                    <div class="flex-1">
                        <h4 class="font-bold text-sm text-slate-800 dark:text-slate-200">4. Kunci Periode Akuntansi</h4>
                        <p class="text-xs text-slate-500">Kunci periode akuntansi untuk mencegah perubahan jurnal retroaktif setelah laporan disetujui Owner.</p>
                    </div>
                    <a href="/finance/periods" class="btn-touch px-4 py-1.5 bg-primary text-white text-xs font-bold rounded-lg">Kunci Periode</a>
                </div>
            </div>
        </x-card>
    </div>
</x-app-layout>
