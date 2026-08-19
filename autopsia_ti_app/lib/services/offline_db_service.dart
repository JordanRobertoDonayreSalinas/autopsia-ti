import 'package:flutter/foundation.dart';
import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../models/establecimiento.dart';
import '../models/cabecera_monitoreo.dart';
import '../models/equipo_computo.dart';
import '../models/equipo_monitoreo.dart';
import '../models/monitoreo_modulo.dart';
import '../models/reunion.dart';
import '../models/profesional.dart';

/// Persistencia local (sqflite en móvil/desktop, memoria en Web). Los
/// nombres de tabla y columna son un espejo de las tablas "offline-first" de
/// Laravel (ver storage/app/database_schema.json, generado por
/// `php artisan schema:export-json`) más un puñado de columnas locales de la
/// cola de sincronización (offline_id, sync_status, local_created_at,
/// acta_offline_id) que no existen en Laravel.
class OfflineDbService {
  static final OfflineDbService instance = OfflineDbService._init();
  static Database? _database;

  // Colecciones en memoria para Web y fallback offline.
  // Arrancan vacías: el catálogo real se descarga de Laravel en el primer
  // sync (GET /v1/catalog) y las actas/reuniones/profesionales se capturan
  // en campo o llegan sincronizadas — no se precargan datos de demostración.
  final List<Establecimiento> _memoriaEstablecimientos = [];
  final List<CabeceraMonitoreo> _memoriaActas = [];
  final List<EquipoComputo> _memoriaEquipos = [];
  final List<EquipoMonitoreo> _memoriaEquipoMonitoreo = [];
  final List<MonitoreoModulo> _memoriaModulos = [];
  final List<Reunion> _memoriaReuniones = [];
  final List<Profesional> _memoriaProfesionales = [];

  OfflineDbService._init();

  Future<Database?> get database async {
    if (kIsWeb) return null;
    if (_database != null) return _database!;
    _database = await _initDB('autopsia_ti_offline.db');
    return _database!;
  }

