<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EstudianteEspecialidad extends Model
{
    protected $table = "estudiante-especialidad";
    protected $primaryKey = "id";
    public $timestamps = false;

    protected $fillable = [
        'especialidadID',
        'estudianteID',
    ];

    protected $hidden = ['laravel_through_key'];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudianteID');
    }

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class, 'especialidadID');
    }
}
