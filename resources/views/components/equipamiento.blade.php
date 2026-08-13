@props(['model'])

<div x-data="equipamientoComponent({{ $model }})" 
     class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">
    
    {{-- (El HTML del encabezado sigue igual...) --}}
    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 opacity-60 pointer-events-none"></div>
    <div class="flex items-center gap-4 mb-8 relative z-10">
        <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
            <i data-lucide="monitor" class="text-white w-6 h-6"></i>
        </div>
        <div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">EQUIPAMIENTO DEL CONSULTORIO</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Registro de Activos Fijos</p>
        </div>
    </div>

    {{-- (Barra de Agregar sigue igual...) --}}
    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 mb-6 flex flex-col md:flex-row gap-4 items-end md:items-center relative z-10">
        <div class="flex-1 w-full">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 pl-1">Seleccionar Equipo</label>
            <select x-model="itemSeleccionado" class="w-full bg-white border border-slate-200 rounded-xl p-3 font-bold uppercase text-xs focus:ring-indigo-500 cursor-pointer">
                <option value="">-- SELECCIONE UN ÍTEM --</option>
                <template x-for="opcion in listaOpciones" :key="opcion">
                    <option :value="opcion" x-text="opcion"></option>
                </template>
            </select>
        </div>
        <button type="button" 
                @click="agregarItem()" 
                :disabled="!itemSeleccionado"
                class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-xl font-black uppercase text-xs tracking-widest shadow-md transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            AGREGAR
        </button>
    </div>

    {{-- TABLA --}}
    <div class="overflow-x-auto min-h-[150px] relative z-10">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="text-[10px] text-slate-400 uppercase tracking-widest border-b border-slate-100">
                    <th class="pb-4 pl-2 min-w-[150px]">Descripción</th>
                    <th class="pb-4 w-40">Propiedad</th>
                    <th class="pb-4 w-32">Estado</th>
                    <th class="pb-4 w-40">Nro. Serie / Cód</th>
                    <th class="pb-4 min-w-[150px]">Observación</th>
                    <th class="pb-4 w-10"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                {{-- Mensaje vacío --}}
                <tr x-show="items.length === 0">
                    <td colspan="6" class="py-8 text-center text-slate-300">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <i data-lucide="inbox" class="w-8 h-8"></i>
                            <span class="text-xs font-bold uppercase tracking-widest">Lista vacía</span>
                        </div>
                    </td>
                </tr>

                {{-- Filas dinámicas --}}
                <template x-for="(item, index) in items" :key="item.id">
                    <tr class="group hover:bg-slate-50/50 transition-colors">
                        {{-- Descripción --}}
                        <td class="py-3 pl-2 align-middle">
                            <span class="font-black text-slate-700 text-xs uppercase bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg" x-text="item.descripcion"></span>
                        </td>
                        
                        {{-- Propiedad --}}
                        <td class="py-3 px-2 align-middle">
                            <select x-model="item.propiedad" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-[10px] font-bold uppercase focus:ring-indigo-500">
                                <option value="COMPARTIDO">COMPARTIDO</option>
                                <option value="EXCLUSIVO">EXCLUSIVO</option>
                                <option value="PERSONAL">PERSONAL</option>
                            </select>
                        </td>

                        {{-- Estado --}}
                        <td class="py-3 px-2 align-middle">
                            <select x-model="item.estado" class="w-full bg-white border border-slate-200 rounded-lg p-2 text-[10px] font-bold uppercase focus:ring-indigo-500">
                                <option value="OPERATIVO">OPERATIVO</option>
                                <option value="REGULAR">REGULAR</option>
                                <option value="INOPERATIVO">INOPERATIVO</option>
                            </select>
                        </td>

                        {{-- Nro Serie / Codigo (CORREGIDO PARA MOSTRAR SIEMPRE S o CP) --}}
                        <td class="py-3 px-2 align-middle">
                            <div class="flex items-center group/input">
                                <div class="relative">
                                    <select x-model="item.tipo_codigo" 
                                            class="appearance-none bg-slate-100 border border-slate-200 border-r-0 rounded-l-lg py-2 pl-3 pr-6 text-[10px] font-black text-slate-600 uppercase focus:ring-0 focus:border-indigo-500 cursor-pointer hover:bg-slate-200 transition-colors w-[60px]">
                                        <option value="S">S</option>
                                        <option value="CP">CP</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-1 text-slate-500">
                                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                                    </div>
                                </div>
                                <div class="relative flex-1">
                                    <input type="text" x-model="item.codigo" placeholder="---" 
                                        class="w-full bg-white border border-slate-200 rounded-r-lg py-2 px-3 text-[10px] font-bold uppercase focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 relative">
                                </div>
                            </div>
                        </td>

                        {{-- Observación --}}
                        <td class="py-3 px-2 align-middle">
                            <input type="text" x-model="item.observacion" placeholder="Sin obs."
                                   class="w-full bg-white border border-slate-200 rounded-lg p-2 text-[10px] font-medium uppercase focus:ring-indigo-500">
                        </td>

                        {{-- Eliminar --}}
                        <td class="py-3 pr-2 align-middle text-right">
                            <button type="button" @click="eliminarItem(index)" class="text-red-500 bg-red-50 hover:bg-red-100 hover:text-red-700 border border-red-200 transition-all p-2 rounded-lg shadow-sm" title="Quitar ítem">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Script del Componente --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('equipamientoComponent', (inventarioRef) => ({
                items: inventarioRef,
                itemSeleccionado: '',
                listaOpciones: [
                    'MONITOR', 'CPU', 'TECLADO', 'MOUSE', 'IMPRESORA', 
                    'LECTORA DE DNIe', 'TICKETERA', 'STABILIZADOR', 
                    'TABLET', 'LAPTOP'
                ],

                agregarItem() {
                    if (!this.itemSeleccionado) return;
                    
                    this.items.push({
                        id: Date.now() + Math.random(),
                        descripcion: this.itemSeleccionado,
                        propiedad: 'COMPARTIDO', // Agregado valor por defecto
                        estado: 'OPERATIVO',     // Agregado valor por defecto
                        tipo_codigo: 'S',        // CORREGIDO: Inicializar en 'S' en vez de vacío
                        codigo: '', 
                        observacion: ''
                    });
                    
                    this.itemSeleccionado = ''; 
                },

                eliminarItem(index) {
                    this.items.splice(index, 1);
                }
            }));
        });
    </script>
</div>