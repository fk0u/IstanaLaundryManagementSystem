<x-app-layout>
    <div x-data="{ showCreateModal: false, selectedOrderId: '', orders: @js($refundableOrders), amount: 0, reason: '' }" class="flex flex-col gap-6">
        <div class="flex justify-between items-center">
            <x-page-header title="Refund & Pembatalan Transaksi" :breadcrumbs="['POS' => '#', 'Refund' => '/refunds']" />
            
            @if(auth()->user()->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin', 'Cashier']))
                <button @click="showCreateModal = true" class="h-11 px-5 bg-primary hover:bg-orange-600 text-white font-bold rounded-xl text-xs flex items-center gap-2 transition-all active:scale-[0.98] cursor-pointer shadow-md shadow-orange-500/10">
                    <span class="material-symbols-outlined">assignment_return</span>
                    Ajukan Refund
                </button>
            @endif
        </div>

        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-4" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-4" />
        @endif

        <x-card title="Daftar Pengajuan Refund & Pembatalan">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-800 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Nota Order</th>
                            <th class="py-3 px-4">Cabang</th>
                            <th class="py-3 px-4">Diajukan Oleh</th>
                            <th class="py-3 px-4">Nilai Refund</th>
                            <th class="py-3 px-4">Alasan</th>
                            <th class="py-3 px-4">Alur Persetujuan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-850">
                        @forelse($refunds as $ref)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors">
                                <td class="py-4 px-4 font-mono font-bold text-slate-800 dark:text-slate-200">
                                    {{ $ref->order?->order_number }}
                                </td>
                                <td class="py-4 px-4 text-slate-500">
                                    {{ $ref->branch?->name }}
                                </td>
                                <td class="py-4 px-4 font-semibold text-slate-700 dark:text-slate-350">
                                    {{ $ref->requester?->name }}
                                </td>
                                <td class="py-4 px-4 font-extrabold text-red-500 font-mono">
                                    Rp {{ number_format($ref->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-4 text-slate-650 dark:text-slate-400 max-w-xs truncate" title="{{ $ref->reason }}">
                                    {{ $ref->reason }}
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-1.5 text-[9px] font-bold text-slate-400">
                                        <!-- Step 1: Cashier -->
                                        <div class="flex items-center gap-1 {{ $ref->cashier_approved_at ? 'text-green-500' : '' }}" title="Tahap 1: Kasir">
                                            <span class="material-symbols-outlined text-[12px] font-bold">check_circle</span>
                                            <span>Kasir</span>
                                        </div>
                                        <span class="material-symbols-outlined text-[10px]">chevron_right</span>

                                        <!-- Step 2: Branch Admin -->
                                        <div class="flex items-center gap-1 {{ $ref->branch_approved_at ? 'text-green-500' : (($ref->status === 'pending') ? 'text-primary animate-pulse' : '') }}" title="Tahap 2: Branch Admin">
                                            <span class="material-symbols-outlined text-[12px] font-bold">check_circle</span>
                                            <span>Cabang</span>
                                        </div>
                                        <span class="material-symbols-outlined text-[10px]">chevron_right</span>

                                        <!-- Step 3: Finance -->
                                        <div class="flex items-center gap-1 {{ $ref->finance_approved_at ? 'text-green-500' : (($ref->status === 'branch_approved') ? 'text-primary animate-pulse' : '') }}" title="Tahap 3: Finance">
                                            <span class="material-symbols-outlined text-[12px] font-bold">check_circle</span>
                                            <span>Finance</span>
                                        </div>
                                        <span class="material-symbols-outlined text-[10px]">chevron_right</span>

                                        <!-- Step 4: Owner -->
                                        <div class="flex items-center gap-1 {{ $ref->owner_approved_at ? 'text-green-500' : (($ref->status === 'finance_approved') ? 'text-primary animate-pulse' : '') }}" title="Tahap 4: Owner">
                                            <span class="material-symbols-outlined text-[12px] font-bold">check_circle</span>
                                            <span>Owner</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    @if ($ref->status === 'completed')
                                        <x-badge type="success">Selesai</x-badge>
                                    @elseif ($ref->status === 'rejected')
                                        <x-badge type="danger">Ditolak</x-badge>
                                    @elseif ($ref->status === 'finance_approved')
                                        <x-badge type="info">Disetujui Keuangan</x-badge>
                                    @elseif ($ref->status === 'branch_approved')
                                        <x-badge type="warning">Disetujui Cabang</x-badge>
                                    @else
                                        <x-badge type="gray">Menunggu Cabang</x-badge>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <!-- Approval Buttons -->
                                        @php
                                            $user = auth()->user();
                                            $showApproveBtn = false;
                                            $approveBtnText = '';
                                            
                                            if ($ref->status === 'pending' && $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Branch_Admin'])) {
                                                $showApproveBtn = true;
                                                $approveBtnText = 'Setujui Cabang';
                                            } elseif ($ref->status === 'branch_approved' && $user->hasAnyRole(['Developer', 'Owner', 'Super_Admin', 'Finance'])) {
                                                $showApproveBtn = true;
                                                $approveBtnText = 'Setujui Keuangan';
                                            } elseif ($ref->status === 'finance_approved' && $user->hasAnyRole(['Developer', 'Owner'])) {
                                                $showApproveBtn = true;
                                                $approveBtnText = 'Owner Approve & Proses';
                                            }
                                        @endphp

                                        @if ($showApproveBtn)
                                            <form action="{{ route('refunds.approve', $ref->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menyetujui dan memproses pengembalian dana (refund) ini?')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-green-500 hover:bg-green-600 text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all">
                                                    {{ $approveBtnText }}
                                                </button>
                                            </form>

                                            <form action="{{ route('refunds.reject', $ref->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menolak permintaan refund ini?')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-red-500 hover:bg-red-650 text-white rounded-lg text-[10px] font-bold cursor-pointer transition-all">
                                                    Tolak
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-slate-350 dark:text-slate-700 material-symbols-outlined text-sm cursor-not-allowed">lock</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">Belum ada pengajuan refund terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $refunds->links() }}
            </div>
        </x-card>

        <!-- Custom Create Refund Modal -->
        <div x-show="showCreateModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
             x-cloak>
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl transition-all duration-300">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200">Ajukan Refund Transaksi</h3>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                
                <form action="{{ route('refunds.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="flex flex-col gap-1.5">
                        <label for="order_id" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor Nota / Transaksi Lunas</label>
                        <select id="order_id" name="order_id" x-model="selectedOrderId" required
                                @change="
                                    let ord = orders.find(o => o.id == selectedOrderId);
                                    if(ord) {
                                        amount = parseFloat(ord.total);
                                    } else {
                                        amount = 0;
                                    }
                                "
                                class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                            <option value="">-- Pilih Nota Paid --</option>
                            <template x-for="o in orders" :key="o.id">
                                <option :value="o.id" x-text="o.order_number + ' - ' + (o.customer ? o.customer.name : 'Walk-In') + ' (Rp ' + parseFloat(o.total).toLocaleString('id-ID') + ')'"></option>
                            </template>
                        </select>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="amount" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nilai Refund (Rp)</label>
                        <input type="number" id="amount" name="amount" x-model="amount" required min="0.01" step="any"
                               class="w-full h-11 px-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                        <span class="text-[9px] text-slate-400">Pastikan nilai tidak melebihi total nota transaksi.</span>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="reason" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Alasan Pengembalian / Pembatalan</label>
                        <textarea id="reason" name="reason" x-model="reason" required placeholder="Contoh: Pakaian luntur/rusak saat dicuci, atau pembatalan order oleh pelanggan..." rows="3"
                                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all"></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false"
                                class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-850 cursor-pointer transition-all">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-3 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl cursor-pointer transition-all">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
