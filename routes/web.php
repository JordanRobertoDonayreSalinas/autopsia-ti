<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// --- IMPORTACIÓN DE CONTROLADORES ACTIVOS ---
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\AuditoriaEquiposController;
use App\Http\Controllers\AuditoriaDuplicidadEquiposController;
use App\Http\Controllers\MonitoreoController;
use App\Http\Controllers\EditMonitoreoController;
use App\Http\Controllers\FirmasMonitoreoController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\ConsolidadoPdfController;
use App\Http\Controllers\ConsolidadoESPPdfController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ReporteEquiposController;
use App\Http\Controllers\ReporteConsultoriosMedicinaController;
use App\Http\Controllers\ReporteMonitoreoController;
use App\Http\Controllers\ReporteDnieController;
use App\Http\Controllers\CronogramaActividadesController;
use App\Http\Controllers\Infraestructura2DController;
use App\Http\Controllers\Infraestructura2DPdfController;
use App\Http\Controllers\MonitoreoModuloGenericController;
use App\Http\Controllers\SignatureBankController;
use App\Http\Controllers\ReunionController;
use App\Http\Controllers\DnieVerificadorController;
use App\Http\Controllers\SuneduTestController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FirmaMovilController;

// --- CONFIGURACIÓN DE VERBOS ---
Route::resourceVerbs([
    'create' => 'crear-acta',
    'edit' => 'editar-acta',
]);

// --- AUTENTICACIÓN PÚBLICA ---
Route::controller(LoginController::class)->group(function () {
    Route::get('/autopsia_ti/login', 'showLoginForm')->name('login');
    Route::post('/autopsia_ti/login', 'login');
    Route::match(['get', 'post'], '/logout', 'logout')->name('logout');
});

// --- RUTAS DE FIRMA MÓVIL (PÚBLICAS) ---
Route::get('/firmar/{token}', [FirmaMovilController::class, 'viewMobilePad'])->name('firma.movil');
Route::post('/firmar/save/{token}', [FirmaMovilController::class, 'saveMobileSignature'])->name('firma.movil.save');

// --- RUTAS PÚBLICAS DE ASISTENCIA Y AUTO-DETECCIÓN ---
Route::prefix('asistencia-reunion')->name('asistencia.')->group(function () {
    Route::get('/{id}', [AttendanceController::class, 'show'])->name('show');
    Route::post('/{id}', [AttendanceController::class, 'store'])->name('store');
});

// Endpoint público para que el script .bat (Powershell) envíe los datos de hardware escaneados
Route::match(['get', 'post'], '/usuario/ajax/guardar-deteccion-hardware', [\App\Http\Controllers\HardwareDetectionController::class, 'guardarDeteccion'])->name('usuario.ajax.guardar-deteccion-hardware');

