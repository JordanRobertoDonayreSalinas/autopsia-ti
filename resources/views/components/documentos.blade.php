@props(['model', 'tipo' => 'triaje'])

{{-- 
    Uso: <x-documentos model="form.inicio_labores" tipo="odontologia" />
    Contiene: Fecha, Cantidad Consultorios, Nombre y Turno.
--}}

@php
    $placeholder = match($tipo) {
        'odontologia' => 'EJ: ODONTOLOGÍA 01',
        'psicologia'  => 'EJ: PSICOLOGÍA 01',
        default       => 'EJ: CONSULTORIO 01'
    };
@endphp

<div x-data="documentosComponent({{ $model }})" 
     class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">
    
    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 opacity-60 pointer-events-none"></div>
    
    <div class="flex items-center gap-4 mb-8 relative z-10">
        <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
            <i data-lucide="clipboard-list" class="text-white w-6 h-6"></i>
        </div>
        <div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">DETALLES DEL CONSULTORIO</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Configuración Inicial</p>
        </div>
    </div>

    <div class="space-y-8 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            {{-- 1. Fecha de Monitoreo --}}
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Fecha de Monitoreo</label>
                <div class="relative">
                    <input type="date" 
                           x-model="entidad.fecha_registro" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 pl-4 font-bold text-sm text-slate-600 focus:ring-indigo-500 uppercase cursor-pointer">
                </div>
            </div>

            {{-- 2. Cantidad Consultorios --}}
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Cantidad de Consultorios</label>
                <div class="relative">
                    <input type="number" 
                           min="0" 
                           x-model="entidad.consultorios" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 pl-4 font-bold text-sm focus:ring-indigo-500">
                </div>
            </div>

            {{-- 3. Nombre del Consultorio --}}
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombre del Consultorio</label>
                <div class="relative">
                    <input type="text" 
                           placeholder="{{ $placeholder }}"
                           x-model="entidad.nombre_consultorio" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 pl-4 font-bold text-sm focus:ring-indigo-500 uppercase">
                </div>
            </div>

            {{-- 4. Turno --}}
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Turno</label>
                <div class="relative">
                    <select x-model="entidad.turno" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 pl-4 font-bold text-sm focus:ring-indigo-500 uppercase cursor-pointer">
                        <option value="" disabled>Seleccione...</option>
                        <option value="MAÑANA">MAÑANA</option>
                        <option value="TARDE">TARDE</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('documentosComponent', (entidadVinculada) => ({
                entidad: entidadVinculada,
                init() {
                    if (!this.entidad.fecha_registro) {
                        this.entidad.fecha_registro = new Date().toISOString().split('T')[0];
                    }
                }
            }));
        });
    </script>
</div>