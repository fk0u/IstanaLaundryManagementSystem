<x-app-layout>
    <div class="flex flex-col gap-6 max-w-6xl mx-auto">
        <x-page-header title="Pengaturan Profil & Keamanan Akun" :breadcrumbs="['Setelan' => '#', 'Profil Akun' => '/profile']" />

        <!-- User Profile Identity Hero Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-orange-950 p-6 md:p-8 text-white shadow-xl">
            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
                <!-- Avatar Circle with Initial -->
                <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-orange-600 to-amber-400 text-white font-black font-display text-4xl flex items-center justify-center shadow-lg shrink-0 ring-4 ring-white/20">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>

                <div class="space-y-2 text-center md:text-left flex-1">
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                        <span class="px-3 py-1 bg-orange-500/20 text-orange-400 border border-orange-500/30 rounded-full text-2xs font-extrabold uppercase tracking-wider">
                            Role: {{ auth()->user()->getRoleNames()->first() ?? 'Staf' }}
                        </span>
                        <span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-full text-2xs font-extrabold uppercase tracking-wider flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Akun Aktif
                        </span>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-black font-display text-white">
                        {{ auth()->user()->name }}
                    </h1>

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-xs text-slate-300 font-medium">
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-orange-400 text-base">mail</span>
                            {{ auth()->user()->email }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-amber-400 text-base">storefront</span>
                            Cabang: {{ auth()->user()->branch?->name ?? 'Semua Cabang (Pusat)' }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sky-400 text-base">schedule</span>
                            Login Terakhir: {{ auth()->user()->last_login_at ? auth()->user()->last_login_at->diffForHumans() : 'Sesi Ini' }}
                        </span>
                    </div>
                </div>
            </div>
            <span class="material-symbols-outlined absolute -right-6 -bottom-10 text-[180px] opacity-10 text-white pointer-events-none">manage_accounts</span>
        </div>

        <!-- Advanced Profile & Security Management Tabs -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Side: Profile Information & Password Edit -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Informational Profile Card -->
                <x-card>
                    @include('profile.partials.update-profile-information-form')
                </x-card>

                <!-- Password Update Card -->
                <x-card>
                    @include('profile.partials.update-password-form')
                </x-card>
            </div>

            <!-- Right Side: Account Security Metadata & Audit Activity -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Account Security Overview Card -->
                <x-card title="Ringkasan Keamanan Akun">
                    <div class="space-y-4 text-xs">
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-1">
                            <span class="text-2xs font-extrabold uppercase text-slate-400 block">Enkripsi Kata Sandi</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">Bcrypt / Argon2 Hash Valid</span>
                            <span class="text-2xs text-emerald-600 dark:text-emerald-400 font-semibold block">✓ Terlindungi standar keamanan ERP</span>
                        </div>

                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-1">
                            <span class="text-2xs font-extrabold uppercase text-slate-400 block">Sesi Login Aktif</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 block">Perangkat Ini (Web Browser)</span>
                            <span class="text-2xs text-slate-500 font-semibold block">Token CSRF & Cookie Terautentikasi</span>
                        </div>

                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 space-y-1">
                            <span class="text-2xs font-extrabold uppercase text-slate-400 block">Hak Akses Sistem (Role)</span>
                            <span class="font-bold text-primary block">{{ auth()->user()->getRoleNames()->first() ?? 'Staf' }}</span>
                            <span class="text-2xs text-slate-500 font-semibold block">Diotorisasi oleh Administrator Istana Laundry</span>
                        </div>
                    </div>
                </x-card>

                <!-- Danger Zone Card -->
                <x-card class="!border-rose-200 dark:!border-rose-900/40">
                    <x-slot name="header">
                        <div class="flex items-center gap-2 text-rose-600 dark:text-rose-400">
                            <span class="material-symbols-outlined text-lg">warning</span>
                            <h3 class="text-xs font-black font-display">Zona Bahaya Akun</h3>
                        </div>
                    </x-slot>
                    <div>
                        @include('profile.partials.delete-user-form')
                    </div>
                </x-card>

            </div>
        </div>
    </div>
</x-app-layout>
