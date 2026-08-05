<x-app-layout>
    <div class="flex flex-col gap-6 max-w-6xl mx-auto">
        <x-page-header title="Pengaturan Profil & Keamanan Akun" :breadcrumbs="['Setelan' => '#', 'Profil Akun' => '/profile']" />

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-bold text-xs flex items-center gap-2">
                <span class="material-symbols-outlined">check_circle</span>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 font-bold text-xs flex items-center gap-2">
                <span class="material-symbols-outlined">error</span>
                {{ session('error') }}
            </div>
        @endif

        <!-- User Profile Identity Hero Banner -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-orange-950 p-6 md:p-8 text-white shadow-xl">
            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
                <!-- Avatar Image or Initial Circle -->
                <div class="relative group shrink-0">
                    @if(auth()->user()->avatar_path)
                        <img src="{{ asset('storage/' . auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}" class="w-28 h-28 rounded-full object-cover shadow-lg ring-4 ring-white/20">
                    @else
                        <div class="w-28 h-28 rounded-full bg-gradient-to-tr from-orange-600 to-amber-400 text-white font-black font-display text-4xl flex items-center justify-center shadow-lg ring-4 ring-white/20">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </div>
                    @endif

                    <!-- Upload Button Overlay -->
                    <label for="avatar-input" class="absolute inset-0 rounded-full bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center cursor-pointer text-white text-[10px] font-bold">
                        <span class="material-symbols-outlined text-xl">photo_camera</span>
                        <span>Ubah Foto</span>
                    </label>
                </div>

                <div class="space-y-2 text-center md:text-left flex-1">
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2">
                        <span class="px-3 py-1 bg-orange-500/20 text-orange-400 border border-orange-500/30 rounded-full text-2xs font-extrabold uppercase tracking-wider">
                            Role: {{ auth()->user()->getRoleNames()->first() ?? 'Staf' }}
                        </span>
                        @if(auth()->user()->two_factor_confirmed_at)
                            <span class="px-3 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 rounded-full text-2xs font-extrabold uppercase tracking-wider flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> 2FA TOTP Aktif
                            </span>
                        @else
                            <span class="px-3 py-1 bg-amber-500/20 text-amber-400 border border-amber-500/30 rounded-full text-2xs font-extrabold uppercase tracking-wider flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span> 2FA Belum Aktif
                            </span>
                        @endif
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

                    <!-- Hidden Form for Avatar Upload -->
                    <form action="{{ route('profile.avatar.update') }}" method="POST" enctype="multipart/form-data" class="pt-2">
                        @csrf
                        <input type="file" id="avatar-input" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                            <label for="avatar-input" class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold border border-white/20 transition-all cursor-pointer">
                                <span class="material-symbols-outlined text-base">upload</span>
                                Upload & Kompres WebP (&lt;200KB)
                            </label>
                            @if(auth()->user()->avatar_path)
                                <span class="text-2xs text-slate-400 font-medium">Format: WebP (Teroptimasi)</span>
                            @endif
                        </div>
                    </form>
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

            <!-- Right Side: 2FA Two-Factor Security & Account Metadata -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Two-Factor Authentication (2FA TOTP) Card -->
                <x-card title="Autentikasi 2FA (Google Authenticator)">
                    <div class="space-y-4 text-xs">
                        <p class="text-slate-600 dark:text-slate-400 text-2xs leading-relaxed">
                            Amankan akun Anda dari peretasan dengan memverifikasi 6 digit OTP dari aplikasi Google Authenticator, Authy, atau 1Password setiap kali login.
                        </p>

                        @if(auth()->user()->two_factor_confirmed_at)
                            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 space-y-3">
                                <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-bold">
                                    <span class="material-symbols-outlined text-lg">verified_user</span>
                                    <span>2FA Status: AKTIF</span>
                                </div>
                                <p class="text-2xs text-slate-500">Akun Anda telah dilindungi enkripsi TOTP RFC 6238.</p>

                                <form action="{{ route('profile.2fa.disable') }}" method="POST" class="pt-2 border-t border-emerald-500/20 space-y-2">
                                    @csrf
                                    <x-input-label for="current_password_2fa" value="Konfirmasi Password untuk Matikan 2FA" class="!text-2xs" />
                                    <div class="flex gap-2">
                                        <x-text-input type="password" id="current_password_2fa" name="current_password" placeholder="Password Anda" required class="text-xs flex-1" />
                                        <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all">
                                            Matikan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 space-y-3">
                                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-bold">
                                    <span class="material-symbols-outlined text-lg">gpp_maybe</span>
                                    <span>2FA Status: Belum Aktif</span>
                                </div>

                                @if($qrCodeUrl)
                                    <div class="text-center space-y-3 pt-2">
                                        <span class="text-2xs font-extrabold uppercase text-slate-500 block">Scan QR Code Berikut:</span>
                                        <div class="p-3 bg-white inline-block rounded-2xl shadow-md border border-slate-200">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($qrCodeUrl) }}" alt="2FA QR Code" class="w-44 h-44 mx-auto">
                                        </div>
                                        <div class="p-2 rounded-xl bg-slate-900 text-amber-400 font-mono text-[10px] tracking-wider select-all">
                                            Secret: {{ $secret }}
                                        </div>

                                        <form action="{{ route('profile.2fa.confirm') }}" method="POST" class="space-y-2 pt-2">
                                            @csrf
                                            <x-input-label for="code" value="Masukkan 6 Digit OTP Authenticator" class="!text-2xs text-center" />
                                            <x-text-input type="text" id="code" name="code" placeholder="123456" maxlength="6" required class="text-center font-mono text-base tracking-widest uppercase w-full" />
                                            <button type="submit" class="w-full py-2 bg-[#FF6600] hover:bg-orange-600 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                                                Aktivasi 2FA Sekarang
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('profile.2fa.enable') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full py-2.5 bg-gradient-to-r from-orange-600 to-amber-500 hover:from-orange-700 hover:to-amber-600 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center justify-center gap-2">
                                            <span class="material-symbols-outlined text-base">qr_code_2</span>
                                            Setup 2FA Google Authenticator
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif

                        @if(session('recovery_codes'))
                            <div class="p-4 rounded-2xl bg-slate-900 text-white space-y-2">
                                <span class="text-xs font-extrabold text-emerald-400 block">⚠️ Simpan Kode Pemulihan Darurat Anda!</span>
                                <p class="text-[10px] text-slate-300">Gunakan kode ini jika ponsel Anda hilang:</p>
                                <div class="grid grid-cols-2 gap-1 font-mono text-[10px] text-amber-300">
                                    @foreach(session('recovery_codes') as $recCode)
                                        <div>{{ $recCode }}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
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
