<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UppercaseAttributes;

class EquipoRequerimiento extends Model
{
    use HasFactory, UppercaseAttributes;

    protected $table = 'mon_equipos_requerimiento';

    protected $fillable = [
        'cabecera_monitoreo_id',
        'modulo',
        'descripcion',
        'cantidad',
        'observacion',
    ];

    public function cabecera()
    {
        return $this->belongsTo(CabeceraMonitoreo::class, 'cabecera_monitoreo_id');
    }
}
