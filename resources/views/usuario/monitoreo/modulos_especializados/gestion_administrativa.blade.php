@extends('layouts.usuario')

@section('title', 'Módulo: Gestion Administrativa CSMC')

@section('content')
<div class="py-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        {{-- ENCABEZADO SUPERIOR --}}
        <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="px-3 py-1 bg-teal-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">Módulo Especializado</span>
                    <span class="text-slate-400 font-bold text-[10px] uppercase">ID Acta: #{{ str_pad($acta->numero_acta ?? $acta->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight">01. Gestión Administrativa</h2>
                <p class="text-slate-500 font-bold uppercase text-xs mt-1">
                    <i data-lucide="hospital" class="inline-block w-4 h-4 mr-1 text-teal-500"></i>{{ $acta->establecimiento->nombre }}
                </p>
            </div>
            <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}" class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-black text-xs hover:bg-slate-50 transition-all uppercase shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al Panel
            </a>
        </div>

        {{-- FORMULARIO --}}
        <form action="{{ route('usuario.monitoreo.gestion_admin_esp.store', $acta->id) }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="space-y-6" 
              id="form-monitoreo-gestion-admin-esp">
            @csrf
            
            {{-- DETALLES DEL CONSULTORIO (Solo Fecha y Turno) --}}
            <div class="bg-white rounded-[2rem] p-8 shadow-lg border border-slate-100 mb-8">
                
                {{-- ENCABEZADO CON ICONO --}}
                <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-4">
                    <div class="h-10 w-10 rounded-xl bg-white text-teal-600 flex items-center justify-center shadow-sm">
                        <i data-lucide="building-2" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-teal-900 font-black text-lg uppercase tracking-tight">DETALLES DEL CONSULTORIO</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                    
                    {{-- FECHA --}}
                    <div>
                        <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2 ml-1">Fecha de Monitoreo</label>
                        <input type="date" 
                               name="contenido[fecha]" 
                               value="{{ data_get($detalle, 'contenido.fecha', date('Y-m-d')) }}" 
                               class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none focus:border-teal-500 transition-all">
                    </div>
                    
                    {{-- TURNO --}}
                    @php
                        $turnoActual = data_get($detalle, 'contenido.turno');
                        if (empty($turnoActual)) {
                            $hour = (int) date('H');
                            $turnoActual = ($hour >= 0 && $hour < 14) ? 'MAÑANA' : 'TARDE';
                        }
                    @endphp
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">
                            Turno
                        </label>
                        <div class="flex gap-4">
                            {{-- OPCIÓN MAÑANA --}}
                            <label class="flex-1 relative cursor-pointer group">
                                <input type="radio" name="contenido[turno]" value="MAÑANA" 
                                       {{ $turnoActual == 'MAÑANA' ? 'checked' : '' }} 
                                       class="peer sr-only">
                                <div class="p-3 rounded-xl border-2 border-slate-200 bg-slate-50 text-center transition-all 
                                       peer-checked:border-amber-400 peer-checked:bg-amber-50 peer-checked:text-amber-700 
                                       peer-checked:shadow-sm hover:border-amber-200">
                                    <div class="flex items-center justify-center gap-2">
                                        <i data-lucide="sun" class="w-4 h-4"></i>
                                        <span class="text-[10px] font-black uppercase tracking-wider">MAÑANA</span>
                                    </div>
                                </div>
                            </label>
                            
                            {{-- OPCIÓN TARDE --}}
                            <label class="flex-1 relative cursor-pointer group">
                                <input type="radio" name="contenido[turno]" value="TARDE" 
                                       {{ $turnoActual == 'TARDE' ? 'checked' : '' }} 
                                       class="peer sr-only">
                                <div class="p-3 rounded-xl border-2 border-slate-200 bg-slate-50 text-center transition-all 
                                       peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 
                                       peer-checked:shadow-sm hover:border-indigo-200">
                                    <div class="flex items-center justify-center gap-2">
                                        <i data-lucide="sunset" class="w-4 h-4"></i>
                                        <span class="text-[10px] font-black uppercase tracking-wider">TARDE</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            {{-- DATOS DEL PROFESIONAL --}}
            <x-esp_2_datosProfesional prefix="profesional" :detalle="$detalle" color="teal" />
            
            {{-- DOCUMENTACIÓN ADMINISTRATIVA --}}
            <x-esp_2_1_docAdmin prefix="profesional" :detalle="$detalle" />

            {{-- DETALLE DNI Y FIRMA DIGITAL --}}
            <x-esp_3_detalleDni :detalle="$detalle" color="teal" />

            {{-- CAPACITACION --}}
            <div id="wrapper_capacitacion_externo">
                <x-esp_4_detalleCap :model="json_encode($detalle->contenido ?? [])" />
            </div>
            {{-- EQUIPAMIENTO --}}  
            <x-esp_5_equipos :equipos="$equipos" modulo="gestion_admin_esp" />

            {{-- SOPORTE--}}
            <div id="wrapper_soporte_externo">
                <x-esp_6_soporte :detalle="$detalle" />
            </div>
            {{-- PROGRAMACIÓN SIHCE --}}
            <div id="section_programacion" class="bg-white border border-slate-200 rounded-[2.5rem] overflow-hidden shadow-xl shadow-slate-200/40 transition-all duration-700 mb-10 group/card relative {{ ($detalle->contenido['profesional']['cuenta_sihce'] ?? '') == 'NO' ? 'hidden' : '' }}">
                
                {{-- ENCABEZADO --}}
                <div class="bg-slate-50/50 border-b border-slate-100 px-10 py-6 flex flex-col lg:flex-row justify-between items-center gap-6 transition-all duration-700">
                    <div class="flex items-center gap-5">
                        <div class="h-14 w-14 rounded-2xl bg-white shadow-sm flex items-center justify-center text-teal-600 border border-slate-100 transition-all duration-700">
                            <i data-lucide="calendar-range" class="w-7 h-7"></i>
                        </div>
                        <div>
                            <h3 class="text-teal-900 font-black text-lg uppercase tracking-tight mb-1">PROGRAMACIÓN ACTUAL SIHCE</h3>
                            <p class="text-slate-500 font-bold uppercase text-[10px] tracking-widest">Fecha límite de programación</p>
                        </div>
                    </div>
                </div>

                {{-- CONTENIDO --}}
                <div class="p-10 pl-16">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Seleccione Fecha (Mes y Año)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none z-10">
                            <i data-lucide="calendar-range" class="w-5 h-5 text-teal-500"></i>
                        </div>
                        <input type="month" 
                               id="programacion_sihce"
                               name="contenido[fecha_programacion]" 
                               value="{{ $detalle->contenido['fecha_programacion'] ?? '' }}" 
                               class="w-full pl-16 pr-6 py-4 bg-slate-50 border-2 border-slate-200 rounded-2xl font-bold text-sm uppercase outline-none focus:border-teal-500 focus:bg-white transition-all text-slate-700 cursor-pointer shadow-sm hide-native-calendar-icon">
                    </div>
                    
                    <style>
                        /* Ocultar el icono de calendario nativo del navegador */
                        .hide-native-calendar-icon::-webkit-calendar-picker-indicator {
                            opacity: 0;
                            cursor: pointer;
                            position: absolute;
                            right: 0;
                            width: 100%;
                            height: 100%;
                        }
                    </style>
                </div>
            </div>
            
            {{-- COMENTARIOS Y EVIDENCIA FOTOGRAFICA --}}
            <x-esp_7_comentariosEvid :comentario="(object) ($detalle->contenido ?? [])" />

            {{-- BOTÓN GRANDE DE GUARDADO --}}
            <div class="pt-10 pb-5 mt-6">
                <button type="submit" id="btn-submit-action" 
                        class="w-full group bg-teal-600 text-white p-8 rounded-[3rem] font-black shadow-2xl shadow-teal-200 flex items-center justify-between hover:bg-teal-700 transition-all duration-500 active:scale-[0.98] cursor-pointer">
                    <div class="flex items-center gap-8 pointer-events-none">
                        <div class="h-16 w-16 bg-white/20 rounded-3xl flex items-center justify-center group-hover:rotate-12 transition-all shadow-lg border border-white/30">
                            <i data-lucide="save" id="icon-save-loader" class="w-8 h-8 text-white"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-xl uppercase tracking-[0.3em] leading-none">Confirmar Registro</p>
                            <p class="text-[10px] text-teal-200 font-bold uppercase mt-3 tracking-widest">Sincronizar Módulo Gestión Admin. Especializada</p>
                        </div>
                    </div>
                    <div class="h-14 w-14 bg-white/10 rounded-full flex items-center justify-center group-hover:bg-white group-hover:text-teal-600 transition-all duration-500">
                        <i data-lucide="chevron-right" class="w-7 h-7"></i>
                    </div>
                </button>
            </div>

        </form>
    </div>
