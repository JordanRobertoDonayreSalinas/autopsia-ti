<?php

namespace App\Helpers;

class ModuloHelper
{
    /**
     * Mapeo de nombres técnicos de módulos a nombres amigables
     */
    public static function getNombreAmigable($moduloTecnico)
    {
        $modulosMaster = [
            // Módulos estándar (NO ESPECIALIZADOS)
            'gestion_administrativa' => 'Gestión Administrativa',
            'citas' => 'Citas',
            'triaje' => 'Triaje',
            'consulta_medicina' => 'Consulta Externa: Medicina',
            'consulta_odontologia' => 'Consulta Externa: Odontología',
            'consulta_nutricion' => 'Consulta Externa: Nutrición',
            'consulta_psicologia' => 'Consulta Externa: Psicología',
            'cred' => 'CRED',
            'inmunizaciones' => 'Inmunizaciones',
            'atencion_prenatal' => 'Atención Prenatal',
            'planificacion_familiar' => 'Planificación Familiar',
            'parto' => 'Parto',
            'puerperio' => 'Puerperio',
            'fua_electronico' => 'FUA Electrónico',
            'farmacia' => 'Farmacia',
            'referencias' => 'Refcon',
            'laboratorio' => 'Laboratorio',
            'urgencias' => 'Urgencias y Emergencias',

            // Módulos especializados (CSMC)
            'gestion_admin_esp' => 'Gestión Administrativa',
            'citas_esp' => 'Citas',
            'triaje_esp' => 'Triaje',
            'salud_mental_group' => 'Salud Mental',
            'toma_muestra' => 'Toma de Muestra',
            'farmacia_esp' => 'Farmacia',

            // Sub-módulos de Salud Mental
            'sm_medicina_general' => 'Medicina General',
            'sm_psiquiatria' => 'Psiquiatría',
            'sm_med_familiar' => 'Medicina Familiar y Comunitaria',
            'sm_psicologia' => 'Psicología',
            'sm_enfermeria' => 'Enfermería',
            'sm_servicio_social' => 'Servicio Social',
            'sm_terapias' => 'Terapia Lenguaje / Ocupacional',
        ];

        // Normalizar: minúsculas y convertir guión (-) a subguión (_)
        $clave = strtolower(str_replace('-', '_', trim($moduloTecnico ?? '')));

        if (isset($modulosMaster[$clave])) {
            return $modulosMaster[$clave];
        }

        // Fallback legible: reemplazar guiones/subguiones por espacios y capitalizar
        return ucwords(str_replace(['_', '-'], ' ', strtolower($clave)));
    }

    /**
     * Obtiene todos los módulos ordenados
     */
    public static function getTodosLosModulos()
    {
        return [
            // Módulos estándar
            'gestion_administrativa' => 'Gestión Administrativa',
            'citas' => 'Citas',
            'triaje' => 'Triaje',
            'consulta_medicina' => 'Consulta Externa: Medicina',
            'consulta_odontologia' => 'Consulta Externa: Odontología',
            'consulta_nutricion' => 'Consulta Externa: Nutrición',
            'consulta_psicologia' => 'Consulta Externa: Psicología',
            'cred' => 'CRED',
            'inmunizaciones' => 'Inmunizaciones',
            'atencion_prenatal' => 'Atención Prenatal',
            'planificacion_familiar' => 'Planificación Familiar',
            'parto' => 'Parto',
            'puerperio' => 'Puerperio',
            'fua_electronico' => 'FUA Electrónico',
            'farmacia' => 'Farmacia',
            'referencias' => 'Refcon',
            'laboratorio' => 'Laboratorio',
            'urgencias' => 'Urgencias y Emergencias',

            // Módulos especializados (CSMC)
            'gestion_admin_esp' => 'Gestión Administrativa',
            'citas_esp' => 'Citas',
            'triaje_esp' => 'Triaje',
            'salud_mental_group' => 'Salud Mental',
            'toma_muestra' => 'Toma de Muestra',
            'farmacia_esp' => 'Farmacia',

            // Sub-módulos de Salud Mental
            'sm_medicina_general' => 'Medicina General',
            'sm_psiquiatria' => 'Psiquiatría',
            'sm_med_familiar' => 'Medicina Familiar y Comunitaria',
            'sm_psicologia' => 'Psicología',
            'sm_enfermeria' => 'Enfermería',
            'sm_servicio_social' => 'Servicio Social',
            'sm_terapias' => 'Terapia Lenguaje / Ocupacional',
        ];
    }

