@extends('layouts.usuario')
@section('title', 'Módulo 12: Parto')

@push('styles')
    <style>
        :root {
            --primary-blue: #0f172a;
            --accent-blue: #2563eb;
            --light-blue: #eff6ff;
            --success: #10b981;
            --danger: #ef4444;
        }

        body {
            background-color: #f8fafc;
        }

        /* --- Estilos Generales --- */
        .form-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .input-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--primary-blue);
            text-transform: uppercase;
            margin-bottom: 0.4rem;
            letter-spacing: 0.05em;
        }

        .input-blue {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
            font-size: 0.875rem;
            color: #334155;
            background-color: #fff;
            transition: all 0.2s;
        }

        .input-blue:focus {
            outline: none;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* --- Toggles y Radios --- */
        .toggle-group {
            display: flex;
            gap: 0.5rem;
        }

        .toggle-radio {
            display: none;
        }

        .toggle-btn {
            padding: 0.5rem 1.2rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: 700;
            font-size: 0.75rem;
            color: #64748b;
            background: white;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .toggle-btn:hover {
            background: #f1f5f9;
        }

        .toggle-radio[value="SI"]:checked+.toggle-btn {
            background-color: var(--success);
            border-color: var(--success);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.4);
        }

        .toggle-radio[value="NO"]:checked+.toggle-btn {
            background-color: var(--danger);
            border-color: var(--danger);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.4);
        }

        /* --- Botones --- */
        .btn-finish {
            background: var(--success);
            color: white;
            border: 1px solid var(--success);
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.4);
            width: 100%;
            justify-content: center;
            padding: 1rem;
            border-radius: 0.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-finish:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        /* --- Upload Area --- */
        .upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            background-color: #f8fafc;
            cursor: pointer;
            transition: 0.2s;
        }

        .upload-area:hover {
            border-color: var(--accent-blue);
            background-color: var(--light-blue);
        }

        /* --- Animaciones --- */
        .section-container {
            /* IMPORTANTE: Eliminado display: block para permitir ocultamiento con clase hidden */
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')

    <div class="max-w-5xl mx-auto py-6 px-3">

        {{-- HEADER NAVEGACION --}}
        <div class="mb-8 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span
                        class="px-3 py-1 bg-indigo-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">Módulo
                        Técnico</span>
                    <span class="text-slate-400 font-bold text-[10px] uppercase">ID Acta:
                        #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
                <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight">12. Parto</h2>
                <p class="text-slate-500 font-bold uppercase text-xs mt-1">
                    <i data-lucide="hospital" class="inline-block w-4 h-4 mr-1 text-indigo-500"></i>
                    {{ $acta->establecimiento->nombre }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                <div class="relative w-full sm:w-auto">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i data-lucide="calendar" class="w-4 h-4 text-indigo-500"></i>
                    </div>
                    <input type="date" form="formulario" name="contenido[fecha_registro]"
                        value="{{ $registro->fecha_registro ?? date('Y-m-d') }}"
                        class="w-full sm:w-40 pl-10 pr-3 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-bold text-xs focus:border-indigo-500 focus:ring-0 uppercase shadow-sm transition-all cursor-pointer">
                </div>

                <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}"
                    class="flex items-center justify-center gap-2 px-6 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-black text-xs hover:bg-slate-50 transition-all uppercase shadow-sm w-full sm:w-auto">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al Panel
                </a>
            </div>
        </div>

        {{-- FORMULARIO --}}
        <form id='formulario' action="{{ route('usuario.monitoreo.parto.create', $acta->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="modulo_nombre" value="parto">

            {{-- ======================================================================== --}}
            {{-- SECCIÓN 1: DETALLES DEL CONSULTORIO (PDF SECCION 1)                    --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-slate-100 text-slate-600 rounded-lg">
                        <i data-lucide="building" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Detalles del Consultorio</h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="input-label">Nro. de Consultorios / Salas</label>
                        <input type="number" min="0" name="contenido[nro_consultorios]" class="input-blue font-bold"
                            placeholder="0" value="{{ $registro->nro_consultorios ?? 0 }}">
                    </div>
                    <div>
                        <label class="input-label">Nombre del Consultorio / Ambiente</label>
                        <input type="text" name="contenido[nombre_consultorio]" class="input-blue"
                            placeholder="Ej. Sala de Partos / Dilatación" value="{{ $registro->nombre_consultorio ?? '' }}">
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN 2: DATOS DEL RESPONSABLE Y SIHCE (PDF SECCION 2)               --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mt-6">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <i data-lucide="user" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Datos del Responsable</h2>
                        <p class="text-slate-500 text-xs">Información del encargado de Parto.</p>
                    </div>
                </div>

                {{-- FILA 1: Identificación --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                    <div class="md:col-span-3">
                        <label class="input-label">Tipo Doc.</label>
                        <select name="contenido[personal_tipo_doc]" id="personal_tipo_doc" class="input-blue text-xs">
                            <option value="DNI" {{ ($registro->personal_tipo_doc ?? '') == 'DNI' ? 'selected' : '' }}>DNI
                            </option>
                            <option value="CE" {{ ($registro->personal_tipo_doc ?? '') == 'CE' ? 'selected' : '' }}>C.E.
                            </option>
                        </select>
                    </div>

                    <div class="md:col-span-4">
                        <label class="input-label">Nro. Documento</label>
                        <div class="flex items-center gap-2">
                            <div class="relative w-full">
                                <input type="text" name="contenido[personal_dni]" id="personal_dni" maxlength="15"
                                    class="input-blue font-bold w-full" placeholder="Ingrese documento..."
                                    value="{{ $registro->personal_dni ?? '' }}"
                                    onkeydown="if(event.key === 'Enter'){event.preventDefault(); }">
                                <div id="loading-doc" class="hidden absolute right-3 top-2.5">
                                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-indigo-600"></i>
                                </div>
                            </div>
                            <button type="button" onclick="buscarPorDoc()"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white p-2.5 rounded-lg shadow-sm transition-colors flex-shrink-0"
                                title="Buscar Profesional">
                                <i data-lucide="search" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <p id="msg-doc" class="text-[10px] text-red-500 mt-1 hidden"></p>
                    </div>

                    <div class="md:col-span-5">
                        @php
                            $listaEspecialidades = [
                                'MEDICO',
                                'ODONTOLOGO(A)',
                                'ENFERMERO(A)',
                                'TECNICO ENFERMERIA',
                                'TECNICO LABORATORIO',
                                'BIOLOGO(A)',
                                'QUIMICO FARMACEUTICO(A)',
                                'NUTRICIONISTA',
                                'PSICOLOGO(A)',
                                'OBSTETRA',
                            ];
                            $valorEsp = $registro->personal_especialidad ?? '';
                            $esOtroEsp = !empty($valorEsp) && !in_array($valorEsp, $listaEspecialidades);
                        @endphp
                        <label class="input-label">Profesión</label>
                        <select id="select_especialidad" onchange="toggleEspecialidad(this)"
                            name="{{ $esOtroEsp ? '' : 'contenido[personal_especialidad]' }}"
                            class="input-blue text-xs w-full mb-2">
                            <option value="">-- Seleccionar --</option>
                            @foreach ($listaEspecialidades as $esp)
                                <option value="{{ $esp }}" {{ $valorEsp == $esp ? 'selected' : '' }}>
                                    {{ $esp }}
                                </option>
                            @endforeach
                            <option value="OTROS" {{ $esOtroEsp ? 'selected' : '' }}>OTROS</option>
                        </select>
                        <div id="div_especialidad_manual" class="{{ $esOtroEsp ? '' : 'hidden' }}">
                            <input type="text" id="input_especialidad_manual"
                                name="{{ $esOtroEsp ? 'contenido[personal_especialidad]' : '' }}"
                                value="{{ $esOtroEsp ? $valorEsp : '' }}"
                                class="input-blue text-xs w-full placeholder-slate-400"
                                placeholder="Especifique la profesión..." {{ $esOtroEsp ? '' : 'disabled' }}>
                        </div>
                    </div>
                    <script>
                        function toggleEspecialidad(select) {
                            const divManual = document.getElementById('div_especialidad_manual');
                            const inputManual = document.getElementById('input_especialidad_manual');
                            const nombreCampo = 'contenido[personal_especialidad]';
                            if (select.value === 'OTROS') {
                                divManual.classList.remove('hidden');
                                inputManual.disabled = false;
                                inputManual.name = nombreCampo;
                                inputManual.focus();
                                select.removeAttribute('name');
                            } else {
                                divManual.classList.add('hidden');
                                inputManual.disabled = true;
                                inputManual.value = '';
                                inputManual.removeAttribute('name');
                                select.name = nombreCampo;
                            }
                        }
                    </script>
                </div>

                {{-- FILA 2: Nombre Completo --}}
                <div class="mb-4 relative">
                    <label class="input-label">Apellidos y Nombres</label>
                    <input type="text" name="contenido[personal_nombre]" id="personal_nombre" class="input-blue"
                        placeholder="Escriba apellidos para buscar..." autocomplete="off"
                        value="{{ $registro->personal_nombre ?? '' }}" oninput="buscarPorNombre()">
                    <div id="lista-sugerencias"
                        class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto">
                    </div>
                </div>

                {{-- FILA 3: Contacto --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
                    <div class="md:col-span-6">
                        <label class="input-label">Correo Electrónico</label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400"><i
                                    data-lucide="mail" class="w-5 h-5"></i></span>
                            <input type="email" name="contenido[personal_correo]" id="personal_correo"
                                class="input-blue text-slate-700" style="padding-left: 2.8rem;"
                                placeholder="Escriba aquí..." value="{{ $registro->personal_correo ?? '' }}">
                        </div>
                    </div>
                    <div class="md:col-span-6">
                        <label class="input-label">Celular</label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400"><i
                                    data-lucide="phone" class="w-5 h-5"></i></span>
                            <input type="text" name="contenido[personal_celular]" id="personal_celular"
                                class="input-blue text-slate-700" style="padding-left: 2.8rem;"
                                placeholder="Escriba aquí..." value="{{ $registro->personal_celular ?? '' }}">
                        </div>
                    </div>
                </div>

                {{-- SUB-SECCION: USO DE SIHCE --}}
                <div class="bg-indigo-50/50 p-5 rounded-xl border border-indigo-100 mb-6">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-indigo-100 p-2 rounded-full text-indigo-600">
                                <i data-lucide="monitor-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">¿El profesional utiliza SIHCE?</h4>
                                <p class="text-[10px] text-slate-500 uppercase font-bold mt-1">Habilita opciones de
                                    seguridad y soporte</p>
                            </div>
                        </div>
                        <div class="toggle-group">
                            <label>
                                <input type="radio" name="contenido[utiliza_sihce]" value="SI"
                                    class="toggle-radio" onchange="toggleSihce(true)"
                                    {{ ($registro->utiliza_sihce ?? '') == 'SI' ? 'checked' : '' }}>
                                <span class="toggle-btn px-6"><i data-lucide="check" class="w-4 h-4"></i> SÍ</span>
                            </label>
                            <label>
                                <input type="radio" name="contenido[utiliza_sihce]" value="NO"
                                    class="toggle-radio" onchange="toggleSihce(false)"
                                    {{ ($registro->utiliza_sihce ?? '') == 'NO' ? 'checked' : '' }}>
                                <span class="toggle-btn px-6"><i data-lucide="x" class="w-4 h-4"></i> NO</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- BLOQUE CONDICIONAL SEGURIDAD (DJ y Confidencialidad) --}}
                <div id="bloque-seguridad-sihce"
                    class="{{ ($registro->utiliza_sihce ?? '') == 'SI' ? '' : 'hidden' }} animate-fadeIn">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm relative group hover:border-indigo-300 transition-colors">
                            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500 rounded-l-xl"></div>
                            <p class="text-sm font-bold text-slate-700 mb-3 pl-3">¿Firmó declaración jurada?</p>
                            <div class="toggle-group w-full flex justify-center">
                                <label class="flex-1"><input type="radio" name="contenido[firma_dj]" value="SI"
                                        class="toggle-radio"
                                        {{ ($registro->firma_dj ?? '') == 'SI' ? 'checked' : '' }}><span
                                        class="toggle-btn w-full justify-center"><i data-lucide="check"
                                            class="w-4 h-4 mr-2"></i> SÍ</span></label>
                                <label class="flex-1"><input type="radio" name="contenido[firma_dj]" value="NO"
                                        class="toggle-radio"
                                        {{ ($registro->firma_dj ?? '') == 'NO' ? 'checked' : '' }}><span
                                        class="toggle-btn w-full justify-center"><i data-lucide="x"
                                            class="w-4 h-4 mr-2"></i> NO</span></label>
                            </div>
                        </div>
                        <div
                            class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm relative group hover:border-indigo-300 transition-colors">
                            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500 rounded-l-xl"></div>
                            <p class="text-sm font-bold text-slate-700 mb-3 pl-3">¿Firmó compromiso de confidencialidad?
                            </p>
                            <div class="toggle-group w-full flex justify-center">
                                <label class="flex-1"><input type="radio" name="contenido[firma_confidencialidad]"
                                        value="SI" class="toggle-radio"
                                        {{ ($registro->firma_confidencialidad ?? '') == 'SI' ? 'checked' : '' }}><span
                                        class="toggle-btn w-full justify-center"><i data-lucide="check"
                                            class="w-4 h-4 mr-2"></i> SÍ</span></label>
                                <label class="flex-1"><input type="radio" name="contenido[firma_confidencialidad]"
                                        value="NO" class="toggle-radio"
                                        {{ ($registro->firma_confidencialidad ?? '') == 'NO' ? 'checked' : '' }}><span
                                        class="toggle-btn w-full justify-center"><i data-lucide="x"
                                            class="w-4 h-4 mr-2"></i> NO</span></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN 3: DETALLE DE DNI Y FIRMA DIGITAL (PDF SECCION 3)              --}}
            {{-- ======================================================================== --}}
            <div id="seccion-tipo-dni" class="form-card section-container mt-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                        <i data-lucide="credit-card" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Tipo de DNI y Firma Digital</h3>
                </div>
                <p class="input-label mb-3 text-xs uppercase text-slate-400">Seleccione el tipo de documento físico</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <label class="cursor-pointer relative group">
                        <input type="radio" name="contenido[tipo_dni_fisico]" value="ELECTRONICO" class="peer sr-only"
                            onchange="toggleDniOptions('ELECTRONICO')"
                            {{ ($registro->tipo_dni_fisico ?? '') == 'ELECTRONICO' ? 'checked' : '' }}>
                        <div
                            class="p-5 rounded-xl border-2 transition-all duration-200 border-slate-200 bg-white hover:border-indigo-300 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/30">
                            <div class="flex items-center gap-4">
                                <div class="bg-indigo-100 p-2.5 rounded-full text-indigo-600"><i data-lucide="credit-card"
                                        class="w-6 h-6"></i></div>
                                <div>
                                    <h4 class="font-bold text-slate-800">DNI ELECTRÓNICO</h4><span
                                        class="text-xs font-medium text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded">CON
                                        CHIP</span>
                                </div>
                                <div class="ml-auto hidden peer-checked:block text-indigo-600"><i
                                        data-lucide="check-circle-2" class="w-6 h-6 fill-indigo-600 text-white"></i></div>
                            </div>
                        </div>
                    </label>
                    <label class="cursor-pointer relative group">
                        <input type="radio" name="contenido[tipo_dni_fisico]" value="AZUL" class="peer sr-only"
                            onchange="toggleDniOptions('AZUL')"
                            {{ ($registro->tipo_dni_fisico ?? '') == 'AZUL' ? 'checked' : '' }}>
                        <div
                            class="p-5 rounded-xl border-2 transition-all duration-200 border-slate-200 bg-white hover:border-sky-300 peer-checked:border-sky-600 peer-checked:bg-sky-50/30">
                            <div class="flex items-center gap-4">
                                <div class="bg-sky-100 p-2.5 rounded-full text-sky-600"><i data-lucide="user-square"
                                        class="w-6 h-6"></i></div>
                                <div>
                                    <h4 class="font-bold text-slate-800">DNI AZUL</h4><span
                                        class="text-xs font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded">SIN
                                        CHIP</span>
                                </div>
                                <div class="ml-auto hidden peer-checked:block text-sky-600"><i
                                        data-lucide="check-circle-2" class="w-6 h-6 fill-sky-600 text-white"></i></div>
                            </div>
                        </div>
                    </label>
                </div>
                <div id="dnie-options-container"
                    class="hidden bg-indigo-50/50 p-6 rounded-xl border border-indigo-100 animate-fadeIn">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div>
                            <label class="input-label text-indigo-900 mb-2 block">VERSIÓN DEL DNIe</label>
                            <div class="relative bg-white rounded-lg">
                                <select name="contenido[dnie_version]" class="input-blue w-full">
                                    <option value="">-- SELECCIONE --</option>
                                    <option value="1.0"
                                        {{ ($registro->dnie_version ?? '') == '1.0' ? 'selected' : '' }}>VERSIÓN 1.0
                                    </option>
                                    <option value="2.0"
                                        {{ ($registro->dnie_version ?? '') == '2.0' ? 'selected' : '' }}>VERSIÓN 2.0
                                    </option>
                                    <option value="3.0"
                                        {{ ($registro->dnie_version ?? '') == '3.0' ? 'selected' : '' }}>VERSIÓN 3.0
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="input-label text-indigo-900 mb-2 block">¿FIRMA DIGITALMENTE EN SIHCE?</label>
                            <div class="flex items-center gap-4 h-[42px]">
                                <label class="flex items-center cursor-pointer"><input type="radio"
                                        name="contenido[firma_sihce]" value="SI"
                                        class="w-5 h-5 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                        {{ ($registro->firma_sihce ?? '') == 'SI' ? 'checked' : '' }}><span
                                        class="ml-2 text-sm font-bold text-slate-700">SÍ</span></label>
                                <label class="flex items-center cursor-pointer"><input type="radio"
                                        name="contenido[firma_sihce]" value="NO"
                                        class="w-5 h-5 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                        {{ ($registro->firma_sihce ?? '') == 'NO' ? 'checked' : '' }}><span
                                        class="ml-2 text-sm font-bold text-slate-700">NO</span></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN 4: DETALLES DE CAPACITACION (PDF SECCION 4)                    --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mt-6">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-orange-50 text-orange-600 rounded-lg">
                        <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">Detalles de Capacitación</h2>
                </div>

                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <div class="flex justify-between items-center gap-4">
                        <p class="text-sm font-bold text-slate-700">¿El personal recibió capacitación?</p>
                        <div class="toggle-group">
                            <label><input type="radio" name="contenido[capacitacion]" value="SI"
                                    class="toggle-radio" onchange="toggleCapacitacion(true)"
                                    {{ ($registro->capacitacion_recibida ?? '') == 'SI' ? 'checked' : '' }}><span
                                    class="toggle-btn"><i data-lucide="check" class="w-4 h-4"></i> SÍ</span></label>
                            <label><input type="radio" name="contenido[capacitacion]" value="NO"
                                    class="toggle-radio" onchange="toggleCapacitacion(false)"
                                    {{ ($registro->capacitacion_recibida ?? '') == 'NO' ? 'checked' : '' }}><span
                                    class="toggle-btn"><i data-lucide="x" class="w-4 h-4"></i> NO</span></label>
                        </div>
                    </div>
                    <div id="div-capacitacion-detalles"
                        class="{{ ($registro->capacitacion_recibida ?? '') == 'SI' ? '' : 'hidden' }} mt-4 pt-4 border-t border-slate-200">
                        <p class="input-label mb-2">Entidad que capacitó:</p>
                        <div class="flex flex-wrap gap-4">
                            @foreach (['MINSA', 'DIRESA', 'UNIDAD EJECUTORA', 'OTROS'] as $ente)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="contenido[capacitacion_ente]"
                                        value="{{ $ente }}" class="text-indigo-600 focus:ring-0"
                                        {{ ($registro->capacitacion_entes ?? '') == $ente ? 'checked' : '' }}>
                                    <span class="text-xs font-bold text-slate-600">{{ $ente }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN 5: MATERIALES (PDF SECCION 5)                                  --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mt-6">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-teal-50 text-teal-600 rounded-lg">
                        <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">Materiales</h2>
                </div>

                <div class="bg-slate-50 p-6 rounded-xl border border-slate-100 mb-6">
                    <p class="input-label mb-4 text-slate-600">Al iniciar sus labores diarias cuenta con:</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach (['REGISTRO DE HISTORIA CLINICA', 'PARTOGRAMA', 'CLAP', 'TAMIZAJE DE VIOLENCIA', 'LIBRO DE REGISTRO DE PARTOS', 'RECETA', 'ORDENES DE LABORATORIO', 'FUA'] as $insumo)
                            <label
                                class="flex items-center gap-3 cursor-pointer bg-white p-3 rounded-lg border border-slate-200 hover:border-indigo-400 group">
                                <input type="checkbox" name="contenido[insumos][]" value="{{ $insumo }}"
                                    class="rounded text-indigo-600 focus:ring-0 border-slate-300"
                                    {{ in_array($insumo, $registro->insumos_disponibles ?? []) ? 'checked' : '' }}>
                                <span
                                    class="text-[11px] font-bold text-slate-600 group-hover:text-indigo-700">{{ $insumo }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN 6: EQUIPAMIENTO (PDF SECCION 6)                                --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mt-6">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg"><i data-lucide="monitor"
                            class="w-6 h-6"></i></div>
                    <h2 class="text-xl font-bold text-slate-800">Equipamiento</h2>
                </div>

                <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden">
                    <div
                        class="bg-slate-50 border-b border-slate-100 px-4 py-3 flex flex-wrap gap-3 justify-between items-center">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Detalle de Equipos</h3>
                        <div class="flex items-center gap-2">
                            <select id="select-equipo-agregar"
                                class="text-xs border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pl-2 pr-8">
                                <option value="" disabled selected>-- Seleccione equipo --</option>
                                @foreach (['Tablet', 'Laptop', 'CPU', 'Monitor', 'Teclado', 'Mouse', 'Impresora', 'Escaner', 'Ticketera', 'Lector de DNIe', 'Lector de Codigo de Barras', 'OTRO'] as $eq)
                                    <option value="{{ $eq }}">{{ $eq }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="agregarEquipoDesdeSelect()"
                                class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-2 rounded-lg flex items-center gap-2 shadow-sm transition-all">
                                <i data-lucide="plus" class="w-3 h-3"></i> AGREGAR
                            </button>
                        </div>
                    </div>

                    <table class="w-full text-left border-collapse" id="tabla-equipos">
                        <thead>
                            <tr
                                class="bg-slate-50/50 border-b border-slate-200 text-[11px] uppercase text-slate-500 font-bold tracking-wider">
                                <th class="px-3 py-2 w-[25%]">Descripción</th>
                                <th class="px-3 py-2 w-[20%]">N° Serie / Cod.</th>
                                <th class="px-3 py-2 w-[15%]">Propiedad</th>
                                <th class="px-3 py-2 w-[15%]">Estado</th>
                                <th class="px-3 py-2 w-[20%]">Observaciones</th>
                                <th class="px-3 py-2 w-[5%] text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="tbody-equipos">
                            @php 
                                $equiposDbFallback = \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $acta->id)->where('modulo', 'parto')->get();
                                $equiposGuardados = [];
                                if ($equiposDbFallback->isNotEmpty()) {
                                    foreach($equiposDbFallback as $eq) {
                                        $equiposGuardados[] = [
                                            'nombre' => $eq->descripcion,
                                            'serie' => $eq->nro_serie,
                                            'propiedad' => $eq->propio,
                                            'estado' => $eq->estado,
                                            'observaciones' => $eq->observacion,
                                        ];
                                    }
                                } else {
                                    $equiposGuardados = $registro->equipos_listado ?? [];
                                }
                            @endphp
                            @foreach ($equiposGuardados as $idx => $item)
                                <tr class="group hover:bg-slate-50 transition-colors">
                                    <td class="p-2 align-middle">
                                        <input type="text" name="contenido[equipos][{{ $idx }}][nombre]"
                                            value="{{ $item['nombre'] ?? '' }}"
                                            class="w-full bg-transparent border-0 border-b border-transparent focus:border-indigo-500 focus:ring-0 font-bold text-slate-700 text-xs px-2 py-1 placeholder-slate-300"
                                            placeholder="Nombre">
                                    </td>

                                    <td class="p-2 align-middle">
                                        @php
                                            $fullSerie = $item['serie'] ?? '';
                                            $prefix = 'S';
                                            $valor = $fullSerie;
                                            if (strpos($fullSerie, ':') !== false) {
                                                $parts = explode(':', $fullSerie, 2);
                                                if (in_array($parts[0], ['S', 'CP'])) {
                                                    $prefix = $parts[0];
                                                    $valor = $parts[1];
                                                }
                                            }
                                        @endphp
                                        <div
                                            class="relative flex items-center w-full bg-slate-50 border border-slate-200 rounded overflow-hidden">
                                            <input type="hidden" class="input-serie-final"
                                                name="contenido[equipos][{{ $idx }}][serie]"
                                                value="{{ $fullSerie }}">
                                            <div class="bg-slate-100 border-r border-slate-200">
                                                <select onchange="actualizarSerieConcatenada(this)"
                                                    class="select-prefix h-full bg-transparent border-none text-[10px] font-bold text-slate-700 focus:ring-0 cursor-pointer pl-2 pr-6 py-1">
                                                    <option value="S" {{ $prefix == 'S' ? 'selected' : '' }}>S
                                                    </option>
                                                    <option value="CP" {{ $prefix == 'CP' ? 'selected' : '' }}>CP
                                                    </option>
                                                </select>
                                            </div>
                                            <input type="text" id="serie-input-{{ $idx }}"
                                                value="{{ $valor }}" oninput="actualizarSerieConcatenada(this)"
                                                class="input-valor w-full bg-white border-none text-[11px] font-mono uppercase text-slate-600 focus:ring-0 px-2 py-1 placeholder-slate-400"
                                                placeholder="DIGITE...">
                                            <button type="button"
                                                onclick="iniciarEscaneo('serie-input-{{ $idx }}')"
                                                class="pr-1 pl-1 text-slate-400 hover:text-indigo-600 cursor-pointer transition-colors bg-white h-full">
                                                <i data-lucide="scan-barcode" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    </td>

                                    <td class="p-2 align-middle">
                                        <select name="contenido[equipos][{{ $idx }}][propiedad]"
                                            class="w-full bg-white border border-slate-200 text-[11px] text-slate-600 rounded px-1 py-1 focus:border-indigo-500 focus:ring-0 cursor-pointer">
                                            <option value="EXCLUSIVO"
                                                {{ ($item['propiedad'] ?? '') == 'EXCLUSIVO' ? 'selected' : '' }}>Exclusivo
                                            </option>
                                            <option value="COMPARTIDO"
                                                {{ ($item['propiedad'] ?? '') == 'COMPARTIDO' ? 'selected' : '' }}>
                                                Compartido</option>
                                            <option value="PERSONAL"
                                                {{ ($item['propiedad'] ?? '') == 'PERSONAL' ? 'selected' : '' }}>Personal
                                            </option>
                                        </select>
                                    </td>

                                    <td class="p-2 align-middle">
                                        <select name="contenido[equipos][{{ $idx }}][estado]"
                                            class="w-full bg-white border border-slate-200 text-[11px] rounded px-1 py-1 focus:border-indigo-500 focus:ring-0 cursor-pointer text-slate-600">
                                            <option value="Operativo"
                                                {{ ($item['estado'] ?? '') == 'Operativo' ? 'selected' : '' }}>Operativo
                                            </option>
                                            <option value="Regular"
                                                {{ ($item['estado'] ?? '') == 'Regular' ? 'selected' : '' }}>Regular
                                            </option>
                                            <option value="Inoperativo"
                                                {{ ($item['estado'] ?? '') == 'Inoperativo' ? 'selected' : '' }}>
                                                Inoperativo</option>
                                        </select>
                                    </td>

                                    <td class="p-2 align-middle">
                                        <input type="text"
                                            name="contenido[equipos][{{ $idx }}][observaciones]"
                                            value="{{ $item['observaciones'] ?? '' }}"
                                            class="w-full bg-transparent text-[11px] text-slate-500 border-0 border-b border-transparent focus:border-indigo-500 focus:ring-0 placeholder-slate-300 italic px-2 py-1"
                                            placeholder="Observaciones...">
                                    </td>

                                    <td class="p-2 text-center align-middle">
                                        <button type="button" onclick="this.closest('tr').remove()"
                                            class="text-slate-300 hover:text-red-500 hover:bg-red-50 p-1 rounded transition-all">
                                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase mb-2"><i
                            data-lucide="message-square" class="w-4 h-4"></i> Observaciones Adicionales</label>
                    <textarea name="contenido[equipos_observaciones]" rows="3"
                        placeholder="Comentarios sobre materiales o equipos..."
                        class="w-full bg-white border border-slate-300 rounded-lg p-3 text-sm resize-none">{{ $registro->equipos_observaciones ?? '' }}</textarea>
                </div>
            </div>

            {{-- Modal del Escáner --}}
            <div id="scanner-modal"
                class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 transition-all">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden relative shadow-2xl">
                    <div class="p-4 bg-white border-b flex justify-between items-center z-10 relative">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2"><i data-lucide="scan"
                                class="text-indigo-600"></i> Escáner</h3>
                        <button type="button" onclick="detenerEscaneo()"
                            class="text-slate-400 hover:text-red-500 bg-slate-50 hover:bg-red-50 p-1 rounded-full transition-colors"><i
                                data-lucide="x" class="w-5 h-5"></i></button>
                    </div>
                    <div id="reader" class="w-full bg-black min-h-[250px] relative"></div>
                </div>
            </div>
            
            {{-- 6.- TIPO DE CONECTIVIDAD (Componente) --}}
                <x-tipo-conectividad :contenido="$detalle->contenido ?? []" color="indigo" />
                
            {{-- ======================================================================== --}}
            {{-- SECCIÓN 7: DATOS DE GESTIÓN (PDF SECCION 7)                            --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mt-6">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg"><i data-lucide="bar-chart-2"
                            class="w-6 h-6"></i></div>
                    <h2 class="text-xl font-bold text-slate-800">Datos de Gestión</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Indicadores --}}
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                        <label class="input-label mb-4 border-b pb-2">Indicadores</label>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-xs font-bold text-slate-700 uppercase">Partos Registrados (Mes):</span>
                            <input type="number" min="0" name="contenido[nro_gestantes_mes]"
                                class="w-20 border border-indigo-200 rounded p-2 text-center font-bold text-indigo-700 bg-white"
                                value="{{ $registro->nro_gestantes_mes ?? 0 }}">
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-600 uppercase">¿Descarga en HISMINSA?</span>
                            <div class="toggle-group">
                                <label><input type="radio" name="contenido[gestion_hisminsa]" value="SI"
                                        class="toggle-radio"
                                        {{ ($registro->gestion_hisminsa ?? '') == 'SI' ? 'checked' : '' }}><span
                                        class="toggle-btn">SÍ</span></label>
                                <label><input type="radio" name="contenido[gestion_hisminsa]" value="NO"
                                        class="toggle-radio"
                                        {{ ($registro->gestion_hisminsa ?? '') == 'NO' ? 'checked' : '' }}><span
                                        class="toggle-btn">NO</span></label>
                            </div>
                        </div>
                    </div>

                    {{-- Reportes (Condicional dentro de gestión) --}}
                    <div id="bloque-reportes-sistema" class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                        <label class="input-label mb-4 border-b pb-2">Reportes del Sistema</label>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-xs font-bold text-slate-600 uppercase">¿Utiliza Reportes?</span>
                            <div class="toggle-group">
                                <label><input type="radio" name="contenido[gestion_reportes]" value="SI"
                                        class="toggle-radio" onchange="toggleReportesPrenatal(true)"
                                        {{ ($registro->gestion_reportes ?? '') == 'SI' ? 'checked' : '' }}><span
                                        class="toggle-btn">SÍ</span></label>
                                <label><input type="radio" name="contenido[gestion_reportes]" value="NO"
                                        class="toggle-radio" onchange="toggleReportesPrenatal(false)"
                                        {{ ($registro->gestion_reportes ?? '') == 'NO' ? 'checked' : '' }}><span
                                        class="toggle-btn">NO</span></label>
                            </div>
                        </div>
                        <div id="div-reportes-prenatal-detalle"
                            class="{{ ($registro->gestion_reportes ?? '') == 'SI' ? '' : 'hidden' }}">

                            @php
                                $listaSocializa = [
                                    'PERSONAL DEL SERVICIO',
                                    'JEFE DE ESTABLECIMIENTO',
                                    'ESTADISTICO',
                                    'UNIDAD EJECUTORA',
                                    'DIRESA',
                                    'OTROS',
                                ];
                                $valSocializa = $registro->gestion_reportes_socializa ?? '';
                            @endphp
                            <label class="input-label mb-1">Si es "SI" ¿con quién lo socializa?</label>
                            <div class="relative">
                                <select name="contenido[gestion_reportes_socializa]"
                                    class="input-blue w-full appearance-none pr-8 cursor-pointer">
                                    <option value="">-- Seleccione --</option>
                                    @foreach ($listaSocializa as $opcion)
                                        <option value="{{ $opcion }}"
                                            {{ $valSocializa == $opcion ? 'selected' : '' }}>
                                            {{ $opcion }}
                                        </option>
                                    @endforeach
                                </select>
                                <span
                                    class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none text-indigo-500">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN 8: SOPORTE (Visible si usa SIHCE) (PDF SECCION 8)              --}}
            {{-- ======================================================================== --}}
            <div id="bloque-dificultades"
                class="form-card section-container mt-6 {{ ($registro->utiliza_sihce ?? '') == 'SI' ? '' : 'hidden' }}">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-red-50 text-red-600 rounded-lg"><i data-lucide="life-buoy" class="w-6 h-6"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">Soporte</h2>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-8 relative">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">1. Ante
                                Dificultades ¿A quién comunica?</p>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach (['MINSA', 'DIRESA', 'OTROS', 'UNIDAD EJECUTORA', 'JEFE DE ESTABLECIMIENTO'] as $opcion)
                                    <label class="cursor-pointer group relative">
                                        <input type="radio" name="contenido[dificultades][comunica]"
                                            value="{{ $opcion }}" class="peer sr-only"
                                            {{ ($registro->dificultad_comunica_a ?? '') == $opcion ? 'checked' : '' }}>
                                        <div
                                            class="text-center py-3 px-1 rounded-lg border border-slate-200 bg-white transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 peer-checked:shadow-sm group-hover:border-indigo-300 h-full flex items-center justify-center">
                                            <span
                                                class="block text-[10px] font-bold text-slate-500 peer-checked:text-indigo-700">{{ $opcion }}</span>
                                        </div>
                                        <div
                                            class="absolute -top-2 -right-2 bg-indigo-600 text-white rounded-full p-0.5 opacity-0 peer-checked:opacity-100 transition-opacity shadow-sm">
                                            <i data-lucide="check" class="w-2 h-2"></i>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="hidden md:block absolute top-4 bottom-4 left-1/2 w-px bg-slate-100 -translate-x-1/2">
                        </div>
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">2. ¿Qué medio
                                utiliza?</p>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach (['WHATSAPP', 'CELULAR', 'CORREO', 'OTROS'] as $opcion)
                                    <label class="cursor-pointer group relative">
                                        <input type="radio" name="contenido[dificultades][medio]"
                                            value="{{ $opcion }}" class="peer sr-only"
                                            {{ ($registro->dificultad_medio_uso ?? '') == $opcion ? 'checked' : '' }}>
                                        <div
                                            class="text-center py-3 px-1 rounded-lg border border-slate-200 bg-white transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 peer-checked:shadow-sm group-hover:border-indigo-300 h-full flex items-center justify-center">
                                            <span
                                                class="block text-[10px] font-bold text-slate-500 peer-checked:text-indigo-700">{{ $opcion }}</span>
                                        </div>
                                        <div
                                            class="absolute -top-2 -right-2 bg-indigo-600 text-white rounded-full p-0.5 opacity-0 peer-checked:opacity-100 transition-opacity shadow-sm">
                                            <i data-lucide="check" class="w-2 h-2"></i>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN 9: EVIDENCIAS FOTOGRÁFICAS (PDF SECCION 9)                     --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mt-6">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-purple-50 text-purple-600 rounded-lg"><i data-lucide="camera" class="w-6 h-6"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">Evidencias Fotográficas</h2>
                </div>
                <div class="mb-6">
                    <div class="flex gap-4 mb-4 border-b border-slate-200">
                        <button type="button" onclick="switchTab('local')" id="tab-local"
                            class="pb-2 text-sm font-bold text-indigo-600 border-b-2 border-indigo-600 transition-all"><i
                                data-lucide="upload-cloud" class="w-4 h-4 inline mr-1"></i> Subir desde PC</button>
                        <button type="button" onclick="switchTab('server')" id="tab-server"
                            class="pb-2 text-sm font-bold text-slate-400 hover:text-indigo-500 transition-all"><i
                                data-lucide="server" class="w-4 h-4 inline mr-1"></i> Explorar Servidor</button>
                    </div>
                    <div id="panel-local" class="block">
                        <div
                            class="upload-area relative group bg-slate-50 border-2 border-dashed border-slate-300 rounded-xl p-8 text-center hover:bg-indigo-50 hover:border-indigo-400 transition-all cursor-pointer">
                            <input type="file" id="input-fotos-local" multiple accept="image/*"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                onchange="handleFiles(this.files)">
                            <div class="flex flex-col items-center gap-3">
                                <div
                                    class="w-12 h-12 bg-white rounded-full shadow-sm flex items-center justify-center text-indigo-500 group-hover:scale-110 transition-transform">
                                    <i data-lucide="image-plus" class="w-6 h-6"></i>
                                </div>
                                <p class="text-slate-700 font-bold text-sm">Haga clic o arrastre fotos aquí</p>
                                <p class="text-slate-400 text-[10px] uppercase font-bold">Máximo 2 imágenes</p>
                            </div>
                        </div>
                    </div>
                    <div id="panel-server" class="hidden">
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-8 text-center">
                            <div class="mb-4"><i data-lucide="hard-drive"
                                    class="w-10 h-10 text-slate-400 mx-auto mb-2"></i>
                                <p class="text-sm font-bold text-slate-600">Seleccionar archivos alojados en el Hosting</p>
                            </div>
                            <button type="button" onclick="openServerModal()"
                                class="bg-slate-800 text-white px-5 py-2.5 rounded-lg text-xs font-bold hover:bg-slate-900 transition flex items-center justify-center gap-2 mx-auto"><i
                                    data-lucide="search" class="w-4 h-4"></i> ABRIR EXPLORADOR DE ARCHIVOS</button>
                        </div>
                    </div>
                </div>
                <div>
                    <h3 class="input-label mb-3 flex justify-between"><span>Archivos Seleccionados </span><span
                            class="text-indigo-600" id="count-display">0 / 2</span></h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="gallery-container">
                        <div id="empty-state"
                            class="col-span-full py-6 text-center text-slate-400 text-xs italic bg-slate-50 rounded-lg border border-dashed border-slate-200">
                            No hay imágenes seleccionadas</div>
                    </div>
                </div>
                <input type="file" name="fotos[]" id="final-input-files" multiple class="hidden">
                <input type="hidden" name="rutas_servidor" id="final-input-server">
            </div>

            {{-- BOTÓN FINAL GUARDAR --}}
            <div class="mt-8 mb-12">
                <button type="submit" class="btn-finish w-full py-4 text-lg">
                    <i data-lucide="check-circle" class="w-6 h-6"></i> Finalizar y Guardar Registro
                </button>
            </div>

        </form>
    </div>

@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        let evidenceList = [];
        const MAX_PHOTOS = 2;
        let timeoutNombre = null;

        // --- 1. FUNCIÓN PARA OCULTAR/MOSTRAR LA SECCIÓN DNI ---
        function verificarVisibilidadSeccionDni(tipoDoc) {
            const seccion = document.getElementById('seccion-tipo-dni');
            if (!seccion) return;

            if (tipoDoc === 'CE') {
                seccion.style.display = 'none';
                const radios = seccion.querySelectorAll('input[type="radio"]');
                radios.forEach(r => r.checked = false);
                toggleDniOptions(null);
            } else {
                seccion.style.display = '';
            }
        }

        // --- 2. INICIALIZACIÓN ---
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            // Conectar evento change para Tipo Doc
            const selectTipo = document.getElementById('personal_tipo_doc');
            if (selectTipo) {
                selectTipo.addEventListener('change', function() {
                    verificarVisibilidadSeccionDni(this.value);
                });
                verificarVisibilidadSeccionDni(selectTipo.value);
            }

            // Verificar estado inicial de SIHCE
            const sihceValue = document.querySelector('input[name="contenido[utiliza_sihce]"]:checked');
            if (sihceValue && sihceValue.value === 'SI') {
                toggleSihce(true);
            } else {
                toggleSihce(false);
            }

            const fotosGuardadas = @json($registro->fotos_evidencia ?? []);
            if (fotosGuardadas.length > 0) {
                fotosGuardadas.forEach((url, i) => {
                    evidenceList.push({
                        type: 'server',
                        file: null,
                        url: url,
                        name: 'Foto Guardada',
                        id: Date.now() + i
                    });
                });
                renderGallery();
                syncInputs();
            }
        });

        // --- 3. LÓGICA DE DATOS ---
        async function buscarPorDoc() {
            const doc = document.getElementById('personal_dni').value.trim();
            const loader = document.getElementById('loading-doc');
            const msg = document.getElementById('msg-doc');

            // Limpiar campos
            document.getElementById('personal_nombre').value = '';
            document.getElementById('personal_correo').value = '';
            document.getElementById('personal_celular').value = '';
            
            if (doc.length < 5) return;
            
            loader.classList.remove('hidden');
            msg.classList.add('hidden');

            try {
                // PRIMER INTENTO LOCAL (usando el local_only=1)
                const resLocal = await fetch(`{{ route('usuario.monitoreo.citas.buscar.profesional') }}?type=doc&q=${doc}&local_only=1`);
                const dataLocal = await resLocal.json();

                if (dataLocal.length > 0) {
                    rellenarDatos(dataLocal[0]);
                    msg.textContent = "Personal encontrado.";
                    msg.className = "text-[10px] text-green-600 mt-1";
                    msg.classList.remove('hidden');
                    loader.classList.add('hidden');
                } else {
                    // SEGUNDO INTENTO: API EXTERNA RENIEC (solo si es DNI)
                    if (doc.length === 8) {
                        Swal.fire({
                            html: `
                                <div class="p-4 flex flex-col items-center">
                                    <div class="relative w-24 h-24 flex items-center justify-center mb-6">
                                        <div class="absolute inset-0 border-[6px] border-indigo-50 rounded-full animate-ping opacity-75"></div>
                                        <div class="absolute inset-3 border-4 border-indigo-100 rounded-full animate-pulse"></div>
                                        <div class="h-14 w-14 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-full flex items-center justify-center shadow-xl shadow-indigo-500/50 z-10 relative">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-white animate-bounce"><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                        </div>
                                    </div>
                                    <h3 class="text-2xl font-black text-indigo-900 uppercase tracking-tight mb-2">Conectando RENIEC</h3>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] text-center mb-6 leading-relaxed">
                                        Extrayendo nombres oficiales<br>de la plataforma nacional.
                                    </p>
                                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden flex gap-1">
                                        <div class="bg-indigo-300 h-full w-1/3 rounded-full animate-pulse"></div>
                                        <div class="bg-indigo-500 h-full w-1/3 rounded-full animate-pulse delay-75"></div>
                                        <div class="bg-indigo-700 h-full w-1/3 rounded-full animate-pulse delay-150"></div>
                                    </div>
                                </div>
                            `,
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-[2rem] border-2 border-indigo-50 shadow-2xl p-0 bg-white' }
                        });

                        const resExt = await fetch(`{{ route('usuario.monitoreo.citas.buscar.profesional') }}?type=doc&q=${doc}`);
                        const dataExt = await resExt.json();
                        
                        Swal.close();

                        if (dataExt.length > 0) {
                            if (dataExt[0].quota_exceeded) {
                                Swal.fire({
                                    title: 'Límite Mensual Excedido',
                                    text: 'Se ha agotado el límite de consultas a RENIEC por este mes. Por favor, ingrese los datos faltantes manualmente.',
                                    icon: 'warning',
                                    confirmButtonText: 'Entendido',
                                    confirmButtonColor: '#4f46e5',
                                    customClass: { popup: 'rounded-[2rem]' }
                                });
                                // Igual activamos para ingreso manual
                                msg.textContent = "Límite excedido. Complete manual.";
                                msg.className = "text-[10px] text-amber-600 mt-1 font-bold";
                                msg.classList.remove('hidden');
                                document.getElementById('personal_nombre').focus();
                            } else if (dataExt[0].exists_external) {
                                rellenarDatos(dataExt[0]);
                                msg.textContent = "Datos básicos encontrados en RENIEC.";
                                msg.className = "text-[10px] text-blue-600 mt-1 font-bold";
                                msg.classList.remove('hidden');
                                
                                const Toast = Swal.mixin({
                                    toast: true, position: 'top-end', showConfirmButton: false, timer: 4000
                                });
                                let tokenMsg = dataExt[0].remaining_tokens !== undefined ? ` (Tokens restantes: ${dataExt[0].remaining_tokens})` : '';
                                Toast.fire({
                                    icon: 'info', title: 'Completado desde RENIEC.' + tokenMsg + ' Por favor, llene los demás datos.'
                                });
                                document.getElementById('personal_correo').focus();
                            } else {
                                // No existe en local ni externo
                                msg.textContent = "Nuevo. Complete los nombres manual.";
                                msg.className = "text-[10px] text-amber-600 mt-1 font-bold";
                                msg.classList.remove('hidden');
                                document.getElementById('personal_nombre').focus();
                            }
                        } else {
                            // No existe en local ni externo
                            msg.textContent = "Nuevo. Complete los nombres manual.";
                            msg.className = "text-[10px] text-amber-600 mt-1 font-bold";
                            msg.classList.remove('hidden');
                            document.getElementById('personal_nombre').focus();
                        }
                    } else {
                        // CE u otro documento no validable por la API (asume no existe)
                        msg.textContent = "Nuevo documento. Ingrese nombres.";
                        msg.className = "text-[10px] text-amber-600 mt-1 font-bold";
                        msg.classList.remove('hidden');
                        document.getElementById('personal_nombre').focus();
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                msg.textContent = "Error al buscar.";
                msg.classList.remove('hidden');
            } finally {
                loader.classList.add('hidden');
            }
        }

        function buscarPorNombre() {
            const query = document.getElementById('personal_nombre').value;
            const lista = document.getElementById('lista-sugerencias');
            clearTimeout(timeoutNombre);
            if (query.length < 3) return lista.classList.add('hidden');
            timeoutNombre = setTimeout(async () => {
                try {
                    const res = await fetch(
                        `{{ route('usuario.monitoreo.citas.buscar.profesional') }}?type=name&q=${query}`);
                    const data = await res.json();
                    lista.innerHTML = '';
                    if (data.length > 0) {
                        lista.classList.remove('hidden');
                        data.forEach(p => {
                            const div = document.createElement('div');
                            div.className = "p-2 hover:bg-slate-100 cursor-pointer text-xs border-b";
                            div.innerHTML =
                                `<strong>${p.apellido_paterno} ${p.apellido_materno} ${p.nombres}</strong> <span class='text-slate-400'>(${p.doc})</span>`;
                            div.onclick = () => {
                                rellenarDatos(p);
                                lista.classList.add('hidden');
                            };
                            lista.appendChild(div);
                        });
                    } else {
                        lista.classList.add('hidden');
                    }
                } catch (error) {
                    console.error(error);
                }
            }, 300);
        }

        document.addEventListener('click', function(e) {
            const lista = document.getElementById('lista-sugerencias');
            const input = document.getElementById('personal_nombre');
            if (!lista.contains(e.target) && e.target !== input) lista.classList.add('hidden');
        });

        function rellenarDatos(prof) {
            document.getElementById('personal_nombre').value =
                `${prof.apellido_paterno} ${prof.apellido_materno} ${prof.nombres}`.trim();
            document.getElementById('personal_dni').value = prof.doc;
            const selectTipo = document.getElementById('personal_tipo_doc');
            selectTipo.value = (['DNI', 'CE'].includes(prof.tipo_doc)) ? prof.tipo_doc : 'OTRO';
            verificarVisibilidadSeccionDni(selectTipo.value);

            document.getElementById('personal_correo').value = prof.email || prof.correo || '';
            document.getElementById('personal_celular').value = prof.celular || prof.telefono || '';
        }

        // --- UI HELPERS ---
        window.toggleCapacitacion = (s) => document.getElementById('div-capacitacion-detalles').classList.toggle('hidden', !
            s);
        window.toggleReportesPrenatal = (s) => document.getElementById('div-reportes-prenatal-detalle').classList.toggle(
            'hidden', !s);

        // ACTUALIZADO: toggleSihce ahora controla explícitamente el bloque de soporte separado
        window.toggleSihce = (s) => {
            // 1. Bloque de Seguridad (Declaración Jurada y Confidencialidad)
            const bloqueSeguridad = document.getElementById('bloque-seguridad-sihce');
            // 2. Bloques de Soporte
            const bloqueDificultades = document.getElementById('bloque-dificultades');

            // Importante: Limpiamos display inline que a veces queda por JS anteriores
            if (s) {
                if (bloqueSeguridad) {
                    bloqueSeguridad.classList.remove('hidden');
                    bloqueSeguridad.style.display = '';
                }
                if (bloqueDificultades) {
                    bloqueDificultades.classList.remove('hidden');
                    bloqueDificultades.style.display = '';
                }
            } else {
                if (bloqueSeguridad) {
                    bloqueSeguridad.classList.add('hidden');
                    bloqueSeguridad.style.display = 'none';
                }
                if (bloqueDificultades) {
                    bloqueDificultades.classList.add('hidden');
                    bloqueDificultades.style.display = 'none';
                }
            }
        }
        window.switchTab = (t) => {
            document.getElementById('panel-local').style.display = t == 'local' ? 'block' : 'none';
            document.getElementById('panel-server').style.display = t == 'server' ? 'block' : 'none';
            document.getElementById('tab-local').className = t == 'local' ?
                'pb-2 text-sm font-bold text-indigo-600 border-b-2 border-indigo-600' :
                'pb-2 text-sm font-bold text-slate-400 hover:text-indigo-500';
            document.getElementById('tab-server').className = t == 'server' ?
                'pb-2 text-sm font-bold text-indigo-600 border-b-2 border-indigo-600' :
                'pb-2 text-sm font-bold text-slate-400 hover:text-indigo-500';
        }
        window.openServerModal = () => alert("🚧 MANTENIMIENTO 🚧\nExplorador habilitado en producción.");

        // --- TABLAS ---
        let equipoIndex = {{ count($equiposGuardados ?? []) }};

        function agregarEquipoDesdeSelect() {
            const select = document.getElementById('select-equipo-agregar');
            const tipoEquipo = select.value;

            if (!tipoEquipo) {
                alert("Por favor seleccione un equipo de la lista.");
                return;
            }

            const tbody = document.getElementById('tbody-equipos');
            const esOtro = tipoEquipo === 'OTRO';
            const valorNombre = esOtro ? '' : tipoEquipo;

            const fila = `
            <tr class="group hover:bg-slate-50 transition-colors">
                <td class="p-2 align-middle">
                    <input type="text" name="contenido[equipos][${equipoIndex}][nombre]" 
                        value="${valorNombre}" 
                        class="w-full bg-transparent border-0 border-b border-transparent focus:border-indigo-500 focus:ring-0 font-bold text-slate-700 text-xs px-2 py-1 placeholder-slate-300" 
                        placeholder="Escriba nombre del equipo..." ${esOtro ? 'autofocus' : ''}>
                </td>

                <td class="p-2 align-middle">
                    <div class="relative flex items-center w-full bg-slate-50 border border-slate-200 rounded overflow-hidden">
                        
                        <input type="hidden" class="input-serie-final" 
                               name="contenido[equipos][${equipoIndex}][serie]" value="">

                        <div class="bg-slate-100 border-r border-slate-200">
                            <select onchange="actualizarSerieConcatenada(this)"
                                    class="select-prefix h-full bg-transparent border-none text-[10px] font-bold text-slate-700 focus:ring-0 cursor-pointer pl-2 pr-6 py-1">
                                <option value="S">S</option>
                                <option value="CP">CP</option>
                            </select>
                        </div>

                        <input type="text" id="serie-input-${equipoIndex}" 
                               oninput="actualizarSerieConcatenada(this)"
                               class="input-valor w-full bg-white border-none text-[11px] font-mono uppercase text-slate-600 focus:ring-0 px-2 py-1 placeholder-slate-400" 
                               placeholder="DIGITE...">
                        
                        <button type="button" onclick="iniciarEscaneo('serie-input-${equipoIndex}')" 
                                class="pr-1 pl-1 text-slate-400 hover:text-indigo-600 cursor-pointer transition-colors bg-white h-full">
                            <i data-lucide="scan-barcode" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </td>

                <td class="p-2 align-middle">
                    <select name="contenido[equipos][${equipoIndex}][propiedad]" class="w-full bg-white border border-slate-200 text-[11px] text-slate-600 rounded px-1 py-1 focus:border-indigo-500 focus:ring-0 cursor-pointer">
                        <option value="EXCLUSIVO" selected>Exclusivo</option>
                        <option value="COMPARTIDO">Compartido</option>
                        <option value="PERSONAL">Personal</option>
                    </select>
                </td>

                <td class="p-2 align-middle">
                    <select name="contenido[equipos][${equipoIndex}][estado]" class="w-full bg-white border border-slate-200 text-[11px] rounded px-1 py-1 focus:border-indigo-500 focus:ring-0 cursor-pointer text-slate-600">
                        <option value="Operativo" selected>Operativo</option>
                        <option value="Regular">Regular</option>
                        <option value="Inoperativo">Inoperativo</option>
                    </select>
                </td>

                <td class="p-2 align-middle">
                    <input type="text" name="contenido[equipos][${equipoIndex}][observaciones]" class="w-full bg-transparent text-[11px] text-slate-500 border-0 border-b border-transparent focus:border-indigo-500 focus:ring-0 placeholder-slate-300 italic px-2 py-1" placeholder="Observaciones...">
                </td>

                <td class="p-2 text-center align-middle">
                    <button type="button" onclick="this.closest('tr').remove()" class="text-slate-300 hover:text-red-500 hover:bg-red-50 p-1 rounded transition-all">
                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                    </button>
                </td>
            </tr>
        `;

            tbody.insertAdjacentHTML('beforeend', fila);

            if (typeof lucide !== 'undefined') lucide.createIcons();
            select.value = "";
            equipoIndex++;
        }

        function actualizarSerieConcatenada(elemento) {
            const contenedor = elemento.closest('.relative');
            if (!contenedor) return;

            const selectPrefix = contenedor.querySelector('.select-prefix').value;
            const inputValor = contenedor.querySelector('.input-valor').value;
            const inputFinal = contenedor.querySelector('.input-serie-final');

            if (inputValor.trim() === '') {
                inputFinal.value = '';
            } else {
                inputFinal.value = `${selectPrefix}:${inputValor.toUpperCase()}`;
            }
        }

        // --- EVIDENCIAS ---
        window.handleFiles = (files) => {
            Array.from(files).forEach(f => {
                if (evidenceList.length >= MAX_PHOTOS) evidenceList.shift();
                evidenceList.push({
                    type: 'local',
                    file: f,
                    url: URL.createObjectURL(f),
                    id: Date.now() + Math.random()
                });
            });
            renderGallery();
            syncInputs();
        }
        window.removeImage = (id) => {
            evidenceList = evidenceList.filter(i => i.id !== id);
            renderGallery();
            syncInputs();
        }

        function renderGallery() {
            const cont = document.getElementById('gallery-container');
            const countDisplay = document.getElementById('count-display');
            cont.querySelectorAll('.group').forEach(e => e.remove());
            document.getElementById('empty-state').style.display = evidenceList.length ? 'none' : 'block';
            evidenceList.forEach(i => {
                const displayUrl = (i.type === 'server' && !i.url.startsWith('http')) 
                    ? `{{ asset('storage') }}/${i.url}` 
                    : i.url;

                const d = document.createElement('div');
                d.className = "relative group h-96 rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-white animate-fade-in";
                d.innerHTML =
                    `<img src="${displayUrl}" class="w-full h-full object-cover rounded-xl shadow-inner shadow-black/10 transition-transform duration-500 group-hover:scale-105"><button type="button" onclick="removeImage(${i.id})" class="absolute top-2 right-2 bg-red-500/80 backdrop-blur-sm text-white p-1.5 rounded-full shadow-lg hover:bg-red-600 transition-all z-20"><i data-lucide="x" class="w-3.5 h-3.5"></i></button><div class="absolute top-2 left-2 ${i.type=='local'?'bg-indigo-600/90':'bg-emerald-600/90'} backdrop-blur-sm text-white text-[9px] font-black tracking-widest px-3 py-1 rounded-full shadow-lg z-20 uppercase"><span>${i.type}</span></div>`;
                cont.appendChild(d);
            });
            if (countDisplay) countDisplay.innerText = `${evidenceList.length} / ${MAX_PHOTOS}`;
            lucide.createIcons();
        }

        function syncInputs() {
            const dt = new DataTransfer();
            evidenceList.filter(i => i.type === 'local').forEach(i => dt.items.add(i.file));
            document.getElementById('final-input-files').files = dt.files;
            document.getElementById('final-input-server').value = JSON.stringify(evidenceList.filter(i => i.type ===
                'server').map(i => i.url));
        }

        // --- ESCANER & DNIe ---
        let html5QrcodeScanner = null,
            currentInputId = null;

        function iniciarEscaneo(id) {
            currentInputId = id;
            document.getElementById('scanner-modal').classList.remove('hidden');
            html5QrcodeScanner = new Html5Qrcode("reader");
            html5QrcodeScanner.start({
                facingMode: "environment"
            }, {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            }, (txt) => {
                if (currentInputId) document.getElementById(currentInputId).value = txt;
                detenerEscaneo();
            }, () => {}).catch(err => {
                alert("Error cámara");
                document.getElementById('scanner-modal').classList.add('hidden');
            });
        }

        function detenerEscaneo() {
            if (html5QrcodeScanner) html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
                document.getElementById('scanner-modal').classList.add('hidden');
            });
        }

        function toggleDniOptions(t) {
            const c = document.getElementById('dnie-options-container');
            if (!c) return;
            if (t === 'ELECTRONICO') c.classList.remove('hidden');
            else c.classList.add('hidden');
        }
        document.addEventListener("DOMContentLoaded", function() {
            const sel = document.querySelector('input[name="contenido[tipo_dni_fisico]"]:checked');
            if (sel) toggleDniOptions(sel.value);
        });
    </script>
@endpush
