@props(['model', 'tipo'])

{{-- 
    Uso: <x-materiales model="form.inicio_labores" tipo="odontologia" />
    Contiene: FUA, Referencia, Receta, Laboratorio (Condicional)
--}}

<div x-data="materialesComponent({{ $model }})" 
     class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">
    
    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 opacity-60 pointer-events-none"></div>
    
    <div class="flex items-center gap-4 mb-8 relative z-10">
        <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
            <i data-lucide="package-search" class="text-white w-6 h-6"></i>
        </div>
        <div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Materiales</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Verificación de insumos</p>
        </div>
    </div>

    <div class="space-y-8 relative z-10">
        <div class="pt-2">
            <label class="block text-sm font-bold text-slate-800 mb-6 uppercase tracking-tight border-b border-slate-100 pb-2">Al iniciar cuenta con:</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- FUA (Común) --}}
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:shadow-md transition-shadow">
                    <span class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">FUA</span>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" value="ELECTRONICA" x-model="entidad.fua" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors uppercase">FUA Electrónica</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" value="MANUAL" x-model="entidad.fua" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors uppercase">FUA Manual</span>
                        </label>
                    </div>
                </div>

                {{-- REFERENCIA (Común) --}}
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:shadow-md transition-shadow">
                    <span class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Referencia</span>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" value="SIHCE" x-model="entidad.referencia" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors uppercase">Por SIHCE</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" value="DIRECTO REFCON" x-model="entidad.referencia" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                            <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors uppercase">Directo a REFCON</span>
                        </label>
                    </div>
                </div>

                {{-- CAMPOS EXCLUSIVOS DE ODONTOLOGÍA --}}
                @if($tipo === 'odontologia')
                    {{-- Receta --}}
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:shadow-md transition-shadow">
                        <span class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Receta</span>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" value="SIHCE" x-model="entidad.receta" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors uppercase">Por SIHCE</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" value="MANUAL" x-model="entidad.receta" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors uppercase">Manual</span>
                            </label>
                        </div>
                    </div>

                    {{-- Orden Laboratorio --}}
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:shadow-md transition-shadow">
                        <span class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Orden Laboratorio</span>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" value="SIHCE" x-model="entidad.orden_lab" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors uppercase">Por SIHCE</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" value="MANUAL" x-model="entidad.orden_lab" class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 border-slate-300">
                                <span class="text-xs font-bold text-slate-600 group-hover:text-indigo-600 transition-colors uppercase">Manual</span>
                            </label>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('materialesComponent', (entidadVinculada) => ({
                entidad: entidadVinculada
            }));
        });
    </script>
</div>