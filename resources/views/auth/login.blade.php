<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-3xl lg:text-4xl font-black font-display text-slate-900 mb-2 tracking-tight">
            Sign In Portal
        </h1>
        <p class="text-xs font-semibold text-slate-500">
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
            <div class="flex flex-col gap-2">
                <label class="text-2xs font-extrabold font-sans text-slate-500 uppercase tracking-widest" for="email">Work Email</label>
                <div class="relative group">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary transition-colors pointer-events-none text-xl" style="font-variation-settings: 'FILL' 0;">mail</span>
                    <input class="w-full pl-12 pr-4 h-[52px] rounded-2xl bg-surface-container border border-surface-outline/50 text-slate-800 text-xs font-semibold focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all placeholder:text-slate-400" 
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
                    <input class="w-full pl-12 pr-12 h-[52px] rounded-2xl bg-surface-container border border-surface-outline/50 text-slate-800 text-xs font-semibold focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 outline-none transition-all placeholder:text-slate-400" 
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

        <!-- Quick Demo Credentials for Dev & Testing -->
        <div class="mt-4 p-5 rounded-expressive border border-surface-outline/30 bg-surface-container-low">
            <div class="flex items-center gap-2 mb-3">
                <span class="material-symbols-outlined text-amber-500 text-xl">verified_user</span>
                <span class="text-2xs font-black font-sans text-slate-600 tracking-widest uppercase">Quick Demo Login</span>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                <button @click="fillCredentials('owner@istanalaundry.com')" class="flex flex-col items-start p-3 text-left bg-white border border-slate-200/80 rounded-2xl hover:border-primary hover:bg-orange-50/50 transition-all focus:outline-none shadow-sm">
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Owner (Pusat)</span>
                    <span class="text-2xs font-bold text-slate-800 truncate w-full mt-0.5">owner@istanalaundry.com</span>
                </button>
                
                <button @click="fillCredentials('admin.wjk@istanalaundry.com')" class="flex flex-col items-start p-3 text-left bg-white border border-slate-200/80 rounded-2xl hover:border-primary hover:bg-orange-50/50 transition-all focus:outline-none shadow-sm">
                    <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest">Admin (Wijaya K.)</span>
                    <span class="text-2xs font-bold text-slate-800 truncate w-full mt-0.5">admin.wjk@istanalaundry.com</span>
                </button>

                <button @click="fillCredentials('cashier.wjk@istanalaundry.com')" class="flex flex-col items-start p-3 text-left bg-white border border-slate-200/80 rounded-2xl hover:border-primary hover:bg-orange-50/50 transition-all focus:outline-none shadow-sm">
                    <span class="text-[10px] font-black text-amber-600 uppercase tracking-widest">Kasir (Wijaya K.)</span>
                    <span class="text-2xs font-bold text-slate-800 truncate w-full mt-0.5">cashier.wjk@istanalaundry.com</span>
                </button>

                <button @click="fillCredentials('staff.wjk@istanalaundry.com')" class="flex flex-col items-start p-3 text-left bg-white border border-slate-200/80 rounded-2xl hover:border-primary hover:bg-orange-50/50 transition-all focus:outline-none shadow-sm">
                    <span class="text-[10px] font-black text-teal-600 uppercase tracking-widest">Staff (Wijaya K.)</span>
                    <span class="text-2xs font-bold text-slate-800 truncate w-full mt-0.5">staff.wjk@istanalaundry.com</span>
                </button>
            </div>
            <p class="text-[10px] text-slate-400 font-semibold text-center mt-3">Klik salah satu akun di atas untuk mengisi form secara otomatis (Password: password)</p>
        </div>
    </div>

    <div class="mt-8 pt-4 border-t border-slate-100 flex justify-center text-center">
        <p class="text-[11px] text-slate-400 font-medium leading-relaxed">
            Secure connection. Internal authorization required. <br/>
            © 2026 Istana Premium Laundry Service.
        </p>
    </div>
</x-guest-layout>
