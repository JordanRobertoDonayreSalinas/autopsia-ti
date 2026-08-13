@extends('layouts.usuario')
@section('title', 'Salud Mental - Medicina General')

@section('content')
    <div class="py-10 bg-[#F8F9FC] min-h-screen">
        <div class="max-w-6xl mx-auto px-4">

            {{-- ENCABEZADO SUPERIOR --}}
            <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span
                            class="px-3 py-1 bg-teal-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">Módulo
                            Especializado</span>
                        <span class="text-slate-400 font-bold text-[10px] uppercase">ID Acta:
                            #{{ str_pad($acta->numero_acta ?? $acta->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight">4.6 SERVICIO SOCIAL</h2>
                    <p class="text-slate-500 font-bold uppercase text-xs mt-1">
                        <i data-lucide="clipboard-pulse" class="inline-block w-4 h-4 mr-1 text-teal-500"></i>
                        {{ $acta->establecimiento->nombre }}
                    </p>
                </div>
                <a href="{{ route('usuario.monitoreo.salud_mental_group.index', $acta->id) }}"
                    class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-black text-xs hover:bg-slate-50 transition-all uppercase shadow-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al Panel
                </a>
            </div>

            {{-- FORMULARIO --}}
            <form id="formFicha" action="{{ route('usuario.monitoreo.sm_servicio_social.store', $acta->id) }}"
                method="POST" enctype="multipart/form-data" onsubmit="sincronizarDatos(event)">
                @csrf

                {{-- CONTENEDOR PARA LOS INPUTS OCULTOS GENERADOS POR JS --}}
                <div id="inputs_ocultos_container"></div>

                <div class="space-y-6">

                    {{-- 1. DETALLE CONSULTORIO --}}
                    <x-esp_1_detalleDeConsultorio :detalle="$detalle" />

                    {{-- 2. DATOS PROFESIONAL --}}
                    <x-esp_2_datosProfesional :detalle="$detalle" prefix="profesional" />

                    {{-- 2.1 DOC ADMINISTRATIVO (Aquí está el switch de SIHCE) --}}
                    <x-esp_2_1_docAdmin :detalle="$detalle" prefix="doc_administrativo" />

                    {{-- 3. DNI --}}
                    <x-esp_3_detalleDni :detalle="$detalle" color="teal" />

                    {{-- 4. DETALLES DE CAPACITACIÓN (SE OCULTA SI SIHCE = NO) --}}
                    <div id="wrapper_capacitacion">
                        <x-esp_4_detalleCap :model="json_encode($detalle->contenido['capacitacion'] ?? [])" />
                    </div>

                    {{-- 5. MATERIALES --}}
                    <div id="wrapper_materiales">
                        <div x-data='{ entidad: @json(data_get($detalle->contenido, 'materiales', (object) [])) }'
                            class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">

                            <div
                                class="absolute top-0 right-0 w-24 h-24 bg-teal-50 rounded-full -mr-12 -mt-12 opacity-60 pointer-events-none">
                            </div>

                            <div class="flex items-center gap-4 mb-8 relative z-10">
                                <div
                                    class="h-12 w-12 rounded-2xl bg-white flex items-center justify-center shadow-lg shadow-slate-100 border border-slate-100">
                                    <i data-lucide="package-search" class="text-teal-600 w-6 h-6"></i>
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Materiales</h3>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Verificación
                                        de insumos</p>
                                </div>
                            </div>

                            <div class="space-y-8 relative z-10">
                                <div class="pt-2">
                                    <label
                                        class="block text-sm font-bold text-slate-800 mb-6 uppercase tracking-tight border-b border-slate-100 pb-2">
                                        Al iniciar cuenta con:
                                    </label>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        {{-- 1. FUA --}}
                                        <div
                                            class="bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:shadow-md transition-shadow hover:border-teal-100">
                                            <span
                                                class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">FUA</span>
                                            <div class="space-y-3">
                                                <label class="flex items-center gap-3 cursor-pointer group">
                                                    <input type="radio" value="ELECTRONICA" x-model="entidad.fua"
                                                        style="accent-color: #0d9488;"
                                                        class="w-5 h-5 text-teal-600 focus:ring-teal-500 border-slate-300 bg-white">
                                                    <span
                                                        class="text-xs font-bold text-slate-600 group-hover:text-teal-600 transition-colors uppercase">Electronica</span>
                                                </label>
                                                <label class="flex items-center gap-3 cursor-pointer group">
                                                    <input type="radio" value="MANUAL" x-model="entidad.fua"
                                                        style="accent-color: #0d9488;"
                                                        class="w-5 h-5 text-teal-600 focus:ring-teal-500 border-slate-300 bg-white">
                                                    <span
                                                        class="text-xs font-bold text-slate-600 group-hover:text-teal-600 transition-colors uppercase">Manual</span>
                                                </label>
                                            </div>
                                        </div>

                                        {{-- 2. REFERENCIA --}}
                                        <div
                                            class="bg-slate-50 p-5 rounded-2xl border border-slate-100 hover:shadow-md transition-shadow hover:border-teal-100">
                                            <span
                                                class="block text-[10px] font-black text-slate-400 uppercase mb-3 tracking-widest">Referencia</span>
                                            <div class="space-y-3">
                                                <label class="flex items-center gap-3 cursor-pointer group">
                                                    <input type="radio" value="SIHCE" x-model="entidad.referencia"
                                                        style="accent-color: #0d9488;"
                                                        class="w-5 h-5 text-teal-600 focus:ring-teal-500 border-slate-300 bg-white">
                                                    <span
                                                        class="text-xs font-bold text-slate-600 group-hover:text-teal-600 transition-colors uppercase">SIHCE</span>
                                                </label>
                                                <label class="flex items-center gap-3 cursor-pointer group">
                                                    <input type="radio" value="REFCON" x-model="entidad.referencia"
                                                        style="accent-color: #0d9488;"
                                                        class="w-5 h-5 text-teal-600 focus:ring-teal-500 border-slate-300 bg-white">
                                                    <span
                                                        class="text-xs font-bold text-slate-600 group-hover:text-teal-600 transition-colors uppercase">REFCON</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 6. EQUIPAMIENTO --}}
                    @php
                        $equiposComoObjetos = collect($equipos)->map(function ($item) {
                            return (object) $item;
                        });
                    @endphp
                    <x-esp_5_equipos :equipos="$equiposComoObjetos" modulo="sm_servicio_social" />

                    {{-- 7. SOPORTE (SE OCULTA SI SIHCE = NO) --}}
                    {{-- IMPORTANTE: Se agregó este wrapper para poder ocultarlo con JS --}}
                    <div id="wrapper_soporte">
                        <x-esp_6_soporte :detalle="$detalle" />
                    </div>

                    {{-- 8. COMENTARIOS --}}
                    @php
                        $comentData = (object) ($detalle->contenido['comentarios'] ?? []);
                        if (isset($comentData->texto)) {
                            $comentData->comentario_esp = $comentData->texto;
                        }
                        if (isset($comentData->foto)) {
                            $comentData->foto_url_esp = $comentData->foto;
                        }
                    @endphp
                    <x-esp_7_comentariosEvid :comentario="$comentData" />

                </div>

                {{-- BOTÓN GRANDE DE GUARDADO --}}
                <div class="pt-10 pb-5 mt-6">
                    <button type="submit" id="btn-submit-action"
                        class="w-full group bg-teal-600 text-white p-8 rounded-[3rem] font-black shadow-2xl shadow-teal-200 flex items-center justify-between hover:bg-teal-700 transition-all duration-500 active:scale-[0.98] cursor-pointer">
                        <div class="flex items-center gap-8 pointer-events-none">
                            <div
                                class="h-16 w-16 bg-white/20 rounded-3xl flex items-center justify-center group-hover:rotate-12 transition-all shadow-lg border border-white/30">
                                <i data-lucide="save" id="icon-save-loader" class="w-8 h-8 text-white"></i>
                            </div>
                            <div class="text-left">
                                <p class="text-xl uppercase tracking-[0.3em] leading-none">Confirmar Registro</p>
                                <p class="text-[10px] text-teal-200 font-bold uppercase mt-3 tracking-widest">
                                    Sincronizar Módulo Salud Mental - Servicio Social</p>
                            </div>
                        </div>
                        <div
                            class="h-14 w-14 bg-white/10 rounded-full flex items-center justify-center group-hover:bg-white group-hover:text-teal-600 transition-all duration-500">
                            <i data-lucide="chevron-right" class="w-7 h-7"></i>
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPT: SINCRONIZACIÓN Y VALIDACIÓN SIHCE --}}
    <script>
        function sincronizarDatos(e) {
            const createHidden = (name, value) => {
                if (!value) return;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                document.getElementById('inputs_ocultos_container').appendChild(input);
            };

            document.getElementById('inputs_ocultos_container').innerHTML = '';

            // 1. CAPTURAR CAPACITACIÓN
            const capWrap = document.getElementById('wrapper_capacitacion');
            if (capWrap && capWrap.style.display !== 'none') {
                const radioCap = capWrap.querySelector('input[type="radio"]:checked');
                if (radioCap) createHidden('contenido[capacitacion][recibieron_cap]', radioCap.value);

                const selectCap = capWrap.querySelector('select');
                if (selectCap && selectCap.value) createHidden('contenido[capacitacion][institucion_cap]', selectCap.value);
            }

            // 2. CAPTURAR MATERIALES
            const matWrap = document.getElementById('wrapper_materiales');
            if (matWrap) {
                const tarjetas = matWrap.querySelectorAll('.bg-slate-50.p-5');

                // En Asistenta Social usualmente hay solo 2 tarjetas: FUA y REFERENCIA

                // Tarjeta 1: FUA
                if (tarjetas[0]) {
                    const checked = tarjetas[0].querySelector('input[type="radio"]:checked');
                    if (checked) createHidden('contenido[materiales][fua]', checked.value);
                }

                // Tarjeta 2: Referencia
                if (tarjetas[1]) {
                    const checked = tarjetas[1].querySelector('input[type="radio"]:checked');
                    if (checked) createHidden('contenido[materiales][referencia]', checked.value);
                }

                // (Opcional) Si hubiera más tarjetas en el futuro, se añadirían aquí igual que en Medicina
            }
        }

        // 3. Lógica de Visibilidad (SIHCE = NO -> Ocultar Capacitación y Soporte)
        document.addEventListener('DOMContentLoaded', () => {
            // El componente x-esp_2_1_docAdmin genera este name
            const inputName = 'contenido[doc_administrativo][cuenta_sihce]';
            const sections = ['wrapper_capacitacion', 'wrapper_soporte'];

            function toggleSections() {
                const select = document.querySelector(`select[name="${inputName}"]`);
                if (!select) return;

                const isNo = select.value === 'NO';

                sections.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.style.display = isNo ? 'none' : 'block';
                    }
                });
            }

            document.body.addEventListener('change', (e) => {
                if (e.target.name === inputName) {
                    toggleSections();
                }
            });

            // Ejecutar al cargar
            toggleSections();
        });
    </script>
@endsection
