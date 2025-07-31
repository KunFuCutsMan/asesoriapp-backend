<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Asesor;
use App\Models\Carrera;
use App\Models\Estudiante;
use Illuminate\Console\Command;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CreaUsuario extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:crea-usuario {tipo=estudiante}}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera rápidamente un usuario, con la opción de especificar sus datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tipo = $this->argument('tipo');

        $estudiante = Estudiante::factory()->makeOne();

        if ($tipo === 'admin') {
            $this->info('Generando un estudiante administrador...');
            $estudiante = Admin::factory()->create()->asesor->estudiante;
        } else if ($tipo === 'asesor') {
            $this->info('Generando un estudiante asesor...');
            $estudiante = Asesor::factory()->create()->estudiante;
        } else {
            $this->info('Generando un estudiante normal...');
        }

        $nombre = text(
            label: 'Ingrese el nombre del estudiante:',
            placeholder: $estudiante->nombre,
            validate: ['rombre' => 'string|max:32'],
            transform: fn($value) => trim($value)
        );

        if ($nombre) {
            $estudiante->nombre = $nombre;
        }

        $apellidoPaterno = text(
            label: 'Ingrese el apellido paterno del estudiante:',
            placeholder: $estudiante->apellidoPaterno,
            validate: ['apellidoPaterno' => 'string|max:32'],
            transform: fn($value) => trim($value)
        );

        if ($apellidoPaterno) {
            $estudiante->apellidoPaterno = $apellidoPaterno;
        }

        $apellidoMaterno = text(
            label: 'Ingrese el apellido materno del estudiante:',
            placeholder: $estudiante->apellidoMaterno,
            validate: ['apellidoMaterno' => 'string|max:32'],
            transform: fn($value) => trim($value)
        );

        if ($apellidoMaterno) {
            $estudiante->apellidoMaterno = $apellidoMaterno;
        }

        $carreraID = select(
            label: 'Seleccione la carrera del estudiante:',
            options: Carrera::pluck('nombre', 'id'),
            default: $estudiante->carreraID,
            validate: ['carreraID' => ['numeric', 'integer', 'exists:carrera,id']],
            transform: fn($value) => (int)$value
        );

        if ($carreraID) {
            $estudiante->carreraID = $carreraID;
        }

        $semestre = text(
            label: 'Ingrese el semestre del estudiante:',
            placeholder: $estudiante->semestre,
            validate: ['semestre' => 'numeric|integer|gt:0'],
            transform: fn($value) => (int)$value ?: 1
        );

        if ($semestre) {
            $estudiante->semestre = $semestre;
        }

        $telefono = text(
            label: 'Ingrese el número de teléfono del estudiante:',
            placeholder: $estudiante->numeroTelefono,
            validate: ['numeroTelefono' => 'integer|min_digits:10|max_digits:10'],
            transform: fn($value) => (int)$value ?: null
        );

        if ($telefono) {
            $estudiante->numeroTelefono = $telefono;
        }

        $numeroControl = text(
            label: 'Ingrese el número de control del estudiante:',
            placeholder: $estudiante->numeroControl,
            validate: ['numeroControl' => 'string|integer|min_digits:8|max_digits:8|unique:estudiante'],
            transform: fn($value) => trim($value)
        );

        if ($numeroControl) {
            $estudiante->numeroControl = $numeroControl;
        }

        $contrasena = password(
            label: 'Ingrese la contraseña del estudiante:',
            placeholder: 'Contraseña',
            validate: ['contrasena' => ['confirmed', Password::defaults()]],
            transform: fn($value) => trim($value)
        );

        if ($contrasena) {
            $estudiante->contrasena = $contrasena;
        }

        $guardar = confirm(
            label: '¿Desea guardar el usuario?',
            default: false,
        );

        if ($guardar) {
            $estudiante->save();
            info('Usuario guardado exitosamente.');
        }

        $this->info('Usuario creado exitosamente:');
        $this->line("ID: {$estudiante->id}");
        $this->line("Nombre: {$estudiante->nombre} {$estudiante->apellidoPaterno} {$estudiante->apellidoMaterno}");
        $this->line("Número de control: {$estudiante->numeroControl}");
        $this->line("Semestre: {$estudiante->semestre}");
        $this->line("Teléfono: {$estudiante->numeroTelefono}");
        $this->line("Carrera: " . Carrera::find($estudiante->carreraID)->nombre);

        if ($contrasena) {
            $this->line("Contraseña: {$contrasena}");
        }

        if ($tipo === 'asesor') {
            $this->info("AsesorID: {$estudiante->asesor->id}");
        } elseif ($tipo === 'admin') {
            $this->info("AdminID: {$estudiante->asesor->admin->id}");
        }
    }
}
