<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Especialidad extends Model
{
    use HasFactory;

    protected $table = 'especialidades';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $with = ['carrera'];

    protected $hidden = ['laravel_through_key'];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carreraID');
    }

    public function estudiantes(): HasMany
    {
        return $this->hasMany(Estudiante::class, 'especialidadID');
    }
}
