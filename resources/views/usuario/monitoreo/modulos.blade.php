@extends('layouts.usuario')

@section('title', 'Gestión de Consultorios | ' . $acta->establecimiento->nombre)

@section('content')
<div class="py-12 bg-[#f4f7fa] min-h-screen" 
     x-data="{ 
        showNewModal: false,
        showEditModal: false,
        editModalSlug: '',
        editModalTitle: '',
        editModalAction: '',
        showUploadModal: false,
        currentModule: '',
        currentModuleName: '',
        openUpload(slug, name) {
            this.currentModule = slug;
            this.currentModuleName = name;
            this.showUploadModal = true;
        }
     }">
    
    <div class="max-w-6xl mx-auto px-6">
        
        {{-- ENCABEZADO EJECUTIVO --}}
        <div class="bg-indigo-900 rounded-[2.5rem] p-10 shadow-2xl mb-12 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32 blur-3xl"></div>
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 relative z-10 text-white">
                <div class="flex items-center gap-8">
                    <div class="h-20 w-20 rounded-3xl bg-indigo-500 flex items-center justify-center shadow-lg border border-indigo-400">
                        <i data-lucide="hospital" class="text-white w-10 h-10"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-3 py-1 bg-emerald-500 text-white text-[10px] font-black rounded-lg uppercase tracking-widest">Acta Activa</span>
                            <span class="text-indigo-200 text-[11px] font-bold uppercase tracking-widest">ACTA DE MONITOREO N°{{ str_pad($acta->numero_acta, 5, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <h2 class="text-3xl font-black tracking-tight uppercase italic">{{ $acta->establecimiento->nombre }}</h2>
                        <div class="flex flex-wrap items-center gap-4 mt-2 text-indigo-200 text-xs font-bold uppercase tracking-widest">
                            <span class="flex items-center gap-1.5"><i data-lucide="user" class="w-4 h-4 text-emerald-400"></i> Implementador: {{ $acta->implementador ?? ($acta->user ? "{$acta->user->apellido_paterno} {$acta->user->name}" : 'NO ASIGNADO') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <button @click="showNewModal = true" 
                            class="flex items-center gap-3 px-8 py-4 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-black text-xs uppercase tracking-widest shadow-xl transition-all hover:scale-105">
                        <i data-lucide="plus-circle" class="w-5 h-5"></i> Añadir Nuevo Consultorio
                    </button>
                    <a href="{{ route('usuario.monitoreo.index') }}" 
                       class="group flex items-center gap-3 px-8 py-4 rounded-2xl bg-white/10 hover:bg-white hover:text-indigo-900 border border-white/20 transition-all font-black text-xs uppercase tracking-widest">
                        <i data-lucide="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i> Volver
                    </a>
                </div>
            </div>
        </div>

        {{-- ALERTA MENSAJES --}}
        @if(session('success'))
            <div class="mb-8 p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-2xl flex items-center gap-3 font-bold text-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-2xl flex items-center gap-3 font-bold text-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- REPORTES CONSOLIDADOS POR SERVICIO --}}
        @php
            $serviciosDetectados = [];
            foreach($consultoriosDinamicos as $c) {
                $cc = is_array($c->contenido) ? $c->contenido : (json_decode($c->contenido, true) ?? []);
                $svc = mb_strtoupper(trim($cc['servicio_asociado'] ?? ''));
                if ($svc !== '') {
                    $serviciosDetectados[$svc] = ($serviciosDetectados[$svc] ?? 0) + 1;
                }
            }
            ksort($serviciosDetectados);
        @endphp

        @if(count($serviciosDetectados) > 0)
        <div class="mb-10 bg-white rounded-[2rem] border border-indigo-100 shadow-lg overflow-hidden">
            <div class="flex items-center gap-4 px-7 py-5 border-b border-indigo-50 bg-gradient-to-r from-indigo-50 to-white">
                <div class="h-10 w-10 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-md flex-shrink-0">
                    <i data-lucide="layers" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-slate-900 font-black text-sm uppercase tracking-tight">Reportes Consolidados por Servicio</h3>
                    <p class="text-slate-400 text-[11px] font-semibold mt-0.5">
                        {{ count($serviciosDetectados) }} {{ count($serviciosDetectados) === 1 ? 'servicio detectado' : 'servicios detectados' }} en esta acta — un PDF por servicio, agrupa todos sus consultorios
                    </p>
                </div>
            </div>
            <div class="px-7 py-5 flex flex-wrap gap-3">
                @foreach($serviciosDetectados as $svcNombre => $svcCount)
                    <a href="{{ route('usuario.monitoreo.consultorio.pdf-servicio', [$acta->id, urlencode($svcNombre)]) }}?v={{ time() }}"
                       target="_blank"
                       class="group inline-flex items-center gap-2.5 px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-[11px] uppercase tracking-wide shadow-md hover:shadow-indigo-200 transition-all hover:scale-105">
                        <i data-lucide="file-down" class="w-4 h-4 opacity-80 group-hover:opacity-100"></i>
                        <span>{{ $svcNombre }}</span>
                        <span class="bg-white/20 rounded-lg px-2 py-0.5 text-[10px] font-black">
                            {{ $svcCount }} {{ $svcCount === 1 ? 'consultorio' : 'consultorios' }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- GRID DE MÓDULOS Y CONSULTORIOS --}}

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            {{-- 1. MÓDULO FIJO: INFRAESTRUCTURA Y CROQUIS 2D --}}
            <div class="relative bg-white rounded-[2.5rem] border-2 border-indigo-200 shadow-xl transition-all duration-500 group overflow-hidden flex flex-col hover:border-indigo-400">
                <div class="p-6 pb-0 flex justify-between items-start z-10">
                    <div class="h-14 w-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white shadow-lg">
                        <i data-lucide="box" class="w-7 h-7"></i>
                    </div>
                    <span class="px-3 py-1 bg-indigo-100 text-indigo-700 font-black text-[9px] rounded-lg uppercase tracking-widest">
                        Módulo Fijo
                    </span>
                </div>

                <div class="flex-1">
                    <a href="{{ route('usuario.monitoreo.infraestructura-2d.index', $acta->id) }}" class="block p-6 group/link">
                        <h3 class="text-slate-800 text-sm font-black uppercase tracking-tight leading-tight mb-2 group-hover/link:text-indigo-600 transition-colors">
                            Infraestructura y Croquis 2D
                        </h3>
                        <span class="text-[9px] font-black uppercase tracking-widest text-indigo-500 flex items-center gap-1">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i> Diseñador & Sincronización 2D
                        </span>
                    </a>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-center gap-2">
                    <a href="{{ route('usuario.monitoreo.infraestructura-2d.pdf', $acta->id) }}" target="_blank" 
                       class="flex-1 h-10 bg-white text-indigo-600 border border-indigo-200 rounded-xl flex items-center justify-center gap-2 hover:bg-indigo-600 hover:text-white transition-all shadow-sm font-black text-[10px] uppercase">
                        <i data-lucide="file-text" class="w-4 h-4"></i> Generar Reporte 2D
                    </a>
                </div>
            </div>

            {{-- 2. MÓDULO FIJO: RR.HH (RECURSOS HUMANOS) --}}
            @php
                $rrhhContent = isset($moduloRrhh->contenido) ? (is_array($moduloRrhh->contenido) ? $moduloRrhh->contenido : json_decode($moduloRrhh->contenido, true)) : [];
                $totalTrabajadoresRrhh = count($rrhhContent['trabajadores'] ?? []);
            @endphp
            <div class="relative bg-white rounded-[2.5rem] border-2 border-violet-200 shadow-xl transition-all duration-500 group overflow-hidden flex flex-col hover:border-violet-400">
                <div class="p-6 pb-0 flex justify-between items-start z-10">
                    <div class="h-14 w-14 rounded-2xl bg-violet-600 flex items-center justify-center text-white shadow-lg">
                        <i data-lucide="users-round" class="w-7 h-7"></i>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($totalTrabajadoresRrhh > 0)
                            <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 font-black text-[9px] rounded-lg uppercase tracking-widest flex items-center gap-1">
                                <i data-lucide="check" class="w-3 h-3"></i> {{ $totalTrabajadoresRrhh }} Registrados
                            </span>
                        @endif
                        <span class="px-3 py-1 bg-violet-100 text-violet-700 font-black text-[9px] rounded-lg uppercase tracking-widest">
                            Módulo Fijo
                        </span>
                    </div>
                </div>

                <div class="flex-1">
                    <a href="{{ route('usuario.monitoreo.rrhh.index', $acta->id) }}" class="block p-6 group/link">
                        <h3 class="text-slate-800 text-sm font-black uppercase tracking-tight leading-tight mb-2 group-hover/link:text-violet-600 transition-colors">
                            RR.HH (Recursos Humanos)
                        </h3>
                        <span class="text-[9px] font-black uppercase tracking-widest text-violet-500 flex items-center gap-1">
                            <i data-lucide="id-card" class="w-3.5 h-3.5"></i> Padrón de Personal por Servicio
                        </span>
                    </a>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-center gap-2">
                    <a href="{{ route('usuario.monitoreo.rrhh.pdf', $acta->id) }}?v={{ time() }}" target="_blank" 
                       class="flex-1 h-10 bg-white text-violet-600 border border-violet-200 rounded-xl flex items-center justify-center gap-2 hover:bg-violet-600 hover:text-white transition-all shadow-sm font-black text-[10px] uppercase">
                        <i data-lucide="file-text" class="w-4 h-4"></i> Generar Reporte RR.HH
                    </a>
                </div>
            </div>

            {{-- 2. TARJETA PARA AÑADIR NUEVO CONSULTORIO --}}
            <div @click="showNewModal = true" 
                 class="cursor-pointer bg-dashed border-2 border-dashed border-emerald-300 hover:border-emerald-500 bg-emerald-50/50 hover:bg-emerald-50 rounded-[2.5rem] p-8 flex flex-col items-center justify-center text-center transition-all group min-h-[220px]">
                <div class="h-16 w-16 rounded-3xl bg-emerald-500 text-white flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform mb-4">
                    <i data-lucide="plus" class="w-8 h-8"></i>
                </div>
                <h3 class="text-slate-800 font-black text-sm uppercase tracking-tight mb-1">Añadir Nuevo Consultorio</h3>
                <p class="text-slate-500 text-xs font-bold uppercase">Haz clic para nombrar e iniciar evaluación</p>
            </div>

            {{-- 3. LISTADO DE CONSULTORIOS REGISTRADOS --}}
            @foreach($consultoriosDinamicos as $index => $consultorio)
                @php
                    $cSlug = $consultorio->modulo_nombre;
                    $cContent = is_array($consultorio->contenido) ? $consultorio->contenido : (json_decode($consultorio->contenido, true) ?? []);
                    $cTitulo = $cContent['titulo_consultorio'] ?? ('Consultorio ' . ($index + 1));
                    $isSigned = !empty($consultorio->pdf_firmado_path);

                    // Si es FUNCIONAL vinculado a un físico, buscar su título para
                    // mostrarlo como badge (sin consulta extra: ya está en la colección).
                    $cVinculadoTitulo = null;
                    if (strtoupper($cContent['tipo_consultorio'] ?? '') === 'FUNCIONAL' && !empty($cContent['consultorio_vinculado'])) {
                        $otroFisico = $consultoriosDinamicos->firstWhere('modulo_nombre', $cContent['consultorio_vinculado']);
                        if ($otroFisico) {
                            $otroFisicoContent = is_array($otroFisico->contenido) ? $otroFisico->contenido : (json_decode($otroFisico->contenido, true) ?? []);
                            $cVinculadoTitulo = $otroFisicoContent['titulo_consultorio'] ?? $cContent['consultorio_vinculado'];
                        }
                    }

                    // Consultorios funcionales que dependen de este (lo tienen como
                    // su físico vinculado), para advertir antes de eliminarlo.
                    $cDependientes = $consultoriosDinamicos->filter(function ($otro) use ($cSlug) {
                        $oc = is_array($otro->contenido) ? $otro->contenido : (json_decode($otro->contenido, true) ?? []);
                        return ($oc['consultorio_vinculado'] ?? null) === $cSlug;
                    })->map(function ($otro) {
                        $oc = is_array($otro->contenido) ? $otro->contenido : (json_decode($otro->contenido, true) ?? []);
                        return $oc['titulo_consultorio'] ?? $otro->modulo_nombre;
                    })->values();

                    $mensajeConfirmarEliminar = $cDependientes->isNotEmpty()
                        ? addslashes('⚠ ' . $cDependientes->implode(', ') . ' ' . ($cDependientes->count() === 1 ? 'está vinculado' : 'están vinculados') . ' a este consultorio físico y perderá(n) la electricidad, tomas, punto de red y conectividad heredada si lo eliminas. ¿Eliminar de todas formas?')
                        : addslashes('¿Estás seguro de eliminar este consultorio de la lista?');
                @endphp

                <div class="relative bg-white rounded-[2.5rem] border-2 border-emerald-200 shadow-xl transition-all duration-500 group overflow-hidden flex flex-col hover:border-emerald-400">
                    <div class="p-6 pb-0 flex justify-between items-start z-10">
                        <div class="h-14 w-14 rounded-2xl bg-emerald-500 flex items-center justify-center text-white shadow-lg">
                            <i data-lucide="stethoscope" class="w-7 h-7"></i>
                        </div>
                        
                        <div class="flex items-center gap-1.5">
                            <button type="button" @click="editModalSlug = '{{ $cSlug }}'; editModalTitle = '{{ addslashes($cTitulo) }}'; editModalAction = '{{ route('usuario.monitoreo.consultorio.renombrar', [$acta->id, $cSlug]) }}'; showEditModal = true;" 
                                    class="h-8 w-8 rounded-xl bg-slate-100 hover:bg-emerald-100 text-slate-400 hover:text-emerald-600 flex items-center justify-center transition-colors" title="Renombrar Consultorio">
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            </button>
                            
                            <form action="{{ route('usuario.monitoreo.consultorio.destroy', [$acta->id, $cSlug]) }}" method="POST" onsubmit="return confirm('{{ $mensajeConfirmarEliminar }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="h-8 w-8 rounded-xl bg-slate-100 hover:bg-rose-100 text-slate-400 hover:text-rose-600 flex items-center justify-center transition-colors" title="Eliminar Consultorio">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="flex-1">
                        <a href="{{ route('usuario.monitoreo.consultorio.show', [$acta->id, $cSlug]) }}" class="block p-6 group/link">
                            <h3 class="text-slate-800 text-sm font-black uppercase tracking-tight leading-tight mb-2 group-hover/link:text-emerald-600 transition-colors">
                                {{ $cTitulo }}
                            </h3>
                            <span class="text-[9px] font-black uppercase tracking-widest text-emerald-500 flex items-center gap-1">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Evaluación Registrada
                            </span>
                            @if($cVinculadoTitulo)
                                <span class="text-[9px] font-black uppercase tracking-widest text-indigo-500 flex items-center gap-1 mt-1">
                                    <i data-lucide="link" class="w-3.5 h-3.5"></i> Vinculado a: {{ $cVinculadoTitulo }}
                                </span>
                            @endif
                        </a>
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-center gap-2">
                        <a href="{{ route('usuario.monitoreo.consultorio.show', [$acta->id, $cSlug]) }}" 
                           class="h-10 w-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center hover:bg-emerald-700 transition-all shadow-sm" title="Evaluar / Editar Formulario">
                            <i data-lucide="edit-3" class="w-5 h-5"></i>
                        </a>

                        <a href="{{ route('usuario.monitoreo.consultorio.pdf', [$acta->id, $cSlug]) }}?v={{ time() }}" target="_blank" 
                           class="h-10 w-10 bg-white text-slate-600 border border-slate-200 rounded-xl flex items-center justify-center hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Previsualizar Reporte PDF">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            @endforeach

        </div>
    </div>

    {{-- MODAL: NUEVO CONSULTORIO --}}
    <div x-show="showNewModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showNewModal = false" 
             class="bg-white rounded-[2.5rem] p-8 max-w-lg w-full shadow-2xl border border-slate-100 transform transition-all">
            
            <div class="flex items-center gap-4 mb-6 pb-4 border-b border-slate-100">
                <div class="h-12 w-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-md">
                    <i data-lucide="plus-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 uppercase">Añadir Nuevo Consultorio</h3>
                    <p class="text-slate-400 font-bold text-xs">Ingrese el nombre descriptivo del consultorio o módulo</p>
                </div>
            </div>

            <form action="{{ route('usuario.monitoreo.consultorio.crear', $acta->id) }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Nombre del Consultorio / Módulo</label>
                    <input type="text" name="titulo_consultorio" required placeholder="EJ: GESTIÓN ADMINISTRATIVA, CONSULTORIO DE MEDICINA, TRIAJE..." 
                           class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 rounded-2xl p-4 font-bold text-slate-800 uppercase outline-none transition-all">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="showNewModal = false" 
                            class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black rounded-xl text-xs uppercase tracking-widest transition-all">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs uppercase tracking-widest shadow-lg hover:shadow-emerald-200 transition-all flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Crear e Iniciar Evaluación
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: EDITAR / RENOMBRAR CONSULTORIO --}}
    <div x-show="showEditModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div @click.away="showEditModal = false" 
             class="bg-white rounded-[2.5rem] p-8 max-w-lg w-full shadow-2xl border border-slate-100 transform transition-all">
            
            <div class="flex items-center gap-4 mb-6 pb-4 border-b border-slate-100">
                <div class="h-12 w-12 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-md">
                    <i data-lucide="edit-3" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900 uppercase">Renombrar Consultorio</h3>
                    <p class="text-slate-400 font-bold text-xs">Modifique la denominación o título de este consultorio</p>
                </div>
            </div>

            <form :action="editModalAction" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-slate-500 text-[10px] font-black uppercase tracking-widest mb-2">Nombre del Consultorio / Módulo</label>
                    <input type="text" name="nuevo_titulo" x-model="editModalTitle" required placeholder="EJ: GESTIÓN ADMINISTRATIVA, CONSULTORIO DE MEDICINA, TRIAJE..." 
                           class="w-full bg-slate-50 border-2 border-slate-200 focus:border-emerald-500 rounded-2xl p-4 font-bold text-slate-800 uppercase outline-none transition-all">
                </div>

                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="showEditModal = false" 
                            class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-black rounded-xl text-xs uppercase tracking-widest transition-all">
                        Cancelar
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl text-xs uppercase tracking-widest shadow-lg hover:shadow-emerald-200 transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Guardar Nombre
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endsection