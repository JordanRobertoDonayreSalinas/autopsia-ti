<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <title>Firmar Documento</title>
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-slate-100 h-screen flex flex-col items-center justify-center p-4">

  <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
    <div class="bg-slate-800 p-4 text-center">
      <h1 class="text-white font-bold text-lg">Panel de Firma</h1>
      <p class="text-slate-400 text-xs">Gire el celular si necesita más espacio</p>
    </div>

    <div class="p-4">
      <div class="border-2 border-dashed border-slate-300 rounded-xl bg-slate-50 touch-none mb-4 relative">
        <canvas id="mobile-pad" class="w-full h-64 block rounded-xl"></canvas>

        <div class="absolute bottom-2 right-2 text-[10px] text-slate-400 pointer-events-none">
          Área de firma
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <button onclick="clearPad()"
          class="py-3 px-4 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg transition">
          Borrar
        </button>
        <button onclick="sendSignature()" id="btn-enviar"
          class="py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-md transition flex justify-center items-center">
          <span>Enviar Firma</span>
        </button>
      </div>
    </div>
  </div>

  <script>
    const canvas = document.getElementById('mobile-pad');
    const btn = document.getElementById('btn-enviar');

    function resizeCanvas() {
      const ratio = Math.max(window.devicePixelRatio || 1, 1);
      canvas.width = canvas.offsetWidth * ratio;
      canvas.height = canvas.offsetHeight * ratio;
      canvas.getContext("2d").scale(ratio, ratio);
    }
    window.onresize = resizeCanvas;
    resizeCanvas();

    const pad = new SignaturePad(canvas, {
      backgroundColor: 'rgb(248, 250, 252)'
    });

    function clearPad() {
      pad.clear();
    }

    function sendSignature() {
      if (pad.isEmpty()) return alert("Por favor realice su firma primero.");

      // Efecto de carga
      btn.innerHTML = 'Enviando...';
      btn.disabled = true;

      fetch("{{ url('/firmar/save/' . $token) }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
          firma: pad.toDataURL()
        })
      })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            document.body.innerHTML = `
                        <div class="h-screen flex flex-col items-center justify-center bg-green-50 text-center p-6">
                            <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <h2 class="text-xl font-bold text-green-800 mb-2">¡Firma Enviada!</h2>
                            <p class="text-green-600">Ya puede verla en la pantalla de su computadora.</p>
                        </div>
                    `;
          }
        })
        .catch(err => {
          alert("Error al enviar. Intente nuevamente.");
          btn.innerHTML = 'Enviar Firma';
          btn.disabled = false;
        });
    }
  </script>
</body>

</html>