<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>500 - Kesalahan Server | Istana Laundry</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 font-sans antialiased flex flex-col justify-between selection:bg-primary selection:text-white">

    <!-- Top Minimal Header -->
    <header class="w-full py-6 px-6 sm:px-12 flex items-center justify-between border-b border-slate-200/60 dark:border-slate-800/60 bg-white/60 dark:bg-slate-900/60 backdrop-blur-md">
        <a href="{{ url('/') }}" class="flex items-center gap-3 group">
            <img src="{{ asset('images/logo.webp') }}" alt="Istana Laundry" class="h-9 w-auto object-contain transition-transform group-hover:scale-105">
            <span class="font-black font-display text-xl text-slate-900 dark:text-white tracking-tight">ISTANA LAUNDRY</span>
        </a>
        <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
            <span class="material-symbols-outlined text-base">dashboard</span>
            <span>Dashboard</span>
        </a>
    </header>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-6 sm:p-12">
        <div class="max-w-md w-full text-center space-y-6">
            <!-- Visual Graphic Badge -->
            <div class="relative inline-flex items-center justify-center">
                <div class="w-28 h-28 rounded-3xl bg-rose-500/10 dark:bg-rose-500/20 border border-rose-500/20 flex items-center justify-center shadow-xl shadow-rose-500/5">
                    <span class="material-symbols-outlined text-6xl text-rose-500 animate-bounce">dns</span>
                </div>
                <div class="absolute -bottom-2 -right-2 px-3 py-1 bg-rose-900 text-white dark:bg-rose-950 text-xs font-black rounded-lg shadow-md font-mono border border-rose-700">
                    500 SERVER ERROR
                </div>
            </div>

            <!-- Text & Explanation -->
            <div class="space-y-2">
                <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-slate-900 dark:text-white">
                    Terjadi Kesalahan Server
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto leading-relaxed">
                    Sistem kami mengalami hambatan internal saat memproses perintah Anda. Jangan khawatir, data Anda aman. Silakan coba muat ulang halaman.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="pt-2 flex flex-col sm:flex-row gap-3 justify-center items-center">
                <button onclick="window.location.reload()" type="button" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-primary hover:bg-primary-hover text-white text-xs font-bold shadow-lg shadow-primary/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    <span class="material-symbols-outlined text-base">refresh</span>
                    <span>Muat Ulang Halaman</span>
                </button>
                <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold transition-all shadow-sm">
                    <span class="material-symbols-outlined text-base">home</span>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 text-center text-2xs text-slate-400 dark:text-slate-600 border-t border-slate-200/40 dark:border-slate-800/40">
        &copy; {{ date('Y') }} Istana Laundry Enterprise Suite. Hak Cipta Dilindungi.
    </footer>

</body>
</html>
