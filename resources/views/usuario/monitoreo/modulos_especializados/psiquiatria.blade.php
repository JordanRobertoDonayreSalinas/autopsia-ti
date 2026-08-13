@extends('layouts.usuario')

@section('title', 'Módulo: Psiquiatria Especializada')

@section('content')
<div class="py-12 bg-slate-50 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        {{-- ENCABEZADO SUPERIOR --}}
        <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="px-3 py-1 bg-teal-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">Módulo Especializado</span>
                    <span class="text-slate-400 font-bold text-[10px] uppercase">ID Acta: #{{ str_pad($monitoreo->numero_acta ?? $monitoreo->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight">4.2 Psiquiatria</h2>
                <p class="text-slate-500 font-bold uppercase text-xs mt-1">
                    <i data-lucide="hospital" class="inline-block w-4 h-4 mr-1 text-teal-500"></i>{{ $monitoreo->establecimiento->nombre }}
                </p>
            </div>
            <a href="{{ route('usuario.monitoreo.salud_mental_group.index', $monitoreo->id) }}" class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-black text-xs hover:bg-slate-50 transition-all uppercase shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver a Salud Mental
            </a>
        </div>

        {{-- FORMULARIO --}}
        <form action="{{ route('usuario.monitoreo.sm_psiquiatria.store', $monitoreo->id) }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="space-y-6" 
              id="form-monitoreo-psiquiatria-esp">
            @csrf
            
            {{-- DETALLES DEL AMBIENTE --}}
            <x-esp_1_detalleDeConsultorio :detalle="$detalle" />

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
            <x-esp_5_equipos :equipos="$equipos" modulo="sm_psiquiatria" />

            {{-- SOPORTE--}}
            <div id="wrapper_soporte_externo">
                <x-esp_6_soporte :detalle="$detalle" />
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
                            <p class="text-[10px] text-teal-200 font-bold uppercase mt-3 tracking-widest">Sincronizar Módulo psiquiatria Especializada</p>
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

        // Función que aplica la visibilidad
        function aplicarReglasExternas() {
            if (!selectSihceComponente) return;
            
            const valor = selectSihceComponente.value;
            
            if (valor === 'NO') {
                if(bloqueCap) bloqueCap.classList.add('hidden');
                if(bloqueSop) bloqueSop.classList.add('hidden');
            } else {
                if(bloqueCap) bloqueCap.classList.remove('hidden');
                if(bloqueSop) bloqueSop.classList.remove('hidden');
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
    document.getElementById('form-monitoreo-psiquiatria-esp').onsubmit = function() {
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
            // Usamos los nombres que tu controlador espera: capacitacion[recibieron_cap]
            
            const inputCap = document.createElement('input');
            inputCap.type = 'hidden';
            inputCap.name = 'capacitacion[recibieron_cap]';
            inputCap.value = valorCap;
            form.appendChild(inputCap);

            const inputInst = document.createElement('input');
            inputInst.type = 'hidden';
            inputInst.name = 'capacitacion[institucion_cap]';
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
