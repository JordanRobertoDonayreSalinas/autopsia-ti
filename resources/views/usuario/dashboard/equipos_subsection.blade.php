{{-- SUBSECCIÓN: EQUIPOS DE CÓMPUTO --}}
<div x-data="{ mostrarFiltros: true }">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest">Panel de Control: Equipos</h3>
        <button @click="mostrarFiltros = !mostrarFiltros" class="flex items-center gap-2 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition-colors">
            <i data-lucide="sliders-horizontal" class="w-4 h-4"></i>
            <span x-text="mostrarFiltros ? 'Ocultar Filtros' : 'Mostrar Filtros'"></span>
        </button>
    </div>

    {{-- FILTROS (Colapsable) --}}
    <div x-show="mostrarFiltros" x-transition.opacity.duration.300ms class="bg-white rounded-2xl shadow-sm p-5 border border-slate-200 mb-6 relative">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="flex flex-col">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Año y Mes</label>
                <div class="flex gap-2">
                    <select id="eq_anio" class="w-1/2 text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                        <option value="">Todos</option>
                        @foreach($aniosDisponibles as $anio)
                            <option value="{{ $anio }}" {{ $anio == now()->year ? 'selected' : '' }}>{{ $anio }}</option>
                        @endforeach
                    </select>
                    <select id="eq_mes" class="w-1/2 text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                        <option value="" selected>Todos</option>
                        <option value="1">Enero</option><option value="2">Febrero</option><option value="3">Marzo</option>
                        <option value="4">Abril</option><option value="5">Mayo</option><option value="6">Junio</option>
                        <option value="7">Julio</option><option value="8">Agosto</option><option value="9">Septiembre</option>
                        <option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-col">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Tipo de IPRESS</label>
                <select id="eq_tipo" class="text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                    <option value="">Todas</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo }}">{{ $tipo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Provincia</label>
                <select id="eq_provincia" class="text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                    <option value="">Todas</option>
                    @foreach($provincias as $provincia)
                        <option value="{{ $provincia }}">{{ $provincia }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex flex-col">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Distrito</label>
                <select id="eq_distrito" class="text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                    <option value="">Todos</option>
                </select>
            </div>

            <div class="flex flex-col">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1">Establecimiento</label>
                <select id="eq_establecimiento" class="text-xs border-slate-200 rounded-xl px-3 py-2 bg-slate-50 font-semibold focus:ring-indigo-500 transition-all">
                    <option value="">Todos</option>
                    @foreach($establecimientos as $est)
                        <option value="{{ $est->id }}">{{ $est->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2 lg:col-span-3 flex flex-col">
                <div class="flex items-center justify-between mb-1">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Filtro por Módulos (Opcional)</label>
                    <div class="flex gap-2">
                        <button type="button" id="btnSeleccionarTodos" class="text-[9px] font-bold text-indigo-500 hover:text-indigo-700 uppercase">Seleccionar Todos</button>
                        <button type="button" id="btnLimpiarModulos" class="text-[9px] font-bold text-slate-400 hover:text-slate-600 uppercase">Limpiar</button>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-100 max-h-32 overflow-y-auto">
                    @foreach($modulos as $mod)
                        <label class="flex items-center gap-1.5 cursor-pointer group">
                            <input type="checkbox" name="modulos[]" value="{{ $mod['valor'] }}" class="eq_modulo_checkbox w-3.5 h-3.5 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <span class="text-[10px] text-slate-600 group-hover:text-indigo-600 font-medium truncate" title="{{ $mod['nombre'] }}">{{ $mod['nombre'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col justify-end">
                <button id="btnAplicarFiltrosEquipos" class="w-full h-10 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i> Obtener Resultados
                </button>
            </div>
        </div>
    </div>

    {{-- METRICAS RAPIDAS (KPIs) --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="monitor" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Equipos</p>
                <h4 id="eq_kpi_total" class="text-2xl font-black text-slate-800 leading-none mt-1">...</h4>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Operativos</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h4 id="eq_kpi_operativos" class="text-2xl font-black text-slate-800 leading-none">...</h4>
                    <span id="eq_kpi_operativos_pct" class="text-xs font-bold text-emerald-500"></span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="building" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sedes Atendidas</p>
                <h4 id="eq_kpi_sedes" class="text-2xl font-black text-slate-800 leading-none mt-1">...</h4>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="wifi-off" class="w-6 h-6"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Equipos sin Red LAN</p>
                <h4 id="eq_kpi_nolan" class="text-2xl font-black text-slate-800 leading-none mt-1">...</h4>
            </div>
        </div>
    </div>

    {{-- SECCION DATA VIZ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
        
        {{-- COLUMNA 1: HARDWARE (Doughnuts) --}}
        <div class="flex flex-col gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex-1">
                <h4 class="text-sm font-extrabold text-slate-800 mb-4 flex items-center gap-2"><i data-lucide="cpu" class="w-4 h-4 text-indigo-500"></i> Distribución por Tipo</h4>
                <div class="h-[220px] w-full">
                    <canvas id="chartTipo"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex-1">
                <h4 class="text-sm font-extrabold text-slate-800 mb-4 flex items-center gap-2"><i data-lucide="activity" class="w-4 h-4 text-emerald-500"></i> Estado de Conservación</h4>
                <div class="h-[220px] w-full">
                    <canvas id="chartEstado"></canvas>
                </div>
            </div>
        </div>

        {{-- COLUMNA 2: RANKINGS & CONECTIVIDAD (Bars & HTML) --}}
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            {{-- Barras: Módulos y Modelos --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 flex-1">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h4 class="text-sm font-extrabold text-slate-800 mb-1 flex items-center gap-2"><i data-lucide="layers" class="w-4 h-4 text-purple-500"></i> Uso por Módulo</h4>
                    <p class="text-[10px] text-slate-400 mb-4">Módulos con mayor cantidad de equipos asignados</p>
                    <div class="h-[260px] w-full">
                        <canvas id="chartModulo"></canvas>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <h4 class="text-sm font-extrabold text-slate-800 mb-1 flex items-center gap-2"><i data-lucide="align-left" class="w-4 h-4 text-amber-500"></i> Dispositivos Comunes</h4>
                    <p class="text-[10px] text-slate-400 mb-4">Modelos o descripciones más frecuentes</p>
                    <div class="h-[260px] w-full">
                        <canvas id="chartDescripcion"></canvas>
                    </div>
                </div>
            </div>

            {{-- HTML Progress Bars: Infraestructura de Conectividad --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h4 class="text-sm font-extrabold text-slate-800 mb-4 flex items-center gap-2"><i data-lucide="network" class="w-4 h-4 text-blue-500"></i> Entorno de Red y Conectividad</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Conexión a Internet</p>
                        <div id="html_conectividad" class="space-y-3">
                            <div class="animate-pulse h-4 bg-slate-100 rounded"></div>
                            <div class="animate-pulse h-4 bg-slate-100 rounded w-5/6"></div>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Fuente de Datos</p>
                        <div id="html_fuente_wifi" class="space-y-3">
                            <div class="animate-pulse h-4 bg-slate-100 rounded"></div>
                            <div class="animate-pulse h-4 bg-slate-100 rounded w-5/6"></div>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Proveedores</p>
                        <div id="html_proveedor" class="space-y-3">
                            <div class="animate-pulse h-4 bg-slate-100 rounded"></div>
                            <div class="animate-pulse h-4 bg-slate-100 rounded w-5/6"></div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>

    {{-- SECCION ESTABLECIMIENTOS --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-10">
        <h4 class="text-sm font-extrabold text-slate-800 mb-1 flex items-center gap-2"><i data-lucide="building-2" class="w-5 h-5 text-cyan-500"></i> Despliegue por Establecimiento</h4>
        <p class="text-[10px] text-slate-400 mb-6">Equipos desplegados en cada IPRESS o Centro de Salud</p>
        <div class="w-full overflow-x-auto">
            <div class="min-w-[800px] h-auto pb-4">
                <canvas id="chartEstablecimiento"></canvas>
            </div>
        </div>
    </div>

</div>