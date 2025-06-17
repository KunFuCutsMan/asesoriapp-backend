<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asesoria extends Model
{

    protected $table = 'asesoria';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudianteID');
    }

    public function asesor(): HasOne
    {
        return $this->hasOne(Asesor::class, 'asesorID');
    }

    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class, 'asignaturaID');
    }

    public function carreraAsignatura(): BelongsTo
    {
        return $this->asignatura();
    }
}
