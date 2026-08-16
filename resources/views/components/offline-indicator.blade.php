<div x-data="pwaOfflineManager()" x-init="initPWA()" class="relative z-50">
    
    {{-- BARRA FLOTANTE DE ESTADO CONECTIVIDAD / SINCRONIZACIÓN --}}
    <div class="fixed bottom-6 right-6 flex flex-col items-end gap-3 z-50">
        
        {{-- ALERTA MODO OFFLINE / MODO CAMPO --}}
        <template x-if="!isOnline">
            <div class="bg-amber-950/90 text-amber-200 border-2 border-amber-500/50 backdrop-blur-md px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 animate-pulse">
                <div class="w-3 h-3 bg-amber-400 rounded-full animate-ping"></div>
                <div class="text-xs font-black uppercase tracking-wider">
                    <span>Modo Campo (Sin Internet)</span>
                    <p class="text-[9px] text-amber-300 font-bold tracking-normal normal-case">Las actas que crees se guardarán en tu Laptop</p>
                </div>
            </div>
        </template>

        {{-- BOTÓN / NOTIFICACIÓN DE ACTAS PENDIENTES DE SINCRONIZAR --}}
        <template x-if="pendingCount > 0">
            <div class="bg-indigo-900 text-white border-2 border-emerald-400 p-4 rounded-3xl shadow-2xl flex items-center gap-4 max-w-sm animate-bounce">
                <div class="h-10 w-10 bg-emerald-500 rounded-2xl flex items-center justify-center font-black text-sm text-white shadow-lg">
                    <span x-text="pendingCount"></span>
                </div>
                <div class="flex-1">
                    <h4 class="text-xs font-black uppercase tracking-tight">Actas Pendientes</h4>
                    <p class="text-[10px] text-indigo-200 font-bold">Listas para subir al servidor central</p>
                </div>
                <button @click="syncDataNow()" :disabled="isSyncing" 
                        class="px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white font-black text-[10px] uppercase tracking-widest rounded-xl transition-all shadow-md flex items-center gap-1.5">
                    <template x-if="isSyncing">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </template>
                    <span x-text="isSyncing ? 'Subiendo...' : 'Sincronizar'"></span>
                </button>
            </div>
        </template>

        {{-- MENÚ DESPLEGABLE MODO OFFLINE --}}
        <div class="relative">
            <button @click="showMenu = !showMenu" 
                    class="h-12 px-5 bg-slate-900/90 hover:bg-slate-900 text-white border border-slate-700/80 backdrop-blur-md rounded-2xl shadow-xl flex items-center gap-3 font-bold text-xs transition-all hover:scale-105">
                <span class="w-2.5 h-2.5 rounded-full" :class="isOnline ? 'bg-emerald-400' : 'bg-amber-400 animate-ping'"></span>
                <span x-text="isOnline ? 'PWA Online' : 'PWA Offline'"></span>
                <svg class="w-4 h-4 transition-transform" :class="showMenu ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="showMenu" @click.away="showMenu = false" x-cloak
                 class="absolute bottom-16 right-0 w-72 bg-white rounded-3xl p-5 shadow-2xl border border-slate-100 text-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Modo Trabajo PWA</span>
                    <span class="px-2 py-0.5 text-[9px] font-black rounded-md uppercase" :class="isOnline ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                        <span x-text="isOnline ? 'Conectado' : 'Sin Internet'"></span>
                    </span>
                </div>

                <div class="space-y-2">
                    <button @click="downloadFieldData()" :disabled="isDownloading" 
                            class="w-full py-3 px-4 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-2xl text-left flex items-center justify-between transition-all">
                        <span class="uppercase tracking-tight text-[11px]" x-text="isDownloading ? 'Descargando...' : 'Descargar Datos Offline'"></span>
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    </button>
                    <p class="text-[9px] text-slate-400 font-medium px-1">Guarda el catálogo de IPRESS en tu laptop antes de viajar a zonas sin señal.</p>
                </div>

                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 block px-1">Descargar Aplicación Instalable</span>
                    
                    <a href="{{ asset('apps/instalar_app_windows.bat') }}" download="Instalar_AutopsiaTI_Windows.bat" 
                       class="w-full py-2.5 px-3.5 bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-700 font-bold text-[11px] rounded-xl flex items-center justify-between transition-all">
                        <span class="flex items-center gap-2">
                            <i data-lucide="laptop" class="w-4 h-4 text-indigo-600"></i>
                            <span>App Windows (.EXE / .BAT)</span>
                        </span>
                        <i data-lucide="download" class="w-3.5 h-3.5 text-slate-400"></i>
                    </a>

                    <a href="{{ asset('apps/autopsia_ti_android.apk') }}" download="AutopsiaTI.apk" 
                       class="w-full py-2.5 px-3.5 bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 font-bold text-[11px] rounded-xl flex items-center justify-between transition-all">
                        <span class="flex items-center gap-2">
                            <i data-lucide="smartphone" class="w-4 h-4 text-emerald-600"></i>
                            <span>App Android (.APK)</span>
                        </span>
                        <i data-lucide="download" class="w-3.5 h-3.5 text-slate-400"></i>
                    </a>
                </div>

                <template x-if="pendingCount > 0">
                    <div class="pt-2 border-t border-slate-100">
                        <button @click="syncDataNow()" :disabled="isSyncing || !isOnline"
                                class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-black text-xs rounded-2xl flex items-center justify-center gap-2 transition-all shadow-md">
                            <span class="uppercase tracking-widest text-[10px]" x-text="isSyncing ? 'Sincronizando...' : `Sincronizar ${pendingCount} Actas`"></span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

    </div>
