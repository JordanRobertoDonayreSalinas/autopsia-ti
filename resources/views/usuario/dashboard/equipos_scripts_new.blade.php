<script>
    // ============================================
    // DASHBOARD DE EQUIPOS DE CÓMPUTO - CHART.JS
    // ============================================

    console.log('Dashboard Equipos - Script cargado');

    // Variables globales para los gráficos
    let chartEstado = null;
    let chartTipo = null;
    let chartModulo = null;
    let chartDescripcion = null;
    let chartEstablecimiento = null;
    let chartConectividad = null;
    let chartFuenteWifi = null;
    let chartProveedor = null;
    let debounceTimer = null;

    let filtrosDebounceTimer = null;

    // Configuración de colores profesionales para presentaciones
    const THEME = {
        primary: 'rgba(79, 70, 229, 0.85)',   // Indigo
        success: 'rgba(16, 185, 129, 0.85)',   // Emerald
        warning: 'rgba(245, 158, 11, 0.85)',   // Amber
        danger: 'rgba(239, 68, 68, 0.85)',    // Rose
        info: 'rgba(14, 165, 233, 0.85)',      // Sky
        secondary: 'rgba(100, 116, 139, 0.85)',// Slate
        purple: 'rgba(139, 92, 246, 0.85)',    // Violet
        fuchsia: 'rgba(217, 70, 239, 0.85)',   // Fuchsia
        teal: 'rgba(20, 184, 166, 0.85)',      // Teal
        palettes: {
            hardware: ['rgba(79, 70, 229, 0.8)', 'rgba(139, 92, 246, 0.8)', 'rgba(217, 70, 239, 0.8)', 'rgba(244, 63, 94, 0.8)'],
            connectivity: ['rgba(59, 130, 246, 0.8)', 'rgba(20, 184, 166, 0.8)', 'rgba(16, 185, 129, 0.8)'],
            status: {
                'OPERATIVO': 'rgba(34, 197, 94, 0.85)',
                'REGULAR': 'rgba(251, 146, 60, 0.85)',
                'INOPERATIVO': 'rgba(239, 68, 68, 0.85)',
                'Sin Estado': 'rgba(148, 163, 184, 0.85)'
            }
        }
    };

    // Plugin personalizado para mostrar el total en el centro (Dona)
    const centerTextPlugin = {
        id: 'centerText',
        afterDraw: (chart) => {
            if (chart.config.type !== 'doughnut') return;
            const { ctx, chartArea } = chart;
            if (!chartArea) return;

            const centerX = (chartArea.left + chartArea.right) / 2;
            const centerY = (chartArea.top + chartArea.bottom) / 2;

            // Calcular total de forma segura
            let total = 0;
            try {
                if (chart.data.datasets && chart.data.datasets.length > 0 && chart.data.datasets[0].data) {
                    total = chart.data.datasets[0].data.reduce((sum, val) => sum + (Number(val) || 0), 0);
                }
            } catch (e) { total = 0; }

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            // Dibujar número total (valor principal)
            ctx.font = 'bold 24px "Inter", sans-serif';
            ctx.fillStyle = '#1e293b'; 
            ctx.fillText(total, centerX, centerY - 8);

            // Dibujar etiqueta (subtítulo)
            ctx.font = '800 9px "Inter", sans-serif';
            ctx.fillStyle = '#94a3b8';
            ctx.fillText('EQUIPOS TOTALES', centerX, centerY + 16);
            ctx.restore();
        }
    };

    // Registrar plugin globalmente
    if (window.Chart) {
        Chart.register(centerTextPlugin);
    }

    // Opciones base para gráficos de Dona (Presentación)
    const doughnutBaseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: { size: 11, weight: '600' }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)',
                padding: 12,
                titleFont: { size: 14 },
                bodyFont: { size: 13 },
                cornerRadius: 8,
                displayColors: true
            }
        },
        animation: {
            animateScale: true,
            animateRotate: true,
            duration: 1500,
            easing: 'easeOutQuart'
        }
    };

    // ============================================
    // FUNCIÓN PRINCIPAL: CARGAR ESTADÍSTICAS
    // ============================================
    function cargarEstadisticas() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            console.log('Cargando estadísticas...');
            mostrarCargando(true);

            const mes = document.getElementById('eq_mes')?.value || '';
            const anio = document.getElementById('eq_anio')?.value || '{{ now()->year }}';
            const tipo = document.getElementById('eq_tipo')?.value || '';
            const provincia = document.getElementById('eq_provincia')?.value || '';
            const distrito = document.getElementById('eq_distrito')?.value || '';
            const establecimientoId = document.getElementById('eq_establecimiento')?.value || '';
            const descripcion = document.getElementById('eq_descripcion')?.value || '';

            const checkboxes = document.querySelectorAll('.eq_modulo_checkbox:checked');
            const modulosSeleccionados = Array.from(checkboxes).map(cb => cb.value);

            const params = new URLSearchParams({
                mes: mes, anio: anio, tipo: tipo, provincia: provincia, distrito: distrito,
                establecimiento_id: establecimientoId, descripcion: descripcion
            });

            modulosSeleccionados.forEach(modulo => {
                if (modulo) params.append('modulos[]', modulo);
            });

            const url = '{{ route("usuario.dashboard.ajax.equipos.stats") }}?' + params.toString();
            
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
                    return response.json();
                })
                .then(data => {
                    actualizarDashboard(data);
                    mostrarCargando(false);
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarError('Error al cargar las estadísticas.');
                    mostrarCargando(false);
                });
        }, 500);
    }

    function actualizarDashboard(data) {
        // --- ACTUALIZAR KPIs ---
        if (document.getElementById('eq_kpi_total')) {
            document.getElementById('eq_kpi_total').textContent = data.totalEquipos || '0';
        }
        
        if (document.getElementById('eq_kpi_operativos')) {
            let operativos = (data.equiposPorEstado && data.equiposPorEstado['OPERATIVO']) ? data.equiposPorEstado['OPERATIVO'] : 0;
            let pctOperativos = data.totalEquipos > 0 ? Math.round((operativos / data.totalEquipos) * 100) : 0;
            document.getElementById('eq_kpi_operativos').textContent = operativos;
            document.getElementById('eq_kpi_operativos_pct').textContent = `(${pctOperativos}%)`;
        }
        
        if (document.getElementById('eq_kpi_sedes')) {
            let sedesCount = Object.keys(data.equiposPorEstablecimiento || {}).length;
            document.getElementById('eq_kpi_sedes').textContent = sedesCount;
        }

        if (document.getElementById('eq_kpi_nolan')) {
            let sinRed = (data.equiposPorConectividad && data.equiposPorConectividad['SIN RED']) ? data.equiposPorConectividad['SIN RED'] : 0; 
            let noAplica = (data.equiposPorConectividad && data.equiposPorConectividad['NO APLICA']) ? data.equiposPorConectividad['NO APLICA'] : 0;
            document.getElementById('eq_kpi_nolan').textContent = sinRed + noAplica;
        }

        // --- ACTUALIZAR GRÁFICOS ---
        try { renderizarGraficoEstado(data.equiposPorEstado || {}); } catch (e) { console.error(e); }
        try { renderizarGraficoTipo(data.equiposPorTipo || {}); } catch (e) { console.error(e); }
        try { chartModulo = renderizarBarrasHorizontales('chartModulo', data.equiposPorModulo || {}, chartModulo, THEME.purple); } catch (e) { console.error(e); }
        try { chartDescripcion = renderizarBarrasHorizontales('chartDescripcion', data.topDescripciones || {}, chartDescripcion, THEME.warning); } catch (e) { console.error(e); }
        try { renderizarGraficoEstablecimiento(data.equiposPorEstablecimiento || {}); } catch (e) { console.error(e); }
        
        // --- ACTUALIZAR BARRAS HTML ---
        try { renderizarBarrasProgreso('html_conectividad', data.equiposPorConectividad || {}); } catch (e) { console.error(e); }
        try { renderizarBarrasProgreso('html_fuente_wifi', data.equiposPorFuenteWifi || {}); } catch (e) { console.error(e); }
        try { renderizarBarrasProgreso('html_proveedor', data.equiposPorProveedor || {}); } catch (e) { console.error(e); }
    }

    // ============================================
    // FUNCIONES DE RENDERIZACIÓN
    // ============================================

    function renderizarGraficoEstado(datos) {
        console.log('Grafico Estado:', datos);
        const ctx = document.getElementById('chartEstado');
        if (!ctx) return;
        if (chartEstado) chartEstado.destroy();

        const labels = Object.keys(datos);
        const values = Object.values(datos);
        const colors = labels.map(label => THEME.palettes.status[label] || THEME.palettes.status['Sin Estado']);

        chartEstado = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    hoverOffset: 15, borderWidth: 3, borderColor: '#ffffff'
                }]
            },
            options: doughnutBaseOptions
        });
    }

    function renderizarGraficoTipo(datos) {
        const ctx = document.getElementById('chartTipo');
        if (!ctx) return;
        if (chartTipo) chartTipo.destroy();

        chartTipo = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(datos),
                datasets: [{
                    data: Object.values(datos),
                    backgroundColor: [THEME.primary, THEME.purple, THEME.fuchsia, THEME.teal],
                    hoverOffset: 15, borderWidth: 3, borderColor: '#ffffff'
                }]
            },
            options: doughnutBaseOptions
        });
    }

    function renderizarBarrasHorizontales(ctxId, dataObj, chartRef, colorPrincipal) {
        const ctx = document.getElementById(ctxId);
        if (!ctx) return chartRef;
        if (chartRef) chartRef.destroy();

        let entries = Object.entries(dataObj).sort((a,b) => b[1] - a[1]).slice(0, 10); // Mostrar Top 10
        let labels = entries.map(e => e[0].length > 22 ? e[0].substring(0,19)+'...' : e[0]);
        let values = entries.map(e => e[1]);

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colorPrincipal,
                    borderRadius: 4,
                    barThickness: 14,
                    hoverBackgroundColor: THEME.primary
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)', padding: 10, cornerRadius: 8, displayColors: false,
                        callbacks: { title: () => '', label: (context) => ` ${entries[context.dataIndex][0]}: ${context.raw} equipos` }
                    }
                },
                scales: {
                    x: { display: false, beginAtZero: true },
                    y: {
                        grid: { display: false },
                        ticks: { color: '#475569', font: { size: 10, weight: '600' } }
                    }
                }
            }
        });
    }

    function renderizarBarrasProgreso(containerId, dataObj) {
        const container = document.getElementById(containerId);
        if (!container) return;
        
        let total = Object.values(dataObj).reduce((sum, val) => sum + val, 0);
        if (total === 0) {
            container.innerHTML = '<p class="text-xs text-slate-400">Sin datos registrados</p>';
            return;
        }

        let html = '';
        let entries = Object.entries(dataObj).sort((a,b) => b[1] - a[1]).slice(0, 5); // Mostrar Top 5 para conectividad
        const colors = ['bg-indigo-500', 'bg-emerald-500', 'bg-amber-400', 'bg-cyan-500', 'bg-slate-400'];

        entries.forEach(([label, count], index) => {
            let pct = Math.round((count / total) * 100);
            let bgClass = colors[index % colors.length];
            
            html += `
            <div class="mb-3 last:mb-0">
                <div class="flex justify-between items-baseline text-[10px] font-bold text-slate-600 mb-1.5 uppercase tracking-wide">
                    <span class="truncate max-w-[70%] text-slate-800" title="${label}">${label}</span>
                    <span>${count} <span class="text-slate-400 font-normal">(${pct}%)</span></span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5">
                    <div class="${bgClass} h-1.5 rounded-full transition-all duration-1000" style="width: ${pct}%"></div>
                </div>
            </div>`;
        });
        
        container.innerHTML = html;
    }

    function renderizarGraficoEstablecimiento(data) {
        const ctx = document.getElementById('chartEstablecimiento');
        if (!ctx) return;
        if (chartEstablecimiento) chartEstablecimiento.destroy();

        const dataArray = Object.entries(data).map(([nombre, cantidad]) => ({ nombre, cantidad }));
        dataArray.sort((a, b) => b.cantidad - a.cantidad);

        const labels = dataArray.map(item => item.nombre);
        const valores = dataArray.map(item => item.cantidad);

        const container = ctx.parentNode;
        const totalItems = dataArray.length;
        container.style.height = totalItems > 12 ? `${totalItems * 30}px` : '384px';

        chartEstablecimiento = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Equipos',
                    data: valores,
                    backgroundColor: THEME.info,
                    hoverBackgroundColor: THEME.primary,
                    borderRadius: 5, barThickness: 18
                }]
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        padding: 12, cornerRadius: 8, displayColors: false,
                        callbacks: { label: (ctx) => ` ${ctx.parsed.x} Equipos` }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                        ticks: { font: { size: 11, weight: '600' }, color: '#64748b' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            color: '#334155', font: { size: 11, weight: '700' },
                            callback: function(v) {
                                const l = this.getLabelForValue(v);
                                return l.length > 25 ? l.substring(0, 22) + '...' : l;
                            }
                        }
                    }
                }
            }
        });
    }

    // ============================================
    // FUNCIONES DE UI Y FILTROS
    // ============================================

    function mostrarCargando(mostrar) {
        const btn = document.getElementById('btnAplicarFiltrosEquipos');
        if (!btn) return;

        if (mostrar) {
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-wait');
            btn.innerHTML = `
                <div class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Analizando...</span>
                </div>`;
        } else {
            btn.disabled = false;
            btn.classList.remove('opacity-80', 'cursor-wait');
            btn.innerHTML = '<i data-lucide="search" class="w-4 h-4 inline-block mr-2"></i> Aplicar Filtros';
            if (window.lucide) window.lucide.createIcons();
        }
    }

    function mostrarError(mensaje) {
        console.error(mensaje);
        alert(mensaje);
    }

    function actualizarFiltros() {
        clearTimeout(filtrosDebounceTimer);
        filtrosDebounceTimer = setTimeout(() => {
            const mes = document.getElementById('eq_mes')?.value || '';
            const anio = document.getElementById('eq_anio')?.value || '{{ now()->year }}';
            const tipo = document.getElementById('eq_tipo')?.value || '';
            const provincia = document.getElementById('eq_provincia')?.value || '';
            const distrito = document.getElementById('eq_distrito')?.value || '';
            const establecimientoId = document.getElementById('eq_establecimiento')?.value || '';
            const checkboxes = document.querySelectorAll('.eq_modulo_checkbox:checked');
            const modulosSeleccionados = Array.from(checkboxes).map(cb => cb.value);

            const params = new URLSearchParams({ mes, anio, tipo, provincia, distrito, establecimiento_id: establecimientoId });
            modulosSeleccionados.forEach(m => params.append('modulos[]', m));

            fetch('{{ route("usuario.dashboard.ajax.equipos.filter-options") }}?' + params.toString())
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        actualizarSelectProvincias(data.provincias);
                        actualizarSelectDistritos(data.distritos || []);
                        actualizarSelectEstablecimientos(data.establecimientos);
                        actualizarCheckboxesModulos(data.modulos);
                        actualizarSelectDescripciones(data.descripciones);
                    }
                });
        }, 300);
    }

    function actualizarSelectProvincias(provincias) {
        const select = document.getElementById('eq_provincia');
        if (!select) return;
        const val = select.value;
        select.innerHTML = '<option value="">Todas</option>';
        provincias.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p; opt.textContent = p;
            if (p === val) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function actualizarSelectDistritos(distritos) {
        const select = document.getElementById('eq_distrito');
        if (!select) return;
        const val = select.value;
        select.innerHTML = '<option value="">Todos</option>';
        distritos.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d; opt.textContent = d;
            if (d === val) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function actualizarSelectEstablecimientos(establecimientos) {
        const select = document.getElementById('eq_establecimiento');
        if (!select) return;
        const val = select.value;
        select.innerHTML = '<option value="">Todos</option>';
        establecimientos.forEach(e => {
            const opt = document.createElement('option');
            opt.value = e.id; opt.textContent = e.nombre;
            if (e.id == val) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function actualizarCheckboxesModulos(modulos) {
        const gridContainer = document.querySelector('.eq_modulo_checkbox')?.closest('.grid') || 
                            document.querySelector('input[name="modulos[]"]')?.closest('.grid');
        if (!gridContainer) return;

        const seleccionados = Array.from(document.querySelectorAll('.eq_modulo_checkbox:checked')).map(cb => cb.value);
        gridContainer.innerHTML = '';

        modulos.forEach(mod => {
            const esEspecializado = mod.valor.endsWith('_esp') || mod.valor.startsWith('sm_');
            const label = document.createElement('label');
            label.className = `flex items-center gap-2 p-2 rounded-lg hover:bg-white transition-all cursor-pointer group ${esEspecializado ? 'border-l-2 border-purple-400' : ''}`;

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox'; checkbox.name = 'modulos[]'; checkbox.value = mod.valor;
            checkbox.className = 'eq_modulo_checkbox w-4 h-4 rounded cursor-pointer ' + (esEspecializado ? 'text-purple-600' : 'text-indigo-600');
            if (seleccionados.includes(mod.valor)) checkbox.checked = true;
            checkbox.addEventListener('change', actualizarContador);

            const span = document.createElement('span');
            span.className = `text-sm transition-colors ${esEspecializado ? 'text-purple-700 font-medium' : 'text-slate-700'}`;
            span.textContent = mod.nombre;

            label.appendChild(checkbox);
            label.appendChild(span);
            if (esEspecializado) {
                const badge = document.createElement('span');
                badge.className = 'ml-auto text-xs px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full font-semibold';
                badge.textContent = 'ESP';
                label.appendChild(badge);
            }
            gridContainer.appendChild(label);
        });
        actualizarContador();
    }

    function actualizarSelectDescripciones(descripciones) {
        const select = document.getElementById('eq_descripcion');
        if (!select) return;
        const val = select.value;
        select.innerHTML = '<option value="">Todas</option>';
        descripciones.forEach(desc => {
            const opt = document.createElement('option');
            opt.value = desc; opt.textContent = desc;
            if (desc === val) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function actualizarContador() {
        const count = document.querySelectorAll('.eq_modulo_checkbox:checked').length;
        const contador = document.getElementById('contadorModulos');
        if (contador) {
            contador.textContent = count === 0 ? '0 seleccionados' : count === 1 ? '1 seleccionado' : `${count} seleccionados`;
        }
    }

    // ============================================
    // INICIALIZACIÓN
    // ============================================
    document.addEventListener('DOMContentLoaded', () => {
        cargarEstadisticas();
        
        document.getElementById('btnAplicarFiltrosEquipos')?.addEventListener('click', (e) => {
            e.preventDefault();
            cargarEstadisticas();
        });

        document.getElementById('btnSeleccionarTodos')?.addEventListener('click', () => {
            document.querySelectorAll('.eq_modulo_checkbox').forEach(cb => cb.checked = true);
            actualizarContador();
        });

        document.getElementById('btnLimpiarModulos')?.addEventListener('click', () => {
            document.querySelectorAll('.eq_modulo_checkbox').forEach(cb => cb.checked = false);
            actualizarContador();
        });

        ['eq_mes', 'eq_anio', 'eq_tipo', 'eq_provincia', 'eq_distrito', 'eq_establecimiento'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', actualizarFiltros);
        });
    });
</script>