@extends('layouts.usuario')
@section('title', 'Actas de Reunión')

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .input-modern {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.6rem 0.75rem;
            transition: all 0.2s;
        }
        .input-modern:focus {
            border-color: #6366f1;
            ring: 2px;
            ring-color: #6366f1;
            outline: none;
        }
        #qr-container canvas, #qr-container img {
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto !important;
        }
        @media (max-height: 800px) {
            #modal-qr .max-w-md {
                max-width: 28rem !important;
            }
            #modal-qr .px-6 {
                padding-left: 1.5rem !important;
                padding-right: 1.5rem !important;
            }
            #modal-qr .py-4 {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }
            #modal-qr .mb-4 {
                margin-bottom: 0.75rem !important;
            }
            #modal-qr .p-4 {
                padding: 1rem !important;
            }
            #modal-qr .p-3 {
                padding: 0.75rem !important;
            }
            #modal-qr .rounded-3xl {
                border-radius: 2rem !important;
            }
            #modal-qr .rounded-t-3xl {
                border-top-left-radius: 2rem !important;
                border-top-right-radius: 2rem !important;
            }
            #modal-qr .rounded-b-3xl {
                border-bottom-left-radius: 2rem !important;
                border-bottom-right-radius: 2rem !important;
            }
            #modal-qr #qr-container canvas, #modal-qr #qr-container img {
                width: 165px !important;
                height: 165px !important;
            }
        }
    </style>
@endpush

@section('header-content')
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Actas de Reunión</h1>
    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
        <span class="text-indigo-600">Operaciones</span>
        <span class="text-slate-300">•</span>
        <span>Reuniones</span>
    </div>
@endsection

