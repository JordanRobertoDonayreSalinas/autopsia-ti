@extends('layouts.usuario')
@php
    use App\Helpers\ModuloHelper;
@endphp

@section('title', 'Reporte de Equipos de Cómputo')

@section('header-content')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">📊 Reporte de Equipos de Cómputo</h1>
        <p class="text-sm text-slate-500 mt-1">Filtra y genera reportes de equipos registrados</p>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Tarjeta de Filtros --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <form method="GET" action="{{ route('usuario.reportes.equipos') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-8 gap-3">
                    {{-- Fecha Inicio --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Inicio</label>
                        <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                    </div>

                    {{-- Fecha Fin --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Fin</label>
                        <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                    </div>

                    {{-- Tipo --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Tipo</label>
                        <select id="tipo" name="tipo"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">Todos</option>
                            <option value="ESPECIALIZADO" {{ request('tipo') == 'ESPECIALIZADO' ? 'selected' : '' }}>ESP
                            </option>
                            <option value="NO ESPECIALIZADO" {{ request('tipo') == 'NO ESPECIALIZADO' ? 'selected' : '' }}>NO
                                ESP</option>
                        </select>
                    </div>

                    {{-- Provincia --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Provincia</label>
                        <select id="provincia" name="provincia"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">Todas</option>
                            @foreach($provincias as $prov)
                                <option value="{{ $prov }}" {{ request('provincia') == $prov ? 'selected' : '' }}>{{ $prov }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Distrito --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Distrito</label>
                        <select id="distrito" name="distrito"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">Todos</option>
                            @isset($distritos)
                                @foreach($distritos as $dist)
                                    <option value="{{ $dist }}" {{ request('distrito') == $dist ? 'selected' : '' }}>{{ $dist }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>

                    {{-- Establecimiento --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Establ.</label>
                        <select id="establecimiento_id" name="establecimiento_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">Todos</option>
                            @foreach($establecimientos as $est)
                                <option value="{{ $est->id }}" {{ request('establecimiento_id') == $est->id ? 'selected' : '' }}>
                                    {{ $est->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Módulo --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Módulo</label>
                        <select id="modulo" name="modulo"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">Todos</option>
                            @foreach($modulos as $key => $nombre)
                                <option value="{{ $key }}" {{ request('modulo') == $key ? 'selected' : '' }}>{{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Desc.</label>
                        <select id="descripcion" name="descripcion"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 transition-all">
                            <option value="">Todas</option>
                            @foreach($descripciones as $descripcion)
                                <option value="{{ $descripcion }}" {{ request('descripcion') == $descripcion ? 'selected' : '' }}>
                                    {{ $descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Solo última visita --}}
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none group">
                            <input type="checkbox" name="solo_ultima_visita" value="1"
                                {{ request('solo_ultima_visita') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500 transition-all">
                            <span class="text-xs font-semibold text-slate-600 group-hover:text-purple-600 transition-colors whitespace-nowrap">
                                Solo última visita
                            </span>
                        </label>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 mt-2">
                    <button type="submit"
                        class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-lg hover:bg-slate-700 transition-all flex items-center gap-2">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i> FILTRAR
                    </button>
                    <a href="{{ route('usuario.reportes.equipos') }}"
                        class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-200 transition-all flex items-center gap-2">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> LIMPIAR
                    </a>

                    <div class="ml-auto flex items-center gap-2">
                        @if($equipos->count() > 0)
                            <button type="button" onclick="exportarExcel()"
                                class="px-4 py-2 bg-green-50 text-green-600 text-xs font-bold rounded-lg hover:bg-green-100 transition-all flex items-center gap-2">
                                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i> EXCEL GENERAL
                            </button>
                            <button type="button" onclick="exportarFicha42()"
                                class="px-4 py-2 bg-blue-50 text-blue-600 text-xs font-bold rounded-lg hover:bg-blue-100 transition-all flex items-center gap-2">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i> FICHA 42 (CM 42)
                            </button>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        {{-- Resultados --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i data-lucide="monitor" class="w-4 h-4 text-purple-600"></i>
                    <h3 class="text-sm font-bold text-slate-800">Equipos Registrados ({{ $equipos->total() }})</h3>
                </div>
            </div>

            @if($equipos->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-white border-b border-slate-200 text-slate-500 uppercase font-bold">
                            <tr>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">IPRESS</th>
                                <th class="px-4 py-3">Establecimiento</th>
                                <th class="px-4 py-3">Tipo</th>
                                <th class="px-4 py-3">Departamento</th>
                                <th class="px-4 py-3">Servicio</th>
                                <th class="px-4 py-3">Consultorio / Módulo</th>
                                <th class="px-4 py-3 text-center">Cant.</th>
                                <th class="px-4 py-3">Descripción</th>
                                <th class="px-4 py-3">Especificaciones Técnicas</th>
                                <th class="px-4 py-3">Propiedad</th>
                                <th class="px-4 py-3">N° Serie</th>
                                <th class="px-4 py-3">Observación</th>
                                <th class="px-4 py-3">Conexión</th>
                                <th class="px-4 py-3">Fuente WiFi</th>
                                <th class="px-4 py-3">Proveedor</th>
                                <th class="px-4 py-3">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($equipos as $item)
                                @php
                                    // $item es {equipo, modulo_efectivo}: un mismo equipo compartido
                                    // puede aparecer varias veces, una por cada consultorio funcional
                                    // vinculado que lo comparte (ver ReporteEquiposController).
                                    $equipo = $item->equipo;
                                    $moduloEfectivo = $item->modulo_efectivo;
                                @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ $equipo->cabecera->fecha ? \Carbon\Carbon::parse($equipo->cabecera->fecha)->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-slate-400">
                                        {{ $equipo->cabecera->establecimiento->codigo ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-700">
                                        {{ $equipo->cabecera->establecimiento->nombre ?? 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @php $tipo = ModuloHelper::getTipoEstablecimiento($equipo->cabecera->establecimiento); @endphp
                                        <span
                                            class="px-2 py-0.5 rounded-full {{ $tipo == 'ESPECIALIZADO' ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $tipo }}
                                        </span>
                                    </td>
                                    @php $datosConsultorio = ModuloHelper::getDatosConsultorio($equipo->cabecera, $moduloEfectivo); @endphp
                                    <td class="px-4 py-3 text-slate-500">{{ $datosConsultorio['departamento_asociado'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $datosConsultorio['servicio_asociado'] ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="block font-bold text-slate-700 mb-1">{{ ModuloHelper::getNombreModulo($equipo->cabecera, $moduloEfectivo) }}</span>
                                        @if($datosConsultorio['tipo_consultorio'] === 'FUNCIONAL')
                                            <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 whitespace-nowrap">FUNCIONAL</span>
                                            @if($datosConsultorio['vinculado_a'])
                                                <span class="block text-[10px] text-slate-400 mt-0.5">→ {{ $datosConsultorio['vinculado_a'] }}</span>
                                            @endif
                                        @elseif($datosConsultorio['tipo_consultorio'])
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 whitespace-nowrap">FÍSICO</span>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $equipo->cantidad ?? 0 }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $equipo->descripcion ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-slate-500">
                                        @php $specs = is_array($equipo->especificaciones) ? $equipo->especificaciones : null; @endphp
                                        @if($specs && (!empty($specs['modelo']) || !empty($specs['procesador'])))
                                            <span class="block font-semibold text-slate-700">{{ $specs['modelo'] ?? '—' }}</span>
                                            <span class="block text-[10px]">{{ $specs['procesador'] ?? '' }}</span>
                                            <span class="block text-[10px]">{{ $specs['ram'] ?? '' }} @if(!empty($specs['ram']) && !empty($specs['disco']))&bull;@endif {{ $specs['disco'] ?? '' }}</span>
                                            <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold {{ !empty($specs['autodetectado']) ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-indigo-600' }}">
                                                {{ !empty($specs['autodetectado']) ? 'AUTODETECTADO' : 'MANUAL' }}
                                            </span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">{{ $equipo->propio ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-500 font-mono">{{ $equipo->nro_serie ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $equipo->observacion ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        @php $conect = ModuloHelper::getConectividadActa($equipo->cabecera, $moduloEfectivo); @endphp
                                        <span class="text-slate-500">{{ $conect['tipo'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-slate-500">{{ $conect['fuente'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-slate-500">{{ $conect['operador'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="px-2 py-0.5 rounded-full {{ $equipo->estado == 'Operativo' ? 'bg-green-50 text-green-600' : ($equipo->estado == 'Inoperativo' ? 'bg-red-50 text-red-600' : 'bg-yellow-50 text-yellow-600') }}">
                                            {{ $equipo->estado ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100 bg-slate-50/30">
                    {{ $equipos->appends(request()->query())->links() }}
                </div>
            @else
                <div class="p-10 text-center">
                    <div class="w-16 h-16 mx-auto mb-3 bg-slate-50 rounded-full flex items-center justify-center">
                        <i data-lucide="monitor-off" class="w-8 h-8 text-slate-300"></i>
                    </div>
                    <p class="text-slate-500 font-medium">No se encontraron equipos registrados</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Formularios ocultos --}}
    <form id="excelForm" method="POST" action="{{ route('usuario.reportes.equipos.excel') }}" style="display: none;">
        @csrf
        <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
        <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
        <input type="hidden" name="establecimiento_id" value="{{ request('establecimiento_id') }}">
        <input type="hidden" name="provincia" value="{{ request('provincia') }}">
        <input type="hidden" name="distrito" value="{{ request('distrito') }}">
        <input type="hidden" name="modulo" value="{{ request('modulo') }}">
        <input type="hidden" name="tipo" value="{{ request('tipo') }}">
        <input type="hidden" name="solo_ultima_visita" value="{{ request('solo_ultima_visita') }}">
    </form>

    <form id="ficha42Form" method="POST" action="{{ route('usuario.reportes.equipos.ficha42') }}" style="display: none;">
        @csrf
        <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
        <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
        <input type="hidden" name="establecimiento_id" value="{{ request('establecimiento_id') }}">
        <input type="hidden" name="provincia" value="{{ request('provincia') }}">
        <input type="hidden" name="distrito" value="{{ request('distrito') }}">
        <input type="hidden" name="modulo" value="{{ request('modulo') }}">
        <input type="hidden" name="tipo" value="{{ request('tipo') }}">
        <input type="hidden" name="solo_ultima_visita" value="{{ request('solo_ultima_visita') }}">
    </form>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();

        function exportarExcel() { document.getElementById('excelForm').submit(); }
        function exportarFicha42() { document.getElementById('ficha42Form').submit(); }

        const tipoSelect = document.getElementById('tipo');
        const provinciaSelect = document.getElementById('provincia');
        const distritoSelect = document.getElementById('distrito');
        const establecimientoSelect = document.getElementById('establecimiento_id');
        const moduloSelect = document.getElementById('modulo');
        const descripcionSelect = document.getElementById('descripcion');

        // Lógica de Cascada
        tipoSelect.addEventListener('change', () => {
            provinciaSelect.value = '';
            distritoSelect.innerHTML = '<option value="">Todos</option>';
            establecimientoSelect.innerHTML = '<option value="">Todos</option>';
            actualizarProvincias();
        });

        provinciaSelect.addEventListener('change', () => {
            distritoSelect.innerHTML = '<option value="">Todos</option>';
            establecimientoSelect.innerHTML = '<option value="">Todos</option>';
            actualizarDistritos();
            actualizarEstablecimientos(); // Actualizar establecimientos de la provincia (sin importar distrito)
        });

        distritoSelect.addEventListener('change', () => {
            establecimientoSelect.innerHTML = '<option value="">Todos</option>';
            actualizarEstablecimientos();
        });

        establecimientoSelect.addEventListener('change', () => {
            moduloSelect.innerHTML = '<option value="">Todos</option>';
            descripcionSelect.innerHTML = '<option value="">Todas</option>';
            actualizarModulos();
        });

        moduloSelect.addEventListener('change', () => {
            descripcionSelect.innerHTML = '<option value="">Todas</option>';
            actualizarDescripciones();
        });

        function actualizarProvincias() {
            const params = new URLSearchParams({ tipo: tipoSelect.value });
            fetch(`{{ route('usuario.reportes.equipos.ajax.provincias') }}?${params}`)
                .then(r => r.json())
                .then(data => {
                    provinciaSelect.innerHTML = '<option value="">Todas</option>';
                    data.forEach(p => {
                        provinciaSelect.innerHTML += `<option value="${p}">${p}</option>`;
                    });
                });
        }

        function actualizarDistritos() {
            const params = new URLSearchParams({ tipo: tipoSelect.value, provincia: provinciaSelect.value });
            fetch(`{{ route('usuario.reportes.equipos.ajax.distritos') }}?${params}`)
                .then(r => r.json())
                .then(data => {
                    distritoSelect.innerHTML = '<option value="">Todos</option>';
                    data.forEach(d => {
                        distritoSelect.innerHTML += `<option value="${d}">${d}</option>`;
                    });
                });
        }

        function actualizarEstablecimientos() {
            const params = new URLSearchParams({
                tipo: tipoSelect.value,
                provincia: provinciaSelect.value,
                distrito: distritoSelect.value
            });
            fetch(`{{ route('usuario.reportes.equipos.ajax.establecimientos') }}?${params}`)
                .then(r => r.json())
                .then(data => {
                    establecimientoSelect.innerHTML = '<option value="">Todos</option>';
                    data.forEach(e => {
                        establecimientoSelect.innerHTML += `<option value="${e.id}">${e.nombre}</option>`;
                    });
                });
        }

        function actualizarModulos() {
            const params = new URLSearchParams({
                tipo: tipoSelect.value,
                provincia: provinciaSelect.value,
                distrito: distritoSelect.value,
                establecimiento_id: establecimientoSelect.value
            });
            fetch(`{{ route('usuario.reportes.equipos.ajax.modulos') }}?${params}`)
                .then(r => r.json())
                .then(data => {
                    moduloSelect.innerHTML = '<option value="">Todos</option>';
                    // data es un array de objetos [{valor:..., nombre:...}]
                    data.forEach(m => {
                        moduloSelect.innerHTML += `<option value="${m.valor}">${m.nombre}</option>`;
                    });
                    // Al cargar módulos, también cargar descripciones si hay módulos
                    actualizarDescripciones();
                });
        }

        function actualizarDescripciones() {
            const params = new URLSearchParams({
                tipo: tipoSelect.value,
                provincia: provinciaSelect.value,
                distrito: distritoSelect.value,
                establecimiento_id: establecimientoSelect.value,
                modulo: moduloSelect.value
            });
            fetch(`{{ route('usuario.reportes.equipos.ajax.descripciones') }}?${params}`)
                .then(r => r.json())
                .then(data => {
                    descripcionSelect.innerHTML = '<option value="">Todas</option>';
                    data.forEach(d => {
                        descripcionSelect.innerHTML += `<option value="${d}">${d}</option>`;
                    });
                });
        }

        // Disparar cascada inicial si hay valores seleccionados
        document.addEventListener('DOMContentLoaded', () => {
            if (provinciaSelect.value && !distritoSelect.value) {
                actualizarDistritos();
            }
            if (establecimientoSelect.value) {
                actualizarModulos();
            }
        });
    </script>
@endpush