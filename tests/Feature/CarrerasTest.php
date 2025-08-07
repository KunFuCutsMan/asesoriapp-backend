<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarrerasTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_Carreras_Index_Route(): void
    {
        $resp = $this->get('/api/v1/carreras');

        $resp->assertOk();
        $resp->assertJsonIsObject();
        $resp->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nombre',
                    'codigo',
                ],
            ]
        ]);
    }

    public function test_Carreras_Show_Route(): void
    {
        $resp = $this->get('api/v1/carreras/6');

        $resp->assertOk();
        $resp->assertJsonIsObject();
        $resp->assertJsonStructure([
            'data' => [
                'id',
                'nombre',
                'codigo'
            ]
        ]);

        $resp->assertJsonPath('data.nombre', "Mecatrónica");
    }

    public function test_carrera_no_existente(): void
    {
        $response = $this->get('api/v1/carreras/999');
        $response->assertNotFound();
    }
}
