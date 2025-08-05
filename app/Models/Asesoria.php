<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asesoria extends Model
{
    use HasFactory;

    protected $table = 'asesoria';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'diaAsesoria',
        'horaInicial',
        'horaFinal',
        'estadoAsesoriaID',
        'estudianteID',
        'carreraID',
        'asignaturaID',
        'asesorID',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudianteID');
    }

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class, 'asesorID');
    }

    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class, 'asignaturaID');
    }

    public function carreraAsignatura(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carreraID');
    }

    public function estadoAsesoria(): BelongsTo
    {
        return $this->belongsTo(AsesoriaEstado::class, 'estadoAsesoriaID');
    }
}
