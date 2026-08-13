<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CabeceraMonitoreo;
use App\Models\MonitoreoModulos;
use stdClass;

class EnfermeriaESPpdfController extends Controller
{
    /**
     * Helper para obtener la ruta de la foto de forma segura
     */
    private function getFotoPath($data)
    {
        // 1. Estructura NUEVA (dentro de comentarios_y_evidencias)
        if (!empty($data['comentarios_y_evidencias']['foto_evidencia'][0])) {
            return $data['comentarios_y_evidencias']['foto_evidencia'][0];
        }
        // 2. Estructura ANTIGUA (raíz)
        if (!empty($data['foto_evidencia'][0])) {
            return $data['foto_evidencia'][0];
        }
        return null;
    }

    public function generar($id)
    {
        // 1. Cargar datos generales
        $acta = CabeceraMonitoreo::with(['establecimiento', 'user'])->findOrFail($id);

        // 2. Cargar el JSON guardado
        $registro = MonitoreoModulos::where('cabecera_monitoreo_id', $id)
                            ->where('modulo_nombre', 'sm_enfermeria')
                            ->first();

        // Obtener datos de forma segura
        $data = $registro ? $registro->contenido : [];
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        $data = $data ?? [];

        $acta->fecha_validacion = $registro ? $registro->updated_at : null;

        // ---------------------------------------------------------
        // 3. MAPEO DE DATOS (NUEVA ESTRUCTURA -> VISTA PDF)
        // ---------------------------------------------------------

        // A. PROFESIONAL (Combina Datos Personales + Doc. Administrativa)
        $profObj = new stdClass();
        $pData    = $data['datos_del_profesional'] ?? ($data['profesional'] ?? []);
        $docAdmin = $data['documentacion_administrativa'] ?? ($data['profesional'] ?? []);

        $profObj->tipo_doc         = $pData['tipo_doc'] ?? 'DNI';
        $profObj->doc              = $pData['doc'] ?? '';
        $profObj->nombres          = $pData['nombres'] ?? '';
        $profObj->apellido_paterno = $pData['apellido_paterno'] ?? '';
        $profObj->apellido_materno = $pData['apellido_materno'] ?? '';
        $profObj->cargo            = $pData['cargo'] ?? '';
        $profObj->telefono         = $pData['telefono'] ?? '';
        $profObj->email            = $pData['email'] ?? '';
        
        // Mapeo de documentación administrativa
        $profObj->cuenta_sihce           = $docAdmin['utiliza_sihce'] ?? ($docAdmin['cuenta_sihce'] ?? 'NO'); 
        $profObj->firmo_dj               = $docAdmin['firmo_dj'] ?? ($data['firmo_dj'] ?? 'NO'); // Fallback a raíz si es antiguo
        $profObj->firmo_confidencialidad = $docAdmin['firmo_confidencialidad'] ?? ($data['firmo_confidencialidad'] ?? 'NO');

        // B. INICIO DE LABORES (Consultorio + Comentarios)
        $dbInicioLabores = new stdClass();
        $initData = $data['detalle_del_consultorio'] ?? ($data['inicio_labores'] ?? []);
        $comData  = $data['comentarios_y_evidencias'] ?? [];

        $dbInicioLabores->fecha_registro     = $initData['fecha_monitoreo'] ?? ($initData['fecha_registro'] ?? ($data['fecha_registro'] ?? null));
        $dbInicioLabores->turno              = $initData['turno'] ?? ($data['turno'] ?? null);
        $dbInicioLabores->cant_consultorios  = $initData['num_consultorios'] ?? ($initData['consultorios'] ?? ($data['num_consultorios'] ?? null));
        $dbInicioLabores->nombre_consultorio = $initData['denominacion'] ?? ($initData['nombre_consultorio'] ?? ($data['denominacion_consultorio'] ?? null));
        
        // Comentarios: prioridad a la nueva sección, luego fallback
        $dbInicioLabores->comentarios        = $comData['comentarios'] ?? ($initData['comentarios'] ?? ($data['comentarios_generales'] ?? null));

        // C. CAPACITACIÓN
        $dbCapacitacion = new stdClass();
        $capData = $data['detalles_de_capacitacion'] ?? [];
        
        $dbCapacitacion->recibieron_cap  = $capData['recibio_capacitacion'] ?? ($data['recibio_capacitacion'] ?? 'NO');
        $dbCapacitacion->institucion_cap = $capData['inst_que_lo_capacito'] ?? ($data['inst_capacitacion'] ?? '-');
        $dbCapacitacion->profesional     = $profObj; 

        // D. DNI
        $dbDni = new stdClass();
        $dniData = $data['detalle_de_dni_y_firma_digital'] ?? ($data['seccion_dni'] ?? []);
        
        $dbDni->tip_dni     = $dniData['tipo_dni'] ?? null;
        $dbDni->version_dni = $dniData['version_dnie'] ?? null;
        $dbDni->firma_sihce = $dniData['firma_digital_sihce'] ?? ($dniData['firma_sihce'] ?? null);
        $dbDni->comentarios = $dniData['observaciones_dni'] ?? ($dniData['comentarios'] ?? null);

        // E. INVENTARIO (Equipos)
        // Buscamos 'equipos_de_computo' (nuevo) o 'inventario' (viejo)
        $invArray = $data['equipos_de_computo'] ?? ($data['inventario'] ?? []);
        
        $dbInventario = collect($invArray)->map(function($item) {
            $item = (array)$item; 
            $obj = new stdClass();
            $obj->descripcion = $item['descripcion'] ?? '';
            $obj->cantidad    = $item['cantidad'] ?? 1;
            $obj->estado      = $item['estado'] ?? '';
            $obj->propio      = $item['propio'] ?? ($item['propiedad'] ?? 'COMPARTIDO'); 
            $obj->nro_serie   = $item['nro_serie'] ?? ($item['codigo'] ?? '');
            $obj->observacion = $item['observacion'] ?? '';
            return $obj;
        });

        // F. DIFICULTADES (Soporte)
        $dbDificultad = new stdClass();
        $sopData = $data['soporte'] ?? [];
        
        $dbDificultad->insti_comunica = $sopData['inst_a_quien_comunica'] ?? ($data['comunica_a'] ?? null);
        $dbDificultad->medio_comunica = $sopData['medio_que_utiliza'] ?? ($data['medio_soporte'] ?? null);

        // G. FOTOS
        $fotoUrl = $this->getFotoPath($data);

        // 4. GENERAR PDF
        // Extraer firma_jefe
        $firma_jefe = '0';
        if (isset($monitoreoModulo)) {
            $c = $monitoreoModulo->contenido ?? [];
            $firma_jefe = $c['profesional']['firma_jefe'] ?? $c['rrhh']['firma_jefe'] ?? $c['personal']['firma_jefe'] ?? $c['datos_del_profesional']['firma_jefe'] ?? $c['firma_jefe'] ?? '0';
        } else {
            $mod_data = \App\Models\MonitoreoModulos::where('cabecera_monitoreo_id', $id)->get();
            foreach($mod_data as $md) {
                $c = $md->contenido ?? [];
                $fj = $c['profesional']['firma_jefe'] ?? $c['rrhh']['firma_jefe'] ?? $c['personal']['firma_jefe'] ?? $c['datos_del_profesional']['firma_jefe'] ?? $c['firma_jefe'] ?? '0';
                if ($fj == '1') {
                    $firma_jefe = '1';
                    break;
                }
            }
        }

        $pdf = Pdf::loadView('usuario.monitoreo.pdf_especializados.enfermeria_pdf', [
            'acta'            => $acta,
            'dbCapacitacion'  => $dbCapacitacion,
            'dbInventario'    => $dbInventario,
            'dbDificultad'    => $dbDificultad,
            'dbInicioLabores' => $dbInicioLabores,
            'dbDni'           => $dbDni,
            'fotoUrl'         => $fotoUrl,
            'profObj'         => $profObj,
            'prof'            => $profObj // Alias importante para algunas vistas
        ]);

        $pdf->setOption('isPhpEnabled', true);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream("04.5_Enfermeria_ESP_Acta_{$acta->numero_acta}.pdf");
    }
}