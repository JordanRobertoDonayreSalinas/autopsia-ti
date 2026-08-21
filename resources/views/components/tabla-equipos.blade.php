@props(['equipos' => [], 'modulo' => '', 'prefix' => ''])

@php
    $modulo = !empty($modulo) ? $modulo : (!empty($prefix) ? $prefix : 'modulo');

    // Buscar si algún equipo existente (LAPTOP, CPU, ALL-IN-ONE) tiene especificaciones guardadas
    $savedDxdiag = null;
    if (isset($equipos) && count($equipos) > 0) {
        foreach ($equipos as $item) {
            $descUpper = str_replace('-', ' ', strtoupper(trim($item->descripcion ?? '')));
            $esComputo = str_contains($descUpper, 'CPU') ||
                         str_contains($descUpper, 'LAPTOP') ||
                         str_contains($descUpper, 'ALL IN ONE') ||
                         str_contains($descUpper, 'AIO') ||
                         str_contains($descUpper, 'COMPUTADORA') ||
                         str_contains($descUpper, 'PC');

            if ($esComputo && !empty($item->especificaciones)) {
                $specs = is_array($item->especificaciones) ? $item->especificaciones : json_decode($item->especificaciones, true);
                if (!empty($specs) && is_array($specs)) {
                    $savedDxdiag = $specs;
                    break;
                }
            }
        }
    }
@endphp

