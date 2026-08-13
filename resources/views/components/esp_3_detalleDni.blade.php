@props(['detalle', 'color' => 'indigo'])

{{-- 1. DEFINICIÓN DE COLORES Y TEMA --}}
@php
    $allThemes = [
        'indigo' => [
            'selected-card'  => 'peer-checked:border-indigo-600 peer-checked:ring-1 peer-checked:ring-indigo-600 peer-checked:shadow-indigo-100', // Borde fuerte, fondo blanco
            'selected-text'  => 'peer-checked:text-indigo-700',
            'icon-bg'        => 'bg-indigo-50 text-indigo-600 border-indigo-200',
            'check-icon'     => 'text-indigo-600',
            'btn-yes-active' => 'peer-checked:bg-indigo-600 peer-checked:border-indigo-600 peer-checked:text-white',
            'btn-yes-hover'  => 'hover:border-indigo-300 hover:bg-indigo-50',
        ],
        'teal' => [
            'selected-card'  => 'peer-checked:border-teal-500 peer-checked:ring-1 peer-checked:ring-teal-500 peer-checked:shadow-teal-100',
            'selected-text'  => 'peer-checked:text-teal-700',
            'icon-bg'        => 'bg-teal-50 text-teal-600 border-teal-200',
            'check-icon'     => 'text-teal-600',
            'btn-yes-active' => 'peer-checked:bg-teal-500 peer-checked:border-teal-500 peer-checked:text-white', // Texto blanco forzado
            'btn-yes-hover'  => 'hover:border-teal-300 hover:bg-teal-50',
        ],
        'blue' => [
            'selected-card'  => 'peer-checked:border-blue-600 peer-checked:ring-1 peer-checked:ring-blue-600 peer-checked:shadow-blue-100',
            'selected-text'  => 'peer-checked:text-blue-700',
            'icon-bg'        => 'bg-blue-50 text-blue-600 border-blue-200',
            'check-icon'     => 'text-blue-600',
            'btn-yes-active' => 'peer-checked:bg-blue-600 peer-checked:border-blue-600 peer-checked:text-white',
            'btn-yes-hover'  => 'hover:border-blue-300 hover:bg-blue-50',
        ],
    ];

    $t = $allThemes[$color] ?? $allThemes['indigo'];
@endphp

