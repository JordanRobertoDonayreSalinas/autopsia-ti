<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel del Administrador')</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    {{-- Fuentes --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- Vite (Tailwind + JS principal) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js (Fundamental para el Dropdown) --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Fondo de cuadrícula (Grid) */
        .bg-grid-slate {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23cbd5e1' fill-opacity='0.4'%3E%3Cpath opacity='0.5' d='M0 38.59l2.83-2.829-1.414-1.415L0 35.758v2.832zM38.59 40l-2.83-2.828 1.414-1.414L40 38.586V40h-1.41zM0 1.414L1.414 0l1.415 1.414L0 4.242V1.414zM38.586 0l1.414 1.414-2.828 2.828L35.758 0h2.828z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* Scrollbar personalizado fino */
        .custom-scroll::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background: rgba(203, 213, 225, 0.6);
            border-radius: 4px;
        }

        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.8);
        }

        /* x-cloak evita que los elementos se vean antes de que cargue Alpine.js */
        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased bg-grid-slate">

    <div class="flex h-screen overflow-hidden">

        {{-- ================= SIDEBAR ================= --}}
        <aside class="w-72 shrink-0 bg-slate-900 text-white hidden md:flex flex-col shadow-2xl relative z-30">
            {{-- Línea degradada superior --}}
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500">
            </div>

            <div class="h-20 flex items-center px-8 bg-slate-900/50 backdrop-blur-sm border-b border-white/5 shrink-0">
                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center font-bold text-white text-lg shadow-lg shadow-indigo-500/40">
                        A</div>
                    <div><span class="font-bold text-lg tracking-wide block leading-tight">Panel del
                            administrador</span></div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto custom-scroll">
                <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-2">Administración
                </p>

                {{-- GESTIONAR USUARIOS --}}
                <a href="{{ route('admin.users.index') }}"
                    class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all overflow-hidden {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600/10 text-indigo-300 border border-indigo-500/10' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    @if(request()->routeIs('admin.users.*'))
                        <div class="absolute inset-y-0 left-0 w-1 bg-indigo-500 rounded-r-full"></div>
                    @endif
                    <svg class="w-5 h-5 group-hover:text-indigo-400 transition-colors {{ request()->routeIs('admin.users.*') ? 'text-indigo-400' : '' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                    <span class="font-semibold">Gestionar usuarios</span>
                </a>


            </nav>



        </aside>

        {{-- ================= CONTENIDO PRINCIPAL ================= --}}
        <main class="flex-1 flex flex-col h-screen overflow-hidden relative">

            {{-- Header Superior --}}
            <header
                class="h-20 shrink-0 bg-white border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-20 shadow-sm">
                <div>
                    @yield('header-content')
                </div>
                <div class="flex items-center gap-4">
                    <div class="h-8 w-px bg-slate-200 mx-2"></div>

                    {{-- DROPDOWN DE USUARIO --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" type="button"
                            class="flex items-center gap-3 p-1 rounded-xl hover:bg-slate-50 transition-all focus:outline-none">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-slate-700 leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-indigo-600 uppercase tracking-wider font-black">
                                    {{ Auth::user()->role }}
                                </p>
                            </div>
                            <div
                                class="h-10 w-10 rounded-full bg-slate-800 text-white flex items-center justify-center font-bold shadow-md border-2 border-slate-100 uppercase transition-transform group-hover:scale-105">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform"
                                :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        {{-- Menú Desplegable --}}
                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-75"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50"
                            x-cloak>



                            <div class="px-4 py-2">
                                <a href="{{ route('usuario.dashboard.general') }}"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-emerald-600 hover:bg-emerald-50 transition-colors font-bold rounded-xl">
                                    <i data-lucide="user" class="w-4 h-4"></i>
                                    <span>Panel de Usuario</span>
                                </a>
                            </div>
                            <div class="border-t border-slate-100 mx-4"></div>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors font-bold">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    <span>Cerrar Sesión</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Zona de Contenido --}}
            <div class="flex-1 overflow-y-auto p-8 custom-scroll">
                @yield('content')
            </div>

        </main>
    </div>

    {{-- Scripts adicionales (Lucide Icons Global) --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>

    @stack('scripts')
</body>

</html>