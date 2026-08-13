<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\UppercaseAttributes;


class Establecimiento extends Model
{
    use HasFactory, UppercaseAttributes;

    protected $table = 'establecimientos';

    /**
     * Atributos asignables masivamente.
     */
    protected $fillable = [
        'codigo',
        'nombre',
        'institucion',
        'direccion',
        'departamento',
        'distrito',
        'provincia',
        'centro_poblado',
        'telefono',
        'correo',
        'microred',
        'red',
        'clas',
        'odsis',
        'responsable',
        'tipo_documento',
        'numero_documento',
        'colegio_profesional',
        'colegiatura',
        'rne',
        'categoria',
        'estado',
        'condicion',
        'latitud',
        'longitud',
        'altitud',
        'fecha_creacion_resolucion',
        'fecha_registro',
        'numero_resolucion_creacion',
        'horario_atencion',
        'numero_ambientes',
        'numero_camas',
        'upss',
        'ups'
    ];

    /**
     * Casteos de atributos a tipos nativos.
     */
    protected $casts = [
        'upss' => 'array',
        'ups'  => 'array',
    ];

    public $timestamps = false;

    /**
     * Relación con las Actas de Monitoreo (Sistema Profesional)
     * Permite acceder a todos los monitoreos realizados a este establecimiento.
     */
    public function monitoreos()
    {
        return $this->hasMany(CabeceraMonitoreo::class, 'establecimiento_id');
    }

    /**
     * Relación con las Actas de Asistencia Técnica (Listado Simple)
     */
    public function actas()
    {
        return $this->hasMany(Acta::class, 'establecimiento_id');
    }

    public function documentosAdministrativos()
    {
        return $this->hasMany(DocumentoAdministrativo::class, 'establecimiento_id');
    }
}