@extends('layouts.usuario')

@section('title', 'Módulo / Consultorio: ' . ($tituloConsultorio ?? 'Evaluación'))

@section('content')
    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ENCABEZADO SUPERIOR --}}
            <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-1">
                        <span class="px-3 py-1 bg-indigo-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">
                            Módulo de Evaluación
                        </span>
                        <span class="text-slate-400 font-bold text-[10px] uppercase">
                            ID Acta: #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight">
                        {{ $tituloConsultorio ?? 'CONSULTORIO / MÓDULO' }}
                    </h2>
                    <p class="text-slate-500 font-bold uppercase text-xs mt-1">
                        <i data-lucide="hospital" class="inline-block w-4 h-4 mr-1 text-indigo-500"></i>
                        {{ $acta->establecimiento->nombre }}
                    </p>
                </div>
                <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}"
                    class="flex items-center gap-2 px-6 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-black text-xs hover:bg-slate-50 transition-all uppercase shadow-sm">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al Panel
                </a>
            </div>

            <form action="{{ route('usuario.monitoreo.consultorio.store', [$acta->id, $slug]) }}" method="POST"
                enctype="multipart/form-data" class="space-y-6" id="form-monitoreo-final">
                @csrf

                @php
                    $contenido = $detalle->contenido ?? [];
                @endphp

                <input type="hidden" name="contenido[titulo_consultorio]" value="{{ $tituloConsultorio ?? 'CONSULTORIO' }}">

                {{-- 1.- DATOS GENERALES --}}
                <div class="monitoreo-section bg-white rounded-[2rem] p-8 shadow-lg border border-slate-100">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <span class="section-number bg-indigo-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">1</span>
                        <h3 class="text-indigo-900 font-black text-lg uppercase tracking-tight">DATOS GENERALES</h3>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 items-center">
                        <div>
                            <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Fecha de Monitoreo</label>
                            <input type="date" name="contenido[fecha]" value="{{ $contenido['fecha'] ?? date('Y-m-d') }}"
                                class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl px-4 py-3 text-slate-800 font-bold outline-none focus:border-indigo-500 transition-all">
                        </div>
                        <div>
                            <x-turno :selected="$contenido['turno'] ?? ''" />
                        </div>
                        <div>
                            <label class="block text-indigo-600 text-[10px] font-black uppercase tracking-widest mb-2">Tipo de Consultorio</label>
                            <select name="contenido[tipo_consultorio]" class="w-full px-4 py-3 bg-indigo-50 border-2 border-indigo-100 rounded-xl font-bold text-sm uppercase outline-none text-indigo-700 cursor-pointer focus:border-indigo-500 transition-all">
                                <option value="FISICO" {{ ($contenido['tipo_consultorio'] ?? '') == 'FISICO' || ($contenido['tipo_consultorio'] ?? '') == 'FÍSICO' ? 'selected' : '' }}>FISICO</option>
                                <option value="FUNCIONAL" {{ ($contenido['tipo_consultorio'] ?? '') == 'FUNCIONAL' ? 'selected' : '' }}>FUNCIONAL</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-indigo-600 text-[10px] font-black uppercase tracking-widest mb-2">¿Qué piso es? (Número)</label>
                            <input type="number" name="contenido[piso]" min="1" max="99" 
                                value="{{ preg_replace('/[^0-9]/', '', $contenido['piso'] ?? '1') ?: '1' }}" 
                                placeholder="Ej: 1"
                                class="w-full px-4 py-3 bg-indigo-50 border-2 border-indigo-100 rounded-xl font-bold text-sm outline-none text-indigo-700 focus:border-indigo-500 transition-all">
                        </div>
                    </div>
                </div>





                {{-- 2.- EQUIPOS DE CÓMPUTO E IMPRESORA --}}
                <div class="monitoreo-section bg-white rounded-[2rem] p-8 shadow-lg border border-slate-100">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <span class="section-number bg-indigo-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">2</span>
                        <h3 class="text-indigo-900 font-black text-lg uppercase tracking-tight">EQUIPOS DE CÓMPUTO E IMPRESORA</h3>
                    </div>

                    <x-tabla-equipos prefix="rrhh" :equipos="$equipos ?? []" />
                </div>

                {{-- 3.- TIPO DE CONECTIVIDAD (RESTAURADO COMPLETO) --}}
                <x-tipo-conectividad num="3" :contenido="$contenido" />

                {{-- 6.- OBSERVACIONES Y EVIDENCIAS --}}
                <div class="monitoreo-section bg-white rounded-[2rem] p-8 shadow-lg border border-slate-100">
                    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                        <span class="section-number bg-indigo-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">6</span>
                        <h3 class="text-indigo-900 font-black text-lg uppercase tracking-tight">OBSERVACIONES Y EVIDENCIAS</h3>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Observaciones Generales</label>
                            <textarea name="contenido[observaciones]" rows="3" class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-4 text-slate-800 font-bold uppercase outline-none focus:border-indigo-500" placeholder="INGRESE LAS OBSERVACIONES O INCIDENCIAS DETECTADAS EN ESTE CONSULTORIO...">{{ $contenido['observaciones'] ?? '' }}</textarea>
                        </div>

                        <div>
                            <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Subir Fotografía / Evidencia (Opcional)</label>
                            <input type="file" name="evidencia" id="input_evidencia_foto" accept="image/*" onchange="previewEvidenciaImage(this)"
                                   class="w-full bg-slate-50 border-2 border-slate-200 rounded-xl p-3 text-slate-600 font-bold text-xs cursor-pointer hover:bg-slate-100 transition-colors">
                            
                            {{-- CONTENEDOR DE PREVISUALIZACIÓN DE IMAGEN --}}
                            @php
                                $evidenciaPath = $detalle->contenido['evidencia_path'] ?? $contenido['evidencia_path'] ?? '';
                            @endphp
                            <div id="container_preview_evidencia" class="mt-4 {{ empty($evidenciaPath) ? 'hidden' : '' }}">
                                <div class="relative inline-block bg-slate-100 p-2 rounded-2xl border-2 border-indigo-200 shadow-md group">
                                    <img id="img_preview_evidencia" 
                                         src="{{ !empty($evidenciaPath) ? asset('storage/' . $evidenciaPath) : '' }}" 
                                         alt="Previsualización Evidencia" 
                                         class="max-h-64 max-w-full rounded-xl object-contain shadow-inner">
                                    <div class="mt-2 text-center text-[10px] font-black text-indigo-700 uppercase tracking-wider flex items-center justify-center gap-1">
                                        <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                        <span id="text_preview_evidencia_name">{{ !empty($evidenciaPath) ? basename($evidenciaPath) : 'Evidencia Adjunta' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BOTÓN GUARDAR --}}
                <div class="flex justify-end gap-4 pt-4">
                    <button type="submit" id="btn-submit-action" class="flex items-center gap-3 px-10 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-black rounded-2xl transition-all shadow-lg hover:shadow-indigo-200 text-sm uppercase tracking-widest">
                        <span id="icon-save-loader">
                            <i data-lucide="save" class="w-5 h-5"></i>
                        </span>
                        <span>Guardar Evaluación</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewEvidenciaImage(input) {
            const container = document.getElementById('container_preview_evidencia');
            const img = document.getElementById('img_preview_evidencia');
            const txtName = document.getElementById('text_preview_evidencia_name');

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    img.src = e.target.result;
                    if (container) container.classList.remove('hidden');
                    if (txtName) txtName.innerText = "NUEVA: " + input.files[0].name.toUpperCase();
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function toggleSihceAndDocs(val) {
            const dj = document.getElementById('div_firmo_dj');
            const conf = document.getElementById('div_firmo_confidencialidad');

            if (val === 'SI') {
                if (dj) dj.classList.remove('hidden');
                if (conf) conf.classList.remove('hidden');
            } else {
                if (dj) dj.classList.add('hidden');
                if (conf) conf.classList.add('hidden');
            }
            updateSectionNumbers();
        }

        function updateSectionNumbers() {
            let index = 1;
            document.querySelectorAll('.monitoreo-section').forEach(section => {
                if (!section.classList.contains('hidden')) {
                    const numberSpan = section.querySelector('.section-number');
                    if (numberSpan) {
                        numberSpan.textContent = index;
                        index++;
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            const selectSihce = document.getElementById('cuenta_sihce');
            if (selectSihce) toggleSihceAndDocs(selectSihce.value);

            updateSectionNumbers();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });

        document.getElementById('form-monitoreo-final').onsubmit = function (e) {
            let faltantes = [];

            // 1. DATOS GENERALES
            const fechaInput = document.querySelector('input[name="contenido[fecha]"]');
            if (!fechaInput || !fechaInput.value.trim()) {
                faltantes.push("DATOS GENERALES: Fecha de Monitoreo");
            }

            const turnoInput = document.querySelector('input[name="contenido[turno]"]:checked');
            if (!turnoInput) {
                faltantes.push("DATOS GENERALES: Turno (Mañana / Tarde)");
            }

            const tipoConsultorio = document.querySelector('select[name="contenido[tipo_consultorio]"]');
            if (!tipoConsultorio || !tipoConsultorio.value.trim()) {
                faltantes.push("DATOS GENERALES: Tipo de Consultorio");
            }

            const pisoInput = document.querySelector('input[name="contenido[piso]"]');
            if (!pisoInput || !pisoInput.value.trim() || parseInt(pisoInput.value) < 1) {
                faltantes.push("DATOS GENERALES: Número de Piso");
            }

            // 2. EQUIPOS DE CÓMPUTO
            const filasEquipos = document.querySelectorAll('tbody[id^="body_equipos_"] tr:not([id^="no_data_"])');
            filasEquipos.forEach((row, i) => {
                const desc = row.querySelector('input[name*="[descripcion]"]');
                const cant = row.querySelector('input[name*="[cantidad]"]');
                const est = row.querySelector('select[name*="[estado]"]');
                const prop = row.querySelector('select[name*="[propio]"]');

                if (desc && !desc.value.trim()) {
                    faltantes.push(`EQUIPOS DE CÓMPUTO: Descripción en fila #${i + 1}`);
                }
                if (cant && (!cant.value || parseInt(cant.value) < 1)) {
                    faltantes.push(`EQUIPOS DE CÓMPUTO: Cantidad en fila #${i + 1}`);
                }
                if (est && !est.value.trim()) {
                    faltantes.push(`EQUIPOS DE CÓMPUTO: Estado en fila #${i + 1}`);
                }
                if (prop && !prop.value.trim()) {
                    faltantes.push(`EQUIPOS DE CÓMPUTO: Propiedad en fila #${i + 1}`);
                }
                // Excepciones permitidas por el usuario: N° Serie u Observación por fila son Opcionales
            });

            // 3. TIPO DE CONECTIVIDAD
            const tipoConectividad = document.getElementById('tipo_conectividad_input')?.value;
            if (!tipoConectividad || !tipoConectividad.trim()) {
                faltantes.push("TIPO DE CONECTIVIDAD: Seleccione opción (WIFI, CABLEADO o SIN CONECTIVIDAD)");
            } else {
                if (tipoConectividad === 'WIFI') {
                    const wifiFuente = document.getElementById('wifi_fuente_input')?.value;
                    if (!wifiFuente || !wifiFuente.trim()) {
                        faltantes.push("TIPO DE CONECTIVIDAD: Procedencia de WiFi (Establecimiento o Personal)");
                    }
                }

                if (tipoConectividad === 'WIFI' || tipoConectividad === 'CABLEADO') {
                    const operador = document.getElementById('operador_servicio_select')?.value;
                    if (!operador || !operador.trim()) {
                        faltantes.push("TIPO DE CONECTIVIDAD: Operador de Servicio de Internet");
                    }

                    const velDescarga = document.getElementById('velocidad_descarga_input')?.value;
                    if (!velDescarga || !velDescarga.trim()) {
                        faltantes.push("TIPO DE CONECTIVIDAD: Velocidad de Descarga");
                    }

                    const velSubida = document.getElementById('velocidad_subida_input')?.value;
                    if (!velSubida || !velSubida.trim()) {
                        faltantes.push("TIPO DE CONECTIVIDAD: Velocidad de Subida");
                    }
                }
            }

            // Excepciones explícitas permitidas por requerimiento del usuario:
            // - N° Serie / C.Pat y Observación en Equipos de Cómputo (opcionales)
            // - Observaciones Generales en la Sección 6 (opcionales)
            // - Fotografía / Evidencia en la Sección 6 (opcionales)

            if (faltantes.length > 0) {
                e.preventDefault();
                let htmlList = '<ul class="text-left text-xs space-y-1.5 mt-2 font-bold text-slate-700 bg-rose-50 p-4 rounded-2xl border border-rose-200 shadow-inner max-h-60 overflow-y-auto custom-scroll">';
                faltantes.forEach(item => {
                    htmlList += `<li class="flex items-center gap-2 text-rose-700"><span class="text-rose-500 font-black">•</span> ${item}</li>`;
                });
                htmlList += '</ul>';

                Swal.fire({
                    icon: 'error',
                    title: 'Formulario Incompleto',
                    html: `<p class="text-xs text-slate-500 font-semibold mb-2">Se requieren los siguientes datos para poder guardar la evaluación:</p>${htmlList}`,
                    confirmButtonText: 'Completar Campos Requeridos',
                    confirmButtonColor: '#4F46E5',
                    customClass: {
                        popup: 'rounded-[2.5rem] p-6'
                    }
                });

                return false;
            }

            const btn = document.getElementById('btn-submit-action');
            const icon = document.getElementById('icon-save-loader');
            if (btn) {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            if (icon) {
                icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="animate-spin"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
            }
            return true;
        };
    </script>
@endsection
