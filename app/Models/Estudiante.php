<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;

class Estudiante extends Model
{
    protected $table = 'estudiante';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carreraID');
    }

    public function asesor(): HasOne
    {
        return $this->hasOne(Asesor::class, 'estudianteID');
    }
}
