@props(['model'])

{{-- 
    Uso: <x-capacitacion model="form.capacitacion" />
    (Versión reducida: Las firmas se movieron a seleccion-profesional)
--}}

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

    $t = $allThemes['teal'];
@endphp

<div x-data="capacitacionComponent({{ $model }})" 
     class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">
    
    <div class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-full -mr-12 -mt-12 opacity-60 pointer-events-none"></div>
    
    <div class="relative z-10">
        {{-- HEADER --}}
        <div class="flex items-center gap-5 mb-8 border-b border-slate-50 pb-6">
            {{-- ICONO PRINCIPAL --}}
            <div class="h-14 w-14 rounded-2xl bg-white shadow-sm flex items-center justify-center {{ $t['check-icon'] }} border border-slate-100 transition-all duration-700 group-hover/dniblock:scale-105 group-hover/dniblock:shadow-md">
                <i data-lucide="graduation-cap" class="w-7 h-7"></i>
            </div>
            <div>
                <h3 class="text-teal-900 font-black text-lg uppercase tracking-tight mb-1 transition-colors duration-300">
                    Detalle de Capacitación
                </h3>
                <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                    Formación Profesional
                </p>
            </div>
        </div>

        <div class="space-y-4">
            {{-- Pregunta Principal --}}
            <label class="block text-sm font-bold text-slate-700">¿El personal ha recibido capacitación?</label>
            <div class="flex gap-4">
                <label class="cursor-pointer flex-1">
                    <input type="radio" value="SI" x-model="entidad.recibieron_cap" class="peer sr-only">
                    <div class="text-center py-3 rounded-xl border-2 border-slate-100 bg-slate-50 text-slate-400 font-bold text-xs uppercase transition-all peer-checked:bg-teal-50 peer-checked:text-teal-600 peer-checked:border-teal-500 hover:bg-white shadow-sm">SI</div>
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
                <select x-model="entidad.institucion_cap" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 font-bold uppercase text-sm focus:ring-teal-500 cursor-pointer">
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
