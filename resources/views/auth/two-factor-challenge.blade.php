<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-3xl lg:text-4xl font-black font-display text-slate-900 mb-2 tracking-tight">
            Autentikasi 2FA
        </h1>
        <p class="text-xs font-semibold text-slate-500">
            Akun Anda terhubung dengan autentikasi dua faktor. Masukkan kode verifikasi untuk melanjutkan.
        </p>
    </div>

    <!-- Session Status & Errors -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

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
