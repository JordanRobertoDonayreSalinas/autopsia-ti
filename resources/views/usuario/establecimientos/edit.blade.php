@extends('layouts.usuario')

@section('title', 'Editar Establecimiento')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        #map {
            height: 320px;
            width: 100%;
        }
    </style>
@endpush

@section('header-content')
    <div class="flex flex-col">
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Editar Establecimiento</h1>
        <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
            <span>Catálogo</span>
            <span class="text-slate-300">•</span>
            <span>Editar Establecimiento</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto pb-12">

    {{-- Botón para regresar --}}
    <div class="mb-4 flex items-center justify-between">
        <a href="{{ route('usuario.establecimientos.index') }}" 
           class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-700 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Volver al Listado</span>
        </a>
    </div>

    {{-- Errores de Validación --}}
    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 shadow-sm">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                <span class="font-bold text-sm">Se encontraron los siguientes errores:</span>
            </div>
            <ul class="text-xs list-disc list-inside space-y-1 ml-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="form-establecimiento" action="{{ route('usuario.establecimientos.update', $establecimiento->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
            
            {{-- Encabezado Visual del Establecimiento --}}
            <div class="p-8 border-b border-slate-100 bg-gradient-to-r from-slate-50/70 via-white to-blue-50/30">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
                    <div class="flex flex-col md:flex-row items-center gap-6">
                        <div class="relative group">
                            <div class="h-20 w-20 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-3xl font-black shadow-2xl shadow-blue-200 uppercase">
                                <i data-lucide="building" class="w-10 h-10"></i>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-2xl font-extrabold text-slate-800" id="header-nombre">{{ $establecimiento->nombre }}</h2>
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-2.5 mt-1.5">
                                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold tracking-wider uppercase border border-slate-200">CÓDIGO: {{ $establecimiento->codigo }}</span>
                                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold tracking-wider uppercase border border-blue-200" id="badge-categoria">CATEGORÍA: {{ $establecimiento->categoria ?? 'SIN CAT.' }}</span>
                                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold tracking-wider uppercase border border-emerald-200" id="badge-estado">ESTADO: {{ $establecimiento->estado ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Botón de Sincronización con RENIPRESS --}}
                    <div>
                        <button type="button" id="btn-sync-renipress" 
                                class="px-6 py-3.5 rounded-2xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold text-xs shadow-lg shadow-cyan-500/20 transition-all hover:scale-[1.02] flex items-center gap-2.5">
                            <i data-lucide="refresh-cw" class="w-4 h-4" id="icon-sync-renipress"></i>
                            <span id="text-sync-renipress">Consultar RENIPRESS</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-8 md:p-10 space-y-10">
                
                {{-- Bloque 1: Información General --}}
                <section data-section="info_general" class="relative group">
                    <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-1 bg-blue-500 rounded-full"></div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Información General</h3>
                        </div>
                        <button type="button" class="btn-restore-section hidden px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-all flex items-center gap-1.5" data-section="info_general">
                            <i data-lucide="undo-2" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span>Restaurar esta sección</span>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Código Único (RENIPRESS)</label>
                            <input type="text" name="codigo" value="{{ old('codigo', $establecimiento->codigo) }}" required
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-100 cursor-not-allowed" readonly>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Nombre del Establecimiento</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $establecimiento->nombre) }}" required
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-sm font-semibold text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Institución / Entidad</label>
                            <input type="text" name="institucion" value="{{ old('institucion', $establecimiento->institucion) }}" placeholder="Ej. GOBIERNO REGIONAL"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Categoría</label>
                            <input type="text" name="categoria" value="{{ old('categoria', $establecimiento->categoria) }}" placeholder="Ej. I-3"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Estado RENIPRESS</label>
                            <input type="text" name="estado" value="{{ old('estado', $establecimiento->estado) }}" placeholder="Ej. ACTIVO"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Condición RENIPRESS</label>
                            <input type="text" name="condicion" value="{{ old('condicion', $establecimiento->condicion) }}" placeholder="Ej. EN FUNCIONAMIENTO"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>
                    </div>
                </section>

                {{-- Bloque 2: Director Médico / Responsable --}}
                <section data-section="responsable_legal" class="relative group">
                    <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-1 bg-indigo-500 rounded-full"></div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Director Médico / Responsable Legal</h3>
                        </div>
                        <button type="button" class="btn-restore-section hidden px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-all flex items-center gap-1.5" data-section="responsable_legal">
                            <i data-lucide="undo-2" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span>Restaurar esta sección</span>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Nombre Completo del Jefe / Responsable</label>
                            <input type="text" name="responsable" value="{{ old('responsable', $establecimiento->responsable) }}" placeholder="Nombre del responsable"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Tipo de Documento</label>
                            <select name="tipo_documento" id="tipo_documento" class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-sm font-semibold text-slate-700 bg-slate-50/30 cursor-pointer">
                                <option value="" {{ old('tipo_documento', $establecimiento->tipo_documento) == '' ? 'selected' : '' }}>-- Seleccione --</option>
                                <option value="DNI" {{ old('tipo_documento', $establecimiento->tipo_documento) == 'DNI' ? 'selected' : '' }}>DNI</option>
                                <option value="CE" {{ old('tipo_documento', $establecimiento->tipo_documento) == 'CE' || old('tipo_documento', $establecimiento->tipo_documento) == 'CEX' ? 'selected' : '' }}>CE (Carné de Extranjería)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Número de Documento</label>
                            <div class="flex gap-2">
                                <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento', $establecimiento->numero_documento) }}" placeholder="Ej. 70123456"
                                       class="flex-1 px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-sm font-semibold text-slate-700 bg-slate-50/30 hover:bg-white">
                                <button type="button" id="btn-buscar-doc"
                                        class="px-5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-1.5 shrink-0">
                                    <i data-lucide="search" class="w-4 h-4"></i>
                                    <span id="btn-buscar-text">Buscar</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Colegio Profesional</label>
                            <input type="text" name="colegio_profesional" value="{{ old('colegio_profesional', $establecimiento->colegio_profesional) }}" placeholder="Ej. COLEGIO MEDICO DEL PERU"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">N° Colegiatura</label>
                            <input type="text" name="colegiatura" value="{{ old('colegiatura', $establecimiento->colegiatura) }}" placeholder="Ej. 045123"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">RNE (Registro Esp.)</label>
                            <input type="text" name="rne" value="{{ old('rne', $establecimiento->rne) }}" placeholder="Ej. 02145"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>
                    </div>
                </section>

                {{-- Bloque 3: Ubicación y Estructura --}}
                <section data-section="ubicacion" class="relative group">
                    <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-1 bg-emerald-500 rounded-full"></div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Ubicación y Estructura</h3>
                        </div>
                        <button type="button" class="btn-restore-section hidden px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-all flex items-center gap-1.5" data-section="ubicacion">
                            <i data-lucide="undo-2" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span>Restaurar esta sección</span>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Departamento</label>
                            <input type="text" name="departamento" value="{{ old('departamento', $establecimiento->departamento) }}"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Provincia</label>
                            <input type="text" name="provincia" value="{{ old('provincia', $establecimiento->provincia) }}" required
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Distrito</label>
                            <input type="text" name="distrito" value="{{ old('distrito', $establecimiento->distrito) }}" required
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Centro Poblado</label>
                            <input type="text" name="centro_poblado" value="{{ old('centro_poblado', $establecimiento->centro_poblado) }}"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Dirección del Establecimiento</label>
                            <input type="text" name="direccion" value="{{ old('direccion', $establecimiento->direccion) }}" placeholder="Ej. AV. PANAMERICANA SUR KM 300"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Latitud</label>
                            <input type="text" name="latitud" id="latitud" value="{{ old('latitud', $establecimiento->latitud) }}" placeholder="-14.0628"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Longitud</label>
                            <input type="text" name="longitud" id="longitud" value="{{ old('longitud', $establecimiento->longitud) }}" placeholder="-75.7286"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Altitud (m.s.n.m.)</label>
                            <input type="text" name="altitud" value="{{ old('altitud', $establecimiento->altitud) }}" placeholder="Ej. 406"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div class="md:col-span-3 mt-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Ubicación Geográfica (Mapa)</label>
                            <div id="map" class="w-full h-80 rounded-2xl border border-slate-200 shadow-inner z-10"></div>
                            <span class="text-[10px] text-slate-400 mt-1.5 block">💡 Puedes arrastrar el marcador o hacer clic en cualquier parte del mapa para ajustar la latitud y longitud automáticamente.</span>
                        </div>
                    </div>
                </section>

                {{-- Bloque 4: Detalles Operativos e Infraestructura --}}
                <section data-section="operativos_infraestructura" class="relative group">
                    <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-1 bg-amber-500 rounded-full"></div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Detalles Operativos e Infraestructura</h3>
                        </div>
                        <button type="button" class="btn-restore-section hidden px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-all flex items-center gap-1.5" data-section="operativos_infraestructura">
                            <i data-lucide="undo-2" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span>Restaurar esta sección</span>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Teléfono de Contacto</label>
                            <input type="text" name="telefono" value="{{ old('telefono', $establecimiento->telefono) }}" placeholder="Ej. 056-234567"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Correo Electrónico</label>
                            <input type="email" name="correo" value="{{ old('correo', $establecimiento->correo) }}" placeholder="correo@minsa.gob.pe"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Horario de Atención</label>
                            <input type="text" name="horario_atencion" value="{{ old('horario_atencion', $establecimiento->horario_atencion) }}" placeholder="Ej. 08:00 - 20:00"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">N° de Ambientes</label>
                            <input type="text" name="numero_ambientes" value="{{ old('numero_ambientes', $establecimiento->numero_ambientes) }}" placeholder="Ej. 12"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">N° de Camas</label>
                            <input type="text" name="numero_camas" value="{{ old('numero_camas', $establecimiento->numero_camas) }}" placeholder="Ej. 4"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">N° Resolución Creación</label>
                            <input type="text" name="numero_resolucion_creacion" value="{{ old('numero_resolucion_creacion', $establecimiento->numero_resolucion_creacion) }}" placeholder="Ej. R.D. N° 451-2018"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Fecha Resolución Creación</label>
                            <input type="text" name="fecha_creacion_resolucion" value="{{ old('fecha_creacion_resolucion', $establecimiento->fecha_creacion_resolucion) }}" placeholder="DD/MM/AAAA"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Fecha de Registro</label>
                            <input type="text" name="fecha_registro" value="{{ old('fecha_registro', $establecimiento->fecha_registro) }}" placeholder="DD/MM/AAAA"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>
                    </div>
                </section>

                {{-- Bloque 5: Redes MINSA --}}
                <section data-section="redes_minsa" class="relative group">
                    <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-1 bg-cyan-500 rounded-full"></div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Estructura Redes MINSA</h3>
                        </div>
                        <button type="button" class="btn-restore-section hidden px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-all flex items-center gap-1.5" data-section="redes_minsa">
                            <i data-lucide="undo-2" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span>Restaurar esta sección</span>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Red de Salud</label>
                            <input type="text" name="red" value="{{ old('red', $establecimiento->red) }}"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-cyan-500/10 focus:border-cyan-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Microred de Salud</label>
                            <input type="text" name="microred" value="{{ old('microred', $establecimiento->microred) }}"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-cyan-500/10 focus:border-cyan-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">CLAS</label>
                            <input type="text" name="clas" value="{{ old('clas', $establecimiento->clas) }}" placeholder="Ej. CLAS ACOMAYO"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-cyan-500/10 focus:border-cyan-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">ODSIS</label>
                            <input type="text" name="odsis" value="{{ old('odsis', $establecimiento->odsis) }}" placeholder="Ej. ICA"
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-cyan-500/10 focus:border-cyan-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>
                    </div>
                </section>

                {{-- Bloque 6: UPSS y UPS (Servicios de Salud) --}}
                <section data-section="upss_ups" class="relative group">
                    <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <div class="h-7 w-1 bg-purple-500 rounded-full"></div>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Unidades Prestadoras (UPSS) y Servicios (UPS)</h3>
                        </div>
                        <button type="button" class="btn-restore-section hidden px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition-all flex items-center gap-1.5" data-section="upss_ups">
                            <i data-lucide="undo-2" class="w-3.5 h-3.5 text-slate-500"></i>
                            <span>Restaurar esta sección</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Contenedor UPSS --}}
                        <div class="bg-slate-50/60 rounded-3xl p-6 border border-slate-200/80">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                    <i data-lucide="activity" class="w-4 h-4 text-purple-600"></i>
                                    <span>UPSS (Unidades Prestadoras)</span>
                                </h4>
                                <span class="px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-700 text-[10px] font-extrabold" id="badge-count-upss">
                                    {{ count($establecimiento->upss ?? []) }} Registrados
                                </span>
                            </div>
                            <div class="max-h-64 overflow-y-auto pr-1 space-y-2" id="container-upss-list">
                                @forelse($establecimiento->upss ?? [] as $u)
                                    <div class="p-3 bg-white rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2.5">
                                            <span class="px-2 py-0.5 bg-purple-50 text-purple-700 font-extrabold text-[10px] rounded-lg border border-purple-100">{{ $u['codigo'] ?? '-' }}</span>
                                            <span class="font-semibold text-slate-700">{{ $u['nombre'] ?? '' }}</span>
                                        </div>
                                        <span class="text-[10px] font-bold uppercase text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">{{ $u['estado'] ?? 'ACTIVO' }}</span>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-slate-400 text-xs italic">
                                        No hay UPSS registradas. Consulta RENIPRESS para sincronizarlas.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Contenedor UPS --}}
                        <div class="bg-slate-50/60 rounded-3xl p-6 border border-slate-200/80">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                    <i data-lucide="layers" class="w-4 h-4 text-blue-600"></i>
                                    <span>UPS (Servicios de Salud)</span>
                                </h4>
                                <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-extrabold" id="badge-count-ups">
                                    {{ count($establecimiento->ups ?? []) }} Registrados
                                </span>
                            </div>
                            <div class="max-h-64 overflow-y-auto pr-1 space-y-2" id="container-ups-list">
                                @forelse($establecimiento->ups ?? [] as $s)
                                    <div class="p-3 bg-white rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2.5">
                                            <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-extrabold text-[10px] rounded-lg border border-blue-100">{{ $s['codigo'] ?? '-' }}</span>
                                            <span class="font-semibold text-slate-700">{{ $s['nombre'] ?? '' }}</span>
                                        </div>
                                        <span class="text-[10px] font-bold uppercase text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">{{ $s['estado'] ?? 'ACTIVO' }}</span>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-slate-400 text-xs italic">
                                        No hay servicios UPS registrados. Consulta RENIPRESS para sincronizarlos.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

            </div>

            {{-- Pie de Tarjeta con Botones --}}
            <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3 rounded-b-[2.5rem]">
                <a href="{{ route('usuario.establecimientos.index') }}"
                   class="px-6 py-3 rounded-2xl text-xs font-bold text-slate-500 hover:text-slate-700 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-8 py-3.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-lg shadow-blue-500/20 transition-all hover:scale-[1.02]">
                    Guardar Cambios
                </button>
            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            let initialSectionState = {};
            let fetchedUpss = null;
            let fetchedUps = null;

            function captureCurrentSectionState() {
                const state = {};
                document.querySelectorAll('section[data-section]').forEach(sec => {
                    const secName = sec.getAttribute('data-section');
                    state[secName] = {};
                    sec.querySelectorAll('input, select, textarea').forEach(input => {
                        if (input.name) {
                            state[secName][input.name] = input.value;
                        }
                    });
                });

                state['ubicacion_mapa'] = {
                    lat: document.getElementById('latitud')?.value,
                    lng: document.getElementById('longitud')?.value
                };
                state['upss_list_html'] = document.getElementById('container-upss-list')?.innerHTML;
                state['ups_list_html'] = document.getElementById('container-ups-list')?.innerHTML;
                state['badge_upss'] = document.getElementById('badge-count-upss')?.textContent;
                state['badge_ups'] = document.getElementById('badge-count-ups')?.textContent;

                return state;
            }

            initialSectionState = captureCurrentSectionState();

            // Configuración del Mapa Leaflet
            const inputLat = document.getElementById('latitud');
            const inputLng = document.getElementById('longitud');
            const mapContainer = document.getElementById('map');
            let map, marker;

            if (mapContainer && inputLat && inputLng) {
                let defaultLat = -14.0678;
                let defaultLng = -75.7286;

                if (inputLat.value) {
                    const latVal = parseFloat(inputLat.value);
                    if (!isNaN(latVal)) defaultLat = latVal;
                }
                if (inputLng.value) {
                    const lngVal = parseFloat(inputLng.value);
                    if (!isNaN(lngVal)) defaultLng = lngVal;
                }

                map = L.map('map').setView([defaultLat, defaultLng], 15);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);

                marker = L.marker([defaultLat, defaultLng], {
                    draggable: true
                }).addTo(map);

                function updateCoordinates(lat, lng) {
                    inputLat.value = lat.toFixed(8);
                    inputLng.value = lng.toFixed(8);
                }

                marker.on('dragend', function () {
                    const position = marker.getLatLng();
                    updateCoordinates(position.lat, position.lng);
                });

                map.on('click', function (event) {
                    const lat = event.latlng.lat;
                    const lng = event.latlng.lng;
                    marker.setLatLng([lat, lng]);
                    updateCoordinates(lat, lng);
                });

                function syncMapFromInputs() {
                    const lat = parseFloat(inputLat.value);
                    const lng = parseFloat(inputLng.value);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        marker.setLatLng([lat, lng]);
                        map.panTo([lat, lng]);
                    }
                }

                inputLat.addEventListener('input', syncMapFromInputs);
                inputLng.addEventListener('input', syncMapFromInputs);

                setTimeout(() => {
                    map.invalidateSize();
                }, 200);
            }

            // Sincronización con RENIPRESS AJAX
            const btnSync = document.getElementById('btn-sync-renipress');
            const btnSyncText = document.getElementById('text-sync-renipress');
            const btnSyncIcon = document.getElementById('icon-sync-renipress');

            if (btnSync) {
                btnSync.addEventListener('click', async () => {
                    btnSync.disabled = true;
                    btnSyncText.textContent = 'Consultando SUSALUD...';
                    btnSyncIcon.classList.add('animate-spin');

                    try {
                        const url = "{{ route('usuario.establecimientos.consultar-renipress', $establecimiento->id) }}";
                        const response = await fetch(url);
                        const res = await response.json();

                        if (res.ok && res.datos) {
                            const d = res.datos;

                            // 1. Llenar Información General
                            setInputValue('nombre', d.nombre);
                            setInputValue('institucion', d.institucion);
                            setInputValue('categoria', d.categoria);
                            setInputValue('estado', d.estado);
                            setInputValue('condicion', d.condicion);

                            if (document.getElementById('header-nombre')) document.getElementById('header-nombre').textContent = d.nombre || 'ESTABLECIMIENTO';
                            if (document.getElementById('badge-categoria')) document.getElementById('badge-categoria').textContent = 'CATEGORÍA: ' + (d.categoria || 'SIN CAT.');
                            if (document.getElementById('badge-estado')) document.getElementById('badge-estado').textContent = 'ESTADO: ' + (d.estado || 'N/A');

                            // 2. Llenar Responsable / Director Médico
                            if (d.director_medico) {
                                setInputValue('responsable', d.director_medico.nombres);
                                setInputValue('tipo_documento', d.director_medico.tipo_documento || 'DNI');
                                setInputValue('numero_documento', d.director_medico.numero_documento);
                                setInputValue('colegio_profesional', d.director_medico.colegio_profesional);
                                setInputValue('colegiatura', d.director_medico.colegiatura);
                                setInputValue('rne', d.director_medico.rne);
                            }

                            // 3. Llenar Ubicación
                            setInputValue('departamento', d.departamento);
                            setInputValue('provincia', d.provincia);
                            setInputValue('distrito', d.distrito);
                            setInputValue('centro_poblado', d.centro_poblado);
                            setInputValue('direccion', d.direccion);
                            setInputValue('latitud', d.latitud);
                            setInputValue('longitud', d.longitud);
                            setInputValue('altitud', d.altitud);

                            if (marker && map && d.latitud && d.longitud) {
                                const lat = parseFloat(d.latitud);
                                const lng = parseFloat(d.longitud);
                                if (!isNaN(lat) && !isNaN(lng)) {
                                    marker.setLatLng([lat, lng]);
                                    map.panTo([lat, lng]);
                                }
                            }

                            // 4. Llenar Operativos e Infraestructura
                            setInputValue('telefono', d.telefono);
                            setInputValue('correo', d.correo);
                            setInputValue('horario_atencion', d.horario_atencion);
                            setInputValue('numero_ambientes', d.numero_ambientes);
                            setInputValue('numero_camas', d.numero_camas);
                            setInputValue('numero_resolucion_creacion', d.numero_resolucion_creacion);
                            setInputValue('fecha_creacion_resolucion', d.fecha_creacion_resolucion);
                            setInputValue('fecha_registro', d.fecha_registro);

                            // 5. Llenar Redes MINSA
                            if (d.minsa) {
                                setInputValue('red', d.minsa.red);
                                setInputValue('microred', d.minsa.microred);
                                setInputValue('clas', d.minsa.clas);
                                setInputValue('odsis', d.minsa.odsis);
                            }

                            // 6. Llenar UPSS y UPS
                            fetchedUpss = d.upss || [];
                            fetchedUps = d.ups || [];
                            renderList('container-upss-list', 'badge-count-upss', fetchedUpss, 'purple');
                            renderList('container-ups-list', 'badge-count-ups', fetchedUps, 'blue');

                            document.querySelectorAll('.btn-restore-section').forEach(btn => btn.classList.remove('hidden'));

                            Swal.fire({
                                icon: 'success',
                                title: 'Sincronización Exitosa',
                                text: 'Se obtuvieron los datos actualizados de RENIPRESS. Puedes revisar y guardar.',
                                confirmButtonColor: '#10b981',
                                timer: 3000,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Atención',
                                text: res.mensaje || 'El servicio de SUSALUD se encuentra temporalmente inactivo o el código es inválido.',
                                confirmButtonColor: '#4f46e5'
                            });
                        }
                    } catch (err) {
                        console.error('Error al sincronizar RENIPRESS:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: 'No se pudo conectar con el servicio RENIPRESS.',
                            confirmButtonColor: '#ef4444'
                        });
                    } finally {
                        btnSync.disabled = false;
                        btnSyncText.textContent = 'Consultar RENIPRESS';
                        btnSyncIcon.classList.remove('animate-spin');
                    }
                });
            }

            // Restauración por Sección
            document.querySelectorAll('.btn-restore-section').forEach(btn => {
                btn.addEventListener('click', () => {
                    const secName = btn.getAttribute('data-section');
                    const sectionEl = document.querySelector(`section[data-section="${secName}"]`);

                    if (sectionEl && initialSectionState[secName]) {
                        Object.keys(initialSectionState[secName]).forEach(inputName => {
                            const val = initialSectionState[secName][inputName];
                            setInputValue(inputName, val);
                        });

                        if (secName === 'ubicacion') {
                            const initLat = parseFloat(initialSectionState['ubicacion_mapa'].lat);
                            const initLng = parseFloat(initialSectionState['ubicacion_mapa'].lng);
                            if (marker && map && !isNaN(initLat) && !isNaN(initLng)) {
                                marker.setLatLng([initLat, initLng]);
                                map.panTo([initLat, initLng]);
                            }
                        }

                        if (secName === 'upss_ups') {
                            fetchedUpss = null;
                            fetchedUps = null;
                            if (document.getElementById('container-upss-list')) {
                                document.getElementById('container-upss-list').innerHTML = initialSectionState['upss_list_html'];
                            }
                            if (document.getElementById('container-ups-list')) {
                                document.getElementById('container-ups-list').innerHTML = initialSectionState['ups_list_html'];
                            }
                            if (document.getElementById('badge-count-upss')) {
                                document.getElementById('badge-count-upss').textContent = initialSectionState['badge_upss'];
                            }
                            if (document.getElementById('badge-count-ups')) {
                                document.getElementById('badge-count-ups').textContent = initialSectionState['badge_ups'];
                            }
                        }

                        btn.classList.add('hidden');

                        Swal.fire({
                            icon: 'info',
                            title: 'Sección Restaurada',
                            text: 'Se han revertido los valores de esta sección al estado inicial.',
                            timer: 2000,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false
                        });
                    }
                });
            });

            // Enviar formulario agregando UPSS y UPS si fueron sincronizados
            const formEstablecimiento = document.getElementById('form-establecimiento');
            if (formEstablecimiento) {
                formEstablecimiento.addEventListener('submit', (e) => {
                    if (fetchedUpss !== null) {
                        fetchedUpss.forEach((item, index) => {
                            addHiddenInput('upss[' + index + '][codigo]', item.codigo || '');
                            addHiddenInput('upss[' + index + '][nombre]', item.nombre || '');
                            addHiddenInput('upss[' + index + '][estado]', item.estado || '');
                        });
                    }
                    if (fetchedUps !== null) {
                        fetchedUps.forEach((item, index) => {
                            addHiddenInput('ups[' + index + '][codigo]', item.codigo || '');
                            addHiddenInput('ups[' + index + '][nombre]', item.nombre || '');
                            addHiddenInput('ups[' + index + '][estado]', item.estado || '');
                        });
                    }
                });
            }

            function addHiddenInput(name, value) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = name;
                hidden.value = value;
                formEstablecimiento.appendChild(hidden);
            }

            function setInputValue(name, value) {
                const el = document.querySelector(`[name="${name}"]`);
                if (el) {
                    el.value = value !== null && value !== undefined ? value : '';
                }
            }

            function renderList(containerId, badgeId, items, color) {
                const container = document.getElementById(containerId);
                const badge = document.getElementById(badgeId);

                if (badge) badge.textContent = `${items.length} Registrados`;
                if (!container) return;

                if (!items || items.length === 0) {
                    container.innerHTML = `<div class="p-6 text-center text-slate-400 text-xs italic">No hay registros devueltos por RENIPRESS.</div>`;
                    return;
                }

                container.innerHTML = items.map(item => `
                    <div class="p-3 bg-white rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="px-2 py-0.5 bg-${color}-50 text-${color}-700 font-extrabold text-[10px] rounded-lg border border-${color}-100">${item.codigo || '-'}</span>
                            <span class="font-semibold text-slate-700">${item.nombre || ''}</span>
                        </div>
                        <span class="text-[10px] font-bold uppercase text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">${item.estado || 'ACTIVO'}</span>
                    </div>
                `).join('');
            }

            // Búsqueda por DNI de Responsable
            const btnBuscarDoc = document.getElementById('btn-buscar-doc');
            const btnBuscarText = document.getElementById('btn-buscar-text');
            const inputDoc = document.getElementById('numero_documento');
            const selectTipoDoc = document.getElementById('tipo_documento');
            const inputResponsable = document.querySelector('input[name="responsable"]');

            if (btnBuscarDoc) {
                btnBuscarDoc.addEventListener('click', async () => {
                    const doc = inputDoc.value.trim();
                    const tipoDoc = selectTipoDoc.value;

                    if (!tipoDoc || !doc) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atención',
                            text: 'Seleccione Tipo de Documento e ingrese el Número de Documento.',
                            confirmButtonColor: '#4f46e5'
                        });
                        return;
                    }

                    btnBuscarDoc.disabled = true;
                    btnBuscarText.textContent = 'Buscando...';

                    try {
                        const baseUrl = "{{ url('/') }}";
                        const response = await fetch(`${baseUrl}/usuario/monitoreo/profesional/buscar/${doc}`);
                        const data = await response.json();

                        if (data.exists || data.exists_external) {
                            const apePat = data.apellido_paterno || '';
                            const apeMat = data.apellido_materno || '';
                            const nombres = data.nombres || '';
                            
                            const nombreCompleto = `${nombres} ${apePat} ${apeMat}`.trim().toUpperCase();
                            inputResponsable.value = nombreCompleto;

                            Swal.fire({
                                icon: 'success',
                                title: 'Encontrado',
                                text: `Se asignó a: ${nombreCompleto}`,
                                confirmButtonColor: '#10b981',
                                timer: 3000,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'No Encontrado',
                                text: 'No se encontró información para el documento ingresado.',
                                confirmButtonColor: '#ef4444'
                            });
                        }
                    } catch (error) {
                        console.error('Error buscando profesional:', error);
                    } finally {
                        btnBuscarDoc.disabled = false;
                        btnBuscarText.textContent = 'Buscar';
                    }
                });
            }
        });
    </script>
@endpush
