<section class="space-y-4">
    <header class="pb-3 border-b border-slate-100 dark:border-slate-800">
        <h2 class="text-base font-extrabold font-display text-slate-800 dark:text-slate-100">
            Informasi Pribadi & Kontak
        </h2>
        <p class="text-xs font-semibold text-slate-400 mt-0.5">
            Perbarui nama akun pengguna dan alamat surel (email) utama Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Nama Lengkap</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">person</span>
                <input id="name" name="name" type="text" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            </div>
            <x-input-error class="mt-1" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Alamat Email / Surel</label>
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">mail</span>
                <input id="email" name="email" type="email" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-semibold focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition-all" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            </div>
            <x-input-error class="mt-1" :messages="$errors->get('email')" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-primary text-white font-bold text-xs rounded-xl hover:bg-primary-hover transition-all shadow-md flex items-center gap-2 cursor-pointer">
                <span class="material-symbols-outlined text-base">save</span> Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-xs font-extrabold text-emerald-600 flex items-center gap-1">
                    <span class="material-symbols-outlined text-base">check_circle</span> Tersimpan
                </p>
            @endif
        </div>
    </form>
</section>