@section('content')
    <div x-data="{ open: {{ request()->anyFilled(['implementador', 'fecha_desde', 'fecha_hasta', 'firmado']) ? 'true' : 'false' }} }" class="w-full">

        {{-- MÉTRICAS Y ACCIONES RÁPIDAS --}}
        <div class="bg-gradient-to-r from-indigo-600 to-blue-500 p-5 rounded-2xl shadow-xl mb-6 relative overflow-hidden text-white">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
            <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-6">
                <div class="flex flex-wrap items-center gap-3 text-center sm:text-left">
                    <div class="bg-slate-900 text-white rounded-xl px-5 py-2.5 shadow-lg border border-slate-700 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-bold leading-none">{{ $total_reuniones }}</span>
                        <span class="text-[0.65rem] uppercase tracking-widest text-slate-400 font-semibold mt-1">Total</span>
                    </div>
                    <div class="bg-white/20 backdrop-blur-md text-white rounded-xl px-5 py-2.5 border border-white/30 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-bold leading-none">{{ $countFirmadas }}</span>
                        <span class="text-[0.65rem] uppercase tracking-widest text-indigo-100 font-semibold mt-1">Firmadas</span>
                    </div>
                    <div class="bg-amber-500 text-white rounded-xl px-5 py-2.5 shadow-lg border border-amber-400 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-bold leading-none">{{ $countPendientes }}</span>
                        <span class="text-[0.65rem] uppercase tracking-widest text-amber-100 font-semibold mt-1">Pendientes</span>
                    </div>
                    <div class="bg-slate-800 text-white rounded-xl px-5 py-2.5 shadow-lg border border-slate-700 flex flex-col items-center min-w-[100px]">
                        <span class="text-2xl font-bold leading-none">{{ $countAnuladas }}</span>
                        <span class="text-[0.65rem] uppercase tracking-widest text-slate-400 font-semibold mt-1">Anuladas</span>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full lg:w-auto justify-center lg:justify-end">
                    <button @click="open = !open" type="button" class="flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition-all shadow-lg border border-white/20 text-white bg-white/10 hover:bg-white/20 backdrop-blur-sm">
                        <i data-lucide="filter" class="w-4 h-4" x-show="!open"></i>
                        <i data-lucide="filter-x" class="w-4 h-4" x-show="open" x-cloak></i>
                        <span x-text="open ? 'Ocultar Filtros' : 'Mostrar Filtros'"></span>
                    </button>
                    <a href="{{ route('usuario.reuniones.create') }}" class="flex items-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition-all shadow-lg bg-white text-indigo-700 hover:bg-indigo-50 border border-transparent">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        <span>Nueva Acta</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- FILTROS AVANZADOS --}}
        <form x-show="open" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            method="GET" action="{{ route('usuario.reuniones.index') }}"
            class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6 space-y-4">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">

                {{-- Implementador --}}
                <div class="lg:col-span-2">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Implementador</label>
                    <select name="implementador" class="input-modern w-full uppercase">
                        <option value="">Todos</option>
                        @foreach ($implementadores as $impl)
                            <option value="{{ $impl }}" {{ request('implementador') == $impl ? 'selected' : '' }}>{{ $impl }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Estado --}}
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Estado Firma</label>
                    <select name="firmado" class="input-modern w-full uppercase">
                        <option value="">Todos</option>
                        <option value="1" {{ request('firmado') === '1' ? 'selected' : '' }}>Firmado</option>
                        <option value="0" {{ request('firmado') === '0' ? 'selected' : '' }}>Pendiente</option>
                    </select>
                </div>

                {{-- Visibilidad --}}
                <div>
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Visibilidad</label>
                    <select name="estado_anulado" class="input-modern w-full uppercase">
                        <option value="todos" {{ request('estado_anulado', 'todos') == 'todos' ? 'selected' : '' }}>Todas</option>
                        <option value="activo" {{ request('estado_anulado') == 'activo' ? 'selected' : '' }}>Activas</option>
                        <option value="anulado" {{ request('estado_anulado') == 'anulado' ? 'selected' : '' }}>Anuladas</option>
                    </select>
                </div>

                {{-- Fechas --}}
                <div class="lg:col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Desde</label>
                    <input type="date" name="fecha_desde" value="{{ $valDesde }}" class="input-modern w-full">
                </div>
                <div class="lg:col-span-1">
                    <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 ml-1">Hasta</label>
                    <input type="date" name="fecha_hasta" value="{{ $valHasta }}" class="input-modern w-full">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-slate-50">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center gap-2 transition-all">
                    <i data-lucide="search" class="w-4 h-4"></i> FILTRAR
                </button>
                <a href="{{ route('usuario.reuniones.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs flex items-center gap-2 transition-all">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i> LIMPIAR
                </a>
                @if($total_reuniones > 0)
                <div class="flex gap-2">
                    <button type="button" onclick="exportarExcel()" class="px-5 py-2.5 bg-green-50 text-green-700 hover:bg-green-100 font-bold text-xs rounded-xl flex items-center gap-2 transition-all border border-green-200">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> EXPORTAR EXCEL
                    </button>
                    <button type="button" onclick="exportarPDF()" class="px-5 py-2.5 bg-red-50 text-red-700 hover:bg-red-100 font-bold text-xs rounded-xl flex items-center gap-2 transition-all border border-red-200">
                        <i data-lucide="file-text" class="w-4 h-4"></i> EXPORTAR PDF
                    </button>
                </div>
                @endif
            </div>
        </form>

        {{-- TABLA DE RESULTADOS --}}
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-800 text-white text-xs">
                        <tr>
                            <th class="px-4 py-4 font-bold uppercase text-center w-16">#</th>
                            <th class="px-4 py-4 font-bold uppercase text-center w-24">Fecha</th>
                            <th class="px-4 py-4 font-bold uppercase">Título de la Reunión / Institución</th>
                            <th class="px-4 py-4 font-bold uppercase text-center w-32">Estado Doc.</th>
                            <th class="px-4 py-4 font-bold uppercase text-right w-32">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-600">
                        @forelse($reuniones as $item)
                            <tr class="hover:bg-indigo-50/30 transition-colors group {{ $item->anulado ? 'bg-red-50/50' : '' }}" data-id="{{ $item->id }}" data-tiene-archivo="{{ $item->archivo_pdf ? 'true' : 'false' }}">
                                <td class="px-4 py-4 text-center font-mono font-bold text-slate-400">#{{ str_pad($item->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-4 py-4 text-center">
                                    <div class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($item->fecha_reunion)->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-slate-400 font-medium">{{ \Carbon\Carbon::parse($item->hora_reunion)->format('H:i') }} hrs</div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-black text-slate-800 uppercase tracking-tight text-sm">{{ $item->titulo_reunion }}</div>
                                    <div class="text-[11px] text-slate-500 font-bold flex items-center gap-1.5 mt-0.5">
                                        <i data-lucide="building-2" class="w-3 h-3"></i>
                                        {{ $item->nombre_institucion }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center whitespace-nowrap">
                                    <div class="flex flex-col items-center justify-center gap-1">
                                        @if($item->anulado)
                                            <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black bg-red-100 text-red-700 border border-red-200 uppercase">ANULADA</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black bg-emerald-100 text-emerald-700 border border-emerald-200 uppercase">ACTIVA</span>
                                            @if($item->firmado)
                                                <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black bg-blue-100 text-blue-700 border border-blue-200 uppercase flex items-center gap-1">
                                                    <i data-lucide="check-circle" class="w-2.5 h-2.5"></i> FIRMADA
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-700 border border-amber-200 uppercase">PENDIENTE</span>
                                            @endif
                                        @endif
                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if(!$item->anulado)
                                            {{-- Subir/Reemplazar PDF Firmado --}}
                                            <button onclick="abrirModalSubirReunion({{ $item->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition-all" title="{{ $item->firmado ? 'Reemplazar Acta Firmada' : 'Subir Acta Firmada' }}">
                                                <i data-lucide="{{ $item->firmado ? 'refresh-cw' : 'upload-cloud' }}" class="w-4 h-4"></i>
                                            </button>

                                            @if($item->firmado && $item->archivo_pdf)
                                                <a href="{{ asset('storage/' . $item->archivo_pdf) }}" target="_blank" class="p-1.5 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-100 transition-all" title="Ver Acta Firmada"><i data-lucide="file-check-2" class="w-4 h-4"></i></a>
                                            @endif

                                            <a href="{{ route('usuario.reuniones.pdf', $item->id) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-all" title="Ver PDF del Sistema"><i data-lucide="file-text" class="w-4 h-4"></i></a>
                                            <a href="{{ route('usuario.reuniones.edit', $item->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all" title="Editar"><i data-lucide="pencil" class="w-4 h-4"></i></a>
                                            
                                            {{-- Botón QR de Asistencia --}}
                                            @php
                                                $estaActiva = $item->asistencia_desde && $item->asistencia_hasta && now()->between($item->asistencia_desde, $item->asistencia_hasta);
                                            @endphp
                                            <button onclick="mostrarModalQR({{ $item->id }}, '{{ addslashes($item->titulo_reunion) }}', '{{ route('asistencia.show', $item->id) }}', '{{ $item->asistencia_hasta ? \Carbon\Carbon::parse($item->asistencia_hasta)->format('d/m/Y H:i') : '' }}', {{ $estaActiva ? 'true' : 'false' }})" 
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-purple-600 hover:bg-purple-50 transition-all" title="QR de Asistencia">
                                                <i data-lucide="qr-code" class="w-4 h-4"></i>
                                            </button>
                                        @endif
                                        <button onclick="confirmarAnulacion({{ $item->id }}, {{ $item->anulado ? 'true' : 'false' }})" class="p-1.5 rounded-lg {{ $item->anulado ? 'text-emerald-500 hover:bg-emerald-50' : 'text-red-400 hover:bg-red-50' }} transition-all" title="{{ $item->anulado ? 'Reactivar' : 'Anular' }}">
                                            <i data-lucide="{{ $item->anulado ? 'refresh-cw' : 'ban' }}" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-xs italic">
                                    <div class="flex flex-col items-center gap-2">
                                        <i data-lucide="inbox" class="w-8 h-8 opacity-20"></i>
                                        No se encontraron actas de reunión registradas
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reuniones->hasPages())
                <div class="p-4 border-t border-slate-50 text-xs">
                    {{ $reuniones->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL QR ASISTENCIA --}}
    {{-- Backdrop separado --}}
    <div id="modal-qr-backdrop" class="hidden fixed inset-0 z-[101] bg-slate-900/75 transition-opacity" onclick="cerrarModalQR()"></div>
    {{-- Panel del modal --}}
    <div id="modal-qr" class="hidden fixed inset-0 z-[102] flex items-center justify-center p-4 animate-in fade-in duration-200" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="bg-white rounded-3xl text-left shadow-2xl w-full max-w-md max-h-[95vh] flex flex-col" onclick="event.stopPropagation()">
            <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between rounded-t-3xl shrink-0">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i data-lucide="qr-code" class="w-5 h-5"></i>
                    QR de Asistencia
                </h3>
                <button onclick="cerrarModalQR()" class="text-indigo-100 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="px-6 py-4 overflow-y-auto flex-1 custom-scroll">
                <div class="text-center mb-4">
                    <h4 id="qr-titulo" class="text-sm font-bold text-slate-800 uppercase tracking-tight"></h4>
                    <p class="text-[11px] text-slate-500 mt-1">Escanee el código para registrar su asistencia</p>
                </div>

                <div class="flex flex-col items-center justify-center bg-slate-50 rounded-3xl p-4 border-2 border-dashed border-slate-200 mb-4">
                    <div id="qr-container" class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100">
                        {{-- Aquí se insertará el QR --}}
                    </div>
                    <p id="qr-url" class="text-[10px] text-slate-400 mt-2 font-mono break-all text-center px-4"></p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-600">Estado de Asistencia:</span>
                        <span id="qr-status" class="px-2 py-1 rounded-lg text-[10px] font-black border uppercase bg-slate-100 text-slate-700 border-slate-200">CERRADO</span>
                    </div>
                    
                    <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                        <label class="block text-[10px] font-black text-indigo-400 uppercase mb-2 ml-1">Activar por tiempo limitado (minutos)</label>
                        <div class="flex gap-2">
                            <input type="number" id="qr-minutos" value="240" min="1" class="w-full bg-white border border-indigo-200 rounded-xl px-4 py-2 text-sm focus:outline-none font-bold text-indigo-700">
                            <button type="button" onclick="activarAsistencia()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl text-xs flex items-center gap-2 transition-all shadow-md">
                                <i data-lucide="play" class="w-4 h-4"></i> ACTIVAR
                            </button>
                        </div>
                        <p id="qr-expira" class="text-[10px] text-indigo-500 mt-2 font-medium"></p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-8 py-5 flex items-center justify-between rounded-b-3xl shrink-0 border-t border-slate-100">
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">ICATEC - SISTEMA DE ACTAS</p>
                <a id="qr-proyectar-btn" href="#" target="_blank" class="bg-white border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-200 px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 transition-all shadow-sm">
                    <i data-lucide="monitor" class="w-4 h-4"></i>
                    Modo Proyectar
                </a>
            </div>
        </div>
    </div>
