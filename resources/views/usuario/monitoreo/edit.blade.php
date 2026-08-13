@extends('layouts.usuario')

@section('title', 'Editar Acta de Monitoreo #' . $monitoreo->id)

@push('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        /* Animaciones */
        @keyframes slide-up {
            from {
                opacity: 0;
                transform: translateY(15px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-up {
            animation: slide-up 0.4s ease-out forwards;
        }

        /* Autocomplete Custom */
        .ui-autocomplete {
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            border: 1px solid #e2e8f0 !important;
            padding: 0.5rem !important;
            background: white !important;
            font-family: inherit;
            z-index: 50 !important;
        }

        .ui-menu-item-wrapper {
            padding: 10px 14px !important;
            border-radius: 0.5rem !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
            color: #475569 !important;
        }

        .ui-state-active {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border: none !important;
            font-weight: 700 !important;
        }

        /* Upload Zone */
        .drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 1rem;
            height: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: #f8fafc;
        }

        .drop-zone:hover {
            border-color: #6366f1;
            background-color: #eef2ff;
        }

        .preview-img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 1rem;
            display: none;
        }

        /* Inputs para la tabla */
        .input-table {
            width: 100%;
            background: transparent;
            border: none;
            padding: 4px 0;
            font-size: 0.85rem;
            color: #334155;
            font-weight: 500;
        }

        .input-table:focus {
            outline: none;
            border-bottom: 2px solid #6366f1;
        }

        /* Ajuste selects en tabla */
        select.input-table {
            background-color: transparent;
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-slate-50 py-10 px-4 sm:px-6 lg:px-8 font-sans text-slate-600">
        <div class="max-w-5xl mx-auto">

            {{-- HEADER --}}
            <div class="mb-10 text-center animate-slide-up">
                <span
                    class="inline-block py-1 px-3 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase tracking-widest mb-3">Editando
                    Registro #{{ $monitoreo->id }}</span>
                <h1 class="text-3xl font-black text-slate-900 tracking-tight uppercase">Acta de Monitoreo</h1>
                <p class="text-slate-500 text-sm mt-2 max-w-2xl mx-auto">Modifique la información del establecimiento o el
                    equipo responsable para esta acta.</p>
            </div>

            {{-- ALERTA DE ERRORES --}}
            @if ($errors->any())
                <div
                    class="mb-8 rounded-2xl bg-white p-6 border-l-4 border-red-500 shadow-lg shadow-slate-200/50 animate-slide-up">
                    <div class="flex items-start gap-4">
                        <div class="p-2 bg-red-100 rounded-full text-red-500">
                            <i data-lucide="alert-triangle" class="h-6 w-6"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">No se pudo actualizar el acta</h3>
                            <ul class="mt-2 space-y-1 text-sm text-red-600 font-medium">
                                @foreach ($errors->all() as $error)
                                    <li class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 bg-red-500 rounded-full"></div> {{ $error }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form id="monitoreoForm" action="{{ route('usuario.monitoreo.update', $monitoreo->id) }}" method="POST"
                enctype="multipart/form-data" class="space-y-8 animate-slide-up">
                @csrf
                @method('PUT')
                <input type="hidden" name="implementador" id="implementador_input" value="{{ $monitoreo->implementador }}">
                <input type="hidden" name="redirect_to" id="redirect_to" value="index">

                {{-- 1. TARJETA: DATOS GENERALES (IMPLEMENTADOR & FECHA) --}}
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-slate-200/60 border border-slate-100">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                        <div class="bg-slate-900 p-2.5 rounded-xl text-white">
                            <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800 uppercase tracking-wide">1. Datos Generales</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Implementador --}}
                        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Implementador
                                Responsable</label>
                            <div class="flex items-center gap-3">
                                <div
                                    class="h-10 w-10 rounded-full bg-white border border-slate-200 flex items-center justify-center text-indigo-600 shadow-sm">
                                    <i data-lucide="user" class="w-5 h-5"></i>
                                </div>
                                <span class="text-sm font-bold text-slate-700 uppercase">
                                    {{ Auth::user()->apellido_paterno }} {{ Auth::user()->apellido_materno }}
                                    {{ Auth::user()->name }}
                                </span>
                            </div>
                        </div>

                        {{-- Fecha --}}
                        <div class="bg-indigo-50 rounded-2xl p-5 border border-indigo-100">
                            <label class="text-[10px] font-black text-indigo-400 uppercase tracking-widest block mb-2">Fecha
                                de Monitoreo</label>
                            <div class="flex items-center gap-3">
                                <i data-lucide="calendar" class="w-5 h-5 text-indigo-600"></i>
                                <input type="date" name="fecha" value="{{ $monitoreo->fecha }}" required
                                    class="bg-transparent border-0 p-0 text-lg font-black text-indigo-900 focus:ring-0 w-full cursor-pointer">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. TARJETA: ESTABLECIMIENTO --}}
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-slate-200/60 border border-slate-100">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                        <div class="bg-indigo-600 p-2.5 rounded-xl text-white">
                            <i data-lucide="building-2" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800 uppercase tracking-wide">2. Datos del Establecimiento
                        </h2>
                    </div>

                    <div class="space-y-6">
                        {{-- Buscador Grande --}}
                        <div class="relative">
                            <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Buscar IPRESS (Escriba
                                nombre o código)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i data-lucide="search" class="h-5 w-5 text-slate-400"></i>
                                </div>
                                <input type="text" id="establecimiento_search" required autocomplete="off"
                                    placeholder="Ej: HOSPITAL REGIONAL..."
                                    value="{{ $monitoreo->establecimiento->nombre ?? '' }}"
                                    class="block w-full pl-11 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-slate-800 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all uppercase">
                                <input type="hidden" name="establecimiento_id" id="establecimiento_id"
                                    value="{{ $monitoreo->establecimiento_id }}" required>
                            </div>
                        </div>

                        {{-- Grid de Datos Automáticos --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            {{-- Categoría (Editable) --}}
                            <div
                                class="col-span-2 md:col-span-1 p-4 rounded-2xl border-2 border-dashed border-indigo-200 bg-indigo-50/30">
                                <label class="text-[9px] font-black text-indigo-500 uppercase block mb-1">Categoría
                                    (Edit)</label>
                                <input type="text" id="categoria" name="categoria"
                                    value="{{ $monitoreo->categoria_congelada }}"
                                    class="w-full bg-transparent border-0 p-0 text-sm font-black text-indigo-700 focus:ring-0 placeholder-indigo-300 uppercase"
                                    placeholder="---">
                            </div>

                            {{-- Datos Readonly --}}
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <label class="text-[9px] font-bold text-slate-400 uppercase block mb-1">Provincia</label>
                                <input type="text" id="provincia"
                                    value="{{ $monitoreo->establecimiento->provincia ?? '—' }}" readonly
                                    class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-700 focus:ring-0">
                            </div>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <label class="text-[9px] font-bold text-slate-400 uppercase block mb-1">Distrito</label>
                                <input type="text" id="distrito"
                                    value="{{ $monitoreo->establecimiento->distrito ?? '—' }}" readonly
                                    class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-700 focus:ring-0">
                            </div>
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <label class="text-[9px] font-bold text-slate-400 uppercase block mb-1">Red</label>
                                <input type="text" id="red"
                                    value="{{ $monitoreo->establecimiento->red ?? '—' }}" readonly
                                    class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-700 focus:ring-0">
                            </div>
                            <div class="col-span-2 md:col-span-4 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                                <label class="text-[9px] font-bold text-slate-400 uppercase block mb-1">Microred</label>
                                <input type="text" id="microred"
                                    value="{{ $monitoreo->establecimiento->microred ?? '—' }}" readonly
                                    class="w-full bg-transparent border-0 p-0 text-xs font-bold text-slate-700 focus:ring-0">
                            </div>
                        </div>

                        {{-- Jefe --}}
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase mb-2 block">Jefe del Establecimiento
                                (Editable)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <i data-lucide="user-cog" class="h-5 w-5 text-slate-400"></i>
                                </div>
                                <input type="text" name="responsable" id="responsable"
                                    value="{{ $monitoreo->responsable }}" required
                                    class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-700 focus:border-indigo-500 focus:ring-indigo-500 uppercase">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. TARJETA: EVIDENCIAS --}}
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-slate-200/60 border border-slate-100">
                    <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                        <div class="bg-emerald-500 p-2.5 rounded-xl text-white">
                            <i data-lucide="camera" class="w-5 h-5"></i>
                        </div>
                        <h2 class="text-lg font-bold text-slate-800 uppercase tracking-wide">3. Evidencia Fotográfica</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @for ($i = 1; $i <= 2; $i++)
                            @php
                                $fotoField = 'foto' . $i;
                                $hasFoto = !empty($monitoreo->$fotoField);
                            @endphp
                            <div class="relative group">
                                <input type="file" name="imagenes[]" id="file_{{ $i }}" accept="image/*"
                                    class="hidden file-input">

                                <label for="file_{{ $i }}" class="drop-zone" id="label_{{ $i }}"
                                    style="{{ $hasFoto ? 'display: none;' : '' }}">
                                    <div
                                        class="h-12 w-12 bg-white rounded-full flex items-center justify-center shadow-md mb-3 group-hover:scale-110 transition-transform">
                                        <i data-lucide="image-plus" class="w-6 h-6 text-emerald-500"></i>
                                    </div>
                                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cambiar Foto
                                        {{ $i }}</span>
                                </label>

                                <img src="{{ $hasFoto ? asset('storage/' . $monitoreo->$fotoField) : '#' }}"
                                    id="preview_{{ $i }}" class="preview-img shadow-lg"
                                    style="{{ $hasFoto ? 'display: block;' : '' }}">

                                <button type="button" onclick="resetFile({{ $i }})"
                                    id="remove_{{ $i }}"
                                    class="absolute top-2 right-2 h-8 w-8 bg-white/90 backdrop-blur text-red-500 rounded-full flex items-center justify-center shadow-lg hover:bg-red-500 hover:text-white transition-all {{ $hasFoto ? '' : 'hidden' }}">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- 4. TARJETA: EQUIPO (FULL WIDTH) --}}
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-slate-200/60 border border-slate-100">
                    <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="bg-orange-500 p-2.5 rounded-xl text-white">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <h2 class="text-lg font-bold text-slate-800 uppercase tracking-wide">4. Equipo de Monitoreo
                            </h2>
                        </div>
                    </div>

                    {{-- BUSCADOR EQUIPO --}}
                    <div id="componente_busqueda" class="bg-slate-50 p-6 rounded-2xl border border-slate-200 mb-8">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="bg-slate-900 text-white text-[10px] font-bold px-2 py-0.5 rounded">PASO 1</div>
                            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wide">Buscar Profesional en MPI
                            </h3>
                        </div>

                        {{-- Render del Componente de Búsqueda --}}
                        @php $dummy = (object)['contenido' => []]; @endphp
                        <x-busqueda-profesional :detalle="$dummy" prefix="busqueda_temporal" />

                        <div class="mt-4 flex justify-end">
                            <button type="button" id="btn_agregar_a_tabla"
                                class="bg-slate-900 text-white px-6 py-3 rounded-xl text-xs font-bold uppercase tracking-wider hover:bg-slate-800 transition-all flex items-center gap-2 shadow-lg shadow-slate-300/50">
                                <i data-lucide="user-plus" class="w-4 h-4"></i>
                                Agregar a la Lista
                            </button>
                        </div>
                    </div>

                    {{-- TABLA --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="bg-slate-900 text-white text-[10px] font-bold px-2 py-0.5 rounded">PASO 2</div>
                            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wide">Listado de Integrantes
                            </h3>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-slate-200 shadow-sm">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-slate-100/50 text-xs uppercase font-bold text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">Tipo</th>
                                        <th class="px-6 py-4">Documento</th>
                                        <th class="px-6 py-4">Apellidos</th>
                                        <th class="px-6 py-4">Nombres</th>
                                        <th class="px-6 py-4">Cargo</th>
                                        <th class="px-6 py-4">Institución</th>
                                        <th class="px-6 py-4 text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="body_equipo" class="divide-y divide-slate-100 bg-white">
                                    <tr id="empty_row">
                                        <td colspan="7" class="py-12 text-center">
                                            <div class="flex flex-col items-center justify-center opacity-40">
                                                <i data-lucide="users" class="w-12 h-12 text-slate-300 mb-2"></i>
                                                <p class="text-sm font-bold text-slate-500">No hay integrantes agregados
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- BOTONES DE ACTUALIZAR (MANTENIENDO OPCIONES DEL EDIT ANTERIOR) --}}
                <div class="pt-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button type="button" onclick="submitForm('modulos')"
                            class="w-full py-4 bg-slate-800 rounded-2xl text-white font-black text-sm uppercase tracking-widest shadow-xl hover:bg-slate-900 transition-all flex items-center justify-center gap-3">
                            <i data-lucide="layers" class="w-5 h-5 text-indigo-400"></i>
                            Actualizar y Editar Módulos
                        </button>
                        <button type="button" onclick="submitForm('index')"
                            class="w-full py-4 bg-indigo-600 rounded-2xl text-white font-black text-sm uppercase tracking-widest shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:scale-[1.01] transition-all flex items-center justify-center gap-3">
                            <i data-lucide="save" class="w-5 h-5"></i>
                            Solo Actualizar Cabecera
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            lucide.createIcons();

            // Cargar equipo existente
            @if ($monitoreo->equipo && count($monitoreo->equipo) > 0)
                $('#empty_row').hide();
                @foreach ($monitoreo->equipo as $persona)
                    addMiembroRow({
                        tipo_doc: "{{ $persona->tipo_doc }}",
                        doc: "{{ $persona->doc }}",
                        apellido_paterno: "{{ $persona->apellido_paterno }}",
                        apellido_materno: "{{ $persona->apellido_materno }}",
                        nombres: "{{ $persona->nombres }}",
                        cargo: "{{ $persona->cargo }}",
                        institucion: "{{ $persona->institucion }}"
                    });
                @endforeach
            @endif

            // 1. Lógica de Imágenes
            $(".file-input").on("change", function() {
                const id = $(this).attr('id').split('_')[1];
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $(`#preview_${id}`).attr('src', e.target.result).show();
                        $(`#label_${id}`).hide();
                        $(`#remove_${id}`).removeClass('hidden').css('display', 'flex');
                    }
                    reader.readAsDataURL(file);
                }
            });

            window.resetFile = function(id) {
                $(`#file_${id}`).val("");
                $(`#preview_${id}`).hide();
                $(`#label_${id}`).show();
                $(`#remove_${id}`).addClass('hidden').hide();
            };

            // 2. Autocomplete Establecimiento
            $("#establecimiento_search").autocomplete({
                minLength: 2,
                source: "{{ route('establecimientos.buscar') }}",
                select: function(e, ui) {
                    $("#establecimiento_id").val(ui.item.id);
                    $("#distrito").val(ui.item.distrito || '—');
                    $("#provincia").val(ui.item.provincia || '—');
                    $("#categoria").val(ui.item.categoria || '—');
                    $("#red").val(ui.item.red || '—');
                    $("#microred").val(ui.item.microred || '—');
                    $("#responsable").val(ui.item.responsable || '');
                    setTimeout(() => $("#categoria").focus(), 100);
                    return true;
                }
            });

            // 3. AGREGAR EQUIPO
            $('#btn_agregar_a_tabla').on('click', function() {
                // Usamos selectores comodín para atrapar los inputs del componente externo
                const tipoDoc = $('#componente_busqueda select[name*="[tipo_doc]"]').val();
                const doc = $('#componente_busqueda input[name*="[doc]"]').val();
                const apePat = $('#componente_busqueda input[name*="[apellido_paterno]"]').val();
                const apeMat = $('#componente_busqueda input[name*="[apellido_materno]"]').val();
                const nombres = $('#componente_busqueda input[name*="[nombres]"]').val();

                if (!doc || !apePat || !nombres) {
                    Swal.fire({
                        title: 'Faltan Datos',
                        text: 'Por favor busque y seleccione un profesional válido.',
                        icon: 'warning',
                        confirmButtonColor: '#4f46e5'
                    });
                    return;
                }

                const data = {
                    tipo_doc: tipoDoc,
                    doc: doc,
                    apellido_paterno: apePat,
                    apellido_materno: apeMat,
                    nombres: nombres,
                    cargo: 'IMPLEMENTADOR',
                    institucion: 'DIRESA'
                };

                addMiembroRow(data);

                // Limpiar
                $('#componente_busqueda input').val('');
                $('#componente_busqueda select').prop('selectedIndex', 0);
            });

            function addMiembroRow(data) {
                $('#empty_row').hide();
                const doc = data.doc;

                if ($(`#row_${doc}`).length > 0) {
                    Swal.fire('Duplicado', 'Este integrante ya está en la lista.', 'info');
                    return;
                }

                const row = `
            <tr id="row_${doc}" class="hover:bg-slate-50 transition-colors animate-slide-up">
                <td class="px-6 py-4">
                    <select name="equipo[${doc}][tipo_doc]" class="input-table font-bold text-xs bg-slate-100 rounded px-2 py-1">
                        <option value="DNI" ${(data.tipo_doc == 'DNI') ? 'selected' : ''}>DNI</option>
                        <option value="CE" ${(data.tipo_doc == 'CE') ? 'selected' : ''}>CE</option>
                    </select>
                </td>
                <td class="px-6 py-4">
                    <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-1 rounded">${doc}</span>
                    <input type="hidden" name="equipo[${doc}][doc]" value="${doc}">
                </td>
                <td class="px-6 py-4">
                    <div class="flex flex-col">
                        <input type="text" name="equipo[${doc}][apellido_paterno]" value="${data.apellido_paterno}" class="input-table font-bold" readonly>
                        <input type="text" name="equipo[${doc}][apellido_materno]" value="${data.apellido_materno}" class="input-table text-xs text-slate-400" readonly>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <input type="text" name="equipo[${doc}][nombres]" value="${data.nombres}" class="input-table" readonly>
                </td>
                <td class="px-6 py-4">
                    <input type="text" name="equipo[${doc}][cargo]" value="${data.cargo}" class="input-table text-indigo-600 font-bold bg-indigo-50/50 px-2 rounded focus:bg-white" required>
                </td>
                <td class="px-6 py-4">
                    <select name="equipo[${doc}][institucion]" class="input-table text-xs">
                        <option value="DIRESA" ${(data.institucion == 'DIRESA') ? 'selected' : ''}>DIRESA</option>
                        <option value="MINSA" ${(data.institucion == 'MINSA') ? 'selected' : ''}>MINSA</option>
                        <option value="U.E RED DE SALUD ICA" ${(data.institucion == 'U.E RED DE SALUD ICA') ? 'selected' : ''}>U.E RED SALUD</option>
                        <option value="ESTABLECIMIENTO" ${(data.institucion == 'ESTABLECIMIENTO') ? 'selected' : ''}>ESTABLECIMIENTO</option>
                        <option value="OTRO" ${(data.institucion == 'OTRO') ? 'selected' : ''}>OTRO</option>
                    </select>
                </td>
                <td class="px-6 py-4 text-center">
                    <button type="button" onclick="$(this).closest('tr').remove()" class="p-2 bg-red-50 text-red-500 rounded-lg hover:bg-red-500 hover:text-white transition-all">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </td>
            </tr>`;

                $('#body_equipo').append(row);
                lucide.createIcons();
            }

            // 4. SUBMIT
            window.submitForm = function(destination) {
                $('#redirect_to').val(destination);
                $('#monitoreoForm').submit();
            };

            $('#monitoreoForm').on('submit', function(e) {
                e.preventDefault();

                if (!$('#establecimiento_id').val()) {
                    Swal.fire('Falta Establecimiento',
                        'Debe buscar y seleccionar un establecimiento válido.', 'warning');
                    return;
                }

                if ($('#body_equipo tr').not('#empty_row').length === 0) {
                    Swal.fire('Equipo Vacío', 'Debe agregar al menos un integrante al equipo.', 'warning');
                    return;
                }

                $('#componente_busqueda input, #componente_busqueda select').prop('disabled', true);

                $(this).find('input[type="text"]').not('[name^="busqueda_temporal"]').each(function() {
                    $(this).val($(this).val().toUpperCase().trim());
                });

                $('#implementador_input').val(
                    "{{ Auth::user()->apellido_paterno }} {{ Auth::user()->apellido_materno }} {{ Auth::user()->name }}"
                    .toUpperCase());

                Swal.fire({
                    title: '¿Actualizar Acta?',
                    text: "Se guardarán los cambios y se actualizará el acta actual.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, Actualizar',
                    confirmButtonColor: '#4f46e5',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('monitoreoForm').submit();
                    } else {
                        $('#componente_busqueda input, #componente_busqueda select').prop(
                            'disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
