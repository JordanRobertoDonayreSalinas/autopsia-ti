<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\UppercaseAttributes;


class User extends Authenticatable
{
    use HasFactory, Notifiable, UppercaseAttributes;

    protected $fillable = [
        'name',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'username',         // DNI
        'tipo_documento',   // AGREGAR ESTO (según tu SQL)
        'documento',        // AGREGAR ESTO (según tu SQL)
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Accessor corregido para mostrar APELLIDOS primero como pediste
    public function getFullNameAttribute()
    {
        return "{$this->apellido_paterno} {$this->apellido_materno}, {$this->name}";
    }
}