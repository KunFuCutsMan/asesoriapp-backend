<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AsignaturasTest extends TestCase
{
    /**
     * Prueba si la ruta index funciona correctamente (sin parametros)
     */
    public function test_Asignaturas_Index_Route(): void
    {
        $response = $this->get('/api/v1/asignaturas');

        $response->assertOk();
        $response->assertJsonIsArray();
        $response->assertJsonStructure([
            '*' => [
                'id',
                'nombre',
            ]
        ]);
    }

    /**
     * Prueba si la ruta index funciona correctamente (incluyendo parametro de carrera correcto)
     */
    public function test_Asignaturas_Index_Route_con_Parametro_Carrera(): void
    {
        // Prueba con una carrera existente
        $response = $this->get('/api/v1/asignaturas?carreraID=6');

        $response->assertOk();

        $response->assertJsonIsArray();
        $response->assertJsonStructure([
            '*' => [
                'id',
                'nombre',
                'pivot' => [
                    'carreraID',
                    'asignaturaID',
                    'semestre'
                ]
            ]
        ]);
    }

    /**
     * Prueba si la ruta index funciona correctamente (incluyendo parametro de carrera que no existe)
     */
    public function test_Asignaturas_Index_Route_con_Parametros_Carrera_Incorrecto(): void
    {
        // Prueba con una carrera existente
        $response = $this->get('/api/v1/asignaturas?carreraID=420');

        $response->assertNotFound();

        //Prueba con algo que no sea un numero
        $response = $this->get("/api/v1/asignaturas?carreraID=owo");
        $response->assertBadRequest();
    }

    function test_Asignaturas_Show_Route(): void
    {
        $response = $this->get('/api/v1/asignaturas/30');

        $response->assertOk();
        $response->assertJsonIsObject();
        /*
        $response->assertJsonStructure([
            'id',
            'nombre',
            'codigo'
        ]);
        */

        $this->assertStringContainsString("Desarrollo Sustentable", $response["nombre"]);
    }
}
