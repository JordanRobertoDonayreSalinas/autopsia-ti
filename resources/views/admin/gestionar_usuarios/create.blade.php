@extends('layouts.usuario')

@section('title', 'Crear Nuevo Usuario')

@section('header-content')
    <h1 class="text-xl font-bold text-slate-800 tracking-tight">Nuevo Usuario</h1>
    <div class="flex items-center gap-2 text-xs text-slate-500 mt-0.5">
        <a href="{{ route('admin.users.index') }}" class="hover:text-indigo-600 transition-colors">Gestionar usuarios</a>
        <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        <span>Crear usuario</span>
    </div>
@endsection

@section('content')
    <div class="max-w-5xl mx-auto">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- COLUMNA DATOS DEL PERFIL --}}
                <div class="lg:col-span-8 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                        <h3 class="text-base font-bold text-slate-800 mb-1 flex items-center gap-2">
                            <i data-lucide="user-circle" class="w-5 h-5 text-indigo-500"></i>
                            Datos del Perfil
                        </h3>
                        <p class="text-xs text-slate-500 mb-6 pl-7">Información básica del nuevo integrante.</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Apellido Paterno --}}
                            <div>
                                <label for="apellido_paterno" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Apellido Paterno</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="apellido_paterno" id="apellido_paterno" value="{{ old('apellido_paterno') }}" class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm @error('apellido_paterno') border-red-300 @enderror" placeholder="Ej: Pérez">
                                </div>
                                @error('apellido_paterno') <p class="mt-1 text-[10px] text-red-600 font-bold uppercase tracking-tight">{{ $message }}</p> @enderror
                            </div>

                            {{-- Apellido Materno --}}
                            <div>
                                <label for="apellido_materno" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Apellido Materno</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="user" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="apellido_materno" id="apellido_materno" value="{{ old('apellido_materno') }}" class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm @error('apellido_materno') border-red-300 @enderror" placeholder="Ej: García">
                                </div>
                                @error('apellido_materno') <p class="mt-1 text-[10px] text-red-600 font-bold uppercase tracking-tight">{{ $message }}</p> @enderror
                            </div>

                            {{-- Nombres --}}
                            <div class="col-span-2">
                                <label for="name" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Nombres</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="info" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm @error('name') border-red-300 @enderror" placeholder="Ej: Juan Alberto">
                                </div>
                                @error('name') <p class="mt-1 text-[10px] text-red-600 font-bold uppercase tracking-tight">{{ $message }}</p> @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-span-2">
                                <label for="email" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Correo Electrónico</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="mail" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}" class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm @error('email') border-red-300 @enderror" placeholder="ejemplo@empresa.com">
                                </div>
                                @error('email') <p class="mt-1 text-[10px] text-red-600 font-bold uppercase tracking-tight">{{ $message }}</p> @enderror
                            </div>

                            {{-- BLOQUE VERIFICACIÓN DE DOCUMENTO --}}
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Documento de Identidad</label>

                                <div class="flex gap-2">
                                    {{-- Selector tipo documento --}}
                                    <div class="relative shrink-0">
                                        <select id="tipo_doc" name="tipo_doc"
                                            class="h-full pl-3 pr-8 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer">
                                            <option value="DNI" {{ old('tipo_doc', 'DNI') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                            <option value="CE"  {{ old('tipo_doc') == 'CE'  ? 'selected' : '' }}>C.E.</option>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-400">
                                            <i data-lucide="chevron-down" class="w-3 h-3"></i>
                                        </div>
                                    </div>

                                    {{-- Número de documento --}}
                                    <div class="relative flex-1 group">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i data-lucide="fingerprint" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                        </div>
                                        <input type="text" name="username" id="username" value="{{ old('username') }}"
                                            class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm font-mono @error('username') border-red-300 @enderror"
                                            placeholder="Número de documento">
                                    </div>

                                    {{-- Botón verificar --}}
                                    <button type="button" id="btn-verificar"
                                        class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-all shadow-sm shadow-indigo-500/20 active:scale-95">
                                        <i data-lucide="search" class="w-4 h-4"></i>
                                        <span>Verificar</span>
                                    </button>
                                </div>

                                {{-- Badge de fuente --}}
                                <div id="doc-badge" class="mt-2 hidden">
                                    <span id="doc-badge-text" class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full"></span>
                                </div>

                                @error('username') <p class="mt-1.5 text-[10px] text-red-600 font-bold uppercase tracking-tight">{{ $message }}</p> @enderror
                            </div>

                            {{-- Rol --}}
                            <div>
                                <label for="role" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Rol de Acceso</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="shield-check" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <select name="role" id="role" class="block w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm appearance-none cursor-pointer">
                                        <option value="" disabled selected>Seleccionar...</option>
                                        <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Usuario</option>
                                        <option value="operador" {{ old('role') == 'operador' ? 'selected' : '' }}>Operador (Monitoreo)</option>
                                        <option value="visor_cronograma" {{ old('role') == 'visor_cronograma' ? 'selected' : '' }}>Visor Cronograma (Solo lectura)</option>
                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                </div>
                                @error('role') <p class="mt-1 text-[10px] text-red-600 font-bold uppercase tracking-tight">{{ $message }}</p> @enderror
                            </div>

                            {{-- Estado del Usuario (NUEVO REINTEGRADO) --}}
                            <div class="col-span-2">
                                <label for="status" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Estado Inicial</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i data-lucide="activity" class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
                                    </div>
                                    <select name="status" id="status" class="block w-full pl-10 pr-10 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 focus:outline-none focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all sm:text-sm appearance-none cursor-pointer">
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>ACTIVO</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>INACTIVO</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500"><i data-lucide="chevron-down" class="w-4 h-4"></i></div>
                                </div>
                                @error('status') <p class="mt-1 text-[10px] text-red-600 font-bold uppercase tracking-tight">{{ $message }}</p> @enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- COLUMNA CREDENCIALES --}}
                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 h-full flex flex-col">
                        <h3 class="text-base font-bold text-slate-800 mb-1 flex items-center gap-2">
                            <i data-lucide="key" class="w-5 h-5 text-emerald-500"></i>
                            Credenciales
                        </h3>
                        <p class="text-xs text-slate-500 mb-6 pl-7">Establece la contraseña de acceso.</p>

                        <div class="space-y-5 flex-1">
                            <div>
                                <label for="password" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Contraseña</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i data-lucide="lock" class="h-4 w-4 text-slate-400 group-focus-within:text-emerald-500 transition-colors"></i></div>
                                    <input type="password" name="password" id="password" class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all sm:text-sm @error('password') border-red-300 @enderror" placeholder="••••••••">
                                </div>
                                @error('password') <p class="mt-1 text-[10px] text-red-600 font-bold uppercase tracking-tight">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-2">Confirmar</label>
                                <div class="relative group">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><i data-lucide="check-circle" class="h-4 w-4 text-slate-400 group-focus-within:text-emerald-500 transition-colors"></i></div>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-xl bg-slate-50 text-slate-900 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all sm:text-sm" placeholder="••••••••">
                                </div>
                            </div>

                            <div class="p-4 bg-blue-50/50 rounded-xl border border-blue-100/50 mt-auto">
                                <div class="flex gap-3">
                                    <i data-lucide="shield-alert" class="w-5 h-5 text-blue-500 shrink-0"></i>
                                    <p class="text-[11px] text-blue-700 leading-relaxed font-medium">Por seguridad, la contraseña debe tener al menos 6 caracteres.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ACCIONES --}}
            <div class="mt-8 flex items-center justify-end gap-4 border-t border-slate-200 pt-8">
                <a href="{{ route('admin.users.index') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-all">Cancelar</a>
                <button type="submit" class="group relative inline-flex items-center gap-2 px-8 py-3 rounded-xl text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all transform active:scale-95">
                    <i data-lucide="save" class="w-4 h-4 transition-transform group-hover:-translate-y-0.5"></i>
                    Guardar Usuario
                </button>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const btn       = document.getElementById('btn-verificar');
    const tipoDocEl = document.getElementById('tipo_doc');
    const docEl     = document.getElementById('username');
    const badge     = document.getElementById('doc-badge');
    const badgeText = document.getElementById('doc-badge-text');
    const baseUrl   = '{{ route("admin.buscarDni") }}';
    const headers   = { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' };

    const fields = {
        apellido_paterno: document.getElementById('apellido_paterno'),
        apellido_materno: document.getElementById('apellido_materno'),
        name:             document.getElementById('name'),
        email:            document.getElementById('email'),
    };

    // ── Utilidades ──────────────────────────────────────────────

    function setBadge(source) {
        badge.classList.remove('hidden');
        if (source === 'local') {
            badgeText.className = 'inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200';
            badgeText.textContent = '✓ Encontrado en base de datos local';
        } else {
            badgeText.className = 'inline-flex items-center gap-1.5 text-[10px] font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200';
            badgeText.textContent = '✓ Encontrado vía RENIEC (API)';
        }
    }

    function fillFields(data) {
        if (fields.apellido_paterno) fields.apellido_paterno.value = data.apellido_paterno || '';
        if (fields.apellido_materno) fields.apellido_materno.value = data.apellido_materno || '';
        if (fields.name)             fields.name.value             = data.nombres || '';
        if (fields.email && data.email) fields.email.value         = data.email;
        if (data.tipo_doc && tipoDocEl)  tipoDocEl.value           = data.tipo_doc;
    }

    function showToast(icon, title) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
        });
        Toast.fire({ icon, title });
    }

    function btnLoading() {
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Buscando...</span>`;
        if (window.refreshLucide) window.refreshLucide();
    }

    function btnReset() {
        btn.disabled = false;
        btn.innerHTML = `<i data-lucide="search" class="w-4 h-4"></i><span>Verificar</span>`;
        if (window.refreshLucide) window.refreshLucide();
    }

    function checkExistingUser(existingUser, tipoDoc, doc) {
        if (!existingUser) return;
        const roles   = { admin: 'Administrador', operador: 'Operador', user: 'Usuario' };
        const estados = { active: '✅ Activo', inactive: '🔴 Bloqueado' };
        Swal.fire({
            icon: 'warning',
            title: 'Este documento ya tiene acceso',
            html: `
                <div style="text-align:left;font-size:13px;line-height:1.9;padding:4px 0">
                    <p>⚠️ El documento <b>${tipoDoc} ${doc}</b> ya está registrado como usuario del sistema.</p>
                    <div style="background:#fef9c3;border:1px solid #fde047;border-radius:8px;padding:10px 14px;margin-top:10px">
                        <p><b>Usuario:</b> ${existingUser.nombre}</p>
                        <p><b>Rol:</b> ${roles[existingUser.role] ?? existingUser.role}</p>
                        <p><b>Estado:</b> ${estados[existingUser.status] ?? existingUser.status}</p>
                    </div>
                    <p style="color:#64748b;font-size:11px;margin-top:10px">Si deseas modificarlo, usa la opción "editar" desde el listado.</p>
                </div>
            `,
            confirmButtonColor: '#d97706',
            confirmButtonText: 'Entendido',
        });
    }

    function promptNoEncontrado(tipoDoc, doc, existingUser) {
        if (existingUser) {
            // Tiene cuenta pero no datos en la BD local
            const roles = { admin: 'Administrador', operador: 'Operador', user: 'Usuario' };
            Swal.fire({
                icon: 'warning',
                title: 'Documento ya registrado',
                html: `El documento <b>${tipoDoc} ${doc}</b> ya tiene acceso como <b>${roles[existingUser.role] ?? existingUser.role}</b>.<br><br>
                       <span style="font-size:12px;color:#64748b">Usa la opción "editar" desde el listado.</span>`,
                confirmButtonColor: '#d97706',
                confirmButtonText: 'Entendido',
            });
        } else {
            Swal.fire({
                title: 'Documento no encontrado',
                text: 'El documento no figura en el padrón local ni en RENIEC. Puedes ingresar los datos manualmente.',
                icon: 'question',
                confirmButtonText: 'Ingresar manualmente',
                confirmButtonColor: '#4f46e5',
                customClass: { popup: 'rounded-[2rem] border-2 border-slate-100 shadow-2xl' },
            });
        }
    }

    // ── Flujo Principal ─────────────────────────────────────────

    btn.addEventListener('click', function () {
        const tipoDoc = tipoDocEl.value;
        const doc     = docEl.value.trim();

        if (doc.length < 6) {
            Swal.fire({
                title: 'N° Documento inválido',
                text: 'Ingresa un número de documento válido (mínimo 6 caracteres).',
                icon: 'warning',
                confirmButtonColor: '#4f46e5',
            });
            return;
        }

        badge.classList.add('hidden');
        btnLoading();

        // ── Paso 1: Solo local ─────────────────────────────────
        fetch(`${baseUrl}?tipo_doc=${encodeURIComponent(tipoDoc)}&dni=${encodeURIComponent(doc)}&local_only=1`, { headers })
            .then(r => r.json())
            .then(data => {
                if (data.exists) {
                    // ✔ Encontrado en BD local
                    fillFields(data);
                    setBadge('local');
                    showToast('success', 'Datos cargados desde la base de datos local');
                    checkExistingUser(data.existing_user, tipoDoc, doc);
                    btnReset();
                    return;
                }

                // ── Paso 2: API RENIEC (solo DNI 8 dígitos) ───
                if (tipoDoc === 'DNI' && doc.length === 8) {
                    // Animación mientras espera la API (igual al componente)
                    Swal.fire({
                        html: `
                            <div class="p-4 flex flex-col items-center">
                                <div class="relative w-24 h-24 flex items-center justify-center mb-6">
                                    <div class="absolute inset-0 border-[6px] border-indigo-50 rounded-full animate-ping opacity-75"></div>
                                    <div class="absolute inset-3 border-4 border-indigo-100 rounded-full animate-pulse"></div>
                                    <div class="h-14 w-14 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-full flex items-center justify-center shadow-xl shadow-indigo-500/50 z-10 relative">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                            class="text-white animate-bounce">
                                            <path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-2xl font-black text-indigo-900 uppercase tracking-tight mb-2">Conectando RENIEC</h3>
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] text-center mb-6 leading-relaxed">
                                    Extrayendo nombres oficiales<br>de la plataforma nacional.
                                </p>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden flex gap-1">
                                    <div class="bg-indigo-300 h-full w-1/3 rounded-full animate-pulse"></div>
                                    <div class="bg-indigo-500 h-full w-1/3 rounded-full animate-pulse" style="animation-delay:.075s"></div>
                                    <div class="bg-indigo-700 h-full w-1/3 rounded-full animate-pulse" style="animation-delay:.15s"></div>
                                </div>
                            </div>
                        `,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        customClass: { popup: 'rounded-[2rem] border-2 border-indigo-50 shadow-2xl p-0 bg-white' },
                    });

                    fetch(`${baseUrl}?tipo_doc=${encodeURIComponent(tipoDoc)}&dni=${encodeURIComponent(doc)}`, { headers })
                        .then(r => r.json())
                        .then(dataExt => {
                            Swal.close();

                            if (dataExt.quota_exceeded) {
                                Swal.fire({
                                    title: 'Límite Mensual Excedido',
                                    text: 'Se agotó el cupo de consultas a RENIEC por este mes. Ingresa los datos manualmente.',
                                    icon: 'warning',
                                    confirmButtonText: 'Entendido',
                                    confirmButtonColor: '#4f46e5',
                                    customClass: { popup: 'rounded-[2rem]' },
                                }).then(() => {
                                    checkExistingUser(dataExt.existing_user, tipoDoc, doc);
                                });
                                return;
                            }

                            if (dataExt.exists_external) {
                                fillFields(dataExt);
                                setBadge('api');
                                let tokenMsg = dataExt.remaining_tokens !== undefined
                                    ? ` (Tokens restantes: ${dataExt.remaining_tokens})`
                                    : '';
                                showToast('info', 'Nombres encontrados en RENIEC.' + tokenMsg + ' Complete los demás datos.');
                                checkExistingUser(dataExt.existing_user, tipoDoc, doc);
                            } else {
                                promptNoEncontrado(tipoDoc, doc, dataExt.existing_user);
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error', 'No se pudo conectar con el servidor externo.', 'error');
                        })
                        .finally(btnReset);

                } else {
                    // Tipo != DNI o longitud != 8 → sin fallback API
                    promptNoEncontrado(tipoDoc, doc, data.existing_user);
                    btnReset();
                }
            })
            .catch(() => {
                Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
                btnReset();
            });
    });

    // Enter en el campo dispara el botón
    docEl.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); btn.click(); }
    });
})();
</script>
@endpush