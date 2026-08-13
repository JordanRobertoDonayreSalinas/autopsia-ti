@props(['files', 'oldFiles'])

{{-- 
    Uso: <x-fotos files="files" old-files="oldFiles" />
    'files': Nombre de la variable en el padre (Alpine) para los nuevos archivos.
    'oldFiles': Nombre de la variable en el padre (Alpine) para las fotos de BD.
--}}

<div x-data="fotosComponent({{ $files }}, {{ $oldFiles }})"
     class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">
    
    {{-- Decoración --}}
    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 opacity-60 pointer-events-none"></div>

    {{-- Encabezado --}}
    <div class="flex items-center gap-4 mb-6 relative z-10">
        <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
            <i data-lucide="camera" class="text-white w-6 h-6"></i>
        </div>
        <div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Evidencia Fotográfica</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Respaldo Visual</p>
        </div>
    </div>

    {{-- ZONA DE CARGA (DRAG & DROP) --}}
    <div class="border-2 border-dashed border-slate-300 rounded-2xl p-10 flex flex-col items-center justify-center text-center hover:bg-slate-50 transition-colors relative cursor-pointer group">
        <input type="file" 
               multiple 
               @change="handleFiles" 
               accept="image/png, image/jpeg, image/jpg" 
               class="absolute inset-0 opacity-0 cursor-pointer z-20">
        
        <div class="group-hover:scale-110 transition-transform duration-300">
            <i data-lucide="cloud-upload" class="w-10 h-10 text-indigo-400 mb-3"></i>
        </div>
        <p class="text-indigo-600 font-bold uppercase text-sm group-hover:text-indigo-700">Clic para subir o arrastrar archivos</p>
        <p class="text-[10px] text-slate-400 mt-1 font-bold uppercase tracking-wide">PNG, JPG o JPEG (Máx. 5MB)</p>
    </div>
    
    {{-- LISTA DE ARCHIVOS NUEVOS --}}
    <div class="mt-4 space-y-2" x-show="newFilesRef.length > 0" x-transition>
        <div class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Nuevos Archivos:</div>
        <template x-for="(file, i) in newFilesRef" :key="i">
            <div class="flex items-center justify-between bg-indigo-50/50 px-4 py-3 rounded-xl border border-indigo-100">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="bg-indigo-200 p-1.5 rounded-lg text-indigo-600">
                        <i data-lucide="image" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-700 truncate" x-text="file.name"></span>
                    <span class="text-[10px] font-medium text-slate-400" x-text="(file.size/1024/1024).toFixed(2) + ' MB'"></span>
                </div>
                <button type="button" @click="removeFile(i)" class="text-slate-400 hover:text-red-500 hover:bg-red-50 p-1 rounded-lg transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </template>
    </div>

    {{-- GALERÍA DE ARCHIVOS GUARDADOS --}}
    <div class="mt-8 border-t border-slate-100 pt-6" x-show="oldFilesRef.length > 0">
        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Galería Guardada</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <template x-for="(foto, index) in oldFilesRef" :key="foto.id">
                <div class="relative group h-96 bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 shadow-sm">
                    <img :src="'/storage/' + foto.url_foto" class="w-full h-full object-cover rounded-2xl shadow-inner shadow-black/10 transition-all duration-700 group-hover:scale-110 group-hover:rotate-1">
                    
                    {{-- Overlay Acciones --}}
                    <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-all duration-300 flex flex-col items-center justify-center gap-3 backdrop-blur-[2px]">
                        <a :href="'/storage/' + foto.url_foto" target="_blank" class="flex items-center gap-2 text-white text-[10px] font-bold uppercase tracking-widest hover:text-indigo-300 transition-colors">
                            <i data-lucide="eye" class="w-4 h-4"></i> Ver
                        </a>
                        <button type="button" 
                                @click="eliminarFotoGuardada(foto.id, index)" 
                                class="bg-white/10 hover:bg-red-500 text-white hover:border-red-500 border border-white/30 p-2 rounded-full shadow-lg transition-all transform hover:scale-110" 
                                title="Eliminar foto permanentemente">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Script del Componente --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('fotosComponent', (filesArray, oldFilesArray) => ({
                // Recibimos las referencias a los arrays del padre
                newFilesRef: filesArray,
                oldFilesRef: oldFilesArray,

                handleFiles(event) {
                    const selectedFiles = Array.from(event.target.files).filter(file => {
                        return file.type === 'image/jpeg' || file.type === 'image/png' || file.type === 'image/jpg';
                    });
                    
                    // Modificamos el array del padre directamente (por referencia)
                    // Usamos push con spread para reactividad
                    this.newFilesRef.push(...selectedFiles);
                    
                    // Limpiar input para permitir subir el mismo archivo de nuevo si se borró
                    event.target.value = '';
                },

                removeFile(index) {
                    this.newFilesRef.splice(index, 1);
                },

                eliminarFotoGuardada(id, index) {
                    if(!confirm('¿Estás seguro de eliminar esta foto de forma permanente?')) return;

                    // NOTA: Ajusta la ruta base si la reutilizas en otros módulos
                    fetch(`/usuario/monitoreo/modulo/triaje/foto/${id}`, {
                        method: 'DELETE',
                        headers: { 
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
                            'Content-Type': 'application/json' 
                        }
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            this.oldFilesRef.splice(index, 1);
                        } else {
                            alert('Error al eliminar: ' + (d.message || 'Desconocido'));
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error de conexión al eliminar la foto.');
                    });
                }
            }));
        });
    </script>
</div>