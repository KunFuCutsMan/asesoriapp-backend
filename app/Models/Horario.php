<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Horario extends Model
{
    /** @use HasFactory<\Database\Factories\HorarioFactory> */
    use HasFactory;

    protected $table = 'horarios';
    protected $primaryKey = 'id';

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class, 'asesorID');
    }

    public function diaSemana(): BelongsTo
    {
        return $this->belongsTo(DiaSemana::class, 'diaSemanaID');
    }
}
