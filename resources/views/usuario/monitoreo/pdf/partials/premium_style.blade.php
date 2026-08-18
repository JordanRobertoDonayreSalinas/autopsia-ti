<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap');

    @page {
        margin: 1.2cm 1.2cm 1.4cm 1.2cm;
    }
    * {
        box-sizing: border-box;
    }
    body {
        font-family: 'Plus Jakarta Sans', 'Inter', 'Segoe UI', 'Century Gothic', 'Calibri', 'Helvetica Neue', 'Arial', sans-serif;
        font-size: 8px;
        color: #1e293b;
        line-height: 1.45;
        background-color: #ffffff;
        margin: 0;
        padding: 0;
    }

    /* Utilidades Generales */
    .text-center { text-align: center !important; }
    .text-right { text-align: right !important; }
    .text-left { text-align: left !important; }
    .font-bold { font-weight: 700 !important; }
    .font-black { font-weight: 800 !important; }
    .uppercase { text-transform: uppercase !important; }
    .no-break { page-break-inside: avoid; }

    /* ENCABEZADO INSTITUCIONAL */
    .header-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
        border-bottom: 2px solid #1e3a8a;
        padding-bottom: 8px;
    }
    .header-table td {
        border: none;
        padding: 0;
        vertical-align: middle;
    }
    .inst-title {
        font-size: 7px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 1px;
        text-transform: uppercase;
    }
    .main-title {
        font-size: 13.5px;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.2px;
        margin: 2px 0 3px 0;
    }
    .submodule-badge {
        display: inline-block;
        background-color: #1e3a8a;
        color: #ffffff;
        font-size: 8.5px;
        font-weight: 700;
        padding: 2.5px 8px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .eess-info {
        font-size: 8px;
        font-weight: 700;
        color: #475569;
        margin-top: 4px;
        text-transform: uppercase;
        letter-spacing: 0.2px;
    }
    .acta-box {
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 6px 12px;
        text-align: center;
    }
    .acta-box-num {
        font-size: 13px;
        font-weight: 800;
        color: #1e3a8a;
        letter-spacing: 0.5px;
    }
    .acta-box-lbl {
        font-size: 7px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    /* SECCIONES Y TARJETAS */
    .card-section {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        margin-bottom: 11px;
        background-color: #ffffff;
        page-break-inside: avoid;
    }
    .card-header {
        background-color: #1e293b;
        color: #ffffff;
        padding: 4.5px 9px;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-top-left-radius: 5px;
        border-top-right-radius: 5px;
    }
    .card-header .num-pill {
        background-color: #3b82f6;
        color: #ffffff;
        padding: 1px 5px;
        border-radius: 3px;
        margin-right: 4px;
        font-size: 7.5px;
        font-weight: 800;
    }
    .card-body {
        padding: 0;
    }

    /* TABLAS DE DATOS */
    table.grid-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    table.grid-table td, table.grid-table th {
        border: 1px solid #e2e8f0;
        padding: 5px 8px;
        font-size: 7.5px;
        vertical-align: middle;
    }
    table.grid-table th {
        background-color: #f1f5f9;
        color: #334155;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 7px;
        letter-spacing: 0.4px;
    }
    .lbl-col {
        background-color: #f8fafc;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        font-size: 7.5px;
        letter-spacing: 0.2px;
    }
    .val-col {
        font-weight: 700;
        color: #0f172a;
        text-transform: uppercase;
    }

    /* BADGES DE ESTADO */
    .badge {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 3px;
        font-size: 7px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        text-align: center;
    }
    .badge-success { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .badge-danger  { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .badge-warning { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .badge-info    { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .badge-neutral { background-color: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }

    /* OBSERVACIONES */
    .obs-box {
        background-color: #f8fafc;
        padding: 8px 10px;
        font-size: 7.5px;
        color: #0f172a;
        font-weight: 600;
        line-height: 1.45;
    }

    /* EVIDENCIA FOTOGRÁFICA */
    .photo-wrapper {
        padding: 9px;
        text-align: center;
        background-color: #f8fafc;
    }
    .photo-img {
        max-width: 95%;
        max-height: 220px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        padding: 2px;
        background-color: #ffffff;
    }
    .photo-meta {
        margin-top: 5px;
        font-size: 7px;
        font-weight: 700;
        color: #1e3a8a;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }
    .no-photo-box {
        padding: 14px;
        text-align: center;
        color: #94a3b8;
        font-style: italic;
        font-size: 7.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* SECCIÓN DE FIRMAS */
    .signatures-container {
        width: 100%;
        margin-top: 14px;
        page-break-inside: avoid;
    }
    .signatures-table {
        width: 100%;
        border-collapse: collapse;
    }
    .signatures-table td {
        width: 50%;
        padding: 0 10px;
        border: none;
        vertical-align: top;
    }
    .sig-card {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background-color: #ffffff;
        text-align: center;
        padding-bottom: 7px;
    }
    .sig-space {
        height: 44px;
    }
    .sig-line {
        width: 80%;
        border-top: 1px solid #475569;
        margin: 0 auto 5px auto;
    }
    .sig-name {
        font-size: 7.5px;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        letter-spacing: 0.2px;
    }
    .sig-role {
        font-size: 6.5px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-top: 1px;
        letter-spacing: 0.3px;
    }

    /* PIE DE PÁGINA */
    #footer {
        position: fixed;
        bottom: -0.9cm;
        left: 0;
        right: 0;
        height: 20px;
        text-align: center;
        border-top: 1px solid #e2e8f0;
        padding-top: 3px;
        font-size: 6.5px;
        color: #94a3b8;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 0.3px;
    }
</style>
