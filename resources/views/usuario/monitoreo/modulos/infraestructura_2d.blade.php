@extends('layouts.usuario')

@section('title', 'Croquis de Infraestructura | ' . $acta->establecimiento->nombre)

@push('styles')
    <style>
        #blueprint-canvas {
            background-color: #f8fafc;
            background-image:
                linear-gradient(rgba(226, 232, 240, 0.4) 1px, transparent 1px),
                linear-gradient(90deg, rgba(226, 232, 240, 0.4) 1px, transparent 1px);
            background-size: 40px 40px;
            cursor: crosshair;
            touch-action: none;
            border-radius: 0.5rem;
            border: 2px solid #e2e8f0;
            width: 100%;
            height: 100%;
            display: block;
        }

        .tool-btn {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tool-btn:hover {
            transform: translateY(-2px);
        }

        .tool-btn.active {
            background-color: #4f46e5;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4);
        }

        .blueprint-container {
            position: relative;
            user-select: none;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.9);
                opacity: 0.7;
            }

            50% {
                transform: scale(1.1);
                opacity: 1;
            }

            100% {
                transform: scale(0.9);
                opacity: 0.7;
            }
        }

        .btn-saving {
            pointer-events: none;
            opacity: 0.7;
        }

        .undo-redo-btn:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        /* Mini-mapa */
        #minimap-panel {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #minimap-panel.collapsed {
            height: 44px;
            overflow: hidden;
        }

        #minimap-container {
            height: 220px;
            border-radius: 0 0 1rem 1rem;
            overflow: hidden;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 0.75rem !important;
            font-family: Inter, sans-serif;
            font-size: 11px;
        }

        /* ══════════════════════════════════════════════════════════════
           RESPONSIVE · PC · Laptop · Tablet · Móvil
           ══════════════════════════════════════════════════════════════ */

        /* El editor ocupa exactamente el alto disponible del layout (que ya descuenta
           su cabecera). Con 100vh se desbordaba y la barra de zoom quedaba fuera. */
        #tablet-editor-container {
            height: 100%;
            min-height: 480px;
        }

        /* En pantalla completa manda el viewport */
        #tablet-editor-container:fullscreen {
            height: 100dvh;
            min-height: 0;
            margin: 0;
            width: 100%;
        }

        /* Barras deslizables sin scrollbar visible */
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Tira de herramientas: en pantallas angostas se desliza en horizontal */
        .tool-strip {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            scroll-snap-type: x proximity;
            -webkit-overflow-scrolling: touch;
        }

        .tool-strip>button {
            flex: 0 0 auto;
            scroll-snap-align: center;
        }

        /* Cuando la tira no cabe, el borde se desvanece para indicar que se desliza */
        @media (max-width: 1279px) {
            .tool-strip {
                -webkit-mask-image: linear-gradient(to right, #000 calc(100% - 22px), transparent);
                mask-image: linear-gradient(to right, #000 calc(100% - 22px), transparent);
            }
        }

        /* El panel de herramientas se vuelve una hoja inferior (bottom-sheet) en tablet/móvil */
        @media (max-width: 1023px) {
            /* Se anula el relleno del layout: en pantallas chicas cada píxel cuenta */
            #tablet-editor-container {
                margin: -2rem;
                width: calc(100% + 4rem);
                height: calc(100% + 4rem);
                min-height: 0;
            }

            #tools-sidebar {
                top: auto !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                width: 100% !important;
                max-height: 60dvh !important;
                border-radius: 1.5rem 1.5rem 0 0 !important;
                padding-bottom: calc(1.25rem + env(safe-area-inset-bottom)) !important;
            }

            /* Objetivos táctiles cómodos (mínimo recomendado 40-44px) */
            #tools-sidebar button,
            #tools-sidebar select,
            #tools-sidebar input[type="text"],
            #tools-sidebar input[type="number"] {
                min-height: 42px;
            }

            #tools-sidebar input[type="range"] {
                height: 22px;
            }

            /* Filtros y acciones se pliegan en menús desplegables.
               Se anclan a la barra completa (no al botón, que está a la izquierda y
               dejaría el panel fuera de pantalla): los envoltorios pasan a static. */
            #topbar-filters-wrap,
            #topbar-actions-wrap {
                position: static;
            }

            #topbar-filters-menu,
            #topbar-actions-menu {
                position: absolute;
                top: calc(100% + 0.35rem);
                left: 0.5rem;
                right: 0.5rem;
                width: auto;
                z-index: 60;
                flex-direction: column;
                align-items: stretch;
                gap: 0.6rem;
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 1rem;
                padding: 0.75rem;
                box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
                max-height: 70dvh;
                overflow-y: auto;
            }

            #topbar-actions-menu>* {
                width: 100%;
                justify-content: flex-start;
            }

            /* El mini-mapa nunca debe tapar el lienzo en pantallas chicas */
            /* Se eleva por encima de la barra de zoom para no solaparse, y cede la
               capa superior a los controles y a la hoja de herramientas */
            #minimap-panel {
                width: min(88vw, 18rem) !important;
                bottom: calc(84px + env(safe-area-inset-bottom)) !important;
                right: 12px !important;
                z-index: 20 !important;
            }

            #minimap-container {
                height: 150px !important;
            }
        }

        /* Móvil: barra inferior y paneles aún más compactos */
        @media (max-width: 639px) {
            #tools-sidebar {
                max-height: 68dvh !important;
            }

            #minimap-panel {
                width: calc(100vw - 24px) !important;
                left: 12px !important;
                right: 12px !important;
            }
        }

        /* En dispositivos táctiles no hay hover: se desactiva el desplazamiento de los botones */
        @media (hover: none) {
            .tool-btn:hover {
                transform: none;
            }
        }

        /* Impide el zoom por doble-tap sobre el lienzo en iOS */
        #blueprint-canvas {
            -webkit-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
    </style>
@endpush

