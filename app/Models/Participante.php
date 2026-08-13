<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Participante extends Model
{
    use HasFactory;

    /**
     * Campos asignables masivamente
     */
    protected $fillable = [
        'acta_id',
        'dni',
        'apellidos',
        'nombres',
        'cargo',
        'modulo',
        'correo',
        'es_implementador',
        'unidad_ejecutora', // ⚠ agregado para que se guarde correctamente
    ];

    protected $casts = [
        'es_implementador' => 'boolean',
    ];

    /**
     * Relación con Acta
     */
    public function acta()
    {
        return $this->belongsTo(Acta::class);
    }
}
