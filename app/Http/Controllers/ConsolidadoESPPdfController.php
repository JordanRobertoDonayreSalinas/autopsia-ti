<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use stdClass;

class ConsolidadoESPPdfController extends Controller
{
    public function generar($id)
    {
        // 1. Datos Generales
        $acta = CabeceraMonitoreo::with(['establecimiento', 'user'])->findOrFail($id);

        $jefe = [
            'nombre' => mb_strtoupper($acta->responsable ?? 'NO REGISTRADO', 'UTF-8'),
            'cargo' => 'JEFE DE ESTABLECIMIENTO'
        ];

        $creador = $acta->user;
        $monitor = [
            'nombre' => $creador
                ? mb_strtoupper("{$creador->apellido_paterno} {$creador->apellido_materno} {$creador->name}", 'UTF-8')
                : 'IMPLEMENTADOR / MONITOR'
        ];

        // --- MAPA MAESTRO DE ORDEN Y TITULOS ---
        // Este array define QUÉ se muestra y EN QUÉ ORDEN
        $titulosMap = [
            'gestion_admin_esp' => 'MODULO: GESTION ADMINISTRATIVA',
            'citas_esp' => 'MODULO: CITAS',
            'triaje_esp' => 'MODULO: TRIAJE',
            'admision_esp' => 'MODULO: ADMISION',

            'sm_medicina_general' => 'MODULO: SALUD MENTAL / MEDICINA GENERAL',
            'sm_psiquiatria' => 'MODULO: SALUD MENTAL / PSIQUIATRIA',
            'sm_med_familiar' => 'MODULO: SALUD MENTAL / MEDICINA FAMILIAR Y COMUNITARIA',
            'sm_psicologia' => 'MODULO: SALUD MENTAL / PSICOLOGIA',
            'sm_enfermeria' => 'MODULO: SALUD MENTAL / ENFERMERIA',
            'sm_servicio_social' => 'MODULO: SALUD MENTAL / SERVICIO SOCIAL',
            'sm_terapias' => 'MODULO: SALUD MENTAL / TERAPIA DE LENGUAJE OCUPACIONAL',

            'toma_muestra' => 'MODULO: TOMA DE MUESTRAS',
            'farmacia_esp' => 'MODULO: FARMACIA',
            'infraestructura_2d' => 'MODULO: INFRAESTRUCTURA Y CROQUIS 2D'
        ];

        // 2. OBTENCIÓN DE DATOS CRUDOS (DESORDENADOS)
        $modulosNuevos = DB::table('mon_detalle_modulos')
            ->where('cabecera_monitoreo_id', $id)
            ->get()
            ->keyBy('modulo_nombre');

        $modulosAntiguos = DB::table('mon_monitoreo_modulos')
            ->where('cabecera_monitoreo_id', $id)
            ->get();

        // Obtener módulos habilitados desde config_modulos
        $configModulos = $modulosAntiguos->firstWhere('modulo_nombre', 'config_modulos');
        $modulosHabilitados = $configModulos
            ? (is_array($configModulos->contenido) ? $configModulos->contenido : json_decode($configModulos->contenido, true))
            : [];
        $modulosHabilitados = array_filter((array) $modulosHabilitados);

        // Lista de submódulos de Salud Mental
        $submodulosSM = ['sm_medicina_general', 'sm_psiquiatria', 'sm_med_familiar', 'sm_psicologia', 'sm_enfermeria', 'sm_servicio_social', 'sm_terapias'];

        // Si salud_mental_group está habilitado, incluir sus submódulos habilitados
        if (in_array('salud_mental_group', $modulosHabilitados)) {
            $submodulosSMHabilitados = array_intersect($modulosHabilitados, $submodulosSM);
            // Agregar submódulos habilitados a la lista
            $modulosHabilitados = array_merge($modulosHabilitados, $submodulosSMHabilitados);
        }

        // Creamos una colección temporal con todo lo que hay en BD
        $poolDeModulos = collect();

        // Unificamos nuevos y antiguos
        $nombresEnBd = $modulosNuevos->keys()
            ->merge($modulosAntiguos->pluck('modulo_nombre'))
            ->unique();

        foreach ($nombresEnBd as $nombre) {
            // FILTRO: Solo incluir si está habilitado en config_modulos, o si es infraestructura_2d
            if (!in_array($nombre, $modulosHabilitados) && !in_array($nombre, ['config_modulos', 'infraestructura_2d'])) {
                continue; // Saltar módulos deshabilitados
            }

            if ($modulosNuevos->has($nombre)) {
                $poolDeModulos->put($nombre, $modulosNuevos->get($nombre));
            } else {
                $old = $modulosAntiguos->firstWhere('modulo_nombre', $nombre);
                if ($old)
                    $poolDeModulos->put($nombre, $old);
            }
        }

        // 3. CONSTRUCCIÓN DE LA LISTA FINAL ORDENADA
        $modulosFinales = collect();

        // Iteramos el MAPA (no la BD) para respetar estrictamente tu orden
        foreach ($titulosMap as $keyBd => $tituloNuevo) {
            // Si el módulo existe en lo que trajimos de la BD...
            if ($poolDeModulos->has($keyBd)) {
                $modulo = $poolDeModulos->get($keyBd);

                // Le cambiamos el nombre aquí mismo
                $modulo->modulo_nombre = $tituloNuevo;

                // Lo agregamos a la lista final
                $modulosFinales->push($modulo);
            }
            // Si no existe en la BD, simplemente no hacemos nada (no se agrega)
        }

        // 4. EXTRACCIÓN DE EQUIPOS
        // Usamos $modulosFinales, así los equipos también salen en el orden correcto
        $equipos = collect();

        foreach ($modulosFinales as $mod) {
            $cont = is_string($mod->contenido) ? json_decode($mod->contenido, true) : $mod->contenido;
            $lista = $cont['equipos_de_computo'] ?? ($cont['equipos_data'] ?? ($cont['inventario'] ?? []));

            if (is_array($lista) && count($lista) > 0) {
                foreach ($lista as $item) {
                    if (!empty($item['descripcion'])) {
                        $obj = new stdClass();
                        $obj->modulo = $mod->modulo_nombre; // Ya tiene el nombre bonito
                        $obj->descripcion = $item['descripcion'];
                        $obj->cantidad = $item['cantidad'] ?? 1;
                        $obj->estado = $item['estado'] ?? 'OPERATIVO';
                        $obj->propio = $item['propiedad'] ?? ($item['propio'] ?? 'ESTABLECIMIENTO');
                        $obj->nro_serie = $item['nro_serie'] ?? ($item['codigo'] ?? '-');
                        $equipos->push($obj);
                    }
                }
            }
        }

        // 5. Equipo de Acompañamiento
        $equipoMonitoreo = DB::table('mon_equipo_monitoreo')
            ->where('cabecera_monitoreo_id', $id)
            ->get();

        // 6. Preparar PDF
        $data = [
            'acta' => $acta,
            'jefe' => $jefe,
            'monitor' => $monitor,
            'modulos' => $modulosFinales, // Pasamos la lista ya ordenada y filtrada
            'equipos' => $equipos,
            'equipoMonitoreo' => $equipoMonitoreo
        ];

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

        $pdf = Pdf::loadView('usuario.monitoreo.pdf_especializados.consolidado_pdf', $data);

        return $pdf->setPaper('a4', 'portrait')
            ->stream("ACTA_ESP_CONSOLIDADA_N{$acta->id}.pdf");
    }
}