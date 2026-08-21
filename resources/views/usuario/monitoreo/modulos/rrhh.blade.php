@extends('layouts.usuario')

@section('title', 'Módulo Fijo: RR.HH (Recursos Humanos) - ' . $acta->establecimiento->nombre)

@section('content')
<div class="py-12 bg-slate-50 min-h-screen" x-data="rrhhManager()">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        {{-- ENCABEZADO SUPERIOR --}}
        <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="px-3 py-1 bg-violet-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">
                        Módulo Fijo
                    </span>
                    <span class="text-slate-400 font-bold text-[10px] uppercase">
                        ID Acta: #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight">
                    RR.HH — RECURSOS HUMANOS POR SERVICIO
                </h2>
                <p class="text-slate-500 font-bold uppercase text-xs mt-1">
                    <i data-lucide="hospital" class="inline-block w-4 h-4 mr-1 text-violet-500"></i>
                    EE.SS: {{ $acta->establecimiento->codigo ?? 'S/C' }} - {{ $acta->establecimiento->nombre }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('usuario.monitoreo.rrhh.pdf', $acta->id) }}?v={{ time() }}" target="_blank"
                    x-show="trabajadores.length > 0"
                    x-transition
                    class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-violet-200 text-violet-700 hover:bg-violet-50 rounded-2xl font-black text-xs transition-all uppercase shadow-sm">
                    <i data-lucide="file-text" class="w-4 h-4"></i> Generar Reporte PDF
                </a>
                <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}"
                    class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-black text-xs hover:bg-slate-50 transition-all uppercase shadow-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al Panel
                </a>
            </div>
        </div>

        {{-- ALERTA MENSAJES --}}
        @if(session('success'))
            <div class="mb-8 p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-2xl flex items-center gap-3 font-bold text-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-2xl flex items-center gap-3 font-bold text-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- FORMULARIO PRINCIPAL DE GUARDADO --}}
        <form action="{{ route('usuario.monitoreo.rrhh.store', $acta->id) }}" method="POST" enctype="multipart/form-data" id="form-rrhh-global">
            @csrf

            {{-- 1. SECCIÓN: AGREGAR / EDITAR TRABAJADOR --}}
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-slate-100 mb-8 relative">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-2xl bg-violet-600 text-white flex items-center justify-center font-black text-sm">
                            <i data-lucide="user-plus" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h3 class="text-slate-900 font-black text-lg uppercase tracking-tight" x-text="editIndex === -1 ? 'Registrar Personal de Salud' : 'Modificando Trabajador #' + (editIndex + 1)"></h3>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Complete los datos y presione agregar</p>
                        </div>
                    </div>
                    <template x-if="editIndex !== -1">
                        <button type="button" @click="resetForm()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold uppercase transition-colors flex items-center gap-1.5">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i> Cancelar Edición
                        </button>
                    </template>
                </div>

                {{-- GRID DE CAMPOS DE ENTRADA --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                    {{-- 1. SERVICIO --}}
                    <div class="md:col-span-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1">
                            <i data-lucide="layers" class="w-3.5 h-3.5 text-violet-500"></i> Servicio Asignado <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="form.servicio" class="w-full px-4 py-3 bg-violet-50/70 border-2 border-violet-100 rounded-xl font-bold text-sm text-violet-900 outline-none focus:border-violet-500 uppercase transition-all cursor-pointer">
                            <template x-for="s in serviciosDisponibles" :key="s">
                                <option :value="s" x-text="s"></option>
                            </template>
                            <option value="OTROS">+ OTRO SERVICIO (ESPECIFICAR)</option>
                        </select>

                        {{-- CAMPO ESPECIFICAR OTRO SERVICIO --}}
                        <div x-show="form.servicio === 'OTROS'" x-transition class="mt-2.5">
                            <label class="block text-[9px] font-black text-violet-700 uppercase tracking-wider mb-1 flex items-center gap-1">
                                <i data-lucide="edit-3" class="w-3 h-3"></i> Especifique el Servicio <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="form.servicio_otro" placeholder="DIGITE EL NOMBRE DEL SERVICIO (EJ: TRAUMATOLOGÍA)..."
                                class="w-full px-4 py-2.5 bg-violet-100/70 border-2 border-violet-300 focus:border-violet-600 rounded-xl font-black text-xs text-violet-900 outline-none uppercase transition-all shadow-sm">
                        </div>
                    </div>

                    {{-- 2. TIPO DOC --}}
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Tipo Doc. <span class="text-rose-500">*</span></label>
                        <select x-model="form.tipo_doc" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm text-slate-700 outline-none focus:border-violet-500 uppercase transition-all cursor-pointer">
                            <option value="DNI">DNI</option>
                            <option value="C.E.">C.E.</option>
                        </select>
                    </div>

                    {{-- 3. N° DOCUMENTO + BOTÓN BÚSQUEDA --}}
                    <div class="md:col-span-6">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center justify-between">
                            <span>N° Documento <span class="text-rose-500">*</span></span>
                            <span class="text-[9px] text-violet-600 font-bold lowercase tracking-normal" x-show="form.tipo_doc === 'DNI'">Búsqueda automática al ingresar 8 dígitos</span>
                        </label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" x-model="form.doc" @input="onDocInput()" maxlength="15" placeholder="EJ: 45892314"
                                    class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-black text-slate-800 text-sm outline-none focus:border-violet-500 uppercase transition-all">
                                <div class="absolute right-3 top-1/2 -translate-y-1/2" x-show="isSearching">
                                    <div class="w-4 h-4 border-2 border-violet-600 border-t-transparent rounded-full animate-spin"></div>
                                </div>
                            </div>
                            <button type="button" @click="consultarDocManual()" :disabled="isSearching"
                                class="px-5 py-3 bg-slate-900 hover:bg-black text-white rounded-xl font-black text-xs uppercase tracking-wider flex items-center gap-1.5 transition-all active:scale-95 disabled:opacity-50">
                                <i data-lucide="search" class="w-4 h-4 text-violet-400"></i>
                                <span>Buscar</span>
                            </button>
                        </div>
                    </div>

                    {{-- 4. APELLIDO PATERNO --}}
                    <div class="md:col-span-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Apellido Paterno <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="form.apellido_paterno" placeholder="APELLIDO PATERNO"
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm text-slate-800 outline-none focus:border-violet-500 uppercase transition-all">
                    </div>

                    {{-- 5. APELLIDO MATERNO --}}
                    <div class="md:col-span-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Apellido Materno</label>
                        <input type="text" x-model="form.apellido_materno" placeholder="APELLIDO MATERNO"
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm text-slate-800 outline-none focus:border-violet-500 uppercase transition-all">
                    </div>

                    {{-- 6. NOMBRES --}}
                    <div class="md:col-span-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">Nombres <span class="text-rose-500">*</span></label>
                        <input type="text" x-model="form.nombres" placeholder="NOMBRES COMPLETOS"
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm text-slate-800 outline-none focus:border-violet-500 uppercase transition-all">
                    </div>

                    {{-- 7. PROFESIÓN (SELECT MEJORADO + CAMPO PARA OTROS) --}}
                    <div class="md:col-span-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1">
                            <i data-lucide="briefcase" class="w-3.5 h-3.5 text-violet-500"></i> Profesión <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="form.profesion" @change="onProfesionChange()" class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm text-slate-800 outline-none focus:border-violet-500 uppercase transition-all cursor-pointer">
                            @foreach($profesiones as $prof)
                                <option value="{{ $prof }}">{{ $prof }}</option>
                            @endforeach
                        </select>

                        {{-- CAMPO ESPECIFICAR OTRA PROFESIÓN --}}
                        <div x-show="form.profesion === 'OTROS'" x-transition class="mt-2.5">
                            <label class="block text-[9px] font-black text-violet-700 uppercase tracking-wider mb-1 flex items-center gap-1">
                                <i data-lucide="edit-3" class="w-3 h-3"></i> Especifique la Profesión <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" x-model="form.profesion_otra" placeholder="DIGITE LA PROFESIÓN O CARGO..."
                                class="w-full px-4 py-2.5 bg-violet-100/70 border-2 border-violet-300 focus:border-violet-600 rounded-xl font-black text-xs text-violet-900 outline-none uppercase transition-all shadow-sm">
                        </div>
                    </div>

                    {{-- 8. COLEGIATURA CON PREFIJO / COLEGIO PROFESIONAL --}}
                    <div class="md:col-span-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center justify-between">
                            <span class="flex items-center gap-1"><i data-lucide="award" class="w-3.5 h-3.5 text-violet-500"></i> N° Colegiatura (Máx 6 dígitos)</span>
                            <span class="text-[9px] font-black text-violet-600 uppercase" x-show="form.colegio_profesional" x-text="'Colegio: ' + form.colegio_profesional"></span>
                        </label>
                        <div class="flex rounded-xl overflow-hidden border-2 border-slate-200 focus-within:border-violet-500 bg-slate-50 transition-all shadow-sm">
                            <template x-if="form.colegio_profesional">
                                <span class="px-3.5 py-3 bg-violet-100/90 text-violet-900 font-black text-xs uppercase flex items-center border-r border-violet-200 select-none tracking-wider"
                                    x-text="form.colegio_profesional"></span>
                            </template>
                            <input type="text" 
                                x-model="form.colegiatura" 
                                @input="form.colegiatura = form.colegiatura.replace(/\D/g, '').slice(0, 6)"
                                @blur="if (form.colegiatura) { const d = form.colegiatura.replace(/\D/g, '').slice(0, 6); if (d) form.colegiatura = d.padStart(6, '0'); }"
                                maxlength="6" 
                                :placeholder="form.colegio_profesional ? 'EJ: 045123' : 'N° DE COLEGIATURA (MÁX 6 DÍGITOS)'"
                                class="w-full px-4 py-3 bg-transparent font-bold text-sm text-slate-800 outline-none uppercase transition-all">
                        </div>
                    </div>

                    {{-- 9. RNE --}}
                    <div class="md:col-span-4">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1">
                            <i data-lucide="shield" class="w-3.5 h-3.5 text-violet-500"></i> RNE (Especialista)
                        </label>
                        <input type="text" x-model="form.rne" placeholder="EJ: RNE 34512 (Opcional)"
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm text-slate-800 outline-none focus:border-violet-500 uppercase transition-all">
                    </div>

                    {{-- 10. CORREO --}}
                    <div class="md:col-span-6">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1">
                            <i data-lucide="mail" class="w-3.5 h-3.5 text-violet-500"></i> Correo Electrónico
                        </label>
                        <input type="email" x-model="form.correo" placeholder="ejemplo@minsa.gob.pe"
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm text-slate-800 outline-none focus:border-violet-500 lowercase transition-all">
                    </div>

                    {{-- 11. CELULAR --}}
                    <div class="md:col-span-6">
                        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 flex items-center gap-1">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-violet-500"></i> Celular de Contacto
                        </label>
                        <input type="tel" x-model="form.celular" maxlength="15" placeholder="EJ: 987654321"
                            class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm text-slate-800 outline-none focus:border-violet-500 transition-all">
                    </div>

                    {{-- 12. ¿TIENE DNI ELECTRÓNICO (DNIe)? Y VERSIÓN --}}
                    <div class="md:col-span-6 bg-blue-50/50 border-2 border-blue-100 rounded-2xl p-4 flex flex-col justify-between">
                        <div>
                            <label class="block text-[10px] font-black text-blue-900 uppercase tracking-widest mb-2 flex items-center gap-1">
                                <i data-lucide="credit-card" class="w-3.5 h-3.5 text-blue-600"></i> ¿Tiene DNI Electrónico (DNIe)?
                            </label>
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <button type="button" @click="form.tiene_dnie = 'SI'"
                                    :class="form.tiene_dnie === 'SI' ? 'bg-blue-600 text-white font-black shadow-md' : 'bg-white text-slate-600 font-bold hover:bg-slate-50 border border-slate-200'"
                                    class="py-2 rounded-xl text-xs uppercase transition-all flex items-center justify-center gap-1">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> SÍ
                                </button>
                                <button type="button" @click="form.tiene_dnie = 'NO'"
                                    :class="form.tiene_dnie === 'NO' ? 'bg-slate-700 text-white font-black shadow-md' : 'bg-white text-slate-600 font-bold hover:bg-slate-50 border border-slate-200'"
                                    class="py-2 rounded-xl text-xs uppercase transition-all flex items-center justify-center gap-1">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i> NO
                                </button>
                            </div>
                        </div>

                        {{-- VERSIÓN DNI ELECTRÓNICO (SOLO SI TIENE DNIe) --}}
                        <div x-show="form.tiene_dnie === 'SI'" x-transition class="pt-2 border-t border-blue-200/50">
                            <label class="block text-[9px] font-black text-blue-800 uppercase tracking-wider mb-1 flex items-center justify-between">
                                <span>Versión del DNI Electrónico</span>
                                <span class="text-[9px] font-bold text-blue-600 lowercase tracking-normal">Seleccione la versión</span>
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" @click="form.version_dnie = 'v1.0'"
                                    :class="form.version_dnie === 'v1.0' ? 'bg-blue-700 text-white font-black shadow-sm ring-2 ring-blue-400' : 'bg-white text-slate-700 font-bold hover:bg-blue-50/50 border border-blue-200'"
                                    class="py-2 px-1 rounded-xl text-xs uppercase transition-all flex flex-col items-center justify-center">
                                    <span class="font-black text-xs">v1.0</span>
                                    <span class="text-[8px] opacity-75 font-normal">(2013)</span>
                                </button>
                                <button type="button" @click="form.version_dnie = 'v2.0'"
                                    :class="form.version_dnie === 'v2.0' ? 'bg-blue-700 text-white font-black shadow-sm ring-2 ring-blue-400' : 'bg-white text-slate-700 font-bold hover:bg-blue-50/50 border border-blue-200'"
                                    class="py-2 px-1 rounded-xl text-xs uppercase transition-all flex flex-col items-center justify-center">
                                    <span class="font-black text-xs">v2.0</span>
                                    <span class="text-[8px] opacity-75 font-normal">(2020)</span>
                                </button>
                                <button type="button" @click="form.version_dnie = 'v3.0'"
                                    :class="form.version_dnie === 'v3.0' ? 'bg-blue-700 text-white font-black shadow-sm ring-2 ring-blue-400' : 'bg-white text-slate-700 font-bold hover:bg-blue-50/50 border border-blue-200'"
                                    class="py-2 px-1 rounded-xl text-xs uppercase transition-all flex flex-col items-center justify-center">
                                    <span class="font-black text-xs">v3.0</span>
                                    <span class="text-[8px] opacity-75 font-normal">(2025)</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 13. ¿ES SERUMS? Y PERIODO --}}
                    <div class="md:col-span-6 bg-violet-50/50 border-2 border-violet-100 rounded-2xl p-4 flex flex-col justify-between">
                        <div>
                            <label class="block text-[10px] font-black text-violet-900 uppercase tracking-widest mb-2 flex items-center gap-1">
                                <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-violet-600"></i> ¿Es SERUMS?
                            </label>
                            <div class="grid grid-cols-2 gap-2 mb-3">
                                <button type="button" @click="form.es_serums = 'SI'"
                                    :class="form.es_serums === 'SI' ? 'bg-emerald-500 text-white font-black shadow-md' : 'bg-white text-slate-600 font-bold hover:bg-slate-50 border border-slate-200'"
                                    class="py-2 rounded-xl text-xs uppercase transition-all flex items-center justify-center gap-1">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i> SÍ
                                </button>
                                <button type="button" @click="form.es_serums = 'NO'"
                                    :class="form.es_serums === 'NO' ? 'bg-slate-700 text-white font-black shadow-md' : 'bg-white text-slate-600 font-bold hover:bg-slate-50 border border-slate-200'"
                                    class="py-2 rounded-xl text-xs uppercase transition-all flex items-center justify-center gap-1">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i> NO
                                </button>
                            </div>
                        </div>

                        {{-- PERIODO (SOLO SI ES SERUMS) --}}
                        <div x-show="form.es_serums === 'SI'" x-transition class="pt-2 border-t border-violet-200/50">
                            <label class="block text-[9px] font-black text-violet-800 uppercase tracking-wider mb-1">Periodo SERUMS</label>
                            <select x-model="form.periodo_serums" class="w-full px-3 py-2 bg-white border border-violet-200 rounded-xl font-black text-xs text-violet-900 outline-none focus:border-violet-500 uppercase cursor-pointer">
                                <option value="">-- SELECCIONE PERIODO --</option>
                                @foreach($periodosSerums as $per)
                                    <option value="{{ $per }}">{{ $per }}</option>
                                @endforeach
                                <template x-if="form.periodo_serums && !@json($periodosSerums).includes(form.periodo_serums)">
                                    <option :value="form.periodo_serums" x-text="form.periodo_serums"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- BOTÓN AGREGAR / ACTUALIZAR EN LA LISTA --}}
                <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="agregarOActualizarTrabajador()"
                        class="px-8 py-3.5 bg-violet-600 hover:bg-violet-700 text-white font-black rounded-xl text-xs uppercase tracking-widest shadow-lg hover:shadow-violet-200 transition-all flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4" x-show="editIndex === -1"></i>
                        <i data-lucide="check-circle" class="w-4 h-4" x-show="editIndex !== -1"></i>
                        <span x-text="editIndex === -1 ? 'Agregar Trabajador al Padrón' : 'Actualizar Datos en la Lista'"></span>
                    </button>
                </div>
            </div>

            {{-- 2. SECCIÓN: TABLA DE TRABAJADORES REGISTRADOS --}}
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-slate-100 mb-8">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-6 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-9 h-9 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black text-sm">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h3 class="text-slate-900 font-black text-lg uppercase tracking-tight">Padrón de Trabajadores Registrados</h3>
                            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">
                                Total: <span class="text-violet-600 font-black" x-text="trabajadores.length"></span> trabajador(es) listado(s)
                            </p>
                        </div>
                    </div>

                    {{-- BUSCADOR RÁPIDO Y FILTRO POR SERVICIO REACTIVO --}}
                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <div class="relative flex-1 md:w-60">
                            <input type="text" x-model="filtroTexto" placeholder="Filtrar por nombre o DNI..."
                                class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 outline-none focus:border-violet-500 uppercase">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        </div>
                        <select x-model="filtroServicio" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 outline-none focus:border-violet-500 uppercase cursor-pointer">
                            <option value="">TODOS LOS SERVICIOS</option>
                            <template x-for="s in serviciosDisponibles" :key="s">
                                <option :value="s" x-text="s"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- TABLA --}}
                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 text-white text-[10px] font-black uppercase tracking-wider">
                                <th class="py-3 px-4 text-center w-12">#</th>
                                <th class="py-3 px-4">Servicio</th>
                                <th class="py-3 px-4">Doc. Identidad</th>
                                <th class="py-3 px-4">Apellidos y Nombres</th>
                                <th class="py-3 px-4">Profesión / Colegiatura</th>
                                <th class="py-3 px-4">Contacto</th>
                                <th class="py-3 px-4 text-center">DNI Electrónico</th>
                                <th class="py-3 px-4 text-center">SERUMS / Periodo</th>
                                <th class="py-3 px-4 text-center w-28">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs font-bold text-slate-700">
                            <template x-for="(t, idx) in trabajadoresFiltrados" :key="t.id || idx">
                                <tr class="hover:bg-violet-50/40 transition-colors">
                                    <td class="py-3 px-4 text-center font-black text-slate-400" x-text="idx + 1"></td>
                                    
                                    {{-- SERVICIO --}}
                                    <td class="py-3 px-4">
                                        <span class="px-2.5 py-1 bg-violet-100 text-violet-800 rounded-lg text-[10px] font-black uppercase tracking-wider" x-text="t.servicio"></span>
                                    </td>

                                    {{-- DOCUMENTO --}}
                                    <td class="py-3 px-4">
                                        <span class="text-[10px] text-slate-400 font-bold uppercase block" x-text="t.tipo_doc || 'DNI'"></span>
                                        <span class="font-black text-slate-900 tracking-wider" x-text="t.doc"></span>
                                    </td>

                                    {{-- APELLIDOS Y NOMBRES --}}
                                    <td class="py-3 px-4">
                                        <span class="font-black text-slate-900 uppercase block" x-text="t.apellido_paterno + ' ' + (t.apellido_materno || '')"></span>
                                        <span class="text-slate-500 font-bold uppercase text-[11px]" x-text="t.nombres"></span>
                                    </td>

                                    {{-- PROFESIÓN / COLEGIATURA / RNE --}}
                                    <td class="py-3 px-4">
                                        <span class="font-bold text-slate-800 uppercase block text-[11px]" x-text="t.profesion || 'NO ESPECIFICADO'"></span>
                                        <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-400">
                                            <span x-show="t.colegiatura" class="font-black text-slate-700" x-text="(t.colegio_profesional ? t.colegio_profesional + ' ' : '') + t.colegiatura"></span>
                                            <span x-show="t.rne" class="text-violet-600 font-bold" x-text="'RNE: ' + t.rne"></span>
                                        </div>
                                    </td>

                                    {{-- CONTACTO --}}
                                    <td class="py-3 px-4">
                                        <div class="text-[11px]">
                                            <span class="block text-slate-600 font-bold" x-show="t.celular" x-text="'📱 ' + t.celular"></span>
                                            <span class="block text-slate-400 lowercase text-[10px]" x-show="t.correo" x-text="t.correo"></span>
                                            <span class="text-slate-300 italic text-[10px]" x-show="!t.celular && !t.correo">Sin datos</span>
                                        </div>
                                    </td>

                                    {{-- DNI ELECTRÓNICO --}}
                                    <td class="py-3 px-4 text-center">
                                        <template x-if="t.tiene_dnie === 'SI'">
                                            <div>
                                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-black rounded-md uppercase">DNIe</span>
                                                <span class="block text-[10px] font-black text-blue-900 mt-0.5" x-text="t.version_dnie || 'v2.0'"></span>
                                            </div>
                                        </template>
                                        <template x-if="t.tiene_dnie !== 'SI'">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-bold rounded-md uppercase">NO</span>
                                        </template>
                                    </td>

                                    {{-- SERUMS --}}
                                    <td class="py-3 px-4 text-center">
                                        <template x-if="t.es_serums === 'SI'">
                                            <div>
                                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-md uppercase">SERUMS</span>
                                                <span class="block text-[10px] font-black text-violet-700 mt-0.5" x-text="t.periodo_serums || 'S/P'"></span>
                                            </div>
                                        </template>
                                        <template x-if="t.es_serums !== 'SI'">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[10px] font-bold rounded-md uppercase">NO</span>
                                        </template>
                                    </td>

                                    {{-- ACCIONES --}}
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" @click="editarTrabajador(idx)"
                                                class="h-8 w-8 rounded-xl bg-slate-100 hover:bg-violet-100 text-slate-500 hover:text-violet-700 flex items-center justify-center transition-colors" title="Editar datos">
                                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                                            </button>
                                            <button type="button" @click="eliminarTrabajador(idx)"
                                                class="h-8 w-8 rounded-xl bg-slate-100 hover:bg-rose-100 text-slate-500 hover:text-rose-600 flex items-center justify-center transition-colors" title="Eliminar">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>

                            <template x-if="trabajadores.length === 0">
                                <tr>
                                    <td colspan="9" class="py-12 text-center text-slate-400">
                                        <i data-lucide="user-x" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                        <p class="font-bold uppercase text-xs">No hay trabajadores registrados en este padrón.</p>
                                        <p class="text-[10px] text-slate-400">Complete el formulario superior y presione "Agregar Trabajador al Padrón".</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- INPUTS OCULTOS PARA ENVIAR EN EL FORMULARIO POST --}}
                <div id="hidden-trabajadores-inputs">
                    <template x-for="(t, idx) in trabajadores" :key="idx">
                        <div>
                            <input type="hidden" :name="'trabajadores[' + idx + '][id]'" :value="t.id">
                            <input type="hidden" :name="'trabajadores[' + idx + '][servicio]'" :value="t.servicio">
                            <input type="hidden" :name="'trabajadores[' + idx + '][tipo_doc]'" :value="t.tipo_doc">
                            <input type="hidden" :name="'trabajadores[' + idx + '][doc]'" :value="t.doc">
                            <input type="hidden" :name="'trabajadores[' + idx + '][apellido_paterno]'" :value="t.apellido_paterno">
                            <input type="hidden" :name="'trabajadores[' + idx + '][apellido_materno]'" :value="t.apellido_materno">
                            <input type="hidden" :name="'trabajadores[' + idx + '][nombres]'" :value="t.nombres">
                            <input type="hidden" :name="'trabajadores[' + idx + '][profesion]'" :value="t.profesion">
                            <input type="hidden" :name="'trabajadores[' + idx + '][colegio_profesional]'" :value="t.colegio_profesional || ''">
                            <input type="hidden" :name="'trabajadores[' + idx + '][colegiatura]'" :value="t.colegiatura || ''">
                            <input type="hidden" :name="'trabajadores[' + idx + '][correo]'" :value="t.correo">
                            <input type="hidden" :name="'trabajadores[' + idx + '][celular]'" :value="t.celular">
                            <input type="hidden" :name="'trabajadores[' + idx + '][rne]'" :value="t.rne">
                            <input type="hidden" :name="'trabajadores[' + idx + '][tiene_dnie]'" :value="t.tiene_dnie">
                            <input type="hidden" :name="'trabajadores[' + idx + '][version_dnie]'" :value="t.version_dnie">
                            <input type="hidden" :name="'trabajadores[' + idx + '][es_serums]'" :value="t.es_serums">
                            <input type="hidden" :name="'trabajadores[' + idx + '][periodo_serums]'" :value="t.periodo_serums">
                        </div>
                    </template>
                </div>
            </div>

            {{-- 3. SECCIÓN: EVIDENCIA FOTOGRÁFICA --}}
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-slate-100 mb-8">
                <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                    <span class="w-9 h-9 rounded-2xl bg-violet-600 text-white flex items-center justify-center font-black text-sm">
                        <i data-lucide="camera" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="text-slate-900 font-black text-lg uppercase tracking-tight">Evidencia Fotográfica de RR.HH</h3>
                        <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Fotografías del área de personal, rol de turnos o documentación</p>
                    </div>
                </div>

                <x-evidencia-fotografica
                    :evidencias="$evidencias"
                    :qr-url="route('usuario.monitoreo.rrhh.evidencia-movil.qr', $acta->id)"
                    :estado-url="route('usuario.monitoreo.rrhh.evidencia-movil.estado', $acta->id)"
                    :max="10"
                    label="Fotografías de RR.HH" />
            </div>

            {{-- 4. OBSERVACIONES GENERALES DE RR.HH --}}
            <div class="bg-white rounded-[2.5rem] p-8 shadow-xl border border-slate-100 mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center font-black text-xs">
                        <i data-lucide="message-square" class="w-4 h-4"></i>
                    </span>
                    <h3 class="text-slate-900 font-black text-sm uppercase tracking-tight">Observaciones o Notas de Recursos Humanos</h3>
                </div>
                <textarea name="observaciones" rows="3" placeholder="Ingrese notas adicionales o incidencias encontradas en la distribución de personal..."
                    class="w-full bg-slate-50 border-2 border-slate-200 rounded-2xl p-4 text-slate-800 font-bold uppercase outline-none focus:border-violet-500 text-xs">{{ $detalle->contenido['observaciones'] ?? '' }}</textarea>
            </div>

            {{-- BOTÓN FINAL DE GUARDAR TODO --}}
            <div class="flex justify-end gap-4">
                <button type="submit" class="flex items-center gap-3 px-12 py-4 bg-violet-600 hover:bg-violet-700 text-white font-black rounded-2xl transition-all shadow-xl hover:shadow-violet-200 text-sm uppercase tracking-widest active:scale-95">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    <span>Guardar Padrón de RR.HH</span>
                </button>
            </div>
        </form>

    </div>
