@extends('layouts.usuario')

@section('title', 'Mapa de Progresión ICATEC')

@section('header-content')
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">
        Mapa de Progresión
    </h1>
    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
        <span>Plataforma</span>
        <span class="text-slate-300">•</span>
        <span>Mapa de Progresión — Implementación → Asistencia → Monitoreo</span>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        .leaflet-container img.leaflet-tile { max-width: none !important; display: inline !important; }
        .leaflet-container img { max-width: none !important; }

        .custom-popup .leaflet-popup-content-wrapper {
            border-radius: 16px; padding: 0; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        .custom-popup .leaflet-popup-content { margin: 0; }
        .custom-popup .leaflet-popup-tip-container { display: none; }

        /* Marcador pulsante para etapa 3 */
        @keyframes glow-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(34,197,94,0.5); }
            70%  { box-shadow: 0 0 0 14px rgba(34,197,94,0); }
            100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
        }
        .marker-completo { animation: glow-pulse 2s infinite; border-radius: 50%; }

        /* Filtro etapa activo */
        .btn-etapa.activo { ring: 2px; transform: scale(1.05); }

        /* Barra de progresión etapas */
        .stage-bar { transition: width 0.6s ease; }

        /* ── MODO FOCO ── */
        #seccion-kpis { transition: opacity .25s, max-height .35s; overflow: hidden; max-height: 9999px; opacity: 1; }

        body.modo-foco #seccion-kpis { max-height: 0 !important; opacity: 0; pointer-events: none; margin: 0 !important; }

        body.modo-foco #mapa-wrapper { border-radius: 0; position: fixed;
            inset: 0; z-index: 800; margin: 0 !important; height: 100dvh !important;
            box-shadow: none; border: none; }
        body.modo-foco #mapa-wrapper #mapa-progresion { height: 100% !important; width: 100% !important; }

        body.modo-foco #seccion-filtros { position: fixed; top: 16px; left: 50%; transform: translateX(-50%);
            z-index: 900; background: rgba(255,255,255,0.96); backdrop-filter: blur(12px);
            box-shadow: 0 8px 40px rgba(0,0,0,0.18); border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.9); max-width: 94vw; width: auto; }

        body.modo-foco #btn-salir-foco { display: flex !important; }
        #btn-salir-foco { display: none; position: fixed; top: 28px; right: 24px;
            z-index: 901; background: #1e293b; color: #fff; border: none;
            border-radius: 50px; padding: 10px 20px; font-size: 11px; font-weight: 900;
            cursor: pointer; box-shadow: 0 8px 32px rgba(0,0,0,0.35);
            letter-spacing: .06em; align-items: center; gap: 8px;
            transition: background .15s; }
        #btn-salir-foco:hover { background: #334155; }

        body.modo-foco #page-wrapper { overflow: hidden; }
    </style>
@endpush

