@props(['detalle' => null])

<section id="bloque-dificultades"
    {{ $attributes->merge(['class' => 'bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden mt-6']) }}>

    {{-- 1. Encabezado Integrado --}}
    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3 bg-slate-50/50">
        {{-- Contenedor del icono con colores teal (verde azulado) --}}
        <div class="p-2 bg-teal-50 text-teal-600 rounded-lg border border-teal-100 shadow-sm">
            {{-- "cpu" es ideal para soporte tecnológico. Otras opciones: "monitor-wrench" o "terminal" --}}
            <i data-lucide="cpu" class="w-5 h-5"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-teal-900 leading-tight">SOPORTE</h2>
            <p class="text-xs text-slate-400 font-medium">DIFICULTADES Y COMUNICACIÓN</p>
        </div>
    </div>

    {{-- 2. Cuerpo del Contenido --}}
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8 relative">

        {{-- Columna 1 --}}
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                Ante Dificultades ¿A quién comunica?
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach (['MINSA', 'DIRESA', 'OTROS', 'UNIDAD EJECUTORA', 'JEFE DE ESTABLECIMIENTO'] as $opcion)
                    <label class="cursor-pointer group relative">
                        <input type="radio" name="contenido[dificultades][comunica]" value="{{ $opcion }}"
                            class="peer sr-only" {{-- CORRECCIÓN: Leer desde contenido['dificultades']['comunica'] --}}
                            {{ ($detalle->contenido['dificultades']['comunica'] ?? '') == $opcion ? 'checked' : '' }}>

                        <div
                            class="text-center py-2.5 px-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all peer-checked:border-teal-500 peer-checked:bg-teal-50 peer-checked:shadow-sm h-full flex items-center justify-center">
                            <span class="block text-[11.5px] font-bold text-slate-600 peer-checked:text-teal-700">
                                {{ $opcion }}
                            </span>
                        </div>

                        {{-- Icono Check Minimalista --}}
                        <div
                            class="absolute -top-1.5 -right-1.5 bg-teal-600 text-white rounded-full p-0.5 opacity-0 peer-checked:opacity-100 transition-all transform scale-50 peer-checked:scale-100 shadow-sm z-10">
                            <i data-lucide="check" class="w-2 h-2"></i>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Separador Vertical (Mejorado para que no toque los bordes) --}}
        <div class="hidden md:block absolute top-6 bottom-6 left-1/2 w-px bg-slate-100 -translate-x-1/2"></div>

        {{-- Columna 2 --}}
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                ¿Qué medio utiliza?
            </p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach (['WHATSAPP', 'CELULAR', 'CORREO', 'OTROS'] as $opcion)
                    <label class="cursor-pointer group relative">
                        <input type="radio" name="contenido[dificultades][medio]" value="{{ $opcion }}"
                            class="peer sr-only" {{-- CORRECCIÓN: Leer desde contenido['dificultades']['medio'] --}}
                            {{ ($detalle->contenido['dificultades']['medio'] ?? '') == $opcion ? 'checked' : '' }}>

                        <div
                            class="text-center py-2.5 px-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 transition-all peer-checked:border-teal-500 peer-checked:bg-teal-50 peer-checked:shadow-sm h-full flex items-center justify-center">
                            <span class="block text-[11.5px] font-bold text-slate-600 peer-checked:text-teal-700">
                                {{ $opcion }}
                            </span>
                        </div>

                        <div
                            class="absolute -top-1.5 -right-1.5 bg-teal-600 text-white rounded-full p-0.5 opacity-0 peer-checked:opacity-100 transition-all transform scale-50 peer-checked:scale-100 shadow-sm z-10">
                            <i data-lucide="check" class="w-2 h-2"></i>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

    </div>
</section>
