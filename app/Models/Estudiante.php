<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Estudiante extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'estudiante';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numeroControl',
        'contrasena',
        'nombre',
        'apellidoPaterno',
        'apellidoMaterno',
        'semestre',
        'numeroTelefono',
        'carreraID',
    ];

    public function contrasena(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $value,
            set: fn(string $value) => Hash::make($value),
        );
    }

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carreraID');
    }

    public function asesor(): HasOne
    {
        return $this->hasOne(Asesor::class, 'estudianteID');
    }
}
