<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordCode extends Model
{
    protected $table = 'password_code';

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudianteID');
    }
}
