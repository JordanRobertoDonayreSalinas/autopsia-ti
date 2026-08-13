@extends('layouts.usuario')
@section('title', 'Módulo 03: Triaje')

@section('content')
<div class="py-12 bg-[#f8fafc] min-h-screen" x-data="triajeForm()">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        {{-- ENCABEZADO --}}
        <div class="mb-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-6">
                <div class="h-16 w-16 bg-white rounded-2xl flex items-center justify-center shadow-sm border border-slate-100">
                    <span class="text-2xl font-black text-indigo-600">03</span>
                </div>
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-black rounded-full uppercase tracking-widest">Módulo Técnico</span>
                        <span class="text-slate-400 font-bold text-[10px] uppercase tracking-wider">ID Acta: #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight italic">Módulo Triaje</h2>

                </div>
            </div>
            
            <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}" class="px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-500 font-black text-xs uppercase tracking-widest shadow-sm hover:bg-slate-50 transition-colors">
                Volver
            </a>
        </div>

        {{-- FORMULARIO --}}
        <form @submit.prevent="guardarTodo" class="space-y-8">

            {{-- 1. SECCIÓN INICIO LABORES (Componente 'documentos') --}}
            <x-documentos model="form.inicio_labores" />
            
            {{-- 2. SECCION DATOS DEL PROFESIONAL --}}
            <x-seleccion-profesional model="form.profesional" capacitacion="form.capacitacion" />

            {{-- 3. SECCIÓN DNI (CONDICIONAL: Solo si Tipo Doc es DNI) --}}
            <div x-show="form.profesional.tipo_doc === 'DNI'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                 
                <x-dni model="form.seccion_dni" />
                
            </div>

            {{-- 4. SECCION: CAPACITACIÓN (CONDICIONAL) --}}
            {{-- Solo se muestra si utiliza_sihce es 'SI' --}}
            <div x-show="form.profesional.utiliza_sihce === 'SI'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                
                <x-capacitacion model="form.capacitacion" />
                
            </div>

            {{-- 5. INVENTARIO --}}
            <x-equipamiento model="form.inventario" />
                  
            {{-- 6.- TIPO DE CONECTIVIDAD (Componente) --}}
            <x-tipo-conectividad :contenido="$detalle->contenido ?? []" color="indigo" />

            {{-- 6. SECCION: DIFICULTADES CON EL SISTEMA (CONDICIONAL) --}}
            {{-- Solo se muestra si utiliza_sihce es 'SI' --}}
            <div x-show="form.profesional.utiliza_sihce === 'SI'"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95">
                 
                <x-dificultad model="form.dificultades" />
                
            </div>

            {{-- 7. NUEVA SECCIÓN: COMENTARIOS GENERALES (HTML Directo) --}}
            <div class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 opacity-60 pointer-events-none"></div>
                
                <div class="flex items-center gap-4 mb-6 relative z-10">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                        <i data-lucide="message-square-plus" class="text-white w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Comentarios</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Observaciones Adicionales</p>
                    </div>
                </div>

                <div class="relative z-10">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Detalle de observaciones</label>
                    <textarea 
                        x-model="form.inicio_labores.comentarios" 
                        rows="3" 
                        placeholder="Ingrese cualquier observación general relevante sobre el servicio..." 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 font-medium text-sm focus:ring-indigo-500 text-slate-700 uppercase"></textarea>
                </div>
            </div>

            {{-- 8. EVIDENCIA FOTOGRÁFICA --}}
            <x-fotos files="files" old-files="oldFiles" />
            
            {{-- BOTÓN GUARDAR --}}
            <div class="fixed bottom-6 right-6 z-50 md:static md:flex md:justify-end mt-10">
                <button type="submit" :disabled="saving" class="bg-indigo-600 hover:bg-indigo-700 text-white px-10 py-4 rounded-2xl font-black uppercase tracking-widest shadow-xl flex items-center gap-3 transition-all transform hover:scale-105 disabled:opacity-70 disabled:scale-100">
                    <i x-show="!saving" data-lucide="save" class="w-5 h-5"></i>
                    <i x-show="saving" data-lucide="loader-2" class="w-5 h-5 animate-spin"></i>
                    <span x-text="saving ? 'Guardando...' : 'Guardar Cambios'"></span>
                </button>
            </div>

        </form>
    </div>
