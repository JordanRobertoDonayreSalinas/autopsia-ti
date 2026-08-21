@extends('layouts.usuario')
@php
    use App\Helpers\ModuloHelper;
@endphp

@section('title', 'Reporte de Personal de Salud (RR.HH.)')

@section('header-content')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">🩺 Reporte de Personal de Salud (RR.HH.)</h1>
        <p class="text-sm text-slate-500 mt-1">Padrón de trabajadores registrados por establecimiento: profesión, colegiatura, DNIe y SERUMS</p>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- KPIs --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col items-center">
                <span class="text-2xl font-bold text-slate-800">{{ $total }}</span>
                <span class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mt-1">Total (Filtro)</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col items-center">
                <span class="text-2xl font-bold text-sky-600">{{ $totalConDnie }}</span>
                <span class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mt-1">Con DNIe</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col items-center">
                <span class="text-2xl font-bold text-purple-600">{{ $totalSerums }}</span>
                <span class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mt-1">SERUMS</span>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col items-center">
                <span class="text-2xl font-bold text-rose-600">{{ $totalSinColegiatura }}</span>
                <span class="text-[10px] uppercase tracking-widest text-slate-400 font-bold mt-1">Sin Colegiatura</span>
            </div>
        </div>

        {{-- Tarjeta de Filtros --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
            <form method="GET" action="{{ route('usuario.reportes.personal_salud') }}" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Inicio</label>
                        <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Fin</label>
                        <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Tipo</label>
                        <select id="tipo" name="tipo"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 transition-all">
                            <option value="">Todos</option>
                            <option value="ESPECIALIZADO" {{ request('tipo') == 'ESPECIALIZADO' ? 'selected' : '' }}>ESP</option>
                            <option value="NO ESPECIALIZADO" {{ request('tipo') == 'NO ESPECIALIZADO' ? 'selected' : '' }}>NO ESP</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Provincia</label>
                        <select id="provincia" name="provincia"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 transition-all">
                            <option value="">Todas</option>
                            @foreach($provincias as $prov)
                                <option value="{{ $prov }}" {{ request('provincia') == $prov ? 'selected' : '' }}>{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Distrito</label>
                        <select id="distrito" name="distrito"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 transition-all">
                            <option value="">Todos</option>
                            @foreach($distritos as $dist)
                                <option value="{{ $dist }}" {{ request('distrito') == $dist ? 'selected' : '' }}>{{ $dist }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Establ.</label>
                        <select id="establecimiento_id" name="establecimiento_id"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 transition-all">
                            <option value="">Todos</option>
                            @foreach($establecimientos as $est)
                                <option value="{{ $est->id }}" {{ request('establecimiento_id') == $est->id ? 'selected' : '' }}>{{ $est->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Servicio</label>
                        <select name="servicio"
                            class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-sky-500 transition-all">
                            <option value="">Todos</option>
                            @foreach($servicios as $svc)
                                <option value="{{ $svc }}" {{ request('servicio') == $svc ? 'selected' : '' }}>{{ $svc }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 mt-2">
                    <button type="submit"
                        class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-lg hover:bg-slate-700 transition-all flex items-center gap-2">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i> FILTRAR
                    </button>
                    <a href="{{ route('usuario.reportes.personal_salud') }}"
                        class="px-4 py-2 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-200 transition-all flex items-center gap-2">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i> LIMPIAR
                    </a>
                    <div class="ml-auto">
                        @if($trabajadores->count() > 0)
                            <button type="button" onclick="exportarExcel()"
                                class="px-4 py-2 bg-green-50 text-green-600 text-xs font-bold rounded-lg hover:bg-green-100 transition-all flex items-center gap-2">
                                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i> EXPORTAR EXCEL
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
                    <i data-lucide="stethoscope" class="w-4 h-4 text-sky-600"></i>
                    <h3 class="text-sm font-bold text-slate-800">Personal Registrado ({{ $trabajadores->total() }})</h3>
                </div>
            </div>

            @if($trabajadores->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-white border-b border-slate-200 text-slate-500 uppercase font-bold">
                            <tr>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Establecimiento</th>
                                <th class="px-4 py-3">Servicio</th>
                                <th class="px-4 py-3">Trabajador</th>
                                <th class="px-4 py-3">Documento</th>
                                <th class="px-4 py-3">Profesión</th>
                                <th class="px-4 py-3">Colegiatura</th>
                                <th class="px-4 py-3">Contacto</th>
                                <th class="px-4 py-3 text-center">DNIe</th>
                                <th class="px-4 py-3 text-center">SERUMS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($trabajadores as $fila)
                                @php $t = $fila['trabajador']; $est = $fila['establecimiento']; @endphp
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-500">
                                        {{ $fila['fecha'] ? \Carbon\Carbon::parse($fila['fecha'])->format('d/m/Y') : 'N/A' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-slate-700 block">{{ $est->nombre ?? 'N/A' }}</span>
                                        <span class="text-[10px] text-slate-400">{{ $est->distrito ?? '' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $t['servicio'] ?? '—' }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-700">
                                        {{ trim(($t['nombres'] ?? '') . ' ' . ($t['apellido_paterno'] ?? '') . ' ' . ($t['apellido_materno'] ?? '')) ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-500 font-mono">{{ $t['tipo_doc'] ?? 'DNI' }}: {{ $t['doc'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $t['profesion'] ?: '—' }}</td>
                                    <td class="px-4 py-3 text-slate-500">
                                        @if(!empty($t['colegiatura']))
                                            <span class="block">{{ $t['colegio_profesional'] ?: '' }} {{ $t['colegiatura'] }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 text-[10px] font-bold">SIN COLEGIATURA</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-500">
                                        <span class="block">{{ $t['correo'] ?: '—' }}</span>
                                        <span class="block text-[10px]">{{ $t['celular'] ?: '' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if(($t['tiene_dnie'] ?? 'NO') === 'SI')
                                            <span class="px-2 py-0.5 rounded-full bg-sky-50 text-sky-600 text-[10px] font-bold">✓ {{ $t['version_dnie'] ?? '' }}</span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if(($t['es_serums'] ?? 'NO') === 'SI')
                                            <span class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-600 text-[10px] font-bold">{{ $t['periodo_serums'] ?? 'SI' }}</span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100 bg-slate-50/30">
                    {{ $trabajadores->links() }}
                </div>
            @else
                <div class="p-10 text-center">
                    <div class="w-16 h-16 mx-auto mb-3 bg-slate-50 rounded-full flex items-center justify-center">
                        <i data-lucide="user-x" class="w-8 h-8 text-slate-300"></i>
                    </div>
                    <p class="text-slate-500 font-medium">No se encontró personal registrado con los filtros seleccionados</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Formulario oculto para exportar Excel --}}
    <form id="excelForm" method="POST" action="{{ route('usuario.reportes.personal_salud.excel') }}" style="display: none;">
        @csrf
        <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
        <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
        <input type="hidden" name="establecimiento_id" value="{{ request('establecimiento_id') }}">
        <input type="hidden" name="provincia" value="{{ request('provincia') }}">
        <input type="hidden" name="distrito" value="{{ request('distrito') }}">
        <input type="hidden" name="tipo" value="{{ request('tipo') }}">
        <input type="hidden" name="servicio" value="{{ request('servicio') }}">
    </form>
@endsection

@push('scripts')
    <script>
        lucide.createIcons();

        function exportarExcel() { document.getElementById('excelForm').submit(); }

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
            fetch(`{{ route('usuario.reportes.personal_salud.ajax.distritos') }}?${params}`)
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
            fetch(`{{ route('usuario.reportes.personal_salud.ajax.establecimientos') }}?${params}`)
                .then(r => r.json())
                .then(data => {
                    establecimientoSelect.innerHTML = '<option value="">Todos</option>';
                    data.forEach(e => { establecimientoSelect.innerHTML += `<option value="${e.id}">${e.nombre}</option>`; });
                });
        }
    </script>
@endpush
