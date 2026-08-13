@extends('layouts.usuario')

@section('title', 'Auditoría de Consistencia')

@section('header-content')
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Auditoría de Consistencia</h1>
    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
        <span>Reportes</span>
        <span class="text-slate-300">•</span>
        <span>SIHCE vs Documentos Administrativos</span>
    </div>
@endsection

@section('content')
    <div class="w-full">
        {{-- Tarjeta de Alerta / Info --}}
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6 rounded-r-xl shadow-sm">
            <div class="flex items-center gap-3">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500"></i>
                <div>
                    <h3 class="text-sm font-bold text-amber-800 uppercase tracking-tight">Reporte de Omisiones</h3>
                    <p class="text-xs text-amber-700">Este reporte identifica profesionales que, según los monitoreos,
                        <b>utilizan el sistema SIHCE</b> pero que aún <b>no han sido registrados</b> en el módulo de
                        Documentos Administrativos.
                    </p>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('usuario.auditoria.index') }}" id="filterForm"
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
                    <a href="{{ route('usuario.auditoria.index') }}"
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
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider">Profesional /
                                DNI</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider">Ubicación /
                                IPRESS</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider text-center">
                                Módulo Origen</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider text-center">
                                Compromiso</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider text-center">D.
                                Jurada</th>
                            <th class="px-6 py-4 text-[10px] font-bold text-white uppercase tracking-wider text-center">
                                Ación</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($inconsistencias as $item)
                            <tr class="hover:bg-indigo-50/30 transition-all group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-bold text-slate-800 uppercase text-xs truncate max-w-[200px]">{{ $item['nombre'] }}</span>
                                        <span
                                            class="text-[10px] font-medium text-slate-400 font-mono mt-0.5 tracking-widest">{{ $item['doc'] }}</span>
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
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="bg-slate-100 text-slate-500 px-2 py-1 rounded-lg text-[9px] font-black border border-slate-200 uppercase tracking-tighter">
                                        {{ $item['modulo_origen'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="px-2 py-1 rounded-full font-black text-[9px] {{ $item['estado_compromiso'] == 'FIRMADO' ? 'bg-emerald-100 text-emerald-600' : ($item['estado_compromiso'] == 'PENDIENTE' ? 'bg-amber-100 text-amber-600 animate-pulse' : 'bg-red-100 text-red-600 animate-pulse') }}">
                                        {{ $item['estado_compromiso'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="px-2 py-1 rounded-full font-black text-[9px] {{ $item['estado_dj'] == 'FIRMADO' ? 'bg-emerald-100 text-emerald-600' : ($item['estado_dj'] == 'PENDIENTE' ? 'bg-amber-100 text-amber-600 animate-pulse' : 'bg-red-100 text-red-600 animate-pulse') }}">
                                        {{ $item['estado_dj'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('usuario.documentos.create', ['dni' => $item['doc']]) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm"
                                        title="Crear Documento">
                                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                    </a>
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

            if (provinciaSelect) {
                provinciaSelect.addEventListener('change', async () => {
                    const provincia = provinciaSelect.value;
                    distritoSelect.innerHTML = '<option value="">TODOS</option>';
                    establecimientoSelect.innerHTML = '<option value="">TODOS</option>';

                    if (provincia) {
                        const resDist = await fetch(`{{ route('usuario.auditoria.ajax.distritos') }}?provincia=${provincia}`);
                        const distritos = await resDist.json();
                        distritos.forEach(d => {
                            const opt = document.createElement('option');
                            opt.value = d;
                            opt.textContent = d;
                            distritoSelect.appendChild(opt);
                        });
                    }
                });
            }

            if (distritoSelect) {
                distritoSelect.addEventListener('change', async () => {
                    const provincia = provinciaSelect.value;
                    const distrito = distritoSelect.value;
                    establecimientoSelect.innerHTML = '<option value="">TODOS</option>';

                    if (provincia && distrito) {
                        const resEst = await fetch(`{{ route('usuario.auditoria.ajax.establecimientos') }}?provincia=${provincia}&distrito=${distrito}`);
                        const establecimientos = await resEst.json();
                        establecimientos.forEach(e => {
                            const opt = document.createElement('option');
                            opt.value = e.id;
                            opt.textContent = e.nombre;
                            establecimientoSelect.appendChild(opt);
                        });
                    }
                });
            }
        });
    </script>
@endpush