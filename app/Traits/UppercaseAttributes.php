<?php

namespace App\Traits;

trait UppercaseAttributes
{
    /**
     * Sobrescribe el método getAttribute para convertir strings a mayúsculas.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        // Si el valor es una cadena y no es un campo excluido, se convierte a mayúsculas
        if (is_string($value) && !empty($value) && !$this->shouldSkipUppercase($key)) {
            return mb_strtoupper($value, 'UTF-8');
        }

        return $value;
    }

    /**
     * Determina si un atributo debe saltarse la conversión.
     */
    protected function shouldSkipUppercase($key)
    {
        // Campos que tradicionalmente no deben ser convertidos
        $excluded = [
            'password',
            'remember_token',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'deleted_at',
            'fecha',
            'profile_photo_path',
            'role',
            'status',
            'foto1',
            'foto2',
            'foto3',
            'foto4',
            'foto5',
            'imagen1',
            'imagen2',
            'imagen3',
            'imagen4',
            'imagen5',
            'firmado_pdf',
            'username',
        ];

        // También saltar si es una fecha definida en el modelo
        if (method_exists($this, 'getDates') && in_array($key, $this->getDates())) {
            return true;
        }

        // Saltar si el campo termina en _id o tiene un formato camello específico
        if (str_ends_with($key, '_id') || str_ends_with($key, '_path') || str_ends_with($key, '_url')) {
            return true;
        }

        return in_array($key, $excluded);
    }
}
