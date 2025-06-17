<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Admin extends Model
{
    protected $table = 'admin';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class, 'asesorID');
    }
}
