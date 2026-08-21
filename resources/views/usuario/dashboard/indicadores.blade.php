@extends('layouts.usuario')

@section('title', 'Panel de Indicadores')

@section('content')
    <div class="max-w-7xl mx-auto space-y-8" x-data="{ mostrarFiltros: true }">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800">Panel de Indicadores</h1>
                <p class="text-slate-600 mt-1">Consultorios, RR.HH., conectividad y calidad de datos, en un solo vistazo</p>
            </div>
            <button @click="mostrarFiltros = !mostrarFiltros" class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
                <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
                <span x-text="mostrarFiltros ? 'Ocultar Filtros' : 'Mostrar Filtros'"></span>
            </button>
        </div>

        {{-- FILTROS --}}
        <div x-show="mostrarFiltros" x-transition.opacity.duration.300ms class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Desde</label>
                    <input type="date" id="ind_fecha_inicio" value="{{ $fechaInicio }}"
                        class="text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Hasta</label>
                    <input type="date" id="ind_fecha_fin" value="{{ $fechaFin }}"
                        class="text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Tipo de IPRESS</label>
                    <select id="ind_tipo" class="text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                        <option value="">Todas</option>
                        <option value="ESPECIALIZADO">ESPECIALIZADO</option>
                        <option value="NO ESPECIALIZADO">NO ESPECIALIZADO</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Provincia</label>
                    <select id="ind_provincia" class="text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                        <option value="">Todas</option>
                        @foreach($provincias as $provincia)
                            <option value="{{ $provincia }}">{{ $provincia }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Distrito</label>
                    <select id="ind_distrito" class="text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Establecimiento</label>
                    <select id="ind_establecimiento" class="text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                        <option value="">Todos</option>
                        @foreach($establecimientos as $est)
                            <option value="{{ $est->id }}">{{ $est->nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex justify-end mt-4">
                <button id="btnAplicarFiltrosIndicadores" class="h-10 px-6 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i> Obtener Resultados
                </button>
            </div>
        </div>

        <p id="ind_periodo_texto" class="text-xs font-bold text-slate-400 uppercase tracking-widest"></p>

        {{-- KPIs GENERALES --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="server" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Consultorios</p>
                    <h4 id="ind_kpi_total_consultorios" class="text-2xl font-black text-slate-800 leading-none mt-1">...</h4>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="building" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Consultorios Físicos</p>
                    <h4 id="ind_kpi_fisico" class="text-2xl font-black text-slate-800 leading-none mt-1">...</h4>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="layers" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Consultorios Funcionales</p>
                    <h4 id="ind_kpi_funcional" class="text-2xl font-black text-slate-800 leading-none mt-1">...</h4>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="stethoscope" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Personal Registrado</p>
                    <h4 id="ind_kpi_total_rrhh" class="text-2xl font-black text-slate-800 leading-none mt-1">...</h4>
                </div>
            </div>
        </div>

        {{-- SECCIÓN CONSULTORIOS --}}
        <div>
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Consultorios: Físico vs Funcional</h3>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h4 class="text-sm font-extrabold text-slate-800 mb-4 flex items-center gap-2"><i data-lucide="server" class="w-4 h-4 text-indigo-500"></i> Físico vs Funcional</h4>
                    <div class="h-[220px] w-full">
                        <canvas id="chartConsultorioTipo"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-2">
                    <h4 class="text-sm font-extrabold text-slate-800 mb-1 flex items-center gap-2"><i data-lucide="map" class="w-4 h-4 text-purple-500"></i> Consultorios por Departamento</h4>
                    <p class="text-[10px] text-slate-400 mb-4">Departamentos con mayor cantidad de consultorios registrados</p>
                    <div class="h-[260px] w-full">
                        <canvas id="chartConsultorioDepartamento"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                <a href="{{ route('usuario.reportes.consultorios', ['solo_alertas' => 1]) }}" class="bg-white rounded-2xl p-4 border border-rose-100 shadow-sm hover:border-rose-300 transition-colors">
                    <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest flex items-center gap-1.5"><i data-lucide="zap-off" class="w-3.5 h-3.5"></i> Sin Electricidad</p>
                    <h4 id="ind_alerta_electricidad" class="text-2xl font-black text-slate-800 leading-none mt-2">...</h4>
                </a>
                <a href="{{ route('usuario.reportes.consultorios', ['solo_alertas' => 1]) }}" class="bg-white rounded-2xl p-4 border border-rose-100 shadow-sm hover:border-rose-300 transition-colors">
                    <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest flex items-center gap-1.5"><i data-lucide="wifi-off" class="w-3.5 h-3.5"></i> Sin Conectividad</p>
                    <h4 id="ind_alerta_conectividad" class="text-2xl font-black text-slate-800 leading-none mt-2">...</h4>
                </a>
                <a href="{{ route('usuario.reportes.consultorios', ['solo_alertas' => 1]) }}" class="bg-white rounded-2xl p-4 border border-rose-100 shadow-sm hover:border-rose-300 transition-colors">
                    <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest flex items-center gap-1.5"><i data-lucide="monitor-x" class="w-3.5 h-3.5"></i> Sin Equipos</p>
                    <h4 id="ind_alerta_equipos" class="text-2xl font-black text-slate-800 leading-none mt-2">...</h4>
                </a>
                <a href="{{ route('usuario.reportes.consultorios', ['solo_alertas' => 1]) }}" class="bg-white rounded-2xl p-4 border border-amber-100 shadow-sm hover:border-amber-300 transition-colors">
                    <p class="text-[10px] font-black text-amber-500 uppercase tracking-widest flex items-center gap-1.5"><i data-lucide="network" class="w-3.5 h-3.5"></i> Requiere Más Puntos de Red</p>
                    <h4 id="ind_alerta_puntos_red" class="text-2xl font-black text-slate-800 leading-none mt-2">...</h4>
                </a>
            </div>
        </div>

        {{-- SECCIÓN RR.HH --}}
        <div>
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">RR.HH. / Personal de Salud</h3>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="flex flex-col gap-4">
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Con DNIe</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <h4 id="ind_kpi_dnie" class="text-2xl font-black text-slate-800 leading-none">...</h4>
                            <span id="ind_kpi_dnie_pct" class="text-xs font-bold text-emerald-500"></span>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">SERUMS</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <h4 id="ind_kpi_serums" class="text-2xl font-black text-slate-800 leading-none">...</h4>
                            <span id="ind_kpi_serums_pct" class="text-xs font-bold text-indigo-500"></span>
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sin Colegiatura</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <h4 id="ind_kpi_sin_colegiatura" class="text-2xl font-black text-slate-800 leading-none">...</h4>
                            <span id="ind_kpi_sin_colegiatura_pct" class="text-xs font-bold text-rose-500"></span>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:col-span-2">
                    <h4 class="text-sm font-extrabold text-slate-800 mb-1 flex items-center gap-2"><i data-lucide="users" class="w-4 h-4 text-cyan-500"></i> Personal por Servicio</h4>
                    <p class="text-[10px] text-slate-400 mb-4">Servicios con mayor cantidad de personal registrado</p>
                    <div class="h-[300px] w-full">
                        <canvas id="chartRrhhServicio"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN CONECTIVIDAD --}}
        <div>
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Conectividad e Infraestructura</h3>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tipo de Conexión</p>
                        <div class="h-[200px] w-full">
                            <canvas id="chartConectividadTipo"></canvas>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Fuente de Wifi</p>
                        <div id="ind_html_fuente_wifi" class="space-y-3">
                            <div class="animate-pulse h-4 bg-slate-100 rounded"></div>
                            <div class="animate-pulse h-4 bg-slate-100 rounded w-5/6"></div>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Proveedores</p>
                        <div id="ind_html_proveedor" class="space-y-3">
                            <div class="animate-pulse h-4 bg-slate-100 rounded"></div>
                            <div class="animate-pulse h-4 bg-slate-100 rounded w-5/6"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN AUDITORÍA --}}
        <div>
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Auditoría de Calidad de Datos</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('usuario.auditoria.equipos') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-amber-300 transition-colors flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="wifi-off" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Equipo sin Datos de Conexión</p>
                        <h4 id="ind_kpi_equipo_sin_conexion" class="text-2xl font-black text-slate-800 leading-none mt-1">...</h4>
                    </div>
                </a>
                <a href="{{ route('usuario.auditoria.equipos') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-amber-300 transition-colors flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="server-off" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Conexión sin Equipo</p>
                        <h4 id="ind_kpi_conexion_sin_equipo" class="text-2xl font-black text-slate-800 leading-none mt-1">...</h4>
                    </div>
                </a>
                <a href="{{ route('usuario.auditoria.duplicidad') }}" class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:border-amber-300 transition-colors flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="copy-x" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Posible Duplicidad</p>
                        <h4 id="ind_kpi_duplicados" class="text-2xl font-black text-slate-800 leading-none mt-1">...</h4>
                    </div>
                </a>
            </div>
        </div>
    </div>

    @include('usuario.dashboard.indicadores_scripts')
@endsection
