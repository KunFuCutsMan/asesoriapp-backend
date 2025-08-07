<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiaSemana extends Model
{
    /** @use HasFactory<\Database\Factories\DiaSemanaFactory> */
    use HasFactory;

    protected $table = 'dias-semana';

    static $LUNES = 1;
    static $MARTES = 2;
    static $MIERCOLES = 3;
    static $JUEVES = 4;
    static $VIERNES = 5;

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'diaSemanaID');
    }
}
