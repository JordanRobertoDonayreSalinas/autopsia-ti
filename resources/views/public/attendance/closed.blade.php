<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia Cerrada - {{ $reunion->titulo_reunion }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full text-center space-y-6">
        <div class="bg-white p-10 rounded-3xl shadow-2xl border border-slate-100 flex flex-col items-center">
            <div class="w-20 h-20 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mb-6 animate-pulse">
                <i data-lucide="clock" class="w-10 h-10"></i>
            </div>
            
            <h1 class="text-2xl font-bold text-slate-800">Formulario Cerrado</h1>
            <p class="text-slate-500 mt-3 text-sm leading-relaxed">
                El formulario de asistencia para la reunión:<br>
                <span class="font-bold text-slate-700">"{{ $reunion->titulo_reunion }}"</span><br>
                ya no se encuentra disponible o ha expirado.
            </p>

            <div class="mt-8 pt-8 border-t border-slate-50 w-full">
                <p class="text-xs text-slate-400 font-medium italic">
                    Si cree que esto es un error, por favor contacte con el responsable de la reunión para que lo reactive.
                </p>
            </div>
        </div>

        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest flex items-center justify-center gap-2">
            <i data-lucide="shield-check" class="w-3 h-3"></i>
            Sistema de Actas - ICATEC &copy; 2026
        </p>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
