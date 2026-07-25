<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-slate-900 mb-2 tracking-tight">
            Sign In Portal
        </h1>
        <p class="text-sm text-slate-500">
            Akses sistem ERP & POS terintegrasi Istana Laundry Samarinda. Masukkan kredensial Anda.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div x-data="{ 
        showPassword: false,
        email: '{{ old('email') }}',
        password: '',
        fillCredentials(roleEmail) {
            this.email = roleEmail;
            this.password = 'password';
        }
    }" class="flex flex-col gap-6">
        
        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-slate-700 uppercase tracking-wider" for="email">Work Email</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors pointer-events-none" style="font-variation-settings: 'FILL' 0;">mail</span>
                    <input class="w-full pl-12 pr-4 h-[48px] rounded-lg bg-slate-50 border border-slate-200 text-slate-800 text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all placeholder:text-slate-400 font-medium" 
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
            <div class="flex flex-col gap-1.5">
                <div class="flex justify-between items-center">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider" for="password">Password</label>
                    @if (Route::has('password.request'))
                        <a class="text-xs font-semibold text-primary hover:underline hover:text-primary-hover transition-colors" href="{{ route('password.request') }}">
                            Lupa password?
                        </a>
                    @endif
                </div>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors pointer-events-none" style="font-variation-settings: 'FILL' 0;">lock</span>
                    <input class="w-full pl-12 pr-12 h-[48px] rounded-lg bg-slate-50 border border-slate-200 text-slate-800 text-sm focus:bg-white focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all placeholder:text-slate-400 font-medium" 
                        id="password" 
                        :type="showPassword ? 'text' : 'password'" 
                        name="password" 
                        x-model="password"
                        placeholder="••••••••" 
                        required 
                        autocomplete="current-password" />
                    
                    <!-- Toggle Password Visibility -->
                    <button type="button" @click="showPassword = !showPassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                        <span class="material-symbols-outlined select-none" x-text="showPassword ? 'visibility' : 'visibility_off'"></span>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between mt-1">
                <div class="flex items-center gap-2.5">
                    <input class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary/30 cursor-pointer" 
                        id="remember_me" 
                        type="checkbox" 
                        name="remember" />
                    <label class="text-xs font-semibold text-slate-600 cursor-pointer select-none" for="remember_me">
                        Ingat saya di perangkat ini
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <button class="mt-2 w-full h-[52px] rounded-xl bg-primary hover:bg-primary-hover text-white font-bold text-sm flex items-center justify-center gap-2 active:scale-[0.98] transition-all group shadow-md shadow-primary/20" type="submit">
                Masuk ke Aplikasi
                <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform" style="font-variation-settings: 'FILL' 0;">login</span>
            </button>
        </form>

        <!-- Quick Demo Credentials for Dev & Testing -->
        <div class="mt-4 p-4 rounded-xl border border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-amber-500 size-5 text-[18px]">verified_user</span>
                <span class="text-xs font-bold text-slate-700 tracking-wide uppercase">Quick Demo Login</span>
            </div>
            
            <div class="grid grid-cols-2 gap-2">
                <button @click="fillCredentials('owner@istanalaundry.com')" class="flex flex-col items-start p-2 text-left bg-white border border-slate-150 rounded-lg hover:border-primary hover:bg-primary/5 transition-all focus:outline-none">
                    <span class="text-[10px] font-extrabold text-indigo-600 uppercase tracking-wider">Owner (Pusat)</span>
                    <span class="text-[11px] font-semibold text-slate-800 truncate w-full">owner@istanalaundry.com</span>
                </button>
                
                <button @click="fillCredentials('admin.wjk@istanalaundry.com')" class="flex flex-col items-start p-2 text-left bg-white border border-slate-150 rounded-lg hover:border-primary hover:bg-primary/5 transition-all focus:outline-none">
                    <span class="text-[10px] font-extrabold text-emerald-600 uppercase tracking-wider">Admin (Wijaya K.)</span>
                    <span class="text-[11px] font-semibold text-slate-800 truncate w-full">admin.wjk@istanalaundry.com</span>
                </button>

                <button @click="fillCredentials('cashier.wjk@istanalaundry.com')" class="flex flex-col items-start p-2 text-left bg-white border border-slate-150 rounded-lg hover:border-primary hover:bg-primary/5 transition-all focus:outline-none">
                    <span class="text-[10px] font-extrabold text-amber-600 uppercase tracking-wider">Kasir (Wijaya K.)</span>
                    <span class="text-[11px] font-semibold text-slate-800 truncate w-full">cashier.wjk@istanalaundry.com</span>
                </button>

                <button @click="fillCredentials('staff.wjk@istanalaundry.com')" class="flex flex-col items-start p-2 text-left bg-white border border-slate-150 rounded-lg hover:border-primary hover:bg-primary/5 transition-all focus:outline-none">
                    <span class="text-[10px] font-extrabold text-teal-600 uppercase tracking-wider">Staff (Wijaya K.)</span>
                    <span class="text-[11px] font-semibold text-slate-800 truncate w-full">staff.wjk@istanalaundry.com</span>
                </button>
            </div>
            <p class="text-[10px] text-slate-400 text-center mt-2.5">Klik salah satu akun di atas untuk mengisi form secara otomatis (Password: password)</p>
        </div>
    </div>

    <div class="mt-8 pt-4 border-t border-slate-100 flex justify-center text-center">
        <p class="text-[11px] text-slate-400 leading-relaxed">
            Secure connection. Internal authorization required. <br/>
            © 2026 Istana Premium Laundry Service.
        </p>
    </div>
</x-guest-layout>
