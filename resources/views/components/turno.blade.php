@props(['selected' => null])

@php
    if (empty($selected)) {
        $hour = (int) date('H');
        $selected = ($hour >= 0 && $hour < 14) ? 'MAÑANA' : 'TARDE';
    }
@endphp

<div class="md:col-span-2">
    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">
        Turno
    </label>
    <div class="flex gap-4">
        {{-- OPCIÓN MAÑANA --}}
        <label class="flex-1 relative cursor-pointer group">
            <input type="radio" name="contenido[turno]" value="MAÑANA" 
                {{ $selected == 'MAÑANA' ? 'checked' : '' }} 
                class="peer sr-only">
            <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center transition-all 
                peer-checked:border-amber-400 peer-checked:bg-amber-50 peer-checked:text-amber-700 
                peer-checked:shadow-sm hover:border-amber-200">
                <div class="flex items-center justify-center gap-2">
                    <i data-lucide="sun" class="w-4 h-4"></i>
                    <span class="text-xs font-black uppercase tracking-wider">MAÑANA</span>
                </div>
            </div>
        </label>
        
        {{-- OPCIÓN TARDE --}}
        <label class="flex-1 relative cursor-pointer group">
            <input type="radio" name="contenido[turno]" value="TARDE" 
                {{ $selected == 'TARDE' ? 'checked' : '' }} 
                class="peer sr-only">
            <div class="p-4 rounded-2xl border-2 border-slate-100 bg-slate-50 text-center transition-all 
                peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 
                peer-checked:shadow-sm hover:border-indigo-200">
                <div class="flex items-center justify-center gap-2">
                    <i data-lucide="sunset" class="w-4 h-4"></i>
                    <span class="text-xs font-black uppercase tracking-wider">TARDE</span>
                </div>
            </div>
        </label>
    </div>
</div>