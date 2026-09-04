<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-3xl lg:text-4xl font-black font-display text-slate-900 mb-2 tracking-tight">
            Autentikasi 2FA
        </h1>
        <p class="text-xs font-semibold text-slate-500">
            Akun Anda terhubung dengan autentikasi dua faktor. Masukkan kode verifikasi untuk melanjutkan.
        </p>
    </div>

    <!-- Alert Banner (Validation Errors / Failed 2FA) -->
    @if ($errors->any())
        <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200/80 flex items-start gap-3.5 text-rose-900 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">error</span>
            </div>
            <div class="flex-1 min-w-0 text-xs">
                <p class="font-bold text-rose-900 text-sm tracking-tight mb-0.5">Verifikasi Gagal</p>
                <p class="text-rose-700 font-medium leading-relaxed">
                    {{ $errors->first() }}
                </p>
            </div>
            <button @click="show = false" type="button" class="text-rose-400 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-100/50 transition-colors focus:outline-none" title="Tutup">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    @elseif (session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200/80 flex items-start gap-3.5 text-rose-900 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">error</span>
            </div>
            <div class="flex-1 min-w-0 text-xs">
                <p class="font-bold text-rose-900 text-sm tracking-tight mb-0.5">Terjadi Kesalahan</p>
                <p class="text-rose-700 font-medium leading-relaxed">
                    {{ session('error') }}
                </p>
            </div>
            <button @click="show = false" type="button" class="text-rose-400 hover:text-rose-600 p-1 rounded-lg hover:bg-rose-100/50 transition-colors focus:outline-none" title="Tutup">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    @endif

    <!-- Alert Banner (Status / Informational) -->
    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80 flex items-start gap-3.5 text-emerald-900 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            </div>
            <div class="flex-1 min-w-0 text-xs">
                <p class="font-bold text-emerald-900 text-sm tracking-tight mb-0.5">Pemberitahuan</p>
                <p class="text-emerald-700 font-medium leading-relaxed">
                    {{ session('status') }}
                </p>
            </div>
            <button @click="show = false" type="button" class="text-emerald-400 hover:text-emerald-600 p-1 rounded-lg hover:bg-emerald-100/50 transition-colors focus:outline-none" title="Tutup">
                <span class="material-symbols-outlined text-base">close</span>
            </button>
        </div>
    @endif

    <div x-data="{ recovery: false }" class="flex flex-col gap-6">
        <form method="POST" action="{{ route('two-factor.verify') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Standard TOTP Code Input -->
            <div x-show="!recovery" class="flex flex-col gap-2">
                <label class="text-2xs font-extrabold font-sans text-slate-500 uppercase tracking-widest" for="code">Kode 2FA (Authenticator App)</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors pointer-events-none text-xl" style="font-variation-settings: 'FILL' 0;">verified_user</span>
                    <input class="w-full pl-12 pr-4 h-[52px] rounded-2xl bg-surface-container border border-surface-outline/50 text-slate-800 text-base tracking-widest font-mono font-bold focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all placeholder:text-slate-400 placeholder:tracking-normal placeholder:font-sans placeholder:text-xs" 
                        id="code" 
                        type="text" 
                        name="code" 
                        placeholder="000000" 
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        x-bind:autofocus="!recovery" 
                        autocomplete="one-time-code" />
                </div>
                <x-input-error :messages="$errors->get('code')" class="mt-1" />
            </div>

            <!-- Recovery Code Input -->
            <div x-show="recovery" x-cloak class="flex flex-col gap-2">
                <label class="text-2xs font-extrabold font-sans text-slate-500 uppercase tracking-widest" for="recovery_code">Kode Pemulihan Darurat</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors pointer-events-none text-xl" style="font-variation-settings: 'FILL' 0;">key</span>
                    <input class="w-full pl-12 pr-4 h-[52px] rounded-2xl bg-surface-container border border-surface-outline/50 text-slate-800 text-xs font-mono font-bold focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all placeholder:text-slate-400" 
                        id="recovery_code" 
                        type="text" 
                        name="recovery_code" 
                        placeholder="xxxxx-xxxxx" 
                        x-bind:autofocus="recovery" 
                        autocomplete="off" />
                </div>
                <x-input-error :messages="$errors->get('recovery_code')" class="mt-1" />
            </div>

            <!-- Trust Device Option (30 Days) -->
            <div class="flex items-center gap-2.5 mt-1 bg-slate-50 p-3 rounded-2xl border border-slate-200/70">
                <input class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/30 cursor-pointer" 
                    id="trust_device" 
                    type="checkbox" 
                    name="trust_device" 
                    value="1" 
                    checked />
                <label class="text-xs font-bold text-slate-700 cursor-pointer select-none" for="trust_device">
                    Percayai perangkat ini selama 30 hari
                </label>
            </div>
            <p class="text-[10px] text-slate-400 -mt-3 pl-1 font-medium">Jika dicentang, Anda tidak akan diminta kode 2FA lagi di browser ini selama 30 hari.</p>

            <!-- Toggle Recovery Mode Link -->
            <div class="flex justify-end">
                <button type="button" 
                    @click="recovery = !recovery" 
                    class="text-xs font-bold text-primary hover:underline transition-colors focus:outline-none">
                    <span x-show="!recovery">Gunakan kode pemulihan darurat</span>
                    <span x-show="recovery" x-cloak>Gunakan kode aplikasi authenticator</span>
                </button>
            </div>

            <!-- Submit Button -->
            <button class="mt-2 md3-btn-primary w-full shadow-md3-2" type="submit">
                <span>Verifikasi & Masuk</span>
                <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 0;">arrow_forward</span>
            </button>
        </form>

        <div class="text-center pt-2">
            <a href="{{ route('login') }}" class="text-xs font-bold text-slate-400 hover:text-slate-600 transition-colors">
                &larr; Kembali ke Halaman Login
            </a>
        </div>
    </div>

    <div class="mt-8 pt-4 border-t border-slate-100 flex justify-center text-center">
        <p class="text-[11px] text-slate-400 font-medium leading-relaxed">
            Protected by Two-Factor Authentication. <br/>
            © 2026 Istana Premium Laundry Service.
        </p>
    </div>
</x-guest-layout>
