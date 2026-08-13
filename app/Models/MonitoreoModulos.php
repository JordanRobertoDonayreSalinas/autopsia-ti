<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoreoModulos extends Model
{
    use HasFactory;

    /**
     * Tabla asociada al modelo en la base de datos.
     */
    protected $table = 'mon_monitoreo_modulos';

    /**
     * Atributos asignables masivamente.
     * Se agrega 'pdf_firmado_path' para permitir el registro de las firmas por módulo.
     */
    protected $fillable = [
        'cabecera_monitoreo_id',
        'modulo_nombre',
        'contenido',
        'pdf_firmado_path',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     * El 'contenido' se maneja como array para procesar datos JSON de forma transparente.
     */
    protected $casts = [
        'contenido' => 'array',
    ];

    /**
     * Relación inversa con la Cabecera del Monitoreo.
     * Un registro de módulo pertenece a una única acta de monitoreo.
     */
    public function cabecera()
    {
        return $this->belongsTo(CabeceraMonitoreo::class, 'cabecera_monitoreo_id');
    }
}