  Future<Database> _initDB(String filePath) async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, filePath);

    return await openDatabase(
      path,
      version: 3,
      onCreate: _createDB,
      onUpgrade: (db, oldVersion, newVersion) async {
        await _createDB(db, newVersion);
      },
    );
  }

  Future _createDB(Database db, int version) async {
    await db.execute('DROP TABLE IF EXISTS establecimientos');
    await db.execute('DROP TABLE IF EXISTS actas_pendientes'); // nombre local antiguo (v2 y anteriores)
    await db.execute('DROP TABLE IF EXISTS equipos_pendientes'); // nombre local antiguo (v2 y anteriores)
    await db.execute('DROP TABLE IF EXISTS reuniones_pendientes'); // nombre local antiguo (v2 y anteriores)
    await db.execute('DROP TABLE IF EXISTS profesionales'); // nombre local antiguo (v2 y anteriores)
    await db.execute('DROP TABLE IF EXISTS mon_cabecera_monitoreo');
    await db.execute('DROP TABLE IF EXISTS mon_equipos_computo');
    await db.execute('DROP TABLE IF EXISTS mon_equipo_monitoreo');
    await db.execute('DROP TABLE IF EXISTS mon_monitoreo_modulos');
    await db.execute('DROP TABLE IF EXISTS mon_profesionales');
    await db.execute('DROP TABLE IF EXISTS reuniones');

    // Espejo de establecimientos (34 columnas en Laravel, ver Establecimiento::$fillable)
    await db.execute('''
      CREATE TABLE establecimientos (
        id INTEGER PRIMARY KEY,
        codigo TEXT NOT NULL,
        nombre TEXT NOT NULL,
        institucion TEXT,
        direccion TEXT,
        departamento TEXT,
        provincia TEXT NOT NULL,
        distrito TEXT NOT NULL,
        centro_poblado TEXT,
        telefono TEXT,
        correo TEXT,
        red TEXT NOT NULL,
        microred TEXT NOT NULL,
        clas TEXT,
        odsis TEXT,
        responsable TEXT NOT NULL,
        tipo_documento TEXT,
        numero_documento TEXT,
        colegio_profesional TEXT,
        colegiatura TEXT,
        rne TEXT,
        categoria TEXT NOT NULL,
        estado TEXT,
        condicion TEXT,
        latitud REAL,
        longitud REAL,
        altitud TEXT,
        fecha_creacion_resolucion TEXT,
        fecha_registro TEXT,
        numero_resolucion_creacion TEXT,
        horario_atencion TEXT,
        numero_ambientes TEXT,
        numero_camas TEXT,
        upss TEXT,
        ups TEXT
      )
    ''');

    // Espejo de mon_cabecera_monitoreo + columnas locales de cola (offline_id, sync_status, local_created_at)
    await db.execute('''
      CREATE TABLE mon_cabecera_monitoreo (
        offline_id TEXT PRIMARY KEY,
        sync_status TEXT,
        local_created_at TEXT,
        id INTEGER,
        user_id INTEGER,
        establecimiento_id INTEGER NOT NULL,
        tipo_origen TEXT,
        numero_acta INTEGER,
        categoria_congelada TEXT,
        responsable_congelado TEXT,
        fecha TEXT,
        responsable TEXT,
        implementador TEXT,
        pozo_tierra TEXT,
        pozo_tierra_cantidad INTEGER,
        pozo_tierra_operativos INTEGER,
        pozo_tierra_inoperativos INTEGER,
        panel_solar TEXT,
        panel_solar_cantidad INTEGER,
        panel_solar_operativos INTEGER,
        panel_solar_inoperativos INTEGER,
        foto1 TEXT,
        foto2 TEXT,
        firmado INTEGER,
        anulado INTEGER,
        firmado_pdf TEXT
      )
    ''');

    // Espejo de mon_equipos_computo + acta_offline_id (columna local de cola)
    await db.execute('''
      CREATE TABLE mon_equipos_computo (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        acta_offline_id TEXT,
        cabecera_monitoreo_id INTEGER,
        modulo TEXT,
        descripcion TEXT,
        cantidad INTEGER,
        estado TEXT,
        nro_serie TEXT,
        propio TEXT,
        observacion TEXT
      )
    ''');

    // Espejo de mon_equipo_monitoreo (personal del establecimiento presente en la visita)
    await db.execute('''
      CREATE TABLE mon_equipo_monitoreo (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        acta_offline_id TEXT,
        cabecera_monitoreo_id INTEGER,
        tipo_doc TEXT,
        doc TEXT,
        apellido_paterno TEXT,
        apellido_materno TEXT,
        nombres TEXT,
        cargo TEXT,
        institucion TEXT
      )
    ''');

    // Espejo de mon_monitoreo_modulos (los hasta 18 módulos dinámicos evaluados por acta)
    await db.execute('''
      CREATE TABLE mon_monitoreo_modulos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        acta_offline_id TEXT,
        cabecera_monitoreo_id INTEGER,
        modulo_nombre TEXT,
        contenido TEXT,
        pdf_firmado_path TEXT
      )
    ''');

    // Espejo de mon_profesionales
    await db.execute('''
      CREATE TABLE mon_profesionales (
        id INTEGER PRIMARY KEY,
        tipo_doc TEXT,
        doc TEXT NOT NULL,
        apellido_paterno TEXT,
        apellido_materno TEXT,
        nombres TEXT,
        email TEXT,
        telefono TEXT,
        cargo TEXT,
        firma_path TEXT,
        tipo_firma TEXT,
        ultima_actualizacion_firma TEXT
      )
    ''');

    // Espejo de reuniones + columnas locales de cola (offline_id, sync_status, local_created_at)
    await db.execute('''
      CREATE TABLE reuniones (
        offline_id TEXT PRIMARY KEY,
        sync_status TEXT,
        local_created_at TEXT,
        id INTEGER,
        titulo_reunion TEXT,
        fecha_reunion TEXT,
        hora_reunion TEXT,
        hora_finalizada_reunion TEXT,
        nombre_institucion TEXT,
        descripcion_general TEXT,
        acuerdos TEXT,
        comentarios_observaciones TEXT,
        foto_1 TEXT,
        foto_2 TEXT,
        participantes TEXT,
        archivo_pdf TEXT,
        firmado INTEGER,
        anulado INTEGER,
        asistencia_desde TEXT,
        asistencia_hasta TEXT
      )
    ''');

    final batch = db.batch();
    for (var item in _memoriaEstablecimientos) {
      batch.insert('establecimientos', item.toMap());
    }
    for (var item in _memoriaProfesionales) {
      batch.insert('mon_profesionales', item.toMap());
    }
    await batch.commit(noResult: true);
  }

  // --- MÉTODOS IPRESS ---
  Future<void> guardarCatalogo(List<Establecimiento> lista) async {
    if (lista.isEmpty) return;
    _memoriaEstablecimientos.clear();
    _memoriaEstablecimientos.addAll(lista);

    if (!kIsWeb) {
      try {
        final db = await instance.database;
        if (db != null) {
          final batch = db.batch();
          batch.delete('establecimientos');
          for (var item in lista) {
            batch.insert('establecimientos', item.toMap());
          }
          await batch.commit(noResult: true);
        }
      } catch (_) {}
    }
  }

  Future<List<Establecimiento>> buscarEstablecimientos(String term) async {
    final termLower = term.toLowerCase().trim();

    if (kIsWeb) {
      if (termLower.isEmpty) return List.from(_memoriaEstablecimientos);
      return _memoriaEstablecimientos.where((item) {
        return item.nombre.toLowerCase().contains(termLower) ||
            item.codigo.toLowerCase().contains(termLower) ||
            item.distrito.toLowerCase().contains(termLower);
      }).toList();
    }

    try {
      final db = await instance.database;
      if (db != null) {
        if (termLower.isEmpty) {
          final result = await db.query('establecimientos', limit: 30);
          if (result.isNotEmpty) {
            return result.map((json) => Establecimiento.fromMap(json)).toList();
          }
        } else {
          final q = '%$termLower%';
          final result = await db.query(
            'establecimientos',
            where: 'LOWER(nombre) LIKE ? OR LOWER(codigo) LIKE ? OR LOWER(distrito) LIKE ?',
            whereArgs: [q, q, q],
            limit: 30,
          );
          if (result.isNotEmpty) {
            return result.map((json) => Establecimiento.fromMap(json)).toList();
          }
        }
      }
    } catch (_) {}

    if (termLower.isEmpty) return List.from(_memoriaEstablecimientos);
    return _memoriaEstablecimientos.where((item) {
      return item.nombre.toLowerCase().contains(termLower) ||
          item.codigo.toLowerCase().contains(termLower) ||
          item.distrito.toLowerCase().contains(termLower);
    }).toList();
  }

  /// Catálogo completo sin el límite de 30 de buscarEstablecimientos() — para
  /// la pantalla "Establecimientos" (listado paginado con filtros), donde el
  /// filtrado/paginado se hace del lado del cliente sobre el catálogo ya
  /// descargado, igual que los filtros geográficos del Dashboard.
  Future<List<Establecimiento>> obtenerTodosLosEstablecimientos() async {
    if (kIsWeb) return List.from(_memoriaEstablecimientos);
    try {
      final db = await instance.database;
      if (db != null) {
        final result = await db.query('establecimientos', orderBy: 'nombre ASC');
        if (result.isNotEmpty) {
          return result.map((json) => Establecimiento.fromMap(json)).toList();
        }
      }
    } catch (_) {}
    return List.from(_memoriaEstablecimientos);
  }

  // --- MÉTODOS ACTAS MONITOREO ---
  Future<void> guardarActa(
    CabeceraMonitoreo acta, {
    List<EquipoComputo> equipos = const [],
    List<EquipoMonitoreo> equipoMonitoreo = const [],
    List<MonitoreoModulo> modulos = const [],
  }) async {
    _memoriaActas.add(acta);
    _memoriaEquipos.addAll(equipos);
    _memoriaEquipoMonitoreo.addAll(equipoMonitoreo);
    _memoriaModulos.addAll(modulos);

    if (!kIsWeb) {
      try {
        final db = await instance.database;
        if (db != null) {
          await db.insert('mon_cabecera_monitoreo', acta.toMap());
          for (var eq in equipos) {
            await db.insert('mon_equipos_computo', eq.toMap());
          }
          for (var p in equipoMonitoreo) {
            await db.insert('mon_equipo_monitoreo', p.toMap());
          }
          for (var m in modulos) {
            await db.insert('mon_monitoreo_modulos', m.toMap());
          }
        }
      } catch (_) {}
    }
  }

  Future<void> guardarActaOffline(Map<String, dynamic> data) async {
    final acta = CabeceraMonitoreo.fromMap(data);
    await guardarActa(acta);
  }

  /// Descarga completa de una acta que YA EXISTE en el servidor (creada en
  /// Laravel o en otro dispositivo) para poder editarla localmente. A
  /// diferencia de guardarActa() (una acta nueva capturada en este
  /// dispositivo), acá primero se borra cualquier copia local previa de la
  /// misma acta (mismo offline_id determinístico 'SRV-{id}') para que
  /// volver a abrirla la refresque en vez de duplicarla.
  Future<void> hidratarActaServidor(
    CabeceraMonitoreo acta, {
    List<EquipoComputo> equipos = const [],
    List<EquipoMonitoreo> equipoMonitoreo = const [],
    List<MonitoreoModulo> modulos = const [],
  }) async {
    if (kIsWeb) {
      _memoriaActas.removeWhere((a) => a.offlineId == acta.offlineId);
      _memoriaEquipos.removeWhere((e) => e.actaOfflineId == acta.offlineId);
      _memoriaEquipoMonitoreo.removeWhere((e) => e.actaOfflineId == acta.offlineId);
      _memoriaModulos.removeWhere((m) => m.actaOfflineId == acta.offlineId);
    } else {
      try {
        final db = await instance.database;
        if (db != null) {
          await db.delete('mon_cabecera_monitoreo', where: 'offline_id = ?', whereArgs: [acta.offlineId]);
          await db.delete('mon_equipos_computo', where: 'acta_offline_id = ?', whereArgs: [acta.offlineId]);
          await db.delete('mon_equipo_monitoreo', where: 'acta_offline_id = ?', whereArgs: [acta.offlineId]);
          await db.delete('mon_monitoreo_modulos', where: 'acta_offline_id = ?', whereArgs: [acta.offlineId]);
        }
      } catch (_) {}
    }
    await guardarActa(acta, equipos: equipos, equipoMonitoreo: equipoMonitoreo, modulos: modulos);
  }

  Future<List<CabeceraMonitoreo>> obtenerActas() async {
    if (kIsWeb) return List.from(_memoriaActas);
    try {
      final db = await instance.database;
      if (db != null) {
        final result = await db.query('mon_cabecera_monitoreo');
        if (result.isNotEmpty) {
          return result.map((json) => CabeceraMonitoreo.fromMap(json)).toList();
        }
      }
    } catch (_) {}
    return List.from(_memoriaActas);
  }

  Future<List<Map<String, dynamic>>> obtenerActasPendientes() async {
    final actas = await obtenerActas();
    return actas.where((a) => a.syncStatus == 'pending').map((a) => a.toMap()).toList();
  }

  Future<CabeceraMonitoreo?> obtenerActaPorOfflineId(String offlineId) async {
    final actas = await obtenerActas();
    final match = actas.where((a) => a.offlineId == offlineId);
    return match.isEmpty ? null : match.first;
  }

  Future<List<EquipoComputo>> obtenerEquiposPorActa(String actaOfflineId) async {
    if (kIsWeb) {
      return _memoriaEquipos.where((e) => e.actaOfflineId == actaOfflineId).toList();
    }
    try {
      final db = await instance.database;
      if (db != null) {
        final result = await db.query('mon_equipos_computo', where: 'acta_offline_id = ?', whereArgs: [actaOfflineId]);
        return result.map((json) => EquipoComputo.fromMap(json)).toList();
      }
    } catch (_) {}
    return _memoriaEquipos.where((e) => e.actaOfflineId == actaOfflineId).toList();
  }

  Future<List<EquipoMonitoreo>> obtenerEquipoMonitoreoPorActa(String actaOfflineId) async {
    if (kIsWeb) {
      return _memoriaEquipoMonitoreo.where((e) => e.actaOfflineId == actaOfflineId).toList();
    }
    try {
      final db = await instance.database;
      if (db != null) {
        final result = await db.query('mon_equipo_monitoreo', where: 'acta_offline_id = ?', whereArgs: [actaOfflineId]);
        return result.map((json) => EquipoMonitoreo.fromMap(json)).toList();
      }
    } catch (_) {}
    return _memoriaEquipoMonitoreo.where((e) => e.actaOfflineId == actaOfflineId).toList();
  }

  Future<List<MonitoreoModulo>> obtenerModulosPorActa(String actaOfflineId) async {
    if (kIsWeb) {
      return _memoriaModulos.where((m) => m.actaOfflineId == actaOfflineId).toList();
    }
    try {
      final db = await instance.database;
      if (db != null) {
        final result = await db.query('mon_monitoreo_modulos', where: 'acta_offline_id = ?', whereArgs: [actaOfflineId]);
        return result.map((json) => MonitoreoModulo.fromMap(json)).toList();
      }
    } catch (_) {}
    return _memoriaModulos.where((m) => m.actaOfflineId == actaOfflineId).toList();
  }

  Future<MonitoreoModulo?> obtenerModulo(String actaOfflineId, String moduloNombre) async {
    final modulos = await obtenerModulosPorActa(actaOfflineId);
    final match = modulos.where((m) => m.moduloNombre == moduloNombre);
    return match.isEmpty ? null : match.first;
  }

  /// Crea o reemplaza el contenido de un módulo fijo (ej. 'rrhh') para una
  /// acta — espejo de MonitoreoModulos::updateOrCreate(['cabecera_monitoreo_id',
  /// 'modulo_nombre'], ['contenido']) en Laravel. A diferencia de los
  /// consultorios de captura rápida (que se guardan una sola vez con
  /// guardarActa), los módulos fijos se editan varias veces antes de sincronizar.
  Future<void> guardarOActualizarModulo(MonitoreoModulo modulo) async {
    final idx = _memoriaModulos.indexWhere(
      (m) => m.actaOfflineId == modulo.actaOfflineId && m.moduloNombre == modulo.moduloNombre,
    );
    if (idx != -1) {
      _memoriaModulos[idx] = modulo;
    } else {
      _memoriaModulos.add(modulo);
    }

    if (!kIsWeb) {
      try {
        final db = await instance.database;
        if (db != null) {
          final existente = await db.query(
            'mon_monitoreo_modulos',
            where: 'acta_offline_id = ? AND modulo_nombre = ?',
            whereArgs: [modulo.actaOfflineId, modulo.moduloNombre],
            limit: 1,
          );
          if (existente.isNotEmpty) {
            await db.update(
              'mon_monitoreo_modulos',
              {'contenido': modulo.contenido},
              where: 'id = ?',
              whereArgs: [existente.first['id']],
            );
          } else {
            await db.insert('mon_monitoreo_modulos', modulo.toMap());
          }
        }
      } catch (_) {}
    }
  }

  /// Elimina un módulo/consultorio y sus equipos de cómputo asociados —
  /// espejo de MonitoreoModuloGenericController::destroyConsultorio.
  Future<void> eliminarModulo(String actaOfflineId, String moduloNombre) async {
    _memoriaModulos.removeWhere((m) => m.actaOfflineId == actaOfflineId && m.moduloNombre == moduloNombre);
    _memoriaEquipos.removeWhere((e) => e.actaOfflineId == actaOfflineId && e.modulo == moduloNombre);

    if (!kIsWeb) {
      try {
        final db = await instance.database;
        if (db != null) {
          await db.delete(
            'mon_monitoreo_modulos',
            where: 'acta_offline_id = ? AND modulo_nombre = ?',
            whereArgs: [actaOfflineId, moduloNombre],
          );
          await db.delete(
            'mon_equipos_computo',
            where: 'acta_offline_id = ? AND modulo = ?',
            whereArgs: [actaOfflineId, moduloNombre],
          );
        }
      } catch (_) {}
    }
  }

  Future<List<EquipoComputo>> obtenerEquiposPorModulo(String actaOfflineId, String moduloNombre) async {
    final todos = await obtenerEquiposPorActa(actaOfflineId);
    return todos.where((e) => e.modulo == moduloNombre).toList();
  }

  /// Reemplaza por completo la lista de equipos de cómputo de un módulo —
  /// espejo de cómo storeConsultorio sincroniza la tabla mon_equipos_computo
  /// con el arreglo `equipos[]` recibido en cada guardado del formulario.
  Future<void> reemplazarEquiposDeModulo(String actaOfflineId, String moduloNombre, List<EquipoComputo> equipos) async {
    _memoriaEquipos.removeWhere((e) => e.actaOfflineId == actaOfflineId && e.modulo == moduloNombre);
    _memoriaEquipos.addAll(equipos);

    if (!kIsWeb) {
      try {
        final db = await instance.database;
        if (db != null) {
          await db.delete(
            'mon_equipos_computo',
            where: 'acta_offline_id = ? AND modulo = ?',
            whereArgs: [actaOfflineId, moduloNombre],
          );
          for (var eq in equipos) {
            await db.insert('mon_equipos_computo', eq.toMap());
          }
        }
      } catch (_) {}
    }
  }

  // --- MÉTODOS REUNIONES ---
  Future<void> guardarReunion(Reunion reunion) async {
    _memoriaReuniones.add(reunion);
    if (!kIsWeb) {
      try {
        final db = await instance.database;
        if (db != null) {
          await db.insert('reuniones', reunion.toMap());
        }
      } catch (_) {}
    }
  }

  Future<List<Reunion>> obtenerReuniones() async {
    if (kIsWeb) return List.from(_memoriaReuniones);
    try {
      final db = await instance.database;
      if (db != null) {
        final result = await db.query('reuniones');
        if (result.isNotEmpty) {
          return result.map((json) => Reunion.fromMap(json)).toList();
        }
      }
    } catch (_) {}
    return List.from(_memoriaReuniones);
  }

  Future<List<Reunion>> obtenerReunionesPendientes() async {
    final reuniones = await obtenerReuniones();
    return reuniones.where((r) => r.syncStatus == 'pending').toList();
  }

  Future<void> marcarReunionSincronizada(String offlineId, {required int id}) async {
    final idx = _memoriaReuniones.indexWhere((r) => r.offlineId == offlineId);
    if (idx != -1) {
      final actual = _memoriaReuniones[idx];
      _memoriaReuniones[idx] = Reunion(
        offlineId: actual.offlineId,
        syncStatus: 'synced',
        localCreatedAt: actual.localCreatedAt,
        id: id,
        tituloReunion: actual.tituloReunion,
        fechaReunion: actual.fechaReunion,
        horaReunion: actual.horaReunion,
        horaFinalizadaReunion: actual.horaFinalizadaReunion,
        nombreInstitucion: actual.nombreInstitucion,
        descripcionGeneral: actual.descripcionGeneral,
        acuerdos: actual.acuerdos,
        comentariosObservaciones: actual.comentariosObservaciones,
        foto1: actual.foto1,
        foto2: actual.foto2,
        participantes: actual.participantes,
        archivoPdf: actual.archivoPdf,
        firmado: actual.firmado,
        anulado: actual.anulado,
        asistenciaDesde: actual.asistenciaDesde,
        asistenciaHasta: actual.asistenciaHasta,
      );
    }
    if (!kIsWeb) {
      try {
        final db = await instance.database;
        if (db != null) {
          await db.update(
            'reuniones',
            {'sync_status': 'synced', 'id': id},
            where: 'offline_id = ?',
            whereArgs: [offlineId],
          );
        }
      } catch (_) {}
    }
  }

  // --- MÉTODOS PROFESIONALES ---
  Future<Profesional?> buscarProfesionalPorDni(String dni) async {
    final dniClean = dni.trim();
    if (kIsWeb) {
      final match = _memoriaProfesionales.where((p) => p.doc == dniClean);
      if (match.isNotEmpty) return match.first;
    }
    try {
      final db = await instance.database;
      if (db != null) {
        final result = await db.query('mon_profesionales', where: 'doc = ?', whereArgs: [dniClean]);
        if (result.isNotEmpty) {
          return Profesional.fromMap(result.first);
        }
      }
    } catch (_) {}

    final match = _memoriaProfesionales.where((p) => p.doc == dniClean);
    if (match.isNotEmpty) return match.first;
    return null;
  }

  /// Marca como sincronizadas (en vez de borrarlas) las actas ya confirmadas
  /// por el servidor, guardando el [id] y [numeroActa] reales que Laravel
  /// asignó. Ver SyncService.sincronizarPendientes.
  Future<void> marcarActaSincronizada(String offlineId, {required int id, required int numeroActa}) async {
    final idx = _memoriaActas.indexWhere((a) => a.offlineId == offlineId);
    if (idx != -1) {
      final actual = _memoriaActas[idx];
      _memoriaActas[idx] = CabeceraMonitoreo(
        offlineId: actual.offlineId,
        syncStatus: 'synced',
        localCreatedAt: actual.localCreatedAt,
        id: id,
        userId: actual.userId,
        establecimientoId: actual.establecimientoId,
        tipoOrigen: actual.tipoOrigen,
        numeroActa: numeroActa,
        categoriaCongelada: actual.categoriaCongelada,
        responsableCongelado: actual.responsableCongelado,
        fecha: actual.fecha,
        responsable: actual.responsable,
        implementador: actual.implementador,
        pozoTierra: actual.pozoTierra,
        pozoTierraCantidad: actual.pozoTierraCantidad,
        pozoTierraOperativos: actual.pozoTierraOperativos,
        pozoTierraInoperativos: actual.pozoTierraInoperativos,
        panelSolar: actual.panelSolar,
        panelSolarCantidad: actual.panelSolarCantidad,
        panelSolarOperativos: actual.panelSolarOperativos,
        panelSolarInoperativos: actual.panelSolarInoperativos,
        foto1: actual.foto1,
        foto2: actual.foto2,
        firmado: actual.firmado,
        anulado: actual.anulado,
        firmadoPdf: actual.firmadoPdf,
      );
    }
    if (!kIsWeb) {
      try {
        final db = await instance.database;
        if (db != null) {
          await db.update(
            'mon_cabecera_monitoreo',
            {'sync_status': 'synced', 'id': id, 'numero_acta': numeroActa},
            where: 'offline_id = ?',
            whereArgs: [offlineId],
          );
        }
      } catch (_) {}
    }
  }
}
