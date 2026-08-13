<?php

namespace App\Helpers;

class ImplementacionHelper
{
    /**
     * Define todos los submódulos de Implementación y sus modelos.
     */
    public static function getModulos()
    {
        return [
            'ges_adm' => [
                'nombre' => 'Gestión Administrativa',
                'tabla' => 'ges_adm_actas',
                'modelo' => \App\Models\Implementacion\GesAdmActa::class,
                'modelo_usuario' => \App\Models\Implementacion\GesAdmUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\GesAdmImplementador::class,
            ],
            'citas' => [
                'nombre' => 'Citas',
                'tabla' => 'citas_actas',
                'modelo' => \App\Models\Implementacion\CitaActa::class,
                'modelo_usuario' => \App\Models\Implementacion\CitaUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\CitaImplementador::class,
            ],
            'triaje' => [
                'nombre' => 'Triaje',
                'tabla' => 'triaje_actas',
                'modelo' => \App\Models\Implementacion\TriajeActa::class,
                'modelo_usuario' => \App\Models\Implementacion\TriajeUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\TriajeImplementador::class,
            ],
            'medicina' => [
                'nombre' => 'Medicina',
                'tabla' => 'medicina_actas',
                'modelo' => \App\Models\Implementacion\MedicinaActa::class,
                'modelo_usuario' => \App\Models\Implementacion\MedicinaUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\MedicinaImplementador::class,
            ],
            'odontologia' => [
                'nombre' => 'Odontología',
                'tabla' => 'odontologia_actas',
                'modelo' => \App\Models\Implementacion\OdontologiaActa::class,
                'modelo_usuario' => \App\Models\Implementacion\OdontologiaUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\OdontologiaImplementador::class,
            ],
            'nutricion' => [
                'nombre' => 'Nutrición',
                'tabla' => 'nutricion_actas',
                'modelo' => \App\Models\Implementacion\NutricionActa::class,
                'modelo_usuario' => \App\Models\Implementacion\NutricionUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\NutricionImplementador::class,
            ],
            'psicologia' => [
                'nombre' => 'Psicología',
                'tabla' => 'psicologia_actas',
                'modelo' => \App\Models\Implementacion\PsicologiaActa::class,
                'modelo_usuario' => \App\Models\Implementacion\PsicologiaUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\PsicologiaImplementador::class,
            ],
            'fua' => [
                'nombre' => 'FUA Electrónico',
                'tabla' => 'fua_actas',
                'modelo' => \App\Models\Implementacion\FuaActa::class,
                'modelo_usuario' => \App\Models\Implementacion\FuaUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\FuaImplementador::class,
            ],
            'farmacia' => [
                'nombre' => 'Farmacia',
                'tabla' => 'farmacia_actas',
                'modelo' => \App\Models\Implementacion\FarmaciaActa::class,
                'modelo_usuario' => \App\Models\Implementacion\FarmaciaUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\FarmaciaImplementador::class,
            ],
            'referencias' => [
                'nombre' => 'Referencias (Refcon)',
                'tabla' => 'referencias_actas',
                'modelo' => \App\Models\Implementacion\ReferenciaActa::class,
                'modelo_usuario' => \App\Models\Implementacion\ReferenciaUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\ReferenciaImplementador::class,
            ],
            'laboratorio' => [
                'nombre' => 'Laboratorio',
                'tabla' => 'laboratorio_actas',
                'modelo' => \App\Models\Implementacion\LaboratorioActa::class,
                'modelo_usuario' => \App\Models\Implementacion\LaboratorioUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\LaboratorioImplementador::class,
            ],
            'emergencia' => [
                'nombre' => 'Emergencia',
                'tabla' => 'emergencia_actas',
                'modelo' => \App\Models\Implementacion\EmergenciaActa::class,
                'modelo_usuario' => \App\Models\Implementacion\EmergenciaUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\EmergenciaImplementador::class,
            ],
            'mental' => [
                'nombre' => 'Salud Mental',
                'tabla' => 'mental_actas',
                'modelo' => \App\Models\Implementacion\MentalActa::class,
                'modelo_usuario' => \App\Models\Implementacion\MentalUsuario::class,
                'modelo_implementador' => \App\Models\Implementacion\MentalImplementador::class,
            ],
        ];
    }
}
