<script>
    console.log('=== EQUIPOS_SCRIPTS.BLADE.PHP CARGADO ===');

    // ===== CONFIGURACIÓN GLOBAL DE CHART.JS =====
    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.color = '#64748b';

    // ===== VARIABLES GLOBALES =====
    let eq_chartEstado, eq_chartTipo, eq_chartModulo, eq_chartDescripcion;

    // ===== FUNCIÓN PARA ACTUALIZAR PROVINCIAS =====
    function eq_actualizarProvincias() {
        const tipo = document.getElementById('eq_tipo').value;
        const provinciaSelect = document.getElementById('eq_provincia');

        fetch(`{{ route('usuario.dashboard.ajax.equipos.provincias') }}?tipo=${tipo}`)
            .then(response => response.json())
            .then(data => {
                provinciaSelect.innerHTML = '<option value="">Todas</option>';
                data.forEach(prov => {
                    provinciaSelect.innerHTML += `<option value="${prov}">${prov}</option>`;
                });
                // NO llamar automáticamente a eq_actualizarEstablecimientos() aquí
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    }

    // ===== FUNCIÓN PARA ACTUALIZAR ESTABLECIMIENTOS =====
    function eq_actualizarEstablecimientos() {
        const tipo = document.getElementById('eq_tipo').value;
        const provincia = document.getElementById('eq_provincia').value;
        const establecimientoSelect = document.getElementById('eq_establecimiento');

        fetch(`{{ route('usuario.dashboard.ajax.equipos.establecimientos') }}?tipo=${tipo}&provincia=${provincia}`)
            .then(response => response.json())
            .then(data => {
                establecimientoSelect.innerHTML = '<option value="">Todos</option>';
                data.forEach(est => {
                    establecimientoSelect.innerHTML += `<option value="${est.id}">${est.nombre}</option>`;
                });
                // NO llamar automáticamente a eq_actualizarModulos() aquí
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    }

    // ===== FUNCIÓN PARA ACTUALIZAR MÓDULOS =====
    function eq_actualizarModulos() {
        const mes = document.getElementById('eq_mes').value;
        const anio = document.getElementById('eq_anio').value;
        const tipo = document.getElementById('eq_tipo').value;
        const provincia = document.getElementById('eq_provincia').value;
        const establecimiento = document.getElementById('eq_establecimiento').value;
        const moduloSelect = document.getElementById('eq_modulo');

        fetch(`{{ route('usuario.dashboard.ajax.equipos.modulos') }}?mes=${mes}&anio=${anio}&tipo=${tipo}&provincia=${provincia}&establecimiento_id=${establecimiento}`)
            .then(response => response.json())
            .then(data => {
                moduloSelect.innerHTML = '<option value="">Todos</option>';
                data.forEach(mod => {
                    moduloSelect.innerHTML += `<option value="${mod.valor}">${mod.nombre}</option>`;
                });
                // NO llamar automáticamente a eq_actualizarDescripciones() aquí
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    }

    // ===== FUNCIÓN PARA ACTUALIZAR DESCRIPCIONES =====
    function eq_actualizarDescripciones() {
        const mes = document.getElementById('eq_mes').value;
        const anio = document.getElementById('eq_anio').value;
        const tipo = document.getElementById('eq_tipo').value;
        const provincia = document.getElementById('eq_provincia').value;
        const establecimiento = document.getElementById('eq_establecimiento').value;
        const modulo = document.getElementById('eq_modulo').value;
        const descripcionSelect = document.getElementById('eq_descripcion');

        fetch(`{{ route('usuario.dashboard.ajax.equipos.descripciones') }}?mes=${mes}&anio=${anio}&tipo=${tipo}&provincia=${provincia}&establecimiento_id=${establecimiento}&modulo=${modulo}`)
            .then(response => response.json())
            .then(data => {
                descripcionSelect.innerHTML = '<option value="">Todas</option>';
                data.forEach(desc => {
                    descripcionSelect.innerHTML += `<option value="${desc}">${desc}</option>`;
                });
                if (typeof lucide !== 'undefined') lucide.createIcons();
            });
    }

    // ===== FUNCIÓN PARA CARGAR ESTADÍSTICAS =====
    function eq_cargarEstadisticas() {
        const mes = document.getElementById('eq_mes').value;
        const anio = document.getElementById('eq_anio').value;
        const tipo = document.getElementById('eq_tipo').value;
        const provincia = document.getElementById('eq_provincia').value;
        const establecimiento = document.getElementById('eq_establecimiento').value;
        const modulo = document.getElementById('eq_modulo').value;
        const descripcion = document.getElementById('eq_descripcion').value;

        // Mostrar loading
        document.getElementById('eq_totalEquipos').textContent = '...';

        // Construir URL con parámetros
        const params = new URLSearchParams({
            mes, anio, tipo, provincia,
            establecimiento_id: establecimiento,
            modulo, descripcion
        });

        // Hacer petición AJAX
        const url = `{{ route('usuario.dashboard.ajax.equipos.stats') }}?${params}`;
        console.log('Fetching:', url);

        fetch(url)
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Data received:', data);
                // Actualizar total global
                document.getElementById('eq_totalEquipos').textContent = data.totalEquipos;
                document.getElementById('eq_periodoTexto').textContent = data.periodoTexto;

                // Actualizar gráficos
                eq_actualizarGraficos(data);

                // Refrescar iconos
                if (typeof lucide !== 'undefined') lucide.createIcons();
            })
            .catch(error => {
                console.error('Error completo:', error);
                document.getElementById('eq_totalEquipos').textContent = '0';
            });
    }

    // ===== FUNCIÓN PARA ACTUALIZAR GRÁFICOS =====
    function eq_actualizarGraficos(data) {
        // Destruir gráficos existentes
        if (eq_chartEstado) eq_chartEstado.destroy();
        if (eq_chartTipo) eq_chartTipo.destroy();
        if (eq_chartModulo) eq_chartModulo.destroy();
        if (eq_chartDescripcion) eq_chartDescripcion.destroy();

        // GRÁFICO 1: Equipos por Estado
        const ctxEstado = document.getElementById('eq_chartEstado').getContext('2d');
        eq_chartEstado = new Chart(ctxEstado, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data.equiposPorEstado),
                datasets: [{
                    data: Object.values(data.equiposPorEstado),
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12, weight: '600' },
                            usePointStyle: true,
                            pointStyle: 'circle',
                            generateLabels: function (chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map(function (label, i) {
                                        const ds = data.datasets[0];
                                        const value = ds.data[i];
                                        const fill = ds.backgroundColor[i];
                                        return {
                                            text: `${label} (${value})`,
                                            fillStyle: fill,
                                            strokeStyle: fill,
                                            lineWidth: 0,
                                            hidden: isNaN(value) || chart.getDatasetMeta(0).data[i].hidden,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        cornerRadius: 8
                    }
                }
            }
        });

        // GRÁFICO 2: Equipos por Tipo
        const ctxTipo = document.getElementById('eq_chartTipo').getContext('2d');
        eq_chartTipo = new Chart(ctxTipo, {
            type: 'bar',
            data: {
                data: {
                    labels: Object.keys(data.equiposPorTipo).map((key, index) => `${key} (${Object.values(data.equiposPorTipo)[index]})`),
                    datasets: [{
                        label: 'Cantidad',
                        data: Object.values(data.equiposPorTipo),
                        backgroundColor: ['#6366f1', '#ec4899'],
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: { beginAtZero: true, ticks: { font: { size: 11 } }, grid: { color: '#f1f5f9' } },
                        x: { ticks: { font: { size: 11, weight: '600' } }, grid: { display: false } }
                    }
                }
            });

        // GRÁFICO 3: Equipos por Módulo
        const ctxModulo = document.getElementById('eq_chartModulo').getContext('2d');
        eq_chartModulo = new Chart(ctxModulo, {
            type: 'bar',
            data: {
                data: {
                    labels: Object.keys(data.equiposPorModulo).map((key, index) => `${key} (${Object.values(data.equiposPorModulo)[index]})`),
                    datasets: [{
                        label: 'Cantidad',
                        data: Object.values(data.equiposPorModulo),
                        backgroundColor: '#8b5cf6',
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: { beginAtZero: true, ticks: { font: { size: 11 } }, grid: { color: '#f1f5f9' } },
                        y: { ticks: { font: { size: 10, weight: '600' } }, grid: { display: false } }
                    }
                }
            });

        // GRÁFICO 4: Descripciones
        const ctxDescripcion = document.getElementById('eq_chartDescripcion').getContext('2d');
        eq_chartDescripcion = new Chart(ctxDescripcion, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data.topDescripciones),
                datasets: [{
                    data: Object.values(data.topDescripciones),
                    backgroundColor: ['#f59e0b', '#f97316', '#eab308', '#84cc16', '#22c55e'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 12, weight: '600' },
                            usePointStyle: true,
                            pointStyle: 'circle',
                            generateLabels: function (chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map(function (label, i) {
                                        const ds = data.datasets[0];
                                        const value = ds.data[i];
                                        const fill = ds.backgroundColor[i];
                                        return {
                                            text: `${label} (${value})`,
                                            fillStyle: fill,
                                            strokeStyle: fill,
                                            lineWidth: 0,
                                            hidden: isNaN(value) || chart.getDatasetMeta(0).data[i].hidden,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 13 },
                        cornerRadius: 8
                    }
                }
            }
        });
    }

    // ===== EVENT LISTENERS =====
    document.addEventListener('DOMContentLoaded', function () {
        // Cargar estadísticas iniciales primero
        eq_cargarEstadisticas();

        // Luego establecer event listeners para filtros en cascada
        // (después de un pequeño delay para que no sobrescriban los valores iniciales)
        setTimeout(function () {
            document.getElementById('eq_mes').addEventListener('change', eq_actualizarModulos);
            document.getElementById('eq_anio').addEventListener('change', eq_actualizarModulos);
            document.getElementById('eq_tipo').addEventListener('change', eq_actualizarProvincias);
            document.getElementById('eq_provincia').addEventListener('change', eq_actualizarEstablecimientos);
            document.getElementById('eq_establecimiento').addEventListener('change', eq_actualizarModulos);
            document.getElementById('eq_modulo').addEventListener('change', eq_actualizarDescripciones);

            // Event listener para botón aplicar filtros
            document.getElementById('btnAplicarFiltrosEquipos').addEventListener('click', eq_cargarEstadisticas);
        }, 100);
    });
</script>