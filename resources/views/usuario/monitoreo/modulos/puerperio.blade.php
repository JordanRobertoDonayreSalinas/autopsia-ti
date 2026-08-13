@extends('layouts.usuario')

@section('title', 'Módulo 13: Puerperio')

@section('content')
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ENCABEZADO SUPERIOR --}}
            <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span
                            class="px-3 py-1 bg-rose-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">Módulo
                            Materno</span>
                        <span class="text-slate-400 font-bold text-[10px] uppercase">ID Acta:
                            #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight">13. Puerperio</h2>
                    <p class="text-slate-500 font-bold uppercase text-xs mt-1">
                        <i data-lucide="baby" class="inline-block w-4 h-4 mr-1 text-rose-500"></i>
                        {{ $acta->establecimiento->nombre }}
                    </p>
                </div>
                <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}"
                    class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-black text-xs hover:bg-slate-50 transition-all uppercase shadow-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al Panel
                </a>
            </div>

            <form action="{{ route('usuario.monitoreo.puerperio.store', $acta->id) }}" method="POST"
                enctype="multipart/form-data" class="space-y-6" id="form-monitoreo-puerperio">
                @csrf

                {{-- DEFINICIÓN DE VARIABLE SEGURA PARA CONTENIDO --}}
                @php
                    $contenido = $detalle->contenido ?? [];
                @endphp

                {{-- 1.- DETALLES DEL CONSULTORIO --}}
                <div class="monitoreo-section bg-white rounded-[2rem] p-8 shadow-lg border border-slate-100">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <span
                            class="section-number bg-rose-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">1</span>
                        <h3 class="text-rose-900 font-black text-lg uppercase tracking-tight">DETALLES DEL CONSULTORIO</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                        {{-- FECHA --}}
                        <div>
                            <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Fecha
                                de Monitoreo</label>
                            <input type="date" name="contenido[fecha]" value="{{ $contenido['fecha'] ?? date('Y-m-d') }}"
                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none focus:border-rose-500 transition-all">
                        </div>

                        {{-- TURNO --}}
                        <div>
                            <x-turno :selected="$contenido['turno'] ?? ''" />
                        </div>

                        {{-- NRO DE AMBIENTES --}}
                        <div>
                            <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Nro.
                                de Consultorios</label>
                            <input type="number" name="contenido[num_ambientes]"
                                value="{{ $contenido['num_ambientes'] ?? '' }}" min="0"
                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none focus:border-rose-500 transition-all text-center"
                                placeholder="EJ: 1">
                        </div>

                        {{-- DENOMINACIÓN --}}
                        <div>
                            <label
                                class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Consultorio
                                entrevistado</label>
                            <input type="text" name="contenido[denominacion_ambiente]"
                                value="{{ $contenido['denominacion_ambiente'] ?? '' }}"
                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none focus:border-rose-500 transition-all uppercase"
                                placeholder="EJ: OBSTETRICIA 01">
                        </div>
                    </div>
                </div>

                {{-- 2.- DATOS DEL PROFESIONAL --}}
                <div class="monitoreo-section bg-white rounded-[2rem] p-8 shadow-lg border border-slate-100">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <span
                            class="section-number bg-rose-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">2</span>
                        <h3 class="text-rose-900 font-black text-lg uppercase tracking-tight">DATOS DEL PROFESIONAL</h3>
                    </div>

                    {{-- BUSQUEDA DE PROFESIONAL --}}
                    <div class="mb-6">
                        <x-busqueda-profesional prefix="rrhh" :detalle="$detalle" />
                    </div>

                    {{-- SIHCE / DDJJ / CONFIDENCIALIDAD --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-100">
                        <div>
                            <label
                                class="block text-rose-600 text-[10px] font-black uppercase tracking-widest mb-2">¿Utiliza
                                SIHCE?</label>
                            <select name="contenido[cuenta_sihce]" id="cuenta_sihce"
                                onchange="toggleSihceAndDocs(this.value)"
                                class="w-full px-4 py-3 bg-rose-50 border-2 border-rose-100 rounded-xl font-bold text-sm uppercase outline-none text-rose-700 cursor-pointer hover:bg-rose-100 transition-colors">
                                <option value="SI" {{ ($contenido['cuenta_sihce'] ?? '') == 'SI' ? 'selected' : '' }}>SI
                                </option>
                                <option value="NO" {{ ($contenido['cuenta_sihce'] ?? '') == 'NO' ? 'selected' : '' }}>NO
                                </option>
                            </select>
                        </div>

                        {{-- DIVS IDENTIFICADOS PARA OCULTAR --}}
                        <div id="div_firmo_dj">
                            <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">¿Firmó
                                Declaración Jurada?</label>
                            <select name="contenido[firmo_dj]" id="firmo_dj"
                                class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm uppercase outline-none focus:border-rose-500">
                                <option value="SI" {{ ($contenido['firmo_dj'] ?? '') == 'SI' ? 'selected' : '' }}>SI
                                </option>
                                <option value="NO" {{ ($contenido['firmo_dj'] ?? '') == 'NO' ? 'selected' : '' }}>NO
                                </option>
                            </select>
                        </div>

                        <div id="div_firmo_confidencialidad">
                            <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">¿Firmó
                                Compromiso Confidencialidad?</label>
                            <select name="contenido[firmo_confidencialidad]" id="firmo_confidencialidad"
                                class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm uppercase outline-none focus:border-rose-500">
                                <option value="SI" {{ ($contenido['firmo_confidencialidad'] ?? '') == 'SI' ? 'selected' : '' }}>SI</option>
                                <option value="NO" {{ ($contenido['firmo_confidencialidad'] ?? '') == 'NO' ? 'selected' : '' }}>NO</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 3.- DETALLE DE DNI Y FIRMA DIGITAL (ESTILO TARJETAS) --}}
                <div id="section_dni_detalle"
                    class="monitoreo-section bg-white rounded-[2rem] p-8 shadow-lg border border-slate-100 hidden">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <span
                            class="section-number bg-rose-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">3</span>
                        <h3 class="text-rose-900 font-black text-lg uppercase tracking-tight">DETALLE DE DNI Y FIRMA
                            DIGITAL</h3>
                    </div>

                    {{-- SELECCIÓN DE TIPO DE DOCUMENTO (TARJETAS) --}}
                    <div class="mb-8">
                        <label class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-4">SELECCIONE
                            EL TIPO DE DOCUMENTO FÍSICO</label>
                        <input type="hidden" name="contenido[tipo_dni]" id="tipo_dni_input"
                            value="{{ $contenido['tipo_dni'] ?? '' }}">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- TARJETA DNI ELECTRÓNICO --}}
                            <div id="card_electronico" onclick="selectDniType('ELECTRONICO')"
                                class="cursor-pointer border-2 rounded-2xl p-6 flex items-center gap-4 transition-all hover:shadow-md {{ ($contenido['tipo_dni'] ?? '') == 'ELECTRONICO' ? 'border-rose-600 bg-rose-50' : 'border-slate-200 bg-white' }}">
                                <div
                                    class="h-12 w-12 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600">
                                    <i data-lucide="credit-card" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-800 uppercase">DNI ELECTRÓNICO</h4>
                                    <span
                                        class="text-[10px] font-bold text-rose-500 bg-rose-100 px-2 py-0.5 rounded uppercase">Con
                                        Chip</span>
                                </div>
                            </div>

                            {{-- TARJETA DNI AZUL --}}
                            <div id="card_azul" onclick="selectDniType('AZUL')"
                                class="cursor-pointer border-2 rounded-2xl p-6 flex items-center gap-4 transition-all hover:shadow-md {{ ($contenido['tipo_dni'] ?? '') == 'AZUL' ? 'border-rose-600 bg-rose-50' : 'border-slate-200 bg-white' }}">
                                <div
                                    class="h-12 w-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                                    <i data-lucide="user-square" class="w-6 h-6"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-800 uppercase">DNI AZUL</h4>
                                    <span
                                        class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded uppercase">Sin
                                        Chip</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BLOQUE INFERIOR (Solo visible si selecciona uno) --}}
                    <div id="bloque_opciones_dni"
                        class="bg-slate-50 rounded-2xl p-6 border border-slate-200 {{ empty($contenido['tipo_dni']) ? 'hidden' : '' }}">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                            {{-- COLUMNA IZQ: VERSIÓN --}}
                            <div id="bloque_version_dnie"
                                class="{{ ($contenido['tipo_dni'] ?? '') == 'ELECTRONICO' ? '' : 'hidden' }}">
                                <label
                                    class="block text-rose-600 text-[10px] font-black uppercase tracking-widest mb-2">Versión
                                    del DNIe</label>
                                <select name="contenido[version_dnie]"
                                    class="w-full px-4 py-3 bg-white border-2 border-rose-100 rounded-xl font-bold text-sm uppercase outline-none focus:border-rose-500 text-rose-700">
                                    <option value="" selected disabled>-- SELECCIONE --</option>
                                    <option value="1.0" {{ ($contenido['version_dnie'] ?? '') == '1.0' ? 'selected' : '' }}>
                                        VERSIÓN 1.0</option>
                                    <option value="2.0" {{ ($contenido['version_dnie'] ?? '') == '2.0' ? 'selected' : '' }}>
                                        VERSIÓN 2.0</option>
                                    <option value="3.0" {{ ($contenido['version_dnie'] ?? '') == '3.0' ? 'selected' : '' }}>
                                        VERSIÓN 3.0</option>
                                </select>
                            </div>

                            {{-- COLUMNA DER: FIRMA DIGITAL --}}
                            <div id="bloque_firma_digital"
                                class="{{ ($contenido['tipo_dni'] ?? '') == 'ELECTRONICO' ? '' : 'hidden' }}">
                                <label
                                    class="block text-rose-600 text-[10px] font-black uppercase tracking-widest mb-3">¿Firma
                                    Digitalmente en SIHCE?</label>
                                <div class="flex items-center gap-6">
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <div class="relative flex items-center">
                                            <input type="radio" name="contenido[firma_digital_sihce]" value="SI"
                                                class="peer h-5 w-5 cursor-pointer appearance-none rounded-full border-2 border-slate-300 checked:border-rose-600 transition-all"
                                                {{ ($contenido['firma_digital_sihce'] ?? '') == 'SI' ? 'checked' : '' }}>
                                            <span
                                                class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-2.5 h-2.5 bg-rose-600 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></span>
                                        </div>
                                        <span
                                            class="text-sm font-bold text-slate-600 group-hover:text-rose-600 transition-colors">SÍ</span>
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <div class="relative flex items-center">
                                            <input type="radio" name="contenido[firma_digital_sihce]" value="NO"
                                                class="peer h-5 w-5 cursor-pointer appearance-none rounded-full border-2 border-slate-300 checked:border-red-500 transition-all"
                                                {{ ($contenido['firma_digital_sihce'] ?? '') == 'NO' ? 'checked' : '' }}>
                                            <span
                                                class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-2.5 h-2.5 bg-red-500 rounded-full opacity-0 peer-checked:opacity-100 transition-opacity"></span>
                                        </div>
                                        <span
                                            class="text-sm font-bold text-slate-600 group-hover:text-red-500 transition-colors">NO</span>
                                    </label>
                                </div>
                            </div>

                            {{-- OBSERVACIONES --}}
                            <div class="md:col-span-2 mt-2">
                                <label
                                    class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-2">Observaciones</label>
                                <textarea name="contenido[observaciones_dni]" rows="2"
                                    class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl font-bold text-sm uppercase outline-none focus:border-rose-500 placeholder-slate-300"
                                    placeholder="Escriba aquí si presenta dificultades...">{{ $contenido['observaciones_dni'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4.- DETALLES DE CAPACITACIÓN --}}
                <div id="section_capacitacion"
                    class="monitoreo-section bg-white rounded-[2rem] p-8 shadow-lg border border-slate-100">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <span
                            class="section-number bg-rose-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">4</span>
                        <h3 class="text-rose-900 font-black text-lg uppercase tracking-tight">DETALLES DE CAPACITACIÓN
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label
                                class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">¿Recibió
                                Capacitación?</label>
                            <select name="contenido[recibio_capacitacion]" id="recibio_capacitacion"
                                onchange="toggleEntidadCapacitadora(this.value)"
                                class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm outline-none focus:border-rose-500 transition-all uppercase cursor-pointer">
                                <option value="SI" {{ ($contenido['recibio_capacitacion'] ?? '') == 'SI' ? 'selected' : '' }}>
                                    SI</option>
                                <option value="NO" {{ ($contenido['recibio_capacitacion'] ?? '') == 'NO' ? 'selected' : '' }}>
                                    NO</option>
                            </select>
                        </div>
                        <div id="wrapper_entidad_capacitadora" class="hidden">
                            <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">¿De
                                parte de quién?</label>
                            <select name="contenido[inst_que_lo_capacito]"
                                class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm outline-none focus:border-rose-500 transition-all uppercase cursor-pointer">
                                <option value="UNIDAD EJECUTORA" {{ ($contenido['inst_que_lo_capacito'] ?? '') == 'UNIDAD EJECUTORA' ? 'selected' : '' }}>UNIDAD EJECUTORA</option>
                                <option value="DIRESA" {{ ($contenido['inst_que_lo_capacito'] ?? '') == 'DIRESA' ? 'selected' : '' }}>DIRESA</option>
                                <option value="MINSA" {{ ($contenido['inst_que_lo_capacito'] ?? '') == 'MINSA' ? 'selected' : '' }}>MINSA</option>
                                <option value="JEFE DE ESTABLECIMIENTO" {{ ($contenido['inst_que_lo_capacito'] ?? '') == 'JEFE DE ESTABLECIMIENTO' ? 'selected' : '' }}>JEFE DE ESTABLECIMIENTO</option>
                                <option value="PERSONAL DEL ESTABLECIMIENTO" {{ ($contenido['inst_que_lo_capacito'] ?? '') == 'PERSONAL DEL ESTABLECIMIENTO' ? 'selected' : '' }}>PERSONAL DEL ESTABLECIMIENTO</option>
                                <option value="OTROS" {{ ($contenido['inst_que_lo_capacito'] ?? '') == 'OTROS' ? 'selected' : '' }}>OTROS</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 5.- EQUIPAMIENTO DEL CONSULTORIO --}}
                <div class="monitoreo-section bg-white rounded-[2rem] p-8 shadow-lg border border-slate-100">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <span
                            class="section-number bg-rose-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">5</span>
                        <h3 class="text-rose-900 font-black text-lg uppercase tracking-tight">EQUIPAMIENTO DEL CONSULTORIO
                        </h3>
                    </div>
                    <x-tabla-equipos :equipos="$equipos" modulo="puerperio" />
                </div>

                {{-- 6.- TIPO DE CONECTIVIDAD --}}
                <x-tipo-conectividad :num="6" :contenido="$contenido" color="rose" />

                {{-- 7.- SOPORTE (Oculto si SIHCE es NO) --}}
                <div id="section_soporte"
                    class="monitoreo-section bg-white rounded-[2rem] p-8 shadow-lg border border-slate-100 {{ ($contenido['cuenta_sihce'] ?? '') == 'NO' ? 'hidden' : '' }}">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <span
                            class="section-number bg-rose-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">7</span>
                        <h3 class="text-rose-900 font-black text-lg uppercase tracking-tight">SOPORTE</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Ante
                                dificultades se comunica con:</label>
                            <select name="contenido[inst_a_quien_comunica]"
                                class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm outline-none uppercase cursor-pointer">
                                @foreach(['DIRESA', 'UNIDAD EJECUTORA', 'JEFE DE ESTABLECIMIENTO', 'MINSA', 'OTROS'] as $op)
                                    <option value="{{$op}}" {{ ($contenido['inst_a_quien_comunica'] ?? '') == $op ? 'selected' : '' }}>{{$op}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Medio
                                que utiliza:</label>
                            <select name="contenido[medio_que_utiliza]"
                                class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm outline-none uppercase cursor-pointer">
                                @foreach(['CELULAR', 'EMAIL', 'WHATSAPP', 'OTROS'] as $me)
                                    <option value="{{$me}}" {{ ($contenido['medio_que_utiliza'] ?? '') == $me ? 'selected' : '' }}>{{$me}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 8.- COMENTARIOS y 9.- EVIDENCIA --}}
                <div class="bg-slate-900 rounded-[3rem] p-10 shadow-2xl text-white">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        <div>
                            <div class="flex items-center gap-3 mb-6">
                                <span
                                    class="bg-rose-500 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">8</span>
                                <h3 class="text-white font-black text-lg uppercase tracking-tight">COMENTARIOS</h3>
                            </div>
                            <textarea name="contenido[comentarios]" rows="6"
                                class="w-full bg-white/10 border-2 border-white/20 rounded-2xl p-4 text-white font-bold outline-none focus:border-rose-500 transition-all uppercase placeholder-white/30">{{ $contenido['comentarios'] ?? '' }}</textarea>
                        </div>
                        <div>
                            <div class="flex items-center gap-3 mb-6">
                                <span
                                    class="bg-rose-500 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">9</span>
                                <h3 class="text-white font-black text-lg uppercase tracking-tight">EVIDENCIA FOTOGRÁFICA
                                </h3>
                            </div>

                            @php
                                $fotoEvidencia = $contenido['foto_evidencia'] ?? null;
                                // Si por error viene un array (de pruebas anteriores), tomamos el primero
                                if (is_array($fotoEvidencia)) {
                                    $fotoEvidencia = $fotoEvidencia[0] ?? null;
                                }
                            @endphp

                            @if($fotoEvidencia)
                                <div
                                    class="mb-4 w-full flex justify-center bg-black/50 rounded-2xl p-2 border-2 border-white/10">
                                    <a href="{{ asset('storage/' . $fotoEvidencia) }}" target="_blank"
                                        class="block relative group/img overflow-hidden rounded-xl h-96 w-full">
                                        <div
                                            class="absolute inset-0 bg-black/0 group-hover/img:bg-black/20 transition-all z-10 flex items-center justify-center opacity-0 group-hover/img:opacity-100">
                                            <i data-lucide="zoom-in"
                                                class="text-white w-10 h-10 drop-shadow-lg scale-75 group-hover/img:scale-100 transition-all duration-300"></i>
                                        </div>
                                        <img src="{{ asset('storage/' . $fotoEvidencia) }}"
                                            class="w-full h-full object-cover shadow-2xl rounded-xl">
                                    </a>
                                </div>
                            @endif

                            <div class="relative group">
                                {{-- Input simple (sin multiple) --}}
                                <input type="file" name="foto_evidencia" id="foto_evidencia" accept="image/*"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
                                    onchange="previewImage(event)">
                                <div id="dropzone"
                                    class="bg-white/5 border-2 border-dashed border-white/20 rounded-[2rem] p-8 flex flex-col items-center justify-center group-hover:bg-white/10 transition-all shadow-inner min-h-64 border-spacing-4">
                                    <i data-lucide="upload-cloud" id="upload-icon" class="w-8 h-8 text-rose-400 mb-2"></i>
                                    <span id="file-name-display"
                                        class="text-[10px] font-bold uppercase tracking-widest text-slate-300 text-center">
                                        {{ $fotoEvidencia ? 'CLICK PARA CAMBIAR' : 'SUBIR FOTO' }}
                                    </span>
                                    <img id="img-preview" src="#"
                                        class="hidden mt-4 h-96 w-full object-cover rounded-lg border-2 border-rose-500 shadow-xl">
                                </div>
                            </div>
                        </div>
                    </div>
                </div> {{-- FIN DEL CONTENEDOR OSCURO --}}

                {{-- 10.- FIRMA (BOTÓN NUEVO DISEÑO FUERA DEL BOX NEGRO) --}}
                <div class="pt-10 pb-5 mt-6">
                    <button type="submit" id="btn-submit-action"
                        class="w-full group bg-rose-600 text-white p-8 rounded-[3rem] font-black shadow-2xl shadow-rose-200 flex items-center justify-between hover:bg-rose-700 transition-all duration-500 active:scale-[0.98] cursor-pointer">
                        <div class="flex items-center gap-8 pointer-events-none">
                            <div
                                class="h-16 w-16 bg-white/20 rounded-3xl flex items-center justify-center group-hover:rotate-12 transition-all shadow-lg border border-white/30">
                                <i data-lucide="save" id="icon-save-loader" class="w-8 h-8 text-white"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-xl uppercase tracking-[0.3em] leading-none">Confirmar Registro</p>
                                <p class="text-[10px] text-rose-200 font-bold uppercase mt-3 tracking-widest">Sincronizar
                                    Módulo Puerperio</p>
                            </div>
                        </div>
                        <div
                            class="h-14 w-14 bg-white/10 rounded-full flex items-center justify-center group-hover:bg-white group-hover:text-rose-600 transition-all duration-500">
                            <i data-lucide="chevron-right" class="w-7 h-7"></i>
                        </div>
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script>
        // --- LÓGICA DE NEGOCIO ---

        // FUNCION PARA RENUMERAR SECCIONES DINAMICAMENTE
        function updateSectionNumbers() {
            const sections = document.querySelectorAll('.monitoreo-section');
            let counter = 1;

            sections.forEach(section => {
                if (!section.classList.contains('hidden')) {
                    const numberSpan = section.querySelector('.section-number');
                    if (numberSpan) {
                        numberSpan.textContent = counter;
                    }
                    counter++;
                }
            });
        }

        // 1. Mostrar/Ocultar campos dependiendo de SIHCE
        function toggleSihceAndDocs(val) {
            const sectionSoporte = document.getElementById('section_soporte');
            const sectionCapacitacion = document.getElementById('section_capacitacion');
            const divDj = document.getElementById('div_firmo_dj');
            const divConf = document.getElementById('div_firmo_confidencialidad');
            const djSelect = document.getElementById('firmo_dj');
            const confSelect = document.getElementById('firmo_confidencialidad');
            const capSelect = document.getElementById('recibio_capacitacion');

            if (val === 'SI') {
                sectionSoporte.classList.remove('hidden');
                sectionCapacitacion.classList.remove('hidden');
                divDj.classList.remove('hidden');
                divConf.classList.remove('hidden');
            } else {
                sectionSoporte.classList.add('hidden');
                sectionCapacitacion.classList.add('hidden');
                divDj.classList.add('hidden');
                divConf.classList.add('hidden');
                if (djSelect) djSelect.value = 'NO';
                if (confSelect) confSelect.value = 'NO';
                if (capSelect) {
                    capSelect.value = 'NO';
                    toggleEntidadCapacitadora('NO');
                }
            }
            updateSectionNumbers();
        }

        // --- NUEVA LÓGICA TIPO DNI (VISUAL) ---
        function selectDniType(tipo) {
            const input = document.getElementById('tipo_dni_input');
            const cardElectronico = document.getElementById('card_electronico');
            const cardAzul = document.getElementById('card_azul');
            const bloqueOpciones = document.getElementById('bloque_opciones_dni');
            const bloqueVersion = document.getElementById('bloque_version_dnie');
            const bloqueFirma = document.getElementById('bloque_firma_digital');

            input.value = tipo;
            bloqueOpciones.classList.remove('hidden');

            if (tipo === 'ELECTRONICO') {
                cardElectronico.classList.add('border-rose-600', 'bg-rose-50');
                cardElectronico.classList.remove('border-slate-200', 'bg-white');
                cardAzul.classList.remove('border-rose-600', 'bg-rose-50');
                cardAzul.classList.add('border-slate-200', 'bg-white');
                bloqueVersion.classList.remove('hidden');
                bloqueFirma.classList.remove('hidden');
            } else {
                cardAzul.classList.add('border-rose-600', 'bg-rose-50');
                cardAzul.classList.remove('border-slate-200', 'bg-white');
                cardElectronico.classList.remove('border-rose-600', 'bg-rose-50');
                cardElectronico.classList.add('border-slate-200', 'bg-white');
                bloqueVersion.classList.add('hidden');
                bloqueFirma.classList.add('hidden');
            }
        }

        function toggleEntidadCapacitadora(val) {
            const wrapper = document.getElementById('wrapper_entidad_capacitadora');
            val === 'SI' ? wrapper.classList.remove('hidden') : wrapper.classList.add('hidden');
        }

        function checkNationality(tipoDoc) {
            const sectionDni = document.getElementById('section_dni_detalle');
            if (tipoDoc === 'DNI') {
                sectionDni.classList.remove('hidden');
            } else {
                sectionDni.classList.add('hidden');
                const dniInput = document.getElementById('tipo_dni_input');
                const bloqueOpciones = document.getElementById('bloque_opciones_dni');
                if (dniInput) dniInput.value = "";
                if (bloqueOpciones) bloqueOpciones.classList.add('hidden');
                document.getElementById('card_electronico').classList.remove('border-rose-600', 'bg-rose-50');
                document.getElementById('card_electronico').classList.add('border-slate-200', 'bg-white');
                document.getElementById('card_azul').classList.remove('border-rose-600', 'bg-rose-50');
                document.getElementById('card_azul').classList.add('border-slate-200', 'bg-white');
            }
            updateSectionNumbers();
        }

        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('img-preview');
            const icon = document.getElementById('upload-icon');
            const fileName = document.getElementById('file-name-display');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    icon.classList.add('hidden');
                    fileName.innerText = "NUEVA: " + input.files[0].name.substring(0, 15).toUpperCase() + "...";
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const selectCapacitacion = document.getElementById('recibio_capacitacion');
            if (selectCapacitacion) toggleEntidadCapacitadora(selectCapacitacion.value);

            const selectSihce = document.getElementById('cuenta_sihce');
            if (selectSihce) toggleSihceAndDocs(selectSihce.value);

            const selectDni = document.getElementById('tipo_dni');
            const dniVal = document.getElementById('tipo_dni_input').value;
            if (dniVal) selectDniType(dniVal);

            const docTypeSelect = document.getElementById('tipo_rrhh');
            if (docTypeSelect) {
                checkNationality(docTypeSelect.value);
                docTypeSelect.addEventListener('change', function () {
                    checkNationality(this.value);
                });
            }

            // Llamada inicial a la renumeración
            updateSectionNumbers();

            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        document.getElementById('form-monitoreo-puerperio').onsubmit = function () {
            const btn = document.getElementById('btn-submit-action');
            const icon = document.getElementById('icon-save-loader');

            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');

            icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';

            return true;
        };
    </script>
@endsection