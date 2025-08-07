<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
