<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admin';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function asesor(): BelongsTo
    {
        return $this->belongsTo(Asesor::class, 'asesorID');
    }
}
