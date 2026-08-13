@extends('layouts.usuario')
@section('title', 'Módulo 02: Citas')

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

        /* --- Estilos Generales del Formulario --- */
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

        /* --- Tablas --- */
        .blue-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            overflow: hidden;
        }

        .blue-table th {
            background-color: var(--light-blue);
            color: var(--primary-blue);
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 800;
            padding: 0.75rem 1rem;
            text-align: left;
        }

        .blue-table td {
            border-top: 1px solid #f1f5f9;
            padding: 0.5rem;
        }

        .table-input {
            width: 100%;
            background: transparent;
            border: 1px solid transparent;
            padding: 0.4rem;
            border-radius: 0.375rem;
            font-size: 0.85rem;
        }

        .table-input:focus {
            border-color: var(--accent-blue);
            background: white;
            outline: none;
        }

        /* --- Botones --- */
        .btn-nav {
            padding: 0.8rem 2rem;
            border-radius: 0.5rem;
            font-weight: 700;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-finish {
            background: var(--success);
            color: white;
            border: 1px solid var(--success);
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.4);
            width: 100%;
            justify-content: center;
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

        /* --- Animaciones y Utilidades --- */
        canvas#signature-pad {
            border: 2px solid #e2e8f0;
            border-radius: 0.5rem;
            background: #fff;
            width: 100%;
            cursor: crosshair;
        }

        .section-container {
            /* IMPORTANTE: Eliminé 'display: block' para que funcione el toggle hidden */
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
                        class="px-3 py-1 bg-indigo-600 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">
                        Módulo Técnico
                    </span>
                    <span class="text-slate-400 font-bold text-[10px] uppercase">
                        ID Acta: #{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}
                    </span>
                </div>
                {{-- Mantenemos el "02. Citas" porque es el nombre del Módulo, no una sección del form --}}
                <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight">02. Citas</h2>
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
                    <input type="date" form="form-citas" name="contenido[fecha_registro]"
                        value="{{ $registro->fecha_registro ?? date('Y-m-d') }}"
                        class="w-full sm:w-40 pl-10 pr-3 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-bold text-xs focus:border-indigo-500 focus:ring-0 uppercase shadow-sm transition-all cursor-pointer">
                </div>

                <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}"
                    class="flex items-center justify-center gap-2 px-6 py-3 bg-white border-2 border-slate-200 rounded-2xl text-slate-600 font-black text-xs hover:bg-slate-50 transition-all uppercase shadow-sm w-full sm:w-auto">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al Panel
                </a>
            </div>
        </div>

        {{-- FORMULARIO PRINCIPAL --}}
        <form id="form-citas" action="{{ route('usuario.monitoreo.citas.create', $acta->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf

            {{-- ======================================================================== --}}
            {{-- SECCIÓN: INFRAESTRUCTURA (NUEVA POSICIÓN)                              --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mb-6">
                <div class="mb-4 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-slate-50 text-slate-600 rounded-lg">
                        <i data-lucide="layout-grid" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Detalles del Ambiente</h2>
                        <p class="text-slate-500 text-xs">Capacidad de atención instalada.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                    {{-- Input Ventanillas --}}
                    <div class="md:col-span-4">
                        <label class="input-label">Nro. de Ventanillas</label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="hash" class="w-5 h-5"></i>
                            </span>
                            <input type="number" name="contenido[nro_ventanillas]" style="padding-left:2.5rem;"
                                class="input-blue pl-10 font-bold text-lg text-indigo-700" placeholder="0" min="0"
                                value="{{ $registro->nro_ventanillas ?? 0 }}">
                        </div>
                    </div>

                </div>
            </div>


            {{-- ======================================================================== --}}
            {{-- SECCIÓN: DATOS DEL PROFESIONAL                                         --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                        <i data-lucide="user" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">Datos del Profesional</h2>
                        <p class="text-slate-500 text-xs">Información del encargado del módulo.</p>
                    </div>
                </div>

                {{-- FILA 1: Identificación --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                    {{-- 1. Tipo de Documento --}}
                    <div class="md:col-span-3">
                        <label class="input-label">Tipo Documento</label>
                        <select name="contenido[personal_tipo_doc]" id="personal_tipo_doc" class="input-blue">
                            <option value="DNI" {{ ($registro->personal_tipo_doc ?? '') == 'DNI' ? 'selected' : '' }}>DNI
                            </option>
                            <option value="CE" {{ ($registro->personal_tipo_doc ?? '') == 'CE' ? 'selected' : '' }}>C.E.
                            </option>
                        </select>
                    </div>

                    {{-- 2. Número de Documento --}}
                    <div class="md:col-span-3">
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

                    {{-- 3. Turno --}}
                    <div class="md:col-span-3">
                        <label class="input-label text-center">Turno</label>
                        <select name="contenido[personal_turno]" class="input-blue text-center">
                            <option value="MAÑANA" {{ ($registro->personal_turno ?? '') == 'MAÑANA' ? 'selected' : '' }}>
                                MAÑANA</option>
                            <option value="TARDE" {{ ($registro->personal_turno ?? '') == 'TARDE' ? 'selected' : '' }}>
                                TARDE</option>
                            <option value="NOCHE" {{ ($registro->personal_turno ?? '') == 'NOCHE' ? 'selected' : '' }}>
                                NOCHE</option>
                        </select>
                    </div>

                    {{-- 4. Roles --}}
                    <div class="md:col-span-3 relative">
                        <label class="input-label text-center">Roles Asignados</label>
                        <button type="button" onclick="toggleRolDropdown()"
                            class="input-blue flex justify-between items-center bg-white w-full text-left">
                            <span id="rol-selected-text" class="text-xs truncate block pr-2">NINGUNA</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400"></i>
                        </button>
                        <div id="rol-dropdown-list"
                            class="hidden absolute z-50 top-full mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-xl p-2 max-h-60 overflow-y-auto">
                            <div class="space-y-1">
                                @foreach (['ADMISIONISTA', 'CAJERO'] as $rol)
                                    <label
                                        class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded cursor-pointer border-b border-slate-50 last:border-0">
                                        <input type="checkbox" name="contenido[personal_rol][]"
                                            value="{{ $rol }}"
                                            class="rounded text-indigo-600 focus:ring-0 border-slate-300"
                                            onchange="updateRolText()"
                                            {{ in_array($rol, $registro->personal_roles ?? []) ? 'checked' : '' }}>
                                        <span class="text-xs font-bold text-slate-600">{{ $rol }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FILA 2: Nombre Completo --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                    <div class="md:col-span-12 relative">
                        <label class="input-label">Apellidos y Nombres</label>
                        <input type="text" name="contenido[personal_nombre]" id="personal_nombre" class="input-blue"
                            placeholder="Escriba para buscar coincidencias..." autocomplete="off"
                            value="{{ $registro->personal_nombre ?? '' }}" oninput="buscarPorNombre()">
                        <div id="lista-sugerencias"
                            class="hidden absolute z-50 w-full bg-white border border-slate-200 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto">
                        </div>
                    </div>
                </div>

                {{-- FILA 3: Correo, Celular y Cargo --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4">
                    <div class="md:col-span-4">
                        <label class="input-label">Correo Electrónico</label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </span>
                            <input type="email" name="contenido[personal_correo]" id="personal_correo"
                                class="input-blue bg-slate-50 text-slate-600 font-regular" style="padding-left: 2.8rem;"
                                placeholder="Escribe aqui..." value="{{ $registro->personal_correo ?? '' }}">
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        <label class="input-label">Celular</label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="phone" class="w-5 h-5"></i>
                            </span>
                            <input type="text" name="contenido[personal_celular]" id="personal_celular"
                                class="input-blue bg-slate-50 text-slate-600 font-regular" style="padding-left: 2.8rem;"
                                placeholder="Escribe aqui..." value="{{ $registro->personal_celular ?? '' }}">
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        @php
                            $cargos = [
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
                            $valorActual = $registro->personal_cargo ?? '';
                            $esOtro = !empty($valorActual) && !in_array($valorActual, $cargos);
                        @endphp
                        <label class="input-label text-indigo-700">Profesión</label>
                        <div class="relative mb-2">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-indigo-500">
                                <i data-lucide="briefcase" class="w-5 h-5"></i>
                            </span>
                            <select id="select_cargo" onchange="toggleCargoManual(this)" name="contenido[personal_cargo]"
                                class="input-blue font-regular border-indigo-200 focus:border-indigo-500 text-indigo-700 w-full rounded-md h-10 pl-10 pr-4 appearance-none"
                                style="padding-left: 2.8rem;">
                                <option value="">Seleccione...</option>
                                @foreach ($cargos as $cargo)
                                    <option value="{{ $cargo }}" {{ $valorActual == $cargo ? 'selected' : '' }}>
                                        {{ $cargo }}
                                    </option>
                                @endforeach
                                <option value="OTROS" {{ $esOtro ? 'selected' : '' }}>OTROS</option>
                            </select>
                        </div>
                        <div id="div_cargo_manual" class="{{ $esOtro ? '' : 'hidden' }}">
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-indigo-500">
                                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                                </span>
                                <input type="text" id="input_cargo_manual" name="contenido[personal_cargo]"
                                    class="input-blue font-regular border-indigo-200 focus:border-indigo-500 text-indigo-700 w-full pl-10 rounded-md"
                                    style="padding-left: 2.8rem;" placeholder="Especifique el cargo aquí..."
                                    value="{{ $esOtro ? $valorActual : '' }}" {{ $esOtro ? '' : 'disabled' }}>
                            </div>
                        </div>
                        <script>
                            function toggleCargoManual(selectElement) {
                                const divManual = document.getElementById('div_cargo_manual');
                                const inputManual = document.getElementById('input_cargo_manual');
                                if (selectElement.value === 'OTROS') {
                                    divManual.classList.remove('hidden');
                                    inputManual.disabled = false;
                                    inputManual.focus();
                                    selectElement.removeAttribute('name');
                                } else {
                                    divManual.classList.add('hidden');
                                    inputManual.disabled = true;
                                    inputManual.value = '';
                                    selectElement.setAttribute('name', 'contenido[personal_cargo]');
                                }
                            }
                        </script>
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN CONDICIONAL: USO DE SIHCE Y DOCUMENTACIÓN                      --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mt-6">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <i data-lucide="monitor-check" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Uso de Sistema SIHCE</h3>
                </div>

                {{-- PREGUNTA MAESTRA: ¿Utiliza SIHCE? --}}
                <div class="bg-indigo-50/50 p-5 rounded-xl border border-indigo-100 mb-6">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="bg-indigo-100 p-2 rounded-full text-indigo-600">
                                <i data-lucide="mouse-pointer-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">¿El profesional utiliza SIHCE?</h4>
                                <p class="text-[10px] text-slate-500 uppercase font-bold mt-1">Habilita opciones de Gestión
                                    y Calidad</p>
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

                {{-- BLOQUE CONDICIONAL (Declaración Jurada y Confidencialidad) --}}
                <div id="bloque-seguridad-sihce"
                    class="{{ ($registro->utiliza_sihce ?? '') == 'SI' ? '' : 'hidden' }} animate-fadeIn">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Declaración Jurada --}}
                        <div
                            class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm relative group hover:border-indigo-300 transition-colors">
                            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500 rounded-l-xl"></div>
                            <p class="text-sm font-bold text-slate-700 mb-3 pl-3">¿Firmó declaración jurada?</p>
                            <div class="toggle-group w-full flex justify-center">
                                <label class="flex-1">
                                    <input type="radio" name="contenido[firma_dj]" value="SI" class="toggle-radio"
                                        {{ ($registro->firma_dj ?? '') == 'SI' ? 'checked' : '' }}>
                                    <span class="toggle-btn w-full justify-center"><i data-lucide="check"
                                            class="w-4 h-4 mr-2"></i> SÍ</span>
                                </label>
                                <label class="flex-1">
                                    <input type="radio" name="contenido[firma_dj]" value="NO" class="toggle-radio"
                                        {{ ($registro->firma_dj ?? '') == 'NO' ? 'checked' : '' }}>
                                    <span class="toggle-btn w-full justify-center"><i data-lucide="x"
                                            class="w-4 h-4 mr-2"></i> NO</span>
                                </label>
                            </div>
                        </div>

                        {{-- Compromiso de Confidencialidad --}}
                        <div
                            class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm relative group hover:border-indigo-300 transition-colors">
                            <div class="absolute top-0 left-0 w-1 h-full bg-indigo-500 rounded-l-xl"></div>
                            <p class="text-sm font-bold text-slate-700 mb-3 pl-3">¿Firmó compromiso de confidencialidad?
                            </p>
                            <div class="toggle-group w-full flex justify-center">
                                <label class="flex-1">
                                    <input type="radio" name="contenido[firma_confidencialidad]" value="SI"
                                        class="toggle-radio"
                                        {{ ($registro->firma_confidencialidad ?? '') == 'SI' ? 'checked' : '' }}>
                                    <span class="toggle-btn w-full justify-center"><i data-lucide="check"
                                            class="w-4 h-4 mr-2"></i> SÍ</span>
                                </label>
                                <label class="flex-1">
                                    <input type="radio" name="contenido[firma_confidencialidad]" value="NO"
                                        class="toggle-radio"
                                        {{ ($registro->firma_confidencialidad ?? '') == 'NO' ? 'checked' : '' }}>
                                    <span class="toggle-btn w-full justify-center"><i data-lucide="x"
                                            class="w-4 h-4 mr-2"></i> NO</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN: TIPO DE DNI Y FIRMA                                           --}}
            {{-- ======================================================================== --}}
            <div id="seccion-tipo-dni" class="form-card section-container mt-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                        <i data-lucide="credit-card" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Detalle de DNI y Firma Digital</h3>
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
                                <div class="bg-indigo-100 p-2.5 rounded-full text-indigo-600"><i data-lucide="cpu"
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
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="contenido[firma_sihce]" value="SI"
                                        class="w-5 h-5 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                        {{ ($registro->firma_sihce ?? '') == 'SI' ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-bold text-slate-700">SÍ</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="contenido[firma_sihce]" value="NO"
                                        class="w-5 h-5 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                        {{ ($registro->firma_sihce ?? '') == 'NO' ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-bold text-slate-700">NO</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN: CAPACITACIÓN                                                  --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mt-6">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-orange-50 text-orange-600 rounded-lg">
                        <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">Detalles de Capacitación</h2>
                </div>

                <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-bold text-slate-700">¿El personal recibió capacitación?</p>
                        </div>
                        <div class="toggle-group">
                            <label>
                                <input type="radio" name="contenido[capacitacion]" value="SI" class="toggle-radio"
                                    onchange="toggleCapacitacion(true)"
                                    {{ ($registro->capacitacion_recibida ?? '') == 'SI' ? 'checked' : '' }}>
                                <span class="toggle-btn"><i data-lucide="check" class="w-4 h-4"></i> SÍ</span>
                            </label>
                            <label>
                                <input type="radio" name="contenido[capacitacion]" value="NO" class="toggle-radio"
                                    onchange="toggleCapacitacion(false)"
                                    {{ ($registro->capacitacion_recibida ?? '') == 'NO' ? 'checked' : '' }}>
                                <span class="toggle-btn"><i data-lucide="x" class="w-4 h-4"></i> NO</span>
                            </label>
                        </div>
                    </div>

                    @php
                        $rawEnte = $registro->capacitacion_entes ?? '';
                        $valorGuardado = is_array($rawEnte) ? $rawEnte[0] ?? '' : $rawEnte;
                    @endphp

                    <div id="div-capacitacion-detalles"
                        class="{{ ($registro->capacitacion_recibida ?? '') == 'SI' ? '' : 'hidden' }} mt-4 pt-4 border-t border-slate-200">
                        <p class="input-label mb-2">Entidad que capacitó (Seleccione una):</p>
                        <div class="flex flex-wrap gap-4">
                            @foreach (['MINSA', 'DIRESA', 'UNIDAD EJECUTORA', 'OTROS'] as $ente)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="contenido[capacitacion_ente]"
                                        value="{{ $ente }}" class="text-indigo-600 focus:ring-0"
                                        {{ $valorGuardado == $ente ? 'checked' : '' }}>
                                    <span class="text-xs font-bold text-slate-600">{{ $ente }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN: MATERIALES                                                    --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mt-6">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-teal-50 text-teal-600 rounded-lg">
                        <i data-lucide="clipboard-list" class="w-6 h-6"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">Materiales</h2>
                </div>

                <div class="bg-slate-50 p-6 rounded-xl border border-slate-100">
                    <div class="flex items-center gap-2 mb-4">
                        <h3 class="input-label mb-0 text-slate-600">Al iniciar sus labores diarias cuenta con:</h3>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach (['TICKET', 'FUA', 'HOJA DE FILIACIÓN', 'PAPEL BOND', 'TONER / TINTA', 'LAPICEROS'] as $insumo)
                            <label
                                class="flex items-center gap-3 cursor-pointer bg-white p-3 rounded-lg border border-slate-200 hover:border-indigo-400 group transition-colors">
                                <input type="checkbox" name="contenido[insumos][]" value="{{ $insumo }}"
                                    class="rounded text-indigo-600 focus:ring-0 border-slate-300"
                                    {{ in_array($insumo, $registro->insumos_disponibles ?? []) ? 'checked' : '' }}>
                                <span
                                    class="text-[11px] font-bold text-slate-600 group-hover:text-indigo-700 uppercase">{{ $insumo }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN: EQUIPAMIENTO                                                  --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mt-6">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                        <i data-lucide="monitor" class="w-6 h-6"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">Equipamiento</h2>
                </div>

                <div class="bg-white rounded-lg border border-slate-200 shadow-sm overflow-hidden mb-4">
                    <div
                        class="bg-slate-50 border-b border-slate-100 px-4 py-3 flex flex-wrap gap-3 justify-between items-center">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Listado de Equipos</h3>
                        <div class="flex items-center gap-2">
                            <select id="select-equipo-agregar"
                                class="text-xs border-slate-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 py-1.5 pl-2 pr-8">
                                <option value="" disabled selected>-- Seleccione equipo --</option>
                                @foreach (['All in One', 'Tablet', 'Laptop', 'CPU', 'Monitor', 'Teclado', 'Mouse', 'Impresora', 'Escaner', 'Ticketera', 'Lector de DNIe', 'Lector de Codigo de Barras', 'OTRO'] as $eq)
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
                                <th class="px-3 py-2 w-[15%]">Descripción</th>
                                <th class="px-3 py-2 w-[20%]">N.SERIE / C.PAT</th>
                                <th class="px-3 py-2 w-[15%]">Propiedad</th>
                                <th class="px-3 py-2 w-[15%]">Estado</th>
                                <th class="px-3 py-2 w-[30%]">Observaciones</th>
                                <th class="px-3 py-2 w-[5%] text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" id="tbody-equipos">
                            @php 
                                $equiposDbFallback = \App\Models\EquipoComputo::where('cabecera_monitoreo_id', $acta->id)->where('modulo', 'citas')->get();
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
                                            class="relative flex items-center w-full bg-white border border-slate-200 rounded shadow-sm focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500 overflow-hidden">
                                            <input type="hidden" class="input-serie-final"
                                                name="contenido[equipos][{{ $idx }}][serie]"
                                                value="{{ $fullSerie }}">
                                            <div class="bg-slate-50 border-r border-slate-200">
                                                <select onchange="actualizarSerieConcatenada(this)"
                                                    class="select-prefix h-7 bg-transparent border-none text-[10px] font-bold text-slate-700 focus:ring-0 cursor-pointer pl-2 pr-6 py-0">
                                                    <option value="S" {{ $prefix == 'S' ? 'selected' : '' }}>S
                                                    </option>
                                                    <option value="CP" {{ $prefix == 'CP' ? 'selected' : '' }}>CP
                                                    </option>
                                                </select>
                                            </div>
                                            <input type="text" id="serie-input-{{ $idx }}"
                                                value="{{ $valor }}" oninput="actualizarSerieConcatenada(this)"
                                                class="input-valor w-full border-none bg-transparent text-[11px] font-mono uppercase text-slate-600 focus:ring-0 px-2 py-1 placeholder-slate-300"
                                                placeholder="DIGITE...">
                                            <button type="button"
                                                onclick="iniciarEscaneo('serie-input-{{ $idx }}')"
                                                class="pr-2 pl-1 text-slate-400 hover:text-indigo-600 cursor-pointer transition-colors">
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
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase mb-2">
                        <i data-lucide="message-square" class="w-4 h-4"></i> Observaciones Adicionales
                    </label>
                    <textarea name="contenido[equipos_observaciones]" rows="3"
                        placeholder="Describa aquí algun comentario y/o dificultad adicional..."
                        class="w-full bg-white border border-slate-300 rounded-lg p-3 text-sm resize-none focus:ring-indigo-500 focus:border-indigo-500">{{ $registro->equipos_observaciones ?? '' }}</textarea>
                </div>
            </div>

            {{-- Modal del Escáner --}}
            <div id="scanner-modal"
                class="fixed inset-0 z-50 hidden bg-black/90 backdrop-blur-sm flex items-center justify-center p-4 transition-all">
                <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden relative shadow-2xl">
                    <div class="p-4 bg-white border-b flex justify-between items-center z-10 relative">
                        <h3 class="font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="scan" class="text-indigo-600"></i> Escáner
                        </h3>
                        <button type="button" onclick="detenerEscaneo()"
                            class="text-slate-400 hover:text-red-500 bg-slate-50 hover:bg-red-50 p-1 rounded-full transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div id="reader" class="w-full bg-black min-h-[250px] relative"></div>
                </div>
            </div>
            
            {{-- 6.- TIPO DE CONECTIVIDAD (Componente) --}}
                <x-tipo-conectividad :contenido="$registro ? $registro->toArray() : []" color="indigo" />

            {{-- ========================================================================================= --}}
            {{-- SECCIÓN: GESTIÓN DE CITAS Y CALIDAD                                                    --}}
            {{-- ESTE BLOQUE COMPLETO SE OCULTA SI NO TIENE SIHCE                                        --}}
            {{-- ========================================================================================= --}}
            <div id="seccion-gestion-completa"
                class="form-card section-container mt-6 {{ ($registro->utiliza_sihce ?? '') == 'SI' ? '' : 'hidden' }}">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-lg">
                        <i data-lucide="bar-chart-2" class="w-6 h-6"></i>
                    </div>
                    <h2 class="text-xl font-bold text-slate-800">Gestión, Calidad y Soporte</h2>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                    {{-- LADO IZQUIERDO: CITAS Y PRODUCCION --}}
                    <div>
                        <div class="flex flex-col md:flex-row md:justify-between md:items-end mb-5 gap-4">
                            <div>
                                <h2 class="text-l font-black text-slate-700 uppercase">Citas Otorgadas</h2>
                            </div>

                        </div>
                        <div class="overflow-hidden rounded-lg border border-slate-200 shadow-sm mb-4">
                            <table class="blue-table mb-0" id="tabla-produccion">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th class="text-center w-32">Total Citas</th>
                                        <th class="w-10"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $produccion = $registro->produccion_listado ?? [];
                                        $prodItems =
                                            count($produccion) > 0
                                                ? $produccion
                                                : [
                                                    ['nombre' => 'MEDICINA', 'cantidad' => 0],
                                                    ['nombre' => 'ODONTOLOGÍA', 'cantidad' => 0],
                                                    ['nombre' => 'NUTRICION', 'cantidad' => 0],
                                                    ['nombre' => 'PSICOLOGIA', 'cantidad' => 0],
                                                    ['nombre' => 'CRED', 'cantidad' => 0],
                                                    ['nombre' => 'OBSTETRICIA', 'cantidad' => 0],
                                                ];
                                    @endphp
                                    @foreach ($prodItems as $i => $item)
                                        <tr>
                                            <td><input type="text"
                                                    name="contenido[produccion][{{ $i }}][nombre]"
                                                    value="{{ $item['nombre'] ?? '' }}"
                                                    class="table-input font-bold text-slate-600"></td>
                                            <td><input type="number"
                                                    name="contenido[produccion][{{ $i }}][cantidad]"
                                                    value="{{ $item['cantidad'] ?? 0 }}" min="0"
                                                    class="table-input text-center font-bold text-indigo-600 bg-indigo-50/50">
                                            </td>
                                            <td class="text-center"><button type="button"
                                                    onclick="this.closest('tr').remove()"
                                                    class="p-1 rounded text-slate-300 hover:text-red-500"><i
                                                        data-lucide="trash-2" class="w-4 h-4"></i></button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <button type="button" onclick="agregarFilaProduccion('tabla-produccion')"
                            class="w-full text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 px-4 py-3 rounded-lg flex items-center justify-center gap-2">
                            <i data-lucide="plus" class="w-3 h-3"></i> AGREGAR OTRO SERVICIO
                        </button>
                    </div>

                    {{-- LADO DERECHO: CALIDAD Y SOPORTE --}}
                    <div id="bloque-calidad-dificultades" class="space-y-8">
                        <div>
                            <label class="input-label border-b border-slate-100 pb-2 mb-4 block">Evaluación de
                                Calidad</label>
                            <div class="space-y-3">
                                {{-- Pregunta 1 --}}
                                <div
                                    class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                                    <p class="text-xs font-bold text-slate-600 pr-4">¿Disminuye el tiempo de espera?</p>
                                    <div class="toggle-group">
                                        <label><input type="radio" name="contenido[calidad][espera]" value="SI"
                                                class="toggle-radio"
                                                {{ ($registro->calidad_tiempo_espera ?? '') == 'SI' ? 'checked' : '' }}><span
                                                class="toggle-btn"><i data-lucide="check" class="w-3 h-3"></i>
                                                SÍ</span></label>
                                        <label><input type="radio" name="contenido[calidad][espera]" value="NO"
                                                class="toggle-radio"
                                                {{ ($registro->calidad_tiempo_espera ?? '') == 'NO' ? 'checked' : '' }}><span
                                                class="toggle-btn"><i data-lucide="x" class="w-3 h-3"></i>
                                                NO</span></label>
                                    </div>
                                </div>
                                {{-- Pregunta 2 --}}
                                <div
                                    class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                                    <p class="text-xs font-bold text-slate-600 pr-4">¿El paciente se muestra satisfecho?
                                    </p>
                                    <div class="toggle-group">
                                        <label><input type="radio" name="contenido[calidad][satisfaccion]"
                                                value="SI" class="toggle-radio"
                                                {{ ($registro->calidad_paciente_satisfecho ?? '') == 'SI' ? 'checked' : '' }}><span
                                                class="toggle-btn"><i data-lucide="check" class="w-3 h-3"></i>
                                                SÍ</span></label>
                                        <label><input type="radio" name="contenido[calidad][satisfaccion]"
                                                value="NO" class="toggle-radio"
                                                {{ ($registro->calidad_paciente_satisfecho ?? '') == 'NO' ? 'checked' : '' }}><span
                                                class="toggle-btn"><i data-lucide="x" class="w-3 h-3"></i>
                                                NO</span></label>
                                    </div>
                                </div>
                                {{-- Pregunta 3 --}}
                                <div class="bg-slate-50 rounded-lg border border-slate-100 p-3">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-bold text-slate-600 pr-4">¿Se utilizan reportes del
                                            sistema?
                                        </p>
                                        <div class="toggle-group">
                                            <label><input type="radio" name="contenido[calidad][reportes]"
                                                    value="SI" class="toggle-radio" onchange="toggleReportes(true)"
                                                    {{ ($registro->calidad_usa_reportes ?? '') == 'SI' ? 'checked' : '' }}><span
                                                    class="toggle-btn"><i data-lucide="check" class="w-3 h-3"></i>
                                                    SÍ</span></label>
                                            <label><input type="radio" name="contenido[calidad][reportes]"
                                                    value="NO" class="toggle-radio" onchange="toggleReportes(false)"
                                                    {{ ($registro->calidad_usa_reportes ?? '') == 'NO' ? 'checked' : '' }}><span
                                                    class="toggle-btn"><i data-lucide="x" class="w-3 h-3"></i>
                                                    NO</span></label>
                                        </div>
                                    </div>
                                    <div id="div-reportes-detalle"
                                        class="{{ ($registro->calidad_usa_reportes ?? '') == 'SI' ? '' : 'hidden' }} mt-3 pt-3 border-t border-slate-200/50">

                                        @php
                                            $opcionesSocializa = [
                                                'PERSONAL DEL SERVICIO',
                                                'JEFE DE ESTABLECIMIENTO',
                                                'ESTADISTICO',
                                                'UNIDAD EJECUTORA',
                                                'DIRESA',
                                                'OTROS',
                                            ];
                                            $valorSocializa = $registro->calidad_socializa_con ?? '';
                                        @endphp
                                        <label class="block text-xs font-medium text-indigo-700 mb-1">
                                            ¿Con quién lo socializa?
                                        </label>
                                        <div class="relative">
                                            <select name="contenido[calidad][reportes_socializa]"
                                                class="input-blue text-xs w-full appearance-none border-indigo-200 focus:border-indigo-500 text-indigo-700 rounded-md py-2 pl-3 pr-8">
                                                <option value="">Seleccione...</option>
                                                @foreach ($opcionesSocializa as $opcion)
                                                    <option value="{{ $opcion }}"
                                                        {{ $valorSocializa == $opcion ? 'selected' : '' }}>
                                                        {{ $opcion }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <span
                                                class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Dificultades (Soporte) --}}
                        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="bg-slate-50 px-4 py-3 border-b border-slate-100 flex items-center gap-2">
                                <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wide">Soporte /
                                    Dificultades</h3>
                            </div>
                            <div class="p-5 space-y-6">
                                <div>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">1. ¿A
                                        quién comunica?</p>
                                    <div class="grid grid-cols-3 gap-3">
                                        @foreach (['MINSA', 'DIRESA', 'OTROS', 'UNIDAD EJECUTORA', 'JEFE DE ESTABLECIMIENTO'] as $opcion)
                                            <label class="cursor-pointer group relative">
                                                <input type="radio" name="contenido[dificultades][comunica]"
                                                    value="{{ $opcion }}" class="peer sr-only"
                                                    {{ ($registro->dificultad_comunica_a ?? '') == $opcion ? 'checked' : '' }}>
                                                <div
                                                    class="text-center py-3 px-2 rounded-lg border border-slate-200 bg-white transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 peer-checked:shadow-sm group-hover:border-indigo-300">
                                                    <span
                                                        class="block text-xs font-bold text-slate-500 peer-checked:text-indigo-700">{{ $opcion }}</span>
                                                </div>
                                                <div
                                                    class="absolute -top-2 -right-2 bg-indigo-600 text-white rounded-full p-0.5 opacity-0 peer-checked:opacity-100 transition-opacity shadow-sm">
                                                    <i data-lucide="check" class="w-2 h-2"></i>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="h-px bg-slate-100"></div>
                                <div>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-3">2. ¿Qué
                                        medio utiliza?</p>
                                    <div class="grid grid-cols-3 gap-3">
                                        @foreach (['WHATSAPP', 'CELULAR', 'CORREO', 'OTROS'] as $opcion)
                                            <label class="cursor-pointer group relative">
                                                <input type="radio" name="contenido[dificultades][medio]"
                                                    value="{{ $opcion }}" class="peer sr-only"
                                                    {{ ($registro->dificultad_medio_uso ?? '') == $opcion ? 'checked' : '' }}>
                                                <div
                                                    class="text-center py-3 px-2 rounded-lg border border-slate-200 bg-white transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50/50 peer-checked:shadow-sm group-hover:border-indigo-300">
                                                    <span
                                                        class="block text-xs font-bold text-slate-500 peer-checked:text-indigo-700">{{ $opcion }}</span>
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
                </div>
            </div>

            {{-- ======================================================================== --}}
            {{-- SECCIÓN: EVIDENCIAS FOTOGRÁFICAS                                       --}}
            {{-- ======================================================================== --}}
            <div class="form-card section-container mt-6">
                <div class="mb-6 border-b border-slate-100 pb-4 flex items-center gap-3">
                    <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                        <i data-lucide="camera" class="w-6 h-6"></i>
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
                            <div class="mb-4">
                                <i data-lucide="hard-drive" class="w-10 h-10 text-slate-400 mx-auto mb-2"></i>
                                <p class="text-sm font-bold text-slate-600">Seleccionar archivos alojados en el Hosting
                                </p>
                            </div>
                            <button type="button" onclick="openServerModal()"
                                class="bg-slate-800 text-white px-5 py-2.5 rounded-lg text-xs font-bold hover:bg-slate-900 transition flex items-center justify-center gap-2 mx-auto">
                                <i data-lucide="search" class="w-4 h-4"></i> ABRIR EXPLORADOR DE ARCHIVOS
                            </button>
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
                <button type="submit" class="btn-nav btn-finish w-full py-4 text-lg">
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
            if (typeof updateRolText === 'function') updateRolText();

            // A. CONECTAR EL EVENTO CHANGE
            const selectTipo = document.getElementById('personal_tipo_doc');
            if (selectTipo) {
                selectTipo.addEventListener('change', function() {
                    verificarVisibilidadSeccionDni(this.value);
                });
                verificarVisibilidadSeccionDni(selectTipo.value);
            }

            // B. Verificar estado inicial de SIHCE
            const sihceValue = document.querySelector('input[name="contenido[utiliza_sihce]"]:checked');
            if (sihceValue && sihceValue.value === 'SI') {
                toggleSihce(true);
            } else {
                toggleSihce(false);
            }

            // C. Cargar fotos guardadas
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

        // --- 3. LÓGICA DE BÚSQUEDA Y DATOS ---
        async function buscarPorDoc() {
            const doc = document.getElementById('personal_dni').value.trim();
            const loader = document.getElementById('loading-doc');
            const msg = document.getElementById('msg-doc');

            if (doc.length < 5) return;

            loader.classList.remove('hidden');
            msg.classList.add('hidden');

            try {
                // Paso 1: Búsqueda Local
                const response = await fetch(
                    `{{ route('usuario.monitoreo.citas.buscar.profesional') }}?type=doc&q=${doc}&local_only=1`);
                const data = await response.json();

                if (data.length > 0) {
                    rellenarDatos(data[0]);
                    msg.textContent = "Personal encontrado.";
                    msg.className = "text-[10px] text-green-600 mt-1";
                    msg.classList.remove('hidden');
                } else {
                    // Paso 2: Búsqueda en API Externa (RENIEC / Decolecta) si es DNI (8 dígitos)
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

                        const responseExt = await fetch(
                            `{{ route('usuario.monitoreo.citas.buscar.profesional') }}?type=doc&q=${doc}`);
                        const dataExt = await responseExt.json();
                        
                        Swal.close();

                        if (dataExt.length > 0 && dataExt[0].exists_external) {
                            rellenarDatos(dataExt[0]);
                            msg.textContent = "Extraído de RENIEC. Complete los demás datos.";
                            msg.className = "text-[10px] text-indigo-600 mt-1 font-bold";
                            msg.classList.remove('hidden');
                            
                            const Toast = Swal.mixin({
                                toast: true, position: 'top-end', showConfirmButton: false, timer: 4000, timerProgressBar: true
                            });
                            Toast.fire({ icon: 'info', title: 'Nombres encontrados en RENIEC. Complete teléfono/celular.' });
                        } else {
                            mostrarMsgNuevoCitas(msg);
                        }
                    } else {
                        mostrarMsgNuevoCitas(msg);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (Swal.isVisible()) Swal.close();
            } finally {
                loader.classList.add('hidden');
            }
        }

        function mostrarMsgNuevoCitas(msg) {
            msg.textContent = "Personal nuevo. Complete los nombres y se guardará automáticamente.";
            msg.className = "text-[10px] text-blue-600 mt-1 font-bold";
            msg.classList.remove('hidden');
            document.getElementById('personal_nombre').value = '';
            document.getElementById('personal_nombre').focus();
        }

        function buscarPorNombre() {
            const query = document.getElementById('personal_nombre').value;
            const lista = document.getElementById('lista-sugerencias');
            clearTimeout(timeoutNombre);

            if (query.length < 3) {
                lista.classList.add('hidden');
                lista.innerHTML = '';
                return;
            }

            timeoutNombre = setTimeout(async () => {
                try {
                    const response = await fetch(
                        `{{ route('usuario.monitoreo.citas.buscar.profesional') }}?type=name&q=${query}`);
                    const data = await response.json();
                    lista.innerHTML = '';

                    if (data.length > 0) {
                        lista.classList.remove('hidden');
                        data.forEach(prof => {
                            const nombreCompleto =
                                `${prof.apellido_paterno} ${prof.apellido_materno} ${prof.nombres}`;
                            const item = document.createElement('div');
                            item.className =
                                "p-2 hover:bg-slate-100 cursor-pointer border-b border-slate-100 text-xs";
                            item.innerHTML =
                                `<strong>${nombreCompleto}</strong> <span class="text-slate-400">(${prof.tipo_doc}: ${prof.doc})</span>`;
                            item.onclick = () => {
                                rellenarDatos(prof);
                                lista.classList.add('hidden');
                            };
                            lista.appendChild(item);
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
            if (!lista.contains(e.target) && e.target !== input) {
                lista.classList.add('hidden');
            }
        });

        function rellenarDatos(prof) {
            const nombreCompleto = `${prof.apellido_paterno} ${prof.apellido_materno} ${prof.nombres}`.trim();
            document.getElementById('personal_nombre').value = nombreCompleto;
            document.getElementById('personal_dni').value = prof.doc;

            const selectTipo = document.getElementById('personal_tipo_doc');
            if (['DNI', 'CE'].includes(prof.tipo_doc)) {
                selectTipo.value = prof.tipo_doc;
            } else {
                selectTipo.value = 'OTRO';
            }
            verificarVisibilidadSeccionDni(selectTipo.value);

            const correo = prof.email || prof.correo || '';
            const celular = prof.celular || prof.telefono || '';
            document.getElementById('personal_correo').value = correo;
            document.getElementById('personal_celular').value = celular;
        }

        window.toggleRolDropdown = function() {
            document.getElementById('rol-dropdown-list').classList.toggle('hidden');
        }
        window.updateRolText = function() {
            const checkboxes = document.querySelectorAll('#rol-dropdown-list input[type="checkbox"]:checked');
            const textSpan = document.getElementById('rol-selected-text');
            if (!textSpan) return;
            if (checkboxes.length === 0) {
                textSpan.textContent = '-- Seleccionar --';
                textSpan.classList.remove('text-indigo-600', 'font-bold');
            } else {
                const values = Array.from(checkboxes).map(cb => cb.value);
                textSpan.textContent = values.length <= 2 ? values.join(', ') : `${values.length} Seleccionados`;
                textSpan.classList.add('text-indigo-600', 'font-bold');
            }
        }
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('rol-dropdown-list');
            const button = e.target.closest('button[onclick="toggleRolDropdown()"]');
            if (dropdown && !button && !dropdown.contains(e.target) && !dropdown.classList.contains('hidden'))
                dropdown.classList.add('hidden');
        });

        window.switchTab = function(tab) {
            const panelLocal = document.getElementById('panel-local');
            const panelServer = document.getElementById('panel-server');
            const btnLocal = document.getElementById('tab-local');
            const btnServer = document.getElementById('tab-server');
            const activeClass = ['text-indigo-600', 'border-b-2', 'border-indigo-600'];
            const inactiveClass = ['text-slate-400', 'hover:text-indigo-500'];
            if (tab === 'local') {
                panelLocal.classList.remove('hidden');
                panelServer.classList.add('hidden');
                btnLocal.classList.add(...activeClass);
                btnLocal.classList.remove(...inactiveClass);
                btnServer.classList.remove(...activeClass);
            } else {
                panelLocal.classList.add('hidden');
                panelServer.classList.remove('hidden');
                btnServer.classList.add(...activeClass);
                btnServer.classList.remove(...inactiveClass);
                btnLocal.classList.remove(...activeClass);
            }
        }
        window.openServerModal = function() {
            alert("🚧 MANTENIMIENTO 🚧\nExplorador habilitado en producción.");
        }
        window.toggleCapacitacion = function(show) {
            const div = document.getElementById('div-capacitacion-detalles');
            if (div) show ? div.classList.remove('hidden') : div.classList.add('hidden');
        }
        window.toggleReportes = function(show) {
            const div = document.getElementById('div-reportes-detalle');
            if (div) show ? div.classList.remove('hidden') : div.classList.add('hidden');
        }

        // --- LÓGICA CLAVE DE VISIBILIDAD SIHCE (Corregida) ---
        window.toggleSihce = function(show) {
            // 1. Bloque de Declaración Jurada y Confidencialidad
            const bloqueSeguridad = document.getElementById('bloque-seguridad-sihce');
            // 2. Bloque COMPLETO de Gestión, Calidad, Ventanillas y Producción
            const bloqueGestionCompleto = document.getElementById('seccion-gestion-completa');

            if (show) {
                if (bloqueSeguridad) bloqueSeguridad.classList.remove('hidden');

                if (bloqueGestionCompleto) {
                    bloqueGestionCompleto.classList.remove('hidden');
                    bloqueGestionCompleto.style.display = '';
                }
            } else {
                if (bloqueSeguridad) bloqueSeguridad.classList.add('hidden');

                if (bloqueGestionCompleto) {
                    bloqueGestionCompleto.classList.add('hidden');
                    bloqueGestionCompleto.style.display = 'none'; // Fuerza el ocultamiento
                }
            }
        }

        // --- TABLAS DINÁMICAS ---
        function generateId() {
            return Date.now() + Math.floor(Math.random() * 1000);
        }

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
                <div class="relative flex items-center w-full bg-white border border-slate-200 rounded shadow-sm focus-within:ring-1 focus-within:ring-indigo-500 focus-within:border-indigo-500 overflow-hidden">
                    
                    <input type="hidden" class="input-serie-final" 
                           name="contenido[equipos][${equipoIndex}][serie]" value="">
                    
                    <div class="bg-slate-50 border-r border-slate-200">
                        <select onchange="actualizarSerieConcatenada(this)"
                                class="select-prefix h-7 bg-transparent border-none text-[10px] font-bold text-slate-700 focus:ring-0 cursor-pointer pl-2 pr-6 py-0">
                            <option value="S">S</option>
                            <option value="CP">CP</option>
                        </select>
                    </div>
                    
                    <input type="text" id="serie-input-${equipoIndex}" 
                           oninput="actualizarSerieConcatenada(this)"
                           class="input-valor w-full border-none bg-transparent text-[11px] font-mono uppercase text-slate-600 focus:ring-0 px-2 py-1 placeholder-slate-300" 
                           placeholder="DIGITE...">
                           
                    <button type="button" onclick="iniciarEscaneo('serie-input-${equipoIndex}')" 
                            class="pr-2 pl-1 text-slate-400 hover:text-indigo-600 cursor-pointer transition-colors"
                            title="Escanear">
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
            const contenedor = elemento.closest('.relative.flex');
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
        window.agregarFilaProduccion = function(tableId) {
            const tbody = document.querySelector(`#${tableId} tbody`);
            if (!tbody) return;
            const id = generateId();
            const tr = document.createElement('tr');
            tr.innerHTML =
                `<td><input type="text" name="contenido[produccion][${id}][nombre]" class="table-input font-bold text-indigo-600" placeholder="Nuevo Servicio..."></td><td><input type="number" name="contenido[produccion][${id}][cantidad]" placeholder="0" class="table-input text-center font-bold text-indigo-600 bg-indigo-50/50"></td><td class="text-center"><button type="button" onclick="this.closest('tr').remove()" class="p-1 rounded text-slate-300 hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button></td>`;
            tbody.appendChild(tr);
            lucide.createIcons();
        }

        // --- EVIDENCIAS & ESCÁNER ---
        window.handleFiles = function(files) {
            if (!files.length) return;
            Array.from(files).forEach(file => {
                if (evidenceList.length >= MAX_PHOTOS) evidenceList.shift();
                evidenceList.push({
                    type: 'local',
                    file: file,
                    url: URL.createObjectURL(file),
                    name: file.name,
                    id: generateId()
                });
            });
            renderGallery();
            syncInputs();
        }
        window.removeImage = function(id) {
            evidenceList = evidenceList.filter(item => item.id !== id);
            renderGallery();
            syncInputs();
        }

        function renderGallery() {
            const container = document.getElementById('gallery-container');
            const countDisplay = document.getElementById('count-display');
            const emptyState = document.getElementById('empty-state');
            if (!container) return;
            Array.from(container.children).forEach(c => {
                if (c.id !== 'empty-state') container.removeChild(c);
            });
            if (evidenceList.length === 0) {
                if (emptyState) emptyState.style.display = 'block';
                if (countDisplay) countDisplay.innerText = `0 / ${MAX_PHOTOS}`;
                return;
            }
            if (emptyState) emptyState.style.display = 'none';
            evidenceList.forEach(item => {
                const displayUrl = (item.type === 'server' && !item.url.startsWith('http')) 
                    ? `{{ asset('storage') }}/${item.url}` 
                    : item.url;

                const div = document.createElement('div');
                div.className =
                    'relative group h-96 rounded-xl overflow-hidden border border-slate-200 shadow-sm bg-white animate-fade-in';
                div.innerHTML =
                    `<img src="${displayUrl}" class="w-full h-full object-cover rounded-xl shadow-inner shadow-black/10 transition-transform duration-500 group-hover:scale-105"><button type="button" onclick="removeImage(${item.id})" class="absolute top-2 right-2 bg-red-500/80 backdrop-blur-sm text-white p-1.5 rounded-full shadow-lg hover:bg-red-600 transition-all z-20"><i data-lucide="x" class="w-3.5 h-3.5"></i></button><div class="absolute top-2 left-2 ${item.type === 'local' ? 'bg-indigo-600/90' : 'bg-emerald-600/90'} backdrop-blur-sm text-white text-[9px] font-black tracking-widest px-3 py-1 rounded-full shadow-lg z-20 uppercase"><span>${item.type}</span></div>`;
                container.appendChild(div);
            });
            if (countDisplay) countDisplay.innerText = `${evidenceList.length} / ${MAX_PHOTOS}`;
            lucide.createIcons();
        }

        function syncInputs() {
            const dt = new DataTransfer();
            evidenceList.filter(i => i.type === 'local').forEach(i => dt.items.add(i.file));
            const input = document.getElementById('final-input-files');
            if (input) input.files = dt.files;
            const serverFiles = evidenceList.filter(i => i.type === 'server').map(i => i.url);
            document.getElementById('final-input-server').value = JSON.stringify(serverFiles);
        }

        let html5QrcodeScanner = null;
        let currentInputId = null;

        function iniciarEscaneo(inputId) {
            currentInputId = inputId;
            const modal = document.getElementById('scanner-modal');
            modal.classList.remove('hidden');
            html5QrcodeScanner = new Html5Qrcode("reader");
            html5QrcodeScanner.start({
                facingMode: "environment"
            }, {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            }, onScanSuccess, () => {}).catch(err => {
                console.error(err);
                alert("Error cámara");
                modal.classList.add('hidden');
            });
        }

        function onScanSuccess(decodedText) {
            if (currentInputId) document.getElementById(currentInputId).value = decodedText;
            detenerEscaneo();
        }

        function detenerEscaneo() {
            if (html5QrcodeScanner) html5QrcodeScanner.stop().then(() => {
                html5QrcodeScanner.clear();
                document.getElementById('scanner-modal').classList.add('hidden');
                currentInputId = null;
            }).catch(console.error);
            else document.getElementById('scanner-modal').classList.add('hidden');
        }

        // --- OPCIONES DNIe ---
        function toggleDniOptions(tipo) {
            const container = document.getElementById('dnie-options-container');
            if (!container) return;
            if (tipo === 'ELECTRONICO') container.classList.remove('hidden');
            else container.classList.add('hidden');
        }
        document.addEventListener("DOMContentLoaded", function() {
            const selectedDni = document.querySelector('input[name="contenido[tipo_dni_fisico]"]:checked');
            if (selectedDni) toggleDniOptions(selectedDni.value);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        });
    </script>
@endpush
