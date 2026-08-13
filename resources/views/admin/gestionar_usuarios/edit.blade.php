@extends('layouts.usuario')

@section('title', 'Editar Usuario - ' . $user->apellido_paterno . ' ' . $user->name)

@section('header-content')
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Editar Usuario</h1>
    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
        <a href="{{ route('admin.users.index') }}" class="hover:text-indigo-600 transition-colors">Usuarios</a>
        <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <span>Editando: <strong class="text-slate-700">{{ $user->apellido_paterno }} {{ $user->name }}</strong></span>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto">

        {{-- Bloque de errores --}}
        @if ($errors->any())
            <div class="mb-8 p-4 rounded-xl bg-red-50 border border-red-200 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-red-100 rounded-full text-red-600">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>
                    <h3 class="font-bold text-red-800">Verifique los siguientes campos:</h3>
                </div>
                <ul class="list-disc list-inside text-sm text-red-700 ml-12">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- COLUMNA IZQUIERDA: DATOS DEL PERFIL --}}
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-8">
                        
                        <div class="flex items-center gap-3 mb-6 border-b border-slate-50 pb-4">
                            <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                                <i data-lucide="user-cog" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Datos del Perfil</h3>
                                <p class="text-xs text-slate-500">Información personal y de contacto del integrante.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Apellido Paterno --}}
                            <div>
                                <label for="apellido_paterno" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Apellido Paterno</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno', $user->apellido_paterno) }}" required
                                        class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm">
                                </div>
                            </div>

                            {{-- Apellido Materno --}}
                            <div>
                                <label for="apellido_materno" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Apellido Materno</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno', $user->apellido_materno) }}" required
                                        class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm">
                                </div>
                            </div>

                            {{-- Nombres --}}
                            <div class="col-span-2">
                                <label for="name" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombres</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="info" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                        class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm">
                                </div>
                            </div>

                            {{-- Email --}}
                            <div class="col-span-2">
                                <label for="email" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Correo Electrónico</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="mail" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                                        class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm">
                                </div>
                            </div>

                            {{-- DNI --}}
                            <div>
                                <label for="username" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Usuario (DNI)</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="fingerprint" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <input type="number" name="username" id="username" value="{{ old('username', $user->username) }}" required
                                        class="block w-full pl-10 pr-3 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm font-mono">
                                </div>
                            </div>

                            {{-- Rol --}}
                            <div>
                                <label for="role" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Rol de Acceso</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="shield-check" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <select name="role" id="role" class="block w-full pl-10 pr-10 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm appearance-none cursor-pointer">
                                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Usuario</option>
                                        <option value="operador" {{ old('role', $user->role) == 'operador' ? 'selected' : '' }}>Operador (Monitoreo)</option>
                                        <option value="visor_cronograma" {{ old('role', $user->role) == 'visor_cronograma' ? 'selected' : '' }}>Visor Cronograma (Solo lectura)</option>
                                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador (Total)</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                </div>
                            </div>

                            {{-- SWITCH ESTADO DE LA CUENTA --}}
                            <div class="col-span-2 mt-2 pt-6 border-t border-slate-100">
                                <div class="flex items-center justify-between" x-data="{ active: {{ old('status', $user->status) === 'active' ? 'true' : 'false' }} }">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-700">Estado de la Cuenta</label>
                                        <p class="text-xs text-slate-500 mt-1">Si se bloquea, el usuario no podrá acceder al sistema.</p>
                                    </div>
                                    <label class="inline-flex items-center cursor-pointer group">
                                        <input type="hidden" name="status" value="inactive"> 
                                        <input type="checkbox" name="status" value="active" class="sr-only peer" 
                                               x-model="active" {{ old('status', $user->status) == 'active' ? 'checked' : '' }}>
                                        
                                        <div class="relative w-14 h-7 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600"></div>
                                        <span class="ms-3 text-sm font-bold text-slate-600 group-hover:text-slate-900 transition-colors uppercase tracking-tighter" 
                                              x-text="active ? 'Activo' : 'Inactivo'">
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- COLUMNA DERECHA: SEGURIDAD --}}
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-8 h-full flex flex-col">
                        
                        <div class="flex items-center gap-3 mb-6 border-b border-slate-50 pb-4">
                            <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
                                <i data-lucide="key" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800">Seguridad</h3>
                                <p class="text-xs text-slate-500">Credenciales de acceso.</p>
                            </div>
                        </div>

                        <div class="p-4 bg-blue-50/50 rounded-xl border border-blue-100 mb-6 flex gap-3 items-start">
                            <i data-lucide="info" class="w-5 h-5 text-blue-500 shrink-0 mt-0.5"></i>
                            <p class="text-xs text-blue-700 leading-tight font-medium">Deje la contraseña en blanco si no desea cambiarla.</p>
                        </div>

                        <div class="space-y-4 flex-1">
                            {{-- Nueva contraseña --}}
                            <div>
                                <label for="password" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nueva Contraseña</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="lock" class="h-4 w-4 text-slate-400 group-focus-within:text-emerald-500 transition-colors"></i>
                                    </div>
                                    <input type="password" name="password" id="password" autocomplete="new-password"
                                        class="block w-full pl-10 pr-10 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-sm"
                                        placeholder="Dejar en blanco para no cambiar">
                                    <button type="button" id="toggle-pass"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-300 hover:text-slate-500 transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4" id="eye-icon"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Confirmar --}}
                            <div>
                                <label for="password_confirmation" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Confirmar</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="check-circle-2" class="h-4 w-4 text-slate-400 group-focus-within:text-emerald-500 transition-colors"></i>
                                    </div>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="block w-full pl-10 pr-10 py-3 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-sm"
                                        placeholder="••••••••">
                                    <button type="button" id="toggle-confirm"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-300 hover:text-slate-500 transition-colors">
                                        <i data-lucide="eye" class="w-4 h-4" id="eye-icon-confirm"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Acciones rápidas --}}
                            <div class="pt-2 space-y-2">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Accesos rápidos</p>
                                <div class="flex gap-2">
                                    <button type="button" id="btn-generar-pass"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-[11px] font-bold text-indigo-600 bg-indigo-50 border border-indigo-100 rounded-xl hover:bg-indigo-100 transition-all">
                                        <i data-lucide="wand-2" class="w-3.5 h-3.5"></i>
                                        Generar segura
                                    </button>
                                    <button type="button" id="btn-usar-dni"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-100 rounded-xl hover:bg-amber-100 transition-all">
                                        <i data-lucide="fingerprint" class="w-3.5 h-3.5"></i>
                                        Usar DNI
                                    </button>
                                </div>
                            </div>

                            {{-- Indicador de fortaleza --}}
                            <div id="strength-wrap" class="hidden">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fortaleza</span>
                                    <span id="strength-label" class="text-[10px] font-bold"></span>
                                </div>
                                <div class="h-1 bg-slate-100 rounded-full overflow-hidden">
                                    <div id="strength-bar" class="h-full rounded-full transition-all duration-300" style="width:0"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- BOTONES DE ACCIÓN --}}
            <div class="mt-8 flex items-center justify-end gap-4 p-6 bg-slate-50 rounded-2xl border border-slate-200">
                <a href="{{ route('admin.users.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-white transition-all">
                    Cancelar
                </a>
                <button type="submit" class="group relative inline-flex items-center gap-2 px-8 py-3 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95">
                    <i data-lucide="save" class="w-5 h-5 transition-transform group-hover:scale-110"></i>
                    Guardar Cambios
                </button>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const passEl    = document.getElementById('password');
    const confirmEl = document.getElementById('password_confirmation');
    const strengthWrap = document.getElementById('strength-wrap');
    const strengthBar  = document.getElementById('strength-bar');
    const strengthLbl  = document.getElementById('strength-label');
    const dniActual    = '{{ $user->username }}';

    // Toggle visibilidad
    function makeToggle(btnId, inputEl, iconId) {
        const btn  = document.getElementById(btnId);
        const icon = document.getElementById(iconId);
        if (!btn) return;
        btn.addEventListener('click', () => {
            const isPass = inputEl.type === 'password';
            inputEl.type = isPass ? 'text' : 'password';
            icon.setAttribute('data-lucide', isPass ? 'eye-off' : 'eye');
            if (window.refreshLucide) window.refreshLucide();
        });
    }
    makeToggle('toggle-pass',    passEl,    'eye-icon');
    makeToggle('toggle-confirm', confirmEl, 'eye-icon-confirm');

    // Indicador de fortaleza
    function evalStrength(pwd) {
        let score = 0;
        if (pwd.length >= 8)  score++;
        if (pwd.length >= 12) score++;
        if (/[A-Z]/.test(pwd)) score++;
        if (/[0-9]/.test(pwd)) score++;
        if (/[^A-Za-z0-9]/.test(pwd)) score++;
        return score;
    }

    passEl.addEventListener('input', function () {
        const val = this.value;
        if (!val) { strengthWrap.classList.add('hidden'); return; }
        strengthWrap.classList.remove('hidden');
        const score = evalStrength(val);
        const levels = [
            { label: 'Muy débil', color: '#ef4444', width: '20%' },
            { label: 'Débil',    color: '#f97316', width: '40%' },
            { label: 'Media',    color: '#eab308', width: '60%' },
            { label: 'Fuerte',   color: '#22c55e', width: '80%' },
            { label: 'Excelente',color: '#10b981', width: '100%' },
        ];
        const lvl = levels[Math.min(score, 4)];
        strengthBar.style.width = lvl.width;
        strengthBar.style.background = lvl.color;
        strengthLbl.textContent = lvl.label;
        strengthLbl.style.color = lvl.color;
    });

    // Generar contraseña segura
    document.getElementById('btn-generar-pass')?.addEventListener('click', function () {
        const chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789@#$!';
        let pwd = '';
        for (let i = 0; i < 12; i++) {
            pwd += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        Swal.fire({
            icon: 'info',
            title: 'Contraseña generada',
            html: `<div style="font-family:monospace;font-size:22px;font-weight:bold;letter-spacing:3px;padding:12px;background:#f1f5f9;border-radius:8px;margin:10px 0">${pwd}</div>
                   <p style="font-size:12px;color:#64748b">Copia y comparte esta contraseña con el usuario de forma segura.</p>`,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Usar esta contraseña',
            showCancelButton: true,
            cancelButtonText: 'Generar otra',
            cancelButtonColor: '#94a3b8',
        }).then(result => {
            if (result.isConfirmed) {
                passEl.value    = pwd;
                confirmEl.value = pwd;
                passEl.dispatchEvent(new Event('input'));
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                document.getElementById('btn-generar-pass').click();
            }
        });
    });

    // Usar DNI como contraseña
    document.getElementById('btn-usar-dni')?.addEventListener('click', function () {
        Swal.fire({
            icon: 'warning',
            title: '¿Usar DNI como contraseña?',
            html: `La contraseña será el DNI <b>${dniActual}</b>.<br><span style="font-size:12px;color:#94a3b8">Recomendado solo para acceso temporal. El usuario debe cambiarla luego.</span>`,
            confirmButtonColor: '#d97706',
            confirmButtonText: 'Sí, usar DNI',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
        }).then(result => {
            if (result.isConfirmed) {
                passEl.value    = dniActual;
                confirmEl.value = dniActual;
                passEl.dispatchEvent(new Event('input'));
            }
        });
    });
})();
</script>
@endpush