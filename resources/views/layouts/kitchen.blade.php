<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tampilan Dapur')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Poppins', 'sans-serif'] },
                    colors: {
                        primary: '#F97316',
                        secondary: '#000000',
                        surface: '#FFFFFF',
                        background: '#F9FAFB',
                        success: '#22C55E',
                        danger: '#EF4444',
                        warning: '#F59E0B',
                    }
                }
            }
        }
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #F9FAFB; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #F9FAFB; }
        ::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #F97316; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 10px; }
    </style>
</head>
<body class="antialiased overflow-hidden text-sm bg-background">

    <div x-data="{ sidebarOpen: false }" class="flex h-screen w-full">

        <div x-show="sidebarOpen" style="display: none;" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 z-40 bg-black/50 lg:hidden backdrop-blur-sm"></div>

        @include('components.sidebars.kitchen')

        <div class="flex-1 flex flex-col h-full relative overflow-hidden w-full min-w-0">
            
            <div class="lg:hidden flex items-center justify-between bg-surface border-b border-gray-100 p-4 shrink-0 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                        <i data-lucide="utensils-crossed" class="text-white w-4 h-4"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-secondary leading-tight text-sm">{{ $globalSetting->resto_name ?? 'Self Order System' }}</h1>
                        <p class="text-[10px] text-gray-400">Modul Dapur</p>
                    </div>
                </div>
                <button @click="sidebarOpen = true" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-500 hover:text-primary transition border border-gray-200">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
            </div>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 custom-scrollbar" id="kds-main-container">
                @yield('content')
            </main>
        </div>
    </div>

    <audio id="notifSound" src="{{ asset('sounds/bell.mp3') }}" preload="auto"></audio>

    <script>
        lucide.createIcons();
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const Alert = {
            success: function(message) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: message, timer: 1500, showConfirmButton: false });
            },
            error: function(message) {
                Swal.fire({ icon: 'error', title: 'Oops...', text: message });
            }
        };
    </script>
    @stack('scripts')
</body>
</html>