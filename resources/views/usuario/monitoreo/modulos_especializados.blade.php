@extends('layouts.usuario')

@section('title', 'Módulos CSMC | ' . $acta->establecimiento->nombre)

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
        
        {{-- ENCABEZADO DIFERENCIADO (TEAL / CSMC) --}}
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
                            <span class="px-3 py-1 bg-emerald-400 text-teal-900 text-[10px] font-black rounded-lg uppercase tracking-widest">Especializado CSMC</span>
                            <span class="text-teal-200 text-[11px] font-bold uppercase tracking-widest">ACTA N°{{ str_pad($acta->numero_acta ?? $acta->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <h2 class="text-3xl font-black tracking-tight uppercase italic">{{ $acta->establecimiento->nombre }}</h2>
                        <p class="text-teal-200/80 text-xs font-bold mt-1 uppercase tracking-widest">Módulos de Salud Mental Comunitaria</p>
                    </div>
                </div>
                <a href="{{ route('usuario.monitoreo.index') }}" class="group flex items-center gap-3 px-8 py-4 rounded-2xl bg-white/10 hover:bg-white hover:text-teal-900 border border-white/20 transition-all font-black text-xs uppercase tracking-widest">
                    <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i> Volver
                </a>
            </div>
        </div>

        {{-- GRID DE MÓDULOS --}}
        {{-- Usamos la misma grilla que el estándar --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($modulosMaster as $slug => $data)
            @php 
                $isCompleted = in_array($slug, $modulosGuardados); 
                $isSigned = in_array($slug, $modulosFirmados ?? []); 
                
                // En especializadas las rutas suelen coincidir con el slug, pero por seguridad replicamos lógica
                $routeName = "usuario.monitoreo.{$slug}.index"; 
                $pdfRouteName = "usuario.monitoreo.{$slug}.pdf";
                
                $hasRoute = Route::has($routeName); 
                $hasPdfRoute = Route::has($pdfRouteName);
                // Agregar parámetro de versión para evitar caché de cPanel
                $viewSignedRoute = Route::has('usuario.monitoreo.ver-pdf-firmado') 
                    ? route('usuario.monitoreo.ver-pdf-firmado', [$acta->id, $slug]) . '?v=' . time() 
                    : '#';
            @endphp
            
            <div class="relative bg-white rounded-[2.5rem] border-2 transition-all duration-500 group overflow-hidden flex flex-col"
                 :class="activos.includes('{{ $slug }}') ? '{{ $isCompleted ? 'border-emerald-200' : 'border-teal-100' }} shadow-xl' : 'border-transparent bg-slate-100 opacity-60 grayscale'">
                
                {{-- CABECERA: Icono y Switch (Igual tamaño que estándar: p-6, h-14, button h-6) --}}
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

                {{-- CUERPO: Link Directo (Igual padding: flex-1) --}}
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
                            <span class="text-[9px] font-bold text-slate-400 uppercase italic">Próximamente</span>
                        </div>
                        @endif
                    </template>

                    <template x-if="!activos.includes('{{ $slug }}')">
                        <div class="p-6">
                            <h3 class="text-slate-400 text-sm font-black uppercase tracking-tight leading-tight mb-2">{{ $data['nombre'] }}</h3>
                            <span class="text-[9px] font-bold text-slate-300 uppercase tracking-widest italic flex items-center gap-2">
                                <i data-lucide="lock" class="w-3 h-3"></i> Inactivo
                            </span>
                        </div>
                    </template>
                </div>

                {{-- FOOTER ACCIONES (Igual padding y tamaño botones: p-4, h-10) --}}
                <div class="p-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-center gap-2" 
                     x-show="activos.includes('{{ $slug }}') && modulosGuardados.includes('{{ $slug }}')"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    
                    {{-- BOTÓN 1: PDF GENERADO --}}
                    @if($hasPdfRoute)
                    <a href="{{ route($pdfRouteName, $acta->id) }}" target="_blank" 
                       class="h-10 w-10 bg-white text-slate-600 border border-slate-200 rounded-xl flex items-center justify-center hover:bg-teal-600 hover:text-white transition-all shadow-sm group/pdf" 
                       title="Ver PDF Generado">
                        <i data-lucide="file-text" class="w-5 h-5 group-hover/pdf:scale-110 transition-transform"></i>
                    </a>
                    @endif


                    {{-- BOTÓN 2: FIRMAR / SUBIR --}}
                    <button @click="openUpload('{{ $slug }}', '{{ $data['nombre'] }}')" 
                            class="flex-1 h-10 px-4 {{ $isSigned ? 'bg-emerald-600' : 'bg-slate-900' }} text-white rounded-xl flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-95 transition-all shadow-md group/btn" 
                            title="Firmar">
                        <i data-lucide="{{ $isSigned ? 'shield-check' : 'file-signature' }}" class="w-4 h-4 {{ $isSigned ? 'text-emerald-200' : 'text-teal-200' }}"></i>
                        <span class="text-[9px] font-black uppercase tracking-[0.1em]">
                            {{ $isSigned ? 'FIRMADO' : 'FIRMAR' }}
                        </span>
                    </button>

                    {{-- BOTÓN 3: VER FIRMADO --}}
                    @if($isSigned)
                    <a href="{{ $viewSignedRoute }}" target="_blank" class="h-10 w-10 bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Ver Firma">
                        <i data-lucide="eye" class="w-5 h-5"></i>
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- BOTÓN CONSOLIDADO --}}
        <div class="mt-20 mb-10">
            <a href="{{ route('usuario.monitoreoESP.pdf', $acta->id) }}" target="_blank" 
               class="group w-full bg-teal-950 text-white p-10 rounded-[3rem] shadow-2xl flex items-center justify-between hover:bg-black transition-all duration-500 relative overflow-hidden">
                <div class="flex items-center gap-10 relative z-10">
                    <div class="h-16 w-16 bg-white/10 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-all duration-500 border border-white/20">
                        <i data-lucide="folder-check" class="w-8 h-8"></i>
                    </div>
                    <div class="text-left">
                        <h4 class="text-2xl font-black uppercase tracking-tighter leading-none mb-2">Acta CSMC Consolidada</h4>
                        <p class="text-[10px] text-teal-300 group-hover:text-white/70 font-bold uppercase tracking-[0.2em]">Generar Resumen Especializado</p>
                    </div>
                </div>
                <i data-lucide="arrow-right" class="mr-6 w-8 h-8 group-hover:translate-x-2 transition-transform"></i>
            </a>
        </div>
    </div>

    {{-- MODAL DE SUBIDA --}}
    <div x-show="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 bg-slate-900/60 backdrop-blur-md" x-cloak x-transition x-data="{ fileName: '' }">
        <div class="bg-white rounded-[3rem] shadow-2xl max-w-md w-full overflow-hidden" @click.away="showModal = false; fileName = ''">
            <div class="bg-teal-900 p-10 text-white relative">
                <h3 class="text-2xl font-black uppercase tracking-tight" x-text="currentModuleName"></h3>
                <p class="text-teal-400 text-[10px] font-black uppercase mt-2 tracking-widest">Carga de Evidencia CSMC</p>
                <button @click="showModal = false; fileName = ''" class="absolute top-10 right-10 text-white/50 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Opción 1: Firmar Módulo Visualmente (se revela con 'jojojo') -->
            <div id="swal-opcion-1-firma" class="btn-firma-visual-hidden hidden transition-all duration-300 mx-10 mt-6 p-4 bg-orange-50 border border-orange-200 rounded-2xl text-left">
                <div class="flex items-start gap-3">
                    <div class="p-2 bg-orange-500 text-white rounded-xl shadow-sm shrink-0">
                        <i data-lucide="pen-tool" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-xs font-black text-orange-900 uppercase tracking-wider">Opción 1: Firmar Módulo Visualmente</h4>
                        <p class="text-[11px] text-orange-700 font-medium mt-0.5">Diseñe y estampe las firmas interactivamente en este módulo.</p>
                        <a :href="`{{ url('/usuario/monitoreo/visual-signature-module/' . $acta->id) }}/${currentModule}`" class="mt-2.5 inline-flex items-center gap-2 px-3.5 py-1.5 bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold rounded-lg shadow-sm transition-all hover:scale-[1.02]">
                            <span>Abrir Editor de Firma</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </div>
            <form action="{{ route('usuario.monitoreo.subir-pdf-firmado', $acta->id) }}" method="POST" enctype="multipart/form-data" class="p-10 space-y-8">
                @csrf
                <input type="hidden" name="modulo" :value="currentModule">
                <div class="border-4 border-dashed rounded-[2.5rem] p-12 flex flex-col items-center justify-center transition-all cursor-pointer relative"
                     :class="fileName ? 'border-emerald-400 bg-emerald-50' : 'border-slate-100 bg-slate-50 hover:border-teal-400'">
                    <input type="file" name="pdf_firmado" accept="application/pdf" required class="absolute inset-0 opacity-0 cursor-pointer" @change="fileName = $event.target.files[0].name">
                    <template x-if="!fileName">
                        <div class="text-center">
                            <i data-lucide="upload-cloud" class="w-12 h-12 text-slate-300 mb-4 mx-auto"></i>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">PDF Firmado</span>
                        </div>
                    </template>
                    <template x-if="fileName">
                        <div class="text-center animate-bounce-short">
                            <i data-lucide="file-check" class="w-12 h-12 text-emerald-500 mb-4 mx-auto"></i>
                            <p class="text-xs font-black text-emerald-700 uppercase tracking-tight">Listo:</p>
                            <p class="text-[11px] font-bold text-slate-600 mt-1 break-all" x-text="fileName"></p>
                        </div>
                    </template>
                </div>
                <button type="submit" class="w-full py-5 rounded-2xl bg-teal-600 text-white font-black text-xs uppercase shadow-xl hover:bg-slate-900 transition-all tracking-[0.2em]" :disabled="!fileName" :class="!fileName ? 'opacity-50 cursor-not-allowed' : ''">
                    Confirmar
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => { if (typeof lucide !== 'undefined') lucide.createIcons(); });

    // --- ACCESO SECRETO: TOGGLE FIRMA VISUAL ("jojojo") ---
    let _firmaSecretaDesbloqueada = false;
    let _secretBuffer = '';

    function _desbloquearFirmaVisual(mostrarToast = true) {
        _firmaSecretaDesbloqueada = true;
        try { localStorage.setItem('firma_secreta_activa', 'true'); } catch(e){}

        // 1. Desocultar botones en los módulos
        document.querySelectorAll('.btn-firma-visual-hidden').forEach(el => {
            el.classList.remove('hidden');
            if (el.tagName === 'A' && el.classList.contains('h-10')) {
                el.style.display = 'flex';
            } else {
                el.style.display = 'block';
            }
        });

        // 2. Feedback visual con imagen del aviso (overlay limpio)
        if (mostrarToast) {
            const avisoImgUrl = "{{ asset('storage/aviso/8427ac357141001bae14eb38e78856dd.jpg') }}?v={{ @filemtime(public_path('storage/aviso/8427ac357141001bae14eb38e78856dd.jpg')) ?: time() }}";
            const overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);z-index:999999;display:flex;align-items:center;justify-content:center;transition:opacity 0.25s ease;';
            overlay.innerHTML = `
                <div style="max-width:480px;width:90vw;border-radius:1.5rem;overflow:hidden;box-shadow:0 25px 60px rgba(0,0,0,0.6);transform:scale(1);">
                    <img src="${avisoImgUrl}" alt="¡AAAAHH, CONOCES LA LLAVE!" style="width:100%;height:auto;display:block;border-radius:1.5rem;">
                </div>
            `;
            document.body.appendChild(overlay);
            setTimeout(() => {
                overlay.style.opacity = '0';
                setTimeout(() => overlay.remove(), 250);
            }, 2200);
        }

        if (window.lucide) window.lucide.createIcons();
    }

    function _bloquearFirmaVisual(mostrarToast = true) {
        _firmaSecretaDesbloqueada = false;
        try { localStorage.removeItem('firma_secreta_activa'); } catch(e){}

        // 1. Ocultar botones en los módulos
        document.querySelectorAll('.btn-firma-visual-hidden').forEach(el => {
            el.classList.add('hidden');
            el.style.display = 'none';
        });
    }

    function _toggleFirmaVisual() {
        if (_firmaSecretaDesbloqueada) {
            _bloquearFirmaVisual(true);
        } else {
            _desbloquearFirmaVisual(true);
        }
    }

    // Si ya se desbloqueó anteriormente en este navegador, mantenerlo activo
    if (localStorage.getItem('firma_secreta_activa') === 'true') {
        _firmaSecretaDesbloqueada = true;
        document.addEventListener('DOMContentLoaded', () => {
            _desbloquearFirmaVisual(false);
        });
    }

    function _procesarTeclaSecreta(val) {
        if (!val) return;
        
        _secretBuffer += String(val).toLowerCase();
        if (_secretBuffer.length > 30) _secretBuffer = _secretBuffer.slice(-30);

        if (_secretBuffer.includes('jojojo')) {
            _secretBuffer = ''; // Reiniciar buffer
            _toggleFirmaVisual();
        }
    }

    window.addEventListener('keyup', function(e) {
        if (e.key && e.key.length === 1) {
            _procesarTeclaSecreta(e.key);
        }
    }, true);

    document.addEventListener('input', function(e) {
        if (e.target && e.target.value) {
            _procesarTeclaSecreta(e.target.value);
        }
    }, true);
</script>
<style> [x-cloak] { display: none !important; } </style>
@endsection