<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsesoriaEstado extends Model
{
    use HasFactory;

    protected $table = 'asesoria-estados';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
