@props(['detalle'])

{{-- 1. DOCUMENTACIÓN ADMINISTRATIVA --}}
<div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100 mb-8">
    <div class="flex items-center gap-4 mb-8">
        <div class="h-10 w-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center shadow-sm">
            <i data-lucide="file-signature" class="w-5 h-5"></i>
        </div>
        <h3 class="text-sm font-black text-slate-700 uppercase tracking-widest">Documentación Administrativa</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        {{-- CAMPO: DECLARACIÓN JURADA --}}
        <div class="flex-1">
            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">¿Firmó Declaración Jurada?</label>
            <div class="flex gap-3">
                <label class="flex-1 relative cursor-pointer group">
                    <input type="radio" name="contenido[firmo_dj]" value="SI" {{ ($detalle->contenido['firmo_dj'] ?? '') == 'SI' ? 'checked' : '' }} class="peer sr-only">
                    <div class="py-3 px-4 rounded-xl border-2 border-slate-100 bg-slate-50 text-center transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 font-black text-[11px] uppercase">SÍ</div>
                </label>
                <label class="flex-1 relative cursor-pointer group">
                    <input type="radio" name="contenido[firmo_dj]" value="NO" {{ ($detalle->contenido['firmo_dj'] ?? '') == 'NO' ? 'checked' : '' }} class="peer sr-only">
                    <div class="py-3 px-4 rounded-xl border-2 border-slate-100 bg-slate-50 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 font-black text-[11px] uppercase">NO</div>
                </label>
            </div>
        </div>

        {{-- CAMPO: COMPROMISO DE CONFIDENCIALIDAD --}}
        <div class="flex-1">
            <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">¿Firmó Compromiso de Confidencialidad?</label>
            <div class="flex gap-3">
                <label class="flex-1 relative cursor-pointer group">
                    <input type="radio" name="contenido[firmo_confidencialidad]" value="SI" {{ ($detalle->contenido['firmo_confidencialidad'] ?? '') == 'SI' ? 'checked' : '' }} class="peer sr-only">
                    <div class="py-3 px-4 rounded-xl border-2 border-slate-100 bg-slate-50 text-center transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 font-black text-[11px] uppercase">SÍ</div>
                </label>
                <label class="flex-1 relative cursor-pointer group">
                    <input type="radio" name="contenido[firmo_confidencialidad]" value="NO" {{ ($detalle->contenido['firmo_confidencialidad'] ?? '') == 'NO' ? 'checked' : '' }} class="peer sr-only">
                    <div class="py-3 px-4 rounded-xl border-2 border-slate-100 bg-slate-50 text-center transition-all peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 font-black text-[11px] uppercase">NO</div>
                </label>
            </div>
        </div>
    </div>
</div>

