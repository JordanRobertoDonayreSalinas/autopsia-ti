@extends('layouts.usuario')

@section('title', 'Banco de Firmas')

@section('header-content')
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Banco de Firmas</h1>
    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
        <span>Administración</span>
        <span class="text-slate-300">•</span>
        <span>Banco de Firmas</span>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Alertas --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-100 flex items-center gap-3 shadow-sm animate-fade-in-down">
                <div class="p-2 bg-emerald-100 rounded-full text-emerald-600">
                    <i data-lucide="check" class="w-5 h-5"></i>
                </div>
                <span class="font-bold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-red-50 text-red-800 border border-red-100 flex items-center gap-3 shadow-sm animate-fade-in-down">
                <div class="p-2 bg-red-100 rounded-full text-red-600">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
                <span class="font-bold text-sm">{{ session('error') }}</span>
            </div>
        @endif

        {{-- BUSCADOR Y RESUMEN --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Directorio de Profesionales</h2>
                            <p class="text-xs text-slate-400 font-medium mt-0.5">Gestione las rúbricas para el sellado automático</p>
                        </div>
                        
                        <form action="{{ route('admin.firmas.index') }}" method="GET" class="relative w-full sm:w-64">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Buscar por DNI o Nombre..." 
                                   class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-indigo-500/20 transition-all">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        </form>
                    </div>

                    <div class="overflow-x-auto custom-scroll">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 text-slate-400 text-[11px] uppercase tracking-wider font-bold border-b border-slate-100">
                                    <th class="px-6 py-4">Profesional</th>
                                    <th class="px-6 py-4">Documento</th>
                                    <th class="px-6 py-4 text-center">Estado Firma</th>
                                    <th class="px-6 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @forelse($profesionales as $p)
                                    <tr class="hover:bg-slate-50/80 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-xs border border-slate-200">
                                                    {{ substr($p->nombres, 0, 1) }}{{ substr($p->apellido_paterno, 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="font-bold text-slate-700 text-sm leading-tight">
                                                        {{ $p->apellido_paterno }} {{ $p->apellido_materno }} {{ $p->nombres }}
                                                    </p>
                                                    <p class="text-[10px] text-slate-400 font-medium uppercase mt-0.5">{{ $p->cargo ?? 'Personal' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="text-xs font-bold text-slate-500 font-mono">
                                                {{ $p->doc }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($p->firma_path)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-600 border border-emerald-100">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                    CARGADA
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-400 border border-slate-200">
                                                    PENDIENTE
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex justify-end gap-2">
                                                <button onclick="openUploadModal('{{ $p->doc }}', '{{ $p->nombres }}')" 
                                                        class="p-2 text-indigo-500 hover:bg-indigo-50 rounded-lg transition-all"
                                                        title="Subir Firma">
                                                    <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                                                </button>
                                                @if($p->firma_path)
                                                    <a href="{{ asset('storage/' . $p->firma_path) }}" target="_blank" 
                                                       class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-all"
                                                       title="Ver Firma">
                                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                                    </a>
                                                    <form action="{{ route('admin.firmas.destroy', $p->doc) }}" method="POST" class="inline delete-form">
                                                        @csrf @method('DELETE')
                                                        <button type="button" onclick="confirmDelete(this)" 
                                                                class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-all"
                                                                title="Eliminar Firma">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center gap-2 opacity-30">
                                                <i data-lucide="users-2" class="w-12 h-12"></i>
                                                <p class="text-sm font-bold">No se encontraron profesionales</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($profesionales->hasPages())
                        <div class="p-4 border-t border-slate-50 bg-slate-50/30 flex justify-center">
                            {{ $profesionales->appends(request()->all())->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- PANEL INFORMATIVO --}}
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-3xl p-6 text-white shadow-xl shadow-indigo-200">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-white/20 rounded-xl">
                            <i data-lucide="info" class="w-5 h-5"></i>
                        </div>
                        <h3 class="font-bold">¿Cómo funciona?</h3>
                    </div>
                    <p class="text-xs text-indigo-100 leading-relaxed mb-4">
                        El "Banco de Firmas" centraliza las rúbricas escaneadas de todo el personal. 
                        Cuando un acta es generada, el sistema busca automáticamente la firma mediante el DNI para estamparla en el documento final.
                    </p>
                    <ul class="space-y-2">
                        <li class="flex items-center gap-2 text-[10px] font-medium text-indigo-50">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-400"></i>
                            Formato recomendado: PNG Transparente
                        </li>
                        <li class="flex items-center gap-2 text-[10px] font-medium text-indigo-50">
                            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 text-emerald-400"></i>
                            Fondo limpio (blanco o transparente)
                        </li>
                    </ul>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                    <h3 class="font-bold text-slate-800 text-sm mb-4">Resumen del Banco</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 font-medium">Total Personal</span>
                            <span class="text-sm font-black text-slate-700">{{ \App\Models\Profesional::count() }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-slate-500 font-medium">Firmas Cargadas</span>
                            <span class="text-sm font-black text-emerald-600">{{ \App\Models\Profesional::whereNotNull('firma_path')->count() }}</span>
                        </div>
                        <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                            @php
                                $total = \App\Models\Profesional::count();
                                $cargadas = \App\Models\Profesional::whereNotNull('firma_path')->count();
                                $pct = $total > 0 ? ($cargadas / $total) * 100 : 0;
                            @endphp
                            <div class="h-full bg-emerald-500" style="width: {{ $pct }}%"></div>
                        </div>
                        <p class="text-[10px] text-center text-slate-400 font-bold uppercase tracking-tight">
                            {{ round($pct) }}% de cobertura de firmas
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE CARGA --}}
    <div id="uploadModal" class="fixed inset-0 z-[60] hidden">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeUploadModal()"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-md p-4">
            <div class="bg-white rounded-3xl shadow-2xl overflow-hidden animate-zoom-in">
                <div class="p-6 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h3 class="font-black text-slate-800 uppercase tracking-tight text-sm">Cargar Firma</h3>
                        <p id="modalProfessionalName" class="text-xs text-indigo-600 font-bold"></p>
                    </div>
                    <button onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <form action="{{ route('admin.firmas.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf
                    <input type="hidden" name="doc" id="modalDocInput">
                    
                    <div class="space-y-2">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest">Archivo de Firma</label>
                        <div class="relative group">
                            <input type="file" name="firma" accept="image/*" required
                                   class="w-full px-4 py-3 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl text-xs font-bold text-slate-500 file:hidden cursor-pointer hover:border-indigo-400 transition-all">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <i data-lucide="image" class="w-4 h-4"></i>
                            </div>
                        </div>
                        <p class="text-[10px] text-slate-400 italic">Formatos: PNG, JPG, JPEG (Máx 2MB)</p>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-indigo-200 transition-all active:scale-95 flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        GUARDAR RÚBRICA
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    function openUploadModal(doc, name) {
        document.getElementById('modalDocInput').value = doc;
        document.getElementById('modalProfessionalName').textContent = name;
        document.getElementById('uploadModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
    }

    function confirmDelete(btn) {
        Swal.fire({
            title: '¿Eliminar firma?',
            text: "Esta acción no se puede deshacer.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-3xl border-none',
                confirmButton: 'rounded-xl font-bold px-6',
                cancelButton: 'rounded-xl font-bold px-6'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('form').submit();
            }
        })
    }
</script>
@endpush