<div id="seccion_dni_firma" class="bg-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/40 border border-slate-100 mb-8 transition-all duration-300 hover:shadow-2xl hover:shadow-slate-200/60 {{ data_get($detalle->contenido, 'profesional.tipo_doc', 'DNI') !== 'DNI' && data_get($detalle->contenido, 'profesional.tipo_doc') !== null ? 'hidden' : '' }} seccion-numerada group/dniblock">
    {{-- HEADER --}}
    <div class="flex items-center gap-5 mb-8 border-b border-slate-50 pb-6">
        {{-- ICONO PRINCIPAL --}}
        <div class="h-14 w-14 rounded-2xl bg-white shadow-sm flex items-center justify-center {{ $t['check-icon'] }} border border-slate-100 transition-all duration-700 group-hover/dniblock:scale-105 group-hover/dniblock:shadow-md">
            <i data-lucide="id-card" class="w-7 h-7"></i>
        </div>
        <div>
            <h3 class="text-teal-900 font-black text-lg uppercase tracking-tight mb-1 transition-colors duration-300">
                Detalle DNI y Firma Digital
            </h3>
            <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                Validación de identidad digital
            </p>
        </div>
    </div>
    
    {{-- TIPO DE DOCUMENTO --}}
    <div class="mb-10">
        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-5 ml-2 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19 7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="m2 2 7.5 8.6L13 18l5-5 3 3"/></svg>
            Seleccione el Tipo de Documento
        </label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- OPCIÓN A: DNI ELECTRÓNICO --}}
            <label class="relative cursor-pointer group perspective-1000">
                <input type="radio" name="contenido[tipo_dni_fisico]" value="ELECTRONICO" class="peer sr-only" onchange="toggleDniFields()" {{ data_get($detalle->contenido, "tipo_dni_fisico") == 'ELECTRONICO' ? 'checked' : '' }}>
                
                {{-- DISEÑO CORREGIDO: Fondo blanco siempre, Borde grueso al seleccionar --}}
                <div class="p-5 rounded-[1.5rem] border-2 bg-white transition-all duration-300 relative overflow-hidden border-slate-200 
                            hover:border-slate-300 hover:shadow-md hover:-translate-y-1 
                            {{ $t['selected-card'] }} peer-checked:shadow-lg">
                    
                    <div class="flex items-center gap-5 relative z-10">
                        {{-- Ícono --}}
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 bg-slate-50 text-slate-400 border border-slate-100 
                                    group-hover:bg-white group-hover:text-slate-600 
                                    peer-checked:bg-slate-900 peer-checked:text-white peer-checked:border-transparent peer-checked:shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M8.5 7v10"/><path d="M15.5 7v10"/><path d="M3 8.5h5"/><path d="M3 15.5h5"/><path d="M16 8.5h5"/><path d="M16 15.5h5"/></svg>
                        </div>
                        
                        <div class="flex-1">
                            <span class="block text-sm font-black text-slate-700 uppercase transition-colors {{ $t['selected-text'] }}">DNI Electrónico</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1 block">Con Chip Digital</span>
                        </div>
                        
                        {{-- Checkmark --}}
                        <div class="opacity-0 scale-50 transition-all duration-300 ease-out peer-checked:opacity-100 peer-checked:scale-100 {{ $t['check-icon'] }}">
                            <div class="bg-white rounded-full p-1 shadow-sm border border-slate-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </label>

            {{-- OPCIÓN B: DNI AZUL --}}
            <label class="relative cursor-pointer group perspective-1000">
                <input type="radio" name="contenido[tipo_dni_fisico]" value="AZUL" class="peer sr-only" onchange="toggleDniFields()" {{ data_get($detalle->contenido, "tipo_dni_fisico") == 'AZUL' ? 'checked' : '' }}>
                
                <div class="p-5 rounded-[1.5rem] border-2 bg-white transition-all duration-300 relative overflow-hidden border-slate-200 
                            hover:border-slate-300 hover:shadow-md hover:-translate-y-1 
                            peer-checked:border-blue-600 peer-checked:ring-1 peer-checked:ring-blue-600 peer-checked:shadow-lg peer-checked:shadow-blue-100">
                    
                    <div class="flex items-center gap-5 relative z-10">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center transition-all duration-300 bg-slate-50 text-slate-400 border border-slate-100 
                                    group-hover:bg-white group-hover:text-slate-600 
                                    peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-transparent peer-checked:shadow-md">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="12" x="2" y="6" rx="2"/></svg>
                        </div>
                        <div class="flex-1">
                            <span class="block text-sm font-black text-slate-700 uppercase transition-colors peer-checked:text-blue-700">DNI Azul</span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-1 block">Sin Chip</span>
                        </div>
                        <div class="opacity-0 scale-50 transition-all duration-300 ease-out peer-checked:opacity-100 peer-checked:scale-100 text-blue-600">
                            <div class="bg-white rounded-full p-1 shadow-sm border border-slate-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </label>
        </div>
    </div>

    {{-- BLOQUE CONDICIONAL DNIe --}}
    <div id="block-info-dnie" class="hidden animate-fade-in-down mb-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8 bg-slate-50 rounded-[2rem] border border-slate-200/60 relative overflow-hidden">
            
            {{-- SELECT VERSIÓN --}}
            <div class="relative z-10">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Versión del DNIe</label>
                <div class="relative group/select">
                    <select name="contenido[dnie_version]" class="appearance-none w-full pl-5 pr-12 py-4 bg-white border-2 border-slate-200 rounded-2xl font-bold text-xs outline-none transition-all shadow-sm cursor-pointer focus:border-teal-500 focus:ring-0 text-slate-700 uppercase relative z-10 hover:border-slate-300">
                        <option value="">-- SELECCIONE --</option>
                        <option value="1.0" {{ data_get($detalle->contenido, "dnie_version") == '1.0' ? 'selected' : '' }}>VERSIÓN 1.0</option>
                        <option value="2.0" {{ data_get($detalle->contenido, "dnie_version") == '2.0' ? 'selected' : '' }}>VERSIÓN 2.0</option>
                        <option value="3.0" {{ data_get($detalle->contenido, "dnie_version") == '3.0' ? 'selected' : '' }}>VERSIÓN 3.0</option>
                    </select>
                    <div class="absolute right-5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none z-0 transition-colors group-hover/select:text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </div>
            </div>

            {{-- TOGGLE SÍ/NO (CORREGIDO PARA TEXTO BLANCO) --}}
            <div class="relative z-10">
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">¿Firma Digitalmente en SIHCE?</label>
                <div class="flex gap-4 h-[54px]">
                    
                    {{-- BOTÓN SÍ --}}
                    <label class="flex-1 relative cursor-pointer group">
                        <input type="radio" name="contenido[dnie_firma_sihce]" value="SI" {{ data_get($detalle->contenido, "dnie_firma_sihce") == 'SI' ? 'checked' : '' }} class="peer sr-only">
                        <div class="h-full w-full flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white text-slate-500 transition-all 
                                    {{ $t['btn-yes-hover'] }}
                                    {{ $t['btn-yes-active'] }} peer-checked:shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                            <span class="text-xs font-black uppercase tracking-wider">SÍ, FIRMA</span>
                        </div>
                    </label>

                    {{-- BOTÓN NO --}}
                    <label class="flex-1 relative cursor-pointer group">
                        <input type="radio" name="contenido[dnie_firma_sihce]" value="NO" {{ data_get($detalle->contenido, "dnie_firma_sihce") == 'NO' ? 'checked' : '' }} class="peer sr-only">
                        <div class="h-full w-full flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white text-slate-500 transition-all 
                                    hover:border-red-300 hover:bg-red-50
                                    peer-checked:bg-red-500 peer-checked:border-red-500 peer-checked:text-white peer-checked:shadow-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                            <span class="text-xs font-black uppercase tracking-wider">NO FIRMA</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>

    {{-- OBSERVACIONES --}}
    <div class="relative">
        <label class="block text-[11px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><line x1="9" x2="15" y1="10" y2="10"/><line x1="12" x2="12" y1="7" y2="13"/></svg>
            Observaciones
        </label>
        <div class="relative group/textarea">
            <textarea name="contenido[dni_observacion]" 
                      rows="2"
                      class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-200 rounded-3xl font-bold text-xs outline-none focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-100 text-slate-700 uppercase placeholder:text-slate-400 transition-all resize-none shadow-inner"
                      placeholder="... descripción">{{ data_get($detalle->contenido, "dni_observacion") }}</textarea>
            <div class="absolute bottom-4 right-5 text-slate-300 pointer-events-none transition-colors group-focus-within/textarea:text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof window.toggleDniFields === 'undefined') {
        window.toggleDniFields = function(isInitialLoad = false) {
            const tipoDniInput = document.querySelector(`input[name="contenido[tipo_dni_fisico]"]:checked`);
            const tipoDni = tipoDniInput ? tipoDniInput.value : null;
            const blockDnie = document.getElementById('block-info-dnie');
            const selectVersion = document.querySelector(`select[name="contenido[dnie_version]"]`);
            const radiosFirma = document.querySelectorAll(`input[name="contenido[dnie_firma_sihce]"]`);
            const txtObservacion = document.querySelector(`textarea[name="contenido[dni_observacion]"]`);

            if (!blockDnie) return;

            if (tipoDni === 'ELECTRONICO') {
                blockDnie.classList.remove('hidden');
                if (!isInitialLoad && txtObservacion) txtObservacion.value = '';
            } else if (tipoDni === 'AZUL') {
                blockDnie.classList.add('hidden');
                if (!isInitialLoad) {
                    if (selectVersion) selectVersion.value = ''; 
                    if(radiosFirma) radiosFirma.forEach(r => r.checked = false);
                }
            } else {
                blockDnie.classList.add('hidden');
            }
        };
        window.initDniFirmaVisibility = function() {
            const sectionDni = document.getElementById('seccion_dni_firma');
            const inputTipoDoc = document.getElementById('profesional_tipo_doc') || document.querySelector('[name="contenido[profesional][tipo_doc]"]') || document.querySelector('select[name*="tipo_doc"]');
            function checkTipoDoc() {
                if (!inputTipoDoc || !sectionDni) return;
                const valor = inputTipoDoc.value.toUpperCase().trim();
                sectionDni.classList.toggle('hidden', valor !== 'DNI');
                if (typeof actualizarCorrelativo === 'function') actualizarCorrelativo();
            }
            if (inputTipoDoc) {
                checkTipoDoc();
                inputTipoDoc.addEventListener('change', checkTipoDoc);
                setInterval(checkTipoDoc, 1000); 
            }
        };
    }
    document.addEventListener('DOMContentLoaded', () => {
        if(typeof toggleDniFields === 'function') toggleDniFields(true);
        if(typeof initDniFirmaVisibility === 'function') initDniFirmaVisibility();
    });
</script>