{{-- 2. TIPO DE DNI Y FIRMA DIGITAL --}}
<div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-100 mb-8">
    <div class="flex items-center gap-4 mb-8">
        <div class="h-12 w-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-xl shadow-inner">3</div>
        <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight">Tipo de DNI y Firma Digital</h3>
    </div>

    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Seleccione el tipo de documento físico</label>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        {{-- DNI ELECTRÓNICO --}}
        <label class="relative cursor-pointer">
            <input type="radio" name="contenido[tipo_dni]" value="ELECTRONICO" onchange="toggleDniOptions('ELECTRONICO')"
                {{ ($detalle->contenido['tipo_dni'] ?? '') == 'ELECTRONICO' ? 'checked' : '' }} class="peer sr-only">
            <div class="flex items-center gap-4 p-6 rounded-[1.5rem] border-2 border-slate-50 bg-slate-50 transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50/30 peer-checked:shadow-md">
                <i data-lucide="credit-card" class="w-6 h-6 text-slate-400 peer-checked:text-indigo-600"></i>
                <div>
                    <p class="font-black text-xs uppercase text-slate-700">DNI Electrónico</p>
                    <p class="text-[9px] font-bold text-slate-400 uppercase">Con Chip</p>
                </div>
            </div>
        </label>

        {{-- DNI AZUL --}}
        <label class="relative cursor-pointer">
            <input type="radio" name="contenido[tipo_dni]" value="AZUL" onchange="toggleDniOptions('AZUL')"
                {{ ($detalle->contenido['tipo_dni'] ?? '') == 'AZUL' ? 'checked' : '' }} class="peer sr-only">
            <div class="flex items-center gap-4 p-6 rounded-[1.5rem] border-2 border-slate-50 bg-slate-50 transition-all peer-checked:border-blue-500 peer-checked:bg-blue-50/30 peer-checked:shadow-md">
                <i data-lucide="user-square-2" class="w-6 h-6 text-slate-400 peer-checked:text-blue-600"></i>
                <div>
                    <p class="font-black text-xs uppercase text-slate-700">DNI Azul</p>
                    <p class="text-[9px] font-bold text-slate-400 uppercase">Sin Chip</p>
                </div>
            </div>
        </label>
    </div>

    {{-- FILA DINÁMICA: VERSIÓN Y FIRMA DIGITAL --}}
    <div id="row_dni_details" class="grid grid-cols-1 md:grid-cols-2 gap-10 items-end animate-in fade-in slide-in-from-top-2 duration-300">
        <div>
            <label class="block text-[9px] font-black text-indigo-500 uppercase tracking-widest mb-3">Versión del DNIe</label>
            <select name="contenido[version_dni]" id="version_dni" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold text-sm outline-none focus:border-indigo-500 uppercase">
                <option value="VERSION 1.0" {{ ($detalle->contenido['version_dni'] ?? '') == 'VERSION 1.0' ? 'selected' : '' }}>Versión 1.0</option>
                <option value="VERSION 2.0" {{ ($detalle->contenido['version_dni'] ?? '') == 'VERSION 2.0' ? 'selected' : '' }}>Versión 2.0</option>
                <option value="VERSION 3.0" {{ ($detalle->contenido['version_dni'] ?? '') == 'VERSION 3.0' ? 'selected' : '' }}>Versión 3.0</option>
            </select>
        </div>

        <div>
            <label class="block text-[9px] font-black text-indigo-500 uppercase tracking-widest mb-3 text-center">¿Firma digitalmente en SIHCE?</label>
            <div class="flex justify-center gap-4">
                <label class="cursor-pointer flex items-center gap-2 px-6 py-3 rounded-xl border-2 border-slate-100 bg-slate-50 hover:bg-white transition-all">
                    <input type="radio" name="contenido[firma_digital]" value="SI" {{ ($detalle->contenido['firma_digital'] ?? '') == 'SI' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs font-black text-slate-700 uppercase">SÍ</span>
                </label>
                <label class="cursor-pointer flex items-center gap-2 px-6 py-3 rounded-xl border-2 border-slate-100 bg-slate-50 hover:bg-white transition-all">
                    <input type="radio" name="contenido[firma_digital]" value="NO" {{ ($detalle->contenido['firma_digital'] ?? '') == 'NO' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-xs font-black text-slate-700 uppercase">NO</span>
                </label>
            </div>
        </div>
    </div>

    <div class="mt-8 pt-8 border-t border-slate-50">
        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Observaciones / Motivo de Uso</label>
        <textarea name="contenido[observaciones_dni]" rows="3" placeholder="INGRESE AQUÍ CUALQUIER OBSERVACIÓN ..." 
            class="w-full bg-slate-50 border-2 border-slate-100 rounded-3xl p-6 text-slate-700 font-bold outline-none focus:border-indigo-500 transition-all uppercase placeholder-slate-300 shadow-inner resize-none">{{ $detalle->contenido['observaciones_dni'] ?? '' }}</textarea>
    </div>
</div>

<script>
    /**
     * Maneja la visibilidad de los detalles del DNI según el tipo seleccionado
     * Definido para ocultar versión y firma digital si el DNI es Azul
     */
    function toggleDniOptions(type) {
        const rowDetails = document.getElementById('row_dni_details');
        const versionSelect = document.getElementById('version_dni');
        const digitalSignRadios = document.querySelectorAll('input[name="contenido[firma_digital]"]');
        
        if (type === 'AZUL') {
            // Resetear valores por coherencia técnica
            versionSelect.value = 'NO APLICA';
            digitalSignRadios.forEach(radio => radio.checked = false);
            
            // Ocultar fila completa para DNI Azul
            rowDetails.style.display = 'none';
        } else {
            // Mostrar fila para DNI Electrónico
            rowDetails.style.display = 'grid';
        }
    }

    // Inicialización al cargar el DOM para respetar estados guardados
    document.addEventListener('DOMContentLoaded', function() {
        const selectedDni = document.querySelector('input[name="contenido[tipo_dni]"]:checked');
        if (selectedDni) {
            toggleDniOptions(selectedDni.value);
        } else {
            // Por defecto ocultar si no hay selección (opcional)
            // toggleDniOptions('AZUL'); 
        }
    });
</script>