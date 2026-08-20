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

                    {{-- DENOMINACIÓN, SERVICIO Y DEPARTAMENTO --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
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

                        <div class="bg-slate-50/60 p-5 rounded-2xl border border-slate-200/80">
                            <label class="block text-slate-700 text-[11px] font-black uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-500"></i> Departamento Asociado al Consultorio
                            </label>
                            <input type="text" name="contenido[departamento_asociado]"
                                value="{{ $contenido['departamento_asociado'] ?? '' }}"
                                placeholder="INGRESE EL DEPARTAMENTO DEL CONSULTORIO..."
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

                                {{-- TOMA ESTABILIZADA (ROJA-NARANJA) --}}
                                <div class="mt-4 pt-4 border-t border-slate-200/70">
                                    <label class="block text-[10px] font-black text-orange-800 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-br from-red-500 to-orange-500 flex-shrink-0"></span>
                                        ¿Tiene toma estabilizada (roja-naranja)?
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        @php $tomaEstabilizada = strtoupper($contenido['tiene_toma_estabilizada'] ?? 'NO'); @endphp
                                        <label class="relative flex items-center justify-center p-2.5 rounded-xl border-2 cursor-pointer transition-all {{ $tomaEstabilizada === 'SI' ? 'border-orange-500 bg-orange-50 text-orange-800 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                            <input type="radio" name="contenido[tiene_toma_estabilizada]" value="SI" {{ $tomaEstabilizada === 'SI' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-orange-500', 'bg-orange-50', 'text-orange-800', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-orange-500', 'bg-orange-50', 'text-orange-800', 'shadow-sm'); document.getElementById('container_toma_estabilizada').classList.remove('hidden');">
                                            <span class="font-black text-xs uppercase">SÍ, TIENE</span>
                                        </label>
                                        <label class="relative flex items-center justify-center p-2.5 rounded-xl border-2 cursor-pointer transition-all {{ $tomaEstabilizada === 'NO' ? 'border-slate-400 bg-slate-100 text-slate-700 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                            <input type="radio" name="contenido[tiene_toma_estabilizada]" value="NO" {{ $tomaEstabilizada === 'NO' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-orange-500', 'bg-orange-50', 'text-orange-800', 'border-slate-400', 'bg-slate-100', 'text-slate-700', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-slate-400', 'bg-slate-100', 'text-slate-700', 'shadow-sm'); document.getElementById('container_toma_estabilizada').classList.add('hidden');">
                                            <span class="font-black text-xs uppercase">NO TIENE</span>
                                        </label>
                                    </div>

                                    @php
                                        $tomaEstInternas = $contenido['toma_estabilizada_internas'] ?? 1;
                                        $tomaEstExternas = $contenido['toma_estabilizada_externas'] ?? 0;
                                    @endphp
                                    <div id="container_toma_estabilizada" class="mt-3 grid grid-cols-2 gap-3 {{ $tomaEstabilizada === 'SI' ? '' : 'hidden' }}">
                                        <div>
                                            <label class="block text-[10px] font-black text-orange-800 uppercase tracking-wider mb-1.5">Internas</label>
                                            <input type="number" min="0" max="99" step="1" name="contenido[toma_estabilizada_internas]"
                                                value="{{ max(0, (int)$tomaEstInternas) }}"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                class="w-full px-4 py-2.5 bg-white border-2 border-orange-200 focus:border-orange-600 rounded-xl font-black text-xs text-orange-900 outline-none transition-all shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-orange-800 uppercase tracking-wider mb-1.5">Externas</label>
                                            <input type="number" min="0" max="99" step="1" name="contenido[toma_estabilizada_externas]"
                                                value="{{ max(0, (int)$tomaEstExternas) }}"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                class="w-full px-4 py-2.5 bg-white border-2 border-orange-200 focus:border-orange-600 rounded-xl font-black text-xs text-orange-900 outline-none transition-all shadow-sm">
                                        </div>
                                    </div>
                                </div>

                                {{-- TOMA COMERCIAL (BLANCO) --}}
                                <div class="mt-4 pt-4 border-t border-slate-200/70">
                                    <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-white border-2 border-slate-400 flex-shrink-0"></span>
                                        ¿Tiene toma comercial (blanco)?
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        @php $tomaComercial = strtoupper($contenido['tiene_toma_comercial'] ?? 'NO'); @endphp
                                        <label class="relative flex items-center justify-center p-2.5 rounded-xl border-2 cursor-pointer transition-all {{ $tomaComercial === 'SI' ? 'border-zinc-500 bg-zinc-100 text-zinc-800 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                            <input type="radio" name="contenido[tiene_toma_comercial]" value="SI" {{ $tomaComercial === 'SI' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-zinc-500', 'bg-zinc-100', 'text-zinc-800', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-zinc-500', 'bg-zinc-100', 'text-zinc-800', 'shadow-sm'); document.getElementById('container_toma_comercial').classList.remove('hidden');">
                                            <span class="font-black text-xs uppercase">SÍ, TIENE</span>
                                        </label>
                                        <label class="relative flex items-center justify-center p-2.5 rounded-xl border-2 cursor-pointer transition-all {{ $tomaComercial === 'NO' ? 'border-slate-400 bg-slate-100 text-slate-700 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                            <input type="radio" name="contenido[tiene_toma_comercial]" value="NO" {{ $tomaComercial === 'NO' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-zinc-500', 'bg-zinc-100', 'text-zinc-800', 'border-slate-400', 'bg-slate-100', 'text-slate-700', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-slate-400', 'bg-slate-100', 'text-slate-700', 'shadow-sm'); document.getElementById('container_toma_comercial').classList.add('hidden');">
                                            <span class="font-black text-xs uppercase">NO TIENE</span>
                                        </label>
                                    </div>

                                    @php
                                        $tomaComInternas = $contenido['toma_comercial_internas'] ?? 1;
                                        $tomaComExternas = $contenido['toma_comercial_externas'] ?? 0;
                                    @endphp
                                    <div id="container_toma_comercial" class="mt-3 grid grid-cols-2 gap-3 {{ $tomaComercial === 'SI' ? '' : 'hidden' }}">
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1.5">Internas</label>
                                            <input type="number" min="0" max="99" step="1" name="contenido[toma_comercial_internas]"
                                                value="{{ max(0, (int)$tomaComInternas) }}"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                class="w-full px-4 py-2.5 bg-white border-2 border-slate-300 focus:border-slate-600 rounded-xl font-black text-xs text-slate-800 outline-none transition-all shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-slate-700 uppercase tracking-wider mb-1.5">Externas</label>
                                            <input type="number" min="0" max="99" step="1" name="contenido[toma_comercial_externas]"
                                                value="{{ max(0, (int)$tomaComExternas) }}"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                class="w-full px-4 py-2.5 bg-white border-2 border-slate-300 focus:border-slate-600 rounded-xl font-black text-xs text-slate-800 outline-none transition-all shadow-sm">
                                        </div>
                                    </div>
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
                                        <input type="radio" name="contenido[cuenta_punto_red]" value="SI" {{ $puntoRed === 'SI' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'shadow-sm'); document.getElementById('container_cantidad_puntos_red').classList.remove('hidden');">
                                        <span class="font-black text-xs uppercase flex items-center gap-1.5"><i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i> SÍ (HABILITADO)</span>
                                    </label>
                                    <label class="relative flex items-center justify-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $puntoRed === 'NO' ? 'border-rose-500 bg-rose-50 text-rose-800 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                        <input type="radio" name="contenido[cuenta_punto_red]" value="NO" {{ $puntoRed === 'NO' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-emerald-500', 'bg-emerald-50', 'text-emerald-800', 'border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-rose-500', 'bg-rose-50', 'text-rose-800', 'shadow-sm'); document.getElementById('container_cantidad_puntos_red').classList.add('hidden');">
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

                                {{-- REQUERIMIENTO: ¿NECESITA IMPLEMENTAR MÁS PUNTOS DE RED? --}}
                                <div class="mt-4 pt-4 border-t border-slate-200/70">
                                    <label class="block text-[10px] font-black text-amber-800 uppercase tracking-wider mb-2 flex items-center gap-1">
                                        <i data-lucide="plus-circle" class="w-3.5 h-3.5 text-amber-600"></i> ¿Necesita implementar más puntos de red?
                                    </label>
                                    <div class="grid grid-cols-2 gap-3">
                                        @php $reqMasPuntosRed = strtoupper($contenido['requiere_mas_puntos_red'] ?? 'NO'); @endphp
                                        <label class="relative flex items-center justify-center p-2.5 rounded-xl border-2 cursor-pointer transition-all {{ $reqMasPuntosRed === 'SI' ? 'border-amber-500 bg-amber-50 text-amber-800 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                            <input type="radio" name="contenido[requiere_mas_puntos_red]" value="SI" {{ $reqMasPuntosRed === 'SI' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-amber-500', 'bg-amber-50', 'text-amber-800', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-amber-500', 'bg-amber-50', 'text-amber-800', 'shadow-sm'); document.getElementById('container_requerimiento_punto_red').classList.remove('hidden');">
                                            <span class="font-black text-xs uppercase">SÍ, REQUIERE</span>
                                        </label>
                                        <label class="relative flex items-center justify-center p-2.5 rounded-xl border-2 cursor-pointer transition-all {{ $reqMasPuntosRed === 'NO' ? 'border-slate-400 bg-slate-100 text-slate-700 shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50' }}">
                                            <input type="radio" name="contenido[requiere_mas_puntos_red]" value="NO" {{ $reqMasPuntosRed === 'NO' ? 'checked' : '' }} class="sr-only" onchange="this.closest('.grid').querySelectorAll('label').forEach(l => { l.classList.remove('border-amber-500', 'bg-amber-50', 'text-amber-800', 'border-slate-400', 'bg-slate-100', 'text-slate-700', 'shadow-sm'); l.classList.add('border-slate-200', 'bg-white', 'text-slate-600'); }); this.parentElement.classList.remove('border-slate-200', 'bg-white', 'text-slate-600'); this.parentElement.classList.add('border-slate-400', 'bg-slate-100', 'text-slate-700', 'shadow-sm'); document.getElementById('container_requerimiento_punto_red').classList.add('hidden');">
                                            <span class="font-black text-xs uppercase">NO REQUIERE</span>
                                        </label>
                                    </div>

                                    @php $cantPuntosRedReq = $contenido['cantidad_puntos_red_requerido'] ?? 1; @endphp
                                    <div id="container_requerimiento_punto_red" class="mt-3 space-y-3 {{ $reqMasPuntosRed === 'SI' ? '' : 'hidden' }}">
                                        <div>
                                            <label class="block text-[10px] font-black text-amber-800 uppercase tracking-wider mb-1.5">
                                                Cantidad Adicional Requerida
                                            </label>
                                            <input type="number" min="1" max="99" step="1" name="contenido[cantidad_puntos_red_requerido]"
                                                value="{{ max(1, (int)$cantPuntosRedReq) }}"
                                                placeholder="Ej: 2"
                                                onkeydown="if(event.key==='-'||event.key==='+'||event.key==='e'||event.key==='E'||event.key==='.') event.preventDefault();"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, ''); if(this.value && parseInt(this.value) < 1) this.value = '1';"
                                                onblur="if(!this.value || parseInt(this.value) < 1) this.value = '1';"
                                                class="w-full px-4 py-2.5 bg-white border-2 border-amber-200 focus:border-amber-600 rounded-xl font-black text-xs text-amber-900 outline-none transition-all shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-black text-amber-800 uppercase tracking-wider mb-1.5">
                                                Observación del Requerimiento
                                            </label>
                                            <textarea name="contenido[observacion_requerimiento_punto_red]" rows="2" placeholder="Ej: se necesita para instalar una laptop adicional en el area de admision..."
                                                class="w-full px-4 py-2.5 bg-white border-2 border-amber-200 focus:border-amber-600 rounded-xl font-semibold text-xs text-slate-700 outline-none transition-all shadow-sm resize-none">{{ $contenido['observacion_requerimiento_punto_red'] ?? '' }}</textarea>
                                        </div>
                                    </div>
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

                {{-- REQUERIMIENTO DE EQUIPOS (manual, equipos que aun no tiene el consultorio) --}}
                <div class="monitoreo-section bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-200/80 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between gap-3 mb-6 border-b border-slate-100 pb-5">
                        <div class="flex items-center gap-3">
                            <div class="section-number bg-gradient-to-r from-blue-600 to-indigo-600 text-white w-9 h-9 flex items-center justify-center rounded-xl font-black text-sm shadow-md shadow-indigo-100">
                                0
                            </div>
                            <div>
                                <h3 class="text-slate-900 font-black text-base sm:text-lg uppercase tracking-tight">
                                    REQUERIMIENTO DE EQUIPOS
                                </h3>
                                <p class="text-xs text-slate-400 font-semibold">Equipos que el consultorio necesita y todavía no tiene</p>
                            </div>
                        </div>
                        <button type="button" onclick="addRequerimientoRow()"
                                class="group flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg active:scale-95">
                            <i data-lucide="plus-circle" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300"></i>
                            Añadir Requerimiento
                        </button>
                    </div>

                    <div class="overflow-x-auto custom-scroll">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 bg-slate-50/30">
                                    <th class="px-6 py-3 text-left">Tipo de Equipo</th>
                                    <th class="px-2 py-3 text-center">Cant.</th>
                                    <th class="px-4 py-3 text-left">Observación</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody id="body_requerimientos">
                                @forelse (($requerimientos ?? []) as $index => $req)
                                    <tr class="group/row hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-none">
                                        <td class="px-6 py-4">
                                            <input type="text" name="requerimientos[{{ $index }}][descripcion]" value="{{ $req->descripcion }}" class="input-table-text" required list="list_equipos_master" placeholder="Seleccione...">
                                        </td>
                                        <td class="px-2 py-4 text-center">
                                            <input type="number" name="requerimientos[{{ $index }}][cantidad]" value="{{ $req->cantidad ?? 1 }}" class="input-table-text text-center font-bold" min="1">
                                        </td>
                                        <td class="px-4 py-4">
                                            <input type="text" name="requerimientos[{{ $index }}][observacion]" value="{{ $req->observacion }}" class="input-table-text" placeholder="Motivo del requerimiento...">
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-500 transition-all opacity-0 group-hover/row:opacity-100">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="no_data_requerimientos">
                                        <td colspan="4" class="px-6 py-8 text-center text-xs font-bold text-slate-400 uppercase">
                                            Sin requerimientos registrados
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- 3.- TIPO DE CONECTIVIDAD (siempre visible: un consultorio puede no
                     tener equipo de computo y aun asi necesitar registrar si cuenta
                     o no con conectividad en el ambiente, ej. "SIN CONECTIVIDAD") --}}
                <div id="container_tipo_conectividad">
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
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-slate-700 text-xs font-black uppercase tracking-wider flex items-center gap-1.5">
                                    <i data-lucide="camera" class="w-4 h-4 text-slate-400"></i> Fotografías / Evidencia Adjunta (Máximo 10, Opcional)
                                </label>
                                <button type="button" id="btn_add_evidencia" onclick="addEvidenciaRow()"
                                        class="group flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-md active:scale-95">
                                    <i data-lucide="plus-circle" class="w-3.5 h-3.5 group-hover:rotate-90 transition-transform duration-300"></i>
                                    Añadir Fotografía
                                </button>
                            </div>

                            @php
                                // Formato nuevo: contenido['evidencias'] = [['path' => ..., 'descripcion' => ...], ...]
                                // Si no existe todavia, se migra desde el formato viejo (3 casillas fijas,
                                // o 1 sola foto antes de eso) para no perder evidencia ya cargada.
                                $evidencias = [];
                                if (!empty($detalle->contenido['evidencias']) && is_array($detalle->contenido['evidencias'])) {
                                    $evidencias = $detalle->contenido['evidencias'];
                                } else {
                                    for ($i = 1; $i <= 3; $i++) {
                                        $pOld = $detalle->contenido['evidencia_path_' . $i]
                                            ?? ($i === 1 ? ($detalle->contenido['evidencia_path'] ?? null) : null);
                                        if (!empty($pOld)) {
                                            $evidencias[] = ['path' => $pOld, 'descripcion' => ''];
                                        }
                                    }
                                }
                            @endphp

                            <div id="container_evidencias" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" data-count="{{ count($evidencias) }}">
                                @foreach ($evidencias as $idx => $ev)
                                    <div class="evidencia-card bg-slate-50 rounded-2xl border-2 border-indigo-200 p-3 shadow-sm" data-idx="{{ $idx }}">
                                        <input type="hidden" name="evidencias[{{ $idx }}][path_existente]" value="{{ $ev['path'] }}">
                                        <div class="relative group">
                                            <img id="img_preview_evidencia_{{ $idx }}"
                                                 src="{{ asset('storage/' . $ev['path']) }}"
                                                 alt="Evidencia {{ $idx + 1 }}"
                                                 class="h-40 w-full rounded-xl object-cover shadow-inner bg-white">
                                            <input type="file" name="evidencias[{{ $idx }}][foto]" accept="image/*" onchange="previewEvidenciaImage({{ $idx }}, this)"
                                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" title="Reemplazar fotografía">
                                            <button type="button" onclick="removeEvidenciaRow({{ $idx }})"
                                                class="absolute top-2 right-2 p-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-lg transition-all hover:scale-105 active:scale-95 z-30" title="Quitar fotografía">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                        <input type="text" name="evidencias[{{ $idx }}][descripcion]" value="{{ $ev['descripcion'] ?? '' }}"
                                               placeholder="Descripción de la foto..."
                                               class="w-full mt-2 px-3 py-2 bg-white border-2 border-indigo-200 focus:border-indigo-600 rounded-xl font-bold text-[11px] text-slate-700 outline-none transition-all">
                                    </div>
                                @endforeach
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
        const MAX_EVIDENCIAS = 10;
        let evidenciaCounter = parseInt(document.getElementById('container_evidencias')?.dataset.count || '0', 10);

        function updateBtnAddEvidenciaState() {
            const btn = document.getElementById('btn_add_evidencia');
            const total = document.querySelectorAll('#container_evidencias .evidencia-card').length;
            if (btn) btn.disabled = total >= MAX_EVIDENCIAS;
            if (btn) btn.classList.toggle('opacity-40', total >= MAX_EVIDENCIAS);
            if (btn) btn.classList.toggle('cursor-not-allowed', total >= MAX_EVIDENCIAS);
        }

        function addEvidenciaRow() {
            const total = document.querySelectorAll('#container_evidencias .evidencia-card').length;
            if (total >= MAX_EVIDENCIAS) return;

            const idx = evidenciaCounter++;
            const container = document.getElementById('container_evidencias');
            const card = document.createElement('div');
            card.className = 'evidencia-card bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300 p-3 shadow-sm';
            card.dataset.idx = idx;
            card.innerHTML = `
                <div class="relative">
                    <div id="dropzone_evidencia_${idx}" class="h-40 rounded-xl bg-white border border-slate-200 flex flex-col items-center justify-center gap-1.5 text-center">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                            <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                        </div>
                        <p class="text-[10px] font-black text-slate-700 uppercase">Toque para subir</p>
                        <p class="text-[9px] font-bold text-slate-400 uppercase">JPG, PNG, WEBP</p>
                    </div>
                    <img id="img_preview_evidencia_${idx}" class="h-40 w-full rounded-xl object-cover shadow-inner bg-white hidden" alt="Evidencia ${idx + 1}">
                    <input type="file" name="evidencias[${idx}][foto]" accept="image/*" onchange="previewEvidenciaImage(${idx}, this)"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                    <button type="button" onclick="removeEvidenciaRow(${idx})"
                        class="absolute top-2 right-2 p-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-lg transition-all hover:scale-105 active:scale-95 z-30 hidden" id="btn_remove_evidencia_${idx}" title="Quitar fotografía">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <input type="text" name="evidencias[${idx}][descripcion]"
                       placeholder="Descripción de la foto..."
                       class="w-full mt-2 px-3 py-2 bg-white border-2 border-indigo-200 focus:border-indigo-600 rounded-xl font-bold text-[11px] text-slate-700 outline-none transition-all">
            `;
            container.appendChild(card);
            if (typeof lucide !== 'undefined') lucide.createIcons();
            updateBtnAddEvidenciaState();
        }

        function previewEvidenciaImage(idx, input) {
            const dropzone = document.getElementById('dropzone_evidencia_' + idx);
            const img = document.getElementById('img_preview_evidencia_' + idx);
            const btnRemove = document.getElementById('btn_remove_evidencia_' + idx);

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (img) {
                        img.src = e.target.result;
                        img.classList.remove('hidden');
                    }
                    if (dropzone) dropzone.classList.add('hidden');
                    if (btnRemove) btnRemove.classList.remove('hidden');
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function removeEvidenciaRow(idx) {
            const card = document.querySelector(`.evidencia-card[data-idx="${idx}"]`);
            if (card) card.remove();
            updateBtnAddEvidenciaState();
        }

        document.addEventListener('DOMContentLoaded', updateBtnAddEvidenciaState);

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

            // 2.5. FOTOGRAFÍAS DE EVIDENCIA: cada casilla presente necesita foto
            // (existente o recien elegida) y descripción, sino no tiene sentido
            document.querySelectorAll('#container_evidencias .evidencia-card').forEach((card, i) => {
                const tienePathExistente = !!card.querySelector('input[name*="[path_existente]"]')?.value;
                const inputFoto = card.querySelector('input[type="file"][name*="[foto]"]');
                const tieneFotoNueva = inputFoto && inputFoto.files && inputFoto.files.length > 0;
                const desc = card.querySelector('input[name*="[descripcion]"]');

                if (!tienePathExistente && !tieneFotoNueva) {
                    faltantes.push(`FOTOGRAFÍAS: Falta subir la imagen en la foto #${i + 1}`);
                }
                if (desc && !desc.value.trim()) {
                    faltantes.push(`FOTOGRAFÍAS: Falta la descripción de la foto #${i + 1}`);
                }
            });

            // 3. TIPO DE CONECTIVIDAD (siempre obligatorio: la sección ya no
            // depende de si hay equipo de cómputo cargado — un consultorio sin
            // computadora igual necesita registrar "SIN CONECTIVIDAD" si aplica)
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

        function addRequerimientoRow() {
            const body = document.getElementById('body_requerimientos');
            const noDataRow = document.getElementById('no_data_requerimientos');
            if (noDataRow) noDataRow.remove();

            const uniqueId = Date.now();
            const row = document.createElement('tr');
            row.className = 'group/row hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-none';
            row.innerHTML = `
                <td class="px-6 py-4">
                    <input type="text" name="requerimientos[${uniqueId}][descripcion]" class="input-table-text" required list="list_equipos_master" placeholder="Seleccione...">
                </td>
                <td class="px-2 py-4 text-center">
                    <input type="number" name="requerimientos[${uniqueId}][cantidad]" value="1" class="input-table-text text-center font-bold" min="1">
                </td>
                <td class="px-4 py-4">
                    <input type="text" name="requerimientos[${uniqueId}][observacion]" class="input-table-text" placeholder="Motivo del requerimiento...">
                </td>
                <td class="px-4 py-4 text-center">
                    <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-500 transition-all opacity-0 group-hover/row:opacity-100">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </td>
            `;
            body.appendChild(row);
            if (window.refreshLucide) window.refreshLucide();
        }
    </script>
@endsection