</div>

<script src="{{ asset('js/offline-db.js') }}"></script>
<script>
    function pwaOfflineManager() {
        return {
            isOnline: navigator.onLine,
            pendingCount: 0,
            showMenu: false,
            isDownloading: false,
            isSyncing: false,

            async initPWA() {
                window.addEventListener('online', () => {
                    this.isOnline = true;
                    this.checkPendingSync();
                    this.autoSyncSilencioso();
                });
                window.addEventListener('offline', () => {
                    this.isOnline = false;
                });

                if ('serviceWorker' in navigator) {
                    try {
                        await navigator.serviceWorker.register('/sw.js');
                        console.log('[PWA] Service Worker registrado exitosamente');
                    } catch (err) {
                        console.warn('[PWA] Error registrando SW:', err);
                    }
                }

                window.descargarDatosCampoOffline = (manual = true) => this.downloadFieldData(manual);
                await this.checkPendingSync();

                // 1. Auto-Precargado silencioso en segundo plano sin pedir clics
                if (this.isOnline) {
                    this.autoPrecacheSilencioso();
                    if (this.pendingCount > 0) {
                        this.autoSyncSilencioso();
                    }
                }
            },

            async checkPendingSync() {
                if (window.OfflineDB) {
                    this.pendingCount = await window.OfflineDB.contarPendientes();
                }
            },

            async autoPrecacheSilencioso() {
                try {
                    if (!window.OfflineDB) return;
                    const locales = await window.OfflineDB.obtenerEstablecimientos();
                    // Si la BD local está vacía, descargar automáticamente en segundo plano
                    if (!locales || locales.length === 0) {
                        console.log('[PWA Auto-Cache] Descargando catálogo de IPRESS en segundo plano...');
                        const res = await fetch("{{ route('usuario.monitoreo.offline.descargar') }}");
                        const data = await res.json();
                        if (data.success && data.establecimientos) {
                            await window.OfflineDB.guardarEstablecimientos(data.establecimientos);
                            console.log(`[PWA Auto-Cache] ${data.total} IPRESS guardadas automáticamente en IndexedDB para trabajo offline.`);
                        }
                    }
                } catch(e) {
                    console.warn('[PWA Auto-Cache Error]', e);
                }
            },

            async autoSyncSilencioso() {
                if (!this.isOnline || this.isSyncing || this.pendingCount === 0) return;
                console.log('[PWA Auto-Sync] Detectadas actas pendientes. Sincronizando en segundo plano...');
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true
                });
                toast.fire({
                    icon: 'info',
                    title: `Sincronizando ${this.pendingCount} acta(s) pendiente(s)...`
                });
                await this.syncDataNow(true);
            },

            async downloadFieldData(manual = true) {
                if (!this.isOnline) {
                    Swal.fire('Atención', 'Necesitas estar conectado a Internet para descargar el catálogo inicial de campo.', 'warning');
                    return;
                }
                this.isDownloading = true;
                if (manual) {
                    Swal.fire({
                        title: 'Actualizando Datos Offline',
                        text: 'Guardando catálogo completo de 524 IPRESS en tu laptop...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                }
                try {
                    const res = await fetch("{{ route('usuario.monitoreo.offline.descargar') }}");
                    const data = await res.json();
                    if (data.success && window.OfflineDB) {
                        await window.OfflineDB.guardarEstablecimientos(data.establecimientos);
                        if (manual) {
                            Swal.fire('¡Datos Guardados!', `Se guardaron ${data.total} establecimientos en la memoria de tu laptop. Ya puedes trabajar 100% offline.`, 'success');
                        }
                    } else if (manual) {
                        Swal.fire('Error', 'No se pudieron procesar los datos para el modo offline.', 'error');
                    }
                } catch(e) {
                    if (manual) Swal.fire('Error', 'No se pudieron descargar los datos para trabajo offline.', 'error');
                } finally {
                    this.isDownloading = false;
                }
            },

            async syncDataNow(isSilent = false) {
                if (!this.isOnline) {
                    if (!isSilent) Swal.fire('Modo Offline', 'Conéctate a Internet para poder sincronizar tus actas al servidor central.', 'info');
                    return;
                }

                this.isSyncing = true;
                try {
                    const actas = await window.OfflineDB.obtenerActasPendientes();
                    if (actas.length === 0) {
                        this.pendingCount = 0;
                        return;
                    }

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                    const res = await fetch("{{ route('usuario.monitoreo.offline.sincronizar') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ actas: actas })
                    });

                    const data = await res.json();
                    if (data.success) {
                        await window.OfflineDB.limpiarSincronizados();
                        this.pendingCount = 0;
                        if (!isSilent) {
                            Swal.fire('¡Sincronización Completa!', data.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            const toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 4000
                            });
                            toast.fire({
                                icon: 'success',
                                title: data.message || '¡Actas sincronizadas exitosamente!'
                            });
                        }
                    } else if (!isSilent) {
                        Swal.fire('Error de Sincronización', data.message || 'No se pudo completar la sincronización.', 'error');
                    }
                } catch(e) {
                    if (!isSilent) Swal.fire('Error', 'Fallo al intentar conectar con el servidor central: ' + e.message, 'error');
                } finally {
                    this.isSyncing = false;
                }
            }
        };
    }
</script>
