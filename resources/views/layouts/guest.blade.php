<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="ngrok-skip-browser-warning" content="true">

        <title>{{ config('app.name', 'Istana Laundry') }}</title>

        <!-- Fonts & Icons -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    </body>
</html>
