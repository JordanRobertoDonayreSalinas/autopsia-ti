@props(['comentario' => null, 'detalle' => null])

@php
    // 1. Recuperar datos
    $data = $comentario ?? $detalle ?? $attributes->get('detalle');

    // 2. Extraer valores
    $fotoUrl = data_get($data, 'foto_url_esp');
    $texto   = data_get($data, 'comentario_esp');

    // 3. Estado Inicial
    $tieneFoto = !empty($fotoUrl);
    $rutaImagen = $tieneFoto ? asset('storage/' . $fotoUrl) : '';
@endphp

<div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 mb-6 relative overflow-hidden">
    {{-- Decoración superior --}}
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-400 via-teal-500 to-emerald-400"></div>

    {{-- SECCIÓN TEXTO --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                {{-- Icono Comentarios --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/><path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/></svg>
            </div>
            <div>
                <h3 class="text-slate-800 font-black text-sm uppercase">Comentarios</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Observaciones Técnicas</p>
            </div>
        </div>
        <textarea name="comentario_esp" rows="3" class="w-full bg-slate-50 border-2 border-slate-100 rounded-2xl p-4 text-slate-700 font-bold text-xs outline-none focus:bg-white focus:border-emerald-500 transition-all resize-none shadow-inner" placeholder="ESCRIBA SUS OBSERVACIONES AQUÍ...">{{ old('comentario_esp', $texto ?? '') }}</textarea>
    </div>

    <div class="h-px w-full bg-slate-100 mb-6"></div>

    {{-- SECCIÓN FOTO --}}
    <div class="photo-wrapper">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-100 flex items-center justify-center text-teal-600">
                {{-- Icono Cámara --}}
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
            </div>
            <div>
                <h3 class="text-slate-800 font-black text-sm uppercase">Evidencia Fotográfica</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Respaldo Visual</p>
            </div>
        </div>

        {{-- AREA DE CARGA --}}
        {{-- CORRECCIÓN: style="min-height: 200px" fuerza la altura aunque Tailwind falle --}}
        <div class="relative group w-full bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl overflow-hidden hover:border-emerald-400 transition-colors" style="min-height: 200px; height: 24rem;">
            
            {{-- INPUT FILE (Cubre todo, Z-Index SUPERIOR) --}}
            <input 
                type="file" 
                name="foto_esp_file" 
                accept="image/*" 
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-50"
                style="display: block;"
                onchange="handlePhotoPreview(this)"
            >

            {{-- 1. PLACEHOLDER (Texto 'Subir Foto') --}}
            {{-- Usamos flex y h-full para centrar el contenido --}}
            <div class="placeholder-container absolute inset-0 w-full h-full flex flex-col items-center justify-center pointer-events-none transition-opacity duration-300 {{ $tieneFoto ? 'opacity-0' : 'opacity-100' }}">
                <div class="bg-white p-3 rounded-2xl shadow-sm border border-slate-100 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-emerald-500"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                </div>
                <span class="text-xs font-black uppercase text-slate-600">Subir Foto</span>
                <span class="text-[10px] font-bold text-slate-400">JPG, PNG</span>
            </div>

            {{-- 2. IMAGEN PREVIA --}}
            <img 
                src="{{ $rutaImagen }}" 
                class="preview-image absolute inset-0 w-full h-full object-cover bg-white p-2 z-20 {{ $tieneFoto ? 'block' : 'hidden' }}"
                style="object-fit: cover;"
            >

            {{-- 3. OVERLAY 'CAMBIAR' --}}
            <div class="overlay-change absolute inset-0 bg-slate-900/40 flex items-center justify-center z-30 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none {{ $tieneFoto ? 'block' : 'hidden' }}">
                <div class="bg-white px-4 py-2 rounded-xl shadow-lg transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300">
                    <span class="text-emerald-600 font-black text-[10px] uppercase tracking-widest flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        Cambiar Foto
                    </span>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function handlePhotoPreview(input) {
        if (input.files && input.files[0]) {
            const wrapper = input.closest('.photo-wrapper');
            const previewImg = wrapper.querySelector('.preview-image');
            const placeholder = wrapper.querySelector('.placeholder-container');
            const overlay = wrapper.querySelector('.overlay-change');

            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                // Forzar visualización usando estilo directo además de clases
                previewImg.classList.remove('hidden');
                previewImg.style.display = 'block'; 
                
                placeholder.classList.remove('opacity-100');
                placeholder.classList.add('opacity-0');
                
                overlay.classList.remove('hidden');
                overlay.classList.add('block');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>