<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia - {{ $reunion->titulo_reunion }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .input-focus:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1); }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100">
        {{-- Header --}}
        <div class="bg-indigo-600 p-8 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
            <div class="relative z-10">
                <div class="bg-white/20 w-12 h-12 rounded-2xl flex items-center justify-center mb-4 backdrop-blur-md">
                    <i data-lucide="user-check" class="w-6 h-6"></i>
                </div>
                <h1 class="text-2xl font-bold leading-tight">Registro de Asistencia</h1>
                <p class="text-indigo-100 text-sm mt-1 opacity-90">{{ $reunion->titulo_reunion }}</p>
            </div>
        </div>

        {{-- Form --}}
        <div class="p-8">
            <form action="{{ route('asistencia.store', $reunion->id) }}" method="POST" class="space-y-5">
                @csrf
                
                @if(session('info'))
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded-2xl text-sm font-medium flex gap-3 items-center">
                        <i data-lucide="info" class="w-5 h-5 flex-shrink-0"></i>
                        {{ session('info') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-2xl text-sm font-medium flex gap-3 items-center">
                        <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Tipo Doc.</label>
                        <select name="tipo_documento" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none input-focus transition-all">
                            <option value="DNI">DNI</option>
                            <option value="CE">C.E.</option>
                            <option value="PASAPORTE">PASAPORTE</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">N° Documento</label>
                        <input type="text" name="dni" required placeholder="00000000" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none input-focus transition-all">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Nombres</label>
                        <input type="text" name="nombres" required placeholder="Escriba sus nombres" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none input-focus transition-all uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Apellidos</label>
                        <input type="text" name="apellidos" required placeholder="Escriba sus apellidos" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none input-focus transition-all uppercase">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Cargo</label>
                    <input type="text" name="cargo" required placeholder="Ej: Especialista, Director, etc." class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none input-focus transition-all uppercase">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Institución / Dependencia</label>
                    <input type="text" name="institucion" required placeholder="Nombre de su institución" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none input-focus transition-all uppercase">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 ml-1">Celular (Opcional)</label>
                    <input type="tel" name="celular" placeholder="999999999" class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:outline-none input-focus transition-all">
                </div>

                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-200 transition-all flex items-center justify-center gap-2 transform active:scale-95">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    REGISTRAR ASISTENCIA
                </button>
            </form>
        </div>

        <div class="bg-slate-50 p-6 border-t border-slate-100 text-center">
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest flex items-center justify-center gap-2">
                <i data-lucide="shield-check" class="w-3 h-3"></i>
                Sistema de Actas - ICATEC &copy; 2026
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();

            const dniInput = document.querySelector('input[name="dni"]');
            const tipoInput = document.querySelector('select[name="tipo_documento"]');
            const nombresInput = document.querySelector('input[name="nombres"]');
            const apellidosInput = document.querySelector('input[name="apellidos"]');
            const cargoInput = document.querySelector('input[name="cargo"]');
            const institucionInput = document.querySelector('input[name="institucion"]');
            const celularInput = document.querySelector('input[name="celular"]');
            const form = document.querySelector('form');

            // Formatear a mayúsculas mientras escriben
            const upperInputs = [nombresInput, apellidosInput, cargoInput, institucionInput];
            upperInputs.forEach(input => {
                input.addEventListener('input', (e) => {
                    e.target.value = e.target.value.toUpperCase();
                });
            });

            // Búsqueda automática al completar 8 dígitos si es DNI
            dniInput.addEventListener('input', (e) => {
                const doc = e.target.value.trim();
                if (doc.length === 8 && tipoInput.value === 'DNI') {
                    buscarProfesional(doc);
                }
            });

            function buscarProfesional(doc) {
                Swal.fire({
                    title: 'Buscando...',
                    html: 'Consultando datos oficiales...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                    customClass: { popup: 'rounded-3xl' }
                });

                // Primero buscar localmente
                fetch(`/usuario/monitoreo/profesional/buscar/${doc}?local_only=1`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.exists) {
                            Swal.close();
                            nombresInput.value = data.nombres.toUpperCase();
                            apellidosInput.value = (data.apellido_paterno + ' ' + data.apellido_materno).trim().toUpperCase();
                            if (data.cargo) cargoInput.value = data.cargo.toUpperCase();
                            if (data.telefono) celularInput.value = data.telefono;
                            
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'success',
                                title: 'Datos encontrados'
                            });
                        } else {
                            // Si no existe localmente, buscar en RENIEC
                            return fetch(`/usuario/monitoreo/profesional/buscar/${doc}`);
                        }
                    })
                    .then(res => res ? res.json() : null)
                    .then(dataExt => {
                        if (dataExt && dataExt.exists_external) {
                            Swal.close();
                            nombresInput.value = dataExt.nombres.toUpperCase();
                            apellidosInput.value = (dataExt.apellido_paterno + ' ' + dataExt.apellido_materno).trim().toUpperCase();
                            
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'info',
                                title: 'Datos obtenidos de RENIEC'
                            });
                        } else if (dataExt) {
                            Swal.close();
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.close();
                    });
            }

            // Validación de formulario
            form.addEventListener('submit', (e) => {
                const celular = celularInput.value.trim();
                if (celular === '999999999') {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Celular Inválido',
                        text: 'Por favor ingrese un número de celular real.',
                        icon: 'warning',
                        confirmButtonColor: '#6366f1',
                        customClass: { popup: 'rounded-3xl', confirmButton: 'rounded-2xl px-6' }
                    });
                }
            });
        });
    </script>
</body>
</html>
