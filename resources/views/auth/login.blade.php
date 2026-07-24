<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-3">
            Sign In
        </h1>
        <p class="text-base text-slate-500">
            Welcome back to the Istana Laundry system. Please enter your credentials to proceed.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-6">
        @csrf

        <!-- Email Address -->
        <div class="flex flex-col gap-2">
            <label class="text-sm font-semibold text-slate-800" for="email">Work Email</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" style="font-variation-settings: 'FILL' 0;">mail</span>
                <input class="w-full pl-12 pr-4 h-[48px] rounded bg-white border border-slate-200 text-slate-800 text-base focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-300" 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
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
                <label class="text-sm font-semibold text-slate-800" for="password">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-xs font-semibold text-primary hover:text-primary-hover transition-colors" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" style="font-variation-settings: 'FILL' 0;">lock</span>
                <input class="w-full pl-12 pr-4 h-[48px] rounded bg-white border border-slate-200 text-slate-800 text-base focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-300" 
                    id="password" 
                    type="password" 
                    name="password" 
                    placeholder="••••••••" 
                    required 
                    autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center gap-3 mt-2">
            <input class="w-4 h-4 rounded border-slate-200 text-primary focus:ring-primary cursor-pointer" 
                id="remember_me" 
                type="checkbox" 
                name="remember" />
            <label class="text-xs font-medium text-slate-500 cursor-pointer select-none" for="remember_me">
                Remember my device for 30 days
            </label>
        </div>

        <!-- Submit Button -->
        <button class="mt-4 w-full h-[56px] rounded bg-primary hover:bg-primary-hover text-white font-semibold text-sm flex items-center justify-center gap-2 active:scale-[0.98] transition-all group shadow-sm" type="submit">
            Login ke Sistem
            <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform" style="font-variation-settings: 'FILL' 0;">arrow_forward</span>
        </button>
    </form>

    <div class="mt-12 pt-6 border-t border-slate-100 flex justify-center text-center">
        <p class="text-xs text-slate-400 leading-relaxed">
            Secure connection. Internal use only. <br/>
            © 2026 Istana Premium Laundry Service.
        </p>
    </div>
</x-guest-layout>