// --- RUTAS PROTEGIDAS (Middleware Auth) ---
Route::middleware(['auth'])->group(function () {

    Route::get('/', function () {
        return match(Auth::user()->role) {
            'admin'              => redirect()->route('usuario.dashboard.general'),
            'operador'           => redirect()->route('usuario.monitoreo.index'),
            'visor_cronograma'   => redirect()->route('usuario.reportes.cronograma'),
            default              => redirect()->route('usuario.perfil'),
        };
    });

    Route::get('/establecimientos/buscar', [EstablecimientoController::class, 'buscar'])->name('establecimientos.buscar');
    Route::patch('/establecimientos/{id}/coordenadas', [EstablecimientoController::class, 'updateCoordenadas'])->name('establecimientos.coordenadas');

    // --- RUTAS DE FIRMA MÓVIL (AUTENTICADAS) ---
    Route::get('/api/firmar/qr/{token}', [FirmaMovilController::class, 'generateQrCode'])->name('firma.movil.qr');
    Route::get('/api/firmar/status/{token}', [FirmaMovilController::class, 'checkSignatureStatus'])->name('firma.movil.status');

    // --- GRUPO USUARIO ---
    Route::prefix('usuario')->name('usuario.')->group(function () {

        Route::prefix('dashboard')->name('dashboard.')->middleware('is_admin')->group(function () {
            Route::get('/', [UsuarioController::class, 'index'])->name('general');
            Route::get('/equipos', [UsuarioController::class, 'dashboardEquipos'])->name('equipos');
        });

        // AJAX para Dashboard - Equipos de Cómputo
        Route::middleware('is_admin')->group(function () {
            Route::get('/dashboard/ajax/equipos-stats', [UsuarioController::class, 'getEquiposStats'])->name('dashboard.ajax.equipos.stats');
            Route::get('/dashboard/ajax/equipos-filter-options', [UsuarioController::class, 'getFilterOptions'])->name('dashboard.ajax.equipos.filter-options');
            Route::get('/dashboard/ajax/equipos-provincias', [ReporteEquiposController::class, 'getProvincias'])->name('dashboard.ajax.equipos.provincias');
            Route::get('/dashboard/ajax/equipos-establecimientos', [ReporteEquiposController::class, 'getEstablecimientos'])->name('dashboard.ajax.equipos.establecimientos');
            Route::get('/dashboard/ajax/equipos-modulos', [ReporteEquiposController::class, 'getModulos'])->name('dashboard.ajax.equipos.modulos');
            Route::get('/dashboard/ajax/equipos-descripciones', [ReporteEquiposController::class, 'getDescripciones'])->name('dashboard.ajax.equipos.descripciones');
        });

        Route::get('/mi-perfil', [UsuarioController::class, 'perfil'])->name('perfil');
        Route::put('/mi-perfil', [UsuarioController::class, 'perfilUpdate'])->name('perfil.update');

        // AJAX utilitarios
        Route::match(['get', 'post'], '/ajax/verificar-dnie/{dni}', [DnieVerificadorController::class, 'verificar'])->name('ajax.verificar-dnie');
        Route::get('/ajax/test-sunedu/{dni}', [SuneduTestController::class, 'buscar'])->name('ajax.test-sunedu');

        Route::post('/ajax/hardware-directo', [\App\Http\Controllers\HardwareDetectionController::class, 'deteccionDirecta'])->name('ajax.hardware-directo');
        Route::post('/ajax/hardware-token', [\App\Http\Controllers\HardwareDetectionController::class, 'generarToken'])->name('ajax.hardware-token');
        Route::get('/ajax/hardware-bat/{token}', [\App\Http\Controllers\HardwareDetectionController::class, 'descargarBat'])->name('ajax.hardware-bat');
        Route::get('/ajax/check-deteccion-hardware/{token}', [\App\Http\Controllers\HardwareDetectionController::class, 'checkDeteccion'])->name('ajax.check-deteccion-hardware');

        // --- SECCIÓN: ACTAS DE REUNIÓN ---
        Route::prefix('actas-reunion')->name('reuniones.')->middleware('is_admin')->group(function () {
            Route::get('/', [ReunionController::class, 'index'])->name('index');
            Route::get('/crear', [ReunionController::class, 'create'])->name('create');
            Route::post('/', [ReunionController::class, 'store'])->name('store');
            Route::get('/{reunion}/editar', [ReunionController::class, 'edit'])->name('edit');
            Route::put('/{reunion}', [ReunionController::class, 'update'])->name('update');
            Route::post('/{id}/anular', [ReunionController::class, 'anular'])->name('anular');
            Route::get('/{reunion}/pdf', [ReunionController::class, 'pdf'])->name('pdf');

            // Firma Visual
            Route::get('/{id}/visual-signature', [App\Http\Controllers\VisualSignatureReunionController::class, 'index'])->name('visual-signature');
            Route::post('/{id}/visual-save', [App\Http\Controllers\VisualSignatureReunionController::class, 'save'])->name('visual-save');
            Route::get('/{id}/pdf-subido', [App\Http\Controllers\VisualSignatureReunionController::class, 'serveUploadedPdf'])->name('pdf-subido');
            Route::post('/{id}/subir-pdf', [ReunionController::class, 'subirPDF'])->name('subirPDF');

            // QR de Asistencia
            Route::post('/{id}/activar-asistencia', [AttendanceController::class, 'activate'])->name('activar_asistencia');
            // Búsqueda de participantes (RENIEC / Local)
            Route::get('/participante/buscar/{doc}', [MonitoreoController::class, 'buscarProfesional'])->name('participante.buscar');
            Route::post('/consolidado-pdf-export', [ReunionController::class, 'consolidadoPDFExport'])->name('consolidadoPDFExport');
        });

        // --- SECCIÓN: REPORTES ---
        Route::prefix('reportes')->name('reportes.')->middleware('is_admin')->group(function () {
            Route::get('/equipos', [ReporteEquiposController::class, 'index'])->name('equipos');
            Route::post('/equipos/excel', [ReporteEquiposController::class, 'exportarExcel'])->name('equipos.excel');
            Route::post('/equipos/ficha-42', [ReporteEquiposController::class, 'exportarFicha42'])->name('equipos.ficha42');

            Route::get('/consultorios-medicina', [ReporteConsultoriosMedicinaController::class, 'index'])->name('consultorios_medicina');
            Route::post('/consultorios-medicina/excel', [ReporteConsultoriosMedicinaController::class, 'exportarExcel'])->name('consultorios_medicina.excel');

            Route::get('/equipos/ajax/establecimientos', [ReporteEquiposController::class, 'getEstablecimientos'])->name('equipos.ajax.establecimientos');
            Route::get('/equipos/ajax/provincias', [ReporteEquiposController::class, 'getProvincias'])->name('equipos.ajax.provincias');
            Route::get('/equipos/ajax/distritos', [ReporteEquiposController::class, 'ajaxGetDistritos'])->name('equipos.ajax.distritos');
            Route::get('/equipos/ajax/modulos', [ReporteEquiposController::class, 'getModulos'])->name('equipos.ajax.modulos');
            Route::get('/equipos/ajax/descripciones', [ReporteEquiposController::class, 'getDescripciones'])->name('equipos.ajax.descripciones');

            Route::get('/actas-monitoreo', [ReporteMonitoreoController::class, 'index'])->name('actas.monitoreo');
            Route::post('/actas-monitoreo/excel', [ReporteMonitoreoController::class, 'exportarExcel'])->name('actas.monitoreo.excel');
            Route::get('/actas-monitoreo/ajax/distritos', [ReporteMonitoreoController::class, 'ajaxGetDistritos'])->name('actas.monitoreo.ajax.distritos');
            Route::get('/actas-monitoreo/ajax/establecimientos', [ReporteMonitoreoController::class, 'ajaxGetEstablecimientos'])->name('actas.monitoreo.ajax.establecimientos');

            Route::post('/actas-reuniones/excel', [ReunionController::class, 'exportarExcel'])->name('actas.reuniones.excel');

            Route::get('/dnie', [ReporteDnieController::class, 'index'])->name('dnie');
            Route::post('/dnie/procesar', [ReporteDnieController::class, 'procesar'])->name('dnie.procesar');
        });

        // --- SECCIÓN: CRONOGRAMA DE ACTIVIDADES ---
        Route::prefix('reportes')->name('reportes.')->middleware('is_cronograma_viewer')->group(function () {
            Route::get('/cronograma-actividades', [CronogramaActividadesController::class, 'index'])->name('cronograma');
            Route::post('/cronograma-actividades/excel', [CronogramaActividadesController::class, 'exportarExcel'])->name('cronograma.excel');
            Route::match(['get', 'post'], '/cronograma-actividades/pdf', [CronogramaActividadesController::class, 'exportarPdf'])->name('cronograma.pdf');
            Route::get('/cronograma-actividades/ajax/provincias', [CronogramaActividadesController::class, 'ajaxGetProvincias'])->name('cronograma.ajax.provincias');
        });

        // --- SECCIÓN: AUDITORÍA DE CONSISTENCIA ---
        Route::middleware('is_admin')->group(function () {
            Route::get('/auditoria-consistencia', [AuditoriaController::class, 'index'])->name('auditoria.index');
            Route::get('/auditoria-consistencia/ajax/distritos', [AuditoriaController::class, 'ajaxGetDistritos'])->name('auditoria.ajax.distritos');
            Route::get('/auditoria-consistencia/ajax/establecimientos', [AuditoriaController::class, 'ajaxGetEstablecimientos'])->name('auditoria.ajax.establecimientos');

            Route::get('/auditoria-equipos', [AuditoriaEquiposController::class, 'index'])->name('auditoria.equipos');
            Route::get('/auditoria-equipos/ajax/distritos', [AuditoriaEquiposController::class, 'ajaxGetDistritos'])->name('auditoria.equipos.ajax.distritos');
            Route::get('/auditoria-equipos/ajax/establecimientos', [AuditoriaEquiposController::class, 'ajaxGetEstablecimientos'])->name('auditoria.equipos.ajax.establecimientos');

            Route::get('/auditoria-duplicidad', [AuditoriaDuplicidadEquiposController::class, 'index'])->name('auditoria.duplicidad');
            Route::get('/auditoria-duplicidad/ajax/distritos', [AuditoriaDuplicidadEquiposController::class, 'ajaxGetDistritos'])->name('auditoria.duplicidad.ajax.distritos');
            Route::get('/auditoria-duplicidad/ajax/establecimientos', [AuditoriaDuplicidadEquiposController::class, 'ajaxGetEstablecimientos'])->name('auditoria.duplicidad.ajax.establecimientos');
        });

        // --- SECCIÓN: ESTABLECIMIENTOS ---
        Route::prefix('establecimientos')->name('establecimientos.')->middleware('is_operador_or_admin')->group(function () {
            Route::get('/ajax/distritos', [EstablecimientoController::class, 'ajaxGetDistritos'])->name('ajax.distritos');
            Route::get('/ajax/establecimientos', [EstablecimientoController::class, 'ajaxGetEstablecimientos'])->name('ajax.establecimientos');
            Route::get('/{id}/consultar-renipress', [EstablecimientoController::class, 'consultarRenipress'])->name('consultar-renipress');
            Route::get('/', [EstablecimientoController::class, 'index'])->name('index');
            Route::get('/{id}/editar', [EstablecimientoController::class, 'edit'])->name('edit');
            Route::put('/{id}', [EstablecimientoController::class, 'update'])->name('update');
        });

        // --- SECCIÓN: MONITOREO MODULAR ---
        Route::prefix('monitoreo')->name('monitoreo.')->middleware('is_operador_or_admin')->group(function () {
            Route::get('/ajax/distritos', [MonitoreoController::class, 'ajaxGetDistritos'])->name('ajax.distritos');
            Route::get('/ajax/establecimientos', [MonitoreoController::class, 'ajaxGetEstablecimientos'])->name('ajax.establecimientos');

            Route::get('/profesional/buscar/{doc}', [MonitoreoController::class, 'buscarProfesional'])->name('profesional.buscar');
            Route::get('/equipo/buscar/{doc}', [MonitoreoController::class, 'buscarMiembroEquipo'])->name('equipo.buscar');
            Route::get('/equipo/buscar-filtro', [MonitoreoController::class, 'buscarFiltro'])->name('equipo.filtro');

            Route::get('/', [MonitoreoController::class, 'index'])->name('index');
            Route::get('/crear-acta', [MonitoreoController::class, 'create'])->name('create');
            Route::post('/', [MonitoreoController::class, 'store'])->name('store');
            Route::get('/{id}/modulos', [MonitoreoController::class, 'gestionarModulos'])->name('modulos');
            Route::post('/{id}/toggle-modulos', [MonitoreoController::class, 'toggleModulos'])->name('toggle');

            Route::post('/{id}/subir-pdf-firmado', [FirmasMonitoreoController::class, 'subir'])->name('subir-pdf-firmado');
            Route::get('/{id}/ver-pdf-firmado/{modulo}', [FirmasMonitoreoController::class, 'ver'])->name('ver-pdf-firmado');

            Route::get('/{id}/editar-acta', [EditMonitoreoController::class, 'edit'])->name('edit');
            Route::put('/{id}/actualizar', [EditMonitoreoController::class, 'update'])->name('update');
            Route::post('/{id}/cambiar-autor', [MonitoreoController::class, 'cambiarAutor'])->name('cambiar-autor');



            Route::get('/{id}/salud-mental-panel', [MonitoreoController::class, 'gestionarSaludMental'])->name('salud_mental_group.index');

            // Módulo Infraestructura 2D
            Route::prefix('modulo/infraestructura-2d')->name('infraestructura-2d.')->group(function () {
                Route::get('/{id}', [Infraestructura2DController::class, 'index'])->name('index');
                Route::get('/{id}/sync-data', [Infraestructura2DController::class, 'getSyncData'])->name('sync-data');
                Route::post('/{id}', [Infraestructura2DController::class, 'store'])->name('store');
                Route::get('/{id}/pdf', [Infraestructura2DPdfController::class, 'generar'])->name('pdf');
            });

            // RUTAS DINÁMICAS DE CONSULTORIOS / MÓDULOS
            Route::post('/{id}/crear-consultorio', [MonitoreoModuloGenericController::class, 'crearConsultorio'])->name('consultorio.crear');
            Route::put('/{id}/consultorio/{slug}/renombrar', [MonitoreoModuloGenericController::class, 'renombrarConsultorio'])->name('consultorio.renombrar');
            Route::get('/{id}/consultorio/{slug}', [MonitoreoModuloGenericController::class, 'showConsultorio'])->name('consultorio.show');
            Route::post('/{id}/consultorio/{slug}', [MonitoreoModuloGenericController::class, 'storeConsultorio'])->name('consultorio.store');
            Route::get('/{id}/consultorio/{slug}/pdf', [MonitoreoModuloGenericController::class, 'pdfConsultorio'])->name('consultorio.pdf');
            Route::delete('/{id}/consultorio/{slug}', [MonitoreoModuloGenericController::class, 'destroyConsultorio'])->name('consultorio.destroy');

            Route::get('/{id}/pdf-consolidado', [MonitoreoController::class, 'generarPDF'])->name('generarPDF');
            Route::post('/{id}/subir-consolidado-final', [MonitoreoController::class, 'subirPDF'])->name('subirConsolidado');
            Route::get('/ver-detalle/{monitoreo}', [MonitoreoController::class, 'show'])->name('show');
            Route::post('/{id}/anular', [MonitoreoController::class, 'anular'])->name('anular');
            Route::get('/{id}/emails', [MonitoreoController::class, 'getEquipoEmails'])->name('get-emails');
            Route::post('/{id}/enviar-correo', [MonitoreoController::class, 'enviarCorreo'])->name('enviarCorreo');
            Route::post('/consolidado-pdf-export', [MonitoreoController::class, 'consolidadoPDFExport'])->name('consolidadoPDFExport');
        });
    });

    Route::get('monitoreo/{id}/consolidado/pdf', [ConsolidadoPdfController::class, 'generar'])->name('usuario.monitoreo.pdf');
    Route::get('monitoreo/{id}/consolidado_esp/pdf', [ConsolidadoESPPdfController::class, 'generar'])->name('usuario.monitoreoESP.pdf');

    // --- GRUPO ADMINISTRADOR ---
    Route::prefix('admin')->name('admin.')->middleware('is_admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::prefix('gestionar-usuarios')->name('users.')->group(function () {
            Route::get('/', [AdminController::class, 'usersIndex'])->name('index');
            Route::get('/crear-usuario', [AdminController::class, 'usersCreate'])->name('create');
            Route::post('/', [AdminController::class, 'usersStore'])->name('store');
            Route::get('/{user}/editar-usuario', [AdminController::class, 'usersEdit'])->name('edit');
            Route::put('/{user}', [AdminController::class, 'usersUpdate'])->name('update');
            Route::delete('/{user}', [AdminController::class, 'usersDestroy'])->name('destroy');
            Route::patch('/{user}/toggle-status', [AdminController::class, 'toggleStatus'])->name('toggleStatus');
        });
        Route::get('/buscar-dni', [AdminController::class, 'buscarDni'])->name('buscarDni');
        Route::prefix('banco-firmas')->name('firmas.')->group(function () {
            Route::get('/', [SignatureBankController::class, 'index'])->name('index');
            Route::post('/guardar', [SignatureBankController::class, 'store'])->name('store');
            Route::delete('/{doc}', [SignatureBankController::class, 'destroy'])->name('destroy');
        });
    });

});
