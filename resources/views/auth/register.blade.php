<x-guest-layout>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-slate-900 mb-3">
            Register Account
        </h1>
        <p class="text-base text-slate-500">
            Create an account to join the Istana Laundry management portal.
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-5">
        @csrf

        <!-- Name -->
        <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-slate-800" for="name">Full Name</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" style="font-variation-settings: 'FILL' 0;">person</span>
                <input class="w-full pl-12 pr-4 h-[46px] rounded bg-white border border-slate-200 text-slate-800 text-base focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-300" 
                    id="name" 
                    type="text" 
                    name="name" 
                    value="{{ old('name') }}" 
                    placeholder="John Doe" 
                    required 
                    autofocus 
                    autocomplete="name" />
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        <!-- Email Address -->
        <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-slate-800" for="email">Work Email</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" style="font-variation-settings: 'FILL' 0;">mail</span>
                <input class="w-full pl-12 pr-4 h-[46px] rounded bg-white border border-slate-200 text-slate-800 text-base focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-300" 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    placeholder="name@istanalaundry.com" 
                    required 
                    autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        <!-- Password -->
        <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-slate-800" for="password">Password</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" style="font-variation-settings: 'FILL' 0;">lock</span>
                <input class="w-full pl-12 pr-4 h-[46px] rounded bg-white border border-slate-200 text-slate-800 text-base focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-300" 
                    id="password" 
                    type="password" 
                    name="password" 
                    placeholder="••••••••" 
                    required 
                    autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Confirm Password -->
        <div class="flex flex-col gap-1.5">
            <label class="text-sm font-semibold text-slate-800" for="password_confirmation">Confirm Password</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" style="font-variation-settings: 'FILL' 0;">lock_reset</span>
                <input class="w-full pl-12 pr-4 h-[46px] rounded bg-white border border-slate-200 text-slate-800 text-base focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-slate-300" 
                    id="password_confirmation" 
                    type="password" 
                    name="password_confirmation" 
                    placeholder="••••••••" 
                    required 
                    autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between mt-2">
            <a class="text-xs font-semibold text-slate-500 hover:text-slate-900 transition-colors" href="{{ route('login') }}">
                Already registered? Login
            </a>
            
            <button class="px-6 h-[46px] rounded bg-primary hover:bg-primary-hover text-white font-semibold text-sm flex items-center justify-center gap-2 active:scale-[0.98] transition-all shadow-sm" type="submit">
                Register
                <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 0;">arrow_forward</span>
            </button>
        </div>
    </form>

    <div class="mt-12 pt-6 border-t border-slate-100 flex justify-center text-center">
        <p class="text-xs text-slate-400 leading-relaxed">
            Secure connection. Internal use only. <br/>
            © 2026 Istana Premium Laundry Service.
        </p>
    </div>
</x-guest-layout>