    /**
     * Determina si un establecimiento es ESPECIALIZADO (CSMC) o NO ESPECIALIZADO
     * Usa la misma lógica que MonitoreoController
     */
    public static function getTipoEstablecimiento($establecimiento)
    {
        if (!$establecimiento) {
            return 'NO ESPECIFICADO';
        }

        // Códigos de CSMC (Centros de Salud Mental Comunitarios)
        $codigosCSMC = ['25933', '28653', '27197', '34021', '25977', '33478', '27199', '30478'];

        // Nombres de CSMC
        $nombresCSMC = [
            'CSMC TUPAC AMARU',
            'CSMC COLOR ESPERANZA',
            'CSMC DECÍDETE A SER FELIZ',
            'CSMC SANTISIMA VIRGEN DE YAUCA',
            'CSMC VITALIZA',
            'CSMC CRISTO MORENO DE LUREN',
            'CSMC NUEVO HORIZONTE',
            'CSMC MENTE SANA'
        ];

        $esEspecializado = in_array($establecimiento->codigo, $codigosCSMC) ||
            in_array(strtoupper(trim($establecimiento->nombre)), $nombresCSMC);

        return $esEspecializado ? 'ESPECIALIZADO' : 'NO ESPECIALIZADO';
    }

    /**
     * Extrae los datos de conectividad de un array de contenido JSON.
     * Maneja las diferentes claves que usan los distintos controladores.
     */
    private static function extraerConectividad(array $contenido): ?array
    {
        if (!isset($contenido['tipo_conectividad'])) {
            return null;
        }

        $tipo = $contenido['tipo_conectividad'];
        if (empty($tipo)) {
            return null;
        }

        // Algunos controladores guardan 'wifi_fuente', otros lo heredan del componente
        $fuente = $contenido['wifi_fuente'] ?? $contenido['fuente'] ?? 'N/A';

        // Algunos controladores guardan 'operador_servicio'
        $operador = $contenido['operador_servicio'] ?? $contenido['operador'] ?? 'N/A';

        return [
            'tipo' => $tipo,
            'fuente' => $fuente ?: 'N/A',
            'operador' => $operador ?: 'N/A',
        ];
    }

    /**
     * Obtiene la información de conectividad de una cabecera de monitoreo.
     * 
     * @param  mixed       $cabecera   Modelo CabeceraMonitoreo (con detalles cargados)
     * @param  string|null $modulo     Slug del módulo del equipo (para buscar primero ahí)
     */
    public static function getConectividadActa($cabecera, ?string $modulo = null): array
    {
        $vacio = ['tipo' => 'N/A', 'fuente' => 'N/A', 'operador' => 'N/A'];

        if (!$cabecera || !$cabecera->detalles) {
            return $vacio;
        }

        $detalles = $cabecera->detalles;
        $detalle = self::resolverDetalleModulo($detalles, $modulo);

        if ($detalle && is_array($detalle->contenido)) {
            $resultado = self::extraerConectividad($detalle->contenido);
            if ($resultado) {
                return $resultado;
            }

            // Si es FUNCIONAL vinculado a un consultorio físico, la conectividad
            // se hereda de ahí (nunca se pregunta en el funcional): resolverla
            // antes de caer al fallback genérico de abajo, que de otro modo
            // podría atribuirle la conectividad de un consultorio no relacionado.
            $detalleVinculado = self::resolverDetalleVinculado($detalles, $detalle);
            if ($detalleVinculado && is_array($detalleVinculado->contenido)) {
                $resultado = self::extraerConectividad($detalleVinculado->contenido);
                if ($resultado) {
                    return $resultado;
                }
            }
        }

        // Fallback: buscar en cualquier módulo que tenga tipo_conectividad
        foreach ($detalles as $d) {
            if (!is_array($d->contenido))
                continue;
            $resultado = self::extraerConectividad($d->contenido);
            if ($resultado) {
                return $resultado;
            }
        }

        return $vacio;
    }

