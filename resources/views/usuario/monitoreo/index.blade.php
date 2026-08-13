@extends('layouts.usuario')

@section('title', 'Actas de Monitoreo')

@push('styles')
    <style>
        input[type="date"] {
            position: relative;
            color: #4b5563;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%233b82f6' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1.2em;
        }
        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0;
            cursor: pointer;
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
        }
        [x-cloak] { display: none !important; }

        .progress-bar-container {
            width: 100%;
            height: 6px;
            background-color: #e2e8f0;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 4px;
        }
        .progress-bar-fill {
            height: 100%;
            transition: width 0.5s ease-in-out;
        }
        /* Chips de correo */
        .email-chips-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background-color: #f8fafc;
            min-height: 45px;
            cursor: text;
        }
        .email-chip {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            background-color: #1d4ed8;
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            animation: chip-in 0.2s ease-out;
        }
        .email-chip button {
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.15);
            border-radius: 50%;
            width: 14px;
            height: 14px;
            transition: background 0.2s;
        }
        .email-chip button:hover { background: rgba(255,255,255,0.2); }
        .email-chip-input {
            flex: 1;
            min-width: 120px;
            border: none !important;
            background: transparent !important;
            padding: 2px 5px !important;
            font-size: 0.75rem !important;
            outline: none !important;
            box-shadow: none !important;
        }
        @keyframes chip-in {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .shake { animation: shake 0.3s cubic-bezier(.36,.07,.19,.97) both; }
        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }
    </style>
@endpush

@section('header-content')
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Actas de Monitoreo</h1>
    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
        <span>Operaciones</span>
        <span class="text-slate-300">•</span>
        <span>Panel de Control de Monitoreo</span>
    </div>
@endsection

