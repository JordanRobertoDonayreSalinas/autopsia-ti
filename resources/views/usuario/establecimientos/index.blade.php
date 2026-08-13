@extends('layouts.usuario')

@section('title', 'Establecimientos')

@push('styles')
    <style>
        [x-cloak] { display: none !important; }
    </style>
@endpush

@section('header-content')
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Establecimientos</h1>
    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
        <span>Operaciones</span>
        <span class="text-slate-300">•</span>
        <span>Catálogo de Establecimientos</span>
    </div>
@endsection

@section('content')
    <div class="w-full">
        {{-- Tarjeta informativa --}}
        <div class="bg-gradient-to-r from-blue-700 to-indigo-600 p-6 rounded-2xl shadow-xl mb-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <h2 class="text-xl font-extrabold text-white">Catálogo Maestro de Establecimientos</h2>
                    <p class="text-xs text-blue-100 mt-1 max-w-xl">
                        Gestione el registro único de establecimientos de salud, incluyendo la actualización del tipo y número de documento, responsables y ubicación geográfica.
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-slate-900/60 backdrop-blur text-white rounded-xl px-5 py-2.5 shadow-lg border border-white/10 flex flex-col items-center min-w-[120px]">
                        <span class="text-2xl font-bold leading-none">{{ $establecimientos->total() }}</span>
                        <span class="text-[0.65rem] uppercase tracking-widest text-slate-300 font-semibold mt-1">Registrados</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtros de Búsqueda --}}
        <form method="GET" action="{{ route('usuario.establecimientos.index') }}" id="filterForm"
            class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6">
            <div class="flex flex-wrap lg:flex-nowrap items-end gap-3">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-3 flex-grow w-full">
                    {{-- Búsqueda General --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">Buscar por nombre o código</label>
                        <select name="search" id="searchSelect" class="w-full text-xs font-bold text-slate-700 border-slate-200 bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500 py-2">
                            <option value="">TODOS</option>
                            @foreach ($todosEstablecimientos as $est)
                                <option value="{{ $est->nombre }}" {{ request('search') == $est->nombre ? 'selected' : '' }}>
                                    {{ $est->codigo }} - {{ $est->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Provincia --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">Provincia</label>
                        <select name="provincia" id="provinciaSelect" class="w-full text-xs font-bold text-slate-700 border-slate-200 bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500 py-2">
                            <option value="">TODAS</option>
                            @foreach ($provincias as $prov)
                                <option value="{{ $prov }}" {{ request('provincia') == $prov ? 'selected' : '' }}>{{ $prov }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Distrito --}}
                    <div>
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1 tracking-wider">Distrito</label>
                        <select name="distrito" id="distritoSelect" class="w-full text-xs font-bold text-slate-700 border-slate-200 bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500 py-2">
                            <option value="">TODOS</option>
                            @foreach ($distritos as $dist)
                                <option value="{{ $dist }}" {{ request('distrito') == $dist ? 'selected' : '' }}>{{ $dist }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <div class="flex items-center gap-2 shrink-0 w-full lg:w-auto mt-3 lg:mt-0">
                    <button type="submit" class="w-full lg:w-auto px-5 h-10 flex items-center justify-center rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-500/20 transition-all hover:scale-105 text-xs font-bold gap-2">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        <span>Buscar</span>
                    </button>
                    <a href="{{ route('usuario.establecimientos.index') }}" 
                        class="w-full lg:w-auto px-5 h-10 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 shadow-sm transition-all hover:scale-105 border border-slate-200 text-xs font-bold gap-2">
                        <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                        <span>Limpiar</span>
                    </a>
                </div>
            </div>
        </form>

        {{-- Notificaciones de Éxito --}}
        @if(session('success'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 5000)" 
                 x-transition:leave="transition ease-in duration-300"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
                    <p class="text-sm font-bold">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-emerald-400 hover:text-emerald-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        {{-- Tabla de Datos --}}
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-800">
                        <tr>
                            <th class="px-4 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Código</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Nombre del Establecimiento</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Categoría</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Ubicación</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Responsable</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Tipo Doc.</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-white uppercase tracking-wider">Nro Documento</th>
                            <th class="px-4 py-3 text-[10px] font-bold text-white uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($establecimientos as $est)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3.5 font-mono font-bold text-blue-600">
                                    {{ $est->codigo }}
                                </td>
                                <td class="px-4 py-3.5 font-bold text-slate-800">
                                    {{ $est->nombre }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-extrabold uppercase border border-slate-200">
                                        {{ $est->categoria ?? 'S/C' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500">
                                    <div class="font-semibold text-slate-700">{{ $est->provincia }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $est->distrito }}</div>
                                </td>
                                <td class="px-4 py-3.5 font-medium text-slate-600">
                                    {{ $est->responsable ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($est->tipo_documento)
                                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 text-[10px] font-bold uppercase border border-blue-100">
                                            {{ $est->tipo_documento }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 font-semibold text-slate-700">
                                    {{ $est->numero_documento ?? '-' }}
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <a href="{{ route('usuario.establecimientos.edit', $est->id) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[10px] rounded-lg transition-all border border-blue-100"
                                       title="Editar Establecimiento">
                                        <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                        <span>Editar</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-slate-400 italic">
                                    No se encontraron establecimientos.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación --}}
            @if($establecimientos->hasPages())
                <div class="px-4 py-3 bg-slate-50 border-t border-slate-100">
                    {{ $establecimientos->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof lucide !== 'undefined') lucide.createIcons();

            const provinciaSelect = document.getElementById('provinciaSelect');
            const distritoSelect = document.getElementById('distritoSelect');
            const searchSelect = document.getElementById('searchSelect');

            if (provinciaSelect) {
                provinciaSelect.addEventListener('change', async () => {
                    const provincia = provinciaSelect.value;
                    
                    // Limpiar selectores dependientes
                    distritoSelect.innerHTML = '<option value="">TODOS</option>';
                    if (searchSelect) {
                        searchSelect.innerHTML = '<option value="">TODOS</option>';
                    }

                    if (provincia) {
                        try {
                            // Cargar Distritos
                            const resDist = await fetch(`{{ route('usuario.establecimientos.ajax.distritos') }}?provincia=${provincia}`);
                            const distritos = await resDist.json();
                            distritos.forEach(d => {
                                const opt = document.createElement('option');
                                opt.value = d;
                                opt.textContent = d;
                                distritoSelect.appendChild(opt);
                            });

                            // Cargar Establecimientos
                            actualizarEstablecimientos(provincia, '');
                        } catch (error) {
                            console.error('Error fetching distritos:', error);
                        }
                    } else {
                        actualizarEstablecimientos('', '');
                    }
                });
            }

            if (distritoSelect) {
                distritoSelect.addEventListener('change', () => {
                    const provincia = provinciaSelect.value;
                    const distrito = distritoSelect.value;
                    actualizarEstablecimientos(provincia, distrito);
                });
            }

            async function actualizarEstablecimientos(provincia, distrito) {
                if (!searchSelect) return;
                searchSelect.innerHTML = '<option value="">TODOS</option>';
                try {
                    const resEst = await fetch(`{{ route('usuario.establecimientos.ajax.establecimientos') }}?provincia=${provincia}&distrito=${distrito}`);
                    const establecimientos = await resEst.json();
                    establecimientos.forEach(e => {
                        const opt = document.createElement('option');
                        opt.value = e.nombre;
                        opt.textContent = `${e.codigo} - ${e.nombre}`;
                        searchSelect.appendChild(opt);
                    });
                } catch (error) {
                    console.error('Error fetching establecimientos:', error);
                }
            }
        });
    </script>
@endpush
