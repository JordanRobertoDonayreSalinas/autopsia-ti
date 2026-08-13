@extends('layouts.usuario')

@section('title', 'Auditoría de Equipos y Conectividad')

@section('header-content')
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Auditoría de Equipos y Conectividad</h1>
    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
        <span>Reportes</span>
        <span class="text-slate-300">•</span>
        <span>Equipos vs Conectividad</span>
    </div>
@endsection

@section('content')
    <div class="w-full">
        {{-- Tarjeta de Alerta / Info --}}
        <div class="bg-indigo-50 border-l-4 border-indigo-400 p-4 mb-6 rounded-r-xl shadow-sm">
            <div class="flex items-center gap-3">
                <i data-lucide="info" class="w-5 h-5 text-indigo-500"></i>
                <div>
                    <h3 class="text-sm font-bold text-indigo-800 uppercase tracking-tight">Reporte de Inconsistencias</h3>
                    <p class="text-xs text-indigo-700">Este reporte identifica módulos con discrepancias entre el inventario
                        de <b>equipos de cómputo</b> y los datos de <b>conectividad</b>.</p>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('usuario.auditoria.equipos') }}" id="filterForm"
            class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-6 lg:flex lg:items-end gap-3 items-end">
                <div class="lg:flex-1 min-w-0">
                    <label
                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Provincia</label>
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
                <div class="lg:flex-[1.5] min-w-0">
                    <label
                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Establecimiento</label>
                    <select name="establecimiento_id" id="establecimientoSelect"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">TODOS</option>
                        @foreach($establecimientos as $est)
                            <option value="{{ $est->id }}" {{ request('establecimiento_id') == $est->id ? 'selected' : '' }}>
                                {{ $est->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:flex-1 min-w-0">
                    <label
                        class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Implementador</label>
                    <select name="implementador" id="implementadorSelect"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">TODOS</option>
                        @foreach($implementadores as $imp)
                            <option value="{{ $imp }}" {{ request('implementador') == $imp ? 'selected' : '' }}>
                                {{ $imp }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:w-36">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Desde</label>
                    <input type="date" name="fecha_inicio" value="{{ $fecha_inicio }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div class="lg:w-36">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 block">Hasta</label>
                    <input type="date" name="fecha_fin" value="{{ $fecha_fin }}"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-3 rounded-xl text-xs transition-all shadow-lg shadow-indigo-200 flex items-center justify-center gap-2">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                    <button type="submit" name="export" value="1"
                        class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-3 rounded-xl text-xs transition-all shadow-lg shadow-emerald-200 flex items-center justify-center gap-2">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    </button>
                    <a href="{{ route('usuario.auditoria.equipos') }}"
                        class="bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold py-2 px-3 rounded-xl text-xs transition-all flex items-center justify-center">
                        <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </form>

        {{-- Tabla de Resultados --}}
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-800">
                        <tr>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider">Acta / Fecha
                            </th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider">Establecimiento
                            </th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider">Módulo</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider text-center">
                                Equipos</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider text-center">
                                Conectividad</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider text-center">
                                Inconsistencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($inconsistencias as $item)
                            <tr class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-slate-800 uppercase text-xs">#{{ str_pad($item['numero_acta'], 5, '0', STR_PAD_LEFT) }}</span>
                                        <span
                                            class="text-[10px] font-medium text-slate-400 mt-0.5 tracking-widest">{{ \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-slate-600 uppercase text-[10px]">{{ $item['ipress'] }}</span>
                                        <span class="text-[9px] text-slate-400 uppercase mt-0.5">{{ $item['provincia'] }} -
                                            {{ $item['distrito'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="bg-slate-100 text-slate-500 px-2 py-1 rounded-lg text-[9px] font-black border border-slate-200 uppercase tracking-tighter">
                                        {{ $item['modulo_nombre'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-black text-slate-700 text-xs">
                                        {{ $item['equipos_count'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="bg-indigo-50 text-indigo-600 px-2 py-1 rounded-lg text-[9px] font-black border border-indigo-100 uppercase tracking-tighter">
                                        {{ $item['conectividad'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if($item['tipo_inconsistencia'] == 'EQUIPO SIN DATOS DE CONEXIÓN')
                                        <span class="px-2 py-1 rounded-full font-black text-[9px] bg-red-100 text-red-600">
                                            EQUIPO SIN DATOS DE CONEXIÓN
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-full font-black text-[9px] bg-amber-100 text-amber-600">
                                            CONEXIÓN SIN EQUIPO
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center opacity-20">
                                        <i data-lucide="check-circle-2" class="w-12 h-12 mb-2 text-emerald-500"></i>
                                        <span class="text-xs font-black uppercase tracking-widest">¡Felicidades! No se
                                            detectaron inconsistencias</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const provinciaSelect = document.getElementById('provinciaSelect');
            const distritoSelect = document.getElementById('distritoSelect');
            const establecimientoSelect = document.getElementById('establecimientoSelect');

            provinciaSelect.addEventListener('change', async () => {
                const provincia = provinciaSelect.value;

                // Limpiar selectores dependientes
                distritoSelect.innerHTML = '<option value="">TODOS</option>';
                establecimientoSelect.innerHTML = '<option value="">TODOS</option>';

                if (provincia) {
                    // Cargar Distritos
                    const resDist = await fetch(`{{ route('usuario.auditoria.equipos.ajax.distritos') }}?provincia=${provincia}`);
                    const distritos = await resDist.json();
                    distritos.forEach(d => {
                        const opt = document.createElement('option');
                        opt.value = d;
                        opt.textContent = d;
                        distritoSelect.appendChild(opt);
                    });

                    // Cargar Establecimientos (solo por provincia inicialmente)
                    actualizarEstablecimientos(provincia, '');
                } else {
                    // Volver a cargar todos si es necesario o dejar en todos
                    actualizarEstablecimientos('', '');
                }
            });

            distritoSelect.addEventListener('change', async () => {
                const provincia = provinciaSelect.value;
                const distrito = distritoSelect.value;
                actualizarEstablecimientos(provincia, distrito);
            });

            async function actualizarEstablecimientos(provincia, distrito) {
                establecimientoSelect.innerHTML = '<option value="">TODOS</option>';
                const resEst = await fetch(`{{ route('usuario.auditoria.equipos.ajax.establecimientos') }}?provincia=${provincia}&distrito=${distrito}`);
                const establecimientos = await resEst.json();
                establecimientos.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.id;
                    opt.textContent = e.nombre;
                    establecimientoSelect.appendChild(opt);
                });
            }
        });
    </script>
@endpush