@section('content')
    <!-- Leaflet CSS — cargado aquí porque el layout no usa @stack('styles') -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

    @php
        $lat = $acta->establecimiento->latitud;
        $lng = $acta->establecimiento->longitud;
        $hasCoords = !is_null($lat) && !is_null($lng);
        $nombreEstab = e($acta->establecimiento->nombre);
        /* Sin escapar: se serializa con @json para el letrero del portón */
        $nombreEstabRaw = $acta->establecimiento->nombre;
    @endphp

    <script>
        /* Respaldo de roundRect para navegadores que aún no lo traen
           (tablets con iOS 15 o Chrome antiguo): sin él, el croquis no se dibujaría. */
        if (typeof CanvasRenderingContext2D !== 'undefined' && !CanvasRenderingContext2D.prototype.roundRect) {
            CanvasRenderingContext2D.prototype.roundRect = function (x, y, w, h, r) {
                let rad = Array.isArray(r) ? r[0] : (r || 0);
                rad = Math.min(rad, Math.abs(w) / 2, Math.abs(h) / 2);
                this.moveTo(x + rad, y);
                this.arcTo(x + w, y, x + w, y + h, rad);
                this.arcTo(x + w, y + h, x, y + h, rad);
                this.arcTo(x, y + h, x, y, rad);
                this.arcTo(x, y, x + w, y, rad);
                this.closePath();
                return this;
            };
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('tabletEditor', () => {
                let canvas, ctx;
                let isDragging = false;
                let dragTarget = null;
                let offset = { x: 0, y: 0 };
                const GRID = 10;
                const MAX_HISTORY = 50;
                let isRotating = false;
                let rotateTarget = null;
                let rotateCenterX = 0, rotateCenterY = 0;
                let rotateStartAngle = 0;
                let rotateStartRot = 0;

                /* ── Resize-handle drag state ── */
                let isResizing = false;
                let resizeTarget = null;
                let resizeHandle = null;   // 'nw','n','ne','e','se','s','sw','w'
                let resizeStartX = 0, resizeStartY = 0;
                let resizeOrigX = 0, resizeOrigY = 0;
                let resizeOrigW = 0, resizeOrigH = 0;

                /* ═══════════════════════════════════════════════════════════════
                   LENGUAJE GRÁFICO DEL CROQUIS
                   Un único sitio donde viven colores, tintas y grosores. Todos los
                   elementos se dibujan con el mismo criterio: relleno muy claro,
                   contorno de color medio y tinta oscura para textos e iconos.
                   ═══════════════════════════════════════════════════════════════ */
                const THEME = {
                    paper: '#ffffff',
                    grid: '#f1f5f9',        // retícula menor
                    gridMajor: '#e2e8f0',   // retícula mayor (cada 5 casillas)
                    ink: '#0f172a',
                    inkSoft: '#94a3b8',
                    select: '#4f46e5',
                    link: '#60a5fa',        // cableado de red
                    linkInk: '#2563eb',
                };

                /* fill: relleno · stroke: contorno · ink: textos e iconos · accent: detalles */
                const STYLES = {
                    ambiente: {
                        consultorio: { fill: '#f0fdf4', stroke: '#4ade80', ink: '#166534', accent: '#22c55e' },
                        consultorio_fisico: { fill: '#f0fdf4', stroke: '#4ade80', ink: '#166534', accent: '#22c55e' },
                        consultorio_funcional: { fill: '#fffbeb', stroke: '#fbbf24', ink: '#92400e', accent: '#f59e0b', dashed: true },
                        emergencias: { fill: '#fef2f2', stroke: '#f87171', ink: '#991b1b', accent: '#ef4444' },
                        quirofano: { fill: '#eff6ff', stroke: '#60a5fa', ink: '#1e40af', accent: '#3b82f6' },
                        administracion: { fill: '#faf5ff', stroke: '#c084fc', ink: '#6b21a8', accent: '#a855f7' },
                        'baño': { fill: '#ecfeff', stroke: '#22d3ee', ink: '#155e75', accent: '#06b6d4' },
                        _default: { fill: '#f8fafc', stroke: '#cbd5e1', ink: '#334155', accent: '#94a3b8' },
                    },
                    pasillo: { _default: { fill: '#fafafa', stroke: '#d4d4d8', ink: '#52525b', accent: '#a1a1aa' } },
                    hardware: {
                        pozo: { fill: '#ecfdf5', stroke: '#34d399', ink: '#065f46', accent: '#10b981' },
                        punto_red: { fill: '#ecfdf5', stroke: '#34d399', ink: '#065f46', accent: '#10b981' },
                        ups: { fill: '#ecfdf5', stroke: '#34d399', ink: '#065f46', accent: '#10b981' },
                        _default: { fill: '#eff6ff', stroke: '#93c5fd', ink: '#1d4ed8', accent: '#3b82f6' },
                    },
                    /* Las puertas se leen mejor como hueco en el muro: fondo neutro y
                       el propio símbolo aporta el color. El portón va en negro. */
                    puerta: {
                        externa: { fill: '#f8fafc', stroke: '#475569', ink: '#0b0f19', accent: '#1f2937' },
                        _default: { fill: '#f8fafc', stroke: '#94a3b8', ink: '#334155', accent: '#0284c7' },
                    },
                    calle: {
                        avenida: { fill: '#e5e7eb', stroke: '#9ca3af', ink: '#374151', accent: '#fbbf24' },
                        jiron: { fill: '#eceef1', stroke: '#b6bcc5', ink: '#4b5563', accent: '#fbbf24' },
                        pasaje: { fill: '#f3f4f6', stroke: '#cbd5e1', ink: '#6b7280', accent: '#cbd5e1' },
                        _default: { fill: '#eceef1', stroke: '#b6bcc5', ink: '#4b5563', accent: '#fbbf24' },
                    },
                    sistema: {
                        tua: { fill: '#f5f3ff', stroke: '#a78bfa', ink: '#5b21b6', accent: '#7c3aed' },
                        sihce: { fill: '#eff6ff', stroke: '#93c5fd', ink: '#1e40af', accent: '#1d4ed8' },
                        sismed: { fill: '#f0fdfa', stroke: '#5eead4', ink: '#115e59', accent: '#0d9488' },
                        hisminsa: { fill: '#fff7ed', stroke: '#fdba74', ink: '#9a3412', accent: '#c2410c' },
                        sisgalenplus: { fill: '#eff6ff', stroke: '#93c5fd', ink: '#1e3a8a', accent: '#2563eb' },
                        _default: { fill: '#f8fafc', stroke: '#cbd5e1', ink: '#334155', accent: '#64748b' },
                    },
                    _default: { _default: { fill: '#ffffff', stroke: '#cbd5e1', ink: '#334155', accent: '#94a3b8' } },
                };

                /* Nombre corto de cada equipo, para cuando la descripción no cabe bajo el icono */
                const HW_LABEL = {
                    pc: 'CPU', laptop: 'LAPTOP', tablet: 'TABLET', monitor: 'MONITOR',
                    teclado: 'TECLADO', mouse: 'MOUSE', impresora: 'IMPRESORA',
                    ticketera: 'TICKETERA', escaner: 'LECTOR', ups: 'UPS',
                    router: 'ROUTER', ap: 'ACCESS POINT', switch: 'SWITCH',
                    pozo: 'POZO TIERRA', punto_red: 'PUNTO RED', equipo: 'EQUIPO',
                    panel_solar: 'PANEL SOLAR',
                };

                /* Estado del equipo en el inventario: tiñe el equipo para verlo de un vistazo */
                const ESTADO_STYLES = {
                    REGULAR: { fill: '#fffbeb', stroke: '#fcd34d', ink: '#b45309', accent: '#f59e0b' },
                    INOPERATIVO: { fill: '#fef2f2', stroke: '#fca5a5', ink: '#b91c1c', accent: '#ef4444' },
                };

                return {
                    elements: @json($contenido['elementos'] ?? []),
                    connections: @json($contenido['conexiones'] ?? []),
                    tool: 'ambiente',
                    hwType: 'pc',
                    /* Catálogo del panel de Equipamiento TI */
                    equiposComputo: [
                        { tipo: 'pc', label: 'CPU', icon: 'cpu' },
                        { tipo: 'laptop', label: 'Laptop', icon: 'laptop' },
                        { tipo: 'monitor', label: 'Monitor', icon: 'monitor' },
                        { tipo: 'teclado', label: 'Teclado', icon: 'keyboard' },
                        { tipo: 'mouse', label: 'Mouse', icon: 'mouse' },
                        { tipo: 'impresora', label: 'Impresora', icon: 'printer' },
                        { tipo: 'ticketera', label: 'Ticketera', icon: 'receipt' },
                        { tipo: 'escaner', label: 'Lector DNIe', icon: 'scan-line' },
                        { tipo: 'tablet', label: 'Tablet', icon: 'tablet' },
                    ],
                    equiposRed: [
                        { tipo: 'router', label: 'Router', icon: 'router' },
                        { tipo: 'ap', label: 'Access Point', icon: 'rss' },
                        { tipo: 'switch', label: 'Switch', icon: 'layers' },
                        { tipo: 'punto_red', label: 'Punto Red', icon: 'share-2' },
                        { tipo: 'pozo', label: 'Pozo Tierra', icon: 'anchor' },
                        { tipo: 'ups', label: 'UPS', icon: 'battery-charging' },
                        { tipo: 'panel_solar', label: 'Panel Solar', icon: 'sun' },
                    ],
                    get hwLabelActual() {
                        const todos = [...this.equiposComputo, ...this.equiposRed];
                        const eq = todos.find(e => e.tipo === this.hwType);
                        return eq ? eq.label : 'Equipo';
                    },
                    layers: { furniture: true, network: true, power: true, calles: false },
                    tileCache: {},
                    tileOpacity: 0.5,
                    tileZoom: 21.5,
                    mapOffsetX: @json($contenido['mapOffsetX'] ?? 0),
                    mapOffsetY: @json($contenido['mapOffsetY'] ?? 0),
                    /* Punto del plano donde se ancla el mapa base. Se fija en el primer
                       dibujo y se guarda, para que el fondo no baile al cambiar de
                       tamaño el lienzo (pantalla completa, otro dispositivo…). */
                    mapAnchorX: @json($contenido['mapAnchorX'] ?? null),
                    mapAnchorY: @json($contenido['mapAnchorY'] ?? null),
                    geoLat: {{ $hasCoords ? $lat : 'null' }},
                    geoLng: {{ $hasCoords ? $lng : 'null' }},
                    /* Nombre del establecimiento del acta: va en el letrero del portón */
                    estabNombre: @json($nombreEstabRaw ?? ''),
                    name: '',
                    roomSubtype: 'consultorio_fisico',
                    doorSubtype: 'interna',
                    calleSubtype: 'jiron',
                    sistemaType: 'tua',
                    attrs: { wifi: false, light: false, red: 0 },
                    selectedId: null,
                    hoveredEl: null,
                    mouseX: 0, mouseY: 0,
                    isConnecting: false,
                    connectionStart: null,
                    sidebarOpen: true,
                    panelVisible: true,
                    /* ─ Responsive / táctil ─ */
                    isMobile: false,        // < 1024px  (tablet vertical y móvil)
                    isCompact: false,       // < 640px   (móvil)
                    isTouch: false,         // dispositivo sin mouse
                    showFilters: false,     // popover de "Filtros de vista" en pantallas angostas
                    showActions: false,     // popover de acciones (guardar / exportar) en móvil
                    panMode: false,         // modo mano: arrastrar el lienzo con un dedo/clic
                    _pinchDist: 0,
                    _pinchCX: 0,
                    _pinchCY: 0,
                    _isPinching: false,
                    _rafId: null,           // repintado agendado (un frame)
                    _hintVisible: false,    // pista de gestos táctiles
                    isSaving: false,
                    isFullscreen: false,
                    history: [],
                    future: [],
                    canvasZoom: 1.0,
                    panX: 0,
                    panY: 0,
                    isPanning: false,
                    canvasOpacity: 1.0,
                    /* ─ Pisos (multi-floor) ─ */
                    currentPiso: 1,
                    totalPisos: @json($contenido['totalPisos'] ?? 1),
                    showGhostFloor: true,
                    /* ─ Sidebar pointer-drag state ─ */
                    _sbDrag: null,           // { type, subtype, startX, startY, isDragging }
                    isDraggingMap: false,
                    _phantomVisible: false,
                    _phantomX: 0,
                    _phantomY: 0,
                    _phantomLabel: '',

                    /* ─ Colaboración en Tiempo Real ─ */
                    colaboradores: [],            // [{ user_id, user_name, color, cursor_x, cursor_y, elements, connections }]
                    _syncInterval: null,
                    _cursorSendThrottle: null,
                    _pendingCursorX: 0,
                    _pendingCursorY: 0,
                    _colabActaId: {{ $acta->id }},
                    /* Colaboración en tiempo real: sondeo cada 900ms contra el servidor,
                       que guarda y devuelve la posición del cursor, elementos, conexiones
                       y eliminados de cada usuario activo en esta acta. */
                    _syncUrl: '{{ route('usuario.monitoreo.infraestructura-2d.croquis-sync', $acta->id) }}',
                    _leaveUrl: '{{ route('usuario.monitoreo.infraestructura-2d.croquis-leave', $acta->id) }}',
                    _csrfToken: '{{ csrf_token() }}',
                    deletedIds: [],              // IDs borrados localmente para sincronizar
                    _lastColabHash: '',          // Hash del estado remoto para detect cambios
                    _toastMsg: '',               // Mensaje del toast de colaboración
                    _toastVisible: false,        // Visibilidad del toast
                    _toastTimer: null,           // Timer para auto-ocultar el toast

                    /* ─ Datos de módulos (sincronización) ─ */
                    modulosData: @json($modulosData ?? []),  // [{ slug, label, equipos[], utiliza_sihce, tipo_conectividad }]

                    /* ─ Pozo a tierra: dato del acta completa, no de un consultorio en particular ─ */
                    pozoTierra: @json($acta->pozo_tierra ?? 'NO'),
                    pozoTierraCantidad: {{ (int) ($acta->pozo_tierra_cantidad ?? 0) }},
                    pozoTierraOperativos: {{ (int) ($acta->pozo_tierra_operativos ?? 0) }},
                    pozoTierraInoperativos: {{ (int) ($acta->pozo_tierra_inoperativos ?? 0) }},

                    /* ─ Panel solar: igual que el pozo a tierra, dato del acta completa ─ */
                    panelSolar: @json($acta->panel_solar ?? 'NO'),
                    panelSolarCantidad: {{ (int) ($acta->panel_solar_cantidad ?? 0) }},
                    panelSolarOperativos: {{ (int) ($acta->panel_solar_operativos ?? 0) }},
                    panelSolarInoperativos: {{ (int) ($acta->panel_solar_inoperativos ?? 0) }},

                    /* ─── Lifecycle ─── */
                    init() {
                        this._applyBreakpoint(true);
                        this._setupDialogs();

                        this.$nextTick(() => {
                            canvas = document.getElementById('blueprint-canvas');
                            if (!canvas) { console.error('Canvas not found'); return; }
                            ctx = canvas.getContext('2d');
                            this.resizeCanvas();
                            this.draw();
                            this._refreshIcons();
                            window.addEventListener('resize', () => { this._applyBreakpoint(); this.resizeCanvas(); });
                            /* Al rotar el dispositivo el viewport tarda en estabilizarse */
                            window.addEventListener('orientationchange', () => {
                                setTimeout(() => { this._applyBreakpoint(); this.resizeCanvas(); this.autoFit(); }, 250);
                            });

                            /* Entrar o salir de pantalla completa cambia el tamaño del lienzo.
                               También cubre la salida con Esc, que no pasa por el botón. */
                            document.addEventListener('fullscreenchange', () => {
                                this.isFullscreen = !!document.fullscreenElement;
                                setTimeout(() => { this._applyBreakpoint(); this.resizeCanvas(); }, 120);
                            });

                            /* Global pointer listeners for sidebar drag */
                            window.addEventListener('pointermove', (e) => this._onWindowPointerMove(e));
                            window.addEventListener('pointerup', (e) => this._onWindowPointerUp(e));

                            /* Zoom con rueda de mouse */
                            canvas.addEventListener('wheel', (e) => {
                                e.preventDefault();
                                const delta = -e.deltaY;
                                const factor = delta > 0 ? 1.1 : 0.9;
                                const rect = canvas.getBoundingClientRect();
                                const mx = e.clientX - rect.left;
                                const my = e.clientY - rect.top;
                                const pBefore = this._screenToCanvas(mx, my);
                                this.canvasZoom = Math.max(0.05, Math.min(5.0, this.canvasZoom * factor));
                                this.panX = mx - pBefore.x * this.canvasZoom;
                                this.panY = my - pBefore.y * this.canvasZoom;
                                this.draw();
                            }, { passive: false });

                            /* Colaboración: iniciar polling */
                            this._startColabSync();

                            /* Auto-ajuste inicial para encuadrar el diseño */
                            setTimeout(() => this.autoFit(), 120);

                            /* Pista de gestos, solo en pantallas táctiles */
                            if (this.isTouch) {
                                this._hintVisible = true;
                                setTimeout(() => { this._hintVisible = false; }, 6000);
                            }
                        });

                        /* Notificar al servidor cuando el usuario cierra/navega */
                        window.addEventListener('beforeunload', () => this._leaveColab());

                        this.$watch('sidebarOpen', () => {
                            this.$nextTick(() => setTimeout(() => { this.resizeCanvas(); this._refreshIcons(); }, 350));
                        });
                        this.$watch('tool', () => this.$nextTick(() => this._refreshIcons()));
                        this.$watch('hwType', () => this.$nextTick(() => this._refreshIcons()));
                        this.$watch('selectedId', () => this.$nextTick(() => this._refreshIcons()));
                    },

                    _refreshIcons() {
                        if (window.lucide) window.lucide.createIcons();
                    },

                    /* ─── Diálogos dentro del editor ───
                       En pantalla completa el navegador solo pinta el elemento a pantalla
                       completa y sus descendientes: un modal colgado de <body> queda
                       invisible y obliga a salir para responderlo. Se fija el destino de
                       todos los diálogos al propio editor. */
                    _setupDialogs() {
                        if (!window.Swal || window.Swal.__croquisDialogPatch) return;

                        const original = window.Swal.fire.bind(window.Swal);
                        const destino = () =>
                            document.fullscreenElement ||
                            document.getElementById('tablet-editor-container') ||
                            undefined;

                        window.Swal.fire = (...args) => {
                            if (args.length && typeof args[0] === 'object' && args[0] !== null) {
                                /* Si quien llama ya indicó un destino, se respeta */
                                return original({ target: destino(), ...args[0] });
                            }
                            const [title, text, icon] = args;   // firma corta: (título, texto, icono)
                            return original({ target: destino(), title, text, icon });
                        };
                        window.Swal.__croquisDialogPatch = true;
                    },

                    /* ─── Breakpoints (PC / laptop / tablet / móvil) ─── */
                    _applyBreakpoint(initial = false) {
                        const w = window.innerWidth;
                        const wasMobile = this.isMobile;
                        this.isMobile = w < 1024;
                        this.isCompact = w < 640;
                        this.isTouch = window.matchMedia('(hover: none), (pointer: coarse)').matches;

                        if (initial) {
                            /* En tablet/móvil el panel arranca oculto para no tapar el croquis */
                            this.sidebarOpen = !this.isMobile;
                        } else if (this.isMobile !== wasMobile) {
                            this.sidebarOpen = !this.isMobile;
                            this.showFilters = false;
                            this.showActions = false;
                        }
                    },

                    /* Alterna el panel de herramientas (hoja inferior en móvil) */
                    toggleSidebar() {
                        this.sidebarOpen = !this.sidebarOpen;
                        if (this.sidebarOpen) { this.showFilters = false; this.showActions = false; }
                    },

                    /* Tras colocar un elemento desde la hoja inferior conviene cerrarla */
                    _autoCloseSheet() {
                        if (this.isMobile) this.sidebarOpen = false;
                    },

                    /* ─── Fullscreen Toggle ─── */
                    toggleFullscreen() {
                        const el = document.getElementById('tablet-editor-container');
                        if (!el) return;

                        if (!document.fullscreenElement) {
                            if (el.requestFullscreen) {
                                el.requestFullscreen();
                            } else if (el.webkitRequestFullscreen) {
                                el.webkitRequestFullscreen();
                            } else if (el.msRequestFullscreen) {
                                el.msRequestFullscreen();
                            }
                            this.isFullscreen = true;
                        } else {
                            if (document.exitFullscreen) {
                                document.exitFullscreen();
                            } else if (document.webkitExitFullscreen) {
                                document.webkitExitFullscreen();
                            } else if (document.msExitFullscreen) {
                                document.msExitFullscreen();
                            }
                            this.isFullscreen = false;
                        }
                    },

                    /* ─── Canvas Resize (HiDPI-safe) ─── */
                    resizeCanvas() {
                        const container = document.getElementById('canvas-container');
                        if (!container || !canvas) return;
                        const dpr = window.devicePixelRatio || 1;
                        const w = container.clientWidth;
                        const h = container.clientHeight;
                        canvas.width = Math.round(w * dpr);
                        canvas.height = Math.round(h * dpr);
                        canvas.style.width = `${w}px`;
                        canvas.style.height = `${h}px`;
                        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                        ctx.lineCap = 'round';
                        ctx.lineJoin = 'round';
                        this.draw();
                    },

                    /* ─── Logical canvas size (CSS pixels) ─── */
                    get logicalW() {
                        const val = canvas ? parseFloat(canvas.style.width) : 800;
                        return isNaN(val) || val <= 0 ? 800 : val;
                    },
                    get logicalH() {
                        const val = canvas ? parseFloat(canvas.style.height) : 600;
                        return isNaN(val) || val <= 0 ? 600 : val;
                    },

                    /* ─── Selected Element Data Binding ─── */
                    get selectedEl() { return this.elements.find(e => e.id === this.selectedId) || null; },
                    get selectedElName() { return this.selectedEl ? this.selectedEl.name : ''; },
                    set selectedElName(val) {
                        if (this.selectedEl) {
                            this.selectedEl.name = val;
                            this.draw();
                        }
                    },

                    /* ─── History (Undo/Redo) ─── */
                    _snapshot() {
                        const snap = JSON.stringify({ elements: this.elements, connections: this.connections });
                        this.history.push(snap);
                        if (this.history.length > MAX_HISTORY) this.history.shift();
                        this.future = [];
                    },
                    undo() {
                        if (!this.history.length) return;
                        this.future.push(JSON.stringify({ elements: this.elements, connections: this.connections }));
                        const prev = JSON.parse(this.history.pop());
                        const now = Date.now();

                        /* Detectar elementos borrados por el undo y notificarlos */
                        this.elements.forEach(cEl => {
                            if (!prev.elements.find(e => e.id === cEl.id)) {
                                if (!this.deletedIds.includes(cEl.id)) this.deletedIds.push(cEl.id);
                            }
                        });

                        /* Actualizar timestamp solo de los elementos que cambian */
                        prev.elements = prev.elements.map(pEl => {
                            const cEl = this.elements.find(e => e.id === pEl.id);
                            if (!cEl) { pEl._ts = now; return pEl; }
                            const attrs = ['x', 'y', 'w', 'h', 'rot', 'name', 'type', 'subtype', 'piso'];
                            let changed = attrs.some(attr => pEl[attr] !== cEl[attr]);
                            pEl._ts = changed ? now : cEl._ts; /* mantener ts actual si no hubo cambio */
                            return pEl;
                        });

                        this.elements = prev.elements;
                        this.connections = prev.connections;
                        this.selectedId = null;
                        this.draw();
                    },
                    redo() {
                        if (!this.future.length) return;
                        this.history.push(JSON.stringify({ elements: this.elements, connections: this.connections }));
                        const next = JSON.parse(this.future.pop());
                        const now = Date.now();

                        /* Detectar elementos borrados por el redo y notificarlos */
                        this.elements.forEach(cEl => {
                            if (!next.elements.find(e => e.id === cEl.id)) {
                                if (!this.deletedIds.includes(cEl.id)) this.deletedIds.push(cEl.id);
                            }
                        });

                        /* Actualizar timestamp solo de los elementos que cambian */
                        next.elements = next.elements.map(nEl => {
                            const cEl = this.elements.find(e => e.id === nEl.id);
                            if (!cEl) { nEl._ts = now; return nEl; }
                            const attrs = ['x', 'y', 'w', 'h', 'rot', 'name', 'type', 'subtype', 'piso'];
                            let changed = attrs.some(attr => nEl[attr] !== cEl[attr]);
                            nEl._ts = changed ? now : cEl._ts;
                            return nEl;
                        });

                        this.elements = next.elements;
                        this.connections = next.connections;
                        this.selectedId = null;
                        this.draw();
                    },

                    /* ─── Hover ─── */
                    checkHover(x, y) {
                        this.hoveredEl = this.elements.find(el => this._isPointInElement(el, x, y)) || null;
                    },

                    _isPointInElement(el, px, py, padding = null) {
                        /* Margen de acierto constante en pantalla y más amplio con el dedo */
                        if (padding === null) padding = (this.isTouch ? 10 : 4) / (this.canvasZoom || 1);
                        const cx = el.x + el.w / 2;
                        const cy = el.y + el.h / 2;
                        const rot = (el.rot || 0) * Math.PI / 180;
                        const cosR = Math.cos(rot), sinR = Math.sin(rot);

                        /* Transform world point to element-local space */
                        const dx = px - cx;
                        const dy = py - cy;
                        const lx = dx * cosR + dy * sinR;
                        const ly = -dx * sinR + dy * cosR;

                        return Math.abs(lx) <= (el.w / 2 + padding) && Math.abs(ly) <= (el.h / 2 + padding);
                    },

                    /* ─── Piso management ─── */
                    pisoRange() {
                        const arr = [];
                        for (let i = 1; i <= this.totalPisos; i++) arr.push(i);
                        return arr;
                    },
                    addPiso() {
                        this._snapshot();
                        this.totalPisos++;
                        this.currentPiso = this.totalPisos;
                        this.selectedId = null;
                        this.draw();
                    },
                    removePiso() {
                        if (this.totalPisos <= 1) return;
                        const pisoToRemove = this.currentPiso;
                        const hasElements = this.elements.some(e => (e.piso || 1) === pisoToRemove);
                        if (hasElements) {
                            Swal.fire({
                                target: document.getElementById('tablet-editor-container'),
                                title: '¿Eliminar este piso?',
                                text: `El Piso ${pisoToRemove} tiene elementos. Se eliminarán también.`,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#ef4444',
                                cancelButtonColor: '#64748b',
                                confirmButtonText: 'Sí, eliminar',
                                cancelButtonText: 'Cancelar'
                            }).then(r => {
                                if (!r.isConfirmed) return;
                                this._doRemovePiso(pisoToRemove);
                            });
                        } else {
                            this._doRemovePiso(pisoToRemove);
                        }
                    },
                    _doRemovePiso(pisoToRemove) {
                        this._snapshot();
                        /* Remove elements of this piso */
                        this.elements = this.elements.filter(e => (e.piso || 1) !== pisoToRemove);
                        this.connections = this.connections.filter(c => {
                            const from = this.elements.find(e => e.id === c.from);
                            const to = this.elements.find(e => e.id === c.to);
                            return from && to;
                        });
                        /* Re-number pisos above the removed one */
                        this.elements.forEach(e => {
                            if ((e.piso || 1) > pisoToRemove) e.piso = (e.piso || 1) - 1;
                        });
                        this.totalPisos--;
                        this.currentPiso = Math.min(this.currentPiso, this.totalPisos);
                        this.selectedId = null;
                        this.draw();
                    },
                    goToPiso(n) {
                        this.currentPiso = n;
                        this.selectedId = null;
                        this.draw();
                    },
                    moveSelectedToPiso(n) {
                        const el = this.elements.find(e => e.id === this.selectedId);
                        if (!el) return;
                        this._snapshot();
                        el.piso = n;
                        el._ts = Date.now();
                        /* Los equipos del ambiente cambian de piso con él */
                        this._childrenOf(el.id).forEach(ch => { ch.piso = n; ch._ts = Date.now(); });
                        this.draw();
                    },
                    /* Count elements per piso */
                    countInPiso(n) { return this.elements.filter(e => (e.piso || 1) === n).length; },

                    /* ─── Add Element ─── */
                    addElement(type = this.tool, dropX = null, dropY = null) {
                        this._snapshot();
                        const lw = this.logicalW || 800;
                        const lh = this.logicalH || 600;
                        const isDoorExt = (type === 'puerta' && this.doorSubtype === 'externa');
                        const calleW = this.calleSubtype === 'avenida' ? 500 : (this.calleSubtype === 'jiron' ? 400 : 300);
                        const calleH = this.calleSubtype === 'avenida' ? 80 : (this.calleSubtype === 'jiron' ? 60 : 40);
                        /* El acceso principal es un portón: nace ancho, como en la realidad */
                        const w = type === 'hardware' ? 62 : (type === 'pasillo' ? 300 : (type === 'puerta' ? (isDoorExt ? 180 : 40) : (type === 'calle' ? calleW : (type === 'sistema' ? 80 : 120))));
                        const h = type === 'hardware' ? 58 : (type === 'pasillo' ? 60 : (type === 'puerta' ? (isDoorExt ? 70 : 40) : (type === 'calle' ? calleH : (type === 'sistema' ? 70 : 100))));
                        /* Use drop coords if provided (drag & drop), otherwise random */
                        const rx = dropX !== null
                            ? Math.max(0, Math.round((dropX - w / 2) / GRID) * GRID)
                            : Math.round((Math.random() * (lw - w - 20) + 10) / GRID) * GRID;
                        const ry = dropY !== null
                            ? Math.max(0, Math.round((dropY - h / 2) / GRID) * GRID)
                            : Math.round((Math.random() * (lh - h - 20) + 10) / GRID) * GRID;
                        
                        let parentId = null;
                        if (type === 'hardware' || type === 'sistema') {
                            const p = [...this.elements].reverse().find(e => 
                                e.type === 'ambiente' && e.piso === this.currentPiso &&
                                rx >= e.x && ry >= e.y && rx + w <= e.x + e.w && ry + h <= e.y + e.h
                            );
                            if (p) parentId = p.id;
                        }

                        const newEl = {
                            id: crypto.randomUUID(),
                            type,
                            parentId,
                            piso: this.currentPiso,
                            subtype: type === 'ambiente' ? this.roomSubtype : (type === 'hardware' ? this.hwType : (type === 'puerta' ? this.doorSubtype : (type === 'calle' ? this.calleSubtype : (type === 'sistema' ? this.sistemaType : null)))),
                            /* El equipo estrena el nombre corto de su tipo (CPU, LECTOR…) */
                            name: this.name || (type === 'hardware' ? (HW_LABEL[this.hwType] || this.hwType.toUpperCase()) : (type === 'ambiente' ? (this.roomSubtype?.toUpperCase() || 'AMBIENTE') : (type === 'puerta' ? '' : (type === 'calle' ? (this.calleSubtype === 'avenida' ? 'Av. ' : (this.calleSubtype === 'jiron' ? 'Jr. ' : 'Psj. ')) : (type === 'sistema' ? this.sistemaType.toUpperCase() : type.toUpperCase()))))),
                            x: rx, y: ry, w, h,
                            rot: 0,
                            attrs: { ...this.attrs },
                            _ts: Date.now(),     /* marca de tiempo para merge en colaboración */
                        };
                        this.elements.push(newEl);
                        this.selectedId = newEl.id;
                        this.name = '';
                        this.draw();
                    },

                    /* ─── Zoom helpers ─── */
                    zoomIn() {
                        const lw = this.logicalW, lh = this.logicalH;
                        const pCenter = this._screenToCanvas(lw / 2, lh / 2);
                        this.canvasZoom = Math.min(5.0, Math.round((this.canvasZoom + 0.1) * 10) / 10);
                        this.panX = lw / 2 - pCenter.x * this.canvasZoom;
                        this.panY = lh / 2 - pCenter.y * this.canvasZoom;
                        this.draw();
                    },
                    zoomOut() {
                        const lw = this.logicalW, lh = this.logicalH;
                        const pCenter = this._screenToCanvas(lw / 2, lh / 2);
                        this.canvasZoom = Math.max(0.05, Math.round((this.canvasZoom - 0.1) * 10) / 10);
                        this.panX = lw / 2 - pCenter.x * this.canvasZoom;
                        this.panY = lh / 2 - pCenter.y * this.canvasZoom;
                        this.draw();
                    },
                    resetZoom() { this.canvasZoom = 1.0; this.panX = 0; this.panY = 0; this.draw(); },

                    /* ─── Ajustar el croquis a la pantalla ───
                       Encuadra lo que se está viendo —el piso actual— dentro de la zona
                       del lienzo que no tapan los paneles flotantes. */
                    autoFit() {
                        const visibles = this.elements.filter(e => (e.piso || 1) === this.currentPiso);
                        if (!visibles.length) {
                            this.resetZoom();
                            return;
                        }

                        /* Calculate Bounding Box of items */
                        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                        visibles.forEach(el => {
                            /* El portón lleva el letrero del establecimiento por encima:
                               cuenta para el encuadre o quedaría cortado */
                            let top = el.y;
                            if ((el.type || '').toLowerCase() === 'puerta' &&
                                (el.subtype || '').toLowerCase() === 'externa' && this.estabNombre) {
                                top -= Math.max(6, el.h * 0.1) + Math.max(8, Math.min(el.w * 0.062, 13)) * 2;
                            }
                            minX = Math.min(minX, el.x); minY = Math.min(minY, top);
                            maxX = Math.max(maxX, el.x + el.w); maxY = Math.max(maxY, el.y + el.h);
                        });

                        const bW = Math.max(1, maxX - minX);
                        const bH = Math.max(1, maxY - minY);
                        const lw = this.logicalW;
                        const lh = this.logicalH;

                        /* Márgenes ocupados por los paneles que flotan sobre el lienzo:
                           herramientas a la izquierda, pisos a la derecha y la barra de
                           zoom abajo. Sin descontarlos, el croquis queda debajo de ellos. */
                        const panelAbierto = this.sidebarOpen && this.panelVisible;
                        const izq = (!this.isMobile && panelAbierto) ? 312 : 24;
                        const der = this.isCompact ? 52 : 72;   // panel de pisos
                        const arr = 24;
                        const aba = this.isCompact ? 72 : 88;   // barra de zoom

                        const utilW = Math.max(140, lw - izq - der);
                        const utilH = Math.max(140, lh - arr - aba);

                        /* Escala para que quepa dentro de esa zona, con un respiro del 3%
                           para que el croquis no quede pegado al borde */
                        const newZoom = Math.min(2.0, (utilW / bW) * 0.97, (utilH / bH) * 0.97);
                        this.canvasZoom = isFinite(newZoom) && newZoom > 0 ? Math.max(0.05, newZoom) : 1.0;

                        /* Centrado en la zona útil, no en el lienzo entero */
                        this.panX = izq + utilW / 2 - (minX + bW / 2) * this.canvasZoom;
                        this.panY = arr + utilH / 2 - (minY + bH / 2) * this.canvasZoom;

                        this.draw();
                    },

                    /* ═══════════ Utilidades de dibujo ═══════════ */

                    /* ═══════════ Elementos agrupados ═══════════
                       Un equipo colocado dentro de un ambiente forma parte de él: se mueve,
                       gira y se borra con el ambiente, y no se selecciona por separado. */

                    _childrenOf(id) { return this.elements.filter(e => e.parentId === id); },

                    _isChild(el) {
                        return !!el.parentId && this.elements.some(p => p.id === el.parentId);
                    },

                    /* Gira el elemento y lleva consigo a sus hijos, que rotan alrededor
                       del centro del padre y sobre sí mismos. */
                    _applyRotation(el, nuevaRot) {
                        const prev = el.rot || 0;
                        const next = ((+nuevaRot) % 360 + 360) % 360;
                        el.rot = next;

                        const delta = next - prev;
                        if (!delta) return;

                        const rad = delta * Math.PI / 180;
                        const cos = Math.cos(rad), sin = Math.sin(rad);
                        const cx = el.x + el.w / 2, cy = el.y + el.h / 2;

                        this._childrenOf(el.id).forEach(ch => {
                            const dx = ch.x + ch.w / 2 - cx;
                            const dy = ch.y + ch.h / 2 - cy;
                            ch.x = cx + (dx * cos - dy * sin) - ch.w / 2;
                            ch.y = cy + (dx * sin + dy * cos) - ch.h / 2;
                            ch.rot = ((ch.rot || 0) + delta) % 360;
                            ch._ts = Date.now();
                        });
                    },

                    /* Estilo del elemento según tipo, subtipo y —en equipos— su estado */
                    _style(el) {
                        const type = (el.type || '').toLowerCase();
                        if (type === 'hardware' && el.estado) {
                            const est = ESTADO_STYLES[String(el.estado).toUpperCase()];
                            if (est) return est;
                        }
                        const fam = STYLES[type] || STYLES._default;
                        return fam[(el.subtype || '').toLowerCase()] || fam._default || STYLES._default._default;
                    },

                    /* Convierte píxeles de pantalla a unidades del plano: el trazo
                       conserva el mismo grosor visual con cualquier zoom */
                    _px(v) { return v / (this.canvasZoom || 1); },

                    /* Nivel de detalle: 0 solo siluetas · 1 silueta y rótulo · 2 todo */
                    _lod() {
                        const z = this.canvasZoom || 1;
                        return z < 0.32 ? 0 : (z < 0.62 ? 1 : 2);
                    },

                    /* ¿Un texto de este tamaño sería legible en pantalla? */
                    _readable(sizeInPlan) { return sizeInPlan * (this.canvasZoom || 1) >= 6; },

                    /* Recorta el texto al ancho disponible añadiendo elipsis */
                    _fitText(text, maxW) {
                        if (ctx.measureText(text).width <= maxW) return text;
                        let t = text;
                        while (t.length > 1 && ctx.measureText(t + '…').width > maxW) t = t.slice(0, -1);
                        return t.length > 1 ? t + '…' : '';
                    },

                    /* Rótulo del elemento: una sola línea, centrada y recortada */
                    _drawLabel(el, st, opts = {}) {
                        const size = opts.size || 12;
                        if (!this._readable(size)) return;
                        const text = (opts.text ?? (el.name || el.subtype || el.type || '')).toUpperCase();
                        if (!text) return;

                        ctx.save();
                        ctx.font = `700 ${size}px Inter, system-ui, Arial`;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        const maxW = el.w - 12;
                        const label = this._fitText(text, maxW);
                        if (!label) { ctx.restore(); return; }

                        const cx = el.x + el.w / 2;
                        const y = opts.y ?? (el.y + size * 0.95 + 4);

                        /* Banda clara detrás del texto: lo separa del contenido sin ensuciar */
                        if (opts.plate !== false) {
                            const tw = ctx.measureText(label).width;
                            ctx.fillStyle = 'rgba(255,255,255,0.82)';
                            ctx.beginPath();
                            ctx.roundRect(cx - tw / 2 - 5, y - size * 0.72, tw + 10, size * 1.45, size * 0.5);
                            ctx.fill();
                        }
                        ctx.fillStyle = opts.color || st.ink;
                        ctx.fillText(label, cx, y);
                        ctx.restore();
                    },

                    /* Marco de trabajo para un pictograma: centra y normaliza el trazo.
                       El dibujo se hace siempre dentro de una caja de 24x24 (de -12 a 12). */
                    _icon(cx, cy, size, color, weight, paint) {
                        const s = size / 24;
                        ctx.save();
                        ctx.translate(cx, cy);
                        ctx.scale(s, s);
                        ctx.strokeStyle = color;
                        ctx.fillStyle = color;
                        ctx.lineWidth = (weight || 1.8) / s;
                        ctx.lineCap = 'round';
                        ctx.lineJoin = 'round';
                        paint(ctx);
                        ctx.restore();
                    },

                    /* ─── Redibujado ───
                       draw() agenda un único repintado por frame: durante un arrastre se
                       generan decenas de eventos por segundo y repintar en todos ellos
                       hace que el croquis se sienta lento, sobre todo en tablet y móvil.
                       _flushDraw() fuerza el repintado inmediato cuando hace falta leer
                       el lienzo (exportar PNG, guardar). */
                    draw() {
                        if (this._rafId) return;
                        this._rafId = requestAnimationFrame(() => {
                            this._rafId = null;
                            this._render();
                        });
                    },
                    _flushDraw() {
                        if (this._rafId) { cancelAnimationFrame(this._rafId); this._rafId = null; }
                        this._render();
                    },

                    /* ─── Main Draw ─── */
                    _render() {
                        if (!ctx || !canvas) return;
                        const lw = this.logicalW;
                        const lh = this.logicalH;

                        /* Clear the full physical canvas (DPR-scaled) so no artifacts remain */
                        ctx.save();
                        ctx.setTransform(1, 0, 0, 1, 0, 0); /* identity — ignore DPR and camera */
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, canvas.width, canvas.height);
                        ctx.restore();

                        /* Apply camera transform ON TOP of the existing DPR transform
                           resizeCanvas() sets: ctx.setTransform(dpr, 0, 0, dpr, 0, 0)
                           We add pan + zoom without resetting DPR: */
                        ctx.save();
                        ctx.translate(this.panX, this.panY);
                        ctx.scale(this.canvasZoom, this.canvasZoom);
                        ctx.globalAlpha = this.canvasOpacity;

                        /* Capa de mapa base (tiles OSM) */
                        if (this.layers.calles && this.geoLat !== null) this.drawStreetBase(lw, lh);

                        /* ── Retícula en dos niveles: la menor se retira al alejarse,
                              de modo que el plano nunca se ve emborronado ── */
                        const z = this.canvasZoom || 1;
                        const step = 40;
                        const xMin = Math.floor((-this.panX / z) / step) * step;
                        const yMin = Math.floor((-this.panY / z) / step) * step;
                        const xMax = xMin + lw / z + step;
                        const yMax = yMin + lh / z + step;

                        const gridPass = (mult, color, width) => {
                            const s = step * mult;
                            ctx.strokeStyle = color;
                            ctx.lineWidth = this._px(width);
                            ctx.beginPath();
                            for (let x = Math.floor(xMin / s) * s; x <= xMax; x += s) {
                                ctx.moveTo(x, yMin); ctx.lineTo(x, yMax);
                            }
                            for (let y = Math.floor(yMin / s) * s; y <= yMax; y += s) {
                                ctx.moveTo(xMin, y); ctx.lineTo(xMax, y);
                            }
                            ctx.stroke();
                        };
                        if (z >= 0.45) gridPass(1, THEME.grid, 1);
                        gridPass(5, THEME.gridMajor, 1);

                        if (this.layers.network) this.drawConnections();

                        /* ── Silueta del piso contiguo: solo el contorno, para que sirva
                              de referencia sin competir con el piso actual ── */
                        if (this.showGhostFloor && this.totalPisos > 1) {
                            const ghostPiso = this.currentPiso > 1 ? this.currentPiso - 1 : this.currentPiso + 1;
                            const ghostEls = this.elements.filter(e => (e.piso || 1) === ghostPiso);
                            if (ghostEls.length > 0) {
                                ctx.save();
                                ctx.strokeStyle = '#cbd5e1';
                                ctx.lineWidth = this._px(1);
                                ctx.setLineDash([this._px(5), this._px(4)]);
                                ghostEls.forEach(el => {
                                    this.drawRoundedRect(el.x, el.y, el.w, el.h, 6);
                                    ctx.stroke();
                                });
                                ctx.setLineDash([]);
                                ctx.restore();
                            }
                        }

                        /* ── Marca de agua del piso ── */
                        ctx.save();
                        ctx.globalAlpha = 0.05;
                        const waterFS = Math.min(lw, lh) * 0.26 / z;
                        ctx.font = `800 ${waterFS}px Inter, system-ui, Arial`;
                        ctx.fillStyle = THEME.select;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(`P${this.currentPiso}`, (lw / 2 - this.panX) / z, (lh / 2 - this.panY) / z);
                        ctx.restore();

                        /* ── Only draw elements of the current floor ── */
                        const floorEls = this.elements.filter(e => (e.piso || 1) == this.currentPiso);

                        /* Ambientes que ya contienen equipos: en ellos el mobiliario
                           esquemático solo estorbaría al equipamiento dibujado dentro */
                        const conContenido = new Set();
                        this.elements.forEach(e => { if (e.parentId) conContenido.add(e.parentId); });

                        floorEls.forEach(el => {
                            ctx.save();
                            ctx.translate(el.x + el.w / 2, el.y + el.h / 2);
                            const rotRad = (el.rot || 0) * Math.PI / 180;
                            ctx.rotate(rotRad);
                            ctx.translate(-(el.x + el.w / 2), -(el.y + el.h / 2));

                            const type = (el.type || '').toLowerCase();
                            const subtype = (el.subtype || '').toLowerCase();
                            const st = this._style(el);
                            const isSel = el.id === this.selectedId;
                            const lod = this._lod();

                            /* ── Cuerpo: relleno claro y contorno fino y nítido.
                                  Sin sombras: a cualquier zoom el plano se ve limpio.
                                  Las vías no llevan marco: la propia calzada es la forma ── */
                            const radius = type === 'hardware' ? Math.min(14, Math.min(el.w, el.h) * 0.28) : 8;
                            if (type !== 'calle') {
                                ctx.fillStyle = st.fill;
                                ctx.strokeStyle = isSel ? THEME.select : st.stroke;
                                ctx.lineWidth = this._px(isSel ? 2.4 : 1.4);
                                if (st.dashed && !isSel) ctx.setLineDash([this._px(7), this._px(5)]);

                                this.drawRoundedRect(el.x, el.y, el.w, el.h, radius);
                                ctx.fill();
                                ctx.stroke();
                                ctx.setLineDash([]);
                            }

                            /* Halo suave de selección, por fuera del contorno */
                            if (isSel) {
                                ctx.save();
                                ctx.strokeStyle = 'rgba(79,70,229,0.22)';
                                ctx.lineWidth = this._px(6);
                                this.drawRoundedRect(el.x, el.y, el.w, el.h, radius);
                                ctx.stroke();
                                ctx.restore();
                            }

                            /* ── Contenido: se retira cuando la vista está muy alejada ── */
                            if (type === 'puerta') this.drawDoorSymbol(el);
                            else if (type === 'calle') this.drawCalleSymbol(el);
                            else if (lod >= 1) {
                                if (type === 'hardware') this.drawHardwareSymbol(el);
                                else if (type === 'sistema') this.drawSistemaSymbol(el);
                                else {
                                    if (this.layers.furniture && lod >= 2 && !conContenido.has(el.id)) this.drawFurnitureIcons(el);
                                    if (this.layers.network || this.layers.power) this.drawServiceIcons(el);
                                }
                            }

                            /* Distintivo FÍSICO / FUNCIONAL. No aplica a las salas traídas de
                               los módulos: ahí el tipo lo define el propio servicio. */
                            if (lod >= 2 && type === 'ambiente' && !el._slug &&
                                (subtype === 'consultorio_fisico' || subtype === 'consultorio_funcional' || subtype === 'consultorio')) {
                                this.drawConsultorioBadge(el, subtype);
                            }

                            /* ── Rótulo ── */
                            if (type === 'calle') {
                                this._drawCalleLabel(el);
                            } else if (type === 'hardware') {
                                /* El nombre del equipo se dibuja bajo su icono, dentro de la caja */
                            } else if (type === 'sistema') {
                                /* el nombre del sistema ya va dentro del símbolo */
                            } else if (type === 'puerta') {
                                if (el.name) this._drawLabel(el, st, { size: 10, y: el.y - 8, plate: false });
                            } else {
                                /* El nombre del ambiente es la referencia principal del plano */
                                this._drawLabel(el, st, { size: 13 });
                            }
                            ctx.restore();

                            /* ── Rotation handle (drawn outside saved transform) ── */
                            if (el.id === this.selectedId && (el.piso || 1) === this.currentPiso) {
                                const cx2 = el.x + el.w / 2;
                                const cy2 = el.y + el.h / 2;
                                const r = el.rot || 0;
                                const rRad2 = r * Math.PI / 180;
                                /* El tirador se mantiene a 34px visuales del borde superior */
                                const handleDist = el.h / 2 + this._px(34);
                                const hx = cx2 - Math.sin(rRad2) * handleDist;
                                const hy = cy2 - Math.cos(rRad2) * handleDist;

                                /* Tallo */
                                ctx.save();
                                ctx.strokeStyle = THEME.select;
                                ctx.lineWidth = this._px(1.2);
                                ctx.setLineDash([this._px(4), this._px(3)]);
                                ctx.beginPath();
                                ctx.moveTo(cx2 - Math.sin(rRad2) * (el.h / 2), cy2 - Math.cos(rRad2) * (el.h / 2));
                                ctx.lineTo(hx, hy);
                                ctx.stroke();
                                ctx.setLineDash([]);
                                ctx.restore();

                                /* Disco del tirador */
                                const hR = this._px(this.isTouch ? 15 : 12);
                                ctx.save();
                                ctx.beginPath();
                                ctx.arc(hx, hy, hR, 0, Math.PI * 2);
                                ctx.fillStyle = THEME.select;
                                ctx.fill();
                                ctx.strokeStyle = '#ffffff';
                                ctx.lineWidth = this._px(2);
                                ctx.stroke();
                                ctx.restore();

                                /* Flecha de giro */
                                this._icon(hx, hy, hR * 1.5, '#ffffff', 2.4, (c) => {
                                    c.beginPath();
                                    c.arc(0, 0, 7, -Math.PI * 0.85, Math.PI * 0.35);
                                    c.stroke();
                                    const ax = 7 * Math.cos(Math.PI * 0.35), ay = 7 * Math.sin(Math.PI * 0.35);
                                    c.beginPath();
                                    c.moveTo(ax - 4.5, ay - 1);
                                    c.lineTo(ax, ay + 3.5);
                                    c.lineTo(ax + 4, ay - 1.5);
                                    c.stroke();
                                });

                                /* Store handle position for hit-testing */
                                el._hx = hx; el._hy = hy;
                            }
                        });

                        /* Draw resize handles LAST (on top, outside element transforms) */
                        if (this.selectedId) {
                            const sel = this.elements.find(e => e.id === this.selectedId && (e.piso || 1) === this.currentPiso);
                            if (sel) this.drawResizeHandles(sel);
                        }

                        /* End zoom+opacity transform */
                        ctx.restore();

                        /* ── Leyenda de estados (en coordenadas de pantalla) ── */
                        this._drawEstadoLegend(floorEls, lw, lh);

                        /* ── Cursores de colaboradores (fuera del zoom transform) ── */
                        this._drawRemoteCursors();
                    },


                    /* ── Leyenda del estado de los equipos ──
                       Solo aparece cuando hay equipos que no están operativos: si todo
                       está bien no hay nada que explicar y el plano queda despejado. */
                    _drawEstadoLegend(floorEls, lw, lh) {
                        const hay = { REGULAR: 0, INOPERATIVO: 0, OPERATIVO: 0 };
                        floorEls.forEach(e => {
                            if ((e.type || '').toLowerCase() !== 'hardware') return;
                            const est = String(e.estado || '').toUpperCase();
                            if (hay[est] !== undefined) hay[est] += (parseInt(e.cantidad, 10) || 1);
                        });
                        if (!hay.REGULAR && !hay.INOPERATIVO) return;

                        const filas = [
                            ['OPERATIVO', '#3b82f6', hay.OPERATIVO],
                            ['REGULAR', '#f59e0b', hay.REGULAR],
                            ['INOPERATIVO', '#ef4444', hay.INOPERATIVO],
                        ].filter(f => f[2] > 0);

                        const FS = 9, PAD = 9, LH = 15, DOT = 4;
                        ctx.save();
                        ctx.setTransform(window.devicePixelRatio || 1, 0, 0, window.devicePixelRatio || 1, 0, 0);
                        ctx.font = `700 ${FS}px Inter, system-ui, Arial`;
                        const textW = Math.max(...filas.map(f => ctx.measureText(`${f[0]}  ${f[2]}`).width));
                        const bw = textW + PAD * 2 + DOT * 2 + 8;
                        const bh = PAD * 2 + filas.length * LH + 12;
                        const bx = 12, by = lh - bh - 12;

                        ctx.fillStyle = 'rgba(255,255,255,0.95)';
                        ctx.strokeStyle = 'rgba(148,163,184,0.5)';
                        ctx.lineWidth = 1;
                        ctx.beginPath(); ctx.roundRect(bx, by, bw, bh, 10);
                        ctx.fill(); ctx.stroke();

                        ctx.fillStyle = '#94a3b8';
                        ctx.font = `700 ${FS - 1}px Inter, system-ui, Arial`;
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'middle';
                        ctx.fillText('ESTADO DE EQUIPOS', bx + PAD, by + PAD + 3);

                        filas.forEach((f, i) => {
                            const y = by + PAD + 18 + i * LH;
                            ctx.fillStyle = f[1];
                            ctx.beginPath(); ctx.arc(bx + PAD + DOT, y, DOT, 0, Math.PI * 2); ctx.fill();
                            ctx.fillStyle = '#334155';
                            ctx.font = `600 ${FS}px Inter, system-ui, Arial`;
                            ctx.fillText(f[0], bx + PAD + DOT * 2 + 6, y);
                            ctx.fillStyle = '#94a3b8';
                            ctx.textAlign = 'right';
                            ctx.fillText(String(f[2]), bx + bw - PAD, y);
                            ctx.textAlign = 'left';
                        });
                        ctx.restore();
                    },

                    /* ── 8 resize handles around selected element ── */
                    _resizeHandlePositions(el) {
                        const { x, y, w, h } = el;
                        return {
                            nw: { hx: x, hy: y },
                            n: { hx: x + w / 2, hy: y },
                            ne: { hx: x + w, hy: y },
                            e: { hx: x + w, hy: y + h / 2 },
                            se: { hx: x + w, hy: y + h },
                            s: { hx: x + w / 2, hy: y + h },
                            sw: { hx: x, hy: y + h },
                            w: { hx: x, hy: y + h / 2 },
                        };
                    },

                    /* Radio del tirador en coordenadas del croquis: tamaño constante en pantalla,
                       más generoso cuando se maneja con el dedo */
                    _handleRadius() {
                        const base = this.isTouch ? 13 : 7;
                        return base / (this.canvasZoom || 1);
                    },

                    drawResizeHandles(el) {
                        const ecx = el.x + el.w / 2;
                        const ecy = el.y + el.h / 2;
                        const rot = (el.rot || 0) * Math.PI / 180;
                        const cosR = Math.cos(rot), sinR = Math.sin(rot);
                        const RH = this._handleRadius();

                        const handles = this._resizeHandlePositions(el);
                        Object.values(handles).forEach(({ hx, hy }) => {
                            /* Rotate handle position around the element center */
                            const dx = hx - ecx, dy = hy - ecy;
                            const rhx = ecx + dx * cosR - dy * sinR;
                            const rhy = ecy + dx * sinR + dy * cosR;

                            ctx.save();
                            ctx.translate(rhx, rhy);
                            ctx.rotate(rot);
                            ctx.fillStyle = 'white';
                            ctx.strokeStyle = '#4f46e5';
                            ctx.lineWidth = 2 / (this.canvasZoom || 1);
                            ctx.shadowBlur = 4;
                            ctx.shadowColor = 'rgba(79,70,229,0.3)';
                            ctx.beginPath();
                            ctx.rect(-RH, -RH, RH * 2, RH * 2);
                            ctx.fill();
                            ctx.stroke();
                            ctx.shadowBlur = 0;
                            ctx.restore();
                        });
                    },

                    _getResizeHandle(el, x, y) {
                        /* Inverse-rotate the mouse point into element-local space */
                        const ecx = el.x + el.w / 2;
                        const ecy = el.y + el.h / 2;
                        const rot = -(el.rot || 0) * Math.PI / 180;   // inverse
                        const cosR = Math.cos(rot), sinR = Math.sin(rot);
                        const dx = x - ecx, dy = y - ecy;
                        const lx = ecx + dx * cosR - dy * sinR;
                        const ly = ecy + dx * sinR + dy * cosR;

                        const RH = this._handleRadius();
                        const TOL = 3 / (this.canvasZoom || 1);
                        const handles = this._resizeHandlePositions(el);
                        for (const [name, { hx, hy }] of Object.entries(handles)) {
                            if (lx >= hx - RH - TOL && lx <= hx + RH + TOL &&
                                ly >= hy - RH - TOL && ly <= hy + RH + TOL) return name;
                        }
                        return null;
                    },

                    _resizeCursor(handle) {
                        const map = {
                            nw: 'nw-resize', n: 'n-resize', ne: 'ne-resize',
                            e: 'e-resize', se: 'se-resize', s: 's-resize',
                            sw: 'sw-resize', w: 'w-resize'
                        };
                        return map[handle] || 'default';
                    },

                    drawRoundedRect(x, y, w, h, r) {
                        ctx.beginPath();
                        ctx.moveTo(x + r, y);
                        ctx.lineTo(x + w - r, y); ctx.quadraticCurveTo(x + w, y, x + w, y + r);
                        ctx.lineTo(x + w, y + h - r); ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
                        ctx.lineTo(x + r, y + h); ctx.quadraticCurveTo(x, y + h, x, y + h - r);
                        ctx.lineTo(x, y + r); ctx.quadraticCurveTo(x, y, x + r, y);
                        ctx.closePath();
                    },

                    /* ── Mapa base de calles (OpenStreetMap) ──
                       El mapa se ancla a un punto FIJO del plano, no al centro del lienzo:
                       si dependiera del tamaño del lienzo, se desplazaría respecto a los
                       elementos al entrar en pantalla completa o al cambiar de dispositivo.
                       El ancla se fija la primera vez y se guarda con el croquis. */
                    drawStreetBase(lw, lh) {
                        if (this.geoLat === null || this.geoLng === null) return;

                        if (this.mapAnchorX === null || this.mapAnchorX === undefined ||
                            this.mapAnchorY === null || this.mapAnchorY === undefined) {
                            this.mapAnchorX = Math.round(lw / 2);
                            this.mapAnchorY = Math.round(lh / 2);
                        }
                        const ax = this.mapAnchorX, ay = this.mapAnchorY;

                        const LAT = this.geoLat;
                        const LNG = this.geoLng;
                        const TILE_ZOOM = 19;   /* Máximo zoom nativo de OSM */
                        const SIM_ZOOM = parseFloat(this.tileZoom);
                        const SCALE = Math.pow(2, SIM_ZOOM - TILE_ZOOM);
                        const T = 256;

                        /* Proyección WebMercator en TILE_ZOOM */
                        const latRad = LAT * Math.PI / 180;
                        const n = Math.pow(2, TILE_ZOOM);
                        const xTileExact = (LNG + 180) / 360 * n;
                        const yTileExact = (1.0 - Math.log(Math.tan(latRad) + (1 / Math.cos(latRad))) / Math.PI) / 2.0 * n;

                        /* Píxel del mapa que cae sobre el ancla, más el micro-ajuste manual */
                        const cx = xTileExact * T + (this.mapOffsetX || 0);
                        const cy = yTileExact * T + (this.mapOffsetY || 0);

                        /* Tramo del plano que se está viendo ahora mismo */
                        const v0 = this._screenToCanvas(0, 0);
                        const v1 = this._screenToCanvas(lw, lh);

                        /* …traducido a píxeles del mapa */
                        const gx0 = (v0.x - ax) / SCALE + cx, gx1 = (v1.x - ax) / SCALE + cx;
                        const gy0 = (v0.y - ay) / SCALE + cy, gy1 = (v1.y - ay) / SCALE + cy;

                        let txMin = Math.floor(gx0 / T), txMax = Math.ceil(gx1 / T);
                        let tyMin = Math.floor(gy0 / T), tyMax = Math.ceil(gy1 / T);

                        /* Tope de seguridad: al alejarse mucho, el área visible abarcaría
                           cientos de teselas y no tiene sentido pedirlas todas */
                        const MAX_TILES = 120;
                        while ((txMax - txMin + 1) * (tyMax - tyMin + 1) > MAX_TILES) {
                            if (txMax - txMin >= tyMax - tyMin) { txMin++; txMax--; }
                            else { tyMin++; tyMax--; }
                            if (txMax < txMin || tyMax < tyMin) return;
                        }

                        const subs = ['a', 'b', 'c'];

                        for (let tx = txMin; tx <= txMax; tx++) {
                            for (let ty = tyMin; ty <= tyMax; ty++) {
                                const sub = subs[Math.abs(tx + ty) % 3];
                                const url = `https://${sub}.tile.openstreetmap.org/${TILE_ZOOM}/${tx}/${ty}.png`;

                                const dx = (tx * T - cx) * SCALE + ax;
                                const dy = (ty * T - cy) * SCALE + ay;
                                const size = T * SCALE;

                                if (this.tileCache[url] instanceof HTMLImageElement && this.tileCache[url].complete) {
                                    ctx.save();
                                    ctx.globalAlpha = this.tileOpacity;
                                    ctx.drawImage(this.tileCache[url], dx, dy, size, size);
                                    ctx.restore();
                                } else if (!this.tileCache[url]) {
                                    this.tileCache[url] = 'loading';
                                    const img = new Image();
                                    img.crossOrigin = 'anonymous';
                                    img.onload = () => {
                                        this.tileCache[url] = img;
                                        this.draw(); /* redibujar cuando llegue la tesela */
                                    };
                                    img.onerror = () => {
                                        console.error('[OSM] No se pudo cargar la tesela:', url);
                                        this.tileCache[url] = null;
                                    };
                                    img.src = url;
                                }
                            }
                        }

                        /* Marcador GPS del establecimiento, sobre el propio ancla */
                        ctx.save();
                        ctx.beginPath();
                        ctx.arc(ax, ay, this._px(8), 0, Math.PI * 2);
                        ctx.fillStyle = 'rgba(239,68,68,0.85)';
                        ctx.strokeStyle = 'white';
                        ctx.lineWidth = this._px(3);
                        ctx.fill(); ctx.stroke();
                        if (this._readable(9)) {
                            ctx.fillStyle = 'white';
                            ctx.font = '700 9px Inter, system-ui, Arial';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText('GPS', ax, ay + 0.5);
                        }
                        ctx.restore();
                    },

                    /* Punto donde la línea toca el borde del elemento, mirando hacia (tx,ty).
                       Evita que el cableado se meta por debajo de las cajas. */
                    _anchorOn(el, tx, ty) {
                        const cx = el.x + el.w / 2, cy = el.y + el.h / 2;
                        const dx = tx - cx, dy = ty - cy;
                        if (!dx && !dy) return { x: cx, y: cy };
                        const hw = el.w / 2 + 2, hh = el.h / 2 + 2;
                        const scale = Math.min(hw / Math.abs(dx || 1e-6), hh / Math.abs(dy || 1e-6));
                        return { x: cx + dx * scale, y: cy + dy * scale };
                    },

                    /* Cableado: recorrido en ángulo recto, como en un plano de instalaciones */
                    drawConnections() {
                        if (!ctx) return;
                        const piso = this.currentPiso;

                        ctx.save();
                        ctx.lineCap = 'round';
                        ctx.lineJoin = 'round';

                        const route = (ax, ay, bx, by) => {
                            /* Codo en L: tramo horizontal y luego vertical, con esquina suavizada */
                            const r = Math.min(14, Math.abs(bx - ax) / 2, Math.abs(by - ay) / 2);
                            ctx.beginPath();
                            ctx.moveTo(ax, ay);
                            if (r > 2) {
                                ctx.lineTo(bx - Math.sign(bx - ax) * r, ay);
                                ctx.quadraticCurveTo(bx, ay, bx, ay + Math.sign(by - ay) * r);
                            } else {
                                ctx.lineTo(bx, ay);
                            }
                            ctx.lineTo(bx, by);
                            ctx.stroke();
                        };

                        this.connections.forEach(conn => {
                            const el1 = this.elements.find(e => e.id === conn.from);
                            const el2 = this.elements.find(e => e.id === conn.to);
                            if (!el1 || !el2) return;
                            if ((el1.piso || 1) !== piso || (el2.piso || 1) !== piso) return;

                            const c1 = { x: el1.x + el1.w / 2, y: el1.y + el1.h / 2 };
                            const c2 = { x: el2.x + el2.w / 2, y: el2.y + el2.h / 2 };
                            const a = this._anchorOn(el1, c2.x, c2.y);
                            const b = this._anchorOn(el2, c1.x, c1.y);

                            /* Trazo grueso claro por debajo: separa el cable del fondo */
                            ctx.strokeStyle = 'rgba(255,255,255,0.9)';
                            ctx.lineWidth = this._px(4.5);
                            route(a.x, a.y, b.x, b.y);

                            ctx.strokeStyle = THEME.link;
                            ctx.lineWidth = this._px(1.8);
                            route(a.x, a.y, b.x, b.y);

                            /* Terminales */
                            ctx.fillStyle = THEME.linkInk;
                            [a, b].forEach(p => {
                                ctx.beginPath();
                                ctx.arc(p.x, p.y, this._px(2.6), 0, Math.PI * 2);
                                ctx.fill();
                            });
                        });

                        /* Cable en curso mientras se arrastra */
                        if (this.isConnecting && this.connectionStart) {
                            const startEl = this.elements.find(e => e.id === this.connectionStart);
                            if (startEl) {
                                const rect = canvas.getBoundingClientRect();
                                const p = this._screenToCanvas(
                                    this._lastMouseClientX - rect.left,
                                    this._lastMouseClientY - rect.top
                                );
                                const a = this._anchorOn(startEl, p.x, p.y);
                                ctx.strokeStyle = THEME.linkInk;
                                ctx.lineWidth = this._px(1.8);
                                ctx.setLineDash([this._px(6), this._px(5)]);
                                ctx.beginPath();
                                ctx.moveTo(a.x, a.y);
                                ctx.lineTo(p.x, p.y);
                                ctx.stroke();
                                ctx.setLineDash([]);
                            }
                        }
                        ctx.restore();
                    },

                    /* ─── Mobiliario esquemático ───
                       Todo se traza en proporción al ambiente (nunca en medidas fijas, que
                       se desbordaban en salas pequeñas) y ocupa la mitad inferior, dejando
                       la superior libre para el rótulo. */
                    drawFurnitureIcons(el) {
                        const st = this._style(el);
                        const sub = (el.subtype || '').toLowerCase();

                        /* Zona útil: sin el carril del rótulo ni los márgenes */
                        const pad = Math.min(12, el.w * 0.09, el.h * 0.09);
                        const zx = el.x + pad, zw = el.w - pad * 2;
                        const zy = el.y + el.h * 0.34, zh = el.h * 0.66 - pad;
                        if (zw < 16 || zh < 14) return;

                        const cx = zx + zw / 2, cy = zy + zh / 2;
                        const line = this._px(1.2);

                        ctx.save();
                        /* Nada puede salirse del ambiente */
                        this.drawRoundedRect(el.x + 1, el.y + 1, el.w - 2, el.h - 2, 7);
                        ctx.clip();

                        ctx.strokeStyle = st.accent;
                        ctx.fillStyle = 'rgba(255,255,255,0.75)';
                        ctx.lineWidth = line;
                        ctx.globalAlpha = 0.9;

                        /* Mueble rectangular con relleno claro */
                        const box = (x, y, w, h, r = 3) => {
                            ctx.beginPath();
                            ctx.roundRect(x, y, w, h, r);
                            ctx.fill();
                            ctx.stroke();
                        };
                        /* Asiento */
                        const seat = (x, y, r) => {
                            ctx.beginPath();
                            ctx.arc(x, y, r, 0, Math.PI * 2);
                            ctx.fill();
                            ctx.stroke();
                        };

                        switch (sub) {
                            case 'consultorio':
                            case 'consultorio_fisico': {
                                /* Escritorio con la silla del profesional y la del paciente */
                                const dw = Math.min(zw * 0.6, 130), dh = Math.min(zh * 0.3, 24);
                                const dy = cy - dh / 2;
                                const sr = Math.min(6, zh * 0.11);
                                box(cx - dw / 2, dy, dw, dh);
                                seat(cx, dy - sr * 1.5, sr);
                                seat(cx, dy + dh + sr * 1.5, sr * 0.92);
                                break;
                            }
                            case 'consultorio_funcional': {
                                /* Mesa compartida con dos puestos enfrentados */
                                const dw = Math.min(zw * 0.6, 130), dh = Math.min(zh * 0.3, 24);
                                const sr = Math.min(5.5, zh * 0.1);
                                ctx.setLineDash([this._px(5), this._px(4)]);
                                box(cx - dw / 2, cy - dh / 2, dw, dh);
                                ctx.setLineDash([]);
                                ctx.beginPath();
                                ctx.moveTo(cx, cy - dh / 2);
                                ctx.lineTo(cx, cy + dh / 2);
                                ctx.stroke();
                                seat(cx - dw * 0.26, cy - dh / 2 - sr * 1.4, sr);
                                seat(cx + dw * 0.26, cy + dh / 2 + sr * 1.4, sr);
                                break;
                            }
                            case 'emergencias': {
                                /* Camilla con cabecera */
                                const bw = Math.min(zw * 0.62, 90), bh = Math.min(zh * 0.42, 30);
                                box(cx - bw / 2, cy - bh / 2, bw, bh, 5);
                                ctx.beginPath();
                                ctx.moveTo(cx - bw / 2 + bw * 0.22, cy - bh / 2);
                                ctx.lineTo(cx - bw / 2 + bw * 0.22, cy + bh / 2);
                                ctx.stroke();
                                break;
                            }
                            case 'quirofano': {
                                /* Mesa quirúrgica y lámpara cenital */
                                const bw = Math.min(zw * 0.6, 86), bh = Math.min(zh * 0.28, 20);
                                box(cx - bw / 2, cy - bh / 2 + zh * 0.08, bw, bh, 4);
                                const lr = Math.min(11, zh * 0.2);
                                ctx.beginPath();
                                ctx.arc(cx, zy + zh * 0.2, lr, 0, Math.PI * 2);
                                ctx.fill(); ctx.stroke();
                                ctx.beginPath();
                                ctx.moveTo(cx - lr * 0.5, zy + zh * 0.2); ctx.lineTo(cx + lr * 0.5, zy + zh * 0.2);
                                ctx.moveTo(cx, zy + zh * 0.2 - lr * 0.5); ctx.lineTo(cx, zy + zh * 0.2 + lr * 0.5);
                                ctx.stroke();
                                break;
                            }
                            case 'administracion': {
                                /* Escritorio en L con silla */
                                const aw = Math.min(zw * 0.62, zw - 10), ah = Math.min(zh * 0.26, 20);
                                const bw = Math.min(zw * 0.2, 24), bh = Math.min(zh * 0.55, 46);
                                box(zx + 2, zy + zh * 0.1, aw, ah);
                                box(zx + 2 + aw - bw, zy + zh * 0.1, bw, bh);
                                seat(zx + 2 + aw * 0.45, zy + zh * 0.1 + ah + Math.min(11, zh * 0.18), Math.min(5.5, zh * 0.1));
                                break;
                            }
                            case 'baño': {
                                /* Inodoro y lavamanos */
                                const r = Math.min(9, zw * 0.14, zh * 0.2);
                                ctx.beginPath();
                                ctx.ellipse(cx - zw * 0.16, cy, r, r * 1.3, 0, 0, Math.PI * 2);
                                ctx.fill(); ctx.stroke();
                                box(cx - zw * 0.16 - r, cy - r * 1.3 - Math.min(9, zh * 0.16), r * 2, Math.min(8, zh * 0.14), 2);
                                ctx.beginPath();
                                ctx.arc(cx + zw * 0.2, cy, r * 0.85, 0, Math.PI * 2);
                                ctx.fill(); ctx.stroke();
                                break;
                            }
                            default: {
                                /* Mesa genérica */
                                box(cx - zw * 0.3, cy - Math.min(zh * 0.18, 14), zw * 0.6, Math.min(zh * 0.36, 28));
                            }
                        }
                        ctx.restore();
                    },

                    /* ── Distintivo FÍSICO / FUNCIONAL, discreto, en la esquina superior ── */
                    drawConsultorioBadge(el, subtype) {
                        const isFuncional = subtype === 'consultorio_funcional';
                        const st = this._style(el);
                        const label = isFuncional ? 'FUNC' : 'FÍS';
                        const fs = 8;
                        if (!this._readable(fs) || el.w < 60) return;

                        ctx.save();
                        ctx.font = `700 ${fs}px Inter, system-ui, Arial`;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        const tw = ctx.measureText(label).width;
                        const ph = fs + 5, pw = tw + 10;
                        const bx = el.x + el.w - pw - 5;
                        const by = el.y + 5;

                        ctx.fillStyle = st.accent;
                        ctx.globalAlpha = 0.14;
                        ctx.beginPath(); ctx.roundRect(bx, by, pw, ph, ph / 2); ctx.fill();
                        ctx.globalAlpha = 1;
                        ctx.strokeStyle = st.accent;
                        ctx.lineWidth = this._px(0.9);
                        ctx.beginPath(); ctx.roundRect(bx, by, pw, ph, ph / 2); ctx.stroke();

                        ctx.fillStyle = st.ink;
                        ctx.fillText(label, bx + pw / 2, by + ph / 2 + 0.5);
                        ctx.restore();
                    },


                    /* ─── Servicios del ambiente (luz · wifi · puntos de red) ───
                       Una sola tira compacta en la esquina inferior derecha, con iconos
                       monocromos sobre fondo blanco: informa sin llenar el plano de color. */
                    drawServiceIcons(el) {
                        const items = [];
                        if (el.attrs?.light && this.layers.power) items.push('light');
                        if (el.attrs?.wifi && this.layers.network) items.push('wifi');
                        if (el.attrs?.red > 0 && this.layers.network) items.push('red');
                        if (!items.length) return;

                        const COLORS = { light: '#f59e0b', wifi: '#2563eb', red: '#10b981' };
                        const s = Math.max(11, Math.min(15, Math.min(el.w, el.h) * 0.13));
                        const gap = s * 0.42;
                        const padIn = s * 0.42;
                        const count = items.length;
                        const stripW = count * s + (count - 1) * gap + padIn * 2;
                        const stripH = s + padIn * 2;
                        if (el.w < stripW + 10 || el.h < stripH + 10) return;

                        const x0 = el.x + el.w - stripW - 6;
                        const y0 = el.y + el.h - stripH - 6;

                        ctx.save();
                        /* Bandeja blanca */
                        ctx.fillStyle = 'rgba(255,255,255,0.94)';
                        ctx.strokeStyle = 'rgba(148,163,184,0.45)';
                        ctx.lineWidth = this._px(0.9);
                        ctx.beginPath();
                        ctx.roundRect(x0, y0, stripW, stripH, stripH / 2);
                        ctx.fill();
                        ctx.stroke();

                        items.forEach((kind, i) => {
                            const cx = x0 + padIn + s / 2 + i * (s + gap);
                            const cy = y0 + stripH / 2;
                            const col = COLORS[kind];

                            if (kind === 'light') {
                                /* Rayo */
                                this._icon(cx, cy, s, col, 1.6, (c) => {
                                    c.beginPath();
                                    c.moveTo(2.5, -9); c.lineTo(-5, 1); c.lineTo(-0.5, 1);
                                    c.lineTo(-2.5, 9); c.lineTo(5, -1); c.lineTo(0.5, -1);
                                    c.closePath();
                                    c.fill();
                                });
                            } else if (kind === 'wifi') {
                                /* Ondas */
                                this._icon(cx, cy, s, col, 2, (c) => {
                                    [4, 7.5, 11].forEach(r => {
                                        c.beginPath();
                                        c.arc(0, 6, r, Math.PI * 1.22, Math.PI * 1.78);
                                        c.stroke();
                                    });
                                    c.beginPath();
                                    c.arc(0, 6, 1.6, 0, Math.PI * 2);
                                    c.fill();
                                });
                            } else {
                                /* Toma de red y número de puntos */
                                this._icon(cx, cy, s, col, 1.7, (c) => {
                                    c.beginPath();
                                    c.roundRect(-6, -7, 12, 11, 1.5);
                                    c.stroke();
                                    c.beginPath();
                                    c.moveTo(-8, 4); c.lineTo(8, 4); c.lineTo(8, 8); c.lineTo(-8, 8);
                                    c.closePath();
                                    c.fill();
                                    [-3, 0, 3].forEach(px => {
                                        c.beginPath();
                                        c.moveTo(px, -4); c.lineTo(px, 0);
                                        c.stroke();
                                    });
                                });
                                if (el.attrs.red > 1 && this._readable(s * 0.62)) {
                                    ctx.font = `700 ${s * 0.62}px Inter, system-ui, Arial`;
                                    ctx.fillStyle = col;
                                    ctx.textAlign = 'left';
                                    ctx.textBaseline = 'middle';
                                    ctx.fillText('×' + el.attrs.red, cx + s * 0.52, cy + s * 0.02);
                                }
                            }
                        });
                        ctx.restore();
                    },

                    /* ─── Equipamiento TI ───
                       Todos los equipos comparten el mismo lenguaje: pictograma plano
                       dibujado dentro de una caja de 24×24, trazo uniforme y color del
                       tipo. Así el plano se lee de un vistazo aunque haya muchos. */
                    _hardwareGlyph(sub) {
                        const G = {
                            router: (c) => {
                                c.beginPath(); c.roundRect(-10, -1, 20, 9, 2); c.stroke();
                                c.beginPath(); c.moveTo(-4, 3.5); c.lineTo(4, 3.5); c.stroke();
                                c.beginPath(); c.moveTo(-5, -1); c.lineTo(-8, -9); c.stroke();
                                c.beginPath(); c.moveTo(5, -1); c.lineTo(8, -9); c.stroke();
                            },
                            ap: (c) => {
                                [4.5, 8.5, 12].forEach(r => {
                                    c.beginPath(); c.arc(0, 7, r, Math.PI * 1.2, Math.PI * 1.8); c.stroke();
                                });
                                c.beginPath(); c.arc(0, 7, 2, 0, Math.PI * 2); c.fill();
                            },
                            switch: (c) => {
                                c.beginPath(); c.roundRect(-11, -3, 22, 9, 2); c.stroke();
                                [-6.5, -2, 2.5, 7].forEach(x => {
                                    c.beginPath(); c.moveTo(x, -3); c.lineTo(x, -8); c.stroke();
                                });
                                c.beginPath(); c.arc(8, 1.5, 1.2, 0, Math.PI * 2); c.fill();
                            },
                            pozo: (c) => {
                                c.beginPath(); c.moveTo(0, -11); c.lineTo(0, -2); c.stroke();
                                c.beginPath(); c.arc(0, -11, 2, 0, Math.PI * 2); c.fill();
                                [[10, -2], [6.5, 3], [3, 8]].forEach(([hw, y]) => {
                                    c.beginPath(); c.moveTo(-hw, y); c.lineTo(hw, y); c.stroke();
                                });
                            },
                            panel_solar: (c) => {
                                c.beginPath(); c.roundRect(-11, -8, 22, 16, 1.5); c.stroke();
                                [-11, -3.7, 3.7, 11].forEach(x => {
                                    c.beginPath(); c.moveTo(x, -8); c.lineTo(x, 8); c.stroke();
                                });
                                [-8, 0, 8].forEach(y => {
                                    c.beginPath(); c.moveTo(-11, y); c.lineTo(11, y); c.stroke();
                                });
                            },
                            punto_red: (c) => {
                                c.beginPath(); c.roundRect(-8, -9, 16, 13, 2); c.stroke();
                                c.beginPath();
                                c.moveTo(-10, 4); c.lineTo(10, 4); c.lineTo(10, 9); c.lineTo(-10, 9);
                                c.closePath(); c.fill();
                                [-3.5, 0, 3.5].forEach(x => {
                                    c.beginPath(); c.moveTo(x, -5); c.lineTo(x, 0); c.stroke();
                                });
                            },
                            pc: (c) => {
                                c.beginPath(); c.roundRect(-6, -11, 12, 22, 2); c.stroke();
                                c.beginPath(); c.moveTo(-3, -7); c.lineTo(3, -7); c.stroke();
                                c.beginPath(); c.arc(0, 5, 1.8, 0, Math.PI * 2); c.fill();
                            },
                            laptop: (c) => {
                                c.beginPath(); c.roundRect(-9, -8, 18, 12, 1.5); c.stroke();
                                c.beginPath();
                                c.moveTo(-12, 6); c.lineTo(12, 6); c.lineTo(10, 9); c.lineTo(-10, 9);
                                c.closePath(); c.stroke();
                            },
                            tablet: (c) => {
                                c.beginPath(); c.roundRect(-7, -11, 14, 22, 2); c.stroke();
                                c.beginPath(); c.arc(0, 8, 1.3, 0, Math.PI * 2); c.fill();
                            },
                            monitor: (c) => {
                                c.beginPath(); c.roundRect(-11, -9, 22, 15, 2); c.stroke();
                                c.beginPath(); c.moveTo(0, 6); c.lineTo(0, 9); c.stroke();
                                c.beginPath(); c.moveTo(-5, 10); c.lineTo(5, 10); c.stroke();
                            },
                            teclado: (c) => {
                                c.beginPath(); c.roundRect(-12, -5, 24, 11, 2); c.stroke();
                                [-8, -4, 0, 4].forEach(x => {
                                    c.beginPath(); c.moveTo(x, -1); c.lineTo(x + 2, -1); c.stroke();
                                });
                                c.beginPath(); c.moveTo(-5, 3); c.lineTo(5, 3); c.stroke();
                            },
                            mouse: (c) => {
                                c.beginPath(); c.roundRect(-5.5, -10, 11, 20, 5.5); c.stroke();
                                c.beginPath(); c.moveTo(0, -10); c.lineTo(0, -3); c.stroke();
                            },
                            impresora: (c) => {
                                c.beginPath(); c.roundRect(-10, -3, 20, 9, 2); c.stroke();
                                c.beginPath(); c.roundRect(-6, -10, 12, 7, 1); c.stroke();
                                c.beginPath(); c.roundRect(-6, 6, 12, 5, 1); c.stroke();
                                c.beginPath(); c.arc(6.5, 0.5, 1.2, 0, Math.PI * 2); c.fill();
                            },
                            ticketera: (c) => {
                                c.beginPath(); c.roundRect(-8, -4, 16, 12, 2); c.stroke();
                                c.beginPath(); c.roundRect(-5, -11, 10, 7, 1); c.stroke();
                                c.beginPath(); c.moveTo(-4, 2); c.lineTo(4, 2); c.stroke();
                            },
                            escaner: (c) => {
                                c.beginPath(); c.roundRect(-11, -6, 22, 12, 2); c.stroke();
                                c.beginPath(); c.moveTo(-7, 0); c.lineTo(7, 0); c.stroke();
                                c.beginPath(); c.roundRect(-4, -3.5, 8, 3, 1); c.fill();
                            },
                            ups: (c) => {
                                c.beginPath(); c.roundRect(-9, -10, 18, 20, 2); c.stroke();
                                c.beginPath();
                                c.moveTo(2, -6); c.lineTo(-4, 1); c.lineTo(0, 1);
                                c.lineTo(-2, 7); c.lineTo(4, 0); c.lineTo(0, 0);
                                c.closePath(); c.fill();
                            },
                        };
                        return G[sub] || ((c) => {
                            c.beginPath(); c.roundRect(-9, -7, 18, 14, 2); c.stroke();
                            c.beginPath(); c.moveTo(-4, 0); c.lineTo(4, 0); c.stroke();
                        });
                    },

                    drawHardwareSymbol(el) {
                        const st = this._style(el);
                        const sub = (el.subtype || '').toLowerCase();
                        const cant = parseInt(el.cantidad, 10) || 1;
                        const estado = String(el.estado || '').toUpperCase();

                        /* Composición de la celda: la cantidad al costado del icono y,
                           debajo, el nombre del equipo. */
                        const fs = Math.max(6.5, Math.min(el.h * 0.15, 9));
                        const labelH = Math.min(el.h * 0.3, fs * 1.9);
                        const verLabel = this._readable(fs) && el.w >= 34;
                        const iconH = el.h - (verLabel ? labelH : 0);

                        let iconSize = Math.min(el.w, iconH) * 0.62;
                        let numW = 0, numFs = 0, hueco = 0;

                        if (cant > 1) {
                            numFs = Math.max(9, iconSize * 0.52);
                            hueco = iconSize * 0.16;
                            ctx.save();
                            ctx.font = `800 ${numFs}px Inter, system-ui, Arial`;
                            numW = ctx.measureText(String(cant)).width;
                            ctx.restore();

                            /* Si el conjunto no cabe a lo ancho, se achica el icono */
                            const disponible = el.w - 8;
                            const total = numW + hueco + iconSize;
                            if (total > disponible) {
                                const factor = disponible / total;
                                iconSize *= factor;
                                numFs *= factor;
                                numW *= factor;
                                hueco *= factor;
                            }
                        }

                        const grupoW = numW + hueco + iconSize;
                        const grupoX = el.x + el.w / 2 - grupoW / 2;
                        const centroY = el.y + iconH / 2;

                        /* Cantidad de unidades, a la izquierda del icono */
                        if (cant > 1) {
                            ctx.save();
                            ctx.font = `800 ${numFs}px Inter, system-ui, Arial`;
                            ctx.textAlign = 'left';
                            ctx.textBaseline = 'middle';
                            ctx.fillStyle = st.ink;
                            ctx.fillText(String(cant), grupoX, centroY);
                            ctx.restore();
                        }

                        this._icon(
                            grupoX + numW + hueco + iconSize / 2,
                            centroY,
                            iconSize,
                            st.ink,
                            1.9,
                            this._hardwareGlyph(sub)
                        );

                        /* Nombre del equipo */
                        if (verLabel) {
                            ctx.save();
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            const maxW = el.w - 5;
                            let cuerpo = fs;
                            const setFont = () => { ctx.font = `700 ${cuerpo}px Inter, system-ui, Arial`; };
                            setFont();

                            let txt = String(el.name || HW_LABEL[sub] || sub).toUpperCase();
                            /* 1º el nombre corto del tipo, 2º achicar la letra, 3º recortar */
                            if (ctx.measureText(txt).width > maxW && HW_LABEL[sub]) {
                                txt = HW_LABEL[sub];
                            }
                            while (cuerpo > 6 && ctx.measureText(txt).width > maxW) {
                                cuerpo -= 0.25;
                                setFont();
                            }
                            txt = this._fitText(txt, maxW);
                            if (txt) {
                                ctx.fillStyle = st.ink;
                                ctx.fillText(txt, el.x + el.w / 2, el.y + iconH + labelH / 2 - fs * 0.1);
                            }
                            ctx.restore();
                        }

                        /* Equipo que no está operativo: marca en la esquina */
                        if (estado === 'REGULAR' || estado === 'INOPERATIVO') {
                            const r = Math.max(2.5, Math.min(el.w, el.h) * 0.09);
                            ctx.save();
                            ctx.fillStyle = st.accent;
                            ctx.strokeStyle = '#ffffff';
                            ctx.lineWidth = this._px(1.2);
                            ctx.beginPath();
                            ctx.arc(el.x + el.w - r - 3, el.y + r + 3, r, 0, Math.PI * 2);
                            ctx.fill();
                            ctx.stroke();
                            /* La aspa distingue el inoperativo del regular sin depender del color */
                            if (estado === 'INOPERATIVO') {
                                ctx.strokeStyle = '#ffffff';
                                ctx.lineWidth = this._px(1.4);
                                const cx = el.x + el.w - r - 3, cy = el.y + r + 3, d = r * 0.45;
                                ctx.beginPath();
                                ctx.moveTo(cx - d, cy - d); ctx.lineTo(cx + d, cy + d);
                                ctx.moveTo(cx + d, cy - d); ctx.lineTo(cx - d, cy + d);
                                ctx.stroke();
                            }
                            ctx.restore();
                        }
                    },

                    /* ── Sistema de salud: pantalla con las siglas ── */
                    drawSistemaSymbol(el) {
                        const sub = (el.subtype || 'tua').toLowerCase();
                        const st = this._style(el);
                        const cx = el.x + el.w / 2;

                        /* Pantalla proporcional al elemento */
                        const mw = el.w * 0.76, mh = el.h * 0.5;
                        const mx = cx - mw / 2, my = el.y + el.h * 0.16;

                        ctx.save();
                        ctx.strokeStyle = st.accent;
                        ctx.lineWidth = this._px(1.5);
                        ctx.fillStyle = '#ffffff';
                        ctx.beginPath(); ctx.roundRect(mx, my, mw, mh, Math.min(5, mh * 0.18));
                        ctx.fill(); ctx.stroke();

                        /* Pie del monitor */
                        const standY = my + mh;
                        ctx.beginPath();
                        ctx.moveTo(cx, standY); ctx.lineTo(cx, standY + el.h * 0.09);
                        ctx.moveTo(cx - mw * 0.2, standY + el.h * 0.09);
                        ctx.lineTo(cx + mw * 0.2, standY + el.h * 0.09);
                        ctx.stroke();

                        /* Siglas dentro de la pantalla, ajustadas al ancho disponible */
                        const label = (el.name || sub).toUpperCase();
                        let fs = Math.min(mh * 0.46, 13);
                        ctx.font = `800 ${fs}px Inter, system-ui, Arial`;
                        while (fs > 6 && ctx.measureText(label).width > mw - 10) {
                            fs -= 0.5;
                            ctx.font = `800 ${fs}px Inter, system-ui, Arial`;
                        }
                        if (this._readable(fs)) {
                            ctx.fillStyle = st.ink;
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            ctx.fillText(this._fitText(label, mw - 8), cx, my + mh / 2);
                        }
                        ctx.restore();
                    },

                    /* ── Vía pública ──
                       Aspecto de mapa real: la calzada es una banda continua con dos
                       filos grises, sin marco ni esquinas redondeadas. La jerarquía se
                       distingue por el ancho y el tono, como en los mapas de calles. */
                    drawCalleSymbol(el) {
                        const sub = (el.subtype || 'jiron').toLowerCase();
                        const VIA = {
                            avenida: { asfalto: '#fdf3d2', filo: '#e8d9a8', eje: true },
                            jiron: { asfalto: '#ffffff', filo: '#dadce0', eje: false },
                            pasaje: { asfalto: '#fafafa', filo: '#e3e5e8', eje: false },
                        };
                        const v = VIA[sub] || VIA.jiron;

                        ctx.save();

                        /* Calzada */
                        ctx.fillStyle = v.asfalto;
                        ctx.fillRect(el.x, el.y, el.w, el.h);

                        /* Filos superior e inferior: el borde de la vía, no un marco */
                        ctx.strokeStyle = v.filo;
                        ctx.lineWidth = this._px(sub === 'avenida' ? 1.6 : 1.2);
                        ctx.beginPath();
                        ctx.moveTo(el.x, el.y + 0.5);
                        ctx.lineTo(el.x + el.w, el.y + 0.5);
                        ctx.moveTo(el.x, el.y + el.h - 0.5);
                        ctx.lineTo(el.x + el.w, el.y + el.h - 0.5);
                        ctx.stroke();

                        /* Eje central discontinuo, solo en avenidas */
                        if (v.eje && el.h > 26) {
                            ctx.strokeStyle = 'rgba(232,217,168,0.95)';
                            ctx.lineWidth = this._px(1.4);
                            ctx.setLineDash([this._px(11), this._px(9)]);
                            ctx.beginPath();
                            ctx.moveTo(el.x + 4, el.y + el.h / 2);
                            ctx.lineTo(el.x + el.w - 4, el.y + el.h / 2);
                            ctx.stroke();
                            ctx.setLineDash([]);
                        }

                        ctx.restore();
                    },

                    /* Nombre de la vía sobre la calzada, como en los mapas: gris,
                       sin recuadro y siguiendo la orientación de la calle. */
                    _drawCalleLabel(el) {
                        const nombre = String(el.name || '').trim();
                        if (!nombre) return;

                        /* En una vía vertical el nombre corre a lo largo de la calzada */
                        const vertical = el.h > el.w * 1.4;
                        const largo = vertical ? el.h : el.w;
                        const ancho = vertical ? el.w : el.h;

                        const size = Math.max(9, Math.min(ancho * 0.34, 13));
                        if (!this._readable(size)) return;

                        ctx.save();
                        ctx.translate(el.x + el.w / 2, el.y + el.h / 2);
                        if (vertical) ctx.rotate(-Math.PI / 2);

                        ctx.font = `600 ${size}px Inter, system-ui, Arial`;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        const txt = this._fitText(nombre, largo - 14);
                        if (txt) {
                            /* Halo claro para que se lea sobre cualquier fondo */
                            ctx.lineWidth = this._px(3);
                            ctx.strokeStyle = 'rgba(255,255,255,0.9)';
                            ctx.lineJoin = 'round';
                            ctx.strokeText(txt, 0, 0);
                            ctx.fillStyle = '#5f6368';
                            ctx.fillText(txt, 0, 0);
                        }
                        ctx.restore();
                    },

                    /* ─── Puertas ───
                       Interior: símbolo de plano (jambas, hoja y arco de barrido).
                       Principal: portón de rejas de dos hojas, el acceso desde la calle. */
                    drawDoorSymbol(el) {
                        const st = this._style(el);
                        const isExt = (el.subtype || 'interna').toLowerCase() === 'externa';

                        if (isExt) { this._drawPorton(el, st); return; }

                        ctx.save();
                        this.drawRoundedRect(el.x + 0.5, el.y + 0.5, el.w - 1, el.h - 1, 7);
                        ctx.clip();

                        const jamb = Math.max(2.5, Math.min(el.w * 0.07, 7));   // grosor de jamba
                        const wallT = Math.max(2.5, Math.min(el.h * 0.16, 7));  // grosor del muro
                        const topY = el.y + wallT / 2;                          // eje del muro
                        const inner = el.h - wallT;                             // fondo disponible

                        /* Jambas del vano */
                        ctx.fillStyle = st.ink;
                        ctx.fillRect(el.x, el.y, jamb, wallT * 1.6);
                        ctx.fillRect(el.x + el.w - jamb, el.y, jamb, wallT * 1.6);

                        /* Umbral */
                        ctx.strokeStyle = st.stroke;
                        ctx.lineWidth = this._px(1);
                        ctx.setLineDash([this._px(3), this._px(3)]);
                        ctx.beginPath();
                        ctx.moveTo(el.x + jamb, topY);
                        ctx.lineTo(el.x + el.w - jamb, topY);
                        ctx.stroke();
                        ctx.setLineDash([]);

                        /* Hoja: pivote, tablero y arco de apertura */
                        const L = Math.max(6, Math.min(el.w - jamb * 2, inner * 0.92));
                        const pivotX = el.x + jamb;

                        ctx.strokeStyle = st.accent;
                        ctx.globalAlpha = 0.55;
                        ctx.lineWidth = this._px(1);
                        ctx.beginPath();
                        ctx.arc(pivotX, topY, L, Math.PI / 2, 0, true);
                        ctx.stroke();
                        ctx.globalAlpha = 1;

                        ctx.strokeStyle = st.ink;
                        ctx.lineWidth = this._px(2.6);
                        ctx.lineCap = 'round';
                        ctx.beginPath();
                        ctx.moveTo(pivotX, topY);
                        ctx.lineTo(pivotX, topY + L);
                        ctx.stroke();

                        ctx.fillStyle = st.ink;
                        ctx.beginPath();
                        ctx.arc(pivotX, topY, this._px(1.8), 0, Math.PI * 2);
                        ctx.fill();

                        ctx.restore();
                    },

                    /* ─── Portón de rejas (acceso principal) ───
                       Dos hojas de barrotes verticales entre pilares, con travesaños
                       arriba y abajo y el remate de puntas cuando el tamaño lo permite. */
                    _drawPorton(el, st) {
                        ctx.save();
                        this.drawRoundedRect(el.x + 0.5, el.y + 0.5, el.w - 1, el.h - 1, 6);
                        ctx.clip();

                        const pilar = Math.max(4, Math.min(el.w * 0.06, 12));
                        const margenY = Math.max(3, el.h * 0.1);
                        const remate = el.h > 46 && el.w > 90;      // hay sitio para las puntas
                        /* Si va a escribirse el rótulo, la reja le cede la franja inferior */
                        const conRotulo = this._readable(8) && el.w > 60 && el.h > 40;
                        const topY = el.y + margenY + (remate ? Math.min(9, el.h * 0.14) : 0);
                        const botY = el.y + el.h - (conRotulo ? Math.max(margenY, 15) : margenY);
                        const alto = botY - topY;
                        if (alto < 8) { ctx.restore(); return; }

                        const trav = Math.max(2.5, Math.min(alto * 0.14, 7));   // travesaños
                        const x0 = el.x + pilar, x1 = el.x + el.w - pilar;
                        const anchoHojas = x1 - x0;
                        const centroX = el.x + el.w / 2;

                        /* Pilares laterales */
                        ctx.fillStyle = st.ink;
                        ctx.fillRect(el.x, el.y + margenY * 0.4, pilar, el.h - margenY * 0.8);
                        ctx.fillRect(el.x + el.w - pilar, el.y + margenY * 0.4, pilar, el.h - margenY * 0.8);

                        /* Travesaños de las dos hojas */
                        ctx.fillStyle = st.accent;
                        ctx.fillRect(x0, topY, anchoHojas, trav);
                        ctx.fillRect(x0, botY - trav, anchoHojas, trav);

                        /* Barrotes: separación regular, con un mínimo y un máximo por hoja */
                        const hojaW = anchoHojas / 2 - this._px(1);
                        const porHoja = Math.max(3, Math.min(9, Math.round(hojaW / 11)));
                        const paso = hojaW / porHoja;

                        ctx.strokeStyle = st.accent;
                        ctx.lineWidth = Math.max(this._px(1.4), Math.min(2.4, paso * 0.22));
                        ctx.lineCap = 'butt';
                        ctx.beginPath();
                        [x0, centroX + this._px(1)].forEach(inicio => {
                            for (let i = 1; i <= porHoja; i++) {
                                const bx = inicio + i * paso - paso / 2;
                                ctx.moveTo(bx, topY + trav);
                                ctx.lineTo(bx, botY - trav);
                            }
                        });
                        ctx.stroke();

                        /* Encuentro de las dos hojas */
                        ctx.fillStyle = st.ink;
                        const cierre = Math.max(2, this._px(2.4));
                        ctx.fillRect(centroX - cierre, topY - trav * 0.3, cierre * 2, alto + trav * 0.6);

                        /* Remate de puntas sobre el travesaño superior */
                        if (remate) {
                            const puntas = Math.max(4, Math.min(16, Math.round(anchoHojas / 16)));
                            const pw = anchoHojas / puntas;
                            const ph = Math.min(9, el.h * 0.14);
                            ctx.fillStyle = st.accent;
                            ctx.beginPath();
                            for (let i = 0; i < puntas; i++) {
                                const px = x0 + i * pw + pw / 2;
                                ctx.moveTo(px - pw * 0.3, topY);
                                ctx.lineTo(px, topY - ph);
                                ctx.lineTo(px + pw * 0.3, topY);
                            }
                            ctx.fill();
                        }

                        ctx.restore();

                        /* Marca de acceso desde la calle, bajo la reja */
                        if (conRotulo) {
                            ctx.save();
                            ctx.font = '800 8px Inter, system-ui, Arial';
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';
                            const txt = this._fitText('ACCESO PRINCIPAL', el.w - 10);
                            if (txt) {
                                ctx.fillStyle = st.ink;
                                ctx.fillText(txt, el.x + el.w / 2, botY + (el.y + el.h - botY) / 2);
                            }
                            ctx.restore();
                        }

                        /* Letrero del establecimiento sobre el portón */
                        this._drawLetreroEstablecimiento(el, st);
                    },

                    /* Cartel con el nombre del establecimiento del acta, encima del portón */
                    _drawLetreroEstablecimiento(el, st) {
                        const nombre = String(this.estabNombre || '').trim().toUpperCase();
                        if (!nombre) return;

                        const fs = Math.max(9, Math.min(el.w * 0.062, 13));
                        /* El cartel identifica el establecimiento: se tolera algo más
                           pequeño que el resto de textos antes de ocultarlo */
                        if (fs * (this.canvasZoom || 1) < 5) return;

                        ctx.save();
                        ctx.font = `800 ${fs}px Inter, system-ui, Arial`;
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        const maxW = Math.max(el.w * 1.25, 120);
                        const txt = this._fitText(nombre, maxW - 16);
                        if (!txt) { ctx.restore(); return; }

                        const tw = ctx.measureText(txt).width;
                        const pw = tw + 18, ph = fs * 2;
                        const px = el.x + el.w / 2 - pw / 2;
                        const py = el.y - ph - Math.max(6, el.h * 0.1);

                        /* Placa oscura, como el cartel de la entrada */
                        ctx.fillStyle = st.ink;
                        ctx.beginPath();
                        ctx.roundRect(px, py, pw, ph, Math.min(5, ph * 0.28));
                        ctx.fill();

                        /* Postes que la sujetan sobre el portón */
                        ctx.strokeStyle = st.ink;
                        ctx.lineWidth = this._px(2);
                        ctx.beginPath();
                        ctx.moveTo(px + pw * 0.2, py + ph);
                        ctx.lineTo(px + pw * 0.2, el.y);
                        ctx.moveTo(px + pw * 0.8, py + ph);
                        ctx.lineTo(px + pw * 0.8, el.y);
                        ctx.stroke();

                        ctx.fillStyle = '#ffffff';
                        ctx.fillText(txt, el.x + el.w / 2, py + ph / 2 + 0.5);
                        ctx.restore();
                    },

                    /* ─── Event helpers ─── */
                    _screenToCanvas(sx, sy) {
                        /* Inverse of: screen = (logical * zoom) + pan */
                        const z = this.canvasZoom || 1;
                        return {
                            x: (sx - this.panX) / z,
                            y: (sy - this.panY) / z,
                        };
                    },

                    _getEventCoords(e) {
                        const rect = canvas.getBoundingClientRect();
                        let sx, sy, clientX, clientY;
                        if (e.touches) {
                            sx = e.touches[0].clientX - rect.left;
                            sy = e.touches[0].clientY - rect.top;
                            clientX = e.touches[0].clientX;
                            clientY = e.touches[0].clientY;
                        } else {
                            sx = e.clientX - rect.left;
                            sy = e.clientY - rect.top;
                            clientX = e.clientX;
                            clientY = e.clientY;
                        }
                        const { x, y } = this._screenToCanvas(sx, sy);
                        return { x, y, clientX, clientY };
                    },

                    _lastMouseClientX: 0,
                    _lastMouseClientY: 0,

                    handleMouseDown(e) {
                        if (e.button === 2 || e.button === 1 || this.panMode) { // Clic derecho / central / modo mano
                            this._beginPan(e.clientX, e.clientY);
                            return;
                        }
                        this._startInteraction(this._getEventCoords(e));
                    },

                    /* ─── Gestos táctiles ─── */
                    _beginPan(clientX, clientY) {
                        this.isPanning = true;
                        this._lastMouseClientX = clientX;
                        this._lastMouseClientY = clientY;
                        if (canvas) canvas.style.cursor = 'grabbing';
                    },

                    /* Distancia y centro entre dos dedos, en coordenadas del lienzo */
                    _pinchInfo(e) {
                        const rect = canvas.getBoundingClientRect();
                        const [t1, t2] = [e.touches[0], e.touches[1]];
                        const dx = t2.clientX - t1.clientX;
                        const dy = t2.clientY - t1.clientY;
                        return {
                            dist: Math.hypot(dx, dy),
                            cx: (t1.clientX + t2.clientX) / 2 - rect.left,
                            cy: (t1.clientY + t2.clientY) / 2 - rect.top,
                        };
                    },

                    /* Cancela cualquier arrastre activo (al pasar a dos dedos) */
                    _cancelActiveGestures() {
                        isDragging = false; dragTarget = null;
                        isResizing = false; resizeTarget = null; resizeHandle = null;
                        isRotating = false; rotateTarget = null;
                        this.isDraggingMap = false;
                        this.isConnecting = false; this.connectionStart = null;
                        this.isPanning = false;
                    },

                    handleTouchStart(e) {
                        e.preventDefault();
                        if (e.touches.length >= 2) {
                            /* Dos dedos → zoom + desplazamiento del lienzo */
                            this._cancelActiveGestures();
                            this._isPinching = true;
                            const p = this._pinchInfo(e);
                            this._pinchDist = p.dist;
                            this._pinchCX = p.cx;
                            this._pinchCY = p.cy;
                            return;
                        }
                        if (this.panMode) {
                            this._beginPan(e.touches[0].clientX, e.touches[0].clientY);
                            return;
                        }
                        this._startInteraction(this._getEventCoords(e));
                    },

                    _startInteraction({ x, y, clientX, clientY }) {
                        if (this.selectedId) {
                            const sel = this.elements.find(e => e.id === this.selectedId);
                            if (sel) {
                                /* 1. Check resize handles */
                                const rh = this._getResizeHandle(sel, x, y);
                                if (rh) {
                                    isResizing = true;
                                    resizeTarget = sel;
                                    resizeHandle = rh;
                                    resizeStartX = x;
                                    resizeStartY = y;
                                    resizeOrigX = sel.x;
                                    resizeOrigY = sel.y;
                                    resizeOrigW = sel.w;
                                    resizeOrigH = sel.h;
                                    canvas.style.cursor = this._resizeCursor(rh);
                                    return;
                                }
                                /* 2. Check rotation handle */
                                if (sel._hx !== undefined) {
                                    const dx = x - sel._hx, dy = y - sel._hy;
                                    const rotHit = (this.isTouch ? 22 : 14) / (this.canvasZoom || 1);
                                    if (Math.sqrt(dx * dx + dy * dy) <= rotHit) {
                                        isRotating = true;
                                        rotateTarget = sel;
                                        rotateCenterX = sel.x + sel.w / 2;
                                        rotateCenterY = sel.y + sel.h / 2;
                                        rotateStartAngle = Math.atan2(y - rotateCenterY, x - rotateCenterX) * 180 / Math.PI;
                                        rotateStartRot = sel.rot || 0;
                                        canvas.style.cursor = 'grab';
                                        return;
                                    }
                                }
                            }
                        }

                        if (this.tool === 'red') {
                            const clicked = this.elements.find(el => !this._isChild(el) && this._isPointInElement(el, x, y));
                            if (clicked) { this.isConnecting = true; this.connectionStart = clicked.id; return; }
                        }
                        for (let i = this.elements.length - 1; i >= 0; i--) {
                            const el = this.elements[i];
                            /* Los equipos de un ambiente no se seleccionan sueltos: se
                               mueven y giran junto con el ambiente que los contiene */
                            if (this._isChild(el)) continue;
                            if (this._isPointInElement(el, x, y)) {
                                this.selectedId = el.id;
                                isDragging = true; dragTarget = el;
                                offset.x = x - el.x; offset.y = y - el.y;
                                this.draw(); return;
                            }
                        }

                        /* No element hit → Start map dragging if layer is visible */
                        if (this.layers.calles && this.geoLat !== null) {
                            this.isDraggingMap = true;
                            resizeStartX = x; // use these as temporary initial coords
                            resizeStartY = y;
                            canvas.style.cursor = 'grabbing';
                        } else if (clientX !== undefined) {
                            /* Zona vacía → se arrastra el lienzo (gesto natural en táctil y con mouse) */
                            this._beginPan(clientX, clientY);
                        }

                        this.selectedId = null; this.draw();
                    },

                    handleMouseMove(e) { this._moveInteraction(this._getEventCoords(e)); },
                    handleTouchMove(e) {
                        e.preventDefault();
                        if (this._isPinching && e.touches.length >= 2) {
                            const p = this._pinchInfo(e);
                            if (this._pinchDist > 0) {
                                /* Punto del croquis bajo el centro del gesto antes de escalar */
                                const before = this._screenToCanvas(this._pinchCX, this._pinchCY);
                                const factor = p.dist / this._pinchDist;
                                this.canvasZoom = Math.max(0.05, Math.min(5.0, this.canvasZoom * factor));
                                /* Ese mismo punto queda bajo el nuevo centro → zoom y paneo a la vez */
                                this.panX = p.cx - before.x * this.canvasZoom;
                                this.panY = p.cy - before.y * this.canvasZoom;
                            }
                            this._pinchDist = p.dist;
                            this._pinchCX = p.cx;
                            this._pinchCY = p.cy;
                            this.draw();
                            return;
                        }
                        if (this._isPinching) return; /* aún queda un dedo del gesto anterior */
                        this._moveInteraction(this._getEventCoords(e));
                    },

                    _moveInteraction({ x, y, clientX, clientY }) {
                        const containerRect = document.getElementById('canvas-container').getBoundingClientRect();
                        this.mouseX = clientX - containerRect.left;
                        this.mouseY = clientY - containerRect.top;
                        /* Delta del paneo: se calcula ANTES de refrescar la última posición */
                        const panDX = clientX - this._lastMouseClientX;
                        const panDY = clientY - this._lastMouseClientY;
                        this._lastMouseClientX = clientX;
                        this._lastMouseClientY = clientY;

                        /* ── Capturar posición para colaboración (en logical canvas coords) ── */
                        this._pendingCursorX = x;
                        this._pendingCursorY = y;

                        /* Paneo del lienzo (modo mano, clic derecho/central o arrastre en zona vacía) */
                        if (this.isPanning) {
                            this.panX += panDX;
                            this.panY += panDY;
                            this.hoveredEl = null;
                            this.draw();
                            return;
                        }

                        this.checkHover(x, y);

                        if (this.isConnecting) { this.draw(); return; }

                        /* Resize drag — rotation-aware */
                        if (isResizing && resizeTarget) {
                            const el = resizeTarget;
                            const rot = (el.rot || 0) * Math.PI / 180;
                            const cosR = Math.cos(rot), sinR = Math.sin(rot);
                            /* cos(-rot)=cosR, sin(-rot)=-sinR */

                            /* Transform world drag delta → element local space */
                            const worldDx = x - resizeStartX;
                            const worldDy = y - resizeStartY;
                            const ldx = worldDx * cosR + worldDy * sinR;   // local X component
                            const ldy = -worldDx * sinR + worldDy * cosR;   // local Y component

                            const h = resizeHandle;
                            let nw = resizeOrigW, nh = resizeOrigH;

                            if (h.includes('e')) nw = Math.max(20, resizeOrigW + ldx);
                            if (h.includes('w')) nw = Math.max(20, resizeOrigW - ldx);
                            if (h.includes('s')) nh = Math.max(20, resizeOrigH + ldy);
                            if (h.includes('n')) nh = Math.max(20, resizeOrigH - ldy);

                            nw = Math.round(nw / GRID) * GRID;
                            nh = Math.round(nh / GRID) * GRID;

                            /* Anchor point (opposite side) in local-space offset from orig center */
                            let aLocalX = 0, aLocalY = 0;
                            if (h.includes('w')) aLocalX = +resizeOrigW / 2;
                            else if (h.includes('e')) aLocalX = -resizeOrigW / 2;
                            if (h.includes('n')) aLocalY = +resizeOrigH / 2;
                            else if (h.includes('s')) aLocalY = -resizeOrigH / 2;

                            /* Anchor world position stays fixed */
                            const origCx = resizeOrigX + resizeOrigW / 2;
                            const origCy = resizeOrigY + resizeOrigH / 2;
                            const aWorldX = origCx + aLocalX * cosR - aLocalY * sinR;
                            const aWorldY = origCy + aLocalX * sinR + aLocalY * cosR;

                            /* New center = anchor + local-offset of new center, rotated to world */
                            let ncLocalX = 0, ncLocalY = 0;
                            if (h.includes('e')) ncLocalX = nw / 2;
                            else if (h.includes('w')) ncLocalX = -nw / 2;
                            if (h.includes('s')) ncLocalY = nh / 2;
                            else if (h.includes('n')) ncLocalY = -nh / 2;

                            const newCx = aWorldX + ncLocalX * cosR - ncLocalY * sinR;
                            const newCy = aWorldY + ncLocalX * sinR + ncLocalY * cosR;

                            resizeTarget.x = Math.round((newCx - nw / 2) / GRID) * GRID;
                            resizeTarget.y = Math.round((newCy - nh / 2) / GRID) * GRID;
                            resizeTarget.w = nw;
                            resizeTarget.h = nh;
                            this.draw();
                            return;
                        }

                        /* Rotation drag — el ambiente gira con todo su contenido */
                        if (isRotating && rotateTarget) {
                            const angle = Math.atan2(y - rotateCenterY, x - rotateCenterX) * 180 / Math.PI;
                            let delta = angle - rotateStartAngle;
                            let newRot = ((rotateStartRot + delta) % 360 + 360) % 360;
                            this._applyRotation(rotateTarget, Math.round(newRot));
                            this.draw();
                            return;
                        }

                        /* Map drag */
                        if (this.isDraggingMap) {
                            const ldx = x - resizeStartX;
                            const ldy = y - resizeStartY;
                            const SCALE = Math.pow(2, parseFloat(this.tileZoom) - 19);
                            this.mapOffsetX -= ldx / SCALE;
                            this.mapOffsetY -= ldy / SCALE;
                            resizeStartX = x;
                            resizeStartY = y;
                            this.draw();
                            return;
                        }

                        /* Update cursor when hovering over handles (no drag active) */
                        if (!isDragging && this.selectedId) {
                            const sel = this.elements.find(e => e.id === this.selectedId);
                            if (sel) {
                                const rh = this._getResizeHandle(sel, x, y);
                                canvas.style.cursor = rh ? this._resizeCursor(rh) : 'default';
                            }
                        }

                        if (isDragging && dragTarget) {
                            const newX = Math.round((x - offset.x) / GRID) * GRID;
                            const newY = Math.round((y - offset.y) / GRID) * GRID;
                            const dx = newX - dragTarget.x;
                            const dy = newY - dragTarget.y;

                            dragTarget.x = newX;
                            dragTarget.y = newY;

                            if (dx !== 0 || dy !== 0) {
                                this.elements.forEach(el => {
                                    if (el.parentId === dragTarget.id) {
                                        el.x += dx;
                                        el.y += dy;
                                    }
                                });
                            }

                            this.draw();
                            return;
                        }
                    },

                    handleMouseUp(e) { this._endInteraction(this._getEventCoords(e)); },
                    handleTouchEnd(e) {
                        e.preventDefault();
                        /* Fin del gesto de dos dedos: se ignora hasta soltar todos */
                        if (this._isPinching) {
                            if (e.touches.length === 0) {
                                this._isPinching = false;
                                this._pinchDist = 0;
                            }
                            return;
                        }
                        const coords = e.changedTouches && e.changedTouches.length
                            ? (() => {
                                const rect = canvas.getBoundingClientRect();
                                return this._screenToCanvas(
                                    e.changedTouches[0].clientX - rect.left,
                                    e.changedTouches[0].clientY - rect.top
                                );
                            })()
                            : { x: 0, y: 0 };
                        this._endInteraction(coords);
                    },

                    _endInteraction({ x, y }) {
                        if (this.isPanning) {
                            this.isPanning = false;
                            canvas.style.cursor = 'default';
                            return;
                        }
                        if (isResizing) {
                            this._snapshot();
                            isResizing = false; resizeTarget = null; resizeHandle = null;
                            canvas.style.cursor = 'default';
                            return;
                        }
                        if (isRotating) {
                            this._snapshot();
                            isRotating = false; rotateTarget = null;
                            canvas.style.cursor = 'default';
                            return;
                        }
                        if (this.isConnecting && this.connectionStart) {
                            const endEl = this.elements.find(el => !this._isChild(el) && this._isPointInElement(el, x, y));
                            if (endEl && endEl.id !== this.connectionStart) {
                                /* No duplicate connections */
                                const already = this.connections.some(c =>
                                    (c.from === this.connectionStart && c.to === endEl.id) ||
                                    (c.from === endEl.id && c.to === this.connectionStart)
                                );
                                if (!already) {
                                    this._snapshot();
                                    this.connections.push({ from: this.connectionStart, to: endEl.id });
                                }
                            }
                            this.isConnecting = false; this.connectionStart = null; this.draw();
                        }
                        if (this.isDraggingMap) {
                            this._snapshot();
                            this.isDraggingMap = false;
                            canvas.style.cursor = 'default';
                            return;
                        }
                        if (isDragging && dragTarget) {
                            dragTarget._ts = Date.now(); /* actualizar timestamp al mover */
                            this._snapshot();
                        }
                        isDragging = false; dragTarget = null;
                    },

                    /* ─── Element operations ─── */
                    resizeSelected(dw, dh) {
                        const el = this.elements.find(e => e.id === this.selectedId);
                        if (el) {
                            this._snapshot();
                            el.w = Math.max(20, el.w + dw);
                            el.h = Math.max(20, el.h + dh);
                            el._ts = Date.now();
                            this.draw();
                        }
                    },

                    rotateSelected(deg = 90) {
                        const el = this.elements.find(e => e.id === this.selectedId);
                        if (el) {
                            this._snapshot();
                            this._applyRotation(el, (el.rot || 0) + deg);
                            el._ts = Date.now();
                            this.draw();
                        }
                    },

                    setRotation(deg) {
                        const el = this.elements.find(e => e.id === this.selectedId);
                        if (el) {
                            this._snapshot();
                            this._applyRotation(el, +deg);
                            el._ts = Date.now();
                            this.draw();
                        }
                    },

                    setSize(prop, val) {
                        const el = this.elements.find(e => e.id === this.selectedId);
                        if (el) {
                            this._snapshot();
                            el[prop] = Math.max(20, +val);
                            el._ts = Date.now();
                            this.draw();
                        }
                    },

                    toggleSelectedAttr(prop) {
                        const el = this.elements.find(e => e.id === this.selectedId);
                        if (el) {
                            if (!el.attrs) el.attrs = {};
                            this._snapshot();
                            el.attrs[prop] = !el.attrs[prop];
                            el._ts = Date.now();
                            this.draw();
                        }
                    },

                    changeSelectedAttrCount(prop, delta) {
                        const el = this.elements.find(e => e.id === this.selectedId);
                        if (el) {
                            if (!el.attrs) el.attrs = {};
                            this._snapshot();
                            el.attrs[prop] = Math.max(0, (el.attrs[prop] || 0) + delta);
                            el._ts = Date.now();
                            this.draw();
                        }
                    },

                    deleteSelected() {
                        if (!this.selectedId) return;
                        this._snapshot();

                        /* Al eliminar un ambiente se van con él los equipos que contiene:
                           sueltos quedarían inaccesibles, porque no se seleccionan solos */
                        const aBorrar = new Set([this.selectedId]);
                        this._childrenOf(this.selectedId).forEach(ch => aBorrar.add(ch.id));

                        aBorrar.forEach(id => {
                            if (!this.deletedIds.includes(id)) this.deletedIds.push(id); /* registrar para sync */
                        });
                        this.elements = this.elements.filter(e => !aBorrar.has(e.id));
                        this.connections = this.connections.filter(c => !aBorrar.has(c.from) && !aBorrar.has(c.to));
                        this.selectedId = null; this.draw();
                    },

                    async confirmDelete(e) {
                        if (!this.selectedId) return;
                        const result = await Swal.fire({
                            target: document.getElementById('tablet-editor-container'),
                            title: '¿Eliminar elemento?',
                            text: 'Esta acción se puede deshacer con Ctrl+Z.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        });
                        if (result.isConfirmed) this.deleteSelected();
                    },

                    exportImage() {
                        if (!canvas) return;
                        this._flushDraw();   /* asegura que el PNG refleje el estado actual */
                        const link = document.createElement('a');
                        link.download = 'croquis-infraestructura.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                        Swal.fire({ target: document.getElementById('tablet-editor-container'), title: '¡Imagen exportada!', text: 'El croquis se descargó como PNG.', icon: 'success', confirmButtonColor: '#4f46e5', timer: 2000, showConfirmButton: false });
                    },

                    async exportPdf() {
                        /* El reporte se arma en el servidor con lo que hay guardado: sin
                           guardar antes, el PDF no existiría (404) o saldría desfasado */
                        if (this.isSaving) return;
                        const guardado = await this.saveData();
                        if (!guardado) return;

                        const ventana = window.open('{{ route('usuario.monitoreo.infraestructura-2d.pdf', $acta->id) }}', '_blank');

                        if (!ventana) {
                            Swal.fire({
                                title: 'Permite las ventanas emergentes',
                                text: 'El navegador bloqueó la pestaña del PDF. Habilita las ventanas emergentes para este sitio y vuelve a intentarlo.',
                                icon: 'warning', confirmButtonColor: '#4f46e5'
                            });
                        } else if (document.fullscreenElement) {
                            /* En pantalla completa la pestaña nueva queda detrás: conviene avisar */
                            Swal.fire({
                                toast: true, position: 'top-end', icon: 'info',
                                title: 'El PDF se abrió en otra pestaña',
                                text: 'Sal de pantalla completa para verlo.',
                                timer: 4000, showConfirmButton: false
                            });
                        }
                    },

                    /* ─────────────────────────────────────────────────────────────────
                          EXPORTAR PLANO A1 — SVG VECTORIAL PARA PLOTTER
                          A1 landscape: 841 × 594 mm  |  viewBox 0 0 841 594  (1u = 1mm)
                       ───────────────────────────────────────────────────────────────── */
                    exportPlano() {
                        /* ── Dimensiones de página A1 horizontal (mm) ── */
                        const PW = 841, PH = 594;
                        const M = 12;   /* margen exterior */
                        const TB = 34;   /* bloque de título abajo */
                        const FH = 7;    /* cabecera de piso */

                        const lw = this.logicalW || 800;
                        const lh = this.logicalH || 600;
                        const nFloors = this.totalPisos || 1;

                        /* Área de dibujo disponible */
                        const GAP = 5;   /* separación entre columnas de pisos */
                        const drawW = PW - 2 * M;
                        const drawH = PH - 2 * M - TB - FH;
                        const floorW = nFloors > 1
                            ? (drawW - GAP * (nFloors - 1)) / nFloors
                            : drawW;

                        /* Escala: hacer que los elementos llenen la columna */
                        const scale = Math.min(floorW / lw, drawH / lh);
                        const fitW = lw * scale;
                        const fitH = lh * scale;

                        /* Centrado dentro de la columna */
                        const padX = (floorW - fitW) / 2;
                        const padY = (drawH - fitH) / 2;

                        /* ── Paleta de colores ── */
                        const COLORS = {
                            ambiente: {
                                consultorio: { f: '#bbf7d0', s: '#16a34a' },
                                consultorio_fisico: { f: '#bbf7d0', s: '#16a34a' },
                                consultorio_funcional: { f: '#fef3c7', s: '#d97706' },
                                emergencias: { f: '#fecaca', s: '#dc2626' },
                                quirofano: { f: '#bae6fd', s: '#0284c7' },
                                administracion: { f: '#e9d5ff', s: '#9333ea' },
                                'baño': { f: '#cffafe', s: '#0891b2' },
                                _d: { f: '#f1f5f9', s: '#94a3b8' },
                            },
                            pasillo: { f: '#f8fafc', s: '#64748b' },
                            hardware: {
                                pozo: { f: '#d1fae5', s: '#059669' },
                                punto_red: { f: '#d1fae5', s: '#059669' },
                                _d: { f: '#dbeafe', s: '#2563eb' },
                            },
                            puerta: {
                                externa: { f: '#fee2e2', s: '#b91c1c' },
                                _d: { f: '#fef9c3', s: '#ca8a04' },
                            },
                            calle: {
                                avenida: { f: '#d1d5db', s: '#6b7280' },
                                jiron: { f: '#e5e7eb', s: '#9ca3af' },
                                pasaje: { f: '#f3f4f6', s: '#9ca3af' },
                                _d: { f: '#e5e7eb', s: '#9ca3af' },
                            },
                            sistema: {
                                tua: { f: '#ede9fe', s: '#7c3aed' },
                                sihce: { f: '#dbeafe', s: '#1d4ed8' },
                                sismed: { f: '#ccfbf1', s: '#0d9488' },
                                hisminsa: { f: '#fed7aa', s: '#c2410c' },
                                sisgalenplus: { f: '#dbeafe', s: '#2563eb' },
                                _d: { f: '#f1f5f9', s: '#64748b' },
                            },
                        };

                        const getColor = (type, subtype) => {
                            const grp = COLORS[type];
                            if (!grp) return { f: '#f1f5f9', s: '#94a3b8' };
                            if (typeof grp.f !== 'undefined') return grp; /* direct (pasillo) */
                            return grp[subtype] || grp._d || { f: '#f1f5f9', s: '#94a3b8' };
                        };

                        const esc = v => String(v || '')
                            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
                        const f = n => Math.round(n * 1000) / 1000; /* 3 decimales */

                        /* ── Sistema de nombres legibles ── */
                        const SIST_LABELS = {
                            tua: 'TUA', sihce: 'SIHCE', sismed: 'SISMED',
                            hisminsa: 'HISMINSA', sisgalenplus: 'GalenPlus'
                        };

                        let L = []; /* líneas SVG */

                        /* ── Cabecera SVG ── */
                        L.push('<' + '?xml version="1.0" encoding="UTF-8"?' + '>');
                        L.push(`<svg xmlns="http://www.w3.org/2000/svg" `
                            + `width="841mm" height="594mm" viewBox="0 0 841 594">`);
                        L.push(`  <!-- Plano de Infraestructura A1 — Sistema de Actas ICATEC -->`);
                        L.push(`  <rect width="841" height="594" fill="white"/>`);

                        /* ── Borde técnico doble ── */
                        L.push(`  <rect x="${M}" y="${M}" width="${PW - 2 * M}" height="${PH - 2 * M}"`
                            + ` fill="none" stroke="#1e293b" stroke-width="0.8"/>`);
                        L.push(`  <rect x="${M + 1.5}" y="${M + 1.5}" width="${PW - 2 * M - 3}" height="${PH - 2 * M - 3}"`
                            + ` fill="none" stroke="#94a3b8" stroke-width="0.25"/>`);

                        /* ── Renderizar cada piso ── */
                        for (let piso = 1; piso <= nFloors; piso++) {
                            const ox = M + (piso - 1) * (floorW + GAP);
                            const oy = M;
                            const floorEls = this.elements.filter(e => (e.piso || 1) === piso);

                            /* Cabecera de piso */
                            L.push(`  <rect x="${f(ox)}" y="${f(oy)}" width="${f(floorW)}" height="${FH}" fill="#4f46e5"/>`);
                            L.push(`  <text x="${f(ox + floorW / 2)}" y="${f(oy + FH * 0.72)}" `
                                + `text-anchor="middle" fill="white" `
                                + `font-family="Arial,Helvetica,sans-serif" font-size="3.5" font-weight="bold">`
                                + `PISO ${piso}  ·  ${floorEls.length} elemento${floorEls.length !== 1 ? 's' : ''}`
                                + `</text>`);

                            /* Fondo de dibujo */
                            L.push(`  <rect x="${f(ox)}" y="${f(oy + FH)}" width="${f(floorW)}" height="${f(drawH)}"`
                                + ` fill="#fafafa" stroke="#e2e8f0" stroke-width="0.25"/>`);

                            /* Grid ligero cada 20px lógicos */
                            const gStep = 20 * scale;
                            for (let gx = 0; gx <= fitW + 0.01; gx += gStep) {
                                L.push(`  <line x1="${f(ox + padX + gx)}" y1="${f(oy + FH + padY)}"` +
                                    ` x2="${f(ox + padX + gx)}" y2="${f(oy + FH + padY + fitH)}"` +
                                    ` stroke="#e8edf2" stroke-width="0.1"/>`);
                            }
                            for (let gy = 0; gy <= fitH + 0.01; gy += gStep) {
                                L.push(`  <line x1="${f(ox + padX)}" y1="${f(oy + FH + padY + gy)}"` +
                                    ` x2="${f(ox + padX + fitW)}" y2="${f(oy + FH + padY + gy)}"` +
                                    ` stroke="#e8edf2" stroke-width="0.1"/>`);
                            }

                            /* Conexiones de red */
                            if (this.layers && this.layers.network) {
                                this.connections.forEach(conn => {
                                    const e1 = floorEls.find(e => e.id === conn.from);
                                    const e2 = floorEls.find(e => e.id === conn.to);
                                    if (!e1 || !e2) return;
                                    const x1 = ox + padX + (e1.x + e1.w / 2) * scale;
                                    const y1 = oy + FH + padY + (e1.y + e1.h / 2) * scale;
                                    const x2 = ox + padX + (e2.x + e2.w / 2) * scale;
                                    const y2 = oy + FH + padY + (e2.y + e2.h / 2) * scale;
                                    L.push(`  <line x1="${f(x1)}" y1="${f(y1)}" x2="${f(x2)}" y2="${f(y2)}"` +
                                        ` stroke="#3b82f6" stroke-width="0.35" stroke-dasharray="2 1.5"/>`);
                                });
                            }

                            /* ── Elementos ── */
                            floorEls.forEach(el => {
                                const type = (el.type || '').toLowerCase();
                                const subtype = (el.subtype || '').toLowerCase();
                                const rot = el.rot || 0;

                                const ex = ox + padX + el.x * scale;
                                const ey = oy + FH + padY + el.y * scale;
                                const ew = el.w * scale;
                                const eh = el.h * scale;
                                const ecx = ex + ew / 2;
                                const ecy = ey + eh / 2;

                                const { f: fill, s: stroke } = getColor(type, subtype);
                                const xfrm = rot ? ` transform="rotate(${rot} ${f(ecx)} ${f(ecy)})"` : '';
                                const r = type === 'hardware' ? Math.min(ew, eh) / 2 : 2;
                                const dash = subtype === 'consultorio_funcional' ? ` stroke-dasharray="2 1.5"` : '';
                                const sw = type === 'hardware' ? '0.7' : '0.5';

                                /* Forma principal — se omite para puertas (tienen su propio render) */
                                if (type !== 'puerta') {
                                    L.push(`  <rect x="${f(ex)}" y="${f(ey)}" width="${f(ew)}" height="${f(eh)}"` +
                                        ` rx="${f(r)}" fill="${fill}" stroke="${stroke}"` +
                                        ` stroke-width="${sw}"${dash}${xfrm}/>`);
                                }

                                /* ── Símbolo de puerta realista (SVG export) ── */
                                if (type === 'puerta') {
                                    /* Helper: dibuja una hoja de puerta en SVG */
                                    const _svgDoor = (px, py, pw, ph, panelFill, knobSide) => {
                                        const fi = Math.max(0.8, Math.min(pw, ph) * 0.07);
                                        const wi = pw - fi * 2;
                                        const hi = ph - fi;
                                        const ww = wi * 0.42;
                                        const wh = hi * 0.58;
                                        const wox = px + fi + wi * 0.29;
                                        const woy = py + fi + hi * 0.10;
                                        const archR = ww / 2;
                                        const divY = woy + wh * 0.46;
                                        const knobX = knobSide === 'right' ? px + pw - fi * 2.4 : px + fi * 2.4;
                                        const knobY = py + ph * 0.56;
                                        const knobR = Math.max(0.6, fi * 0.9);

                                        /* Marco exterior negro */
                                        L.push(`  <rect x="${f(px)}" y="${f(py)}" width="${f(pw)}" height="${f(ph)}" rx="0.6" fill="#111111"${xfrm}/>`);
                                        /* Panel */
                                        L.push(`  <rect x="${f(px + fi)}" y="${f(py + fi)}" width="${f(wi)}" height="${f(hi)}" rx="0.4" fill="${panelFill}"${xfrm}/>`);
                                        /* Ventana: arco superior + rect inferior (clip via mask) */
                                        const archTopY = woy + archR;
                                        /* Rect inferior de ventana */
                                        L.push(`  <rect x="${f(wox)}" y="${f(woy + archR)}" width="${f(ww)}" height="${f(wh - archR)}" fill="#dedede" stroke="#111" stroke-width="${f(fi * 0.4)}"${xfrm}/>`);
                                        /* Arco superior de ventana */
                                        L.push(`  <path d="M ${f(wox)} ${f(archTopY)} A ${f(archR)} ${f(archR)} 0 0 1 ${f(wox + ww)} ${f(archTopY)}" fill="#dedede" stroke="#111" stroke-width="${f(fi * 0.4)}"${xfrm}/>`);
                                        /* Línea de unión arco-rect (para cerrar la forma visualmente) */
                                        L.push(`  <rect x="${f(wox)}" y="${f(woy)}" width="${f(ww)}" height="${f(wh)}" rx="0" fill="none" stroke="#111" stroke-width="${f(fi * 0.4)}"${xfrm}/>`);
                                        /* Divisor horizontal ventana */
                                        L.push(`  <line x1="${f(wox + fi * 0.2)}" y1="${f(divY)}" x2="${f(wox + ww - fi * 0.2)}" y2="${f(divY)}" stroke="#111" stroke-width="${f(fi * 0.4)}"${xfrm}/>`);
                                        /* Pomo: aro negro + círculo gris oscuro + reflejo */
                                        L.push(`  <circle cx="${f(knobX)}" cy="${f(knobY)}" r="${f(knobR * 1.45)}" fill="#111"${xfrm}/>`);
                                        L.push(`  <circle cx="${f(knobX)}" cy="${f(knobY)}" r="${f(knobR)}" fill="#444"${xfrm}/>`);
                                        L.push(`  <circle cx="${f(knobX - knobR * 0.28)}" cy="${f(knobY - knobR * 0.28)}" r="${f(knobR * 0.38)}" fill="rgba(255,255,255,0.65)"${xfrm}/>`);
                                    };

                                    if (subtype === 'externa') {
                                        /* ══ DOBLE HOJA ══ */
                                        const outerFi = Math.max(0.8, Math.min(ew, eh) * 0.065);
                                        /* Marco exterior */
                                        L.push(`  <rect x="${f(ex)}" y="${f(ey)}" width="${f(ew)}" height="${f(eh)}" rx="0.8" fill="#111111"${xfrm}/>`);
                                        const halfW = (ew - outerFi * 3) / 2;
                                        /* Hoja izquierda */
                                        _svgDoor(ex + outerFi, ey + outerFi, halfW, eh - outerFi, '#e0e0e0', 'right');
                                        /* Hoja derecha */
                                        _svgDoor(ex + outerFi * 2 + halfW, ey + outerFi, halfW, eh - outerFi, '#e0e0e0', 'left');
                                    } else {
                                        /* ══ HOJA SIMPLE ══ */
                                        _svgDoor(ex, ey, ew, eh, '#ffffff', 'right');
                                    }
                                }

                                /* ── Símbolo de hardware ── */
                                if (type === 'hardware') {
                                    const hw = subtype || 'router';
                                    if (hw === 'router') {
                                        L.push(`  <rect x="${f(ecx - 4)}" y="${f(ecy - 2)}" width="8" height="4" rx="0.8"` +
                                            ` fill="none" stroke="#2563eb" stroke-width="0.5"${xfrm}/>`);
                                        L.push(`  <line x1="${f(ecx - 2)}" y1="${f(ecy - 2)}" x2="${f(ecx - 3)}" y2="${f(ecy - 5)}"` +
                                            ` stroke="#2563eb" stroke-width="0.5"${xfrm}/>`);
                                        L.push(`  <line x1="${f(ecx + 2)}" y1="${f(ecy - 2)}" x2="${f(ecx + 3)}" y2="${f(ecy - 5)}"` +
                                            ` stroke="#2563eb" stroke-width="0.5"${xfrm}/>`);
                                    } else if (hw === 'ap') {
                                        L.push(`  <circle cx="${f(ecx)}" cy="${f(ecy)}" r="2.5"` +
                                            ` fill="none" stroke="#2563eb" stroke-width="0.5"${xfrm}/>`);
                                        L.push(`  <circle cx="${f(ecx)}" cy="${f(ecy)}" r="0.9" fill="#2563eb"${xfrm}/>`);
                                        [4, 6, 8].forEach(rv => L.push(`  <path d="M ${f(ecx - rv * 0.6)} ${f(ecy + rv * 0.4)}` +
                                            ` A ${rv} ${rv} 0 0 1 ${f(ecx + rv * 0.6)} ${f(ecy + rv * 0.4)}"` +
                                            ` fill="none" stroke="#2563eb" stroke-width="0.4"${xfrm}/>`));
                                    } else if (hw === 'switch') {
                                        L.push(`  <rect x="${f(ecx - 5)}" y="${f(ecy - 1.5)}" width="10" height="3" rx="0.5"` +
                                            ` fill="none" stroke="#2563eb" stroke-width="0.5"${xfrm}/>`);
                                        [1, 2, 3, 4].forEach(p => L.push(`  <line x1="${f(ecx - 5 + p * 2)}" y1="${f(ecy - 1.5)}"` +
                                            ` x2="${f(ecx - 5 + p * 2)}" y2="${f(ecy - 3.5)}" stroke="#2563eb" stroke-width="0.4"${xfrm}/>`));
                                    } else if (hw === 'pozo') {
                                        L.push(`  <line x1="${f(ecx)}" y1="${f(ecy - 5)}" x2="${f(ecx)}" y2="${f(ecy - 1)}"` +
                                            ` stroke="#059669" stroke-width="0.7"${xfrm}/>`);
                                        [[4, 0], [3, 2], [2, 4]].forEach(([hw2, off]) =>
                                            L.push(`  <line x1="${f(ecx - hw2)}" y1="${f(ecy - 1 + off)}"` +
                                                ` x2="${f(ecx + hw2)}" y2="${f(ecy - 1 + off)}"` +
                                                ` stroke="#059669" stroke-width="0.6"${xfrm}/>`));
                                    } else if (hw === 'panel_solar') {
                                        L.push(`  <rect x="${f(ecx - 5.5)}" y="${f(ecy - 4)}" width="11" height="8"` +
                                            ` fill="none" stroke="#d97706" stroke-width="0.5"${xfrm}/>`);
                                        [-1.8, 1.8].forEach(x => L.push(`  <line x1="${f(ecx + x)}" y1="${f(ecy - 4)}"` +
                                            ` x2="${f(ecx + x)}" y2="${f(ecy + 4)}" stroke="#d97706" stroke-width="0.4"${xfrm}/>`));
                                        L.push(`  <line x1="${f(ecx - 5.5)}" y1="${f(ecy)}"` +
                                            ` x2="${f(ecx + 5.5)}" y2="${f(ecy)}" stroke="#d97706" stroke-width="0.4"${xfrm}/>`);
                                    } else if (hw === 'punto_red') {
                                        L.push(`  <rect x="${f(ecx - 3)}" y="${f(ecy - 2)}" width="6" height="5" rx="0.5"` +
                                            ` fill="none" stroke="#10b981" stroke-width="0.6"${xfrm}/>`);
                                    }
                                }

                                /* ── Sistema label (interior) ── */
                                if (type === 'sistema') {
                                    const sl = (SIST_LABELS[subtype] || subtype.toUpperCase()).substring(0, 10);
                                    const fs = ew > 14 ? 3 : 2.2;
                                    L.push(`  <text x="${f(ecx)}" y="${f(ecy + fs * 0.38)}"` +
                                        ` text-anchor="middle" fill="#4c1d95"` +
                                        ` font-family="Arial,Helvetica,sans-serif"` +
                                        ` font-size="${fs}" font-weight="bold"${xfrm}>${esc(sl)}</text>`);
                                }

                                /* ── Label de texto ── */
                                const displayName = el.name || subtype || type || '';
                                const showLabel = type !== 'sistema' && !(type === 'puerta' && !el.name);
                                if (showLabel && displayName) {
                                    const lc = type === 'calle' ? '#374151' : (type === 'hardware' ? '#1e40af' : '#1e293b');
                                    const fs = type === 'calle' ? 3.5 : 2.8;
                                    const ty = type === 'hardware' ? ey - 2 : (type === 'calle' ? ecy + fs * 0.38 : ey + 4.5);
                                    const lbl = displayName.substring(0, 22).toUpperCase();
                                    L.push(`  <text x="${f(ecx)}" y="${f(ty)}"` +
                                        ` text-anchor="middle" fill="${lc}"` +
                                        ` font-family="Arial,Helvetica,sans-serif"` +
                                        ` font-size="${fs}" font-weight="bold"${xfrm}>${esc(lbl)}</text>`);
                                }

                                /* ── Badge FÍS / FUNC ── */
                                if (type === 'ambiente' &&
                                    (subtype === 'consultorio_fisico' || subtype === 'consultorio_funcional' || subtype === 'consultorio')) {
                                    const isFun = subtype === 'consultorio_funcional';
                                    const bc = isFun ? '#d97706' : '#16a34a';
                                    const bl = isFun ? 'FUNC' : 'FÍS';
                                    const bw = 7, bh = 3.2;
                                    const bx = ex + ew - bw - 1, by = ey + 1;
                                    L.push(`  <rect x="${f(bx)}" y="${f(by)}" width="${bw}" height="${bh}"` +
                                        ` rx="1" fill="${bc}"${xfrm}/>`);
                                    L.push(`  <text x="${f(bx + bw / 2)}" y="${f(by + 2.2)}"` +
                                        ` text-anchor="middle" fill="white"` +
                                        ` font-family="Arial,Helvetica,sans-serif"` +
                                        ` font-size="1.9" font-weight="bold"${xfrm}>${bl}</text>`);
                                }
                            }); /* /floorEls.forEach */

                            /* Watermark de piso */
                            L.push(`  <text x="${f(ox + floorW - 2)}" y="${f(oy + FH + drawH - 1)}"` +
                                ` text-anchor="end" fill="#eff0f2"` +
                                ` font-family="Arial,Helvetica,sans-serif"` +
                                ` font-size="12" font-weight="900">P${piso}</text>`);
                        } /* /for piso */

                        /* ── Bloque de título ── */
                        const tbY = PH - M - TB;
                        const tbW = PW - 2 * M;
                        const now = new Date();
                        const dateStr = now.toLocaleDateString('es-PE');
                        const estabName = @json($nombreEstab);
                        const actaNum = @json($acta->numero_acta);
                        const nPisos = this.totalPisos;

                        L.push(`  <rect x="${M}" y="${tbY}" width="${tbW}" height="${TB}" fill="none" stroke="#1e293b" stroke-width="0.5"/>`);
                        /* Bloque izquierdo azul */
                        L.push(`  <rect x="${M}" y="${tbY}" width="50" height="${TB}" fill="#4f46e5"/>`);
                        L.push(`  <text x="${M + 25}" y="${tbY + TB * 0.38}" text-anchor="middle"` +
                            ` fill="white" font-family="Arial,Helvetica,sans-serif" font-size="7.5" font-weight="900">ICATEC</text>`);
                        L.push(`  <text x="${M + 25}" y="${tbY + TB * 0.68}" text-anchor="middle"` +
                            ` fill="#a5b4fc" font-family="Arial,Helvetica,sans-serif" font-size="2.8">ACTA ${esc(actaNum)}</text>`);
                        L.push(`  <line x1="${M + 50}" y1="${tbY}" x2="${M + 50}" y2="${tbY + TB}" stroke="#334155" stroke-width="0.35"/>`);

                        /* Info principal */
                        L.push(`  <text x="${M + 53}" y="${tbY + 7}" fill="#94a3b8"` +
                            ` font-family="Arial,Helvetica,sans-serif" font-size="2.5">ESTABLECIMIENTO DE SALUD</text>`);
                        L.push(`  <text x="${M + 53}" y="${tbY + 15}" fill="#1e293b"` +
                            ` font-family="Arial,Helvetica,sans-serif" font-size="5.5" font-weight="bold">${esc(String(estabName).substring(0, 55))}</text>`);
                        L.push(`  <text x="${M + 53}" y="${tbY + 22}" fill="#475569"` +
                            ` font-family="Arial,Helvetica,sans-serif" font-size="2.8">CROQUIS DE INFRAESTRUCTURA Y DISTRIBUCIÓN DE AMBIENTES — PLANO TÉCNICO</text>`);

                        /* Separador horizontal */
                        L.push(`  <line x1="${M}" y1="${tbY + TB / 2}" x2="${PW - M}" y2="${tbY + TB / 2}" stroke="#e2e8f0" stroke-width="0.3"/>`);

                        /* Celdas de metadatos (derecha) */
                        const META = [
                            ['FORMATO', 'A1 HORIZONTAL'],
                            ['ESCALA', 'N.T.S.'],
                            ['PISOS', String(nPisos)],
                            ['FECHA', dateStr],
                        ];
                        const metaCW = 42;
                        META.forEach((col, i) => {
                            const cx2 = PW - M - metaCW * (META.length - i);
                            L.push(`  <line x1="${cx2}" y1="${tbY}" x2="${cx2}" y2="${tbY + TB}" stroke="#334155" stroke-width="0.3"/>`);
                            L.push(`  <text x="${cx2 + metaCW / 2}" y="${tbY + 8}" text-anchor="middle" fill="#94a3b8"` +
                                ` font-family="Arial,Helvetica,sans-serif" font-size="2.4">${col[0]}</text>`);
                            L.push(`  <text x="${cx2 + metaCW / 2}" y="${tbY + 18}" text-anchor="middle" fill="#1e293b"` +
                                ` font-family="Arial,Helvetica,sans-serif" font-size="5" font-weight="bold">${esc(col[1])}</text>`);
                        });

                        L.push(`</svg>`);

                        /* ── Descargar ── */
                        const svgStr = L.join('\n');
                        const blob = new Blob([svgStr], { type: 'image/svg+xml;charset=utf-8' });
                        const url = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = `Plano_A1_Acta_${String(actaNum).replace(/[\/\\]/g, '_')}.svg`;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        URL.revokeObjectURL(url);

                        Swal.fire({
                            target: document.getElementById('tablet-editor-container'),
                            title: '¡Plano A1 exportado!',
                            html: `<div style="text-align:left;font-size:13px;line-height:1.6">
                                                                            <p>✅ Archivo <strong>SVG vectorial A1 horizontal</strong> descargado.</p>
                                                                            <p style="margin-top:8px;color:#64748b;font-size:12px;">
                                                                                Ábrelo en <strong>Inkscape</strong> (gratis), <strong>Adobe Illustrator</strong>
                                                                                o imprímelo directamente en tu <strong>plotter A1</strong>.<br>
                                                                                El SVG es totalmente vectorial: sin pixelado a cualquier escala.
                                                                            </p>
                                                                        </div>`,
                            icon: 'success',
                            confirmButtonColor: '#4f46e5',
                        });
                    },


                    /* ─── Drag & Drop desde el panel lateral ─── */
                    handleDrop(e) {
                        e.preventDefault();
                        const data = e.dataTransfer.getData('text/plain');
                        if (!data) return;
                        const [type, subtype] = data.split('|');
                        if (!type) return;

                        /* Apply the subtype to the matching reactive state before adding */
                        if (subtype) {
                            if (type === 'ambiente') this.roomSubtype = subtype;
                            if (type === 'hardware') this.hwType = subtype;
                            if (type === 'puerta') this.doorSubtype = subtype;
                            if (type === 'calle') this.calleSubtype = subtype;
                            if (type === 'sistema') this.sistemaType = subtype;
                        }
                        this.tool = type;

                        /* Calculate logical canvas coordinates from mouse position */
                        const rect = canvas.getBoundingClientRect();
                        const x = e.clientX - rect.left;
                        const y = e.clientY - rect.top;

                        this.addElement(type, x, y);
                    },

                    /* ─── Sidebar Pointer-Drag ─── */
                    startSidebarDrag(type, subtype, e) {
                        /* Don't prevent default — click still works if mouse never moves */
                        this._sbDrag = { type, subtype, startX: e.clientX, startY: e.clientY, isDragging: false };
                        const labels = { ambiente: 'Ambiente', hardware: 'Equipo TI', puerta: 'Puerta', calle: 'Calle', sistema: 'Sistema' };
                        const subs = { router: 'Router', ap: 'AP', switch: 'Switch', pozo: 'Pozo', punto_red: 'Punto de Red', tua: 'TUA', sihce: 'SIHCE', sismed: 'SISMED', hisminsa: 'HISMINSA', sisgalenplus: 'SIS GalenPlus', consultorio_fisico: 'C. Físico', consultorio_funcional: 'C. Funcional', consultorio: 'Consultorio', emergencias: 'Emergencias', quirofano: 'Quirófano', administracion: 'Adm.', baño: 'Baño', interna: 'Interna', externa: 'Externa', avenida: 'Avenida', jiron: 'Jirón', pasaje: 'Pasaje' };
                        this._phantomLabel = (labels[type] || type) + (subtype ? ': ' + (subs[subtype] || subtype) : '');
                    },

                    _onWindowPointerMove(e) {
                        if (!this._sbDrag) return;
                        const dx = e.clientX - this._sbDrag.startX;
                        const dy = e.clientY - this._sbDrag.startY;
                        /* Start drag ghost after 8px movement threshold */
                        if (!this._sbDrag.isDragging && Math.sqrt(dx * dx + dy * dy) > 8) {
                            this._sbDrag.isDragging = true;
                        }
                        if (this._sbDrag.isDragging) {
                            this._phantomVisible = true;
                            this._phantomX = e.clientX;
                            this._phantomY = e.clientY;
                        }
                    },

                    _onWindowPointerUp(e) {
                        if (!this._sbDrag) return;
                        const { type, subtype, isDragging } = this._sbDrag;
                        this._sbDrag = null;
                        this._phantomVisible = false;

                        /* If it wasn't actually dragged, let the click event handle it */
                        if (!isDragging) return;

                        /* Check if the pointer is over the canvas */
                        if (!canvas) return;
                        const rect = canvas.getBoundingClientRect();
                        if (e.clientX < rect.left || e.clientX > rect.right ||
                            e.clientY < rect.top || e.clientY > rect.bottom) return;

                        /* Apply subtype and create element at drop position */
                        if (subtype) {
                            if (type === 'ambiente') this.roomSubtype = subtype;
                            if (type === 'hardware') this.hwType = subtype;
                            if (type === 'puerta') this.doorSubtype = subtype;
                            if (type === 'calle') this.calleSubtype = subtype;
                            if (type === 'sistema') this.sistemaType = subtype;
                        }
                        this.tool = type;
                        const { x, y } = this._screenToCanvas(e.clientX - rect.left, e.clientY - rect.top);
                        this.addElement(type, x, y);
                    },

                    /* ─── Micro-ajuste del Mapa ─── */
                    moveMap(dx, dy) {
                        this.mapOffsetX += dx;
                        this.mapOffsetY += dy;
                        this.draw();
                    },
                    resetMapOffset() {
                        this.mapOffsetX = 0;
                        this.mapOffsetY = 0;
                        this.draw();
                    },

                    /* ─── Fetch AJAX de modulos ─── */
                    async fetchAndSyncModulos() {
                        const btn = document.getElementById('btn-sync');
                        try {
                            if (btn) btn.classList.add('opacity-50', 'pointer-events-none');
                            
                            const res = await fetch(`{{ route('usuario.monitoreo.infraestructura-2d.sync-data', $acta->id) }}`, {
                                cache: 'no-cache'
                            });
                            if (!res.ok) throw new Error('Error al sincronizar');
                            
                            const data = await res.json();
                            this.modulosData = data.modulos || [];

                            // Datos del acta a nivel de establecimiento: se refrescan aquí
                            // para que el botón funcione con los cambios recién guardados
                            // en el acta (por ejemplo, panel solar o pozo a tierra) sin
                            // tener que recargar la página.
                            this.pozoTierra = data.pozo_tierra ?? 'NO';
                            this.pozoTierraCantidad = data.pozo_tierra_cantidad ?? 0;
                            this.pozoTierraOperativos = data.pozo_tierra_operativos ?? 0;
                            this.pozoTierraInoperativos = data.pozo_tierra_inoperativos ?? 0;
                            this.panelSolar = data.panel_solar ?? 'NO';
                            this.panelSolarCantidad = data.panel_solar_cantidad ?? 0;
                            this.panelSolarOperativos = data.panel_solar_operativos ?? 0;
                            this.panelSolarInoperativos = data.panel_solar_inoperativos ?? 0;

                            if (btn) btn.classList.remove('opacity-50', 'pointer-events-none');

                            if (!this.modulosData || this.modulosData.length === 0) {
                                Swal.fire({ title: 'Sin datos de módulos', text: 'No hay módulos registrados en la base de datos.', icon: 'info' });
                                return;
                            }

                            const activos = this.modulosData.filter(m => m.activo);
                            if (!activos.length) {
                                Swal.fire({
                                    title: 'Sin servicios activos',
                                    text: 'Este establecimiento no tiene módulos activos, así que no hay servicios que dibujar en el croquis.',
                                    icon: 'info', confirmButtonColor: '#4f46e5'
                                });
                                return;
                            }
                            const totalEq = activos.reduce((s, m) => s + (m.total_equipos || 0), 0);
                            const sinEq = activos.filter(m => (m.equipos || []).length === 0).length;

                            const result = await Swal.fire({
                                title: '⚡ Sincronizar desde Módulos',
                                html: `<p class='text-sm text-slate-600 mb-1'><strong>${activos.length}</strong> servicio(s) activo(s) con <strong>${totalEq}</strong> equipo(s) registrado(s).</p>
                                    ${sinEq ? `<p class='text-[11px] text-slate-400 mb-3'>${sinEq} sin equipos: se dibujará la sala vacía.</p>` : `<div class='mb-3'></div>`}
                                    <div class='flex flex-col gap-3'>
                                        <label class='flex items-start gap-3 cursor-pointer p-3 border-2 border-indigo-200 rounded-xl hover:bg-indigo-50 transition-all'>
                                        <input type='radio' name='sync_mode' value='agregar' checked class='mt-0.5 accent-indigo-600'>
                                        <div class='text-left'><p class='font-bold text-xs text-slate-800'>Agregar a lo existente</p><p class='text-[10px] text-slate-500'>Solo añade equipos nuevos detectados y consultorios faltantes.</p></div>
                                        </label>
                                        <label class='flex items-start gap-3 cursor-pointer p-3 border-2 border-rose-200 rounded-xl hover:bg-rose-50 transition-all'>
                                        <input type='radio' name='sync_mode' value='limpiar' class='mt-0.5 accent-rose-600'>
                                        <div class='text-left'><p class='font-bold text-xs text-slate-800'>Limpiar y reemplazar</p><p class='text-[10px] text-slate-500'>Borra TODO el croquis actual y lo genera desde cero.</p></div>
                                        </label>
                                    </div>`,
                                showCancelButton: true,
                                confirmButtonText: '⚡ Sincronizar',
                                cancelButtonText: 'Cancelar',
                                confirmButtonColor: '#4f46e5',
                                cancelButtonColor: '#94a3b8',
                                customClass: { popup: 'rounded-[2rem]' },
                                preConfirm: () => document.querySelector('input[name=sync_mode]:checked')?.value || 'agregar'
                            });

                            if (result.isConfirmed) {
                                this.prepopularModulos(result.value === 'limpiar');
                            }
                        } catch (e) {
                            console.error(e);
                            if (btn) btn.classList.remove('opacity-50', 'pointer-events-none');
                            Swal.fire({title: 'Error', text: 'No se pudieron actualizar los datos del servidor.', icon: 'error'});
                        }
                    },

                    /* ─── Volcar los módulos del acta al croquis ───
                       Cada servicio con equipos registrados se dibuja como un ambiente
                       con sus equipos dentro. Los servicios sin equipos no se dibujan. */

                    /* Tipo de ambiente según el servicio, para que el plano se lea por color.
                       Si el consultorio respondió la pregunta "Físico o Funcional" en su ficha,
                       eso manda: es dato real del establecimiento. Los módulos fijos (citas,
                       urgencias, farmacia...) no traen esa pregunta, así que para ellos se
                       sigue infiriendo el tipo de ambiente a partir del nombre del servicio. */
                    _ambienteDeServicio(slug, tipoConsultorio) {
                        if (tipoConsultorio === 'FUNCIONAL') return 'consultorio_funcional';
                        if (tipoConsultorio === 'FISICO') return 'consultorio_fisico';

                        const s = (slug || '').toLowerCase();
                        if (s.includes('urgencia') || s.includes('emergencia')) return 'emergencias';
                        if (s.includes('parto') || s.includes('quirofano') || s.includes('quirófano')) return 'quirofano';
                        if (s.includes('cita') || s.includes('admin') || s.includes('fua') ||
                            s.includes('referencia') || s.includes('farmacia')) return 'administracion';
                        return 'consultorio_fisico';
                    },

                    prepopularModulos(modoLimpiar = false) {
                        /* Se dibuja todo servicio activo del establecimiento; los equipos
                           solo se añaden a los que tengan alguno registrado. */
                        const servicios = (this.modulosData || []).filter(m => m.activo);

                        if (!servicios.length) {
                            Swal.fire({
                                title: 'Sin servicios activos',
                                text: 'Este establecimiento no tiene módulos activos, así que no hay servicios que dibujar en el croquis.',
                                icon: 'info', confirmButtonColor: '#4f46e5'
                            });
                            return;
                        }

                        /* ── Medidas: el ambiente se dimensiona según cuántos equipos entran.
                              Cada celda de equipo lleva su icono y, debajo, su nombre. ── */
                        const HWW = 68, HWH = 66, HWGAP = 8, PADX = 16;
                        const HEAD = 42;   // franja del rótulo
                        const FOOT = 34;   // franja de los indicadores wifi/luz/red
                        const COLS_MAX = 4;

                        const layouts = servicios.map(m => {
                            const n = (m.equipos || []).length;
                            const cols = Math.min(COLS_MAX, Math.max(2, n));
                            return { m, cols, rows: Math.ceil(n / cols) };
                        });

                        /* Todas las salas comparten tamaño: el plano queda alineado */
                        const maxCols = Math.max(...layouts.map(l => l.cols));
                        const maxRows = Math.max(...layouts.map(l => l.rows));
                        const RW = Math.max(240, PADX * 2 + maxCols * HWW + (maxCols - 1) * HWGAP);
                        const RH = Math.max(190, HEAD + maxRows * HWH + (maxRows - 1) * HWGAP + FOOT);
                        const GAP = 34;
                        const STARTX = 60, STARTY = 60;
                        const COLS_SALA = Math.max(1, Math.min(4, Math.ceil(Math.sqrt(layouts.length))));

                        this._snapshot();
                        if (modoLimpiar) {
                            /* Los elementos borrados se notifican a los demás editores */
                            this.elements.forEach(e => {
                                if (!this.deletedIds.includes(e.id)) this.deletedIds.push(e.id);
                            });
                            this.elements = [];
                            this.connections = [];
                            this.selectedId = null;
                        }

                        const rid = () => Math.random().toString(36).slice(2, 7);
                        const now = () => Date.now();
                        let salasNuevas = 0, equiposNuevos = 0, salasActualizadas = 0;

                        /* ── Piso de cada servicio: lo declara la propia ficha del
                              consultorio dinámico ("¿Qué piso es?"); los módulos fijos,
                              que no preguntan eso, se asumen en el piso 1. Cada piso
                              lleva su propia cuenta de posición, para que la rejilla de
                              salas se vea ordenada dentro de cada planta y no dependa
                              de en qué piso esté parado el usuario al sincronizar. ── */
                        const pisoDe = (m) => Math.max(1, parseInt(m.piso, 10) || 1);
                        const maxPisoServicios = Math.max(1, ...layouts.map(({ m }) => pisoDe(m)));
                        if (maxPisoServicios > this.totalPisos) this.totalPisos = maxPisoServicios;
                        const idxPorPiso = {};

                        layouts.forEach(({ m, cols }) => {
                            const label = m.label;
                            const labelUp = label.toUpperCase();
                            const pisoDestino = pisoDe(m);

                            /* ¿La sala ya está en el croquis? */
                            let sala = modoLimpiar ? null : this.elements.find(
                                e => e.type === 'ambiente' && (e.name || '').toUpperCase() === labelUp
                            );

                            const idx = idxPorPiso[pisoDestino] || 0;

                            if (!sala) {
                                const col = idx % COLS_SALA;
                                const row = Math.floor(idx / COLS_SALA);
                                sala = {
                                    id: 'mod_' + m.slug + '_' + now() + '_' + rid(),
                                    type: 'ambiente',
                                    subtype: this._ambienteDeServicio(m.slug, m.tipo_consultorio),
                                    x: STARTX + col * (RW + GAP),
                                    y: STARTY + row * (RH + GAP),
                                    w: RW, h: RH,
                                    name: label,
                                    rot: 0,
                                    attrs: {
                                        wifi: m.tipo_conectividad === 'WIFI',
                                        light: true,
                                        red: m.tipo_conectividad === 'CABLEADO' ? 1 : 0,
                                    },
                                    piso: pisoDestino,
                                    _ts: now(),
                                    _synced: true,
                                    _slug: m.slug,
                                };
                                this.elements.push(sala);
                                salasNuevas++;
                            } else {
                                salasActualizadas++;
                                /* La sala existente debe poder acoger la rejilla de equipos */
                                if (sala.w < RW) sala.w = RW;
                                if (sala.h < RH) sala.h = RH;
                                /* Si cambiaron Físico ↔ Funcional en la ficha, la sala ya
                                   dibujada se repinta para reflejarlo (solo entre esos dos:
                                   nunca le pisa un tipo especial como emergencias/quirófano). */
                                const nuevoTipo = this._ambienteDeServicio(m.slug, m.tipo_consultorio);
                                if ((sala.subtype === 'consultorio_fisico' || sala.subtype === 'consultorio_funcional') &&
                                    (nuevoTipo === 'consultorio_fisico' || nuevoTipo === 'consultorio_funcional')) {
                                    sala.subtype = nuevoTipo;
                                }
                                /* Si en la ficha cambiaron el piso, la sala se traslada a la
                                   planta correcta (conserva su posición dentro de la nueva). */
                                if (sala.piso !== pisoDestino) sala.piso = pisoDestino;
                            }
                            idxPorPiso[pisoDestino] = idx + 1;

                            /* ── Equipos del servicio, en rejilla bajo el rótulo.
                                  Un servicio activo sin equipos se queda como sala vacía. ── */
                            const gridW = cols * HWW + (cols - 1) * HWGAP;
                            const originX = sala.x + (sala.w - gridW) / 2;
                            const originY = sala.y + HEAD;

                            (m.equipos || []).forEach((eq, i) => {
                                const c = i % cols;
                                const r = Math.floor(i / cols);

                                /* En modo agregar no se duplica un equipo ya representado */
                                const yaEsta = !modoLimpiar && this.elements.some(e =>
                                    e.type === 'hardware' &&
                                    e.parentId === sala.id &&
                                    e.subtype === eq.tipo &&
                                    String(e.estado || '') === String(eq.estado || '')
                                );
                                if (yaEsta) return;

                                this.elements.push({
                                    id: 'hw_' + m.slug + '_' + eq.tipo + '_' + rid(),
                                    type: 'hardware',
                                    subtype: eq.tipo,
                                    parentId: sala.id,
                                    x: originX + c * (HWW + HWGAP),
                                    y: originY + r * (HWH + HWGAP),
                                    w: HWW, h: HWH,
                                    name: eq.descripcion || eq.tipo.toUpperCase(),
                                    rot: 0,
                                    estado: eq.estado,
                                    cantidad: eq.cantidad,
                                    piso: this.currentPiso,
                                    _ts: now(),
                                    _synced: true,
                                });
                                equiposNuevos++;
                            });

                            /* ── Sistema SIHCE, si el módulo declara que lo usa ── */
                            if (m.utiliza_sihce === 'SI') {
                                const tieneSihce = this.elements.some(
                                    e => e.parentId === sala.id && e.type === 'sistema' && e.subtype === 'sihce'
                                );
                                if (!tieneSihce) {
                                    this.elements.push({
                                        id: 'sis_' + m.slug + '_sihce_' + rid(),
                                        type: 'sistema', subtype: 'sihce',
                                        parentId: sala.id,
                                        x: sala.x + 10, y: sala.y + sala.h - 30,
                                        w: 66, h: 24,
                                        name: 'SIHCE',
                                        rot: 0,
                                        piso: this.currentPiso,
                                        _ts: now(),
                                        _synced: true,
                                    });
                                }
                            }
                        });

                        /* Pozo a tierra y panel solar son datos del acta completa, no de un
                           consultorio en particular: se colocan en el piso actualmente
                           abierto en el editor, siguiendo la rejilla de ese piso donde haya
                           quedado la de los consultorios que ya se ubicaron ahí. */
                        let idx = idxPorPiso[this.currentPiso] || 0;

                        /* ── Pozo a tierra: dato del acta completa, no de un consultorio en
                              particular, así que va en su propia sala de apoyo. Si el acta
                              marcó "NO" o no tiene ninguno, no se dibuja nada. ── */
                        if (this.pozoTierra === 'SI' && this.pozoTierraCantidad > 0) {
                            const POZO_LABEL = 'INFRAESTRUCTURA ELÉCTRICA';
                            let salaPozo = modoLimpiar ? null : this.elements.find(
                                e => e.type === 'ambiente' && (e.name || '').toUpperCase() === POZO_LABEL
                            );

                            const totalPozos = Math.max(1, this.pozoTierraOperativos + this.pozoTierraInoperativos);
                            const colsPozo = Math.min(COLS_MAX, Math.max(2, totalPozos));
                            const rowsPozo = Math.ceil(totalPozos / colsPozo);
                            const RWP = Math.max(240, PADX * 2 + colsPozo * HWW + (colsPozo - 1) * HWGAP);
                            const RHP = Math.max(190, HEAD + rowsPozo * HWH + (rowsPozo - 1) * HWGAP + FOOT);

                            if (!salaPozo) {
                                const col = idx % COLS_SALA;
                                const row = Math.floor(idx / COLS_SALA);
                                salaPozo = {
                                    id: 'mod_pozo_tierra_' + now() + '_' + rid(),
                                    type: 'ambiente',
                                    subtype: 'administracion',
                                    x: STARTX + col * (RW + GAP),
                                    y: STARTY + row * (RH + GAP),
                                    w: RWP, h: RHP,
                                    name: POZO_LABEL,
                                    rot: 0,
                                    attrs: { wifi: false, light: true, red: 0 },
                                    piso: this.currentPiso,
                                    _ts: now(),
                                    _synced: true,
                                    _slug: 'pozo_tierra',
                                };
                                this.elements.push(salaPozo);
                                salasNuevas++;
                                idx++;
                            } else {
                                salasActualizadas++;
                                if (salaPozo.w < RWP) salaPozo.w = RWP;
                                if (salaPozo.h < RHP) salaPozo.h = RHP;
                            }

                            /* En modo agregar, solo se completan los pozos que falten por
                               cada estado: si ya había 2 operativos dibujados y ahora la
                               ficha dice 3, se agrega uno solo, no se duplican los 2 previos. */
                            const yaOperativos = modoLimpiar ? 0 : this.elements.filter(e =>
                                e.type === 'hardware' && e.parentId === salaPozo.id && e.subtype === 'pozo' &&
                                (e.estado || 'OPERATIVO') === 'OPERATIVO'
                            ).length;
                            const yaInoperativos = modoLimpiar ? 0 : this.elements.filter(e =>
                                e.type === 'hardware' && e.parentId === salaPozo.id && e.subtype === 'pozo' &&
                                e.estado === 'INOPERATIVO'
                            ).length;

                            const faltanOperativos = Math.max(0, this.pozoTierraOperativos - yaOperativos);
                            const faltanInoperativos = Math.max(0, this.pozoTierraInoperativos - yaInoperativos);

                            const gridWP = colsPozo * HWW + (colsPozo - 1) * HWGAP;
                            const originXP = salaPozo.x + (salaPozo.w - gridWP) / 2;
                            const originYP = salaPozo.y + HEAD;
                            let posPozo = yaOperativos + yaInoperativos;

                            const colocarPozo = (estado) => {
                                const c = posPozo % colsPozo;
                                const r = Math.floor(posPozo / colsPozo);
                                this.elements.push({
                                    id: 'hw_pozo_tierra_' + rid(),
                                    type: 'hardware',
                                    subtype: 'pozo',
                                    parentId: salaPozo.id,
                                    x: originXP + c * (HWW + HWGAP),
                                    y: originYP + r * (HWH + HWGAP),
                                    w: HWW, h: HWH,
                                    name: 'POZO TIERRA',
                                    rot: 0,
                                    estado,
                                    piso: this.currentPiso,
                                    _ts: now(),
                                    _synced: true,
                                });
                                posPozo++;
                                equiposNuevos++;
                            };

                            for (let i = 0; i < faltanOperativos; i++) colocarPozo('OPERATIVO');
                            for (let i = 0; i < faltanInoperativos; i++) colocarPozo('INOPERATIVO');
                        }

                        /* ── Panel solar: mismo tratamiento que el pozo a tierra, dato del
                              acta completa en su propia sala de apoyo. Si el acta marcó "NO"
                              o no tiene ninguno, no se dibuja nada. ── */
                        if (this.panelSolar === 'SI' && this.panelSolarCantidad > 0) {
                            const PANEL_LABEL = 'PANEL SOLAR';
                            let salaPanel = modoLimpiar ? null : this.elements.find(
                                e => e.type === 'ambiente' && (e.name || '').toUpperCase() === PANEL_LABEL
                            );

                            const totalPaneles = Math.max(1, this.panelSolarOperativos + this.panelSolarInoperativos);
                            const colsPanel = Math.min(COLS_MAX, Math.max(2, totalPaneles));
                            const rowsPanel = Math.ceil(totalPaneles / colsPanel);
                            const RWS = Math.max(240, PADX * 2 + colsPanel * HWW + (colsPanel - 1) * HWGAP);
                            const RHS = Math.max(190, HEAD + rowsPanel * HWH + (rowsPanel - 1) * HWGAP + FOOT);

                            if (!salaPanel) {
                                const col = idx % COLS_SALA;
                                const row = Math.floor(idx / COLS_SALA);
                                salaPanel = {
                                    id: 'mod_panel_solar_' + now() + '_' + rid(),
                                    type: 'ambiente',
                                    subtype: 'administracion',
                                    x: STARTX + col * (RW + GAP),
                                    y: STARTY + row * (RH + GAP),
                                    w: RWS, h: RHS,
                                    name: PANEL_LABEL,
                                    rot: 0,
                                    attrs: { wifi: false, light: true, red: 0 },
                                    piso: this.currentPiso,
                                    _ts: now(),
                                    _synced: true,
                                    _slug: 'panel_solar',
                                };
                                this.elements.push(salaPanel);
                                salasNuevas++;
                                idx++;
                            } else {
                                salasActualizadas++;
                                if (salaPanel.w < RWS) salaPanel.w = RWS;
                                if (salaPanel.h < RHS) salaPanel.h = RHS;
                            }

                            /* En modo agregar, solo se completan los paneles que falten por
                               cada estado, igual que con los pozos a tierra. */
                            const yaOperativosP = modoLimpiar ? 0 : this.elements.filter(e =>
                                e.type === 'hardware' && e.parentId === salaPanel.id && e.subtype === 'panel_solar' &&
                                (e.estado || 'OPERATIVO') === 'OPERATIVO'
                            ).length;
                            const yaInoperativosP = modoLimpiar ? 0 : this.elements.filter(e =>
                                e.type === 'hardware' && e.parentId === salaPanel.id && e.subtype === 'panel_solar' &&
                                e.estado === 'INOPERATIVO'
                            ).length;

                            const faltanOperativosP = Math.max(0, this.panelSolarOperativos - yaOperativosP);
                            const faltanInoperativosP = Math.max(0, this.panelSolarInoperativos - yaInoperativosP);

                            const gridWS = colsPanel * HWW + (colsPanel - 1) * HWGAP;
                            const originXS = salaPanel.x + (salaPanel.w - gridWS) / 2;
                            const originYS = salaPanel.y + HEAD;
                            let posPanel = yaOperativosP + yaInoperativosP;

                            const colocarPanel = (estado) => {
                                const c = posPanel % colsPanel;
                                const r = Math.floor(posPanel / colsPanel);
                                this.elements.push({
                                    id: 'hw_panel_solar_' + rid(),
                                    type: 'hardware',
                                    subtype: 'panel_solar',
                                    parentId: salaPanel.id,
                                    x: originXS + c * (HWW + HWGAP),
                                    y: originYS + r * (HWH + HWGAP),
                                    w: HWW, h: HWH,
                                    name: 'PANEL SOLAR',
                                    rot: 0,
                                    estado,
                                    piso: this.currentPiso,
                                    _ts: now(),
                                    _synced: true,
                                });
                                posPanel++;
                                equiposNuevos++;
                            };

                            for (let i = 0; i < faltanOperativosP; i++) colocarPanel('OPERATIVO');
                            for (let i = 0; i < faltanInoperativosP; i++) colocarPanel('INOPERATIVO');
                        }

                        this.draw();
                        this._refreshIcons();

                        const sinEquipos = servicios.filter(m => (m.equipos || []).length === 0).length;
                        const inactivos = (this.modulosData || []).filter(m => !m.activo).length;

                        if (!salasNuevas && !equiposNuevos) {
                            Swal.fire({
                                title: 'Todo al día',
                                text: 'Los servicios activos y sus equipos ya están representados en el croquis.',
                                icon: 'info', confirmButtonColor: '#4f46e5'
                            });
                            return;
                        }

                        const totalEquipos = servicios.reduce((s, m) => s + (m.total_equipos || 0), 0);
                        Swal.fire({
                            title: '¡Croquis actualizado!',
                            html: `<div class="text-left text-sm text-slate-600 space-y-1">
                                     <p><strong>${servicios.length}</strong> servicio(s) activo(s) · <strong>${totalEquipos}</strong> equipo(s) en total.</p>
                                     <p>${salasNuevas} sala(s) nueva(s)${salasActualizadas ? ` · ${salasActualizadas} ya existente(s)` : ''} · ${equiposNuevos} equipo(s) colocado(s).</p>
                                     ${sinEquipos ? `<p class="text-xs text-slate-400">${sinEquipos} servicio(s) sin equipos registrados: se dibujó la sala vacía.</p>` : ''}
                                     ${inactivos ? `<p class="text-xs text-slate-400">${inactivos} módulo(s) inactivo(s): no se dibujaron.</p>` : ''}
                                     <p class="text-xs text-slate-400 pt-1">Puedes mover y editar todo. Recuerda guardar el croquis.</p>
                                   </div>`,
                            icon: 'success', confirmButtonColor: '#4f46e5', confirmButtonText: 'Perfecto'
                        });
                    },

                    /* ─── Save ─── */
                    async saveData() {
                        if (this.isSaving) return;
                        this.isSaving = true;

                        try {
                            this._flushDraw();   /* la miniatura guardada debe estar al día */
                            const dataUrl = canvas.toDataURL('image/png');
                            const payload = {
                                contenido: {
                                    elementos: this.elements,
                                    conexiones: this.connections,
                                    totalPisos: this.totalPisos,
                                    mapOffsetX: this.mapOffsetX,
                                    mapOffsetY: this.mapOffsetY,
                                    mapAnchorX: this.mapAnchorX,
                                    mapAnchorY: this.mapAnchorY
                                },
                                croquis_image: dataUrl,
                                _token: '{{ csrf_token() }}'
                            };

                            const res = await fetch("{{ route('usuario.monitoreo.infraestructura-2d.store', $acta->id) }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify(payload)
                            });

                            if (res.ok) {
                                Swal.fire({
                                    target: document.getElementById('tablet-editor-container'),
                                    title: '¡Guardado!',
                                    text: 'El croquis se actualizó correctamente.',
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                                return true;
                            } else {
                                throw new Error('Failed to save');
                            }
                        } catch (e) {
                            console.error(e);
                            Swal.fire({
                                target: document.getElementById('tablet-editor-container'),
                                title: 'Error',
                                text: 'No se pudo guardar la información.',
                                icon: 'error'
                            });
                            return false;
                        } finally {
                            this.isSaving = false;
                        }
                    },

                    /* ═══════════════════════════════════════════════════
                       COLABORACIÓN EN TIEMPO REAL (polling cada 900ms)
                    ═══════════════════════════════════════════════════ */

                    /** Iniciar el ciclo de sincronización */
                    _startColabSync() {
                        /* Sin _syncUrl no hay a quién consultar: se queda en modo un solo usuario. */
                        if (!this._syncUrl) return;

                        if (this._syncInterval) clearInterval(this._syncInterval);
                        /* Primera sincronización inmediata */
                        this._syncState();
                        /* Polling cada 900 ms */
                        this._syncInterval = setInterval(() => this._syncState(), 900);
                    },

                    /** Envía el estado propio y recibe el de los otros — con merge de elementos */
                    async _syncState() {
                        try {
                            const body = {
                                cursor_x: this._pendingCursorX,
                                cursor_y: this._pendingCursorY,
                                elements: this.elements,
                                connections: this.connections,
                                deletedIds: this.deletedIds,
                            };
                            const res = await fetch(this._syncUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this._csrfToken,
                                },
                                body: JSON.stringify(body),
                            });
                            if (!res.ok) return;
                            const data = await res.json();
                            if (!data.ok) return;

                            /* Actualizar lista de colaboradores (para cursores) */
                            this.colaboradores = data.colaboradores;

                            /* ── MERGE DE ELEMENTOS POR TIMESTAMP ── */
                            let anyElementChange = false;
                            let authorOfChange = null;
                            let actionName = 'actualizó';
                            let elementName = 'el croquis';

                            for (const colab of this.colaboradores) {

                                /* 1. Aplicar eliminaciones remotas */
                                for (const deletedId of (colab.deletedIds || [])) {
                                    const idx = this.elements.findIndex(e => e.id === deletedId);
                                    if (idx !== -1) {
                                        elementName = this.elements[idx].name || 'un elemento';
                                        this.elements.splice(idx, 1);
                                        /* Limpiar conexiones huérfanas */
                                        this.connections = this.connections.filter(
                                            c => c.from !== deletedId && c.to !== deletedId
                                        );
                                        anyElementChange = true;
                                        authorOfChange = colab.user_name;
                                        actionName = 'eliminó';
                                    }
                                }

                                /* 2. Merge / upsert de elementos remotos */
                                for (const remoteEl of (colab.elements || [])) {
                                    /* Ignorar si el ID está en nuestra lista de borrados locales */
                                    if (this.deletedIds.includes(remoteEl.id)) continue;

                                    const localIdx = this.elements.findIndex(e => e.id === remoteEl.id);
                                    if (localIdx === -1) {
                                        /* Elemento nuevo de otro usuario → agregar */
                                        this.elements.push(remoteEl);
                                        anyElementChange = true;
                                        authorOfChange = colab.user_name;
                                        actionName = 'agregó';
                                        elementName = remoteEl.name || 'un elemento';
                                    } else {
                                        const localEl = this.elements[localIdx];
                                        const remoteTs = remoteEl._ts || 0;
                                        const localTs = localEl._ts || 0;
                                        if (remoteTs > localTs) {
                                            /* Versión remota es más reciente → actualizar */
                                            Object.assign(localEl, remoteEl);
                                            anyElementChange = true;
                                            authorOfChange = colab.user_name;
                                            actionName = 'modificó';
                                            elementName = remoteEl.name || 'un elemento';
                                        }
                                    }
                                }

                                /* 3. Merge de conexiones (cableado): no tienen id propio ni
                                   timestamp, así que solo se agregan las que faltan, sin
                                   duplicar. Las que queden huérfanas (su elemento ya no
                                   existe) se limpian abajo, después del merge completo. */
                                for (const remoteConn of (colab.connections || [])) {
                                    const yaExiste = this.connections.some(
                                        c => c.from === remoteConn.from && c.to === remoteConn.to
                                    );
                                    if (!yaExiste) {
                                        this.connections.push(remoteConn);
                                        anyElementChange = true;
                                    }
                                }
                            }

                            /* Conexiones huérfanas: su elemento fue borrado por otro usuario
                               y ya se aplicó arriba, pero la conexión pudo llegar en el mismo
                               ciclo de sondeo antes de que se descartara. */
                            this.connections = this.connections.filter(c =>
                                this.elements.some(e => e.id === c.from) &&
                                this.elements.some(e => e.id === c.to)
                            );

                            /* Limpiar deletedIds locales que ya fueron confirmados por el servidor
                               (después de un ciclo completo, todos los colaboradores los conocen) */
                            if (this.deletedIds.length > 0 && this.colaboradores.length === 0) {
                                this.deletedIds = [];
                            }

                            if (anyElementChange) {
                                this.draw();
                                if (authorOfChange) this._showColabToast(authorOfChange, actionName, elementName);
                            } else if (this.colaboradores.length > 0) {
                                /* Solo redibujar cursores */
                                this.draw();
                            }

                        } catch (_) {
                            /* Silencioso — no romper el editor si hay problemas de red */
                        }
                    },

                    /** Muestra un toast no intrusivo indicando que un colaborador hizo cambios */
                    _showColabToast(userName, action = 'actualizó', target = 'el croquis') {
                        this._toastMsg = `${userName} ${action} ${target}`;
                        this._toastVisible = true;
                        if (this._toastTimer) clearTimeout(this._toastTimer);
                        this._toastTimer = setTimeout(() => {
                            this._toastVisible = false;
                        }, 3500);
                    },

                    /** Notificar al servidor que el usuario se va */
                    _leaveColab() {
                        if (this._syncInterval) clearInterval(this._syncInterval);
                        /* Sin _leaveUrl, sendBeacon dispararía un POST contra la propia página. */
                        if (!this._leaveUrl) return;
                        /* sendBeacon garantiza que el request sale aunque la página se esté cerrando.
                           Usa FormData para incluir el CSRF token (sendBeacon no admite headers custom). */
                        const fd = new FormData();
                        fd.append('_token', this._csrfToken);
                        navigator.sendBeacon(this._leaveUrl, fd);
                    },

        /** Dibuja los cursores remotos encima del canvas (llamado desde draw()) */
        _drawRemoteCursors() {
            if (!ctx || !this.colaboradores || this.colaboradores.length === 0) return;

            const z = this.canvasZoom || 1;
            const lw = this.logicalW;
            const lh = this.logicalH;

            this.colaboradores.forEach(colab => {
                const rawX = colab.cursor_x;
                const rawY = colab.cursor_y;
                if (rawX === 0 && rawY === 0) return; /* Sin posición aún */

                /* Screen-space → logical canvas-space (usar helper de cámara) */
                const { x: cx, y: cy } = this._screenToCanvas(rawX, rawY);

                const color = colab.color || '#ef4444';
                const name = (colab.user_name || '?').substring(0, 20);

                ctx.save();

                /* ── Forma del cursor (flecha SVG clásica) ── */
                ctx.fillStyle = color;
                ctx.strokeStyle = 'white';
                ctx.lineWidth = 1.5;
                ctx.shadowBlur = 8;
                ctx.shadowColor = color + '80';
                ctx.beginPath();
                ctx.moveTo(cx, cy);
                ctx.lineTo(cx, cy + 18);
                ctx.lineTo(cx + 4, cy + 13);
                ctx.lineTo(cx + 9, cy + 20);
                ctx.lineTo(cx + 11, cy + 19);
                ctx.lineTo(cx + 6, cy + 12);
                ctx.lineTo(cx + 12, cy + 12);
                ctx.closePath();
                ctx.fill();
                ctx.stroke();
                ctx.shadowBlur = 0;

                /* ── Etiqueta con nombre ── */
                ctx.font = 'bold 9px Inter, Arial';
                const tw = ctx.measureText(name).width;
                const pw = tw + 10;
                const ph = 14;
                const lx = cx + 13;
                const ly = cy + 2;

                /* Fondo redondeado */
                ctx.fillStyle = color;
                ctx.beginPath();
                ctx.roundRect(lx, ly, pw, ph, 4);
                ctx.fill();

                /* Texto */
                ctx.fillStyle = 'white';
                ctx.textAlign = 'left';
                ctx.textBaseline = 'middle';
                ctx.fillText(name, lx + 5, ly + ph / 2 + 0.5);

                ctx.restore();
            });
        },
                                };
                            });
                        });
    </script>

    <div id="tablet-editor-container" class="h-screen flex flex-col bg-slate-100 overflow-hidden font-sans"
        x-data="tabletEditor" @keydown.ctrl.z.window="undo()" @keydown.ctrl.y.window="redo()"
        @keydown.meta.z.window="undo()" @keydown.delete.window="selectedId && confirmDelete($event)">

        <!-- Ghost fantasma que sigue al cursor durante el drag -->
        <div x-show="_phantomVisible" :style="`left:${_phantomX+14}px;top:${_phantomY+14}px`"
            class="fixed z-[99999] pointer-events-none flex items-center gap-2 bg-indigo-600 text-white text-[9px] font-black uppercase px-3 py-2 rounded-xl shadow-2xl opacity-90 select-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="5 9 2 12 5 15" />
                <polyline points="9 5 12 2 15 5" />
                <polyline points="15 19 12 22 9 19" />
                <polyline points="19 9 22 12 19 15" />
                <line x1="2" y1="12" x2="22" y2="12" />
                <line x1="12" y1="2" x2="12" y2="22" />
            </svg>
            <span x-text="_phantomLabel"></span>
        </div>

        <!-- Toast de Colaboración (cambios de otros usuarios) -->
        <div x-show="_toastVisible" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed bottom-6 left-1/2 -translate-x-1/2 z-[99998] pointer-events-none flex items-center gap-3 bg-slate-900/95 backdrop-blur-sm text-white px-5 py-3 rounded-2xl shadow-2xl border border-white/10 select-none">
            <!-- Icono sync animado -->
            <svg class="w-4 h-4 text-emerald-400 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="text-[11px] font-bold" x-text="_toastMsg"></span>
        </div>

        <!-- Barra Superior -->
        <div x-show="panelVisible" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="-translate-y-full opacity-0"
            class="relative bg-white border-b border-slate-200 px-2 sm:px-4 py-2 sm:py-3 flex flex-col lg:flex-row lg:flex-wrap lg:items-center lg:justify-between gap-2 lg:gap-4 shadow-sm z-50 flex-shrink-0">

            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button @click="toggleSidebar()" title="Mostrar/ocultar herramientas"
                        class="w-9 h-9 sm:w-8 sm:h-8 bg-slate-100 hover:bg-slate-200 rounded-lg flex items-center justify-center text-slate-600 transition-colors">
                        <i :data-lucide="sidebarOpen ? 'panel-left-close' : 'panel-left-open'" class="w-4 h-4"></i>
                    </button>
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg items-center justify-center text-white hidden sm:flex">
                        <i data-lucide="layout" class="w-5 h-5"></i>
                    </div>
                    <h1 class="text-sm font-black text-slate-800 uppercase tracking-tighter hidden xl:block">Gestor de
                        <span class="text-indigo-600">Infraestructura</span>
                    </h1>
                </div>

                <div class="h-6 w-px bg-slate-200 hidden xl:block flex-shrink-0"></div>

                <div class="tool-strip no-scrollbar items-center gap-1 bg-slate-100 p-1 rounded-xl min-w-0 flex-1 lg:flex-none">
                    <button @click="tool = 'ambiente'"
                        :class="tool === 'ambiente' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500'"
                        class="px-2.5 sm:px-3 py-2 sm:py-1.5 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-1.5 sm:gap-2">
                        <i data-lucide="square" class="w-4 h-4"></i> <span class="hidden sm:inline">Ambiente</span>
                    </button>
                    <button @click="tool = 'hardware'"
                        :class="tool === 'hardware' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500'"
                        class="px-2.5 sm:px-4 py-2 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-1.5 sm:gap-2">
                        <i data-lucide="cpu" class="w-4 h-4"></i> <span class="hidden sm:inline">Equipamiento TI</span>
                    </button>
                    <button @click="tool = 'puerta'"
                        :class="tool === 'puerta' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500'"
                        class="px-2.5 sm:px-4 py-2 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-1.5 sm:gap-2">
                        <i data-lucide="door-open" class="w-4 h-4"></i> <span class="hidden sm:inline">Puerta</span>
                    </button>
                    <button @click="tool = 'red'"
                        :class="tool === 'red' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500'"
                        class="px-2.5 sm:px-3 py-2 sm:py-1.5 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-1.5 sm:gap-2">
                        <i data-lucide="share-2" class="w-4 h-4"></i> <span class="hidden sm:inline">Cableado</span>
                    </button>
                    <button @click="tool = 'calle'"
                        :class="tool === 'calle' ? 'bg-white shadow-sm text-emerald-600' : 'text-slate-500'"
                        class="px-2.5 sm:px-3 py-2 sm:py-1.5 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-1.5 sm:gap-2">
                        <i data-lucide="map" class="w-4 h-4"></i> <span class="hidden sm:inline">Calle</span>
                    </button>
                    <button @click="tool = 'sistema'"
                        :class="tool === 'sistema' ? 'bg-white shadow-sm text-violet-600' : 'text-slate-500'"
                        class="px-2.5 sm:px-3 py-2 sm:py-1.5 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-1.5 sm:gap-2">
                        <i data-lucide="monitor" class="w-4 h-4"></i> <span class="hidden sm:inline">Sistemas</span>
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 xl:gap-6 flex-wrap">
                <!-- ── Badge Piso Actual ── -->
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 border border-indigo-200 rounded-xl">
                    <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span class="text-[9px] font-black uppercase text-indigo-400">Piso</span>
                    <span class="text-[13px] font-black text-indigo-700" x-text="currentPiso"></span>
                    <span class="text-[9px] text-indigo-300 font-bold">/</span>
                    <span class="text-[9px] font-bold text-indigo-400" x-text="totalPisos"></span>
                    <!-- Navegación rápida -->
                    <button @click="currentPiso > 1 && goToPiso(currentPiso - 1)" :disabled="currentPiso <= 1"
                        class="w-5 h-5 rounded flex items-center justify-center text-indigo-400 hover:text-indigo-700 hover:bg-indigo-100 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                        title="Piso anterior">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button @click="currentPiso < totalPisos && goToPiso(currentPiso + 1)"
                        :disabled="currentPiso >= totalPisos"
                        class="w-5 h-5 rounded flex items-center justify-center text-indigo-400 hover:text-indigo-700 hover:bg-indigo-100 transition-all disabled:opacity-30 disabled:cursor-not-allowed"
                        title="Piso siguiente">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- ── Badge Colaboradores en Tiempo Real ── -->
                <div x-show="colaboradores.length > 0" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                    class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl"
                    title="Usuarios editando ahora">
                    <!-- Pulso animado verde -->
                    <span class="relative flex h-2.5 w-2.5">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <!-- Contador -->
                    <span class="text-[9px] font-black text-emerald-700 uppercase"
                        x-text="colaboradores.length + ' editando'"></span>
                    <!-- Avatares con colores -->
                    <div class="flex -space-x-1.5">
                        <template x-for="colab in colaboradores.slice(0, 4)" :key="colab.user_id">
                            <div class="w-5 h-5 rounded-full border-2 border-white flex items-center justify-center text-[7px] font-black text-white shadow-sm"
                                :style="`background-color: ${colab.color}`" :title="colab.user_name"
                                x-text="colab.user_name.charAt(0).toUpperCase()">
                            </div>
                        </template>
                        <div x-show="colaboradores.length > 4"
                            class="w-5 h-5 rounded-full border-2 border-white bg-slate-400 flex items-center justify-center text-[7px] font-black text-white shadow-sm"
                            x-text="'+' + (colaboradores.length - 4)">
                        </div>
                    </div>
                </div>

                <!-- ── Sin colaboradores (solo yo) ── -->
                <div x-show="colaboradores.length === 0 && !isMobile"
                    class="flex items-center gap-1.5 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-xl opacity-60"
                    title="Solo tú en este croquis">
                    <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-[8px] font-bold text-slate-400 uppercase">Solo</span>
                </div>

                <!-- Undo / Redo -->
                <div class="flex items-center gap-1">
                    <button @click="undo()" :disabled="!history.length"
                        class="undo-redo-btn w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-lg flex items-center justify-center text-slate-600 transition-colors"
                        title="Deshacer (Ctrl+Z)">
                        <i data-lucide="undo-2" class="w-4 h-4"></i>
                    </button>
                    <button @click="redo()" :disabled="!future.length"
                        class="undo-redo-btn w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-lg flex items-center justify-center text-slate-600 transition-colors"
                        title="Rehacer (Ctrl+Y)">
                        <i data-lucide="redo-2" class="w-4 h-4"></i>
                    </button>
                </div>


                <!-- Capas Toggle -->
                <div id="topbar-filters-wrap" class="relative flex-shrink-0" @click.outside="showFilters = false">
                    <!-- Disparador (solo tablet/móvil) -->
                    <button x-show="isMobile" @click="showFilters = !showFilters; showActions = false"
                        :class="showFilters ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-600'"
                        class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors"
                        title="Filtros de vista">
                        <i data-lucide="layers" class="w-4 h-4"></i>
                    </button>

                    <div id="topbar-filters-menu" x-show="!isMobile || showFilters"
                        class="flex items-center gap-4 px-4 py-2 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[8px] font-bold text-slate-400 uppercase flex items-center gap-1"><i data-lucide="layers" class="w-3 h-3"></i> Filtros de Vista:</span>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" x-model="layers.furniture" @change="draw()" class="rounded text-indigo-600">
                        <span
                            class="text-[8px] font-black uppercase text-slate-500 group-hover:text-indigo-600 transition-colors">Mobiliario</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" x-model="layers.network" @change="draw()" class="rounded text-blue-600">
                        <span
                            class="text-[8px] font-black uppercase text-slate-500 group-hover:text-blue-600 transition-colors">Conexiones</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" x-model="layers.power" @change="draw()" class="rounded text-amber-500">
                        <span
                            class="text-[8px] font-black uppercase text-slate-500 group-hover:text-amber-500 transition-colors">Energía</span>
                    </label>
                    @if($hasCoords)
                        <div class="h-4 w-px bg-slate-200"></div>
                        <label class="flex items-center gap-2 cursor-pointer group"
                            title="Muestra las calles reales del establecimiento como fondo">
                            <input type="checkbox" x-model="layers.calles" @change="draw()" class="rounded text-emerald-600">
                            <span
                                class="text-[8px] font-black uppercase text-emerald-600 group-hover:text-emerald-800 transition-colors">🗺️
                                Calles</span>
                        </label>
                        <div x-show="layers.calles" class="flex items-center gap-3">
                            <div class="flex items-center gap-1.5">
                                <span class="text-[7px] text-slate-400 font-bold uppercase">Opac.</span>
                                <input type="range" min="0.2" max="1" step="0.05" x-model="tileOpacity" @input="draw()"
                                    class="w-14 h-1 accent-emerald-600">
                            </div>
                            <div class="flex items-center gap-1.5 border-l border-slate-200 pl-3">
                                <span class="text-[7px] text-slate-400 font-bold uppercase"
                                    title="Acercar/Alejar mapa base">Zoom</span>
                                <input type="range" min="19" max="23" step="0.1" x-model="tileZoom" @input="draw()"
                                    class="w-16 h-1 accent-emerald-600">
                            </div>
                            <!-- Micro-ajuste del Mapa -->
                            <div class="flex items-center gap-3 border-l border-emerald-100 pl-4 py-1">
                                <div class="flex flex-col items-center">
                                    <span class="text-[7px] text-emerald-600 font-black uppercase mb-2">Micro-ajuste</span>
                                    <div
                                        class="flex flex-col items-center gap-1 p-1.5 bg-emerald-50/50 rounded-2xl border border-emerald-100/50">
                                        <!-- Fila Superior -->
                                        <button @click="moveMap(0, -5)" title="Mover mapa hacia arriba"
                                            class="w-7 h-7 bg-white border border-emerald-200 rounded-xl flex items-center justify-center text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                            <i data-lucide="chevron-up" class="w-4 h-4"></i>
                                        </button>

                                        <!-- Fila Central -->
                                        <div class="flex items-center gap-1">
                                            <button @click="moveMap(-5, 0)" title="Mover mapa hacia la izquierda"
                                                class="w-7 h-7 bg-white border border-emerald-200 rounded-xl flex items-center justify-center text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                            </button>
                                            <button @click="resetMapOffset()" title="Resetear ajuste"
                                                class="w-7 h-7 bg-emerald-600 rounded-xl flex items-center justify-center text-white hover:bg-emerald-700 transition-all shadow-md">
                                                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <button @click="moveMap(5, 0)" title="Mover mapa hacia la derecha"
                                                class="w-7 h-7 bg-white border border-emerald-200 rounded-xl flex items-center justify-center text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                            </button>
                                        </div>

                                        <!-- Fila Inferior -->
                                        <button @click="moveMap(0, 5)" title="Mover mapa hacia abajo"
                                            class="w-7 h-7 bg-white border border-emerald-200 rounded-xl flex items-center justify-center text-emerald-600 hover:bg-emerald-600 hover:text-white transition-all shadow-sm">
                                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    </div><!-- /topbar-filters-menu -->
                </div>

                <div id="topbar-actions-wrap" class="flex items-center gap-2 sm:gap-3 relative"
                    @click.outside="showActions = false">
                    <!-- Disparador del menú de acciones (solo tablet/móvil) -->
                    <button x-show="isMobile" @click="showActions = !showActions; showFilters = false"
                        :class="showActions ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-600'"
                        class="w-9 h-9 rounded-lg flex items-center justify-center transition-colors"
                        title="Más acciones">
                        <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                    </button>

                    <div id="topbar-actions-menu" x-show="!isMobile || showActions"
                        class="flex items-center gap-2 sm:gap-3">
                    <button @click="toggleFullscreen()"
                        :class="isFullscreen ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-600'"
                        class="px-4 py-2 hover:bg-indigo-200 rounded-xl text-[10px] font-black uppercase transition-all flex items-center gap-2"
                        title="Pantalla Completa">
                        <i :data-lucide="isFullscreen ? 'minimize' : 'maximize'" class="w-4 h-4"></i>
                        <span :class="isMobile ? '' : 'hidden xl:inline'"
                            x-text="isFullscreen ? 'Salir' : 'Pantalla Completa'"></span>
                    </button>
                    {{-- ⚡ Botón Sincronizar desde Módulos --}}
                    <button id="btn-sync" @click="fetchAndSyncModulos()"
                        class="relative px-4 py-2 bg-violet-600 text-white rounded-xl text-[10px] font-black uppercase hover:bg-violet-700 transition-all flex items-center gap-2 shadow-lg shadow-violet-200">
                        {{-- El contador refleja los servicios activos del establecimiento --}}
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-amber-400 rounded-full text-[8px] flex items-center justify-center font-black text-slate-900 shadow"
                            x-text="(modulosData || []).filter(m => m.activo).length"></span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span :class="isMobile ? '' : 'hidden xl:inline'">Sync Módulos</span>
                    </button>
                    {{-- Botón Exportar Imagen --}}
                    <button @click="exportImage()" title="Exportar croquis como imagen PNG"
                        class="px-3 xl:px-5 py-2 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase hover:bg-emerald-700 transition-all flex items-center gap-2 shadow-lg shadow-emerald-100">
                        <i data-lucide="image" class="w-4 h-4"></i>
                        <span :class="isMobile ? '' : 'hidden xl:inline'">Exportar PNG</span>
                    </button>
                    {{-- Botón Exportar PDF: guarda el croquis y luego abre el reporte --}}
                    <button @click="exportPdf()" :class="isSaving ? 'btn-saving' : ''"
                        title="Guardar y exportar el reporte a PDF"
                        class="px-3 xl:px-5 py-2 bg-rose-600 text-white rounded-xl text-[10px] font-black uppercase hover:bg-rose-700 transition-all flex items-center gap-2 shadow-lg shadow-rose-100">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span :class="isMobile ? '' : 'hidden xl:inline'">Exportar PDF</span>
                    </button>
                    <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}"
                        class="h-10 px-3 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-xl flex items-center justify-center gap-2 transition-all text-[10px] font-black uppercase"
                        title="Volver al Panel de Módulos">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                        <span :class="isMobile ? '' : 'hidden'">Volver a módulos</span>
                    </a>
                    <button @click="panelVisible = false; showActions = false"
                        class="px-3 h-10 bg-rose-50 hover:bg-rose-100 text-rose-500 text-[10px] uppercase font-black tracking-wide rounded-xl flex items-center justify-center gap-2 transition-all shadow-sm"
                        title="Ocultar Panel Superior">
                        <i data-lucide="chevron-up" class="w-4 h-4"></i>
                        <span :class="isMobile ? '' : 'hidden xl:inline'">Ocultar</span>
                    </button>
                    </div><!-- /topbar-actions-menu -->

                    {{-- Guardar: siempre accesible, en cualquier tamaño de pantalla --}}
                    <button @click="saveData()" :class="isSaving ? 'btn-saving' : ''"
                        class="px-4 sm:px-6 h-10 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase hover:bg-indigo-600 transition-all flex items-center gap-2 shadow-lg shadow-slate-200 flex-shrink-0">
                        <i :data-lucide="isSaving ? 'loader' : 'save'" :class="isSaving ? 'animate-spin' : ''"
                            class="w-4 h-4"></i>
                        <span x-text="isSaving ? 'Guardando…' : 'Guardar'"></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden bg-slate-100 relative">
            <div class="flex-1 bg-[#f1f5f9] overflow-hidden relative flex flex-col p-0" id="canvas-container"
                @click.self="selectedId = null; draw()">

                <!-- Botón Flotante Restaurar -->
                <button x-show="!panelVisible" @click="panelVisible = true"
                    x-transition:enter="transition ease-out duration-500 delay-350"
                    x-transition:enter-start="scale-0 rotate-180" x-transition:enter-end="scale-100 rotate-0"
                    class="absolute top-6 left-6 w-14 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-2xl z-50 hover:bg-indigo-700 hover:scale-110 active:scale-95 transition-all group">
                    <i data-lucide="layout" class="w-6 h-6"></i>
                    <div
                        class="absolute left-full ml-4 px-3 py-1 bg-slate-900 text-white text-[10px] font-black uppercase rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap shadow-xl">
                        Restaurar Herramientas
                    </div>
                </button>

                <!-- Fondo atenuado tras la hoja de herramientas (tablet/móvil) -->
                <div x-show="sidebarOpen && panelVisible && isMobile" x-transition.opacity
                    @click="sidebarOpen = false" class="absolute inset-0 bg-slate-900/30 z-30"></div>

                <!-- Panel de Herramientas · lateral en PC · hoja inferior en tablet y móvil -->
                <div id="tools-sidebar" x-show="sidebarOpen && panelVisible"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                    class="absolute top-4 left-4 w-72 bg-white/95 backdrop-blur-xl border border-white/20 flex flex-col p-5 shadow-2xl z-40 rounded-3xl overflow-y-auto overscroll-contain"
                    :style="panelVisible ? 'max-height: calc(100dvh - 130px)' : 'max-height: calc(100dvh - 40px)'">

                    <!-- Asa y cierre (solo tablet/móvil) -->
                    <div x-show="isMobile" class="relative flex items-center justify-center mb-3 flex-shrink-0">
                        <div class="h-1.5 w-12 bg-slate-200 rounded-full"></div>
                        <button @click="sidebarOpen = false" title="Cerrar herramientas"
                            class="absolute right-0 top-1/2 -translate-y-1/2 w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center hover:bg-slate-200 transition-colors">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="flex flex-col gap-6" id="tools-content">

                        <template x-if="tool === 'ambiente'">
                            <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100">
                                <h2
                                    class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <i data-lucide="plus-circle" class="w-3 h-3"></i> Nuevo Ambiente
                                </h2>
                                <div class="space-y-4">
                                    <select x-model="roomSubtype"
                                        class="w-full bg-white border-none rounded-xl px-4 py-3 text-xs font-bold text-slate-700 shadow-sm focus:ring-2 focus:ring-indigo-500 transition-all">
                                        <option value="consultorio_fisico">🏥 CONSULTORIO FÍSICO</option>
                                        <option value="consultorio_funcional">🔄 CONSULTORIO FUNCIONAL</option>
                                        <option value="quirofano">🔪 QUIRÓFANO</option>
                                        <option value="emergencias">🚨 EMERGENCIAS</option>
                                        <option value="administracion">📁 ADMINISTRACIÓN</option>
                                        <option value="baño">🚻 BAÑO</option>
                                    </select>

                                    <!-- Descripción contextual consultorio -->
                                    <div x-show="roomSubtype === 'consultorio_fisico' || roomSubtype === 'consultorio_funcional'"
                                        class="p-3 rounded-xl text-[8px] leading-relaxed"
                                        :class="roomSubtype === 'consultorio_funcional'
                                                                                                             ? 'bg-amber-50 text-amber-700 border border-amber-100'
                                                                                                             : 'bg-emerald-50 text-emerald-700 border border-emerald-100'">
                                        <span x-show="roomSubtype === 'consultorio_fisico'">🏥 <strong>Físico:</strong>
                                            Espacio permanente, de uso exclusivo para atención clínica. Borde sólido
                                            verde.</span>
                                        <span x-show="roomSubtype === 'consultorio_funcional'">🔄
                                            <strong>Funcional:</strong> Espacio compartido o adaptado. Se muestra con borde
                                            discontinuo ámbar y badge FUNC.</span>
                                    </div>
                                    <input type="text" x-model="name" placeholder="NOMBRE…"
                                        class="w-full bg-white border-none rounded-xl px-4 py-3 text-xs font-bold text-slate-700 shadow-sm focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-slate-300">
                                    <div class="grid grid-cols-2 gap-2">
                                        <button @click="attrs.wifi = !attrs.wifi"
                                            :class="attrs.wifi ? 'bg-blue-600 text-white shadow-indigo-200' : 'bg-white text-slate-400'"
                                            class="p-4 rounded-2xl flex flex-col items-center gap-2 transition-all shadow-sm group">
                                            <i data-lucide="wifi"
                                                class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                                            <span class="text-[8px] font-black uppercase">Wifi</span>
                                        </button>
                                        <button @click="attrs.light = !attrs.light"
                                            :class="attrs.light ? 'bg-amber-500 text-white shadow-amber-200' : 'bg-white text-slate-400'"
                                            class="p-4 rounded-2xl flex flex-col items-center gap-2 transition-all shadow-sm group">
                                            <i data-lucide="zap"
                                                class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                                            <span class="text-[8px] font-black uppercase">Luz</span>
                                        </button>
                                        <div
                                            class="col-span-2 bg-white/50 p-2 rounded-2xl border border-slate-100 flex items-center justify-between">
                                            <div class="flex items-center gap-2 ml-2">
                                                <i data-lucide="share-2" class="w-4 h-4 text-emerald-500"></i>
                                                <span class="text-[9px] font-black text-slate-500 uppercase">Puntos
                                                    Red</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <button @click="attrs.red = Math.max(0, attrs.red - 1)"
                                                    class="w-8 h-8 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-rose-500 transition-all">-</button>
                                                <span class="text-xs font-black text-indigo-600 w-4 text-center"
                                                    x-text="attrs.red"></span>
                                                <button @click="attrs.red++"
                                                    class="w-8 h-8 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-emerald-500 transition-all">+</button>
                                            </div>
                                        </div>
                                    </div>
                                    <button @click="addElement('ambiente'); _autoCloseSheet()"
                                        @pointerdown="startSidebarDrag('ambiente', roomSubtype, $event)"
                                        class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-700 active:scale-95 transition-all shadow-lg shadow-indigo-100 cursor-grab active:cursor-grabbing">
                                        Añadir al Plano
                                    </button>
                                    <p class="text-[7px] text-center text-indigo-300 mt-1">↗ o arrástralo directo al plano
                                    </p>
                                </div>
                            </div>
                        </template>

                        <template x-if="tool === 'hardware'">
                            <div class="bg-indigo-50/50 p-5 rounded-2xl border border-indigo-100">
                                <h2
                                    class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3 flex items-center gap-2">
                                    <i data-lucide="cpu" class="w-3 h-3"></i> Equipamiento TI
                                </h2>

                                {{-- Equipos de cómputo del consultorio --}}
                                <p class="text-[7px] font-black uppercase text-slate-400 mb-1.5">Cómputo</p>
                                <div class="grid grid-cols-3 gap-1.5 mb-3">
                                    <template x-for="eq in equiposComputo" :key="eq.tipo">
                                        <button @click="hwType = eq.tipo"
                                            :class="hwType === eq.tipo ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-white text-slate-400 hover:text-indigo-500'"
                                            class="p-2 rounded-xl flex flex-col items-center gap-1 transition-all shadow-sm border border-indigo-50">
                                            <i :data-lucide="eq.icon" class="w-4 h-4"></i>
                                            <span class="text-[7px] font-black uppercase leading-tight text-center"
                                                x-text="eq.label"></span>
                                        </button>
                                    </template>
                                </div>

                                {{-- Red y energía --}}
                                <p class="text-[7px] font-black uppercase text-slate-400 mb-1.5">Red y energía</p>
                                <div class="grid grid-cols-3 gap-1.5 mb-4">
                                    <template x-for="eq in equiposRed" :key="eq.tipo">
                                        <button @click="hwType = eq.tipo"
                                            :class="hwType === eq.tipo ? 'bg-emerald-600 text-white shadow-md shadow-emerald-200' : 'bg-white text-slate-400 hover:text-emerald-600'"
                                            class="p-2 rounded-xl flex flex-col items-center gap-1 transition-all shadow-sm border border-emerald-50">
                                            <i :data-lucide="eq.icon" class="w-4 h-4"></i>
                                            <span class="text-[7px] font-black uppercase leading-tight text-center"
                                                x-text="eq.label"></span>
                                        </button>
                                    </template>
                                </div>

                                <button @click="addElement('hardware'); _autoCloseSheet()"
                                    @pointerdown="startSidebarDrag('hardware', hwType, $event)"
                                    class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-700 active:scale-95 transition-all shadow-lg shadow-indigo-100 cursor-grab active:cursor-grabbing">
                                    Colocar <span x-text="hwLabelActual"></span>
                                </button>
                                <p class="text-[7px] text-center text-indigo-300 mt-1">↗ o arrástralo directo al plano</p>
                            </div>
                        </template>

                        <template x-if="tool === 'puerta'">
                            <div class="bg-amber-50/50 p-5 rounded-2xl border border-amber-100">
                                <h2
                                    class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <i data-lucide="door-open" class="w-3 h-3"></i> Colocar Puerta
                                </h2>

                                <!-- Selector subtipo -->
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                    <button @click="doorSubtype = 'interna'"
                                        :class="doorSubtype === 'interna' ? 'bg-amber-500 text-white shadow-amber-200 shadow-md' : 'bg-white text-slate-500'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-1.5 transition-all border border-amber-100">
                                        <!-- Puerta interna: panel blanco + ventana arcada gris + pomo negro -->
                                        <svg width="22" height="26" viewBox="0 0 22 26" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <!-- Marco exterior negro -->
                                            <rect x="0.5" y="0.5" width="21" height="25" rx="1.5" fill="#111" />
                                            <!-- Panel blanco -->
                                            <rect x="2" y="2" width="18" height="23" rx="1" fill="#ffffff" />
                                            <!-- Ventana: parte rectangular inferior -->
                                            <rect x="6" y="13" width="7" height="8" rx="0" fill="rgba(220,220,220,0.95)"
                                                stroke="#111" stroke-width="0.8" />
                                            <!-- Ventana: arco superior -->
                                            <path d="M6 16.5 A3.5 3.5 0 0 1 13 16.5" fill="rgba(220,220,220,0.95)"
                                                stroke="#111" stroke-width="0.8" />
                                            <rect x="6" y="10" width="7" height="6.5" rx="0"
                                                fill="rgba(220,220,220,0.95)" />
                                            <path d="M6 10 A3.5 3.5 0 0 1 13 10" fill="rgba(220,220,220,0.95)" stroke="#111"
                                                stroke-width="0.8" />
                                            <!-- Divisor ventana -->
                                            <line x1="6.5" y1="16" x2="12.5" y2="16" stroke="#111" stroke-width="0.8" />
                                            <!-- Contorno ventana completo -->
                                            <rect x="6" y="10" width="7" height="11" rx="0" fill="none" stroke="#111"
                                                stroke-width="0.8" />
                                            <!-- Pomo negro con reflejo -->
                                            <circle cx="17" cy="15" r="2" fill="#111" />
                                            <circle cx="17" cy="15" r="1.1" fill="#555" />
                                            <circle cx="16.5" cy="14.5" r="0.4" fill="rgba(255,255,255,0.7)" />
                                        </svg>
                                        <span class="text-[8px] font-black uppercase">Interna</span>
                                        <span class="text-[7px] text-center leading-tight opacity-70">1 hoja ·
                                            interna</span>
                                    </button>
                                    <button @click="doorSubtype = 'externa'"
                                        :class="doorSubtype === 'externa' ? 'bg-slate-800 text-white shadow-slate-300 shadow-md' : 'bg-white text-slate-500'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-1.5 transition-all border border-slate-200">
                                        <!-- Portón de rejas: pilares, barrotes y remate de puntas -->
                                        <svg width="32" height="26" viewBox="0 0 32 26" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <!-- Pilares laterales -->
                                            <rect x="0" y="3" width="3" height="22" rx="0.6" fill="currentColor" />
                                            <rect x="29" y="3" width="3" height="22" rx="0.6" fill="currentColor" />
                                            <!-- Travesaños de las hojas -->
                                            <rect x="3" y="7" width="26" height="2" fill="currentColor" />
                                            <rect x="3" y="21" width="26" height="2" fill="currentColor" />
                                            <!-- Barrotes -->
                                            <g stroke="currentColor" stroke-width="1.3">
                                                <line x1="6" y1="9" x2="6" y2="21" />
                                                <line x1="9.5" y1="9" x2="9.5" y2="21" />
                                                <line x1="13" y1="9" x2="13" y2="21" />
                                                <line x1="19" y1="9" x2="19" y2="21" />
                                                <line x1="22.5" y1="9" x2="22.5" y2="21" />
                                                <line x1="26" y1="9" x2="26" y2="21" />
                                            </g>
                                            <!-- Encuentro de las dos hojas -->
                                            <rect x="15.2" y="6" width="1.6" height="18" fill="currentColor" />
                                            <!-- Remate de puntas -->
                                            <path d="M4 7 L5.5 3.5 L7 7 Z M8.5 7 L10 3.5 L11.5 7 Z M13 7 L14.5 3.5 L16 7 Z M17.5 7 L19 3.5 L20.5 7 Z M22 7 L23.5 3.5 L25 7 Z M26 7 L27.5 3.5 L29 7 Z"
                                                fill="currentColor" />
                                        </svg>
                                        <span class="text-[8px] font-black uppercase">Portón</span>
                                        <span class="text-[7px] text-center leading-tight opacity-70">rejas · acceso
                                            calle</span>
                                    </button>
                                </div>

                                <!-- Descripción contextual -->
                                <div class="mb-4 p-3 rounded-xl text-[8px] leading-relaxed"
                                    :class="doorSubtype === 'externa' ? 'bg-red-50 text-red-700 border border-red-100' : 'bg-amber-50 text-amber-700 border border-amber-100'">
                                    <span x-show="doorSubtype === 'interna'">🚪 Puerta simple de una hoja. Para pasillos,
                                        consultorios y ambientes interiores.</span>
                                    <span x-show="doorSubtype === 'externa'">🚧 Portón de rejas de dos hojas. Acceso
                                        principal del establecimiento desde la calle.</span>
                                </div>

                                <button @click="addElement('puerta'); _autoCloseSheet()"
                                    @pointerdown="startSidebarDrag('puerta', doorSubtype, $event)"
                                    :class="doorSubtype === 'externa' ? 'bg-red-600 hover:bg-red-700' : 'bg-slate-900 hover:bg-slate-800'"
                                    class="w-full py-4 text-white rounded-2xl text-[10px] font-black uppercase transition-all shadow-lg cursor-grab active:cursor-grabbing active:scale-95">
                                    Añadir Puerta
                                </button>
                                <p class="text-[7px] text-center text-slate-400 mt-1">↗ o arrástrala directo al plano</p>
                            </div>
                        </template>

                        <template x-if="tool === 'red'">
                            <div class="bg-blue-50/50 p-5 rounded-2xl border border-blue-100">
                                <h2
                                    class="text-[10px] font-black text-blue-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <i data-lucide="share-2" class="w-3 h-3"></i> Cableado de Red
                                </h2>
                                <div class="space-y-3">
                                    <p class="text-[9px] text-slate-500">Haz clic en un equipo o ambiente y arrastra hacia
                                        otro para crear una conexión de red.</p>
                                    <div class="p-3 bg-white rounded-xl border border-blue-100 flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></div>
                                        <span class="text-[9px] font-bold text-slate-600 uppercase">Modo Conexión
                                            Activo</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template x-if="tool === 'calle'">
                            <div class="p-5 rounded-2xl"
                                style="background-color:rgba(209,250,229,0.45);border:1px solid #a7f3d0;">
                                <h2 class="text-[10px] font-black uppercase tracking-widest mb-4 flex items-center gap-2"
                                    style="color:#047857;">
                                    <i data-lucide="map" class="w-3 h-3"></i> Calle / Referencia
                                </h2>

                                <!-- Tipo de calle -->
                                <div class="space-y-2 mb-4">
                                    <p class="text-[7px] font-black uppercase text-slate-400">Tipo de vía</p>
                                    <div class="grid grid-cols-3 gap-2">
                                        <button @click="calleSubtype = 'avenida'"
                                            :style="calleSubtype === 'avenida' ? 'background:#047857;color:white;box-shadow:0 4px 6px rgba(4,120,87,0.35)' : 'background:white;color:#64748b'"
                                            class="p-2.5 rounded-2xl flex flex-col items-center gap-1 transition-all shadow-sm"
                                            style="border:1px solid #a7f3d0;">
                                            <i data-lucide="chevrons-right" class="w-4 h-4"></i>
                                            <span class="text-[7px] font-black uppercase">Avenida</span>
                                            <span class="text-[6px] opacity-60">doble vía</span>
                                        </button>
                                        <button @click="calleSubtype = 'jiron'"
                                            :style="calleSubtype === 'jiron' ? 'background:#059669;color:white;box-shadow:0 4px 6px rgba(5,150,105,0.35)' : 'background:white;color:#64748b'"
                                            class="p-2.5 rounded-2xl flex flex-col items-center gap-1 transition-all shadow-sm"
                                            style="border:1px solid #a7f3d0;">
                                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                            <span class="text-[7px] font-black uppercase">Jirón</span>
                                            <span class="text-[6px] opacity-60">una vía</span>
                                        </button>
                                        <button @click="calleSubtype = 'pasaje'"
                                            :style="calleSubtype === 'pasaje' ? 'background:#10b981;color:white;box-shadow:0 4px 6px rgba(16,185,129,0.35)' : 'background:white;color:#64748b'"
                                            class="p-2.5 rounded-2xl flex flex-col items-center gap-1 transition-all shadow-sm"
                                            style="border:1px solid #a7f3d0;">
                                            <i data-lucide="minus" class="w-4 h-4"></i>
                                            <span class="text-[7px] font-black uppercase">Pasaje</span>
                                            <span class="text-[6px] opacity-60">angosta</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Nombre de la calle -->
                                <div class="mb-4">
                                    <p class="text-[7px] font-black uppercase text-slate-400 mb-1.5">Nombre de la vía</p>
                                    <input type="text" x-model="name"
                                        :placeholder="calleSubtype === 'avenida' ? 'Av. Los Héroes...' : (calleSubtype === 'jiron' ? 'Jr. Lima...' : 'Psj. San Juan...')"
                                        class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 focus:ring-2 transition-all placeholder:text-slate-300"
                                        style="focus-ring-color:#a7f3d0;">
                                </div>

                                <!-- Descripción visual -->
                                <div class="mb-4 p-3 rounded-xl text-[8px] text-slate-500 leading-relaxed"
                                    style="background:white;border:1px solid #d1fae5;">
                                    <span x-show="calleSubtype === 'avenida'">🛣️ <strong>Avenida:</strong> vía ancha de
                                        doble sentido con aceras y líneas de carril.</span>
                                    <span x-show="calleSubtype === 'jiron'">🛤️ <strong>Jirón:</strong> calle urbana
                                        estándar de un sentido con línea central.</span>
                                    <span x-show="calleSubtype === 'pasaje'">🚶 <strong>Pasaje:</strong> vía angosta
                                        peatonal o vehicular restringida.</span>
                                </div>

                                <button @click="addElement('calle'); _autoCloseSheet()"
                                    @pointerdown="startSidebarDrag('calle', calleSubtype, $event)"
                                    @mouseenter="$el.style.backgroundColor='#065f46'"
                                    @mouseleave="$el.style.backgroundColor='#047857'"
                                    class="w-full py-4 text-white rounded-2xl text-[10px] font-black uppercase active:scale-95 transition-all shadow-lg cursor-grab active:cursor-grabbing"
                                    style="background-color:#047857;">
                                    Añadir Calle
                                </button>
                                <p class="text-[7px] text-center mt-1" style="color:#6ee7b7;">↗ o arrástrala directo al
                                    plano</p>
                            </div>
                        </template>

                        <template x-if="tool === 'sistema'">
                            <div class="p-5 rounded-2xl"
                                style="background-color:rgba(245,243,255,0.5);border:1px solid #ede9fe;">
                                <h2 class="text-[10px] font-black uppercase tracking-widest mb-4 flex items-center gap-2"
                                    style="color:#7c3aed;">
                                    <i data-lucide="monitor" class="w-3 h-3"></i> Sistema de Salud
                                </h2>

                                <!-- Selector sistema -->
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                    <button @click="sistemaType = 'tua'"
                                        :style="sistemaType === 'tua' ? 'background:#6d28d9;color:white;box-shadow:0 4px 6px rgba(109,40,217,0.35)' : 'background:white;color:#64748b'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-1.5 transition-all shadow-sm"
                                        style="border:1px solid #ede9fe;">
                                        <i data-lucide="app-window" class="w-5 h-5"></i>
                                        <span class="text-[9px] font-black uppercase">TUA</span>
                                        <span class="text-[7px] opacity-70">Turnos únicos</span>
                                    </button>
                                    <button @click="sistemaType = 'sihce'"
                                        :style="sistemaType === 'sihce' ? 'background:#1d4ed8;color:white;box-shadow:0 4px 6px rgba(29,78,216,0.35)' : 'background:white;color:#64748b'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-1.5 transition-all shadow-sm"
                                        style="border:1px solid #dbeafe;">
                                        <i data-lucide="file-text" class="w-5 h-5"></i>
                                        <span class="text-[9px] font-black uppercase">SIHCE</span>
                                        <span class="text-[7px] opacity-70">Hist. clínica</span>
                                    </button>
                                    <button @click="sistemaType = 'sismed'"
                                        :style="sistemaType === 'sismed' ? 'background:#0f766e;color:white;box-shadow:0 4px 6px rgba(15,118,110,0.35)' : 'background:white;color:#64748b'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-1.5 transition-all shadow-sm"
                                        style="border:1px solid #ccfbf1;">
                                        <i data-lucide="pill" class="w-5 h-5"></i>
                                        <span class="text-[9px] font-black uppercase">SISMED</span>
                                        <span class="text-[7px] opacity-70">Medicamentos</span>
                                    </button>
                                    <button @click="sistemaType = 'hisminsa'"
                                        :style="sistemaType === 'hisminsa' ? 'background:#c2410c;color:white;box-shadow:0 4px 6px rgba(194,65,12,0.35)' : 'background:white;color:#64748b'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-1.5 transition-all shadow-sm"
                                        style="border:1px solid #fed7aa;">
                                        <i data-lucide="activity" class="w-5 h-5"></i>
                                        <span class="text-[9px] font-black uppercase">HISMINSA</span>
                                        <span class="text-[7px] opacity-70">Indicadores HIS</span>
                                    </button>
                                    <button @click="sistemaType = 'sisgalenplus'"
                                        :style="sistemaType === 'sisgalenplus' ? 'background:#2563eb;color:white;box-shadow:0 4px 6px rgba(37,99,235,0.35)' : 'background:white;color:#64748b'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-1.5 transition-all shadow-sm"
                                        style="border:1px solid #dbeafe;">
                                        <i data-lucide="plus-square" class="w-5 h-5"></i>
                                        <span class="text-[9px] font-black uppercase">SIS GalenPlus</span>
                                        <span class="text-[7px] opacity-70">Gestión Hospitalaria</span>
                                    </button>
                                </div>

                                <!-- Info contextual -->
                                <div class="mb-4 p-3 rounded-xl text-[8px] leading-relaxed"
                                    :style="sistemaType === 'tua'          ? 'background:#f5f3ff;color:#5b21b6;border:1px solid #ede9fe;' :
                                                                                                             sistemaType === 'sihce'        ? 'background:#eff6ff;color:#1e40af;border:1px solid #dbeafe;' :
                                                                                                             sistemaType === 'sismed'       ? 'background:#f0fdfa;color:#134e4a;border:1px solid #ccfbf1;' :
                                                                                                             sistemaType === 'hisminsa'     ? 'background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;' :
                                                                                                                                              'background:#eff6ff;color:#1e40af;border:1px solid #dbeafe;'">
                                    <span x-show="sistemaType === 'tua'">🖥️ <strong>TUA:</strong> Sistema de turnos y citas
                                        únicas de atención.</span>
                                    <span x-show="sistemaType === 'sihce'">📋 <strong>SIHCE:</strong> Historia clínica
                                        electrónica del paciente.</span>
                                    <span x-show="sistemaType === 'sismed'">💊 <strong>SISMED:</strong> Sistema de
                                        información de medicamentos.</span>
                                    <span x-show="sistemaType === 'hisminsa'">📊 <strong>HISMINSA:</strong> Indicadores de
                                        salud y producción de servicios.</span>
                                    <span x-show="sistemaType === 'sisgalenplus'">🏥 <strong>SIS GalenPlus:</strong> Sistema
                                        integral de gestión hospitalaria.</span>
                                </div>

                                <button @click="addElement('sistema'); _autoCloseSheet()"
                                    @pointerdown="startSidebarDrag('sistema', sistemaType, $event)"
                                    :style="'background:' + (sistemaType === 'tua' ? '#6d28d9' : sistemaType === 'sihce' ? '#1d4ed8' : sistemaType === 'sismed' ? '#0f766e' : sistemaType === 'hisminsa' ? '#c2410c' : '#1d4ed8')"
                                    class="w-full py-4 text-white rounded-2xl text-[10px] font-black uppercase transition-all shadow-lg cursor-grab active:cursor-grabbing active:scale-95">
                                    Colocar Sistema
                                </button>
                                <p class="text-[7px] text-center mt-1" style="color:#c4b5fd;">↗ o arrástralo directo al
                                    plano</p>
                            </div>
                        </template>

                        <template x-if="selectedId">
                            <div class="mt-6 pt-6 border-t border-slate-100">
                                <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Editar
                                    Selección</h2>

                                <!-- Nombre -->
                                <div class="mb-4">
                                    <input type="text" x-model="selectedElName" placeholder="Nombre..."
                                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-indigo-500 transition-all placeholder:text-slate-300">
                                </div>

                                <!-- Propiedades (solo si es ambiente) -->
                                <template x-if="selectedEl && selectedEl.type === 'ambiente'">
                                    <div class="mb-4 bg-slate-50 rounded-2xl p-3 border border-slate-100">
                                        <p class="text-[8px] font-black uppercase text-slate-400 mb-2 flex items-center gap-1">
                                            <i data-lucide="settings" class="w-3 h-3"></i> Propiedades
                                        </p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button @click="toggleSelectedAttr('wifi')"
                                                :class="selectedEl.attrs?.wifi ? 'bg-blue-600 text-white shadow-indigo-200' : 'bg-white border-slate-200 text-slate-400'"
                                                class="p-4 rounded-xl flex flex-col items-center gap-2 transition-all shadow-sm border">
                                                <i data-lucide="wifi" class="w-4 h-4"></i>
                                                <span class="text-[8px] font-black uppercase">Wifi</span>
                                            </button>
                                            <button @click="toggleSelectedAttr('light')"
                                                :class="selectedEl.attrs?.light ? 'bg-amber-500 text-white shadow-amber-200' : 'bg-white border-slate-200 text-slate-400'"
                                                class="p-4 rounded-xl flex flex-col items-center gap-2 transition-all shadow-sm border">
                                                <i data-lucide="zap" class="w-4 h-4"></i>
                                                <span class="text-[8px] font-black uppercase">Luz</span>
                                            </button>
                                        </div>
                                        <div class="mt-2 bg-white p-2 rounded-xl border border-slate-100 flex items-center justify-between">
                                            <div class="flex items-center gap-2 ml-2">
                                                <i data-lucide="share-2" class="w-4 h-4 text-emerald-500"></i>
                                                <span class="text-[9px] font-black text-slate-500 uppercase">Puntos Red</span>
                                            </div>
                                            <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-lg border border-slate-200">
                                                <button @click="changeSelectedAttrCount('red', -1)"
                                                    class="w-6 h-6 flex justify-center items-center rounded bg-white text-slate-400 shadow-sm hover:text-emerald-600 font-bold transition-colors">
                                                    -
                                                </button>
                                                <span class="w-4 text-center text-[10px] font-black text-slate-600"
                                                    x-text="selectedEl.attrs?.red || 0"></span>
                                                <button @click="changeSelectedAttrCount('red', 1)"
                                                    class="w-6 h-6 flex justify-center items-center rounded bg-emerald-500 text-white shadow-sm hover:bg-emerald-600 font-bold transition-colors">
                                                    +
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Tamaño numérico -->
                                <div class="mb-4 bg-slate-50 rounded-2xl p-3 border border-slate-100">
                                    <p class="text-[8px] font-black uppercase text-slate-400 mb-2 flex items-center gap-1">
                                        <i data-lucide="move" class="w-3 h-3"></i> Tamaño
                                    </p>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="text-[7px] font-bold uppercase text-slate-400">Ancho (px)</label>
                                            <input type="number" min="20" step="10" :value="selectedEl ? selectedEl.w : ''"
                                                @change="setSize('w', $event.target.value)"
                                                class="w-full mt-1 bg-white border border-slate-200 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all">
                                        </div>
                                        <div>
                                            <label class="text-[7px] font-bold uppercase text-slate-400">Largo (px)</label>
                                            <input type="number" min="20" step="10" :value="selectedEl ? selectedEl.h : ''"
                                                @change="setSize('h', $event.target.value)"
                                                class="w-full mt-1 bg-white border border-slate-200 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-700 focus:ring-2 focus:ring-indigo-500 transition-all">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 mt-2">
                                        <button @click="resizeSelected(20, 0)"
                                            class="p-2 bg-white border border-slate-200 rounded-xl text-[8px] font-black uppercase hover:bg-slate-100 transition-all">+
                                            Ancho</button>
                                        <button @click="resizeSelected(-20, 0)"
                                            class="p-2 bg-white border border-slate-200 rounded-xl text-[8px] font-black uppercase hover:bg-slate-100 transition-all">−
                                            Ancho</button>
                                        <button @click="resizeSelected(0, 20)"
                                            class="p-2 bg-white border border-slate-200 rounded-xl text-[8px] font-black uppercase hover:bg-slate-100 transition-all">+
                                            Largo</button>
                                        <button @click="resizeSelected(0, -20)"
                                            class="p-2 bg-white border border-slate-200 rounded-xl text-[8px] font-black uppercase hover:bg-slate-100 transition-all">−
                                            Largo</button>
                                    </div>
                                </div>

                                <!-- Rotación libre -->
                                <div class="mb-4 bg-indigo-50/60 rounded-2xl p-3 border border-indigo-100">
                                    <p class="text-[8px] font-black uppercase text-indigo-400 mb-2 flex items-center gap-1">
                                        <i data-lucide="rotate-cw" class="w-3 h-3"></i> Rotación
                                    </p>
                                    <!-- Slider -->
                                    <input type="range" min="0" max="359" step="1"
                                        :value="selectedEl ? (selectedEl.rot || 0) : 0"
                                        @input="setRotation($event.target.value)" class="w-full h-2 accent-indigo-600 mb-2">
                                    <!-- Grados numérico + reset -->
                                    <div class="flex items-center gap-2">
                                        <input type="number" min="0" max="359" step="1"
                                            :value="selectedEl ? (selectedEl.rot || 0) : 0"
                                            @change="setRotation($event.target.value)"
                                            class="flex-1 bg-white border border-indigo-200 rounded-lg px-2 py-1.5 text-xs font-bold text-indigo-700 focus:ring-2 focus:ring-indigo-500 transition-all text-center">
                                        <span class="text-[9px] font-bold text-indigo-400">°</span>
                                        <button @click="setRotation(0)" title="Reiniciar rotación"
                                            class="px-2 py-1.5 bg-white border border-indigo-200 rounded-lg text-[8px] font-black uppercase text-indigo-600 hover:bg-indigo-100 transition-all">
                                            Reset
                                        </button>
                                    </div>
                                    <!-- Botones rápidos -->
                                    <div class="grid grid-cols-4 gap-1 mt-2">
                                        <button @click="rotateSelected(-15)"
                                            class="py-1.5 bg-white border border-indigo-100 rounded-lg text-[7px] font-black uppercase text-indigo-600 hover:bg-indigo-100 transition-all">−15°</button>
                                        <button @click="rotateSelected(15)"
                                            class="py-1.5 bg-white border border-indigo-100 rounded-lg text-[7px] font-black uppercase text-indigo-600 hover:bg-indigo-100 transition-all">+15°</button>
                                        <button @click="rotateSelected(-90)"
                                            class="py-1.5 bg-white border border-indigo-100 rounded-lg text-[7px] font-black uppercase text-indigo-600 hover:bg-indigo-100 transition-all">−90°</button>
                                        <button @click="rotateSelected(90)"
                                            class="py-1.5 bg-indigo-600 text-white rounded-lg text-[7px] font-black uppercase hover:bg-indigo-700 transition-all">+90°</button>
                                    </div>
                                    <p class="text-[7px] text-indigo-300 mt-1.5 text-center">💡 Arrastra el ícono ↻ sobre el
                                        objeto para rotar libremente</p>
                                </div>

                                <!-- Mover a otro piso -->
                                <div x-show="totalPisos > 1"
                                    class="mb-4 bg-violet-50/60 rounded-2xl p-3 border border-violet-100">
                                    <p class="text-[8px] font-black uppercase text-violet-400 mb-2 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Mover a piso
                                    </p>
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="n in pisoRange()" :key="n">
                                            <button @click="moveSelectedToPiso(n)"
                                                :class="selectedEl && (selectedEl.piso || 1) === n
                                                                                                                        ? 'bg-violet-600 text-white shadow-md shadow-violet-200'
                                                                                                                        : 'bg-white text-slate-500 hover:bg-violet-100 hover:text-violet-700'"
                                                class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase transition-all border border-violet-100"
                                                :title="'Mover al Piso ' + n" x-text="'P' + n">
                                            </button>
                                        </template>
                                    </div>
                                    <p class="text-[7px] text-violet-300 mt-1.5">El elemento se traslada al piso
                                        seleccionado</p>
                                </div>

                                <button @click="confirmDelete($event)"
                                    class="w-full py-3 bg-rose-50 text-rose-600 rounded-2xl text-[10px] font-black uppercase flex items-center justify-center gap-2 hover:bg-rose-100 transition-all">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i> Eliminar
                                </button>
                            </div>
                        </template>

                    </div><!-- /tools-content -->
                </div><!-- /sidebar -->

                <canvas id="blueprint-canvas" :style="panMode ? 'cursor:grab' : ''" @mousedown="handleMouseDown"
                    @mousemove="handleMouseMove"
                    @mouseup="handleMouseUp" @touchstart="handleTouchStart" @touchmove="handleTouchMove"
                    @touchend="handleTouchEnd" @contextmenu.prevent="confirmDelete($event)" @dragover.prevent
                    @drop="handleDrop($event)">
                </canvas>

                <!-- ══════════════════════════════════════════════════════ -->
                <!-- Panel Flotante de Pisos (derecha del canvas)          -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="absolute top-2 right-2 sm:top-4 sm:right-4 z-40 flex flex-col items-center gap-0 select-none"
                    style="filter: drop-shadow(0 8px 24px rgba(79,70,229,0.18));">

                    <!-- Botón Añadir Piso -->
                    <button @click="addPiso()" title="Añadir piso"
                        class="w-10 sm:w-12 h-9 sm:h-10 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-t-2xl flex items-center justify-center transition-all shadow-lg shadow-indigo-200 border-b border-indigo-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                    </button>

                    <!-- Lista de pisos (mayor arriba, 1 abajo — como un edificio) -->
                    <div class="flex flex-col items-center max-h-[38dvh] overflow-y-auto no-scrollbar overscroll-contain">
                    <template x-for="piso in pisoRange().slice().reverse()" :key="piso">
                        <div class="relative group">
                            <!-- Etiqueta ACTUAL -->
                            <div x-show="piso === currentPiso"
                                class="absolute -left-14 top-1/2 -translate-y-1/2 text-[7px] font-black uppercase text-indigo-600 bg-indigo-50 border border-indigo-200 px-1.5 py-0.5 rounded-lg whitespace-nowrap pointer-events-none"
                                style="letter-spacing:0.06em;">ACTUAL</div>

                            <button @click="goToPiso(piso)" :title="'Ir al Piso ' + piso"
                                :class="piso === currentPiso
                                                                                                        ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-300 scale-105 z-10'
                                                                                                        : 'bg-white/95 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600'"
                                class="w-10 sm:w-12 flex flex-col items-center justify-center py-2 transition-all duration-150 border-b border-slate-100 relative">
                                <!-- Icono edificio mini -->
                                <svg :class="piso === currentPiso ? 'text-indigo-200' : 'text-slate-300'"
                                    class="w-3 h-3 mb-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <rect x="3" y="4" width="14" height="13" rx="1" fill="currentColor" opacity="0.5" />
                                    <rect x="7" y="9" width="2" height="2" rx="0.3" fill="white" />
                                    <rect x="11" y="9" width="2" height="2" rx="0.3" fill="white" />
                                    <rect x="7" y="13" width="2" height="2" rx="0.3" fill="white" />
                                    <rect x="11" y="13" width="2" height="2" rx="0.3" fill="white" />
                                </svg>
                                <span class="text-[10px] font-black leading-none" x-text="'P' + piso"></span>
                                <!-- Badge de elementos -->
                                <span class="text-[7px] font-bold leading-none mt-0.5 opacity-70"
                                    x-text="countInPiso(piso) > 0 ? countInPiso(piso) + '' : '·'"></span>
                            </button>

                            <!-- Tooltip al hover (izquierda) -->
                            <div
                                class="absolute right-full mr-2 top-1/2 -translate-y-1/2
                                                                                                        bg-slate-900 text-white text-[8px] font-bold px-2 py-1 rounded-lg
                                                                                                        whitespace-nowrap pointer-events-none shadow-xl
                                                                                                        opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                <span x-text="'Piso ' + piso + ' · ' + countInPiso(piso) + ' elementos'"></span>
                            </div>
                        </div>
                    </template>
                    </div><!-- /lista de pisos -->

                    <!-- Botón Quitar Piso -->
                    <button @click="removePiso()" :disabled="totalPisos <= 1" title="Eliminar piso actual"
                        class="w-10 sm:w-12 h-9 sm:h-10 bg-rose-50 hover:bg-rose-100 active:scale-95 text-rose-400 hover:text-rose-600 rounded-b-2xl flex items-center justify-center transition-all border-t border-rose-100 disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path d="M5 12h14" />
                        </svg>
                    </button>

                    <!-- Toggle Ghost Floor -->
                    <button @click="showGhostFloor = !showGhostFloor; draw()" x-show="totalPisos > 1"
                        :title="showGhostFloor ? 'Ocultar silueta del piso adyacente' : 'Mostrar silueta del piso adyacente'"
                        :class="showGhostFloor ? 'bg-indigo-100 text-indigo-600 border-indigo-200' : 'bg-white/80 text-slate-400 border-slate-200'"
                        class="mt-2 w-10 sm:w-12 h-9 sm:h-10 border rounded-2xl flex flex-col items-center justify-center gap-0.5 transition-all hover:scale-105 active:scale-95 shadow-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span class="text-[6px] font-black uppercase" x-text="showGhostFloor ? 'Ghost' : 'Ghost'"></span>
                    </button>
                </div>
                <!-- / Panel de Pisos -->

                <!-- ══ Panel Zoom + Opacidad del Croquis ══ -->
                <div x-show="!(isMobile && sidebarOpen && panelVisible)"
                    class="absolute bottom-3 sm:bottom-6 left-1/2 -translate-x-1/2 z-40 flex items-center gap-2 sm:gap-3
                           max-w-[calc(100vw-1rem)] bg-white/90 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-2xl px-2.5 sm:px-4 py-2 sm:py-2.5"
                    style="pointer-events:auto;">

                    <!-- Modo mano: arrastrar el croquis con un dedo o con el ratón -->
                    <button @click="panMode = !panMode"
                        :class="panMode ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-slate-100 text-slate-600 hover:bg-indigo-100 hover:text-indigo-600'"
                        :title="panMode ? 'Modo mover activo · toca para volver a seleccionar' : 'Modo mover: arrastra el croquis'"
                        class="w-9 h-9 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center transition-all active:scale-90 flex-shrink-0">
                        <i data-lucide="hand" class="w-4 h-4"></i>
                    </button>

                    <!-- Separador -->
                    <div class="w-px h-5 bg-slate-200 flex-shrink-0"></div>

                    <!-- Zoom -->
                    <div class="flex items-center gap-1.5 sm:gap-2">
                        <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0 hidden sm:block" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8" />
                            <path d="M21 21l-4.35-4.35" />
                        </svg>
                        <button @click="zoomOut()" title="Reducir zoom"
                            class="w-9 h-9 sm:w-6 sm:h-6 bg-slate-100 hover:bg-indigo-100 text-slate-600 hover:text-indigo-600 rounded-lg flex items-center justify-center font-black text-base sm:text-sm transition-all active:scale-90">
                            −
                        </button>
                        <button @click="resetZoom()" title="Restablecer zoom (100%)"
                            class="min-w-[46px] h-9 sm:h-6 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-[10px] sm:text-[9px] font-black uppercase transition-all active:scale-90"
                            x-text="Math.round(canvasZoom * 100) + '%'">
                        </button>
                        <button @click="zoomIn()" title="Aumentar zoom"
                            class="w-9 h-9 sm:w-6 sm:h-6 bg-slate-100 hover:bg-indigo-100 text-slate-600 hover:text-indigo-600 rounded-lg flex items-center justify-center font-black text-base sm:text-sm transition-all active:scale-90">
                            +
                        </button>
                        <button @click="autoFit()" title="Ajustar el croquis a la pantalla"
                            class="sm:ml-1 w-9 h-9 sm:w-8 sm:h-6 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center justify-center transition-all active:scale-90 shadow-md shadow-indigo-200">
                            <i data-lucide="focus" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Navegación de pisos (solo móvil, donde el badge superior está oculto) -->
                    <template x-if="isCompact && totalPisos > 1">
                        <div class="flex items-center gap-1 pl-2 border-l border-slate-200">
                            <button @click="currentPiso > 1 && goToPiso(currentPiso - 1)" :disabled="currentPiso <= 1"
                                class="w-8 h-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center disabled:opacity-30 active:scale-90 transition-all"
                                title="Piso anterior">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="3">
                                    <path d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <span class="text-[10px] font-black text-indigo-700 min-w-[26px] text-center"
                                x-text="'P' + currentPiso"></span>
                            <button @click="currentPiso < totalPisos && goToPiso(currentPiso + 1)"
                                :disabled="currentPiso >= totalPisos"
                                class="w-8 h-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center disabled:opacity-30 active:scale-90 transition-all"
                                title="Piso siguiente">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="3">
                                    <path d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </template>

                    <!-- Separador -->
                    <div class="w-px h-5 bg-slate-200 hidden lg:block"></div>

                    <!-- Opacidad (se oculta en pantallas angostas para no saturar la barra) -->
                    <div class="hidden lg:flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 2a10 10 0 0 1 0 20V2z" fill="currentColor" class="text-slate-400" />
                        </svg>
                        <span class="text-[8px] font-black uppercase text-slate-400">Opac.</span>
                        <input type="range" min="0.1" max="1" step="0.05" x-model="canvasOpacity" @input="draw()"
                            class="w-20 h-1.5 accent-indigo-600" title="Opacidad del croquis">
                        <span class="text-[9px] font-bold text-indigo-600 min-w-[28px] text-right"
                            x-text="Math.round(canvasOpacity * 100) + '%'">
                        </span>
                    </div>

                </div>

                <!-- Pista de gestos (táctil) -->
                <div x-show="_hintVisible" x-transition.opacity.duration.400ms
                    class="absolute top-3 left-1/2 -translate-x-1/2 z-20 pointer-events-none max-w-[92vw]
                           bg-slate-900/85 text-white text-[10px] font-bold px-3.5 py-2 rounded-xl shadow-xl flex items-center gap-2">
                    <i data-lucide="move" class="w-3.5 h-3.5 flex-shrink-0"></i>
                    <span>Pellizca para acercar · arrastra para mover</span>
                </div>

                <!-- Tooltip flotante (solo con ratón: en táctil estorbaría bajo el dedo) -->
                <div x-show="hoveredEl && !isTouch" :style="`left: ${mouseX + 20}px; top: ${mouseY + 20}px`"
                    class="absolute z-50 bg-white/90 backdrop-blur shadow-2xl border border-slate-200 rounded-2xl p-4 pointer-events-none transition-all w-48">
                    <template x-if="hoveredEl">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[9px] font-black uppercase text-indigo-600"
                                    x-text="hoveredEl.subtype || hoveredEl.type"></span>
                                <div class="flex gap-1" x-show="layers.network || layers.power">
                                    <div x-show="hoveredEl.attrs?.wifi  && layers.network"
                                        class="w-2 h-2 rounded-full bg-blue-500"></div>
                                    <div x-show="hoveredEl.attrs?.light && layers.power"
                                        class="w-2 h-2 rounded-full bg-amber-500"></div>
                                </div>
                            </div>
                            <h3 class="text-xs font-bold text-slate-800 mb-1" x-text="hoveredEl.name || 'Sin nombre'"></h3>
                        </div>
                    </template>
                </div>

                <!-- ══════════════════════════════════════════════ -->
                <!-- Mini-Mapa de Referencia (Leaflet)             -->
                <!-- ══════════════════════════════════════════════ -->

                <div id="minimap-panel"
                    class="fixed w-72 bg-white/95 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-2xl z-[9999] overflow-hidden"
                    style="bottom:20px;right:20px;">

                    <!-- Header del mapa (click = colapsar, drag icon = arrastrar) -->
                    <div id="minimap-header"
                        class="flex items-center justify-between px-3 py-2.5 bg-white select-none border-b border-slate-100">
                        <!-- Drag handle -->
                        <div id="minimap-drag-handle"
                            class="cursor-move p-1 rounded-lg hover:bg-slate-100 transition-colors flex-shrink-0"
                            title="Arrastrar mapa">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 6h.01M8 12h.01M8 18h.01M16 6h.01M16 12h.01M16 18h.01" />
                            </svg>
                        </div>
                        <!-- Title (click = colapsar) -->
                        <div class="flex items-center gap-2 flex-1 cursor-pointer" onclick="toggleMinimap()">
                            <div class="w-6 h-6 bg-emerald-500 rounded-lg flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase text-slate-400 leading-none">Mapa de Referencia
                                </p>
                                <p class="text-[10px] font-bold text-slate-700 truncate max-w-[130px]">{{ $nombreEstab }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 cursor-pointer" onclick="toggleMinimap()">
                            @if($hasCoords)
                                <span class="text-[8px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">GPS
                                    ✓</span>
                            @else
                                <span class="text-[8px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">Sin
                                    coords</span>
                            @endif
                            <svg id="minimap-chevron" class="w-4 h-4 text-slate-400 transition-transform duration-300"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <!-- Cuerpo del mapa -->
                    @if($hasCoords)
                        <div id="minimap-container" style="height:220px;width:100%;"></div>
                        <!-- Footer dinámico -->
                        <div class="px-3 py-2 bg-slate-50 border-t border-slate-100">
                            <!-- Fila coordenadas + acciones -->
                            <div class="flex items-center justify-between">
                                <span id="minimap-coords" class="text-[8px] font-mono text-slate-400">
                                    {{ number_format($lat, 6) }}, {{ number_format($lng, 6) }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <button id="btn-edit-location" onclick="toggleEditMode()"
                                        class="text-[8px] font-black uppercase text-indigo-600 hover:text-indigo-800 transition-colors flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                        Editar
                                    </button>
                                    <a href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" target="_blank"
                                        class="text-[8px] font-black uppercase text-slate-400 hover:text-slate-600 transition-colors">
                                        Maps →
                                    </a>
                                </div>
                            </div>
                            <!-- Banner de confirmación (oculto por defecto) -->
                            <div id="minimap-save-banner"
                                class="hidden mt-2 p-2 bg-indigo-50 rounded-xl border border-indigo-100">
                                <p class="text-[8px] text-indigo-700 font-bold mb-1.5">📍 Haz clic en el mapa para mover el
                                    marcador</p>
                                <div class="flex gap-1.5">
                                    <button onclick="saveNewCoords()"
                                        class="flex-1 py-1.5 bg-indigo-600 text-white rounded-lg text-[8px] font-black uppercase hover:bg-indigo-700 transition-all">
                                        Guardar
                                    </button>
                                    <button onclick="cancelEditMode()"
                                        class="flex-1 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-[8px] font-black uppercase hover:bg-slate-200 transition-all">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-8 px-4 text-center">
                            <div class="w-10 h-10 bg-amber-50 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z" />
                                </svg>
                            </div>
                            <p class="text-[10px] font-bold text-slate-600 mb-1">Sin coordenadas registradas</p>
                            <p class="text-[9px] text-slate-400">Registra latitud y longitud del establecimiento para ver el
                                mapa.</p>
                        </div>
                    @endif
                </div>

            </div><!-- /canvas-container -->
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
        /* ─── Mini-mapa Leaflet ─── */
        @if($hasCoords)
            document.addEventListener('DOMContentLoaded', function () {
                setTimeout(function () {
                    const LAT = {{ $lat }};
                    const LNG = {{ $lng }};
                    const NAME = @json($nombreEstab);
                    const ESTAB_ID = {{ $acta->establecimiento->id }};
                    const CSRF = '{{ csrf_token() }}';
                    const COORDS_URL = @json(route('establecimientos.coordenadas', ['id' => '__ID__']));

                    let editMode = false;
                    let pendingLat = LAT;
                    let pendingLng = LNG;

                    const map = L.map('minimap-container', {
                        center: [LAT, LNG],
                        zoom: 17,
                        zoomControl: false,
                        attributionControl: false,
                        dragging: true,
                        scrollWheelZoom: false,
                    });

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '\u00a9 OpenStreetMap'
                    }).addTo(map);

                    /* Marcador tipo pin */
                    const icon = L.divIcon({
                        html: `<div style="
                                                                                                                            width:24px;height:24px;background:#4f46e5;
                                                                                                                            border-radius:50% 50% 50% 0;transform:rotate(-45deg);
                                                                                                                            border:3px solid white;box-shadow:0 3px 10px rgba(79,70,229,0.5);
                                                                                                                        "></div>`,
                        iconSize: [24, 24],
                        iconAnchor: [12, 24],
                        className: ''
                    });

                    const marker = L.marker([LAT, LNG], { icon, draggable: false }).addTo(map);
                    marker.bindPopup(
                        `<strong style="font-size:11px">${NAME}</strong><br>
                                                                                                                        <span style="color:#64748b;font-size:10px">${LAT.toFixed(6)}, ${LNG.toFixed(6)}</span>`,
                        { offset: [0, -8] }
                    );
                    /* El globo solo se abre solo en pantallas grandes: en las demás
                       taparía buena parte del croquis */
                    if (window.innerWidth >= 1280) marker.openPopup();

                    /* Clic en el mapa → mover marcador en modo edición */
                    map.on('click', function (e) {
                        if (!editMode) return;
                        pendingLat = e.latlng.lat;
                        pendingLng = e.latlng.lng;
                        marker.setLatLng([pendingLat, pendingLng]);
                        marker.getPopup()
                            .setContent(`<strong style="font-size:11px">${NAME}</strong><br>
                                                                                                                                <span style="color:#e11d48;font-size:10px">📍 Nueva: ${pendingLat.toFixed(6)}, ${pendingLng.toFixed(6)}</span>`)
                            .update();
                        document.getElementById('minimap-coords').textContent =
                            `${pendingLat.toFixed(6)}, ${pendingLng.toFixed(6)}`;
                    });

                    /* Redibujar al expandir/colapsar */
                    document.getElementById('minimap-panel').addEventListener('transitionend', () => {
                        map.invalidateSize();
                    });

                    window._minimapInstance = map;

                    /* — Funciones globales de edición — */
                    window.toggleEditMode = function () {
                        editMode = !editMode;
                        const banner = document.getElementById('minimap-save-banner');
                        const btn = document.getElementById('btn-edit-location');
                        if (editMode) {
                            banner.classList.remove('hidden');
                            btn.classList.add('text-rose-500');
                            btn.classList.remove('text-indigo-600');
                            btn.textContent = '✕ Editar';
                            map.getContainer().style.cursor = 'crosshair';
                            map.scrollWheelZoom.enable();
                        } else {
                            cancelEditMode();
                        }
                    };

                    window.cancelEditMode = function () {
                        editMode = false;
                        pendingLat = LAT; pendingLng = LNG;
                        marker.setLatLng([LAT, LNG]);
                        marker.getPopup()
                            .setContent(`<strong style="font-size:11px">${NAME}</strong><br>
                                                                                                                                <span style="color:#64748b;font-size:10px">${LAT.toFixed(6)}, ${LNG.toFixed(6)}</span>`)
                            .update();
                        document.getElementById('minimap-coords').textContent = `${LAT.toFixed(6)}, ${LNG.toFixed(6)}`;
                        document.getElementById('minimap-save-banner').classList.add('hidden');
                        const btn = document.getElementById('btn-edit-location');
                        btn.classList.remove('text-rose-500');
                        btn.classList.add('text-indigo-600');
                        btn.textContent = 'Editar';
                        map.getContainer().style.cursor = '';
                        map.scrollWheelZoom.disable();
                    };

                    window.saveNewCoords = async function () {
                        const btn = document.querySelector('#minimap-save-banner button:first-child');
                        btn.textContent = 'Guardando…'; btn.disabled = true;
                        try {
                            /* La URL la genera Laravel: escrita a mano desde la raíz del
                               dominio no llega, porque la app vive en un subdirectorio */
                            const res = await fetch(COORDS_URL.replace('__ID__', ESTAB_ID), {
                                method: 'PATCH',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                                body: JSON.stringify({ latitud: pendingLat, longitud: pendingLng }),
                            });
                            const data = await res.json();
                            if (data.ok) {
                                Swal.fire({
                                    target: document.getElementById('tablet-editor-container'), title: '¡Ubicación actualizada!', text: data.mensaje, icon: 'success',
                                    confirmButtonColor: '#4f46e5', timer: 2500, showConfirmButton: false
                                });
                                /* Actualizar LAT/LNG locales para cancelar correctamente después */
                                cancelEditMode();
                                /* Reiniciar el popup con las coords definitivas */
                                marker.getPopup()
                                    .setContent(`<strong style="font-size:11px">${NAME}</strong><br>
                                                                                                                                        <span style="color:#64748b;font-size:10px">${pendingLat.toFixed(6)}, ${pendingLng.toFixed(6)}</span>`)
                                    .update();
                            } else {
                                Swal.fire({ target: document.getElementById('tablet-editor-container'), title: 'Error', text: data.mensaje || 'No se pudo guardar.', icon: 'error' });
                            }
                        } catch (e) {
                            Swal.fire('Error de red', e.message, 'error');
                        } finally {
                            btn.textContent = 'Guardar'; btn.disabled = false;
                        }
                    };

                }, 100);
            });
        @endif

        /* ─── Toggle colapso del mini-mapa ─── */
        function toggleMinimap() {
            const panel = document.getElementById('minimap-panel');
            const chevron = document.getElementById('minimap-chevron');
            panel.classList.toggle('collapsed');
            chevron.style.transform = panel.classList.contains('collapsed') ? 'rotate(-90deg)' : '';
            if (window._minimapInstance) window._minimapInstance.invalidateSize();
        }

            /* ─── Drag & Move del mini-mapa ─── */
            (function () {
                const panel = document.getElementById('minimap-panel');
                const handle = document.getElementById('minimap-drag-handle');
                if (!panel || !handle) return;

                let dragging = false, startX, startY, origRight, origBottom;

                handle.addEventListener('pointerdown', function (e) {
                    e.stopPropagation();
                    dragging = true;
                    handle.setPointerCapture(e.pointerId);
                    startX = e.clientX;
                    startY = e.clientY;
                    const rect = panel.getBoundingClientRect();
                    origRight = window.innerWidth - rect.right;
                    origBottom = window.innerHeight - rect.bottom;
                    panel.style.transition = 'none';
                });

                handle.addEventListener('pointermove', function (e) {
                    if (!dragging) return;
                    const dx = e.clientX - startX;
                    const dy = e.clientY - startY;
                    let newRight = origRight - dx;
                    let newBottom = origBottom + dy;
                    /* Clamp within viewport */
                    newRight = Math.max(0, Math.min(window.innerWidth - panel.offsetWidth, newRight));
                    newBottom = Math.max(0, Math.min(window.innerHeight - panel.offsetHeight, newBottom));
                    panel.style.right = newRight + 'px';
                    panel.style.bottom = newBottom + 'px';
                    panel.style.left = 'auto';
                    panel.style.top = 'auto';
                });

                handle.addEventListener('pointerup', function () {
                    dragging = false;
                    panel.style.transition = '';
                    if (window._minimapInstance) window._minimapInstance.invalidateSize();
                });
            })();

        /* ─── Salvo en pantallas grandes, el mini-mapa arranca plegado
               para no robarle sitio al croquis ─── */
        (function () {
            const panel = document.getElementById('minimap-panel');
            if (!panel || window.innerWidth >= 1280) return;
            panel.classList.add('collapsed');
            const chevron = document.getElementById('minimap-chevron');
            if (chevron) chevron.style.transform = 'rotate(-90deg)';
        })();
    </script>

@endsection