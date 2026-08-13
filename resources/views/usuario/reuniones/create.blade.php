@extends('layouts.usuario')
@section('title', 'Crear Acta de Reunión')

@push('styles')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .section-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1); margin-bottom: 2rem; overflow: hidden; }
        .section-header { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.5rem; background: #1e293b; color: #f8fafc; font-weight: 600; font-size: 0.95rem; letter-spacing: 0.025em; }
        .section-body { padding: 1.5rem; }
        .inp { width: 100%; border: 1px solid #94a3b8; border-radius: 0.375rem; padding: 0.5rem 0.75rem; font-size: 0.875rem; transition: all 0.2s; background: #fff; color: #1e293b; font-weight: 500; }
        .inp:focus { outline:none; border-color:#4f46e5; box-shadow:0 0 0 3px rgba(79,70,229,0.15); }
        .lbl { display:block; font-size:0.75rem; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px; }
        .tbl { width:100%; border-collapse:collapse; font-size:0.85rem; }
        .tbl th { background: #f1f5f9; border-bottom: 1px solid #e2e8f0; color:#475569; padding:0.6rem 0.4rem; font-weight:700; text-align:center; font-size:0.75rem; letter-spacing:0.02em; white-space:nowrap; text-transform: uppercase;}
        .tbl td { border-bottom:1px solid #e2e8f0; padding:0.4rem 0.4rem; vertical-align:middle; }
        .tbl input, .tbl select { border:1px solid #cbd5e1; border-radius:0.375rem; padding:0.35rem 0.45rem; width:100%; font-size:0.85rem; background:#fff; transition:all 0.15s; }
        .tbl input:focus, .tbl select:focus { outline:none; border-color:#4f46e5; box-shadow:0 0 0 2px rgba(79,70,229,0.15); }
        .btn-lupa { background:#f1f5f9; color:#0f172a; border:1px solid #cbd5e1; border-radius:0.375rem; padding:0.3rem 0.5rem; cursor:pointer; font-size:0.85rem; transition:all 0.15s; }
        .btn-lupa:hover { background:#e2e8f0; color:#0f172a; border-color:#94a3b8; }
        .btn-del { background:#fff1f2; color:#be123c; border:1px solid #fecdd3; border-radius:0.375rem; padding:0.3rem 0.5rem; cursor:pointer; font-size:0.85rem; transition:all 0.15s; }
        .btn-del:hover { background:#fecdd3; color:#9f1239; }
        .btn-add { display:inline-flex; align-items:center; gap:0.4rem; padding:0.45rem 1rem; border-radius:0.375rem; font-size:0.85rem; font-weight:600; cursor:pointer; border:1px solid transparent; transition:all 0.2s; background:#4f46e5; color:#fff; }
        .btn-add:hover { background:#4338ca; box-shadow:0 4px 6px -1px rgba(0,0,0,0.1); }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
@endpush

@section('header-content')
    <div>
        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Crear Acta de Reunión</h2>
        <div class="flex items-center gap-2 text-xs text-slate-500 mt-1 font-medium">
            <span class="text-indigo-600">Operaciones</span>
            <span>&bull;</span>
            <span>Reuniones</span>
        </div>
    </div>
@endsection

@section('content')
    <div class="min-h-screen pb-10 pt-4 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-10 text-center">
                <h2 class="text-3xl font-black text-slate-800 tracking-tight">Acta de Reunión</h2>
                <p class="text-slate-500 text-sm mt-2 max-w-2xl mx-auto">Complete la información general y acuerdos de la reunión</p>
            </div>

            @if ($errors->any())
                <div class="max-w-4xl mx-auto mb-8 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-xl shadow-sm">
                    <ul class="text-sm text-red-700 list-disc list-inside font-semibold">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form id="actaForm" action="{{ route('usuario.reuniones.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- INFORMACIÓN GENERAL --}}
                <div class="section-card">
                    <div class="section-header">&#128197; Información de la Reunión</div>
                    <div class="section-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-4">
                            <div>
                                <label class="lbl">Título de Reunión</label>
                                <input type="text" name="titulo_reunion" value="{{ old('titulo_reunion', $reunion->titulo_reunion ?? '') }}" required class="inp uppercase" oninput="this.value = this.value.toUpperCase()" placeholder="Ingrese el título">
                            </div>
                            <div>
                                <label class="lbl">Nombre Institución</label>
                                <input type="text" name="nombre_institucion" value="{{ old('nombre_institucion', $reunion->nombre_institucion ?? '') }}" required class="inp uppercase" oninput="this.value = this.value.toUpperCase()" placeholder="Nombre de la institución">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-4">
                            <div>
                                <label class="lbl">Fecha</label>
                                <input type="date" name="fecha_reunion" value="{{ old('fecha_reunion', $reunion->fecha_reunion ?? date('Y-m-d')) }}" required class="inp">
                            </div>
                            <div>
                                <label class="lbl">Hora de Inicio</label>
                                <input type="time" name="hora_reunion" value="{{ old('hora_reunion', $reunion->hora_reunion ?? date('H:i')) }}" required class="inp">
                            </div>
                            <div>
                                <label class="lbl">Hora Finalizada</label>
                                <input type="time" name="hora_finalizada_reunion" value="{{ old('hora_finalizada_reunion', $reunion->hora_finalizada_reunion ?? '') }}" class="inp">
                            </div>
                        </div>
                        <div>
                            <label class="lbl">Descripción General</label>
                            <textarea name="descripcion_general" rows="4" required class="inp" placeholder="Detalles generales de la reunión...">{{ old('descripcion_general', $reunion->descripcion_general ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- PARTICIPANTES --}}
                <div class="section-card">
                    <div class="section-header">&#128101; Relación de Participantes</div>
                    <div class="section-body" style="padding:1rem">
                        <div class="overflow-x-auto">
                            <table class="tbl">
                                <thead>
                                    <tr>
                                        <th style="width:36px">N°</th>
                                        <th style="width:170px">Documento / DNI</th>
                                        <th>Apellidos</th>
                                        <th>Nombres</th>
                                        <th style="width:180px">Cargo</th>
                                        <th>Institución</th>
                                        <th style="width:100px">Celular</th>
                                        <th style="width:52px">Acc.</th>
                                    </tr>
                                </thead>
                                <tbody id="tabla-participantes">
                                    @php
                                        $participantes    = old('participantes', $reunion->participantes ?? []);
                                        // Datos del usuario autenticado (pasados desde el controlador)
                                        $dniCreador       = $usuarioActual->documento ?? $usuarioActual->username ?? '';
                                        $apellidosCreador = trim(($usuarioActual->apellido_paterno ?? '') . ' ' . ($usuarioActual->apellido_materno ?? ''));
                                        $nombresCreador   = $usuarioActual->name ?? '';
                                        // $cargoDefault, $institucionDefault y $celularCreador vienen del controlador
                                    @endphp
                                    @if(count($participantes) === 0)
                                        <tr style="background: linear-gradient(90deg, #eef2ff 0%, #f8fafc 100%); border-left: 3px solid #4f46e5;">
                                            <td class="text-center font-bold text-indigo-600">1</td>
                                            <td>
                                                <div style="display:flex;gap:3px;align-items:center">
                                                    <input type="text" name="participantes[0][dni]" data-base="dni" value="{{ strtoupper($dniCreador) }}" placeholder="Documento" style="flex:1;min-width:70px">
                                                    <button type="button" class="btn-lupa buscar-participante" title="Buscar">&#128269;</button>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="participantes[0][apellidos]" data-base="apellidos" value="{{ strtoupper($apellidosCreador) }}" placeholder="Apellidos" required class="uppercase" oninput="this.value = this.value.toUpperCase()">
                                            </td>
                                            <td>
                                                <input type="text" name="participantes[0][nombres]" data-base="nombres" value="{{ strtoupper($nombresCreador) }}" placeholder="Nombres" required class="uppercase" oninput="this.value = this.value.toUpperCase()">
                                            </td>
                                            <td>
                                                <input type="text" name="participantes[0][cargo]" data-base="cargo" value="{{ $cargoDefault }}" placeholder="Cargo" class="uppercase" oninput="this.value = this.value.toUpperCase()">
                                            </td>
                                            <td>
                                                <input type="text" name="participantes[0][institucion]" data-base="institucion" value="{{ $institucionDefault }}" placeholder="Institución" class="uppercase" oninput="this.value = this.value.toUpperCase()">
                                            </td>
                                            <td><input type="text" name="participantes[0][celular]" data-base="celular" value="{{ $celularCreador ?? '' }}" placeholder="Celular"></td>
                                            <td class="text-center">
                                                <div style="display:flex;flex-direction:column;align-items:center;gap:3px">
                                                    <span style="font-size:0.6rem;font-weight:700;color:#4f46e5;background:#eef2ff;border:1px solid #c7d2fe;border-radius:4px;padding:1px 5px;white-space:nowrap;">CREADOR</span>
                                                    <button type="button" class="btn-del eliminar-fila" title="Eliminar">&#10006;</button>
                                                </div>
                                            </td>
                                            <input type="hidden" name="participantes[0][tipo_documento]" data-base="tipo_documento" value="DNI">
                                        </tr>
                                    @else
                                        @foreach($participantes as $i => $p)
                                        <tr>
                                            <td class="text-center font-bold text-indigo-600">{{ $i+1 }}</td>
                                            <td>
                                                <div style="display:flex;gap:3px;align-items:center">
                                                    <input type="text" name="participantes[{{ $i }}][dni]" data-base="dni" value="{{ $p['dni'] ?? '' }}" style="flex:1;min-width:70px">
                                                    <button type="button" class="btn-lupa buscar-participante" title="Buscar">&#128269;</button>
                                                </div>
                                            </td>
                                            <td><input type="text" name="participantes[{{ $i }}][apellidos]" data-base="apellidos" value="{{ $p['apellidos'] ?? '' }}" required class="uppercase" oninput="this.value = this.value.toUpperCase()"></td>
                                            <td><input type="text" name="participantes[{{ $i }}][nombres]" data-base="nombres" value="{{ $p['nombres'] ?? '' }}" required class="uppercase" oninput="this.value = this.value.toUpperCase()"></td>
                                            <td><input type="text" name="participantes[{{ $i }}][cargo]" data-base="cargo" value="{{ $p['cargo'] ?? '' }}" class="uppercase" oninput="this.value = this.value.toUpperCase()"></td>
                                            <td><input type="text" name="participantes[{{ $i }}][institucion]" data-base="institucion" value="{{ $p['institucion'] ?? '' }}" class="uppercase" oninput="this.value = this.value.toUpperCase()"></td>
                                            <td><input type="text" name="participantes[{{ $i }}][celular]" data-base="celular" value="{{ $p['celular'] ?? '' }}" placeholder="Celular"></td>
                                            <td class="text-center"><button type="button" class="btn-del eliminar-fila" title="Eliminar">&#10006;</button></td>
                                            <input type="hidden" name="participantes[{{ $i }}][tipo_documento]" data-base="tipo_documento" value="{{ $p['tipo_documento'] ?? 'DNI' }}">
                                        </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <button type="button" id="agregar-participante" class="btn-add mt-3">&#43; Agregar participante</button>
                    </div>
                </div>

                {{-- Template participante --}}
                <template id="fila-participante">
                    <tr>
                        <td class="text-center font-bold text-indigo-600"></td>
                        <td>
                            <div style="display:flex;gap:3px;align-items:center">
                                <input type="text" data-base="dni" placeholder="Doc..." style="flex:1;min-width:70px">
                                <button type="button" class="btn-lupa buscar-participante" title="Buscar">&#128269;</button>
                            </div>
                        </td>
                        <td><input type="text" data-base="apellidos" placeholder="Apellidos" required class="uppercase" oninput="this.value = this.value.toUpperCase()"></td>
                        <td><input type="text" data-base="nombres" placeholder="Nombres" required class="uppercase" oninput="this.value = this.value.toUpperCase()"></td>
                        <td><input type="text" data-base="cargo" placeholder="Cargo" class="uppercase" oninput="this.value = this.value.toUpperCase()"></td>
                        <td><input type="text" data-base="institucion" placeholder="Institución" class="uppercase" oninput="this.value = this.value.toUpperCase()"></td>
                        <td><input type="text" data-base="celular" placeholder="Celular"></td>
                        <td class="text-center"><button type="button" class="btn-del eliminar-fila" title="Eliminar">&#10006;</button></td>
                        <input type="hidden" data-base="tipo_documento" value="DNI">
                    </tr>
                </template>

                {{-- ACUERDOS --}}
                <div class="section-card">
                    <div class="section-header">&#129309; Acuerdos</div>
                    <div class="section-body" style="padding:1rem">
                        <table class="tbl">
                            <thead><tr><th style="width:36px">N°</th><th>Descripción</th><th style="width:52px">Acc.</th></tr></thead>
                            <tbody id="tabla-acuerdos">
                                @php $acuerdos = old('acuerdos', $reunion->acuerdos ?? []); @endphp
                                @if(count($acuerdos) === 0)
                                    <tr>
                                        <td class="text-center font-bold text-indigo-600">1</td>
                                        <td><input type="text" name="acuerdos[0][descripcion]" required placeholder="Describa el acuerdo..."></td>
                                        <td class="text-center"><button type="button" class="btn-del eliminar-fila-generica">&#10006;</button></td>
                                    </tr>
                                @else
                                    @foreach($acuerdos as $i => $ac)
                                        <tr>
                                            <td class="text-center font-bold text-indigo-600">{{ $i+1 }}</td>
                                            <td><input type="text" name="acuerdos[{{ $i }}][descripcion]" value="{{ $ac['descripcion'] ?? '' }}" required></td>
                                            <td class="text-center"><button type="button" class="btn-del eliminar-fila-generica">&#10006;</button></td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <button type="button" id="agregar-acuerdo" class="btn-add mt-3">&#43; Acuerdo</button>
                    </div>
                </div>

                {{-- OBSERVACIONES --}}
                <div class="section-card">
                    <div class="section-header">&#128203; Comentarios / Observaciones</div>
                    <div class="section-body" style="padding:1rem">
                        <table class="tbl">
                            <thead><tr><th style="width:36px">N°</th><th>Descripción</th><th style="width:52px">Acc.</th></tr></thead>
                            <tbody id="tabla-observaciones">
                                @php $observaciones = old('observaciones', $reunion->comentarios_observaciones ?? []); @endphp
                                @if(count($observaciones) === 0)
                                    <tr>
                                        <td class="text-center font-bold text-indigo-600">1</td>
                                        <td><input type="text" name="observaciones[0][descripcion]" required placeholder="Escriba el comentario u observación..."></td>
                                        <td class="text-center"><button type="button" class="btn-del eliminar-fila-generica">&#10006;</button></td>
                                    </tr>
                                @else
                                    @foreach($observaciones as $i => $ob)
                                        <tr>
                                            <td class="text-center font-bold text-indigo-600">{{ $i+1 }}</td>
                                            <td><input type="text" name="observaciones[{{ $i }}][descripcion]" value="{{ $ob['descripcion'] ?? '' }}" required></td>
                                            <td class="text-center"><button type="button" class="btn-del eliminar-fila-generica">&#10006;</button></td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <button type="button" id="agregar-observacion" class="btn-add mt-3">&#43; Observación</button>
                    </div>
                </div>

                {{-- IMÁGENES --}}
                <div class="section-card">
                    <div class="section-header">&#128247; Evidencia Fotográfica (máx. 2)</div>
                    <div class="section-body">
                        <div id="drop-area" style="border:2px dashed #cbd5e1;border-radius:0.5rem;padding:2rem;text-align:center;background:#f8fafc;cursor:pointer;transition:all 0.2s">
                            <p style="color:#475569;font-weight:600">&#128247; Haz clic o arrastra imágenes aquí</p>
                            <p style="color:#64748b;font-size:0.78rem;margin-top:4px">(máx. 2 imágenes, 2 MB cada una)</p>
                            <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple class="hidden">
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px">
                            <p id="file-counter" style="font-size:0.82rem;color:#64748b">0 / 2 imágenes</p>
                            <button type="button" id="clear-all" class="hidden" style="font-size:0.78rem;color:#e11d48;font-weight:600">Quitar todas</button>
                        </div>
                        <div id="thumbnails" style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:1rem"></div>
                    </div>
                </div>

                <div class="text-center mt-6 pb-4">
                    <button type="button" id="btnGuardar" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-md text-lg transition-colors">
                        &#128190; Guardar Acta
                    </button>
                </div>

            </form>
        </div>
    </div>
    <div id="toast-container" class="fixed bottom-4 right-4 space-y-2 z-50"></div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // URL base generada por Laravel (soporta subdirectorios)
    const BUSCAR_PARTICIPANTE_URL = '{{ url("usuario/actas-reunion/participante/buscar") }}';
    const prefixMap = {
        '#tabla-participantes': 'participantes',
        '#tabla-acuerdos': 'acuerdos',
        '#tabla-observaciones': 'observaciones'
    };

    function reindexRows(tbodySelector) {
        $(tbodySelector).find('tr').each(function(i){
            $(this).find('td').first().text(i + 1);
            $(this).find('input, select').each(function(){
                const $el = $(this);
                const base = $el.data('base');
                if (base) {
                    const prefix = prefixMap[tbodySelector];
                    $el.attr('name', `${prefix}[${i}][${base}]`);
                } else if ($el.attr('name')) {
                    const oldName = $el.attr('name');
                    $el.attr('name', oldName.replace(/\[\d+\]/, `[${i}]`));
                }
            });
        });
    }

    // Agregar y Eliminar filas Generales
    $(document).on('click', '.eliminar-fila-generica, .eliminar-fila', function() {
        const tbody = '#' + $(this).closest('tbody').attr('id');
        $(this).closest('tr').remove();
        reindexRows(tbody);
    });

    $('#agregar-acuerdo').click(function() {
        $('#tabla-acuerdos').append(`<tr><td class="text-center font-bold text-indigo-600"></td><td><input type="text" data-base="descripcion" name="" required class="inp" style="padding:0.35rem 0.45rem;"></td><td class="text-center"><button type="button" class="btn-del eliminar-fila-generica">&#10006;</button></td></tr>`);
        reindexRows('#tabla-acuerdos');
    });

    $('#agregar-observacion').click(function() {
        $('#tabla-observaciones').append(`<tr><td class="text-center font-bold text-indigo-600"></td><td><input type="text" data-base="descripcion" name="" required class="inp" style="padding:0.35rem 0.45rem;"></td><td class="text-center"><button type="button" class="btn-del eliminar-fila-generica">&#10006;</button></td></tr>`);
        reindexRows('#tabla-observaciones');
    });

    const tplPart = document.getElementById('fila-participante');
    $('#agregar-participante').click(function() {
        $('#tabla-participantes').append(tplPart.content.cloneNode(true));
        reindexRows('#tabla-participantes');
    });

    // Subida de imágenes (Drag & Drop UI)
    const inputFiles = document.getElementById('imagenes');
    const dropArea = document.getElementById('drop-area');
    const thumbnails = document.getElementById('thumbnails');
    const counter = document.getElementById('file-counter');
    const clearBtn = document.getElementById('clear-all');
    let fileArray = [];

    dropArea.addEventListener('click', () => inputFiles.click());
    inputFiles.addEventListener('change', (e) => handleFiles(e.target.files));

    function handleFiles(files) {
        for(let file of files) {
            if (fileArray.length >= 2) break;
            fileArray.push(file);
        }
        updateImages();
    }

    clearBtn.addEventListener('click', () => { fileArray = []; updateImages(); });

    function updateImages() {
        const dt = new DataTransfer();
        thumbnails.innerHTML = '';
        fileArray.forEach((f, idx) => {
            dt.items.add(f);
            const reader = new FileReader();
            reader.onload = e => {
                const div = document.createElement('div');
                div.className = "relative border border-gray-300 rounded-lg shadow-sm bg-white p-2 w-32 h-32 flex items-center justify-center";
                div.innerHTML = `<img src="${e.target.result}" style="max-height:100%; max-width:100%; border-radius:4px;"><button type="button" data-idx="${idx}" class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow-md border border-white quit-img">✖</button>`;
                thumbnails.appendChild(div);
            }
            reader.readAsDataURL(f);
        });
        inputFiles.files = dt.files;
        counter.textContent = `${fileArray.length} / 2 imágenes`;
        clearBtn.classList.toggle('hidden', fileArray.length === 0);
    }

    $(document).on('click', '.quit-img', function(e) {
        e.stopPropagation();
        fileArray.splice($(this).data('idx'), 1);
        updateImages();
    });

    // Guardar SWAL
    $('#btnGuardar').click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: '¿Guardar Acta?',
            text: "Asegúrate de haber completado todos los datos.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#e11d48',
            confirmButtonText: 'Sí, guardar'
        }).then((result) => {
            if (result.isConfirmed) $('#actaForm').submit();
        });
    });

    // Búsqueda de participantes (ruta propia del módulo de reuniones)
    $(document).on('click', '.buscar-participante', function() {
        const fila = $(this).closest('tr');
        const dniInput = fila.find('input[data-base="dni"]');
        const doc = dniInput.val().trim();

        if (doc.length < 6) return Swal.fire({ icon:'warning', title:'Documento muy corto', text:'Ingrese al menos 6 dígitos.' });

        Swal.fire({
            title: 'Buscando...', html: 'Validando en base de datos local.', allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        // Paso 1: búsqueda local
        fetch(`${BUSCAR_PARTICIPANTE_URL}/${encodeURIComponent(doc)}?local_only=1`)
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.json();
            })
            .then(data => {
                if (data.exists) {
                    fila.find('input[data-base="apellidos"]').val(((data.apellido_paterno||'') + ' ' + (data.apellido_materno||'')).trim().toUpperCase());
                    fila.find('input[data-base="nombres"]').val((data.nombres||'').toUpperCase());
                    fila.find('input[data-base="cargo"]').val((data.cargo||'').toUpperCase());
                    Swal.fire({ icon:'success', title:'Encontrado', timer:1500, showConfirmButton:false });
                } else if (doc.length === 8) {
                    // Paso 2: consulta a API externa (RENIEC/MPI)
                    Swal.update({ html: 'No encontrado localmente. Consultando RENIEC...' });
                    return fetch(`${BUSCAR_PARTICIPANTE_URL}/${encodeURIComponent(doc)}`)
                        .then(r => {
                            if (!r.ok) throw new Error(`HTTP ${r.status}`);
                            return r.json();
                        })
                        .then(ext => {
                            if (ext.exists_external) {
                                fila.find('input[data-base="apellidos"]').val(((ext.apellido_paterno||'') + ' ' + (ext.apellido_materno||'')).trim().toUpperCase());
                                fila.find('input[data-base="nombres"]').val((ext.nombres||'').toUpperCase());
                                const fuente = ext.fuente === 'decolecta' ? 'RENIEC (Decolecta)' : (ext.fuente || 'RENIEC');
                                Swal.fire({ icon:'info', title:'Encontrado externamente', text:'Fuente: ' + fuente, timer:2000, showConfirmButton:false });
                            } else if (ext.quota_exceeded) {
                                Swal.fire({ icon:'warning', title:'Límite de consultas RENIEC excedido', text:'Ingrese los datos manualmente.' });
                            } else {
                                Swal.fire({ icon:'warning', title:'No encontrado', text:'Ingrese los datos manualmente.' });
                            }
                        });
                } else {
                    Swal.fire({ icon:'warning', title:'No encontrado', text:'Ingrese los datos manualmente.' });
                }
            })
            .catch(err => Swal.fire({ icon:'error', title:'Error en la conexión', text: err.message || 'Intente nuevamente.' }));
    });
</script>
@endpush
