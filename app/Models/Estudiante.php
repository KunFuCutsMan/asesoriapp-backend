<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Estudiante extends Model implements Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = ['contrasena'];

    protected $with = ['carrera', 'especialidad'];

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

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class, 'especialidadID');
    }

    public function passwordCode(): HasOne
    {
        return $this->hasOne(PasswordCode::class, 'estudianteID')->latestOfMany();
    }

    function activePasswordCode(): HasOne
    {
        return $this->hasOne(PasswordCode::class, 'estudianteID')->ofMany([
            'created_at' => 'max',
            'id' => 'max'
        ], function (EloquentBuilder $query) {
            $query
                ->where('created_at', '>', now()->subMinutes(10))
                ->where('used', '==', false);
        });
    }

    function isAsesor(): bool
    {
        return $this?->asesor !== null ?? false;
    }

    function isAdmin(): bool
    {
        return $this->asesor?->admin !== null ?? false;
    }

    function isEstudiante(): bool
    {
        return !$this->isAsesor() && !$this->isAdmin();
    }

    /** Authenticatable Contract */

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return $this->getKeyName();
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * Get the unique broadcast identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifierForBroadcasting()
    {
        return $this->getAuthIdentifier();
    }

    /**
     * Get the name of the password attribute for the user.
     *
     * @return string
     */
    public function getAuthPasswordName()
    {
        return 'contrasena';
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->{$this->getAuthPasswordName()};
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        if (! empty($this->getRememberTokenName())) {
            return (string) $this->{$this->getRememberTokenName()};
        }
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        if (! empty($this->getRememberTokenName())) {
            $this->{$this->getRememberTokenName()} = $value;
        }
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return $this->rememberTokenName;
    }

    /** Notifiable stuff */
    /**
     * Route notifications for the Vonage channel.
     */
    public function routeNotificationForVonage(Notification $notification): string
    {
        return '52' . $this->numeroTelefono;
    }
}
