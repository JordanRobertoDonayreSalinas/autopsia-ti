@extends('layouts.usuario')

@section('title', 'Mi Perfil')

{{-- HEADER DEL PERFIL --}}
@section('header-content')
    <div class="flex flex-col">
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Configuración de Perfil</h1>
        <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
            <span>Plataforma</span>
            <span class="text-slate-300">•</span>
            <span>Mi Perfil</span>
        </div>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto pb-12">

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

    {{-- Errores de Validación en Español --}}
    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 shadow-sm animate-fade-in">
            <div class="flex items-center gap-2 mb-2">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                <span class="font-bold text-sm">Se encontraron los siguientes errores:</span>
            </div>
            <ul class="text-xs list-disc list-inside space-y-1 ml-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('usuario.perfil.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
            
            {{-- Encabezado Visual del Perfil --}}
            <div class="p-8 border-b border-slate-50 bg-gradient-to-r from-slate-50/50 to-white">
                <div class="flex flex-col md:flex-row items-center gap-6 text-center md:text-left">
                    <div class="relative group">
                        <div class="h-24 w-24 rounded-3xl bg-emerald-600 text-white flex items-center justify-center text-4xl font-black shadow-2xl shadow-emerald-200 uppercase transform transition-transform group-hover:scale-105">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div class="absolute -bottom-2 -right-2 bg-white p-1.5 rounded-xl shadow-lg border border-slate-100">
                            <i data-lucide="shield-check" class="w-5 h-5 text-emerald-600"></i>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl font-extrabold text-slate-800">{{ $user->name }} {{ $user->apellido_paterno }}</h2>
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mt-1">
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold tracking-wider uppercase border border-slate-200">DNI: {{ $user->username }}</span>
                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold tracking-wider uppercase border border-emerald-200">Rol: Usuario</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8 md:p-10 space-y-8">
                
                {{-- Bloque: Información Personal --}}
                <section>
                    <div class="flex items-center gap-2 mb-6">
                        <div class="h-8 w-1 bg-emerald-500 rounded-full"></div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Información Personal</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Nombres</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Apellido Paterno</label>
                            <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno', $user->apellido_paterno) }}" required
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Apellido Materno</label>
                            <input type="text" name="apellido_materno" value="{{ old('apellido_materno', $user->apellido_materno) }}" required
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Correo Electrónico</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all outline-none text-sm font-medium text-slate-700 bg-slate-50/30 hover:bg-white">
                        </div>
                    </div>
                </section>

                <div class="h-px bg-slate-100 w-full"></div>

                {{-- Bloque: Seguridad --}}
                <section>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="h-8 w-1 bg-amber-500 rounded-full"></div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Seguridad</h3>
                    </div>
                    <p class="text-[11px] text-slate-500 mb-6 ml-3 font-medium text-amber-600 italic">Deje estos campos vacíos para mantener su contraseña actual.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Nueva Contraseña</label>
                            <div class="relative">
                                <input type="password" name="password" placeholder="Mínimo 8 caracteres"
                                       class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none text-sm font-medium text-slate-700">
                                <i data-lucide="lock" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 ml-1">Confirmar Contraseña</label>
                            <div class="relative">
                                <input type="password" name="password_confirmation" placeholder="Repita la nueva contraseña"
                                       class="w-full px-5 py-3.5 rounded-2xl border border-slate-200 focus:ring-4 focus:ring-amber-500/10 focus:border-amber-500 transition-all outline-none text-sm font-medium text-slate-700">
                                <i data-lucide="check-square" class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Pie de Formulario --}}
            <div class="p-8 bg-slate-50/80 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-xs text-slate-400 italic flex items-center gap-2">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    Última actualización: {{ $user->updated_at->diffForHumans() }}
                </p>
                <button type="submit" 
                        class="w-full md:w-auto flex items-center justify-center gap-3 px-10 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-bold text-sm transition-all shadow-xl shadow-emerald-200 hover:shadow-emerald-300 hover:-translate-y-0.5 active:scale-95">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Cambios
                </button>
            </div>
        </div>
    </form>
</div>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in { animation: fade-in 0.4s ease-out forwards; }
</style>
@endsection