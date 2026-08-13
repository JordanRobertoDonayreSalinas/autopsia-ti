@extends('layouts.usuario')

@section('title', 'Módulo 11: Planificación Familiar')

@push('styles')
    <style>
        /* Inicializa el contador en el formulario */
        #form-planificacion-familiar-store {
            counter-reset: section-counter;
        }

        /* Selecciona los contenedores de las secciones (el div que tiene el número) */
        .section-number::before {
            counter-increment: section-counter; /* Aumenta el número */
            content: counter(section-counter, decimal-leading-zero); /* Muestra 01, 02, etc. */
        }
        [x-cloak] { display: none !important; }
        .input-standard { 
            width: 100%; 
            background-color: #f8fafc; 
            border: 1px solid #e2e8f0; 
            border-radius: 1rem; 
            padding: 0.75rem 1rem; 
            font-size: 0.875rem; 
            font-weight: 600; 
            color: #1e293b; 
            transition: all 0.2s;
        }
        .input-standard:focus { 
            outline: none; 
            border-color: #6366f1; 
            background-color: #fff; 
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); 
        }
        .spinner { border: 2px solid #f3f3f3; border-top: 2px solid #6366f1; border-radius: 50%; width: 16px; height: 16px; animation: spin 1s linear infinite; display: inline-block; }
        
        /* Estilo para los botones de eliminar imagen */
        .btn-remove-image {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background-color: #ef4444;
            color: white;
            padding: 0.4rem;
            border-radius: 9999px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
            z-index: 20;
        }
        .btn-remove-image:hover {
            background-color: #dc2626;
            transform: scale(1.1);
        }
        
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
@endpush

@section('content')
<div class="py-10 bg-slate-50 min-h-screen" 
     x-data="{ 
        openModal: false,
        tipoDoc: '{{ $detalle->contenido['personal']['tipo_doc'] ?? 'DNI' }}',
        utilizaSihce: '{{ $detalle->contenido['personal']['utiliza_sihce'] ?? 'NO' }}',
        docNuevo: '',
        profesion: '{{ $detalle->contenido['personal']['profesion'] ?? '' }}', 
        images: {
            img1: '{{ !empty($detalle->foto_1) ? asset('storage/'.$detalle->foto_1) : null }}',
            img2: '{{ !empty($detalle->foto_2) ? asset('storage/'.$detalle->foto_2) : null }}'
        },
        previewImage(event, key) {
            const file = event.target.files[0];
            if (file) { this.images[key] = URL.createObjectURL(file); }
        },
        removeImage(key, inputId, actualId) {
            this.images[key] = null;
            document.getElementById(inputId).value = '';
            document.getElementById(actualId).value = '';
        }
     }" 
     @abrir-modal-nuevo.window="openModal = true; docNuevo = $event.detail.doc">
    
    <div class="py-12 bg-slate-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                {{-- ENCABEZADO SUPERIOR ACTUALIZADO --}}
                <div class="mb-10 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            {{-- Badge en Azul (Indigo 600) --}}
                            <span class="px-3 py-1 bg-indigo-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest shadow-sm shadow-indigo-200">
                                Módulo 11
                            </span>
                            <span class="text-slate-400 font-bold text-[10px] uppercase tracking-tighter">
                                ID Acta: #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                        <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight italic">
                            PLANIFICACION FAMILIAR
                        </h2>
                        <p class="text-slate-500 font-bold uppercase text-[11px] mt-1 tracking-widest flex items-center gap-2">
                            <i data-lucide="hospital" class="w-4 h-4 text-indigo-500"></i> 
                            {{ $acta->establecimiento->nombre ?? 'Establecimiento no asignado' }}
                        </p>
                    </div>

                    <div class="flex flex-col md:flex-row items-center gap-4">
                        {{-- Selector de Fecha integrado --}}
                        <div class="flex items-center gap-3 px-4 py-2 bg-white border-2 border-slate-100 rounded-2xl shadow-sm">
                            <i data-lucide="calendar" class="w-4 h-4 text-indigo-500"></i>
                            <input type="date" name="fecha_monitoreo" form="form-planificacion-familiar-store"
                                value="{{ old('fecha_monitoreo', \Carbon\Carbon::parse($fechaParaVista)->format('Y-m-d')) }}"
                                class="border-none p-0 text-xs font-black text-slate-600 focus:ring-0 cursor-pointer uppercase">
                        </div>

                        {{-- Botón Volver --}}
                        <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}" 
                        class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-black text-xs hover:bg-slate-100 hover:border-slate-300 transition-all uppercase shadow-sm group">
                            <i data-lucide="arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-1"></i> 
                            Volver al Panel
                        </a>
                    </div>
                </div>

            <form id="form-planificacion-familiar-store" action="{{ route('usuario.monitoreo.planificacion-familiar.store', $acta->id) }}" method="POST" enctype="multipart/form-data" class="p-8 md:p-12 space-y-12">
                @csrf
                {{-- Persistencia de Fotos con IDs para Alpine --}}
                <input type="hidden" name="foto_1_actual" id="foto_1_actual" value="{{ $detalle->foto_1 ?? '' }}">
                <input type="hidden" name="foto_2_actual" id="foto_2_actual" value="{{ $detalle->foto_2 ?? '' }}">

                {{-- 01. DETALLES DEL CONSULTORIO --}}
                <div class="space-y-8 mb-12">
                    <div class="flex items-center gap-4">
                        {{-- Número de sección en Indigo-600 --}}
                        <span class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-indigo-500/20 section-number"></span>
                        <div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Detalles del Consultorio</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Información logística de la entrevista</p>
                        </div>
                    </div>

                    {{-- CONTENEDOR DE DETALLES --}}
                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            
                            {{-- Cantidad --}}
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2 flex items-center gap-2">
                                    <i data-lucide="layers" class="w-3 h-3 text-indigo-600"></i> Cantidad
                                </label>
                                <input type="number" 
                                    name="contenido[consultorio][cantidad]" 
                                    value="{{ $detalle->contenido['consultorio']['cantidad'] ?? '' }}" 
                                    placeholder="0" 
                                    min="0" {{-- Bloquea las flechitas del navegador --}}
                                    oninput="if(this.value < 0) this.value = 0;" {{-- Corrección inmediata si escriben manualmente -1 --}}
                                    class="input-standard">
                            </div>

                            {{-- Consultorio Entrevistado --}}
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2 flex items-center gap-2">
                                    <i data-lucide="door-open" class="w-3 h-3 text-indigo-600"></i> Consultorio Entrevistado
                                </label>
                                <input type="text" name="contenido[consultorio][nombre]" 
                                    value="{{ $detalle->contenido['consultorio']['nombre'] ?? '' }}" 
                                    placeholder="Ej. Consultorio 01" class="input-standard uppercase">
                            </div>

                            {{-- Turno --}}
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2 flex items-center gap-2">
                                    <i data-lucide="clock" class="w-3 h-3 text-indigo-600"></i> Turno
                                </label>
                                <select name="contenido[consultorio][turno]" class="input-standard font-bold text-indigo-600">
                                    <option value="MAÑANA" {{ ($detalle->contenido['consultorio']['turno'] ?? '') == 'MAÑANA' ? 'selected' : '' }}>MAÑANA</option>
                                    <option value="TARDE" {{ ($detalle->contenido['consultorio']['turno'] ?? '') == 'TARDE' ? 'selected' : '' }}>TARDE</option>
                                </select>
                            </div>

                        </div>
                    </div>
                </div>
                {{-- 01. RESPONSABLE DE ATENCIÓN --}}
                <div class="space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-indigo-200 section-number">
                            
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Responsable de Atención</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Datos del personal y nivel de capacitación</p>
                        </div>
                    </div>

                    {{-- CONTENEDOR PRINCIPAL --}}
                    <div class="p-4 md:p-8 rounded-[2.5rem] border border-slate-100 bg-slate-50/50 space-y-8">
                        
                        {{-- TARJETA BLANCA PARA DATOS PERSONALES --}}
                        <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-200/60 shadow-sm space-y-8">
                            
                            {{-- FILA 1: DOCUMENTO Y NOMBRES --}}
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                                <div class="md:col-span-3 space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Tipo de Doc.</label>
                                    <select name="contenido[personal][tipo_doc]" id="tipo_doc" x-model="tipoDoc" class="input-standard w-full">
                                        <option value="DNI" {{ ($detalle->contenido['personal']['tipo_doc'] ?? '') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                        <option value="C.E." {{ ($detalle->contenido['personal']['tipo_doc'] ?? '') == 'C.E.' ? 'selected' : '' }}>C.E.</option>
                                    </select>
                                </div>
                                <div class="md:col-span-3 space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2 flex items-center justify-between">
                                        Nro. Documento <span id="loading_profesional" class="hidden"><span class="spinner border-indigo-500"></span></span>
                                    </label>
                                    <input type="text" name="contenido[personal][dni]" id="doc" maxlength="12" value="{{ $detalle->contenido['personal']['dni'] ?? '' }}" class="input-standard w-full font-mono tracking-wider">
                                    <button type="button" id="btn-validar-doc" 
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 rounded-2xl transition-all flex items-center justify-center group shadow-lg shadow-indigo-100">
                                        <i data-lucide="search" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                                        <span class="ml-2 text-[10px] font-black uppercase">Validar</span>
                                    </button>
                                </div>
                                <div class="md:col-span-6 space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Nombres Completos</label>
                                    <input type="text" name="contenido[personal][nombre]" id="nombres" value="{{ $detalle->contenido['personal']['nombre'] ?? '' }}" class="input-standard w-full uppercase font-semibold">
                                </div>
                            </div>

                            {{-- FILA 2: APELLIDOS --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Apellido Paterno</label>
                                    <input type="text" name="contenido[personal][apellido_paterno]" id="apellido_paterno" value="{{ $detalle->contenido['personal']['apellido_paterno'] ?? '' }}" class="input-standard w-full uppercase font-semibold">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Apellido Materno</label>
                                    <input type="text" name="contenido[personal][apellido_materno]" id="apellido_materno" value="{{ $detalle->contenido['personal']['apellido_materno'] ?? '' }}" class="input-standard w-full uppercase font-semibold">
                                </div>
                            </div>

                            {{-- FILA 3: CONTACTO Y PROFESIÓN --}}
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-5 items-end" x-data="{ profesion: '{{ $detalle->contenido['personal']['profesion'] ?? '' }}' }">
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Celular</label>
                                    <input type="text" name="contenido[personal][contacto]" id="telefono" value="{{ $detalle->contenido['personal']['contacto'] ?? '' }}" class="input-standard w-full font-mono">
                                </div>
                                <div class="md:col-span-3 space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Correo Electrónico</label>
                                    <input type="email" name="contenido[personal][email]" id="email" value="{{ $detalle->contenido['personal']['email'] ?? '' }}" class="input-standard w-full">
                                </div>
                                <div class="md:col-span-2 space-y-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2 text-center block">Turno</label>
                                    <select name="contenido[personal][turno]" class="input-standard w-full text-center uppercase font-bold text-indigo-600">
                                        <option value="">-- SELEC. --</option>
                                        <option value="MAÑANA" {{ ($detalle->contenido['personal']['turno'] ?? '') == 'MAÑANA' ? 'selected' : '' }}>MAÑANA</option>
                                        <option value="TARDE" {{ ($detalle->contenido['personal']['turno'] ?? '') == 'TARDE' ? 'selected' : '' }}>TARDE</option>
                                    </select>
                                </div>

                                {{-- Profesión con ancho dinámico --}}
                                <div :class="profesion === 'OTROS' ? 'md:col-span-2' : 'md:col-span-5'" class="space-y-2 transition-all duration-300">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Profesión</label>
                                    <select name="contenido[personal][profesion]" x-model="profesion" class="input-standard w-full uppercase font-semibold">
                                        <option value="">-- SELECCIONE --</option>
                                        @foreach(['MEDICO', 'ODONTOLOGO(A)', 'ENFERMERO(A)', 'TECNICO ENFERMERIA', 'TECNICO LABORATORIO', 'BIOLOGO(A)', 'QUIMICO FARMACEUTICO(A)', 'NUTRICIONISTA', 'PSICOLOGO(A)', 'OBSTETRA'] as $p)
                                            <option value="{{ $p }}">{{ $p }}</option>
                                        @endforeach
                                        <option value="OTROS">OTROS</option>
                                    </select>
                                </div>
                                {{-- Campo Especifique aparece a la derecha --}}
                                <div class="md:col-span-3 space-y-2" x-show="profesion === 'OTROS'" x-cloak x-transition>
                                    <label class="text-[10px] font-black text-indigo-500 uppercase tracking-widest ml-2 italic">¿Cuál?</label>
                                    <input type="text" name="contenido[personal][profesion_otro]" value="{{ $detalle->contenido['personal']['profesion_otro'] ?? '' }}" class="input-standard w-full border-indigo-200 bg-indigo-50/30 uppercase" placeholder="Digitar profesión...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN NUEVA: USO DE SISTEMA --}}
                <div class="space-y-8 mt-12">
                    <div class="flex items-center gap-4">
                        <span class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-indigo-200 section-number"></span>
                        <div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Uso del Sistema SIHCE</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Estado de implementación tecnológica en el consultorio</p>
                        </div>
                    </div>

                    {{-- CONTENEDOR UNIFICADO (Ancho total) --}}
                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm space-y-6">
                        <div class="space-y-4">
                            <label class="text-[11px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2 ml-2">
                                <i data-lucide="monitor" class="w-4 h-4"></i> ¿Utiliza el aplicativo SIHCE?
                            </label>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- Opción SÍ --}}
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="contenido[personal][utiliza_sihce]" value="SI" x-model="utilizaSihce" class="peer sr-only">
                                    <div class="py-5 rounded-2xl border-2 border-slate-100 bg-white text-center transition-all peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 shadow-sm hover:border-slate-200">
                                        <span class="text-xs font-black uppercase tracking-widest">SÍ</span>
                                    </div>
                                </label>

                                {{-- Opción NO --}}
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="contenido[personal][utiliza_sihce]" value="NO" x-model="utilizaSihce" class="peer sr-only">
                                    <div class="py-5 rounded-2xl border-2 border-slate-100 bg-white text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 shadow-sm hover:border-slate-200">
                                        <span class="text-xs font-black uppercase tracking-widest">NO</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DOCUMENTACIÓN ADMINISTRATIVA --}}
                <div x-show="utilizaSihce === 'SI'" class="mt-6 mb-6 border-t border-slate-100 pt-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                            <i data-lucide="file-signature" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700 uppercase tracking-tight">Documentación Administrativa</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Declaración Jurada --}}
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-3">¿Firmó declaración jurada?</p>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer font-bold text-sm text-slate-600">
                                    <input type="radio" name="contenido[documentacion][firma_dj]" value="SI" 
                                        {{ ($detalle->contenido['documentacion']['firma_dj'] ?? '') == 'SI' ? 'checked' : '' }}> SÍ
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer font-bold text-sm text-slate-600">
                                    <input type="radio" name="contenido[documentacion][firma_dj]" value="NO" 
                                        {{ ($detalle->contenido['documentacion']['firma_dj'] ?? '') == 'NO' ? 'checked' : '' }}> NO
                                </label>
                            </div>
                        </div>

                        {{-- Compromiso de Confidencialidad --}}
                        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                            <p class="text-[10px] font-black text-slate-400 uppercase mb-3">¿Firmó compromiso de confidencialidad?</p>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer font-bold text-sm text-slate-600">
                                    <input type="radio" name="contenido[documentacion][firma_confidencialidad]" value="SI" 
                                        {{ ($detalle->contenido['documentacion']['firma_confidencialidad'] ?? '') == 'SI' ? 'checked' : '' }}> SÍ
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer font-bold text-sm text-slate-600">
                                    <input type="radio" name="contenido[documentacion][firma_confidencialidad]" value="NO" 
                                        {{ ($detalle->contenido['documentacion']['firma_confidencialidad'] ?? '') == 'NO' ? 'checked' : '' }}> NO
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SECCIÓN 02: TIPO DE DNI Y FIRMA DIGITAL --}}
                <div class="mt-6 border-t border-slate-100 pt-6" 
                x-show="tipoDoc === 'DNI'" 
                x-cloak 
                x-transition
                x-data="{ tipoDni: '{{ $detalle->contenido['dni_firma']['tipo_dni_fisico'] ?? ($registro->tipo_dni_fisico ?? 'AZUL') }}' }">
                    <div class="flex items-center gap-4 mb-6">
                        <span class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-indigo-200 section-number"></span>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Detalle de DNI y Firma Digital</h4>
                    </div>

                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm space-y-8">
                        
                        <div>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2 mb-4 italic">Seleccione el tipo de documento físico</p>

                            {{-- Grid de Tarjetas de Selección --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                {{-- TARJETA: DNI ELECTRÓNICO --}}
                                <label class="cursor-pointer relative group">
                                    <input type="radio" name="contenido[dni_firma][tipo_dni_fisico]" value="ELECTRONICO" class="peer sr-only"
                                        x-model="tipoDni" @change="toggleDniOptions('ELECTRONICO')"
                                        {{ ($detalle->contenido['dni_firma']['tipo_dni_fisico'] ?? '') == 'ELECTRONICO' ? 'checked' : '' }}>

                                    <div class="p-6 rounded-[2rem] border-2 transition-all duration-200 border-slate-100 bg-slate-50/50 hover:border-indigo-300 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/30">
                                        <div class="flex items-center gap-4">
                                            <div class="bg-indigo-100 p-3 rounded-2xl text-indigo-600">
                                                <i data-lucide="credit-card" class="w-6 h-6"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-black text-slate-800 text-sm uppercase">DNI ELECTRÓNICO</h4>
                                                <span class="text-[9px] font-black text-indigo-500 bg-indigo-100/50 px-2 py-0.5 rounded-full uppercase">Con Chip</span>
                                            </div>
                                            <div class="ml-auto hidden peer-checked:block text-indigo-600">
                                                <i data-lucide="check-circle-2" class="w-6 h-6 fill-indigo-600 text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                {{-- TARJETA: DNI AZUL --}}
                                <label class="cursor-pointer relative group">
                                    <input type="radio" name="contenido[dni_firma][tipo_dni_fisico]" value="AZUL" class="peer sr-only"
                                        x-model="tipoDni" @change="toggleDniOptions('AZUL')"
                                        {{ ($detalle->contenido['dni_firma']['tipo_dni_fisico'] ?? 'AZUL') == 'AZUL' ? 'checked' : '' }}>

                                    <div class="p-6 rounded-[2rem] border-2 transition-all duration-200 border-slate-100 bg-slate-50/50 hover:border-sky-300 peer-checked:border-sky-600 peer-checked:bg-sky-50/30">
                                        <div class="flex items-center gap-4">
                                            <div class="bg-sky-100 p-3 rounded-2xl text-sky-600">
                                                <i data-lucide="user-square" class="w-6 h-6"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-black text-slate-800 text-sm uppercase">DNI AZUL</h4>
                                                <span class="text-[9px] font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full uppercase">Sin chip</span>
                                            </div>
                                            <div class="ml-auto hidden peer-checked:block text-sky-600">
                                                <i data-lucide="check-circle-2" class="w-6 h-6 fill-sky-600 text-white"></i>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- CONTENEDOR CONDICIONAL --}}
                        <div id="dnie-options-container" 
                            x-show="tipoDni === 'ELECTRONICO'" 
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform scale-95"
                            x-transition:enter-end="opacity-100 transform scale-100"
                            class="bg-indigo-50/50 p-8 rounded-[2.5rem] border border-indigo-100 space-y-8">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                {{-- Versión del DNIe --}}
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-indigo-700 uppercase tracking-widest ml-2">Versión del DNIe</label>
                                    <select name="contenido[dni_firma][dnie_version]" class="input-standard">
                                        <option value="">-- SELECCIONE --</option>
                                        <option value="1.0" {{ ($detalle->contenido['dni_firma']['dnie_version'] ?? '') == '1.0' ? 'selected' : '' }}>VERSIÓN 1.0</option>
                                        <option value="2.0" {{ ($detalle->contenido['dni_firma']['dnie_version'] ?? '') == '2.0' ? 'selected' : '' }}>VERSIÓN 2.0</option>
                                        <option value="3.0" {{ ($detalle->contenido['dni_firma']['dnie_version'] ?? '') == '3.0' ? 'selected' : '' }}>VERSIÓN 3.0</option>
                                    </select>
                                </div>

                                {{-- Firma Digital en SIHCE --}}
                                <div class="space-y-2">
                                    <label class="text-[10px] font-black text-indigo-700 uppercase tracking-widest ml-2 block">¿Firma digitalmente en SIHCE?</label>
                                    <div class="flex items-center gap-8 mt-4">
                                        <label class="flex items-center gap-2 cursor-pointer font-black text-xs text-slate-500 group">
                                            <input type="radio" name="contenido[dni_firma][firma_sihce]" value="SI" 
                                                class="w-5 h-5 text-indigo-600 border-slate-300 focus:ring-indigo-500"
                                                {{ ($detalle->contenido['dni_firma']['firma_sihce'] ?? '') == 'SI' ? 'checked' : '' }}> 
                                            <span class="group-hover:text-indigo-600 transition-colors">SÍ</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer font-black text-xs text-slate-500 group">
                                            <input type="radio" name="contenido[dni_firma][firma_sihce]" value="NO" 
                                                class="w-5 h-5 text-indigo-600 border-slate-300 focus:ring-indigo-500"
                                                {{ ($detalle->contenido['dni_firma']['firma_sihce'] ?? '') == 'NO' ? 'checked' : '' }}> 
                                            <span class="group-hover:text-red-500 transition-colors">NO</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- OBSERVACIONES --}}
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2 flex items-center gap-2">
                                <i data-lucide="message-square" class="w-3 h-3 text-indigo-500"></i>
                                Observaciones o motivos de uso
                            </label>
                            <textarea name="contenido[dni_firma][observaciones]" rows="3" class="input-standard w-full resize-none" placeholder="Escriba aquí los detalles adicionales si el profesional no cuenta con el documento actualizado o presenta dificultades...">{{ $detalle->contenido['dni_firma']['observaciones'] ?? '' }}</textarea>
                        </div>

                    </div> {{-- FIN DEL CONTENEDOR CON BORDE --}}
                </div>

                {{-- SECCIÓN: CAPACITACIÓN --}}
                <div class="space-y-8 mt-12">
                    <div class="flex items-center gap-4">
                        {{-- El número se genera automáticamente por el CSS section-number que ya tienes --}}
                        <span class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-indigo-200 section-number"></span>
                        <div>
                            <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Detalles de Capacitación</h4>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Nivel de instrucción recibido por el personal</p>
                        </div>
                    </div>

                    {{-- CONTENEDOR UNIFICADO --}}
                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm space-y-8" x-data="{ recibio: '{{ $detalle->contenido['capacitacion']['recibio'] ?? 'NO' }}' }">
                        
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                            
                            {{-- 1. Pregunta principal --}}
                            <div :class="recibio === 'SI' ? 'md:col-span-4' : 'md:col-span-12'" class="transition-all duration-500">
                                <label class="text-[11px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2 ml-2 mb-4">
                                    <i data-lucide="graduation-cap" class="w-4 h-4"></i> ¿Recibió capacitación?
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="contenido[capacitacion][recibio]" value="SI" x-model="recibio" class="peer sr-only">
                                        <div class="py-3 rounded-2xl border-2 border-slate-100 bg-white text-center transition-all peer-checked:border-emerald-600 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 shadow-sm">
                                            <span class="text-[10px] font-black uppercase">SÍ</span>
                                        </div>
                                    </label>
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="contenido[capacitacion][recibio]" value="NO" x-model="recibio" class="peer sr-only">
                                        <div class="py-3 rounded-2xl border-2 border-slate-100 bg-white text-center transition-all peer-checked:border-rose-600 peer-checked:bg-rose-50 peer-checked:text-rose-700 shadow-sm">
                                            <span class="text-[10px] font-black uppercase">NO</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- 2. Entidad (Solo visible si marca SÍ) --}}
                            <div class="md:col-span-8 space-y-4" 
                                x-show="recibio === 'SI'" 
                                x-cloak 
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform translate-x-4"
                                x-transition:enter-end="opacity-100 transform translate-x-0">
                                
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2 ml-2">
                                    <i data-lucide="building-2" class="w-4 h-4 text-indigo-400"></i> ¿Por parte de quién?
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    @php $entes_guardados = (array)($detalle->contenido['capacitacion']['ente'] ?? []); @endphp
                                    @foreach(['MINSA', 'DIRESA', 'UNIDAD EJECUTORA', 'OTROS'] as $label)
                                        <label class="relative cursor-pointer group">
                                            <input type="radio" name="contenido[capacitacion][ente][]" value="{{ $label }}" 
                                                {{ in_array($label, $entes_guardados) ? 'checked' : '' }} 
                                                class="peer sr-only">
                                            <div class="px-4 py-3 min-w-[90px] text-center rounded-2xl border-2 border-slate-100 bg-white transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-600 peer-checked:text-white shadow-sm hover:border-indigo-200">
                                                <span class="text-[10px] font-black tracking-widest uppercase">{{ $label }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Aviso informativo --}}
                        <div class="p-4 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                            <p class="text-[9px] text-slate-400 leading-relaxed">
                                <span class="font-bold text-indigo-500 italic">Información:</span> El registro de capacitación es obligatorio para la gestión de usuarios y perfiles dentro del Sistema de Historias Clínicas Electrónicas (SIHCE).
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 03. EQUIPOS --}}
                <div class="space-y-8 mt-12">
                    <div class="flex items-center gap-4">
                        <span class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-indigo-200 section-number"></span>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Equipamiento del Consultorio</h4>
                    </div>

                    {{-- CONTENEDOR CON BORDE Y SOMBRA (Igual a Secciones 01 y 02) --}}
                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                        
                        {{-- Encabezado interno opcional para dar contexto --}}
                        <div class="mb-6 flex items-center gap-2">
                            <i data-lucide="monitor-speaker" class="w-4 h-4 text-indigo-500"></i>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                                Inventario de equipos informáticos y médicos en el consultorio
                            </p>
                        </div>

                        {{-- Componente de la Tabla --}}
                        <div class="overflow-hidden rounded-3xl border border-slate-100">
                            <x-tabla-equipos :equipos="$equipos" modulo="planificacion_familiar" />
                        </div>

                        {{-- Nota informativa al pie del contenedor --}}
                        <div class="mt-4 flex items-center gap-2 px-2">
                            <i data-lucide="info" class="w-3 h-3 text-slate-400"></i>
                            <p class="text-[9px] text-slate-400 font-medium">
                                * Asegúrese de registrar el estado actual y el número de serie de cada equipo para el reporte patrimonial.
                            </p>
                        </div>
                    </div> 
                </div>

                {{-- 08.- TIPO DE CONECTIVIDAD (Componente) --}}
                    <x-tipo-conectividad :contenido="$detalle->contenido ?? []" color="indigo" />

                {{-- 04. PROCESOS Y CALIDAD --}}
                <div class="space-y-8">
                    <div class="flex items-center gap-4">
                        <span class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-indigo-200 section-number"></span>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Procesos HIS y Tiempos</h4>
                    </div>

                    {{-- CONTENEDOR PRINCIPAL UNIFICADO --}}
                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm space-y-8">
                        
                        <div class="flex items-center gap-2 mb-2">
                            <i data-lucide="clock-cog" class="w-4 h-4 text-indigo-500"></i>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                                Indicadores de gestión y flujo de información HIS
                            </p>
                        </div>

                        {{-- Métricas Numéricas --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.1em] ml-2 flex items-center gap-2">
                                    <i data-lucide="timer" class="w-3 h-3 text-indigo-400"></i>
                                    Tiempo promedio atención (min)
                                </label>
                                <input 
                                    type="number" 
                                    name="contenido[tiempo_atencion]" 
                                    value="{{ $detalle->contenido['tiempo_atencion'] ?? '' }}" 
                                    min="0"
                                    oninput="if(this.value < 0) this.value = 0;"
                                    class="input-standard focus:ring-indigo-500/10"
                                    placeholder="0">
                            </div>
                            <div class="space-y-3">
                                <label class="text-[10px] font-black text-slate-500 uppercase tracking-[0.1em] ml-2 flex items-center gap-2">
                                    <i data-lucide="bar-chart" class="w-3 h-3 text-indigo-400"></i>
                                    Atenciones P.F. al mes
                                </label>
                                <input 
                                    type="number" 
                                    name="contenido[atenciones_mes]" 
                                    value="{{ $detalle->contenido['atenciones_mes'] ?? '' }}" 
                                    min="0"
                                    oninput="if(this.value < 0) this.value = 0;"
                                    class="input-standard focus:ring-indigo-500/10"
                                    placeholder="0">
                            </div>
                        </div>

                        {{-- Listado de Preguntas de Calidad --}}
                        <div class="grid grid-cols-1 gap-4">
                            @foreach([
                                'contingencia' => '¿Existe cuaderno de contingencia ante caída de internet?',
                                'coord_estad' => '¿Coordina con estadística para validar cierres mensuales?',
                                'conoce_anul' => '¿Conoce el procedimiento para anular registros?',
                                'cierre_producc' => '¿Realiza el cierre de su hoja de producción (HIS)?'
                            ] as $key => $pregunta)
                            <div class="flex flex-col md:flex-row md:items-center justify-between p-5 bg-slate-50/50 rounded-3xl border border-slate-100 hover:border-indigo-100 hover:bg-indigo-50/10 transition-all group">
                                <span class="text-[11px] font-bold text-slate-700 uppercase mb-3 md:mb-0">
                                    {{ $pregunta }}
                                </span>
                                
                                <div class="flex gap-3">
                                    @foreach(['SI', 'NO'] as $opcion)
                                    <label class="relative cursor-pointer flex-1 md:flex-none">
                                        <input type="radio" 
                                            name="contenido[preguntas][{{ $key }}]" 
                                            value="{{ $opcion }}" 
                                            {{ ($detalle->contenido['preguntas'][$key] ?? '') == $opcion ? 'checked' : '' }} 
                                            class="peer sr-only">
                                        <div class="px-5 py-2 rounded-xl border-2 border-slate-200 bg-white text-center transition-all 
                                                    peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-700
                                                    group-hover:border-slate-300">
                                            <span class="text-[10px] font-black uppercase">{{ $opcion }}</span>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Pie del contenedor --}}
                        <div class="mt-4 p-4 bg-indigo-50/30 rounded-2xl border border-dashed border-indigo-100">
                            <p class="text-[9px] text-slate-500 leading-relaxed italic">
                                * La validación de estos procesos es crítica para la calidad de la información estadística del establecimiento.
                            </p>
                        </div>

                    </div> {{-- FIN DEL CONTENEDOR --}}
                </div>
                
                {{-- 06. SOPORTE --}}
                <div class="space-y-6" 
                x-show="utilizaSihce === 'SI'"
                x-cloak 
                x-transition.duration.400ms>
                    <div class="flex items-center gap-4">
                        <span class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-indigo-200 section-number"></span>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Dificultades y Soporte</h4>
                    </div>

                    <div class="p-8 bg-white rounded-[2.5rem] border border-slate-200 shadow-sm space-y-10">
                        {{-- Pregunta 1 --}}
                        <div class="space-y-5">
                            <label class="text-[11px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                                <i data-lucide="user-cog" class="w-4 h-4"></i>
                                ¿A quién comunica dificultades?
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                @foreach(['MINSA', 'DIRESA', 'UNIDAD EJECUTORA', 'JEFE DE EESS', 'OTROS'] as $com)
                                <label class="relative cursor-pointer group">
                                    <input type="radio" name="contenido[soporte][comunica]" value="{{$com}}" 
                                        {{ ($detalle->contenido['soporte']['comunica'] ?? '') == $com ? 'checked' : '' }} 
                                        class="peer sr-only">
                                    <div class="px-4 py-3 rounded-2xl border-2 border-slate-100 bg-slate-50/50 text-center transition-all 
                                        peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-700
                                        group-hover:border-slate-300">
                                        <span class="text-[10px] font-bold uppercase tracking-tight">{{ $com }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Pregunta 2 --}}
                        <div class="space-y-5">
                            <label class="text-[11px] font-black text-indigo-600 uppercase tracking-widest flex items-center gap-2">
                                <i data-lucide="message-circle" class="w-4 h-4"></i>
                                ¿Qué medio utiliza?
                            </label>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"> 
                                @foreach(['WhatsApp', 'Celular', 'Correo', 'Otros'] as $medio)
                                    @php
                                        $icon = [
                                            'WhatsApp' => 'message-square',
                                            'Celular'  => 'phone',
                                            'Correo'   => 'mail',
                                            'Otros'    => 'more-horizontal'
                                        ][$medio];
                                    @endphp
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="contenido[soporte][medio]" value="{{$medio}}" 
                                            {{ ($detalle->contenido['soporte']['medio'] ?? '') == $medio ? 'checked' : '' }} 
                                            class="peer sr-only">
                                        <div class="flex flex-col items-center gap-2 p-4 rounded-3xl border-2 border-slate-100 bg-slate-50/50 transition-all
                                            peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700
                                            group-hover:border-slate-300">
                                            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
                                            <span class="text-[10px] font-black uppercase">{{ $medio }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 07. COMENTARIOS Y OBSERVACIONES GENERALES --}}
                <div class="space-y-6">
                    <div class="flex items-center gap-4">
                        <span class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-indigo-200 section-number"></span>
                        <h4 class="text-xs font-black text-slate-700 uppercase tracking-widest">Observaciones y/o Comentarios</h4>
                    </div>
                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm space-y-4">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2 flex items-center gap-2">
                            <i data-lucide="message-square" class="w-4 h-4 text-indigo-500"></i>
                            Notas adicionales del monitoreo
                        </label>
                        <textarea 
                            name="contenido[observaciones_generales]" 
                            rows="4" 
                            class="input-standard w-full resize-none" 
                            placeholder="Describa hallazgos relevantes, dificultades adicionales o recomendaciones para este establecimiento...">{{ $detalle->contenido['observaciones_generales'] ?? '' }}</textarea>
                    </div>
                </div>

                {{-- 08. EVIDENCIAS --}}
                <div class="space-y-8">
                    <div class="flex items-center gap-4">
                        <span class="h-12 w-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-lg font-black shadow-lg shadow-indigo-200 section-number"></span>
                        <h4 class="text-sm font-black text-slate-800 uppercase tracking-wider">Evidencias Fotográficas</h4>
                    </div>

                    {{-- CONTENEDOR UNIFICADO --}}
                    <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm space-y-6">
                        
                        <div class="flex items-center gap-2 mb-2">
                            <i data-lucide="camera" class="w-4 h-4 text-indigo-500"></i>
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest italic">
                                Capture o suba fotos del consultorio o documentación relevante
                            </p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            {{-- Espacio para Evidencia 01 --}}
                            <div class="space-y-4">
                                <label class="text-[10px] font-black uppercase text-slate-400 ml-2 italic flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-indigo-400"></span> Evidencia Principal
                                </label>
                                <div class="relative h-96 rounded-[2rem] border-2 border-dashed border-slate-200 overflow-hidden bg-slate-50 flex items-center justify-center transition-all hover:bg-slate-100 group">
                                    <template x-if="images.img1">
                                        <div class="relative w-full h-full">
                                            <img :src="images.img1" class="h-full w-full object-cover shadow-inner rounded-[2rem]">
                                            <button type="button" @click="removeImage('img1', 'input_foto_1', 'foto_1_actual')" class="btn-remove-image hover:scale-110 active:scale-95 transition-transform">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                            <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-black/50 to-transparent p-4">
                                                <p class="text-white text-[9px] font-bold uppercase tracking-widest">Imagen cargada</p>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!images.img1">
                                        <div class="flex flex-col items-center gap-3 text-slate-300 group-hover:text-indigo-400 transition-colors">
                                            <div class="p-4 rounded-full bg-white shadow-sm transition-transform group-hover:scale-110">
                                                <i data-lucide="image-plus" class="w-8 h-8"></i>
                                            </div>
                                            <span class="text-[10px] font-black uppercase tracking-tighter">Seleccionar Archivo</span>
                                        </div>
                                    </template>
                                    <input type="file" name="foto_evidencia_1" id="input_foto_1" @change="previewImage($event, 'img1')" class="absolute inset-0 opacity-0 cursor-pointer z-10">
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex items-start gap-3 bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100">
                            <i data-lucide="shield-check" class="w-4 h-4 text-indigo-500 mt-0.5"></i>
                            <p class="text-[9px] text-indigo-700 leading-relaxed font-medium">
                                <span class="font-bold">Privacidad de datos:</span> Las imágenes subidas serán utilizadas exclusivamente para fines de auditoría y respaldo del presente monitoreo Planificación Familiar, cumpliendo con la normativa vigente de protección de datos personales.
                            </p>
                        </div>

                    </div> {{-- FIN DEL CONTENEDOR --}}
                </div>
                <div class="pt-10">
                    <button type="submit" class="w-full bg-indigo-600 text-white py-8 rounded-[2.5rem] font-black uppercase shadow-xl hover:bg-indigo-700 transition-all">
                        FINALIZAR Y GUARDAR PLANIFICACIÓN FAMILIAR
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL --}}
    <div x-show="openModal" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="openModal = false"></div>
        <div class="relative bg-white rounded-[3rem] p-10 text-center max-w-lg w-full">
            <h3 class="text-2xl font-black text-slate-800 uppercase italic">Nuevo Profesional</h3>
            <p class="my-6 text-slate-600">El documento <strong x-text="docNuevo"></strong> no existe. Se han limpiado los campos.</p>
            <button @click="openModal = false" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black uppercase">ENTENDIDO</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();

        // Búsqueda de profesional
            const inputDoc = document.getElementById('doc');
            const btnValidar = document.getElementById('btn-validar-doc');
            const loader = document.getElementById('loading_profesional');
            const tipoDocSelect = document.getElementById('tipo_doc');

            const limpiarInputs = () => {
                ['nombres', 'apellido_paterno', 'apellido_materno', 'telefono', 'email'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = '';
                });
                const root = document.querySelector('[x-data]');
                if (root) { Alpine.$data(root).profesion = ''; }
            };

            if (btnValidar) {
                btnValidar.addEventListener('click', function() {
                    const docValue = inputDoc.value.trim();
                    if (docValue === '') { alert('Ingrese documento'); return; }

                    loader.classList.remove('hidden');
                    btnValidar.disabled = true;

                    // 1. ACCESO A ALPINE
                    const alpineRoot = document.querySelector('[x-data]');
                    const store = Alpine.$data(alpineRoot);

                    fetch(`{{ url('usuario/monitoreo/profesional/buscar') }}/${docValue}`)
                        .then(r => r.json())
                        .then(data => {
                            loader.classList.add('hidden');
                            btnValidar.disabled = false;

                            if (data.exists || data.exists_external) {
                                // 2. RESET TOTAL DE ESTADOS
                                store.profesion = ''; 
                                const selectProf = document.querySelector('select[name="contenido[personal][profesion]"]');
                                const inputOtro = document.querySelector('input[name="contenido[personal][profesion_otro]"]');
                                
                                if (selectProf) selectProf.value = '';
                                if (inputOtro) inputOtro.value = '';

                                // 3. LLENADO DE DATOS PERSONALES
                                document.getElementById('nombres').value = data.nombres || '';
                                document.getElementById('apellido_paterno').value = data.apellido_paterno || '';
                                document.getElementById('apellido_materno').value = data.apellido_materno || '';
                                if(document.getElementById('telefono')) document.getElementById('telefono').value = data.telefono || '';
                                if(document.getElementById('email')) document.getElementById('email').value = data.email || '';

                                // 4. LÓGICA MAESTRA DE ASIGNACIÓN
                                if (data.exists) {
                                    const cargoBD = (data.cargo || '').toUpperCase().trim();
                                    const normalizar = (t) => t.replace(/[\s()]/g, '');
                                    
                                    // Buscamos si el cargo de la BD existe en las opciones del SELECT
                                    let coincidencia = Array.from(selectProf.options).find(opt => 
                                        opt.value !== "OTROS" && opt.value !== "" && (opt.value === cargoBD || normalizar(opt.value) === normalizar(cargoBD))
                                    );

                                    if (coincidencia) {
                                        // --- CASO A: PROFESIÓN ENCONTRADA EN LA LISTA ---
                                        console.log("Coincidencia encontrada:", coincidencia.value);
                                        
                                        // Forzamos el valor en el HTML y en Alpine simultáneamente
                                        selectProf.value = coincidencia.value; 
                                        store.profesion = coincidencia.value; 

                                    } else if (cargoBD !== '') {
                                        // --- CASO B: NO ESTÁ EN LA LISTA (OTROS) ---
                                        console.log("No está en lista, moviendo a OTROS:", cargoBD);
                                        
                                        selectProf.value = 'OTROS';
                                        store.profesion = 'OTROS';
                                        
                                        // Retardo para asegurar que el input "¿Cuál?" sea visible
                                        setTimeout(() => {
                                            const inputManual = document.querySelector('input[name="contenido[personal][profesion_otro]"]');
                                            if (inputManual) {
                                                inputManual.value = cargoBD;
                                                inputManual.dispatchEvent(new Event('input', { bubbles: true }));
                                            }
                                        }, 100);
                                    }
                                } else {
                                    // exists_external = true
                                    if (typeof Swal !== 'undefined') {
                                        const Toast = Swal.mixin({
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 4000,
                                            timerProgressBar: true
                                        });
                                        const fuente = data.fuente === 'mpi_engineers' ? 'fuente alternativa' : 'RENIEC';
                                        let tokenMsg = (data.remaining_tokens !== undefined && data.remaining_tokens !== null)
                                            ? ` (Tokens: ${data.remaining_tokens})` : '';
                                        Toast.fire({
                                            icon: 'info',
                                            title: `Datos encontrados en ${fuente}.${tokenMsg} Complete la profesión.`
                                        });
                                    } else {
                                        alert("Datos encontrados en RENIEC/externo. Complete la información restante.");
                                    }
                                }
                                
                                // Sincronización final obligatoria para disparar reactividad
                                selectProf.dispatchEvent(new Event('change', { bubbles: true }));

                            } else {
                                limpiarInputs();
                                if (data.quota_exceeded) {
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire('Límite Excedido', 'Se ha agotado el límite de consultas a RENIEC. Ingrese los datos manualmente.', 'warning');
                                    } else {
                                        alert('Límite de consultas a RENIEC excedido. Ingrese los datos manualmente.');
                                    }
                                }
                                window.dispatchEvent(new CustomEvent('abrir-modal-nuevo', { detail: { doc: docValue } }));
                            }
                        })
                        .catch(err => {
                            loader.classList.add('hidden');
                            btnValidar.disabled = false;
                            console.error("Error:", err);
                        });
                });
            }
        });

        // Función para mostrar/ocultar opciones de DNIe
        function toggleDniOptions(tipo) {
        const container = document.getElementById('dnie-options-container');

        if (tipo === 'ELECTRONICO') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            // Opcional: Limpiar los campos internos si se cambia a DNI Azul
            // document.querySelector('select[name="contenido[dnie_version]"]').value = "";
            // document.querySelectorAll('input[name="contenido[firma_sihce]"]').forEach(el => el.checked = false);
        }
        }

        // Ejecutar al cargar la página (para ediciones donde ya hay datos guardados)
        document.addEventListener("DOMContentLoaded", function() {
        // Verificar cuál radio button está seleccionado al inicio
        const selectedDni = document.querySelector('input[name="contenido[tipo_dni_fisico]"]:checked');
        if (selectedDni) {
            toggleDniOptions(selectedDni.value);
        }

        // Reinicializar iconos si usas Lucide
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
        });
    </script>


    @endpush
