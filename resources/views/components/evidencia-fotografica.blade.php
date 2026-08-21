@props([
    'evidencias' => [],
    'qrUrl',
    'estadoUrl',
    'max' => 10,
    'label' => 'Fotografías / Evidencia Adjunta',
])

{{--
    Componente reutilizable de evidencia fotográfica: lista dinámica de fotos
    con descripción (añadir/quitar/reemplazar) + subida desde el celular por
    código QR. Usado por el consultorio dinámico, RR.HH., y cualquier otra
    sección que necesite el mismo comportamiento: basta con pasarle la lista
    de evidencias ya guardadas y las URLs de generarQr()/estado() del backend
    correspondiente (ambas deben operar sobre contenido['evidencias'] con el
    mismo formato [['path'=>...,'descripcion'=>...], ...]).
--}}
<div>
    <div class="flex items-center justify-between mb-2">
        <label class="block text-slate-700 text-xs font-black uppercase tracking-wider flex items-center gap-1.5">
            <i data-lucide="camera" class="w-4 h-4 text-slate-400"></i> {{ $label }} (Máximo {{ $max }}, Opcional)
        </label>
        <div class="flex items-center gap-2">
            <button type="button" id="btn_evidencia_movil" onclick="abrirEvidenciaMovil()"
                    class="group flex items-center gap-1.5 px-3.5 py-2 bg-white text-indigo-600 border-2 border-indigo-200 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50 transition-all shadow-sm active:scale-95">
                <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
                Desde el Celular
            </button>
            <button type="button" id="btn_add_evidencia" onclick="addEvidenciaRow()"
                    class="group flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-md active:scale-95">
                <i data-lucide="plus-circle" class="w-3.5 h-3.5 group-hover:rotate-90 transition-transform duration-300"></i>
                Añadir Fotografía
            </button>
        </div>
    </div>

    <div id="container_evidencias" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" data-count="{{ count($evidencias) }}">
        @foreach ($evidencias as $idx => $ev)
            <div class="evidencia-card bg-slate-50 rounded-2xl border-2 border-indigo-200 p-3 shadow-sm" data-idx="{{ $idx }}">
                <input type="hidden" name="evidencias[{{ $idx }}][path_existente]" value="{{ $ev['path'] }}">
                <div class="relative group">
                    <img id="img_preview_evidencia_{{ $idx }}"
                         src="{{ asset('storage/' . $ev['path']) }}"
                         alt="Evidencia {{ $idx + 1 }}"
                         class="h-40 w-full rounded-xl object-cover shadow-inner bg-white">
                    <input type="file" name="evidencias[{{ $idx }}][foto]" accept="image/*" onchange="previewEvidenciaImage({{ $idx }}, this)"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" title="Reemplazar fotografía">
                    <button type="button" onclick="removeEvidenciaRow({{ $idx }})"
                        class="absolute top-2 right-2 p-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-lg transition-all hover:scale-105 active:scale-95 z-30" title="Quitar fotografía">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <input type="text" name="evidencias[{{ $idx }}][descripcion]" value="{{ $ev['descripcion'] ?? '' }}"
                       placeholder="Descripción de la foto..."
                       class="w-full mt-2 px-3 py-2 bg-white border-2 border-indigo-200 focus:border-indigo-600 rounded-xl font-bold text-[11px] text-slate-700 outline-none transition-all">
            </div>
        @endforeach
    </div>
</div>

