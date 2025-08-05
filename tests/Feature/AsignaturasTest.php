<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nombre',
                    'carreras' => [
                        '*' => [
                            'carreraID',
                            'semestre',
                        ]
                    ],
                ]
            ]
        ]);
        $response->assertJsonMissingPath('data.0.carrera');
    }

    /**
     * Prueba si la ruta index funciona correctamente (incluyendo parametro de carrera correcto)
     */
    public function test_Asignaturas_Index_Route_con_Parametro_Carrera(): void
    {
        // Prueba con una carrera existente
        $response = $this->get('/api/v1/asignaturas?carreraID=6');

        $response->assertOk();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nombre',
                    'carrera' => [
                        'carreraID',
                        'semestre',
                    ],
                ]
            ]
        ]);
        $response->assertJsonMissingPath('data.0.carreras');
    }

    /**
     * Prueba si la ruta index funciona correctamente (incluyendo parametro de carrera que no existe)
     */
    public function test_Asignaturas_Index_Route_con_Parametros_Carrera_Incorrecto(): void
    {
        // Prueba con una carrera existente
        $response = $this->get('/api/v1/asignaturas?carreraID=420');
        $response->assertStatus(302);

        //Prueba con algo que no sea un numero
        $response = $this->get("/api/v1/asignaturas?carreraID=owo");
        $response->assertStatus(302);
    }

    function test_Asignaturas_Show_Route(): void
    {
        $response = $this->get('/api/v1/asignaturas/30');

        $response->assertOk();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'nombre',
                'carreras' => [
                    '*' => [
                        'carreraID',
                        'semestre',
                    ]
                ],
            ]
        ]);
        $response->assertJsonMissingPath('data.carrera');
    }
}
