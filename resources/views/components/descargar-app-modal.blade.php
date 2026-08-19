<script>
    function abrirModalDescargaApp() {
        Swal.fire({
            title: 'Descargar App de Campo',
            html: `
                <p style="font-size:13px;color:#64748b;margin-bottom:20px;">
                    Elegí el dispositivo donde vas a instalar la aplicación para trabajar sin conexión.
                </p>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    <a href="{{ asset('downloads/AutopsiaTI-Setup.exe') }}"
                       style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:12px;text-decoration:none;color:#1e40af;font-weight:700;font-size:14px;">
                        <span style="font-size:22px;">💻</span>
                        <span style="text-align:left;">
                            PC / Laptop (Windows)<br>
                            <small style="font-weight:500;color:#64748b;">Descarga instalador .exe</small>
                        </span>
                    </a>
                    <a href="{{ asset('downloads/AutopsiaTI.apk') }}"
                       style="display:flex;align-items:center;gap:12px;padding:14px 16px;background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;text-decoration:none;color:#166534;font-weight:700;font-size:14px;">
                        <span style="font-size:22px;">📱</span>
                        <span style="text-align:left;">
                            Celular / Tablet (Android)<br>
                            <small style="font-weight:500;color:#64748b;">Descarga app .apk</small>
                        </span>
                    </a>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            width: 420,
        });
    }
</script>
