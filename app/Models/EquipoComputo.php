<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UppercaseAttributes;


class EquipoComputo extends Model
{
    use HasFactory, UppercaseAttributes;

    /**
     * Tabla asociada al modelo.
     */
    protected $table = 'mon_equipos_computo';

    /**
     * Atributos asignables masivamente.
     * Se agregaron 'nro_serie' y 'observacion' para permitir su guardado.
     */
    protected $fillable = [
        'cabecera_monitoreo_id',
        'modulo',
        'descripcion',
        'cantidad',
        'estado',
        'nro_serie',    // Campo verificado según tu migración
        'propio',
        'observacion'   // Campo verificado según tu migración
    ];

    /**
     * Relación inversa: Un equipo pertenece a una cabecera de monitoreo.
     */
    public function cabecera()
    {
        return $this->belongsTo(CabeceraMonitoreo::class, 'cabecera_monitoreo_id');
    }
}
