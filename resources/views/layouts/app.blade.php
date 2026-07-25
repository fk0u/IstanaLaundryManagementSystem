<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ 
          darkMode: localStorage.getItem('darkMode') === 'true', 
          sidebarOpen: false,
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

        <!-- Fonts & Icons -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

        <!-- Scripts & Styling -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            [x-cloak] { display: none !important; }
            .safe-area-bottom { padding-bottom: env(safe-area-inset-bottom, 0px); }
        </style>
    </head>
    <body class="font-sans antialiased bg-surface dark:bg-slate-950 text-on-surface dark:text-slate-200 h-full transition-colors duration-200">
        <!-- Sidebar Navigation -->
        <x-sidebar />

        <!-- Mobile Sidebar Backdrop Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-slate-950/50 z-40 md:hidden backdrop-blur-[2px]"
             x-cloak>
        </div>

        <!-- Content Area Wrapper — Dynamic padding based on desktop sidebar state -->
        <div class="flex flex-col min-h-screen transition-all duration-300 ease-in-out"
             :class="{ 'md:pl-72': desktopSidebarOpen, 'md:pl-20': !desktopSidebarOpen }">
            <!-- Top Navigation Bar -->
            <x-topbar />

            <!-- Page Main Content -->
            <main class="flex-1 p-3 sm:p-4 md:p-6 lg:p-8 max-w-[1600px] mx-auto w-full pb-24 md:pb-6">
                {{ $slot }}
            </main>
        </div>

        <!-- Bottom Navigation (Mobile) -->
        <x-bottom-nav />

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
            });
        </script>
        
        <!-- Global Toast Container -->
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
                     class="flex items-center gap-3 p-4 rounded-xl border shadow-lg pointer-events-auto bg-white dark:bg-slate-900 border-slate-100 dark:border-slate-800/80 transition-all">
                     
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
                     <span class="text-xs font-bold text-slate-800 dark:text-slate-200" x-text="toast.message"></span>
                     
                     <!-- Close button -->
                     <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="ml-auto text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none">
                         <span class="material-symbols-outlined text-base">close</span>
                     </button>
                </div>
            </template>
        </div>
    </body>
</html>
