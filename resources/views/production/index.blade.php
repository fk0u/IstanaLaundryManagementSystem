<x-app-layout>
    <div class="flex flex-col gap-4 md:gap-6" x-data="{ showList: @js(!$isWorkshopRole || !empty($search) || request()->has('status')) }">
        <x-page-header title="Produksi & Pelacakan Cucian" :breadcrumbs="['Produksi' => '/production']" />

        <!-- Alert Flash Messages -->
        @if (session('success'))
            <x-alert type="success" :message="session('success')" class="mb-2" />
        @endif
        @if (session('error'))
            <x-alert type="danger" :message="session('error')" class="mb-2" />
        @endif

        <!-- Primary Order Search Bar -->
        <x-card :compact="true">
            <form action="{{ route('production.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-2">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif

                @if($isGlobalUser)
                    <div class="w-full sm:w-48">
                        <select name="branch_id" onchange="this.form.submit()" class="w-full h-10 px-3 text-xs font-bold rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 text-slate-800 dark:text-slate-200 focus:outline-none focus:border-primary">
                            <option value="">Semua Cabang (Global Scope)</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)$branchId === (string)$b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="relative flex-1 w-full">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none">search</span>
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           data-realtime-search="production-orders-container"
                           data-search-url="{{ route('production.index') }}"
                           placeholder="Cari Nomor Nota / Nama Pelanggan / No. Telp (Real-time)..." 
                           class="w-full pl-9 pr-10 py-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200 outline-none focus:border-primary transition-all">
                    @if($search)
                        <a href="{{ route('production.index', request()->only('status', 'branch_id')) }}" 
                           class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </a>
                    @endif
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="submit" class="btn-touch w-full sm:w-auto px-5 py-2.5 bg-primary hover:bg-primary-hover text-white text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 shadow-sm shrink-0">
                        <span class="material-symbols-outlined text-base">search</span> Cari
                    </button>
                    @if($isWorkshopRole)
                        <button type="button" 
                                @click="showList = !showList" 
                                class="btn-touch w-full sm:w-auto px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl flex items-center justify-center gap-1.5 shrink-0 transition-colors">
                            <span class="material-symbols-outlined text-base" x-text="showList ? 'visibility_off' : 'format_list_bulleted'"></span>
                            <span x-text="showList ? 'Sembunyikan List' : 'Tampilkan Semua List'"></span>
                        </button>
                    @endif
                </div>
            </form>
        </x-card>

        <!-- Status Filter Bar — Horizontal Scroll Pills for Mobile -->
        <x-card :compact="true">
            <div class="scroll-pills">
                <a href="{{ route('production.index', request()->only('search', 'branch_id')) }}" 
                   class="btn-touch px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer whitespace-nowrap {{ !request()->has('status') ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50' }}">
                    Semua Antrean
                </a>
                @foreach (['TERIMA', 'PILAH', 'CUCI', 'KERING', 'LIPAT', 'CEK', 'SIAP'] as $status)
                    <a href="{{ route('production.index', array_merge(request()->only('search', 'branch_id'), ['status' => $status])) }}" 
                       class="btn-touch px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer whitespace-nowrap {{ request('status') === $status ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-50' }}">
                        {{ $status }}
                    </a>
                @endforeach
                <a href="{{ route('production.index', array_merge(request()->only('search', 'branch_id'), ['status' => 'DIAMBIL'])) }}" 
                   class="btn-touch px-4 py-2 rounded-xl text-xs font-bold border transition-all cursor-pointer whitespace-nowrap {{ request('status') === 'DIAMBIL' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-white dark:bg-slate-900 border-emerald-200 dark:border-emerald-900/40 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/20' }}">
                    <span class="material-symbols-outlined text-xs align-middle mr-0.5">check_circle</span>DIAMBIL
                </a>
            </div>
        </x-card>

        @if($isWorkshopRole)
            <!-- Staff Landing Focus Notice when list is hidden -->
            <div x-show="!showList" x-cloak class="p-6 bg-slate-900 text-white rounded-2xl shadow-lg text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-primary/20 text-primary flex items-center justify-center mx-auto">
                    <span class="material-symbols-outlined text-2xl">qr_code_scanner</span>
                </div>
                <h4 class="text-base font-extrabold">Mode Fokus Pencarian Stasiun Kerja</h4>
                <p class="text-xs text-slate-400 max-w-md mx-auto">
                    Cari nomor nota di kolom pencarian di atas untuk langsung mengupdate status order, atau klik tombol <strong>Tampilkan Semua List</strong> jika ingin melihat daftar antrean.
                </p>
                <button type="button" @click="showList = true" class="btn-touch px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl inline-flex items-center gap-1.5 shadow-sm">
                    <span class="material-symbols-outlined text-sm">format_list_bulleted</span> Tampilkan Semua Antrean
                </button>
            </div>
        @endif

        <!-- Orders list -->
        <div id="production-orders-container" class="space-y-4">
            <div class="grid grid-cols-1 gap-4 md:gap-6" x-show="showList" x-cloak>
                @if ($orders->isEmpty())
                    <x-card>
                        <div class="text-center py-12 md:py-16">
                            <span class="material-symbols-outlined text-slate-350 dark:text-slate-700 text-5xl md:text-6xl mb-3">dry_cleaning</span>
                            <p class="text-xs md:text-sm font-semibold text-slate-400 dark:text-slate-500">
                                {{ $search ? "Tidak ada order ditemukan untuk pencarian '{$search}'." : ($requestedStatus === 'DIAMBIL' ? 'Belum ada order yang sudah diambil pelanggan.' : 'Tidak ada antrean cucian aktif di stasiun ini.') }}
                            </p>
                        </div>
                    </x-card>
                @else
                    @foreach ($orders as $order)
                        <x-card>
                            <div class="flex flex-col lg:flex-row justify-between gap-4 md:gap-6" x-data="{ openNotesForm: false }">
                                
                                <!-- Order details & Items (Left side) -->
                                <div class="flex-1 space-y-3 md:space-y-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-base md:text-lg font-black text-slate-800 dark:text-slate-200">
                                                #{{ $order->order_number }}
                                            </h3>
                                            <x-badge type="primary">{{ $order->production_status }}</x-badge>
                                            @if($isGlobalUser && $order->branch)
                                                <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 font-bold text-2xs">
                                                    {{ $order->branch->name }}
                                                </span>
                                            @endif
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
                                        <div class="bg-emerald-50 dark:bg-emerald-950/10 p-3.5 rounded-xl border border-emerald-100 dark:border-emerald-900/30 text-emerald-800 dark:text-emerald-400 flex items-center gap-2 mb-2">
                                            <span class="material-symbols-outlined text-xl">verified</span>
                                            <span class="text-xs font-bold">Produksi Selesai</span>
                                        </div>
                                    @endif

                                    @if(in_array($order->production_status, ['SIAP', 'DIAMBIL']))
                                        <a href="{{ route('invoices.ready-whatsapp', $order->id) }}" target="_blank"
                                           class="btn-touch w-full rounded-xl bg-[#25D366] hover:bg-emerald-600 text-white font-bold text-2xs py-2 px-3 flex items-center justify-center gap-1.5 transition-all shadow-sm">
                                            <span class="material-symbols-outlined text-base">chat</span>
                                            Kirim WA Siap Diambil
                                        </a>
                                    @endif
                                </div>
                                
                                <div class="mt-3 pt-3 border-t border-slate-50 dark:border-slate-800/30 flex flex-col gap-1.5 text-2xs text-slate-400">
                                     <div class="flex justify-between items-center">
                                         <span>Nota: <strong class="text-slate-700 dark:text-slate-300 font-mono">{{ $order->order_number }}</strong></span>
                                         <span class="font-extrabold uppercase {{ $order->payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">{{ $order->payment_status }}</span>
                                     </div>
                                     <div class="flex items-center gap-2 pt-1">
                                         <a href="{{ route('invoices.show', $order->id) }}" target="_blank" class="flex-1 py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold rounded-lg text-center flex items-center justify-center gap-1 transition-colors">
                                             <span class="material-symbols-outlined text-xs">description</span> Invoice A4
                                         </a>
                                         <a href="{{ route('track', ['order_number' => $order->order_number]) }}" target="_blank" class="flex-1 py-1.5 px-2 bg-orange-50 dark:bg-slate-800 hover:bg-orange-100 dark:hover:bg-slate-700 text-primary font-bold rounded-lg text-center flex items-center justify-center gap-1 transition-colors">
                                             <span class="material-symbols-outlined text-xs">my_location</span> Lacak Live
                                         </a>
                                     </div>
                                 </div>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            @endif
            @if ($orders->hasPages())
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
