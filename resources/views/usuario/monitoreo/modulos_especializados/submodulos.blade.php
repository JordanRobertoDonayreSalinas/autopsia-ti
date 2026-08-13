@extends('layouts.usuario')

@section('title', 'Salud Mental CSMC | ' . $acta->establecimiento->nombre)

@section('content')
<div class="py-12 bg-[#f4f7fa] min-h-screen" 
     x-data="{ 
        activos: {{ json_encode($modulosActivos) }},
        modulosFirmados: {{ json_encode($modulosFirmados ?? []) }},
        modulosGuardados: {{ json_encode($modulosGuardados ?? []) }},
        showModal: false,
        currentModule: '',
        currentModuleName: '',
        async toggle(slug) {
            if(this.activos.includes(slug)) {
                this.activos = this.activos.filter(i => i !== slug);
            } else {
                this.activos.push(slug);
            }
            try {
                await fetch('{{ route('usuario.monitoreo.toggle', $acta->id) }}', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify({ modulos_activos: this.activos })
                });
            } catch (error) {
                console.error('Error de sincronización:', error);
            }
        },
        openUpload(slug, name) {
            this.currentModule = slug;
            this.currentModuleName = name;
            this.showModal = true;
        },
        init() {
            this.$watch('activos', () => {
                this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
            });
        }
     }">
    
    <div class="max-w-6xl mx-auto px-6">
        
        {{-- ENCABEZADO SUB-NIVEL (Manteniendo identidad Teal) --}}
        <div class="bg-teal-900 rounded-[2.5rem] p-10 shadow-2xl mb-12 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 bg-emerald-500/10 rounded-full -ml-20 -mb-20 blur-2xl"></div>

            <div class="flex flex-col md:flex-row justify-between items-center gap-8 relative z-10 text-white">
                <div class="flex items-center gap-8">
                    <div class="h-20 w-20 rounded-3xl bg-teal-500 flex items-center justify-center shadow-lg border border-teal-400">
                        <i data-lucide="brain-circuit" class="text-white w-10 h-10"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-3 py-1 bg-emerald-400 text-teal-900 text-[10px] font-black rounded-lg uppercase tracking-widest">Nivel 4</span>
                            <span class="text-teal-200 text-[11px] font-bold uppercase tracking-widest">SUB-MÓDULOS</span>
                        </div>
                        <h2 class="text-3xl font-black tracking-tight uppercase italic">4. SALUD MENTAL</h2>
                        <p class="text-teal-200/80 text-xs font-bold mt-1 uppercase tracking-widest">Seleccione la especialidad a evaluar</p>
                    </div>
                </div>
                
                {{-- BOTÓN VOLVER AL MENÚ PRINCIPAL (CAMBIO IMPORTANTE: Redirige a .modulos) --}}
                <a href="{{ route('usuario.monitoreo.modulos', $acta->id) }}" class="group flex items-center gap-3 px-8 py-4 rounded-2xl bg-white/10 hover:bg-white hover:text-teal-900 border border-white/20 transition-all font-black text-xs uppercase tracking-widest">
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i> Volver al Menú
                </a>
            </div>
        </div>

        {{-- GRID DE SUB-MÓDULOS (4.1 - 4.7) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($modulosSaludMental as $slug => $data)
            @php 
                $isCompleted = in_array($slug, $modulosGuardados); 
                $isSigned = in_array($slug, $modulosFirmados ?? []); 
                
                // Generar rutas dinámicas para los sub-módulos
                $routeName = "usuario.monitoreo.{$slug}.index"; 
                $pdfRouteName = "usuario.monitoreo.{$slug}.pdf";
                
                $hasRoute = Route::has($routeName); 
                $hasPdfRoute = Route::has($pdfRouteName);
                $hasPdfRoute = Route::has($pdfRouteName);
                // Agregar parámetro de versión para evitar caché de cPanel
                $viewSignedRoute = Route::has('usuario.monitoreo.ver-pdf-firmado') 
                    ? route('usuario.monitoreo.ver-pdf-firmado', [$acta->id, $slug]) . '?v=' . time() 
                    : '#';
            @endphp
            
            <div class="relative bg-white rounded-[2.5rem] border-2 transition-all duration-500 group overflow-hidden flex flex-col"
                 :class="activos.includes('{{ $slug }}') ? '{{ $isCompleted ? 'border-emerald-200' : 'border-teal-100' }} shadow-xl' : 'border-transparent bg-slate-100 opacity-60 grayscale'">
                
                {{-- CABECERA --}}
                <div class="p-6 pb-0 flex justify-between items-start z-10">
                    <div :class="activos.includes('{{ $slug }}') ? '{{ $isCompleted ? 'bg-emerald-500' : 'bg-teal-600' }}' : 'bg-slate-300'"
                         class="h-14 w-14 rounded-2xl flex items-center justify-center text-white shadow-lg transition-all duration-500">
                        <i data-lucide="{{ $data['icon'] }}" class="w-7 h-7"></i>
                    </div>

                    <button @click="toggle('{{ $slug }}')" 
                            :class="activos.includes('{{ $slug }}') ? 'bg-teal-600' : 'bg-slate-400'"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none shadow-inner">
                        <span :class="activos.includes('{{ $slug }}') ? 'translate-x-6' : 'translate-x-1'"
                              class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-300"></span>
                    </button>
                </div>

                {{-- CUERPO --}}
                <div class="flex-1">
                    <template x-if="activos.includes('{{ $slug }}')">
                        @if($hasRoute)
                        <a href="{{ route($routeName, $acta->id) }}" class="block p-6 group/link">
                            <h3 class="text-slate-800 text-sm font-black uppercase tracking-tight leading-tight mb-2 group-hover/link:text-teal-600 transition-colors">
                                {{ $data['nombre'] }}
                            </h3>
                            <span class="text-[9px] font-black uppercase tracking-widest flex items-center gap-2 {{ $isCompleted ? 'text-emerald-500' : 'text-teal-500' }}">
                                @if($isCompleted)
                                    <i data-lucide="check" class="w-3 h-3 stroke-[3]"></i> Evaluación Registrada
                                @else
                                    <i data-lucide="circle" class="w-3 h-3"></i> Módulo Habilitado
                                @endif
                            </span>
                        </a>
                        @else
                        <div class="p-6 opacity-50">
                            <h3 class="text-slate-500 text-sm font-black uppercase tracking-tight leading-tight mb-2">{{ $data['nombre'] }}</h3>
                            <span class="text-[9px] font-bold text-slate-400 uppercase italic">En desarrollo técnico</span>
                        </div>
                        @endif
                    </template>

                    <template x-if="!activos.includes('{{ $slug }}')">
                        <div class="p-6">
                            <h3 class="text-slate-400 text-sm font-black uppercase tracking-tight leading-tight mb-2">{{ $data['nombre'] }}</h3>
                            <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest flex items-center gap-2">
                                <i data-lucide="lock" class="w-3 h-3"></i> Inactivo
                            </span>
                        </div>
                    </template>
                </div>

                {{-- FOOTER ACCIONES --}}
                <div class="p-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-center gap-2" 
                     x-show="activos.includes('{{ $slug }}') && modulosGuardados.includes('{{ $slug }}')">
                    
                    @if($hasPdfRoute)
                    <a href="{{ route($pdfRouteName, $acta->id) }}" target="_blank" 
                       class="h-10 w-10 bg-white text-slate-600 border border-slate-200 rounded-xl flex items-center justify-center hover:bg-teal-600 hover:text-white transition-all shadow-sm group/pdf" 
                       title="Ver PDF Generado">
                        <i data-lucide="file-text" class="w-5 h-5 group-hover/pdf:scale-110 transition-transform"></i>
                    </a>
                    @endif
                    
                    <button @click="openUpload('{{ $slug }}', '{{ $data['nombre'] }}')" 
                            class="flex-1 h-10 px-4 {{ $isSigned ? 'bg-emerald-600' : 'bg-slate-900' }} text-white rounded-xl flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-95 transition-all shadow-md group/btn" 
                            title="Firmar Acta">
                        <i data-lucide="{{ $isSigned ? 'shield-check' : 'file-signature' }}" class="w-4 h-4 {{ $isSigned ? 'text-emerald-200' : 'text-teal-200' }}"></i>
                        <span class="text-[9px] font-black uppercase tracking-[0.1em]">
                            {{ $isSigned ? 'FIRMADO' : 'FIRMAR' }}
                        </span>
                    </button>

                    @if($isSigned)
                    <a href="{{ $viewSignedRoute }}" target="_blank" class="h-10 w-10 bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Ver Firma">
                        <i data-lucide="eye" class="w-5 h-5"></i>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- MODAL DE SUBIDA --}}
    <div x-show="showModal" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-md" 
         x-cloak 
         x-transition
         x-data="{ fileName: '' }">
        
        <div class="bg-white rounded-[3rem] shadow-2xl max-w-md w-full overflow-hidden" @click.away="showModal = false; fileName = ''">
            <div class="bg-teal-900 p-10 text-white relative">
                <h3 class="text-2xl font-black uppercase tracking-tight" x-text="currentModuleName"></h3>
                <p class="text-teal-400 text-[10px] font-black uppercase mt-2 tracking-widest">Carga de Evidencia CSMC</p>
                <button @click="showModal = false; fileName = ''" class="absolute top-10 right-10 text-white/50 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <form action="{{ route('usuario.monitoreo.subir-pdf-firmado', $acta->id) }}" method="POST" enctype="multipart/form-data" class="p-10 space-y-8">
                @csrf
                <input type="hidden" name="modulo" :value="currentModule">
                
                <div class="border-4 border-dashed rounded-[2.5rem] p-12 flex flex-col items-center justify-center transition-all cursor-pointer relative"
                     :class="fileName ? 'border-emerald-400 bg-emerald-50' : 'border-slate-100 bg-slate-50 hover:border-teal-400'">
                    
                    <input type="file" 
                           name="pdf_firmado" 
                           accept="application/pdf" 
                           required 
                           class="absolute inset-0 opacity-0 cursor-pointer"
                           @change="fileName = $event.target.files[0].name">

                    <template x-if="!fileName">
                        <div class="text-center">
                            <i data-lucide="upload-cloud" class="w-12 h-12 text-slate-300 mb-4 mx-auto"></i>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Seleccionar Acta en PDF</span>
                        </div>
                    </template>

                    <template x-if="fileName">
                        <div class="text-center animate-bounce-short">
                            <i data-lucide="file-check" class="w-12 h-12 text-emerald-500 mb-4 mx-auto"></i>
                            <p class="text-xs font-black text-emerald-700 uppercase tracking-tight">Archivo listo para cargar:</p>
                            <p class="text-[11px] font-bold text-slate-600 mt-1 break-all" x-text="fileName"></p>
                        </div>
                    </template>
                </div>

                <div class="space-y-3">
                    <button type="submit" 
                            class="w-full py-5 rounded-2xl bg-teal-600 text-white font-black text-xs uppercase shadow-xl hover:bg-slate-900 transition-all tracking-[0.2em]"
                            :disabled="!fileName"
                            :class="!fileName ? 'opacity-50 cursor-not-allowed' : ''">
                        Confirmar Certificación
                    </button>
                    
                    <button type="button" 
                            @click="showModal = false; fileName = ''" 
                            class="w-full py-3 text-slate-400 font-bold text-[10px] uppercase tracking-widest hover:text-slate-600 transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection