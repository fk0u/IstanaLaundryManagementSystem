<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-primary text-3xl">account_circle</span>
            <div>
                <h2 class="font-black font-display text-2xl text-slate-800 dark:text-slate-100 leading-tight">
                    Pengaturan Profil & Keamanan Staf
                </h2>
                <p class="text-xs font-semibold text-slate-400">
                    Kelola informasi pribadi, ubah kata sandi, dan privasi akun Anda.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Informational Profile Header -->
            <x-card variant="filled" class="lg:col-span-2 bg-gradient-to-r from-primary-container via-surface-container to-surface border border-primary/10">
                <div class="flex flex-col sm:flex-row items-center gap-6 p-2">
                    <div class="w-20 h-20 rounded-full bg-primary text-white font-black font-display text-3xl flex items-center justify-center shadow-md3-2 shrink-0">
                        {{ substr(auth()->user()->name, 0, 2) }}
                    </div>
                    <div class="space-y-1 text-center sm:text-left min-w-0 flex-1">
                        <span class="inline-block px-3 py-1 text-2xs font-extrabold bg-primary/20 text-primary-on-container rounded-full uppercase tracking-wider">
                            Role: {{ auth()->user()->getRoleNames()->first() ?? 'Staf' }}
                        </span>
                        <h3 class="text-2xl font-black font-display text-slate-900 dark:text-slate-100 truncate">
                            {{ auth()->user()->name }}
                        </h3>
                        <p class="text-xs font-semibold text-slate-500 flex items-center justify-center sm:justify-start gap-1">
                            <span class="material-symbols-outlined text-base text-primary">mail</span>
                            {{ auth()->user()->email }} • Cabang: {{ auth()->user()->branch?->name ?? 'Pusat' }}
                        </p>
                    </div>
                </div>
            </x-card>

            <!-- Update Profile Information Card -->
            <x-card variant="elevated">
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">badge</span>
                        <h3 class="text-base font-black font-display text-slate-800 dark:text-slate-100">Informasi Pribadi</h3>
                    </div>
                </x-slot>
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </x-card>

            <!-- Update Password Card -->
            <x-card variant="elevated">
                <x-slot name="header">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-xl">lock_reset</span>
                        <h3 class="text-base font-black font-display text-slate-800 dark:text-slate-100">Ubah Kata Sandi</h3>
                    </div>
                </x-slot>
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </x-card>

            <!-- Delete User Account Card -->
            <x-card variant="outlined" class="lg:col-span-2 !border-rose-200 dark:!border-rose-900/40">
                <x-slot name="header">
                    <div class="flex items-center gap-2 text-rose-600 dark:text-rose-400">
                        <span class="material-symbols-outlined text-xl">warning</span>
                        <h3 class="text-base font-black font-display">Zona Bahaya (Hapus Akun)</h3>
                    </div>
                </x-slot>
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