</div>

<script>
function rrhhManager() {
    const serviciosLista = @json($servicios ?? []);
    const profesionesLista = @json($profesiones ?? []);

    // Mapa oficial de colegios profesionales del Perú
    const mapaPrefijos = {
        'MÉDICO CIRUJANO': 'CMP',
        'CIRUJANO DENTISTA / ODONTÓLOGO(A)': 'COP',
        'LIC. EN ENFERMERÍA': 'CEP',
        'LIC. EN OBSTETRICIA': 'COP',
        'LIC. EN PSICOLOGÍA': 'C.Ps.P',
        'LIC. EN NUTRICIÓN': 'CNP',
        'QUÍMICO FARMACÉUTICO(A)': 'CQFP',
        'LIC. TECNOLOGÍA MÉDICA': 'CTMP',
        'BIÓLOGO(A)': 'CBP'
    };

    return {
        trabajadores: (@json($trabajadores ?? [])).map(t => {
            let colNum = (t.colegiatura || '').toString().trim();
            let colProf = (t.colegio_profesional || '').trim();
            if (!colProf) {
                const prof = (t.profesion || '').trim().toUpperCase();
                const pref = mapaPrefijos[prof] || '';
                if (pref && colNum.toUpperCase().startsWith(pref.toUpperCase())) {
                    colProf = pref;
                    colNum = colNum.substring(pref.length).trim();
                } else if (pref) {
                    colProf = pref;
                }
            }
            colNum = colNum.replace(/\D/g, '').slice(0, 6);
            if (colNum) {
                colNum = colNum.padStart(6, '0');
            }
            return {
                ...t,
                colegio_profesional: colProf,
                colegiatura: colNum,
                tiene_dnie: t.tiene_dnie || 'NO',
                version_dnie: t.version_dnie || 'v2.0'
            };
        }),
        serviciosCatalogo: serviciosLista,
        profesionesCatalogo: profesionesLista,
        form: {
            id: '',
            servicio: 'MEDICINA',
            servicio_otro: '',
            tipo_doc: 'DNI',
            doc: '',
            apellido_paterno: '',
            apellido_materno: '',
            nombres: '',
            profesion: 'MÉDICO CIRUJANO',
            profesion_otra: '',
            colegio_profesional: 'CMP',
            colegiatura: '',
            correo: '',
            celular: '',
            rne: '',
            tiene_dnie: 'NO',
            version_dnie: 'v2.0',
            es_serums: 'NO',
            periodo_serums: '{{ end($periodosSerums) }}'
        },
        editIndex: -1,
        isSearching: false,
        filtroTexto: '',
        filtroServicio: '',

        init() {
            this.$nextTick(() => {
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
        },

        // Devuelve el prefijo oficial según la profesión seleccionada
        get prefijoColegiatura() {
            if (this.form.profesion === 'OTROS') return this.form.colegio_profesional || '';
            return mapaPrefijos[this.form.profesion] || '';
        },

        // Lista consolidada de servicios: estándar + personalizados registrados
        get serviciosDisponibles() {
            const set = new Set();
            this.serviciosCatalogo.forEach(s => {
                if (s && s !== 'OTROS') set.add(s.trim().toUpperCase());
            });
            this.trabajadores.forEach(t => {
                if (t.servicio && t.servicio.trim() && t.servicio.toUpperCase() !== 'OTROS') {
                    set.add(t.servicio.trim().toUpperCase());
                }
            });
            return Array.from(set);
        },

        get trabajadoresFiltrados() {
            return this.trabajadores.filter(t => {
                const matchServicio = !this.filtroServicio || t.servicio === this.filtroServicio;
                const search = this.filtroTexto.trim().toUpperCase();
                const matchTexto = !search || 
                    (t.doc && t.doc.includes(search)) ||
                    (t.apellido_paterno && t.apellido_paterno.toUpperCase().includes(search)) ||
                    (t.apellido_materno && t.apellido_materno.toUpperCase().includes(search)) ||
                    (t.nombres && t.nombres.toUpperCase().includes(search)) ||
                    (t.profesion && t.profesion.toUpperCase().includes(search)) ||
                    (t.servicio && t.servicio.toUpperCase().includes(search));
                return matchServicio && matchTexto;
            });
        },

        onDocInput() {
            const doc = this.form.doc.trim();
            if (this.form.tipo_doc === 'DNI' && doc.length === 8) {
                this.consultarDoc(doc);
            }
        },

        onProfesionChange() {
            if (this.form.profesion !== 'OTROS') {
                this.form.profesion_otra = '';
                this.form.colegio_profesional = mapaPrefijos[this.form.profesion] || '';
            } else {
                this.form.colegio_profesional = '';
            }
        },

        consultarDocManual() {
            const doc = this.form.doc.trim();
            if (!doc) {
                Swal.fire({ icon: 'warning', title: 'Ingrese un N° de documento' });
                return;
            }
            this.consultarDoc(doc);
        },

        async consultarDoc(doc) {
            this.isSearching = true;
            try {
                // 1. Consulta inicial
                const res = await fetch(`{{ url('/usuario/monitoreo/profesional/buscar') }}/${doc}`);
                const data = await res.json();

                if (data.exists || data.exists_external) {
                    this.form.nombres = (data.nombres || '').toUpperCase();
                    this.form.apellido_paterno = (data.apellido_paterno || '').toUpperCase();
                    this.form.apellido_materno = (data.apellido_materno || '').toUpperCase();
                    if (data.email) this.form.correo = data.email.toLowerCase();
                    if (data.telefono) this.form.celular = data.telefono;
                    
                    if (data.cargo) {
                        const cargoDb = data.cargo.toUpperCase().trim();
                        if (this.profesionesCatalogo.includes(cargoDb)) {
                            this.form.profesion = cargoDb;
                            this.form.profesion_otra = '';
                            this.form.colegio_profesional = mapaPrefijos[cargoDb] || '';
                        } else {
                            this.form.profesion = 'OTROS';
                            this.form.profesion_otra = cargoDb;
                            this.form.colegio_profesional = '';
                        }
                    }

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500
                    });
                    Toast.fire({
                        icon: 'success',
                        title: data.exists ? 'Personal encontrado en base de datos' : 'Datos obtenidos desde RENIEC'
                    });
                } else {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500
                    });
                    Toast.fire({
                        icon: 'info',
                        title: 'No se encontraron datos automáticos. Complete manualmente.'
                    });
                }
            } catch (e) {
                console.error('Error al consultar DNI:', e);
            } finally {
                this.isSearching = false;
                this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
            }
        },

        agregarOActualizarTrabajador() {
            if (!this.form.doc.trim() || !this.form.nombres.trim() || !this.form.apellido_paterno.trim()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Por favor complete N° de Documento, Nombres y Apellido Paterno.',
                    confirmButtonColor: '#7c3aed'
                });
                return;
            }

            // Validar si seleccionó OTROS en Servicio pero dejó el texto vacío
            let servicioFinal = this.form.servicio;
            if (this.form.servicio === 'OTROS') {
                const espec = this.form.servicio_otro.trim().toUpperCase();
                if (!espec) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Especifique el Servicio',
                        text: 'Ha seleccionado OTROS en servicio. Por favor describa el nombre del servicio.',
                        confirmButtonColor: '#7c3aed'
                    });
                    return;
                }
                servicioFinal = espec;
            }

            // Validar si seleccionó OTROS en Profesión pero dejó el texto vacío
            let profesionFinal = this.form.profesion;
            if (this.form.profesion === 'OTROS') {
                const especProf = this.form.profesion_otra.trim().toUpperCase();
                if (!especProf) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Especifique la Profesión',
                        text: 'Ha seleccionado OTROS en profesión. Por favor describa la profesión o cargo.',
                        confirmButtonColor: '#7c3aed'
                    });
                    return;
                }
                profesionFinal = especProf;
            }

            // Sanitizar colegiatura a solo números (máximo 6 dígitos) completando con ceros a la izquierda
            const rawCol = (this.form.colegiatura || '').toString().trim();
            const digitsCol = rawCol.replace(/\D/g, '').slice(0, 6);
            const colegiaturaFinal = digitsCol ? digitsCol.padStart(6, '0') : '';
            const colegioProfFinal = (this.form.colegio_profesional || mapaPrefijos[profesionFinal] || '').trim().toUpperCase();

            const itemData = {
                id: this.form.id || ('tr_' + Date.now() + '_' + Math.random().toString(36).substr(2, 4)),
                servicio: servicioFinal.toUpperCase(),
                tipo_doc: this.form.tipo_doc || 'DNI',
                doc: this.form.doc.trim(),
                apellido_paterno: this.form.apellido_paterno.trim().toUpperCase(),
                apellido_materno: this.form.apellido_materno.trim().toUpperCase(),
                nombres: this.form.nombres.trim().toUpperCase(),
                profesion: profesionFinal.toUpperCase(),
                colegio_profesional: colegioProfFinal,
                colegiatura: colegiaturaFinal,
                correo: this.form.correo.trim().toLowerCase(),
                celular: this.form.celular.trim(),
                rne: this.form.rne.trim().toUpperCase(),
                tiene_dnie: this.form.tiene_dnie,
                version_dnie: this.form.tiene_dnie === 'SI' ? (this.form.version_dnie || 'v2.0') : '',
                es_serums: this.form.es_serums,
                periodo_serums: this.form.es_serums === 'SI' ? this.form.periodo_serums : ''
            };

            if (this.editIndex === -1) {
                // Verificar si ya existe este DNI en el mismo servicio
                const yaExiste = this.trabajadores.some(t => t.doc === itemData.doc && t.servicio === itemData.servicio);
                if (yaExiste) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Documento duplicado',
                        text: `El personal con documento ${itemData.doc} ya está registrado en el servicio ${itemData.servicio}.`
                    });
                    return;
                }
                this.trabajadores.push(itemData);
                Swal.fire({
                    icon: 'success',
                    title: 'Agregado al Padrón',
                    text: `${itemData.nombres} ${itemData.apellido_paterno} fue agregado a la lista.`,
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                this.trabajadores[this.editIndex] = itemData;
                Swal.fire({
                    icon: 'success',
                    title: 'Datos Actualizados',
                    text: 'Los datos del trabajador fueron modificados en la lista.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }

            this.resetForm();
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        editarTrabajador(index) {
            const t = this.trabajadores[index];
            if (!t) return;

            this.editIndex = index;

            // Determinar si el servicio está en catálogo base o es personalizado
            let servVal = t.servicio || 'MEDICINA';
            let servOtroVal = '';
            if (!this.serviciosCatalogo.includes(servVal) || servVal === 'OTROS') {
                servOtroVal = (servVal === 'OTROS') ? '' : servVal;
                servVal = 'OTROS';
            }

            // Determinar si la profesión está en catálogo base o es personalizada
            let profVal = t.profesion || 'MÉDICO CIRUJANO';
            let profOtraVal = '';
            if (!this.profesionesCatalogo.includes(profVal) || profVal === 'OTROS') {
                profOtraVal = (profVal === 'OTROS') ? '' : profVal;
                profVal = 'OTROS';
            }

            // Extraer solo el número de la colegiatura y el colegio profesional
            let colProf = (t.colegio_profesional || '').trim();
            let rawCol = (t.colegiatura || '').toString().trim();
            let colNum = rawCol;

            if (!colProf) {
                const prefActual = mapaPrefijos[profVal] || '';
                if (prefActual && rawCol.toUpperCase().startsWith(prefActual.toUpperCase())) {
                    colProf = prefActual;
                    colNum = rawCol.substring(prefActual.length).trim();
                } else if (prefActual) {
                    colProf = prefActual;
                }
            }
            colNum = colNum.replace(/\D/g, '').slice(0, 6);
            if (colNum) {
                colNum = colNum.padStart(6, '0');
            }

            this.form = {
                id: t.id || '',
                servicio: servVal,
                servicio_otro: servOtroVal,
                tipo_doc: t.tipo_doc || 'DNI',
                doc: t.doc || '',
                apellido_paterno: t.apellido_paterno || '',
                apellido_materno: t.apellido_materno || '',
                nombres: t.nombres || '',
                profesion: profVal,
                profesion_otra: profOtraVal,
                colegio_profesional: colProf,
                colegiatura: colNum,
                correo: t.correo || '',
                celular: t.celular || '',
                rne: t.rne || '',
                tiene_dnie: t.tiene_dnie || 'NO',
                version_dnie: t.version_dnie || 'v2.0',
                es_serums: t.es_serums || 'NO',
                periodo_serums: t.periodo_serums || '{{ end($periodosSerums) }}'
            };

            window.scrollTo({ top: 0, behavior: 'smooth' });
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        eliminarTrabajador(index) {
            Swal.fire({
                title: '¿Eliminar de la lista?',
                text: 'El trabajador será quitado del padrón de este monitoreo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.trabajadores.splice(index, 1);
                    if (this.editIndex === index) {
                        this.resetForm();
                    }
                    this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
                }
            });
        },

        resetForm() {
            this.editIndex = -1;
            this.form = {
                id: '',
                servicio: 'MEDICINA',
                servicio_otro: '',
                tipo_doc: 'DNI',
                doc: '',
                apellido_paterno: '',
                apellido_materno: '',
                nombres: '',
                profesion: 'MÉDICO CIRUJANO',
                profesion_otra: '',
                colegio_profesional: 'CMP',
                colegiatura: '',
                correo: '',
                celular: '',
                rne: '',
                tiene_dnie: 'NO',
                version_dnie: 'v2.0',
                es_serums: 'NO',
                periodo_serums: '{{ end($periodosSerums) }}'
            };
        }
    };
}
</script>
@endsection
