<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habilidade extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'nome',
    ];

    public function requisitos()
    {
        return $this->hasMany(Requisito::class);
    }

    public function toArray() 
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome
        ];
    }
}
