@props(['model'])

{{-- 
    Uso: <x-dni model="form.seccion_dni" /> 
    Encapsula la lógica de selección de tipo de DNI y firma digital
--}}

{{-- CAMBIO 1: Renombramos el x-data a 'dniComponent' --}}
<div x-data="dniComponent({{ $model }})" 
     class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">
    
    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 opacity-60 pointer-events-none"></div>
    
    <div class="flex items-center gap-4 mb-8">
        <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
            <i data-lucide="id-card" class="text-white w-6 h-6"></i>
        </div>
        <div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">DETALLE DE DNI Y FIRMA DIGITAL</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Configuración de Identidad Digital</p>
        </div>
    </div>

    <div class="space-y-8">
        {{-- 1. TIPO DE DNI --}}
        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tipo de DNI</label>
            <div class="flex flex-col md:flex-row gap-4">
                <label class="cursor-pointer flex-1 relative">
                    <input type="radio" value="ELECTRONICO" x-model="entidad.tipo_dni" class="peer sr-only">
                    <div class="text-center py-3 rounded-xl border-2 border-slate-100 bg-slate-50 text-slate-400 font-bold text-xs uppercase transition-all peer-checked:bg-indigo-50 peer-checked:text-indigo-600 peer-checked:border-indigo-500 hover:bg-white shadow-sm hover:shadow-md">
                        ELECTRONICO
                    </div>
                </label>
                <label class="cursor-pointer flex-1 relative">
                    <input type="radio" value="AZUL" x-model="entidad.tipo_dni" class="peer sr-only">
                    <div class="text-center py-3 rounded-xl border-2 border-slate-100 bg-slate-50 text-slate-400 font-bold text-xs uppercase transition-all peer-checked:bg-indigo-50 peer-checked:text-indigo-600 peer-checked:border-indigo-500 hover:bg-white shadow-sm hover:shadow-md">
                        AZUL
                    </div>
                </label>
            </div>
        </div>

        {{-- 2. BLOQUE CONDICIONAL (DNIe) --}}
        <div x-show="entidad.tipo_dni === 'ELECTRONICO'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-slate-50/50 border border-slate-100 rounded-2xl p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Versión DNIe</label>
                <select x-model="entidad.version_dnie" class="w-full bg-white border border-slate-200 rounded-xl p-3 font-bold uppercase text-sm focus:ring-indigo-500 cursor-pointer">
                    <option value="" selected disabled>Seleccione Versión...</option>
                    <option value="1.0">1.0</option>
                    <option value="2.0">2.0</option>
                    <option value="3.0">3.0</option>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">¿Realiza firma electrónica en SIHCE?</label>
                <div class="flex gap-4">
                    <label class="cursor-pointer flex-1">
                        <input type="radio" value="SI" x-model="entidad.firma_sihce" class="peer sr-only">
                        <div class="text-center py-3 rounded-xl border-2 border-slate-200 bg-white text-slate-400 font-bold text-xs uppercase transition-all peer-checked:bg-indigo-50 peer-checked:text-indigo-600 peer-checked:border-indigo-500">SÍ</div>
                    </label>
                    <label class="cursor-pointer flex-1">
                        <input type="radio" value="NO" x-model="entidad.firma_sihce" class="peer sr-only">
                        <div class="text-center py-3 rounded-xl border-2 border-slate-200 bg-white text-slate-400 font-bold text-xs uppercase transition-all peer-checked:bg-slate-100 peer-checked:text-slate-600 peer-checked:border-slate-300">NO</div>
                    </label>
                </div>
            </div>
        </div>

        {{-- 3. COMENTARIOS --}}
        <div>
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Comentarios / Observaciones</label>
            <textarea x-model="entidad.comentarios" rows="2" placeholder="Ingrese observaciones generales del acta..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 font-medium text-sm focus:ring-indigo-500"></textarea>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            // CAMBIO 2: Registramos el componente con nombre único 'dniComponent'
            Alpine.data('dniComponent', (entidadVinculada) => ({
                entidad: entidadVinculada,
                
                init() {
                    this.$watch('entidad.tipo_dni', (valor) => {
                        if (valor === 'DNI_AZUL') {
                            this.entidad.version_dnie = null;
                            this.entidad.firma_sihce = null;
                        }
                    });
                }
            }));
        });
    </script>
</div>