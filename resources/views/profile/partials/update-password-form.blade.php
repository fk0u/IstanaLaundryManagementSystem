<section class="space-y-4">
    <header class="pb-3 border-b border-slate-100 dark:border-slate-800">
        <h2 class="text-base font-extrabold font-display text-slate-800 dark:text-slate-100">
            Pembaruan Kata Sandi
        </h2>
        <p class="text-xs font-semibold text-slate-400 mt-0.5">
            Gunakan kombinasi sandi acak dan unik untuk menjaga keamanan akses akun Anda.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Saat Ini</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">lock</span>
                <input id="update_password_current_password" name="current_password" type="password" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all" autocomplete="current-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1" />
        </div>

        <div>
            <label for="update_password_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Kata Sandi Baru</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">key</span>
                <input id="update_password_password" name="password" type="password" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all" autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Konfirmasi Kata Sandi Baru</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">enhanced_encryption</span>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all" autocomplete="new-password" />
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-primary text-white font-bold text-xs rounded-xl hover:bg-primary-hover transition-all shadow-md flex items-center gap-2 cursor-pointer">
                <span class="material-symbols-outlined text-base">lock_reset</span> Perbarui Kata Sandi
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-xs font-extrabold text-emerald-600 flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">check_circle</span> Kata Sandi Diperbarui
                </p>
            @endif
        </div>
    </form>
</section>
