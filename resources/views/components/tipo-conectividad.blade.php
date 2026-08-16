@props(['num', 'contenido', 'color' => 'indigo'])

<div class="monitoreo-section bg-white rounded-[2rem] p-8 shadow-lg border border-slate-100">
    <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
        @if(trim($num ?? '') !== '')
            <span
                class="section-number bg-{{ $color }}-600 text-white w-8 h-8 flex items-center justify-center rounded-full font-black text-sm">{{ $num }}</span>
        @endif
        <h3 class="text-{{ $color }}-900 font-black text-lg uppercase tracking-tight">TIPO DE CONECTIVIDAD</h3>
    </div>

    <input type="hidden" name="contenido[tipo_conectividad]" id="tipo_conectividad_input"
        value="{{ $contenido['tipo_conectividad'] ?? '' }}">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- TARJETA WIFI --}}
        <div id="card_wifi" onclick="selectConectividad('WIFI')"
            class="cursor-pointer border-2 rounded-2xl p-6 flex items-center gap-4 transition-all hover:shadow-md {{ ($contenido['tipo_conectividad'] ?? '') == 'WIFI' ? 'border-' . $color . '-600 bg-' . $color . '-50' : 'border-slate-200 bg-white' }}">
            <div
                class="h-12 w-12 rounded-xl bg-{{ $color }}-100 flex items-center justify-center text-{{ $color }}-600">
                <i data-lucide="wifi" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-800 uppercase">WIFI</h4>
                <span
                    class="text-[10px] font-bold text-{{ $color }}-500 bg-{{ $color }}-100 px-2 py-0.5 rounded uppercase">Inalámbrico</span>
            </div>
        </div>

        {{-- TARJETA CABLEADO --}}
        <div id="card_cableado" onclick="selectConectividad('CABLEADO')"
            class="cursor-pointer border-2 rounded-2xl p-6 flex items-center gap-4 transition-all hover:shadow-md {{ ($contenido['tipo_conectividad'] ?? '') == 'CABLEADO' ? 'border-' . $color . '-600 bg-' . $color . '-50' : 'border-slate-200 bg-white' }}">
            <div class="h-12 w-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500">
                <i data-lucide="cable" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-800 uppercase">CABLEADO</h4>
                <span
                    class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded uppercase">Ethernet</span>
            </div>
        </div>

        {{-- TARJETA SIN CONECTIVIDAD --}}
        <div id="card_sin_conectividad" onclick="selectConectividad('SIN CONECTIVIDAD')"
            class="cursor-pointer border-2 rounded-2xl p-6 flex items-center gap-4 transition-all hover:shadow-md md:col-span-2 {{ ($contenido['tipo_conectividad'] ?? '') == 'SIN CONECTIVIDAD' ? 'border-' . $color . '-600 bg-' . $color . '-50' : 'border-slate-200 bg-white' }}">
            <div class="h-12 w-12 rounded-xl bg-rose-100 flex items-center justify-center text-rose-600">
                <i data-lucide="wifi-off" class="w-6 h-6"></i>
            </div>
            <div>
                <h4 class="text-sm font-black text-slate-800 uppercase">SIN CONECTIVIDAD</h4>
                <span class="text-[10px] font-bold text-rose-500 bg-rose-100 px-2 py-0.5 rounded uppercase">No cuenta
                    con internet</span>
            </div>
        </div>
    </div>

    {{-- SUB-OPCIÓN: WIFI DEL ESTABLECIMIENTO O PERSONAL --}}
    <input type="hidden" name="contenido[wifi_fuente]" id="wifi_fuente_input"
        value="{{ $contenido['wifi_fuente'] ?? '' }}">
    <div id="bloque_wifi_fuente"
        class="mt-6 bg-slate-50 rounded-2xl p-6 border border-slate-200 {{ ($contenido['tipo_conectividad'] ?? '') == 'WIFI' ? '' : 'hidden' }}">
        <label class="block text-{{ $color }}-600 text-[10px] font-black uppercase tracking-widest mb-4">¿De dónde
            proviene el WiFi?</label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- ESTABLECIMIENTO --}}
            <div id="card_wifi_establecimiento" onclick="selectWifiFuente('ESTABLECIMIENTO')"
                class="cursor-pointer border-2 rounded-xl p-4 flex items-center gap-3 transition-all hover:shadow-md {{ ($contenido['wifi_fuente'] ?? '') == 'ESTABLECIMIENTO' ? 'border-' . $color . '-600 bg-' . $color . '-50' : 'border-slate-200 bg-white' }}">
                <div
                    class="h-10 w-10 rounded-lg bg-{{ $color }}-100 flex items-center justify-center text-{{ $color }}-600">
                    <i data-lucide="building-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-slate-800 uppercase">Establecimiento</h4>
                    <span class="text-[9px] font-bold text-{{ $color }}-400">Red del EESS</span>
                </div>
            </div>
            {{-- PERSONAL --}}
            <div id="card_wifi_personal" onclick="selectWifiFuente('PERSONAL')"
                class="cursor-pointer border-2 rounded-xl p-4 flex items-center gap-3 transition-all hover:shadow-md {{ ($contenido['wifi_fuente'] ?? '') == 'PERSONAL' ? 'border-' . $color . '-600 bg-' . $color . '-50' : 'border-slate-200 bg-white' }}">
                <div class="h-10 w-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500">
                    <i data-lucide="smartphone" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs font-black text-slate-800 uppercase">Personal</h4>
                    <span class="text-[9px] font-bold text-slate-400">Hotspot / Propio</span>
                </div>
            </div>
        </div>
    </div>

    {{-- OPERADOR DE SERVICIO Y VELOCIDAD --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6" id="bloque_operador_servicio">
        <div>
            <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Operador de
                Servicio</label>
            <select name="contenido[operador_servicio]" id="operador_servicio_select"
                class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-200 rounded-xl font-bold text-sm outline-none focus:border-{{ $color }}-500 transition-all uppercase cursor-pointer">
                <option value="" selected disabled>-- SELECCIONE --</option>
                @foreach(['WOW', 'MOVISTAR', 'ENTEL', 'CLARO', 'BITEL', 'FIBERPRO', 'NUBYX', 'WIN', 'TICTEL', 'GILAT', 'ALTINET', 'DELAFIBER', 'COMPUIVAN', 'OTROS'] as $op)
                    <option value="{{ $op }}" {{ ($contenido['operador_servicio'] ?? '') == $op ? 'selected' : '' }}>
                        {{ $op }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest">Velocidad de Internet</label>
                <div class="flex items-center gap-1.5">
                    <button type="button" onclick="ejecutarSpeedtestEnVivo()"
                        class="px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-black text-[10px] uppercase tracking-wider flex items-center gap-1 shadow-md shadow-indigo-200 transition-all hover:scale-105 active:scale-95 cursor-pointer">
                        <i data-lucide="zap" class="w-3.5 h-3.5 text-yellow-300"></i>
                        <span>⚡ Medir Speedtest</span>
                    </button>
                    <button type="button" onclick="abrirModalImportarSpeedtest()"
                        class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-bold text-[10px] uppercase tracking-wider flex items-center gap-1 transition-all cursor-pointer">
                        <i data-lucide="download" class="w-3 h-3 text-slate-500"></i>
                        <span>Cargar</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 mb-2">
                <div>
                    <label class="block text-slate-400 text-[9px] font-bold uppercase mb-1">Descarga</label>
                    <div class="flex gap-1">
                        <input type="number" step="0.01" name="contenido[velocidad_descarga]" id="velocidad_descarga_input" 
                            value="{{ $contenido['velocidad_descarga'] ?? $contenido['velocidad_internet_cantidad'] ?? '' }}" placeholder="Ej. 100"
                            class="w-2/3 px-2 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg font-bold text-sm outline-none focus:border-{{ $color }}-500 transition-all placeholder-slate-300">
                        <select name="contenido[velocidad_descarga_unidad]" id="velocidad_descarga_unidad_select"
                            class="w-1/3 px-1 py-2 bg-slate-100 border-2 border-slate-200 rounded-lg font-bold text-xs outline-none focus:border-{{ $color }}-500 transition-all cursor-pointer text-slate-600">
                            <option value="Mbps" {{ ($contenido['velocidad_descarga_unidad'] ?? $contenido['velocidad_internet_unidad'] ?? 'Mbps') == 'Mbps' ? 'selected' : '' }}>Mbps</option>
                            <option value="Gbps" {{ ($contenido['velocidad_descarga_unidad'] ?? $contenido['velocidad_internet_unidad'] ?? '') == 'Gbps' ? 'selected' : '' }}>Gbps</option>
                            <option value="Kbps" {{ ($contenido['velocidad_descarga_unidad'] ?? $contenido['velocidad_internet_unidad'] ?? '') == 'Kbps' ? 'selected' : '' }}>Kbps</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-slate-400 text-[9px] font-bold uppercase mb-1">Subida</label>
                    <div class="flex gap-1">
                        <input type="number" step="0.01" name="contenido[velocidad_subida]" id="velocidad_subida_input" 
                            value="{{ $contenido['velocidad_subida'] ?? '' }}" placeholder="Ej. 50"
                            class="w-2/3 px-2 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg font-bold text-sm outline-none focus:border-{{ $color }}-500 transition-all placeholder-slate-300">
                        <select name="contenido[velocidad_subida_unidad]" id="velocidad_subida_unidad_select"
                            class="w-1/3 px-1 py-2 bg-slate-100 border-2 border-slate-200 rounded-lg font-bold text-xs outline-none focus:border-{{ $color }}-500 transition-all cursor-pointer text-slate-600">
                            <option value="Mbps" {{ ($contenido['velocidad_subida_unidad'] ?? $contenido['velocidad_internet_unidad'] ?? 'Mbps') == 'Mbps' ? 'selected' : '' }}>Mbps</option>
                            <option value="Gbps" {{ ($contenido['velocidad_subida_unidad'] ?? $contenido['velocidad_internet_unidad'] ?? '') == 'Gbps' ? 'selected' : '' }}>Gbps</option>
                            <option value="Kbps" {{ ($contenido['velocidad_subida_unidad'] ?? $contenido['velocidad_internet_unidad'] ?? '') == 'Kbps' ? 'selected' : '' }}>Kbps</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables globales para el componente (usando el color pasado por props)
    const colorTheme = "{{ $color }}";

    function selectConectividad(tipo, isInit = false) {
        const input = document.getElementById('tipo_conectividad_input');
        const cardWifi = document.getElementById('card_wifi');
        const cardCableado = document.getElementById('card_cableado');
        const cardSinConectividad = document.getElementById('card_sin_conectividad');
        const bloqueWifiFuente = document.getElementById('bloque_wifi_fuente');

        if (!input) return;

        // Si no es inicialización y el valor actual es igual al que se clickeó, lo vaciamos (unselect)
        if (!isInit && input.value === tipo) {
            input.value = '';
            tipo = '';
        } else {
            input.value = tipo;
        }

        // Resetear todas las tarjetas
        const cards = [
            { el: cardWifi, val: 'WIFI' },
            { el: cardCableado, val: 'CABLEADO' },
            { el: cardSinConectividad, val: 'SIN CONECTIVIDAD' }
        ];

        cards.forEach(card => {
            if (card.el) {
                if (card.val === tipo && tipo !== '') {
                    card.el.classList.add(`border-${colorTheme}-600`, `bg-${colorTheme}-50`);
                    card.el.classList.remove('border-slate-200', 'bg-white');
                } else {
                    card.el.classList.remove(`border-${colorTheme}-600`, `bg-${colorTheme}-50`);
                    card.el.classList.add('border-slate-200', 'bg-white');
                }
            }
        });

        if (tipo === 'WIFI') {
            bloqueWifiFuente.classList.remove('hidden');
        } else {
            bloqueWifiFuente.classList.add('hidden');

            // Limpiar selección de fuente WiFi
            document.getElementById('wifi_fuente_input').value = '';

            // Resetear estilos de fuentes wifi
            const cardEst = document.getElementById('card_wifi_establecimiento');
            const cardPers = document.getElementById('card_wifi_personal');

            if (cardEst) {
                cardEst.classList.remove(`border-${colorTheme}-600`, `bg-${colorTheme}-50`);
                cardEst.classList.add('border-slate-200', 'bg-white');
            }
            if (cardPers) {
                cardPers.classList.remove(`border-${colorTheme}-600`, `bg-${colorTheme}-50`);
                cardPers.classList.add('border-slate-200', 'bg-white');
            }
        }

        // Mostrar/Ocultar bloque de operador y velocidad
        const bloqueOperador = document.getElementById('bloque_operador_servicio');
        const selectOperador = document.getElementById('operador_servicio_select');
        const inputVelocidadDescarga = document.getElementById('velocidad_descarga_input');
        const inputVelocidadSubida = document.getElementById('velocidad_subida_input');
        
        if (bloqueOperador) {
            if (tipo === 'WIFI' || tipo === 'CABLEADO') {
                bloqueOperador.classList.remove('hidden');
            } else {
                bloqueOperador.classList.add('hidden');
                if (selectOperador) selectOperador.value = ''; // Limpiar valor si se oculta
                if (inputVelocidadDescarga) inputVelocidadDescarga.value = '';
                if (inputVelocidadSubida) inputVelocidadSubida.value = '';
            }
        }
    }

    function selectWifiFuente(fuente, isInit = false) {
        const input = document.getElementById('wifi_fuente_input');
        const cardEstablecimiento = document.getElementById('card_wifi_establecimiento');
        const cardPersonal = document.getElementById('card_wifi_personal');

        if (!input) return;

        // Toggle logic (solo si no es inicialización)
        if (!isInit && input.value === fuente) {
            input.value = '';
            fuente = '';
        } else {
            input.value = fuente;
        }

        if (fuente === 'ESTABLECIMIENTO') {
            cardEstablecimiento.classList.add(`border-${colorTheme}-600`, `bg-${colorTheme}-50`);
            cardEstablecimiento.classList.remove('border-slate-200', 'bg-white');
            cardPersonal.classList.remove(`border-${colorTheme}-600`, `bg-${colorTheme}-50`);
            cardPersonal.classList.add('border-slate-200', 'bg-white');
        } else if (fuente === 'PERSONAL') {
            cardPersonal.classList.add(`border-${colorTheme}-600`, `bg-${colorTheme}-50`);
            cardPersonal.classList.remove('border-slate-200', 'bg-white');
            cardEstablecimiento.classList.remove(`border-${colorTheme}-600`, `bg-${colorTheme}-50`);
            cardEstablecimiento.classList.add('border-slate-200', 'bg-white');
        } else {
            // Unselected state
            if (cardEstablecimiento) {
                cardEstablecimiento.classList.remove(`border-${colorTheme}-600`, `bg-${colorTheme}-50`);
                cardEstablecimiento.classList.add('border-slate-200', 'bg-white');
            }
            if (cardPersonal) {
                cardPersonal.classList.remove(`border-${colorTheme}-600`, `bg-${colorTheme}-50`);
                cardPersonal.classList.add('border-slate-200', 'bg-white');
            }
        }
    }

    // Exponer funciones globalmente
    window.selectConectividad = selectConectividad;
    window.selectWifiFuente = selectWifiFuente;

    // --- DETECCIÓN DE CAMBIO DE RED ---
    // Rastrear cambios de red para invalidar datos ISP cacheados
    let _redCambioDesdeUltimoTest = false;
    if (navigator.connection) {
        navigator.connection.addEventListener('change', () => {
            _redCambioDesdeUltimoTest = true;
            console.log('[SpeedTest] Cambio de red detectado:', navigator.connection.type, navigator.connection.effectiveType);
        });
    }
    // También detectar reconexiones WiFi
    window.addEventListener('online', () => {
        _redCambioDesdeUltimoTest = true;
        console.log('[SpeedTest] Reconexión de red detectada');
    });

    // --- AUTODETECCION EN VIVO TIPO SPEEDTEST ---
    window.ejecutarSpeedtestEnVivo = function() {
        if (typeof Swal === 'undefined') {
            alert("No se cargó la librería de notificaciones.");
            return;
        }

        let testAborted = false;
        let finalPing = 0;
        let finalDown = 0;
        let finalUp = 0;
        let detectedIsp = '';
        // Si la red cambió desde el último test, NO confiar en el selector (puede ser del test anterior)
        const operadorInicial = _redCambioDesdeUltimoTest ? '' : (document.getElementById('operador_servicio_select')?.value || '');
        _redCambioDesdeUltimoTest = false; // Reiniciar para el siguiente test

        Swal.fire({
            title: '⚡ SPEEDTEST EN VIVO',
            html: `
                <div class="bg-slate-900 rounded-3xl p-6 text-white text-center shadow-2xl relative overflow-hidden my-2">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-indigo-500/20 rounded-full blur-2xl"></div>
                    <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-yellow-500/20 rounded-full blur-2xl"></div>

                    <!-- ESTADO -->
                    <div id="st_status" class="text-[11px] font-black uppercase tracking-widest text-indigo-300 mb-4 animate-pulse">
                        Iniciando prueba de conexión...
                    </div>

                    <!-- VELOCÍMETRO / DISPLAY GRANDE -->
                    <div class="relative flex flex-col items-center justify-center my-4">
                        <div class="text-5xl font-black tracking-tight text-white font-mono" id="st_speed_display">
                            0.00
                        </div>
                        <div class="text-xs font-black text-indigo-400 uppercase tracking-widest mt-1" id="st_unit_display">
                            Mbps
                        </div>
                    </div>

                    <!-- BARRA DE PROGRESO -->
                    <div class="w-full bg-slate-800 h-2.5 rounded-full overflow-hidden my-4 border border-white/10">
                        <div id="st_progress_bar" class="bg-gradient-to-r from-indigo-500 via-purple-500 to-yellow-400 h-full w-0 transition-all duration-300"></div>
                    </div>

                    <!-- MÉTRICAS CLAVE (PING, DESCARGA, SUBIDA, ISP) -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6 pt-4 border-t border-white/10 text-left">
                        <div class="bg-white/5 p-3 rounded-2xl border border-white/5">
                            <span class="block text-[9px] font-black text-slate-400 uppercase">PING</span>
                            <span class="text-sm font-black text-yellow-400" id="st_val_ping">-- ms</span>
                        </div>
                        <div class="bg-white/5 p-3 rounded-2xl border border-white/5">
                            <span class="block text-[9px] font-black text-slate-400 uppercase">DESCARGA</span>
                            <span class="text-sm font-black text-emerald-400" id="st_val_down">-- Mbps</span>
                        </div>
                        <div class="bg-white/5 p-3 rounded-2xl border border-white/5">
                            <span class="block text-[9px] font-black text-slate-400 uppercase">SUBIDA</span>
                            <span class="text-sm font-black text-sky-400" id="st_val_up">-- Mbps</span>
                        </div>
                        <div class="bg-white/5 p-3 rounded-2xl border border-white/5">
                            <span class="block text-[9px] font-black text-slate-400 uppercase">PROVEEDOR</span>
                            <span class="text-xs font-black text-indigo-300 truncate block" id="st_val_isp">Detectando...</span>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '⚡ Aplicar al Formulario',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4F46E5',
            allowOutsideClick: false,
            focusConfirm: false,
            didOpen: () => {
                const btnConfirm = Swal.getConfirmButton();
                if (btnConfirm) btnConfirm.style.display = 'none';

                // Lógica de ejecución asíncrona por etapas
                runSpeedtestSequence();
            },
            willClose: () => {
                testAborted = true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Seleccionar automáticamente tipo de conectividad si estaba en blanco o sin conectividad
                const inputTipo = document.getElementById('tipo_conectividad_input');
                if (!inputTipo.value || inputTipo.value === 'SIN CONECTIVIDAD') {
                    selectConectividad('WIFI', true);
                }

                // Autocompletar Operador ISP si se detectó
                if (detectedIsp) {
                    const selectOperador = document.getElementById('operador_servicio_select');
                    if (selectOperador) selectOperador.value = detectedIsp;
                }

                // Autocompletar Descarga y Subida
                const inputDescarga = document.getElementById('velocidad_descarga_input');
                const inputSubida = document.getElementById('velocidad_subida_input');
                const selectDescargaUnidad = document.getElementById('velocidad_descarga_unidad_select');
                const selectSubidaUnidad = document.getElementById('velocidad_subida_unidad_select');

                if (inputDescarga && finalDown > 0) inputDescarga.value = finalDown.toFixed(2);
                if (inputSubida && finalUp > 0) inputSubida.value = finalUp.toFixed(2);

                if (selectDescargaUnidad) selectDescargaUnidad.value = 'Mbps';
                if (selectSubidaUnidad) selectSubidaUnidad.value = 'Mbps';

                Swal.fire({
                    icon: 'success',
                    title: '¡Velocidad Autodetectada!',
                    text: `Descarga: ${finalDown.toFixed(2)} Mbps | Subida: ${finalUp.toFixed(2)} Mbps`,
                    timer: 3000,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
            }
        });

        async function runSpeedtestSequence() {
            try {
                // ETAPA 0: Detección rápida de red del cliente (NetworkInfo & IP)
                updateStatus('Etapa 1/4: Identificando Proveedor de Red...', 10);
                detectedIsp = await detectClientISP(operadorInicial);
                document.getElementById('st_val_isp').innerText = detectedIsp || 'DETECTADO';

                if (testAborted) return;

                // ETAPA 1: Latencia (Ping)
                updateStatus('Etapa 2/4: Calculando Latencia (Ping)...', 25);
                finalPing = await measurePing();
                document.getElementById('st_val_ping').innerText = `${finalPing.toFixed(0)} ms`;

                if (testAborted) return;

                // ETAPA 2: Medición de Descarga (Download Mbps)
                updateStatus('Etapa 3/4: Midiendo Velocidad de Descarga...', 50);
                finalDown = await measureDownloadSpeed();
                document.getElementById('st_val_down').innerText = `${finalDown.toFixed(2)} Mbps`;

                if (testAborted) return;

                // ETAPA 3: Medición de Subida (Upload Mbps)
                updateStatus('Etapa 4/4: Midiendo Velocidad de Subida...', 85);
                finalUp = await measureUploadSpeed();
                document.getElementById('st_val_up').innerText = `${finalUp.toFixed(2)} Mbps`;

                if (testAborted) return;

                // FINALIZACIÓN
                updateStatus('¡Prueba Completada Exitosamente!', 100);
                document.getElementById('st_speed_display').innerText = finalDown.toFixed(2);
                document.getElementById('st_unit_display').innerText = 'Mbps (Descarga)';

                const btnConfirm = Swal.getConfirmButton();
                if (btnConfirm) {
                    btnConfirm.style.display = 'inline-block';
                    btnConfirm.classList.add('animate-bounce');
                }
            } catch (err) {
                console.error("Error en speedtest:", err);
                updateStatus('⚠️ Prueba parcial completada (Modo Local)', 100);
                const btnConfirm = Swal.getConfirmButton();
                if (btnConfirm) btnConfirm.style.display = 'inline-block';
            }
        }

        function updateStatus(msg, progressPercent) {
            const statusEl = document.getElementById('st_status');
            const barEl = document.getElementById('st_progress_bar');
            if (statusEl) statusEl.innerText = msg;
            if (barEl) barEl.style.width = `${progressPercent}%`;
        }

        // 1. Detectar ISP - Intenta ambas APIs con cache-busting para evitar datos obsoletos
        async function detectClientISP(operadorFallback) {
            const cacheBust = `_t=${Date.now()}`;
            let resultadoApi1 = null;

            // --- Intento 1: ipapi.co ---
            try {
                const response = await fetch(`https://ipapi.co/json/?${cacheBust}`, { cache: 'no-store' });
                if (response.ok) {
                    const data = await response.json();
                    const ispStr = (data.isp || data.org || data.asn || '').toUpperCase();
                    console.log('[SpeedTest] ipapi.co respuesta:', { isp: data.isp, org: data.org, ip: data.ip });
                    resultadoApi1 = matchIspOption(ispStr);
                    if (resultadoApi1 !== 'OTROS') return resultadoApi1;
                    // Si ipapi.co no reconoce el ISP, intentar con ip-api.com antes de caer al fallback
                }
            } catch (e) {
                console.warn('[SpeedTest] ipapi.co falló:', e.message);
            }

            // --- Intento 2: ip-api.com (siempre se intenta si el primer resultado fue OTROS o falló) ---
            try {
                const resp2 = await fetch(`http://ip-api.com/json/?${cacheBust}`, { cache: 'no-store' });
                if (resp2.ok) {
                    const d2 = await resp2.json();
                    const ispStr2 = (d2.isp || d2.org || '').toUpperCase();
                    console.log('[SpeedTest] ip-api.com respuesta:', { isp: d2.isp, org: d2.org, query: d2.query });
                    const matched2 = matchIspOption(ispStr2);
                    if (matched2 !== 'OTROS') return matched2;
                }
            } catch (e2) {
                console.warn('[SpeedTest] ip-api.com falló:', e2.message);
            }

            // Ambas APIs devolvieron OTROS o fallaron: usar fallback del operador seleccionado
            console.log('[SpeedTest] ISP no reconocido por APIs. Fallback:', operadorFallback || 'OTROS');
            return operadorFallback || 'OTROS';
        }

        function matchIspOption(ispName) {
            // WOW: razón social = "Desarrollo De Infraestructura De Telecomunicaciones Peru S.A.C."
            if (ispName.includes('WOW') || ispName.includes('DESARROLLO DE INFRAESTRUCTURA DE TELECOMUNICACIONES')) return 'WOW';
            if (ispName.includes('TELEFONICA') || ispName.includes('MOVISTAR') || ispName.includes('TDF')) return 'MOVISTAR';
            if (ispName.includes('CLARO') || ispName.includes('AMERICA MOVIL')) return 'CLARO';
            // WIN: verificar antes de ENTEL para evitar falsos positivos
            if (ispName.includes('OPTICAL TECHNOLOGIES') || ispName.includes('OPTIKA') || (ispName.includes('WIN') && !ispName.includes('ENTEL'))) return 'WIN';
            if (ispName.includes('ENTEL')) return 'ENTEL';
            if (ispName.includes('BITEL') || ispName.includes('VIETTEL')) return 'BITEL';
            if (ispName.includes('FIBERPRO')) return 'FIBERPRO';
            if (ispName.includes('NUBYX')) return 'NUBYX';
            if (ispName.includes('TICTEL')) return 'TICTEL';
            if (ispName.includes('GILAT')) return 'GILAT';
            if (ispName.includes('ALTINET')) return 'ALTINET';
            if (ispName.includes('DELAFIBER')) return 'DELAFIBER';
            if (ispName.includes('COMPUIVAN')) return 'COMPUIVAN';
            return 'OTROS';
        }

        // 2. Medir Ping (Latencia) real a CDN de alta velocidad
        async function measurePing() {
            const pings = [];
            for (let i = 0; i < 4; i++) {
                if (testAborted) break;
                const start = performance.now();
                try {
                    // Endpoint ultra-liviano de Cloudflare para medir latencia de red real
                    await fetch(`https://speed.cloudflare.com/__down?bytes=0&_t=${Date.now()}_${i}`, { cache: 'no-store' });
                    const rtt = performance.now() - start;
                    pings.push(rtt);
                } catch (e) {
                    try {
                        const startLocal = performance.now();
                        await fetch(`{{ url('/api/speedtest/ping') }}?t=${Date.now()}_${i}`, { cache: 'no-store' });
                        pings.push(performance.now() - startLocal);
                    } catch (errLocal) {
                        pings.push(20);
                    }
                }
            }
            if (pings.length === 0) return 20;
            // Tomar el menor RTT o promedio de los mejores para eliminar jitter de inicio
            pings.sort((a, b) => a - b);
            return pings[0];
        }

        // 3. Medir Descarga (Download Mbps) con Streaming Continuo y Ventana Deslizante (Arquitectura Speedtest/Fast.com)
        async function measureDownloadSpeed() {
            const displayEl = document.getElementById('st_speed_display');
            const unitEl = document.getElementById('st_unit_display');
            if (unitEl) unitEl.innerText = 'Mbps Descarga';

            let totalLoadedBytes = 0;
            const testDurationMs = 3500; // 3.5 segundos de descarga continua sostenida
            const startTime = performance.now();
            let isRunning = true;
            const snapshots = [];

            // Muestreo cada 100ms
            const interval = setInterval(() => {
                const elapsed = (performance.now() - startTime) / 1000;
                // Descartar primeros 500ms (Handshake TLS + TCP Slow Start)
                if (elapsed > 0.5) {
                    snapshots.push({
                        time: performance.now(),
                        bytes: totalLoadedBytes
                    });
                    if (snapshots.length >= 3) {
                        const past = snapshots[Math.max(0, snapshots.length - 6)];
                        const dt = (performance.now() - past.time) / 1000;
                        const db = totalLoadedBytes - past.bytes;
                        if (dt > 0.1) {
                            const currentMbps = (db * 8) / (dt * 1000000);
                            if (displayEl) displayEl.innerText = currentMbps.toFixed(2);
                        }
                    }
                }
            }, 100);

            const runWorker = async () => {
                while (isRunning && !testAborted && (performance.now() - startTime < testDurationMs)) {
                    try {
                        const res = await fetch(`https://speed.cloudflare.com/__down?bytes=25000000&_t=${Date.now()}_${Math.random()}`, { cache: 'no-store' });
                        if (!res.ok || !res.body) break;
                        const reader = res.body.getReader();
                        while (isRunning && !testAborted && (performance.now() - startTime < testDurationMs)) {
                            const { done, value } = await reader.read();
                            if (done) break;
                            if (value) {
                                totalLoadedBytes += value.length;
                            }
                        }
                        reader.cancel().catch(() => {});
                    } catch (e) {
                        break;
                    }
                }
            };

            // 6 streams concurrentes paralelos para saturar fibra óptica
            await Promise.all([
                runWorker(),
                runWorker(),
                runWorker(),
                runWorker(),
                runWorker(),
                runWorker()
            ]);

            isRunning = false;
            clearInterval(interval);

            // Calcular velocidad pico sostenida (usando la mejor ventana deslizante)
            let peakMbps = 0;
            if (snapshots.length >= 5) {
                for (let i = 3; i < snapshots.length; i++) {
                    const startSnap = snapshots[Math.max(0, i - 6)];
                    const endSnap = snapshots[i];
                    const dt = (endSnap.time - startSnap.time) / 1000;
                    const db = endSnap.bytes - startSnap.bytes;
                    if (dt > 0.25) {
                        const rate = (db * 8) / (dt * 1000000);
                        if (rate > peakMbps) peakMbps = rate;
                    }
                }
            }

            if (peakMbps <= 0) {
                const totalElapsed = (performance.now() - startTime) / 1000;
                peakMbps = totalElapsed > 0 ? (totalLoadedBytes * 8) / (totalElapsed * 1000000) : 45;
            }

            if (displayEl) displayEl.innerText = peakMbps.toFixed(2);
            return peakMbps;
        }

        // 4. Medir Subida (Upload Mbps) con Streaming Continuo y Ventana Deslizante (Arquitectura Speedtest/Fast.com)
        async function measureUploadSpeed() {
            const displayEl = document.getElementById('st_speed_display');
            const unitEl = document.getElementById('st_unit_display');
            if (unitEl) unitEl.innerText = 'Mbps Subida';

            let totalUploadedBytes = 0;
            const testDurationMs = 3500; // 3.5 segundos de subida continua sostenida
            const startTime = performance.now();
            let isRunning = true;
            const snapshots = [];

            const interval = setInterval(() => {
                const elapsed = (performance.now() - startTime) / 1000;
                if (elapsed > 0.5) {
                    snapshots.push({
                        time: performance.now(),
                        bytes: totalUploadedBytes
                    });
                    if (snapshots.length >= 3) {
                        const past = snapshots[Math.max(0, snapshots.length - 6)];
                        const dt = (performance.now() - past.time) / 1000;
                        const db = totalUploadedBytes - past.bytes;
                        if (dt > 0.1) {
                            const currentMbps = (db * 8) / (dt * 1000000);
                            if (displayEl) displayEl.innerText = currentMbps.toFixed(2);
                        }
                    }
                }
            }, 100);

            const chunkData = new Uint8Array(2 * 1024 * 1024); // 2MB chunk

            const runUploadWorker = async () => {
                while (isRunning && !testAborted && (performance.now() - startTime < testDurationMs)) {
                    try {
                        const res = await fetch(`https://speed.cloudflare.com/__up?_t=${Date.now()}_${Math.random()}`, {
                            method: 'POST',
                            body: chunkData,
                            headers: { 'Content-Type': 'application/octet-stream' }
                        });
                        if (res.ok) {
                            totalUploadedBytes += chunkData.length;
                        }
                    } catch (e) {
                        break;
                    }
                }
            };

            // 6 streams concurrentes paralelos de subida
            await Promise.all([
                runUploadWorker(),
                runUploadWorker(),
                runUploadWorker(),
                runUploadWorker(),
                runUploadWorker(),
                runUploadWorker()
            ]);

            isRunning = false;
            clearInterval(interval);

            let peakMbps = 0;
            if (snapshots.length >= 5) {
                for (let i = 3; i < snapshots.length; i++) {
                    const startSnap = snapshots[Math.max(0, i - 6)];
                    const endSnap = snapshots[i];
                    const dt = (endSnap.time - startSnap.time) / 1000;
                    const db = endSnap.bytes - startSnap.bytes;
                    if (dt > 0.25) {
                        const rate = (db * 8) / (dt * 1000000);
                        if (rate > peakMbps) peakMbps = rate;
                    }
                }
            }

            if (peakMbps <= 0) {
                const totalElapsed = (performance.now() - startTime) / 1000;
                peakMbps = totalElapsed > 0 ? (totalUploadedBytes * 8) / (totalElapsed * 1000000) : 30;
            }

            if (displayEl) displayEl.innerText = peakMbps.toFixed(2);
            return peakMbps;
        }
    };

    window.abrirModalImportarSpeedtest = function() {
        if (typeof Swal === 'undefined') return;

        const currentIsp = document.getElementById('operador_servicio_select')?.value || 'WOW';
        const currentDown = document.getElementById('velocidad_descarga_input')?.value || '';
        const currentUp = document.getElementById('velocidad_subida_input')?.value || '';

        Swal.fire({
            title: '📥 Cargar Resultado de Speedtest.net',
            html: `
                <div class="text-left text-xs text-slate-600 mb-3 leading-relaxed">
                    Ingresa los valores obtenidos en tu prueba de <b>Speedtest.net</b> para autocompletar la Sección 6:
                </div>
                <div class="space-y-3 text-left">
                    <div>
                        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Operador de Servicio (ISP)</label>
                        <select id="swal_isp" class="w-full p-2 border border-slate-300 rounded-lg text-xs font-bold bg-slate-50">
                            <option value="WOW" ${currentIsp === 'WOW' ? 'selected' : ''}>WOW TEL (WOW)</option>
                            <option value="MOVISTAR" ${currentIsp === 'MOVISTAR' ? 'selected' : ''}>MOVISTAR</option>
                            <option value="CLARO" ${currentIsp === 'CLARO' ? 'selected' : ''}>CLARO</option>
                            <option value="WIN" ${currentIsp === 'WIN' ? 'selected' : ''}>WIN</option>
                            <option value="ENTEL" ${currentIsp === 'ENTEL' ? 'selected' : ''}>ENTEL</option>
                            <option value="BITEL" ${currentIsp === 'BITEL' ? 'selected' : ''}>BITEL</option>
                            <option value="FIBERPRO" ${currentIsp === 'FIBERPRO' ? 'selected' : ''}>FIBERPRO</option>
                            <option value="NUBYX" ${currentIsp === 'NUBYX' ? 'selected' : ''}>NUBYX</option>
                            <option value="OTROS" ${currentIsp === 'OTROS' ? 'selected' : ''}>OTROS</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Descarga (Mbps)</label>
                            <input id="swal_down" type="number" step="0.01" value="${currentDown}" placeholder="Ej. 50.52" class="w-full p-2 border border-slate-300 rounded-lg text-xs font-bold bg-slate-50">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Subida (Mbps)</label>
                            <input id="swal_up" type="number" step="0.01" value="${currentUp}" placeholder="Ej. 58.45" class="w-full p-2 border border-slate-300 rounded-lg text-xs font-bold bg-slate-50">
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Aplicar a la Sección 6',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4F46E5',
            focusConfirm: false,
            preConfirm: () => {
                const isp = document.getElementById('swal_isp').value;
                const down = document.getElementById('swal_down').value;
                const up = document.getElementById('swal_up').value;

                if (!down && !up) {
                    Swal.showValidationMessage('Ingresa al menos un valor de velocidad');
                    return false;
                }
                return { isp, down, up };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const { isp, down, up } = result.value;

                if (typeof window.selectConectividad === 'function') {
                    window.selectConectividad('WIFI', true);
                }

                const selectOperador = document.getElementById('operador_servicio_select');
                if (selectOperador) selectOperador.value = isp;

                const inputDescarga = document.getElementById('velocidad_descarga_input');
                const inputSubida = document.getElementById('velocidad_subida_input');

                if (inputDescarga && down) inputDescarga.value = down;
                if (inputSubida && up) inputSubida.value = up;

                Swal.fire({
                    icon: 'success',
                    title: '¡Velocidades Aplicadas!',
                    text: 'Se autocompletaron los datos de Speedtest en la Sección 6.',
                    timer: 2500,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false
                });
            }
        });
    };

    // Inicialización automática
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar conectividad si tiene valor guardado
        const conectividadVal = document.getElementById('tipo_conectividad_input')?.value;
        if (conectividadVal) selectConectividad(conectividadVal, true);

        // Inicializar fuente WiFi si tiene valor guardado
        const wifiFuenteVal = document.getElementById('wifi_fuente_input')?.value;
        if (wifiFuenteVal) selectWifiFuente(wifiFuenteVal, true);

        // Reinicializar iconos si es necesario
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>