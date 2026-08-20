@extends('layouts.usuario')

@section('title', 'Módulo / Consultorio: ' . ($tituloConsultorio ?? 'Evaluación'))

@section('content')
    <div class="py-10 bg-slate-50/80 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- ENCABEZADO SUPERIOR MODERNO --}}
            <div class="mb-8 bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
                <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-full blur-2xl pointer-events-none"></div>

                <div class="flex items-start sm:items-center gap-5 relative z-10">
                    <div class="h-16 w-16 rounded-2xl bg-gradient-to-tr from-indigo-600 via-blue-600 to-cyan-500 flex items-center justify-center text-white shadow-lg shadow-indigo-200 flex-shrink-0">
                        <i data-lucide="stethoscope" class="w-8 h-8"></i>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
                            <span class="px-3 py-1 bg-indigo-50 border border-indigo-200/70 text-indigo-700 text-[10px] font-black rounded-lg uppercase tracking-wider">
                                Módulo Dinámico
                            </span>
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-extrabold rounded-lg uppercase tracking-wider">
                                ID Acta: #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 uppercase tracking-tight">
                            {{ $tituloConsultorio ?? 'CONSULTORIO / MÓDULO' }}
                        </h2>
                        <div class="text-slate-500 font-bold uppercase text-xs mt-2 flex flex-wrap items-center gap-x-5 gap-y-1.5">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="building-2" class="w-4 h-4 text-indigo-500"></i>
                                {{ $acta->establecimiento->nombre }}
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="user-check" class="w-4 h-4 text-emerald-500"></i>
                                Implementador: {{ $acta->implementador ?? ($acta->user ? "{$acta->user->apellido_paterno} {$acta->user->name}" : 'NO ASIGNADO') }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 flex-shrink-0">
                    <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}"
                        class="inline-flex items-center gap-2 px-5 py-3 bg-slate-100 hover:bg-slate-200/80 text-slate-700 font-extrabold text-xs rounded-2xl transition-all uppercase tracking-wider border border-slate-200 shadow-sm">
                        <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
                        <span>Volver a Módulos</span>
                    </a>
                </div>
            </div>

            <form action="{{ route('usuario.monitoreo.consultorio.store', [$acta->id, $slug]) }}" method="POST"
                enctype="multipart/form-data" class="space-y-8" id="form-monitoreo-final">
                @csrf

                @php
                    $contenido = $detalle->contenido ?? [];
                @endphp

                {{-- 1.- DATOS GENERALES --}}
                <div class="monitoreo-section bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between gap-3 mb-6 border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="section-number bg-gradient-to-r from-blue-600 to-indigo-600 text-white w-9 h-9 flex items-center justify-center rounded-xl font-black text-sm shadow-md shadow-indigo-100">
                                1
                            </div>
                            <div>
                                <h3 class="text-slate-900 font-black text-base sm:text-lg uppercase tracking-tight">
                                    DATOS GENERALES DEL CONSULTORIO / MÓDULO
                                </h3>
                                <p class="text-xs text-slate-400 font-semibold">Identificación, turno y especificaciones del ambiente evaluado</p>
                            </div>
                        </div>
                    </div>

                    {{-- DENOMINACIÓN Y SERVICIO --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-indigo-50/40 p-5 rounded-2xl border border-indigo-100">
                            <label class="block text-indigo-900 text-[11px] font-black uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <i data-lucide="tag" class="w-3.5 h-3.5 text-indigo-600"></i> Nombre o Denominación del Consultorio / Módulo <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="contenido[titulo_consultorio]" 
                                value="{{ $contenido['titulo_consultorio'] ?? ($tituloConsultorio ?? 'CONSULTORIO') }}" required 
                                placeholder="EJ: GESTIÓN ADMINISTRATIVA, CONSULTORIO DE MEDICINA 01, TRIAJE..." 
                                class="w-full bg-white border-2 border-indigo-200/80 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 rounded-xl px-4 py-3 font-black text-indigo-950 text-sm uppercase outline-none transition-all shadow-sm">
                        </div>

                        <div class="bg-slate-50/60 p-5 rounded-2xl border border-slate-200/80">
                            <label class="block text-slate-700 text-[11px] font-black uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <i data-lucide="activity" class="w-3.5 h-3.5 text-slate-500"></i> Servicio Asociado al Consultorio
                            </label>
                            <input type="text" name="contenido[servicio_asociado]" 
                                value="{{ $contenido['servicio_asociado'] ?? '' }}" 
                                placeholder="INGRESE EL SERVICIO DEL CONSULTORIO..." 
                                class="w-full bg-white border-2 border-slate-200 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 rounded-xl px-4 py-3 font-bold text-slate-800 text-sm uppercase outline-none transition-all shadow-sm">
                        </div>
                    </div>

                    {{-- 4 CAMPOS BÁSICOS --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                        <div class="bg-slate-50/60 p-4 rounded-2xl border border-slate-200/60">
                            <label class="block text-slate-600 text-[10px] font-black uppercase tracking-wider mb-2 flex items-center gap-1">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i> Fecha de Monitoreo <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="contenido[fecha]" value="{{ $contenido['fecha'] ?? date('Y-m-d') }}" required
                                class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2.5 text-slate-800 font-bold text-xs outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                        <div class="bg-slate-50/60 p-4 rounded-2xl border border-slate-200/60">
                            <x-turno :selected="$contenido['turno'] ?? ''" />
                        </div>
                        <div class="bg-slate-50/60 p-4 rounded-2xl border border-slate-200/60">
                            <label class="block text-slate-600 text-[10px] font-black uppercase tracking-wider mb-2 flex items-center gap-1">
                                <i data-lucide="layout-grid" class="w-3.5 h-3.5 text-slate-400"></i> Tipo de Consultorio
                            </label>
                            <select name="contenido[tipo_consultorio]" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl font-bold text-xs uppercase outline-none text-slate-800 cursor-pointer focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all">
                                <option value="FISICO" {{ ($contenido['tipo_consultorio'] ?? '') == 'FISICO' || ($contenido['tipo_consultorio'] ?? '') == 'FÍSICO' ? 'selected' : '' }}>FÍSICO</option>
                                <option value="FUNCIONAL" {{ ($contenido['tipo_consultorio'] ?? '') == 'FUNCIONAL' ? 'selected' : '' }}>FUNCIONAL</option>
                            </select>
                        </div>
                        <div class="bg-slate-50/60 p-4 rounded-2xl border border-slate-200/60">
                            <label class="block text-slate-600 text-[10px] font-black uppercase tracking-wider mb-2 flex items-center gap-1">
                                <i data-lucide="layers" class="w-3.5 h-3.5 text-slate-400"></i> Nivel / Piso
                            </label>
                            <input type="number" name="contenido[piso]" min="1" max="99" 
                                value="{{ preg_replace('/[^0-9]/', '', $contenido['piso'] ?? '1') ?: '1' }}" 
                                placeholder="Ej: 1"
                                class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl font-bold text-xs outline-none text-slate-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 transition-all">
                        </div>
                    </div>

                    {{-- AIRE ACONDICIONADO --}}
                    @php $aireAcondicionado = strtoupper($contenido['aire_acondicionado'] ?? 'NO'); @endphp
                    <div class="mt-5 bg-slate-50/70 border border-slate-200 rounded-2xl p-5">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="wind" class="w-4 h-4"></i>
                            </div>
                            <label class="text-xs font-black text-slate-800 uppercase tracking-tight">
                                ¿El consultorio cuenta con aire acondicionado?
                            </label>
                        </div>
                        <div class="grid grid-cols-2 gap-3 max-w-xs">
                            <label class="relative flex items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $aireAcondicionado === 'SI' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                <input type="radio" name="contenido[aire_acondicionado]" value="SI" {{ $aireAcondicionado === 'SI' ? 'checked' : '' }} class="sr-only"
                                    onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'shadow-sm');">
                                <span class="font-black text-xs uppercase flex items-center gap-1.5"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i> SÍ</span>
                            </label>
                            <label class="relative flex items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $aireAcondicionado === 'NO' ? 'border-rose-500 bg-rose-50 text-rose-800 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                <input type="radio" name="contenido[aire_acondicionado]" value="NO" {{ $aireAcondicionado === 'NO' ? 'checked' : '' }} class="sr-only"
                                    onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm');">
                                <span class="font-black text-xs uppercase flex items-center gap-1.5"><i data-lucide="x-circle" class="w-4 h-4 text-rose-600"></i> NO</span>
                            </label>
                        </div>
                    </div>

                    {{-- CONDICIONES BÁSICAS DE RED Y ENERGÍA --}}
                    <div class="mt-7 pt-6 border-t border-slate-100">
                        <label class="block text-slate-800 text-xs font-black uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i data-lucide="power" class="w-4 h-4 text-indigo-600"></i> Condiciones e Instalaciones Básicas del Ambiente
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            {{-- PREGUNTA 1: ELECTRICIDAD --}}
                            <div class="bg-slate-50/70 border border-slate-200 rounded-2xl p-5">
                                <div class="flex items-center gap-2.5 mb-3">
                                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="zap" class="w-4 h-4"></i>
                                    </div>
                                    <label class="text-xs font-black text-slate-800 uppercase tracking-tight">
                                        ¿El consultorio cuenta con electricidad?
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    @php $electricidad = strtoupper($contenido['cuenta_electricidad'] ?? 'SI'); @endphp
                                    <label class="relative flex items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $electricidad === 'SI' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                        <input type="radio" name="contenido[cuenta_electricidad]" value="SI" {{ $electricidad === 'SI' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'shadow-sm');">
                                        <span class="font-black text-xs uppercase flex items-center gap-1.5"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i> SÍ (CUENTA)</span>
                                    </label>
                                    <label class="relative flex items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $electricidad === 'NO' ? 'border-rose-500 bg-rose-50 text-rose-800 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                        <input type="radio" name="contenido[cuenta_electricidad]" value="NO" {{ $electricidad === 'NO' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm');">
                                        <span class="font-black text-xs uppercase flex items-center gap-1.5"><i data-lucide="x-circle" class="w-4 h-4 text-rose-600"></i> NO CUENTA</span>
                                    </label>
                                </div>
                            </div>

                            {{-- PREGUNTA 2: PUNTO DE RED --}}
                            <div class="bg-slate-50/70 border border-slate-200 rounded-2xl p-5">
                                <div class="flex items-center gap-2.5 mb-3">
                                    <div class="w-8 h-8 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center flex-shrink-0">
                                        <i data-lucide="network" class="w-4 h-4"></i>
                                    </div>
                                    <label class="text-xs font-black text-slate-800 uppercase tracking-tight">
                                        ¿El consultorio cuenta con punto de red?
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    @php $puntoRed = strtoupper($contenido['cuenta_punto_red'] ?? 'SI'); @endphp
                                    <label class="relative flex items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $puntoRed === 'SI' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                        <input type="radio" name="contenido[cuenta_punto_red]" value="SI" {{ $puntoRed === 'SI' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'shadow-sm');">
                                        <span class="font-black text-xs uppercase flex items-center gap-1.5"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i> SÍ (HABILITADO)</span>
                                    </label>
                                    <label class="relative flex items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $puntoRed === 'NO' ? 'border-rose-500 bg-rose-50 text-rose-800 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                        <input type="radio" name="contenido[cuenta_punto_red]" value="NO" {{ $puntoRed === 'NO' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm');">
                                        <span class="font-black text-xs uppercase flex items-center gap-1.5"><i data-lucide="x-circle" class="w-4 h-4 text-rose-600"></i> NO CUENTA</span>
                                    </label>
                                </div>

                                {{-- CAMPO DINÁMICO: CANTIDAD DE PUNTOS DE RED CUANDO ES SÍ --}}
                                @php $cantPuntosRed = $contenido['cantidad_puntos_red'] ?? 1; @endphp
                                <div id="container_cantidad_puntos_red" class="mt-4 pt-3 border-t border-slate-200/70 {{ $puntoRed === 'SI' ? '' : 'hidden' }}">
                                    <label class="block text-[10px] font-black text-indigo-900 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                                        <i data-lucide="hash" class="w-3.5 h-3.5 text-indigo-600"></i> Cantidad de Puntos de Red (Mínimo: 1) <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="number" min="1" max="99" step="1" name="contenido[cantidad_puntos_red]" id="input_cantidad_puntos_red"
                                        value="{{ max(1, (int)$cantPuntosRed) }}"
                                        placeholder="Ej: 1"
                                        onkeydown="if(event.key==='-'||event.key==='+'||event.key==='e'||event.key==='E'||event.key==='.') event.preventDefault();"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value && parseInt(this.value) < 1) this.value = '1';"
                                        onblur="if(!this.value || parseInt(this.value) < 1) this.value = '1';"
                                        class="w-full px-4 py-2.5 bg-white border-2 border-indigo-200 focus:border-indigo-600 rounded-xl font-black text-xs text-indigo-900 outline-none transition-all shadow-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2.- EQUIPOS DE CÓMPUTO E IMPRESORA --}}
                <div class="monitoreo-section bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 transition-all hover:shadow-md">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-5">
                        <div class="section-number bg-gradient-to-r from-blue-600 to-indigo-600 text-white w-9 h-9 flex items-center justify-center rounded-xl font-black text-sm shadow-md shadow-indigo-100">
                            2
                        </div>
                        <div>
                            <h3 class="text-slate-900 font-black text-base sm:text-lg uppercase tracking-tight">
                                EQUIPOS DE CÓMPUTO E IMPRESORA
                            </h3>
                            <p class="text-xs text-slate-400 font-semibold">Registro de hardware, cantidad, estado operativo y número de serie</p>
                        </div>
                    </div>

                    <x-tabla-equipos :prefix="$slug" :modulo="$slug" :equipos="$equipos ?? []" />
                </div>

                {{-- 3.- TIPO DE CONECTIVIDAD --}}
                @php
                    $hasComputo = false;
                    if (isset($equipos) && count($equipos) > 0) {
                        foreach ($equipos as $eq) {
                            $descUpper = str_replace('-', ' ', strtoupper(trim($eq->descripcion ?? '')));
                            if (
                                str_contains($descUpper, 'CPU') ||
                                str_contains($descUpper, 'LAPTOP') ||
                                str_contains($descUpper, 'COMPUTADORA') ||
                                str_contains($descUpper, 'COMPUTADOR') ||
                                str_contains($descUpper, 'ALL IN ONE') ||
                                str_contains($descUpper, 'AIO') ||
                                str_contains($descUpper, 'PC')
                            ) {
                                $hasComputo = true;
                                break;
                            }
                        }
                    }
                @endphp
                <div id="container_tipo_conectividad" class="{{ !$hasComputo ? 'hidden' : '' }}">
                    <x-tipo-conectividad num="3" :contenido="$contenido" />
                </div>

                {{-- 4.- OBSERVACIONES Y EVIDENCIAS --}}
                <div class="monitoreo-section bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 transition-all hover:shadow-md">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-5">
                        <div class="section-number bg-gradient-to-r from-blue-600 to-indigo-600 text-white w-9 h-9 flex items-center justify-center rounded-xl font-black text-sm shadow-md shadow-indigo-100">
                            4
                        </div>
                        <div>
                            <h3 class="text-slate-900 font-black text-base sm:text-lg uppercase tracking-tight">
                                OBSERVACIONES Y EVIDENCIAS FOTOGRÁFICAS
                            </h3>
                            <p class="text-xs text-slate-400 font-semibold">Anotaciones de incidencias y registro visual del consultorio</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-slate-700 text-xs font-black uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <i data-lucide="file-text" class="w-4 h-4 text-slate-400"></i> Observaciones Generales
                            </label>
                            <textarea name="contenido[observaciones]" rows="3" 
                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl p-4 text-slate-800 font-bold uppercase text-xs outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 transition-all" 
                                placeholder="INGRESE LAS OBSERVACIONES O INCIDENCIAS DETECTADAS EN ESTE CONSULTORIO...">{{ $contenido['observaciones'] ?? '' }}</textarea>
                        </div>

                        <div>
                            <label class="block text-slate-700 text-xs font-black uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <i data-lucide="camera" class="w-4 h-4 text-slate-400"></i> Fotografía / Evidencia Adjunta (Opcional)
                            </label>
                            
                            <div class="border-2 border-dashed border-slate-300 hover:border-indigo-400 rounded-2xl p-6 text-center bg-slate-50/50 hover:bg-indigo-50/20 transition-all cursor-pointer relative">
                                <input type="file" name="evidencia" id="input_evidencia_foto" accept="image/*" onchange="previewEvidenciaImage(this)"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                                
                                <div class="flex flex-col items-center justify-center gap-2 pointer-events-none">
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                        <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-xs font-black text-slate-700 uppercase">Haga clic o arrastre aquí una imagen para adjuntar</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Formatos compatibles: JPG, PNG, WEBP</p>
                                </div>
                            </div>
                            
                            {{-- CONTENEDOR DE PREVISUALIZACIÓN DE IMAGEN --}}
                            @php
                                $evidenciaPath = $detalle->contenido['evidencia_path'] ?? $contenido['evidencia_path'] ?? '';
                            @endphp
                            <input type="hidden" name="eliminar_evidencia" id="input_eliminar_evidencia" value="0">
                            <div id="container_preview_evidencia" class="mt-4 {{ empty($evidenciaPath) ? 'hidden' : '' }}">
                                <div class="relative inline-block bg-slate-100 p-3 rounded-2xl border-2 border-indigo-200 shadow-md group">
                                    <img id="img_preview_evidencia" 
                                         src="{{ !empty($evidenciaPath) ? asset('storage/' . $evidenciaPath) : '' }}" 
                                         alt="Previsualización Evidencia" 
                                         class="max-h-64 max-w-full rounded-xl object-contain shadow-inner bg-white">
                                    <button type="button" onclick="eliminarEvidenciaActual()"
                                        class="absolute top-5 right-5 px-3 py-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-lg transition-all hover:scale-105 active:scale-95 flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider" title="Quitar fotografía">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Quitar</span>
                                    </button>
                                    <div class="mt-2.5 text-center text-[10px] font-black text-indigo-700 uppercase tracking-wider flex items-center justify-center gap-1.5">
                                        <i data-lucide="camera" class="w-3.5 h-3.5 text-indigo-500"></i>
                                        <span id="text_preview_evidencia_name">EVIDENCIA: {{ $tituloConsultorio }} (ACTA #{{ str_pad($acta->numero_acta ?? $acta->id, 5, '0', STR_PAD_LEFT) }})</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BOTÓN GUARDAR MODERNO --}}
                <div class="flex items-center justify-end gap-4 pt-4">
                    <button type="submit" id="btn-submit-action" class="inline-flex items-center gap-3 px-10 py-4 bg-gradient-to-r from-indigo-600 via-blue-600 to-indigo-700 hover:from-indigo-700 hover:to-blue-800 text-white font-black rounded-2xl transition-all shadow-lg shadow-indigo-200 hover:shadow-indigo-300 text-xs uppercase tracking-widest active:scale-95">
                        <span id="icon-save-loader">
                            <i data-lucide="save" class="w-5 h-5"></i>
                        </span>
                        <span>Guardar Evaluación</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewEvidenciaImage(input) {
            const container = document.getElementById('container_preview_evidencia');
            const img = document.getElementById('img_preview_evidencia');
            const txtName = document.getElementById('text_preview_evidencia_name');
            const inputEliminar = document.getElementById('input_eliminar_evidencia');

            if (input.files && input.files[0]) {
                if (inputEliminar) inputEliminar.value = '0';
                const reader = new FileReader();
                reader.onload = function (e) {
                    img.src = e.target.result;
                    if (container) container.classList.remove('hidden');
                    if (txtName) txtName.innerText = "NUEVA: " + input.files[0].name.toUpperCase();
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function eliminarEvidenciaActual() {
            const inputEliminar = document.getElementById('input_eliminar_evidencia');
            const container = document.getElementById('container_preview_evidencia');
            const inputFoto = document.getElementById('input_evidencia_foto');
            if (inputEliminar) inputEliminar.value = '1';
            if (inputFoto) inputFoto.value = '';
            if (container) container.classList.add('hidden');
        }

        function toggleSihceAndDocs(val) {
            const dj = document.getElementById('div_firmo_dj');
            const conf = document.getElementById('div_firmo_confidencialidad');

            if (val === 'SI') {
                if (dj) dj.classList.remove('hidden');
                if (conf) conf.classList.remove('hidden');
            } else {
                if (dj) dj.classList.add('hidden');
                if (conf) conf.classList.add('hidden');
            }
            updateSectionNumbers();
        }

        function checkComputoEquipos() {
            const containerConectividad = document.getElementById('container_tipo_conectividad');
            if (!containerConectividad) return;

            const descInputs = document.querySelectorAll('input[name*="[descripcion]"]');
            let hasComputo = false;

            descInputs.forEach(input => {
                const rawVal = input.value.trim().toUpperCase();
                const val = rawVal.replace(/-/g, ' ');
                if (
                    val.includes('CPU') ||
                    val.includes('LAPTOP') ||
                    val.includes('COMPUTADORA') ||
                    val.includes('COMPUTADOR') ||
                    val.includes('ALL IN ONE') ||
                    val.includes('AIO') ||
                    val.includes('PC')
                ) {
                    hasComputo = true;
                }
            });

            if (hasComputo) {
                containerConectividad.classList.remove('hidden');
            } else {
                containerConectividad.classList.add('hidden');
            }

            updateSectionNumbers();
        }

        function updateSectionNumbers() {
            let index = 1;
            document.querySelectorAll('.monitoreo-section').forEach(section => {
                if (!section.classList.contains('hidden') && !section.closest('.hidden')) {
                    const numberSpan = section.querySelector('.section-number');
                    if (numberSpan) {
                        numberSpan.textContent = index;
                        index++;
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const selectSihce = document.getElementById('cuenta_sihce');
            if (selectSihce) toggleSihceAndDocs(selectSihce.value);

            checkComputoEquipos();

            const bodyEquipos = document.querySelector('tbody[id^="body_equipos_"]');
            if (bodyEquipos) {
                bodyEquipos.addEventListener('input', function (e) {
                    if (e.target && e.target.name && e.target.name.includes('[descripcion]')) {
                        checkComputoEquipos();
                    }
                });

                const observer = new MutationObserver(function () {
                    checkComputoEquipos();
                });
                observer.observe(bodyEquipos, { childList: true, subtree: true });
            }

            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        document.getElementById('form-monitoreo-final').onsubmit = function (e) {
            let faltantes = [];

            // 1. DATOS GENERALES
            const fechaInput = document.querySelector('input[name="contenido[fecha]"]');
            if (!fechaInput || !fechaInput.value.trim()) {
                faltantes.push("DATOS GENERALES: Fecha de Monitoreo");
            }

            const turnoInput = document.querySelector('input[name="contenido[turno]"]:checked');
            if (!turnoInput) {
                faltantes.push("DATOS GENERALES: Turno (Mañana / Tarde)");
            }

            const tipoConsultorio = document.querySelector('select[name="contenido[tipo_consultorio]"]');
            if (!tipoConsultorio || !tipoConsultorio.value.trim()) {
                faltantes.push("DATOS GENERALES: Tipo de Consultorio");
            }

            const pisoInput = document.querySelector('input[name="contenido[piso]"]');
            if (!pisoInput || !pisoInput.value.trim() || parseInt(pisoInput.value) < 1) {
                faltantes.push("DATOS GENERALES: Número de Piso");
            }

            const puntoRedRadio = document.querySelector('input[name="contenido[cuenta_punto_red]"]:checked');
            if (puntoRedRadio && puntoRedRadio.value === 'SI') {
                const cantPuntos = document.getElementById('input_cantidad_puntos_red');
                if (!cantPuntos || !cantPuntos.value.trim() || parseInt(cantPuntos.value) < 1) {
                    faltantes.push("DATOS GENERALES: Cantidad de Puntos de Red");
                }
            }

            // 2. EQUIPOS DE CÓMPUTO
            const filasEquipos = document.querySelectorAll('tbody[id^="body_equipos_"] tr:not([id^="no_data_"])');
            filasEquipos.forEach((row, i) => {
                const desc = row.querySelector('input[name*="[descripcion]"]');
                const cant = row.querySelector('input[name*="[cantidad]"]');
                const est = row.querySelector('select[name*="[estado]"]');
                const prop = row.querySelector('select[name*="[propio]"]');

                if (desc && !desc.value.trim()) {
                    faltantes.push(`EQUIPOS DE CÓMPUTO: Descripción en fila #${i + 1}`);
                }
                if (cant && (!cant.value || parseInt(cant.value) < 1)) {
                    faltantes.push(`EQUIPOS DE CÓMPUTO: Cantidad en fila #${i + 1}`);
                }
                if (est && !est.value.trim()) {
                    faltantes.push(`EQUIPOS DE CÓMPUTO: Estado en fila #${i + 1}`);
                }
                if (prop && !prop.value.trim()) {
                    faltantes.push(`EQUIPOS DE CÓMPUTO: Propiedad en fila #${i + 1}`);
                }
            });

            // 3. TIPO DE CONECTIVIDAD
            const tipoConectividad = document.getElementById('tipo_conectividad_input')?.value;
            if (!tipoConectividad || !tipoConectividad.trim()) {
                faltantes.push("TIPO DE CONECTIVIDAD: Seleccione opción (WIFI, CABLEADO o SIN CONECTIVIDAD)");
            } else {
                if (tipoConectividad === 'WIFI') {
                    const wifiFuente = document.getElementById('wifi_fuente_input')?.value;
                    if (!wifiFuente || !wifiFuente.trim()) {
                        faltantes.push("TIPO DE CONECTIVIDAD: Procedencia de WiFi (Establecimiento o Personal)");
                    }
                }

                if (tipoConectividad === 'WIFI' || tipoConectividad === 'CABLEADO') {
                    const operador = document.getElementById('operador_servicio_select')?.value;
                    if (!operador || !operador.trim()) {
                        faltantes.push("TIPO DE CONECTIVIDAD: Operador de Servicio de Internet");
                    }

                    const velDescarga = document.getElementById('velocidad_descarga_input')?.value;
                    if (!velDescarga || !velDescarga.trim()) {
                        faltantes.push("TIPO DE CONECTIVIDAD: Velocidad de Descarga");
                    }

                    const velSubida = document.getElementById('velocidad_subida_input')?.value;
                    if (!velSubida || !velSubida.trim()) {
                        faltantes.push("TIPO DE CONECTIVIDAD: Velocidad de Subida");
                    }
                }
            }

            if (faltantes.length > 0) {
                e.preventDefault();
                let htmlList = '<ul class="text-left text-xs space-y-1.5 mt-2 font-bold text-slate-700 bg-rose-50 p-4 rounded-2xl border border-rose-200 shadow-inner max-h-60 overflow-y-auto custom-scroll">';
                faltantes.forEach(item => {
                    htmlList += `<li class="flex items-center gap-2 text-rose-700"><span class="text-rose-500 font-black">•</span> ${item}</li>`;
                });
                htmlList += '</ul>';

                Swal.fire({
                    icon: 'error',
                    title: 'Formulario Incompleto',
                    html: `<p class="text-xs text-slate-500 font-semibold mb-2">Se requieren los siguientes datos para poder guardar la evaluación:</p>${htmlList}`,
                    confirmButtonText: 'Completar Campos Requeridos',
                    confirmButtonColor: '#4F46E5',
                    customClass: {
                        popup: 'rounded-[2.5rem] p-6'
                    }
                });

                return false;
            }

            // Aviso (no bloqueante) si el piso declarado es inusualmente alto: la
            // mayoría de establecimientos no supera los 4-5 pisos, y un número mal
            // tipeado aquí (ej. "17" en vez de "1") hace que el croquis genere de
            // golpe una decena de pisos vacíos y la sala termine ubicada en un piso
            // que nadie más revisa. No se bloquea porque sí existen establecimientos
            // con más pisos: solo se pide confirmar antes de continuar.
            const pisoVal = parseInt(pisoInput ? pisoInput.value : '1', 10);
            if (pisoVal > 5 && this.dataset.pisoConfirmado !== '1') {
                e.preventDefault();
                const form = this;
                Swal.fire({
                    icon: 'warning',
                    title: `¿Seguro que es el piso ${pisoVal}?`,
                    html: '<p class="text-xs text-slate-500 font-semibold">La mayoría de establecimientos no supera los 4 o 5 pisos. Si el número es correcto, puede continuar; si fue un error de tipeo, corríjalo.</p>',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, es correcto',
                    cancelButtonText: 'Corregir el número',
                    confirmButtonColor: '#4F46E5',
                    cancelButtonColor: '#94a3b8',
                    customClass: { popup: 'rounded-[2.5rem] p-6' }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.dataset.pisoConfirmado = '1';
                        if (form.requestSubmit) form.requestSubmit();
                        else form.submit();
                    } else if (pisoInput) {
                        pisoInput.focus();
                    }
                });
                return false;
            }

            const btn = document.getElementById('btn-submit-action');
            const icon = document.getElementById('icon-save-loader');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            if (icon) {
                icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
            }
            return true;
        };
    </script>
@endsection