</div>
<script>
    // Inicializar iconos al cargar
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar iconos
        if (typeof lucide !== 'undefined') lucide.createIcons();

        // --- LÓGICA EXTERNA PARA CONTROLAR COMPONENTES ---
        // Buscamos el select que está DENTRO del componente 2.1
        // Como usas prefix="profesional", el ID generado automáticamente es "sihce_profesional"
        const selectSihceComponente = document.getElementById('sihce_profesional');
        
        // Referencias a los bloques que acabamos de crear en el Paso 1
        const bloqueCap = document.getElementById('wrapper_capacitacion_externo');
        const bloqueSop = document.getElementById('wrapper_soporte_externo');
        const bloqueProgramacion = document.getElementById('section_programacion');
        const inputProgramacion = document.getElementById('programacion_sihce');

        // Función que aplica la visibilidad
        function aplicarReglasExternas() {
            if (!selectSihceComponente) return;
            
            const valor = selectSihceComponente.value;
            
            if (valor === 'NO') {
                if(bloqueCap) bloqueCap.classList.add('hidden');
                if(bloqueSop) bloqueSop.classList.add('hidden');
                if(bloqueProgramacion) bloqueProgramacion.classList.add('hidden');
                // Limpiar el valor del campo de programación
                if(inputProgramacion) inputProgramacion.value = '';
            } else {
                if(bloqueCap) bloqueCap.classList.remove('hidden');
                if(bloqueSop) bloqueSop.classList.remove('hidden');
                if(bloqueProgramacion) bloqueProgramacion.classList.remove('hidden');
            }
        }

        if (selectSihceComponente) {
            // 1. Ejecutar al cargar la página (para respetar lo guardado en BD)
            aplicarReglasExternas();

            // 2. Agregar un "oído" (listener) al evento change
            selectSihceComponente.addEventListener('change', aplicarReglasExternas);
        }
    });

    // Efecto de carga al enviar el formulario (Bloquea el botón)
    document.getElementById('form-monitoreo-gestion-admin-esp').onsubmit = function() {
        const form = this;
        const btn = document.getElementById('btn-submit-action');
        const icon = document.getElementById('icon-save-loader');
        const wrapper = document.getElementById('wrapper_capacitacion_externo');
        if (wrapper) {
            // A. Obtener valor de "Recibió Capacitación" (SI/NO)
            // Buscamos los radios dentro del componente y vemos cuál está marcado
            const radioSi = wrapper.querySelector('input[type="radio"][value="SI"]');
            const radioNo = wrapper.querySelector('input[type="radio"][value="NO"]');
            
            let valorCap = '';
            if (radioSi && radioSi.checked) valorCap = 'SI';
            if (radioNo && radioNo.checked) valorCap = 'NO';

            // B. Obtener valor de "Institución"
            const selectInst = wrapper.querySelector('select');
            const valorInst = selectInst ? selectInst.value : '';

            // C. Crear inputs ocultos para enviarlos al controlador
            // Los nombres deben coincidir con lo que espera el controlador
            
            const inputCap = document.createElement('input');
            inputCap.type = 'hidden';
            inputCap.name = 'contenido[recibio_capacitacion]';
            inputCap.value = valorCap;
            form.appendChild(inputCap);

            const inputInst = document.createElement('input');
            inputInst.type = 'hidden';
            inputInst.name = 'contenido[inst_que_lo_capacito]';
            inputInst.value = valorInst;
            form.appendChild(inputInst);
        }
        if(btn) {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        }
        
        if(icon) {
            icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
        }
        
        return true;
    };

</script>
@endsection