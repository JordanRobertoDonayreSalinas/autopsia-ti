@extends('layouts.usuario')

@section('title', 'Módulo 09: Inmunizaciones')

@section('content')
<div class="py-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        {{-- ENCABEZADO --}}
        <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="px-3 py-1 bg-indigo-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">Módulo Técnico</span>
                    <span class="text-slate-400 font-bold text-[10px] uppercase">ID Acta: #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight">09. Inmunizaciones</h2>
                <p class="text-slate-500 font-bold uppercase text-xs mt-1">
                    <i data-lucide="hospital" class="inline-block w-4 h-4 mr-1 text-indigo-500"></i> {{ $acta->establecimiento->nombre }}
                </p>
            </div>
            <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}" class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-black text-xs hover:bg-slate-50 transition-all uppercase shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al Panel
            </a>
        </div>

        {{-- FORMULARIO --}}
        <form action="{{ route('usuario.monitoreo.inmunizaciones.store', $acta->id) }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="space-y-8" 
              id="form-inmunizaciones">
            @csrf
            {{-- SECCIÓN 1: DETALLES DEL CONSULTORIO --}}
            <div class="seccion-numerada bg-white rounded-[3rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100">
                <div class="flex items-center gap-4 mb-8">
                    <div class="badge-numero h-12 w-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-inner">1</div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Detalles del Consultorio</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    
                    {{-- 1. FECHA DE MONITOREO --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Fecha de Monitoreo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="calendar" class="h-5 w-5 text-indigo-500"></i>
                            </div>
                            <input type="date" 
                                   name="contenido[fecha_monitoreo_inmunizaciones]" 
                                   value="{{ $detalle->contenido['fecha_monitoreo_inmunizaciones'] ?? date('Y-m-d') }}"
                                   class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-black text-slate-600 outline-none focus:border-indigo-500 transition-all uppercase cursor-pointer">
                        </div>
                    </div>

                    {{-- 2. CANTIDAD DE CONSULTORIOS (Movido aquí para llenar el espacio) --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Cantidad de Consultorios</label>
                        <input type="number" 
                               name="contenido[numero_consultorio]" 
                               min="1"
                               onkeydown="return event.keyCode !== 69 && event.keyCode !== 189"
                               oninput="this.value = Math.abs(this.value)"
                               value="{{ $detalle->contenido['numero_consultorio'] ?? 1 }}" 
                               class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold text-sm outline-none text-center" />
                    </div>

                    {{-- 3. DENOMINACIÓN (Ancho completo para nombres largos) --}}
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Denominación del Consultorio</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i data-lucide="tag" class="h-5 w-5 text-indigo-500"></i>
                            </div>
                            <input type="text" 
                                   name="contenido[denominacion_consultorio]" 
                                   value="{{ $detalle->contenido['denominacion_consultorio'] ?? '' }}" 
                                   class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold text-sm outline-none uppercase" 
                                   placeholder="EJ: INMUNIZACIONES - NIÑO" />
                        </div>
                    </div>

                    {{-- 4. HORARIOS DE ATENCIÓN --}}
                    <div class="md:col-span-2 bg-slate-50/50 p-6 rounded-3xl border border-slate-100">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">
                            Horario de Atención (Detallado)
                        </label>
                        
                        <div class="space-y-6">
                            @php
                                $diasSemana = ['LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB', 'DOM'];
                                $filas = [
                                    ['label' => 'HORARIO PRINCIPAL (Lun-Vie)', 'index' => 0],
                                    ['label' => 'HORARIO ADICIONAL (Sáb/Dom/Otros)', 'index' => 1]
                                ];
                            @endphp

                            @foreach($filas as $fila)
                                @php
                                    $i = $fila['index'];
                                    $diasSeleccionados = $detalle->contenido['horarios'][$i]['dias'] ?? [];
                                    $hInicio = $detalle->contenido['horarios'][$i]['inicio'] ?? '';
                                    $hFin = $detalle->contenido['horarios'][$i]['fin'] ?? '';
                                @endphp

                                <div class="bg-white p-4 rounded-2xl border border-slate-200 relative">
                                    <span class="absolute -top-2.5 left-4 bg-indigo-50 text-indigo-500 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider border border-indigo-100">
                                        {{ $fila['label'] }}
                                    </span>
                                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mt-2">
                                        {{-- Selector Días --}}
                                        <div class="flex flex-wrap gap-1.5 items-center">
                                            @foreach($diasSemana as $dia)
                                                <label class="cursor-pointer group">
                                                    <input type="checkbox" name="contenido[horarios][{{$i}}][dias][]" value="{{ $dia }}" class="peer sr-only" {{ in_array($dia, $diasSeleccionados) ? 'checked' : '' }}>
                                                    <div class="px-2.5 py-2 rounded-lg bg-slate-50 border border-slate-200 text-slate-400 text-[9px] font-black transition-all peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 peer-checked:shadow-sm group-hover:border-indigo-300">{{ $dia }}</div>
                                                </label>
                                            @endforeach
                                        </div>
                                        {{-- Selector Horas --}}
                                        <div class="flex items-center gap-2 border-l-2 border-slate-100 pl-0 xl:pl-4">
                                            <div class="relative w-full"><input type="time" name="contenido[horarios][{{$i}}][inicio]" value="{{ $hInicio }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-xs text-slate-600 outline-none focus:border-indigo-500 text-center"></div>
                                            <span class="text-slate-300 font-black">-</span>
                                            <div class="relative w-full"><input type="time" name="contenido[horarios][{{$i}}][fin]" value="{{ $hFin }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl font-bold text-xs text-slate-600 outline-none focus:border-indigo-500 text-center"></div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- 5. CONSULTORIO COMPARTIDO --}}
                    <div class="md:col-span-2 border-t-2 border-slate-50 pt-6 mt-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">¿Es un consultorio compartido?</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="flex gap-4">
                                <label class="flex-1 relative cursor-pointer group">
                                    <input type="radio" name="contenido[es_compartido]" value="SI" 
                                        {{ ($detalle->contenido['es_compartido'] ?? '') == 'SI' ? 'checked' : '' }} 
                                        onchange="toggleCompartido('SI')" class="peer sr-only">
                                    <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:shadow-sm hover:border-indigo-200">
                                        <span class="text-xs font-black uppercase tracking-wider">SÍ</span>
                                    </div>
                                </label>
                                <label class="flex-1 relative cursor-pointer group">
                                    <input type="radio" name="contenido[es_compartido]" value="NO" 
                                        {{ ($detalle->contenido['es_compartido'] ?? '') == 'NO' ? 'checked' : '' }} 
                                        onchange="toggleCompartido('NO')" class="peer sr-only">
                                    <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center transition-all peer-checked:border-slate-400 peer-checked:bg-slate-100 peer-checked:text-slate-600 peer-checked:shadow-sm hover:border-slate-300">
                                        <span class="text-xs font-black uppercase tracking-wider">NO</span>
                                    </div>
                                </label>
                            </div>
                            {{-- CAMPO SELECT (Antes era Input Text) --}}
                            <div id="div_con_quien" class="{{ ($detalle->contenido['es_compartido'] ?? '') == 'SI' ? '' : 'hidden' }} animate-fade-in-down">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i data-lucide="users" class="h-4 w-4 text-indigo-400"></i>
                                    </div>
                                    <select id="input_con_quien" name="contenido[con_quien_comparte]" 
                                            class="w-full pl-10 pr-6 py-4 bg-indigo-50/30 border-2 border-indigo-100 rounded-2xl font-bold text-sm outline-none focus:border-indigo-500 text-slate-600 uppercase transition-all cursor-pointer appearance-none">
                                        <option value="">-- SELECCIONE SERVICIO --</option>
                                        @foreach(['CRED', 'MEDICINA', 'OBSTETRICIA', 'NUTRICION', 'ODONTOLOGIA', 'PSICOLOGIA', 'OTROS'] as $opcion)
                                            <option value="{{ $opcion }}" {{ ($detalle->contenido['con_quien_comparte'] ?? '') == $opcion ? 'selected' : '' }}>
                                                {{ $opcion }}
                                            </option>
                                        @endforeach
                                    </select>
                                    {{-- Flechita personalizada para el select --}}
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <i data-lucide="chevron-down" class="h-4 w-4 text-indigo-400"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- SECCIÓN 2: PROFESIONAL --}}
            <div class="seccion-numerada bg-white rounded-[3rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100">
                <div class="flex items-center gap-4 mb-8">
                    <div class="badge-numero h-12 w-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-inner">2</div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Datos del Profesional</h3>
                </div>
                <x-busqueda-profesional prefix="profesional" :detalle="$detalle" />
                {{-- PREGUNTA: ¿UTILIZA SIHCE? --}}
                <div class="mt-8 mb-6 p-6 bg-slate-50 rounded-3xl border border-slate-100">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">¿El profesional utiliza SIHCE?</label>
                    <div class="flex gap-4">
                        {{-- SI --}}
                        <label class="flex-1 relative cursor-pointer group">
                            <input type="radio" name="contenido[utiliza_sihce]" value="SI" 
                                {{ ($detalle->contenido['utiliza_sihce'] ?? '') == 'SI' ? 'checked' : '' }} 
                                onchange="toggleSihce('SI')"
                                class="peer sr-only">
                            <div class="p-4 rounded-2xl border-2 border-slate-100 bg-white text-center transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-checked:shadow-sm hover:border-indigo-200">
                                <span class="text-xs font-black uppercase tracking-wider">SÍ</span>
                            </div>
                        </label>
                        
                        {{-- NO --}}
                        <label class="flex-1 relative cursor-pointer group">
                            <input type="radio" name="contenido[utiliza_sihce]" value="NO" 
                                {{ ($detalle->contenido['utiliza_sihce'] ?? '') == 'NO' ? 'checked' : '' }} 
                                onchange="toggleSihce('NO')"
                                class="peer sr-only">
                            <div class="p-4 rounded-2xl border-2 border-slate-100 bg-white text-center transition-all peer-checked:border-slate-400 peer-checked:bg-slate-100 peer-checked:text-slate-600 peer-checked:shadow-sm hover:border-slate-300">
                                <span class="text-xs font-black uppercase tracking-wider">NO</span>
                            </div>
                        </label>
                    </div>
                </div>
                {{-- NUEVA SECCIÓN: DOCUMENTACIÓN ADMINISTRATIVA --}}
                <div id="bloque_doc_administrativa" class="bg-white rounded-[3rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100 mb-8 {{ ($detalle->contenido['utiliza_sihce'] ?? '') == 'NO' ? 'hidden' : '' }} animate-fade-in-down">
                    <div class="flex items-center gap-4 mb-8">
                        {{-- Icono representativo --}}
                        <div class="h-10 w-10 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-inner">
                            <i data-lucide="file-signature" class="w-6 h-6"></i>
                        </div>
                        <h3 class="text-lg font-black text-slate-600 uppercase tracking-tight">Documentación Administrativa</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        {{-- PREGUNTA 1: DECLARACIÓN JURADA --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">¿Firmó Declaración Jurada?</label>
                            <div class="flex gap-4">
                                {{-- OPCIÓN SÍ --}}
                                <label class="flex-1 relative cursor-pointer group">
                                    <input type="radio" name="contenido[firmo_dj]" value="SI" 
                                        {{ ($detalle->contenido['firmo_dj'] ?? '') == 'SI' ? 'checked' : '' }} 
                                        class="peer sr-only">
                                    <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:shadow-sm hover:border-emerald-200">
                                        <span class="text-xs font-black uppercase tracking-wider">SÍ</span>
                                    </div>
                                </label>
                                
                                {{-- OPCIÓN NO --}}
                                <label class="flex-1 relative cursor-pointer group">
                                    <input type="radio" name="contenido[firmo_dj]" value="NO" 
                                        {{ ($detalle->contenido['firmo_dj'] ?? '') == 'NO' ? 'checked' : '' }} 
                                        class="peer sr-only">
                                    <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 peer-checked:shadow-sm hover:border-red-200">
                                        <span class="text-xs font-black uppercase tracking-wider">NO</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- PREGUNTA 2: COMPROMISO DE CONFIDENCIALIDAD --}}
                        <div>
                            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">¿Firmó Compromiso de Confidencialidad?</label>
                            <div class="flex gap-4">
                                {{-- OPCIÓN SÍ --}}
                                <label class="flex-1 relative cursor-pointer group">
                                    <input type="radio" name="contenido[firmo_confidencialidad]" value="SI" 
                                        {{ ($detalle->contenido['firmo_confidencialidad'] ?? '') == 'SI' ? 'checked' : '' }} 
                                        class="peer sr-only">
                                    <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 peer-checked:shadow-sm hover:border-emerald-200">
                                        <span class="text-xs font-black uppercase tracking-wider">SÍ</span>
                                    </div>
                                </label>
                                
                                {{-- OPCIÓN NO --}}
                                <label class="flex-1 relative cursor-pointer group">
                                    <input type="radio" name="contenido[firmo_confidencialidad]" value="NO" 
                                        {{ ($detalle->contenido['firmo_confidencialidad'] ?? '') == 'NO' ? 'checked' : '' }} 
                                        class="peer sr-only">
                                    <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 peer-checked:shadow-sm hover:border-red-200">
                                        <span class="text-xs font-black uppercase tracking-wider">NO</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- SECCIÓN 3: DATOS DEL DNI Y FIRMA DIGITAL --}}
            <div id="seccion_dni_firma" class="bg-white rounded-[3rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100 mb-8 {{ ($detalle->contenido['profesional']['tipo_doc'] ?? 'DNI') !== 'DNI' ? 'hidden' : '' }} seccion-numerada">
                <div class="flex items-center gap-4 mb-8">
                    <div class="badge-numero h-12 w-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-inner">3</div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">DETALLE DE DNI Y FIRMA DIGITAL</h3>
                </div>
                
                {{-- SELECCIÓN DEL TIPO DE DOCUMENTO --}}
                <div class="mb-6">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Seleccione el Tipo de Documento Físico</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- OPCIÓN A: DNI ELECTRÓNICO --}}
                        <label class="relative cursor-pointer group">
                            <input type="radio" 
                                   name="contenido[tipo_dni_fisico]" 
                                   value="ELECTRONICO" 
                                   class="peer sr-only"
                                   onchange="toggleDniFields()"
                                   {{ ($detalle->contenido['tipo_dni_fisico'] ?? '') == 'ELECTRONICO' ? 'checked' : '' }}>
                            <div class="p-5 rounded-2xl border-2 border-slate-100 bg-slate-50 peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 transition-all hover:border-indigo-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-indigo-600 shadow-sm">
                                        <i data-lucide="credit-card" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-black text-slate-700 uppercase">DNI Electrónico</span>
                                        <span class="text-[9px] text-slate-400 font-bold uppercase">Con Chip</span>
                                    </div>
                                    <div class="ml-auto opacity-0 peer-checked:opacity-100 text-indigo-600">
                                        <i data-lucide="check-circle-2" class="w-6 h-6 fill-indigo-100"></i>
                                    </div>
                                </div>
                            </div>
                        </label>

                        {{-- OPCIÓN B: DNI AZUL / AMARILLO --}}
                        <label class="relative cursor-pointer group">
                            <input type="radio" 
                                   name="contenido[tipo_dni_fisico]" 
                                   value="AZUL" 
                                   class="peer sr-only"
                                   onchange="toggleDniFields()"
                                   {{ ($detalle->contenido['tipo_dni_fisico'] ?? '') == 'AZUL' ? 'checked' : '' }}>
                            <div class="p-5 rounded-2xl border-2 border-slate-100 bg-slate-50 peer-checked:border-blue-500 peer-checked:bg-blue-50/50 transition-all hover:border-blue-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-blue-500 shadow-sm">
                                        <i data-lucide="user-square" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-black text-slate-700 uppercase">DNI Azul</span>
                                        <span class="text-[9px] text-slate-400 font-bold uppercase">Sin Chip</span>
                                    </div>
                                    <div class="ml-auto opacity-0 peer-checked:opacity-100 text-blue-500">
                                        <i data-lucide="check-circle-2" class="w-6 h-6 fill-blue-100"></i>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- BLOQUE CONDICIONAL (SOLO SI ES DNIe) --}}
                <div id="block-info-dnie" class="hidden animate-fade-in-down mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-indigo-50/40 rounded-3xl border border-indigo-100">
                        {{-- Versión del DNIe --}}
                        <div>
                            <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3">Versión del DNIe</label>
                            <select name="contenido[dnie_version]" class="w-full px-5 py-3 bg-white border-2 border-indigo-100 rounded-xl font-bold text-xs outline-none focus:border-indigo-500 text-slate-600 uppercase transition-all">
                                <option value="">-- SELECCIONE --</option>
                                <option value="1.0" {{ ($detalle->contenido['dnie_version'] ?? '') == '1.0' ? 'selected' : '' }}>VERSIÓN 1.0</option>
                                <option value="2.0" {{ ($detalle->contenido['dnie_version'] ?? '') == '2.0' ? 'selected' : '' }}>VERSIÓN 2.0</option>
                                <option value="3.0" {{ ($detalle->contenido['dnie_version'] ?? '') == '3.0' ? 'selected' : '' }}>VERSIÓN 3.0</option>
                            </select>
                        </div>

                        {{-- Realiza Firma SIHCE --}}
                        <div>
                            <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3">¿Firma Digitalmente en SIHCE?</label>
                            <div class="flex gap-4 mt-1">
                                <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-xl border border-indigo-100 shadow-sm hover:border-indigo-400 transition-colors">
                                    <input type="radio" name="contenido[dnie_firma_sihce]" value="SI" {{ ($detalle->contenido['dnie_firma_sihce'] ?? '') == 'SI' ? 'checked' : '' }} class="w-4 h-4 accent-indigo-600">
                                    <span class="text-xs font-bold text-slate-700">SÍ</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer bg-white px-4 py-2 rounded-xl border border-indigo-100 shadow-sm hover:border-red-400 transition-colors">
                                    <input type="radio" name="contenido[dnie_firma_sihce]" value="NO" {{ ($detalle->contenido['dnie_firma_sihce'] ?? '') == 'NO' ? 'checked' : '' }} class="w-4 h-4 accent-red-500">
                                    <span class="text-xs font-bold text-slate-700">NO</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{--BLOQUE FIJO (SIEMPRE VISIBLE) --}}
                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Observaciones / Motivo de uso</label>
                    <textarea name="contenido[dni_observacion]" 
                              rows="2"
                              class="w-full px-5 py-4 bg-white border-2 border-slate-200 rounded-2xl font-bold text-xs outline-none focus:border-indigo-400 focus:ring-4 focus:ring-indigo-50/50 text-slate-600 uppercase placeholder:text-slate-300 transition-all"
                              placeholder="INGRESE AQUÍ CUALQUIER OBSERVACIÓN ...">{{ $detalle->contenido['dni_observacion'] ?? '' }}</textarea>
                </div>
            </div>

            {{-- SECCIÓN 4: DETALLES DE CAPACITACIÓN --}}
            <div id="seccion_capacitacion" class="seccion-numerada bg-white rounded-[3rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100 {{ ($detalle->contenido['utiliza_sihce'] ?? '') == 'NO' ? 'hidden' : '' }}">
                <div class="flex items-center gap-4 mb-8">
                    <div class="badge-numero h-12 w-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-inner">4</div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Detalles de Capacitación</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-10">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">¿Recibió capacitación?</label>
                        <div class="flex gap-8">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="contenido[recibio_capacitacion]" value="SI" {{ ($detalle->contenido['recibio_capacitacion'] ?? '') == 'SI' ? 'checked' : '' }} class="w-5 h-5" onchange="toggleInstCapacitacion(this.value)">
                                <span class="text-sm font-bold">SÍ</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="contenido[recibio_capacitacion]" value="NO" {{ ($detalle->contenido['recibio_capacitacion'] ?? '') == 'NO' ? 'checked' : '' }} class="w-5 h-5" onchange="toggleInstCapacitacion(this.value)">
                                <span class="text-sm font-bold">NO</span>
                            </label>
                        </div>
                    </div>
                    <div id="section_inst_capacitacion" class="{{ ($detalle->contenido['recibio_capacitacion'] ?? '') === 'NO' ? 'hidden' : '' }}">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">¿De parte de quién?</label>
                        <select name="contenido[inst_capacitacion]" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold text-sm outline-none focus:border-indigo-500 transition-all">
                            @foreach(['MINSA','DIRESA','UNIDAD EJECUTORA','PERSONAL DEL EESS','OTROS'] as $op)
                                <option value="{{$op}}" {{ ($detalle->contenido['inst_capacitacion'] ?? '') == $op ? 'selected' : '' }}>{{$op}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 5: EQUIPAMIENTO DEL ÁREA --}}
            <div class="seccion-numerada bg-white rounded-[3rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100">
                <div class="flex items-center gap-4 mb-8">
                    <div class="badge-numero h-12 w-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-inner">5</div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">EQUIPAMIENTO DEL CONSULTORIO</h3>
                </div>
                <x-tabla-equipos :equipos="$equipos" modulo="inmunizaciones" />
            </div>

            {{-- TIPO DE CONECTIVIDAD --}}
            <x-tipo-conectividad :num="7" :contenido="$detalle->contenido ?? []" color="indigo" />

            {{-- SECCIÓN 6: UTILIZACIÓN DE REPORTES DEL SISTEMA --}}
            <div id="seccion_reportes" class="seccion-numerada bg-white rounded-[3rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100 {{ ($detalle->contenido['utiliza_sihce'] ?? '') == 'NO' ? 'hidden' : '' }}">
                <div class="flex items-center gap-4 mb-8">
                    <div class="badge-numero h-12 w-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-inner">6</div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Utilización de Reportes del Sistema</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">¿Utiliza reportes del Sistema?</label>
                        <div class="flex gap-8">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="contenido[utiliza_reportes]" value="SI" {{ ($detalle->contenido['utiliza_reportes'] ?? '') == 'SI' ? 'checked' : '' }} class="w-5 h-5" onchange="toggleSocializaReportes(this.value)">
                                <span class="text-sm font-bold">SÍ</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="radio" name="contenido[utiliza_reportes]" value="NO" {{ ($detalle->contenido['utiliza_reportes'] ?? '') == 'NO' ? 'checked' : '' }} class="w-5 h-5" onchange="toggleSocializaReportes(this.value)">
                                <span class="text-sm font-bold">NO</span>
                            </label>
                        </div>
                    </div>
                    <div id="section_socializa_reportes" class="{{ ($detalle->contenido['utiliza_reportes'] ?? '') === 'NO' ? 'hidden' : '' }}">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Si es "SÍ" con quién lo socializa</label>
                        <select name="contenido[socializa_reportes]" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold text-sm outline-none">
                            @foreach(['PERSONAL DEL SERVICIO','JEFE DE ESTABLECIMIENTO','ESTADISTICO','UNIDAD EJECUTORA','DIRESA','OTROS'] as $op)
                                <option value="{{$op}}" {{ ($detalle->contenido['socializa_reportes'] ?? '---') == $op ? 'selected' : '' }}>{{$op}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 7: SOPORTE TÉCNICO --}}
            <div id="seccion_soporte" class="seccion-numerada bg-white rounded-[3rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100 {{ ($detalle->contenido['utiliza_sihce'] ?? '') == 'NO' ? 'hidden' : '' }}">
                <div class="flex items-center gap-4 mb-8">
                    <div class="badge-numero h-12 w-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-inner">7</div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Soporte</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">¿A quién le comunica?</label>
                        <select name="contenido[comunica_a]" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold text-sm outline-none">
                            @foreach(['MINSA','DIRESA','JEFE DE ESTABLECIMIENTO','PERSONAL DEL EESS','OTROS'] as $op)
                                <option value="{{$op}}" {{ ($detalle->contenido['comunica_a'] ?? '---') == $op ? 'selected' : '' }}>{{$op}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">¿Qué medio utiliza?</label>
                        <div class="flex gap-8 mt-3">
                            @foreach(['CELULAR' => 'celular', 'CORREO' => 'correo', 'WHATSAPP' => 'whatsapp', 'OTROS' => 'otros'] as $label => $key)
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" name="contenido[medio_soporte]" value="{{$label}}" {{ ($detalle->contenido['medio_soporte'] ?? '') == $label ? 'checked' : '' }} class="w-5 h-5">
                                    <span class="text-sm font-bold">{{$label}}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 8: COMENTARIOS Y FOTOS --}}
            <div class="bg-slate-900 rounded-[3.5rem] p-12 shadow-2xl text-white relative overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 relative z-10">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-[0.3em] text-indigo-400 mb-6 flex items-center gap-2">
                            <i data-lucide="message-square" class="w-5 h-5"></i> Comentarios
                        </h3>
                        <textarea name="contenido[comentarios]" rows="5" class="w-full bg-white/5 border-2 border-white/10 rounded-3xl p-6 text-white font-bold outline-none focus:border-indigo-500 transition-all uppercase placeholder-white/20 shadow-inner" placeholder="OBSERVACIONES...">{{ $detalle->contenido['comentarios'] ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        @php
                            $fotosActuales = [];
                            if (isset($detalle->contenido['foto_evidencia'])) {
                                $val = $detalle->contenido['foto_evidencia'];
                                $fotosActuales = is_array($val) ? $val : [$val];
                            }
                            $fotoPortada = $fotosActuales[0] ?? null;
                            $fotosExtra  = array_slice($fotosActuales, 1);
                        @endphp
                        <h3 class="text-sm font-black uppercase tracking-[0.3em] text-red-400 mb-6 flex items-center gap-2">
                            <i data-lucide="camera" class="w-5 h-5"></i> Evidencia Fotográfica
                        </h3>

                        {{-- FOTO PRINCIPAL GUARDADA (grande, estilo gestion_administrativa) --}}
                        @if($fotoPortada)
                            <div id="saved-images-block" class="mb-4 w-full flex justify-center bg-black/50 rounded-2xl p-2 border-2 border-white/10">
                                <a href="{{ asset('storage/' . $fotoPortada) }}" target="_blank"
                                    class="block relative group/img overflow-hidden rounded-xl h-96 w-full">
                                    <div class="absolute inset-0 bg-black/0 group-hover/img:bg-black/20 transition-all z-10 flex items-center justify-center opacity-0 group-hover/img:opacity-100">
                                        <i data-lucide="zoom-in" class="text-white w-10 h-10 drop-shadow-lg scale-75 group-hover/img:scale-100 transition-all duration-300"></i>
                                    </div>
                                    <img src="{{ asset('storage/' . $fotoPortada) }}"
                                        class="w-full h-full object-cover shadow-2xl rounded-xl">
                                </a>
                            </div>
                            {{-- Fotos adicionales en grilla --}}
                            @if(count($fotosExtra) > 0)
                                <div class="grid grid-cols-3 gap-2 mb-4">
                                    @foreach($fotosExtra as $foto)
                                        <a href="{{ asset('storage/'.$foto) }}" target="_blank"
                                           class="block aspect-video rounded-xl overflow-hidden border border-white/10 hover:opacity-80 transition">
                                            <img src="{{ asset('storage/'.$foto) }}" class="w-full h-full object-cover">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        {{-- DROPZONE PARA SUBIR NUEVAS FOTOS --}}
                        <div class="relative group">
                            <input type="file" 
                            name="foto_evidencia[]" 
                            id="foto_evidencia"
                            accept="image/*" multiple 
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" 
                            onchange="simplePreview(event)">
                            <div id="dropzone" class="bg-white/5 border-2 border-dashed border-white/20 rounded-[2rem] p-8 flex flex-col items-center justify-center group-hover:bg-white/10 transition-all shadow-inner min-h-48">
                                <i data-lucide="upload-cloud" id="upload-icon" class="w-8 h-8 text-indigo-400 mb-2"></i>
                                <span id="placeholder-content" class="text-[10px] font-black uppercase tracking-widest text-slate-300 text-center">
                                    {{ $fotoPortada ? 'CLICK PARA CAMBIAR' : 'SUBIR FOTO(S)' }}
                                </span>
                                {{-- Preview de nuevas fotos --}}
                                <img id="img-preview-single" src="#"
                                    class="hidden mt-4 h-64 w-full object-cover rounded-lg border-2 border-indigo-500 shadow-xl">
                                <div id="new-previews" class="hidden mt-4 grid grid-cols-3 gap-3 w-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BOTÓN DE GUARDADO FINAL --}}
            <div class="pt-10 pb-20">
                <button type="submit" id="btn-submit-action" 
                        class="w-full group bg-indigo-600 text-white p-10 rounded-[3rem] font-black shadow-2xl shadow-indigo-200 flex items-center justify-between hover:bg-indigo-700 transition-all duration-500 active:scale-[0.98] cursor-pointer">
                    <div class="flex items-center gap-8 pointer-events-none">
                        <div class="h-16 w-16 bg-white/20 rounded-3xl flex items-center justify-center group-hover:rotate-12 transition-all shadow-lg border border-white/30">
                            <i data-lucide="save" id="icon-save-loader" class="w-8 h-8 text-white"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-xl uppercase tracking-[0.3em] leading-none">Confirmar Registro</p>
                            <p class="text-[10px] text-indigo-200 font-bold uppercase mt-3 tracking-widest">Sincronizar Módulo 09 con el Maestro</p>
                        </div>
                    </div>
                    <div class="h-14 w-14 bg-white/10 rounded-full flex items-center justify-center group-hover:bg-white group-hover:text-indigo-600 transition-all duration-500">
                        <i data-lucide="chevron-right" class="w-7 h-7"></i>
                    </div>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // ============================================================
        // 1. INICIALIZACIONES BÁSICAS
        // ============================================================
        toggleDniFields(true); // Lógica interna del DNIe

        // Inyectar clases al componente x-tipo-conectividad sin modificarlo
        const compConectividad = document.querySelector('.monitoreo-section');
        if (compConectividad) {
            compConectividad.classList.add('seccion-numerada');
            const spanNum = compConectividad.querySelector('.section-number');
            if (spanNum) spanNum.classList.add('badge-numero');
        }

        // Verificar estado inicial de consultorio compartido
        const compartidoState = document.querySelector('input[name="contenido[es_compartido]"]:checked')?.value;
        const divCompartido = document.getElementById('div_con_quien');
        
        if (compartidoState === 'SI') {
            divCompartido.classList.remove('hidden');
        } else {
            divCompartido.classList.add('hidden');
        }

        // Verificar estado inicial de SIHCE
        const estadoSihce = document.querySelector('input[name="contenido[utiliza_sihce]"]:checked')?.value;
        if (estadoSihce === 'NO') toggleSihce('NO');


        // ============================================================
        // 2. LÓGICA ROBUSTA PARA DETECTAR CAMBIO DE TIPO DE DOC
        // ============================================================
        
        // Buscamos el campo por ID o por atributo name (cubrimos ambas posibilidades)
        const inputTipoDoc = document.getElementById('profesional_tipo_doc') || 
                             document.querySelector('[name="contenido[profesional][tipo_doc]"]') ||
                             document.querySelector('select[name*="tipo_doc"]');
                             
        const sectionDni = document.getElementById('seccion_dni_firma');

        function checkTipoDoc() {
            if (!inputTipoDoc || !sectionDni) return;

            // Obtenemos el valor actual en mayúsculas para evitar errores
            const valor = inputTipoDoc.value.toUpperCase().trim();

            // REGLA: Solo mostramos si es "DNI". Ocultamos para C.E., PASAPORTE, etc.
            if (valor === 'DNI') {
                sectionDni.classList.remove('hidden');
            } else {
                sectionDni.classList.add('hidden');
            }
            // Recalcular números tras mostrar/ocultar
            actualizarCorrelativo();
        }

        if (inputTipoDoc) {
            // A. Ejecutar inmediatamente al cargar
            checkTipoDoc();

            // B. Escuchar cambios manuales (si el usuario cambia el select)
            inputTipoDoc.addEventListener('change', checkTipoDoc);

            // C. Escuchar cambios automáticos (Intervalo de seguridad)
            // Revisamos cada 1 segundo si el valor cambió (ideal para cuando "Validar Doc" rellena los datos)
            setInterval(checkTipoDoc, 1000); 
            
        } else {
            console.warn("⚠️ ALERTA: No se encontró el campo 'Tipo de Documento'. Revise si el ID es 'profesional_tipo_doc'.");
        }
        // Recalcular números tras mostrar/ocultar
        actualizarCorrelativo();
    });

    // Muestra/Oculta Doc. Administrativa, Capacitación y Soporte según SIHCE
    function toggleSihce(valor) {
        const bloqueDoc = document.getElementById('bloque_doc_administrativa');
        const bloqueSoporte = document.getElementById('seccion_soporte');
        const bloqueCapacitacion = document.getElementById('seccion_capacitacion');
        const bloqueReportes = document.getElementById('seccion_reportes');
        
        if (valor === 'SI') {
            if(bloqueDoc) bloqueDoc.classList.remove('hidden');
            if(bloqueReportes) bloqueReportes.classList.remove('hidden');
            if(bloqueCapacitacion) {
                bloqueCapacitacion.classList.remove('hidden');
                // [NUEVO] Si el select aparece vacío, forzamos que seleccione "---"
                const selectCapacitacion = bloqueCapacitacion.querySelector('select[name="contenido[inst_capacitacion]"]');
                if (selectCapacitacion && !selectCapacitacion.value) {
                    selectCapacitacion.value = 'MINSA';
                }
            }
            if(bloqueSoporte) {
                bloqueSoporte.classList.remove('hidden');
                // [NUEVO] Si el select aparece vacío, forzamos que seleccione "---"
                const selectSoporte = bloqueSoporte.querySelector('select[name="contenido[comunica_a]"]');
                if (selectSoporte && !selectSoporte.value) {
                    selectSoporte.value = 'MINSA';
                }
            }
        } else {
            if(bloqueDoc) {
                bloqueDoc.classList.add('hidden');
                // Limpiar radios internos para que se guarden como NULL
                bloqueDoc.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
            }
            if(bloqueReportes) {
                bloqueReportes.classList.add('hidden');
                // Limpiar inputs dentro de reportes
                bloqueReportes.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
                bloqueReportes.querySelectorAll('input[type="text"]').forEach(t => t.value = '');
                // Ocultar sub-sección condicional interna de reportes
                const subBloqueRep = document.getElementById('section_socializa_reportes');
                if(subBloqueRep) subBloqueRep.classList.add('hidden');
            }
            if(bloqueCapacitacion) {
                bloqueCapacitacion.classList.add('hidden');
                // Limpiamos radios y selects internos
                bloqueCapacitacion.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
                bloqueCapacitacion.querySelectorAll('select').forEach(s => s.value = '');
                // Opcional: Ocultar también el sub-bloque de institución por si acaso
                const subBloqueInst = document.getElementById('section_inst_capacitacion');
                if(subBloqueInst) subBloqueInst.classList.add('hidden'); 
            }
            if(bloqueSoporte) {
                bloqueSoporte.classList.add('hidden');
                // Limpiar selects/radios internos de soporte
                bloqueSoporte.querySelectorAll('input, select').forEach(el => {
                    if(el.type === 'radio' || el.type === 'checkbox') el.checked = false;
                    else el.value = '';
                });
            }
        }
        // Recalcular números tras mostrar/ocultar
        actualizarCorrelativo();
    }

    function toggleDniFields(isInitialLoad = false) {
        // Obtenemos el valor seleccionado (puede ser "DNI ELECTRONICO" o "DNI AZUL")
        const tipoDni = document.querySelector('input[name="contenido[tipo_dni_fisico]"]:checked')?.value;
        
        // Referencias a los bloques
        const blockDnie = document.getElementById('block-info-dnie');
        
        // Referencias a los inputs para limpiarlos
        const selectVersion = document.querySelector('select[name="contenido[dnie_version]"]');
        const radiosFirma = document.querySelectorAll('input[name="contenido[dnie_firma_sihce]"]');
        const txtObservacion = document.querySelector('textarea[name="contenido[dni_observacion]"]');

        if (tipoDni === 'ELECTRONICO') {
            // Mostrar campos de DNIe
            blockDnie.classList.remove('hidden');
            
            // Limpiar observación de DNI Azul (si no es carga inicial)
            if (!isInitialLoad && txtObservacion) {
                txtObservacion.value = '';
            }

        } else if (tipoDni === 'AZUL') {
            // Ocultar campos de DNIe
            blockDnie.classList.add('hidden');
            
            // Limpiar datos de DNIe (si no es carga inicial)
            if (!isInitialLoad) {
                if (selectVersion) selectVersion.value = ''; // Reset select
                radiosFirma.forEach(r => r.checked = false); // Reset radios
            }
        } else {
            // Ninguno seleccionado
            blockDnie.classList.add('hidden');
        }
    }
    
    function toggleCompartido(valor) {
        const divConQuien = document.getElementById('div_con_quien');
        const inputConQuien = document.getElementById('input_con_quien');

        if (valor === 'SI') {
            divConQuien.classList.remove('hidden');
            // Opcional: poner foco en el input
            setTimeout(() => inputConQuien.focus(), 100);
        } else {
            divConQuien.classList.add('hidden');
            inputConQuien.value = ''; // Limpiamos el campo para que no se guarde
        }
    }

    function toggleInstCapacitacion(value) {
        const section = document.getElementById('section_inst_capacitacion');
        value === 'NO' ? section.classList.add('hidden') : section.classList.remove('hidden');
    }

    function toggleSocializaReportes(value) {
        const section = document.getElementById('section_socializa_reportes');
        value === 'NO' ? section.classList.add('hidden') : section.classList.remove('hidden');
    }

    function simplePreview(event) {
        const input = event.target;
        const previewContainer = document.getElementById('new-previews');
        const previewSingle    = document.getElementById('img-preview-single');
        const uploadIcon       = document.getElementById('upload-icon');
        const placeholder      = document.getElementById('placeholder-content');
        const savedBlock       = document.getElementById('saved-images-block');
        
        // Limpiamos previsualizaciones anteriores
        previewContainer.innerHTML = '';
        previewSingle.classList.add('hidden');
        previewContainer.classList.add('hidden');
        
        // VALIDACIÓN
        if (input.files && input.files.length > 0) {
            for (let i = 0; i < input.files.length; i++) {
                if (!input.files[i].type.startsWith('image/')) {
                    alert(`⚠️ ERROR DE FORMATO:\n\nEl archivo "${input.files[i].name}" NO es una imagen.\nSolo se permiten archivos JPG, PNG o JPEG.`);
                    input.value = "";
                    if(savedBlock) savedBlock.style.display = '';
                    return;
                }
            }

            // Ocultar fotos antiguas
            if(savedBlock) savedBlock.style.display = 'none';
            placeholder.classList.add('hidden');
            if(uploadIcon) uploadIcon.classList.add('hidden');

            const files = Array.from(input.files).slice(0, 5);

            if (files.length === 1) {
                // Una sola foto: mostrar grande
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewSingle.src = e.target.result;
                    previewSingle.classList.remove('hidden');
                };
                reader.readAsDataURL(files[0]);
            } else {
                // Varias fotos: mostrar en grilla
                previewContainer.classList.remove('hidden');
                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'w-full h-96 object-cover rounded-xl border border-indigo-500 shadow-sm animate-fade-in';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }

        } else {
            // Si el usuario cancela la selección (input vacío)
            previewContainer.classList.add('hidden');
            placeholder.classList.remove('hidden');
            if(savedBlock) savedBlock.style.display = 'block';
        }
    }
    
    // FUNCIÓN MAESTRA PARA RENUMERAR SECCIONES
    function actualizarCorrelativo() {
        // 1. Buscamos todas las secciones que marcamos en el HTML
        const secciones = document.querySelectorAll('.seccion-numerada');
        let contador = 1;

        secciones.forEach(seccion => {
            // 2. Si la sección NO está oculta, le asignamos el número actual
            if (!seccion.classList.contains('hidden')) {
                const badge = seccion.querySelector('.badge-numero');
                if (badge) {
                    badge.textContent = contador; // Ponemos el número (1, 2, 3...)
                    contador++; // Aumentamos para la siguiente
                }
            }
        });
    }

    document.getElementById('form-inmunizaciones').onsubmit = function() {
        const btn = document.getElementById('btn-submit-action');
        const icon = document.getElementById('icon-save-loader');
        
        btn.disabled = true;
        btn.classList.add('opacity-50', 'cursor-not-allowed');
        
        icon.innerHTML = '<i data-lucide="loader-2" class="w-8 h-8 text-white animate-spin"></i>';
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
        return true;
    };
</script>
@endsection
