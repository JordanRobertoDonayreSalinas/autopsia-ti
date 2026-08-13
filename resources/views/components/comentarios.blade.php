{{-- 7. NUEVA SECCIÓN: COMENTARIOS GENERALES (HTML Directo) --}}
            <div class="bg-white border border-slate-200 rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50 rounded-full -mr-12 -mt-12 opacity-60 pointer-events-none"></div>
                
                <div class="flex items-center gap-4 mb-6 relative z-10">
                    <div class="h-12 w-12 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                        <i data-lucide="message-square-plus" class="text-white w-6 h-6"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Comentarios</h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">Observaciones Adicionales</p>
                    </div>
                </div>

                <div class="relative z-10">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Detalle de observaciones</label>
                    <textarea 
                        x-model="form.inicio_labores.comentarios" 
                        rows="3" 
                        placeholder="Ingrese cualquier observación general relevante sobre el servicio..." 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 font-medium text-sm focus:ring-indigo-500 text-slate-700 uppercase"></textarea>
                </div>
            </div>