@extends('layouts.usuario')

@section('title', 'Reporte Consultorios Medicina')

@section('header-content')
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Reporte de Consultorios de Medicina</h1>
    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
        <span>Reportes</span>
        <span class="text-slate-300">•</span>
        <span>Consultorios de Medicina</span>
    </div>
@endsection

@section('content')
    <div class="w-full">
        {{-- ========== KPIs ========== --}}
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-500 p-5 rounded-2xl shadow-xl relative overflow-hidden text-white mb-6">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-3 mr-4">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <i data-lucide="stethoscope" class="w-5 h-5 text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white uppercase tracking-tight">Medicina</h3>
                            <p class="text-[10px] text-indigo-100 uppercase tracking-widest">Resumen de Consultorios</p>
                        </div>
                    </div>

                    {{-- Total Registros --}}
                    <div class="bg-slate-900 text-white rounded-xl px-5 py-2.5 shadow-lg border border-slate-700 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-bold leading-none">{{ count($registros) }}</span>
                        <span class="text-[0.65rem] uppercase tracking-widest text-slate-400 font-semibold mt-1">Registros</span>
                    </div>
                    {{-- Total Consultorios --}}
                    <div class="bg-white/20 backdrop-blur-md rounded-xl px-5 py-2.5 border border-white/30 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-bold leading-none">{{ $registros->sum('num_consultorios') }}</span>
                        <span class="text-[0.65rem] uppercase tracking-widest text-indigo-100 font-semibold mt-1">Consultorios</span>
                    </div>
                </div>
                <div class="flex flex-col md:items-end gap-1 text-sm font-semibold text-white/80">
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar-range" class="w-4 h-4"></i>
                        {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </div>
                    @if(request('ultima_visita') == '1')
                        <div class="text-[10px] bg-indigo-800/50 px-2 py-0.5 rounded text-indigo-100 uppercase tracking-widest border border-indigo-400/30">
                            Filtro: Última Visita Activo
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('usuario.reportes.consultorios_medicina') }}" id="filterForm"
            class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-6 lg:flex lg:items-end gap-3 items-end">
                <div class="lg:flex-1 min-w-0">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Provincia</label>
                    <select name="provincia" id="provinciaSelect"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">TODAS</option>
                        @foreach($provincias as $prov)
                            <option value="{{ $prov }}" {{ request('provincia') == $prov ? 'selected' : '' }}>
                                {{ $prov }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:flex-1 min-w-0">
                    <label
                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Distrito</label>
                    <select name="distrito" id="distritoSelect"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">TODOS</option>
                        @foreach($distritos as $dist)
                            <option value="{{ $dist }}" {{ request('distrito') == $dist ? 'selected' : '' }}>
                                {{ $dist }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:flex-1 min-w-0">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Categoría</label>
                    <select name="categoria" id="categoriaSelect"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">TODAS</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:flex-[1.5] min-w-0">
                    <label
                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Establecimiento</label>
                    <select name="establecimiento_id" id="establecimientoSelect"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">TODOS</option>
                        {{-- AJAX lo llena si es necesario, pero lo dejamos básico --}}
                    </select>
                </div>
                <div class="lg:w-36">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ $fechaInicio }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div class="lg:w-36">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ $fechaFin }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div class="lg:w-36">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Filtro Especial</label>
                    <label class="flex items-center gap-2 cursor-pointer h-[38px] bg-slate-50 border border-slate-200 rounded-xl px-3 text-xs font-bold text-slate-700 hover:bg-slate-100 transition-all">
                        <input type="checkbox" name="ultima_visita" value="1" {{ request('ultima_visita') == '1' ? 'checked' : '' }}
                            class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                        Última Visita
                    </label>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded-xl text-xs transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                    <a href="{{ route('usuario.reportes.consultorios_medicina') }}"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2 px-3 rounded-xl text-xs transition-all flex items-center justify-center">
                        <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </form>

        {{-- Acciones --}}
        <div class="mb-4 flex justify-end">
            <form method="POST" action="{{ route('usuario.reportes.consultorios_medicina.excel') }}">
                @csrf
                <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
                <input type="hidden" name="fecha_fin" value="{{ $fechaFin }}">
                <input type="hidden" name="provincia" value="{{ $provincia }}">
                <input type="hidden" name="distrito" value="{{ $distrito }}">
                <input type="hidden" name="categoria" value="{{ $categoria }}">
                <input type="hidden" name="establecimiento_id" value="{{ $establecimiento_id }}">
                <input type="hidden" name="ultima_visita" value="{{ request('ultima_visita') }}">
                <button type="submit"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl text-xs transition-all shadow-lg shadow-emerald-200 flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> EXPORTAR EXCEL
                </button>
            </form>
        </div>

        {{-- Tabla de Resultados --}}
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-800">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider">Acta / Fecha</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider">Establecimiento</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider">Profesional</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider text-center">Turno</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider text-center">Nro Consultorios</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider">Denominación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($registros as $item)
                            <tr class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-indigo-600 uppercase text-xs">#{{ str_pad($item['acta_id'], 5, '0', STR_PAD_LEFT) }}</span>
                                        <span class="text-[10px] font-medium text-slate-400 mt-0.5 tracking-widest">{{ \Carbon\Carbon::parse($item['fecha_monitoreo'])->format('d/m/Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-600 uppercase text-[10px]">{{ $item['establecimiento'] }}</span>
                                        <span class="text-[9px] text-slate-400 uppercase mt-0.5">{{ $item['distrito'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-slate-600 uppercase text-[10px]">{{ $item['profesional_entrevistado'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-lg text-[9px] font-black border border-slate-200 uppercase tracking-tighter">
                                        {{ $item['turno'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg text-xs font-black border border-indigo-200 shadow-sm">
                                        {{ $item['num_consultorios'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-medium text-slate-500 uppercase">
                                        {{ $item['denominacion_consultorio'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i data-lucide="file-search" class="w-12 h-12 mb-3 text-slate-300"></i>
                                        <p class="text-sm font-bold uppercase tracking-widest">No se encontraron registros</p>
                                        <p class="text-[10px] mt-1">Modifique los filtros de búsqueda para ver resultados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Resumen --}}
            @if(count($registros) > 0)
                <div class="bg-slate-50 border-t border-slate-100 p-4 text-right">
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">
                        Total de Registros: <span class="text-indigo-600 text-xs">{{ count($registros) }}</span>
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Script para cargar establecimientos por distrito (opcional, igual al de duplicidad) --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const provinciaSelect = document.getElementById('provinciaSelect');
            const distritoSelect = document.getElementById('distritoSelect');
            const establecimientoSelect = document.getElementById('establecimientoSelect');
            const selectedEstablecimiento = "{{ $establecimiento_id }}";

            // Cargar distritos al cambiar provincia
            provinciaSelect.addEventListener('change', () => {
                distritoSelect.innerHTML = '<option value="">Cargando...</option>';
                establecimientoSelect.innerHTML = '<option value="">TODOS</option>';
                
                if (!provinciaSelect.value) {
                    distritoSelect.innerHTML = '<option value="">TODOS</option>';
                    return;
                }

                fetch(`{{ route('usuario.reportes.actas.monitoreo.ajax.distritos') }}?provincia=${encodeURIComponent(provinciaSelect.value)}`)
                    .then(res => res.json())
                    .then(data => {
                        distritoSelect.innerHTML = '<option value="">TODOS</option>';
                        data.forEach(d => {
                            const option = document.createElement('option');
                            option.value = d;
                            option.textContent = d;
                            distritoSelect.appendChild(option);
                        });
                    })
                    .catch(e => {
                        console.error('Error fetching distritos:', e);
                        distritoSelect.innerHTML = '<option value="">TODOS</option>';
                    });
            });

            async function cargarEstablecimientos() {
                const distrito = distritoSelect.value;
                const categoria = document.getElementById('categoriaSelect').value;
                establecimientoSelect.innerHTML = '<option value="">Cargando...</option>';
                establecimientoSelect.disabled = true;

                try {
                    // Usamos el endpoint de auditoria duplicidad (o reporte equipos) que carga los establecimientos
                    const res = await fetch(`{{ route('usuario.auditoria.duplicidad.ajax.establecimientos') }}?distrito=${encodeURIComponent(distrito)}`);
                    let data = await res.json();
                    
                    if (categoria) {
                        data = data.filter(est => est.categoria === categoria);
                    }

                    establecimientoSelect.innerHTML = '<option value="">TODOS</option>';
                    data.forEach(est => {
                        const option = document.createElement('option');
                        option.value = est.id;
                        option.textContent = est.nombre;
                        if (est.id == selectedEstablecimiento) option.selected = true;
                        establecimientoSelect.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error al cargar establecimientos:', error);
                    establecimientoSelect.innerHTML = '<option value="">TODOS</option>';
                } finally {
                    establecimientoSelect.disabled = false;
                }
            }

            if (distritoSelect.value || document.getElementById('categoriaSelect').value) {
                cargarEstablecimientos();
            }

            distritoSelect.addEventListener('change', cargarEstablecimientos);
            document.getElementById('categoriaSelect').addEventListener('change', cargarEstablecimientos);
        });
    </script>
@endsection
