<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6">
        <x-page-header title="Produksi & Pelacakan Cucian" :breadcrumbs="['Produksi' => '/production']" />

        <!-- Alert Flash Messages -->
        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-2" />
        @endif

        <!-- Status Filter Bar — Horizontal Scroll Pills for Mobile -->
        <x-card :compact="true">
            <div class="scroll-pills">
                <a href="{{ route('production.index') }}" 
                   class="btn-touch px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer whitespace-nowrap {{ !request()->has('status') ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50' }}">
                    Semua Antrean
                </a>
                @foreach (['TERIMA', 'PILAH', 'CUCI', 'KERING', 'LIPAT', 'CEK', 'SIAP'] as $status)
                    <a href="{{ route('production.index', ['status' => $status]) }}" 
                       class="btn-touch px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer whitespace-nowrap {{ request('status') === $status ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50' }}">
                        {{ $status }}
                    </a>
                @endforeach
                <a href="{{ route('production.index', ['status' => 'DIAMBIL']) }}" 
                   class="btn-touch px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer whitespace-nowrap {{ request('status') === 'DIAMBIL' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-white dark:bg-slate-900 border-emerald-200 dark:border-emerald-900/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' }}">
                    <span class="material-symbols-outlined text-xs align-middle mr-0.5">check_circle</span>DIAMBIL
                </a>
            </div>
        </x-card>

        <!-- Orders list -->
        <div class="grid grid-cols-1 gap-4 md:gap-6">
            @if ($orders->isEmpty())
                <x-card>
                    <div class="text-center py-12 md:py-16">
                        <span class="material-symbols-outlined text-slate-350 dark:text-slate-700 text-5xl md:text-6xl mb-3">dry_cleaning</span>
                        <p class="text-xs md:text-sm font-semibold text-slate-400 dark:text-slate-500">Tidak ada antrean cucian aktif di stasiun ini.</p>
                    </div>
                </x-card>
            @else
                @foreach ($orders as $order)
                    <x-card>
                        <div class="flex flex-col lg:flex-row justify-between gap-4 md:gap-6" x-data="{ openNotesForm: false }">
                            
                            <!-- Order details & Items (Left side) -->
                            <div class="flex-1 space-y-3 md:space-y-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-base md:text-lg font-black text-slate-800 dark:text-slate-200">
                                            #{{ $order->order_number }}
                                        </h3>
                                        <x-badge type="primary">{{ $order->production_status }}</x-badge>
                                    </div>
                                    <span class="text-2xs text-slate-400 font-medium">
                                        {{ $order->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <div class="flex flex-col gap-0.5">
                                    <span class="text-2xs text-slate-400 font-bold uppercase tracking-wider">Pelanggan</span>
                                    <span class="text-xs md:text-sm font-bold text-slate-700 dark:text-slate-200">
                                        {{ $order->customer?->name ?? 'Pelanggan Umum (Walk-In)' }} 
                                        @if($order->customer)
                                            <span class="text-2xs font-semibold text-slate-400">({{ $order->customer->phone }})</span>
                                        @endif
                                    </span>
                                </div>

                                <!-- Items List -->
                                <div>
                                    <span class="block text-2xs text-slate-400 font-bold uppercase tracking-wider mb-2">Item Cucian</span>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        @foreach ($order->items as $item)
                                            <div class="flex items-center gap-2.5 p-2.5 border border-slate-100 dark:border-slate-800/80 rounded-xl bg-slate-50/40 dark:bg-slate-900/50">
                                                <span class="w-7 h-7 rounded-lg bg-orange-100/60 dark:bg-slate-800 text-primary flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ round($item->quantity, 1) }}
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <span class="block text-xs font-semibold text-slate-700 dark:text-slate-300 truncate">{{ $item->service->name }}</span>
                                                    <span class="block text-2xs text-slate-400 truncate">{{ $item->service->type }} • {{ $item->notes ?? 'tanpa catatan' }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Linear Status Progression Bar — Responsive Scroll Pills -->
                                <div class="pt-2">
                                    <span class="block text-2xs text-slate-400 font-bold uppercase tracking-wider mb-2">Progress Alur Produksi</span>
                                    <div class="scroll-pills items-center gap-1.5">
                                        @php
                                            $steps = ['TERIMA', 'PILAH', 'CUCI', 'KERING', 'LIPAT', 'CEK', 'SIAP', 'DIAMBIL'];
                                            $currentIdx = array_search($order->production_status, $steps);
                                        @endphp
                                        @foreach ($steps as $idx => $step)
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <span class="text-2xs md:text-xs font-bold px-2.5 py-1 rounded-lg {{ $idx === $currentIdx ? 'bg-primary text-white font-extrabold shadow-sm' : ($idx < $currentIdx ? 'bg-emerald-50 text-emerald-600 border border-emerald-100 dark:bg-emerald-950/25 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-400') }}">
                                                    {{ $step }}
                                                </span>
                                                @if (!$loop->last)
                                                    <span class="material-symbols-outlined text-xs text-slate-300 dark:text-slate-700">arrow_forward</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Action Box (Right side / Full-width bottom on mobile) -->
                            <div class="w-full lg:w-72 flex flex-col justify-between items-stretch border-t lg:border-t-0 lg:border-l border-slate-100 dark:border-slate-800 pt-4 lg:pt-0 lg:pl-6 shrink-0">
                                <div>
                                    <span class="block text-2xs text-slate-400 font-bold uppercase tracking-wider mb-2">Aksi Stasiun Kerja</span>
                                    
                                    @php
                                        $nextStatus = null;
                                        if ($currentIdx !== false && isset($steps[$currentIdx + 1])) {
                                            $nextStatus = $steps[$currentIdx + 1];
                                        }
                                    @endphp

                                    @if ($nextStatus)
                                        <div class="bg-orange-50/50 dark:bg-slate-850 p-3.5 rounded-xl border border-orange-100/50 dark:border-slate-800 mb-3">
                                            <span class="block text-2xs text-slate-400 font-bold uppercase tracking-wider mb-0.5">Target Langkah</span>
                                            <span class="text-sm md:text-base font-extrabold text-primary block mb-1.5">{{ $nextStatus }}</span>
                                            
                                            <button @click="openNotesForm = !openNotesForm" type="button" 
                                                    class="text-2xs font-semibold text-slate-500 hover:text-primary transition-colors flex items-center gap-1 cursor-pointer">
                                                <span class="material-symbols-outlined text-xs">edit_note</span>
                                                Catatan transisi
                                            </button>
                                        </div>

                                        <form action="{{ route('production.update', $order->id) }}" method="POST" class="space-y-2.5" onsubmit="return confirm('Kirim order #{{ $order->order_number }} ke status {{ $nextStatus }}?')">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $nextStatus }}">
                                            
                                            <div x-show="openNotesForm" x-cloak class="transition-all duration-200">
                                                <textarea name="notes" placeholder="Catatan transisi..." rows="2"
                                                          class="w-full p-2 text-2xs border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 rounded-xl outline-none focus:border-primary"></textarea>
                                            </div>

                                            <button type="submit" 
                                                    class="btn-touch w-full rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-xs flex items-center justify-center gap-1.5 active:scale-[0.98] transition-all cursor-pointer shadow-sm">
                                                <span class="material-symbols-outlined text-base">forward</span>
                                                Kirim ke {{ $nextStatus }}
                                            </button>
                                        </form>
                                    @else
                                        <div class="bg-emerald-50 dark:bg-emerald-950/10 p-3.5 rounded-xl border border-emerald-100 dark:border-emerald-900/30 text-emerald-800 dark:text-emerald-400 flex items-center gap-2">
                                            <span class="material-symbols-outlined text-xl">verified</span>
                                            <span class="text-xs font-bold">Produksi Selesai</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="mt-3 pt-3 border-t border-slate-50 dark:border-slate-800/30 text-center flex flex-col gap-0.5 text-2xs text-slate-400">
                                    <span>Nota: {{ $order->order_number }}</span>
                                    <span>Bayar: {{ strtoupper($order->payment_method) }} ({{ strtoupper($order->payment_status) }})</span>
                                </div>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            @endif
        </div>
    </div>
</x-app-layout>
