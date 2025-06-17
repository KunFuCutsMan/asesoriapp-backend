<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Asignatura extends Model
{
    protected $table = 'carrera';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public string $nombre;

    public function carreras(): BelongsToMany
    {
        return $this->belongsToMany(Carrera::class, 'carrera-asignatura', 'carreraID', 'asignaturaID');
    }
}
