<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Carrera extends Model
{

    protected $table = 'carrera';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function asignaturas(): BelongsToMany
    {
        return $this->belongsToMany(Asignatura::class, 'carrera-asignatura', 'carreraID', 'asignaturaID')
            ->withPivot('semestre');
    }
}
