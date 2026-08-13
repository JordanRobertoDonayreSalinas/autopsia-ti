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
    @endphp

    <script>
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
                const RH = 7; // handle half-size px

                return {
                    elements: @json($contenido['elementos'] ?? []),
                    connections: @json($contenido['conexiones'] ?? []),
                    tool: 'ambiente',
                    hwType: 'router',
                    layers: { furniture: true, network: true, power: true, calles: false },
                    tileCache: {},
                    tileOpacity: 0.5,
                    tileZoom: 21.5,
                    mapOffsetX: @json($contenido['mapOffsetX'] ?? 0),
                    mapOffsetY: @json($contenido['mapOffsetY'] ?? 0),
                    geoLat: {{ $hasCoords ? $lat : 'null' }},
                    geoLng: {{ $hasCoords ? $lng : 'null' }},
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
                    _syncUrl: '{{ route("usuario.croquis.sync", ["actaId" => $acta->id]) }}',
                    _leaveUrl: '{{ route("usuario.croquis.leave", ["actaId" => $acta->id]) }}',
                    _csrfToken: '{{ csrf_token() }}',
                    deletedIds: [],              // IDs borrados localmente para sincronizar
                    _lastColabHash: '',          // Hash del estado remoto para detect cambios
                    _toastMsg: '',               // Mensaje del toast de colaboración
                    _toastVisible: false,        // Visibilidad del toast
                    _toastTimer: null,           // Timer para auto-ocultar el toast

                    /* ─── Lifecycle ─── */
                    init() {
                        this.$nextTick(() => {
                            canvas = document.getElementById('blueprint-canvas');
                            if (!canvas) { console.error('Canvas not found'); return; }
                            ctx = canvas.getContext('2d');
                            this.resizeCanvas();
                            this.draw();
                            this._refreshIcons();
                            window.addEventListener('resize', () => this.resizeCanvas());

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
                        });

                        /* Notificar al servidor cuando el usuario cierra/navega */
                        window.addEventListener('beforeunload', () => this._leaveColab());

                        this.$watch('sidebarOpen', () => {
                            this.$nextTick(() => setTimeout(() => { this.resizeCanvas(); this._refreshIcons(); }, 350));
                        });
                        this.$watch('tool', () => this.$nextTick(() => this._refreshIcons()));
                        this.$watch('selectedId', () => this.$nextTick(() => this._refreshIcons()));
                    },

                    _refreshIcons() {
                        if (window.lucide) window.lucide.createIcons();
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

                    _isPointInElement(el, px, py, padding = 4) {
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
                        const w = type === 'hardware' ? 50 : (type === 'pasillo' ? 300 : (type === 'puerta' ? (isDoorExt ? 50 : 25) : (type === 'calle' ? calleW : (type === 'sistema' ? 80 : 120))));
                        const h = type === 'hardware' ? 40 : (type === 'pasillo' ? 60 : (type === 'puerta' ? (isDoorExt ? 50 : 25) : (type === 'calle' ? calleH : (type === 'sistema' ? 70 : 100))));
                        /* Use drop coords if provided (drag & drop), otherwise random */
                        const rx = dropX !== null
                            ? Math.max(0, Math.round((dropX - w / 2) / GRID) * GRID)
                            : Math.round((Math.random() * (lw - w - 20) + 10) / GRID) * GRID;
                        const ry = dropY !== null
                            ? Math.max(0, Math.round((dropY - h / 2) / GRID) * GRID)
                            : Math.round((Math.random() * (lh - h - 20) + 10) / GRID) * GRID;
                        const newEl = {
                            id: crypto.randomUUID(),
                            type,
                            piso: this.currentPiso,
                            subtype: type === 'ambiente' ? this.roomSubtype : (type === 'hardware' ? this.hwType : (type === 'puerta' ? this.doorSubtype : (type === 'calle' ? this.calleSubtype : (type === 'sistema' ? this.sistemaType : null)))),
                            name: this.name || (type === 'hardware' ? this.hwType.toUpperCase() : (type === 'ambiente' ? (this.roomSubtype?.toUpperCase() || 'AMBIENTE') : (type === 'puerta' ? '' : (type === 'calle' ? (this.calleSubtype === 'avenida' ? 'Av. ' : (this.calleSubtype === 'jiron' ? 'Jr. ' : 'Psj. ')) : (type === 'sistema' ? this.sistemaType.toUpperCase() : type.toUpperCase()))))),
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

                    /* ─── Auto-Fit / Centrar Vista ─── */
                    autoFit() {
                        if (this.elements.length === 0) {
                            this.resetZoom();
                            return;
                        }

                        /* Calculate Bounding Box of items */
                        let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
                        this.elements.forEach(el => {
                            minX = Math.min(minX, el.x); minY = Math.min(minY, el.y);
                            maxX = Math.max(maxX, el.x + el.w); maxY = Math.max(maxY, el.y + el.h);
                        });

                        const bW = maxX - minX;
                        const bH = maxY - minY;
                        const lw = this.logicalW;
                        const lh = this.logicalH;
                        const PAD = 60; // Logical padding

                        /* Scale to fit */
                        const scaleX = (lw - PAD * 2) / bW;
                        const scaleY = (lh - PAD * 2) / bH;
                        const newZoom = Math.min(2.0, Math.min(scaleX, scaleY));
                        this.canvasZoom = isFinite(newZoom) ? Math.max(0.1, newZoom) : 1.0;

                        /* Center it */
                        this.panX = lw / 2 - (minX + bW / 2) * this.canvasZoom;
                        this.panY = lh / 2 - (minY + bH / 2) * this.canvasZoom;

                        this.draw();
                    },

                    /* ─── Main Draw ─── */
                    draw() {
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

                        /* Infinite Grid (drawing based on visible logical area) */
                        ctx.strokeStyle = '#f1f5f9';
                        ctx.lineWidth = 1 / this.canvasZoom; /* Constant visual thickness */
                        const step = 40;
                        const xMin = Math.floor((-this.panX / this.canvasZoom) / step) * step;
                        const yMin = Math.floor((-this.panY / this.canvasZoom) / step) * step;
                        const xMax = xMin + lw / this.canvasZoom + step;
                        const yMax = yMin + lh / this.canvasZoom + step;

                        for (let x = xMin; x <= xMax; x += step) {
                            ctx.beginPath(); ctx.moveTo(x, yMin); ctx.lineTo(x, yMax); ctx.stroke();
                        }
                        for (let y = yMin; y <= yMax; y += step) {
                            ctx.beginPath(); ctx.moveTo(xMin, y); ctx.lineTo(xMax, y); ctx.stroke();
                        }

                        if (this.layers.network) this.drawConnections();

                        /* ── Ghost floor (adjacent piso at low opacity) ── */
                        if (this.showGhostFloor && this.totalPisos > 1) {
                            const ghostPiso = this.currentPiso > 1 ? this.currentPiso - 1 : this.currentPiso + 1;
                            const ghostEls = this.elements.filter(e => (e.piso || 1) === ghostPiso);
                            if (ghostEls.length > 0) {
                                ctx.save();
                                ctx.globalAlpha = 0.12;
                                ghostEls.forEach(el => {
                                    this.drawRoundedRect(el.x, el.y, el.w, el.h, 6);
                                    ctx.fillStyle = '#94a3b8';
                                    ctx.strokeStyle = '#64748b';
                                    ctx.lineWidth = 1;
                                    ctx.fill(); ctx.stroke();
                                });
                                ctx.restore();
                                /* Ghost label */
                                ctx.save();
                                ctx.globalAlpha = 0.20;
                                ctx.font = 'bold 8px Inter,Arial';
                                ctx.fillStyle = '#475569';
                                ctx.textAlign = 'center';
                                ghostEls.forEach(el => {
                                    ctx.fillText(`P${ghostPiso}`, el.x + el.w / 2, el.y + el.h / 2 + 3);
                                });
                                ctx.restore();
                            }
                        }

                        /* ── Floor watermark (centered in current view) ── */
                        ctx.save();
                        ctx.globalAlpha = 0.045;
                        const waterFS = Math.min(lw, lh) * 0.28 / this.canvasZoom;
                        ctx.font = `bold ${waterFS}px Inter,Arial`;
                        ctx.fillStyle = '#4f46e5';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        const vcx = (lw / 2 - this.panX) / this.canvasZoom;
                        const vcy = (lh / 2 - this.panY) / this.canvasZoom;
                        ctx.fillText(`P${this.currentPiso}`, vcx, vcy);
                        ctx.restore();

                        /* ── Only draw elements of the current floor ── */
                        const floorEls = this.elements.filter(e => (e.piso || 1) == this.currentPiso);

                        floorEls.forEach(el => {
                            ctx.save();
                            ctx.translate(el.x + el.w / 2, el.y + el.h / 2);
                            const rotRad = (el.rot || 0) * Math.PI / 180;
                            ctx.rotate(rotRad);
                            ctx.translate(-(el.x + el.w / 2), -(el.y + el.h / 2));

                            let fill = '#ffffff', stroke = '#475569';
                            const type = (el.type || '').toLowerCase();
                            const subtype = (el.subtype || '').toLowerCase();

                            if (type === 'ambiente') {
                                switch (subtype) {
                                    case 'consultorio':          // backward compat
                                    case 'consultorio_fisico': fill = '#bbf7d0'; stroke = '#16a34a'; break;
                                    case 'consultorio_funcional': fill = '#fef3c7'; stroke = '#d97706'; break;
                                    case 'emergencias': fill = '#fecaca'; stroke = '#dc2626'; break;
                                    case 'quirofano': fill = '#bae6fd'; stroke = '#0284c7'; break;
                                    case 'administracion': fill = '#e9d5ff'; stroke = '#9333ea'; break;
                                    case 'baño': fill = '#cffafe'; stroke = '#0891b2'; break;
                                    default: fill = '#f1f5f9'; stroke = '#94a3b8'; break;
                                }
                            } else if (type === 'pasillo') {
                                fill = '#f8fafc'; stroke = '#64748b';
                            } else if (type === 'hardware') {
                                if (subtype === 'pozo') { fill = '#d1fae5'; stroke = '#059669'; }
                                else { fill = '#dbeafe'; stroke = '#2563eb'; }
                            } else if (type === 'calle') {
                                switch (subtype) {
                                    case 'avenida': fill = '#d1d5db'; stroke = '#6b7280'; break;
                                    case 'jiron': fill = '#e5e7eb'; stroke = '#9ca3af'; break;
                                    case 'pasaje': fill = '#f3f4f6'; stroke = '#9ca3af'; break;
                                    default: fill = '#e5e7eb'; stroke = '#9ca3af'; break;
                                }
                            } else if (type === 'puerta') {
                                if (subtype === 'externa') { fill = '#fee2e2'; stroke = '#b91c1c'; }
                                else { fill = '#fef9c3'; stroke = '#ca8a04'; }
                            } else if (type === 'sistema') {
                                switch (subtype) {
                                    case 'tua': fill = '#ede9fe'; stroke = '#7c3aed'; break;
                                    case 'sihce': fill = '#dbeafe'; stroke = '#1d4ed8'; break;
                                    case 'sismed': fill = '#ccfbf1'; stroke = '#0d9488'; break;
                                    case 'hisminsa': fill = '#fed7aa'; stroke = '#c2410c'; break;
                                    case 'sisgalenplus': fill = '#dbeafe'; stroke = '#2563eb'; break;
                                    default: fill = '#f1f5f9'; stroke = '#64748b'; break;
                                }
                            }

                            ctx.shadowBlur = el.id === this.selectedId ? 15 : 4;
                            ctx.shadowColor = el.id === this.selectedId ? 'rgba(79,70,229,0.4)' : 'rgba(0,0,0,0.1)';
                            ctx.shadowOffsetY = 2;
                            ctx.lineWidth = el.id === this.selectedId ? 4 : 2.5;
                            ctx.strokeStyle = el.id === this.selectedId ? '#fbbf24' : stroke;
                            ctx.fillStyle = fill;

                            /* Consultorio funcional: borde discontinuo para diferenciarlo visualmente */
                            const isFuncional = subtype === 'consultorio_funcional';
                            if (isFuncional && el.id !== this.selectedId) {
                                ctx.setLineDash([6, 4]);
                            }

                            this.drawRoundedRect(el.x, el.y, el.w, el.h, type === 'hardware' ? 20 : 6);
                            ctx.fill(); ctx.stroke();
                            ctx.setLineDash([]);

                            ctx.shadowBlur = 0; ctx.shadowOffsetY = 0;

                            if (type === 'hardware') this.drawHardwareSymbol(el);
                            else if (type === 'puerta') this.drawDoorSymbol(el);
                            else if (type === 'calle') this.drawCalleSymbol(el);
                            else if (type === 'sistema') this.drawSistemaSymbol(el);
                            else {
                                if (this.layers.furniture) this.drawFurnitureIcons(el);
                                if (this.layers.network || this.layers.power) this.drawServiceIcons(el);
                            }

                            /* Badge FÍS / FUNC para consultorios */
                            if (type === 'ambiente' &&
                                (subtype === 'consultorio_fisico' || subtype === 'consultorio_funcional' || subtype === 'consultorio')) {
                                this.drawConsultorioBadge(el, subtype);
                            }

                            /* Label */
                            ctx.fillStyle = type === 'calle' ? '#374151' : (type === 'hardware' ? '#2563eb' : (type === 'sistema' ? '#1e1b4b' : '#1e293b'));
                            ctx.font = type === 'calle' ? 'bold 11px Inter, Arial' : 'bold 10px Inter, Arial';
                            ctx.textAlign = 'center';
                            const displayName = (el.name || subtype || type || 'Sin Nombre').toUpperCase();
                            if (type !== 'sistema' && !(type === 'puerta' && !el.name)) {
                                ctx.fillText(displayName, el.x + el.w / 2, type === 'hardware' ? el.y - 10 : el.y + (type === 'calle' ? el.h / 2 + 4 : 18));
                            }
                            ctx.restore();

                            /* ── Rotation handle (drawn outside saved transform) ── */
                            if (el.id === this.selectedId && (el.piso || 1) === this.currentPiso) {
                                const cx2 = el.x + el.w / 2;
                                const cy2 = el.y + el.h / 2;
                                const r = el.rot || 0;
                                const rRad2 = r * Math.PI / 180;
                                /* Handle sits 36px above top-center in element-local space */
                                const handleDist = el.h / 2 + 36;
                                const hx = cx2 - Math.sin(rRad2) * handleDist;
                                const hy = cy2 - Math.cos(rRad2) * handleDist;

                                /* Stem line */
                                ctx.save();
                                ctx.strokeStyle = '#4f46e5';
                                ctx.lineWidth = 1.5;
                                ctx.setLineDash([4, 3]);
                                ctx.beginPath();
                                ctx.moveTo(cx2 - Math.sin(rRad2) * (el.h / 2), cy2 - Math.cos(rRad2) * (el.h / 2));
                                ctx.lineTo(hx, hy);
                                ctx.stroke();
                                ctx.setLineDash([]);
                                ctx.restore();

                                /* Circle background */
                                ctx.save();
                                ctx.beginPath();
                                ctx.arc(hx, hy, 13, 0, Math.PI * 2);
                                ctx.fillStyle = '#4f46e5';
                                ctx.shadowBlur = 10;
                                ctx.shadowColor = 'rgba(79,70,229,0.5)';
                                ctx.fill();
                                ctx.shadowBlur = 0;
                                ctx.restore();

                                /* Rotation arrow SVG-path imitated on canvas */
                                ctx.save();
                                ctx.translate(hx, hy);
                                ctx.strokeStyle = 'white';
                                ctx.lineWidth = 2;
                                ctx.lineCap = 'round';
                                /* Arc */
                                ctx.beginPath();
                                ctx.arc(0, 0, 6, -Math.PI * 0.9, Math.PI * 0.1);
                                ctx.stroke();
                                /* Arrowhead at end of arc */
                                ctx.beginPath();
                                ctx.moveTo(6 * Math.cos(Math.PI * 0.1) - 3, 6 * Math.sin(Math.PI * 0.1) - 2);
                                ctx.lineTo(6 * Math.cos(Math.PI * 0.1) + 2, 6 * Math.sin(Math.PI * 0.1) + 3);
                                ctx.lineTo(6 * Math.cos(Math.PI * 0.1) + 4, 6 * Math.sin(Math.PI * 0.1) - 3);
                                ctx.stroke();
                                ctx.restore();

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

                        /* ── Cursores de colaboradores (fuera del zoom transform) ── */
                        this._drawRemoteCursors();
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

                    drawResizeHandles(el) {
                        const ecx = el.x + el.w / 2;
                        const ecy = el.y + el.h / 2;
                        const rot = (el.rot || 0) * Math.PI / 180;
                        const cosR = Math.cos(rot), sinR = Math.sin(rot);

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
                            ctx.lineWidth = 2;
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

                        const handles = this._resizeHandlePositions(el);
                        for (const [name, { hx, hy }] of Object.entries(handles)) {
                            if (lx >= hx - RH - 3 && lx <= hx + RH + 3 &&
                                ly >= hy - RH - 3 && ly <= hy + RH + 3) return name;
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

                    drawStreetBase(lw, lh) {
                        if (this.geoLat === null || this.geoLng === null) return;

                        const LAT = this.geoLat;
                        const LNG = this.geoLng;
                        const TILE_ZOOM = 19;   /* Max OSM native zoom */
                        const SIM_ZOOM = parseFloat(this.tileZoom);  /* Zoom simulado controlable */
                        const SCALE = Math.pow(2, SIM_ZOOM - TILE_ZOOM);
                        const T = 256;

                        /* Simplest WebMercator Projector at TILE_ZOOM */
                        const latRad = LAT * Math.PI / 180;
                        const n = Math.pow(2, TILE_ZOOM);
                        const xTileExact = (LNG + 180) / 360 * n;
                        const yTileExact = (1.0 - Math.log(Math.tan(latRad) + (1 / Math.cos(latRad))) / Math.PI) / 2.0 * n;

                        /* Center of canvas in logical zoom 19 coordinates + local offsets */
                        const cx = xTileExact * T + (this.mapOffsetX || 0);
                        const cy = yTileExact * T + (this.mapOffsetY || 0);

                        /* Range of tiles needed for logical canvas size, adjusted by SCALE */
                        const txMin = Math.floor((cx - lw / (2 * SCALE)) / T);
                        const tyMin = Math.floor((cy - lh / (2 * SCALE)) / T);
                        const txMax = Math.ceil((cx + lw / (2 * SCALE)) / T);
                        const tyMax = Math.ceil((cy + lh / (2 * SCALE)) / T);

                        console.log(`[OSM] Overzoom ${SCALE}x. Drawing ${txMax - txMin + 1}x${tyMax - tyMin + 1} tiles around ${LAT}, ${LNG}`);

                        const subs = ['a', 'b', 'c'];

                        for (let tx = txMin; tx <= txMax; tx++) {
                            for (let ty = tyMin; ty <= tyMax; ty++) {
                                const sub = subs[(tx + ty) % 3];
                                const url = `https://${sub}.tile.openstreetmap.org/${TILE_ZOOM}/${tx}/${ty}.png`;

                                /* Top left pixel of this tile scaled and centered */
                                const dx = (tx * T - cx) * SCALE + lw / 2;
                                const dy = (ty * T - cy) * SCALE + lh / 2;
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
                                        this.draw(); /* trigger redraw when loaded */
                                    };
                                    img.onerror = () => {
                                        console.error('[OSM] Failed to load tile:', url);
                                        this.tileCache[url] = null;
                                    };
                                    img.src = url;
                                }
                            }
                        }

                        /* Marcador GPS: círculo rojo en canvas center */
                        ctx.save();
                        ctx.beginPath();
                        ctx.arc(lw / 2, lh / 2, 8, 0, Math.PI * 2);
                        ctx.fillStyle = 'rgba(239,68,68,0.85)';
                        ctx.strokeStyle = 'white';
                        ctx.lineWidth = 3;
                        ctx.fill(); ctx.stroke();
                        ctx.fillStyle = 'white';
                        ctx.font = 'bold 9px Inter,Arial';
                        ctx.textAlign = 'center';
                        ctx.fillText('GPS', lw / 2, lh / 2 + 3.5);
                        ctx.restore();
                    },

                    drawConnections() {
                        if (!ctx) return;
                        ctx.strokeStyle = '#3b82f6'; ctx.setLineDash([5, 5]); ctx.lineWidth = 2;
                        this.connections.forEach(conn => {
                            const el1 = this.elements.find(e => e.id === conn.from);
                            const el2 = this.elements.find(e => e.id === conn.to);
                            if (el1 && el2) {
                                ctx.beginPath();
                                ctx.moveTo(el1.x + el1.w / 2, el1.y + el1.h / 2);
                                ctx.lineTo(el2.x + el2.w / 2, el2.y + el2.h / 2);
                                ctx.stroke();
                            }
                        });
                        if (this.isConnecting && this.connectionStart) {
                            const startEl = this.elements.find(e => e.id === this.connectionStart);
                            if (startEl) {
                                const rect = canvas.getBoundingClientRect();
                                const cx = this._lastMouseClientX - rect.left;
                                const cy = this._lastMouseClientY - rect.top;
                                ctx.beginPath();
                                ctx.moveTo(startEl.x + startEl.w / 2, startEl.y + startEl.h / 2);
                                ctx.lineTo(cx, cy);
                                ctx.stroke();
                            }
                        }
                        ctx.setLineDash([]);
                    },

                    /* ─── Furniture icons per subtype ─── */
                    drawFurnitureIcons(el) {
                        ctx.strokeStyle = '#94a3b8'; ctx.lineWidth = 1;
                        const cx = el.x + el.w / 2, cy = el.y + el.h / 2;
                        const subtype = (el.subtype || '').toLowerCase();
                        switch (subtype) {
                            case 'consultorio':          // backward compat
                            case 'consultorio_fisico':
                                /* desk (sólido) */
                                ctx.strokeStyle = '#16a34a'; ctx.lineWidth = 1.2;
                                ctx.strokeRect(el.x + 10, el.y + 30, el.w - 20, 22);
                                /* chair circle */
                                ctx.beginPath(); ctx.arc(cx, el.y + 68, 6, 0, Math.PI * 2); ctx.stroke();
                                break;

                            case 'consultorio_funcional':
                                /* mesa plegable / multipropósito: rectángulo + línea central punteada */
                                ctx.strokeStyle = '#d97706'; ctx.lineWidth = 1.2;
                                ctx.setLineDash([4, 3]);
                                ctx.strokeRect(el.x + 14, el.y + 32, el.w - 28, 18);
                                ctx.setLineDash([]);
                                /* línea central (mesa compartida) */
                                ctx.beginPath();
                                ctx.moveTo(cx, el.y + 32);
                                ctx.lineTo(cx, el.y + 50);
                                ctx.stroke();
                                /* silla izq */
                                ctx.beginPath(); ctx.arc(cx - 18, el.y + 64, 5, 0, Math.PI * 2); ctx.stroke();
                                /* silla der */
                                ctx.beginPath(); ctx.arc(cx + 18, el.y + 64, 5, 0, Math.PI * 2); ctx.stroke();
                                break;
                            case 'emergencias':
                                /* stretcher */
                                ctx.strokeRect(cx - 20, cy - 8, 40, 16);
                                ctx.beginPath(); ctx.arc(cx + 20, cy, 8, -Math.PI / 2, Math.PI / 2); ctx.stroke();
                                break;
                            case 'quirofano':
                                /* operating table */
                                ctx.strokeRect(cx - 25, cy - 6, 50, 12);
                                /* overhead lamp cross */
                                ctx.beginPath(); ctx.moveTo(cx, cy - 20); ctx.lineTo(cx, cy - 10); ctx.stroke();
                                ctx.beginPath(); ctx.moveTo(cx - 5, cy - 15); ctx.lineTo(cx + 5, cy - 15); ctx.stroke();
                                break;
                            case 'administracion':
                                /* L-desk */
                                ctx.strokeRect(el.x + 8, el.y + 28, el.w - 30, 18);
                                ctx.strokeRect(el.x + el.w - 28, el.y + 28, 20, 30);
                                break;
                            case 'baño':
                                /* toilet oval */
                                ctx.beginPath(); ctx.ellipse(cx, cy, 10, 14, 0, 0, Math.PI * 2); ctx.stroke();
                                ctx.strokeRect(cx - 10, el.y + 28, 20, 10);
                                break;
                            default:
                                /* generic table */
                                ctx.strokeRect(el.x + 10, el.y + 30, el.w - 20, el.h - 50);
                        }
                    },

                    /* ── Badge FÍS / FUNC para diferenciar consultorio físico del funcional ── */
                    drawConsultorioBadge(el, subtype) {
                        const isFuncional = subtype === 'consultorio_funcional';
                        const bgColor = isFuncional ? '#d97706' : '#16a34a';
                        const label = isFuncional ? 'FUNC' : 'FÍS';

                        ctx.save();
                        ctx.font = 'bold 7px Inter, Arial';
                        const tw = ctx.measureText(label).width;
                        const ph = 10, pw = tw + 8;
                        const bx = el.x + el.w - pw - 4;
                        const by = el.y + 4;

                        /* Pill background */
                        ctx.fillStyle = bgColor;
                        ctx.beginPath();
                        ctx.roundRect(bx, by, pw, ph, 4);
                        ctx.fill();

                        /* Text */
                        ctx.fillStyle = 'white';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(label, bx + pw / 2, by + ph / 2 + 0.5);
                        ctx.restore();
                    },


                    drawServiceIcons(el) {
                        /* Badge size and starting position (bottom-right area) */
                        const BADGE = 14;   /* badge circle radius */
                        const PAD = 6;
                        let bx = el.x + el.w - PAD - BADGE;  /* start from right */
                        const by = el.y + el.h - PAD - BADGE;

                        /* ── Lightning bolt (luz eléctrica) ── */
                        if (el.attrs?.light && this.layers.power) {
                            ctx.save();
                            /* Amber pill background */
                            ctx.beginPath();
                            ctx.arc(bx, by, BADGE, 0, Math.PI * 2);
                            ctx.fillStyle = '#f59e0b';
                            ctx.shadowBlur = 6;
                            ctx.shadowColor = 'rgba(245,158,11,0.5)';
                            ctx.fill();
                            ctx.shadowBlur = 0;
                            /* White border */
                            ctx.strokeStyle = 'white'; ctx.lineWidth = 1.5; ctx.stroke();
                            /* Lightning bolt polygon */
                            ctx.fillStyle = 'white';
                            ctx.beginPath();
                            ctx.moveTo(bx + 2, by - 7);   /* top-right tip */
                            ctx.lineTo(bx - 3, by - 0.5); /* middle-left */
                            ctx.lineTo(bx + 1, by - 0.5); /* middle-right (inner) */
                            ctx.lineTo(bx - 2, by + 7);   /* bottom-left tip */
                            ctx.lineTo(bx + 4, by + 0);   /* middle-right */
                            ctx.lineTo(bx - 0.5, by + 0);   /* middle inner */
                            ctx.closePath();
                            ctx.fill();
                            ctx.restore();
                            bx -= BADGE * 2 + PAD;
                        }

                        /* ── WiFi arcs ── */
                        if (el.attrs?.wifi && this.layers.network) {
                            ctx.save();
                            /* Blue pill background */
                            ctx.beginPath();
                            ctx.arc(bx, by, BADGE, 0, Math.PI * 2);
                            ctx.fillStyle = '#2563eb';
                            ctx.shadowBlur = 6;
                            ctx.shadowColor = 'rgba(37,99,235,0.5)';
                            ctx.fill();
                            ctx.shadowBlur = 0;
                            /* White border */
                            ctx.strokeStyle = 'white'; ctx.lineWidth = 1.5; ctx.stroke();
                            /* WiFi arcs (bottom-up: dot, small arc, medium arc) */
                            ctx.strokeStyle = 'white';
                            ctx.lineCap = 'round';
                            /* Dot */
                            ctx.beginPath();
                            ctx.arc(bx, by + 5, 1.5, 0, Math.PI * 2);
                            ctx.fillStyle = 'white'; ctx.fill();
                            /* Small arc */
                            ctx.lineWidth = 1.8;
                            ctx.beginPath();
                            ctx.arc(bx, by + 5, 4, Math.PI * 1.2, Math.PI * 1.8, false);
                            ctx.stroke();
                            /* Medium arc */
                            ctx.beginPath();
                            ctx.arc(bx, by + 5, 7.5, Math.PI * 1.2, Math.PI * 1.8, false);
                            ctx.stroke();
                            /* Large arc */
                            ctx.beginPath();
                            ctx.arc(bx, by + 5, 11, Math.PI * 1.2, Math.PI * 1.8, false);
                            ctx.stroke();
                            ctx.restore();
                            bx -= BADGE * 2 + PAD;
                        }

                        /* ── Red Cableada (Toma RJ45) ── */
                        if (el.attrs?.red > 0 && this.layers.network) {
                            ctx.save();
                            /* Emerald pill background */
                            ctx.beginPath();
                            ctx.arc(bx, by, BADGE, 0, Math.PI * 2);
                            ctx.fillStyle = '#10b981';
                            ctx.shadowBlur = 6;
                            ctx.shadowColor = 'rgba(16,185,129,0.5)';
                            ctx.fill();
                            ctx.shadowBlur = 0;
                            /* White border */
                            ctx.strokeStyle = 'white'; ctx.lineWidth = 1.5; ctx.stroke();
                            /* RJ45 square look */
                            ctx.strokeStyle = 'white'; ctx.lineWidth = 1.5;
                            ctx.strokeRect(bx - 3.5, by - 3, 7, 6);
                            ctx.beginPath();
                            ctx.moveTo(bx - 4.5, by + 3); ctx.lineTo(bx + 4.5, by + 3);
                            ctx.lineTo(bx + 4.5, by + 5); ctx.lineTo(bx - 4.5, by + 5);
                            ctx.closePath();
                            ctx.fillStyle = 'white'; ctx.fill();
                            /* Number count */
                            if (el.attrs.red > 1) {
                                ctx.font = 'bold 8px Inter, Arial';
                                ctx.fillStyle = 'white';
                                ctx.textAlign = 'center';
                                ctx.fillText(el.attrs.red, bx, by + 11.5);
                            }
                            ctx.restore();
                        }
                    },

                    drawHardwareSymbol(el) {
                        ctx.strokeStyle = '#2563eb'; ctx.lineWidth = 2.5;
                        const cx = el.x + el.w / 2, cy = el.y + el.h / 2;
                        switch ((el.subtype || '').toLowerCase()) {
                            case 'router':
                                ctx.strokeRect(cx - 15, cy - 8, 30, 16);
                                ctx.beginPath(); ctx.moveTo(cx - 10, cy - 8); ctx.lineTo(cx - 12, cy - 18); ctx.stroke();
                                ctx.beginPath(); ctx.moveTo(cx + 10, cy - 8); ctx.lineTo(cx + 12, cy - 18); ctx.stroke();
                                break;
                            case 'ap':
                                ctx.beginPath(); ctx.arc(cx, cy, 10, 0, Math.PI * 2); ctx.stroke();
                                [12, 17, 22].forEach(r => {
                                    ctx.beginPath(); ctx.arc(cx, cy, r, -Math.PI / 4, Math.PI / 4); ctx.stroke();
                                });
                                ctx.beginPath(); ctx.arc(cx, cy, 4, 0, Math.PI * 2);
                                ctx.fillStyle = '#2563eb'; ctx.fill();
                                break;
                            case 'switch':
                                ctx.strokeRect(cx - 20, cy - 6, 40, 12);
                                for (let i = 0; i < 4; i++) {
                                    ctx.beginPath(); ctx.moveTo(cx - 14 + i * 10, cy - 6); ctx.lineTo(cx - 14 + i * 10, cy - 12); ctx.stroke();
                                }
                                break;
                            case 'pozo':
                                /* Standard earth ground symbol */
                                ctx.strokeStyle = '#059669'; ctx.lineWidth = 2.5; ctx.lineCap = 'round';
                                /* Vertical stem */
                                ctx.beginPath(); ctx.moveTo(cx, cy - 12); ctx.lineTo(cx, cy - 2); ctx.stroke();
                                /* Horizontal lines (decreasing width) */
                                [0, 5, 10].forEach((off, i) => {
                                    const hw = 14 - i * 4;
                                    ctx.lineWidth = 2.5 - i * 0.5;
                                    ctx.beginPath();
                                    ctx.moveTo(cx - hw, cy - 2 + off);
                                    ctx.lineTo(cx + hw, cy - 2 + off);
                                    ctx.stroke();
                                });
                                /* Green dot at center */
                                ctx.beginPath(); ctx.arc(cx, cy - 12, 3, 0, Math.PI * 2);
                                ctx.fillStyle = '#059669'; ctx.fill();
                                break;
                            case 'punto_red':
                                /* Wall network socket symbol */
                                ctx.strokeStyle = '#10b981'; ctx.lineWidth = 2.5; ctx.lineCap = 'round';
                                /* Square frame */
                                ctx.strokeRect(cx - 10, cy - 8, 20, 16);
                                /* RJ45-like cut */
                                ctx.strokeRect(cx - 3.5, cy - 1, 7, 5);
                                ctx.beginPath();
                                ctx.moveTo(cx - 4.5, cy + 4); ctx.lineTo(cx - 4.5, cy + 6.5);
                                ctx.lineTo(cx + 4.5, cy + 6.5); ctx.lineTo(cx + 4.5, cy + 4);
                                ctx.stroke();
                                /* Plug pins */
                                ctx.lineWidth = 1;
                                [cx - 2, cx, cx + 2].forEach(px => {
                                    ctx.beginPath(); ctx.moveTo(px, cy); ctx.lineTo(px, cy + 3); ctx.stroke();
                                });
                                break;
                            default:
                                /* generic box */
                                ctx.strokeRect(cx - 12, cy - 8, 24, 16);
                        }
                    },

                    /* ── Sistema de salud icon (monitor + abbreviation badge) ── */
                    drawSistemaSymbol(el) {
                        const sub = (el.subtype || 'tua').toLowerCase();
                        const cx = el.x + el.w / 2;
                        const cy = el.y + el.h / 2 - 4;
                        const colors = {
                            tua: { bg: '#7c3aed', text: '#ede9fe' },
                            sihce: { bg: '#1d4ed8', text: '#dbeafe' },
                            sismed: { bg: '#0d9488', text: '#ccfbf1' },
                            hisminsa: { bg: '#c2410c', text: '#fed7aa' },
                            sisgalenplus: { bg: '#2563eb', text: '#dbeafe' },
                        };
                        const c = colors[sub] || { bg: '#475569', text: '#f1f5f9' };

                        /* Monitor screen */
                        const mw = el.w - 18, mh = Math.round(el.h * 0.52);
                        const mx = el.x + 9, my = el.y + 10;
                        ctx.save();
                        ctx.fillStyle = c.bg;
                        ctx.globalAlpha = 0.15;
                        ctx.beginPath();
                        ctx.roundRect(mx, my, mw, mh, 4);
                        ctx.fill();
                        ctx.globalAlpha = 1;
                        ctx.restore();

                        /* Monitor outline */
                        ctx.save();
                        ctx.strokeStyle = c.bg; ctx.lineWidth = 1.8;
                        ctx.beginPath(); ctx.roundRect(mx, my, mw, mh, 4); ctx.stroke();

                        /* Stand */
                        const standY = my + mh;
                        ctx.beginPath();
                        ctx.moveTo(cx, standY);
                        ctx.lineTo(cx, standY + 6);
                        ctx.moveTo(cx - 8, standY + 6);
                        ctx.lineTo(cx + 8, standY + 6);
                        ctx.stroke();
                        ctx.restore();

                        /* System abbreviation pill at screen center */
                        ctx.save();
                        const label = sub.toUpperCase();
                        ctx.font = 'bold 9px Inter, Arial';
                        const tw = ctx.measureText(label).width;
                        const ph = 13, pw = tw + 12;
                        const pilX = cx - pw / 2, pilY = my + mh / 2 - ph / 2;
                        ctx.fillStyle = c.bg;
                        ctx.beginPath(); ctx.roundRect(pilX, pilY, pw, ph, 6); ctx.fill();
                        ctx.fillStyle = c.text;
                        ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                        ctx.fillText(label, cx, my + mh / 2);
                        ctx.restore();
                    },

                    /* ── Calle drawing (sidewalks + lane dashes) ── */
                    drawCalleSymbol(el) {
                        const sub = (el.subtype || 'jiron').toLowerCase();
                        const cy = el.y + el.h / 2;

                        /* Aceras (sidewalks) at top and bottom edges */
                        const acera = sub === 'avenida' ? 8 : (sub === 'jiron' ? 6 : 4);
                        ctx.fillStyle = sub === 'avenida' ? '#9ca3af' : '#d1d5db';
                        ctx.fillRect(el.x, el.y, el.w, acera);
                        ctx.fillRect(el.x, el.y + el.h - acera, el.w, acera);

                        /* Center dashed line */
                        ctx.strokeStyle = sub === 'pasaje' ? '#9ca3af' : '#fbbf24';
                        ctx.lineWidth = sub === 'avenida' ? 2.5 : 1.5;
                        ctx.setLineDash(sub === 'pasaje' ? [4, 4] : [12, 8]);
                        ctx.beginPath();
                        ctx.moveTo(el.x + 10, cy);
                        ctx.lineTo(el.x + el.w - 10, cy);
                        ctx.stroke();
                        ctx.setLineDash([]);

                        /* Avenida only: secondary lane dividers */
                        if (sub === 'avenida') {
                            const off = el.h * 0.22;
                            [cy - off, cy + off].forEach(ly => {
                                ctx.strokeStyle = '#9ca3af'; ctx.lineWidth = 1;
                                ctx.setLineDash([6, 6]);
                                ctx.beginPath();
                                ctx.moveTo(el.x + 10, ly);
                                ctx.lineTo(el.x + el.w - 10, ly);
                                ctx.stroke();
                                ctx.setLineDash([]);
                            });
                        }
                    },

                    drawDoorSymbol(el) {
                        const sub = (el.subtype || 'interna').toLowerCase();
                        const isExt = sub === 'externa';
                        const cx = el.x + el.w / 2;
                        const cy = el.y + el.h / 2;

                        ctx.save();
                        if (el.rot) {
                            ctx.translate(cx, cy);
                            ctx.rotate(el.rot * Math.PI / 180);
                            ctx.translate(-cx, -cy);
                        }

                        /* ── Función auxiliar: dibuja una puerta ──
                           px,py = esquina superior-izq  pw,ph = tamaño
                           panelColor = color del panel de la hoja */
                        const _drawOneDoor = (px, py, pw, ph, panelColor, frameColor, knobSide) => {
                            const fi = Math.max(2.5, Math.min(pw, ph) * 0.07); // grosor del marco

                            /* 1. Marco exterior (negro/oscuro) */
                            ctx.fillStyle = frameColor;
                            ctx.beginPath();
                            ctx.roundRect(px, py, pw, ph, 2);
                            ctx.fill();

                            /* 2. Panel de la hoja */
                            ctx.fillStyle = panelColor;
                            ctx.beginPath();
                            ctx.roundRect(px + fi, py + fi, pw - fi * 2, ph - fi, 1.5);
                            ctx.fill();

                            /* 3. Ventana con arco
                               Centrada horizontalmente, ~40% ancho panel, empieza al 10% vert */
                            const wi = pw - fi * 2;
                            const hi = ph - fi;
                            const ww = wi * 0.42;           // ancho ventana
                            const wh = hi * 0.58;           // alto ventana
                            const wox = px + fi + wi * 0.29; // desplazamiento horizontal (ligeramente a la izq del centro)
                            const woy = py + fi + hi * 0.10; // posición vertical
                            const archR = ww / 2;           // radio del arco superior
                            const divY = woy + wh * 0.46;   // línea divisoria ventana

                            /* Clip al interior del panel para que la ventana no salga */
                            ctx.save();
                            ctx.beginPath();
                            ctx.rect(px + fi, py + fi, wi, hi);
                            ctx.clip();

                            /* Contorno negro ventana */
                            ctx.strokeStyle = frameColor;
                            ctx.lineWidth = Math.max(1, fi * 0.55);
                            ctx.fillStyle = 'rgba(235, 235, 235, 0.92)'; // gris muy claro

                            ctx.beginPath();
                            ctx.moveTo(wox, woy + archR);
                            ctx.arc(wox + archR, woy + archR, archR, Math.PI, 0);
                            ctx.lineTo(wox + ww, woy + wh);
                            ctx.lineTo(wox, woy + wh);
                            ctx.closePath();
                            ctx.fill();
                            ctx.stroke();

                            /* Línea divisoria de la ventana */
                            ctx.beginPath();
                            ctx.moveTo(wox + ctx.lineWidth / 2, divY);
                            ctx.lineTo(wox + ww - ctx.lineWidth / 2, divY);
                            ctx.stroke();

                            ctx.restore(); /* fin clip */

                            /* 4. Pomo */
                            const knobX = knobSide === 'right'
                                ? px + pw - fi * 2.4
                                : px + fi * 2.4;
                            const knobY = py + ph * 0.56;
                            const knobR = Math.max(2, fi * 0.9);

                            /* Aro oscuro */
                            ctx.fillStyle = frameColor;
                            ctx.beginPath();
                            ctx.arc(knobX, knobY, knobR * 1.45, 0, Math.PI * 2);
                            ctx.fill();
                            /* Pomo negro */
                            ctx.fillStyle = '#222';
                            ctx.beginPath();
                            ctx.arc(knobX, knobY, knobR, 0, Math.PI * 2);
                            ctx.fill();
                            /* Reflejo blanco */
                            ctx.fillStyle = 'rgba(255,255,255,0.70)';
                            ctx.beginPath();
                            ctx.arc(knobX - knobR * 0.28, knobY - knobR * 0.28, knobR * 0.38, 0, Math.PI * 2);
                            ctx.fill();
                        };

                        if (isExt) {
                            /* ══ PUERTA PRINCIPAL: DOBLE HOJA ══ */
                            const frameColor = '#111111';
                            const outerFi = Math.max(3, Math.min(el.w, el.h) * 0.065);

                            /* Marco exterior general */
                            ctx.fillStyle = frameColor;
                            ctx.beginPath();
                            ctx.roundRect(el.x, el.y, el.w, el.h, 3);
                            ctx.fill();

                            /* Divisor central */
                            const halfW = (el.w - outerFi * 3) / 2;
                            /* Hoja izquierda */
                            _drawOneDoor(
                                el.x + outerFi, el.y + outerFi,
                                halfW, el.h - outerFi,
                                '#e0e0e0', frameColor, 'right'
                            );
                            /* Hoja derecha */
                            _drawOneDoor(
                                el.x + outerFi * 2 + halfW, el.y + outerFi,
                                halfW, el.h - outerFi,
                                '#e0e0e0', frameColor, 'left'
                            );
                        } else {
                            /* ══ PUERTA INTERNA: HOJA SIMPLE ══ */
                            _drawOneDoor(
                                el.x, el.y, el.w, el.h,
                                '#ffffff', '#111111', 'right'
                            );
                        }

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
                        if (e.button === 2 || e.button === 1) { // Right or Middle click
                            this.isPanning = true;
                            canvas.style.cursor = 'grabbing';
                            this._lastMouseClientX = e.clientX;
                            this._lastMouseClientY = e.clientY;
                            return;
                        }
                        this._startInteraction(this._getEventCoords(e));
                    },
                    handleTouchStart(e) { e.preventDefault(); this._startInteraction(this._getEventCoords(e)); },

                    _startInteraction({ x, y }) {
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
                                    if (Math.sqrt(dx * dx + dy * dy) <= 14) {
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
                            const clicked = this.elements.find(el => this._isPointInElement(el, x, y));
                            if (clicked) { this.isConnecting = true; this.connectionStart = clicked.id; return; }
                        }
                        for (let i = this.elements.length - 1; i >= 0; i--) {
                            const el = this.elements[i];
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
                        }

                        this.selectedId = null; this.draw();
                    },

                    handleMouseMove(e) { this._moveInteraction(this._getEventCoords(e)); },
                    handleTouchMove(e) { e.preventDefault(); this._moveInteraction(this._getEventCoords(e)); },

                    _moveInteraction({ x, y, clientX, clientY }) {
                        const containerRect = document.getElementById('canvas-container').getBoundingClientRect();
                        this.mouseX = clientX - containerRect.left;
                        this.mouseY = clientY - containerRect.top;
                        this._lastMouseClientX = clientX;
                        this._lastMouseClientY = clientY;

                        /* ── Capturar posición para colaboración (en logical canvas coords) ── */
                        this._pendingCursorX = x;
                        this._pendingCursorY = y;

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

                        /* Rotation drag */
                        if (isRotating && rotateTarget) {
                            const angle = Math.atan2(y - rotateCenterY, x - rotateCenterX) * 180 / Math.PI;
                            let delta = angle - rotateStartAngle;
                            let newRot = ((rotateStartRot + delta) % 360 + 360) % 360;
                            rotateTarget.rot = Math.round(newRot);
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
                            dragTarget.x = Math.round((x - offset.x) / GRID) * GRID;
                            dragTarget.y = Math.round((y - offset.y) / GRID) * GRID;
                            this.draw();
                            return;
                        }

                        if (this.isPanning) {
                            const dx = clientX - this._lastMouseClientX;
                            const dy = clientY - this._lastMouseClientY;
                            this.panX += dx;
                            this.panY += dy;
                            this._lastMouseClientX = clientX;
                            this._lastMouseClientY = clientY;
                            this.draw();
                        }
                    },

                    handleMouseUp(e) { this._endInteraction(this._getEventCoords(e)); },
                    handleTouchEnd(e) {
                        e.preventDefault();
                        const coords = e.changedTouches
                            ? (() => {
                                const rect = canvas.getBoundingClientRect();
                                return { x: e.changedTouches[0].clientX - rect.left, y: e.changedTouches[0].clientY - rect.top };
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
                            const endEl = this.elements.find(el => this._isPointInElement(el, x, y));
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
                            el.rot = ((el.rot || 0) + deg + 360) % 360;
                            el._ts = Date.now();
                            this.draw();
                        }
                    },

                    setRotation(deg) {
                        const el = this.elements.find(e => e.id === this.selectedId);
                        if (el) {
                            this._snapshot();
                            el.rot = ((+deg) % 360 + 360) % 360;
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

                    deleteSelected() {
                        this._snapshot();
                        if (this.selectedId) this.deletedIds.push(this.selectedId); /* registrar para sync */
                        this.elements = this.elements.filter(e => e.id !== this.selectedId);
                        this.connections = this.connections.filter(c => c.from !== this.selectedId && c.to !== this.selectedId);
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
                        const link = document.createElement('a');
                        link.download = 'croquis-infraestructura.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                        Swal.fire({ target: document.getElementById('tablet-editor-container'), title: '¡Imagen exportada!', text: 'El croquis se descargó como PNG.', icon: 'success', confirmButtonColor: '#4f46e5', timer: 2000, showConfirmButton: false });
                    },

                    async exportPdf() {
                        /* Guardar primero (para persistir la imagen del canvas) */
                        await this.saveData();
                        /* Abrir el PDF en una nueva pestaña */
                        window.open('{{ route('usuario.monitoreo.infraestructura-2d.pdf', $acta->id) }}', '_blank');
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

                    /* ─── Save ─── */
                    async saveData() {
                        if (this.isSaving) return;
                        this.isSaving = true;

                        try {
                            const dataUrl = canvas.toDataURL('image/png');
                            const payload = {
                                contenido: {
                                    elementos: this.elements,
                                    conexiones: this.connections,
                                    totalPisos: this.totalPisos,
                                    mapOffsetX: this.mapOffsetX,
                                    mapOffsetY: this.mapOffsetY
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
                        } finally {
                            this.isSaving = false;
                        }
                    },

                    /* ═══════════════════════════════════════════════════
                       COLABORACIÓN EN TIEMPO REAL (polling cada 900ms)
                    ═══════════════════════════════════════════════════ */

                    /** Iniciar el ciclo de sincronización */
                    _startColabSync() {
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
                            }

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
            class="bg-white border-b border-slate-200 px-4 py-3 flex flex-wrap items-center justify-between gap-4 shadow-sm z-30">

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-lg flex items-center justify-center text-slate-600 transition-colors">
                        <i :data-lucide="sidebarOpen ? 'panel-left-close' : 'panel-left-open'" class="w-4 h-4"></i>
                    </button>
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white">
                        <i data-lucide="layout" class="w-5 h-5"></i>
                    </div>
                    <h1 class="text-sm font-black text-slate-800 uppercase tracking-tighter">Gestor de <span
                            class="text-indigo-600">Infraestructura</span></h1>
                </div>

                <div class="h-6 w-px bg-slate-200"></div>

                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
                    <button @click="tool = 'ambiente'"
                        :class="tool === 'ambiente' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500'"
                        class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-2">
                        <i data-lucide="square" class="w-4 h-4"></i> Ambiente
                    </button>
                    <button @click="tool = 'hardware'"
                        :class="tool === 'hardware' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500'"
                        class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-2">
                        <i data-lucide="cpu" class="w-4 h-4"></i> Equipamiento TI
                    </button>
                    <button @click="tool = 'puerta'"
                        :class="tool === 'puerta' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500'"
                        class="px-4 py-2 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-2">
                        <i data-lucide="door-open" class="w-4 h-4"></i> Puerta
                    </button>
                    <button @click="tool = 'red'"
                        :class="tool === 'red' ? 'bg-white shadow-sm text-indigo-600' : 'text-slate-500'"
                        class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-2">
                        <i data-lucide="share-2" class="w-4 h-4"></i> Cableado
                    </button>
                    <button @click="tool = 'calle'"
                        :class="tool === 'calle' ? 'bg-white shadow-sm text-emerald-600' : 'text-slate-500'"
                        class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-2">
                        <i data-lucide="map" class="w-4 h-4"></i> Calle
                    </button>
                    <button @click="tool = 'sistema'"
                        :class="tool === 'sistema' ? 'bg-white shadow-sm text-violet-600' : 'text-slate-500'"
                        class="px-3 py-1.5 rounded-lg text-[10px] font-black uppercase transition-all flex items-center gap-2">
                        <i data-lucide="monitor" class="w-4 h-4"></i> Sistemas
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-6">
                <!-- ── Badge Piso Actual ── -->
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 border border-indigo-200 rounded-xl">
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
                <div x-show="colaboradores.length === 0"
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
                <div class="flex items-center gap-4 px-4 py-2 bg-slate-50 rounded-xl border border-slate-200">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" x-model="layers.furniture" @change="draw()" class="rounded text-indigo-600">
                        <span
                            class="text-[8px] font-black uppercase text-slate-500 group-hover:text-indigo-600 transition-colors">Mobiliario</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" x-model="layers.network" @change="draw()" class="rounded text-blue-600">
                        <span
                            class="text-[8px] font-black uppercase text-slate-500 group-hover:text-blue-600 transition-colors">Internet</span>
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
                </div>

                <div class="flex items-center gap-3">
                    <button @click="toggleFullscreen()"
                        :class="isFullscreen ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-600'"
                        class="px-4 py-2 hover:bg-indigo-200 rounded-xl text-[10px] font-black uppercase transition-all flex items-center gap-2"
                        title="Pantalla Completa">
                        <i :data-lucide="isFullscreen ? 'minimize' : 'maximize'" class="w-4 h-4"></i>
                        <span x-text="isFullscreen ? 'Salir' : 'Pantalla Completa'"></span>
                    </button>
                    {{-- Botón Exportar Imagen --}}
                    <button @click="exportImage()" title="Exportar croquis como imagen PNG"
                        @mouseenter="$el.style.backgroundColor='#047857'" @mouseleave="$el.style.backgroundColor='#059669'"
                        class="px-5 py-2 text-white rounded-xl text-[10px] font-black uppercase transition-all flex items-center gap-2 shadow-lg"
                        style="background-color:#059669; box-shadow:0 4px 12px rgba(5,150,105,0.3);">
                        <i data-lucide="image" class="w-4 h-4"></i>
                        Exportar PNG
                    </button>
                    {{-- Botón Plano A1 SVG --}}
                    <button @click="exportPlano()" title="Exportar plano vectorial A1 para plotter (SVG)"
                        @mouseenter="$el.style.backgroundColor='#5b21b6'" @mouseleave="$el.style.backgroundColor='#7c3aed'"
                        class="px-5 py-2 text-white rounded-xl text-[10px] font-black uppercase transition-all flex items-center gap-2 shadow-lg"
                        style="background-color:#7c3aed; box-shadow:0 4px 12px rgba(124,58,237,0.35);">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        Plano A1
                    </button>
                    {{-- Botón Exportar PDF --}}
                    <button @click="exportPdf()" title="Guarda el croquis y genera el reporte PDF"
                        @mouseenter="$el.style.backgroundColor='#b91c1c'" @mouseleave="$el.style.backgroundColor='#dc2626'"
                        class="px-5 py-2 text-white rounded-xl text-[10px] font-black uppercase transition-all flex items-center gap-2 shadow-lg"
                        style="background-color:#dc2626; box-shadow:0 4px 12px rgba(220,38,38,0.3);">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        Exportar PDF
                    </button>
                    <button @click="saveData()" :class="isSaving ? 'btn-saving' : ''"
                        class="px-6 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase hover:bg-indigo-600 transition-all flex items-center gap-2 shadow-lg shadow-slate-200">
                        <i :data-lucide="isSaving ? 'loader' : 'save'" :class="isSaving ? 'animate-spin' : ''"
                            class="w-4 h-4"></i>
                        <span x-text="isSaving ? 'Guardando…' : 'Guardar'"></span>
                    </button>
                    <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}"
                        class="w-10 h-10 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-xl flex items-center justify-center transition-all"
                        title="Volver al Panel de Módulos">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                    </a>
                    <button @click="panelVisible = false"
                        class="w-10 h-10 bg-rose-50 hover:bg-rose-100 text-rose-500 rounded-xl flex items-center justify-center transition-all"
                        title="Minimizar herramientas">
                        <i data-lucide="minimize-2" class="w-5 h-5"></i>
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

                <!-- Panel Lateral Flotante -->
                <div x-show="sidebarOpen && panelVisible" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="-translate-x-full opacity-0"
                    x-transition:enter-end="translate-x-0 opacity-100" x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="translate-x-0 opacity-100"
                    x-transition:leave-end="-translate-x-full opacity-0"
                    class="absolute top-4 left-4 w-72 bg-white/90 backdrop-blur-xl border border-white/20 flex flex-col p-5 shadow-2xl z-40 rounded-3xl overflow-y-auto"
                    :style="panelVisible ? 'max-height: calc(100vh - 120px)' : 'max-height: calc(100vh - 40px)'">

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
                                    <button @click="addElement('ambiente')"
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
                                    class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <i data-lucide="cpu" class="w-3 h-3"></i> Equipamiento TI
                                </h2>
                                <div class="grid grid-cols-2 gap-2 mb-4">
                                    <button @click="hwType = 'router'"
                                        :class="hwType === 'router' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-400'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-2 transition-all shadow-sm">
                                        <i data-lucide="router" class="w-5 h-5"></i>
                                        <span class="text-[8px] font-black uppercase">Router</span>
                                    </button>
                                    <button @click="hwType = 'ap'"
                                        :class="hwType === 'ap' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-400'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-2 transition-all shadow-sm">
                                        <i data-lucide="rss" class="w-5 h-5"></i>
                                        <span class="text-[8px] font-black uppercase">AP</span>
                                    </button>
                                    <button @click="hwType = 'switch'"
                                        :class="hwType === 'switch' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-400'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-2 transition-all shadow-sm">
                                        <i data-lucide="layers" class="w-5 h-5"></i>
                                        <span class="text-[8px] font-black uppercase">Switch</span>
                                    </button>
                                    <button @click="hwType = 'pozo'"
                                        :class="hwType === 'pozo' ? 'bg-emerald-600 text-white shadow-emerald-200 shadow-md' : 'bg-white text-slate-400'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-2 transition-all shadow-sm border border-emerald-100">
                                        <i data-lucide="anchor" class="w-5 h-5"></i>
                                        <span class="text-[8px] font-black uppercase">Pozo tierra</span>
                                    </button>
                                    <button @click="hwType = 'punto_red'"
                                        :class="hwType === 'punto_red' ? 'bg-emerald-600 text-white shadow-emerald-200 shadow-md' : 'bg-white text-slate-400'"
                                        class="p-3 rounded-2xl flex flex-col items-center gap-2 transition-all shadow-sm border border-emerald-100">
                                        <i data-lucide="share-2" class="w-5 h-5"></i>
                                        <span class="text-[8px] font-black uppercase">Punto Red</span>
                                    </button>
                                </div>
                                <button @click="addElement('hardware')"
                                    @pointerdown="startSidebarDrag('hardware', hwType, $event)"
                                    class="w-full py-4 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase hover:bg-indigo-700 active:scale-95 transition-all shadow-lg shadow-indigo-100 cursor-grab active:cursor-grabbing">
                                    Colocar Equipo
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
                                        <!-- Puerta principal: doble hoja gris + ventanas + pomos negros -->
                                        <svg width="28" height="26" viewBox="0 0 28 26" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <!-- Marco exterior negro -->
                                            <rect x="0.5" y="0.5" width="27" height="25" rx="1.5" fill="#111" />
                                            <!-- Divisor central -->
                                            <rect x="13" y="2" width="1.5" height="23" fill="#111" />
                                            <!-- Hoja izquierda gris -->
                                            <rect x="2" y="2" width="11" height="23" rx="0.8" fill="#dddddd" />
                                            <!-- Hoja derecha gris -->
                                            <rect x="14.5" y="2" width="11" height="23" rx="0.8" fill="#dddddd" />
                                            <!-- Ventana izquierda: arco -->
                                            <rect x="4.5" y="9" width="6" height="9" rx="0" fill="rgba(220,220,220,0.95)"
                                                stroke="#111" stroke-width="0.7" />
                                            <path d="M4.5 12 A3 3 0 0 1 10.5 12" fill="rgba(200,200,200,0.95)" />
                                            <path d="M4.5 9 A3 3 0 0 1 10.5 9" stroke="#111" stroke-width="0.7"
                                                fill="none" />
                                            <line x1="5" y1="14" x2="10" y2="14" stroke="#111" stroke-width="0.7" />
                                            <!-- Ventana derecha: arco -->
                                            <rect x="17" y="9" width="6" height="9" rx="0" fill="rgba(220,220,220,0.95)"
                                                stroke="#111" stroke-width="0.7" />
                                            <path d="M17 12 A3 3 0 0 1 23 12" fill="rgba(200,200,200,0.95)" />
                                            <path d="M17 9 A3 3 0 0 1 23 9" stroke="#111" stroke-width="0.7" fill="none" />
                                            <line x1="17.5" y1="14" x2="22.5" y2="14" stroke="#111" stroke-width="0.7" />
                                            <!-- Pomo izquierdo -->
                                            <circle cx="11.5" cy="15" r="1.6" fill="#111" />
                                            <circle cx="11.5" cy="15" r="0.8" fill="#555" />
                                            <!-- Pomo derecho -->
                                            <circle cx="16" cy="15" r="1.6" fill="#111" />
                                            <circle cx="16" cy="15" r="0.8" fill="#555" />
                                        </svg>
                                        <span class="text-[8px] font-black uppercase">Principal</span>
                                        <span class="text-[7px] text-center leading-tight opacity-70">2 hojas · acceso
                                            calle</span>
                                    </button>
                                </div>

                                <!-- Descripción contextual -->
                                <div class="mb-4 p-3 rounded-xl text-[8px] leading-relaxed"
                                    :class="doorSubtype === 'externa' ? 'bg-red-50 text-red-700 border border-red-100' : 'bg-amber-50 text-amber-700 border border-amber-100'">
                                    <span x-show="doorSubtype === 'interna'">🚪 Puerta simple de una hoja. Para pasillos,
                                        consultorios y ambientes interiores.</span>
                                    <span x-show="doorSubtype === 'externa'">🔒 Puerta principal de doble hoja. Acceso desde
                                        la calle, con indicador de seguridad.</span>
                                </div>

                                <button @click="addElement('puerta')"
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

                                <button @click="addElement('calle')"
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

                                <button @click="addElement('sistema')"
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

                <canvas id="blueprint-canvas" @mousedown="handleMouseDown" @mousemove="handleMouseMove"
                    @mouseup="handleMouseUp" @touchstart="handleTouchStart" @touchmove="handleTouchMove"
                    @touchend="handleTouchEnd" @contextmenu.prevent="confirmDelete($event)" @dragover.prevent
                    @drop="handleDrop($event)">
                </canvas>

                <!-- ══════════════════════════════════════════════════════ -->
                <!-- Panel Flotante de Pisos (derecha del canvas)          -->
                <!-- ══════════════════════════════════════════════════════ -->
                <div class="absolute top-4 right-4 z-40 flex flex-col items-center gap-0 select-none"
                    style="filter: drop-shadow(0 8px 24px rgba(79,70,229,0.18));">

                    <!-- Botón Añadir Piso -->
                    <button @click="addPiso()" title="Añadir piso"
                        class="w-12 h-10 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-t-2xl flex items-center justify-center transition-all shadow-lg shadow-indigo-200 border-b border-indigo-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                    </button>

                    <!-- Lista de pisos (mayor arriba, 1 abajo — como un edificio) -->
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
                                class="w-12 flex flex-col items-center justify-center py-2 transition-all duration-150 border-b border-slate-100 relative">
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

                    <!-- Botón Quitar Piso -->
                    <button @click="removePiso()" :disabled="totalPisos <= 1" title="Eliminar piso actual"
                        class="w-12 h-10 bg-rose-50 hover:bg-rose-100 active:scale-95 text-rose-400 hover:text-rose-600 rounded-b-2xl flex items-center justify-center transition-all border-t border-rose-100 disabled:opacity-30 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path d="M5 12h14" />
                        </svg>
                    </button>

                    <!-- Toggle Ghost Floor -->
                    <button @click="showGhostFloor = !showGhostFloor; draw()" x-show="totalPisos > 1"
                        :title="showGhostFloor ? 'Ocultar silueta del piso adyacente' : 'Mostrar silueta del piso adyacente'"
                        :class="showGhostFloor ? 'bg-indigo-100 text-indigo-600 border-indigo-200' : 'bg-white/80 text-slate-400 border-slate-200'"
                        class="mt-2 w-12 h-10 border rounded-2xl flex flex-col items-center justify-center gap-0.5 transition-all hover:scale-105 active:scale-95 shadow-sm">
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
                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-40 flex items-center gap-3
                                                                                         bg-white/90 backdrop-blur-xl border border-slate-200 rounded-2xl shadow-2xl px-4 py-2.5"
                    style="pointer-events:auto;">

                    <!-- Zoom -->
                    <div class="flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <circle cx="11" cy="11" r="8" />
                            <path d="M21 21l-4.35-4.35" />
                        </svg>
                        <button @click="zoomOut()" title="Reducir zoom"
                            class="w-6 h-6 bg-slate-100 hover:bg-indigo-100 text-slate-600 hover:text-indigo-600 rounded-lg flex items-center justify-center font-black text-sm transition-all active:scale-90">
                            −
                        </button>
                        <button @click="resetZoom()" title="Resetear zoom (100%)"
                            class="min-w-[42px] h-6 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-[9px] font-black uppercase transition-all active:scale-90"
                            x-text="Math.round(canvasZoom * 100) + '%'">
                        </button>
                        <button @click="zoomIn()" title="Aumentar zoom"
                            class="w-6 h-6 bg-slate-100 hover:bg-indigo-100 text-slate-600 hover:text-indigo-600 rounded-lg flex items-center justify-center font-black text-sm transition-all active:scale-90">
                            +
                        </button>
                        <button @click="autoFit()" title="Centrar todos los elementos (Ajustar vista)"
                            class="ml-1 w-8 h-6 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center justify-center transition-all active:scale-90 shadow-md shadow-indigo-200">
                            <i data-lucide="focus" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>

                    <!-- Separador -->
                    <div class="w-px h-5 bg-slate-200"></div>

                    <!-- Opacidad -->
                    <div class="flex items-center gap-2">
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

                <!-- Tooltip flotante -->
                <div x-show="hoveredEl" :style="`left: ${mouseX + 20}px; top: ${mouseY + 20}px`"
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
                    ).openPopup();

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
                            const res = await fetch(`/establecimientos/${ESTAB_ID}/coordenadas`, {
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
    </script>

@endsection