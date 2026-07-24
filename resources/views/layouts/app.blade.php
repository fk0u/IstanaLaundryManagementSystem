<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true', sidebarOpen: false }"
      :class="{ 'dark': darkMode }"
      class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

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
        </style>
    </head>
    <body class="font-sans antialiased bg-surface dark:bg-slate-950 text-on-surface dark:text-slate-200 h-full transition-colors duration-200">
        <!-- Sidebar Navigation -->
        <x-sidebar />

        <!-- Content Area Wrapper -->
        <div class="md:pl-72 flex flex-col min-h-screen">
            <!-- Top Navigation Bar -->
            <x-topbar />

            <!-- Page Main Content -->
            <main class="flex-1 p-6 md:p-10 max-w-[1400px] mx-auto w-full">
                {{ $slot }}
            </main>
        </div>

        <script>
            // Micro-interactions for premium elements
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('.premium-shadow').forEach(card => {
                    card.addEventListener('mouseenter', () => {
                        card.style.transform = 'translateY(-4px)';
                        card.style.transition = 'transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    });
                    card.addEventListener('mouseleave', () => {
                        card.style.transform = 'translateY(0px)';
                    });
                });
            });
        </script>
    </body>
</html>
