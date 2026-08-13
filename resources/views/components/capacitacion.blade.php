@props(['model'])

{{-- 
    Uso: <x-capacitacion model="form.capacitacion" />
    (Versión reducida: Las firmas se movieron a seleccion-profesional)
--}}

<div x-data="capacitacionComponent({{ $model }})" 
     class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">
    
    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 opacity-60 pointer-events-none"></div>
    
    <div class="relative z-10">
        <div class="flex items-center gap-4 mb-6">
            <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                <i data-lucide="graduation-cap" class="text-white w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">DETALLES DE CAPACITACION</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Formación Profesional</p>
            </div>
        </div>

        <div class="space-y-4">
            {{-- Pregunta Principal --}}
            <label class="block text-sm font-bold text-slate-700">¿El personal ha recibido capacitación?</label>
            <div class="flex gap-4">
                <label class="cursor-pointer flex-1">
                    <input type="radio" value="SI" x-model="entidad.recibieron_cap" class="peer sr-only">
                    <div class="text-center py-3 rounded-xl border-2 border-slate-100 bg-slate-50 text-slate-400 font-bold text-xs uppercase transition-all peer-checked:bg-indigo-50 peer-checked:text-indigo-600 peer-checked:border-indigo-500 hover:bg-white shadow-sm">SI</div>
                </label>
                <label class="cursor-pointer flex-1">
                    <input type="radio" value="NO" x-model="entidad.recibieron_cap" class="peer sr-only">
                    <div class="text-center py-3 rounded-xl border-2 border-slate-100 bg-slate-50 text-slate-400 font-bold text-xs uppercase transition-all peer-checked:bg-slate-100 peer-checked:text-slate-600 peer-checked:border-slate-300 hover:bg-white shadow-sm">NO</div>
                </label>
            </div>

            {{-- Selector Condicional --}}
            <div x-show="entidad.recibieron_cap === 'SI'" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-4">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Entidad que capacitó</label>
                <select x-model="entidad.institucion_cap" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 font-bold uppercase text-sm focus:ring-indigo-500 cursor-pointer">
                    <option value="" disabled>Seleccione entidad...</option>
                    <option value="JEFE DE ESTABLECIMIENTO">JEFE DE ESTABLECIMIENTO</option>
                    <option value="PERSONAL DEL ESTABLECIMIENTO">PERSONAL DEL ESTABLECIMIENTO</option>
                    <option value="UNIDAD EJECUTORA">UNIDAD EJECUTORA</option>
                    <option value="MINSA">MINSA</option>
                    <option value="DIRESA">DIRESA</option>
                    <option value="OTROS">OTROS</option>
                </select>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('capacitacionComponent', (entidadVinculada) => ({
                entidad: entidadVinculada,
            }));
        });
    </script>
</div>