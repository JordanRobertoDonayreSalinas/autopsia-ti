<script>
    // ============================================
    // PANEL DE INDICADORES - CHART.JS
    // ============================================

    let indChartConsultorioTipo = null;
    let indChartConsultorioDepartamento = null;
    let indChartRrhhServicio = null;
    let indChartConectividadTipo = null;
    let indDebounceTimer = null;
    let indFiltrosDebounceTimer = null;

    const IND_THEME = {
        primary: 'rgba(79, 70, 229, 0.85)',
        success: 'rgba(16, 185, 129, 0.85)',
        warning: 'rgba(245, 158, 11, 0.85)',
        danger: 'rgba(239, 68, 68, 0.85)',
        info: 'rgba(14, 165, 233, 0.85)',
        purple: 'rgba(139, 92, 246, 0.85)',
        fuchsia: 'rgba(217, 70, 239, 0.85)',
        teal: 'rgba(20, 184, 166, 0.85)',
    };

    const IND_DOUGHNUT_OPTIONS = {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { usePointStyle: true, padding: 16, font: { size: 11, weight: '600' } }
            },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.95)', padding: 12, cornerRadius: 8, displayColors: true
            }
        },
        animation: { animateScale: true, animateRotate: true, duration: 1200, easing: 'easeOutQuart' }
    };

    function indRenderizarBarrasHorizontales(ctxId, dataObj, chartRef, color) {
        const ctx = document.getElementById(ctxId);
        if (!ctx) return chartRef;
        if (chartRef) chartRef.destroy();

        let entries = Object.entries(dataObj).sort((a, b) => b[1] - a[1]).slice(0, 10);
        let labels = entries.map(e => e[0].length > 24 ? e[0].substring(0, 21) + '...' : e[0]);
        let values = entries.map(e => e[1]);

        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{ data: values, backgroundColor: color, borderRadius: 4, barThickness: 14, hoverBackgroundColor: IND_THEME.primary }]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.95)', padding: 10, cornerRadius: 8, displayColors: false,
                        callbacks: { title: () => '', label: (context) => ` ${entries[context.dataIndex][0]}: ${context.raw}` }
                    }
                },
                scales: {
                    x: { display: false, beginAtZero: true },
                    y: { grid: { display: false }, ticks: { color: '#475569', font: { size: 10, weight: '600' } } }
                }
            }
        });
    }

    function indRenderizarBarrasProgreso(containerId, dataObj) {
        const container = document.getElementById(containerId);
        if (!container) return;

        let total = Object.values(dataObj).reduce((sum, val) => sum + val, 0);
        if (total === 0) {
            container.innerHTML = '<p class="text-xs text-slate-400">Sin datos registrados</p>';
            return;
        }

        let html = '';
        let entries = Object.entries(dataObj).sort((a, b) => b[1] - a[1]).slice(0, 5);
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

    function indCargarEstadisticas() {
        clearTimeout(indDebounceTimer);
        indDebounceTimer = setTimeout(() => {
            const btn = document.getElementById('btnAplicarFiltrosIndicadores');
            if (btn) { btn.disabled = true; btn.classList.add('opacity-70', 'cursor-wait'); }

            const params = new URLSearchParams({
                fecha_inicio: document.getElementById('ind_fecha_inicio')?.value || '',
                fecha_fin: document.getElementById('ind_fecha_fin')?.value || '',
                tipo: document.getElementById('ind_tipo')?.value || '',
                provincia: document.getElementById('ind_provincia')?.value || '',
                distrito: document.getElementById('ind_distrito')?.value || '',
                establecimiento_id: document.getElementById('ind_establecimiento')?.value || '',
            });

            fetch('{{ route("usuario.dashboard.ajax.indicadores.stats") }}?' + params.toString())
                .then(r => {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(data => indActualizarDashboard(data))
                .catch(err => console.error('Error al cargar indicadores:', err))
                .finally(() => {
                    if (btn) { btn.disabled = false; btn.classList.remove('opacity-70', 'cursor-wait'); }
                });
        }, 300);
    }

    function indActualizarDashboard(data) {
        const periodoEl = document.getElementById('ind_periodo_texto');
        if (periodoEl) periodoEl.textContent = 'Periodo: ' + (data.periodoTexto || '');

        // --- Consultorios ---
        const c = data.consultorios || {};
        document.getElementById('ind_kpi_total_consultorios').textContent = c.total ?? 0;
        document.getElementById('ind_kpi_fisico').textContent = c.fisico ?? 0;
        document.getElementById('ind_kpi_funcional').textContent = c.funcional ?? 0;

        const alertas = c.alertas || {};
        document.getElementById('ind_alerta_electricidad').textContent = alertas['SIN ELECTRICIDAD'] ?? 0;
        document.getElementById('ind_alerta_conectividad').textContent = alertas['SIN CONECTIVIDAD'] ?? 0;
        document.getElementById('ind_alerta_equipos').textContent = alertas['SIN EQUIPOS DE CÓMPUTO'] ?? 0;
        document.getElementById('ind_alerta_puntos_red').textContent = alertas['REQUIERE MÁS PUNTOS DE RED'] ?? 0;

        const tipoCtx = document.getElementById('chartConsultorioTipo');
        if (tipoCtx) {
            if (indChartConsultorioTipo) indChartConsultorioTipo.destroy();
            indChartConsultorioTipo = new Chart(tipoCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Físico', 'Funcional'],
                    datasets: [{ data: [c.fisico ?? 0, c.funcional ?? 0], backgroundColor: [IND_THEME.success, IND_THEME.purple], hoverOffset: 12, borderWidth: 3, borderColor: '#ffffff' }]
                },
                options: IND_DOUGHNUT_OPTIONS
            });
        }
        try { indChartConsultorioDepartamento = indRenderizarBarrasHorizontales('chartConsultorioDepartamento', c.porDepartamento || {}, indChartConsultorioDepartamento, IND_THEME.purple); } catch (e) { console.error(e); }

        // --- RR.HH ---
        const rrhh = data.rrhh || {};
        const totalRrhh = rrhh.total ?? 0;
        document.getElementById('ind_kpi_total_rrhh').textContent = totalRrhh;
        document.getElementById('ind_kpi_dnie').textContent = rrhh.conDnie ?? 0;
        document.getElementById('ind_kpi_dnie_pct').textContent = totalRrhh > 0 ? `(${Math.round((rrhh.conDnie ?? 0) / totalRrhh * 100)}%)` : '';
        document.getElementById('ind_kpi_serums').textContent = rrhh.serums ?? 0;
        document.getElementById('ind_kpi_serums_pct').textContent = totalRrhh > 0 ? `(${Math.round((rrhh.serums ?? 0) / totalRrhh * 100)}%)` : '';
        document.getElementById('ind_kpi_sin_colegiatura').textContent = rrhh.sinColegiatura ?? 0;
        document.getElementById('ind_kpi_sin_colegiatura_pct').textContent = totalRrhh > 0 ? `(${Math.round((rrhh.sinColegiatura ?? 0) / totalRrhh * 100)}%)` : '';
        try { indChartRrhhServicio = indRenderizarBarrasHorizontales('chartRrhhServicio', rrhh.porServicio || {}, indChartRrhhServicio, IND_THEME.info); } catch (e) { console.error(e); }

        // --- Conectividad ---
        const conn = data.conectividad || {};
        const connCtx = document.getElementById('chartConectividadTipo');
        if (connCtx) {
            if (indChartConectividadTipo) indChartConectividadTipo.destroy();
            const porTipo = conn.porTipo || {};
            indChartConectividadTipo = new Chart(connCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(porTipo),
                    datasets: [{ data: Object.values(porTipo), backgroundColor: [IND_THEME.info, IND_THEME.teal, IND_THEME.success, IND_THEME.warning, IND_THEME.danger, IND_THEME.purple], hoverOffset: 12, borderWidth: 3, borderColor: '#ffffff' }]
                },
                options: IND_DOUGHNUT_OPTIONS
            });
        }
        try { indRenderizarBarrasProgreso('ind_html_fuente_wifi', conn.porFuente || {}); } catch (e) { console.error(e); }
        try { indRenderizarBarrasProgreso('ind_html_proveedor', conn.porProveedor || {}); } catch (e) { console.error(e); }

        // --- Auditoría ---
        const aud = data.auditoria || {};
        document.getElementById('ind_kpi_equipo_sin_conexion').textContent = aud.equipoSinConexion ?? 0;
        document.getElementById('ind_kpi_conexion_sin_equipo').textContent = aud.conexionSinEquipo ?? 0;
        document.getElementById('ind_kpi_duplicados').textContent = aud.duplicados ?? 0;
    }

    // ============================================
    // FILTROS EN CASCADA (provincia -> distrito -> establecimiento)
    // ============================================
    function indActualizarFiltros() {
        clearTimeout(indFiltrosDebounceTimer);
        indFiltrosDebounceTimer = setTimeout(() => {
            const params = new URLSearchParams({
                tipo: document.getElementById('ind_tipo')?.value || '',
                provincia: document.getElementById('ind_provincia')?.value || '',
                distrito: document.getElementById('ind_distrito')?.value || '',
            });

            fetch('{{ route("usuario.dashboard.ajax.indicadores.provincias") }}?' + params.toString())
                .then(r => r.json())
                .then(provincias => indActualizarSelect('ind_provincia', provincias, 'Todas'));

            fetch('{{ route("usuario.dashboard.ajax.indicadores.distritos") }}?' + params.toString())
                .then(r => r.json())
                .then(distritos => indActualizarSelect('ind_distrito', distritos, 'Todos'));

            fetch('{{ route("usuario.dashboard.ajax.indicadores.establecimientos") }}?' + params.toString())
                .then(r => r.json())
                .then(establecimientos => {
                    const select = document.getElementById('ind_establecimiento');
                    if (!select) return;
                    const val = select.value;
                    select.innerHTML = '<option value="">Todos</option>';
                    establecimientos.forEach(e => {
                        const opt = document.createElement('option');
                        opt.value = e.id; opt.textContent = e.nombre;
                        if (e.id == val) opt.selected = true;
                        select.appendChild(opt);
                    });
                });
        }, 250);
    }

    function indActualizarSelect(id, valores, etiquetaTodos) {
        const select = document.getElementById(id);
        if (!select) return;
        const val = select.value;
        select.innerHTML = `<option value="">${etiquetaTodos}</option>`;
        valores.forEach(v => {
            const opt = document.createElement('option');
            opt.value = v; opt.textContent = v;
            if (v === val) opt.selected = true;
            select.appendChild(opt);
        });
    }

    // ============================================
    // INICIALIZACIÓN
    // ============================================
    document.addEventListener('DOMContentLoaded', () => {
        indCargarEstadisticas();

        document.getElementById('btnAplicarFiltrosIndicadores')?.addEventListener('click', (e) => {
            e.preventDefault();
            indCargarEstadisticas();
        });

        ['ind_tipo', 'ind_provincia', 'ind_distrito', 'ind_establecimiento'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', indActualizarFiltros);
        });
    });
</script>
