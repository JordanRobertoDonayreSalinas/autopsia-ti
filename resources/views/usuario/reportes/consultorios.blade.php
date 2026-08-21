@extends('layouts.usuario')
@php
    use App\Helpers\ModuloHelper;
@endphp

@section('title', 'Reporte de Consultorios')

@section('header-content')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">🏥 Reporte de Consultorios</h1>
        <p class="text-sm text-slate-500 mt-1">Infraestructura (electricidad, tomas, punto de red, conectividad) y requerimientos de equipos, por consultorio</p>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Tarjeta de Filtros --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <form method="GET" action="{{ route('usuario.reportes.consultorios') }}" class="space-y-4">
                <input type="hidden" name="vista" value="{{ $vista }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Inicio</label>
                        <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Fin</label>
                        <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Tipo</label>
                        <select id="tipo" name="tipo"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">Todos</option>
                            <option value="ESPECIALIZADO" {{ request('tipo') == 'ESPECIALIZADO' ? 'selected' : '' }}>ESP</option>
                            <option value="NO ESPECIALIZADO" {{ request('tipo') == 'NO ESPECIALIZADO' ? 'selected' : '' }}>NO ESP</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Provincia</label>
                        <select id="provincia" name="provincia"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">Todas</option>
                            @foreach($provincias as $prov)
                                <option value="{{ $prov }}" {{ request('provincia') == $prov ? 'selected' : '' }}>{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Distrito</label>
                        <select id="distrito" name="distrito"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">Todos</option>
                            @foreach($distritos as $dist)
                                <option value="{{ $dist }}" {{ request('distrito') == $dist ? 'selected' : '' }}>{{ $dist }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Establ.</label>
                        <select id="establecimiento_id" name="establecimiento_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">Todos</option>
                            @foreach($establecimientos as $est)
                                <option value="{{ $est->id }}" {{ request('establecimiento_id') == $est->id ? 'selected' : '' }}>{{ $est->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Consultorio</label>
                        <select name="tipo_consultorio"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">Todos</option>
                            <option value="FISICO" {{ request('tipo_consultorio') == 'FISICO' ? 'selected' : '' }}>Físico</option>
                            <option value="FUNCIONAL" {{ request('tipo_consultorio') == 'FUNCIONAL' ? 'selected' : '' }}>Funcional</option>
                        </select>
                    </div>

                    @if($vista === 'infraestructura')
                        <div class="flex items-end pb-1">
                            <label class="flex items-center gap-2 cursor-pointer select-none group">
                                <input type="checkbox" name="solo_alertas" value="1" {{ request('solo_alertas') ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500 transition-all">
                                <span class="text-xs font-semibold text-slate-600 group-hover:text-rose-600 transition-colors whitespace-nowrap">
                                    Solo con alertas
                                </span>
                            </label>
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 mt-2">
                    <button type="submit"
                        class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-lg hover:bg-slate-700 transition-all flex items-center gap-2">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i> FILTRAR
                    </button>
                    <a href="{{ route('usuario.reportes.consultorios', ['vista' => $vista]) }}"
                        class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-200 transition-all flex items-center gap-2">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> LIMPIAR
                    </a>

                    <div class="ml-auto flex items-center gap-2">
                        <button type="button" onclick="exportarExcel()"
                            class="px-4 py-2 bg-green-50 text-green-600 text-xs font-bold rounded-lg hover:bg-green-100 transition-all flex items-center gap-2">
                            <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i> EXCEL INFRAESTRUCTURA
                        </button>
                        <button type="button" onclick="exportarRequerimientosExcel()"
                            class="px-4 py-2 bg-amber-50 text-amber-600 text-xs font-bold rounded-lg hover:bg-amber-100 transition-all flex items-center gap-2">
                            <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i> EXCEL REQUERIMIENTOS
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tabs de vista --}}
        <div class="flex items-center gap-2 border-b border-slate-200">
            <a href="{{ request()->fullUrlWithQuery(['vista' => 'infraestructura', 'page' => null]) }}"
                class="px-4 py-2.5 text-xs font-black uppercase tracking-widest border-b-2 transition-all {{ $vista === 'infraestructura' ? 'border-purple-600 text-purple-600' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                <i data-lucide="server" class="w-3.5 h-3.5 inline-block -mt-0.5"></i> Infraestructura
            </a>
            <a href="{{ request()->fullUrlWithQuery(['vista' => 'requerimientos', 'page' => null]) }}"
                class="px-4 py-2.5 text-xs font-black uppercase tracking-widest border-b-2 transition-all {{ $vista === 'requerimientos' ? 'border-amber-600 text-amber-600' : 'border-transparent text-slate-400 hover:text-slate-600' }}">
                <i data-lucide="clipboard-list" class="w-3.5 h-3.5 inline-block -mt-0.5"></i> Requerimientos de Equipos
            </a>
        </div>

        {{-- Resultados --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="{{ $vista === 'infraestructura' ? 'server' : 'clipboard-list' }}" class="w-4 h-4 text-purple-600"></i>
                    <h3 class="text-sm font-bold text-slate-800">
                        {{ $vista === 'infraestructura' ? 'Consultorios' : 'Requerimientos de Equipos' }} ({{ $consultorios->total() }})
                    </h3>
                </div>
            </div>

            @if($consultorios->count() > 0)
                <div class="overflow-x-auto">
                    @if($vista === 'infraestructura')
                        <table class="w-full text-xs text-left">
                            <thead class="bg-white border-b border-slate-200 text-slate-500 uppercase font-bold">
                                <tr>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Establecimiento</th>
                                    <th class="px-4 py-3">Consultorio</th>
                                    <th class="px-4 py-3">Servicio / Depto.</th>
                                    <th class="px-4 py-3">Tipo</th>
                                    <th class="px-4 py-3">Electricidad</th>
                                    <th class="px-4 py-3">Tomas</th>
                                    <th class="px-4 py-3">Punto de Red</th>
                                    <th class="px-4 py-3">Conectividad</th>
                                    <th class="px-4 py-3 text-center">Equipos</th>
                                    <th class="px-4 py-3">Alertas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($consultorios as $c)
                                    @php
                                        $modulo = $c['modulo']; $cabecera = $c['cabecera']; $contenido = $c['contenido'];
                                        $ce = $c['contenidoEfectivo']; $datos = $c['datosConsultorio']; $conect = $c['conectividad'];
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors align-top">
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                            {{ $cabecera->fecha ? \Carbon\Carbon::parse($cabecera->fecha)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-medium text-slate-700 block">{{ $cabecera->establecimiento->nombre ?? 'N/A' }}</span>
                                            <span class="text-[10px] text-slate-400">{{ $cabecera->establecimiento->distrito ?? '' }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="font-bold text-slate-700 block">{{ $contenido['titulo_consultorio'] ?? $modulo->modulo_nombre }}</span>
                                            @if($datos['tipo_consultorio'] === 'FUNCIONAL')
                                                <span class="text-[10px] text-indigo-500 flex items-center gap-1"><i data-lucide="link" class="w-3 h-3"></i> {{ $datos['vinculado_a'] ?: '—' }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $datos['servicio_asociado'] ?: '—' }}
                                            @if($datos['departamento_asociado'])
                                                <span class="block text-[10px] text-slate-400">{{ $datos['departamento_asociado'] }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($datos['tipo_consultorio'] === 'FUNCIONAL')
                                                <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 whitespace-nowrap">FUNCIONAL</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 whitespace-nowrap">FÍSICO</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(strtoupper($ce['cuenta_electricidad'] ?? 'SI') === 'SI')
                                                <span class="px-2 py-0.5 rounded-full bg-green-50 text-green-600">✓ CUENTA</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-600">✗ NO CUENTA</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">
                                            @if(strtoupper($ce['tiene_toma_estabilizada'] ?? 'NO') === 'SI')
                                                <span class="block">Estab.: {{ $ce['toma_estabilizada_internas'] ?? 0 }} int / {{ $ce['toma_estabilizada_externas'] ?? 0 }} ext</span>
                                            @endif
                                            @if(strtoupper($ce['tiene_toma_comercial'] ?? 'NO') === 'SI')
                                                <span class="block">Comerc.: {{ $ce['toma_comercial_internas'] ?? 0 }} int / {{ $ce['toma_comercial_externas'] ?? 0 }} ext</span>
                                            @endif
                                            @if(strtoupper($ce['tiene_toma_estabilizada'] ?? 'NO') !== 'SI' && strtoupper($ce['tiene_toma_comercial'] ?? 'NO') !== 'SI')
                                                <span>—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">
                                            @if(strtoupper($ce['cuenta_punto_red'] ?? 'SI') === 'SI')
                                                ✓ {{ $ce['cantidad_puntos_red'] ?? 1 }} pto(s)
                                            @else
                                                <span class="text-red-500">✗ No cuenta</span>
                                            @endif
                                            @if(strtoupper($ce['requiere_mas_puntos_red'] ?? 'NO') === 'SI')
                                                <span class="block text-[10px] text-amber-600">+{{ $ce['cantidad_puntos_red_requerido'] ?? 1 }} requerido(s)</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $conect['tipo'] ?? 'N/A' }}
                                            @if(!empty($conect['operador']) && $conect['operador'] !== 'N/A')
                                                <span class="block text-[10px] text-slate-400">{{ $conect['operador'] }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $c['cantidadEquipos'] }}</td>
                                        <td class="px-4 py-3">
                                            @forelse($c['alertas'] as $alerta)
                                                <span class="block px-2 py-0.5 mb-1 rounded-full bg-rose-50 text-rose-600 text-[10px] font-bold whitespace-nowrap w-fit">{{ $alerta }}</span>
                                            @empty
                                                <span class="text-green-500 text-[10px] font-bold">✓ SIN ALERTAS</span>
                                            @endforelse
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <table class="w-full text-xs text-left">
                            <thead class="bg-white border-b border-slate-200 text-slate-500 uppercase font-bold">
                                <tr>
                                    <th class="px-4 py-3">Fecha</th>
                                    <th class="px-4 py-3">Establecimiento</th>
                                    <th class="px-4 py-3">Consultorio</th>
                                    <th class="px-4 py-3">Servicio / Depto.</th>
                                    <th class="px-4 py-3">Tipo de Equipo Requerido</th>
                                    <th class="px-4 py-3 text-center">Cant.</th>
                                    <th class="px-4 py-3">Observación</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($consultorios as $fila)
                                    @php $req = $fila['requerimiento']; $datos = $fila['datosConsultorio']; @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                            {{ $fila['fecha'] ? \Carbon\Carbon::parse($fila['fecha'])->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 font-medium text-slate-700">{{ $fila['establecimiento']->nombre ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 font-bold text-slate-700">{{ $fila['titulo_consultorio'] }}</td>
                                        <td class="px-4 py-3 text-slate-500">
                                            {{ $datos['servicio_asociado'] ?: '—' }}
                                            @if($datos['departamento_asociado'])
                                                <span class="block text-[10px] text-slate-400">{{ $datos['departamento_asociado'] }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-slate-700 font-semibold">{{ $req->descripcion }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $req->cantidad ?? 1 }}</td>
                                        <td class="px-4 py-3 text-slate-500">{{ $req->observacion ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <div class="p-4 border-t border-slate-100 bg-slate-50/30">
                    {{ $consultorios->links() }}
                </div>
            @else
                <div class="p-10 text-center">
                    <div class="w-16 h-16 mx-auto mb-3 bg-slate-50 rounded-full flex items-center justify-center">
                        <i data-lucide="{{ $vista === 'infraestructura' ? 'server-off' : 'clipboard-check' }}" class="w-8 h-8 text-slate-300"></i>
                    </div>
                    <p class="text-slate-500 font-medium">
                        {{ $vista === 'infraestructura' ? 'No se encontraron consultorios registrados' : 'No hay requerimientos de equipos pendientes' }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Formularios ocultos --}}
    <form id="excelForm" method="POST" action="{{ route('usuario.reportes.consultorios.excel') }}" style="display: none;">
        @csrf
        <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
        <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
        <input type="hidden" name="establecimiento_id" value="{{ request('establecimiento_id') }}">
        <input type="hidden" name="provincia" value="{{ request('provincia') }}">
        <input type="hidden" name="distrito" value="{{ request('distrito') }}">
        <input type="hidden" name="tipo" value="{{ request('tipo') }}">
        <input type="hidden" name="tipo_consultorio" value="{{ request('tipo_consultorio') }}">
        <input type="hidden" name="solo_alertas" value="{{ request('solo_alertas') }}">
    </form>

    <form id="requerimientosExcelForm" method="POST" action="{{ route('usuario.reportes.consultorios.requerimientos.excel') }}" style="display: none;">
        @csrf
        <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
        <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
        <input type="hidden" name="establecimiento_id" value="{{ request('establecimiento_id') }}">
        <input type="hidden" name="provincia" value="{{ request('provincia') }}">
        <input type="hidden" name="distrito" value="{{ request('distrito') }}">
        <input type="hidden" name="tipo" value="{{ request('tipo') }}">
        <input type="hidden" name="tipo_consultorio" value="{{ request('tipo_consultorio') }}">
    </form>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();

        function exportarExcel() { document.getElementById('excelForm').submit(); }
        function exportarRequerimientosExcel() { document.getElementById('requerimientosExcelForm').submit(); }

        const tipoSelect = document.getElementById('tipo');
        const provinciaSelect = document.getElementById('provincia');
        const distritoSelect = document.getElementById('distrito');
        const establecimientoSelect = document.getElementById('establecimiento_id');

        tipoSelect.addEventListener('change', () => {
            provinciaSelect.value = '';
            distritoSelect.innerHTML = '<option value="">Todos</option>';
            establecimientoSelect.innerHTML = '<option value="">Todos</option>';
        });

        provinciaSelect.addEventListener('change', () => {
            distritoSelect.innerHTML = '<option value="">Todos</option>';
            establecimientoSelect.innerHTML = '<option value="">Todos</option>';
            actualizarDistritos();
            actualizarEstablecimientos();
        });

        distritoSelect.addEventListener('change', () => {
            establecimientoSelect.innerHTML = '<option value="">Todos</option>';
            actualizarEstablecimientos();
        });

        function actualizarDistritos() {
            const params = new URLSearchParams({ tipo: tipoSelect.value, provincia: provinciaSelect.value });
            fetch(`{{ route('usuario.reportes.consultorios.ajax.distritos') }}?${params}`)
                .then(r => r.json())
                .then(data => {
                    distritoSelect.innerHTML = '<option value="">Todos</option>';
                    data.forEach(d => { distritoSelect.innerHTML += `<option value="${d}">${d}</option>`; });
                });
        }

        function actualizarEstablecimientos() {
            const params = new URLSearchParams({
                tipo: tipoSelect.value,
                provincia: provinciaSelect.value,
                distrito: distritoSelect.value
            });
            fetch(`{{ route('usuario.reportes.consultorios.ajax.establecimientos') }}?${params}`)
                .then(r => r.json())
                .then(data => {
                    establecimientoSelect.innerHTML = '<option value="">Todos</option>';
                    data.forEach(e => { establecimientoSelect.innerHTML += `<option value="${e.id}">${e.nombre}</option>`; });
                });
        }
    </script>
@endpush
