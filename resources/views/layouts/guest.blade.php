<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="ngrok-skip-browser-warning" content="true">

        <title>{{ config('app.name', 'Istana Laundry') }}</title>

        <!-- Fonts & Icons (Material Design 3 Expressive typography) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
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
    <body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen selection:bg-primary selection:text-white">
        <main class="min-h-screen flex w-full bg-white">
            <!-- Left Side: Atmospheric Premium Image -->
            <div class="hidden lg:flex lg:w-[55%] relative overflow-hidden bg-slate-900">
                <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuDVvY_NEY7p8Mr4FcpXe83XGY-4xR2a2dd1onO5t5SKwQpmCbJ7RRmOob_A7ch9S2l9Qgk18yfJqkoFYh57MvjZZ5BMgvv0EgEReTFV2n2VhEtGt-G9akPx79xWBU1DnNDwZvkTZwJsZawqaSsbUxeMkz7dc36uJ2O97aK-RC3D8Ie7fn9bVPoIE7gYpqJBbpSnqv5d-bV6srhsXluVuQOWXEfqtkQ2xMg8KewyL3SfyUWtRObqv3sQd83noCTX_KCMTxufchmsowY')"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-slate-950/80 via-slate-900/50 to-transparent"></div>
                <div class="absolute inset-0 bg-slate-950/20 backdrop-blur-[2px]"></div>
                <div class="relative z-10 flex flex-col justify-end p-16 w-full text-white h-full pb-32">
                    <div class="max-w-xl">
                        <span class="inline-block px-3 py-1 mb-6 rounded bg-primary/20 border border-primary/50 text-xs font-semibold uppercase tracking-wider text-orange-200 backdrop-blur-sm">
                            Enterprise Access
                        </span>
                        <h2 class="text-4xl font-bold text-white mb-6 leading-tight">
                            Precision care for the world's most delicate garments.
                        </h2>
                        <p class="text-lg text-slate-300 border-l-2 border-primary pl-4">
                            Istana Laundry management portal. Secure access for authorized personnel only.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Side: Form Content -->
            <div class="w-full lg:w-[45%] flex flex-col justify-center items-center p-5 sm:p-8 md:p-16 relative bg-white">
                <div class="w-full max-w-[420px] flex flex-col">
                    <div class="mb-8 flex justify-start">
                        <img alt="Istana Laundry Logo" class="h-16 w-auto object-contain" src="{{ asset('images/logo.webp') }}"/>
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </main>

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
        class="fixed bottom-5 right-4 md:right-5 z-[9999] flex flex-col gap-3 w-[calc(100%-2rem)] md:w-full max-w-sm pointer-events-none"
        x-cloak>
            <template x-for="toast in toasts" :key="toast.id">
                <div x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="translate-y-5 opacity-0 scale-95"
                     x-transition:enter-end="translate-y-0 opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="translate-y-0 opacity-100 scale-100"
                     x-transition:leave-end="translate-y-5 opacity-0 scale-95"
                     class="flex items-center gap-3 p-4 rounded-2xl border shadow-xl pointer-events-auto bg-white border-slate-200/80 transition-all">
                     
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
                     <span class="text-xs font-bold text-slate-800" x-text="toast.message"></span>
                     
                     <!-- Close button -->
                     <button @click="toasts = toasts.filter(t => t.id !== toast.id)" class="ml-auto text-slate-400 hover:text-slate-600 focus:outline-none">
                         <span class="material-symbols-outlined text-base">close</span>
                     </button>
                </div>
            </template>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                @if(session('success'))
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: @js(session('success')), type: 'success' } }));
                @endif
                @if(session('error'))
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: @js(session('error')), type: 'error' } }));
                @endif
                @if(session('status'))
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: @js(session('status')), type: 'info' } }));
                @endif
                @if(session('warning'))
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: @js(session('warning')), type: 'warning' } }));
                @endif
                @if($errors->any())
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: @js($errors->first()), type: 'error' } }));
                @endif
            });
        </script>
    </body>
</html>