@section('content')
    <div id="page-wrapper" class="max-w-7xl mx-auto space-y-5">

        {{-- ══ PANEL SUPERIOR: KPIs de Progresión ══ --}}
        <div id="seccion-kpis" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">

            {{-- Etapa 0 --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Total</span>
                    <span class="w-3 h-3 rounded-full bg-slate-400 shadow-sm"></span>
                </div>
                <div id="stats-total" class="text-3xl font-black text-slate-700">{{ $contadores['total'] }}</div>
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total IPRESS</div>
                <div class="mt-3 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="stage-bar h-full bg-slate-400 rounded-full"
                         style="width: 100%"></div>
                </div>
            </div>

            {{-- Sin Inicio --}}
            <div class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[9px] font-black text-slate-300 uppercase tracking-widest">Sin Inicio</span>
                    <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                </div>
                <div id="stats-etapa0" class="text-2xl font-black text-slate-400 group-hover:text-slate-500 transition-colors">{{ $contadores['etapa0'] }}</div>
                <div class="text-[9px] font-bold text-slate-400 uppercase tracking-tight mt-1">Sin Inicio</div>
            </div>

            {{-- Etapa 1 --}}
            <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[9px] font-black text-blue-400 uppercase tracking-widest">Implementado</span>
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                </div>
                <div id="stats-etapa1" class="text-2xl font-black text-blue-500 group-hover:text-blue-600 transition-colors">{{ $contadores['etapa1'] }}</div>
                <div class="text-[9px] font-bold text-blue-400 uppercase tracking-tight mt-1">Total EESS</div>
            </div>

            {{-- Etapa 2 --}}
            <div class="bg-white rounded-2xl border border-amber-100 p-5 shadow-sm group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest">Asistencia</span>
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                </div>
                <div id="stats-etapa2" class="text-2xl font-black text-amber-500 group-hover:text-amber-600 transition-colors">{{ $contadores['etapa2'] }}</div>
                <div class="text-[9px] font-bold text-amber-500 uppercase tracking-tight mt-1">Total EESS</div>
            </div>

            {{-- Etapa 3 --}}
            <div class="bg-white rounded-2xl border border-violet-100 p-5 shadow-sm group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[9px] font-black text-violet-500 uppercase tracking-widest">Monitoreo</span>
                    <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                </div>
                <div id="stats-etapa3" class="text-2xl font-black text-violet-500 group-hover:text-violet-600 transition-colors">{{ $contadores['etapa3'] }}</div>
                <div class="text-[9px] font-bold text-violet-500 uppercase tracking-tight mt-1">Total EESS</div>
            </div>

            {{-- Etapa 4 --}}
            <div class="bg-white rounded-2xl border border-emerald-100 p-5 shadow-sm group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest">Ciclo Completo</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                </div>
                <div id="stats-etapa4" class="text-2xl font-black text-emerald-500 group-hover:text-emerald-600 transition-colors">{{ $contadores['etapa4'] }}</div>
                <div class="text-[9px] font-bold text-emerald-500 uppercase tracking-tight mt-1">Total EESS</div>
            </div>
        </div>

        {{-- ══ PANEL DE CONTROL + FILTROS ══ --}}
        <div id="seccion-filtros" class="bg-white rounded-2xl shadow-sm border border-slate-200 transition-all duration-300">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100 cursor-pointer hover:bg-slate-50 transition-colors" onclick="document.getElementById('filtros-content').classList.toggle('hidden'); document.getElementById('icon-filtros-toggle').classList.toggle('rotate-180');">
                <div class="flex items-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4 text-emerald-500"></i>
                    <h3 class="text-[11px] font-black text-slate-600 uppercase tracking-widest">Filtros de Búsqueda</h3>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i data-lucide="chevron-up" id="icon-filtros-toggle" class="w-4 h-4 transition-transform duration-300 rotate-180"></i>
                </button>
            </div>
            
            <div id="filtros-content" class="p-6 hidden">
                <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">

                {{-- Filtros por etapa --}}
                <div class="space-y-3">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="filter" class="w-3 h-3"></i> Filtrar por Etapa
                    </h3>
                    <div class="flex flex-wrap gap-2" id="filtros-etapa">
                        <button data-etapa="" class="btn-etapa activo px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider bg-slate-800 text-white transition-all">
                            Todas
                        </button>
                        <button data-etapa="0" class="btn-etapa px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-slate-400"></span> Sin Inicio
                        </button>
                        <button data-etapa="1" class="btn-etapa px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 hover:bg-blue-100 transition-all flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Implementados
                        </button>
                        <button data-etapa="2" class="btn-etapa px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider bg-amber-50 text-amber-700 hover:bg-amber-100 transition-all flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Con Asistencia
                        </button>
                        <button data-etapa="3" class="btn-etapa px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider bg-violet-50 text-violet-700 hover:bg-violet-100 transition-all flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-violet-500"></span> Con Monitoreo
                        </button>
                        <button data-etapa="4" class="btn-etapa px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition-all flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Ciclo Completo
                        </button>
                    </div>
                </div>

                {{-- Filtros geográficos y de año --}}
                <div class="flex flex-wrap items-end gap-3">
                    <div class="flex flex-col gap-1 border-r border-slate-200 pr-3 mr-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase">Año</label>
                        <select id="filtro-anio" class="text-xs border-slate-200 rounded-xl px-3 py-2 focus:ring-violet-500 transition font-bold text-slate-700 bg-slate-50">
                            <option value="todos" {{ $anioFiltro == 'todos' ? 'selected' : '' }}>Todos los años</option>
                            @foreach($aniosDisponibles as $anio)
                                <option value="{{ $anio }}" {{ $anioFiltro == $anio ? 'selected' : '' }}>{{ $anio }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex flex-col gap-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase">Red</label>
                        <select id="filtro-red" class="text-xs border-slate-200 rounded-xl px-3 py-2 focus:ring-indigo-500 transition">
                            <option value="">Todas</option>
                            @foreach($redes as $red)
                                <option value="{{ $red }}">{{ $red }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase">Microred</label>
                        <select id="filtro-microred" class="text-xs border-slate-200 rounded-xl px-3 py-2 focus:ring-indigo-500 transition">
                            <option value="">Todas</option>
                            @foreach($microredes as $mred)
                                <option value="{{ $mred }}">{{ $mred }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase">Provincia</label>
                        <select id="filtro-provincia" class="text-xs border-slate-200 rounded-xl px-3 py-2 focus:ring-indigo-500 transition">
                            <option value="">Todas</option>
                            @foreach($provincias as $prov)
                                <option value="{{ $prov }}">{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase">Distrito</label>
                        <select id="filtro-distrito" class="text-xs border-slate-200 rounded-xl px-3 py-2 focus:ring-indigo-500 transition">
                            <option value="">Todos</option>
                            @foreach($distritos as $dist)
                                <option value="{{ $dist }}">{{ $dist }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase">Categoría</label>
                        <select id="filtro-categoria" class="text-xs border-slate-200 rounded-xl px-3 py-2 focus:ring-indigo-500 transition">
                            <option value="">Todas</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-[9px] font-black text-slate-400 uppercase">Establecimiento</label>
                        <select id="filtro-establecimiento" class="text-xs border-slate-200 rounded-xl px-3 py-2 focus:ring-indigo-500 transition max-w-[200px]">
                            <option value="">Todos</option>
                            @foreach($establecimientosMap as $e)
                                <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- BOTÓN MODO FOCO --}}
                    <div class="flex items-end">
                        <button id="btn-foco" title="Ver solo mapa"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wide bg-slate-900 text-white hover:bg-emerald-600 transition-all shadow-sm">
                            <svg id="icon-expand" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l5-5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                            <span id="label-foco">Ver solo mapa</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Contador dinámico --}}
            <div id="contador-filtro" class="px-6 pb-6 hidden">
                <span class="text-[10px] text-slate-500 font-semibold bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-100"></span>
            </div>
        </div> <!-- Cierra filtros-content -->
        </div> <!-- Cierra seccion-filtros -->

        {{-- ══ MAPA ══ --}}
        <div id="mapa-wrapper" class="relative rounded-2xl overflow-hidden shadow-xl border border-slate-200" style="height: 560px;">
            <div id="mapa-progresion" class="h-full w-full"></div>

            {{-- LEYENDA FLOTANTE --}}
            <div class="absolute bottom-5 right-5 z-[1000] bg-white/95 backdrop-blur-md p-4 rounded-2xl shadow-2xl border border-slate-100 min-w-[190px]">
                <h4 class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-1.5">
                    <i data-lucide="layers" class="w-3 h-3"></i> Etapa de Progresión
                </h4>
                <div class="space-y-2.5">
                    <div class="flex items-center gap-3">
                        <span class="w-3.5 h-3.5 rounded-full bg-slate-400 border-2 border-white shadow-sm flex-shrink-0"></span>
                        <div>
                            <p class="text-[10px] font-bold text-slate-600 leading-none">Sin inicio</p>
                            <p class="text-[9px] text-slate-400">Sin ningún acta</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-4 h-4 rounded-full bg-blue-500 border-2 border-white shadow-sm flex-shrink-0"></span>
                        <div>
                            <p class="text-[10px] font-bold text-blue-700 leading-none">Implementado</p>
                            <p class="text-[9px] text-blue-400">Listo para Asistencia</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-amber-500 border-2 border-white shadow-sm flex-shrink-0"></span>
                        <div>
                            <p class="text-[10px] font-bold text-amber-700 leading-none">Con Asistencia</p>
                            <p class="text-[9px] text-amber-400">Listo para Monitoreo</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-violet-500 border-2 border-white shadow-sm flex-shrink-0"></span>
                        <div>
                            <p class="text-[10px] font-bold text-violet-700 leading-none">Con Monitoreo</p>
                            <p class="text-[9px] text-violet-400">Monitoreo activo</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-emerald-500 border-2 border-white shadow-sm flex-shrink-0 animate-pulse"></span>
                        <div>
                            <p class="text-[10px] font-bold text-emerald-700 leading-none">⭐ Ciclo Completo</p>
                            <p class="text-[9px] text-emerald-400">Las 3 etapas cubiertas</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Conteo flotante superior izquierdo --}}
            <div id="badge-visible" class="absolute top-4 left-4 z-[1000] bg-white/95 backdrop-blur-md px-4 py-2.5 rounded-xl shadow-lg border border-slate-100">
                <span class="text-[9px] font-black text-slate-400 uppercase">Mostrando</span>
                <span id="badge-count" class="text-sm font-black text-slate-800 ml-1">{{ $establecimientosMap->count() }}</span>
                <span class="text-[9px] font-black text-slate-400 uppercase ml-1">EESS</span>
            </div>
        </div>

    </div>

    {{-- Botón flotante para salir del modo foco --}}
    <button id="btn-salir-foco" aria-label="Salir del modo foco">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        Salir del modo mapa
    </button>


@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function () {

        var map = L.map('mapa-progresion').setView([-14.07, -75.73], 9);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19
        }).addTo(map);

        var establecimientos = @json($establecimientosMap);
        var markers = [];

        /* ── Configuración visual por etapa ── */
        var etapaConfig = {
            0: { color: '#94a3b8', size: 9,  label: 'Sin Inicio' },
            1: { color: '#3b82f6', size: 13, label: 'Implementado' },
            2: { color: '#f59e0b', size: 17, label: 'Con Asistencia' },
            3: { color: '#8b5cf6', size: 21, label: 'Con Monitoreo' },
            4: { color: '#22c55e', size: 26, label: 'Ciclo Completo' },
        };

        /* ── Popup HTML por establecimiento ── */
        function buildPopup(e) {
            var cfg = etapaConfig[e.etapa];

            /* Checkboxes de etapas */
            var check = function(ok, label) {
                return `<div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0 ${ok ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-300'}">
                        ${ok ? '✓' : '○'}
                    </span>
                    <span class="text-[10px] font-semibold ${ok ? 'text-slate-700' : 'text-slate-400'}">${label}</span>
                </div>`;
            };

            /* Módulos implementados */
            var modulosHtml = '';
            if (e.modulos_impl && e.modulos_impl.length > 0) {
                modulosHtml = `<div class="mt-2 flex flex-wrap gap-1">` +
                    e.modulos_impl.map(function(m) {
                        return `<span class="text-[8px] font-bold bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full border border-blue-100">${m}</span>`;
                    }).join('') +
                `</div>`;
            }

            /* Etiqueta de etapa */
            var colorClass = { 0: 'bg-slate-100 text-slate-600', 1: 'bg-blue-100 text-blue-700', 2: 'bg-amber-100 text-amber-700', 3: 'bg-violet-100 text-violet-700', 4: 'bg-emerald-100 text-emerald-700 font-black' };

            return `
            <div class="bg-white min-w-[240px] max-w-[280px]">
                <div class="px-4 pt-4 pb-3 border-b border-slate-100">
                    <h4 class="font-black text-slate-800 text-[13px] leading-tight">${e.nombre}</h4>
                    <p class="text-[10px] text-slate-400 uppercase tracking-wider mt-0.5">${e.distrito || ''} — ${e.provincia || ''}</p>
                    <span class="mt-2 inline-flex text-[9px] font-black px-2.5 py-1 rounded-full uppercase tracking-wider ${colorClass[e.etapa]}">
                        ${cfg.label}
                    </span>
                </div>
                <div class="px-4 py-3 space-y-1.5">
                    ${check(e.tiene_impl, 'Implementación' + (e.total_impl > 0 ? ' (' + e.total_impl + ' mód.)' : ''))}
                    ${modulosHtml}
                    ${check(e.tiene_asist, 'Asistencia Técnica' + (e.total_asistencias > 0 ? ' (' + e.total_asistencias + ' actas)' : ''))}
                    ${check(e.tiene_monitoreo, 'Monitoreo' + (e.total_monitoreos > 0 ? ' (' + e.total_monitoreos + ' actas)' : ''))}
                </div>
                ${e.etapa < 4 ? `<div class="px-4 pb-3"><p class="text-[9px] text-slate-400 bg-slate-50 px-2.5 py-1.5 rounded-lg">Siguiente: ${e.etapa === 0 ? '→ Necesita Implementación' : e.etapa === 1 ? '→ Programar Asistencia Técnica' : e.etapa === 2 ? '→ Programar Monitoreo' : '→ Monitoreo / Continuar'}</p></div>` : `<div class="px-4 pb-3"><p class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2.5 py-1.5 rounded-lg">⭐ Ciclo completo alcanzado</p></div>`}
            </div>`;
        }

        /* ── Crear marcadores ── */
        establecimientos.forEach(function (e) {
            var lat = parseFloat(e.latitud);
            var lon = parseFloat(e.longitud);
            if (Math.abs(lat) > 180) lat /= 100000000;
            if (Math.abs(lon) > 180) lon /= 100000000;
            if (isNaN(lat) || isNaN(lon)) return;

            var cfg = etapaConfig[e.etapa];

            var m = L.circleMarker([lat, lon], {
                radius:      cfg.size / 2,
                fillColor:   cfg.color,
                color:       '#fff',
                weight:      e.etapa === 4 ? 3 : 2,
                opacity:     1,
                fillOpacity: 0.9,
                className:   e.etapa === 4 ? 'marker-completo' : ''
            }).addTo(map).bindPopup(buildPopup(e), { className: 'custom-popup', maxWidth: 300 });

            markers.push({
                marker:    m,
                id:        e.id,
                etapa:     e.etapa,
                tiene_impl:      !!e.tiene_impl,
                tiene_asist:     !!e.tiene_asist,
                tiene_monitoreo: !!e.tiene_monitoreo,
                red:       e.red       || '',
                microred:  e.microred  || '',
                provincia: e.provincia  || '',
                distrito:  e.distrito   || '',
                categoria: e.categoria  || '',
            });
        });

        /* ── FILTROS ── */
        var filtroEtapa     = '';
        var filtroRed       = '';
        var filtroMicrored  = '';
        var filtroProvincia = '';
        var filtroDistrito  = '';
        var filtroCategoria = '';
        var filtroEstId     = '';

        var badgeCount = document.getElementById('badge-count');

        function applyFilters() {
            var group      = L.featureGroup();
            var visibleCnt = 0;
            var totalGeografico = 0;
            var counts = { 0:0, 1:0, 2:0, 3:0, 4:0 };

            markers.forEach(function (m) {
                var matchEtapa = (filtroEtapa === '' || String(m.etapa) === filtroEtapa);

                var matchRed   = (filtroRed   === '' || m.red   === filtroRed);
                var matchMRed  = (filtroMicrored === '' || m.microred === filtroMicrored);
                var matchProv  = (filtroProvincia === '' || m.provincia === filtroProvincia);
                var matchDist  = (filtroDistrito === ''  || m.distrito  === filtroDistrito);
                var matchCat   = (filtroCategoria === '' || m.categoria  === filtroCategoria);
                var matchEst   = (filtroEstId === ''     || String(m.id) === filtroEstId);

                if (matchEtapa && matchRed && matchMRed && matchProv && matchDist && matchCat && matchEst) {
                    if (!map.hasLayer(m.marker)) map.addLayer(m.marker);
                    group.addLayer(m.marker);
                    visibleCnt++;
                } else {
                    if (map.hasLayer(m.marker)) map.removeLayer(m.marker);
                    group.removeLayer(m.marker);
                }
                
                // Los counts superiores en modo exclusivo SIEMPRE se calculan independientemente del botón de etapa presionado
                // esto evita que las demás tarjetas se pongan en cero al clickear una, para poder saltar libremente
                if (matchRed && matchMRed && matchProv && matchDist && matchCat && matchEst) {
                    totalGeografico++;
                    if (m.etapa === 0) counts[0]++;
                    if (m.etapa === 1) counts[1]++;
                    if (m.etapa === 2) counts[2]++;
                    if (m.etapa === 3) counts[3]++;
                    if (m.etapa === 4) counts[4]++;
                }
            });

            // Actualizar números en cards Superiores
            document.getElementById('stats-total').textContent  = totalGeografico;
            document.getElementById('stats-etapa0').textContent = counts[0];
            document.getElementById('stats-etapa1').textContent = counts[1];
            document.getElementById('stats-etapa2').textContent = counts[2];
            document.getElementById('stats-etapa3').textContent = counts[3];
            document.getElementById('stats-etapa4').textContent = counts[4];

            badgeCount.textContent = visibleCnt;

            if (visibleCnt > 0) {
                if (filtroEstId !== '') {
                    markers.forEach(function (m) {
                        if (String(m.id) === filtroEstId) { map.setView(m.marker.getLatLng(), 15); m.marker.openPopup(); }
                    });
                } else if (group.getLayers().length > 0) {
                    var anyFilter = filtroEtapa || filtroRed || filtroMicrored || filtroProvincia || filtroDistrito || filtroCategoria;
                    if (anyFilter) map.fitBounds(group.getBounds(), { padding: [50, 50], maxZoom: 13 });
                }
            }
        }

        /* Botones de etapa */
        document.querySelectorAll('.btn-etapa').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.btn-etapa').forEach(function(b) {
                    b.classList.remove('activo', 'bg-slate-800', 'text-white');
                });
                btn.classList.add('activo');
                filtroEtapa = btn.dataset.etapa;
                applyFilters();
            });
        });

        /* Selects geográficos */
        function updateRelationalOptions() {
            var selectMRed = document.getElementById('filtro-microred');
            var selectDist = document.getElementById('filtro-distrito');
            var selectCat  = document.getElementById('filtro-categoria');
            var selectEst  = document.getElementById('filtro-establecimiento');

            var mredSet = new Set(), distSet = new Set(), catSet = new Set();
            establecimientos.forEach(function (e) {
                var matchRed = (filtroRed === '' || e.red === filtroRed);
                var matchMRed = (filtroMicrored === '' || e.microred === filtroMicrored);
                var matchProv = (filtroProvincia === '' || e.provincia === filtroProvincia);

                if (matchRed && matchProv) {
                    if (e.microred) mredSet.add(e.microred);
                }
                if (matchRed && matchMRed && matchProv) {
                    if (e.distrito) distSet.add(e.distrito);
                    if (e.categoria) catSet.add(e.categoria);
                }
            });

            // Conservar valores seleccionados si siguen siendo válidos lo omito para simplicidad
            
            selectMRed.innerHTML = '<option value="">Todas</option>';
            Array.from(mredSet).sort().forEach(function (mr) {
                selectMRed.innerHTML += `<option value="${mr}" ${mr === filtroMicrored ? 'selected' : ''}>${mr}</option>`;
            });

            selectDist.innerHTML = '<option value="">Todos</option>';
            Array.from(distSet).sort().forEach(function (d) {
                selectDist.innerHTML += `<option value="${d}" ${d === filtroDistrito ? 'selected' : ''}>${d}</option>`;
            });

            selectCat.innerHTML = '<option value="">Todas</option>';
            Array.from(catSet).sort().forEach(function (c) {
                selectCat.innerHTML += `<option value="${c}" ${c === filtroCategoria ? 'selected' : ''}>${c}</option>`;
            });

            selectEst.innerHTML = '<option value="">Todos</option>';
            establecimientos.forEach(function (e) {
                var matchRed = (filtroRed === '' || e.red === filtroRed);
                var matchMRed = (filtroMicrored === '' || e.microred === filtroMicrored);
                var matchProv = (filtroProvincia === '' || e.provincia === filtroProvincia);
                var matchDist = (filtroDistrito === ''  || e.distrito  === filtroDistrito);
                var matchCat  = (filtroCategoria === '' || e.categoria  === filtroCategoria);
                if (matchRed && matchMRed && matchProv && matchDist && matchCat) {
                    selectEst.innerHTML += `<option value="${e.id}" ${String(e.id) === filtroEstId ? 'selected' : ''}>${e.nombre}</option>`;
                }
            });
        }

        document.getElementById('filtro-anio').addEventListener('change', function () {
            // Este filtro recarga la página porque afecta la consulta a base de datos
            window.location.href = "{{ route('usuario.dashboard.general') }}?anio=" + this.value;
        });

        document.getElementById('filtro-red').addEventListener('change', function () {
            filtroRed = this.value; filtroMicrored = ''; filtroDistrito = ''; filtroCategoria = ''; filtroEstId = '';
            updateRelationalOptions(); applyFilters();
        });
        document.getElementById('filtro-microred').addEventListener('change', function () {
            filtroMicrored = this.value; filtroDistrito = ''; filtroCategoria = ''; filtroEstId = '';
            updateRelationalOptions(); applyFilters();
        });
        document.getElementById('filtro-provincia').addEventListener('change', function () {
            filtroProvincia = this.value; filtroDistrito = ''; filtroCategoria = ''; filtroEstId = '';
            updateRelationalOptions(); applyFilters();
        });
        document.getElementById('filtro-distrito').addEventListener('change', function () {
            filtroDistrito = this.value; filtroEstId = '';
            updateRelationalOptions(); applyFilters();
        });
        document.getElementById('filtro-categoria').addEventListener('change', function () {
            filtroCategoria = this.value; filtroEstId = '';
            updateRelationalOptions(); applyFilters();
        });
        document.getElementById('filtro-establecimiento').addEventListener('change', function () {
            filtroEstId = this.value; applyFilters();
        });

        /* ══════════════════════════════════════════════════════
           MODO FOCO
        ══════════════════════════════════════════════════════ */
        var enModoFoco = false;

        function toggleFoco() {
            enModoFoco = !enModoFoco;
            document.body.classList.toggle('modo-foco', enModoFoco);

            var labelBtn  = document.getElementById('label-foco');
            var iconExp   = document.getElementById('icon-expand');
            var btnFoco   = document.getElementById('btn-foco');
            var filtCont  = document.getElementById('filtros-content');
            var filtIcon  = document.getElementById('icon-filtros-toggle');

            if (enModoFoco) {
                labelBtn.textContent = 'Vista normal';
                btnFoco.style.background = '#10b981';
                iconExp.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 9L4 4m0 0h4m-4 0v4m15-4l-5 5m5-5h-4m4 0v4M9 15l-5 5m5-5H5m4 0v4m11-4l-5-5m5 5h-4m4 0v-4"/>';
                
                // Colapsar filtros al entrar a modo foco
                filtCont.classList.add('hidden');
                filtIcon.classList.add('rotate-180');
            } else {
                labelBtn.textContent = 'Ver solo mapa';
                btnFoco.style.background = '';
                iconExp.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>';
                
                // Expandir filtros al salir de modo foco
                filtCont.classList.remove('hidden');
                filtIcon.classList.remove('rotate-180');
            }

            // Forzar que Leaflet recalcule el tamaño después de la animación
            setTimeout(function() { map.invalidateSize(); }, 400);
        }

        document.getElementById('btn-foco').addEventListener('click', toggleFoco);
        document.getElementById('btn-salir-foco').addEventListener('click', toggleFoco);

        // Salir con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && enModoFoco) toggleFoco();
        });

        // Asegurar que inicie expandido en vista normal
        document.getElementById('filtros-content').classList.remove('hidden');
        document.getElementById('icon-filtros-toggle').classList.remove('rotate-180');

    })();
    </script>
@endpush
