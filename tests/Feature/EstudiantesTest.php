<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Estudiante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EstudiantesTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     * A basic feature test example.
     */
    public function test_Estudiantes_Post_Route_crea_estudiante_correctamente(): void
    {
        $estudiante = Estudiante::factory()->state([
            'contrasena' => '123456789aB&',
        ])->make();

        $response = $this->post('/api/v1/estudiante', [
            'numeroControl' => $estudiante->numeroControl,
            'contrasena' => $estudiante->contrasena,
            'contrasena_confirmation' => $estudiante->contrasena,
            'nombre' => $estudiante->nombre,
            'apellidoPaterno' => $estudiante->apellidoPaterno,
            'apellidoMaterno' => $estudiante->apellidoMaterno,
            'numeroTelefono' => $estudiante->numeroTelefono,
            'semestre' => $estudiante->semestre,
            'carreraID' => $estudiante->carreraID,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('estudiante', 1);
    }

    public function test_Estudiante_es_editable_por_si_mismo(): void
    {
        $modificado = [
            'numeroControl' => '12345678',
            'nombre' => 'Juan',
            'apellidoPaterno' => 'Camanei',
            'apellidoMaterno' => 'Ramirez',
            'numeroTelefono' => '2290000012',
            'semestre' => 3,
            'carreraID' => 3,
        ];

        $estudiante = Estudiante::factory()->create();
        $estudiante->refresh();

        Sanctum::actingAs($estudiante);

        $route = '/api/v1/estudiante/' . ($estudiante->id);
        $response = $this->patch($route, [
            'numeroControl' => $modificado['numeroControl'],
            'nombre' => $modificado['nombre'],
            'apellidoPaterno' => $modificado['apellidoPaterno'],
            'apellidoMaterno' => $modificado['apellidoMaterno'],
            'numeroTelefono' => $modificado['numeroTelefono'],
            'semestre' => $modificado['semestre'],
            'carreraID' => $modificado['carreraID'],
        ]);

        $response->assertSuccessful();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'nombre',
            'apellidoPaterno',
            'apellidoMaterno',
            'numeroControl',
            'numeroTelefono',
            'semestre',
            'carreraID'
        ]);
        $response->assertJsonMissingPath('contrasena');

        // ¿Se modificó el estudiante?
        $estudiante->refresh();
        $body = $response->getData(true);

        foreach ($modificado as $key => $value) {
            $this->assertEquals($estudiante->{$key}, $value, 'Llave en DB: ' . $key);
            $this->assertEquals($body[$key], $value, 'Llave en respuesta: ' . $key);
        }
    }

    public function test_Admin_puede_cambiar_datos_de_estudiante(): void
    {
        $modificado = [
            'nombre' => 'Juan',
            'apellidoPaterno' => 'Camanei',
            'apellidoMaterno' => 'Ramirez',
        ];

        $admin = Admin::factory()->create();
        $estudiante = Estudiante::factory()->create();

        $admin->refresh();
        $estudiante->refresh();

        Sanctum::actingAs($admin->asesor->estudiante, ['role:admin']);

        $route = 'api/v1/estudiante/' . ($estudiante->id);
        $response = $this->patch($route, [
            'nombre' => $modificado['nombre'],
            'apellidoPaterno' => $modificado['apellidoPaterno'],
            'apellidoMaterno' => $modificado['apellidoMaterno'],
        ]);

        $response->assertSuccessful();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'nombre',
            'apellidoPaterno',
            'apellidoMaterno',
            'numeroControl',
            'numeroTelefono',
            'semestre',
            'carreraID'
        ]);
        $response->assertJsonMissingPath('contrasena');

        // ¿Se modificó el estudiante?
        $estudiante->refresh();
        $body = $response->getData(true);

        foreach ($modificado as $key => $value) {
            $this->assertEquals($estudiante->{$key}, $value, 'Llave en DB: ' . $key);
            $this->assertEquals($body[$key], $value, 'Llave en respuesta: ' . $key);
        }
    }
}
