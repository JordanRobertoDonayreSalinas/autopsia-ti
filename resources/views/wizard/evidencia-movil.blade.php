<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <title>Subir Fotos - {{ $tituloConsultorio }}</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-slate-100 min-h-screen flex flex-col items-center p-4">

  <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden my-4">
    <div class="bg-slate-800 p-4 text-center">
      <h1 class="text-white font-black text-base uppercase tracking-tight">📷 Evidencia Fotográfica</h1>
      <p class="text-indigo-300 text-sm font-bold mt-1">{{ $tituloConsultorio }}</p>
      <p class="text-slate-400 text-[11px] mt-1">Las fotos se suben directo, sin pasar por la computadora</p>
    </div>

    <div class="p-5 space-y-4">
      <div class="flex items-center justify-between bg-indigo-50 rounded-xl px-4 py-2.5">
        <span class="text-[11px] font-black text-indigo-700 uppercase">Fotos subidas</span>
        <span id="contador" class="text-sm font-black text-indigo-700">{{ count($evidencias) }} / {{ $maxEvidencias }}</span>
      </div>

      {{-- Paso 1: tomar / elegir foto --}}
      <div id="paso_captura" class="{{ count($evidencias) >= $maxEvidencias ? 'hidden' : '' }}">
        <label for="input_foto" class="block cursor-pointer">
          <div class="border-2 border-dashed border-indigo-300 rounded-2xl bg-indigo-50/60 h-48 flex flex-col items-center justify-center gap-2 text-center px-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl">📷</div>
            <p class="text-xs font-black text-indigo-700 uppercase">Toque para tomar una foto</p>
            <p class="text-[10px] font-bold text-indigo-400">o elegir de la galería</p>
          </div>
        </label>
        <input type="file" id="input_foto" accept="image/*" capture="environment" class="hidden" onchange="mostrarPreview(this)">
      </div>

      <div id="max_alcanzado" class="{{ count($evidencias) >= $maxEvidencias ? '' : 'hidden' }}">
        <div class="border-2 border-dashed border-slate-300 rounded-2xl bg-slate-50 h-24 flex flex-col items-center justify-center gap-1 text-center px-4">
          <p class="text-xs font-black text-slate-500 uppercase">Máximo de {{ $maxEvidencias }} fotos alcanzado</p>
          <p class="text-[10px] font-bold text-slate-400">Elimine alguna de abajo para poder subir otra</p>
        </div>
      </div>

      {{-- Paso 2: preview + descripción + subir --}}
      <div id="paso_descripcion" class="hidden space-y-3">
        <img id="preview_img" class="w-full h-48 object-cover rounded-2xl bg-slate-100" alt="Vista previa">
        <div>
          <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Descripción de la foto</label>
          <textarea id="input_descripcion" rows="2" maxlength="255"
            class="w-full px-3 py-2.5 bg-slate-50 border-2 border-indigo-200 focus:border-indigo-600 rounded-xl font-bold text-sm text-slate-700 outline-none transition-all"
            placeholder="Ej: Vista general del consultorio, equipo de cómputo, tomacorriente..."></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <button onclick="cancelarFoto()"
            class="py-3 px-4 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl transition text-sm">
            Cancelar
          </button>
          <button onclick="subirFoto()" id="btn_subir"
            class="py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition text-sm">
            Subir Foto
          </button>
        </div>
      </div>

      <div id="mensaje_estado" class="hidden text-center text-xs font-bold rounded-xl p-3"></div>

      {{-- Galería: fotos ya subidas para este consultorio, con opción de eliminar --}}
      <div>
        <label class="block text-[10px] font-black text-slate-500 uppercase mb-1">Fotos de este consultorio</label>
        <div id="galeria" class="space-y-2">
          @forelse ($evidencias as $ev)
            <div class="flex items-center gap-3 bg-slate-50 rounded-xl p-2 border border-slate-100" data-path="{{ $ev['path'] }}">
              <img src="{{ asset('storage/' . $ev['path']) }}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
              <div class="min-w-0 flex-1">
                <p class="text-[11px] font-bold text-slate-700 truncate">{{ $ev['descripcion'] ?: 'Sin descripción' }}</p>
                <p class="text-[9px] font-black text-emerald-600 uppercase">✓ Subida</p>
              </div>
              <button onclick="eliminarFoto('{{ $ev['path'] }}', this)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition flex-shrink-0" title="Eliminar foto">
                🗑️
              </button>
            </div>
          @empty
            <p id="galeria_vacia" class="text-center text-[11px] font-bold text-slate-400 py-2">Todavía no hay fotos subidas</p>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <script>
    const TOKEN = @json($token);
    const MAX_EVIDENCIAS = @json($maxEvidencias);
    let contador = @json(count($evidencias));
    let archivoActual = null;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const pasoCaptura = document.getElementById('paso_captura');
    const maxAlcanzado = document.getElementById('max_alcanzado');
    const pasoDescripcion = document.getElementById('paso_descripcion');
    const previewImg = document.getElementById('preview_img');
    const inputFoto = document.getElementById('input_foto');
    const inputDescripcion = document.getElementById('input_descripcion');
    const btnSubir = document.getElementById('btn_subir');
    const contadorEl = document.getElementById('contador');
    const mensajeEstado = document.getElementById('mensaje_estado');
    const galeria = document.getElementById('galeria');

    function actualizarEstadoCaptura() {
      const alTope = contador >= MAX_EVIDENCIAS;
      pasoCaptura.classList.toggle('hidden', alTope);
      maxAlcanzado.classList.toggle('hidden', !alTope);
    }

    function mostrarPreview(input) {
      if (!input.files || !input.files[0]) return;
      archivoActual = input.files[0];
      const reader = new FileReader();
      reader.onload = e => {
        previewImg.src = e.target.result;
        pasoCaptura.classList.add('hidden');
        pasoDescripcion.classList.remove('hidden');
        inputDescripcion.value = '';
        inputDescripcion.focus();
      };
      reader.readAsDataURL(archivoActual);
    }

    function cancelarFoto() {
      archivoActual = null;
      inputFoto.value = '';
      pasoDescripcion.classList.add('hidden');
      actualizarEstadoCaptura();
    }

    function mostrarMensaje(texto, tipo) {
      mensajeEstado.textContent = texto;
      mensajeEstado.className = 'text-center text-xs font-bold rounded-xl p-3 ' +
        (tipo === 'error' ? 'bg-rose-50 text-rose-700' : 'bg-emerald-50 text-emerald-700');
      mensajeEstado.classList.remove('hidden');
      setTimeout(() => mensajeEstado.classList.add('hidden'), 4000);
    }

    function agregarAGaleria(path, descripcion, dataUrl) {
      const vacia = document.getElementById('galeria_vacia');
      if (vacia) vacia.remove();

      const item = document.createElement('div');
      item.className = 'flex items-center gap-3 bg-slate-50 rounded-xl p-2 border border-slate-100';
      item.dataset.path = path;
      item.innerHTML = `
        <img src="${dataUrl}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
        <div class="min-w-0 flex-1">
          <p class="text-[11px] font-bold text-slate-700 truncate">${descripcion || 'Sin descripción'}</p>
          <p class="text-[9px] font-black text-emerald-600 uppercase">✓ Subida</p>
        </div>
        <button onclick="eliminarFoto('${path}', this)" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition flex-shrink-0" title="Eliminar foto">🗑️</button>
      `;
      galeria.prepend(item);
    }

    function eliminarFoto(path, btnEl) {
      if (!confirm('¿Eliminar esta foto? Esta acción no se puede deshacer.')) return;

      btnEl.disabled = true;
      btnEl.textContent = '...';

      fetch(`{{ url('/evidencia-movil') }}/${TOKEN}/eliminar`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ path }),
      })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
          if (!ok || !data.success) {
            btnEl.disabled = false;
            btnEl.textContent = '🗑️';
            return mostrarMensaje(data.message || 'No se pudo eliminar la foto.', 'error');
          }

          contador = data.total;
          contadorEl.textContent = `${contador} / ${MAX_EVIDENCIAS}`;
          actualizarEstadoCaptura();
          mostrarMensaje('Foto eliminada.', 'ok');

          const item = document.querySelector(`#galeria [data-path="${CSS.escape(path)}"]`);
          if (item) item.remove();
          if (galeria.children.length === 0) {
            galeria.innerHTML = '<p id="galeria_vacia" class="text-center text-[11px] font-bold text-slate-400 py-2">Todavía no hay fotos subidas</p>';
          }
        })
        .catch(() => {
          btnEl.disabled = false;
          btnEl.textContent = '🗑️';
          mostrarMensaje('Error de conexión. Intente nuevamente.', 'error');
        });
    }

    function subirFoto() {
      if (!archivoActual) return;
      const descripcion = inputDescripcion.value.trim();
      if (!descripcion) {
        inputDescripcion.focus();
        return mostrarMensaje('Escriba una descripción para la foto.', 'error');
      }

      btnSubir.disabled = true;
      btnSubir.textContent = 'Subiendo...';

      const formData = new FormData();
      formData.append('foto', archivoActual);
      formData.append('descripcion', descripcion);

      fetch(`{{ url('/evidencia-movil') }}/${TOKEN}/subir`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        body: formData,
      })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
          btnSubir.disabled = false;
          btnSubir.textContent = 'Subir Foto';

          if (!ok || !data.success) {
            return mostrarMensaje(data.message || 'Error al subir la foto.', 'error');
          }

          contador = data.total;
          contadorEl.textContent = `${contador} / ${MAX_EVIDENCIAS}`;
          agregarAGaleria(data.path, descripcion, previewImg.src);
          mostrarMensaje('¡Foto subida! Ya aparece en la computadora.', 'ok');

          archivoActual = null;
          inputFoto.value = '';
          pasoDescripcion.classList.add('hidden');
          actualizarEstadoCaptura();
        })
        .catch(() => {
          btnSubir.disabled = false;
          btnSubir.textContent = 'Subir Foto';
          mostrarMensaje('Error de conexión. Intente nuevamente.', 'error');
        });
    }
  </script>
</body>

</html>
