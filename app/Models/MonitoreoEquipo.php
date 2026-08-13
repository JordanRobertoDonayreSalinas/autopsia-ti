<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoreoEquipo extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos.
     * Asegúrate de que coincida con tu migración (mon_equipo_monitoreo).
     */
    protected $table = 'mon_equipo_monitoreo';

    /**
     * Atributos asignables masivamente.
     * Se han añadido 'nro_serie' y 'observacion' al array fillable.
     */
    protected $fillable = [
        'cabecera_monitoreo_id',
        'tipo_doc',
        'doc',
        'nro_serie',        // Nuevo campo
        'apellido_paterno',
        'apellido_materno',
        'nombres',
        'cargo',
        'institucion',
        'observacion'       // Nuevo campo
    ];

    /**
     * Relación con la Cabecera del Monitoreo.
     * Permite acceder a los datos generales del acta desde un miembro del equipo.
     */
    public function cabecera()
    {
        return $this->belongsTo(CabeceraMonitoreo::class, 'cabecera_monitoreo_id');
    }

    /**
     * Scope para búsqueda por documento o apellido.
     * Útil para limpiar el código del controlador en el futuro.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('doc', 'LIKE', "%$term%")
                     ->orWhere('apellido_paterno', 'LIKE', "%$term%");
    }
}