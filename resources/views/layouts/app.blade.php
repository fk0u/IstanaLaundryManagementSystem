<!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ 
          darkMode: localStorage.getItem('darkMode') === 'true', 
          sidebarOpen: false,
          mobileMenuOpen: false,
          desktopSidebarOpen: localStorage.getItem('desktopSidebarOpen') !== 'false'
      }"
      :class="{ 'dark': darkMode }"
      class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#FF6600">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <title>{{ config('app.name', 'Istana Laundry') }}</title>

        <!-- PWA Manifest & Meta -->
        <link rel="manifest" href="/manifest.json">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="application-name" content="Istana Laundry ERP">
        <link rel="apple-touch-icon" href="/favicon.ico">

        <!-- Fonts & Material Icons (Material Design 3 Expressive typography) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

        <!-- Scripts & Styling -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
        <script src="/js/secure-dialog.js"></script>
        <script src="/js/realtime-validation.js"></script>
        <script src="/js/realtime-search.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            [x-cloak] { display: none !important; }
            .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 0px); }
            .material-symbols-outlined {
                font-family: 'Material Symbols Outlined' !important;
                font-weight: normal;
                font-style: normal;
                font-size: 24px;
                line-height: 1;
                letter-spacing: normal;
                text-transform: none;
                display: inline-block;
                white-space: nowrap;
                word-wrap: normal;
                direction: ltr;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                font-feature-settings: 'liga' 1;
            }
        </style>
    </head>
    <body class="font-sans antialiased h-full overflow-x-hidden transition-colors duration-200" style="background: var(--nm-bg); color: var(--text-primary);">
        <!-- Sidebar Navigation (Desktop only: >= 1024px) -->
        <x-sidebar />

        <!-- Mobile & iPad Sidebar Backdrop Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 lg:hidden backdrop-blur-sm"
             style="background: rgba(30, 26, 23, 0.40);"
             x-cloak>
        </div>

        <!-- Content Area Wrapper — Dynamic padding based on desktop sidebar state -->
        <div class="flex flex-col min-h-screen max-w-full overflow-x-hidden transition-all duration-300 ease-in-out"
             :class="{ 'lg:pl-72': desktopSidebarOpen, 'lg:pl-20': !desktopSidebarOpen }">
            <!-- Top Navigation Bar -->
            <x-topbar />

            <!-- Page Main Content -->
            <main class="flex-1 p-3 sm:p-4 md:p-6 lg:p-8 max-w-[1600px] mx-auto w-full pb-28 lg:pb-6">
                {{ $slot }}
            </main>
        </div>

        <!-- Bottom Navigation (Mobile) -->
        <x-bottom-nav />

        <!-- Dedicated Mobile Menu Sheet (Halaman Menu Mobile) -->
        <x-mobile-menu />

        <script>
            // Micro-interactions for premium elements
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.premium-shadow').forEach(card => {
                    card.addEventListener('mouseenter', () => {
                        card.style.transform = 'translateY(-3px)';
                        card.style.transition = 'transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    });
                    card.addEventListener('mouseleave', () => {
                        card.style.transform = 'translateY(0px)';
                    });
                });

                // Auto-dispatch session flash messages as toast
                @if(session('success'))
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: @js(session('success')), type: 'success' } }));
                @endif
                @if(session('error'))
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: @js(session('error')), type: 'error' } }));
                @endif
            });

            // Global scope reference for branch-mismatch warnings in forms
            window.__scopedBranchId = @js(session('scoped_branch_id'));
        </script>
        
        <!-- Global Toast Container (Neumorphism Raised Toast) -->
        <div x-data="{ 
            toasts: [],
            addToast(message, type = 'success') {
                const id = Date.now();
                this.toasts.push({ id, message, type });
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 4000);
            }
        }"
        @toast.window="addToast($event.detail.message, $event.detail.type)"
        class="fixed bottom-20 md:bottom-5 right-4 md:right-5 z-[9999] flex flex-col gap-3 w-[calc(100%-2rem)] md:w-full max-w-sm pointer-events-none"
        x-cloak>
            <template x-for="toast in toasts" :key="toast.id">
                <div x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="translate-y-5 opacity-0 scale-95"
                     x-transition:enter-end="translate-y-0 opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="translate-y-0 opacity-100 scale-100"
                     x-transition:leave-end="translate-y-5 opacity-0 scale-95"
                     class="flex items-center gap-3 p-4 rounded-xl nm-card-sm pointer-events-auto transition-all"
                     style="background: var(--nm-surface-high);">
                     
                     <!-- Icon -->
                     <span class="material-symbols-outlined shrink-0 text-xl" 
                           :class="{
                               'text-emerald-500': toast.type === 'success',
                               'text-rose-500': toast.type === 'error',
                               'text-amber-500': toast.type === 'warning',
                               'text-sky-500': toast.type === 'info'
                           }"
                           x-text="toast.type === 'success' ? 'check_circle' : (toast.type === 'error' ? 'error' : (toast.type === 'warning' ? 'warning' : 'info'))">
                     </span>
                     
                     <!-- Text -->
                     <span class="text-xs font-bold" style="color: var(--text-primary);" x-text="toast.message"></span>
                     
                     <!-- Close button -->
                     <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="ml-auto focus:outline-none" style="color: var(--text-tertiary);">
                         <span class="material-symbols-outlined text-base">close</span>
                     </button>
                </div>
            </template>
        </div>

        <!-- Service Worker Registration for PWA -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js').then(reg => {
                        console.log('PWA Service Worker registered:', reg.scope);
                    }).catch(err => {
                        console.log('PWA Service Worker registration failed:', err);
                    });
                });
            }
        </script>

        @stack('scripts')
    </body>
</html>
