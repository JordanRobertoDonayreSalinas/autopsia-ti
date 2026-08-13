@extends('layouts.usuario')

@section('title', 'Cronograma de Actividades')

@push('styles')
<style>
    [x-cloak] { display: none !important; }

    .input-filter {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        color: #334155;
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.5rem 0.75rem;
        width: 100%;
        transition: all 0.2s;
    }
    .input-filter:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
    }

    /* Tipo badges */
    .badge-asistencia {
        background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;
    }
    .badge-monitoreo {
        background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;
    }
    .badge-implementacion {
        background: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe;
    }
    .badge-reunion {
        background: #fff1f2; color: #e11d48; border: 1px solid #fecdd3;
    }
    .badge-anulado {
        background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa;
    }

    /* Timeline table row hover */
    .timeline-row:hover { background-color: #f8fafc; }

    /* Month separator */
    .month-separator td {
        background: linear-gradient(90deg, #f1f5f9, #f8fafc);
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        color: #64748b;
        text-transform: uppercase;
        padding: 0.4rem 0.75rem;
        border-top: 2px solid #e2e8f0;
    }
</style>
@endpush

@section('header-content')
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">🗓️ Cronograma de Actividades</h1>
        <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
            <span>Reportes</span>
            <span class="text-slate-300">•</span>
            <span>Cronograma de Actividades</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-[1400px] mx-auto space-y-6">

    {{-- ========== BANNER KPI ========== --}}
    <div class="bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 p-5 rounded-2xl shadow-xl relative overflow-hidden text-white">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-white/5 rounded-full blur-2xl -ml-8 -mb-8 pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex flex-wrap gap-3">

                    {{-- Total --}}
                    <div class="bg-slate-900/70 backdrop-blur-md rounded-xl px-5 py-3 border border-white/10 flex flex-col items-center min-w-[110px]">
                        <span class="text-3xl font-black leading-none">{{ $totalActividades }}</span>
                        <span class="text-[0.6rem] uppercase tracking-widest text-slate-300 font-bold mt-1">Total Actividades</span>
                    </div>

                    {{-- Implementacion --}}
                    <div class="bg-violet-500/20 backdrop-blur-md rounded-xl px-4 py-3 border border-violet-400/30 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-black leading-none">{{ $countImplementacion }}</span>
                        <span class="text-[0.6rem] uppercase tracking-widest text-violet-200 font-bold mt-1">Implementación</span>
                    </div>

                    {{-- Asistencia --}}
                    <div class="bg-emerald-500/20 backdrop-blur-md rounded-xl px-4 py-3 border border-emerald-400/30 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-black leading-none">{{ $countAsistencia }}</span>
                        <span class="text-[0.6rem] uppercase tracking-widest text-emerald-200 font-bold mt-1">Asistencia T.</span>
                    </div>

                    {{-- Monitoreo --}}
                    <div class="bg-blue-500/20 backdrop-blur-md rounded-xl px-4 py-3 border border-blue-400/30 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-black leading-none">{{ $countMonitoreo }}</span>
                        <span class="text-[0.6rem] uppercase tracking-widest text-blue-200 font-bold mt-1">Monitoreo</span>
                    </div>

                    {{-- Reunión --}}
                    <div class="bg-rose-500/20 backdrop-blur-md rounded-xl px-4 py-3 border border-rose-400/30 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-black leading-none">{{ $countReunion }}</span>
                        <span class="text-[0.6rem] uppercase tracking-widest text-rose-200 font-bold mt-1">Reunión</span>
                    </div>

                    {{-- Firmadas --}}
                    <div class="bg-white/20 backdrop-blur-md rounded-xl px-4 py-3 border border-white/30 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-black leading-none">{{ $totalFirmadas }}</span>
                        <span class="text-[0.6rem] uppercase tracking-widest text-indigo-100 font-bold mt-1">Firmadas</span>
                    </div>

                    {{-- Pendientes --}}
                    <div class="bg-amber-500/80 rounded-xl px-4 py-3 border border-amber-400 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-black leading-none">{{ $totalPendientes }}</span>
                        <span class="text-[0.6rem] uppercase tracking-widest text-amber-100 font-bold mt-1">Pendientes</span>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-sm font-semibold text-white/80 shrink-0">
                    <i data-lucide="calendar-range" class="w-4 h-4"></i>
                    {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ========== FILTROS ========== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <form method="GET" action="{{ route('usuario.reportes.cronograma') }}" id="filtroForm">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

                {{-- Fecha Inicio --}}
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Desde</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio"
                           value="{{ $fechaInicio }}" class="input-filter">
                </div>

                {{-- Fecha Fin --}}
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Hasta</label>
                    <input type="date" name="fecha_fin" id="fecha_fin"
                           value="{{ $fechaFin }}" class="input-filter">
                </div>

                {{-- Tipo de Acta --}}
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Tipo de Acta</label>
                    <select name="tipo_acta" class="input-filter">
                        <option value="">Todos los tipos</option>
                        <option value="asistencia"     {{ request('tipo_acta') === 'asistencia'     ? 'selected' : '' }}>Asistencia Técnica</option>
                        <option value="monitoreo"      {{ request('tipo_acta') === 'monitoreo'      ? 'selected' : '' }}>Monitoreo</option>
                        <option value="implementacion" {{ request('tipo_acta') === 'implementacion' ? 'selected' : '' }}>Implementación</option>
                        <option value="reunion"        {{ request('tipo_acta') === 'reunion'        ? 'selected' : '' }}>Reunión</option>
                    </select>
                </div>

                {{-- Provincia --}}
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1 ml-1">Provincia</label>
                    <select name="provincia" class="input-filter uppercase">
                        <option value="">Todas</option>
                        @foreach($provincias as $prov)
                            <option value="{{ $prov }}" {{ request('provincia') == $prov ? 'selected' : '' }}>{{ $prov }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-4 mt-2 border-t border-slate-100">
                <button type="submit"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl flex items-center gap-2 transition-all">
                    <i data-lucide="search" class="w-4 h-4"></i> FILTRAR
                </button>
                <a href="{{ route('usuario.reportes.cronograma') }}"
                   class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl flex items-center gap-2 transition-all">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> LIMPIAR
                </a>
                <div class="ml-auto flex items-center gap-2">
                    <button type="button" onclick="exportarPdf()"
                            class="px-5 py-2.5 bg-red-50 text-red-700 hover:bg-red-100 font-bold text-xs rounded-xl flex items-center gap-2 transition-all border border-red-200">
                        <i data-lucide="file-text" class="w-4 h-4"></i> EXPORTAR PDF
                    </button>
                    <button type="button" onclick="exportarExcel()"
                            class="px-5 py-2.5 bg-green-50 text-green-700 hover:bg-green-100 font-bold text-xs rounded-xl flex items-center gap-2 transition-all border border-green-200">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> EXPORTAR EXCEL
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ========== LEYENDA ========== --}}
    <div class="flex flex-wrap items-center gap-3 px-1">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Leyenda:</span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold badge-asistencia">
            <i data-lucide="file-text" class="w-3 h-3"></i> Asistencia Técnica
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold badge-monitoreo">
            <i data-lucide="activity" class="w-3 h-3"></i> Monitoreo
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold badge-implementacion">
            <i data-lucide="pen-tool" class="w-3 h-3"></i> Implementación
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold badge-reunion">
            <i data-lucide="users" class="w-3 h-3"></i> Reunión
        </span>
    </div>

    {{-- ========== TABLA CRONOGRAMA ========== --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 bg-slate-50/60 border-b border-slate-100 flex items-center gap-2">
            <i data-lucide="calendar-days" class="w-4 h-4 text-indigo-500"></i>
            <h3 class="text-sm font-bold text-slate-800">
                Actividades Registradas ({{ $actasPaginadas->total() }})
            </h3>
        </div>

        @if($actasPaginadas->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs" id="tablaActividades">
                <thead class="bg-slate-800 text-white sticky top-0 z-10">
                    <tr>
                        <th class="px-3 py-3 text-center font-bold uppercase text-[10px] w-10">#</th>
                        <th class="px-3 py-3 text-center font-bold uppercase text-[10px] w-24">Fecha</th>
                        <th class="px-3 py-3 font-bold uppercase text-[10px]">Tipo de Actividad</th>
                        <th class="px-3 py-3 font-bold uppercase text-[10px]">Establecimiento</th>
                        <th class="px-3 py-3 font-bold uppercase text-[10px] w-28">Provincia</th>
                        <th class="px-3 py-3 font-bold uppercase text-[10px]">Responsable</th>
                        <th class="px-3 py-3 font-bold uppercase text-[10px]">Actividad / Módulo</th>
                        <th class="px-3 py-3 text-center font-bold uppercase text-[10px] w-24">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @php
                        $currentMonth = null;
                        $rowNum = ($actasPaginadas->currentPage() - 1) * $actasPaginadas->perPage();
                    @endphp

                    @foreach($actasPaginadas as $fila)
                    @php
                        $mesActual = \Carbon\Carbon::parse($fila['fecha'])->format('F Y');
                        $rowNum++;
                    @endphp

                    {{-- Separador de mes --}}
                    @if($mesActual !== $currentMonth)
                        @php $currentMonth = $mesActual; @endphp
                        <tr class="month-separator">
                            <td colspan="8">
                                <div class="flex items-center gap-2">
                                    <i data-lucide="calendar" class="w-3 h-3 inline-block text-indigo-400 flex-shrink-0" style="display:inline-block;"></i>
                                    {{ strtoupper(\Carbon\Carbon::parse($fila['fecha'])->locale('es')->isoFormat('MMMM [de] YYYY')) }}
                                </div>
                            </td>
                        </tr>
                    @endif

                    <tr class="timeline-row transition-colors">
                        <td class="px-3 py-3 text-center font-mono text-slate-400 font-semibold">{{ $rowNum }}</td>

                        {{-- Fecha --}}
                        <td class="px-3 py-3 text-center whitespace-nowrap">
                            <div class="font-black text-slate-700">
                                {{ \Carbon\Carbon::parse($fila['fecha'])->format('d') }}
                            </div>
                            <div class="text-[10px] text-slate-400 font-semibold uppercase">
                                {{ \Carbon\Carbon::parse($fila['fecha'])->locale('es')->isoFormat('MMM') }}
                            </div>
                        </td>

                        {{-- Tipo --}}
                        <td class="px-3 py-3 whitespace-nowrap">
                            @if($fila['tipo_key'] === 'asistencia')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold badge-asistencia">
                                    <i data-lucide="file-text" class="w-3 h-3"></i>
                                    Asistencia Técnica
                                </span>
                            @elseif($fila['tipo_key'] === 'monitoreo')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold badge-monitoreo">
                                    <i data-lucide="activity" class="w-3 h-3"></i>
                                    Monitoreo
                                </span>
                            @elseif($fila['tipo_key'] === 'reunion')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold badge-reunion">
                                    <i data-lucide="users" class="w-3 h-3"></i>
                                    Reunión
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold badge-implementacion">
                                    <i data-lucide="pen-tool" class="w-3 h-3"></i>
                                    Implementación
                                </span>
                            @endif
                        </td>

                        {{-- Establecimiento --}}
                        <td class="px-3 py-3 font-semibold text-slate-800 max-w-[200px]">
                            <div class="truncate" title="{{ $fila['establecimiento'] }}">
                                {{ $fila['establecimiento'] }}
                            </div>
                        </td>

                        {{-- Provincia --}}
                        <td class="px-3 py-3 uppercase text-[11px] font-semibold text-slate-500 whitespace-nowrap">
                            {{ $fila['provincia'] }}
                        </td>

                        {{-- Responsable --}}
                        <td class="px-3 py-3 text-slate-500 max-w-[140px]">
                            <div class="truncate" title="{{ $fila['responsable'] }}">
                                {{ $fila['responsable'] }}
                            </div>
                        </td>

                        {{-- Actividad / Módulo --}}
                        <td class="px-3 py-3 max-w-[160px]">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 truncate block"
                                  title="{{ $fila['actividad'] }}">
                                {{ $fila['actividad'] }}
                            </span>
                            @if($fila['modalidad'] !== '—')
                                <span class="text-[9px] text-slate-400 font-medium ml-1">{{ $fila['modalidad'] }}</span>
                            @endif
                        </td>

                        {{-- Estado --}}
                        <td class="px-3 py-3 text-center">
                            @if($fila['anulado'])
                                <span class="inline-flex px-2 py-0.5 rounded-md text-[9px] font-black badge-anulado uppercase">Anulado</span>
                            @elseif($fila['firmado'])
                                <span class="inline-flex px-2 py-0.5 rounded-md text-[9px] font-black bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase">Firmado</span>
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-md text-[9px] font-black bg-amber-100 text-amber-700 border border-amber-200 uppercase">Pendiente</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="p-4 border-t border-slate-100 bg-slate-50/40">
            {{ $actasPaginadas->links() }}
        </div>

        @else
        <div class="py-20 text-center">
            <div class="w-16 h-16 mx-auto mb-4 bg-indigo-50 rounded-full flex items-center justify-center">
                <i data-lucide="calendar-x" class="w-8 h-8 text-indigo-200"></i>
            </div>
            <p class="text-slate-400 font-semibold text-sm">No se encontraron actividades en el período seleccionado</p>
            <p class="text-slate-300 text-xs mt-1">Ajusta las fechas o los filtros para ampliar la búsqueda</p>
        </div>
        @endif
    </div>

</div>

{{-- Formularios ocultos para exportar --}}
<form id="excelForm" method="POST" action="{{ route('usuario.reportes.cronograma.excel') }}" style="display:none;">
    @csrf
    <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
    <input type="hidden" name="fecha_fin"    value="{{ $fechaFin }}">
    <input type="hidden" name="provincia"    value="{{ request('provincia') }}">
    <input type="hidden" name="tipo_acta"    value="{{ request('tipo_acta') }}">
</form>

<form id="pdfForm" method="POST" action="{{ route('usuario.reportes.cronograma.pdf') }}" target="_blank" style="display:none;">
    @csrf
    <input type="hidden" name="fecha_inicio" value="{{ $fechaInicio }}">
    <input type="hidden" name="fecha_fin"    value="{{ $fechaFin }}">
    <input type="hidden" name="provincia"    value="{{ request('provincia') }}">
    <input type="hidden" name="tipo_acta"    value="{{ request('tipo_acta') }}">
</form>
@endsection

@push('scripts')
<script>
    lucide.createIcons();

    function exportarExcel() {
        document.getElementById('excelForm').submit();
    }

    function exportarPdf() {
        document.getElementById('pdfForm').submit();
    }
</script>
@endpush
