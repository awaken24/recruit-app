<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'nome', 
        'email', 
        'password',
        'perfil_completo',
        'usuarioable_id', 
        'usuarioable_type'
    ];
    
    protected $hidden = [
        'password',
        'created_at',
        'updated_at'
    ];

    public function usuarioable()
    {
        return $this->morphTo();
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'usuarioable_type' => $this->usuarioable_type,
            'usuarioable_id' => $this->usuarioable_id
        ];
    }
}
