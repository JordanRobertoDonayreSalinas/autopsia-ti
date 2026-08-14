<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Usuario')</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

    {{-- Fuentes --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    {{-- Vite (Tailwind + JS principal) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js con Plugins --}}
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Librería para Escáner de Código de Barras (Html5-QRCode) --}}
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .bg-grid-slate {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23cbd5e1' fill-opacity='0.4'%3E%3Cpath opacity='0.5' d='M0 38.59l2.83-2.829-1.414-1.415L0 35.758v2.832zM38.59 40l-2.83-2.828 1.414-1.414L40 38.586V40h-1.41zM0 1.414L1.414 0l1.415 1.414L0 4.242V1.414zM38.586 0l1.414 1.414-2.828 2.828L35.758 0h2.828z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

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

        [x-cloak] {
            display: none !important;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased bg-grid-slate">

    <div class="flex h-screen overflow-hidden">

        {{-- ================= SIDEBAR USUARIO ================= --}}
        <aside class="w-72 shrink-0 bg-slate-900 text-white hidden md:flex flex-col shadow-2xl relative z-30">
        <div class="absolute top-0 left-0 w-full h-[2px] bg-cyan-500"></div>

            <div class="h-20 flex items-center px-5 border-b border-white/[0.07] shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-md border border-cyan-500/20 bg-cyan-500/10 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-cyan-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-6 14h-2v-4H7v-2h4V7h2v4h4v2h-4v4z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="font-black text-sm tracking-[0.08em] text-white uppercase block leading-tight">Autopsia TI</span>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto custom-scroll">
                {{-- DASHBOARD --}}
                <a href="{{ route('usuario.dashboard.general') }}"
                    class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('usuario.dashboard*') ? 'bg-emerald-600/10 text-emerald-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>

                @if(Auth::user()->role === 'admin')
                <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-2">Plataforma</p>

                {{-- GESTIONAR USUARIOS --}}
                <a href="{{ route('admin.users.index') }}"
                    class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600/10 text-indigo-400' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="font-medium">Gestionar Usuarios</span>
                </a>
                @endif

                <a href="{{ route('usuario.perfil') }}"
                    class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('usuario.perfil') ? 'bg-emerald-600/10 text-emerald-400' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="user-circle" class="w-5 h-5"></i>
                    <span class="font-semibold">Mi Perfil</span>
                </a>

                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'operador')
                <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-6">Operaciones</p>
                @endif

                @if(Auth::user()->role === 'admin')
                {{-- ACTAS DE REUNIÓN --}}
                <a href="{{ route('usuario.reuniones.index') }}"
                    class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('usuario.reuniones.*') ? 'bg-indigo-500/10 text-indigo-400' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span class="font-medium">Actas de Reunión</span>
                </a>
                @endif

                @if(Auth::user()->role === 'admin' || Auth::user()->role === 'operador')
                {{-- MONITOREO --}}
                <a href="{{ route('usuario.monitoreo.index') }}"
                    class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('usuario.monitoreo.*') ? 'bg-blue-600/10 text-blue-400' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="activity" class="w-5 h-5"></i>
                    <span class="font-medium">Actas de Diagnóstico Situacional</span>
                </a>

                {{-- ESTABLECIMIENTOS --}}
                <a href="{{ route('usuario.establecimientos.index') }}"
                    class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('usuario.establecimientos.*') ? 'bg-blue-600/10 text-blue-400' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="building" class="w-5 h-5"></i>
                    <span class="font-medium">Establecimientos</span>
                </a>
                @endif

                @if(Auth::user()->role === 'admin')
                {{-- REPORTES (Desplegable) --}}
                <div x-data="{ open: {{ request()->routeIs('usuario.reportes.*', 'usuario.auditoria.*') ? 'true' : 'false' }} }">
                    {{-- Botón Principal --}}
                    <button @click="open = !open" type="button"
                        class="w-full group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('usuario.reportes.*', 'usuario.auditoria.*') ? 'bg-purple-600/10 text-purple-400' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                        <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                        <span class="font-medium flex-1 text-left">Reportes</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-200"
                            :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    {{-- Submenú de Reportes --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="ml-4 mt-1 space-y-1 border-l-2 border-slate-700/50 pl-2" x-cloak>

                        {{-- Equipos de Cómputo --}}
                        <a href="{{ route('usuario.reportes.equipos') }}"
                            class="group relative flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all {{ request()->routeIs('usuario.reportes.equipos') ? 'bg-purple-600/10 text-purple-300' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            <i data-lucide="monitor" class="w-4 h-4"></i>
                            <span class="font-medium text-sm">Equipos de Cómputo</span>
                        </a>

                        {{-- Cronograma de Actividades --}}
                        <a href="{{ route('usuario.reportes.cronograma') }}"
                            class="group relative flex items-center gap-3 px-4 py-2.5 rounded-lg transition-all {{ request()->routeIs('usuario.reportes.cronograma') ? 'bg-purple-600/10 text-purple-300' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                            <i data-lucide="calendar-days" class="w-4 h-4"></i>
                            <span class="font-medium text-sm">Cronograma de Actividades</span>
                        </a>
                    </div>
                </div>
                @endif

                @if(Auth::user()->role === 'visor_cronograma')
                <p class="px-4 text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-3 mt-6">Reportes</p>

                {{-- CRONOGRAMA DE ACTIVIDADES (Acceso directo) --}}
                <a href="{{ route('usuario.reportes.cronograma') }}"
                    class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all {{ request()->routeIs('usuario.reportes.cronograma*') ? 'bg-purple-600/10 text-purple-400' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
                    <i data-lucide="calendar-days" class="w-5 h-5"></i>
                    <span class="font-medium">Cronograma de Actividades</span>
                </a>
                @endif



            </nav>
        </aside>

        {{-- ================= CONTENIDO PRINCIPAL ================= --}}
        <main class="flex-1 flex flex-col h-screen overflow-hidden relative">

            <header
                class="h-20 shrink-0 bg-white border-b border-slate-200 flex items-center justify-between px-8 sticky top-0 z-20 shadow-sm">
                <div>
                    @yield('header-content')
                </div>

                <div class="flex items-center gap-4">
                    <div class="h-8 w-px bg-slate-200 mx-2 hidden sm:block"></div>

                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" @click.away="open = false" type="button"
                            class="flex items-center gap-3 p-1 rounded-xl hover:bg-slate-50 transition-all focus:outline-none group">

                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-bold text-slate-700 leading-tight">{{ Auth::user()->name }}</p>
                                <p class="text-[11px] text-cyan-600 font-semibold">
                                    @switch(Auth::user()->role)
                                        @case('admin') Administrador @break
                                        @case('operador') Operador @break
                                        @case('visor_cronograma') Visor Cronograma @break
                                        @default Usuario
                                    @endswitch
                                </p>
                            </div>

                            <div
                                class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center font-bold shadow-md border-2 border-white uppercase transition-transform group-hover:scale-105">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>

                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform"
                                :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-100 py-2 z-50"
                            x-cloak>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors font-bold text-left">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                    <span>Cerrar Sesión</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto p-8 custom-scroll">
                @yield('content')
            </div>

        </main>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });

        window.refreshLucide = () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    </script>

    {{-- Chart.js para gráficos estadísticos --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        window.openModernDocumentSelectionModal = function(generatedUrl, uploadedUrl) {
            const modalId = 'doc-selection-modal-' + Date.now();
            const modalHtml = `
                <div id="${modalId}" class="fixed inset-0 z-[99999] flex items-center justify-center p-4 transition-all duration-300" style="opacity: 0; pointer-events: none;">
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-md transition-opacity duration-300"></div>
                    
                    <!-- Modal Body -->
                    <div class="relative bg-white/95 backdrop-blur-xl rounded-[32px] p-8 max-w-md w-full shadow-[0_25px_50px_-12px_rgba(0,0,0,0.25)] border border-slate-100/50 transform scale-95 transition-all duration-300 ease-out translate-y-4" id="${modalId}-body">
                        <div class="text-center mb-6">
                            <div class="inline-flex p-3.5 bg-indigo-50 text-indigo-600 rounded-2xl mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-800 tracking-tight leading-snug">Seleccionar Documento Base</h3>
                            <p class="text-xs text-slate-500 mt-1.5 font-medium px-4">Elige qué versión del documento deseas abrir en el editor de firmas visuales.</p>
                        </div>
                        
                        <div class="space-y-3 mb-6">
                            <!-- Opción Generado -->
                            <button class="w-full text-left p-4 rounded-2xl border-2 border-slate-100 hover:border-indigo-500 hover:bg-indigo-50/20 transition-all group flex items-start gap-4 focus:outline-none" id="${modalId}-opt-gen">
                                <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl group-hover:bg-indigo-100 transition-colors shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 tracking-tight">Documento Generado</span>
                                    <span class="block text-[11px] text-slate-400 font-semibold mt-0.5 leading-normal">Carga el documento dinámico con los datos actuales del sistema.</span>
                                </div>
                            </button>
                            
                            <!-- Opción Subido -->
                            <button class="w-full text-left p-4 rounded-2xl border-2 border-slate-100 hover:border-blue-500 hover:bg-blue-50/20 transition-all group flex items-start gap-4 focus:outline-none" id="${modalId}-opt-up">
                                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl group-hover:bg-blue-100 transition-colors shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.2 15c.7-1.2 1-2.5.7-3.9-.3-2-1.9-3.6-3.9-3.9C16.9 3.6 13 1.8 9.5 4.1 6.9 5.8 5.4 8.7 5.4 11.8c-1.4.3-2.6 1.3-3 2.7-.4 1.4.1 2.9 1.2 3.8.8.7 1.8 1.1 2.9 1.1h13.2c1 .1 1.9-.3 2.5-1.1.7-.8.7-2-.1-2.8z"/></svg>
                                </div>
                                <div>
                                    <span class="block text-sm font-bold text-slate-700 tracking-tight">Documento Subido</span>
                                    <span class="block text-[11px] text-slate-400 font-semibold mt-0.5 leading-normal">Carga el archivo PDF que fue subido o firmado previamente.</span>
                                </div>
                            </button>
                        </div>
                        
                        <button class="w-full py-3 text-center text-xs font-bold text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors rounded-xl focus:outline-none" id="${modalId}-opt-cancel">
                            Cancelar
                        </button>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const modalEl = document.getElementById(modalId);
            const bodyEl = document.getElementById(modalId + '-body');
            
            // Trigger animation
            setTimeout(() => {
                modalEl.style.opacity = '1';
                modalEl.style.pointerEvents = 'auto';
                bodyEl.classList.remove('scale-95', 'translate-y-4');
                bodyEl.classList.add('scale-100', 'translate-y-0');
            }, 50);
            
            const closeModal = (targetUrl) => {
                modalEl.style.opacity = '0';
                modalEl.style.pointerEvents = 'none';
                bodyEl.classList.remove('scale-100', 'translate-y-0');
                bodyEl.classList.add('scale-95', 'translate-y-4');
                setTimeout(() => {
                    modalEl.remove();
                    if (targetUrl) window.location.href = targetUrl;
                }, 300);
            };
            
            document.getElementById(modalId + '-opt-gen').onclick = () => closeModal(generatedUrl);
            document.getElementById(modalId + '-opt-up').onclick = () => closeModal(uploadedUrl);
            document.getElementById(modalId + '-opt-cancel').onclick = () => closeModal(null);
            modalEl.querySelector('.absolute').onclick = () => closeModal(null);
        };

        /**
         * Limpia la selección de DNI y tarjetas para evitar arrastrar datos anteriores
         */
        window.limpiarEstadoDni = function() {
            const inputTypeHidden = document.getElementById('tipo_dni_input');
            if (inputTypeHidden) inputTypeHidden.value = '';

            const cardElectronico = document.getElementById('card_electronico');
            const cardAzul = document.getElementById('card_azul');
            const bloqueOpciones = document.getElementById('bloque_opciones_dni');
            const bloqueVersion = document.getElementById('bloque_version_dnie');
            const bloqueFirma = document.getElementById('bloque_firma_digital');

            if (cardElectronico) {
                cardElectronico.classList.remove('border-indigo-600', 'bg-indigo-50');
                cardElectronico.classList.add('border-slate-200', 'bg-white');
            }
            if (cardAzul) {
                cardAzul.classList.remove('border-indigo-600', 'bg-indigo-50');
                cardAzul.classList.add('border-slate-200', 'bg-white');
            }
            if (bloqueOpciones) bloqueOpciones.classList.add('hidden');
            if (bloqueVersion) bloqueVersion.classList.add('hidden');
            if (bloqueFirma) bloqueFirma.classList.add('hidden');

            document.querySelectorAll('input[name="contenido[tipo_dni]"]').forEach(r => r.checked = false);

            const selectVer = document.querySelector('select[name="contenido[version_dnie]"]') ||
                              document.querySelector('select[name="contenido[version_dni]"]') ||
                              document.getElementById('version_dni');
            if (selectVer) selectVer.selectedIndex = 0;

            document.querySelectorAll('input[name="contenido[firma_digital_sihce]"]').forEach(r => r.checked = false);
            document.querySelectorAll('input[name="contenido[firma_digital]"]').forEach(r => r.checked = false);
        };

        /**
         * Rastraea vía API RENIEC si el DNI del profesional es DNIe o DNI Azul
         * y autoselecciona el tipo, la versión y firma digital en el formulario.
         */
        window.verificarYSeleccionarDni = function(dni) {
            const cleanDni = (dni || '').toString().trim();

            if (cleanDni.length !== 8 || !/^\d{8}$/.test(cleanDni)) {
                if (typeof window.limpiarEstadoDni === 'function') window.limpiarEstadoDni();
                return;
            }

            // Limpiar estado previo siempre antes de la nueva consulta
            if (typeof window.limpiarEstadoDni === 'function') window.limpiarEstadoDni();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            fetch(`{{ url('/usuario/ajax/verificar-dnie') }}/${cleanDni}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (!data || data.success === false) {
                        if (typeof selectDniType === 'function') selectDniType('AZUL');
                        return;
                    }

                    const esElectronico = (data.tieneDNIe === true || data.tieneDNIe === 'SI' || data.tieneDNIe === '1');
                    const tipo = esElectronico ? 'ELECTRONICO' : 'AZUL';

                    const sectionDni = document.getElementById('section_dni_detalle');
                    if (sectionDni) sectionDni.classList.remove('hidden');

                    if (typeof selectDniType === 'function') {
                        selectDniType(tipo);
                    }

                    if (typeof toggleDniOptions === 'function') {
                        toggleDniOptions(tipo);
                    }

                    const radioType = document.querySelector(`input[name="contenido[tipo_dni]"][value="${tipo}"]`);
                    if (radioType) {
                        radioType.checked = true;
                        radioType.dispatchEvent(new Event('change'));
                    }

                    const inputTypeHidden = document.getElementById('tipo_dni_input');
                    if (inputTypeHidden) {
                        inputTypeHidden.value = tipo;
                    }

                    if (esElectronico && data.versionDnie) {
                        const verClean = data.versionDnie.toString().toUpperCase();
                        const selectVer = document.querySelector('select[name="contenido[version_dnie]"]') ||
                                          document.querySelector('select[name="contenido[version_dni]"]') ||
                                          document.getElementById('version_dni');
                        if (selectVer) {
                            for (let i = 0; i < selectVer.options.length; i++) {
                                const optVal = selectVer.options[i].value.toUpperCase();
                                if (optVal.includes(verClean)) {
                                    selectVer.selectedIndex = i;
                                    break;
                                }
                            }
                        }
                    }

                    if (esElectronico) {
                        const firmaVal = (data.certificadoVigente === true || data.certificadoVigente === 'SI') ? 'SI' : 'NO';
                        const radioFirma = document.querySelector(`input[name="contenido[firma_digital_sihce]"][value="${firmaVal}"]`) ||
                                           document.querySelector(`input[name="contenido[firma_digital]"][value="${firmaVal}"]`);
                        if (radioFirma) {
                            radioFirma.checked = true;
                        }
                    }

                    if (typeof updateSectionNumbers === 'function') {
                        updateSectionNumbers();
                    }
                })
                .catch(err => {
                    console.error("Error al rastrear DNIe:", err);
                    if (typeof selectDniType === 'function') selectDniType('AZUL');
                });
        };
    </script>

    @stack('scripts')
</body>

</html>