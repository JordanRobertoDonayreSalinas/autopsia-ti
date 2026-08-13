<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespuestaEntrevistado extends Model
{
    use HasFactory;

    // Conectamos con tu tabla específica de la imagen
    protected $table = 'mon_respuesta_entrevistado';

    protected $fillable = [
        'cabecera_monitoreo_id',
        'doc_profesional',
        'modulo',
        'recibio_capacitacion',
        'inst_que_lo_capacito',
        'inst_a_quien_comunica',
        'medio_que_utiliza'
    ];
}