</div>

    {{-- Formulario oculto para exportar Excel --}}
    <form id="excelForm" method="POST" action="{{ route('usuario.reportes.actas.reuniones.excel') }}" style="display:none;">
        @csrf
        <input type="hidden" name="titulo" value="{{ request('titulo') }}">
        <input type="hidden" name="institucion" value="{{ request('institucion') }}">
        <input type="hidden" name="fecha_desde" value="{{ $valDesde }}">
        <input type="hidden" name="fecha_hasta" value="{{ $valHasta }}">
        <input type="hidden" name="firmado" value="{{ request('firmado') }}">
        <input type="hidden" name="estado_anulado" value="{{ request('estado_anulado') }}">
    </form>

    {{-- Formulario oculto para exportar PDF Consolidado --}}
    <form id="pdfConsolidadoForm" method="POST" action="{{ route('usuario.reuniones.consolidadoPDFExport') }}" style="display:none;">
        @csrf
        <input type="hidden" name="titulo" value="{{ request('titulo') }}">
        <input type="hidden" name="institucion" value="{{ request('institucion') }}">
        <input type="hidden" name="fecha_desde" value="{{ $valDesde }}">
        <input type="hidden" name="fecha_hasta" value="{{ $valHasta }}">
        <input type="hidden" name="firmado" value="{{ request('firmado') }}">
        <input type="hidden" name="estado_anulado" value="{{ request('estado_anulado') }}">
    </form>

    {{-- MENU CONTEXTUAL --}}
    <div id="context-menu" class="hidden fixed z-[9999] w-64 bg-white rounded-2xl shadow-2xl border border-slate-200 p-2 animate-in fade-in zoom-in duration-200">
        <div class="px-3 py-2 border-b border-slate-100 mb-1">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Opciones de Acta</p>
        </div>
        <a id="ctx-visual-sig" href="#" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-orange-600 hover:bg-orange-50 rounded-xl transition-all hidden">
            <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">
                <i data-lucide="pen-tool" class="w-4 h-4"></i>
            </div>
            <span>Firmar Acta Visualmente</span>
        </a>
        <a id="ctx-edit" href="#" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-blue-50 hover:text-blue-700 rounded-xl transition-all">
            <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                <i data-lucide="pencil" class="w-4 h-4"></i>
            </div>
            Editar Acta
        </a>
        <a id="ctx-pdf" href="#" target="_blank" class="flex items-center gap-3 px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-red-50 hover:text-red-700 rounded-xl transition-all">
            <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                <i data-lucide="file-text" class="w-4 h-4"></i>
            </div>
            Ver PDF Temporal
        </a>
        <div class="mt-2 pt-2 border-t border-slate-100">
            <p class="text-[9px] text-center text-slate-400 italic font-medium">Click derecho en filas para abrir</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>
    <script>
        function exportarExcel() {
            document.getElementById('excelForm').submit();
        }

        async function exportarPDF() {
            const form = document.getElementById('pdfConsolidadoForm');
            const formData = new FormData(form);
            
            Swal.fire({
                title: 'Generando documento',
                text: 'Descargando y fusionando los PDFs, por favor espere...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Error al obtener los documentos');
                }

                if (data.urls && data.urls.length > 0) {
                    const { PDFDocument } = PDFLib;
                    const mergedPdf = await PDFDocument.create();

                    for (const url of data.urls) {
                        try {
                            const pdfBytes = await fetch(url).then(res => res.arrayBuffer());
                            const pdfDoc = await PDFDocument.load(pdfBytes);
                            const copiedPages = await mergedPdf.copyPages(pdfDoc, pdfDoc.getPageIndices());
                            copiedPages.forEach((page) => {
                                mergedPdf.addPage(page);
                            });
                        } catch (err) {
                            console.error("Error cargando PDF: " + url, err);
                        }
                    }

                    const mergedPdfFile = await mergedPdf.save();
                    const blob = new Blob([mergedPdfFile], { type: 'application/pdf' });
                    const blobUrl = URL.createObjectURL(blob);
                    
                    window.open(blobUrl, '_blank');
                    
                    let summaryHtml = `<div class="text-left mt-2">
                        <p class="mb-2">Se ha generado el documento con <b>${data.incluidas}</b> de <b>${data.total}</b> actas firmadas.</p>`;
                    if (data.omitidas > 0) {
                        summaryHtml += `<div class="p-3 bg-red-50 text-red-700 rounded-lg border border-red-200 text-sm">
                            <div class="mb-2"><i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                            <b>${data.omitidas} actas omitidas</b> por no tener archivo:</div>`;
                        
                        if (data.lista_omitidas && data.lista_omitidas.length > 0) {
                            summaryHtml += `<ul class="list-disc list-inside text-xs max-h-32 overflow-y-auto bg-white/50 p-2 rounded">`;
                            data.lista_omitidas.forEach(idText => {
                                summaryHtml += `<li>${idText}</li>`;
                            });
                            summaryHtml += `</ul>`;
                        }
                        summaryHtml += `</div>`;
                    }
                    summaryHtml += `</div>`;

                    Swal.fire({
                        icon: 'success',
                        title: 'PDF Generado',
                        html: summaryHtml,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#10b981',
                        didOpen: () => {
                            if (window.lucide) window.lucide.createIcons();
                        }
                    });
                } else {
                    throw new Error('No se encontraron URLs de PDFs.');
                }

            } catch (error) {
                Swal.fire('Error', error.message, 'error');
            }
        }

        let currentReunionId = null;
        let qrGenerator = null;

        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) window.lucide.createIcons();

            const contextMenu = document.getElementById('context-menu');
            const ctxVisualSig = document.getElementById('ctx-visual-sig');
            const ctxEdit = document.getElementById('ctx-edit');
            const ctxPdf = document.getElementById('ctx-pdf');

            document.querySelectorAll('table tbody tr').forEach(row => {
                row.addEventListener('contextmenu', (e) => {
                    const id = row.dataset.id;
                    if (!id) return;

                    e.preventDefault();
                    
                    ctxEdit.href = `/usuario/actas-reunion/${id}/editar`;
                    ctxPdf.href = `/usuario/actas-reunion/${id}/pdf`;

                    contextMenu.classList.remove('hidden');
                    let top = e.pageY;
                    let left = e.pageX;

                    if (left + 256 > window.innerWidth) left -= 256;
                    if (top + 200 > window.innerHeight) top -= 200;

                    contextMenu.style.top = `${top}px`;
                    contextMenu.style.left = `${left}px`;
                    if (window.lucide) window.lucide.createIcons();
                });
            });

            document.addEventListener('click', (e) => {
                if (!contextMenu.contains(e.target)) contextMenu.classList.add('hidden');
            });
            document.addEventListener('scroll', () => contextMenu.classList.add('hidden'));
        });

        function mostrarModalQR(id, titulo, url, expira, isActive) {
            currentReunionId = id;
            document.getElementById('qr-titulo').innerText = titulo;
            document.getElementById('qr-url').innerText = url;
            document.getElementById('qr-proyectar-btn').href = `/usuario/actas-reunion/${id}/proyectar-qr`;
            
            const statusEl = document.getElementById('qr-status');
            const expiraEl = document.getElementById('qr-expira');
            
            if (isActive) {
                statusEl.innerText = 'ACTIVO';
                statusEl.className = 'px-2 py-1 rounded-lg text-[10px] font-black border uppercase bg-emerald-100 text-emerald-700 border-emerald-200';
                expiraEl.innerText = 'Expira el: ' + expira;
            } else {
                statusEl.innerText = 'CERRADO';
                statusEl.className = 'px-2 py-1 rounded-lg text-[10px] font-black border uppercase bg-slate-100 text-slate-700 border-slate-200';
                expiraEl.innerText = 'No activado';
            }

            // Generar QR
            const container = document.getElementById('qr-container');
            container.innerHTML = '';
            qrGenerator = new QRCode(container, {
                text: url,
                width: 180,
                height: 180,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });

            document.getElementById('modal-qr').classList.remove('hidden');
            document.getElementById('modal-qr-backdrop').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            if (window.lucide) window.lucide.createIcons();
        }

        function cerrarModalQR() {
            document.getElementById('modal-qr').classList.add('hidden');
            document.getElementById('modal-qr-backdrop').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function activarAsistencia() {
            const minutos = document.getElementById('qr-minutos').value;
            if (!minutos || minutos < 1) return Swal.fire('Error', 'Ingrese un tiempo válido', 'error');

            Swal.fire({
                title: 'Activando...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            fetch(`{{ url('/usuario/actas-reunion') }}/${currentReunionId}/activar-asistencia`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ minutos: minutos })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Éxito!', data.message, 'success').then(() => window.location.reload());
                } else {
                    throw new Error(data.message);
                }
            })
            .catch(err => Swal.fire('Error', err.message, 'error'));
        }

        function confirmarAnulacion(id, isAnulado) {
            const title = isAnulado ? '¿Reactivar acta?' : '¿Anular acta?';
            const text = isAnulado ? 'El acta volverá a estar activa y editable.' : 'El acta quedará inactiva y no se podrá editar.';
            const confButton = isAnulado ? 'Sí, reactivar' : 'Sí, anular';

            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: isAnulado ? '#10b981' : '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: confButton,
                cancelButtonText: 'Cancelar',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(`{{ url('/usuario/actas-reunion') }}/${id}/anular`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) return data;
                        throw new Error(data.message || 'Error en la operación');
                    })
                    .catch(error => Swal.showValidationMessage(`Fallo: ${error}`));
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('¡Hecho!', result.value.message, 'success').then(() => window.location.reload());
                }
            });
        }

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: @json(session('success')),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: @json(session('error')),
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true
            });
        @endif

        // --- ACCESO SECRETO: TOGGLE FIRMA VISUAL ("jojojo") ---
        let _firmaSecretaDesbloqueada = false;
        let _secretBuffer = '';

        function _desbloquearFirmaVisual(mostrarToast = true) {
            _firmaSecretaDesbloqueada = true;
            try { localStorage.setItem('firma_secreta_activa', 'true'); } catch(e){}

            // 1. Desocultar botones en las filas de la tabla
            document.querySelectorAll('.btn-firma-visual-hidden').forEach(el => {
                el.classList.remove('hidden');
                el.style.display = 'inline-flex';
            });

            // 2. Desocultar opción en el menú contextual
            const ctxVisualSig = document.getElementById('ctx-visual-sig');
            if (ctxVisualSig) {
                ctxVisualSig.classList.remove('hidden');
                ctxVisualSig.style.display = 'flex';
            }

            // 3. Desocultar Opción 1 dentro del modal de SweetAlert (si está abierto)
            const swalOpc1 = document.getElementById('swal-opcion-1-firma');
            if (swalOpc1) {
                swalOpc1.classList.remove('hidden');
                swalOpc1.style.display = 'block';
            }
            const swalOpc2Label = document.getElementById('swal-opcion-2-label');
            if (swalOpc2Label) {
                swalOpc2Label.textContent = 'Opción 2: Subir PDF escaneado/firmado';
            }

            // 4. Feedback visual con imagen del aviso (overlay limpio sin contenedores extra)
            if (mostrarToast) {
                const avisoImgUrl = "{{ asset('storage/aviso/8427ac357141001bae14eb38e78856dd.jpg') }}?v={{ @filemtime(public_path('storage/aviso/8427ac357141001bae14eb38e78856dd.jpg')) ?: time() }}";
                const overlay = document.createElement('div');
                overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);z-index:999999;display:flex;align-items:center;justify-content:center;transition:opacity 0.25s ease;';
                overlay.innerHTML = `
                    <div style="max-width:480px;width:90vw;border-radius:1.5rem;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,0.6);transform:scale(1);">
                        <img src="${avisoImgUrl}" alt="¡AAAAHH, CONOCES LA LLAVE!" style="width:100%;height:auto;display:block;border-radius:1.5rem;">
                    </div>
                `;
                document.body.appendChild(overlay);
                setTimeout(() => {
                    overlay.style.opacity = '0';
                    setTimeout(() => overlay.remove(), 250);
                }, 2200);
            }

            if (window.lucide) window.lucide.createIcons();
        }

        function _bloquearFirmaVisual(mostrarToast = true) {
            _firmaSecretaDesbloqueada = false;
            try { localStorage.removeItem('firma_secreta_activa'); } catch(e){}

            // 1. Ocultar botones en las filas de la tabla
            document.querySelectorAll('.btn-firma-visual-hidden').forEach(el => {
                el.classList.add('hidden');
                el.style.display = 'none';
            });

            // 2. Ocultar opción en el menú contextual
            const ctxVisualSig = document.getElementById('ctx-visual-sig');
            if (ctxVisualSig) {
                ctxVisualSig.classList.add('hidden');
                ctxVisualSig.style.display = 'none';
            }

            // 3. Ocultar Opción 1 dentro del modal de SweetAlert (si está abierto)
            const swalOpc1 = document.getElementById('swal-opcion-1-firma');
            if (swalOpc1) {
                swalOpc1.classList.add('hidden');
                swalOpc1.style.display = 'none';
            }
            const swalOpc2Label = document.getElementById('swal-opcion-2-label');
            if (swalOpc2Label) {
                swalOpc2Label.textContent = 'Subir PDF escaneado/firmado';
            }
        }

        function _toggleFirmaVisual() {
            if (_firmaSecretaDesbloqueada) {
                _bloquearFirmaVisual(true);
            } else {
                _desbloquearFirmaVisual(true);
            }
        }

        // Si ya se desbloqueó anteriormente en este navegador, mantenerlo activo
        if (localStorage.getItem('firma_secreta_activa') === 'true') {
            _firmaSecretaDesbloqueada = true;
            document.addEventListener('DOMContentLoaded', () => {
                _desbloquearFirmaVisual(false);
            });
        }

        function _procesarTeclaSecreta(val) {
            if (!val) return;
            
            _secretBuffer += String(val).toLowerCase();
            if (_secretBuffer.length > 30) _secretBuffer = _secretBuffer.slice(-30);

            if (_secretBuffer.includes('jojojo')) {
                _secretBuffer = ''; // Reiniciar buffer para evitar disparo continuo
                _toggleFirmaVisual();
            }
        }

        // Listener keyup en window (con captura)
        window.addEventListener('keyup', function(e) {
            if (e.key && e.key.length === 1) {
                _procesarTeclaSecreta(e.key);
            }
        }, true);

        document.addEventListener('input', function(e) {
            if (e.target && e.target.value) {
                _procesarTeclaSecreta(e.target.value);
            }
        }, true);

        function abrirModalSubirReunion(id) {
            const baseUrl = "{{ url('/usuario/actas-reunion') }}";
            const rutaFirma = `${baseUrl}/${id}/visual-signature`;

            const opc1Hidden = _firmaSecretaDesbloqueada ? '' : 'hidden';
            const opc1Style  = _firmaSecretaDesbloqueada ? 'display:block;' : 'display:none;';
            const labelOpc2  = _firmaSecretaDesbloqueada ? 'Opción 2: Subir PDF escaneado/firmado' : 'Subir PDF escaneado/firmado';

            Swal.fire({
                title: 'Finalizar / Firmar Acta de Reunión',
                html: `
                    <div class="space-y-4 p-2 text-left">
                        <!-- Opción 1: Firmar Acta Visualmente (se revela con 'jojojo') -->
                        <div id="swal-opcion-1-firma" class="${opc1Hidden} transition-all duration-300 mb-4 p-3.5 bg-orange-50 border border-orange-200 rounded-xl" style="${opc1Style}">
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-orange-500 text-white rounded-lg shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19 7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18"/><path d="m2 2 7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-xs font-black text-orange-900 uppercase tracking-wider">Opción 1: Firmar Acta Visualmente</h4>
                                    <p class="text-[11px] text-orange-700 font-medium mt-0.5">Diseñe y estampe las firmas interactivamente en la plataforma.</p>
                                    <a href="${rutaFirma}" class="mt-2.5 inline-flex items-center gap-2 px-3.5 py-1.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-lg shadow-sm transition-all hover:scale-[1.02]">
                                        <span>Abrir Editor de Firma</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Opción 2: Subir PDF escaneado/firmado -->
                        <div class="p-1">
                            <label id="swal-opcion-2-label" class="text-[10px] font-bold text-slate-500 uppercase mb-1.5 block tracking-wider">
                                ${labelOpc2}
                            </label>
                            <input type="file" id="swal-input-file" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 border border-slate-200 rounded-xl p-2" accept="application/pdf">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Subir Archivo Seleccionado',
                cancelButtonText: 'Cerrar',
                confirmButtonColor: '#9333ea',
                showLoaderOnConfirm: true,
                didOpen: () => {
                    if (window.lucide) window.lucide.createIcons();
                },
                preConfirm: () => {
                    const file = document.getElementById('swal-input-file').files[0];
                    if (!file) {
                        Swal.showValidationMessage('Debe seleccionar un archivo PDF para la Opción 2');
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('pdf_firmado', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    const rootUrl = "{{ url('/') }}";
                    return fetch(`${rootUrl}/usuario/actas-reunion/${id}/subir-pdf`, {
                        method: 'POST',
                        body: formData,
                        headers: { 
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        if (!response.ok) return response.json().then(err => { throw new Error(err.message || 'Error en el servidor'); });
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Hubo un problema: ${error.message}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Acta Cargada!',
                        text: 'El acta firmada ha sido subida correctamente.',
                        timer: 2000
                    }).then(() => location.reload());
                }
            });
        }
    </script>
@endpush
