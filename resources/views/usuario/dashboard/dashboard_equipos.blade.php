@extends('layouts.usuario')

@section('title', 'Dashboard - Equipos de Cómputo')

@section('content')
    <div class="max-w-7xl mx-auto space-y-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800">Dashboard de Equipos de Cómputo</h1>
                <p class="text-slate-600 mt-1">Estadísticas y análisis de equipos de cómputo</p>
            </div>
        </div>

        @include('usuario.dashboard.equipos_subsection')
    </div>

    @include('usuario.dashboard.equipos_scripts_new')
@endsection