    /**
     * Encuentra el registro de módulo (mon_monitoreo_modulos) que corresponde
     * a un slug o nombre amigable de módulo, dentro de la colección de
     * detalles de una cabecera.
     */
    private static function resolverDetalleModulo($detalles, ?string $modulo)
    {
        if (!$modulo) {
            return null;
        }

        $slugBuscado = strtolower(trim($modulo));

        // Si $modulo es un nombre amigable (ej: "Consulta Externa: Psicología"), buscar su slug
        $mapaNormalizado = array_map(fn($val) => strtolower(trim($val)), self::getTodosLosModulos());
        if (in_array($slugBuscado, $mapaNormalizado)) {
            $slugBuscado = array_search($slugBuscado, $mapaNormalizado);
        }

        return $detalles->firstWhere('modulo_nombre', $slugBuscado)
            ?? $detalles->firstWhere('modulo_nombre', strtolower($modulo))
            ?? $detalles->firstWhere('modulo_nombre', $modulo);
    }

    /**
     * Si el detalle dado es un consultorio FUNCIONAL vinculado a un físico,
     * devuelve el registro de ese físico dentro de la misma colección de
     * detalles (o null si no aplica o no se encuentra).
     */
    private static function resolverDetalleVinculado($detalles, $detalle)
    {
        if (!$detalle || !is_array($detalle->contenido)) {
            return null;
        }

        $tipoConsultorio = strtoupper($detalle->contenido['tipo_consultorio'] ?? '');
        if ($tipoConsultorio !== 'FUNCIONAL') {
            return null;
        }

        $vinculadoSlug = trim($detalle->contenido['consultorio_vinculado'] ?? '');
        if (!$vinculadoSlug) {
            return null;
        }

        return $detalles->firstWhere('modulo_nombre', $vinculadoSlug);
    }

    /**
     * Datos del consultorio (servicio/departamento asociado, tipo físico o
     * funcional, y a qué consultorio físico está vinculado si aplica) para
     * mostrar en reportes junto a los equipos de cada módulo.
     */
    public static function getDatosConsultorio($cabecera, ?string $modulo = null): array
    {
        $vacio = [
            'servicio_asociado' => '',
            'departamento_asociado' => '',
            'tipo_consultorio' => '',
            'vinculado_a' => '',
        ];

        if (!$cabecera || !$cabecera->detalles) {
            return $vacio;
        }

        $detalles = $cabecera->detalles;
        $detalle = self::resolverDetalleModulo($detalles, $modulo);

        if (!$detalle || !is_array($detalle->contenido)) {
            return $vacio;
        }

        $contenido = $detalle->contenido;
        $vinculadoTitulo = '';
        $detalleVinculado = self::resolverDetalleVinculado($detalles, $detalle);
        if ($detalleVinculado && is_array($detalleVinculado->contenido)) {
            $vinculadoTitulo = $detalleVinculado->contenido['titulo_consultorio'] ?? $detalleVinculado->modulo_nombre;
        }

        return [
            'servicio_asociado' => strtoupper($contenido['servicio_asociado'] ?? ''),
            'departamento_asociado' => strtoupper($contenido['departamento_asociado'] ?? ''),
            'tipo_consultorio' => strtoupper($contenido['tipo_consultorio'] ?? ''),
            'vinculado_a' => strtoupper($vinculadoTitulo),
        ];
    }
}
