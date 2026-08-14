@props(['model', 'capacitacion'])

<div x-data="seleccionProfesional({{ $model }}, {{ $capacitacion }})" 
     x-init="initComponent()"
     class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">
    
    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-full -mr-16 -mt-16 opacity-60 pointer-events-none"></div>
    
    <div class="relative z-10">
        {{-- Encabezado --}}
        <div class="flex items-center gap-4 mb-8">
            <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                <i data-lucide="user-cog" class="text-white w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Datos del Profesional</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Responsable</p>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Fila 1: Tipo Doc y Buscador --}}
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <div class="md:col-span-4">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Tipo Doc.</label>
                    <select x-model="entidad.tipo_doc" class="w-full bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl font-bold uppercase p-3">
                        <option value="DNI">DNI</option>
                        <option value="CE">C.E.</option>
                    </select>
                </div>
                <div class="md:col-span-8 relative">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Número Documento</label>
                    <div class="flex gap-2">
                        <input type="text" x-model="entidad.doc" @keydown.enter.prevent="buscar()" placeholder="Ingrese Documento..." 
                               class="flex-1 bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl p-3 font-bold tracking-wider">
                        <button type="button" @click="buscar()" :disabled="buscando" class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl px-4 py-2 transition-colors">
                            <i x-show="!buscando" data-lucide="search" class="w-5 h-5"></i>
                            <i x-show="buscando" data-lucide="loader-2" class="w-5 h-5 animate-spin"></i>
                        </button>
                    </div>
                    <span x-show="msgEstado" x-text="msgEstado" :class="encontrado ? 'text-emerald-500' : 'text-red-400'" class="absolute -bottom-5 left-0 text-[10px] font-bold"></span>
                </div>
            </div>

            {{-- Fila 2: Nombres --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Apellido Paterno</label>
                    <input type="text" x-model="entidad.apellido_paterno" class="w-full bg-white border border-slate-200 rounded-xl p-3 font-bold uppercase">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Apellido Materno</label>
                    <input type="text" x-model="entidad.apellido_materno" class="w-full bg-white border border-slate-200 rounded-xl p-3 font-bold uppercase">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nombres</label>
                    <input type="text" x-model="entidad.nombres" class="w-full bg-white border border-slate-200 rounded-xl p-3 font-bold uppercase">
                </div>
            </div>
                
            {{-- Fila 3: Datos de Contacto y Cargo --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Profesión / Cargo</label>
                    <div class="flex flex-col gap-2">
                        <select x-model="cargoSelect" @change="actualizarCargoFinal()" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 font-bold uppercase text-xs focus:ring-indigo-500 cursor-pointer">
                            <option value="" disabled>SELECCIONE...</option>
                            <template x-for="prof in listaProfesiones" :key="prof">
                                <option :value="prof" x-text="prof"></option>
                            </template>
                            <option value="OTROS">OTROS</option>
                        </select>
                        <div x-show="cargoSelect === 'OTROS'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                            <input type="text" x-model="cargoManual" @input="actualizarCargoFinal()" placeholder="Especifique cargo..." class="w-full bg-white border border-indigo-300 text-indigo-700 rounded-xl p-3 font-bold uppercase text-xs focus:ring-indigo-500 shadow-sm">
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Email</label>
                    <input type="email" x-model="entidad.email" class="w-full bg-white border border-slate-200 rounded-xl p-3 font-medium lowercase">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Teléfono</label>
                    <input type="text" x-model="entidad.telefono" class="w-full bg-white border border-slate-200 rounded-xl p-3 font-bold">
                </div>
            </div>

            {{-- Fila 4: UTILIZA SIHCE --}}
            <div class="mt-2 pt-4 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">¿El profesional utiliza SIHCE?</label>
                    <div class="flex gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" value="SI" x-model="entidad.utiliza_sihce" class="peer sr-only">
                            <div class="px-6 py-2 rounded-xl border-2 border-slate-200 text-slate-400 font-bold text-xs peer-checked:bg-indigo-50 peer-checked:text-indigo-600 peer-checked:border-indigo-500 transition-all hover:bg-slate-50">SÍ</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" value="NO" x-model="entidad.utiliza_sihce" class="peer sr-only">
                            <div class="px-6 py-2 rounded-xl border-2 border-slate-200 text-slate-400 font-bold text-xs peer-checked:bg-slate-100 peer-checked:text-slate-600 peer-checked:border-slate-400 transition-all hover:bg-slate-50">NO</div>
                        </label>
                    </div>
                </div>


            </div>

            {{-- BLOQUE MOVIDO: FIRMAS (Solo si SIHCE = SI) --}}
            <div x-show="entidad.utiliza_sihce === 'SI'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mt-4 pt-4 border-t border-dashed border-slate-200">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Declaración Jurada (ESTILO NEUTRO RESTAURADO) --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">¿Firmó Declaración Jurada?</label>
                        <div class="flex gap-4">
                            <label class="cursor-pointer flex-1">
                                <input type="radio" value="SI" x-model="cap.decl_jurada" class="peer sr-only">
                                {{-- Estilo índigo/azul para el SÍ --}}
                                <div class="text-center py-2 rounded-lg border-2 border-slate-200 bg-white text-slate-500 font-bold text-[10px] peer-checked:bg-indigo-50 peer-checked:text-indigo-600 peer-checked:border-indigo-500 hover:bg-slate-50 transition-all">SÍ</div>
                            </label>
                            <label class="cursor-pointer flex-1">
                                <input type="radio" value="NO" x-model="cap.decl_jurada" class="peer sr-only">
                                {{-- Estilo gris para el NO --}}
                                <div class="text-center py-2 rounded-lg border-2 border-slate-200 bg-white text-slate-500 font-bold text-[10px] peer-checked:bg-slate-100 peer-checked:text-slate-600 peer-checked:border-slate-400 hover:bg-slate-50 transition-all">NO</div>
                            </label>
                        </div>
                    </div>

                    {{-- Confidencialidad (ESTILO NEUTRO RESTAURADO) --}}
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">¿Firmó Confidencialidad?</label>
                        <div class="flex gap-4">
                            <label class="cursor-pointer flex-1">
                                <input type="radio" value="SI" x-model="cap.comp_confidencialidad" class="peer sr-only">
                                <div class="text-center py-2 rounded-lg border-2 border-slate-200 bg-white text-slate-500 font-bold text-[10px] peer-checked:bg-indigo-50 peer-checked:text-indigo-600 peer-checked:border-indigo-500 hover:bg-slate-50 transition-all">SÍ</div>
                            </label>
                            <label class="cursor-pointer flex-1">
                                <input type="radio" value="NO" x-model="cap.comp_confidencialidad" class="peer sr-only">
                                <div class="text-center py-2 rounded-lg border-2 border-slate-200 bg-white text-slate-500 font-bold text-[10px] peer-checked:bg-slate-100 peer-checked:text-slate-600 peer-checked:border-slate-400 hover:bg-slate-50 transition-all">NO</div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('seleccionProfesional', (profesionalData, capacitacionData) => ({
                entidad: profesionalData,
                cap: capacitacionData,
                
                buscando: false,
                msgEstado: '',
                encontrado: false,
                listaProfesiones: ['MEDICO', 'ODONTOLOGO(A)', 'ENFERMERO(A)', 'TECNICO ENFERMERIA', 'TECNICO LABORATORIO', 'BIOLOGO(A)', 'QUIMICO FARMACEUTICO(A)', 'NUTRICIONISTA', 'PSICOLOGO(A)', 'OBSTETRA'],
                cargoSelect: '', 
                cargoManual: '',

                initComponent() {
                    this.detectarCargoInicial();
                    this.$watch('entidad.cargo', (val) => { this.detectarCargoInicial(); });
                },

                actualizarCargoFinal() {
                    if (this.cargoSelect === 'OTROS') {
                        this.entidad.cargo = this.cargoManual.toUpperCase();
                    } else {
                        this.entidad.cargo = this.cargoSelect;
                        this.cargoManual = ''; 
                    }
                },

                detectarCargoInicial() {
                    let cargoBD = this.entidad.cargo;
                    if (!cargoBD) {
                        this.cargoSelect = '';
                        this.cargoManual = '';
                        return;
                    }
                    let cargoLimpio = String(cargoBD).trim().toUpperCase();
                    let coincidencia = this.listaProfesiones.find(prof => prof.trim().toUpperCase() === cargoLimpio);

                    if (coincidencia) {
                        this.$nextTick(() => { this.cargoSelect = coincidencia; });
                        this.cargoManual = ''; 
                    } else {
                        this.cargoSelect = 'OTROS';
                        this.cargoManual = cargoBD; 
                    }
                },

                async buscar() {
                    let doc = this.entidad.doc;
                    if (!doc || doc.length < 8) return;
                    this.buscando = true;
                    this.msgEstado = "Buscando...";
                    this.encontrado = false;
                    try {
                        {{-- OJO: esta ruta no está registrada en routes/web.php, así que responde 404
                             aunque la URL base ya sea la correcta. Queda pendiente de definir. --}}
                        let response = await fetch(`{{ url('/usuario/monitoreo/modulo/triaje/buscar-profesional') }}/${doc}`);
                        let data = await response.json();
                        if (data.success) {
                            this.entidad.tipo_doc = data.data.tipo_doc || 'DNI';
                            this.entidad.apellido_paterno = data.data.apellido_paterno;
                            this.entidad.apellido_materno = data.data.apellido_materno;
                            this.entidad.nombres = data.data.nombres;
                            this.entidad.email = data.data.email;
                            this.entidad.telefono = data.data.telefono;
                            this.entidad.cargo = data.data.cargo; 
                            this.msgEstado = "Encontrado.";
                            this.encontrado = true;
                            this.detectarCargoInicial();
                        } else {
                            this.limpiarCampos();
                            this.msgEstado = "No encontrado.";
                            this.encontrado = false;
                        }
                    } catch (error) { this.msgEstado = "Error de conexión."; } 
                    finally { this.buscando = false; }
                },

                limpiarCampos() {
                    this.entidad.nombres = '';
                    this.entidad.apellido_paterno = '';
                    this.entidad.apellido_materno = '';
                    this.entidad.email = '';
                    this.entidad.cargo = '';
                    this.entidad.telefono = '';
                    this.detectarCargoInicial(); 
                }
            }));
        });
    </script>
</div>