@section('content')

    @php
        // Filtros de fecha automáticos (Año actual)
        $fechaInicioDefault = now()->startOfYear()->format('Y-m-d');
        $fechaFinDefault = now()->format('Y-m-d');

        $filtersAreActive = request()->anyFilled(['implementador', 'provincia', 'fecha_inicio', 'fecha_fin', 'estado']);
        
        $valInicio = request('fecha_inicio', $fechaInicioDefault);
        $valFin = request('fecha_fin', $fechaFinDefault);
    @endphp

    <div x-data="{ open: {{ $filtersAreActive ? 'true' : 'false' }} }" class="w-full">

        {{-- TARJETA AZUL SUPERIOR --}}
        <div class="bg-gradient-to-r from-blue-700 to-indigo-600 p-5 rounded-2xl shadow-xl mb-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-6">
                <div class="flex flex-col gap-4 w-full">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="bg-slate-900 text-white rounded-xl px-5 py-2.5 shadow-lg border border-slate-700 flex flex-col items-center min-w-[100px]">
                            <span class="text-2xl font-bold leading-none">{{ $monitoreos->total() }}</span>
                            <span class="text-[0.65rem] uppercase tracking-widest text-slate-400 font-semibold mt-1">TOTAL</span>
                        </div>
                        <div class="bg-emerald-500/20 backdrop-blur-md text-white rounded-xl px-5 py-2.5 border border-emerald-500/30 flex flex-col items-center min-w-[100px]">
                            <span class="text-2xl font-bold leading-none text-emerald-400">{{ $countCompletados ?? 0 }}</span>
                            <span class="text-[0.65rem] uppercase tracking-widest text-emerald-100 font-semibold mt-1">FIRMADAS</span>
                        </div>
                        <div class="bg-amber-500/20 backdrop-blur-md text-white rounded-xl px-5 py-2.5 border border-amber-500/30 flex flex-col items-center min-w-[100px]">
                            <span class="text-2xl font-bold leading-none text-amber-400">{{ $countPendientes ?? 0 }}</span>
                            <span class="text-[0.65rem] uppercase tracking-widest text-amber-100 font-semibold mt-1">Pendientes</span>
                        </div>
                        <div class="bg-slate-900 text-white rounded-xl px-5 py-2.5 shadow-lg border border-slate-700 flex flex-col items-center min-w-[100px]">
                            <span class="text-2xl font-bold leading-none">{{ $countAnuladas ?? 0 }}</span>
                            <span class="text-[0.65rem] uppercase tracking-widest text-slate-400 font-semibold mt-1">Anuladas</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full lg:w-auto justify-center lg:justify-end mt-2 lg:mt-0">
                    @if(Auth::user()->role !== 'operador')
                    <button @click="open = !open" type="button"
                        class="flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition-all shadow-lg border border-white/20 text-white bg-white/10 hover:bg-white/20 backdrop-blur-sm">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        <span x-text="open ? 'Ocultar Filtros' : 'Mostrar Filtros'"></span>
                    </button>
                    @endif

                    <a href="{{ route('usuario.monitoreo.create') }}"
                        class="flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition-all shadow-lg bg-white text-blue-700 hover:bg-blue-50 border border-transparent">
                        <i data-lucide="activity" class="w-5 h-5"></i>
                        <span>Nueva Acta</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- FILTROS ACTUALIZADOS --}}
        @if(Auth::user()->role !== 'operador')
        <form x-show="open" x-cloak 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            method="GET" action="{{ route('usuario.monitoreo.index') }}" id="filterForm"
            class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
            
            <div class="flex flex-wrap lg:flex-nowrap items-end gap-3">
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 flex-grow w-full">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">Implementador</label>
                        <select name="implementador" class="w-full text-[11px] font-bold text-slate-700 border-slate-200 bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500 py-2">
                            <option value="">TODOS</option>
                            @foreach ($implementadores as $impl)
                                <option value="{{ $impl }}" {{ request('implementador') == $impl ? 'selected' : '' }}>{{ $impl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">Provincia</label>
                        <select name="provincia" id="provinciaSelect" class="w-full text-[11px] font-bold text-slate-700 border-slate-200 bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500 py-2">
                            <option value="">TODAS</option>
                            @foreach ($provincias as $prov)
                                <option value="{{ $prov }}" {{ request('provincia') == $prov ? 'selected' : '' }}>{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">Distrito</label>
                        <select name="distrito" id="distritoSelect" class="w-full text-[11px] font-bold text-slate-700 border-slate-200 bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500 py-2">
                            <option value="">TODOS</option>
                            @foreach ($distritos as $dist)
                                <option value="{{ $dist }}" {{ request('distrito') == $dist ? 'selected' : '' }}>{{ $dist }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">Establecimiento</label>
                        <select name="establecimiento_id" id="establecimientoSelect" class="w-full text-[11px] font-bold text-slate-700 border-slate-200 bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500 py-2">
                            <option value="">TODOS</option>
                            @foreach ($establecimientos as $est)
                                <option value="{{ $est->id }}" {{ request('establecimiento_id') == $est->id ? 'selected' : '' }}>{{ $est->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">Estado</label>
                        <select name="estado" class="w-full text-[11px] font-bold text-slate-700 border-slate-200 bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500 py-2">
                            <option value="">TODOS</option>
                            <option value="firmada" {{ request('estado') == 'firmada' ? 'selected' : '' }}>FIRMADO</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>PENDIENTE</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">Visibilidad</label>
                        <select name="estado_anulado" class="w-full text-[11px] font-bold text-slate-700 border-slate-200 bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500 py-2">
                            <option value="todos" {{ request('estado_anulado', 'todos') == 'todos' ? 'selected' : '' }}>Todas</option>
                            <option value="activo" {{ request('estado_anulado') == 'activo' ? 'selected' : '' }}>Activas</option>
                            <option value="anulado" {{ request('estado_anulado') == 'anulado' ? 'selected' : '' }}>Anuladas</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">Desde</label>
                        <input type="date" name="fecha_inicio" value="{{ $valInicio }}" 
                            class="w-full text-[11px] font-bold text-slate-700 border-slate-200 bg-slate-50 rounded-xl py-2">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">Hasta</label>
                        <input type="date" name="fecha_fin" value="{{ $valFin }}" 
                            class="w-full text-[11px] font-bold text-slate-700 border-slate-200 bg-slate-50 rounded-xl py-2">
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0 mt-3 md:mt-0">
                    <button type="submit" class="w-full lg:w-10 h-10 flex items-center justify-center rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-500/30 transition-all hover:scale-105 text-sm" title="Filtrar">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span class="block lg:hidden ml-2 font-bold">Buscar</span>
                    </button>
                    <a href="{{ route('usuario.monitoreo.index') }}" 
                        class="w-full lg:w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 shadow-sm transition-all hover:scale-105 border border-slate-200 text-sm" title="Limpiar">
                        <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                        <span class="block lg:hidden ml-2 font-bold">Limpiar</span>
                    </a>
                    @if($monitoreos->total() > 0)
                    <div class="flex gap-2 w-full lg:w-auto">
                        <button type="button" onclick="exportarExcel()" class="flex-1 lg:flex-none h-10 px-4 py-2 bg-green-50 text-green-700 hover:bg-green-100 font-bold text-[10px] sm:text-xs rounded-xl flex items-center justify-center gap-2 transition-all border border-green-200" title="Exportar a Excel">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> 
                            <span>EXCEL</span>
                        </button>
                        <button type="button" onclick="exportarPDF()" class="flex-1 lg:flex-none h-10 px-4 py-2 bg-red-50 text-red-700 hover:bg-red-100 font-bold text-[10px] sm:text-xs rounded-xl flex items-center justify-center gap-2 transition-all border border-red-200" title="Exportar PDFs Consolidados">
                            <i data-lucide="file-text" class="w-4 h-4"></i> 
                            <span>PDF</span>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </form>
        @endif

        {{-- TABLA --}}
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-800">
                        <tr>
                            <th class="px-3 py-3 text-[10px] font-bold text-white uppercase tracking-wider">N° Acta</th>
                            <th class="px-3 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Fecha</th>
                            <th class="px-3 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Establecimiento</th>
                            <th class="px-3 py-3 text-[10px] font-bold text-white uppercase tracking-wider text-center">Provincia/Distrito</th>
                            <th class="px-3 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Implementador</th>
                            <th class="px-3 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Submódulos Firmados</th>
                            <th class="px-3 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Módulos Firmados</th>
                            <th class="px-3 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Acta Consolidada</th>
                            <th class="px-3 py-3 text-[10px] font-bold text-white uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($monitoreos as $monitoreo)
                            @php
                                $misDetalles = $monitoreo->detalles ?? collect();
                                $configMod = $misDetalles->where('modulo_nombre', 'config_modulos')->first();
                                
                                $activosKeys = $configMod ? (is_array($configMod->contenido) ? $configMod->contenido : json_decode($configMod->contenido, true)) : [
                                    'gestion_administrativa', 'citas', 'triaje', 'consulta_medicina', 'consulta_odontologia', 
                                    'consulta_nutricion', 'consulta_psicologia', 'cred', 'inmunizaciones', 'atencion_prenatal', 
                                    'planificacion_familiar', 'parto', 'puerperio', 'fua_electronico', 'farmacia', 'referencias', 
                                    'laboratorio', 'urgencias'
                                ];
                                $activosKeys = array_filter((array)$activosKeys);
                                
                                // Verificar si salud_mental_group está habilitado
                                $saludMentalHabilitado = in_array('salud_mental_group', $activosKeys);
                                
                                // Si salud_mental_group está habilitado, excluir submódulos individuales del conteo total
                                $submodulosSM = ['sm_medicina_general', 'sm_psiquiatria', 'sm_med_familiar', 'sm_psicologia', 'sm_enfermeria', 'sm_servicio_social', 'sm_terapias'];
                                
                                // Guardar submódulos habilitados ANTES de eliminarlos de activosKeys
                                $submodulosSMHabilitados = array_intersect($activosKeys, $submodulosSM);
                                
                                if ($saludMentalHabilitado) {
                                    // Remover submódulos de SM de activosKeys para evitar conteo duplicado
                                    $activosKeys = array_diff($activosKeys, $submodulosSM);
                                }
                                
                                // Contar módulos firmados (excluyendo submódulos de Salud Mental)
                                $modulosNoSM = array_diff($activosKeys, ['salud_mental_group']);
                                $firmadosCount = $misDetalles->filter(fn($d) => in_array($d->modulo_nombre, $modulosNoSM) && !empty($d->pdf_firmado_path))->count();
                                
                                // Si Salud Mental está habilitado, verificar si todos sus submódulos HABILITADOS están firmados
                                if ($saludMentalHabilitado) {
                                    // Contar cuántos submódulos habilitados están firmados
                                    $totalSMHabilitados = count($submodulosSMHabilitados);
                                    $firmadosSMHabilitados = $misDetalles->filter(fn($d) => 
                                        in_array($d->modulo_nombre, $submodulosSMHabilitados) && !empty($d->pdf_firmado_path)
                                    )->count();
                                    
                                    // Solo contar Salud Mental como firmado si TODOS los submódulos habilitados están firmados
                                    if ($totalSMHabilitados > 0 && $firmadosSMHabilitados === $totalSMHabilitados) {
                                        $firmadosCount++;
                                    }
                                }
                                
                                $totalHabilitados = count($activosKeys);
                                $porcentaje = $totalHabilitados > 0 ? ($firmadosCount / $totalHabilitados) * 100 : 0;
                            @endphp

                            <tr class="hover:bg-blue-50/30 transition-colors group {{ $monitoreo->anulado ? 'bg-slate-50 opacity-65 grayscale-[0.5]' : '' }}"
                                data-id="{{ $monitoreo->id }}"
                                data-firmado-pdf="{{ $monitoreo->firmado_pdf ? 'true' : 'false' }}">
                                <td class="px-3 py-3 font-mono font-bold text-slate-700">{{ str_pad($monitoreo->numero_acta, 5, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-3 py-3 text-slate-600">{{ \Carbon\Carbon::parse($monitoreo->fecha)->format('d/m/Y') }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex flex-col">
                                        <span class="font-semibold text-slate-800">{{ $monitoreo->establecimiento->nombre ?? '—' }}</span>
                                        <div class="flex items-center gap-1 mt-1">
                                            <span class="text-[9px] w-fit px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-bold border border-slate-200">
                                                {{ $monitoreo->categoria_congelada ?? '—' }}
                                            </span>
                                            @if($monitoreo->anulado)
                                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-red-100 text-red-700 font-black border border-red-200 uppercase">ANULADA</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex flex-col">
                                        <span class="font-medium text-slate-700">{{ $monitoreo->establecimiento->provincia ?? '—' }}</span>
                                        <span class="text-[10px] text-slate-400 uppercase tracking-tighter">{{ $monitoreo->establecimiento->distrito ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center justify-between min-w-[150px] group/author">
                                        <span class="font-bold text-slate-700 leading-tight">
                                            @if($monitoreo->user)
                                                {{ mb_strtoupper("{$monitoreo->user->apellido_paterno} {$monitoreo->user->apellido_materno} {$monitoreo->user->name}", 'UTF-8') }}
                                            @else
                                                <span class="text-slate-400 italic text-[10px]">NO ASIGNADO</span>
                                            @endif
                                        </span>
                                        @if(Auth::user()->role === 'admin')
                                            <button onclick="abrirModalCambiarAutor({{ $monitoreo->id }}, {{ $monitoreo->user_id ?? 'null' }}, '{{ $monitoreo->user ? addslashes(mb_strtoupper("{$monitoreo->user->apellido_paterno} {$monitoreo->user->apellido_materno} {$monitoreo->user->name}", 'UTF-8')) : 'NO ASIGNADO' }}')" 
                                                class="text-slate-400 hover:text-indigo-600 opacity-40 group-hover/author:opacity-100 transition-opacity ml-1.5" 
                                                title="Cambiar Autor">
                                                <i data-lucide="user-cog" class="w-3.5 h-3.5 inline"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                
                                {{-- COLUMNA SUBMÓDULOS SALUD MENTAL --}}
                                <td class="px-3 py-3 min-w-[110px]">
                                    @if($saludMentalHabilitado && isset($totalSMHabilitados) && $totalSMHabilitados > 0)
                                        @php
                                            $porcentajeSM = $totalSMHabilitados > 0 ? ($firmadosSMHabilitados / $totalSMHabilitados) * 100 : 0;
                                        @endphp
                                        <div class="flex flex-col">
                                            <div class="flex items-center justify-between mb-0.5">
                                                <span class="text-[10px] font-bold text-slate-500">{{ $firmadosSMHabilitados }}/{{ $totalSMHabilitados }}</span>
                                                <span class="text-[9px] font-black {{ $porcentajeSM == 100 ? 'text-emerald-500' : 'text-amber-500' }}">
                                                    {{ round($porcentajeSM) }}%
                                                </span>
                                            </div>
                                            <div class="progress-bar-container">
                                                <div class="progress-bar-fill {{ $porcentajeSM == 100 ? 'bg-emerald-500' : 'bg-amber-400' }}" style="width: {{ $porcentajeSM }}%"></div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                                
                                {{-- COLUMNA MÓDULOS --}}
                                <td class="px-3 py-3 min-w-[110px]">
                                    <div class="flex flex-col">
                                        <div class="flex items-center justify-between mb-0.5">
                                            <span class="text-[10px] font-bold text-slate-500">{{ $firmadosCount }}/{{ $totalHabilitados }}</span>
                                            <span class="text-[9px] font-black {{ $porcentaje == 100 ? 'text-emerald-500' : 'text-amber-500' }}">
                                                {{ round($porcentaje) }}%
                                            </span>
                                        </div>
                                        <div class="progress-bar-container">
                                            <div class="progress-bar-fill {{ $porcentaje == 100 ? 'bg-emerald-500' : 'bg-amber-400' }}" style="width: {{ $porcentaje }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                {{-- COLUMNA ACTA FINAL --}}
                                <td class="px-3 py-3 text-center">
                                    @if($monitoreo->firmado)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[9px] uppercase border border-emerald-200">
                                            <i data-lucide="check-circle-2" class="w-3 h-3"></i> Firmada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-100 text-slate-500 font-bold text-[9px] uppercase border border-slate-200">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Pendiente
                                        </span>
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if(!$monitoreo->anulado)
                                        <button onclick="abrirModalSubir({{ $monitoreo->id }})" 
                                            class="p-1.5 rounded-lg {{ $monitoreo->firmado ? 'text-emerald-500 bg-emerald-50' : 'text-slate-400 hover:bg-slate-50' }} transition-all" 
                                            title="Subir acta consolidada firmada">
                                            <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                        </button>

                                        <a href="{{ route('usuario.monitoreo.modulos', $monitoreo->id) }}" class="p-1.5 rounded-lg text-blue-500 hover:bg-blue-50 transition-all" title="Gestionar módulos">
                                            <i data-lucide="layers" class="w-4 h-4"></i>
                                        </a>

                                        <a href="{{ route('usuario.monitoreo.pdf', $monitoreo->id) }}" target="_blank" 
                                           class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all" 
                                           title="Ver acta consolidada">
                                            <i data-lucide="file-text" class="w-4 h-4"></i>
                                        </a>

                                         @if($monitoreo->firmado_pdf)
                                            @php
                                                $carpeta = strtolower(dirname($monitoreo->firmado_pdf));
                                                $archivoBuscado = strtolower(basename($monitoreo->firmado_pdf));
                                                $archivoReal = basename($monitoreo->firmado_pdf);
                                                $archivosEnServidor = \Illuminate\Support\Facades\Storage::disk('public')->files($carpeta);
                                                foreach($archivosEnServidor as $archivoFisico) {
                                                    if (strtolower(basename($archivoFisico)) === $archivoBuscado) {
                                                        $archivoReal = basename($archivoFisico); 
                                                        break;
                                                    }
                                                }
                                                $rutaFinal = ($carpeta === '.') ? $archivoReal : $carpeta . '/' . $archivoReal;
                                            @endphp
                                            <a href="{{ asset('storage/' . $rutaFinal) }}" target="_blank" 
                                            class="p-1.5 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 transition-all" 
                                            title="Ver acta consolidada firmada">
                                                <i data-lucide="file-check-2" class="w-4 h-4"></i>
                                            </a>
                                            <button onclick="confirmarEnvioCorreoMonitoreo({{ $monitoreo->id }})"
                                                class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition-all"
                                                title="Enviar Acta por Correo">
                                                <i data-lucide="mail" class="w-4 h-4"></i>
                                            </button>
                                        @endif

                                        <a href="{{ route('usuario.monitoreo.edit', $monitoreo->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" title="Editar acta">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </a>
                                        
                                        <a href="{{ route('usuario.monitoreo.visual-signature', $monitoreo->id) }}" 
                                             class="btn-firma-visual-hidden hidden p-1.5 rounded-lg text-orange-600 hover:text-orange-900 hover:bg-orange-50 transition-all border border-transparent hover:border-orange-200" 
                                             title="Firmar Acta Visualmente">
                                              <i data-lucide="pen-tool" class="w-4 h-4"></i>
                                        </a>

                                        {{-- FIRMA_VISUAL_OCULTO --}}
                                        @endif

                                        <button onclick="confirmarAnulacion({{ $monitoreo->id }}, {{ $monitoreo->anulado ? 'true' : 'false' }})"
                                            class="p-1.5 {{ $monitoreo->anulado ? 'text-emerald-500 hover:bg-emerald-50' : 'text-red-400 hover:bg-red-50' }} transition-all rounded-lg"
                                            title="{{ $monitoreo->anulado ? 'Reactivar Acta' : 'Anular Acta' }}">
                                            <i data-lucide="{{ $monitoreo->anulado ? 'rotate-ccw' : 'ban' }}" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-12 text-center text-slate-400">No se encontraron registros de monitoreo para el periodo seleccionado</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($monitoreos->hasPages())
            <div class="mt-4">{{ $monitoreos->appends(request()->query())->links() }}</div>
        @endif
    </div>

    {{-- Formulario oculto para exportar Excel --}}
    <form id="excelForm" method="POST" action="{{ route('usuario.reportes.actas.monitoreo.excel') }}" style="display:none;">
        @csrf
        <input type="hidden" name="fecha_inicio"       value="{{ $valInicio }}">
        <input type="hidden" name="fecha_fin"           value="{{ $valFin }}">
        <input type="hidden" name="implementador"       value="{{ request('implementador') }}">
        <input type="hidden" name="provincia"           value="{{ request('provincia') }}">
        <input type="hidden" name="distrito"            value="{{ request('distrito') }}">
        <input type="hidden" name="establecimiento_id"  value="{{ request('establecimiento_id') }}">
        <input type="hidden" name="firmado"             value="{{ request('estado') === 'firmada' ? '1' : (request('estado') === 'pendiente' ? '0' : '') }}">
    </form>

    {{-- Formulario oculto para exportar PDF Consolidado --}}
    <form id="pdfConsolidadoForm" method="POST" action="{{ route('usuario.monitoreo.consolidadoPDFExport') }}" style="display:none;">
        @csrf
        <input type="hidden" name="fecha_inicio"       value="{{ $valInicio }}">
        <input type="hidden" name="fecha_fin"           value="{{ $valFin }}">
        <input type="hidden" name="implementador"       value="{{ request('implementador') }}">
        <input type="hidden" name="provincia"           value="{{ request('provincia') }}">
        <input type="hidden" name="distrito"            value="{{ request('distrito') }}">
        <input type="hidden" name="establecimiento_id"  value="{{ request('establecimiento_id') }}">
    </form>

    {{-- MENU CONTEXTUAL PARA FIRMA VISUAL --}}
    <div id="context-menu" class="hidden fixed z-[9999] w-64 bg-white rounded-2xl shadow-2xl border border-slate-200 p-2 animate-in fade-in zoom-in duration-200">
        <div class="px-3 py-2 border-b border-slate-100 mb-1">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Opciones de Acta</p>
        </div>
        <a id="ctx-visual-sig" href="#" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-orange-600 hover:bg-orange-50 rounded-xl transition-all hidden">
            <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">
                <i data-lucide="pen-tool" class="w-4 h-4"></i>
            </div>
            <span>Firmar Acta Visualmente</span>
        </a>
        <a id="ctx-edit" href="#" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-xl transition-all">
            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                <i data-lucide="pencil" class="w-4 h-4"></i>
            </div>
            Editar Acta
        </a>
        <a id="ctx-pdf" href="#" target="_blank" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-red-50 hover:text-red-700 rounded-xl transition-all">
            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                <i data-lucide="file-text" class="w-4 h-4"></i>
            </div>
            Ver PDF Consolidado
        </a>
        <div class="mt-2 pt-2 border-t border-slate-100">
            <p class="text-[9px] text-center text-slate-400 italic">Click fuera para cerrar</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const provinciaSelect = document.getElementById('provinciaSelect');
            const distritoSelect = document.getElementById('distritoSelect');
            const establecimientoSelect = document.getElementById('establecimientoSelect');

            if (provinciaSelect) {
                provinciaSelect.addEventListener('change', async () => {
                    const provincia = provinciaSelect.value;
                    
                    // Limpiar selectores dependientes
                    distritoSelect.innerHTML = '<option value="">TODOS</option>';
                    establecimientoSelect.innerHTML = '<option value="">TODOS</option>';

                    if (provincia) {
                        const resDist = await fetch(`{{ route('usuario.monitoreo.ajax.distritos') }}?provincia=${provincia}`);
                        const distritos = await resDist.json();
                        distritos.forEach(d => {
                            const opt = document.createElement('option');
                            opt.value = d;
                            opt.textContent = d;
                            distritoSelect.appendChild(opt);
                        });
                        actualizarEstablecimientos(provincia, '');
                    } else {
                        actualizarEstablecimientos('', '');
                    }
                });
            }

            if (distritoSelect) {
                distritoSelect.addEventListener('change', async () => {
                    const provincia = provinciaSelect.value;
                    const distrito = distritoSelect.value;
                    actualizarEstablecimientos(provincia, distrito);
                });
            }

            async function actualizarEstablecimientos(provincia, distrito) {
                if (!establecimientoSelect) return;
                establecimientoSelect.innerHTML = '<option value="">TODOS</option>';
                const resEst = await fetch(`{{ route('usuario.monitoreo.ajax.establecimientos') }}?provincia=${provincia}&distrito=${distrito}`);
                const establecimientos = await resEst.json();
                establecimientos.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.id;
                    opt.textContent = e.nombre;
                    establecimientoSelect.appendChild(opt);
                });
            }
        });

        function exportarExcel() {
            document.getElementById('excelForm').submit();
        }

        async function exportarPDF() {
            const form = document.getElementById('pdfConsolidadoForm');
            const formData = new FormData(form);
            
            Swal.fire({
                title: 'Generando documento',
                text: 'Descargando y fusionando los PDFs, por favor espere...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Error al obtener los documentos');
                }

                if (data.urls && data.urls.length > 0) {
                    const { PDFDocument } = PDFLib;
                    const mergedPdf = await PDFDocument.create();

                    for (const url of data.urls) {
                        try {
                            const pdfBytes = await fetch(url).then(res => res.arrayBuffer());
                            const pdfDoc = await PDFDocument.load(pdfBytes);
                            const copiedPages = await mergedPdf.copyPages(pdfDoc, pdfDoc.getPageIndices());
                            copiedPages.forEach((page) => {
                                mergedPdf.addPage(page);
                            });
                        } catch (err) {
                            console.error("Error cargando PDF: " + url, err);
                        }
                    }

                    const mergedPdfFile = await mergedPdf.save();
                    const blob = new Blob([mergedPdfFile], { type: 'application/pdf' });
                    const blobUrl = URL.createObjectURL(blob);
                    
                    window.open(blobUrl, '_blank');
                    
                    let summaryHtml = `<div class="text-left mt-2">
                        <p class="mb-2">Se ha generado el documento con <b>${data.incluidas}</b> de <b>${data.total}</b> actas firmadas.</p>`;
                    if (data.omitidas > 0) {
                        summaryHtml += `<div class="p-3 bg-red-50 text-red-700 rounded-lg border border-red-200 text-sm">
                            <div class="mb-2"><i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                            <b>${data.omitidas} actas omitidas</b> por no tener archivo:</div>`;
                        
                        if (data.lista_omitidas && data.lista_omitidas.length > 0) {
                            summaryHtml += `<ul class="list-disc list-inside text-xs max-h-32 overflow-y-auto bg-white/50 p-2 rounded">`;
                            data.lista_omitidas.forEach(idText => {
                                summaryHtml += `<li>${idText}</li>`;
                            });
                            summaryHtml += `</ul>`;
                        }
                        summaryHtml += `</div>`;
                    }
                    summaryHtml += `</div>`;

                    Swal.fire({
                        icon: 'success',
                        title: 'PDF Generado',
                        html: summaryHtml,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#10b981',
                        didOpen: () => {
                            if (window.lucide) window.lucide.createIcons();
                        }
                    });
                } else {
                    throw new Error('No se encontraron URLs de PDFs.');
                }

            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        }

        function abrirModalSubir(id) {
            const baseUrl = "{{ url('/usuario/monitoreo') }}";
            const rutaFirma = `${baseUrl}/${id}/visual-signature`;

            const opc1Hidden = _firmaSecretaDesbloqueada ? '' : 'hidden';
            const opc1Style  = _firmaSecretaDesbloqueada ? 'display:block;' : 'display:none;';
            const labelOpc2  = _firmaSecretaDesbloqueada ? 'Opción 2: Subir PDF escaneado/firmado' : 'Subir PDF escaneado/firmado';

            Swal.fire({
                title: 'Finalizar / Firmar Acta Consolidada',
                html: `
                    <div class="space-y-4 p-2 text-left">
                        <!-- Opción 1: Firmar Acta Visualmente (se revela con 'jojojo') -->
                        <div id="swal-opcion-1-firma" class="${opc1Hidden} transition-all duration-300 mb-4 p-3.5 bg-orange-50 border border-orange-200 rounded-xl" style="${opc1Style}">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-orange-500 text-white rounded-lg shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19 7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18"/><path d="m2 2 7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xs font-black text-orange-900 uppercase tracking-wider">Opción 1: Firmar Acta Visualmente</h4>
                                    <p class="text-[11px] text-orange-700 font-medium mt-0.5">Diseñe y estampe las firmas interactivamente en la plataforma.</p>
                                    <a href="${rutaFirma}" class="mt-2.5 inline-flex items-center gap-2 px-3.5 py-1.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-lg shadow-sm transition-all hover:scale-[1.02]">
                                        <span>Abrir Editor de Firma</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Opción 2: Subir PDF escaneado/firmado -->
                        <div class="p-1">
                            <label id="swal-opcion-2-label" class="text-[10px] font-bold text-slate-500 uppercase mb-1.5 block tracking-wider">
                                ${labelOpc2}
                            </label>
                            <input type="file" id="swal-input-file" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-2" accept="application/pdf">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Subir Archivo Seleccionado',
                cancelButtonText: 'Cerrar',
                confirmButtonColor: '#3b82f6',
                showLoaderOnConfirm: true,
                didOpen: () => {
                    if (window.lucide) window.lucide.createIcons();
                },
                preConfirm: () => {
                    const file = document.getElementById('swal-input-file').files[0];
                    if (!file) {
                        Swal.showValidationMessage('Debe seleccionar un archivo PDF para la Opción 2');
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('pdf_firmado', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    return fetch(`/usuario/monitoreo/${id}/subir-consolidado-final`, {
                        method: 'POST',
                        body: formData,
                        headers: { 
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        if (!response.ok) return response.json().then(err => { throw new Error(err.message || 'Error en el servidor'); });
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Hubo un problema: ${error.message}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Acta Cargada!',
                        text: 'El acta firmada ha sido subida correctamente.',
                        timer: 2000
                    }).then(() => location.reload());
                }
            });
        }

        function confirmarEnvioCorreoMonitoreo(id) {
            Swal.fire({
                title: 'Preparando envío...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const baseUrl = "{{ url('/') }}";
            fetch(`${baseUrl}/usuario/monitoreo/${id}/emails`)
                .then(r => r.json())
                .then(data => {
                    const defaultEmails = data.emails || [];
                    const html = `
                        <div class="text-left mb-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Detalles del Acta</p>
                            <div class="grid grid-cols-2 gap-1.5 text-[11px]">
                                <div><span class="text-slate-500">🔢</span> <span class="font-bold">N° ${data.numero}</span></div>
                                <div><span class="text-slate-500">📅</span> <span class="font-bold">${data.fecha}</span></div>
                                <div class="col-span-2"><span class="text-slate-500">🏥</span> <span class="font-bold">${data.establecimiento}</span></div>
                            </div>
                        </div>
                        <div class="text-left mb-2">
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Destinatarios</label>
                                ${defaultEmails.length > 0 ? '<button type="button" id="mon-btn-precargar" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 bg-blue-50 border border-blue-200 rounded-lg px-2 py-1 transition-all">⚡ Precargar profesionales ('+defaultEmails.length+')</button>' : '<span class="text-[10px] text-slate-400 italic">Sin correos en los módulos</span>'}
                            </div>
                            <div id="mon-chips-wrapper" class="email-chips-container">
                                <input type="text" id="mon-tag-input" class="email-chip-input" placeholder="Escriba un correo y presione Enter o ;">
                            </div>
                            <p class="text-[9px] text-slate-400 mt-1 italic">Use coma (,), punto y coma (;) o Enter para agregar.</p>
                            <div id="mon-list-preview" class="mt-2 hidden">
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mb-1">📋 Lista de envío:</p>
                                <div id="mon-list-container" class="flex flex-wrap gap-1 max-h-16 overflow-y-auto"></div>
                            </div>
                        </div>
                    `;

                    Swal.fire({
                        title: '✉️ Enviar Acta por Correo',
                        html: html,
                        showCancelButton: true,
                        confirmButtonText: '🚀 Enviar Ahora',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#1d4ed8',
                        width: '520px',
                        didOpen: () => {
                            const wrapper = document.getElementById('mon-chips-wrapper');
                            const input = document.getElementById('mon-tag-input');
                            const listPreview = document.getElementById('mon-list-preview');
                            const listContainer = document.getElementById('mon-list-container');
                            const tags = new Set();

                            const renderTags = () => {
                                wrapper.querySelectorAll('.email-chip').forEach(c => c.remove());
                                listContainer.innerHTML = '';
                                tags.forEach(email => {
                                    const chip = document.createElement('div');
                                    chip.className = 'email-chip';
                                    chip.innerHTML = `${email} <button type="button" data-email="${email}"><i data-lucide="x" class="w-3 h-3"></i></button>`;
                                    wrapper.insertBefore(chip, input);
                                    const item = document.createElement('span');
                                    item.className = 'inline-flex items-center gap-1 text-[10px] bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded-full';
                                    item.innerHTML = `<i data-lucide="mail" class="w-2.5 h-2.5"></i> ${email}`;
                                    listContainer.appendChild(item);
                                });
                                listPreview.classList.toggle('hidden', tags.size === 0);
                                if (window.lucide) window.lucide.createIcons();
                            };

                            const btnPrecargar = document.getElementById('mon-btn-precargar');
                            if (btnPrecargar) {
                                btnPrecargar.addEventListener('click', () => {
                                    defaultEmails.forEach(e => tags.add(e.toLowerCase()));
                                    renderTags();
                                    btnPrecargar.disabled = true;
                                    btnPrecargar.textContent = '✅ Equipo cargado';
                                    btnPrecargar.className = 'text-[10px] font-bold text-slate-400 bg-slate-50 border border-slate-200 rounded-lg px-2 py-1';
                                });
                            }

                            wrapper.addEventListener('click', () => input.focus());

                            input.addEventListener('keydown', (e) => {
                                if ([';', ',', 'Enter'].includes(e.key)) {
                                    e.preventDefault();
                                    const val = input.value.trim().toLowerCase();
                                    if (val && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                                        tags.add(val);
                                        input.value = '';
                                        renderTags();
                                    } else if (val) {
                                        input.classList.add('shake');
                                        setTimeout(() => input.classList.remove('shake'), 300);
                                    }
                                }
                                if (e.key === 'Backspace' && !input.value && tags.size > 0) {
                                    const last = Array.from(tags).pop();
                                    tags.delete(last);
                                    renderTags();
                                }
                            });

                            wrapper.addEventListener('click', (e) => {
                                const btn = e.target.closest('button');
                                if (btn && btn.dataset.email) {
                                    tags.delete(btn.dataset.email);
                                    renderTags();
                                }
                            });

                            window._monCurrentTags = tags;
                        },
                        preConfirm: () => {
                            const tags = Array.from(window._monCurrentTags);
                            if (tags.length === 0) {
                                Swal.showValidationMessage('Debe ingresar al menos un correo válido');
                                return false;
                            }
                            return fetch(`${baseUrl}/usuario/monitoreo/${id}/enviar-correo`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ correos: tags.join(',') })
                            })
                            .then(r => {
                                if (!r.ok) return r.json().then(e => { throw new Error(e.message || 'Error al enviar'); });
                                return r.json();
                            })
                            .catch(error => Swal.showValidationMessage(`Error: ${error.message}`));
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                icon: 'success',
                                title: '✅ ¡Correos Enviados!',
                                text: result.value.message || 'El acta ha sido enviada exitosamente.',
                                confirmButtonColor: '#1d4ed8'
                            });
                        }
                    });
                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudieron obtener los datos del acta.', 'error');
                });
        }



        // Lógica del Menú Contextual
        const contextMenu = document.getElementById('context-menu');
        const ctxVisualSig = document.getElementById('ctx-visual-sig');
        const ctxEdit = document.getElementById('ctx-edit');
        const ctxPdf = document.getElementById('ctx-pdf');

        document.querySelectorAll('table tbody tr').forEach(row => {
            row.addEventListener('contextmenu', (e) => {
                const id = row.dataset.id;
                if (!id) return;

                e.preventDefault();
                
                // Configurar links
                ctxVisualSig.href = `/usuario/monitoreo/${id}/visual-signature`;
                ctxEdit.href = `/usuario/monitoreo/${id}/editar-acta`;
                ctxPdf.href = `/usuario/monitoreo/${id}/pdf`;

                // Posicionar menú
                contextMenu.classList.remove('hidden');
                contextMenu.style.top = `${e.pageY}px`;
                contextMenu.style.left = `${e.pageX}px`;

                // Evitar que el menú se salga por la derecha
                const menuWidth = contextMenu.offsetWidth;
                const windowWidth = window.innerWidth;
                if (e.pageX + menuWidth > windowWidth) {
                    contextMenu.style.left = `${e.pageX - menuWidth}px`;
                }
            });
        });

        // Cerrar menú contextual al hacer click fuera
        document.addEventListener('click', (e) => {
            if (!contextMenu.contains(e.target)) {
                contextMenu.classList.add('hidden');
            }
        });

        function confirmarAnulacion(id, esAnulada) {
            const accion = esAnulada ? 'Reactivar' : 'Anular';
            const icono = esAnulada ? 'question' : 'warning';
            const color = esAnulada ? '#10b981' : '#ef4444';
            const baseUrl = "{{ url('/') }}";

            Swal.fire({
                title: `¿${accion} Acta?`,
                text: `¿Está seguro que desea ${accion.toLowerCase()} esta acta de monitoreo?`,
                icon: icono,
                showCancelButton: true,
                confirmButtonText: `Sí, ${accion.toLowerCase()}`,
                cancelButtonText: 'Cancelar',
                confirmButtonColor: color,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(`${baseUrl}/usuario/monitoreo/${id}/anular`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(r => {
                        if (!r.ok) return r.json().then(e => { throw new Error(e.message); });
                        return r.json();
                    })
                    .catch(error => Swal.showValidationMessage(`Error: ${error}`));
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: result.value.anulado ? '¡Acta Anulada!' : '¡Acta Reactivada!',
                        text: result.value.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                }
            });
        }

        @if(Auth::user()->role === 'admin')
        function abrirModalCambiarAutor(id, currentUserId, currentUserName) {
            const usuarios = @json($usuariosActivos);
            
            let optionsHtml = '';
            usuarios.forEach(u => {
                const nombreCompleto = `${u.apellido_paterno} ${u.apellido_materno}, ${u.name}`.toUpperCase();
                const selected = u.id == currentUserId ? 'selected' : '';
                optionsHtml += `<option value="${u.id}" ${selected}>${nombreCompleto} (${u.role.toUpperCase()})</option>`;
            });

            const html = `
                <div class="text-left mb-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Autor Actual</p>
                    <p class="text-xs font-bold text-slate-700">${currentUserName}</p>
                </div>
                <div class="text-left">
                    <label for="swal-new-user-id" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Seleccionar Nuevo Autor</label>
                    <select id="swal-new-user-id" class="block w-full py-2.5 px-3 border border-slate-200 rounded-xl bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-xs cursor-pointer">
                        ${optionsHtml}
                    </select>
                </div>
            `;

            Swal.fire({
                title: '👤 Cambiar Autor del Acta',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Guardar Cambios',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#4f46e5',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const newUserVal = document.getElementById('swal-new-user-id').value;
                    if (!newUserVal) {
                        Swal.showValidationMessage('Debe seleccionar un usuario');
                        return false;
                    }
                    
                    const baseUrl = "{{ url('/') }}";
                    return fetch(`${baseUrl}/usuario/monitoreo/${id}/cambiar-autor`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ user_id: newUserVal })
                    })
                    .then(r => {
                        if (!r.ok) return r.json().then(e => { throw new Error(e.message || 'Error al actualizar'); });
                        return r.json();
                    })
                    .catch(error => Swal.showValidationMessage(`Error: ${error.message}`));
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Autor Actualizado!',
                        text: result.value.message || 'El autor del acta se ha cambiado correctamente.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => window.location.reload());
                }
            });
        }
        @endif

        // --- ACCESO SECRETO: TOGGLE FIRMA VISUAL ("jojojo") ---
        let _firmaSecretaDesbloqueada = false;
        let _secretBuffer = '';

        function _desbloquearFirmaVisual(mostrarToast = true) {
            _firmaSecretaDesbloqueada = true;
            try { localStorage.setItem('firma_secreta_activa', 'true'); } catch(e){}

            // 1. Desocultar botones en las filas de la tabla
            document.querySelectorAll('.btn-firma-visual-hidden').forEach(el => {
                el.classList.remove('hidden');
                el.style.display = 'inline-flex';
            });

            // 2. Desocultar opción en el menú contextual
            const ctxVisualSig = document.getElementById('ctx-visual-sig');
            if (ctxVisualSig) {
                ctxVisualSig.classList.remove('hidden');
                ctxVisualSig.style.display = 'flex';
            }

            // 3. Desocultar Opción 1 dentro del modal de SweetAlert (si está abierto)
            const swalOpc1 = document.getElementById('swal-opcion-1-firma');
            if (swalOpc1) {
                swalOpc1.classList.remove('hidden');
                swalOpc1.style.display = 'block';
            }
            const swalOpc2Label = document.getElementById('swal-opcion-2-label');
            if (swalOpc2Label) {
                swalOpc2Label.textContent = 'Opción 2: Subir PDF escaneado/firmado';
            }

            // 4. Feedback visual con imagen del aviso (overlay limpio sin contenedores extra)
            if (mostrarToast) {
                const avisoImgUrl = "{{ asset('storage/aviso/8427ac357141001bae14eb38e78856dd.jpg') }}?v={{ @filemtime(public_path('storage/aviso/8427ac357141001bae14eb38e78856dd.jpg')) ?: time() }}";
                const overlay = document.createElement('div');
                overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);z-index:999999;display:flex;align-items:center;justify-content:center;transition:opacity 0.25s ease;';
                overlay.innerHTML = `
                    <div style="max-width:480px;width:90vw;border-radius:1.5rem;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,0.6);transform:scale(1);">
                        <img src="${avisoImgUrl}" alt="¡AAAAHH, CONOCES LA LLAVE!" style="width:100%;height:auto;display:block;border-radius:1.5rem;">
                    </div>
                `;
                document.body.appendChild(overlay);
                setTimeout(() => {
                    overlay.style.opacity = '0';
                    setTimeout(() => overlay.remove(), 250);
                }, 2200);
            }

            if (window.lucide) window.lucide.createIcons();
        }

        function _bloquearFirmaVisual(mostrarToast = true) {
            _firmaSecretaDesbloqueada = false;
            try { localStorage.removeItem('firma_secreta_activa'); } catch(e){}

            // 1. Ocultar botones en las filas de la tabla
            document.querySelectorAll('.btn-firma-visual-hidden').forEach(el => {
                el.classList.add('hidden');
                el.style.display = 'none';
            });

            // 2. Ocultar opción en el menú contextual
            const ctxVisualSig = document.getElementById('ctx-visual-sig');
            if (ctxVisualSig) {
                ctxVisualSig.classList.add('hidden');
                ctxVisualSig.style.display = 'none';
            }

            // 3. Ocultar Opción 1 dentro del modal de SweetAlert (si está abierto)
            const swalOpc1 = document.getElementById('swal-opcion-1-firma');
            if (swalOpc1) {
                swalOpc1.classList.add('hidden');
                swalOpc1.style.display = 'none';
            }
            const swalOpc2Label = document.getElementById('swal-opcion-2-label');
            if (swalOpc2Label) {
                swalOpc2Label.textContent = 'Subir PDF escaneado/firmado';
            }
        }

        function _toggleFirmaVisual() {
            if (_firmaSecretaDesbloqueada) {
                _bloquearFirmaVisual(true);
            } else {
                _desbloquearFirmaVisual(true);
            }
        }

        // Si ya se desbloqueó anteriormente en este navegador, mantenerlo activo
        if (localStorage.getItem('firma_secreta_activa') === 'true') {
            _firmaSecretaDesbloqueada = true;
            document.addEventListener('DOMContentLoaded', () => {
                _desbloquearFirmaVisual(false);
            });
        }

        function _procesarTeclaSecreta(val) {
            if (!val) return;
            
            _secretBuffer += String(val).toLowerCase();
            if (_secretBuffer.length > 30) _secretBuffer = _secretBuffer.slice(-30);

            if (_secretBuffer.includes('jojojo')) {
                _secretBuffer = ''; // Reiniciar buffer para evitar disparo continuo
                _toggleFirmaVisual();
            }
        }

        // Listener keyup en window (con captura)
        window.addEventListener('keyup', function(e) {
            if (e.key && e.key.length === 1) {
                _procesarTeclaSecreta(e.key);
            }
        }, true);

        document.addEventListener('input', function(e) {
            if (e.target && e.target.value) {
                _procesarTeclaSecreta(e.target.value);
            }
        }, true);
    </script>
@endpush