</div>

<script>
    function triajeForm() {
        // --- 1. RECEPCIÓN DE DATOS ---
        const dbCapacitacion  = @json($dbCapacitacion ?? null);
        const dbDetalle       = @json($detalle ?? null);
        const dbInventario    = @json($dbInventario ?? []);
        const dbDificultad    = @json($dbDificultad ?? null);
        const dbFotos         = @json($dbFotos ?? []);
        const dbInicioLabores = @json($dbInicioLabores ?? null);
        const dbDni           = @json($dbDni ?? null);

        // --- 2. INICIALIZACIÓN ---
        
        // A) Profesional: Agregamos 'utiliza_sihce' y 'firma_jefe' aquí para el componente visual
        let initProfesional = { 
            tipo_doc: 'DNI', doc: '', nombres: '', apellido_paterno: '', apellido_materno: '', 
            email: '', cargo: '', telefono: '', 
            utiliza_sihce: '', // <--- CAMPO VISUAL
            firma_jefe: '0' // <--- CAMPO FIRMA JEFE
        };

        let initCapacitacion = { recibieron_cap: '', institucion_cap: '', decl_jurada: '', comp_confidencialidad: '' };

        if (dbCapacitacion) {
            initCapacitacion.recibieron_cap = dbCapacitacion.recibieron_cap || '';
            initCapacitacion.institucion_cap = dbCapacitacion.institucion_cap || '';
            initCapacitacion.decl_jurada = dbCapacitacion.decl_jurada || ''; 
            initCapacitacion.comp_confidencialidad = dbCapacitacion.comp_confidencialidad || '';
            
            if (dbCapacitacion.profesional) {
                // Copiamos los datos del profesional
                initProfesional = { ...initProfesional, ...dbCapacitacion.profesional };
            }
        }

        // B) Inicio Labores (Aquí está el dato real en BD)
        let initInicioLabores = { 
            fecha_registro: '', consultorios: '', nombre_consultorio: '', turno: '', comentarios: '' 
        };
        
        if (dbInicioLabores) {
            initInicioLabores.fecha_registro = dbInicioLabores.fecha_registro || '';
            initInicioLabores.consultorios = dbInicioLabores.cant_consultorios || '';
            initInicioLabores.nombre_consultorio = dbInicioLabores.nombre_consultorio || '';
            initInicioLabores.turno = dbInicioLabores.turno || '';
            initInicioLabores.comentarios = dbInicioLabores.comentarios || '';
            
            // *** CRUCIAL ***: Pasamos el dato de la tabla inicio_labores al objeto visual del profesional
            initProfesional.utiliza_sihce = dbInicioLabores.utiliza_sihce || ''; 
        }

        // C) Inventario
        let initInventario = [];
        if (dbInventario && dbInventario.length > 0) {
            initInventario = dbInventario.map(item => {
                let fullCode = item.nro_serie || '';
                
                // Valores por defecto
                let tipoDetectado = 'S'; 
                let codigoLimpio = fullCode;

                // Lógica mejorada para separar el Prefijo del Código
                // Buscamos si empieza con "S " o "CP " (o los antiguos NS, CB, S/C)
                const prefijosPosibles = ['S', 'CP', 'NS', 'CB', 'S/C'];
                
                for (let prefijo of prefijosPosibles) {
                    // Verificamos si la cadena comienza con el prefijo + espacio
                    if (fullCode.startsWith(prefijo + ' ')) {
                        tipoDetectado = prefijo;
                        // Cortamos el prefijo y el espacio para dejar solo el número
                        codigoLimpio = fullCode.substring(prefijo.length + 1);
                        break; 
                    }
                }

                // CORRECCIÓN VISUAL: Si la BD tiene un tipo antiguo (NS, CB, etc)
                // forzamos a que el selector muestre 'S' o 'CP' para que no quede en blanco.
                if (tipoDetectado !== 'S' && tipoDetectado !== 'CP') {
                    tipoDetectado = 'S'; // Por defecto S si no se reconoce
                }

                // CORRECCIÓN DE SEGURIDAD: 
                // Si por algún error de guardado anterior el código limpio aún tiene el prefijo (ej: "S 456"), lo limpiamos de nuevo.
                if (codigoLimpio.startsWith('S ')) codigoLimpio = codigoLimpio.substring(2);
                if (codigoLimpio.startsWith('CP ')) codigoLimpio = codigoLimpio.substring(3);

                return {
                    id: Date.now() + Math.random(),
                    descripcion: item.descripcion,
                    propiedad: item.propio,       
                    estado: item.estado,
                    tipo_codigo: tipoDetectado, 
                    codigo: codigoLimpio,       
                    observacion: item.observacion
                };
            });
        }

        // D) Dificultades
        let initDificultades = { institucion: '', medio: '' };
        if (dbDificultad) {
            initDificultades.institucion = dbDificultad.insti_comunica || '';
            initDificultades.medio = dbDificultad.medio_comunica || '';
        }

        // E) DNI
        let initDni = { tipo_dni: '', version_dnie: '', firma_sihce: '', comentarios: '' };
        if (dbDni) {
            initDni.tipo_dni = dbDni.tip_dni || ''; 
            initDni.version_dnie = dbDni.version_dni || '';
            initDni.firma_sihce = dbDni.firma_sihce || '';
            initDni.comentarios = dbDni.comentarios || '';
        }

        return {
            saving: false,
            files: [],      
            oldFiles: dbFotos, 
            form: {
                profesional: initProfesional,
                capacitacion: initCapacitacion,
                inventario: initInventario,
                dificultades: initDificultades,
                inicio_labores: initInicioLabores,
                seccion_dni: initDni
            },
            guardarTodo() {
                this.saving = true;
                let formToSend = JSON.parse(JSON.stringify(this.form));

                formToSend.inventario = formToSend.inventario.map(item => {
                    let tipo = item.tipo_codigo || 'NS';
                    let valor = item.codigo || '';
                    item.codigo = (tipo + ' ' + valor).trim(); 
                    return item;
                });

                // Leer conectividad desde los hidden inputs del componente x-tipo-conectividad
                // (no forman parte del form Alpine, se leen directamente del DOM)
                formToSend.conectividad = {
                    tipo:    document.getElementById('tipo_conectividad_input')?.value || '',
                    fuente:  document.getElementById('wifi_fuente_input')?.value || '',
                    operador: document.querySelector('select[name="contenido[operador_servicio]"]')?.value || '',
                    descarga: document.getElementById('velocidad_descarga_input')?.value || '',
                    subida:   document.getElementById('velocidad_subida_input')?.value || '',
                    descarga_unidad: document.getElementById('velocidad_descarga_unidad_select')?.value || '',
                    subida_unidad: document.getElementById('velocidad_subida_unidad_select')?.value || ''
                };
                let formData = new FormData();
                formData.append('data', JSON.stringify(formToSend));
                this.files.forEach(file => { formData.append('fotos[]', file); });

                fetch("{{ route('usuario.monitoreo.triaje.store', $acta->id) }}", {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.redirect ? window.location.href = data.redirect : window.location.reload();
                    } else {
                        this.saving = false;
                        alert('Error al guardar: ' + JSON.stringify(data.message));
                    }
                })
                .catch(error => {
                    this.saving = false;
                    alert('Error técnico.');
                    console.error(error);
                });
            }
        }
    }
</script>
@endsection