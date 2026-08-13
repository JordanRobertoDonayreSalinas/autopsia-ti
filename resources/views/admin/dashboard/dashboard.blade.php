@extends('layouts.usuario')

@section('title', 'Dashboard — SIHCE')

@section('header-content')
    <div>
        <h1 class="text-base font-bold text-slate-800 tracking-tight">Panel de Control</h1>
        <p class="text-xs text-slate-400 mt-0.5 font-medium tracking-wide">
            Autopsia TI
            <span class="text-slate-300 mx-1">·</span>
            <span id="fecha-actual" class="text-slate-500"></span>
        </p>
    </div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto space-y-5">

    {{-- BANNER INSTITUCIONAL --}}
    <div class="bg-[#0B1F36] rounded-xl px-7 py-5 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-bold text-cyan-400 tracking-[0.2em] uppercase mb-2">Sistema de Información Hospitales y Centros de Atención</p>
            <h2 class="text-lg font-black text-white tracking-tight">
                {{ mb_strtoupper((Auth::user()->apellido_paterno ?? '') . ' ' . (Auth::user()->apellido_materno ?? '') . ', ' . Auth::user()->name, 'UTF-8') }}
            </h2>
            <div class="flex items-center gap-3 mt-2">
                <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 inline-block"></span>
                    Sesión activa
                </span>
                <span class="text-slate-600">·</span>
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                    {{ Auth::user()->role === 'admin' ? 'Administrador del sistema' : (Auth::user()->role === 'operador' ? 'Operador de campo' : 'Usuario') }}
                </span>
            </div>
        </div>
        <div class="hidden lg:block text-right">
            <p class="text-[10px] text-slate-500 uppercase tracking-widest font-semibold mb-1">Fecha del sistema</p>
            <p id="fecha-banner" class="text-sm font-bold text-slate-300"></p>
            <div class="mt-3 flex items-center justify-end gap-1.5">
                <div class="w-2 h-2 rounded-full bg-cyan-500"></div>
                <div class="w-2 h-2 rounded-full bg-cyan-500/40"></div>
                <div class="w-2 h-2 rounded-full bg-cyan-500/20"></div>
            </div>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Total Actas AT --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 flex flex-col gap-4 hover:border-cyan-200 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Total Actas</span>
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <span class="text-4xl font-black text-slate-800 font-mono tabular-nums leading-none">{{ $totalActas }}</span>
                <p class="text-[11px] text-slate-400 mt-2 font-medium">Asistencia Técnica</p>
            </div>
            <div class="h-[3px] bg-cyan-500 rounded-full w-8"></div>
        </div>

        {{-- Usuarios --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 flex flex-col gap-4 hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Usuarios</span>
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <span class="text-4xl font-black text-slate-800 font-mono tabular-nums leading-none">{{ $totalUsuarios }}</span>
                <p class="text-[11px] text-slate-400 mt-2 font-medium">Cuentas del sistema</p>
            </div>
            <div class="h-[3px] bg-slate-300 rounded-full w-8"></div>
        </div>

        {{-- Mes actual --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 flex flex-col gap-4 hover:border-emerald-200 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Este Mes</span>
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <span class="text-4xl font-black text-slate-800 font-mono tabular-nums leading-none">{{ $actasPorMes->first()['total'] ?? 0 }}</span>
                <p class="text-[11px] text-slate-400 mt-2 font-medium">{{ $actasPorMes->first()['nombre_mes'] ?? '—' }}</p>
            </div>
            <div class="h-[3px] bg-emerald-400 rounded-full w-8"></div>
        </div>

        {{-- IPRESS líder --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5 flex flex-col gap-4 hover:border-amber-200 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">IPRESS Líder</span>
                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <span class="text-xs font-bold text-slate-700 leading-snug block">
                    {{ \Illuminate\Support\Str::limit($topEstablecimientos->first()?->nombre ?? '—', 30) }}
                </span>
                <p class="text-[11px] text-slate-400 mt-2 font-medium font-mono">{{ $topEstablecimientos->first()?->actas_count ?? 0 }} actas</p>
            </div>
            <div class="h-[3px] bg-amber-400 rounded-full w-8"></div>
        </div>

    </div>

    {{-- PANEL INFERIOR --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- HISTORIAL MENSUAL --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-700 tracking-tight">Producción Mensual</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5 font-medium">Actas de Asistencia Técnica · {{ date('Y') }}</p>
                </div>
                <span class="text-[9px] font-black text-cyan-600 bg-cyan-50 border border-cyan-100/80 px-2.5 py-1 rounded tracking-widest uppercase">AT</span>
            </div>
            @php $maxTotal = $actasPorMes->max('total') ?: 1; @endphp
            <div class="divide-y divide-slate-50">
                @forelse($actasPorMes as $registro)
                    <div class="px-6 py-3 flex items-center gap-4 group hover:bg-slate-50/60 transition-colors">
                        <span class="text-[11px] font-semibold text-slate-400 w-24 shrink-0 uppercase tracking-wider">{{ $registro['nombre_mes'] }}</span>
                        <div class="flex-1 bg-slate-100 rounded-full h-1 overflow-hidden">
                            <div class="h-full bg-cyan-500 rounded-full transition-all duration-700"
                                 style="width: {{ ($registro['total'] / $maxTotal) * 100 }}%"></div>
                        </div>
                        <span class="text-sm font-black text-slate-700 font-mono tabular-nums w-8 text-right">{{ $registro['total'] }}</span>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center">
                        <p class="text-xs text-slate-400 font-medium">Sin registros disponibles para este año.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RANKING IPRESS --}}
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-700 tracking-tight">Establecimientos Activos</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5 font-medium">Ranking por volumen de actas</p>
                </div>
                <span class="text-[9px] font-black text-amber-600 bg-amber-50 border border-amber-100/80 px-2.5 py-1 rounded tracking-widest uppercase">Top 5</span>
            </div>
            @php $maxCount = $topEstablecimientos->max('actas_count') ?: 1; @endphp
            <div class="divide-y divide-slate-50">
                @forelse($topEstablecimientos as $est)
                    <div class="px-6 py-3.5 flex items-center gap-4 group hover:bg-slate-50/60 transition-colors">
                        <span class="text-[11px] font-black text-slate-200 w-5 shrink-0 font-mono tabular-nums">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-semibold text-slate-700 truncate uppercase tracking-tight">{{ $est->nombre }}</p>
                            <div class="mt-1.5 bg-slate-100 rounded-full h-0.5 overflow-hidden">
                                <div class="h-full bg-amber-400 rounded-full transition-all duration-700"
                                     style="width: {{ ($est->actas_count / $maxCount) * 100 }}%"></div>
                            </div>
                        </div>
                        <span class="text-sm font-black text-slate-600 font-mono tabular-nums shrink-0">{{ $est->actas_count }}</span>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center">
                        <p class="text-xs text-slate-400 font-medium">Sin actividad registrada.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- PIE INFORMATIVO --}}
    <div class="flex items-center justify-between text-[10px] text-slate-300 font-medium tracking-wider uppercase border-t border-slate-100 pt-4">
        <span>Autopsia TI</span>
        <span id="hora-actual"></span>
    </div>

</div>
@endsection

@push('scripts')
<script>
    (function () {
        const dias   = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
        const meses  = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

        function actualizar() {
            const now  = new Date();
            const fecha = `${dias[now.getDay()]}, ${now.getDate()} de ${meses[now.getMonth()]} de ${now.getFullYear()}`;
            const hora  = now.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

            const elFecha1 = document.getElementById('fecha-actual');
            const elFecha2 = document.getElementById('fecha-banner');
            const elHora   = document.getElementById('hora-actual');

            if (elFecha1) elFecha1.textContent = fecha;
            if (elFecha2) elFecha2.textContent = fecha;
            if (elHora)   elHora.textContent   = hora;
        }

        actualizar();
        setInterval(actualizar, 1000);
    })();
</script>
@endpush