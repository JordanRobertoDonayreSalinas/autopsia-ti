<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Asistencia Técnica – HERRAMIENTA DE IMPLEMENTACION SIHCE</title>
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
                            <td class="header-inner" style="padding: 0; background: linear-gradient(135deg, #059669 0%, #047857 50%, #065f46 100%); position: relative;">
                                <!-- Top accent bar -->
                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td style="background: rgba(255,255,255,0.08); height: 4px;"></td>
                                    </tr>
                                </table>
                                <!-- Header content -->
                                <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td align="center" style="padding: 48px 40px 44px;">
                                            <!-- Icon badge -->
                                            <div style="width: 64px; height: 64px; background: rgba(255,255,255,0.15); border-radius: 16px; margin: 0 auto 20px; line-height: 64px; font-size: 32px; text-align: center; border: 1px solid rgba(255,255,255,0.2);">📄</div>
                                            <!-- System tag -->
                                            <p style="margin: 0 0 10px; color: rgba(255,255,255,0.65); font-size: 10px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase;">HERRAMIENTA DE IMPLEMENTACION SIHCE</p>
                                            <!-- Title -->
                                            <h1 style="margin: 0 0 8px; color: #ffffff; font-size: 28px; font-weight: 800; letter-spacing: -0.03em; line-height: 1.2;">Acta de Asistencia<br>Técnica <span style="color: #6ee7b7;">Firmada</span></h1>
                                            <!-- Correlativo badge -->
                                            <div style="display: inline-block; margin-top: 16px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.15); border-radius: 999px; padding: 6px 18px;">
                                                <span style="color: rgba(255,255,255,0.9); font-size: 12px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;">AAT Nº {{ str_pad($acta->id, 3, '0', STR_PAD_LEFT) }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- ══ BODY ══ -->
                        <tr>
                            <td class="body-inner" style="padding: 44px 44px 36px; background-color: #ffffff;">
                                <!-- Greeting -->
                                <h2 style="margin: 0 0 8px; color: #0f172a; font-size: 20px; font-weight: 700;">Estimado(a) profesional,</h2>
                                <p style="margin: 0 0 32px; color: #64748b; font-size: 14px; line-height: 1.75; font-weight: 400;">
                                    Se adjunta el acta de asistencia técnica correspondiente a la actividad realizada. El documento ha sido <strong style="color: #059669;">validado y firmado oficialmente</strong> por las partes involucradas.
                                </p>

                                <!-- Summary Card -->
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" class="card-inner" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border-radius: 16px; border: 1px solid #bbf7d0; padding: 28px 28px;">
                                    <tr>
                                        <td>
                                            <!-- Card header -->
                                            <p style="margin: 0 0 20px; font-size: 10px; font-weight: 800; color: #059669; text-transform: uppercase; letter-spacing: 0.15em;">📋 Resumen del Documento</p>
                                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                <!-- Fecha -->
                                                <tr>
                                                    <td style="padding: 10px 0; border-bottom: 1px solid rgba(5,150,105,0.12);">
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                            <tr>
                                                                <td style="font-size: 11px; font-weight: 600; color: #6b7280;">📅 Fecha de Emisión</td>
                                                                <td align="right" style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ \Carbon\Carbon::parse($acta->fecha)->format('d/m/Y') }}</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <!-- Establecimiento -->
                                                <tr>
                                                    <td style="padding: 10px 0; border-bottom: 1px solid rgba(5,150,105,0.12);">
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                            <tr>
                                                                <td style="font-size: 11px; font-weight: 600; color: #6b7280;">🏥 Establecimiento</td>
                                                                <td align="right" style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $acta->establecimiento->nombre ?? 'N/A' }}</td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <!-- Tema -->
                                                <tr>
                                                    <td style="padding: 10px 0;">
                                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                                            <tr>
                                                                <td style="font-size: 11px; font-weight: 600; color: #6b7280;">📝 Tema / Actividad</td>
                                                                <td align="right" style="font-size: 13px; font-weight: 700; color: #0f172a; max-width: 260px; word-break: break-word;">{{ $acta->tema }}</td>
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
                                        <td align="center" style="background: linear-gradient(135deg, #059669, #047857); border-radius: 12px; padding: 16px 20px;">
                                            <p style="margin: 0; color: #ffffff; font-size: 13px; font-weight: 600;">
                                                <span style="font-size: 18px; vertical-align: middle; margin-right: 8px;">🖇️</span>
                                                El documento firmado se encuentra adjunto a este correo en formato PDF.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <!-- ══ FOOTER ══ -->
                        <tr>
                            <td style="padding: 28px 44px 32px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; text-align: center;">
                                <!-- Logo text -->
                                <p style="margin: 0 0 6px; font-size: 12px; font-weight: 800; color: #0f172a; letter-spacing: 0.05em;">HERRAMIENTA DE IMPLEMENTACION SIHCE</p>
                                <p style="margin: 0 0 12px; font-size: 11px; color: #94a3b8; line-height: 1.6;">
                                    Este es un mensaje automático. Por favor no responda directamente a este correo.
                                </p>
                                <!-- Divider -->
                                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                                    <tr>
                                        <td style="height: 1px; background: linear-gradient(to right, transparent, #e2e8f0, transparent);"></td>
                                    </tr>
                                </table>
                                <p style="margin: 12px 0 0; font-size: 10px; font-weight: 600; color: #059669; text-transform: uppercase; letter-spacing: 0.1em;">
                                    © {{ date('Y') }} ICATEC · Todos los derechos reservados
                                </p>
                            </td>
                        </tr>

                    </table>
                    <!-- End Main Card -->

                </td>
            </tr>
        </table>
    </center>
</body>
</html>
