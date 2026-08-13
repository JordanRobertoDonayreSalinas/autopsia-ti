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

        // Mapa inverso para buscar el slug interno a partir del nombre amigable o viceversa
        $mapaModulos = self::getTodosLosModulos();
        $slugBuscado = $modulo ? strtolower(trim($modulo)) : null;

        // Si $modulo es un nombre amigable (ej: "Consulta Externa: Psicología"), buscar su slug
        if ($modulo) {
            // Normalizamos ambos para una comparación segura
            $mapaNormalizado = array_map(function ($val) {
                return strtolower(trim($val));
            }, $mapaModulos);

            $moduloNormalizado = strtolower(trim($modulo));

            if (in_array($moduloNormalizado, $mapaNormalizado)) {
                $slugBuscado = array_search($moduloNormalizado, $mapaNormalizado);
            }
        }

        // 1) Buscar primero en el módulo específico del equipo usando el slug
        if ($slugBuscado) {
            $detalle = $detalles->firstWhere('modulo_nombre', $slugBuscado);
            if (!$detalle) {
                // intentar también case sensitive o exacto si vino directamente como slug ej CONSULTA_PSICOLOGIA
                $detalle = $detalles->firstWhere('modulo_nombre', strtolower($modulo));
            }
            if (!$detalle) {
                $detalle = $detalles->firstWhere('modulo_nombre', $modulo);
            }
            if ($detalle && is_array($detalle->contenido)) {
                $resultado = self::extraerConectividad($detalle->contenido);
                if ($resultado) {
                    return $resultado;
                }
            }
        }

        // 2) Fallback: buscar en cualquier módulo que tenga tipo_conectividad
        foreach ($detalles as $detalle) {
            if (!is_array($detalle->contenido))
                continue;
            $resultado = self::extraerConectividad($detalle->contenido);
            if ($resultado) {
                return $resultado;
            }
        }

        return $vacio;
    }
}
