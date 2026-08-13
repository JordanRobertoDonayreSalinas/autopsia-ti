@props(['model'])

{{-- 
    Uso: <x-dificultad model="form.dificultades" />
    Encapsula los selectores de problemas de comunicación/coordinación.
--}}

<div x-data="dificultadComponent({{ $model }})" 
     class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">
    
    {{-- Decoración de fondo --}}
    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 opacity-60 pointer-events-none"></div>

    {{-- Encabezado --}}
    <div class="flex items-center gap-4 mb-6 relative z-10">
        <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
            <i data-lucide="alert-circle" class="text-white w-6 h-6"></i>
        </div>
        <div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Soporte</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Reporte de Incidencias</p>
        </div>
    </div>
    
    {{-- Grid de Selectores --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 relative z-10">
        
        {{-- 1. INSTITUCIÓN --}}
        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Institución con la que coordina</label>
            <div class="relative">
                <select x-model="entidad.institucion" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 font-bold uppercase text-sm focus:ring-indigo-500 cursor-pointer">
                    <option value="">Seleccione una entidad...</option>
                    <option value="JEFE DE ESTABLECIMIENTO">JEFE DE ESTABLECIMIENTO</option>
                    <option value="UNIDAD EJECUTORA">UNIDAD EJECUTORA</option>
                    <option value="MINSA">MINSA</option>
                    <option value="DIRESA">DIRESA</option>
                    <option value="OTROS">OTROS</option>
                </select>
            </div>
        </div>

        {{-- 2. MEDIO DE COMUNICACIÓN --}}
        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Medio de comunicación</label>
            <div class="relative">
                <select x-model="entidad.medio" 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 font-bold uppercase text-sm focus:ring-indigo-500 cursor-pointer">
                    <option value="">Seleccione una opción...</option>
                    <option value="CELULAR">CELULAR</option>
                    <option value="CORREO">CORREO</option>
                    <option value="WHATSAPP">WHATSAPP</option>
                    <option value="OTROS">OTROS</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Script del Componente --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('dificultadComponent', (entidadVinculada) => ({
                // Vinculamos por referencia el objeto 'form.dificultades' del padre
                entidad: entidadVinculada,
            }));
        });
    </script>
</div>