<?php

namespace App\Http\Controllers;

use App\Models\CabeceraMonitoreo;
use App\Models\EquipoComputo;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsolidadoPdfController extends Controller
{
    public function generar($id)
    {
        // 1. Datos Generales
        $acta = CabeceraMonitoreo::with(['establecimiento', 'user'])->findOrFail($id);

        $jefe = [
            'nombre' => mb_strtoupper($acta->responsable ?? 'NO REGISTRADO', 'UTF-8'),
            'cargo'  => 'JEFE DE ESTABLECIMIENTO'
        ];
        
        $creador = $acta->user;
        $monitor = [
            'nombre' => $creador 
                ? mb_strtoupper("{$creador->apellido_paterno} {$creador->apellido_materno} {$creador->name}", 'UTF-8')
                : 'USUARIO NO IDENTIFICADO'
        ];

        // 2. FUSIÓN DE DATOS (CRÍTICO PARA QUE JALE TODO)
        // A. Tabla Nueva (Prioridad)
        $modulosNuevos = DB::table('mon_detalle_modulos')
            ->where('cabecera_monitoreo_id', $id)
            ->get()
            ->keyBy('modulo_nombre');

        // B. Tabla Antigua (Respaldo)
        $modulosAntiguos = DB::table('mon_monitoreo_modulos')
            ->where('cabecera_monitoreo_id', $id)
            ->get();

        // C. Combinación en una sola colección '$modulos'
        $modulos = collect();
        
        $nombres = $modulosNuevos->keys()
            ->merge($modulosAntiguos->pluck('modulo_nombre'))
            ->unique();

        foreach($nombres as $nombre) {
            if ($modulosNuevos->has($nombre)) {
                $modulos->push($modulosNuevos->get($nombre));
            } else {
                $old = $modulosAntiguos->firstWhere('modulo_nombre', $nombre);
                if ($old) $modulos->push($old);
            }
        }

        // 3. Equipos (Híbrido SQL + JSON para asegurar compatibilidad)
        $equipos = EquipoComputo::where('cabecera_monitoreo_id', $id)->get();
        
        // Si no hay equipos en SQL, buscar dentro de los JSON de los módulos (Respaldo)
        if ($equipos->isEmpty()) {
            foreach ($modulos as $mod) {
                $cont = is_string($mod->contenido) ? json_decode($mod->contenido, true) : $mod->contenido;
                $lista = $cont['equipos_data'] ?? ($cont['inventario'] ?? []);
                if (is_array($lista) && count($lista) > 0) {
                    foreach ($lista as $item) {
                        if (!empty($item['descripcion'])) {
                            $obj = new \stdClass();
                            $obj->modulo = $mod->modulo_nombre;
                            $obj->descripcion = $item['descripcion'];
                            $obj->cantidad = $item['cantidad'] ?? 1;
                            $obj->estado = $item['estado'] ?? 'REGULAR';
                            $obj->propio = $item['propiedad'] ?? ($item['propio'] ?? 'ESTABLECIMIENTO');
                            $obj->nro_serie = $item['nro_serie'] ?? ($item['codigo'] ?? '-');
                            $obj->observacion = $item['observacion'] ?? ($item['observaciones'] ?? null);
                            $obj->especificaciones = $item['especificaciones'] ?? null;
                            $equipos->push($obj);
                        }
                    }
                }
            }
        }

        // 4. Equipo de Acompañamiento
        $equipoMonitoreo = DB::table('mon_equipo_monitoreo')
            ->where('cabecera_monitoreo_id', $id)
            ->get();

        // 5. Análisis General de Conectividad
        $descargas = [];
        $subidas = [];
        $operadores = [];
        $tipos = [];

        foreach ($modulos as $mod) {
            $cont = is_string($mod->contenido) ? json_decode($mod->contenido, true) : $mod->contenido;
            
            // Try different keys where connectivity might be stored
            $con = $cont['conectividad'] ?? $cont;
            
            $d = floatval($con['velocidad_descarga'] ?? $con['descarga'] ?? $con['velocidad_internet_cantidad'] ?? 0);
            $s = floatval($con['velocidad_subida'] ?? $con['subida'] ?? 0);
            $op = strtoupper(trim($con['operador_servicio'] ?? $con['operador'] ?? ''));
            $tipo = strtoupper(trim($con['tipo_conectividad'] ?? $con['tipo'] ?? ''));

            if ($d > 0) $descargas[] = $d;
            if ($s > 0) $subidas[] = $s;
            if (!empty($op) && $op != 'OTROS') $operadores[] = $op;
            if (!empty($tipo)) $tipos[] = $tipo;
        }

        $analisisConectividad = [
            'max_descarga' => count($descargas) > 0 ? max($descargas) : 0,
            'max_subida'   => count($subidas) > 0 ? max($subidas) : 0,
            'avg_descarga' => count($descargas) > 0 ? round(array_sum($descargas) / count($descargas), 2) : 0,
            'avg_subida'   => count($subidas) > 0 ? round(array_sum($subidas) / count($subidas), 2) : 0,
            'mod_operador' => count($operadores) > 0 ? array_keys(array_count_values($operadores), max(array_count_values($operadores)))[0] : 'NO DEFINIDO',
            'mod_tipo'     => count($tipos) > 0 ? array_keys(array_count_values($tipos), max(array_count_values($tipos)))[0] : 'NO DEFINIDO',
        ];

        // 5. Preparar PDF
        $data = [
            'acta'            => $acta,
            'jefe'            => $jefe,
            'monitor'         => $monitor,
            'modulos'         => $modulos, // VARIABLE CORREGIDA (antes era modulosFinal)
            'equipos'         => $equipos,
            'equipoMonitoreo' => $equipoMonitoreo,
            'analisisConectividad' => $analisisConectividad
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

        $pdf = Pdf::setOptions([
            'isPhpEnabled'         => true,
            'isRemoteEnabled'      => true,
            'isHtml5ParserEnabled' => true,
        ])->loadView('usuario.monitoreo.pdf.consolidado_pdf', $data);
        
        $pdf->setPaper('a4', 'portrait');

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="ACTA_CONSOLIDADA_N' . $acta->id . '.pdf"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0',
            'Pragma'              => 'no-cache',
            'Expires'             => 'Sun, 02 Jan 1990 00:00:00 GMT',
        ]);
    }
}