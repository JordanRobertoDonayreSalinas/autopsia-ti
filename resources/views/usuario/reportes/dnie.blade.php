@extends('layouts.usuario')

@section('title', 'Reporte de DNI Electrónico')

@section('header-content')
    <div>
        <h1 class="text-2xl font-bold text-slate-800">💳 Reporte de DNI Electrónico</h1>
        <p class="text-sm text-slate-500 mt-1">Sube tu lista de personal y obtén el estado DNIe de cada documento</p>
    </div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- ── Mensajes de Error ────────────────────────────────────────────────── --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0 mt-0.5">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
            </div>
            <div>
                <p class="font-bold text-red-700 text-sm">Ocurrió un error</p>
                @foreach ($errors->all() as $error)
                    <p class="text-red-600 text-sm mt-1">{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Instrucciones ─────────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
            <h3 class="text-sm font-bold text-slate-800">¿Cómo funciona?</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm shrink-0">1</div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Prepara tu archivo Excel</p>
                    <p class="text-xs text-slate-500 mt-1">Debe contener las columnas: <strong>IPRESS, ESTABLECIMIENTO, CAT, DISTRITO, PROVINCIA, MICRORED, RED, PROFESION, TIPO_DOC, DOC_PERSONAL, APELLIDO_PATERNO_PERSONAL, APELLIDO_MATERNO_PERSONAL, NOMBRES_PERSONAL</strong>.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm shrink-0">2</div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Sube el archivo</p>
                    <p class="text-xs text-slate-500 mt-1">Arrastra o selecciona tu archivo .xlsx o .xls. El sistema leerá la primera hoja automáticamente.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">3</div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Descarga el reporte</p>
                    <p class="text-xs text-slate-500 mt-1">Recibirás un Excel enriquecido con el estado DNIe de cada persona: si tiene DNI electrónico, la versión, y si el certificado está vigente.</p>
                </div>
            </div>
        </div>

        {{-- Columnas de salida --}}
        <div class="px-5 pb-5">
            <p class="text-xs font-bold text-slate-500 uppercase mb-2">Columnas que se añaden al reporte de salida (columnas N–S):</p>
            <div class="flex flex-wrap gap-2">
                @foreach([
                    ['label' => '¿Tiene DNI Electrónico?', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                    ['label' => 'Versión DNIe', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                    ['label' => '¿Certificado Activo?', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                    ['label' => 'Vigencia del Certificado', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                    ['label' => 'Fuente de Versión', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                    ['label' => 'Estado Consulta', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
                ] as $col)
                    <span class="px-3 py-1 rounded-full text-xs font-semibold border {{ $col['color'] }}">{{ $col['label'] }}</span>
                @endforeach
            </div>
        </div>

        {{-- Leyenda de colores --}}
        <div class="px-5 pb-5 border-t border-slate-100 pt-4">
            <p class="text-xs font-bold text-slate-500 uppercase mb-2">Leyenda de colores en el Excel de salida:</p>
            <div class="flex flex-wrap gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded" style="background:#D4EDDA; border:1px solid #ccc;"></span>
                    <span class="text-xs text-slate-600">DNIe Activo</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded" style="background:#FFF3CD; border:1px solid #ccc;"></span>
                    <span class="text-xs text-slate-600">Certificado digital vencido</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded" style="background:#F8D7DA; border:1px solid #ccc;"></span>
                    <span class="text-xs text-slate-600">Sin DNI Electrónico</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded" style="background:#E2E3E5; border:1px solid #ccc;"></span>
                    <span class="text-xs text-slate-600">No aplica (CE, pasaporte, etc.)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded" style="background:#FFE5B4; border:1px solid #ccc;"></span>
                    <span class="text-xs text-slate-600">Error de consulta</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Formulario de Carga ────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center gap-2">
            <i data-lucide="upload-cloud" class="w-4 h-4 text-purple-600"></i>
            <h3 class="text-sm font-bold text-slate-800">Subir archivo Excel</h3>
        </div>

        <form id="uploadForm" method="POST" action="{{ route('usuario.reportes.dnie.procesar') }}" enctype="multipart/form-data" class="p-6">
            @csrf

            {{-- Zona Drag & Drop --}}
            <div id="dropZone"
                 class="relative border-2 border-dashed border-slate-300 rounded-2xl p-10 text-center cursor-pointer transition-all hover:border-purple-400 hover:bg-purple-50/30 group"
                 onclick="document.getElementById('archivoInput').click()">

                <div id="dropIcon" class="flex flex-col items-center gap-3 pointer-events-none">
                    <div class="w-16 h-16 rounded-2xl bg-purple-50 flex items-center justify-center group-hover:bg-purple-100 transition-colors">
                        <i data-lucide="file-spreadsheet" class="w-8 h-8 text-purple-500"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-700">Arrastra tu archivo aquí</p>
                        <p class="text-sm text-slate-500 mt-1">o haz clic para seleccionarlo</p>
                        <p class="text-xs text-slate-400 mt-2">Formatos aceptados: <strong>.xlsx, .xls</strong> — Máximo <strong>500 filas</strong></p>
                    </div>
                </div>

                {{-- Preview del archivo seleccionado --}}
                <div id="filePreview" class="hidden flex-col items-center gap-2">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center">
                        <i data-lucide="file-check" class="w-8 h-8 text-emerald-500"></i>
                    </div>
                    <div>
                        <p id="fileName" class="font-semibold text-slate-700 text-sm"></p>
                        <p id="fileSize" class="text-xs text-slate-400 mt-0.5"></p>
                        <button type="button" id="clearFile"
                                onclick="event.stopPropagation(); clearFile()"
                                class="mt-2 text-xs text-red-500 hover:text-red-700 font-medium flex items-center gap-1 mx-auto">
                            <i data-lucide="x" class="w-3 h-3"></i> Quitar archivo
                        </button>
                    </div>
                </div>

                <input type="file" id="archivoInput" name="archivo" accept=".xlsx,.xls" class="sr-only">
            </div>

            {{-- Botón de envío --}}
            <div class="flex items-center justify-between mt-5">
                <div class="text-xs text-slate-400 flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-green-500"></i>
                    Los datos se consultan directamente a RENIEC vía PKI. No se almacenan en el servidor.
                </div>
                <button type="submit" id="submitBtn"
                        class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white text-sm font-bold rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all flex items-center gap-2 shadow-md shadow-purple-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Generar Reporte Excel
                </button>
            </div>
        </form>
    </div>

    {{-- ── Barra de Progreso (visible durante el envío) ─────────────────── --}}
    <div id="progressCard" class="hidden bg-white rounded-2xl shadow-sm border border-purple-200 p-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
                <div class="w-6 h-6 border-3 border-purple-600 border-t-transparent rounded-full animate-spin"></div>
            </div>
            <div class="flex-1">
                <p class="font-bold text-slate-800 text-sm">Procesando consultas a RENIEC...</p>
                <p class="text-xs text-slate-500 mt-1">Este proceso puede tardar unos minutos dependiendo del número de registros. Por favor espera.</p>
                <div class="mt-3 h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-purple-500 to-indigo-500 rounded-full animate-pulse" style="width: 60%"></div>
                </div>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-3 gap-3 text-center">
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="text-xs text-slate-500">Fuente</p>
                <p class="text-sm font-bold text-slate-800 mt-0.5">PKI RENIEC</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="text-xs text-slate-500">Caché</p>
                <p class="text-sm font-bold text-slate-800 mt-0.5">60 minutos</p>
            </div>
            <div class="bg-slate-50 rounded-xl p-3">
                <p class="text-xs text-slate-500">Resultado</p>
                <p class="text-sm font-bold text-slate-800 mt-0.5">Descarga .xlsx</p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    lucide.createIcons();

    const dropZone      = document.getElementById('dropZone');
    const archivoInput  = document.getElementById('archivoInput');
    const dropIcon      = document.getElementById('dropIcon');
    const filePreview   = document.getElementById('filePreview');
    const fileNameEl    = document.getElementById('fileName');
    const fileSizeEl    = document.getElementById('fileSize');
    const submitBtn     = document.getElementById('submitBtn');
    const uploadForm    = document.getElementById('uploadForm');
    const progressCard  = document.getElementById('progressCard');

    // ── Drag & Drop events ────────────────────────────────────────────────
    ['dragenter', 'dragover'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => {
            e.preventDefault();
            dropZone.classList.add('border-purple-500', 'bg-purple-50/50');
        });
    });

    ['dragleave', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-purple-500', 'bg-purple-50/50');
        });
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            archivoInput.files = files;
            handleFile(files[0]);
        }
    });

    archivoInput.addEventListener('change', () => {
        if (archivoInput.files.length > 0) {
            handleFile(archivoInput.files[0]);
        }
    });

    function handleFile(file) {
        const validTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
        ];
        const validExt = /\.(xlsx|xls)$/i.test(file.name);

        if (!validExt) {
            alert('Por favor selecciona un archivo .xlsx o .xls');
            return;
        }

        const sizeMB = (file.size / 1024 / 1024).toFixed(2);

        fileNameEl.textContent  = file.name;
        fileSizeEl.textContent  = `${sizeMB} MB`;

        dropIcon.classList.add('hidden');
        dropIcon.classList.remove('flex');
        filePreview.classList.remove('hidden');
        filePreview.classList.add('flex');

        submitBtn.removeAttribute('disabled');

        lucide.createIcons();
    }

    function clearFile() {
        archivoInput.value = '';
        filePreview.classList.add('hidden');
        filePreview.classList.remove('flex');
        dropIcon.classList.remove('hidden');
        dropIcon.classList.add('flex');
        submitBtn.setAttribute('disabled', '');
        lucide.createIcons();
    }

    // ── Mostrar barra de progreso y enviar vía Fetch ─────────────────────
    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!archivoInput.files.length) {
            alert('Por favor selecciona un archivo antes de continuar.');
            return;
        }

        const originalBtnText = submitBtn.innerHTML;
        submitBtn.setAttribute('disabled', '');
        submitBtn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Procesando...';

        progressCard.classList.remove('hidden');
        progressCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        try {
            const formData = new FormData(uploadForm);
            const response = await fetch(uploadForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const json = await response.json();
                    alert(json.message || 'Error inesperado del servidor');
                } else {
                    // Es un archivo Blob
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    
                    let filename = 'Reporte_DNIe.xlsx';
                    const disposition = response.headers.get('content-disposition');
                    if (disposition && disposition.indexOf('attachment') !== -1) {
                        const matches = /filename[^;=\n]*=(([\'"]).*?\2|[^\;\n]*)/.exec(disposition);
                        if (matches != null && matches[1]) {
                            filename = matches[1].replace(/[\'"]/g, '');
                        }
                    }
                    
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    
                    // Reiniciar el file input
                    clearFile();
                }
            } else {
                const json = await response.json();
                let errMsg = 'Error al procesar el archivo.';
                if (json.errors && json.errors.archivo) {
                    errMsg = json.errors.archivo.join('\n');
                }
                alert(errMsg);
            }
        } catch (error) {
            console.error(error);
            alert('Ocurrió un error de red al intentar subir el archivo.');
        } finally {
            submitBtn.removeAttribute('disabled');
            submitBtn.innerHTML = originalBtnText;
            progressCard.classList.add('hidden');
        }
    });
</script>
@endpush
