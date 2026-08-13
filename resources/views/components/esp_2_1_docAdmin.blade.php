@props(['prefix' => 'rrhh', 'detalle'])

@php
    // Recuperación de datos
    $sihce = data_get($detalle->contenido, "$prefix.cuenta_sihce", '');
    $firmoDj = data_get($detalle->contenido, "$prefix.firmo_dj", '');
    $firmoConf = data_get($detalle->contenido, "$prefix.firmo_confidencialidad", '');
@endphp

<div id="card_admin_{{$prefix}}" class="bg-white border border-slate-200 rounded-[3rem] overflow-hidden shadow-xl shadow-slate-200/40 transition-all duration-700 mb-10 group/card relative">
    
    {{-- BARRA LATERAL --}}
    <div class="absolute left-0 top-0 w-2 h-full bg-slate-100 transition-colors duration-700"></div>

    {{-- HEADER --}}
    <div class="bg-slate-50/50 border-b border-slate-100 px-10 py-6 flex flex-col lg:flex-row justify-between items-center gap-6">
        <div class="flex items-center gap-5">
            {{-- ICONO --}}
            <div class="h-14 w-14 rounded-2xl bg-white shadow-sm flex items-center justify-center text-teal-600 border border-slate-100 transition-all duration-700">
                <i data-lucide="file-signature" class="w-7 h-7"></i>
            </div>
            <div>
                {{-- TÍTULO --}}
                <h3 class="text-teal-900 font-black text-lg uppercase tracking-tight mb-1">
                    DOCUMENTACIÓN ADMINISTRATIVA
                </h3>
                {{-- SUBTÍTULO --}}
                <p class="text-slate-400 font-bold uppercase text-[10px] tracking-widest">
                    Accesos y Declaraciones Juradas
                </p>
            </div>
        </div>
    </div>

    {{-- BODY --}}
    <div class="p-10 pl-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            {{-- 1. Utiliza SIHCE --}}
            <div>
                <label class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-3 ml-1">
                    ¿Utiliza SIHCE?
                </label>
                <div class="relative">
                    {{-- Se eliminó la opción vacía. Si es un registro nuevo, se seleccionará "SI" por defecto --}}
                    <select name="contenido[{{$prefix}}][cuenta_sihce]" 
                            id="sihce_{{$prefix}}" 
                            onchange="toggleDocAdmin(this.value, '{{$prefix}}')" 
                            class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold text-sm uppercase outline-none text-slate-700 cursor-pointer focus:border-teal-500 focus:bg-white transition-all shadow-sm appearance-none">
                        <option value="SI" {{ $sihce == 'SI' ? 'selected' : '' }}>SI</option>
                        <option value="NO" {{ $sihce == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                    <i data-lucide="chevrons-up-down" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none"></i>
                </div>
            </div>

            {{-- 2. Declaración Jurada --}}
            <div id="div_firmo_dj_{{$prefix}}" class="{{ $sihce == 'NO' ? 'hidden' : '' }}">
                <label class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-3 ml-1">
                    ¿Firmó Declaración Jurada?
                </label>
                <div class="relative group">
                    <select name="contenido[{{$prefix}}][firmo_dj]" 
                            id="dj_{{$prefix}}" 
                            class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold text-sm uppercase outline-none focus:border-teal-500 focus:bg-white text-slate-600 appearance-none shadow-sm transition-all group-hover:border-slate-200">
                        <option value="SI" {{ $firmoDj == 'SI' ? 'selected' : '' }}>SI</option>
                        <option value="NO" {{ $firmoDj == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                    <i data-lucide="file-check" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none group-focus-within:text-teal-500"></i>
                </div>
            </div>

            {{-- 3. Confidencialidad --}}
            <div id="div_firmo_conf_{{$prefix}}" class="{{ $sihce == 'NO' ? 'hidden' : '' }}">
                <label class="block text-slate-400 text-[10px] font-black uppercase tracking-widest mb-3 ml-1">
                    ¿Firmó Confidencialidad?
                </label>
                <div class="relative group">
                    <select name="contenido[{{$prefix}}][firmo_confidencialidad]" 
                            id="conf_{{$prefix}}" 
                            class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-bold text-sm uppercase outline-none focus:border-teal-500 focus:bg-white text-slate-600 appearance-none shadow-sm transition-all group-hover:border-slate-200">
                        <option value="SI" {{ $firmoConf == 'SI' ? 'selected' : '' }}>SI</option>
                        <option value="NO" {{ $firmoConf == 'NO' ? 'selected' : '' }}>NO</option>
                    </select>
                    <i data-lucide="shield" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-300 pointer-events-none group-focus-within:text-teal-500"></i>
                </div>
            </div>

        </div>
    </div>
</div>

@once
<script>
    /**
     * Muestra u Oculta (display:none) los campos dependiendo de si usa SIHCE
     */
    function toggleDocAdmin(valor, prefix) {
        const divDj = document.getElementById('div_firmo_dj_' + prefix);
        const divConf = document.getElementById('div_firmo_conf_' + prefix);
        const selDj = document.getElementById('dj_' + prefix);
        const selConf = document.getElementById('conf_' + prefix);

        if (valor === 'NO') {
            divDj.classList.add('hidden');
            divConf.classList.add('hidden');
            
            // Asignar valor NO para consistencia en BD
            selDj.value = 'NO'; 
            selConf.value = 'NO'; 
        } else {
            divDj.classList.remove('hidden');
            divConf.classList.remove('hidden');
            
            // Opcional: Si quieres que al volver a SI se reinicie a SI (o lo dejas como estaba)
            // if(selDj.value === 'NO') selDj.value = 'SI';
            // if(selConf.value === 'NO') selConf.value = 'SI';
        }
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endonce