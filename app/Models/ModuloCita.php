<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModuloCita extends Model
{
    use HasFactory;

    protected $table = 'mon_modulo_citas';

    protected $fillable = [
        'fecha_registro',
        'monitoreo_id',
        'personal_nombre',
        'personal_dni',
        'personal_turno',
        'personal_roles',
        'personal_correo',
        'personal_celular',
        'personal_cargo',
        'utiliza_sihce',
        'firma_dj',
        'firma_confidencialidad',
        'tipo_dni_fisico',
        'dnie_version',
        'firma_sihce',
        'capacitacion_recibida',
        'capacitacion_entes',
        'insumos_disponibles',
        'equipos_listado',
        'equipos_observaciones',
        'nro_ventanillas',
        'produccion_listado',
        'calidad_tiempo_espera',
        'calidad_paciente_satisfecho',
        'calidad_usa_reportes',
        'calidad_socializa_con',
        'dificultad_comunica_a',
        'dificultad_medio_uso',
        'fotos_evidencia'
    ];

    // Casting automático: Array <-> JSON
    protected $casts = [
        'personal_roles'      => 'array',
        'capacitacion_entes'  => 'array',
        'insumos_disponibles' => 'array',
        'equipos_listado'     => 'array',
        'produccion_listado'  => 'array',
        'fotos_evidencia'     => 'array',
    ];

    public function acta()
    {
        return $this->belongsTo(CabeceraMonitoreo::class, 'monitoreo_id');
    }
}
