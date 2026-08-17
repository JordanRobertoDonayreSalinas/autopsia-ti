import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';
import '../models/establecimiento.dart';

class OfflineDbService {
  static final OfflineDbService instance = OfflineDbService._init();
  static Database? _database;

  OfflineDbService._init();

  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDB('autopsia_ti_offline.db');
    return _database!;
  }

  Future<Database> _initDB(String filePath) async {
    final dbPath = await getDatabasesPath();
    final path = join(dbPath, filePath);

    return await openDatabase(
      path,
      version: 1,
      onCreate: _createDB,
    );
  }

  Future _createDB(Database db, int version) async {
    // 1. Catálogo de Establecimientos IPRESS
    await db.execute('''
      CREATE TABLE establecimientos (
        id INTEGER PRIMARY KEY,
        codigo TEXT,
        nombre TEXT,
        departamento TEXT,
        provincia TEXT,
        distrito TEXT,
        categoria TEXT,
        direccion TEXT
      )
    ''');

    // 2. Actas creadas sin internet (Pendientes de Sincronización)
    await db.execute('''
      CREATE TABLE actas_pendientes (
        offline_id TEXT PRIMARY KEY,
        establecimiento_id INTEGER,
        establecimiento_nombre TEXT,
        fecha TEXT,
        created_at TEXT,
        sync_status TEXT
      )
    ''');

    // 3. Consultorios evaluados offline
    await db.execute('''
      CREATE TABLE consultorios_pendientes (
        offline_id TEXT PRIMARY KEY,
        acta_offline_id TEXT,
        titulo_consultorio TEXT,
        contenido_json TEXT,
        created_at TEXT
      )
    ''');

    // 4. Equipos de cómputo inventariados
    await db.execute('''
      CREATE TABLE equipos_pendientes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        acta_offline_id TEXT,
        descripcion TEXT,
        cantidad INTEGER,
        estado TEXT,
        propio TEXT,
        nro_serie TEXT,
        observacion TEXT
      )
    ''');
  }

  // Guardar catálogo completo de IPRESS
  Future<void> guardarCatálogo(List<Establecimiento> lista) async {
    final db = await instance.database;
    final batch = db.batch();
    batch.delete('establecimientos');
    for (var item in lista) {
      batch.insert('establecimientos', item.toMap());
    }
    await batch.commit(noResult: true);
  }

  // Buscar establecimientos localmente en SQLite
  Future<List<Establecimiento>> buscarEstablecimientos(String term) async {
    final db = await instance.database;
    final q = '%${term.toLowerCase()}%';
    final result = await db.query(
      'establecimientos',
      where: 'LOWER(nombre) LIKE ? OR LOWER(codigo) LIKE ? OR LOWER(distrito) LIKE ?',
      whereArgs: [q, q, q],
      limit: 20,
    );
    return result.map((json) => Establecimiento.fromMap(json)).toList();
  }

  // Guardar acta creada en campo sin internet
  Future<void> guardarActaOffline(Map<String, dynamic> actaData) async {
    final db = await instance.database;
    await db.insert('actas_pendientes', actaData);
  }

  // Obtener actas pendientes de sincronizar
  Future<List<Map<String, dynamic>>> obtenerActasPendientes() async {
    final db = await instance.database;
    final actas = await db.query('actas_pendientes', where: 'sync_status = ?', whereArgs: ['pending']);
    List<Map<String, dynamic>> resultado = [];

    for (var a in actas) {
      var map = Map<String, dynamic>.from(a);
      final offlineId = map['offline_id'];

      final consultorios = await db.query('consultorios_pendientes', where: 'acta_offline_id = ?', whereArgs: [offlineId]);
      final equipos = await db.query('equipos_pendientes', where: 'acta_offline_id = ?', whereArgs: [offlineId]);

      map['consultorios'] = consultorios;
      map['equipos'] = equipos;
      resultado.add(map);
    }
    return resultado;
  }

  // Marcar actas como sincronizadas
  Future<void> limpiarSincronizados() async {
    final db = await instance.database;
    await db.delete('actas_pendientes');
    await db.delete('consultorios_pendientes');
    await db.delete('equipos_pendientes');
  }
}
