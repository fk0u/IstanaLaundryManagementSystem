<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-3xl lg:text-4xl font-black font-display text-slate-900 mb-2 tracking-tight">
            Sign In Portal
        </h1>
        <p class="text-xs font-semibold text-slate-500">
            Akses sistem ERP & POS terintegrasi Istana Laundry Samarinda. Masukkan kredensial Anda.
        </p>
    </div>

    <!-- Alert Banner (Validation Errors / Failed Login) -->
    @if ($errors->any())
        <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200/80 flex items-start gap-3.5 text-rose-900 shadow-sm">
            <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">error</span>
            </div>
            <div class="flex-1 min-w-0 text-xs">
                <p class="font-bold text-rose-900 text-sm tracking-tight mb-0.5">Gagal Masuk</p>
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

    <!-- Alert Banner (Status / Informational / Success) -->
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

    <div x-data="{ 
        showPassword: false,
        email: '{{ old('email') }}',
        password: ''
    }" class="flex flex-col gap-6">
        
        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <div class="flex flex-col gap-2">
                <label class="text-2xs font-extrabold font-sans text-slate-500 uppercase tracking-widest" for="email">Work Email</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors pointer-events-none text-xl" style="font-variation-settings: 'FILL' 0;">mail</span>
                    <input class="w-full pl-12 pr-4 h-[52px] rounded-2xl bg-surface-container border {{ $errors->has('email') ? 'border-rose-400 ring-2 ring-rose-100' : 'border-surface-outline/50' }} text-slate-800 text-xs font-semibold focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all placeholder:text-slate-400" 
                        id="email" 
                        type="email" 
                        name="email" 
                        x-model="email"
                        placeholder="name@istanalaundry.com" 
                        required 
                        autofocus 
                        autocomplete="username" />
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>

            <!-- Password -->
            <div class="flex flex-col gap-2">
                <div class="flex justify-between items-center">
                    <label class="text-2xs font-extrabold font-sans text-slate-500 uppercase tracking-widest" for="password">Password</label>
                    @if (Route::has('password.request'))
                        <a class="text-xs font-bold text-primary hover:underline transition-colors" href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    @endif
                </div>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors pointer-events-none text-xl" style="font-variation-settings: 'FILL' 0;">lock</span>
                    <input class="w-full pl-12 pr-12 h-[52px] rounded-2xl bg-surface-container border {{ $errors->has('password') ? 'border-rose-400 ring-2 ring-rose-100' : 'border-surface-outline/50' }} text-slate-800 text-xs font-semibold focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all placeholder:text-slate-400" 
                        id="password" 
                        :type="showPassword ? 'text' : 'password'" 
                        name="password" 
                        x-model="password"
                        placeholder="••••••••" 
                        required 
                        autocomplete="current-password" />
                    
                    <!-- Toggle Password Visibility -->
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                        <span class="material-symbols-outlined select-none text-xl" x-text="showPassword ? 'visibility' : 'visibility_off'"></span>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between mt-1">
                <div class="flex items-center gap-2.5">
                    <input class="w-4 h-4 rounded-md border-slate-300 text-primary focus:ring-primary/30 cursor-pointer" 
                        id="remember_me" 
                        type="checkbox" 
                        name="remember" />
                    <label class="text-xs font-bold text-slate-600 cursor-pointer select-none" for="remember_me">
                        Ingat saya di perangkat ini
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <button class="mt-3 md3-btn-primary w-full shadow-md3-2" type="submit">
                <span>Masuk ke Aplikasi ERP</span>
                <span class="material-symbols-outlined text-lg" style="font-variation-settings: 'FILL' 0;">arrow_forward</span>
            </button>
        </form>


    </div>

    <div class="mt-8 pt-4 border-t border-slate-100 flex justify-center text-center">
        <p class="text-[11px] text-slate-400 font-medium leading-relaxed">
            Secure connection. Internal authorization required. <br/>
            © 2026 Istana Premium Laundry Service.
        </p>
    </div>
</x-guest-layout>