<script>
(function () {
    const MAX_EVIDENCIAS = {{ (int) $max }};
    const STORAGE_BASE = '{{ asset('storage') }}';
    const EVIDENCIA_MOVIL_URLS = {
        qr: @json($qrUrl),
        estado: @json($estadoUrl),
    };

    let evidenciaCounter = parseInt(document.getElementById('container_evidencias')?.dataset.count || '0', 10);
    // Paths de fotos ya guardadas que el auditor quitó localmente en esta
    // pantalla (aún sin guardar el formulario): evita que el sondeo de
    // evidencia móvil las vuelva a insertar mientras tanto.
    const evidenciasRemovidasLocalmente = new Set();
    let pollingEvidenciaMovil = null;
    // Token del QR activo: se usa para poder avisar al servidor cuando el
    // auditor quita localmente una foto pendiente subida desde el celular.
    let tokenEvidenciaMovilActivo = null;

    window.updateBtnAddEvidenciaState = function () {
        const btn = document.getElementById('btn_add_evidencia');
        const total = document.querySelectorAll('#container_evidencias .evidencia-card').length;
        if (btn) btn.disabled = total >= MAX_EVIDENCIAS;
        if (btn) btn.classList.toggle('opacity-40', total >= MAX_EVIDENCIAS);
        if (btn) btn.classList.toggle('cursor-not-allowed', total >= MAX_EVIDENCIAS);
    };

    window.addEvidenciaRow = function () {
        const total = document.querySelectorAll('#container_evidencias .evidencia-card').length;
        if (total >= MAX_EVIDENCIAS) return;

        const idx = evidenciaCounter++;
        const container = document.getElementById('container_evidencias');
        const card = document.createElement('div');
        card.className = 'evidencia-card bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300 p-3 shadow-sm';
        card.dataset.idx = idx;
        card.innerHTML = `
            <div class="relative">
                <div id="dropzone_evidencia_${idx}" class="h-40 rounded-xl bg-white border border-slate-200 flex flex-col items-center justify-center gap-1.5 text-center">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <i data-lucide="upload-cloud" class="w-5 h-5"></i>
                    </div>
                    <p class="text-[10px] font-black text-slate-700 uppercase">Toque para subir</p>
                    <p class="text-[9px] font-bold text-slate-400 uppercase">JPG, PNG, WEBP</p>
                </div>
                <img id="img_preview_evidencia_${idx}" class="h-40 w-full rounded-xl object-cover shadow-inner bg-white hidden" alt="Evidencia ${idx + 1}">
                <input type="file" name="evidencias[${idx}][foto]" accept="image/*" onchange="previewEvidenciaImage(${idx}, this)"
                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                <button type="button" onclick="removeEvidenciaRow(${idx})"
                    class="absolute top-2 right-2 p-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-lg transition-all hover:scale-105 active:scale-95 z-30 hidden" id="btn_remove_evidencia_${idx}" title="Quitar fotografía">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </div>
            <input type="text" name="evidencias[${idx}][descripcion]"
                   placeholder="Descripción de la foto..."
                   class="w-full mt-2 px-3 py-2 bg-white border-2 border-indigo-200 focus:border-indigo-600 rounded-xl font-bold text-[11px] text-slate-700 outline-none transition-all">
        `;
        container.appendChild(card);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        window.updateBtnAddEvidenciaState();
    };

    window.previewEvidenciaImage = function (idx, input) {
        const dropzone = document.getElementById('dropzone_evidencia_' + idx);
        const img = document.getElementById('img_preview_evidencia_' + idx);
        const btnRemove = document.getElementById('btn_remove_evidencia_' + idx);

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                }
                if (dropzone) dropzone.classList.add('hidden');
                if (btnRemove) btnRemove.classList.remove('hidden');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    window.removeEvidenciaRow = function (idx) {
        const card = document.querySelector(`.evidencia-card[data-idx="${idx}"]`);
        if (card) {
            // Si esta foto ya estaba guardada (tiene path_existente), se recuerda
            // para que el sondeo de evidencia móvil no la vuelva a insertar
            // mientras el auditor la está quitando aquí (el borrado recién
            // queda firme al guardar el formulario completo).
            const input = card.querySelector('input[name*="[path_existente]"]');
            if (input && input.value) {
                evidenciasRemovidasLocalmente.add(input.value);
                // Si vino del celular y todavía está pendiente (sin guardar el
                // formulario), se avisa también al servidor para liberar el
                // archivo y que no vuelva a contar en el máximo de fotos.
                if (card.dataset.origen === 'movil' && tokenEvidenciaMovilActivo) {
                    fetch(`{{ url('/evidencia-movil') }}/${tokenEvidenciaMovilActivo}/eliminar`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({ path: input.value }),
                    }).catch(() => {});
                }
            }
            card.remove();
        }
        window.updateBtnAddEvidenciaState();
    };

    window.abrirEvidenciaMovil = function () {
        Swal.fire({
            title: '📷 Cargar Fotos desde el Celular',
            html: `
                <div class="text-left space-y-3 text-xs font-semibold text-slate-600">
                    <div id="ev_movil_qr_container" class="flex justify-center p-4 bg-white rounded-2xl border border-slate-200 min-h-[180px] items-center">
                        <div class="text-slate-400 text-[11px]">Generando código QR...</div>
                    </div>
                    <p class="text-slate-500 text-[11px] text-center">
                        Escanee este código con la cámara de su celular. Se abrirá una página para tomar fotos con descripción; aparecerán aquí automáticamente, sin transferir nada a mano.
                    </p>
                    <div id="ev_movil_status" class="p-2.5 bg-indigo-50 rounded-xl text-center text-[10px] font-bold text-indigo-600 flex items-center justify-center gap-2">
                        <div class="w-3 h-3 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                        Esperando fotos...
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            customClass: { popup: 'rounded-[2.5rem] p-6 max-w-sm' },
            willClose: () => {
                if (pollingEvidenciaMovil) clearInterval(pollingEvidenciaMovil);
            }
        });

        fetch(EVIDENCIA_MOVIL_URLS.qr)
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                const cont = document.getElementById('ev_movil_qr_container');
                if (!cont) return;
                if (!ok || !data.qr_html) {
                    cont.innerHTML = `<div class="text-rose-500 text-[11px] text-center">${data.message || 'No se pudo generar el código QR. Intente de nuevo.'}</div>`;
                    return;
                }
                tokenEvidenciaMovilActivo = data.token || null;
                cont.innerHTML = data.qr_html;
            })
            .catch(() => {
                const cont = document.getElementById('ev_movil_qr_container');
                if (cont) cont.innerHTML = '<div class="text-rose-500 text-[11px] text-center">No se pudo conectar con el servidor. Verifique su conexión e intente de nuevo.</div>';
            });

        if (pollingEvidenciaMovil) clearInterval(pollingEvidenciaMovil);
        pollingEvidenciaMovil = setInterval(sondearEvidenciaMovil, 4000);
    };

    function sondearEvidenciaMovil() {
        fetch(EVIDENCIA_MOVIL_URLS.estado)
            .then(r => r.json())
            .then(data => {
                const pendientes = data.pendientes || [];
                const pathsPendientesServidor = new Set(pendientes.map(ev => ev.path));
                const tarjetasMovil = Array.from(document.querySelectorAll('#container_evidencias .evidencia-card[data-origen="movil"]'));
                const pathsActuales = new Set(
                    tarjetasMovil
                        .map(card => card.querySelector('input[name*="[path_existente]"]')?.value)
                        .filter(Boolean)
                );

                let nuevas = 0;
                pendientes.forEach(ev => {
                    if (!pathsActuales.has(ev.path) && !evidenciasRemovidasLocalmente.has(ev.path)) {
                        agregarEvidenciaDesdeMovil(ev.path, ev.descripcion || '');
                        nuevas++;
                    }
                });

                let eliminadas = 0;
                tarjetasMovil.forEach(card => {
                    const path = card.querySelector('input[name*="[path_existente]"]')?.value;
                    if (path && !pathsPendientesServidor.has(path) && !evidenciasRemovidasLocalmente.has(path)) {
                        card.remove();
                        eliminadas++;
                    }
                });
                if (eliminadas > 0) window.updateBtnAddEvidenciaState();

                const statusEl = document.getElementById('ev_movil_status');
                if (statusEl && (nuevas > 0 || eliminadas > 0)) {
                    const partes = [];
                    if (nuevas > 0) partes.push(`+${nuevas} nueva(s)`);
                    if (eliminadas > 0) partes.push(`${eliminadas} quitada(s) desde el celular`);
                    statusEl.innerHTML = `<span class="text-emerald-600">✓ ${partes.join(', ')} · recuerda guardar el formulario para dejarlas guardadas</span>`;
                }
            })
            .catch(() => {});
    }

    function agregarEvidenciaDesdeMovil(path, descripcion) {
        const total = document.querySelectorAll('#container_evidencias .evidencia-card').length;
        if (total >= MAX_EVIDENCIAS) return;

        const idx = evidenciaCounter++;
        const container = document.getElementById('container_evidencias');
        const card = document.createElement('div');
        card.className = 'evidencia-card bg-amber-50 rounded-2xl border-2 border-amber-300 p-3 shadow-sm';
        card.dataset.idx = idx;
        card.dataset.origen = 'movil';
        const descripcionSegura = (descripcion || '').replace(/"/g, '&quot;');
        card.innerHTML = `
            <input type="hidden" name="evidencias[${idx}][path_existente]" value="${path}">
            <div class="relative group">
                <img src="${STORAGE_BASE}/${path}" alt="Evidencia desde celular" class="h-40 w-full rounded-xl object-cover shadow-inner bg-white">
                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-amber-500 text-white text-[9px] font-black uppercase shadow flex items-center gap-1">
                    <i data-lucide="smartphone" class="w-2.5 h-2.5"></i> Celular · sin guardar
                </span>
                <button type="button" onclick="removeEvidenciaRow(${idx})"
                    class="absolute top-2 right-2 p-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white shadow-lg transition-all hover:scale-105 active:scale-95 z-30" title="Quitar fotografía">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </div>
            <input type="text" name="evidencias[${idx}][descripcion]" value="${descripcionSegura}"
                   placeholder="Descripción de la foto..."
                   class="w-full mt-2 px-3 py-2 bg-white border-2 border-amber-200 focus:border-amber-600 rounded-xl font-bold text-[11px] text-slate-700 outline-none transition-all">
        `;
        container.appendChild(card);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        window.updateBtnAddEvidenciaState();
    }

    document.addEventListener('DOMContentLoaded', window.updateBtnAddEvidenciaState);

    // Al guardar el formulario (cualquiera que lo contenga), se corta el
    // sondeo del código QR (el servidor también lo cierra) para no seguir
    // consultando de más una vez que la evaluación ya quedó guardada.
    const formPadre = document.getElementById('container_evidencias')?.closest('form');
    if (formPadre) {
        formPadre.addEventListener('submit', () => {
            if (pollingEvidenciaMovil) clearInterval(pollingEvidenciaMovil);
        });
    }
})();
</script>
