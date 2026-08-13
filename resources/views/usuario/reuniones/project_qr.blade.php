<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proyectar QR - {{ $reunion->titulo_reunion }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        #qr-container canvas, #qr-container img {
            image-rendering: -webkit-optimize-contrast;
            image-rendering: crisp-edges;
            display: block;
            margin: 0 auto;
            max-width: 100%;
            height: auto !important;
        }
        .bg-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Ajustar dinámicamente para pantallas cortas (proyectores/laptops) */
        @media (max-height: 800px) {
            body {
                padding: 2rem !important;
            }
            .max-w-xl {
                max-width: 32rem !important;
                padding: 1.75rem 2rem 2rem 2rem !important;
                border-radius: 2rem !important;
            }
            .absolute.-top-10 {
                top: -0.85rem !important;
                padding: 0.4rem 1.5rem !important;
            }
            .absolute.-top-10 span {
                font-size: 9px !important;
            }
            .absolute.-top-10 i {
                width: 1rem !important;
                height: 1rem !important;
            }
            h1 {
                font-size: 1.5rem !important;
            }
            .mb-4 {
                margin-bottom: 0.75rem !important;
            }
            .bg-slate-50 {
                padding: 0.75rem !important;
                margin-bottom: 0.75rem !important;
                border-radius: 1.25rem !important;
            }
            #qr-container {
                padding: 0.75rem !important;
                border-radius: 1rem !important;
            }
            #qr-container canvas, #qr-container img {
                width: 210px !important;
                height: 210px !important;
            }
            .inline-flex {
                margin-bottom: 0.5rem !important;
                padding: 0.4rem 1rem !important;
                font-size: 11px !important;
            }
            .mt-6 {
                margin-top: 1rem !important;
            }
        }
    </style>
</head>
<body class="bg-pattern min-h-screen flex flex-col items-center justify-center p-6 md:p-10 overflow-y-auto">
    
    <div class="max-w-xl w-full bg-white rounded-[2.5rem] shadow-2xl p-6 md:p-8 border border-slate-100/50 flex flex-col items-center animate-in fade-in zoom-in duration-700 relative">
        
        {{-- Adorno superior --}}
        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-indigo-600 text-white px-8 py-3 rounded-full shadow-xl shadow-indigo-200 border-4 border-white flex items-center gap-3 shrink-0">
            <i data-lucide="qr-code" class="w-5 h-5"></i>
            <span class="text-[10px] font-black uppercase tracking-[0.3em]">Registro de Asistencia</span>
        </div>

        <div class="text-center mb-4">
            <h1 class="text-2xl md:text-3xl font-black text-slate-800 uppercase tracking-tight leading-tight mb-2">
                {{ $reunion->titulo_reunion }}
            </h1>
            <p class="text-xs md:text-sm text-slate-400 font-bold uppercase tracking-widest flex items-center justify-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                {{ \Carbon\Carbon::parse($reunion->fecha_reunion)->format('d \d\e F, Y') }}
                <span class="mx-1.5 text-slate-200">|</span>
                <i data-lucide="map-pin" class="w-4 h-4"></i>
                {{ $reunion->nombre_institucion }}
            </p>
        </div>

        <div class="bg-slate-50 rounded-[1.5rem] p-4 border-2 border-dashed border-slate-200 mb-4 shadow-inner">
            <div id="qr-container" class="bg-white p-4 rounded-[1.2rem] shadow-xl border border-slate-100">
                {{-- QR --}}
            </div>
        </div>

        <div class="text-center">
            <div class="inline-flex items-center gap-2 bg-indigo-50 text-indigo-600 px-4 py-2 rounded-xl font-black uppercase tracking-widest text-xs mb-3 border border-indigo-100">
                <i data-lucide="smartphone" class="w-4 h-4 animate-pulse"></i>
                Escanee para registrarse
            </div>
            
            <p class="text-slate-400 font-mono text-[9px] tracking-tighter opacity-60">
                {{ $url }}
            </p>
        </div>

    </div>

    <div class="mt-6 text-slate-400 flex items-center gap-4 font-black uppercase tracking-[0.3em] text-[8px] shrink-0">
        <span class="flex items-center gap-1.5"><i data-lucide="shield-check" class="w-3 h-3"></i> ICATEC</span>
        <span class="opacity-30">●</span>
        <span>SISTEMA DE ACTAS DIGITALES</span>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            
            new QRCode(document.getElementById('qr-container'), {
                text: "{{ $url }}",
                width: 240,
                height: 240,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });
        });
    </script>
</body>
</html>
