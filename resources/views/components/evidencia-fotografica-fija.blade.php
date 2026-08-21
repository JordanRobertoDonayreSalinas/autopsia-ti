@props([
    'qrUrl',
    'estadoUrl',
])

{{--
    Botón "Desde el Celular" para objetivos de 2 casillas fijas (portada del
    acta, actas de reunión): a diferencia de <x-evidencia-fotografica>, aquí
    no hay tarjetas dinámicas que mostrar — las fotos ya guardadas se
    muestran con el markup propio de cada página (foto1/foto2). Este
    componente solo se encarga del botón, el modal QR, y de inyectar un
    input oculto por cada foto pendiente que llega del celular para que se
    absorba junto con el resto del formulario al guardar (el backend la
    reconoce vía fotos_pendientes_movil[] y la asigna a la primera casilla
    libre).
--}}
<div class="inline-block">
    <button type="button" onclick="abrirEvidenciaMovilFija()"
            class="group flex items-center gap-1.5 px-3.5 py-2 bg-white text-indigo-600 border-2 border-indigo-200 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50 transition-all shadow-sm active:scale-95">
        <i data-lucide="smartphone" class="w-3.5 h-3.5"></i>
        Desde el Celular
    </button>
    <p id="ev_movil_fija_contador" class="hidden text-[10px] font-bold text-amber-600 mt-1.5"></p>
</div>

<script>
(function () {
    const URLS = { qr: @json($qrUrl), estado: @json($estadoUrl) };
    const pathsInyectados = new Set();
    let polling = null;
    let token = null;

    window.abrirEvidenciaMovilFija = function () {
        Swal.fire({
            title: '📷 Cargar Fotos desde el Celular',
            html: `
                <div class="text-left space-y-3 text-xs font-semibold text-slate-600">
                    <div id="ev_movil_fija_qr_container" class="flex justify-center p-4 bg-white rounded-2xl border border-slate-200 min-h-[180px] items-center">
                        <div class="text-slate-400 text-[11px]">Generando código QR...</div>
                    </div>
                    <p class="text-slate-500 text-[11px] text-center">
                        Escanee este código con la cámara de su celular. Se abrirá una página para tomar la foto; aparecerá aquí automáticamente, sin transferir nada a mano.
                    </p>
                    <div id="ev_movil_fija_status" class="p-2.5 bg-indigo-50 rounded-xl text-center text-[10px] font-bold text-indigo-600 flex items-center justify-center gap-2">
                        <div class="w-3 h-3 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                        Esperando fotos...
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            customClass: { popup: 'rounded-[2.5rem] p-6 max-w-sm' },
            willClose: () => { if (polling) clearInterval(polling); }
        });

        fetch(URLS.qr)
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                const cont = document.getElementById('ev_movil_fija_qr_container');
                if (!cont) return;
                if (!ok || !data.qr_html) {
                    cont.innerHTML = `<div class="text-rose-500 text-[11px] text-center">${data.message || 'No se pudo generar el código QR. Intente de nuevo.'}</div>`;
                    return;
                }
                token = data.token || null;
                cont.innerHTML = data.qr_html;
            })
            .catch(() => {
                const cont = document.getElementById('ev_movil_fija_qr_container');
                if (cont) cont.innerHTML = '<div class="text-rose-500 text-[11px] text-center">No se pudo conectar con el servidor. Verifique su conexión e intente de nuevo.</div>';
            });

        if (polling) clearInterval(polling);
        polling = setInterval(sondear, 4000);
    };

    function sondear() {
        fetch(URLS.estado)
            .then(r => r.json())
            .then(data => {
                const pendientes = data.pendientes || [];
                let nuevas = 0;
                pendientes.forEach(p => {
                    if (!pathsInyectados.has(p.path)) {
                        inyectarPendiente(p.path);
                        nuevas++;
                    }
                });

                if (nuevas > 0) {
                    const statusEl = document.getElementById('ev_movil_fija_status');
                    if (statusEl) {
                        statusEl.innerHTML = `<span class="text-emerald-600">✓ +${nuevas} foto(s) recibida(s) · recuerda guardar el formulario para dejarlas guardadas</span>`;
                    }
                    actualizarContadorVisible();
                }
            })
            .catch(() => {});
    }

    function inyectarPendiente(path) {
        pathsInyectados.add(path);
        const form = document.querySelector('button[onclick="abrirEvidenciaMovilFija()"]')?.closest('form');
        if (!form) return;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'fotos_pendientes_movil[]';
        input.value = path;
        input.dataset.evidenciaMovilFija = '1';
        form.appendChild(input);
    }

    function actualizarContadorVisible() {
        const el = document.getElementById('ev_movil_fija_contador');
        if (!el) return;
        if (pathsInyectados.size === 0) {
            el.classList.add('hidden');
            return;
        }
        el.textContent = `📱 ${pathsInyectados.size} foto(s) del celular pendiente(s) de guardar`;
        el.classList.remove('hidden');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('button[onclick="abrirEvidenciaMovilFija()"]')?.closest('form');
        if (form) {
            form.addEventListener('submit', () => { if (polling) clearInterval(polling); });
        }
    });
})();
</script>
