<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Monitoreo – HERRAMIENTA DE IMPLEMENTACION SIHCE</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { margin: 0; padding: 0; width: 100% !important; background-color: #0f172a; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
        table { border-collapse: collapse !important; mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }
        p { display: block; margin: 13px 0; }
        @media only screen and (max-width: 640px) {
            .wrapper { width: 100% !important; border-radius: 0 !important; }
            .header-inner { padding: 40px 24px !important; }
            .body-inner { padding: 28px 20px !important; }
            .card-inner { padding: 20px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #0f172a;">
    <center>
        <!-- Outer bg -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background: linear-gradient(160deg, #0f172a 0%, #1e293b 100%); padding: 40px 10px;">
            <tr>
                <td align="center">

                    <!-- Main Card -->
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600" class="wrapper" style="background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.5);">

                        <!-- ══ HEADER ══ -->
                        <tr>
                            <td class="header-inner" style="padding: 0; background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 50%, #1e3a8a 100%); position: relative;">
                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="background: rgba(255,255,255,0.1); height: 4px;"></td>
                                    </tr>
                                </table>
                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center" style="padding: 48px 40px 44px;">
                                            <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.15); border-radius: 16px; margin: 0 auto 20px; line-height: 64px; font-size: 32px; text-align: center; border: 1px solid rgba(255,255,255,0.2);">📊</div>
                                            <p style="margin: 0 0 10px; color: rgba(255,255,255,0.65); font-size: 10px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase;">HERRAMIENTA DE IMPLEMENTACION SIHCE</p>
                                            <h1 style="margin: 0 0 8px; color: #ffffff; font-size: 28px; font-weight: 800; letter-spacing: -0.03em; line-height: 1.2;">Acta de Monitoreo<br><span style="color: #93c5fd;">Firmada</span></h1>
                                            <div style="display: inline-block; margin-top: 16px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.15); border-radius: 999px; padding: 6px 18px;">
                                                <span style="color: rgba(255,255,255,0.9); font-size: 12px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;">ACTA N° {{ str_pad($acta->numero_acta ?? $acta->id, 5, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- ══ BODY ══ -->
                        <tr>
                            <td class="body-inner" style="padding: 44px 44px 36px; background-color: #ffffff;">
                                <h2 style="margin: 0 0 8px; color: #0f172a; font-size: 20px; font-weight: 700;">Estimado(a) profesional,</h2>
                                <p style="margin: 0 0 32px; color: #64748b; font-size: 14px; line-height: 1.75; font-weight: 400;">
                                    Se adjunta el acta de monitoreo correspondiente a la visita realizada. El documento consolidado ha sido <strong style="color: #1d4ed8;">validado y firmado oficialmente</strong>.
                                </p>

                                <!-- Summary Card -->
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="card-inner" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 16px; border: 1px solid #bfdbfe; padding: 28px 28px;">
                                    <tr>
                                        <td>
                                            <p style="margin: 0 0 20px; font-size: 10px; font-weight: 800; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.15em;">📋 Resumen del Acta</p>
                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <!-- Fecha -->
                                                <tr>
                                                    <td style="padding: 10px 0; border-bottom: 1px solid rgba(29,78,216,0.1);">
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                            <tr>
                                                                <td style="font-size: 11px; font-weight: 600; color: #6b7280;">📅 Fecha de Visita</td>
                                                                <td align="right" style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ \Carbon\Carbon::parse($acta->fecha)->format('d/m/Y') }}</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <!-- Establecimiento -->
                                                <tr>
                                                    <td style="padding: 10px 0; border-bottom: 1px solid rgba(29,78,216,0.1);">
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                            <tr>
                                                                <td style="font-size: 11px; font-weight: 600; color: #6b7280;">🏥 Establecimiento</td>
                                                                <td align="right" style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $acta->establecimiento->nombre ?? 'N/A' }}</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <!-- Provincia -->
                                                <tr>
                                                    <td style="padding: 10px 0; border-bottom: 1px solid rgba(29,78,216,0.1);">
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                            <tr>
                                                                <td style="font-size: 11px; font-weight: 600; color: #6b7280;">📍 Ubicación</td>
                                                                <td align="right" style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $acta->establecimiento->distrito ?? '' }} – {{ $acta->establecimiento->provincia ?? 'N/A' }}</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <!-- Implementador -->
                                                <tr>
                                                    <td style="padding: 10px 0;">
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                            <tr>
                                                                <td style="font-size: 11px; font-weight: 600; color: #6b7280;">👤 Implementador</td>
                                                                <td align="right" style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $acta->implementador ?? 'N/A' }}</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Attachment Notice -->
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 24px;">
                                    <tr>
                                        <td align="center" style="background: linear-gradient(135deg, #1d4ed8, #1e40af); border-radius: 12px; padding: 16px 20px;">
                                            <p style="margin: 0; color: #ffffff; font-size: 13px; font-weight: 600;">
                                                <span style="font-size: 18px; vertical-align: middle; margin-right: 8px;">🖇️</span>
                                                El acta consolidada firmada se encuentra adjunta en formato PDF.
                                            </p>
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin: 20px 0 0; color: #94a3b8; font-size: 11px; text-align: center; line-height: 1.6;">
                                    Cualquier modificación al documento adjunto invalidará la firma digital.
                                </p>
                            </td>
                        </tr>

                        <!-- ══ FOOTER ══ -->
                        <tr>
                            <td style="padding: 28px 44px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                                <p style="margin: 0 0 6px; font-size: 12px; font-weight: 800; color: #0f172a; letter-spacing: 0.05em;">HERRAMIENTA DE IMPLEMENTACION SIHCE</p>
                                <p style="margin: 0 0 12px; font-size: 11px; color: #94a3b8; line-height: 1.6;">
                                    Este es un mensaje automático. Por favor no responda directamente a este correo.
                                </p>
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="height: 1px; background: linear-gradient(to right, transparent, #e2e8f0, transparent);"></td>
                                    </tr>
                                </table>
                                <p style="margin: 12px 0 0; font-size: 10px; font-weight: 600; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.1em;">
                                    © {{ date('Y') }} ICATEC · Todas las firmas son válidas digitalmente
                                </p>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
