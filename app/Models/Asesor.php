<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asesor extends Model
{
    use HasFactory;

    protected $table = 'asesor';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudianteID');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'asesorID');
    }
}