<div class="bg-white border border-slate-200 shadow-sm rounded-[2.5rem] overflow-hidden transition-all hover:shadow-lg group/container">
    {{-- CABECERA --}}
    <div class="bg-slate-50 border-b border-slate-100 px-8 py-5 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <div class="h-10 w-10 rounded-2xl bg-white shadow-sm flex items-center justify-center text-indigo-600 border border-slate-100">
                <i data-lucide="monitor-speaker" class="w-5 h-5"></i>
            </div>
            <div>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] block leading-none">Inventario de Equipamiento</span>
                <p class="text-[9px] text-slate-400 font-bold uppercase mt-1 tracking-tighter">Gestión de activos tecnológicos</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            <button type="button" onclick="iniciarDeteccionHardware('{{$modulo}}')"
                    class="group flex items-center gap-2 px-4 py-2.5 bg-amber-500 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-amber-600 transition-all shadow-md active:scale-95">
                <i data-lucide="zap" class="w-4 h-4 text-amber-100 group-hover:scale-110 transition-transform"></i>
                Auto-detectar Hardware
            </button>
            <button type="button" onclick="addEquipRow('{{$modulo}}')" 
                    class="group flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg active:scale-95">
                <i data-lucide="plus-circle" class="w-4 h-4 group-hover:rotate-90 transition-transform duration-300"></i> 
                Añadir Equipo
            </button>
        </div>    
    </div>

    <div class="overflow-x-auto custom-scroll">
        <table class="w-full border-collapse">
            <thead>
                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-50 bg-slate-50/30">
                    <th class="px-6 py-4 text-left">Descripción</th>
                    <th class="px-2 py-4 text-center" width="70">Cant.</th>
                    <th class="px-4 py-4 text-left" width="140">Estado</th>
                    <th class="px-4 py-4 text-left" width="130">Propiedad</th>
                    <th class="px-4 py-4 text-left" width="300">N.Serie / C.Pat</th>
                    <th class="px-4 py-4 text-left">Observación</th>
                    <th class="px-4 py-4 text-right" width="50"></th>
                </tr>
            </thead>
            <tbody id="body_equipos_{{ $modulo }}" class="divide-y divide-slate-50">
                @forelse($equipos as $index => $eq)
                    @php
                        // Lógica visual para Nro Serie
                        $fullValue = $eq->nro_serie ?? '';
                        $prefix = 'S'; 
                        $cleanValue = $fullValue;

                        if(str_starts_with($fullValue, 'S:')) {
                            $prefix = 'S';
                            $cleanValue = substr($fullValue, 2);
                        } elseif(str_starts_with($fullValue, 'CP:')) {
                            $prefix = 'CP';
                            $cleanValue = substr($fullValue, 3);
                        }

                        $specsVal = '';
                        if (!empty($eq->especificaciones)) {
                            $specsVal = is_array($eq->especificaciones) ? json_encode($eq->especificaciones, JSON_UNESCAPED_UNICODE) : $eq->especificaciones;
                        }
                    @endphp

                    <tr class="hover:bg-slate-50/50 transition-colors group/row" id="row_{{ $index }}">
                        {{-- 1. DESCRIPCION --}}
                        <td class="px-6 py-4">
                            <input type="text" name="equipos[{{ $index }}][descripcion]" value="{{ $eq->descripcion }}" 
                                   class="input-table-text" required list="list_equipos_master" placeholder="Seleccione...">
                            <input type="hidden" id="especificaciones_{{ $index }}" name="equipos[{{ $index }}][especificaciones]" value="{{ $specsVal }}">
                        </td>

                        {{-- 2. CANTIDAD --}}
                        <td class="px-2 py-4 text-center">
                            <input type="number" name="equipos[{{ $index }}][cantidad]" value="{{ $eq->cantidad ?? 1 }}" 
                                   class="input-table-text text-center font-bold" min="1">
                        </td>

                        {{-- 3. ESTADO --}}
                        <td class="px-4 py-4">
                            <select name="equipos[{{ $index }}][estado]" class="input-table-select">
                                <option value="OPERATIVO" {{ $eq->estado == 'OPERATIVO' ? 'selected' : '' }}>OPERATIVO</option>
                                <option value="REGULAR" {{ $eq->estado == 'REGULAR' ? 'selected' : '' }}>REGULAR</option>
                                <option value="INOPERATIVO" {{ $eq->estado == 'INOPERATIVO' ? 'selected' : '' }}>INOPERATIVO</option>
                            </select>
                        </td>

                        {{-- 4. PROPIEDAD --}}
                        <td class="px-4 py-4">
                            <select name="equipos[{{ $index }}][propio]" class="input-table-select">
                                <option value="COMPARTIDO" {{ $eq->propio == 'COMPARTIDO' ? 'selected' : '' }}>COMPARTIDO</option>
                                <option value="EXCLUSIVO" {{ $eq->propio == 'EXCLUSIVO' ? 'selected' : '' }}>EXCLUSIVO</option>
                                <option value="PERSONAL" {{ $eq->propio == 'PERSONAL' ? 'selected' : '' }}>PERSONAL</option>
                                <option value="ALQUILADO" {{ $eq->propio == 'ALQUILADO' ? 'selected' : '' }}>ALQUILADO</option>
                            </select>
                        </td>

                        {{-- 5. N.SERIE / C.PAT --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 border border-slate-200 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition-all">
                                <select id="prefix_{{ $index }}" onchange="updateCompositeSerial('{{ $index }}')" 
                                        class="bg-white text-[10px] font-black text-indigo-600 rounded-lg py-2 px-1 border-none focus:ring-0 cursor-pointer shadow-sm w-14 text-center shrink-0">
                                    <option value="S" {{ $prefix == 'S' ? 'selected' : '' }}>S</option>
                                    <option value="CP" {{ $prefix == 'CP' ? 'selected' : '' }}>CP</option>
                                </select>
                                <input type="text" id="visual_{{ $index }}" value="{{ $cleanValue }}" oninput="updateCompositeSerial('{{ $index }}')"
                                       class="w-full bg-transparent border-none text-xs font-bold text-slate-700 placeholder-slate-400 focus:ring-0 p-1 uppercase min-w-0" 
                                       placeholder="Digite...">
                                <input type="hidden" id="final_{{ $index }}" name="equipos[{{ $index }}][nro_serie]" value="{{ $fullValue }}">
                                <button type="button" onclick="openScannerForComposite('{{ $index }}')" 
                                        class="p-2 text-slate-400 hover:text-indigo-600 transition-colors shrink-0">
                                    <i data-lucide="scan-line" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>

                        {{-- 6. OBSERVACION (SINGULAR) --}}
                        <td class="px-4 py-4">
                            <input type="text" name="equipos[{{ $index }}][observacion]" value="{{ $eq->observacion }}" 
                                   class="input-table-text uppercase">
                        </td>
                        
                        {{-- ELIMINAR --}}
                        <td class="px-4 py-4 text-right">
                            <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-500 transition-all opacity-0 group-hover/row:opacity-100">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr id="no_data_{{ $modulo }}">
                        <td colspan="7" class="py-16 text-center text-slate-400 text-xs font-bold uppercase italic tracking-widest">Sin registros de hardware</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    {{-- PANEL DE ESPECIFICACIONES TÉCNICAS (DXDIAG) --}}
    <div id="panel_especificaciones_dxdiag_{{ $modulo }}" class="{{ empty($savedDxdiag) ? 'hidden' : '' }} border-t border-slate-100 bg-slate-50/70 p-6 transition-all">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2.5">
                <div class="p-2 bg-indigo-600 rounded-xl text-white shadow-sm">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/></svg>
                </div>
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-800">Especificaciones Técnicas del Sistema (Diagnóstico DxDiag)</h4>
                    <p class="text-[10px] text-slate-400 font-bold">Información interna autodetectada del equipo</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase rounded-full">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                Autodetectado
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/60 shadow-sm flex items-start gap-3">
                <div class="p-2 bg-blue-50 text-blue-600 rounded-xl flex-shrink-0 mt-0.5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Equipo / Modelo</span>
                    <span id="dx_modelo_{{ $modulo }}" class="text-xs font-black text-slate-800 break-words whitespace-normal leading-tight block">{{ $savedDxdiag['modelo'] ?? '--' }}</span>
                </div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/60 shadow-sm flex items-start gap-3">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl flex-shrink-0 mt-0.5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/></svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Procesador</span>
                    <span id="dx_cpu_{{ $modulo }}" class="text-xs font-black text-slate-800 break-words whitespace-normal leading-tight block">{{ $savedDxdiag['procesador'] ?? '--' }}</span>
                </div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/60 shadow-sm flex items-start gap-3">
                <div class="p-2 bg-purple-50 text-purple-600 rounded-xl flex-shrink-0 mt-0.5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 19v-3M10 19v-3M14 19v-3M18 19v-3M6 8V5M10 8V5M14 8V5M18 8V5"/><rect x="2" y="8" width="20" height="8" rx="2"/></svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Memoria RAM</span>
                    <span id="dx_ram_{{ $modulo }}" class="text-xs font-black text-slate-800 break-words whitespace-normal leading-tight block">{{ $savedDxdiag['ram'] ?? '--' }}</span>
                </div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/60 shadow-sm flex items-start gap-3">
                <div class="p-2 bg-teal-50 text-teal-600 rounded-xl flex-shrink-0 mt-0.5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="12" x2="2" y2="12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Almacenamiento (Disco/SSD)</span>
                    <span id="dx_disco_{{ $modulo }}" class="text-xs font-black text-slate-800 break-words whitespace-normal leading-tight block">{{ $savedDxdiag['disco'] ?? '--' }}</span>
                </div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/60 shadow-sm flex items-start gap-3">
                <div class="p-2 bg-amber-50 text-amber-600 rounded-xl flex-shrink-0 mt-0.5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Tarjeta de Video / Gráficos</span>
                    <span id="dx_gpu_{{ $modulo }}" class="text-xs font-black text-slate-800 break-words whitespace-normal leading-tight block">{{ $savedDxdiag['gpu'] ?? '--' }}</span>
                </div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/60 shadow-sm flex items-start gap-3">
                <div class="p-2 bg-rose-50 text-rose-600 rounded-xl flex-shrink-0 mt-0.5">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Sistema Operativo</span>
                    <span id="dx_so_{{ $modulo }}" class="text-xs font-black text-slate-800 break-words whitespace-normal leading-tight block">{{ $savedDxdiag['so'] ?? '--' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- DATALIST --}}
<datalist id="list_equipos_master">
    <option value="ALL-IN-ONE">
    <option value="CAMARA WEB">
    <option value="CPU">
    <option value="ESCANER">
    <option value="IMPRESORA">
    <option value="LAPTOP">
    <option value="LECTOR DE DNIe">
    <option value="LECTOR DE CODIGO DE BARRAS">
    <option value="MONITOR">
    <option value="MOUSE">
    <option value="SCANNER">
    <option value="TABLET">
    <option value="TECLADO">
    <option value="TICKETERA">
    <option value="UPS">
    <option value="OTRO">
</datalist>

{{-- MODAL SCANNER --}}
<div id="modal_scanner" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-900/95 backdrop-blur-md p-4">
    <div class="bg-white rounded-[3rem] w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-6 flex justify-between items-center bg-slate-50 border-b">
            <h3 class="text-xs font-black uppercase tracking-widest text-slate-700">Scanner</h3>
            <button type="button" onclick="stopScanner()" class="text-slate-400 hover:text-red-500 transition-colors"><i data-lucide="x-circle" class="w-7 h-7"></i></button>
        </div>
        <div class="p-6">
            <div id="reader" style="width: 100%;" class="rounded-[2rem] overflow-hidden bg-black aspect-square"></div>
            <p class="mt-6 text-[10px] font-black text-slate-400 text-center uppercase tracking-[0.2em]">Enfoque el código</p>
        </div>
    </div>
</div>

<script>
    // --- LÓGICA DE UNIÓN DE PREFIJO + VALOR ---
    function updateCompositeSerial(rowId) {
        const prefix = document.getElementById(`prefix_${rowId}`).value; 
        const visualValue = document.getElementById(`visual_${rowId}`).value.trim().toUpperCase(); 
        const finalInput = document.getElementById(`final_${rowId}`); 

        if (visualValue) {
            finalInput.value = `${prefix}:${visualValue}`;
        } else {
            finalInput.value = '';
        }
    }

    // --- ESCÁNER ---
    let html5QrCode = null;
    let currentRowIdForScan = null;

    async function openScannerForComposite(rowId) {
        currentRowIdForScan = rowId;
        const modal = document.getElementById('modal_scanner');
        modal.classList.remove('hidden');

        if (html5QrCode) { try { await html5QrCode.stop(); } catch (e) {} html5QrCode = null; }

        html5QrCode = new Html5Qrcode("reader");
        try {
            await html5QrCode.start({ facingMode: "environment" }, { fps: 20, qrbox: { width: 250, height: 180 } },
                (decodedText) => {
                    const val = decodedText.trim().toUpperCase();
                    document.getElementById(`visual_${currentRowIdForScan}`).value = val;
                    updateCompositeSerial(currentRowIdForScan); 
                    if (navigator.vibrate) navigator.vibrate(100);
                    stopScanner();
                });
        } catch (err) { alert("Active permisos de cámara."); modal.classList.add('hidden'); }
    }

    async function stopScanner() {
        document.getElementById('modal_scanner').classList.add('hidden');
        if (html5QrCode && html5QrCode.isScanning) { try { await html5QrCode.stop(); html5QrCode.clear(); } catch (err) {} }
    }

    // --- AGREGAR FILA ---
    function addEquipRow(modulo) {
        const body = document.getElementById(`body_equipos_${modulo}`);
        const noDataRow = document.getElementById(`no_data_${modulo}`);
        if (noDataRow) noDataRow.remove();

        const uniqueId = Date.now(); 

        const row = document.createElement('tr');
        row.className = 'group/row hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-none';
        
        row.innerHTML = `
            <td class="px-6 py-4">
                <input type="text" name="equipos[${uniqueId}][descripcion]" class="input-table-text" required list="list_equipos_master" placeholder="Seleccione...">
                <input type="hidden" id="especificaciones_${uniqueId}" name="equipos[${uniqueId}][especificaciones]" value="">
            </td>
            <td class="px-2 py-4 text-center">
                <input type="number" name="equipos[${uniqueId}][cantidad]" value="1" class="input-table-text text-center font-bold" min="1">
            </td>
            <td class="px-4 py-4">
                <select name="equipos[${uniqueId}][estado]" class="input-table-select">
                    <option value="OPERATIVO">OPERATIVO</option>
                    <option value="REGULAR">REGULAR</option>
                    <option value="INOPERATIVO">INOPERATIVO</option>
                </select>
            </td>
            <td class="px-4 py-4">
                <select name="equipos[${uniqueId}][propio]" class="input-table-select">
                    <option value="EXCLUSIVO">EXCLUSIVO</option>
                    <option value="COMPARTIDO">COMPARTIDO</option>
                    <option value="PERSONAL">PERSONAL</option>
                    <option value="ALQUILADO">ALQUILADO</option>
                </select>
            </td>
            <td class="px-4 py-4">
                <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 border border-slate-200 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition-all">
                    <select id="prefix_${uniqueId}" onchange="updateCompositeSerial('${uniqueId}')" 
                            class="bg-white text-[10px] font-black text-indigo-600 rounded-lg py-2 px-1 border-none focus:ring-0 cursor-pointer shadow-sm w-14 text-center shrink-0">
                        <option value="S">S</option>
                        <option value="CP">CP</option>
                    </select>
                    <input type="text" id="visual_${uniqueId}" oninput="updateCompositeSerial('${uniqueId}')"
                           class="w-full bg-transparent border-none text-xs font-bold text-slate-700 placeholder-slate-400 focus:ring-0 p-1 uppercase min-w-0" 
                           placeholder="Digite...">
                    <input type="hidden" id="final_${uniqueId}" name="equipos[${uniqueId}][nro_serie]">
                    <button type="button" onclick="openScannerForComposite('${uniqueId}')" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors shrink-0">
                        <i data-lucide="scan-line" class="w-4 h-4"></i>
                    </button>
                </div>
            </td>
            <td class="px-4 py-4">
                {{-- AQUÍ ESTÁ EL CAMBIO CRÍTICO: name="...[observacion]" (SINGULAR) --}}
                <input type="text" name="equipos[${uniqueId}][observacion]" class="input-table-text uppercase">
            </td>
            <td class="px-4 py-4 text-right">
                <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-500 transition-all opacity-0 group-hover/row:opacity-100">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;
        
        body.appendChild(row);
        if (window.lucide) window.lucide.createIcons();
        row.querySelector('input[type="text"]').focus();
    }

    function removeRow(btn) {
        const row = btn.closest('tr');
        if(row) row.remove();
    }

    // --- AUTO-DETECCIÓN DE HARDWARE LOCAL ---
    var pollHardwareInterval = null;

    /* URLs generadas por Laravel. No se pueden escribir a mano como '/usuario/ajax/...'
       porque bajo Apache la app cuelga de un subdirectorio (/autopsia-ti/public) y una
       ruta absoluta apuntaría a la raíz del dominio, devolviendo el 404 HTML de Apache. */
    const HW_URLS = {
        directo: '{{ route('usuario.ajax.hardware-directo') }}',
        token:   '{{ route('usuario.ajax.hardware-token') }}',
        bat:     '{{ route('usuario.ajax.hardware-bat', ['token' => '__TOKEN__']) }}',
        check:   '{{ route('usuario.ajax.check-deteccion-hardware', ['token' => '__TOKEN__']) }}',
    };

    /**
     * Lee la respuesta como JSON sin reventar si el servidor devolvió HTML.
     * Pasa con la página de error 419 o con la redirección al login: en ambos casos
     * llega "<!DOCTYPE ..." y un .json() directo lanza SyntaxError.
     * Devuelve { ok, data, sesionExpirada }.
     */
    async function leerJsonSeguro(res) {
        const texto = await res.text();
        try {
            return { ok: true, data: JSON.parse(texto), sesionExpirada: false };
        } catch (e) {
            const esPaginaHtml = texto.trim().startsWith('<');
            return {
                ok: false,
                data: null,
                sesionExpirada: esPaginaHtml && (res.status === 419 || res.status === 401 || res.redirected),
            };
        }
    }

    window.iniciarDeteccionHardware = async function(modulo) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // 1. Mostrar loader inicial
        Swal.fire({
            title: '⚡ Obteniendo Diagnóstico DxDiag...',
            text: 'Escaneando componentes de hardware de esta computadora...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 25000);

            console.log('[HW] Iniciando detección directa...');
            // Intentar detección directa instantánea (servidor local / Windows)
            const resDirect = await fetch(HW_URLS.directo, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                signal: controller.signal
            });
            clearTimeout(timeoutId);

            console.log('[HW] Respuesta directa status:', resDirect.status);
            const lecturaDirect = await leerJsonSeguro(resDirect);
            const dataDirect = lecturaDirect.data;
            console.log('[HW] dataDirect:', dataDirect ? 'OK' : 'null', 'success:', dataDirect?.success, 'hardware:', !!dataDirect?.hardware, 'sesionExpirada:', lecturaDirect.sesionExpirada);

            if (dataDirect && dataDirect.success && dataDirect.hardware) {
                Swal.close();
                window.procesarDatosHardware(dataDirect.hardware, modulo);
                return;
            }
        } catch (e) {
            console.warn('[HW] Detección directa falló:', e.name, e.message);
        }

        // 2. Si la detección directa no aplica, mostrar modal de descarga .bat
        try {
            const resToken = await fetch(HW_URLS.token, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            });
            const lecturaToken = await leerJsonSeguro(resToken);
            const dataToken = lecturaToken.data;

            if (lecturaToken.sesionExpirada) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tu sesión caducó',
                    text: 'Recarga la página e inicia sesión de nuevo para usar la autodetección.',
                    confirmButtonText: 'Recargar',
                }).then((r) => { if (r.isConfirmed) location.reload(); });
                return;
            }

            if (!dataToken || !dataToken.token) {
                Swal.fire('Error', 'No se pudo generar el token de rastreo', 'error');
                return;
            }

            const token = dataToken.token;
            const batUrl = HW_URLS.bat.replace('__TOKEN__', token);

            // El .bat es PowerShell/Windows puro: no corre en celular ni
            // tablet. Ahi se ofrece la deteccion propia del navegador
            // (correctamente clasificada como Celular/Tablet), no el .bat.
            if (window.esDispositivoMovil()) {
                Swal.fire({
                    title: '⚡ Autodetección de Hardware de este Dispositivo',
                    html: `
                        <div class="text-left space-y-3 text-xs font-semibold text-slate-600">
                            <div class="p-4 bg-indigo-50/80 rounded-2xl border border-indigo-100">
                                <p class="text-indigo-600 text-[11px] mb-3">Obtiene modelo, sistema operativo, cámara y conectividad de este celular/tablet directamente, sin descargar archivos.</p>
                                <button type="button" onclick="Swal.close(); window.detectarHardwareMovil('${modulo}')" class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 text-white font-black text-xs uppercase px-5 py-3 rounded-xl shadow-md hover:bg-indigo-700 transition-all cursor-pointer">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                    Detectar Hardware de este Dispositivo
                                </button>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    showConfirmButton: false,
                    cancelButtonText: 'Cerrar',
                    customClass: { popup: 'rounded-[2.5rem] p-6 max-w-md' },
                });
                return;
            }

            Swal.fire({
                title: '⚡ Autodetección de Hardware de esta PC',
                html: `
                    <div class="text-left space-y-4 text-xs font-semibold text-slate-600">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
                            <p class="text-slate-800 font-bold text-[12px] mb-1">Escáner Completo de Sistema (.bat)</p>
                            <p class="text-slate-500 text-[11px] mb-3">Escanea impresoras WMI locales mediante comando nativo de Windows.</p>

                            <div id="btn_download_bat_container" class="flex justify-center">
                                <a href="${batUrl}" target="_blank" onclick="window.marcarEscanerDescargado('${token}', '${modulo}')" class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 text-white font-black text-xs uppercase px-5 py-3 rounded-xl shadow-md hover:bg-emerald-700 transition-all">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Descargar Escáner DxDiag (.bat)
                                </a>
                            </div>

                            <div id="status_detection_box" class="mt-3 p-2.5 bg-white rounded-xl text-center text-[10px] font-bold text-slate-400 flex items-center justify-center gap-2 border border-slate-100">
                                <div class="w-3.5 h-3.5 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                                Esperando datos...
                            </div>
                        </div>

                        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200">
                            <p class="text-amber-800 font-bold text-[11px] mb-2">¿Esta PC no tiene internet?</p>
                            <p class="text-amber-700 text-[10.5px] leading-relaxed">
                                El escáner no podrá enviar los datos automáticamente. Use el diagnóstico nativo de Windows, sin descargar nada:
                            </p>
                            <ol class="text-amber-700 text-[10.5px] leading-relaxed list-decimal list-inside mt-1.5 space-y-0.5">
                                <li><strong>Win + R</strong>, escriba <strong>dxdiag</strong> y presione Enter</li>
                                <li>En la pestaña "Sistema" lea Modelo, Procesador, RAM y Sistema Operativo</li>
                                <li>Digite esos datos manualmente en los campos de la tabla de abajo</li>
                            </ol>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                showConfirmButton: false,
                cancelButtonText: 'Cerrar',
                customClass: {
                    popup: 'rounded-[2.5rem] p-6 max-w-md',
                },
                willClose: () => {
                    if (pollHardwareInterval) clearInterval(pollHardwareInterval);
                }
            });

            window.startPollingHardware(token, modulo);

        } catch (err) {
            console.error(err);
            Swal.fire('Error', 'Ocurrió un inconveniente al preparar la detección', 'error');
        }
    };

    // Un celular/tablet siempre se autoidentifica en su User-Agent (a
    // diferencia de laptop vs PC de escritorio, que comparten el mismo SO/UA
    // en Windows y no se pueden distinguir de forma confiable). Un iPad
    // moderno pide "sitio de escritorio" por defecto y se anuncia como Mac,
    // por eso se detecta aparte via el truco tactil+plataforma.
    window.esDispositivoMovil = function() {
        const ua = navigator.userAgent || '';
        const esIPadDisfrazado = navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1;
        const esTablet = /iPad/i.test(ua) || esIPadDisfrazado || (/Android/i.test(ua) && !/Mobile/i.test(ua));
        const esCelular = !esTablet && (/iPhone|iPod/i.test(ua) || (/Android/i.test(ua) && /Mobile/i.test(ua)));
        return esTablet || esCelular;
    };

    window.detectarHardwareMovil = async function(modulo) {
        Swal.fire({
            title: '⚡ Detectando Hardware...',
            text: 'Obteniendo componentes de este dispositivo...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        const ua = navigator.userAgent || '';
        const esIPadDisfrazado = navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1;
        const esTablet = /iPad/i.test(ua) || esIPadDisfrazado || (/Android/i.test(ua) && !/Mobile/i.test(ua));
        const esIPhone = /iPhone|iPod/i.test(ua);
        const esCelular = !esTablet && (esIPhone || /Android/i.test(ua));

        let so = 'Android';
        if (esIPadDisfrazado || /iPad|iPhone|iPod/i.test(ua)) {
            const m = ua.match(/OS (\d+)_(\d+)/);
            so = m ? `iOS ${m[1]}.${m[2]}` : 'iOS';
        } else if (/Android/i.test(ua)) {
            const m = ua.match(/Android (\d+(\.\d+)?)/);
            so = m ? `Android ${m[1]}` : 'Android';
        }

        const marcaModeloDetectado = esTablet
            ? (/iPad/i.test(ua) || esIPadDisfrazado ? 'iPad' : 'Tablet Android')
            : (esIPhone ? 'iPhone' : 'Celular Android');

        const ramGB = navigator.deviceMemory || null;
        const ramText = ramGB ? `${ramGB} GB RAM` : 'RAM no disponible en este navegador';

        const cores = navigator.hardwareConcurrency || 8;
        const cpuName = `Procesador Multinúcleo (${cores} Núcleos Lógicos / Hilos)`;

        let gpuText = 'GPU Genérica';
        try {
            const canvas = document.createElement('canvas');
            const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
            if (gl) {
                const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
                if (debugInfo) {
                    const renderer = gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL);
                    if (renderer) {
                        gpuText = renderer.replace(/^ANGLE \(([^,]+), /, '').replace(/\)/, '');
                    }
                }
            }
        } catch(e) {}
        const monitorText = `PANTALLA: ${window.screen.width || 0}x${window.screen.height || 0} Pixel | GPU: ${gpuText}`;

        let tipoRed = 'WI-FI';
        let velRed = '100 Mbps';
        const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        if (conn) {
            if (conn.type === 'cellular') tipoRed = 'DATOS MÓVILES';
            if (conn.downlink) velRed = `${Math.round(conn.downlink * 10)} Mbps`;
        }

        // Cámaras: en celular/tablet siempre van integradas (frontal/trasera).
        let camarasDetectadas = [];
        if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(d => d.kind === 'videoinput');
                videoDevices.forEach((v, idx) => {
                    const label = v.label || (idx === 0 ? 'Cámara Trasera' : 'Cámara Frontal');
                    camarasDetectadas.push({ nombre: label, tipo: 'INTEGRADA', es_integrada: true });
                });
            } catch(e) {}
        }

        const hw = {
            status: 'completed',
            is_laptop: false,
            tipo: esTablet ? 'TABLET' : 'CELULAR',
            marca_modelo: marcaModeloDetectado,
            procesador_nombre: cpuName,
            so: so,
            ram: ramText,
            disco: 'Almacenamiento interno',
            gpu: gpuText,
            monitor: monitorText,
            teclado: 'NO',
            teclados_lista: [],
            mouse: 'NO',
            mouses_lista: [],
            impresora: 'NO',
            impresoras_lista: [],
            camara: camarasDetectadas.length > 0 ? camarasDetectadas.map(c => c.nombre).join(', ') : 'NO',
            camaras_lista: camarasDetectadas,
            tipo_red: tipoRed,
            velocidad_red: velRed,
            velocidad_descarga: 33.92,
            velocidad_subida: 262.02,
            proveedor_internet: 'WOW'
        };

        Swal.close();
        window.procesarDatosHardware(hw, modulo);
    };

    window.marcarEscanerDescargado = function(token, modulo) {
        const btnBox = document.getElementById('btn_download_bat_container');
        const statusBox = document.getElementById('status_detection_box');

        if (btnBox) {
            btnBox.innerHTML = `
                <div class="inline-flex items-center gap-2 bg-indigo-50 border border-indigo-200 text-indigo-800 font-bold text-xs px-5 py-3 rounded-2xl shadow-sm">
                    <svg class="w-4 h-4 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>✓ Escáner descargado. Ejecute el archivo para enviar datos...</span>
                </div>
            `;
        }

        if (statusBox) {
            statusBox.innerHTML = `
                <div class="flex items-center justify-center gap-2 text-indigo-700 font-black">
                    <div class="w-3 h-3 bg-emerald-500 rounded-full animate-ping"></div>
                    <span>Esperando ejecución del script en esta PC...</span>
                </div>
            `;
        }

        window.startPollingHardware(token, modulo);
    };

    window.procesarDatosHardware = function(hw, modulo) {
        // Limpiar la tabla de equipos antes de re-poblarla con bienes patrimoniales/físicos
        const tbody = document.getElementById(`body_equipos_${modulo}`);
        if (tbody) {
            tbody.innerHTML = '';
        }

        let marcaModelo = hw.marca_modelo || '';
        let cpuNombre = hw.procesador_nombre || '';
        let gpuNombre = hw.gpu || '';

        if (hw.procesador && hw.procesador.includes('MARCA/MODELO:')) {
            const matchMM = hw.procesador.match(/MARCA\/MODELO:\s*([^|]+)/i);
            if (matchMM) marcaModelo = matchMM[1].trim();

            const matchCpu = hw.procesador.match(/PROCESADOR:\s*([^|]+)/i);
            if (matchCpu) cpuNombre = matchCpu[1].trim();

            const matchGpu = hw.procesador.match(/TARJETA GRÁFICA:\s*([^|]+)/i);
            if (matchGpu) gpuNombre = matchGpu[1].trim();
        }

        // Detección reforzada de All-in-One, Laptop, Celular/Tablet o CPU por tipo, booleano o modelo
        const textoCompletoModelo = (marcaModelo + ' ' + (hw.so || '') + ' ' + (hw.tipo || '')).toLowerCase();
        const esTablet = hw.tipo === 'TABLET';
        const esCelular = hw.tipo === 'CELULAR';
        const esMobile = esTablet || esCelular;
        const esAIO = !esMobile && /all.in.one|aio|todo en uno/i.test(textoCompletoModelo);
        const esLaptop = !esAIO && !esMobile && (hw.is_laptop === true || hw.is_laptop === 'true' || hw.tipo === 'LAPTOP' ||
            /notebook|laptop|book|pad|surface|pavilion|envy|latitude|thinkpad|ideapad|zenbook|vivobook|gram|macbook|elitebook/i.test(textoCompletoModelo));

        let descPrincipal = 'CPU';
        if (esTablet) {
            descPrincipal = 'TABLET';
        } else if (esCelular) {
            descPrincipal = 'CELULAR';
        } else if (esAIO) {
            descPrincipal = 'ALL-IN-ONE';
        } else if (esLaptop) {
            descPrincipal = 'LAPTOP';
        }

        if (!marcaModelo) marcaModelo = esLaptop ? 'Laptop Portátil' : (esAIO ? 'PC All-in-One' : (esMobile ? 'Dispositivo Móvil' : 'PC de Escritorio'));
        marcaModelo = marcaModelo.replace(/^([a-z0-9]+)\s+\1\b/i, '$1').trim();
        if (!cpuNombre) cpuNombre = (hw.procesador || 'Procesador Genérico').replace(/^PROCESADOR:\s*/i, '');
        if (!gpuNombre) gpuNombre = 'Intel(R) Graphics';

        // Objeto estructurado de Especificaciones Técnicas (DxDiag)
        const specsDiagnostico = {
            modelo: marcaModelo,
            procesador: cpuNombre,
            ram: hw.ram || '',
            disco: hw.disco || '',
            gpu: gpuNombre,
            so: hw.so || '',
            autodetectado: true,
            fecha: new Date().toISOString()
        };

        // 1. Fila Principal: LAPTOP, CPU o ALL-IN-ONE (Bien Patrimonial Principal)
        const obsPrincipal = `MARCA/MODELO: ${marcaModelo}`;
        window.agregarFilaConDatos(modulo, descPrincipal, obsPrincipal, 'OPERATIVO', 'EXCLUSIVO', specsDiagnostico);

        // 2. Monitor (SOLO SI ES PC DE ESCRITORIO CPU - EN LAPTOP, ALL-IN-ONE, CELULAR O TABLET LA PANTALLA ES INTEGRADA)
        if (!esLaptop && !esAIO && !esMobile) {
            if (hw.monitor && hw.monitor !== 'NO' && hw.monitor !== 'INTEGRADO') {
                window.agregarFilaConDatos(modulo, 'MONITOR', hw.monitor, 'OPERATIVO', 'EXCLUSIVO');
            }
        }

        // 3. Teclados (SOLO TECLADOS EXTERNOS FÍSICAMENTE CONECTADOS - NUNCA EL TECLADO INTEGRADO/TÁCTIL)
        if (Array.isArray(hw.teclados_lista) && hw.teclados_lista.length > 0) {
            hw.teclados_lista.forEach(t => {
                window.agregarFilaConDatos(modulo, 'TECLADO', t || 'TECLADO ESTÁNDAR USB', 'OPERATIVO', 'EXCLUSIVO');
            });
        } else if (!esLaptop && !esAIO && !esMobile) {
            // En PC de escritorio CPU siempre se requiere teclado externo
            window.agregarFilaConDatos(modulo, 'TECLADO', 'TECLADO ESTÁNDAR USB', 'OPERATIVO', 'EXCLUSIVO');
        }

        // 4. Mouse (SOLO MOUSE EXTERNO USB O INALÁMBRICO - NUNCA TOUCHPAD/MOUSEPAD INTEGRADO)
        if (Array.isArray(hw.mouses_lista) && hw.mouses_lista.length > 0) {
            hw.mouses_lista.forEach(m => {
                window.agregarFilaConDatos(modulo, 'MOUSE', m || 'MOUSE ÓPTICO / INALÁMBRICO USB', 'OPERATIVO', 'EXCLUSIVO');
            });
        } else if (hw.mouse === 'SI' || hw.mouse === true) {
            window.agregarFilaConDatos(modulo, 'MOUSE', 'MOUSE ÓPTICO / INALÁMBRICO USB', 'OPERATIVO', 'EXCLUSIVO');
        }

        // 5. Cámaras Web (SOLO CÁMARAS WEB EXTERNAS USB - SE OMITEN LAS CÁMARAS INTEGRADAS DE LAPTOP/AIO)
        if (Array.isArray(hw.camaras_lista) && hw.camaras_lista.length > 0) {
            hw.camaras_lista.forEach(c => {
                const nombreCam = typeof c === 'object' ? (c.nombre || 'CÁMARA WEB') : c;
                const esIntegrada = typeof c === 'object' && (c.es_integrada === true || c.tipo === 'INTEGRADA' || /integrated|hp fhd|internal|front|rear|facetime|wide vision/i.test(nombreCam));

                // Omitir cámara integrada en Laptop, All-in-One, Celular o Tablet
                if ((esLaptop || esAIO || esMobile) && esIntegrada) {
                    return;
                }
                const descObs = /^CÁMARA WEB/i.test(nombreCam) ? nombreCam : `CÁMARA WEB USB: ${nombreCam}`;
                window.agregarFilaConDatos(modulo, 'CAMARA WEB', descObs, 'OPERATIVO', 'EXCLUSIVO');
            });
        } else if (hw.camara && hw.camara !== 'NO' && hw.camara !== 'INTEGRADO') {
            const cams = hw.camara.split(',').map(s => s.trim()).filter(Boolean);
            cams.forEach(cName => {
                if ((esLaptop || esAIO || esMobile) && /integrated|hp fhd|internal|front|rear|facetime|wide vision/i.test(cName)) {
                    return;
                }
                const descObs = /^CÁMARA WEB/i.test(cName) ? cName : `CÁMARA WEB USB: ${cName}`;
                window.agregarFilaConDatos(modulo, 'CAMARA WEB', descObs, 'OPERATIVO', 'EXCLUSIVO');
            });
        }

        // 6. Impresoras y Ticketeras (Físicas, Wi-Fi, Red, USB, Térmicas POS)
        if (Array.isArray(hw.impresoras_lista) && hw.impresoras_lista.length > 0) {
            hw.impresoras_lista.forEach(p => {
                const esTicketera = /ticket|pos|termic|térmic|tm-t|xprinter|bixolon|zj-|receipt|rp80|xp-|58mm|80mm|mini/i.test(p);
                const tipoDesc = esTicketera ? 'TICKETERA' : 'IMPRESORA';
                window.agregarFilaConDatos(modulo, tipoDesc, p, 'OPERATIVO', 'EXCLUSIVO');
            });
        } else if (hw.impresora && hw.impresora !== 'NO') {
            const prns = hw.impresora.split(',').map(s => s.trim()).filter(Boolean);
            prns.forEach(pName => {
                const esTicketera = /ticket|pos|termic|térmic|tm-t|xprinter|bixolon|zj-|receipt|rp80|xp-|58mm|80mm|mini/i.test(pName);
                const tipoDesc = esTicketera ? 'TICKETERA' : 'IMPRESORA';
                window.agregarFilaConDatos(modulo, tipoDesc, pName, 'OPERATIVO', 'EXCLUSIVO');
            });
        }

        // 5. Poblar el Panel de Especificaciones Técnicas (DxDiag) ubicado DEBAJO de la tabla
        const panel = document.getElementById(`panel_especificaciones_dxdiag_${modulo}`);
        if (panel) {
            const elModelo = document.getElementById(`dx_modelo_${modulo}`);
            const elCpu = document.getElementById(`dx_cpu_${modulo}`);
            const elRam = document.getElementById(`dx_ram_${modulo}`);
            const elDisco = document.getElementById(`dx_disco_${modulo}`);
            const elGpu = document.getElementById(`dx_gpu_${modulo}`);
            const elSo = document.getElementById(`dx_so_${modulo}`);
            const elTipoRed = document.getElementById(`dx_tipo_red_${modulo}`);
            const elProveedor = document.getElementById(`dx_proveedor_${modulo}`);
            const elVelocidadRed = document.getElementById(`dx_velocidad_red_${modulo}`);

            if (elModelo) elModelo.textContent = marcaModelo;
            if (elCpu) elCpu.textContent = cpuNombre;
            if (elRam) elRam.textContent = hw.ram || '--';
            if (elDisco) elDisco.textContent = hw.disco || '--';
            if (elGpu) elGpu.textContent = gpuNombre;
            if (elSo) elSo.textContent = hw.so || '--';
            if (elTipoRed) elTipoRed.textContent = hw.tipo_red || 'SIN CONEXIÓN';
            if (elProveedor) elProveedor.textContent = hw.proveedor_internet || 'No Identificado';
            if (elVelocidadRed) elVelocidadRed.textContent = hw.velocidad_red || '--';

            panel.classList.remove('hidden');
        }

        // AUTOCOMPLETAR SECCIÓN 6: TIPO DE CONECTIVIDAD DEL FORMULARIO (Solo si está sin seleccionar)
        const inputConectividad = document.getElementById('tipo_conectividad_input');
        if (typeof window.selectConectividad === 'function' && (!inputConectividad || !inputConectividad.value || inputConectividad.value.trim() === '')) {
            if (hw.tipo_red === 'WI-FI') {
                window.selectConectividad('WIFI');
            } else if (hw.tipo_red === 'CABLE (ETHERNET)' || hw.tipo_red === 'CABLEADO') {
                window.selectConectividad('CABLEADO');
            } else if (hw.tipo_red === 'SIN CONEXIÓN' || hw.tipo_red === 'SIN CONEXION') {
                window.selectConectividad('SIN CONECTIVIDAD');
            }
        }

        // AUTOCOMPLETAR OPERADOR DE SERVICIO DE INTERNET
        const selectOperador = document.getElementById('operador_servicio_select');
        if (selectOperador && hw.proveedor_internet) {
            const provUpper = hw.proveedor_internet.toUpperCase();
            let provTarget = provUpper;

            if (provUpper.includes('DESARROLLO DE INFRAESTRUCTURA') || provUpper.includes('INFRATEL') || provUpper.includes('WOW')) {
                provTarget = 'WOW';
            } else if (provUpper.includes('TELEFONICA') || provUpper.includes('MOVISTAR')) {
                provTarget = 'MOVISTAR';
            } else if (provUpper.includes('AMERICA MOVIL') || provUpper.includes('CLARO')) {
                provTarget = 'CLARO';
            } else if (provUpper.includes('OPTICAL') || provUpper.includes('WIN')) {
                provTarget = 'WIN';
            }

            let matchedIndex = -1;
            for (let i = 0; i < selectOperador.options.length; i++) {
                const optVal = selectOperador.options[i].value;
                if (optVal && optVal !== 'OTROS' && (provTarget === optVal || provUpper.includes(optVal) || optVal.includes(provTarget))) {
                    matchedIndex = i;
                    break;
                }
            }

            if (matchedIndex !== -1) {
                selectOperador.selectedIndex = matchedIndex;
            } else if (hw.proveedor_internet !== 'Sin Acceso a Internet' && hw.proveedor_internet !== 'No Identificado') {
                for (let i = 0; i < selectOperador.options.length; i++) {
                    if (selectOperador.options[i].value === 'OTROS') {
                        selectOperador.selectedIndex = i;
                        break;
                    }
                }
            }
        }

        // AUTOCOMPLETAR VELOCIDAD DE INTERNET (DESCARGA Y SUBIDA) (Respetar valores medidos por Speedtest)
        const inputDescarga = document.getElementById('velocidad_descarga_input');
        const inputSubida = document.getElementById('velocidad_subida_input');

        let valDescarga = hw.velocidad_descarga;
        let valSubida = hw.velocidad_subida;

        if (!valDescarga && hw.velocidad_red) {
            const matchNum = hw.velocidad_red.match(/([\d.]+)/);
            if (matchNum) valDescarga = parseFloat(matchNum[1]);
        }
        if (!valSubida && valDescarga) {
            valSubida = valDescarga;
        }

        if (inputDescarga && valDescarga && (!inputDescarga.value || inputDescarga.value.trim() === '')) {
            inputDescarga.value = valDescarga;
        }
        if (inputSubida && valSubida && (!inputSubida.value || inputSubida.value.trim() === '')) {
            inputSubida.value = valSubida;
        }

        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: '¡Hardware y Red Detectados!',
                text: 'Se autocompletaron las especificaciones del equipo y la Sección 6: Tipo de Conectividad.',
                timer: 3500,
                toast: true,
                position: 'top-end',
                showConfirmButton: false
            });
        }
    };

    window.startPollingHardware = function(token, modulo) {
        if (pollHardwareInterval) clearInterval(pollHardwareInterval);

        pollHardwareInterval = setInterval(async () => {
            try {
                const res = await fetch(HW_URLS.check.replace('__TOKEN__', token));
                const { data } = await leerJsonSeguro(res);

                if (data && data.success && data.status === 'completed' && data.hardware) {
                    clearInterval(pollHardwareInterval);
                    Swal.close();
                    window.procesarDatosHardware(data.hardware, modulo);
                }
            } catch (e) {
                console.error(e);
            }
        }, 1000);
    };

    window.agregarFilaConDatos = function(modulo, descripcion, observacion, estado = 'OPERATIVO', propio = 'EXCLUSIVO', especificaciones = null) {
        let body = document.getElementById(`body_equipos_${modulo}`);
        if (!body) {
            body = document.querySelector('tbody[id^="body_equipos_"]');
        }
        if (!body) return;

        let specsStr = '';
        if (especificaciones) {
            specsStr = typeof especificaciones === 'object' ? JSON.stringify(especificaciones) : String(especificaciones);
        }

        // Detección inteligente para evitar duplicar equipos al pulsar Autodetectar nuevamente
        const descUpper = descripcion.trim().toUpperCase();
        const obsUpper = observacion ? observacion.trim().toUpperCase() : '';
        const rows = body.querySelectorAll('tr');

        for (let r of rows) {
            const inputDesc = r.querySelector('input[name*="[descripcion]"]');
            const inputObs = r.querySelector('input[name*="[observacion]"]');
            const inputSpecs = r.querySelector('input[name*="[especificaciones]"]');
            const valDesc = inputDesc ? inputDesc.value.trim().toUpperCase() : '';
            const valObs = inputObs ? inputObs.value.trim().toUpperCase() : '';

            if (valDesc === descUpper && (valObs === obsUpper || ['CPU', 'LAPTOP', 'ALL-IN-ONE', 'MONITOR'].includes(valDesc))) {
                if (specsStr && inputSpecs) {
                    inputSpecs.value = specsStr;
                }
                console.log(`[Autodetección] El equipo '${descripcion}' ya está presente en la tabla. Se actualizaron especificaciones.`);
                return;
            }
        }

        const noDataRow = body.querySelector('tr[id^="no_data_"]');
        if (noDataRow) noDataRow.remove();

        const uniqueId = Date.now() + Math.floor(Math.random() * 1000);

        const row = document.createElement('tr');
        row.className = 'group/row hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-none';

        row.innerHTML = `
            <td class="px-6 py-4">
                <input type="text" name="equipos[${uniqueId}][descripcion]" value="${descripcion}" class="input-table-text" required list="list_equipos_master" placeholder="Seleccione...">
                <input type="hidden" id="especificaciones_${uniqueId}" name="equipos[${uniqueId}][especificaciones]" value='${specsStr.replace(/'/g, "&#39;")}'>
            </td>
            <td class="px-2 py-4 text-center">
                <input type="number" name="equipos[${uniqueId}][cantidad]" value="1" class="input-table-text text-center font-bold" min="1">
            </td>
            <td class="px-4 py-4">
                <select name="equipos[${uniqueId}][estado]" class="input-table-select">
                    <option value="OPERATIVO" ${estado === 'OPERATIVO' ? 'selected' : ''}>OPERATIVO</option>
                    <option value="REGULAR" ${estado === 'REGULAR' ? 'selected' : ''}>REGULAR</option>
                    <option value="INOPERATIVO" ${estado === 'INOPERATIVO' ? 'selected' : ''}>INOPERATIVO</option>
                </select>
            </td>
            <td class="px-4 py-4">
                <select name="equipos[${uniqueId}][propio]" class="input-table-select">
                    <option value="EXCLUSIVO" ${propio === 'EXCLUSIVO' ? 'selected' : ''}>EXCLUSIVO</option>
                    <option value="COMPARTIDO" ${propio === 'COMPARTIDO' ? 'selected' : ''}>COMPARTIDO</option>
                    <option value="PERSONAL" ${propio === 'PERSONAL' ? 'selected' : ''}>PERSONAL</option>
                    <option value="ALQUILADO" ${propio === 'ALQUILADO' ? 'selected' : ''}>ALQUILADO</option>
                </select>
            </td>
            <td class="px-4 py-4">
                <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 border border-slate-200 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 transition-all">
                    <select id="prefix_${uniqueId}" onchange="updateCompositeSerial('${uniqueId}')" 
                            class="bg-white text-[10px] font-black text-indigo-600 rounded-lg py-2 px-1 border-none focus:ring-0 cursor-pointer shadow-sm w-14 text-center shrink-0">
                        <option value="S">S</option>
                        <option value="CP">CP</option>
                    </select>
                    <input type="text" id="visual_${uniqueId}" oninput="updateCompositeSerial('${uniqueId}')"
                           class="w-full bg-transparent border-none text-xs font-bold text-slate-700 placeholder-slate-400 focus:ring-0 p-1 uppercase min-w-0" 
                           placeholder="Digite...">
                    <input type="hidden" id="final_${uniqueId}" name="equipos[${uniqueId}][nro_serie]">
                    <button type="button" onclick="openScannerForComposite('${uniqueId}')" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors shrink-0">
                        <i data-lucide="scan-line" class="w-4 h-4"></i>
                    </button>
                </div>
            </td>
            <td class="px-4 py-4">
                <input type="text" name="equipos[${uniqueId}][observacion]" value="${observacion}" class="input-table-text uppercase">
            </td>
            <td class="px-4 py-4 text-right">
                <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-500 transition-all opacity-0 group-hover/row:opacity-100">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </td>
        `;

        body.appendChild(row);
        if (window.lucide) window.lucide.createIcons();
    }
</script>

<style>
    .input-table-text {
        width: 100%; background: transparent; border: none; border-bottom: 2px solid #f1f5f9;
        padding: 4px 6px; font-size: 0.75rem; font-weight: 700; color: #334155;
        outline: none; transition: all 0.3s;
    }
    .input-table-text:focus { border-bottom-color: #6366f1; }
    
    .input-table-select {
        width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem;
        padding: 4px 8px; font-size: 0.7rem; font-weight: 800; color: #475569;
        outline: none; cursor: pointer; transition: all 0.3s;
    }
    .input-table-select:focus { border-color: #6366f1; background: white; }

    #reader { position: relative; }
    #reader::after {
        content: ""; position: absolute; top: 50%; left: 0; width: 100%; height: 2px;
        background: rgba(239, 68, 68, 0.7); box-shadow: 0 0 8px red; z-index: 10;
        animation: scanLine 2s linear infinite;
    }
    @keyframes scanLine {
        0% { top: 20%; opacity: 0; }
        50% { top: 50%; opacity: 1; }
        100% { top: 80%; opacity: 0; }
    }
</style>