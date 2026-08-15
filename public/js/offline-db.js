/**
 * Autopsia TI - Motor de Base de Datos Local IndexedDB para Trabajo Offline
 */
(function (window) {
    'use strict';

    const DB_NAME = 'AutopsiaTI_OfflineDB';
    const DB_VERSION = 1;
    let dbPromise = null;

    function openDB() {
        if (!dbPromise) {
            dbPromise = new Promise((resolve, reject) => {
                const request = indexedDB.open(DB_NAME, DB_VERSION);

                request.onupgradeneeded = (event) => {
                    const db = event.target.result;

                    // 1. Catálogo de Establecimientos (IPRESS)
                    if (!db.objectStoreNames.contains('establecimientos')) {
                        const estStore = db.createObjectStore('establecimientos', { keyPath: 'id' });
                        estStore.createIndex('codigo_ipress', 'codigo_ipress', { unique: false });
                        estStore.createIndex('departamento', 'departamento', { unique: false });
                    }

                    // 2. Actas creadas sin internet (Pendientes de Sincronizar)
                    if (!db.objectStoreNames.contains('actas_pendientes')) {
                        const actaStore = db.createObjectStore('actas_pendientes', { keyPath: 'offline_id' });
                        actaStore.createIndex('sync_status', 'sync_status', { unique: false });
                    }

                    // 3. Evaluaciones de Consultorios / Módulos creadas sin internet
                    if (!db.objectStoreNames.contains('consultorios_pendientes')) {
                        const consStore = db.createObjectStore('consultorios_pendientes', { keyPath: 'offline_id' });
                        consStore.createIndex('acta_offline_id', 'acta_offline_id', { unique: false });
                    }

                    // 4. Equipos de Cómputo inventariados offline
                    if (!db.objectStoreNames.contains('equipos_pendientes')) {
                        db.createObjectStore('equipos_pendientes', { keyPath: 'id', autoIncrement: true });
                    }
                };

                request.onsuccess = (event) => resolve(event.target.result);
                request.onerror = (event) => reject(event.target.error);
            });
        }
        return dbPromise;
    }

    const OfflineDB = {
        // --- GUARDAR CATÁLOGO DE ESTABLECIMIENTOS (DE CUSCO/ICA, ETC) ---
        async guardarEstablecimientos(lista) {
            const db = await openDB();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('establecimientos', 'readwrite');
                const store = tx.objectStore('establecimientos');
                store.clear();
                lista.forEach(item => store.put(item));
                tx.oncomplete = () => resolve(true);
                tx.onerror = (e) => reject(e.target.error);
            });
        },

        // --- OBTENER ESTABLECIMIENTOS LOCALES ---
        async obtenerEstablecimientos() {
            const db = await openDB();
            return new Promise((resolve) => {
                const tx = db.transaction('establecimientos', 'readonly');
                const store = tx.objectStore('establecimientos');
                const req = store.getAll();
                req.onsuccess = () => resolve(req.result || []);
                req.onerror = () => resolve([]);
            });
        },

        // --- GUARDAR ACTA OFFLINE ---
        async guardarActaOffline(actaData) {
            const db = await openDB();
            const offlineId = 'acta_off_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
            const payload = {
                offline_id: offlineId,
                establecimiento_id: actaData.establecimiento_id,
                establecimiento_nombre: actaData.establecimiento_nombre,
                fecha: actaData.fecha || new Date().toISOString().split('T')[0],
                sync_status: 'pending',
                created_at: new Date().toISOString(),
                consultorios: []
            };

            return new Promise((resolve, reject) => {
                const tx = db.transaction('actas_pendientes', 'readwrite');
                const store = tx.objectStore('actas_pendientes');
                store.put(payload);
                tx.oncomplete = () => resolve(offlineId);
                tx.onerror = (e) => reject(e.target.error);
            });
        },

        // --- GUARDAR O ACTUALIZAR EVALUACIÓN DE CONSULTORIO OFFLINE ---
        async guardarConsultorioOffline(actaOfflineId, consultorioData) {
            const db = await openDB();
            const offlineConsId = 'cons_off_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
            const payload = {
                offline_id: offlineConsId,
                acta_offline_id: actaOfflineId,
                titulo_consultorio: consultorioData.titulo_consultorio || 'CONSULTORIO',
                contenido: consultorioData.contenido || {},
                equipos: consultorioData.equipos || [],
                created_at: new Date().toISOString()
            };

            return new Promise((resolve, reject) => {
                const tx = db.transaction('consultorios_pendientes', 'readwrite');
                const store = tx.objectStore('consultorios_pendientes');
                store.put(payload);
                tx.oncomplete = () => resolve(offlineConsId);
                tx.onerror = (e) => reject(e.target.error);
            });
        },

        // --- OBTENER TODAS LAS ACTAS PENDIENTES DE SINCRONIZAR ---
        async obtenerActasPendientes() {
            const db = await openDB();
            return new Promise((resolve) => {
                const tx = db.transaction(['actas_pendientes', 'consultorios_pendientes'], 'readonly');
                const actasStore = tx.objectStore('actas_pendientes');
                const consStore = tx.objectStore('consultorios_pendientes');

                const reqActas = actasStore.getAll();
                reqActas.onsuccess = () => {
                    const actas = reqActas.result || [];
                    const reqCons = consStore.getAll();
                    reqCons.onsuccess = () => {
                        const consultorios = reqCons.result || [];
                        // Vincular consultorios con sus respectivas actas
                        actas.forEach(acta => {
                            acta.consultorios = consultorios.filter(c => c.acta_offline_id === acta.offline_id);
                        });
                        resolve(actas);
                    };
                };
            });
        },

        // --- CONTEO DE ELEMENTOS PENDIENTES ---
        async contarPendientes() {
            const db = await openDB();
            return new Promise((resolve) => {
                const tx = db.transaction('actas_pendientes', 'readonly');
                const store = tx.objectStore('actas_pendientes');
                const req = store.count();
                req.onsuccess = () => resolve(req.result || 0);
                req.onerror = () => resolve(0);
            });
        },

        // --- LIMPIAR ACTAS SINCRONIZADAS ---
        async limpiarSincronizados() {
            const db = await openDB();
            return new Promise((resolve, reject) => {
                const tx = db.transaction(['actas_pendientes', 'consultorios_pendientes'], 'readwrite');
                tx.objectStore('actas_pendientes').clear();
                tx.objectStore('consultorios_pendientes').clear();
                tx.oncomplete = () => resolve(true);
                tx.onerror = (e) => reject(e.target.error);
            });
        }
    };

    window.OfflineDB = OfflineDB;
})(window);
